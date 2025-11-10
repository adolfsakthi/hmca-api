<?php

namespace App\Repositories\HR\ESSL;

use App\Models\HR\ESSL\Device;
use App\Repositories\HR\ESSL\Interfaces\DeviceRepositoryInterface;

class DeviceRepository implements DeviceRepositoryInterface
{
    public function listByProperty(string $propertyCode)
    {
        return Device::where('property_code', $propertyCode)->orderBy('id', 'desc')->get();
    }

    public function findByIdAndProperty(int $id, string $propertyCode)
    {
        return Device::where('property_code', $propertyCode)->find($id);
    }

    public function create(array $data)
    {
        return Device::create($data);
    }

    public function update(int $id, string $propertyCode, array $data)
    {
        $dev = $this->findByIdAndProperty($id, $propertyCode);
        if (!$dev) return null;
        $dev->update($data);
        return $dev;
    }

    public function delete(int $id, string $propertyCode): bool
    {
        $dev = $this->findByIdAndProperty($id, $propertyCode);
        if (!$dev) return false;
        $dev->delete();
        return true;
    }
}
