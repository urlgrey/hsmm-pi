<!-- File: /app/View/Status/index.ctp -->
<div class="page-header">
  <p><h1>Backup</h1></p>
</div>

<h3>Download Backup</h3>
<p>
<?php
echo $this->Html->link(__('Download'), array(
	'controller' => 'backup',
	'action' => 'get'), array(
	'class' => 'btn btn-primary'));
?>
</p>
<p></p>
<p>

<h3>Restore Backup</h3>
<form action="backup/edit" method="POST" enctype="multipart/form-data">
<div class="fileupload fileupload-new" data-provides="fileupload">
  <div class="input-append">
    <div class="uneditable-input span3"><i class="icon-file fileupload-exists"></i> <span class="fileupload-preview"></span></div><span class="btn btn-file"><span class="fileupload-new">Select file</span><span class="fileupload-exists">Change</span><input type="file" /></span><a href="#" class="btn fileupload-exists" data-dismiss="fileupload">Remove</a>
  </div>
</div>

<?php
echo $this->Form->submit(__('Save'), array('name' => 'submit', 'div' => false, 'class' => 'btn btn-primary'));
?>
</form>

</p>
