<?php

// app/Model/User.php
class User extends AppModel {
	public $validate = array(
		'username' => array('required' => array('rule' => array('notEmpty'), 'message' => 'A username is required')),
		'current_password' => array('required' => array('rule' => array('notEmpty'), 'message' => 'Current password is required')),
		'password' => array('required' => array('rule' => array('notEmpty'), 'message' => 'A password is required'), array('rule' => array('between', 8, 20), 'message' => 'Password must be at least 8 characters long')),
		'password_confirmation' => array('required' => array('rule' => array('notEmpty'), 'message' => 'Confirmation password is required')),
		'role' => array('valid' => array('rule' => array('inList', array('admin', 'author')), 'message' => 'Please enter a valid role', 'allowEmpty' => false)));

	public function beforeSave($options = array()) {
		if (isset($this->data[$this->alias]['password'])) {
			$this->data[$this->alias]['password'] = AuthComponent::password($this->data[$this->alias]['password']);
		}
		return true;
	}
}

?>
