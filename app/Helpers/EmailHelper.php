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
            if (!$user || !$user->email) return;

            Mail::to($user->email)->send(
                new BorrowNotification($type, $user->name, $title, $body, $details)
            );
        } catch (\Exception $e) {
            Log::warning("Email send failed to user {$userId}: " . $e->getMessage());
        }
    }
}
