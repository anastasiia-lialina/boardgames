<?php

namespace app\models\forms;

interface Form
{
    public function getSafeAttributes(): array;
}
