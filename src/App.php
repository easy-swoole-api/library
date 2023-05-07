<?php
/**
 * Created by PhpStorm.
 * User: hlh XueSi <1592328848@qq.com>
 * Date: 2023/5/6
 * Time: 1:27 下午
 */
declare(strict_types=1);

namespace EasyApi\Library;

use App\Exception\EasyHttpExceptionHandler;
use App\Log\EasyLoggerHandler;
use App\Trigger\EasyTrigger;
use EasyApi\Library\Consts\CoreConst;
use EasyApi\Library\Consts\HttpConst;
use EasyApi\Library\Crontab\CrontabManager;
use EasyApi\Library\Env\EnvManager;
use EasyApi\Library\Process\ProcessManager;
use EasySwoole\Command\Color;
use EasySwoole\Component\Context\ContextManager;
use EasySwoole\Component\Di;
use EasySwoole\EasySwoole\Config;
use EasySwoole\EasySwoole\Config as ESGlobalConfig;
use EasySwoole\EasySwoole\ServerManager;
use EasySwoole\EasySwoole\SysConst;
use EasySwoole\FileWatcher\FileWatcher;
use EasySwoole\FileWatcher\WatchRule;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use EasySwoole\Http\Message\Status as HttpStatusCode;
use EasySwoole\Mysqli\Client;
use EasySwoole\ORM\Db\Connection;
use EasySwoole\ORM\DbManager;
use EasySwoole\Redis\Config\RedisConfig;
use EasySwoole\RedisPool\RedisPool;
use EasySwoole\Utility\File;
use EasyApi\Lang\Lang as EasyApiLang;
use EasySwoole\Command\CommandManager as ESCommandManager;

class App
{
    public static function initGlobalConst()
    {
        if (!defined('CONFIG_FILE_PATH')) {
            define('CONFIG_FILE_PATH', ROOT_PATH . CoreConst::DS . 'Config');
        }
        if (!defined('EXTRA_CONFIG_FILE_PATH')) {
            define('EXTRA_CONFIG_FILE_PATH', ROOT_PATH . CoreConst::DS . 'ExtraConfig');
        }
        if (!defined('LANG_PATH')) {
            define('LANG_PATH', ROOT_PATH . CoreConst::DS . 'App' . CoreConst::DS . 'LANG');
        }
    }

    public static function registerCommand()
    {
        $commands = easy_config('commands');
        $esCommandManager = ESCommandManager::getInstance();
        foreach ($commands as $command) {
            if (class_exists($command)) {
                $esCommandManager->addCommand(new $command());
            }
        }
    }

    public static function initialize()
    {
        $globalDi = Di::getInstance();

        // 修改默认的控制器命名空间
        $globalDi->set(SysConst::HTTP_CONTROLLER_NAMESPACE, CoreConst::HTTP_CONTROLLER_NAMESPACE);

        // 加载额外配置
        ESGlobalConfig::getInstance()->loadDir(CONFIG_FILE_PATH, true);
        ESGlobalConfig::getInstance()->loadDir(EXTRA_CONFIG_FILE_PATH, true);

        // 初始化日志配置
        $logDir = config('LOG.dir');
        $globalDi->set(SysConst::LOGGER_HANDLER, new EasyLoggerHandler($logDir));

        // 初始化错误触发器
        $globalDi->set(SysConst::TRIGGER_HANDLER, new EasyTrigger());

        // 初始化 HTTP 异常处理 handler
        $globalDi->set(SysConst::HTTP_EXCEPTION_HANDLER, [EasyHttpExceptionHandler::class, 'handler']);

        // 初始化 mysql orm 连接池
        self::initESOrm();

        // 初始化 redis 连接池
        self::initESRedisPool();

        // 初始化语言包
        self::initLang();
    }

    public static function initHttpGlobalOnRequest(Request $request, Response $response)
    {
        // 将请求实例 响应实例写入上下文
        ContextManager::getInstance()->set(CoreConst::HTTP_REQUEST, $request);
        ContextManager::getInstance()->set(CoreConst::HTTP_RESPONSE, $response);

        // 配置跨域
        $origin = $request->getHeaderLine('origin') ?? '*';
        $response->withHeader('Access-Control-Allow-Origin', $origin);
        $crossConfig = config('cross');
        foreach ($crossConfig as $name => $value) {
            $response->withHeader($name, $value);
        }
        if (HttpConst::isOptionsMethod($request->getMethod())) {
            $response->withStatus(HttpStatusCode::CODE_OK);
            return false;
        }

        return true;
    }

    public static function initHttpGlobalAfterRequest(Request $request, Response $response)
    {

    }

    public static function initMainServerCreate()
    {
        // 初始化热重载配置
        self::initFileWatcher();
        // 注册定时任务
        CrontabManager::registerCrontab();
        // 注册自定义进程
        ProcessManager::registerProcess();
    }

    public static function initFileWatcher()
    {
        $fileWatcherConfig = \config('file_watcher');
        $env = EnvManager::getEnv();
        if ($fileWatcherConfig['enable'] && in_array($env, $fileWatcherConfig['allow_mode'], true)) {
            $watcher = new FileWatcher();
            $rule = new WatchRule($fileWatcherConfig['monitor_dir']); // 设置监控规则和监控目录
            $watcher->addRule($rule);
            $watcher->setOnChange($fileWatcherConfig['on_change_handler']);
            $watcher->attachServer(ServerManager::getInstance()->getSwooleServer());
        }
    }

    public static function initESOrm()
    {
        $databaseConfig = \config('database');
        foreach ($databaseConfig as $connectionName => $config) {
            if ($config['driver_class'] === DbManager::class) {
                $configObj = new \EasySwoole\ORM\Db\Config($config);
                $connection = new Connection($configObj);
                DbManager::getInstance()->addConnection($connection, $connectionName);
            }
        }
    }

    public static function initESRedisPool()
    {
        $redisConfig = \config('redis');
        foreach ($redisConfig as $connectionName => $config) {
            if ($config['driver_class'] === RedisPool::class) {
                $configObj = new RedisConfig($config);
                RedisPool::getInstance()->register($configObj, $connectionName);
            }
        }
    }

    public static function initLang()
    {
        $fileList = File::scanDirectory(LANG_PATH);
        $lang = EasyApiLang::getInstance();
        foreach ($fileList['files'] as $filePath) {
            $range = pathinfo($filePath, PATHINFO_FILENAME);
            $lang->load($filePath, $range);
        }
    }
}
