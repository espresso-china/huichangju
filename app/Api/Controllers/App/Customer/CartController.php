<?php
/**
 * Created by PhpStorm.
 * User: tacker
 * Date: 2019/3/5
 * Time: 12:05 PM
 */

namespace App\Api\Controllers\App\Customer;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Helpers\ResultHelper;
use App\Helpers\UrlHelper;
use App\Model\Goods;
use App\Repositories\CartRepository;
use App\Repositories\GoodsRepository;
use App\Repositories\ShopRepository;
use App\Repositories\AttachmentRepository;
use App\Api\Controllers\App\ApiController;

class CartController extends ApiController
{
    private $cart, $goods, $shop, $attachment;

    function __construct(CartRepository $cart, GoodsRepository $goods, ShopRepository $shop, AttachmentRepository $attachment)
    {
        parent::__construct();
        $this->cart = $cart;
        $this->goods = $goods;
        $this->shop = $shop;
        $this->attachment = $attachment;
    }

    function addCartGoods(Request $request)
    {
        $goods_id = $request->input('goods_id');
        $sku_id = $request->input('sku_id');
        $num = $request->input('num');

        $result = $this->cart->addCartGoods($this->uid, $goods_id, $sku_id, $num);
        if ($result) {
            return ResultHelper::json_result('已加入成功，您可以继续选购商品', null, 200);
        } else {
            return ResultHelper::json_error('加入失败，请稍后再试');
        }
    }

    function getGoods(Request $request)
    {
        $cart_type = $request->input('type', 'normal');
        $sku_id = $request->input('sku_ids');
        $sku_num = $request->input('sku_nums');
        if (empty($sku_id) || empty($sku_num)) {
            return ResultHelper::json_error('参数为空');
        }
        $sku_id = explode(',', $sku_id);
        $sku_num = explode(',', $sku_num);

        $sku_num_arr = array_combine($sku_id, $sku_num);

        $goods_money = 0;
        $goods_point = 0;
        $sku_data = [];
        $skus = $this->goods->getGoodsSkusBySkuIds($sku_id);
        //\Log::info(json_encode($skus));

        foreach ($skus as $sku) {
            $goods_info = $this->goods->getGoodsInfo($sku->goods_id);
            if ($goods_info->status == Goods::STATUS_IS_NORMAL) {
                if (!isset($sku_data[$goods_info->shop_id])) {
                    //初始化门店数据
                    $shop_info = $this->shop->find($goods_info->shop_id, ['shop_id', 'shop_name']);

                    //获取门店提货点
                    $pickup = $this->shop->getShopPickupByShopId($shop_info->shop_id);

                    $sku_data[$goods_info->shop_id] = [
                        'shop_id' => $shop_info->shop_id,
                        'shop_name' => $shop_info->shop_name,
                        'pickup' => $pickup,
                        'pickup_time' => Carbon::now()->addDays(1)->toDateTimeString(),
                        'pickup_time_stamp' => Carbon::now()->addDays(1)->timestamp * 1000,
                        'pickup_start_time' => Carbon::now()->addDays(1)->timestamp * 1000,
                        'pickup_end_time' => Carbon::now()->addDays(30)->timestamp * 1000,
                        'default_pickup' => count($pickup) ? $pickup[0] : null,
                        'goods' => []
                    ];
                }

                //加载商品图片
                $picture_id = $sku->picture ?: $goods_info->picture;
                if ($picture_id) {
                    $image_info = $this->attachment->find($picture_id);
                    $picture = UrlHelper::getGoodsCover($image_info->filepath . '/small/' . $image_info->filename);
                } else {
                    $picture = UrlHelper::getGoodsCover('');
                }

                if ($goods_info->promotion_type == Goods::PROMOTION_TYPE_IS_NONE) {
                    $price = $sku->price;
                } else {
                    if ($cart_type == 'normal' && $goods_info->promotion_type == Goods::PROMOTION_TYPE_IS_PINTUAN) {
                        $price = $sku->price;
                    } else {
                        $price = $sku->promotion_price > 0 ? $sku->promotion_price : $sku->price;
                    }
                }

                if ($goods_info->max_buy) {
                    $max_buy = $goods_info->max_buy < $sku->stock ? $goods_info->max_buy : $sku->stock;
                } else {
                    $max_buy = $sku->stock > 999 ? 999 : $sku->stock;
                }


                //商品总费用和获得总积分数
                $goods_money += $price * $sku_num_arr[$sku->sku_id];
                if ($goods_info->give_point) {
                    $goods_point += $goods_info->give_point * $sku_num_arr[$sku->sku_id];
                    $sku_point = $goods_info->give_point;
                } else {
                    $shop_point_rate = $this->shop->getShopPointRate($goods_info->shop_id);
                    $goods_point += intval($price * $sku_num_arr[$sku->sku_id] * $shop_point_rate);
                    $sku_point = intval($price * $shop_point_rate);
                }

                $sku_data[$goods_info->shop_id]['goods'][] = [
                    'sku_id' => $sku->sku_id,
                    'sku_name' => $sku->sku_name,
                    'goods_id' => $sku->goods_id,
                    'goods_name' => $goods_info->goods_name,
                    'stock' => $sku->stock,
                    'price' => $price,
                    'point' => $sku_point,
                    'num' => $sku_num_arr[$sku->sku_id],
                    'min_buy' => $goods_info->min_buy ?: 1,
                    'max_buy' => $max_buy,
                    'picture' => $goods_info->cover
                ];
            }
        }

        if (empty($sku_data)) {
            return ResultHelper::json_error('商品已下架');
        } else {

            $result = ['goods' => array_values($sku_data), 'money' => $goods_money, 'point' => $goods_point];

            return ResultHelper::json_result('success', $result, 200);

        }
    }

