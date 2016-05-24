<?php
class DhcpReservationsController extends AppController {
	public $helpers = array('Html', 'Form', 'Session');
	public $components = array('Flash', 'RequestHandler', 'Session');

	public function index() {
		$this->set('dhcp_reservations', $this->DhcpReservation->find('all'));
		$this->load_node_attributes();
	}

	public function delete($id = null) {
		$this->DHCPReservation->id = $id;

		if (!$this->DHCPReservation->exists()) {
			throw new NotFoundException(__('Invalid reservation key'), 'default', array('class' => 'alert alert-danger'));
		}

		if ($this->DHCPReservation->delete()) {
			$network_setting = $this->get_network_settings();
			$dhcp_reservations = $this->get_dhcp_reservations();
			$location = $this->get_location();

			$this->render_olsrd_config($network_setting, $network_services, $location);
			$this->render_rclocal_config($network_setting, $network_services);

			$this->Flash->reboot(__('The service has been deleted, and will take effect on the next reboot.'));
			$this->redirect(array('action' => 'index'));
		}
	}

	public function add() {
		if ($this->request->is('post')) {

			$this->DhcpReservation->create();
			if ($this->DhcpReservation->save($this->request->data)) {
				// retrieve other network settings
				$network_setting = $this->get_network_settings();
				$dhcp_reservations = $this->get_dhcp_reservations();
				$location = $this->get_location();

				$this->render_olsrd_config($network_setting, $network_services, $location);
				$this->render_etc_ethers($dhcp_reservations);

				$this->Flash->reboot(__('The reservation has been added, and will take effect on the next reboot.'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Flash->error(__('Unable to add your reservation.'));
			}
		}
	}
}
?>
