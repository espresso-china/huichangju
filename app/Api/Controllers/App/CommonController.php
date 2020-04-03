<?php
/**
 * Created by PhpStorm.
 * User: tacker
 * Date: 2018/8/2
 * Time: 上午8:36
 * Edited by Espresso
 * Time:2019年2月22日17:30:50
 */

namespace App\Api\Controllers\App;

use App\Api\Controllers\App\ApiController;
use App\Helpers\NoticeHelper;
use App\Helpers\ResultHelper;
use App\Helpers\Session3rdHelper;
use App\Helpers\UrlHelper;
use App\Helpers\UtilHelper;
use App\Model\Attachment;
use App\Model\Focus;
use App\Model\Menu;
use App\Model\WechatFans;
use App\Repositories\AttachmentRepository;
use App\Repositories\ClassifyRepository;
use App\Repositories\GoodsRepository;
use App\Repositories\MemberFavoritesRepository;
use App\Repositories\MemberRepository;
use App\Repositories\NewsRepository;
use App\Repositories\RegionRepository;
use App\Repositories\ShopRepository;
use App\Repositories\TaoWenRepository;
use App\Repositories\WechatFansRepository;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Image;
use Session;

class CommonController extends ApiController
{

    private $fans, $member;
    private $news, $news_cate;
    private $region;
    private $shop, $goods;
    private $attachment;
    private $taowen;
    private $fav;

    public function __construct(MemberRepository $member, WechatFansRepository $fans, GoodsRepository $goods,
                                NewsRepository $news, ClassifyRepository $news_cate, RegionRepository $region,
                                ShopRepository $shop, AttachmentRepository $attachment, TaoWenRepository $taowen, MemberFavoritesRepository $fav)
    {
        parent::__construct();
        $this->member = $member;
        $this->fans = $fans;
        $this->goods = $goods;
        $this->shop = $shop;
        $this->news = $news;
        $this->news_cate = $news_cate;
        $this->region = $region;
        $this->attachment = $attachment;
        $this->taowen = $taowen;
        $this->fav = $fav;
    }

    /*
     * 用户信息获取或创建
     */
    public function getOrCreateUser(Request $request)
    {
        $session3rd = $request->input('session3rd');
        if ($session3rd) {
            $wxUserInfo = $this->fans->getWeChatUserBySession3rd($session3rd);
            if ($wxUserInfo) {
                $memberInfo = $this->member->getInfoByWxId($wxUserInfo->fans_id);
                $result = [
                    'wxid' => $wxUserInfo->fans_id,
                    'session3rd' => $session3rd,
                    'memberInfo' => $memberInfo,
                    'wxUserInfo' => $wxUserInfo,
                ];
                return ResultHelper::json_result('success', $result);
            }
        }

        //session3rd 无效重新验证用户信息
        $code = $request->input('code');
        $sessions = $this->program->auth->session($code);

        if (isset($sessions['openid'])) {
            $wxUserInfo = $this->fans->getWeChatUserByOpenId($sessions['openid']);
            $session3rd = md5($sessions['openid'] . $sessions['session_key'] . time());
            if ($wxUserInfo) {
                $memberInfo = $this->member->getInfoByWxId($wxUserInfo->fans_id);
                $this->fans->updateWeChatUser($wxUserInfo->fans_id, ['session3rd' => $session3rd, 'session' => $sessions['session_key']]);
            } else {
                $uid = 0;
                if (isset($sessions['unionid']) && ($sessions['unionid'])) {
                    $mini_program_user_info = $this->fans->getWeChatUserByUnionId($sessions['unionid'], WechatFans::TYPE_IS_OFFICIAL);
                    if ($mini_program_user_info) {
                        $uid = $mini_program_user_info->uid;
                    }
                }
                $userData = [
                    'uid' => $uid,
                    'unionid' => isset($sessions['unionid']) ? $sessions['unionid'] : '',
                    'openid' => $sessions['openid'],
                    'nickname' => env('APP_NAME') . UtilHelper::createRandomStr(6),
                    'headimgurl' => $request->input('avatarUrl'),
                    'sex' => $request->input('gender'),
                    'country' => $request->input('country'),
                    'province' => $request->input('province'),
                    'city' => $request->input('city'),
                    'session' => $sessions['session_key'],
                    'session3rd' => $session3rd
                ];
                $wxUserInfo = $this->fans->addWeChatUser($userData);
                $memberInfo = null;
            }

            $result = [
                'wxid' => $wxUserInfo->fans_id,
                'session3rd' => $session3rd,
                'memberInfo' => $memberInfo,
                'wxUserInfo' => $wxUserInfo,
            ];

            return ResultHelper::json_result('success', $result);
        } else {
            return ResultHelper::json_error(isset($sessions['errmsg']) ? $sessions['errmsg'] : '系统错误，请稍后再使用');
        }
    }

