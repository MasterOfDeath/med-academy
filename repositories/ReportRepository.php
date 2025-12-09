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
            $query = (new \yii\db\Query())
                ->select([
                    'author_name' => 'a.full_name',
                    'book_count' => 'COUNT(b.id)',
                ])
                ->from('authors a')
                ->innerJoin('book_authors ba', 'a.id = ba.author_id')
                ->innerJoin('books b', 'ba.book_id = b.id')
                ->where(['b.year' => $year])
                ->groupBy(['a.id', 'a.full_name'])
                ->orderBy(['book_count' => SORT_DESC])
                ->limit(10);

            $result = $query->all();

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
