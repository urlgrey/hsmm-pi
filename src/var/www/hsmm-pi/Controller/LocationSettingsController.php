<?php
class LocationSettingsController extends AppController {
	public $helpers = array('Html', 'Session');
	public $components = array('Flash', 'RequestHandler', 'Session');

	public function edit($id = null) {
		$location = $this->LocationSetting->findById($id);

		if (!$location) {
			throw new NotFoundException(__('Invalid setting'));
		}

		$this->set('location_source', $location['LocationSetting']['location_source']);
		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->LocationSetting->save($this->request->data)) {
				$latest_location = $this->get_location();
				$network_setting = $this->get_network_settings();

				$this->render_olsrd_config($this->get_network_settings(),
					$this->get_network_services(),
					$this->get_dhcp_reservations(),
					$latest_location);
				$this->render_gpsd_config($latest_location);
				$this->render_ntp_config($network_setting, $latest_location);
				$this->set('location_source', $latest_location['LocationSetting']['location_source']);
				$this->Flash->reboot(__('Your settings have been saved and will take effect on the next reboot.'));
			} else {
				$this->Flash->error(__('Unable to update your settings, please review any validation errors.'));
			}
		}

		if (!$this->request->data) {
			$this->request->data = $location;
		}
	}

	private function render_gpsd_config($location) {
		$gpsd_config = file_get_contents(WWW_ROOT . "/files/gpsd.conf.template");
		$gpsd_config_output = str_replace(array('{gpsd_enabled}', '{gps_device_name}'),
			array(
				(
					(0 == strcasecmp($location['LocationSetting']['location_source'], 'gps')) &&
					($location['LocationSetting']['transmit_location_enabled'] == TRUE))
				? 'true' : 'false',
				$location['LocationSetting']['gps_device_name']),
			$gpsd_config);

		file_put_contents('/etc/default/gpsd', $gpsd_config_output);
	}

}

?>
