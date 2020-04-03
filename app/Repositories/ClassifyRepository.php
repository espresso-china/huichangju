<?php

namespace App\Repositories;

use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * @package namespace App\Repositories;
 */
interface ClassifyRepository extends RepositoryInterface
{
    function getList($page, $size,$where='');

    function getWhere($data=[]);

    function getCount($where='');

    function setClassifyStatus($id);

    function cancelClassifyStatus($id);

}
