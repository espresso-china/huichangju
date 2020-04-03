<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Goods.
 *
 * @package namespace App\Model;
 */
class Hotel extends Model implements Transformable
{
    use SoftDeletes;
    use TransformableTrait;

    protected $table = 'cms_hotel';

    protected $primaryKey = 'id';

    protected $fillable = [
        'name', 'description', 'thumb', 'area', 'status', 'content', 'listorder', 'address', 'limit_people', 'price', 'market_price', 'stars', 'has_pillar', 'screen_type', 'district_id', 'city_id', 'province_id', 'type', 'tel'
    ];

    const SCREEN_TYPE_IS_LED = 1;
    const SCREEN_TYPE_IS_LCD = 2;
    const SCREEN_TYPE_IS_OTHER = 3;
    const SCREEN_TYPE_IS_NO = 0;

    const LEVEL_IS_0 = 0;
    const LEVEL_IS_1 = 1;
    const LEVEL_IS_2 = 2;
    const LEVEL_IS_3 = 3;
    const LEVEL_IS_4 = 4;
    const LEVEL_IS_5 = 5;

    protected $appends = ['classify_name'];

    public const CREATED_AT = 'create_time';

    public const UPDATED_AT = 'update_time';

    public const DELETED_AT = 'delete_time';

    public static function getTypes($type = '')
    {
        $types = [
            self::SCREEN_TYPE_IS_LED => 'LED屏幕',
            self::SCREEN_TYPE_IS_LCD => '液晶屏',
            self::SCREEN_TYPE_IS_OTHER => '其他屏幕',
            self::SCREEN_TYPE_IS_NO => '没有屏幕'
        ];
        if ($type === '') {
            return $types;
        } else {
            return isset($types[$type]) ? $types[$type] : '';
        }
    }

    public static function getStars($type = '')
    {
        $types = [
            self::LEVEL_IS_0 => '未评星',
            self::LEVEL_IS_1 => '一星级',
            self::LEVEL_IS_2 => '二星级',
            self::LEVEL_IS_3 => '三星级',
            self::LEVEL_IS_4 => '四星级',
            self::LEVEL_IS_5 => '五星级'
        ];
        if ($type === '') {
            return $types;
        } else {
            return isset($types[$type]) ? $types[$type] : '';
        }
    }

    public function getClassifyNameAttribute()
    {
        return $this->classify ? $this->classify->title : '';
    }

}

