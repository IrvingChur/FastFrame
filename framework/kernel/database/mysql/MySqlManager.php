<?php
/**
 * auth irving
 * describe mysql连接池管理
 */
namespace Framework\Kernel\Database\Mysql;

use \Swoole\Coroutine\MySQL as CoMySQL;
use \Swoole\Coroutine\Channel as CoChannel;

class MySqlManager
{
    protected $maxConnectNum;
    protected $minConnectNum;
    protected $config;
    protected $channel;
    protected $number;

    private $isRecycling = false; //回收开关

    public function __construct()
    {
        // 初始化配置
        $this->maxConnectNum = getenv('DB_MAX_ACTIVE');
        $this->minConnectNum = getenv('DB_MIN_ACTIVE');
        $this->config = [
            'host'     => getenv('DB_URI'),
            'port'     => getenv('DB_PORT'),
            'user'     => getenv('DB_USER'),
            'password' => getenv('DB_PASSWORD'),
            'database' => getenv('DB_DATABASE'),
            'timeout'  => getenv('DB_TIMEOUT'),
        ];
    }

    /**
     * @describe 初始化信道
     */
    public function initChannel()
    {
        // 最大值信道
        $this->channel = new CoChannel($this->maxConnectNum);
    }

    /**
     * @describe 判断满载
     * @return bool
     */
    private function isFull()
    {
        return $this->number === $this->maxConnectNum;
    }

    /**
     * @describe 判断空载
     * @return bool
     */
    private function isEmpty()
    {
        return $this->number === 0;
    }

    /**
     * @describe 大于最小连接数
     * @return bool
     */
    private function shouldRecover()
    {
        return $this->number > $this->minConnectNum;
    }

    /**
     * @describe number自增
     * @return int
     */
    private function increase()
    {
        return $this->number += 1;
    }

    /**
     * @describe number自减
     * @return int
     */
    private function decrease()
    {
        return $this->number -= 1;
    }

    /**
     * @describe 构建连接
     * @return bool|MySqlCoroutine
     */
    protected function build()
    {
        // 非满载
        if (!$this->isFull()) {
            $this->increase();
            $mysql = new MySqlCoroutine();
            $mysql->connect($this->config);
            $mysql->setUsedAt(time());
            return $mysql;
        }

        return false;
    }

    /**
     * @describe 重构连接
     * @param MySqlCoroutine $mysql
     * @return MySqlCoroutine
     */
    protected function rebuild(MySqlCoroutine $mysql)
    {
        $mysql->connect($this->config);
        $mysql->setUsedAt(time());

        return $mysql;
    }

    /**
     * @describe 摧毁
     * @return bool
     */
    protected function destroy()
    {
        if (!$this->isEmpty()) {
            $this->decrease();
            return true;
        }

        return false;
    }

    /**
     * @describe 增加实例连接
     * @param MySqlCoroutine $mysql
     * @return void
     */
    public function push(MySqlCoroutine $mysql)
    {
        // 添加实例到信道
        if (!$this->channel->isFull()) {
            $this->channel->push($mysql);
        }
    }

    /**
     * @describe 获取实例
     * @return bool|MySqlCoroutine
     */
    public function pop()
    {
        if ($mysql = $this->build()) {
            return $mysql;
        }
        $mysql = $this->channel->pop();
        $now   = time();
        if (!$mysql->isConnected()) {
            return $this->rebuild($mysql);
        }
        $mysql->setUsedAt($now);

        return $mysql;
    }

    /**
     * @describe 自回收机制
     * @param int $timeout 超时
     * @param int $sleep 检测间隔
     */
    public function autoRecycling($timeout = 200, $sleep = 20)
    {
        if (!$this->isRecycling) {
            // 开启自回收机制
            $this->isRecycling = true;
            // 常驻运行
            while (true) {
                Co::sleep($sleep);
                // 当前连接大于最小连接
                if ($this->shouldRecover()) {
                    $mysql = $this->channel->pop();
                    $now   = time();
                    if ($now - $mysql->getUsedAt() > $timeout) {
                        $this->decrease();
                    } else {
                        !$this->channel->isFull() && $this->channel->push($mysql);
                    }
                }
            }
        }
    }

}