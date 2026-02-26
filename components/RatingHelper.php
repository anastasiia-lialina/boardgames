<?php

namespace app\components;

use yii\helpers\Html;

/**
 * Класс для работы с рейтингом (отображение рейтинга в виде звёздочек)
 */
class RatingHelper
{
    public static function getStars(float|int $rating, int $max = 5): array
    {
        $rating = round($rating);

        $activeStars = str_repeat('★', $rating);
        $emptyStars = str_repeat('☆', $max - $rating);

        return [
            'active' => $activeStars,
            'empty' => $emptyStars,
        ];
    }

    /**
     * Рендерит звёздочки рейтинга
     * @param float|int $rating
     * @param int $max Максимальное количество звезд
     * @return string HTML-строка
     */
    public static function renderStars(float|int $rating, int $max = 5): string
    {
        $stars = self::getStars($rating, $max);

        return Html::tag('span', $stars['active'], ['class' => 'text-warning']) .
            Html::tag('span', $stars['empty'], ['class' => 'text-muted']);
    }

    /**
     * Рендерит только текст (для dropDownList или простых подписей)
     */
    public static function renderStarsUnstyled(float|int $rating, int $max = 5): string
    {
        $stars = self::getStars($rating, $max);
        return $stars['active'] . $stars['empty'];
    }

    /**
     * Генерирует массив для dropDownList: [1 => '★☆☆☆☆', 2 => '★★☆☆☆', ...]
     */
    public static function getRatingOptions(int $max = 5): array
    {
        $options = [];
        foreach (range(1, $max) as $i) {
            $options[$i] = self::renderStarsUnstyled($i, $max);
        }
        return $options;
    }
}
