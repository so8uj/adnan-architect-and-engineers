<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// is_developer is deliberately NOT fillable: nobody can promote themselves through a form.
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /** A new person needs attendance unless HR switches it off. */
    protected $attributes = ['attendance_required' => true];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_developer' => 'boolean',
            'attendance_required' => 'boolean',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function shiftAssignments()
    {
        return $this->hasMany(ShiftAssignment::class);
    }

    /** The shift this person works on a given day (latest assignment that has started), or null. */
    public function shiftOn(?\Carbon\Carbon $date = null): ?Shift
    {
        $date ??= now();

        return $this->shiftAssignments()->with('shift')->whereDate('starts_on', '<=', $date->toDateString())
            ->orderByDesc('starts_on')->orderByDesc('id')->first()?->shift;
    }

    /** Super Admin role or the developer account: passes every check. */
    public function isSuper(): bool
    {
        return $this->is_developer || $this->role?->isSuper() === true;
    }

    /** "<module>.view" / "<module>.manage". Manage includes view. No role = no permission. */
    public function hasPermission(string $permission): bool
    {
        if ($this->isSuper()) {
            return true;
        }
        $have = (array) ($this->role?->permissions ?? []);
        if (in_array($permission, $have, true)) {
            return true;
        }

        return str_ends_with($permission, '.view') && in_array(substr($permission, 0, -5).'.manage', $have, true);
    }

    /** Users the given viewer is allowed to see. The developer account is invisible to everybody else. */
    public function scopeVisibleTo(Builder $query, ?self $viewer): Builder
    {
        return $viewer?->is_developer ? $query : $query->where('is_developer', false);
    }
}
