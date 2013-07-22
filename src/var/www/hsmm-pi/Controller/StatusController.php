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
    $this->set('network_interfaces', $this->get_network_interfaces());
  }

  private function get_network_interfaces() {
    $netstat_output = shell_exec('/sbin/ifconfig -a');
    return $netstat_output;
  }

  private function get_mesh_links() {
    // create curl resource 
    $ch = curl_init(); 

    // set url 
    curl_setopt($ch, CURLOPT_URL, "http://localhost:2006/links"); 

    //return the transfer as a string 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 

    // $output contains the output string 
    $output = curl_exec($ch); 

    // close curl resource to free up system resources 
    curl_close($ch);  

    $links = array();
    $ready_to_parse = FALSE;
    foreach(preg_split("/((\r?\n)|(\r\n?))/", $output) as $line){
      if (strlen(trim($line)) == 0) {
	break;
      }

      $line_parts = explode("\t", $line);
      if (1 >= count($line_parts)) {
	continue;
      }

      if (0 == strcmp($line_parts[0], "Local IP")) {
	$ready_to_parse = TRUE;
	continue;
      } 

      if ($ready_to_parse == TRUE) {
	array_push($links, $line_parts);
      }
    } 

    return $links;
  }
}

?>

