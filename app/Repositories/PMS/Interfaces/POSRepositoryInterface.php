<?php

namespace App\Repositories\PMS\Interfaces;

interface POSRepositoryInterface {
    public function getAllByProperty(string $propertyCode);
    public function findByIdByProperty(int $id, string $propertyCode);
    public function createSale(array $data);
    public function addSaleItems($sale, array $items);
    public function addPayment($sale, array $payment);
}
