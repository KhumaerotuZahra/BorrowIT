<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Borrowing extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'asset_id',
        'asset_group_id',
        'quantity',
        'borrow_date',
        'due_date',
        'return_date',
        'status',
        'approved_date',
        'approved_by',
        'handover_by',
        'handover_date',
        'handover_notes',
        'return_pic',
        'return_notes',
        'purpose',
        'notes',
        'parent_borrowing_id',
    ];

    protected $casts = [
        'borrow_date' => 'date',
        'due_date' => 'date',
        'return_date' => 'date',
        'approved_date' => 'datetime',
        'handover_date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function assetGroup()
    {
        return $this->belongsTo(AssetGroup::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function parentBorrowing()
    {
        return $this->belongsTo(Borrowing::class, 'parent_borrowing_id');
    }

    public function childBorrowings()
    {
        return $this->hasMany(Borrowing::class, 'parent_borrowing_id');
    }

    public function isOverdue(): bool
    {
        return $this->status === 'active' && Carbon::now()->gt($this->due_date);
    }

    public static function checkAndUpdateOverdue()
    {
        $overdueBorrowings = self::with(['user', 'asset'])
            ->where('status', 'active')
            ->where('due_date', '<', Carbon::now())
            ->get();

        foreach ($overdueBorrowings as $borrow) {
            $borrow->update(['status' => 'overdue']);

            // Notify user
            Notification::send(
                $borrow->user_id,
                'borrow_overdue',
                'Overdue: Please Return Asset',
                "Your borrowed asset \"{$borrow->asset?->asset_name}\" is overdue (due: {$borrow->due_date->format('d M Y')}). Please return it immediately.",
                ['borrowing_id' => $borrow->id]
            );

            // Notify all admins
            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                Notification::send(
                    $admin->id,
                    'borrow_overdue',
                    'Overdue Borrowing Alert',
                    "Asset \"{$borrow->asset?->asset_name}\" borrowed by {$borrow->user->name} is overdue (due: {$borrow->due_date->format('d M Y')}).",
                    ['borrowing_id' => $borrow->id]
                );
            }
        }
    }
}
