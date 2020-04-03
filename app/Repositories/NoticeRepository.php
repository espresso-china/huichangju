<?php
/**
 * Created by PhpStorm.
 * User: tacker
 * Date: 2019/3/25
 * Time: 2:29 PM
 */

namespace App\Repositories;

use Prettus\Repository\Contracts\RepositoryInterface;

interface NoticeRepository extends RepositoryInterface
{

    function getShopWhere($shop_id);

    function getNoticeList($page, $pageSize, $where = null);

    function getNoticeCount($where = null);

    function getShopTemplateWhere($shop_id);

    function getNoticeTemplateList($page, $pageSize, $where);

    function getNoticeTemplateCount($where);

    function getNoticeCodesByType($type, $isAdmin);

    function getNoticeItemsByCode($code);

    function updateNoticeTemplate($template_id, $data);

    function createNoticeTemplate($data);

    function createOrderPickupNotice($order_no);

    function createOrderCreateNotice($order_info);

    function createOrderPaySuccessNotice($order_info);

    function createOrderPickupCodeNotice($order_info);

    function createOrderPintuanSuccessNotice($order_pintuan_info);

    function createSmsCodeNotice($template_code, $phone, $code, $is_admin = true);

    function getShopTemplateInfo($type, $code, $shop_id = 0);

    function getWaitSendNotices($count);

}
