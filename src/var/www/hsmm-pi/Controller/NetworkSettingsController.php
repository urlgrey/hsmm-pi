<?php
class NetworkSettingsController extends AppController {
  public $helpers = array('Html', 'Session');
  public $components = array('RequestHandler', 'Session');
    
  public function edit($id = null) {
    $network_setting = $this->NetworkSetting->findById($id);

    if (!$network_setting) {
      throw new NotFoundException(__('Invalid setting'));
    }

    if ($this->request->isPost() || $this->request->isPut()) {
      if ($this->NetworkSetting->save($this->request->data)) {
	$latest_network_setting = $this->get_network_settings();
	$network_services = $this->get_network_services();
	$location = $this->get_location();

	$this->render_olsrd_config($latest_network_setting, $network_services, $location);
	$this->render_rclocal_config($latest_network_setting, $network_services);
	$this->render_network_interfaces_config($latest_network_setting);
	$this->render_dnsmasq_config($latest_network_setting);
	$this->render_hostname_config($latest_network_setting);
	$this->render_dhclient_config($latest_network_setting);
	$this->render_resolv_config($latest_network_setting);
	$this->render_hosts_config($latest_network_setting);
	$this->render_callsign_announcement_config($latest_network_setting);
	$this->render_ntp_config($latest_network_setting, $location);
	$this->render_vpn_client_config($latest_network_setting);
	$this->render_vtun_config($latest_network_setting);
	
	$this->Session->setFlash('Your settings have been saved and will take effect on the next reboot: <a href="#rebootModal" data-toggle="modal" class="btn btn-primary">Reboot</a>',
				 'default', array('class' => 'alert alert-success'));
      } else {
	$this->Session->setFlash('Unable to update your settings, please review any validation errors.', 'default', array('class' => 'alert alert-error'));
      }
    } else {
      // perform some checks in the case of an HTTP GET
      if ($network_setting['NetworkSetting']['wifi_ip_address'] == NULL) {
	// if no WIFI IP is set, then use one derived from the adapter MAC address
	$mac_file = '/sys/class/net/'.$network_setting['NetworkSetting']['wifi_adapter_name'].'/address';
	if (file_exists($mac_file)) {
	  $mac_address = explode(':', file_get_contents($mac_file));
	  $network_setting['NetworkSetting']['wifi_ip_address'] = 
	    '10.'.
	    hexdec($mac_address[3]).'.'.
	    hexdec($mac_address[4]).'.'.
	    hexdec($mac_address[5]);
	}
      }
    }

    if (!$this->request->data) {
      $this->request->data = $network_setting;
    }

    $this->set('wired_interface_mode', $network_setting['NetworkSetting']['wired_interface_mode']);
  }

  private function render_resolv_config($network_setting) {
    // update the resolv.conf file if the wired interface is in LAN mode so that DNS lookup hit the dnsmasq cache
    // it's possible that the node was previously running with the wired interface in WAN mode and there are left-over
    // DNS server entries in the resolv.conf file.  Wipe them out.
    if (0 == strcmp($network_setting['NetworkSetting']['wired_interface_mode'], 'LAN')) {
      file_put_contents('/etc/resolv.conf', file_get_contents(WWW_ROOT . "/files/resolv.conf.template"));
    }
  }
  
  
  
  private function render_callsign_announcement_config($network_setting) {
    $callsign_announcement = file_get_contents(WWW_ROOT . "/files/callsign_announcement.sh.template");
    $callsign_announcement_output = str_replace(array('{callsign}', '{wifi_adapter_name}'), 
						array(
						      str_pad($network_setting['NetworkSetting']['callsign'], 10, ' ', STR_PAD_LEFT),
						      $network_setting['NetworkSetting']['wifi_adapter_name']
						      ),
						$callsign_announcement);

    file_put_contents('/usr/local/bin/callsign_announcement.sh', $callsign_announcement_output);
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
      $wired_mode_block = 
	"static
    address ".$network_setting['NetworkSetting']['lan_ip_address']."
    netmask ".$network_setting['NetworkSetting']['lan_netmask']."
";
	//added this so we set the ip to static on the wan BWATT need to add if static or dhcp to this 
	} elseif (0 == strcmp($network_setting['NetworkSetting']['wired_interface_mode'], 'WAN') AND 0 == strcmp($network_setting['NetworkSetting']['wan_protocol'], 'STATIC')) {
      $wired_mode_block = 
	"static
    address ".$network_setting['NetworkSetting']['wan_static_ip']."
    netmask ".$network_setting['NetworkSetting']['wan_subnet_mask']."
	gateway ".$network_setting['NetworkSetting']['wan_gateway']."
";


    } else {
      $wired_mode_block = "dhcp";
    }

