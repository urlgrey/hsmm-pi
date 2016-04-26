<!-- File: /app/View/NetworkSettings/index.ctp -->
<div class="page-header">
  <h1>Location Settings</h1>
</div>

<script type="text/javascript">
  function show_location_source(e) {
    if (e.value == "fixed") {
      document.getElementById('fixed').style.display = "block";
      document.getElementById('gps').style.display = "none";
    } else if (e.value == "gps") {
      document.getElementById('gps').style.display = "block";
      document.getElementById('fixed').style.display = "none";
    }
  }
</script>

<?php
echo $this->Form->create('LocationSetting', array(
  'inputDefaults' => array(
    'div' => 'form-group',
    'label' => array('class' => 'col col-md-3 control-label'),
    'class' => 'form-control'),
	'url' => array('controller' => 'location_settings', 'action' => 'edit')));
echo $this->Form->input('id', array(
	'type' => 'hidden',
));

?>
<span class="pull-right">
<?php echo $this->Form->submit(__('Save'), array('name' => 'submit', 'div' => false, 'class' => 'btn btn-primary'));?>
</span>
<p></p>

<?php
echo $this->Form->input('maps_api_key', array('label' => __('Bing Maps API Key (optional)')));
?>

<?php
echo $this->Form->input('transmit_location_enabled', array('label' => __('Transmit Location in Mesh'), 'type' => 'checkbox'));
echo $this->Form->input('location_source',
	array(
		'label' => __('Location Data Source'),
		'options' => array('fixed' => 'Fixed', 'gps' => 'GPS'),
		'onchange' => 'show_location_source(this)',
	)
);
?>
  <div id="fixed" style="padding: 0; display: <?php echo (0 == strcmp($location_source, 'fixed')) ? 'block' : 'none';?>;">
    <?php
echo $this->Form->input('lat', array('label' => __('Latitude')));
echo $this->Form->input('lon', array('label' => __('Longitude')));
?>
  </div>
  <div id="gps" style="padding: 0; display: <?php echo (0 == strcmp($location_source, 'gps')) ? 'block' : 'none';?>;">
    <?php
echo $this->Form->input('gps_device_name', array('label' => __('GPS Device Name')));
?>
  </div>
<?php
echo $this->Form->end();
?>
