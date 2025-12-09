<?php

namespace app\services;

use app\factories\SendSmsJobFactory;
use app\models\Book;
use app\models\BookAuthor;
use Yii;
use yii\web\UploadedFile;

class BookService
{
    public function __construct(
        private SendSmsJobFactory $smsJobFactory
    ) {
    }

    /**
     * @param Book $model
     * @param UploadedFile|null $file
     * @param array $authorIds
     * @return bool
     */
    public function createBook(Book $model, ?UploadedFile $file, array $authorIds): bool
    {
        $model->cover_image_file = $file;

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($model->save()) {
                $this->handleCoverImage($model);
                
                if (!empty($authorIds)) {
                    $this->linkAuthors($model, $authorIds);
                }
                
                $job = $this->smsJobFactory->create([
                    'bookId' => $model->id,
                ]);
                Yii::$app->queue->push($job);
                
                $transaction->commit();
                return true;
            } else {
                $transaction->rollback();
                return false;
            }
        } catch (\Exception $e) {
            $transaction->rollback();
            throw $e;
        } catch (\Throwable $e) {
            $transaction->rollback();
            throw $e;
        }
    }

    /**
     * @param Book $model
     * @param UploadedFile|null $file
     * @param array $authorIds
     * @return bool
     */
    public function updateBook(Book $model, ?UploadedFile $file, array $authorIds): bool
    {
        $model->cover_image_file = $file;

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($model->save()) {
                $this->handleCoverImage($model);
                
                if (!empty($authorIds)) {
                    $this->linkAuthors($model, $authorIds);
                }
                
                $transaction->commit();
                return true;
            } else {
                $transaction->rollback();
                return false;
            }
        } catch (\Exception $e) {
            $transaction->rollback();
            throw $e;
        } catch (\Throwable $e) {
            $transaction->rollback();
            throw $e;
        }
    }

    /**
     * @param Book $model
     */
    private function handleCoverImage(Book $model): void
    {
        if ($model->cover_image_file !== null) {
            $filename = 'book_' . $model->id . '_' . time() . '.' . $model->cover_image_file->extension;
            
            $uploadDir = Yii::getAlias('@webroot/uploads');
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }
            
            $model->cover_image_file->saveAs($uploadDir . '/' . $filename);

            if ($model->cover_image && file_exists($uploadDir . '/' . $model->cover_image)) {
                unlink($uploadDir . '/' . $model->cover_image);
            }
            
            $model->cover_image = $filename;
            $model->save(false);
        }
    }

    /**
     * @param Book $model
     * @param array $authorIds
     * @throws \Exception
     */
    private function linkAuthors(Book $model, array $authorIds): void
    {
        $deleteResult = BookAuthor::deleteAll(['book_id' => $model->id]);
        if ($deleteResult === false) {
            throw new \Exception('Failed to delete existing author links for book ID: ' . $model->id);
        }
        
        foreach ($authorIds as $authorId) {
            $bookAuthor = new BookAuthor();
            $bookAuthor->book_id = $model->id;
            $bookAuthor->author_id = $authorId;
            if (!$bookAuthor->save()) {
                throw new \Exception('Failed to save author link: book_id=' . $model->id . ', author_id=' . $authorId);
            }
        }
    }
}
