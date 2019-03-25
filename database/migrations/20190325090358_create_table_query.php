<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateTableQuery extends Migrator
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function up()
    {
        $this->down();
        $this->table('questionnaire_topic_option')
            ->addColumn('questionnaire_topic_id', 'integer')
            ->addColumn('query', 'string')
            ->addColumn('score', 'string', ['limit' => 5])
            ->create();
    }

    public function down()
    {
        if ($this->hasTable('questionnaire_topic_option')) {
            $this->dropTable('questionnaire_topic_option');
        }
    }
}
