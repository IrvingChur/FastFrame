<?php
/**
 * auth irving
 * describe 自动加载核心
 */

namespace Framework\Kernel;

class AutoLoading
{
    /**
     * @describe 初始化自动加载
     */
    public static function init()
    {
        spl_autoload_register([new self(), 'autoLoading']);
    }

    /**
     * @describe 自动加载方法
     * @param $class string 类
     */
    public static function autoLoading(string $class)
    {
        $explodeClass = explode('\\', $class);
        $newClass = [];
        foreach ($explodeClass as $value) {
            if ($value != end($explodeClass)) {
                $newClass[] = strtolower($value);
            } else {
                $newClass[] = $value;
            }
        }

        $newClass = implode('/', $newClass);

        // 加载文件
        if (file_exists(ROOT_PATH.'/'.$newClass.'.php')) {
            include ROOT_PATH.'/'.$newClass.'.php';
        }
    }
}