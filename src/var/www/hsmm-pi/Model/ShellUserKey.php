<?php
class ShellUserKey extends AppModel {

	public $validate = array(
		'key' => array('required' => array('rule' => array('notEmpty'), 'message' => 'An SSH key is required')),
		'name' => array('required' => array('rule' => array('notEmpty'), 'message' => 'A name is required')),
	);

}

?>
