<?php
/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('Controller', 'Controller');

class AppController extends Controller {

	protected $olsrd_fixed_location_file = '/var/run/latlong-input-olsrd';

	public $components = array('Session',
		'Auth' => array('loginRedirect' => array('controller' => 'status',
			'action' => 'index'),
			'logoutRedirect' => array('controller' => 'status',
				'action' => 'index')));

	protected function load_node_attributes() {
		$this->loadModel('NetworkSetting');
		$settings = $this->NetworkSetting->findById(1);
		$this->set('node_name', $settings['NetworkSetting']['node_name']);
		$this->set('node_wifi_ip_address', $settings['NetworkSetting']['wifi_ip_address']);
	}

	protected function get_location() {
		$this->loadModel('LocationSetting');
		return $this->LocationSetting->findById(1);
	}

	protected function get_network_settings() {
		$this->loadModel('NetworkSetting');
		return $this->NetworkSetting->findById(1);
	}

	protected function get_network_services() {
		$this->loadModel('NetworkService');
		return $this->NetworkService->find('all');
	}

	protected function get_dhcp_reservations() {
		$this->loadModel('DhcpReservation');
		return $this->DhcpReservation->find('all');
	}

	protected function render_ntp_config($network_setting, $location) {
		if (0 == strcasecmp($location['LocationSetting']['location_source'], 'gps')) {
			$gpsd_time_server_info =
			"
server 127.127.28.0
fudge 127.127.28.0 time1 0.420 refid GPS

server 127.127.28.1 prefer
fudge 127.127.28.1 refid GPS1";
		} else {
			$gpsd_time_server_info = "";
		}

		$ntp_config = file_get_contents(WWW_ROOT . "/files/ntp.conf.template");
		$ntp_config_output = str_replace(array('{gpsd_time_server_info}',
			'{ntp_server}'),
			array($gpsd_time_server_info,
				$network_setting['NetworkSetting']['ntp_server']),
			$ntp_config);
		file_put_contents('/etc/ntp.conf', $ntp_config_output);
	}

	protected function render_rclocal_config($network_setting, $network_services) {
		$rclocal_conf = file_get_contents(WWW_ROOT . "/files/rc.local.template");

		$iptables_service_routing = "";
		if (0 == strcmp($network_setting['NetworkSetting']['wired_interface_mode'], 'WAN')) {
			if ($network_setting['NetworkSetting']['wan_mesh_gateway'] == TRUE) {
				$iptables_gateway_commands =
				"
iptables -t nat -A POSTROUTING -o " . $network_setting['NetworkSetting']['wired_adapter_name'] . " -j MASQUERADE
iptables -A FORWARD -i " . $network_setting['NetworkSetting']['wired_adapter_name'] . " -o " . $network_setting['NetworkSetting']['wifi_adapter_name'] . " -m conntrack --ctstate RELATED,ESTABLISHED -j ACCEPT
iptables -A FORWARD -i " . $network_setting['NetworkSetting']['wifi_adapter_name'] . " -o " . $network_setting['NetworkSetting']['wired_adapter_name'] . " -j ACCEPT
";
			} else {
				$iptables_gateway_commands = "";
			}
		} else {
			if (0 == strcmp($network_setting['NetworkSetting']['lan_mode'], 'NAT')) {
				$iptables_gateway_commands =
				"
# Flush the tables
iptables -F INPUT
iptables -F OUTPUT
iptables -F FORWARD

iptables -t nat -P PREROUTING ACCEPT
iptables -t nat -P POSTROUTING ACCEPT
iptables -t nat -P OUTPUT ACCEPT

# Allow forwarding packets:
iptables -A FORWARD -p ALL -i " . $network_setting['NetworkSetting']['wired_adapter_name'] . " -j ACCEPT
iptables -A FORWARD -i " . $network_setting['NetworkSetting']['wifi_adapter_name'] . " -m state --state ESTABLISHED,RELATED -j ACCEPT

# Packet masquerading
iptables -t nat -A POSTROUTING -o " . $network_setting['NetworkSetting']['wifi_adapter_name'] . " -j SNAT --to-source " . $network_setting['NetworkSetting']['wifi_ip_address'];

                        	if ($network_services != NULL && sizeof($network_services) > 0) {
                                	foreach ($network_services as $service) {
                                        	$iptables_service_routing .=
                                        	"
iptables -t nat -A PREROUTING -i " . $network_setting['NetworkSetting']['wifi_adapter_name'] . " -p " .$service['NetworkService']['protocol'] . " --dport " . $service['NetworkService']['port'] . " -j DNAT--to-destination " . $service['NetworkService']['host'] . ":" . $service['NetworkService']['local_port'] . "
iptables -t nat -A POSTROUTING -p " . $service['NetworkService']['protocol'] . " --dport " .$service['NetworkService']['port'] . " -j MASQUERADE\n";
                                	}
				}
			}
			else { // Direct mode
				$iptables_gateway_commands =
				"
# Flush the tables
iptables -F INPUT
iptables -F OUTPUT
iptables -F FORWARD

# Allow forwarding packets:
iptables -A FORWARD -p ALL -i " . $network_setting['NetworkSetting']['wired_adapter_name'] . " -j ACCEPT
iptables -A FORWARD -i " . $network_setting['NetworkSetting']['wifi_adapter_name'] . " -m state --state ESTABLISHED,RELATED -j ACCEPT

# Packet masquerading
iptables -t nat -A POSTROUTING -o " . $network_setting['NetworkSetting']['wifi_adapter_name'] . " -j SNAT --to-source " . $network_setting['NetworkSetting']['wifi_ip_address'];

                        	if ($network_services != NULL && sizeof($network_services) > 0) {
                                	foreach ($network_services as $service) {
                                        	$iptables_service_routing .=
                                        	"";
                                	}
				}
			}
		}

		$rclocal_conf_output = str_replace(array('{wifi_adapter_name}',
			'{iptables_gateway_commands}',
			'{iptables_service_routing}'),
			array($network_setting['NetworkSetting']['wifi_adapter_name'],
				$iptables_gateway_commands, $iptables_service_routing),
			$rclocal_conf);

		file_put_contents('/etc/rc.local', $rclocal_conf_output);
	}

