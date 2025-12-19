<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use App\Models\Role;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'active_school_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

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
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function schools()
    {
        return $this->belongsToMany(School::class, 'school_user')
            ->withPivot('role_id', 'is_active')
            ->withTimestamps();
    }

    public function ownedSchools()
    {
        return $this->schools()->wherePivot('role_id', Role::where('name', 'admin')->first()->id);
    }

    public function activeSchool()
    {
        return $this->belongsTo(School::class, 'active_school_id');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class)
            ->withPivot('school_id');
    }

    public function setActiveSchool(School $school): void
    {
        $this->active_school_id = $school->id;
        $this->save();
    }

    public function roleForActiveSchool(): ?Role
    {
        if (! $this->active_school_id) {
            return null;
        }

        return Role::find(
            $this->schools()
                ->where('schools.id', $this->active_school_id)
                ->first()
                ?->pivot
                ?->role_id
        );
    }

    public function hasRole(string $roleName): bool
    {
        $role = $this->roleForActiveSchool();

        return $role && $role->name === $roleName;
    }

    public function hasAnyRole(array $roles): bool
    {
        $role = $this->roleForActiveSchool();

        return $role && in_array($role->name, $roles, true);
    }

    public function permissionsForActiveSchool()
    {
        $school = $this->activeSchool;

        if (! $school) {
            return collect();
        }

        $rolePermissions = $this
            ->roleForActiveSchool()
            ?->permissions
            ?? collect();

        $directPermissions = $this
            ->permissions()
            ->wherePivot('school_id', $school->id)
            ->get();

        return $rolePermissions
            ->merge($directPermissions)
            ->unique('id');
    }

    public function hasPermission(string $permission): bool
    {
        if (! $this->active_school_id) {
            return false;
        }

        // Explicit per-user override
        $override = \DB::table('permission_school_user')
            ->where('school_id', $this->active_school_id)
            ->where('user_id', $this->id)
            ->where('permission_id', Permission::where('name', $permission)->value('id'))
            ->first();

        if ($override) {
            return (bool) $override->allowed;
        }

        /*return $this
            ->permissionsForActiveSchool()
            ->contains('name', $permission);*/

        // Fallback to role permissions
        return $this->roleForActiveSchool()
            ?->permissions()
            ->where('name', $permission)
            ->exists() ?? false;
            }


}
