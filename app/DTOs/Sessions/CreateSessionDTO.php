<?php

declare(strict_types=1);

namespace App\DTOs\Sessions;

use App\Http\Requests\Session\StoreSessionRequest;

final readonly class CreateSessionDTO
{
    /**
     * @param array<CreateSessionBlockDTO> $blocks
     */
    public function __construct(
        public string $title,
        public ?string $description,
        public ?int  $template_id,
        public array $blocks,
        public string $session_mode = 'standard',
        public bool $auto_advance = false,
    ) {}

    public static function fromRequest(StoreSessionRequest $request): self
    {
        $validated = $request->validated();

        $blocks = array_map(
            fn(array $blockData) => CreateSessionBlockDTO::fromArray($blockData),
            $validated['blocks']
        );

        return new self(
            title       : $validated['title'],
            description : $validated['description'] ?? null,
            template_id : $validated['template_id'] ?? null,
            blocks      : $blocks,
            session_mode: $validated['session_mode'] ?? 'standard',
            auto_advance: $validated['auto_advance'] ?? false,
        );
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

    public function toArray(): array
    {
        return [
            'title'        => $this->title,
            'description'  => $this->description,
            'template_id'  => $this->template_id,
            'session_mode' => $this->session_mode,
            'auto_advance' => $this->auto_advance,
        ];
    }
}
