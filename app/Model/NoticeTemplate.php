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

class NoticeTemplate extends Model implements Transformable
{
    use TransformableTrait;

    const TYPE_IS_SMS = 'sms';
    const TYPE_IS_EMAIL = 'email';
    const TYPE_IS_WECHAT = 'wechat';
    const TYPE_IS_MINIPG = 'minipg';

    protected $table = 'sys_notice_template';

    protected $primaryKey = 'template_id';

    protected $fillable = [
        'shop_id', 'template_type', 'template_code', 'template_title', 'template_content', 'third_tpl_id', 'sign_name', 'is_enable',
    ];

    protected $appends = ['template_type_name', 'template_code_name'];

    public const CREATED_AT = 'create_time';

    public const UPDATED_AT = 'update_time';

    public static function getTypeNames()
    {
        return [
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

    public function code()
    {
        return $this->hasOne(NoticeTemplateType::class, 'template_code', 'template_code');
    }

    public function getTemplateCodeNameAttribute()
    {
        return $this->code ? $this->code->template_name : '-';
    }
}
