<?php

use yii\helpers\Html;

/* @var $model \app\models\game\GameSession */

?>
<div class="panel panel-default" style="margin-bottom: 15px; padding: 15px; border: 1px solid #ddd; border-radius: 5px;">
    <div class="panel-body">
        <h4><?= Yii::$app->formatter->asDatetime($model->scheduled_at) ?></h4>
        <p><?= Yii::t('app', 'Max Participants') ?>: <?= $model->max_participants ?></p>
        <p><?= Yii::t('app', 'Status') ?>: <?= Html::encode($model->statusLabel) ?></p>
        <p>
            <?= Html::a('Подробнее', ['game-sessions/view', 'id' => $model->id], ['class' => 'btn btn-primary btn-sm']) ?>
        </p>
    </div>
</div>