<?php

namespace App\Api\Controllers\Admin;

use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

use App\Model\User;
use App\Model\Role;
use App\Model\RoleUser;
use App\Model\Permission;
use App\Helpers\ResultHelper;
use App\Repositories\AuthRepository;
use App\Http\Resources\Resources;
use App\Http\Resources\UserList as UserListResource;
use App\Http\Resources\RoleList as RoleListResource;
use App\Http\Resources\PermissionList as PermissionListResource;


class AuthController extends BaseController
{
    private $rbac;

    /**
     * The authentication guard that should be used.
     *
     * @var string
     */
    public function __construct(AuthRepository $rbac)
    {
        $this->rbac = $rbac;
        parent::__construct();
    }

    /**登录
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function authenticate(Request $request)
    {
        $payload = [
            'password' => $request->get('password')
        ];
        try {
            $account = $request->get('account');
            if (preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/', $account)) {
                $payload['user_email'] = $account;
            } elseif (preg_match('/^1\d{10}$/', $account)) {
                $payload['phone'] = $account;
            } else {
                $payload['name'] = $account;
            }

            if (!$token = $this->auth->attempt($payload, true)) {
                return response()->json(['code' => 1, 'msg' => '用户名或密码错误！', 'data' => ''], 200);
            }
            $user = $this->auth->user();
            $user = [
                'id' => $user->id,
                'name' => $user->name,
                'type' => $user->type,
                'is_agree'=>$user->is_agree,
                'shop_id' => $user->shop_id,
                'shop_name' => $user->shop_name
            ];
            $data = ['access_token' => $token, 'user' => $user];
            return response()->json(['code' => 0, 'msg' => '登陆成功！', 'data' => $data], 200);
        } catch (JWTException $e) {
            return response()->json(['code' => 1, 'msg' => '不能创建token！', 'data' => ''], 500);
        }
    }

    /** register注册
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        if ($this->shop_id > 0) {
            $shop_id = $this->shop_id;
            $type = $this->user_info->type;
        } else {
            $shop_id = $request->input('shop_id', 0);
            $type = $request->input('type', User::TYPE_IS_ADMIN);
        }

        $newUser = [
            'type' => $type,
            'shop_id' => $shop_id,
            'user_email' => $request->get('user_email'),
            'name' => $request->get('name'),
            'avatar' => $request->get('avatar', ''),
            'phone' => $request->get('phone'),
            'disabled' => $request->get('status', 0) ? 1 : 0
        ];

        $password = $request->get('password');

        if ($password) {
            $newUser['password'] = bcrypt($request->get('password'));
        }

        if ($id = $request->get('id')) {

            $validator = Validator::make($newUser, [
                'name' => [
                    'required', 'max:50',
                    Rule::unique('sys_users')->ignore($id)
                ],
                'phone' => [
                    'required', 'max:11',
                    Rule::unique('sys_users')->ignore($id)
                ],
                'user_email' => [
                    'required', 'max:50',
                    Rule::unique('sys_users')->ignore($id)
                ]
            ]);

            if ($validator->fails()) {
                return ResultHelper::json_error('输入数据验证失败', $validator->errors(), ResultHelper::ERROR_VALIDATOR_CODE);
            }

            //编辑
            $user = User::find($id);
            if (!$user)
                return response()->json(['code' => 1, 'msg' => '用户不存在！']);

            $res = User::where('id', $user->id)->update($newUser);

            //删除原角色
            $user->roles()->detach();

            //分配新角色
            if ($request->get('roles')) {
                $user->attachRoles($request->get('roles')); // id only
            }

            if ($res) {
                return response()->json(['code' => 0, 'msg' => '编辑成功！']);
            }
            return response()->json(['code' => 1, 'msg' => '编辑失败！']);
        } else {
            //创建
            $data = [
                'phone' => $request->input('phone'),
                'user_email' => $request->input('user_email'),
                'name' => $request->input('name'),
                'password' => $request->input('password')
            ];

            $validator = Validator::make($data, [
                'name' => 'required|max:50',
                'phone' => 'required|unique:sys_users|max:11',
                'user_email' => 'required|unique:sys_users|max:50',
                'password' => 'required|max:50|min:6',
            ]);

            if ($validator->fails()) {
                return ResultHelper::json_error('输入数据验证失败', $validator->errors(), ResultHelper::ERROR_VALIDATOR_CODE);
            }

            $user = User::create($newUser);
            if (!$request->get('roles')) {
                return response()->json(['code' => 1, 'msg' => '请选择权限！']);
            }
            //分配角色
            if ($request->get('roles')) {
                $user->attachRoles($request->get('roles')); // id only
            }

            if ($user) {
                return response()->json(['code' => 0, 'msg' => '创建成功！']);
            }
            return response()->json(['code' => 1, 'msg' => '创建失败！']);
        }
    }

    /****
     * 获取用户的信息
     * @return \Illuminate\Http\JsonResponse
     */
    public function AuthenticatedUser()
    {
        try {
            if (!$user = $this->auth->user()) {
                return response()->json(['code' => 401, 'msg' => ['user_not_found']]);
            }
        } catch (TokenExpiredException $e) {
            return response()->json(['code' => $e->getStatusCode(), 'msg' => ['token_expired']]);
        } catch (TokenInvalidException $e) {
            return response()->json(['code' => $e->getStatusCode(), 'msg' => ['token_invalid']]);
        } catch (JWTException $e) {
            return response()->json(['code' => $e->getStatusCode(), 'msg' => ['token_absent']]);
        }
        // the token is valid and we have found the user via the sub claim
        return response()->json(['code' => 0, 'msg' => 'success', 'data' => $user]);
    }

