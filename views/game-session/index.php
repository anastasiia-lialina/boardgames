<?php

use app\models\game\GameSession;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel \app\models\search\GameSessionSearch */
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
                                        GameSession::STATUS_PLANNED => 'bg-info text-dark',
                                        GameSession::STATUS_ACTIVE => 'bg-primary',
                                        GameSession::STATUS_COMPLETED => 'bg-success',
                                        GameSession::STATUS_CANCELLED => 'bg-danger',
                                ];

                                $class = $classMap[$model->status] ?? 'bg-secondary';

                                return Html::tag('span', $model->statusLabel, ['class' => "badge $class"]);
                            },

                            'format' => 'raw',
                            'filter' => function ($model) {
                                return $model->getStatusLabels();

                            },
                    ],
                    [
                            'class' => 'yii\grid\ActionColumn',
                    ],
            ],
    ]); ?>

    <?php Pjax::end(); ?>
</div>
