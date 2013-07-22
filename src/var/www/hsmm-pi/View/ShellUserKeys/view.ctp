<!-- File: /app/View/ShellUserKeys/view.ctp -->
<h1><?php echo h($shell_user_key['ShellUserKey']['name']);?></h1>

<p><small>Created: <?php
echo $shell_user_key['ShellUserKey']['created'];
?></small></p> 
<p><pre style="pre-scrollable"><?php
echo h($shell_user_key['ShellUserKey']['key']);
?></pre></p>

<?php
echo $this->Html->link(__('Back'), 
		       array(
			     'action' => 'index'
			     ),
		       array('class' => 'btn btn-primary'));
?>