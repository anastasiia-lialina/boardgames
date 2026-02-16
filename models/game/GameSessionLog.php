<?php

namespace app\models\game;

use Yii;

/**
 * This is the model class for table "game_session_log".
 *
 * @property int $id
 * @property int $session_id
 * @property string|null $old_status
 * @property string|null $new_status
 * @property string $changed_at
 */
class GameSessionLog extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'game_session_log';
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'session_id' => Yii::t('app', 'Session ID'),
            'old_status' => Yii::t('app', 'Old Status'),
            'new_status' => Yii::t('app', 'New Status'),
            'changed_at' => Yii::t('app', 'Changed At'),
        ];
    }

}
