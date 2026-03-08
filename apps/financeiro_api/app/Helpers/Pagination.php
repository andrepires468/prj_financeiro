<?php

namespace App\Helpers;

// presposta padrão para paginação
class Pagination
{

    public static function Paginate($data, $count, $page, $size)
    {
        $result = [
            'Page' => intval($page),
            'PageSize' => intval($size),
            'PageRange' => intval(ceil($count / $size)),
            'TotalCount' => intval($count),
            'Registers' => $data
        ];
        return $result;
    }
}
