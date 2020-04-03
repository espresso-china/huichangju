<?php
/**
 * Created by PhpStorm.
 * User: tacker
 * Date: 2019/3/20
 * Time: 6:06 PM
 */

namespace App\Repositories\Eloquent;

use App\Model\Account;
use App\Model\AccountRecord;
use App\Model\AccountReturnRecord;
use App\Model\AccountWithdrawRecord;
use App\Repositories\AccountRepository;

class AccountRepositoryEloquent extends BaseRepository implements AccountRepository
{

    use AccountTraitRepositoryEloquent;

    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Account::class;
    }

    public function getAccountInfo()
    {
        $account_info = $this->model->where('account_id', 1)->first();
        if (empty($account_info)) {
            $account_info = $this->model->create(['account_id' => 1]);
        }

        return $account_info;
    }

    public function getPlatformRecordsWhere($data = [])
    {
        $where = $this->switchModel(AccountRecord::class);
        if ($data['no']) {
            $where = $data['no'] ? $where->where('serial_no', 'like', '%' . $data['no'] . '%') : $where;
        }
        if ($data['name']) {
            $where = $data['name'] ? $where->whereHas('shop', function ($where) use ($data) {
                $where->where('shop_name', 'like', '%' . $data['name'] . '%');
            }) : $where;
        }
        return $where;
    }

    public function getPlatformRecordsList($page, $pageSize, $where = '')
    {
        if (empty($where)) {
            $where = $this->switchModel(AccountRecord::class);
        }
        return $where->orderBy('id', 'desc')
            ->skip($page * $pageSize ?? 0)->take($pageSize ?? 10)->get();
    }

    public function getPlatformRecordsCount($where = '')
    {
        if (empty($where)) {
            $where = $this->switchModel(AccountRecord::class);
        }
        return $where->skip(0)->take(1)->count();
    }

    public function getPlatformReturnWhere($data = [])
    {
        $where = $this->switchModel(AccountReturnRecord::class);
        if ($data['no']) {
            $where = $data['no'] ? $where->where('serial_no', 'like', '%' . $data['no'] . '%') : $where;
        }
        if ($data['name']) {
            $where = $data['name'] ? $where->whereHas('shop', function ($where) use ($data) {
                $where->where('shop_name', 'like', '%' . $data['name'] . '%');
            }) : $where;
        }
        return $where;
    }

    public function getPlatformReturnList($page, $pageSize, $where = '')
    {
        if (empty($where)) {
            $where = $this->switchModel(AccountReturnRecord::class);
        }
        return $where->orderBy('id', 'desc')
            ->skip($page * $pageSize ?? 0)->take($pageSize ?? 10)->get();
    }

    public function getPlatformReturnCount($where = '')
    {
        if (empty($where)) {
            $where = $this->switchModel(AccountReturnRecord::class);
        }
        return $where->skip(0)->take(1)->count();
    }

    public function getPlatformWithdrawWhere($data = [])
    {
        $where = $this->switchModel(AccountWithdrawRecord::class);
        if ($data['no']) {
            $where = $data['no'] ? $where->where('serial_no', 'like', '%' . $data['no'] . '%') : $where;
        }
        if ($data['name']) {
            $where = $data['name'] ? $where->whereHas('shop', function ($where) use ($data) {
                $where->where('shop_name', 'like', '%' . $data['name'] . '%');
            }) : $where;
        }
        return $where;
    }

    public function getPlatformWithdrawList($page, $pageSize, $where = '')
    {
        if (empty($where)) {
            $where = $this->switchModel(AccountWithdrawRecord::class);
        }
        return $where->orderBy('id', 'desc')
            ->skip($page * $pageSize ?? 0)->take($pageSize ?? 10)->get();
    }

    public function getPlatformWithdrawCount($where = '')
    {
        if (empty($where)) {
            $where = $this->switchModel(AccountWithdrawRecord::class);
        }
        return $where->skip(0)->take(1)->count();
    }
}