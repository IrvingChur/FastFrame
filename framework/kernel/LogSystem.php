<?php
/**
 * auth irving
 * describe 日志核心
 */
namespace Framework\Kernel;

use Swoole\Coroutine as co;

class LogSystem
{
    const LOG_LEVEL = [
        'operation',
        'warning',
        'severity',
        'deadly'
    ];

    /**
     * @describe 写入日志
     * @param $data mixed 信息
     * @param $level int 日志登记
     * @return boolean
     */
    public static function write($data, int $level = 0)
    {
        $logLevel = self::LOG_LEVEL[$level];

        if (!is_string($data)) {
            $data = serialize($data);
        }

        self::createDir(ROOT_PATH.'/runtime/'.$logLevel);

        // 写入日志
        $fileName = date('Y-m-d', time());
        $fileName = $fileName.'.log';
        $fileName = ROOT_PATH.'/runtime/'.$logLevel.'/'.$fileName;
        $string = date('Y-m-d H:i:s').':'.$data.PHP_EOL;
        // swoole协程写入
        return co::writeFile($fileName, $string, FILE_APPEND);
    }

    /**
     * @title 递归创建目录
     * @param $dirPath string 目录
     * @return boolean
     */
    private static function createDir($dirPath)
    {
        // 如果目录已经存在，直接返回
        if(is_dir($dirPath)) {
            return true;
        }
        return is_dir(dirname($dirPath)) || self::createDir(dirname($dirPath)) ? mkdir($dirPath) : false;
    }
}