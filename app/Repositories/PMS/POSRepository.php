<?php

namespace App\Repositories\PMS;

use App\Models\PMS\POSSale;
use App\Models\PMS\POSSaleItem;
use App\Models\PMS\POSPayment;
use App\Repositories\PMS\Interfaces\POSRepositoryInterface;
use Illuminate\Support\Str;

class POSRepository implements POSRepositoryInterface
{
    public function getAllByProperty(string $propertyCode)
    {
        return POSSale::with(['items', 'payments'])
            ->where('property_code', $propertyCode)
            ->latest()->get();
    }

    public function findByIdByProperty(int $id, string $propertyCode)
    {
        return POSSale::with(['items', 'payments'])
            ->where('property_code', $propertyCode)
            ->find($id);
    }

    public function createSale(array $data)
    {
        $data['invoice_no'] = $data['invoice_no'] ?? 'POS-' . strtoupper(Str::random(8));
        return POSSale::create($data);
    }

    public function addSaleItems($sale, array $items)
    {
        foreach ($items as $it) {
            POSSaleItem::create(array_merge($it, ['pos_sale_id' => $sale->id]));
        }
        return $sale->load('items');
    }

    public function addPayment($sale, array $payment)
    {
        $p = POSPayment::create(array_merge($payment, ['pos_sale_id' => $sale->id]));
        return $p;
    }
}
