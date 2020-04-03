<?php
/**
 * Created by PhpStorm.
 * User: tacker
 * Date: 2018/7/31
 * Time: 下午7:32
 */

namespace App\Api\Controllers\App\Customer;

use App\Helpers\NoticeHelper;
use App\Helpers\SettingHelper;
use App\Helpers\UrlHelper;
use App\Model\Activity;
use App\Model\ActivityJoin;
use App\Model\Goods;
use App\Model\Member;
use App\Model\MemberAccountRecord;
use App\Model\MemberFavorite;
use App\Model\MemberLogs;
use App\Model\MemberSuggest;
use App\Model\Order;
use App\Model\ShopApply;
use App\Model\WechatFans;
use App\Repositories\FxPromoterRepository;
use App\Repositories\AttachmentRepository;
use App\Repositories\GoodsRepository;
use App\Repositories\MemberFavoritesRepository;
use App\Repositories\NewsRepository;
use App\Repositories\NoticeRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ShopApplyRepository;
use App\Repositories\TaoWenRepository;
use App\Repositories\WechatFansRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Session;
use Illuminate\Http\Request;
use App\Helpers\ResultHelper;
use App\Repositories\MemberRepository;
use App\Api\Controllers\App\ApiController;
use Image, File;

class MemberController extends ApiController
{
    private $member, $fans, $order, $news, $favorites, $goods, $attachment, $promoter, $notice, $taowen, $favority;

    public function __construct(MemberRepository $member, WechatFansRepository $fans, OrderRepository $order,
                                ShopApplyRepository $apply, NewsRepository $news, MemberFavoritesRepository $favorites,
                                GoodsRepository $goods, AttachmentRepository $attachment,
                                FxPromoterRepository $promoter, NoticeRepository $notice, TaoWenRepository $taowen, MemberFavoritesRepository $favority)
    {
        parent::__construct();
        $this->member = $member;
        $this->fans = $fans;
        $this->order = $order;
        $this->apply = $apply;
        $this->news = $news;
        $this->goods = $goods;
        $this->favorites = $favorites;
        $this->attachment = $attachment;
        $this->promoter = $promoter;
        $this->notice = $notice;
        $this->taowen = $taowen;
        $this->favority = $favority;
    }

    /*
     * 会员绑定注册
     */
    public function register(Request $request)
    {
        $encryptedData = $request->input('encryptedData');
        $iv = $request->input('iv');

        $wxUserInfo = $this->fans->getWeChatUserBySession3rd($this->session3rd);

        if (!$wxUserInfo) {
            return ResultHelper::json_error('数据错误');
        }
        try {
            $result = $this->program->encryptor->decryptData($wxUserInfo->session, $iv, $encryptedData);

            \Log::info(is_string($result) ? $result : json_encode($result));

        } catch (\Exception $exception) {
            return ResultHelper::json_error('授权请求过期，请重新获取授权', null, 202);
        }

        if (empty($result)) {
            return ResultHelper::json_error('绑定数据解密错误');
        }

        $phone = $result['phoneNumber'];

        if (empty($phone)) {
            return ResultHelper::json_error('手机号码不能为空');
        }

        $memberInfo = $this->member->getInfoByPhone($phone);

        if ($memberInfo) {
            $data = [
                'memberInfo' => $memberInfo,
                'uid' => $memberInfo->uid
            ];
            return ResultHelper::json_error('该号码已经绑定过了', $data, 201);
        } else {
            $data = [
                'phone' => $phone,
                'member_name' => $wxUserInfo->nickname,
                'member_level' => 1,
                'member_point' => 0,
                'member_balance' => 0,
                'avatar' => $wxUserInfo->headimgurl,
                'wx_uid' => $wxUserInfo->fans_id
            ];
            $res = $this->member->create($data);
            if (empty($res)) {
                return ResultHelper::json_error('用户创建失败');
            }

            $wxUserInfo->uid = $res->uid;
            if ($wxUserInfo->unionid) {
                $this->fans->updateUidByUnionId($wxUserInfo->unionid, $res->uid);
            } else {
                $this->fans->updateUidByOpenId(WechatFans::TYPE_IS_MINI_PROGRAM, $wxUserInfo->openid, $res->uid);
            }

            $data = [
                'memberInfo' => $res,
                'uid' => $res->uid
            ];
            return $res ? ResultHelper::json_result('注册成功', $data, 200) : ResultHelper::json_error('注册失败');
        }
    }

