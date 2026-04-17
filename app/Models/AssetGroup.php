<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AssetGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_code',
        'group_name',
    ];

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }

    public function borrowings()
    {
        return $this->hasMany(Borrowing::class);
    }

    public function totalAvailableStock(): int
    {
        return $this->assets()->sum('available_stock');
    }
}
