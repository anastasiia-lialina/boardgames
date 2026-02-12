<?php

use app\models\Games;
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Games $model */
/** @var yii\bootstrap5\ActiveForm $form */
?>

<div class="game-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'players_min')->input('number', ['min' => Games::MIN_PLAYERS, 'max' => Games::MAX_PLAYERS]) ?>

    <?= $form->field($model, 'players_max')->input('number', ['min' => Games::MIN_PLAYERS, 'max' => Games::MAX_PLAYERS]) ?>

    <?= $form->field($model, 'duration_min')->input('number', ['min' => Games::MIN_DURATION]) ?>

    <?= $form->field($model, 'complexity')->input('number', ['min' => Games::MIN_COMPLEXITY]) ?>

    <?= $form->field($model, 'year')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
