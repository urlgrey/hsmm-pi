<!-- File: /app/View/NetworkServices/index.ctp -->
<p><h1>Network Services</h1></p>

<div class="row">
  <div class="span9">
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

<div class="row">
  <div class="span8">
      <?php
	 if ($services != NULL && sizeof($services) > 0) {
      ?>
      <table class="table table-striped table-bordered">
	<tr>
	  <th>Name</th>
	  <th>Hostname</th>
	  <th>Port</th>
	  <th>Protocol</th>
	  <th>Forwarding Port</th>
	  <th>Actions</th>
	</tr>
	<?php
	   foreach ($services as $service) {
	   ?> 
	<tr>
	  <td><?php echo $service['NetworkService']['name']; ?></td>
	  <td><?php echo $service['NetworkService']['hostname']; ?></td>
	  <td><?php echo $service['NetworkService']['port']; ?></td>
	  <td><?php echo $service['NetworkService']['protocol']; ?></td>
	  <td><?php echo $service['NetworkService']['forwarding_port']; ?></td>
	  <td>
	        <?php
	  	 echo $this->Html->link('', array(
		       'action' => 'delete',
		       $service['NetworkService']['id']
		       ),
		       array('class' => 'icon-trash'));
		?>
          </td>
	  </div></td>
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
      <?php } ?>
    </div>
  </div>
</div>
