<?php


namespace Buzz\Authorization\Traits;


use Illuminate\Database\Eloquent\Model;

trait UserAuthorizationTrait
{
    /**
     * The permission of user.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $permissions;

    /**
     * The roles that belong to the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(\Config::get('authorization.model_role'));
    }

    public function detachRole($role)
    {
        $this->roles()->detach($role);
    }

    public function attachRole($role)
    {
        $this->roles()->attach($role);
    }

    public function syncRole($role)
    {
        $this->roles()->sync($role);
    }

    public function is($role, $any = false)
    {
        if ($role instanceof Model) {
            $role = $role->slug;
        }
        $slugs = $this->roles->lists('slug');
        if (is_array($role)) {
            foreach ($role as $item) {
                if ($slugs->search($item) === false) {
                    return false;
                } elseif ($any === true) {
                    return true;
                }
            }

            return true;
        }

        return $slugs->search($role) !== false;
    }

    public function isAny($role)
    {
        return $this->is($role, true);
    }

    public function can($permission, $any = false)
    {
        if (is_null($this->permissions)) {
            $permissions = collect();
            $this->roles->each(function ($item, $key) use (&$permissions) {
                $permissions = $permissions->merge($item->permissions->lists('slug'));
            });
            $this->permissions = $permissions->unique();
        }
        if ($permission instanceof Model) {
            $permission = $permission->slug;
        }
        if (is_array($permission)) {
            foreach ($permission as $item) {
                if ($this->permissions->search($item) === false) {
                    return false;
                } elseif ($any === true) {
                    return true;
                }
            }

            return true;
        }

        return $this->permissions->search($permission) !== false;
    }

    public function canAny($permission)
    {
        return $this->can($permission, true);
    }
}