    /****
     * 获取用户的信息
     * @return \Illuminate\Http\JsonResponse
     */
    public function AuthenticatedUserProfile()
    {
        try {
            if (!$user = $this->auth->user()) {
                return response()->json(['code' => 401, 'msg' => ['user_not_found']]);
            }
        } catch (TokenExpiredException $e) {
            return response()->json(['code' => $e->getStatusCode(), 'msg' => ['token_expired']]);
        } catch (TokenInvalidException $e) {
            return response()->json(['code' => $e->getStatusCode(), 'msg' => ['token_invalid']]);
        } catch (JWTException $e) {
            return response()->json(['code' => $e->getStatusCode(), 'msg' => ['token_absent']]);
        }
        // the token is valid and we have found the user via the sub claim
        return response()->json(['code' => 0, 'msg' => 'success', 'data' => $user]);
    }

    /****
     * 获取用户的信息
     * @return \Illuminate\Http\JsonResponse
     */
    public function AuthenticatedUserProfileSave(Request $request)
    {
        try {
            if (!$user = $this->auth->user()) {
                return response()->json(['code' => 401, 'msg' => ['user_not_found']]);
            }
        } catch (TokenExpiredException $e) {
            return response()->json(['code' => $e->getStatusCode(), 'msg' => ['token_expired']]);
        } catch (TokenInvalidException $e) {
            return response()->json(['code' => $e->getStatusCode(), 'msg' => ['token_invalid']]);
        } catch (JWTException $e) {
            return response()->json(['code' => $e->getStatusCode(), 'msg' => ['token_absent']]);
        }

        $newUser = [
            'phone' => $request->input('phone'),
            'name' => $request->input('name'),
            'avatar' => $request->input('avatar')
        ];

        User::where('id', $this->user_info->id)->update($newUser);

        // the token is valid and we have found the user via the sub claim
        return response()->json(['code' => 0, 'msg' => 'success', 'data' => $user]);
    }

    /****
     * 获取用户的信息
     * @return \Illuminate\Http\JsonResponse
     */
    public function AuthenticatedUserPwdSave(Request $request)
    {
        try {
            if (!$user = $this->auth->user()) {
                return response()->json(['code' => 401, 'msg' => ['user_not_found']]);
            }
        } catch (TokenExpiredException $e) {
            return response()->json(['code' => $e->getStatusCode(), 'msg' => ['token_expired']]);
        } catch (TokenInvalidException $e) {
            return response()->json(['code' => $e->getStatusCode(), 'msg' => ['token_invalid']]);
        } catch (JWTException $e) {
            return response()->json(['code' => $e->getStatusCode(), 'msg' => ['token_absent']]);
        }

        $password = $request->input('password');
        $repassword = $request->input('repassword');
        if (empty($password) || $password != $repassword) {
            return response()->json(['code' => 500, 'msg' => '新密码两者不一致']);
        }

        $payload = [
            'user_email' => $user->user_email,
            'password' => $request->get('oldPassword')
        ];
        if (!$token = $this->auth->attempt($payload)) {
            return response()->json(['code' => 500, 'msg' => '用户名或密码错误！', 'data' => '']);
        }

        $newUser = [
            'password' => bcrypt($request->input('password'))
        ];

        User::where('id', $this->user_info->id)->update($newUser);

        // the token is valid and we have found the user via the sub claim
        return response()->json(['code' => 0, 'msg' => 'success', 'data' => $token]);
    }

