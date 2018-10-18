<?php

namespace QueueTask\Connection;
use QueueTask\Job\Job;

/**
 * 连接类
 * 增加新的存储介质，需要继承该类
 * Class Connection
 */
abstract class Connection
{

    /**
     * Connection constructor.
     */
    protected function __construct(){}

    /**
     * Connection destruct.
     */
    public function __destruct()
    {
        $this->close();
        static::$instance = null;
    }

    /**
     * 不允许被克隆
     * @throws \Exception
     */
    protected function __clone()
    {
        throw new \Exception("This class cannot be cloned" , -101);
    }

    /**
     * 获取单例
     * @param array $config 配置参数
     * @return Connection|null
     */
    public static function getInstance($config = [])
    {
        if(!(static::$instance instanceof Connection)) {
            static::$instance = new static($config);
        }
        return static::$instance;
    }

    /**
     * 返回存储方式(mysql/redis/file...)
     * @return String
     */
    abstract public function getType();


    /**
     * 关闭连接
     * @return boolean
     */
    abstract public function close();


    /**
     * 弹出队头任务(先删除后返回该任务)
     * @param $queueName
     * @return Job|null
     */
    abstract public function pop($queueName);


    /**
     * 压入队列
     * @param Job $job
     * @return boolean
     */
    abstract public function push(Job $job);


    /**
     * 添加一条延迟任务
     * @param int $delay    延迟的秒数
     * @param Job $job  任务
     * @return boolean
     */
    abstract public function laterOn($delay , Job $job);

} 