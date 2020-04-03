<?php
/**
 * Created by PhpStorm.
 * User: Espresso
 * Date: 2019-02-23
 * Time: 10:03
 */


namespace App\Repositories\Eloquent;

use Prettus\Repository\Criteria\RequestCriteria;
use App\Model\WechatFans;
use App\Repositories\WechatFansRepository;

/**
 * @package namespace App\Repositories\Eloquent;
 */
class WechatFansRepositoryEloquent extends BaseRepository implements WechatFansRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return WechatFans::class;
    }


    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }


    public function getWeChatUserByOpenId($openId)
    {
        return $this->model->where('openid', $openId)->first();
    }

    public function getWeChatUserBySession3rd($session3rd)
    {
        return $this->model->where('session3rd', $session3rd)->first();
    }

    public function addWeChatUser(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * @param $id
     * @param array $data
     * @return mixed
     */
    public function updateWeChatUser($id, array $data)
    {
        return $this->model->where('fans_id', $id)->update($data);
    }

    public function updateUidByOpenId($type, $openid, $uid)
    {
        return $this->model->where('type', $type)->where('openid', $openid)->update(['uid' => $uid]);
    }

    public function updateUidByUnionId($unionId, $uid)
    {
        return $this->model->where('unionid', $unionId)->update(['uid' => $uid]);
    }

    public function getWeChatUserByUnionId($unionId, $type)
    {
        if (empty($unionId)) {
            return null;
        }
        return $this->model->where('unionid', $unionId)->where('type', $type)->first();
    }

    public function getWeChatUserByUid($uid, $type)
    {
        if (empty($uid)) {
            return null;
        }
        return $this->model->where('uid', $uid)->where('type', $type)->first();
    }
}
