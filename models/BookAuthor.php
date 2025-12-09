<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "book_authors".
 *
 * @property int $book_id
 * @property int $author_id
 *
 * @property Authors $author
 * @property Books $book
 */
class BookAuthor extends \yii\db\ActiveRecord
{
    private const REPORT_CACHE_TAG = 'report-top-authors';


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'book_authors';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['book_id', 'author_id'], 'required'],
            [['book_id', 'author_id'], 'integer'],
            [['book_id', 'author_id'], 'unique', 'targetAttribute' => ['book_id', 'author_id']],
            [['author_id'], 'exist', 'skipOnError' => true, 'targetClass' => Author::class, 'targetAttribute' => ['author_id' => 'id']],
            [['book_id'], 'exist', 'skipOnError' => true, 'targetClass' => Book::class, 'targetAttribute' => ['book_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'book_id' => 'Book ID',
            'author_id' => 'Author ID',
        ];
    }

    /**
     * Gets query for [[Author]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAuthor()
    {
        return $this->hasOne(Author::class, ['id' => 'author_id']);
    }

    /**
     * Gets query for [[Book]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBook()
    {
        return $this->hasOne(Book::class, ['id' => 'book_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        
        $this->invalidateReportCache();
    }
    
    /**
     * {@inheritdoc}
     */
    public function afterDelete()
    {
        parent::afterDelete();
        
        $this->invalidateReportCache();
    }

    private function invalidateReportCache(): void
    {
        if (isset(\Yii::$app)) {
            \yii\caching\TagDependency::invalidate(\Yii::$app->cache, self::REPORT_CACHE_TAG);
        }
    }
}
