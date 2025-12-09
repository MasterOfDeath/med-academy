<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%book_authors}}`.
 */
class m251209_115839_create_book_authors_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%book_authors}}', [
            'book_id' => $this->integer()->notNull(),
            'author_id' => $this->integer()->notNull(),
        ]);

        $this->addPrimaryKey('pk-book_authors', '{{%book_authors}}', ['book_id', 'author_id']);
        $this->addForeignKey('fk-book_authors-book_id', '{{%book_authors}}', 'book_id', '{{%books}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-book_authors-author_id', '{{%book_authors}}', 'author_id', '{{%authors}}', 'id', 'CASCADE', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%book_authors}}');
    }
}
