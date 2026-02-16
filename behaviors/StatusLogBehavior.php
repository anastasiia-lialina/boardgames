<?php

namespace app\behaviors;

use app\models\game\GameSessionLog;
use yii\base\Behavior;
use yii\db\ActiveRecord;

class StatusLogBehavior extends Behavior
{
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_UPDATE => 'logStatusChange',
        ];
    }

    public function logStatusChange($event)
    {
        if (isset($event->changedAttributes['status'])) {
            $log = new GameSessionLog();
            $log->session_id = $this->owner->id;
            $log->old_status = $event->changedAttributes['status'];
            $log->new_status = $this->owner->status;
            $log->save();
        }
    }
}
