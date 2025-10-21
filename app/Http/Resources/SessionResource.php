<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Domains\Planning\Models\Session;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Session
 */
class SessionResource extends JsonResource
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
            'planned_duration' => $this->planned_duration,
            'actual_duration' => $this->actual_duration,
            'status'       => $this->status,
            'started_at'   => $this->started_at?->format('Y-m-d H:i:s'),
            'completed_at' => $this->completed_at?->format('Y-m-d H:i:s'),
            'created_at'   => $this->created_at->format('Y-m-d H:i:s'),
            'template'     => $this->whenLoaded('template'),
            'blocks'       => SessionBlockResource::collection($this->whenLoaded('blocks')),
        ];
    }
}
