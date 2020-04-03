<?php
/**
 * Created by PhpStorm.
 * User: tacker
 * Time: 上午8:36
 * Edited by Espresso
 * Time:2019年2月22日17:30:50
 */

namespace App\Api\Controllers\App;

use App\Model\PromotionPintuanGroup;
use Carbon\Carbon;
use File;
use Illuminate\Http\Request;
use App\Helpers\ResultHelper;
use App\Helpers\UrlHelper;
use App\Model\City;
use App\Model\Goods;
use App\Model\MemberFavorite;
use App\Model\OrderGoods;
use App\Repositories\AttachmentRepository;
use App\Repositories\CartRepository;
use App\Repositories\GoodsRepository;
use App\Repositories\MemberRepository;
use App\Repositories\PromotionRepository;
use App\Repositories\ShopRepository;
use App\Repositories\WechatFansRepository;
use App\Repositories\OrderRepository;
use Image;

class GoodsController extends ApiController
{

    private $fans, $member, $goods, $shop, $attachment, $cart, $promotion, $order;

    public function __construct(MemberRepository $member, WechatFansRepository $fans, GoodsRepository $goods,
                                ShopRepository $shop, AttachmentRepository $attachment, CartRepository $cart,
                                PromotionRepository $promotion, OrderRepository $order)
    {
        parent::__construct();
        $this->member = $member;
        $this->fans = $fans;
        $this->goods = $goods;
        $this->shop = $shop;
        $this->attachment = $attachment;
        $this->cart = $cart;
        $this->promotion = $promotion;
        $this->order = $order;
    }

    /*
     * 普通商品列表
     */
    public function getGoodsList(Request $request)
    {
        $promotion_type = $request->get('promotion_type', null);
        $category_id = $request->get('category_id', '');
        $shop_id = $request->get('shop_id', 0);
        $keywords = $request->input('keywords', 0);
        $c_id = $request->input('city_id', '');
        $cityid = $request->input('c_id', 0);
        $page = $request->get('page') - 1;
        if ($page < 0) $page = 0;
        $size = $request->get('limit', 4);
        $cityInfo = City::where('city_code', $c_id)->first();
        $is_hot = $request->get('is_hot', 0);
        $group_id = $request->get('groupid', 0);
        if ($cityid > 0) {
            $city_id = $cityid;
        } else {
            if ($cityInfo) {
                $city_id = $cityInfo->city_id;
            } else {
                $city_id = 0;
            }
        }

        $whereData = [
            'category_id' => $category_id,
            'promotion_type' => $promotion_type,
            'shop_id' => $shop_id,
            'city_id' => $city_id,
            'keywords' => $keywords,
            'status' => 1,
            'is_hot' => $is_hot,
            'group_id' => $group_id
        ];

        $where = $this->goods->getWhere($whereData);

        $data = [];
        $lists = $this->goods->getList($page, $size, $where);
        $i = 0;
        foreach ($lists as $key => $v) {
            $data[$key]['goods_id'] = $v->goods_id;
            $data[$key]['goods_name'] = $v->goods_name;
            $data[$key]['cover'] = UrlHelper::getGoodsCover($v->goods_cover);
            $data[$key]['is_new'] = $v->is_new == 1 ? true : false;
            $data[$key]['market_price'] = $v->market_price;
            $data[$key]['price'] = $v->promotion_type == Goods::PROMOTION_TYPE_IS_NONE ? $v->price : $v->promotion_price;
            $data[$key]['sales'] = $v->display_sales;
            $data[$key]['stock'] = $v->stock ?: 0;
            $data[$key]['stoptime'] = 0;
            $data[$key]['shop_id'] = $v->shop_id;
            $data[$key]['unit'] = $v->unit ?: '件';
            //  $buyers = $this->goods->getOrderListByGoodsid($v->goods_id);
            $logs = [];
            if ($promotion_type == Goods::PROMOTION_TYPE_IS_MIAOSHA) {
                $promotionInfo = $this->promotion->getMiaoshaValidGoods($v->promotion_id);
                if (!$promotionInfo) {
                    continue;
                }
                if ($promotionInfo->start_time->timestamp > time()) {
                    $data[$key]['tags'] = '开始';
                    $data[$key]['type'] = 1;
                    $data[$key]['stoptime'] = $promotionInfo->start_time->timestamp;
                } elseif ($promotionInfo->start_time->timestamp < time() && $promotionInfo->end_time->timestamp > time()) {
                    $data[$key]['tags'] = '剩余';
                    $data[$key]['type'] = 2;
                    $data[$key]['stoptime'] = $promotionInfo->end_time->timestamp;
                } elseif ($promotionInfo->end_time->timestamp < time()) {
                    $data[$key]['tags'] = '已结束';
                    $data[$key]['type'] = 0;
                    $data[$key]['stoptime'] = 0;
                }
            } else if ($promotion_type == Goods::PROMOTION_TYPE_IS_PINTUAN) {
                $promotionInfo = $this->promotion->getPintuanInfo($v->promotion_id);
                $successPerson = $this->promotion->getSuccessPinTuanPerson($v->goods_id);
                $data[$key]['successPerson'] = $successPerson > 0 ? "已有" . $successPerson . "人拼团成功" : '暂时没有人拼团';
                $data[$key]['people'] = $promotionInfo ? $promotionInfo->people : '-';
            }else if ($promotion_type == Goods::PROMOTION_TYPE_IS_KANJIA) {
                $promotionInfo = $this->promotion->getkanjiaGoods($v->promotion_id);

                \Log::info($promotionInfo);
            }
//            foreach ($buyers as $k => $_v) {
//                $logs[$k]['name'] = $_v->buyerInfo ? str_limit($_v->buyerInfo->member_name, 2, '**') : '幸孕淘淘';
//                $logs[$k]['avatar'] = $_v->buyerInfo ? UrlHelper::getUserAvatar($_v->buyerInfo->avatar) : UrlHelper::getUserAvatar('');
//                if ($k >= 2) {
//                    break;
//                }
//            }
//            $data[$key]['buyers'] = $logs;
            $i++;
        }
        return ResultHelper::json_result('success', ['goods' => $data, 'count' => $i], 200);
    }

