<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%network}}`.
 */
class m231030_144635_create_network_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%network}}', [
            'id' => $this->primaryKey()->notNull(),
            'network' => $this->string(255)->null(),
            'mask' => $this->string(255)->null(),
            'mask_size' => $this->integer(10)->null(),
            'country_id' => $this->integer(10)->null(),
            'ip' => $this->string(255)->null(),
        ]);
        $this->createTable('{{%country}}', [
            'id' => $this->primaryKey()->notNull(),
            'iso_code' => $this->string(255)->null(),

        ]);
        $this->createIndex('mask_size_ip', 'network', 'mask_size, ip');

        $this->createIndex(
            'idx-network-country_id',
            'network',
            'country_id'
        );
        $this->addForeignKey(
            'fk-network-country_id',
            'network',
            'country_id',
            'country',
            'id',
            'RESTRICT'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%network}}');
        $this->dropTable('{{%country}}');
        return true;
    }
}
