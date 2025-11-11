<?php

namespace App\Services\HR;

use App\Repositories\HR\Interfaces\LeaveTypeRepositoryInterface;
use Illuminate\Validation\ValidationException;

class LeaveTypeService
{
    protected LeaveTypeRepositoryInterface $repo;

    public function __construct(LeaveTypeRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function list(string $propertyCode)
    {
        return $this->repo->listByProperty($propertyCode);
    }

    public function get(string $propertyCode, int $id)
    {
        return $this->repo->findByIdAndProperty($id, $propertyCode);
    }

    public function create(string $propertyCode, array $data)
    {
        $data['property_code'] = $propertyCode;

        $existing = collect($this->repo->listByProperty($propertyCode))->firstWhere('name', $data['name']);
        if ($existing) {
            throw ValidationException::withMessages(['name' => ['Leave type already exists for this property.']]);
        }

        return $this->repo->create($data);
    }

    public function update(string $propertyCode, int $id, array $data)
    {
        return $this->repo->update($id, $propertyCode, $data);
    }

    public function delete(string $propertyCode, int $id): bool
    {
        return $this->repo->delete($id, $propertyCode);
    }
}
