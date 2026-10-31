<?php

namespace App\Models;

use App\Models\Concerns\Translatable;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use Translatable;

    protected $guarded = [];

    protected $casts = ['title' => 'array', 'note' => 'array', 'active' => 'boolean'];
}
