<?php

class UserAuthManager {

	const TABLE_NAME = 'api_auth';

	function __construct() {
		global $__CMS_CONN__;
		$this->db = $__CMS_CONN__;
	}

	function executeSql($sql) {
		$stmt = $this->db->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	function getUserIDFromAuthID($id)
	{
		$authtable = TABLE_PREFIX.self::TABLE_NAME;
		$usertable = TABLE_PREFIX."user";

		$sql="	SELECT {$usertable}.id from {$usertable}
				INNER JOIN {$authtable} ON {$usertable}.id={$authtable}.user_id
				WHERE {$authtable}.id={$id}";

		#@id King Creosote is brilliant
		if ($result=self::executeSql($sql)){
			if (count($result)>0 && array_key_exists('id', $result[0])){
				return $result[0]['id'];
			}
		}
		return FALSE;
	}

	function userList($id=NULL) {
		$authtable = TABLE_PREFIX.self::TABLE_NAME;
		$usertable = TABLE_PREFIX."user";

		$sql = "SELECT * FROM {$usertable},{$authtable}
				WHERE {$usertable}.id={$authtable}.user_id";

		if($id) $sql .= " AND {$usertable}.id='$id'";
		$sql .= " ORDER BY {$usertable}.name ASC";
		
		return self::executeSql($sql);
	}

	function bindMetricsToUser($user)
	{
		if (is_array($user))
		{
			$id=$user['id'];
			
			$sql = "SELECT count(*)  as total_hits, av_api_usage.accesstime as last_accessed
					FROM av_api_usage
					WHERE api_auth_id = {$id}
					ORDER BY accesstime DESC
					LIMIT 1";
			$metrics = self::executeSql($sql);

			#@id tuuune 'Nirvana-Lithium'
			if (sizeof($metrics)==1){
				list($metrics)=$metrics;
				foreach($metrics as $key=>$value){
					if (!array_key_exists($key, $user)){
						$user[$key]=$value;
					}else{
						$user['metric_'.$key]=$value;
					}
				}
			}
		}
		return $user;
	}


	function add($_POST) {
		$sql = "INSERT INTO ".self::TABLE_NAME."
				(id, name, type)
				VALUES ('', '".filter_var($_POST['name'], FILTER_SANITIZE_STRING)."', '".filter_var($_POST['type'], FILTER_SANITIZE_STRING)."')";
		return self::executeSql($sql);
	}
	
	function quickAdd($name, $type, $association, $document) {
		$sql = "INSERT INTO ".self::TABLE_NAME."
				(id, name, type, associationid, documentid)
				VALUES ('', '".filter_var($name, FILTER_SANITIZE_STRING)."', '".filter_var($type, FILTER_SANITIZE_STRING)."', '".filter_var($association, FILTER_SANITIZE_STRING)."', '".filter_var($document, FILTER_SANITIZE_STRING)."')";
//				echo $sql;exit;
		return self::executeSql($sql);
	}

	function delete($id) {
		$sql = "DELETE FROM ".self::TABLE_NAME."
				WHERE id='".$id."'";
		return self::executeSql($sql);
	}

}