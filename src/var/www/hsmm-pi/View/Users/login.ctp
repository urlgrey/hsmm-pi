<div class="col-sm-4">
  <?php
    echo $this->Session->flash();
  ?>
  <?php
    echo $this->Form->create('User', array(
      'inputDefaults' => array(
        'div' => 'form-group',
        'label' => array(
          'class' => 'control-label'
        ),
        'class' => 'form-control'
      )
    ));
  ?>
  <?php
    echo __('Please enter your username and password');
  ?>
  <?php
    echo $this->Form->input('username');
    echo $this->Form->input('password');
  ?>
  <?php
    echo $this->Form->submit(__('Login'), array('name' => 'submit', 'div' => false, 'class' => 'btn btn-primary'));
    echo $this->Form->end();
  ?>
</div>
