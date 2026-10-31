<?php

namespace App\Models;

use App\Models\Concerns\Translatable;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    use Translatable;

    protected $guarded = [];

    protected $casts = ['question' => 'array', 'answer' => 'array', 'active' => 'boolean'];
}