    public function agreeRule(Request $request){
        $id= $request->input('id');
        User::where('id', $id)->update(['is_agree'=>1]);
        $user = User::where('id',$id)->first();
        $userInfo = [
            'id' => $user->id,
            'name' => $user->name,
            'type' => $user->type,
            'is_agree'=>$user->is_agree,
            'shop_id' => $user->shop_id,
            'shop_name' => $user->shop_name
        ];
        $data = [ 'user' => $userInfo];
        return response()->json(['code' => 0, 'msg' => 'success！', 'data' => $data], 200);

    }

    /**
     * @param Request $request
     * @return UserListResource
     */
    public function userList(Request $request)
    {
        $page = $request->get('page') - 1;
        if ($page < 0) $page = 0;
        $pageSize = $request->get('limit');

        if ($this->user_info->type == User::TYPE_IS_SHOP) {
            $lists = User::where('shop_id', $this->shop_id)->orderBy('id', 'desc')
                ->skip($page * $pageSize ?? 0)->take($pageSize ?? 10)->get();
        } else {
            $lists = User::orderBy('id', 'desc')
                ->skip($page * $pageSize ?? 0)->take($pageSize ?? 10)->get();
        }

        return new UserListResource($lists);
    }

    /**删除角色
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function userDelete(Request $request)
    {
        $userId = $request->get('id', 0);

        $user = User::find($userId);

        //不允许商家删除门店管理员
        if ($user->hasRole('shop-admin') && $this->user_info->type == User::TYPE_IS_SHOP) {
            return response()->json(['code' => 1, 'msg' => '不能删除门店管理员']);
        }

        //删除
        $user = $user->delete();
        $res = RoleUser::where('user_id', $userId)->delete();

        if ($user) {
            return response()->json(['code' => 0, 'msg' => '删除成功！']);
        } else {
            return response()->json(['code' => 1, 'msg' => '删除失败！']);
        }
    }


    /**角色列表
     * @param Request $request
     * @return RoleListResource
     */
    public function roleList(Request $request)
    {
        $page = $request->get('page') - 1;
        if ($page < 0) $page = 0;
        $pageSize = $request->get('limit');

        if ($this->user_info->type == User::TYPE_IS_SHOP) {
            $roleList = Role::where('shop_id', $this->shop_id)->orderBy('id', 'desc')
                ->skip($page * $pageSize ?? 0)->take($pageSize ?? 10)->get();
        } else {
            $roleList = Role::orderBy('id', 'desc')
                ->skip($page * $pageSize ?? 0)->take($pageSize ?? 10)->get();
        }
        return new RoleListResource($roleList);
    }

    /**权限列表
     * @param Request $request
     * @return PermissionListResource
     */
    public function permissionList(Request $request)
    {
        $page = $request->get('page') - 1;
        if ($page < 0) $page = 0;
        $pageSize = $request->get('limit');

        $fid = $request->input('fid', 0);

        $lists = Permission::where('fid', $fid)->orderBy('sort', 'asc')->orderBy('id', 'asc')
            ->skip($page * $pageSize ?? 0)->take($pageSize ?? 10)->get();

        return new PermissionListResource($lists);
    }

