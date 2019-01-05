<?php
/**
 * auth irving
 * describe swoole启动文件
 */

final class FrameworkStart {

    private $config = [];
    private $service;

    /**
     * FrameworkStart constructor.
     */
    public function __construct()
    {
        $this->loadEnvFile();
        $this->initService();
    }

    /**
     * @describe 初始化swoole
     */
    private function initService()
    {
        $this->service = new Swoole\Http\Server($this->config['TCP_HOST'], $this->config['TCP_PORT'], constant($this->config['TCP_MODE']), constant($this->config['TCP_TYPE']));

        // 配置
        $this->service->set([
            'worker_num' => $this->config['WORKER_NUM'],
            'backlog' => $this->config['BACKLOG'],
            'max_request' => $this->config['MAX_REQUEST'],
            'dispatch_mode' => $this->config['DISPATCH_MODE'],
            'daemonize' => $this->config['DAEMONIZE'],
        ]);

        $this->registerFunction();
    }

    /**
     * @describe 注册事件
     */
    private function registerFunction()
    {
        // 在此数组中加入需要注册的事件
        $functions = [
            ['WorkerStart', 'onWorkerStart'],
            ['Request', 'onRequest'],
        ];

        // 轮询注册
        try {
            foreach ($functions as $key => $value) {
                $this->service->on($value[0], [$this, $value[1]]);
            }
        } catch (\Exception $e) {
            // 注册失败时工作
        }
    }

    /**
     * @describe 加载.env文件的配置
     */
    private function loadEnvFile()
    {
        $fp = fopen(ROOT_PATH.'/.env',"r");

        while(!feof($fp)) {
            $stringResult = trim(fgets($fp));
            // 过滤设置标题
            if (strpos($stringResult, '#') === false && !empty($stringResult)) {
                $arrayConfig = explode('=', $stringResult);
                $this->config[$arrayConfig[0]] = $arrayConfig[1];
            }
        }

        fclose($fp);
    }

    /**
     * @describe 进程启动时
     * @param $service object
     * @param $workerId int
     */
    public function onWorkerStart($service, $workerId)
    {
        // 载入框架
        require ROOT_PATH.'/framework/run.php';
        (new Framework())->run();
    }

    /**
     * @describe 请求事件
     * @param $request
     * @param $response
     */
    public function onRequest($request, $response)
    {
        $requestHandle = \Framework\Kernel\RequestHandle::handle($request);

        // 测试用例 返回string
        $response->header('content-type', 'text/html;charset=utf-8', true);
        $response->end($requestHandle);
    }

    /**
     * @describe 运行
     */
    public function run()
    {
        $this->service->start();
    }

}