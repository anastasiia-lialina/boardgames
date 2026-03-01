<?php

use app\models\forms\SignupForm;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

/** @var SignupForm $model */

$this->title = Yii::t('app', 'Signup');
?>
<div class="site-signup">
    <div class="row justify-content-center">
        <div class="col-lg-5">
            <h1 class="mb-4"><?= Html::encode($this->title) ?></h1>
            <p><?= Yii::t('app', 'Please fill out the following fields to signup:') ?></p>

            <?php $form = ActiveForm::begin(['id' => 'form-signup']); ?>

            <?= $form->field($model, 'username')->textInput(['autofocus' => true, 'placeholder' => $model->getAttributeLabel('username')]) ?>

            <?= $form->field($model, 'email')->input('email', ['placeholder' => 'example@mail.com']) ?>

            <?= $form->field($model, 'password')->passwordInput(['placeholder' => '******']) ?>

            <div class="form-group mt-3">
                <?= Html::submitButton(Yii::t('app', 'Signup'), ['class' => 'btn btn-primary w-100', 'name' => 'signup-button']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
