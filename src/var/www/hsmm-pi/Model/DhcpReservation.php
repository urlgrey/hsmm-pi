<?php
class DhcpReservation extends AppModel {

	public $validate = array(
		'hostname' => array('required' => array('rule' => array('notBlank'), 'message' => 'Host name is required')),
		'ip_address' => array('required' => array('rule' => '/^((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/i', 'message' => 'A valid IP address is required')),
		'mac_address' => array('required' => array('rule' => array('notBlank'), 'message' => 'Port is required')),
	);

}

?>
