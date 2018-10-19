<?php

namespace QueueTask\Job;

use QueueTask\Connection\ConnectAdapter;
use QueueTask\Exception\TaskException;
use QueueTask\Handler\JobHandler;

class GeneralJob extends Job
{

    private $handler;           //job handler         JobHandler
    private $isexec;            //是否执行成功         boolean
    private $attempts;          //已经执行次数         Int
    private $func;              //执行方法             String
    private $param;             //执行参数             array

    public $queueName;          //队列名称              String

    /**
     * @param String $queueName   队列名称
     * @param JobHandler $handler 回调类
     * @param String $func        回调类中的回调方法名
     * @param array $param        该回调方法需要的参数数组
     */
    public function __construct($queueName, JobHandler $handler , $func , array $param)
    {
        parent::__construct($queueName);

        $this->init();

        $this->handler = $handler;
        $this->func    = $func;
        $this->param   = $param;
        $this->queueName = $queueName;
    }

    /**
     * 初始化默认任务参数
     */
    public function init()
    {
        $this->isexec = false;
        $this->attempts = 0;
    }

    /**
     * 该任务已经执行的次数
     * @return int
     */
    public function getAttempts()
    {
        return $this->attempts;
    }


    /**
     * 任务失败回调
     * @return void
     */
    public function failed()
    {
        $this -> handler -> failed($this,$this->func,$this->param);
    }

    /**
     * 任务成功回调
     * @return void
     */
    public function success()
    {
        $this -> handler -> success($this,$this->func,$this->param);
    }

    /**
     * 执行任务
     * @return mixed
     */
    public function execute()
    {
        $this -> attempts ++;
        try{

            //执行handler回调
            $this->handler->handler($this,$this->func,$this->param);

            $this->isexec = true;

        }catch (TaskException $e){

            $this -> isexec = false;

        }

    }

    /**
     * 任务是否执行成功
     * @return boolean
     */
    public function isExec()
    {
        return $this->isexec;
    }


    /**
     * 重试该任务
     * @param int $delay 延迟秒数
     * @return mixed
     */
    public function release($delay = 0)
    {
        return ConnectAdapter::getConnection($this->connectType) -> laterOn($delay , $this);
    }


}