<?php
/**
 * auth irving
 * describe 注解核心
 */

namespace Framework\Kernel;

class Annotation
{
    private static $controllerClass = [];

    /**
     * @describe 注解初始化
     */
    public static function init()
    {
        self::foreachDirectory(ROOT_PATH.'/application');
        self::reflectionClass();
    }

    /**
     * @describe 反射获取类详情
     */
    private static function reflectionClass()
    {
        try {
            foreach (self::$controllerClass as $item => $value) {
                $reflectionObject = new \ReflectionClass($value);
                // 获取类路由前缀
                $classAnnotation = $reflectionObject->getDocComment();
                $classPrefix = self::matchSomething($classAnnotation, '@prefix');
                if (empty($classPrefix)) {
                    // 如果类没有前缀直接跳过
                    continue;
                }

                // 获取方法路由
                $arrMethods = [];
                $methods = $reflectionObject->getMethods();
                foreach ($methods as $methodItem => $methodValue) {
                    $methodDes = [];
                    $methodDes['methodName'] = $methodValue->name;
                    $methodDes['methodUrl'] = self::matchSomething($methodValue->getDocComment(), '@url');
                    $methodDes['method'] = self::matchSomething($methodValue->getDocComment(), '@method');
                    $methodDes['prefix'] = $classPrefix;
                    $methodDes['class'] = $value;
                    $arrMethods[] = $methodDes;
                }

                // 注册路由
                RegisteredRoute::registered($arrMethods);

            }
        } catch (\Exception $e) {
            echo $e;
        }
    }

    /**
     * @describe 正则过滤获取器
     * @param string $string 需要过滤的字符
     * @return mixed
     */
    private static function matchSomething(string $string, string $someThing)
    {
        $matches = [];
        preg_match("/".$someThing."(.*)(\\r\\n|\\r|\\n)/U", $string, $matches);

        if (isset($matches[1])) {
            $return = trim($matches[1]);
            if (strpos($return, '=') !== false) {
                $return = substr($return, 1);
                return $return;
            }
        }

        return false;
    }

    /**
     * @describe 递归目录
     * @param $dirPath string 文件目录
     */
    private static function foreachDirectory(string $dirPath)
    {
        $resDir = opendir($dirPath);
        while($basename = readdir($resDir)) {
            // 当前文件路径
            $path = $dirPath.'/'.$basename;
            if(is_dir($path) AND $basename != '.' AND $basename != '..') {
                self::foreachDirectory($path);
            } else if (basename($path) != '.' AND basename($path) != '..') {
                // 判断为文件
                self::$controllerClass[] = self::getFileNamespace($path);
            }

        }

        closedir($resDir);
    }

    /**
     * @describe 将获取到的文件转换成类名
     */
    private static function getFileNamespace(string $filePath)
    {
        $arrFilePath = explode('/', $filePath);
        $arrFilePath = array_filter($arrFilePath);

        $arrNamespace = [];
        $mark = false;
        foreach ($arrFilePath as $item => $value) {

            if ($value === 'application') {
                $mark = true;
            }

            if ($mark === true) {
                $arrFileName = explode('.', $value);
                if (!empty($arrFileName[1])) {
                    $changeValue = $arrFileName[0];
                } else {
                    $changeValue = ucwords($value);
                }

                $arrNamespace[] = $changeValue;
            }
        }

        return implode('\\', $arrNamespace);
    }
}