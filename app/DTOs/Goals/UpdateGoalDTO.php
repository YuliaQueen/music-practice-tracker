<?php

declare(strict_types=1);

namespace App\DTOs\Goals;

use App\Http\Requests\Goal\UpdateGoalRequest;

final readonly class UpdateGoalDTO
{
    public function __construct(
        public string $title,
        public ?string $description,
        public string $type,
        public array $target,
        public string $start_date,
        public ?string $end_date,
        public bool $is_active,
    ) {
    }

    public static function fromRequest(UpdateGoalRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            title: $validated['title'],
            description: $validated['description'] ?? null,
            type: $validated['type'],
            target: $validated['target'],
            start_date: $validated['start_date'],
            end_date: $validated['end_date'] ?? null,
            is_active: $validated['is_active'] ?? true,
        );
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'target' => $this->target,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'is_active' => $this->is_active,
        ];
    }
}
