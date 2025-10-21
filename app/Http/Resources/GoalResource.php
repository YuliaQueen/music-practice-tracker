<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Domains\Goals\Models\Goal;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Goal
 */
class GoalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'type_label' => $this->getTypeLabel(),
            'type_icon' => $this->getTypeIcon(),
            'type_color' => $this->getTypeColor(),
            'target' => $this->target,
            'progress' => $this->progress,
            'progress_percentage' => $this->getProgressPercentage(),
            'remaining' => $this->getRemaining(),
            'current_value' => $this->getCurrentValue(),
            'target_value' => $this->getTargetValue(),
            'start_date' => $this->start_date->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'is_active' => $this->is_active,
            'is_completed' => $this->is_completed,
            'completed_at' => $this->completed_at?->format('Y-m-d H:i'),
            'created_at' => $this->created_at->format('Y-m-d H:i'),
        ];
    }
}
