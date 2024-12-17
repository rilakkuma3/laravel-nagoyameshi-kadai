<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    // 1つの予約は1人のユーザーに属する（1対多）
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 1つの予約は1つの店舗に属する（1対多）
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
}
