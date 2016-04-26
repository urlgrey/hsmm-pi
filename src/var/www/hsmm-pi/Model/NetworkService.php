<?php
class NetworkService extends AppModel {

	public $validate = array(
		'name' => array('required' => array('rule' => array('notBlank'), 'message' => 'Service name is required')),
		'service_protocol_name' => array('required' => array('rule' => array('notBlank'), 'message' => 'Service protocol is required')),
		'host' => array('required' => array('rule' => '/^((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/i', 'message' => 'A valid IP address is required')),
		'port' => array('required' => array('rule' => array('notBlank'), 'message' => 'Port is required')),
		'local_port' => array('required' => array('rule' => array('notBlank'), 'message' => 'Local port is required')),
		'protocol' => array('required' => array('rule' => array('notBlank'), 'message' => 'Protocol is required')),
	);

}

?>