	protected function render_olsrd_config($network_setting, $network_services, $dhcp_reservations, $location) {
		$olsrd_conf = file_get_contents(WWW_ROOT . "/files/olsrd/olsrd.conf.template");
		$olsrd_secure_block = null;

		if ($network_setting['NetworkSetting']['mesh_olsrd_secure'] == TRUE) {
			$olsrd_secure_block =
			"LoadPlugin \"olsrd_secure.so.0.6\"
{
    PlParam     \"Keyfile\"   \"/etc/olsrd/olsrd.key\"
}";

		} else {
			$olsrd_secure_block = "";
		}

		if ((0 == strcmp($network_setting['NetworkSetting']['wired_interface_mode'], 'WAN')) &&
			($network_setting['NetworkSetting']['wan_mesh_gateway'] == TRUE)) {
			$olsrd_network_services = "";
			if ($network_setting['NetworkSetting']['wan_fixed_connection'] == FALSE) {
				$olsrd_gateway =
				"
LoadPlugin \"olsrd_dyn_gw.so.0.5\"
{
    # The plugin check interval can be set here in milliseconds.
    # The default is 1000 ms (1 second).
    PlParam     \"CheckInterval\"  \"5000\"

    # The ping check interval in case there is any pinged host specified.
    # The default is 5 seconds.
    PlParam     \"PingInterval\"   \"40\"

    # If one or more IPv4 addresses are given, do a ping on these in
    # descending order to validate that there is not only an entry in
    # routing table, but also a real network connection. If any of
    # these addresses could be pinged successfully, the test was
    # succesful, i.e. if the ping on the 1st address was successful,the
    # 2nd won't be pinged.
    #
    # The Ping list applies to the group of HNAs specified above or to the
    # default internet gateway when no HNA is specified.
    #
    # Running the plugin without parameters acts as the 'old' dyn_gw_plain.

    #   The following ping entries for the internet gateway
    PlParam \"Ping\"   \"" . $network_setting['NetworkSetting']['wan_dns1'] . "\"
    PlParam \"Ping\"   \"" . $network_setting['NetworkSetting']['wan_dns2'] . "\"
}";
			} else {
				$olsrd_gateway =
				"
LoadPlugin \"olsrd_dyn_gw_plain.so.0.4\"
{
}
";
			}
		} else {
			$olsrd_gateway = "";
			$olsrd_network_services = "";
		}

		if ($network_services != NULL && sizeof($network_services) > 0) {
			foreach ($network_services as $service) {
				$olsrd_network_services .= "
    PlParam \"service\" \"" . $service['NetworkService']['service_protocol_name'] . "://" . $network_setting['NetworkSetting']['node_name'] . ":" . $service['NetworkService']['port'] . "/" . $service['NetworkService']['path'] . "|" . $service['NetworkService']['protocol'] . "|" . $service['NetworkService']['name'] . "\"
";
			}
		}

		$transmit_location_option = null;
		if ($location['LocationSetting']['transmit_location_enabled'] == TRUE) {
			if (0 == strcmp($location['LocationSetting']['location_source'], 'fixed')) {
				$transmit_location_option =
				"
    PlParam \"lat\" \"" . $location['LocationSetting']['lat'] . "\"
    PlParam \"lon\" \"" . $location['LocationSetting']['lon'] . "\"
";
			} else {
				$transmit_location_option = "    PlParam \"latlon-infile\" \"" . $this->olsrd_fixed_location_file . "\"";
			}
		} else {
			$transmit_location_option = "";
		}

		// Load reservations from DhcpReservations
		if ($dhcp_reservations != NULL && sizeof($dhcp_reservations) > 0) {
			foreach ($dhcp_reservations as $reservation) {
				$olsrd_dhcp_reservations .=
"    PlParam \"" . $reservation['DhcpReservation']['ip_address'] . "\" \"" . $reservation['DhcpReservation']['hostname'] . "\"";
			}
		}

		// Calculate networka and netmask for HNA4 entry
		$lan_ip_network = '0.0.0.0';
		$lan_ip_netmask = '0.0.0.0';
		if ((0 == strcmp($network_setting['NetworkSetting']['wired_interface_mode'], 'LAN')) ) {
			if ((0 == strcmp($network_setting['NetworkSetting']['lan_mode'], 'NAT')) ) {
				$ip_parts = split('\.', $network_setting['NetworkSetting']['lan_ip_address']);
				$ip3 = intval($ip_parts[3], 10);
				$lan_ip_netmask = $network_setting['NetworkSetting']['lan_netmask'];
				$mask_parts = split('\.', $lan_ip_netmask);
				$mask3 = intval($mask_parts[3], 10);
				$lan_ip_network = $ip_parts[0].'.'.$ip_parts[1].'.'.$ip_parts[2].'.'.strval($ip3 & $mask3);
			}
			else if ((0 == strcmp($network_setting['NetworkSetting']['lan_mode'], 'Direct')) ) {
				$ip_parts = split('\.', $network_setting['NetworkSetting']['direct_ip_address']);
				$ip3 = intval($ip_parts[3], 10);
				$lan_ip_netmask = $network_setting['NetworkSetting']['direct_netmask'];
				$mask_parts = split('\.', $lan_ip_netmask);
				$mask3 = intval($mask_parts[3], 10);
				$lan_ip_network = $ip_parts[0].'.'.$ip_parts[1].'.'.$ip_parts[2].'.'.strval($ip3 & $mask3);
			} 
		} 

		$olsrd_conf_output = str_replace(array('{latlon_infile}',
			'{wifi_ip_address}',
			'{wifi_adapter_name}',
			'{node_name}',
			'{lan_ip_network}',
			'{lan_ip_netmask}',
			'{olsrd_dynamic_gateway_block}',
			'{olsrd_secure_block}',
			'{olsrd_network_services}',
			'{olsrd_dhcp_reservations}'),
			array($transmit_location_option,
				$network_setting['NetworkSetting']['wifi_ip_address'],
				$network_setting['NetworkSetting']['wifi_adapter_name'],
				$network_setting['NetworkSetting']['node_name'],
				$lan_ip_network,
				$lan_ip_netmask,
				$olsrd_secure_block,
				$olsrd_gateway,
				$olsrd_network_services,
				$olsrd_dhcp_reservations),
			$olsrd_conf);

		file_put_contents('/etc/olsrd/olsrd.conf', $olsrd_conf_output);

		$olsrd_key = null;
		if ($network_setting['NetworkSetting']['mesh_olsrd_secure_key'] != null) {
			$olsrd_key = $network_setting['NetworkSetting']['mesh_olsrd_secure_key'];
		} else {
			$olsrd_key = "";
		}

		file_put_contents('/etc/olsrd/olsrd.key', $olsrd_key);
	}

	// Output dhcp reservations to /etc/ethers
	protected function render_ethers($dhcp_reservations) {
		$ethers = file_get_contents(WWW_ROOT . "/files/ethers.template");
		$ethers_reservations = null;
        	foreach ($dhcp_reservations as $dhcp_reservation) {
                       	$ethers_reservations .=
$dhcp_reservation['DhcpReservation']['hostname'] . " " . $dhcp_reservation['DhcpReservation']['ip_address'] . " " . $dhcp_reservation['DhcpReservation']['mac_address'];
		}
		// Read in template file
		// Replace strings
		$ethers_output = str_replace(array('{dhcp_reservations}'),
			array($ethers_reservations),
			$ethers);

		file_put_contents('/etc/ethers', $ethers_output);
	}
}
?>
