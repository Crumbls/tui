<?php

declare(strict_types=1);

namespace Crumbls\Tui\Events;

use Crumbls\Tui\Contracts\EventInterface;

/**
 * Event fired on any user activity (input, mouse, etc.)
 */
class UserActivityEvent extends Event implements EventInterface
{
    // Optionally, include details about the activity
}