    public function registerByPhone(Request $request)
    {
        $wxUserInfo = $this->fans->getWeChatUserBySession3rd($this->session3rd);
        if (!$wxUserInfo) {
            return ResultHelper::json_error('数据错误');
        }
        $phone = $request->get('phone');
        if (empty($phone)) {
            return ResultHelper::json_error('手机号码不能为空');
        }

        $memberInfo = $this->member->getInfoByPhone($phone);

        if ($memberInfo) {
            $data = [
                'memberInfo' => $memberInfo,
                'uid' => $memberInfo->uid
            ];
            return ResultHelper::json_error('该号码已经绑定过了', $data, 201);
        } else {
            $data = [
                'phone' => $phone,
                'member_name' => $wxUserInfo->nickname,
                'member_level' => 1,
                'member_point' => 0,
                'member_balance' => 0,
                'avatar' => $wxUserInfo->headimgurl,
                'wx_uid' => $wxUserInfo->fans_id
            ];
            $res = $this->member->create($data);

            if (empty($res)) {
                return ResultHelper::json_error('用户创建失败');
            }

            $wxUserInfo->uid = $res->uid;
            if ($wxUserInfo->unionid) {
                $this->fans->updateUidByUnionId($wxUserInfo->unionid, $res->uid);
            } else {
                $this->fans->updateUidByOpenId(WechatFans::TYPE_IS_MINI_PROGRAM, $wxUserInfo->openid, $res->uid);
            }

            $data = [
                'memberInfo' => $res,
                'uid' => $res->uid
            ];
            return $res ? ResultHelper::json_result('注册成功', $data, 200) : ResultHelper::json_error('注册失败');
        }
    }

    public function sendSmsCode(Request $request)
    {

        $phone = $request->input('phone');
        $memberInfo = $this->member->getInfoByPhone($phone);
        if ($memberInfo) {
            return ResultHelper::json_error('该号码已经被注册过了');
        }
        $template_code = 'register_validate';
        $code = rand(100000, 999999);
        $notice_id = $this->notice->createSmsCodeNotice($template_code, $phone, $code);

        if ($notice_id['status']) {
            NoticeHelper::send($notice_id);
            return ResultHelper::json_result('success', $code, 200);
        } else {
            return ResultHelper::json_error($notice_id['msg']);
        }

    }

    /*
     * 小程序设置页获取用户基础、日志信息
     */
    public function getUserInfo(Request $request)
    {
        $uid = $request->input('uid');
        if (empty($uid)) {
            return ResultHelper::json_error('非法请求');
        }
        $userInfo = $this->member->find($uid);
        if (!$userInfo) {
            return ResultHelper::json_error('找不到用户');
        }
        $data = [];
        $data['userInfo'] = $userInfo;
        $data['collects_goods'] = MemberFavorite::where('uid', $uid)->where('fav_type', 'goods')->count();
        $data['collects_shops'] = MemberFavorite::where('uid', $uid)->where('fav_type', 'shop')->count();
        $data['goods_logs'] = MemberLogs::where('uid', $uid)->count();
        // $info = MemberAccount::where('uid', $uid)->first();

        $promoter = $this->promoter->getPromoterByUid($uid);

        $data['wait_pay'] = $this->order->getOrderCountByBuyerId($uid, Order::ORDER_STATUS_IS_WAIT_PAY);
        $data['wait_express'] = $this->order->getOrderCountByBuyerId($uid, Order::ORDER_STATUS_IS_WAIT_EXPRESS);
        $data['expressed'] = $this->order->getOrderCountByBuyerId($uid, Order::ORDER_STATUS_IS_RECEIVED);

        $data['promoter'] = $promoter->count() > 0 ? true : false;

        $data['is_open'] = SettingHelper::getConfigValue('ALLOW_JOIN') == 1 ? true : false;

        $data['score'] = $userInfo ? $userInfo->member_point : 0;
        return ResultHelper::json_result('请求成功', $data, 200);
    }

