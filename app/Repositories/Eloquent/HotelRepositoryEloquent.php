<?php

namespace App\Repositories\Eloquent;

use App\Model\City;
use App\Model\District;
use App\Model\Hotel;
use App\Model\Province;
use App\Repositories\HotelRepository;
use Prettus\Repository\Criteria\RequestCriteria;


/**
 *
 * @package namespace App\Repositories\Eloquent;
 */
class HotelRepositoryEloquent extends BaseRepository implements HotelRepository
{

    /**
     *
     * @return string
     */
    public function model()
    {
        return Hotel::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }


    function getInfo($id)
    {
        return Hotel::find($id);
    }

    function getHotelWhere($data = [])
    {
        $where = $this->model;
        if (isset($data['name'])) {
            $where = $data['name'] ? $where->where('name', 'like', '%' . $data['name'] . '%') : $where;
        }
        if (isset($data['status'])) {
            $where = $data['status'] ? $where->where('status', 1) : $where;
        }
        return $where;
    }

    function getHotelList($page, $size, $where = '')
    {
        if (empty($where)) {
            $where = $this->model;
        }
        return $where->orderBy('listorder', 'asc')->orderBy('create_time', 'desc')->skip($page * $size ?? 0)->take($size ?? 10)->get();
    }

    function getHotelCount($where = '')
    {
        if (empty($where)) {
            $where = $this->model;
        }
        return $where->skip(0)->take(1)->count();
    }

    function getProvinceList()
    {
        return Province::orderBy('province_id', 'asc')->get();
    }

    function getCityList($provinceId = '')
    {
        $Query = City::orderBy('city_id', 'asc');
        if ($provinceId) {
            $Query = $Query->where('province_id', $provinceId);
        }
        return $Query->get();
    }

    function getDistrictList($cityId = '')
    {
        $Query = District::orderBy('district_id', 'asc');
        if ($cityId) {
            $Query = $Query->where('city_id', $cityId);
        }
        return $Query->get();
    }

}
