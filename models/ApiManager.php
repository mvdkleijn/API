<?php
/*
 * API plugin for Wolf CMS
 * November 2009
 * @author Ian Dundas for Band-x.org
 */

class APIManager {


		const TABLE_NAME = 'api_allowedtables';


		function __construct() {
			global $__CMS_CONN__;
			$this->db = $__CMS_CONN__;
		
		}


		function countRows($table_name, $params=array())
		{
			$sql='SELECT COUNT(*) as count FROM '.$table_name;

			$flag=TRUE;
			if (sizeof($params)>0){
				foreach($params as $key=>$value)
				{
					if ($flag)$sql.=' WHERE ';
					else $sql.= ' AND ';
					$flag=FALSE;

					$sql.= $table_name.'.'.$key.'= \''.$value.'\'';
				}
			}
			if ($result=self::executeSql($sql))
			{
				#be careful. Can't be arsed with array-key not found errors.
				if (is_array($result)){
					if (is_array($result[0])){
						if (array_key_exists('count', $result[0])){
							return $result[0]['count'];
						}
					}
				}
			}
			return 0;
		}

		# switch to 4x4 for this function, we're going offroad
		#Gets all tablenames and associates them with the API permissions if they've been defined
		function getAllTables()
		{
			$sql='SHOW TABLES';
			$tablesArray=self::executeSql($sql);
			
			if (is_array($tablesArray) && sizeof($tablesArray)>0)
			{				
				foreach ($tablesArray as &$table)
				{
					#need to get details on $table
					list($tablename_raw)=array_values($table);

					#phewy: clean table name of prefix
					$tablename=substr(	
										$tablename_raw,
										strpos($tablename_raw, TABLE_PREFIX)+strlen(TABLE_PREFIX),
										strlen($tablename_raw)
									);
					
					$sql='	SELECT *
							FROM '.TABLE_PREFIX.'api_allowedtables
							WHERE tablename = \''.$tablename.'\'';
					
					$result = self::executeSql($sql);
					
					if (is_array($result)&&sizeof($result)==1)
					{
						$table=array_merge($table,$result[0]);
					}
					$table['raw_name']=$tablename_raw;
					$table['clean_name']=$tablename;


					//print $sql."\n";
				}
			}//die();
			return $tablesArray;
		}
		function getColumnNamesByTable($tablename)
		{
                        $sql = "SELECT column_name
                                FROM information_schema.columns
                                WHERE table_schema='".DB_DATABASE_NAME."' AND table_name = '".TABLE_PREFIX.$tablename."'";

                        $result = $this->executeSql($sql);
                        $return = array();
                        if(!empty($result))foreach($result as $key=>$value){
                            $return=array_merge($return,array_flip($value));
                        }
                        return $return;
		}



        function getAllowedColumnsByTable($slug=FALSE)
        {
			if ($slug){
				$sql = "select columns from ".TABLE_PREFIX."api_allowedtables where tablename_slug='{$slug}' and enabled=1";
				if ($result = self::executeSql($sql)){
					list($result)=$result;
					return explode(';',$result['columns']);
				}
			}
			return array();
		}

        function getTableNameBySlug($slug=FALSE)
        {
            if($slug)
            {
                $sql = "SELECT tablename
                        FROM ".TABLE_PREFIX.self::TABLE_NAME."
                        WHERE  tablename_slug = '{$slug}'
                        LIMIT 1";
                $result = self::executeSql($sql);
                if (sizeof($result)==1)
                {
                    return $result[0]['tablename'];
                }
            }
            return FALSE;
        }

        function doSelectByColumns($slug=FALSE, $allowed_columns=array(),$id=NULL)
        {			
            if ($table_name = $this->getTableNameBySlug($slug))
            {
                if (is_array($allowed_columns)&&sizeof($allowed_columns)>0)
                {
                    $columns_string = implode(',',$allowed_columns);

                    $sql = "SELECT {$columns_string}
                            FROM   ".TABLE_PREFIX.$table_name;

                    if (isset($id))$sql.=' WHERE id='.$id;
                    
                    return $result = self::executeSql($sql);
                }
            }
            return array();
        }

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


	function executeSql($sql) {
		$stmt = $this->db->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	function accessList($id=NULL) {
		$sql = "SELECT * FROM ".TABLE_PREFIX.self::TABLE_NAME."";
		if($id) $sql .= " WHERE id='$id'";
		return self::executeSql($sql);
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
            $sql.= "{$comma} {$key} = '{$value}'";
            $comma=',';
          }
          $sql.= " WHERE id = {$id}";
          self::executeSql($sql);
          if (mysql_error()){ die('dev error: '.mysql_error().' ... '.$sql);}
          return;
      }


	function update($_POST) {
		$sql = "UPDATE ".TABLE_PREFIX.self::TABLE_NAME."
				SET name='".filter_var($_POST['name'], FILTER_SANITIZE_STRING)."',
					description='".filter_var($_POST['description'], FILTER_SANITIZE_MAGIC_QUOTES)."'
				WHERE id='".filter_var($_POST['id'], FILTER_SANITIZE_STRING)."'
		";
		die('about to update: '.$sql);
		return self::executeSql($sql);
	}

	function add($GET) {
		$sql = "INSERT INTO ".TABLE_PREFIX.self::TABLE_NAME."
				VALUES ('',
						'".filter_var($GET['tablename_slug'], FILTER_SANITIZE_STRING)."',
						'".filter_var($GET['tablename_slug'],  FILTER_SANITIZE_STRING)."',
						'',
						'1')
		";
		return self::executeSql($sql);
	}

	function delete($id) {
		$sql = "DELETE FROM ".TABLE_PREFIX.self::TABLE_NAME."
				WHERE id='".$id."'
		";
		return self::executeSql($sql);
	}

}