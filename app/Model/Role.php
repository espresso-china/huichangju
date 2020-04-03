<?php
/**
 * Created by PhpStorm.
 * User: tacker
 * Date: 2018/4/9
 * Time: 下午2:03
 */

namespace App\Model;

use Zizaco\Entrust\EntrustRole;

class Role extends EntrustRole
{
    const TYPE_IS_ADMIN = 1;
    const TYPE_IS_SHOP = 2;

    protected $table = 'sys_roles';

    //protected $dateFormat = 'U';

    protected $fillable = ['name', 'display_name', 'description', 'type', 'shop_id'];

    protected $appends = ['shop_name', 'type_name'];

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