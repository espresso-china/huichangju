<?php

namespace App\Repositories;

use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * 会员信息相关业务操作
 *
 * @package namespace App\Repositories;
 */
interface MemberRepository extends RepositoryInterface
{
    //

    function getInfoByPhone($phone);

    function getMemberList($page, $pageSize, $where = null);

    function getMemberCount($where = null);

    function getUid($uid);

}
