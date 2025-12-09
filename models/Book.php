<?php

namespace app\models;

use Yii;
use yii\web\UploadedFile;

/**
 * This is the model class for table "books".
 *
 * @property int $id
 * @property string $title
 * @property int|null $year
 * @property string|null $description
 * @property string|null $isbn
 * @property string|null $cover_image
 *
 * @property Author[] $authors
 * @property BookAuthor[] $bookAuthors
 */
class Book extends \yii\db\ActiveRecord
{
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
            [['cover_image_file'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg, gif', 'maxSize' => 1024*1024], // Максимальный размер 1MB
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
     * Сохраняет связь между книгой и авторами
     * @param array $authorIds IDs авторов
     */
    public function linkAuthors($authorIds)
    {
        // Удаляем старые связи
        BookAuthor::deleteAll(['book_id' => $this->id]);
        
        // Создаем новые связи
        foreach ($authorIds as $authorId) {
            $bookAuthor = new BookAuthor();
            $bookAuthor->book_id = $this->id;
            $bookAuthor->author_id = $authorId;
            $bookAuthor->save();
        }
    }
    
    /**
     * Возвращает IDs всех авторов книги
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
            // Генерируем уникальное имя файла
            $filename = 'book_' . $this->id . '_' . time() . '.' . $this->cover_image_file->extension;
            
            // Создаем директорию, если она не существует
            $uploadDir = Yii::getAlias('@webroot/uploads');
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }
            
            // Сохраняем файл
            $this->cover_image_file->saveAs($uploadDir . '/' . $filename);
            
            // Удаляем старый файл, если он существует
            if ($this->cover_image && file_exists($uploadDir . '/' . $this->cover_image)) {
                unlink($uploadDir . '/' . $this->cover_image);
            }
            
            // Обновляем поле cover_image в модели
            $this->cover_image = $filename;
            
            return true;
        }
        
        return false;
    }
}
