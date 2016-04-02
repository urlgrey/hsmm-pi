<?php
// app/Controller/UsersController.php
class UsersController extends AppController {

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('logout');
	}

	public function login() {
		if ($this->request->is('post')) {

			if ($this->Auth->login()) {
				$this->Session->setFlash(__('Login successful, carry on'), 'default', array('class' => 'alert alert-success'));
				$this->redirect($this->Auth->redirectUrl());
			} else {
				$this->Session->setFlash(__('Invalid username or password, try again'), 'default', array('class' => 'alert alert-error'));
			}
		} else {
			$this->Session->setFlash(__('You must login to access this part of the site'), 'default', array('class' => 'alert alert-info'));
		}
	}

	public function logout() {
		$this->redirect($this->Auth->logout());
	}

	public function edit() {
		if ($this->request->is('post')) {
			$admin_user = $this->User->find('first');

			if ($admin_user == null) {
				$this->Session->setFlash(__('Unable to find user account, this should never happen'), 'default', array('class' => 'alert alert-error'));
			} else if (strcmp($admin_user['User']['password'], Security::hash($this->request->data['User']['current_password'], null, true)) != 0) {
				$this->Session->setFlash(__('The current password was incorrect'), 'default', array('class' => 'alert alert-error'));
			} else if (strcmp($this->request->data['User']['password'], $this->request->data['User']['password_confirmation']) != 0) {
				$this->Session->setFlash(__('New passwords did not match'), 'default', array('class' => 'alert alert-error'));
			} else {
				$admin_user['User']['password'] = $this->request->data['User']['password'];

				if ($this->User->save($admin_user)) {
					$this->Session->setFlash('Password changed successfully.', 'default', array('class' => 'alert alert-success'));
				} else {
					$this->Session->setFlash('Unable to update your settings, please review any validation errors.', 'default', array('class' => 'alert alert-error'));
				}
			}
		}
	}
}
?>
