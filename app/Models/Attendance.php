<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = ['user_id', 'date', 'clock_in', 'clock_out', 'shift_id', 'late_minutes', 'worked_minutes', 'note'];

    protected function casts(): array
    {
        return ['date' => 'date', 'clock_in' => 'datetime', 'clock_out' => 'datetime'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    /** Minutes late for a clock-in against a shift (0 inside the grace period). */
    public static function lateMinutes(?Shift $shift, Carbon $in): int
    {
        if (! $shift) {
            return 0;
        }
        $start = $in->copy()->setTimeFromTimeString($shift->start());
        $late = (int) $start->diffInMinutes($in, false);

        return $late > $shift->grace_minutes ? $late : 0;
    }

    public static function clockIn(User $user, ?Carbon $at = null): self
    {
        $at ??= now();
        $shift = $user->shiftOn($at);

        return static::create([
            'user_id' => $user->id, 'date' => $at->toDateString(), 'clock_in' => $at,
            'shift_id' => $shift?->id, 'late_minutes' => static::lateMinutes($shift, $at),
        ]);
    }

    public function clockOut(?Carbon $at = null): self
    {
        $at ??= now();
        $this->clock_out = $at;

        return $this->recalc();
    }

    /** Worked minutes from the two times (call after editing them). */
    public function recalc(): self
    {
        $this->worked_minutes = $this->clock_in && $this->clock_out && $this->clock_out->gt($this->clock_in)
            ? (int) $this->clock_in->diffInMinutes($this->clock_out) : 0;
        $this->save();

        return $this;
    }
}