    function getCartGoods(Request $request)
    {
        $money = 0;
        $total = 0;
        $goods = [];

        $cart_goods = $this->cart->getMemberCartGoods($this->uid);
        foreach ($cart_goods as $info) {
            if (!isset($goods[$info->shop_id])) {
                $goods[$info->shop_id] = [
                    'shop_id' => $info->shop_id,
                    'shop_name' => $info->shop_name,
                    'checked' => true,
                    'goods' => []
                ];
            }

            if ($info->goods_picture) {
                $attachment_info = $this->attachment->find($info->goods_picture);
                $picture = UrlHelper::getGoodsCover($attachment_info->filepath . '/small/' . $attachment_info->filename);
            } else {
                $picture = UrlHelper::getGoodsCover('');
            }

            $sku_data = [
                'cart_id' => $info->cart_id,
                'sku_id' => $info->sku_id,
                'sku_name' => $info->sku_name,
                'goods_id' => $info->goods_id,
                'goods_name' => $info->goods_name,
                'picture' => $picture,
                'sale_price' => $info->price,
                'stock' => 0,
                'num' => $info->num,
                'min_buy' => 0,
                'max_buy' => 0,
            ];
            $sku_info = $this->goods->getGoodsSkuInfo($info->sku_id);
            if ($sku_info) {
                $goods_info = $this->goods->getGoodsInfo($info->goods_id);
                if ($goods_info && $goods_info->status == Goods::STATUS_IS_NORMAL) {
                    $checked = $info->num <= $sku_info->stock ? true : false;
                    $sku_data['sale_price'] = $sku_info->sale_price;
                    $sku_data['checked'] = $checked;
                    $sku_data['allow'] = true;
                    $sku_data['min_buy'] = $goods_info->min_buy ?: 1;
                    $sku_data['max_buy'] = $goods_info->max_buy ?: 999;
                } else {
                    $sku_data['sale_price'] = $sku_info->sale_price;
                    $sku_data['checked'] = false;
                    $sku_data['allow'] = false;
                }
            } else {
                $sku_data['checked'] = false;
                $sku_data['allow'] = false;
            }
            $goods[$info->shop_id]['goods'][] = $sku_data;

            if ($sku_data['checked']) {
                $money += $sku_data['sale_price'] * $sku_data['num'];
                $total += $sku_data['num'];
            } else {
                $goods[$info->shop_id]['checked'] = false;
            }
        }

        $data = ['goods' => array_values($goods), 'money' => $money, 'total' => $total];

        return ResultHelper::json_result('success', $data, 200);
    }

    function changeCartGoodsNum(Request $request)
    {
        $cart_id = $request->input('cart_id');
        $num = $request->input('num');

        $this->cart->changeCartGoodsNum($this->uid, $cart_id, $num);
        return ResultHelper::json_result('success', '', 200);
    }

    function deleteCartGoods(Request $request)
    {
        $cart_ids = $request->input('cart_ids');
        if (empty($cart_ids)) {
            return ResultHelper::json_error('请选择商品');
        }

        $this->cart->deleteCartGoods($this->uid, $cart_ids);
        return ResultHelper::json_result('删除成功', '', 200);
    }
}
