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
    curl_setopt($ch, CURLOPT_URL, "http://localhost:9090/links"); 

    //return the transfer as a string 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 

    // $output contains the output string 
    $output = curl_exec($ch); 

    // close curl resource to free up system resources 
    curl_close($ch);  

    return json_decode($output);
  }
}

?>

