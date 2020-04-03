<?php
/**
 * Created by PhpStorm.
 * User: tacker
 * Date: 2019/3/20
 * Time: 6:06 PM
 */

namespace App\Repositories;

use Prettus\Repository\Contracts\RepositoryInterface;

interface AccountRepository extends RepositoryInterface
{

    function getAccountInfo();

    function getPlatformRecordsWhere($data = []);

    function getPlatformRecordsList($page, $pageSize, $where = '');

    function getPlatformRecordsCount($where = '');

    function getPlatformReturnWhere($data = []);

    function getPlatformReturnList($page, $pageSize, $where = '');

    function getPlatformReturnCount($where = '');

    function getPlatformWithdrawWhere($data = []);

    function getPlatformWithdrawList($page, $pageSize, $where = '');

    function getPlatformWithdrawCount($where = '');

}