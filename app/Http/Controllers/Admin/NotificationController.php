<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.notifications.index', compact('notifications'));
    }

    public function userIndex()
    {
        $notifications = Notification::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('user.notifications.index', compact('notifications'));
    }

    public function markRead(Notification $notification)
    {
        $notification->update(['read_at' => now()]);
        return back()->with('success', 'Notification marked as read.');
    }

    /**
     * Mark a notification as read and redirect to its target page.
     */
    public function open(Notification $notification)
    {
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }
        if (!$notification->isRead()) {
            $notification->update(['read_at' => now()]);
        }
        return redirect($notification->link);
    }

    public function markAllRead()
    {
        Notification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back()->with('success', 'All notifications marked as read.');
    }

    public function getUnreadCount()
    {
        $count = Notification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->count();

        return response()->json(['count' => $count]);
    }

    public function getLatest()
    {
        $notifications = Notification::with('user')
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($n) {
                return [
                    'id'         => $n->id,
                    'type'       => $n->type,
                    'title'      => $n->title,
                    'message'    => $n->message,
                    'read_at'    => $n->read_at,
                    'created_at' => $n->created_at,
                    'open_url'   => route('notifications.open', $n),
                ];
            });

        return response()->json($notifications);
    }
}
