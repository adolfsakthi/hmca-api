<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RoomResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'room_number' => $this->room_number,
            'property_code' => $this->property_code,
            'type' => $this->roomType->name ?? null,
            'capacity' => $this->capacity,
            'extra_beds' => $this->extra_capability,
            'bed_type' => ucfirst($this->bed_type->value),
            'room_size' => $this->room_size,
            'description' => $this->room_description,
            'condition' => $this->reserve_condition,
            'room_price' => (float) $this->room_price,
            'extra_bed_charge' => (float) $this->bed_charge,
            'amenities' => $this->amenities->pluck('name'),
            'status' => $this->status,
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
