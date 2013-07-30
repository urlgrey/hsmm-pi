<!-- File: /app/view/ShellUserKeys/add.ctp -->
<div class="page-header">
  <h1>Add Shell User Key</h1>
</div>

<?php
echo $this->Form->create('ShellUserKey');
echo $this->Form->input('name');
echo $this->Form->input('key', array(
    'rows' => '5'
));

echo $this->Form->submit(__('Save'), array('name' => 'submit', 'div' => false, 'class' => 'btn btn-primary'));
echo $this->Form->end();
?>

