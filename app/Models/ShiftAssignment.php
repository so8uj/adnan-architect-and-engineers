<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShiftAssignment extends Model
{
    protected $fillable = ['user_id', 'shift_id', 'starts_on'];

    protected function casts(): array
    {
        return ['starts_on' => 'date'];
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }
}
