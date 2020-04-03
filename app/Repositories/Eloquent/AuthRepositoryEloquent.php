<?php
/**
 * Created by PhpStorm.
 * User: Espresso
 * Date: 2019-03-20
 * Time: 10:38
 */

namespace App\Repositories\Eloquent;

use App\Model\User;
use App\Repositories\AuthRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Model\Attachment;

/**
 * Class OrderRepositoryEloquent.
 *
 * @package namespace App\Repositories\Eloquent;
 */
class AuthRepositoryEloquent extends BaseRepository implements AuthRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return User::class;
    }


    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    function getValidUser($data)
    {
        return $this->model->where(function ($query) use ($data) {
            $query->where('phone', $data['phone'])
                ->orWhere('user_email', $data['user_email']);
        })->get();
    }

    function resetPassword($phone, $password)
    {
        return $this->model->where('phone', $phone)->update(['password' => bcrypt($password)]);
    }
}