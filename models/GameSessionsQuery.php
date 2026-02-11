<?php

namespace app\models;

use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[GameSession]].
 *
 * @see GameSessions
 */
class GameSessionsQuery extends ActiveQuery
{

    /**
     * {@inheritdoc}
     * @return GameSessions[]|array
     */
    public function all($db = null): array
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return GameSessions|array|null
     */
    public function one($db = null): GameSessions|array|null
    {
        return parent::one($db);
    }
}
