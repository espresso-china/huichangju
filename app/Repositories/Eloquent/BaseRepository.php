<?php
/**
 * Created by PhpStorm.
 * User: tacker
 * Date: 2019/1/25
 * Time: 5:27 PM
 */

namespace App\Repositories\Eloquent;

use Prettus\Repository\Eloquent\BaseRepository as PrettusBaseRepository;

abstract class BaseRepository extends PrettusBaseRepository
{
    protected $current_model_name;

    public function switchModel($modelClassName)
    {
        $this->current_model_name = $modelClassName;

        return $this->model = $this->app->make($modelClassName);
    }
    
}