<?php

use Illuminate\Support\Facades\Notification;
use App\Models\User;
use App\Models\EvaluationSession;
use App\Notifications\PendingSignatureNotification;

beforeEach(function () {
    Notification::fake();
});

it('envía la notificación de firma pendiente por correo electrónico', function () {
    $user = User::factory()->create([
        'email' => 'prueba@example.com',
    ]);
    $session = EvaluationSession::factory()->create([
        'participant_id' => $user->id,
    ]);
    $url = 'https://local.coaching/firmar/test-token';

    $user->notify(new PendingSignatureNotification($session, $url));

    Notification::assertSentTo(
        $user,
        PendingSignatureNotification::class,
        function ($notification, $channels) use ($session, $url) {
            return $notification->session->id === $session->id
                && $notification->url === $url
                && in_array('mail', $channels);
        }
    );
});

it('incluye el subject y la url en el correo', function () {
    $user = User::factory()->create();
    $session = EvaluationSession::factory()->create([
        'participant_id' => $user->id,
    ]);
    $url = 'https://local.coaching/firmar/test-token';

    $notification = new PendingSignatureNotification($session, $url);
    $mailMessage = $notification->toMail($user);

    expect($mailMessage->subject)->toContain('firma');
    expect($mailMessage->viewData['url'] ?? null)->toBe($url);
});