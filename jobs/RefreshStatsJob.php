<?php

namespace app\jobs;

use app\services\ReportService;
use Yii;
use yii\base\BaseObject;
use yii\queue\JobInterface;

class RefreshStatsJob extends BaseObject implements JobInterface
{
    public function execute($queue)
    {
        ReportService::refresh();
    }
}
