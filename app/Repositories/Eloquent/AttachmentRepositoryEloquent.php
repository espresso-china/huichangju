<?php
/**
 * Created by PhpStorm.
 * User: Espresso
 * Date: 2019-02-18
 * Time: 14:25
 */


namespace App\Repositories\Eloquent;

use Prettus\Repository\Criteria\RequestCriteria;
use App\Model\Attachment;
use App\Repositories\AttachmentRepository;
/**
 * Class OrderRepositoryEloquent.
 *
 * @package namespace App\Repositories\Eloquent;
 */
class AttachmentRepositoryEloquent extends BaseRepository implements AttachmentRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Attachment::class;
    }


    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    public function store($params = [])
    {
        return $this->model->insertGetId($params);
    }

    function getImgsByImgIds($ids){
        return $this->model->whereIn('id',$ids)->get(['id','url']);
    }

}