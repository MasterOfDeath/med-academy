<?php

namespace app\models;

use Yii;
use yii\web\UploadedFile;

/**
 * This is the model class for table "books".
 *
 * @property int          $id
 * @property string       $title
 * @property int|null     $year
 * @property string|null  $description
 * @property string|null  $isbn
 * @property string|null  $cover_image
 * @property Author[]     $authors
 * @property BookAuthor[] $bookAuthors
 */
class Book extends \yii\db\ActiveRecord
{
    private const REPORT_CACHE_TAG = 'report-top-authors';

    /**
     * @var UploadedFile
     */
    public $cover_image_file;
    public $author_ids = [];
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'books';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['year', 'description', 'isbn', 'cover_image'], 'default', 'value' => null],
            [['title'], 'required'],
            [['year'], 'integer'],
            [['description'], 'string'],
            [['title', 'isbn', 'cover_image'], 'string', 'max' => 255],
            [['author_ids'], 'safe'],
            [['cover_image_file'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg, gif', 'maxSize' => 1024 * 1024], // Максимальный размер 1MB
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'year' => 'Year',
            'description' => 'Description',
            'isbn' => 'ISBN',
            'cover_image' => 'Cover Image',
            'author_ids' => 'Authors',
        ];
    }

    /**
     * Gets query for [[Authors]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAuthors()
    {
        return $this->hasMany(Author::class, ['id' => 'author_id'])->viaTable('book_authors', ['book_id' => 'id']);
    }

    /**
     * Gets query for [[BookAuthors]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBookAuthors()
    {
        return $this->hasMany(BookAuthor::class, ['book_id' => 'id']);
    }
    
    /**
     * @param array $authorIds IDs авторов
     */
    public function linkAuthors($authorIds)
    {
        BookAuthor::deleteAll(['book_id' => $this->id]);
        
        foreach ($authorIds as $authorId) {
            $bookAuthor = new BookAuthor();
            $bookAuthor->book_id = $this->id;
            $bookAuthor->author_id = $authorId;
            $bookAuthor->save();
        }
    }
    
    /**
     * @return array
     */
    public function getAuthorIds()
    {
        return $this->getAuthors()->select('id')->column();
    }
    
    /**
     * Загружает файл обложки
     */
    public function uploadCoverImage()
    {
        if ($this->cover_image_file !== null) {
            $filename = 'book_' . $this->id . '_' . time() . '.' . $this->cover_image_file->extension;
            
            $uploadDir = Yii::getAlias('@webroot/uploads');
            if (! file_exists($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }
            
            $this->cover_image_file->saveAs($uploadDir . '/' . $filename);
            
            if ($this->cover_image && file_exists($uploadDir . '/' . $this->cover_image)) {
                unlink($uploadDir . '/' . $this->cover_image);
            }
            
            $this->cover_image = $filename;
            
            return true;
        }
        
        return false;
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
