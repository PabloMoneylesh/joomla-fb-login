<?php
/**
 * Social Login
 *
 * @version 	1.0
 * @author		Arkadiy, Joomline
 * @copyright	© 2012. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

// No direct access
defined('_JEXEC') or die;

class plgAuthenticationFblogin extends JPlugin
{
	/**
	 * This method should handle any authentication and report back to the subject
	 *
	 * @access	public
	 * @param	array	Array holding the user credentials
	 * @param	array	Array of extra options
	 * @param	object	Authentication response object
	 * @return	boolean
	 * @since 1.5
	 */
	function onUserAuthenticate($credentials, $options, &$response)
	{
		//echo "onUserAuthenticate=".$options['component'];
        if($options['component'] != "fblogin"){
            return false;
        }
		if (JFactory::getApplication()->isAdmin())
		{
			return false;
		}
        
		$response->type = 'FBlogin';

		// Joomla does not like blank passwords
		if (empty($credentials['password']))
		{
			$response->status = JAuthentication::STATUS_FAILURE;
			$response->error_message = JText::_('JGLOBAL_AUTH_EMPTY_PASS_NOT_ALLOWED');
			return false;
		}
		$email =  $credentials['email'];		
		// Get a database object
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);

		$query->select('id')
			->from('#__users')
			->where('email=' . $db->quote($email));
		$uid = $db->setQuery($query,0,1)->loadResult();
		//echo "<br>".$uid;
		
		if ($uid)
		{			
			if ($credentials['password'] == $credentials['email'])
			{
				//echo "<br> ok";
				$user = JUser::getInstance($uid); // Bring this in line with the rest of the system
				$response->email = $user->email;
				$response->fullname = $user->name;				
				
				$response->language = $user->getParam('language');
				
				$response->status = JAuthentication::STATUS_SUCCESS;
				$response->error_message = '';
			}
			else
			{
				$response->status = JAuthentication::STATUS_FAILURE;
				$response->error_message = JText::_('JGLOBAL_AUTH_INVALID_PASS');
			}
		}
		else
		{
			$response->status = JAuthentication::STATUS_FAILURE;
			$response->error_message = JText::_('JGLOBAL_AUTH_NO_USER');
		}
	}
}
