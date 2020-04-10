<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * Class Member.
 *
 * @package namespace App\Model;
 */
class Member extends Model implements Transformable
{
    use TransformableTrait;

    protected $table = 'sys_member';

    protected $primaryKey = 'uid';

    protected $fillable = [
        'member_name', 'member_level', 'member_point', 'member_balance', 'remark','wx_uid','phone','avatar'
    ];


    public const CREATED_AT = 'reg_time';

    public const UPDATED_AT = 'update_time';


    public function WechatMember()
    {
        return $this->hasOne(WechatFans::class, 'uid', 'uid');
    }
}
