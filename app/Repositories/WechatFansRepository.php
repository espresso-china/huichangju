<?php
/**
 * Created by PhpStorm.
 * User: Espresso
 * Date: 2019-02-23
 * Time: 10:02
 */


namespace App\Repositories;

use Prettus\Repository\Contracts\RepositoryInterface;

/**
 *
 * @package namespace App\Repositories;
 */
interface WechatFansRepository extends RepositoryInterface
{

    public function getWeChatUserByOpenId($openId);

    public function getWeChatUserBySession3rd($session3rd);

    public function addWeChatUser(array $data);

    public function updateWeChatUser($id, array $data);

    public function updateUidByOpenId($type, $openid, $uid);

    public function updateUidByUnionId($unionId, $uid);

    public function getWeChatUserByUnionId($unionId, $type);

    public function getWeChatUserByUid($uid, $type);
}
