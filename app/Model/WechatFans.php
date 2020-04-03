<?php
/**
 * Created by PhpStorm.
 * User: Espresso
 * Date: 2019-02-23
 * Time: 9:56
 */

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * Class Member.
 *
 * @package namespace App\Model;
 */
class WechatFans extends Model implements Transformable
{
    use TransformableTrait;

    const TYPE_IS_MINI_PROGRAM = 1; //小程序用户
    const TYPE_IS_OFFICIAL = 2; //公众号用户

    protected $table = 'sys_weixin_fans';

    protected $primaryKey = 'fans_id';

    protected $fillable = [
        'type', 'uid', 'source_uid', 'instance_id', 'nickname', 'nickname_decode', 'headimgurl', 'sex',
        'language', 'country', 'province', 'city', 'district', 'openid', 'unionid', 'groupid',
        'is_subscribe', 'subscribe_date', 'unsubscribe_date', 'update_date', 'memo', 'session', 'session3rd'
    ];

    protected $dateFormat = 'U';

    protected $dates = ['created_at', 'updated_at'];


}
