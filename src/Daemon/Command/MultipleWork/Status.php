<?php

namespace QueueTask\Daemon\Command\MultipleWork;


use ProcessManage\Command\Action\Action;
use ProcessManage\Process\Manage;
use QueueTask\Daemon\MultipleWorkDaemon;
use ProcessManage\Exception\Exception;
use ProcessManage\Process\ManageMultiple;

/**
 * status 命令动作
 * Class Status
 * @package QueueTask\Daemon\Command\MultipleWork
 */
class Status extends Action
{

    /**
     * 执行该命令的程序
     * @return void
     * @throws \Exception
     */
    public function handler()
    {
        $daemon = MultipleWorkDaemon::getInstance();

        $workList = [];
        if ($queueName = $this->getParam('queueName')) {
            // 单任务
            $work = $daemon->getWork($queueName);
            $workList[] = $work;

            if (!empty($work)) {

                $status = (new Manage($work->getProcessConfig()))
                    ->setWorkInit($work->getWorkInit())
                    ->setWork($work->getWork())
                    ->status();

            } else {
                throw new Exception('There is no such queue');
            }

        } else {
            // 多任务
            $multipleManage = new ManageMultiple();

            $workList = $daemon->getWorks();
            foreach ($workList as $work) {
                // 添加多个manage
                $multipleManage->addManage(
                    (new Manage($work->getProcessConfig()))
                        ->setWorkInit($work->getWorkInit())
                        ->setWork($work->getWork())
                );
            }

            $status = $multipleManage->status();


        }

        $queueConfig = ['QueueConfig' => []];
        // 转换queue_name
        foreach ($workList as $work) {
            $status = $work->formatQueueStatus($status);
            $queueConfig['QueueConfig'][] = $work->getQueueConfig();
        }

        $status = array_merge($queueConfig, $status);

        // 格式化显示
        Manage::showStatus($status);
    }

    /**
     * 获取命令
     * @return string
     */
    public static function getCommandStr()
    {
        return 'status';
    }

    /**
     * 获取命令描述
     * @return string
     */
    public static function getCommandDescription()
    {
        return 'process status';
    }
}