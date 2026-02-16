<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use app\models\GameSessions;

/* @var $this yii\web\View */
/* @var $searchModel app\models\SearchGameSessions */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Game Sessions');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="game-session-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Create Session'), ['create'], ['class' => 'btn btn-success']) ?>
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
                        'value' => 'game.title',
                    ],
                    [
                        'attribute' => 'organizerUsername',
                        'value' => 'organizer.username',
                    ],
                    'scheduled_at:datetime',
                    'max_participants',
                    [
                            'attribute' => 'status',
                            'value' => function ($model) {
                                $classMap = [
                                        GameSessions::STATUS_PLANNED => 'label-info',
                                        GameSessions::STATUS_ACTIVE => 'label-primary',
                                        GameSessions::STATUS_COMPLETED => 'label-success',
                                        GameSessions::STATUS_CANCELLED => 'label-danger',
                                ];
                                $class = $classMap[$model->status] ?? 'label-default';
                                return Html::tag('span', $model->statusLabel, ['class' => "label $class"]);
                            },
                            'format' => 'raw',
                            'filter' => function ($model) {
                                return $model->getStatusLabels();

                            },
                    ],
                    'created_at:datetime',

                    [
                            'class' => 'yii\grid\ActionColumn',
                    ],
            ],
    ]); ?>

    <?php Pjax::end(); ?>
</div>
