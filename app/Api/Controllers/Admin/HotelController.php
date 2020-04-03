<?php
/**
 * Created by PhpStorm.
 * User: tacker
 * Date: 2019/1/28
 * Time: 2:40 PM
 */

namespace App\Api\Controllers\Admin;

use App\Model\Hotel;
use App\Repositories\HotelRepository;
use File, Image;
use Illuminate\Http\Request;
use App\Helpers\ResultHelper;
use App\Helpers\SettingHelper;
use App\Repositories\RegionRepository;

class HotelController extends BaseController
{
    private $region, $hotel;

    public function __construct(RegionRepository $region, HotelRepository $hotel)
    {
        parent::__construct();
        $this->region = $region;
        $this->hotel = $hotel;
    }

    public function hotels(Request $request)
    {
        $page = $request->get('page') - 1;
        if ($page < 0) $page = 0;
        $pageSize = $request->get('limit');

        $name = $request->input('name');

        $whereData = [
            'name' => $name
        ];

        $where = $this->hotel->getHotelWhere($whereData);

        $data = $this->hotel->getHotelList($page, $pageSize, $where);

        $count = $this->hotel->getHotelCount($where);

        return ResultHelper::resources($data, ['count' => $count]);
    }

    public function Add(Request $request)
    {
        if ($request->get('id')) {
            //编辑
            $data = [
                'name' => $request->get('name'),
                'status' => $request->input('status') ? $request->input('status') : 0,
                'type' => $request->get('type'),
                'thumb' => $request->get('thumb'),
                'stars' => $request->get('stars'),
                'tel' => $request->get('tel'),
                'listorder' => $request->get('listorder',99),
                'description' => $request->get('description') ? $request->get('description') : strip_tags(mb_substr($request->get('content'), 0, 30)),
                'content' => $request->get('content'),
                'area' => $request->get('area'),
                'address' => $request->get('address'),
                'limit_people' => $request->get('limit_people'),
                'price' => $request->get('price'),
                'market_price' => $request->get('market_price'),
                'has_pillar' => $request->get('has_pillar'),
                'screen_type' => $request->get('screen_type'),
                'district_id' => $request->get('district_id'),
                'city_id' => $request->get('city_id'),
                'province_id' => $request->get('province_id'),
                'sort' => $request->get('sort'),
            ];

            //更新分类
            $res = Hotel::where('id', $request->get('id'))->update($data);

            if ($res) {
                return response()->json(['code' => 0, 'status' => 0, 'msg' => '编辑成功！']);
            } else {
                return response()->json(['code' => 0, 'status' => 1, 'msg' => '编辑失败！']);
            }
        } else {
            $news = Hotel::create([
                'name' => $request->get('name'),
                'status' => $request->input('status') ? $request->input('status') : 0,
                'type' => $request->get('type'),
                'thumb' => $request->get('thumb'),
                'stars' => $request->get('stars'),
                'tel' => $request->get('tel'),
                'listorder' => $request->get('listorder',999),
                'description' => $request->get('description') ? $request->get('description') : strip_tags(mb_substr($request->get('content'), 0, 30)),
                'content' => $request->get('content'),
                'area' => $request->get('area'),
                'address' => $request->get('address'),
                'limit_people' => $request->get('limit_people'),
                'price' => $request->get('price'),
                'market_price' => $request->get('market_price'),
                'has_pillar' => $request->get('has_pillar'),
                'screen_type' => $request->get('screen_type'),
                'district_id' => $request->get('district_id'),
                'city_id' => $request->get('city_id'),
                'province_id' => $request->get('province_id'),
                'sort' => $request->get('sort'),
            ]);

            if ($news) {
                return response()->json(['code' => 0, 'status' => 0, 'msg' => '添加成功！']);
            } else {
                return response()->json(['code' => 0, 'status' => 1, 'msg' => '添加失败！']);
            }
        }
    }

    public function Info(Request $request)
    {
        $id = $request->input('id');
        $info = $this->hotel->find($id);
        return ResultHelper::json_result('success', $info);
    }

    public function Delete(Request $request)
    {
        $id = $request->input('id');
        $res = Hotel::where('id', $id)->delete();
        if ($res) {
            return response()->json(['code' => 0, 'status' => 0, 'msg' => '删除成功！']);
        } else {
            return response()->json(['code' => 0, 'status' => 1, 'msg' => '删除失败！']);
        }
    }

    public function Province()
    {
        //提货省份下拉框
        $data = $this->hotel->getProvinceList();
        return ResultHelper::resources($data);
    }

    public function City(Request $request)
    {
        //提货城市下拉框
        $province_id = $request->input('province_id');
        if ($province_id) {
            $data = $this->hotel->getCityList($province_id);
            return ResultHelper::resources($data);
        } else {
            return ResultHelper::resources(collect([]));
        }
    }

    public function District(Request $request)
    {
        //提货区县下拉框
        $city_id = $request->input('city_id');
        if ($city_id) {
            $data = $this->hotel->getDistrictList($city_id);
            return ResultHelper::resources($data);
        } else {
            return ResultHelper::resources(collect([]));
        }
    }

    public function Screens(Request $request)
    {
        $data = Hotel::getTypes();
        return ResultHelper::json_result('', $data);
    }

    public function Stars(Request $request)
    {
        $data = Hotel::getStars();
        return ResultHelper::json_result('', $data);
    }
}
