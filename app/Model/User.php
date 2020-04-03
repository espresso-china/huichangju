<?php

namespace App\Model;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Zizaco\Entrust\Traits\EntrustUserTrait;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable, EntrustUserTrait;

    const TYPE_IS_ADMIN = 1;
    const TYPE_IS_SHOP = 2;

    protected $primaryKey = 'id';

    protected $table = 'sys_users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'phone', 'user_email', 'password', 'avatar', 'type', 'comp_id', 'shop_id', 'disabled', 'wx_uid','is_agree'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $dateFormat = 'U';

    public const CREATED_AT = 'created_time';

    public const UPDATED_AT = 'updated_time';

    protected $appends = ['role_name', 'shop_name', 'type_name'];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function getRoleNameAttribute()
    {
        $roles = $this->roles;
        $name = [];
        foreach ($roles as $role) {
            $name[] = $role['display_name'];
        }
        return $this->attributes['role_name'] = implode(',', $name);
    }

    public function shop()
    {
        return $this->hasOne('\App\Model\Shop', 'shop_id', 'shop_id');
    }

    public function getShopNameAttribute()
    {
        if ($this->type == self::TYPE_IS_ADMIN)
            return '[平台账号]';
        else
            return $this->shop ? $this->shop->shop_name : '[未关联店铺]';
    }

    public static function getTypeNames()
    {
        return [self::TYPE_IS_ADMIN => '平台', self::TYPE_IS_SHOP => '商家'];
    }

    public function getTypeNameAttribute()
    {
        return self::getTypeNames()[$this->attributes['type']];
    }
}
