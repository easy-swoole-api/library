<?php
/**
 * Created by PhpStorm.
 * User: hlh XueSi <1592328848@qq.com>
 * Date: 2023/5/7
 * Time: 11:34 下午
 */
declare(strict_types=1);

namespace EasyApi\Library\Process;

use EasySwoole\Component\Process\Config as ESProcessConfig;
use EasySwoole\Component\Process\Manager as ESProcessManager;

class ProcessManager
{
    public static function registerProcess()
    {
        $processes = config('processes');
        foreach ($processes as $processConfig) {
            if (!$processConfig['enable']) {
                continue;
            }
            $processConfigObject = new ESProcessConfig([
                'processName'         => $processConfig['process_name'], // 设置 进程名称
                'processGroup'        => $processConfig['process_group'], // 设置 进程组名称
                'arg'                 => $processConfig['arg'], // 传递参数到自定义进程中
                'redirectStdinStdout' => $processConfig['redirect_stdin_stdout'],
                'pipeType'            => $processConfig['pipe_type'],
                'enableCoroutine'     => $processConfig['enable_coroutine'], // 设置 自定义进程自动开启协程环境，
                'maxExitWaitTime'     => $processConfig['max_exit_wait_time'], // 设置 自定义进程自动开启协程环境，
            ]);
            $className = $processConfig['class'];
            if (!class_exists($className)) {
                throw new \Error($processConfig['class'] . ' class not exist.');
            }
            $process = new $className($processConfigObject);
            ESProcessManager::getInstance()->addProcess($process);
        }
    }
}
