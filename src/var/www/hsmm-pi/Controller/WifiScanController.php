<?php
class WifiScanController extends AppController {
	public $components = array('RequestHandler', 'Session');

	public function index() {
		$this->set('wifi_networks', $this->get_wifi_networks());
	}

	private function get_wifi_networks() {
		$networks = array();
		$essid = null;
		$address = null;
		$channel = null;
		$signal_quality = null;

		$this->loadModel('NetworkSetting');
		$settings = $this->NetworkSetting->findById(1);

		$output = array();
		exec('sudo /sbin/iwlist ' . $settings['NetworkSetting']['wifi_adapter_name'] . ' scan', $output);
		foreach ($output AS $line) {
			if (preg_match('/ESSID:"(\S+)"/', $line, $matches)) {
				$essid = $matches[1];
			}

			if (preg_match('/Address: (\S+)/', $line, $matches)) {
				$address = $matches[1];
			}

			if (preg_match('/Channel (\d+)/', $line, $matches)) {
				$channel = $matches[1];
			}

			if (preg_match('/Quality=(\d+)\/(\d+)/', $line, $matches) || preg_match('/Signal level=(\d+)\/(\d+)/', $line, $matches)) {
				$signal_quality = $matches[1] / $matches[2];
			}

			if ($address && $essid && $channel && $signal_quality) {
				$networks[] = array('essid' => $essid, 'address' => $address, 'channel' => $channel,
					'signal_quality' => $signal_quality);
				$essid = null;
				$address = null;
				$channel = null;
				$signal_quality = null;
			}
		}
		return $networks;
	}
}

?>

