<?php
class NetworkService extends AppModel {

  public $validate = array(
			   'name' => array('required' => array('rule' => array('notEmpty'), 'message' => 'Service name is required')),
			   'service_protocol_name' => array('required' => array('rule' => array('notEmpty'), 'message' => 'Service protocol is required')),
			   'host' => array('required' => array('rule' => '/^((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/i', 'message' => 'A valid IP address is required')),
			   'port' => array('required' => array('rule' => array('notEmpty'), 'message' => 'Port is required')),
			   'forwarding_port' => array('required' => array('rule' => array('notEmpty'), 'message' => 'Forwarding port is required')),
			   'protocol' => array('required' => array('rule' => array('notEmpty'), 'message' => 'Protocol is required')),
			   );


}

?>
