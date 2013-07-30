<?php
class NetworkService extends AppModel {

  public $validate = array(
			   'name' => array('required' => array('rule' => array('notEmpty'), 'message' => 'Service name is required')),
			   'service_protocol_name' => array('required' => array('rule' => array('notEmpty'), 'message' => 'Service protocol is required')),
			   'hostname' => array('required' => array('rule' => array('notEmpty'), 'message' => 'Hostname is required')),
			   'port' => array('required' => array('rule' => array('notEmpty'), 'message' => 'Port is required')),
			   'protocol' => array('required' => array('rule' => array('notEmpty'), 'message' => 'Protocol is required')),
			   );


}

?>
