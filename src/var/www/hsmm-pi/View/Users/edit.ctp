<!-- File: /app/View/Users/index.ctp -->
<div class="page-header">
  <h1>Change Password</h1>
</div>

<?php
echo $this->Form->create('User', array(
  'inputDefaults' => array(
    'div' => 'form-group',
    'label' => array('class' => 'col col-md-3 control-label'),
    'class' => 'form-control'),
  'url' => array('controller' => 'users', 'action' => 'edit')));
echo $this->Form->input('current_password', array('label' => __('Current Password'), 'type' => 'password'));
echo $this->Form->input('password', array('label' => __('New Password'), 'type' => 'password'));
echo $this->Form->input('password_confirmation', array('label' => __('New Password (again)'), 'type' => 'password'));

echo $this->Form->submit(__('Save'), array('name' => 'submit', 'div' => false, 'class' => 'btn btn-primary'));
echo $this->Form->end();
?>
