<?php
class LocationSetting extends AppModel {

	public $validate = array(
		'location_source' => array('required' => array('rule' => '/^(fixed|gps)$/i', 'message' => 'The location source must be set to GPS or a fixed location.')),
		'lat' => array('rule' => array('decimal')),
		'lon' => array('rule' => array('decimal')),
	);
}
?>