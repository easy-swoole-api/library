<?php
/**
 * Created by PhpStorm.
 * User: hlh XueSi <1592328848@qq.com>
 * Date: 2023/5/6
 * Time: 1:29 下午
 */
declare(strict_types=1);

namespace EasyApi\Library\Consts;

class HttpConst
{
    const METHOD_OPTIONS = 'OPTIONS';
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';

    public static function isOptionsMethod(string $method)
    {
        return self::METHOD_OPTIONS === $method;
    }

    public static function isGetMethod(string $method)
    {
        return self::METHOD_GET === $method;
    }

    public static function isPostMethod(string $method)
    {
        return self::METHOD_POST === $method;
    }
}
