<?php

namespace App\Http\Resources;

use App\Model\User;
use App\Model\Role;
use Illuminate\Http\Resources\Json\ResourceCollection;

class RoleList extends ResourceCollection
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
            'count' => Role::count(),
            'name' => $request->get('name', ''),
            'email' => $request->get('email', ''),
            'status' => $request->get('status', 0),
        ];
        //return parent::toArray($request);
    }
}
