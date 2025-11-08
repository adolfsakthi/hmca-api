<?php

namespace App\Services\PMS;

use App\Repositories\PMS\Interfaces\ReservationRepositoryInterface;
use App\Repositories\PMS\Interfaces\RoomRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
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
        $reservation =  $this->reservationRepository->findByIdByProperty($id, $propertyCode);
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

        $reservation = $this->reservationRepository->create($data);
        foreach (['guest_image' => 'guest_images', 'front_doc' => 'guest_docs', 'back_doc' => 'guest_docs'] as $key => $folder) {
            if (isset($data[$key]) && $data[$key] instanceof UploadedFile) {

                $reservationFolder = "reservations/{$reservation->id}/{$folder}";

                $data[$key] = $data[$key]->store($reservationFolder, 'public');
            }
        }

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



        $reservation = $this->reservationRepository->findByIdByProperty($id, $data['property_code']);
        if (!$reservation) {
            return response()->json([
                'success' => false,
                'message' => 'Reservation not found.'
            ], 404);
        }

        foreach (
            [
                'guest_image' => 'guest_images',
                'front_doc'   => 'guest_docs',
                'back_doc'    => 'guest_docs'
            ] as $key => $folder
        ) {

            if (isset($data[$key]) && $data[$key] instanceof UploadedFile) {
                // Delete old file if exists
                if (!empty($reservation->$key) && Storage::disk('public')->exists($reservation->$key)) {
                    Storage::disk('public')->delete($reservation->$key);
                }

                // Store new file in same folder
                $reservationFolder = "reservations/{$reservation->id}/{$folder}";
                $path = $data[$key]->store($reservationFolder, 'public');
                $data[$key] = $path;
            } else {
                // Don't overwrite if no new file provided
                unset($data[$key]);
            }
        }

        $updatedReservation = $this->reservationRepository->update($reservation, $data);

        Log::info(["after updated Data", $updatedReservation]);

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


        $reservation = $this->reservationRepository->findByIdByProperty($id, $propertyCode);
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

        $folder = "reservations/{$reservation->id}";
        if (Storage::disk('public')->exists($folder)) {
            Storage::disk('public')->deleteDirectory($folder);
        }

        // foreach (['guest_image', 'front_doc', 'back_doc'] as $fileField) {
        //     if ($reservation->$fileField && Storage::disk('public')->exists($reservation->$fileField)) {
        //         Storage::disk('public')->delete($reservation->$fileField);
        //     }
        // }

        return response()->json([
            'success' => true,
            'message' => 'Reservation deleted successfully. Room set to available.',
        ]);
    }
}
