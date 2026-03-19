<?php

namespace app\models\game;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "game_session_log".
 *
 * @property int $id
 * @property int $session_id
 * @property null|string $old_status
 * @property null|string $new_status
 * @property string $changed_at
 */
class GameSessionLog extends ActiveRecord
{
    public static function tableName()
    {
        return 'game_session_log';
    }

    public function rules()
    {
        return [
            [['old_status', 'new_status'], 'default', 'value' => null],
            [['session_id'], 'required'],
            [['session_id'], 'default', 'value' => null],
            [['session_id'], 'integer'],
            [['changed_at'], 'safe'],
            [['old_status', 'new_status'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => \Yii::t('app', 'ID'),
            'session_id' => \Yii::t('app', 'Session ID'),
            'old_status' => \Yii::t('app', 'Old Status'),
            'new_status' => \Yii::t('app', 'New Status'),
            'changed_at' => \Yii::t('app', 'Changed At'),
        ];
    }
}
