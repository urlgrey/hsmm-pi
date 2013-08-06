<?php
class LocationSetting extends AppModel {

  public $validate = array(
			   'location_source' => array('required' => array('rule' => '/^(fixed|gps)$/i', 'message' => 'The location source must be set to GPS or a fixed location.')),
			   'lat' => array('required' => array('rule' => '/\d+\.?\d*/', 'message' => 'Latitude must be a decimal value')),
			   'lon' => array('required' => array('rule' => '/\d+\.?\d*/', 'message' => 'Longitude must be a decimal value')),
			   );
}
?>