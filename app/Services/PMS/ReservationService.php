<?php

namespace App\Services\PMS;

use App\Repositories\PMS\Interfaces\ReservationRepositoryInterface;
use App\Repositories\PMS\Interfaces\GuestRepositoryInterface;
use App\Repositories\PMS\Interfaces\RoomRepositoryInterface;
use App\Repositories\PMS\Interfaces\TaxRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ReservationService
{
    protected $reservationRepository;
    protected $guestRepository;
    protected $taxRepository;
    protected $roomRepository;

    public function __construct(
        ReservationRepositoryInterface $reservationRepository,
        GuestRepositoryInterface $guestRepository,
        TaxRepositoryInterface $taxRepository,
        RoomRepositoryInterface $roomRepository
    ) {
        $this->reservationRepository = $reservationRepository;
        $this->guestRepository = $guestRepository;
        $this->taxRepository = $taxRepository;
        $this->roomRepository = $roomRepository;
    }

    /**
     * Get all reservations for a property
     */
    public function getAllReservations(string $propertyCode)
    {
        $reservations = $this->reservationRepository->getAllByProperty($propertyCode);

        return response()->json([
            'success' => true,
            'message' => 'Reservations fetched successfully.',
            'data' => $reservations
        ], 200);
    }

    /**
     * Get a single reservation by ID
     */
    public function getReservationById(int $id, string $propertyCode)
    {
        $reservation = $this->reservationRepository->findByIdByProperty($id, $propertyCode);

        if (!$reservation) {
            return response()->json([
                'success' => false,
                'message' => 'Reservation not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Reservation fetched successfully.',
            'data' => $reservation
        ], 200);
    }

    /**
     * Create a new reservation
     */
    public function createReservation(array $data)
    {
        try {
            return DB::transaction(function () use ($data) {

                // 🧍 Guest: Find or Create
                $guest = $this->guestRepository->findByMobileOrEmail(
                    $data['property_code'],
                    $data['mobile_no'] ?? null,
                    $data['email'] ?? null
                );

                if (!$guest) {
                    $guest = $this->guestRepository->create([
                        'property_code' => $data['property_code'],
                        'first_name' => $data['first_name'],
                        'last_name' => $data['last_name'] ?? null,
                        'mobile_no' => $data['mobile_no'] ?? null,
                        'email' => $data['email'] ?? null,
                        'nationality' => $data['nationality'] ?? 'Indian',
                    ]);
                }

                // 🏨 Room Validation

                $room = $this->roomRepository->findByIdByProperty($data['room_id'], $data['property_code']);
                // $room = Room::where('property_code', $data['property_code'])
                //     ->where('id', $data['room_id'])
                //     ->first();

                if (!$room) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Room not found.'
                    ], 404);
                }

                if ($room->status !== 'vacant') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Room is not available for booking.'
                    ], 409);
                }

                // 🧮 Capacity Validation
                $maxAllowed = $room->capacity + $room->extra_capability;
                $totalGuests = ($data['adults'] ?? 0) + ($data['children'] ?? 0);

                if ($totalGuests > $maxAllowed) {
                    return response()->json([
                        'success' => false,
                        'message' => "This room allows only {$maxAllowed} persons (including extra)."
                    ], 422);
                }

                // 💰 Calculate Tax & Total
                $base = $data['booking_charge'] ?? $room->room_price;
                $taxes = $this->taxRepository->getActiveTaxes($data['property_code']);
                $taxAmount = 0;

                foreach ($taxes as $tax) {
                    $taxAmount += $tax->calculateAmount($base);
                }

                $total = $base + $taxAmount + ($data['service_charge'] ?? 0);

                // 🕒 Auto Set Check-in / Check-out Times
                // $checkIn = Carbon::parse($data['check_in'])->setTime(14, 0, 0);   // 2 PM
                // $checkOut = Carbon::parse($data['check_out'])->setTime(11, 0, 0); // 11 AM

                // 📝 Create Reservation
                $reservation = $this->reservationRepository->create([
                    'property_code' => $data['property_code'],
                    'guest_id' => $guest->id,
                    'room_id' => $room->id,
                    'check_in' => $data['check_in'],
                    'check_out' => $data['check_out'],
                    'adults' => $data['adults'] ?? 1,
                    'children' => $data['children'] ?? 0,
                    'booking_type' => $data['booking_type'] ?? 'Walk-in',
                    'booking_charge' => $base,
                    'tax' => $taxAmount,
                    'service_charge' => $data['service_charge'] ?? 0,
                    'total' => $total,
                    'status' => 'reserved'
                ]);

                // 📅 Update room status only if check-in is today
                $today = Carbon::today()->toDateString();
                $checkInDate = $data['check_in']->toDateString();

                if ($checkInDate === $today) {
                    $room->update(['status' => 'reserved']);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Reservation created successfully.',
                    'data' => [
                        'reservation_id' => $reservation->id,
                        'guest' => $guest->first_name,
                        'room' => $room->room_number,
                        'check_in' => $reservation->check_in,
                        'check_out' => $reservation->check_out,
                        // 'room_status' => ($checkInDate === $today) ? 'reserved' : 'availabe',
                        'total' => $total
                    ]
                ], 201);
            });
        } catch (Exception $e) {
            Log::error('Reservation Creation Failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create reservation.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update reservation details
     */

    public function updateReservation(int $id, array $data)
    {
        try {
            return DB::transaction(function () use ($id, $data) {

                $reservation = $this->reservationRepository->findByIdByProperty($id, $data['property_code']);

                if (!$reservation) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Reservation not found.'
                    ], 404);
                }

                $room = $reservation->room;
                $guest = $reservation->guest;

                // 🖼️ Handle guest file uploads
                if (isset($data['front_doc']) && $data['front_doc'] instanceof UploadedFile) {
                    $path = $data['front_doc']->store("public/guests/{$guest->id}");
                    $guest->front_doc = Storage::url($path);
                }

                if (isset($data['back_doc']) && $data['back_doc'] instanceof UploadedFile) {
                    $path = $data['back_doc']->store("public/guests/{$guest->id}");
                    $guest->back_doc = Storage::url($path);
                }

                if (isset($data['guest_image']) && $data['guest_image'] instanceof UploadedFile) {
                    $path = $data['guest_image']->store("public/guests/{$guest->id}");
                    $guest->guest_image = Storage::url($path);
                }

                if (isset($data['identity_type'])) $guest->identity_type = $data['identity_type'];
                if (isset($data['identity_no'])) $guest->identity_no = $data['identity_no'];

                $guest->save();

                // 💰 FINANCIAL RE-CALCULATION LOGIC
                $bookingCharge = $data['booking_charge'] ?? $reservation->booking_charge;
                $advance = $data['advance_amount'] ?? $reservation->advance_amount ?? 0;
                $serviceCharge = $data['service_charge'] ?? $reservation->service_charge ?? 0;
                $discountPercent = $data['discount_percent'] ?? $reservation->discount_percent ?? 0;
                $commissionPercent = $data['commission_percent'] ?? $reservation->commission_percent ?? 0;
                $commissionAmount = $data['commission_amount'] ?? null;

                // 1️⃣ Calculate discount
                $discountAmount = ($discountPercent > 0)
                    ? round(($bookingCharge * $discountPercent) / 100, 2)
                    : 0;

                // 2️⃣ Calculate commission (priority to amount if given)
                if ($commissionAmount === null && $commissionPercent > 0) {
                    $commissionAmount = round(($bookingCharge * $commissionPercent) / 100, 2);
                }

                // 3️⃣ Apply tax dynamically
                $taxes = $this->taxRepository->getActiveTaxes($data['property_code']);
                $taxAmount = 0;
                foreach ($taxes as $tax) {
                    $taxAmount += $tax->calculateAmount($bookingCharge - $discountAmount);
                }

                // 4️⃣ Compute total amount
                $total = ($bookingCharge - $discountAmount)
                    + $serviceCharge
                    + $taxAmount;

                // 5️⃣ Update all financial fields in reservation
                $reservation->update([
                    'booking_charge' => $bookingCharge,
                    'discount_percent' => $discountPercent,
                    'commission_percent' => $commissionPercent,
                    'commission_amount' => $commissionAmount,
                    'discount_reason' => $data['discount_reason'] ?? $reservation->discount_reason,
                    'advance_amount' => $advance,
                    'service_charge' => $serviceCharge,
                    'tax' => $taxAmount,
                    'total' => $total,
                    'paid_amount' => $data['paid_amount'] ?? $reservation->paid_amount,
                ]);

                // 🧾 Handle status logic
                if (isset($data['status']) && $data['status'] === 'checked_in') {
                    if (!in_array($room->status, ['reserved', 'vacant'])) {
                        return response()->json([
                            'success' => false,
                            'message' => "Room is not available for check-in (current status: {$room->status})."
                        ], 409);
                    }

                    if (
                        empty($guest->identity_type) ||
                        empty($guest->identity_no) ||
                        empty($guest->front_doc) ||
                        empty($guest->guest_image)
                    ) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Guest details incomplete. ID and photo are mandatory before check-in.'
                        ], 422);
                    }

                    $reservation->update([
                        'status' => 'checked_in',
                        'check_in' => now()
                    ]);
                    $room->update(['status' => 'occupied']);

                    return response()->json([
                        'success' => true,
                        'message' => 'Guest checked in successfully.',
                        'data' => [
                            'reservation_id' => $reservation->id,
                            'room_status' => 'occupied',
                            'check_in_time' => $reservation->check_in,
                            'financials' => [
                                'booking_charge' => $bookingCharge,
                                'tax' => $taxAmount,
                                'discount' => $discountAmount,
                                'commission' => $commissionAmount,
                                'total' => $total,
                                'advance_paid' => $advance
                            ]
                        ]
                    ], 200);
                }

                if (isset($data['status']) && $data['status'] === 'checked_out') {
                    $paidAmount = $data['paid_amount'] ?? $reservation->paid_amount;

                    if ($paidAmount < $reservation->total) {
                        return response()->json([
                            'success' => false,
                            'message' => "Total amount not paid. ₹{$reservation->total} required, only ₹{$paidAmount} received."
                        ], 422);
                    }

                    $reservation->update([
                        'status' => 'checked_out',
                        'check_out' => now(),
                        'paid_amount' => $paidAmount
                    ]);

                    $room->update(['status' => 'vacant']);

                    return response()->json([
                        'success' => true,
                        'message' => 'Guest checked out successfully.',
                        'data' => [
                            'reservation_id' => $reservation->id,
                            // 'room_status' => 'vacant',
                            'check_out_time' => $reservation->check_out
                        ]
                    ], 200);
                }

                // ✅ Normal update response
                return response()->json([
                    'success' => true,
                    'message' => 'Reservation updated successfully with recalculated amounts.',
                    'data' => [
                        'reservation_id' => $reservation->id,
                        'booking_charge' => $bookingCharge,
                        'discount_percent' => $discountPercent,
                        'commission_percent' => $commissionPercent,
                        'commission_amount' => $commissionAmount,
                        'tax' => $taxAmount,
                        'service_charge' => $serviceCharge,
                        'total' => $total,
                        'advance_amount' => $advance
                    ]
                ], 200);
            });
        } catch (Exception $e) {
            Log::error('Reservation Update Failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update reservation.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete reservation
     */
    public function deleteReservation(int $id, string $propertyCode)
    {
        $reservation = $this->reservationRepository->findByIdByProperty($id, $propertyCode);

        if (!$reservation) {
            return response()->json([
                'success' => false,
                'message' => 'Reservation not found.'
            ], 404);
        }

        $deleted = $this->reservationRepository->delete($reservation);

        return response()->json([
            'success' => $deleted,
            'message' => $deleted ? 'Reservation deleted successfully.' : 'Failed to delete reservation.'
        ], $deleted ? 200 : 500);
    }

    /**
     * Get active (checked-in) reservations
     */
    public function getActiveReservations(string $propertyCode)
    {
        $reservations = $this->reservationRepository->getActiveReservations($propertyCode);

        return response()->json([
            'success' => true,
            'message' => 'Active reservations fetched successfully.',
            'data' => $reservations
        ], 200);
    }

    /**
     * Get reservations between given dates
     */
    public function getReservationsBetweenDates(string $propertyCode, $from, $to)
    {
        $reservations = $this->reservationRepository->getBetweenDates($propertyCode, $from, $to);

        return response()->json([
            'success' => true,
            'message' => 'Reservations fetched successfully.',
            'data' => $reservations
        ], 200);
    }
}
