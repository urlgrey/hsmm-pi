<!-- File: /app/view/NetworkServices/add.ctp -->
<div class="page-header">
  <h1>Add Network Service</h1>
</div>

<?php
echo $this->Form->create('NetworkService');
echo $this->Form->input('name');
echo $this->Form->input('service_protocol_name');
echo $this->Form->input('host');
echo $this->Form->input('port');
echo $this->Form->input('protocol', array('options' => array('tcp' => 'TCP', 'udp' => 'UDP')));
echo $this->Form->input('forwarding_port');

echo $this->Html->link(__('Cancel'),
	array(
		'controller' => 'network_services',
		'action' => 'index'),
	array('class' => 'btn')
);
echo "&nbsp;&nbsp;";
echo $this->Form->submit(__('Save'), array('name' => 'submit', 'div' => false, 'class' => 'btn btn-primary'));
echo $this->Form->end();
?>

