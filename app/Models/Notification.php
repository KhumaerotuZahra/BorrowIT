<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    /**
     * Compute target URL when this notification is clicked.
     */
    public function getLinkAttribute(): string
    {
        $isAdmin = optional($this->user)->isAdmin() ?? false;

        if ($isAdmin) {
            return match ($this->type) {
                'new_request', 'borrow_request', 'borrow_approved', 'borrow_rejected', 'borrow_cancelled'
                    => route('admin.borrow-requests.index'),
                'borrow_handover', 'borrow_returned', 'borrow_overdue'
                    => route('admin.active-borrows.index'),
                default => route('admin.notifications.index'),
            };
        }

        return match ($this->type) {
            'borrow_overdue' => route('user.borrowings.index', ['status' => 'overdue']),
            default          => route('user.borrowings.index'),
        };
    }

    public static function send($userId, $type, $title, $message, $data = null)
    {
        // Only create web notification — email is handled by EmailHelper
        $notification = self::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
        ]);

        return $notification;
    }
}
