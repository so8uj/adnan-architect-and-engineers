<?php

namespace App\Models;

use App\Models\Concerns\Translatable;
use Illuminate\Database\Eloquent\Model;

class WhyItem extends Model
{
    use Translatable;

    protected $guarded = [];

    protected $casts = ['title' => 'array', 'description' => 'array', 'active' => 'boolean'];
}