    /*
     * 获取秒杀的商品列表
     */
    public function getMiaoShaGoodsList(Request $request)
    {
        $page = $request->get('page') - 1;
        if ($page < 0) $page = 0;
        $size = $request->get('size', 5);
        $shop_id = $request->input('shop_id');
        $status = $request->input('status', 0);//0 正在秒杀，1 即将开启
        $lists = $this->goods->getMiaoShaGoodsList($page, $size, $shop_id, $status);
        $data = [];
        foreach ($lists as $key => $v) {
            $data[$key]['goods_id'] = $v->goods_id;
            $data[$key]['goods_name'] = $v->goods_name;
            $data[$key]['cover'] = UrlHelper::getGoodsCover($v->goods_cover);
            $data[$key]['market_price'] = $v->market_price;
            $data[$key]['price'] = $v->promotion_price;
            $data[$key]['sales'] = $v->display_sales;
            $data[$key]['stock'] = $v->stock ?: 0;
            $data[$key]['stoptime'] = $status == 1 ? $v->start_time : $v->end_time;
            $data[$key]['type'] = $status;
            $data[$key]['shop_id'] = $v->shop_id;
            $data[$key]['unit'] = $v->unit ?: '件';
            $data[$key]['tags'] = $status == 0 ? '剩余' : '即将开始';

        }
        return ResultHelper::json_result('success', ['goods' => $data], 200);
    }

    /*
     * 积分商品列表
     */
    public function getScoreGoodsList(Request $request)
    {

        $page = $request->get('page') - 1;
        if ($page < 0) $page = 0;
        $pageSize = $request->get('size', 12);


        $lists = $this->goods->getScoreList($page, $pageSize, $where = []);
        $data = [];
        foreach ($lists as $key => $v) {
            $data[$key]['id'] = $v->goods_id;
            $data[$key]['thumb'] = UrlHelper::getGoodsCover($v->goods_cover);
            $data[$key]['title'] = $v->goods_name;
            $data[$key]['point_exchange'] = $v->point_exchange ?: 0;
        }
        return ResultHelper::json_result('success', ['goods' => $data], 200);
    }

