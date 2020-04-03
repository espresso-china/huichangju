<?php
/**
 * Created by PhpStorm.
 * User: tacker
 * Date: 2019/1/25
 * Time: 12:09 PM
 */

namespace App\Api\Controllers\Admin;

use App\Model\Bank;
use Session;
use Carbon\Carbon;
use Gregwar\Captcha\CaptchaBuilder;
use Illuminate\Http\Request;
use App\Helpers\ResultHelper;
use App\Helpers\WeChatHelper;
use App\Helpers\NoticeHelper;
use App\Model\Order;
use App\Repositories\AuthRepository;
use App\Repositories\AccountRepository;
use App\Repositories\ShopAccountRepository;
use App\Repositories\GoodsRepository;
use App\Repositories\ShopRepository;
use App\Repositories\OrderRepository;
use App\Repositories\NoticeRepository;

class CommonController extends BaseController
{
    private $goods, $shop, $order, $shop_account, $platform_account, $notice, $user;

    public function __construct(GoodsRepository $goods, ShopRepository $shop, OrderRepository $order, AuthRepository $user,
                                AccountRepository $platform_account, ShopAccountRepository $shop_account, NoticeRepository $notice)
    {
        parent::__construct();
        $this->goods = $goods;
        $this->shop = $shop;
        $this->order = $order;
        $this->shop_account = $shop_account;
        $this->platform_account = $platform_account;
        $this->notice = $notice;
        $this->user = $user;
    }

