<?php
class ShellUserKeysController extends AppController {
	public $helpers = array('Html', 'Form', 'Session');
	public $components = array('RequestHandler', 'Session');

	public function index() {
		$this->set('shell_user_keys', $this->ShellUserKey->find('all'));
	}

	public function view($id = null) {
		if (!$id) {
			throw new NotFoundException(__('Invalid user key'));
		}

		// Retrieve SSH user keys
		$shell_user_key = $this->ShellUserKey->findById($id);
		if (!$shell_user_key) {
			throw new NotFoundException(__('Invalid user key'));
		}

		$this->set('shell_user_key', $shell_user_key);
	}

	public function edit($id = null) {
		if (!$id) {
			throw new NotFoundException(__('Invalid user key'));
		}

		$shell_user_key = $this->ShellUserKey->findById($id);
		if (!$shell_user_key) {
			throw new NotFoundException(__('Invalid user key'));
		}

		if ($this->request->isPost() || $this->request->isPut()) {
			$this->ShellUserKey->id = $id;

			if ($this->ShellUserKey->save($this->request->data)) {
				$this->Session->setFlash('Your user key has been updated.', 'default', array('class' => 'alert alert-success'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash('Unable to update your user key.', 'default', array('class' => 'alert alert-error'));
			}
		}

		if (!$this->request->data) {
			$this->request->data = $shell_user_key;
		}
	}

	public function delete($id = null) {
		$this->ShellUserKey->id = $id;

		if (!$this->ShellUserKey->exists()) {
			throw new NotFoundException(__('Invalid user key'), 'default', array('class' => 'alert alert-error'));
		}

		if ($this->ShellUserKey->delete()) {
			$this->Session->setFlash(__('User key deleted'), 'default', array('class' => 'alert alert-success'));
			$this->redirect(array(
				'action' => 'index',
			));
		}
	}

	public function add() {
		if ($this->request->is('post')) {

			$this->ShellUserKey->create();
			if ($this->ShellUserKey->save($this->request->data)) {
				$this->Session->setFlash('Your user key has been saved.', 'default', array('class' => 'alert alert-success'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash('Unable to add your user key.', 'default', array('class' => 'alert alert-error'));
			}
		}
	}

	private function writeKeys() {
		// 1) clear the contents of the authorized_keys file
		exec('/usr/share/hsmm-pi/bin/clear_authorized_keys.sh');

		$keys = $this->ShellUserKey->find('all');

		// 2) add each key to the file
		foreach ($keys as $key) {

		}

		return TRUE;
	}
}
?>

