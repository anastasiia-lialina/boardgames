<?php

use app\models\search\GameSubscriptionSearch;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel GameSubscriptionSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Subscriptions');
?>
<div class="game-subscription-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php Pjax::begin(); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'game.title',
                'value' => function ($model) {
                    return Html::a($model->game->title, ['game/view', 'id' => $model->game_id], [
                        'target' => '_blank',
                        'class' => 'text-decoration-none'
                    ]);
                },
                'format' => 'raw',
            ],
            [
                'attribute' => 'is_active',
                'format' => 'raw',
                'value' => function ($model) {
                    return $model->is_active
                        ? '<span class="badge bg-success">' . Yii::t('app', 'Active') . '</span>'
                        : '<span class="badge bg-secondary">' . Yii::t('app', 'Inactive') . '</span>';
                },
                'filter' => [
                    1 => Yii::t('app', 'Active'),
                    0 => Yii::t('app', 'Inactive'),
                ],
            ],
            [
                'attribute' => 'created_at',
                'format' => 'datetime',
            ],

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{toggle} {delete}',
                'buttons' => [
                    'toggle' => function ($url, $model) {
                        $title = $model->is_active
                            ? Yii::t('app', 'Deactivate')
                            : Yii::t('app', 'Activate');

                        return Html::a($title, ['toggle', 'id' => $model->id], [
                            'title' => $title,
                            'class' => 'btn btn-sm ' . ($model->is_active ? 'btn-secondary' : 'btn-primary'),
                            'data' => [
                                'method' => 'post',
                                'confirm' => Yii::t('app', 'Are you sure you want to change the status of the subscription?'),
                            ],
                        ]);
                    },
                    'delete' => function ($url, $model) {
                        return Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
                            'class' => 'btn btn-sm btn-danger',
                            'data' => [
                                'method' => 'post',
                                'confirm' => Yii::t('app', 'Are you sure you want to delete this subscription?'),
                            ],
                        ]);
                    },
                ],
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>