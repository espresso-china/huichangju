<?php
/**
 * Created by PhpStorm.
 * User: tacker
 * Date: 2019/3/25
 * Time: 11:56 AM
 */

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class NoticeTemplateType extends Model implements Transformable
{
    use TransformableTrait;

    const TYPE_IS_ALL = 'all';
    const TYPE_IS_SMS = 'sms';
    const TYPE_IS_EMAIL = 'email';
    const TYPE_IS_WECHAT = 'wechat';
    const TYPE_IS_MINIPG = 'minipg';

    protected $table = 'sys_notice_template_type';

    protected $primaryKey = 'type_id';

    protected $fillable = [
        'template_name', 'template_code', 'template_type', 'is_system'
    ];

    protected $appends = ['template_type_name'];

    public const CREATED_AT = 'create_time';

    public const UPDATED_AT = 'update_time';

    public static function getTypeNames()
    {
        return [
            self::TYPE_IS_ALL => '全部',
            self::TYPE_IS_EMAIL => '邮件',
            self::TYPE_IS_SMS => '短信',
            self::TYPE_IS_WECHAT => '微信',
            self::TYPE_IS_MINIPG => '小程序',
        ];
    }

    public function getTemplateTypeNameAttribute()
    {
        return self::getTypeNames()[$this->attributes['template_type']];
    }
}
