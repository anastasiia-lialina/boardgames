<?php

namespace app\controllers;

use app\models\search\ReviewSearch;
use app\services\ReviewService;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;

/**
 * AdminController for moderating reviews.
 */
class AdminController extends Controller
{
    public function __construct(
        $id,
        $module,
        private readonly ReviewService $reviewService,
        $config = []
    ) {
        parent::__construct($id, $module, $config);
    }

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'approve', 'reject'],
                        'permissions' => ['manageReviews'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Список отзывов на модерации.
     */
    public function actionIndex()
    {
        $searchModel = new ReviewSearch();
        $dataProvider = $searchModel->search(\Yii::$app->request->queryParams);

        $dataProvider->query->andWhere(['is_approved' => false]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Одобрить отзыв.
     *
     * @param mixed $id
     */
    public function actionApprove($id): Response
    {
        try {
            $this->reviewService->approveReview($id);
            \Yii::$app->session->setFlash('success', \Yii::t('app', 'Review approved!'));
        } catch (\Exception $e) {
            \Yii::$app->session->setFlash('error', $e->getMessage());
        }

        return $this->redirect(['index']);
    }

    /**
     * Отклонить отзыв (удалить).
     *
     * @param mixed $id
     */
    public function actionReject($id): Response
    {
        try {
            $this->reviewService->rejectReview($id);
            \Yii::$app->session->setFlash('success', \Yii::t('app', 'Review rejected!'));
        } catch (\Exception $e) {
            \Yii::$app->session->setFlash('error', $e->getMessage());
        }

        return $this->redirect(['index']);
    }
}
