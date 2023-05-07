<?php
/**
 * Created by PhpStorm.
 * User: hlh XueSi <1592328848@qq.com>
 * Date: 2023/5/7
 * Time: 12:27 上午
 */
declare(strict_types=1);

use EasySwoole\EasySwoole\Config as ESGlobalConfig;
use EasyApi\Library\Response as EasyApiResponse;

if (!function_exists('config')) {
    /**
     * @param mixed $keyPath  配置的key 支持多级
     *                        如 MYSQL.default.host
     *
     * @return array|mixed|null
     * @author: XueSi <1592328848@qq.com>
     * @date  : 2023/5/6 1:38 下午
     */
    function config(string $keyPath = '')
    {
        return ESGlobalConfig::getInstance()->getConf($keyPath);
    }
}

if (!function_exists('easy_config')) {
    /**
     * @param mixed $keyPath  配置的key 支持多级
     *                        如 MYSQL.default.host
     *
     * @return array|mixed|null
     * @author: XueSi <1592328848@qq.com>
     * @date  : 2023/5/6 1:38 下午
     */
    function easy_config($keyPath = '')
    {
        $config = ESGlobalConfig::getInstance();
        $config->loadDir(CONFIG_FILE_PATH);
        return $config->getConf($keyPath);
    }
}

if (!function_exists('json')) {
    /**
     * 响应json给到客户端
     *
     * @param mixed   $data    返回的数据
     * @param integer $code    状态码
     * @param array   $header  头部
     * @param array   $options 参数
     *
     * @return \EasyApi\Library\Response\Json
     */
    function json($data = [], int $code = 200, array $header = [], array $options = [])
    {
        $response = EasyApiResponse::create($data, 'json', $code, $header, $options);
        return $response->send();
    }
}
