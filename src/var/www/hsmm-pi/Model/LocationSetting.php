<?php
class LocationSetting extends AppModel {

  public $validate = array(
			   'maps_api_key' => array('rule' => array('maxlength', 256), 'message' => 'The maps API key length must not exceed 256 characters.'),
			   'location_source' => array('required' => array('rule' => '/^(fixed|gps)$/i', 'message' => 'The location source must be set to GPS or a fixed location.')),
			   'lat' => array('rule' => array('decimal')),
			   'lon' => array('rule' => array('decimal')),
			   );
}
?>