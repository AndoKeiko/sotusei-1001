<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Task;
use Illuminate\Support\Facades\Mail;
use App\Mail\TaskStartNotification;
use Carbon\Carbon;

class TaskNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function testTaskNotificationIsSent()
    {
        Mail::fake();

        $user = User::factory()->create(['email_verified_at' => now()]);
        
        $task = Task::factory()->create([
            'user_id' => $user->id,
            'start_date' => Carbon::today(),
            'start_time' => Carbon::now()->addMinutes(10)->format('H:i:s'),
            'last_notification_sent' => null,
        ]);

        $this->artisan('tasks:send-notifications');

        Mail::assertSent(TaskStartNotification::class, function ($mail) use ($user, $task) {
            return $mail->hasTo($user->email) && $mail->task->id === $task->id;
        });

        $this->assertNotNull($task->fresh()->last_notification_sent);
    }

    public function testTaskNotificationIsNotSentToUnverifiedUser()
    {
        Mail::fake();

        $user = User::factory()->create(['email_verified_at' => null]);
        
        $task = Task::factory()->create([
            'user_id' => $user->id,
            'start_date' => Carbon::today(),
            'start_time' => Carbon::now()->addMinutes(10)->format('H:i:s'),
            'last_notification_sent' => null,
        ]);

        $this->artisan('tasks:send-notifications');

        Mail::assertNotSent(TaskStartNotification::class);

        $this->assertNull($task->fresh()->last_notification_sent);
    }

    public function testTaskNotificationIsNotSentForPastTasks()
    {
        Mail::fake();

        $user = User::factory()->create(['email_verified_at' => now()]);
        
        $task = Task::factory()->create([
            'user_id' => $user->id,
            'start_date' => Carbon::yesterday(),
            'start_time' => Carbon::now()->subHours(1)->format('H:i:s'),
            'last_notification_sent' => null,
        ]);

        $this->artisan('tasks:send-notifications');

        Mail::assertNotSent(TaskStartNotification::class);

        $this->assertNull($task->fresh()->last_notification_sent);
    }
}