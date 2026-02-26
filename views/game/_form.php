<?php

use app\models\game\Game;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\game\Game $model */
/** @var yii\bootstrap5\ActiveForm $form */
?>

<div class="game-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'players_min')->input('number', ['min' => Game::MIN_PLAYERS, 'max' => Game::MAX_PLAYERS]) ?>

    <?= $form->field($model, 'players_max')->input('number', ['min' => Game::MIN_PLAYERS, 'max' => Game::MAX_PLAYERS]) ?>

    <?= $form->field($model, 'duration_min')->input('number', ['min' => Game::MIN_DURATION]) ?>

    <?= $form->field($model, 'complexity')->input('number', ['min' => Game::MIN_COMPLEXITY]) ?>

    <?= $form->field($model, 'year')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
