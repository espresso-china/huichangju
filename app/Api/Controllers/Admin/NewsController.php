<?php
/**
 * Created by PhpStorm.
 * User: tacker
 * Date: 2019/2/25
 * Time: 10:44 AM
 */

namespace App\Api\Controllers\Admin;

use App\Model\Activity;
use Illuminate\Http\Request;
use App\Model\Classify;
use App\Model\News;
use App\Helpers\ResultHelper;
use App\Repositories\NewsRepository;
use App\Repositories\ClassifyRepository;

class NewsController extends BaseController
{

    private $news, $classify;

    public function __construct(NewsRepository $news, ClassifyRepository $classify)
    {
        parent::__construct();
        $this->news = $news;
        $this->classify = $classify;
    }

    public function newsList(Request $request)
    {
        //资讯列表
        $page = $request->get('page') - 1;
        if ($page < 0) $page = 0;
        $pageSize = $request->get('limit');

        $title = $request->input('title');

        $classify = $request->input('classify');

        $whereData = [
            'title' => $title,
            'cate' => $classify
        ];
        $where = $this->news->getWhere($whereData);
        $data = $this->news->getList($page, $pageSize, $where);
        $count = $this->news->getCount($where);

        return ResultHelper::resources($data, ['count' => $count]);
    }

    public function newsAdd(Request $request)
    {
        if ($request->get('id')) {
            //编辑
            $data = [
                'title' => $request->get('title'),
                'status' => $request->input('status') ? $request->input('status') : 0,
                'thumb' => $request->get('thumb'),
                'listorder' => $request->get('listorder'),
                'description' => $request->get('description') ? $request->get('description') : strip_tags(mb_substr($request->get('content'), 0, 30)),
                'content' => $request->get('content'),
                'classify_id' => $request->get('classify_id'),
                'url' => $request->get('url'),
                'sort' => $request->input('sort') ? $request->input('sort') : 0,
            ];

            //更新分类
            $res = News::where('id', $request->get('id'))->update($data);

            if ($res) {
                return response()->json(['code' => 0, 'status' => 0, 'msg' => '新闻编辑成功！']);
            } else {
                return response()->json(['code' => 0, 'status' => 1, 'msg' => '新闻编辑失败！']);
            }
        } else {
            $news = News::create([
                'title' => $request->get('title'),
                'status' => $request->input('status') ? $request->input('status') : 0,
                'thumb' => $request->get('thumb'),
                'listorder' => $request->get('listorder'),
                'description' => $request->get('description') ? $request->get('description') : strip_tags(mb_substr($request->get('content'), 0, 30)),
                'content' => $request->get('content'),
                'classify_id' => $request->get('classify_id'),
                'url' => $request->get('url'),
                'sort' => $request->get('sort') ? $request->get('sort') : 0,
            ]);

            if ($news) {
                return response()->json(['code' => 0, 'status' => 0, 'msg' => '新闻添加成功！']);
            } else {
                return response()->json(['code' => 0, 'status' => 1, 'msg' => '新闻添加失败！']);
            }
        }
    }

    public function newsDelete(Request $request)
    {
        $id = $request->input('id');
        $res = News::where('id', $id)->delete();
        if ($res) {
            return response()->json(['code' => 0, 'status' => 0, 'msg' => '删除成功！']);
        } else {
            return response()->json(['code' => 0, 'status' => 1, 'msg' => '删除失败！']);
        }
    }

    public function newsClassify(Request $request)
    {
        //资讯分类下拉框
        $page = $request->get('page') - 1;
        if ($page < 0) $page = 0;
        $pageSize = $request->get('limit');

        $data = $this->classify->getList($page, $pageSize);

        $count = $this->classify->getCount();

        return ResultHelper::resources($data, ['count' => $count]);
    }

    public function setStatusEdit(Request $request)
    {
        $data = $request->all();
        $data = $data['data'];
        foreach ($data as $v) {
            $this->news->setStatusNews($v['id']);
        }
        return response()->json(['code' => 0, 'msg' => '设置成功！']);
    }

