<?php

namespace App\Http\Resources;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Branch
 */
class BranchResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Branch $branch */
        $branch = $this->resource;

        return [
            'id' => $branch->id,
            'name' => $branch->display_name,
            'country' => $branch->country,
            'country_label' => $branch->country_label,
            'city' => $branch->city,
            'address' => $branch->address,
            'postal_code' => $branch->postal_code,
            'email' => $branch->email,
            'phonecode' => $branch->phonecode,
            'phone' => $branch->phone,
            'active' => (bool) $branch->active,
            'is_warehouse' => (bool) $branch->is_warehouse,
            'is_pickup' => (bool) $branch->is_pickup,
            'working_hours' => $branch->workingHours(),
            'order' => $branch->order,
            'location_summary' => $branch->location_summary,
        ];
    }
}
