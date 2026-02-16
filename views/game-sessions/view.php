<?php

use app\models\game\GameSessions;
use yii\helpers\Html;
use yii\web\YiiAsset;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model GameSessions */

$this->title = Yii::t('app', 'Session #{id}', ['id' => $model->id]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Game Sessions'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
YiiAsset::register($this);
?>
<div class="game-session-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                        'confirm' => Yii::t('app', 'Are you sure you want to delete this session?'),
                        'method' => 'post',
                ],
        ]) ?>
    </p>

    <?= DetailView::widget([
            'model' => $model,
            'attributes' => [
                    'id',
                    [
                            'attribute' => 'game.title',
                            'label' => Yii::t('app', 'Game'),
                            'value' => Html::a($model->game->title, ['game/view', 'id' => $model->game_id], ['target' => '_blank']),
                            'format' => 'raw',
                    ],
                    [
                            'attribute' => 'organizer_id',
                            'value' => Yii::t('app', 'User #{id}', ['id' => $model->organizer_id]),
                    ],
                    'scheduled_at:datetime',
                    'max_participants',
                    [
                            'attribute' => 'status',
                            'value' => $model->statusLabel,
                    ],
                    'created_at:datetime',
            ],
    ]) ?>

</div>
