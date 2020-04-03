<?php

namespace App\Repositories;

use Prettus\Repository\Contracts\RepositoryInterface;

/**
 *
 * @package namespace App\Repositories;
 */
interface NewsRepository extends RepositoryInterface
{
    function getCateById($id);

    function getCateList();

    function getWhere($data=[]);

    function getList($page, $size, $where='');

    function getCount($where='');

    function setStatusNews($id);

    function cancelStatusNews($id);

    function setSortNews($id);

    function cancelSortNews($id);

    function getMenusList($page, $size);

    function getMenusCount();

    function getFocusList($page, $size);

    function setFocusStatus($id);

    function cancelFocusStatus($id);

    function getActivityWhere($data=[]);

    function getActivityList($page, $pageSize,$where='');

    function getActivityCount($where='');

    function getActivityJoinWhere($data=[]);

    function getActivityJoinList($page, $pageSize, $where='');

    function getActivityJoinCount($where='');

    function setActivityStatusGoods($activity_id);

    function cancelActivityStatusGoods($activity_id);

    function getInfo($id);
}
