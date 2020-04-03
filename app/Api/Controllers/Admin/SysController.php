<?php
/**
 * Created by PhpStorm.
 * User: Espresso
 * Date: 2019-02-25
 * Time: 13:53
 */

namespace App\Api\Controllers\Admin;

use App\Model\Config;
use App\Repositories\NewsRepository;
use App\Repositories\RegionRepository;
use Illuminate\Http\Request;
use App\Model\Menu;
use App\Model\District;
use App\Model\City;
use App\Model\Province;
use App\Model\Focus;
use App\Helpers\ResultHelper;

class SysController extends BaseController
{

    private $news, $region;

    public function __construct(NewsRepository $news, RegionRepository $region)
    {
        $this->news = $news;
        $this->region = $region;
        parent::__construct();
    }

    public function MenusList(Request $request)
    {
        $page = $request->get('page') - 1;
        if ($page < 0) $page = 0;
        $pageSize = $request->get('limit');

        $lists = $this->news->getMenusList($page, $pageSize);
        $data = [];
        foreach ($lists as $key => $v) {
            $data[$key]['id'] = $v->id;
            $data[$key]['name'] = $v->name;
            $data[$key]['thumb'] = $v->thumb;
            $data[$key]['url'] = $v->url;
            $data[$key]['listorder'] = $v->listorder;
            $data[$key]['status'] = $v->status;
            $data[$key]['version'] = $v->version;
            $data[$key]['version_text'] = $v->version==1?"v1.0":"v2.0";
            $data[$key]['status_text'] = $v->status ? '启用' : '禁用';
        }
        $count = $this->news->getMenusCount();
        return ResultHelper::json_result('success', $data, 0);
    }

    public function MenusAdd(Request $request)
    {
        if ($request->get('id')) {
            //编辑
            $data = [
                'name' => $request->get('name'),
                'status' => $request->input('status') ? $request->input('status') : 0,
                'thumb' => $request->get('thumb'),
                'listorder' => $request->get('listorder'),
                'version' => $request->get('version'),
                'url' => $request->get('url')
            ];

            //更新分类
            $res = Menu::where('id', $request->get('id'))->update($data);

            if ($res) {
                return response()->json(['code' => 0, 'status' => 0, 'msg' => '导航编辑成功！']);
            } else {
                return response()->json(['code' => 0, 'status' => 1, 'msg' => '导航编辑失败！']);
            }
        } else {

            if (!$request->get('name'))
                return response()->json(['code' => 1, 'msg' => '名称不能为空！']);

            $data = [
                'name' => $request->get('name'),
                'status' => $request->input('status') ? $request->input('status') : 0,
                'thumb' => $request->get('thumb'),
                'listorder' => $request->get('listorder'),
                'version' => $request->get('version'),
                'url' => $request->get('url')
            ];

            $menus = Menu::create($data);

            if ($menus) {
                return response()->json(['code' => 0, 'status' => 0, 'msg' => '导航添加成功！']);
            } else {
                return response()->json(['code' => 0, 'status' => 1, 'msg' => '导航添加失败！']);
            }
        }
    }

    public function Delete(Request $request)
    {
        $id = $request->input('id');
        $res = Menu::where('id', $id)->delete();
        if ($res) {
            return response()->json(['code' => 0, 'msg' => '删除成功！']);
        } else {
            return response()->json(['code' => 1, 'msg' => '删除失败！']);
        }
    }


    public function FocusList(Request $request)
    {
        //轮播图列表
        $page = $request->get('page') - 1;
        if ($page < 0) $page = 0;
        $pageSize = $request->get('limit');

        $lists = $this->news->getFocusList($page, $pageSize);
        $data = [];
        foreach ($lists as $key => $v) {
            $data[$key]['id'] = $v->id;
            $data[$key]['name'] = $v->name;
            $data[$key]['type'] = $v->type;
            $data[$key]['type_name'] = $v->type_name;
            $data[$key]['thumb'] = $v->thumb;
            $data[$key]['url'] = $v->url;
            $data[$key]['listorder'] = $v->listorder;
            $data[$key]['status'] = $v->status ? '启用' : '禁用';
        }
        return ResultHelper::json_result('success', $data, 0);
    }

    public function FocusType(Request $request)
    {
        //轮播图状态
        $data = Focus::getTypes();
        return ResultHelper::json_result('success', $data, 0);
    }

    public function setFocusStatusEdit(Request $request)
    {
        $data = $request->all();
        $data = $data['data'];
        foreach ($data as $v) {
            $this->news->setFocusStatus($v['id']);
        }
        return response()->json(['code' => 0, 'msg' => '设置成功！']);
    }