    /*
     * 编辑用户信息
     */
    public function editUserInfo(Request $request)
    {
        $uid = $request->input('uid');
        $userInfo = $this->member->find($uid);
        if (!$userInfo) {
            return ResultHelper::json_error('请求非法');
        }
        $nickname = $request->input('nickname');
        if (!empty($nickname)) {
            $userInfo->member_name = $nickname;
            $userInfo->save();
        }
        $result = [
            'memberInfo' => $userInfo,
        ];
        return ResultHelper::json_result('修改成功', $result, 200);
    }

    /*
     * 保存加盟商家信息
     */
    public function shopApplySave(Request $request)
    {
        $uid = $request->input('uid');
        $data = [
            'shop_name' => $request->input('shopname'),
            'shop_address' => $request->input('shopaddress'),
            'contacts_name' => $request->input('contacts_name'),
            'contacts_phone' => $request->input('contacts_phone'),
            'uid' => $request->input('uid')
        ];
        $result = ShopApply::create($data);
        return ResultHelper::json_result('申请提交成功', $result, 200);
    }

    /*
     * 加盟商家申请列表
     */
    public function getShopList(Request $request)
    {
        $uid = $request->input('uid');
        $list = $this->apply->getListByUid($uid);
        $data = [];
        foreach ($list as $key => $v) {
            $data[$key]['date'] = Carbon::parse($v->create_time)->format('Y-m-d');
            $data[$key]['state'] = ShopApply::getState($v->apply_state);
            $data[$key]['info'] = $v->apply_state == ShopApply::STATE_FAIL ? '原因：' . $v->apply_message : '';
            $data[$key]['time'] = Carbon::parse($v->create_time)->format('Y-m-d H:m:s');
        }
        return ResultHelper::json_result('success', $data, 200);
    }


    /*
     * 活动列表
     */
    public function ActivityList(Request $request)
    {
        $page = $request->input('page', 0);
        $size = $request->input('size', 10);
        $isjoin = $request->input('isjoin', false);
        $data = [];
        if ($isjoin == 'true') {
            $uid = $request->input('uid');
            $where = $this->news->getActivityJoinWhere(['uid' => $uid]);
            $list = $this->news->getActivityJoinList($page, $size, $where);

            foreach ($list as $key => $v) {
                if ($v->activity) {
                    $_v = $v->activity;
                    $data[$key]['id'] = $_v->activity_id;
                    $data[$key]['title'] = $_v->title;
                    $data[$key]['thumb'] = UrlHelper::getActivityCover($_v->thumb);
                    $data[$key]['description'] = $_v->description;
                    $data[$key]['number'] = $_v->number;
                    $data[$key]['date'] = Carbon::parse($_v->create_time)->format('Y-m-d');
                }
            }
        } else {
            $list = $this->news->getActivityList($page, $size);
            foreach ($list as $key => $v) {
                $data[$key]['id'] = $v->activity_id;
                $data[$key]['title'] = $v->title;
                $data[$key]['thumb'] = UrlHelper::getActivityCover($v->thumb);
                $data[$key]['description'] = $v->description;
                $data[$key]['number'] = 0;
                $data[$key]['date'] = Carbon::parse($v->create_time)->format('Y-m-d');
            }
        }

        return ResultHelper::json_result('success', $data, 200);
    }

