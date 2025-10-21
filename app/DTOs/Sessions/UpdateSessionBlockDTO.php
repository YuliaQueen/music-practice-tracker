<?php

declare(strict_types=1);

namespace App\DTOs\Sessions;

use App\Http\Requests\Session\UpdateSessionBlockRequest;

final readonly class UpdateSessionBlockDTO
{
    public function __construct(
        public ?string $status,
        public ?int $actual_duration,
        public ?string $started_at,
        public ?string $completed_at,
        public ?string $notes,
        public ?int $planned_duration,
    ) {}

    public static function fromRequest(UpdateSessionBlockRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            status          : $validated['status'] ?? null,
            actual_duration : isset($validated['actual_duration']) ? (int)$validated['actual_duration'] : null,
            started_at      : $validated['started_at'] ?? null,
            completed_at    : $validated['completed_at'] ?? null,
            notes           : $validated['notes'] ?? null,
            planned_duration: isset($validated['planned_duration']) ? (int)$validated['planned_duration'] : null,
        );
    }

    public function toArray(): array
    {
        $data = [];

        if ($this->status !== null) {
            $data['status'] = $this->status;
        }
        if ($this->actual_duration !== null) {
            $data['actual_duration'] = $this->actual_duration;
        }
        if ($this->started_at !== null) {
            $data['started_at'] = $this->started_at;
        }
        if ($this->completed_at !== null) {
            $data['completed_at'] = $this->completed_at;
        }
        if ($this->notes !== null) {
            $data['notes'] = $this->notes;
        }
        if ($this->planned_duration !== null) {
            $data['planned_duration'] = $this->planned_duration;
        }

        return $data;
    }
}
