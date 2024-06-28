<?php
//kPlaylist 1.5 Build 388 (30-12-04_10.51)

/*****************************************************************************
kPlaylist is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

kPlaylist is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with kPlaylist; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
##############################################################################

kPlaylist v1.5 makes your MP3 archive available via the WEB. Play music, 
	search, create and edit playlists from everywhere by just having a webbrowser 
	and a MP3 player. Features include logon, accounts, account classes, user editor, 
	automatic installation (MySQL) and automatic search engine update. 

Are you a PHP programmer? 
	Would you like to join us in the creation of this product? Before you start 
	changing the code please send a mail to us and tell us that you want to help us. 
	We'll send you some information on how you can  send us upgrade information and 
	how to get the latest up2date source. We got a development source available.

Translate or errors in the grammar?
	Please submit new languages, or grammar fixes directly to us for immediate
	new builds. Se http://www.kplaylist.net/ for more information.

	Our website helps you to create new languages. Please look there if your
	language is missing.

Note!
	You can get updates and installation instructions here: http://www.kplaylist.net
  
	Need answers? Goto the kPlaylist forum: http://www.kplaylist.net/forum/
	
	We develop other products than PHP applications, for commercial and non
	commercial use. Contact our company Keyteq AS here: http://www.keyteq.no.

Script information:
	Also note, this is a script under construction and strange things may happen,
	though it hasn't on the machines we tested it on. The system writes by
	default only to a MySQL database, but can also be set up to write
	id3v1 tags (mp3 files.).

	Due to the legal responsibility however, we have to note: There
	are NO GUARANTEES WHATSOEVER other than this application will
	occupy certain amount of space on the device you put it.

*****************************************************************************/


// try to set the execution time to 3600 sec = 60 min. 
@ini_set('max_execution_time', 3600);
@ini_set('register_globals', 'Off');
@ini_set('display_errors', 'Off');

// what to prepend before the db, don't change this after installing! Do it before. If you do after, change the db names manually or set enable_install = 1.

$cfg['dbprepend'] = 'tbl_';

######################################################
## START OF EASY CONFIGURATION
######################################################

#  STEP 1
#  FIRST THING FIRST: START YOUR WEBBROWSER,
#  AND POINT IT TO THIS SCRIPT.
#

# NOTE: Default login user is: admin, password: admin

# STEP 2
# Have fun! Remember to click settings and change
# base_dir to somewhere you have audio files :)

######################################################
## END OF EASY CONFIGURATION
######################################################

//////////////////////////////////////////////////////////////////////
// IMPORTANT!! READ THIS!!!
// Before changing the database information; you should be aware that the 
// installer-script will start when you point your browser to 
// this script and create (AND EVEN DROP DATABASE if the checkbox 
// 'drop database' is checked on the installer page)
// You should NOT need to change the default information below.
// The automatic installer will create both database and the mysql user.
//////////////////////////////////////////////////////////////////////


// if you for any reason want to reset the configuration, set this variable to true, reload the page and set it back to false.
$resetconfiguration = false;

$db = array(
	'host' => 'localhost', # MySql server
	'name' => 'kplaylist', # Database name
	'user' => 'kplaylist', # MySql user
	'pass' => 'kplaylist'  # MySql password
);


// If you use the Bad Blue webserver, set the following value to 1
$cfg['badblue'] = 0;

// Read here before enabling: http://www.kplaylist.net/forum/viewtopic.php?t=196
$cfg['id3editor'] = 0;

// If you want to disable logins and let everybody with http access to your
// site get in, change the two following options. (WARNING! ALL SECURITY NOW VANISH.)
$cfg['disablelogin'] = 0;

// If you disable logins, a default user has to be chosen. Setting this to 1 means
// the first user which is usually the admin.
$cfg['assumeuserid'] = 1;

// enable the getid3 package. getid package must reside under getid3/ under the directory
// this file exists. If it does not, please change the 'include' statement below.
$cfg['enablegetid3'] = 0;

//how many titles of one album do we need to treat as a album? Turn to zero to show all.
$cfg['titlesperalbum'] = 0;

// demo mode on/off. Default off.
$cfg['demomode'] = 0; 

// for multiple downloads.
$cfg['archivemode'] = false;

$cfg['archivefilelist_cr'] = "\n";

// turn this on to show commands when creating instead of executing
$cfg['archivemodedebug'] = false;

// where archivemode stores data. For UNIX it should be /tmp/, For win32 it should be: c:\\tmp\\
$cfg['archivetemp'] = '/tmp/'; 

// cookie name
$cfg['cookie'] = 'kplaylist';

// list of directories to ignore.
$cfg['dirignorelist'] = array('..' => 1, '.' => 1, 'lost+found' => 1);

// For use of automatic search engine update via lynx / cron. Turn to 1 to enable. Check
// www.kplaylist.net for information how to run this update automatically.
$cfg['autoupdate'] = 0;
$cfg['autoupdatehost'] = '127.0.0.1';
$cfg['autoupdateuser'] = 'autooperate';

// what date format to use. if you want to change, look here: http://php.net/date/ for the format
$cfg['dateformat'] = 'd.m.y H:i:s';

// small format
$cfg['smalldateformat'] = 'd.m.y';

// if the dir count exceeds this count, it will not be considered a 'album' directory and albums will not be shown
$cfg['isalbumdircount'] = 1;

// sort the root? does not affect sorting in subdirs.
$cfg['sortroot'] = true;

// where to cut the front bulletin message
$cfg['frontbulletinchars'] = 100;

$lamebitrates = array(0, 32, 40, 48, 56, 64, 80, 96, 112, 128, 160, 192, 224, 256, 320);

//	enabled	(0/1)	name	cmd	(%D = destination file,	%F source OR %LIST if using filelist.)
// YOU MUST SUIT THESE ARCHIVERS TO YOUR OWN NEED. DO NOT USE THE DEFAULT BLINDLY.
$archivers = array(
	0 => array(1,	'zip', '/usr/bin/zip -j -0 %D "%F"', 'application/zip'),
	1 => array(1,	'rar', 'C:\Programfiler\WinRAR\rar.exe -m0 a %D "%F"', 'application/x-rar'),
	2 => array(0,	'rar2', 'C:\Programfiler\WinRAR\rar.exe -m0 a %D @"%LIST"', 'application/x-rar')
);

// stream 'engine' finetune settings. 
$streamsettings = 
array(
	'preload'		=> 175,
	'buffer'		=> 100,
	'sleep'			=> 0.999,
	'bitrates'		=> array(96,128,160,192,256),
	'defaultrate'	=> 256,
	'precision'		=> 1000
);

// syntax: .filename, mime header, file in M3U, get id function, viewable, log access
$streamtypes_default = array(
	0 => array	('mp3',		'audio/mpeg',				1, 1, 1, 1),
	1 => array	('mp2',		'audio/mpeg',				1, 1, 1, 1),
	2 => array	('ogg',		'application/x-ogg',		1, 2, 1, 1),
	3 => array	('wav',		'audio/wave',				1, 0, 1, 1),
	4 => array	('wma',		'audio/x-ms-wma',			1, 0, 1, 1),
	5 => array	('mpg',		'video/mpeg',				0, 0, 1, 1),
	6 => array	('mpeg',	'video/mpeg',				0, 0, 1, 1),
	7 => array	('avi',		'video/avi',				0, 0, 1, 1),
	8 => array	('wmv',		'video/x-ms-wmv',			0, 0, 1, 1),
	9 => array	('asf',		'application/vnd.ms-asf',	0, 0, 1, 1),
	10 => array	('m3u',		'audio/x-mpegurl',			0, 0, 1, 0),
	11 => array	('flac',	'audio/x-flac',				1, 0, 1, 1),
	12 => array	('jpg' ,	'image/jpeg',				0, 0, 1, 0),
	13 => array	('gif' ,	'image/gif',				0, 0, 1, 0),
	14 => array	('png' ,	'image/png',				0, 0, 1, 0)
); 

if (file_exists('kpconfig.php')) include('kpconfig.php');

// end of configuration


$app_ver  = 1.5;
$app_build = 388;


$kpdbtables = array('playlist', 'playlist_list', 'search', 'users', 'kplayversion', 'mhistory', 'config', 'filetypes', 'settings', 'bulletin');
foreach ($kpdbtables as $name) define('TBL_'.strtoupper($name), $cfg['dbprepend'].$name);

if ($cfg['enablegetid3']) 
{
	include('getid3/getid3.php');
	if (defined('GETID3VERSION')) define('GETID3_V', 16); 
		else
	if (defined('GETID3_VERSION')) define('GETID3_V', 17); 		
}

function db_gconnect()
{
	global $db;
	if (mysql_connect($db['host'], $db['user'], $db['pass']) && mysql_select_db ($db['name'])) return true;
	return false;
}

function db_execquery($query, $fast=false)
{
	if ($fast && function_exists('mysql_unbuffered_query')) return mysql_unbuffered_query($query);
	return mysql_query($query);
}

function db_execcheck($query)
{
	if (db_gconnect()) return mysql_query($query); else return 0;	
}

class settings
{
	var $keys;
	var $keysvtype;
	var $dbperform = true;
	var $defaults;
	var $defaultsloaded = false;

	function setdbperform($dbperform)
	{
		$this->dbperform = $dbperform;
	}

	function update($key, $value, $vtype = 0)
	{
		if (!isset($this->keys[$key])) 
			$sql = 'INSERT INTO '.TBL_CONFIG.' SET `key` = "'.$key.'", value = "'.mysql_escape_string($value).'", vtype = '.$vtype; 
		else $sql = 'UPDATE '.TBL_CONFIG.' SET value = "'.mysql_escape_string($value).'", vtype = '.$vtype.' WHERE `key` = "'.$key.'"'; 
		
		if ($this->dbperform) db_execquery($sql, true);		
		$this->keys[$key] = $this->recast($value, $vtype);
	}

	function get($key)
	{
		if (!isset($this->keys[$key])) 
		{
			$this->loaddefaults();
			if (isset($this->defaults[$key]))
			{
				$sql = 'INSERT INTO '.TBL_CONFIG.' SET `key` = "'.$key.'", value = "'.mysql_escape_string($this->defaults[$key][0]).'", vtype = '.$this->defaults[$key][1];
				if ($this->dbperform) db_execquery($sql, true);				
				$this->insert($key, $this->defaults[$key][0], $this->defaults[$key][1]);
			} else return false;
		}
		return $this->keys[$key];
	}

	function set($key, $value)
	{
		if (isset($this->keys[$key])) 
		{
			$sql = 'UPDATE '.TBL_CONFIG.' SET value = "'.mysql_escape_string($value).'" WHERE `key` = "'.$key.'"'; 
			if ($this->dbperform) db_execquery($sql, true);			
			$this->keys[$key] = $this->recast($value, $this->keysvtype[$key]);
		}
	}

	function publish($key)
	{
		if (isset($this->keys[$key])) define(strtoupper($key), $this->keys[$key]); 
		else 
		{
			$this->loaddefaults();
			if (isset($this->defaults[$key])) 
				define(strtoupper($key), $this->defaults[$key][0]); 
			else define(strtoupper($key), 0);
		}
	}

	function recast($value, $vtype)
	{
		switch ($vtype)
		{
			case 0: return $value;
			case 1: return (bool) $value;
			case 2: return (int) $value;
			case 3: return (float) $value;
			default: break;
		}
	}

	function getchecked($key, $defaultvalue = 0, $vtype = 1)
	{
		$this->get($key, $defaultvalue, $vtype);
		if ($this->keys[$key]) return 'checked="checked"'; 
	}

	function insert($key, $value, $vtype)
	{
		$this->keys[$key] = $this->recast($value, $vtype);
		$this->keysvtype[$key] = $vtype;
	}

	function fetch_old()
	{
		$res = db_execquery('SELECT * FROM '.TBL_SETTINGS);
		$row = mysql_fetch_assoc($res);
		if ($row)
		{
			@$this->update('allowseek', $row['s_allowseek'], 1);
			@$this->update('allowdownload', $row['s_allowdownload'], 1);
			@$this->update('base_dir', $row['s_base_dir'], 0);
			@$this->update('streamlocation', $row['s_streamlocation'], 0);
			@$this->update('default_language', $row['s_default_language'], 2);
			@$this->update('windows', $row['s_windows'], 1);
			@$this->update('timeout', $row['s_timeout'], 2);
			@$this->update('require_https', $row['s_require_https'], 1);
			@$this->update('report_attempts', $row['s_report_attempts'], 1);
			@$this->update('streamingengine', $row['s_streamingengine'], 1);
			@$this->update('usersignup', $row['u_usersignup'], 1);
			@$this->update('externimagespath', $row['s_externimagespath'], 0);
			@$this->update('dlrate', $row['dlrate'], 2);
			if (isset($row['s_streamurl'])) $this->update('streamurl', $row['s_streamurl'], 0); 
			@$this->update('externalcss', $row['s_externalcss'], 0);
		}		
	}

	function loaddefaults()
	{
		global $app_ver, $app_build;
		if (!$this->defaultsloaded)
		{
			$this->defaults = array(
				'windows'					=> array(0, 1),
				'allowseek'					=> array(1,1),
				'allowdownload'				=> array(1,1),
				'base_dir'					=> array('/path/to/my/music/archive/', 0),
				'streamlocation'			=> array('', 0),
				'default_language'			=> array(0, 2),
				'timeout'					=> array(43200, 2),
				'require_https'				=> array(0, 1),
				'report_attempts'			=> array(1,1),
				'streamingengine'			=> array(0, 1),
				'usersignup'				=> array(0, 1),
				'externimagespath'			=> array('', 0),
				'dlrate'					=> array(0, 2),
				'streamurl'					=> array('http://', 0),
				'externalcss'				=> array('', 0),
				'includeheaders'			=> array(1, 1),
				'homepage'					=> array('http://www.kplaylist.net/&#63;ver=KVER&amp;build=KBUILD', 0),
				'unauthorizedstreams'		=> array(0, 1),
				'sendfileextension'			=> array(1, 1),
				'disksync'					=> array(1, 1),
				'externaljavascript'		=> array('', 0),
				'showkeyteq'				=> array(1,1),
				'showupgrade'				=> array(1,1),
				'showstatistics'			=> array(0, 1),
				'writeid3v2'				=> array(0, 1),
				'unauthorizedstreamsextm3u'	=> array(0, 1),
				'optimisticfile'			=> array(1, 0),
				'lamesupport'				=> array(1, 0),
				'smtphost'					=> array('127.0.0.1', 0),
				'smtpport'					=> array('25', 0),
				'enableupload'				=> array(0, 1),
				'uploadpath'				=> array('', 0),
				'mailmp3'					=> array(0, 1),
				'lamecmd'					=> array('/usr/local/bin/lame --silent --nores --mp3input -h -m s -b %bitrate% "%file%" -', 0),
				'albumcover'				=> array(1, 1),
				'albumfiles'				=> array('*album*.jpg,*album*.gif,*cover*.jpg,*cover*.gif,*front*.jpg,*front*.gif', 0),
				'albumresize'				=> array(1, 1),
				'albumheight'				=> array(320, 2),
				'albumwidth'				=> array(400, 2),
				'mailmethod'				=> array(2,2),
				'albumurl'					=> array('http://www.last.fm/music/%artist/%album', 0),
				'fetchalbum'				=> array(0, 1),
				'bulletin'					=> array(1, 1)
			);
			$this->defaultsloaded = true;
		}		
	}

	function defaults()
	{
		$this->loaddefaults();
		foreach ($this->defaults as $name => $value) 
			$this->update($name, $this->defaults[$name][0], $this->defaults[$name][1]);
	}

	function load()
	{
		$this->keys = array();
		$this->keysvtype = array();
		$res = db_execquery('SELECT * FROM '.TBL_CONFIG);
		if ($res)
		{
			if (mysql_num_rows($res) > 0) 
				while ($row = mysql_fetch_row($res)) $this->insert($row[1], $row[2], $row[3]); 
			else $this->fetch_old(); 
		}
	}
}


$setctl = new settings();

if (db_gconnect())
{
	define('DBCONNECTION', true);
	$enable_install = 0;
	$setctl->load();
	if ($resetconfiguration) 
	{
		$setctl->defaults();
		echo 'Configuration has been reset. Set $resetconfiguration = false; and reload.';
		die();
	}
} else
{
	define('DBCONNECTION', false);
	$enable_install = 1;
	$setctl->setdbperform(false);
	$setctl->defaults();
}

$setctl->publish('allowdownload');
$setctl->publish('allowseek');
$setctl->publish('require_https');
$setctl->publish('usersignup');
$setctl->publish('optimisticfile');
$setctl->publish('mailmp3');
$setctl->publish('enableupload');
$setctl->publish('unauthorizedstreams');
$setctl->publish('albumcover');
$setctl->publish('mailmethod');
$setctl->publish('fetchalbum');
$setctl->publish('disksync');
$setctl->publish('bulletin');

$base_dir = explode(';', $setctl->get('base_dir'));
$deflanguage = $setctl->get('default_language');
$win32 = $setctl->get('windows');
if (!$win32) $dlrate = $setctl->get('dlrate'); else $dlrate = 0;

// General stuff used as globals

$userauth = $gCnt = 0;
$u_playlist = $u_playlistid = $dir_list = $gData = $mark = $validated_user = array();
$marksid = $u_cookieid = $u_id = -1;
$pdir64 = '';

if (isset($_GET['d'])) $curdrive = $_GET['d']; else if (isset($_POST['drive'])) $curdrive = $_POST['drive']; else $curdrive = 0;

$phpenv = array();

if (!isset($PHP_SELF) || empty($PHP_SELF)) $PHP_SELF = $_SERVER['PHP_SELF'];

if ($cfg['badblue'])
{
	$qpos = strrpos($PHP_SELF, '?');
	if ($qpos !== false) $PHP_SELF = substr($PHP_SELF, 0, $qpos);
}

function phpfigure()
{
	global $phpenv, $setctl, $PHP_SELF, $_SERVER;

	$phpenv['streamlocation'] = $setctl->get('streamlocation');
	if (empty($phpenv['streamlocation']))
	{
		if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] != 80 && $_SERVER['SERVER_PORT'] != 443) $streamport = ':'.$_SERVER['SERVER_PORT']; else $streamport = '';
		$host = '';
		if (isset($_SERVER['HTTP_HOST'])) 
			$host = $_SERVER['HTTP_HOST'];
		else 
		if (isset($_SERVER['SERVER_NAME'])) $host = $_SERVER['SERVER_NAME'].$streamport;

		$i = @strpos('php.exe', strtolower($_SERVER['SCRIPT_NAME']));
		if ($i !== false)
			$script = $_SERVER['SCRIPT_NAME'];
		else
			$script = $PHP_SELF;

		$phpenv['streamlocation'] = $host.$script;
	}
	$phpenv['remote'] = $_SERVER['REMOTE_ADDR'];
	$phpenv['useragent'] = @$_SERVER['HTTP_USER_AGENT'];
	$phpenv['https'] = false;
	if (isset($_SERVER['HTTPS'])) 
	{
		$phpenv['https'] = true;
		if (stristr($_SERVER['HTTPS'],'off')) $phpenv['https'] = false;
	}
}

phpfigure();

if (DBCONNECTION)
{
	$streamtypes = $streamtypes_default;
	$res = db_execquery('SELECT extension, mime, m3u, getid, search, logaccess FROM '.TBL_FILETYPES.' WHERE enabled = 1', true);
	if ($res) while ($row = mysql_fetch_row($res)) $streamtypes[] = $row;
} else $streamtypes = array();


function verchar($in)
{
	if ($in == '1' || $in == '0') return $in; else return 0;
}

function vernum($in)
{
	if (is_numeric($in)) return $in; else return 0;
}

function vernumset($in, $value)
{
	if (is_numeric($in)) return $in; else return $value;
}

function checkchs($in)
{
	return htmlentities($in, ENT_QUOTES);
}

function make_seed() {
    list($usec, $sec) = explode(' ', microtime());
    return (float) $sec + ((float) $usec * 100000);
}

function timeresults($name)
{
	global $kqm;
	if (class_exists('kqMeasure') && $kqm)
	{
		$kqm->stop();
		user_error('Operation '.$name.' took '.$kqm->resultinsec(3));	
	}
}

function refreshurl($url)
{
	?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
	<html>
		<head>
			<title></title>
			<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
			<meta http-equiv="Refresh" content="0; url=<?php echo $url; ?>"/>
		</head> 
		<body></body>
	</html>
	<?php
}

function syslog_write($msg)
{
	global $phpenv, $win32;
	$msg = "Client ".$phpenv['remote']." ".$phpenv['useragent']." $msg";
	if (!$win32)
	{
		define_syslog_variables();
		openlog("kplaylist", LOG_PID | LOG_PERROR, LOG_LOCAL0);
		syslog(LOG_INFO,$msg);
		closelog();
	} else user_error($msg);
}

function selected($val, $ret = 'selected="selected"', $uret = '')
{
	if ($val) return $ret;
	return $uret;
}

function checked($val, $ret = 'checked="checked"', $uret = '')
{
	if ($val) return $ret;
	return $uret;
}

function getrand($from = 1, $to = 0)
{
	$randmax = getrandmax();
	srand((double)microtime()*1000000);
	if ($to > 0 && $to < $randmax) return rand($from,$to);
	return rand($from,$randmax);
}

function lzero($in, $len=2)
{
	if (strlen($in) >= $len) return $in; 
		else
	return (substr('00000000',0,$len - strlen($in)).$in);
}

function slashtranslate($in,$key='\\', $rep='/')
{
	$out = $in;
	if (strlen($in) > 0)
		$out = str_replace($key, $rep, $in);
	return stripslashes($out);
}

function getimagelink($image)
{
	global $PHP_SELF, $setctl;
	if (!empty($setctl->keys['externimagespath'])) return $setctl->get('externimagespath').$image; else return $PHP_SELF.'?image='.$image;
}


$klang[0] = array('English', 'ISO-8859-1', 'English', 'What\'s hot', 'What\'s new', 'Search', '(only %1 shown)', 'sec', 'Search results: \'%1\'', 'found', 'None.', 'update search database options', 'Delete unused records?', 'Rebuild ID3?', 'Debug mode?', 'Update', 'Cancel', 'update search database', 'Found %1 files.', 'Could not determine this file: %1, skipped.', 'Installed: %1 - Update: %2, scan: ', 'Scan: ', 'Failed - query: %1', 'Could not read this file: %1. Skipped.', 'Removed: %1', 'Inserted %1, updated %2, deleted %3 where %4 failed and %5 skipped through %6 files - %7 sec - %8 marked for deletion.', 'Done', 'Close', 'Could not find any files here: "%1"', 'kPlaylist Logon', 'Album list for artist: %1', 'Hotselect %1', 'No tunes seleted. Playlist not updated.', 'Playlist updated!', 'Back', 'Playlist added!', 'Remember to reload page.', 'login:', 'secret:', 'Notice! This is a non public website. All actions are logged.', 'Login', 'SSL required for logon.', 'Play', 'Delete', 'Shared: ', 'Save', 'Control playlist: "%1" - %2 titles', 'Editor', 'Viewer', 'Select', 'Seq', 'Status', 'Info', 'Del', 'Name', 'Totals:', 'Error', 'Action on selected: ', 'Sequence:', 'edit playlist', 'Delete this entry', 'add playlist', 'Name:', 'Create', 'Play: ', 'File', 'Album', 'All', 'Selected', 'add', 'play', 'edit', 'new', 'Select:', 'Play Control: ', 'Playlist: ', 'Hotselect numeric', 'Keyteq gives you:', '(check for upgrade)', 'Homesite', 'only id3', 'album', 'title', 'artist', 'Hotselect album from artist', 'view', 'Shared playlists', 'Users', 'Admin control', 'What\'s new', 'What\'s hot', 'Logout', 'Options', 'Check', 'My', 'edit user', 'new user', 'Full name', 'Login', 'Change password?', 'Password', 'Comment', 'Access level', 'On', 'Off', 'Delete user', 'Logout user', 'Refresh', 'New user', 'del', 'logout', 'Use EXTM3U feature?', 'Show how many rows (hot/new)', 'Max search rows', 'Reset', 'Open directory', 'Go to directory: %1', 'Download', 'Go one step up', 'Go to root directory.', 'Check for upgrade', 'users', 'Language', 'options', 'Booted', 'Shuffle:', 'Settings', 'Base directory', 'Stream location', 'Default language', 'A Windows system', 'Require HTTPS', 'Allow seek', 'Allow download', 'Session timeout', 'Report failed login attempts', 'Hold on - fetching file list', 'Playlist could not be added!', 'Admin', 'Login with HTTPS to change!', 'Enable streaming engine', 'Title', 'Artist', 'Album', 'Comment', 'Year', 'Track', 'Genre', 'not set', 'Max download rate (kbps)', 'User', '%1 mins - %2 titles', '%1 kbit %2 mins', 'Genre list: %1', 'Go', '%1d %2h %3m playtime %4 files %5 mb', 'No relevant resources here.', 'Password changed!', 'Signup', 'Please make a selection!', 'What is update?', 'Click here for help', 'Use external images?', 'External images path', 'Current password', 'Current password does not match!', 'Preferred archiver', 'Archive could not be made', 'Probable file duplicate found:  "%1" "%2"', 'Really delete playlist?', 'Alphabetical', 'Random', 'Sort', 'Original', 'Use javascript', 'Are you sure you want to delete this user?', 'View history', 'history', 'Rows', 'External CSS file', 'Remove duplicates', 'OK', 'ERR', 'Stream', '(show as)', 'files', 'albums', '%1d %2h %3m %4s', 'General', 'Customize', 'Filehandling', 'Click on ? for help.', 'Automatic database sync', 'Send file extension', 'Allow unauthorized streams', 'Include headers', 'External javascript', 'Homepage', 'Show Keyteq gives you part', 'Show upgrade part', 'Show statistics', 'Write ID3v2 with stream', 'Enable user signup', 'Filetypes', 'Yes', 'No', 'Extension', 'MIME', 'Include in M3U', 'edit filetype', 'Sure?', 'Optimistic filecheck', 'Randomizer', 'Mode', 'Playlist', 'None, directly', 'My favourites', 'Did not find any hits', 'Alltime hits', 'Order', 'Enable LAME support?', 'Disabled', 'Allow LAME usage?', 'Email', 'Allow to mail files?', 'SMTP server', 'SMTP port', 'Mail to', 'Message', 'Send', 'Mail sent!', 'Activate upload', 'Upload directory', 'Activate mp3mail', 'Upload', 'File uploaded!', 'File could not be uploaded!', 'You must enable cookies to log in!', 'Period', 'ever', 'this week', 'this month', 'last month', 'hits', 'LAME command', 'Show album cover', 'Album files', 'Resize album images', 'Album height', 'Album width', 'Mail method', 'Direct', 'Pear', 'Wait!', 'Please enter a valid e-mail in options!', 'Playlists inline?', 'Show album from URL?', 'Album URL', 'Could not send!', 'User added!', 'Archive creator', 'Archive is deleted.', 'User updated!','Music match', '%1 entries filtered','Log access','Viewable', 'Archived','Bulletin','Entered %1 by %2','more', 'Publish','%1 mb', '%1 kb', '%1 bytes');

$klang[1]  = array('Norwegian', 'ISO-8859-1', 'Norsk', 'Hva er mest spilt', 'Hva er nytt', 'S�k', '(bare %1 vist)', 'sek', 'S�ke resultater: \'%1\'', 'fant', 'Ingen.', 'oppdater s�ke database valg', 'Slett ubrukte rader?', 'Ombygg ID3?', 'Debug modus?', 'Oppdater', 'Avbryt', 'oppdaterer s�ke database', 'Fant %1 filer.', 'Kunne ikke lese fil: %1, hoppet over.', 'Installert: %1 - Oppdaterer: %2, skanner: ', 'Skanner: ', 'Feilet - query: %1', 'Kunne ikke lese denne filen: %1. Hoppet over.', 'Fjernet: %1', 'La inn %1, oppdaterte %2, slettet %3 hvor %4 feilet og %5 ble hoppet over igjennom %6 filer - %7 sek - %8 markert for sletting.', 'Ferdig', 'Lukk', 'Kunne ikke finne noen filer her: "%1"', 'kPlaylist Innlogging', 'Album liste fra artist: %1', 'Hurtigvelg %1', 'Ingen l�ter valgt. L�tliste ikke oppdatert.', 'L�tliste oppdatert!', 'Tilbake', 'L�tliste lagt til!', 'Husk � oppdatere side.', 'logg inn:', 'hemmelighet:', 'Advarsel! Dette er en privat webside. Alle handlinger blir logget.', 'Logg inn', 'SSL kreves for p�logging.', 'Spill', 'Slett', 'Delte: ', 'Lagre', 'Kontroller l�tliste: "%1" - %2 titler', 'Editor', 'Viser', 'Velg', 'Sek', 'Status', 'Info', 'Slett', 'Navn', 'Totalt:', 'Feil', 'Handling p� valgte: ', 'Sekvens:', 'editer l�tliste', 'Slett denne raden', 'ny l�tliste', 'Navn:', 'Lag', 'Spill: ', 'Fil', 'Album', 'Alle', 'Valgte', 'legg til', 'spill', 'editer', 'ny', 'Velg:', 'Spille kontroll: ', 'L�tliste: ', 'Hurtigvelg numerisk', 'Keyteq gir deg:', '(sjekk for ny versjon)', 'Hjemmeside', 'bare id3', 'album', 'tittel', 'artist', 'Hurtigvelg album fra artist', 'vis', 'Delte l�tlister', 'Brukere', 'Admin kontroll', 'Hva er nytt', 'Mest spilt', 'Logg ut', 'Valg', 'Sjekk', 'Min', 'editer bruker', 'ny bruker', 'Fullt navn', 'Brukernavn', 'Endre passord?', 'Passord', 'Kommentar', 'Aksess niv�', 'P�', 'Av', 'Slett bruker', 'Logg ut bruker', 'Oppdater', 'Ny bruker', 'slett', 'logg ut', 'Bruke EXTM3U egenskap?', 'Vise hvor mange rader (mest spilt/nytt)', 'Maks s�ke rader', 'Omsetting', '�pne katalog', 'G� til katalog: %1', 'Last ned', 'G� ett steg opp', 'G� til kjerne katalog.', 'Sjekk for ny versjon', 'brukere', 'Spr�k', 'valg', 'Avsperret', 'Omskuff:', 'Innstillinger', 'Arkiv katalog', 'Nedlastningslokalisasjon', 'Standard spr�k', 'Et Windows system', 'Krev HTTPS', 'Tillat spoling', 'Tillat nedlastninger', 'Innloggingstidsavbrudd', 'Rapportere mislykkete p�loggingsfors�k', 'Vent - skaper filliste', 'Spilleliste kunne ikke bli lagt til!', 'Admin', 'Logg inn med HTTPS for � endre!', 'Aktiver innebygd kanalvirkning', 'Tittel', 'Artist', 'Album', 'Kommentar', '�r', 'L�tnummer', 'Stil', 'ikke satt', 'Maksimal nedlastningshastighet', 'Bruker', '%1 minutter - %2 titler', '%1 kbit %2 minutter', 'Stil liste: %1', 'G�', '%1d %2t %3m spilletid %4 filer %5 mb', 'Ingen relevante ressurser her.', 'Passord endret!', 'Ny bruker', 'Vennligst foreta et valg!', 'Hva er oppdatering?', 'Klikk her for hjelp', 'Bruk eksterne bilder?', 'Eksterne bilder lokalisasjon', 'Eksisterende passord', 'Det eksisterende passordet stemmer ikke!', '�nsket arkiverer', ' Arkiv kunne ikke bli laget', 'Sannsynlig duplikat ble funnet: %1 - %2', 'Virkelig slette spilleliste?', 'Alfabetisk', 'Tilfeldig', 'Sorter', 'Original', 'Bruke javascript', 'Sikker p� at du vil slette denne brukeren?', 'Vis historie', 'historie', 'Rader', 'Ekstern CSS fil', 'Fjern duplikater', 'OK', 'FEIL', 'Stream', '(vis som)', 'filer', 'album', '%1d %2t %3m %4s', 'Generelt', 'Skreddersy', 'Filh�ndtering', 'Klikk p� ? for hjelp.', 'Automatisk database synkronisering', 'Send filendelse', 'Tillat uautorisert streams', 'Inkluder headers', 'Ekstern javascript', 'Hjemmeside', 'Vis Keyteq gir deg del', 'Vis oppgraderingsdel', 'Vis statistikk', 'Skriv ID3v2 i stream', 'Ny bruker funksjonalitet', 'Filtyper', 'Ja', 'Nei', 'Filendelse', 'MIME', 'Inkluder i M3U', 'editer filtype', 'Sikker?', 'Optimistisk filsjekk', 'Randomiserer', 'Modus', 'Spilleliste', 'Ingen, direkte', 'Mine favoritter', 'Fant ingen rader', 'Hits p� systemet', 'Rekkef�lge', 'Sl� p� LAME st�tte', 'Avsl�tt', 'Tillatt LAME bruk?', 'E-post', 'Tillatt � e-post filer', ' SMTP tjener', 'SMTP port', 'E-post til', 'Beskjed', 'Send', 'E-post sendt!', 'Aktiver opplastning', 'Opplastningskatalog', 'Aktiver mp3e-post', 'Opplast', 'Fil opplastet!', 'Fil kunne ikke bli opplastet!', 'Du er n�dt til � skru p� cookies for � logge inn!', 'Periode', 'siden alltid', 'denne uken', 'denne m�neden', 'siste m�ned', 'hits', 'LAME kommando', 'Vis albumcover', 'Albumfiler', 'Omskaler albumbilder', 'Album h�yde', 'Album bredde', 'E-postmetode', 'Direkte', 'Pear', 'Vent', 'Vennligst skriv inn en gyldig e-post i alternativer!', 'Spillelister direkte?', 'Vis album fra URL?', 'Album URL', 'Kunne ikke sende!', 'Bruker lagt til!', 'Lager arkiv', 'Arkiv er slettet.', 'Bruker oppdatert!', 'Musikk tilpasning', '%1 rader filtrert', 'Logg aksess', 'Vis', 'Arkivert', 'Meldingsbord', 'Skrevet %1 av %2', 'mer', 'Publiser', '%1 mb', '%1 kb', '%1 bytes');

$klang[2]  = array('German', 'ISO-8859-15', 'Deutsch', 'Was ist hip', 'Was ist neu', 'Suchen', '(nur %1 angezeigt)', 'Sek', 'Such Ergebnisse: \'%1\'', 'gefunden', 'Keine.', 'Such-Datenbank Optionen aktualisieren', 'Unbenutze Datens�tze l�schen?', 'ID3 erneuern?', 'Debug Modus?', 'Update', 'Abbrechen', 'Such-Datenbank aktualisieren', '%1 Dateien gefunden', 'Konnte Datei nicht ermitteln: %1, �bersprungen.', 'Installiert: %1 - Aktualisiert: %2, untersuche:', 'Scan: ', 'Fehler - Abfrage: %1', 'Konnte Datei nicht lesen: %1, �bersprungen.', 'Entfernt: %1', 'Eingef�gt %1, aktualisiert %2, gel�scht %3 dabei %4 fehlgeschlagen und %5 �bersprungen; %6 Dateien gesamt - %7 Sek - %8 markiert zum l�schen.', 'Erledigt', 'Schliessen', 'Konnte hier keine Dateien finden: "%1"', 'kPlaylist Login', 'Album Liste f�r Interpret: %1', 'Kurzwahl %1', 'Keine Lieder ausgew�hlt. Playliste nicht aktualisiert.', 'Playliste aktualisiert', 'Zur�ck', 'Playliste hinzugef�gt!', 'Die Seite erneut laden!', 'Login:', 'Passwort:', 'Achtung! Dies ist eine private Webseite! Alle Aktionen werden protokolliert!', 'Login', 'SSL wird zum einloggen ben�tigt.', 'Abspielen', 'L�schen', '�ffentlich: ', 'Sichern', 'Playliste bearbeiten: "%1" - %2 Titel', 'Editor', 'Betrachter', 'Ausw�hlen', 'Seq', 'Status', 'Info', 'L�schen', 'Name', 'Summe:', 'Fehler', 'Aktion auf Auswahl:', 'Reihenfolge:', 'bearbeite Playliste', 'Diesen Eintrag l�schen', 'Playliste hinzuf�gen', 'Name:', 'Erstellen', 'Abspielen: ', 'Datei', 'Album', 'Alle', 'Auswahl', 'Hinzuf�gen', 'Abspielen', 'Bearbeiten', 'Neu', 'Ausw�hlen:', 'Spielen: ', 'Playliste: ', 'Kurzwahl numerisch', 'Keyteq pr�sentiert:', '(Suche nach Update)', 'Webseite', 'Nur ID3 Tags', 'Album', 'Titel', 'Interpret', 'Kurzwahl Album nach Interpret', 'Zeige', 'Gemeinsame Playlisten', 'Benutzer', 'Administration', 'Was ist neu', 'Was ist hip', 'Logout', 'Optionen', '�berpr�fen', 'Mein KPlaylist', 'Benutzer �ndern', 'Neuer Benutzer', 'Vollst�ndiger Name', 'Login', 'Passwort �ndern?', 'Passwort', 'Anmerkung', 'Zugangslevel', 'An', 'Aus', 'Benutzer l�schen', 'Benutzer ausloggen', 'Erneuern', 'Neuer Benutzer', 'L�schen', 'Logout', 'EXTM3U Feature benutzen?', 'Wieviele Zeilen zeigen (hip/neu)', 'Max. Zeilen bei Suchergebnissen', 'Reset', 'Verzeichnis �ffnen', 'Gehe zum Verzeichnis: %1', 'Download', 'Eine Ebene h�her', 'In das Basisverzeichnis', 'Nach einem Upgrade suchen', 'Benutzer', 'Sprache', 'Optionen', 'Gebootet', 'Zufall:', 'Einstellungen', 'Hauptverzeichnis', 'Stream Location', 'Voreingestellte Sprache', 'Ein Windows-System', 'Ben�tigt HTTPS', 'Suche erlaubt', 'Download erlaubt', 'Session Timeout', 'Berichte fehlgeschlagene Login-Versuche', 'Bitte warten - hole Dateiliste', 'Playliste konnte nicht erstellt werden!', 'Administrator', 'Einloggen mit HTTPS f�r �nderungen', 'Streaming Engine aktivieren', 'Titel', 'Artist', 'Album', 'Kommentar', 'Jahr', 'Lied', 'Genre', 'nicht gesetzt', 'Max. Download Rate (kb/s)', 'Benutzer', '%1 Min - %2 Titel', '%1 kbit %2 Min', 'Genre Liste: %1', 'Los', '%1T %2Std %3Min Spielzeit %4 Dateien %5 MB', 'Hier gibt es keine passenden Eintr�ge.', 'Passwort ge�ndert!', 'Anmelden', 'Bitte treffe eine Auswahl!', 'Was ist ein Update?', 'Klicke hier f�r Hilfe', 'Benutze externe Bilder?', 'Pfad zu externen Bildern', 'Aktuelles Passwort', 'Aktuelles Passwort nicht korrekt!', 'Bevorzugter Archivierer', 'Archiv konnte nicht erstellt werden', 'M�gliche doppelte Datei gefunden: "%1" - "%2"', 'Playliste wirklich l�schen?', 'Alphabetisch', 'Zufall', 'Sortiert', 'Original', 'Benutze Javascript', 'Benutzer wirklich l�schen?', 'Zeige History', 'History', 'Zeilen', 'Externe CSS Datei', 'L�sche doppelte Eintr�ge', 'OK', 'FEHLER', 'Stream', '(erscheinen wie)', 'Dateien', 'Album', '%1T %2Std %3Min %4Sek ', 'Allgemein', 'Anpassen', 'Datei Kontrolle', 'Klick das "?" f�r Hilfe', 'Automatische Datenbanksynchronisierung', 'Dateiendungen senden', 'Nichtautorisierte Streams erlauben', 'Header einbeziehen', 'Externes Javascript', 'Homepage', 'Zeige "Keyteq hat" Teil', 'Zeige Upgrade-Teil', 'Zeige Statistik', 'Schreibe ID3v2 Tags beim Streaming', 'Aktiviere Benutzer Anmeldung', 'Datei Typen', 'Ja', 'Nein', 'Dateiendung', 'MIME', 'M3U einbeziehen', 'Datei Typ bearbeiten', 'Sicher?', 'Optimistischer Dateipr�fung', 'Zufallsgenerator', 'Modus', 'Playliste', 'Nein, direkt', 'Meine Favoriten', 'Keine Treffer gefunden', 'Absolute Hits', 'Reihenfolge', 'Aktiviere LAME Unterst�tzung', 'Deaktiviert', 'LAME Verwendung erlauben?', 'Email', 'Datein per Email versenden erlauben?', 'SMTP Server', 'SMTP Port', 'Email an', 'Nachricht', 'Senden', 'Email gesendet!', 'Aktiviere Upload', 'Upload Verzeichnis', 'Aktiviere mp3mail', 'Upload', 'Datei hochgeladen!', 'Datei konnte nicht hochgeladen werden!', 'Um einzuloggen m�ssen Cookies erlaubt sein!', 'Periode', 'Immer', 'Diese Woche', 'Diesen Monat', 'Letzten Monat', 'Hits', 'LAME Befehl', 'Zeige Album Cover', 'Album Dateien', 'Gr�sse der Album Bilder anpassen', 'Album H�he', 'Album Breite', 'Email Methode', 'Direkt', 'Pear', 'Warten', 'Bitte eine g�ltige Emailadresse angeben!', 'Playlisten inline?', 'Zeige Album von URL?', 'Album URL', 'Konnte nicht senden!', 'Benutzer hinzugef�gt!', 'Archiv Ersteller', 'Archiv wurde gel�scht');

$klang[3]  = array('Swedish', 'ISO-8859-10', 'Svenska', 'Vad �r mest spelat', 'Vad �r nytt', 'S�k', '(endast %1 visad)', 'Sek', 'S�kresultat: \'%1\'', 'hittade', 'Ingen.', 'uppdatera s�k databas inst�llningar', 'Ta bort oanv�nda album', '�teruppbygg ID3?', 'K�r debug?', 'Uppdatera', 'Avbryt', 'uppdatera s�kdatabas', 'Hittade %1 filer.', 'Kunde inte l�sa fil: %1, hoppade �ver.', 'Installerer %1 - Uppdaterar: %2, l�ser:', 'L�ser:', 'Misslyckades - fr�ga: %1', 'Kunde inte l�sa filen: %1, hoppade �ver', 'Tog bort: %1', 'Infogade %1, uppdaterade %2, tog bort %3, varav %4 misslyckades och hoppade �ver %5 av %6 filer - %7 sek - %8 markerade f�r borttaganing', 'F�rdig', 'St�ng', 'Kunde inte hitta n�gra filer h�r: \'%1\'', 'kPlaylist Inloggning', 'Albumlista f�r artist: %1', 'Snabbval %1', 'Inga l�tar valda. L�tlistan �r ej updaterad.', 'L�tlista uppdaterad!', 'Tillbaka', 'Spellista inlagd!', 'Kom ih�g att uppdatera sidan.', 'Anv�ndarnamn:', 'L�senord:', 'Observera! Detta �r inte en publik websida. All aktivitet �r loggad.', 'Inloggning', 'SSL beh�vs f�r inloggning', 'Spela', 'Ta Bort', 'Delad:', 'Spara', 'Kontrollera l�tlista: "%1" - %2 titlar', 'Redigerare ', 'Visare ', 'V�lj ', 'Sek ', 'Status ', 'Info ', 'Ta Bort', 'Namn ', 'Totalt:', 'Fel', 'Handling vid val', 'Sekvens:', 'redigera spellista', 'Ta bort den h�r raden', 'L�gg till spellista', 'Namn:', 'Skapa', 'Spela:', 'Fil', 'Album', 'Alla', 'Markerad', 'l�gg till', 'spela', 'redigera', 'ny', 'V�lj:', 'Spelkontroll:', 'Spellista:', 'Snabbv�lj numeriskt', 'Keyteq ger dig:', '(Uppgraderings kontroll)', 'Hemsida', 'endast id3', 'album', 'titel', 'artist', 'Snabbv�lj album fr�n artist', 'visa', 'Delade spellistor', 'Anv�ndare', 'Adminkontroll', 'Vad �r nytt', 'Mest spelat', 'Logga ut', 'Inst�llningar', 'Kontrollera ', 'Min ', 'redigera anv�ndare', 'ny anv�ndare', 'Fullst�ndigt namn', 'Anv�ndarnamn ', '�ndra l�senord?', 'L�senord', 'Kommentar ', 'Beh�righet ', 'P� ', 'Av ', 'Ta bort anv�ndare', 'Logga ut anv�ndare', 'Uppdatera ', 'Ny anv�ndare', 'ta bort', 'logga ut', 'Anv�nd EXTM3U funktion?', 'Visa hur m�nga rader (mest spelat/nytt)', 'H�gst antal s�krader', 'Nollst�ll', '�ppna katalog', 'G� till katalog: %1', 'Ladda ner', 'G� ett steg upp', 'G� till rotkatalogen', 'Kolla efter uppgradering', 'anv�ndare ', 'Spr�k ', 'inst�llningar ', 'Kickad', 'Blanda', 'Inst�llningar', 'Rotniv� ', 'Stream lokalisering', 'Standard spr�k', 'Ett Windowssystem', 'Kr�v HTTPS', 'Till�t fils�k', 'Till�t nerladdning', 'Sessionen avbruten.', 'Rapportera misslyckat loginf�rs�k', 'V�nta - h�mtar fillista', 'Spellista kunde inte l�ggas till!', 'Admin', 'Lagga in med HTTPS f�r att �ndra!', 'Aktivera streaming', 'Titel', 'Artist', 'Album', 'Kommentar', '�r', 'Sp�r', 'Genre', 'Inte satt', 'Max nerladdningshastighet (kbps)', 'Anv�ndare', '%1 min - %2 titlar', '%1 kbit %2 min', 'Genre lista: %1', 'G�', '%1d %2t %3m speltid %4 filer %5 MB', 'Inga relevanta resurser h�r.', 'L�senordet �ndrat!', 'Skapa konto', 'Var v�nlig och g�r ett val!', 'Vad �r uppdatering?', 'Klicka h�r f�r hj�lp.', 'Anv�nda externa bilder?', 'Externa bildens s�kv�g.', 'Nuvarande l�senord', 'Nuvarande l�senord matchar inte!', '�nskad arkiverare', 'Arkiv kunde inte skapas', 'Trolig fildubblett hittad: "%1"  "%2"', 'Verkligen radera spellistan?', 'Alfabetisk', 'Mixat', 'Sortera', 'Original', 'Anv�nd javascript', '�r du s�ker att du vill radera denna anv�ndare?', 'Visa historia', 'historia', 'Rader', 'Extern CSS fil', 'Ta bort dubletter', 'OK', 'FEL', 'Stream', '(visa som)', 'filer', 'album', '%1d %2t %3m %4s', 'Generellt', 'Anpassa', 'Filhanterning', 'Klicka p� ? f�r hj�lp', 'Automatisk databas synkronisering', 'Skicka fil �ndelse', 'Till�t', 'Inkludera headers', 'Externt javascript', 'Hemsida', 'Visa Keyteq ger dig del', 'Visa uppgraderingsdel', 'Visa statistik', 'Skriv ID3v2 med stream', 'Aktivera anv�ndar registrering', 'Filtyper', 'Ja', 'Nej', 'Fil �ndelse', 'MIME', 'Inkludera i M3U', 'editera filtyp', 'S�kert?', 'Optimistisk filkontroll', 'Randomisera', 'L�ge', 'Spellista', 'Ingen, direkt', 'Mina favoriter', 'Kunde inte hitta n�gra tr�ffar', 'Alla tiders hitl�tar', 'Ordning', 'Aktivera LAME-st�d?', 'Avst�ngd', 'Till�t LAME-anv�ndning?', 'Epost', 'Till�t epost av filer?', 'SMTP-server', 'SMTP-port', 'Till', 'Meddelande', 'S�nd', 'Meddelande s�nt!', 'Aktivera uppladdning', 'Uppladdningsbibliotek', 'Aktivera mp3mail', 'Uppladdning ', 'Fil uppladdad', 'Filen kunde ej laddas upp', 'Du m�ste aktivera cookies f�r att kunna logga in!', 'Period', 'N�gonsin', 'Denna vecka ', 'Denna m�nad', 'Senaste m�naden', 'tr�ffar', 'LAME kommando', 'Visa omslag', 'Albumfiler', 'Anpassa bildens storlek', 'H�jd', 'Bredd', 'Brevmetod', 'Direkt', 'Pear', 'V�nta', 'Skriv in en giltig epostadress i inst�llningar!', 'Playlist inline', 'Visa album fr�n URL?', 'Album URL');

$klang[4]  = array('Dutch', 'ISO-8859-15', 'Nederlands', 'Meest gespeeld', 'Wat is nieuw', 'Zoeken', '(waarvan %1 in deze lijst)', 'sec', 'Gezocht op: \'%1\'', 'gevonden', 'Geen.', 'update zoek database opties', 'Verwijderen ongebruikte bestanden? ', 'ID3 vernieuwen?', 'Fout opsporings mode?', 'Vernieuwen', 'Annuleren', 'Vernieuwen database', '%1 bestanden gevonden.', 'Problemen met : %1, overgeslagen.', 'Toegevoegd: %1 Aangepast: %2 Scan:', 'Scan:', 'Fout - gezocht: %1', 'Kan het bestand niet lezen: %1. Overgeslagen.', 'Verwijderd: %1', 'Toegevoegd %1, bijgewerkt %2, verwijderd %3 waar van %4 mislukt en %5 overgelagen van %6 bestanden - %7 sec - %8 gemarkeerd voor verwijdering.', 'Klaar', 'Sluiten', 'Kan geen bestanden vinden in: "%1"', 'kPlaylist inloggen', 'Albumlijst van artiest: %1', 'Snelkeuze %1', 'Geen muziek geselecteerd. Afspeellijst niet bijgewerkt.', 'Afspeellijst bijgewerkt!', 'Terug', 'Afspeellijst toegevoegd!', 'Niet vergeten om de pagina te verversen.', 'Gebruikersnaam:', 'Wachtwoord:', 'NB! Dit is een niet publieke website. Alle acties worden opgeslagen in een log bestand.', 'Ga verder...', 'SSL benodigd om in te loggen.', 'Afspelen', 'Verwijderen', 'Gedeeld', 'Opslaan', 'Instellingen afspeellijst "%1"- %2 nummer(s)', 'Bewerken', 'Viewer', 'Selecteren', 'Volgorde', 'Status', 'Informatie', 'Wis', 'Naam', 'Totalen:', 'Fout', 'Actie op selectie:', 'Volgorde:', 'afspeellijst bewerken', 'Verwijder deze regel!', 'afspeellijst toevoegen', 'Naam:', 'Aanmaken', 'Afspelen:', 'Bestand', 'Album', 'Alles', 'Geselecteerd', 'toevoegen', 'afspelen', 'bewerken', 'nieuw', 'Selectie:', 'Afspeel opties', 'Afspeellijst:', 'Snelkeuze nummer', 'Keyteq presenteert:', '(Update controle)', 'Homepage', 'alleen id3', 'album', 'titel', 'artiest', 'Snelkeuze selectie (Uitvoerende)', 'bekijk', 'Gedeelde afspeellijsten', 'Gebruikers', 'Administrator opties', 'Wat is nieuw', 'Wat is Populair', 'Uitloggen', 'Instellingen', 'Controleer', 'Mijn opties', 'Bewerk gebruikersaccount', 'Nieuw gebruikersaccount', 'Volledige naam', 'Inlog naam:', 'Wachtwoord veranderen?', 'Wachtwoord', 'Commentaar', 'Toegangs level', 'Actief', '----', 'Verwijder gebruiker', 'Gebruiker afsluiten', 'Ververs pagina', 'Nieuwe gebruiker', 'Wis', 'uitloggen', 'Gebruik EXTM3U optie?', 'Hoeveel rijen tonen (Populair / Nieuw)', 'Maximaal aantal rijen zoekresultaat', 'Reset', 'Open map', 'Ga naar map: %1', 'Download', 'Een stap terug', 'Bovenste map', 'Update controle', 'gebruikers', 'Taal', 'opties', 'Booted', 'Willekeurig:', 'Instellingen', 'Start directory', 'Stream lokatie', 'Standaard taal', 'Een Windows systeem', 'HTTPS benodigd', 'Seek toestaan', 'Downloaden toestaan', 'Sessie timeout', 'Raporteer niet geslaagde inlog pogingen', 'Een ogenblik - bestands lijst ophalen', 'Afspeellijst kan niet toegevoegd worden!', 'Beheer', 'Om te wijzigen inloggen met https verbinding!', 'Gebruik stream engine', 'Titel', 'Artiest', 'Album', 'Bijzonderheden', 'Jaar', 'Nummer', 'Genre', 'niet ingesteld', 'Maximale downloadsnelheid (kbps)', 'Gebruiker', '%1 minuten- %2 titels', '%1 kbit %2 minuten', 'Genre lijst: %1', 'Ok', '%1d %2h %3m afspeelduur %4 bestanden %5 mb', 'Geen relevante bron aanwezig', 'Wachtwoord veranderd!', 'Aanmelden', 'Maak een keuze a.u.b.!', 'Toelichting bij het vernieuwen van de database?', 'Klik hier voor help', 'Gebruik externe plaatjes', 'Path naar externe plaatjes', 'Oude wachtwoord', 'oude wachtwoord is niet correct!', 'Compressie programma voorkeur', 'Bestand kon niet aangemaakt worden', 'Bestand mogelijk dubbel: %1 - %2', 'Afspeellijst echt verwijderen?', 'Alfabetisch', 'Willekeurig', 'Sorteer', 'Origineel', 'Gebruik Javascript', 'Weet u zeker dat u deze gebruiker wilt verwijderen?', 'Geef geschiedenis weer', 'geschiedenis', 'Regels', 'Extern Css bestand', 'Verwijder dubbelingen', 'Ok', 'FOUT', 'Stream', '(laat zien als)', 'bestanden', 'albums', '%1d %2u %3m %4s', 'Algemeen', 'Aanpassen', 'Bestands afhandeling', 'Klik op ? voor hulp.', 'Database automatisch synchroniseren', 'Zend bestands extentie', 'Sta niet geautoriseerde streams toe', 'Sluit headers in', 'Extern javascript', 'Home pagina', 'Laat "Keyteq presenteert" regel zien', 'Laat "Update controle" regel zien', 'Laat tekst met statistieken zien', 'Stuur ID3v2 mee met stream', 'Sta aanmelding van gebruikers toe', 'Bestand typen', 'Ja', 'Nee', 'Extentie', 'MIME', 'M3U insluiten', 'Pas bestandtype aan', 'Weet je het zeker?', 'Optimistische bestandscontrole', 'Willekeurig afspelen', 'Modus', 'Afspeel lijst', 'Geen, direct', 'Mijn favorieten', 'Niets gevonden', 'Meest gespeeld', 'Volgorde', 'Ondersteuning voor LAME aanzetten?', 'Uitgezet', 'Gebruik van LAME toestaan?', 'Email adres', 'Sta het sturen van bestanden via de mail toe?', 'SMTP server', 'SMTP poort', 'Bericht aan', 'Bericht', 'Verstuur', 'Bericht verzonden!', 'Activeer upload', 'Upload map', 'Activeer MP3Mail', 'Upload', 'Bestand geupload!', 'Bestand kon niet geupload worden!', '"Cookies" moeten aan staan om in te loggen!', 'Periode', 'ooit', 'deze week', 'deze maand', 'laatste maand', 'gevonden', 'LAME parameters', 'Albumhoes tonen', 'Albumhoes bestanden', 'Albumhoes formaat aanpassen', 'Albumhoes hoogte', 'Albumhoes breedte', 'Wijze van mail versturen', 'Direct', 'Pear', 'Wacht', 'Gelieve geldig email adres in te vullen! Zie "Opties"!', 'Afspeellijst insluiten?  ', 'Albumhoes ophalen vanaf URL?', 'Albumhoes URL', 'Het verzenden is mislukt!', 'Gebruiker toegevoegd!', 'Compressie bestand aangemaakt door', 'Compressie bestand gewist.', 'Gebruikersaccount aangepast!', 'Muziek overeenkomst', '%1 gefilterd', 'Log toegang', 'Zichtbaar');

$klang[5]  = array("Spanish", "ISO-8859-1", "Espa�ol", "Lo Padre", "Lo Nuevo", "B&uacute;squeda", "s&oacute;lo 1% visible", "seg", "Resulados de B&uacute;squeda: '%1'", "encontrado", "Ninguno.", "actualizar opciones de base de datos de b&uacute;squeda", "&iquest;Suprimir entradas sin uso? ", "&iquest;ReconstruirID3? ", "&iquest;Modo de Debug? ", "Actualizar", "Cancelar", "actualizar base de datos de b&uacute;squeda", "Se Encontraron %1 archivos", "No se pod&iacute;a determinar este archivo: %1, saltado", "%1 - Actualizar: %2, scanear:  ", "Scanear", "B&uacute;squeda Fallada: %1", "No se pod&iacute;a enconrar archivo: %1. Saltado. ", "Quitado: %1", "Insertado %1, actualizado %2, quitado %3 d&oacute;nde %4 fall&oacute; y %5 saltado por %6 archivos - %7 seg - %8 marcado para borrar.", "Finalizado", "Cerrar", "No se pod&iacute;a encontrar archivos utilzando %1", "kPlaylist Nombre de Usuario", "Lista de disco de artista: %1 ", "Hotselect %1 ", "Ninguna canci&oacute;n seleccionada. Lista no actualizada. ", "&iexcl;Lista actualizada con &eacute;xito!", "Regresar", "&iexcl;Lista actualizada!", "Actualice la p&aacute;gina", "nombre de usuario:", "contrase&ntilde;a", "Aviso! Este es un sitio restringido. Todos movimientos se guardan.", "Nombre de usuario", "SSL requirido para entrar.", "Tocar", "Suprimir", "Compartido:", "Guardar", "Lista de Control: &quot;%1&quot; - %2 t&iacute;tulos", "Editor", "Visor", "Seleccionar", "Seq", "Estatus", "Info", "Sup", "Nombre", "Totales:", "Error", "Acci&oacute;n sobre seleccionado", "Sequencia:", "editar lista", "Suprimir esta entrada", "agregar lista", "Nombre:", "Crear", "Tocar:", "Archivo", "Disco", "Todo", "Seleccionados", "agregar", "tocar",  "editar", "nuevo", "Seleccionar:", "Tocar Control:", "Lista:", "Seleccionador N&uacute;merico ", "Keyteq le proporciona:", "(checar por actualizaciones)", "P&aacute;gina Principal", "s&oacute;lo id3", "disco", "t&iacute;tulo", "artista", "Seleccionador disco de artista", "vista", "Listas compartidas", "Usuarios", "Control de administrador", "Lo nuevo", "Lo popular", "Salir", "Opciones", "Checar", "Mi", "editar usuario", "nuevo usuario", "Nombre completo", "Nombre de usuario", "&iquest;cambiar contrase&ntilde;a?", "Contrase&ntilde;a", "Comentario", "Nivel de aceso", "Encendido", "Apagado", "Suprimir usuario", "Salir usuario", "Actualizar", "Nuevo usuario", "sup", "salir", "Utilizar la opci&oacute;n de EXTM3U?", "Mostrar cuantas filas (popular/nuevo)", "M&aacute;x filas de b&uacute;squeda", "Restaurar", "Directorio abierto", "Abriri directorio: %1", "Descargar", "Subir un nivel", "Ir directo al directorio de ra&iacute;z", "Buscar actualizaciones", "usuarios", "Idioma", "opciones", "Cerrado", "Barajadura:", "Ajustes", "Directorio bajo", "Localizaci&oacute;n de la corriente", "Lengua del defecto", "Un sistema de Windows", "Requiera HTTPS", "Permita seek", "Permita download", "Sesi&oacute;n descanso", "Informe fallado conexi&oacute;n tentativas", "Sostenga encendido - traer la lista del archivo", "Playlist no pod&iacute;a ser agregado!", "Admin", "Conexi&oacute;n con HTTPS a cambiar", "", "Titulo", "Artista", "Alb�m", "Comentario", "A�o", "Corte", "Genero", "", "M�ximo r�tio de descarga (kbps)", "Usuario", "", "", "", "", "", "", "Contrase�a cambiada!", "", "");

$klang[6]  = array("Portuguese", "ISO-8859-1", "Portugu�s", "este � popular", "Este � novo", "Busca", "(apenas %1 encontrado)", "seg", "Resultados da busca: '%1'", "encontrado", "Nenhum", "atualizar op��es da busca na base de dados ", "Apagar entradas sem uso? ", "Reconstruir ID3?",  "Modo Debug?", "Atualizar", "Cancelar", "Atualizar busca no banco de dados", "Encontrados %1 arquivos.", "N�o foi poss�vel determinar este arquivo: %1, descartado", "Install %1 - Atualizar: %2, escanear:", "Escanear:", "Falha na busca: %1", "N�o foi poss�vel ler este arquivo: %1. Descartado.", "Removido: %1",  "Inserido %1, atualizado %2, apagado %2, onde %4, falhou em %5, descartado por %6, arquivos - %7 seg - %8 marcado para ser deletado", "Finalizado", "Fechar", "N�o foi poss�vel encontrar arquivos aqui: \"%1\"", "Logon kPlaylist", "Lista de �lbum por artista: %1", "Populares %1", "Nenhuma m�sica selecionada. Lista n�o atualizada.", "Lista atualizada!", "Voltar", "Lista atualizada",  "Lembre-se de atualizar a p�gina.", "login:", "senha:", "Aten��o! Este n�o � um site restrito. Todas as a��es s�o monitoradas.", "Login", "SSL necess�rio para entrar.", "Tocar", "Apagar", "Compartilhado", "Salvar", "Lista de controlhe: \"%1\" - %2 t�tulos",  "Editor", "Visualizador", "Selecionar", "Seq", "Status", "Info", "Del", "Nome", "Totais", "Erro", "A��o selecionada:", "Sequ�ncia", "editar lista", "Apagar esta entrada", "adicionar lista", "Nome:", "Criar", "Tocar:", "Arquivo", "�lbum", "Todos", "Selecionado",  "adicionar", "tocar", "editar", "novo", "Selecionar", "Controle", "Lista:", "Selecionar n�mero", "Keyteq oferece:", "(verificar atualiza��o)", "P�gina incial", "apenas id3", "�lbum", "t�tulo", "artista", "Selecionar �lbum por artista", "ver", "Listas compartilhadas", "Usu�rios", "Controle de administrador", "Este � novo", "Este � popular", "Logout", "Op��es", "Verificar", "Meu", "editar usu�rio", "novo usu�rio", "Nome completo", "Login", "Mudar senha?", "Senha", "Coment�rio", "N�vel de acesso", "Ligado", "Desligado", "Apagar usu�rio", "Desconectar usu�rio", "Atualizar", "Novo usu�rio", "apagar", "desconectar", "Utilizar op��o EXTM3U?", "Mostrar quantos arquivos (popular/novo)",  "M�ximo de arquivos encontrados", "Restaurar", "Abrir diret�rio", "Para o diret�rio: %1", "Download", "Subir um n�vel", "Para o diret�rio principal", "Verificar atualiza��es", "usu�rios", "Linguagem", "op��es", "Carregado", "Aleat�rio", "Configura��es", "Diret�rio base", "Local de stream", "Linguagem padr�o", "Sistema Windows", "Requer HTTPS", "Permitir busca", "Permitir download", "Sess�o expirou",  "Falha na tentativa de login", "Aguarde - buscando a lista de arquivos", "Lista n�o pode ser adicionada!", "Admin", "In�cio de uma sess�o com o HTTPS a mudar");

$klang[7]  = array('Finnish', 'ISO-8859-1', 'Suomi', 'Suosituimmat', 'Uusimmat', 'Etsi', '(pelk�st��n %1 n�ytet��n)', 'sek', 'Haku-tulokset: \'%1\'', 'l�ytyi', 'Tyhj�.', 'p�ivit� hakutietokannan asetukset', 'Poista k�ytt�m�tt�m�t tiedot?', 'Uudelleenrakenna ID3?', 'Debug-moodi?', 'P�ivit�', 'Peruuta', 'p�ivit� hakutietokanta', 'L�ytyi %1 tiedostoa', 'Ei voinut m��ritt��: %1, skipattu.', 'Install %1 - P�ivit�: %1,  tarkistus:', 'Tarkistus:', 'Ep�onnistui - haku: %1', 'Ei voinut lukea t�t� tiedostoa: %1. Skipattu.', 'Poistettu: %1', 'Sy�tetty %1, p�ivitetty %2, poistettu %3, miss� %4 ep�onnistui ja %5 skipattiin %6 tiedostosta - %7 sekuntia - %8 merkitty poistettavaksi', 'Valmis', 'Sulje', 'Mik��n ei vastannut: %1', 'kPlaylist Kirjautuminen', 'Albumilista artistille: %1', 'Pikavalinta: %1', 'Ei valittuina mit��n. Soittolistaa ei p�ivitetty', 'Soittolista p�ivitetty!', 'Takaisin', 'Soittolista lis�tty!', 'Muista p�ivitt�� sivu.', 'tunnus', 'salasana:', 'Huomautus! T�m� ei ole julkinen sivu. Kaikki teot kirjataan yl�s', 'Kirjaudu', 'SSL vaaditaan kirjautumiseen.', 'Soita', 'Poista', 'Jaettu:', 'Tallenna', 'Hallitse soittolistaa: \'%1\' - %2 nimet', 'Muokkain', 'Selain', 'Valitse', 'J�rj.', 'Tila', 'Info', 'Poista', 'Nimi', 'Yhteens�:', 'Virhe', 'Toiminto valitussa:', 'J�rjestys:', 'muokkaa soittolistaa', 'Poista t�m� tulos', 'lis�� soittolista', 'Nimi:', 'Luo', 'Soita', 'Tiedosto', 'Albumi', 'Kaikki', 'Valitut', 'lis��', 'soita', 'muokkaa', 'uusi', 'Valitse:', 'Hallinta:', 'Soittolista', 'Pikavalinta numero', 'Keyteqin tuote:', '(tarkista p�ivityksien varalta)', 'Kotisivu', 'ainoastaan id3', 'albumi', 'biisi', 'artisti', 'Albumit artistin mukaan', 'katso', 'Jaetut soittolistat', 'K�ytt�j�t', 'Yll�pito', 'Mit� uutta', 'Suosituimmat', 'Kirjaudu ulos', 'Asetukset', 'Tarkasta', 'Oma', 'muokkaa k�ytt�j��', 'uusi k�ytt�j�', 'Kokonimi', 'Kirjaudu', 'Vaihda salasana?', 'Salasana', 'Kommentti', 'Taso', 'On', 'Off', 'Poista k�ytt�j�', 'Kirjaa ulos k�ytt�j�', 'P�ivit�', 'Uusi k�ytt�j�', 'poista', 'kirjaa ulos', 'K�yt� EXT3MU-toimintoa?', 'N�yt� kuinka monta tulosta (suosittu/uusi)', 'Maksimi haku tulokset', 'Resetoi', 'Avaa hakemisto', 'Mene hakemistoon: %1', 'Imuroi', 'Avaa yl�kansio', 'Mene p��hakemistoon', 'Tarkista p�ivityksien varalta', 'k�ytt�j�t', 'Kieli', 'asetukset', 'Bannattu', 'Shuffle', 'Asetukset', 'Perushakemisto', 'Streamin l�hde', 'Oletuskieli', 'Windows systeemi', 'Vaadi HTTPS (Salattu yhteys)', 'Salli etsiminen', 'Salli imurointi', 'Istunto p��ttynyt', 'Ilmoita ep�onnistuneet kirjautumisyritykset', 'Hetki. Haen tiedostolistaa', 'Soittolistaa ei voitu lis�t�', 'Yll�pit�j�', 'Kirjaudu HTTPS:ll� vaihtaaksesi', 'Streaming moottori p��lle', 'Nimi', 'Artisti', 'Albumi', 'Kommentti', 'Vuosi', 'Raidan numero', 'Tyyppi', 'ei asetettu', 'Maksimi imurointinopeus (kbps)', 'K�ytt�j�', '%1 minuuttia - %2 biisi�', '%1 kilobitti� %2 minuuttia', 'Tyyppilista: %1', 'Mene', ' %1d %2h %3m soittoaika %4 tiedostoa %5 mt', 'Ei soitettavia fileit�', 'Salasana vaihdettu!', 'Rekister�i', 'Tee valintasi', 'Mik� on p�ivitys?', 'Ohje painamalla t�st�', 'K�yt� ulkoisia kuvia?', 'Ulkoisten kuvien polku', 'Nykyinen salasana', 'Nykyinen salasana ei natsaa!', 'Valitse pakkaaja', 'Pakkausta ei pystytty tekem��n', 'Todenn�k�inen kopio: %1 - %2', 'Haluatko varmasti poistaa soittolistan?', 'Aakkosellinen', 'Shuffle', 'J�rjest�', 'Alkuper�inen', 'K�yt� javascripti�', 'Haluatko varmasti posistaa t�m�n k�ytt�j�n?', 'N�yt� historia', 'historia', 'Rivi�', 'Ulkopuolinen CSS tiedosto', 'Poista tuplat', 'OK', 'VIRHE', 'Stream', '(n�yt� tyyppin�)', 'tiedostot', 'albumit', '%1d %2h %3m %4s ', 'Yleist�', 'Muokkaa', 'Tiedostonk�sittely', 'Klikkaa ? ohjeen n�ytt�miseksi.', 'Automaattinen tietokanta-synkronisaation', 'L�het� tiedostop��te', 'Salli kirjautumattomat streamit', 'Sis�llyt� otsikot', 'Ulkopuolinen javascript', 'Kotisivu', 'N�yt� \'Keyteq toi sinulle\'-kohdan', 'N�yt� p�ivit� kohta', 'N�yt� statistiikka', 'Kirjoita ID3v2 streamiin', 'Salli k�ytt�jien rekister�inti', 'Tiedostotyypit', 'Kyll�', 'Ei', 'P��te', 'MIME', 'Sis�llyt� M3U-tiedostoon', 'muokkaa tyyppi�', 'Varmasti?', 'Optimistinen tiedoston tarkastus', 'Arpoja', 'Toimintatila', 'Soittolista', 'Ei mit��n, suoraan', 'Omat suosikit', 'Osumia ei l�ytynyt', 'Kaikkien aikojen parhaat', 'J�rjestys', 'LAME tuki p��lle', 'Pois', 'Salli LAMEn k�ytt�?', 'S�hk�posti', 'Salli tiedoston s�hk�postitus?', 'SMTP palvelin', 'SMTP portti', 'L�het� s�hk�posti', 'Viesti', 'L�het�', 'Viesti l�hetetty!', 'Aktivoi tiedoston lis�ys', 'Tiedoston lis�ys kansio', 'Aktivoi mp3mail', 'Lis�� tiedosto', 'Tiedosto lis�tty', 'Tiedoston lis�ys ei onnistunut!', 'Ev�steiden on oltava p��ll�, jotta sis��nkirjautuminen onnistuisi!', 'Ajanjakso', 'koskaan', 't�ll� viikolla', 't�ss� kuussa', 'edellisess� kuussa', 'osumia', 'LAME komento', 'N�yt� albumin kansi', 'Albumin tiedostot', 'Sovita albumin kuvien koko', 'Albumin korkeus', 'Albumin leveys', 'Postitusmuoto', 'Suora', 'PEAR', 'Odota', 'Anna oikea s�hk�postiosoite asetuksissa!', 'Soittolistat sisennettyin�?', 'N�yt� albumi URLista?', 'Albumin URL', 'L�hetys ei onnistunut!', 'K�ytt�j� lis�tty!', 'Arkiston luonti', 'Arkisto on poistettu.', 'K�ytt�j�n tiedot p�ivitetty!', 'Musiikin vertailu');

$klang[8]  = array("Danish", "ISO-8859-1", "Dansk", "Hvad er hot?", "Hvad er nyt?", "S�g", "(kun %1 vist)", "sec", "S�geresultater: %1", "fundet", "Ingen.", "opdater s�gedatabase-indstillinger", "Slet ubrugelige albums?", "Genopbyg ID3?", "Fejls�gning", "Opdater", "Annuller", "opdater s�gedatabase", "Fundet % fil(er).", "Kunne ikke bestemme filtypen p�: %1", "Installeret: %1 - Opdateret: %2, scannet: ", "Scan:", "Fejlede - foresp�rgsel: %1", "Kunne ikke l�se: %1", "Fjernet: %1", "Der er indsat %1, opdateret %2, slettet %3, hvor %4 fejlede og %5 blev sprunget over, gennem %6 filer - %7 sec - %8 markeret for sletning.", "F�rdig", "Luk", "Kunne ikke finde filer her: %1", "kPlaylist login", "Albumliste for kunstner: %1", "Hurtigv�lg %1", "Ingen numre valgt. Playlist ikke opdateret.", "Playlist opdateret!", "Tilbage", "Playlist tilf�jet!", "Husk at genindl�se siden.", "brugernavn:", "adgangskode:", "Bem�rk! Dette er en privat webside. Alt logges.", "Log p�", "SSL er kr�vet for at logge p�.", "Afspil", "Slet", "Delt:", "Gem", "Kontroller playlist: %1 - %2 titler", "Redigeringsvindue", "Fremviser", "V�lg", "Sek", "Status", "Info", "Slet", "Navn", "Total:", "Fejl", "Handling p� valgte:", "Sekvens:", "rediger playlist", "Slet nummer", "tilf�j playlist", "Navn:", "Opret", "Afspil:", "Fil", "Album", "Alle", "Valgte", "tilf�j", "afspil", "rediger", "ny", "V�lg:", "Afspilningskontrol:", "Playlist:", "Numerisk hurtigvalg", "Keyteq giver dig:", "(tjek for update)", "Webside", "Kun ID3", "album", "titel", "kunstner", "Hurtigv�lg album fra kunstner", "vis", "Delte playlists", "Brugere", "Administrator", "Hvad er nyt", "Hvad er hot", "Log ud", "Indstillinger", "Tjek", "Min konto", "rediger bruger", "ny bruger", "Fulde navn", "Brugernavn", "�ndre adgangskode?", "Adgangskode", "Kommentar", "Adgang", "P� / On", "Af / Off", "Slet bruger", "Log bruger ud", "Opdater", "Ny bruger", "slet", "logud", "Brug EXTM3U?", "Vis r�kker (hotte/nye)", "Max. antal s�ger�kker", "Reset", "�bn mappe", "G� til mappe: %1", "Download", "Et trin op", "Til rodmappe", "Tjek efter updates", "brugere", "Sprog", "indstillinger", "Afsp�rret", "Shuffle / Random", "Indstillinger", "Basemappe", "Stream-lokation", "Standardsprog", "Windows-system?", "Kr�ver HTTPS", "Tillad s�gning", "Tillad download", "Sessionstimeout", "Rapporter fejlede logins", "Vent - henter filliste", "Playlist kunne ikke tilf�jes", "Admin", "Log ind via HTTPS for at �ndre!", "Aktiver streaming", "Titel", "Kunstner", "Album", "Kommentar", "�r", "Nummer", "Genre", "ikke sat", "Max. download rate (kbps)", "Bruger", "%1 minutter - %2 titler", "%1 kbit %2 minutter", "Genreliste: %1", "G�", "Spilletid: %1d %2h %3m - %4 filer %5 mb", "Intet relevant her.", "Kodeordet er �ndret", "", "Foretag venligst en markering", "Hvad er en opdatering?", "Klik her for hj�lp", "Brug eksterne billeder", "Sti til eksterne billeder", "Nuv�rende kodeord", "", "", "", "To ens filer er muligvis fundet: %1 - %2", "Vil du virkelig slette playlisten?", "Alfabetisk", "Tilf�ldig", "Sorter", "Original", "Brug javascript", "Er du sikker p�, du vil slette denne bruger?", "Vis historie", "Historie", "R�kker", "Ekstern CSS-fil");

$klang[9] = array('Russian', 'Windows-1251', '�������', '����������', '�����', '�����', '(������ %1 �������)', '���.', '��������� ������: "%1"', '�������', '�� ����.', '�������� ��������� ������ ���� ������', '������� �������������� ������ � ����?', '����������� ID3?', '����� �������?', '�������� ����', '������', 'O������� ���� ������ ������', '������� %1 ����(��).', '�� ���� ���������� ���� ����: %1, ���������.', '���������: %1 - ���������: %2, �����������: ', '�����������: ', '������ - ������: %1', '�� ���� ��������� ���� ����: %1. ���������.', '�������: %1', '��������� %1, ��������� %2, ������� %3, �� ��� %4 ������ � %5 ���������. ����� %6 ������ - %7 ���. - %8 ���������� ��� ��������.', '�����', '�������', '�� ������� �� ������ �����: "%1"', 'kPlaylist. ����', '������ �������� ��� �����������: %1', '������� ����� %1', '�� ������� �� ����� ����������. �������� �� �������.', '�������� �������!', '�����', '�������� ��������!', '�� �������� ������������� ��������.', '�����:', '������:', '��� �������� ������������� ������������.', '�����', '��� ����� ��������� SSL', '���������', '�������', '��������� ������������: ', '���������', '���������� ����������: "%1" - %2 ����������', '�������������', '��������', '�������', '������.', '����������', '����������', '����.', '���', '�����:', '������', '�������� � ��������: ', '������������������:', '������������� ��������', '������� ��� �������', '�������� ��������', '���:', '�������', '���������: ', '����', '������', '���', '���������', '��������', '���������', '�������������', '�����', '�������:', '���������� �������������: ', '��������: ', '������� ����� �� �����', 'Keyteq �������� ���:', '(��������� ����������)', '�������� ��������', '������ �  id3', '������', '��������', '�����������', '������� �� ��������', '��������', '����� ���������', '������������', '�����������������', '�����', '����������', '�����', '���������', '��������', '��� ���������', '������������� �������������', '����� ������������', '������ ���', '�����', '�������� ������?', '������', '�����������', '������� �������', '���', '����', '������� ������������', '��������� ������������', '��������', '����� ������������', '����.', '�����', '������������ EXTM3U?', '���������� ��������� ����� (����������/�����)', '���������� ��������� ����� ��� ������', '�����', '����� � �����', '������� � �����: %1', '�������', '����� �� ���� �������', '� ������', '��������� ����������', '�������������', '����', '���������', '���������', '��������� �������:', '���������', '���� � ������������ ������', '���� ��� ���������� �������', '���� �� ���������', '������ ��� Windows', '��������� HTTPS', '��������� �����������', '��������� ���������', '������� ��� ������', '�������� � ��������� �������� �����', '��������� - ���������� �� ������� ������', '�������� �� ����� ���� ��������!', '�����������������', '���� ������ ����� HTTPS', '�������� ���������� ������� ���������� �������', '��������', '�����������', '������', '�����������', '���', '����', '�����', '�� ����������', '������������ �������� ���������� (kbps)', '������������', '%1 ���. - %2 ������', '%1 kbit %2 ���.', '������ ������: %1', '���������', '� ���� %4 ������ ����� ������� %5 ��.<br>������ ����� �������������: %1 ���� %2 ����� %3 �����.', '����������� ����� �����������.', '������ �������', '�����������', '�������� ���� �� ���� ����', '��� ����� ����������?', 'ٸ������ ����� ��� ���������', '������������ ������� ��������?', '���� � ���������', '������� ������', '�������� ������ �� ��������� � �������!', '������������ ���������', '���������� ������� �����', '������� ��������� ��������� ������:  "%1" "%2"', '�� ������������� ������ ������� ��������?', '�� ��������', '��������', '�����������', '��� � ���������', '������������ JavaScript', '�� ������������� ������ ������� ����� ������������?', '�������� �������', '�������', '������', ' ���� CSS', '������� ���������', 'OK', 'ERR', '�����', '(�������� ���)', '�����', '�������', '%1 ���� %2 ����� %3 ����� %4 ������', '�����', '���������', '������ � �������', 'ٸ������ �� ? ��� ���������.', '�������������� ���������� ���� ������', '�������� ���������� ����� ��� ��������', '��������� �������� ���������� ����� ��� �����������', '�������� ���������', '������� JavaScript', '����� ������ �����', '���������� ����� "Keyteq �������� ���"', '���������� ����� "��������� ����������"', '���������� ����������', '��������� ��� ID3v2 � �����', '��������� ����������� �������������', '���� ������', '��', '���', '����������', 'MIME', '�������� � M3U', '������������� ��� �����', '�������?', '������ �������� ������', '��������� �������', '����� �������', '�������� � ��������', '�� ���������', '�� ����� ����������', '���������� ���������� �� �������', '����� ����������', '����������', '��������� ��������� LAME?', '���������', '��������� ������������� LAME?', 'Email', '��������� �������� ������ �� Email\'�?', 'SMTP ������', 'SMTP ����', '����������', '���������', '��������', '������ ��������!', '����������� �������� �� �������', '���������� ��� ��������', '������������ mp3�����', '���������', '���� ��������!', '���� �� ��������!', '���� ������ ���� ��������', '����������', '�����', '�� ���� ������', '� ���� ������', '� ������� ������', 'hits', 'LAME �������', '���������� ������� ��������', '������� ��������', '�������� ������� �������', '������ �������', '������ �������', '������ ������� �����', '������', 'Pear', '�����', '���������� ������� � ������ ���������� ������ ��������� �����!', '�������� "inline"', '���������� ������� � ��������-�������?', '������ ��� �������', '�� �������!', '������������ ��������!', '�������� ������', '����� ������.', '������������ ���������������', '������', '%1 ������������', '��� �������', '���������', '� ������', '�����', '���� %1 �� %2', '������', '����������');

$klang[10]  = array("Swiss German", "ISO-8859-15", "Schwiizerd�tsch", "Wasch geil", "Wasch neu", "Wo isch das Z��g", "Gseesch nur �s Proz�nt", "sek", "Suechergebnis: '%1'", "gfund�", "keini", "pass das datebank-suech-z��g aa", "n�d benutzte seich i de db kick� ?", "ID3 erneuer�?",  "Dib�g-Modus?", "Update", "Abbr�che", "Suech-DB update", "%1 Files gfund�", "Bin bi dem File n�d druus cho: %1. Has usglaa.", "Inschtalliert:%1 - Draa umebaschtlet: %2, abchecke:", "sc�n:", "Problem bi de Abfrag: %1", "Han glaub es File verh�eneret: %1. Ussglaa..", "Weggnoo: %1",  "inetaa: %1, umebaschtlet: %2, weggnoo: %3, %4 h�nd n�d gfunzt und %5 hani ussglaa; %6 dateie insgesamt - %7 sekunde - %8 hani markiert zum abtsch�sse.", "Schnornig.", "Zuemach�.", "Da h�tts kei Dateie: \"%1\"", "KPlaylist Login", "Albumlischte f�r Interpret: %1", "Churzwahl %1", "Kein Song usgw�hlt. Playlischte n�d aktualisiert.", "Playlischte aktualisiert.", "Zrugg", "Playlischte zuegf�egt!", "Nomal lade das z��g.", "Login:", "Passwort:", "Achtung! Dasch privat da z��g. Jede seich gitt eis uf de Deckel!", "Login", "Bruchsch SSL zum inechoo", "Abschpile", "L�sche", "Die wommer z�me h�nd:", "Seiv�", "A de Playlischte umebaschtle: \"%1\" - %2 Titel",  "Editor", "Aazeiger", "Usw�hle", "Nummer�", "Schtatus", "Info", "Abtsch�sse", "Nam�", "Z�mezellt", "Sch�ne seich", "Das machemer mit dene wo uusgw�hlt sind",  "Reiefolg", "a de Playlischte umebaschtle", "De Iitrag useschmeisse", "Playlischte dezuetue", "Nam�:", "Mache", "Abschpile:", "Datei", "Album", "Ali", "die Uusgw�hlte",  "Dezue tue", "Abschpile", "draa umebaschtle", "neu", "Usw�hle:", "Abschpile:", "Playlischte:", "Churzwahl numerisch", "Keyteq pr�sentiert eu:", "(Suche nacheme neue versi�nli)", "Houmpeitsch", "Nume id3 T�gZ", "Album", "Titel", "Interpret", "Churzwahl Album nach Interpret", "Aasicht", "Playlischtene, wommer z�me h�nd", "Benutzer", "Admin kontroll�", "Wasch neu", "Wasch geil", "Und tsch�ss", "Iischtellige", "Abtsch�gge", "Mini", "Benutzer ab�ndere", "Neue Benutzer", "De ganz Name", "Login", "Passwort ab�ndere?", "Passwort", "S�nf dezue gee",  "Wie m�chtig isch de Typ", "Aagschtellt", "Abgschtellt", "Benutzer abtsch�sse", "Uuslogge", "Erneuer�", "Neue Benutzer", "L�sche", "Uuslogge", "S�li das EXTM3U z��g bruuche?", "Wivill ziile aazeige (geil/neu)", "Max. Ziile bi Suechergebnis", "Reset", "Ordner ufmache", "Gang zum Ordner: %1", "Abesuuge", "Ein Ordner ufe", "Is Grundverzeichnis", "Mal luege �bs es Update gitt", "Benutzer", "Spraach", "Opzione",  "Aaghalte", "Mischle:", "Iischtellige", "Hauptverzeichnis", "Stream location", "Standardspraach", "Es windoof-system", "bruucht HTTPS", "d�rf me sueche", "d�rf me suuge", "session isch abgloffe",  "s�g mer, wenn eine sis PW verh�ngt", "mom�ntli, mues schn�ll go d'files l�se", "han die bl��d playlist n�d ch�ne mache!", "Admin", "Login mit HTTPS zum �ndere");

$klang[11]  = array('French', 'ISO-8859-15', 'francais', 'liens HOT', 'Nouveau', 'rechercher', '(seulement %1 visibles)', 'sec', 'R�sultats de la recherche : \'%1\'', 'trouv�', 'aucun', 'actualiser les options de la base de donn�es de recherche', '<b>Supprimer</b> les entr�es inutiles ?', 'Reconstruire<b>ID3</b> ?', 'Mode de d�buggage ?', 'Actualiser', 'Annuler', 'Actualiser la base de donn�es de recherche', '%1 fichiers trouv�s', 'Ce fichier n\'a pas pu �tre d�termin� : %1, ignor�.', 'Install�s : %1 - Actualis�s : %2, Scann�s: ', 'Scanner', 'Echec - Requete : %1', 'Le fichier : %1 n\'a pas �t� trouv�. Abandon.', 'Elimin�s : %1', 'Ins�r�(s) :%1, Actualis�s %2, Supprim�s : %3 dont %4 �chou�s et %5 ignor�s parmi %6 fichiers - %7 sec. - %8 marqu�s pour effacement.', 'Termin�', 'Fermer', 'Impossible de trouver des fichiers dans : "%1"', 'Nom d\'utilisateur KPlaylist', 'Liste des albums de l\'artiste : %1', 'Hotselect %1', 'Aucune chanson s�lectionn�e. La liste n\'a pas �t� actualis�e.', 'Liste actualis�e avec succ�s !', 'Pr�c�dent', 'Liste ajout�e !', 'Pensez � actualiser la page.', 'nom d\'utilisateur :', 'mot de passe :', 'Attention ! Ce site est priv�, toute action est enregistr�e.', 'Nom d\'utilisateur', 'SSL n�cessaire pour s\'identifier.', 'Lire', 'Effacer', 'Partag�e :', 'Enregistrer', 'Actions sur la liste : "%1" contenant %2 titres', 'Editeur', 'Viseur', 'S�lectionner', 'N� piste', 'Status', 'Informations', 'Supprimer', 'Nom du fichier', 'Totaux :', 'Erreur', 'Action � effectuer sur la selection', 'Liste :', '�diter la liste', 'Supprimer cette entr�e', 'ajouter une liste', 'Titre :', 'Cr�er', 'Lire :', 'Fichier', 'Album', 'Tous', 'S�lectionn�s', 'ajouter', 'lire', '�diter', 'nouveau', 'S�lectionner :', 'Lire :', 'Liste :', 'S�lection num�rique', 'Keyteq vous propose :', '(rechercher des mises � jour)', 'Accueil', 'seulement id3', 'album', 'titre', 'artiste', 'Acc�der � un artiste', 'voir', 'Listes partag�es', 'Utilisateurs', 'Console d\'administration', 'Nouveaux', 'Populaires', 'D�connecter', 'Options', 'Consulter les fichiers', 'Mon compte', '�diter un utilisateur', 'nouvel utilisateur', 'Nom complet', 'Nom d\'utilisateur', 'changer le mot de passe ?', 'Mot de passe', 'Commentaires', 'Niveau d\'acc�s', 'On', 'Off', 'Supprimer l\'utilisateur', 'D�connecter l\'utilisateur', 'Actualiser', 'Nouvel utilisateur', 'supprimer', 'd�connecter', 'Utiliser l\'option de EXTM3U ?', 'Montrer combien de lignes (populaires/nouveaux)', 'R�sultat maximum de r�ponses', 'Reset', 'Ouvrir le r�pertoire', 'Aller dans le r�pertoire : %1', 'T�l�charger', 'Dossier parent', 'Aller au r�pertoire racine', 'Chercher les mises � jour', 'utilisateurs ', 'Langue', 'options', 'D�sactiver le compte', 'Lecture al�atoire :', 'Configuration', 'Chemin racine de la librairie musicale', 'Forcer l\'url du flux', 'Langue pa d�faut', 'Syst�me de type Windows', 'HTTPS n�cessaire', 'Permettre la recherche', 'Permettre les t�l�chargements', 'D�lai d\'expiration de la session', 'Rapport des tentatives de connexion �chou�es', 'Patientez - Analyse de la librairie', 'La liste n\'a pas pu �tre ajout�e !', 'Admin', 'Connexion en HTTPS obligatoire', 'Activer le moteur de streaming', 'Titre', 'Artiste', 'Album', 'Commentaires', 'Ann�e', 'N� piste', 'Genre', 'n/a', 'Taux de t�l�chargement Max (kbps)', 'Utilisateur', '%1 min - %2 titres', '%1 kbit %2 min', 'Liste des genres : %1', 'Go', 'Temps de lecture : %1 J %2 H %3 m, %4 fichiers %5 Mo', 'Aucune ressource correspondante', 'Mot de passe mis � jour !', 'Inscrivez vous', 'Faites une selection SVP !', 'Qu\'est ce que la mise � jour ?', 'Clickez ici pour l\'aide', 'Utiliser des images externes ?', 'Chemin vers les images externes', 'Mot de passe actuel', 'Mauvais mot de passe', 'Archiveur pr�f�r�', 'Impossible de cr�er l\'archive', 'Doublon probable : "%1" "%2"', 'Voulez-vous vraiment supprimer la liste ?', 'Alphab�tique', 'Al�atoire', 'Classer', 'Original', 'Utiliser Javascript', 'Voulez vous vraiment supprimer cet utilisateur ?', 'Voir l\'historique', 'historique', 'Lignes', 'Fichier CSS externe', 'Supprimer les doublons', 'OK', 'ERREUR', 'Flux', '(afficher par)', 'fichiers', 'albums', '%1J %2H %3m %4s', 'Principal', 'Personnalisation', 'Gestion de la librairie', 'Cliquer sur "?" pour afficher l\'aide', 'Synchronisation automatique de la base de donn�es ', 'Envoyer les extensions de fichiers', 'Accepter les flux interdits', 'Inclure les en-tetes', 'Javascript externe', 'Accueil', 'Afficher "Keyteq vous propose :"', 'Afficher "rechercher des mises � jour"', 'Afficher les statistiques', 'Inclure les ID3v2 dans le flux', 'Ouvrir les inscriptions d\'utilisateurs', 'Types de fichiers', 'Oui', 'Non', 'Extensions', 'MIME', 'Inclure dans le M3U', 'Editer les types de fichiers', 'Etes-vous s�r ?', 'Analyse optimale des fichiers', 'Auto DJ', 'Mode', 'Dans la liste', 'Aucune, lire directement', 'Mes Favoris', 'Aucun fichier trouv�', 'Les plus �cout�s', 'Ordre', 'Activer le support de LAME ?', 'D�sactiv�', 'Autoriser l\'utilisation de LAME ?', 'E-mail', 'Autoriser l\'envoi de fichiers par e-mail ?', 'Adresse du serveur SMTP', 'Port du serveur SMTP', 'Destinataire', 'Message', 'Envoyer', 'E-mail envoy� !', 'Activer l\'envoi de fichiers upload', 'R�pertoire pour les envois upload', 'Activer mp3mail', 'Envoyer un fichier', 'Fichier envoy� !', 'Impossible d\'envoyer le fichier !', 'Vous devez autoriser les cookies pour vous connecter !', 'P�riode', 'depuis le d�but', 'cette semaine', 'ce mois', 'le mois dernier', 'requetes', 'Commande LAME', 'Afficher la couverture de l\'album', 'Fichiers de l\'album', 'Redimensionner les images de l\'album', 'Hauteur', 'Largeur', 'M�thode d\'envoi d\'e-mail', 'Direct', 'Pear', 'Attendre', 'Veuillez saisir une adresse e-mail valide dans les options !', 'Listes inline ?', 'Afficher l\'album depuis l\'url ?', 'Url de l\'album', 'Impossible d\'envoyer !', 'Utilisateur ajout� !', 'Utilitaire d\'archive', 'L\'archive a �t� supprim�e.', 'Mis � jour', 'Similitudes');

$klang[12]  = array("Indonesian", "ISO-8859-1", "Indonesia", "Yang Ter-Hot", "Yang Terbaru", "Cari", "(hanya %1 tampilan)", "dtk", "Hasil Pencarian: '%1'", "ditemukan", "Kosong", "Opsi update pencarian database", "Hapus record tdk terpakai", "Bangun Ulang ID3?",  "Mode Debug ?", "Update", "Batal", "update pencarian database", "ada %1 file", "Tipe file tdk ada: %1, abaikan.", "Terinstall: %1 - Update %2, scan:", "Scan:", "Gagal - query: %1", "File %1 tdk terbaca, Abaikan", "Menghapus: %1",  "Tambah %1, Ubah %2, Hapus %3 dimana %4 gagal dan %5 abaikan bila %6 file - %7 detik - %8 dipilih utk dihapus.", "Selesai", "Tutup", "File yang dicari tdk ada: \"%1\"", "Login kPlaylist", "Daftar album dengan artis: %1", "Hotselect %1", "Tdk ada pilihan, Playlist tdk terupdate", "Playlist ter-update!", "Kembali", "Playlist ditambah!",  "Ingatlah utk me-reload hal. ini", "Login:", "Password:", "Peringatan! Ini bukan web umum. Semua Aktifitas terekam disini.", "Login", "Butuh SSL untuk Login", "Putar", "Hapus", "Sharing:", "Simpan", "Playlist kontrol: \"%1\" - %2 judul",  "Editor", "Viewer", "Pilih", "Seq", "Status", "Info", "Hapus", "Nama", "Total:", "Error", "Action pd terpilih:",  "Sekuen", "Ubah Playlist", "Hapus entri ini", "Tambah playlist", "Nama", "Buat", "Putar:", "File", "Album", "Semua", "terpilih",  "tambah", "putar", "ubah", "baru", "Pilih:", "Kontrol:", "Playlist:", "Nomor HotSelect", "KeyTeq Anda:", "(Cek Upgrade)", "Homesite",  "hanya id3", "album", "judul", "artis", "Hotselect Album dari Artis ", "lihat", "Playlist lainnya", "User", "Kontrol Admin", "Yang terbaru", "Yang Terhot", "Logout", "Opsi", "Cek", "Profil", "Ubah user", "User baru", "Nama Lengkap", "Login", "Ubah Password?", "Password", "Komentar",  "Level Akses", "On", "Off", "Hapus user", "Logout user", "Refresh", "User baru", "hapus", "logout", "Gunakan EXTM3U", "Tampilkan banyak baris (hot/baru)",  "Max. Baris pencarian", "Reset", "Buka direktori", "ke direktori: %1", "Download", "Naik keatas", "Ke direktori root", "Cek Upgrade", "User", "Bahasa", "Opsi",  "Bootd", "Acak:", "Seting", "Direktori base", "Lokasi stream", "Bahasa default", "System Windows", "Butuh HTTPS", "Boleh mencari", "Boleh dowload", "Batas session",  "Report gagal login diperlukan", "Hold on - fetching file list ", "Playlist tdk bisa ditambah!", "Admin", "Login dengan HTTPS untuh mengganti!");

$klang[13]  = array("Italian", "ISO-8859-1", "Italiano", "Cosa c'� di Hot", "Cosa c'� di nuovo", "Ricerca", "(soltanto 1% visibile)", "sec", "risultato della ricerca: '%1'", "trovato", "nessuno.", "aggiona opzioni ricerca nel database", "Cancella records non utilizzati?", "Ricostruisci ID3?", "modalit� di Debug?", "Aggiorna", "Annulla", "aggiorna ricerca nel database", "Trovati %1 files.", "Impossibile determinare questo file: %1, saltato.", "Installato: %1 - Aggiornato: %2, scansione:", "Scansione:", "Fallita - ricerca: %1", "Impossibile leggere questo file: %1. Saltato.", "Rimosso: %1", "Inserito %1, aggiornato %1, cancellato %3, quando %4 � fallito e %5 saltato su %6 files - %7 secondi - %8 segnati per la cancellazione.", "Fatto", "Chiuso", "Impossibile trovare files qui: \"%1\"", "KPlaylist Login", "Lista album per artista: %1", "Hotselect %1", "Nessuna canzone selezionata. Playlist non aggiornata.", "Playlist aggiornata!", "Indietro", "Playlist aggiunta!", "Ricorda di ricaricare la pagina.", "login:", "password:", "Attenzione! Questo non � un sito pubblico. Tutte le azioni vengono registrate.", "Login", "SSL richiesto per l'accesso.", "Play", "Cancella", "Condiviso:", "Salva", "Controllo playlist: \"%1\" - %2 titoli", "Editor", "Visualizzatore", "Selezione", "Seq", "Stato", "Informazioni", "Canc", "Nome", "Totale:", "Errore", "Azione da eseguire sulla selezione:", "Sequenza:", "Edita playlist", "Cancella questa riga", "aggiungi playlist", "Nome:", "Crea", "Esegui:", "File", "Album", "Tutto", "Selezionati", "aggiungi", "play", "modifica", "nuovo", "Selezione:", "Controllo:", "Playlist:", "Selezione numerica", "Keyteq vi propone:", "(controlla aggiornamenti)", "Homepage", "solo id3", "album", "titolo", "artista", "Seleziona album per artista", "visualizza", "Playlists condivise", "Utenti", "Controllo dell'amministratore", "Cosa c'� di nuovo", "Cosa c'� di Hot", "Esci", "Opzioni", "Controlla", "Mio", "modifica utente", "nuovo utente", "Nome completo", "Login", "Cambio Password?", "Password", "Commento", "Livello d'accesso", "On", "Off", "Cancella utente", "Uscita utente", "Refresh", "Nuovo utente", "canc", "Uscita", "Usa opzione EXTM3U", "Mostra quante righe (hot/nuove)", "Righe massime da cercare", "Reset", "Apri directory", "Vai alla directory: %1", "Download", "Sali di un livello", "Vai al livello principale", "Controlla per l'aggiornamento", "utenti", "lingua", "opzioni", "Booted", "Casuale:", "Impostazioni", "Directory iniziale", "locazione brano", "Lingua di default", "Un sistema Windows", "Richiede HTTPS", "Permetti ricerca", "Permetti download", "timeout sessione", "Riporta tentativi falliti di login", "Aspetta - estrazione lista file", "La playlist non pu� essere aggiunta!", "Amministratore", "Collegarsi tramite HTTPS per cambiare!", "Abilita morore di streaming", "Titolo", "Artista", "Album", "Commento", "Anno", "Traccia", "Genere", "non settato", "Limitazione download (kbps)", "Utente", "%1 minuti - %2 titoli", "%1 kilobit %2 minuti", "Lista generi: %1", "Vai", "%1d %2h %3m playtime %4 files %5 mb", "Nessuna risorsa.", "Password cambiata!", "Crea utente", "Fai la tua selezione!", "Cos'� l'update?", "Aiuto", "Usa immagini esterne?", "Path immagini esterne", "Password corrente", "La passord corrente � sbagliata!", "Archiver preferito", "L'archivio potrebbe non essere stato creato", "Probabile file duplicato: %1 - %2", "Eliminare la playlist?", "Alfabetico", "Random", "Ordina", "Originale", "Usa javascript", "Eliminare questo utente?", "Guarda la history", "history", "Righe",  "File CSS Esterno");

$klang[14]  = array("Traditional Chinese [&amp;#12345]", "big5", "&#32321;&#39636;&#20013;&#25991;", "&#26368;&#29105;&#38272;", "&#26368;&#26032;", "&#25628;&#23563;", "(&#21482;&#26377; %1 &#31558;&#39023;&#31034;)", "&#31186;", "'%1' &#65306;&#25628;&#23563;&#32080;&#26524;", "&#25214;&#21040;", "&#27794;&#26377;", "&#26356;&#26032;&#25628;&#23563;&#36039;&#26009;&#24235;&#36984;&#38917;", "&#21034;&#38500; &#26410;&#29992;&#36942;&#30340;&#35352;&#37636;&#65311;", "&#37325;&#24314; ID3", "&#38500;&#34802;&#27169;&#24335;", "&#26356;&#26032;", "&#21462;&#28040;", "&#26356;&#26032;&#25628;&#23563;&#36039;&#26009;&#24235;", "&#25214;&#21040; %1 &#27284;&#26696;&#12290;", "&#30906;&#23450;&#19981;&#21040;&#27492; %1 &#27284;&#26696;&#65072; &#30053;&#36942;&#12290;", "&#24050;&#23433;&#35037;&#65072; %1 - &#26356;&#26032;&#65306; %2 &#65104; &#25475;&#30596;&#65306;", "&#25475;&#30596;&#65306;", "&#22833;&#25943; - &#21839;&#38988;&#65072; %1", "&#35712;&#19981;&#21040;&#27492; %1 &#27284;&#26696; &#65072;&#30053;&#36942;", "&#24050;&#31227;&#38500;&#65306; %1", "&#24050;&#25554;&#20837; %1 &#65292; &#24050;&#26356;&#26032; %2 &#65292; &#24050;&#21034;&#38500; %3&#65292; &#22320;&#40670; %4  &#22833;&#25943; &#21450; %6 &#27284;&#26696;&#20013;&#30053;&#36942;%5  - %7 &#31186; - &#24050;&#21034;&#38500; %8 &#26377;&#35352;&#34399;&#30340;&#27284;&#26696;", "&#24050;&#23436;&#25104;", "&#38359;&#38281;", "&#22312;&#27492;&#25214;&#19981;&#21040;&#20219;&#20309;&#27284;&#26696;&#65306; \"%1\"","kPlaylist &#30331;&#20837;", "&#27492;&#27468;&#25163;&#30340;&#23560;&#36655;&#28165;&#21934;&#65306; %1", "&#29105;&#36984; %1", "&#27794;&#26377;&#27468;&#26354;&#36984;&#25799;&#12290; &#25773;&#25918;&#28165;&#21934;&#27794;&#26377;&#26356;&#26032;&#12290;", "&#25773;&#25918;&#28165;&#21934;&#24050;&#26356;&#26032;&#65281;", "&#36820;&#22238;", "&#25773;&#25918;&#28165;&#21934;&#24050;&#21152;&#20837;&#65281;",  "&#35352;&#20303;&#37325;&#26032;&#25972;&#29702;&#27492;&#38913;&#12290;", "&#30331;&#20837;&#21517;&#31281;&#65306;","&#23494;&#30908;&#65306;","&#35686;&#21578;&#65281;&#27492;&#32178;&#31449;&#26159;&#19981;&#20844;&#38283;&#30340;&#65292;&#25152;&#26377;&#21205;&#20316;&#26159;&#26371;&#34987;&#35352;&#37636;&#12290;", "&#30331;&#20837;", "&#23433;&#20840;&#24615;(SSL)&#30331;&#20837;", "&#25773;&#25918;", "&#21034;&#38500;", "&#20998;&#20139;&#65109;", "&#20786;&#23384;", "&#25511;&#21046;&#25773;&#25918;&#28165;&#21934;&#65072; \"%1\" - %2 &#27161;&#38988;", "&#32232;&#36655;&#22120;", "&#27298;&#35222;&#22120;", "&#36984;&#25799;","&#38918;&#24207;", "&#29376;&#24907;", "&#36039;&#35338;", "&#21034;&#38500;", "&#21517;&#31281;", "&#32317;&#25976;&#65109;", "&#37679;&#35492;", "&#36984;&#25799;&#20013;&#65306;", "&#27425;&#24207;&#65109;", "&#32232;&#36655;&#25773;&#25918;&#28165;&#21934;", "&#21034;&#38500;&#27492;&#21152;&#20837;", "&#21152;&#20837;&#25773;&#25918;&#28165;&#21934;", "&#21517;&#23383;&#65109;", "&#24314;&#31435;", "&#25773;&#25918;&#65306;", "&#27284;&#26696;", "&#23560;&#36655;", "&#20840;&#37096;", "&#24050;&#36984;&#25799;", "&#26032;&#22686;", "&#25773;&#25918;", "&#32232;&#36655;", "&#26032;&#22686;", "&#36984;&#25799;&#65306;", "&#25773;&#25918;&#25511;&#21046;&#65306;", "&#25773;&#25918;&#30446;&#37636;&#65306;", "&#29105;&#36984;&#25976;&#20540;", "Keyteq &#25552;&#25552;&#20320;&#65306;", "(&#27298;&#26597;&#26356;&#26032;)", "&#20027;&#38913;", "&#21482;&#25628;&#23563; id3", "&#23560;&#36655;", "&#27161;&#38988;", "&#27468;&#25163;", "&#29105;&#36984;&#27468;&#25163;&#23560;&#36655;", "&#27298;&#35222;", "&#20998;&#20139;&#25773;&#25918;&#30446;&#37636;", "&#29992;&#25142;", "&#31649;&#29702;", "&#26368;&#26032;", "&#26368;&#29105;&#38272;", "&#30331;&#20986;", "&#36984;&#38917;", "&#27298;&#26597;", "&#20854;&#20182;", "&#32232;&#36655;&#20351;&#29992;&#32773;", "&#26032;&#22686;&#20351;&#29992;&#32773;", "&#20840;&#21517;", "&#30331;&#20837;", "&#35722;&#26356;&#23494;&#30908;&#65311;", "&#23494;&#30908;", "&#20633;&#35387;", "&#23384;&#21462;&#23652;&#32026;", "&#38283;", "&#38364;", "&#21034;&#38500;&#20351;&#29992;&#32773;", "&#20999;&#26039;&#20351;&#29992;&#32773;","&#37325;&#26032;&#25972;&#29702;","&#26032;&#22686;&#20351;&#29992;&#32773;", "&#21034;&#38500;", "&#30331;&#20986;", "&#20351;&#29992; EXTM3U &#25928;&#26524;&#65311;", "&#39023;&#31034;&#22810;&#23569;&#34892; (&#29105;&#38272;/&#26032;)", "&#26368;&#22823;&#25628;&#23563;&#34892;&#25976;", "&#37325;&#35373;", "&#38283;&#21855;&#30446;&#37636;", "&#36339;&#21040;&#30446;&#37636;&#65306; %1", "&#19979;&#36617;", "&#36339;&#21040;&#19978;&#19968;&#23652;", "&#36339;&#21040;&#26681;&#30446;&#37636;", "&#27298;&#26597;&#26356;&#26032;", "&#20351;&#29992;&#32773;", "&#35486;&#35328;", "&#36984;&#38917;", "&#24050;&#36215;&#21205;", "&#38568;&#27231;", "&#35373;&#23450;", "&#26681;&#30446;&#37636;&#32085;&#23565;&#36335;&#24465;", "&#20018;&#27969;&#36335;&#24465;", "&#38928;&#35373;&#35486;&#35328;", "&#35222;&#31383;&#31995;&#32113;", "&#35201;&#27714;HTTPS", "&#20801;&#35377;&#25628;&#23563;", "&#20801;&#35377;&#19979;&#36617;","&#36926;&#26178;", "&#22577;&#21578;&#30331;&#20837;&#22833;&#25943;", "&#35531;&#31561;&#31561; - &#24314;&#31435;&#27284;&#26696;&#30446;&#37636;&#20013;","&#25773;&#25918;&#28165;&#21934;&#19981;&#34987;&#26356;&#26032;&#65281;", "&#31649;&#29702;&#32773;", "&#20351;&#29992;HTTPS&#30331;&#20837;&#24460;&#26356;&#25913;&#65281;");

$klang[15] = array("Traditional Chinese - big5", "big5", "�c�餤��", "�̼���", "�̷s", "�j�M", "(�u�� %1 �����)", "��", "'%1' �G�j�M���G", "���", "�S��", "��s�j�M��Ʈw�ﶵ", "�R�� ���ιL���O���H", "���� ID3", "���μҦ�", "��s", "����", "��s�j�M��Ʈw", "��� %1 �ɮסC", "�T�w���즹 %1 �ɮסJ ���L�C", "�w�w�ˡJ %1 - ��s�G %2 �M ���ˡG","���ˡG", "���� - ���D�J %1", "Ū���즹 %1 �ɮ� �J���L", "�w�����G %1", "�w���J %1 �A �w��s %2 �A �w�R�� %3�A �a�I %4 ���� �� %6 �ɮפ����L%5 - %7 �� - �w�R�� %8 ���O�����ɮ�","�w����", "?��", "�b���䤣������ɮסG '%1'", "kPlaylist �n�J", "���q�⪺�M��M��G %1", "���� %1", "�S���q����ܡC ����M��S����s�C", "����M��w��s�I", "��^", "����M��w�[�J�I", "�O���s��z�����C", "�n�J�W�١G", "�K�X�G", "ĵ�i�I�������O�����}���A�Ҧ��ʧ@�O�|�Q�O���C", "�n�J", "�w����(SSL)�n�J", "����", "�R��", "���ɡR", "�x�s", "�����M��J '%1' - %2 ���D", "�s�边", "�˵���", "���", "����", "���A", "��T", "�R��", "�W��", "�`�ơR", "���~", "��ܤ��G", "���ǡR", "�s�輽��M��","�R�����[�J", "�[�J����M��", "�W�r�R", "�إ�", "����G", "�ɮ�", "�M��", "����", "�w���", "�s�W", "����", "�s��", "�s�W", "��ܡG", "���񱱨�G", "����ؿ��G", "����ƭ�", "Keyteq �����A�G", "(�ˬd��s)", "�D��", "�u�j�M id3", "�M��", "���D", "�q��", "����q��M��", "�˵�", "���ɼ���ؿ�", "�Τ�", "�޲z", "�̷s", "�̼���","�n�X", "�ﶵ", "�ˬd", "��L", "�s��ϥΪ�", "�s�W�ϥΪ�", "���W", "�n�J", "�ܧ�K�X�H", "�K�X", "�Ƶ�", "�s���h��", "�}", "��", "�R���ϥΪ�", "���_�ϥΪ�", "���s��z","�s�W�ϥΪ�", "�R��", "�n�X", "�ϥ� EXTM3U �ĪG�H", "��ܦh�֦� (����/�s)", "�̤j�j�M���", "���]", "�}�ҥؿ�", "����ؿ��G %1", "�U��", "����W�@�h", "����ڥؿ�","�ˬd��s", "�ϥΪ�", "�y��", "�ﶵ", "�w�_��", "�H��", "�]�w", "�ڥؿ�������|", "��y���|", "�w�]�y��", "�����t��", "�n�DHTTPS", "���\�j�M", "���\�U��", "�O��", "���i�n�J����", "�е��� - �إ��ɮץؿ���", "����M�椣�Q��s�I", "�޲z��", "�ϥ�HTTPS�n�J����I");

$klang[16] = array("Traditional Chinese - gb2312", "gb2312", "�c�餤��", "�̼���", "�̷s", "�j�M", "(�u�� %1 �����)", "��", "'%1' �G�j�M���G", "���", "�S��", "��s�j�M��Ʈw�ﶵ", "�R�� ���ιL���O���H", "���� ID3", "���μҦ�", "��s", "����", "��s�j�M��Ʈw", "��� %1 �ɮסC", "�T�w���즹 %1 �ɮסJ ���L�C", "�w�w�ˡJ %1 - ��s�G %2 �M ���ˡG", "���ˡG", "���� - ���D�J %1", "Ū���즹 %1 �ɮ� �J���L", "�w�����G %1", "�w���J %1 �A �w��s %2 �A �w�R�� %3�A �a�I %4 ���� �� %6 �ɮפ����L%5 - %7 �� - �w�R�� %8 ���O�����ɮ�", "�w����", "?��", "�b���䤣������ɮסG '%1'", "kPlaylist �n�J", "���q�⪺�M��M��G %1", "���� %1", "�S���q����ܡC ����M��S����s�C", "����M��w��s�I", "��^", "����M��w�[�J�I", "�O���s��z�����C", "�n�J�W�١G", "�K�X�G", "ĵ�i�I�������O�����}���A�Ҧ��ʧ@�O�|�Q�O���C", "�n�J", "�w����(SSL)�n�J", "����", "�R��", "���ɡR", "�x�s", "�����M��J '%1' - %2 ���D", "�s�边", "�˵���", "���", "����", "���A", "��T", "�R��", "�W��", "�`�ơR", "���~", "��ܤ��G", "���ǡR", "�s�輽��M��", "�R�����[�J", "�[�J����M��", "�W�r�R", "�إ�", "����G", "�ɮ�", "�M��", "����", "�w���", "�s�W", "����", "�s��", "�s�W", "��ܡG", "���񱱨�G", "����ؿ��G", "����ƭ�", "Keyteq �����A�G", "(�ˬd��s)", "�D��", "�u�j�M id3", "�M��", "���D", "�q��", "����q��M��", "�˵�", "���ɼ���ؿ�", "�Τ�", "�޲z", "�̷s", "�̼���", "�n�X", "�ﶵ", "�ˬd", "��L", "�s��ϥΪ�", "�s�W�ϥΪ�", "���W", "�n�J", "�ܧ�K�X�H", "�K�X", "�Ƶ�", "�s���h��", "�}", "��", "�R���ϥΪ�", "���_�ϥΪ�", "���s��z", "�s�W�ϥΪ�", "�R��", "�n�X", "�ϥ� EXTM3U �ĪG�H", "��ܦh�֦� (����/�s)", "�̤j�j�M���", "���]", "�}�ҥؿ�", "����ؿ��G %1", "�U��", "����W�@�h", "����ڥؿ�", "�ˬd��s", "�ϥΪ�", "�y��", "�ﶵ", "�w�_��", "�H��", "�]�w", "�ڥؿ�������|", "��y���|", "�w�]�y��", "�����t��", "�n�DHTTPS", "���\�j�M", "���\�U��", "�O��", "���i�n�J����", "�е��� - �إ��ɮץؿ���", "����M�椣�Q��s�I", "�޲z��", "�ϥ�HTTPS�n�J����I");

$klang[17]  = array("Korean", "ISO-8859-1", "&#54620;&#44397;&#50612;", "&#51064;&#44592;&#51221;&#48372;", "&#52572;&#49888;&#51221;&#48372;", "&#44160;&#49353;", "(%1 &#47564; &#48372;&#51076;)", "&#52488;", "&#44160;&#49353; &#44208;&#44284; : '%1'", "&#52286;&#50520;&#51020;", "&#50630;&#51020;.", "&#44160;&#49353; &#51088;&#47308; &#50741;&#49496; &#50629;&#45936;&#51060;&#53944;", "&#49324;&#50857;&#54616;&#51648; &#50506;&#45716; &#44592;&#47197; &#49325;&#51228;?", "ID3&#51116;&#44396;&#49457;?", "&#46356;&#48260;&#44536; &#47784;&#46300;?", "&#50629;&#45936;&#51060;&#53944;", "&#52712;&#49548;", "&#44160;&#49353; &#51088;&#47308; &#50629;&#45936;&#51060;&#53944;", "%1 &#54028;&#51068;&#51012; &#52286;&#50520;&#51020;.", "&#51060; &#54028;&#51068;&#51012; &#44208;&#51221;&#54624; &#49688; &#50630;&#51020;: %1, &#44148;&#45320;&#46848;.", "&#49444;&#52824;&#46120;: %1 - &#50629;&#45936;&#51060;&#53944;: %2, &#44160;&#49353;:", "&#44160;&#49353;:", "&#49892;&#54056; - &#51656;&#47928;: %1", "&#51060; &#54028;&#51068;&#51012; &#51069;&#51012; &#49688; &#50630;&#51020;: %1. &#44148;&#45320;&#46848;.", "&#51228;&#44144;&#46120;: %1", "%6 &#54028;&#51068;&#46308; &#51473; %4 &#45716; &#49892;&#54056;, %5&#45716; &#44148;&#45320;&#46832;&#44256;,%1 &#52628;&#44032; %2 &#44081;&#49888;&#46104;&#44256; %3 &#49325;&#51228;&#46120; - %7 &#52488; - %8 &#51008; &#49325;&#51228;&#54364;&#49884;&#46120;.", "&#45149;", "&#45803;&#51020;", "&#50612;&#46500; &#54028;&#51068;&#46020; &#52286;&#51012; &#49688; &#50630;&#51020;: \"%1\"", "kPlaylist &#47196;&#44536;&#50728;", "&#50500;&#54000;&#49828;&#53944;&#51032; &#50536;&#48276; &#47532;&#49828;&#53944; : %1", "&#51064;&#44592;&#49440;&#53469;&#44257; %1", "&#44257;&#51060; &#49440;&#53469;&#46104;&#51648; &#50506;&#50520;&#51020;. Playlist&#44032; &#44081;&#49888;&#46104;&#51648; &#50506;&#50520;&#51020;.", "Playlist &#44081;&#49888;!", "&#46244;&#47196;", "Playlist &#52628;&#44032;!", "&#51060; &#54168;&#51060;&#51648;&#47484; &#45796;&#49884; &#51069;&#51004;&#49464;&#50836;.", "&#47196;&#44536;&#51064;:", "&#50516;&#54840;:", "&#51452;&#51032;! &#51060; &#44275;&#51008; &#44277;&#44060;&#46108; &#50937;&#49324;&#51060;&#53944;&#44032; &#50500;&#45785;&#45768;&#45796;. &#47784;&#46304; &#54665;&#46041;&#51060; &#44592;&#47197;&#46121;&#45768;&#45796;.", "&#47196;&#44536;&#51064;", "&#47196;&#44536;&#50728;&#51012; &#50948;&#54644; SSL&#51060; &#54596;&#50836;&#54633;&#45768;&#45796;.", "&#51116;&#49373;", "&#49325;&#51228;", "&#44277;&#50976;&#46120;:", "&#51200;&#51109;", "playlist &#44288;&#47532;: \"%1\" - %2 &#51228;&#47785;", "&#54200;&#51665;&#44592;", "&#48624;&#50612;", "&#49440;&#53469;", "&#49692;&#49436;", "&#49345;&#53468;", "&#51221;&#48372;", "&#49325;&#51228;", "&#51060;&#47492;", "&#54633;&#44228;:", "&#50724;&#47448;", "&#49440;&#53469;&#54620; &#46041;&#51089;:", "&#49692;&#49436;:", "playlist &#54200;&#51665;", "&#51060; &#44592;&#47197;&#51012; &#49325;&#51228;&#54632;", "playlist &#52628;&#44032;", "&#51060;&#47492;:", "&#47564;&#46308;&#44592;", "&#51116;&#49373;:", "&#54028;&#51068;:", "&#50536;&#48276;", "&#51204;&#48512;", "&#49440;&#53469;&#46120;", "&#52628;&#44032;", "&#51116;&#49373;", "&#54200;&#51665;", "&#49352;&#47196; &#47564;&#46308;&#44592;", "&#49440;&#53469;:", "&#51116;&#49373; &#44288;&#47532;:", "Playlist:", "&#51064;&#44592;&#49440;&#53469;&#44257; &#49707;&#51088;", "&#45817;&#49888;&#50640;&#44172; Keyteq &#51060; &#51452;&#45716; &#44163;:", "(&#50629;&#44536;&#47112;&#51060;&#46300;&#47484; &#52404;&#53356;&#54616;&#49464;&#50836;)", "&#54856;", "id3&#47564;", "&#50536;&#48276;", "&#51228;&#47785;", "&#50500;&#54000;&#49828;&#53944;", "&#50500;&#54000;&#49828;&#53944;&#50640;&#49436; &#51064;&#44592;&#50536;&#48276;", "&#48372;&#44592;", "&#44277;&#50976;&#54620; playlist", "&#49324;&#50857;&#51088;", "&#50612;&#46300;&#48124; &#44288;&#47532;", "&#52572;&#49888;&#51221;&#48372;", "&#51064;&#44592;&#51221;&#48372;", "&#47196;&#44536;&#50500;&#50883;", "&#50741;&#49496;", "&#52404;&#53356;", "&#45208;&#51032;", "&#49324;&#50857;&#51088; &#54200;&#51665;", "&#49352;&#47196;&#50868; &#49324;&#50857;&#51088;", "&#51060;&#47492;", "&#47196;&#44536;&#51064;", "&#50516;&#54840;&#47484; &#48148;&#44984;&#49884;&#44192;&#49845;&#45768;&#44620;?", "&#50516;&#54840;", "&#53076;&#47704;&#53944;", "&#51217;&#44540;&#47112;&#48296;", "&#53020;&#44592;", "&#45124;&#44592;", "&#49324;&#50857;&#51088; &#49325;&#51228;", "&#49324;&#50857;&#51088; &#47196;&#44536;&#50500;&#50883;", "&#49352;&#47196; &#44256;&#52824;&#44592;", "&#49352;&#47196;&#50868; &#49324;&#50857;&#51088;", "&#49325;&#51228;", "&#47196;&#44536;&#50500;&#50883;", "EXTM3U &#47484; &#49324;&#50857;&#54633;&#45768;&#44620;?", "&#51460; &#49688; &#48372;&#51060;&#44592;(hot/new)", "&#44032;&#51109; &#47566;&#51008; &#44160;&#49353; &#51460;", "&#47532;&#49483;", "&#46356;&#47113;&#53664;&#47532; &#50676;&#44592;", "&#46356;&#47113;&#53664;&#47532;&#47196; &#44032;&#44592;: %1", "&#45236;&#47140;&#48155;&#44592;", "&#54620; &#45800;&#44228; &#50948;&#47196; &#44032;&#44592;", "&#51228;&#51068; &#50948;&#47196; &#44032;&#44592;.", "&#50629;&#44536;&#47112;&#51060;&#47484; &#52404;&#53356;&#54616;&#49464;&#50836;", "&#49324;&#50857;&#51088;", "&#50616;&#50612;", "&#50741;&#49496;", "&#48512;&#54021;&#46120;", "&#46244;&#49438;&#44592;:", "&#49464;&#54021;", "&#44592;&#48376; &#46356;&#47113;&#53664;&#47532;", "&#49828;&#53944;&#47548; &#51109;&#49548;", "&#44592;&#48376; &#50616;&#50612;", "&#50952;&#46020;&#50864; &#49884;&#49828;&#53596;", "HTTPS &#44032; &#54596;&#50836;&#54632;", "Seek &#54728;&#50857;", "&#45236;&#47140;&#48155;&#44592; &#54728;&#50857;", "&#49464;&#49496; &#49884;&#44036;&#51473;&#45800;", "&#49892;&#54056;&#54620; &#47196;&#44596; &#49884;&#46020; &#50508;&#47532;&#44592;", "&#51104;&#44624;&#47564; - &#54028;&#51068; &#47785;&#47197;&#51012; &#44032;&#51648;&#44256; &#50724;&#44256; &#51080;&#49845;&#45768;&#45796;", "Playlist &#50640; &#52628;&#44032;&#54624; &#49688; &#50630;&#49845;&#45768;&#45796;!", "&#50612;&#46300;&#48124;", "&#48148;&#44984;&#44592; &#50948;&#54644;&#49436; HTTPS&#47196; &#47196;&#44596;&#54616;&#49464;&#50836;!");

$klang[18]  = array("Estonian", "iso-8859-1", "eesti keel", "Mis hetkel populaarne", "Mis vanalehm", "Peksapihku", "mitte munnigi %1 ei ole n�ha", "peeretus", "Peksapihku tulemused: '%1'", "tapeti", "t�ra no mitte midagi ei leia", "k�garda peksapihku trammi tagumineratas", "Saadavittu need pasapead?", "K�ip�rgu ID3?", "Siluvalmoel?", "K�garda", "Soepeast", "k�garda peksapihku trammi", "Tapeti %1 k�rvits.", "Ei suudetud �mbernikkuda k�rvitsat: %1,  Kepiti s��tut", "N�piti nibusid: %1 - K�gardatud: %2, rinnad j�id koju:", "Rinnad j�id koju:", "L�ks viinaravile - sopajoodik: %1", "Ei suudetud s�nnitada : %1. Kepiti s��tut", "V�eti libuna t��le: %1", "V�nati ilgelt taha %1, k�gardatud %2, saadetudvittu %3 kus %4 l�ks viinaravile ning %5 Kepiti s��tud hooradeks%6 k�rvits ning see k�ik toimus %7 peeretuse -%8 saeti mitmeks t�kiks ja saadeti vittu.", "Keerasidki k�ik l�puks persse. palju �nne!", "Tapa see idjootne asi!", "Ei suudetud s�nnitada mitteainustki k�rvitsat: \"%1\"", "kJ��nuse hukkumine", "koristaja saeti mitmeks t�kiks t�rasid: %1", "Vaata kuumalt! %1", "J��nust ei k�gardatud.", "j��nus k�gardatud!", "Edaspiidi", "j��nus peeretas", "�ra unusta, et siit ei ole keegi veel eluga p��senud!", "hukkumine", "Pane paar s�na!", "K�ik munnid  on hukkunud.", "Hukku", "SSL venelits saeti mitmeks t�kiks hukkuma", "S��sitta", "Saadavittu", "Abordid:", "Silita peenist", "Raisk j��nus: \"%1\" - %2 sitapeadest", "emakeppija", "isakeppija", "�ekeppar", "kepieit", "lehmanisa", "kepiinfo", "Saada", "Pealdis", "Kogumoos", "Putsis", "munn on �ekepparid:", "Kepitudeit:", "kepiema j��nust", "Saadavittu see m�tetus", "peereta j��nus", "perselutsija", "keeruta", "S��gisitt:", "Fail", "Album", "K�iksemees korraga", "�ekepparid", "peereta", "mine:", "kepiema", "vanalehm", "�ekeppar:", "S��sitta Raisk:", "J��nused:", "Vaata kuumalt numbriliselt", "Keyteq annab taha:", "(liputaja saeti mitmeks t�kiks)", "Hooralaager", "mitte munnigi id3", "album", "sitapea", "t�ra", "Vaata kuumalt koristaja lahmakat t�ra", "kepiisa", "Abordi j��nused", "Rullnokad", "Sajajalgne raisk", "Mida vanalehm", "Mis hetkel populaarne", "K�i koju p�ngerjas", "tagumineratas", "Liputa", "kusi", "kepiema rullnokk", "vanalehm rullnokk", "Purjus perselutsija", "Hukkumine", "r��vi kommipoodi?", "Kommipood", "Kauri", "M�istuse vastane", "V�lja", "Sisse", "saadvittu rullnokk", "Hukku�ra rullnokk", "K�i putsi", "Vanalehm rullnokk", "saada", "hukku�ra", "Pitsike EXTM3U-d ja kutu?", "Miks k�ik on perses (kuum/vanalehm)", "V�ga palju pihkupeksjaid on perses", "Sitak�i", "Vehi riista", "V�ta veidike riista: %1", "Magatama", "Mine �lalkorrusele", "Mine vaata rooti riista", "Liputaja saeti mitmeks t�kiks", "rullnokad", "Vemblad", "tagumineratas", "Allalastud", "T�iestisegamini", "Kotikuke", "�lek�la riist", "Ribidevahele pekstud", "Peatage vemblad", "Windows-i ajuleiutis", "venelits HTTPS", "karaga pikkipead", "karaga magatama", "hooaeg l�bi", "hukkumist", "V�ta sisse - neela alla k�rvitsa j��nukid", "J��nust ei suudetud peeretada", "Sajajalgne", "Hukku", "Nuga ribidevahele vanapaks", "Sitapea", "T�ra", "Koristaja", "Kauri", "l��di teibasse", "hirmuks teistele", "T��phukkamine", "Ei ole");

$klang[19] = array('Brazillian Portuguese', 'ISO-8859-1', 'Portugu�s do Brasil', 'Mais Popular', 'Novo', 'Busca', '(apenas %1 encontrado)', 'seg', 'Resultados da busca: \'%1\'', 'encontrado', 'Nenhum.', 'atualizar op��es de busca na base de dados ', 'Apagar entradas sem uso? ', 'Reconstruir ID3?', 'Modo Debug?', 'Atualizar', 'Cancelar', 'Atualizar busca no banco de dados', 'Encontrados %1 arquivos.', 'N�o foi poss�vel determinar este arquivo: %1, descartado', 'Instala��o %1 - Atualizar: %2, escanear:', 'Escanear:', 'Falha na busca: %1', 'N�o foi poss�vel ler este arquivo: %1. Descartado.', 'Removido: %1', 'Inserido %1, atualizado %2, apagado %2, onde %4, falhou em %5, descartado por %6, arquivos - %7 seg - %8 marcado para ser deletado', 'Finalizado', 'Fechar', 'N�o foi encontrado nenhum arquivo aqui: "%1"', 'Logon kPlaylist', 'Lista de �lbum por artista: %1', 'Populares %1', 'Nenhuma m�sica selecionada. Lista n�o atualizada.', 'Lista atualizada!', 'Voltar', 'Lista atualizada', 'Lembre-se de atualizar a p�gina.', 'Login:', 'Senha:', 'Aten��o! Este n�o � um site restrito. Todas as a��es s�o monitoradas.', 'Login', 'SSL necess�rio para entrar.', 'Tocar', 'Apagar', 'Compartilhado', 'Salvar', 'Lista de controlhe: "%1" - %2 t�tulos', 'Editor', 'Visualizador', 'Selecionar', 'Seq', 'Status', 'Info', 'Del', 'Nome', 'Totais:', 'Erro', 'A��o selecionada:', 'Sequ�ncia:', 'editar lista', 'Apagar esta entrada', 'adicionar lista', 'Nome:', 'Criar', 'Tocar:', 'Arquivo', '�lbum', 'Todos', 'Selecionado', 'adicionar', 'tocar', 'editar', 'novo', 'Selecionar', 'Controle', 'Lista:', 'Selecionar n�mero', 'Keyteq oferece:', '(verificar atualiza��o)', 'P�gina inicial', 'apenas id3', '�lbum', 'T�tulo', 'Artista', 'Selecionar �lbum por artista', 'ver', 'Listas compartilhadas', 'Usu�rios', 'Controle de administrador', 'Este � novo', 'Este � popular', 'Sair', 'Op��es', 'Verificar', 'Meu', 'editar usu�rio', 'novo usu�rio', 'Nome completo', 'Login', 'Mudar senha?', 'Senha', 'Coment�rio', 'N�vel de acesso', 'Ligado', 'Desligado', 'Apagar usu�rio', 'Desconectar usu�rio', 'Atualizar', 'Novo usu�rio', 'apagar', 'desconectar', 'Utilizar op��o EXTM3U?', 'Mostrar quantos arquivos (popular/novo)', 'M�ximo de arquivos encontrados', 'Restaurar', 'Abrir diret�rio', 'Para o diret�rio: %1', 'Download', 'Subir um n�vel', 'Para o diret�rio principal', 'Verificar atualiza��es', 'usu�rios', 'Linguagem', 'op��es', 'Carregado', 'Aleat�rio', 'Configura��es', 'Diret�rio base', 'Local de stream', 'Linguagem padr�o', 'Sistema Windows', 'Requer HTTPS', 'Permitir busca', 'Permitir download', 'Sess�o expirou', 'Falha na tentativa de login', 'Aguarde - buscando a lista de arquivos', 'Lista n�o pode ser adicionada!', '0 = Admin, 1 = Usu�rio', 'In�cio de uma sess�o com o HTTPS a mudar', 'Habilite processo streaming', 'T�tulo', 'Artista', '�lbum', 'Coment�rio', 'Ano', 'Trilha', 'G�nero', 'Desativado', 'Taxa m�xima de download (kbps)', 'Usu�rio', '%1 minuto(s) - %2 T�tulos ', '%1 kbit %2 minuto(s)', 'Lista de G�neros: %1 ', 'Ir', '%1d %2h %3m Tocando %4 files %5 mb', 'Nenhum recurso relevante aqui.', 'Mundan�a Senha', 'Assine', 'Por favor fa�a uma sele��o', 'O que � foi atualizado?', 'Clique aqui para Ajuda', 'Usar Imagens Externas?', 'Path externo de imagens ', 'Senha Atual', 'A senha atual n�o confere', 'Arquivo preferido ', 'Arquivo n�o pode ser feito', 'Provavelmente encontrado arquivo duplicado: "%1" "%2"', 'Deseja apagar a lista?', 'Alfab�tico', 'Rand�mico', 'Tipo', 'Original', 'Usar javascript', 'Voc� deseja realmente deletar este usu�rio?', 'Ver descri��o', 'Descri��o', 'Filas', 'Arquivo CSS externo', 'Remover duplos', 'OK', 'ERR', 'Stream', '(mostrar como)', 'arquivos', '�lbuns', '%1d %2h %3m %4s', 'Geral', 'Customizar', 'Menu do arquivo', 'Clique em ? para ajuda.', 'Autom�tico banco de dados sync', 'Enviar extens�o de arquivo ', 'Permitir streams n�o autorizados ', 'Incluir cabe��lho', 'Javascript externo', 'Homepage', 'Exibir o que Keyteq lhe oferece a parte', 'Mostrar atualiza��o a parte', 'Mostrar estat�sticas', 'Escrever ID3v2 com stream', 'Habilite assinatura do usu�rio', 'Tipo de arquivos', 'Sim', 'N�o', 'Extens�o', 'MIME', 'Incluir no M3U', 'Editar tipo de arquivo', '� isso mesmo?', 'Optimistic filecheck', 'Randomizar', 'Modo', 'Lista', 'Nenhum, direto', 'Meus favoritos', 'N�o foi encontrado nenhum sucesso (hit)', 'Sempre sucessos (hits)', 'Ordem', 'Habilitar suporte LAME?', 'Desabilitado', 'Pertimir o uso de LAME?', 'E-mail', 'Permitir o envio de arquivos por e-mail?', 'Servidor SMTP', 'Porta SMTP', 'E-mail para', 'Mensagem', 'Enviar', 'E-mail enviado!', 'Ativar upload', 'Diret�rio de uploads', 'Ativar mp3mail', 'Upload', 'Upload completo', 'N�o foi poss�vel fazer upload do arquivo', '� necess�rio ativar cookies para o login', 'Per�odo', 'Sempre', 'Esta semana ', 'Este m�s', '�ltimo m�s', 'Sucessos (hits)', 'Comando LAME', 'Exibir capa do �lbum', 'Arquivos do �lbum', 'Mudar o tamanho das imagens do �lbum', 'Altura do �lbum', 'Largura do �lbum', 'M�todo de enviar e-mail', 'Direto', 'Pear', 'Aguarde', 'Por favor, insira seu e-mail v�lido nas op��es', 'Listas em espera?', 'Exibir �lbum da URL', 'URL do �lbum', 'N�o foi poss�vel enviar!', 'Usu�rio adicionado!', 'Compressor de arquivos', 'Arquivo deletado.');

$klang[20]  = array("Simplified Chinese", "big5", "��������", "�����Ƽ�", "�������", "����", "Ŀǰֻ�� %1", "��", "�����������%1��", "���ҵ�", "û��", "�����������ݿ�ѡ��", "ɾ��δʹ�õļ�¼��", "�ؽ�ID3��ǩ��", "�Ŵ�ģʽ��", "����", "ȡ��", "�����������ݿ�", "���ҵ� %1 ���ļ�", "�޷�ʶ����ļ���%1����������", "�Ѱ�װ��%1 -���£�%2��ɨ�裺", "ɨ�裺", "��ѯ��%1��ʧ����", "�޷���ȡ���ļ���%1����������", "%1�ѱ�ɾ����", "����%4����%1������%2��ɾ��%3", "�����", "�ر�", "�ڡ�%1���Ҳ����κ��ļ�", "��½KPlayList", "��%1����ר���б�", "��ѡ%1", "δѡ��Ƶ���������б�δ���£�", "�����б��ѱ����£�", "����", "�����б�����ӣ�", "��ǵ�ˢ��ҳ�棡", "�ʺţ�", "���ܷ��ʣ�", "��ע�⣡����վ���ǹ����ģ����в�������ϵͳ��¼��", "��½", "��½��ҪSSL֧�֣�", "����", "ɾ��", "����", "����", "���Ʋ����б���%1��-%2 ����", "�༭�ˣ�", "�鿴�ߣ�", "ѡ��", "��", "״̬", "��Ϣ", "ɾ��", "����", "�ܼƣ�", "����", "����ѡ��ʱ��", "���⣺", "�༭�����б�", "ɾ���˼�¼", "��Ӳ����б�", "���ƣ�", "����", "���ڲ��ţ�", "�ļ�", "ר��", "ȫ��", "��ѡ�е�", "���", "����", "�༭", "��", "ѡ��", "���ſ��ƣ�", "�����б�", "��ѡ��Ŀ", "Keyteq ��ʾ��", "�������£�", "��վ", "��ID3", "ר��", "����", "������", "�����Ҽ�ѡר��", "�鿴", "������Ĳ����б�", "�û�", "����Ա�������", "�������", "�����Ƽ�", "�˳�", "ѡ��", "���", "�ҵ�", "�༭�û���Ϣ", "�������û��ʺ�", "ȫ��", "�ʺ�", "�������룿", "����", "ע��", "����Ȩ��", "��", "��", "ɾ���û�", "ʹ�û��˳�", "ˢ��", "�������û��ʺ�", "ɾ��", "�˳�", "ʹ��EXTM3U���ԣ�.m3u��", "�鿴����������/���£�", "�����������", "����", "��Ŀ¼", "���뵽Ŀ¼��%1", "����", "������һ��Ŀ¼", "���ظ�Ŀ¼", "�������", "�û�", "����", "ѡ��", "�ѱ�ϵͳ�߳�", "���򲥷ţ�", "����", "��Ŀ¼", "���ļ�Դ", "ȱʡ����", "Windowsϵͳ", "��ҪHTTPS", "��������", "��������", "Session���̳�ʱ", "����ʧ�ܵĵ�½������Ϊ", "���Եȡ������ڶ�ȡ�ļ��б�", "�����б��޷�����ӣ�", "����Ա", "����HTTPS��ʽ��½", "��������Ч", "����", "������", "ר��", "ע��", "��", "����", "����", "δ����", "�����������(Kbps)", "�û�", "%1 ���� - %2 ������", "%1 ǧ���� %2 ����", "�����б�", "ȷ��", "%1d %2h %3m ����ʱ�� %4 ���ļ� %5 ��", "û�������Դ", "�����Ѿ��ɹ��޸ģ�", "��½", "��ѡ��һ�", "�����ʲô���£�", "��������ȡ����", "ʹ����չͼ����ʾ��", "��չͼƬ·��", "��ǰ����", "��ǰ���뻥��ƥ�䣡", "��ȡ�õĴ浵", "�޷��浵", "������ͬ���ļ�%1-%2�ҵ���", "���ɾ�������б�", "����ĸ˳������", "�������", "����", "��Դ");

$klang[21]  = array("Catalan", "iso-8859-1", "Catal�", "El m�s nou", "Novetat", "Cerca", "(nom�s es mostra %1)", "seg", "Resultats de la Recerca: '%1'", "trobat", "Cap.", "actualitza les opcions de recerca a la base de dades", "Esborrar registres no utilitzats?", "Regenerar ID3?", "Mode depuraci�?", "Actualitza", "Cancel�la", "Actualitza base de dades de recerca", "Trobats %1 fitxers.", "No puc determinar aquest fitxer: %1, l'ignoro.", "Instal�lat: %1 - Actualitzat: %2, Escanejat:", "Scanejat:", "Error - query: %1", "No puc llegir aquest arxiu: %1. L'ignoro.", "Esborrat: %1","Insertat %1, actualitzat %2, esborrat %3 amb %4 errors i %5 ignorats de %6 arxius - %7 seg - %8 marcats per esborrar.", "Fet", "Tanca", "No he trobat cap arxiu a: \"%1\"", "Entrar a kPlaylist", "Llista d'�lbums de l'artista: %1", "Marcat %1", "No s'han sel�leccionat can�ons. Playlist no actualitzada.", "Playlist actualitzada!", "Tornar", "Playlist afegida!", "Recorda recarregar la p�gina.", "Entrar:", "Secret:", "Compte! Aix� �s una WEB no p�blica. Totes les accions es registren. ", "Entrar", "Es requereix SSL per entrar.", "Reprodueix", "Esborra", "Compartit:", "Graba.", "Playlist de Control: \"%1\" - %2 t�tols", "Editor", "Visualitzador", "Sel�lecciona", "Seq", "Estat", "Info", "Esborra", "Nom", "Totals:", "Error", "Accions en sel�leccionar:", "Seq��ncia:", "edita Playlist","Esborra aquesta entrada", "afegeix playlist", "Nom:", "Crea", "Reprodueix:", "Arxiu", "�lbum", "Tot", "Sel�leccionat", "afegeix", "reprodueix", "edita", "nou", "Sel�lecciona:", "Control de reproducci�:", "Playlist;", "Sel�lecci� num�rica", "Keyteq et dona:", "(actualitzaci� de soft)", "Homesite", "nom�s id3", "�lbum", "t�tol", "artista", "�lbum sel�leccionat de l'artista", "veure", "Playlists compartits", "Usuaris", "Control d'Administrador", "Que hi ha de nou", "Que hi ha novedos", "Sortir", "Opcions", "Txequeja", "Jo", "edita usuari", "nou usuari", "Nom complet", "Entrada", "Canviar password?", "Password", "Comentari", "Nivell d'acc�s", "On", "Off", "Esborrar usuari", "Desconnectar usuari", "Refrescar", "Nou usuari", "esborra", "sortir", "Utilitzar caracter�stiques EXTM3U?", "Mostrar quantes columnes (hot/nou)", "M�xim de columnes de recerca", "Resetejar", "Obrir directori", "Anar al directori: %1", "Descarregar", "Pujar un nivell", "Anar al directori root.", "Txequeja actualitzacions.", "usuaris", "Llenguatge", "opcions", "Iniciat", "Aleatori:", "Configuraci�", "directori base", "Localitzaci� d'Stream", "Llenguatge per defecte", "Sistema Windows", "Necessita HTTPS", "Permetre recerques", "Permetre desc�rregues", "Temps de sessi� (COOKIE)", "Reporta errors d'intent d'entrada", "Espera. Recuperant llista de fitxers.", "No es pot afegir la Playlist!", "Admin", "Entra per HTTPS per acceptar els canvis!", "Activa el motor d'streaming", "T�tol", "Artista", "�lbum", "Comentaris", "Any", "Pista", "G�nere", "no especificat", "M�xim ample de desc�rrega (kbps)", "Usuari", "%1 mins - %2 t�tols", "%1 kbit %2 mins", "Llista de g�neres: %1", "Som-hi", "Temps de reproducci� %1d %2h %3m %4 arxius %5 mb", "No hi ha arxius relevants.", "Password canviat!", "Signa", "Siusplau fes una sel�lecci�!", "Que hi ha de nou?", "Clica aqu� per a ajuda", "Utilitza imatges externes?", "Cam� per a imatges externes", "Password actual", "Password actual no coincideix!", "Arxivador preferit", "No es pot crear l'arxiu", "Trobat un problable arxiu duplicat: %1 - %2", "Esborrar Playlist de deb�?", "Alfab�tic", "Al�leatori", "Ordena", "Original", "Utilitza javascript", "Estas segur que vols esborrar aquest usuari?", "Veure historial", "Historial", "Files", "Arxiu CCS extern");

$klang[22]  = array("Bulgarian", "windows-1251", "���������", "��� � �����?", "��� � ����?", "�������", "(only %1 shown)", "���", "�������� �� ���������: '%1' ", "�������", "����.", "���������� �� ����������� �� ������� - �����", "��������� �� �������������� ������?", "������������� ID3? ", "������������ �� ��������� ?", "����������", "�����", "���������� �� ����������� �� �������", "�������� %1 �����. ", "������ �� �� �������� ���� ����: %1, ���������.  ", "����������: %1 - ����������: %2, ���������:", "���������:", "������ - ������: %1 ", "���� ���� ������ �� �� �������: %1. ���������.", "����������: %1 ","������� %1, ���������� %2, ������� %3 ���� %4 failed and %5 skipped through %6 files - %7 sec - %8 marked for deletion", "������", "�������", "������ �� �� ������� ������� ������� ����:  \"%1\" ", "kPlaylist ����", "������ �� �������� �� �����: %1", "���� ����� %1 ", "������� ����� �� �� �������. Playlist �� � �������. ", "Playlist ����������!", "�������", "Playlist ��������!", "������������ �� ����������� ����������.", "���:", "������:", "���������! ���� �� � ��������� ����. ������ �������� �� ��������.", "����", "���������� � SSL �� �������.", "�����", "������", "�������:", "������", "���������� playlist: \"%1\" - %2 ��������", "��������", "��������", "������", "������.", "���������", "����������", "����.", "���", "����:", "������", "�������� �� ���������:", "����������������:", "�������� playlist ", "�������� ����", "�������� playlist ", "���:", "������", "�����:", "����", "�����", "������", "������", "������", "�����", "�����������", "���", "������:", "������� �� �������:", "Playlist:", "����� �������� �� �����", "Keyteq gives you:", "(������� �� ����������)", "Homesite ", "���� id3 ", "�����", "��������", "�����", "����� �������� �� �����", "���", "������� playlists ", "�����������", "�����. ����������", "��� � ����?", "��� � �����?", "��������", "���������", "���", "���", "����������� �� �����������", "��� ����������", "����� ���", "���(���)", "����� �� ��������?", "������", "��������", "����� �� �������", "���.", "����.", "��������� �� ����������", "�������� �� ����������", "����������", "��� ����������", "����.", "�����", "���������� �� Win�mp EXTM3U?", "��������� �� ����� ������ (������/����)", "�������� ������ �� �������", "�����", "������ ����������", "����� � ����������:  %1 ", "�������", "����� ��� ������ ������", "����� � �������� ����������.", "������� ������������", "������������", "����", "�����", "Booted", "Shuffle:", "���������", "������ ����������", "Stream location", "���� �� ������������", "A Windows system", "��������� HTTPS ", "Allow seek", "������ �������", "����� �� �������", "������ �� ���������� �� ������", "Hold on - fetching file list", "Playlist  ������ �� ���� �������!", "�������������", "���� � HTTPS �� �����!", "�������� streaming engine ", "��������", "�����", "�����", "��������", "������", "�����", "����", "�� �������", "����. ������� �� ������� (kbps)", "����������", "%1 ���. - %2 �������� ", "%1 kbit %2 ���. ", "������ ������: %1", "�����", "%1��� %2�. %3�. ����� �� ������� %4 ������� %5 ��.", "No relevant resources here.", "�������� � �������!", "�����������", "���� ��������� �����!", "����� �� ���������", "��������� ��� �� �����", "���������� �� ������ ������?", "���� �� �������� ������", "������ ������", "�������� ������ �� �������!", "Preferred archiver", "������ ������ �� ���� ��������", "Probable file duplicate found:  %1 - %2", "������ �� �� �������� playlist?", "�� ������� ���", "���������", "���������", "��������", "��������� javascript ", "������� �� ���, �� ������ �� �������� ���� ����������?", "��� ���������", "�������", "������", "������ CSS ����");

$klang[23]  = array("Polish", "ISO-8859-2", "Polski", "Popularne", "Nowo&#347;ci", "Wyszukaj", "pokazano tylko %1", "sek", "Wyniki wyszukiwania: \'%1\'", "znaleziono", "Nic.", "aktualizacja opcji wyszukiwania bazy", "Usun&#261;&#263; nieu&#380;ywane wpisy?", "Odbudowa&#263; ID3?", "Tryb usuwania b&#322;&#281;d�w?", "Aktualizacja", "Anuluj", "aktualizacja wyszukiwania bazy", "Znaleziono %1 plik�w", "Nie mo&#380;na okre&#380;li&#263; po&#322;o&#380;enia pliku: %1", "Instalacja: %1 - Aktualizacja: %2, badanie:", "Skanowanie:", "Niepowodzenie - pytanie: %1", "Nie mo&#380;na odczyta&#263; tego pliku: %1. Pomini&#281;cie.", "Usuni&#281;to: %1", "Wstawiono %1, uaktualniono %2, usuni&#281;to %3 gdzie %4 uszkodzonych i %5 pomini&#281;to z powodu %6 plik�w - %7 sek - %8 zaznaczonych do usuni&#281;cia. ", "Sko&#324;czone", "Zamknij", "Nie mo&#380;na znale&#378;&#263; tutaj &#380;adnych plik�w: \"%1\"", "Logowanie kPlaylist", "Lista album�w dla wykonawcy: %1", "popularny wyb�r %1", "nie wybrana melodia. Playlista nie zaktualizowana.", "Playlista zaktualizowana!", "Wstecz", "Playlista dodana!", "Pami&#281;taj o prze&#322;adowaniu strony", "login:", "has&#322;o:", "Uwaga! To nie jest strona publiczna. Wszystkie akcje s&#261; rejestrowane.", "Login", "Do zalogowania wymagany jest SSL", "Odgrywaj", "Usu&#324;", "Wsp�lny:", "Zapisz", "Kontrola playlist: \"%1\" - %2 tytu&#322;y", "Edytor", "Przegl&#261;darka", "Zaznacz", "Ci&#261;g", "Status", "Info", "Kasuj", "Nazwa", "Podsumowanie:", "B&#322;&#261;d", "Akcja na zaznaczonych:", "Kolejno&#347;&#263;", "edytuj playlist&#281;", "Usu&#324; ten zapis", "dodaj playlist&#281;", "Nazwa:", "Utw�rz", "Odtwarzaj:", "Plik", "Album", "Wszystko", "Wybrane", "dodaj", "odtwarzaj", "edytuj", "nowe", "Zaznacz:", "Kontrol odtwarzania:", "Playlista:", "popularne numery", "Tw�j identyfikator:", "(sprawd&#378; czy s&#261; poprawki)", "Stona domowa", "tylko id3", "album", "tytu&#322;", "wykonawca", "Popularne albumy wykonawcy", "widok", "Wsp�lne playlisty", "U&#380;ytkownicy", "Panel administratora", "Nowo&#347;ci", "Popularne", "Wylogowanie", "Opcje", "Sprawd&#378;", "M�j", "edytuj u&#380;ytkownika", "nowy u&#380;ytkownik", "Pe&#322;na nazwa", "Login", "Zmieni&#263; has&#322;o?", "Has&#322;o", "Komentarz", "poziom dost&#281;pu", "W&#322;&#261;czony", "Wy&#322;&#261;czony", "Usu&#324; u&#380;ytkownika", "Wyloguj u&#380;ytkownika", "Od&#347;wie&#380;", "Nowy u&#380;ytkownik", "usu&#324;", "wyloguj", "Mo&#380;liwo&#347;&#263; u&#380;ycia EXTM3U?", "Ile pokaza&#263; wierszy (popularne/nowe)", "Max przeszukiwanych wierszy", "Resetuj", "Otw�rz katalog", "Id&#378; do katalogu: %1", "Pobierz", "Id&#378; katalog wy&#380;ej", "Id&#378; do katalogu g&#322;�wnego", "Sprawd&#378; czy s&#261; poprawki", "u&#380;ytkownicy", "J&#281;zyk", "opcje", "Inicjowanie", "Mieszanie:", "Ustawienia", "Katalog bazowy", "Lokalizacja strumienia", "Domy&#347;lny j&#281;zyk", "System Windows?", "Wymagane HTTPS", "Wszyscy mog&#261; ogl&#261;da&#263;", "Wszyscy mog&#261; &#347;ci&#261;ga&#263;", "Maksymalny czas sesji", "Raportuj b&#322;&#281;dne pr�by logowania", "W&#322;&#261;cz wstrzymywanie - najlepsze listy plik�w", "Playlista nie mo&#380;e by&#263; dodana!", "Administrator", "Zaloguj z HTTPS aby zmieni&#263;!", "Aktywny strumie&#324; silnika", "Tytu&#322;", "Wykonawca", "Album", "Komentarz", "Rok", "&#346;cie&#380;ka", "Rodzaj", "nie ustawione", "Max pr&#281;dko&#347;&#263; &#347;ci&#261;gania (kbps)", "U&#380;ytkownik", "%1 minuty - %2 tytu&#322;y", "%1 kbit %2 minuty", "Rodzaj listy: %1", "Id&#378;", "%1d %2h %3m czas odtwarzania %4 plik�w %5 MB", "Nie zwi&#261;zany z tymi zasobami", "Has&#322;o zmienione!", "Wy&#347;lij", "Prosz&#281; wykona&#263; zaznaczenie!", "Co to jest aktualizacja?", "Kliknij tutaj aby uzyska&#263; pomoc", "U&#380;y&#263; zewn&#281;trznych obrazk�w?", "&#346;cie&#380;ka zewn&#281;trznych obrazk�w", "Bie&#380;&#261;ce has&#322;o", "Bie&#380;&#261;ce has&#322;o nie jest w&#322;a&#347;ciwe!", "Preferowany archiwizator", "Archiwum nie mo&#380;e zosta&#263; utworzone", "Prawdopodobnie znaleziono duplikat pliku: %1 - %2", "Na pewno usun&#261;&#263; pleylist&#281;?", "Alfabetycznie", "Losowo", "Sortuj", "Oryginalnie", "U&#380;yj javascript", "Czy jeste&#347; pewny, &#380;e chcesz usun&#261;&#263; tego uzytkownika?", "Przegl&#261;daj histori&#281;", "historia", "Wiersze", "Zewn&#281;trzny plik CSS");

$klang[24]  = array('Lithuanian', 'ISO-8859-13', 'Lietuvi�kai', 'Da&#254;niausiai klausomi', 'Nauja', 'Paie&#240;ka', '(rodoma tiktai %1)', 'sec', 'Paie&#65533;kos rezultatai: \'%1\'', 'rasta', 'N&#279;ra.', 'atnaujinti pai&#65533;kos duomen&#371; baz&#279;s nustatymus', 'I&#65533;trinti nereikalingus &#303;ra&#65533;us?', 'Atnaujinti ID3?', 'Su klaid&#371; aptikimu?', 'Atnaujinti', 'Nutraukti', 'atnaujinti paie&#65533;kos duomen&#371; baz&#281;', 'Rasta %1 fail&#371;.', 'Neina nustatyti &#65533;io failo: %1, praleid&#65533;iam.', '&#302;diegta: %1 - Atnaujinti: %2, skenuoti:', 'Skenuoti:', 'Nepavykusi u&#65533;klausa: %1', 'Neina perskaityt &#65533;io failo: %1. Praleid&#65533;iam.', 'Pa&#65533;alinta: %1', '&#302;traukta %1, atnaujinta %2, i&#65533;trinta %3 kur %4 nepavyk&#281; ir %5 praleisti i&#65533; %6 fail&#371; - %7 sec - %8 pa&#65533;ym&#279;ti i&#65533;trynimui.', 'Atlikta', 'U&#65533;daryti', 'Nepavyko rasti joki&#371; fail&#371; &#269;ia: "%1"', 'kPlaylist Prisijungimas', 'Album&#371; s&#261;ra&#65533;as pagal autori&#371;: %1', 'Populiariausi %1', 'Nepa&#65533;im&#279;jote n&#279; vieno failo. Playlist\'as neatnaujintas.', 'Playlist\'as atnaujintas!', 'Atgal', '&#302;trauktas Playlist\'as.', 'Neu&#65533;mir&#65533;kite perkrauti puslapio.', 'vartotojo vardas:', 'slapta&#65533;odis:', 'D&#279;mesio! Tai ne vie&#65533;as interneto puslapis. Visi veiksmai yra &#303;ra&#65533;omi.', 'Prisijungti', 'SSL reikia norint prisijungti.', 'Groti', 'I&#65533;trinti', 'Vie&#65533;i:', 'I&#65533;saugoti', 'Redaguojamas Playlist\'as: "%1" - %2 pavadinimai', 'Redaktorius', 'Per&#65533;valga', 'Pa&#65533;ym&#279;ti', 'T&#281;sinys', 'Pad&#279;tis', 'Info', 'I&#65533;trinti', 'Vardas', 'I&#65533;viso:', 'Klaida', 'Atlikti veiksm&#261; su pa&#65533;ym&#279;tais:', 'Eil&#279;s tvarka:', 'redaguoti playlist\'&#261;', 'I&#65533;trinti &#65533;&#303; &#303;ra&#65533;&#261;', '&#303;traukti playlist\'&#261;', 'Vardas:', 'Sukurti', 'Groti:', 'Failas', 'Albumas', 'Visi', 'Pa&#65533;ym&#279;tus', '&#303;traukti', 'groti', 'redaguoti', 'naujas', 'Pa&#65533;ym&#279;ti:', 'Grojimo valdymas:', 'Playlist\'as:', 'Pasirink&#371; numeravimas', 'Keyteq si&#363;lo:', '(patikrinti ar naudoji naujausi&#261; versij&#261;)', 'J&#363;s&#371; puslapis', 'tiktai id3', 'albumas', 'pavainimas', 'atlik&#279;jas', 'Pa&#65533;ym&#279;ti atlik&#279;jo album&#261;', 'per&#65533;i&#363;r&#279;ti', 'Vie&#65533;i playlist\'ai', 'Vartotojai', 'Admin valdymas', 'Naujienos', 'Da&#65533;niausiai', 'Atsijungti', 'Nustatymai', 'Pa&#65533;ym&#279;ti', 'Mano', 'redaguoti vartotoj&#261;', 'naujas vartotojas', 'Pilnas vardas', 'Vartotojo vardas', 'Pakeisti slapta&#65533;od&#303;?', 'Slapta&#65533;odis', 'Komentaras', 'Vartotojo lygis', '&#302;jungta', 'I&#65533;jungta', 'I&#65533;trinti vartotoj&#261;', 'Atjungti vartotoj&#261;', 'Perkrauti', 'Naujas vartotojas', 'i&#65533;trinti', 'atjungti', 'Naudoti EXTM3U?', 'Kiek rodyti stulpeli&#371;?', 'Daugiausia paie&#65533;kos eilu&#269;i&#371;', 'Atstatyti', 'Atidaryti direktorij&#261;', 'Eiti &#303;: %1', 'Parsisi&#371;sti', 'Vienu ejimu atgal', '&#302; root direktorij&#261;', 'Patikrinti atnaujinim&#261;', 'vartotojai', 'Kalba', 'nustatymai', 'Pakrautas', 'Mai&#65533;yti:', 'Nustatymai', 'Pradin&#279; direktorija', 'Stream vieta', 'Pagrindin&#279; kalba', 'Windows sistema', 'Reikalauti HTTPS', 'Leisti paie&#65533;k&#261;', 'Leisti parsisiuntimus', 'Session timeout', 'Prane&#65533;ti apie nepavykusius prisijungimus', 'Palaukite - sudaromas fail&#371; s&#261;ra&#65533;as', 'Playlist\'o neina &#303;traukti', 'Admin', 'Prisijunkite su HTTPS nor&#279;dami k&#261; nors pakeisti!', 'Leisti streming', 'Pavadinimas', 'Atlik&#279;jas', 'Albumas', 'Komentaras', 'metai', 'Takelis', '&#65533;anras', 'nenustatyta', 'Did&#65533;iausias siuntimosi greitis', 'Vartotojas', '%1 min - %2 pavadinimai', '%1 kbit %2 min', '&#65533;anr&#371; s&#261;ra&#65533;as: %1', 'Eiti', '%1 d %2h %3m grojimo laikas %4 fail7 %5 mb', 'N&#279;ra susijusi&#371; resurs&#371;.', 'Slapta&#65533;odis pakeistas.', 'Prisiregistruoti', 'Pasirinkite!', 'Kas yra - Atnaujinimas?', 'Pagalba', 'Naudoti i&#65533;orinius paveiksliukus', 'I&#65533;orini&#371; paveiksliuk&#371; vieta', 'Dabartrinis slapta&#65533;odis', 'Slapta&#65533;od&#65533;iai nesutampa', 'Naudojamas archyvatorius', 'Nepavyko sudaryti archyvo', 'Grei&#269;iausiai rasti du vienodi failai: "%1" "%2"', 'I&#65533;trinti playlist\'&#261;?', 'Alfabeti&#65533;kai', 'Atsitiktinai', 'Sutraukti', 'Orginaliai', 'Naudoti javascript', 'Ar tikrai norite i&#65533;trinti &#65533;&#303; vartotoj&#261;?', 'Per&#65533;i&#363;r&#279;ti istorij&#261;', 'istorija', 'Eilut&#279;s', 'I&#65533;orinis CSS failas', 'I&#65533;trinti dublikatus', 'Taip', 'Klaida', 'Stream', '(rodyti kaip)', 'failai', 'albumai', '%1d %2h %3m %4s', 'Pagrindinis', 'Redaguoti', 'Fail&#371; palaikymas', 'Paspauskite ant ? kad gaut pagalb&#261;.', 'Automatinis Duomen&#371; baz&#279;s atnaujinimas', 'Nusi&#371;sti failo pl&#279;tin&#303;', 'Leisti neautorizuotus streamus', '&#302;traukti headerius', 'I&#65533;orinis javascript', 'Puslapis', 'Rodyti Keuteq duoda tau', 'Rodyti atnaujinim&#261;', 'Rodyti statistik&#261;', '&#302;ra&#65533;yti ID3v2 su streamu', 'Leisti vartotoj&#371; prisiregistravim&#261;', 'Fail&#371; tipai', 'Taip', 'Ne', 'Pl&#279;tinys', 'MIME', '&#302;traukti M3U', 'redaguoti fail&#371; tip&#261;', 'Tikrai?', 'Optimistinis fail&#184; patikrinimas', 'Sumai&#240;yti', 'Metodas', 'Playlistas', 'N&#235;ra, tiesiogiai', 'M&#235;gstamiausi', 'Nerasta nei vieno paspaudimo', 'Vis&#184; laik&#184; hitai', 'U&#254;sisakyti', '&#193;jungti LAME palaikym&#224;?', 'I&#240;jungta', 'Lesti naudotis LAME?', 'El. pa&#240;tas', 'Lesiti si&#184;sti failus el. pa&#240;tu?', 'SMTP serveris', 'SMTP portas', 'Kam si&#184;sti', '&#222;inut&#235;', 'Si&#184;sti', 'Lai&#240;kas i&#240;si&#184;stas!', 'Aktyvuoti atsiuntimus', 'Atsiuntim&#184; direktorija', 'Aktivuoti mp3pa&#240;t&#224;', 'Atsi&#184;sti', 'Failas atsi&#184;stas', 'Nepavyko atsi&#184;sti failo!', 'Cookies palaikymas turi b&#251;ti &#225;jungtas jei norite prisijungti!', 'Periodas', 'kadanors', '&#240;i&#224; savait&#191;', '&#240;&#225; m&#235;nes&#225;', 'praeit&#224; m&#235;nes&#225;', 'paspaudimai', 'LAME komanda', 'Rodyti albumo vir&#240;el&#225;', 'Albumo failai', 'Pakeisti albumo paveiksliuk&#184; dyd&#225;', 'Albumo auk&#240;tis', 'Albumo plotis', 'Siuntimo el. pa&#240;tu metodas', 'Tiesiogiai', 'Netiesiogiai', 'Palaukti', '&#193;veskite teising&#224; el. pa&#240;to adres&#224; nustatymuose.', 'Playlist\'as inline?', 'Rodyti album&#224; i&#240; nuorodos?', 'Albumo nuoroda', 'Nepavyko nusi&#184;sti!', 'Vartotojas &#225;trauktas!', 'Archyv&#224; suk&#251;r&#235;', 'Archyvas i&#240;trintas.', 'Vartotojo apra&#240;ymas atnaujintas!', 'Atitikmenys', '%1 &#225;ra&#240;�', 'Pri�jimas prie log�', 'Skaitoma', 'Suarchyvuota', 'Suvestin&#235;', '&#193;vesta %1 - %2', 'daugiau', 'Publikuoti', '%1 mb', '%1 kb', '%1 bait�');

$klang[25]  = array("Thai", "ISO-8859-11", "&#3652;&#3607;&#3618;", "&#3617;&#3634;&#3651;&#3627;&#3617;&#3656;", "&#3617;&#3634;&#3649;&#3619;&#3591;", "&#3588;&#3657;&#3609;&#3627;&#3634;", "(&#3649;&#3626;&#3604;&#3591;&#3648;&#3593;&#3614;&#3634;&#3632; %1)", "&#3623;&#3636;&#3609;&#3634;&#3607;&#3637;", "&#3612;&#3621;&#3585;&#3634;&#3619;&#3588;&#3657;&#3609;&#3627;&#3634; :\'%1\'", "&#3614;&#3610;", "&#3652;&#3617;&#3656;", "&#3611;&#3619;&#3633;&#3610;&#3611;&#3619;&#3640;&#3591;&#3605;&#3633;&#3623;&#3648;&#3621;&#3639;&#3629;&#3585;&#3600;&#3634;&#3609;&#3586;&#3657;&#3629;&#3617;&#3641;&#3621;&#3626;&#3635;&#3627;&#3619;&#3633;&#3610;&#3588;&#3657;&#3609;&#3627;&#3634;", "&#3621;&#3610;&#3648;&#3619;&#3588;&#3588;&#3629;&#3619;&#3660;&#3604;&#3607;&#3637;&#3656;&#3652;&#3617;&#3656;&#3648;&#3588;&#3618;&#3651;&#3594;&#3657;", "&#3626;&#3619;&#3657;&#3634;&#3591; ID3 &#3651;&#3627;&#3617;&#3656;", "&#3648;&#3611;&#3636;&#3604; Debug Mode", "&#3611;&#3619;&#3633;&#3610;&#3611;&#3619;&#3640;&#3591;", "&#3618;&#3585;&#3648;&#3621;&#3636;&#3585;", "&#3611;&#3619;&#3633;&#3610;&#3611;&#3619;&#3640;&#3591;&#3600;&#3634;&#3609;&#3586;&#3657;&#3629;&#3617;&#3641;&#3621;&#3651;&#3609;&#3585;&#3634;&#3619;&#3588;&#3657;&#3609;&#3627;&#3634;", "&#3614;&#3610;&#3607;&#3633;&#3657;&#3591;&#3627;&#3617;&#3604; %1 &#3652;&#3615;&#3621;&#3660;", "&#3652;&#3617;&#3656;&#3626;&#3634;&#3617;&#3634;&#3619;&#3606;&#3619;&#3632;&#3610;&#3640;&#3652;&#3615;&#3621;&#3660; %1 , &#3586;&#3657;&#3634;&#3617;&#3652;&#3611;", "&#3605;&#3636;&#3604;&#3605;&#3633;&#3657;&#3591;: %1 -&#3611;&#3619;&#3633;&#3610;&#3611;&#3619;&#3640;&#3591;: %2 ,&#3605;&#3619;&#3623;&#3592;&#3627;&#3634;", "&#3605;&#3619;&#3623;&#3592;&#3627;&#3634;", "&#3585;&#3634;&#3619;&#3588;&#3657;&#3609;&#3627;&#3634;&#3612;&#3636;&#3604;&#3614;&#3621;&#3634;&#3604; :%1", "&#3652;&#3617;&#3656;&#3626;&#3634;&#3617;&#3634;&#3619;&#3606;&#3629;&#3656;&#3634;&#3609;&#3652;&#3615;&#3621;&#3660; : %1 &#3586;&#3657;&#3634;&#3617;&#3652;&#3611;", "&#3621;&#3610; %1", "&#3648;&#3614;&#3636;&#3656;&#3617; %1 ,&#3611;&#3619;&#3633;&#3610;&#3611;&#3619;&#3640;&#3591; %2,&#3621;&#3610; %3,&#3607;&#3637;&#3656; %4,&#3612;&#3636;&#3604;&#3614;&#3621;&#3634;&#3604;&#3649;&#3621;&#3632; %5 ,&#3586;&#3657;&#3634;&#3617;&#3652;&#3611; %6 &#3652;&#3615;&#3621;&#3660; %7 &#3623;&#3636;&#3609;&#3634;&#3607;&#3637; %8 &#3607;&#3635;&#3648;&#3588;&#3619;&#3639;&#3656;&#3629;&#3591;&#3627;&#3617;&#3634;&#3618;&#3648;&#3614;&#3639;&#3656;&#3629;&#3621;&#3610;", "&#3648;&#3619;&#3637;&#3618;&#3610;&#3619;&#3657;&#3629;&#3618;", "&#3611;&#3636;&#3604;", "&#3652;&#3617;&#3656;&#3614;&#3610;&#3652;&#3615;&#3621;&#3660;&#3652;&#3604;&#3654;&#3607;&#3637;&#3656;&#3617;&#3637;&#3626;&#3656;&#3623;&#3609;&#3611;&#3619;&#3632;&#3585;&#3629;&#3610; \"%1\"", "&#3648;&#3586;&#3657;&#3634;&#3626;&#3641;&#3656;&#3619;&#3632;&#3610;&#3610;", "&#3619;&#3634;&#3618;&#3585;&#3634;&#3619;&#3629;&#3633;&#3621;&#3610;&#3633;&#3617;&#3626;&#3635;&#3627;&#3619;&#3633;&#3610;&#3624;&#3636;&#3621;&#3611;&#3636;&#3609; : %1", "&#3606;&#3641;&#3585;&#3648;&#3621;&#3639;&#3629;&#3585;&#3617;&#3634;&#3585;&#3607;&#3637;&#3656;&#3626;&#3640;&#3604; %1", "&#3652;&#3617;&#3656;&#3614;&#3610;&#3619;&#3634;&#3618;&#3585;&#3634;&#3619;&#3607;&#3637;&#3656;&#3648;&#3621;&#3639;&#3629;&#3585; &#3619;&#3634;&#3618;&#3585;&#3634;&#3619;&#3648;&#3621;&#3656;&#3609;&#3652;&#3617;&#3656;&#3652;&#3604;&#3657;&#3617;&#3637;&#3585;&#3634;&#3619;&#3611;&#3619;&#3633;&#3610;&#3611;&#3619;&#3640;&#3591;", "&#3611;&#3619;&#3633;&#3610;&#3611;&#3619;&#3640;&#3591;&#3619;&#3634;&#3618;&#3585;&#3634;&#3619;&#3648;&#3621;&#3656;&#3609;&#3649;&#3621;&#3657;&#3623;", "&#3618;&#3657;&#3629;&#3618;&#3585;&#3621;&#3633;&#3610;", "&#3648;&#3614;&#3636;&#3656;&#3617;&#3648;&#3586;&#3657;&#3634;&#3619;&#3634;&#3618;&#3585;&#3634;&#3619;&#3648;&#3621;&#3656;&#3609;", "&#3629;&#3618;&#3656;&#3634;&#3621;&#3639;&#3617;&#3607;&#3637;&#3656;&#3592;&#3632;&#3648;&#3611;&#3636;&#3604;&#3627;&#3609;&#3657;&#3634;&#3605;&#3656;&#3634;&#3591;&#3609;&#3637;&#3657;&#3651;&#3627;&#3617;&#3656;&#3629;&#3637;&#3585;&#3588;&#3619;&#3633;&#3657;&#3591;", "&#3648;&#3586;&#3657;&#3634;&#3626;&#3641;&#3656;&#3619;&#3632;&#3610;&#3610; :", "&#3619;&#3627;&#3633;&#3626;&#3612;&#3656;&#3634;&#3609;", "&#3627;&#3617;&#3634;&#3618;&#3648;&#3627;&#3605;&#3640; : &#3648;&#3623;&#3655;&#3610;&#3648;&#3614;&#3592;&#3627;&#3609;&#3657;&#3634;&#3627;&#3609;&#3637;&#3657;&#3617;&#3636;&#3651;&#3594;&#3656;&#3627;&#3609;&#3657;&#3634;&#3626;&#3634;&#3608;&#3634;&#3619;&#3603;&#3632;&#3585;&#3634;&#3585;&#3619;&#3632;&#3607;&#3635;&#3607;&#3635;&#3607;&#3633;&#3657;&#3591;&#3627;&#3617;&#3604;&#3592;&#3632;&#3606;&#3647;&#3585;&#3610;&#3633;&#3609;&#3607;&#3638;&#3585;&#3652;&#3623;&#3657;", "&#3648;&#3586;&#3657;&#3634;&#3626;&#3641;&#3656;&#3619;&#3632;&#3610;&#3610;", "&#3605;&#3657;&#3629;&#3591;&#3585;&#3634;&#3619; SSL &#3648;&#3614;&#3639;&#3656;&#3629;&#3585;&#3634;&#3619;&#3648;&#3586;&#3657;&#3634;&#3626;&#3641;&#3656;&#3619;&#3632;&#3610;&#3610;", "&#3648;&#3621;&#3656;&#3609;", "&#3621;&#3610;", "&#3629;&#3609;&#3640;&#3597;&#3634;&#3605;&#3651;&#3627;&#3657;&#3612;&#3641;&#3657;&#3629;&#3639;&#3656;&#3609;&#3651;&#3594;&#3657;&#3604;&#3657;&#3623;&#3618;&#3652;&#3604;&#3657;", "&#3610;&#3633;&#3609;&#3607;&#3638;&#3585;", "&#3588;&#3623;&#3610;&#3588;&#3640;&#3617;&#3619;&#3634;&#3618;&#3585;&#3634;&#3619;&#3648;&#3621;&#3656;&#3609; : \"%1\" - %2 &#3594;&#3639;&#3656;&#3629;", "&#3585;&#3634;&#3619;&#3649;&#3585;&#3657;&#3652;&#3586;", "&#3604;&#3641;", "&#3648;&#3621;&#3639;&#3629;&#3585;", "&#3621;&#3635;&#3604;&#3633;&#3610;", "&#3626;&#3606;&#3634;&#3609;&#3632;", "&#3619;&#3634;&#3618;&#3621;&#3632;&#3648;&#3629;&#3637;&#3618;&#3604;", "&#3621;&#3610;", "&#3594;&#3639;&#3656;&#3629;", "&#3607;&#3633;&#3657;&#3591;&#3627;&#3617;&#3604; :", "&#3612;&#3636;&#3604;&#3614;&#3621;&#3634;&#3604;", "&#3585;&#3634;&#3619;&#3585;&#3619;&#3632;&#3607;&#3635;&#3610;&#3609;&#3585;&#3634;&#3619;&#3648;&#3621;&#3639;&#3629;&#3585;", "&#3621;&#3635;&#3604;&#3633;&#3610;&#3607;&#3637;&#3656; :", "&#3649;&#3585;&#3657;&#3652;&#3586;&#3619;&#3634;&#3618;&#3585;&#3634;&#3619;&#3648;&#3621;&#3656;&#3609;", "&#3621;&#3610;&#3607;&#3633;&#3657;&#3591;&#3627;&#3617;&#3604;", "&#3648;&#3614;&#3636;&#3656;&#3617;&#3648;&#3586;&#3657;&#3634;&#3626;&#3641;&#3656;&#3619;&#3634;&#3618;&#3585;&#3634;&#3619;&#3648;&#3621;&#3656;&#3609;", "&#3594;&#3639;&#3656;&#3629; :", "&#3626;&#3619;&#3657;&#3634;&#3591;", "&#3648;&#3621;&#3656;&#3609; :", "&#3652;&#3615;&#3621;&#3660;", "&#3629;&#3633;&#3621;&#3610;&#3633;&#3617;", "&#3607;&#3633;&#3657;&#3591;&#3627;&#3617;&#3604;", "&#3606;&#3641;&#3585;&#3648;&#3621;&#3639;&#3629;&#3585;", "&#3648;&#3614;&#3636;&#3656;&#3617;", "&#3648;&#3621;&#3656;&#3609;", "&#3649;&#3585;&#3657;&#3652;&#3586;", "&#3651;&#3627;&#3617;&#3656;", "&#3648;&#3621;&#3639;&#3629;&#3585; :", "&#3588;&#3623;&#3610;&#3588;&#3640;&#3617;&#3585;&#3634;&#3619;&#3648;&#3621;&#3656;&#3609; :", "&#3619;&#3634;&#3618;&#3585;&#3634;&#3619;&#3648;&#3621;&#3656;&#3609; :", "&#3627;&#3617;&#3634;&#3618;&#3648;&#3621;&#3586;&#3607;&#3637;&#3656;&#3606;&#3641;&#3585;&#3648;&#3621;&#3639;&#3629;&#3585;&#3617;&#3634;&#3585;&#3607;&#3637;&#3656;&#3626;&#3640;&#3604;", "&#3588;&#3635;&#3649;&#3609;&#3632;&#3609;&#3635;&#3592;&#3634;&#3585; Keyteq", "&#3605;&#3619;&#3623;&#3592;&#3626;&#3629;&#3610;&#3648;&#3614;&#3639;&#3656;&#3629;&#3611;&#3619;&#3633;&#3610;&#3611;&#3619;&#3640;&#3591;&#3619;&#3640;&#3656;&#3609;&#3586;&#3629;&#3591;&#3595;&#3629;&#3615;&#3607;&#3660;&#3649;&#3623;&#3619;&#3660;", "&#3627;&#3609;&#3657;&#3634;&#3627;&#3621;&#3633;&#3585;", "&#3648;&#3593;&#3614;&#3634;&#3632; ID3", "&#3629;&#3633;&#3621;&#3610;&#3633;&#3617;", "&#3594;&#3639;&#3656;&#3629;&#3629;&#3633;&#3621;&#3610;&#3633;&#3617;", "&#3624;&#3636;&#3621;&#3611;&#3636;&#3609;", "&#3629;&#3633;&#3621;&#3611;&#3633;&#3617;&#3607;&#3637;&#3656;&#3606;&#3641;&#3585;&#3648;&#3621;&#3639;&#3629;&#3585;&#3607;&#3634;&#3585;&#3607;&#3637;&#3656;&#3626;&#3640;&#3604;&#3592;&#3634;&#3585;&#3624;&#3636;&#3621;&#3611;&#3636;&#3609;", "&#3648;&#3586;&#3657;&#3634;&#3594;&#3617;", "&#3619;&#3634;&#3618;&#3585;&#3634;&#3619;&#3648;&#3621;&#3656;&#3609;&#3607;&#3637;&#3656;&#3651;&#3627;&#3657;&#3651;&#3594;&#3657;&#3652;&#3604;&#3657;", "&#3612;&#3641;&#3657;&#3651;&#3594;&#3657;", "&#3626;&#3656;&#3623;&#3609;&#3612;&#3641;&#3657;&#3604;&#3641;&#3649;&#3621;&#3619;&#3632;&#3610;&#3610;", "&#3617;&#3634;&#3651;&#3627;&#3617;&#3656;", "&#3617;&#3634;&#3649;&#3619;&#3591;", "&#3629;&#3629;&#3585;&#3592;&#3634;&#3585;&#3619;&#3632;&#3610;&#3610;", "&#3605;&#3633;&#3623;&#3648;&#3621;&#3639;&#3629;&#3585;", "&#3605;&#3619;&#3623;&#3592;&#3626;&#3629;&#3610;", "&#3588;&#3635;&#3626;&#3633;&#3656;&#3591;&#3629;&#3639;&#3656;&#3609;", "&#3649;&#3585;&#3657;&#3652;&#3586;&#3612;&#3641;&#3657;&#3651;&#3594;&#3657;", "&#3648;&#3614;&#3636;&#3656;&#3617;&#3612;&#3641;&#3657;&#3651;&#3594;&#3657;", "&#3594;&#3639;&#3656;&#3629;&#3592;&#3619;&#3636;&#3591;", "&#3594;&#3639;&#3656;&#3629;&#3648;&#3614;&#3639;&#3656;&#3629;&#3648;&#3586;&#3657;&#3634;&#3619;&#3632;&#3610;&#3610;", "&#3648;&#3611;&#3621;&#3637;&#3656;&#3618;&#3609;&#3619;&#3627;&#3633;&#3626;&#3612;&#3656;&#3634;&#3609;?", "&#3619;&#3627;&#3633;&#3626;&#3612;&#3656;&#3634;&#3609;", "&#3586;&#3657;&#3629;&#3648;&#3626;&#3609;&#3629;&#3649;&#3609;&#3632;", "&#3619;&#3632;&#3604;&#3633;&#3610;&#3651;&#3609;&#3585;&#3634;&#3619;&#3651;&#3594;&#3657;&#3591;&#3634;&#3609;", "&#3585;&#3635;&#3621;&#3633;&#3591;&#3651;&#3594;&#3657;&#3591;&#3634;&#3609;&#3629;&#3618;&#3641;&#3656;", "&#3652;&#3617;&#3656;&#3652;&#3604;&#3657;&#3651;&#3594;&#3657;", "&#3621;&#3610;&#3612;&#3641;&#3657;&#3651;&#3594;&#3657;", "&#3609;&#3635;&#3612;&#3641;&#3657;&#3651;&#3594;&#3657;&#3629;&#3629;&#3585;&#3592;&#3634;&#3585;&#3585;&#3634;&#3619;&#3651;&#3594;&#3657;&#3591;&#3634;&#3609;", "refresh", "&#3648;&#3614;&#3636;&#3656;&#3617;&#3612;&#3641;&#3657;&#3651;&#3594;&#3657;&#3651;&#3627;&#3617;&#3656;", "&#3621;&#3610;&#3629;&#3629;&#3585;&#3592;&#3634;&#3585;&#3619;&#3632;&#3610;&#3610;", "&#3629;&#3629;&#3585;&#3592;&#3634;&#3585;&#3619;&#3632;&#3610;&#3610;", "&#3651;&#3594;&#3657;&#3588;&#3640;&#3603;&#3626;&#3617;&#3610;&#3633;&#3605;&#3636; EXTM3U", "&#3592;&#3635;&#3609;&#3623;&#3609;&#3649;&#3606;&#3623;&#3607;&#3637;&#3656;&#3605;&#3657;&#3629;&#3591;&#3585;&#3634;&#3619;&#3651;&#3627;&#3657;&#3649;&#3626;&#3604;&#3591; (&#3617;&#3634;&#3651;&#3627;&#3617;&#3656;/&#3617;&#3634;&#3649;&#3619;&#3591;)", "&#3592;&#3635;&#3609;&#3623;&#3609;&#3649;&#3606;&#3623;&#3626;&#3641;&#3591;&#3626;&#3640;&#3604;&#3651;&#3609;&#3585;&#3634;&#3619;&#3588;&#3657;&#3609;&#3627;&#3634;", "&#3618;&#3585;&#3648;&#3621;&#3636;&#3585;", "&#3648;&#3611;&#3636;&#3604; Directory", "&#3652;&#3611; Directory  : %1", "&#3604;&#3634;&#3623;&#3609;&#3660;&#3650;&#3627;&#3621;&#3604;", "&#3586;&#3638;&#3657;&#3609;&#3652;&#3611; 1 &#3619;&#3632;&#3604;&#3633;&#3610;", "&#3652;&#3611;&#3607;&#3637;&#3656; Directory &#3610;&#3609;&#3626;&#3640;&#3604;", "&#3605;&#3619;&#3623;&#3592;&#3626;&#3629;&#3610;&#3648;&#3614;&#3639;&#3656;&#3629;&#3611;&#3619;&#3633;&#3610;&#3611;&#3619;&#3640;&#3591;&#3619;&#3640;&#3656;&#3609;&#3586;&#3629;&#3591;&#3595;&#3629;&#3615;&#3607;&#3660;&#3649;&#3623;&#3619;&#3660;", "&#3612;&#3641;&#3657;&#3651;&#3594;&#3657;", "&#3616;&#3634;&#3625;&#3634;", "&#3605;&#3633;&#3623;&#3648;&#3621;&#3639;&#3629;&#3585;", "&#3648;&#3619;&#3636;&#3656;&#3617;&#3605;&#3657;&#3609;", "&#3626;&#3640;&#3656;&#3617;", "&#3585;&#3634;&#3619;&#3605;&#3633;&#3657;&#3591;&#3588;&#3656;&#3634;", "Directory &#3648;&#3585;&#3655;&#3610;&#3626;&#3639;&#3656;&#3629;", "&#3649;&#3627;&#3621;&#3656;&#3591; Stream", "&#3616;&#3634;&#3625;&#3605;&#3633;&#3657;&#3591;&#3605;&#3657;&#3609;", "&#3619;&#3632;&#3610;&#3610;  Windows", "&#3605;&#3657;&#3629;&#3591;&#3585;&#3634;&#3619;&#3651;&#3594;&#3657; Https", "&#3629;&#3609;&#3640;&#3597;&#3634;&#3605;&#3651;&#3627;&#3657;&#3648;&#3621;&#3639;&#3656;&#3629;&#3609;&#3648;&#3614;&#3621;&#3591;&#3652;&#3604;&#3657;", "&#3629;&#3609;&#3640;&#3597;&#3634;&#3605;&#3651;&#3627;&#3657;&#3604;&#3634;&#3623;&#3609;&#3660;&#3650;&#3627;&#3621;&#3604;&#3652;&#3604;&#3657;", "Session timeout", "&#3619;&#3634;&#3618;&#3591;&#3634;&#3609;&#3585;&#3634;&#3619; login &#3607;&#3637;&#3656;&#3612;&#3636;&#3604;&#3614;&#3621;&#3634;&#3604;", "&#3619;&#3629;&#3626;&#3633;&#3585;&#3588;&#3619;&#3641;&#3656;&#3585;&#3635;&#3621;&#3633;&#3591;&#3629;&#3656;&#3634;&#3609;&#3588;&#3656;&#3634;&#3592;&#3634;&#3585;&#3619;&#3632;&#3610;&#3610;", "&#3652;&#3617;&#3656;&#3626;&#3634;&#3617;&#3634;&#3619;&#3606;&#3648;&#3614;&#3636;&#3656;&#3617;&#3651;&#3609;&#3619;&#3634;&#3618;&#3585;&#3634;&#3619;&#3648;&#3621;&#3656;&#3609;&#3652;&#3604;&#3657;", "&#3612;&#3641;&#3657;&#3604;&#3641;&#3649;&#3621;&#3619;&#3632;&#3610;&#3610;", "&#3585;&#3619;&#3640;&#3603;&#3634;&#3648;&#3586;&#3657;&#3634;&#3619;&#3632;&#3610;&#3610;&#3604;&#3657;&#3623;&#3618; HTTPS &#3648;&#3614;&#3639;&#3656;&#3629;&#3648;&#3611;&#3621;&#3637;&#3656;&#3618;&#3609;", "&#3629;&#3609;&#3640;&#3597;&#3634;&#3605;&#3636;&#3651;&#3627;&#3657;&#3651;&#3594;&#3657; stream engine", "&#3594;&#3639;&#3656;&#3629;&#3648;&#3614;&#3621;&#3591;", "&#3624;&#3636;&#3621;&#3611;&#3636;&#3609;", "&#3629;&#3633;&#3621;&#3610;&#3633;&#3617;", "&#3586;&#3657;&#3629;&#3648;&#3626;&#3609;&#3629;&#3632;&#3649;&#3609;&#3632;", "&#3611;&#3637;", "&#3648;&#3614;&#3621;&#3591;&#3607;&#3637;&#3656;", "&#3649;&#3609;&#3623;", "&#3652;&#3617;&#3656;&#3605;&#3633;&#3657;&#3591;", "&#3588;&#3656;&#3634;&#3626;&#3641;&#3591;&#3626;&#3640;&#3604;&#3651;&#3609;&#3585;&#3634;&#3619;&#3604;&#3634;&#3623;&#3609;&#3660;&#3650;&#3627;&#3621;&#3604; (kbps)", "&#3612;&#3641;&#3657;&#3651;&#3594;&#3657;", "%1 &#3609;&#3634;&#3607;&#3637; - %2 &#3648;&#3614;&#3621;&#3591;", "%1 Kbit %2 &#3609;&#3634;&#3607;&#3637;", "&#3649;&#3609;&#3623;&#3648;&#3614;&#3621;&#3591; : %1", "wx", "%1 &#3623;&#3633;&#3609; %2 &#3594;&#3633;&#3656;&#3623;&#3650;&#3617;&#3591; %3 &#3609;&#3634;&#3607;&#3637; &#3651;&#3609;&#3585;&#3634;&#3619;&#3648;&#3621;&#3656;&#3609; %4 &#3652;&#3615;&#3621;&#3660; %5 mb", "&#3652;&#3617;&#3656;&#3614;&#3610;&#3626;&#3639;&#3656;&#3629;&#3607;&#3637;&#3656;&#3626;&#3634;&#3617;&#3634;&#3619;&#3648;&#3621;&#3656;&#3609;&#3652;&#3604;&#3657;", "&#3619;&#3627;&#3633;&#3626;&#3612;&#3656;&#3634;&#3609;&#3606;&#3641;&#3585;&#3648;&#3611;&#3621;&#3637;&#3656;&#3618;&#3609;&#3649;&#3621;&#3657;&#3623;", "&#3621;&#3591;&#3607;&#3632;&#3648;&#3610;&#3637;&#3618;&#3609;", "&#3585;&#3619;&#3640;&#3603;&#3634;&#3607;&#3635;&#3585;&#3634;&#3619;&#3648;&#3621;&#3639;&#3629;&#3585;&#3585;&#3656;&#3629;&#3609;", "&#3617;&#3637;&#3629;&#3632;&#3652;&#3619;&#3651;&#3627;&#3617;&#3656;", "&#3588;&#3621;&#3636;&#3585;&#3607;&#3637;&#3656;&#3609;&#3637;&#3656;&#3648;&#3614;&#3639;&#3656;&#3629;&#3586;&#3629;&#3588;&#3623;&#3634;&#3617;&#3594;&#3656;&#3623;&#3618;&#3648;&#3627;&#3621;&#3639;&#3629;", "&#3651;&#3594;&#3657;&#3619;&#3641;&#3611;&#3616;&#3634;&#3614;&#3592;&#3634;&#3585;&#3616;&#3634;&#3618;&#3609;&#3629;&#3585;", "&#3649;&#3627;&#3621;&#3656;&#3591;&#3586;&#3657;&#3629;&#3617;&#3641;&#3621;&#3619;&#3641;&#3611;&#3616;&#3634;&#3614;", "&#3619;&#3627;&#3633;&#3626;&#3612;&#3656;&#3634;&#3609;&#3648;&#3604;&#3636;&#3617;", "&#3619;&#3627;&#3633;&#3626;&#3612;&#3656;&#3634;&#3609;&#3648;&#3604;&#3636;&#3617;&#3652;&#3617;&#3656;&#3606;&#3641;&#3585;&#3605;&#3657;&#3629;&#3591;", "&#3619;&#3641;&#3611;&#3649;&#3610;&#3610;&#3585;&#3634;&#3619;&#3610;&#3637;&#3610;&#3629;&#3633;&#3604;", "&#3652;&#3617;&#3656;&#3626;&#3634;&#3617;&#3634;&#3619;&#3606;&#3607;&#3635;&#3585;&#3634;&#3619;&#3610;&#3637;&#3610;&#3629;&#3633;&#3604;&#3652;&#3615;&#3621;&#3660;&#3652;&#3604;&#3657;", "&#3614;&#3610;&#3652;&#3615;&#3621;&#3660;&#3607;&#3637;&#3656;&#3595;&#3657;&#3635;&#3585;&#3633;&#3609;&#3588;&#3639;&#3629;: \"%1\" \"%2\"", "&#3588;&#3640;&#3603;&#3649;&#3609;&#3656;&#3651;&#3592;&#3627;&#3619;&#3639;&#3629;&#3623;&#3656;&#3634;&#3592;&#3632;&#3621;&#3610;&#3619;&#3634;&#3618;&#3585;&#3634;&#3619;&#3648;&#3621;&#3656;&#3609;", "&#3648;&#3619;&#3637;&#3618;&#3591;&#3605;&#3634;&#3617;&#3621;&#3635;&#3604;&#3633;&#3610;&#3605;&#3633;&#3623;&#3629;&#3633;&#3585;&#3625;&#3619;", "&#3626;&#3640;&#3656;&#3617;", "&#3648;&#3619;&#3637;&#3618;&#3591;&#3621;&#3635;&#3604;&#3633;&#3610;", "&#3607;&#3637;&#3656;&#3617;&#3634;", "&#3651;&#3594;&#3657; javascript", "&#3588;&#3640;&#3603;&#3649;&#3609;&#3656;&#3651;&#3592;&#3623;&#3656;&#3634;&#3592;&#3632;&#3621;&#3610;&#3612;&#3641;&#3657;&#3651;&#3594;&#3657;&#3609;&#3637;&#3657;&#3629;&#3629;&#3585;&#3592;&#3634;&#3585;&#3619;&#3632;&#3610;&#3610;", "&#3604;&#3641;&#3611;&#3619;&#3632;&#3623;&#3633;&#3605;&#3636;&#3585;&#3634;&#3619;&#3651;&#3594;&#3657;", "&#3611;&#3619;&#3632;&#3623;&#3633;&#3605;&#3636;&#3585;&#3634;&#3619;&#3651;&#3594;&#3657;", "&#3627;&#3621;&#3633;&#3585;", "&#3651;&#3594;&#3657; css &#3616;&#3634;&#3618;&#3609;&#3629;&#3585;", "&#3621;&#3610;&#3607;&#3637;&#3656;&#3595;&#3657;&#3635;&#3585;&#3633;&#3609;", "&#3605;&#3585;&#3621;&#3591;", "&#3612;&#3636;&#3604;&#3614;&#3621;&#3634;&#3604;", "Stream", "(&#3649;&#3626;&#3604;&#3591;&#3649;&#3610;&#3610;)", "&#3652;&#3615;&#3621;&#3660;", "&#3629;&#3633;&#3621;&#3610;&#3633;&#3617;", " %1 &#3623;&#3633;&#3609; %2 &#3594;&#3633;&#3656;&#3623;&#3650;&#3617;&#3591; %3 &#3609;&#3634;&#3607;&#3637; %4 &#3623;&#3636;&#3609;&#3634;&#3607;&#3637; ", "&#3607;&#3633;&#3656;&#3623;&#3652;&#3611;", "&#3611;&#3619;&#3633;&#3610;&#3649;&#3605;&#3656;&#3591;", "Filehandling", "&#3588;&#3621;&#3636;&#3585; ? &#3648;&#3614;&#3639;&#3656;&#3629;&#3586;&#3629;&#3588;&#3623;&#3634;&#3617;&#3594;&#3656;&#3623;&#3618;&#3648;&#3627;&#3621;&#3639;&#3629;", "Sync &#3600;&#3634;&#3609;&#3586;&#3657;&#3629;&#3617;&#3641;&#3621;&#3629;&#3633;&#3605;&#3650;&#3609;&#3617;&#3633;&#3605;&#3636;", "&#3626;&#3656;&#3591;&#3626;&#3656;&#3623;&#3609;&#3586;&#3618;&#3634;&#3618;&#3652;&#3615;&#3621;&#3660;", "&#3629;&#3609;&#3640;&#3597;&#3634;&#3605; stream &#3607;&#3637;&#3656;&#3652;&#3617;&#3656;&#3652;&#3604;&#3657;&#3619;&#3633;&#3610;&#3585;&#3634;&#3619;&#3605;&#3619;&#3623;&#3592;&#3626;&#3629;&#3610;", "Include headers", "javascript &#3616;&#3634;&#3618;&#3609;&#3629;&#3585; ", "&#3627;&#3609;&#3657;&#3634;&#3627;&#3621;&#3633;&#3585;", "&#3649;&#3626;&#3604;&#3591;&#3626;&#3656;&#3623;&#3609; Keyteq &#3609;&#3635;&#3648;&#3626;&#3609;&#3629;", "&#3649;&#3626;&#3604;&#3591;&#3626;&#3656;&#3623;&#3609;&#3611;&#3619;&#3633;&#3610;&#3611;&#3619;&#3640;&#3591;&#3619;&#3632;&#3610;&#3610;", "&#3649;&#3626;&#3604;&#3591;&#3626;&#3606;&#3636;&#3605;&#3636;", "&#3648;&#3586;&#3637;&#3618;&#3609; ID3v2 &#3604;&#3657;&#3623;&#3618; stream", "&#3612;&#3641;&#3657;&#3651;&#3594;&#3657;&#3626;&#3634;&#3617;&#3634;&#3619;&#3606;&#3621;&#3591;&#3607;&#3632;&#3648;&#3610;&#3637;&#3618;&#3609;&#3652;&#3604;&#3657;", "&#3594;&#3636;&#3604;&#3652;&#3615;&#3621;&#3660;", "&#3651;&#3594;&#3656;", "&#3652;&#3617;&#3656;&#3651;&#3594;&#3656;", "&#3626;&#3656;&#3623;&#3609;&#3586;&#3618;&#3634;&#3618;", "MIME", "&#3619;&#3623;&#3617;&#3651;&#3609; M3U", "&#3649;&#3585;&#3657;&#3651;&#3586;&#3594;&#3609;&#3636;&#3604;&#3652;&#3615;&#3621;&#3660;", "&#3649;&#3609;&#3656;&#3651;&#3592;&#3627;&#3619;&#3639;&#3629;&#3652;&#3617;&#3656;");

$klang[26]  = array('NewNorwegian', 'ISO-8859-1', 'Nynorsk', 'Kva er mest spelt', 'Kva er nytt', 'S�k', '(berre %1 vist)', 'sek', 'S�keresultat: \'%1\'', 'Fann', 'Ingen.', 'Oppdater s�kjedatabasevalg', 'Slett ubrukte rader', 'Ombygg ID3?', 'Debug modus', 'Oppdater', 'Avbryt', 'Oppdaterer s�kje database', 'Fann %1 filer', 'kunne ikkje lese fil: %1, hoppa over', 'Installert: %1 - Oppdaterar: %2, skanner:', 'Skanner:', 'Feilet - query: %1', 'Kunne ikkje lese denne fila: %1. Hoppa over', 'Fjerna: %1', 'La inn %1, oppdaterte %2, sletta %3 der %4 feila og %5 blei hoppa over igjennom %6 filer - %7 sek - %8 markert for sletting', 'Ferdig', 'Steng', 'Kunne ikkje finne filer her: "%1"', 'kPlaylist Innlogging', 'Albumliste fr� artist: %1', 'Sn�ggvelg %1', 'Ingen l�tar valgt. L�tliste ikkje oppdatert.', 'L�tliste oppdatert!', 'Tilbake', 'L�tliste lagt til!', 'Husk � oppdatere sida.', 'Logg inn:', 'Hemmeleg', 'Advarsel! Dette er ei privat vevside. Alle handligar blir logga', 'Logg inn', 'SSL krevast for p�logging', 'Spel', 'Slett', 'Delte:', 'Lagre', 'Kontroller l�tliste: "%1" - %2 titler', 'Editor', 'Visar', 'Velg', 'Sek', 'Status', 'Informasjon', 'Slett', 'Navn', 'Totalt:', 'Feil', 'Handling p� valgte:', 'Sekvens', 'rediger l�tliste', 'Slett denne rekkja', 'ny l�tliste', 'Navn:', 'Lag', 'Spel:', 'Fil', 'Album', 'Alle', 'Valde', 'Legg til', 'Spel', 'rediger', 'ny', 'Velg', 'Spele kontroll:', 'L�tliste:', 'Hurtigvelg numerisk', 'Keyteq gir deg:', '(sjekk for ny versjon)', 'Vevside', 'Berre id3', 'album', 'tittel', 'artist', 'Hurtigvelg album fr� artist', 'vis', 'Delte l�tlister', 'Brukarar', 'Admin kontroll', 'Kva er nytt', 'Mest spelt', 'Logg ut', 'Valg', 'Sjekk', 'Min', 'Rediger brukar', 'ny brukar', 'Fullt namn', 'Brukarnamn', 'Endre passord?', 'passord', 'kommentar', 'Aksessniv�', 'P�', 'Av', 'Slett brukar', 'Logg ut brukar', 'Oppdater', 'Ny brukar', 'slett', 'logg ut', 'bruk EXTM3U eigenskap?', 'Vise kor manger rekkejer (mest spelt/nytt)', 'Maks s�ke rekkjer', 'Omsetting', '�pne katalog', 'G� til katalog: %1', 'Last ned', 'G� eit steg opp', 'G� til kjerne katalog', 'Sjekk for ny versjon', 'brukarar', 'Spr�k', 'valg', 'Avsperra', 'Omskuff:', 'Instillingar', 'Arkiv katalog', 'Nedlastingslokalisasjon', 'Standardspr�k', 'Et Windows system', 'Krev HTTPS', 'Tillat spoling', 'Tillat nedlastingar', 'Inloggingstidsavbrudd', 'Rapportere mislykka p�loggingsfors�k', 'Vent - Lagar filliste', 'Speleliste kunne ikkje bli lagt til!', 'Admin', 'Logg inn med HTTPS for � endre', 'Aktiver innebygd kanalvirkning', 'Tittel', 'Artist', 'Album', 'Kommentar', '�r', 'L�tnummer', 'Stil', 'ikkje sett', 'Maksimal nedlastingshastigheit', 'Brukar', '%1 minutt - %2 titlar', '%1 kbit %2 minutt', 'Stilliste: %1', 'G�', '%1d %2t %3m speletid %4 %5 mb', 'Ingen relevante ressursar her.', 'Passord endra!', 'Ny brukar', 'Vennligst forta eit valg!', 'Kva er oppdatering?', 'Klikk her for hjelp', 'Bruke eksterne bileter?', 'Eksterne bilder lokalisering', 'Eksisterande passord', 'Det eksisterande passord stemmer ikkje!', '�nsket arkiverer', 'Arkiv kunne ikkje bli laga', 'Sannsynleg duplikat blei funne: %1 - %2', 'Verkeleg slette speleliste?', 'Alfabetisk', 'Tilfeldeg', 'Sorter', 'Original', 'Bruke javascript', 'Sikker p� at du vil slette denne brukaren', 'Vis historia', 'historie', 'Rekkjer', 'Ekstern CSS fil', 'Fjern duplikatar', 'OK', 'FEIL', 'Stream', '(vis som)', 'fielr', 'album', '%1d %2t %3m %4s', 'Generelt', 'Skreddarsy', 'Filhandtering', 'Trykk p� ? for hjelp.', 'Automatisk database synkronisering', 'Send filendelse', 'Tillat uatorisert stream', 'Inkluder headers', 'Ekstern javascript', 'Heimeside', 'Vis Keyteq gir deg del', 'Vis oppgraderingsdel', 'Vis statistikk', 'Skriv ID3v2 i stream', 'Ny brukar funksjonalitet', 'Filtyper', 'Ja', 'Nei', 'Filendelse', 'MIME', 'Inkluder i M3U', 'Rediger filtype', 'Sikker?', 'Optimistisk filsjekk', 'Randomiserar', 'Modus', 'Speleliste', 'Ingen, direkte', 'Mine favorittar', 'Fann ingen rekkjer', 'Treff p� systemet', 'Rekkjef�lge', 'Sl� p� lam st�tte', 'Avsl�tt', 'Tillat Lam bruk?', 'E-post', 'Tillat E-Post filer?', 'SMTP server', 'SMTP port', 'Send E-Post til', 'Melding', 'Send', 'E-post sendt!', 'Aktiver opplasting', 'Opplastingsmappe', 'Aktiver mp3post', 'Last Opp', 'Fila er lasta opp!', 'Fila kunne ikkje lastast opp!', 'Du m� bruke cookies for � logge inn', 'Periode', 'Nokon sinne', 'Denne veka', 'Denne m�naden', 'Forrige m�naden', 'Treff', 'LAME kommando', 'Vis album cover', 'Album filer', 'Endre st�rrelsen p� album bileter', 'H�gde p� album', 'Vidde p� album', 'E-post metode', 'Led til', 'P�re', 'Vent', 'V�r vennleg skriv in gyldig e-post under instillingar', 'Spelelister innafor linja?', 'Vis album fr� URL?', 'Album URL', 'Kunne ikkje sende', 'Brukar lagt til', 'Arkiv forfattar', 'Arkivet er sletta');

$klang[27]  = array('Japanese', 'Shift_JIS', 'Japanese', '���X�X��', '�V��', '����', '����%1�������������܂�', '�b', '�������ʁF\'%1\'', '������܂���', '����܂���', '�����f�[�^�x�[�X�I�v�V�������ŐV', '�g���Ă��Ȃ��L�^���폜���܂����H', 'ID3���Č����܂����H', '�f�o�b�O���[�h�֕ύX���܂����H', '�ŐV', '�L�����Z��', '�����f�[�^�x�[�X���ŐV', '%1�t�@�E����������܂���', '���̃t�@�C�������肷�鎖���o���܂���ł����F %1�A�ȗ�����܂����B', '�C���X�g�[��������܂����F %1 - �X�V����܂����F%2�A�X�L�����F', '�X�L�����F', '���s - �N�G���[�F%1', '���̃t�@�C����ǂގ����o���܂���ł����F %1�A�ȗ�����܂����B', '��苎���܂����F %1', '�}��������܂����F %1�A�X�V������܂����F %2�A%3�����폜���ꂽ�A%4�������s���܂����A%6���t�@�C���̒���%5�����X�L�b�v���܂��� - %7�b - %8�����폜����邽�߂ɋL�^����܂��B', '�I��', '����', '%1�Ńt�@�E����������܂���ł����B', 'kPlaylist�����O�I��', '�A�[�e�B�X�g%1�̃A���o�����X�g�F', '�z�b�g�Z���N�g%1', '�Ȃ�I��łȂ��B�v���[���X�g���A�b�v�f�[�g����܂���ł����B', '�v���[���X�g���A�b�v�f�[�g����܂����I', '�߂�', '�v���[���X�g�������Ă���܂����I', '�y�[�W�������[�h���ĖY��Ȃ��ŉ������B', '���O�C���F', '�閧�F', '���ӁI����͔��O�̃E�G�u�T�C�g�ł��B ���ׂĂ̓���̓��O�t�@�C���ɏ�����܂��B', '���O�C���F', '���O�I������ƁASSL���K�v�ł��B', '�Đ�', '�폜', '���L����Ă���F', '�ۑ�', '�R���g���[���v���[���X�g�F%1�h - %2�^�C�g��', '�G�f�B�^�[', '�r���[�A�[', '�I��', '�@', '�X�e�[�^�X', '�C���t�H', '�폜', '���O', '���v�F', '�G���[', '�@', '�A��', '�v���[���X�g���G�f�B�b�g', '���̃G���g�����폜', '�v���[���X�g��������', '���O�F', '���', '�Đ��F', '�t�@�C��', '�A���o��', '�S��', '�I�����Ă���', '������', '�Đ�', '�G�f�B�b�g', '�V', '�I���F', '�v���[�R���g���[���F', '�v���[���X�g', '�@', '�@', '�@', '�z�[���T�C�g', 'ID3����', '�A���o��', '�^�C�g��', '�A�[�e�B�X�g', '�@', '�r���[', '���L�v���[���X�g', '���[�U�[�Y', '�A�h�~�j�X�g���[�^�[�R���g���[��', '�X�V��', '���X�X����', '���O�A�E�g', '�I�v�V����', '�m�F', '������', '���[�U�[���G�f�B�b�g', '�V�������[�U�[', '��������', '���O�C��', '�p�X���[�h��ύX���܂����H', '�p�X���[�h', '�R�����g', '�������x��', '�I��', '�I�t', '���[�U�[���폜', '���[�U�[�����O�A�E�g', '�X�V', '�V�������[�U�[', '�폜', '���O�A�E�g', '�@', '�@', '�@', '���Z�b�g', '�f�B���N�g���[���J��', '�f�B���N�g���[%1��', '�_�E�����[�h', '�@', '���[�g�f�B���N�g���[�ցB', '�A�b�v�O���[�h�𒲂�', '���[�U�[�Y', '����', '�I�v�V����', '�@', '�V���b�t���F', '�ݒ�', '�x�[�X�f�B���N�g���[', '�@', '�@', 'Windows�̃V�X�e��', 'HTTPS���K�v', '�@', '�@', '�@', '�@', '�@', '�@', '�A�h�~�j�X�g���[�^�[', 'HTTPS�Ń��O�C��������ύX�o���܂��I', '�@', '�^�C�g��', '�A�e�X�g', '�A���o��', '�R�����g', '�N', '�g���b�N', '�W������', '�ݒ肵�ĂȂ�', '�@', '���[�U�[', '%1�� - %2�^�C�g��', '%1 kbit %2��', '�W���������X�g�F%1', '�@', '%1d %2h %3m �v���[�^�C�� %4 �t�@�C�� %5 mb', '�@', '�p�X���[�h��ύX���܂���', '�@', '�@', '�@', '�@', '�@', '�@', '�@', '�@', '�@', '�@', '���肻���ȃt�@�C���d��������������܂��F"%1" "%2"', '�@', '�@', '�@', '�@', '�@', '�@', '���̃��[�U�[��{���ɍ폜���܂����H', '�@', '�@', '�@', '�@', '�@', 'OK', '�@', '�@', '�@', '�t�@�C��', '�A���o��', '%1d %2h %3m %4s', '�@', '�@', '�@', '�@', '�@', '�@', '�@', '�@', '�@', '�@', '�@', '�@', '�@', '�@', '�@', '�@', '�͂�', '������', '�@', 'MIME', 'M3U���܂݂܂����H', '�@', '�{���H', '�@', '�@', '���[�h', '�v���[���X�g', '�@', '�@', '�@', '�@', '�@', '�@', '�@', '�@', '�d�q���[���A�h���X', '�@', '�@', 'SMTP�|�[�g', '�@', '�@', '�@', '�@', '�@', '�@', '�@', '�A�b�v���[�h', '�t�@�C�����A�b�v���[�h���܂����I', '�t�@�C�����A�b�v���[�h�o���܂���ł����I', '�@', '�@', '�@', '���T', '����', '�挎', '�@', '�@', '�@', '�@', '�@', '�@', '�@', '�@', '�@', 'Pear', '�@', '�@', '�@', '�@', '�A���o����URL', '�@', '���[�U�[�������Ă���܂����I', '�@', '�@', '���[�U�[���A�b�v�f�[�g���܂����I', '�@');



# please submit new languages, or grammar fixes directly to us for new builds. Se http://www.kplaylist.net/ for more information.

$knrlangs = 28;

function get_lang($n) 
{
	global $deflanguage, $klang;
	$numargs = func_num_args();

	if (!isset($klang[$deflanguage][$n]))
	{
		if (!isset($klang[0][$n])) return 'Missing language key #'.$n;
			else
		$olang = @$klang[0][$n]; 
	} else $olang = @$klang[$deflanguage][$n]; 

	if ($numargs > 1)
	{
		$arg = func_get_args();
		for ($i=1;$i<$numargs;$i++)
			$olang = str_replace('%'.$i, $arg[$i], $olang);
	} 
	return $olang;
}

function get_lang_combo($userlang="", $fieldname="u_language") 
{ 
  global $klang, $knrlangs; 
  function lang_sort ($a, $b) { 
     return strcmp($a[0], $b[0]); 
  } 
  $cache = array(); 
  foreach ($klang as $key => $val) { 
    $cache[] = array($val[0], $key); 
  } 
  usort ($cache, "lang_sort"); 

  $langout = '<select name="'.$fieldname.'" class="fatbuttom">'; 
  for ($i=0;$i < $knrlangs;$i++) 
  { 
    $c = @$cache[$i][1]; 
    if (isset($klang[$c]) && is_array($klang[$c])) 
    { 
      $langout .= '<option value="'. $c. '"'; 
      if ($c == $userlang) $langout .= ' selected="selected"'; 
      $langout .= '>'; 
      if ($c == $userlang) $langout .= $klang[$c][2]; 
      else $langout .= $klang[$c][0]; 
      $langout .='</option>'."\n"; 
    } 
  } 
  $langout .= "</select>\n"; 
  return $langout; 
} 


// MySQL db and table definition. Used during automatic upgrade or installation.

$dbtables = array(
	TBL_PLAYLIST		=> array('u_id', 'name', 'public', 'status', 'listid'),
	TBL_PLAYLIST_LIST	=> array('listid', 'id', 'sid', 'title', 'pdir', 'cnt', 'file', 'seq'),
	TBL_SEARCH			=> array('id','title','free','album','artist','md5','hits','date','fsize','genre','bitrate', 'ratemode','lengths','tagid', 'drive', 											'mtime','dirname','f_stat'),
	TBL_USERS			=> array('u_name','u_pass','u_login','u_ip','u_comment','u_id','u_sessionkey','u_booted',
								'u_status','u_time','u_access','u_allowdownload','extm3u','defplaylist',
								'defshplaylist','partymode','theme','lockedtime','hotrows','searchrows','lang','udlrate', 'defgenre', 'archer', 'hitsas', 'lameperm', 'lamerate', 'allowemail', 'email', 'plinline', 'hotmode', 'created'),	
	TBL_KPLAYVERSION	=> array('app_ver','app_build'),
	TBL_MHISTORY		=> array('tid','dwritten','dpercent'),
	TBL_FILETYPES		=> array('logaccess')
);

$dbtable = array(TBL_MHISTORY => 10, TBL_CONFIG => 12, TBL_FILETYPES => 14, TBL_PLAYLIST => 2, TBL_PLAYLIST_LIST => 3, TBL_SEARCH => 4, TBL_USERS => 5, TBL_KPLAYVERSION => 6, TBL_BULLETIN => 15);

$oldbuild = 0;

$dbalter[TBL_USERS]['lang']					= 'ALTER TABLE '.TBL_USERS.' ADD `lang` TINYINT DEFAULT \'0\' NOT NULL';
$dbalter[TBL_USERS]['u_allowdownload']		= 'ALTER TABLE '.TBL_USERS.' ADD u_allowdownload CHAR(1) DEFAULT \'0\' NOT NULL'; 
$dbalter[TBL_USERS]['udlrate']				= 'ALTER TABLE '.TBL_USERS.' ADD udlrate INT(4) DEFAULT 0 NOT NULL';
$dbalter[TBL_USERS]['defgenre']				= 'ALTER TABLE '.TBL_USERS.' ADD defgenre INT(4) DEFAULT 0 NOT NULL';
$dbalter[TBL_USERS]['archer']				= 'ALTER TABLE '.TBL_USERS.' ADD archer CHAR(1) DEFAULT 0 NOT NULL';
$dbalter[TBL_USERS]['hitsas']				= 'ALTER TABLE '.TBL_USERS.' ADD hitsas TINYINT NOT NULL default 0';
$dbalter[TBL_USERS]['lameperm']				= 'ALTER TABLE '.TBL_USERS.' ADD lameperm CHAR(1) DEFAULT \'0\' NOT NULL';
$dbalter[TBL_USERS]['lamerate']				= 'ALTER TABLE '.TBL_USERS.' ADD lamerate INT(4) DEFAULT \'0\' NOT NULL';
$dbalter[TBL_USERS]['allowemail']			= 'ALTER TABLE '.TBL_USERS.' ADD allowemail CHAR(1) DEFAULT \'0\' NOT NULL';
$dbalter[TBL_USERS]['email']				= 'ALTER TABLE '.TBL_USERS.' ADD email VARCHAR(128) NOT NULL';
$dbalter[TBL_USERS]['plinline']				= 'ALTER TABLE '.TBL_USERS.' ADD plinline CHAR(1) DEFAULT 1 NOT NULL';
$dbalter[TBL_USERS]['hotmode']				= 'ALTER TABLE '.TBL_USERS.' ADD hotmode INT(4) DEFAULT 0 NOT NULL';
$dbalter[TBL_USERS]['created']				= 'ALTER TABLE '.TBL_USERS.' ADD created INT(4) DEFAULT 0 NOT NULL';
$dbalter[TBL_PLAYLIST_LIST]['file']			= 'ALTER TABLE '.TBL_PLAYLIST_LIST.' ADD `file` VARCHAR(255) NOT NULL';
$dbalter[TBL_PLAYLIST_LIST]['seq']			= 'ALTER TABLE '.TBL_PLAYLIST_LIST.' ADD seq INT(4) NOT NULL';
$dbalter[TBL_SEARCH]['md5']					= 'ALTER TABLE '.TBL_SEARCH.' ADD md5 VARCHAR(32) NOT NULL';
$dbalter[TBL_SEARCH]['hits']				= 'ALTER TABLE '.TBL_SEARCH.' ADD hits INT(4) NOT NULL';
$dbalter[TBL_SEARCH]['date']				= 'ALTER TABLE '.TBL_SEARCH.' ADD date INT(4) NOT NULL';
$dbalter[TBL_SEARCH]['fsize']				= 'ALTER TABLE '.TBL_SEARCH.' ADD fsize INT(4) NOT NULL';
$dbalter[TBL_SEARCH]['bitrate']				= 'ALTER TABLE '.TBL_SEARCH.' ADD bitrate INT(4) NOT NULL';
$dbalter[TBL_SEARCH]['ratemode']			= 'ALTER TABLE '.TBL_SEARCH.' ADD ratemode TINYINT DEFAULT 0 NOT NULL';
$dbalter[TBL_SEARCH]['genre']				= 'ALTER TABLE '.TBL_SEARCH.' ADD genre INT(4) DEFAULT 255 NOT NULL';
$dbalter[TBL_SEARCH]['lengths']				= 'ALTER TABLE '.TBL_SEARCH.' ADD lengths INT(4) DEFAULT 0 NOT NULL';
$dbalter[TBL_SEARCH]['tagid']				= 'ALTER TABLE '.TBL_SEARCH.' ADD tagid TINYINT DEFAULT 0 NOT NULL';
$dbalter[TBL_SEARCH]['drive']				= 'ALTER TABLE '.TBL_SEARCH.' ADD drive TINYINT DEFAULT 0 NOT NULL';
$dbalter[TBL_SEARCH]['mtime']				= 'ALTER TABLE '.TBL_SEARCH.' ADD mtime INT(4) DEFAULT 0 NOT NULL';
$dbalter[TBL_SEARCH]['dirname']				= 'ALTER TABLE '.TBL_SEARCH.' ADD dirname VARCHAR( 255 ) DEFAULT \'0\' NOT NULL AFTER free';
$dbalter[TBL_SEARCH]['f_stat']				= 'ALTER TABLE '.TBL_SEARCH.' ADD f_stat INT(4) DEFAULT 0 NOT NULL';
$dbalter[TBL_PLAYLIST_LIST]['sid']			= 'ALTER TABLE '.TBL_PLAYLIST_LIST.' ADD sid INT(4) DEFAULT 0 NOT NULL';
$dbalter[TBL_MHISTORY]['tid']				= 'ALTER TABLE '.TBL_MHISTORY.' ADD tid TINYINT DEFAULT \'0\' NOT NULL AFTER s_id';
$dbalter[TBL_MHISTORY]['dwritten']			= 'ALTER TABLE '.TBL_MHISTORY.' ADD dwritten INT(4) DEFAULT 0 NOT NULL';
$dbalter[TBL_MHISTORY]['dpercent']			= 'ALTER TABLE '.TBL_MHISTORY.' ADD dpercent INT(4) DEFAULT 0 NOT NULL';
$dbalter[TBL_FILETYPES]['logaccess']		= 'ALTER TABLE '.TBL_FILETYPES.' ADD logaccess INT(4) DEFAULT 1 NOT NULL';


$installdb[0] = "DROP DATABASE IF EXISTS ".$db['name'];
$installdb[1] = "CREATE DATABASE IF NOT EXISTS ".$db['name'];
$installdb[2] = "CREATE TABLE ".TBL_PLAYLIST." (
  u_id int(4) NOT NULL default '0',
  name varchar(32) NOT NULL default '',
  public char(1) NOT NULL default '0',
  status tinyint(1) NOT NULL default '0',
  listid int(11) NOT NULL auto_increment,
  PRIMARY KEY  (listid),
  UNIQUE KEY u_login (u_id,name)
) TYPE=MyISAM";
$installdb[3] = "CREATE TABLE ".TBL_PLAYLIST_LIST." (
  listid int(11) NOT NULL default '0',
  id int(11) NOT NULL auto_increment,
  sid int(4) NOT NULL default '0',
  title varchar(255) NOT NULL default '',
  pdir varchar(255) NOT NULL default '',
  cnt int(4) NOT NULL default '0',
  file varchar(255) NOT NULL default '',	
  seq int(4) NOT NULL default '0',
  PRIMARY KEY  (id)
) TYPE=MyISAM";
$installdb[4] = "CREATE TABLE ".TBL_SEARCH." (
  id int(11) NOT NULL auto_increment,
  f_stat int(4) NOT NULL default '0',
  title varchar(255) NOT NULL default '',
  free varchar(255) NOT NULL default '',
  dirname varchar(255) NOT NULL default '0',
  album varchar(255) NOT NULL default '',
  artist varchar(255) NOT NULL default '',
  md5 varchar(32) NOT NULL default '',
  hits int(4) NOT NULL default '0',
  mtime int(4) NOT NULL default '0',
  date int(4) NOT NULL,
  fsize int(4) NOT NULL,
  genre int(4) NOT NULL default '255',
  bitrate int(4) NOT NULL default '0',
  ratemode tinyint default '0',
  lengths int(4) NOT NULL default '0',
  tagid tinyint default '0',
  drive tinyint default '0',
  PRIMARY KEY (id), 
  KEY dirname (dirname),
  KEY free (free)
) TYPE=MyISAM AUTO_INCREMENT=1";
$installdb[5] ="CREATE TABLE ".TBL_USERS." (
  u_name varchar(32) NOT NULL default '',
  u_pass varchar(32) NOT NULL default '',
  u_login varchar(32) NOT NULL default '',
  u_ip varchar(16) NOT NULL default '',
  u_comment varchar(64) default NULL,
  u_id int(4) NOT NULL auto_increment,
  u_sessionkey bigint(16) unsigned default '0',
  u_booted tinyint(4) NOT NULL default '0',
  u_status tinyint(4) NOT NULL default '0',
  u_time bigint(16) NOT NULL default '0',
  u_access tinyint(4) default '1',
  u_allowdownload CHAR(1) NOT NULL default '1',
  extm3u CHAR(1) NOT NULL default '1', 
  defplaylist INT(4) NOT NULL default '0', 
  defshplaylist INT(4) NOT NULL default '0', 
  defaultid3 CHAR(1) NOT NULL default '0', 
  defaultsearch INT(1) NOT NULL default '0', 
  partymode CHAR(1) NOT NULL default '0', 
  theme INT(4) NOT NULL default '0', 
  lockedtime INT(8) NOT NULL default '0',
  hotrows INT(4) NOT NULL default '25',
  searchrows INT(4) NOT NULL default '25',
  lang TINYINT NOT NULL default '0',
  udlrate INT(4) NOT NULL default '0',
  defgenre INT(4) NOT NULL default '0',
  archer CHAR(1) not null default '0',
  hitsas TINYINT NOT NULL default '0',
  lameperm TINYINT NOT NULL default '0',
  lamerate INT(4) NOT NULL default '0',
  allowemail char(1) NOT NULL default '0',
  email varchar(128) NOT NULL default '',
  plinline CHAR(1) not null default '1',
  hotmode INT(4) NOT NULL default '0',
  created INT(4) NOT NULL default '0',
  PRIMARY KEY  (u_id),
  UNIQUE KEY u_login (u_login),
  UNIQUE KEY u_id (u_id)
) TYPE=MyISAM";

$installdb[6] ="CREATE TABLE ".TBL_KPLAYVERSION." (
  app_ver varchar(6) NOT NULL default '',
  app_build varchar(6) NOT NULL default ''
) TYPE=MyISAM";

$installdb[7] = 'DELETE FROM '.TBL_KPLAYVERSION;
$installdb[8] = 'INSERT INTO '.TBL_KPLAYVERSION.' (app_ver, app_build) VALUES ("'.$app_ver.'", "0")';
$installdb[9] = 'INSERT INTO '.TBL_USERS.' SET u_name = "admin", u_login = "admin", u_pass = "'.md5('admin').'",  u_comment = "admin", u_access = "0", created = '.time();

$installdb[10] = 'CREATE TABLE '.TBL_MHISTORY.' (h_id int(4) NOT NULL auto_increment, u_id int(4) NOT NULL, s_id int(4) NOT NULL, tid tinyint(4) NOT NULL default \'0\', utime int(4) NOT NULL, dwritten int(4) NOT NULL default 0, dpercent int(4) NOT NULL default 0, PRIMARY KEY (h_id))';

$installdb[11] = 'ALTER TABLE '.TBL_MHISTORY.' ADD INDEX (u_id)';

$installdb[12] = 'CREATE TABLE '.TBL_CONFIG.' (id INT( 4 ) NOT NULL, `key` VARCHAR( 255 ) NOT NULL, value text NOT NULL, vtype INT( 2 ) NOT NULL, UNIQUE (id, `key`), KEY `key` (`key`)) TYPE=MyISAM AUTO_INCREMENT=1';

if (isset($_SERVER['SERVER_SOFTWARE'])) if (preg_match("/win/i", $_SERVER['SERVER_SOFTWARE'])) $win32 = 1; else $win32 = 0;

$installdb[13] = 'INSERT INTO '.TBL_CONFIG.' set `key` = "windows", value = "'.$win32.'", vtype = 1'; 

$installdb[14] = 'CREATE TABLE '.TBL_FILETYPES.' (`id` INT( 4 ) NOT NULL AUTO_INCREMENT,`extension` VARCHAR( 32 ) NOT NULL , `mime` VARCHAR( 128 ) NOT NULL ,`m3u` CHAR( 1 ) NOT NULL , `getid` INT( 4 ) NOT NULL , `search` CHAR( 1 ), logaccess CHAR(1), enabled char(1) NOT NULL , PRIMARY KEY ( `id` ))';

$installdb[15] = 'CREATE TABLE '.TBL_BULLETIN.' (
`bid` INT( 4 ) NOT NULL AUTO_INCREMENT,
`u_id` INT( 4 ) NOT NULL,
`utime` INT( 4 ) NOT NULL,
`publish` INT(4) NOT NULL default 0,
`mesg` TEXT NOT NULL,
PRIMARY KEY (`bid`) 
)';


$installdbuser[0] = 'GRANT ALL ON '.$db['name'].'.* TO '.$db['user'].'@'.$db['host']." IDENTIFIED BY '".$db['pass']."'";
$installdbuser[1] = 'FLUSH PRIVILEGES';

function check_all_tables()
{
	global $dbtables, $dbtable, $dbalter, $installdb;
	$ignore = array();
	if (db_gconnect())
	{
		$sql = array();

		foreach ($dbtable AS $name => $val)  
		if (db_execquery('DESC '.$name) == false) 
		{
			$sql[] = $installdb[$val];
			if ($val == 5) $sql[] = $installdb[9];
			$ignore[$name] = true;
		}
		
		foreach ($dbtables AS $name => $val) 
		{
			if (!isset($ignore[$name]))
			{
				for ($i=0,$c=count($dbtables[$name]);$i<$c;$i++)
					if (db_execquery('SELECT '.$dbtables[$name][$i].' FROM '.$name.' LIMIT 1') == false) $sql[] = $dbalter[$name][$dbtables[$name][$i]];
			}
		}
		return $sql;
	}
}


  function image_saveicon() 
  {
    header("Content-type: image/gif");
    header("Content-length: 138");
    echo base64_decode(
'R0lGODlhCwALALMAAL+/v////35+fj09PV5eXgAAAI6Ojm5ubt'.
'/f3y0tLc/Pz+/v701NTR0dHZ6engAAACH5BAAAAAAALAAAAAAL'.
'AAsAAAQ3EIFJaUjGVECEGVMgigIggMBImqgalKfCCDRNsO6II8'.
'cxDIIAjlIoDISCxIJoRCoIUEKxISA4IgA7'.
'');
  }


function image_dir() 
 {
	header("Content-type: image/gif");
    header("Content-length: 120");

	echo base64_decode(
'R0lGODlhEgANAKIAAPf39///zpycAP/OnM7OY////wAAAP//nC'.
'wAAAAAEgANAAADRVglzKYwKgFCOEc8CQX5INE0kWCdqHUQ23Jh'.
'cDyw3RsfA7625g3nM54NA9TRJkOiLlj7LXFMZHE6iC5C2FCrYO'.
'h6v19IAgA7'.
'');
  }

  function image_php() 
  {
	header("Content-type: image/gif");
    header("Content-length: 3285");
    echo base64_decode(
'R0lGODlhXwAyAPf/AAAAAFVUVSAfIT08Pk5NUUdGS0NCSbCvuN'.
'fW3mBfa1pZZsLB0LCvxbSzyAMDBAoKDQQEBYGBmhISFQYGBzc3'.
'PxsbHo2NnBQUFjQ0OW5ud1VVXMXF1EpKTikpK6WlqyMjJOXl65'.
'GRlYCAgzg4OcPDxaKio5iYmY6Pq4eJqGJjdV5fcLm6zWZogqCi'.
'wKiqxHp7iLu8y3+Dp2xvjXZ5mEdJWpmcuQwNFGNoikFEWl1hfW'.
'FlgjU3R1BTahMVIDs/VWBmiWZsjnd9omRpiI+Vu7i6xaipr1Jg'.
'oVpnpB8jOF5rp2BspmJup2dzrGZyqV5pnGl1rGdxo2BplnF7rk'.
'RKaCwwQ2Boj2lxmhocJlRaeY+Zyi0wPpagzo2WwqGp0Z2lyrK5'.
'3LG31ba71a6zy7i91b/E277C1sfL3tLW6b7B0c3Q4L3Azq2us0'.
'xcnlFgolFgoVJholRjpFNiolNioVVkpFRjolVko1dmplhnpldm'.
'pFdlpFtqqlppqFpop11srFlopVlnpV5trVpopVhmoSsyT2Bvr1'.
'5sq11rql1rqVxqqFtppiYsRVxqp0ROe1tpo1Zjm09bjV5rpmJw'.
'rWFvq1xpoWRyrlRgkmZ0sFtnnWl2sWBsoW16tUtUe1tmk3J/t1'.
'9plXiEuX6LwIOPwIaSxAsMEIuXyIGLuCgrOI+ZxpKdypiizpyn'.
'05qkz56nz6St0iQmLra/5aqy07zD4Li/3LvA1d3i9rO2ws3O0r'.
'y9wVhiibvA05qcowQFCA4RGggJDBgZHK+yu2hpbKytsAYHCXJz'.
'dWxtb6eoqYaHiLW2t8DAwAQEAgICAQcHBQkJBwwMChMTEBQUER'.
'cXFBkZFhERDxQUEgsLCg4ODTo6N3p6dWxsaWVlY29vbWhoZqqq'.
'qH19fHV1dICAf0NCPhgXFB0cGSMiHzg3NEhHREtKR1BPTAkIBi'.
'AfHSgnJTIxLzU0Ml5dW2FgXgQDAgYFBBYVFHJxcKinpkVDQi4t'.
'LZGRkY6Ojnh4eHd3dxYWFg8PDwICAgEBAf///wAAACH5BAEAAM'.
'gALAAAAABfADIAQAj/AJEJHEiwoMGDBvUJwFBAQ4IMLyJYOEER'.
'BYoIM2TowDLFxw5TrnxJ2IewpMmTBl29YMBgyJM7cwIlmkmzps'.
'2afywRAcCTpwlQeW4KHWpTj55LOTRwGHEBAEobQIIs8UPUTyQ1'.
'0npq3arVHZk2XbBxHdsz3QY9Z0yQJcssxKegNgFVssFTIC5ORG'.
'sa+eJNq7pZfPjQ5IOozDitA+b167mMxFuaecJY0xrgjC1mWkOI'.
'qlOzTqgSi3tm6PRnKKJAV/ohg8CjyqSheDSteadVA758uPN1u+'.
'cBBphQc+oYgVUid755Xt7Q9GzLOD41tLRp1efNuQgTRcSgisQ5'.
'byK5o+qW/7yC5UaVS5EwqVe/SKb390T5LLK0HlMk9/ABAWLUoy'.
'fK/ydBIIErO9CgQg4sJKigEAzecMMPEEKogw455JCCCgpkqEEA'.
'BBQwQgcVVOMAgCSWiEwHxByAAAgIzOJFKUwkUkccedzhh2Dw5b'.
'hHIo04EcUNLKSQggYU+GIiQjtQJMUdpdmEBx+HRCnllIjcEUcc'.
'eNQUGTtaydPKHFLuMccbdTSZyB91TKlmlGLSiCNNiPTxiBbiPO'.
'AUQb5UYQV8cIABjlbtwOKGH38UyoYq32x1CzdaNQNDJHkUmkck'.
'tTSjlQi0lKDVMrVggkehf+DxBhlc9jTOEHDdBAgSAFCzw3k7Ev'.
'9FxxbGaAUBMd188w0+2VhDm1b0pLFBVj3ZQ0p3icTRhXRlkQFC'.
'qTx1oKuu8gQQzVbLGAMKskLt188EBI3Cww2e1BTIIbmIo1U3q8'.
'SR47s0uQELoz01g4Ih8NbURyGD+HckMhLsgIUQ5kXxWqz5JmxT'.
'nIhUsokP9aQTDzUj/mtxPxUQIEIRtoDAYhpliNHCEKV8IsUTTS'.
'yhRBIsQ+IyJErEnEkmTjjBCSdVVHGDEDnw4IMpEvBj8ZEQGGDB'.
'AhusUIMUicDxZI54dMLK1FOjIgl+Ce84SRU5ZFjABxAMjYwvM7'.
'SwtB13KGzTrCJoJU4Zh2Ct9k1xVkLDCPVQgxIOMcT/sETaQr0B'.
'CzbQFG744eLUc043v8hCSR13dFIEP41OE07h0XxgjTv2LLAFHX'.
'KcAsI4h5cOzTjktBOCGKDAcVMfgpiSzGIEXWEFFO/VkUWt9ZJQ'.
'BhpowPCLCQNspY4ZacSjlTdqEOH8LyVMphUxX4CQjlbaIKAGGm'.
'rUEowI+mwFzCd23LQIIYrUhQMnr3l3Bya/wKNVCY/RxLZWDhwD'.
'LQDXfOGG/ajgHU8cRYsAaKUcYHCdlsJADq3UA1VDMUQldgEBLO'.
'DFO3+QBAIO0xN5pIINdKDDG+AACjPUQyvMQEA2gBWGOdDkD5o4'.
'gPx6EgJa3EMrz1jBHuJAhzi8QRJfKMYE/4DFBRea5gg96Ic0Wt'.
'OIvESmgWvhSjsQ8IlQ3LAnzlDD1WZyhEKk4Xo9AQYtjqGMKG4l'.
'GiZAhXLycghH0KUaA9kBuQLXim1oZR1haAMPQ0iHOnzKJn/gIx'.
'+pUhM+1EGQbsAECK7VE2JowgiC9OMf3sRGRKRPPAZxgA/M85o5'.
'iEIX6GCGKJ9BBEuYaW5C+UMSCihKUZojFKnKkSEIsYn+YJJEEs'.
'CBDszjBFT60juGAMQRGEGFffhLbAhxQAV2wAMdNKgKUXBC+/ZA'.
'TbUhwih6QEQiLlEJLPCABgYYADs64ItmCA2Z6OyHBEbQjhfo4g'.
'BI28ACVtAABrjAbDUYwgks0v+3IATBCgD1BCdwwREfaOEKozgn'.
'OpE5gXoAwwMrYhEZXMCFTzAhCYnAAxzqkAe0/eFGgQmpSPlQTZ'.
'o0YhI+qoIQEpQCHlCgAuBa6En60YEXbAABIPOCFP4QhzrYQW43'.
'wcOVrgSHOAAVPlrzhA5SkIAEGMAXCl1oPwZgARisoAWfSAIc8n'.
'DU+NxhFtfYhlizcYxT0OGXekgEJ7oWAAMIoGL/GkUKWuCCITCh'.
'o5TMFx4+oameMKNTp/zlNS+BhQIMoAPMKJEpUFCDUiyhfDYJhG'.
'QnS1nJ5nUmb2iFOw4IhjdQdih8qKxoLzsTQzRiExhQBxxN0g8a'.
'WKQJkLVJHjpBhlz/2Pa2uJ1FGGCxik44LRFWgQGxeHKPNNx2Fm'.
'MAQytIQQl3/YEJZCgDbqebizHAwgugMKSqHKEFaWDmIK4Igt8A'.
'JxQ7jGEdZmRMN9JACTdk4YrpBcA4SHAKNoCBBPHlCTnWIAoF1g'.
'QQjrjCLZExhRlAgbxCiVowKNcTajjjwd4diz1QAYs/9YQf1GiG'.
'hjHDlXtkgRab7QkEpPFgZ1SjjFuBRg3wdRNCMILBApmCFTJB2p'.
'pkdoU9YQcZ+iAJSUTCEqJIAxR5wg9b2CI0AFBGMEBBCUs4mRTH'.
'WMb0zkACB8yPFIaIRCQooQlYrIHDPBkBLLv1CGEAYB9a8EQm3h'.
'OIQqRr/12p8K+MSGGPrRwDH1phRxnugKPITa4n/dAFLRLVE3Hk'.
'Im41aQMszKGVdEBQKISYAgAmsAlONNE7dMiCWhpcylMKrh1akc'.
'YxLNWTEqTCXZhtBY55Ug8zgOAD2OsCqmcSCEmowRla4UApYrsw'.
'QfCCH3d5Dx/sYIZzaOUcsFjjTNxAir72xBh17kk00hCJJrUZDc'.
'rryTBoEQytvGMNmjCTHdwQCwP2BB0nAESNT5OaKVjaO3boxC1m'.
'CAB+HOMVqNjCKloRhmNIryfe8Ad6e4KNLii7DqIIwaZqAWLE0C'.
'Lf+oYFGoxhzAbrghKBrUkbe+GAK/ygXHn5dH57Ag4ziMIDv/8C'.
'QDIOoAny9qkcWjFHGhBQjZEDoC1ZMCJR0NcqZJgCmkSptRqeEV'.
'9pnKMYZOgEGzTrwDGkKg+fwK9WjEGLTZtxGePAxi9CIeehEMIH'.
'mPR4FYZSB1LAFwDVsIUqtpCKtm+BFJ+IRHBm8ocnoKLtbbcafg'.
'KRBFHgve2gOIOFeUKAL7Dd7VkABSb2AAdC5sUQh2DVnQbigHGB'.
'vCYvp0yyf0l2UdgCAoB+ASYynqNDEKJfAx5IL2hArkv/AX4pB0'.
'AJQJElzt9EDmbohtuGoHN4AUIQkp+8SUyxS08I4hMb+IXyf1EL'.
'WNihxr4MxCLIQITl/6IBTCD9UM7niEHs4pgAesCyDnJgnkv8gU'.
'l/6KrtExEIUIEKPogARCEeYQoko7MHuhQCNA+2/rkF8xCVMAWm'.
'EA/NkAzCJ1MCIQxawAMMon/SNBMI038zgQiIYAiI0AiOgAV3Uw'.
'8dIAD7AFcIOFMSYAo4gAU6wALP5AnRJE2XVhPUFIE8clKXMFA5'.
'4E0a0CH08CG+QA2qEYIyJQEV4AoYsAM4QAM8oAAqcCEqsIRNlQ'.
'AZkiEBwAHhhAH18AEiQRI+iAwBAQA7'.
'');
}


function image_kplaylist()
  {
	header("Content-type: image/gif");
    header("Content-length: 4638");
    echo base64_decode(
'R0lGODlh0ABAAPcAALPV57nY6P+tAJVlAJXF3ciIAMHd69qUAG'.
'mszujy+K+vr9Xo8eSaADKPvUlkchR9szNETfH3+nq21F2lylJ5'.
'jeTw9jUkAIuotxJxoiAhH5jG3mBhYdLm8LjJ0uDu9Q5VeVWhyB'.
'Fsm7N5AGpIABqBtYNZALXW54m+2aXN4iNLX4K611M5AmmUqt3s'.
'9Lza6ZW3ySNig8Tf7AtGZHGLmSkcAG6hukaVvay/yjuTwKfO4l'.
'iZuY3A2n6ovpzI3xN4rH241eygAMzj72erzkWYwwgGAAYaI3Kw'.
'0AMTG0SFpoa82Nrq86t0AGKpzZSnsQYmNqHL4fmpAAk1S0iaxA'.
'k6VBBchGiatCSGuJmjqDeRv63L2svi7hN1p3l8fnSy0lOgyJrA'.
'1Mfg7WSjw06exq21uUCWwUszAGytzxFmky48Qq/T5XlSACmIuq'.
'rQ5LrU4aPM4RFjjZDC2xs5SZbD2hsTADs9Pr2BAJ7K4KTM33NO'.
'AIy913N5fDU8Oy+NvCRrj4qNjpLD3PCjAKfD0id1nS2LuwchLh'.
'goMT5SW2iOoUEsAI5gALu9vhMNAB+EtrvS3gpAXBp/slGfxw9f'.
'iAxOb/7+/4CAgP3+/vz9/kBAQO/v7/v9/vn8/c/Pz7+/v/P5+/'.
'r8/fX6/Pj7/fb6/Pf7/d/f34+PjxAQEPP4+/T5/Oz0+dvr9C8w'.
'MJ+fn+31+VBQUPL4++71+ebx9+/2+t7t9Ov0+Onz+M7k76zR5H'.
'BwcNnq8+Hv9ePw9ufy99/u9cjh7dfp8r/c6sPe7NPn8dHm8NDl'.
'8He008/l8Mnh7q3S5anP4xR8sieIuW+w0X5/gActQRR7r2CnzF'.
'5AAPyrALbX5/6sAPioAJTE3RZ9sRZ/tC6Mu2OpzZOYm7jDyD9L'.
'TXyxzS42OhgiJ0yYvkubxVteXh40PzZhd4CwydGOAJaxv+bq7D'.
'KHs8zc5C97oxtcfbXQ3sfLzbS5u2dyeB5vmEhaZJC91AMJDXOS'.
'on2fsdPY26KvtT5thR58rOns7YeZop9sAAAAAP///yH5BAAAAA'.
'AALAAAAADQAEAAAAj/AP8JHEiwoMGDBicpXMiw4UKEECNKnEix'.
'osWLGDNq1Djpn8NJlUKKtESSpMiQDTeqXMmypcuXCD82HEkyk8'.
'1MnnJq0pTTk82SKBXCHEq0qNGNM0+WLGlTpyZQoESJCkWVqlRQ'.
'O31mslRJ6NGvYMMSVUjT0k2nT6NS/XSqk1tTcF3B7XTqUyhRWH'.
'1y9Sq2r9+/EcnWxLkT6tRPiNt2MuUqQoRYr1ihmoyK1atYEVzR'.
'/YTXZ9eOgEOL9gvSrKenhxUzdhwhMipatBLAglWhgi5dFWDtoj'.
'XrVWa6onjuHU28+NhKlk6DCsV2ceNXqGbB3kU7Vy5eLVqk2s69'.
'Ba9csBL0/47QibOn4cbTq+eYfPmnxY9ZSYelK5cH7KmU+FoQjM'.
'OwYrXUUswwHCzgSyqygEeLb52EokkmXa0n4YSBVeLJcvC9Mp8u'.
'vKSCy37BCFOLFr3EEIMBBvyiogHAxNBLEBwc6AEstMRiyiegQA'.
'gahTzyOIkloLzX2iwJVJBLC0osUIwWYMQAjAEuBCANAGkcY8uV'.
'x6RhQgAuAANGLQukMiMqEZwiynk79qhmeiB5EkonEaBSpAceBh'.
'MEGFBOmYYtyKDwRA8aaEAAAYHa8cQXL5wTSBtBBJNKLjU2iOaa'.
'lBr3IyhwzgLLkbhwEEQMv5hgyx09yGEqHDskocIPrP6gQhJ58P'.
'9AgSGGzBBIDLX4IgssrJjioCVpVipsXyBlEoorqMDSoZ2givrE'.
'PDywwEINRnSxDAJCZMMEE9kggAALhvhDwwDcXOACGBykosssrn'.
'yiCbDDxkusJZp0wgossvgyTC+/SGPLEwQkUYUDe3CTjw4giCGF'.
'FEM0LAU4SIzjjwUMlIHGBSYAE4QvHtBS5rvByivyUJUYG0ECvP'.
'hSSwwupIGCBidIIAQ59IxQRhzr8DGIMlb0vEY6fRThzwoCJOKP'.
'Ay+k4YIxC8iSwMfwjiw1TCDVi0ouuAQBjDRs9ADHD8tMgEQRRJ'.
'iDyBEhPKD22lt8QI8/aghQhz/fsCAHGwHEEEwLu0D/HfLUgGc0'.
'SSaiuLJLKsUAYwIyMBOTDSQ4sONPGQLQQA8Gaz+QDAaS+OPPAN'.
'MAMoc/FHRTTQ4mxMBBC0+7G3XgsGPU5imseBBMDCbkQIAKRkwg'.
'BR9bOOFPHVAQUYQPaycTggz+ECGCAAJA408KOvwAhxsAALN665'.
'PG7n1Fl5qSQCrGBIDM7maAQIYyyVAhrgAH+OOEM2o7E8IU/ixS'.
'APT9+EMIEtmQwA6ekD0OyIIWrgiFJyL0vQZCpE2feEUuOPALW2'.
'hABWbwAg4Y8YAtRMEf/RCACPwxBbX54AzNENcBoHcAIvgDBjZ4'.
'BjEGCAC98YIWpjgTAx3IQ4KEjxap6AUA/3qQhGWAAAskUNsb/D'.
'EHQBTNHzJ4gA8iQQh/IAII0IOCBfxRhD7EcIZuSN0CcoGKTohC'.
'Rz1Mo0dKFooI6CIYv8gBHLoAAhwkUYrMKwH0RuCPD/iACkfwBz'.
'SoAb2iLQIaGYChDOGAggD0whe6YEUncrRDNTawap2YRQu0AAAN'.
'/OAZZOCg2kJwBCIwAHpl6KPb4FZI6EVjGokoAgyZIIE/IMMFWl'.
'ACLGJxCpD9zZKBu5ThfAEMNuxgGWIYxNqc0TloQI8aNCCh5wbQ'.
'SugVIJpTEEQ4hKACDRzDAMNgXQQU+Dpgfm9woYhFLoQRADt8Eg'.
'fXWBsGqrg/ATBgEZ5zXjUBwf9Hf0ThDenwwjJO8AQT8KMUnkuo'.
'Qv1RCgUUBBMbYOgmMrIJVSz0opQYCCk8t4qIjMKilxiFRjmKkY'.
'9edKEZNYgCEHpSzzX0IBttqedUMdGCTMITp0DFJgFQjS6IYQ3J'.
'+8DkChm//JmjmiIY3RE+gAFGkGECxLClC5qwBzpY9RJYzSpWa0'.
'qQVVyiFZjIyChaodWyYrWjAtnEViVyi0vcgiBqvQRXK4IJsppV'.
'q6QIa0E4cdeyznUgXu0rVlvx139YqBO0UEIMkHECIZDhjh1M4R'.
'KIKq5TFhIIK/DcFM6AvDVIgZsaSEMTOiBO1wVrFJwwCCdEipFN'.
'FNYgmOCEXl07kdj/6nUgtGUJaiey2pf0ViKV0IQrYBEMFzzhBx'.
'NoQPIi8b5CigARTizkEkZHiA9sIRlqa4AXjLCDL3iDoVco4xkr'.
'uQqEXkKvmLgEQx1qkVt4LqUI2UQG/JGBTbjXH/A9iHzpy9X75j'.
'cj5fWHKm77UPW+dCXpJekDLSGKCOSiGNLQgE+twDb8UbOQ1IjG'.
'ZaXnD0eEgH5qIwEOJiABAtiCqhvwWC/L+Q9MtDW1A+HrWy8yVr'.
'BKhBSXIMU/akxgmOaYIDxmyYsjImOXqPUWPbapJULBihYYIw1w'.
'MMMQIHsGJmKxmtYcXRGYit21MWII3OwBABqBj22gIodoLMgmZE'.
'uQ2L5W/yKcgLFHfxvniaCWtTGWs0rWnOQ2c+LNGHGzRAb3iVmk'.
'IgZsOEE2cLA2HzAvblgWwABcSAhHUOHDmVNGOMxwAhRkwaL+eM'.
'eKQ7aKmaIX1HqeSETxG5FNILQUm1j1f9X8aq7KeiUKMDVCMIFq'.
'liTYH2iNSSY6kQBf/MINKkju2khJhBVGeov+owEhJLGFzA1CDE'.
'aAAxt+0QE9aMOMC/zbKiiB5x1TIth0JUVe4UyJ1GJC3X0mCCfa'.
'3WZ4s2TcgDY3ulUyCkroODCeMAVxXeBOEPChfkIlGpZXWAAXEu'.
'GaRzhD5rDhhS5UwxbA8AUsIqAAZnicGZQIucj3LZB+s5eiIv9P'.
'ucjXrWaVq5zl9f43rslNEQW4POUkPwgmzg1c4epiGAHQADG8oM'.
'zI+qOerQQCDZyIByYKgI8fyBwfQEAMAqQhBrhIABdkmtANPHS+'.
'/ji5RTjBdV3HuOwC7nErPDfjjZSavvEeCCXQ7vWI8NpzMj9IJU'.
'Dh4FqYQOhEH+URLDCNwrey6c8THQ0EsAR/gvgBU696GsCQClp0'.
'wg8fvznMB7JzsV/E5jcXebkFAvrQ09wg6m6JzeNOEFKY3t+sF0'.
'jngct3DwTh78QwuNrOUIQSPLeVLfQHHqC3BD0CgQhHwJzaKC55'.
'MNxAD37QRCUnsgl/Bzrksbe55yWi/TZj/96UyLf/RVyf737n/S'.
'AMjgUvgtDJ3GNhlI6IJiJamVlxadjwAkCEPyKxNmxAwuJp0Aig'.
'pgjTZ3e9ZhG3BhGuxlCjp4CvhmcJuBG5lnb35jnntWvqBWwRwW'.
'CvIAta8HcSMAF21EFvIDR6RHxcFEjOVkhGIwNddm1d8AdXpw5j'.
'4A4LFGh/lhF1JhGoFXsF0YN7lWoaAYQtsWa8lW8/wmQeaAI98A'.
'NMMAQUtjlCc1QCAASj8wGO4A+JUE0N5wTV9gBrIAbLAAe2EANX'.
'QAfisA+VgBEKcAkb4INw1QqEJRGUcAmzFhF1mF+bIIfiJxEudg'.
'k5F2gbcAnbJ29fFXdJ2GRaIA12oAII/yAG2KBEE1NIfOQIW+A+'.
'CtdK0SRxYLhpO4AM7cANDEWAv6Rza1cKQoiHeNdqFkVTFVFRAs'.
'ZVc+cP58eG5gWHEbFS/mBjpshQgegRmfAJOhUEAeAGJ2AEdcQI'.
'zsA8Wyg3XHQGykMPi3BllOgPkoBdyiAFCHACOfALwTAG9yAKLM'.
'ZbDUh9fWhbF4GOcNWHtZWKQ+iOcFWOA0FoqCALteACOfAHEpAN'.
'4YANW1BFK6RFfYQ8PpBCdYBU/oQ82SgESeAGWRBRquAO40h9oN'.
'ZSGaBnpFAKB4aB/nCBLbZ2MlUKtSh7INlSIllgZTeSsChTFzkR'.
'c1dfDxSM9TgMFXRBYUMG8f8wMdMgACXgT8qXDJ0zAtV0T8kHhp'.
'+VBE8QAPAgYOiQCaUIEZzAUiapZysFax4VURvAWibFdSO5YxXZ'.
'Uue3lTJFCUkWlVyHin6YkaoAj4NDO7YDDADwBF+DAF4AA4IkAO'.
'ZABPTwBo9XZTQABQwQXfm3fw8wCNuElAEQBC2ACqfglOaURoTG'.
'CrmwAGAQAGwAMxIQBnHgD8+zRZKAPGzzQUtAA84EPS1oDQ0gBm'.
'aAmFrQArPQCZ7wlI85NYT2ChWgBEHwCwCAAgRwAlVQCnPwRM2Q'.
'NpmTDJEQBS4EQtBjDvKjDzgAAgPlBgGgBZUHm2s4mw5EaBEACy'.
'3AAWDQMi9zCP7/MAIMoJd98AiZozZbEAKRwDyLsELQ5A+CMARQ'.
'xUiJWZ3Sh53ZaTK04AEqwzIAgAwzUAr9kErkYANk0ABWAFmakw'.
'xbwDyU83Qv5AUI8APVwAYuEASVZwr5qZ/nRDimgAoV0AIL8Cmh'.
'cgHysALf4ABVYAbPAAJSgAMKyqAhIDwhNEIpMAFdkAQ9cAy/UA'.
'wtgEMd6qGxcymdEAsJkAupoCS9AAyBcAEzUA88kAdJ8ANdgADP'.
'4AUx2gBrwAgk8AjuM41A4A+FEAYq8AdhpDoHZAqgcJ1ECjuXcg'.
'pxAgt04gueYgCNkAVZkAM5YAcaAAdVagRCMAFeEA5DQAY4kA4p'.
'IEjT/2AB9FAFO9ADtuACvbAAHvCaQ/qmgPMjmvAJphALtLApSL'.
'IAwvApUBIAAHAMfUoAO6ACEmAECMAEEzABIIAEhcCZeFAKLNAD'.
'yJAxtaAEZNQJmaqpUtMmotAJrqAhu1ABHqAdvlCqTHIiqGoLKN'.
'ADrFqlEkAMXWAGFFAIc4AHEGAPyAAAv2AMewMLryCsbkqsxVoy'.
'QYKssfAay5oLsuAhIDIMWgAq04oCfloNqHIC5XAIDuAAM/ACJv'.
'ALYCAMSuABT3MKN8iutFkymiAK72EK8TEfRmIfstACuBAMtVAi'.
'v4Cqe5IDbvAniPICgdAOBtALw6ArsIAKCfSwEDsypZEJT/vBHM'.
'5xsbAxHRXQIb7gscZwIiE7JQAAANLgAgYABo2CCy2gCwtiRhAy'.
'sxFrGmrRHDnLGhHACqFKrx5CqiPSJMDQIi8SI6nACxWQAKxQJp'.
'Qkte2KHE2RFlPBHIiRGPD6GvTRrF0bIsJQILiAIAqStuUBMmxL'.
'syAREoOBFjuRuBSbIfJ6txubCi2QILlBC6jAIOYxkYNLKQyhFE'.
'yxFUxxs21hsbEgH7ExG7rBG6yAGaZQHsGRZpkrMjJxEp9huMpB'.
'saHbGJDBCpbhGJpRHnchHAX4urTJEAIhGKaBGsyhGqu7GXeRF1'.
'uBEsILTMb7tnBrFXiRF1rBFZ8RvZQSEAA7'.
'');
  }

function image_link()
{
  header("Content-type: image/gif");
  header("Content-length: 311");
  echo base64_decode(
'R0lGODlhCgAKANX/APn28ffz7fHp3+7j1uvf0Onby+jayOfXxu'.
'XUwOLYzOLQut/Ls9/Ksd3HrNrSydS5mdS4l9G0kdCzj8+wi8ys'.
'hsvHw8rHw7+WZb6WZLujhrmTZbaHT7KBRrB+Qqt7P6CPfI5mNY'.
'B5cIBbL3VUK3RTK21cSVg/IVZNQFA5Hko+L0gzG0Y7LS8iESwg'.
'EP///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'.
'AAAAAAAAAAAAAAAAAAAAAAACH/C0FET0JFOklSMS4wAt7tACH5'.
'BAEAAC4ALAAAAAAKAAoAAAZCQJdwSHQBBIcGYzAMEAqIx0WTEB'.
'oWCAWlI/oIJRNIZENSpYQYjgeEarVWwszIxHK3QkJHyd46WYYV'.
'ISsrIX9Fhy5BADs='.
'');
}
 
function image_root()
{
  header("Content-type: image/gif");
	header("Content-length: 109");
    echo base64_decode(
'R0lGODlhEgALAKIAAP///8zMzJmZmWZmZgAAAAAAAAAAAAAAAC'.
'H5BAAAAAAALAAAAAASAAsAAAMyCLrcPjDKyEa4ON9B1HCOwHla'.
'BgliB3yChgZtSk6DeMprAC7wCNi0iUpBKBqPxp2SkQAAOw=='.
'');
}

function image_cdback()
{
  header("Content-type: image/gif");
   header("Content-length: 125");
    echo base64_decode(
'R0lGODlhDwANAKIAAP//////zP//mf/MmczMZpmZAAAAAAAAAC'.
'H5BAAAAAAALAAAAAAPAA0AAANCCFDMphAWEIIQ5cVFuidNs1Rk'.
'WQmEUViXcb3CkK6t4cb4bNn8NcS6l+tHRKlYgltRdhTybAPmao'.
'mLSj/Yz+PJ5SYAADs='.
'');
}

function image_album() 
{
  	header("Content-type: image/gif");
    header("Content-length: 286");

	    echo base64_decode(
'R0lGODlhEgANAMQAAKZlCvvu1/eon/zQb/7LUvTKxr56Bv/7+/'.
'zotv+6J//2429IC/7ahvK8YOuZA9OGAORyUc+zevHh2finDpx4'.
'MubHtOyJTP/BPOZLM7o9BN+jRPrv7bSXW8ePTsWMP////yH5BA'.
'AAAAAALAAAAAASAA0AAAWP4Cd+R2BK0naMrCg1AxIoiqm0YmU4'.
'FzPTtcBqVADsejJgQDbadB5QQoRCiSAQDMJgJAE4vp1FJrPgDB'.
'KTyahidCQoCwhmQSE4DAYi4DG5NBaACxp3ABRcEH0EDAwREQ0J'.
'DwAAESMHAhZai4sXEw6FGywblw2aAwQTDx4SOJYQFg2wGhoCoD'.
'giGwUCugUqOCEAOw=='.
'');
 }

  function image_login() 
  {
	header("Content-type: image/jpg");
    header("Content-length: 37698");
    echo base64_decode(
'/9j/4AAQSkZJRgABAgEASABIAAD/7RBSUGhvdG9zaG9wIDMuMA'.
'A4QklNA+0KUmVzb2x1dGlvbgAAAAAQAEgAAAACAAIASAAAAAIA'.
'AjhCSU0EDRhGWCBHbG9iYWwgTGlnaHRpbmcgQW5nbGUAAAAABA'.
'AAAHg4QklNBBkSRlggR2xvYmFsIEFsdGl0dWRlAAAAAAQAAAAe'.
'OEJJTQPzC1ByaW50IEZsYWdzAAAACQAAAAAAAAAAAQA4QklNBA'.
'oOQ29weXJpZ2h0IEZsYWcAAAAAAQAAOEJJTScQFEphcGFuZXNl'.
'IFByaW50IEZsYWdzAAAAAAoAAQAAAAAAAAACOEJJTQP1F0NvbG'.
'9yIEhhbGZ0b25lIFNldHRpbmdzAAAASAAvZmYAAQBsZmYABgAA'.
'AAAAAQAvZmYAAQChmZoABgAAAAAAAQAyAAAAAQBaAAAABgAAAA'.
'AAAQA1AAAAAQAtAAAABgAAAAAAAThCSU0D+BdDb2xvciBUcmFu'.
'c2ZlciBTZXR0aW5ncwAAAHAAAP////////////////////////'.
'////8D6AAAAAD/////////////////////////////A+gAAAAA'.
'/////////////////////////////wPoAAAAAP////////////'.
'////////////////8D6AAAOEJJTQQAC0xheWVyIFN0YXRlAAAA'.
'AgABOEJJTQQCDExheWVyIEdyb3VwcwAAAAAEAAAAADhCSU0ECA'.
'ZHdWlkZXMAAAAAEAAAAAEAAAJAAAACQAAAAAA4QklNBB4NVVJM'.
'IG92ZXJyaWRlcwAAAAQAAAAAOEJJTQQaBlNsaWNlcwAAAAB1AA'.
'AABgAAAAAAAAAAAAABRwAAAlgAAAAKAFUAbgB0AGkAdABsAGUA'.
'ZAAtADEAAAABAAAAAAAAAAAAAAAAAAAAAAAAAAEAAAAAAAAAAA'.
'AAAlgAAAFHAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'.
'AAAAADhCSU0EERFJQ0MgVW50YWdnZWQgRmxhZwAAAAEBADhCSU'.
'0EFBdMYXllciBJRCBHZW5lcmF0b3IgQmFzZQAAAAQAAAACOEJJ'.
'TQQMFU5ldyBXaW5kb3dzIFRodW1ibmFpbAAADHUAAAABAAAAcA'.
'AAAD0AAAFQAABQEAAADFkAGAAB/9j/4AAQSkZJRgABAgEASABI'.
'AAD/7gAOQWRvYmUAZIAAAAAB/9sAhAAMCAgICQgMCQkMEQsKCx'.
'EVDwwMDxUYExMVExMYEQwMDAwMDBEMDAwMDAwMDAwMDAwMDAwM'.
'DAwMDAwMDAwMDAwMAQ0LCw0ODRAODhAUDg4OFBQODg4OFBEMDA'.
'wMDBERDAwMDAwMEQwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwM'.
'DAz/wAARCAA9AHADASIAAhEBAxEB/90ABAAH/8QBPwAAAQUBAQ'.
'EBAQEAAAAAAAAAAwABAgQFBgcICQoLAQABBQEBAQEBAQAAAAAA'.
'AAABAAIDBAUGBwgJCgsQAAEEAQMCBAIFBwYIBQMMMwEAAhEDBC'.
'ESMQVBUWETInGBMgYUkaGxQiMkFVLBYjM0coLRQwclklPw4fFj'.
'czUWorKDJkSTVGRFwqN0NhfSVeJl8rOEw9N14/NGJ5SkhbSVxN'.
'Tk9KW1xdXl9VZmdoaWprbG1ub2N0dXZ3eHl6e3x9fn9xEAAgIB'.
'AgQEAwQFBgcHBgU1AQACEQMhMRIEQVFhcSITBTKBkRShsUIjwV'.
'LR8DMkYuFygpJDUxVjczTxJQYWorKDByY1wtJEk1SjF2RFVTZ0'.
'ZeLys4TD03Xj80aUpIW0lcTU5PSltcXV5fVWZnaGlqa2xtbm9i'.
'c3R1dnd4eXp7fH/9oADAMBAAIRAxEAPwD1K62ump91rttdbS97'.
'vBrRuc5clgf4wvtVIzLelZFWC4Fzb2vY9waCW77Md5ot/N/wPr'.
'/8HvVn/GB1B9HR29OoJGR1R/oBwMbaW/pc2xxH5n2drq3/APGr'.
'in9Lqa6amsO2HF9NgB7WBx2OZ/W+gpIcvkyi4TjCj+mOIS/e2l'.
'BbLLCB9cZS0/QNEf8ANk97Z9d/q9VTZbbdZT6bC8+rRcwcS1u8'.
'1envfubsr3+o9ed9NzcSnCxcPqPTLzXRQ1phrcip5cN9l217bq'.
'2Ossfv2/of+PV3G6fd1DZguvt22PDgDY6SQI3OP03taz+wp9Q+'.
'rY6fV+0KMwuxqmOtl9TA4iHvaf1b7L9JjPzt+z1ETy+WGkzAy/'.
'qGW3+HwKjmxz1hxCP9cR3/AMBv9LxunZ3qfYs59NDGlxxy8hoj'.
'ndTl+uz/ADmf9cVnJqxa67Tk2NurZXvFuMPsluxv0vU9J1mHdU'.
'xv+F9H0lgfUr6xse/K6d1OLXZNbn13FhfZIh1mO91TH2eh6LN3'.
'82tSjp/RMzrGDj4+osyGuNLXP2baWuyntfj2O+g70mf4L01GSv'.
'G1u/8AU/6utw2N6xe5zsjLpHp1vDQ6ut59UMtsaN11+z0vVc76'.
'Hv8ASXTpJIE2hSSSSSlJJJJKUkkkkp//0B/Xbrd/UPrS3HwX+7'.
'Ht+w0O0cG7P0vVLvTduZ+dXjv/APCyP+YBsY0N4dGp0jVbf1jr'.
'wavrJTjY2Ox99mPZk5Vj2yKtz/SxnNte13ptzbX5FeRX/wAFXZ'.
'6X856kaMHHurHq1htwj1BU4tbr9FzW2fR9v71at8vmxw+YGxQs'.
'fy/enJhy45y2Io9D/L+qh6S6nHruvdcW2MaG1xo6XRWGVNs/nP'.
'pOWL9ceo2P6faYFVdzmUU0N4bXy6P3v0eOz1HLo/2MxpJZc7e0'.
'yGvZJ48i3/qVzf146H1JmJVl1MbZg4jHWX2h7QRu211n0nllj2'.
'bW/mKTLlxGM5Rlc5DhFiqBY8ePIDCJFRibNF5Xouff0/quNmY7'.
'/Tcx8FxAI2OHp3AtcWsf+jd9B69L+orXdR6jk9ScAaccFtbm1e'.
'k03W62fROyx9VH/tz9NeX9KLWZLXvY20NDttLnFnqFw2NrY9gP'.
'6R2/2L3f6u9KHSOjY2Dta21jd1+zg2v99xB/Ob6jvZ/IVBt9F+'.
'sddwejDHfmh4Zk2ekHsAIYY3epbq13pt/kb0LD+tHSMzJzKK7d'.
'jMEsbZkWbW0uLya2+jaXe79I30/+E/wPqIf1k6NkdVd070hW6r'.
'Gy2W5NdvDqoLbAPa/e7+QsjK+peQ79sY+IaqMXMZjfYmyTtdjn'.
'e6u1se1r3t/nd9n76cOGtd2KRycWgBj/AOg/9+7L/rNituvx24'.
'uXZdjevurZSS5wxxVudU3dusbkfaGNxNv8/wDpf9ErOB1rAz7L'.
'KqbG763ENYX1lz2tDd9zK6rLH+lvd6e6xrP9Iz9C+q2zFw/q9m'.
'WdU+3dSpxsPErxLMV2Pjvc4PFrn232vsLMd1Tnutssts/nH2fp'.
'FkdDxb+l9fopfbTSa/UbbSHG5zqmtf7qnPpp2bXU/wCn+0/8Ah'.
'Q7p4p2PTpf1p7Y9U6YMdmUcugY9h2suNjNjiJlrLN2x30Uam+n'.
'IqF1FjbanTtsYQ5pg7TDm+36S87prNFPS25lmPdmYLrxdiZAe6'.
'l4ueW1epfj031Oymv/AMHsu/6C3fqpm4mJidQvyHVY7HX+rdXU'.
'0troe932ZuAWu2u+2Mtr9N9ddH85s/nUiB0KomROorR6l72MEv'.
'cGgkNBJgS47GN/tPdtUl5V9ev8YPTndRs6QenftGvCeRaLbbK2'.
'NtZubaG1Y8eq+n/T2P8A0Vv9HZ/2ou636ofXbpvXMFkzi3scyl'.
'1Nz95D3D9Cz17Pdd6zf5qx/wCls/wn6X+dC+n/0en+u3RcmqjK'.
'+svSsy7G6hjUN9SlsOpurpLrPTtodXZus/S2en+Z/wAGuCwPrB'.
'1qq47sq2suJOzMZur1O5zWW2NY+v8A6ite0vYyxjq3jcx4LXNP'.
'BB0IXkHXep9e6B1+/pj7G2UUuL8X1mavot91P6b9G6z0v6Pb/w'.
'ALQkqrdjE+tmbXW37XhCwHRj8ezaZ3aV+lkB39bf6npLXr+s3R'.
'H+pVbksxLTpZTnVup1dofUZcKmWsd+ftt/trkqfrbafbZg1kaE'.
'ljyGyO/p/RWjldXxOv4rmZDbHZm1uPQ24CxrC8n9PV7W7LNzvp'.
'7vV/RoqA8baXTOg0M+vGPj0V0uwbshuTjGoi2r0aWvv9j3Oscz'.
'9ap2r0T6zdUu6fgsZi/wBMzLG0Y2hdtc76dzmD6TKa91j1j/UL'.
'6sZPSqrMrOe03S+mmlhlrGtefWsd+b691rPzPoV/4T9ItT6ydD'.
'yer/YxjXNodj2Oc+xwJIa9jqnOqDdv6b3ez3Ia1IxriqXBxfLx'.
'8P6vj/qcfzKn0GtWOKvm4eL18P8AW4XmOk9R6vmdSpOTnW243S'.
'3/AK7sc0BpafSqpyX17WX33Pd6mT/N4uL/AOeu3xOpY2WXCrd7'.
'GNsMt0LXOtrbsc3c233Y1n81v/kfTXOdA+rnUendYNeVWx+Eyh'.
'tbchjoFno2NtwfVone3Ix/3/oWVrpbun4t2S3KeHC1o2kse9ge'.
'0btrL2VvYzIYze/023ts9P1HpDi4Y8RuX6R03vh9PD+hL/J/+O'.
'/rvcWirlQoXp5b/wCN+80L+rdIzsZ+Pe81U2sO6xxa1oja4Vus'.
'3OZXkO/7i3fp/ZZ+hUf2Vg/YbHtt6g5rGv3D7Xk+rodztpfeHN'.
'e3b7Nrles6XhvpbSBZWxjWVt9K2yo7Kw5tTC+l9b3Mb6j/AM5J'.
'3SsRxtM2tbez031sutYwAx/NU12Nqof7f5ylldn+ekucpvT+jO'.
'v+zbsut2ZS+Kn5Dw1oyGllltNVt/8ASvbb+lxmWWV2X3Pf/PIg'.
'6DhdQwXU2uyj6WY3Jxn5VhtfTdj7Kqzjvsda70Gupd/OP/S+rk'.
'f6ZaX7LxJok2uGKAK2uutc07TuY62t1mzIexw3Msv9R6sV1sqa'.
'WsEAuc4/FxL3/wDSckp8X+sf1L6nifWPLyBRZfjZtj7YpsbX/P'.
'O3OoPqur31Oe7b9L/wRdH9TvqU/Lx7snqTHY2Nk202jHrMAjHd'.
'vpqDo3Or3s3W3fn/APaf/TLuOsdB6V1plLOpUC8Y9gtqMlpBH0'.
'm7mFu6qxvttq/m7Ffa1rGhjAGtaIa0aAAdgjab0f/S9VXM/wCM'.
'DomD1L6v5OXkDbk9MptyMa0GCC1u91Lv+Cv9NjbF0yzvrF+zP2'.
'Jmftbd+z/T/WPT37tsjj0P0v8Ar+k/RpKfFKMXIEWejYWjg1um'.
'QNT7XS9aV3UD0445ysS2izEsFxLxBsb7X+nrRV7bNv8AOb7/AP'.
'g1vY7fq67LZ+zrM+vUbPWZU8f8HG62nL2f11o5w6efrLjHqpub'.
'iB7Za4PNDrZ/QuyX2uZ6TPV/N2XU/wDoN6iS4Pa4VXo4lNRG0s'.
'ra0jzA93CMkkktUkkkkpSSSSSlJJJJKUkkkkp//9kAOEJJTQQh'.
'GlZlcnNpb24gY29tcGF0aWJpbGl0eSBpbmZvAAAAAFUAAAABAQ'.
'AAAA8AQQBkAG8AYgBlACAAUABoAG8AdABvAHMAaABvAHAAAAAT'.
'AEEAZABvAGIAZQAgAFAAaABvAHQAbwBzAGgAbwBwACAANgAuAD'.
'AAAAABADhCSU0EBgxKUEVHIFF1YWxpdHkAAAAABwADAQEAAwEA'.
'/+4AJkFkb2JlAGQAAAAAAQMAFQQDBgoNAAAAAAAAAAAAAAAAAA'.
'AAAP/bAIQACgcHBwgHCggICg8KCAoPEg0KCg0SFBAQEhAQFBEM'.
'DAwMDAwRDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAELDA'.
'wVExUiGBgiFA4ODhQUDg4ODhQRDAwMDAwREQwMDAwMDBEMDAwM'.
'DAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwM/8IAEQgBRwJYAwERAA'.
'IRAQMRAf/EAPgAAQACAwEBAAAAAAAAAAAAAAAEBQECAwYHAQEB'.
'AQEBAQEAAAAAAAAAAAAAAQIDBAUGEAACAgIBAwIGAQUAAwEAAA'.
'ABAgADEQQSEBMFICFAUGAxIhQwQTIjMxVwkCQ0EQABAgIECgYI'.
'AwgBAwUAAAABAAIRITFBEgPwUWFxgZGhwSIyECCx0eFC8VJikh'.
'MjMwQwUHJAgqKywuJDFNJgUyTyg5M0FRIAAAUBBgUFAAAAAAAA'.
'AAAAAGDwAREhEJBBUWFxcIAxocEgoJHhAhMBAAIBAgQFBAMBAQ'.
'EBAAAAAQARITFBEFFhcSCBkaGxMFDwwUDR4fFgcID/2gAMAwED'.
'AhEDEQAAAfZgAAAAAAA+ZF8d1hkKzjZNJZ3jsuI7LwIsupjU16'.
'Zj6zzxfSYXc0AAAAAAAAAAAAAAAAAAAAAAAAAAOR8+PRzUSzFk'.
'g1BGI8K6pMJx2moGdRSs1nazZPbx2UAAAAAAAAAAAAAAAAAAAA'.
'AQyETySZAABXni7LJdqlR3MxhYtneOpk0WvFlrHco5ZusWUs9c'.
'gAAAAAAAAAAAAAAAAAAAAA8TFUvbj2xvFhrNnczKmkkyUB46zm'.
'SjepROjuu5Yyd1incjEU6lfNQdY91LkyAAAAAAAAAAAAAAAAAA'.
'AAACMfOyf4fbY+fruaazXerjJxrnvEfrzkdMVVzYmq6WaEc6p2'.
'LCpctpHI4TXKzonIhktPTKAAAAAAAAAAAAAAAAAAAAAAPMx5/t'.
'zxy7d+e+nLfDeLHyeqVy1z1O+dVXu8cT0cdDdcnVJRg51LJ8SS'.
'lmua7nC5lJFufazXQAAAAAAAAAAAAAAAAAAAAAAweBMezz9tZx'.
'Lzs1l0zrXnuVi9+Han78dc2Vm7tYNyRLGueB6Gr2I5FmoksFIe'.
'pxT1SXi5AAAAAAAAAAAAAAAAAAAAAAKiXxfTFl6uG641nW4GTW'.
'zNb51sm+N9uPaKRJYsueeuUcSfZ6wnZsea4LFs0srU9odQAAAA'.
'AAAAAAAAAAAAAAAAAAeMlqvV57HpMpjWcayhZitpemddM2y4du'.
'2dbRWejlpZrjfm/P15xuX9no5Y0uudQ62ua89pZkAAAAAAAAAA'.
'AAAAAAAAAAAA4nzmrP1+edje8a6mtzrZH6csVvL1zvtjVn5+0b'.
'pmdy3w3mF0xBmvO+feV1JNl+1rnWU7JzWJc+4AAAAAAAAAAAAA'.
'AAAAAAAAABQS+W7c7rpiH34yMdNdZ2jaOe87GYxZO4d7Hj0idM'.
'yMazFd6OVNy6VHHeDJk9dLg4VHNZJ9eqAAAAAAAAAAAAAAAAAA'.
'AAAAMHhCP7PPM+f7unflIs17cufTjmuO8dsa1slY6WPn7b5uxH'.
'6ZidOfHefO+X0R+esA2PVlvnUCodnBL5LdQAAAAAAAAAAAAAAA'.
'AAAAAAK2PE9cvRwgeD2Xnt80zl16Z3y7+fTtxWZlylhw79ca5a'.
'lpw6UXs8/Dpzxz6ed8no0zRqbk621xrka2Ln25uAAAAAAAAAAA'.
'AAAAAAAAAAAeRlqfZ5q7l0zjV97fNmzbNWBZtEnn0kc+k/j0id'.
'cRunOL1561w4dqHzdsRgGTJ6VY8YJ1nqzIANEG66mqbrkGDU2B'.
'gyYMmQAAAAAAAAAAAco+caSfd5KTweu49PGy78smYyK3jJvmz+'.
'Pbti1Xq4aazqCt83oqeHTAMGToT6zZ0l9RFwADB4Pr4p2enqce'.
'ih3wo98fYcfbYKKQrSQaEAtynPYEgAAAAAAAAAAAFFHkO/LXeY'.
'Xm7+l9vm6azlcoNNZ2TJtNSeXTlvBddTaXRPP+X0xeWtULsYOh'.
'JOizU9yZABqeD6+HnVjjrX75YT1PH3cN8e01Nz185ctcL/AB3w'.
'tRvhhOqzJu4z1ptceSXue9fedXrnk1S9x3lNAAAVxYGQACmlr+'.
'uK/N4ln6uGmpkxZlNbnZSYrea1TvnfLWcxyl854/TyzcJhdzBN'.
'XY2T2iWagAanhOviZ7bpvNRenn9Rx9tD18m2enbHovbjyHXx+j'.
'5e2NrlUXMzHbclL6OaqbmtL2PI9fJ1x6J2ekTfHmz7LHp6gAwe'.
'dPGaerj1UZABqVxTrvqbx2NtZ5bzp0xy3jnvAHSa1s6TWE0sj8'.
'u3nfL1xLoDcyXBtLvZ7gyAAanguvhm57+kx3ga5eb6eb1HH2ef'.
'6+XbPT2mPRBuPH9fJ6Hl67Ka8rvhzMJ3ufV8/T5/p56+z1XP0+'.
'P6+O75er0s6VNxU3Pp89ewBggGsaLsSKlJkAA8kRVsOmI2NTSQ'.
'ZMxtZp0zw3jOpz3nlrPXOtap+PSn8/XBoYNjYnkhfQSegoAAan'.
'g+vimY7ewz3qtcvK9PN6jj7YWsU+vPYzeiQGvTY72x5Dfn0sys'.
'Q9Jz9EDfnqrn2XP1eX3w0uZksS5znt63PWSDBASGvn1iE49DEy'.
'piAAeIjiXWmTTU72dDEb5uF2joaGLNtTnvPmpqs5a5GgMncmnO'.
'X6HZ1AABgpgXJyKMtSUUqc1wkhbgyUxSJOUXBVpGPTrAISRlJc'.
'rHLkwV8lTb4y3jJoZJNvtJbhJqAaHzOPQkis11gdxZitgYOsc1'.
'2juaHI8YQAZOxLLnOvW6zkAAGAgLkAAAAGh5kjEg9EVJFKosTg'.
'djmSiYXRQJLPnl1Ek0Bk73VjHrllsWgKaPHp6y3QwZMHU3M0Os'.
'amK3jFmsvc1KOvORHMm8ux76LTUAwYTWsxrZys6S9JdlAAAAoS'.
'zMHQrS5IJXlyV51MEkqS5PI2RktWvDnGOxNtgkteB7iXz6emZv'.
'yjjxdzYS2S63NRZYTUxZhsdDJsZrCdowFyYKAoSOWkrG/fb59K'.
'A5pC6cqvOrDeK/pyq/J6rzri7x02UAAADyhCJhFJJkhk0ryAfR'.
'jyJyOp7E8zVOloviDnG6iwqUtYnsJvz9zczPszmUMUZV3PWM3G'.
'huubbSXss9ZRsbGTQV1k5W848wtYk+akZvvd4AwQenLyvXhx+d'.
'7M/X+Y+R9PXefU+vja8e+ygAAAYMmAAAZMAyCpLYwUqa14FriS'.
'CwWSQkzHonSQ5+hTIMA+YnAkS92drnonNOVdl1Wzl7tWBINTqY'.
'OZ5taiJUt0nrdQDRPN+rx1PzvZz1O/m7zfD6q39H8X2fPvLx0A'.
'yAYAAAMgwAAAAZBgqko7fJr1JRqcY9C6X7nbJkAA8CVBkydyWS'.
'bOKdIWck0l6N9lnEw6R3SBVHLFPUl/YBE1z8b6/FY/lf0GmpG9'.
'fCN9v5HpefePw9HdcmC9BAK8Ec9CZKs5l6CuKI9EQjmSSyPMnc'.
'9AZBgqCqtpjU2Wxmrpm6ZyAADzEtZYOdcU2l3JlYMyDqT7YCVi'.
'xY0Oy2MYiOvuLmRQwVXXz+U469F+c+zWerlTfX+fb/AEPB6vye'.
'6lzvsSSIX4K8rwSDiak8gl+AUJeleQwXRXncmAAwVFVltfLOtt'.
'85tUyAAARZfOGTpXJOpubAwDus6Oyebtj3MOIa9M6m1Nj12s5B'.
'gpe/m8lzt18H61X9XwX3w/p0H6z8/wC04emHjrdmCsIx2BYmSs'.
'OZoWBXmxYHMpD0BuVZyL8gHclgAGDwh5g96elMgAAAwUMePWWd'.
'CYdakGybmFwdDMQrJJXFMdM76HtGbHUAwlN28/nfD6eHs8+vbh'.
'f/AJX9BTfd+V6T1crrh6MqMGTBkwZMAAGTAMmAZMGTAMgAAwVJ'.
'bGQAAAAYPmkRq2NzobGyyDsdzsdzumVqSCnDO/Qyen3nIBhK/p'.
'y8pw63fr8vDWaLGpPyvoX31vBeef07KAAAAAAAAAAAAAAAAAAA'.
'AAPIx5mhsZMgG5uZJdXGbCrlEvOu9npdZyADBzuedm8oq+vCi4'.
'd/S9edhz67KAAAAAAAAAAAAAAAAAAAAAIR88jjXQ3NjJk3Mrqb'.
'HWO9nSPXkPOrTeQABgAGlzpW8u8uQAAAAAAAAAAAAAAAAAAAAA'.
'YKmPA1g3Njcwdq2jrUkwcM2VZeY1ZakmwAAAAYAMgAAAAAAAAA'.
'AAAAAAAAAAAAAweZjy5muYMG52l6297LBKvNlFzm3285AAAAAA'.
'AAAAAAAAAAAAAAAAAAAAAAABg8pL5izobnQG0vWu1d1iSdYupf'.
'QayAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAIcfODNZOhmNlzWZNb'.
'do2W7PV3IAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAr48CdK6G5qa'.
'EshxzXWXB6yz0lgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA8/Hka'.
'nLsnReqTC2IVlYQV4R7mJ9AAAAAAAAAAAAAAAAAAAAAAAAAAAA'.
'AADystInSuhudiyOxEK1eUs6PX6zkAAAAAAAAAAAAAAAAAAAAA'.
'AAAAAAAAAGCFERYZGOKzLL2SDVMRs69vc9KAAAAAAAAAAAAAAA'.
'AAAAAAAAAAAAAAAAGAIVgq5rRY8RS7ZsNQAAAAAAAAAAAAAAAA'.
'AAAAAAAAAAD//aAAgBAQABBQL4Bfz2aLaUpCV2KdMS7V4qtmwg'.
'rcNBtWrBtVtBXq2A6USiqNrXZZ/IVRNpmhsrn90NdRnaCzx1WE'.
'+bWNwTVxDTSS+okFVyTvbST9ilp29S2WajpDXYJwMFllUTeMW2'.
'lpxBj0VtH1LwGQ55StzY1VYrr+WbGzTrqvltFomzr2fwbzcdPU'.
's4rzWC6sStnMLmdygzsVPOyqkC+ZIgWtodOvH6vCCyxIu1rEEV'.
'MN2xu7r+PDLVp1Vv8t3bhZu8u4f05wtqib+6sTypg8ppGJsUP1'.
'8xcE0wB2sLFu2EnfibNYK7BbotiCVhWjBZ+JncaO1s/Z4x7dS2'.
'W68QWWX/AC7YtFNLcu1qVlF/thUGNxBrqNrNXyr/AEsxKbJ+1v'.
'Vy9Ny9qu0B29R4dPIOteo/IROJiriLfsgLesG5r8eXKEYgtTOb'.
'TM1y79da/G0lm+XeWtzMByl7oE2G5jZ/y7VrvPws162QrQjLP7'.
'jdjZP6t4nK1D3ZzSC72W2wz8JjVytDSqnYl1jordkxSVY7Fxnc'.
'o5d11GtQb71VUX5dfb3bkH44hRTO0kFeCa7ML3EYcw73X4NVkD'.
'MsXauBGyvHuajFEpYLSYbWVP2LZqF3qQDDsphoqaNqLH1tiMHU'.
'/hjxdPGr5d5G/s67DNgH8QXM/SRwdOkxtIifr3Tm1csMzEv4HT'.
'2TatgSdpSCjx+8sGws2H53IvFPl3kLue1QIMj1joFlVWZcYqDg'.
'UyT7S3iA/u0zNHZZHPGyGpcstwhvKHv0vF7bbPy62wV1uTKwFI'.
'v5RmVgBVDSOHYaEYME9hE9yMgZLtZgJSvu45HbbC5meldrVyt9'.
'tU/Y2Fg2swbYlj63b1sHY+XeUs/Cn/JcRVhfygw9i+ypy5C1lT'.
'AmJ79KkzLSQlXu9pg/Grlk7b/l0HSmvYWprIx1mg163h1rs+Or'.
'zsfLty43WVjgirYah7uEE7eUdclkyzvme3aJYLWrE0HJtlaDFh'.
'ybbuQSWtyPo8fZyQtmWJSS+rWYarah46vhr/Ld67s6+MtsNhQ7'.
'iU5Re5AyRjlpkzMGJWv49tu03HGVrR3LdHPGlvv6Na8VQWswa0'.
'zuSxmuKrxX5b5O3lfUJc/KylOVnqAMRJ94cqobButL9dogJ6R9'.
'uLYIrnbE8dUDs+nkMgg9M4nNfSrKw6ZGYSB8VY4rS52ZmPbrmq'.
'nt6Ricorysflc2F5Fieu2Ry6DrjMzYIuw07xnjauGv6X9n1NgU'.
'xbEZNvaquqzNWzmnTy1xq0vHb9fLyu49Z8LY5V9tV3vHo2vq1a'.
'+x5NaEZKfiPKWYqp/Ozab3EROK+rEAiWFZYxYj7RoJe/I9P6dF'.
'IDdwTM1Nfv249T45SvbZF6aH9lnkXS7V3Wtba7HY0V7dV20ty6'.
'fa7L6Wr3W8gzqvkGSv/pvy1d1rWm3uvTZV5FnuBzNzZahP+nZP'.
'+nZn/p28abu6nrbdoW/1bRVbTrco/juRr0Sj8GmP4Fn9fsB92+'.
'7nFb/eH0UcQO3WZw4TQp7Wv6rOXOuvmz1PWz0EJND+za5d/WJ7'.
'l37e/ZtDhqzx5ONx9hdj3LdpePYHLWpHIfbcWsxKB3H5rQbrSt'.
'VTWT9J5drvXKrbVsR+Q9PkPI9qZnjfI59RAMv0KLpr0X1WM1ql'.
'XzOamdvI7STsmGtxD1EH3P2UdNk4TPqqpcUmt8UVi671tjlp/w'.
'C16A83kVdeaH9mxjv63+1ftvY/Wnj/ALX0CwWUMjcDOM17uy1F'.
'y3J5LHfpbjZaQ2rPH/ebt1aBX1u5WoA9D2Gwpr0pLO6Hs16XlZ'.
'4j1ecYqNS7cZEtbjz1mlSDALiAzKz2MaqdpJ2GnZcQiD7ATdP5'.
'+n+gutWfsuR4qkLV67P79P8A2j7eR/0TQ/s3Nb3Vip/f2I+5dY'.
'FQsdOpkB+x27if2bOJ2HJUZOmjrPJf7k/uReVOxQazVfZUf+hs'.
'5t2LLVqrZjTnj1dzab9zW1BZ5faaf9LczV5e6VbFV612erzD89'.
'rQNve/w3QVpEqCwF5kzKQDMxiBm6f42gVjCghRZfrXtGRlh9CD'.
'LCsxU7liIqL679PuNRp9tpYvJT4/3op7YIyH0st+iZ+gZTp8GA'.
'6W6hdq0ssvr1C6W0Wa6adve1/I2P3F1wt6LxXaZQf0JfrimuvX'.
'Fj0a/bmruXW39HfuTf3P1kzOc5mcpXc9ba+yt1VdvoZgq22m19'.
'Svta4FQhVwx9orjp7Tisw6zlPxgLGMiRPaEvOSRkQzbKm/rW2D'.
'3AZ4ynnd8KzBV1tha/F729fSf3OPjaK+3TVZW3lK7q/+vseT2D'.
'Lb+PkN3yFgsS176advsDR3jbdoEW2yzcF15cIlthtsY5PXE0Lu'.
'3en2p2lNvTyjEalNYvvM4LApAEPvARPefaDEOZ7TAgDxpyrmSZ'.
'xE3NZ7RZTZUegEH38fXw1vRmZmYWncgb+OzZ3Xvq7rU16uvUGp'.
'pY69de1XHo1q217zbf2quXkLU16aVS+mywV7FlYNOjocNWuuup'.
'fJeT5TxZCnyFv/AMZ6VJya5alCDM4wjDJ/u8i47vjfIjZWeT/s'.
'HNGTyOwoTyGs0tvVjTs21xN9YuzQ0zkAkTM9p94eUyJkw8WnbU'.
'D3xhptaZsa6l6WiEcAoZlXivVjHtxK98NabxLNsR9qyxdIv2h/'.
'FRsbGsfIJsWXLpO60a11dadx/G7mt2Bra+2mjdXsKvjKLBtW12'.
'WqoAXyJ5bdNG7Toadlux5WeT8d3Z4tUeb9edQ9BkQ8jKKjjhGO'.
'WZA9vkUWtvFeMOYyq4s8PrmbGlsaw5VmKBlleBVnFoHZZTsOEr'.
'3vZL6bAPcfaZIg4mEGKLBOU7vCDZqYb/HuD7swxpLys9F74Gzs'.
'Nixe1Yz+1VDbEdxdbrj2Hw+no/rN0u1e3c+HS+s12AZipEAmZc'.
'/GaFXcvo/KU6Qt2PQQDN7XFG5Ec57ixVradt4QVnBYveSV2Guf'.
'uOiJvrFtqcDOP65M/El61ab1KiJ/dyQzxiZt6mblvvrr3NjYcD'.
'ap1jYdm2pE1avzpXA+Lup7c29bvqyPWy2CC1Y18qqt2LAiImvr'.
'59fl/wD9/QEiDgYoEHfx3KjO1U0NVyxvyi1pyOzbU6+Qlfkap+'.
'zQIt9DQZEdarjeiI+J4qvFXW5sLfZNWrt0jVQXW7haV1flr0YC'.
'a9e8f+NoSgfrbG6gOx027npqHidJgfEaSi522NGv2rs/s8YoXX'.
'0UC3dNjR19k1+O1W2tfVp1l2f813/G0JR47V13IyNnxenVVR4v'.
'T4+i+jjLkDBtGoz/AJuxBqayTLFNXUwPXv6yts/p1T9IGNpMAa'.
'LJ+tcIFuBFlmDTruezck5XCa5DsdCoyzxgj6dlZh94lttc19uy'.
'yPUbGajjKUFdXXbOFRe7sSwrtKoc16xHKvHFclseYlGu3Le4fs'.
'dNs6oqUeV4sPK4pq0L6OHlhGHlcaZ1TXocO91p4f8ARmxr5OPM'.
'QXbVDAgjyGP1dXH6/p2EFQ/wWiutXs/+ema+uSf4L6+5WeanEw'.
's5AQNnr+U/IQHBWxQVtQw8cHY7k/TUy+haulGZkzVr71vo3f7X'.
'Qs21d2q9b/DsFFI1yVGu2U8c3Lrto+f+vqSryWtbZ5JuOvX/AK'.
'39k8a2aD5bVBPltUjVqsqrNv6Vn/X1Jr7VWwNjdp1ynkKl2dfZ'.
'r2FuuSmv/r6kbaTdgzjePHW1jmj1eU8Z2oORPjfGCj+PyWmba6'.
'Ni+fvbCweQaJuVkDZrMzW04pPwE7izkhigGdq3gmvh+Hvtr+Jl'.
'WRXzsmnR2avRtDIoA/Z5G6y3IC3VubRw2tNvx+QUeO16Lv5Nmv'.
'tXdAZzaczBZiDZtg2nE/aJgtSLsKJX+vmzgZetzBtd1UeyeO1S'.
'T6bVzFoueJrDF2r7NWazsMti6oxB8t8tVxuwJie/X36YnvAXiW'.
'WiU7TcGbXLLhmOvbYmhqqT6iJwnGMmRZrgy3TtEqTAHy3a112K'.
'rqXpszMmZnJZ+PTEwZ+MAi8s4/y0gixPxq0B/g/ixOEx8v39Mb'.
'FZyD7QETPTHQEQHMXWd5bQ9UGTNb/YXbtULxp+eeQ8fyIqYw0u'.
'JxImOnKB2ncMrKE2VPhvxbUJLuf8fz7zGovEMZ3GndnIzInJIt'.
'lQne14z60f/IaA1bn3+f7SK+uAuOCziZ/kEyZyE/H0faaD22Xf'.
'Pt1uOqqgTgZ2Uy1WJ2TDWwn65IblMzM5TxFf4fPvLt/gCmYUwZ'.
'nFjOMrR5SoliaxL015sqTJpONOvt63z7yq2G3LQEzHvhIolVlg'.
'fYJLNXmWE1tzZpqazXN9ANp6zRvG1R/FtDpXrPdIhyVSW1KZZr'.
'mU1dyxFCL9CHEsTx7RtSuHVuEbXczWr16/nH//2gAIAQIAAQUC'.
'+Nz1z0P/AJG5iZ+iXPUTlic53JyH0GBDWJ2oaoqYn9TD0QfQQ6'.
'46YhQGGkTsTj9Aj+XE4zj88X+MmD58OmIOnKcvSeohgh+dCD04'.
'9Jghg6n50J3fdXncnOA9B1JhghMECww/OQI0xAvt25xMQYHoPo'.
'A6GH5yojGD+An0AdT85E+3RB/BiGD0n5ysc9B/CRAPSfhsejHX'.
'ExMevExMfAgwmCZ/mMPwJ6D0H4EfDZnKcpn+E/BHoPQeg9GZmZ'.
'6j0Hrj4PHr5TnOUz6D8Eeg/nHxmeuPXmcofoPP/ivHTExHf6GE'.
'PsKPcy2+U1w/QqibLzX9ltviKSVGAfoQdLGyeftXRmBQIT9CrL'.
'2wIPxht9z9DLLcStOR2F/HMU5B+hRNgypOIcZBUzXb2b6G4/lm'.
'Ax0BFP4sfonMZff58P8A1sf/2gAIAQMAAQUC+Nx9GZ+vgJwnGY'.
'+iEHo4zjOE4zH0ETBZO5BZGb0sfoI9czMzA0Fk7sz9An+bMz88'.
'P8YH8uPmmevGcfSPQP4MfNTB6M+kdB8+M7cKThOEI6HriD0E/O'.
'yYJynL35zlGPpHoz0Hzpov8IHoJ6j5yZ9+jfwA9D6R8CD0J+TN'.
'F/jzCfh+Xo5QHoZy68pynKA9CZy6EzlOU5fA464/mHwSz+rdTE'.
'6HoIcwT29eYOjT3+FxMTjMfwj4JOjdTE6HoOnCcZxgOIDGiw9F'.
'6Ezl8VicZiY9A+CXo3XEE5GZijryM5GZMUdGg6ETM5GZgHx+Jj'.
'/xRn5Xnrn4nPTM5RV+ZZ+EJg9zd7CV1S14Pl+PhmMoWXe5rqxG'.
'MY5I+hDPvFGBxj24hYmKPoVpSvT7zt+wg+hWlUdsSlveP7EfQr'.
'SkS18xD75l4i/QpmfbjCsV8Gz3C/RJWKfYfPj/AOtj/9oACAEC'.
'AgY/AuTbW4H0Ym6XhCxthq/pVJDvmvNkMvsS/VyPASwEMKCtSR'.
'CcV+CRvYz5hsnTEicWGmI2GxIjO3sNiPOXRdvRCVSXOPi8X//a'.
'AAgBAwIGPwL2v6ryLV6EuOL+pIjDjLF4v//aAAgBAQEGPwL9gv'.
'Xmi0e1WHxa6fFVsxKTmujThKpcu7+1CDY5d0paVAONnFzbDFe3'.
'jEsAN6IJtNFMcIrjZCNYnsKkeLVsO5Sns8FB8QcRox00UzXyb3'.
'UYeC4jbb7QirJufdMNh4VSQfaG9uWuCqcNfcVRAqIKN7W6Qzfm'.
'7neqIpuJx49aFkAxrEuypYtvcoXd6c3piFyBwwxdy+dcwOPxEC'.
'uB8DhjmuF0WnDKpiNcexYYUxJ2IQNNXhRhlUHYYZlGGGhSMRi9'.
'Cxbe4qLDEHF3FcTAD7qpcwe8O9fDDaTBrqNYQYKB+W2r0wjQvq'.
'QzgrgvGnT+BfH2T3KVmYhB1Esq4rjSwr6rrvI8R71FrmXmaR3d'.
'i+ZcnOuaGfxkvK7DIvluddnF4GC5m3ucQOtfMuSMomuB0MnpU2'.
'/vCWGpEgnMp8Xb3di+ZwnE4bxEbVaY6Ipkg3/HD09yt3ldQVsT'.
'NUfy4nyXAhpr7lCxEr6eoq0195djGvqMvMjhA7l824cMrZqb7P'.
'6gQuC8adPSWea8kO0oWgpOguF8Rln2r5ly12UcJ2SXC+8uf4wo'.
'C9u73I4WVO4P6rsxUPikZHjvUTDIWqlUqUCpN3+K47vDT3qJ4H'.
'VOwki5sHe1RtahdvMXUfl770+UehR8zzafhlPYjeOoIorUuZ59'.
'OpAHlFWVG8fQ0QGGVG9+nd5MNqtXRjijOOtcQbaySUbv4jMoKs'.
'/7E8T271bvD8Q5NwRF+05Kd3cpOLMhwCix4OxUb1xBequEw2KE'.
'bQqiA7xXHczxsl4bVN1n9S4HMvMlBXHdPZlE+xQbfaHeKEWh2Z'.
'Qe0g5fBFzHfMqAMEb92jPj/L7v7YUu4nZhRt7EY0ZP4e9QaTZG'.
'NWjCMK5Zaoq04RFADTrphFQgQwINa+AlHuXy5hskXv8AqPwAUn'.
'cvMB2LhIbYlxSiuWOZQJIz+KnB2juXJqKhaLf1T2qUH4YiuO51'.
'eCk8sOXAKN28OioUZSoXl2HRrHco1rgvTmiR4bFxweMoB2iBUL'.
'JacbHQ2Ogotv5e2N/irNqIpcRhWg1sgPy+8vTQ7gZmx6u1Z592'.
'zomOgTkKvRWse3+aoBSZZjiPdEUK2Hm17U1wBuf0rlUiQgY2oS'.
'gd+GRWX3bXZaFA3ZZlBwOxRF9DI4YVKMA4Y2o2TI0LHnCDjdgt'.
'oEMiiIjIoFw0hcuoqLXFpy/2qUH4aCvmMIGGNRp9mjwRvIQL6M'.
'35eQOe84W79ibdihtOfzd34logdilEYZVJ+vwUmxzIjlJl3oDo'.
'EIiFbXQ8EJ+94LiG5RjDIe8QXARmjucuNkcvoiFQW7cNS4OWjv'.
'TW4hD8vPqfbj+LCARcaXfh9qs4ZFxZ4qSgFFwiEen4QmzCOs5l'.
'xNK4HwyFSEc0+4qYIOFRkpw0hAMHDaH5e55oaIoCl7zbdpoQGL'.
'VpVo0N7VRlz5FxiBxq2DmHYM6qlT3Z1A1dWKylHUESooDLHV1C'.
'Ww0zXC+EZkenvRttjmwIUK8MSmZZZovIFqqzLYrqHrTH5ey5/w'.
'C4eL9Imi81Ubtig2l3McletC7FbkY8jU93lqz1Lioha7lHGcDN'.
'YlGrphjWeWGhZkG10lHXuCknZrOunZ1WARhDPlzqDmA7O1QeCw'.
'5R3RXy3g6dyhTo7kTatC7FOWjv/L7xwoPC39I5jp3qecphjBxn'.
'GERCoGFCLQeU2dNehQEJ0qA5YxRIOhNHkaKdpVEEYThBNANOtS'.
'dRTFE+qEMQ3q1jRySQaJAdBykncOq4ueXO0yFAEaZzNa9bUfFT'.
'ZxZJdqiDD9Q3q013DkMlE0vn3flziOZ3C3SgyqjVzaz2KHrdi4'.
'XEIetSSqJ41iRPTCpSRxlCFZnuQsgxrisjR0uOTwWaXVNpsY1g'.
'wOipWmmINRgTprXGNXiuF5bhpGxXbBxYoZcyDRVL8uDR/i/mNG'.
'pF2gYZSjiEkPwJ9B2diEaOo0Yz2dfiumnMvM3ahB4OeSc7/t4q'.
'OtCM8SkY9InTR1YtMRjHTCM8XRP9qc91DRFD13G0dNGxZqOgu0'.
'fgTQhOtCNdXVOQQ1+HWAVY17oqZ1w9KMWDP4K1W+fd1nQdHLjR'.
'jGBqVsHhpRawwINdfQK+l1ml/Drp2CCuvs7phLYTvMvMTDFHKM'.
'yb9tdGw+8pvDKAopqz1Z1fXRdbZdu4H44xoycMdK+4+5vP8Hy7'.
'm7xmY1SJJ9oJ9/f0vjeuwxmnYn398+y2YuG1R7hRGk6Exjjac0'.
'AE/tIu/XM8wmUXmrAbEGYplZUG4vwZKfVOUx3DryiMMiptZ/7l'.
'QLA5z6JT65gIDF0WLMWUEdRzbPCJZcNCLXiGKCc6/AddtmQcKc'.
'Svfv3MAc+V20UBvdublUL25Y9wrO6vah8NgYPVC/2DdxvKcBRF'.
'Pa+7BY6rvx7E1jGNbDVq8VyCzir1+CLXiGKHQGNblMdyslsGHX'.
'0CyIk40OEe14YtqPAIVeK5Rax+Hig6iNX4AuC7iOoYgc/XHxCA'.
'wiUcemNRVq5LC3EJfyy2BFzosOgjVI6ooOLwQNHaqPxnHIs3Wd'.
'a3Ht3LnhnwKMwY5dyb6zuI7tnXda5ozUFDUVb6X2qY+jYgm/bG'.
'6NzctPzHGuFE4AZgIznQEW3cgBCGT0dMQSG+XFhnVNNKBLxBQt'.
'i0otcDjh0C0QDVFQtC1iTrPNCSslxIy9ORNIco9Y3V0fmVuxf3'.
'diiULi+M/I7cdx601RYfU9sjspT7p73FzZg0gg0ScDQaVNuze2'.
'I2KTHfuz2SOxc4OR0u2C5NSrCkQVR+CBjPZ12n1pwPiuSikjCC'.
'bdBsG0u3z7PwDCYq6QMsul8MaHQ6MupDoyIupVoallhNNKdakI'.
'dQNcIk1ICBga1Lqll2Zed/9Lfaxny51wsGfxKaLtgs+Y9q42A6'.
'EGGionf17ksJa6cxoR+ZGHldhFfNaP3cD2rnN2cTsCFwm1laVT'.
'72BU2g5sCvMNvZ3Lmac6nd6lTDOpEFcvUhiG09eTjhnREBOURh'.
'BG9rdKOQfgGVmdHTRGfSXtGdRFKbkyU4ZIJzXQLT5cW9SU+h0Q'.
'NVGGVVZ4LlEPVwmoLIhKqlBQNBXsrhRozYt+tD4kyPNuQICn1L'.
'DJM8z/6W73VVTosmbqrtuEtK4LN2NZw0L6uxQvIPGSlcPu4Uqy'.
'dB3HLhT1mXXqCJ0p5ZCFni3AZSVQ0u9kz71R789s+1Gy0fuy71'.
'TDI6feptBytVbc6lB+1UFv6SucH9Q9C4rvS1cxbnXA8P29qmwj'.
'MuF0M6Lg20C6kToUxDP1QO1Y8x9KDGxiTBBjaBR+BFR6IKKh0R'.
'6Y9MU67Zdxu2OsuvI1inag6BbGp1OFa+Iy7+IRSKJY/QmXtmza'.
'q2baUy5u7v4t44RhiFCZdEcbm2zkwKgru7hF16YDtJ6Lbp0CAy'.
'yV4wf4zZJy07Oh9zeXPwiwRpjm6bLTwVux5G5MbtWNC5ufqke6'.
'O/ErROdykOmLTA41b/APkb/Ugxx/ScK+3qFxoEyn3xpeZZqtiE'.
'ed/Ef6e/SoGs4o4TUnCGpcQnr7IKTp4vByiQOzwXm7V5Y6juXm'.
'G0bVxBp2d4UrTOzZFSLX9uzuXFdlpxjAFQbexyEz2qbcNChCeT'.
'xhNTd73ijZgBKijDHl6kVQviw4W0af2YuNAmU+9a8G94nOyOce'.
'GI1U0pl2L3iawfEA5rVcTZc3LAQ7EXXl5G+vWmw10LU+EQDYcN'.
'YMFd3fqtAV/FwtNa27YMfndDHAq9D7ycAy7EKYzLYwlA5olON3'.
'fFt5bsi5s+Wo2qzk2L7f8A2LywRdgwh53SLaCQDX2q+a2++Ebu'.
'AZd2Y2sZLqsmRfYtvXWnPebxxyXcTPYri8ebF39w+8vLwwjkaK'.
'zioyL7h95ej4DeSMgBEwmYVa19z9wJh77LTkYIRGePR/r3XIJ3'.
'jv6RppKLzQ0RTrx1LuzrCPK7hciw+WW9uxf67z8wCLco7xXr6X'.
'NFL+Hv2Jl2OSvNWsMNiww7FAuj+rdGvSpUathkpjWN4VYOSfiv'.
'Kdh71xAw1qR1cOxY84jtaqJ+ydxVPvDepGOYx2GK4mg5xDaI9i'.
'kS3bs8F5XbDhoVbVBtmLTmNGOtQeIYumPQ3G6f7K+7+0u2lt3J'.
'zn46YCBFCh9yG2zzBtG2NVKIu7sAOp0UalacxpOMgJn3N9dN+J'.
'5ZVAmz0O+5+GLbQXF2GRXNq7YLx918V7oTnJkDmxq3YFv1oT1o'.
'/cWGuvBAMiN9MqVd3t9dtL3NBMsc64lfb3LGjjtaGgRliiYIsa'.
'BRwCqNSN19yA8udaIpzYBWLttloqCNxccvnduGE1evdIABOs+a'.
'XTA0KXUj67GlXd7dOmJRGNpVh8r4bco3joZiiosdA4woPAvBl7'.
'whbBYdY79ic+7daF2INzmZOOZsjQUWx1riE8mAKpnll3KmI19q'.
'lRklsMlMRzje1Sj+6Y+K5gf1S7VQRmmN4VUcsjrHcq/5vHYp2T'.
'nkqCM0wuFw7EbbJZFFrqKnKD4TERDV0BuJCCDcXWsQkSQ0/pp7'.
'e3pLmmxctpvKz7LM+NNt834d6132t49zrxzrTRKeL0q9Dri8vK'.
'PgOEbLcchIxrX2Vz9w0uDQ83mT1WxGcapSX33y3C6gRd3eOmBG'.
'OyITryoXDLl12XOax3a9+QUxlARgrq7uLhz/ALYkm9u2F0SaBE'.
'zMK8RX3PA4fFg27uzTMwcYVSOIUTkvu3XLTEBl1d47I57OupWw'.
'LxsAaW2QTRxEk0medAOuL3/Zj8y8gTHIBICCAFARt/bvvWtZZb'.
'IwiZ2rQ1JpsOJ+KHm6rsjJSIuq0wTr5zHMDWwsmqqBorjp6Df3'.
'I+Z5m4/Htzq9Y8REpa0WtlZn0y6InoOVG6NDbsNKYwYonT6E37'.
'm+lXdt/qPd0WXCIUWOcw4YUoucPiXfrN3joi061MLEpTClFuZT'.
'dryU0Lib7uAUe0ehRjv2OUpa2+CnMZRHa1Ue6dxUnluR2EFQCM'.
'bPBU+8N8l9M57vukoWoZHyKFmFcSM6AUoau5Nbl60G8xk3Orm7'.
'HkYTr71EmStP4bmoVu/t7UA36F3yjGceYVftN661bdemMdffPp'.
'/2boUyvWj+YZRXjzqHldvrRaaumjohWVE/TuuJ+4a96ffOlbMd'.
'FS/2r4SH0mZvMc9IGvqwNCddjlpbmNWjoipjDOFKSlxYZUQ5sI'.
'0qRgpcQ1qgjN3UIWTTU7FoXEyBxs8IdijGP6hv4SpGXsn+l6oA'.
'OWLNokq4e8NYmowGgwOori/iCtNaBZMHEZaIDu6KlGHKPDqwUf'.
'LdfzHuCeT5Wgb96+JfDh8t3vd3Ky881DW06FJtltTYx1xr2ZP2'.
'0vYPlnnbi9oZPWGlS5/KcYxZ1BwgVNUrhGkqwyZrdUMpOGRf69'.
'0eATv7xBzxBg5Wb3bm1VzkOt+4N/UogVSR+mf8J4tRXDC9GIf8'.
'XTUL26sHJLtkvl3muSxjCuhcbNSECcyIaZV1qbB+7LwULRGRw/'.
'4qZbPzNP8A6TrX1Qcj4b4HauWWNvcUaI0QMsCoNFg1gz1YlUnP'.
'9Yy0dVzygDzHidnPdQnX7pmrEMufKrP2+m8q0Y+xes80uPQ533'.
'LGxZwtsXkdcKDqJxBch1lf61y1oujxTfxe4Z7sq+3ixrpyJfZO'.
'hvm2mqHTaYGk+26yNZ7wg5zOIzPETtrzokXcxRxEbalG8Zdnih'.
'9Xh98SjVC1lyJtUqIx21506uVFG2rOuFrWiPlfbGuYCv4Ma2c7'.
'L7eseXYcnSDeiJFEzuT7o3TbDaIXkXaWUjZpRbdCAM6Y9qH2r2'.
'tNy6Z44O9wTO0ZlyHWV8S6aQ7OVBF7buJHrPLRrPgmXliDqZPJ'.
'Gg19UkfTNI9XKMmPFSFC+b8RtTxzL5V+P0vkue7hnXzr637F2r'.
'N23/XuK/WKD3iAHIz+p/tYhVno69tw52wacoqU3bRvUndilE6E'.
'JQ0Haqtam2OWvYoPbbHtj+ob4qQN27Vt5excD5ZcIL5l1a9pvg'.
'jYpy0xqU8NI3rgdoPgomYGLoC+W8tzKzeAES4oTzy7laLgScpU'.
'dFKawVDqFNZ5W8bt23ovrhp4mV5e6Mirx/L8KAs/z9Lv/wAw3A'.
'b/AJIxjHR5cVWJc1x/F3L4v3LLs/cVPZi017lcR+DGP+SNr92E'.
'velHp/8AKs2IytY8leqpD4Z+3+H5YWqKoSxI/Ed9vY80bUIVxk'.
'rN01jruMwMfb4ZFBpuA0UDi7kfiO+3seaNqEK4yX/jWbNdnH26'.
'1f2fg0/4ox/ejL3ZR6l7A3MfZ+pp8v6oTx9HxbljP9kUPf4V4l'.
'zXH8XcrX3t5ci6MuG1GOTeoiYNBTo2P/d5dk44oK7hZhDycv7v'.
'Wtj6deT+3szLyvRt3Nlow0xXlGQU6ghe3on5GYsrsb/5c8/wYD'.
'mpacq5ImvPiUTd6vELlI1eC+o4Z/SvqYaiuZp1dwXkOrcVyDb4'.
'rlI07iFRZyju7lEgE+sKdVKpUTRjTx5apb+9UnLAhNgacfh0Wg'.
'qGoAiDWzMNg09UoMaZ3hAObwUG/UdJnfoTMTxZOekJwhz83YoG'.
'lhsnR0XvHb4v+38P0nPMV9Lb4PDWXU3CxbOisaM68/uoXbbVp1'.
'HCVzWZ02PibDIZym1yphD+GrMnVSphHZXmR4rU/U+HsEjnChxy'.
'9kqAtAmuyeytWbx4fXENDNgTjfvtC9MWht3DWW001zrXn91E3c'.
'ZYxBBt5aicQin3peSx1DfhQOl9J0ouu4wEpiCN4+NkYhHsXn91'.
'C5uH2H08d3aH8UhnU5nGncVnLZt/w0a1dmMZUws/w1dc39wPl+'.
'ZuLKMnZmUBMr419O+qHq+PZ+Gb26JF63FX4o/NIz0acSnNcTcN'.
'IK5T7o3QXlGcOHeF5Dmd3r6Z0eheYYZwuZ+3dFfU1jvauZrtPo'.
'RgeAyhTgFkpO6YUd/egQK6fRjRUqYrwU+d03dZz3cty3t8Eb45'.
'mDJ4q2KWG0NCstdxQtQyGjDvTxU/iHYUPyF180TPL7OOz+LeMo'.
'4zqq6lKmI6ApcOaIX1Ha4/zKo52b2rlbrI7ZKbDosnuKhxtGVr'.
't0VaJEM8NhgvlU549hQaccZx0agi6RwyoZV8Z4l5Rv65uiINLi'.
'68OP1W9EkH3Yg9u3Irn7huOydOPMR+Xtv/ACkQOerDJ16Ojm6O'.
'GGsjfBOa9pcTRQe2G2KaPhttHG2G1sFwyzE74qd46ycc9yJfxB'.
'kmg/iOZdzu7ycMTvW01oflxuzLEUWOpHRQqOrR0iCF40H4QEid'.
'owqV5mB2RTcjVaPmJP55KV42g7lAqhVrm1rylcqodhrXNrHoXM'.
'FJjTmKmwis1jYjDFQr46FT5UwZPz03102LjzDeqJipV4ZupSVz'.
'HDUpwOcelfSadMN4Rc26e1h9V1rZOS4bQ/VTuTx7JQywH5+PuG'.
'81Du9SVa5ujlaV9MayuK6iM6/+uIZZ9y+U74fvDsJUfiWnZT3w'.
'KcaiIR9EVdt9ofn9411FkoKlUrGuVUEKnqhrnFzWznq/P7zKID'.
'TLejMaVRqn3rm14NVR1qjUYrvC+U63kA111LiGzqOvTXIfn7GV'.
'ud2eMFaVI0juVWg96ojq3QU29vipR0T2KLoHOJowZqMOw7lS4Z'.
'xHcO1cMBrGlRqyGOylMbkidP5+x0PlhtOU+AVGkYFU61NojhmV'.
'BGn/AJRUnEIRhDGvpmBrp12YqUD+947lCJaVz7FaMPhj/oGd2J'.
'rhc5uGVcLg7OIdi5PdO4riiP1N3hSgcx/5d645nKuC80SPaokM'.
'cBXCz3KyxtOEZoNFA/6FnQuIsjnAXDfOb+9HtUvuRDKhbvbuxj'.
'jBQunBzvMYxP5x/9oACAEBAwE/If4HNNc+aXosKouTjcNOZBaj'.
'ahXPDWT5NYPDq0RUeWYyYVtszXmCAHoE+B7SiQDDVe96YwG2sb'.
'cVBHpd+ic7i7KedZklUr0P4yAM197vupnujXqrVkhFi8w2irTV'.
'YpUu3xLA6QA8motrt31/9SKu9jOrS6dUohdMCOSex+CWA+09nE'.
'vXqNT/AGG9rWdHLz+72ronoLll1oKujk74CFg7rbclvs1xE633'.
'B7m2GNFB1Nekxbu22w+ecq8nSX+Bymu6c6/H3QbrLHPZebvP3e'.
'Olq2w4hsiVs9gzezADWPQrbVzRslRpvHxYwCOnX8/JKdWnk9KZ'.
'i7lG2KesENJU3vGw1uOUxwncLPPBi+XKBujch8op0dZULZ0Ms1'.
'eiKTQ1a/t837aLosAZV5AT9Uz9JPbFF+msvx9qb1P7RSUUFqiy'.
'ZVV9GXF33zvp/wBgFHXgeuNRYFmb+AIg2w5af6ZQq3Tava+6JL'.
'oeej6/1hzljbBu6DE2YHb2gRwxt8HzrPrK4yN39UPaKZr/ALWm'.
'T2y7SdGE+R9I42t1wueeR5sAxu8jt1KSRdorx8koMQBLNVvG9j'.
'3Q4qazlOlvWZPiVyO3X7dcZdK/G7ZCVb7H9xTmw9C/moYaHced'.
'P6leUP8Aot8D5g4fTDN4fL5AqbuuwPji2nIF0GzyCVrQLs9sec'.
'Ac3smI2Sqn5+0M7W3v8jK3lMYDk/Bd+0wBObfrRKsspyI74bqY'.
'z1k+REsutV31rDrDF/j0i9b1/wBzGmqFZvX2xOaOmB+o/blfqQ'.
'p6CJtqZebFpIpgZHKkp/cyi4vr6pjEACjQ+3brEhzdnm1MhW2D'.
'o6XByUkC6c/OJovzG3N0Dtzjm0K5tnkawk5qD3a5rBGU8gwwct'.
'qN4G/vZXUp0DMOTyW9xp6S5Q5jQ+6WS7ie5eonQMFCB0NPSKxD'.
'dQwH7c4c5h6nfX9oPQP/AK095jje3/TMlfrf3LVDba/b9sdnbU'.
'vwqMHyjtA2Ym7eZTEDmHlLrmy9BMymGgb6XQj5MtkU6B+S5+oC'.
'96+0whHkU+tJp283/wBJkUvkx66Ocs4q00rXfRwZYd+NG6u7sD'.
'7fmnL+0d8ob4amOgwPQZoLDdUa6ZBHfWZNWArQNWULd49gaGMc'.
'24eZFQe8ndasxtmFbK+rRquVxYcjHQuvODc6pyP0RwlC7324nl'.
'vRNBNVvZOccqrFxyzuMfhnelGXfdG+y60/SOmeah/uYwq6A/sY'.
'Fo+jfyxW6XXD+vtLK8jaIWGtuGrvfNZTrLIs+RZt0dijM6hWa9'.
'2R84cSy2lZ5r5S9FTlHmZhjDje+OH9Jb3Kg9z7ywUHrh3qkWhe'.
'V/a81ys0QwqdB9u0y6Tetdg6+i+c1Dn/AMHlGfWW1PXH7JgKEr'.
'JTm9VVte1xdZJNmuraPNAUxy3Wp5i+5u5kGsrIb4sVBzTL9orB'.
'W5tUW6C6i78tFzVrfR+GN5Gq3Gn4mNEaANmuVWq6t2wS7T+zYW'.
'7v9Si7p4dXFg6RSEnXTauXoMv9w2Op3351rprLy7ctzsvaFFKH'.
'pPvrDXB2X4tWebLydxxMyPT8K9Zmqd6vZsi7QcFP3q9IB03Sh/'.
'qjvozX3isIc6csOsA7Y4dOnq39v6aC76vKNSB+pmGOaR41wqVK'.
'juqvvn5grzbGVc8ViEfOv2jOG9B/r4lLoeaH2aYaoArZpwPOVG'.
'2EOeFQOAsJ7mvMsxchBbWvJryjqRXZu3r/ALGm9QB+RM1ccmX4'.
'9YrHqqU+vsIxppa1Q/uGoriBqlXybtQzdC9GPt/MeIbKzXr6TF'.
'rIb/X1YNQqa/QMy5Xq6fmIbWzV/SBOoe4Xz54i7gZdwwrxCPX8'.
'2l0eXpAySqsPEAal7cmbsxXqmqwtUdO5h9pnLXVPVK9yFbPehe'.
'x+yXzc1X+LvN5o6mf1Me8dAwA86TNOhf2/UzC8tvPSLNRvNNfd'.
'fSGctNvZn+0DRPlZaPXF+sJaTdjsDijNsoli6jTyC8Vif3oc+8'.
'3iKgrDVoudXZiIgU4PlrwDAgsO0JBi8+W3tmanq1+diJAyP9Pk'.
'hLCYrF99b54lqCrfypQ+r6H/AFLOu8ODXEcYHVvk7jAzvpBBdQ'.
'q1WlENpZd/lXwVNcxspp8nK/KPw/QD3a+0RSHds7Gzdli1o1rK'.
'5rh54+34I1k9a96lNJaHsTsJrkJuXvDfQGIwY2R7aHoXLZpMHQ'.
'wf3HQaCh1PwNYsDw+K8uekuPXEc611sjWY0W4au+v7gaAjzafM'.
'QYlMxtzr+5rDGD+or7Dvoe8Dlf4P7mp4abd/c3qNU0Eqqch7HK'.
'FxmF3BxCowNjrXW9WLqo5Z+lfmXwp1PeHkoB8ldekfvNv2LnuS'.
'yVjFzdVP28Wd+R5cvNGZw/D4lqG5tzlljdlypo6n8e8ymWgGIq'.
'iTuL2jiBdF8YAK9olVgD+HVlShJbb9o3B0/wCmsAo1wGjPPrBg'.
'WH7HLylmgbh1wX5RCW6iZzydgj6tVh6GPmFaxudsY7sUVsj2t6'.
'zWrp+XL1z3t/RfDYl44NvF1VVlHoGNEXAQHP8AbXslAqbol96Q'.
'M/GM6KZZDbF2+h08pWFkfJp7M/br0V6zh7FsaC3rdsl6pFaNfg'.
'/Knt4KfELN2szm5b8oPOAiqTAu6cp140ls52e+YCmmWoYhLZbc'.
'W/qUFdBj87Rwm7Em+3eatSoAeqdCjTnEK9gHOv8AYpGd41eeHz'.
'EYmg9mvvGOIanKXki1w7meNxlrmyHdWitGwt1TTonJPxcmrYs3'.
'k86gQoV1hpcLcPchaAAPLH25cjBffGPJT6wKDR9M1YEgzgeWr6'.
'yqamr2M/5A41wqKh3cGVGrpLZyK+CBY4OnbMWzpy4VGe7j2y+Y'.
't5559c8NUm7wUYtLRyvqS06fK6ejcWoN+Wn9RUVWjmOCu2fFaq'.
'Ka2z5m0HshzM/HBAtaDVmFVrUvXtz8vCCI+iWepjXikELZN2Na'.
'Ol8NQBeM8+X8rVqi8v7mWm83XQ8l7wlTZXd/3M3lXO4eWX3lQI'.
'ESb5iO0eSV40MdbEZHb/YPPF+T/ZYLNfA1jgdTHd54tzOs2hYG'.
'6fmUJc0M5UPig8WvQv6i4KWsZFdbSvRmtfLT2Z8/C6RNgsonZv'.
'67zP6Uw+c0bwzmDLpv6ROITQqulGvO8xSAuDQ/qLacALd+vFrw'.
'QLyMvUkFt4XNaHCudwtMkCpHbKO5G4o3qJjLFA29ShQ5dAkrzk'.
'NzOoe1XsRv0cLsVene8xC6A2A81BnUaqOprU0KXOc/yRs6/Lnl'.
'A9nPPA8oxj/RpCrjKwEDlzPff3lQODwCWmW2Wu7SZcvhOWaAhz'.
'LFrd5D+x4LLqNo8oTUsLxDdfhzpBN47qy+DFMCrdBrlXkMQAAY'.
'DAeF0Yk+aa5OnlwIVU9UOuf8jVtYOGnKAV3Kyxi70BfRDU0ytH'.
'Zu85wkwLKovTSupxDug0VWKwGlqIBag0nB2Nb3qaTZZeZovNus'.
'LtFsYas5TfO/XWsJNlGe/sVy1zSZnF4ci1nXKfuE5eyrglCMrR'.
'ypu88uAMkqy2bxVKqubAAEUVo6ro9cQBZEAF0OgrOdM8sz/bs/'.
'h556WebO7fyCYm+zlVea75sFT2Fv425fQ5/qdzcc/GvMPYDg5L'.
'FLgfUpQvPL1Qdi1y/4nDJYqsq9tIa9Z3nUz8TDWVKjAzMEuWzR'.
'ccw+hwlG1javPBOTpQ/b7vFuRmrEgFtowHXAXfdDSdgp7kFels'.
'Jkb9WepMyK910eVPE6M1LdljS966TDtZcBALt34aM1NfI5QIQ4'.
'iuDfbFosLM0MBxgUNgaEO2jgrNuJgisHRWbNNdd0FF8xfMx0vT'.
'9zoqur/NvaIxSwFRU3ve/wmJsdbZ/OcFtou37zoxE9torz3ck0'.
'nbTglGL6YwApLLxW4mleOxi20/FgpXIurOxeu/J+fiNED3hd/i'.
'I4eqUDPemHLAuCaKac2l9pjaTnc86GIHhXIt7IpY2Obj1HqS6Z'.
'qz8syx04VNPAeiajKthW9a3bV8kbW82/Xg6w1uGvaBVwoIU8xn'.
'Zy6zNVSz+xWHpKPs2YujNgZMVFV4nRlZttbPAr1j4aQU5vJ6Vw'.
'0Zruy+Xnv6OOC0YM1TGnOyjzeOguJtfJmKLMtwdSpKqCceytR5'.
'TCpWmnLlXedBWOK3KfnPhrcEwbdy5v6iB4DPZenK8LKHo8NUY4'.
'HbZT+j7JtG5otea2LCyDq0NNlbYyQGurYPqUw2CjGQ9lz5O/jb'.
'aU201lqU1cQU7dK6WWui8ax+q5txr4PlF6vRDy/sI7S/zIWFRG'.
'lVyF+/8AaWvmGHxWGxRz0/Jh0BvLB96g1ad3/wAJdo3yP7hX6A'.
'zmj2z8Rjp0YaBM8G2ysPV9mJ4DgMCgbOTtViDRiYFOeg5d45Db'.
'LX/R8boxatlu3p5cDQjTpFvLXP64aMbXDmnzNPfN/wBlYBDd+z'.
'lAxQdnqHk6sq3Do6SFvKIDHY+DnDSKD0s+WnoR4Ngpp736otTe'.
'G0lRba+7+sac57qBTWFJLWDyf7CDz5E16f8AIXehMVx1Dq80JQ'.
'JxSmuyivKaTSAnf4Ej0YLV5r+TEBvB4qWd9oMNNnj3GKrXfJXx'.
'DIPMCvZ/UtjvmtfT9JQi2P5ns5+JT2Tzz/IRwlCyzptE2N8rg6'.
'W8tFtzaw41lJFnQ08oGcKtq7en9iEwv8Cn+0LvPSn0zMjd/T+y'.
'4ppFy/FzqPmh7Snjoc3r/aWhfnn9/wBx0LXK3yWe8zpvJT91Te'.
'Rz/pj4le+w/viJPodFgaP6iVMuivmaH804VNpo9z5MekVoHQ37'.
'r7wrINs58zlCco6H5z8bA4ABpL7mhPAsbEvhOtOpMggBiJZHEL'.
'wUxQcg67KI+c9iqgoWbWBCy9tXDU9VVpzRH7Sud7ozi6GyWDpV'.
'NUatN3FqRDowDICjXe0mJQQ97HVtRY6GIq4yuFhqKgB5svR1xo'.
'0sHouEecqVh3jhgDJmx4u8bZa9VBYQGFXYE5Rclirm1Xet2Ov1'.
'nP8Ak6sy19obqH4vnLErGP0DU6+sYbJdXfo/Hk8CI0dnQzORXn'.
'IYMWcJjN6/oZQRVRYYAXXCKXlglafsLX25e03qOevlYT3+vfsT'.
'LoG+F+pcW9KHkf79os6r8Wsa4Q6/k8pbYelv3gfuzPq+YmD6e4'.
'+Bi/ajXP8AEeYmzj0a/SCNbuf4mWrwvOv+raFYB5D9j9xkFDA1'.
'ai31hWeG9SzpeK9f+Qxr30p/pgpi3+7oa0KL/jaS6p0MvxD8Ep'.
'oqxyWdjRBC7C3XUHGASsdUM1a2C3+k62T3AF9ZTEaSCM03kLqC'.
'Z8rVGJsM5bzEQESGUBhENiU83JCSySAhg1UC39kb68azmgTVuj'.
'vaJkqpRyaS3f2XhGpbogv6imQBiVWg3lRY6bnCDoJcLH3aPcN3'.
'k0PQxNbfLsbf1Fgzq9OTsGJ2bY4msukz/wDGOC+zNb2v0ankjM'.
'cYER9f4nE/NKW6OfaZgmOvoz7MEo6lmw8ttS9Izbl6/wDG68mK'.
'LErjKzuti9IVcg6r9x+UNsD1+K8ITR3lq/jtBth6L+6rRaV3bD'.
'3t9470B3K+RFpXuL9j1gXp/L5TF3NH8t57w207b2kV4I/JgQFV'.
'/wAhp+09Eu0C12dPU9ciQgNhnCCHQULl17TsedJhm5CJVhp/Op'.
'L6dNvwlz6j4PY8LFeASUlkPpPI8fcs6EnOsJqLS9i2VqeZKjmJ'.
'DXVa7u2TlGm/bTjTKLiXYlbOjNRXNF54IPDBzgy8h5pcCAeAVS'.
'WlrNm58Ej4L94lLuCcrjoC8H0mJrYXRgaTOlzeCzG9Ma0Hk16X'.
'K4xApSsdAGtJl7M3CqDfMpe6GQaLS6+cRso4HV57wG7/ANLBQL'.
'7rjzqAMrKhOS5+I6H04AeDdKTrliFJcVs/7Eo5k76fuafA8l8g'.
'uBnB56T3uBpmizLpyLeXeN+qC+sb6YrpOzBNPsfE/FGtywQUnV'.
'TWgBexekgWuLetz0RE7SrlObM/Lnc2mee8mQXdhgeWD6MA28qf'.
'mxmYhs/5/tA5R6B9Meyac09jb2ykvu37D8UuyX6H+zGK7PZB6P'.
'wghgHSv3mgD1/F+oV9Cb9jXxLKAuqcs5vlW1QsELBYa5oclQ5m'.
'ZheL9DZNrgxbKf3rZNig0VWfMphDaAPTHgoIG6ZUHOS0a9MIEa'.
'wG8Qb30ch1ViHtrZzqDkF5hNH0nOTWKsVaqoDohPxDZaFqVleT'.
'iL0Y1aDl2MiGt5QKRRcLa6RzUhi3eEObTZq1kiCYOVKRTVjYNk'.
'FMleqFkwRjXJlDHEuiUS3NfyRddFmpKFvs2PZs8tbbqzWA7Yoh'.
'pUAB0MBBx3+BsvKhcgyixe/TUhF0uDqgCzaZm6yq9nClgOh5bO'.
'khSfU7dEdyrg4YqDtj9zfwac0sZthFjvSUgUM2x8HwS2yxDCPR'.
'20itrkzqqq2oCCo5F5lPtwOQLqMVVHzPTDHNRG15Vh10lTG/t6'.
'aytDex+3/JevzTT2it7e0p1dLX2nzYp7aR43maqaG7NDVAuDsX'.
'mr9hXsyoHPae154ixGnRD8vowNVeelf3POOZX+f1INAev8TMo6'.
'I0e5b1nqgVPow9oKqbdLvZ/aO5uWl6Z1xiYnbfOus+cTDICZeq'.
'Q5FXUF7C/wDsuaWd8H4HtG6dv5fbwi0asq1dWD0hL9YeqBfMMU'.
'VMaxgu7J1HcXrFA4tBoGMPInE/kCZdqRVFoNW11uJJpHNjGA7g'.
'2QG2sNTl+jDNdxe20CCg6hoSkjlHQ3YInvG2PcaekN6gDsMW5F'.
'Zj7QQnLR31+EgELWEfhJrE0Llk+Q4BFsG1/wB6eTFa9erPk95k'.
'Wr19sPvLy2Dl/lH0lgOlsvpmX6q6/wCSk3EE6L1QvD5Rd2LRYL'.
'0ymB7ZmnymuvnKXnF/Z+6agVXV2H9DnLrJ5k/DrLCxuCE96Su8'.
'+qpMoWXZ+KZeHkLvfOPOAarA1wpbkG7YqXdA5X0lXX4/shbczZ'.
'ktw/fgdEspZfvS/o97zjz4L89J5wcpDL+0aVlitR6U0OsZt1Me'.
'Zbm9VCl/magMg+ofUPbmDoa52tq0XOHs/PbrNkp5mkGdHSWiZv'.
'wx/c1y+toegCOZJHd5XsbByiHdp4Xp+/eeMRfZwFYmgRpnqDTz'.
'NvKUdJto+eFEp21gmeg1CXrW+29nwVB2hyMn7Peat39T0X7pmN'.
'goeU0MYjc9vZnymU1cEA18espcbmtfpmNhbpYPXL1hQTbKHqFD'.
'2TAius9GIEuwNHx5WnozOiGj1fg0YraBW8W5to6SjrfviYqAop'.
'y/0vgvpXyGn6PNjf7TR9FeSNbY2HZA1bK7Im5tl0du/rGqzzIX'.
'z5SqazLapyRrWgwLr2FPxv8AcEC1bLpyl1bKwc0tiRkZZN4tvj'.
'jGS4A0VPYL6QfjTQGty1cpfmjVkrCazS7NOsqHgAww3UtzMBo1'.
'gMKTGg8nq1gsqLLJo40TkeyLC1cXXXJHQY1pOZUtuu92Ta4mrl'.
'qgeyrzgzgWl2Kqcl3nCGDOsKy6Wa8pcfm7b24oIrqc4/G/3MVD'.
'q7sOuFp84DLRw7e5k7kA97LCrV6sMfimuWuFKdvCIHuBu9veCT'.
'0bUu/PuekzpHuvXp2hvFzfPSb9u5759iFCR1fJvb1bhak5df3b'.
'voHBWKLlUiuGqiVcsap/DklLL5X+GLFdDX7NR6M+DS81SecM48'.
'x/aoMbBoE9CrmWvIR9AYUTqDDyFrzIvXnbY+5aPOOzj1Per3uV'.
'0WlUcLZGBDnLgN3otD6Z7Iq9foxgbkXDv+d4/QCY3uRr00imds'.
'mpepQ2bVCDUM5vWOBZF0X16VfvMIgR+338Ot0/YdPnw1wqNWHJ'.
'rrQzquqt26E7ZiS89IOxMcjsdLprDbzcG2K0wN7LSNNLhZMcit'.
'RqxGuz1eP4QhWF21gxDU5TsKVsgWwP7gUpNbjQOsDHXmi8mBs1'.
'xlA2A5CA0h/cClJrc5lvL6ruuzkiyi6A1vUHm5ngdTA6B5Lu92'.
'XocE0tArg3bKoODYdjM8iomfhDTgrDInMdxizZMaF1cvTDNeun'.
'l6qOavnnn4nZNNJnvoz2TW8PK0/eYSASm+Y6jdUcipWbs0W6GR'.
'ekpBDPs9r2Nn0S2xDshpn2Y9ADmStyu9OZM3cbqz2agP4noxZS'.
'5yp+oqWPu3+x95n9w/dJZy/ByY8vkP6sGYqOwq9WRdWNkPlWIK'.
'menycF7xSj149bmiSmX5Nw8/IcsjkZXVQIp6TzNXGLSWxS8dcK'.
'j+oLuwgPvM+U7le9Td1GuWx1eFQYwDZuNV3oKsfoLfPYIAJqF9'.
'U9VEldwWw3ssvNqCNSey+J6JFG07DnoF2/unHZpbUrObb0VLpe'.
'pMokq1Hm5o66c5kcadAdm7mQaqtkczjW2/0R2rwXI41rmvqiux'.
'jp9oI5lEWUsq7Xen1IibECanS+hyl7AtBgOzWX1qDi+BUq+u4G'.
'TDpepMT103ei8PkzHwbLxy108rgwqoaGlUYyv8TP0LXei8Pkzn'.
'oixZ0xb1cTpepLsrs7ANqsnJQgioM0q+tZq5e+R5vd2r54ERTK'.
'eb66fKY5eKhKdIyynJyaiuTAH6IaBb1T9Gur9MaBbpIE1EOTRh'.
'EYRiy25C9XP2g7I87+mJ3+yfE2LWb5O+uVcoTzbeS/GUOUdaPu'.
'ftL/ALYh0LyXynkfpSH94PbUS7N2sNK0WzdCarRoZ/oXnyiwbO'.
'er2MM0JRVb5AeZa4gz2RP0f0wXGv45LH/Cmh5eB0lhLCVqOTqf'.
'SygOHsv76mUOjPcSqho5m4+PwTkiHe/al/YYQSksdSZ6dYdNg7'.
'uuhj6uCqWH48mXwrKPz0qG2/X+7lmh3p7lQ8ydT+Nqf4H7RNee'.
'zfphu/t+/jbPviAVeTAZ12pboXtrXqq+mZsycwBXmNxHmhLqYp'.
'rBtIFXTnPQGm8u5CkpeenWvOCzhzVv0cjbwunBKs8rddDzKBf+'.
'wVKxNYJY09nRuuYk0zcXlpTyaMCaPtrhakOnVkdRiyD5/ntOoJ'.
'jc/PKY5zvhV5H52lOp2f7uFdEd7/U1Cj3p+S/ec8ORKKNxVE9+'.
'TCAYzLg0bW0Coajal7e86USgp2PM1be8ylKgxblU3raUBRp4rZ'.
'W4CBC+xDy4C5JEHSlQ9jWszR9tdm2vLdmcrOcY6J0dYLo+0N/0'.
'yu6Pebfzf7h2+X+NQDn8P9M6AfU/uYNXlT8M7q6InzA2SAXKbK'.
'Rr3JvKXWao97i5KaR2oPS6leQD2I1tT136aWUhX7ejd653X3lg'.
'Iph4PNj3/uA/w/5Evf8ADzhl+j/yUhodswDVH46xsH73/sa6jY'.
'nxTHBWMl4ud6KM5jQ2TEGc4NOcF7CvYX+pdZYw9KnS8/2/fR10'.
'A6ryG/Ul4CEpSD6KM2L0X+03R6iRXIfP+4FbJ2/ypY/D+4bY6J'.
'F2PJH9QTt17frgMh1JX64PexKMXaLMA7ui7XNmNl+2e8Wr/qT9'.
'QKPvzSqCV7jou27AbPWoFrT3gn+xObT3CG55T+qmHc5nwXGvIr'.
'nyMV06Jf6QtL9B6WDiWv8AV7BoUfcigwjCp160qyvOqJgzeTl9'.
'/wBV57BY+STOnacik5MjuHdK9Q9v8lv4Pzzgox5ESVj+n9ayzn'.
'XeBC9GPzpDdE1yyFHnv9/v+697HFJyXpoo6xasa3X8OSCUw5U/'.
'7gkrN1Hvk95aWLyPZrFKVO4fIEL1WAvnMBOWNIDhSuaPkn5zH8'.
'VMTD+ecY11+UZfevv9YNC10N/KGrXf/PzEoDa9If2lWnqfixDO'.
'+T+yBDRfJ80mqx8qexT7SlR5YPUuKVfVlfaiFaByI+qsavLTS7'.
'1IX6TbHUPuRz69Rl9/CN1G4uzfLBmWHNzB9mL1AHkK/r4mXoDH'.
'xZg256fpRKX1A/ZUNNRuujdB/uFsOlT1C885Q9MA9kPlFn8vv/'.
'P3OoHUP+wfefLSXWxof+AoZZNjVWPiL9Iuz0gGelfM/rMue/jm'.
'/OdiCQme56D4g6vudR2c+0vCh3dPuuZephWe2frKuqKmloN6W0'.
'7wY6Cg/wDC4dPNf+xTF7p7JEmE8jpiCsnbU/0iwzujW7YFhjyC'.
'HmUtH3j/2gAIAQIDAT8h/g1K4XLl8Klcb4MQWHNKII/erlzEqU'.
'y+Fy/BXC+G33uvPjXC/vShwAy/p+8r6Nfc7scp+iDUToR0tYGN'.
'Mp5cIb/8FfHRxi1RH7pSWSBvO8fLQn7ih3l/UuX9zIMcK4KjOw'.
'RBO6WlSpUr76fpsU4ipT4qmZf3Mb/SvhGXmXDMfoX9yErHee+W'.
'mYZSniMqiao4vEuX90PtEx59JodWJEPea+0JfFzVNPDqyvoa+5'.
'ENEUKFpbpObaAueYQcdWGbwPbgfP6YQy+Fy/uMqO8TAw6R2XDk'.
'0bmEdZXFillwmr9EKlSvudm7Q2+O+AzViSvwX7y0cNzx1GFDKq'.
'HF/QqV9BOAfZjntNjho+jbKI8CP8PXhJXA4HwhOAROAfxBTLGU'.
'uVl/QeLCL+GX4BxHBhUZmZ8JweCmP4ly/CGVhxeBHh84/wAYPC'.
'PhCR4GHgB/DHjCwcIrKwYxY/b+ODGUSovGiVKI8TwGVKOC/QqV'.
'9QiyUS0rw3LwhX9C/vfqlzEr/wAZf1DxVwqVK/8AAL4iBFrcr8'.
'enATS6h+W6Smuv/gHwiVsj6gjEq5kVH/hwlB1TWQzyOc6Uc9I2'.
'RZ8vz3i+/LL8Il0TJ7bS9Dgh8sCCcfea/onhY014C3NPx/pMUf'.
'h/aaI/zz7OAL7lTtD8fnKbL8fjBYfJwg/+APFvoZfx/U746y0O'.
'kBl0v1Eurv8A+AA8QzOz8NeK0KXRfe143D6Fy5cJsA0f9Rf/AB'.
'S/v5/4W+B/+K7l8T/xFf8AhK4XL8K//A//2gAIAQMDAT8h/g3F'.
'4VK43L4XKJ0Mb7xlS2DD73UzLZfGpXG+KuJ95v6VzHhPuCMtFf'.
'xl/cmUHfi0awtlwRKY4LxpxeFfQqV91EIQEt7TCdIMPfhdK+pR'.
'4H7g8V+AqbxeCnG5cxK8Vy/uZFK4P0CAy+E8a5iJwfuL2+gcA4'.
'DwrgeIZRxP3Bfab9p7YzEeA8DwLjwMPDXBXC4/byL7wE5OsG3p'.
'LxBfQuae/CuIjwM2lw8J90Y7YBDnMmO05Yr0iXEz0I46T9w4DA'.
'i8Eh4QlfdA2y0dSHTmAx0l7Ll8RKxGaR4h4Rly/uSgxFr6Zfgh'.
'9J8V+C/svwmrguJ4L4BFLuPEfQv6DCCXDTtwPsix1Yd+Dr9CoR'.
'ZDgwh47+i8DZtxI1ekv4KiXg2eH38K2FnhV4ON/wABgKjwV4r4'.
'HEjBDxEr6LDjpEYGNjgTXPZw08NEydJrzVTyTHSFbcGt6hV9Y6'.
'cBxzlEHLSBb+BfgqU41o8SHA+jPpPFp4E18Wnhp8C07cJZNc1T'.
'Rw0cKZW/4TL8VSvDfhp/iHj0c+BpE6xJpPwqLlhK4fhU/wCk/w'.
'CUsgVNc18CCDOAUn7g67/wSHGpmXL8dOAPt5/Er/we/wDGOD4r'.
'4XwX/MPobR8BjiuEX/CPCxhEL58LG/hF5/mH0No+KpUqVwvgv+'.
'JaSYB+fmJlm4zZafZbH6NcFcbgy/4NuwoBAydYAZxCwYqD7GY7'.
'xfqsqVxDjUqVxqVHwOVaYyUu4GjLGc/Y0JpF+viV9C5f0djexw'.
'XI3IZL0TX9jX9I/lUrzJRuWrrK94Iv7dfDH0b8b4leZYraVQBm'.
'W+cf3S5cx9Al+IRrU1eIbJDnIPvz9CpUTgU2aQfftX1K+/v/AI'.
'd/8VUz0l/+Nf8Aw9y5cuP/AIepXF/8Rcv/AMFcuY4VK/8AF5ly'.
'5cfvH//aAAwDAQMCEQMRAAAQAAAAAAAAjusNUeH7QgAAAAAAAA'.
'AAAAAAAAAAAAAAAAAAAgcAFr2xm5mYAAAAAAAAAAAAAAAAAAAA'.
'AEAAAAEOgpUf4k0YcAAAAAAAAAAAAAAAAAAAAAAc3bMgHbq7DG'.
'h2RkAAAAAAAAAAAAAAAAAAAAAAFxHmLgZsn5E6qPQAAAAAAAAA'.
'AAAAAAAAAAAAAHSrsJBISRZPq0XMAAAAAAAAAAAAAAAAAAAAAA'.
'AeqqlZv49XwF25XgAAAAAAAAAAAAAAAAAAAAAA1UH6qR7puQ5a'.
'hFgAAAAAAAAAAAAAAAAAAAAAAH83Gsu7TuQWM7z0AAAAAAAAAA'.
'AAAAAAAAAAAAElPPXf3eF461kTWAAAAAAAAAAAAAAAAAAAAAAA'.
'yfgTh5xU5mSWe7QAAAAAAAAAAAAAAAAAAAAAAhViLA/tzJoOBZ'.
'qfAAAAAAAAAAAAAAAAAAAAAADUxtBm6/yN2SELzAAAAAAAAAAA'.
'AAAAAAAAAAAA7n19AT43cwywgSMAANAMAAgAEAAAAAAAAAAAAR'.
'GpCwSdcO+W2G2gAAGyowAEkEAAAAAAAAAAAAH+SctjL7wGj+2H'.
'DAAEUUqEgfKeErny7AAACAAAp2MEfXgwjrzikHYAAmCIdMhAms'.
'EHVR4AAmFgAEJhio12SF4OiQvMAAE8ADmCFvH+sg0wgAkEkgAA'.
'lTSGQIlYK0kHHAAAm8JUa2l1Ez5Q+sAhudcAAH8gR7GUesfBAO'.
'gAAAkAEBBkEMEpkIMAnM25IAEysS12VI0GcE10AAAlsAAAAAEE'.
'AgEgEm2AjydA3AeTUiwVwhVhKAEc7y4AAAAkggAAgAh489iFUD'.
'u7i1rSlbCjZO4AxCo7AAAAEEAgEEkknli1V94nRfaD+aEkY4IA'.
'Agzh94AAAAgkkkEgAEZmafaUAgBXrdMa8ggSXAHFelEgAEkkgA'.
'EAgkAE7h5ycAAACy9KBzxCWdAAa1jmAgEEkAgAgAAggEWgm0AA'.
'AGUO5jZhjZf1Aj5FNAkAkkkkAAgEgAAEl4UAAAEHjttfCULeGg'.
'Eny+QEAAgAkgAAgkgAAEEkAAAAn77orQit1EYAPUZwoEEEEkkE'.
'gkEEgAAAkAAAAAjJa4VJNGM0ADts1HAAAAAAAAAAAAAAAAAAAA'.
'AAZhpqfx9/sAAlmTD4AAAAAAAAAAAAAAAAAAAAAA9tJZkj7PAA'.
'AkksEAAAAAAAAAAAAAAAAAAAAAAnJBWHIjrgAAAAEkAAAAAAAA'.
'AAAAAAAAAAAAAAAh4rVYL9YAAAAAAAAAAAAAAAAAAAAAAAAAAA'.
'AAAktpOxJuAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAzmPcc6IAA'.
'AAAAAAAAAAAAAAAAAAAAAAAAAAAAH+Rpb+9AAAAAAAAAAAAAAA'.
'AAAAAAAAAAAAAAAAdFlSvBoAAAAAAAAAAAAAAAAAAAAAAAAAAA'.
'AAAE9WTzK0AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAnBMivvAAA'.
'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAk0MDqAAAAAAAAAAAAAAAA'.
'AAAAAAAAAAAH//2gAIAQEDAT8Q/gA1UOpQrtCSZQAzYOA7BEKt'.
'EwoDBaalOTOcZC+4TjsGCRcl61lN6Q4obQ528/AjJAUuvIMFUR'.
'A7ankwhajZdS+dBOrfEW87pnhXpTUYSQUmxqEFjBuCZWsjNrXu'.
'MMRlLXGtUiZ47K5YKSwF4hOfdb4evPVb9AgKWwKVrBQ2pG1Pu+'.
'7w1Mfzv1gsFOCwdZByn2y13BphNjWuigG60JgJlwuvZBWNhJb4'.
'BCSt6knygxJRtEjKmJGwnC7BSRkoKANLJlpsXFDXaCIBD4ONC8'.
'jzmUjWVsNXOomZawuoBz1VAqEKm+juQRkDdkbVDuiNGkdGNpeL'.
'wCukkFpI4LS1sEqko0qYaJRZBsg6r9tCwPqZOLjag3pSkhI3eh'.
'8ogCWNnjFLYHeSZ9iyw3CztLMeHo3uyOHSwkoSBjDMHihyoyyG'.
'3sr1Ql8sl0PMsqG1KEDoCyAwjBlarXSQ2kbVGR3K8HratSB5UF'.
'81FFG7QINIeGKHXd4kNBEgBvJ1aQYXDTgF6oMDggDXWrKzVRYV'.
'WTlPoMcbizaJv8ghDugD7cZ6FoabaXEOWx8H2QAbXRPbD9tKop'.
't1QmSoPFpDyQOAn5HZzZB13J6xUlnoXfdcvgBg3V0IjcbY6xBb'.
'DApY7Gf6QzPjCpcqEt4BbfWMCypXXpzBeUTNknfNr6v0F0QqAC'.
'XSyY0sQyw0wBoNGDPhAsC3XrPImdLtJMOmWW3moqXcLywPONaD'.
'1eZJ6DPIpQNJS9pgBhum0LtqBLOc8U6uENhQUdjH27OZanJigO'.
'hqoiySdbhaDiAjNtA7bFjFAnzptrN5r6yCRKu70GuOxde1NkgP'.
'RNKAKWEjkny6mzoTMsFQtsa0TSkAMgAxqEUByUFCV9MSoFSsuT'.
'JXkwZFZQVJTelT2gCIebuGtqqSZBpyIyUCLjKZbKnrFGVCWaQe'.
'ainTW0wuGlBVoec6NYB+hrIGjMBvThxy9FFixBxioFK2pX2qYo'.
'DO0w/PMrXqKpos6O6EoZGU2CSXUBbCoDW1+3hMWCmwyeQSRig2'.
'OUakMaoIZzJEgK9VLnRFBSIhUhtKTNkZ4WXFQA2csygDnEmfML'.
'oXQDC2C19wGi53QD8fuZLVAssIENHIJpzeTPOhaTGhIxjQ0x0B'.
'frmYhoHpdoHesQIAcKD6WNlW9B9UOVd9RdXdBMAWgFw6kCighB'.
'U1GGDJZqVXyDAkj4dMvBdMm5p1BDJOYhgBM+dLWm9YQFQwwr0+'.
'ckJEaAQ6ManykzSqDsnAzJiD2D7coFKFq7VEPZLaZdUFaEpLht'.
'cAF7BdSz3h1mpkAWm+ZrGsTayIW4QiUjGWaN9kUIb8jaGIKtVa'.
'+5IUIoDDzCgGWpUioOyPPogCxW7/AGkXqtPIMEP4IkhGxBC4yt'.
'GkCrmkKWI7llysLEQ5JtoOVVtx/AIlg4dysGUBcuK73EEAxWMH'.
'3BDj0IdOFlSG2UrNi3QQtgOHZa7hAbVJqNXKE4aBsOTcYohXRb'.
'oAqtjCHGjAALxpFAJGpEba5cstCBdsn2+hYZq2tmMNiqjtAHox'.
'0lAMTm9Y4MSh1844QEumoaWGGmkL0mGUbl2AOcGAAYBQTlWldR'.
'i65LDbAuGj6qG1nhIRgBCiq4dTClbutrcS3A3mVQwsMsyPow4A'.
'1a1pibVHQwrTprRV82NhEGNeDWhhN0oZFGN+QNiLJ8qKUqTZ1u'.
'wQixoAdh9vNmGRNDEpdDVhbzbvjrWq0RNY3h6+copjXrDRiYiN'.
'QXqQLwREKqHYGnIa81d4pPFMOxpCo9Cw0MsJWfIuTNb+0dIpMq'.
'yjuASurpC0ZSV5U/uOI6WWRUbrcWwtMy8ssGqpFBlhDnHDrziy'.
'j7TwavUW2KMpOgw3mFHQ42wXiSPRyoOSqppb7eJD664Uv1IZ+D'.
'DugSPX8K7QC2odYKAWtckosacBGtc22KkkFBWZkuuBBJqNYBVw'.
'uGR5icIARb0WFuxI60wDzNpjYlUuWMWga9IbzXk76bwcQwxWoO'.
'PZjEUjvOXYZYKxrqL2jyIVuHqtEXEEEtjOi2sZzkq2+blmXPJr'.
'4hSleflGAIx1lGkIMB1EIAR6BQZSq4VM2wzKalGq94AxycHcc4'.
'nehKOtStSO5M4GQvsn7etVyiXoNrAAdsI7BhpGWgGIAMqEgEM7'.
'sAma3WwJQ6bzqBCIBTcVOV17sBK0bOuBb5dOqGsZkC00lmR1ea'.
'PRho0uXLH8EvlBHNxNvlORDQRgQw18YvXSipwK9CFAWF2a24y7'.
'zs/A+UBBVmXsQQ2Ba3EwJkNdD9wlnJXrAuhrmu7Kiq2TZ0EAsK'.
'W0a+2CiaqKLiumlJW85uzOs8YoKmyEOtCowILb5Jofbl1EPXL0'.
'DBUSj0ot0SyEwxQS11CRo1yHMhyVDEsCFnWOcP34da3XhgfErY'.
'jYORBcCVTWbEDxxDTbQbyoGmXJSp8kybPFQOFiFe2wi7S6Qiio'.
'BLDN5KiwM3ZqILFeSsdBCmKYr7SOLgqG7oyKhZisBeV5XpK/zc'.
'O6IHTXSCHWtvzhZI6uPiWXXPTyhGhlqflbVkOhiGqUaiGyVvnG'.
'I96mE6pswXHrdFje9BKUavS/bsxgOg1dYTBqty9ZvTCswau0Bl'.
'U2gZ6KXpbgLmFCqFQpz5xt7ZClZR5SiNFh00IHdLiGujsftEMI'.
'o6wmdpgcVEuoKoUyWsOsEJUr7QGl05BU0Y5FB5glCVE3CGhCBW'.
'rzW1sVrzlvNNC3vQQxBgehfuS1NYP7hA9CjvChOB+oGytM/uAV'.
'P4+ixKNj4FBVoM1Me3AFDNWCwNE09EtsNVzCKR0EA8o40wPtyl'.
'CnDkhATBZcms8RDAiGAqWnzEJvCqYaHOYPeODowtXaAYJU6RrL'.
'JqFU64jC1V8YpT9kEWSLqU6vaMbU3A0rWawo51lIAozk290Vrw'.
'q0FfK/iWg82YW5HzKwt50lDVVfflKRABgCUdWMIBYsgesaaU1R'.
'h3wg1nfbN718SCrHZCl+ZHLMKg9eBiYVo0BEMhwqUnnuV8Votd'.
'NZol9xqaauXEFQahpFLakBHRusMpG1AtVrQXNf5LMV5ArqKMAZ'.
'2NlSEmcmfNY+UGeZXMLXr0dMkgy5TAxsEWx0JRTZG90fQUSxra'.
'lQnqzuaEO6wo5jRcqrxobS5Tka94VW+mIVJtftGgwreJKzWXq7'.
'17syQ2oIDaN32jYM5cwhdoKsMGWQBOqLPqHMcAbYYh5yUobpMA'.
'9JcsaHPqp4Vqk8oPgpJLFs6SHtAVjVXvZIBG5rgNxi5rlgyYsV'.
'Ijfc2F5abQzRb5IAt34tIi7hsLiKLPtuGHge4dLLE4YQ87UUAs'.
'SE3Q2S+E9w5UJ232b33pIehJIahdol71G9q1lnJ/Jbj4XIINzh'.
'ZrRgVi/7GEiqWDmlwQWFUHyZFlmB6wKaN9YGsa6xFPeaXSFmk5'.
'YpxEG6YDtrLSyCi4UHNbmWc2iEAS3l0FeusoxbJpuaMHbbEoHa'.
'34xB0Gv7YlV3wEDZ2f3FXAwNNuDWGNRrCZwc+FqGrIKQvuYK7n'.
'SLCZdggBQBoHh9sxIC3eotpRjSWzdpuw1UNEJILHBdobZxw9h+'.
'oIa30mwZ5RQljPFLrOgCxiOQXbsVVtAxo40iqvklKC1iVFgp60'.
'C+SxHsze8hlD2hGr3bGgYZ3BAvhqCk6CgOr0nYGswLeM8UvVNy'.
'BEshQMdHE5HQQQ/awupqJAFLGIvHYYmw2UnT4jdkvrhCRSjShN'.
'nPJaYyW4jYgAvIRDo7/QDwApvmuS3S+XiSDGPgwcpgFCRBc01Y'.
'qympXkihAO29FGG8yItKjPYQpNEesaCy+GLYgKuBZL9p6EHetg'.
'e9ggETZR3hs9M3AaGaCJf0WOsVLmgHuSW9dcW9o8Acqe8Mhs5f'.
'KJaDW8QG3lmVxWYMo1RATaK6aMiJaauzoSxhDDVaWqb8UHtmaU'.
'ZACrOhCFgBqS0sNQ0b6xOlhMsVd8PPV+orxWKUVyIrYLUlqDEK'.
'ViaCJ6IsJY4AkVLA+JqxzYFGulHUrSpoVbgtKKXTXomlciN0F6'.
'yYBNYGts1GAdQiW4MBluXDceQDReOtRVa4Vqmt0BPjMMV5yQYB'.
'Xo3VMy3l1a1j2tlWmjzgIoljWOhEIzv4gxJYsnOYI0oVbVXm84'.
'fVDcjcPFAwFMgBz5zN0ORnyE7wYAlVK0IJAHlo5snJJQ5YxCGi'.
'0HMrE4iTMWnvI9o2nk2aiDh88HtANIrqU+8DASiUwVlssODsWx'.
'kczHrmYIvuv7jQo0fKuG5TV1Hqluga1f7mspjXznpivWVy5F84'.
'SW39EYeYMOvVPyg8AQrGbGDlSRTQEIgAAAGAOXi9swAMFqkFpZ'.
'WnYlfNAiealZwJqcHbcv1ALe9eoa9YPy9ye3IwHyBF3Y2uBrd3'.
'5nRw6RgYjQG3WYWGxzhqWpADbWdJhIBLQl3mX+pXRBuqZOki6Z'.
'O9BXrd5XPSJvVb45Vngp52ZRyhqQUpKDVjAo6BACjrAg+kMeFW'.
'j3eRccJuKM0DnQlqMFlNAtVKrzp7vogHUmAFAeeg3XxnbkClq0'.
'WwKECLOtIOJFOFSywvDUWI7gCTPqkpUgvPHLoqYsTt/wANIWqj'.
'Ci4UNjRIofXBZ7DfoTxkeS9GD5k9PYVLKr5UPoktPokWi8jCCN'.
'zqBn1iKUoNO8RZsYNkqBb5SnN6Aueeka9WGNN2Up6vxFcay482'.
'BDCG3BQJDairwjokDYLnUCa+N9sxLzTkBW43GlyxmewIAAhuM1'.
'rDFmm8PpfqKbF5bncEs7RgRdQmFgwDr54lmkNEQ2E3Jj9bclRN'.
'XmJTEKXq0NLjMBdgL2d0XiNW8zRuJbGW4EyeYgeFqgvfARIGkN'.
'bs27iK6W8PmJiWI0RKlGhKbghHQ1WlL1XmQZ5lnlJoxZhRpWq0'.
'SoM7YlaWbw+2PA/NF7S7HBOO4ZEBiUNMbo4PckA82+BDu7V1Ii'.
'wvnYJg2b47dvM8TFfKdOddT4ITBTmbUCKb0lbdTKJV1ojcOgYs'.
'i6g7tcPTRLRgx5CGp/DGpM93dyTq9mbC97lJpF0Ae8JQFdtDsa'.
'wMBHso6Qe3hg96OzybiJ3IsNpFOE98XUCdQSjujT745LAzc4V5'.
'esSC0sW1fKKvBtghTHLaBgc/21MmYqwZVvOYz6mayHohO9LGZm'.
'i3xSw7pDfv4wsTnHXM5YIX0goHKcnxVRXzMqYeJqTTnbc6/giL'.
'JUBAqpcHMqAVq22Lf0QAYVaIq8nYnCvBQ10JoDbRmp06w4QYxk'.
'y5sHkzltasC9gNBLSxC6YkfGA6YPdcmLZGs3KW5vZ8lQ0NJSWD'.
'leaCGyG/Pll1UnBVtIsVfSaUygmhIUZCilAgWlFU21vLAsABbp'.
'sx0XoQWGDz1RNmRTe4UGDrV1yND0gSUen8BlMZRoBaL/S3/gKJ'.
'dKPJK1LSq1XuDYhgSuvS6vsuboNMEBKTC1qDrUeUDUJ9gKllBK'.
'98H0Ym2+0u/IpBTXdqfMgdyrf2OCpv8YZYQDo/PUh8nUMr1uNW'.
'yqmgHIyQqRdQp62RWjWvAwmIAxcNrWXnoyy4GoVQSWoHeu0RrG'.
'qynsfqFWVgFSrXiocFyGR1zAjV7XUKtR9EVks+olFcuA/BAnqB'.
'rfXmSJs9YLEKlKU12FBrZo5bYVoNU382EXxunIAUMIBjKMNBfU'.
'Mpq13dAw3UrmkbVY2IR7gfzTRF6l8BiAGsRjEus1LDIbzjoeXY'.
'WmCV9detVYkk1rLpDdPJ60NSQvBqcGMN0hBua89JFWwsN9I4mw'.
'QZMdO8EOwDoxCm0Ppbu+o5wFR5StY0dbj8I5c1TWXOMFi6wQs7'.
'EEKwWoOqsIBRUjkBsIBFhDxE6bgjoERsDyCfUmPMUbhBmIaAP0'.
'Y2ia6wIWklogL0SUwTZ92JhMQ6BfOEulOahg11Nq89KQq2wanr'.
'ERHk8sVQT6IYpYed4AC3SF7VwUWHBoHqImYVqdEANJUtREay0K'.
'vB1TtLKu/4wlARyNXWLyQV1nBObJUCwUefWvAy1BN5c1crrc1d'.
'I24Ye/WO/pCmkQAEEQ89CNLwZ0kbDwajXUgiXnKcE2rTCPnW8C'.
'E0UQMf3ArOcB0nYJfaQBlWaKTDry73/S5SGdSgzUR9V90hCgow'.
'I61Ot4tCxM/EJCdHooAbAchQJPTaaypQttd0QrYvhTGDC0oNvt'.
'cuGzv98QKhORb04N9uLpMPqmvWGtBZpcIUZeUseCycx/6gsc2p'.
'NA2vtFYKaVG81jtqHOhWOBBbCLLwqGxaodeDqdbC3MAENXGkWs'.
'YnbQcAnbYQgwFEA5orKDkIQARz9ie5RqV2uocdV7y6ryYmXbtz'.
'EFIlg0byyzUCB/1K8qtCm6D6Ep6A5smmiBm2/wAqBUWLc9qcgP'.
'fiew8QxDyA/cEQINFXzqQ1FyJJ0dwNWK2AxhWBTKlsgbu5mPBH'.
'IhdQ6mBYBRNahDSQb5DwBuuW90g8Mm6FkIusy7rE1eVTRpDBSw'.
'REn5W1ivqDPpupL6NKZubaPwozSZP0aCPXsA4Mke6HEVFbBOtv'.
'BIOSkfCkLSbANakpdrxlanEBJQdFpctUyTarPjmuxwS39Vaotl'.
'sUaPm3qwBFTJOx3bXoETQV7FLHkCyHRU1Ri6vBPnXRXthUKhXh'.
'Rd5ABCKL5LCWKN2ng4cKakVYJyqCVlKblRpuno6ayhOSdeYTNh'.
'8VHMhdTh2ECjATNsxaOHp01VkVOilIa9REBTnSzzkCrVRTVag5'.
'lYvOlCnvzE3+GqV2q15ojlDs/tA7T2A9Ueqi60M74VcgpiamhR'.
'6PoVoV0Upa0pq6TkDZG0nkQJgbQ95Roa1sgnCKRjUrRsRRK+bQ'.
'Bbad5jPWJXz1sC3RBzrWYWwEJ0K43z3whnVQGuo0MahaAhnA3A'.
'17Oe++ageFF0HwBazLUaoiJoQNVcUZzxRvE7JiyEtFQoSNre4N'.
'CNAgBMrS8RlueRBQfwaPFpZUJnEiC0YUCVeG/qD0S6xKqMBZ5N'.
'dU5TJPpLxZtB7FvSYHFhpiXpoPRImfouQigKaZrRlyYV6f0Fvu'.
'JKPABAqEIjqiBvQA0ghWS9tnJM+mkrGnfFcq6UUKUYADozhBff'.
'AC4OxS6e/SAJHCNQ3avOWr5TOPWHQoUWgwhgrqQmuZloCJZLAq'.
'0KOYJzqv/oqGn9EUaNBqREdTYli2oloB1s3FEUq1ofzoGsNtAv'.
'DihlaGam+LxSHps+OFdKKcha6di3kpiIaGmVwYkFxrN07eALtx'.
'VznXMpFeFuISNJrmkpaWsH3M6RtpD3S1jdmC2DiRWV61RlArIQ'.
'K4X4NZpwG5pwHrxuZly5nx0SxcBA1OmckRlBEKeK3lkea9j1Lj'.
'NQ7bF+ib08llZqsqunHYMVBs3b6FE6mu/SaimrEvRzQSYGCKuV'.
'4q15l9RK1dpYHJuKde23nAiRbNrz4uai1ZAU3VQIJBa7qRMDgQ'.
'LGoHoHDcjSLm0ZSnAtgrzCtZp2KrBRBKsSazVxjB90BeDWRLg2'.
'tmsLyMEoXFZGdSZc9tMtQupCHbJLu19I8g1GRWRMcrcL03OWM2'.
'K3ehEIoxIqWV+tFfExHft2zNvg1gusMBMsvXdYAt6y7yAj+Oxo'.
'dcIEpgGBtOTxrlZDPXPVBVhreeddo/hdgQVcPIAhbWqhblW2QC'.
'q6lfTSPiCDTElK3CTRhQBaCiBFYxmQCwMC8OsjVgEJ3q1IHYoD'.
'Y5Lp/HbzEFKaKwpBVxH+tPZ7GLcWWL/CUFvScIQmJQOXsEzKlQ'.
'SmBOUtZYVwOCgQhFC2DKLj62hVSViqBVb7FRUobgv59IoWk8DH'.
'gfTdpquJppVi+CHlp+XXcYo6oiIvmaaCKt1tUd15SU5DsCg64S'.
'8FHOTWDT6AIloBbxagywELF0rH6ZCbKXrHVjTmiwJXaBUIClM1'.
'sYquSmxGF+N6WRbevGgQkA2KA2PJQRyYs1LCaTcogq07Bd5Xdm'.
'GOaRFJcYXfBmRWuyMgMbQZVd4owGpNKLzMTLAOnNzC4wPgGspQ'.
'ILRtHXNY00tdRkjCJytR9yY+opqbTDewB1rwB+yYrRRy6N6XhO'.
'Hqao1BqCBeyAoMvIIMCmovprElQsZvSKyC0hJcL8CK/50Vtg7J'.
'ioqscq4xSKjiCaQbkCu5BCoQEmmuMCOQhUNlcZtsNdCQTVWIwB'.
'jQCPTKQekh0Ohxm2etYdQg5Uxr7eNNY3VfAG3ELcPY4eCSyeI0'.
'GIBB/zo36GTIrymgHtDIhkBAHJfOyU2GIpKgoS2+EZ0HCYdZqn'.
'CNQ3OkQVjialu+KhgHThTiTgNBydLBGJloI23HQH6I5eS/kU6r'.
'SC03SFhiklBCzXGni5hI2OkBXbxaLhrIzXNKEKRRnaflNwBQzZ'.
'EM1pkHST1wpFo4tw1opC7EO9zjgUWjTFZpJwVpdW5sAay1Ei7E'.
'AuhFHC6QqXRCNmEtWSr03StvMOnSu7XMFdrFp0YR6tw89ENqmm'.
'RBwpY5RNwU8C6MsI5Mczh9FlhZAVS7yLvzPU25dRKppaIIBftw'.
'YklEHVMqZvOV5faPSwu25vLiJSy0i0ShouAC3NU4BLVeAg66BF'.
'TGrRTJUKNSSplC3BaSBKiwmNEImIBguGIN9EJAz0CASLkaoUYh'.
'usqIAIaUlNP0rQuEfbm2K/l0vIxCdSMbB2SSu6MlKDk6APi91e'.
'jG0sCZQVYXANyRpaXXNAa4CXiBUIKaWBw9m1gEYBAt2Ltvacnx'.
'BqACsI5EiPVaXLdQJDes5ZVcAEMWC2oDZhFX9MmCPZAriLwWQV'.
'+2hEGoKLDkJ0GJvciKa4dluEZFGoz63EMdsWXskarywRQGHpAS'.
'f3g8tDIppcoKLvta07hir6LbYAlma29TpDY+TKaQCje0V9oYaw'.
'2qDUkI06FTzuW+sxSrorunObwLgyVXFEBrlui14EqUovMZVwiL'.
'037IOkFZ2nCz6+WfdUU9IWtbFwC/ch2eP40RBdiGS/HX1KPqPj'.
'CRCxHURlmp2TBKD6t9YAa4wbpEtyermAGpjmSkxBdJY5b1cE+U'.
'2aADnWtiZHh/hUgwB2QUyh5dH1YrrHo3foZaJ55bfYZp1nPsie'.
'FiIaCreggqjYKvyjpT3iQ9qWLQMIQvVkqFiD6Gw2BCtEGQdCov'.
'Y+Ea0ppVxyv9KpssZxxICggjrNZNmHhrMKKJi32bcAwAN6qgiw'.
'+27MKISKGCYax2gdLcrpgNCDzP0wdydQs+UTpedezMNEnJzNIt'.
'xkKjLZfzsggMRoAHugHyO+KhSKBwIfeCd2IKa1SPZ+ABM1Qxep'.
'gqNGCBWJo4gg0qQuiWAC5VYAAAGAMAeFLIMXWqG2jQSIquZytI'.
'BjFDV3S89ZgofbVeu1Mp7EZZFsyV6yom5FuraAdS91/cKr6lR8'.
'ILq8h8DAMj5lSKbHWx+yS/YlSQrOLmX0ujbDdzR7IgzuzmGFyF'.
'g170pUeCOXujVSRkjAwFQBiI7h1eywYGveeH9eOpRwS+GZLqFo'.
'gUfbmDpUi6l4dJD9FBKRMVhijXzin6lLzdP9ot4r5P6kD6jR+Z'.
'czPRr+ZNUNa4T0jTO5OewgVRXJgmpN1RZRMGsEFKIvAnCiF5aX'.
'C41v18ilcpJbRQLlhGm6dU/Z99Szc83GkbqMKDGfpsGDjUP4+I'.
'k01Z/wISuzzR+0FMOhTfySLVw6CEonT+RIJ5uV/QzMWXQErJoM'.
'dfW8yYGcwuzBAdHuQ9NYjjrYAA0Cj77RLoPwYTo4AsFlxm+YkZ'.
'fMUTDCt2yZ7kUK7P8ArRHvlX1cAUqtoe0dt1o85C4FLV3He6yS'.
'5Mq0jHE7otGCPMlR5xNp4mUgxWq5Eti2ffyYtx6KSB4XNHDz5y'.
'5t+sYaNoaKD8zDAuST5xLPlCD9IZ1jrk/cWM79QeZBtsJdUF16'.
'IRaV2E+YTpk5WTmFtaU94EvJahQV/f3M9TImZRAKigbowM9wop'.
'3oyUHWbCDmOBHgJm+aQplXcv3TuNGu+sh1cYGmINBE/CpplTl5'.
'D+4Rzrqn4nILsZIWVSK1gDS6vv8AFl5hIbCFFSm9c8xtiMvyGi'.
'K2VwMJpWpSS4UA1LWEgW29ROpXWsjWurCtiARRxsxgddalWba8'.
'U8YVLR8FrsBbsRG6VTgzCAt2n8Dz+/uDLkU4PXJAiN0VSr0lE7'.
'orAPaNSErQQvK8CtUFa3wQe9grFZFftoGtLrMjdKAvbmgcU4sl'.
'Wi+U6KAQvFVN1XJFNVAZfW7YBwzniUAAACgwB9/QIgjhHRIpcy'.
'2brz0xJRXaC8hnqx+kNUAas0tW3eVQ87crW7eERiq4CIR5CiLE'.
'Uty12kEllbRByrAIwfawJCgmjgYwf+BxxxM30DXnC4b6pZRt14'.
'Goq6iIczwqfMKUxkKnNCJcpYtG33gP/9oACAECAwE/EP4ORctX'.
'AcpFEsZXaK4PSWIhMkOSCgsWwQv70qWnbwdLxF8GWsxEjGB4rX'.
'KL94DErrLQkqZTMvvLGAOkpiQWDEzKgZiy/t2sNtZ1ojRgXJL8'.
'RpwvnBhCOUrrG48alyhmGkbl7/cDnL1YG8ukFCkWEGJExi3aR1'.
'IYseOWWy5iXLlm5HhiXBuUcM4mn294JVYbQYSDOJCpAaAmiO6L'.
'nMuBcvw54MqW3LwDBqeUvGfuBtlUMq8Bpgt4jmYohADCEVyjwL'.
'RuXDTg1xZklw4L9vdI6kWprAgcCVLiy4gJeqJmcJGW3AYnBUKQ'.
'vrKIfcNWVloowqEI8Xg04NjcXogQgpc0sTbjqPBuKjAMqH3Cxm'.
'FYWYIAWXfETBIUQbplxY2wUSi1wWUaBKCoqI54mNYg1JVlyrlf'.
'cGZIs2w1BEJWIStiEtJWxHZMTHBNxhA1Y3TCpmLwso4KYzb7eL'.
'ZnGYwlhya0IjILDohQmdLcL1wFVgAgSDTGuocShuckefCxrgHl'.
'BfcC0S6Y+E7RV8XhARoROcFJUTMTUuYNnG5WU4oISo9Ys8HjRM'.
'zHWEXN/t4W1NyXUJpDiwYcETYJhmBC0ahZcFixMnxb8Kg+Ko8a'.
'+yhuKLFuYIGXFhMxFhO8TFHWUE0cHSbXjo4XBleIMS5lRh4Or9'.
'jZfacUBdEFA8BwuXFgUhDE1QwRcCvxBK4W+M0nSOt4usLHHC2u'.
'EVwRiVuUqGcLyUl8LmVzKVKxK+hXjumNGSYwLgm8o6Mu4MCXjh'.
'UAmqGCGWaIcSgWPNeO36JpFSQFRZ4Os0E08NZw1MxZmnE1TRG9'.
'+Be0bqapUeZbOZHDxhMRPoXmUMqCIHBmvBCaMQ6PHJjpBmKXRF'.
'ioVq+JMx+iaTbw1cHVmgmk4ajhqeOsLYl0FYmmaWajhq4Wy9eO'.
'5cv6KhcseB2lSuAoDeLuQSX7wIrfGC+CRMfRNCa+GqXHWJpANQ'.
'zQJUYeLilb4TSqi3FiMrgyxDfCAHE1DxVcriaeNjqQUmkplmpL'.
'NGMnwBrhCmXMG0rxP8FfC/q3wvxBLly+CfQ1zwuzjULNIIirqS'.
'lpHllMuV4Hxav2WuD4CMfEuPHcvhUzLS+FeBK4q3LnP4a4Khbh'.
'afygjCPgPGx4XN4njuXMRPDRE4MaoFHgC5ZFJiRvAAJB1C4f5I'.
'xhHgRhx24PgzwupcvjXjuXwTlKjwqC6PDexKUZmBoXMyMsqWY/'.
'yr4J4XgRfoV4blSvoXNeGZv4AubkwGaI4ZYohHBmF2P8y4cL4X'.
'L4X9F8FSk4kWVK8JxBnPgsYi+X0KvCSKqAJbFv+aPCoypUfoLT'.
'wuaypUrhcxKicFxrjXACNVBR4Bbx41BQ0Jiu7E1/z1Us4NRfop'.
'cGX9FWXLhBjrwXM2mvh1xlCXEgjRjEwwFJQ/bagZifTPA6xAhr'.
'wuowyhJxlyQLRB5pA5+3Gv06i1jhXB1ljnwkqhamzSWaxSCOFH'.
'cfu2JiVNjL4MAV8Y8DBSMQ5pb4/dqleB0l4hx9MeBfuA+Koysw'.
'C8sTlwdIwUffW7lzXjXC2apa4gXDw2iff0h4ag1LZdyokfv7Dh'.
'Xgvhcvhy+/sOFZgSm5WJUqVKlQ+/viuLCKTEcE0ff95Z4g4NiV'.
'U1/8DXBTLSsEeBLlmIFf+GolSmIwC+v3j//aAAgBAwMBPxD+CU'.
'YK4mJRlxgTJpLQ55iECawkocxRjySjWAzPP3kbG4gykoacKu5M'.
'SuC0zLhSFtZhijWXHDBR94umWlkvrMktlyjhmXCGKmZrGwIGB9'.
'u0FiNEYgykWbRJXhdZXDMxLzrCX4SNwcKibfcOSZaXlEtlRYKQ'.
'IMb8w5cbVw0mDGo4SoypmAw0hCFGsvhjMu2/t5KSABFtWMVYTo'.
'iwBAukKawwHwUynWPgs8NmkpHDF58NX3DAjthcUS8aYgjeA4gb'.
'bEpiXQTnBYLhGARczMDgQhLKNpVscfcNU14WzNJrizXMdIaQMn'.
'AIG5qmDiCAyzbjXAhIrWcmUkTX3AwXM1ICRuXhi5mk0JeIJvLO'.
'A9oGI3Y4amo8G8GBBYo0iv3HtReqApcMalVzAq4q4NSEtCiZM0'.
'IWYqIN4bYM+BgEmrWUmTgX2/qlRmKFEV1KwWMEy5TDYDKbwFDG'.
'zSA8N4muPaaWmbD4QRiy5cdft+ipiCCVyZll1hiAAxFxLBCzEp'.
'pioAQ1xXmMhTNLio4cQ8JeNzEr7eFsqtmcgO8UqglsW6oJ0lqe'.
'CyqhVygiqVKYIoJcy7YLqDHhoeNVxcfb7oubcpissNZcvE3jpN'.
'YDU3IZxFohRzLOATNAx4bgwZc1eKkG+DKfZUGNpyMJtR1lYgiT'.
'EEGM7DDm4QS6mqENZivxVMzCDmXfiVLGVwCXtEVPBoPsZjMp4F'.
'aR2maypeY6TaECG6V3EjRKtmBwGuBr4z46WypQYcJNBGoqHeTg'.
'9hMWkzHAfKXrTMvemI7pOCkBHorg5sl+Uvyl60zKl/XCyE6wxR'.
'L01FzCJU3iwmIQtzTHLNCZsdyxCDf6A4+he25YrjTpThwLUXLX'.
'uWt4LXrgWlw6MXvAtCLvDz4HYgtCpvaopKZebjpRaKRVfRrx2m'.
'3FhSJioyCanHRNUW8Q1MrBm4YPEsTXEfoDLKU8BfAcEFOAt4HP'.
'hQESbsy9awcrgrMA2QwMYcuFa8DkQH0CqhE8ZOaa+F14EMXiNo'.
'ysRDMDEDMOPFtBg5j9AKuJcHHDpxTNCZlMwRRHBCbEgNZCmnDU'.
'jASs1U5AkAUQYYhQ2RLmMECAIyOIQWUUjxA3mIVMcE8ZWMrg2l'.
'CWlZfhojB5ZVwrgeCtX+BXCvDUqVwqVxqVK8Q5xeJxTwrRAoDx'.
'VGKZcGX4r8FcHB9G5f8ALWGcRfAR4MSuJDP8MHEWSpyeG+C4g4'.
'C38pZU1R4kOBr4Qlopcw6MdTOZAXB+jfgviVDmLb4FUpgFyklz'.
'RESQBVaQ/lrMeATMDgOfG0RPC5cAwcIpwrxkqVjhhg0vhoJYhC'.
'ASWUSueYiOIfymEeJLiwIqxHnx3LuJ4LEXFQJaovCIxPEQ1mIv'.
'gWpszUfAAAhXWgNWFR/MqHG5c1hpbHI+mcOqJSX4NLMkGY4MJb'.
'hUPEqCCpgBFDesx/AgUECv5+ZURhUXv0AslTPC5cGNphwxAicC'.
'LmWnXFKiwuLb4FQwrfBIixNWDBDAV9guDjgX6KqVmafRqVwrEe'.
'JpwcHheGVxAVUQTCUhUlx9tGaYJwUSpUquFMpmeB4BCDiDcd+E'.
'2SouNWmvYpQyibEfbtvDeOIRRKlMqVLxDWaI0MeFlkpphDGSFl'.
'Qo1RD7aaR8W/C+FwL4Ym0VAeNOEgUlkVIKYfbniwOFS5fCuGjD'.
'Wb/Uk4Q+57TMz4Ll846Q1hrFb99JXG5cuFRCGN48w5x1h9/HxY'.
'gDvKShDBDH/gKmZfAIkJUqBNIrPv5xuLLJcya+AP8A4AIMqEuM'.
'qVGbTMC2LP38lMb43wupmFZltFr/AMDbgGKOBXDM1gVmLf8A4U'.
'gxflO2dkT94//Z'.
'');
}

function image_sendmail()
{
	header("Content-type: image/gif");
	header("Content-length: 509");
	echo base64_decode(
'R0lGODlhCwAIAOYAAAAAAP////r6/fPz9v7+//Dw8efn6MnKt9'.
'PUldTVms7PlsvMlLW2hLu8icHClsrKjMbGirW1ftXVlsvLj+Lh'.
'oNjYmczMkdvbnNram7y8huLiopGRaObmpt/fod7eoNbWmsjIkM'.
'TEjeDgot7eod3doNzcn8/PlsvLk76+ira2hMvLlLe3hbGxgb6+'.
'i6+vgKWleZ2dc4aGYs3NmMjIlcbGlcjIl7y8kMHBlcTEmI+PcM'.
'DAmI6OcqGhgsjIosjIpMLCn5WVesbGo6ioisXFo8PDpMfHqrq6'.
'otTUwcXFuMbGutTUytvb08rKw+Li3+7u7eHh4MXEi8zLlNLRxu'.
'rq6v///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'.
'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'.
'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'.
'AAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAAFQALAAAAAALAA'.
'gAAAdagAQDTUoHRkRBPj9JSDoqEwgVGBcUEDxCESAiIyQlHigM'.
'OFJQKTANJwotMSY1TzIhLwsrLlEfLDYFNxsJHSQjJRoPQE45GR'.
'wXFRIWNENMBjszDj1FR0tTAgGBADs='.
'');
}


function image_w3c_xhtml_valid() 
  {
   header("Content-type: image/gif");
    header("Content-length: 2345");
    echo base64_decode(
'R0lGODlhWAAfAPf/AP///wgICBAQEBgYGCkpKTExMTk5OUJCQk'.
'pKSlJSUlpaWmNjY2tra3Nzc3t7e8DAwIyMjJSUlK2trbW1tb29'.
'vcbGxs7OztbW1t7e3u/v7/f391JKSt6trc57e8Zzc8Zra71aWp'.
'wICAgAAJQAAJwAAJwIAK1rY60hEIQ5KbU5GIwpCLVKIb1KIc5j'.
'McZaKaVSKdZzOc5rMd6EQs57OWNaUueUSt6MQntza1pSSnNjUh'.
'gQCDEhEBAIAGNKKVpCIe+tUq2MWoRjMUIxGP/v1ntrUpR7UnNa'.
'Mee1Y2tSKdalUve9WpRrKZyEWrWUWtatY86lWu+9Y72USue1Wv'.
'/GY5RzOd6tUrWMQoxrMSEYCIRzUqWMWr2cWt61Y//Oa1JCIaWE'.
'QvfGY3tjMc6lUu+9WsacSpx7OXNaKZRzMUo5GGtSIZSMe2tjUq'.
'2UWsalWue9Y4xzObWUSt61WikhEOe9Wv/OY9atUq2MQoRrMYx7'.
'UrWcWta1Y//Wa2NSKe/GY/fOY72cSpR7Od61UlpKIf/WY721nJ'.
'yUe5SMc3NrUr2lWjkxGDEpECkhCP/ea0I5GP/31ntzUv/nc//v'.
'c87OxlJSSpSUe1paShAQCBgYCAgIAO/39/f//97n573GxkJKSt'.
'bn787e57XO3r3W55S1zhBalIStznOcvWOUvUJ7rTlzpRhalN7n'.
'773O3q3G3jlCSgBKjOfv98bW56W91oytzoSlxnOcxmuUvVKEtU'.
'p7rTFrpSljnBBSlAhKjABChFqEtTlrpSFanBBKjAhKlABCjHuc'.
'xmOMvUJzrZy11iFSlAA5hAAxewAxhAApe9be787W5zE5Su/v9/'.
'f3/0JCSjk5SgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'.
'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'.
'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'.
'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAAA'.
'8ALAAAAABYAB8AQAj/AC0IHEiwoMGDCC1IWsiwocOHECNGrPCg'.
'okULAAA8c/UrIwBmyFB5BAAKGTJhKHkpEwmAwbSXFRxMyOiIDp'.
'09cAJMsxRnDxg5mIyEMYNJzhRGkHxMi8OIUZhpOuT41KFjGhVI'.
'Zaa92WOzS5wzhixWxDjSlytbyGoBKHYWmayRAIYhywVAA9yRNb'.
'va5Eqni16/fbsMsvm3b9c9fgH33SODxIgRjkd4GHu3suXLcPP6'.
'3aPF2RbEYCZtcLNnS7RJQPBEw+Gm8GEYjiOTgMFoz9ewYsli3s'.
'1bc+LAwBML70J8uOHixhPfFvtANwBUwjwB+CSMVzBVJJXZAqAJ'.
'FzJWGTUU/7hgeQidQF68xIEkphESKHwEgekCRhCfrlcaxemy54'.
'uQOmCgwUcXczRiByRRCCFEIooI0oUUgKDg2AeUwTVMMuBlJEsy'.
'xjy3TC3QqJJLMrRowAB5GU0QAAUZmdfXE4hkEU0eiP1FQytt7D'.
'EIE9LgUeNmMjxGgmMh1GBDbLGpQAlzzvHm5F0uTtHFFIRNadiV'.
'imEpJZVbErYHX4Ytl9uTZEJJ2Jlopqnmmmx6tQRuF3mkyUiazH'.
'lXnXjWuVtNfXhxyReQ2KGDEX4IscN8YCQiRBxYUNWoJWSUcYkc'.
'dRA3RiKXlAHJF5ny5RVYTHoUSzKzeERLMqJ4dIsysWRUSjKmZP'.
'8EwQAZZLSAAR4N4RcjV+w0jRWM/GTJHH5AcQkWVO6hVBJdODUN'.
'GWgkIocidWiFlVWRfPmpGqF6VFIxAOTyCyvI3BLuLpkAYAowwi'.
'RTTCYaQHCZi1liqdhvVeqV5r1dMTIDZACPwEGTn7iCTCrpMrML'.
'L7q8AtcqwuyySWV2AeAiHVA48Yg0TXhqGBSHtLJBJ9EUcdyVe7'.
'Qwwgk/8LVHDbFBFkIHY8KVyTAjaTCMnaDkwoswuYTi0QQCvKRA'.
'rS2izIY0WwBWHBgJIOCEjktngW9gLAgpG8AtiBlnmWBbrC++Xf'.
'gh5V5guAEFlldi+XIJI8Dwo9cVVdDAAgrkjffeCvD/7XffgP+N'.
'd9+V9CCI4T0kjvjihy+eeOOHN+444pD34MUiN3QbNpm6Iuf556'.
'CHLnpxcVBRiOabO3kxm6y3zvpX3Nacuup0TDHGfH2BMcYUYIBB'.
'WO909C48GFPw7jthvHNJvF6wd/vJMaV4e8xbHr2CSy7YZ29uXR'.
'E4gNEFE4u9RxKWYCKoHH10gQUPXUCyhw5YuPHFF4pME8YXbgTx'.
'khF7MPLGS1fJylY2U4UzxO5rAOgFMmDhkVMIAzuuSoYtPOGJWE'.
'SsGQDIQAAI4IADDEAAKKLXHoQwDS8gZgpyeIkK5TCfLghiGnXo'.
'31PuIAIxfGEad8DEG641wL4U8IAVAsAr/1zBEgCcQhnb00Qqdu'.
'EwZgijYRk5gLw8giIA1KQLN6SUHGrYBR1gAgxcsYQO5rMHL0wj'.
'CTKchhjs8BI7HMFagJhGIsIQBirQ4StLkh0ASrGMtwyDF95JVS'.
'0wBIBZJMMVtUhXBhJQsbvUhErEkZLTqkS2ewmHK8SpXSY3GYcX'.
'BAwEzRlJJnaRClgI4xTPIKUpt1cXVgQjGW+pQCPhYh5+TTJf+a'.
'rXyfjVF0Y0BkmSCeVIZnESXrSKFMj42SoeZh1oYKaWi4FCE46g'.
'r65MoQl4YMITMommTMKgZcdhjNYeM5kgegQXyiCGR1ChHQDEwh'.
'TOBIAslIELClQALhGQAE1sMv+FNZAsGlsYTJaY4AyrHSEBrdjC'.
'vlT2mCL1L0gAc4wJUJeRVQQjXRlRmHRgsYuD6QIZo6jVBQzAAA'.
'coYBoJ8IgjqNTLpQW0baFBgB5u0oRWEOFMxFFCCsY5pIiOYAWA'.
'AKIw6RRPj8zyGaFgIFw0cAEM0PJMnJFGHgQKheMRQRpaYMQgHu'.
'EMJoApnC54DGR6OoIY2MaAFJ3dvKC6BRy0wS9gyMEaWrOHNtAg'.
'Ggg4BBfa1rZmHQlgtPFL8/So1stc0TW3dNogbonLxDCiBnIDzB'.
'5+yJwLaMACFMisZjfL2c56VrOc4AQhovAH0pY2CqRFrWpVe9rV'.
'pra0sG0tbFFLBjIGfCENQg0IADs='.
'');
}

function image_bl_pix()
{
	header("Content-type: image/gif");
	header("Content-length: 43");
	echo base64_decode('R0lGODlhAQABAIAAAGYzzAAAACH5BAAAAAAALAAAAAABAAEAAA'.
	'ICRAEAOw=='.
	'');
}

if (isset($_GET['image']))
{
	switch ( $_GET['image'] )
	{
		case 'w3c_xhtml_valid.gif': image_w3c_xhtml_valid(); break;
		case 'dir.gif':				image_dir(); break;
		case 'login.jpg':			image_login(); break;
		case 'kplaylist.gif':		image_kplaylist(); break;
		case 'album.gif':			image_album(); break;
		case 'link.gif':			image_link(); break;
		case 'cdback.gif':			image_cdback(); break;
		case 'root.gif':			image_root(); break;
		case 'php.gif':				image_php(); break;
		case 'saveicon.gif':		image_saveicon(); break;
		case 'spacer.gif':			image_bl_pix(); break;
		case 'sendmail.gif':		image_sendmail(); break;
		default: break;
	}	
	die();
}

// end of pictures...


class kprandomizer
{
	var $limit;
	var $genre;
	var $mode;
	var $playlist;
	var $sids;
	var $ssort;
	var $order;
	var $users;

	function kprandomizer()
	{
		$this->limit = 25;
		$this->genre = -1;
		$this->mode = 0;
		$this->playlist = -1;
		$this->sids = array();
		$this->ssort = 'DESC';
		$this->order = 0;		
		$this->users = array();
	}

	function setusers($users)
	{
		$this->users = $users;
	}

	function setorder($order)
	{
		$this->order = $order;		
	}

	function setgenre($genre)
	{
		$this->genre = $genre;
		if (is_array($this->genre) && count($this->genre) == 1 && $this->genre[0] == -1) $this->genre = -1;
	}
	
	function setmode($mode)
	{
		$this->mode = $mode;
	}

	function setplaylist($playlist)
	{
		$this->playlist = $playlist;
	}

	function setlimit($limit)
	{
		$this->limit = $limit;
	}

	function getgenreor($name)
	{
		if (is_array($this->genre))
		{
			$sql = $name;
			foreach($this->genre as $g) $sql .= ' = '.$g.' or '.$name;
			return substr($sql, 0, strlen($sql) - (strlen($name) + 4));
		}
	}
	
	function getfavourites()
	{
		global $u_id;
		$sql = 'SELECT h.s_id,count(*) as cnt, sum(h.dpercent) as rate from '.TBL_MHISTORY.' h, '.TBL_SEARCH.' s where s.id = h.s_id AND u_id = '.$u_id;
		if (is_array($this->genre))  $sql .= ' AND ('.$this->getgenreor('s.genre').')';
		$sql .= ' GROUP by h.s_id ORDER by rate '.$this->ssort.', cnt '.$this->ssort.' limit '.$this->limit;
		$res = db_execquery($sql, true);
		if ($res !== false) while ($row = mysql_fetch_row($res))$this->sids[] = $row[0];
	}

	function getalltime()
	{
		global $u_id;
		$sql = 'SELECT id from '.TBL_SEARCH.' where hits > 0';
		if (is_array($this->genre))  $sql .= ' AND ('.$this->getgenreor('genre').')';
		$sql .= ' ORDER by hits '.$this->ssort.' LIMIT '.$this->limit;
		$res = db_execquery($sql, true);
		if ($res !== false) while ($row = mysql_fetch_row($res))$this->sids[] = $row[0];
	}

	function getrandom()
	{
		global $u_id;
		$sql = 'SELECT id from '.TBL_SEARCH.' ';
		if (is_array($this->genre))  $sql .= ' WHERE ('.$this->getgenreor('genre').')';
		$res = db_execquery($sql, true);
		srand(make_seed());
		if ($res !== false) while ($row = mysql_fetch_row($res)) $this->sids[$row[0]] = getrand();
		arsort($this->sids, SORT_DESC);
		reset($this->sids);
		
		$nlist = array();
		$ncnt = 0;
		foreach ($this->sids as $id => $key)
		{
			if ($ncnt >= $this->limit) break;
			$nlist[] = $id;
			$ncnt++;
		}
		$this->sids = $nlist;	
	}

	function getmusicmatch()
	{	
		global $u_id;
		$master = array();
		$users = array();
		
		$sql = 'SELECT h.s_id, sum(h.dpercent) as rate, count(*) as cnt FROM '.TBL_MHISTORY.' h, '.TBL_SEARCH.' s WHERE h.s_id = s.id AND h.u_id = '.$u_id;
		if (is_array($this->genre)) $sql .= ' AND ('.$this->getgenreor('s.genre').')';
		$sql .= ' GROUP BY h.s_id ORDER BY rate DESC,cnt DESC';

		$res = db_execquery($sql, true);	
		while ($row = mysql_fetch_row($res)) $master[] = array($row[0], $row[1]+$row[2]);
		
		for ($i=0,$c=count($this->users);$i<$c;$i++)
		{
			$res = db_execquery('SELECT s_id, sum(dpercent) as rate, count(*) AS cnt FROM '.TBL_MHISTORY.' WHERE u_id = '.$this->users[$i].' GROUP BY s_id ORDER BY rate DESC,cnt DESC', true);
			while ($row = mysql_fetch_row($res)) $users[$this->users[$i]][$row[0]] = $row[1] + $row[2];
		}		
		
		$musicm = array();

		for ($i=0,$c=count($master);$i<$c;$i++)
		{
			$add = true;
			for ($i2=0,$c2=count($this->users);$i2<$c2;$i2++) if (!isset($users[$this->users[$i2]][$master[$i][0]])) $add = false;
		
			if ($add)
			{
				$hits = $master[$i][1];
				for ($i2=0,$c2=count($this->users);$i2<$c2;$i2++) $hits += $users[$this->users[$i2]][$master[$i][0]];
				$musicm[$master[$i][0]] = $hits;
			}		
		}

		arsort($musicm, SORT_NUMERIC);
		
		$cnt = 0;
		foreach ($musicm as $sid => $hits)
		{			
			$cnt++;
			$this->sids[] = $sid;
			if ($cnt > $this->limit) break;
		}
	}

	function execute()
	{
		switch ($this->mode)
		{
			case 0:
				$this->getfavourites();	
				break;
			case 1:
				$this->getalltime();
				break;
			case 2:
				$this->getrandom();
				break;
			case 3:
				$this->getmusicmatch();
				break;
				
			default: break;
		
		}
		
		if (count($this->sids) > 0)
		{
			if ($this->order == 1)
			{
				$nlist = array();
				for ($i=count($this->sids) - 1;$i>=0;$i--) $nlist[] = $this->sids[$i];
				$this->sids = $nlist;
			}
			if ($this->playlist == -1)
			{
				kplay_m3uurl();
				for ($i=0,$c=count($this->sids);$i<$c;$i++)	kPlay_sendlink2($this->sids[$i]);
			} else
			{
				db_addtoplaylist($this->playlist, $this->sids);
				$this->view(get_lang(33));
			}
		} else $this->view(get_lang(217));
	}

	function getusers($selected)
	{
		$out = '';
		global $u_id;

		
		$res = db_execquery('SELECT * FROM '.TBL_USERS, true);
		while ($row = mysql_fetch_assoc($res))
		{
			if ($row['u_id'] != $u_id) 
			{
				$found = false;
				for ($i=0,$c=count($this->users);$i<$c;$i++)
				if ($row['u_id'] == $this->users[$i]) 
				{
					$out .= '<option selected="selected" value="'.$row['u_id'].'">'.$row['u_login'].'</option>';
					$found = true;
				}
				if (!$found) $out .= '<option value="'.$row['u_id'].'">'.$row['u_login'].'</option>';
			}
		}
		return $out;
	}

	function view($message = '')
	{
		global $userauth, $PHP_SELF, $setctl, $u_id;
		kprintheader(get_lang(212),1);
		?>
		<form name="randomizer" method="POST" action="<?php echo $PHP_SELF; ?>">
		<input type="hidden" name="action" value="randomizer"/>
		<table width="95%" align="center" border="0" cellspacing="1" cellpadding="0">
		<tr>
			<td class="importnant" colspan="2"><?php echo $message; ?></td>
		</tr>
		<tr>
			<td height="5"></td>
		</tr>
		<tr>
			<td valign="top" class="wtext"><?php echo get_lang(213); ?></td>
			<td valign="top">
				<select name="mode" class="fatbuttom">
					<option value="0"<?php if ($this->mode == 0) echo ' selected="selected"'; ?>><?php echo get_lang(216); ?></option>
					<option value="1"<?php if ($this->mode == 1) echo ' selected="selected"'; ?>><?php echo get_lang(218); ?></option>
					<option value="2"<?php if ($this->mode == 2) echo ' selected="selected"'; ?>><?php echo get_lang(171); ?></option>
					<option value="3"<?php if ($this->mode == 3) echo ' selected="selected"'; ?>><?php echo get_lang(263); ?></option>
				</select>
			</td>
			<td valign="top" class="wtext"><?php echo helplink('randmode'); ?></td>
		</tr>
		<tr>
			<td valign="top" valign="top" class="wtext"><?php echo get_lang(87); ?></td>
			<td valign="top"><select class="fatbuttom" style="width:150px" multiple="multiple" size="6" name="usersfilter[]"><?php echo $this->getusers($this->users); ?></select></td>
			<td valign="top" class="wtext"><?php echo helplink('randusers'); ?></td>
		</tr>
		<tr>
			<td valign="top" class="wtext"><?php echo get_lang(178); ?></td>
			<td valign="top"><input type="text" size="5" maxlength="6" name="limit" value="<?php echo $this->limit; ?>" class="fatbuttom"/></td>
			<td class="wtext"><?php echo helplink('randlimit'); ?></td>
		</tr>
		<tr>
			<td valign="top" class="wtext"><?php echo get_lang(147); ?></td>
			<td valign="top">
				<select name="genres[]" size="6" style="width:200px" multiple="multiple" class="fatbuttom">
				<option value="-1"<?php if ($this->genre == -1) echo ' selected="selected"'; ?>><?php echo get_lang(67); ?></option>
				<?php if (class_exists('id3')) echo genre_select(false,$this->genre); ?></select>
			</td>
			<td valign="top" class="wtext"><?php echo helplink('randgenre'); ?></td>
		</tr>
		<tr>
			<td valign="top" class="wtext"><?php echo get_lang(214); ?></td>			
			<td valign="top">
				<select name="playlist" class="fatbuttom">
				<option value="-1"<?php if ($this->playlist == -1) echo ' selected="selected"'; ?>><?php echo get_lang(215); ?></option>
				<?php				
				$playlists = db_getplaylist($u_id);
				for ($c=0,$cnt=count($playlists);$c<$cnt;$c++) 
				{			
					if ($playlists[$c][1] == $this->playlist) $sel =' selected="selected" '; else $sel='';
					echo '<option value="'. $playlists[$c][1].'"'.$sel.'>'.$playlists[$c][0].'</option>';
				}
				?>
				</select>		
			</td>
			<td valign="top" class="wtext"><?php echo helplink('randplaylist'); ?></td>
		</tr>
		<tr>
			<td valign="top" class="wtext"><?php echo get_lang(219); ?></td>			
			<td valign="top">
				<select name="order" class="fatbuttom">
				<option value="0">+</option>
				<option value="1">-</option>
				</select>
			</td>
			<td valign="top" class="wtext"><?php echo helplink('randorder'); ?></td>
		</tr>
		<tr>
			<td colspan="3" height="5"></td>
		</tr>
		<tr>
			<td valign="top"><input class="fatbuttom" type="submit" name="execute" value="<?php echo get_lang(154); ?>"/>
			<input class="fatbuttom" type="button" name="closeme" value="<?php echo get_lang(27); ?>" onclick="javascript: window.close();"/>
			</td>
			<td colspan="2" class="wtext" align="right"><?php echo get_lang(191); ?>&nbsp;</td>
		</tr>
		</table>
		</form>
		<?php
		kprintend();
	}
}


class kbulletin
{
	function getlatest()
	{
		$res = db_execquery('SELECT b.*,u.u_login FROM '.TBL_BULLETIN.' b, '.TBL_USERS.' u WHERE b.u_id = u.u_id AND b.publish = 1 ORDER BY bid DESC LIMIT 1');
		if (mysql_num_rows($res) == 1)
		{
			$row = mysql_fetch_assoc($res);
			return $this->formatted($row);
		} else
		{
			$row['u_login'] = 'none';
			$row['utime'] = time();
			$row['mesg'] = 'Welcome to kPlaylist! This is a autogenerated bulletin. Click on \'more\' to add a real one.';
			return $this->formatted($row);
		}
	}

	function getlink($msg)
	{
		global $PHP_SELF;
		return '<a href="'.$PHP_SELF.'?action=bulletin&amp;m=read">'.$msg.'</a>';
	}

	function savebulletin($bid, $publish, $mesg)
	{
		global $u_id;
		$mesg = stripcslashes($mesg);
		$mesg = str_replace("\r\n", "\n", $mesg);
		if ($bid == 0)
		{
			$sql = 'INSERT INTO '.TBL_BULLETIN.' SET publish = '.$publish.', mesg = "'.mysql_escape_string($mesg).'", utime = '.time().', u_id = '.$u_id;		
			$res = db_execquery($sql);
			return mysql_insert_id();
		} else
		{
			$sql = 'UPDATE '.TBL_BULLETIN.' SET publish = '.$publish.', mesg = "'.mysql_escape_string($mesg).'" WHERE bid = '.$bid;
			$res = db_execquery($sql);
			return $bid;
		}
	}

	function editbulletin($bid, $reload=false)
	{
		global $PHP_SELF;
		
		if ($bid)
		{
			$res = db_execquery('SELECT * FROM '.TBL_BULLETIN.' WHERE bid = '.$bid);
			$row = mysql_fetch_assoc($res);
		} else
		{
			$row['publish'] = 0;
			$row['mesg'] = '';
		}
		
		kprintheader(get_lang(268),1);
		?>
		<form method="post" action="<?php echo $PHP_SELF; ?>">
		<input type="hidden" name="action" value="savebulletin"/>
		<input type="hidden" name="bid" value="<?php echo $bid; ?>"/>
		<table width="100%" border="0" cellspacing="5" cellpadding="0">
		<?php if (db_guinfo('u_access') == 0)
		{
		?>
			<tr>
				<td class="wtext"><?php echo get_lang(271); ?></td>
				<td><input type="checkbox" class="fatbuttom" name="publish" value="1"<?php if ($row['publish']) echo ' checked="checked"'; ?>/></td>
				<td class="wtext"><?php echo helplink('btpublish'); ?></td>
			</tr>			
		<?php
		}
		?>
			<tr>
				<td class="wtext"><?php echo get_lang(228); ?></td>
				<td><textarea class="fatbuttom" rows="10" cols="70" name="mesg"><?php echo $row['mesg']; ?></textarea></td>
				<td></td>
			</tr>
			<tr>
				<td></td>
				<td>
					<input class="fatbuttom" type="submit" name="store" value="<?php echo get_lang(45); ?>"/>
					<input class="fatbuttom" type="button" name="closeme" value="<?php echo get_lang(27); ?>" onclick="javascript: window.close();<?php 
					if ($reload) echo ' window.opener.location.reload();'; ?>"/>	
				</td>
				<td></td>
			</tr>
		
		</table>
		</form>
		<?php
		kprintend();
	}

	function delbulletin($bid, $u_id)
	{
		if (db_guinfo('u_access') == 0) db_execquery('DELETE FROM '.TBL_BULLETIN.' WHERE bid = '.$bid); else
					db_execquery('DELETE FROM '.TBL_BULLETIN.' WHERE bid = '.$bid.' AND u_id = '.$u_id);
	}

	function showall()
	{
		global $PHP_SELF, $u_id;
		
		kprintheader(get_lang(268), 1);
		mp3table('');
		showdir('',get_lang(268),0);
		echo '</td></tr>';
		
		$res = db_execquery('SELECT b.*,u.u_login FROM '.TBL_BULLETIN.' b, '.TBL_USERS.' u WHERE b.u_id = u.u_id ORDER BY bid DESC');
		?>
		<tr><td height="15"></td></tr>
		<tr>
		<td>
		<table width="35%" align="left" cellpadding="0" cellspacing="0" border="0">
		<?php
		while ($row = mysql_fetch_assoc($res))
		{
			echo $this->formatted($row, false);
			
			if (db_guinfo('u_access') == 0 || $row['u_id'] == $u_id) echo '<tr><td><a href="#" onclick="javascript: newwin(\'editbulletin\', \''.$PHP_SELF.'?action=editbulletin&amp;bid='.$row['bid'].'\',300,550);">'.get_lang(71).'</a>&nbsp;<a onclick="javascript: if (!confirm(\''.get_lang(210).'\')) return false;" href="'.$PHP_SELF.'?action=delbulletin&amp;bid='.$row['bid'].'">'.get_lang(109).'</a></td></tr>';
			?>
			<tr><td height="10"></td></tr>
			<?php
		}
		echo '<tr><td><a href="#" onclick="javascript: newwin(\'newbulletin\', \''.$PHP_SELF.'?action=newbulletin\',300,550);">'.get_lang(72).'</a></td></tr>';
		?>
		</table>
		</td>
		</tr>
		<?php
		echo '</table></form></td>';
		infobox();
		echo '</tr></table>';
		kprintend();
	}

	function formatted($row, $single =true)
	{
		global $cfg;
		$out = '';
		$moreset = false;
		if ($single) $out .= '<table width="95%" align="center" cellpadding="0" cellspacing="0" border="0">';
		$out .= '<tr><td height="4"></td></tr><tr>';
		$msg = $row['mesg'];
		$msg = str_replace("\n", '<br/>', $msg);
		if (strlen($msg) > $cfg['frontbulletinchars'] && $single) 
		{
			$msg = substr($msg, 0, 120).'&nbsp; ... '.$this->getlink(get_lang(270));
			$moreset = true;
		}
		$out .= '<td class="wtext">'.$msg.'</td>';
		$out .= '</tr><tr><td height="4"></td></tr><tr>';
		$out .= '<td class="loginkplaylist">'.get_lang(269, date($cfg['smalldateformat'],$row['utime']), $row['u_login']);
		if (!$moreset && $single) $out .= ' '.$this->getlink(get_lang(270));
		$out .= '</td>';
		$out .= '</tr>';
		if ($single) $out .= '</table>';
		return $out;
	}
}


// for the mailing stuff, you'll need this package: http://pear.php.net/get/Mail_Mime-1.2.1.tgz if you intend to use pear.

class mailmp3
{
	var $sid;
	var $tomail;
	var $message;
	var $crlf;

	function mailmp3()
	{
		$this->sid = -1;
		$this->tomail = '';
		$this->message = '';
		$this->crlf = "\r\n";
	}

	function generatemailheader($subject, $from, $to, $html, $mimetype, $f2)
	{
		$data  = 'From: '.$from.$this->crlf;
		$data .= 'To: '.$to.$this->crlf;
		$data .= 'MIME-Version: 1.0'.$this->crlf;
		$data .= 'Content-Type: multipart/mixed;boundary="----=_20041023160256_48355"'.$this->crlf;
		$data .= $this->crlf;
		$data .= '------=_20041023160256_48355'.$this->crlf;
		$data .= 'Content-Type: text/html; charset="ISO-8859-1"'.$this->crlf;
		$data .= 'Content-Transfer-Encoding: 8bit'.$this->crlf;
		$data .= $this->crlf;
		$data .= $html.$this->crlf; 
		$data .= $this->crlf;
		$data .= '------=_20041023160256_48355'.$this->crlf;
		$data .= 'Content-Type: '.$mimetype.$this->crlf;
		$data .= '      name="'.$f2->fname.'"'.$this->crlf;
		$data .= 'Content-Transfer-Encoding: base64'.$this->crlf;
		$data .= 'Content-Disposition: attachment;'.$this->crlf;
		$data .= '      filename="'.$f2->fname.'"'.$this->crlf;
		$data .= $this->crlf;
	
		$fp = fopen($f2->fullpath, 'rb');
		$data .= chunk_split(base64_encode(fread($fp, $f2->fsize)), 76, $this->crlf);
		fclose($fp);

		$data .= '------=_20041021175925_81962--'.$this->crlf.$this->crlf;

		return $data;
	}
	
	function senddirect($from, $subject, $html, $mimetype, $mailaddr, $f2)
	{
		global $win32, $setctl;

		$data = $this->generatemailheader($subject, $from, $mailaddr, $html, $mimetype, $f2);
		
		if ($win32)
		{
			ini_set('SMTP', $setctl->get('smtphost'));
			ini_set('smtp_port', $setctl->get('smtpport'));
		}		
		return mail ($mailaddr, $subject, '', $data, '');
	}

	function setsid($sid)
	{
		$this->sid = $sid;
	}

	function setmessage($message)
	{
		$this->message = $message;
	}

	function settomail($tomail)
	{
		$this->tomail = $tomail;
	}

	function message($finfo, $message)
	{
		return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html>
		<head>
		<title>kPlaylist mail</title>
		<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1"/>
		<style type="text/css">
		td
		{
			font-family: Verdana, Arial, Helvetica, sans-serif;
			font-size: x-small;
			font-style: normal;
			color: #000000
		}
		</style>
		</head>
		<body bgcolor="#ffffff">
		<table width="50%" border="0" align="center" cellspacing="0">
		<tr>
		<td colspan="2">Your requested file: <b>'.$finfo.'</b></td>
		</tr>
		<tr><td colspan="2" height="15"></td></tr>
		<tr><td colspan="2">'.$message.'</td></tr>
		<tr><td colspan="2" height="35"></td></tr>
		<tr><td class="notice"><a href="http://www.kplaylist.net" target="_blank"><img alt="kPlaylist" src="http://www.kplaylist.net/kplaylist_box.gif" border="0"/></a></td>
		<td align="right" valign="bottom" height="15" class="notice">Powered by kPlaylist</td>
		</tr>
		</table>
		</body>
		</html>';
	}

	function sendpear($from, $subject, $html, $mimetype, $mailaddr, $f2)
	{
		global $setctl;

		include('Mail.php');
		include('Mail/mime.php');
		include('Mail/mimeDecode.php');
		
		$hdrs = array(	'From' => $from, 'To' => $mailaddr, 'Subject' => $subject);

		$mime = new Mail_mime($this->crlf);
		$mime->setHTMLBody($html);

		$mime->addAttachment($f2->fullpath, $mimetype);
		$body = $mime->get();
		$hdrs = $mime->headers($hdrs);		

		$params['host'] = $setctl->get('smtphost');
		$params['port'] = $setctl->get('smtpport');
		$params['include_bodies'] = true;
		$params['decode_bodies'] = true;
		$params['decode_headers'] = true;
		$params['auth'] = false;
		$mail =& Mail::factory('smtp', $params);
		$result = $mail->send($mailaddr, $hdrs, $body);
		if (is_object($result)) return false;
		return $result;
	}
	
	function sendmail($from, $sid, $mailaddr, $message)
	{
		global $u_id, $setctl, $streamtypes;
		if (MAILMP3 && db_guinfo('allowemail'))
		{
			$f2 = new file2($sid, true);
			if ($f2->fexists)
			{				
				if (empty($f2->id3['artist'])) $title = $f2->fname; else $title = $f2->id3['artist'].' '.$f2->id3['title'];
				$html = $this->message($title, str_replace("\n", '<br/>', $message));
				$html = str_replace("\n", $this->crlf, $html);
				$subject = 'Requested: '.$title;
				$ftype = file_type($f2->fname);
				if ($ftype != -1) $mimetype = $streamtypes[$ftype][1]; else $mimetype = 'application/octet-stream';

				switch (MAILMETHOD)
				{
					case 2: $status = $this->sendpear($from, $subject, $html, $mimetype, $mailaddr, $f2);
							break;
					case 1: $status = $this->senddirect($from, $subject, $html, $mimetype, $mailaddr, $f2);
							break;
					default: $status = 0; break;
				}
				if ($status) addhistory($u_id, $sid, 2);
				return $status;
			}
		}
	}

	function decide()
	{
		if (isset($_POST['sid'])) $this->setsid($_POST['sid']);
		if (isset($_POST['message'])) $this->setmessage($_POST['message']);
		if (isset($_POST['tomail'])) $this->settomail($_POST['tomail']);	
		$msg = '';
		if (!empty($this->tomail))
		{
			$from = db_guinfo('email');
			if (empty($from)) $msg = get_lang(254); 
			else
			{
				if ($this->sendmail($from, $this->sid, $this->tomail, $this->message)) $msg = get_lang(230); else $msg = get_lang(258);
			}
		}		
		$this->gui($msg);
	}

	function gui($msg = '')
	{
		global $PHP_SELF;
		$f2 = new file2($this->sid, true);
		
		if (empty($f2->id3['artist'])) $title = $f2->fname; else $title = $f2->id3['artist'].' '.$f2->id3['title'];
		
		kprintheader(get_lang(223), 1);
		?>
		<form name="mail" method="post" action="<?php echo $PHP_SELF; ?>">
		<input type="hidden" name="action" value="sendmail"/>
		<input type="hidden" name="sid" value="<?php echo $this->sid; ?>"/>
		<table width="100%" border="0" cellpadding="0" cellspacing="0">

		<?php if (!empty($msg))
		{
			echo '<tr><td colspan="2" height="25" class="notice">'.$msg.'</td></tr>';
		}
		?>
		<tr class="wtext">
			<td colspan="2">
				<img src="<?php echo getimagelink('sendmail.gif'); ?>" alt="<?php echo get_lang(223); ?>" border="0"/>
				<?php echo $title; ?>
			</td>
		</tr>
		<tr>
			<td colspan="2" height="12"></td>
		</tr>
		
		<tr class="wtext">
			<td><?php echo get_lang(227); ?></td>
			<td><input type="text" name="tomail" class="fatbuttom" size="40" value="<?php echo $this->tomail; ?>"/></td>
		</tr>
		<tr>
			<td colspan="2" height="5"></td>
		</tr>
		<tr class="wtext">
			<td><?php echo get_lang(228); ?></td>
			<td><textarea class="fatbuttom" name="message" cols="40" rows="5"><?php echo $this->message; ?></textarea></td>
		</tr>
		<tr>
			<td colspan="2" height="5"></td>
		</tr>
		<tr>
			<td></td>
			<td>
				<input type="button" onclick="javascript: document.mail.send.disabled = true; document.mail.send.value = '<?php echo get_lang(253); ?>'; document.mail.submit();" class="fatbuttom" name="send" value="<?php echo get_lang(229); ?>"/>
				<input type="button" name="Close" value="<?php echo get_lang(27); ?>" onclick="javascript: window.close();" class="fatbuttom" />
			</td>
		</tr>
		</table>
		</form>
		<?php
		kprintend();
	}
}


class fupload
{
	function decide()
	{
		global $setctl;
		$msg = '';
		if (ENABLEUPLOAD && isset($_FILES['fileupload']) && isset($_FILES['fileupload']['name']))
		{
			$path = $setctl->get('uploadpath');
			$msg = get_lang(236);
			if (!empty($path))
			{
				$uploadfile = $path.$this->replace($_FILES['fileupload']['name']);			
				if (move_uploaded_file($_FILES['fileupload']['tmp_name'], $uploadfile)) $msg = get_lang(235);
			}
		}
		$this->view($msg);
	}

	function replace($o) 
	{ 
		$checks = array("/", "\\", ":", "*", "?", "<", ">", "\"", "|");
		foreach ($checks as $clear) $o = str_replace($clear,'',$o); 
		return $o;
	}

	function view($msg = '')
	{
		global $PHP_SELF;
		kprintheader(get_lang(234), 1);
		?>
		<form method="post" name="fupload" enctype="multipart/form-data" action="<?php echo $PHP_SELF; ?>">
		<input type="hidden" name="action" value="fupload"/>
		<input type="hidden" name="fuploader" value="true"/>
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<?php if (!empty($msg)) echo '<tr><td class="notice" colspan="2">'.$msg.'</td></tr>'; ?>
		<tr> 
			<td colspan="2" align="center" class="notice"> 
			<input type="file" name="fileupload" class="fatbuttom" size="35"/>
			</td>
		</tr>
		<tr>
			<td colspan="2" height="5"></td>
		</tr>
		<tr>
			<td align="center" colspan="2">
				<input type="button" name="sbutton" onclick="javascript: document.fupload.sbutton.disabled = true; document.fupload.sbutton.value = '<?php echo get_lang(253); ?>'; document.fupload.submit();" value="<?php echo get_lang(234); ?>" class="fatbuttom"/>&nbsp; 
				<input type="button" name="Close" value="<?php echo get_lang(27); ?>" onclick="javascript: window.close();" class="fatbuttom"/>
			</td>
		</tr>
		</table>
		</form>
		<?php
		kprintend();
	}
}


// Uncomment the folling define if you want the class to automatically
// read the MPEG frame info to get bitrate, mpeg version, layer, etc.
//
// NOTE: This is needed to maintain pre-version 1.0 behavior which maybe
// needed if you are using info that is from the mpeg frame. This includes
// the length of the song.
//
// This is discouraged because it will siginfincantly lengthen script
// execution time if all you need is the ID3 tag info.
define('ID3_AUTO_STUDY', true);

// Uncomment the following define if you want tons of debgging info.
// Tip: make sure you use a <PRE> block so the print_r's are readable.
// define('ID3_SHOW_DEBUG', true);

class id3 {
    /*
     * id3 - A Class for reading/writing MP3 ID3 tags
     * 
     * By Sandy McArthur, Jr. <Leknor@Leknor.com>
     * 
     * Copyright 2001 (c) All Rights Reserved, All Responsibility Yours
     *
     * This code is released under the GNU LGPL Go read it over here:
     * http://www.gnu.org/copyleft/lesser.html
     * 
     * I do make one optional request, I would like an account on or a
     * copy of where this code is used. If that is not possible then
     * an email would be cool.
     * 
     * Warning: I really hope this doesn't mess up your MP3s but you
     * are on your own if bad things happen.
     *
     * Note: This code doesn't try to deal with corrupt mp3s. So if you get
     * incorrect length times or something else it may be your mp3. To fix just
     * re-enocde from the CD. :~)
     * 
     * To use this code first create a id3 object passing the path to the mp3 as the first
     * parameter. Then just access the ID3 fields directly (look below for their names).
     * If you want to update a tag just change the fields and then $id3->write();
     * The methods designed for general use are study(), write(), copy($from), remove(),
     * and genres(). Please read the comment before each method for the specifics of each.
     *
     * eg:
     * 	require_once('class.id3.php');
     *	$id3 = new id3('/path/to/our lady peace - middle of yesterday.mp3');
     *  echo $id3->artists, ' - ', $id3->name;
     *	$id3->comment = 'Go buy some OLP CDs, they rock!';
     *	$id3->write();
     *
     * Change Log:
     *	1.24:	Small change to the write() method because the old way it worked was poorly
     *		thought out. The new write() method has optional parameters. $id3->frameoffset
     *		added which will have the byte offset of the first mpeg frame and $id3->filesize
     *	1.23:	MPEG Frame pasrsion code should be perfect on everything but VBR mp3's.
     *	1.20:	Reimplemented most of the mpeg frame parsing code plus a whole lot more.
     *	1.10:	ID3v1 and v1.1 functionality completed.
     *	1.00:	Decided to rewrite and correct all my poor choices and to implement ID3v1.1
     *		Looking at my old code I'm ashamed I ever released it and called it functional.
     * 
     * TODO:
     *	Implement ID3v2 reader and maybe writer if enought people want it.
     * 
     * The most recent version is available at:
     *	http://Leknor.com/code/
     *
     */

    var $_version = 1.24; // Version of the id3 class


    var $file = false;		// mp3/mpeg file name

    var $id3v1 = false;		// ID3 v1 tag found? (also true if v1.1 found)
    var $id3v11 = false;	// ID3 v1.1 tag found?
    var $id3v2 = false;		// ID3 v2 tag found? (not used yet)

    // ID3v1.1 Fields:
    var $name = '';		// track name
    var $artists = '';		// artists
    var $album = '';		// album
    var $year = '';		// year
    var $comment = '';		// comment
    var $track = 0;		// track number
    var $genre = '';		// genre name
    var $genreno = 255;		// genre number

    // MP3 Frame Stuff
    var $studied = false;	// Was the file studied to learn more info?
    var $mpeg_ver = false;	// version of mpeg
    var $layer = false;		// version of layer
    var $bitrate = false;	// bitrate
    var $crc = false;		// Frames are crc protected?
    var $frequency = 0;		// Frequency
    var $padding = false;	// Frames padded
    var $private = false;	// Private bit set?
    var $mode = '';		// Mode (Stereo etc)
    var $copyright = false;	// Copyrighted?
    var $original = false;	// On Original Media? (never used)
    var $emphasis = '';		// Emphasis (also never used)
    var $filesize = -1;		// Bytes in file
    var $frameoffset = -1;	// Byte at which the first mpeg header was found.

    var $length = false;	// length of mp3 format hh:ss
    var $lengths = false;	// length of mp3 in seconds

    var $error = false;		// if any errors they will be here

    var $debug = false;		// print debugging info?
    var $debugbeg = '<DIV STYLE="margin: 0.5 em; padding: 0.5 em; border-width: thin; border-color: black; border-style: solid">';
    var $debugend = '</DIV>';

    /*
     * id3 constructor - creates a new id3 object and maybe loads a tag
     * from a file.
     *
     * $file - the path to the mp3/mpeg file. When in doubt use a full path.
     * $study - (Optional) - study the mpeg frame to get extra info like bitrate and frequency
     *		You should advoid studing alot of files as it will siginficantly slow this down.
     */
    function id3($file, $study = false) 
	{
	if (defined('ID3_SHOW_DEBUG')) $this->debug = true;
	if ($this->debug) print($this->debugbeg . "id3('$file')<HR>\n");

	if (!empty($file))	
	{
		$this->file = $file;
		$this->_read_v1();

		if ($study or defined('ID3_AUTO_STUDY'))
		 $this->study();

		if ($this->debug) print($this->debugend);
	}
	} // id3($file)

    /*
     * write - update the id3v1 tags on the file.
     *
     * $v1 - if true update/create an id3v1 tag on the file. (defaults to true)
     *
     * Note: If/when ID3v2 is implemented this method will probably get another
     *       parameters.
     */
    function write($v1 = true) {
	if ($this->debug) print($this->debugbeg . "write()<HR>\n");
	if ($v1) {
	    $this->_write_v1();
	}
	if ($this->debug) print($this->debugend);
    } // write()

    /*
     * study() - does extra work to get the MPEG frame info.
     */
    function study() {
	$this->studied = true;
	$this->_readframe();
    } // study()

    /*
     * copy($from) - set's the ID3 fields to the same as the fields in $from
     */
    function copy($from) {
	if ($this->debug) print($this->debugbeg . "copy(\$from)<HR>\n");
	$this->name	= $from->name;
	$this->artists	= $from->artists;
	$this->album	= $from->album;
	$this->year	= $from->year;
	$this->comment	= $from->comment;
	$this->track	= $from->track;
	$this->genre	= $from->genre;
	$this->genreno	= $from->genreno;
	if ($this->debug) print($this->debugend);
    } // copy($from)

    /*
     * remove - removes the id3 tag(s) from a file.
     *
     * $id3v1 - true to remove the tag
     * $id3v2 - true to remove the tag (Not yet implemented)
     */
    function remove($id3v1 = true, $id3v2 = true) {
	if ($this->debug) print($this->debugbeg . "remove()<HR>\n");

	if ($id3v1) {
	    $this->_remove_v1();
	}

	if ($id3v2) {
	    // TODO: write ID3v2 code
	}

	if ($this->debug) print($this->debugend);
    } // remove


    /*
     * _read_v1 - read a ID3 v1 or v1.1 tag from a file
     *
     * $file should be the path to the mp3 to look for a tag.
     * When in doubt use the full path.
     *
     * if there is an error it will return false and a message will be
     * put in $this->error
     */
    function _read_v1() {
	if ($this->debug) print($this->debugbeg . "_read_v1()<HR>\n");

	if (! ($f = fopen($this->file, 'rb')) ) {
	    $this->error = 'Unable to open ' . $file;
	    return false;
	}

	if (fseek($f, -128, SEEK_END) == -1) {
	    $this->error = 'Unable to see to end - 128 of ' . $file;
	    return false;
	}

	$r = fread($f, 128);
	fclose($f);

	if ($this->debug) {
	    $unp = unpack('H*raw', $r);
	    print_r($unp);
	}

	$id3tag = $this->_decode_v1($r);

	if($id3tag) {
	    $this->id3v1 = true;

	    $tmp = explode(Chr(0), $id3tag['NAME']);
	    $this->name = $tmp[0];

	    $tmp = explode(Chr(0), $id3tag['ARTISTS']);
	    $this->artists = $tmp[0];

	    $tmp = explode(Chr(0), $id3tag['ALBUM']);
	    $this->album = $tmp[0];

	    $tmp = explode(Chr(0), $id3tag['YEAR']);
	    $this->year = $tmp[0];

	    $tmp = explode(Chr(0), $id3tag['COMMENT']);
	    $this->comment = $tmp[0];

	    if (isset($id3tag['TRACK'])) {
		$this->id3v11 = true;
		$this->track = $id3tag['TRACK'];
	    }

	    $this->genreno = $id3tag['GENRENO'];
	    $this->genre = $id3tag['GENRE'];
	}

	if ($this->debug) print($this->debugend);
    } // _read_v1()

    /*
     * _decode_v1 - decodes that ID3v1 or ID3v1.1 tag
     *
     * false will be returned if there was an error decoding the tag
     * else an array will be returned
     */
    function _decode_v1($rawtag) {
	if ($this->debug) print($this->debugbeg . "_decode_v1(\$rawtag)<HR>\n");

	if ($rawtag[125] == Chr(0) and $rawtag[126] != Chr(0)) {
	    // ID3 v1.1
	    $format = 'a3TAG/a30NAME/a30ARTISTS/a30ALBUM/a4YEAR/a28COMMENT/x1/C1TRACK/C1GENRENO';
	} else {
	    // ID3 v1
	    $format = 'a3TAG/a30NAME/a30ARTISTS/a30ALBUM/a4YEAR/a30COMMENT/C1GENRENO';
	}

	$id3tag = unpack($format, $rawtag);
	if ($this->debug) print_r($id3tag);

	if ($id3tag['TAG'] == 'TAG') {
	    $id3tag['GENRE'] = $this->getgenre($id3tag['GENRENO']);
	} else {
	    $this->error = 'TAG not found';
	    $id3tag = false;
	}
	if ($this->debug) print($this->debugend);
	return $id3tag;
    } // _decode_v1()


    /*
     * _write_v1 - writes a ID3 v1 or v1.1 tag to a file
     *
     * if there is an error it will return false and a message will be
     * put in $this->error
     */
    function _write_v1() {
	if ($this->debug) print($this->debugbeg . "_write_v1()<HR>\n");

	$file = $this->file;

	if (! ($f = fopen($file, 'r+b')) ) {
	    $this->error = 'Unable to open ' . $file;
	    return false;
	}

	if (fseek($f, -128, SEEK_END) == -1) {
	    $this->error = 'Unable to see to end - 128 of ' . $file;
	    return false;
	}

	$this->genreno = $this->getgenreno($this->genre, $this->genreno);

	$newtag = $this->_encode_v1();

	$r = fread($f, 128);

	if ($this->_decode_v1($r)) {
	    if (fseek($f, -128, SEEK_END) == -1) {
		$this->error = 'Unable to see to end - 128 of ' . $file;
		return false;
	    }
	    fwrite($f, $newtag);
	} else {
	    if (fseek($f, 0, SEEK_END) == -1) {
		$this->error = 'Unable to see to end of ' . $file;
		return false;
	    }
	    fwrite($f, $newtag);
	}
	fclose($f);


	if ($this->debug) print($this->debugend);
    } // _write_v1()

    /*
     * _encode_v1 - encode the ID3 tag
     *
     * the newly built tag will be returned
     */
    function _encode_v1() {
	if ($this->debug) print($this->debugbeg . "_encode_v1()<HR>\n");

	if ($this->track) {
	    // ID3 v1.1
	    $id3pack = 'a3a30a30a30a4a28x1C1C1';
	    $newtag = pack($id3pack,
		    'TAG',
		    $this->name,
		    $this->artists,
		    $this->album,
		    $this->year,
		    $this->comment,
		    $this->track,
		    $this->genreno
			  );
	} else {
	    // ID3 v1
	    $id3pack = 'a3a30a30a30a4a30C1';
	    $newtag = pack($id3pack,
		    'TAG',
		    $this->name,
		    $this->artists,
		    $this->album,
		    $this->year,
		    $this->comment,
		    $this->genreno
			  );
	}

	if ($this->debug) {
	    print('id3pack: ' . $id3pack . "\n");
	    $unp = unpack('H*new', $newtag);
	    print_r($unp);
	}

	if ($this->debug) print($this->debugend);
	return $newtag;
    } // _encode_v1()

    /*
     * _remove_v1 - if exists it removes an ID3v1 or v1.1 tag
     *
     * returns true if the tag was removed or none was found
     * else false if there was an error
     */
    function _remove_v1() {
	if ($this->debug) print($this->debugbeg . "_remove_v1()<HR>\n");

	$file = $this->file;

	if (! ($f = fopen($file, 'r+b')) ) {
	    $this->error = 'Unable to open ' . $file;
	    return false;
	}

	if (fseek($f, -128, SEEK_END) == -1) {
	    $this->error = 'Unable to see to end - 128 of ' . $file;
	    return false;
	}

	$r = fread($f, 128);

	$success = false;
	if ($this->_decode_v1($r)) {
	    $size = filesize($this->file) - 128;
	    if ($this->debug) print('size: old: ' . filesize($this->file));
	    $success = ftruncate($f, $size);	
	    clearstatcache();
	    if ($this->debug) print(' new: ' . filesize($this->file));
	}
	fclose($f);
	if ($this->debug) print($this->debugend);
	return $success;
    } // _remove_v1()

    function _readframe() {
	if ($this->debug) print($this->debugbeg . "_readframe()<HR>\n");

	$file = $this->file;

	if (! ($f = fopen($file, 'rb')) ) {
	    $this->error = 'Unable to open ' . $file;
	    if ($this->debug) print($this->debugend);
	    return false;
	}

	$this->filesize = filesize($file);

	do {
	    while (fread($f,1) != Chr(255)) { // Find the first frame
		//if ($this->debug) echo "Find...\n";
		if (feof($f)) {
		    $this->error = 'No mpeg frame found';
		    if ($this->debug) print($this->debugend);
		    return false;
		}
	    }
	    fseek($f, ftell($f) - 1); // back up one byte

	    $frameoffset = ftell($f);

	    $r = fread($f, 4);
	    // Binary to Hex to a binary sting. ugly but best I can think of.
	    $bits = @unpack('H*bits', $r);
	    $bits =  base_convert($bits['bits'],16,2);
	} while (!$bits[8] and !$bits[9] and !$bits[10]); // 1st 8 bits true from the while
	if ($this->debug) print('Bits: ' . $bits . "\n");

	$this->frameoffset = $frameoffset;

	fclose($f);

	if ($bits[11] == 0) {
	    $this->mpeg_ver = "2.5";
	    $bitrates = array(
		    '1' => array(0, 32, 48, 56, 64, 80, 96, 112, 128, 144, 160, 176, 192, 224, 256, 0),
		    '2' => array(0,  8, 16, 24, 32, 40, 48,  56,  64,  80,  96, 112, 128, 144, 160, 0),
		    '3' => array(0,  8, 16, 24, 32, 40, 48,  56,  64,  80,  96, 112, 128, 144, 160, 0),
			     );
	} else if ($bits[12] == 0) {
	    $this->mpeg_ver = "2";
	    $bitrates = array(
		    '1' => array(0, 32, 48, 56, 64, 80, 96, 112, 128, 144, 160, 176, 192, 224, 256, 0),
		    '2' => array(0,  8, 16, 24, 32, 40, 48,  56,  64,  80,  96, 112, 128, 144, 160, 0),
		    '3' => array(0,  8, 16, 24, 32, 40, 48,  56,  64,  80,  96, 112, 128, 144, 160, 0),
			     );
	} else {
	    $this->mpeg_ver = "1";
	    $bitrates = array(
		    '1' => array(0, 32, 64, 96, 128, 160, 192, 224, 256, 288, 320, 352, 384, 416, 448, 0),
		    '2' => array(0, 32, 48, 56,  64,  80,  96, 112, 128, 160, 192, 224, 256, 320, 384, 0),
		    '3' => array(0, 32, 40, 48,  56,  64,  80,  96, 112, 128, 160, 192, 224, 256, 320, 0),
			     );
	}
	if ($this->debug) print('MPEG' . $this->mpeg_ver . "\n");

	$layer = array(
		array(0,3),
		array(2,1),
		      );
	$this->layer = $layer[$bits[13]][$bits[14]];
	if ($this->debug) print('layer: ' . $this->layer . "\n");

	if ($bits[15] == 0) {
	    // It's backwards, if the bit is not set then it is protected.
	    if ($this->debug) print("protected (crc)\n");
	    $this->crc = true;
	}

	$bitrate = 0;
	if ($bits[16] == 1) $bitrate += 8;
	if ($bits[17] == 1) $bitrate += 4;
	if ($bits[18] == 1) $bitrate += 2;
	if ($bits[19] == 1) $bitrate += 1;
	$this->bitrate = @$bitrates[$this->layer][$bitrate];

	$frequency = array(
		'1' => array(
		    '0' => array(44100, 48000),
		    '1' => array(32000, 0),
			    ),
		'2' => array(
		    '0' => array(22050, 24000),
		    '1' => array(16000, 0),
			    ),
		'2.5' => array(
		    '0' => array(11025, 12000),
		    '1' => array(8000, 0),
			      ),
		  );
	$this->frequency = $frequency[$this->mpeg_ver][$bits[20]][$bits[21]];

	$this->padding = $bits[22];
	$this->private = $bits[23];

	$mode = array(
		array('Stereo', 'Joint Stereo'),
		array('Dual Channel', 'Mono'),
		     );
	$this->mode = $mode[$bits[24]][$bits[25]];

	// XXX: I dunno what the mode extension is for bits 26,27

	$this->copyright = $bits[28];
	$this->original = $bits[29];

	$emphasis = array(
		array('none', '50/15ms'),
		array('', 'CCITT j.17'),
			 );
	$this->emphasis = $emphasis[$bits[30]][$bits[31]];

	if ($this->bitrate == 0) {
	    $s = -1;
	} else {
	    $s = ((8*filesize($this->file))/1000) / $this->bitrate;        
	}
	$this->length = sprintf('%02d:%02d',floor($s/60),floor($s-(floor($s/60)*60)));
	$this->lengths = (int)$s;

	if ($this->debug) print($this->debugend);
    } // _readframe()

    /*
     * getgenre - return the name of a genre number
     *
     * if no genre number is specified the genre number from
     * $this->genreno will be used.
     *
     * the genre is returned or false if an error or not found
     * no error message is ever returned
     */
    function getgenre($genreno) {
	if ($this->debug) print($this->debugbeg . "getgenre($genreno)<HR>\n");

	$genres = $this->genres();
	if (isset($genres[$genreno])) {
	    $genre = $genres[$genreno];
	    if ($this->debug) print($genre . "\n");
	} else {
	    $genre = '';
	}

	if ($this->debug) print($this->debugend);
	return $genre;
    } // getgenre($genreno)

    /*
     * getgenreno - return the number of the genre name
     *
     * the genre number is returned or 0xff (255) if a match is not found
     * you can specify the default genreno to use if one is not found
     * no error message is ever returned
     */
    function getgenreno($genre, $default = 0xff) {
	if ($this->debug) print($this->debugbeg . "getgenreno('$genre',$default)<HR>\n");

	$genres = $this->genres();
	$genreno = false;
	if ($genre) {
	    foreach ($genres as $no => $name) {
		if (strtolower($genre) == strtolower($name)) {
		    if ($this->debug) print("$no:'$name' == '$genre'");
		    $genreno = $no;
		}
	    }
	}
	if ($genreno === false) $genreno = $default;
	if ($this->debug) print($this->debugend);
	return $genreno;
    } // getgenreno($genre, $default = 0xff)

    /*
     * genres - reuturns an array of the ID3v1 genres
     */
    function genres() {
	return array(
		0   => 'Blues',
		1   => 'Classic Rock',
		2   => 'Country',
		3   => 'Dance',
		4   => 'Disco',
		5   => 'Funk',
		6   => 'Grunge',
		7   => 'Hip-Hop',
		8   => 'Jazz',
		9   => 'Metal',
		10  => 'New Age',
		11  => 'Oldies',
		12  => 'Other',
		13  => 'Pop',
		14  => 'R&B',
		15  => 'Rap',
		16  => 'Reggae',
		17  => 'Rock',
		18  => 'Techno',
		19  => 'Industrial',
		20  => 'Alternative',
		21  => 'Ska',
		22  => 'Death Metal',
		23  => 'Pranks',
		24  => 'Soundtrack',
		25  => 'Euro-Techno',
		26  => 'Ambient',
		27  => 'Trip-Hop',
		28  => 'Vocal',
		29  => 'Jazz+Funk',
		30  => 'Fusion',
		31  => 'Trance',
		32  => 'Classical',
		33  => 'Instrumental',
		34  => 'Acid',
		35  => 'House',
		36  => 'Game',
		37  => 'Sound Clip',
		38  => 'Gospel',
		39  => 'Noise',
		40  => 'Alternative Rock',
		41  => 'Bass',
		42  => 'Soul',
		43  => 'Punk',
		44  => 'Space',
		45  => 'Meditative',
		46  => 'Instrumental Pop',
		47  => 'Instrumental Rock',
		48  => 'Ethnic',
		49  => 'Gothic',
		50  => 'Darkwave',
		51  => 'Techno-Industrial',
		52  => 'Electronic',
		53  => 'Pop-Folk',
		54  => 'Eurodance',
		55  => 'Dream',
		56  => 'Southern Rock',
		57  => 'Comedy',
		58  => 'Cult',
		59  => 'Gangsta',
		60  => 'Top 40',
		61  => 'Christian Rap',
		62  => 'Pop/Funk',
		63  => 'Jungle',
		64  => 'Native US',
		65  => 'Cabaret',
		66  => 'New Wave',
		67  => 'Psychadelic',
		68  => 'Rave',
		69  => 'Showtunes',
		70  => 'Trailer',
		71  => 'Lo-Fi',
		72  => 'Tribal',
		73  => 'Acid Punk',
		74  => 'Acid Jazz',
		75  => 'Polka',
		76  => 'Retro',
		77  => 'Musical',
		78  => 'Rock & Roll',
		79  => 'Hard Rock',
		80  => 'Folk',
		81  => 'Folk-Rock',
		82  => 'National Folk',
		83  => 'Swing',
		84  => 'Fast Fusion',
		85  => 'Bebob',
		86  => 'Latin',
		87  => 'Revival',
		88  => 'Celtic',
		89  => 'Bluegrass',
		90  => 'Avantgarde',
		91  => 'Gothic Rock',
		92  => 'Progressive Rock',
		93  => 'Psychedelic Rock',
		94  => 'Symphonic Rock',
		95  => 'Slow Rock',
		96  => 'Big Band',
		97  => 'Chorus',
		98  => 'Easy Listening',
		99  => 'Acoustic',
		100 => 'Humour',
		101 => 'Speech',
		102 => 'Chanson',
		103 => 'Opera',
		104 => 'Chamber Music',
		105 => 'Sonata',
		106 => 'Symphony',
		107 => 'Booty Bass',
		108 => 'Primus',
		109 => 'Porn Groove',
		110 => 'Satire',
		111 => 'Slow Jam',
		112 => 'Club',
		113 => 'Tango',
		114 => 'Samba',
		115 => 'Folklore',
		116 => 'Ballad',
		117 => 'Power Ballad',
		118 => 'Rhytmic Soul',
		119 => 'Freestyle',
		120 => 'Duet',
		121 => 'Punk Rock',
		122 => 'Drum Solo',
		123 => 'Acapella',
		124 => 'Euro-House',
		125 => 'Dance Hall',
		126 => 'Goa',
		127 => 'Drum & Bass',
		128 => 'Club-House',
		129 => 'Hardcore',
		130 => 'Terror',
		131 => 'Indie',
		132 => 'BritPop',
		133 => 'Negerpunk',
		134 => 'Polsk Punk',
		135 => 'Beat',
		136 => 'Christian Gangsta Rap',
		137 => 'Heavy Metal',
		138 => 'Black Metal',
		139 => 'Crossover',
		140 => 'Contemporary Christian',
		141 => 'Christian Rock',
		142 => 'Merengue',
		143 => 'Salsa',
		144 => 'Trash Metal',
		145 => 'Anime',
		146 => 'Jpop',
		147 => 'Synthpop'
		    );
    } // genres
} // end of id3


// THIS IS BETA. DO NOT USE UNLESS YOU ENJOY THE RISK OF VERY BAD THINGS
// HAPPENING TO YOUR DATA. That said, it doesn't write anything so it
// should be harmless. This has been tested on a few ogg files I've
// created for testing but that is it.

// Uncomment the following define if you want tons of debgging info.
// Tip: make sure you use a <PRE> block so the print_r's are readable.
// define('OGG_SHOW_DEBUG', true);

class ogg {
    /*
     * ogg - A Class for reading Ogg comment tags
     * 
     * By Sandy McArthur, Jr. <Leknor@Leknor.com>
     * 
     * Copyright 2001 (c) All Rights Reserved, All Responsibility Yours
     *
     * This code is released under the GNU LGPL Go read it over here:
     * http://www.gnu.org/copyleft/lesser.html
     * 
     * I do make one optional request, I would like an account on or a
     * copy of where this code is used. If that is not possible then
     * an email would be cool.
     * 
     * Warning: I really hope this doesn't mess up your Ogg files but you
     * are on your own if bad things happen.
     *
     * To use this code first create a new instance on a file. Then loop
     * though the $ogg->fields array. Inside that loop, loop again. The
     * ogg comment format allows mome then one field with the same name
     * so it is possible for the ARTIST fields to appear twice if a work
     * has two performers.
     *
     * eg:
     *	require_once('class.ogg.php');
     *	$ogg = new ogg('/path/to/filename.ogg');
     *	echo '<UL>';
     *	foreach ($ogg->fields AS $name => $val) {
     *	    echo "<LI>$name:<OL>";
     *	    foreach ($val AS $contents) {
     *		echo '<LI>', $contents;
     *	    }
     *	    echo '</OL>';
     *	}
     *	echo '</UL>';
     *
     * This site was useful to me:
     *	http://www.xiph.org/ogg/vorbis/docs.html
     *
     * Change Log:
     *	0.10:	Clean up for release.
     *	0.01:	Got off my ass and wrote something until it works enough.
     * 
     * Thanks To:
     *
     * TODO:
     *	Collect nifty info like bitrate, etc...
     *	Maybe implement ogg comment writer. We'll see I don't like using php
     *	    to manipulate large amounts of data.
     * 
     * The most recent version is available at:
     *	http://Leknor.com/code/
     *
     */

    var $_version = 0.10;	// Version of the ogg class (float, not major/minor)

    var $file = false;		// ogg file name (you should never modify this)

    var $fields = array();	// comments fields, this is a two dimentional array.
    var $_rawfields = array();	// The comments fields read and split but not orgainzed.

    var $error = false;		// Check here for an error message
    var $debug = false;		// print debugging info?
    var $debugbeg = '<DIV STYLE="margin: 0.5 em; padding: 0.5 em; border-width: thin; border-color: black; border-style: solid">';
    var $debugend = '</DIV>';

    /*
     * ogg constructor - creates a new ogg object
     *
     * $file - the path to the ogg file. When in doubt use a full path.
     */
    function ogg($file) {
	if (defined('OGG_SHOW_DEBUG')) $this->debug = true;
	if ($this->debug) print($this->debugbeg . "ogg('$file')<HR>\n");

	$this->file = $file;
	$this->_read();

	if ($this->debug) print($this->debugend);
    } // ogg($file)

    /*
     * _read() - finds the comment in a ogg stream. You should not call this.
     */
    function _read() {
	if ($this->debug) print($this->debugbeg . "_read()<HR>\n");

	if (! ($f = fopen($this->file, 'rb')) ) {
	    $this->error = 'Unable to open ' . $file;
	    if ($this->debug) print("<B>$this->error</B>$this->debugend");
	    return false;
	}

	$this->_find_page($f);
	$this->_find_page($f);

	fseek($f, 26 - 4, SEEK_CUR);
	$segs = fread($f, 1);
	$segs = unpack('C1size', $segs);
	$segs = $segs['size'];
	if ($this->debug) print("segs: $segs<BR>");
	fseek($f, $segs, SEEK_CUR);

	// Skip preable
	//$r = fread($f, 1);
	//print_r(unpack('H*raw', $r));
	fseek($f, 7, SEEK_CUR);

	// Skip Vendor
	$size = fread($f, 4);
	$size = unpack('V1size', $size);
	$size = $size['size'];
	if ($this->debug) print("vendor size: $size<BR>");
	fseek($f, $size, SEEK_CUR);

	// Comments
	$comments = fread($f, 4);
	$comments = unpack('V1comments', $comments);
	$comments = $comments['comments'];
	if ($this->debug) print("Comments: $comments<BR>");
	for ($i=0; $i < $comments; $i++) {
	    $size = fread($f, 4);
	    $size = unpack('V1size', $size);
	    $size = $size['size'];
	    if ($this->debug) print("comment size: $size<BR>");

	    $comment = fread($f, $size);
	    if ($this->debug) print("comment: $comment<BR>");
	    $comment = explode('=', $comment, 2);
	    $this->fields[strtoupper($comment[0])][] = $comment[1];
	    $this->_rawfields[] = $comment;
	}

	if ($this->debug) print($this->debugend);
    } // _read()

    /*
     * _find_page - seeks to the next ogg page start.
     */
    function _find_page(&$f) {
	if ($this->debug) print($this->debugbeg . "_find_page($f)<HR>\n");

	$header = 'OggS'; // 0xf4 . 0x67 . 0x 67 . 0x53
	$bytes = fread($f, 4);

	while ($header != $bytes) {
	    //if ($this->debug) print('.');
	    $bytes = substr($bytes, 1);
	    $bytes .= fread($f, 1);
	}

	if ($this->debug) {
	    echo 'Page found at byte: ', ftell($f) - 4, '<BR>';
	    print($this->debugend);
	}
    } // _find_page(&$file)

} // end of class ogg


$themes[0] = array('standard','
body
{
	background-color: #FFFFFF;
	color: #000000;
	margin-top: 10px;
	margin-left: 10px;
	margin-right: 5px;
	margin-bottom: 5px;
	padding-top: 0px;
	padding-right: 0px;
	padding-bottom: 0px;
	padding-left: 0px
}
.smalltext
{
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: xx-small; background-color: #FFFFFF;
	color: #003333
}
.row2nd
{
	background-color : #dcdef4
}
.tdlogin
{
	background-color : #000000
}
.logintext
{
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: xx-small;
	color: #FFFFFF;
	background-color: #000000
}
.loginkplaylist
{
	color: #BBBBBB;
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px
}
.statistics
{
	color: #000000;
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 8px
}
a:hover.hot
{
	color: #EF610C;
	text-decoration: underline;
	font-style: normal
}
.warning
{
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: xx-small;
	font-style: normal
}
.notice
{
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: xx-small;
	font-style: normal;
	color: #000000
}
.fatbuttom
{
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: xx-small;
	background-color: #FFFFFF;
	border: 1px #000000;
	border-style: solid
}
.fatfield
{
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: xx-small;
	background-color: #dcdef4;
	border: 1px #000000;
	border-style: solid
}
.logonbuttom
{
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: xx-small;
	background-color: #000000;
	border: 1px #CCCCCC solid;
	color: #FFFFFF
}
.wtext
{
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: xx-small;
	font-style: normal;
	color: #000000
}
.text
{
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: x-small;
	font-style: normal;
	color: #FFFFFF
}
.dir
{
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: x-small;
	font-style: normal;
	color: #030670
}
.finfo
{
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: xx-small;
	font-style: normal;
	color: #333333
}
a
{
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: xx-small;
	font-style: normal;
	color: #000000;
	text-decoration: none
}
.file
{
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: xx-small;
	font-style: normal;
	color: #000066
}
.filemarked
{
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: xx-small;
	font-style: normal;
	color: #EF6100
}
.curdir
{
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: x-small;
	color: #000000;
	text-decoration: none
}
.userfield
{
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: xx-small
}
.boxhotlist
{
	color: #BBBBBB;
	background-color: #EF6100;
	border-style: solid;
	border-top-width: 0px;
	border-right-width: 0px;
	border-bottom-width: 1px;
	border-left-width: 1px
}
.box
{
	color: #BBBBBB;
	background-color: #4F35B3;
	border-style: solid;
	border-top-width: 0px;
	border-right-width: 0px;
	border-bottom-width: 1px;
	border-left-width: 1px
}
.tdborder
{
	border-color: black black black #666666;
	border-style: solid;
	border-top-width: 0px;
	border-right-width: 0px;
	border-bottom-width: 1px;
	border-left-width: 0px
}
.importnant
{
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10pt;
	font-style: normal;
	color: #000000
}
.header
{
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10pt;
	font-style: normal;
	color: #000000
}
.headermarked
{
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10pt;
	font-style: normal;
	color: #EF6100
}
.bbox
{
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: xx-small;
	font-style: normal;
	color: #FFFFFF
}
.bboxtable
{
	background-color: #FFFFFF
}
');


function klogon($msg = '')
{
	kprintheader(get_lang(29), 1); 
	kprintlogin($msg);
	kprintend(); 
	die();
}

function errormessage($msg, $back = true)
{
	kprintheader(get_lang(56),0);
	if ($back) $code = '<a href="javascript:history.go(-1)" class="fatbuttom">&nbsp;'.get_lang(34).'&nbsp;</a>'; else $code = '';
	blackbox(get_lang(56),'<br/>'.$msg.'<br/><br/>'.$code.'<br/><br/>',0);
	kprintend();
	die();
}

function mp3table($pdir="")
{
	global $PHP_SELF, $pdir64;
	$pdir64 = base64_encode($pdir);
	?>
	<table width="100%" border="0" align="left" cellspacing="0" cellpadding="0">
	<tr>
	<td align="left" width="70%" valign="top">
	<form name="psongs" action="<?php echo $PHP_SELF?>" method="post">
	<input type="hidden" name="action" value="listedres"/>
	<input type="hidden" name="previous" value="<?php echo $pdir64; ?>"/>
	<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
	<td>
	<?php
}

function compute_statistics()
{
	$row = mysql_fetch_array(db_execquery('SELECT SUM(lengths) AS ls, COUNT(*) AS nr, SUM(fsize) AS fs FROM '.TBL_SEARCH), true);
	if ($row)
	{
		$secs = (int)$row['ls'];
		$days = floor($secs/86400);
		$secs = $secs % 86400;
		$hours = floor($secs/3600);
		$secs = $secs % 3600;
		$min = floor($secs/60);
		$mb = floor($row['fs'] / 1048576);
		return get_lang(155,$days, $hours, $min, $row['nr'],$mb);
	}
}

function endmp3table($showalbum=1, $dirs=0, $files=0) 
{ 
	global $u_id, $PHP_SELF, $curdrive, $cfg, $pdir64;
	echo '<tr><td>&nbsp;</td></tr>';	
	$crstr = '';
	$crstr_dl = null;
	$text = '';
	if ($showalbum)
	{
		$crstr .= '<input type="submit" name="psongsall" value="'; 
		if ($files == 1 && $dirs == 0) $text = get_lang(65); else
		if ($files > 0 && $dirs == 0) $text = get_lang(66); else
		if ($files > 0 && $dirs > 0) $text = get_lang(67);
		$crstr .= $text.'" class="fatbuttom"/>&nbsp;&nbsp;';
		$crstr_dl = '<input type="button" name="pdlall" value="'.$text.'" onclick="javascript: newwin(\'dlall\', \''.$PHP_SELF.'?action=dlall&amp;p='.$pdir64.'&amp;d='.$curdrive.'\', 130, 450);" class="fatbuttom"/>&nbsp;&nbsp;'; 
	} 	
	
	$crstr .= '<input type="submit" onclick="javascript: if (!anyselected()) { alert(\''.get_lang(159).'\'); return false; }" name="psongsselected" value="'.get_lang(68).'" class="fatbuttom"'."/>";
	$crstr_dl .= '<input type="button" onclick="javascript: if (!anyselected()) alert(\''.get_lang(159).'\'); else newwin(\'dlselected\', \''.$PHP_SELF.'?action=dlselectedjs\', 130, 450);" name="pdlselected" value="'.get_lang(68).'" class="fatbuttom"'."/>";

	$playlists = db_getplaylist($u_id);
	$ploutput = "";
	if (count($playlists)>0)
	{
		if ($files > 0) $ploutput .= '<input type="submit" name="addplaylist" onclick="javascript: if (!anyselected()) { alert(\''.get_lang(32).'\'); return false; }" value="'.get_lang(69).'" class="fatbuttom"/>&nbsp;';
		$ploutput .= '<select name="sel_playlist" class="file">';
		
		$playid = db_guinfo("defplaylist");
		for ($c=0,$cnt=count($playlists);$c<$cnt;$c++) 
		{		
			if ($playlists[$c][1] == $playid) $sel=' selected="selected" '; else $sel='';
			$ploutput .= '<option value="'. $playlists[$c][1].'"'.$sel.'>'.$playlists[$c][0].'</option>';
		}
		$ploutput .= '</select>';
	}
	$ploutput .= '<input type="hidden" name="drive" value="'.$curdrive.'"/>';
	if (count($playlists)>0)
	{
		$ploutput .= '<input type="submit" name="playplaylist" value="'.get_lang(70).'" class="fatbuttom"/>&nbsp;';
		$ploutput .= '<input type="submit" name="editplaylist" value="'.get_lang(71).'" class="fatbuttom"/>&nbsp;';
	}
	
	$upload = '<input type="button" name="upload" onclick="javascript: newwin(\'upload\', \''.$PHP_SELF.'?action=fupload\', 120, 450);" value="'.get_lang(69).'" class="fatbuttom"/>';

	$ploutput .= "<input type=\"button\" name=\"newplaylist\" onclick=\"javascript: newwin('playlist', '$PHP_SELF?action=playlist_new', 100, 350);\" value=\"".get_lang(72)."\" class=\"fatbuttom\"".'/>';
	$selectallcode='<input type="button" value="+" class="fatbuttom" onclick="javascript: SelectAll();"'."/>".'&nbsp;&nbsp;<input type="button" value="-" class="fatbuttom" onclick="javascript: SelectAll();"'."/>";
	
	?>	
	<tr>
	<td>
	<table border="0" cellspacing="5" cellpadding="0">	
		<tr>
		<?php
		if ($files > 0)
		{
			echo '<td align="left"> '.blackbox(get_lang(73), $selectallcode).'</td>';
			echo '<td align="left"> '.blackbox(get_lang(74), $crstr).'</td>';
			if (ALLOWDOWNLOAD && db_guinfo('u_allowdownload') && $cfg['archivemode']) echo '<td align="left"> '.blackbox(get_lang(117), $crstr_dl).'</td>';
		}
		echo '<td align="left">'.blackbox(get_lang(75), $ploutput).'</td>';
		if (ENABLEUPLOAD) echo '<td align="left">'.blackbox(get_lang(234), $upload).'</td>';
		?>
		</tr>
	</table>
	</td></tr>
	</table>
	</form>
	</td>
	<?php		
}

function infobox()
{
 	global $PHP_SELF, $u_cookieid, $u_id, $app_ver, $setctl, $u_id, $u_playlist, $u_playlistid, $pdir64, $app_build, $homepage;

	$homepage = str_replace('KBUILD', $app_build, str_replace('KVER', $app_ver, $setctl->get('homepage')));

	?>
	<td valign="top" align="left" width="30%">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td colspan="2">		
		<?php if ($setctl->get('showkeyteq')) 
		{
			?><span class="notice"><?php echo '<a href="http://keyteq.no" target="_blank">'.substr(get_lang(77),0,3).'</a>'.substr(get_lang(77),3); ?></span><?php
		}
		?>		
		<?php if ($setctl->get('showupgrade')) 
		{
			?><a title="<?php echo get_lang(120); ?>" href="http://www.kplaylist.net/?ver=<?php echo $app_ver; ?>&amp;build=<?php echo $app_build; ?>" target="_blank">
			<font color="#CCCCCC"><?php echo get_lang(78); ?></font></a><br/><?php
		} else if ($setctl->get('showkeyteq')) echo "<br/>"; ?>
		<a title="<?php echo get_lang(79); ?>" href="<?php echo $homepage; ?>" target="_blank">
		<img alt="<?php echo get_lang(79); ?>" src="<?php echo getimagelink('kplaylist.gif'); ?>" border="0"/><span class="notice">v<?php echo $app_ver.' '.$app_build; ?></span></a>
		</td>    
	</tr>	
	</table>	
	<form name="search" action="<?php echo $PHP_SELF; ?>" method="post">
	<input type="hidden" name="action" value="search"/>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">		
	<?php
	if ($setctl->get('showstatistics'))
	{
		?>
		<tr>
		<td colspan="4">
		<font class="statistics">&nbsp;<?php echo compute_statistics(); ?></font>
		</td>
		</tr>
		<?php
	}
	?>		
	<tr>
		<td colspan="4">&nbsp;<input type="text" name="searchfor" value='<?php if (isset($_POST['searchfor'])) echo stripslashes(checkchs($_POST['searchfor'])); ?>' maxlength="150" size="35" class="fatbuttom"/></td>		
	</tr>
	<tr>
		<td colspan="4">
			<input type="radio" name="search" value="0" <?php if (db_guinfo('defaultsearch')=='0') echo 'checked="checked"';?>/><font class="notice"><?php echo get_lang(81); ?>&nbsp;</font>
			<input type="radio" name="search" value="1" <?php if (db_guinfo('defaultsearch')=='1') echo 'checked="checked"';?>/><font class="notice"><?php echo get_lang(82); ?>&nbsp;</font>
			<input type="radio" name="search" value="2" <?php if (db_guinfo('defaultsearch')=='2') echo 'checked="checked"';?>/><font class="notice"><?php echo get_lang(83); ?></font>
		</td>
		
	</tr>
	<tr>
		<td height="5" colspan="4"></td>
	</tr>
	<tr>
		<td colspan="4" align="left">
			<input type="checkbox" name="onlyid3" value="1" <?php if (db_guinfo('defaultid3')) echo ' checked="checked"'; ?>/>
			<font class="notice"><?php echo get_lang(80); ?></font>			
			&nbsp;
			<select class="fatbuttom" name="hitsas">
			<option value="0"<?php if (db_guinfo('hitsas') == 0) echo 'selected="selected"'; ?>><?php echo get_lang(185); ?></option>
			<option value="1"<?php if (db_guinfo('hitsas') == 1) echo 'selected="selected"'; ?>><?php echo get_lang(186); ?></option>
			</select>
			<font class="notice"><?php echo get_lang(184); ?></font>
		</td>		
	</tr>
	<tr>
		<td height="5" colspan="4"></td>
	</tr>
	<tr>
		<td colspan="4" align="left">&nbsp;<input type="submit" name="startsearch" value="<?php echo get_lang(5); ?>" class="fatbuttom"/></td>
	</tr>
	<tr>		
		<td colspan="4"></td>  
	</tr>	
	<tr><td colspan="4">&nbsp;</td></tr>
	
	<tr>
		<td class="finfo" colspan="4" align="left">

		<script type="text/javascript">
			<!--
			document.search.searchfor.focus();
			// -->
		</script>
		<?php blackbox(get_lang(84), album_hotlist('artist'), 0, 'boxhotlist'); ?>
		</td>
	</tr>
	<?php if (class_exists('kbulletin') && BULLETIN)
	{
	?>
	<tr>
		<td height="15"></td>
	</tr>
	<tr>
		<td><?php 
				$kb = new kbulletin();
				blackbox(get_lang(268), $kb->getlatest(), 0, 'box','','60%'); ?>
			</td>
	</tr>
	<?php
	}
	?>
	</table>
	</form>
	<?php		
		db_sharedplaylist($u_id);
		$ploutput = '';
		if (count($u_playlist)>0)
		{
			$ploutput .= '&nbsp;<input type="hidden" name="action" value="playlist"/>';
			$ploutput .= '<input type="hidden" name="previous" value="'.$pdir64.'"/>';
			$ploutput .= '<select name="sel_shplaylist" class="file">';

			$playid = db_guinfo('defshplaylist');
			for ($c=0;$c<count($u_playlist);$c++) 
			{
				if ($u_playlistid[$c] == $playid) $sel=' selected="selected" '; else $sel='';
				$ploutput .= '<option value="'. $u_playlistid[$c] . '"'.$sel.'>'.$u_playlist[$c].'</option>';
			}
			$ploutput .= '</select>';
			$ploutput .= '<input type="submit" name="playplaylist" value="'.get_lang(70).'" class="fatbuttom"/>&nbsp;';
			$ploutput .= '<input type="submit" name="viewplaylist" value="'.get_lang(85).'" class="fatbuttom"/>&nbsp;';
		}

		if (!empty($ploutput))
		{
			?>
			<form name="sharedplaylist" action="<?php echo $PHP_SELF?>" method="post">
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td colspan="2"><?php echo blackbox(get_lang(86), $ploutput); ?></td>
			</tr>
			</table>
			</form>
			<?php 
		}
		?>

	<form name="misc" action="<?php echo $PHP_SELF?>" method="post">
	<input type="hidden" name="action" value="misc"/>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<?php
		
		if (db_guinfo('u_access') == 0)
		{
			?>
			<tr>
				<td align="left" colspan="2">
			<?php
			$admincode='&nbsp;<input type="button" name="action" value="'.get_lang(87).'" class="fatbuttom" onclick="javascript: openwin(\'Users\', \''.$PHP_SELF.'?action=showusers\');"/>
			<input type="button" name="updatesearch" value="'.get_lang(15).'" class="fatbuttom" onclick="javascript: openwin(\'Update\', \''. $PHP_SELF.'?action=updateoptions\');"/>&nbsp;'.
			'<input type="button" name="settings" value="'.get_lang(126).'" class="fatbuttom" onclick="javascript: newwin(\'Settings\',\''.
			$PHP_SELF.'?action=settingsview\',395,675);"/>&nbsp;';
			
			echo blackbox(get_lang(88),$admincode); ?>
			</td></tr>
			<tr><td colspan="2" height="12"></td></tr>
		<?php } 

		$genres = '';
		
		if (class_exists('id3'))
		{
			$genres = genre_select(true,db_guinfo('defgenre'));
			$genres .= '&nbsp;<input type="submit" class="fatbuttom" name="genrelist" value="'.get_lang(154).'"/>';
		}
		
		$othercode = '&nbsp;<input type="submit" name="whatsnew" value="'.get_lang(89).'" class="fatbuttom"/>&nbsp;';

		$othercode .= '<input type="submit" name="whatshot" value="'.get_lang(90).'" class="fatbuttom"/>&nbsp;';


		$usermisc = '&nbsp;<input type="submit" name="logmeout" value="'.get_lang(91).'" class="fatbuttom"/>&nbsp;'.
					'<input type="button" name="editoptions" value="'.get_lang(92).'" class="fatbuttom" '. 'onclick="javascript: newwin(\'Options\', \''.$PHP_SELF.'?action=editoptions\',285,550);"/>&nbsp;';
		$usermisc .=  '<input type="button" name="randomizer" value="'.get_lang(212).'" class="fatbuttom" '. 'onclick="javascript: newwin(\'Randomizer\', \''.$PHP_SELF.'?action=showrandomizer\',350,550);"/>&nbsp;';

		?>
		<tr>
		<td colspan="2"><?php echo blackbox(get_lang(93), $othercode); ?></td>
		</tr>

		<tr><td colspan="2" height="12"></td></tr>
		
		<?php
		if (!empty($genres))
		{
			?>
			<tr>
			<td colspan="2"><?php echo blackbox(get_lang(147), $genres,1); ?></td>
			</tr>
			<?php
		}
		?>
		<tr><td colspan="2" height="12"></td></tr>
		<tr>
			<td colspan="2"><?php echo blackbox(get_lang(94), $usermisc,1); ?></td>
		</tr>
		</table>
		</form>
		</td>
<?php
}

function kprintheader($title='',$js_out=0)
{
	global $deflanguage, $klang, $setctl, $app_build;
	if (empty($title)) $title = '| kPlaylist'; else $title = '| '.$title;	
	if ($setctl->get('includeheaders', 1, 1)) 
	{
	?>
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html>
		<head>
		<title><?php echo $title; ?></title>
		<!-- kp build <?php echo $app_build; ?> -->
		<meta http-equiv="Content-Type" content="text/html; charset=<?php echo get_lang(1); ?>"/>
		<?php echo kprintcss(); 
	}
	$extjs = $setctl->get('externaljavascript');
	if (empty($extjs)) outjavascripts($js_out); else echo '<script type="text/javascript" src="'.$extjs.'"></script>';

	if ($setctl->get('includeheaders', 1, 1))
	{
		?>
		</head>
		<body>
		<?php
	}
}

function kprintcss()
{
	global $themes, $setctl;
	if ($setctl->get('includeheaders', 1, 1))
	{
		$css = $setctl->get('externalcss', '', 0); 
		if (!empty($css))
		{
			?>
			<link href="<?php echo $css; ?>" rel="stylesheet" type="text/css" />
			<?php
		} else 
		{
			if (is_array($themes))
			{
				?>
				<style type="text/css">
				<?php echo $themes[0][1]; ?>
				</style>
				<?php
			}
		}
	}	
}

function kprintend()
{
	global $setctl;
	if ($setctl->get('includeheaders', 1, 1)) echo '</body></html>';
}

function blackbox($title,$code,$returncode=1,$class='box',$textalign='center',$width=0)
{
	$mix = '<table class="'.$class.'" border="0" cellspacing="0" cellpadding="0"';
	if ($width != 0) $mix .= ' width="'.$width.'"';
	$mix .= 
	'><tr><td class="bbox"><b>&nbsp;'.$title.'&nbsp;</b></td></tr><tr><td class="notice">'.
	'<table class="bboxtable" border="0" cellspacing="0" width="100%">'.
	'<tr><td width="100%"';
	if ($textalign != '') $mix .= ' align="'.$textalign.'"';
	$mix .= '>'.$code.'</td></tr></table>'.
	'</td></tr></table>';
	if (!$returncode) echo $mix; else return($mix);
}

function blackboxpart($title, $pos)
{
	$data = blackbox($title, '%code');
	$p = strpos($data, '%code');
	if ($p !== false)
	{
		if ($pos == 1) return substr($data, 0, $p);
			else return substr($data, $p+5);
	}
}

function outjavascripts()
{
	?>
	<script type="text/javascript">
	<!--
	function openwin(name, url) 
	{
		popupWin = window.open(url, name, 'resizable=yes,scrollbars=yes,status=no,toolbar=no,menubar=no,width=675,height=320,left=150,top=270');
		if (popupWin) popupWin.focus();
	}

	function newwin(name, url, height, width) 
	{
		popupWin = window.open(url, name, 'resizable=no,scrollbars=no,status=no,toolbar=no,menubar=no,width='+width+',height='+height+',left=250,top=270');
		if (popupWin) popupWin.focus();
	}

	function SelectAll() 
	{
		for(var i=0;i<document.psongs.elements.length;i++) 
		{
			if(document.psongs.elements[i].type == "checkbox")
			{
				if (document.psongs.elements[i].checked == false) document.psongs.elements[i].checked = true; 
					else
				if (document.psongs.elements[i].checked == true) document.psongs.elements[i].checked = false;
			}
		}
	}	

	function anyselected()
	{
		for(var i=0;i<document.psongs.elements.length;i++) if(document.psongs.elements[i].type == "checkbox") if (document.psongs.elements[i].checked == true) return true;
		return false;
	}

	function chhttp(where) { 
		document.location = where;
	}
	//-->
	</script>
	<?php
}


function GetDirArray($sPath)
{
	global $gData, $gCnt;
	$fList = array();
	$fListcnt = 0;
	if (@$handle=opendir($sPath))
	{
		while ($file = readdir($handle)) $fList[$fListcnt++] = $file;
		closedir($handle);

		if ($fListcnt > 0)
		{
			for ($i=0;$i<$fListcnt;$i++)
			{
				$val = $fList[$i];
				if ($val != "." && $val != "..")
				{
					if (is_dir($sPath.$val)) 
						GetDirArray($sPath.$val."/");
					else 
						if (file_type($val) != -1) $gData[$gCnt++] = $sPath.$val;
				}
			}
		}
	}
}


$enable_tablecheck = false;
$install_debug=1;

$dbi = array('user' => $db['user'], 'host' => $db['host'], 'name' => $db['name'], 'pass' => $db['pass']);

$mysqlserverv = '';

function check_version()
{
	global $enable_tablecheck, $app_build, $oldbuild;
	$result = db_execcheck('SELECT * from '.TBL_KPLAYVERSION);
	if ($result)
	{
		$data = mysql_fetch_array($result);
		if (isset($data['app_build']))
		{
			$oldbuild = (int)$data['app_build'];
			if ($oldbuild != $app_build) $enable_tablecheck = true;
		}
	} else $enable_tablecheck = true;
}

function Kinstall_logo($height='64', $width='208')
{
	return '<a href="http://www.kplaylist.net" title="Visit homepage"><img width="'.$width.'" height="'.$height.'" src="'.getimagelink('kplaylist.gif').'" alt="kPlaylist" border="0"/></a>';
}

function kcheckaccess($user, $pass, &$errmsg, &$errno)
{
	global $db;
	$status = 0;
	$link = @mysql_connect($db['host'], $user, $pass, true);
	if ($link)
	{
		if (@mysql_select_db($db['name'], $link)) $status = 1;
		else
		{
			$errmsg = mysql_error($link);
			$errno = mysql_errno($link);
			switch ($errno)
			{
				case 1049: $status = 1; break; // database not exist. OK.
				default: $status = 0; break;
			}
		}
		@mysql_close($link);
	} else 
	{
		$errno = mysql_errno();
		$errmsg = mysql_error();
	}
	return $status;
}

function showsql()
{
	global $installdb,$installdbuser;
	echo '<table width="600" border="0" align="center">';
	echo '<tr><td class="wtext">'."\n";
	echo '<font size="4">The installers SQL code:</font>';
	echo "\n".'</td></tr>';

		echo '<tr><td class="wtext"> <font color="green">##GREEN##</font> = Optional <br /><br /><br />'."\n";
		$querytext = str_replace("\n", "<br />\n", $installdb[0]);
		echo '<font color="green">'.$querytext.";</font>";
		echo '<br />';
		echo "\n".'</td></tr>';

	for ($i=1;$i<count($installdb);$i++)
	{
		echo '<tr><td class="wtext">'."\n";
		$querytext = str_replace("\n", "<br />\n", $installdb[$i]);
		echo $querytext.";";
		echo "\n".'</td></tr>';
	}
	for ($i=0;$i<count($installdbuser);$i++)
	{
		echo '<tr><td class="wtext">'."\n";
		$querytext = str_replace("\n", "<br />\n", $installdbuser[$i]);
		echo '<font color="green">'.$querytext.";</font>";
		echo "\n".'</td></tr>';
	}
	echo '</table>';
}

function show_feedback($upgrade = false)
{
	global $app_ver, $app_build, $oldbuild, $mysqlserverv;
	?>
	<?php				
	if (isset($_SERVER['SERVER_SOFTWARE'])) $os = $_SERVER['SERVER_SOFTWARE']; else $os = 'Unknown';
	?>
	
	<form method="get" action="http://kplaylist.net/success.php">
	<?php if ($upgrade)
	{
		echo '<input type="hidden" name="upgrade" value="1"/>'; 
		echo '<input type="hidden" name="upgradefrom" value="'.$oldbuild.'"/>'; 
	}
	?>
	<table width="100%" cellpadding="0" cellspacing="2" border="0">
		<tr>
			<td>Software</td>
			<td><input class="fatbuttom" type="text" name="os" size="45" value="<?php echo $os; ?>"/></td>
		</tr>
		<tr>
			<td>MySQL</td>
			<td><input class="fatbuttom" type="text" name="mysql" size="45" value="<?php echo $mysqlserverv; ?>"/></td>
		</tr>
		<tr>
			<td>Version</td>
			<td><input class="fatbuttom" type="text" name="version" size="4" value="<?php echo $app_ver; ?>"/></td>
		</tr>
		<tr>
			<td>Build</td>
			<td><input class="fatbuttom" type="text" name="build" size="4" value="<?php echo $app_build; ?>"/></td>
		</tr>
		<tr>
			<td>Have a comment?</td>
			<td><input class="fatbuttom" type="text" name="comment" size="45" value=""/></td>
		</tr>		
		<tr>
			<td></td>
			<td><input class="fatbuttom" name="send" type="submit" value="Send!"/></td>
		</tr>
	</table>
	</form>
	<?php
}

function insterror($msg)
{
	kprintheader('Error during install', 1);
	?>
		<table width="600" border="0" align="center">
			<tr> 
				<td><?php echo Kinstall_logo(); ?></td>
			</tr>
			<tr>
				<td height="15"></td>
			</tr>
			<tr>
				<td class="importnant">An error occured during install.</td>
			</tr>
			<tr>
				<td height="15"></td>
			</tr>
			<tr> 
				<td class="importnant">Error: <font size="2"><?php echo $msg ?></font></td>
			</tr>
			<tr>
				<td height="15"></td>
			</tr>
			<tr>
				<td class="importnant">You could restart the installation process by opening up a new window and enter the same URL.</td>
			</tr>
			<tr>
				<td class="importnant">Look also for help in the kPlaylist documentation pages. Click <a href="http://kplaylist.net/index.php?showinstall=1" target="_blank">here</a> for reading the INSTALL document.</td>
			</tr>
		</table>
	<?php
	kprintend();
	die();
}

function kpinstall($dropdatabase=false)
{
	global $db, $dbi, $installdb, $initdb, $installdbuser, $mysqlserverv;

	$link = @mysql_connect($db['host'], $dbi['user'], $dbi['pass'], true);
	
	if (!$link) insterror('Could not establish connection to MySQL!');

	$mysqlserverv = mysql_get_server_info($link);

	kprintheader('Installing MySQL database', 1);
	?>
	<table width="600" border="0" align="center">
	<tr> 
      <td colspan="4" class="wtext"><font size="4"></font></td>
	 </tr>
	<?php
	if ($dropdatabase) $result = mysql_query($installdb[0],$link);
	$error=0;

	$result = mysql_query($installdb[1],$link);
	if ($result)
	{
		if (mysql_select_db($db['name'], $link))
		{
			$sqltorun = array();
			for ($i=2,$c=count($installdb);$i<$c;$i++) $sqltorun[] = $installdb[$i];
			$err = '';
			$errno = 0;
			if (kcheckaccess($db['user'], $db['pass'], $err, $errno) == 0) for ($i=0,$c=count($installdbuser);$i<$c;$i++) $sqltorun[] = $installdbuser[$i];

			for ($i=0,$c=count($sqltorun);$i<$c;$i++)
			{				
				$querytext = str_replace("\n", '<br/>', $sqltorun[$i]);
				$result = mysql_query($sqltorun[$i], $link);
				if (!$result) 
				{ 
					echo '<tr><td class="wtext"><font color="FF0000">Failed query ['.$i.']: </font>'.$querytext.';</td></tr>';
					echo '<tr><td class="wtext">'.mysql_error($link).'</td></tr>';
					$error=$i;
				}
			}
		}
	}

	if ($error)
	{
		echo '<tr><td class="dir"><br/><b>Installation may have failed!</b>';
		echo '</td></tr>';
	}
	else
	{
		?>
			<tr>
			<td class="dir">
			<br/>
				<h2>Installation is now completed.</h2>
				<ul>
					<li>To log in to kPlaylist, reload this page (firm reload) and you should be able to log in.</li>
					<li>All settings and configuration is available via WEB, click the 'Settings' button to the right.</li>
					<li>The default kPlaylist login is admin with admin as the password.</li>
				</ul>				
				<br/>

				<b>Would</b> you like to send the following information about this successful installation? This would
				give the kPlaylist site valuable information about supported systems, but also to increase the motivation knowing
				that this script actually is used. Thank you!
				<br/><br/>

 				<?php show_feedback(false); ?>
				
				Remember to visit <a href="http://www.kplaylist.net" target="_blank">http://www.kplaylist.net</a> for updates and help.
			</td>
			</tr>
			<?php
	}
	echo '</table>';
	kprintend();
}

function kInstall_show_form($text='')
{
	global $dbi, $db, $PHP_SELF;

	if (!function_exists('kprintheader')) insterror('Seems like we\'re not able to declare functions. Can\'t go further. Please upgrade PHP!');

	kprintheader("Install", 1);

	$err = '';
	$errno = 0;
	if (kcheckaccess($db['user'], $db['pass'], $err, $errno) == 0) 
	{
		$dbi['user'] = 'root';
		$dbi['pass'] = '';
	}
	?>
	<form name="installform" method="post" action="<?php echo $PHP_SELF; ?>">
	<table width="680" border="0" align="center">
		<tr>
			<td><?php echo Kinstall_logo(); ?></td>
		</tr>
	</table>
	<table width="600" border="0" align="center" class="tdborder">
	<tr>
		<td colspan="4" class="wtext"><font size="4">Welcome to the kPlaylist installer!</font></td>
	</tr>
	<tr>
		<td height="6"></td>
	</tr>
	<tr>
		<td class="importnant" colspan="4">
        To install kPlaylist, you'll need a working and running copy of MySQL. This is a GPL product, 
        please read the <a href="<?php echo $PHP_SELF ?>?showgpl=1" target="_blank"><font color="#0000FF">disclaimer of liability</font></a>
        before you continue. If you do not agree with the disclaimer <u>you 
        must abort</u> the installation and use of this product.
		</td>
	</tr>
	<tr> 
		<td height="22" colspan="4"><hr size="1"/></td>
	</tr>
	<tr>
		<td height="22" class="wtext" colspan="4">
	  If you are installing kPlaylist for the FIRST time, you must enter a user and password to MySQL 
	  which has access to create a new database and a new users for kPlaylist. 
	  In most cases, the root user of MySQL should be used.<br/><br/>
	  <a href="<?php echo $PHP_SELF ?>?showsql=1" target="_blank"><font color="#0000FF">Click here</font></a> to view what the installer is going to do. Click 'Continue' when ready to install ! <br/>
	  <?php
		if ($dbi['user'] == 'root')
		{
			?><br/>Note! The root password will only be used to create
		the tables, a new user called <?php echo $db['user']; ?> with password <?php echo $db['pass']; ?> will be created for the operation of kPlaylist. If you like to change the name and password for this user, please edit the script, and click Reload.<br/> 
		<?php }
		if (!empty($text)) echo '<br/>'.$text.'<br/>'; ?>
		
		</td>
    </tr>
	<tr><td height="10"></td></tr>	
	<tr> 
		<td height="22" class="wtext" width="121">MySQL user:</td>
		<td height="22" width="221"> 
		<input type="text" name="mysqluser" size="25" value="<?php echo $dbi['user']; ?>" class="fatbuttom"/>
		</td>
		<td height="22" colspan="2" class="wtext">default: <font color="green"><?php echo $db['user']; ?></font></td>
	</tr>
	<tr> 
		<td height="22" class="wtext" width="121">MySQL password:</td>
		<td height="22" width="221"> 
		<input type="password" name="mysqlpass" size="25" value="<?php echo $dbi['pass']; ?>" class="fatbuttom"/>
		</td>
		<td height="22" colspan="2" class="wtext">default: <font color="green"><?php echo $db['pass']; ?></font></td>
	</tr>
	<tr>
		<td colspan="4" class="wtext"><font color="gray">If you need to change the settings below, please edit them in the script and click Reload.</font></td>
	</tr>	
	<tr> 
		<td height="22" class="wtext" width="121">MySQL host:</td>
		<td height="22" width="221"> 
		<input type="text" name="mysqlhost" size="25" value="<?php echo $dbi['host']; ?>" disabled="disabled" class="fatbuttom"/>
		</td>
		<td colspan="2" class="wtext" height="22">&nbsp;</td>
	</tr>
	<tr> 
		<td height="22" class="warning" width="121">MySQL database:</td>
		<td height="22" width="221"> 
		<input type="text" name="mysqldatabase" size="25" value="<?php echo $dbi['name']; ?>" disabled="disabled" class="fatbuttom"/>
		</td>
		<td colspan="4" class="wtext" height="22">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="4" class="wtext"><br/><input type="checkbox" name="dropdatabase" value="1"/> Drop database '<?php echo $db['name']; ?>' (for full reinstallation; deletes all data in the database)</td>
	</tr>
	<tr>
      <td colspan="4">
		<input type="submit" name="reload" value="Reload" class="fatbuttom"/>&nbsp;
		<input type="submit" name="continue" value="Continue" class="fatbuttom"/>
		</td>
	</tr>
	<tr> 
		<td colspan="4" align="right"><font class="wtext">You'll find documentation here:</font>&nbsp;<a href="http://www.kplaylist.net" target="_blank"><font color="#0000FF">kPlaylist Homepage</font></a></td>
	</tr>  
	</table>
	</form>
	<?php
	kprintend();
	die();
}

if ($enable_install)
{
	if (!function_exists('mysql_connect')) insterror('Function \'mysql_connect()\' does not exist! You need to compile PHP with MySQL support or enable MySQL support in your php configuration.');

	if (isset($_POST['continue']))
	{
		$user = $_POST['mysqluser'];
		$pass = $_POST['mysqlpass'];
		if (isset($_POST['dropdatabase'])) $dropdatabase = 1; else $dropdatabase = 0;
		
		$err = '';
		$errno = 0;
		if (kcheckaccess($user, $pass, $err, $errno))
		{
			$dbi['user'] = $user;
			$dbi['pass'] = $pass;
			kpinstall($dropdatabase);
			
		} else 
		{
			$msg = '<font color="red" size="2">Could not login with the supplied user name and password! MySQL said: '.$err.'</font>'; 
			if ($errno == 1251) $msg .= '<br/><br/><font color="red" size="2">Seems like you are running MySQL 4.1. Please go to the following location to read the solution: </font><a href="http://www.kplaylist.net/forum/viewtopic.php?p=2231" target="_blank">http://www.kplaylist.net/forum/viewtopic.php?p=2231</a>'; 
			kInstall_show_form($msg);
		}
	} else
	if (isset($_GET['showgpl']))
	{
		kprintheader();
		echo 'The GPL license is available here: http://www.kplaylist.net/COPYING';
		kprintend();
	} else
	if (isset($_GET['showsql']))
	{
		kprintheader();
		showsql();
		kprintend();
	} else kInstall_show_form();
	die();
}

function show_upgrade($sql, $error="")
{
	global $db, $dbi, $PHP_SELF;
	kprintheader();
	?>
	<table width="50%" align="center" cellpadding="0" cellspacing="0" border="0">
	<tr><td><?php Kinstall_logo(); ?></td></tr>
	<tr> 
		<td colspan="4" class="wtext"><font size="4">kPlaylist database upgrader.</font></td>
	</tr>
	<tr>
		<td class="importnant"><br/>
		We are sorry for the inconvience, but there are some changes in the database in this new version of kPlaylist and we have to perform a simple upgrade.<br/><br/> Please supply a user who has access to alter the MySQL database (usually the root user of MySQL.). You can also run the SQL calls listed below manually and reload this page.</td>
	</tr>
	<?php
	if (!empty($error))
	{
		?>
		<tr><td height="10"></td></tr>
		<tr><td class="importnant"><font color="red">Errors during upgrade, please check the errors below and try again.</font></td></tr>
		<tr><td height="10"></td></tr>
		<tr><td class="wtext"><?php echo $error; ?></td></tr>
		<tr><td height="10"></td></tr>
		<?php
	}
	?>
	<tr><td height="10"></td></tr>
	<tr><td height="10"></td></tr>
	<tr><td colspan="2" class="wtext">SQL call(s) we will be executing:</td></tr>
	<tr><td height="10"></td></tr>
	<tr><td colspan="2" class="wtext"><?php 
	for ($i=0,$c=count($sql);$i<$c;$i++) 
	{
		echo str_replace("\n", '<br/>', $sql[$i]).';<br/><br/>';
	}
	?></td></tr>
	</table>
	<form name="upgradeform" method="post" action="<?php echo $PHP_SELF; ?>">
	<table width="50%" align="center" cellpadding="0" cellspacing="0" border="0">
	<tr><td height="20"></td></tr>
	<tr> 
		<td height="22" class="warning" width="121">MySQL database:</td>
		<td height="22" width="221" align="left">
		<input type="text" name="mysqldatabase" size="25" value="<?php echo $db['name']; ?>" disabled="disabled" class="fatbuttom"/>
		</td>
	</tr>
	<tr> 
		<td height="22" class="warning" width="121">MySQL host:</td>
		<td height="22" width="221" align="left"> 
		<input type="text" name="mysqlhost" size="25" value="<?php echo $db['host']; ?>" disabled="disabled" class="fatbuttom"/>
		</td>
	</tr>
	<tr><td colspan="2" class="wtext">If either the database name or the host is wrong, please edit the script and reload this page.</td></tr>
	<tr><td height="10"></td></tr>	
	<tr> 
		<td height="22" class="wtext" width="121">MySQL user:</td>
		<td height="22" width="221" align="left"> 
		<input type="text" name="mysqluser" size="25" value="<?php echo $dbi['user']; ?>" class="fatbuttom"/>
		</td>
	</tr>
	<tr> 
		<td height="22" class="wtext" width="121">MySQL password:</td>
		<td height="22" width="221" align="left"> 
		<input type="password" name="mysqlpass" size="25" value="<?php echo $dbi['pass']; ?>" class="fatbuttom"/>
		</td>
	</tr>
	<tr><td height="10"></td></tr>
	<tr>
		<td colspan="2" class="wtext"><input type="submit" class="fatbuttom" name="executeupgrade" value="Upgrade"/></td>
	</tr>
	</table>
	</form>
	<?php
	kprintend();
	die();
}

function upgrade_ok()
{
	global $setctl;
	$setctl->load();
	kprintheader();
	?>
	<table width="50%" align="center" cellpadding="0" cellspacing="0" border="0">
	<tr><td><?php Kinstall_logo(); ?></td></tr>
	<tr> 
		<td colspan="4" class="wtext"><font size="4">kPlaylist database upgrader.</font></td>
	</tr>
	<tr>
		<td class="importnant"><br/>
		Upgrading performed successfully. Enjoy your new version of kPlaylist.<br/><br/> 
		Reload this page to get started.<br/><br/>
		
		<b>Would</b> you like to send the following information about this successful upgrade? This would
		give the kPlaylist site valuable information about supported systems, but also to increase the motivation knowing
		that this script actually is used. Thank you!
		<br/><br/>

		<?php show_feedback(true); ?>
		
		</td>
	</tr>
	</table>
	<?php

	kprintend();
	die();
}

if (!$enable_install) check_version();

if ($enable_tablecheck)
{
	$update_sql = check_all_tables();
	if (count($update_sql) > 0) 
	{
		$error = "";
		if (isset($_POST['executeupgrade']))
		{
			$dbi['user'] = $_POST['mysqluser'];
			$dbi['pass'] = $_POST['mysqlpass'];
			$link = mysql_connect($db['host'], $dbi['user'], $dbi['pass'], true);
			if ($link)
			{
				$mysqlserverv = mysql_get_server_info($link);
	
				$sqls = check_all_tables();
				if (mysql_select_db($db['name'],$link))
				{
					for ($i=0,$c=count($sqls);$i<$c;$i++)
					{
						if (!empty($sqls[$i]))
						{
							if (!mysql_query($sqls[$i], $link))
							{
								$error = "Could not execute: ".$sqls[$i]."<br/>MySQL said: ".mysql_error($link)."<br/>";
								break;
							}
						}
					}
				} else $error = "Could not select the database name";
			} else $error = "Could not connect. Please check that the username or password is correct.";
			if (empty($error)) upgrade_ok();
		}
		show_upgrade(check_all_tables(),$error);
	} else
	{
		$sql = 'update '.TBL_KPLAYVERSION.' set app_build = "'.$app_build.'", app_ver = "'.$app_ver.'"';
		db_execcheck($sql);
	}
}


function kprintlogin($msg = '')
{
 	global $app_ver, $app_build, $PHP_SELF, $phpenv;
?>
<form name="userform" method="post" action="<?php if (((REQUIRE_HTTPS) && ($phpenv['https'])) || (!REQUIRE_HTTPS)) echo $PHP_SELF;?>">
<p>&nbsp;</p>
<table width="600" border="0" cellspacing="0" cellpadding="0" align="center">
	<tr>
		<td colspan="3"><img src="<?php echo getimagelink('login.jpg'); ?>" height="327" width="600" alt="kPlaylist v<?php echo $app_ver; ?> build <?php echo $app_build; ?>"/></td>
	</tr>
	<tr>
		<td height="12"/>
	</tr>
	<tr>
		<td height="12" width="600" align="left" valign="top" class="tdlogin">
			<table width="100%" border="0" cellpadding="0" cellspacing="5">
				<tr>
					<td width="17%" height="30"><font class="text"><?php echo get_lang(37); ?></font></td>
					<td width="31%" height="30">
						<input type="text" name="user" maxlength="30" size="15" class="logonbuttom"/>
					</td>
					<td rowspan="2" height="31" width="54%" align="right" valign="top"><img src="<?php echo getimagelink('php.gif'); ?>" border="0" alt="PHP - www.php.net" /></td>
				</tr>
				<tr>
					<td width="17%" height="27"><font class="text"><?php echo get_lang(38); ?></font></td>
					<td width="31%" height="27">
						<input type="password" name="password" maxlength="30" size="15" class="logonbuttom"/>
					</td>
				</tr>
				<tr>
					<td colspan="3">
						<table width="100%" border="0" cellpadding="0" cellspacing="0">
							<tr>
								<td>
									<?php 
									if (((REQUIRE_HTTPS) && ($phpenv['https'])) || (!REQUIRE_HTTPS))
									{
										?><input type="submit" name="Submit" value="<?php echo get_lang(40); ?>" class="logonbuttom" />
										<?php
										if (USERSIGNUP) 
										{ 
											?><input type="button" name="Signup" onclick="newwin('Users', '<?php echo $PHP_SELF; ?>?signup=1', 195, 350);" value="<?php echo get_lang(158); ?>" class="logonbuttom" /><?php 
										}
									} else echo '<a href="https://'.$phpenv['streamlocation'].'"><font class="logintext">'.get_lang(41).'</font></a>'; 
									?>
								</td>
								<td valign="bottom" align="right"><font class="logintext"><?php echo get_lang(39); ?></font></td>
							</tr>
						<?php if (!empty($msg))
						{
							?>
							<tr>
								<td colspan="2" height="15"></td>
							</tr>
							<tr>
								<td colspan="2"><font class="logintext"><?php echo $msg; ?></font></td>
							</tr>
							<tr>
								<td colspan="2" height="15"></td>
							</tr>
							<?php
						}
						?>
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
</form>

<script type="text/javascript">
	<!--
	document.userform.user.focus();
	// -->
</script>

<table width="600" border="0" cellspacing="0" cellpadding="0" align="center">
<tr>
	<td align="right">
	<p>
		<a href="http://validator.w3.org/check/referer">
		<img src="<?php echo getimagelink('w3c_xhtml_valid.gif'); ?>" border="0" alt="Valid XHTML 1.0!" height="31" width="88"/></a>
		<br/>
		<a href="http://www.kplaylist.net/"><font class="loginkplaylist">www.kplaylist.net</font></a>
	</p>
	</td></tr></table>
<?php
}


class kp_playlist
{
	var $status;
	var $listid;
	var $name;

	function kp_playlist($listid)
	{
		$this->listid = -1;
		$res = db_execquery('SELECT * FROM '.TBL_PLAYLIST.' WHERE listid = '.$listid);
		if ($res && mysql_num_rows($res) > 0)
		{
			$row = mysql_fetch_assoc($res);
			$this->status = $row['status'];
			$this->name = $row['name'];
			$this->listid = $listid;
		}
	}

	function getres()
	{
		return db_execquery('SELECT sid FROM '.TBL_PLAYLIST_LIST.' WHERE listid = '.$this->listid.' ORDER BY seq ASC');
	}

	function play()
	{
		if ($this->listid >= 0)
		{
			$result = $this->getres();
			if ($result && mysql_num_rows($result) > 0)
			{
				$tunes = array();
				$i=0;
				while ($row = mysql_fetch_row($result)) $tunes[$i++] = $row[0];
				$cnt = $i;
				if ($this->status)
				{
					srand ((double)microtime()*1000000);
					for ($j=count($tunes)-1; $j>0; $j--) 
					{
						if (($i = rand(0,$j))<$j) 
						{
							$swp=$tunes[$i]; 
							$tunes[$i]=$tunes[$j]; 
							$tunes[$j]=$swp;
						}
					}
				}
				kplay_m3uurl();	
				for ($i=0;$i<$cnt;$i++) kPlay_sendlink2($tunes[$i]);
			}
		}
	}
}

function playlist_createnew($name,$shared=0)
{
	global $u_id;
	if (db_execquery('INSERT INTO '.TBL_PLAYLIST.' SET name = "'.$name.'", u_id = '.$u_id.', public = '.$shared)) return 1;
	return 0;
}

function playlist_delete($nr)
{
	db_execquery('DELETE FROM '.TBL_PLAYLIST_LIST.' WHERE listid = '.$nr);
	db_execquery('DELETE FROM '.TBL_PLAYLIST.' WHERE listid = '.$nr);
}

function pl_sortoriginal($id)
{
	$result = db_execquery('SELECT id from '.TBL_PLAYLIST_LIST.' WHERE listid = '.$id.' ORDER BY ID ASC');	
	$seq = 1;
	while ($row = mysql_fetch_row($result))
	{
		$id = $row[0];
		db_execquery('UPDATE '.TBL_PLAYLIST_LIST.' SET seq = '.$seq.' WHERE id = '.$id, true);
		$seq++;
	}
}

function pl_removeduplicates($playlist_id)
{
	$result = db_execquery('SELECT sid,id from '.TBL_PLAYLIST_LIST.' WHERE listid = '.$playlist_id.' ORDER BY ID ASC');	
	$sids = array();
	while ($row = mysql_fetch_row($result))
	{
		$sid = $row[0];
		$id = $row[1];
		if (isset($sids[$sid])) db_execquery('DELETE FROM '.TBL_PLAYLIST_LIST.' WHERE id = '.$id, true);
		$sids[$sid] = true;
	}
	playlist_rewriteseq($playlist_id);
}

function pl_sortrandom($id)
{
	$result = db_execquery('SELECT id from '.TBL_PLAYLIST_LIST.' WHERE listid = '.$id.' ORDER BY ID ASC');		
	srand(make_seed());
	while ($row = mysql_fetch_row($result))
		db_execquery('UPDATE '.TBL_PLAYLIST_LIST.' SET seq = '.getrand().' WHERE id = '.$row[0], true);	
	playlist_rewriteseq($id);
}

function pl_sortalphabetic($id)
{
	$result = db_execquery('SELECT pl.id as id FROM '.TBL_PLAYLIST_LIST.' pl, '.TBL_SEARCH.' s WHERE pl.listid = '.$id.' and pl.sid = s.id order by s.free asc');		
	$seq = 1;
	while ($row = mysql_fetch_row($result))
	{
		db_execquery('UPDATE '.TBL_PLAYLIST_LIST.' SET seq = '.$seq.' WHERE id = '.$row[0], true);
		$seq++;
	}
}

function db_addtoplaylist($playlistnr, $tunes)
{
	global $u_id, $base_dir;
	$result = db_execquery('SELECT * FROM '.TBL_PLAYLIST_LIST.' WHERE listid = '.$playlistnr);	
	$row = mysql_num_rows($result);
	$cntr=$row;
	$cntr++;

	if (count($tunes) > 0)
	{
		for ($i=0,$c=count($tunes);$i<$c;$i++)
		{			
			$f2 = new file2($tunes[$i]);
			if ($f2->ifexists())
			{
				db_execquery('INSERT INTO '.TBL_PLAYLIST_LIST.' (listid, sid, seq) VALUES ('.$playlistnr.', '.$f2->sid.', '. $cntr.')');
				$cntr++;
			} 			
		}		
	}
}

function db_readplaylist($playlistnr)
{
	global $u_id;
	$result = db_execquery('SELECT list FROM '.TBL_PLAYLIST.' WHERE u_id = '.$u_id.' AND listid = '.$playlistnr);
	$row = mysql_fetch_array($result);
	return $row['list'];
}

function playlist_rewriteseq($plid)
{
	if (is_numeric($plid))
	{
		$result = db_execquery('SELECT * FROM '.TBL_PLAYLIST_LIST.' WHERE listid = '.$plid.' ORDER BY seq ASC');
		if (mysql_num_rows($result) > 0)
		{
			$cntr=1;
			while ($row = mysql_fetch_array($result))
			{
				db_execquery('UPDATE '.TBL_PLAYLIST_LIST.' SET seq = '.$cntr.' WHERE id = '.$row['id'], true);
				$cntr++;
			}
		}
	}
}

function playlist_savesequence($seqlist, $id)
{
	global $u_id;
	$result = db_execquery('SELECT id FROM '.TBL_PLAYLIST_LIST.' WHERE listid = '.$id.' ORDER BY seq ASC');
	$data = array();
	$cnt=0;
	while ($row = mysql_fetch_array($result))
	{
		$data['id'][$cnt] = $row['id'];
		$data['seq'][$cnt] = (int)$seqlist[$cnt];
		$cnt++;
	}
	if ($cnt > 0)
	{
		for ($i=0;$i<$cnt;$i++) db_execquery('UPDATE '.TBL_PLAYLIST_LIST.' SET seq = '.$data['seq'][$i].' WHERE id = '.$data['id'][$i]);
		playlist_rewriteseq($id);
	}
}

function playlist_editor($plid, $prev, $sort = 0)
{
	global $PHP_SELF,$u_cookieid, $base_dir, $u_id, $curdrive,$phpenv, $cfg;
	kprintheader(get_lang(59), 1);

	$result = db_execquery('SELECT * FROM '.TBL_PLAYLIST.' WHERE listid = '.$plid);	
		
	if ($result)
	{
		$row = mysql_fetch_array($result);
		$name = $row['name'];
		$public = $row['public'];
		if ($row['u_id'] == $u_id || db_guinfo('u_access') == 0) $myown = 1; else $myown = 0;
		$shuffle = $row['status'];
	}
	
	$result = db_execquery('SELECT * FROM '.TBL_PLAYLIST_LIST.' WHERE listid = '.$plid.' ORDER BY seq ASC');

	if ($result) $many = mysql_num_rows($result); else $many = 0;
	$playlistlink = '<input type="hidden" name="action" value="playlisteditor"/>'.
					'<input type="hidden" name="sel_playlist" value="'.$plid.'"/>'.
					'<input type="hidden" name="previous" value="'.$prev.'"/>'.
					'<input type="hidden" name="drive" value="'.$curdrive.'"/>';
	
	$code = '<table width="800" cellspacing="0" border="0" cellpadding="0"><tr><td align="left">';
	$code .= '&nbsp;&nbsp;<input type="button" value="'.get_lang(34).'" class="fatbuttom" onclick="javascript: '."chhttp('$PHP_SELF?p=$prev&amp;d=$curdrive');\"/>&nbsp;&nbsp;".
	$playlistlink.
	'<input type="submit" name="playplaylist" value="'.get_lang(42).'" class="fatbuttom"/>&nbsp;&nbsp;';

	if ($myown) $code .=
	"<input type=\"submit\" name=\"deleteplaylist\" onclick=\"javascript: if (!confirm('".get_lang(169)."')) return false;\"  value=\"".get_lang(43)."\" class=\"fatbuttom\"/>&nbsp;&nbsp;";
	if (ALLOWDOWNLOAD && db_guinfo('u_allowdownload') && $cfg['archivemode']) $code .= '<input type="button" name="pdlall" value="'.get_lang(117).'" onclick="javascript: newwin(\'dlplaylist\', \''.$PHP_SELF.'?action=dlplaylist&amp;pid='.$plid.'\', 130, 450);" class="fatbuttom"/>&nbsp;&nbsp;';
	if ($myown) $code .= '<input type="text" name="playlistname" value="'.$name.'" size="30" class="fatbuttom"/>&nbsp;&nbsp;';

	if ($myown)
	{			
		$code .= '<font class="wtext">'.get_lang(44).'&nbsp;<input type="checkbox" name="shared" value="1" '.checked($public).'/>&nbsp;'.
		get_lang(125).'&nbsp;<input type="checkbox" name="shuffle" value="1" '.checked($shuffle).'/>&nbsp;&nbsp;&nbsp;</font>'.
		'<input type="submit" class="fatbuttom" name="saveplaylist" value="'.get_lang(45).'"/>';
	
		$e = array(0 => '', 1 => '', 2 => '', 3 => '');
		$e[$sort] = ' selected="selected"';
		$code .= '&nbsp;&nbsp;&nbsp;<select name="sort" class="fatbuttom">'.
				'<option value="0"'.$e[0].'>'.get_lang(170).'</option>'.
				'<option value="1"'.$e[1].'>'.get_lang(171).'</option>'.
				'<option value="2"'.$e[2].'>'.get_lang(173).'</option>'.
				'<option value="3"'.$e[3].'>'.get_lang(180).'</option>'.
				'</select>';
		$code .= '&nbsp;&nbsp;<input type="submit" name="sortplaylist" value="'.get_lang(172).'" class="fatbuttom"/>';
	}
	
	$code .= '&nbsp;&nbsp;';
	if (UNAUTHORIZEDSTREAMS) $code .= '<a href="'.$PHP_SELF.'?streamplaylist='.$plid.'&amp;extm3u=true">i</a>';
	$code .= '</td></tr></table>';

	echo '<form action="'.$PHP_SELF.'" method="post">';	
	blackbox(get_lang(46, $name, $many),$code,0);
	echo '</form>';
	
	echo '<form name="psongs" action="'.$PHP_SELF.'" method="post">';	
	echo '<input type="hidden" name="action" value="playlisteditor"/>';
	if ($myown) echo blackboxpart(get_lang(47),1); else echo blackboxpart(get_lang(48),1);

	$out = '';
	if ($many > 0)
	{
		echo '<input type="hidden" name="previous" value="'.$prev.'"/>';
		echo	'<input type="hidden" name="sort" value="0"/>';
		echo	'<input type="hidden" name="sel_playlist" value="'.$plid.'"/>';

		echo '<table width="800" cellspacing="0" border="0" cellpadding="0">';
		
		echo '
		<tr> 
		    <td width="60" class="wtext"><b>'.get_lang(49).'</b></td>
		    <td width="60" class="wtext"><b>'.get_lang(50).'</b></td>
			<td width="100" class="wtext"><b>'.get_lang(51).'</b></td>
		    <td width="120" class="wtext"><b>'.get_lang(52).'</b></td>
		    <td width="100" class="wtext"><b>';		
			if ($myown) echo get_lang(53);
			echo '
			</b></td>

			<td class="wtext" width="360" align="left"><b>'.get_lang(54).'</b></td>
		</tr>';
		echo '<tr><td height="8" colspan="6"></td></tr>';		
		echo '<tr><td colspan="6"><img src="'.getimagelink('spacer.gif').'" border="0" height="1" width="800" alt=""/></td></tr>';	
		echo '<tr><td height="6" colspan="6"></td></tr>';			
		$totplaytime = $count = $countfails = 0;
		
		$l181 = get_lang(181);
		$l60 = get_lang(60);
		$l43 = get_lang(43);

		while ($row = mysql_fetch_array($result))
		{
			$count++;			
			$id = $row['id'];

			$f2 = new file2($row['sid'], true);
			if (!$f2) continue;
			$fexists = $f2->ifexists();
			$id3 = $f2->getid3();
			$filelink = $PHP_SELF.'?sid='.$row['sid'].'&amp;c='.$u_cookieid;
		
			if (($count % 2) == 0) echo '<tr class="row2nd">'; else echo '<tr>';
			
			echo '<td class="file" align="center" width="60">
			<input type="checkbox" class="wtext" name="selected[]" value="'.$id.'"/></td>
			<td width="60" class="wtext">';

			if ($myown) echo '<input class="smalltext" type="text" name="seq[]" value="'.lzero($row['seq']).'" size="4"/>'; 
				else
			echo lzero($row['seq']);
			echo '</td><td width="100" class="file">';
			$idv3title = '';
			$idv3info  = '';

			if (!$fexists)
			{ 
				echo '<font color="RED">'.get_lang(182).'</font>'; 
				$countfails++; 
			} else
			{
				$idv3title = $id3['title'].' - '.$id3['album'];				
				if (!empty($id3['bitrate']) && !empty($id3['length'])) $idv3info = $id3['bitrate'].'kb - '.$id3['length']; 
				if (is_numeric($id3['lengths'])) $totplaytime += $id3['lengths'];
				echo $l181;
			}
			echo '</td><td width="120" class="wtext">'.$idv3info.'</td>';
			echo '<td width="100" class="file">';
			if ($myown) echo '<a title="'.$l60.'" class="smalltext" href="'. $PHP_SELF . "?action=delsingleplaylist&amp;plid=$plid&amp;del=$id&amp;p=$prev&amp;d=$curdrive".'">&nbsp;'.$l43.'&nbsp;</a>';
			echo '</td><td width="360" align="left" class="file">';
			if ($fexists) echo '<a title="'.$idv3title.'" href="'.$filelink.'">'.$f2->fname.'</a>'; else echo '&nbsp;';
			echo '</td></tr>';
		} 

		?>
		<tr>
			<td height="6" colspan="6"></td>
		</tr>
		<tr>
			<td colspan="6"><img src="<?php echo getimagelink('spacer.gif'); ?>" border="0" height="1" width="800" alt=""/></td>
		</tr>
		<tr>
			<td height="10" colspan="6"></td>
		</tr>
		<tr>
			<td class="wtext" align="center" colspan="2"><b><?php echo get_lang(55); ?></b></td>
			<td class="file">

		<?php
		if ($countfails==0) echo get_lang(181); else echo '<font color="red">'.get_lang(56).'</font>';
		echo '</td>';

		$secs = $totplaytime;
		$days = floor($secs/86400);
		$secs = $secs % 86400;
		$hours = floor($secs/3600);
		$secs = $secs % 3600;
		$min = floor($secs/60);
		$secs = $secs % 60;

		$totshow = get_lang(187, $days, $hours, $min, $secs);
		
		echo '<td class="wtext">'.$totshow.'</td></tr>';
		echo '<tr><td colspan="6">&nbsp;</td></tr>';
		echo '<tr><td align="left" class="file" colspan="6">';

		echo	'<input type="hidden" name="drive" value="'.$curdrive.'"/>'.
				'&nbsp;&nbsp;'.get_lang(73).'&nbsp;&nbsp;<input type="button" value="+" class="fatbuttom" onclick="javascript: SelectAll();"/>&nbsp;&nbsp;'.
				'<input type="button" value="-" class="fatbuttom" onclick="javascript: SelectAll();"/>&nbsp;&nbsp;'.
		get_lang(57).'&nbsp;&nbsp;<input type="submit" class="fatbuttom" onclick="javascript: if (!anyselected()) { alert(\''.get_lang(159).'\'); return false; }" name="playselected" value="'.get_lang(42).'"/>&nbsp;&nbsp;';

		if ($myown) echo '<input type="submit" class="fatbuttom" onclick="javascript: if (!anyselected()) { alert(\''.get_lang(159).'\'); return false; } else if (!confirm(\''.get_lang(210).'\')) return false;" name="delselected" value="'.
		get_lang(43).'"/>&nbsp;&nbsp;'.get_lang(58).'&nbsp;&nbsp;<input type="submit" class="fatbuttom" name="saveseq" value="'.get_lang(45).'"/>';

		echo '&nbsp;&nbsp;</td></tr><tr><td colspan="6">&nbsp;</td></tr>';
		echo '</table>';
	}
	if ($myown) echo blackboxpart(get_lang(47),2); else echo blackboxpart(get_lang(48),2);
	echo '</form></body></html>'; 
}

function playlist_new()
{
	global $PHP_SELF;
	kprintheader(get_lang(61), 1);
	?>
	<form method="post" action="<?php echo $PHP_SELF; ?>">
	<input type="hidden" name="action" value="playlist_newsave"/>
	<table width="300" border="0" cellpadding="0" cellspacing="0">
	<tr> 
		<td class="wtext" align="left" width="60"><?php echo get_lang(62); ?></td>
		<td class="wtext" colspan="2" width="240">&nbsp;<input type="text" name="name" class="wtext"/></td>
	</tr>
	<tr> 
		<td class="wtext" align="left" width="60"><?php echo get_lang(44); ?></td>
		<td class="wtext" colspan="2" width="240"><input type="checkbox" name="shared" value="1" class="wtext"/></td>
	</tr>
	<tr>
		<td colspan="2" height="10"></td>
	</tr>
	<tr> 
		<td colspan="2" align="left" class="wtext">
			<input type="submit" value="<?php echo get_lang(63); ?>" class="fatbuttom"/>&nbsp;
			<input type="button" onclick="javascript: window.close();" value="<?php echo get_lang(16); ?>" class="fatbuttom"/>
		</td>
	</tr>
	</table>
	</form>
	<?php
	kprintend();
}

function db_getplaylist($u_id)
{
	$result = db_execquery('SELECT u_id, name, listid FROM '.TBL_PLAYLIST.' WHERE u_id = '.$u_id);
	$playlists = array();
	if ($result !== false) while ($row = mysql_fetch_array($result)) $playlists[] = array($row['name'], $row['listid']);
	return $playlists;
}

function db_sharedplaylist($u_id)
{
	global $u_playlist, $u_playlistid;
	$result = db_execquery('SELECT name, listid FROM '.TBL_PLAYLIST.' WHERE public = 1 AND u_id != '.$u_id.' ORDER by name ASC');

	$u_playlist = array();
	$u_playlistid = array();
	
	$c=0;
	while ($row = mysql_fetch_array($result)) 
	{
		$u_playlist[$c]	= " ".$row['name']. " ";
		$u_playlistid[$c] = $row['listid'];
		$c++;
	}
}


function settings_save($data, $page)
{
	global $setctl;
	if ($data != NULL)
	{
		switch ($page)
		{
			case 0:
				$setctl->set('report_attempts', 0);
				$setctl->set('windows', 0);
				$setctl->set('require_https', 0);
				$setctl->set('usersignup', 0);
				$setctl->set('mailmp3', 0);
				$setctl->set('bulletin', 0);
				break;

			case 1:
				$setctl->set('includeheaders', 0);
				$setctl->set('showkeyteq', 0);
				$setctl->set('showupgrade', 0);
				$setctl->set('showstatistics', 0);
				$setctl->set('albumcover', 0);
				$setctl->set('albumresize', 0);
				$setctl->set('fetchalbum', 0);				
				break;

			case 2:
				$setctl->set('streamingengine', 0);
				$setctl->set('allowdownload', 0);
				$setctl->set('allowseek', 0);
				$setctl->set('disksync', 0);
				$setctl->set('sendfileextension', 0);
				$setctl->set('unauthorizedstreams', 0);				
				$setctl->set('writeid3v2', 0);
				$setctl->set('optimisticfile', 0);
				$setctl->set('lamesupport', 0);
				$setctl->set('enableupload', 0);
				break;
		}
				
		foreach ($data as $key => $value)
		{
			
			switch ($key)
			{
				case 'base_dir':
					$s_base_dir = explode(';',$value);
					$value = '';
					for ($i=0;$i<count($s_base_dir);$i++) 
					{
						if (!empty($s_base_dir[$i]))
						{
							$value .= slashtranslate($s_base_dir[$i]);
							if ($value[strlen($value)-1] != '/') $value .= '/';
							if (isset($s_base_dir[$i+1])) if (!empty($s_base_dir[$i+1])) $value .= ';';
						}
					}
					break;

				case 'uploadpath':
					if (!empty($value))
					{
						$value = slashtranslate($value);
						if (strlen($value) > 0) if ($value[strlen($value)-1] != '/') $value .= '/';	
					}
					break;

				case 'homepage':
					$value = htmlentities($value);
					break;	
				
				case 'lamecmd':
					$value = stripcslashes($value);
					break;

				case 'albumfiles': $value = stripcslashes($value); break;
				
				case 'externimagespath':						
					if (!empty($value)) if ($value[strlen($value)-1] != '/') $value .= '/';	
					break;				
			}
			$setctl->set($key, $value);			
		}				
	}
}

function helplink($section, $name='?')
{
	global $deflanguage, $app_build;
	return '<a target="_new" title="'.get_lang(161).'" href="http://www.kplaylist.net/?configuration='.$section.'&amp;lang='.$deflanguage.'&amp;b='.$app_build.'">'.$name.'</a>';
}

function store_filetype($id, $m3u, $search, $logaccess, $mime, $extension='')
{
	if ($id != 0)
	{	
		db_execquery('UPDATE '.TBL_FILETYPES.' SET m3u = '.$m3u.', search = '.$search.', logaccess = '.$logaccess.', mime = "'.$mime.'", extension = "'.$extension.'" WHERE id = '.$id);
		return $id;
	} else 
	{
		db_execquery('INSERT INTO '.TBL_FILETYPES.' SET m3u = '.$m3u.', search = '.$search.', logaccess = '.$logaccess.', mime = "'.$mime.'", enabled = 1, getid = 0, extension = "'.$extension.'"');
		return mysql_insert_id();
	}	
}

function edit_filetype($id, $reload = false)
{
	global $PHP_SELF;

	if ($id != 0)
	{
		$res = db_execquery('SELECT * FROM '.TBL_FILETYPES.' WHERE id = '.$id);
		$row = mysql_fetch_assoc($res);		
	} else 
	{
		$row['extension'] = '';
		$row['mime'] = '';
		$row['m3u'] = 1;
		$row['search'] = 1;		
		$row['logaccess'] = 1;
	}
	kprintheader(get_lang(209), 1);
	?>
	<form name="edit_filetype" method="post" action="<?php echo $PHP_SELF; ?>">
	<input type="hidden" name="action" value="storefiletype"/>
	<input type="hidden" name="id" value="<?php echo $id; ?>"/>

	<table width="97%" align="center" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td class="wtext"><?php echo get_lang(206); ?></td>
		<td class="wtext"><input type="text" name="extension" class="fatbuttom" maxlength="32" size="30" value="<?php echo $row['extension']; ?>"/></td>
		<td class="wtext"><?php echo helplink('ftextension'); ?></td>
	</tr>
	<tr>
		<td class="wtext"><?php echo get_lang(207); ?></td>
		<td class="wtext"><input type="text" name="mime" class="fatbuttom" size="30" maxlength="128" value="<?php echo $row['mime']; ?>"/></td>
		<td class="wtext"><?php echo helplink('ftmime'); ?></td>
	</tr>
	<tr>
		<td class="wtext"><?php echo get_lang(208); ?></td>
		<td class="wtext"><input type="checkbox" name="m3u" value="1" <?php echo checked($row['m3u']); ?>></td>
		<td class="wtext"><?php echo helplink('ftm3u'); ?></td>
	</tr>
	<tr>
		<td class="wtext"><?php echo get_lang(265); ?></td>
		<td class="wtext"><input type="checkbox" name="logaccess" value="1" <?php echo checked($row['logaccess']); ?>></td>
		<td class="wtext"><?php echo helplink('ftlogaccess'); ?></td>
	</tr>
	<tr>
		<td class="wtext"><?php echo get_lang(266); ?></td>
		<td class="wtext"><input type="checkbox" name="search" value="1" <?php echo checked($row['search']); ?>></td>
		<td class="wtext"><?php echo helplink('ftsearch'); ?></td>
	</tr>
	<tr>
		<td colspan="3" height="10"/>
	</tr>
	<tr>
		<td colspan="3">
			<input type="submit" class="fatbuttom" name="save" value="<?php echo get_lang(45); ?>"/>&nbsp;
			<input type="button" class="fatbuttom" name="close" value="<?php echo get_lang(27); ?>" onclick="javascript: window.close(); <?php if ($reload) echo 'window.opener.location.reload();'; ?>"/>
		</td>
	</tr>
	</table>
	</form>
	<?php
	kprintend();
}

function settings_page($page)
{
	global $phpenv, $setctl, $cfg, $win32, $streamtypes_default, $PHP_SELF;
	
	phpfigure();

	switch ($page)
	{
		case 0: 

			?>
			<tr>
			<td class="wtext"><?php echo get_lang(129); ?></td>
			<td class="wtext"><?php echo get_lang_combo($setctl->get('default_language'),'default_language'); ?></td>
			<td class="wtext"><?php echo helplink('defaultlanguage'); ?></td>
			</tr>
			
			<tr>
			<td class="wtext"><?php echo get_lang(130); ?></td>
			<td class="wtext"><input type="checkbox" value="1" name="windows" <?php echo $setctl->getchecked('windows'); ?>/></td>
			<td class="wtext"><?php echo helplink('windowssystem'); ?></td>
			</tr>

			<tr>
			<td class="wtext"><?php if ($phpenv['https']) echo get_lang(131); else echo get_lang(139); ?></td>
			<td class="wtext"><input type="checkbox" <?php if (!$phpenv['https']) echo 'disabled="disabled"'; ?> value="1" name="require_https" <?php echo $setctl->getchecked('require_https'); ?>/></td>
			<td class="wtext"><?php echo helplink('https'); ?></td>
			</tr>

			<tr>
			<td class="wtext"><?php echo get_lang(134); ?></td>
			<td class="wtext"><input type="text" class="fatbuttom" name="timeout" value="<?php echo $setctl->get('timeout'); ?>"/></td>
			<td class="wtext"><?php echo helplink('timeout'); ?></td>
			</tr>

			<tr>
			<td class="wtext"><?php echo get_lang(135); ?></td>
			<td class="wtext"><input type="checkbox" value="1" name="report_attempts" <?php echo $setctl->getchecked('report_attempts'); ?>/></td>
			<td class="wtext"><?php echo helplink('report'); ?></td>
			</tr>
			<tr>
			<td class="wtext"><?php echo get_lang(202); ?></td>
			<td class="wtext"><input type="checkbox" value="1" name="usersignup" <?php echo $setctl->getchecked('usersignup'); ?>/></td>
			<td class="wtext"><?php echo helplink('usersignup'); ?></td>
			</tr>
			<tr>
			<td class="wtext"><?php echo get_lang(225); ?></td>
			<td class="wtext"><input type="text" class="fatbuttom" name="smtphost" value="<?php echo $setctl->get('smtphost'); ?>"/></td>
			<td class="wtext"><?php echo helplink('smtphost'); ?></td>
			</tr>
			<tr>
			<td class="wtext"><?php echo get_lang(226); ?></td>
			<td class="wtext"><input type="text" class="fatbuttom" name="smtpport" value="<?php echo $setctl->get('smtpport'); ?>"/></td>
			<td class="wtext"><?php echo helplink('smtpport'); ?></td>
			</tr>
			<tr>
			<td class="wtext"><?php echo get_lang(233); ?></td>
			<td class="wtext"><input type="checkbox" value="1" name="mailmp3" <?php echo $setctl->getchecked('mailmp3'); ?>/></td>
			<td class="wtext"><?php echo helplink('mailmp3'); ?></td>
			</tr>
			<tr>				
				<td class="wtext"><?php echo get_lang(250); ?></td>
				<td>
					<?php $method = $setctl->get('mailmethod'); ?>
					<select class="fatbuttom" name="mailmethod">
					<option value="1"<?php if ($method == 1) echo ' selected="selected"'; ?>><?php echo get_lang(251); ?></option>
					<option value="2"<?php if ($method == 2) echo ' selected="selected"'; ?>><?php echo get_lang(252); ?></option>
					</select>
				</td>
				<td class="wtext"><?php echo helplink('mailmethod'); ?></td>
			</tr>
			<?php if (class_exists('kbulletin'))
			{
			?>
			<tr>
				<td class="wtext"><?php echo get_lang(268); ?></td>
				<td class="wtext"><input type="checkbox" value="1" name="bulletin" <?php echo $setctl->getchecked('bulletin'); ?>/></td>
				<td class="wtext"><?php echo helplink('bulletin'); ?></td>
			</tr>
			<?php
			}
			break;
	
		case 1:
			?>
			<tr>
				<td class="wtext"><?php echo get_lang(197); ?></td>
				<td class="wtext"><input type="text" class="fatbuttom" name="homepage" maxlength="50" size="50" value="<?php echo $setctl->get('homepage'); ?>"/></td>
				<td class="wtext"><?php echo helplink('homepage'); ?></td>
			</tr>
			<tr>
				<td class="wtext"><?php echo get_lang(195); ?></td>
				<td class="wtext"><input type="checkbox" value="1" name="includeheaders" <?php echo $setctl->getchecked('includeheaders'); ?>/></td>
				<td class="wtext"><?php echo helplink('includeheaders'); ?></td>
			</tr>
			<tr>
				<td class="wtext"><?php echo get_lang(163); ?></td>
				<td class="wtext"><input type="text" class="fatbuttom" name="externimagespath" maxlength="50" size="50" value="<?php echo $setctl->get('externimagespath'); ?>"/></td>
				<td class="wtext"><?php echo helplink('s_externimagespath'); ?></td>
			</tr>
			<tr>
				<td class="wtext"><?php echo get_lang(179); ?></td>
				<td class="wtext"><input type="text" class="fatbuttom" name="externalcss" maxlength="50" size="50" value="<?php echo $setctl->get('externalcss'); ?>"/></td>
				<td class="wtext"><?php echo helplink('s_externalcss'); ?></td>
			</tr>
			<tr>
				<td class="wtext"><?php echo get_lang(196); ?></td>
				<td class="wtext"><input type="text" class="fatbuttom" name="externaljavascript" maxlength="50" size="50" value="<?php echo $setctl->get('externaljavascript'); ?>"/></td>
				<td class="wtext"><?php echo helplink('externaljavascript'); ?></td>
			</tr>
			<tr>
				<td class="wtext"><?php echo get_lang(198); ?></td>
				<td class="wtext"><input type="checkbox" value="1" name="showkeyteq" <?php echo $setctl->getchecked('showkeyteq'); ?>/></td>
				<td class="wtext"></td>
			</tr>
			<tr>
				<td class="wtext"><?php echo get_lang(199); ?></td>
				<td class="wtext"><input type="checkbox" value="1" name="showupgrade" <?php echo $setctl->getchecked('showupgrade'); ?>/></td>
				<td class="wtext"></td>
			</tr>
			<tr>
				<td class="wtext"><?php echo get_lang(200); ?></td>
				<td class="wtext"><input type="checkbox" value="1" name="showstatistics" <?php echo $setctl->getchecked('showstatistics'); ?>/></td>
				<td class="wtext"><?php echo helplink('showstatistics'); ?></td>
			</tr>
			<tr>
				<td class="wtext"><?php echo get_lang(245); ?></td>
				<td class="wtext"><input type="checkbox" value="1" name="albumcover" <?php echo $setctl->getchecked('albumcover'); ?>/></td>
				<td class="wtext"><?php echo helplink('albumcover'); ?></td>
			</tr>
			<tr>
				<td class="wtext"><?php echo get_lang(246); ?></td>
				<td class="wtext"><input type="text" class="fatbuttom" name="albumfiles" maxlength="250" size="50" value="<?php echo $setctl->get('albumfiles'); ?>"/></td>
				<td class="wtext"><?php echo helplink('albumfiles'); ?></td>
			</tr>
			<tr>
				<td class="wtext"><?php echo get_lang(247); ?></td>
				<td class="wtext"><input type="checkbox" value="1" name="albumresize" <?php echo $setctl->getchecked('albumresize'); ?>/></td>
				<td class="wtext"><?php echo helplink('albumresize'); ?></td>
			</tr>
			<tr>
				<td class="wtext"><?php echo get_lang(248); ?></td>
				<td class="wtext"><input type="text" class="fatbuttom" name="albumheight" value="<?php echo $setctl->get('albumheight'); ?>"/></td>
				<td class="wtext"><?php echo helplink('albumheight'); ?></td>
			</tr>
			<tr>
				<td class="wtext"><?php echo get_lang(249); ?></td>
				<td class="wtext"><input type="text" class="fatbuttom" name="albumwidth" value="<?php echo $setctl->get('albumwidth'); ?>"/></td>
				<td class="wtext"><?php echo helplink('albumwidth'); ?></td>
			</tr>
			<tr>
				<td class="wtext"><?php echo get_lang(256); ?></td>
				<td class="wtext"><input type="checkbox" value="1" name="fetchalbum" <?php echo $setctl->getchecked('fetchalbum'); ?>/></td>
				<td class="wtext"><?php echo helplink('fetchalbum'); ?></td>
			</tr>
			<tr>
				<td class="wtext"><?php echo get_lang(257); ?></td>
				<td class="wtext"><input type="text" size="50" class="fatbuttom" name="albumurl" value="<?php echo $setctl->get('albumurl'); ?>"/></td>
				<td class="wtext"><?php echo helplink('albumurl'); ?></td>
			</tr>
			<?php

			break;
		
		case 2:
			?>
			<tr>
				<td class="wtext"><?php echo get_lang(127); ?></td>
				<td class="wtext"><input type="text" name="base_dir" class="fatbuttom" size="50" value="<?php echo $setctl->get('base_dir'); ?>"/></td>
				<td class="wtext"><?php echo helplink('basedir'); ?></td>
			</tr>
			<tr>
				<td class="wtext"><?php echo get_lang(192); ?></td>
				<td class="wtext"><input type="checkbox" value="1" name="disksync" <?php echo $setctl->getchecked('disksync'); ?>/></td>
				<td class="wtext"><?php echo helplink('disksync'); ?></td>
			</tr>
			<tr>
				<td class="wtext"><?php echo get_lang(211); ?></td>
				<td class="wtext"><input type="checkbox" value="1" name="optimisticfile" <?php echo $setctl->getchecked('optimisticfile'); ?>/></td>
				<td class="wtext"><?php echo helplink('optimisticfile'); ?></td>
			</tr>
			<tr>
				<td class="wtext"><?php echo get_lang(128); ?></td>
				<td class="wtext"><input type="text" name="streamurl" size="7" maxlength="32" class="fatbuttom" value="<?php echo $setctl->get('streamurl'); ?>"/>&nbsp;<input type="text" name="streamlocation" class="fatbuttom" size="40" value="<?php echo $setctl->get('streamlocation'); ?>"/></td>
				<td class="wtext"><?php echo helplink('streamlocation'); ?>&nbsp;<a href="#" title="<?php echo $setctl->get('streamurl').$phpenv['streamlocation']; ?>">i</a></td>
			</tr>	
			<tr>
				<td class="wtext"><?php echo get_lang(132); ?></td>
				<td class="wtext"><input type="checkbox" value="1" name="allowseek" <?php echo $setctl->getchecked('allowseek'); ?>/></td>
				<td class="wtext"><?php echo helplink('allowseek'); ?></td>
			</tr>
			<tr>
				<td class="wtext"><?php echo get_lang(133); ?></td>
				<td class="wtext"><input type="checkbox" value="1" name="allowdownload" <?php echo $setctl->getchecked('allowdownload'); ?>/></td>
				<td class="wtext"><?php echo helplink('allowdownload'); ?></td>
			</tr>	
			<tr>
				<td class="wtext"><?php echo get_lang(140); ?></td>
				<td class="wtext"><input type="checkbox" value="1" <?php if ($win32) echo 'disabled="disabled"'; ?> name="streamingengine" <?php if (!$win32) echo $setctl->getchecked('streamingengine'); ?>/></td>
				<td class="wtext"><?php echo helplink('streamingengine'); ?></td>
			</tr>
			<tr>
				<td class="wtext"><?php echo get_lang(149); ?></td>
				<td class="wtext"><input type="text" class="fatbuttom" name="dlrate" maxlength="6" size="6" value="<?php echo $setctl->get('dlrate'); ?>"/></td>
				<td class="wtext"><?php echo helplink('dlrate'); ?></td>
			</tr>
			<tr>
				<td class="wtext"><?php echo get_lang(193); ?></td>
				<td class="wtext"><input type="checkbox" value="1" name="sendfileextension" <?php echo $setctl->getchecked('sendfileextension'); ?>/></td>
				<td class="wtext"><?php echo helplink('sendfileextension'); ?></td>
			</tr>
			<tr>
				<td class="wtext"><?php echo get_lang(194); ?></td>
				<td class="wtext"><input type="checkbox" value="1" name="unauthorizedstreams" <?php echo $setctl->getchecked('unauthorizedstreams'); ?>/></td>
				<td class="wtext"><?php echo helplink('unauthorizedstreams'); ?></td>
			</tr>
			<tr>
				<td class="wtext"><?php echo get_lang(201); ?></td>
				<td class="wtext"><input type="checkbox" value="1" <?php if (!$cfg['enablegetid3']) echo 'disabled="disabled"'; ?> name="writeid3v2" <?php if ($cfg['enablegetid3']) echo $setctl->getchecked('writeid3v2'); ?>/></td>
				<td class="wtext"><?php echo helplink('writeid3v2'); ?></td>
			</tr>
			<tr>
				<td class="wtext"><?php echo get_lang(220); ?></td>
				<td class="wtext"><input type="checkbox" value="1" name="lamesupport" <?php echo $setctl->getchecked('lamesupport'); ?>/></td>
				<td class="wtext"><?php echo helplink('lamesupport'); ?></td>
			</tr>
			<tr>
				<td class="wtext"><?php echo get_lang(244); ?></td>
				<td class="wtext"><input type="text" class="fatbuttom" size="55" maxlength="255" name="lamecmd" value='<?php echo $setctl->get('lamecmd'); ?>'/></td>
				<td class="wtext"><?php echo helplink('lamecmd'); ?></td>
			</tr>
			<tr>
				<td class="wtext"><?php echo get_lang(231); ?></td>
				<td class="wtext"><input type="checkbox" value="1" name="enableupload" <?php echo $setctl->getchecked('enableupload'); ?>/></td>
				<td class="wtext"><?php echo helplink('enableupload'); ?></td>
			</tr>
			<tr>
				<td class="wtext"><?php echo get_lang(232); ?></td>
				<td class="wtext"><input type="text" name="uploadpath" class="fatbuttom" size="50" value="<?php echo $setctl->get('uploadpath'); ?>"/></td>
				<td class="wtext"><?php echo helplink('uploadpath'); ?></td>
			</tr>
			<?php
			break;

		case 3:
				$cnt=0;
				?>
				<tr>
					<td colspan="3">
						<table width="100%" border="0" cellpadding="0" cellspacing="0">
							<tr class="wtext">
								<td width="15%"><?php echo get_lang(206); ?></td>
								<td width="25%"><?php echo get_lang(207); ?></td>
								<td width="15%"><?php echo get_lang(208); ?></td>
								<td width="10%"><?php echo get_lang(49); ?></td>
								</tr>
								<tr>
									<td colspan="3" height="10"></td>
								</tr>
							<?php

							$editstreamtypes = array();
							for ($i=0,$c=count($streamtypes_default);$i<$c;$i++) $editstreamtypes[] = array($streamtypes_default[$i],1);
							$res = db_execquery('SELECT extension, mime, m3u, getid, search, id FROM '.TBL_FILETYPES.' WHERE enabled = 1', true);
							if ($res) while ($row = mysql_fetch_row($res)) $editstreamtypes[] = array($row, 0);;

							for ($i=0,$c=count($editstreamtypes);$i<$c;$i++)
							{
								$cnt++;
								if ($cnt % 2 == 0) echo '<tr>'; else echo '<tr class="row2nd">';
								?>
									<td class="wtext"><?php echo '.'.$editstreamtypes[$i][0][0]; ?></td>
									<td class="wtext"><?php echo $editstreamtypes[$i][0][1]; ?></td>
									<td class="wtext"><?php echo selected($editstreamtypes[$i][0][2],get_lang(204), get_lang(205)); ?></td>
									<td class="wtext"><?php 
									if (!$editstreamtypes[$i][1]) 
									{
										echo '<a class="hot" onclick="javascript: if (!confirm(\''.get_lang(210).'\')) return false;" href="'. $PHP_SELF .'?action=deletefiletype&amp;del='.$editstreamtypes[$i][0][5].'">'.get_lang(109).'</a>&nbsp;&nbsp;';
										
										echo '<a class="hot" href="javascript: void(0);" onclick="javascript: newwin(\'fileditor\', \''.$PHP_SELF.'?action=editfiletype&amp;id='.$editstreamtypes[$i][0][5].'\',180,365);">'.get_lang(71).'</a>&nbsp;';
									}
								?>
								</td>
								
								</tr>
								<?php
							}
							?>
							<tr>
								<td colspan="3"/><td><?php echo '<a class="hot" href="javascript: void(0);" onclick="javascript: newwin(\'fileditor\', \''.$PHP_SELF.'?action=editfiletype&amp;id=0\',180,365);">'.get_lang(69).'</a>&nbsp;';?></td>
							</tr>
						</table>
					</td>
				</tr>
				<?php
				break;
	}
}

function settings_edit($reload = 0, $page = 0)
{
	global $userauth, $PHP_SELF, $phpenv, $setctl;
	kprintheader(get_lang(126), 1);

	$menuclass = array('header', 'header', 'header', 'header');
	$menuclass[$page] = 'headermarked';

	$widths = array('35%', '50%', '15%'); 

	function pagelink($id, $reload)
	{
		global $PHP_SELF;
		return $PHP_SELF.'?action=settingsview&amp;reload='.$reload.'&amp;page='.$id;
	}
	
	?>
	<form name="settings" method="post" action="<?php echo $PHP_SELF; ?>">
	<input type="hidden" name="action" value="savesettings"/>
	<input type="hidden" name="page" value="<?php echo $page; ?>"/>	
	<table width="100%" border="0" cellpadding="0" cellspacing="0">	
		<tr>
			<td width="25%" class="<?php echo $menuclass[0]; ?>"><a class="<?php echo $menuclass[0]; ?>" href="<?php echo pagelink(0,$reload); ?>"><?php echo get_lang(188); ?></a></td>
			<td width="25%" class="<?php echo $menuclass[1]; ?>"><a class="<?php echo $menuclass[1]; ?>" href="<?php echo pagelink(1,$reload); ?>"><?php echo get_lang(189); ?></a></td>
			<td width="25%" class="<?php echo $menuclass[2]; ?>"><a class="<?php echo $menuclass[2]; ?>" href="<?php echo pagelink(2,$reload); ?>"><?php echo get_lang(190); ?></a></td>
			<td width="25%" class="<?php echo $menuclass[3]; ?>"><a class="<?php echo $menuclass[3]; ?>" href="<?php echo pagelink(3,$reload); ?>"><?php echo get_lang(203); ?></a></td>
		</tr>
		<tr>
			<td colspan="4"><hr size="1"/></td>
		</tr>
		<tr>
			<td colspan="4" height="5"/>
		</tr>
	</table>

	<table width="100%" border="0" cellpadding="0" cellspacing="0">	
		<tr>
			<td width="<?php echo $widths[0]; ?>"></td>
			<td width="<?php echo $widths[1]; ?>"></td>
			<td width="<?php echo $widths[2]; ?>"></td>
		</tr>
		<?php settings_page($page); ?>
	</table>

	<?php
	if ($page != 3)
	{
		?>
		<div id="bottommsg" style="position:absolute; left:10px; bottom:10px; width:98%; height:35px; z-index:1">
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
					<td align="left"><input class="fatbuttom" type="submit" name="submit" value="<?php echo get_lang(45); ?>"/>
					&nbsp;<input class="fatbuttom" type="submit" name="button" value="<?php echo get_lang(27); ?>" onclick="javascript: window.close(); <?php 
					if ($reload) echo 'window.opener.location.reload();'; ?>"/></td>
					<td align="right" class="wtext"><?php echo get_lang(191); ?></td>
				</tr>
			</table>
		</div>
	<?php
	}
	?>
	</form>
	<?php	
	kprintend();
	die();
}


function webprocess()
{
	global $_POST, $userauth, $u_cookieid, $phpenv, $cfg;
	if ($cfg['disablelogin']) 
	{
		$userauth = 1;
		return true;
	} else
	{
		if (isset($_POST['user']) && isset($_POST['password']))
		{
			$user = mysql_escape_string($_POST['user']);
			$pass = mysql_escape_string($_POST['password']);
			if (!empty($user) && !empty($pass))
			{
				if (db_verify_user($user, $pass) == 1)
				{
					if ($cfg['demomode']) 
					{
						$result = db_execquery('SELECT u_sessionkey FROM '.TBL_USERS.' WHERE u_pass = "'.md5($pass).'" AND u_login = "'.$user.'"');
						$row = mysql_fetch_array($result);
						$num = $row['u_sessionkey'];
					} else
					{
						$num = getrand(1);
						db_login($user, $phpenv['remote']);
						db_update_session($num, $user);
						$u_cookieid = $num;
					}
					$userauth = 1;
					setcookie($cfg['cookie'],'');
					setcookie($cfg['cookie'],$num);
				}
			}
		}
	}
}

function db_verify_stream($cookie = 0, $ip)
{
	global $u_id, $setctl, $cfg, $validated_user;	
	if ($cfg['disablelogin']) 
	{
		$u_id = $cfg['assumeuserid'];
		loadvalidated($u_id);
		if ($validated_user === false) 
		{
			echo $cfg['assumeuserid'].' has a ID to a user that does not exist. Please set it correctly in the script and reload this page.';
			die();
		}
		return 1;
	} else
	{
		$result = db_execquery('SELECT u_id, u_login, u_pass, u_time FROM '.TBL_USERS.' WHERE u_sessionkey = '.$cookie);
		if ($result)
		{
			$row = mysql_fetch_array($result);
			$u_id = $row['u_id'];
			loadvalidated($u_id);
			$time = $row['u_time'];
			if ($cfg['demomode'] == 1) return 1;
			if ($setctl->get('timeout') != 0) if (($time + $setctl->get('timeout')) < time()) return 0;
			return mysql_num_rows ($result);
		}
		return 0;		
	}
}

function loadvalidated($uid)
{
	global $validated_user;
	if ($uid) $validated_user = mysql_fetch_array(db_execquery('SELECT * FROM '.TBL_USERS.' WHERE u_id = '.$uid));	
}

function db_verify_user($user, $pass)
{
	global $u_id;
	$result = db_execquery('SELECT u_id FROM '.TBL_USERS.' WHERE u_login = "'.$user.'" AND u_pass = "'.md5($pass).'" and u_booted = 0');
	$row = mysql_fetch_array($result);
	$u_id = $row['u_id'];
	return mysql_num_rows ($result);
}

function db_guinfo($field)
{
	global $validated_user;
	return $validated_user[$field];
}

function addhistory($u_id, $sid, $tid = 0)
{
	if (db_execquery('INSERT INTO '.TBL_MHISTORY.' SET u_id = '.$u_id.', s_id = '.$sid.', utime = '.time().', tid = '.$tid)) return mysql_insert_id();
}

function updatehistory($id, $pos)
{
	$res = db_execquery('SELECT s_id, dwritten FROM '.TBL_MHISTORY.' WHERE h_id = '.$id);
	if ($res && mysql_num_rows($res) == 1)
	{
		$row = mysql_fetch_row($res);
		$add = (int) $row[1];
		$add = $add + $pos;
		$res = db_execquery('SELECT fsize FROM '.TBL_SEARCH.' WHERE id = '.$row[0]);
		if ($res && mysql_num_rows($res) == 1)
		{
			$row = mysql_fetch_row($res);
			$size = $row[0];
			if ($add > $size) $add = $size;
			$dpercent = ($add / $size) * 100;
			db_execquery('UPDATE '.TBL_MHISTORY.' SET dwritten = '.$add.', dpercent = '.number_format($dpercent,0).' WHERE h_id = '.$id);
		}
	}
}

function getlasthistory($sid, $uid, $rhid=false)
{
	$res = db_execquery('SELECT s_id, utime, h_id FROM '.TBL_MHISTORY.' WHERE s_id = '.$sid.' AND u_id = '.$uid.' ORDER BY h_id DESC LIMIT 1');	
	if ($res !== false && mysql_num_rows($res) > 0)
	{
		$row = mysql_fetch_assoc($res);
		if ($rhid) return $row['h_id']; 
			else return $row['utime'];
	} 
	return 0;
}

function gethistory($u_id, $rows = 0)
{
	$sql = 'SELECT h.tid, h.utime, s.free, h.dpercent FROM '.TBL_MHISTORY.' h, '.TBL_SEARCH.' s WHERE h.s_id = s.id and h.u_id = '.$u_id.' ORDER BY h.utime DESC';
	if ($rows != 0) $sql .= ' LIMIT '.$rows;
	return db_execquery($sql);
}

function db_login($user, $ip)
{
	global $cfg;
	if ($cfg['demomode'] != 1)
	{
		db_execquery('UPDATE '.TBL_USERS.' SET u_ip = "'.$ip.'" WHERE u_login = "'.$user.'"');
		db_execquery('UPDATE '.TBL_USERS.' SET u_status = 1 WHERE u_login = "'.$user.'"');
	}
}

function get_archiver_combo($default)
{
	global $archivers;
	$out = '';
	for ($i=0,$c=count($archivers);$i<$c;$i++) 
	{
		if (is_array($archivers[$i]))
		{
			$out .= '<option value="'. $i. '"';
			if ($default == $i) $out .= ' selected="selected"'; 
			$out .= '>'.$archivers[$i][1].'</option>';
		}
	}
	return $out;
}

function db_logout($cookie, $ip)
{
	global $cfg;
	if ($cfg['demomode'] != 1) db_execquery('UPDATE '.TBL_USERS.' SET u_status = 0, u_sessionkey = 0 WHERE u_sessionkey = '.$cookie.' and u_ip = "'.$ip.'"');
}

function db_update_session($num, $user)
{
	global $cfg;
	if ($cfg['demomode'] != 1) db_execquery('UPDATE '.TBL_USERS.' SET u_sessionkey = "'.$num.'", u_time = '.time().' WHERE u_login like "'.$user.'"');
}

function show_userform($id = -1, $text = '', $name='', $pass='', $comment='', $login='', $access=1, $download=0, $udlrate=0, $allowemail = 0)
{
	global $PHP_SELF, $u_id;

	if ($id != -1)
	{
		$title = get_lang(95);
		$row = mysql_fetch_array(db_execquery('SELECT * FROM '.TBL_USERS.' where u_id = '.$id));
		$pass = "";
	}
	if ($id == -1) 
	{
		$title=get_lang(96); 
		$row['u_access'] = $access;
		$row['udlrate'] = 0;
		$row['u_allowdownload'] = $download;
		$row['u_name'] = $name;
		$row['u_login'] = $login;
		$row['u_comment'] = $comment;
		$row['udlrate'] = $udlrate;
		$row['allowemail'] = $allowemail;
		$row['u_booted'] = 0;
		$row['lameperm'] = 0;
	} 
		
	kprintheader($title, 1);
	?>
	<form method="post" action="<?php echo $PHP_SELF; ?>">
	<input type="hidden" name="action" value="usersave"/>
	<input type="hidden" name="u_id" value="<?php echo $id; ?>"/>
	<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr><td width="40%"></td><td width="50%"></td><td width="10%"></td></tr>

	<tr>
		<td class="wtext" colspan="3"><?php echo $text; ?></td>
	</tr>

<?php if ($id != -1 && $u_id != $id) { ?>
	<tr> 
      <td class="wtext"><?php echo get_lang(124); ?></td>
		 <td><input type="checkbox" name="booted" value="1" <?php echo checked($row['u_booted']); ?> /></td>
		<td class="wtext"><?php echo helplink('ubooted'); ?></td>
	</tr>
<?php } ?>

	<tr> 
		<td class="wtext"><?php echo get_lang(97); ?></td>
		<td><input type="text" name="name" class="userfield" value="<?php echo @$row['u_name']; ?>" /></td>
		<td class="wtext"><?php echo helplink('uname'); ?></td>
	</tr>
	<tr> 
		<td class="wtext"><?php echo get_lang(98); ?></td>
		<td><input type="text" name="login" class="userfield" value="<?php echo @$row['u_login']; ?>" /></td>
		<td class="wtext"><?php echo helplink('ulogin'); ?></td>
	</tr>

<?php if ($id != -1) { ?>
		<tr>
		<td class="wtext"><?php echo get_lang(99); ?></td>
		<td align="left"><input type="checkbox" name="passchange" value="1" /></td>
		<td class="wtext"><?php echo helplink('upasschange'); ?></td>
		</tr>
<?php } ?>

		<tr> 
		<td class="wtext"><?php echo get_lang(100); ?></td>
		<td width="490"><input type="password" name="password" class="userfield" value="<?php echo $pass; ?>" /></td>
		<td class="wtext"><?php echo helplink('upassword'); ?></td>
		</tr>    
		<tr> 
			<td class="wtext"><?php echo get_lang(101); ?></td>
			<td><input type="text" name="comment" class="userfield" value="<?php echo @$row['u_comment']; ?>" /></td>
			<td class="wtext"><?php echo helplink('ucomment'); ?></td>
		</tr>
		<tr> 
			<td class="wtext"><?php echo get_lang(102); ?></td>
			<td>
			<select name="access" class="userfield">
			<option value="0"<?php if ($row['u_access'] == 0) echo ' selected="selected"';?>><?php echo get_lang(138); ?></option>
			<option value="1"<?php if ($row['u_access'] == 1) echo ' selected="selected"';?>><?php echo get_lang(150); ?></option>
			</select>
			</td>
			<td class="wtext"><?php echo helplink('uaccess'); ?></td>
		</tr>
		<tr>
			<td class="wtext"><?php echo get_lang(133); ?></td>
			<td><input type="checkbox" name="download" value="1" <?php echo checked($row['u_allowdownload']); ?> /></td>
			<td class="wtext"><?php echo helplink('udownload'); ?></td>
		</tr>
		<tr> 
			<td class="wtext"><?php echo get_lang(149); ?></td>
			<td width="490"><input type="text" size="5" maxlength="5" name="udlrate" class="userfield" value="<?php echo $row['udlrate']; ?>" /></td>
			<td class="wtext"><?php echo helplink('udlrate'); ?></td>
		</tr>
		<tr> 
			<td class="wtext"><?php echo get_lang(222); ?></td>
			<td width="490"><input type="checkbox" name="lameperm" value="1" size="5" <?php echo checked($row['lameperm']); ?> /></td>
			<td class="wtext"><?php echo helplink('lameperm'); ?></td>
		</tr>
		<tr> 
			<td class="wtext"><?php echo get_lang(224); ?></td>
			<td width="490"><input type="checkbox" name="allowemail" value="1" size="5" <?php echo checked($row['allowemail']); ?> /></td>
			<td class="wtext"><?php echo helplink('allowemail'); ?></td>
		</tr>

		<tr><td colspan="3" height="10"></td></tr>
		<tr>		
		<td colspan="3" class="wtext">		
		<input type="submit" name="submit" value="<?php echo get_lang(45); ?>" class="fatbuttom" />&nbsp;
		<input type="submit" name="cancel" value="<?php echo get_lang(34); ?>" class="fatbuttom"/>
		</td>
		</tr>
  </table>
</form>
</body>
</html>
<?php
}

function KSignup()
{
	global $_POST, $_GET, $deflanguage;
	if (USERSIGNUP && empty($_GET['usersignup']) && empty($_POST['usersignup'])) 
	{
		if (isset($_POST['adduser'])) 
		{
			if (!empty($_POST['name']) && !empty($_POST['login']) && !empty($_POST['password'])) 
			{
				$mail = $_POST['email'];
				$result = db_execquery('INSERT INTO '.TBL_USERS.' SET u_name = "'.mysql_escape_string($_POST['name']).'", u_login = "'.mysql_escape_string($_POST['login']).'", u_pass = "'.
				md5(mysql_escape_string($_POST['password'])).'", u_comment = "'.mysql_escape_string($_POST['comment']).'", u_access = 1, u_allowdownload = 1, lang = '.$deflanguage.', email = "'.mysql_escape_string($mail).'", created = '.time());
				if ($result) signup_form(get_lang(259), false); 
					else signup_form(get_lang(56));
			} else signup_form(get_lang(56));
		} else signup_form(); 
	}	
}

function signup_form($error='', $controls = true)
{
	global $PHP_SELF;
	kprintheader(get_lang(96),1);
	?>
	<form method="post" action="<?php echo $PHP_SELF; ?>">
	<input type="hidden" name="signup" value="1"/>
	<input type="hidden" name="adduser" value="1"/>
	<table width="100%" align="center" border="0" cellpadding="2" cellspacing="2">
	<tr>
		<td class="wtext" colspan="2"><?php echo $error; ?></td>
	</tr>
	<tr> 
		<td width="50%" class="wtext"><?php echo get_lang(97); ?></td>
		<td width="50%"><input type="text" name="name" class="fatbuttom" value=""/></td>
	</tr>    
	<tr> 
		<td class="wtext"><?php echo get_lang(98); ?></td>
		<td><input type="text" name="login" class="fatbuttom" value=""/></td>
	</tr>
	<tr> 
		<td class="wtext"><?php echo get_lang(100); ?></td>
		<td><input type="password" name="password" class="fatbuttom" value=""/></td>
	</tr>    
	<tr> 
		<td class="wtext"><?php echo get_lang(101); ?></td>
		<td><input type="text" name="comment" class="fatbuttom" value=""/></td>
	</tr>
	<tr> 
		<td class="wtext"><?php echo get_lang(223); ?></td>
		<td><input type="text" name="email" class="fatbuttom" value=""/></td>
	</tr>
	<tr>
		<td></td>
		<td class="wtext">
			<?php if ($controls) { ?><input type="submit" name="submit" value="<?php echo get_lang(45); ?>" class="fatbuttom"/>&nbsp;<?php } ?>
			<input type="submit" name="cancel" value="<?php echo get_lang(27); ?>" onclick="javascript: window.close();" class="fatbuttom"/>
		</td>      
	</tr>
	</table>
	</form>
<?php
kprintend();
die();
}

function show_userhistory($id, $length = 20)
{
	global $PHP_SELF, $cfg;
	kprintheader(get_lang(121),1);
	$res = gethistory($id, $length);
	?>
	<form method="post" action="<?php echo $PHP_SELF; ?>">
	<input type="hidden" name="action" value="historyview"/>
	<input type="hidden" name="id" value="<?php echo $id; ?>"/>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td class="wtext" colspan="4">
			<input class="fatbuttom" type="button" onclick="javascript: location = '<?php echo $PHP_SELF.'?action=showusers'; ?>';" name="back" value="<?php echo get_lang(34); ?>"/>&nbsp;&nbsp;	
			<input class="fatfield" size="3" maxlength="5" type="text" name="length" value="<?php echo $length; ?>"/>&nbsp;
			<?php echo get_lang(178); ?>&nbsp;&nbsp;
			<input type="submit" value="<?php echo get_lang(107) ;?>" name="Refresh" class="fatbuttom"/>
		</td>
	</tr>
	<tr>
		<td colspan="3" height="15"></td>
	</tr>	
	<?php
		$tidarray = array(0 => get_lang(183), 1 => get_lang(117), 2 => get_lang(223), 3 => get_lang(267));	
		if ($res)
		{
			$cnt = 0;
			while ($row = mysql_fetch_assoc($res))
			{
				if (($cnt % 2) == 0) echo '<tr class="row2nd">'; else echo '<tr>';
				?>
					<td class="file"><?php echo $tidarray[$row['tid']]; ?></td>
					<td class="file"><?php echo strlen($row['free']) > 80 ? substr($row['free'], 0, 80) : $row['free']; ?></td>
					<td class="file"><?php echo date($cfg['dateformat'],$row['utime']); ?></td>
					<td class="file"><?php if ($row['tid'] == 0 || $row['tid'] == 1) echo $row['dpercent'].'%'; ?></td>
				</tr>
				<?php
				$cnt++;
			}
		}		
	?>
	<tr>
		<td colspan="3" height="15"></td>
	</tr>	
	</table>
	</form>
	<?php
	kprintend();
}

function show_users()
{
	global $PHP_SELF, $setctl, $u_id, $cfg;
	kprintheader(get_lang(121),1);

	$result = db_execquery('SELECT * FROM '.TBL_USERS.' ORDER BY u_time DESC');
	
	$pereach=0;
	$out = '<table width="570" border="0" cellspacing="0" cellpadding="0">';
	while ($row = mysql_fetch_array($result)) 
	{
		if (($pereach % 2) == 0) $out .= '<tr class="row2nd">'; else $out .= '<tr>';
		$pereach++;

		$ulogin = $row['u_login'];
		
		$uname = '<font title="'.date($cfg['dateformat'],$row['created']).'"';

		if ($row['u_access'] == 0) $uname .= ' color="red">'; else $uname .= '>';
		$uname .= $row['u_name'].'</font>';
		
		$out .= '<td width="90" class="file"><a class="hot" href="'. $PHP_SELF .'?action=useredit&amp;id='.$row['u_id'].'" title="'.get_lang(95).'">'. $ulogin. "</a></td>\n";
		$out .= '<td width="175" class="file">'. $uname. '</td>';
		$out .= '<td width="135" class="file"><font title="';
		$out .= date($cfg['dateformat'],$row['u_time']);
		$out .= '"> '.$row['u_ip']. '</font></td>';

		if ($setctl->get('timeout') != 0 && $row['u_status'] == 1)  if (((int)$row['u_time'] + $setctl->get('timeout')) < time()) $row['u_status'] = 0;

		switch ($row['u_status'] )
		{
			case 0: $stout = get_lang(104); break;
			case 1: $stout = '<font color="red">'.get_lang(103).'</font>'; break;	
		}
		$out .= '<td width="60" class="file">'. $stout. "</td>\n";
		$out .= '<td width="110" class="file">'."\n";
		
		if ($u_id != $row['u_id']) $out .= '<a class="hot" onclick="javascript: if (!confirm(\''.get_lang(175).'\')) return false;" href="'.$PHP_SELF.'?action=userdel&amp;id='.$row['u_id'].'" title="'.get_lang(105).'">'.get_lang(109).'&nbsp;&nbsp;</a>';
		$out .= '<a class="hot" href="'. $PHP_SELF .'?action=userhistory&amp;id='.$row['u_id'].'" title="'.get_lang(176).'">'.get_lang(177).'</a>&nbsp;&nbsp;';
		if ($row['u_status'] == 1 && $u_id != $row['u_id']) $out .= '<a class="hot" href="'. $PHP_SELF .'?action=userlogout&amp;id='.$row['u_id'].'" title="'.get_lang(106).'">'.get_lang(110).'</a>';
		$out .= '</td></tr>';
	}

	$out .= "</table>";
	$out .= "<form action=\"$PHP_SELF\" method=\"post\">". "\n";
	$out .= '<input type="hidden" name="action" value="useraction"/>';
	$out .= '<table width="600" border="0">'."\n";
	$out .= "<tr><td height=\"5\" colspan=\"6\"></td></tr>\n";
	$out .= "<tr><td colspan=\"8\">";
	$out .= '<input type="submit" value="'.get_lang(107).'" name="refresh" class="fatbuttom" />';
	$out .= '&nbsp;<input type="submit" value="'.get_lang(108).'" name="newuser" class="fatbuttom" />';
	$out .= '&nbsp;<input type="submit" value="'.get_lang(27).'" name="close" class="fatbuttom" onclick="javascript: window.close();" />';
	$out .= '</td></tr></table></form>';

    echo $out;
	kprintend();
	die();	
}

function user_saveoption($field, $value)
{
	global $u_id;
	db_execquery('UPDATE '.TBL_USERS.' SET '.$field.' = "'.mysql_escape_string($value).'" where u_id = '.$u_id);
	loadvalidated($u_id);
}

function save_useroptions($_POST)
{
	global $u_id, $deflanguage;
	$state = 0;
	$pass = "";
	if (@$_POST['extm3u'] == '1') $extm3u = 1; else $extm3u = 0;
	if (@$_POST['plinline'] == '1') $plinline = 1; else $plinline = 0;
	if (is_numeric($_POST['hotrows'])) $hotrows = $_POST['hotrows']; else $hotrows = 25;
	if (is_numeric($_POST['searchrows'])) $searchrows = $_POST['searchrows']; else $searchrows = 25;
	if (is_numeric($_POST['u_language'])) $ulang = $_POST['u_language']; else $ulang = 0;
	if (is_numeric($_POST['archer'])) $archer = $_POST['archer']; else $archer = 0;
	$lamerate = 0;
	if (isset($_POST['email'])) $email = mysql_escape_string($_POST['email']); else $email = '';
	if (isset($_POST['lamerate']) && is_numeric($_POST['lamerate'])) $lamerate = $_POST['lamerate'];
	if (isset($_POST['changepass']) && isset($_POST['password']) && !empty($_POST['password'])) 
	{
		if (isset($_POST['curpassword']))
		{
			if (db_guinfo('u_pass') == md5($_POST['curpassword']))
				$pass = md5($_POST['password']);
			else
			$state = 3;
		}		
	}
	$deflanguage = $ulang;
	db_execquery("UPDATE ".TBL_USERS." set extm3u = $extm3u, plinline = $plinline, hotrows = $hotrows, searchrows = $searchrows, lang = $ulang, archer = $archer, lamerate = $lamerate, email = '".$email."' where u_id = $u_id");
	if (!empty($pass)) 
	{ 
		db_execquery('UPDATE '.TBL_USERS.' set u_pass = "'.$pass.'" where u_id = '.$u_id); 
		$state = 2; 
	}
	loadvalidated($u_id);
	return $state;
}

function show_useroptions($msg='', $reload = false)
{
	global $PHP_SELF, $u_id, $klang, $deflanguage, $lamebitrates, $setctl;
	$result = db_execquery('SELECT * from '.TBL_USERS.' WHERE u_id = '.$u_id);
	if ($result) $row = mysql_fetch_array($result);
	if (!$row) die();
	if ($row['extm3u'] == 1) $ext3mu = 'checked="checked"'; else $ext3mu = '';
	if ($row['plinline'] == 1) $plinline = 'checked="checked"'; else $plinline = '';
	$langout = get_lang_combo($row['lang'], 'u_language');
	
	kprintheader(get_lang(123),1);
	?>
	<form name="useroptions" method="post" action="<?php echo $PHP_SELF; ?>">
	<input type="hidden" name="action" value="saveuseroptions"/>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">	
	<tr><td width="40%"></td><td width="50%"></td><td width="10%"></td></tr>
	<?php if (!empty($msg))
	{
	?>
		<tr><td class="importnant" colspan="3"><?php echo $msg; ?></td></tr>
		<tr><td height="10" colspan="3"></td></tr>
	<?php
	}
	?>
	<tr>
		<td class="wtext"><?php echo get_lang(111); ?></td>
		<td><input type="checkbox" value="1" name="extm3u" <?php echo $ext3mu; ?>/></td>
		<td class="wtext"><?php echo helplink('oextm3u'); ?></td>
	</tr>
	<tr>	
		<td class="wtext"><?php echo get_lang(255); ?></td>
		<td><input type="checkbox" value="1" name="plinline" <?php echo $plinline; ?>/></td>
		<td class="wtext"><?php echo helplink('plinline'); ?></td>
	</tr>
	<tr>
		<td class="wtext"><?php echo get_lang(112); ?></td>
		<td><input type="text" maxlength="4" size="4" class="fatbuttom" value="<?php echo $row['hotrows']; ?>" name="hotrows"/></td>
		<td class="wtext"><?php echo helplink('ohotrows'); ?></td>
	</tr>
	<tr>
		<td class="wtext"><?php echo get_lang(113); ?></td>
		<td><input type="text" maxlength="3" size="3" class="fatbuttom" value="<?php echo $row['searchrows']; ?>" name="searchrows"/></td>
		<td class="wtext"><?php echo helplink('osearchrows'); ?></td>
    </tr>	
	<tr>
		<td class="wtext"><?php echo get_lang(122); ?></td>
		<td><?php echo $langout; ?></td>
		<td></td>
    </tr>	
	<tr>
		<td class="wtext"><?php echo get_lang(99); ?></td>
		<td><input type="checkbox" name="changepass" value="1" /></td>
		<td class="wtext"><?php echo helplink('ochangepass'); ?></td>
	</tr>
	<tr>	
		<td class="wtext"><?php echo get_lang(164); ?></td>
		<td><input type="password" maxlength="10" size="10" class="fatbuttom" name="curpassword"/></td>
		<td class="wtext"><?php echo helplink('ocurpassword'); ?></td>
	</tr>
	<tr>	
		<td class="wtext"><?php echo get_lang(100); ?></td>
		<td><input type="password" maxlength="10" size="10" class="fatbuttom" name="password"/></td>
		<td class="wtext"><?php echo helplink('onewpassword'); ?></td>
	</tr>
	<tr>	
		<td class="wtext"><?php echo get_lang(166); ?></td>
		<td><select name="archer" class="fatbuttom"><?php echo get_archiver_combo($row['archer']); ?></select></td>
		<td class="wtext"><?php echo helplink('oarchiver'); ?></td>
	</tr>
	<?php if ($row['lameperm'] && $setctl->get('lamesupport'))
	{
	?>
	<tr>	
		<td class="wtext"><?php echo get_lang(220); ?></td>
		<td>
			<select name="lamerate" class="fatbuttom">
				<option value="0"<?php if ($row['lamerate'] == 0) echo ' selected="selected"'; ?>><?php echo get_lang(221); ?></option>
				<?php				
				for ($i=1;$i<count($lamebitrates);$i++)
				{
					$option = '<option value="'.$i.'"';
					if ($i == $row['lamerate']) $option .= ' selected="selected"';
					$option .= '>'.$lamebitrates[$i].'</option>';
					echo $option;
				}
				?>
			</select>
		</td>
		<td class="wtext"><?php echo helplink('olamerate'); ?></td>
	</tr>
	<?php
	}
	?>
	<tr>	
		<td class="wtext"><?php echo get_lang(223); ?></td>
		<td><input type="text" maxlength="128" size="30" class="fatbuttom" name="email" value="<?php echo $row['email']; ?>"/></td>
		<td class="wtext"><?php echo helplink('oemail'); ?></td>
	</tr>
	<tr><td colspan="3" height="10"></td></tr>
	<tr>
		<td>
		<input class="fatbuttom" type="submit" name="save" value="<?php echo get_lang(45); ?>"/>&nbsp;
		<input class="fatbuttom" type="button" name="closeme" value="<?php echo get_lang(27); ?>" onclick="javascript: window.close(); <?php 
			if ($reload) echo 'window.opener.location.reload();'; ?>"/>		
		</td>
		<td colspan="2" align="right" class="wtext"><?php echo get_lang(191); ?>&nbsp;</td>
	</tr>
	</table>
	</form>
	</body>
	</html>
	<?php	
}


function nextch($ssearch,$pos)
{
	for ($i=$pos,$c=strlen($ssearch);$i<$c;$i++) 
		if ($ssearch[$i] != ' ') return $i-1;
	return strlen($ssearch);
}

function nhghlist($sql, $header, $ndir, $special=0, $extra = '')
{
	global $cfg;
	$result = db_execquery($sql);
			
	kprintheader($header, 1);
	mp3table('');			
	
	showdir('',$ndir,0);

	echo $extra;

	$cntr=0;
	$many = 0;			
	echo '</td></tr>';
	while ($row = mysql_fetch_array($result)) 
	{
		if ($row['many'] >= $cfg['titlesperalbum'])
		{
			$f2 = new file2($row['id'], false);			
			$dir = $f2->relativepath;
			$many++;
			$ainf = gen_aheader($row['album'], $row['artist'], $row['lengths'], $row['many']);		
			switch ($special)
			{
				case 0: echo print_dir($row['drive'],$row['artist'].' - '.$row['album'], $dir, -1 , 1,'link.gif', $row['artist'], $ainf, '', true, 0, $row['album']); 
						break;
				case 1: if ($row['cntr'] > 0)  
						{
							echo print_dir($row['drive'],' '.lzero($cntr+1).' '.$row['artist'].' -  '.$row['album'], $dir, -1 , 1, 'link.gif',$row['cntr'].' hits - '.$row['many'].' tunes', $ainf, '', true, $row['cntr'], $row['album']);							
							$cntr++;
						}
						break;
				case 2: echo print_dir($row['drive'],date($cfg['dateformat'],$row['date']).' - '.$row['artist'].' - '.$row['album'], $dir, -1 , 1, 'link.gif',$row['artist'],$ainf, '', true,0, $row['album']); 
						break;
				
				case 3: if ($row['cntr'] > 0)  
						{
							echo print_dir($row['drive'], ' '.lzero($cntr+1).' '.$row['artist'].' -  '.$row['album'], $dir, -1 , 1, 'link.gif',$row['cntr'].' hits - '.$row['many'].' tunes', null, '', true, $row['many'], $row['album']);							
							$cntr++;
						}
						break;
			}
		}
	}
	
	if ($many==0) echo '<tr><td><font class="finfo">'.get_lang(10).'</font></td></tr>';
	
	endmp3table(0);
	infobox();
	echo '</tr></table></body></html>';	
	die();
}

function album_hotlist($type)
{
	global $PHP_SELF;
	$alf = '*0abcdefghijklmnopqrstuvwxyz';
	$chlist = $alfa = array();
	for ($i=0,$c=strlen($alf);$i<$c;$i++) $alfa[] = $alf[$i];
	$qres = db_execquery('SELECT LOWER(SUBSTRING(artist,1,1)) AS ch FROM '.TBL_SEARCH.' WHERE TRIM(album) != "" AND TRIM(artist) != "" GROUP BY SUBSTRING(artist,1,1)');
	while ($row = mysql_fetch_row($qres)) if (is_numeric($row[0])) $chlist['0'] = true; else $chlist[$row[0]] = true;
	$out = '';
	
	for ($i=0,$c=strlen($alf);$i<$c;$i++)
	{
		$add = false;
		if ($i == 0)
		{
			foreach ($chlist as $tch => $val) if (!in_array($tch, $alfa)) $add = true; 
		} else
			if (isset($chlist[$alf[$i]])) $add = true;

		if ($add)
			$out .= '<a title="'.get_lang(30, $alf[$i]).'" href="'.$PHP_SELF.'?action=hotselect&amp;'.$type.'='.$alf[$i].'" class="hot">'.$alf[$i].'</a>&nbsp;'; 
		else 
			$out .= '<font class="loginkplaylist">'.$alf[$i].'</font>&nbsp;';
	}
	return $out;
}

function genre_select($top = true, $default)
{
	$inf = new id3('');
	$res = db_execquery('SELECT genre FROM '.TBL_SEARCH.' WHERE genre != 255 AND TRIM(album) != "" GROUP BY genre ORDER BY genre');
	if ($top) $genres = '<select name="genreno" class="fatbuttom">'; else $genres = '';
	$cnt = 0;
	while ($row = mysql_fetch_array($res))
	{
		$gname = $inf->getgenre($row[0]);
		if (empty($gname)) continue;
		$glist[$cnt] = checkchs($gname);
		$glistid[$cnt] = $row[0];
		$cnt++;
	}

	if ($cnt > 0)
	{
		array_multisort($glist, $glistid, SORT_STRING);
		for ($i=0;$i<$cnt;$i++)
		{
			$selected = false;
			if (is_array($default))
			{
				foreach($default as $name) if ($name == $glistid[$i]) $selected = true;
			} else if ($glistid[$i] == $default) $selected = true;
			if ($selected)
			$genres .= '<option value="'.$glistid[$i].'" selected="selected">'.$glist[$i].'</option>'; 
				else
			$genres .= '<option value="'.$glistid[$i].'">'.$glist[$i].'</option>';
		}
	}
	if ($top) $genres .= '</select>';
	return $genres;
}

function hotselect($char='')
{
	$query = 'SELECT id,artist,album,free,drive,count(free) AS many, sum(lengths) AS lengths FROM '.TBL_SEARCH.' WHERE ';
	
	switch($char)
	{
		case '*': $query .= 'rtrim(artist) NOT REGEXP("^[0-9a-zA-Z]")'; break;
		case '0': 
				for ($i=0;$i<10;$i++) 
				{
					$query .= 'rtrim(artist) like "'.$i.'%"';
					if ($i < 9) $query .= ' or ';
				}
				break;
		default: $query .= 'rtrim(artist) like "'.$char.'%"'; break;
	} 
	$query .= ' and length(rtrim(album)) > 0 group by rtrim(album) order by artist';
	nhghlist($query, get_lang(31, $char), get_lang(30, $char));
}

function genrelist($genreno)
{
	$i = new id3('');
	$query = "select id,artist,album,free,drive,count(free) as many, sum(lengths) as lengths from ".TBL_SEARCH." where genre = ".$genreno.' and length(rtrim(album)) > 0 group by rtrim(album),genre order by artist';	
	nhghlist($query, get_lang(147), get_lang(153, $i->getgenre($genreno)));
}

function whats_hot($max=25, $filter = 0)
{
	$fsel = array(0 => '', 1 => '', 2 => '', 3 => '');
	
	if ($filter == 0) $filter = db_guinfo('hotmode');

	$fsel[$filter] = ' selected="selected"';
	$mode = 1;
	if ($filter == 0)
	{	
		$query = 'select sum(hits) as cntr, artist, id, album, bitrate, sum(lengths) as lengths, genre, drive, count(free) as many, free from '.TBL_SEARCH.' where rtrim(album) != "" group by album order by cntr desc, many desc limit '.$max;
	} else
	{
		$mode = 3;
		$uxfrom = 0;
		$uxto = time();

		switch ($filter)
		{
			case 1:
				$uxfrom = mktime(0, 0, 0, date('n'), date('j') - 7, date('Y'));
				$uxto = mktime(23, 59, 59, date('n'), date('j'), date('Y'));				
				break;
			case 2:
				$uxfrom = mktime(0, 0, 0, date('n'), date('j') - 31, date('Y'));
				$uxto = mktime(23, 59, 59, date('n'), date('j'), date('Y'));				
				break;			
			case 3:
				$uxfrom = mktime(0, 0, 0, date('n'), date('j') - 62, date('Y'));
				$uxto = mktime(23, 59, 59, date('n'), date('j') - 31, date('Y'));				
				break;
		}
		$query = 'SELECT s.*, sum(s.hits) as cntr, count(*) AS many, sum(h.dpercent) as rate FROM '.TBL_SEARCH.' s, '.TBL_MHISTORY.' h WHERE h.utime >= '.$uxfrom.' AND h.utime <= '.$uxto.
		' AND rtrim(s.album) != "" AND h.s_Id = s.id GROUP BY s.album ORDER BY many DESC limit '.$max;	
	}
	$extra = '</td></tr><tr><td height="5"></td></tr><tr><td class="notice">'.get_lang(238).':&nbsp; ';		
	$extra .= '<select name="hotperiod" class="fatbuttom">';
	$extra .= '<option value="0"'.$fsel[0].'>'.get_lang(239).'</option>';
	$extra .= '<option value="1"'.$fsel[1].'>'.get_lang(240).'</option>';
	$extra .= '<option value="2"'.$fsel[2].'>'.get_lang(241).'</option>';
	$extra .= '<option value="3"'.$fsel[3].'>'.get_lang(242).'</option>';
	$extra .= '</select>&nbsp;';
	$extra .= '<input type="submit" class="fatbuttom" name="hotoptions" value="'.get_lang(107).'"/>';	
	$extra .= '</td></tr><tr><td height="15"></td></tr><tr><td>';
	nhghlist($query, get_lang(3), get_lang(3), $mode, $extra);
}

function whats_new($cnt)
{
	$query = 'select *,count(free) as many,sum(lengths) as lengths from '.TBL_SEARCH.' where rtrim(album) != "" group by album order by date desc limit 0,'.$cnt;
	nhghlist($query, get_lang(4), get_lang(4)."!", 2);
}

function search($what, $where, $id3, $hitsas = 0)
{
	global $base_dir, $cfg;

	$ssearch = stripslashes($what); 	
	$ssearchlinefree = '';	

    kprintheader(get_lang(5), 1);
	$slines = array('text','opt');

	$i2=0;
	$quote=0;

	$ssearch = trim($ssearch);
	
	for ($i=0;$i<strlen($ssearch);$i++)	
	{
		if ($ssearch[$i] == ' ' && $quote==0) 
		{
			$i2++; 
			$slines['text'][$i2] = '';
			$i = nextch($ssearch,$i);
		} else if ($ssearch[$i] == '"') { if ($quote == 1) $quote=0; else $quote=1; }	
		else if ($ssearch[$i] == ';') { 
		} else @$slines['text'][$i2] .= $ssearch[$i]; 
	}

	$i2++;

	for ($i=0;$i<$i2;$i++)
	{
		if ($slines['text'][$i][0] == '-') 
		{
			$slines['opt'][$i] = 1; 
			$slines['text'][$i] = substr($slines['text'][$i],1);
		} else $slines['opt'][$i] = 0;
		if ($slines['text'][$i][0] == '+') $slines['text'][$i] = substr($slines['text'][$i],1);
	}

	if ($where == 0) $safter = 'album';
	if ($where == 1) $safter = 'title';
	if ($where == 2) $safter = 'artist';

	$ssearchline = 'AND ';
	for ($i=0;$i<$i2;$i++) 
	{ 
			if ($slines['opt'][$i] == 0) $ssearchline .= $safter.' LIKE "%'.$slines['text'][$i].'%"'; else
			if ($slines['opt'][$i] == 1) $ssearchline .= $safter.' NOT LIKE "%'.$slines['text'][$i].'%"';
			if (($i+1) < $i2) $ssearchline .= ' AND ';
	}
	
	for ($i=0;$i<$i2;$i++) 
	{ 
			if ($slines['opt'][$i] == 0) $ssearchlinefree .= 'concat(dirname,free) LIKE "%'.$slines['text'][$i].'%"'; else
			if ($slines['opt'][$i] == 1) $ssearchlinefree .= 'concat(dirname,free) NOT LIKE "%'.$slines['text'][$i].'%"';
			if (($i+1) < $i2) $ssearchlinefree .= ' AND ';
	}	

	if ($hitsas == 1) $extra = ',COUNT(free) AS many, SUM(lengths) AS lengths'; else $extra = '';
	
	if ($id3==0) 
	$query = 'SELECT *'.$extra.' FROM '.TBL_SEARCH.' WHERE f_stat = 0 '.$ssearchline.' OR '.$ssearchlinefree;
		else 
	$query= 'SELECT *'.$extra.' FROM '.TBL_SEARCH.' WHERE f_stat = 0 '.$ssearchline;	
	
	if ($hitsas == 1) $query .= ' AND LENGTH(rtrim(album)) > 0 GROUP BY RTRIM(album)';
	
	$query .= ' ORDER BY dirname, free ASC';

	$startt = microtime();
	$result = db_execquery($query);
	$endt   = microtime();

	$exectime = $endt-$startt;
	if ($exectime < 0) $execstr = '0.00'; else $execstr =  substr($exectime, 0, 4);

	mp3table('');
	$many = mysql_num_rows($result);
	$mwritten=0;

	$max = db_guinfo('searchrows');
	$extra="";
	if ($many > $max) $extra = get_lang(6, $max); 
	showdir('',get_lang(8, $ssearch),0);
	echo '<font class="wtext"> - '.get_lang(9)." $many $extra / $execstr ".get_lang(7).'</font>';
	echo '</td></tr>';
	$filter = 0;

	while ($row = mysql_fetch_array($result)) 
	{
		if ($mwritten+1 > $max) break;
		$f2 = new file2($row['id']);
		$fdesc = new filedesc($f2->fname);
		if ($fdesc->view && $f2->ifexists())
		{
			switch ($hitsas)
			{
				case 0: print_file($row['id'],1,1,$f2,$row['id']);
						break;
						
				case 1: $ainf = gen_aheader($row['album'], $row['artist'], $row['lengths'], $row['many']);		
						echo print_dir($row['drive'],date($cfg['dateformat'], $row['date']).' - '.$row['artist'].' - '.$row['album'], $f2->relativepath, -1 , 1, 'link.gif',$row['artist'],$ainf,$ssearch, true, 0, $row['album']);
						break;
			}			
			$mwritten++;			
		} else $filter++;
	}
	if ($many==0) echo '<tr><td><font class="finfo">'.get_lang(10).'</font></td></tr>';
	if ($filter>0) echo '<tr><td><font class="finfo">'.get_lang(264,$filter).'</font></td></tr>';

	endmp3table(0, 0, $mwritten);
	infobox();
	echo '</tr></table>';
	kprintend();
	die();
}

function md5file($file)
{
	$fp = fopen($file, "rb");
	if ($fp)
	{
		$md5data = fread($fp, 12284);
		fclose($fp);	
		return md5($md5data);
	}
	return null;
}

function getrelative($dir)
{
	$dirout = dirname($dir);
	if ($dirout == '.') $dirout = ''; 
		else 
	$dirout .= '/';
	return $dirout;
}

function search_qinscontruct($album, $title, $artist, $genre=255, $filein, $md5, $fsize, $lengths=0, $ratemode=0, $bitrate=0, $tagid=0, $drive=0, $mtime = 0)
{
	return 'INSERT INTO '.TBL_SEARCH.' (title, free, album, artist, md5, hits, date, fsize, genre, lengths, ratemode, bitrate, tagid, drive, mtime, dirname)  VALUES ("'.mysql_escape_string($title).'","'.
	mysql_escape_string(basename($filein)).'", "'.mysql_escape_string($album).'", "'.mysql_escape_string($artist).'", "'.$md5.'", 0, '.time().', '.$fsize.', '.$genre.', '.
	$lengths.', '.$ratemode.', '.$bitrate.', '.$tagid.', '.$drive.', '.$mtime.', "'.mysql_escape_string(getrelative($filein)).'")';
}

function search_qupdcontruct($album, $title, $artist, $genre=255, $filein, $md5,$id, $lengths=0, $ratemode=0, $bitrate=0, $tagid=0, $drive=0, $mtime = 0, $f_stat = 0, $fsize=0)
{
	return 'UPDATE '.TBL_SEARCH.' SET title = "'.mysql_escape_string($title).'", album = "'.mysql_escape_string($album).'", artist = "'.mysql_escape_string($artist).'", md5 = "'.$md5.'", free = "'.mysql_escape_string(basename($filein)).'", genre = '.$genre.', lengths = '.$lengths.', ratemode = '.$ratemode.', bitrate = '.$bitrate.', tagid = '.$tagid.', drive = '.$drive.', mtime = '.$mtime.', dirname = "'.mysql_escape_string(getrelative($filein)).'", f_stat = '.$f_stat.', fsize = '.$fsize.' WHERE id = '.$id;
}

function search_qupdfree($free, $drive, $id)
{
	return 'UPDATE '.TBL_SEARCH.' SET free = "'.mysql_escape_string(basename($free)).'", dirname = "'.mysql_escape_string(getrelative($free)).'", drive = '.$drive.' where id = '.$id;
}

function search_findid($free)
{
	$fsize = filesize($free);
	$md5 = md5file($free);
	if (!empty($md5))
	{
		$query = 'SELECT id FROM '.TBL_SEARCH.' WHERE md5 = "'.$md5.'" AND fsize = '.$fsize;
		$result = db_execquery($query);
		$row = mysql_fetch_array($result);
       		$cnt = mysql_num_rows($result);
		if ($cnt > 0) return $row['id']; else return 0;
	} 	
}

function updatesingle($free)
{
	global $base_dir;
	$id = search_findid($free);
	$fid = get_file_info($free);
	$drive = -1;
	for ($i=0;$i<count($base_dir);$i++)
	{
		$str = substr($free,0,strlen($base_dir[$i]));
		if (strcasecmp($base_dir[$i], $str) == 0) $drive = $i;
	}
	
	if ($fid && $drive != -1)
	{		
		$freestrip = substr($free, strlen($base_dir[$drive]));
	
		if (!$id)
		{
			$sfree = mysql_escape_string(basename($freestrip));
			$sdirname = mysql_escape_string(getrelative($freestrip));

			$res = db_execquery('SELECT id FROM '.TBL_SEARCH.' WHERE free = "'.$sfree.'" AND dirname = "'.$sdirname.'"');
			
			if ($res && mysql_num_rows($res) == 1) 
			{
				$row = mysql_fetch_row($res);
				$id = $row[0];
			}
		}

		if ($id)
			$query = search_qupdcontruct($fid['album'], $fid['title'], $fid['artist'], vernumset($fid['genre'],255), $freestrip, md5file($free), $id, $fid['lengths'], $fid['ratemode'], $fid['bitrate'], $fid['tagid'], $drive, filemtime($free), 0, filesize($free));
		else
			$query = search_qinscontruct($fid['album'], $fid['title'], $fid['artist'], vernumset($fid['genre'],255), $freestrip, md5file($free), filesize($free), $fid['lengths'], $fid['ratemode'], $fid['bitrate'], $fid['tagid'], $drive, filemtime($free));
		
		db_execquery($query);
		return $id;
	}
}

function search_updatevote($id)
{
	$query = 'UPDATE '.TBL_SEARCH.' SET hits = hits + 1 WHERE id = '.$id;
	db_execquery($query);
}

function search_updatelist_options()
{
	global $PHP_SELF;
	kprintheader(get_lang(11), 1);
	?>
	<form name="updateoptions" method="post" action="<?php echo $PHP_SELF; ?>">
	<input type="hidden" name="action" value="performupdate"/>
	<table width="400" border="0">
	<tr>
	<td colspan="3" class="warning">
	</td>
	</tr>
	<tr>
		<td colspan="3" class="wtext"><?php echo helplink('whatisupdate', get_lang(160)); ?></td>
	</tr>
	<tr>
		<td class="wtext"><?php echo get_lang(12);?></td>
		<td><input type="checkbox" value="1" name="deleteunused"/></td>
		<td class="wtext"><?php echo helplink('updatedeleteunused'); ?></td>
	</tr>	
	<tr>
		<td class="wtext"><?php echo get_lang(14);?></td>
		<td><input type="checkbox" value="1" name="debugmode"/></td>
		<td class="wtext"><?php echo helplink('updatedebugmode'); ?></td>
	</tr>	
	<tr><td colspan="3" height="10"><hr size="1"/></td></tr>
	<tr>
		<td colspan="3">
		<input class="fatbuttom" type="submit" name="go" value="<?php echo get_lang(15);?>"/>&nbsp;
		<input type="button" value="<?php echo get_lang(16); ?>" name="Cancel" class="fatbuttom" onclick="javascript: self.close();"/>
		</td>
	</tr>
	</table>
	</form>
	<?php
	kprintend();	
}

function search_updatelist($options="")
{
	global $base_dir, $win32, $gData, $gCnt;
	kprintheader(get_lang(17), 1);
	
	if (@$options['deleteunused'] == '1') $deleteunused = 1; else $deleteunused = 0;
	if (@$options['debugmode'] == '1') $debugmode = 1; else $debugmode = 0;
	if (@$options['sleeppertrans'] != 0) $sleeptrans = $options['sleeppertrans']; else $sleeptrans = 0;

	$db_out = $db_mtime = $db_unique = $db_path = array();	

	$filecntr = 0;
	$file = '';

	$updateall = false;

	echo '<font class="notice">'.get_lang(136).'..</font><br/>';
	flush();

	$data = array();
	$basedirlen = array();
	$datacnt = 0;
	
	$starttime = time();

	for ($i=0,$c=count($base_dir);$i<$c;$i++)
	{
		$gData = array();
		$gCnt = 0;
		GetDirArray($base_dir[$i]);
		$basedirlen[$i] = strlen($base_dir[$i]);
		$data[$i] = $gData;
		$gData = array();
		$datacnt += count($data[$i]);
	}

	if ($datacnt > 0)
	{
		$result = db_execquery ('SELECT count(*) FROM '.TBL_SEARCH);
		$row = mysql_fetch_row($result);
		$dbrows = $row[0];
		$result = db_execquery ('SELECT count(*) FROM '.TBL_SEARCH.' WHERE dirname = "0"');
		$row = mysql_fetch_row($result);
		$forupdate = $row[0];

		if ($dbrows == $forupdate) $updateall = true;
		$result = db_execquery('SELECT fsize, id, md5, free, drive, mtime, dirname FROM '.TBL_SEARCH.' ORDER BY id ASC', true);

		$dcntr=0;
		while ($row = mysql_fetch_row($result)) 
		{
			$db_out[$dcntr++] = $row;
			if (!isset($db_mtime[$row[0]][$row[5]])) $db_mtime[$row[0]][$row[5]] = $dcntr - 1;
			if (!isset($db_unique[$row[0]][$row[2]])) $db_unique[$row[0]][$row[2]] = $dcntr - 1;

			$path = crc32($row[6].$row[3]);

			if (isset($db_path[$path]))
			{
				$ids = $db_path[$path];
				$ids[] = $dcntr; 
				$db_path[$path] = $ids;
			} else $db_path[$path] = array($dcntr);
		}

		if ($updateall) $db_mtime = array();
		
		$query=null;
		
		$totalqupds = $dcntr;	
		
		echo '<font class="notice">'.get_lang(18, $datacnt).'</font><br/><br/>';
		echo '<div id="up_status" class="notice"></div><br/>';
		flush();

		$totalins = $datacnt;	
		$qins = $qupd = $failed = $qupdins = $skips= $qdels = $perten = $fdups = 0;
		
		if ($datacnt > 0)
		{
			for ($drive=0,$drivec=count($data);$drive<$drivec;$drive++)
			{
				for ($i=0,$ic=count($data[$drive]);$i<$ic;$i++)
				{
					$file = $data[$drive][$i];
					$filein = substr($file, $basedirlen[$drive]);

					$perten++;					
					if ($perten == 50 || $debugmode)
					{
						if ($totalins > $totalqupds)
						{
							$percent2 = (($qins + $qupd + $skips + $fdups) / $totalins) * 100;
							if ($percent2 < 100) $percent2 = number_format($percent2,0).'%'; else $percent2 = '100%';
						} else $percent2 = '100%';
						if ($qupd > 0 && $totalqupds>0)
						{ 
							$percent = (($qupd + $qupdins) / $totalqupds) * 100;
							if ($percent < 100) $percent = number_format($percent,0).'%'; else $percent='100%';
						} else $percent='100%';
						$perout = get_lang(20,$percent2,$percent);					
						echo '<script type="text/javascript">d = document.getElementById("up_status"); d.innerHTML="'.$perout;
						echo (strlen($filein) > 60) ? addslashes(substr($filein,0,60))."..." : addslashes($filein);
						echo '";</script>';
						flush();
						$perten = 0;
					}

					$fsize = filesize($file);
					$mtime = filemtime($file);

					if (!$fsize)
					{						
						echo '<font class="notice">'.get_lang(19,$file).'</font><br/>';
						flush();
						$skips++;
						continue;
					}

					$filecntr++;
					if (isset($db_mtime[$fsize][$mtime]))
					{
						$i2 = @$db_mtime[$fsize][$mtime];
						if ($db_out[$i2][0] != -1 && $db_out[$i2][6].$db_out[$i2][3] == $filein && $db_out[$i2][4] == $drive)
						{
							$db_out[$i2][0] = -1;						
							$qupd++;
							continue;						
						}
					}
					
					$md5 = md5file($file);
					if ($sleeptrans > 0 && !$win32) usleep($sleeptrans);

					if (!empty($md5))
					{
						if (isset($db_unique[$fsize][$md5])) 
						{
							$idupdate = $db_unique[$fsize][$md5]; 
	
							if ($db_out[$idupdate][0] == -1)
							{
								echo '<font class="notice">'.get_lang(168, $filein,  $db_out[$idupdate][6].$db_out[$idupdate][3]).'</font><br/><br/>';
								$fdups++;
								continue;								
							}
						} else $idupdate = -1;

						if ($idupdate != -1)
						{
							$db_out[$idupdate][0] = -1;

							if ($mtime != $db_out[$idupdate][5])
							{
								$fid = get_file_info($file);
								$query = search_qupdcontruct($fid['album'], $fid['title'], $fid['artist'], vernumset($fid['genre'],255), $filein, $md5, $db_out[$idupdate][1], $fid['lengths'], $fid['ratemode'], $fid['bitrate'], $fid['tagid'], $drive, $mtime, 0, $fsize);							
								$qupdins++;
							}
							else
							if ($db_out[$idupdate][6].$db_out[$idupdate][3] != $filein || $db_out[$idupdate][4] != $drive || $updateall)
							{
								$query = search_qupdfree($filein, $drive, $db_out[$idupdate][1]);
								$qupdins++;
							}
							$qupd++;
						} 
						else
						{
							$frel = getrelative($filein);
							$ffilein = basename($filein);
							$checkex = crc32($frel.$ffilein);
							$useid = -1;
							if (isset($db_path[$checkex]))
							{
								$ids = $db_path[$checkex];
								for ($i3=0,$c3=count($ids);$i3<$c3;$i3++)
								{
									$cid = $ids[$i3] - 1;
									if ($db_out[$cid][3] == $ffilein && $db_out[$cid][6] == $frel)
									{
										$useid = $cid;
										break;
									}
								}							
							}

							$fid = get_file_info($file);								
							
							if ($useid == -1) 
							{
								$query = search_qinscontruct($fid['album'], $fid['title'], $fid['artist'], vernumset($fid['genre'],255), $filein, $md5, $fsize, $fid['lengths'],$fid['ratemode'], $fid['bitrate'], $fid['tagid'], $drive, $mtime);
								$db_out[$dcntr++] = array(-1, 0, $md5, $filein, $drive, $mtime, $filein);
								$db_unique[$fsize][$md5] = $dcntr-1;
								$qins++;								
							} else
							{
								$query = search_qupdcontruct($fid['album'], $fid['title'], $fid['artist'], vernumset($fid['genre'],255), $filein, $md5, $db_out[$useid][1], $fid['lengths'], $fid['ratemode'], $fid['bitrate'], $fid['tagid'], $drive, $mtime, 0, $fsize);	
								$qupdins++;
							}						
						}

						if ($query !== null)
						{
							$result = db_execquery($query, true);

							if (!$result)
							{
								$failed++;
								echo '<font class="wtext">'.get_lang(22, $query).'</font><br/>';
							}
							$query=null;
						}
					} else
					{
						echo '<font class="notice">'.get_lang(23,$file).'</font><br/>';
						flush();
						$skips++;
					}
				} // end of file loop
			} // end of drive loop
		} // if found any files
		$fordel = 0;
		for ($i2=0;$i2<$dcntr;$i2++)
		if ($db_out[$i2][0] != -1) $fordel++;

		if ($deleteunused)
		{		
			if ($skips == 0)
			{
				for ($i2=0;$i2<$dcntr;$i2++)
				if ($db_out[$i2][0] != -1) 
				{
					echo '<font class="notice">'.get_lang(24, $db_out[$i2][6].$db_out[$i2][3]);
					$result = db_execquery('DELETE FROM '.TBL_SEARCH.' WHERE id = '.$db_out[$i2][1], true);
					if ($result) $qdels++;
					echo '</font><br/>';
				}
			}
		}
		$runtime = (time() - $starttime);

		echo '<script type="text/javascript">d = document.getElementById("up_status"); d.innerHTML="";</script>';         
		echo '<br/><font class="notice">'.get_lang(26).'<br/><br/>';
		echo get_lang(25, $qins, $qupdins, $qdels, $failed, $skips, $filecntr, $runtime, $fordel);
		echo '</font><br/><br/>';
		echo '<input type="button" value="'.get_lang(27).'" name="close me" class="fatbuttom" onclick="javascript: self.close();"/>';
	} 
	else 
	{
		$prbasedir="";
		for ($i=0;$i<count($base_dir);$i++)	$prbasedir .= $base_dir[$i];
		echo '<br/><font class="notice">'.get_lang(28, $prbasedir).'</font><br/>';
	}
	kprintend();
}

function search_updateautomatic($user, $host, $waittrans=0)
{
	global $cfg;

	if ($cfg['autoupdate'])
	{
		if ($host == $cfg['autoupdatehost'] && $user == $cfg['autoupdateuser'])
		{ 
			$options = array('deleteunused', 'rebuildid3', 'debugmode');
			$options['deleteunused'] = 0;
			$options['rebuildid3'] = 0;
			$options['debugmode'] = 0;
			$options['sleeppertrans'] = $waittrans;
			search_updatelist($options);
		} else echo "Wrong host ($host) or user ($user) for update.";
	} 
	die();
}


$crlf = "\r\n";
$addcrlf = false;

function getarchiveline($archiver, $destination, $file, $flist = '')
{
	global $archivers;
	if (isset($archivers[$archiver]))
	if ($archivers[$archiver][0])
	{
		$out = $archivers[$archiver][2];
		$out = str_replace('%D', $destination, $out); 
		$out = str_replace('%F', $file, $out); 
		$out = str_replace('%LIST', $flist, $out);
		return $out;
	}
	return 0;
}

class kparchiver
{
	var $files;

	function kparchiver()
	{
		$this->files = array();
	}
	
	function setfile($file)
	{
		$this->files[] = $file;
	}

	function execute()
	{
		global $win32, $cfg, $archivers, $PHP_SELF, $u_id;
		
		$listmode = false;

		kprintheader(get_lang(260),0);
		echo '<div id="up_status" class="notice">0%</div><br/>';
		flush();
		
		$usearc = 0;
		$preferarc = db_guinfo('archer');
		if (isset($archivers[$preferarc]) && $archivers[$preferarc][0] == 1) $usearc = db_guinfo('archer');
		$tf = tempnam($cfg['archivetemp'], 'kppack');
		$f = $tf . '.'.$archivers[$usearc][1];

		if (strpos($archivers[$usearc][2],'%LIST') !== false) $listmode = true;
		
		$cwd = getcwd();
	
		$files = 0;
		for ($i=0,$c=count($this->files);$i<$c;$i++)
		{
			$f2 = new file2($this->files[$i]);
			if ($f2->ifexists()) 
			{
				if ($listmode)
				{
					$fp = fopen($tf, 'a');
					if ($fp) 
					{
						fwrite($fp, $f2->fullpath.$cfg['archivefilelist_cr']);
						fclose($fp);
					}
				}
				$files++;
			}
		}

		if ($listmode)
		{
			$run = getarchiveline($usearc, $f, $f2->getfullpath($win32), $tf);
			if ($cfg['archivemodedebug']) echo($run); else exec($run);
			echo '<script type="text/javascript">d = document.getElementById("up_status"); d.innerHTML="100%";</script>';
		} else
		{
			$cnt = 0;
			for ($i=0,$c=count($this->files);$i<$c;$i++)
			{
				$f2 = new file2($this->files[$i]);
				if ($f2->ifexists()) 
				{
					chdir($f2->getdrivedir());
					$run = getarchiveline($usearc, $f, $f2->getfullpath($win32));
					if (!empty($run)) 
					{
						if ($cfg['archivemodedebug']) echo($run); else exec($run);						
					}
					$cnt++;
					$per = ($cnt / $files) * 100;
					$per = number_format($per, 0).'%';
					echo '<script type="text/javascript">d = document.getElementById("up_status"); d.innerHTML="'.$per;
					echo ' .. '.$f2->fname;
					echo '";</script>';
					flush();
				}
			}
		}
		
		chdir($cwd);
		if (file_exists($f)) 
		{
			for ($i=0,$c=count($this->files);$i<$c;$i++)
			{
				$f2 = new file2($this->files[$i]);
				if ($f2->ifexists()) 
				{
					$fdesc = new filedesc($f2->fname);
					if ($u_id && $fdesc->logaccess) $hid = addhistory($u_id, $f2->sid, 3);
				}				
			}
			
			?>
				<form action="<?php echo $PHP_SELF; ?>" method="post">			
				<input type="hidden" name="action" value="downloadarchive"/>
				<input type="hidden" name="file" value="<?php echo basename($f); ?>"/>
				<input type="hidden" name="mime" value="<?php echo $archivers[$usearc][3]; ?>"/>
				<table width="95%" cellpadding="0" cellspacing="0" border="0" align="center">
				<tr>
					<td class="notice"><?php echo get_lang(65); ?></td>
					<td><input type="text" class="fatbuttom" name="filename" value="<?php echo 'kpdl'.date('hi').'.'.$archivers[$usearc][1]; ?>"/></td>
				</tr>
				<tr>
					<td></td>
					<td>
						<input type="submit" class="fatbuttom" name="download" value="<?php echo get_lang(117); ?>"/>
						<input type="button" value="<?php echo get_lang(27); ?>" name="close" class="fatbuttom" onclick="javascript: window.close();"/>
					</td>
				</tr>
				</table>
				
				</form>
			<?php
		} else echo '<font class="notice">'.get_lang(167).'</font>'; 
		@unlink($tf);
		kprintend();
	}
}

function httpstreamheader2($ftype=1, $sid)
{
	global $phpenv, $streamtypes, $crlf, $addcrlf, $setctl, $u_cookieid;	
	if (isset($streamtypes[$ftype]))
	{
		if ($setctl->get('sendfileextension')) 
			$fend = '&file=.'.$streamtypes[$ftype][0]; 
		else $fend = '';
		if ($streamtypes[$ftype][2] == 1) 
		{
			if ($addcrlf) echo $crlf;
			echo $setctl->get('streamurl').$phpenv['streamlocation'].'?streamsid='.$sid.'&c='.$u_cookieid.$fend;
			$addcrlf = true;
		}
	}
}

function kpgenerateid3v2tag($sid)
{
	global $cfg;
	if ($cfg['enablegetid3'])
	{
		$f2 = new file2($sid, true);

		$data['id3v2']['TIT2']['encodingid'] = 0;
		$data['id3v2']['TIT2']['data']       = $f2->id3['title'];
		$data['id3v2']['TPE1']['encodingid'] = 0;
		$data['id3v2']['TPE1']['data']       = $f2->id3['artist'];
		$data['id3v2']['TALB']['encodingid'] = 0;
		$data['id3v2']['TALB']['data']       = $f2->id3['album'];
		
		switch (GETID3_V)
		{
			case 16:
					require_once(GETID3_INCLUDEPATH.'getid3.id3v2.php');					
					return GenerateID3v2Tag($data['id3v2'], 3, 0, 0, '', false, false, false); 
					break;					
		}
	}	
}

function mkextinf2($name, $lengths)
{
	global $crlf;
	return $crlf.'#EXTINF:'.$lengths.','.$name;
}

function decode64($b64)
{
	$data = '';
	if (!empty($b64)) $data = stripslashes(base64_decode($b64));
	if (checkstructure($data, false) == 0) return $data; else die();
}

function kPlay_sendlink2($sid)
{
	$f2 = new file2($sid, true);
	if ($f2->ifexists())
	{
		$fd = new filedesc($f2->fname);
		if ($fd->found && $fd->m3u)
		{
			if (db_guinfo('extm3u')) echo mkextinf2($f2->fname, $f2->getlengths());
			httpstreamheader2($fd->fid, $sid);
		}
	}
}

function kplay_m3uurl()
{
	global $addcrlf;
	$num = lzero(getrand(1,999999),6);
    if (db_guinfo('plinline')) $method = 'inline'; else $method = 'attachment';
	header('Content-Disposition: '.$method.'; filename="kp'.$num.'.m3u"');
	header('Content-Type: audio/x-mpegurl');
	if (db_guinfo('extm3u'))
	{ 
		echo '#EXTM3U';
		$addcrlf = true;
	}
}

function Kplay_resource2($sid, $many=0)
{
	$f2 = new file2($sid, true);
	if ($f2->ifexists())
	{
		$fd = new filedesc($f2->fname);
		if ($fd->m3u)
		{
			kplay_m3uurl();
			if (!$many)
			{
				if (db_guinfo('extm3u')) echo mkextinf2($f2->fname, $f2->getlengths());
				httpstreamheader2($fd->fid, $sid);
			}
		} else Kplay_senduser2($sid, 1);	
	}
}

class kq_Measure
{
	var $start = 0;
	var $alarm = 0;
	
	function getmicrotime()
	{ 
		list($usec, $sec) = explode(" ",microtime());
		return ((float)$usec + (float)$sec);
    }

	function start()
	{
		$this->start = $this->getmicrotime();
		usleep(100);
	}

	function setalarm($alarm)
	{
		$this->alarm = $alarm;
	}

	function alarm()
	{
		if ( ($this->getmicrotime() - $this->start)   >= (float)$this->alarm) return true;
		return false;
	}	
}

function streamfp($fp, $kbit, $prebuffer=true)
{
	global $streamsettings;
	@ini_set("output_buffering", 0);
	$bread = ($kbit * 1000) / 8;
	$kqm = new kq_Measure();
	$kqm->setalarm($streamsettings['sleep']);

	if ($streamsettings['preload'] && $prebuffer)
	{
		$prebuff = ($bread / 100) * (int)$streamsettings['preload'];
		echo fread($fp, $prebuff);
		flush();
	}

	$breadbuf = ($bread / 100) * (int)$streamsettings['buffer'];
	$precision = (int)$streamsettings['precision'];	

	$kqm->start();
	while (!feof($fp) && !connection_aborted())
	{
		echo fread($fp, $breadbuf);
		flush();
		while (!$kqm->alarm()) usleep($precision);
		$kqm->start();
	}
}

function getlame($bitrate=128,$file)
{
	global $setctl;
	$out = str_replace('%bitrate%', $bitrate, $setctl->get('lamecmd'));
	$out = str_replace('%file%', $file, $out);
	return $out;
}


function Kplay_senduser2($sid, $inline=0)
{
	global $win32, $_SERVER, $setctl, $streamsettings, $u_id, $lamebitrates;
	ignore_user_abort(true);
	$hid = 0;
	$f2 = new file2($sid, true);
	if ($f2->ifexists())
	{
		$fp = fopen($f2->fullpath, "rb");
		if ($fp)
		{
			$fdesc = new filedesc($f2->fname);
			if ($u_id && db_guinfo('lameperm') && db_guinfo('lamerate') != 0 && $setctl->get('lamesupport') && $fdesc->gid == 1) $uselame = true; else $uselame = false;
			$posfrom = 0;
			if (isset($_SERVER['HTTP_RANGE']))
			{
				$data = explode('=',$_SERVER['HTTP_RANGE']);
        		$ppos = explode('-', trim($data[1]));
        		$posfrom = (int)trim($ppos[0]);
			}

			if ($posfrom == 0)
			{
				$lastux = getlasthistory($sid, $u_id);
				if ($lastux + 3 < time()) 
				{
					search_updatevote($sid);
					if ($u_id && $fdesc->logaccess) $hid = addhistory($u_id, $sid);
				}
			}

			if (!ALLOWSEEK) $posfrom = 0;

			if ($posfrom > 0)
				$hid = getlasthistory($sid, $u_id, true);
			
			if (!$inline)
			{		
				header('Content-Disposition: filename='.$f2->fname); 
				if (ALLOWSEEK && !$uselame) 
				{
					header('Content-Length: '.$f2->fsize);
					header('Accept-Ranges: bytes');
				}
			} else
			{
				header('Content-Disposition: inline; filename='.$f2->fname);
				if (!$uselame) 
				{
					header('Content-Length: '.$f2->fsize);
					header('Accept-Ranges: bytes');
				}
			}

			header('Content-Type: '.$fdesc->mime);
			header('Content-Range: bytes '.$posfrom.'-');

			if ($setctl->get('writeid3v2') && $fdesc->gid == 1 && $posfrom == 0)
			{
				$id = fread($fp, 3);
				if ($id != 'ID3') echo kpgenerateid3v2tag($sid);
				fseek($fp, 0, SEEK_SET);
			}

			if ($posfrom > 0)
			{				
				header('Partial content', true, 206);
				if ($posfrom == ($f2->fsize - 129) || $posfrom == ($f2->fsize - 128)) // request id3v1
				{
					fseek($fp, -128, SEEK_END);
					echo fread($fp, 128);
					rewind($fp);
				} else fseek($fp, $posfrom);
			}

			if ($uselame)
			{
				if ($win32)
				{
					$descriptorspec = array(
						0 => array('pipe', 'r'),
						1 => array('pipe', 'w'));

					$process = proc_open(getlame($lamebitrates[db_guinfo('lamerate')], $f2->fullpath), $descriptorspec, $pipes);
					if (is_resource($process))
					{
						while (!feof($pipes[1]) && !connection_aborted()) echo fgets($pipes[1], 1024);
						fclose($pipes[0]);
						fclose($pipes[1]);
						proc_close($process);
					}
				} else
				{
					$temp = @popen(getlame($lamebitrates[db_guinfo('lamerate')], $f2->fullpath), 'r');
					while ($data = @fread($temp, 131072)) echo $data;
					pclose($temp);
				}
				die();
			}

			if ($setctl->get('streamingengine') && !$win32)
			{
				if (in_array ($f2->id3['bitrate'],$streamsettings['bitrates']) && $f2->id3['ratemode'] == 1)  // cbr
					streamfp($fp, $f2->id3['bitrate']);
						else
					streamfp($fp, $streamsettings['defaultrate']);
					if ($hid != 0) updatehistory($hid, ftell($fp) - $posfrom);
			} else 
			{	
				while (!feof($fp) && !connection_aborted()) echo fread($fp, 32768);
				if ($hid != 0) updatehistory($hid, ftell($fp) - $posfrom);
			}
			@fclose($fp);
		}
	}
	die();
}

function kplay_archivedownload($file, $mime, $name)
{
	global $dlrate, $win32;
	$fp = fopen($file, 'rb');
	if ($fp)
	{
		header('Content-Type: '.$mime);
		header('Content-Disposition: inline; filename="'.$name.'"');
		header('Content-Length: '.filesize($file));

		if (db_guinfo('udlrate')) $udlrate = db_guinfo('udlrate');
			else
		if ($dlrate) $udlrate = $dlrate;
			else
		$udlrate = 0;

		if ($udlrate && !$win32) streamfp($fp, $udlrate, false);
			else
		fpassthru($fp);
		if (!connection_aborted()) fclose($fp);
	}
}

function Kplay_download($sid)
{
	global $win32, $dlrate, $curdrive, $u_id;
	ignore_user_abort(true);
	if (ALLOWDOWNLOAD && db_guinfo('u_allowdownload'))
	{
		$f2 = new file2($sid);
		if ($f2->ifexists())
		{
			$fp = fopen($f2->fullpath, 'rb');	
			if ($fp)
			{
				$hid = 0;
				if ($u_id) $hid = addhistory($u_id, $sid, 1);
				$fdesc = new filedesc($f2->fname);			
				header('Content-Type: '.$fdesc->mime);
				header('Content-Disposition: attachment; filename="'.$f2->fname.'"');
				header('Content-Length: '.$f2->fsize);

				if (db_guinfo('udlrate')) $udlrate = db_guinfo('udlrate');
					else
				if ($dlrate) $udlrate = $dlrate;
					else
				$udlrate = 0;
			
				if ($udlrate && !$win32) streamfp($fp, $udlrate, false);
					else
				while (!feof($fp) && !connection_aborted()) echo fread($fp, 32768);
				
				if ($hid != 0) updatehistory($hid, ftell($fp));
				if (!connection_aborted()) fclose($fp);
			} 
		}
	}
	die();
}

function print_dir($drive,$name, $pdir, $nr, $return=0,$image='dir.gif',$title='', $ainf=null, $mark='', $showalbum = false, $hits = 0, $albumid='')
{
	global $PHP_SELF;
	if (!empty($pdir)) $pdir_64 = base64_encode($pdir); else $pdir_64='';
	$out = '<tr><td>&nbsp;';
	if ($showalbum) 
	{
		$out .= '<a href="'.$PHP_SELF.'?n='.$nr.'&amp;p='.$pdir_64.'&amp;d='.$drive.'&amp;ft='.$albumid.'&amp;action=playalbum" class="dir">';
		$out .= '<img alt="'.get_lang(42).'" src="'.getimagelink('album.gif').'" border="0"/>&nbsp;';
		$out .= '</a>';
	}
	if ($nr != -1) $md = md5($name); else $md = '';
	$out .= '<a href="'.$PHP_SELF.'?n='.$nr.'&amp;n2='.$md.'&amp;p='.$pdir_64.'&amp;d='.$drive.'&amp;mark='.urlencode($mark).'" class="dir">';
	$out .= '<img alt="'.$name.'" src="'.getimagelink($image).'" border="0"';
	if (!empty($title)) $out .= ' title="'.get_lang(116, checkchs($pdir)).'"';
	$out .= '/>&nbsp;'.checkchs($name).'</a>';
	if ($ainf) $out .= ' <span class="finfo">&nbsp;('.get_lang(151, $ainf['length'], $ainf['index']).')</span>';
	if ($hits > 0) $out .= ' <span class="finfo">&nbsp;('.$hits.' '.get_lang(243).')</span>';
	$out .= '</td></tr>';
	if ($return) return $out; else echo $out;
}


class filedesc
{
	var $fid;

	var $found;
	var $gid;
	var $m3u;
	var $logaccess;
	var $view;
	var $mime;

	function filedesc($fname)
	{
		global $streamtypes;
		$this->fid = file_type($fname);
		if ($this->fid != -1)
		{
			$this->found = true;
			$this->mime = $streamtypes[$this->fid][1];
			$this->m3u = $streamtypes[$this->fid][2];
			$this->gid = $streamtypes[$this->fid][3];
			$this->view = $streamtypes[$this->fid][4];
			$this->logaccess = $streamtypes[$this->fid][5];
		} else
		{
			$this->found = false;
			$this->mime = 0;
			$this->gid = 0;
			$this->m3u = 0;
			$this->view = 0;
			$this->logaccess = 0;
		}		
	}
}

function file_type($name, $sindex= 0)
{
	global $streamtypes;
	$l = strlen($name);
	for ($i=0,$c=count($streamtypes);$i<$c;$i++)
	{
		if ($l >= strlen($streamtypes[$i][0]) )
		{
			$match = substr($name, strlen($name)-strlen($streamtypes[$i][0]));
			if (preg_match('/'.$streamtypes[$i][0].'/i', $match)) 
			{
				if ($sindex) 
				{ 
					if ($streamtypes[$i][4]) return $i; 
				} else return $i;
			}
		}
	}
	return -1;
}

function file_extension($name)
{
	if (strrpos($name, '.') != false)
		return strtolower(substr($name, strrpos($name,'.')));
	return null;
}

function ratetypeid($strtype)
{
	switch($strtype)
	{
		case 'cbr': return 1; 
		case 'abr': return 2;
		case 'vbr': return 3;
		default: return 0;
	}
}

function get_aheader()
{
	return array('album' => '', 'artist' => '', 'lengths' => 0, 'index' => 0, 'length' => '00:00');
}

function gen_aheader($album, $artist, $lengths, $index)
{
	$ret = get_aheader();
	$ret['album'] = $album;
	$ret['artist'] = $artist;
	$ret['lengths'] = $lengths;
	$ret['index'] = $index;
	if ($lengths > 0) $ret['length'] = sprintf('%02d:%02d',floor($lengths/60), $lengths % 60);
	return $ret;
}

function gen_file_header()
{
	return array('title' => "",'artist' => "",'album' => "",'length' => "00:00",'bitrate' => 0,'lengths' => 0, 'genre' => 255, 'tag' => false, 'ratemode' => 1, 'tagid' => 0);
}

function gen_file_info($title = "", $artist = "", $album = "", $bitrate = 0, $lengths = 0, $genre = 255, $ratemode = 1, $tagid = 0, $drive = 0)
{
	$ret = gen_file_header();
	$ret['title'] = $title;
	$ret['artist'] = $artist;
	$ret['album'] = $album;
	$ret['bitrate'] = $bitrate;
	$ret['lengths'] = $lengths;
	$ret['genre'] = $genre;
	$ret['ratemode'] = $ratemode;
	$ret['tagid'] = $tagid;
	if ($lengths > 0) $ret['length'] = sprintf('%02d:%02d',floor($lengths/60), $lengths % 60);
	return $ret;
}

function gen_file_info_sid($row)
{
	if ($row) return gen_file_info($row['title'], $row['artist'], $row['album'], $row['bitrate'], $row['lengths'], $row['genre'], $row['ratemode'], $row['tagid']);
	return false;
}

function get_searchrow($sid)
{
	return @mysql_fetch_array(db_execquery('SELECT * FROM '.TBL_SEARCH.' WHERE id = '.$sid, true));
}

function get_file_info($name)
{
	global $streamtypes, $cfg;
	$ret = gen_file_header();
	if ($cfg['enablegetid3'])
	{
		if (GETID3_V == 16)
		{
			$finfo = GetAllFileInfo($name, file_extension($name));
			if ($finfo)
			{
				$ret['length'] = isset($finfo['playtime_string']) ? $finfo['playtime_string'] : "00:00";
				$ret['lengths'] = isset($finfo['playtime_seconds']) ? round($finfo['playtime_seconds']) : 0;
				$ret['bitrate'] = isset($finfo['bitrate']) ? round($finfo['bitrate']) / 1000 : 0;			
				$ret['ratemode'] = isset($finfo['audio']['bitrate_mode']) ? ratetypeid($finfo['audio']['bitrate_mode']) : 0;

				if (isset($finfo['id3v1']) && is_array($finfo['id3v1'])) 
				{
					$ret['tag'] = 'id3v1';
					$ret['tagid'] = 2;
				}
				else
				if (isset($finfo['id3v2']) && is_array($finfo['id3v2'])) 
				{
					$ret['tag'] = 'id3v2';
					$ret['tagid'] = 1;

					$ret['title'] =		@$finfo['comments']['title'][0];
					$ret['artist'] =	@$finfo['comments']['artist'][0];
					$ret['album'] =		@$finfo['comments']['album'][0];
					$ret['genre'] =	@is_numeric(@$finfo['comments']['genreid'][0]) ? @$finfo['comments']['genreid'][0] : 255;	
					
					return $ret;
				} 
				else
				if (isset($finfo['comments']) && is_array($finfo['comments'])) 
				{
					
					$ret['tag'] = 'comments';
					$ret['tagid'] = 0; // unknown
					
					$ret['title'] =		@$finfo[$ret['tag']]['title'][0];
					$ret['artist'] =	@$finfo[$ret['tag']]['artist'][0];
					$ret['album'] =		@$finfo[$ret['tag']]['album'][0];
					$ret['genre'] =	@is_numeric(@$finfo[$ret['tag']]['genreid'][0]) ? $finfo[$ret['tag']]['genreid'][0] : 255;	
					
					return $ret;
				}
				
				if (!empty($ret['tag']))
				{
					$ret['title'] =		@$finfo[$ret['tag']]['title'];
					$ret['artist'] =	@$finfo[$ret['tag']]['artist'];
					$ret['album'] =		@$finfo[$ret['tag']]['album'];
					$ret['genre'] =		@$finfo[$ret['tag']]['genreid'];			
				} 
			} 
		} else
		if (GETID3_V == 17)
		{
			$getID3 = new getID3();
			$finfo = $getID3->analyze($name);
			getid3_lib::CopyTagsToComments($finfo);

			$ret['title'] =		@$finfo['comments_html']['title'];
			$ret['artist'] =	@$finfo['comments_html']['artist'];
			$ret['album'] =		@$finfo['comments_html']['album'];
					
			$ret['length'] = isset($finfo['playtime_string']) ? $finfo['playtime_string'] : "00:00";
			$ret['lengths'] = isset($finfo['playtime_seconds']) ? round($finfo['playtime_seconds']) : 0;		
		}
		return $ret;
	}

	$ftype = file_type($name);
	
	if ($ftype != -1)
	{
		$getidf = @$streamtypes[$ftype][3] or $getidf = 0;
		switch($getidf)
		{
			case 1: 
				if (class_exists('id3'))
				{
					$id3 = new id3($name);
					$ret['title'] = trim($id3->name);
					$ret['artist'] = trim($id3->artists);
					$ret['album'] = trim($id3->album);
					$ret['length'] = $id3->length;
					if ($id3->bitrate) $ret['bitrate'] = $id3->bitrate;
					if ($id3->lengths > 0) $ret['lengths'] = $id3->lengths;
					$ret['genre'] = $id3->genreno;
					if ($id3->id3v1) 
					{	
						$ret['tag'] = 'id3v1';
						$ret['tagid'] = 1;
					}
				}
			break;

			case 2:
				if (class_exists('ogg'))
				{
					$ogg = new ogg($name);
					foreach ($ogg->fields as $name => $val) 
					{
						$ch = strtolower($name);
						
						if (isset($ret[$ch]))
						{
							$ind = '';
							foreach ($val AS $contents) $ind .= $contents; 
							
							switch($ch)
							{
								case 'genre':
										if (is_numeric($ind)) $ret[$ch] = $ind;
										break;
								case 'lengths':
										if (is_numeric($ind)) $ret[$ch] = $ind;
										break;
								default:
										$ret[$ch] = $ind;
										break;
							}							
						}
					}					
				}
				break;

			default: break;
		}
	}
	return $ret;
}

function ismarked($info)
{
	global $mark;
	if (count($mark) == 0) return false;
	$hits = 0;
	for ($i=0,$c=count($mark);$i<$c;$i++) 
	if (strpos(strtoupper($info), $mark[$i]) !== false) $hits++; else return false;
	if ($hits == $c) return true;
}

function file_parse($f2, $link, $class)
{
	global $setctl;
	$str = '<a href="%i"[ title="%t - %l"]><span class="%c">%f</span></a>[ <span class="finfo">(%b kbit %m mins)</span>]'; 	
	$str2 = '';
	$spos = -1;
	$epos = -1;
	$fullfilled = true;
	$conditional = false;
	for ($i=0,$l=strlen($str);$i<$l;$i++)
	{
		switch ($str[$i])
		{
			case '[': 
					$conditional = true; 
					$spos = strlen($str2); 
					break;
			case ']': 
				$epos = strlen($str2);	
				if (!$fullfilled) 
				{
					$strbak = $str2;
					$str2 = substr($strbak, 0, $spos);
					$str2 .= substr($strbak, $epos);
				}
				$fullfilled = true;
				$conditional = false;  
				$spos = -1;
				$epos = -1;
				break;
			case '%':
				if ($i + 1 >= $l) 
				{
					$str2 .= $str[$i]; 
					break; 
				}
				$add = '';
				$match = true;
				switch ($str[$i+1])
				{
					case 'f': $add = $f2->fname; break;
					case 'a': $add = $f2->id3['artist']; break;
					case 'l': $add = $f2->id3['album']; break;
					case 't': $add = $f2->id3['title']; break;
					case 'b': $add = $f2->id3['bitrate']; break;
					case 'm': //$add = $f2->id3['lengthmin']; break;
					case 's': $add = $f2->id3['length']; break;
					case 'h': $add = $f2->hits; break;
					case 'i': $add = $link; break;
					case 'c': $add = $class; break;
					default: $match = false; break;
				}
				if ($match) 
				{
					$i++;
					$str2 .= $add; 
				} else $str2 .= '%';
				if ($conditional && empty($add)) $fullfilled = false;
				break;
			default: $str2 .= $str[$i]; break;
		}
	}
	return $str2;
}

function print_file($sid, $showlink=0, $includeabsolute=0, $f2=false, $smarksid = -1)
{
	global $PHP_SELF, $u_cookieid, $setctl, $cfg, $marksid;

	if (!$f2) $f2 = new file2($sid, true);
	$inf = $f2->getid3();
	
	if (!empty($inf['title'])) $title = rtrim($inf['title']).' - '.rtrim($inf['album']); else $title='';
	
	$extra = '';
	$extravalue = '';

	if ($showlink) $extra = '<a href="'.$PHP_SELF.'?p='.$f2->getdir64().'&amp;d='.$f2->drive.'&amp;marksid='.$smarksid.'" title="'.get_lang(116, checkchs($f2->relativepath)).'">'.
	'<img src="'.getimagelink('link.gif').'" alt="'.get_lang(116, checkchs($f2->relativepath)).'" border="0"/></a>&nbsp;';
	if ($includeabsolute) $extravalue = ';'.$f2->getdir64();

	echo '<tr><td><input type="checkbox" name="selected[]" value="'.$sid.'"/> ';

	if ($cfg['id3editor'] && db_guinfo('u_access') == 0)
	{
		$id3link = '&amp;id3sid='.$sid;
		echo '<a href="javascript: void(0);" onclick="javascript: openwin(\'id3editor\', \''.$PHP_SELF.'?action=id3edit'.$id3link.'\');">id3</a>&nbsp;';
	}

	if (ALLOWDOWNLOAD && db_guinfo('u_allowdownload')) 
	echo '<span class="file"><a href="'. $PHP_SELF. "?downloadfile=".$sid.'&amp;c='.$u_cookieid.'">'.
	'<img src="'.getimagelink('saveicon.gif').'" alt="'.get_lang(117).'" border="0"/></a></span>&nbsp;';

	if (MAILMP3 && db_guinfo('allowemail') && class_exists('mailmp3'))
	echo '<a href="javascript: void(0);" onclick="javascript: newwin(\'mp3mail\', \''.$PHP_SELF.'?action=sendmail&amp;id='.$sid.'&amp;c='.$u_cookieid.'\', 195, 390);">'.
	'<img src="'.getimagelink('sendmail.gif').'" alt="'.get_lang(223).'" border="0"/></a>&nbsp;';

	echo $extra.'<a href="'.$PHP_SELF.'?sid='.$sid.'&amp;c='.$u_cookieid.'"';
	if (!empty($title)) echo ' title="'. $title. '"';
	if (ismarked($f2->fname.$title) || $f2->sid == $marksid) $useclass = 'filemarked'; else $useclass = 'file';
	echo '>'.'<span class="'.$useclass.'">'.checkchs($f2->fname).'</span></a><span class="finfo">&nbsp;&nbsp;';

	if (!empty($inf['bitrate']) && !empty($inf['length'])) echo '('.get_lang(152, $inf['bitrate'], $inf['length']).') ';
	if ($f2->fsize > 1048576) echo get_lang(272, number_format($f2->fsize / 1048576,1)); else
	if ($f2->fsize > 8192) echo get_lang(273, number_format($f2->fsize / 1024,1)); else
							echo get_lang(274, $f2->fsize);
	echo '</span></td></tr>';

	// new parsed (later) echo $extra.file_parse($f2, $link, $useclass);
}

function listfiles($where, &$in, $drive)
{
	if ($d = @opendir($where)) 
	{
		while ($file = readdir($d)) if (is_file($where.$file) && file_type($file) != -1) $in[] = array($file, $drive, filesize($where.$file),filemtime($where.$file)); 
		closedir($d);
	}
}

function disksync($dir, $drive, $root = false, $stop = false)
{
	global $base_dir;
	$flist = array();
	$dblist = array();
	$found = 0;
	if (!$root) 
		$dbres = db_execquery('SELECT free,fsize,mtime FROM '.TBL_SEARCH.' WHERE f_stat = 0 AND dirname = "'.$dir.'" AND drive = '.$drive.' ORDER BY free ASC');
			else
		$dbres = db_execquery('SELECT free,fsize,mtime FROM '.TBL_SEARCH.' WHERE f_stat = 0 AND dirname = "" ORDER BY free ASC');

	while ($row = mysql_fetch_row($dbres)) $dblist[] = array($row[0], $row[1], $row[2]);
	
	if (!$root) listfiles($base_dir[$drive].$dir, $flist, $drive); else
	for ($i=0;$i<count($base_dir);$i++) listfiles($base_dir[$i], $flist, $i);
	
	$c2=count($flist);
	$c=count($dblist);

	if ($c2 != $c)
	{
		if (!$root) $sql = 'UPDATE '.TBL_SEARCH.' SET f_stat = 1 WHERE dirname = "'.$dir.'" AND drive = '.$drive;
		else $sql = 'UPDATE '.TBL_SEARCH.' SET f_stat = 1 WHERE dirname = ""';
		db_execquery($sql);
		for ($i=0;$i<$c2;$i++) updatesingle($base_dir[$flist[$i][1]].$dir.$flist[$i][0]);
	} else
	{
		$changes = false;
		for ($i=0;$i<$c;$i++) for ($i2=0;$i2<$c2;$i2++) if ($dblist[$i][0] == $flist[$i2][0] && $dblist[$i][1] == $flist[$i2][2] && $dblist[$i][2] == $flist[$i2][3]) $flist[$i2][0] = '';
		for ($i2=0;$i2<$c2;$i2++) if (!empty($flist[$i2][0])) 
		{
			$changes = true;
			updatesingle($base_dir[$flist[$i2][1]].$dir.$flist[$i2][0]);
		}
		if ($changes && !$stop) disksync($dir, $drive, $root, true);
	}
}


function file_id3editor_save($file, $_POST)
{
	$inf = new id3($file, true);
	if ($inf)
	{
		$genrelist = $inf->genres();		
		if (isset($_POST['name'])) $inf->name = stripcslashes($_POST['name']);
		if (isset($_POST['artists'])) $inf->artists = stripcslashes($_POST['artists']);
		if (isset($_POST['album'])) $inf->album = stripcslashes($_POST['album']);
		if (isset($_POST['comment'])) $inf->comment = stripcslashes($_POST['comment']);
		if (isset($_POST['year'])) $inf->year = $_POST['year'];
		if (isset($_POST['track'])) $inf->track = $_POST['track'];
		if (isset($_POST['genreno'])) 
		{
			$inf->genreno = $_POST['genreno'];
			$inf->genre = $genrelist[$_POST['genreno']];
		}
		$inf->write();
		updatesingle($file);
	}
}

function file_id3editor($file)
{
	global $userauth, $PHP_SELF;
	$inf = new id3($file, true);
	if ($inf)
	{
		$genrelist = $inf->genres();
		
		kprintheader("id3editor (beta)", 1);
		?>
			<form name="settings" method="post" action="<?php echo $PHP_SELF; ?>">
			<input type="hidden" name="action" value="id3save"/>
			<input type="hidden" name="file" value="<?php echo base64_encode($file); ?>"/>
			<table width="100%" border="0">
			<tr>
				<td class="wtext"><?php echo get_lang(141); ?></td>
				<td class="wtext"><input type="text" name="name" class="fatbuttom" size="50" value="<?php echo trim($inf->name); ?>"/></td>				
			</tr>
			<tr>
				<td class="wtext"><?php echo get_lang(142); ?></td>
				<td class="wtext"><input type="text" name="artists" class="fatbuttom" size="50" value="<?php echo trim($inf->artists); ?>"/></td>				
			</tr>
			<tr>
				<td class="wtext"><?php echo get_lang(143); ?></td>
				<td class="wtext"><input type="text" name="album" class="fatbuttom" size="50" value="<?php echo trim($inf->album); ?>"/></td>				
			</tr>
			<tr>
				<td class="wtext"><?php echo get_lang(144); ?></td>
				<td class="wtext"><input type="text" name="comment" class="fatbuttom" size="50" value="<?php echo trim($inf->comment); ?>"/></td>				
			</tr>
			<tr>
				<td class="wtext"><?php echo get_lang(145); ?></td>
				<td class="wtext"><input type="text" name="year" class="fatbuttom" size="50" value="<?php echo trim($inf->year); ?>"/></td>				
			</tr>
			<tr>
				<td class="wtext"><?php echo get_lang(146); ?></td>
				<td class="wtext"><input type="text" name="track" class="fatbuttom" size="50" value="<?php echo trim($inf->track); ?>"/></td>				
			</tr>
			<tr>
				<td class="wtext"><?php echo get_lang(147); ?></td>
				<td class="wtext">
				<select name="genreno" class="fatbuttom">
				<?php foreach ($genrelist as $no => $name) 
				{ 
					if ($no == $inf->genreno) echo '<option value="'.$no.'" selected="selected">'.checkchs($name).'</option>';  else
							echo '<option value="'.$no.'">'.checkchs($name).'</option>';
				}
				?></select><?php if ($inf->genreno == 255) echo '&nbsp;('.get_lang(148).')'; ?>
				</td>
			</tr>
			<tr>
			<td colspan="2">
				<input type="submit" class="fatbuttom" name="save" value="<?php echo get_lang(45); ?>"/>&nbsp;
				<input type="button" class="fatbuttom" name="close" value="<?php echo get_lang(27); ?>" onclick="javascript: window.close();"/>
			</td>
			</tr>
			</table>
			</form>
		<?php
		kprintend();
	}
}

function checkstructure($where, $b64=true)
{
	if ($b64) $checkdir=stripslashes(base64_decode($where)); else $checkdir = $where;
	$srcstr1 = '../';	
	if (strlen($checkdir) > 0)
	{
		if ($checkdir[0] == '/') return 1;
		$i = strpos ( $checkdir, $srcstr1);
		if ($i !== false) return 1;
	}
	return 0;
}

$lastwhere = false;

function readresources($where)
{
	global $dir_list, $lastwhere, $cfg;
	$c = 0;
	
	if ($lastwhere == $where) return; 
	$lastwhere = $where;

	$dir_list = array();	

	if ($dir = @opendir($where)) 
	{
		while ($file = readdir($dir)) if (!isset($cfg['dirignorelist'][$file])) if (is_dir($where.$file)) $dir_list[$c++] = $file;
		closedir($dir);
	}
	usort($dir_list, 'strcasecmp');
}

function firsttime()
{
	?>
	<tr>
		<td>
			<table width="80%" bgcolor="#CCCCCC" align="center" cellpadding="2" cellspacing="5" border="0">
			<tr>
				<td class="fatfield">Welcome to kPlaylist!</td>
			</tr>			
			<tr>
				<td class="importnant">To get your site quickly up:</td>
			</tr>
			<tr>
				<td class="importnant">Click Settings to your right, choose 'File handling' on the top and enter the paths to your music directory or directories in the 'base directory' field. Click
				on the ? for help.</td>
			</tr>
			<tr>
				<td class="importnant">If you have problems, click <a class="importnant" href="http://kplaylist.net/index.php?showinstall=1" target="_blank">-here-</a> for the kPlaylist installation manual.</td>
			</tr>
			<tr>
				<td class="importnant">Good luck!</td>
			</tr>
			</table>
		</td>
	</tr>
	<?php
}

function listroot()
{
	global $base_dir, $dir_list, $cfg;
	mp3table();
	echo '</td></tr>';

	if (count($base_dir) == 1 && $base_dir[0] == '/path/to/my/music/archive/' && db_guinfo('u_access') == 0) firsttime();

	$dcnt = 0;
	$fcnt = 0;
	$sortlist = array();
	$drivelist = array();
	$nrlist = array();
	$sortcnt = 0;
	for ($i=0;$i<count($base_dir);$i++)
	{
		readresources($base_dir[$i]);		
		$dcnt += count($dir_list);
		for ($i2=0,$c=count($dir_list);$i2<$c;$i2++)
		{
			$sortlist[$sortcnt] = $dir_list[$i2];		
			$drivelist[$sortcnt] = $i;
			$nrlist[$sortcnt] = $i2;
			$sortcnt++;
		}
	}
	if ($cfg['sortroot']) array_multisort($sortlist, $drivelist, $nrlist, SORT_STRING);	
	for ($i=0;$i<$sortcnt;$i++) echo print_dir($drivelist[$i],$sortlist[$i], '', $nrlist[$i],1);
	
	if (DISKSYNC) disksync('', 0, true);

	$res = fsearch('/', true,0,'id, free');
	$fcnt = 0;
	while ($row = mysql_fetch_row($res)) 
	{
		$fdesc = new filedesc($row[1]);
		if ($fdesc->view) 
		{
			$fcnt++;
			print_file($row[0],0,1);
		}
	}
	
	endmp3table(0, $dcnt, $fcnt);
	infobox();
	?>
	</tr>
	</table>
	<?php	
}

function fsearch($dir, $root = false, $drive = 0, $r='id')
{
	if (!$root) $sql = 'SELECT '.$r.' FROM '.TBL_SEARCH.' WHERE f_stat = 0 AND dirname = "'.$dir.'" AND drive = '.$drive.' ORDER BY free ASC';	
			else
				$sql = 'SELECT '.$r.' FROM '.TBL_SEARCH.' WHERE f_stat = 0 AND dirname = "" ORDER BY free ASC';
	return db_execquery($sql);
}

function fmatch($file, $pattern)
{
	$match = '';
	$fpos = 0;
	for ($i=0,$c=strlen($pattern);$i<=$c;$i++)
	{
		if ($i == $c || $pattern[$i] == '*')
		{
			if (!empty($match))
			{
				$p = strpos($file, $match, $fpos);
				if ($p !== false) $fpos = $p + strlen($match);
					else return false;
			}
			$match = '';
		} else $match .= $pattern[$i];
	}
	return true;
}

function albumshow($rows)
{
	global $setctl;
	$names = array();
	for ($i=0,$c=count($rows);$i<$c;$i++)
	{
		$f2 = new file2($rows[$i], false);
		$names[] = array($f2->fname, $f2->weblink(), $f2->fullpath);
	}

	$albumfiles = explode(',', strtoupper($setctl->get('albumfiles')));
	
	for ($i=0,$c=count($albumfiles);$i<$c;$i++)
	{		
		$amatch = trim($albumfiles[$i]);
		if (empty($amatch)) continue;
		for ($i2=0,$c2=count($names);$i2<$c2;$i2++)
		{
			if (fmatch(strtoupper($names[$i2][0]), $amatch))
			{
				?>
				<tr>
					<td>
					<?php
					if ($setctl->get('albumresize')) 
					{ 
						if ($imagesize = @getimagesize($names[$i2][2]))
						{
							$w = $imagesize[0]; 
							$h = $imagesize[1]; 
							$wm = $setctl->get('albumwidth'); 
							$hm = $setctl->get('albumheight'); 
							$nw = min ($wm, $w); 
							$nh = min ($hm, $h); 
							$p = ($wm > $hm) ? $w / $h : $h / $w; 
							if ($p > 1) 
							$nh = round($h * $nw / $w); 
							else 
							$nw = round($w * $nh / $h); 
							if ($nw > $wm || $nh > $hm) 
							{ 
								$nw = min ($wm, $nw); 
								$nh = min ($hm, $nh); 
								$p = ($wm > $hm) ? $h / $w : $w / $h; 
								if ($p > 1) 
								$nh = round($h * $nw / $w); 
								elseif ($p < 1) 
								$nw = round($w * $nh / $h); 
							}
						} else
						{
							$nw = $setctl->get('albumwidth');
							$nh = $setctl->get('albumheight');
						}
						echo '<img src="'.$names[$i2][1].'" alt="album" width="'.$nw.'" height="'.$nh.'"/>'; 
					}
					else echo '<img alt="album" src="'.$names[$i2][1].'"/>';
					?>
					</td>
				</tr>
				<?php
				return true;
			}
		}
	}
	return false;
}

function retornaEndImgCapa($artist, $album)
{
	global $setctl;
	$url = str_replace('%artist', urlencode($artist), $setctl->get('albumurl'));
	$strEndereco = str_replace('%album', urlencode($album), $url);
	$handle = fopen($strEndereco, 'r');	
	if (!empty($handle))
	{
		$contents = '';
		while (!feof($handle)) {
		  $contents .= fread($handle, 8192);
		}
		fclose($handle);
				
		if (is_numeric(strpos($contents,'coverart')))
		{
			$contents = substr($contents,strpos($contents,'coverart'),strlen($contents));
			$contents = substr($contents,strpos($contents,'src="')+5,strlen($contents));			
			$contents = substr($contents, 0, strpos($contents,'"'));			
		}
		else $contents = '';
		
		if (!is_numeric(strpos($contents,'.jpg')) & !is_numeric(strpos($contents,'.gif'))) return false;
			else 
		return $contents;
	}
	return false;
}

function get_dir($dirlist, $cnt, $n2)
{
	if (isset($dirlist[$cnt]) && md5($dirlist[$cnt]) == $n2) return $dirlist[$cnt];
	for ($i=0,$c=count($dirlist);$i<$c;$i++)
		if (md5($dirlist[$i]) == $n2) return $dirlist[$i];
	if (isset($dirlist[$cnt])) return $dirlist[$cnt];
	return '';
}

function read_dir($pdir, $count=-1, $drive=0, $n2='')
{
	global $base_dir, $dir_list, $setctl, $cfg;

	if (!empty($pdir)) $pdir=base64_decode($pdir);

	$pdir = stripslashes($pdir);

	readresources($base_dir[$drive].$pdir);

	if (is_numeric($count) && ($count != -1))
	{
			$pdir .= get_dir($dir_list, $count, $n2);
			if (!empty($pdir)) 
			{ 
				if ($pdir[strlen($pdir)-1] != '/') $pdir .= '/'; 
			} else $pdir ="";
			readresources($base_dir[$drive].$pdir);
	}

	
	mp3table($pdir);
	showdir($pdir,'',$drive);
	echo '</td></tr>';
	
	if (DISKSYNC) disksync($pdir, $drive);

	$res = fsearch($pdir, false, $drive,'id, free');

	$dcnt = count($dir_list);

	$rows = $viewrows = array();
	while ($row = mysql_fetch_row($res)) 
	{
		$rows[] = $row[0];
		$fdesc = new filedesc($row[1]);
		if ($fdesc->view) $viewrows[] = $row[0];
	}

	$fcnt = count($viewrows);

	if (ALBUMCOVER && $dcnt <= $cfg['isalbumdircount'] && $fcnt > 0) 
	{
		$result = albumshow($rows);
	
		if (!$result && FETCHALBUM)
		{
			$artist = '';
			$album = '';
			for ($i=0,$c=count($rows);$i<$c;$i++)
			{
				$sql = 'SELECT artist, album FROM '.TBL_SEARCH.' WHERE id = '.$rows[$i];
				$res = db_execquery($sql);
				if ($res !== false)
				{
					$id = mysql_fetch_row($res);
					$artist = $id[0];
					$album = $id[1];
					if (!empty($artist) && !empty($album)) break;
				}
			}
			if (!empty($artist) && !empty($album)) 
			{				
				$img = retornaEndImgCapa($artist, $album);
				if (!empty($img))
				{
					?>
					<tr><td>
					<?php
					if ($setctl->get('albumresize'))  echo '<img src="'.$img.'" alt="album" width="'.$setctl->get('albumwidth').'" height="'.$setctl->get('albumheight').'"/>'; 
						else 
							echo '<img alt="album" src="'.$img.'"/>';
					?>
					</td></tr>
					<?php
				}
			}

		}
	}

	if ($fcnt == 0 && $dcnt == 0) echo '<tr><td class="file">'.get_lang(156).'</td></tr>'; 
	else
	{
		for ($i=0;$i<$dcnt;$i++) echo print_dir($drive,$dir_list[$i], $pdir, $i,1);
		for ($i=0,$c=count($viewrows);$i<$c;$i++) print_file($viewrows[$i],0,1);
	}
	
	endmp3table(1, $dcnt, $fcnt);
	infobox();
	?>
	</tr>
	</table>
	<?php
}

function dir_divide($path, $drive)
{
	global $PHP_SELF;
	$out = null;
	$dir = null;
	$dirs = explode('/', $path);
	for ($i=0,$c=count($dirs);$i<$c;$i++)
	{
		if (!empty($dirs[$i]))
		{
			$dir .= $dirs[$i].'/';
			if ($i == 0) $show = '/'; else $show = null; 
			$out .= '<a href="'.$PHP_SELF.'?p='.base64_encode($dir).'&amp;d='.$drive.'">'.$show.$dirs[$i].'/'.'</a>';
		}
	}
	return $out;	
}

function showdir($pdir, $text="", $drive)
{
	global $PHP_SELF;

	$show= null;
	$root = '<a href="'.$PHP_SELF.'"><img src="'.getimagelink('root.gif').'" title="'.get_lang(119).'" alt="'.get_lang(119).'" border="0"/></a>&nbsp;';
	
	$dirname = null;
	$dirs = explode('/', $pdir);
	$selection = count($dirs) - 1;
	if (empty($dirs[count($dirs)-1])) $selection--;
	for ($i=0;$i<$selection;$i++) $dirname .= $dirs[$i].'/';
	
	if (empty($text))
	{
		$show = $root . '<a title="'.get_lang(118).'" href="'.$PHP_SELF.'?p='.base64_encode($dirname).'&amp;d='.$drive.'"><img src="'.getimagelink('cdback.gif').'" alt="'.get_lang(118).'" border="0"/></a>&nbsp;&nbsp;&nbsp;' . dir_divide($pdir,$drive);
	} else $show = $root.$text;
	echo '<font class="curdir">'.$show.'&nbsp;</font><hr width="80%" align="left" size="1"/>'; 
}

function kplaylist_filelist($where, $n=-1, $drive=0, $n2='')
{
	global $mark, $marksid;
	if (checkstructure($where) == 0)
	{
		if (isset($_GET['mark']) && !empty($_GET['mark'])) $mark = explode(' ', strtoupper(trim($_GET['mark']))); else $mark = array();
		if (isset($_GET['marksid'])) $marksid = $_GET['marksid'];
		kprintheader("kPlaylist", 1);
		if ( (!isset($n) || $n == -1) && empty($where)) listroot(); else read_dir($where, $n, $drive, $n2);  
		kprintend(); 
	} 
}


class file2
{
	var $sid = -1;
	var $fexists = false;
	var $fullpath = '';
	var $fsize = 0;
	var $fname = '';
	var $drive;
	var $origrow = false;
	var $investigated = false;
	var $id3 = false;
	var $relativepath = '';
	var $dir64 = '';
	
	function investigate()
	{
		global $base_dir;
		$this->origrow = get_searchrow($this->sid);
		if ($this->origrow !== false)
		{
			$this->fexists = false;
			$this->drive = $this->origrow['drive'];
			$this->investigated = true;
			$this->relativepath = $this->origrow['dirname']; 
			$this->fsize = $this->origrow['fsize'];

			if (isset($base_dir[$this->drive]))
			{
				$this->fname = basename($this->origrow['free']);				
				$this->fullpath = $base_dir[$this->drive].$this->relativepath.$this->fname;			
				if (OPTIMISTICFILE) $this->fexists = true; 
					else 
				if (@file_exists($this->fullpath)) $this->fexists = true;
			}
		} else return false;
	}

	function ifexists()
	{
		return $this->fexists;
	}

	function getfullpath($win32)
	{
		if ($win32) return str_replace('/', '\\', $this->fullpath); else return $this->fullpath;
	}

	function getdrivedir()
	{
		global $base_dir;
		return $base_dir[$this->drive];
	}
	
	function getdir64()
	{
		if (empty($this->dir64)) $this->dir64 = base64_encode($this->relativepath);
		return $this->dir64;
	}

	function weblink()
	{
		global $PHP_SELF, $u_cookieid;
		return $PHP_SELF.'?sid='.$this->sid.'&amp;c='.$u_cookieid;
	}

	// don't get to hung up about the name. It's not really a id3 tag, it's anything (ogg, id3v1, id3v2, etc.)
	function getid3()
	{
		if ($this->investigated)
		{
			if ($this->id3 == false) 
				$this->id3 = gen_file_info_sid($this->origrow);
		}
		return $this->id3;
	}

	function getlengths()
	{
		$this->getid3();
		return $this->id3['lengths'];
	}

	function file2($sid = -1, $id3 = false)
	{
		$this->sid = $sid;
		if ($sid != -1)
		{
			$this->investigate();
			if ($id3) $this->getid3();
		}
	}
}


function KCheckActions()
{ 
	global $_POST, $_GET, $phpenv, $u_cookieid, $u_id, $PHP_SELF, $setctl, $validated_user;

	if (isset($_GET['c'])) $stat = db_verify_stream($_GET['c'], $phpenv['remote']); else $stat = 0;
	
	if ($setctl->get('unauthorizedstreams') && $stat == 0)
	{
		$validated_user['extm3u'] = $setctl->get('unauthorizedstreamsextm3u');
		$validated_user['plinline'] = 1;
		if (isset($_GET['streamsid'])) Kplay_senduser2($_GET['streamsid'], 0);
		if (isset($_GET['sid']))
		{
			Kplay_resource2($_GET['sid'], 0);
			die();
		} else
		if (isset($_GET['streamplaylist'])) 
		{
			if (isset($_GET['extm3u'])) $validated_user['extm3u'] = 1;
			$kp = new kp_playlist($_GET['streamplaylist']);
			$kp->play();
		}
	}

	if ($stat == 0) die();

	if (isset($_GET['c'])) $u_cookieid = $_GET['c'];

	if (isset($_GET['downloadfile'])) Kplay_download($_GET['downloadfile']); else
	if (isset($_GET['sid'])) Kplay_resource2($_GET['sid'], 0); else	
	if (isset($_GET['streamsid'])) Kplay_senduser2($_GET['streamsid'], 0);
	die();
}	

if (isset($_GET['downloadfile']) || isset($_GET['sid']) || isset($_GET['streamsid']) || isset($_GET['streamplaylist'])) KCheckActions();

if (isset($_GET['update']) && isset($_GET['user'])) search_updateautomatic($_GET['user'],$phpenv['remote'],$_GET['update']);

if (isset($_POST['signup']) || isset($_GET['signup']) && USERSIGNUP) KSignup();

if (isset($_POST['user']) && isset($_POST['password']) && !empty($_POST['user']) && !empty($_POST['password']))
{
	webprocess(); 
	if ($userauth) 
	{
		refreshurl($PHP_SELF.'?checkcookie=true');
		die();
	} else
	{
		if ($setctl->get('report_attempts')) syslog_write('User could not be validated (user: "'.$_POST['user'].'")');
		klogon();
	}
} 

if (isset($_COOKIE[$cfg['cookie']]) || $cfg['disablelogin'])
{	
	if (db_verify_stream(@$_COOKIE[$cfg['cookie']], $phpenv['remote']))
	{
		if (REQUIRE_HTTPS && !$phpenv['https']) klogon();

		if (isset($_COOKIE[$cfg['cookie']])) $u_cookieid = $_COOKIE[$cfg['cookie']]; else $u_cookieid = null;
		
		$deflanguage = db_guinfo('lang');

		if (isset($_POST['sel_playlist'])) user_saveoption('defplaylist', vernum($_POST['sel_playlist']));

		if (isset($_POST['sel_shplaylist'])) 
		{
			user_saveoption('defshplaylist', vernum($_POST['sel_shplaylist']));
			$_POST['sel_playlist'] = $_POST['sel_shplaylist'];
		}

		if (isset($_GET['action']) || isset($_POST['action']))
		{
			if (isset($_GET['action'])) $action = $_GET['action']; else $action = $_POST['action'];		
			$match = true;
			
			switch ($action)
			{
				case 'bulletin':
						if (class_exists('kbulletin') && BULLETIN)
						{
							$kb = new kbulletin();
							$kb->showall();
						}
						break;
				
				case 'newbulletin':
						$kb = new kbulletin();
						$kb->editbulletin(0);
						break;
					
				case 'delbulletin':
						if (isset($_GET['bid']) && is_numeric($_GET['bid']))
						{
							$kb = new kbulletin();
							$kb->delbulletin($_GET['bid'], $u_id);
							$kb->showall();
							
						}
						break;

				case 'editbulletin':
						if (isset($_GET['bid'])) 
						{
							$kb = new kbulletin();
							$kb->editbulletin($_GET['bid']);
						}
						break;
				
				case 'savebulletin':
						$kb = new kbulletin();
						if (isset($_POST['publish']) && db_guinfo('u_access') == 0) $publish = 1; else $publish = 0;
						if (isset($_POST['mesg'])) $mesg = $_POST['mesg']; else $mesg = '';
						if (isset($_POST['bid'])) $bid = $_POST['bid']; else $bid = 0;				
						$bid = $kb->savebulletin($bid, $publish, $mesg);
						$kb = new kbulletin();
						$kb->editbulletin($bid, true);
						break;

				case 'sendmail':
						if (class_exists('mailmp3'))
						{
							$mail3 = new mailmp3();
							if (isset($_GET['id'])) $mail3->setsid($_GET['id']);
							$mail3->decide();
						}
						break;

				case 'fupload':
						if (class_exists('fupload'))
						{
							$fu = new fupload();
							$fu->decide();
						}
						break;

				case 'playlist_new':
						playlist_new();
						break;

				case 'playlist_newsave':
						if (!empty($_POST['name'])) 
						{
							if (isset($_POST['shared'])) $shared = 1; else $shared = 0;
							$added = playlist_createnew($_POST['name'],$shared);
							kprintheader(get_lang(61), 1);    				
							echo '<font color="#000000" class="notice">';
							if ($added) echo get_lang(35); else echo get_lang(137);
							echo '</font><br/><br/>';
							echo '<a href="javascript:void(0);" onclick="javascript: window.close(); window.opener.location.reload();"><font color="blue">'.get_lang(27).'</font></a>';
							if ($added) echo '<font class="notice"> - '.get_lang(36).'</font>';
							kprintend();
						} else playlist_new();
						break;

				case 'editoptions':
						show_useroptions();
						break;				
				
				case 'randomizer':
						$rz = new kprandomizer();
						if (isset($_POST['mode'])) $rz->setmode($_POST['mode']);
						if (isset($_POST['limit'])) $rz->setlimit($_POST['limit']);
						if (isset($_POST['genres'])) $rz->setgenre($_POST['genres']);
						if (isset($_POST['playlist'])) $rz->setplaylist($_POST['playlist']);
						if (isset($_POST['order'])) $rz->setorder($_POST['order']);
						if (isset($_POST['usersfilter'])) $rz->setusers($_POST['usersfilter']);
						if (isset($_POST['execute'])) $rz->execute(); else $rz->view();
						break;

				case 'showrandomizer':
						if (class_exists('kprandomizer'))
						{
							$rz = new kprandomizer();
							$rz->view();
						}
						break;
						
				case 'updateoptions':
						if (db_guinfo('u_access') == 0) search_updatelist_options();
						break;

				case 'settingsview':
						if (db_guinfo('u_access') == 0)
						{
							isset($_GET['page']) ? $page = $_GET['page'] : $page = 0;
							isset($_GET['reload']) ? $reload = $_GET['reload'] : $reload = 0;
							settings_edit($reload, $page);
						}
						break;
				
				case 'savesettings':
						if (db_guinfo('u_access') == 0)
						{
							isset($_POST['page']) ? $page = $_POST['page'] : $page = 0;
							settings_save($_POST, $page); 
							settings_edit(1, $page);
						}
						break;
				
				case 'performupdate':
						if (db_guinfo('u_access') == 0) search_updatelist($_POST);
						break;

				case 'id3edit':
						if (db_guinfo('u_access') == 0 && $cfg['id3editor']) 
						{
							$f2 = new file2($_GET['id3sid']);
							if ($f2->ifexists()) file_id3editor($f2->fullpath);
						}
						break;

				case 'id3save':
						if (db_guinfo('u_access') == 0 && $cfg['id3editor'])
						{
							file_id3editor_save(stripcslashes(base64_decode($_POST['file'])), $_POST);
							file_id3editor(stripcslashes(base64_decode($_POST['file'])));
						} 
						break;
				
				case 'showusers':
						if (db_guinfo('u_access') == 0) show_users();			
						break;

				case 'userdel':
						if (db_guinfo('u_access') == 0)
						{
								$id = $_GET['id'];
								if (is_numeric($id)) db_execquery('DELETE FROM '.TBL_USERS.' WHERE u_id = '.$id);
								show_users();
						}
						break;
				
				case 'usersave': 
						if (db_guinfo('u_access') == 0)
						{
							if (isset($_POST['submit']))
							{
								$id = $_POST['u_id'];
								if (isset($_POST['passchange']) && $id != -1) $changepw = 1; else $changepw = 0;
								isset($_POST['booted']) ? $booted = 1 : $booted = 0;
								isset($_POST['lameperm']) ? $lameperm = 1 : $lameperm = 0;
								isset($_POST['download']) ? $download = 1 : $download = 0;
								isset($_POST['allowemail']) ? $allowemail = 1 : $allowemail = 0;							
								
								$name = mysql_escape_string($_POST['name']);
								$login = mysql_escape_string($_POST['login']);
								$pass = mysql_escape_string($_POST['password']);
								$comm = mysql_escape_string($_POST['comment']);
								$access = mysql_escape_string($_POST['access']);
								if (is_numeric($_POST['udlrate'])) $udlrate = $_POST['udlrate']; else $udlrate = 0;

								if (empty($pass) && $changepw) show_userform($id);
								else
								if (empty($name) || empty($login)) show_userform($id,'',$name,$pass,$comm,$login,$access,$download, $udlrate);
								else
								{
									$text = get_lang(262);
									$pass = md5($pass);
									if ($id == -1) $query = 'INSERT into '.TBL_USERS.' set u_name = "'.$name.'", u_login = "'.$login.'", u_pass = "'.$pass.'",  u_comment = "'.$comm.'", u_access = '.$access. ', u_allowdownload = "'.$download.'", lang = "'.$setctl->get('default_language').'", udlrate = '.$udlrate.', allowemail = '.$allowemail.', created = '.time(); 
									else
									{
										if ($changepw) 
										{
											$text = get_lang(157);
											db_execquery('UPDATE '.TBL_USERS.' SET u_pass = "'.$pass.'" WHERE u_id = '.$id);
										}
										$query = "UPDATE ".TBL_USERS." set u_name = \"$name\", u_login = \"$login\", u_booted = $booted, u_comment = \"$comm\", u_access = $access, u_allowdownload = \"$download\", udlrate = $udlrate, lameperm = $lameperm, allowemail = $allowemail where u_id = $id";
									}
									db_execquery($query);
									if ($id != -1) show_userform($id, $text); else show_users();
								}								
							} else show_users();
						}
						break;
				
				case 'useraction':
						if (db_guinfo('u_access') == 0)
						{				
							if (isset($_POST['newuser'])) show_userform();
								else
							if (isset($_POST['refresh'])) show_users();
						}
						break;

				case 'useredit':
						if (db_guinfo('u_access') == 0)
							show_userform(vernum($_GET['id']));
						break;
				
				case 'userlogout':
						if (db_guinfo('u_access') == 0)
						{
							$id = $_GET['id'];
							if (is_numeric($id)) if (!$cfg['demomode']) db_execquery('UPDATE '.TBL_USERS.' SET u_sessionkey = 0, u_status = 0 WHERE u_id = '.$id);
							show_users();
						}
						break;

				case 'userhistory':
						if (db_guinfo('u_access') == 0) show_userhistory(vernum($_GET['id']));
						break;

				case 'historyview':
						if (db_guinfo('u_access') == 0) show_userhistory(vernum($_POST['id']), vernum($_POST['length']));
						break;

				case 'saveuseroptions':
						$state = save_useroptions($_POST);
						switch ($state)
						{
							case 2: show_useroptions(get_lang(157),true); break;
							case 3: show_useroptions(get_lang(165),true); break;
							default: show_useroptions(null,true); break;
						}
						break;

				case 'deletefiletype':
						if (db_guinfo('u_access') == 0)
						{
							db_execquery('DELETE from '.TBL_FILETYPES.' WHERE id = '.vernum($_GET['del']));
							settings_edit(1, 3);
						}
						break;

				case 'editfiletype':
						if (db_guinfo('u_access') == 0) edit_filetype(vernum($_GET['id']));
						break;		

				case 'storefiletype':
						if (db_guinfo('u_access') == 0)
						{
							if (isset($_POST['extension'])) $extension = $_POST['extension']; else $extension = '';
							if (isset($_POST['m3u'])) $m3u = 1; else $m3u = 0;
							if (isset($_POST['search'])) $search = 1; else $search = 0;
							if (isset($_POST['logaccess'])) $logaccess = 1; else $logaccess = 0;
							$id = store_filetype(vernum($_POST['id']), $m3u, $search, $logaccess, mysql_escape_string($_POST['mime']), $extension);
							edit_filetype(vernum($id), true);
						}
						break;

				case 'search':
						if (isset($_POST['onlyid3'])) user_saveoption('defaultid3', 1); else user_saveoption('defaultid3', 0);
						if (isset($_POST['hitsas'])) user_saveoption('hitsas', verchar($_POST['hitsas']));
						if (isset($_POST['search'])) user_saveoption('defaultsearch', vernum($_POST['search'])); 
						
						if (isset($_POST['searchfor']) && !empty($_POST['searchfor']))
						{
							if (isset($_POST['onlyid3'])) $idv3 = 1; else $idv3 = 0;
							isset($_POST['hitsas']) ? $hitsas = $_POST['hitsas'] : $hitsas = 0;
							search($_POST['searchfor'], $_POST['search'], $idv3, $hitsas);
						} else $match = false;
						break;

				case 'playlist':
						if (isset($_POST['editplaylist']) || isset($_POST['viewplaylist']))  
							playlist_editor($_POST['sel_playlist'], $_POST['previous']);
						else
						if (isset($_POST['playplaylist']))
						{
							if (isset($_POST['sel_playlist']) && is_numeric($_POST['sel_playlist']))
							{
								$kp = new kp_playlist($_POST['sel_playlist']);
								$kp->play();
							}		
						}
						break;

				case 'playlisteditor':
						if (isset($_POST['saveseq'])) 
						{
							playlist_savesequence($_POST['seq'],$_POST['sel_playlist']);
							playlist_editor($_POST['sel_playlist'], $_POST['previous'], $_POST['sort']);
						} else							
						if (isset($_POST['saveplaylist']))
						{
							if (is_numeric($_POST['sel_playlist']) && !empty($_POST['playlistname']) )
							{
								if (isset($_POST['shared'])) $shared = 1; else $shared = 0;
								if (isset($_POST['shuffle'])) $shuffle = 1; else $shuffle = 0;
								$id = $_POST['sel_playlist'];
								if (is_numeric($id))
								{
									$name = mysql_escape_string(stripslashes(checkchs($_POST['playlistname'])));						
									db_execquery('UPDATE '.TBL_PLAYLIST.' SET name = "'.$name.'", public = '.$shared.', status = '.$shuffle.' WHERE listid = '.$id);
								}
							}
							playlist_editor($_POST['sel_playlist'], $_POST['previous'], $_POST['sort']);				
						} else
						if (isset($_POST['sortplaylist']))
						{
							$id = $_POST['sel_playlist'];
							switch ($_POST['sort'])
							{					
								case 0: pl_sortalphabetic($id); break;
								case 1: pl_sortrandom($id); break;
								case 2: pl_sortoriginal($id); break;
								case 3: pl_removeduplicates($id); break;
							}				
							playlist_editor($_POST['sel_playlist'], $_POST['previous'], $_POST['sort']);
						} else
						if (isset($_POST['playplaylist']))
						{
							if (isset($_POST['sel_playlist']) && is_numeric($_POST['sel_playlist']))
							{
								$kp = new kp_playlist($_POST['sel_playlist']);
								$kp->play();
							}			
						} else
						if (isset($_POST['deleteplaylist']))
						{
							if (is_numeric($_POST['sel_playlist']))
							{
								$id = $_POST['sel_playlist'];
								playlist_delete($id);
								kplaylist_filelist($_POST['previous'],-1,$_POST['drive']);
							}
						} else 
						if (isset($_POST['playselected']))
						{
							kplay_m3uurl(); 	
							for ($i=0,$c=count($_POST['selected']);$i<$c;$i++)
							{
								$row = mysql_fetch_array(db_execquery('SELECT * FROM '.TBL_PLAYLIST_LIST.' WHERE id = '.$_POST['selected'][$i]));
								kPlay_sendlink2($row['sid']);					
							}				
						} else
						if (isset($_POST['delselected']))
						{
							if (count($_POST['selected']) > 0)
							{
								for ($i=0;$i<count($_POST['selected']);$i++)
								{
									$id = $_POST['selected'][$i];
									db_execquery('DELETE from '.TBL_PLAYLIST_LIST.' WHERE id = '.$id);
								}
								playlist_rewriteseq($_POST['sel_playlist']);
							}
							playlist_editor($_POST['sel_playlist'], $_POST['previous'], $_POST['sort']);				
						}
						break;
				
				case 'misc':
						if (isset($_POST['whatshot'])) whats_hot(db_guinfo('hotrows'));	
							else
						if (isset($_POST['whatsnew'])) whats_new(db_guinfo('hotrows'));
							else
						if (!empty($_POST['genrelist']))
						{
							if (isset($_POST['genreno'])) user_saveoption('defgenre', $_POST['genreno']);
							genrelist($_POST['genreno']);
						} else
						if (isset($_POST['logmeout']))
						{ 
							if ($cfg['demomode'] != 1) db_logout($u_cookieid, $phpenv['remote']); 
							klogon(); 
						} 
						break;
				
				case 'delsingleplaylist':
						$plid = $_GET['plid'];
						if (!empty($_GET['del']))
						{
							$id = $_GET['del'];
							if (is_numeric($id) && is_numeric($plid))
							{
								db_execquery('DELETE from '.TBL_PLAYLIST_LIST.' WHERE id = '.$id);					
								playlist_rewriteseq($plid);
							}
						}
						playlist_editor($plid, $_GET['p']);
						break;
				
				case 'hotselect':
						if (isset($_GET['artist'])) hotselect($_GET['artist']);	
						break;				
				
				case 'playalbum':
						$res = fsearch(decode64($_GET['p']), false, $curdrive, 'id,album');
						kplay_m3uurl();	
						if (!empty($_GET['ft'])) $ft = stripcslashes($_GET['ft']); else $ft = '';
						while ($row = mysql_fetch_row($res)) 
						{
							if (!empty($ft) && $ft != $row[1]) continue;
							kPlay_sendlink2($row[0]);
						}
						break;

				case 'downloadarchive':
						if (isset($_POST['mime']) && isset($_POST['file']))
						{
							if (!isset($_POST['filename']) || empty($_POST['filename'])) $filename = 'kpdl'.date('hi'); 
								else $filename = $_POST['filename'];
						
							$file = $cfg['archivetemp'] . $_POST['file'];
							if (file_exists($file) && checkstructure($_POST['file'], false) == 0)
							{
								kplay_archivedownload($file, $_POST['mime'], $filename);
								@unlink($file);								
							} else 
							{
								kprintheader(get_lang(260),0);
								echo '<form action="'.$PHP_SELF.'" method="get">';
								echo '<font class="notice">'.get_lang(261).'</font><br/><br/>';
								echo '<input type="button" value="'.get_lang(27).'" name="close" class="fatbuttom" onclick="javascript: window.close();"/>'; 
								echo '</form>';
								kprintend();
							}
						}
				
				case 'dlall':
						if (isset($_GET['p']) && isset($_GET['d']) && ALLOWDOWNLOAD && db_guinfo('u_allowdownload') && $cfg['archivemode'])
						{
							$kpa = new kparchiver();
							$res = fsearch(decode64($_GET['p']), false, $_GET['d']);
							while ($row = mysql_fetch_row($res)) $kpa->setfile($row[0]);								
							$kpa->execute();
						}
						break;

				case 'dlplaylist':
						if (isset($_GET['pid']) && ALLOWDOWNLOAD && db_guinfo('u_allowdownload') && $cfg['archivemode'])
						{
							$kpa = new kparchiver();
							$kp = new kp_playlist($_GET['pid']);
							$res = $kp->getres();							
							while ($row = mysql_fetch_row($res)) $kpa->setfile($row[0]);								
							$kpa->execute();
						}
						break;

				case 'dlselected':
						if (isset($_POST['filestoarc']) && ALLOWDOWNLOAD && db_guinfo('u_allowdownload') && $cfg['archivemode'])
						{
							$kpa = new kparchiver();
							$fl = explode(';', $_POST['filestoarc']);
							for ($i=0,$c=count($fl);$i<$c;$i++) if (is_numeric($fl[$i])) $kpa->setfile($fl[$i]);
							$kpa->execute();
						}
						break;

				case 'dlselectedjs':						
						kprintheader(get_lang(260),0);
						if (isset($_POST['filestoarc'])) echo $_POST['filestoarc'];
						?>
						<form name="arcfiles" action="<?php echo $PHP_SELF; ?>" method="post">
						<input type="hidden" name="action" value="dlselected"/>
						<input type="hidden" name="filestoarc" value=""/>
						<script type="text/javascript">
						<!--
						for(var i=0;i<opener.document.psongs.elements.length;i++) if(opener.document.psongs.elements[i].type == "checkbox") if (opener.document.psongs.elements[i].checked == true) 
							document.arcfiles.filestoarc.value = document.arcfiles.filestoarc.value + opener.document.psongs.elements[i].value + ';';
						document.arcfiles.submit();
						//-->
						</script>
						</form>
						<?php
						kprintend();
						break;

				case 'listedres':
						if (isset($_POST['hotoptions'])) 
						{
							if (isset($_POST['hotperiod']) && is_numeric($_POST['hotperiod'])) 
							{
								$filter = $_POST['hotperiod']; 
								user_saveoption('hotmode', $filter);
							} else $filter = 0;
							whats_hot(db_guinfo('hotrows'), $filter);
						} else
						if (isset($_POST['editplaylist']) || isset($_POST['viewplaylist']))  playlist_editor($_POST['sel_playlist'], $_POST['previous']);
						else
						if (isset($_POST['addplaylist']))
						{
							kprintheader(get_lang(61), 1);
							if (empty($_POST['selected'])) 
								echo '<font color="#000000" class="notice">'.get_lang(32).'&nbsp;&nbsp;</font>';
							else
							{
								db_addtoplaylist($_POST['sel_playlist'], $_POST['selected']);
								echo '<font color="#000000" class="notice">'.get_lang(33).'&nbsp;&nbsp;</font>';
							}
							echo '<a href="javascript:history.go(-1)" class="fatbuttom">&nbsp;'.get_lang(34).'&nbsp;</a>';
							echo '</body></html>';				
						} else
						if (isset($_POST['playplaylist']))
						{
							if (isset($_POST['sel_playlist']) && is_numeric($_POST['sel_playlist']))
							{
								$kp = new kp_playlist($_POST['sel_playlist']);
								$kp->play();
							}		
						} else
						if (isset($_POST['psongsselected']) || isset($_POST['psongsall']))
						{
							if (isset($_POST['psongsselected']))
							{
								kplay_m3uurl();
								if (isset($_POST['selected']))
									for ($i=0,$c=count($_POST['selected']);$i<$c;$i++) kPlay_sendlink2($_POST['selected'][$i]);
							} else 
							if (isset($_POST['psongsall']))
							{				
								$res = fsearch(decode64($_POST['previous']), false, $curdrive);
								kplay_m3uurl();	
								while ($row = mysql_fetch_row($res)) kPlay_sendlink2($row[0]);
							} 
						}					
						break;
				
				default:
					$match = false;
					break;	
			
			}
			if ($match) die();
		}
		isset($_GET['p']) ? $p = $_GET['p'] : $p = null;
		isset($_GET['n']) ? $n = $_GET['n'] : $n = null;
		isset($_GET['n2']) ? $n2 = $_GET['n2'] : $n2 = null;
		isset($_GET['d']) ? $d = $_GET['d'] : $d = null;
		kplaylist_filelist($p, $n, $d, $n2);

	} else
	{
		klogon();
	}
} else if (isset($_GET['checkcookie']))
{
	klogon(get_lang(237));
} else klogon();

?>
