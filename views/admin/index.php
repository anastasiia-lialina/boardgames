<?php

use app\components\RatingHelper;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel \app\models\search\ReviewsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Review Moderation');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="admin-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Yii::t('app', 'Reviews pending moderation: {count}', ['count' => $dataProvider->totalCount]) ?>
    </p>

    <?php Pjax::begin(); ?>

    <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],

                    'id',
                    [
                            'attribute' => 'gameTitle',
                            'label' => Yii::t('app', 'Game'),
                            'value' => function ($model) {
                                return Html::a($model->game->title, ['games/view', 'id' => $model->game_id], ['target' => '_blank']);
                            },
                            'format' => 'raw',
                    ],
                    [
                            'attribute' => 'username',
                            'value' => 'user.username',
                    ],
                    [
                            'attribute' => 'rating',
                            'format' => 'raw',
                            'value' => function ($model) {
                                return RatingHelper::renderStars($model->rating);
                            },
                    ],
                    [
                            'attribute' => 'comment',
                            'value' => function ($model) {
                                return $model->comment ? mb_substr($model->comment, 0, 100) . '...' : '-';
                            },
                    ],
                    'created_at:datetime',

                    [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{approve} {reject} {view}',
                            'buttons' => [
                                    'approve' => function ($url, $model) {
                                        return Html::a(Yii::t('app', 'Approve'), ['admin/approve', 'id' => $model->id], [
                                                'class' => 'btn btn-success btn-sm',
                                                'data' => [
                                                        'confirm' => Yii::t('app', 'Approve this review?'),
                                                        'method' => 'post',
                                                ],
                                        ]);
                                    },
                                    'reject' => function ($url, $model) {
                                        return Html::a(Yii::t('app', 'Reject'), ['admin/reject', 'id' => $model->id], [
                                                'class' => 'btn btn-danger btn-sm',
                                                'data' => [
                                                        'confirm' => Yii::t('app', 'Reject this review?'),
                                                        'method' => 'post',
                                                ],
                                        ]);
                                    },
                                    'view' => function ($url, $model) {
                                        return Html::a(Yii::t('app', 'View'), ['reviews/view', 'id' => $model->id], [
                                                'class' => 'btn btn-info btn-sm',
                                                'target' => '_blank',
                                        ]);
                                    },
                            ],
                    ],
            ],
    ]); ?>

    <?php Pjax::end(); ?>
</div>
