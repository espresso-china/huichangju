<?php

namespace App\Repositories;

use Prettus\Repository\Contracts\RepositoryInterface;

/**
 *
 * @package namespace App\Repositories;
 */
interface HotelRepository extends RepositoryInterface
{

    function getInfo($id);

    function getHotelWhere($data = []);

    function getHotelList($page, $size, $where = '');

    function getHotelCount($where = '');

    function getProvinceList();

    function getCityList($provinceId = '');

    function getDistrictList($cityId = '');
}
