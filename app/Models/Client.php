<?php

namespace App\Models;

use App\Models\Concerns\Translatable;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use Translatable;

    protected $guarded = [];

    protected $casts = ['name' => 'array', 'show_on_home' => 'boolean', 'active' => 'boolean'];
}
