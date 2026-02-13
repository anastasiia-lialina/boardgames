<?php

use yii\helpers\Html;

/* @var $model app\models\Reviews */

?>
<div class="panel panel-default" style="margin-bottom: 15px; padding: 15px; border: 1px solid #ddd; border-radius: 5px;">
    <div class="panel-body">
        <h4>
            <?= str_repeat('⭐', $model->rating) ?>
            <small class="text-muted">(<?= $model->rating ?>/5)</small>
        </h4>
        <?php if ($model->comment): ?>
            <p><?= nl2br(Html::encode($model->comment)) ?></p>
        <?php endif; ?>
        <small class="text-muted">
            <?= Yii::t('app', 'By: User #{id}', ['id' => $model->user_id]) ?> |
            <?= Yii::$app->formatter->asDatetime($model->created_at) ?>
        </small>
    </div>
</div>