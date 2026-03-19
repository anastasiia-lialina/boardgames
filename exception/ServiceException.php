<?php

namespace app\exception;

use yii\base\Exception;
use yii\base\Model;

class ServiceException extends Exception
{
    public function __construct(Model $model, int $code = 0, ?\Throwable $previous = null)
    {
        $messages = [];
        foreach ($model->getErrors() as $attribute => $errorList) {
            $label = $model::instance()->getAttributeLabel($attribute);
            foreach ($errorList as $error) {
                $messages[] = \Yii::t('app', '{attribute}: {error}', [
                    'attribute' => $label,
                    'error' => $error,
                ]);
            }
        }

        \Yii::error($messages, 'serviceError');

        parent::__construct(implode('; ', $messages), $code, $previous);
    }
}
