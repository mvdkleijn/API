<?php
/*
 * @author Ian Dundas with Band-x.org
 * TODO refactor all this.
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
		$this->display(API_VIEWS_BASE.'/documentation');
        
    }

	public function userAuthByAuthID($id=NULL) {
		$authManager = new UserAuthManager();
		return $this->userauth($authManager->getUserIDFromAuthID($id));
	}


	public function userauth($id=NULL) {
		$authManager = new UserAuthManager();
		
		if(is_numeric($id)||$id==NULL) {
			$users = $authManager->userList($id);

			#TODO: would be nice to write a shit hot SQL query to do this at getuser time instead
			if (sizeof($users)>0)foreach($users as &$user){
				$user=$authManager->bindMetricsToUser($user);

				if (array_key_exists('last_accessed', $user)){
					$user['prettytime']=ApiUsageManager::bb_since($user['last_accessed']);
				}
			}
			$this->display(API_VIEWS_BASE.'/userauth', array('id' => $id, 'users' => $users));
		}
		elseif($id == 'add') {
//			if($_POST[name] == '' ) {
//				Flash::set('error','This contract has NOT been added to our contracts - you need to give it a name');
//				Observer::notify('log_event', 'Contract was NOT added: no name supplied', 'programme', 3);
//				redirect(get_url('programme/contracts'));
//			}
//			else {
//				$addContract = $contractManager->add($_POST);
//				Flash::set('success',''.$_POST[name].' has been added to our contracts');
//				Observer::notify('log_event', 'Contract was added: <strong>'.$_POST[name].'</strong>', 'programme', 5);
//				redirect(get_url('programme/contracts'));
//			}
		}
		elseif($id == 'delete') {
//			$deleteContract = $contractManager->delete($_GET[id]);
//			Flash::set('success','This contract has been deleted');
//			Observer::notify('log_event', 'Contract was deleted', 'programme', 5);
//			redirect(get_url('programme/contracts'));
		}
		else
		{

		}
    }

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
		$this->display(API_VIEWS_BASE.'/apimethodstream', array('usageList' => $usageList));
    }


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
		$this->display(API_VIEWS_BASE.'/apiusage', array('id' => $id, 'usageMetrics' => $usageMetrics));
    }

	public function allowedentities($id=NULL) {

		$apiManager = new ApiManager();
		$tables=$apiManager->getAllTables();		
		
		#TODO: would be nice to write a shit hot SQL query to do this at getuser time instead
		if(is_numeric($id)) {
//			$contracts = $contractManager->contractList($id);
		}
		elseif($id == 'add') {
			$addEntity = $apiManager->add($_GET);
			Flash::set('success',''.$_GET['tablename'].' has been enabled: please add some columns');
			Observer::notify('log_event', 'Table was enabled for API access: <strong>'.$_GET['tablename'].'</strong>', 'programme', 5);
			redirect(get_url('plugin/api/allowedentities'));
		}
		elseif($id == 'delete') {
//			$deleteContract = $contractManager->delete($_GET[id]);
//			Flash::set('success','This contract has been deleted');
//			Observer::notify('log_event', 'Contract was deleted', 'programme', 5);
//			redirect(get_url('programme/contracts'));
		}
		else{
			$entity = $apiManager->update($_POST);
			Flash::set('success',''.$_GET['tablename'].' has been updated');
			Observer::notify('log_event', 'Table was updated for API access: <strong>'.$_GET['tablename'].'</strong>', 'programme', 5);
			redirect(get_url('plugin/api/allowedentities'));
		}
		

		$this->display(API_VIEWS_BASE.'/allowedtables', array('id' => $id, 'tables' => $tables));
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
			$this->stats['api_auth_id']=$_GET['authid'];
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
		return $xml->asXML();
	}


}