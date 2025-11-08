<?php

namespace App\Services\PMS;

use App\Repositories\PMS\Interfaces\GuestRepositoryInterface;

class GuestService
{
    protected $guestRepository;

    public function __construct(GuestRepositoryInterface $guestRepository)
    {
        $this->guestRepository = $guestRepository;
    }

    public function getAllGuests(string $propertyCode)
    {
        $guests = $this->guestRepository->getAllByProperty($propertyCode);

        return response()->json([
            'success' => true,
            'message' => 'Guests fetched successfully.',
            'data' => $guests
        ], 200);
    }

    public function getGuestById(int $id, string $propertyCode)
    {
        $guest = $this->guestRepository->findByIdByProperty($id, $propertyCode);

        if (!$guest) {
            return response()->json([
                'success' => false,
                'message' => 'Guest not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Guest fetched successfully.',
            'data' => $guest
        ], 200);
    }

    public function createGuest(array $data)
    {
        $existing = $this->guestRepository->findByMobileOrEmail(
            $data['property_code'],
            $data['mobile_no'] ?? null,
            $data['email'] ?? null
        );

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Guest with this email or phone already exists.'
            ], 409);
        }

        $guest = $this->guestRepository->create($data);

        return response()->json([
            'success' => true,
            'message' => 'Guest created successfully.',
            'data' => $guest
        ], 201);
    }

    public function updateGuest(int $id, array $data)
    {
        $guest = $this->guestRepository->findByIdByProperty($id, $data['property_code']);

        if (!$guest) {
            return response()->json([
                'success' => false,
                'message' => 'Guest not found.'
            ], 404);
        }

        $updatedGuest = $this->guestRepository->update($guest, $data);

        return response()->json([
            'success' => true,
            'message' => 'Guest updated successfully.',
            'data' => $updatedGuest
        ], 200);
    }

    public function deleteGuest(int $id, string $propertyCode)
    {
        $guest = $this->guestRepository->findByIdByProperty($id, $propertyCode);

        if (!$guest) {
            return response()->json([
                'success' => false,
                'message' => 'Guest not found.'
            ], 404);
        }

        $deleted = $this->guestRepository->delete($guest);

        return response()->json([
            'success' => $deleted,
            'message' => $deleted ? 'Guest deleted successfully.' : 'Failed to delete guest.'
        ], $deleted ? 200 : 500);
    }
}
