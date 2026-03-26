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

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isOverdue(): bool
    {
        return $this->status === 'active' && Carbon::now()->gt($this->due_date);
    }

    public static function checkAndUpdateOverdue()
    {
        self::where('status', 'active')
            ->where('due_date', '<', Carbon::now())
            ->update(['status' => 'overdue']);
    }
}
