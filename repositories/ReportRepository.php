<?php

namespace app\repositories;

use Yii;
use yii\caching\TagDependency;

class ReportRepository
{
    private const CACHE_TAG = 'report-top-authors';
    private const CACHE_DURATION = 86400;

    /**
     * @param int $year Год для отчета
     *
     * @return array Результаты отчета
     */
    public function getTopAuthorsByYear(int $year): array
    {
        $cacheKey = [
            self::CACHE_TAG,
            'year' => $year,
        ];

        $dependency = new TagDependency([
            'tags' => [self::CACHE_TAG],
        ]);

        $result = Yii::$app->cache->get($cacheKey);

        if ($result === false) {
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
            $result = $command->queryAll();

            Yii::$app->cache->set(
                $cacheKey,
                $result,
                self::CACHE_DURATION,
                $dependency
            );
        }

        return $result;
    }
}
