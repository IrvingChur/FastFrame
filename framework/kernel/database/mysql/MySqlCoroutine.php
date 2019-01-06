<?php
/**
 * auth irving
 * describe 继承swoole异步mysql用于保留创建时间
 */
namespace Framework\Kernel\Database\Mysql;


use \Swoole\Coroutine\MySQL as CoMySQL;

class MySqlCoroutine extends CoMySQL
{
    protected $usedAt = null;

    public function getUsedAt()
    {
        return $this->usedAt;
    }

    public function setUsedAt($time)
    {
        $this->usedAt = $time;
    }

    public function isConnected()
    {
        return $this->connected;
    }
}