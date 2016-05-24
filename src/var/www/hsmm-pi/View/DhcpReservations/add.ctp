<!-- File: /app/view/DHCPReservations/add.ctp -->
<div class="page-header">
  <h1>Add DHCP Reservation</h1>
</div>

<?php
echo $this->Form->create('DHCPReservation', array(
  'inputDefaults' => array(
    'div' => 'form-group',
    'label' => array('class' => 'col col-md-3 control-label'),
    'class' => 'form-control'),
));
echo $this->Form->input('hostname');
echo $this->Form->input('ip_address');
echo $this->Form->input('mac_address');

echo $this->Html->link(__('Cancel'),
	array(
		'controller' => 'dhcp_reservations',
		'action' => 'index'),
	array('class' => 'btn btn-default')
);
echo "&nbsp;&nbsp;";
echo $this->Form->submit(__('Save'), array('name' => 'submit', 'div' => false, 'class' => 'btn btn-primary'));
echo $this->Form->end();
?>
