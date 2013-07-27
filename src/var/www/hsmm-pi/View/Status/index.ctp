<!-- File: /app/View/Status/index.ctp -->
<p><h1>Status</h1></p>

<div class="row">
  <div class="span9">
    <p>
      <?php
	 echo $this->Html->link(__('Refresh'), array(
      'controller' => 'status',
      'action' => 'index'), array(
      'class' => 'btn btn-primary'));
      ?>
    </p>
  </div>
</div>

<div class="row">
  <div class="span8">
    <div class="well">
      <p><h3>Neighboring Mesh Nodes</h3></p>
      
      <?php
	 if ($mesh_links != NULL && sizeof($mesh_links['links']) > 0) {
      ?>
      <table class="table table-striped table-bordered">
	<tr>
	  <th>IP Address</th>
	  <th>Link Quality</th>
	</tr>
	<?php
	   foreach ($mesh_links['links'] as $node) {
	   ?> 
	<tr>
	  <td><?php echo $node['remoteIP']; ?></td>
	  <td>
	    <div class="progress"><div class="bar" style="width: <?php echo round($node['linkQuality'] * 100).'%'; ?>;"><?php echo round($node['linkQuality'] * 100).'%'; ?></div>
	  </div></td>
	</tr>
	<?php 
	   }
	   ?>
      </table>
      <?php
	 } else {
	 ?>
      <div class="alert alert-error">
	<strong>Warning!</strong>.  There are no mesh nodes currently in range.  It's a bit quiet around here.
      </div>
      <?php } ?>
    </div>
  </div>
  <div class="span4">
    <div class="well">
      <p><h3>Mesh Services</h3></p>
      
      <?php
	 if ($mesh_services != NULL && sizeof($mesh_services) > 0) {
      ?>
      <table class="table table-striped table-bordered">
	<tr>
	  <th>Service</th>
	</tr>
	<?php
	   foreach ($mesh_services as $service) {
	   ?> 
	<tr>
	  <td><a href="<?php echo $service[0]; ?>"><?php echo $service[2]; ?></a></td>
	</tr>
	<?php 
	   }
	   ?>
      </table>
      <?php
	 } else {
	 ?>
      <div class="alert alert-info">
	There are no mesh services being announced at this time.
      </div>
      <?php } ?>
      </div>
    </div>
  </div>
</div>
