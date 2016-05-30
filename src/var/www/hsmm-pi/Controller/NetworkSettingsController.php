<?php
class NetworkSettingsController extends AppController {
	public $helpers = array('Html', 'Session');
	public $components = array('Flash', 'RequestHandler', 'Session');

	public function edit($id = null) {
		$network_setting = $this->NetworkSetting->findById($id);

		if (!$network_setting) {
			throw new NotFoundException(__('Invalid setting'));
		}

		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->NetworkSetting->save($this->request->data)) {
				$latest_network_setting = $this->get_network_settings();
				$network_services = $this->get_network_services();
				$dhcp_reservations = $this->get_dhcp_reservations();
				$location = $this->get_location();

				$this->render_olsrd_config($latest_network_setting, $network_services, 
							   $dhcp_reservatins, $location);
				$this->render_rclocal_config($latest_network_setting, $network_services);
				$this->render_network_interfaces_config($latest_network_setting);
				$this->render_dnsmasq_config($latest_network_setting);
				$this->render_hostname_config($latest_network_setting);
				$this->render_dhclient_config($latest_network_setting);
				$this->render_resolv_config($latest_network_setting);
				$this->render_hosts_config($latest_network_setting);
				$this->render_ntp_config($latest_network_setting, $location);

				$this->Flash->reboot(__('Your settings have been saved and will take effect on the next reboot.'));
			} else {
				$this->Flash->error(__('Unable to update your settings, please review any validation errors.'));
			}
		} else {
			// perform some checks in the case of an HTTP GET
			if ($network_setting['NetworkSetting']['wifi_ip_address'] == NULL) {
				// if no WIFI IP is set, then use one derived from the adapter MAC address
				$mac_file = '/sys/class/net/' . $network_setting['NetworkSetting']['wifi_adapter_name'] . '/address';
				if (file_exists($mac_file)) {
					$mac_address = explode(':', file_get_contents($mac_file));
					$network_setting['NetworkSetting']['wifi_ip_address'] =
					'10.' .
					hexdec($mac_address[3]) . '.' .
					hexdec($mac_address[4]) . '.' .
					hexdec($mac_address[5]);
				}
			}
			if (($network_setting['NetworkSetting']['direct_ip_address'] == NULL) ||
			    (0 == strcmp($network_setting['NetworkSetting']['direct_ip_address'], '10.2.2.2'))) {
				// if no Direct IP is set, then use one derived from the adapter MAC address
				$mac_file = '/sys/class/net/' . $network_setting['NetworkSetting']['wired_adapter_name'] . '/address';
				if (file_exists($mac_file)) {
					$mac_address = explode(':', file_get_contents($mac_file));
					$mac5 = hexdec($mac_address[5]);
					if ($mac5 > 240) $mac5 = 240;	// highest, 240
					$network_setting['NetworkSetting']['direct_ip_address'] =
					'10.' .
					hexdec($mac_address[3]) . '.' .
					hexdec($mac_address[4]) . '.' . 
					$mac5;
					$dhcp_start =  $mac5 + 1;
					$dhcp_end =  $mac5 + 13;
					$network_setting['NetworkSetting']['direct_dhcp_start'] = $dhcp_start;
					$network_setting['NetworkSetting']['direct_dhcp_end'] = $dhcp_end;
				}
			}
		}

		if (!$this->request->data) {
			$this->request->data = $network_setting;
		}

		$this->set('wired_interface_mode', $network_setting['NetworkSetting']['wired_interface_mode']);
		$this->set('lan_mode', $network_setting['NetworkSetting']['lan_mode']);
	}

	private function render_resolv_config($network_setting) {
		// update the resolv.conf file if the wired interface is in LAN mode so that DNS lookup hit the dnsmasq cache
		// it's possible that the node was previously running with the wired interface in WAN mode and there are left-over
		// DNS server entries in the resolv.conf file.  Wipe them out.
		if (0 == strcmp($network_setting['NetworkSetting']['wired_interface_mode'], 'LAN')) {
			file_put_contents('/etc/resolv.conf', file_get_contents(WWW_ROOT . "/files/resolv.conf.template"));
		}
	}

	private function render_hosts_config($network_setting) {
		$hosts_conf = file_get_contents(WWW_ROOT . "/files/hosts.template");
		$hosts_conf_output = str_replace(array('{node_name}'), array($network_setting['NetworkSetting']['node_name']), $hosts_conf);

		file_put_contents('/etc/hosts', $hosts_conf_output);
	}

	private function render_hostname_config($network_setting) {
		$node_name = $network_setting['NetworkSetting']['node_name'];
		file_put_contents('/etc/hostname', $node_name);
	}

	private function render_network_interfaces_config($network_setting) {
		$interfaces_conf = file_get_contents(WWW_ROOT . "/files/network_interfaces/interfaces.template");
		$wired_mode_block = null;
		if (0 == strcmp($network_setting['NetworkSetting']['wired_interface_mode'], 'LAN')) {
			if (0 == strcmp($network_setting['NetworkSetting']['lan_mode'], 'NAT')) {
				$wired_mode_block =
				"static
    address " . $network_setting['NetworkSetting']['lan_ip_address'] . "
    netmask " . $network_setting['NetworkSetting']['lan_netmask'] . "
";
			} else if (0 == strcmp($network_setting['NetworkSetting']['lan_mode'], 'Direct')) {
				$wired_mode_block =
				"static
    address " . $network_setting['NetworkSetting']['direct_ip_address'] . "
    netmask " . $network_setting['NetworkSetting']['direct_netmask'] . "
";
			}
		} else {
			$wired_mode_block = "dhcp";
		}

		$interfaces_conf_output =
		str_replace(
			array('{wired_adapter_name}', '{wifi_protocol}', '{wifi_ip_address}', '{wifi_netmask}', '{wifi_mode}', '{wifi_channel}', '{wifi_ssid}', '{wired_mode}', '{wifi_adapter_name}'),
			array(
				$network_setting['NetworkSetting']['wired_adapter_name'],
				strtolower($network_setting['NetworkSetting']['wifi_protocol']),
				$network_setting['NetworkSetting']['wifi_ip_address'],
				$network_setting['NetworkSetting']['wifi_netmask'],
				strtolower($network_setting['NetworkSetting']['wifi_mode']),
				$network_setting['NetworkSetting']['wifi_channel'],
				$network_setting['NetworkSetting']['wifi_ssid'],
				$wired_mode_block,
				$network_setting['NetworkSetting']['wifi_adapter_name'],
			),
			$interfaces_conf);

		file_put_contents('/etc/network/interfaces', $interfaces_conf_output);
	}

	private function render_dnsmasq_config($network_setting) {
		$dnsmasq_conf = file_get_contents(WWW_ROOT . "/files/dnsmasq/hsmm-pi.conf.template");

		$dhcp_interface = null;
		if ($network_setting['NetworkSetting']['lan_dhcp_server'] == TRUE) {
			$dhcp_interface = "interface=" . $network_setting['NetworkSetting']['wired_adapter_name'];
		} else {
			$dhcp_interface = "
no-dhcp-interface=" . $network_setting['NetworkSetting']['wired_adapter_name'] . "
no-dhcp-interface=" . $network_setting['NetworkSetting']['wifi_adapter_name'];
		}

		$lan_ip_address = null;
		$lan_dhcp_start = null;
		$lan_dhcp_start = null;
		if (0 == strcmp($network_setting['NetworkSetting']['lan_mode'], 'NAT')) {
			$lan_ip_address = $network_setting['NetworkSetting']['lan_ip_address'];
			$ip_parts = split("\.", $lan_ip_address);
			$lan_dhcp_start = $ip_parts[0] . '.' . $ip_parts[1] . '.' . $ip_parts[2] . '.' . $network_setting['NetworkSetting']['lan_dhcp_start'];
			$lan_dhcp_end = $ip_parts[0] . '.' . $ip_parts[1] . '.' . $ip_parts[2] . '.' . $network_setting['NetworkSetting']['lan_dhcp_end'];
		} else if (0 == strcmp($network_setting['NetworkSetting']['lan_mode'], 'Direct')) {
			$lan_ip_address = $network_setting['NetworkSetting']['direct_ip_address'];
			$ip_parts = split("\.", $lan_ip_address);
			$lan_dhcp_start = $ip_parts[0] . '.' . $ip_parts[1] . '.' . $ip_parts[2] . '.' . $network_setting['NetworkSetting']['direct_dhcp_start'];
			$lan_dhcp_end = $ip_parts[0] . '.' . $ip_parts[1] . '.' . $ip_parts[2] . '.' . $network_setting['NetworkSetting']['direct_dhcp_end'];
		}
		$dnsmasq_conf_output = str_replace(array('{node_name}', '{interfaces}', '{lan_ip_address}', '{lan_dhcp_start}', '{lan_dhcp_end}', '{lan_netmask}', '{wan_dns1}', '{wan_dns2}'), array($network_setting['NetworkSetting']['node_name'], $dhcp_interface, $lan_ip_address, $lan_dhcp_start, $lan_dhcp_end, $network_setting['NetworkSetting']['lan_netmask'], $network_setting['NetworkSetting']['wan_dns1'], $network_setting['NetworkSetting']['wan_dns2']), $dnsmasq_conf);

		file_put_contents('/etc/dnsmasq.d/hsmm-pi.conf', $dnsmasq_conf_output);
	}

	private function render_dhclient_config($network_setting) {
		$dhclient_conf = file_get_contents(WWW_ROOT . "/files/dhclient.conf.template");
		$dhclient_conf_output = str_replace('{wired_adapter_name}', $network_setting['NetworkSetting']['wired_adapter_name'], $dhclient_conf);
		file_put_contents('/etc/dhcp/dhclient.conf', $dhclient_conf_output);
	}

}

?>