    public function dashboard()
    {
        $data = [];

        //商品总数
        $data['goods_total'] = $this->goods->getGoodsCountByShopId($this->shop_id);
        //今日订单金额
        $data['today_order_money'] = $this->order->getTodayOrderMoney($this->shop_id);
        //本月订单总笔数
        $data['current_month_order_total'] = $this->order->getCurrentMonthOrderCount($this->shop_id);
        //本月订单交易成功数
        $data['current_month_order_success_total'] = $this->order->getCurrentMonthOrderCount($this->shop_id, Order::ORDER_STATUS_IS_COMPLETE);

        //待支付订单数
        $data['wait_pay_order_total'] = $this->order->getWaitPayOrderCount($this->shop_id);
        //待提货订单数
        $data['wait_express_order_total'] = $this->order->getWaitExpressOrderCount($this->shop_id);
        //待完成订单数
        $data['wait_complete_order_total'] = $this->order->getWaitCompleteOrderCount($this->shop_id);
        //待回复评论订单数
        $data['wait_reply_order_total'] = $this->order->getWaitReplyOrderCount($this->shop_id);
        //库存预警商品数
        $data['stock_alarm_goods_total'] = $this->goods->getStockAlarmGoodsCount($this->shop_id);

        $data['yesterday_order_goods_total'] = $this->order->getYesterdayOrderGoodsCount($this->shop_id); //昨日销售商品数量
        $data['yesterday_order_goods_money'] = $this->order->getYesterdayOrderGoodsMoney($this->shop_id); //昨日销售商品金额

        $data['current_month_order_goods_total'] = $this->order->getCurrentMonthOrderGoodsCount($this->shop_id); //本月销售商品总数
        $data['current_month_order_goods_money'] = $this->order->getCurrentMonthOrderGoodsMoney($this->shop_id); //本月销售商品金额


        $month = [];
        $value = [];
        $current_month = Carbon::now()->month;
        for ($i = 1; $i <= $current_month; $i++) {
            $month[] = $i . '月';
            $value[] = 0;
        }

        //每月销售额
        $month_sales_moneys = array_combine($month, $value);
        //每月销售订单数
        $month_sale_orders = array_combine($month, $value);

        $month_sale_data = $this->order->getYearOrderGroupByMonth($this->shop_id, Carbon::now()->year);
        foreach ($month_sale_data as $item) {
            $month_sales_moneys[intval($item->order_month) . '月'] = sprintf("%.2f", $item->money / 1000);
            $month_sale_orders[intval($item->order_month) . '月'] = $item->total;
        }

        $data['month_sales_moneys'] = array_values($month_sales_moneys);
        $data['month_sale_orders'] = array_values($month_sale_orders);

        //每月成交额
        $month_success_moneys = array_combine($month, $value);
        //每月成交订单数
        $month_success_orders = array_combine($month, $value);

        $month_success_data = $this->order->getYearSuccessOrderGroupByMonth($this->shop_id, Carbon::now()->year);
        foreach ($month_success_data as $item) {
            $month_success_moneys[intval($item->order_month) . '月'] = sprintf("%.2f", $item->money / 1000);
            $month_success_orders[intval($item->order_month) . '月'] = $item->total;
        }
        $data['month_success_moneys'] = array_values($month_success_moneys);
        $data['month_success_orders'] = array_values($month_success_orders);

        $current_month = Carbon::now()->month;
        $current_year = Carbon::now()->year;

        $month_sale_money_percent = 100; //销售额增加百分比
        $month_sale_order_percent = 100; //订单数增加百分比
        $last_month_data = $this->order->getMonthOrderCount($this->shop_id, $current_year - 1, $current_month);
        if ($last_month_data && $last_month_data->money > 0) {
            $percent1 = ($month_sales_moneys[intval($current_month) . '月'] - $last_month_data->money) * 100 / $last_month_data->money;
            $month_sale_money_percent = sprintf('%.2f', $percent1);

            $percent2 = ($month_sale_orders[intval($current_month) . '月'] - $last_month_data->total) * 100 / $last_month_data->total;
            $month_sale_order_percent = sprintf('%.2f', $percent2);
        }
        $data['month_sale_money_percent'] = $month_sale_money_percent;
        $data['month_sale_order_percent'] = $month_sale_order_percent;

        $month_success_money_percent = 100; //成交额增加百分比
        $month_success_order_percent = 100; //订单数增加百分比
        $last_month_success_data = $this->order->getMonthSuccessOrderCount($this->shop_id, $current_year - 1, $current_month);
        if ($last_month_success_data && $last_month_success_data->money > 0) {
            $percent3 = ($month_success_moneys[intval($current_month) . '月'] - $last_month_success_data->money) * 100 / $last_month_success_data->money;
            $month_success_money_percent = sprintf('%.2f', $percent3);

            $percent4 = ($month_success_orders[intval($current_month) . '月'] - $last_month_success_data->total) * 100 / $last_month_success_data->total;
            $month_success_order_percent = sprintf('%.2f', $percent4);
        }
        $data['month_success_money_percent'] = $month_success_money_percent;
        $data['month_success_order_percent'] = $month_success_order_percent;

        //本月消费用户排行
        $data['current_month_top_members'] = $this->order->getCurrentMonthMemberMoneyTopN($this->shop_id, 10);

        //本月销售商品排行
        $data['current_month_top_goods'] = $this->order->getCurrentMonthGoodsSaleTopN($this->shop_id, 10);

        $data['shop_id'] = $this->shop_id;

        if ($this->shop_id) {
            $data['account_info'] = $this->shop_account->getShopAccountInfo($this->shop_id);
        } else {
            $data['account_info'] = $this->platform_account->getAccountInfo();
        }

        return ResultHelper::json_result('success', $data);
    }


    public function albumInfo()
    {

    }

    public function getGoodsCategoryOptions(Request $request)
    {
        $pageSize = $request->get('limit');

        $level = $request->get('level');

        $pid = $request->get('pid');

        $where = $this->goods->getCategoryWhere($level, $pid, 1);

        $data = $this->goods->getCategoryList(0, $pageSize, $where);

        $count = $this->goods->getCategoryCount($where);

        return ResultHelper::resources($data, ['count' => $count]);
    }

