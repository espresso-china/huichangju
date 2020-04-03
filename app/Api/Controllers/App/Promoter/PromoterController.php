<?php
/**
 * Created by PhpStorm.
 * User: Espresso
 * Date: 2019-03-13
 * Time: 15:27
 */


namespace App\Api\Controllers\App\Promoter;

use App\Helpers\SettingHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Helpers\ResultHelper;
use App\Helpers\NoticeHelper;
use App\Helpers\UrlHelper;
use App\Model\Order;
use App\Repositories\AttachmentRepository;
use App\Repositories\FxPromoterRepository;
use App\Repositories\FxUserAccountRepository;
use App\Repositories\GoodsRepository;
use App\Repositories\NoticeRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ShopRepository;
use App\Repositories\WechatFansRepository;
use App\Repositories\MemberRepository;
use App\Api\Controllers\App\ApiController;

class PromoterController extends ApiController
{
    private $member, $fans, $fxuser, $goods, $shop, $order, $attachment, $notice;

    public function __construct(MemberRepository $member, WechatFansRepository $fans, FxPromoterRepository $promoter,
                                FxUserAccountRepository $fxuser, GoodsRepository $goods, OrderRepository $order,
                                ShopRepository $shop, AttachmentRepository $attachment, NoticeRepository $notice)
    {
        parent::__construct();
        $this->member = $member;
        $this->fans = $fans;
        $this->promoter = $promoter;
        $this->fxuser = $fxuser;
        $this->goods = $goods;
        $this->order = $order;
        $this->shop = $shop;
        $this->attachment = $attachment;
        $this->notice = $notice;
    }

    public function getWithDrawAccount(Request $request)
    {
        $uid = $request->input('uid');
        $userInfo = $this->fxuser->getAccountByUid($uid);
        $data = [];
        if ($userInfo) {
            $data['commission'] = $userInfo->commission;//总佣金
            $data['commission_cash'] = $userInfo->commission_cash;//可提现佣金
            $data['commission_withdraw'] = $userInfo->commission_withdraw;//已提现佣金
            return ResultHelper::json_result('success', $data, 200);
        } else {
            return ResultHelper::json_result('fail', $data, 201);
        }
    }

    public function getWithDrawLogs(Request $request)
    {
        $uid = $request->input('uid');
        $page = $request->input('page', 0);
        $size = $request->input('size', 10);
        $whereData = [
            'uid' => $uid
        ];
        $data = [];
        $lists = $this->fxuser->getWithDrawLogs($page, $size, $whereData);
        foreach ($lists as $key => $list) {
            $data[$key]['cash'] = $list->cash;
            $data[$key]['bank_name'] = $list->bank_name;
            $data[$key]['account_number'] = $list->account_number;
            $data[$key]['pay_time'] = Carbon::parse($list->payment_times)->format('Y-m-d H:i:s');
        }
        return ResultHelper::json_result('success', $data, 200);

    }

    public function getWithDrawMoney(Request $request)
    {
        $uid = $request->input('uid');
        $shop_id = $request->input('shop_id');
        $info = $this->fxuser->getAccountByUid($uid, $shop_id);
        if ($info) {
            return ResultHelper::json_result('success', ['money' => $info->commission_cash], 200);
        } else {
            return ResultHelper::json_error('账户不存在', ['money' => 0]);
        }
    }

    /*
     * 分销会员店铺和商品列表
     */
    public function ShopList(Request $request)
    {
        $uid = $request->input('uid');
        $page = $request->input('page', 0);
        $size = $request->input('size', 10);
        $whereData = [
            'uid' => $uid,
            'is_audit' => 1,
            'is_lock' => 0
        ];
        $where = $this->promoter->getColoneWhere($whereData);
        $promoterList = $this->promoter->getList($page, $size, $where);

        $data = [];
        foreach ($promoterList as $key => $item) {
            $data[$key]['shopInfo']['shop_name'] = $item->shop_name;
            $data[$key]['shopInfo']['address'] = $item->shop ? $item->shop->shop_address : '';
            $data[$key]['shopInfo']['number'] = $item->shop ? $item->shop->rateGoods->count() : 0;
            $data[$key]['shopInfo']['rate'] = $item->shop ? $item->shop->shop_platform_commission_rate : 0;

            $whereData = [
                'shop_id' => $item->shop_id,
                'is_open' => 1
            ];
            $pwhere = $this->promoter->getGoodsWhere($whereData);
            $commissionGoods = $this->promoter->getPromoterGoodsList($page, $size, $pwhere);

            foreach ($commissionGoods as $k => $commissionGood) {
                \Log::info($commissionGood);
                $goodsInfo = $commissionGood->goodsInfo;
                if ($goodsInfo && $goodsInfo->status == 1) {
                    $data[$key]['goods'][$k]['goods_id'] = $goodsInfo->goods_id;
                    $data[$key]['goods'][$k]['goods_name'] = $goodsInfo->goods_name;
                    $data[$key]['goods'][$k]['price'] = $goodsInfo->price;
                    $data[$key]['goods'][$k]['goods_rate'] = round($goodsInfo->price * $commissionGood->distribution_commission_rate / 100, 2) ?: '-';
                    $data[$key]['goods'][$k]['goods_cover'] = UrlHelper::getGoodsCover($goodsInfo->goods_cover);
                    $data[$key]['goods'][$k]['sales'] = $goodsInfo->display_sales;
                    $data[$key]['goods'][$k]['stock'] = $goodsInfo->stock;
                    $data[$key]['goods'][$k]['promoter_id'] = $item->promoter_id;
                }
            }
        }
        return ResultHelper::json_result('success', $data, 200);

    }

