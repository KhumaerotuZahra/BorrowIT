<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    public static function send($userId, $type, $title, $message, $data = null)
    {
        $notification = self::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
        ]);

        // Only send email if SMTP is configured (not in log mode)
        if (config('mail.default') === 'smtp') {
            try {
                $user = User::find($userId);
                if ($user && $user->email) {
                    Mail::send('emails.notification', [
                        'notifData' => [
                            'name' => $user->name,
                            'message' => $message,
                            'action_url' => url($user->isAdmin() ? '/admin/dashboard' : '/user/dashboard'),
                            'action_text' => 'Open BorrowIT',
                        ],
                    ], function ($mail) use ($user, $title) {
                        $mail->to($user->email);
                        $mail->subject('BorrowIT - ' . $title);
                    });
                }
            } catch (\Exception $e) {
                Log::warning('Failed to send notification email: ' . $e->getMessage());
            }
        }

        return $notification;
    }
}
