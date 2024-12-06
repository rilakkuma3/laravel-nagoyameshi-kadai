<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Restaurant extends Model
{
    use HasFactory;

    public function category(){
        return $this->belongsToMany(Category::class, 'category_restaurants')->withTimestamps();
    }

    public function regular_holidays(){
        return $this->belongsToMany(RegularHoliday::class, 'category_restaurants')->withTimestamps();
    }
}

