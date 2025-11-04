<?php

namespace App\Repositories\SuperAdmin;

use App\Models\SuperAdmin\Property;
use App\Repositories\SuperAdmin\Interfaces\PropertyRepositoryInterface;

class PropertyRepository implements PropertyRepositoryInterface
{

    public function findAll()
    {
        return Property::all();
    }

    public function findById($id)
    {
        return Property::find($id);
    }

    public function findByCode(string $code)
    {
        return Property::where('property_code', $code)->first();
    }

    public function findByEmail(string $email)
    {
        return Property::where('email', $email)->first();
    }

    public function create(array $data)
    {
        return Property::create($data);
    }

    public function update(Property $property, array $data)
    {
        $property->update($data);
        return $property;
    }

    public function delete(Property $property)
    {
        return $property->delete();
    }
}