    /**
     * 重新登录刷新授权session
     * @param Request $request
     */
    public function reLogin(Request $request)
    {
        $session3rd = $request->input('session3rd');
        if (empty($session3rd)) {
            return ResultHelper::json_error('无授权信息，请重新进入小程序');
        }

        $wxUserInfo = $this->fans->getWeChatUserBySession3rd($session3rd);
        if (empty($wxUserInfo)) {
            return ResultHelper::json_error('无授权信息，请重新进入小程序');
        }

        //session3rd 无效重新验证用户信息
        $code = $request->input('code');
        $sessions = $this->program->auth->session($code);
        if (isset($sessions['openid'])) {
            $this->fans->updateWeChatUser($wxUserInfo->fans_id, ['session' => $sessions['session_key']]);
            return ResultHelper::json_result('授权信息刷新成功，您可继续操作', null, 200);
        } else {
            return ResultHelper::json_error('授权信息刷新失败，请稍后再试');
        }

    }

    /*
     * 小程序首页获取滚动图，导航
     */
    public function getInit(Request $request)
    {

        $version = $request->input('ver', 1);
        $result = Menu::where('status', 1)->where('version', $version)->orderBy('listorder', 'asc')->orderBy('id', 'desc')->get();
        $MenuData = [];
        foreach ($result as $key => $item) {
            $MenuData[$key]['name'] = $item->name;
            $MenuData[$key]['url'] = $item->url;
            $MenuData[$key]['thumb'] = UrlHelper::getMenuIcon($item->thumb);
            if ($key >= 3) {
                break;
            }
        }

        $type = $request->input('type', Focus::TYPE_IS_INDEX);
        $result = Focus::where('status', 1)->where('type', $type)->orderBy('listorder', 'asc')->orderBy('id', 'desc')->get();
        $FocusData = [];
        foreach ($result as $key => $item) {
            $FocusData[$key]['name'] = $item->name;
            $FocusData[$key]['url'] = $item->url;
            $FocusData[$key]['thumb'] = UrlHelper::getFocusCover($item->thumb);
        }
        $data['menus'] = $MenuData;
        $data['focus'] = $FocusData;
        $data['citys'] = $this->region->getOpenCityList();

        return ResultHelper::json_result('success', $data, 200);
    }

    public function getFocusList(Request $request)
    {
        $type = $request->input('type', Focus::TYPE_IS_INDEX);
        $result = Focus::where('status', 1)->where('type', $type)->orderBy('listorder', 'asc')->orderBy('id', 'desc')->get();
        $FocusData = [];
        foreach ($result as $key => $item) {
            $FocusData[$key]['name'] = $item->name;
            $FocusData[$key]['url'] = $item->url;
            $FocusData[$key]['thumb'] = UrlHelper::getFocusCover($item->thumb);
        }
        $data['focus'] = $FocusData;
        return ResultHelper::json_result('success', $data, 200);
    }

    /*
     * 获取商品分类
     */
    public function getTypes(Request $request)
    {
        $where = $this->goods->getCategoryWhere(1, 0, 1);
        $result = $this->goods->getCategoryList(0, 20, $where);
        return ResultHelper::json_result('success', $result, 200);
    }

    /*
     * 小程序获取资讯列表
     */
    public function getNews(Request $request)
    {
        $cateid = $request->input('cateid', 0);
        if ($cateid == 'undefined') {
            $cateid = 0;
        }
        $page = $request->input('page', 1);
        $size = $request->input('size', 6);
        $sort = $request->input('sort', 0);
        $keywords = $request->input('keywords', '');
        $cateInfo = [];
        if ($cateid > 0) {
            $cateInfo = $this->news->getCateById($cateid);
            if (!$cateInfo) {
                return ResultHelper::json_error('分类不存在');
            }
            if ($cateInfo->status == 0) {
                return ResultHelper::json_error('无效分类');
            }
        }

        $where = $this->news->getWhere(['status' => 1, 'cate' => $cateid, 'title' => $keywords, 'sort' => $sort]);
        $infos = $this->news->getList($page - 1, $size, $where);
        $data = [];
        foreach ($infos as $key => $v) {
            $data[$key] = [
                'id' => $v->id,
                'title' => $v->title,
                'description' => $v->description ?: '',
                'thumb' => asset($v->thumb),
                'date' => $v->create_time->toDateTimeString()
            ];
        }

        return ResultHelper::json_result('success', ['news' => $data, 'catename' => $cateInfo ? $cateInfo->title : ''], 200);
    }

