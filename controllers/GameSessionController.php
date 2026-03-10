<?php

namespace app\controllers;

use app\models\forms\GameSessionForm;
use app\models\game\GameSession;
use app\models\search\GameSessionSearch;
use app\services\GameSessionService;
use Throwable;
use Yii;
use yii\db\Exception;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * GameSessionController implements the CRUD actions for GameSession model.
 */
class GameSessionController extends Controller
{
    public function __construct(
        $id,
        $module,
        private readonly GameSessionService $gameSessionService,
        $config = []
    ) {
        parent::__construct($id, $module, $config);
    }

    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'view'],
                        'roles' => ['?', '@'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['create'],
                        'roles' => ['@'],
                        'permissions' => ['createSession'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['update', 'delete'],
                        'roles' => ['updateSession'],
                        'roleParams' => function ($rule) {
                            return ['model' => $this->gameSessionService->findModel(Yii::$app->request->get('id'))];
                        },
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all GameSession models.
     * @return mixed
     * @throws Throwable
     */
    public function actionIndex()
    {
        // Для тестирования обновляем статусы при открытии списка, на проде это должно происходить через крон
        if (YII_ENV === 'dev') {
            $this->gameSessionService->updateExpiredSessions();
        }

        $searchModel = new GameSessionSearch();

        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single GameSession model.
     * @param int $id ID
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id): mixed
    {
        return $this->render('view', [
            'model' => $this->gameSessionService->findModel($id),
        ]);
    }

    /**
     * Creates a new GameSession model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|Response
     * @throws Exception
     */
    public function actionCreate(): string|Response
    {
        $form = new GameSessionForm();

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            $session = $this->gameSessionService->createSession($form, Yii::$app->user->id);

            Yii::$app->session->setFlash('success', Yii::t('app', 'Game session created successfully!'));
            return $this->redirect(['view', 'id' => $session->id]);
        }
        return $this->render('create', [
            'model' => $form,
        ]);
    }

    /**
     * Updates an existing GameSession model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|Response
     * @throws NotFoundHttpException|Exception if the model cannot be found
     * @throws \yii\base\Exception
     */
    public function actionUpdate(int $id): string|Response
    {
        $session = $this->gameSessionService->findModel($id);
        $form = new GameSessionForm();
        $form->isNewRecord = false;
        $form->setAttributes($session->attributes);

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            $session = $this->gameSessionService->updateSession(
                $id,
                $form
            );

            if (!$session->hasErrors()) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'Game session updated successfully!'));
                return $this->redirect(['view', 'id' => $session->id]);
            }
        }

        return $this->render('update', [
            'model' => $form,
        ]);
    }

    /**
     * Deletes an existing GameSession model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->gameSessionService->deleteSession($id);

        return $this->redirect(['index']);
    }
}
