<?php
/**
 * auth irving
 * describe 框架开启工具
 */

define('ROOT_PATH', dirname(__DIR__));

// 引入框架内核
require ROOT_PATH.'/framework/start.php';
(new FrameworkStart())->run();