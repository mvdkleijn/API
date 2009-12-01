<?php
/*    sdfsfsdf
 * API plugin for Wolf CMS
 * November 2009
 * @author Ian Dundas for Band-x.org
 */
class ApiController extends PluginController {

	public function __construct() {
		$this->api_manager = new ApiManager();
		$this->apiUsageManager = new ApiUsageManager();

		if (defined('CMS_BACKEND')) {
			$this->setLayout('backend');
		} else {
			$this->setLayout('plaintext');
		}

		$this->stats=array();
	}

	/* -----------------------------------------------
	 * BACKEND FUNCTIONS
	 * CAN I MOVE THESE INTO A SEPARATE CONTROLLER?
	 * -----------------------------------------------
	 */

	public function documentation() {
		$this->display(API_VIEWS_BASE.'/backend/documentation/index');        
    }

	/* API CALL STREAM CONTROLLER */
	public function methodstream($id=NULL) {

		$authManager = new ApiUsageManager();
		$usageList = $authManager->usageList();

		if (sizeof($usageList)>0)
		{
			foreach ($usageList as &$i){ #assign reference instead of copying the value.
				if (array_key_exists('accesstime', $i)){
					$i['prettytime']=ApiUsageManager::bb_since($i['accesstime']);
				}
			}
		}
		$this->display(API_VIEWS_BASE.'/backend/apicalls/stream', array('usageList' => $usageList));
    }

	/* API CALL OVERVIEW CONTROLLER */
	public function methodusage($id=NULL) {

		$authManager = new ApiUsageManager();
		$usageMetrics = $authManager->methodUsage();
		
		if (sizeof($usageMetrics)>0)
		{
			foreach ($usageMetrics as &$i){ #assign reference instead of copying the value.
				if (array_key_exists('last_accessed', $i)){
					$i['prettytime']=ApiUsageManager::bb_since($i['last_accessed']);
				}
			}
		}
		#TODO: would be nice to write a shit hot SQL query to do this at getuser time instead
		$this->display(API_VIEWS_BASE.'/backend/apicalls/overview', array('id' => $id, 'usageMetrics' => $usageMetrics));
    }

	/* ALLOWED TABLE ENTITES CONTROLLER */
	public function allowedentities($token=NULL) {
		$apiManager = new ApiManager();
		$tables=$apiManager->getAllTables();		
		
		#TODO: would be nice to write a shit hot SQL query to do this at getuser time instead		
		#View single row:
		if(is_numeric($token))
		{
			if(isset($_POST)&&!empty($_POST))
			{
				if (array_key_exists('enabled',$_POST))
					$_POST['enabled']=1;
				else
					$_POST['enabled']=0;

				if(isset($_POST['columns'])&&!empty($_POST['columns']))
					$_POST['columns']=implode(';',$_POST['columns']);

				$allowed_fields = array_flip(array('id','tablename','tablename_slug','columns','enabled'));
				$data = array_intersect_key ($_POST,$allowed_fields);

				$table = array();
				#If we've posted to this URL, we're editing:
				if (0<sizeof($data))
				{
					$entity = $apiManager->update_array($data);
					Flash::set('success',''.$data['tablename'].' has been updated');
					Observer::notify('log_event', 'Table was updated for API access: <strong>'.$data['tablename'].'</strong>', 'programme', 5);
					redirect(get_url('plugin/api/allowedentities'));
					exit;
				}
			}
			#Else we just want a look at it (implicit else, because exit; would stop it getting here if was successful at updating)
			$table = $apiManager->doSelectById($token);
			$table['allcolumns'] = $apiManager->getColumnNamesByTable($table['tablename']);
			$this->display(API_VIEWS_BASE.'/backend/allowedentities/single', array('id' =>$token, 'table' => $table));
		}
		#Add
		elseif($token == 'add') {
			$addEntity = $apiManager->add($_GET);
			Flash::set('success',''.$_GET['tablename'].' has been enabled: please add some columns');
			Observer::notify('log_event', 'Table was enabled for API access: <strong>'.$_GET['tablename'].'</strong>', 'programme', 5);
			redirect(get_url('plugin/api/allowedentities'));
		}
		#Delete
		elseif($token == 'delete') {die('disabled2');
//			$deleteContract = $contractManager->delete($_GET[id]);
//			Flash::set('success','This contract has been deleted');
//			Observer::notify('log_event', 'Contract was deleted', 'programme', 5);
//			redirect(get_url('programme/contracts'));
		}
		else{
			$this->display(API_VIEWS_BASE.'/backend/allowedentities/list', array('id' =>$token, 'tables' => $tables));
		}
		//
    }