    public function getGoodsSpecOptions(Request $request)
    {
        $attrId = $request->input('attr_id', 0);

        $where = $this->goods->getSpecWhere($this->shop_id, $attrId);

        $data = $this->goods->getSpecList(0, 100, $where);

        return ResultHelper::resources($data);
    }

    public function getGoodsSpecValueOptions(Request $request)
    {
        $specId = $request->input('spec_id', 0);

        $where = $this->goods->getSpecValueWhere($specId);

        $data = $this->goods->getSpecValueList(0, 100, $where);

        return ResultHelper::resources($data);
    }

    public function getGoodsAttrOptions(Request $request)
    {
        $where = $this->goods->getAttributeWhere(1);

        $data = $this->goods->getAttributeList(0, 100, $where);

        return ResultHelper::resources($data);
    }

    public function getGoodsAttrValueOptions(Request $request)
    {
        $attr_id = $request->input('attr_id');

        $data = $this->goods->getAttributeValues($attr_id);

        return ResultHelper::resources($data);
    }

    public function getGoodsGroupOptions(Request $request)
    {
        $data = $this->goods->getGoodsGroupByShopId($this->shop_id);

        return ResultHelper::resources($data);
    }

    public function getGoodsBrandOptions(Request $request)
    {
        $data = $this->goods->getGoodsBrandByShopId($this->shop_id);

        return ResultHelper::resources($data);
    }

    public function getShopPickupOptions(Request $request)
    {
        $data = $this->shop->getShopPickupByShopId($this->shop_id);

        return ResultHelper::resources($data);
    }

    public function banks()
    {
        $banks = array_keys(WeChatHelper::getBankCodes());
        return ResultHelper::json_result('success', $banks);
    }

    public function bankBranch(Request $request)
    {
        $keywords = $request->input('keywords');
        $data = [];
        if (strlen($keywords) > 1) {
            $data = Bank::where('branch_name', 'like', '%' . trim($keywords) . '%')->take(20)->get();
        }
        return ResultHelper::json_result('success', $data);
    }

    public function captcha(Request $request)
    {
        header('Content-type: image/jpeg');

        $builder = new CaptchaBuilder;
        $builder->build();

        Session::start();
        Session::put('phrase', $builder->getPhrase());
        Session::save();

        setcookie('session', session()->getId(), Carbon::now()->addMinutes(5)->timestamp);

        $builder->output();
    }

    public function sendSmsCode(Request $request)
    {
        Session::setId($request->cookie('session'));
        Session::start();

        if ($request->input('code') == Session::get('phrase')) {
            $phone = $request->input('phone');
            $template_code = $request->input('type');

            $code = rand(100000, 999999);
            Session::put('sms_code_' . $phone, $code);
            Session::save();

            $notice_id = $this->notice->createSmsCodeNotice($template_code, $phone, $code);

            if ($notice_id['status']) {
                NoticeHelper::send($notice_id);
                return ResultHelper::json_result('success');
            } else {
                return ResultHelper::json_error($notice_id['msg']);
            }
        } else {
            return ResultHelper::json_error('图形验证码错误');
        }

    }

    public function userForget(Request $request)
    {
        Session::setId($request->cookie('session'));
        Session::start();

        $phone = $request->input('cellphone');
        $code = $request->input('vercode');

        \Log::info($code);

        if ($code && $code == Session::get('sms_code_' . $phone)) {
            Session::put('user_forget_verify', $phone);
            Session::save();
            return ResultHelper::json_result('success');
        } else {
            return ResultHelper::json_error('短信验证码错误');
        }
    }

    public function resetUserPass(Request $request)
    {
        Session::setId($request->cookie('session'));
        Session::start();

        $phone = Session::get('user_forget_verify', '');
        if (empty($phone)) {
            return ResultHelper::json_error('您没有验证手机号码的有效性', null, 201);
        }

        $password = $request->input('password');
        $this->user->resetPassword($phone, $password);

        Session::put('user_forget_verify', '');
        Session::save();

        return ResultHelper::json_result('密码重置成功');
    }

}
