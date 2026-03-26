<?php

namespace app\controllers;

use app\models\game\GameSession;
use app\services\GameSessionService;
use app\services\ReportService;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;

/**
 * Контроллер для отображения отчетов модератору
 */
class ReportController extends Controller
{
    public function __construct($id, $module, private ReportService $reportService, private GameSessionService $gameSessionService, $config = [])
    {
        parent::__construct($id, $module, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['admin', 'moderator'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Главная страница отчетов
     */
    public function actionIndex(): string
    {
        return $this->render('index');
    }

    /**
     * Общий метод для обработки AJAX запросов к сервисам отчетов
     */
    private function handleAjaxRequest(callable $serviceMethod, string $errorMessage): array
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            \Yii::info('handleAjaxRequest: calling service method', 'report');
            $data = $serviceMethod();
            \Yii::info('handleAjaxRequest: service method returned data', 'report');

            $result = [
                'success' => true,
                'data' => $data,
            ];

            \Yii::info('handleAjaxRequest: returning success response: ' . json_encode($result), 'report');
            return $result;
        } catch (\Exception $e) {
            \Yii::error('handleAjaxRequest: exception - ' . $e->getMessage(), 'report');

            $result = [
                'success' => false,
                'message' => $errorMessage . ': ' . $e->getMessage(),
            ];

            \Yii::info('handleAjaxRequest: returning error response: ' . json_encode($result), 'report');
            return $result;
        }
    }

    /**
     * AJAX endpoint для получения общей статистики
     */
    public function actionGamesStats(): array
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $stats = $this->reportService->getGamesStats();

        if (!$stats) {
            return ['success' => false];
        }

        $jsonFields = [
            'reviews_distribution',
            'popular_games_sessions',
            'popular_games_subscriptions',
            'recent_activity',
        ];

        foreach ($jsonFields as $field) {
            if (isset($stats[$field]) && is_string($stats[$field])) {
                $stats[$field] = json_decode($stats[$field], true);
            }
            if (empty($stats[$field])) {
                $stats[$field] = [];
            }
        }

        return [
            'success' => true,
            'data' => $stats,
            'labels' => [
                'statuses' => GameSession::getStatusLabels(),
            ],
        ];
    }

    /**
     * AJAX endpoint для обновления данных
     */
    public function actionRefresh(): array
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $this->reportService->refresh();

            return [
                'success' => true,
            ];
        } catch (\Exception $e) {
            \Yii::error($e->getMessage(), 'report');

            $this->reportService->refresh(false);

            return [
                'success' => true,
            ];
        }
    }

    /**
     * Экспорт статистики в CSV
     */
    public function actionExport(): Response
    {
        $filename = 'games_report_' . date('Y-m-d_H-i') . '.csv';
        $csvData = $this->reportService->getExportData();

        return \Yii::$app->response->sendContentAsFile($csvData, $filename, [
            'mimeType' => 'text/plain',
            'inline' => true,
        ]);
    }
}
