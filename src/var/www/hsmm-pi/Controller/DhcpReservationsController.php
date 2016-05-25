<?php
class DhcpReservationsController extends AppController {
	public $helpers = array('Html', 'Form', 'Session');
	public $components = array('Flash', 'RequestHandler', 'Session');

	public function index() {
		$this->set('dhcp_reservations', $this->DhcpReservation->find('all'));
		$this->load_node_attributes();
	}

	public function delete($id = null) {
		$this->DhcpReservation->id = $id;

		if (!$this->DhcpReservation->exists()) {
			throw new NotFoundException(__('Invalid reservation key'), 'default', array('class' => 'alert alert-danger'));
		}

		if ($this->DhcpReservation->delete($id)) {
			$network_setting = $this->get_network_settings();
			$network_services = $this->get_network_services();
			$dhcp_reservations = $this->get_dhcp_reservations();
			$location = $this->get_location();

			$this->render_olsrd_config($network_setting, $network_services, $dhcp_reservations, $location);
			$this->render_ethers($dhcp_reservations);

			$this->Flash->reboot(__('The service has been deleted, and will take effect on the next reboot.'));
			$this->redirect(array('action' => 'index'));
		}
	}

	public function add($reservation = null) {
		// new reservation from selected lease
		$res_parts = explode("+", $reservation);	// Split out the parts
		$res_parts[2] = str_replace('-', ':', $res_parts[2]);
		$existing = $this->DhcpReservation->find('first', array(
			'conditions' => array('DhcpReservation.ip_address' => $res_parts[1])
			));
		if ($existing != null) {
			$this->Flash->success(__('Existing reservation has been found, deleting.'));

			$this->DhcpReservation->deleteAll(array('DhcpReservation.ip_address' => $res_parts[1]));
		}
		$this->DhcpReservation->create();
		if ($this->DhcpReservation->save(array(
	'hostname' => $res_parts[0],
	'ip_address' => $res_parts[1],
	'mac_address' => $res_parts[2]))) {
			// retrieve other network settings
			$network_setting = $this->get_network_settings();
			$network_services = $this->get_network_services();
			$dhcp_reservations = $this->get_dhcp_reservations();
			$location = $this->get_location();

			$this->render_olsrd_config($network_setting, $network_services, $dhcp_reservations, $location);
			$this->render_ethers($dhcp_reservations);

			$this->Flash->reboot(__('The reservation ' . $res_parts[0] . ' ' . $record_count . ' has been added, and will take effect on the next reboot.'));
			$this->redirect(array('action' => 'index'));
		}
		$this->redirect(array('action' => 'index'));
	}

}
?>
