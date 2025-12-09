<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\Author;
use yii\data\ArrayDataProvider;

class ReportController extends Controller
{
    /**
     * Отчет: ТОП-10 авторов, выпустивших наибольшее количество книг за указанный год
     * @param int $year Год для отчета (по умолчанию текущий год)
     * @return string
     */
    public function actionTopAuthors($year = null)
    {
        if ($year === null) {
            $year = date('Y'); // По умолчанию текущий год
        }

        // SQL-запрос для получения ТОП-10 авторов по количеству книг за указанный год
        $sql = "
            SELECT 
                a.full_name as author_name,
                COUNT(b.id) as book_count
            FROM authors a
            JOIN book_authors ba ON a.id = ba.author_id
            JOIN books b ON ba.book_id = b.id
            WHERE b.year = :year
            GROUP BY a.id, a.full_name
            ORDER BY book_count DESC
            LIMIT 10
        ";

        $command = Yii::$app->db->createCommand($sql, [':year' => $year]);
        $results = $command->queryAll();

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