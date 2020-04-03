<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class City extends Model implements Transformable
{
    use TransformableTrait;

    protected $table = 'sys_city';

    protected $primaryKey = 'city_id';

    protected $fillable = [
        'province_id', 'city_name', 'zipcode', 'is_open', 'listorder', 'citycode','begins'
    ];

    protected $appends = ['province_names'];

    public function getProvinceNamesAttribute()
    {
        return $this->province ? $this->province->province_name : '';
    }

    public $timestamps = false;

    public function province()
    {
        return $this->hasOne('\App\Model\Province', 'province_id', 'province_id');
    }


}
