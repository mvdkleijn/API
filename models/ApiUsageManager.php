<?php
/*
 * API plugin for Wolf CMS
 * November 2009
 * @author Ian Dundas for Band-x.org
 */
class ApiUsageManager {

	const TABLE_NAME = 'api_usage';

	function __construct() {
		global $__CMS_CONN__;
		$this->db = $__CMS_CONN__;
	}

	#TODO: need to sterilise data
	# api_auth_id, accesstime, api_method, ip_address, api_result
	function logApiUsage($array)
	{
		if (!array_key_exists('accesstime', $array)){
				$array['accesstime']=time();
		}if (!array_key_exists('api_result', $array)){
				$array['api_result']='unknown';
		}if (!array_key_exists('ip_address', $array)){
				$array['ip_address']=$_SERVER['REMOTE_ADDR'];
		}if (!array_key_exists('api_auth_id', $array)){
			#	TODO get here: handle, might be an authorised access which shold be logged anyway
		};
		$false=false;
		$sql='INSERT INTO '.TABLE_PREFIX.self::TABLE_NAME;
		$values = ' ';
		foreach ($array as $key=>$value){
			$values .= ($false)?',':'SET ';
			$values .= " {$key} = '{$value}'";
			$false=true;			
		}
		$sql.=$values;
		$this->executeSql($sql);
	}


	function executeSql($sql) {
		$stmt = $this->db->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	function usageList($id=NULL) {
		$usagetable = TABLE_PREFIX.self::TABLE_NAME;
		$sql = "SELECT * FROM {$usagetable}";

		if($id) $sql .= " WHERE id='$id'";
		//$sql .= "	GROUP BY {$usagetable}.api_method";
		$sql .= "	ORDER BY {$usagetable}.accesstime DESC";
		
		$result = self::executeSql($sql);
		return $result;
	}

	function methodUsage()
	{
		$methods = $this->getAllUsedMethods();
		$usagemetrics = array();
		foreach($methods as $method)
		{
			$temp=array("method_name"=>$method['api_method']);

			#get method stats:
			$sql =  "SELECT count(*) as total_hits, FROM_UNIXTIME(av_api_usage.accesstime) as last_accessed, api_result
					FROM ".TABLE_PREFIX.self::TABLE_NAME."
					WHERE id = (SELECT id from av_api_usage WHERE api_method='".$temp['method_name']."' ORDER BY accesstime DESC LIMIT 1)
					ORDER BY accesstime DESC";

			list($metrics)=$this->executeSql($sql);
			$usagemetrics[]=array_merge($temp,$metrics);
		}
		return $usagemetrics;
	}

	function getAllUsedMethods()
	{
		$usagetable = TABLE_PREFIX.self::TABLE_NAME;
		$sql = "	SELECT api_method FROM {$usagetable}";
		$sql .= "	GROUP BY {$usagetable}.api_method";
		$sql .= "	ORDER BY api_method ASC";

		$result = self::executeSql($sql);
		return $result;
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




/*
 * Following functions lifted from bbPress. TODO: integrate better, refactor and move.
 * dev.
 */
// GMT -> so many minutes ago
public static function bb_since( $original, $do_more = 0 ) {
	$today = time();

	if ( !is_numeric($original) ) {
		if ( $today < $_original = self::bb_gmtstrtotime( str_replace(',', ' ', $original) ) ) // Looks like bb_since was called twice
			return $original;
		else
			$original = $_original;
	}

	// array of time period chunks
	$chunks = array(
		( 60 * 60 * 24 * 365 ), // years
		( 60 * 60 * 24 * 30 ),  // months
		( 60 * 60 * 24 * 7 ),   // weeks
		( 60 * 60 * 24 ),       // days
		( 60 * 60 ),            // hours
		( 60 ),                 // minutes
		( 1 )                   // seconds
	);

	$since = $today - $original;

	for ($i = 0, $j = count($chunks); $i < $j; $i++) {
		$seconds = $chunks[$i];

		if ( 0 != $count = floor($since / $seconds) )
			break;
	}

	$trans = array(
		self::_n( '%d year', '%d years', $count ),
		self::_n( '%d month', '%d months', $count ),
		self::_n( '%d week', '%d weeks', $count ),
		self::_n( '%d day', '%d days', $count ),
		self::_n( '%d hour', '%d hours', $count ),
		self::_n( '%d minute', '%d minutes', $count ),
		self::_n( '%d second', '%d seconds', $count )
	);

	$print = sprintf( $trans[$i], $count );

	if ( $do_more && $i + 1 < $j) {
		$seconds2 = $chunks[$i + 1];
		if ( 0 != $count2 = floor( ($since - $seconds * $count) / $seconds2) )
			$print .= sprintf( $trans[$i + 1], $count2 );
	}
	return $print;
}

#ian hack
public static function _n($single, $plural, $number, $domain = 'default') {
	if ($number>1)return $plural;
	else return $single;
}

public static function bb_gmtstrtotime( $string ) {
	if ( is_numeric($string) )
		return $string;
	if ( !is_string($string) )
		return -1;

	if ( stristr($string, 'utc') || stristr($string, 'gmt') || stristr($string, '+0000') )
		return strtotime($string);

	if ( -1 == $time = strtotime($string . ' +0000') )
		return strtotime($string);

	return $time;
}


}