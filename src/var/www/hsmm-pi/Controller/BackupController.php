<?php
class BackupController extends AppController
{
    public $helpers = array('Html', 'Form', 'Session');
    public $components = array('RequestHandler', 'Session');
    
    public function index() { }

    public function get() {
      $this->viewClass = 'Media';
      $params = array(
		      'id'        => 'hsmm-pi.sqlite',
		      'name'      => 'hsmm-pi',
		      'download'  => true,
		      'extension' => 'sqlite',
		      'path'      => APP . 'Database' . DS
		      );
      $this->set($params);
    }

    public function edit() {
      debug($this->request);
      //      $this->redirect(array('controller'=>'status', 'action'=>'index'));
    }


}

?>

