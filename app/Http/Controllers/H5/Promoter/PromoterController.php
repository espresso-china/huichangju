<?php
/**
 * Created by PhpStorm.
 * User: tacker
 * Date: 2019/9/30
 * Time: 4:08 PM
 */

namespace App\Http\Controllers\H5\Promoter;

use Illuminate\Http\Request;
use App\Helpers\ResultHelper;
use App\Repositories\FxPromoterRepository;

class PromoterController extends BaseController
{
    private $promoter;

    public function __construct(FxPromoterRepository $promoter)
    {
        parent::__construct();
        $this->promoter = $promoter;
    }

    /**
     * 分销记录
     */
    public function createPromoterRecord(Request $request)
    {
        $promoter_id = $request->input('promoter_id', 0);
        $goods_id = $request->input('goods_id');
        $shop_id = $request->input('shop_id');
        $source_uid = $request->input('source_uid', 0);

        \Log::info('创建分销记录：');
        \Log::info(json_encode($request->input()));

        if (empty($promoter_id) && empty($source_uid)) {
            return ResultHelper::json_error('用户ID和推广员ID不能同时为空');
        }

        if ($promoter_id) {
            $promoter_info = $this->promoter->getPromoterById($promoter_id);
            if ($promoter_info) {
                $source_uid = $promoter_info->uid;
            }
        }

        $result = $this->promoter->createPromoterRecord($source_uid, $this->uid, $shop_id, $goods_id, $promoter_id);

        \Log::info(json_encode($result));

        if ($result['status']) {
            return ResultHelper::json_result('success');
        } else {
            return ResultHelper::json_error('fail');
        }
    }
}
