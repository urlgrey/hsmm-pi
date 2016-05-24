<?php
/**
 * Routes configuration
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different URLs to chosen controllers and their actions (functions).
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
 * @package       app.Config
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * Here, we are connecting '/' (base path) to controller called 'Pages',
 * its action called 'display', and we pass a param to select the view file
 * to use (in this case, /app/View/Pages/home.ctp)...
 */
//	Router::connect('/', array('controller' => 'pages', 'action' => 'display', 'home'));
/**
 * ...and connect the rest of 'Pages' controller's URLs.
 */
Router::connect('/pages/*', array('controller' => 'pages', 'action' => 'display'));

Router::connect('/status', array('controller' => 'status', 'action' => 'index'));
Router::connect('/', array('controller' => 'status', 'action' => 'index'));
Router::connect('/location_settings', array('controller' => 'location_settings', 'action' => 'edit'));
Router::connect('/network_settings', array('controller' => 'network_settings', 'action' => 'edit'));
Router::connect('/user', array('controller' => 'users', 'action' => 'edit'));
Router::connect('/system', array('controller' => 'system', 'action' => 'reboot'));
Router::connect('/wifi_scan', array('controller' => 'wifi_scan', 'action' => 'index'));
Router::connect('/network_services', array('controller' => 'network_services', 'action' => 'index'));
Router::connect('/dhcp_reservations', array('controller' => 'dhcp_reservations', 'action' => 'index'));
/**
 * Load all plugin routes. See the CakePlugin documentation on
 * how to customize the loading of plugin routes.
 */
CakePlugin::routes();

/**
 * Load the CakePHP default routes. Only remove this if you do not want to use
 * the built-in default routes.
 */
require CAKE . 'Config' . DS . 'routes.php';
