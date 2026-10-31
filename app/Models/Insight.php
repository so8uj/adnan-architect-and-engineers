<?php

namespace App\Models;

use App\Models\Concerns\HasSeo;
use App\Models\Concerns\Translatable;
use Illuminate\Database\Eloquent\Model;

class Insight extends Model
{
    use HasSeo, Translatable;

    protected $guarded = [];

    protected $casts = [
        'title' => 'array', 'category' => 'array', 'excerpt' => 'array', 'body' => 'array', 'read_time' => 'array',
        'published_at' => 'date', 'active' => 'boolean',
    ];
}