    public function cancelFocusStatusEdit(Request $request)
    {
        $data = $request->all();
        $data = $data['data'];
        foreach ($data as $v) {
            $this->news->cancelFocusStatus($v['id']);
        }
        return response()->json(['code' => 0, 'msg' => '设置成功！']);
    }

    public function FocusAdd(Request $request)
    {
        if ($request->get('id')) {
            //编辑
            $data = [
                'name' => $request->get('name'),
                'status' => $request->input('status') ? $request->input('status') : 0,
                'thumb' => $request->get('thumb'),
                'type' => $request->input('type'),
                'listorder' => $request->get('listorder'),
                'url' => $request->get('url')
            ];

            //更新分类
            $res = Focus::where('id', $request->get('id'))->update($data);

            if ($res) {
                return response()->json(['code' => 0, 'msg' => '图片编辑成功！']);
            } else {
                return response()->json(['code' => 1, 'msg' => '图片编辑失败！']);
            }
        } else {

            $data = [
                'name' => $request->get('name'),
                'status' => $request->input('status') ? $request->input('status') : 0,
                'type' => $request->input('type'),
                'thumb' => $request->get('thumb'),
                'listorder' => $request->get('listorder'),
                'version' => $request->get('version'),
                'url' => $request->get('url')
            ];
            $result = Focus::create($data);

            if ($result) {
                return response()->json(['code' => 0, 'msg' => '图片添加成功！']);
            } else {
                return response()->json(['code' => 1, 'msg' => '图片添加失败！']);
            }
        }
    }

    public function FocusDelete(Request $request)
    {
        $id = $request->input('id');
        $res = Focus::where('id', $id)->delete();
        if ($res) {
            return response()->json(['code' => 0, 'status' => 0, 'msg' => '删除成功！']);
        } else {
            return response()->json(['code' => 0, 'status' => 1, 'msg' => '删除失败！']);
        }
    }

    public function provinceList(Request $request)
    {
        //省份列表和下拉框
        $page = $request->get('page') - 1;
        if ($page < 0) $page = 0;
        $pageSize = $request->get('limit');

        $name = $request->input('name');
        $whereData = [
            'name' => $name
        ];

        if ($request->get('status') != 1) {
            //列表
            $where = $this->region->getProvinceWhere($whereData);

            $data = $this->region->getProvinceList($page, $pageSize, $where);

            $count = $this->region->getProvinceCount($where);
        } else {
            //下拉框
            $data = Province::orderBy('province_id', 'asc')->get();

            $count = '';
        }

        return ResultHelper::resources($data, ['count' => $count]);
    }

    public function cityProvinceList(Request $request)
    {
        //城市查询上级省份下拉框
        $province_id = $request->get('province_id');

        $data = City::where('province_id', $province_id)->orderBy('city_id', 'asc')->get();

        return ResultHelper::resources($data);
    }

    public function provinceAdd(Request $request)
    {
        if ($request->get('province_id')) {
            //编辑
            $data = [
                'area_id' => $request->get('area_id') ? $request->get('area_id') : 4,
                'province_name' => $request->get('province_name')
            ];

            //更新分类
            $res = Province::where('province_id', $request->get('province_id'))->update($data);

            if ($res) {
                return response()->json(['code' => 0, 'status' => 0, 'msg' => '省份编辑成功！']);
            } else {
                return response()->json(['code' => 0, 'status' => 1, 'msg' => '省份编辑失败！']);
            }
        } else {
            $province = Province::create([
                'area_id' => 4,
                'province_name' => $request->get('province_name')
            ]);
            if ($province) {
                return response()->json(['code' => 0, 'status' => 0, 'msg' => '省份添加成功！']);
            } else {
                return response()->json(['code' => 0, 'status' => 1, 'msg' => '省份添加失败！']);
            }
        }
    }

    public function provinceDelete(Request $request)
    {
        $province_id = $request->input('province_id');
        $res = Province::where('province_id', $province_id)->delete();
        if ($res) {
            return response()->json(['code' => 0, 'status' => 0, 'msg' => '删除成功！']);
        } else {
            return response()->json(['code' => 0, 'status' => 1, 'msg' => '删除失败！']);
        }
    }

    public function cityList(Request $request)
    {
        //城市列表与下拉框
        $page = $request->get('page') - 1;
        if ($page < 0) $page = 0;
        $pageSize = $request->get('limit');
        $name = $request->input('name');
        $open = $request->input('open');
        $whereData = [
            'name' => $name,
            'open' => $open
        ];

        if ($request->get('status') != 1) {
            //列表
            $where = $this->region->getCityWhere($whereData);

            $data = $this->region->getCityList($page, $pageSize, $where);

            $count = $this->region->getCityCount($where);

        } else {
            //下拉框
            $data = City::orderBy('city_id', 'asc')->get();

            $count = '';
        }

        return ResultHelper::resources($data, ['count' => $count]);
    }


