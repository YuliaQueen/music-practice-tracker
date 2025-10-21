<?php

declare(strict_types=1);

namespace App\Events;

use App\Domains\Planning\Models\SessionBlock;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SessionBlockCompleted
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public SessionBlock $sessionBlock
    ) {
    }
}
