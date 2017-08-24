<?php
/* @var $this SiteController */
/* @var $model LoginForm */
/* @var $form CActiveForm  */

?>

<h1>Hello, <?php echo $user->name; ?></h1>
	<div class="row">
		<p>Your balance is <?php echo $user->balances->balance; ?></p>
	</div>
	<div class="row">
		<p>Your ApiKey is <?php echo $user->ApiKey; ?></p>
	</div>

<div class="form">
<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'login-form',
	'enableClientValidation'=>true,
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
	),
)); ?>


	<div class="row">
		<?php echo $form->textField($user,'name'); ?>
		<?php echo $form->error($user,'name'); ?>
		<?php echo CHtml::submitButton('Change name'); ?>
	</div>

	<div class="row buttons">
		
	</div>

<?php $this->endWidget(); ?>
</div><!-- form -->
