<?php
/**
 * Created by PhpStorm.
 * User: Espresso
 * Date: 2019-02-18
 * Time: 14:26
 */

namespace App\Repositories;


use Prettus\Repository\Contracts\RepositoryInterface;

interface AttachmentRepository extends RepositoryInterface
{

    /**
     * Store user
     * @param array $payload
     * @return bool
     */
    public function store($payload = []);

    function getImgsByImgIds($ids);

}

