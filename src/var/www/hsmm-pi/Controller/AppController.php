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

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package		app.Controller
 * @link		http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{
    
    public $components = array('Session', 'Auth' => array('loginRedirect' => array('controller' => 'status', 'action' => 'index'), 'logoutRedirect' => array('controller' => 'status', 'action' => 'index')));

    protected function render_rclocal_config($network_setting, $network_services) {
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


    protected function render_olsrd_config($network_setting, $network_services) {
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
	$olsrd_network_services = "";
      } else {
      $olsrd_dynamic_gateway = "";
      $olsrd_network_services = "";
      if ($network_services != NULL && sizeof($network_services) > 0) {
	foreach($network_services as $service) {
 	  $olsrd_network_services += "
    PlParam \"service\" \"".$service['NetworkService']['service_protocol_name']."://".$network_setting['NetworkSetting']['node_name'].".local.mesh:".$service['NetworkService']['port']."|".$service['NetworkService']['protocol']."|".$service['NetworkService']['name']."\"
";
	  
	}
      }
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

}
?>
