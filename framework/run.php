<?php
/**
 * auth irving
 * describe 框架入口文件
 */

final class Framework {

    public function __construct()
    {
        $this->loadKernel();
    }

    /**
     * @describe 初始化
     */
    private function loadKernel()
    {
        // 自动加载初始化
        require ROOT_PATH.'/framework/kernel/AutoLoading.php';
        \Framework\Kernel\AutoLoading::init();

        // 注解初始化
        \Framework\Kernel\Annotation::init();

    }

    /**
     * @describe 运行
     */
    public function run()
    {

    }

}