    /*
     * 获取资讯详细内容
     */
    public function getNewsInfo(Request $request)
    {
        $id = $request->input('id');
        $newsInfo = $this->news->find($id);
        if (!$newsInfo) {
            return ResultHelper::json_error('信息不存在');
        }
        $newsInfo->increment('hits');
        $url = route('front.news.info', ['id' => $newsInfo->id]);
        return ResultHelper::json_result('success', ['url' => $url, 'info' => $newsInfo], 200);
    }

    public function getNewsClassify(Request $request)
    {
        $newsClassify = $this->news->getCateList();
        return ResultHelper::json_result('success', $newsClassify, 200);
    }

    public function postNewsZans(Request $request)
    {
        $wxid = $request->input('wxid', 0);
        $news_id = $request->input('id');
        $uid = $request->input('uid');
        if ($wxid > 0) {

            $result = $this->fav->postMemberFav('news', $news_id, $uid, $wxid);

            if ($result['collected'] == true) {
                return ResultHelper::json_result('已经收藏过了', $result, 201);
            } else {
                return ResultHelper::json_result('success', $result, 200);
            }
        }
        return ResultHelper::json_result('error', [], 202);
    }

    public function getCityByName(Request $request)
    {
        $name = $request->input('name');
        $city = $this->region->getCityByName($name);
        if ($city) {
            return ResultHelper::json_result('success', ['data' => $city], 200);
        } else {
            return ResultHelper::json_result('false', [], 201);
        }
    }

    /*
     * 获取开通城市列表
     */
    public function getOpenCitys(Request $request)
    {
        $citys = $this->region->getOpenCityList();
        $data = [];
        foreach ($citys as $city) {
            $data[$city->city_code] = $city->city_name;
        }
        $arr = $citys->toArray();

        $result = [];
        $lists = [];
        foreach ($arr as $k => $v) {
            isset($result[$v['begins']]) || $result[$v['begins']] = [];
            $result[$v['begins']][$k]['key'] = $v['city_id'];
            $result[$v['begins']][$k]['name'] = $v['city_name'];
        }
        ksort($result, SORT_NATURAL);//这个是键值按字母先后顺序排列

        foreach ($result as $key => $_v) {
            $lists[$key] = [
                'title' => $key,
                'item' => array_values($_v)
            ];
        }
        //\Log::info(array_values($lists));
        return ResultHelper::json_result('success', ['citys' => $data, 'lists' => array_values($lists)], 200);
    }

    //最近搜索
    public function getSearchLogs(Request $request)
    {
        $wx_uid = $request->input('wxid');
        $page = $request->input('page', 0);
        $size = $request->input('size', 6);
        $data = $this->member->getSearchLogs($page, $size, ['wx_uid' => $wx_uid]);
        return ResultHelper::json_result('success', ['searchs' => $data], 200);
    }

    //推荐搜索
    public function getSearchTuijian(Request $request)
    {
        $data = $this->member->getSearchLogs(0, 6, ['is_tuijian' => true]);
        return ResultHelper::json_result('success', ['searchs' => $data], 200);
    }

    public function postSearchKeywords(Request $request)
    {
        $wx_uid = $request->input('wxid');
        $keywords = $request->input('keywords');
        if (empty($keywords)) {
            return ResultHelper::json_error('搜索词不能为空');
        }
        $result = $this->member->saveSearchKeyword(['wx_uid' => $wx_uid, 'keywords' => $keywords]);

        //搜索商品
        $goodsSearchWhere = $this->goods->getWhere(['keywords' => $keywords, 'category_id' => 0]);
        $goodsSearchResult = $this->goods->getList(0, 3, $goodsSearchWhere);
        $goods = [];
        foreach ($goodsSearchResult as $key => $v) {
            $goods[$key]['id'] = $v->goods_id;
            $goods[$key]['goods_name'] = $v->goods_name;
            $goods[$key]['goods_cover'] = UrlHelper::getGoodsCover($v->goods_cover);
            $goods[$key]['price'] = $v->price;
        }
        //搜索新闻
        $newsSearchWhere = $this->news->getWhere(['title' => $keywords]);
        $newsSearchResult = $this->news->getList(0, 3, $newsSearchWhere);
        $news = [];
        foreach ($newsSearchResult as $key => $item) {
            $news[$key]['id'] = $item->id;
            $news[$key]['title'] = $item->title;
            $news[$key]['description'] = $item->description;
            $news[$key]['thumb'] = UrlHelper::getNewsCover($item->thumb);
        }
        //搜索淘问
        $taowenSearchWhere = $this->taowen->getQuestionWhere($keywords);
        $taowenSearchResult = $this->taowen->getQuestionList($taowenSearchWhere, 0, 3);
        $taowens = [];
        foreach ($taowenSearchResult as $key => $item) {
            $taowens[$key]['id'] = $item->question_id;
            $taowens[$key]['title'] = $item->subject;
            $taowens[$key]['description'] = $item->content;
        }

        //搜索店铺
        $where = $this->shop->getShopWhere(['name' => $keywords]);
        $shopSearchResult = $this->shop->getShopList(0, 3, $where);
        $shop = [];
        foreach ($shopSearchResult as $key => $item) {
            $shop[$key]['id'] = $item->shop_id;
            $shop[$key]['shop_name'] = $item->shop_name;
            $shop[$key]['shop_cover'] = UrlHelper::getShopCover($item->shop_logo);
            $shop[$key]['address'] = $item->address;
        }
        return ResultHelper::json_result('success', ['goods' => $goods, 'news' => $news, 'shops' => $shop, 'taowen' => $taowens], 200);
    }

