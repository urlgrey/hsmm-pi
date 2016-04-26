<?php
// app/Controller/UsersController.php
class UsersController extends AppController {
	public $components = array('Flash', 'RequestHandler', 'Session');

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('logout');
	}

	public function login() {
		if ($this->request->is('post')) {

			if ($this->Auth->login()) {
				$this->Flash->success(__('Login successful, carry on'));
				$this->redirect($this->Auth->redirectUrl());
			} else {
				$this->Flash->error(__('Invalid username or password, try again'));
			}
		} else {
			$this->Flash->info(__('You must login to access this part of the site'));
		}
	}

	public function logout() {
		$this->redirect($this->Auth->logout());
	}

	public function edit() {
		if ($this->request->is('post')) {
			$admin_user = $this->User->find('first');

			if ($admin_user == null) {
				$this->Flash->error(__('Unable to find user account, this should never happen'));
			} else if (strcmp($admin_user['User']['password'], Security::hash($this->request->data['User']['current_password'], null, true)) != 0) {
				$this->Flash->error(__('The current password was incorrect'));
			} else if (strcmp($this->request->data['User']['password'], $this->request->data['User']['password_confirmation']) != 0) {
				$this->Flash->error(__('New passwords did not match'));
			} else {
				$admin_user['User']['password'] = $this->request->data['User']['password'];

				if ($this->User->save($admin_user)) {
					$this->Flash->success(__('Password changed successfully.'));
				} else {
					$this->Flash->error(__('Unable to update your settings, please review any validation errors.'));
				}
			}
		}
	}
}
?>
