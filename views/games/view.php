<?php
use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\bootstrap5\ActiveForm;
use yii\widgets\ListView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $model app\models\Games */
/* @var $reviewsDataProvider \app\models\SearchReviews */
/* @var $sessionsDataProvider \app\models\SearchGameSessions */
/* @var $reviewForm app\models\Reviews */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Games'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="game-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                        'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
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
                            'label' => Yii::t('app', 'Players'),
                            'value' => Yii::t('app', '{min}–{max} players', [
                                    'min' => $model->players_min,
                                    'max' => $model->players_max
                            ]),
                    ],
                    [
                            'attribute' => 'duration_min',
                            'label' => Yii::t('app', 'Duration'),
                            'value' => Yii::t('app', 'From {n} min.', ['n' => $model->duration_min]),
                    ],
                    [
                            'attribute' => 'complexity',
                            'value' => $model->complexity . '/5',
                    ],
                    'year',
                    'created_at:datetime',
                    [
                            'attribute' => 'averageRating',
                            'label' => Yii::t('app', 'Average Rating'),
                            'format' => 'raw',
                            'value' => $model->averageRating . '/5' .
                                    ' (' . Yii::t('app', '{n, plural, =0{No reviews} one{# review} few{# reviews} many{# reviews} other{# reviews}}', ['n' => $model->reviewsCount]) . ')',
                    ],
            ],
    ]) ?>


    <!-- Предстоящие сессии -->
    <h2><?= Yii::t('app', 'Upcoming Sessions')?></h2>
    <?php if ($sessionsDataProvider->totalCount > 0): ?>
        <?php Pjax::begin(['id' => 'sessions-pjax']); ?>

        <?= ListView::widget([
                'dataProvider' => $sessionsDataProvider,
                'itemView' => '_session_item',
                'layout' => "{items}\n{pager}",
                'options' => ['class' => 'list-view sessions-list'],
        ]) ?>

        <?php Pjax::end(); ?>
    <?php else: ?>
        <p class="text-muted"><?= Yii::t('app', 'No sessions scheduled.')?></p>
    <?php endif; ?>

    <!-- Отзывы -->
    <h2>Отзывы (<?= $model->reviewsCount ?>)</h2>
    <?php if ($reviewsDataProvider->totalCount > 0): ?>
        <?php Pjax::begin(['id' => 'reviews-pjax']); ?>

        <?= ListView::widget([
                'dataProvider' => $reviewsDataProvider,
                'itemView' => '_review_item',
                'layout' => "{summary}\n{items}\n{pager}",
                'options' => ['class' => 'list-view reviews-list'],
        ]) ?>

        <?php Pjax::end(); ?>
    <?php else: ?>
        <p class="text-muted">Нет отзывов.</p>
    <?php endif; ?>




    <?php if (!Yii::$app->user->isGuest): ?>
        <div class="review-form mt-4 bg-light p-3 rounded">
            <h2><?= Yii::t('app', 'Leave a Review') ?></h2>
            <?php $form = ActiveForm::begin([
                    'fieldConfig' => ['errorOptions' => ['class' => 'invalid-feedback d-block']],
            ]); ?>

            <?= $form->field($reviewForm, 'rating')->dropDownList([
                    1 => '⭐', 2 => '⭐⭐', 3 => '⭐⭐⭐', 4 => '⭐⭐⭐⭐', 5 => '⭐⭐⭐⭐⭐'
            ], ['prompt' => Yii::t('app', 'Choose rating...')]) ?>

            <?= $form->field($reviewForm, 'comment')->textarea(['rows' => 5]) ?>
            <?= $form->field($reviewForm, 'game_id')->hiddenInput()->label(false) ?>

            <div class="form-group">
                <?= Html::submitButton(Yii::t('app', 'Submit Review'), ['class' => 'btn btn-success']) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    <?php endif; ?>
</div>