    public function cancelStatusEdit(Request $request)
    {
        $data = $request->all();
        $data = $data['data'];
        foreach ($data as $v) {
            $this->news->cancelStatusNews($v['id']);
        }
        return response()->json(['code' => 0, 'msg' => '设置成功！']);
    }

    public function newsInfo(Request $request)
    {
        $id = $request->get('id');
        $data = $this->news->getInfo($id);
        return response()->json(['code' => 0, 'data' => $data]);
    }

    public function setSortEdit(Request $request)
    {
        $data = $request->all();
        $data = $data['data'];
        foreach ($data as $v) {
            $this->news->setSortNews($v['id']);
        }
        return response()->json(['code' => 0, 'msg' => '设置成功！']);
    }

    public function cancelSortEdit(Request $request)
    {
        $data = $request->all();
        $data = $data['data'];
        foreach ($data as $v) {
            $this->news->cancelSortNews($v['id']);
        }
        return response()->json(['code' => 0, 'msg' => '设置成功！']);
    }

    public function classifyList(Request $request)
    {
        //资讯分类列表
        $page = $request->get('page') - 1;
        if ($page < 0) $page = 0;
        $pageSize = $request->get('limit');

        $title = $request->input('title');

        $whereData = [
            'title' => $title
        ];
        $where = $this->classify->getWhere($whereData);

        $data = $this->classify->getList($page, $pageSize, $where);

        $count = $this->classify->getCount($where);

        return ResultHelper::resources($data, ['count' => $count]);
    }

    public function classifyAdd(Request $request)
    {
        if ($request->get('id')) {
            //编辑
            $data = [
                'title' => $request->input('title'),
                'status' => $request->input('status') ? $request->input('status') : 0,
                'thumb' => $request->input('thumb'),
                'listorder' => $request->input('listorder'),
                'description' => $request->input('description'),
                'pid' => $request->input('pid'),
            ];

            //更新分类
            $res = Classify::where('id', $request->get('id'))->update($data);

            if ($res) {
                return response()->json(['code' => 0, 'status' => 0, 'msg' => '分类编辑成功！']);
            } else {
                return response()->json(['code' => 0, 'status' => 1, 'msg' => '分类编辑失败！']);
            }
        } else {
            $classify = Classify::create([
                'title' => $request->get('title'),
                'status' => $request->input('status') ? $request->input('status') : 0,
                'thumb' => $request->get('thumb'),
                'listorder' => $request->get('listorder'),
                'description' => $request->get('description'),
                'pid' => $request->get('pid'),
            ]);

            if ($classify) {
                return response()->json(['code' => 0, 'status' => 0, 'msg' => '分类添加成功！']);
            } else {
                return response()->json(['code' => 0, 'status' => 1, 'msg' => '分类添加失败！']);
            }
        }
    }

    public function classifyDelete(Request $request)
    {
        $id = $request->input('id');
        $res = Classify::where('id', $id)->delete();
        if ($res) {
            return response()->json(['code' => 0, 'status' => 0, 'msg' => '删除成功！']);
        } else {
            return response()->json(['code' => 0, 'status' => 1, 'msg' => '删除失败！']);
        }
    }

    public function setClassifyStatusEdit(Request $request)
    {
        $data = $request->all();
        $data = $data['data'];
        foreach ($data as $v) {
            $this->classify->setClassifyStatus($v['id']);
        }
        return response()->json(['code' => 0, 'msg' => '设置成功！']);
    }

    public function cancelClassifyStatusEdit(Request $request)
    {
        $data = $request->all();
        $data = $data['data'];
        foreach ($data as $v) {
            $this->classify->cancelClassifyStatus($v['id']);
        }
        return response()->json(['code' => 0, 'msg' => '设置成功！']);
    }

