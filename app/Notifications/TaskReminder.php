<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Task;
use Illuminate\Support\Facades\Http;

class TaskReminder extends Notification
{
    use Queueable;

    protected $task;

    public function __construct(Task $task)  // Task モデルを受け取るように変更
    {
        $this->task = $task;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'line'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->line('Task Reminder')
            ->line('Your task "' . $this->task->name . '" is scheduled to start soon.')
            ->line('Start time: ' . $this->task->start_time)
            ->action('View Task', url('/tasks/' . $this->task->id))
            ->line('Thank you for using our application!');
    }

    public function toLine($notifiable)
    {
        $lineApiUrl = 'https://api.line.me/v2/bot/message/push';

        $lineAccessToken = $notifiable->line_access_token;

        $messageData = [
            'to' => $lineAccessToken,
            'messages' => [
                [
                    'type' => 'text',
                    'text' => "Task Reminder: Your task \"" . $this->task->name . "\" is starting soon.\nStart time: " . $this->task->start_time
                ]
            ]
        ];

        Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.line.channel_token'),
            'Content-Type' => 'application/json',
        ])->post($lineApiUrl, $messageData);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'task_name' => $this->task->name,
            'start_time' => $this->task->start_time,
        ];
    }
}
