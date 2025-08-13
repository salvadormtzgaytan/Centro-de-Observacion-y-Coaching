<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;

class LogSuccessfulLogin
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Login $event)
    {
        // Ensure $event->user is an instance of App\Models\User
        $user = $event->user instanceof \App\Models\User
            ? $event->user
            : \App\Models\User::find($event->user->id ?? null);

        if ($user) {
            $user->last_login_at = now();
            $user->save();
        }
    }
}
