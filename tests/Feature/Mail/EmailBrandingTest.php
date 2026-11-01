<?php

use App\Mail\ClaimConfirmationMail;
use App\Mail\ClaimNotificationMail;
use App\Mail\ExchangeRevealReminderMail;
use App\Mail\ExchangeWishlistNudgeMail;
use App\Mail\FriendDigestMail;
use App\Mail\GaveTwiceMail;
use App\Mail\GiftExchangeInviteMail;
use App\Mail\InactiveAccountWarningMail;
use App\Mail\ListInvitationMail;
use App\Mail\ReservationAlertMail;
use App\Mail\ReservationLinkMail;
use App\Mail\ReservationMail;
use App\Mail\WelcomeEmail;
use App\Models\Claim;
use App\Models\GiftExchange;
use App\Models\GiftExchangeParticipant;
use App\Models\ListInvitation;
use App\Models\ReservationAlert;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Mail\MailManager;
use Illuminate\Mail\Markdown;
use Illuminate\Notifications\Channels\MailChannel;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\Queue;

describe('email branding', function () {
    it('renders every mailable and notification without framework branding when APP_NAME is unset', function (string $locale) {
        $environmentName = $_ENV['APP_NAME'] ?? null;
        $serverName = $_SERVER['APP_NAME'] ?? null;
        $processName = getenv('APP_NAME');
        unset($_ENV['APP_NAME'], $_SERVER['APP_NAME']);
        putenv('APP_NAME');

        try {
            expect(Env::get('APP_NAME'))->toBeNull();
            $appConfig = require config_path('app.php');
            expect($appConfig['name'])->toBe('GiveTwice');
            config(['app.name' => $appConfig['name'], 'mail.default' => 'array']);
            app()->setLocale($locale);
            Queue::fake();

            $user = User::factory()->create(['locale_preference' => $locale]);
            $claim = Claim::factory()->anonymous()->confirmed()->create();
            $gift = $claim->gift;
            $gift->update(['title' => 'Updated gift']);
            $alert = ReservationAlert::sole();
            $exchange = GiftExchange::factory()->create(['locale' => $locale]);
            $participant = GiftExchangeParticipant::factory()->create(['exchange_id' => $exchange->id]);
            $invitation = ListInvitation::factory()->create(['invitee_id' => $user->id]);
            $digest = collect([[
                'list' => $invitation->list,
                'added_gifts' => collect([$gift]),
                'removed_gifts' => collect(),
            ]]);
            $mailables = [
                ClaimConfirmationMail::class => new ClaimConfirmationMail($claim, $locale),
                ClaimNotificationMail::class => new ClaimNotificationMail($claim),
                ExchangeRevealReminderMail::class => new ExchangeRevealReminderMail($participant, $exchange),
                ExchangeWishlistNudgeMail::class => new ExchangeWishlistNudgeMail($participant, $exchange),
                FriendDigestMail::class => new FriendDigestMail($user, $digest),
                GaveTwiceMail::class => new GaveTwiceMail($claim, $gift),
                GiftExchangeInviteMail::class => new GiftExchangeInviteMail($participant, $exchange),
                InactiveAccountWarningMail::class => new InactiveAccountWarningMail($user),
                ListInvitationMail::class => new ListInvitationMail($invitation),
                ReservationAlertMail::class => new ReservationAlertMail($alert),
                ReservationLinkMail::class => new ReservationLinkMail($user->email, $locale),
                ReservationMail::class => new ReservationMail($claim, $locale),
                WelcomeEmail::class => new WelcomeEmail($user),
            ];
            expect(array_map(fn (string $path): string => 'App\\Mail\\'.basename($path, '.php'), glob(app_path('Mail/*.php'))))
                ->toEqualCanonicalizing(array_keys($mailables));

            $manager = new MailManager(app());
            $channel = new MailChannel($manager, app(Markdown::class));
            $messages = [];
            foreach ($mailables as $mail) {
                $messages[] = $mail->locale($locale)->to($user)->send($manager->mailer('array'));
            }
            foreach ([new ResetPassword('reset-token'), new VerifyEmail] as $notification) {
                $messages[] = $channel->send($user, $notification);
            }

            foreach ($messages as $sent) {
                $message = $sent->getSymfonySentMessage()->getOriginalMessage();
                expect($message->getSubject())->not->toContain('Laravel');
                expect($message->getHtmlBody())->not->toBeEmpty()->not->toContain('Laravel')->toContain('GiveTwice ❤️');
                if ($message->getTextBody() !== null) {
                    expect($message->getTextBody())->not->toBeEmpty()->not->toContain('Laravel')->toContain('GiveTwice ❤️');
                }
                foreach ($message->getFrom() as $from) {
                    expect($from->getName())->not->toContain('Laravel');
                }
            }
        } finally {
            if ($environmentName !== null) {
                $_ENV['APP_NAME'] = $environmentName;
            }
            if ($serverName !== null) {
                $_SERVER['APP_NAME'] = $serverName;
            }
            if ($processName !== false) {
                putenv('APP_NAME='.$processName);
            }
        }
    })->with(['en', 'nl', 'fr']);
});
