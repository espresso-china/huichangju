<?php
/**
 * Created by PhpStorm.
 * User: tacker
 * Date: 2019/3/25
 * Time: 3:03 PM
 */

namespace App\Api\Controllers\Admin;


use App\Model\NoticeTemplate;
use Illuminate\Http\Request;
use App\Helpers\ResultHelper;
use App\Repositories\NoticeRepository;

class NoticeController extends BaseController
{
    private $notice;

    public function __construct(NoticeRepository $notice)
    {
        parent::__construct();
        $this->notice = $notice;
    }

    public function noticeList(Request $request)
    {
        $page = $request->get('page') - 1;
        if ($page < 0) $page = 0;
        $pageSize = $request->get('limit');

        $shop_id = $this->shop_id > 0 ? $this->shop_id : 0;

        $where = $this->notice->getShopWhere($shop_id);

        $data = $this->notice->getNoticeList($page, $pageSize, $where);

        $count = $this->notice->getNoticeCount($where);

        return ResultHelper::resources($data, ['count' => $count]);
    }

    public function noticeTemplateList(Request $request)
    {
        $page = $request->get('page') - 1;
        if ($page < 0) $page = 0;
        $pageSize = $request->get('limit');

        $shop_id = $this->shop_id > 0 ? $this->shop_id : 0;

        $where = $this->notice->getShopTemplateWhere($shop_id);

        $data = $this->notice->getNoticeTemplateList($page, $pageSize, $where);

        $count = $this->notice->getNoticeTemplateCount($where);

        return ResultHelper::resources($data, ['count' => $count]);
    }

    public function noticeTemplateSave(Request $request)
    {
        $template_id = $request->input('template_id');
        $data = $request->all();

        unset($data['token']);
        $data['sign_name'] = $data['sign_name'] ?? '';

        if (!isset($data['is_enable'])) {
            $data['is_enable'] = 0;
        }

        $third_tpl_id = $request->input('third_tpl_id');

        if ($data['template_type'] == NoticeTemplate::TYPE_IS_SMS) {
            $data['third_tpl_id'] = json_encode($third_tpl_id);
        } else if (in_array($data['template_type'], [NoticeTemplate::TYPE_IS_MINIPG, NoticeTemplate::TYPE_IS_WECHAT])) {
            $data['third_tpl_id'] = trim($third_tpl_id);
        } else {
            unset($data['third_tpl_id']);
        }

        if ($template_id) {

            $this->notice->updateNoticeTemplate($template_id, $data);

            return ResultHelper::json_result('保存成功' . json_encode($third_tpl_id));
        } else {

            $this->notice->createNoticeTemplate($data);

            return ResultHelper::json_result('保存成功');
        }
    }

    public function noticeTemplateCodes(Request $request)
    {
        $type = $request->input('type', 'email');

        $items = $this->notice->getNoticeCodesByType($type, $this->shop_id > 0 ? false : true);

        return ResultHelper::json_result('success', $items);

    }

    public function noticeTemplateItems(Request $request)
    {
        $code = $request->input('code');

        $items = $this->notice->getNoticeItemsByCode($code);

        return ResultHelper::json_result('success', $items);
    }

}
