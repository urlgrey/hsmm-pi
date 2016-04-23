<!-- File: /app/View/NetworkServices/index.ctp -->
<div class="page-header">
  <h1>Network Services</h1>
</div>

<div class="row">
  <div class="col-md-12">
    <div class="pull-right">
      <p>
	<?php
echo $this->Html->link(__('Add'), array(
	'controller' => 'network_services',
	'action' => 'add'), array(
	'class' => 'btn btn-primary'));
?>
      </p>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-12">
      <?php
if ($services != NULL && sizeof($services) > 0) {
	?>
      <table class="table table-striped table-bordered">
	<tr>
	  <th>Name</th>
	  <th>Host</th>
	  <th>Port</th>
	  <th>Protocol</th>
	  <th>Local Port</th>
	  <th>Service URL</th>
	  <th>Actions</th>
	</tr>
	<?php
foreach ($services as $service) {
		?>
	<tr>
	  <td><?php echo $service['NetworkService']['name'];?></td>
	  <td><?php echo $service['NetworkService']['host'];?></td>
	  <td><?php echo $service['NetworkService']['port'];?></td>
	  <td><?php echo $service['NetworkService']['protocol'];?></td>
	  <td><?php echo $service['NetworkService']['local_port'];?></td>
	  <td><a href="<?php echo $service['NetworkService']['service_protocol_name'] . "://" . $node_name . ":" . $service['NetworkService']['port'] . '/' . $service['NetworkService']['path'];?>"><?php echo $service['NetworkService']['service_protocol_name'] . "://" . $node_name . ":" . $service['NetworkService']['port'] . '/' . $service['NetworkService']['path'];?></a></td>
	  <td>
	        <?php
echo $this->Html->link('', array(
			'action' => 'delete',
			$service['NetworkService']['id'],
		),
			array('class' => 'glyphicon glyphicon-trash'));
		?>
	  </td>
	</tr>
	<?php
}
	?>
      </table>
      <?php
} else {
	?>
      <div class="alert alert-info">
      	   No services have been defined.
      </div>
      <?php }
?>
  </div>
</div>
