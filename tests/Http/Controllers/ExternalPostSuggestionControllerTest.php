<?php

namespace Tests\Http\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Models\ExternalPost;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use App\Mail\ExternalPostSuggestedMail;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\BlogPostController;
use Illuminate\Support\Facades\Notification;
use App\Http\Controllers\ExternalPostSuggestionController;

class ExternalPostSuggestionControllerTest extends TestCase
{
    /** @test */
    public function external_post_can_be_submitted()
    {
        $this->withoutExceptionHandling();

        $user = User::factory()->create();

        Mail::fake();

        $this
            ->post(action(ExternalPostSuggestionController::class), [
                'title' => 'test',
                'url' => 'https://spatie.be',
            ])
            ->assertRedirect(action([BlogPostController::class, 'index']))
            ->assertSessionHas('laravel_flash_message')
        ;

        Mail::assertSent(function (ExternalPostSuggestedMail $mail) use ($user) {
            return $mail->to[0]['address'] === $user->email;
        });

        $this->assertDatabaseHas(ExternalPost::class, [
            'title' => 'test',
            'url' => 'https://spatie.be',
        ]);
    }
}
