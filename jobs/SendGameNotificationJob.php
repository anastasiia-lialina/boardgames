<?php

namespace app\jobs;

use app\models\game\GameSubscription;
use app\notifications\SessionNotification;
use yii\base\BaseObject;
use yii\queue\RetryableJobInterface;
use Yii;

/**
 * Задание для отправки уведомлений подписчикам игры.
 */
class SendGameNotificationJob extends BaseObject implements RetryableJobInterface
{
    public $statusLabel;
    public $gameId;
    public $sessionId;
    public $sessionDate;

    public int $maxAttempts = 3;

    public int $ttr = 300;

    /**
     * @inheritdoc
     */
    public function execute($queue)
    {
        Yii::info("Старт рассылки: GameId {$this->gameId}", 'queue');

        $subscriptions = GameSubscription::find()
            ->where(['game_id' => $this->gameId])
            ->andWhere(['is_active' => GameSubscription::STATUS_ACTIVE])
            ->all();

        Yii::info("Найдено подписок: " . count($subscriptions), 'queue');

        foreach ($subscriptions as $sub) {
            SessionNotification::create($this->sessionId,[
                'userId'      => $sub->user_id,
                'gameName'    => $sub->game->title,
                'sessionDate' => $this->sessionDate,
                'statusLabel'  => $this->statusLabel
                ])->send();
        }
        Yii::info("Рассылка завершена. Отправлено уведомлений: " . count($subscriptions), 'queue');
    }

    /**
     * @inheritdoc
     * Максимальное количество попыток
     */
    public function getTtr()
    {
        return $this->ttr;
    }

    /**
     * @inheritdoc
     * Время до следующей попытки
     */
    public function getDelay($attempt, $exception)
    {
        return (60 * pow(2, $attempt - 1));
    }

    public function canRetry($attempt, $error)
    {
        return $attempt < $this->maxAttempts;
    }
}