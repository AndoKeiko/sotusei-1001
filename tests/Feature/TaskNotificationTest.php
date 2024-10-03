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

    protected function createTestTask($startTime, $user)
    {
        return Task::factory()->create([
            'user_id' => $user->id,
            'start_date' => Carbon::today(),
            'start_time' => $startTime,
            'last_notification_sent' => null,
        ]);
    }
    
    public function testTaskNotificationIsSent()
    {
        Mail::fake();
        $user = User::factory()->create(['email_verified_at' => now()]);
        $task = $this->createTestTask(Carbon::now()->addMinutes(10)->format('H:i'), $user);
    
        $this->artisan('tasks:send-notifications');
        
        Mail::assertSent(TaskStartNotification::class, function ($mail) use ($user, $task) {
            return $mail->hasTo($user->email) && $mail->task->id === $task->id;
        });
        
        $this->assertNotNull($task->fresh()->last_notification_sent);
    }
    



}