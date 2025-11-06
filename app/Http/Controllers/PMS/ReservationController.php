<?php

namespace App\Http\Controllers\PMS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PMS\ReservationService;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Tag(
 *     name="Reservation",
 *     description="APIs for managing guest reservations"
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
     *     tags={"Reservation"},
     *     summary="Get all reservations",
     *     @OA\Response(
     *         response=200,
     *         description="List of reservations fetched successfully"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $propertyCode = $request->get('property_code');
        return $this->reservationService->getAllReservations($propertyCode);
    }

    /**
     * @OA\Get(
     *     path="/api/pms/reservations/{id}",
     *     tags={"Reservation"},
     *     summary="Get a single reservation by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Reservation UUID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reservation details"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Reservation not found"
     *     )
     * )
     */
    public function show($id, Request $request)
    {
        $propertyCode = $request->get('property_code');
        return $this->reservationService->getReservationById($id, $propertyCode);
    }

    /**
     * @OA\Post(
     *     path="/api/pms/reservations",
     *     tags={"Reservation"},
     *     summary="Create a new reservation",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"room_id","check_in","check_out","first_name"},
     *                 @OA\Property(property="room_id", type="string", example="1"),
     *                 @OA\Property(property="check_in", type="string", format="date-time", example="2025-11-07 12:00:00"),
     *                 @OA\Property(property="check_out", type="string", format="date-time", example="2025-11-08 12:00:00"),
     *                 @OA\Property(property="arrival_from", type="string", example="Chennai"),
     *                 @OA\Property(property="booking_type", type="string", example="walk-in"),
     *                 @OA\Property(property="booking_reference_no", type="string", example="BK12345"),
     *                 @OA\Property(property="purpose_of_visit", type="string", example="Business"),
     *                 @OA\Property(property="remarks", type="string", example="Early check-in requested"),
     *
     *                 @OA\Property(property="adults", type="integer", example=2),
     *                 @OA\Property(property="children", type="integer", example=1),
     *
     *                 @OA\Property(property="country_code", type="string", example="+91"),
     *                 @OA\Property(property="mobile_no", type="string", example="9876543210"),
     *                 @OA\Property(property="title", type="string", example="Mr"),
     *                 @OA\Property(property="first_name", type="string", example="John"),
     *                 @OA\Property(property="last_name", type="string", example="Doe"),
     *                 @OA\Property(property="father_name", type="string", example="Robert Doe"),
     *                 @OA\Property(property="gender", type="string", example="male"),
     *                 @OA\Property(property="occupation", type="string", example="Software Engineer"),
     *                 @OA\Property(property="dob", type="string", format="date", example="1995-06-12"),
     *                 @OA\Property(property="anniversary", type="string", format="date", example="2020-02-14"),
     *                 @OA\Property(property="nationality", type="string", example="Indian"),
     *                 @OA\Property(property="is_vip", type="boolean", example=false),
     *
     *                 @OA\Property(property="contact_type", type="string", example="personal"),
     *                 @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *                 @OA\Property(property="country", type="string", example="India"),
     *                 @OA\Property(property="state", type="string", example="Tamil Nadu"),
     *                 @OA\Property(property="city", type="string", example="Chennai"),
     *                 @OA\Property(property="zipcode", type="string", example="600001"),
     *                 @OA\Property(property="address", type="string", example="123 Mount Road, Chennai"),
     *
     *                 @OA\Property(property="identity_type", type="string", example="aadhaar"),
     *                 @OA\Property(property="identity_no", type="string", example="1234-5678-9123"),
     *                 @OA\Property(property="identity_comments", type="string", example="ID verified at front desk"),
     *                 @OA\Property(property="front_doc", type="string", format="binary"),
     *                 @OA\Property(property="back_doc", type="string", format="binary"),
     *                 @OA\Property(property="guest_image", type="string", format="binary"),
     *
     *                 @OA\Property(property="discount_reason", type="string", example="Corporate discount"),
     *                 @OA\Property(property="discount_percent", type="number", format="float", example="10"),
     *                 @OA\Property(property="commission_percent", type="number", format="float", example="5"),
     *                 @OA\Property(property="commission_amount", type="number", format="float", example="500"),
     *
     *                 @OA\Property(property="payment_mode", type="string", example="card"),
     *                 @OA\Property(property="advance_amount", type="number", format="float", example="2000"),
     *                 @OA\Property(property="advance_remarks", type="string", example="Paid via credit card"),
     *
     *                 @OA\Property(property="booking_charge", type="number", format="float", example="4000"),
     *                 @OA\Property(property="tax", type="number", format="float", example="480"),
     *                 @OA\Property(property="service_charge", type="number", format="float", example="200"),
     *                 @OA\Property(property="total", type="number", format="float", example="4680"),
     *
     *                 @OA\Property(property="status", type="string", example="reserved")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Reservation created successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input data"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'first_name' => 'required|string|max:255',
            'booking_type' => 'required|string',
            'guest_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'front_doc' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'back_doc' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'property_code' => 'required|string'
        ]);

        return $this->reservationService->createReservation($request->all());
    }

    /**
     * @OA\Post(
     *     path="/api/pms/reservations/{id}",
     *     tags={"Reservation"},
     *     summary="Update an existing reservation (all fields)",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Reservation UUID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",   
     *                 @OA\Property(property="property_code", type="string", example="H001"),
     *                 @OA\Property(property="room_id", type="string", example="1"),
     *                 @OA\Property(property="check_in", type="string", format="date-time", example="2025-11-07 12:00:00"),
     *                 @OA\Property(property="check_out", type="string", format="date-time", example="2025-11-08 12:00:00"),
     *                 @OA\Property(property="arrival_from", type="string", example="Chennai"),
     *                 @OA\Property(property="booking_type", type="string", example="online"),
     *                 @OA\Property(property="booking_reference_no", type="string", example="BK56789"),
     *                 @OA\Property(property="purpose_of_visit", type="string", example="Vacation"),
     *                 @OA\Property(property="remarks", type="string", example="Late checkout requested"),
     *                 @OA\Property(property="adults", type="integer", example=2),
     *                 @OA\Property(property="children", type="integer", example=1),
     *                 @OA\Property(property="country_code", type="string", example="+91"),
     *                 @OA\Property(property="mobile_no", type="string", example="9876543210"),
     *                 @OA\Property(property="title", type="string", example="Mr"),
     *                 @OA\Property(property="first_name", type="string", example="John"),
     *                 @OA\Property(property="last_name", type="string", example="Doe"),
     *                 @OA\Property(property="father_name", type="string", example="Robert Doe"),
     *                 @OA\Property(property="gender", type="string", example="male"),
     *                 @OA\Property(property="occupation", type="string", example="Software Engineer"),
     *                 @OA\Property(property="dob", type="string", format="date", example="1995-06-12"),
     *                 @OA\Property(property="anniversary", type="string", format="date", example="2020-02-14"),
     *                 @OA\Property(property="nationality", type="string", example="Indian"),
     *                 @OA\Property(property="is_vip", type="int", example=0),
     *                 @OA\Property(property="contact_type", type="string", example="personal"),
     *                 @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *                 @OA\Property(property="country", type="string", example="India"),
     *                 @OA\Property(property="state", type="string", example="Tamil Nadu"),
     *                 @OA\Property(property="city", type="string", example="Chennai"),
     *                 @OA\Property(property="zipcode", type="string", example="600001"),
     *                 @OA\Property(property="address", type="string", example="123 Mount Road, Chennai"),
     *                 @OA\Property(property="identity_type", type="string", example="aadhaar"),
     *                 @OA\Property(property="identity_no", type="string", example="1234-5678-9123"),
     *                 @OA\Property(property="identity_comments", type="string", example="ID verified at front desk"),
     *                 @OA\Property(property="front_doc", type="string", format="binary"),
     *                 @OA\Property(property="back_doc", type="string", format="binary"),
     *                 @OA\Property(property="guest_image", type="string", format="binary"),
     *                 @OA\Property(property="discount_reason", type="string", example="Corporate discount"),
     *                 @OA\Property(property="discount_percent", type="number", format="float", example="10"),
     *                 @OA\Property(property="commission_percent", type="number", format="float", example="5"),
     *                 @OA\Property(property="commission_amount", type="number", format="float", example="500"),
     *                 @OA\Property(property="payment_mode", type="string", example="upi"),
     *                 @OA\Property(property="advance_amount", type="number", format="float", example="1500"),
     *                 @OA\Property(property="advance_remarks", type="string", example="UPI payment received"),
     *                 @OA\Property(property="booking_charge", type="number", format="float", example="4000"),
     *                 @OA\Property(property="tax", type="number", format="float", example="480"),
     *                 @OA\Property(property="service_charge", type="number", format="float", example="200"),
     *                 @OA\Property(property="total", type="number", format="float", example="4680"),
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Reservation updated successfully"),
     *     @OA\Response(response=404, description="Reservation not found")
     * )
     */
    public function update(Request $request, $id)
    {
        return $this->reservationService->updateReservation($id, $request->all());
    }

    /**
     * @OA\Put(
     *     path="/api/pms/reservations/{id}/check-in",
     *     tags={"Reservation"},
     *     summary="Mark reservation as checked-in",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Reservation UUID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="Reservation checked in successfully"),
     *     @OA\Response(response=404, description="Reservation not found")
     * )
     */
    public function checkIn($id, Request $request)
    {
        $propertyCode = $request->get('property_code');
        return $this->reservationService->updateReservation($id, ['property_code' => $propertyCode, 'status' => 'checked-in']);
    }

    /**
     * @OA\Put(
     *     path="/api/pms/reservations/{id}/check-out",
     *     tags={"Reservation"},
     *     summary="Mark reservation as checked-out (room set to dirty)",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Reservation UUID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="Reservation checked out successfully"),
     *     @OA\Response(response=404, description="Reservation not found")
     * )
     */
    public function checkOut($id, Request $request)
    {
        $propertyCode = $request->get('property_code');
        return $this->reservationService->updateReservation($id, ['property_code' => $propertyCode, 'status' => 'checked-out']);
    }

    /**
     * @OA\Put(
     *     path="/api/pms/reservations/{id}/cancel",
     *     tags={"Reservation"},
     *     summary="Cancel a reservation",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Reservation UUID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="Reservation cancelled successfully"),
     *     @OA\Response(response=404, description="Reservation not found")
     * )
     */
    public function cancel($id)
    {
        return $this->reservationService->updateReservation($id, ['status' => 'cancelled']);
    }

    /**
     * @OA\Delete(
     *     path="/api/pms/reservations/{id}",
     *     tags={"Reservation"},
     *     summary="Delete a reservation",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Reservation UUID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="Reservation deleted successfully"),
     *     @OA\Response(response=404, description="Reservation not found")
     * )
     */
    public function destroy($id, Request $request)
    {
        $propertyCode = $request->get('property_code');
        return $this->reservationService->deleteReservation($id, $propertyCode);
    }
}
