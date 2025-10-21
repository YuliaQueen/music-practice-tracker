<?php

declare(strict_types=1);

namespace App\DTOs\Sessions;

final readonly class CreateSessionBlockDTO
{
    public function __construct(
        public string $title,
        public ?string $description,
        public int $duration,
        public string $type,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'],
            description: $data['description'] ?? null,
            duration: (int) $data['duration'],
            type: $data['type'],
        );
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'planned_duration' => $this->duration,
            'type' => $this->type,
        ];
    }
}