    public function getPromoterOders(Request $request)
    {
        $uid = $request->input('uid');
        $page = $request->input('page', 0);
        $size = $request->input('size', 10);
        $status = $request->input('status', 0);
        $type = $request->input('type', 0);
        $page = $page - 1;
        $promoters = $this->promoter->getPromoterIdsByUid($uid);

        if ($status == 0) {
            //全部
            $orders = $this->promoter->getFxCommissionDistributionsByUid($page, $size, $promoters, [], $type, true);
        } elseif ($status == 1) {
            //待提货
            $orders = $this->promoter->getFxCommissionDistributionsByUid($page, $size, $promoters, [1], $type, true);
        } elseif ($status == 2) {
            //已提货
            $orders = $this->promoter->getFxCommissionDistributionsByUid($page, $size, $promoters, [3, 4], $type, true);
        }
        $data = [];
        foreach ($orders as $key => $v) {
            $cover_id = $v->orderGoods->goods_picture;
            $img = $this->attachment->find($cover_id);
            $orderInfo = $v->orderInfo;
            $data[$key]['shop_name'] = $v->shopInfo ? $v->shopInfo->shop_name : '-';
            $data[$key]['goods_name'] = $v->orderGoods ? str_limit($v->orderGoods->goods_name, 36, '...') : '-';
            $data[$key]['cover'] = UrlHelper::getShopCover($img->filepath . 'small/' . $img->filename);
            $data[$key]['goods_money'] = $v->goods_money;
            $data[$key]['commission_money'] = $v->commission_money;
            $data[$key]['order_name'] = $orderInfo->user_name;
            $data[$key]['order_no'] = $orderInfo->order_no;
            $data[$key]['create_time'] = Carbon::parse($v->create_time)->format('Y-m-d H:i:s');
            $data[$key]['status'] = Order::getOrderStatus($orderInfo->order_status);
            $data[$key]['warn'] = $orderInfo->order_status == 1 ? false : true;
        }
        $today = $orders = $this->promoter->getFxCommissionDistributionsByUid($page, $size, $promoters, '', 1, false)->count();
        $week = $orders = $this->promoter->getFxCommissionDistributionsByUid($page, $size, $promoters, '', 2, false)->count();
        $month = $orders = $this->promoter->getFxCommissionDistributionsByUid($page, $size, $promoters, '', 3, false)->count();
        return ResultHelper::json_result('success', ['orders' => $data, 'today' => $today, 'week' => $week, 'month' => $month], 200);
    }

    /*
     * 提交提现申请
     */
    public function postWithdrawApply(Request $request)
    {
        $uid = $request->input('uid');
        $shop_id = $request->input('shop_id');
        $bank_name = $request->input('bank_name');
        $account_number = $request->input('account_number');
        $realname = $request->input('realname');
        $cash = $request->input('money');

        if (empty($realname)) {
            $realname = $realname ?: '';
            // return ResultHelper::json_error('请填写真实姓名');
        }

        $account_type = $request->input('account_type', 2); //1银行卡，2微信支付
        if ($account_type == 2) {
            $wxUserInfo = $this->fans->getWeChatUserBySession3rd($this->session3rd);
            if ($wxUserInfo->uid != $this->uid) {
                return ResultHelper::json_error('数据错误，请重新进入小程序');
            }

            $account_number = $wxUserInfo->openid;
            $bank_name = '微信支付';
        } else {
            $account_number = str_replace(' ', '', trim($account_number));
        }

        if ($cash <= 0) {
            return ResultHelper::json_error('提现金额必须大于0');
        }

        $memberInfo = $this->member->find($uid);
        $result = $this->fxuser->applyCommissionWithdraw($shop_id, $memberInfo, $bank_name, $account_number, $realname, $cash, $account_type);
        if ($result['status']) {
            return resultHelper::json_result('success', $result['data'], 200);
        } else {
            return ResultHelper::json_error($result['msg']);
        }
    }