    public function cityAdd(Request $request)
    {
        if ($request->get('city_id')) {
            //编辑
            $data = [
                'province_id' => $request->get('province_id'),
                'city_name' => $request->get('city_name'),
                'zipcode' => $request->get('zipcode'),
                'city_code' => $request->get('city_code','0'),
                'is_open' => $request->get('is_open'),
                'begins' =>$request->get('begins'),
                'listorder' => $request->get('listorder')
            ];

            //更新分类
            $res = City::where('city_id', $request->get('city_id'))->update($data);

            if ($res) {
                return response()->json(['code' => 0, 'status' => 0, 'msg' => '编辑成功！']);
            } else {
                return response()->json(['code' => 0, 'status' => 1, 'msg' => '编辑失败！']);
            }
        } else {
            $city = City::create([
                'province_id' => $request->get('province_id'),
                'city_name' => $request->get('city_name'),
                'zipcode' => $request->get('zipcode'),
                'city_code' => $request->get('city_code',''),
                'is_open' => $request->get('is_open'),
                'begins' =>$request->get('begins'),
                'listorder' => $request->get('listorder')
            ]);
            if ($city) {
                return response()->json(['code' => 0, 'status' => 0, 'msg' => '添加成功！']);
            } else {
                return response()->json(['code' => 0, 'status' => 1, 'msg' => '添加失败！']);
            }
        }
    }

    public function cityDelete(Request $request)
    {
        $city_id = $request->input('city_id');
        $res = City::where('city_id', $city_id)->delete();
        if ($res) {
            return response()->json(['code' => 0, 'status' => 0, 'msg' => '删除成功！']);
        } else {
            return response()->json(['code' => 0, 'status' => 1, 'msg' => '删除失败！']);
        }
    }

    public function districtList(Request $request)
    {
        //区县列表
        $page = $request->get('page') - 1;
        if ($page < 0) $page = 0;
        $pageSize = $request->get('limit');

        $name = $request->input('name');
        $whereData = [
            'name' => $name
        ];

        $where = $this->region->getDistrictWhere($whereData);

        $data = $this->region->getDistrictList($page, $pageSize, $where);

        $count = $this->region->getDistrictCount($where);

        return ResultHelper::resources($data, ['count' => $count]);
    }


    public function districtAdd(Request $request)
    {
        if ($request->get('district_id')) {
            //编辑

            $data = [
                'city_id' => $request->get('city_id'),
                'district_name' => $request->get('district_name')
            ];

            //更新分类
            $res = District::where('district_id', $request->get('district_id'))->update($data);

            if ($res) {
                return response()->json(['code' => 0, 'status' => 0, 'msg' => '区县编辑成功！']);
            } else {
                return response()->json(['code' => 0, 'status' => 1, 'msg' => '区县编辑失败！']);
            }
        } else {
            $district = District::create([
                'city_id' => $request->get('city_id'),
                'district_name' => $request->get('district_name')
            ]);
            if ($district) {
                return response()->json(['code' => 0, 'status' => 0, 'msg' => '区县添加成功！']);
            } else {
                return response()->json(['code' => 0, 'status' => 1, 'msg' => '区县添加失败！']);
            }
        }
    }

    public function districtDelete(Request $request)
    {
        $district_id = $request->input('district_id');
        $res = District::where('district_id', $district_id)->delete();
        if ($res) {
            return response()->json(['code' => 0, 'status' => 0, 'msg' => '删除成功！']);
        } else {
            return response()->json(['code' => 0, 'status' => 1, 'msg' => '删除失败！']);
        }
    }

    public function websiteConfigs(Request $request)
    {
        $config_data = [];
        $use_data = [];
        $configs = Config::where('instance_id', 0)->get();
        foreach ($configs as $config) {
            $config_data[$config->key] = $config->value;
            $use_data[$config->key] = $config->is_use;
        }
        return ResultHelper::json_result('success', ['configs' => $config_data, 'uses' => $use_data]);
    }

    public function websiteConfigsSave(Request $request)
    {
        $configs = $request->input();
        if (isset($configs['token'])) {
            unset($configs['token']);
        }

        $uses = $configs['USED'];
        unset($configs['USED']);

        foreach ($configs as $key => $val) {
            $val = $val ?? '';
            $data = ['value' => is_array($val) ? json_encode($val) : $val];
            if (isset($uses[$key])) {
                $data['is_use'] = $uses[$key];
            }
            $info = Config::where('key', $key)->where('instance_id', 0)->first();
            if ($info) {
                Config::where('key', $key)->where('instance_id', 0)->update($data);
            } else {
                $data['key'] = $key;
                $data['instance_id'] = 0;
                Config::create($data);
            }
        }

        return ResultHelper::json_result('success');
    }


}
