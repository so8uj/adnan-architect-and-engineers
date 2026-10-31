<?php

namespace App\Models;

use App\Models\Concerns\Translatable;
use Illuminate\Database\Eloquent\Model;

class HeroSlide extends Model
{
    use Translatable;

    protected $guarded = [];

    protected $casts = ['badge' => 'array', 'title1' => 'array', 'title2' => 'array', 'text' => 'array', 'cta_label' => 'array', 'chip_title' => 'array', 'chip_sub' => 'array', 'active' => 'boolean'];
}
