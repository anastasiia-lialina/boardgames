<?php

namespace app\services;

use app\models\forms\Form;

abstract class BaseService
{
    protected function load(object $model, Form $form): void
    {
        $attributes = $form->getSafeAttributes();

        foreach ($attributes as $attribute => $value) {
            if ($model->hasAttribute($attribute)) {
                $model->$attribute = $value;
            }
        }
    }
}
