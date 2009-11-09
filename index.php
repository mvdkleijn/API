<?php
/* @author Ian Dundas with Band-x.org */

define('API_VIEWS_BASE', 'api/views');
Plugin::setInfos(array(
	'id'		=> 'api',
	'title'		=> 'API',
	'description'   => 'Ian\'s wicked API, dev1',
	'version'       => '0.1',
    'type'		=>	'both'
));

Plugin::addController('api', 'Api', 'administrator,developer', TRUE);

if (defined('CMS_BACKEND')) {
	#load different controller here? couldn't work out how to do this.
	Dispatcher::addRoute(array(
		'/api/userauth/:num'=> '/plugin/api/userauth/$1',
		'/api/userauth/byauthid/:num'=> '/plugin/api/userAuthByAuthID/$1',
		'/api/userauth'=> '/plugin/api/userauth',
		
		'/api/methodusage/:num' => '/plugin/api/methodusage/$1',
		'/api/methodusage' => '/plugin/api/methodusage',

	));
} else {
	
	Dispatcher::addRoute(array(

		'/api'				=>	'/plugin/api/status',

		#BUG: preg_replace in Framework won't accept :any.:any, so have to define .json and .xml manually

		#/api/method/format/id
		'/api/:any/:any/:num'            =>	'/plugin/api/fetchbyid/$1/$2/$3',
		#/api/method/format
		'/api/:any/:any'                 =>	'/plugin/api/fetchall/$1/$2',
	));

}
include('models/ApiManager.php');
include('models/ApiUsageManager.php');
include('models/UserAuthManager.php');