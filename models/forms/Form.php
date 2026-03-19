<?php

namespace app\models\forms;

interface Form
{
    /**
     * Аналог DTO для безопасного получения атрибутов.
     * ДЛя простоты не написала DTO.
     */
    public function getSafeAttributes(): array;
}
