<?php
class AppSchema extends CakeSchema {
	public $connection = 'default';

	public function before($event = array()) {
		$db = ConnectionManager::getDataSource($this->connection);
		$db->cacheSources = false;
		return true;
	}

	public function after($event = array()) {
		App::uses('ClassRegistry', 'Utility');
		App::uses('AuthComponent', 'Controller/Component');

		if (isset($event['create'])) {
			switch ($event['create']) {
				case 'location_settings':
					$location_setting = ClassRegistry::init('LocationSetting');
					$location_setting->create();
					$location_setting->save();
					break;
				case 'network_settings':
					$network_setting = ClassRegistry::init('NetworkSetting');
					$network_setting->create();
					$network_setting->save(
						array(
							'wan_mesh_gateway' => true,
							'mesh_olsrd_secure_key' => 'FFFFFFFFFFFFFFFF'
						)
					);
					break;
				case 'users':
					$user = ClassRegistry::init('User');
					$user->create();
					$user->save(
						array('User' =>
							array(
								'username' => 'admin',
								'password' => 'changeme',
								'role' => 'admin'
							)
						), $validate = false
					);
					break;
			}
		}
	}

	public $location_settings = array(
		'id' => array('type' => 'integer', 'null' => false, 'length' => 11, 'key' => 'primary'),
		'transmit_location_enabled' => array('type' => 'boolean', 'null' => false, 'default' => false),
		'location_source' => array('type' => 'string', 'null' => false, 'default' => 'fixed'),
		'lat' => array('type' => 'float', 'null' => false, 'default' => '0.0'),
		'lon' => array('type' => 'float', 'null' => false, 'default' => '0.0'),
		'gps_device_name' => array('type' => 'string', 'null' => false, 'default' => 'gps0'),
		'maps_api_key' => array('type' => 'string', 'null' => true),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => true)
		),
		'tableParameters' => array()
	);

	public $network_services = array(
		'id' => array('type' => 'integer', 'null' => false, 'length' => 11, 'key' => 'primary'),
		'name' => array('type' => 'string', 'null' => false),
		'host' => array('type' => 'string', 'null' => false, 'default' => 'null'),
		'port' => array('type' => 'integer', 'null' => false),
		'path' => array('type' => 'string', 'null' => false, 'default' => ''),
		'protocol' => array('type' => 'string', 'null' => false, 'default' => 'tcp'),
		'local_port' => array('type' => 'integer', 'null' => false),
		'service_protocol_name' => array('type' => 'string', 'null' => false, 'default' => 'http'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => true)
		),
		'tableParameters' => array()
	);
	public $dhcp_reservations = array(
		'id' => array('type' => 'integer', 'null' => false, 'length' => 11, 'key' => 'primary'),
		'hostname' => array('type' => 'string', 'null' => false),
		'ip_address' => array('type' => 'string', 'null' => true),
		'mac_address' => array('type' => 'string', 'null' => false, 'default' => ''),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => true)
		),
		'tableParameters' => array()
	);

	public $network_settings = array(
		'id' => array('type' => 'integer', 'null' => false, 'length' => 11, 'key' => 'primary'),
		'wifi_protocol' => array('type' => 'string', 'null' => false, 'default' => 'Static'),
		'wifi_ip_address' => array('type' => 'string', 'null' => true),
		'wifi_netmask' => array('type' => 'string', 'null' => false, 'default' => '255.0.0.0'),
		'wifi_ssid' => array('type' => 'string', 'null' => false, 'default' => 'AREDN-20-v3'),
		'wifi_mode' => array('type' => 'string', 'null' => false, 'default' => 'Ad-Hoc'),
		'wifi_channel' => array('type' => 'integer', 'null' => false, 'default' => 1),
		'wired_interface_mode' => array('type' => 'string', 'null' => false, 'default' => 'LAN'),
		'lan_mode' => array('type' => 'string', 'null' => false, 'default' => 'NAT'),
		'lan_ip_address' => array('type' => 'string', 'null' => false, 'default' => '172.27.2.1'),
		'lan_netmask' => array('type' => 'string', 'null' => false, 'default' => '255.255.255.0'),
		'direct_ip_address' => array('type' => 'string', 'null' => false, 'default' => '10.2.2.2'),
		'direct_netmask' => array('type' => 'string', 'null' => false, 'default' => '255.255.255.240'),
		'lan_dhcp_server' => array('type' => 'boolean', 'null' => false, 'default' => true),
		'lan_dhcp_start' => array('type' => 'integer', 'null' => false, 'default' => 5),
		'lan_dhcp_end' => array('type' => 'integer', 'null' => false, 'default' => 25),
		'direct_dhcp_start' => array('type' => 'integer', 'null' => false, 'default' => 5),
		'direct_dhcp_end' => array('type' => 'integer', 'null' => false, 'default' => 25),
		'wan_protocol' => array('type' => 'string', 'null' => false, 'default' => 'DHCP'),
		'wan_dns1' => array('type' => 'string', 'null' => true, 'default' => '8.8.8.8'),
		'wan_dns2' => array('type' => 'string', 'null' => true, 'default' => '8.8.4.4'),
		'wan_mesh_gateway' => array('type' => 'boolean', 'null' => false, 'default' => false),
		'mesh_olsrd_secure' => array('type' => 'boolean', 'null' => false, 'default' => false),
		'mesh_olsrd_secure_key' => array('type' => 'string', 'null' => true),
		'node_name' => array('type' => 'string', 'null' => false, 'default' => 'UNDEF-1'),
		'wifi_adapter_name' => array('type' => 'string', 'null' => false, 'default' => 'wlan0'),
		'wired_adapter_name' => array('type' => 'string', 'null' => false, 'default' => 'eth0'),
		'wan_fixed_connection' => array('type' => 'boolean', 'null' => false, 'default' => false),
		'ntp_server' => array('type' => 'string', 'null' => false, 'default' => 'ntp.ubuntu.com'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => true)
		),
		'tableParameters' => array()
	);

	public $users = array(
		'id' => array('type' => 'integer', 'null' => false, 'length' => 11, 'key' => 'primary'),
		'username' => array('type' => 'string', 'null' => false, 'length' => 50),
		'password' => array('type' => 'string', 'null' => false, 'length' => 50),
		'role' => array('type' => 'string', 'null' => true, 'length' => 20),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => true)
		),
		'tableParameters' => array()
	);

}
