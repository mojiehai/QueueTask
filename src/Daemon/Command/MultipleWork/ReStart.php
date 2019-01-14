<?php

namespace QueueTask\Daemon\Command\MultipleWork;


use ProcessManage\Command\Action\Action;
use ProcessManage\Exception\Exception;
use ProcessManage\Process\Manage;
use ProcessManage\Process\ManageMultiple;
use QueueTask\Daemon\MultipleWorkDaemon;

/**
 * restart 命令动作
 * Class ReStart
 * @package QueueTask\Daemon\Command\MultipleWork
 */
class ReStart extends Action
{

    /**
     * 执行该命令的程序
     * @return void
     * @throws \Exception
     */
    public function handler()
    {
        $daemon = MultipleWorkDaemon::getInstance();

        if ($queueName = $this->getParam('queueName')) {
            // 单任务
            $work = $daemon->getWork($queueName);

            if (!empty($work)) {
                (new Manage($work->getProcessConfig()))
                    ->setWorkInit($work->getWorkInit())       // 设置初始化
                    ->setWork($work->getWork())               // 设置任务
                    ->setBackground()                           // 后台执行
                    ->restart();                                // restart

            } else {
                throw new Exception('There is no such queue');
            }

        } else {
            // 多任务
            $multipleManage = new ManageMultiple();

            foreach ($daemon->getWorks() as $work) {
                // 添加多个manage
                $multipleManage->addManage(
                    (new Manage($work->getProcessConfig()))
                        ->setWorkInit($work->getWorkInit())
                        ->setWork($work->getWork())
                );
            }

            $multipleManage->restart();
        }

    }

    /**
     * 获取命令
     * @return string
     */
    public static function getCommandStr()
    {
        return 'restart';
    }

    /**
     * 获取命令描述
     * @return string
     */
    public static function getCommandDescription()
    {
        return 'restart process';
    }
}