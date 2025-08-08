<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mail;
use App\Models\User;
use App\Models\EmailLogs;
use Illuminate\Support\Str;
use Log;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $details;

    public function __construct($details)
    {
        $this->details = $details;
    }

    public function handle()
    {
        $emailLog = EmailLogs::create([
            'email' => $this->details['email'],
            'subject' => $this->details['mailable_data']->subject,
            'content' => 'Reset your password using the following link: ' . $this->details['mailable_data']->resetLink,
            'status' => 'pending',
        ]);

        try {
            $email = $this->details['mailable_data'];
            $emailaddress = $this->details['email'];

            if ($emailaddress) {
                if ($email) {
                    Mail::to($emailaddress)->send($email);

                    $emailLog->update(['status' => 'sent']);
                } else {
                    Log::error('Mailable data is null or not properly instantiated.');
                    $emailLog->update(['status' => 'failed', 'error_message' => 'Mailable data is null or not properly instantiated.']);
                }
            } else {
                Log::error('No email address provided.');
                $emailLog->update(['status' => 'failed', 'error_message' => 'No email address provided.']);
            }
        } catch (\Throwable $e) {
            Log::error('Failed to send email: ' . $e->getMessage());
            report($e);
            $emailLog->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
        }
    }
}