    /*
     * 活动详情
     */
    public function ActivityInfo(Request $request)
    {
        $id = $request->input('activity_id');
        $info = Activity::find($id);
        if (!$info) {
            return ResultHelper::json_error('请求非法');
        }
        $data = [
            'id' => $info->activity_id,
            'title' => $info->title,
            'originator' => $info->originator,
            'thumb' => UrlHelper::getActivityCover($info->thumb),
            'content' => $info->content,
            'date' => Carbon::parse($info->create_time)->format('Y-m-d'),
            'start_time' => Carbon::parse($info->start_time)->format('Y-m-d h:i:s'),
            'end_time' => Carbon::parse($info->end_time)->format('Y-m-d h:i:s'),
            'address' => $info->address
        ];
        return ResultHelper::json_result('success', $data, 200);
    }

    /*
     * 参加活动
     */
    public function ActivityJoin(Request $request)
    {
        $uid = $request->input('uid');
        $activity_id = $request->input('activity_id');
        $name = $request->input('name');
        $phone = $request->input('phone');
        $number = $request->input('number');
        $is_valid = ActivityJoin::where('activity_id', $activity_id)->where('uid', $uid)->count();
        if ($is_valid > 0) {
            return ResultHelper::json_error('已经报名参与了活动');
        }
        $data = [
            'uid' => $uid,
            'activity_id' => $activity_id,
            'phone' => $phone,
            'contacts' => $name,
            'number' => $number
        ];
        $result = ActivityJoin::create($data);
        return $result ? ResultHelper::json_result('报名成功', $result, 200) : ResultHelper::json_error('报名失败');
    }

    /*
     * 收藏列表
     */
    public function CollectList(Request $request)
    {
        $uid = $request->input('uid');
        $page = $request->input('page', 1);
        $size = $request->input('size', 5);
        $type = $request->input('type', 1);
        $page = $page > 0 ? ($page - 1) : 0;
        switch ($type) {
            case 1:
                $fav_type = 'goods';
                break;
            case 2:
                $fav_type = 'shop';
                break;
        }
        $FavData = [
            'uid' => $uid,
            'fav_type' => $fav_type
        ];
        $where = $this->favorites->getWhere($FavData);
        $list = $this->favorites->getList($page, $size, $where);
        $data = [];
        foreach ($list as $key => $v) {
            if ($v->fav_type == 'goods') {
                $info = $this->goods->find($v->fav_id);
                if (!$info) {
                    continue;
                }
                $data[$key]['id'] = $v->fav_id;
                $data[$key]['stock'] = $info->stock;
                $data[$key]['sales'] = $info->sales;
                $data[$key]['price'] = $info->price;
                $data[$key]['market_price'] = $info->market_price;
                $data[$key]['status'] = $info->status == 1 ? true : false;
                $data[$key]['unit'] = $info->unit;
                $data[$key]['thumb'] = UrlHelper::getGoodsCover($v->goods_image);
                $data[$key]['name'] = $v->goods_name;
                $data[$key]['url'] = '/pages/goods/info?id=' . $v->fav_id;
                $data[$key]['time'] = Carbon::parse($v->fav_time)->format('Y-m-d h:i:s');
            } elseif ($v->fav_type == 'shop') {
                $data[$key]['url'] = '/pages/shop/info?id=' . $v->fav_id;
                $data[$key]['thumb'] = UrlHelper::getGoodsCover($v->shop_logo);
                $data[$key]['name'] = $v->shop_name;
            }
        }
        return ResultHelper::json_result('success', $data, 200);
    }

    /*
     * 用户积分信息
     */
    public function ScoreInfo(Request $request)
    {
        $uid = $request->input('uid');
        $info = Member::where('uid', $uid)->first();
        $score = $info ? $info->member_point : 0;
        return ResultHelper::json_result('success', $score, 200);
    }

