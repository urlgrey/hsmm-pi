<?php
/**
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.View.Layouts
 * @since         CakePHP(tm) v 0.10.0.1076
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

$cakeDescription = __d('cake_dev', __('BBHN-Pi-VPN'));
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <?php echo $this->Html->charset(); ?>
    <title>
      <?php echo $cakeDescription ?>:
      <?php echo $title_for_layout; ?>
    </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
          <?php echo $this->Html->script('jquery-2.0.3.min'); ?>
    <?php
      echo $this->Html->css('cake.generic');
      echo $this->Html->css('bootstrap.min');
    ?>
    <style>
      body {
        padding-top: 60px; /* 60px to make the container go all the way to the bottom of the topbar */
      }
    </style>
    <?php
      echo $this->Html->css('bootstrap-responsive.min');
      echo $this->fetch('meta');
      echo $this->fetch('css');
      echo $this->fetch('script');
    ?>
    <!-- Bing Map APIs to display node maps -->
    <script type="text/javascript" src="http://ecn.dev.virtualearth.net/mapcontrol/mapcontrol.ashx?v=7.0"></script>
  </head>
  <body>
    <!-- Reboot Modal -->
    <div id="rebootModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="myModalLabel"><? echo __('Reboot Confirmation'); ?></h3>
      </div>
      <div class="modal-body">
        <p>The node will be unavailable during the reboot.  Are you sure you want to do this?</p>
      </div>
      <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
        <?php
          echo $this->Html->link(__('Reboot'), array(
                                                    'controller' => 'system',
                                                    'action' => 'reboot'),
                                                    array('class'=>'btn btn-danger'));
        ?>
      </div>
    </div>

    <!-- Shutdown Modal -->
    <div id="shutdownModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="myModalLabel"><? echo __('Shutdown Confirmation'); ?></h3>
      </div>
      <div class="modal-body">
        <p>The node will be totally unavailable after shutdown.  Are you sure you want to do this?</p>
      </div>
      <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
        <?php
          echo $this->Html->link(__('Shutdown'), array(
                                                      'controller' => 'system',
                                                      'action' => 'shutdown'),
                                                      array('class'=>'btn btn-danger'));
        ?>
      </div>
    </div>

    <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
          <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <!-- <a class="brand" href="#"><?php echo __('BBHN-Pi-VPN'); ?></a> -->
          <div class="brand"><?php echo __('BBHN-Pi-VPN'); ?></div>
          <div class="nav-collapse collapse">
            <ul class="nav pull-left">
              <li <?php if (strstr($this->here, '/hsmm-pi/status') != FALSE) { echo 'class="active"'; }  ?>>
                <?php
                  echo $this->Html->link(__('Status'), array(
                                                            'controller' => 'status',
                                                            'action' => 'index'));
                ?>
              </li>
              <?php 
                 if ($this->Session->read('Auth.User')) {
              ?>
                <li class="dropdown">
                  <a href="#" class="dropdown-toggle" data-toggle="dropdown">Admin <b class="caret"></b></a>
                <ul class="dropdown-menu">
                <li <?php if (strstr($this->here, '/hsmm-pi/network_settings') != FALSE) { echo 'class="active"'; }  ?>>
                  <?php 
                    echo $this->Html->link(__("<i class=\"icon-signal\"></i>&nbsp;".'Network'),
                                           array(
                                                 'controller' => 'network_settings',
                                                 'action' => 'edit/1'),
                                           array('escape' => false));
                  ?>
                </li>
                <li <?php if (strstr($this->here, '/hsmm-pi/network_services') != FALSE) { echo 'class="active"'; }  ?>>
                  <?php 
                    echo $this->Html->link("<i class=\"icon-bullhorn\"></i>&nbsp;".__('Services'),
                                           array(
                                                 'controller' => 'network_services',
                                                 'action' => 'index'),
                                           array('escape' => false));
                  ?>
                </li>
                <li <?php if (strstr($this->here, '/hsmm-pi/location') != FALSE) { echo 'class="active"'; }  ?>>
                  <?php 
                    echo $this->Html->link("<i class=\"icon-globe\"></i>&nbsp;".__('Location'),
                                           array(
                                                 'controller' => 'location_settings',
                                                 'action' => 'edit/1'),
                                           array('escape' => false));
                   ?>
                </li>
                <li <?php if (strstr($this->here, '/hsmm-pi/user') != FALSE) { echo 'class="active"'; }  ?>>
                  <?php
                    echo $this->Html->link("<i class=\"icon-user\"></i>&nbsp;".__('Account'),
                                          array(
                                                'controller' => 'users',
                                                'action' => 'edit'),
                                          array('escape' => false));
                  ?>
                </li>
                <li class="divider"></li>
                <li <?php if (strstr($this->here, '/hsmm-pi/wifi_scan') != FALSE) { echo 'class="active"'; }  ?>>
                  <?php
                     echo $this->Html->link("<i class=\"icon-search\"></i>&nbsp;".__('WiFi Scan'),
                                            array(
                                                  'controller' => 'wifi_scan',
                                                  'action' => 'index'),
                                            array('escape' => false));
                  ?>
                </li>
                <li class="divider"></li>
                <li><a href="#rebootModal" data-toggle="modal"><? echo __('<i class="icon-refresh"></i>&nbspReboot'); ?></a></li>
                <li><a href="#shutdownModal" data-toggle="modal"><? echo __('<i class="icon-off"></i>&nbspShutdown'); ?></a></li>
              <?php
                }
              ?>
            </ul>
          </li>
        </ul>
        <?php 
          if ($this->Session->read('Auth.User')) {
        ?>
          <ul class="nav pull-right">
            <li>
              <?php
                echo $this->Html->link(__('Logout'),
                                      array(
                                            'controller' => 'users',
                                            'action' => 'logout'));
              ?>
            </li>
          </ul>
        </div>
        <?php 
          } else {
        ?>
          <ul class="nav pull-right">
            <li>
              <?php
                echo $this->Html->link(__('Login'),
                                      array(
                                            'controller' => 'users',
                                            'action' => 'login'));
              ?>
            </li>
          </ul>
        <?php
          }
        ?>
      </div>
    </div>
  </div>

  <div class="container">
    <?php echo $this->Session->flash(); ?>
    <?php echo $this->fetch('content'); ?>
    <p>
      <h4> BBHN-Pi-VPN Version:&nbsp;<?php echo Configure::read('App.version'); ?></h4>
    </p>
  </div>

  <?php echo $this->element('sql_dump'); ?>
  <?php echo $this->Html->script('bootstrap.min', array('media' => 'screen')); ?>
</body>
</html>
