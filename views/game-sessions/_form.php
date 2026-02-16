<?php

use app\models\game\Game;
use kartik\datetime\DateTimePicker;
use yii\bootstrap5\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

// Рекомендую использовать bootstrap5

/* @var $this yii\web\View */
/* @var $model \app\models\game\GameSession */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="game-session-form">

    <?php $form = ActiveForm::begin([
            'fieldConfig' => ['errorOptions' => ['class' => 'invalid-feedback d-block']],
    ]); ?>

    <?= $form->field($model, 'game_id')->dropDownList(
            ArrayHelper::map(Game::find()->orderBy('title')->all(), 'id', 'title'),
            ['prompt' => Yii::t('app', 'Select game...')]
    ) ?>

    <?= $form->field($model, 'scheduled_at')->widget(DateTimePicker::class, [
            'options' => [
                    'placeholder' => Yii::t('app', 'Выберите дату и время...'),
                    'autocomplete' => 'off',
                    'value' => $model->scheduled_at
                            ? Yii::$app->formatter->asDatetime($model->scheduled_at, 'php:d.m.Y H:i')
                            : '',
            ],
            'pluginOptions' => [
                    'autoclose' => true,
                    'todayHighlight' => true,
                    'format' => 'dd.mm.yyyy hh:ii',
                    'startDate' => $model->isNewRecord ? date('dd.mm.yyyy hh:ii') : null, // Только будущие даты при создании
                    'todayBtn' => true,
                    'minuteStep' => 5,
            ],
            'pluginEvents' => [
                    'changeDate' => 'function(e) { $(this).trigger("blur"); }', // Триггер валидации при изменении
            ],
    ]) ?>

    <?= $form->field($model, 'max_participants')->textInput([
            'type' => 'number',
            'min' => 2,
            'max' => 20
    ]) ?>
    <?php if (!$model->isNewRecord):?>
        <?= $form->field($model, 'status')->dropDownList($model->getStatusLabels()) ?>
    <?php endif;?>

    <div class="form-group mt-3">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
