<?php

use app\components\RatingHelper;
use app\models\user\Review;
use yii\helpers\Html;
use yii\web\YiiAsset;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var Review $model */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Review Moderation'), 'url' => ['admin/index']];
$this->params['breadcrumbs'][] = $this->title;
YiiAsset::register($this);
?>
<div class="reviews-view">

    <h1><?= Html::encode($this->title); ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']); ?>
        <?php if ($model->is_approved != Review::STATUS_APPROVED): ?>
            <?= Html::a(Yii::t('app', 'Approve'), ['admin/approve', 'id' => $model->id], ['class' => 'btn btn-success']); ?>
        <?php endif; ?>
        <?= Html::a(Yii::t('app', 'Reject'), ['admin/reject', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]); ?>
    </p>

    <?php echo DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'game_id',
            'user.username',
            [
                'attribute' => 'rating',
                'format' => 'raw',
                'value' => function ($model) {
                    return RatingHelper::renderStars($model->rating);
                },
            ],
            'comment:ntext',
            'is_approved:boolean',
            'created_at',
        ],
    ]); ?>

</div>
