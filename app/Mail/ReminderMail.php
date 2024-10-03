<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Task;

class ReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $task;

    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    public function build()
    {
        return $this->subject('Task Starting Soon')
                    ->view('emails.reminder') // ビューのパス
                    ->with([
                        'taskName' => $this->task->name,
                        'startTime' => $this->task->start_time,
                        'description' => $this->task->description,
                    ]);
    }
}
