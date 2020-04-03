<?php

namespace App\Http\Resources;

use App\Model\User;
use App\Model\Permission;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PermissionList extends ResourceCollection
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'code' => 0,
            'msg' => '数据拉取成功',
            'data' => $this->collection,
            'count' => Permission::where('fid', $request->input('fid', 0))->count(),
            'name' => $request->get('name', '')
        ];
        //return parent::toArray($request);
    }
}
