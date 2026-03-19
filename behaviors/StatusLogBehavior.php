<?php

namespace app\behaviors;

use app\models\game\GameSessionLog;
use yii\base\Behavior;
use yii\base\Event;
use yii\db\BaseActiveRecord;
use yii\db\Exception;

class StatusLogBehavior extends Behavior
{
    public function events(): array
    {
        return [
            BaseActiveRecord::EVENT_AFTER_UPDATE => 'logStatusChange',
        ];
    }

    /**
     * @throws Exception
     */
    public function logStatusChange(Event $event): void
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
