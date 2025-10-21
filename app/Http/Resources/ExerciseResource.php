<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Domains\Planning\Models\Exercise;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Exercise
 */
class ExerciseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'title'         => $this->title,
            'description'   => $this->description,
            'type'          => $this->type,
            'type_label'    => $this->type_label,
            'type_icon'     => $this->type_icon,
            'planned_duration' => $this->planned_duration,
            'actual_duration' => $this->actual_duration,
            'status'        => $this->status,
            'status_label'  => $this->status_label,
            'scheduled_for' => $this->scheduled_for?->format('Y-m-d H:i:s'),
            'started_at'    => $this->started_at?->format('Y-m-d H:i:s'),
            'completed_at'  => $this->completed_at?->format('Y-m-d H:i:s'),
            'created_at'    => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
