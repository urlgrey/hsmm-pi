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
  function show_lan_mode(e) {
    if (e.value == "NAT") {
      document.getElementById('nat').style.display = "block";
      document.getElementById('direct').style.display = "none";
    } else if (e.value == "Direct") {
      document.getElementById('direct').style.display = "block";
      document.getElementById('nat').style.display = "none";
    }
  }
</script>

<?php
echo $this->Form->create('NetworkSetting', array(
  'inputDefaults' => array(
    'div' => 'form-group',
    'label' => array('class' => 'col col-md-3 control-label'),
    'class' => 'form-control'),
	'url' => array('controller' => 'network_settings', 'action' => 'edit')));
echo $this->Form->input('id', array(
	'type' => 'hidden',
));

?>
<span class="pull-right">
<?php echo $this->Form->submit(__('Save'), array('name' => 'submit', 'div' => false, 'class' => 'btn btn-primary'));?>
</span>
<p></p>

<div class="tabbable"> <!-- Only required for left/right tabs -->
  <ul class="nav nav-tabs">
    <li class="active"><a href="#wifi" data-toggle="tab"><?php echo __('WiFi');?></a></li>
    <li><a href="#wired" data-toggle="tab"><?php echo __('Wired');?></a></li>
    <li><a href="#mesh" data-toggle="tab"><?php echo __('Mesh');?></a></li>
    <li><a href="#time" data-toggle="tab"><?php echo __('Time');?></a></li>
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
		'options' => array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9, 10 => 10, 11 => 11),
	));

?>
    </div>
    <div class="tab-pane" id="wired">
      <?php
echo $this->Form->input('wired_adapter_name', array('label' => __('Adapter Name')));
echo $this->Form->input('wired_interface_mode',
	array(
		'label' => __('Wired interface mode'),
		'options' => array('LAN' => 'LAN', 'WAN' => 'WAN'),
		'onchange' => 'show_wired_mode(this)',
	)
);
?>
      <div id="lan" style="padding: 0; display: <?php echo (0 == strcmp($wired_interface_mode, 'LAN')) ? 'block' : 'none';?>;">
      <?php
echo $this->Form->input('lan_mode',
	array(
		'label' => __('LAN Mode'),
		'options' => array('NAT' => 'NAT', 'Direct' => 'Direct'),
		'onchange' => 'show_lan_mode(this)',
	)
);
?>
      <div id="nat" style="padding: 0; display: <?php echo (0 == strcmp($lan_mode, 'NAT')) ? 'block' : 'none';?>;">
<?php
echo $this->Form->input('lan_ip_address', array('label' => __('IP Address')));
echo $this->Form->input('lan_netmask', array('label' => __('Netmask')));

echo $this->Form->input('lan_dhcp_server', array('label' => __('DHCP Server'), 'type' => 'checkbox'));

echo $this->Form->input('lan_dhcp_start', array('label' => __('DHCP Start')));
echo $this->Form->input('lan_dhcp_end', array('label' => __('DHCP End')));
?>
</div>
      <div id="direct" style="padding: 0; display: <?php echo (0 == strcmp($lan_mode, 'Direct')) ? 'block' : 'none';?>;">
<?php
echo $this->Form->input('direct_ip_address', array('label' => __('IP Address')));
echo $this->Form->input('direct_netmask', array('label' => __('Netmask')));

echo $this->Form->input('lan_dhcp_server', array('label' => __('DHCP Server'), 'type' => 'checkbox'));

echo $this->Form->input('direct_dhcp_start', array('label' => __('DHCP Start')));
echo $this->Form->input('direct_dhcp_end', array('label' => __('DHCP End')));
?>
</div>
      </div>
      <div id="wan" style="padding: 0; display: <?php echo (0 == strcmp($wired_interface_mode, 'WAN')) ? 'block' : 'none';?>;">
      <?php
echo $this->Form->input('wan_protocol',
	array(
		'label' => __('Protocol'),
		'options' => array('DHCP' => 'DHCP'),
	)
);
echo $this->Form->input('wan_dns1', array('label' => __('DNS 1')));
echo $this->Form->input('wan_dns2', array('label' => __('DNS 2')));
echo $this->Form->input('wan_fixed_connection', array('label' => __('WAN port is always on (periodic testing of Internet connectivity is unnecessary)'), 'type' => 'checkbox'));
?>
      </div>
    </div>
    <div class="tab-pane" id="mesh">
      <?php
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
  </div>
</div>
<?php
echo $this->Form->end();
?>
