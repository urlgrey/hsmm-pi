<?php
class NetworkSetting extends AppModel {

	public $validate = array(
		'wifi_adapter_name' => array('required' => array('rule' => array('notBlank'), 'message' => 'A WIFI adapter name is required')),
		'wifi_ssid' => array('required' => array('rule' => array('notBlank'), 'message' => 'An SSID is required')),
		'wifi_netmask' => array('required' => array('rule' => '/^(((128|192|224|240|248|252|254)\.0\.0\.0)|(255\.(0|128|192|224|240|248|252|254)\.0\.0)|(255\.255\.(0|128|192|224|240|248|252|254)\.0)|(255\.255\.255\.(0|128|192|224|240|248|252|254)))$/i', 'message' => 'A valid netmask is required')),
		'wifi_ip_address' => array('rule' => array('ip'), 'message' => 'Please supply a valid IP address.'),
		'lan_ip_address' => array('rule' => array('ip'), 'message' => 'Please supply a valid IP address.'),
		'lan_netmask' => array('required' => array('rule' => '/^(((128|192|224|240|248|252|254)\.0\.0\.0)|(255\.(0|128|192|224|240|248|252|254)\.0\.0)|(255\.255\.(0|128|192|224|240|248|252|254)\.0)|(255\.255\.255\.(0|128|192|224|240|248|252|254)))$/i', 'message' => 'A valid netmask is required')),
		'direct_ip_address' => array('rule' => array('ip'), 'message' => 'Please supply a valid IP address.'),
		'direct_netmask' => array('required' => array('rule' => '/^(((128|192|224|240|248|252|254)\.0\.0\.0)|(255\.(0|128|192|224|240|248|252|254)\.0\.0)|(255\.255\.(0|128|192|224|240|248|252|254)\.0)|(255\.255\.255\.(0|128|192|224|240|248|252|254)))$/i', 'message' => 'A valid netmask is required')),
		'lan_dhcp_start' => array('number' => array('rule' => array('range', 0, 254), 'message' => 'A DHCP address between 0 and 254 is required')),
		'lan_dhcp_end' => array('number' => array('rule' => array('range', 0, 254), 'message' => 'A DHCP address between 0 and 254 is required')),
		'direct_dhcp_start' => array('number' => array('rule' => array('range', 0, 254), 'message' => 'A DHCP address between 0 and 254 is required')),
		'direct_dhcp_end' => array('number' => array('rule' => array('range', 0, 254), 'message' => 'A DHCP address between 0 and 254 is required')),
		'lan_mode' => array('rule' =>  '/^(NAT|Direct)$/i', 'message' => 'Please select NAT or Direct mode for the LAN Interface'),
		'wired_adapter_name' => array('required' => array('rule' => array('notBlank'), 'message' => 'A wired adapter name is required')),
		'wired_interface_mode' => array('required' => array('rule' => '/^(WAN|LAN)$/i', 'message' => 'The wired interface mode must be set to WAN or LAN.')),
		'wan_protocol' => array('required' => array('rule' => '/^(DHCP)$/i', 'message' => 'DHCP is the only mode currently supported.')),
		'wifi_channel' => array('required' => array('rule' => '/^(1|2|3|4|5|6|7|8|9|10|11)$/i', 'message' => 'Specify a valid channel-id.')),
		'wan_dns1' => array('rule' => array('ip'), 'message' => 'Please supply a valid IP address.'),
		'wan_dns2' => array('rule' => array('ip'), 'message' => 'Please supply a valid IP address.'),
		'node_name' => array('required' => array('rule' => array('notBlank'), 'message' => 'A globally unique nodename with your callsign is required')),
		'mesh_olsrd_secure_key' => array('required' => array('rule' => array('notBlank'), 'message' => 'A secure key value is required')),
		'ntp_server' => array('required' => array('rule' => array('notBlank'), 'message' => 'A NTP server is required')),
	);

}

?>
