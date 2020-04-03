<?php

namespace App\Repositories;

use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * 地区信息业务类
 *
 * @package namespace App\Repositories;
 */
interface RegionRepository extends RepositoryInterface
{
    function getProvinceWhere($data = []);

    function getProvinceList($page, $pageSize, $where = '');

    function getProvinceCount($where = '');

    function getCityWhere($data = []);

    function getCityList($page, $pageSize, $where = '');

    function getCityCount($where = '');

    function getDistrictWhere($data = []);

    function getDistrictList($page, $pageSize, $where = '');

    function getDistrictCount($where = '');

    //
    function getOpenCityList();

    function getCityByCode($code);

    function getProvinceInfo($id);

    function getCityInfo($id);

    function getDistrictInfo($id);

    function getCityByName($name);
}
