<?php

namespace app\controllers;

use app\models\Book;
use app\factories\SendSmsJobFactory;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\UploadedFile;

/**
 * BookController implements the CRUD actions for Book model.
 */
class BookController extends Controller
{
    public function __construct(
        $id,
        $module,
        private SendSmsJobFactory $smsJobFactory,
        $config = [],
    ) {
        $this->smsJobFactory = $smsJobFactory;
        parent::__construct($id, $module, $config);
    }

    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
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
                            'actions' => ['create', 'update', 'delete'],
                            'roles' => ['@'],
                        ],
                    ],
                ],
                'verbs' => [
                    'class' => VerbFilter::class,
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Book models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Book::find(),
            /*
            'pagination' => [
                'pageSize' => 50
            ],
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ],
            */
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Book model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Book model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Book();

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                // Загружаем файл обложки
                $model->cover_image_file = UploadedFile::getInstance($model, 'cover_image_file');
                
                if ($model->validate()) {
                    if ($model->save()) {
                        // Сохраняем файл обложки
                        $model->uploadCoverImage();
                        
                        // Сохраняем связи с авторами
                        $authorIds = \Yii::$app->request->post('Book')['author_ids'];
                        if (!empty($authorIds)) {
                            $model->linkAuthors($authorIds);
                        }
                        
                        // Добавляем задачу в очередь на отправку SMS
                        $job = $this->smsJobFactory->create([
                            'bookId' => $model->id
                        ]);
                        \Yii::$app->queue->push($job);

                        return $this->redirect(['view', 'id' => $model->id]);
                    }
                }
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Book model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post())) {
            // Загружаем файл обложки
            $model->cover_image_file = UploadedFile::getInstance($model, 'cover_image_file');
            
            $oldAuthorIds = $model->getAuthorIds();
            if ($model->validate()) {
                if ($model->save()) {
                    // Сохраняем файл обложки
                    $model->uploadCoverImage();
                    
                    // Сохраняем связи с авторами
                    $authorIds = \Yii::$app->request->post('Book')['author_ids'];
                    if (!empty($authorIds)) {
                        $model->linkAuthors($authorIds);
                    }
                    
                    // Проверяем, изменились ли авторы, и если да, то отправляем уведомления
                    $newAuthorIds = $model->getAuthorIds();
                    $authorsChanged = array_diff($oldAuthorIds, $newAuthorIds) || array_diff($newAuthorIds, $oldAuthorIds);
                    
                    if ($authorsChanged) {
                        // Добавляем задачу в очередь на отправку SMS
                        $job = $this->smsJobFactory->create([
                            'bookId' => $model->id
                        ]);
                        \Yii::$app->queue->push($job);
                    }

                    return $this->redirect(['view', 'id' => $model->id]);
                }
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Book model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Book model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Book the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Book::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
