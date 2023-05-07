<?php
/**
 * Created by PhpStorm.
 * User: hlh XueSi <1592328848@qq.com>
 * Date: 2023/5/7
 * Time: 12:50 上午
 */
declare(strict_types=1);

namespace EasyApi\Library\Crontab;

use EasySwoole\Crontab\Config as CrontabConfig;
use EasySwoole\EasySwoole\Crontab\Crontab;

class CrontabManager
{
    public static function registerCrontab()
    {
        $crontabList = config('crontab');
        foreach ($crontabList as $crontab) {
            if ($crontab['enable']) {
                $crontabConfig = new CrontabConfig();
                $crontabConfig->setTempDir($crontab['temp_dir']);
                $crontabConfig->setServerName($crontab['server_name']);
                $crontabConfig->setWorkerNum($crontab['worker_num']);
                $crontabConfig->setOnException($crontab['on_exception']);
                $crontabObject = Crontab::getInstance($crontabConfig);
                $crontabClass = $crontab['class'];
                if (!class_exists($crontabClass)) {
                    throw new \Error($crontabClass . ' class not exist.');
                }
                $crontabObject->register(new $crontabClass());
            }
        }
    }
}
