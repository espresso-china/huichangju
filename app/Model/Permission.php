<?php
/**
 * Created by PhpStorm.
 * User: tacker
 * Date: 2018/4/9
 * Time: 下午2:04
 */

namespace App\Model;

use Zizaco\Entrust\EntrustPermission;

class Permission extends EntrustPermission
{
    const TYPE_IS_PLATFORM = 1;
    const TYPE_IS_SHOP = 2;

    protected $table = 'sys_permissions';

    protected $fillable = ['fid', 'icon', 'name', 'display_name', 'description', 'is_menu', 'uri', 'sort', 'type', 'disabled'];

    protected $appends = ['icon_html', 'sub_permission', 'type_name'];

    //protected $dateFormat = 'U';

    public function getNameAttribute($value)
    {
        if (starts_with($value, '#')) {
            return head(explode('-', $value));
        }
        return $value;
    }

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = ($value == '#') ? '#-' . time() : $value;
    }

    public function getIconHtmlAttribute()
    {
        return $this->attributes['icon'] ? '<i class="' . $this->attributes['icon'] . '"></i>' : '';
    }

    public static function getTypeNames()
    {
        return [self::TYPE_IS_PLATFORM => '平台', self::TYPE_IS_SHOP => '商家'];
    }

    public function getTypeNameAttribute()
    {
        $type_names = [];
        $types = explode(',', $this->attributes['type']);
        foreach ($types as $type) {
            $type_names[] = self::getTypeNames()[$type];
        }
        return implode(',', $type_names);
    }

    public function getSubPermissionAttribute()
    {
        return $this->where('fid', $this->attributes['id'])->orderBy('sort', 'asc')->get();
    }
}