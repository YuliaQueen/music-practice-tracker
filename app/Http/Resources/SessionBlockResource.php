<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Domains\Planning\Models\SessionBlock;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin SessionBlock
 */
class SessionBlockResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'title'        => $this->title,
            'description'  => $this->description,
            'type'         => $this->type,
            'planned_duration' => $this->planned_duration,
            'actual_duration' => $this->actual_duration,
            'status'       => $this->status,
            'sort_order'   => $this->sort_order,
            'started_at'   => $this->started_at?->format('Y-m-d H:i:s'),
            'completed_at' => $this->completed_at?->format('Y-m-d H:i:s'),
        ];
    }
}
