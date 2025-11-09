<?php

namespace App\Repositories\HR;

use App\Models\HR\Shift;
use App\Repositories\HR\Interfaces\ShiftRepositoryInterface;

class ShiftRepository implements ShiftRepositoryInterface
{
    public function listByProperty(string $propertyCode, ?int $perPage = null, ?string $search = null)
    {
        $q = Shift::where('property_code', $propertyCode);
        if ($search) {
            $q->where(function($qr) use ($search) {
                $qr->where('code', 'like', "%$search%")
                   ->orWhere('name', 'like', "%$search%");
            });
        }
        $q->orderBy('code');
        return $perPage ? $q->paginate($perPage) : $q->get();
    }

    public function findByIdAndProperty(int $id, string $propertyCode)
    {
        return Shift::where('property_code', $propertyCode)->find($id);
    }

    public function create(array $data)
    {
        return Shift::create($data);
    }

    public function update(int $id, string $propertyCode, array $data)
    {
        $shift = $this->findByIdAndProperty($id, $propertyCode);
        if (!$shift) return null;
        $shift->update($data);
        return $shift;
    }

    public function delete(int $id, string $propertyCode): bool
    {
        $shift = $this->findByIdAndProperty($id, $propertyCode);
        if (!$shift) return false;
        $shift->delete();
        return true;
    }

    public function existsByCode(string $propertyCode, string $code, ?int $ignoreId = null): bool
    {
        $q = Shift::where('property_code', $propertyCode)->where('code', $code);
        if ($ignoreId) $q->where('id', '!=', $ignoreId);
        return $q->exists();
    }
}
