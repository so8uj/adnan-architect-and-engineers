<?php

namespace App\Models;

use App\Models\Concerns\Translatable;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    use Translatable;

    protected $guarded = [];

    protected $casts = ['name' => 'array', 'role' => 'array', 'text' => 'array', 'is_sample' => 'boolean', 'active' => 'boolean'];
}
