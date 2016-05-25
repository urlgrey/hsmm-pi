<!-- File: /app/View/DhcpReservations/index.ctp -->
<div class="page-header">
  <h1>DHCP Reservations</h1>
</div>

<div class="row">
  <div class="col-md-12">
      <?php
if ($dhcp_reservations != NULL && sizeof($dhcp_reservations) > 0) {
	?>
      <table class="table table-striped table-bordered">
	<tr>
	  <th>Hostame</th>
	  <th>IP Address</th>
	  <th>MAC Address</th>
	  <th>Actions</th>
	</tr>
	<?php
foreach ($dhcp_reservations as $dhcp_reservation) {
		?>
	<tr>
	  <td><?php echo $dhcp_reservation['DhcpReservation']['hostname'];?></td>
	  <td><?php echo $dhcp_reservation['DhcpReservation']['ip_address'];?></td>
	  <td><?php echo $dhcp_reservation['DhcpReservation']['mac_address'];?></td>
	  <td>
	        <?php
echo $this->Html->link('', array(
			'action' => 'delete',
			$dhcp_reservation['DhcpReservation']['id'],
		),
			array('class' => 'glyphicon glyphicon-trash'));
		?>
	  </td>
	</tr>
	<?php
}
	?>
</table>
</div>
</div>
<?php
} else {
	?>
      <div class="alert alert-info">
      	   No addresses have been reserved.
      </div>
      <?php 
	}
?>
<div>
  <h2>DHCP Leases</h2>
</div>

<?php
	$hostnames = array();
	$ip_addresses = array();
	$mac_addresses = array();
	$lease_count = 0;
        $lease_parts = null;
        // Load leases file
        $leases_handle = fopen("/var/lib/misc/dnsmasq.leases", "r");
        if ($leases_handle) {
                // Scan for leases
                while (($leases_line = fgets($leases_handle, 4096)) !== false) {
                        if ($leases_line[0]  == '#') continue;
                        $lease_parts = explode(" ", $leases_line);
			if (count($lease_parts) == 5) { // Valid lease line
				$hostnames[$lease_count] = $lease_parts[3];
				$ip_addresses[$lease_count] = $lease_parts[2];
				$mac_addresses[$lease_count] = $lease_parts[1];
				$lease_count++;
			}
                }
//		if (!feof($leasess_handle)) {
//		}
	       	fclose($leases_handle);
	}
	if ($lease_count > 0) {
?>
<div class="row">
  <div class="col-md-12">
      <table class="table table-striped table-borderless">
        <tr>
          <th>Hostname</th>
          <th>IP Address</th>
          <th>MAC Address</th>
          <th>Actions</th>
        </tr>

<?php
for ($i = 0; $i < $lease_count; $i++) {
?>
	<tr>
	  <td><?php echo $hostnames[$i];?></td>
	  <td><?php echo $ip_addresses[$i];?></td>
	  <td><?php echo $mac_addresses[$i];?></td>
	  <td>
	        <?php
		$mac = explode(":", $mac_addresses[$i]);
		$lease = $hostnames[$i] . '+' . $ip_addresses[$i] . '+' . 
			$mac[0] . '-' . $mac[1] . '-' . $mac[2] . '-' . $mac[3] . '-' . $mac[4] . '-' . $mac[5];
echo $this->Html->link('', array(
			'action' => 'add',
			$lease,
			),
			array('class' => 'glyphicon glyphicon-star'));
		?>
	</td>
	</tr>
<?php
}
?>
     </table>

<script>
function getRow(x) {
	return x.rowIndex;
}
</script>

  </div>
</div>
<?php
} else {
	?>
      <div class="alert alert-info">
      	   No addresses have been leased.
      </div>
      <?php 
	}
?>