    /*
     * 积分明细
     */
    public function ScoreLogs(Request $request)
    {
        $uid = $request->input('uid');
        $page = $request->input('page', 0);
        $size = $request->input('size', 10);
        $list = MemberAccountRecord::where('uid', $uid)->where('account_type', 1)->orderBy('id', 'desc')
            ->skip($page * $size ?? 0)->take($size ?? 10)->get();
        $data = [];
        foreach ($list as $key => $item) {
            $data[$key]['remark'] = $item->remark;
            $data[$key]['number'] = $item->number < 0 ? $item->number : '+' . $item->number;
            $data[$key]['date'] = Carbon::parse($item->create_time)->format('Y-m-d h:i:s');
        }
        return ResultHelper::json_result('success', $data, 200);
    }

    /*
     * 积分订单
     */
    public function ScoreOrderGoods(Request $request)
    {
        $page = $request->input('page', 0);
        $size = $request->input('size', 3);

        $page = $page > 0 ? $page - 1 : 0;

        $data = [];
        $order_goods = $this->order->getMemberExchangeOrderGoods($this->uid, $page, $size);
        foreach ($order_goods as $goods) {
            if ($goods->goods_picture) {
                $imgInfo = $this->attachment->find($goods->goods_picture);
                $picture = UrlHelper::getGoodsCover($imgInfo->filepath . $imgInfo->filename);
            } else {
                $picture = UrlHelper::getGoodsCover('');
            }
            $data[] = [
                'id' => $goods->goods_id,
                'title' => $goods->goods_name,
                'thumb' => $picture,
            ];
        }

        return ResultHelper::json_result('success', $data, 200);
    }

    /*
     * 商品浏览记录
     */
    public function MemberLogs(Request $request)
    {
        $uid = $request->input('uid');
        $goods_id = $request->input('goods_id');
        $goodsInfo = Goods::find('goods_id');
        if ($goodsInfo) {
            $goodsInfo->increment('hits');
        }
        $info = MemberLogs::where('uid', $uid)->where('goods_id', $goods_id)->first();
        if ($info) {
            $result = $info->increment('hits');
        } else {
            $result = MemberLogs::create(['uid' => $uid, 'goods_id' => $goods_id]);
        }
        return $result ? ResultHelper::json_result('success') : ResultHelper::json_error('error');
    }

    /*
     * 收藏店铺
     */
    public function ShopCollect(Request $request)
    {
        $uid = $request->input('uid');
        $shop_id = $request->input('shop_id');
        $result = $this->favorites->getMemberShopCollect($uid, $shop_id);
        if ($result['collected'] == 'true') {
            return ResultHelper::json_result('success', ['data' => $result['data'], 'count' => $result['count']], 200);
        } else {
            return ResultHelper::json_result('success', ['data' => $result['data'], 'count' => $result['count']], 201);
        }
    }

    /*
     * 收藏/取消收藏商品
     */
    public function GoodsCollect(Request $request)
    {
        $uid = $request->input('uid');
        $goods_id = $request->input('goods_id');
        $result = $this->favorites->getMemberGoodsCollect($uid, $goods_id);
        if ($result['collected'] == 'true') {
            $this->favorites->cancelMemberGoodsCollect($uid, $goods_id);
            return ResultHelper::json_result('success', ['data' => $result['data'], 'count' => $result['count']], 200);
        } else {
            return ResultHelper::json_result('success', ['data' => $result['data'], 'count' => $result['count']], 201);
        }
    }

    /*
     * 获取用户商品访问记录
     */
    public function GoodsLogsList(Request $request)
    {
        $uid = $request->input('uid');
        $page = $request->input('page', 0);
        $size = $request->input('size', 6);
        if ($page > 0) $page--;
        $where = [
            'uid' => $uid
        ];
        $list = $this->goods->getGoodsLogsList($page, $size, $where);
        $data = [];
        foreach ($list as $key => $v) {
            $_v = $v->goods;
            if ($_v) {
                $data[$key]['goods_name'] = $_v->goods_name;
                $data[$key]['cover'] = UrlHelper::getGoodsCover($_v->goods_cover);
                $data[$key]['price'] = $_v->price;
                $data[$key]['market_price'] = $_v->market_price;
                $data[$key]['goods_id'] = $_v->goods_id;
            }
        }
        return ResultHelper::json_result('success', ['goods' => $data], 200);
    }

