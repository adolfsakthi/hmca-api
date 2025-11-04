<?php

namespace App\Repositories\PMS;

use App\Models\User;
use App\Repositories\PMS\Interfaces\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{
    public function getAllUserByProperty(int $propertyCode)
    {
        return User::where('property_id', $propertyCode)->get();
    }

    public function findByIdByProperty(int $id, $propertyCode)
    {
        return User::where('property_id', $propertyCode)
            ->where('id', $id)
            ->first();
    }

    public function create(array $data)
    {
        return User::create($data);
    }

    public function update($user, array $data)
    {
        $user->update($data);
        return $user;
    }

    public function delete($user)
    {
        return $user->delete();
    }

    public function findAll()
    {
        return User::all();
    }

    public function findById($id)
    {
        return User::find($id);
    }
}
