<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Models\Permission;

class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use HasRoles;

    protected $guard_name = 'web';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
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
        ];
    }

    // public function hasPermissionToCustom($permissionName)
    // {
    //     // Si el permiso está revocado explícitamente
    //     $permission = Permission::where('name', $permissionName)->first();

    //     if ($permission && UserRevokedPermission::where('user_id', $this->id)
    //         ->where('permission_id', $permission->id)
    //         ->exists()) {
    //         return false;
    //     }

    //     // Caso normal: permisos por rol o usuario (Spatie)
    //     return $this->hasPermissionTo($permissionName);
    // }

    // public function revokePermission($permissionName)
    // {
    //     $permission = Permission::where('name', $permissionName)->first();
    //     if ($permission) {
    //         UserRevokedPermission::firstOrCreate([
    //             'user_id' => $this->id,
    //             'permission_id' => $permission->id
    //         ]);
    //     }
    // }

    // public function allowPermission($permissionName)
    // {
    //     $permission = Permission::where('name', $permissionName)->first();
    //     if ($permission) {
    //         UserRevokedPermission::where('user_id', $this->id)
    //             ->where('permission_id', $permission->id)
    //             ->delete();
    //     }
    // }

    public function branchOffices()
    {
        return $this->belongsToMany(
            Planta::class,
            'branch_office_user',     
            'user_id',                
            'branch_office_id'        
        );
    }
}