    $interfaces_conf_output = 
      str_replace(
		  array('{wired_adapter_name}','{wifi_protocol}', '{wifi_ip_address}','{wifi_netmask}', '{wifi_mode}', '{wifi_channel}', '{wifi_ssid}', '{wired_mode}', '{wifi_adapter_name}'), 
		  array(
			$network_setting['NetworkSetting']['wired_adapter_name'],
			strtolower($network_setting['NetworkSetting']['wifi_protocol']), 
			$network_setting['NetworkSetting']['wifi_ip_address'],
			$network_setting['NetworkSetting']['wifi_netmask'], 
			strtolower($network_setting['NetworkSetting']['wifi_mode']), 
			$network_setting['NetworkSetting']['wifi_channel'], 
			$network_setting['NetworkSetting']['wifi_ssid'], 
			$wired_mode_block,
			$network_setting['NetworkSetting']['wifi_adapter_name']
			), 
		  $interfaces_conf);

    file_put_contents('/etc/network/interfaces', $interfaces_conf_output);
  }



  private function render_dnsmasq_config($network_setting) {
    $dnsmasq_conf = file_get_contents(WWW_ROOT . "/files/dnsmasq/hsmm-pi.conf.template");

    $dhcp_interface = null;
    if ($network_setting['NetworkSetting']['lan_dhcp_server'] == TRUE) {
      $dhcp_interface = "interface=".$network_setting['NetworkSetting']['wired_adapter_name'];
    } else {
      $dhcp_interface = "
no-dhcp-interface=".$network_setting['NetworkSetting']['wired_adapter_name']."
no-dhcp-interface=".$network_setting['NetworkSetting']['wifi_adapter_name'];
    }

    $ip_parts = split("\.", $network_setting['NetworkSetting']['lan_ip_address']);
    $lan_dhcp_start = $ip_parts[0].'.'.$ip_parts[1].'.'.$ip_parts[2].'.'.$network_setting['NetworkSetting']['lan_dhcp_start'];
    $lan_dhcp_end = $ip_parts[0].'.'.$ip_parts[1].'.'.$ip_parts[2].'.'.$network_setting['NetworkSetting']['lan_dhcp_end'];

    $dnsmasq_conf_output = str_replace(array('{node_name}','{interfaces}', '{lan_ip_address}', '{lan_dhcp_start}', '{lan_dhcp_end}', '{lan_netmask}', '{wan_dns1}', '{wan_dns2}'), array($network_setting['NetworkSetting']['node_name'],$dhcp_interface, $network_setting['NetworkSetting']['lan_ip_address'], $lan_dhcp_start, $lan_dhcp_end, $network_setting['NetworkSetting']['lan_netmask'], $network_setting['NetworkSetting']['wan_dns1'], $network_setting['NetworkSetting']['wan_dns2']), $dnsmasq_conf);

    file_put_contents('/etc/dnsmasq.d/hsmm-pi.conf', $dnsmasq_conf_output);
  }


  private function render_dhclient_config($network_setting) {
    $dhclient_conf = file_get_contents(WWW_ROOT . "/files/dhclient.conf.template");
    $dhclient_conf_output = str_replace('{wired_adapter_name}', $network_setting['NetworkSetting']['wired_adapter_name'], $dhclient_conf);
    file_put_contents('/etc/dhcp/dhclient.conf', $dhclient_conf_output);
  }
 //this is a for vtund.conf to add the callsign, ip addresses, and passwords 
  private function render_vpn_client_config($network_setting)  {
	$vpnclient_conf = file_get_contents(WWW_ROOT . "/files/VPN/vtund.conf.template");
	$vpnclient_conf_output = str_replace(array('{server_port}','{callsign}','{server_password}','{client_ip}','{server_ip}',
	                                            '{client_one_callsign}','{client_one_password}','{client_one_server_ip}','{client_one_ip}',
												'{client_two_callsign}','{client_two_password}','{client_two_server_ip}','{client_two_ip}',
												'{client_three_callsign}','{client_three_password}','{client_three_server_ip}','{client_three_ip}',
												'{client_four_callsign}','{client_four_password}','{client_four_server_ip}','{client_four_ip}',
												'{client_five_callsign}','{client_five_password}','{client_five_server_ip}','{client_five_ip}',
												'{client_six_callsign}','{client_six_password}','{client_six_server_ip}','{client_six_ip}',
												'{client_seven_callsign}','{client_seven_password}','{client_seven_server_ip}','{client_seven_ip}',
												'{client_eight_callsign}','{client_eight_password}','{client_eight_server_ip}','{client_eight_ip}',
												'{client_nine_callsign}','{client_nine_password}','{client_nine_server_ip}','{client_nine_ip}',
												'{client_ten_callsign}','{client_ten_password}','{client_ten_server_ip}','{client_ten_ip}'),
												
						array( $network_setting['NetworkSetting']['server_port'],
						       strtolower($network_setting['NetworkSetting']['callsign']),
							   $network_setting['NetworkSetting']['server_password'],
							   $network_setting['NetworkSetting']['client_ip'],
							   $network_setting['NetworkSetting']['server_ip'],
							   
							   strtolower($network_setting['NetworkSetting']['client_one_callsign']),
							   $network_setting['NetworkSetting']['client_one_password'],
							   $network_setting['NetworkSetting']['client_one_server_ip'],
							   $network_setting['NetworkSetting']['client_one_ip'],
							   
							   strtolower($network_setting['NetworkSetting']['client_two_callsign']),
							   $network_setting['NetworkSetting']['client_two_password'],
							   $network_setting['NetworkSetting']['client_two_server_ip'],
							   $network_setting['NetworkSetting']['client_two_ip'],
							   
							   strtolower($network_setting['NetworkSetting']['client_three_callsign']),
							   $network_setting['NetworkSetting']['client_three_password'],
							   $network_setting['NetworkSetting']['client_three_server_ip'],
							   $network_setting['NetworkSetting']['client_three_ip'],
							   
							   strtolower($network_setting['NetworkSetting']['client_four_callsign']),
							   $network_setting['NetworkSetting']['client_four_password'],
							   $network_setting['NetworkSetting']['client_four_server_ip'],
							   $network_setting['NetworkSetting']['client_four_ip'],
							   
							   strtolower($network_setting['NetworkSetting']['client_five_callsign']),
							   $network_setting['NetworkSetting']['client_five_password'],
							   $network_setting['NetworkSetting']['client_five_server_ip'],
							   $network_setting['NetworkSetting']['client_five_ip'],
							   
							   strtolower($network_setting['NetworkSetting']['client_six_callsign']),
							   $network_setting['NetworkSetting']['client_six_password'],
							   $network_setting['NetworkSetting']['client_six_server_ip'],
							   $network_setting['NetworkSetting']['client_six_ip'],
							   
							   strtolower($network_setting['NetworkSetting']['client_seven_callsign']),
							   $network_setting['NetworkSetting']['client_seven_password'],
							   $network_setting['NetworkSetting']['client_seven_server_ip'],
							   $network_setting['NetworkSetting']['client_seven_ip'],
							   
							   strtolower($network_setting['NetworkSetting']['client_eight_callsign']),
							   $network_setting['NetworkSetting']['client_eight_password'],
							   $network_setting['NetworkSetting']['client_eight_server_ip'],
							   $network_setting['NetworkSetting']['client_eight_ip'],
							   
							   strtolower($network_setting['NetworkSetting']['client_nine_callsign']),
							   $network_setting['NetworkSetting']['client_nine_password'],
							   $network_setting['NetworkSetting']['client_nine_server_ip'],
							   $network_setting['NetworkSetting']['client_nine_ip'],
							   
							   strtolower($network_setting['NetworkSetting']['client_ten_callsign']),
							   $network_setting['NetworkSetting']['client_ten_password'],
							   $network_setting['NetworkSetting']['client_ten_server_ip'],
							   $network_setting['NetworkSetting']['client_ten_ip']), $vpnclient_conf);
	
							
    file_put_contents('/etc/vtund.conf', $vpnclient_conf_output);
	}
	
	private function render_vtun_config($network_setting) {
    $vtun_conf = file_get_contents(WWW_ROOT . "/files/VPN/vtun.template");
    $vtun_conf_output = str_replace(array('{server_port}','{callsign}','{server_dns}'),
	                                     array($network_setting['NetworkSetting']['server_port'],
										 strtolower($network_setting['NetworkSetting']['callsign']),
										 $network_setting['NetworkSetting']['server_dns']), $vtun_conf);
										 
	file_put_contents('/etc/default/vtun', $vtun_conf_output);
	
}
}
?>