    /**新增和编辑权限
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function permissionAdd(Request $request)
    {
        $input = $request->except('token');
        $input['type'] = is_array($input['type']) ? implode(',', $input['type']) : $input['type'];
        if (isset($input['id']) && $input['id']) {
            $input['name'] = $input['name'] == '#' ? '#-' . time() : $input['name'];
            $input['is_menu'] = isset($input['is_menu']) && $input['is_menu'] ? 1 : 0;
            $input['disabled'] = isset($input['disabled']) && $input['disabled'] ? 1 : 0;
            Permission::where('id', $input['id'])->update($input);
        } else {
            Permission::create($input);
        }
        return response()->json(['code' => 0, 'msg' => '保存成功！']);
    }

    public function permissionInfo(Request $request)
    {
        $id = $request->input('id');
        $info = Permission::find($id);
        return response()->json(['code' => 0, 'data' => $info]);
    }

    /**删除权限
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function permissionDelete(Request $request)
    {
        $res = Permission::where('id', $request->get('id', 0))->delete();
        if ($res) {
            return response()->json(['code' => 0, 'msg' => '删除成功！']);
        } else {
            return response()->json(['code' => 1, 'msg' => '删除失败！']);
        }
    }

    /**获取可用权限
     * @return PermissionListResource
     */
    public function allPermission(Request $request)
    {
        $permissionIds = [];

        if ($id = $request->get('id')) {
            $role = Role::find($id);
            $type = $role->type;
            $permissionIds = $role->perms()->allRelatedIds()->toArray();
        } else {
            $type = $request->input('type', 1);
        }

        $permissions = Permission::where('disabled', 0)->where('fid', 0)->whereRaw(' find_in_set(' . $type . ',type) ')->orderBy('sort', 'asc')->get();

        return ResultHelper::json_result('success', ['permissions' => $permissions, 'has' => $permissionIds]);
    }

    public function menus(Request $request)
    {
        $permissions = Permission::where('disabled', 0)->where('fid', 0)->orderBy('sort', 'asc')->get();
        $menus = [];
        foreach ($permissions as $permission) {
            $sub_menus = [];
            foreach ($permission->sub_permission as $item) {
                if ($item->is_menu) {
                    $sub_menus[] = [
                        'id' => $item->id,
                        'display_name' => $item->display_name,
                        'name' => $item->name,
                    ];
                }
            }

            $menus[] = [
                'id' => $permission->id,
                'display_name' => $permission->display_name,
                'name' => $permission->name,
                'sub_menus' => $sub_menus
            ];
        }

        return new Resources(new Collection($menus));
    }

