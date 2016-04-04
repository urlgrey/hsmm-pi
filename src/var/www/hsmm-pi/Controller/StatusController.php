<?php
class StatusController extends AppController {
	public $components = array('RequestHandler');

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('index');
	}

	public function index() {
		$this->set('mesh_links', $this->get_mesh_info('links'));
		$this->set('mesh_routes', $this->get_mesh_info('routes')['routes']);
		$this->set('mesh_hosts', $this->get_mesh_hosts());
		$this->set('mesh_services', $this->get_mesh_services());
		$this->set('mesh_node_locations', $this->get_mesh_node_locations());
		$this->load_node_attributes();

		$neighbors = array();
		foreach ($this->get_mesh_info('neighbors')['neighbors'] as $node) {
			$neighbors[] = $node['ipv4Address'];
		}
		$this->set('mesh_neighbors', $neighbors);

		$location = $this->get_location();
		$this->set('maps_api_key', $location['LocationSetting']['maps_api_key']);
	}

	private function get_mesh_info($info_type) {
		$socket = null;
		try {
			$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
			if ($socket === false) {
				socket_clear_error($socket);
				return null;
			}

			$result = socket_connect($socket, "127.0.0.1", 9090);
			if ($result === false) {
				socket_clear_error($socket);
				return null;
			}

			$input = "GET /$info_type HTTP/1.1\r\n";
			$output = '';

			socket_write($socket, $input, strlen($input));
			while ($buffer = socket_read($socket, 2048)) {
				$output .= $buffer;
			}
			socket_close($socket);
			return json_decode($output, true);
		} catch (Exception $e) {
			socket_clear_error($socket);
			return null;
		}
	}

	private function get_mesh_hosts() {
		$hosts = array();
		if (file_exists("/var/run/hosts_olsr")) {
			$handle = @fopen("/var/run/hosts_olsr", "r");
			if ($handle) {
				while (($buffer = fgets($handle, 1024)) !== false) {
					if ($buffer != null) {
						$host_s = trim(substr($buffer, 0, strpos($buffer, '#')));
						if (strlen($host_s) > 0) {
							$host_parts = explode("\t", $host_s);
							if (sizeof($host_parts) >= 2) {
								$ip = $host_parts[0];
								$name = $host_parts[1];
								if (!array_key_exists($ip, $hosts)) {
									$hosts[$ip] = $name;
								}
							}
						}
					}
				}
				fclose($handle);
			}
		}

		return $hosts;
	}

	private function get_mesh_services() {
		$services = array();
		if (file_exists("/var/run/services_olsr")) {
			$handle = @fopen("/var/run/services_olsr", "r");
			if ($handle) {
				while (($buffer = fgets($handle, 1024)) !== false) {
					if ($buffer != null) {
						$service_s = trim(substr($buffer, 0, strpos($buffer, '#')));
						if (strlen($service_s) > 0) {
							$service_parts = explode('|', $service_s);
							if (sizeof($service_parts) > 0) {
								$services[] = $service_parts;
							}
						}
					}
				}
				fclose($handle);
			}
		}

		return $services;
	}

	private function get_mesh_node_locations() {
		$locations = array();
		if (file_exists("/var/run/latlon.js")) {
			$handle = @fopen("/var/run/latlon.js", "r");
			if ($handle) {
				while (($buffer = fgets($handle, 1024)) !== false) {
					if ($buffer != null) {
						if ((false != strstr($buffer, 'Self(')) || (false != strstr($buffer, 'Node('))) {
							// found a line with coordinates, handle it, and remove apostrophes
							$trimmed_node_str = str_replace('\'', '', substr($buffer, 5, -3));
							$location_parts = explode(',', $trimmed_node_str);
							if (sizeof($location_parts) > 0 &&
								!((floatval($location_parts[1]) == 0.0) && (floatval($location_parts[2] == 0.0)))
							) {
								// set the lat/long in the returned array using the host IP for the array index
								$locations[$location_parts[0]] = array('lat' => $location_parts[1], 'lon' => $location_parts[2]);
							}
						}
					}
				}
				fclose($handle);
			}
		}

		return $locations;
	}

	function startsWith($haystack, $needle) {
		return !strncmp($haystack, $needle, strlen($needle));
	}
}

?>