    /*
     * 商品详情
     */
    public function getGoodsInfo(Request $request)
    {
        $goods_id = $request->input('goods_id', 0);

        $info = $this->goods->getGoodsInfo($goods_id);
        if (!$info) {
            return ResultHelper::json_error('商品已下架');
        }

        $goodsCover = UrlHelper::getGoodsCover($info->goods_cover);
        $ShopInfo = $this->shop->find($info->shop_id);
        $ShopInfo->shop_logo = UrlHelper::getShopCover($ShopInfo->shop_logo);

        $img_ids = explode(",", $info->img_id_array);
        //$images = $this->attachment->getImgsByImgIds($img_ids);
        $images = [];
        if (is_array($img_ids) && $info->img_id_array != "") {
            foreach ($img_ids as $key => $v) {
                $img = $this->attachment->find($v);
                $images[$key]['url'] = $img ? asset($img->qiniu_url) : '';
            }
        }

//        foreach ($images as $key => $attachment_info) {
//            $images[$key]->url = asset($attachment_info->qiniu_url?:$attachment_info->url);
//        }

        $goods_attributes = $this->goods->getGoodsAttributesByGoodsId($goods_id);

        $cart_goods_count = 0;
        $is_collect = false;
        if ($this->uid) {
            $cart_goods_count = $this->cart->getMemberCartGoodsCount($this->uid);
            $is_collect = MemberFavorite::where('fav_id', $goods_id)->where('uid', $this->uid)->count();
            if ($is_collect > 0) {
                $is_collect = true;
            }
        }

        $info->pick_time = Carbon::today()->addDays(1)->addHours(8)->format('Y-m-d h:i:s');

        $promotionInfo = null;
        $is_miaosha = false;
        $PinGroups = [];
        $PinTuanInfo = null;
        if ($info->promotion_type == Goods::PROMOTION_TYPE_IS_MIAOSHA) {
            //$promotion_info = $this->promotion->getMiaoshaInfo($info->promotion_id);
            $promotionInfo = $this->promotion->getMiaoshaValidGoods($info->promotion_id);
            //\Log::info($promotionInfo);
            $is_miaosha = true;
            if ($promotionInfo) {
                if ($promotionInfo->start_time->timestamp > time()) {
                    $info->tags = '即将开始';
                    $info->type = 1;
                    $info->stoptime = $promotionInfo->start_time->timestamp;
                } elseif ($promotionInfo->start_time->timestamp < time() && $promotionInfo->end_time->timestamp > time()) {
                    $info->tags = '即将结束';
                    $info->type = 2;
                    $info->stoptime = $promotionInfo->end_time->timestamp;
                } elseif ($promotionInfo->end_time->timestamp < time()) {
                    $info->tags = '已结束';
                    $info->type = 0;
                    $info->stoptime = $promotionInfo->end_time->timestamp;
                }
                $promotionInfo->starttime = $promotionInfo->start_time->toDateTimeString();
                $promotionInfo->endtime = $promotionInfo->end_time->toDateTimeString();
            }
        } else if ($info->promotion_type == Goods::PROMOTION_TYPE_IS_PINTUAN) {
            $promotionInfo = $this->promotion->getPinTuanValidGoods($info->promotion_id);
            //  \Log::info($promotionInfo);
            $PinTuanInfo = $this->promotion->getPinTuanInfo($promotionInfo->pintuan_id);
            if ($promotionInfo) {
                //$PinTuanGroupWhere = $this->promotion->getPintuanGroupsWhere($info->shop_id, $goods_id,PromotionPintuanGroup::STATUS_IS_NORMAL);
                //$PinTuanGroups = $this->promotion->getPintuanGroupsList(0, 5, $PinTuanGroupWhere);

                $PinTuanGroups = $this->promotion->getPintuanGroupsValid($goods_id, 5);
                $data = [];
                foreach ($PinTuanGroups as $k => $v) {
                    $data[$k]['avatar'] = $v->WechatMember ? $v->WechatMember->headimgurl : '';
                    $data[$k]['group_id'] = $v->group_id;
                    $data[$k]['name'] = $v->user_name;
                    $data[$k]['need_people'] = $v->people - $v->has_people;
                    $data[$k]['end_time'] = $v->end_time->toDateTimeString();
                }
                $PinGroups = $data;
            }
        }

        $collectLogs = MemberFavorite::where('fav_id', $goods_id)->count();
        $collects = $collectLogs + $info->collects;

        $data = [
            'cartGoodsCount' => $cart_goods_count,
            'collects' => $collects,
            'goodsInfo' => $info,
            'shopInfo' => $ShopInfo,
            'goodsCover' => $goodsCover,
            'goodsInfoImg' => $images,
            'goodsAttributes' => $goods_attributes,
            'goodsSpecData' => $info->goods_spec_format ? json_decode($info->goods_spec_format, true) : [],
            'goodsSkuData' => $this->goods->getGoodsSkusByGoodsId($goods_id),
            'promotionInfo' => $PinTuanInfo,
            'currentDateTime' => Carbon::now()->toDateTimeString(),
            'PinGroups' => $PinGroups,
            'step_img' => 'http://fmcdn.0755fc.com/step.jpg?v=1',
            'is_miaosha' => $is_miaosha,
            'is_collect' => $is_collect
        ];

        return ResultHelper::json_result('success', $data, 200);
    }

