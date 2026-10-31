<?php

namespace App\Models;

use App\Models\Concerns\HasSeo;
use App\Models\Concerns\Translatable;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasSeo, Translatable;

    protected $guarded = [];

    protected $casts = [
        'title' => 'array', 'category' => 'array', 'tagline' => 'array', 'summary' => 'array',
        'meta' => 'array', 'description' => 'array', 'highlights' => 'array', 'images' => 'array', 'active' => 'boolean',
    ];
}
