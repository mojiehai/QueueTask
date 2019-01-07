<?php

require __DIR__."/bootstrap.php";

use QueueTask\Load\Load;
use QueueTask\Daemon\Daemon;

$config = include './config.php';

Load::Queue($config);

$queueConfig = [
    'queueName' => 'testQueue', //队列名称
    'attempt' => 3,     //队列任务失败尝试次数，0为不限制
    'memory' => 128,    //允许使用的最大内存  单位:M
    'sleep' => 3,       //每次检测的时间间隔
    'delay' => 1,       //失败后延迟的秒数重新入队列
];

$processConfig = [
    // master 进程配置
    'checkWorkerInterval' => 600,   // 10分钟检测一次进程数量
    'maxWorkerNum' => 2,            //2个进程

    // worker 进程配置
    'executeTimes' => 0,    // 任务的最大执行次数(到次数后停止，master进程重新启动)(0为不限制)
    'limitSeconds' => 0,    // 工作进程最大执行时长(秒)(到时间后停止，master进程重新启动)(0为不限制)
];


// 监听命令
(Daemon::getInstance($queueConfig))->setProcessConfig($processConfig)->listenCommand();
