<?php

namespace App\Repositories\Eloquent;

use App\Repositories\ClassifyRepository;

use Prettus\Repository\Criteria\RequestCriteria;
use App\Model\Classify;
use App\Validators\ClassifyValidator;

/**
 * @package namespace App\Repositories\Eloquent;
 */
class ClassifyRepositoryEloquent extends BaseRepository implements ClassifyRepository
{

    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Classify::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    function getList($page, $size, $where = '')
    {
        if(empty($where)){
            $where= $this->model;
        }
        return $where->orderBy('listorder','desc')->orderBy('id', 'desc')->skip($page * $size ?? 0)->take($size ?? 10)->get();
    }

    function getWhere($data=[]){
        $where = $this->model;
        if (isset($data['title'])) {
            $where = $data['title'] ? $where->where('title', 'like', '%' . $data['title'] . '%') : $where;
        }
        return $where;
    }

    function getCount($where='')
    {
        if(empty($where)){
            $where= $this->model;
        }
        return $where->skip(0)->take(1)->count();
    }

    function setClassifyStatus($id)
    {
        $info = $this->model->find($id);
        $info['status'] = 1;
        return $info->save();
    }

    function cancelClassifyStatus($id)
    {
        $info = $this->model->find($id);
        $info['status'] = 0;
        return $info->save();
    }

}
