<div class="users form">
<?php
echo $this->Session->flash();
?>
<?php
echo $this->Form->create('User');
?>

<fieldset>
<legend><?php
echo __('Please enter your username and password');
?></legend>
<?php
echo $this->Form->input('username');
echo $this->Form->input('password');
?>
</fieldset>
<?php
echo $this->Form->submit(__('Login'), array('name' => 'submit', 'div' => false, 'class' => 'btn btn-primary'));
echo $this->Form->end();
?>
</div>
