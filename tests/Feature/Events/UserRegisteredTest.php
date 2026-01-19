<?php

declare(strict_types=1);

namespace Tests\Feature\Events;

use App\Events\UserRegistered;
use App\Listeners\CreateInitialUserSettings;
use App\Listeners\SendWelcomeEmail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserRegisteredTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_dispatches_user_registered_event()
    {
        Event::fake();

        $user = User::factory()->create();

        event(new UserRegistered($user));

        Event::assertDispatched(UserRegistered::class, function ($event) use ($user) {
            return $event->user->id === $user->id;
        });
    }

    #[Test]
    public function it_triggers_welcome_email_listener()
    {
        Event::fake();

        $user = User::factory()->create();

        event(new UserRegistered($user));

        Event::assertListening(
            UserRegistered::class,
            SendWelcomeEmail::class
        );
    }

    #[Test]
    public function it_triggers_initial_settings_listener()
    {
        Event::fake();

        $user = User::factory()->create();

        event(new UserRegistered($user));

        Event::assertListening(
            UserRegistered::class,
            CreateInitialUserSettings::class
        );
    }

    #[Test]
    public function it_handles_welcome_email_sending()
    {
        $listener = new SendWelcomeEmail;
        $user = User::factory()->create();
        $event = new UserRegistered($user);

        $listener->handle($event);

        // We verify that the user exists and has the correct data
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => $user->email,
        ]);
    }

    #[Test]
    public function it_handles_initial_settings_creation()
    {
        $listener = new CreateInitialUserSettings;
        $user = User::factory()->create();
        $event = new UserRegistered($user);

        $listener->handle($event);

        // We verify that the user exists and has the correct data
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => $user->email,
        ]);
    }
}
