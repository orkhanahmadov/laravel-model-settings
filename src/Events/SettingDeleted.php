<?php

declare(strict_types=1);

namespace Orkhanahmadov\ModelSettings\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Orkhanahmadov\ModelSettings\Models\Setting;

class SettingDeleted
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public Setting $setting)
    {
    }
}
