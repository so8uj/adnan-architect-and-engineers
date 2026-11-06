<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    public const SUPER = 'super-admin';

    protected $fillable = ['name', 'description', 'permissions'];

    protected function casts(): array
    {
        return ['permissions' => 'array', 'is_system' => 'boolean'];
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function isSuper(): bool
    {
        return $this->slug === self::SUPER;
    }

    public static function superAdmin(): self
    {
        return static::where('slug', self::SUPER)->firstOrFail();
    }
}
