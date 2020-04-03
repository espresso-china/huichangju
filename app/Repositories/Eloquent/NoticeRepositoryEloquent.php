<?php
/**
 * Created by PhpStorm.
 * User: tacker
 * Date: 2019/3/25
 * Time: 2:30 PM
 */

namespace App\Repositories\Eloquent;

use App\Model\OrderPickup;
use App\Model\Shop;
use App\Model\WechatFans;
use Carbon\Carbon;
use App\Helpers\NoticeHelper;
use App\Model\Notice;
use App\Model\NoticeTemplate;
use App\Model\NoticeTemplateItem;
use App\Model\NoticeTemplateType;
use App\Model\Order;
use App\Model\OrderGoods;
use App\Model\User;
use App\Model\Member;
use App\Repositories\NoticeRepository;

class NoticeRepositoryEloquent extends BaseRepository implements NoticeRepository
{

    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Notice::class;
    }

    function getShopWhere($shop_id)
    {
        $where = $this->model;
        if ($shop_id) {
            $where = $where->where('shop_id', $shop_id);
        }
        return $where;
    }

    function getNoticeList($page, $size, $where = null)
    {
        if (empty($where)) {
            $where = $this->model;
        }
        return $where->orderBy('id', 'desc')->skip($page * $size ?? 0)->take($size ?? 10)->get();
    }

    function getNoticeCount($where = null)
    {
        if ($where) {
            return $where->skip(0)->take(1)->count();
        }
        return $this->model->skip(0)->take(1)->count();
    }

    function getShopTemplateWhere($shop_id)
    {
        $where = $this->switchModel(NoticeTemplate::class);
        if ($shop_id) {
            $where = $where->where('shop_id', $shop_id);
        }
        return $where;
    }

    function getNoticeTemplateList($page, $size, $where)
    {
        if (empty($where)) {
            $where = $this->switchModel(NoticeTemplate::class);
        }
        return $where->orderBy('template_id', 'asc')->skip($page * $size ?? 0)->take($size ?? 10)->get();
    }

    function getNoticeTemplateCount($where)
    {
        if (empty($where)) {
            $where = $this->switchModel(NoticeTemplate::class);
        }
        return $where->skip(0)->take(1)->count();;
    }

    function getNoticeCodesByType($type, $isAdmin)
    {
        switch ($type) {
            case 'email':
                $where = NoticeTemplateType::whereIn('template_type', ['email', 'all']);
                break;
            case 'sms':
                $where = NoticeTemplateType::whereIn('template_type', ['sms', 'all']);
                break;
            default:
                $where = NoticeTemplateType::where('template_type', 'all');
                break;
        }
        if (!$isAdmin) {
            $where = $where->where('is_system', 0);
        }
        return $where->get();
    }

    function getNoticeItemsByCode($code)
    {
        return NoticeTemplateItem::whereRaw(" find_in_set('{$code}', type_ids) ")->orderBy('order', 'asc')->get();
    }

    function updateNoticeTemplate($template_id, $data)
    {
        return NoticeTemplate::where('template_id', $template_id)->update($data);
    }

    function createNoticeTemplate($data)
    {
        return NoticeTemplate::create($data);
    }

    function getShopTemplateInfo($type, $code, $shop_id = 0)
    {
        $info = NoticeTemplate::where('template_type', $type)->where('template_code', $code)
            ->where('is_enable', 1)->where('shop_id', $shop_id)->first();
        if (empty($info)) {
            $info = NoticeTemplate::where('template_type', $type)->where('template_code', $code)
                ->where('is_enable', 1)->first();
        }
        return $info;
    }

    function createOrderPickupNotice($order_no)
    {
        $order_info = Order::where('order_no', $order_no)->first();
        if (empty($order_info)) {
            return ['status' => false, 'msg' => '订单不存在'];
        }

//        if ($order_info->shipping_status != Order::SHIPPING_STATUS_IS_WAIT) {
//            return ['status' => false, 'msg' => '订单已提货，不需要提醒'];
//        }

        $notice_template_code = 'order_pickup';
        $template_type_info = NoticeTemplateType::where('template_code', $notice_template_code)->first();
        $template_items = $this->getNoticeItemsByCode($notice_template_code);
        $order_params = $this->getOrderNoticeParams($order_info);

        $params = [];
        $show_params = [];
        foreach ($template_items as $item) {
            if (isset($order_params[$item->replace_name])) {
                $params[$item->replace_name] = $order_params[$item->replace_name];
                $show_params[$item->show_name] = $order_params[$item->replace_name];
            }
        }

        $notice_ids = [];
        $notice_template_types = NoticeTemplate::getTypeNames();
        foreach ($notice_template_types as $type => $type_name) {

            $template = $this->getShopTemplateInfo($type, $notice_template_code, $order_info->shop_id);
            if (empty($template)) {
                continue;
            }

            $data = [
                'shop_id' => $order_info->shop_id,
                'notice_type' => $template_type_info->type_id,
                'notice_message' => $template->template_content,
                'is_enable' => 1,
                'to_admin' => 0,
                'to_uid' => $order_info->buyer_id,
                'to_tag_id' => 0,
                'to' => $this->getNoticeTo($type, $order_info->buyer_id, false),
                'status' => Notice::STATUS_IS_WAIT_SEND,
                'total' => 1,
                'notice_method' => $template->template_type,
                'params' => json_encode($params),
                'third_tpl_id' => $template->third_tpl_id,
                'create_time' => Carbon::now()->toDateTimeString()
            ];

            $notice_ids[] = Notice::insertGetId($data);
        }

        if ($notice_ids) {
            return ['status' => true, 'data' => $notice_ids];
        } else {
            return ['status' => false, 'msg' => '没有启用短信提醒通知模版'];
        }
    }

    function createOrderCreateNotice($order_info)
    {
        return $this->createOrderNotice('create_order', $order_info);
    }

    public function createOrderPaySuccessNotice($order_info)
    {
        return $this->createOrderNotice('pay_success', $order_info);
    }

    public function createOrderPickupCodeNotice($order_info)
    {
        if ($order_info->shipping_type == Order::SHIPPING_TYPE_IS_SELF) {

            $template_type_info = NoticeTemplateType::where('template_code', 'pickup_code')->first();

            $notice_count = Notice::where('to_uid', $order_info->buyer_id)
                ->where('create_time', '>=', Carbon::now()->addSeconds(-120)->toDateTimeString())
                ->where('notice_type', $template_type_info->type_id)
                ->where('to_admin', 0)
                ->orderBy('id', 'desc')->count();

            if ($notice_count) {
                return ['status' => false, 'msg' => '120秒内不能重发'];
            }

            $number = rand(100000, 999999);
            $pickupInfo = OrderPickup::where('order_id', $order_info->order_id)->first();
            if ($pickupInfo) {
                $pickupInfo->buyer_code = $number;
                $pickupInfo->save();

                $params = [
                    'shop_addr' => $pickupInfo->address,
                    'shop_tel' => $pickupInfo->phone,
                    'number' => $number
                ];

                return $this->createOrderNotice('pickup_code', $order_info, $params);
            }
        } else {
            return ['status' => false, 'msg' => '不是自提订单，不需要发送取货码'];
        }
    }

    public function createOrderPintuanSuccessNotice($order_pintuan_info)
    {
        $number = rand(100000, 999999);
        $pickupInfo = OrderPickup::where('order_id', $order_pintuan_info->order_id)->first();
        $order_info = Order::where('order_id', $order_pintuan_info->order_id)->first();
        if ($pickupInfo) {
            $pickupInfo->buyer_code = $number;
            $pickupInfo->save();

            $params = [
                'shop_addr' => $pickupInfo->address,
                'shop_tel' => $pickupInfo->phone,
                'number' => $number
            ];

            return $this->createOrderNotice('pintuan_success', $order_info, $params);
        } else {
            return $this->createOrderNotice('pintuan_success2', $order_info, []);
        }
    }

    private function createOrderNotice($notice_template_code, $order_info, $params = [])
    {
        $template_type_info = NoticeTemplateType::where('template_code', $notice_template_code)->first();
        $template_items = $this->getNoticeItemsByCode($notice_template_code);
        $order_params = $this->getOrderNoticeParams($order_info);

        //$show_params = [];
        foreach ($template_items as $item) {
            if (isset($order_params[$item->replace_name]) && !isset($params[$item->replace_name])) {
                $params[$item->replace_name] = $order_params[$item->replace_name];
                //$show_params[$item->show_name] = $order_params[$item->replace_name];
            }
        }

        $notice_ids = [];
        $notice_template_types = NoticeTemplate::getTypeNames();
        foreach ($notice_template_types as $type => $type_name) {

            $template = $this->getShopTemplateInfo($type, $notice_template_code, $order_info->shop_id);
            if (empty($template)) {
                continue;
            }

            $data = [
                'shop_id' => $order_info->shop_id,
                'notice_type' => $template_type_info->type_id,
                'notice_message' => $template->template_content,
                'is_enable' => 1,
                'to_admin' => 0,
                'to_uid' => $order_info->buyer_id,
                'to_tag_id' => 0,
                'to' => $this->getNoticeTo($type, $order_info->buyer_id, false),
                'status' => Notice::STATUS_IS_WAIT_SEND,
                'total' => 1,
                'notice_method' => $template->template_type,
                'params' => json_encode($params),
                'third_tpl_id' => $template->third_tpl_id,
                'create_time' => Carbon::now()->toDateTimeString()
            ];

            $notice_ids[] = Notice::insertGetId($data);
        }

        if ($notice_ids) {
            return ['status' => true, 'data' => $notice_ids];
        } else {
            return ['status' => false, 'msg' => '没有启用短信提醒通知模版'];
        }
    }

    private function getNoticeTo($type, $uid, $admin = true)
    {
        switch ($type) {
            case NoticeTemplate::TYPE_IS_SMS:
                if ($admin) {
                    $phone = User::where('id', $uid)->value('phone');
                } else {
                    $phone = Member::where('uid', $uid)->value('phone');
                }
                return $phone;
            case NoticeTemplate::TYPE_IS_EMAIL:
                if ($admin) {
                    $email = User::where('id', $uid)->value('user_email');
                } else {
                    $email = '';
                }
                return $email;
            case NoticeTemplate::TYPE_IS_WECHAT:
                if ($admin) {
                    $openid = '';
                } else {
                    $openid = WechatFans::where('uid', $uid)->where('type', 2)->value('openid');
                }
                return $openid;
            case NoticeTemplate::TYPE_IS_MINIPG:
                if ($admin) {
                    $openid = '';
                } else {
                    $openid = WechatFans::where('uid', $uid)->where('type', 1)->value('openid');
                }
                return $openid;
            default:
                return '';
        }
    }

    private function getOrderNoticeParams($order_info, $goods_id = 0)
    {
        if ($goods_id) {
            $order_goods = OrderGoods::where('order_id', $order_info->order_id)->where('goods_id', $goods_id)->get();
        } else {
            $order_goods = OrderGoods::where('order_id', $order_info->order_id)->get();
        }
        $goods_name = [];
        $goods_sku = [];
        foreach ($order_goods as $goods) {
            $goods_name[] = $goods->goods_name;
            $goods_sku[] = $goods->sku_name;
        }

        $shop_info = Shop::where('shop_id', $order_info->shop_id)->first();

        $params = [
            'shop_name' => $order_info->shop_name,
            'user_name' => $order_info->user_name,
            'goods_name' => implode(',', $goods_name),
            'goods_sku' => implode(',', $goods_sku),
            'order_no' => $order_info->order_no,
            'order_money' => $order_info->order_money,
            'goods_money' => $order_info->goods_money,
            'shop_addr' => $shop_info->shop_address,
            'shop_tel' => $shop_info->shop_phone,
        ];

        return $params;
    }

    function createSmsCodeNotice($template_code, $phone, $code, $is_admin = true)
    {

        $typeInfo = NoticeTemplateType::where('template_code', $template_code)->first();
        if (empty($typeInfo)) {
            return ['status' => false, 'msg' => '短信模版类型未定义'];
        }

        $templateInfo = $this->getShopTemplateInfo('sms', $template_code, 0);
        if (empty($templateInfo)) {
            return ['status' => false, 'msg' => '此类型短信模版未启用'];
        }

        $params = ['code' => $code];

        switch ($template_code) {
            case 'forgot_password':
                if ($is_admin) {
                    $userInfo = User::where('phone', $phone)->first();
                    if (empty($userInfo)) {
                        return ['status' => false, 'msg' => '用户不存在'];
                    }

                    $data = [
                        'shop_id' => $userInfo->shop_id,
                        'notice_type' => $typeInfo->type_id,
                        'notice_message' => $templateInfo->template_content,
                        'is_enable' => 1,
                        'to_admin' => $is_admin ? 1 : 0,
                        'to_uid' => $userInfo->id,
                        'to_tag_id' => 0,
                        'to' => $phone,
                        'status' => Notice::STATUS_IS_WAIT_SEND,
                        'total' => 1,
                        'notice_method' => $templateInfo->template_type,
                        'params' => json_encode($params),
                        'third_tpl_id' => $templateInfo->third_tpl_id,
                        'create_time' => Carbon::now()->toDateTimeString()
                    ];

                    $notice_id = Notice::insertGetId($data);

                    return ['status' => true, 'data' => $notice_id];
                }
                break;
            case 'register_validate':
                $data = [
                    'shop_id' => 0,
                    'notice_type' => $typeInfo->type_id,
                    'notice_message' => $templateInfo->template_content,
                    'is_enable' => 1,
                    'to_admin' => 0,
                    'to_uid' => 0,
                    'to_tag_id' => 0,
                    'to' => $phone,
                    'status' => Notice::STATUS_IS_WAIT_SEND,
                    'total' => 1,
                    'notice_method' => $templateInfo->template_type,
                    'params' => json_encode($params),
                    'third_tpl_id' => $templateInfo->third_tpl_id,
                    'create_time' => Carbon::now()->toDateTimeString()
                ];

                $notice_id = Notice::insertGetId($data);

                return ['status' => true, 'data' => $notice_id];
                break;
            case 'bind_mobile':

                break;
        }

        return ['status' => false, 'msg' => '未定义此类型短信数据处理方法'];
    }

    function getWaitSendNotices($count)
    {
        return $this->model->where('status', Notice::STATUS_IS_WAIT_SEND)->orderBy('id', 'asc')->take($count)->get();
    }
}
