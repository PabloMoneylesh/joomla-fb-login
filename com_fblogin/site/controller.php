<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

require_once (JPATH_ROOT.'/libraries/facebook_sdk/autoload.php');

/**
 * Content Component Controller
 *
 * @since  1.5
 */
class FbLoginController extends JControllerForm
{
	
	
	
	function createFbContext(){
		$fb = new Facebook\Facebook([
		  'app_id' => '1832989900287917', 
		  'app_secret' => '268c2b869a5e520347680701fd156c20',
		  'default_graph_version' => 'v2.8',
		  ]);
		  return $fb;
	}
	
	public function login()
	{
		
		$returnURL=JRequest::getVar( 'baseurl' );
		
		
		$app = JFactory::getApplication();        
       
		$fb = $this->createFbContext();
		

		$helper = $fb->getRedirectLoginHelper();

		try {
		  $accessToken = $helper->getAccessToken();
		} catch(Facebook\Exceptions\FacebookResponseException $e) {
		  // When Graph returns an error
		  //echo 'Graph returned an error: ' . $e->getMessage();
		  JError::raiseError('ERROR', 'Login FB Graph returned an error: '. $e->getMessage(), $e->getMessage());
		  exit;
		} catch(Facebook\Exceptions\FacebookSDKException $e) {
		  // When validation fails or other local issues
		  //echo 'Facebook SDK returned an error1: ' . $e->getMessage();
		  JError::raiseError('ERROR', 'Facebook SDK returned an error1: ' . $e->getMessage(), $e->getMessage());
		  exit;
		}

		if (! isset($accessToken)) {
		  if ($helper->getError()) {
			JError::raiseError('ERROR', "Error: " . $helper->getError() . " Error Reason: " . $helper->getErrorReason(),$helper->getError());
			/*header('HTTP/1.0 401 Unauthorized');
			echo "Error: " . $helper->getError() . "\n";
			echo "Error Code: " . $helper->getErrorCode() . "\n";
			echo "Error Reason: " . $helper->getErrorReason() . "\n";
			echo "Error Description: " . $helper->getErrorDescription() . "\n";*/
		  } else {
			/*header('HTTP/1.0 400 Bad Request');
			echo 'Bad request';*/
			JError::raiseError('ERROR', "Error login via FB: Bad request","");
		  }
		  exit;
		}

		// Logged in
		/*echo '<h3>Access Token</h3>';
		var_dump($accessToken->getValue());*/

		// The OAuth 2.0 client handler helps us manage access tokens
		$oAuth2Client = $fb->getOAuth2Client();

		// Get the access token metadata from /debug_token
		$tokenMetadata = $oAuth2Client->debugToken($accessToken);
		/*echo '<h3>Metadata</h3>';
		var_dump($tokenMetadata);*/

		// Validation (these will throw FacebookSDKException's when they fail)
		$tokenMetadata->validateAppId('1832989900287917'); // Replace {app-id} with your app id
		// If you know the user ID this access token belongs to, you can validate it here
		//$tokenMetadata->validateUserId('123');
		$tokenMetadata->validateExpiration();

		if (! $accessToken->isLongLived()) {
		  // Exchanges a short-lived access token for a long-lived one
		  try {
			$accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
		  } catch (Facebook\Exceptions\FacebookSDKException $e) {
			//echo "<p>Error getting long-lived access token: " . $helper->getMessage() . "</p>\n\n";
			JError::raiseError('ERROR', "Error getting long-lived access token: " . $helper->getMessage(),$helper->getMessage());
			exit;
		  }

		  //echo '<h3>Long-lived</h3>';
		 // var_dump($accessToken->getValue());
		}

		$_SESSION['fb_access_token'] = (string) $accessToken;

		// User is logged in with a long-lived access token.
		// You can redirect them to a members-only page.
		//header('Location: https://example.com/members.php');
		
		
		$userprofile = $this->getUserData($fb, $accessToken);
		
		
		 /*echo '<h3>USER DATA</h3>';
		 var_dump($userprofile);*/
		  
		  $user = $userprofile->getGraphUser();
		/*echo "<br>name:".$user->getName();
		echo "<br>id:".$user->getId();
		echo "<br>email:".$user['email'];*/
		
		$usermodel	=$this->getModel( "user" );
		$userCreationResult = $usermodel->registerUser($user);
		
		//echo "<br>userCreationResult: ".$userCreationResult;
		
		
		/*if(! $userCreationResult instanceof JUser){
			
			JError::raiseWarning('ERROR', $userCreationResult  ." - ". $user['email'], $user['email']);
			return false;
		}*/
		
		
		
		$credentials = array('username' => $user['email'], 'password' => $user['email']);
		
		
		
		$result = $app->login($credentials, array('action' => 'core.login.admin'));
		//echo "<p>login error: " . $result . "</p>\n\n";
		if (!($result instanceof Exception))
		{
			// Only redirect to an internal URL.
			if (JUri::isInternal($return))
			{
				JError::raiseError('ERROR', "login error: " . $result, $result);
				echo "<p>login error: " . $result . "</p>\n\n";
				/*// If &tmpl=component - redirect to index.php
				if (strpos($return, "tmpl=component") === false)
				{
					$app->redirect($return);
				}
				else
				{
					$app->redirect('index.php');
				}*/
			}
		}
		//$authorisations = $authenticate->authorise($response, $options);
		$app->redirect($returnURL);
		
		return true;
	}
	
	function getUserData($fb, $accessToken){
		try {
		  // Returns a `Facebook\FacebookResponse` object
		  $response = $fb->get('/me?fields=id,name,email', $accessToken);
		} catch(Facebook\Exceptions\FacebookResponseException $e) {
		  //echo 'Graph returned an error: ' . $e->getMessage();
		  JError::raiseError('ERROR', "Graph returned an error getUserData: " . $e->getMessage(), $e->getMessage());
		  exit;
		} catch(Facebook\Exceptions\FacebookSDKException $e) {
		  //echo 'Facebook SDK returned an error2: ' . $e->getMessage();
		  JError::raiseError('ERROR', "Facebook SDK returned an error getUserData: " . $e->getMessage(), $e->getMessage());
		  exit;
		}

		return $response;
	}
}
