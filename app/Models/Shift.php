<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    protected $fillable = ['name', 'starts_at', 'ends_at', 'grace_minutes', 'work_days'];

    protected function casts(): array
    {
        return ['work_days' => 'array', 'grace_minutes' => 'integer'];
    }

    public function assignments()
    {
        return $this->hasMany(ShiftAssignment::class);
    }

    public function worksOn(Carbon $date): bool
    {
        return in_array($date->dayOfWeekIso, array_map('intval', (array) $this->work_days), true);
    }

    /** "09:00" - the stored time may carry seconds. */
    public function start(): string
    {
        return substr((string) $this->starts_at, 0, 5);
    }

    public function end(): string
    {
        return substr((string) $this->ends_at, 0, 5);
    }

    public function daysLabel(): string
    {
        $names = [1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat', 7 => 'Sun'];

        return collect((array) $this->work_days)->map(fn ($d) => $names[(int) $d] ?? '')->implode(', ');
    }
}
