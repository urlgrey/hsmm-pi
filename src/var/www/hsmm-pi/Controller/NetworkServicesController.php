<?php
class NetworkServicesController extends AppController
{
  public $helpers = array('Html', 'Form', 'Session');
  public $components = array('RequestHandler', 'Session');
    
  public function index()
  {
    $this->set('services', $this->NetworkService->find('all'));

    $this->loadModel('NetworkSetting');
    $settings = $this->NetworkSetting->findById(1);
    $this->set('node_name', $settings['NetworkSetting']['node_name']);
  }

  public function delete($id = null)
  {
    $this->NetworkService->id = $id;
        
    if (!$this->NetworkService->exists()) {
      throw new NotFoundException(__('Invalid service key'), 'default', array('class' => 'alert alert-error'));
    }
        
    if ($this->NetworkService->delete()) {
      $this->loadModel('NetworkSetting');
      $network_setting = $this->NetworkSetting->findById(1);
      $network_services = $this->NetworkService->find('all');

      $this->render_olsrd_config($network_setting, $network_services);
      $this->render_rclocal_config($network_setting, $network_services);

      $this->Session->setFlash(__('Service deleted'), 'default', array('class' => 'alert alert-success'));
      $this->redirect(array('action' => 'index'));
    }
  }

  public function add() {
    if ($this->request->is('post')) {

	$this->NetworkService->create();
	if ($this->NetworkService->save($this->request->data)) {
	  // retrieve other network settings
	  $this->loadModel('NetworkSetting');
	  $network_setting = $this->NetworkSetting->findById(1);
	  $network_services = $this->NetworkService->find('all');

	  $this->render_olsrd_config($network_setting, $network_services);
	  $this->render_rclocal_config($network_setting, $network_services);

	  $this->Session->setFlash('Your new service has been added.', 'default', array('class' => 'alert alert-success'));
	  $this->redirect(array('action' => 'index'));
	} else {
	  $this->Session->setFlash('Unable to add your service.', 'default', array('class' => 'alert alert-error'));
	}
    }
  }
}
  ?>

