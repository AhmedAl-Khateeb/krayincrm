<?php

namespace Webkul\User\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use Webkul\User\Contracts\User as UserContract;

class User extends Authenticatable implements UserContract
{
    use HasApiTokens;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'image',
        'password',
        'api_token',
        'role_id',
        'status',
        'view_permission',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'api_token',
        'remember_token',
    ];

    /**
     * Get image url for the product image.
     */
    public function image_url()
    {
        if (!$this->image) {
            return;
        }

        return Storage::url($this->image);
    }

    /**
     * Get image url for the product image.
     */
    public function getImageUrlAttribute()
    {
        return $this->image_url();
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $array = parent::toArray();

        $array['image_url'] = $this->image_url;

        return $array;
    }

    /**
     * Get the role that owns the user.
     */
    public function role()
    {
        return $this->belongsTo(RoleProxy::modelClass());
    }

    /**
     * The groups that belong to the user.
     */
    public function groups()
    {
        return $this->belongsToMany(GroupProxy::modelClass(), 'user_groups');
    }

    /**
     * Checks if user has permission to perform certain action.
     *
     * @param string $permission
     *
     * @return bool
     */

    // public function hasPermission($permission)
    // {
    //     if ($this->role->permission_type == 'custom' && ! $this->role->permissions) {
    //         return false;
    //     }

    //     return in_array($permission, $this->role->permissions);
    // }

    public function hasPermission($permission)
    {
        if ($this->role->permission_type === 'all') {
            return true;
        }

        $perms = $this->role->permissions;

        if (!$perms) {
            return false;
        }

        if (is_string($perms)) {
            $perms = json_decode($perms, true);
        }

        if (!is_array($perms)) {
            return false;
        }

        // لو permission اتبعت "dashboard" يبقى dashboard.view يعتبر سماح
        if (isset($perms[$permission]) && is_array($perms[$permission]) && count($perms[$permission])) {
            return true;
        }

        // لو permission اتبعت "dashboard.view" أو "leads.view"
        [$module, $action] = array_pad(explode('.', $permission, 2), 2, null);

        if ($module && $action && isset($perms[$module]) && is_array($perms[$module])) {
            return in_array($action, $perms[$module], true);
        }

        return false;
    }
}
