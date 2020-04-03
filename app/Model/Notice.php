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

class Notice extends Model implements Transformable
{
    use TransformableTrait;

    const STATUS_IS_INIT = 0;
    const STATUS_IS_APPLY = 1;
    const STATUS_IS_REFUSE = -1;
    const STATUS_IS_WAIT_SEND = 2;
    const STATUS_IS_SENDING = 3;
    const STATUS_IS_FAIL = 4;
    const STATUS_IS_SUCCESS = 9;

    protected $table = 'sys_notice';

    protected $primaryKey = 'id';

    protected $fillable = [
        'shop_id', 'notice_type', 'notice_message', 'is_enable', 'to_admin', 'to_uid', 'to_tag_id', 'to',
        'status', 'total', 'notice_method', 'params', 'third_tpl_id'
    ];

    protected $appends = ['notice_type_name', 'status_name'];

    public const CREATED_AT = 'create_time';

    public const UPDATED_AT = 'update_time';

    public function template_type()
    {
        return $this->hasOne(NoticeTemplateType::class, 'type_id', 'notice_type');
    }

    public function getNoticeTypeNameAttribute()
    {
        return $this->template_type ? $this->template_type->template_name : '网站通知';
    }

    public function getStatusNames()
    {
        return [
            self::STATUS_IS_REFUSE => '已拒绝',
            self::STATUS_IS_INIT => '初始化',
            self::STATUS_IS_APPLY => '申请中',
            self::STATUS_IS_WAIT_SEND => '待发送',
            self::STATUS_IS_SENDING => '发送中',
            self::STATUS_IS_FAIL => '已失败',
            self::STATUS_IS_SUCCESS => '已发送',
        ];
    }

    public function getStatusNameAttribute()
    {
        return isset(self::getStatusNames()[$this->attributes['status']]) ? self::getStatusNames()[$this->attributes['status']] : '-';
    }

    public function user()
    {
        if ($this->attributes['to_admin']) {
            return $this->hasOne(User::class, 'id', 'to_uid');
        } else {
            return $this->hasOne(Member::class, 'uid', 'to_uid');
        }
    }

    public function getParamsAttribute()
    {
        return html_entity_decode($this->attributes['params']);
    }
}