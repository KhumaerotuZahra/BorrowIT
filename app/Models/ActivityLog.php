<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'asset_id',
        'asset_name',
        'description',
    ];

    // optional tapi bagus untuk kejelasan
    public $timestamps = true;

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Relation ke user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Helper untuk log activity biar ga nulis berulang
     */
    public static function log($action, $asset = null, $description = null)
    {
        return self::create([
            'user_id'     => auth()->id(),
            'action'      => $action,
            'asset_id'    => $asset->asset_id ?? null,
            'asset_name'  => $asset->asset_name ?? null,
            'description' => $description,
        ]);
    }
}