<?php

namespace App\Helpers;

use App\Mail\BorrowNotification;
use App\Models\NotificationSetting;
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

            // Normalize short type aliases to canonical notification types
            $canonical = match ($type) {
                'approved'    => 'borrow_approved',
                'rejected'    => 'borrow_rejected',
                'cancelled'   => 'borrow_cancelled',
                'handover'    => 'borrow_handover',
                'returned'    => 'borrow_returned',
                'overdue'     => 'borrow_overdue',
                default       => $type,
            };

            $role = $user->isAdmin() ? 'admin' : 'user';
            if (!NotificationSetting::isEmailEnabled($canonical, $role)) {
                Log::info("Email skipped (disabled by setting) for {$user->email} [{$canonical}/{$role}].");
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
