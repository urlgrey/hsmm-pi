<!-- File: /app/View/Status/index.ctp -->
<div class="page-header">
  <p><h1>Status <small><?php echo $node_name; ?></small></h1></p>
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
          <th>Hostname</th>
	  <th>IP Address</th>
	  <th>Link Quality</th>
	</tr>
	<?php
	   foreach ($mesh_links['links'] as $node) {
	   ?> 
	<tr>
          <td><?php echo gethostbyaddr($node['remoteIP']); ?>
	   <?php  
	   if (array_key_exists($node['remoteIP'], $mesh_node_locations)) {
	     $location = $mesh_node_locations[$node['remoteIP']];
	     if ($location != NULL) {
	       echo "&nbsp;<a href=\"#mapModal\" role=\"button\" class=\"icon-globe\" data-toggle=\"modal\"></a>";
	     }
	   }
	   ?>
	  </td>
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

<!-- Modal -->
<div id="mapModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3 id="myModalLabel">Node Location Map</h3>
  </div>
  <div class="modal-body">
    <p>Work in progress</p>
  </div>
  <div class="modal-footer">
    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
  </div>
</div>