	/* USER AUTHENTICATION CONTROLLER */
	public function userauth($token=NULL) {
		$authManager = new UserAuthManager();

		if((isset($_POST)&&!empty($_POST)))
		{
			if (array_key_exists('enabled',$_POST))
				$_POST['enabled']=1;
			else
				$_POST['enabled']=0;

			$allowed_fields = array_flip(array('user_id','id','key','throttle_limit','enabled'));
			$data = array_intersect_key ($_POST,$allowed_fields);

			if (0<sizeof($data))
			{
				if(is_numeric($token))
				{
					$entity = $authManager->update_array($data);
					Flash::set('success','User record has been updated');
					Observer::notify('log_event', 'User table updated: <strong>User '.$data['user_id'].'</strong>', 'programme', 5);
					redirect(get_url('plugin/api/userauth'));
					exit;
				}
				else
				{
					$entity = $authManager->insert_array($data);
					Flash::set('success','User auth has been added');
					Observer::notify('log_event', 'Userauth table updated: <strong>User '.$data['user_id'].'</strong>', 'programme', 5);
					redirect(get_url('plugin/api/userauth'));
					exit;
				}
			}
		}
		else if(is_numeric($token))
		{
			#Else we just want a look at it (implicit else, because exit; would stop it getting here if was successful at updating)
			$user = $authManager->doSelectById($token);
			$user = $authManager->bindMetricsToUser($user);
			$allusers=$authManager->globalUserUsernameList();
			$this->display(API_VIEWS_BASE.'/backend/users/single', array('id' =>$token, 'user' => $user,'allusers'=>$allusers));
		}
		#Add
		elseif($token == 'add') {
			$allusers=$authManager->globalUserUsernameList();
			$this->display(API_VIEWS_BASE.'/backend/users/single', array('id' =>$token, 'user' => array(),'allusers'=>$allusers));
		}
		#Delete
		elseif($token == 'delete') {die('disabled2');
//			$deleteContract = $contractManager->delete($_GET[id]);
//			Flash::set('success','This contract has been deleted');
//			Observer::notify('log_event', 'Contract was deleted', 'programme', 5);
//			redirect(get_url('programme/contracts'));
		}
		else{
			$users = $authManager->userList();

			#TODO: would be nice to write a shit hot SQL query to do this at getuser time instead
			if (sizeof($users)>0)foreach($users as &$user){
				$user=$authManager->bindMetricsToUser($user);

				if (array_key_exists('last_accessed', $user)&&isset($user['last_accessed'])){
					$user['prettytime']=ApiUsageManager::bb_since($user['last_accessed']);
				}
			}
			$this->display(API_VIEWS_BASE.'/backend/users/list', array('users' => $users));
		}		
    }
	/* USER AUTHENTICATION BY AUTHID CONTROLLER */
	public function userAuthByAuthID($id=NULL) {
		$authManager = new UserAuthManager();
		return $this->userauth($authManager->getUserIDFromAuthID($id));
	}



	/* -----------------------------------------------
	 * FRONTEND FUNCTIONS
	 * CAN I MOVE THESE INTO A SEPARATE CONTROLLER?
	 * -----------------------------------------------
	 */
	public function isAuthenticated($array) {

		if(array_key_exists('authid', $array)&&array_key_exists('authkey', $array))
		{
			extract($array);{			
				$auth =$this->api_manager->countRows(TABLE_PREFIX.'api_auth', array('id'=>$authid,'key'=>$authkey));
				return $auth==1;
			}
		}
		return FALSE;
	}


	#api_auth_id, accesstime, api_method, ip_address, api_result
	public function fetchbyid($slug, $format, $id) {
		if (!array_key_exists('api_method', $this->stats)){
			$this->stats['api_method']='fetchbyid|'.$slug.'|'.$format;
		}
		#already handled in the fetchall message:
		//#$this->apiUsageManager->logApiUsage($this->stats);
		$this->fetchall($slug, $format, $id);
	}