    public function saveWeChatFormId(Request $request)
    {
        \Log::info(json_encode($request->all()));
    }

    public function uploadFile(Request $request)
    {
        $file = $request->file('file');
        $type = 'mini';

        if (empty($file)) {
            return ResultHelper::json_result('空文件', [], 201);
        }
        if ($file->getClientSize() > 10000000) {
            return ResultHelper::json_result('上传文件不能超过10M', [], 413);
        }
        if (!$file->isValid()) {
            return ResultHelper::json_result('文件上传出错', [], 201);
        }
        $extension = $file->getClientOriginalExtension();

        $types = explode(',', env('ALLOW_UPLOAD_FILE_SUFFIX', 'jpg,png,gif,jpeg'));//定义检查的文件类型


        if (!in_array(strtolower($extension), $types)) {
            return ResultHelper::json_result('文件类型非法', [], 201);
        }

        if ($request->hasFile('file')) {
            $tmpPath = 'uploads' . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . date('Ymd', time()) . DIRECTORY_SEPARATOR;
            $uploadDir = public_path($tmpPath); //上传地址

            $disk = Storage::disk('qiniu');
            $qiniu_url = $disk->put($type, $file);
            $qiniu_url = env('QINIU_DOMAIN', 'http://fm.0755fc.com/') . $qiniu_url . '?imageMogr2/auto-orient/thumbnail/800x800/blur/1x0/quality/100';
            //. '?imageMogr2/auto-orient/thumbnail/800x800/blur/1x0/quality/100'

            if (!File::exists($uploadDir)) {
                File::makeDirectory($uploadDir . 'small', 0777, true);
                File::makeDirectory($uploadDir . 'large', 0777, true);
                File::makeDirectory($uploadDir . 'orig', 0777, true);
            }
            $newName = md5(date('ymdhis')) . "." . $extension;
            $attachment = [
                'filesize' => $file->getClientSize(),
                'original_filename' => $file->getClientOriginalName()
            ];
            $orig = $tmpPath . 'orig' . DIRECTORY_SEPARATOR . $newName;
            $big = $tmpPath . 'large' . DIRECTORY_SEPARATOR . $newName;
            $small = $tmpPath . 'small' . DIRECTORY_SEPARATOR . $newName;
            //移动文件
            $result = $file->move($uploadDir . 'orig', $newName);
            $is_img = strpos($result->getMimeType(), 'image') !== false ? 1 : 0;
            if ($is_img) {
                Image::make($result)->widen(800)->save($big);
                Image::make($result)->widen(400)->save($small);
            }

            if ($result) {
                $attachment += [
                    'basename' => $result->getBasename(),
                    'filename' => $result->getFilename(),
                    'filepath' => $tmpPath,
                    'fileext' => $result->getExtension(),
                    'mime' => $result->getMimeType(),
                    'isimage' => $is_img,
                    'url' => $orig,
                    'qiniu_url' => $qiniu_url,
                    'ip' => $request->ip(),
                    'shopid' => '',
                    'created_at' => time(),
                    'updated_at' => time()
                ];
                $aid = $this->attachment->store($attachment);
                $attachment += [
                    'aid' => $aid,
                    'large' => $big,
                    'small' => $small
                ];
                return ResultHelper::json_result('上传成功', $attachment, 200);

            } else {
                return ResultHelper::json_result('文件写入失败', [], 201);
            }
        } else {
            return ResultHelper::json_result('文件为空', [], 201);
        }
    }
}
