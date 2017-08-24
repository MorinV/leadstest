<?php

class m170824_073849_create_tables extends CDbMigration
{
	public function up()
	{
		$this->createTable('user', array(
            'id' => 'pk',
            'email' => 'string NOT NULL',
            'name' => 'string NOT NULL',
            'AccesKey' => 'string NOT NULL',
            'ApiKey' => 'string NOT NULL',
        ));
		$this->createTable('balance', array(
            'id' => 'pk',
            'user' => 'integer NOT NULL',
			'balance' => 'integer NOT NULL',
        ));
		$this->createTable('transactionLog', array(
            'id' => 'pk',
            'user' => 'integer NOT NULL',
            'current_balance' => 'integer',
            'new_balance' => 'integer',
            'transaction' => 'integer',
            'datetime' => 'timestamp DEFAULT CURRENT_TIMESTAMP',
            'comment' => 'string',
        ));
		$this->addForeignKey('BalanceUser', 'balance', 'user', 'user', 'id', 'CASCADE', 'CASCADE');
		$this->addForeignKey('TransactionLogUser', 'transactionLog', 'user', 'user', 'id', 'CASCADE', 'CASCADE');
	}

	public function down()
	{
		$this->dropForeignKey('BalanceUser', 'balance');
		$this->dropForeignKey('TransactionLogUser', 'transactionLog');
		$this->dropTable('user');
		$this->dropTable('transactionLog');
		$this->dropTable('balance');		
	}

	/*
	// Use safeUp/safeDown to do migration with transaction
	public function safeUp()
	{
	}

	public function safeDown()
	{
	}
	*/
}