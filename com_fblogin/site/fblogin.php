<?php
/**
 * Social Login
 *
 * @version 	1.0
 * @author		SmokerMan, Arkadiy, Joomline
 * @copyright	© 2012. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

// No direct access.
defined('_JEXEC') or die('(@)|(@)');

// import joomla controller library
jimport('joomla.application.component.controller');

// Require the com_content helper library
require_once(JPATH_COMPONENT.'/controller.php');

// Get an instance of the controller prefixed by SLogin
$controller = new FbLoginController();
//$controller = call_user_func(array($className, 'getInstance'), 'SLogin');

$app = JFactory::getApplication();

// Perform the Request task
$controller->login();

//http://localhost/rupoland/component/fblogin