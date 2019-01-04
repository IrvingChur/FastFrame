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
        return self::searchRoute($request);
    }

    /**
     * @describe 搜索路由
     * @param \Swoole\Http\Request $request 请求类对象
     * @return array
     */
    private static function searchRoute(\Swoole\Http\Request $request)
    {
        $method = $request->server['request_method'];
        $uri = $request->server['request_uri'];

        try {
            $route = RegisteredRoute::getGather($method, $uri);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }

        return $route;
    }
}