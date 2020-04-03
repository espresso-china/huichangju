<?php

namespace App\Repositories\Eloquent;

use Prettus\Repository\Criteria\RequestCriteria;
use App\Model\Member;
use App\Repositories\MemberRepository;

/**
 * Class MemberRepositoryEloquent.
 *
 * @package namespace App\Repositories\Eloquent;
 */
class MemberRepositoryEloquent extends BaseRepository implements MemberRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Member::class;
    }


    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    function getInfoByPhone($phone)
    {
        return $this->model->where('phone', $phone)->first();
    }

    function getMemberList($page, $pageSize, $where = null)
    {
        if (empty($where)) {
            $where = $this->model;
        }
        return $where->orderBy('uid', 'desc')->skip($page * $pageSize ?? 0)->take($pageSize ?? 10)->get();
    }

    function getMemberCount($where = null)
    {
        if ($where) {
            return $where->skip(0)->take(1)->count();
        }
        return $this->model->count();
    }

    function getUid($uid)
    {
        return Member::where('uid', $uid)->first();
    }

}
