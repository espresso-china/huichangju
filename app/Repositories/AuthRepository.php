<?php
/**
 * Created by PhpStorm.
 * User: Espresso
 * Date: 2019-03-20
 * Time: 10:38
 */

namespace App\Repositories;

use Prettus\Repository\Contracts\RepositoryInterface;

interface AuthRepository extends RepositoryInterface
{

    function getValidUser($data);

    function resetPassword($phone, $password);
}