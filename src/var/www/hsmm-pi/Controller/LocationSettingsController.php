<?php
class LocationSettingsController extends AppController {
  public $helpers = array('Html', 'Session');
  public $components = array('RequestHandler', 'Session');

  public function edit($id = null) {
    $location = $this->LocationSetting->findById($id);

    if (!$location) {
      throw new NotFoundException(__('Invalid setting'));
    }
        
    $this->set('location_source', $location['LocationSetting']['location_source']);
    if ($this->request->isPost() || $this->request->isPut()) {
      if ($this->LocationSetting->save($this->request->data)) {

	$latest_location = $this->get_location();
	$this->render_olsrd_config($this->get_network_settings(), 
				   $this->get_network_services(), 
				   $latest_location);
	$this->render_gpsd_config($latest_location);
	$this->render_ntp_config($latest_location);
	$this->set('location_source', $latest_location['LocationSetting']['location_source']);
	$this->Session->setFlash('Your settings have been saved and will take effect on the next reboot: <a href="#rebootModal" data-toggle="modal" class="btn btn-primary">Reboot</a>',
				 'default', array('class' => 'alert alert-success'));
      } else {
	$this->Session->setFlash('Unable to update your settings, please review any validation errors.', 'default', array('class' => 'alert alert-error'));
      }
    }

    if (!$this->request->data) {
      $this->request->data = $location;
    }
  }

  private function render_ntp_config($location) {
    if (0 == strcasecmp($location['LocationSetting']['location_source'], 'gps')) {
      $gpsd_time_server_info = "
server 127.127.28.0
fudge 127.127.28.0 time1 0.420 refid GPS

server 127.127.28.1 prefer
fudge 127.127.28.1 refid GPS1";
    } else {
      $gpsd_time_server_info = "";
    }

    $ntp_config = file_get_contents(WWW_ROOT . "/files/ntp.conf.template");
    $ntp_config_output = str_replace(array('{gpsd_time_server_info}'), 
				      array($gpsd_time_server_info),
				      $ntp_config);
    
    file_put_contents('/etc/ntp.conf', $ntp_config_output);
  }

  private function render_gpsd_config($location) {
    $gpsd_config = file_get_contents(WWW_ROOT . "/files/gpsd.conf.template");
    $gpsd_config_output = str_replace(array('{gpsd_enabled}', '{gps_device_name}'), 
				      array(
					    (
					     (0 == strcasecmp($location['LocationSetting']['location_source'], 'gps')) && 
					     ($location['LocationSetting']['transmit_location_enabled'] == TRUE)) 
					    ? 'true' : 'false',
					    $location['LocationSetting']['gps_device_name']
					    ),
				      $gpsd_config);
    
    file_put_contents('/etc/default/gpsd', $gpsd_config_output);
  }

}

?>