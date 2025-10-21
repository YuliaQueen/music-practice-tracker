<?php

declare(strict_types=1);

namespace App\DTOs\Sessions;

use App\DTOs\Sessions\CreateSessionBlockDTO;
use App\Http\Requests\Session\StoreSessionRequest;

final readonly class CreateSessionDTO
{
    /**
     * @param array<CreateSessionBlockDTO> $blocks
     */
    public function __construct(
        public string $title,
        public ?string $description,
        public ?int $template_id,
        public array $blocks,
    ) {
    }

    public static function fromRequest(StoreSessionRequest $request): self
    {
        $validated = $request->validated();

        $blocks = array_map(
            fn(array $blockData) => CreateSessionBlockDTO::fromArray($blockData),
            $validated['blocks']
        );

        return new self(
            title: $validated['title'],
            description: $validated['description'] ?? null,
            template_id: $validated['template_id'] ?? null,
            blocks: $blocks,
        );
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'template_id' => $this->template_id,
        ];
    }

    /**
     * Get blocks as array
     *
     * @return array<array>
     */
    public function getBlocksArray(): array
    {
        return array_map(
            fn(CreateSessionBlockDTO $block) => $block->toArray(),
            $this->blocks
        );
    }
}
