<?php
/**
 * Created by PhpStorm.
 * User: tacker
 * Date: 2019/1/28
 * Time: 4:58 PM
 */

namespace App\Api\Controllers\Admin;

use Illuminate\Http\Request;
use App\Helpers\ResultHelper;
use App\Repositories\MemberRepository;

class MemberController extends BaseController
{

    private $member;

    public function __construct(MemberRepository $member)
    {
        parent::__construct();
        $this->member = $member;
    }


    public function memberList(Request $request)
    {
        $page = $request->get('page') - 1;
        if ($page < 0) $page = 0;
        $pageSize = $request->get('limit');

        $data = $this->member->getMemberList($page, $pageSize);

        $count = $this->member->getMemberCount();

        return ResultHelper::resources($data, ['count' => $count]);
    }


}
