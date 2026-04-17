<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'asset_group_id',
        'asset_number',
        'asset_name',
        'total_stock',
        'available_stock',
    ];

    public function assetGroup()
    {
        return $this->belongsTo(AssetGroup::class);
    }

    public static function generateAssetId(): string
    {
        $year = date('y');
        $prefix = "BPI-{$year}-";

        $lastAsset = self::where('asset_id', 'like', $prefix . '%')
            ->orderBy('asset_id', 'desc')
            ->first();

        if ($lastAsset) {
            $lastNumber = intval(substr($lastAsset->asset_id, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    public function borrowings()
    {
        return $this->hasMany(Borrowing::class);
    }
}
