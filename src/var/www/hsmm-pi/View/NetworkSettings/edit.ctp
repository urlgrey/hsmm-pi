<!-- File: /app/View/NetworkSettings/index.ctp -->
<div class="page-header">
  <h1>Network Settings</h1>
</div>

<script type="text/javascript">
  function show_wired_mode(e) {
    if (e.value == "LAN") {
      document.getElementById('lan').style.display = "block";
      document.getElementById('wan').style.display = "none";
    } else if (e.value == "WAN") {
      document.getElementById('wan').style.display = "block";
      document.getElementById('lan').style.display = "none";
    }
  }
</script>

<script type="text/javascript">
  function show_wan_mode(e) {
    if (e.value == "STATIC") {
      document.getElementById('wan_static').style.display = "block";
      document.getElementById('wan_dhcp').style.display = "none";
    } else if (e.value == "DHCP") {
      document.getElementById('wan_dhcp').style.display = "block";
      document.getElementById('wan_static').style.display = "none";
    }
  }
</script>


<?php
echo $this->Form->create('NetworkSetting', array(
					  'url' => array('controller' => 'network_settings', 'action' => 'edit')));
echo $this->Form->input('id', array(
    'type' => 'hidden'
));

?>
<span class="pull-right">
<?php echo $this->Form->submit(__('Save'), array('name' => 'submit', 'div' => false, 'class' => 'btn btn-primary')); ?>
</span>
<p></p>

<div class="tabbable"> <!-- Only required for left/right tabs -->
  <ul class="nav nav-tabs">
    <li class="active""><a href="#wifi" data-toggle="tab"><?php echo __('WiFi'); ?></a></li>
    <li><a href="#wired" data-toggle="tab"><?php echo __('Wired'); ?></a></li>
    <li><a href="#mesh" data-toggle="tab"><?php echo __('Mesh'); ?></a></li>
    <li><a href="#time" data-toggle="tab"><?php echo __('Time'); ?></a></li>
    <li><a href="#vpn_client" data-toggle="tab"><?php echo __('VPN Client'); ?></a></li>
	<li><a href="#vpn_server" data-toggle="tab"><?php echo __('VPN Server'); ?></a></li>
	<li><a href="#vpn_server_settings" data-toggle="tab"><?php echo __('VPN Server Settings'); ?></a></li>
	
	</ul>
  <div class="tab-content">
    <div class="tab-pane active" id="wifi">
      <?php
echo $this->Form->input('wifi_adapter_name', array('label' => __('Adapter Name')));
echo $this->Form->input('wifi_ip_address', array('label' => __('IP Address')));
echo $this->Form->input('wifi_netmask', array('label' => __('Netmask')));
echo $this->Form->input('wifi_ssid', array('label' => __('SSID')));
echo $this->Form->input('wifi_channel', 
			array(
			      'label' => __('Channel'),
			      'options' => array(1=>1,2=>2,3=>3,4=>4,5=>5,6=>6,7=>7,8=>8,9=>9,10=>10,11=>11),
			      ));

      ?>
    </div>
    <div class="tab-pane" id="wired">
      <?php
echo $this->Form->input('wired_adapter_name', array('label' => __('Adapter Name')));
echo $this->Form->input('wired_interface_mode', 
			array(
			      'label' => __('Wired interface mode'),
			      'options' => array('LAN'=>'LAN','WAN'=>'WAN'),
                              'onchange' => 'show_wired_mode(this)',
			      )
			);
      ?>
      <span id="lan" style="display: <?php echo (0 == strcmp($wired_interface_mode, 'LAN')) ? 'block' : 'none'; ?>;">
      <?php
echo $this->Form->input('lan_mode', 
			array(
			      'label' => __('LAN Mode'),
			      'options' => array('NAT'=>'NAT'),
			      )
			);
echo $this->Form->input('lan_ip_address', array('label' => __('IP Address')));
echo $this->Form->input('lan_netmask', array('label' => __('Netmask')));

echo $this->Form->input('lan_dhcp_server', array('label' => __('DHCP Server'), 'type' => 'checkbox'));

echo $this->Form->input('lan_dhcp_start', array('label' => __('DHCP Start')));
echo $this->Form->input('lan_dhcp_end', array('label' => __('DHCP End')));
      ?>
      </span>
      <span id="wan" style="display: <?php echo (0 == strcmp($wired_interface_mode, 'WAN')) ? 'block' : 'none'; ?>;">
      <?php
echo $this->Form->input('wan_protocol', 
			array(
			      'label' => __('Protocol'),
			      'options' => array('DHCP'=>'DHCP','STATIC'=>'STATIC',),
				  'onchange' => 'show_wan_mode(this)',
			      )
			);
			?>
			<span id="wan_dhcp" style="display: <?php echo  (0 == strcmp($wan_protocol, 'DHCP')) ? 'block' : 'none'; ?>;">
			<?php
echo $this->Form->input('wan_dns1', array('label' => __('DNS 1')));
echo $this->Form->input('wan_dns2', array('label' => __('DNS 2')));
echo $this->Form->input('wan_fixed_connection', array('label' => __('WAN port is always on (periodic testing of Internet connectivity is unnecessary)'), 'type' => 'checkbox'));
      ?>
	  </span>
	  		<span id="wan_static" style="display: <?php echo  (0 == strcmp($wan_protocol, 'STATIC')) ? 'block' : 'none'; ?>;">
			<?php
