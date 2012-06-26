<?php

define('APP_NAME','EnergyBat');

define('CLIENT_NAME','EngesGabon'); //TODO find a way to choose client !


if (is_file('../'.APP_NAME.'_'.CLIENT_NAME.'_seed.php'))
{
	define('APP_TYPE','local');
	require_once('../'.APP_NAME.'_'.CLIENT_NAME.'_seed.php');
}
elseif (is_file('../'.APP_NAME.'_'.CLIENT_NAME.'.php'))
{
	define('APP_TYPE','server');
	require_once('../'.APP_NAME.'_'.CLIENT_NAME.'.php');
}
else exit('i need a brain !');

if ($_SERVER['HTTPS'] == 'on') $url = 'https://';
else $url = 'http://';

$url .= $_SERVER['HTTP_HOST'].'/EnergyWatch/base_info.php?pass=k86jnbHGDhcSgdkjbKJHS89398';

$ewatch = file_get_contents($url);
$ewatch = explode('/',$ewatch);

define('MYSQL_HOST_EWATCH',$ewatch[2]);
define('MYSQL_BASE_EWATCH',$ewatch[0]);
define('MYSQL_USER_EWATCH',$ewatch[1]);
define('MYSQL_PASS_EWATCH',$ewatch[3]);

/*
|---------------------------------------------------------------
| PHP ERROR REPORTING LEVEL
|---------------------------------------------------------------
|
| By default CI runs with error reporting set to ALL.  For security
| reasons you are encouraged to change this when your site goes live.
| For more info visit:  http://www.php.net/error_reporting
|
*/
	error_reporting(E_ERROR);

/*
|---------------------------------------------------------------
| SYSTEM FOLDER NAME
|---------------------------------------------------------------
|
| This variable must contain the name of your "system" folder.
| Include the path if the folder is not in the same  directory
| as this file.
|
| NO TRAILING SLASH!
|
*/
	$system_folder = "system";

/*
|---------------------------------------------------------------
| APPLICATION FOLDER NAME
|---------------------------------------------------------------
|
| If you want this front controller to use a different "application"
| folder then the default one you can set its name here. The folder 
| can also be renamed or relocated anywhere on your server.
| For more info please see the user guide:
| http://codeigniter.com/user_guide/general/managing_apps.html
|
|
| NO TRAILING SLASH!
|
*/
	$application_folder = "application";

/*
|===============================================================
| END OF USER CONFIGURABLE SETTINGS
|===============================================================
*/


/*
|---------------------------------------------------------------
| SET THE SERVER PATH
|---------------------------------------------------------------
|
| Let's attempt to determine the full-server path to the "system"
| folder in order to reduce the possibility of path problems.
| Note: We only attempt this if the user hasn't specified a 
| full server path.
|
*/

//add comment to test GIT !

//add third test
//add fourth test

if (strpos($system_folder, '/') === FALSE)
{
	if (function_exists('realpath') AND @realpath(dirname(__FILE__)) !== FALSE)
	{
		$system_folder = realpath(dirname(__FILE__)).'/'.$system_folder;
	}
}
else
{
	// Swap directory separators to Unix style for consistency
	$system_folder = str_replace("\\", "/", $system_folder); 
}

/*
|---------------------------------------------------------------
| DEFINE APPLICATION CONSTANTS
|---------------------------------------------------------------
|
| EXT		- The file extension.  Typically ".php"
| SELF		- The name of THIS file (typically "index.php")
| FCPATH	- The full server path to THIS file
| BASEPATH	- The full server path to the "system" folder
| APPPATH	- The full server path to the "application" folder
|
*/
define('EXT', '.php');
define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));
define('FCPATH', str_replace(SELF, '', __FILE__));
define('BASEPATH', $system_folder.'/');

if (is_dir($application_folder))
{
	define('APPPATH', $application_folder.'/');
}
else
{
	if ($application_folder == '')
	{
		$application_folder = 'application';
	}

	define('APPPATH', BASEPATH.$application_folder.'/');
}

/*
|---------------------------------------------------------------
| LOAD THE FRONT CONTROLLER
|---------------------------------------------------------------
|
| And away we go...
|
*/

require_once BASEPATH.'codeigniter/CodeIgniter'.EXT;

/* End of file index.php */
/* Location: ./index.php */
