<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Task;

class TaskReminder extends Notification
{
    use Queueable;

    public function __construct(Task $task)  // Task モデルを受け取るように変更
    {
        $this->task = $task;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
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
    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
