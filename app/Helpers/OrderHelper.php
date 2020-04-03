<?php
/**
 * Created by PhpStorm.
 * User: tacker
 * Date: 2019/3/8
 * Time: 12:25 PM
 */

namespace App\Helpers;

use Cache;
use App\Model\Order;
use App\Model\OrderGoods;
use App\Model\OrderRefund;
use App\Model\OrderPayment;

class OrderHelper
{
    public static function getSerialNo()
    {
        return date('ymdHis') . rand(1000, 9999);
    }

    public static function createOutTradeNo()
    {
        $cache = Cache::get("xshop_out_trade_no" . time());
        if (empty($cache)) {
            Cache::set("xshop_out_trade_no" . time(), 1000);
            $cache = Cache::get("xshop_out_trade_no" . time());
        } else {
            $cache = $cache + 1;
            Cache::put("xshop_out_trade_no" . time(), $cache, 1);
        }
        $no = time() . rand(1000, 9999) . $cache;
        return $no;
    }

    public static function createOrderRefundNo()
    {
        $cache = Cache::get("xshop_order_refund_no" . time());
        if (empty($cache)) {
            Cache::set("xshop_order_refund_no" . time(), 1000);
            $cache = Cache::get("xshop_order_refund_no" . time());
        } else {
            $cache = $cache + 1;
            Cache::put("xshop_order_refund_no" . time(), $cache, 1);
        }
        $no = time() . rand(1000, 9999) . $cache;
        return $no;
    }

    public static function createOrderNo($shopId)
    {
        $time_str = 'OD' . date('ymd') . str_pad($shopId, 6, '0', STR_PAD_LEFT);

        $order_info = Order::where('shop_id', $shopId)->orderBy('order_id', 'desc')->first();

        if (!empty($order_info)) {
            $order_no_max = $order_info->order_no;
            if (empty($order_no_max)) {
                $num = 1;
            } else {
                if (substr($time_str, 0, 14) == substr($order_no_max, 0, 14)) {
                    $max_no = substr($order_no_max, 14, 8);
                    $num = $max_no * 1 + 1;
                } else {
                    $num = 1;
                }
            }
        } else {
            $num = 1;
        }

        return $time_str . sprintf("%08d", $num);
    }

    /**
     * 获取订单所有可能的订单状态
     */
    public static function getOrderCommonStatus()
    {
        $status = array(
            array(
                'status_id' => Order::ORDER_STATUS_IS_WAIT_PAY,
                'status_name' => '待付款',
                'is_refund' => 0, // 是否可以申请退款
                'admin_operation' => array(
                    '0' => array(
                        'no' => 'pay',
                        'name' => '线下支付',
                        'color' => '#FF9800'
                    ),
                    '1' => array(
                        'no' => 'close',
                        'color' => '#E61D1D',
                        'name' => '交易关闭'
                    ),
//                    '2' => array(
//                        'no' => 'adjust_price',
//                        'color' => '#4CAF50',
//                        'name' => '修改价格'
//                    ),
                    '3' => array(
                        'no' => 'remark',
                        'color' => '#8e8c8c',
                        'name' => '添加备注'
                    )
                ),
                'member_operation' => array(
                    '0' => array(
                        'no' => 'close',
                        'name' => '关闭订单',
                        'color' => '#999999',
                        'type' => 'default',
                    ),
                    '1' => array(
                        'no' => 'pay',
                        'name' => '去支付',
                        'color' => '#F15050',
                        'type' => 'primary',
                    )
                )
            ),
            array(
                'status_id' => Order::ORDER_STATUS_IS_WAIT_EXPRESS,
                'status_name' => '待发货',
                'is_refund' => 1,
                'admin_operation' => array(
                    '0' => array(
                        'no' => 'delivery',
                        'color' => 'green',
                        'name' => '发货'
                    ),
                    '1' => array(
                        'no' => 'remark',
                        'color' => '#8e8c8c',
                        'name' => '添加备注'
                    ),
                    '2' => array(
                        'no' => 'update_address',
                        'color' => '#51A351',
                        'name' => '修改地址'
                    )
                ),
                'member_operation' => array()
            ),
            array(
                'status_id' => Order::ORDER_STATUS_IS_EXPRESSED,
                'status_name' => '已发货',
                'is_refund' => 1,
                'admin_operation' => array(
                    '0' => array(
                        'no' => 'remark',
                        'color' => '#8e8c8c',
                        'name' => '添加备注'
                    ),
                    '1' => array(
                        'no' => 'logistics',
                        'color' => '#ccc',
                        'name' => '查看物流'
                    ),
                    '2' => array(
                        'no' => 'get_delivery',
                        'name' => '确认收货',
                        'color' => '#FF6600'
                    )
                ),
                'member_operation' => array(
                    '0' => array(
                        'no' => 'get_delivery',
                        'name' => '确认收货',
                        'color' => '#FF6600',
                        'type' => 'primary',
                    ),
                    '1' => array(
                        'no' => 'logistics',
                        'color' => '#ccc',
                        'name' => '查看物流',
                        'type' => 'default'
                    )
                )
            ),
            array(
                'status_id' => Order::ORDER_STATUS_IS_RECEIVED,
                'status_name' => '已收货',
                'is_refund' => 0,
                'admin_operation' => array(
                    '0' => array(
                        'no' => 'remark',
                        'color' => '#8e8c8c',
                        'name' => '添加备注'
                    ),
                    '1' => array(
                        'no' => 'logistics',
                        'color' => '#ccc',
                        'name' => '查看物流'
                    )
                ),
                'member_operation' => array(
                    '0' => array(
                        'no' => 'logistics',
                        'color' => '#ccc',
                        'name' => '查看物流',
                        'type' => 'default'
                    ),
                    '1' => array(
                        'no' => 'complete',
                        'name' => '确认完成',
                        'color' => '#F15050',
                        'type' => 'warning',
                    )
                )
            ),
            array(
                'status_id' => Order::ORDER_STATUS_IS_COMPLETE,
                'status_name' => '已完成',
                'is_refund' => 0,
                'admin_operation' => array(
                    '0' => array(
                        'no' => 'remark',
                        'color' => '#8e8c8c',
                        'name' => '添加备注'
                    ),
                    '1' => array(
                        'no' => 'logistics',
                        'color' => '#ccc',
                        'name' => '查看物流'
                    ),

                ),
                'member_operation' => array(
                    '0' => array(
                        'no' => 'logistics',
                        'color' => '#ccc',
                        'name' => '查看物流',
                        'type' => 'default'
                    )
                )
            ),
            array(
                'status_id' => Order::ORDER_STATUS_IS_CLOSED,
                'status_name' => '已关闭',
                'is_refund' => 0,
                'admin_operation' => array(
                    '0' => array(
                        'no' => 'remark',
                        'color' => '#8e8c8c',
                        'name' => '添加备注'
                    ),
                    '1' => array(
                        'no' => 'delete_order',
                        'color' => '#ff0000',
                        'name' => '删除订单'
                    )
                ),
                'member_operation' => array(
                    '0' => array(
                        'no' => 'delete_order',
                        'color' => '#ff0000',
                        'name' => '删除订单',
                        'type' => 'danger',
                    )
                )
            ),
            array(
                'status_id' => Order::ORDER_STATUS_IS_REFUND,
                'status_name' => '退款中',
                'is_refund' => 1,
                'admin_operation' => array(
                    '0' => array(
                        'no' => 'remark',
                        'color' => '#8e8c8c',
                        'name' => '添加备注'
                    )
                ),
                'member_operation' => array()
            )
        );
        return $status;
    }

