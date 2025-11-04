<?php

namespace App\Repositories\PMS\Interfaces;

use App\Models\PMS\Room;

interface RoomRepositoryInterface
{
    /**
     * Find room by ID (no property check)
     */
    public function findById(int $id);

    /**
     * Find room by ID and property code (scoped)
     */
    public function findByIdByProperty(int $id, string $propertyCode);

    /**
     * Get all rooms (no property scope)
     */
    public function getAll();

    /**
     * Get all rooms for a specific property
     */
    public function getAllByProperty(string $propertyCode);

    /**
     * Create a new room
     */
    public function create(array $data);

    /**
     * Update a room
     */
    public function update(Room $room, array $data);

    /**
     * Delete a room
     */
    public function delete(Room $room): bool;

    public function findByPropertyCode(string $room_number, string $propertyCode);
}
