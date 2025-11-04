<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PMS\Room;
use App\Models\PMS\RoomType;
use App\Models\PMS\Amenity;
use App\Enums\BedTypeEnum;
use App\Enums\RoomSizeEnum;
use App\Enums\RoomStatusEnum;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        $deluxe = RoomType::where('name', 'Deluxe King')->first();
        $suite = RoomType::where('name', 'Executive Suite')->first();
        $villa = RoomType::where('name', 'Ocean Villa')->first();

        $rooms = [
            [
                'property_code' => 'H001',
                'room_type_id' => $deluxe->id,
                'room_number' => '101',
                'capacity' => 2,
                'extra_capability' => 1,
                'room_price' => 3500,
                'bed_charge' => 300,
                'room_size' => RoomSizeEnum::SINGLE->value,
                'bed_number' => 1,
                'bed_type' => BedTypeEnum::KINGBED->value,
                'room_description' => 'Deluxe King room with city view',
                'reserve_condition' => 'Non-refundable.',
                'is_active' => true,
                'status' => RoomStatusEnum::AVAILABLE->value,
            ],
            [
                'property_code' => 'H001',
                'room_type_id' => $suite->id,
                'room_number' => '102',
                'capacity' => 3,
                'extra_capability' => 1,
                'room_price' => 5500,
                'bed_charge' => 400,
                'room_size' => RoomSizeEnum::KING->value,
                'bed_number' => 2,
                'bed_type' => BedTypeEnum::KINGBED->value,
                'room_description' => 'Executive Suite with workspace',
                'reserve_condition' => 'Free cancellation before 24 hours.',
                'is_active' => true,
                'status' => RoomStatusEnum::AVAILABLE->value,
            ],
            [
                'property_code' => 'H002',
                'room_type_id' => $villa->id,
                'room_number' => '201',
                'capacity' => 4,
                'extra_capability' => 2,
                'room_price' => 8000,
                'bed_charge' => 800,
                'room_size' => RoomSizeEnum::QUEEN->value,
                'bed_number' => 2,
                'bed_type' => BedTypeEnum::QUEENBED->value,
                'room_description' => 'Ocean view villa with private pool',
                'reserve_condition' => 'Breakfast included.',
                'is_active' => true,
                'status' => RoomStatusEnum::AVAILABLE->value,
            ],
        ];

        foreach ($rooms as $roomData) {
            $room = Room::updateOrCreate(
                [
                    'property_code' => $roomData['property_code'],
                    'room_number' => $roomData['room_number']
                ],
                $roomData
            );

            // Attach amenities based on property
            $amenityIds = Amenity::where('property_code', $roomData['property_code'])
                ->inRandomOrder()
                ->take(3)
                ->pluck('id')
                ->toArray();

            $room->amenities()->sync($amenityIds);
        }
    }
}
