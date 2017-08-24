<?php

class SiteController extends Controller
{
	/**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array(
			// captcha action renders the CAPTCHA image displayed on the contact page
			'captcha'=>array(
				'class'=>'CCaptchaAction',
				'backColor'=>0xFFFFFF,
			),
			// page action renders "static" pages stored under 'protected/views/site/pages'
			// They can be accessed via: index.php?r=site/page&view=FileName
			'page'=>array(
				'class'=>'CViewAction',
			),
		);
	}

	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionIndex()
	{ 
		// renders the view file 'protected/views/site/index.php'
		// using the default layout 'protected/views/layouts/main.php'
		$this->render('index');
	}

	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError()
	{
		if($error=Yii::app()->errorHandler->error)
		{
			if(Yii::app()->request->isAjaxRequest)
				echo $error['message'];
			else
				$this->render('error', array ('error'=>$error));
		}
	}

	/**
	 * Displays the contact page
	 */
	public function actionContact()
	{
		$model=new ContactForm;
		if(isset($_POST['ContactForm']))
		{
			$model->attributes=$_POST['ContactForm'];
			if($model->validate())
			{
				$name='=?UTF-8?B?'.base64_encode($model->name).'?=';
				$subject='=?UTF-8?B?'.base64_encode($model->subject).'?=';
				$headers="From: $name <{$model->email}>\r\n".
					"Reply-To: {$model->email}\r\n".
					"MIME-Version: 1.0\r\n".
					"Content-Type: text/plain; charset=UTF-8";

				mail(Yii::app()->params['adminEmail'],$subject,$model->body,$headers);
				Yii::app()->user->setFlash('contact','Thank you for contacting us. We will respond to you as soon as possible.');
				$this->refresh();
			}
		}
		$this->render('contact',array('model'=>$model));
	}

	/**
	 * Displays the login page
	 */
	public function actionLogin()
	{
		/*
		$identity=new UserIdentity('', '');
		if($identity->authenticate())
			Yii::app()->user->login($identity);
		else
			echo $identity->errorMessage;
		*/
		$model=new LoginForm;

		if (!Yii::app()->user->isGuest){
			$this->redirect(Yii::app()->homeUrl);
		}
		if(Yii::app()->request->getPost('LoginForm')){
			$post = Yii::app()->request->getPost('LoginForm');
			$email = $post['email'];
			$user=User::model()->model()->findByAttributes(array('email'=>$email));
			if($user){
				$user->getNewAccesKey();
				$user->save();
				$headers="From: info@leadstest.com\r\nReply-To: noreply";
				mail($email, "AuthMessage", Yii::app()->request->baseUrl."/index.php/site/auth?email=".$email."&AccesKey=".$user->AccesKey, $headers);
				$this->redirect(Yii::app()->homeUrl);
			}
			else{
				$headers="From: info@leadstest.com\r\nReply-To: noreply";
				mail($email, "RegistrationMessage", Yii::app()->request->baseUrl."/index.php/site/auth?email=".$email."&AccesKey=".uniqid('', true), $headers);
				$this->redirect(Yii::app()->homeUrl);
			}
		}
		$this->render('login',array('model'=>$model));
	}
	
	public function actionAuth(){
		if (!Yii::app()->user->isGuest){
			$this->redirect(Yii::app()->homeUrl);
		}
		$email = Yii::app()->request->getQuery('email');
		$AccesKey = Yii::app()->request->getQuery('AccesKey');
		$user=User::model()->findByAttributes(array('email'=>$email));
		if($user){
			$identity=new UserIdentity($email, $AccesKey);
			if($identity->authenticate()){
				//Yii::app()->user->logout();
				Yii::app()->user->login($identity);
				$user->getNewAccesKey();
				$user->save();
				$this->redirect('account');
			}
			else
				echo $identity->errorMessage;
		}
		else {
			$user = new User();
			$user->email=$email;
			$user->name=$email;
			$user->getNewAccesKey();
			$user->getNewApiKey();
			if($user->save()){
				$transactionLog = new TransactionLog();
				$transactionLog->user=$user->id;
				$transactionLog->current_balance=0;
				$transactionLog->transaction=1000;
				$transactionLog->new_balance=1000;
				$transactionLog->comment="Зачисление стартового бонуса";
				$transactionLog->save();
				$balance = new Balance();
				$balance->user=$user->id;
				$balance->balance=1000;
				$balance->save();
				$identity=new UserIdentity($user->email, $user->AccesKey);
				if($identity->authenticate()){
					Yii::app()->user->login($identity);
					$user->getNewAccesKey();
					$user->save();
					$this->redirect('account');
				}
			}
			else
				echo $identity->errorMessage;
		}
	}
	
	public function actionAccount(){
		if (Yii::app()->user->isGuest){
			$this->redirect(Yii::app()->homeUrl);
		}
		$user=User::model()->findByPk(Yii::app()->user->id);
		
		if(Yii::app()->request->getPost('User')){
			$post = Yii::app()->request->getPost('User');
			$user->name=$post['name'];
			$user->save();
		}
		
		$this->render('account',array('user'=>$user));
	}

	/**
	 * Logs out the current user and redirect to homepage.
	 */
	public function actionLogout()
	{
		Yii::app()->user->logout();
		$this->redirect(Yii::app()->homeUrl);
	}
}