<?php

namespace QueueTask\Worker;

use QueueTask\Queue\Queue;
use QueueTask\Job\Job;

/**
 * 任务监听
 * Class Worker
 */
class Worker
{

    /**
     * 启用一个队列后台监听任务
     * @param Queue $queue
     * @param string $queueName 监听队列的名称(在push的时候把任务推送到哪个队列，则需要监听相应的队列才能获取任务)
     * @param int $attempt 队列任务失败尝试次数，0为不限制
     * @param int $memory 允许使用的最大内存
     * @param int $sleep 每次检测的时间间隔
     * @param int $delay 失败后延迟的秒数重新入队列
     */
    public static function listen(Queue $queue, $queueName = 'default', $attempt = 10, $memory = 128, $sleep = 3, $delay = 0)
    {
        $job = null;
        while (true) {

            //弹出任务
            $job = $queue->pop($queueName);

            if($job instanceof Job) {

                if($attempt > 0 && $job->getAttempts() >= $attempt) {
                    //任务失败，触发回调
                    $job->failed();
                } else {

                    $job->execute();

                    if (! $job->isExec() ) {
                        //执行失败，重新将任务放入队尾
                        $job->release($delay);
                    } else {
                        //任务成功，触发回调
                        $job->success();
                    }
                }
            }

            self::sleep($sleep);


            if (self::memoryExceeded($memory)) {
                self::stop($queue);
            }
        }
    }



    /**
     * 判断内存使用是否超出
     * @param  int   $memoryLimit
     * @return bool
     */
    public static function memoryExceeded($memoryLimit)
    {
        return (memory_get_usage() / 1024 / 1024) >= $memoryLimit;
    }

    /**
     * 停止队列监听
     * @param Queue $queue
     */
    public static function stop(Queue $queue)
    {
        $queue->close();
        die;
    }

    /**
     * 休眠
     * @param int $seconds
     */
    public static function sleep($seconds)
    {
        sleep($seconds);
    }

} 