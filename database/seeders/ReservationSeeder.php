<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PMS\Reservation;
use App\Models\PMS\Room;
use Illuminate\Support\Str;
use App\Enums\ReservationStatusEnum;
use App\Enums\BookingTypeEnum;
use App\Enums\GenderEnum;
use App\Enums\PaymentModeEnum;

class ReservationSeeder extends Seeder
{
    public function run(): void
    {
        $room = Room::where('room_number', '101')->first();

        Reservation::updateOrCreate(
            ['booking_reference_no' => 'REF-0001'],
            [
                'id' => Str::uuid(),
                'property_code' => 'H001',
                'room_id' => $room->id,
                'check_in' => now()->addDays(1),
                'check_out' => now()->addDays(3),
                'arrival_from' => 'Bangalore',
                'booking_type' => BookingTypeEnum::WalkIn->value,
                'purpose_of_visit' => 'Business',
                'remarks' => 'Late check-in expected',
                'adults' => 2,
                'children' => 0,
                'first_name' => 'John',
                'last_name' => 'Doe',
                'mobile_no' => '9876543210',
                'gender' => GenderEnum::Male->value,
                'occupation' => 'Engineer',
                'country' => 'India',
                'state' => 'Tamil Nadu',
                'city' => 'Chennai',
                'payment_mode' => PaymentModeEnum::Card->value,
                'advance_amount' => 2000,
                'total' => 5500,
                'status' => ReservationStatusEnum::RESERVED->value,
            ]
        );
    }
}
