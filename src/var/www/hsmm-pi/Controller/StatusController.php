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
    $this->load_node_attributes();
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

    return $services;
  }
}

?>

