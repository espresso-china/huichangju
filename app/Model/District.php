<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class District extends Model implements Transformable
{
    use TransformableTrait;

    protected $table = 'sys_district';

    protected $primaryKey = 'district_id';

    protected $fillable = [
        'city_id', 'district_name'
    ];

    protected $appends = ['city_names'];

    public function getCityNamesAttribute()
    {
        return $this->city ? $this->city->city_name : '';
    }

    public $timestamps = false;

    public function city()
    {
        return $this->hasOne('\App\Model\City', 'city_id', 'city_id');
    }

}
