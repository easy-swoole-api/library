<?php
/**
 * Created by PhpStorm.
 * User: hlh XueSi <1592328848@qq.com>
 * Date: 2023/5/7
 * Time: 12:10 上午
 */
declare(strict_types=1);

namespace EasyApi\Library\Route;

interface RouterInterface
{
    public function register(&$routeCollector);
}
