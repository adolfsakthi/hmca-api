<?php

namespace App\Services\PMS;

use App\Repositories\PMS\Interfaces\ReservationRepositoryInterface;
use App\Repositories\PMS\Interfaces\RoomRepositoryInterface;
use Illuminate\Support\Facades\Storage;

class ReservationService
{
    protected $reservationRepository;
    protected $roomRepository;

    public function __construct(ReservationRepositoryInterface $reservationRepository, RoomRepositoryInterface $roomRepository)
    {
        $this->reservationRepository = $reservationRepository;
        $this->roomRepository = $roomRepository;
    }

    public function getAllReservations(string $propertyCode)
    {
        $reservations = $this->reservationRepository->getAllbyProperty($propertyCode);

        return response()->json([
            'success' => true,
            'message' => 'Reservations fetched successfully.',
            'data' => $reservations,
        ]);
    }

    public function getReservationById(string $id, string $propertyCode)
    {
        $reservation =  $this->reservationRepository->getByProperty($id, $propertyCode);
        if (!$reservation) {
            return response()->json([
                'success' => false,
                'message' => 'Reservation not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Reservation fetched successfully.',
            'data' => $reservation,
        ]);
    }

    public function createReservation(array $data)
    {
        foreach (['guest_image' => 'guest_images', 'front_doc' => 'guest_docs', 'back_doc' => 'guest_docs'] as $key => $folder) {
            if (isset($data[$key]) && $data[$key] instanceof \Illuminate\Http\UploadedFile) {
                $data[$key] = $data[$key]->store($folder, 'public');
            }
        }

        $reservation = $this->reservationRepository->create($data);

        $room = $this->roomRepository->findById($data['room_id']);

        $this->roomRepository->update($room, ['status' => 'reserved']);

        return response()->json([
            'success' => true,
            'message' => 'Reservation created successfully and room marked as reserved.',
            'data' => $reservation,
        ], 201);
    }

    public function updateReservation(string $id, array $data)
    {

        $reservation = $this->reservationRepository->getByProperty($id, $data['property_code']);
        if (!$reservation) {
            return response()->json([
                'success' => false,
                'message' => 'Reservation not found.'
            ], 404);
        }

        $this->handleFileUpdate($reservation, $data, 'guest_image', 'guest_images');
        $this->handleFileUpdate($reservation, $data, 'front_doc', 'guest_docs');
        $this->handleFileUpdate($reservation, $data, 'back_doc', 'guest_docs');

        $updatedReservation = $this->reservationRepository->update($reservation, $data);

        if (isset($data['status'])) {
            $room = $updatedReservation->room;
            if ($room) {
                match ($data['status']) {
                    'checked-in' => $room->update(['status' => 'occupied']),
                    'checked-out' => $room->update(['status' => 'dirty']),
                    'cancelled' => $room->update(['status' => 'available']),
                    'reserved' => $room->update(['status' => 'reserved']),
                    default => null,
                };
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Reservation updated successfully.',
            'data' => $updatedReservation
        ]);
    }

    public function deleteReservation(string $id, string $propertyCode)
    {
        $reservation = $this->reservationRepository->getByProperty($id, $propertyCode);
        if (!$reservation) {
            return response()->json([
                'success' => false,
                'message' => 'Reservation not found.'
            ], 404);
        }

        $room = $reservation->room;
        if ($room) {
            $room->update(['status' => 'available']);
        }

        $this->reservationRepository->delete($reservation);

        foreach (['guest_image', 'front_doc', 'back_doc'] as $fileField) {
            if ($reservation->$fileField && Storage::disk('public')->exists($reservation->$fileField)) {
                Storage::disk('public')->delete($reservation->$fileField);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Reservation deleted successfully. Room set to available.',
        ]);
    }


    private function handleFileUpdate($reservation, array &$data, string $fieldName, string $folder)
    {
        if (isset($data[$fieldName]) && $data[$fieldName] instanceof \Illuminate\Http\UploadedFile) {

            // Delete the old file if it exists
            if ($reservation->$fieldName && Storage::disk('public')->exists($reservation->$fieldName)) {
                Storage::disk('public')->delete($reservation->$fieldName);
            }

            // Store new file and update the data array
            $data[$fieldName] = $data[$fieldName]->store($folder, 'public');
        } else {
            // No new file uploaded → keep existing file path
            unset($data[$fieldName]);
        }
    }
}