    /*
     * 积分商品详情
     */
    public function getScoreGoodsInfo(Request $request)
    {
        $id = $request->input('id');

        $info = $this->goods->find($id);
        if (!$info) {
            return ResultHelper::json_error('商品不存在');
        }

        if ($info->point_exchange_type != 1) {
            return ResultHelper::json_error('不是积分商品');
        }

        if ($this->uid) {
            $info->exchanged = $this->order->checkMemberBuyGoodsSuccess($this->uid, $id);
        } else {
            $info->exchanged = false;
        }

        return ResultHelper::json_result('success', $info, 200);
    }

    /*
     * 商品支付记录
     */
    public function getGoodsBuyers(Request $request)
    {
        $page = $request->input('page', 0);
        $size = 5;
        $goods_id = $request->input('goods_id');
        $lists = OrderGoods::where('goods_id', $goods_id)->skip($page * $size ?? 0)->take($size ?? 10)->orderBy('create_time', 'desc')->get();
        $data = [];
        foreach ($lists as $key => $v) {
            $data[$key]['name'] = $v->buyerInfo ? str_limit($v->buyerInfo->member_name, 2, '*') : '';
            $data[$key]['avatar'] = $v->buyerInfo ? UrlHelper::getUserAvatar($v->buyerInfo->avatar) : UrlHelper::getUserAvatar('');
            $data[$key]['number'] = $v->num;
            $data[$key]['time'] = Carbon::parse($v->create_time)->format('Y-m-d h:i:s');
        }
        return ResultHelper::json_result('success', ['buyers' => $data], 200);
    }

    /*
     * 获取拼团商品
     */
    public function getPinGoodsList(Request $request)
    {
        $page = $request->input('page', 0);
        $size = 5;
        $shopid = $request->input('shop_id');
    }

    public function getGoodsQrcodeCover(Request $request)
    {
        $uid = $request->input('uid', 0);
        $goods_id = $request->input('goods_id');
        $goodsInfo = $this->goods->find($goods_id);
        // $promoterInfo = $this->promoter->getPromoterInfo($uid, $goodsInfo->shop_id);
        $path = '/pages/goods/info?id=' . $goods_id . '&uid=' . $uid;

        $response = $this->program->app_code->get('h2XOEweBVoSW1QZF35QGmrW91eRUf29Z', [
            'page' => $path,
            'width' => 300
        ]);

        if ($response instanceof \EasyWeChat\Kernel\Http\StreamResponse) {
            $appcode = $goods_id . '_' . $uid . '.png';
            $response->saveAs(storage_path('appcode/goods/'), $appcode);//存储二维码

            $board = storage_path('appcode/board.jpg');//画板

            $img = Image::make($board);
            $img->insert(storage_path('appcode/goods/' . $appcode), 'bottom-right', 60, 120);


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
     *
     */
    public function getBargainGoods(Request $request){
        $goods_id = $request->input('goods_id');
        $goodsInfo = $this->goods->getGoodsInfo($goods_id);
        $promotionInfo = $this->promotion->getkanjiaGoodsInfo($goods_id);
        $stopTime =  $promotionInfo->end_time->timestamp;
        return  ResultHelper::json_result('success', ['goodsInfo' => $goodsInfo,'promotionInfo'=>$promotionInfo,'stopTime'=>$stopTime], 200);
    }
    /**
     * 分享商品
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function shareGoodsInfo(Request $request)
    {
        $goods_id = $request->input('goodsId', 0);

        $info = $this->goods->getGoodsInfo($goods_id);
        if (!$info) {
            return ResultHelper::json_error('商品已下架');
        }

        //砍价商品，分享用户存在，创建砍价发起记录
        if ($this->uid && $info->promotion_type == Goods::PROMOTION_TYPE_IS_KANJIA) {
            $result = $this->promotion->createPromotionKanjiaGroup($info, $this->uid);
            if ($result['status'])
                return ResultHelper::json_result('创建砍价记录成功', $result['data']);
            else
                return ResultHelper::json_error($result['msg']);
        }

        return ResultHelper::json_error('无操作');
    }

    /**
     * 砍价助力
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function helpKanjia(Request $request)
    {
        $goods_id = $request->input('goodsId', 0);
        $group_id = $request->input('groupId', 0);

        if (empty($goods_id) || empty($group_id)) {
            return ResultHelper::json_error('参数不能为空');
        }

        if (empty($this->wxUid)) {
            return ResultHelper::json_error('用户参数不存在');
        }

        $result = $this->promotion->createPromotionKanjiaRecord($group_id, $goods_id, $this->wxUid);

        if ($result['status']) {
            return ResultHelper::json_result('帮助好友砍价 ' . $result['data'] . ' 元成功', $result['data']);
        } else {
            return ResultHelper::json_error($result['msg']);
        }
    }
}
