<?php

namespace app\rbac;

use yii\rbac\Rule;

class OrganizerRule extends Rule
{
    public $name = 'isOrganizer';

    /**
     * @inheritDoc
     */
    public function execute($user, $item, $params)
    {
        return isset($params['model']) && (int)$params['model']->organizer_id === (int)$user;
    }
}
