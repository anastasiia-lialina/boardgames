<?php

use app\models\game\GameSession;
use app\models\search\GameSessionSearch;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

/**
 * @var yii\data\ActiveDataProvider$dataProvider
 * @var GameSessionSearch$searchModel
 */

$this->title = Yii::t('app', 'Game Sessions');
?>
<div class="game-session-index container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0"><?= Html::encode($this->title) ?></h1>
        <?php if (Yii::$app->user->can('createSession')): ?>
            <?= Html::a(Yii::t('app', 'Create Session'), ['create'], ['class' => 'btn btn-success btn-sm']) ?>
        <?php endif; ?>
    </div>

    <div class="table-responsive bg-white shadow-sm rounded border">
        <?php Pjax::begin(); ?>

        <?=
        GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'tableOptions' => ['class' => 'table table-hover mb-0'],
            'layout' => "{items}\n<div class='p-3 border-top small'>{pager}</div>",
            'columns' => [
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
                    'format' => 'raw',
                    'value' => function ($model) {
                        $colors = [
                            GameSession::STATUS_PLANNED => 'text-bg-info',
                            GameSession::STATUS_ACTIVE => 'text-bg-primary',
                            GameSession::STATUS_COMPLETED => 'text-bg-success',
                            GameSession::STATUS_CANCELLED => 'text-bg-danger',
                        ];
                        $color = $colors[$model->status] ?? 'text-bg-secondary';

                        return "<span class=\"badge $color\">" . Html::encode($model->statusLabel) . "</span>";
                    },
                    'filter' => $searchModel->getStatusLabels(),
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'visibleButtons' => [
                            'update' => function ($model, $key, $index) {
                                return Yii::$app->user->can('updateSession', ['model' => $model]);
                            },
                            'delete' => function ($model, $key, $index) {
                                return Yii::$app->user->can('deleteSession', ['model' => $model]);
                            },
                    ],
                    'buttonOptions' => ['class' => 'btn btn-link btn-sm text-decoration-none'],
                ],
            ],
        ]); ?>

        <?php Pjax::end(); ?>
    </div>
</div>
