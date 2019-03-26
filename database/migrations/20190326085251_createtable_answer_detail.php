<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreatetableAnswerDetail extends Migrator
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
    public function up(){
        $this->down();
        $this->table('answer_detail')
            ->addColumn('user_id', 'integer')
            ->addColumn('questionnaire_id', 'integer')
            ->addColumn('topic_id', 'integer')
            ->addColumn('query', 'string', ['limit' => 20])
            ->addTimestamps()
            ->create();
    }
    
    public function down(){
        if($this->hasTable('answer_detail')) {
            $this->dropTable('answer_detail');
        }
    }
}
