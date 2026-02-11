<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Games */
/* @var $reviews app\models\Reviews[] */
/* @var $upcomingSessions app\models\GameSessions[] */
/* @var $reviewForm app\models\Reviews */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Games', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="game-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                        'confirm' =>  Yii::t('app', 'Are you sure you want to delete this item?'),
                        'method' => 'post',
                ],
        ]) ?>
    </p>

    <?= DetailView::widget([
            'model' => $model,
            'attributes' => [
                    'id',
                    'title',
                    'description:ntext',
                    [
                            'attribute' => 'players_min',
                            'format' => 'raw',
                            'value' => $model->players_min . '–' . $model->players_max . ' игроков',//Todo yii::t
                    ],
                    [
                            'attribute' => 'duration_min',
                            'label' => Yii::t('app', 'Время игры'),
                            'format' => 'raw',
                            'value' => 'От ' . $model->duration_min . ' мин.',//Todo yii::t
                    ],
                    [
                            'attribute' => 'complexity',
                            'label' => Yii::t('app','Сложность'),
                            'format' => 'raw',
                            'value' => $model->complexity . '/5 ⭐',
                    ],
//                    'year',
                    'created_at:datetime',
                    [
                            'attribute' => 'averageRating',
                            'label' => Yii::t('app','Средний рейтинг'),
                            'format' => 'raw',
                            'value' => $model->averageRating . '/5 ⭐ (' . $model->reviewsCount . ' отзывов)',//Todo yii::t
                    ],
            ],
    ]) ?>

    <!-- Предстоящие сессии -->
    <h2><?= Yii::t('app','Предстоящие сессии') ?></h2>
    <?php if ($upcomingSessions && count($upcomingSessions) > 0): ?>
        <div class="list-group">
            <?php foreach ($upcomingSessions as $session): ?>
                <div class="list-group-item">
                    <h5><?= Yii::$app->formatter->asDatetime($session->scheduled_at) ?></h5>
                    <p>Макс. участников: <?= $session->max_participants ?></p>
                    <p>Статус: <?= $session->statusLabel ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="text-muted"><?= Yii::t('app', 'Нет запланированных сессий.') ?></p>
    <?php endif; ?>

    <!-- Отзывы -->
    <h2><?= Yii::t('app','Отзывы') ?> (<?= $model->reviewsCount ?>)</h2>
    <?php if ($reviews && count($reviews) > 0): ?>
        <div class="reviews-list">
            <?php foreach ($reviews as $review): ?>
                <div class="panel panel-default" style="margin-bottom: 15px; padding: 15px; border: 1px solid #ddd; border-radius: 5px;">
                    <div class="panel-body">
                        <h4>
                            <?= str_repeat('⭐', $review->rating) ?>
                            <small class="text-muted">(<?= $review->rating ?>/5)</small>
                        </h4>
                        <?php if ($review->comment): ?>
                            <p><?= nl2br(Html::encode($review->comment)) ?></p>
                        <?php endif; ?>
                        <small class="text-muted">
                            От: Пользователь #<?= $review->user_id ?> |
                            <?= Yii::$app->formatter->asDatetime($review->created_at) ?>
                        </small>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="text-muted"><?= Yii::t('app', 'Нет отзывов.')?></p>
    <?php endif; ?>

    <!-- Форма отзыва -->
    <h2><?= Yii::t('app','Оставить отзыв') ?></h2>
    <div class="review-form">
        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($reviewForm, 'rating')->dropDownList(
                [
                    1 => '⭐',
                    2 => '⭐⭐',
                    3 => '⭐⭐⭐',
                    4 => '⭐⭐⭐⭐',
                    5 => '⭐⭐⭐⭐⭐'
                ],
                ['prompt' => Yii::t('app', 'Выберите рейтинг...')]
        ) ?>

        <?= $form->field($reviewForm, 'comment')->textarea(['rows' => 5]) ?>

        <?= $form->field($reviewForm, 'game_id')->hiddenInput()->label(false) ?>

        <div class="form-group">
            <?= Html::submitButton(
                    Yii::t('app', 'Отправить отзыв'),
                    ['class' => 'btn btn-success']
            ) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>

</div>