    /**
     * 获取自提订单相关状态
     */
    public static function getSinceOrderStatus()
    {
        $status = array(
            array(
                'status_id' => Order::ORDER_STATUS_IS_WAIT_PAY,
                'status_name' => '待付款',
                'is_refund' => 0, // 是否可以申请退款
                'admin_operation' => array(
                    '0' => array(
                        'no' => 'pay',
                        'name' => '线下支付',
                        'color' => '#FF9800'
                    ),
                    '1' => array(
                        'no' => 'close',
                        'color' => '#E61D1D',
                        'name' => '交易关闭'
                    ),
//                    '2' => array(
//                        'no' => 'adjust_price',
//                        'color' => '#4CAF50',
//                        'name' => '修改价格'
//                    ),
                    '3' => array(
                        'no' => 'remark',
                        'color' => '#8e8c8c',
                        'name' => '添加备注'
                    )
                ),
                'member_operation' => array(
                    '0' => array(
                        'no' => 'close',
                        'name' => '关闭订单',
                        'color' => '#999999',
                        'type' => 'default',
                    ),
                    '1' => array(
                        'no' => 'pay',
                        'name' => '去支付',
                        'color' => '#F15050',
                        'type' => 'primary',
                    )
                )
            ),
            array(
                'status_id' => Order::ORDER_STATUS_IS_WAIT_EXPRESS,
                'status_name' => '待提货',
                'is_refund' => 1,
                'admin_operation' => array(
                    '0' => array(
                        'no' => 'pickup',
                        'color' => '#FF9800',
                        'name' => '提货'
                    ),
                    '1' => array(
                        'no' => 'remark',
                        'color' => '#8e8c8c',
                        'name' => '添加备注'
                    )
                ),
                'member_operation' => array()
            ),
            array(
                'status_id' => Order::ORDER_STATUS_IS_RECEIVED,
                'status_name' => '已提货',
                'is_refund' => 0,
                'admin_operation' => array(

                    '0' => array(
                        'no' => 'remark',
                        'color' => '#8e8c8c',
                        'name' => '添加备注'
                    ),
                    '1' => array(
                        'no' => 'pickup_view',
                        'color' => '#51A351',
                        'name' => '查看提货信息'
                    )
                ),
                'member_operation' => array(
                    '0' => array(
                        'no' => 'complete',
                        'name' => '确认完成',
                        'color' => '#F15050',
                        'type' => 'warning',
                    )
                )
            ),
            array(
                'status_id' => Order::ORDER_STATUS_IS_COMPLETE,
                'status_name' => '已完成',
                'is_refund' => 0,
                'admin_operation' => array(
                    '0' => array(
                        'no' => 'remark',
                        'color' => '#8e8c8c',
                        'name' => '添加备注'
                    ),
                    '1' => array(
                        'no' => 'pickup_view',
                        'color' => '#51A351',
                        'name' => '查看提货信息'
                    )
                ),
                'member_operation' => array()
            ),
            array(
                'status_id' => Order::ORDER_STATUS_IS_CLOSED,
                'status_name' => '已关闭',
                'is_refund' => 0,
                'admin_operation' => array(
                    '0' => array(
                        'no' => 'remark',
                        'color' => '#8e8c8c',
                        'name' => '添加备注'
                    ),
                    '1' => array(
                        'no' => 'delete_order',
                        'color' => '#ff0000',
                        'name' => '删除订单'
                    )
                ),
                'member_operation' => array(
                    '0' => array(
                        'no' => 'delete_order',
                        'color' => '#ff0000',
                        'name' => '删除订单',
                        'type' => 'danger',
                    )
                )
            ),
            array(
                'status_id' => Order::ORDER_STATUS_IS_REFUND,
                'status_name' => '退款中',
                'is_refund' => 1,
                'admin_operation' => array(
                    '0' => array(
                        'no' => 'remark',
                        'color' => '#8e8c8c',
                        'name' => '添加备注'
                    )
                ),
                'member_operation' => array()
            )
        );
        return $status;
    }

