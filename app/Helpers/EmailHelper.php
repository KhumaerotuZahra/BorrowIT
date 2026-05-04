<?php

namespace App\Helpers;

use App\Mail\BorrowNotification;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailHelper
{
    /**
     * Send email notification to a user.
     * Silently fails if email sending fails (won't break the app).
     */
    public static function sendBorrowEmail(
        int $userId,
        string $type,
        string $title,
        string $body,
        array $details = []
    ): void {
        try {
            $user = User::find($userId);
            if (!$user || !$user->email) {
                Log::warning("Email skipped: user {$userId} not found or no email.");
                return;
            }

            Log::info("Sending email to {$user->email} [{$type}]: {$title}");

            Mail::to($user->email)->send(
                new BorrowNotification($type, $user->name, $title, $body, $details)
            );

            Log::info("Email sent successfully to {$user->email}");
        } catch (\Exception $e) {
            Log::error("Email send failed to user {$userId} ({$type}): " . $e->getMessage());
        }
    }
}
