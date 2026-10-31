<?php

namespace App\Models;

use App\Models\Concerns\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SeoMeta extends Model
{
    use Translatable;

    protected $table = 'seo_metas';

    protected $guarded = [];

    protected $casts = [
        'meta_title' => 'array', 'meta_description' => 'array', 'meta_keywords' => 'array',
        'og_title' => 'array', 'og_description' => 'array',
        'robots_index' => 'boolean', 'robots_follow' => 'boolean',
    ];

    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }
}