    /**
     * 当前用户菜单
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function userMenusData(Request $request)
    {
        try {
            if (!$user = $this->auth->user()) {
                return response()->json(['code' => 401, 'msg' => ['user_not_found']]);
            }

            $permissions = [];
            $roles = $user->cachedRoles();
            foreach ($roles as $role) {
                $permissions = array_merge($permissions, $role->cachedPermissions()->toArray());
            }

            $menus = $this->subMenus(0, $permissions);

            return response()->json(['code' => 0, 'msg' => 'success', 'data' => $menus]);
        } catch (TokenExpiredException $e) {
            return response()->json(['code' => $e->getStatusCode(), 'msg' => ['token_expired']]);
        } catch (TokenInvalidException $e) {
            return response()->json(['code' => $e->getStatusCode(), 'msg' => ['token_invalid']]);
        } catch (JWTException $e) {
            return response()->json(['code' => $e->getStatusCode(), 'msg' => ['token_absent']]);
        }
    }

    private function subMenus($fid, $permissions)
    {
        $menus = [];
        foreach ($permissions as $permission) {
            if ($permission['fid'] == $fid && $permission['is_menu']) {
                $menu = [
                    'name' => $permission['name'],
                    'title' => $permission['display_name'],
                    'icon' => $permission['icon'],
                ];

                if ($permission['uri']) {
                    $menu['jump'] = $permission['uri'];
                } else {
                    $menu['list'] = $this->subMenus($permission['id'], $permissions);
                }

                $menus[] = $menu;
            }
        }
        return $menus;
    }

    /**添加和编辑角色
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function roleAdd(Request $request)
    {
        if ($this->shop_id > 0) {
            $shop_id = $this->shop_id;
            $name = 'shop' . $shop_id . ':' . $request->input('name');
        } else {
            $shop_id = $request->input('shop_id', 0);
            $name = $request->input('name');
        }

        if ($request->get('id')) {
            //编辑
            $role = Role::find($request->get('id'));

            $data = [
                'type' => $request->input('type'),
                'shop_id' => $shop_id,
                'name' => $name,
                'display_name' => $request->input('display_name'),
                'description' => $request->input('description')
            ];

            $validator = Validator::make($data, [
                'name' => ['required', 'max:255', Rule::unique('sys_roles')->ignore($request->get('id'))],
                'display_name' => 'required|max:255',
            ]);

            if ($validator->fails()) {
                return ResultHelper::json_error('输入数据验证失败', $validator->errors(), ResultHelper::ERROR_VALIDATOR_CODE);
            }

            //更新角色名称
            $res = Role::where('id', $request->get('id'))->update($data);

            $permission_ids = $request->get('permission');
            $role->perms()->sync($permission_ids);

            if ($res) {
                return response()->json(['code' => 0, 'msg' => '角色编辑成功！']);
            } else {
                return response()->json(['code' => 1, 'msg' => '角色编辑失败！']);
            }
        } else {
            if (!$request->get('name'))
                return response()->json(['code' => 1, 'msg' => '角色名称不能为空！']);

            $exist = Role::where('name', $name)->count();
            if ($exist) {
                return response()->json(['code' => 1, 'msg' => '角色标志已存在！']);
            }

            $data = [
                'type' => $request->input('type'),
                'shop_id' => $shop_id,
                'name' => $name,
                'display_name' => $request->input('display_name'),
                'description' => $request->input('description')
            ];


            $validator = Validator::make($data, [
                'name' => 'required|unique:sys_roles|max:255',
                'display_name' => 'required|max:255',
            ]);

            if ($validator->fails()) {
                return ResultHelper::json_error('输入数据验证失败', $validator->errors(), ResultHelper::ERROR_VALIDATOR_CODE);
            }

            $role = Role::create($data);

            $permission_ids = $request->get('permission');
            $role->perms()->sync($permission_ids);
            if ($role) {
                return response()->json(['code' => 0, 'msg' => '角色添加成功！']);
            } else {
                return response()->json(['code' => 1, 'msg' => '角色添加失败！']);
            }
        }
    }

    public function roleInfo(Request $request)
    {
        $id = $request->input('id');
        $info = Role::find($id);

        $permissions = Permission::where('disabled', 0)->get();
        if ($id = $request->get('id')) {
            //编辑
            $permissionIds = $info->perms()->allRelatedIds()->toArray();
            foreach ($permissions as $v) {
                $v->checked = '';
                if (in_array($v->id, $permissionIds)) {
                    $v->checked = 'checked';
                }
            }
        }

        return response()->json(['code' => 0, 'data' => ['info' => $info, 'permissions' => $permissions]]);
    }

    /**删除角色
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function roleDelete(Request $request)
    {
        $roleId = $request->get('id', 0);
        //删除绑定权限
        $role = Role::findById($roleId);
        $permission = $role->permissions()->allRelatedIds()->toArray();
        $permission = Permission::whereIn('id', $permission)->get();
        $role->revokePermissionTo($permission);
        //删除角色
        $res = Role::where('id', $roleId)->delete();
        if ($res) {
            return response()->json(['code' => 0, 'msg' => '删除成功！']);
        } else {
            return response()->json(['code' => 1, 'msg' => '删除失败！']);
        }
    }

    /**获取可用角色
     * @param $request
     * @return RoleListResource
     */
    public function allRoles(Request $request)
    {
        $current_user_info = $this->auth->user();
        $shop_id = $current_user_info->shop_id;

        if ($shop_id > 0) {
            $roles = Role::where('shop_id', $shop_id)->where('type', $current_user_info->type)->get();
        } else {
            $roles = Role::where('shop_id', 0)->get();
        }

        if ($id = $request->get('id')) {
            $user = User::find($id);
            $hasRole = $user->roles()->pluck('id')->toArray();
            foreach ($roles as $v) {
                if (in_array($v->id, $hasRole)) {
                    $v->checked = 'checked';
                }
            }
        }
        return new RoleListResource($roles);
    }

    /**修改管理员状态
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeAdminStatus(Request $request)
    {
        $id = $request->get('id');
        $data['status'] = $request->get('status') ? 1 : 0;
        if ($id) {
            User::where('id', $id)->update($data);
            return response()->json(['code' => 0, 'msg' => '操作成功！']);
        }
        return response()->json(['code' => 1, 'msg' => '操作失败！']);
    }

    /**修改权限状态
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePermissionStatus(Request $request)
    {
        $id = $request->get('id');
        $data['disabled'] = $request->get('status') ? 1 : 0;
        if ($id) {
            Permission::where('id', $id)->update($data);
            return response()->json(['code' => 0, 'msg' => '操作成功！']);
        }
        return response()->json(['code' => 1, 'msg' => '操作失败！']);
    }
}