    /**
     * 获取团长账号
     */
    public function getWithDrawAccountList(Request $request)
    {
        $uid = $request->input('uid');
        $list = $this->fxuser->getAccountListByUid($uid);
        $count = $list->sum('commission_cash');
        $data = [];
        foreach ($list as $key => $v) {
            $data[$key]['shop_logo'] = $v->shop ? UrlHelper::getShopCover($v->shop->shop_logo) : UrlHelper::getShopCover('');
            $data[$key]['shop_name'] = $v->shop ? $v->shop->shop_name : '已删除店铺';
            $data[$key]['shop_id'] = $v->shop_id;
            $data[$key]['commission_cash'] = $v->commission_cash;
            $data[$key]['commission_withdraw'] = $v->commission_withdraw;//
            $data[$key]['create_time'] = Carbon::parse($v->create_time)->format('Y-m-d H:i:s');
        }
        return ResultHelper::json_result('success', ['shops' => $data, 'count' => $count], 200);
    }

    /*
     * 申请团长
     */
    public function postPromoterApply(Request $request)
    {
        $uid = $request->input('uid');
        $shop_id = $request->input('shop_id');
        $is_valid = $this->promoter->getPromoterVaild($uid, $shop_id);
        if ($is_valid > 0) {
            return ResultHelper::json_error('您已经申请过该店铺的团长了');
        }
        $shopInfo = $this->shop->find($shop_id);
        $data = [
            'uid' => $uid,
            'shop_id' => $shop_id,
            'parent_promoter' => 0,
            'parent_level' => 1,
            'promoter_shop_name' => $shopInfo->shop_name,
        ];
        $result = $this->promoter->create($data);
        return ResultHelper::json_result('申请成功，请等待店铺管理员审核', [], 200);
    }

    /*
     * 分销记录
     */
    public function createPromoterRecord(Request $request)
    {
        $promoter_id = $request->input('promoter_id');
        $goods_id = $request->input('goods_id');
        $shop_id = $request->input('shop_id');
        $source_uid = $request->input('source_uid');

        $promoter_info = $this->promoter->getPromoterById($promoter_id);
        if ($promoter_info) {
            $source_uid = $promoter_info->uid;
        }

        $result = $this->promoter->createPromoterRecord($source_uid, $this->uid, $shop_id, $goods_id, $promoter_id);

        if ($result['status']) {
            return ResultHelper::json_result('success');
        } else {
            return ResultHelper::json_error('fail');
        }
    }

    /*
     * 获取团长提现申请详细
     */
    public function getUserCommissionWithDraw(Request $request)
    {
        $id = $request->input('id');
        $info = $this->fxuser->getUserCommissionWithDraw($id);
        return ResultHelper::json_result('success', $info, 200);
    }

    public function getUserCommissionWithDrawLogs(Request $request)
    {
        $shop_id = $request->input('shop_id');
        $uid = $request->input('uid');
        $page = $request->input('page', 0);
        $size = $request->input('size', 10);
        $page = $page - 1;
        $list = $this->fxuser->getUserCommissionWithDrawLogs($uid, $shop_id, $page, $size);
        $data = [];
        foreach ($list as $key => $v) {
            $data[$key]['bank_name'] = $v->bank_name;
            $data[$key]['account_number'] = $v->account_number;
            $data[$key]['cash'] = $v->cash;
            $data[$key]['status'] = $v->status;
            $data[$key]['date'] = Carbon::parse($v->ask_for_time)->format('Y-m-d H:i:s');
        }
        return ResultHelper::json_result('success', $data, 200);
    }

    public function postTipsGetGoods(Request $request)
    {
        $order_no = $request->input('order_no');
        $result = $this->notice->createOrderPickupNotice($order_no);
        if ($result['status']) {
            NoticeHelper::send($result['data']);
            return ResultHelper::json_result('已成功提醒用户', null, 200);
        } else {
            return ResultHelper::json_error($result['msg']);
        }

        return ResultHelper::json_result('success', [], 200);
    }
}
