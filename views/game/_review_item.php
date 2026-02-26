<?php

use app\components\RatingHelper;
use app\models\user\Review;
use yii\helpers\Html;

/* @var $model \app\models\user\Review */

?>
<div class="panel panel-default" style="margin-bottom: 15px; padding: 15px; border: 1px solid #ddd; border-radius: 5px;">
    <div class="panel-body">
        <h4>
            <?= RatingHelper::renderStars($model->rating); ?>
            <small class="text-muted">(<?= $model->rating?>/<?= Review::MAX_RATING?>)</small>
        </h4>
        <?php if ($model->comment): ?>
            <p><?= nl2br(Html::encode($model->comment)) ?></p>
        <?php endif; ?>
        <small class="text-muted">
            <?= Yii::t('app', 'От:') ?>
            <?php if ($model->user): ?>
                <strong><?= Html::encode($model->user->username) ?></strong>
            <?php else: ?>
                <strong><?= Yii::t('app', 'Неизвестный пользователь') ?></strong>
            <?php endif; ?>
            | <?= Yii::$app->formatter->asDatetime($model->created_at) ?>
        </small>
    </div>
</div>