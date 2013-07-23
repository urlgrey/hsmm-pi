<?php
class SystemController extends AppController
{
    public $components = array('RequestHandler', 'Session');
    
    public function reboot()
    {

      file_put_contents('/var/data/hsmm-pi/reboot', time());

      $this->Session->setFlash(__('Reboot initiated, please reload this page in 2 minutes.'), 'default', array('class' => 'alert alert-success'));
      $this->redirect(array('controller'=>'status', 'action'=>'index'));
    }

}
?>
