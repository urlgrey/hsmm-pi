<!-- File: /app/view/ShellUserKeys/edit.ctp -->
<div class="page-header">
  <h1>Edit Shell User Key</h1>
</div>

<?php
echo $this->Form->create('ShellUserKey');
echo $this->Form->input('name');
echo $this->Form->input('key', array(
	'rows' => '5',
));
echo $this->Form->input('id', array(
	'type' => 'hidden',
));

echo $this->Form->submit(__('Save'), array('name' => 'submit', 'div' => false, 'class' => 'btn btn-primary'));
echo $this->Form->end();
?>