    /*
     * 提交用户建议
     */
    public function postSuggest(Request $request)
    {
        $uid = $request->input('uid');
        $suggest = $request->input('suggest');
        if (empty($suggest)) {
            return ResultHelper::json_result('用户建议不能为空', [], 201);
        }
        $result = MemberSuggest::create(['uid' => $uid, 'suggest' => $suggest]);
        return ResultHelper::json_result('感谢您的建议！', $result, 200);
    }

    /*
     * 保存分享商品图片
     */
    public function getGoodsShareImg(Request $request)
    {
        $uid = $request->input('uid', 0);

        $goods_id = $request->input('goods_id');
        $goodsInfo = $this->goods->find($goods_id);
        $promoterInfo = $this->promoter->getPromoterInfo($uid, $goodsInfo->shop_id);
        $path = '/pages/goods/info?id=' . $goods_id . '&promoter_id=' . $promoterInfo->promoter_id;

        $response = $this->program->app_code->get('h2XOEweBVoSW1QZF35QGmrW91eRUf29Z', [
            'page' => $path,
            'width' => 300
        ]);

        if ($response instanceof \EasyWeChat\Kernel\Http\StreamResponse) {
            $appcode = $goods_id . '_' . $uid . '.png';
            $response->saveAs(storage_path('appcode/'), $appcode);//存储二维码

            $board = storage_path('appcode/board.jpg');//画板

            $img = Image::make($board);
            $img->insert(storage_path('appcode' . DIRECTORY_SEPARATOR . $appcode), 'bottom-right', 60, 120);


            //封面图
            if ($goodsInfo->pictureInfo) {
                $cover = Image::make(public_path($goodsInfo->pictureInfo->url));
                $cover->resize(900, 900);
                $img->insert($cover, 'top-left', 90, 175);
            }


            // 插入标题
            $img->text(str_limit($goodsInfo->goods_name, 46, '...'), 80, 100, function ($font) {
                $font->file(storage_path('app/sc.otf'));
                $font->size(36);
                $font->color('#000000');
            });
            //插入售价
            $img->text('售价：￥' . $goodsInfo->price, 90, 1200, function ($font) {
                $font->file(storage_path('app/sc.otf'));
                $font->size(48);
                $font->color('#ff0000');
            });
            //插入原价
            $img->text('原价：￥' . $goodsInfo->market_price, 90, 1280, function ($font) {
                $font->file(storage_path('app/sc.otf'));
                $font->size(48);
                $font->color('#999');
            });
            $img->line(80, 1260, 480, 1260, function ($draw) {
                $draw->color('#999');
            });
            $img->text('长按识别直接购买', 750, 1460, function ($font) {
                $font->file(storage_path('app/sc.otf'));
                $font->size(30);
            });
            // 保存
            $savePath = 'uploads/share/goods-' . $goods_id;
            $shareImgName = $goods_id . '-' . $uid . '.png';
            if (!File::exists(public_path($savePath))) {
                File::makeDirectory(public_path($savePath), 0777, true);
            }

            $result = $img->save(public_path($savePath . $shareImgName));


            return $result ? ResultHelper::json_result('success', ['img' => asset($savePath . $shareImgName)], 200) : ResultHelper::json_error('error');

        }
    }

    /*
     * 获取用户订单消息
     */
    public function getOrderMessage(Request $request)
    {

    }

    /*
     * 淘问问题
     */
    public function postQuestion(Request $request)
    {
        $uid = $request->get('uid');
        $hidden_username = $request->get('hiddenUserName');
        $subject = $request->get('subject');
        $content = $request->get('content');
        $imgs = $request->get('imgs');
        $data = [
            'uid' => $uid,
            'hidden_username' => $hidden_username,
            'subject' => $subject,
            'content' => $content,
            'ip' => $request->getClientIp(),
            'create_time' => Carbon::now(),
            'img_array' => $imgs,
            'update_time' => Carbon::now()
        ];
        $result = $this->taowen->store($data);
        return $result ? ResultHelper::json_result('发布成功！', $result, 200) : ResultHelper::json_error('发布失败');
    }

