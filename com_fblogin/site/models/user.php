<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
jimport('joomla.user.helper');


class fbloginModelUser extends JModelItem
{
	public function checkUserExists($fbUser)
	{
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);

		$query->select('id')
			->from('#__users')
			->where('email=' . $db->quote($fbUser['email']));
		$uid = $db->setQuery($query,0,1)->loadResult();
		
		return $uid;
	}
	
	
	public function registerUser($fbUser)
	{
		$password = $this->generatePassword($fbUser['email']);
		$data = array(
        "name"=>$fbUser['name'],
        "username"=>$fbUser['email'],
        "password"=>$password,
        "password2"=>$password,
        "email"=>$fbUser['email'],
        "block"=>0,
        "groups"=>array(2)
    );
	
	 $user = new JUser;
    //Write to database
    if(!$user->bind($data)) {
		return $user->getError();
        //throw new Exception("Could not bind data. Error: " . $user->getError());
    }
    if (!$user->save()) {
        //throw new Exception("Could not save user. Error: " . $user->getError());
		return $user->getError();
    }

   
			
		return $user;
	}

	private function generatePassword($pw)
	{
		 $salt = JUserHelper::genRandomPassword(32);
		 $crypted = JUserHelper::getCryptedPassword($pw, $salt);
		 $password = $crypted.':'.$salt;
		 return $password;
	}
}
?>	