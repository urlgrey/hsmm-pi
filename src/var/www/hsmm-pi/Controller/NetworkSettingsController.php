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
	$latest_network_setting = $this->NetworkSetting->findById($id);
	$this->render_olsrd_config($latest_network_setting);
	$this->render_network_interfaces_config($latest_network_setting);
	$this->render_dnsmasq_config($latest_network_setting);
	$this->render_hostname_config($latest_network_setting);
	$this->render_resolv_config($latest_network_setting);
	$this->render_hosts_config($latest_network_setting);
	$this->render_rclocal_config($latest_network_setting);
	$this->render_callsign_announcement_config($latest_network_setting);
	$this->Session->setFlash('Your settings have been saved and will take effect on the next reboot: <a href="#rebootModal" data-toggle="modal" class="btn btn-primary">Reboot</a>',
				 'default', array('class' => 'alert alert-success'));
      } else {
	$this->Session->setFlash('Unable to update your settings, please review any validation errors.', 'default', array('class' => 'alert alert-error'));
      }
    }

    if (!$this->request->data) {
      $this->request->data = $network_setting;
    }

    $this->set('wired_interface_mode', $network_setting['NetworkSetting']['wired_interface_mode']);
  }


  private function render_olsrd_config($network_setting) {
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
	($network_setting['NetworkSetting']['wan_mesh_gateway'] == TRUE)) 
      {
	$olsrd_dynamic_gateway = 
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
    PlParam \"Ping\"   \"141.1.1.1\"
    PlParam \"Ping\"   \"194.25.2.129\"

    #   First group of HNAs with related ping host
    PlParam\"HNA\"   \"192.168.80.0 255.255.255.0\"
    PlParam\"HNA\"   \"192.168.81.0 255.255.255.0\"
    PlParam\"Ping\" \"192.168.81.12\"

    #   Second HNA group with multiple related ping hosts.
    #   Specifying multiple ping hosts provides redundancy.
    PlParam \"HNA\"    \"192.168.100.0 255.255.255.0\"
    PlParam \"HNA\"    \"192.168.101.0 255.255.255.0\"
    PlParam \"HNA\"    \"192.168.102.0 255.255.255.0\"
    PlParam \"Ping\"   \"192.168.100.10\"
    PlParam \"Ping\"   \"192.168.101.10\"

    #   Third HNA group without ping check
    PlParam \"HNA\"    \"192.168.200.0 255.255.255.0\"
    PlParam \"HNA\"    \"192.168.201.0 255.255.255.0\"
    PlParam \"HNA\"    \"192.168.202.0 255.255.255.0\"
}";
	  
      } else {
      $olsrd_dynamic_gateway = "";
    }
      
    $olsrd_conf_output = str_replace(array('{wifi_ip_address}','{wifi_adapter_name}', '{node_name}', '{olsrd_dynamic_gateway_block}', '{olsrd_secure_block}'), array($network_setting['NetworkSetting']['wifi_ip_address'],$network_setting['NetworkSetting']['wifi_adapter_name'], $network_setting['NetworkSetting']['node_name'], $olsrd_secure_block, $olsrd_dynamic_gateway), $olsrd_conf);

    file_put_contents('/etc/olsrd/olsrd.conf', $olsrd_conf_output);

    $olsrd_key = null;
    if ($network_setting['NetworkSetting']['mesh_olsrd_secure_key'] != null) {
      $olsrd_key = $network_setting['NetworkSetting']['mesh_olsrd_secure_key'];
    } else {
      $olsrd_key = "";
    }
      
    file_put_contents('/etc/olsrd/olsrd.key', $olsrd_key);
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
    $callsign_announcement_output = str_replace(array('{callsign}'), 
						array(str_pad($network_setting['NetworkSetting']['callsign'], 7, ' ', STR_PAD_LEFT)),
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


  private function render_rclocal_config($network_setting) {
    $rclocal_conf = file_get_contents(WWW_ROOT . "/files/rc.local.template");

    if (0 == strcmp($network_setting['NetworkSetting']['wired_interface_mode'], 'WAN')) {
      if ($network_setting['NetworkSetting']['wan_mesh_gateway'] == TRUE) {
	$iptables_gateway_commands = "
iptables -t nat -A POSTROUTING -o ".$network_setting['NetworkSetting']['wired_adapter_name']." -j MASQUERADE
iptables -A FORWARD -i ".$network_setting['NetworkSetting']['wired_adapter_name']." -o ".$network_setting['NetworkSetting']['wifi_adapter_name']." -m conntrack --ctstate RELATED,ESTABLISHED -j ACCEPT
iptables -A FORWARD -i ".$network_setting['NetworkSetting']['wifi_adapter_name']." -o ".$network_setting['NetworkSetting']['wired_adapter_name']." -j ACCEPT
";
      } else {
	$iptables_gateway_commands = "";
      }	
    } else {
      $iptables_gateway_commands = "
# Flush the tables
iptables -F INPUT
iptables -F OUTPUT
iptables -F FORWARD

iptables -t nat -P PREROUTING ACCEPT
iptables -t nat -P POSTROUTING ACCEPT
iptables -t nat -P OUTPUT ACCEPT

# Allow forwarding packets:
iptables -A FORWARD -p ALL -i ".$network_setting['NetworkSetting']['wired_adapter_name']." -j ACCEPT
iptables -A FORWARD -i ".$network_setting['NetworkSetting']['wifi_adapter_name']." -m state --state ESTABLISHED,RELATED -j ACCEPT

# Packet masquerading
iptables -t nat -A POSTROUTING -o ".$network_setting['NetworkSetting']['wifi_adapter_name']." -j SNAT --to-source ".$network_setting['NetworkSetting']['wifi_ip_address'];
    }

    $rclocal_conf_output = str_replace(array('{wifi_adapter_name}','{iptables_gateway_commands}'), array($network_setting['NetworkSetting']['wifi_adapter_name'], $iptables_gateway_commands), $rclocal_conf);

    file_put_contents('/etc/rc.local', $rclocal_conf_output);
  }



  private function render_dnsmasq_config($network_setting) {
    $dnsmasq_conf = file_get_contents(WWW_ROOT . "/files/dnsmasq/hsmm-pi.conf.template");

    $dhcp_interface = null;
    if ($network_setting['NetworkSetting']['lan_dhcp_server'] == TRUE) {
      $dhcp_interface = "interface=".$network_setting['NetworkSetting']['wired_adapter_name'];
    } else {
      $dhcp_interface = "";
    }

    $ip_parts = split("\.", $network_setting['NetworkSetting']['lan_ip_address']);
    $lan_dhcp_start = $ip_parts[0].'.'.$ip_parts[1].'.'.$ip_parts[2].'.'.$network_setting['NetworkSetting']['lan_dhcp_start'];
    $lan_dhcp_end = $ip_parts[0].'.'.$ip_parts[1].'.'.$ip_parts[2].'.'.$network_setting['NetworkSetting']['lan_dhcp_end'];

    $dnsmasq_conf_output = str_replace(array('{interfaces}', '{lan_ip_address}', '{lan_dhcp_start}', '{lan_dhcp_end}', '{lan_netmask}', '{wan_dns1}', '{wan_dns2}'), array($dhcp_interface, $network_setting['NetworkSetting']['lan_ip_address'], $lan_dhcp_start, $lan_dhcp_end, $network_setting['NetworkSetting']['lan_netmask'], $network_setting['NetworkSetting']['wan_dns1'], $network_setting['NetworkSetting']['wan_dns2']), $dnsmasq_conf);

    file_put_contents('/etc/dnsmasq.d/hsmm-pi.conf', $dnsmasq_conf_output);
  }

    
}

?>

