<?php

namespace App\Http\Controllers\PMS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PMS\ReservationService;
use Illuminate\Validation\Rule;

/**
 * @OA\Tag(
 *     name="Reservations",
 *     description="API Endpoints for Property Management System Reservations"
 * )
 */
class ReservationController extends Controller
{
    protected $reservationService;

    public function __construct(ReservationService $reservationService)
    {
        $this->reservationService = $reservationService;
    }

    /**
     * @OA\Get(
     *     path="/api/pms/reservations",
     *     tags={"Reservations"},
     *     summary="Get all reservations for a property",
     *     @OA\Response(response=200, description="Reservations fetched successfully")
     * )
     */
    public function index(Request $request)
    {
        $request->validate([
            'property_code' => 'required|string'
        ]);

        return $this->reservationService->getAllReservations($request->property_code);
    }

    /**
     * @OA\Get(
     *     path="/api/pms/reservations/{id}",
     *     tags={"Reservations"},
     *     summary="Get reservation by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Reservation fetched successfully"),
     *     @OA\Response(response=404, description="Reservation not found")
     * )
     */
    public function show(Request $request, int $id)
    {
        $request->validate(['property_code' => 'required|string']);
        return $this->reservationService->getReservationById($id, $request->property_code);
    }

    /**
     * @OA\Post(
     *     path="/api/pms/reservations",
     *     tags={"Reservations"},
     *     summary="Create a new reservation",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"property_code","room_id","first_name","check_in","check_out"},
     *             @OA\Property(property="property_code", type="string"),
     *             @OA\Property(property="room_id", type="integer"),
     *             @OA\Property(property="first_name", type="string"),
     *             @OA\Property(property="last_name", type="string"),
     *             @OA\Property(property="mobile_no", type="string"),
     *             @OA\Property(property="check_in", type="string", format="date-time"),
     *             @OA\Property(property="check_out", type="string", format="date-time"),
     *         )
     *     ),
     *     @OA\Response(response=201, description="Reservation created successfully")
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'property_code' => 'required|string',
            'room_id' => 'required|exists:rooms,id',
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'mobile_no' => 'nullable|string|max:15',
            'email' => 'nullable|email|max:150',
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'booking_type' => ['nullable', Rule::in(['Walk-in', 'Online', 'Corporate'])],
        ]);

        return $this->reservationService->createReservation($validated);
    }

    /**
     * @OA\Post(
     *     path="/api/pms/reservations/{id}/update",
     *     tags={"Reservations"},
     *     summary="Update reservation or perform check-in/check-out",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="property_code", type="string"),
     *             @OA\Property(property="status", type="string", enum={"reserved","checked_in","checked_out","cancelled"}),
     *             @OA\Property(property="booking_charge", type="number"),
     *             @OA\Property(property="discount_percent", type="number"),
     *             @OA\Property(property="commission_percent", type="number"),
     *             @OA\Property(property="commission_amount", type="number"),
     *             @OA\Property(property="service_charge", type="number"),
     *             @OA\Property(property="advance_amount", type="number"),
     *             @OA\Property(property="paid_amount", type="number")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Reservation updated successfully"),
     *     @OA\Response(response=422, description="Validation error or insufficient payment")
     * )
     */
    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'property_code' => 'required|string',
            'booking_charge' => 'nullable|numeric|min:0',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'commission_percent' => 'nullable|numeric|min:0|max:100',
            'commission_amount' => 'nullable|numeric|min:0',
            'service_charge' => 'nullable|numeric|min:0',
            'advance_amount' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'identity_type' => 'nullable|string|max:50',
            'identity_no' => 'nullable|string|max:50',
            'front_doc' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'back_doc' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'guest_image' => 'nullable|file|mimes:jpg,jpeg,png|max:2048'
        ]);

        return $this->reservationService->updateReservation($id, $validated);
    }

    /**
     * @OA\Delete(
     *     path="/api/pms/reservations/{id}",
     *     tags={"Reservations"},
     *     summary="Delete a reservation",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Reservation deleted successfully"),
     *     @OA\Response(response=404, description="Reservation not found")
     * )
     */
    public function destroy(Request $request, int $id)
    {
        $request->validate([
            'property_code' => 'required|string'
        ]);

        return $this->reservationService->deleteReservation($id, $request->property_code);
    }


    /**
     * @OA\Post(
     *     path="/api/pms/reservations/{id}/checkin",
     *     tags={"Reservations"},
     *     summary="Check-in guest for reservation",
     *     description="Validates guest details and room availability before check-in.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"property_code"},
     *                 @OA\Property(property="property_code", type="string"),
     *                 @OA\Property(property="identity_type", type="string"),
     *                 @OA\Property(property="identity_no", type="string"),
     *                 @OA\Property(property="front_doc", type="string", format="binary"),
     *                 @OA\Property(property="back_doc", type="string", format="binary"),
     *                 @OA\Property(property="guest_image", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Guest checked in successfully"),
     *     @OA\Response(response=422, description="Missing guest details"),
     *     @OA\Response(response=409, description="Room not available")
     * )
     */
    public function checkIn(Request $request, int $id)
    {
        // Merge status for service logic
        $validated['status'] = 'checked_in';

        return $this->reservationService->updateReservation($id, $validated);
    }

    /**
     * @OA\Post(
     *     path="/api/pms/reservations/{id}/checkout",
     *     tags={"Reservations"},
     *     summary="Check-out guest and close reservation",
     *     description="Ensures full payment is completed before checkout.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Guest checked out successfully"),
     *     @OA\Response(response=422, description="Total amount not paid")
     * )
     */
    public function checkOut(Request $request, int $id)
    {
        $validated = $request->validate([
            'property_code' => 'required|string',
            'paid_amount' => 'required|numeric|min:0'
        ]);

        // Merge status for service logic
        $validated['status'] = 'checked_out';

        return $this->reservationService->updateReservation($id, $validated);
    }
}
