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
                        'task' => $this->task, // ビューに $task を渡す
                    ]);
    }
}
