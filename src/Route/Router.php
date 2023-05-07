<?php
/**
 * Created by PhpStorm.
 * User: hlh XueSi <1592328848@qq.com>
 * Date: 2023/5/7
 * Time: 12:02 上午
 */
declare(strict_types=1);

namespace EasyApi\Library\Route;

class Router
{
    public static function init(&$routeCollector)
    {
        // 注册路由
        $routes = config('routes') ?? [];
        foreach ($routes as $route) {
            if (!class_exists($route)) {
                continue;
            }
            $routeObj = new $route;
            if (!$routeObj instanceof RouterInterface) {
                continue;
            }
            $routeObj->register($routeCollector);
        }
    }
}