    /*
     * 回答淘问问题
     */
    public function postAnswer(Request $request)
    {
        $uid = $request->get('uid');
        $questionId = $request->get('questionId');
        $hidden_username = $request->get('hiddenUserName');
        $content = $request->get('content');
        $imgs = $request->get('imgs');
        $data = [
            'question_id' => $questionId,
            'uid' => $uid,
            'hidden_username' => $hidden_username,
            'answer_content' => $content,
            'img_array' => $imgs,
            'create_time' => Carbon::now(),
            'update_time' => Carbon::now()
        ];
        $result = $this->taowen->storeAnswer($data);
        return $result ? ResultHelper::json_result('回答成功！', $result, 200) : ResultHelper::json_error('回答失败');
    }

    /*
     * 留言
     */
    public function postComment(Request $request)
    {
        $uid = $request->get('uid');
        $answerId = $request->get('id');
        $content = $request->get('content');
        $info = $this->taowen->getAnswerById($answerId);
        $data = [
            'answer_id' => $answerId,
            'uid' => $uid,
            'author_uid' => $info->uid,
            'comment_content' => $content,
            'status' => 0,
            'create_time' => Carbon::now(),
            'update_time' => Carbon::now()
        ];
        $result = $this->taowen->storeComment($data);
        return $result ? ResultHelper::json_result('留言成功！', $result, 200) : ResultHelper::json_error('留言失败');

    }

    /*
     * 点赞
     */
    public function postZan(Request $request)
    {
        $uid = $request->get('uid');
        $answer_id = $request->get('answer_id');
        $info = $this->taowen->getAnswerById($answer_id);

        $data = [
            'answer_id' => $answer_id,
            'uid' => $request->get('uid'),
            'create_time' => Carbon::now()
        ];
        $is_vaild = $this->taowen->getZanByUid($uid, $answer_id);
        if (!$is_vaild) {
            $result = $this->taowen->storeZan($data);
            $info->increment('zan');
            return ResultHelper::json_result('点赞成功！', $info->zans, 200);
        } else {
            return ResultHelper::json_error('已经点赞过了');
        }

    }

    /*
     * 淘问消息中心
     */
    public function getComments(Request $request)
    {
        $author_uid = $request->get('uid');
        $answer_id = $request->get('answer_id');
        $size = $request->get('size', 5);
        $page = $request->get('page', 1);
        $page = $page > 0 ? $page - 1 : 0;
        $where = $this->taowen->getCommentWhere($answer_id, $author_uid);
        $lists = $this->taowen->getCommentList($where, $page, $size);
        $data = [];
        foreach ($lists as $key => $v) {
            $data[$key]['answer_id'] = $v->answer_id;
            $i = $this->taowen->getAnswerById($v->answer_id);
            $data[$key]['question_id'] = $i->question_id;
            $data[$key]['content'] = $v->answer ? $v->answer->answer_content : '';
            $data[$key]['time'] = Carbon::parse($v->create_time)->format('Y-m-d H:m:s');
        }
        return ResultHelper::json_result('success', $data, 200);
    }

    public function myActivity(Request $request)
    {
        $uid = $request->get('uid');
        $size = $request->get('size', 5);
        $page = $request->get('page', 1);
        $page = $page > 0 ? $page - 1 : 0;
        $whereData = [
            'uid' => $uid
        ];
        $lists = $this->favority->getActivityJoinList($whereData, $page, $size);
        $data = [];
        foreach ($lists as $key => $v) {
            $data[$key]['title'] = $v->activity ? $v->activity->title : '';
            $data[$key]['cover'] = $v->activity ? $v->activity->thumb : '';
            $data[$key]['time'] = Carbon::parse($v->create_time)->format('Y-m-d H:m:s');
        }
        return ResultHelper::json_result('success', $data, 200);
    }
}
