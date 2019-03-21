<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateTabelContent extends Migrator
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
        $this->table('content')
            ->addColumn('title', 'string')
            ->addColumn('image', 'text')
            ->addColumn('article', 'text')
            ->addTimestamps()
            ->create();
    }

    public function down(){
        if($this->hasTable('content')) {
            $this->dropTable('content');
        }
    }
}
