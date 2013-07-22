<!-- File: /app/View/ShellUserKeys/index.ctp -->
<h1>SSH User Keys</h1>

<?php
echo $this->Html->link(__('Add Key'), 
		       array(
			     'action' => 'add'
			     ),
		       array('class' => 'btn btn-primary'));
?>
<p></p>

<table class="table table-striped table-bordered">
  <tr>
    <th>Id</th>
    <th>Name</th>
    <th>Action</th>
    <th>Created</th>
    <th>Updated</th>
  </tr>
  <!-- Here is where we loop through our $shell_user_keys array, printing out shell_user_key info -->
  <?php
     foreach ($shell_user_keys as $shell_user_key):
     ?> <tr>
    <td><?php
	   echo $shell_user_key['ShellUserKey']['id'];
	   ?></td> <td>
      <?php
	 echo $this->Html->link($shell_user_key['ShellUserKey']['name'], array(
      'controller' => 'shell_user_keys',
      'action' => 'view',
      $shell_user_key['ShellUserKey']['id']
      ));
      ?>
    </td>
    <td><?php
	   echo $this->Html->link('', array(
      'action' => 'edit',
      $shell_user_key['ShellUserKey']['id']
      ),
      array('class' => 'icon-pencil'));
      ?>&nbsp;
      <?php
	   echo $this->Html->link('', array(
      'action' => 'delete',
      $shell_user_key['ShellUserKey']['id']
      ),
      array('class' => 'icon-trash'));
      ?>
    </td>
    <td><?php
	   echo $shell_user_key['ShellUserKey']['created'];
	   ?></td>
    <td><?php
	   echo $shell_user_key['ShellUserKey']['updated'];
	   ?></td></tr>
  <?php
     endforeach;
     ?>
  <?php
     unset($shell_user_key);
     ?>
</table>