    public function activityList(Request $request)
    {
        //活动列表
        $page = $request->get('page') - 1;
        if ($page < 0) $page = 0;
        $pageSize = $request->get('limit');

        $title = $request->input('title');
        $originator = $request->input('originator');
        $address = $request->input('address');

        $whereData = [
            'title' => $title,
            'originator' => $originator,
            'address' => $address
        ];
        $where = $this->news->getActivityWhere($whereData);

        $data = $this->news->getActivityList($page, $pageSize, $where);

        $count = $this->news->getActivityCount($where);

        return ResultHelper::resources($data, ['count' => $count]);
    }

    public function activityAdd(Request $request)
    {
        if ($request->get('activity_id')) {
            //编辑
            $data = [
                'title' => $request->get('title'),
                'originator' => $request->input('originator'),
                'thumb' => $request->get('thumb'),
                'start_time' => $request->get('start_time'),
                'end_time' => $request->get('end_time'),
                'address' => $request->get('address'),
                'join' => $request->get('join'),
                'status' => $request->get('status') ? $request->get('status') : 0,
                'sort' => $request->get('sort'),
                'description' => $request->get('description') ? $request->get('description') : mb_substr($request->get('content'), 0, 20),
                'content' => $request->get('content'),
            ];

            //更新
            $res = Activity::where('activity_id', $request->get('activity_id'))->update($data);

            if ($res) {
                return response()->json(['code' => 0, 'status' => 0, 'msg' => '活动编辑成功！']);
            } else {
                return response()->json(['code' => 0, 'status' => 1, 'msg' => '活动编辑失败！']);
            }
        } else {
            $activity = Activity::create([
                'title' => $request->get('title'),
                'originator' => $request->input('originator'),
                'thumb' => $request->get('thumb'),
                'start_time' => $request->get('start_time'),
                'end_time' => $request->get('end_time'),
                'address' => $request->get('address'),
                'content' => $request->get('content'),
                'join' => $request->get('join'),
                'status' => $request->get('status') ? $request->get('status') : 0,
                'sort' => $request->get('sort'),
                'description' => $request->get('description') ? $request->get('description') : mb_substr($request->get('content'), 0, 20),
            ]);

            if ($activity) {
                return response()->json(['code' => 0, 'status' => 0, 'msg' => '活动添加成功！']);
            } else {
                return response()->json(['code' => 0, 'status' => 1, 'msg' => '活动添加失败！']);
            }
        }
    }

    public function activityDelete(Request $request)
    {
        $activity_id = $request->input('activity_id');
        $res = Activity::where('activity_id', $activity_id)->delete();
        if ($res) {
            return response()->json(['code' => 0, 'status' => 0, 'msg' => '删除成功！']);
        } else {
            return response()->json(['code' => 0, 'status' => 1, 'msg' => '删除失败！']);
        }
    }

    public function activityJoin(Request $request)
    {
        //报名列表
        $page = $request->get('page') - 1;
        if ($page < 0) $page = 0;
        $pageSize = $request->get('limit');

        $name = $request->input('name');
        $phone = $request->input('phone');
        $whereData = [
            'name' => $name,
            'phone' => $phone,
        ];
        $where = $this->news->getActivityJoinWhere($whereData);

        $data = $this->news->getActivityJoinList($page, $pageSize, $where);

        $count = $this->news->getActivityjoinCount($where);

        return ResultHelper::resources($data, ['count' => $count]);
    }

    public function setActivityStatusEdit(Request $request)
    {
        $data = $request->all();
        $data = $data['data'];
        foreach ($data as $v) {
            $this->news->setActivityStatusGoods($v['activity_id']);
        }
        return response()->json(['code' => 0, 'msg' => '设置成功！']);
    }

    public function cancelActivityStatusEdit(Request $request)
    {
        $data = $request->all();
        $data = $data['data'];
        foreach ($data as $v) {
            $this->news->cancelActivityStatusGoods($v['activity_id']);
        }
        return response()->json(['code' => 0, 'msg' => '设置成功！']);
    }

}