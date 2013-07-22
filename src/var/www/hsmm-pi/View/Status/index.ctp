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
  <div class="span12">
    <div class="well">
      <p><h3>Neighboring Mesh Nodes</h3></p>
      
      <?php
	 if (sizeof($mesh_links) > 0) {
      ?>
      <table class="table table-striped table-bordered">
	<tr>
	  <th>IP Address</th>
	  <th>Link Quality</th>
	</tr>
	<?php
	   foreach ($mesh_links as $node) {
	   ?> 
	<tr>
	  <td><?php echo $node[1]; ?></td>
	  <td>
	    <div class="progress"><div class="bar" style="width: <?php echo round($node[3] * 100).'%'; ?>;"><?php echo round($node[3] * 100).'%'; ?></div>
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
  </div>
  <div class="row">
    <div class="span12">
      <div class="well">
	<p><h3>Network Interfaces</h3></p>
	<pre><?php echo $network_interfaces; ?></pre>
      </div>
    </div>
   </div>
  </div>
</div>
