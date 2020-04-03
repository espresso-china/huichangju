<?php

namespace App\Model;

use Eloquent;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class RoleUser extends Eloquent implements Transformable
{
    use TransformableTrait;

    protected $fillable = [];

    protected $table = 'sys_role_user';

    //protected $dateFormat = 'U';

    public function role()
    {
        return $this->hasOne('App\Models\Role', 'id', 'role_id');
    }

    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }
}
