<?php

declare(strict_types=1);

namespace App\DTOs\Exercises;

use App\Http\Requests\Exercise\UpdateExerciseRequest;

final readonly class UpdateExerciseDTO
{
    public function __construct(
        public string $title,
        public ?string $description,
        public string $type,
        public int $planned_duration,
        public ?string $scheduled_for,
    ) {
    }

    public static function fromRequest(UpdateExerciseRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            title: $validated['title'],
            description: $validated['description'] ?? null,
            type: $validated['type'],
            planned_duration: (int) $validated['planned_duration'],
            scheduled_for: $validated['scheduled_for'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'planned_duration' => $this->planned_duration,
            'scheduled_for' => $this->scheduled_for,
        ];
    }
}
