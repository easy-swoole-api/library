<?php
/**
 * Created by PhpStorm.
 * User: hlh XueSi <1592328848@qq.com>
 * Date: 2023/5/6
 * Time: 1:35 下午
 */
declare(strict_types=1);

namespace EasyApi\Library\Env;

use EasySwoole\EasySwoole\Core;

class EnvManager
{
    const DEV_ENV = 'dev';
    const PRODUCE_ENV = 'produce';

    public static function getEnv()
    {
        return Core::getInstance()->runMode();
    }

    public static function isDev()
    {
        return self::getEnv() === self::DEV_ENV;
    }

    public static function isProduce()
    {
        return self::getEnv() === self::PRODUCE_ENV;
    }
}
