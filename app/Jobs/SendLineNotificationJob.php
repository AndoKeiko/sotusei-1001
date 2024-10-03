<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use App\Models\User;

class SendLineNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userId;
    protected $message;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($userId, $message)
    {
        $this->userId = $userId;
        $this->message = $message;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user = User::find($this->userId);

        if ($user && $user->line_user_id) {
            Http::withHeaders([
                'Authorization' => 'Bearer ' . env('LINE_CHANNEL_ACCESS_TOKEN'),
            ])->post('https://api.line.me/v2/bot/message/push', [
                'to' => $user->line_user_id,
                'messages' => [
                    [
                        'type' => 'text',
                        'text' => $this->message,
                    ],
                ],
            ]);
        }
    }
}
