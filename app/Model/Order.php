<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class Order extends Model implements Transformable
{
    use TransformableTrait;

    protected $table = 'sys_hotel_order';

    protected $primaryKey = 'id';

    protected $fillable = [
        'uid', 'hotel_id','remark'
    ];


    public const CREATED_TIME = 'create_time';
    public const UPDATE_TIME = 'update_time';

    public function Hotel()
    {
        return $this->hasOne('\App\Model\Hotel', 'hotel_id', 'hotel_id');
    }

    public function member()
    {
        return $this->hasOne('\App\Model\Member', 'uid', 'uid');
    }

}

