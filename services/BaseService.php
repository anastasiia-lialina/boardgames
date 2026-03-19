<?php

namespace app\services;

use app\models\forms\Form;
use yii\base\Model;

abstract class BaseService
{
    /**
     * @throws \Exception
     */
    public function findModel(string $modelClass, int $id)
    {
        /** @var Model $modelClass */
        $model = $modelClass::findOne($id);

        if (null === $model) {
            throw new \Exception('Model not found');
        }

        return $model;
    }

    protected function load(object $model, Form $form): void
    {
        $attributes = $form->getSafeAttributes();

        foreach ($attributes as $attribute => $value) {
            if ($model->hasAttribute($attribute)) {
                $model->{$attribute} = $value;
            }
        }
    }
}
