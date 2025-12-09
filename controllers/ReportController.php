<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\Author;
use yii\data\ArrayDataProvider;
use app\repositories\ReportRepository;

class ReportController extends Controller
{
    public function __construct(
        $id,
        $module,
        private readonly ReportRepository $reportRepository,
        $config = [],
    ) {
        parent::__construct($id, $module, $config);
    }

    /**
     * @param int $year Год для отчета (по умолчанию текущий год)
     * @return string
     */
   public function actionTopAuthors(?int $year = null)
   {
       if ($year === null) {
           $year = (int) date('Y'); // По умолчанию текущий год
       }

       $results = $this->reportRepository->getTopAuthorsByYear($year);

       $dataProvider = new ArrayDataProvider([
           'allModels' => $results,
           'pagination' => [
               'pageSize' => 10,
           ],
       ]);

       return $this->render('top-authors', [
           'dataProvider' => $dataProvider,
           'year' => $year,
       ]);
   }
}
