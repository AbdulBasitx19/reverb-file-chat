<?php

namespace App\Listeners;

use App\Events\UserStatusUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;

// ✅ Laravel 11 Attribute: Automatically event ko listener se jorta hai
#[\Illuminate\Foundation\Attributes\ListensTo(UserStatusUpdated::class)]
class UpdateUserLastSeen implements ShouldQueue
{
    public function __construct()
    {
        //
    }

    public function handle(UserStatusUpdated $event): void
    {
        // User ka last_seen current time par update karein
        $event->user->update([
            'last_seen' => now(),
        ]);
    }
}