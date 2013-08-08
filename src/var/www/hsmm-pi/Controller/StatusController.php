<?php
class StatusController extends AppController
{
  public $components = array('RequestHandler');
    
  public function beforeFilter()
  {
    parent::beforeFilter();
    $this->Auth->allow('index');
  }

  public function index()
  {
    $this->set('mesh_links', $this->get_mesh_links());
    $this->set('mesh_services', $this->get_mesh_services());
    $this->set('mesh_node_locations', $this->get_mesh_node_locations());
    $this->load_node_attributes();

    $location = $this->get_location();
    $this->set('maps_api_key', $location['LocationSetting']['maps_api_key']);
  }

  private function get_mesh_links() {
    $ch = curl_init(); 
    curl_setopt($ch, CURLOPT_URL, "http://localhost:9090/links"); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
    $output = curl_exec($ch); 
    curl_close($ch);  
    return json_decode("{".$output, true);
  }


  private function get_mesh_services() {
    $services = array();
    if (file_exists("/var/run/services_olsr")) {
      $handle = @fopen("/var/run/services_olsr", "r");
      if ($handle) {
	while (($buffer = fgets($handle, 1024)) !== false) {
	  if ($buffer != null) {
	    $service_s = trim(substr($buffer, 0, strpos($buffer, '#')));
	    if (strlen($service_s) > 0) {
	      $service_parts = explode('|', $service_s);
	      if (sizeof($service_parts) > 0) {
		$services[] = $service_parts;
	      }
	    }
	  }	
	}
	fclose($handle);
      }
    }

    return $services;
  }


  private function get_mesh_node_locations() {
    $locations = array();
    if (file_exists("/var/run/latlon.js")) {
      $handle = @fopen("/var/run/latlon.js", "r");
      if ($handle) {
	while (($buffer = fgets($handle, 1024)) !== false) {
	  if ($buffer != null) {
	    if ((false != strstr($buffer, 'Self(')) || (false != strstr($buffer, 'Node('))) {
	      // found a line with coordinates, handle it, and remove apostrophes
	      $trimmed_node_str = str_replace('\'', '', substr($buffer, 5, -3));
	      $location_parts = explode(',', $trimmed_node_str);
	      if (sizeof($location_parts) > 0 &&
		  !((floatval($location_parts[1]) == 0.0) && (floatval($location_parts[2] == 0.0)))
		  ) {
		// set the lat/long in the returned array using the host IP for the array index
		$locations[$location_parts[0]] = array('lat'=>$location_parts[1], 'lon'=>$location_parts[2]);
	      }	      
	    }
	  }
	}
	fclose($handle);
      }
    }
   
    return $locations;
  }
}

?>