echo $this->Form->input('wan_static_ip', array('label' => __('Wan IP')));
echo $this->Form->input('wan_subnet_mask', array('label' => __('Wan Subnet Mask')));
echo $this->Form->input('wan_gateway', array('label' => __('Wan IP Gateway')));
echo $this->Form->input('wan_dns1', array('label' => __('Wan DNS 1')));
echo $this->Form->input('wan_dns2', array('label' => __('Wan DNS 2')));

      ?>
	  
      </span>
    </div>
    <div class="tab-pane" id="mesh">
      <?php
echo $this->Form->input('callsign', array('label' => __('Amateur Radio Callsign')));
echo $this->Form->input('node_name', array('label' => __('Node Name')));
echo $this->Form->input('mesh_olsrd_secure', array('label' => __('OLSRD Secure'), 'type' => 'checkbox'));
echo $this->Form->input('mesh_olsrd_secure_key', array('label' => __('OLSRD Secure Key')));
      ?>
    </div>
    <div class="tab-pane" id="time">
      <?php
echo $this->Form->input('ntp_server', array('label' => __('Network Time Server')));
      ?>
     </div>                               
     <div class="tab-pane" id="vpn_client">
      <?php
echo $this->Form->input('server_dns', array('label' => __('Server DNS Name')));
echo $this->Form->input('server_password', array('label' => __('Server Password')));
echo $this->Form->input('client_ip', array('label' => __('Client VPN IP')));
echo $this->Form->input('server_ip', array('label' => __('Server VPN IP')));
      ?>
     </div>                               
     <div class="tab-pane active" id="vpn_server">
      <?php
echo $this->Form->input('client_one_callsign', array('label' => __('Client 1 Callsign')));
echo $this->Form->input('client_one_password', array('label' => __('Client 1 Password')));
echo $this->Form->input('client_one_server_ip', array('label' => __('Client 1 Server IP')));
echo $this->Form->input('client_one_ip', array('label' => __('Client 1 IP VPN IP Address')));
echo $this->Form->input('client_two_callsign', array('label' => __('Client 2 Callsign')));
echo $this->Form->input('client_two_password', array('label' => __('Client 2 Password')));
echo $this->Form->input('client_two_server_ip', array('label' => __('Client 2 Server IP')));
echo $this->Form->input('client_two_ip', array('label' => __('Client 2 IP VPN IP Address')));
echo $this->Form->input('client_three_callsign', array('label' => __('Client 3 Callsign')));
echo $this->Form->input('client_three_password', array('label' => __('Client 3 Password')));
echo $this->Form->input('client_three_server_ip', array('label' => __('Client 3 Server IP')));
echo $this->Form->input('client_three_ip', array('label' => __('Client 3 IP VPN IP Address')));
echo $this->Form->input('client_four_callsign', array('label' => __('Client 4 Callsign')));
echo $this->Form->input('client_four_password', array('label' => __('Client 4 Password')));
echo $this->Form->input('client_four_server_ip', array('label' => __('Client 4 Server IP')));
echo $this->Form->input('client_four_ip', array('label' => __('Client 4 IP VPN IP Address')));
echo $this->Form->input('client_five_callsign', array('label' => __('Client 5 Callsign')));
echo $this->Form->input('client_five_password', array('label' => __('Client 5 Password')));
echo $this->Form->input('client_five_server_ip', array('label' => __('Client 5 Server IP')));
echo $this->Form->input('client_five_ip', array('label' => __('Client 5 IP VPN IP Address')));
echo $this->Form->input('client_six_callsign', array('label' => __('Client 6 Callsign')));
echo $this->Form->input('client_six_password', array('label' => __('Client 6 Password')));
echo $this->Form->input('client_six_server_ip', array('label' => __('Client 6 Server IP')));
echo $this->Form->input('client_six_ip', array('label' => __('Client 6 IP VPN IP Address')));
echo $this->Form->input('client_seven_callsign', array('label' => __('Client 7 Callsign')));
echo $this->Form->input('client_seven_password', array('label' => __('Client 7 Password')));
echo $this->Form->input('client_seven_server_ip', array('label' => __('Client 7 Server IP')));
echo $this->Form->input('client_seven_ip', array('label' => __('Client 7 IP VPN IP Address')));
echo $this->Form->input('client_eight_callsign', array('label' => __('Client 8 Callsign')));
echo $this->Form->input('client_eight_password', array('label' => __('Client 8 Password')));
echo $this->Form->input('client_eight_server_ip', array('label' => __('Client 8 Server IP')));
echo $this->Form->input('client_eight_ip', array('label' => __('Client 8 IP VPN IP Address')));
echo $this->Form->input('client_nine_callsign', array('label' => __('Client 9 Callsign')));
echo $this->Form->input('client_nine_password', array('label' => __('Client 9 Password')));
echo $this->Form->input('client_nine_server_ip', array('label' => __('Client 9 Server IP')));
echo $this->Form->input('client_nine_ip', array('label' => __('Client 9 IP VPN IP Address')));
echo $this->Form->input('client_ten_callsign', array('label' => __('Client 10 Callsign')));
echo $this->Form->input('client_ten_password', array('label' => __('Client 10 Password')));
echo $this->Form->input('client_ten_server_ip', array('label' => __('Client 10 Server IP')));
echo $this->Form->input('client_ten_ip', array('label' => __('Client 10 IP VPN IP Address')));
      ?>                                     
    </div>
	<div class="tab-pane" id="vpn_server_settings">
      <?php
echo $this->Form->input('server_port', array('label' => __('VPN Server Port ')));
      ?>
     </div>                 
    <?php
echo $this->Form->end();
    ?>
  </div>
</div>
