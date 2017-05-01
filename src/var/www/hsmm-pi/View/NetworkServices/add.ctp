<!-- File: /app/view/NetworkServices/add.ctp -->
<div class="page-header">
  <h1>Add Network Service</h1>
</div>

<div class="row">
  <div class="col-sm-8 col-md-5">
    <?php
    echo $this->Form->create('NetworkService', array(
      'inputDefaults' => array(
        'div' => 'form-group',
        'class' => 'form-control')
      )
    );
    echo $this->Form->input('name');
    echo $this->Form->input('service_protocol_name');
    echo $this->Form->input('host');
    echo $this->Form->input('port');
    echo $this->Form->input('path');
    echo $this->Form->input('protocol', array('options' => array('tcp' => 'TCP', 'udp' => 'UDP')));
    echo $this->Form->input('local_port');

    echo $this->Html->link(__('Cancel'),
      array(
        'controller' => 'network_services',
        'action' => 'index'),
      array('class' => 'btn btn-default')
    );
    echo "&nbsp;&nbsp;";
    echo $this->Form->submit(__('Save'), array('name' => 'submit', 'div' => false, 'class' => 'btn btn-primary'));
    echo $this->Form->end();
    ?>
  </div>
</div>
