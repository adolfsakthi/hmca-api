<?php

namespace App\Services\HR;

use App\Repositories\HR\Interfaces\ShiftRepositoryInterface;
use Illuminate\Validation\ValidationException;

class ShiftService
{
    protected ShiftRepositoryInterface $repo;

    public function __construct(ShiftRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function list(string $propertyCode, ?int $perPage = null, ?string $search = null)
    {
        return $this->repo->listByProperty($propertyCode, $perPage, $search);
    }

    public function get(string $propertyCode, int $id)
    {
        return $this->repo->findByIdAndProperty($id, $propertyCode);
    }

    public function create(string $propertyCode, array $data)
    {
        $data['property_code'] = $propertyCode;
        $data['code'] = trim($data['code']);

        if ($this->repo->existsByCode($propertyCode, $data['code'])) {
            throw ValidationException::withMessages(['code' => ['Shift code already exists for this property.']]);
        }

        // basic validation for times: allow overnight shifts (end < start) so don't reject
        return $this->repo->create($data);
    }

    public function update(string $propertyCode, int $id, array $data)
    {
        if (isset($data['code'])) {
            $data['code'] = trim($data['code']);
            if ($this->repo->existsByCode($propertyCode, $data['code'], $id)) {
                throw ValidationException::withMessages(['code' => ['Shift code already exists for this property.']]);
            }
        }

        return $this->repo->update($id, $propertyCode, $data);
    }

    public function delete(string $propertyCode, int $id): bool
    {
        // optionally check for dependencies (employees assigned) here
        return $this->repo->delete($id, $propertyCode);
    }
}
