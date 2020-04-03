<?php
/**
 * Created by PhpStorm.
 * User: tacker
 * Date: 2019/3/20
 * Time: 6:05 PM
 */

namespace App\Api\Controllers\Admin;

use Illuminate\Http\Request;
use App\Helpers\ResultHelper;
use App\Repositories\ShopRepository;
use App\Repositories\AccountRepository;

class PlatformController extends BaseController
{
    private $shop, $account;

    public function __construct(ShopRepository $shop, AccountRepository $account)
    {
        parent::__construct();
        $this->shop = $shop;
        $this->account = $account;
    }

    public function platformAccountInfo(Request $request)
    {
        $account_info = $this->account->getAccountInfo();

        return ResultHelper::json_result('success', $account_info);
    }

    public function periodAccountList(Request $request)
    {
        $page = $request->get('page') - 1;
        if ($page < 0) $page = 0;
        $pageSize = $request->get('limit');

        $year = $request->input('year');
        $month = $request->input('month');
        $whereData = [
            'year' => $year,
            'month' => $month
        ];

        $where = $this->shop->getPeriodWhere($whereData);

        $data = $this->shop->getPeriodAccountList($page, $pageSize, $where);

        $count = $this->shop->getPeriodAccountCount($where);

        return ResultHelper::resources($data, ['count' => $count]);
    }

    public function platformRecordsList(Request $request)
    {
        //平台账户流水列表
        $page = $request->get('page') - 1;
        if ($page < 0) $page = 0;
        $pageSize = $request->get('limit');

        $no = $request->get('no');
        $name = $request->get('name');

        $whereData = [
            'no' => $no,
            'name' => $name
        ];

        $where = $this->account->getPlatformRecordsWhere($whereData);

        $data = $this->account->getPlatformRecordsList($page, $pageSize, $where);

        $count = $this->account->getPlatformRecordsCount($where);

        return ResultHelper::resources($data, ['count' => $count]);
    }

    public function platformReturnList(Request $request)
    {
        //平台利润流水列表
        $page = $request->get('page') - 1;
        if ($page < 0) $page = 0;
        $pageSize = $request->get('limit');

        $no = $request->get('no');
        $name = $request->get('name');

        $whereData = [
            'no' => $no,
            'name' => $name
        ];

        $where = $this->account->getPlatformReturnWhere($whereData);

        $data = $this->account->getPlatformReturnList($page, $pageSize, $where);

        $count = $this->account->getPlatformReturnCount($where);

        return ResultHelper::resources($data, ['count' => $count]);
    }

    public function platformWithdrawList(Request $request)
    {
        //平台提现流水列表
        $page = $request->get('page') - 1;
        if ($page < 0) $page = 0;
        $pageSize = $request->get('limit');

        $no = $request->get('no');
        $name = $request->get('name');

        $whereData = [
            'no' => $no,
            'name' => $name
        ];

        $where = $this->account->getPlatformWithdrawWhere($whereData);

        $data = $this->account->getPlatformWithdrawList($page, $pageSize, $where);

        $count = $this->account->getPlatformWithdrawCount($where);

        return ResultHelper::resources($data, ['count' => $count]);
    }

}