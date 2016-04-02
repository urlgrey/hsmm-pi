<?php
class SystemController extends AppController {
	public $components = array('Flash', 'RequestHandler', 'Session');

	public function reboot() {

		file_put_contents('/var/data/hsmm-pi/reboot', time());

		$this->Flash->success(__('Reboot initiated, please reload this page in 2 minutes.'));
		$this->redirect(array('controller' => 'status', 'action' => 'index'));
	}

	public function shutdown() {

		file_put_contents('/var/data/hsmm-pi/shutdown', time());

		$this->Flash->success(__('Shutdown initiated, goodbye.'));
		$this->redirect(array('controller' => 'status', 'action' => 'index'));
	}

}
?>
