<?php
/**
 * auth irving
 * describe 路由核心类
 */
namespace Framework\Kernel;


class RegisteredRoute
{
    private static $gather = [];

    /**
     * @describe 绑定路由
     * @param array $route
     */
    public static function registered(array $route)
    {
        try {
            foreach ($route as $item => $value) {
                $method = strtolower($value['method']);

                // 组合uri
                $uri = $value['prefix'].$value['methodUrl'];
                self::$gather[$method][$uri] = [
                    'class' => $value['class'],
                    'method' => $value['methodName'],
                ];
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * @describe 获取路由详情
     * @param string $method
     * @param string $uri
     * @return array
     */
    public static function getGather(string $method, string $uri)
    {
        $method = strtolower($method);

        if (!isset(self::$gather[$method][$uri])) {
            throw new \Exception("No route found");
        }

        return self::$gather[$method][$uri];
    }
}