	public function fetchall($slug,$format, $id=NULL) {
		if (!array_key_exists('api_method', $this->stats)){
			$this->stats['api_method']='fetchall|'.$slug.'|'.$format;
		}
		if (!array_key_exists('api_auth_id', $this->stats)){
			$this->stats['api_auth_id']=(array_key_exists('authid',$_GET))?$_GET['authid']:'[undefined]';
		}

		if ($this->isAuthenticated($_GET)):
			#need to know what columns are to be in returned dataset
			$allowed_columns = $this->api_manager->getAllowedColumnsByTable($slug);
			$result_set = array();
			if (sizeof($allowed_columns)>0) {
				$result_set = $this->api_manager->doSelectByColumns($slug, $allowed_columns, $id);
			}
			$count = sizeof($result_set);
			$out = array(
				'slug'=>$slug,
				'resultset'=>$result_set,
				'resultcount'=>$count,
				'format'=>$format);

			if ($count>0) {
				if (!array_key_exists('api_result', $this->stats)){
					$this->stats['api_result']=1;
					$this->stats['api_result_message']='success';
				}
			}
			else{
				if (!array_key_exists('api_result', $this->stats)){
					$this->stats['api_result']=0;
					$this->stats['api_result_message']='no results';
				}
			}
			$this->apiUsageManager->logApiUsage($this->stats);
			self::renderArray($out,$format);
		else: $this->error($format,'Unauthorized',401);
		
		endif;
		exit;
	}

	#@id consider revising this implementation
	public function error($format, $str='Unknown error',$code=NULL) {
		header  ($str, TRUE, $code);
		$out=array(
			'error'=>$str,
			'code'=>$code
		);

		$this->stats['api_result_message']=$str;
		$this->stats['api_result']=-1;
		$this->apiUsageManager->logApiUsage($this->stats);
		self::renderArray($out,$format);
	}

	public static function renderArray($out=array(),$format='json') {
		if (strcasecmp('json', $format)==0) {
		#http://snippets.dzone.com/posts/show/5882
			header('Cache-Control: no-cache, must-revalidate');
			header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
			header('Content-type: application/json');

			echo self::renderAsJSON($out);
		}
		elseif (strcasecmp('xml', $format)==0) {
		#http://www.satya-weblog.com/2008/02/header-for-xml-content-in-php-file.html
			header('Cache-Control: no-cache, must-revalidate');
			header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
			header ("Content-Type:text/xml");
			echo self::renderAsXML($out, 'xml', NULL, 'entity');
		}
	}

	private static function renderAsJSON($array=array()) {
		return json_encode($array);
	}

	private static function renderAsXML($data=array(), $rootNodeName = 'data', $xml=null, $nodeName='unknownNode') {

		/**
		 * edited from http://snipplr.com/view.php?codeview&id=3491
		 *
		 * The main function for converting to an XML document.
		 * Pass in a multi dimensional array and this recrusively loops through and builds up an XML document.
		 *
		 * @param array $data
		 * @param string $rootNodeName - what you want the root node to be - defaultsto data.
		 * @param SimpleXMLElement $xml - should only be used recursively
		 * @return string XML
		 */

		// turn off compatibility mode as simple xml throws a wobbly if you don't.
		if (ini_get('zend.ze1_compatibility_mode') == 1) {
			ini_set ('zend.ze1_compatibility_mode', 0);
		}

		if ($xml == null) {
			$xml = simplexml_load_string("<?xml version='1.0' encoding='utf-8'?><$rootNodeName />");
		}

		// loop through the data passed in.
		foreach($data as $key => $value) {
		// no numeric keys in our xml please!
			if (is_numeric($key)) {
			// make string key...
				$key = $nodeName. (string) $key;
			}

			// replace anything not alpha numeric
			$key = preg_replace('/[^a-z]/i', '', $key);

			// if there is another array found recrusively call this function
			if (is_array($value)) {
				$node = $xml->addChild($key);
				// recrusive call.
				self::renderAsXML($value, $rootNodeName, $node, $nodeName);
			}
			else {
			// add single node.
				$value = htmlentities($value);
				$xml->addChild($key,$value);
			}

		}
		// pass back as string. or simple xml object if you want!

		$dom = dom_import_simplexml($xml)->ownerDocument;
		$dom->formatOutput = true;
		return $dom->saveXML();
	}


}