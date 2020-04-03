<?php

namespace App\Repositories\Eloquent;

use App\Model\City;
use App\Model\District;
use App\Model\Province;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\RegionRepository;
use App\Validators\RegionValidator;

/**
 * Class RegionRepositoryEloquent.
 *
 * @package namespace App\Repositories\Eloquent;
 */
class RegionRepositoryEloquent extends BaseRepository implements RegionRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return City::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    function getProvinceWhere($data = [])
    {
        $where = $this->switchModel(Province::class);
        if (isset($data['name'])) {
            $where = $data['name'] ? $where->where('province_name', 'like', '%' . $data['name'] . '%') : $where;
        }
        return $where;
    }

    function getProvinceList($page, $pageSize, $where = '')
    {
        if (empty($where)) {
            $where = $this->switchModel(Province::class);
        }
        return $where->orderBy('province_id', 'asc')->skip($page * $pageSize ?? 0)->take($pageSize ?? 10)->get();
    }

    function getProvinceCount($where = '')
    {
        if (empty($where)) {
            $where = $this->switchModel(Province::class);
        }
        return $where->skip(0)->take(1)->count();
    }

    function getCityWhere($data = [])
    {
        $where = $this->model;
        if (isset($data['name'])) {
            $where = $data['name'] ? $where->where('city_name', 'like', '%' . $data['name'] . '%') : $where;
        }
        if (isset($data['open'])) {
            $where = $where->where('is_open', $data['open']);
        }
        return $where;
    }

    function getCityList($page, $pageSize, $where = '')
    {
        if (empty($where)) {
            $where = $this->model;
        }
        return $where->orderBy('city_id', 'asc')->skip($page * $pageSize ?? 0)->take($pageSize ?? 10)->get();
    }

    function getCityCount($where = '')
    {
        if (empty($where)) {
            $where = $this->model;
        }
        return $where->skip(0)->take(1)->count();
    }

    function getDistrictWhere($data = [])
    {
        $where = $this->switchModel(District::class);
        if (isset($data['name'])) {
            $where = $data['name'] ? $where->where('district_name', 'like', '%' . $data['name'] . '%') : $where;
        }
        return $where;
    }

    function getDistrictList($page, $pageSize, $where = '')
    {
        if (empty($where)) {
            $where = $this->switchModel(District::class);
        }
        return $where->orderBy('district_id', 'asc')->skip($page * $pageSize ?? 0)->take($pageSize ?? 10)->get();
    }

    function getDistrictCount($where = '')
    {
        if (empty($where)) {
            $where = $this->switchModel(District::class);
        }
        return $where->skip(0)->take(1)->count();
    }

    function getOpenCityList()
    {
        return $this->model->where('is_open', 1)->orderBy('listorder', 'asc')->get();
    }

    function getCityByCode($code)
    {
        return $this->model->where('city_code', $code)->first();
    }

    function getProvinceInfo($id)
    {
        return Province::where('province_id', $id)->first();
    }

    function getCityInfo($id)
    {
        return City::where('city_id', $id)->first();
    }

    function getDistrictInfo($id)
    {
        return District::where('district_id', $id)->first();
    }

    function getCityByName($name){
        return $this->model->where('city_name', $name)->first();
    }
}
