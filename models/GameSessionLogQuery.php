<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[GameSessionLog]].
 *
 * @see GameSessionLog
 */
class GameSessionLogQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return GameSessionLog[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return GameSessionLog|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
