<?php
/*
 * API plugin for Wolf CMS
 * November 2009
 * @author Ian Dundas for Band-x.org
 */
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

	#TODO: remove replicated functionality at userList($id=NULL)
	function doSelectById($id)
	{
		if (isset($id)&&is_numeric($id))
		{
			$sql =	"SELECT * ".
					"FROM   ".TABLE_PREFIX.self::TABLE_NAME." ".
					"WHERE id='{$id}'";
			list($result) = self::executeSql($sql);
			return $result;
		}
	}

	function update_array($array)
      {
          global $table_prefix;

          if (empty($array)     ||!isset($array))     return FALSE;
          if (is_object($array)){
            $array=(array)$array;
          }
          if (0==sizeof($array))                return FALSE;
          if (!array_key_exists('id', $array))  return FALSE;

          $id = $array['id'];
          unset($array['id'],$array['action'],$array['submit']);

          $comma='';
          $sql = "update ".TABLE_PREFIX.self::TABLE_NAME." SET ";
          foreach ($array as $key=>$value){
            $sql.= "{$comma} ".TABLE_PREFIX.self::TABLE_NAME.".{$key} = '{$value}'";
            $comma=',';
          }
          $sql.= " WHERE ".TABLE_PREFIX.self::TABLE_NAME.".id = {$id}";
          self::executeSql($sql);
          if (mysql_error()){ die('dev error: '.mysql_error().' ... '.$sql);}
          return;
      }


	  function insert_array($array)
      {
          global $table_prefix;

          if (empty($array)     ||!isset($array))     return FALSE;
          if (is_object($array)){
            $array=(array)$array;
          }
          if (0==sizeof($array))                return FALSE;
          unset($array['id'],$array['action'],$array['submit']);

          $comma='';
          $sql = "INSERT INTO ".TABLE_PREFIX.self::TABLE_NAME." SET ";
          foreach ($array as $key=>$value){
            $sql.= "{$comma} ".TABLE_PREFIX.self::TABLE_NAME.".{$key} = '{$value}'";
            $comma=',';
          }
          
          self::executeSql($sql);
          if (mysql_error()){ die('dev error: '.mysql_error().' ... '.$sql);}
          return;
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

	function globalUserUsernameList() {
		$usertable = TABLE_PREFIX."user";

		$sql = "SELECT id, username FROM {$usertable} ";
		$sql.= "ORDER BY {$usertable}.username ASC";

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