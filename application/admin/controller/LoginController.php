<?php
/**
 * auth irving
 * describe 注解路由测试
 */

namespace Application\Admin\Controller;

/**
 * @prefix=/login
 */
class LoginController
{
    /**
     * @url=/index
     * @method=get
     */
    public function index()
    {
        \Framework\Kernel\LogSystem::write('测试用例');
        return 'string';
    }
}