<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%subscriptions}}`.
 */
class m251209_115933_create_subscriptions_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%subscriptions}}', [
            'id' => $this->primaryKey(),
            'author_id' => $this->integer()->notNull(),
            'phone' => $this->string()->notNull(),
        ]);

        $this->addForeignKey('fk-subscriptions-author_id', '{{%subscriptions}}', 'author_id', '{{%authors}}', 'id', 'CASCADE', 'CASCADE');
        
        $this->createIndex('idx-subscriptions-author_id', '{{%subscriptions}}', 'author_id');
        $this->createIndex('idx-subscriptions-phone', '{{%subscriptions}}', 'phone');
        $this->createIndex('idx-subscriptions-author_phone', '{{%subscriptions}}', ['author_id', 'phone']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%subscriptions}}');
    }
}
