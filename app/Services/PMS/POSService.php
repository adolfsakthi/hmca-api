<?php

namespace App\Services\PMS;

use App\Repositories\PMS\Interfaces\POSRepositoryInterface;
use App\Repositories\PMS\Interfaces\ReservationRepositoryInterface;
use App\Models\PMS\Guest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class POSService
{
    protected $posRepository;
    protected $reservationRepository;

    public function __construct(
        POSRepositoryInterface $posRepository,
        ReservationRepositoryInterface $reservationRepository
    ) {
        $this->posRepository = $posRepository;
        $this->reservationRepository = $reservationRepository;
    }

    public function index(string $propertyCode)
    {
        return response()->json(['success' => true, 'data' => $this->posRepository->getAllByProperty($propertyCode)], 200);
    }

    public function show(int $id, string $propertyCode)
    {
        $sale = $this->posRepository->findByIdByProperty($id, $propertyCode);
        if (!$sale) return response()->json(['success' => false, 'message' => 'Sale not found'], 404);
        return response()->json(['success' => true, 'data' => $sale], 200);
    }

    public function createSale(array $data)
    {
        try {
            return DB::transaction(function () use ($data) {

                // ✅ Pay Later verification
                if (($data['payment_mode'] ?? '') === 'Pay Later') {
                    $hasReservation = !empty($data['reservation_id']);
                    $hasContact = !empty($data['customer_name']) &&
                        (!empty($data['customer_email']) || !empty($data['customer_phone']));

                    if (!$hasReservation && !$hasContact) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Pay Later orders require a reservation or guest contact (name + email/phone).'
                        ], 422);
                    }

                    if (!$hasReservation && $hasContact) {
                        $guest = Guest::firstOrCreate(
                            ['email' => $data['customer_email'], 'phone' => $data['customer_phone']],
                            [
                                'name' => $data['customer_name'],
                                'property_code' => $data['property_code']
                            ]
                        );
                        $data['customer_name'] = $guest->name;
                        $data['customer_email'] = $guest->email;
                        $data['customer_phone'] = $guest->phone;
                    }
                }

                // calculate totals
                $items = $data['items'];
                $subtotal = 0;
                foreach ($items as $it) {
                    $subtotal += ($it['unit_price'] * $it['quantity']);
                }
                $tax = $data['tax'] ?? 0;
                $discount = $data['discount'] ?? 0;
                $total = round(($subtotal + $tax - $discount), 2);

                $sale = $this->posRepository->createSale([
                    'property_code' => $data['property_code'],
                    'reservation_id' => $data['reservation_id'] ?? null,
                    'customer_name' => $data['customer_name'] ?? null,
                    'customer_email' => $data['customer_email'] ?? null,
                    'customer_phone' => $data['customer_phone'] ?? null,
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'discount' => $discount,
                    'total' => $total,
                    'payment_mode' => $data['payment_mode'] ?? 'Pay Later',
                    'status' => ($data['payment_mode'] === 'Pay Later') ? 'open' : 'settled',
                    'notes' => $data['notes'] ?? null
                ]);

                $payload = [];
                foreach ($items as $it) {
                    $payload[] = [
                        'pos_item_id' => $it['pos_item_id'] ?? null,
                        'name' => $it['name'],
                        'unit_price' => $it['unit_price'],
                        'quantity' => $it['quantity'],
                        'line_total' => ($it['unit_price'] * $it['quantity'])
                    ];
                }
                $this->posRepository->addSaleItems($sale, $payload);

                // payments (for immediate modes)
                if (!empty($data['payments'])) {
                    foreach ($data['payments'] as $p) {
                        $this->posRepository->addPayment($sale, [
                            'amount' => $p['amount'],
                            'payment_mode' => $p['payment_mode'] ?? null,
                            'txn_reference' => $p['txn_reference'] ?? null
                        ]);
                    }
                }

                // 🔗 Update reservation totals if applicable
                if (!empty($data['reservation_id'])) {
                    $reservation = $this->reservationRepository->findByIdByProperty($data['reservation_id'], $data['property_code']);
                    if ($reservation) {
                        $update = ['total' => round(($reservation->total + $total), 2)];
                        if ($sale->payment_mode !== 'Pay Later') {
                            $paid = $sale->payments->sum('amount');
                            $update['paid_amount'] = round(($reservation->paid_amount + $paid), 2);
                        }
                        $this->reservationRepository->update($reservation, $update);
                    }
                }

                return response()->json(['success' => true, 'message' => 'POS sale created successfully', 'data' => $sale], 201);
            });
        } catch (Exception $e) {
            Log::error('POS createSale failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to create POS sale', 'error' => $e->getMessage()], 500);
        }
    }

    public function addPaymentToSale(int $saleId, string $propertyCode, array $payment)
    {
        try {
            return DB::transaction(function () use ($saleId, $propertyCode, $payment) {
                $sale = $this->posRepository->findByIdByProperty($saleId, $propertyCode);
                if (!$sale) return response()->json(['success' => false, 'message' => 'Sale not found'], 404);

                $p = $this->posRepository->addPayment($sale, [
                    'amount' => $payment['amount'],
                    'payment_mode' => $payment['payment_mode'],
                    'txn_reference' => $payment['txn_reference'] ?? null
                ]);

                if ($sale->reservation_id) {
                    $reservation = $this->reservationRepository->findByIdByProperty($sale->reservation_id, $propertyCode);
                    if ($reservation) {
                        $reservation->update([
                            'paid_amount' => round(($reservation->paid_amount + $p->amount), 2)
                        ]);
                    }
                }

                $paidSum = $sale->payments->sum('amount');
                if (round($paidSum, 2) >= round($sale->total, 2)) {
                    $sale->update(['status' => 'settled']);
                }

                return response()->json(['success' => true, 'message' => 'Payment recorded', 'data' => $p], 201);
            });
        } catch (Exception $e) {
            Log::error('POS addPayment failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Payment add failed', 'error' => $e->getMessage()], 500);
        }
    }
}
