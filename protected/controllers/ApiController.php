<?php

class ApiController extends Controller
{
	protected function renderJSON($data){
		header('Content-type: application/json');
		echo CJSON::encode($data);

		foreach (Yii::app()->log->routes as $route) {
			if($route instanceof CWebLogRoute) {
				$route->enabled = false; // disable any weblogroutes
			}
		}
		Yii::app()->end();
	}
	
	public function actionWithdraw(){
		$amount = Yii::app()->request->getQuery('amount');
		$ApiKey = Yii::app()->request->getQuery('ApiKey');
		$user=User::model()->findByAttributes(array('ApiKey'=>$ApiKey));
		if(!$user){
			$result[errcode]=1;
			$result[msg]="No such user";
			$result[balance]=0;
			$this->renderJSON($result);	
		}
		$balance=Balance::model()->findByAttributes(array('user'=>$user->id));
		$transaction=$balance->dbConnection->beginTransaction();
		try{
			if($amount>0){
				if($amount<$balance->balance){
					$transactionLog = new TransactionLog();
					$transactionLog->user=$user->id;
					$transactionLog->current_balance=$balance->balance;
					$transactionLog->transaction=$amount;
					$transactionLog->new_balance=$balance->balance - $amount;
					$transactionLog->comment="Снятие средств";
					if ($transactionLog->save()){
						$balance->balance-=$amount;
						if ($balance->save()){
							$transaction->commit();
							$result[errcode]=0;
							$result[msg]="Succes";
							$result[balance]=$balance->balance;
						}
						else{
							$transaction->rollback();
							$result[errcode]=5;
							$result[msg]="database error";
							$result[balance]=$balance->balance;
						}
					}
					else{
						$transaction->rollback();
						$result[errcode]=5;
						$result[msg]="database error";
						$result[balance]=$balance->balance;
					}
				}
				else{
					$transaction->rollback();
					$result[errcode]=3;
					$result[msg]="Not enough money";
					$result[balance]=$balance->balance;
				}
			}
			else{
				$transaction->rollback();
				$result[errcode]=2;
				$result[msg]="Negative amount";
				$result[balance]=$balance->balance;
			}
		}
		catch(Exception $e){
			$transaction->rollback();
			$result[errcode]=4;
			$result[msg]=$e;
			$result[balance]=$balance->balance;
		}
		$this->renderJSON($result);	
	}
}