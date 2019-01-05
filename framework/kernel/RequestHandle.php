<?php
/**
 * @auth irving
 * @describe 请求处理
 */

namespace Framework\Kernel;


class RequestHandle
{
    /**
     * @describe 请求处理
     * @param \Swoole\Http\Request $request 请求类对象
     * @return array
     */
    public static function handle(\Swoole\Http\Request $request)
    {
        return self::trigger(self::searchRoute($request));
    }

    /**
     * @describe 搜索路由
     * @param \Swoole\Http\Request $request 请求类对象
     * @return mixed
     */
    private static function searchRoute(\Swoole\Http\Request $request)
    {
        $method = $request->server['request_method'];
        $uri = $request->server['request_uri'];

        try {
            $route = RegisteredRoute::getGather($method, $uri);
        } catch (\Exception $e) {
            return null;
        }

        return $route;
    }

    /**
     * @describe 触发调度
     * @param mixed $route 解析后的路由信息
     * @return mixed
     */
    private static function trigger($route)
    {
        $return = [];

        // 路由错误或调度错误处理
        if (empty($route)) {
            $return = [
                'code' => 404,
                'data' => null,
            ];
        } else {
            try {
                $controller = new $route['class']();
                $method = $route['method'];
                $result = $controller->$method();
            } catch (\Exception $e) {
                $result = null;
            } finally {
                $code = $result ? 200 : 500 ;
                $return = [
                    'code' => $code,
                    'data' => $result,
                ];
            }
        }

        return json_encode($return);
    }
}