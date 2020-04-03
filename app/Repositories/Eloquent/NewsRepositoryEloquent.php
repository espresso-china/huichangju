<?php

namespace App\Repositories\Eloquent;

use App\Model\Classify;
use App\Model\Focus;
use App\Model\Menu;
use App\Model\News;
use App\Model\Activity;
use App\Model\ActivityJoin;
use App\Repositories\NewsRepository;
use Prettus\Repository\Criteria\RequestCriteria;

use App\Validators\NewsValidator;

/**
 *
 * @package namespace App\Repositories\Eloquent;
 */
class NewsRepositoryEloquent extends BaseRepository implements NewsRepository
{

    /**
     *
     * @return string
     */
    public function model()
    {
        return News::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    function getCateById($id){
        return Classify::where('id',$id)->first();
    }

    function getCateList(){
        return Classify::where('status',1)->orderBy('listorder','asc')->get();
    }
    function getWhere($data = [])
    {
        $where = $this->model;
        if (isset($data['title'])) {
            $where = $data['title'] ? $where->where('title', 'like', '%' . $data['title'] . '%') : $where;
        }
        if (isset($data['cate'])) {
            $where = $data['cate'] ? $where->where('classify_id', $data['cate']) : $where;
        }
        if (isset($data['status'])) {
            $where = $data['status'] ? $where->where('status', 1) : $where;
        }
        if (isset($data['sort'])) {
            $where = $data['sort'] ? $where->where('sort', 1) : $where;
        }
        return $where;
    }

    function getList($page, $size, $where = '')
    {
        if (empty($where)) {
            $where = $this->model;
        }
        return $where->orderBy('listorder', 'asc')->orderBy('id', 'desc')->skip($page * $size ?? 0)->take($size ?? 10)->get();
    }

    function getCount($where = '')
    {
        if (empty($where)) {
            $where = $this->model;
        }
        return $where->skip(0)->take(1)->count();
    }

    function setStatusNews($id)
    {
        $info = $this->model->find($id);
        $info['status'] = 1;
        return $info->save();
    }

    function cancelStatusNews($id)
    {
        $info = $this->model->find($id);
        $info['status'] = 0;
        return $info->save();
    }

    function setSortNews($id)
    {
        $info = $this->model->find($id);
        $info['sort'] = 1;
        return $info->save();
    }

    function cancelSortNews($id)
    {
        $info = $this->model->find($id);
        $info['sort'] = 0;
        return $info->save();
    }


    function getMenusList($page, $size, $where = null)
    {
        return Menu::orderBy('listorder', 'asc')->orderBy('id', 'desc')->skip($page * $size ?? 0)->take($size ?? 10)->get();
    }

    function getMenusCount($where = null)
    {
        return Menu::count();
    }

    function getFocusList($page, $size)
    {
        $where = Focus::orderBy('listorder', 'asc');
        return $where->orderBy('id', 'desc')->skip($page * $size ?? 0)->take($size ?? 10)->get();
    }

    function getActivityWhere($data = [])
    {
        $where = $this->switchModel(Activity::class);
        if (isset($data['originator'])) {
            $where = $data['originator'] ? $where->where('originator', 'like', '%' . $data['originator'] . '%') : $where;
        }
        if (isset($data['address'])) {
            $where = $data['address'] ? $where->where('address', 'like', '%' . $data['address'] . '%') : $where;
        }
        if (isset($data['status'])) {
            $where = $data['status'] ? $where->where('status', 1) : $where;
        }
        if (isset($data['title'])) {
            $where = $data['title'] ? $where->where('title', 'like', '%' . $data['title'] . '%') : $where;
        }
        return $where;
    }

    function getActivityList($page, $pageSize, $where = '')
    {
        if (empty($where)) {
            $where = $this->switchModel(Activity::class);
        }
        return $where->orderBy('sort', 'desc')->orderBy('activity_id', 'desc')->skip($page * $pageSize ?? 0)->take($pageSize ?? 10)->get();
    }

    function getActivityCount($where = '')
    {
        if (empty($where)) {
            $where = $this->switchModel(Activity::class);
        }
        return $where->skip(0)->take(1)->count();
    }

    public function getActivityJoinWhere($data = [])
    {
        $where = $this->switchModel(ActivityJoin::class);
        if (isset($data['name'])) {
            $where = $data['name'] ? $where->where('contacts', 'like', '%' . $data['name'] . '%') : $where;
        }
        if (isset($data['phone'])) {
            $where = $data['phone'] ? $where->where('phone', 'like', '%' . $data['phone'] . '%') : $where;
        }
        if (isset($data['uid'])) {
            $where = $data['uid'] ? $where->where('uid', $data['uid']) : $where;
        }
        return $where;
    }

    function getActivityJoinList($page, $pageSize, $where = '')
    {
        if (empty($where)) {
            $where = $this->switchModel(ActivityJoin::class);
        }
        return $where->orderBy('id', 'desc')->skip($page * $pageSize ?? 0)->take($pageSize ?? 10)->get();
    }

    function getActivityJoinCount($where = '')
    {
        if (empty($where)) {
            $where = $this->switchModel(ActivityJoin::class);
        }
        return $where->skip(0)->take(1)->count();
    }

    function setActivityStatusGoods($activity_id)
    {
        $info = Activity::find($activity_id);
        $info['status'] = 1;
        return $info->save();
    }

    function cancelActivityStatusGoods($activity_id)
    {
        $info = Activity::find($activity_id);
        $info['status'] = 0;
        return $info->save();
    }

    function getInfo($id)
    {
        return News::find($id);
    }


    function setFocusStatus($id)
    {
        $info = Focus::find($id);
        $info['status'] = 1;
        return $info->save();
    }

    function cancelFocusStatus($id)
    {
        $info = Focus::find($id);
        $info['status'] = 0;
        return $info->save();
    }

}
