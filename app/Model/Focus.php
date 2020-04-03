<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 *
 * @package namespace App\Model;
 */
class Focus extends Model implements Transformable
{
    use TransformableTrait;

    const TYPE_IS_INDEX = 1;//首页滚动图
    const TYPE_IS_PIN = 2;    //拼团
    const TYPE_IS_TAOWEN = 3; //淘问
    const TYPE_IS_KANJIA = 4; //砍价

    protected $table = 'cms_focus';

    protected $primaryKey = 'id';

    protected $fillable = [
        'name', 'url', 'status', 'listorder', 'thumb', 'type'
    ];

    protected $appends = ['type_name'];

    public static function getTypes()
    {
        return [
            self::TYPE_IS_INDEX => '首页滚动图',
            self::TYPE_IS_PIN => '拼团首页',
            self::TYPE_IS_TAOWEN => '淘问首页',
            self::TYPE_IS_KANJIA => '砍价首页'
        ];
    }

    public function getTypeNameAttribute()
    {
        $names = self::getTypes();
        return isset($names[$this->attributes['type']]) ? $names[$this->attributes['type']] : '无';
    }

}