    /**
     * 获取订单退款操作状态
     */
    public static function getRefundStatus()
    {
        $refund_status = array(
            '0' => array(
                'status_id' => OrderGoods::REFUND_STATUS_IS_APPLY,
                'status_name' => '买家申请退款',
                'status_desc' => '发起了退款申请,等待卖家处理',
                'refund_operation' => array(
                    '0' => array(
                        'no' => 'agree',
                        'name' => '同意',
                        'color' => '#FF9800'
                    ),
                    '1' => array(
                        'no' => 'refuse',
                        'name' => '拒绝',
                        'color' => 'rgba(244, 67, 54, 0.87)'
                    )
                )
            ),
            '1' => array(
                'status_id' => OrderGoods::REFUND_STATUS_IS_WAIT_GOODS,
                'status_name' => '等待买家退货',
                'status_desc' => '卖家已同意退款申请,等待买家退货',
                'refund_operation' => array()
            ),
            '2' => array(
                'status_id' => OrderGoods::REFUND_STATUS_IS_RECEIVE_GOODS,
                'status_name' => '等待卖家确认收货',
                'status_desc' => '买家已退货,等待卖家确认收货',
                'refund_operation' => array(
                    '0' => array(
                        'no' => 'confirm_receipt',
                        'name' => '确认收货',
                        'color' => '#4CAF50'
                    )
                )
            ),
            '3' => array(
                'status_id' => OrderGoods::REFUND_STATUS_IS_WAIT_PAY,
                'status_name' => '等待卖家确认退款',
                'status_desc' => '卖家同意退款',
                'refund_operation' => array(
                    '0' => array(
                        'no' => 'confirm_refund',
                        'name' => '确认退款',
                        'color' => '#4CAF50'
                    )
                )
            ),
            '4' => array(
                'status_id' => OrderGoods::REFUND_STATUS_IS_SUCCESS,
                'status_name' => '退款已成功',
                'status_desc' => '卖家退款给买家，本次退款申请成功',
                'refund_operation' => array()
            ),
            '5' => array(
                'status_id' => OrderGoods::REFUND_STATUS_IS_REFUSE,
                'status_name' => '退款已拒绝',
                'status_desc' => '卖家拒绝本次退款，本次退款申请结束',
                'refund_operation' => array()
            ),
            '6' => array(
                'status_id' => OrderGoods::REFUND_STATUS_IS_CLOSE,
                'status_name' => '退款已关闭',
                'status_desc' => '主动撤销退款，退款关闭',
                'refund_operation' => array()
            ),
            '7' => array(
                'status_id' => OrderGoods::REFUND_STATUS_IS_RETRY,
                'status_name' => '退款申请不通过',
                'status_desc' => '拒绝了本次退款申请,等待买家修改',
                'refund_operation' => array()
            )
        );
        return $refund_status;
    }

    public static function convertYsepayDivisionStatusCode($code)
    {
        $code = '' . $code;
        if (strlen($code) == 4) {
            switch ($code) {
                case '0000':
                case '0001':
                    return OrderPayment::DIVISION_STATUS_IS_ACCEPT;
                case '9999':
                default:
                    return OrderPayment::DIVISION_STATUS_IS_FAIL;
            }
        } else {
            switch ($code) {
                case '01':
                    return OrderPayment::DIVISION_STATUS_IS_ACCEPT;
                case '02':
                    return OrderPayment::DIVISION_STATUS_IS_READY;
                case '00';
                    return OrderPayment::DIVISION_STATUS_IS_SUCCESS;
                case '99':
                default:
                    return OrderPayment::DIVISION_STATUS_IS_FAIL;
            }
        }
    }

}
