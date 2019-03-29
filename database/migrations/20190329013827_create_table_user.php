<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateTableUser extends Migrator
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
        $this->table('user')
            ->addColumn('name', 'string', array('comment' => '姓名', 'limit' => 20))
            ->addColumn('phone', 'string', array('comment' => '手机号', 'limit' => 20))
            ->addColumn('openid', 'string', array('comment' => '微信openid', 'limit' => 40))
            ->addColumn('nickname', 'string', array('comment' => '微信昵称', 'limit' => 20))
            ->addColumn('avatar_url', 'string', array('comment' => '头像url', 'limit' => 200))
            ->addColumn('due_childbirth_date', 'datetime', array('comment' => '预产期'))
            ->addColumn('doctor_id', 'integer', array('comment' => '医生id'))
            ->addTimestamps()
            ->create();
    }

    public function down()
    {
        if ($this->hasTable('user')) {
            $this->dropTable('user');
        }
    }
}
