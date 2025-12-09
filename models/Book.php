<?php

namespace app\models;

use Yii;

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
            'isbn' => 'Isbn',
            'cover_image' => 'Cover Image',
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
}
