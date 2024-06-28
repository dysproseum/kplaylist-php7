<?php
//kPlaylist 1.4 Build 363 (25-09-04_19.02) build by root

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

kPlaylist v1.4 makes your MP3 archive available via the WEB. Play music, 
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
	You can reach us at this e-mail address: kplaylist@kplaylist.net
  
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


// try to set the execution time to 1800 sec = 30 min. 
@ini_set('max_execution_time', 1800);
@ini_set('register_globals', 'Off');
@ini_set('display_errors', 'Off');

// what to prepend before the db, don't change this after installing! Do it before. If you do after, change the db names manually or set enable_install = 1.

$dbprepend = 'tbl_';

function setuptblconstants()
{
	global $dbprepend;
	$kpdbtables = array('playlist', 'playlist_list', 'search', 'users', 'kplayversion', 'mhistory', 'config', 'filetypes', 'settings');
	foreach ($kpdbtables as $name) define('TBL_'.strtoupper($name), $dbprepend.$name);
}

setuptblconstants();


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


// read here before enabling: http://www.kplaylist.net/forum/viewtopic.php?t=196
$id3editor = 0; // set to 1 to enable editing of id3 information.

// lame parameters (enable in web settings and then user.)
$lamecmd = '/usr/local/bin/lame --silent --nores --mp3input -h -m s -b %bitrate% "%file%" -';

$lamebitrates = array(0, 32, 40, 48, 56, 64, 80, 96, 112, 128, 160, 192, 224, 256, 320);

// If you want to disable logins and let everybody with http access to your
// site get in, change the two following options. (WARNING! ALL SECURITY NOW VANISH.)
$disablelogin = false;
// If you disable logins, a default user has to be chosen. Setting this to 1 means
// the first user which is usually the admin.
$assumeuserid = 1;

// enable the getid3 package. getid package must reside under getid3/ under the directory
// this file exists. If it does not, please change the 'include' statement below.
$enablegetid3 = 0;
if ($enablegetid3) include('getid3/getid3.php');

//how many titles of one album do we need to treat as a album? Turn to zero to show all.
$titlesperalbum = 0;

// For use of automatic search engine update via lynx / cron. Turn to 1 to enable. Check
// www.kplaylist.net for information how to run this update automatically.
$autoupdate = 0;
$autoupdatehost = '127.0.0.1';
$autoupdateuser = 'autooperate';

// list of directories to ignore.
$dirignorelist = array('..' => 1, '.' => 1, 'lost+found' => 1);

// demo mode on/off. Default off.
$demo_mode = 0; 

// for multiple downloads.
$archivemode = false;

$archivetemp = '/tmp/'; 

//	enabled	(0/1)	name	cmd	(%D = destination file,	%F source.)
// YOU MUST SUIT THESE ARCHIVERS TO YOUR OWN NEED. DO NOT USE THE DEFAULT BLINDLY.
$archivers = array(
	0 => array(1,	'zip', '/usr/bin/zip -j -0 %D "%F"', 'application/zip'),
	1 => array(1,	'rar', 'C:\Programfiler\WinRAR\rar.exe -m0 a %D "%F"', 'application/x-rar')
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

// syntax: .filename, mime header, file in M3U, get id function, search index
$streamtypes_default = array(
	0 => array ('mp3',	'audio/mpeg',			1, 1, 1),
	1 => array ('mp2',	'audio/mpeg',			1, 1, 1),
	2 => array ('ogg',	'application/x-ogg',	1, 2, 1),
	3 => array ('wav',	'audio/wave',			1, 0, 1),
	4 => array ('wma',	'audio/x-ms-wma',		1, 0, 1),
	5 => array ('mpg',	'video/mpeg',			0, 0, 1),
	6 => array ('mpeg', 'video/mpeg',			0, 0, 1),
	7 => array ('avi',	'video/avi',			0, 0, 1),
	8 => array ('wmv',	'video/x-ms-wmv',		0, 0, 1),
	9 => array('asf', 'application/vnd.ms-asf', 0, 0, 1),
	10 => array('m3u', 'audio/x-mpegurl',		0, 0, 1),
	11 => array('flac', 'audio/x-flac',			1, 0, 1),
	12 => array('jpg' , 'image/jpeg',			0, 0, 1)
); 


// fix for older versions of PHP.  
if (!isset($_GET) && !isset($_POST))
{
	$_GET = @$HTTP_GET_VARS;
	$_POST = @$HTTP_POST_VARS;
	$_COOKIE = @$HTTP_COOKIE_VARS;
	$_SESSION = @$HTTP_SESSION_VARS;
	$_ENV = @$HTTP_ENV_VARS;
	$_SERVER = @$HTTP_SERVER_VARS;
}

if (function_exists('mysql_unbuffered_query')) define('MYSQLFAST', true); else define('MYSQLFAST', false); 

function db_gconnect()
{
	global $db;
	if (mysql_connect($db['host'], $db['user'], $db['pass'])) if (mysql_select_db ($db['name'])) return true;
	return false;
}

function db_execquery($query, $fast=false)
{
	if ($fast && MYSQLFAST) return mysql_unbuffered_query($query);
	return mysql_query($query);
}

function db_execcheck($query)
{
	if (db_gconnect()) return mysql_query($query); else return 0;	
}

$app_ver  = '1.4'; 
$app_build = '363';

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
		if (isset($this->keys[$key])) define(strtoupper($key), $this->keys[$key]); else define(strtoupper($key), 0);
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
				'mailmp3'					=> array(0, 1)
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
	if ($resetconfiguration) $setctl->defaults();	
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

$base_dir = explode(';', $setctl->get('base_dir'));
$homepage = str_replace('KBUILD', $app_build, str_replace('KVER', $app_ver, $setctl->get('homepage')));
$deflanguage = $setctl->get('default_language');
$win32 = $setctl->get('windows');
if (!$win32) $dlrate = $setctl->get('dlrate'); else $dlrate = 0;
$disksync = $setctl->get('disksync'); 

// Audio and other file types to look / search / play / and open. 
// Syntax: .filename, mime header, file in M3U, get id function, search index


// general stuff used as globals
$u_searchstr  = '';
$userauth = 0;
$cookie_name = 'kplaylist';
$u_playlist = $u_playlistid = $dir_list = $gData = $mark = $validated_user = array();
$u_cookieid = -1;
$u_id = -1;
$pdir64='';
$gCnt = 0;

if (isset($_GET['d'])) $curdrive = $_GET['d']; else if (isset($_POST['drive'])) $curdrive = $_POST['drive']; else $curdrive = 0;

$phpenv = array();

if (!isset($PHP_SELF) || empty($PHP_SELF)) $PHP_SELF = $_SERVER['PHP_SELF'];

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

$streamtypes = array();

function loadstreamtypes()
{
	global $streamtypes_default, $streamtypes;
	$streamtypes = $streamtypes_default;
	$res = db_execquery('SELECT extension, mime, m3u, getid, search, id FROM '.TBL_FILETYPES.' WHERE enabled = 1', true);
	if ($res) while ($row = mysql_fetch_row($res)) $streamtypes[] = $row;
}

if (DBCONNECTION) loadstreamtypes();


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
        $out = stripslashes($in);        
		if (!empty($out)) { for ($i=0;$i<strlen($out);$i++) if ($out[$i] == $key ) $out[$i] = $rep; } return $out;
}

function getimagelink($image)
{
	global $PHP_SELF, $setctl;
	if (!empty($setctl->keys['externimagespath'])) return $setctl->get('externimagespath').$image; else return $PHP_SELF.'?image='.$image;
}


$klang[0]  = array("English", "iso-8859-1", "English", // 2
"What's hot",	"What's new",	"Search",	"(only %1 shown)",	"sec",	"Search results: '%1'", // 8
"found",	"None.",		"update search database options", "Delete unused records?", "Rebuild ID3?", // 13
"Debug mode?", "Update", "Cancel", // 16
"update search database", "Found %1 files.", "Could not determine this file: %1, skipped.", "Installed: %1 - Update: %2, scan: ", "Scan: ", // 21
"Failed - query: %1", "Could not read this file: %1. Skipped.", "Removed: %1", // 24
"Inserted %1, updated %2, deleted %3 where %4 failed and %5 skipped through %6 files - %7 sec - %8 marked for deletion.", // 25
"Done", "Close", 'Could not find any files here: "%1"', "kPlaylist Logon", "Album list for artist: %1", "Hotselect %1", // 31
"No tunes seleted. Playlist not updated.", "Playlist updated!", "Back", "Playlist added!", "Remember to reload page.", // 36
"login:", "secret:", "Notice! This is a non public website. All actions are logged.", "Login", "SSL required for logon.", // 41
"Play", "Delete", "Shared: ", "Save", 'Control playlist: "%1" - %2 titles', "Editor", "Viewer", "Select", "Seq", // 50
"Status", "Info", "Del", "Name", "Totals:", "Error", "Action on selected: ", "Sequence:", "edit playlist", // 59
"Delete this entry", "add playlist", "Name:", "Create", "Play: ", "File", "Album", "All", "Selected", "add", // 69
"play", "edit", "new", "Select:", "Play Control: ", "Playlist: ", "Hotselect numeric", "Keyteq gives you:", // 77
"(check for upgrade)", "Homesite", "only id3", "album", "title", "artist", "Hotselect album from artist", // 84
"view", "Shared playlists", "Users", "Admin control", "What's new", "What's hot", "Logout", "Options", // 92
"Check", "My", "edit user", "new user", "Full name", "Login", "Change password?", "Password", "Comment", // 101
"Access level", "On", "Off", "Delete user", "Logout user", "Refresh", "New user", "del", "logout", // 110
"Use EXTM3U feature?", "Show how many rows (hot/new)", "Max search rows", "Reset", "Open directory", // 115
"Go to directory: %1", "Download", "Go one step up", "Go to root directory.", "Check for upgrade", "users", "Language", // 122
"options", "Booted", "Shuffle:", "Settings", //126
"Base directory", "Stream location", "Default language", "A Windows system", "Require HTTPS", // 131
"Allow seek", "Allow download", "Session timeout", "Report failed login attempts", //135
"Hold on - fetching file list", "Playlist could not be added!", "Admin", "Login with HTTPS to change!", // 139
'Enable streaming engine', 'Title', 'Artist', 'Album', 'Comment', 'Year', 'Track', 'Genre', // 147
'not set', 'Max download rate (kbps)','User', '%1 mins - %2 titles','%1 kbit %2 mins', 'Genre list: %1', // 153
'Go', '%1d %2h %3m playtime %4 files %5 mb', 'No relevant resources here.', 'Password changed!', 'Signup', 'Please make a selection!', // 159
'What is update?', 'Click here for help','Use external images?', 'External images path', 'Current password', 'Current password does not match!', // 165
'Preferred archiver', 'Archive could not be made', 'Probable file duplicate found:  "%1" "%2"', 'Really delete playlist?', 'Alphabetical', 'Random', 'Sort', // 172
'Original', 'Use javascript', 'Are you sure you want to delete this user?', 'View history', 'history', 'Rows', 'External CSS file', 'Remove duplicates', // 180
'OK', 'ERR', 'Stream', '(show as)', 'files', 'albums','%1d %2h %3m %4s', 'General', 'Customize', 'Filehandling', 'Click on ? for help.', 'Automatic database sync', // 192
'Send file extension', 'Allow unauthorized streams', 'Include headers', 'External javascript', 'Homepage', 'Show Keyteq gives you part', 'Show upgrade part', // 199
'Show statistics', 'Write ID3v2 with stream', 'Enable user signup', 'Filetypes', 'Yes', 'No', 'Extension', 'MIME', 'Include in M3U', 'edit filetype', 'Sure?', // 210
'Optimistic filecheck', 'Randomizer', 'Mode','Playlist', 'None, directly', 'My favourites', 'Did not find any hits', 'Alltime hits', 'Order', 'Enable LAME support?', // 220
'Disabled','Allow LAME usage?', 'Email', 'Allow to mail files?', 'SMTP server', 'SMTP port','Mail to', 'Message', 'Send', 'Mail sent!', 'Activate upload', // 231
'Upload directory','Activate mp3mail', 'Upload', 'File uploaded!', 'File could not be uploaded!', 'You must enable cookies to log in!','Period', 'ever',
'this week','this month','last month', 'hits');

$klang[1]  = array("Norwegian", "ISO-8859-1", "Norsk", "Hva er mest spilt", "Hva er nytt", "S&oslash;k", "(bare %1 vist)", "sek", "S&oslash;ke resultater: '%1'", "fant", "Ingen.", "oppdater s&oslash;ke database valg", "Slett ubrukte rader?", "Ombygg ID3?", 
"Debug modus?", "Oppdater", "Avbryt", "oppdaterer s&oslash;ke database", "Fant %1 filer.", "Kunne ikke lese fil: %1, hoppet over.", "Installert: %1 - Oppdaterer: %2, skanner: ", "Skanner: ", "Feilet - query: %1", "Kunne ikke lese denne filen: %1. Hoppet over.", "Fjernet: %1", 
"La inn %1, oppdaterte %2, slettet %3 hvor %4 feilet og %5 ble hoppet over igjennom %6 filer - %7 sek - %8 markert for sletting.", "Ferdig", "Lukk", "Kunne ikke finne noen filer her: &quot;%1&quot;", "kPlaylist Innlogging", "Album liste fra artist: %1", "Hurtigvelg %1", "Ingen l&aring;ter valgt. L&aring;tliste ikke oppdatert.", "L&aring;tliste oppdatert!", "Tilbake", "L&aring;tliste lagt til!", "Husk &aring; oppdatere side.", "logg inn:", "hemmelighet:", "Advarsel! Dette er en privat webside. Alle handlinger blir logget.", "Logg inn", "SSL kreves for p&aring;logging.", "Spill", "Slett", "Delte: ", "Lagre", "Kontroller l&aring;tliste: &quot;%1&quot; - %2 titler", 
"Editor", "Viser", "Velg", "Sek", "Status", "Info", "Slett", "Navn", "Totalt:", "Feil", "Handling p&aring; valgte: ", 
"Sekvens:", "editer l&aring;tliste", "Slett denne raden", "ny l&aring;tliste", "Navn:", "Lag", "Spill: ", "Fil", "Album", "Alle", "Valgte", 
"legg til", "spill", "editer", "ny", "Velg:", "Spille kontroll: ", "L&aring;tliste: ", "Hurtigvelg numerisk", "Keyteq gir deg:", "(sjekk for ny versjon)", "Hjemmeside", 
"bare id3", "album", "tittel", "artist", "Hurtigvelg album fra artist", "vis", "Delte l&aring;tlister", "Brukere", "Admin kontroll", "Hva er nytt", "Mest spilt",
"Logg ut", "Valg", "Sjekk", "Min", "editer bruker", "ny bruker", "Fullt navn", "Brukernavn", "Endre passord?", "Passord", "Kommentar", 
"Aksess niv&aring;", "P&aring;", "Av", "Slett bruker", "Logg ut bruker", "Oppdater", "Ny bruker", "slett", "logg ut", "Bruke EXTM3U egenskap?", "Vise hvor mange rader (mest spilt/nytt)", 
"Maks s&oslash;ke rader", "Omsetting", "&Aring;pne katalog", "G&aring; til katalog: %1", "Last ned", "G&aring; ett steg opp", "G&aring; til kjerne katalog.", "Sjekk for ny versjon", "brukere", "Spr&aring;k", "valg", "Avsperret", "Omskuff:", "Innstillinger", "Arkiv katalog", "Nedlastningslokalisasjon", "Standard spr&aring;k", "Et Windows system", "Krev HTTPS", "Tillat spoling", "Tillat nedlastninger", "Innloggingstidsavbrudd", "Rapportere mislykkete p&aring;loggingsfors&oslash;k", "Vent - skaper filliste", "Spilleliste kunne ikke bli lagt til!", "Admin", "Logg inn med HTTPS for &aring; endre!", "Aktiver innebygd kanalvirkning", "Tittel", "Artist", "Album", "Kommentar", "&Aring;r", 
"L&aring;tnummer", "Stil", "ikke satt", "Maksimal nedlastningshastighet", "Bruker", '%1 minutter - %2 titler','%1 kbit %2 minutter','Stil liste: %1', 
'Gå', '%1d %2t %3m spilletid %4 filer %5 mb', 'Ingen relevante ressurser her.', 'Passord endret!', 'Ny bruker', 'Vennligst foreta et valg!', 'Hva er oppdatering?', 'Klikk her for hjelp',
'Bruk eksterne bilder?', 'Eksterne bilder lokalisasjon', 'Eksisterende passord', 'Det eksisterende passordet stemmer ikke!', 'Ønsket arkiverer',' Arkiv kunne ikke bli laget',
'Sannsynlig duplikat ble funnet: %1 - %2', 'Virkelig slette spilleliste?', 'Alfabetisk', 'Tilfeldig', 'Sorter','Original','Bruke javascript','Sikker på at du vil slette denne brukeren?', 'Vis historie', 'historie', 'Rader', 'Ekstern CSS fil', 'Fjern duplikater', 'OK', 'FEIL', 'Stream', '(vis som)','filer','album', '%1d %2t %3m %4s', 'Generelt', 'Skreddersy', 'Filhåndtering', 'Klikk på ? for hjelp.', 'Automatisk database synkronisering', 'Send filendelse', 'Tillat uautorisert streams', 'Inkluder headers', 'Ekstern javascript', 'Hjemmeside', 'Vis Keyteq gir deg del', 'Vis oppgraderingsdel', 'Vis statistikk', 'Skriv ID3v2 i stream', 'Ny bruker funksjonalitet', 'Filtyper', 'Ja', 'Nei', 'Filendelse', 'MIME', 'Inkluder i M3U', 'editer filtype' ,'Sikker?', 'Optimistisk filsjekk', 'Randomiserer', 'Modus', 'Spilleliste', 'Ingen, direkte', 'Mine favoritter', 'Fant ingen rader', 'Hits på systemet', 'Rekkefølge','Slå på LAME støtte','Avslått','Tillatt LAME bruk?', 'E-post','Tillatt å e-post filer',' SMTP tjener', 'SMTP port','E-post til','Beskjed','Send','E-post sendt!','Aktiver opplastning','Opplastningskatalog'); 

$klang[2]  = array("German", "ISO-8859-15", "Deutsch", "Was ist begehrt", "Was ist neu", "Suchen", "(nur %1 angezeigt)", "sek", "Such Ergebnisse: '%1'", "gefunden", "keine.", "aktuelle Datenbank-Such-Optionen", "unbenutze Datensätze löschen ?", "ID3 erneuern?", "Debug Modus?", "Update", "Abbrechen", "Such Datenbank erneuern", "%1 Dateien gefunden", "Konnte Datei nicht ermitteln: %1, übersprungen.", "Installiert: %1 - Bearbeitet: %2, untersuche:", "Scan: ", "Fehler - Abfrage: %1", "Konnte File nicht lesen: %1. Übersprungen.", "Entfernt: %1", "eingefügt %1, geändert %2, gelöscht %3 dabei %4 fehlgeschlagen und %5 übersprungen; %6 Dateien gesamt - %7 sek - %8 markiert zum löschen", "Erledigt", "Schliessen", "Konnte hier keine Dateien finden: \"%1\"", "kPlaylist Login", "Album Liste für Interpret: %1", "Kurzwahl %1", "Keine Lieder ausgewählt. Playliste nicht aktualisiert.", "Playliste aktualisiert", "Zurück", "Playliste hinzugefügt!", "Die Seite erneut laden !", "Login:", "Passwort:", "Achtung ! Dies ist eine Private Webseite ! Alle Aktionen werden protokolliert !", "Login", "SSL wird zum einloggen benötigt", "Abspielen", "Löschen", "Gemeinsame: ", "Sichern", "Playliste bearbeiten: \"%1\" - %2 Titel", "Editor", "Viewer", "Auswählen", "Seq", "Status", "Info", "Löschen", "Name", "Summe", "Fehler", "Aktion auf ausgewählte ", "Reihenfolge:", "bearbeite Playlist", "Diesen Eintrag löschen", "Playliste hinzufügen", "Name:", "Erstellen", "Spielen: ", "Datei", "Album", "Alle", "Ausgewählte", "Hinzufügen", "Spielen", "ändern", "neu", "Auswählen:", "Spielen: ", "Playlist: ", "Kurzwahl Numerisch", "Keyteq präsentiert:", "(Suche nach Update)", "Webseite", "Nur id3 Tags", "Album", "Titel", "Interpret", "Kurzwahl Album nach Interpret", "zeige", "Gemeinsame Playliste", "Benutzer", "Admin Kontrolle", "Was ist neu", "Was ist Hip", "Logout", "Optionen", "Überprüfen", "Meine", "Benutzer ändern", "Neuer Benutzer", "Vollständiger Name", "Login", "Passwort ändern ?", "Passwort", "Anmerkung", "Zugangs Level", "An", "Aus", "Benutzer löschen", "Benutzer ausloggen", "Erneuern", "Neuer Benutzer", "Löschen", "Logout", "EXTM3U Feature benutzen?", "Wieviele Zeilen zeigen (hip/neu)", "max. Zeilen bei Suchergebnissen", "Reset", "Verzeichnis öffnen", "Gehe zum Verzeichnis: %1", "Download", "Eine Ebene höher", "In das Basisverzeichnis", "Nach einem Upgrade suchen", "Benutzer", "Sprache", "Optionen", "Gestoppt", "Zufall:", "Einstellungen", "Hauptverzeichnis", "Stream location", "Voreingestellte Sprache", "Ein Windows-System", "benötigt HTTPS", "Suche erlaubt", "Download erlaubt", "Session abgelaufen", "Berichte fehlgeschlagene Login-Versuche", "Bitte warten - hole Dateiliste", "Playliste konnte nicht erstellt werden!", "Administrator", "LOGON mit HTTPS zum zu ändern", "Streaming Engine aktivieren", "Titel", "Artist", "Album", "Kommentar", "Jahr", "Track", "Genre", "nicht gesetzt", "Max download rate (kbps)", "Benutzer", "%1 min - %2 Titel", "%1 kbit %2 min", "Genre liste: %1", "Los", "%1d %2h %3m Spielzeit %4 Dateien %5 MB", "Keine relevanten Resourcen hier:", "Passwort geändert!", "Anmelden", "Bitte mache eine Auswahl!", "Was ist das update?", "Klicke hier für Hilfe", "Benutze externe Bilder?", "Externe Bilder Pfad", "Aktuelles Passwort", "Aktuelles Passwort nicht korrekt!", "Bevorzugter Archivierer", "Archiv konnte nicht erstellt werden", "Mögliche doppelte Datei gefunden: %1 - %2", "Playliste wirklich löschen?", "Alphabetisch", "Zufall", "Sortiert", "Original", "Benutze Javascript", "Den Benutzer wirklich löschen?", "Zeige History", "History", "Zeilen", "Externes CSS File", "Auslösche Doppel", "O.K", "ERR", "Strom", "(erscheinen wie)", "akten", "album", "%1d %2h %3m %4s ", "Allgemein", "Anpassen", "Akte Behandlung", "Klick das ? für Hilfe", "Automatische Datenbanksynchronisierung", "Senden Sie Dateiextension", "Erlauben Sie nicht autorisierte Ströme", "Überschriften mit", "Extern javascript", "Webseite");

$klang[3]  = array("Swedish", "iso-8859-10", "Svenska", "Vad är mest spelat", "Vad är nytt", "Sök", "(endast %1 visad)", "Sek", "Sökresultat: '%1'", "hittade", "Ingen.", "uppdatera sök databas inställningar", "Ta bort oanvända album", "Återuppbygg ID3?", 
"Kör debug?", "Uppdatera", "Avbryt", "uppdatera sökdatabas", "Hittade %1 filer.", "Kunde inte läsa fil: %1, hoppade över.", "Installerer %1 - Uppdaterar: %2, läser:", "Läser:", "Misslyckades - fråga: %1", "Kunde inte läsa filen: %1, hoppade över", "Tog bort: %1", 
"Infogade %1, uppdaterade %2, tog bort %3, varav %4 misslyckades och hoppade över %5 av %6 filer - %7 sek - %8 markerade för borttaganing", "Färdig", "Stäng", "Kunde inte hitta några filer här: '%1'", "kPlaylist Inloggning", "Albumlista för artist: %1", "Snabbval %1", "Inga låtar valda. Låtlistan är ej updaterad.", "Låtlista uppdaterad!", "Tillbaka", "Spellista inlagd!", 
"Kom ihåg att uppdatera sidan.", "Användarnamn:", "Lösenord:", "Observera! Detta är inte en publik websida. All aktivitet är loggad.", "Inloggning", 
"SSL behövs för inloggning", "Spela", "Ta Bort", "Delad:", "Spara", "Kontrollera låtlista: \"%1\" - %2 titlar", 
"Redigerare ", "Visare ", "Välj ", "Sek ", "Status ", "Info ", "Ta Bort", "Namn ", "Totalt:", "Fel", "Handling vid val", 
"Sekvens:", "redigera spellista", "Ta bort den här raden", "Lägg till spellista", "Namn:", "Skapa", "Spela:", "Fil", "Album", "Alla", "Markerad", 
"lägg till", "spela", "redigera", "ny", "Välj:", "Spelkontroll:", "Spellista:", "Snabbvälj numeriskt", "Keyteq ger dig:", "(Uppgraderings kontroll)", "Hemsida", 
"endast id3", "album", "titel", "artist", "Snabbvälj album från artist", "visa", "Delade spellistor", "Användare", "Adminkontroll", "Vad är nytt", "Mest spelat", 
"Logga ut", "Inställningar", "Kontrollera ", "Min ", "redigera användare", "ny användare", "Fullständigt namn", "Användarnamn ", "Ändra lösenord?", "Lösenord", "Kommentar ", 
"Behörighet ", "På ", "Av ", "Ta bort användare", "Logga ut användare", "Uppdatera ", "Ny användare", "ta bort", "logga ut", "Använd EXTM3U funktion?", "Visa hur många rader (mest spelat/nytt)", 
"Högst antal sökrader", "Nollställ", "Öppna katalog", "Gå till katalog: %1", "Ladda ner", "Gå ett steg upp", "Gå till rotkatalogen", "Kolla efter uppgradering", "användare ", "Språk ", "inställningar ", 
"Kickad", "Blanda", "Inställningar", "Rotnivå ", "Stream lokalisering", "Standard språk", "Ett Windowssystem", "Kräv HTTPS", "Tillåt filsök", "Tillåt nerladdning", "Sessionen avbruten.", 
"Rapportera misslyckat loginförsök", "Vänta - hämtar fillista", "Spellista kunde inte läggas till!", "Admin", "Lagga in med HTTPS för att ändra!", "Aktivera streaming", "Titel", "Artist", "Album", "Kommentar", "År",
"Spår", "Genre", "Inte satt", "Max nerladdningshastighet (kbps)", "Användare", "%1 min - %2 titlar", "%1 kbit %2 min", "Genre lista: %1", "Gå", "%1d %2t %3m speltid %4 filer %5 MB", "Inga relevanta resurser här.", 
"Lösenordet ändrat!", "Skapa konto", "Var vänlig och gör ett val!", "Vad är uppdatering?", "Klicka här för hjälp.", "Använda externa bilder?", "Externa bildens sökväg.", "Nuvarande lösenord", "Nuvarande lösenord matchar inte!", "Önskad arkiverare", "Arkiv kunde inte skapas", 
"Trolig fildubblett hittad", "Verkligen radera spellistan?", "Alfabetisk", "Mixat", "Sortera", "Original", "Använd javascript", "Är du säker att du vill radera denna användare?", "Visa historia", "historia", "Rader", 
"Extern CSS fil", "Ta bort dubletter", "OK", "FEL", "Stream", "(visa som)", "filer", "album", "%1d %2t %3m %4s", "Generellt", "Anpassa", 
"Filhanterning", "Klicka på ? för hjälp", "Automatisk databas synkronisering", "Skicka fil ändelse", "Tillåt", "Inkludera headers", "Externt javascript", "Hemsida", "Visa Keyteq ger dig del", "Visa uppgraderingsdel", "Visa statistik", 
"Skriv ID3v2 med stream", "Aktivera användar registrering", "Filtyper", "Ja", "Nej", "Fil ändelse", "MIME", "Inkludera i M3U", "editera filtyp", "Säkert?", 
"Optimistisk filkontroll");

$klang[4]  = array("Dutch", "ISO-8859-15", "Nederlands", "Meest gespeeld", "Wat is nieuw", "Zoeken", "slechts %1 zichtbaar", "sec", "Zoek resultaten: '%1'", "gevonden", "Geen.", "???", "Verwijderen ongebruikte bestanden? ", "ID3 vernieuwen?", "Fout opsporings mode?", "Vernieuwen", "Annuleren", "Vernieuwen database", "%1 bestanden gevonden.", "Bestand kan niet benaderd worden: %1, overgeslagen.", "Geïnstalleerd: %1 Aangepast: %2 Scan:", "Scannen:", "Fout - selectie: %1", "Kan het bestand niet lezen: %1. Overgeslagen.", "Verwijderd: %1", "Toegevoegd %1, bijgewerkt %2, verwijderd %3 waar %4 is mislukt en %5 overgelagen op %6 bestanden - %7 sec - %8 gemarkeerd voor verwijdering.", "Klaar", "Sluiten", "Kan geen bestanden vinden in: \"%1\"", "kPlaylist inloggen", "Albumlijst van artiest: %1", "Snelkeuze %1", "Geen muziek geselecteerd. Afspeellijst niet bijgewerkt.", "Afspeellijst bijgewerkt!", "Terug", "Afspeellijst toegevoegd!", "Niet vergeten om de pagina te verversen.", "Gebruikersnaam:", "Wachtwoord:", "NB! Dit is een niet publieke website. Alle acties worden opgeslagen in een log bestand.", "Ga verder...", "SSL benodigd om in te loggen.", "Afspelen", "Verwijderen", "Lijst delen?:", "Bewaren", "Instellingen afspeellijst \"%1\"- %2 nummer(s)", "Editor", "Viewer", "Selecteren", "Volgorde", "Status", "Informatie", "Verwijderen", "Naam", "Totalen:", "Fout", "Actie op selectie:", "Volgorde:", "afspeellijst bewerken", "Verwijder dit bestand", "afspeellijst toevoegen", "Naam:", "Creëren", "Afspelen:", "Bestand", "Album", "Alles", "Selectie", "selectie toevoegen", "afspelen", "bewerken", "nieuw", "Selectie:", "Afspeel opties", "Afspeellijst:", "Snelkeuze nummer", "Keyteq presenteerd:", "(Zoek update)", "Homepage", "alleen id3", "album", "titel", "artiest", "Snelkeuze albums van artiest", "bekijk", "Gedeelde afspeellijsten", "Gebruikers", "Administratie opties", "Wat is nieuw", "Wat is Populair", "Uitloggen", "Opties", "Controleer", "Gebruiker", "bewerk gebruikersaccount", "nieuw gebruikersaccount", "Volledige naam", "Inlog naam:", "Wachtwoord veranderen?", "Wachtwoord", "Commentaar", "Toegangs level", "Actief", "Uit", "Verwijder gebruiker", "Gebruiker afsluiten", "Ververs pagina", "Nieuwe gebruiker", "Wissen", "uitloggen", "Gebruik EXTM3U optie?", "Aantal rijen (Populair / Nieuw)", "Aantal rijen zoekresultaat", "Reset", "Open map", "Ga naar map: %1", "Download", "Een stap terug", "Bovenste map", "Update controle", "gebruikers", "Taal", "opties", "Booted", "Willekeurig", "Instellingen", "Start folder", "Stream lokatie", "Standaard taal", "Een Windows systeem", "HTTPS benodigd", "Seek toestaan", "Downloaden toestaan", "Sessie timeout", "Raporteer niet geslaagde inlog procedures", "Een ogenblik - lijst ophalen", "Afspeellijst kan niet toegevoegd worden!", "Beheer", "Verbinding via https gebruiken ", "Gebruik stream engine", "Titel", "Artiest", "Album", "Bijzonderheden", "Jaar", "Nummer", "Genre", "niet ingesteld", "Maximale downloadsnelheid (kbps)", "Gebruiker", "%1 minuten- %2 titels", "%1 kbit %2 minuten", "Genre lijst: %1", "Ok", "%1d %2h %3m afspeelduur %4 bestanden %5 mb", "Geen relevante bron aanwezig", "Wachtwoord veranderd!", "Aanmelden", "Maak een keuze a.u.b.!", "Wat is vernieuwen database?", "Klik hier voor help", "Gebruik externe plaatjes", "Path naar externe plaatjes", "Oude wachtwoord", "oude wachtwoord is niet correct!", "Archief bestands voorkeur", "Archief kon niet aangemaakt worden", "Mogelijk bestand dubbeling gevonden: %1 - %2", "Afspeellijst verwijderen?", "Alfabetisch", "Willekeurig", "Sorteer", "Origineel", "Gebruik Javascript", "Weet u zeker dat u deze gebruiker wilt verwijderen?", "Geef geschiedenis weer", "geschiedenis", "Regels", "Extern Css bestand", "Verwijder dubbelingen", "Ok", "FOUT", "", "(Show als)", "bestanden", "", "", "Algemeen", "Aangepast", "Bestands opties", "Klik op ? voor hulp.", "Automatische database synchronisatie", "Zend bestand extentie", "Accepteer niet geautoriseerde stream", "Sluit headers in", "Extern javascript", "", "Laat \"Keyteq presenteerd\" regel zien", "Laat \"Controleer update\" regel zien", "Show tekst met statistieken", "Stuur ID3v2 mee met stream", "Sta aanmelding van gebruikers toe", "Bestand typen", "Ja", "Nee", "Extentie", "", "M3U insluiten", "Pas bestandtype aan", "Zeker?", "Optimistische bestandscontrole", "Willekeurig afspelen", "", "Afspeel lijst", "Geen, direct", "Mijn favorieten", "Niets gevonden", "Meest gespeeld", "Volgorde", "Ondersteuning voor LAME aanzetten?", "Uitgezet", "Gebruik van LAME toestaan?");


$klang[5]  = array("Spanish", "ISO-8859-1", "Español", "Lo Padre", "Lo Nuevo", "B&uacute;squeda", "s&oacute;lo 1% visible", "seg", "Resulados de B&uacute;squeda: '%1'", "encontrado", "Ninguno.", "actualizar opciones de base de datos de b&uacute;squeda", "&iquest;Suprimir entradas sin uso? ", "&iquest;ReconstruirID3? ", 
"&iquest;Modo de Debug? ", "Actualizar", "Cancelar", "actualizar base de datos de b&uacute;squeda", "Se Encontraron %1 archivos", "No se pod&iacute;a determinar este archivo: %1, saltado", "%1 - Actualizar: %2, scanear:  ", "Scanear", "B&uacute;squeda Fallada: %1", "No se pod&iacute;a enconrar archivo: %1. Saltado. ", "Quitado: %1", 
"Insertado %1, actualizado %2, quitado %3 d&oacute;nde %4 fall&oacute; y %5 saltado por %6 archivos - %7 seg - %8 marcado para borrar.", "Finalizado", "Cerrar", "No se pod&iacute;a encontrar archivos utilzando %1", "kPlaylist Nombre de Usuario", "Lista de disco de artista: %1 ", "Hotselect %1 ", "Ninguna canci&oacute;n seleccionada. Lista no actualizada. ", "&iexcl;Lista actualizada con &eacute;xito!", "Regresar", "&iexcl;Lista actualizada!", 
"Actualice la p&aacute;gina", "nombre de usuario:", "contrase&ntilde;a", "Aviso! Este es un sitio restringido. Todos movimientos se guardan.", "Nombre de usuario", "SSL requirido para entrar.", "Tocar", "Suprimir", "Compartido:", "Guardar", "Lista de Control: &quot;%1&quot; - %2 t&iacute;tulos", 
"Editor", "Visor", "Seleccionar", "Seq", "Estatus", "Info", "Sup", "Nombre", "Totales:", "Error", "Acci&oacute;n sobre seleccionado", 
"Sequencia:", "editar lista", "Suprimir esta entrada", "agregar lista", "Nombre:", "Crear", "Tocar:", "Archivo", "Disco", "Todo", "Seleccionados", 
"agregar", "tocar", "editar", "nuevo", "Seleccionar:", "Tocar Control:", "Lista:", "Seleccionador N&uacute;merico ", "Keyteq le proporciona:", "(checar por actualizaciones)", "P&aacute;gina Principal", "s&oacute;lo id3", "disco", "t&iacute;tulo", "artista", "Seleccionador disco de artista", "vista", "Listas compartidas", "Usuarios", "Control de administrador", "Lo nuevo", "Lo popular", "Salir", "Opciones", "Checar", "Mi", "editar usuario", "nuevo usuario", "Nombre completo", "Nombre de usuario", "&iquest;cambiar contrase&ntilde;a?", "Contrase&ntilde;a", "Comentario", 
"Nivel de aceso", "Encendido", "Apagado", "Suprimir usuario", "Salir usuario", "Actualizar", "Nuevo usuario", "sup", "salir", "Utilizar la opci&oacute;n de EXTM3U?", "Mostrar cuantas filas (popular/nuevo)", 
"M&aacute;x filas de b&uacute;squeda", "Restaurar", "Directorio abierto", "Abriri directorio: %1", "Descargar", "Subir un nivel", "Ir directo al directorio de ra&iacute;z", "Buscar actualizaciones", "usuarios", "Idioma", "opciones", 
"Cerrado", "Barajadura:", "Ajustes", "Directorio bajo", "Localizaci&oacute;n de la corriente", "Lengua del defecto", "Un sistema de Windows", "Requiera HTTPS", "Permita seek", "Permita download", "Sesi&oacute;n descanso", 
"Informe fallado conexi&oacute;n tentativas", "Sostenga encendido - traer la lista del archivo", "Playlist no pod&iacute;a ser agregado!", "Admin", "Conexi&oacute;n con HTTPS a cambiar", "", "Titulo", "Artista", "Albúm", "Comentario", "Año", 
"Corte", "Genero", "", "Máximo rátio de descarga (kbps)", "Usuario", "", "", "", "", "", "", "Contraseña cambiada!", "", "");

$klang[6]  = array("Portuguese", "ISO-8859-1", "Português", "este é popular", "Este é novo", "Busca", "(apenas %1 encontrado)", "seg", "Resultados da busca: '%1'", "encontrado", "Nenhum", "atualizar opções da busca na base de dados ", "Apagar entradas sem uso? ", "Reconstruir ID3?", 
 "Modo Debug?", "Atualizar", "Cancelar", "Atualizar busca no banco de dados", "Encontrados %1 arquivos.", "Não foi possível determinar este arquivo: %1, descartado", "Install %1 - Atualizar: %2, escanear:", "Escanear:", "Falha na busca: %1", "Não foi possível ler este arquivo: %1. Descartado.", "Removido: %1", 
 "Inserido %1, atualizado %2, apagado %2, onde %4, falhou em %5, descartado por %6, arquivos - %7 seg - %8 marcado para ser deletado", "Finalizado", "Fechar", "Não foi possível encontrar arquivos aqui: \"%1\"", "Logon kPlaylist", "Lista de álbum por artista: %1", "Populares %1", "Nenhuma música selecionada. Lista não atualizada.", "Lista atualizada!", "Voltar", "Lista atualizada",  "Lembre-se de atualizar a página.", "login:", "senha:", "Atenção! Este não é um site restrito. Todas as ações são monitoradas.", "Login", "SSL necessário para entrar.", "Tocar", "Apagar", "Compartilhado", "Salvar", "Lista de controlhe: \"%1\" - %2 títulos", 
 "Editor", "Visualizador", "Selecionar", "Seq", "Status", "Info", "Del", "Nome", "Totais", "Erro", "Ação selecionada:", 
 "Sequência", "editar lista", "Apagar esta entrada", "adicionar lista", "Nome:", "Criar", "Tocar:", "Arquivo", "Álbum", "Todos", "Selecionado", 
 "adicionar", "tocar", "editar", "novo", "Selecionar", "Controle", "Lista:", "Selecionar número", "Keyteq oferece:", "(verificar atualização)", "Página incial", 
 "apenas id3", "álbum", "título", "artista", "Selecionar álbum por artista", "ver", "Listas compartilhadas", "Usuários", "Controle de administrador", "Este é novo", "Este é popular", 
 "Logout", "Opções", "Verificar", "Meu", "editar usuário", "novo usuário", "Nome completo", "Login", "Mudar senha?", "Senha", "Comentário", 
 "Nível de acesso", "Ligado", "Desligado", "Apagar usuário", "Desconectar usuário", "Atualizar", "Novo usuário", "apagar", "desconectar", "Utilizar opção EXTM3U?", "Mostrar quantos arquivos (popular/novo)",  "Máximo de arquivos encontrados", "Restaurar", "Abrir diretório", "Para o diretório: %1", "Download", "Subir um nível", "Para o diretório principal", "Verificar atualizações", "usuários", "Linguagem", "opções", 
 "Carregado", "Aleatório", "Configurações", "Diretório base", "Local de stream", "Linguagem padrão", "Sistema Windows", "Requer HTTPS", "Permitir busca", "Permitir download", "Sessão expirou",  "Falha na tentativa de login", "Aguarde - buscando a lista de arquivos", "Lista não pode ser adicionada!", "Admin",
 "Início de uma sessão com o HTTPS a mudar");

$klang[7]  = array("Finnish", "ISO-8859-1", "Suomi", "Suosituimmat", "Uusimmat", "Etsi", "(pelkästään %1 näytetään)", "sek", "Haku-tulokset: '%1'", "löytyi", "Tyhjä.", "päivitä hakutietokannan asetukset", "Poista käyttämättömät tiedot?", "Uudelleenrakenna ID3?", 
"Debug-moodi?", "Päivitä", "Peruuta", "päivitä hakutietokanta", "Löytyi %1 tiedostoa", "Ei voinut määrittää: %1, skipattu.", "Install %1 - Päivitä: %1,  tarkistus:", "Tarkistus:", "Epäonnistui - haku: %1", "Ei voinut lukea tätä tiedostoa: %1. Skipattu.", "Poistettu: %1", 
"Syötetty %1, päivitetty %2, poistettu %3, missä %4 epäonnistui ja %5 skipattiin %6 tiedostosta - %7 sekuntia - %8 merkitty poistettavaksi", "Valmis", "Sulje", "Mikään ei vastannut: %1", "kPlaylist Kirjautuminen", "Albumilista artistille: %1", "Pikavalinta: %1", "Ei valittuina mitään. Soittolistaa ei päivitetty", "Soittolista päivitetty!", "Takaisin", "Soittolista lisätty!", 
"Muista päivittää sivu.", "tunnus", "salasana:", "Huomautus! Tämä ei ole julkinen sivu. Kaikki teot kirjataan ylös", "Kirjaudu", "SSL vaaditaan kirjautumiseen.", "Soita", "Poista", "Jaettu:", "Tallenna", "Hallitse soittolistaa: '%1' - %2 nimet", 
"Muokkain", "Selain", "Valitse", "Järj.", "Tila", "Info", "Poista", "Nimi", "Yhteensä:", "Virhe", "Toiminto valitussa:", 
"Järjestys:", "muokkaa soittolistaa", "Poista tämä tulos", "lisää soittolista", "Nimi:", "Luo", "Soita", "Tiedosto", "Albumi", "Kaikki", "Valitut", 
"lisää", "soita", "muokkaa", "uusi", "Valitse:", "Hallinta:", "Soittolista", "Pikavalinta numero", "Keyteqin tuote:", "(tarkista päivityksien varalta)", "Kotisivu", 
"ainoastaan id3", "albumi", "biisi", "artisti", "Albumit artistin mukaan", "katso", "Jaetut soittolistat", "Käyttäjät", "Ylläpito", "Mitä uutta", "Suosituimmat", 
"Kirjaudu ulos", "Asetukset", "Tarkasta", "Oma", "muokkaa käyttäjää", "uusi käyttäjä", "Kokonimi", "Kirjaudu", "Vaihda salasana?", "Salasana", "Kommentti", 
"Taso", "On", "Off", "Poista käyttäjä", "Kirjaa ulos käyttäjä", "Päivitä", "Uusi käyttäjä", "poista", "kirjaa ulos", "Käytä EXT3MU-toimintoa?", "Näytä kuinka monta tulosta (suosittu/uusi)", 
"Maksimi haku tulokset", "Resetoi", "Avaa hakemisto", "Mene hakemistoon: %1", "Imuroi", "Avaa yläkansio", "Mene päähakemistoon", "Tarkista päivityksien varalta", "käyttäjät", "Kieli", "asetukset", 
"Bannattu", "Shuffle", "Asetukset", "Perushakemisto", "Streamin lähde", "Oletuskieli", "Windows systeemi", "Vaadi HTTPS (Salattu yhteys)", "Salli etsiminen", "Salli imurointi", "Istunto päättynyt", 
"Ilmoita epäonnistuneet kirjautumisyritykset", "Hetki. Haen tiedostolistaa", "Soittolistaa ei voitu lisätä", "Ylläpitäjä", "Kirjaudu HTTPS:llä vaihtaaksesi", "Streaming moottori päälle", "Nimi", "Artisti", "Albumi", "Kommentti", "Vuosi", 
"Raidan numero", "Tyyppi", "ei asetettu", "Maksimi imurointinopeus (kbps)", "Käyttäjä", "%1 minuuttia - %2 biisiä", "%1 kilobittiä %2 minuuttia", "Tyyppilista: %1", "Mene", " %1d %2h %3m soittoaika %4 tiedostoa %5 mt", "Ei soitettavia fileitä", 
"Salasana vaihdettu!", "Rekisteröi", "Tee valintasi", "Mikä on päivitys?", "Ohje painamalla tästä", "Käytä ulkoisia kuvia?", "Ulkoisten kuvien polku", "Nykyinen salasana", "Nykyinen salasana ei natsaa!", "Valitse pakkaaja", "Pakkausta ei pystytty tekemään", 
"Todennäköinen kopio: %1 - %2", "Haluatko varmasti poistaa soittolistan?", "Aakkosellinen", "Shuffle", "Järjestä", "Alkuperäinen", "Käytä javascriptiä", "Haluatko varmasti posistaa tämän käyttäjän?", "Näytä historia", "historia", "Riviä", 
"Ulkopuolinen CSS tiedosto", "Poista tuplat", "OK", "VIRHE", "Stream", "(näytä tyyppinä)", "tiedostot", "albumit", "%1d %2h %3m %4s ", "Yleistä", "Muokkaa", 
"Tiedostonkäsittely", "Klikkaa ? ohjeen näyttämiseksi.", "Automaattinen tietokanta-synkronisaation", "Lähetä tiedostopääte", "Salli kirjautumattomat streamit", "Sisällytä otsikot", "Ulkopuolinen javascript", "Kotisivu", "Näytä \'Keyteq toi sinulle\'-kohdan", "Näytä päivitä kohta", "Näytä statistiikka", 
"Kirjoita ID3v2 streamiin", "Salli käyttäjien rekisteröinti", "Tiedostotyypit", "Kyllä", "Ei", "Pääte", "MIME", "Sisällytä M3U-tiedostoon", "muokkaa tyyppiä", "Varmasti?");

$klang[8]  = array("Danish", "ISO-8859-1", "Dansk", "Hvad er hot?", "Hvad er nyt?", "Søg", "(kun %1 vist)", "sec", "Søgeresultater: %1", "fundet", "Ingen.", 
"opdater søgedatabase-indstillinger", "Slet ubrugelige albums?", "Genopbyg ID3?", 
"Fejlsøgning", "Opdater", "Annuller", "opdater søgedatabase", "Fundet % fil(er).", "Kunne ikke bestemme filtypen på: %1", "Installeret: %1 - Opdateret: %2, scannet: ", "Scan:", 
"Fejlede - forespørgsel: %1", "Kunne ikke læse: %1", "Fjernet: %1", 
"Der er indsat %1, opdateret %2, slettet %3, hvor %4 fejlede og %5 blev sprunget over, gennem %6 filer - %7 sec - %8 markeret for sletning.", "Færdig", "Luk", 
"Kunne ikke finde filer her: %1", "kPlaylist login", "Albumliste for kunstner: %1", "Hurtigvælg %1", "Ingen numre valgt. Playlist ikke opdateret.", "Playlist opdateret!", "Tilbage", "Playlist tilføjet!", 
"Husk at genindlæse siden.", "brugernavn:", "adgangskode:", "Bemærk! Dette er en privat webside. Alt logges.", "Log på", "SSL er krævet for at logge på.", "Afspil", "Slet", "Delt:", "Gem", "Kontroller playlist: %1 - %2 titler", 
"Redigeringsvindue", "Fremviser", "Vælg", "Sek", "Status", "Info", "Slet", "Navn", "Total:", "Fejl", "Handling på valgte:", 
"Sekvens:", "rediger playlist", "Slet nummer", "tilføj playlist", "Navn:", "Opret", "Afspil:", "Fil", "Album", "Alle", "Valgte", 
"tilføj", "afspil", "rediger", "ny", "Vælg:", "Afspilningskontrol:", "Playlist:", "Numerisk hurtigvalg", "Keyteq giver dig:", "(tjek for update)", "Webside", 
"Kun ID3", "album", "titel", "kunstner", "Hurtigvælg album fra kunstner", "vis", "Delte playlists", "Brugere", "Administrator", "Hvad er nyt", "Hvad er hot", 
"Log ud", "Indstillinger", "Tjek", "Min konto", "rediger bruger", "ny bruger", "Fulde navn", "Brugernavn", "Ændre adgangskode?", "Adgangskode", "Kommentar", 
"Adgang", "På / On", "Af / Off", "Slet bruger", "Log bruger ud", "Opdater", "Ny bruger", "slet", "logud", "Brug EXTM3U?", "Vis rækker (hotte/nye)", 
"Max. antal søgerækker", "Reset", "Åbn mappe", "Gå til mappe: %1", "Download", "Et trin op", "Til rodmappe", "Tjek efter updates", "brugere", "Sprog", "indstillinger", 
"Afspærret", "Shuffle / Random", "Indstillinger", "Basemappe", "Stream-lokation", "Standardsprog", "Windows-system?", "Kræver HTTPS", "Tillad søgning", "Tillad download", "Sessionstimeout", 
"Rapporter fejlede logins", "Vent - henter filliste", "Playlist kunne ikke tilføjes", "Admin", "Log ind via HTTPS for at ændre!", "Aktiver streaming", "Titel", "Kunstner", "Album", "Kommentar", "År", 
"Nummer", "Genre", "ikke sat", "Max. download rate (kbps)", "Bruger", "%1 minutter - %2 titler", "%1 kbit %2 minutter", "Genreliste: %1", "Gå", "Spilletid: %1d %2h %3m - %4 filer %5 mb", "Intet relevant her.", 
"Kodeordet er ændret", "", "Foretag venligst en markering", "Hvad er en opdatering?", "Klik her for hjælp", "Brug eksterne billeder", "Sti til eksterne billeder", "Nuværende kodeord", "", "", "", "To ens filer er muligvis fundet: %1 - %2", "Vil du virkelig slette playlisten?", "Alfabetisk", "Tilfældig", "Sorter", "Original", "Brug javascript", 
"Er du sikker på, du vil slette denne bruger?", "Vis historie", "Historie", "Rækker", "Ekstern CSS-fil");

$klang[9] = array("Russian","windows-1251","&#1056;&#1091;&#1089;&#1089;&#1082;&#1080;&#1081;","&#1055;&#1086;&#1087;&#1091;&#1083;&#1103;&#1088;&#1085;&#1099;&#1077;","&#1053;&#1086;&#1074;&#1099;&#1077;","&#1055;&#1086;&#1080;&#1089;&#1082;","(&#1090;&#1086;&#1083;&#1100;&#1082;&#1086; %1 &#1087;&#1086;&#1082;&#1072;&#1079;&#1072;&#1085;)","&#1089;&#1077;&#1082;.","&#1056;&#1077;&#1079;&#1091;&#1083;&#1100;&#1090;&#1072;&#1090; &#1087;&#1086;&#1080;&#1089;&#1082;&#1072;: \"%1\"","&#1085;&#1072;&#1081;&#1076;&#1077;&#1085;&#1086;","&#1053;&#1080; &#1086;&#1076;&#1080;&#1085;.","&#1086;&#1073;&#1085;&#1086;&#1074;&#1080;&#1090;&#1100; &#1085;&#1072;&#1089;&#1090;&#1088;&#1086;&#1081;&#1082;&#1080; &#1087;&#1086;&#1080;&#1089;&#1082;&#1072; &#1073;&#1072;&#1079;&#1099; &#1076;&#1072;&#1085;&#1085;&#1099;&#1093;","&#1059;&#1076;&#1072;&#1083;&#1080;&#1090;&#1100; &#1085;&#1077;&#1080;&#1089;&#1087;&#1086;&#1083;&#1100;&#1079;&#1091;&#1077;&#1084;&#1099;&#1077; &#1079;&#1072;&#1087;&#1080;&#1089;&#1080; &#1074; &#1073;&#1072;&#1079;&#1077;?","&#1055;&#1077;&#1088;&#1077;&#1089;&#1086;&#1079;&#1076;&#1072;&#1090;&#1100; ID3?","&#1056;&#1077;&#1078;&#1080;&#1084; &#1086;&#1090;&#1083;&#1072;&#1076;&#1082;&#1080;?","&#1054;&#1073;&#1085;&#1086;&#1074;&#1080;&#1090;&#1100; &#1073;&#1072;&#1079;&#1091;","&#1054;&#1090;&#1084;&#1077;&#1085;&#1072;","O&#1073;&#1085;&#1086;&#1074;&#1080;&#1090;&#1100; &#1073;&#1072;&#1079;&#1091; &#1076;&#1072;&#1085;&#1085;&#1099;&#1093; &#1087;&#1086;&#1080;&#1089;&#1082;&#1072;","&#1053;&#1072;&#1081;&#1076;&#1077;&#1085;&#1086; %1 &#1092;&#1072;&#1081;&#1083;(&#1086;&#1074;).","&#1053;&#1077; &#1084;&#1086;&#1075;&#1091; &#1086;&#1087;&#1088;&#1077;&#1076;&#1077;&#1083;&#1080;&#1090;&#1100; &#1101;&#1090;&#1086;&#1090; &#1092;&#1072;&#1081;&#1083;: %1, &#1087;&#1088;&#1086;&#1087;&#1091;&#1089;&#1082;&#1072;&#1102;.","&#1044;&#1086;&#1073;&#1072;&#1074;&#1083;&#1077;&#1085;&#1086;: %1 - &#1054;&#1073;&#1085;&#1086;&#1074;&#1083;&#1077;&#1085;&#1086;: %2, &#1089;&#1082;&#1072;&#1085;&#1080;&#1088;&#1091;&#1077;&#1090;&#1089;&#1103;: ","&#1057;&#1082;&#1072;&#1085;&#1080;&#1088;&#1086;&#1074;&#1072;&#1090;&#1100;: ","&#1054;&#1096;&#1080;&#1073;&#1082;&#1072; - &#1079;&#1072;&#1087;&#1088;&#1086;&#1089;: %1","&#1053;&#1077; &#1084;&#1086;&#1075;&#1091; &#1087;&#1088;&#1086;&#1095;&#1080;&#1090;&#1072;&#1090;&#1100; &#1101;&#1090;&#1086;&#1090; &#1092;&#1072;&#1081;&#1083;: %1. &#1055;&#1088;&#1086;&#1087;&#1091;&#1096;&#1077;&#1085;&#1086;.","&#1059;&#1076;&#1072;&#1083;&#1077;&#1085;&#1086;: %1","&#1044;&#1086;&#1073;&#1072;&#1074;&#1083;&#1077;&#1085;&#1086; %1, &#1086;&#1073;&#1085;&#1086;&#1074;&#1083;&#1077;&#1085;&#1086; %2, &#1091;&#1076;&#1072;&#1083;&#1077;&#1085;&#1086; %3, &#1080;&#1079; &#1085;&#1080;&#1093; %4 &#1086;&#1096;&#1080;&#1073;&#1082;&#1080; &#1080; %5 &#1087;&#1088;&#1086;&#1087;&#1091;&#1097;&#1077;&#1085;&#1086;. &#1042;&#1089;&#1077;&#1075;&#1086; %6 &#1092;&#1072;&#1081;&#1083;&#1086;&#1074; - %7 &#1089;&#1077;&#1082;. - %8 &#1086;&#1090;&#1084;&#1077;&#1095;&#1077;&#1085;&#1085;&#1099;&#1093; &#1076;&#1083;&#1103; &#1091;&#1076;&#1072;&#1083;&#1077;&#1085;&#1080;&#1103;.","&#1043;&#1086;&#1090;&#1086;&#1074;","&#1047;&#1072;&#1082;&#1088;&#1099;&#1090;&#1100;","&#1053;&#1077; &#1085;&#1072;&#1081;&#1076;&#1077;&#1085;&#1086; &#1085;&#1080; &#1086;&#1076;&#1085;&#1086;&#1075;&#1086; &#1092;&#1072;&#1081;&#1083;&#1072;: \"%1\"","kPlaylist. &#1042;&#1093;&#1086;&#1076;","&#1057;&#1087;&#1080;&#1089;&#1086;&#1082; &#1072;&#1083;&#1100;&#1073;&#1086;&#1084;&#1086;&#1074; &#1076;&#1083;&#1103; &#1080;&#1089;&#1087;&#1086;&#1083;&#1085;&#1080;&#1090;&#1077;&#1083;&#1103;: %1","&#1041;&#1099;&#1089;&#1090;&#1088;&#1099;&#1081; &#1074;&#1099;&#1073;&#1086;&#1088; %1","&#1053;&#1077; &#1074;&#1099;&#1073;&#1088;&#1072;&#1085;&#1086; &#1085;&#1080; &#1086;&#1076;&#1085;&#1086;&#1081; &#1082;&#1086;&#1084;&#1087;&#1086;&#1079;&#1080;&#1094;&#1080;&#1080;. &#1055;&#1083;&#1077;&#1081;&#1083;&#1080;&#1089;&#1090; &#1085;&#1077; &#1086;&#1073;&#1085;&#1086;&#1074;&#1083;&#1105;&#1085;.","&#1055;&#1083;&#1077;&#1081;&#1083;&#1080;&#1089;&#1090; &#1086;&#1073;&#1085;&#1086;&#1074;&#1083;&#1105;&#1085;!","&#1053;&#1072;&#1079;&#1072;&#1076;","&#1055;&#1083;&#1077;&#1081;&#1083;&#1080;&#1089;&#1090; &#1076;&#1086;&#1073;&#1072;&#1074;&#1083;&#1077;&#1085;!","&#1053;&#1077; &#1079;&#1072;&#1073;&#1091;&#1076;&#1100;&#1090;&#1077; &#1087;&#1077;&#1088;&#1077;&#1079;&#1072;&#1075;&#1088;&#1091;&#1079;&#1080;&#1090;&#1100; &#1089;&#1090;&#1088;&#1072;&#1085;&#1080;&#1094;&#1091;.","&#1051;&#1086;&#1075;&#1080;&#1085;:","&#1055;&#1072;&#1088;&#1086;&#1083;&#1100;:","&#1042;&#1089;&#1077; &#1076;&#1077;&#1081;&#1089;&#1090;&#1074;&#1080;&#1103; &#1087;&#1086;&#1083;&#1100;&#1079;&#1086;&#1074;&#1072;&#1090;&#1077;&#1083;&#1077;&#1081; &#1079;&#1072;&#1087;&#1080;&#1089;&#1099;&#1074;&#1072;&#1102;&#1090;&#1089;&#1103;.","&#1042;&#1086;&#1081;&#1090;&#1080;","&#1044;&#1083;&#1103; &#1074;&#1093;&#1086;&#1076;&#1072; &#1085;&#1077;&#1086;&#1073;&#1093;&#1086;&#1076;&#1080;&#1084; SSL","&#1055;&#1088;&#1086;&#1080;&#1075;&#1088;&#1072;&#1090;&#1100;","&#1059;&#1076;&#1072;&#1083;&#1080;&#1090;&#1100;","&#1057;&#1086;&#1074;&#1084;&#1077;&#1089;&#1090;&#1085;&#1086; &#1080;&#1089;&#1087;&#1086;&#1083;&#1100;&#1079;&#1091;&#1077;&#1084;&#1099;&#1081;: ","&#1057;&#1086;&#1093;&#1088;&#1072;&#1085;&#1080;&#1090;&#1100;","&#1059;&#1087;&#1088;&#1072;&#1074;&#1083;&#1077;&#1085;&#1080;&#1077; &#1087;&#1083;&#1077;&#1081;&#1083;&#1080;&#1089;&#1090;&#1086;&#1084;: \"%1\" - %2 &#1082;&#1086;&#1084;&#1087;&#1086;&#1079;&#1080;&#1094;&#1080;&#1080;","&#1056;&#1077;&#1076;&#1072;&#1082;&#1090;&#1080;&#1088;&#1086;&#1074;&#1072;&#1090;&#1100;","&#1055;&#1088;&#1086;&#1089;&#1084;&#1086;&#1090;&#1088;","&#1042;&#1099;&#1073;&#1088;&#1072;&#1090;&#1100;","&#1055;&#1086;&#1089;&#1083;&#1077;&#1076;.","&#1057;&#1086;&#1089;&#1086;&#1090;&#1086;&#1103;&#1085;&#1080;&#1077;","&#1048;&#1085;&#1092;&#1086;&#1088;&#1084;&#1072;&#1094;&#1080;&#1103;","&#1059;&#1076;&#1072;&#1083;.","&#1048;&#1084;&#1103;","&#1048;&#1090;&#1086;&#1075;&#1080;:","&#1054;&#1096;&#1080;&#1073;&#1082;&#1072;","&#1054;&#1087;&#1077;&#1088;&#1072;&#1094;&#1080;&#1080; &#1089; &#1074;&#1099;&#1073;&#1086;&#1088;&#1082;&#1086;&#1081;: ","&#1055;&#1086;&#1089;&#1083;&#1077;&#1076;&#1086;&#1074;&#1072;&#1090;&#1077;&#1083;&#1100;&#1085;&#1086;&#1089;&#1090;&#1100;:","&#1088;&#1077;&#1076;&#1072;&#1082;&#1090;&#1080;&#1088;&#1086;&#1074;&#1072;&#1090;&#1100; &#1087;&#1083;&#1077;&#1081;&#1083;&#1080;&#1089;&#1090;","&#1059;&#1076;&#1072;&#1083;&#1080;&#1090;&#1100; &#1101;&#1090;&#1091; &#1087;&#1086;&#1079;&#1080;&#1094;&#1080;&#1102;","&#1076;&#1086;&#1073;&#1072;&#1074;&#1080;&#1090;&#1100; &#1087;&#1083;&#1077;&#1081;&#1083;&#1080;&#1089;&#1090;","&#1048;&#1084;&#1103;:","&#1057;&#1086;&#1079;&#1076;&#1072;&#1090;&#1100;","&#1055;&#1088;&#1086;&#1080;&#1075;&#1088;&#1072;&#1090;&#1100;: ","&#1060;&#1072;&#1081;&#1083;","&#1040;&#1083;&#1100;&#1073;&#1086;&#1084;","&#1042;&#1089;&#1077;","&#1042;&#1099;&#1073;&#1088;&#1072;&#1085;&#1085;&#1099;&#1077;","&#1044;&#1086;&#1073;&#1072;&#1074;&#1080;&#1090;&#1100;","&#1055;&#1088;&#1086;&#1080;&#1075;&#1088;&#1072;&#1090;&#1100;","&#1056;&#1077;&#1076;&#1072;&#1082;&#1090;&#1080;&#1088;&#1086;&#1074;&#1072;&#1090;&#1100;","&#1053;&#1086;&#1074;&#1099;&#1081;","&#1042;&#1099;&#1073;&#1088;&#1072;&#1090;&#1100;:","&#1059;&#1087;&#1088;&#1072;&#1074;&#1083;&#1077;&#1085;&#1080;&#1077; &#1087;&#1088;&#1086;&#1080;&#1075;&#1088;&#1099;&#1074;&#1072;&#1085;&#1080;&#1077;&#1084;: ","&#1055;&#1083;&#1077;&#1081;&#1083;&#1080;&#1089;&#1090;: ","&#1041;&#1099;&#1089;&#1090;&#1088;&#1099;&#1081; &#1074;&#1099;&#1073;&#1086;&#1088; &#1087;&#1086; &#1095;&#1080;&#1089;&#1083;&#1091;","Keyteq &#1087;&#1086;&#1084;&#1086;&#1075;&#1072;&#1077;&#1090; &#1074;&#1072;&#1084;:","(&#1087;&#1088;&#1086;&#1074;&#1077;&#1088;&#1080;&#1090;&#1100; &#1086;&#1073;&#1085;&#1086;&#1074;&#1083;&#1077;&#1085;&#1080;&#1103;)","&#1044;&#1086;&#1084;&#1072;&#1096;&#1085;&#1103;&#1103; &#1089;&#1090;&#1088;&#1072;&#1085;&#1080;&#1094;&#1072;","&#1090;&#1086;&#1083;&#1100;&#1082;&#1086; &#1074;  id3","&#1072;&#1083;&#1100;&#1073;&#1086;&#1084;","&#1085;&#1072;&#1079;&#1074;&#1072;&#1085;&#1080;&#1077;","&#1080;&#1089;&#1087;&#1086;&#1083;&#1085;&#1080;&#1090;&#1077;&#1083;&#1100;","&#1040;&#1083;&#1100;&#1073;&#1086;&#1084;&#1099; &#1087;&#1086; &#1072;&#1083;&#1092;&#1072;&#1074;&#1080;&#1090;&#1091;","&#1087;&#1088;&#1086;&#1089;&#1084;&#1086;&#1090;&#1088;","&#1054;&#1073;&#1097;&#1080;&#1077; &#1087;&#1083;&#1077;&#1081;&#1083;&#1080;&#1089;&#1090;&#1099;","&#1055;&#1086;&#1083;&#1100;&#1079;&#1086;&#1074;&#1072;&#1090;&#1077;&#1083;&#1080;","&#1040;&#1076;&#1084;&#1080;&#1085;&#1080;&#1089;&#1090;&#1088;&#1080;&#1088;&#1086;&#1074;&#1072;&#1085;&#1080;&#1077;","&#1053;&#1086;&#1074;&#1099;&#1077;","&#1055;&#1086;&#1087;&#1091;&#1083;&#1103;&#1088;&#1085;&#1099;&#1077;","&#1042;&#1099;&#1093;&#1086;&#1076;","&#1053;&#1072;&#1089;&#1090;&#1088;&#1086;&#1081;&#1082;&#1080;","&#1055;&#1086;&#1082;&#1072;&#1079;&#1072;&#1090;&#1100;","&#1052;&#1086;&#1080; &#1085;&#1072;&#1089;&#1090;&#1088;&#1086;&#1081;&#1082;&#1080;","&#1056;&#1077;&#1076;&#1072;&#1082;&#1090;&#1080;&#1088;&#1086;&#1074;&#1072;&#1090;&#1100; &#1087;&#1086;&#1083;&#1100;&#1079;&#1086;&#1074;&#1072;&#1090;&#1077;&#1083;&#1077;&#1081;","&#1053;&#1086;&#1074;&#1099;&#1081; &#1087;&#1086;&#1083;&#1100;&#1079;&#1086;&#1074;&#1072;&#1090;&#1077;&#1083;&#1100;","&#1055;&#1086;&#1083;&#1085;&#1086;&#1077; &#1080;&#1084;&#1103;","&#1051;&#1086;&#1075;&#1080;&#1085;","&#1048;&#1079;&#1084;&#1077;&#1085;&#1080;&#1090;&#1100; &#1087;&#1072;&#1088;&#1086;&#1083;&#1100;?","&#1055;&#1072;&#1088;&#1086;&#1083;&#1100;","&#1050;&#1086;&#1084;&#1084;&#1077;&#1085;&#1090;&#1072;&#1088;&#1080;&#1081;","&#1059;&#1088;&#1086;&#1074;&#1077;&#1085;&#1100; &#1076;&#1086;&#1089;&#1090;&#1091;&#1087;&#1072;","&#1042;&#1082;&#1083;","&#1042;&#1099;&#1082;&#1083;","&#1059;&#1076;&#1072;&#1083;&#1080;&#1090;&#1100; &#1087;&#1086;&#1083;&#1100;&#1079;&#1086;&#1074;&#1072;&#1090;&#1077;&#1083;&#1103;","&#1054;&#1090;&#1082;&#1083;&#1102;&#1095;&#1080;&#1090;&#1100; &#1087;&#1086;&#1083;&#1100;&#1079;&#1086;&#1074;&#1072;&#1090;&#1077;&#1083;&#1103;","&#1054;&#1073;&#1085;&#1086;&#1074;&#1080;&#1090;&#1100;","&#1053;&#1086;&#1074;&#1099;&#1081; &#1087;&#1086;&#1083;&#1100;&#1079;&#1086;&#1074;&#1072;&#1090;&#1077;&#1083;&#1100;","&#1059;&#1076;&#1072;&#1083;.","&#1042;&#1099;&#1093;&#1086;&#1076;","&#1048;&#1089;&#1087;&#1086;&#1083;&#1100;&#1079;&#1086;&#1074;&#1072;&#1090;&#1100; EXTM3U?","&#1050;&#1086;&#1083;&#1080;&#1095;&#1077;&#1089;&#1090;&#1074;&#1086; &#1074;&#1099;&#1074;&#1086;&#1076;&#1080;&#1084;&#1099;&#1093; &#1089;&#1090;&#1088;&#1086;&#1082; (&#1087;&#1086;&#1087;&#1091;&#1083;&#1103;&#1088;&#1085;&#1099;&#1077;/&#1085;&#1086;&#1074;&#1099;&#1077;)","&#1050;&#1086;&#1083;&#1080;&#1095;&#1077;&#1089;&#1090;&#1074;&#1086; &#1074;&#1099;&#1074;&#1086;&#1076;&#1080;&#1084;&#1099;&#1093; &#1089;&#1090;&#1088;&#1086;&#1082; &#1087;&#1088;&#1080; &#1087;&#1086;&#1080;&#1089;&#1082;&#1077;","&#1057;&#1073;&#1088;&#1086;&#1089;","&#1042;&#1086;&#1081;&#1090;&#1080; &#1074; &#1087;&#1072;&#1087;&#1082;&#1091;","&#1055;&#1077;&#1088;&#1077;&#1081;&#1090;&#1080; &#1074; &#1087;&#1072;&#1087;&#1082;&#1091;: %1","&#1057;&#1082;&#1072;&#1095;&#1072;&#1090;&#1100;","&#1042;&#1074;&#1077;&#1088;&#1093; &#1085;&#1072; &#1086;&#1076;&#1080;&#1085; &#1091;&#1088;&#1086;&#1074;&#1077;&#1085;&#1100;","&#1042; &#1085;&#1072;&#1095;&#1072;&#1083;&#1086;","&#1055;&#1088;&#1086;&#1074;&#1077;&#1088;&#1080;&#1090;&#1100; &#1086;&#1073;&#1085;&#1086;&#1074;&#1083;&#1077;&#1085;&#1080;&#1103;","&#1087;&#1086;&#1083;&#1100;&#1079;&#1086;&#1074;&#1072;&#1090;&#1077;&#1083;&#1077;&#1081;","&#1071;&#1079;&#1099;&#1082;","&#1085;&#1072;&#1089;&#1090;&#1088;&#1086;&#1081;&#1082;&#1080;","&#1047;&#1072;&#1075;&#1088;&#1091;&#1078;&#1077;&#1085;&#1086;","&#1057;&#1083;&#1091;&#1095;&#1072;&#1081;&#1085;&#1099;&#1081; &#1087;&#1086;&#1088;&#1103;&#1076;&#1086;&#1082;:","&#1053;&#1072;&#1089;&#1090;&#1088;&#1086;&#1081;&#1082;&#1080;","&#1055;&#1091;&#1090;&#1100; &#1082; &#1084;&#1091;&#1079;&#1099;&#1082;&#1072;&#1083;&#1100;&#1085;&#1086;&#1084;&#1091; &#1072;&#1088;&#1093;&#1080;&#1074;&#1091;","&#1055;&#1091;&#1090;&#1100; &#1076;&#1083;&#1103; &#1087;&#1086;&#1090;&#1086;&#1082;&#1086;&#1074;&#1086;&#1075;&#1086; &#1074;&#1077;&#1097;&#1072;&#1085;&#1080;&#1103;","&#1071;&#1079;&#1099;&#1082; &#1087;&#1086; &#1091;&#1084;&#1086;&#1083;&#1095;&#1072;&#1085;&#1080;&#1102;","&#1056;&#1072;&#1073;&#1086;&#1090;&#1072; &#1087;&#1086;&#1076; Windows","&#1053;&#1077;&#1086;&#1073;&#1093;&#1086;&#1076;&#1080;&#1084; HTTPS","&#1056;&#1072;&#1079;&#1088;&#1077;&#1096;&#1080;&#1090;&#1100; &#1087;&#1088;&#1086;&#1084;&#1072;&#1090;&#1099;&#1074;&#1072;&#1090;&#1100;","&#1056;&#1072;&#1079;&#1088;&#1077;&#1096;&#1080;&#1090;&#1100; &#1089;&#1082;&#1072;&#1095;&#1080;&#1074;&#1072;&#1090;&#1100;","&#1058;&#1072;&#1081;&#1084;&#1072;&#1091;&#1090; &#1076;&#1083;&#1103; &#1089;&#1077;&#1089;&#1089;&#1080;&#1080;","&#1057;&#1086;&#1086;&#1073;&#1097;&#1072;&#1090;&#1100; &#1086; &#1085;&#1077;&#1091;&#1076;&#1072;&#1095;&#1085;&#1099;&#1093; &#1087;&#1086;&#1087;&#1099;&#1090;&#1082;&#1072;&#1093; &#1074;&#1093;&#1086;&#1076;&#1072;","&#1055;&#1086;&#1076;&#1086;&#1078;&#1076;&#1080;&#1090;&#1077; - &#1088;&#1072;&#1079;&#1073;&#1080;&#1088;&#1072;&#1102;&#1089;&#1100; &#1089;&#1086; &#1089;&#1087;&#1080;&#1089;&#1082;&#1086;&#1084; &#1092;&#1072;&#1081;&#1083;&#1086;&#1074;","&#1055;&#1083;&#1077;&#1081;&#1083;&#1080;&#1089;&#1090; &#1085;&#1077; &#1084;&#1086;&#1078;&#1077;&#1090; &#1073;&#1099;&#1090;&#1100; &#1076;&#1086;&#1073;&#1072;&#1074;&#1083;&#1077;&#1085;!","&#1040;&#1076;&#1084;&#1080;&#1085;&#1080;&#1089;&#1090;&#1088;&#1080;&#1088;&#1086;&#1074;&#1072;&#1085;&#1080;&#1077;","&#1042;&#1093;&#1086;&#1076; &#1090;&#1086;&#1083;&#1100;&#1082;&#1086; &#1095;&#1077;&#1088;&#1077;&#1079; HTTPS","&#1042;&#1082;&#1083;&#1102;&#1095;&#1080;&#1090;&#1100; &#1074;&#1089;&#1090;&#1088;&#1086;&#1077;&#1085;&#1085;&#1091;&#1102; &#1089;&#1080;&#1089;&#1090;&#1077;&#1084;&#1091; &#1087;&#1086;&#1090;&#1086;&#1082;&#1086;&#1074;&#1086;&#1075;&#1086; &#1074;&#1077;&#1097;&#1072;&#1085;&#1080;&#1103;","&#1053;&#1072;&#1079;&#1074;&#1072;&#1085;&#1080;&#1077;","&#1048;&#1089;&#1087;&#1086;&#1083;&#1085;&#1080;&#1090;&#1077;&#1083;&#1100;","&#1040;&#1083;&#1100;&#1073;&#1086;&#1084;","&#1050;&#1086;&#1084;&#1084;&#1077;&#1085;&#1090;&#1072;&#1088;&#1080;&#1081;","&#1043;&#1086;&#1076;","&#1058;&#1088;&#1077;&#1082;","&#1057;&#1090;&#1080;&#1083;&#1100;","&#1085;&#1077; &#1091;&#1089;&#1090;&#1072;&#1085;&#1086;&#1074;&#1083;&#1077;&#1085;","&#1052;&#1072;&#1082;&#1089;&#1080;&#1084;&#1072;&#1083;&#1100;&#1085;&#1072;&#1103; &#1089;&#1082;&#1086;&#1088;&#1086;&#1089;&#1090;&#1100; &#1089;&#1082;&#1072;&#1095;&#1080;&#1074;&#1072;&#1085;&#1080;&#1103; (kbps)","&#1055;&#1086;&#1083;&#1100;&#1079;&#1086;&#1074;&#1072;&#1090;&#1077;&#1083;&#1100;","%1 &#1084;&#1080;&#1085;. - %2 &#1090;&#1088;&#1077;&#1082;&#1086;&#1074;","%1 kbit %2 &#1084;&#1080;&#1085;.","&#1057;&#1087;&#1080;&#1089;&#1086;&#1082; &#1089;&#1090;&#1080;&#1083;&#1077;&#1081;: %1","&#1042;&#1099;&#1087;&#1086;&#1083;&#1085;&#1080;&#1090;&#1100;","&#1042; &#1073;&#1072;&#1079;&#1077; %4 &#1092;&#1072;&#1081;&#1083;&#1086;&#1074; &#1086;&#1073;&#1097;&#1080;&#1084; &#1086;&#1073;&#1098;&#1077;&#1084;&#1086;&#1084; %5 &#1052;&#1073;.<br>&nbsp;&#1054;&#1073;&#1097;&#1077;&#1077; &#1074;&#1088;&#1077;&#1084;&#1103; &#1087;&#1088;&#1086;&#1089;&#1083;&#1091;&#1096;&#1080;&#1074;&#1072;&#1085;&#1080;&#1103;: %1 &#1076;&#1085;&#1077;&#1081; %2 &#1095;&#1072;&#1089;&#1086;&#1074; %3 &#1084;&#1080;&#1085;&#1091;&#1090;.","&#1052;&#1091;&#1079;&#1099;&#1082;&#1072;&#1083;&#1100;&#1085;&#1099;&#1077; &#1092;&#1072;&#1081;&#1083;&#1099; &#1086;&#1090;&#1089;&#1091;&#1090;&#1089;&#1090;&#1074;&#1091;&#1102;&#1090;.","&#1055;&#1072;&#1088;&#1086;&#1083;&#1100; &#1080;&#1079;&#1084;&#1077;&#1085;&#1077;&#1085;","&#1056;&#1077;&#1075;&#1080;&#1089;&#1090;&#1088;&#1072;&#1094;&#1080;&#1103;","&#1042;&#1099;&#1073;&#1077;&#1088;&#1080;&#1090;&#1077; &#1093;&#1086;&#1090;&#1103; &#1073;&#1099; &#1086;&#1076;&#1080;&#1085; &#1092;&#1072;&#1081;&#1083;","&#1063;&#1090;&#1086; &#1090;&#1072;&#1082;&#1086;&#1077; &#1086;&#1073;&#1085;&#1086;&#1074;&#1083;&#1077;&#1085;&#1080;&#1077;?","&#1065;&#1105;&#1083;&#1082;&#1085;&#1080;&#1090;&#1077; &#1079;&#1076;&#1077;&#1089;&#1100; &#1076;&#1083;&#1103; &#1087;&#1086;&#1076;&#1089;&#1082;&#1072;&#1079;&#1082;&#1080;","&#1048;&#1089;&#1087;&#1086;&#1083;&#1100;&#1079;&#1086;&#1074;&#1072;&#1090;&#1100; &#1074;&#1085;&#1077;&#1096;&#1085;&#1080;&#1077; &#1082;&#1072;&#1088;&#1090;&#1080;&#1085;&#1082;&#1080;?","&#1055;&#1091;&#1090;&#1100; &#1082; &#1082;&#1072;&#1088;&#1090;&#1080;&#1085;&#1082;&#1072;&#1084;","&#1058;&#1077;&#1082;&#1091;&#1097;&#1080;&#1081; &#1087;&#1072;&#1088;&#1086;&#1083;&#1100;","&#1042;&#1074;&#1077;&#1076;&#1105;&#1085;&#1085;&#1099;&#1081; &#1087;&#1072;&#1088;&#1086;&#1083;&#1100; &#1085;&#1077; &#1089;&#1086;&#1074;&#1087;&#1072;&#1076;&#1072;&#1077;&#1090; &#1089; &#1090;&#1077;&#1082;&#1091;&#1097;&#1080;&#1084;!","&#1048;&#1089;&#1087;&#1086;&#1083;&#1100;&#1079;&#1086;&#1074;&#1072;&#1090;&#1100; &#1072;&#1088;&#1093;&#1080;&#1074;&#1072;&#1090;&#1086;&#1088;","&#1053;&#1077;&#1074;&#1086;&#1079;&#1084;&#1086;&#1078;&#1085;&#1086; &#1089;&#1086;&#1079;&#1076;&#1072;&#1090;&#1100; &#1072;&#1088;&#1093;&#1080;&#1074;","&#1053;&#1072;&#1081;&#1076;&#1077;&#1085;&#1099; &#1074;&#1086;&#1079;&#1084;&#1086;&#1078;&#1085;&#1099;&#1077; &#1076;&#1091;&#1073;&#1083;&#1080;&#1082;&#1072;&#1090;&#1099; &#1092;&#1072;&#1081;&#1083;&#1086;&#1074;:  \"%1\" \"%2\"","&#1042;&#1099; &#1076;&#1077;&#1081;&#1089;&#1090;&#1074;&#1080;&#1090;&#1077;&#1083;&#1100;&#1085;&#1086; &#1093;&#1086;&#1090;&#1080;&#1090;&#1077; &#1091;&#1076;&#1072;&#1083;&#1080;&#1090;&#1100; &#1087;&#1083;&#1077;&#1081;&#1083;&#1080;&#1089;&#1090;?","&#1055;&#1086; &#1072;&#1083;&#1092;&#1072;&#1074;&#1080;&#1090;&#1091;","&#1057;&#1083;&#1091;&#1095;&#1072;&#1081;&#1085;&#1086;","&#1057;&#1086;&#1088;&#1090;&#1080;&#1088;&#1086;&#1074;&#1072;&#1090;&#1100;","&#1050;&#1072;&#1082; &#1074; &#1086;&#1088;&#1080;&#1075;&#1080;&#1085;&#1072;&#1083;&#1077;","&#1048;&#1089;&#1087;&#1086;&#1083;&#1100;&#1079;&#1086;&#1074;&#1072;&#1090;&#1100; JavaScript","&#1042;&#1099; &#1076;&#1077;&#1081;&#1089;&#1090;&#1074;&#1080;&#1090;&#1077;&#1083;&#1100;&#1085;&#1086; &#1093;&#1086;&#1090;&#1080;&#1090;&#1077; &#1091;&#1076;&#1072;&#1083;&#1080;&#1090;&#1100; &#1101;&#1090;&#1086;&#1075;&#1086; &#1087;&#1086;&#1083;&#1100;&#1079;&#1086;&#1074;&#1072;&#1090;&#1077;&#1083;&#1103;?","&#1055;&#1088;&#1086;&#1089;&#1084;&#1086;&#1090;&#1088; &#1080;&#1089;&#1090;&#1086;&#1088;&#1080;&#1080;","&#1048;&#1089;&#1090;&#1086;&#1088;&#1080;&#1103;","&#1057;&#1090;&#1088;&#1086;&#1082;&#1080;"," &#1060;&#1072;&#1081;&#1083; CSS","&#1059;&#1076;&#1072;&#1083;&#1080;&#1090;&#1100; &#1076;&#1091;&#1073;&#1083;&#1080;&#1082;&#1072;&#1090;&#1099;","OK","ERR","&#1055;&#1086;&#1090;&#1086;&#1082;","(&#1087;&#1086;&#1082;&#1072;&#1079;&#1072;&#1090;&#1100; &#1082;&#1072;&#1082;)","&#1092;&#1072;&#1081;&#1083;&#1099;","&#1072;&#1083;&#1100;&#1073;&#1086;&#1084;&#1099;","%1 &#1076;&#1085;&#1077;&#1081; %2 &#1095;&#1072;&#1089;&#1086;&#1074; %3 &#1084;&#1080;&#1085;&#1091;&#1090; %4 &#1089;&#1077;&#1082;&#1091;&#1085;&#1076;","&#1054;&#1073;&#1097;&#1080;&#1077;","&#1048;&#1085;&#1090;&#1077;&#1088;&#1092;&#1077;&#1081;&#1089;","&#1056;&#1072;&#1073;&#1086;&#1090;&#1072; &#1089; &#1092;&#1072;&#1081;&#1083;&#1072;&#1084;&#1080;","&#1065;&#1105;&#1083;&#1082;&#1085;&#1080;&#1090;&#1077; &#1085;&#1072; ? &#1076;&#1083;&#1103; &#1087;&#1086;&#1076;&#1089;&#1082;&#1072;&#1079;&#1082;&#1080;.","&#1040;&#1074;&#1090;&#1086;&#1084;&#1072;&#1090;&#1080;&#1095;&#1077;&#1089;&#1082;&#1086;&#1077; &#1086;&#1073;&#1085;&#1086;&#1074;&#1083;&#1077;&#1085;&#1080;&#1077; &#1073;&#1072;&#1079;&#1099; &#1076;&#1072;&#1085;&#1085;&#1099;&#1093;","&#1054;&#1090;&#1089;&#1099;&#1083;&#1072;&#1090;&#1100; &#1088;&#1072;&#1089;&#1096;&#1080;&#1088;&#1077;&#1085;&#1080;&#1077; &#1092;&#1072;&#1081;&#1083;&#1072; &#1087;&#1088;&#1080; &#1087;&#1077;&#1088;&#1077;&#1076;&#1072;&#1095;&#1077;","&#1056;&#1072;&#1079;&#1088;&#1077;&#1096;&#1080;&#1090;&#1100; &#1087;&#1077;&#1088;&#1077;&#1076;&#1072;&#1095;&#1091; &#1087;&#1086;&#1090;&#1086;&#1082;&#1086;&#1074;&#1086;&#1075;&#1086; &#1079;&#1074;&#1091;&#1082;&#1072; &#1073;&#1077;&#1079; &#1072;&#1074;&#1090;&#1086;&#1088;&#1080;&#1079;&#1072;&#1094;&#1080;&#1080;","&#1042;&#1082;&#1083;&#1102;&#1095;&#1080;&#1090;&#1100; &#1079;&#1072;&#1075;&#1086;&#1083;&#1086;&#1074;&#1082;&#1080;","&#1042;&#1085;&#1077;&#1096;&#1085;&#1080;&#1081; JavaScript","&#1040;&#1076;&#1088;&#1077;&#1089; &#1074;&#1072;&#1096;&#1077;&#1075;&#1086; &#1089;&#1072;&#1081;&#1090;&#1072;","&#1055;&#1086;&#1082;&#1072;&#1079;&#1099;&#1074;&#1072;&#1090;&#1100; &#1092;&#1088;&#1072;&#1079;&#1091; \"Keyteq &#1087;&#1086;&#1084;&#1086;&#1075;&#1072;&#1077;&#1090; &#1074;&#1072;&#1084;\"","&#1055;&#1086;&#1082;&#1072;&#1079;&#1099;&#1074;&#1072;&#1090;&#1100; &#1092;&#1088;&#1072;&#1079;&#1091; \"&#1055;&#1088;&#1086;&#1074;&#1077;&#1088;&#1080;&#1090;&#1100; &#1086;&#1073;&#1085;&#1086;&#1074;&#1083;&#1077;&#1085;&#1080;&#1103;\"","&#1055;&#1086;&#1082;&#1072;&#1079;&#1099;&#1074;&#1072;&#1090;&#1100; &#1089;&#1090;&#1072;&#1090;&#1080;&#1089;&#1090;&#1080;&#1082;&#1091;","&#1044;&#1086;&#1073;&#1072;&#1074;&#1083;&#1103;&#1090;&#1100; &#1090;&#1101;&#1075; ID3v2 &#1074; &#1087;&#1086;&#1090;&#1086;&#1082;","&#1056;&#1072;&#1079;&#1088;&#1077;&#1096;&#1080;&#1090;&#1100; &#1088;&#1077;&#1075;&#1080;&#1089;&#1090;&#1088;&#1072;&#1094;&#1080;&#1102; &#1087;&#1086;&#1083;&#1100;&#1079;&#1086;&#1074;&#1072;&#1090;&#1077;&#1083;&#1077;&#1081;","&#1058;&#1080;&#1087;&#1099; &#1092;&#1072;&#1081;&#1083;&#1086;&#1074;","&#1044;&#1072;","&#1053;&#1077;&#1090;","&#1056;&#1072;&#1089;&#1096;&#1080;&#1088;&#1077;&#1085;&#1080;&#1077;","MIME","&#1042;&#1082;&#1083;&#1102;&#1095;&#1080;&#1090;&#1100; &#1074; M3U","&#1056;&#1077;&#1076;&#1072;&#1082;&#1090;&#1080;&#1088;&#1086;&#1074;&#1072;&#1090;&#1100; &#1090;&#1080;&#1087; &#1092;&#1072;&#1081;&#1083;&#1072;","&#1059;&#1074;&#1077;&#1088;&#1077;&#1085;&#1099;?","&#1058;&#1086;&#1095;&#1085;&#1072;&#1103; &#1087;&#1088;&#1086;&#1074;&#1077;&#1088;&#1082;&#1072; &#1092;&#1072;&#1081;&#1083;&#1086;&#1074;","&#1057;&#1083;&#1091;&#1095;&#1072;&#1081;&#1085;&#1072;&#1103; &#1074;&#1099;&#1073;&#1086;&#1088;&#1082;&#1072;","&#1056;&#1077;&#1078;&#1080;&#1084; &#1074;&#1099;&#1073;&#1086;&#1088;&#1082;&#1080;","&#1044;&#1086;&#1073;&#1072;&#1074;&#1080;&#1090;&#1100; &#1074; &#1087;&#1083;&#1077;&#1081;&#1083;&#1080;&#1089;&#1090;","&#1053;&#1077; &#1076;&#1086;&#1073;&#1072;&#1074;&#1083;&#1103;&#1090;&#1100;","&#1048;&#1079; &#1084;&#1086;&#1077;&#1075;&#1086; &#1080;&#1079;&#1073;&#1088;&#1072;&#1085;&#1085;&#1086;&#1075;&#1086;","&#1055;&#1086;&#1087;&#1091;&#1083;&#1103;&#1088;&#1085;&#1099;&#1077; &#1082;&#1086;&#1084;&#1087;&#1086;&#1079;&#1080;&#1094;&#1080;&#1080; &#1085;&#1077; &#1085;&#1072;&#1081;&#1076;&#1077;&#1085;&#1099;","&#1057;&#1072;&#1084;&#1099;&#1077; &#1087;&#1086;&#1087;&#1091;&#1083;&#1103;&#1088;&#1085;&#1099;&#1077;","&#1057;&#1086;&#1088;&#1090;&#1080;&#1088;&#1086;&#1074;&#1082;&#1072;","&#1056;&#1072;&#1079;&#1088;&#1077;&#1096;&#1080;&#1090;&#1100; &#1087;&#1086;&#1076;&#1076;&#1077;&#1088;&#1078;&#1082;&#1091; LAME?","&#1042;&#1099;&#1082;&#1083;&#1102;&#1095;&#1077;&#1085;&#1086;","&#1056;&#1072;&#1079;&#1088;&#1077;&#1096;&#1080;&#1090;&#1100; &#1080;&#1089;&#1087;&#1086;&#1083;&#1100;&#1079;&#1086;&#1074;&#1072;&#1085;&#1080;&#1077; LAME?");

$klang[10]  = array("Swiss German", "ISO-8859-15", "Schwiizerdütsch", "Wasch geil", "Wasch neu", "Wo isch das Züüg", "Gseesch nur äs Prozänt", "sek", "Suechergebnis: '%1'", "gfundä", "keini", "pass das datebank-suech-züüg aa", "nöd benutzte seich i de db kickä ?", "ID3 erneuerä?", 
 "Dibög-Modus?", "Update", "Abbräche", "Suech-DB update", "%1 Files gfundä", "Bin bi dem File nöd druus cho: %1. Has usglaa.", "Inschtalliert:%1 - Draa umebaschtlet: %2, abchecke:", "scän:", "Problem bi de Abfrag: %1", "Han glaub es File verhüeneret: %1. Ussglaa..", "Weggnoo: %1", 
 "inetaa: %1, umebaschtlet: %2, weggnoo: %3, %4 händ nöd gfunzt und %5 hani ussglaa; %6 dateie insgesamt - %7 sekunde - %8 hani markiert zum abtschüsse.", "Schnornig.", "Zuemachä.", "Da hätts kei Dateie: \"%1\"", "KPlaylist Login", "Albumlischte für Interpret: %1", "Churzwahl %1", "Kein Song usgwählt. Playlischte nöd aktualisiert.", "Playlischte aktualisiert.", "Zrugg", "Playlischte zuegfüegt!", "Nomal lade das züüg.", "Login:", "Passwort:", "Achtung! Dasch privat da züüg. Jede seich gitt eis uf de Deckel!", "Login", "Bruchsch SSL zum inechoo", "Abschpile", "Lösche", "Die wommer zäme händ:", "Seivä", "A de Playlischte umebaschtle: \"%1\" - %2 Titel", 
 "Editor", "Aazeiger", "Uswähle", "Nummerä", "Schtatus", "Info", "Abtschüsse", "Namä", "Zämezellt", "Schöne seich", "Das machemer mit dene wo uusgwählt sind", 
 "Reiefolg", "a de Playlischte umebaschtle", "De Iitrag useschmeisse", "Playlischte dezuetue", "Namä:", "Mache", "Abschpile:", "Datei", "Album", "Ali", "die Uusgwählte", 
 "Dezue tue", "Abschpile", "draa umebaschtle", "neu", "Uswähle:", "Abschpile:", "Playlischte:", "Churzwahl numerisch", "Keyteq präsentiert eu:", "(Suche nacheme neue versiönli)", "Houmpeitsch", "Nume id3 TägZ", "Album", "Titel", "Interpret", "Churzwahl Album nach Interpret", "Aasicht", "Playlischtene, wommer zäme händ", "Benutzer", "Admin kontrollä", "Wasch neu", "Wasch geil", "Und tschüss", "Iischtellige", "Abtschägge", "Mini", "Benutzer abändere", "Neue Benutzer", "De ganz Name", "Login", "Passwort abändere?", "Passwort", "Sänf dezue gee", 
 "Wie mächtig isch de Typ", "Aagschtellt", "Abgschtellt", "Benutzer abtschüsse", "Uuslogge", "Erneuerä", "Neue Benutzer", "Lösche", "Uuslogge", "Söli das EXTM3U züüg bruuche?", "Wivill ziile aazeige (geil/neu)", "Max. Ziile bi Suechergebnis", "Reset", "Ordner ufmache", "Gang zum Ordner: %1", "Abesuuge", "Ein Ordner ufe", "Is Grundverzeichnis", "Mal luege öbs es Update gitt", "Benutzer", "Spraach", "Opzione",  "Aaghalte", "Mischle:", "Iischtellige", "Hauptverzeichnis", "Stream location", "Standardspraach", "Es windoof-system", "bruucht HTTPS", "dörf me sueche", "dörf me suuge", "session isch abgloffe",  "säg mer, wenn eine sis PW verhängt", "momäntli, mues schnäll go d'files läse", "han die blööd playlist nöd chöne mache!", "Admin", "Login mit HTTPS zum ändere");

$klang[11]  = array("French", "ISO-8859-15", "francais", "liens \"HOT\"", "Nouveau", "rechercher", "Seulement 1% visible", "sec", "R&eacute;sultats de la recherche :'%1'", "trouv&eacute;", "aucun", "actualiser les options de la base de donn&eacute;es de recherche", "&lt;b&gt;Supprimer&lt;/b&gt; les entr&eacute;es inutiles?", "Reconstruire&lt;b&gt;ID3&lt;/b&gt;?", "Mode de d&eacute;buggage?", "Actualiser", "Annuler", 
"Actualiser la base de donn&eacute;es de recherche", "%1 fichier(s) trouv&eacute;(s)", "Ce fichier n'a pas pu &ecirc;tre d&eacute;termin&eacute;: 1% ignor&eacute;", 
" %1 - Actualiser: %2, scanner: ", "Scanner", "Echec de la recherche", "Le fichier: %1 n'a pas &eacute;t&eacute; trouv&eacute;. Abandon", "Elimin&eacute;: %1", 
"Ins&eacute;r&eacute;:%1. Actualis&eacute; %2. Elimin&eacute; %3 o&ugrave; %4 a &eacute;chou&eacute; et %5 omis dans %6 r&eacute;pertoires - %7seg - %8 marqu&eacute; pour effacement.", "Finalis&eacute;", "Fermer", "Pas de r&eacute;pertoires utilisant %1 trouv&eacute;s", "Nom d'utilisateur KPlaylist", "Liste de disque de l'artiste: %1", "Hotselect %1", "Aucune chanson s&eacute;lectionn&eacute;e. La liste n'a pas &eacute;t&eacute; actualis&eacute;e.", "Liste actualis&eacute;e avec succ&egrave;s!", "Retourner", "Liste actualis&eacute;e!", 
"Actualiser la page", "nom d'utilisateur", "mot de passe", "Attention! Ce site est protégé, toute action est enregistrée", "Nom d'utilisateur", "SSL n&eacute;cessaire pour entrer.", "Jouer", "Supprimer", "Partag&eacute;:", "Sauvegarder", "Liste de Controle: &quot;%1&quot; - %2 titres", "Editeur", "Viseur", "S&eacute;lectionner", 
"sec", "Status", "Info", "Sup", "Nom", "Totaux", "Erreur", "Action &agrave; effectuer sur la selection",
"S&eacute;quence:", "&eacute;diter la liste", "Supprimer cette entr&eacute;e", "ajouter une liste", "Nom:", "Cr&eacute;er", "Jouer:", "R&eacute;pertoire", "Disque", "Tout", "S&eacute;lectionn&eacute;s", "ajouter", "jouer", "&eacute;diter", "nouveau", "S&eacute;lectionner", "Controle:", "Liste:", "Sélection numérique", "Keyteq vous offre:",
"(rechercher des actualisations)", "Page principale", "seulement id3", "disque", "titre", "artiste", "S&eacute;lectionner un disque de l'artiste", "vue", "Listes partag&eacute;es", "Utilisateurs", 
"Controle d'administration", "Nouveau", "Populaire", "Sortir", "Options", "Controler", "mes ...", "&eacute;diter un utilisateur", "nouvel utilisateur", "Nom complet", "Nom d'utilisateur", 
"changer le mot de passe?", "Mot de passe", "Commentaires","Niveau d'acc&egrave;s", "En marche", "Eteint", "Supprimer l'utilisateur", "Sortie utilisateur", "Actualiser", "Nouvel utilisateur", "sup", "sortir", 
"Utiliser l'option de EXTM3U?", "Montrer combien de lignes (populaire/nouveau)", 
"Nombre maximum de lignes ", "Restaurer", "R&eacute;pertoire ouvert", "ouvrir le r&eacute;pertoire: %1", "D&eacute;charger", "Monter d'un niveau", "Aller au r&eacute;pertoire racine", "Chercher des actualisations", " utilisateurs ", "Langue", "options", 
"Ferm&eacute;", "Al&eacute;atoire", "Contôles", "R&eacute;pertoire base", "Localisation du courant", "Langue pr&eacute;f&eacute;r&eacute;e", "Un syst&egrave;me de Windows", "HTTPS n&eacute;cessaire", "Permettre la recherche", "Permettre les d&eacute;chargements", "Arr&ecirc;t de la session", 
"Information de tentatives de connexion &eacute;chou&eacute;es", "Garder allum&eacute; - ramener la liste des r&eacute;pertoires", "La playlist n'a pas pu &ecirc;tre ajout&eacute;e!", "Admin", "Connexion sur HTTPS &agrave; changer", "Activer le moteur de streaming", "Titre", "Artiste", "Album", "Commentaire", "Année", 
"Piste", "Genre", "non spécifié", "Taux de téléchargement Max. (kbps)", "Utilisateur", "%1 min - %2 titres", "%1 kbit %2 min", "liste de genre : %1", "Go", "Temps de lecture : %1d %2h %3m , %4 fichiers %5 Mb", "Aucune ressource correspondante", 
"Mot de passe mis à jour !", "Inscrivez vous", "Faites une selection SVP !", "Qu\'est ce que la mise à jour ?", "Clickez ici pour l\'aide", "Utiliser des images externes?", "Chemin vers les images externes", "Mot de passe actuel", "Mauvais mot de passe", "Archiveur préféré", "Larchive n\'a put être créer", 
"Doublon probable : \"%1\" \"%2\"", "Voulez-vous vraiment supprimer la playlist ?", "Alphabétique", "Au hasard", "Ordonné", "Original", "Utiliser Javascript", "Voulez vous vraiment supprimer cet utilisateur?", "Voir l\'historique", "historique", "lignes", 
"fichier CSS externe", "Supprimer les doublons", "OK", "ERREUR", "Flux", "(voir comme)", "fichiers", "albums", "%1d %2h %3m %4s", "Général", "Personaliser", 
"", "clicker sur \"?\" pour afficher l\'aide", "Synchronisation Automatique de la base de données ", "Envoyer les extension de fichier", "Autorisez les flux inautorisés", "Inclure les en-tetes", "Javascript externe", "Accueil", "", "", "Afficher les statistiques", "Inclure les ID3v2 dans le stream", "Activer l\'inscritpion d\'utilisateur"
, "Types de fichiers", "Oui", "Non", "Extensions", "Type-MIME", "Inclure dans le M3U", "Editer les types de fichiers", "En etes vous sûr?");


$klang[12]  = array("Indonesian", "ISO-8859-1", "Indonesia", "Yang Ter-Hot", "Yang Terbaru", "Cari", "(hanya %1 tampilan)", "dtk", "Hasil Pencarian: '%1'", "ditemukan", "Kosong", "Opsi update pencarian database", "Hapus record tdk terpakai", "Bangun Ulang ID3?", 
 "Mode Debug ?", "Update", "Batal", "update pencarian database", "ada %1 file", "Tipe file tdk ada: %1, abaikan.", "Terinstall: %1 - Update %2, scan:", "Scan:", "Gagal - query: %1", "File %1 tdk terbaca, Abaikan", "Menghapus: %1", 
 "Tambah %1, Ubah %2, Hapus %3 dimana %4 gagal dan %5 abaikan bila %6 file - %7 detik - %8 dipilih utk dihapus.", "Selesai", "Tutup", "File yang dicari tdk ada: \"%1\"", "Login kPlaylist", "Daftar album dengan artis: %1", "Hotselect %1", "Tdk ada pilihan, Playlist tdk terupdate", "Playlist ter-update!", "Kembali", "Playlist ditambah!", 
 "Ingatlah utk me-reload hal. ini", "Login:", "Password:", "Peringatan! Ini bukan web umum. Semua Aktifitas terekam disini.", "Login", "Butuh SSL untuk Login", "Putar", "Hapus", "Sharing:", "Simpan", "Playlist kontrol: \"%1\" - %2 judul", 
 "Editor", "Viewer", "Pilih", "Seq", "Status", "Info", "Hapus", "Nama", "Total:", "Error", "Action pd terpilih:", 
 "Sekuen", "Ubah Playlist", "Hapus entri ini", "Tambah playlist", "Nama", "Buat", "Putar:", "File", "Album", "Semua", "terpilih", 
 "tambah", "putar", "ubah", "baru", "Pilih:", "Kontrol:", "Playlist:", "Nomor HotSelect", "KeyTeq Anda:", "(Cek Upgrade)", "Homesite", 
 "hanya id3", "album", "judul", "artis", "Hotselect Album dari Artis ", "lihat", "Playlist lainnya", "User", "Kontrol Admin", "Yang terbaru", "Yang Terhot", 
 "Logout", "Opsi", "Cek", "Profil", "Ubah user", "User baru", "Nama Lengkap", "Login", "Ubah Password?", "Password", "Komentar", 
 "Level Akses", "On", "Off", "Hapus user", "Logout user", "Refresh", "User baru", "hapus", "logout", "Gunakan EXTM3U", "Tampilkan banyak baris (hot/baru)", 
 "Max. Baris pencarian", "Reset", "Buka direktori", "ke direktori: %1", "Download", "Naik keatas", "Ke direktori root", "Cek Upgrade", "User", "Bahasa", "Opsi", 
 "Bootd", "Acak:", "Seting", "Direktori base", "Lokasi stream", "Bahasa default", "System Windows", "Butuh HTTPS", "Boleh mencari", "Boleh dowload", "Batas session", 
 "Report gagal login diperlukan", "Hold on - fetching file list ", "Playlist tdk bisa ditambah!", "Admin", "Login dengan HTTPS untuh mengganti!");

$klang[13]  = array("Italian", "ISO-8859-1", "Italiano", "Cosa c'è di Hot", "Cosa c'è di nuovo", "Ricerca", "(soltanto 1% visibile)", "sec", "risultato della ricerca: '%1'", "trovato", "nessuno.", "aggiona opzioni ricerca nel database", "Cancella records non utilizzati?", "Ricostruisci ID3?", 
"modalità di Debug?", "Aggiorna", "Annulla", "aggiorna ricerca nel database", "Trovati %1 files.", "Impossibile determinare questo file: %1, saltato.", "Installato: %1 - Aggiornato: %2, scansione:", "Scansione:", "Fallita - ricerca: %1", "Impossibile leggere questo file: %1. Saltato.", "Rimosso: %1", 
"Inserito %1, aggiornato %1, cancellato %3, quando %4 è fallito e %5 saltato su %6 files - %7 secondi - %8 segnati per la cancellazione.", "Fatto", "Chiuso", "Impossibile trovare files qui: \"%1\"", "KPlaylist Login", "Lista album per artista: %1", "Hotselect %1", "Nessuna canzone selezionata. Playlist non aggiornata.", "Playlist aggiornata!", "Indietro", "Playlist aggiunta!", 
"Ricorda di ricaricare la pagina.", "login:", "password:", "Attenzione! Questo non è un sito pubblico. Tutte le azioni vengono registrate.", "Login", "SSL richiesto per l'accesso.", "Play", "Cancella", "Condiviso:", "Salva", "Controllo playlist: \"%1\" - %2 titoli", 
"Editor", "Visualizzatore", "Selezione", "Seq", "Stato", "Informazioni", "Canc", "Nome", "Totale:", "Errore", "Azione da eseguire sulla selezione:", 
"Sequenza:", "Edita playlist", "Cancella questa riga", "aggiungi playlist", "Nome:", "Crea", "Esegui:", "File", "Album", "Tutto", "Selezionati", 
"aggiungi", "play", "modifica", "nuovo", "Selezione:", "Controllo:", "Playlist:", "Selezione numerica", "Keyteq vi propone:", "(controlla aggiornamenti)", "Homepage", 
"solo id3", "album", "titolo", "artista", "Seleziona album per artista", "visualizza", "Playlists condivise", "Utenti", "Controllo dell'amministratore", "Cosa c'è di nuovo", "Cosa c'è di Hot", "Esci", "Opzioni", "Controlla", "Mio", "modifica utente", "nuovo utente", "Nome completo", "Login", "Cambio Password?", "Password", "Commento", 
"Livello d'accesso", "On", "Off", "Cancella utente", "Uscita utente", "Refresh", "Nuovo utente", "canc", "Uscita", "Usa opzione EXTM3U", "Mostra quante righe (hot/nuove)", 
"Righe massime da cercare", "Reset", "Apri directory", "Vai alla directory: %1", "Download", "Sali di un livello", "Vai al livello principale", "Controlla per l'aggiornamento", "utenti", "lingua", "opzioni", 
"Booted", "Casuale:", "Impostazioni", "Directory iniziale", "locazione brano", "Lingua di default", "Un sistema Windows", "Richiede HTTPS", "Permetti ricerca", "Permetti download", "timeout sessione", 
"Riporta tentativi falliti di login", "Aspetta - estrazione lista file", "La playlist non può essere aggiunta!", "Amministratore", "Collegarsi tramite HTTPS per cambiare!", "Abilita morore di streaming", "Titolo", "Artista", "Album", "Commento", "Anno", 
"Traccia", "Genere", "non settato", "Limitazione download (kbps)", "Utente", "%1 minuti - %2 titoli", "%1 kilobit %2 minuti", "Lista generi: %1", "Vai", "%1d %2h %3m playtime %4 files %5 mb", "Nessuna risorsa.", 
"Password cambiata!", "Crea utente", "Fai la tua selezione!", "Cos'è l'update?", "Aiuto", "Usa immagini esterne?", "Path immagini esterne", "Password corrente", "La passord corrente è sbagliata!", "Archiver preferito", "L'archivio potrebbe non essere stato creato", 
"Probabile file duplicato: %1 - %2", "Eliminare la playlist?", "Alfabetico", "Random", "Ordina", "Originale", "Usa javascript", "Eliminare questo utente?", "Guarda la history", "history", "Righe",  "File CSS Esterno");

$klang[14]  = array("Traditional Chinese [&amp;#12345] ", "big5", "&#32321;&#39636;&#20013;&#25991;", "&#26368;&#29105;&#38272;", "&#26368;&#26032;", "&#25628;&#23563;", 
"(&#21482;&#26377; %1 &#31558;&#39023;&#31034;)", "&#31186;", "'%1' &#65306;&#25628;&#23563;&#32080;&#26524;", "&#25214;&#21040;", "&#27794;&#26377;", 
"&#26356;&#26032;&#25628;&#23563;&#36039;&#26009;&#24235;&#36984;&#38917;", "&#21034;&#38500; &#26410;&#29992;&#36942;&#30340;&#35352;&#37636;&#65311;", 
"&#37325;&#24314; ID3", "&#38500;&#34802;&#27169;&#24335;", "&#26356;&#26032;", "&#21462;&#28040;", "&#26356;&#26032;&#25628;&#23563;&#36039;&#26009;&#24235;", 
"&#25214;&#21040; %1 &#27284;&#26696;&#12290;", "&#30906;&#23450;&#19981;&#21040;&#27492; %1 &#27284;&#26696;&#65072; &#30053;&#36942;&#12290;", 
"&#24050;&#23433;&#35037;&#65072; %1 - &#26356;&#26032;&#65306; %2 &#65104; &#25475;&#30596;&#65306;", "&#25475;&#30596;&#65306;", "&#22833;&#25943; - &#21839;&#38988;&#65072; %1", 
"&#35712;&#19981;&#21040;&#27492; %1 &#27284;&#26696; &#65072;&#30053;&#36942;", "&#24050;&#31227;&#38500;&#65306; %1", 
"&#24050;&#25554;&#20837; %1 &#65292; &#24050;&#26356;&#26032; %2 &#65292; &#24050;&#21034;&#38500; %3&#65292; &#22320;&#40670; %4  &#22833;&#25943; &#21450; %6 &#27284;&#26696;&#20013;&#30053;&#36942;%5  - %7 &#31186; - &#24050;&#21034;&#38500; %8 &#26377;&#35352;&#34399;&#30340;&#27284;&#26696;",
"&#24050;&#23436;&#25104;", "&#38359;&#38281;", "&#22312;&#27492;&#25214;&#19981;&#21040;&#20219;&#20309;&#27284;&#26696;&#65306; \"%1\"",
"kPlaylist &#30331;&#20837;", "&#27492;&#27468;&#25163;&#30340;&#23560;&#36655;&#28165;&#21934;&#65306; %1", "&#29105;&#36984; %1", 
"&#27794;&#26377;&#27468;&#26354;&#36984;&#25799;&#12290; &#25773;&#25918;&#28165;&#21934;&#27794;&#26377;&#26356;&#26032;&#12290;",
"&#25773;&#25918;&#28165;&#21934;&#24050;&#26356;&#26032;&#65281;", "&#36820;&#22238;", "&#25773;&#25918;&#28165;&#21934;&#24050;&#21152;&#20837;&#65281;", 
"&#35352;&#20303;&#37325;&#26032;&#25972;&#29702;&#27492;&#38913;&#12290;", "&#30331;&#20837;&#21517;&#31281;&#65306;", "&#23494;&#30908;&#65306;",
"&#35686;&#21578;&#65281;&#27492;&#32178;&#31449;&#26159;&#19981;&#20844;&#38283;&#30340;&#65292;&#25152;&#26377;&#21205;&#20316;&#26159;&#26371;&#34987;&#35352;&#37636;&#12290;", 
"&#30331;&#20837;", "&#23433;&#20840;&#24615;(SSL)&#30331;&#20837;", "&#25773;&#25918;", "&#21034;&#38500;", "&#20998;&#20139;&#65109;", "&#20786;&#23384;", 
"&#25511;&#21046;&#25773;&#25918;&#28165;&#21934;&#65072; \"%1\" - %2 &#27161;&#38988;", "&#32232;&#36655;&#22120;", "&#27298;&#35222;&#22120;", "&#36984;&#25799;",
"&#38918;&#24207;", "&#29376;&#24907;", "&#36039;&#35338;", "&#21034;&#38500;", "&#21517;&#31281;", "&#32317;&#25976;&#65109;", "&#37679;&#35492;", "&#36984;&#25799;&#20013;&#65306;",
"&#27425;&#24207;&#65109;", "&#32232;&#36655;&#25773;&#25918;&#28165;&#21934;", "&#21034;&#38500;&#27492;&#21152;&#20837;", "&#21152;&#20837;&#25773;&#25918;&#28165;&#21934;",
"&#21517;&#23383;&#65109;", "&#24314;&#31435;", "&#25773;&#25918;&#65306;", "&#27284;&#26696;", "&#23560;&#36655;", "&#20840;&#37096;", "&#24050;&#36984;&#25799;", "&#26032;&#22686;", 
"&#25773;&#25918;", "&#32232;&#36655;", "&#26032;&#22686;", "&#36984;&#25799;&#65306;", "&#25773;&#25918;&#25511;&#21046;&#65306;", "&#25773;&#25918;&#30446;&#37636;&#65306;",
"&#29105;&#36984;&#25976;&#20540;", "Keyteq &#25552;&#25552;&#20320;&#65306;", "(&#27298;&#26597;&#26356;&#26032;)", "&#20027;&#38913;", "&#21482;&#25628;&#23563; id3", "&#23560;&#36655;",
"&#27161;&#38988;", "&#27468;&#25163;", "&#29105;&#36984;&#27468;&#25163;&#23560;&#36655;", "&#27298;&#35222;", "&#20998;&#20139;&#25773;&#25918;&#30446;&#37636;", "&#29992;&#25142;",
"&#31649;&#29702;", "&#26368;&#26032;", "&#26368;&#29105;&#38272;", "&#30331;&#20986;", "&#36984;&#38917;", "&#27298;&#26597;", "&#20854;&#20182;", "&#32232;&#36655;&#20351;&#29992;&#32773;", 
"&#26032;&#22686;&#20351;&#29992;&#32773;", "&#20840;&#21517;", "&#30331;&#20837;", "&#35722;&#26356;&#23494;&#30908;&#65311;", "&#23494;&#30908;", "&#20633;&#35387;", 
"&#23384;&#21462;&#23652;&#32026;", "&#38283;", "&#38364;", "&#21034;&#38500;&#20351;&#29992;&#32773;", "&#20999;&#26039;&#20351;&#29992;&#32773;", "&#37325;&#26032;&#25972;&#29702;", 
"&#26032;&#22686;&#20351;&#29992;&#32773;", "&#21034;&#38500;", "&#30331;&#20986;", "&#20351;&#29992; EXTM3U &#25928;&#26524;&#65311;", 
"&#39023;&#31034;&#22810;&#23569;&#34892; (&#29105;&#38272;/&#26032;)", "&#26368;&#22823;&#25628;&#23563;&#34892;&#25976;", "&#37325;&#35373;", 
"&#38283;&#21855;&#30446;&#37636;", "&#36339;&#21040;&#30446;&#37636;&#65306; %1", "&#19979;&#36617;", "&#36339;&#21040;&#19978;&#19968;&#23652;", 
"&#36339;&#21040;&#26681;&#30446;&#37636;", "&#27298;&#26597;&#26356;&#26032;", "&#20351;&#29992;&#32773;", "&#35486;&#35328;", "&#36984;&#38917;", 
"&#24050;&#36215;&#21205;", "&#38568;&#27231;", "&#35373;&#23450;", "&#26681;&#30446;&#37636;&#32085;&#23565;&#36335;&#24465;", "&#20018;&#27969;&#36335;&#24465;", 
"&#38928;&#35373;&#35486;&#35328;", "&#35222;&#31383;&#31995;&#32113;", "&#35201;&#27714;HTTPS", "&#20801;&#35377;&#25628;&#23563;", "&#20801;&#35377;&#19979;&#36617;",
"&#36926;&#26178;", "&#22577;&#21578;&#30331;&#20837;&#22833;&#25943;", "&#35531;&#31561;&#31561; - &#24314;&#31435;&#27284;&#26696;&#30446;&#37636;&#20013;",
"&#25773;&#25918;&#28165;&#21934;&#19981;&#34987;&#26356;&#26032;&#65281;", "&#31649;&#29702;&#32773;", 
"&#20351;&#29992;HTTPS&#30331;&#20837;&#24460;&#26356;&#25913;&#65281;");

$klang[15] = array("Traditional Chinese - big5", "big5", "ÁcÅé¤¤¤å", "³Ì¼öªù", "³Ì·s", "·j´M", "(¥u¦³ %1 µ§Åã¥Ü)", "¬í", "'%1' ¡G·j´Mµ²ªG", "§ä¨ì", "¨S¦³", "§ó·s·j´M¸ê®Æ®w¿ï¶µ", 
"§R°£ ¥¼¥Î¹Lªº°O¿ý¡H", "­««Ø ID3", "°£ÂÎ¼Ò¦¡", "§ó·s", "¨ú®ø", "§ó·s·j´M¸ê®Æ®w", "§ä¨ì %1 ÀÉ®×¡C", "½T©w¤£¨ì¦¹ %1 ÀÉ®×¡J ²¤¹L¡C", "¤w¦w¸Ë¡J %1 - §ó·s¡G %2 ¡M ±½ºË¡G",
"±½ºË¡G", "¥¢±Ñ - °ÝÃD¡J %1", "Åª¤£¨ì¦¹ %1 ÀÉ®× ¡J²¤¹L", "¤w²¾°£¡G %1", "¤w´¡¤J %1 ¡A ¤w§ó·s %2 ¡A ¤w§R°£ %3¡A ¦aÂI %4 ¥¢±Ñ ¤Î %6 ÀÉ®×¤¤²¤¹L%5 - %7 ¬í - ¤w§R°£ %8 ¦³°O¸¹ªºÀÉ®×",
"¤w§¹¦¨", "?³¬", "¦b¦¹§ä¤£¨ì¥ô¦óÀÉ®×¡G '%1'", "kPlaylist µn¤J", "¦¹ºq¤âªº±M¿è²M³æ¡G %1", "¼ö¿ï %1", "¨S¦³ºq¦±¿ï¾Ü¡C ¼½©ñ²M³æ¨S¦³§ó·s¡C", "¼½©ñ²M³æ¤w§ó·s¡I", "ªð¦^", 
"¼½©ñ²M³æ¤w¥[¤J¡I", "°O¦í­«·s¾ã²z¦¹­¶¡C", "µn¤J¦WºÙ¡G", "±K½X¡G", "Äµ§i¡I¦¹ºô¯¸¬O¤£¤½¶}ªº¡A©Ò¦³°Ê§@¬O·|³Q°O¿ý¡C", "µn¤J", "¦w¥þ©Ê(SSL)µn¤J", "¼½©ñ", "§R°£", "¤À¨É¡R", 
"Àx¦s", "±±¨î¼½©ñ²M³æ¡J '%1' - %2 ¼ÐÃD", "½s¿è¾¹", "ÀËµø¾¹", "¿ï¾Ü", "¶¶§Ç", "ª¬ºA", "¸ê°T", "§R°£", "¦WºÙ", "Á`¼Æ¡R", "¿ù»~", "¿ï¾Ü¤¤¡G", "¦¸§Ç¡R", "½s¿è¼½©ñ²M³æ",
"§R°£¦¹¥[¤J", "¥[¤J¼½©ñ²M³æ", "¦W¦r¡R", "«Ø¥ß", "¼½©ñ¡G", "ÀÉ®×", "±M¿è", "¥þ³¡", "¤w¿ï¾Ü", "·s¼W", "¼½©ñ", "½s¿è", "·s¼W", "¿ï¾Ü¡G", "¼½©ñ±±¨î¡G", "¼½©ñ¥Ø¿ý¡G", 
"¼ö¿ï¼Æ­È", "Keyteq ´£´£§A¡G", "(ÀË¬d§ó·s)", "¥D­¶", "¥u·j´M id3", "±M¿è", "¼ÐÃD", "ºq¤â", "¼ö¿ïºq¤â±M¿è", "ÀËµø", "¤À¨É¼½©ñ¥Ø¿ý", "¥Î¤á", "ºÞ²z", "³Ì·s", "³Ì¼öªù",
"µn¥X", "¿ï¶µ", "ÀË¬d", "¨ä¥L", "½s¿è¨Ï¥ÎªÌ", "·s¼W¨Ï¥ÎªÌ", "¥þ¦W", "µn¤J", "ÅÜ§ó±K½X¡H", "±K½X", "³Æµù", "¦s¨ú¼h¯Å", "¶}", "Ãö", "§R°£¨Ï¥ÎªÌ", "¤ÁÂ_¨Ï¥ÎªÌ", "­«·s¾ã²z",
"·s¼W¨Ï¥ÎªÌ", "§R°£", "µn¥X", "¨Ï¥Î EXTM3U ®ÄªG¡H", "Åã¥Ü¦h¤Ö¦æ (¼öªù/·s)", "³Ì¤j·j´M¦æ¼Æ", "­«³]", "¶}±Ò¥Ø¿ý", "¸õ¨ì¥Ø¿ý¡G %1", "¤U¸ü", "¸õ¨ì¤W¤@¼h", "¸õ¨ì®Ú¥Ø¿ý",
"ÀË¬d§ó·s", "¨Ï¥ÎªÌ", "»y¨¥", "¿ï¶µ", "¤w°_°Ê", "ÀH¾÷", "³]©w", "®Ú¥Ø¿ýµ´¹ï¸ô®|", "¦ê¬y¸ô®|", "¹w³]»y¨¥", "µøµ¡¨t²Î", "­n¨DHTTPS", "¤¹³\·j´M", "¤¹³\¤U¸ü", "¹O®É", 
"³ø§iµn¤J¥¢±Ñ", "½Ðµ¥µ¥ - «Ø¥ßÀÉ®×¥Ø¿ý¤¤", "¼½©ñ²M³æ¤£³Q§ó·s¡I", "ºÞ²zªÌ", "¨Ï¥ÎHTTPSµn¤J«á§ó§ï¡I");

$klang[16] = array("Traditional Chinese - gb2312", "gb2312", "ÁcÅé¤¤¤å", "³Ì¼öªù", "³Ì·s", "·j´M", "(¥u¦³ %1 µ§Åã¥Ü)", "¬í", "'%1' ¡G·j´Mµ²ªG", "§ä¨ì", "¨S¦³", 
"§ó·s·j´M¸ê®Æ®w¿ï¶µ", "§R°£ ¥¼¥Î¹Lªº°O¿ý¡H", "­««Ø ID3", "°£ÂÎ¼Ò¦¡", "§ó·s", "¨ú®ø", "§ó·s·j´M¸ê®Æ®w", "§ä¨ì %1 ÀÉ®×¡C", "½T©w¤£¨ì¦¹ %1 ÀÉ®×¡J ²¤¹L¡C", 
"¤w¦w¸Ë¡J %1 - §ó·s¡G %2 ¡M ±½ºË¡G", "±½ºË¡G", "¥¢±Ñ - °ÝÃD¡J %1", "Åª¤£¨ì¦¹ %1 ÀÉ®× ¡J²¤¹L", "¤w²¾°£¡G %1", 
"¤w´¡¤J %1 ¡A ¤w§ó·s %2 ¡A ¤w§R°£ %3¡A ¦aÂI %4 ¥¢±Ñ ¤Î %6 ÀÉ®×¤¤²¤¹L%5 - %7 ¬í - ¤w§R°£ %8 ¦³°O¸¹ªºÀÉ®×", "¤w§¹¦¨", "?³¬", "¦b¦¹§ä¤£¨ì¥ô¦óÀÉ®×¡G '%1'", "kPlaylist µn¤J", 
"¦¹ºq¤âªº±M¿è²M³æ¡G %1", "¼ö¿ï %1", "¨S¦³ºq¦±¿ï¾Ü¡C ¼½©ñ²M³æ¨S¦³§ó·s¡C", "¼½©ñ²M³æ¤w§ó·s¡I", "ªð¦^", "¼½©ñ²M³æ¤w¥[¤J¡I", "°O¦í­«·s¾ã²z¦¹­¶¡C", "µn¤J¦WºÙ¡G", "±K½X¡G", 
"Äµ§i¡I¦¹ºô¯¸¬O¤£¤½¶}ªº¡A©Ò¦³°Ê§@¬O·|³Q°O¿ý¡C", "µn¤J", "¦w¥þ©Ê(SSL)µn¤J", "¼½©ñ", "§R°£", "¤À¨É¡R", "Àx¦s", "±±¨î¼½©ñ²M³æ¡J '%1' - %2 ¼ÐÃD", "½s¿è¾¹", "ÀËµø¾¹", "¿ï¾Ü", 
"¶¶§Ç", "ª¬ºA", "¸ê°T", "§R°£", "¦WºÙ", "Á`¼Æ¡R", "¿ù»~", "¿ï¾Ü¤¤¡G", "¦¸§Ç¡R", "½s¿è¼½©ñ²M³æ", "§R°£¦¹¥[¤J", "¥[¤J¼½©ñ²M³æ", "¦W¦r¡R", "«Ø¥ß", "¼½©ñ¡G", "ÀÉ®×", "±M¿è", 
"¥þ³¡", "¤w¿ï¾Ü", "·s¼W", "¼½©ñ", "½s¿è", "·s¼W", "¿ï¾Ü¡G", "¼½©ñ±±¨î¡G", "¼½©ñ¥Ø¿ý¡G", "¼ö¿ï¼Æ­È", "Keyteq ´£´£§A¡G", "(ÀË¬d§ó·s)", "¥D­¶", "¥u·j´M id3", "±M¿è", "¼ÐÃD", 
"ºq¤â", "¼ö¿ïºq¤â±M¿è", "ÀËµø", "¤À¨É¼½©ñ¥Ø¿ý", "¥Î¤á", "ºÞ²z", "³Ì·s", "³Ì¼öªù", "µn¥X", "¿ï¶µ", "ÀË¬d", "¨ä¥L", "½s¿è¨Ï¥ÎªÌ", "·s¼W¨Ï¥ÎªÌ", "¥þ¦W", "µn¤J", "ÅÜ§ó±K½X¡H", 
"±K½X", "³Æµù", "¦s¨ú¼h¯Å", "¶}", "Ãö", "§R°£¨Ï¥ÎªÌ", "¤ÁÂ_¨Ï¥ÎªÌ", "­«·s¾ã²z", "·s¼W¨Ï¥ÎªÌ", "§R°£", "µn¥X", "¨Ï¥Î EXTM3U ®ÄªG¡H", "Åã¥Ü¦h¤Ö¦æ (¼öªù/·s)", "³Ì¤j·j´M¦æ¼Æ", 
"­«³]", "¶}±Ò¥Ø¿ý", "¸õ¨ì¥Ø¿ý¡G %1", "¤U¸ü", "¸õ¨ì¤W¤@¼h", "¸õ¨ì®Ú¥Ø¿ý", "ÀË¬d§ó·s", "¨Ï¥ÎªÌ", "»y¨¥", "¿ï¶µ", "¤w°_°Ê", "ÀH¾÷", "³]©w", "®Ú¥Ø¿ýµ´¹ï¸ô®|", "¦ê¬y¸ô®|", 
"¹w³]»y¨¥", "µøµ¡¨t²Î", "­n¨DHTTPS", "¤¹³\·j´M", "¤¹³\¤U¸ü", "¹O®É", "³ø§iµn¤J¥¢±Ñ", "½Ðµ¥µ¥ - «Ø¥ßÀÉ®×¥Ø¿ý¤¤", "¼½©ñ²M³æ¤£³Q§ó·s¡I", "ºÞ²zªÌ", 
"¨Ï¥ÎHTTPSµn¤J«á§ó§ï¡I");

$klang[17]  = array("Korean", "ISO-8859-1", "&#54620;&#44397;&#50612;", "&#51064;&#44592;&#51221;&#48372;", "&#52572;&#49888;&#51221;&#48372;", "&#44160;&#49353;", 
"(%1 &#47564; &#48372;&#51076;)", "&#52488;", "&#44160;&#49353; &#44208;&#44284; : '%1'", "&#52286;&#50520;&#51020;", "&#50630;&#51020;.", "&#44160;&#49353; &#51088;&#47308; &#50741;&#49496; &#50629;&#45936;&#51060;&#53944;", "&#49324;&#50857;&#54616;&#51648; &#50506;&#45716; &#44592;&#47197; &#49325;&#51228;?", "ID3&#51116;&#44396;&#49457;?", "&#46356;&#48260;&#44536; &#47784;&#46300;?", "&#50629;&#45936;&#51060;&#53944;", "&#52712;&#49548;", "&#44160;&#49353; &#51088;&#47308; &#50629;&#45936;&#51060;&#53944;", "%1 &#54028;&#51068;&#51012; &#52286;&#50520;&#51020;.", "&#51060; &#54028;&#51068;&#51012; &#44208;&#51221;&#54624; &#49688; &#50630;&#51020;: %1, &#44148;&#45320;&#46848;.", "&#49444;&#52824;&#46120;: %1 - &#50629;&#45936;&#51060;&#53944;: %2, &#44160;&#49353;:", "&#44160;&#49353;:", "&#49892;&#54056; - &#51656;&#47928;: %1", "&#51060; &#54028;&#51068;&#51012; &#51069;&#51012; &#49688; &#50630;&#51020;: %1. &#44148;&#45320;&#46848;.", "&#51228;&#44144;&#46120;: %1", "%6 &#54028;&#51068;&#46308; &#51473; %4 &#45716; &#49892;&#54056;, %5&#45716; &#44148;&#45320;&#46832;&#44256;,%1 &#52628;&#44032; %2 &#44081;&#49888;&#46104;&#44256; %3 &#49325;&#51228;&#46120; - %7 &#52488; - %8 &#51008; &#49325;&#51228;&#54364;&#49884;&#46120;.", "&#45149;", "&#45803;&#51020;", "&#50612;&#46500; &#54028;&#51068;&#46020; &#52286;&#51012; &#49688; &#50630;&#51020;: \"%1\"", "kPlaylist &#47196;&#44536;&#50728;", "&#50500;&#54000;&#49828;&#53944;&#51032; &#50536;&#48276; &#47532;&#49828;&#53944; : %1", "&#51064;&#44592;&#49440;&#53469;&#44257; %1", "&#44257;&#51060; &#49440;&#53469;&#46104;&#51648; &#50506;&#50520;&#51020;. Playlist&#44032; &#44081;&#49888;&#46104;&#51648; &#50506;&#50520;&#51020;.", "Playlist &#44081;&#49888;!", "&#46244;&#47196;", "Playlist &#52628;&#44032;!", "&#51060; &#54168;&#51060;&#51648;&#47484; &#45796;&#49884; &#51069;&#51004;&#49464;&#50836;.", "&#47196;&#44536;&#51064;:", "&#50516;&#54840;:", "&#51452;&#51032;! &#51060; &#44275;&#51008; &#44277;&#44060;&#46108; &#50937;&#49324;&#51060;&#53944;&#44032; &#50500;&#45785;&#45768;&#45796;. &#47784;&#46304; &#54665;&#46041;&#51060; &#44592;&#47197;&#46121;&#45768;&#45796;.", "&#47196;&#44536;&#51064;", "&#47196;&#44536;&#50728;&#51012; &#50948;&#54644; SSL&#51060; &#54596;&#50836;&#54633;&#45768;&#45796;.", "&#51116;&#49373;", "&#49325;&#51228;", "&#44277;&#50976;&#46120;:", "&#51200;&#51109;", "playlist &#44288;&#47532;: \"%1\" - %2 &#51228;&#47785;", "&#54200;&#51665;&#44592;", "&#48624;&#50612;", "&#49440;&#53469;", "&#49692;&#49436;", "&#49345;&#53468;", "&#51221;&#48372;", "&#49325;&#51228;", "&#51060;&#47492;", "&#54633;&#44228;:", "&#50724;&#47448;", "&#49440;&#53469;&#54620; &#46041;&#51089;:", "&#49692;&#49436;:", "playlist &#54200;&#51665;", "&#51060; &#44592;&#47197;&#51012; &#49325;&#51228;&#54632;", "playlist &#52628;&#44032;", "&#51060;&#47492;:", "&#47564;&#46308;&#44592;", "&#51116;&#49373;:", "&#54028;&#51068;:", "&#50536;&#48276;", "&#51204;&#48512;", "&#49440;&#53469;&#46120;", "&#52628;&#44032;", "&#51116;&#49373;", "&#54200;&#51665;", "&#49352;&#47196; &#47564;&#46308;&#44592;", "&#49440;&#53469;:", "&#51116;&#49373; &#44288;&#47532;:", "Playlist:", "&#51064;&#44592;&#49440;&#53469;&#44257; &#49707;&#51088;", "&#45817;&#49888;&#50640;&#44172; Keyteq &#51060; &#51452;&#45716; &#44163;:", "(&#50629;&#44536;&#47112;&#51060;&#46300;&#47484; &#52404;&#53356;&#54616;&#49464;&#50836;)", "&#54856;", "id3&#47564;", "&#50536;&#48276;", "&#51228;&#47785;", "&#50500;&#54000;&#49828;&#53944;", "&#50500;&#54000;&#49828;&#53944;&#50640;&#49436; &#51064;&#44592;&#50536;&#48276;", "&#48372;&#44592;", "&#44277;&#50976;&#54620; playlist", "&#49324;&#50857;&#51088;", "&#50612;&#46300;&#48124; &#44288;&#47532;", "&#52572;&#49888;&#51221;&#48372;", "&#51064;&#44592;&#51221;&#48372;", "&#47196;&#44536;&#50500;&#50883;", "&#50741;&#49496;", "&#52404;&#53356;", "&#45208;&#51032;", "&#49324;&#50857;&#51088; &#54200;&#51665;", "&#49352;&#47196;&#50868; &#49324;&#50857;&#51088;", "&#51060;&#47492;", "&#47196;&#44536;&#51064;", "&#50516;&#54840;&#47484; &#48148;&#44984;&#49884;&#44192;&#49845;&#45768;&#44620;?", "&#50516;&#54840;", "&#53076;&#47704;&#53944;", "&#51217;&#44540;&#47112;&#48296;", "&#53020;&#44592;", "&#45124;&#44592;", "&#49324;&#50857;&#51088; &#49325;&#51228;", "&#49324;&#50857;&#51088; &#47196;&#44536;&#50500;&#50883;", "&#49352;&#47196; &#44256;&#52824;&#44592;", "&#49352;&#47196;&#50868; &#49324;&#50857;&#51088;", "&#49325;&#51228;", "&#47196;&#44536;&#50500;&#50883;", "EXTM3U &#47484; &#49324;&#50857;&#54633;&#45768;&#44620;?", "&#51460; &#49688; &#48372;&#51060;&#44592;(hot/new)", "&#44032;&#51109; &#47566;&#51008; &#44160;&#49353; &#51460;", "&#47532;&#49483;", "&#46356;&#47113;&#53664;&#47532; &#50676;&#44592;", "&#46356;&#47113;&#53664;&#47532;&#47196; &#44032;&#44592;: %1", "&#45236;&#47140;&#48155;&#44592;", "&#54620; &#45800;&#44228; &#50948;&#47196; &#44032;&#44592;", "&#51228;&#51068; &#50948;&#47196; &#44032;&#44592;.", "&#50629;&#44536;&#47112;&#51060;&#47484; &#52404;&#53356;&#54616;&#49464;&#50836;", "&#49324;&#50857;&#51088;", "&#50616;&#50612;", "&#50741;&#49496;", "&#48512;&#54021;&#46120;", "&#46244;&#49438;&#44592;:", "&#49464;&#54021;", "&#44592;&#48376; &#46356;&#47113;&#53664;&#47532;", "&#49828;&#53944;&#47548; &#51109;&#49548;", "&#44592;&#48376; &#50616;&#50612;", "&#50952;&#46020;&#50864; &#49884;&#49828;&#53596;", "HTTPS &#44032; &#54596;&#50836;&#54632;", "Seek &#54728;&#50857;", "&#45236;&#47140;&#48155;&#44592; &#54728;&#50857;", "&#49464;&#49496; &#49884;&#44036;&#51473;&#45800;", "&#49892;&#54056;&#54620; &#47196;&#44596; &#49884;&#46020; &#50508;&#47532;&#44592;", "&#51104;&#44624;&#47564; - &#54028;&#51068; &#47785;&#47197;&#51012; &#44032;&#51648;&#44256; &#50724;&#44256; &#51080;&#49845;&#45768;&#45796;", "Playlist &#50640; &#52628;&#44032;&#54624; &#49688; &#50630;&#49845;&#45768;&#45796;!", "&#50612;&#46300;&#48124;", 
"&#48148;&#44984;&#44592; &#50948;&#54644;&#49436; HTTPS&#47196; &#47196;&#44596;&#54616;&#49464;&#50836;!");

$klang[18]  = array("Estonian", "iso-8859-1", "eesti keel", "Mis hetkel populaarne", "Mis vanalehm", "Peksapihku", "mitte munnigi %1 ei ole näha", "peeretus", "Peksapihku tulemused: '%1'", "tapeti", "türa no mitte midagi ei leia", "kägarda peksapihku trammi tagumineratas", "Saadavittu need pasapead?", "Käipõrgu ID3?", "Siluvalmoel?", "Kägarda", "Soepeast", "kägarda peksapihku trammi", "Tapeti %1 kõrvits.", "Ei suudetud ümbernikkuda kõrvitsat: %1,  Kepiti süütut", "Näpiti nibusid: %1 - Kägardatud: %2, rinnad jäid koju:", "Rinnad jäid koju:", "Läks viinaravile - sopajoodik: %1", "Ei suudetud sünnitada : %1. Kepiti süütut", "Võeti libuna tööle: %1", "Vänati ilgelt taha %1, kägardatud %2, saadetudvittu %3 kus %4 läks viinaravile ning %5 Kepiti süütud hooradeks%6 kõrvits ning see kõik toimus %7 peeretuse -%8 saeti mitmeks tükiks ja saadeti vittu.", "Keerasidki kõik lõpuks persse. palju õnne!", "Tapa see idjootne asi!", "Ei suudetud sünnitada mitteainustki kõrvitsat: \"%1\"", "kJäänuse hukkumine", "koristaja saeti mitmeks tükiks türasid: %1", "Vaata kuumalt! %1", "Jäänust ei kägardatud.", "jäänus kägardatud!", "Edaspiidi", "jäänus peeretas", "Ära unusta, et siit ei ole keegi veel eluga pääsenud!", "hukkumine", "Pane paar sõna!", "Kõik munnid  on hukkunud.", "Hukku", "SSL venelits saeti mitmeks tükiks hukkuma", "Söösitta", "Saadavittu", "Abordid:", "Silita peenist", "Raisk jäänus: \"%1\" - %2 sitapeadest", "emakeppija", "isakeppija", "õekeppar", "kepieit", "lehmanisa", "kepiinfo", "Saada", "Pealdis", "Kogumoos", "Putsis", "munn on õekepparid:", "Kepitudeit:", "kepiema jäänust", "Saadavittu see mõtetus", "peereta jäänus", "perselutsija", "keeruta", "Söögisitt:", "Fail", "Album", "Kõiksemees korraga", "Õekepparid", "peereta", "mine:", "kepiema", "vanalehm", "Õekeppar:", "Söösitta Raisk:", "Jäänused:", "Vaata kuumalt numbriliselt", "Keyteq annab taha:", "(liputaja saeti mitmeks tükiks)", "Hooralaager", "mitte munnigi id3", "album", "sitapea", "türa", "Vaata kuumalt koristaja lahmakat türa", "kepiisa", "Abordi jäänused", "Rullnokad", "Sajajalgne raisk", "Mida vanalehm", "Mis hetkel populaarne", "Käi koju põngerjas", "tagumineratas", "Liputa", "kusi", "kepiema rullnokk", "vanalehm rullnokk", "Purjus perselutsija", "Hukkumine", "röövi kommipoodi?", "Kommipood", "Kauri", "Mõistuse vastane", "Välja", "Sisse", "saadvittu rullnokk", "Hukkuära rullnokk", "Käi putsi", "Vanalehm rullnokk", "saada", "hukkuära", "Pitsike EXTM3U-d ja kutu?", "Miks kõik on perses (kuum/vanalehm)", "Väga palju pihkupeksjaid on perses", "Sitakäi", "Vehi riista", "Võta veidike riista: %1", "Magatama", "Mine ülalkorrusele", "Mine vaata rooti riista", "Liputaja saeti mitmeks tükiks", "rullnokad", "Vemblad", "tagumineratas", "Allalastud", "Täiestisegamini", "Kotikuke", "Üleküla riist", "Ribidevahele pekstud", "Peatage vemblad", "Windows-i ajuleiutis", "venelits HTTPS", "karaga pikkipead", "karaga magatama", "hooaeg läbi", "hukkumist", "Võta sisse - neela alla kõrvitsa jäänukid", "Jäänust ei suudetud peeretada", "Sajajalgne", "Hukku", "Nuga ribidevahele vanapaks", "Sitapea", "Türa", "Koristaja", "Kauri", "löödi teibasse", "hirmuks teistele", "Tüüphukkamine", "Ei ole");

$klang[19]  = array("Portuguese of Brazil", "ISO-8859-1", "brasileiro", "este &eacute; popular", "Este &eacute; novo", "Busca", "(apenas %1 encontrado)", "seg", "Resultados da busca: '%1'", "encontrado", "Nenhum", "atualizar op&ccedil;&otilde;es da busca na base de dados ", "Apagar entradas sem uso? ", "Reconstruir ID3?", "Modo Debug?", "Atualizar", "Cancelar", "Atualizar busca no banco de dados", "Encontrados %1 arquivos.", "N&atilde;o foi poss&iacute;vel determinar este arquivo: %1, descartado", "Install %1 - Atualizar: %2, escanear:", "Escanear:", "Falha na busca: %1", "N&atilde;o foi poss&iacute;vel ler este arquivo: %1. Descartado.", "Removido: %1", "Inserido %1, atualizado %2, apagado %2, onde %4, falhou em %5, descartado por %6, arquivos - %7 seg - %8 marcado para ser deletado", "Finalizado", "Fechar", "N&atilde;o foi poss&iacute;vel encontrar arquivos aqui: &quot;%1&quot;", "Logon kPlaylist", "Lista de &aacute;lbum por artista: %1", "Populares %1", "Nenhuma m&uacute;sica selecionada. Lista n&atilde;o atualizada.", "Lista atualizada!", "Voltar", "Lista atualizada", "Lembre-se de atualizar a p&aacute;gina.", "login:", "senha:", "Aten&ccedil;&atilde;o! Este n&atilde;o &eacute; um site restrito. Todas as a&ccedil;&otilde;es s&atilde;o monitoradas.", "Login", "SSL necess&aacute;rio para entrar.", "Tocar", "Apagar", "Compartilhado", "Salvar", "Lista de controlhe: &quot;%1&quot; - %2 t&iacute;tulos", "Editor", "Visualizador", "Selecionar", "Seq", "Status", "Info", "Del", "Nome", "Totais", "Erro", "A&ccedil;&atilde;o selecionada:", "Sequ&ecirc;ncia", "editar lista", "Apagar esta entrada", "adicionar lista", "Nome:", "Criar", "Tocar:", "Arquivo", "&Aacute;lbum", "Todos", "Selecionado", "adicionar", "tocar", "editar", "novo", "Selecionar", "Controle", "Lista:", "Selecionar n&uacute;mero", "Keyteq oferece:", "(verificar atualiza&ccedil;&atilde;o)", "P&aacute;gina incial", "apenas id3", "&aacute;lbum", "t&iacute;tulo", "artista", "Selecionar &aacute;lbum por artista", "ver", "Listas compartilhadas", "Usu&aacute;rios", "Controle de administrador", "Este &eacute; novo", "Este &eacute; popular", "Logout", "Op&ccedil;&otilde;es", "Verificar", "Meu", "editar usu&aacute;rio", "novo usu&aacute;rio", "Nome completo", "Login", "Mudar senha?", "Senha", "Coment&aacute;rio", "N&iacute;vel de acesso", "Ligado", "Desligado", "Apagar usu&aacute;rio", "Desconectar usu&aacute;rio", "Atualizar", "Novo usu&aacute;rio", "apagar", "desconectar", "Utilizar op&ccedil;&atilde;o EXTM3U?", "Mostrar quantos arquivos (popular/novo)", "M&aacute;ximo de arquivos encontrados", "Restaurar", "Abrir diret&oacute;rio", "Para o diret&oacute;rio: %1", "Download", "Subir um n&iacute;vel", "Para o diret&oacute;rio principal", "Verificar atualiza&ccedil;&otilde;es", "usu&aacute;rios", "Linguagem", "op&ccedil;&otilde;es", "Carregado", "Aleat&oacute;rio", "Configura&ccedil;&otilde;es", "Diret&oacute;rio base", "Local de stream", "Linguagem padr&atilde;o", "Sistema Windows", "Requer HTTPS", "Permitir busca", "Permitir download", "Sess&atilde;o expirou", "Falha na tentativa de login", "Aguarde - buscando a lista de arquivos", "Lista n&atilde;o pode ser adicionada!", "0 = Admin, 1 = Usu&aacute;rio", "In&iacute;cio de uma sess&atilde;o com o HTTPS a mudar", "Habilite engenharia streaming", "Titulo", "Artista", "Album", "Comentário", "Ano", "Trilha", "Gênero", "Desativado", "Taxa máxima de download (kbps)", "Usuário", "%1 minuto(s) - %2 Titulos ", "%1 kbit %2 minuto(s)", "Lista de Generos: %1 ", "Ir", "%1d %2h %3m Tocando %4 files %5 mb", "Nenhun recursos relevantes aqui", "Mundança Senha", "Assine", "Por favor faça uma seleção", "O que foi atualizado ?", "Clique aqui para Ajuda", "Use Imagens Externas", "Path externo de imagens ", "Senha Atual", "A senha atual nao confere", "Arquivo preferido ", "Arquivo não pode ser feito");

$klang[20]  = array("Simplified Chinese ", "big5", "¼òÌåÖÐÎÄ", "ÈÈÁ¦ÍÆ¼ö", "×î½ü¸üÐÂ", "ËÑË÷", "Ä¿Ç°Ö»ÓÐ %1", "Ãë", "ËÑË÷½á¹û£º¡°%1¡±", "±»ÕÒµ½", "Ã»ÓÐ", "¸üÐÂËÑË÷Êý¾Ý¿âÑ¡Ïî", "É¾µôÎ´Ê¹ÓÃµÄ¼ÍÂ¼£¿", "ÖØ½¨ID3±êÇ©£¿", "ÅÅ´íÄ£Ê½£¿", "Éý¼¶", "È¡Ïû", "¸üÐÂËÑË÷Êý¾Ý¿â", "¹²ÕÒµ½ %1 ¸öÎÄ¼þ", "ÎÞ·¨Ê¶±ð´ËÎÄ¼þ£º%1£¬ÒÑÌø¹ý£¡", "ÒÑ°²×°£º%1 -¸üÐÂ£º%2£¬É¨Ãè£º", "É¨Ãè£º", "²éÑ¯¡°%1¡±Ê§°ÜÁË", "ÎÞ·¨¶ÁÈ¡´ËÎÄ¼þ£º%1£¬ÒÑÌø¹ý£¡", "%1ÒÑ±»É¾³ý£¡", "ÒÑÔÚ%4²åÈë%1£¬¸üÐÂ%2£¬É¾³ý%3", "ÒÑÍê³É", "¹Ø±Õ", "ÔÚ¡°%1¡±ÕÒ²»µ½ÈÎºÎÎÄ¼þ", "µÇÂ½KPlayList", "¡°%1¡±µÄ×¨¼­ÁÐ±í", "¼öÑ¡%1", "Î´Ñ¡ÔñÆµµÀ£¡²¥·ÅÁÐ±íÎ´¸üÐÂ£¡", "²¥·ÅÁÐ±íÒÑ±»¸üÐÂ£¡", "·µ»Ø", "²¥·ÅÁÐ±íÒÑÌí¼Ó£¡", "Çë¼ÇµÃË¢ÐÂÒ³Ãæ£¡", "ÕÊºÅ£º", "¼ÓÃÜ·ÃÎÊ£º", "Çë×¢Òâ£¡´ËÍøÕ¾²¢·Ç¹«¹²µÄ£¬ËùÓÐ²Ù×÷½«±»ÏµÍ³¼ÇÂ¼£¡", "µÇÂ½", "µÇÂ½ÐèÒªSSLÖ§³Ö£¡", "²¥·Å", "É¾³ý", "¹²Ïí£º", "±£´æ", "¿ØÖÆ²¥·ÅÁÐ±í£º¡°%1¡±-%2 ±êÌâ", "±à¼­ÈË£º", "²é¿´Õß£º", "Ñ¡Ôñ", "Ãë", "×´Ì¬", "ÐÅÏ¢", "É¾³ý", "Ãû³Æ", "×Ü¼Æ£º", "´íÎó", "µ±±»Ñ¡ÖÐÊ±£º", "¾ùºâ£º", "±à¼­²¥·ÅÁÐ±í", "É¾³ý´Ë¼ÍÂ¼", "Ìí¼Ó²¥·ÅÁÐ±í", "Ãû³Æ£º", "´´½¨", "ÕýÔÚ²¥·Å£º", "ÎÄ¼þ", "×¨¼­", "È«²¿", "±»Ñ¡ÖÐµÄ", "Ìí¼Ó", "²¥·Å", "±à¼­", "ÐÂ", "Ñ¡Ôñ£º", "²¥·Å¿ØÖÆ£º", "²¥·ÅÁÐ±í£º", "¼öÑ¡ÊýÄ¿", "Keyteq ÌáÊ¾Äã", "£¨¼ì²é¸üÐÂ£©", "ÍøÕ¾", "½öID3", "×¨¼­", "±êÌâ", "ÒÕÊõ¼Ò", "ÒÕÊõ¼Ò¼öÑ¡×¨¼­", "²é¿´", "±»¹²ÏíµÄ²¥·ÅÁÐ±í", "ÓÃ»§", "¹ÜÀíÔ±¿ØÖÆÃæ°å", "×î½ü¸üÐÂ", "ÈÈÁ¦ÍÆ¼ö", "ÍË³ö", "Ñ¡Ïî", "¼ì²é", "ÎÒµÄ", "±à¼­ÓÃ»§ÐÅÏ¢", "´´½¨ÐÂÓÃ»§ÕÊºÅ", "È«Ãû", "ÕÊºÅ", "¸ü¸ÄÃÜÂë£¿", "ÃÜÂë", "×¢ÊÍ", "·ÃÎÊÈ¨ÏÞ", "ÊÇ", "·ñ", "É¾³ýÓÃ»§", "Ê¹ÓÃ»§ÍË³ö", "Ë¢ÐÂ", "´´½¨ÐÂÓÃ»§ÕÊºÅ", "É¾³ý", "ÍË³ö", "Ê¹ÓÃEXTM3UÊôÐÔ£¨.m3u£©", "²é¿´ÐÐÊý£¨×îÈÈ/×îÐÂ£©", "×î´óËÑË÷ÐÐÊý", "ÖØÖÃ", "´ò¿ªÄ¿Â¼", "½øÈëµ½Ä¿Â¼£º%1", "ÏÂÔØ", "·µ»ØÉÏÒ»¼¶Ä¿Â¼", "·µ»Ø¸ùÄ¿Â¼", "¼ì²éÉý¼¶", "ÓÃ»§", "ÓïÑÔ", "Ñ¡Ïî", "ÒÑ±»ÏµÍ³Ìß³ö", "ÂÒÐò²¥·Å£º", "ÉèÖÃ", "¸ùÄ¿Â¼", "Á÷ÎÄ¼þÔ´", "È±Ê¡ÓïÑÔ", "WindowsÏµÍ³", "ÐèÒªHTTPS", "ÔÊÐíËÑË÷", "ÔÊÐíÏÂÔØ", "SessionÁ÷³Ì³¬Ê±", "±¨¸æÊ§°ÜµÄµÇÂ½³¢ÊÔÐÐÎª", "ÇëÉÔµÈ¡ª¡ªÕýÔÚ¶ÁÈ¡ÎÄ¼þÁÐ±í", "²¥·ÅÁÐ±íÎÞ·¨±»Ìí¼Ó£¡", "¹ÜÀíÔ±", "»»ÒÔHTTPS·½Ê½µÇÂ½", "Á÷ÒýÇæÉúÐ§", "±êÌâ", "ÒÕÊõ¼Ò", "×¨¼­", "×¢ÊÍ", "Äê", "Òô¹ì", "Á÷ÅÉ", "Î´ÉèÖÃ", "×î´óÏÂÔØËÙÂÊ(Kbps)", "ÓÃ»§", "%1 ·ÖÖÓ - %2 ¸ö±êÌâ", "%1 Ç§±ÈÌØ %2 ·ÖÖÓ", "Á÷ÅÉÁÐ±í", "È·¶¨", "%1d %2h %3m ²¥·ÅÊ±³¤ %4 ¸öÎÄ¼þ %5 Õ×", "Ã»ÓÐÏà¹Ø×ÊÔ´", "ÃÜÂëÒÑ¾­³É¹¦ÐÞ¸Ä£¡", "µÇÂ½", "ÇåÑ¡ÔñÒ»Ïî£¡", "×î½üÓÐÊ²Ã´¸üÐÂ£¿", "µã»÷ÕâÀï»ñÈ¡°ïÖú", "Ê¹ÓÃÀ©Õ¹Í¼ÏñÏÔÊ¾£¿", "À©Õ¹Í¼Æ¬Â·¾¶", "µ±Ç°ÃÜÂë", "µ±Ç°ÃÜÂë»¥²»Æ¥Åä£¡", "¿ÉÈ¡µÃµÄ´æµµ", "ÎÞ·¨´æµµ", "¿ÉÄÜÏàÍ¬µÄÎÄ¼þ%1-%2ÕÒµ½ÁË", "ÕæµÄÉ¾³ý²¥·ÅÁÐ±í£¿", "°´×ÖÄ¸Ë³ÐòÅÅÐò", "Ëæ»ú²¥·Å", "ÅÅÐò", "ÆðÔ´");

$klang[21]  = array("Catalan", "iso-8859-1", "Català", "El més nou", "Novetat", "Cerca", "(només es mostra %1)", "seg", "Resultats de la Recerca: '%1'", "trobat", "Cap.", "actualitza les opcions de recerca a la base de dades", "Esborrar registres no utilitzats?", "Regenerar ID3?", "Mode depuració?", "Actualitza", "Cancel·la", "Actualitza base de dades de recerca", "Trobats %1 fitxers.", "No puc determinar aquest fitxer: %1, l'ignoro.", "Instal·lat: %1 - Actualitzat: %2, Escanejat:", "Scanejat:", "Error - query: %1", "No puc llegir aquest arxiu: %1. L'ignoro.", "Esborrat: %1","Insertat %1, actualitzat %2, esborrat %3 amb %4 errors i %5 ignorats de %6 arxius - %7 seg - %8 marcats per esborrar.", "Fet", "Tanca", "No he trobat cap arxiu a: \"%1\"", "Entrar a kPlaylist", "Llista d'àlbums de l'artista: %1", "Marcat %1", "No s'han sel·leccionat cançons. Playlist no actualitzada.", "Playlist actualitzada!", "Tornar", "Playlist afegida!", "Recorda recarregar la pàgina.", "Entrar:", "Secret:", "Compte! Això és una WEB no pública. Totes les accions es registren. ", "Entrar", "Es requereix SSL per entrar.", "Reprodueix", "Esborra", "Compartit:", "Graba.", "Playlist de Control: \"%1\" - %2 títols", "Editor", "Visualitzador", "Sel·lecciona", "Seq", "Estat", "Info", "Esborra", "Nom", "Totals:", "Error", "Accions en sel·leccionar:", "Seqüència:", "edita Playlist","Esborra aquesta entrada", "afegeix playlist", "Nom:", "Crea", "Reprodueix:", "Arxiu", "Àlbum", "Tot", "Sel·leccionat", "afegeix", "reprodueix", "edita", "nou", "Sel·lecciona:", "Control de reproducció:", "Playlist;", "Sel·lecció numérica", "Keyteq et dona:", "(actualització de soft)", "Homesite", "només id3", "àlbum", "títol", "artista", "àlbum sel·leccionat de l'artista", "veure", "Playlists compartits", "Usuaris", "Control d'Administrador", "Que hi ha de nou", "Que hi ha novedos", "Sortir", "Opcions", "Txequeja", "Jo", "edita usuari", "nou usuari", "Nom complet", "Entrada", "Canviar password?", "Password", "Comentari", "Nivell d'accés", "On", "Off", "Esborrar usuari", "Desconnectar usuari", "Refrescar", "Nou usuari", "esborra", "sortir", "Utilitzar característiques EXTM3U?", "Mostrar quantes columnes (hot/nou)", "Màxim de columnes de recerca", "Resetejar", "Obrir directori", "Anar al directori: %1", "Descarregar", "Pujar un nivell", "Anar al directori root.", "Txequeja actualitzacions.", "usuaris", "Llenguatge", "opcions", "Iniciat", "Aleatori:", "Configuració", "directori base", "Localització d'Stream", "Llenguatge per defecte", "Sistema Windows", "Necessita HTTPS", "Permetre recerques", "Permetre descàrregues", "Temps de sessió (COOKIE)", "Reporta errors d'intent d'entrada", "Espera. Recuperant llista de fitxers.", "No es pot afegir la Playlist!", "Admin", "Entra per HTTPS per acceptar els canvis!", "Activa el motor d'streaming", "Títol", "Artista", "Àlbum", "Comentaris", "Any", "Pista", "Gènere", "no especificat", "Màxim ample de descàrrega (kbps)", "Usuari", "%1 mins - %2 títols", "%1 kbit %2 mins", "Llista de gèneres: %1", "Som-hi", "Temps de reproducció %1d %2h %3m %4 arxius %5 mb", "No hi ha arxius relevants.", "Password canviat!", "Signa", "Siusplau fes una sel·lecció!", "Que hi ha de nou?", "Clica aquí per a ajuda", "Utilitza imatges externes?", "Camí per a imatges externes", "Password actual", "Password actual no coincideix!", "Arxivador preferit", "No es pot crear l'arxiu", "Trobat un problable arxiu duplicat: %1 - %2", "Esborrar Playlist de debò?", "Alfabètic", "Al·leatori", "Ordena", "Original", "Utilitza javascript", "Estas segur que vols esborrar aquest usuari?", "Veure historial", "Historial", "Files", "Arxiu CCS extern");

$klang[22]  = array("Bulgarian", "windows-1251", "Áúëãàðñêè", "Êîå å ñóïåð?", "Êîå å íîâî?", "Òúðñåíå", "(only %1 shown)", "ñåê", "Ðåçóëòàò îò òúðñåíåòî: '%1' ", "íàìåðåí", "Íèùî.", "Îáíîâëåíèå íà áàçàòàäàííè çà òúðñåíå - îïöèÿ", "Èçòðèâàíå íà íåèçïîëçâàíèòå çàïèñè?", "Âúçñòàíîâÿâàì ID3? ", "Îòñòðàíÿâàíå íà äåôåêòèòå ?", "Îáíîâëåíèå", "Îòêàç", "Îáíîâëåíèå íà áàçàòàäàííè çà òúðñåíå", "Íàìåðåíè %1 ôàèëà. ", "Íåìîçå äà ñå îïðåäåëè òîçè ôàèë: %1, ïðîïóñíàò.  ", "Èíñòàëèðàí: %1 - Îáíîâëåíèå: %2, ñêàíèðàíå:", "Ñêàíèðàíå:", "Ãðåøêà - âúïðîñ: %1 ", "Òîçè ôàèë íåìîçå äà ñå ïðî÷åòå: %1. Ïðîïóñíàò.", "Ïðåìàõíàòè: %1 ","Âêàðàíè %1, îáíîâëåíèå %2, èçòðèòè %3 êúäå %4 failed and %5 skipped through %6 files - %7 sec - %8 marked for deletion", "Ãîòîâî", "Çàòâîðè", "Íåìîæå äà ñå íàìåðÿò íèêàêâè ôàèëîâå òóêà:  \"%1\" ", "kPlaylist Âõîä", "Ñïèñúê íà àëáóìèòå ïî ïåâöè: %1", "Áúðç èçáîð %1 ", "Íèêàêâè ïåñíè íå ñà èçáðàíè. Playlist íå å îáíîâåí. ", "Playlist îáíîâÿâàíå!", "Îáðàòíî", "Playlist äîáàâÿíå!", "Íåçàáðàâÿéòå äà ïðåçàðåäèòå ñòðàíèöàòà.", "Èìå:", "Ïàðîëà:", "Ñúîáùåíèå! Òîâà íå å îáùåñòâåí ñàéò. Âñè÷êó äåéñòâèÿ ñå çàïàçâàò.", "Âëåç", "Íåîáõîâèìî å SSL çà ôëèçàíå.", "Ïóñíè", "Èçòðèé", "Ñïîäåëè:", "Çàïàçè", "Óïðàâëåíèå playlist: \"%1\" - %2 çàãëàâèå", "Ðåäàêòîð", "Ïîãëåäíè", "Èçáåðè", "Ïîñëåä.", "Ñúñòîÿíèå", "Èíôîðìàöèÿ", "Èçòð.", "Èìå", "Îáùî:", "Ãðåøêà", "Äåéñòâèå íà èçáðàíîòî:", "Ïîñëåäîâàòåëíîñò:", "Ðåäàêöèÿ playlist ", "Èçòðèéòå òîâà", "äîáàâÿíå playlist ", "Èìå:", "Ñúçäàé", "Ïóñíè:", "Ôàéë", "Àëáóì", "Âñè÷êî", "Èçáåðè", "äîáàâè", "ïóñíè", "ðåäàêòèðàíå", "íîâ", "Èçáåðè:", "Êîíòðîë íà ïóñêàíå:", "Playlist:", "Áúðçî èçáèðàíå ïî íîìåð", "Keyteq gives you:", "(ïðîâåðè çà îáíîâÿâàíå)", "Homesite ", "ñàìî id3 ", "àëáóì", "çàãëàâèå", "ïåâåö", "Áúðçî èçáèðàíå ïî áóêâà", "âèæ", "Ñïîäåëè playlists ", "Ïîòðåáèòåëè", "Àäìèí. óïðàâëåíèå", "Êîå å íîâî?", "Êîå å ñóïåð?", "Èçëèçàíå", "Íàñòðîéêè", "Øàõ", "Ìîé", "ðåäàêòèðàíå íà ïîòðåáèòåëÿ", "íîâ ïîòðåáèòåë", "Ïúëíî èìå", "Èìå(íèê)", "Ñìÿíà íà ïàðîëàòà?", "Ïàðîëà", "Êîìåíòàð", "Èçáîð íà äîñòúïà", "Âêë.", "Èçêë.", "Èçòðèâàíå íà ïîòðåáèòåë", "Èçëèçàíå íà ïîòðåáèòåë", "Îïðåñíåíèå", "Íîâ ïîòðåáèòåë", "èçòð.", "èçõîä", "Èçïîëçâàíå íà Winàmp EXTM3U?", "Ïîêàçâàíå íà êîëêî êîëîíè (ãîðåùè/íîâè)", "Ìàêñèìóì êîëîíè íà òúðñåíå", "Ðåçåò", "Îòâîðè äèðåêòîðèÿ", "Îòèäè â äèðåêòîðèÿ:  %1 ", "Ñâàëÿíå", "Îòèäè åíà ñòúïêà íàãîðå", "Îòèäè â ãëàâíàòà äèðåêòîðèÿ.", "Ïðîâåðè îáíîâëåíèåòî", "ïîòðåáèëòåëè", "Åçèê", "Îïöèè", "Booted", "Shuffle:", "Íàñòðèéêè", "Ãëàâíà äèðåêòîðèÿ", "Stream location", "Åçèê ïî ïîäðàçáèðàíå", "A Windows system", "Èçèñêâàíå HTTPS ", "Allow seek", "Âèíàãè ñâàëÿíå", "Âðåìå íà ñåñèÿòà", "Ðàïîðò íà íåóñïåëèòå äà âëÿçàò", "Hold on - fetching file list", "Playlist  íåìîæå äà áúäå äîáàâåí!", "Àäìèíèñòðàòîð", "Âëåç ñ HTTPS çà ñìÿíà!", "Ðàçðåøåí streaming engine ", "Çàãëàâèå", "Ïåâåö", "Àëáóì", "Êîìåíòàð", "Ãîäèíà", "Ïåñåí", "Æàíð", "íå èçáðàíî", "Ìàêñ. ñêîðîñò íà ñâàëÿíð (kbps)", "Ïîòðåáèòåë", "%1 ìèí. - %2 çàãëàâèÿ ", "%1 kbit %2 ìèí. ", "Æàíðîâ ñïèñúê: %1", "Îòèäè", "%1äåí %2÷. %3ì. âðåìå çà ñëóøàíå %4 ôàéëîâå %5 ìá.", "No relevant resources here.", "Ïàðîëàòà å ñìåíåíà!", "Ðåãèñòðàöèÿ", "Ìîëå íàïðàâåòå èçáîð!", "Êàêâî ñå îáíîâëÿâà", "Íàòèñíåòå òóê çà ïîìîù", "Èçïîëçâàíå íà âúíøíè ñíèìêè?", "Ïúòÿ äî âúíøíèòå ñíèìêè", "Òåêóùà ïàðîëà", "Òåêóùàòà ïàðîëà íå ñúâïàäà!", "Preferred archiver", "Àðõèâà íåìîçå äà áúäå íàïðàâåí", "Probable file duplicate found:  %1 - %2", "Èñêàòå ëè äà èçòðèåòå playlist?", "Ïî àçáó÷åí ðåä", "Ðàáúðêàíî", "Ñîðòèðàíî", "Îðèãèíàë", "Èçïîëçâàé javascript ", "Ñèãóðåí ëè ñòå, ÷å èñêàòå äà èçòðèåòå òîçè ïîòðåáèòåë?", "Âèç èñòîðèÿòà", "èñòîðèÿ", "Êîëîíè", "Âúíøåí CSS ôàèë");

$klang[23]  = array("Polish", "iso-8859-1", "Polski", "Popularne", "Nowo&#347;ci", "Wyszukaj", "pokazano tylko %1", "sek", "Wyniki wyszukiwania: \'%1\'", "znaleziono", "Nic.", "aktualizacja opcji wyszukiwania bazy", "Usun&#261;&#263; nieu&#380;ywane wpisy?", "Odbudowa&#263; ID3?", "Tryb usuwania b&#322;&#281;dów?", "Aktualizacja", "Anuluj", "aktualizacja wyszukiwania bazy", "Znaleziono %1 plików", "Nie mo&#380;na okre&#380;li&#263; po&#322;o&#380;enia pliku: %1", "Instalacja: %1 - Aktualizacja: %2, badanie:", "Skanowanie:", "Niepowodzenie - pytanie: %1", "Nie mo&#380;na odczyta&#263; tego pliku: %1. Pomini&#281;cie.", "Usuni&#281;to: %1", "Wstawiono %1, uaktualniono %2, usuni&#281;to %3 gdzie %4 uszkodzonych i %5 pomini&#281;to z powodu %6 plików - %7 sek - %8 zaznaczonych do usuni&#281;cia. ", "Sko&#324;czone", "Zamknij", "Nie mo&#380;na znale&#378;&#263; tutaj &#380;adnych plików: \"%1\"", "Logowanie kPlaylist", "Lista albumów dla wykonawcy: %1", "popularny wybór %1", "nie wybrana melodia. Playlista nie zaktualizowana.", "Playlista zaktualizowana!", "Wstecz", "Playlista dodana!", "Pami&#281;taj o prze&#322;adowaniu strony", "login:", "has&#322;o:", "Uwaga! To nie jest strona publiczna. Wszystkie akcje s&#261; rejestrowane.", "Login", "Do zalogowania wymagany jest SSL", "Odgrywaj", "Usu&#324;", "Wspólny:", "Zapisz", "Kontrola playlist: \"%1\" - %2 tytu&#322;y", "Edytor", "Przegl&#261;darka", "Zaznacz", "Ci&#261;g", "Status", "Info", "Kasuj", "Nazwa", "Podsumowanie:", "B&#322;&#261;d", "Akcja na zaznaczonych:", "Kolejno&#347;&#263;", "edytuj playlist&#281;", "Usu&#324; ten zapis", "dodaj playlist&#281;", "Nazwa:", "Utwórz", "Odtwarzaj:", "Plik", "Album", "Wszystko", "Wybrane", "dodaj", "odtwarzaj", "edytuj", "nowe", "Zaznacz:", "Kontrol odtwarzania:", "Playlista:", "popularne numery", "Twój identyfikator:", "(sprawd&#378; czy s&#261; poprawki)", "Stona domowa", "tylko id3", "album", "tytu&#322;", "wykonawca", "Popularne albumy wykonawcy", "widok", "Wspólne playlisty", "U&#380;ytkownicy", "Panel administratora", "Nowo&#347;ci", "Popularne", "Wylogowanie", "Opcje", "Sprawd&#378;", "Mój", "edytuj u&#380;ytkownika", "nowy u&#380;ytkownik", "Pe&#322;na nazwa", "Login", "Zmieni&#263; has&#322;o?", "Has&#322;o", "Komentarz", "poziom dost&#281;pu", "W&#322;&#261;czony", "Wy&#322;&#261;czony", "Usu&#324; u&#380;ytkownika", "Wyloguj u&#380;ytkownika", "Od&#347;wie&#380;", "Nowy u&#380;ytkownik", "usu&#324;", "wyloguj", "Mo&#380;liwo&#347;&#263; u&#380;ycia EXTM3U?", "Ile pokaza&#263; wierszy (popularne/nowe)", "Max przeszukiwanych wierszy", "Resetuj", "Otwórz katalog", "Id&#378; do katalogu: %1", "Pobierz", "Id&#378; katalog wy&#380;ej", "Id&#378; do katalogu g&#322;ównego", "Sprawd&#378; czy s&#261; poprawki", "u&#380;ytkownicy", "J&#281;zyk", "opcje", "Inicjowanie", "Mieszanie:", "Ustawienia", "Katalog bazowy", "Lokalizacja strumienia", "Domy&#347;lny j&#281;zyk", "System Windows?", "Wymagane HTTPS", "Wszyscy mog&#261; ogl&#261;da&#263;", "Wszyscy mog&#261; &#347;ci&#261;ga&#263;", "Maksymalny czas sesji", "Raportuj b&#322;&#281;dne próby logowania", "W&#322;&#261;cz wstrzymywanie - najlepsze listy plików", "Playlista nie mo&#380;e by&#263; dodana!", "Administrator", "Zaloguj z HTTPS aby zmieni&#263;!", "Aktywny strumie&#324; silnika", "Tytu&#322;", "Wykonawca", "Album", "Komentarz", "Rok", "&#346;cie&#380;ka", "Rodzaj", "nie ustawione", "Max pr&#281;dko&#347;&#263; &#347;ci&#261;gania (kbps)", "U&#380;ytkownik", "%1 minuty - %2 tytu&#322;y", "%1 kbit %2 minuty", "Rodzaj listy: %1", "Id&#378;", "%1d %2h %3m czas odtwarzania %4 plików %5 MB", "Nie zwi&#261;zany z tymi zasobami", "Has&#322;o zmienione!", "Wy&#347;lij", "Prosz&#281; wykona&#263; zaznaczenie!", "Co to jest aktualizacja?", "Kliknij tutaj aby uzyska&#263; pomoc", "U&#380;y&#263; zewn&#281;trznych obrazków?", "&#346;cie&#380;ka zewn&#281;trznych obrazków", "Bie&#380;&#261;ce has&#322;o", "Bie&#380;&#261;ce has&#322;o nie jest w&#322;a&#347;ciwe!", "Preferowany archiwizator", "Archiwum nie mo&#380;e zosta&#263; utworzone", "Prawdopodobnie znaleziono duplikat pliku: %1 - %2", "Na pewno usun&#261;&#263; pleylist&#281;?", "Alfabetycznie", "Losowo", "Sortuj", "Oryginalnie", "U&#380;yj javascript", "Czy jeste&#347; pewny, &#380;e chcesz usun&#261;&#263; tego uzytkownika?", "Przegl&#261;daj histori&#281;", "historia", "Wiersze", 
"Zewn&#281;trzny plik CSS");

$klang[24]  = array("Lithuanian", "ISO-8859-13", "Lietuviškai", "Dažniausiai klausomi", "Nauja", "Paieška", "(rodoma tiktai %1)", "sec", "Paieškos rezultatai: \'%1\'", "rasta", "N&#279;ra.", "atnaujinti paiškos duomen&#371; baz&#279;s nustatymus", "Ištrinti nereikalingus &#303;rašus?", "Atnaujinti ID3?", "Su klaid&#371; aptikimu?", "Atnaujinti", "Nutraukti", "atnaujinti paieškos duomen&#371; baz&#281;", "Rasta %1 fail&#371;.", "Neina nustatyti šio failo: %1, praleidžiam.", "&#302;diegta: %1 - Atnaujinti: %2, skenuoti:", "Skenuoti:", "Nepavykusi užklausa: %1", "Neina perskaityt šio failo: %1. Praleidžiam.", "Pašalinta: %1", "&#302;traukta %1, atnaujinta %2, ištrinta %3 kur %4 nepavyk&#281; ir %5 praleisti iš %6 fail&#371; - %7 sec - %8 pažym&#279;ti ištrynimui.", "Atlikta", "Uždaryti", "Nepavyko rasti joki&#371; fail&#371; &#269;ia: \"%1\"", "kPlaylist Prisijungimas", "Album&#371; s&#261;rašas pagal autori&#371;: %1", "Populiariausi %1", "Nepažim&#279;jote n&#279; vieno failo. Playlist\'as neatnaujintas.", "Playlist\'as atnaujintas!", "Atgal", "&#302;trauktas Playlist\'as.", "Neužmirškite perkrauti puslapio.", "vartotojo vardas:", "slaptažodis:", "D&#279;mesio! Tai ne viešas interneto puslapis. Visi veiksmai yra &#303;rašomi.", "Prisijungti", "SSL reikia norint prisijungti.", "Groti", "Ištrinti", "Vieši:", "Išsaugoti", "Redaguojamas Playlist\'as: \"%1\" - %2 pavadinimai", "Redaktorius", "Peržvalga", "Pažym&#279;ti", "T&#281;sinys", "Pad&#279;tis", "Info", "Ištrinti", "Vardas", "Išviso:", "Klaida", "Atlikti veiksm&#261; su pažym&#279;tais:", "Eil&#279;s tvarka:", "redaguoti playlist\'&#261;", "Ištrinti š&#303; &#303;raš&#261;", "&#303;traukti playlist\'&#261;", "Vardas:", "Sukurti", "Groti:", "Failas", "Albumas", "Visi", "Pažym&#279;tus", "&#303;traukti", "groti", "redaguoti", "naujas", "Pažym&#279;ti:", "Grojimo valdymas:", "Playlist\'as:", "Pasirink&#371; numeravimas", "Keyteq si&#363;lo:", "(patikrinti ar naudoji naujausi&#261; versij&#261;)", "J&#363;s&#371; puslapis", "tiktai id3", "albumas", "pavainimas", "atlik&#279;jas", "Pažym&#279;ti atlik&#279;jo album&#261;", "perži&#363;r&#279;ti", "Vieši playlist\'ai", "Vartotojai", "Admin valdymas", "Naujienos", "Dažniausiai", "Atsijungti", "Nustatymai", "Pažym&#279;ti", "Mano", "redaguoti vartotoj&#261;", "naujas vartotojas", "Pilnas vardas", "Vartotojo vardas", "Pakeisti slaptažod&#303;?", "Slaptažodis", "Komentaras", "Vartotojo lygis", "&#302;jungta", "Išjungta", "Ištrinti vartotoj&#261;", "Atjungti vartotoj&#261;", "Perkrauti", "Naujas vartotojas", "ištrinti", "atjungti", "Naudoti EXTM3U?", "Kiek rodyti stulpeli&#371;?", "Daugiausia paieškos eilu&#269;i&#371;", "Atstatyti", "Atidaryti direktorij&#261;", "Eiti &#303;: %1", "Parsisi&#371;sti", "Vienu ejimu atgal", "&#302; root direktorij&#261;", "Patikrinti atnaujinim&#261;", "vartotojai", "Kalba", "nustatymai", "Pakrautas", "Maišyti:", "Nustatymai", "Pradin&#279; direktorija", "Stream vieta", "Pagrindin&#279; kalba", "Windows sistema", "Reikalauti HTTPS", "Leisti paiešk&#261;", "Leisti parsisiuntimus", "Session timeout", "Pranešti apie nepavykusius prisijungimus", "Palaukite - sudaromas fail&#371; s&#261;rašas", "Playlist\'o neina &#303;traukti", "Admin", "Prisijunkite su HTTPS nor&#279;dami k&#261; nors pakeisti!", "Leisti streming", "Pavadinimas", "Atlik&#279;jas", "Albumas", "Komentaras", "metai", "Takelis", "Žanras", "nenustatyta", "Didžiausias siuntimosi greitis", "Vartotojas", "%1 min - %2 pavadinimai", "% kbit %2 min", "Žanr&#371; s&#261;rašas: %1", "Eiti", "%1 d %2h %3m grojimo laikas %4 fail7 %5 mb", "N&#279;ra susijusi&#371; resurs&#371;.", "Slaptažodis pakeistas.", "Prisiregistruoti", "Pasirinkite!", "Kas yra - Atnaujinimas?", "Pagalba", "Naudoti išorinius paveiksliukus", "Išorini&#371; paveiksliuk&#371; vieta", "Dabartrinis slaptažodis", "Slaptažodžiai nesutampa", "Naudojamas archyvatorius", "Nepavyko sudaryti archyvo", "Grei&#269;iausiai rasti du vienodi failai: \"%1\" \"%2\"", "Ištrinti playlist\'&#261;?", "Alfabetiškai", "Atsitiktinai", "Sutraukti", "Orginaliai", "Naudoti javascript", "Ar tikrai norite ištrinti š&#303; vartotoj&#261;?", "Perži&#363;r&#279;ti istorij&#261;", "istorija", "Eilut&#279;s", "Išorinis CSS failas", "Ištrinti dublikatus", "Taip", "Klaida", "Stream", "(rodyti kaip)", "failai", "albumai", "%1d %2h %3m %4s", "Pagrindinis", "Redaguoti", "Fail&#371; palaikymas", "Paspauskite ant ? kad gaut pagalb&#261;.", "Automatinis Duomen&#371; baz&#279;s atnaujinimas", "Nusi&#371;sti failo pl&#279;tin&#303;", "Leisti neautorizuotus streamus", "&#302;traukti headerius", "Išorinis javascript", "Puslapis", "Rodyti Keuteq duoda tau", "Rodyti atnaujinim&#261;", "Rodyti statistik&#261;", "&#302;rašyti ID3v2 su streamu", "Leisti vartotoj&#371; prisiregistravim&#261;", "Fail&#371; tipai", "Taip", "Ne", "Pl&#279;tinys", "MIME", "&#302;traukti M3U", "redaguoti fail&#371; tip&#261;", "Tikrai?");

$klang[25]  = array("Thai", "ISO-8859-11", "&#3652;&#3607;&#3618;", "&#3617;&#3634;&#3651;&#3627;&#3617;&#3656;", "&#3617;&#3634;&#3649;&#3619;&#3591;", "&#3588;&#3657;&#3609;&#3627;&#3634;", "(&#3649;&#3626;&#3604;&#3591;&#3648;&#3593;&#3614;&#3634;&#3632; %1)", "&#3623;&#3636;&#3609;&#3634;&#3607;&#3637;", "&#3612;&#3621;&#3585;&#3634;&#3619;&#3588;&#3657;&#3609;&#3627;&#3634; :\'%1\'", "&#3614;&#3610;", "&#3652;&#3617;&#3656;", "&#3611;&#3619;&#3633;&#3610;&#3611;&#3619;&#3640;&#3591;&#3605;&#3633;&#3623;&#3648;&#3621;&#3639;&#3629;&#3585;&#3600;&#3634;&#3609;&#3586;&#3657;&#3629;&#3617;&#3641;&#3621;&#3626;&#3635;&#3627;&#3619;&#3633;&#3610;&#3588;&#3657;&#3609;&#3627;&#3634;", "&#3621;&#3610;&#3648;&#3619;&#3588;&#3588;&#3629;&#3619;&#3660;&#3604;&#3607;&#3637;&#3656;&#3652;&#3617;&#3656;&#3648;&#3588;&#3618;&#3651;&#3594;&#3657;", "&#3626;&#3619;&#3657;&#3634;&#3591; ID3 &#3651;&#3627;&#3617;&#3656;", "&#3648;&#3611;&#3636;&#3604; Debug Mode", "&#3611;&#3619;&#3633;&#3610;&#3611;&#3619;&#3640;&#3591;", "&#3618;&#3585;&#3648;&#3621;&#3636;&#3585;", "&#3611;&#3619;&#3633;&#3610;&#3611;&#3619;&#3640;&#3591;&#3600;&#3634;&#3609;&#3586;&#3657;&#3629;&#3617;&#3641;&#3621;&#3651;&#3609;&#3585;&#3634;&#3619;&#3588;&#3657;&#3609;&#3627;&#3634;", "&#3614;&#3610;&#3607;&#3633;&#3657;&#3591;&#3627;&#3617;&#3604; %1 &#3652;&#3615;&#3621;&#3660;", "&#3652;&#3617;&#3656;&#3626;&#3634;&#3617;&#3634;&#3619;&#3606;&#3619;&#3632;&#3610;&#3640;&#3652;&#3615;&#3621;&#3660; %1 , &#3586;&#3657;&#3634;&#3617;&#3652;&#3611;", "&#3605;&#3636;&#3604;&#3605;&#3633;&#3657;&#3591;: %1 -&#3611;&#3619;&#3633;&#3610;&#3611;&#3619;&#3640;&#3591;: %2 ,&#3605;&#3619;&#3623;&#3592;&#3627;&#3634;", "&#3605;&#3619;&#3623;&#3592;&#3627;&#3634;", "&#3585;&#3634;&#3619;&#3588;&#3657;&#3609;&#3627;&#3634;&#3612;&#3636;&#3604;&#3614;&#3621;&#3634;&#3604; :%1", "&#3652;&#3617;&#3656;&#3626;&#3634;&#3617;&#3634;&#3619;&#3606;&#3629;&#3656;&#3634;&#3609;&#3652;&#3615;&#3621;&#3660; : %1 &#3586;&#3657;&#3634;&#3617;&#3652;&#3611;", "&#3621;&#3610; %1", "&#3648;&#3614;&#3636;&#3656;&#3617; %1 ,&#3611;&#3619;&#3633;&#3610;&#3611;&#3619;&#3640;&#3591; %2,&#3621;&#3610; %3,&#3607;&#3637;&#3656; %4,&#3612;&#3636;&#3604;&#3614;&#3621;&#3634;&#3604;&#3649;&#3621;&#3632; %5 ,&#3586;&#3657;&#3634;&#3617;&#3652;&#3611; %6 &#3652;&#3615;&#3621;&#3660; %7 &#3623;&#3636;&#3609;&#3634;&#3607;&#3637; %8 &#3607;&#3635;&#3648;&#3588;&#3619;&#3639;&#3656;&#3629;&#3591;&#3627;&#3617;&#3634;&#3618;&#3648;&#3614;&#3639;&#3656;&#3629;&#3621;&#3610;", "&#3648;&#3619;&#3637;&#3618;&#3610;&#3619;&#3657;&#3629;&#3618;", "&#3611;&#3636;&#3604;", "&#3652;&#3617;&#3656;&#3614;&#3610;&#3652;&#3615;&#3621;&#3660;&#3652;&#3604;&#3654;&#3607;&#3637;&#3656;&#3617;&#3637;&#3626;&#3656;&#3623;&#3609;&#3611;&#3619;&#3632;&#3585;&#3629;&#3610; \"%1\"", "&#3648;&#3586;&#3657;&#3634;&#3626;&#3641;&#3656;&#3619;&#3632;&#3610;&#3610;", "&#3619;&#3634;&#3618;&#3585;&#3634;&#3619;&#3629;&#3633;&#3621;&#3610;&#3633;&#3617;&#3626;&#3635;&#3627;&#3619;&#3633;&#3610;&#3624;&#3636;&#3621;&#3611;&#3636;&#3609; : %1", "&#3606;&#3641;&#3585;&#3648;&#3621;&#3639;&#3629;&#3585;&#3617;&#3634;&#3585;&#3607;&#3637;&#3656;&#3626;&#3640;&#3604; %1", "&#3652;&#3617;&#3656;&#3614;&#3610;&#3619;&#3634;&#3618;&#3585;&#3634;&#3619;&#3607;&#3637;&#3656;&#3648;&#3621;&#3639;&#3629;&#3585; &#3619;&#3634;&#3618;&#3585;&#3634;&#3619;&#3648;&#3621;&#3656;&#3609;&#3652;&#3617;&#3656;&#3652;&#3604;&#3657;&#3617;&#3637;&#3585;&#3634;&#3619;&#3611;&#3619;&#3633;&#3610;&#3611;&#3619;&#3640;&#3591;", "&#3611;&#3619;&#3633;&#3610;&#3611;&#3619;&#3640;&#3591;&#3619;&#3634;&#3618;&#3585;&#3634;&#3619;&#3648;&#3621;&#3656;&#3609;&#3649;&#3621;&#3657;&#3623;", "&#3618;&#3657;&#3629;&#3618;&#3585;&#3621;&#3633;&#3610;", "&#3648;&#3614;&#3636;&#3656;&#3617;&#3648;&#3586;&#3657;&#3634;&#3619;&#3634;&#3618;&#3585;&#3634;&#3619;&#3648;&#3621;&#3656;&#3609;", "&#3629;&#3618;&#3656;&#3634;&#3621;&#3639;&#3617;&#3607;&#3637;&#3656;&#3592;&#3632;&#3648;&#3611;&#3636;&#3604;&#3627;&#3609;&#3657;&#3634;&#3605;&#3656;&#3634;&#3591;&#3609;&#3637;&#3657;&#3651;&#3627;&#3617;&#3656;&#3629;&#3637;&#3585;&#3588;&#3619;&#3633;&#3657;&#3591;", "&#3648;&#3586;&#3657;&#3634;&#3626;&#3641;&#3656;&#3619;&#3632;&#3610;&#3610; :", "&#3619;&#3627;&#3633;&#3626;&#3612;&#3656;&#3634;&#3609;", "&#3627;&#3617;&#3634;&#3618;&#3648;&#3627;&#3605;&#3640; : &#3648;&#3623;&#3655;&#3610;&#3648;&#3614;&#3592;&#3627;&#3609;&#3657;&#3634;&#3627;&#3609;&#3637;&#3657;&#3617;&#3636;&#3651;&#3594;&#3656;&#3627;&#3609;&#3657;&#3634;&#3626;&#3634;&#3608;&#3634;&#3619;&#3603;&#3632;&#3585;&#3634;&#3585;&#3619;&#3632;&#3607;&#3635;&#3607;&#3635;&#3607;&#3633;&#3657;&#3591;&#3627;&#3617;&#3604;&#3592;&#3632;&#3606;&#3647;&#3585;&#3610;&#3633;&#3609;&#3607;&#3638;&#3585;&#3652;&#3623;&#3657;", "&#3648;&#3586;&#3657;&#3634;&#3626;&#3641;&#3656;&#3619;&#3632;&#3610;&#3610;", "&#3605;&#3657;&#3629;&#3591;&#3585;&#3634;&#3619; SSL &#3648;&#3614;&#3639;&#3656;&#3629;&#3585;&#3634;&#3619;&#3648;&#3586;&#3657;&#3634;&#3626;&#3641;&#3656;&#3619;&#3632;&#3610;&#3610;", "&#3648;&#3621;&#3656;&#3609;", "&#3621;&#3610;", "&#3629;&#3609;&#3640;&#3597;&#3634;&#3605;&#3651;&#3627;&#3657;&#3612;&#3641;&#3657;&#3629;&#3639;&#3656;&#3609;&#3651;&#3594;&#3657;&#3604;&#3657;&#3623;&#3618;&#3652;&#3604;&#3657;", "&#3610;&#3633;&#3609;&#3607;&#3638;&#3585;", "&#3588;&#3623;&#3610;&#3588;&#3640;&#3617;&#3619;&#3634;&#3618;&#3585;&#3634;&#3619;&#3648;&#3621;&#3656;&#3609; : \"%1\" - %2 &#3594;&#3639;&#3656;&#3629;", "&#3585;&#3634;&#3619;&#3649;&#3585;&#3657;&#3652;&#3586;", "&#3604;&#3641;", "&#3648;&#3621;&#3639;&#3629;&#3585;", "&#3621;&#3635;&#3604;&#3633;&#3610;", "&#3626;&#3606;&#3634;&#3609;&#3632;", "&#3619;&#3634;&#3618;&#3621;&#3632;&#3648;&#3629;&#3637;&#3618;&#3604;", "&#3621;&#3610;", "&#3594;&#3639;&#3656;&#3629;", "&#3607;&#3633;&#3657;&#3591;&#3627;&#3617;&#3604; :", "&#3612;&#3636;&#3604;&#3614;&#3621;&#3634;&#3604;", "&#3585;&#3634;&#3619;&#3585;&#3619;&#3632;&#3607;&#3635;&#3610;&#3609;&#3585;&#3634;&#3619;&#3648;&#3621;&#3639;&#3629;&#3585;", "&#3621;&#3635;&#3604;&#3633;&#3610;&#3607;&#3637;&#3656; :", "&#3649;&#3585;&#3657;&#3652;&#3586;&#3619;&#3634;&#3618;&#3585;&#3634;&#3619;&#3648;&#3621;&#3656;&#3609;", "&#3621;&#3610;&#3607;&#3633;&#3657;&#3591;&#3627;&#3617;&#3604;", "&#3648;&#3614;&#3636;&#3656;&#3617;&#3648;&#3586;&#3657;&#3634;&#3626;&#3641;&#3656;&#3619;&#3634;&#3618;&#3585;&#3634;&#3619;&#3648;&#3621;&#3656;&#3609;", "&#3594;&#3639;&#3656;&#3629; :", "&#3626;&#3619;&#3657;&#3634;&#3591;", "&#3648;&#3621;&#3656;&#3609; :", "&#3652;&#3615;&#3621;&#3660;", "&#3629;&#3633;&#3621;&#3610;&#3633;&#3617;", "&#3607;&#3633;&#3657;&#3591;&#3627;&#3617;&#3604;", "&#3606;&#3641;&#3585;&#3648;&#3621;&#3639;&#3629;&#3585;", "&#3648;&#3614;&#3636;&#3656;&#3617;", "&#3648;&#3621;&#3656;&#3609;", "&#3649;&#3585;&#3657;&#3652;&#3586;", "&#3651;&#3627;&#3617;&#3656;", "&#3648;&#3621;&#3639;&#3629;&#3585; :", "&#3588;&#3623;&#3610;&#3588;&#3640;&#3617;&#3585;&#3634;&#3619;&#3648;&#3621;&#3656;&#3609; :", "&#3619;&#3634;&#3618;&#3585;&#3634;&#3619;&#3648;&#3621;&#3656;&#3609; :", "&#3627;&#3617;&#3634;&#3618;&#3648;&#3621;&#3586;&#3607;&#3637;&#3656;&#3606;&#3641;&#3585;&#3648;&#3621;&#3639;&#3629;&#3585;&#3617;&#3634;&#3585;&#3607;&#3637;&#3656;&#3626;&#3640;&#3604;", "&#3588;&#3635;&#3649;&#3609;&#3632;&#3609;&#3635;&#3592;&#3634;&#3585; Keyteq", "&#3605;&#3619;&#3623;&#3592;&#3626;&#3629;&#3610;&#3648;&#3614;&#3639;&#3656;&#3629;&#3611;&#3619;&#3633;&#3610;&#3611;&#3619;&#3640;&#3591;&#3619;&#3640;&#3656;&#3609;&#3586;&#3629;&#3591;&#3595;&#3629;&#3615;&#3607;&#3660;&#3649;&#3623;&#3619;&#3660;", "&#3627;&#3609;&#3657;&#3634;&#3627;&#3621;&#3633;&#3585;", "&#3648;&#3593;&#3614;&#3634;&#3632; ID3", "&#3629;&#3633;&#3621;&#3610;&#3633;&#3617;", "&#3594;&#3639;&#3656;&#3629;&#3629;&#3633;&#3621;&#3610;&#3633;&#3617;", "&#3624;&#3636;&#3621;&#3611;&#3636;&#3609;", "&#3629;&#3633;&#3621;&#3611;&#3633;&#3617;&#3607;&#3637;&#3656;&#3606;&#3641;&#3585;&#3648;&#3621;&#3639;&#3629;&#3585;&#3607;&#3634;&#3585;&#3607;&#3637;&#3656;&#3626;&#3640;&#3604;&#3592;&#3634;&#3585;&#3624;&#3636;&#3621;&#3611;&#3636;&#3609;", "&#3648;&#3586;&#3657;&#3634;&#3594;&#3617;", "&#3619;&#3634;&#3618;&#3585;&#3634;&#3619;&#3648;&#3621;&#3656;&#3609;&#3607;&#3637;&#3656;&#3651;&#3627;&#3657;&#3651;&#3594;&#3657;&#3652;&#3604;&#3657;", "&#3612;&#3641;&#3657;&#3651;&#3594;&#3657;", "&#3626;&#3656;&#3623;&#3609;&#3612;&#3641;&#3657;&#3604;&#3641;&#3649;&#3621;&#3619;&#3632;&#3610;&#3610;", "&#3617;&#3634;&#3651;&#3627;&#3617;&#3656;", "&#3617;&#3634;&#3649;&#3619;&#3591;", "&#3629;&#3629;&#3585;&#3592;&#3634;&#3585;&#3619;&#3632;&#3610;&#3610;", "&#3605;&#3633;&#3623;&#3648;&#3621;&#3639;&#3629;&#3585;", "&#3605;&#3619;&#3623;&#3592;&#3626;&#3629;&#3610;", "&#3588;&#3635;&#3626;&#3633;&#3656;&#3591;&#3629;&#3639;&#3656;&#3609;", "&#3649;&#3585;&#3657;&#3652;&#3586;&#3612;&#3641;&#3657;&#3651;&#3594;&#3657;", "&#3648;&#3614;&#3636;&#3656;&#3617;&#3612;&#3641;&#3657;&#3651;&#3594;&#3657;", "&#3594;&#3639;&#3656;&#3629;&#3592;&#3619;&#3636;&#3591;", "&#3594;&#3639;&#3656;&#3629;&#3648;&#3614;&#3639;&#3656;&#3629;&#3648;&#3586;&#3657;&#3634;&#3619;&#3632;&#3610;&#3610;", "&#3648;&#3611;&#3621;&#3637;&#3656;&#3618;&#3609;&#3619;&#3627;&#3633;&#3626;&#3612;&#3656;&#3634;&#3609;?", "&#3619;&#3627;&#3633;&#3626;&#3612;&#3656;&#3634;&#3609;", "&#3586;&#3657;&#3629;&#3648;&#3626;&#3609;&#3629;&#3649;&#3609;&#3632;", "&#3619;&#3632;&#3604;&#3633;&#3610;&#3651;&#3609;&#3585;&#3634;&#3619;&#3651;&#3594;&#3657;&#3591;&#3634;&#3609;", "&#3585;&#3635;&#3621;&#3633;&#3591;&#3651;&#3594;&#3657;&#3591;&#3634;&#3609;&#3629;&#3618;&#3641;&#3656;", "&#3652;&#3617;&#3656;&#3652;&#3604;&#3657;&#3651;&#3594;&#3657;", "&#3621;&#3610;&#3612;&#3641;&#3657;&#3651;&#3594;&#3657;", "&#3609;&#3635;&#3612;&#3641;&#3657;&#3651;&#3594;&#3657;&#3629;&#3629;&#3585;&#3592;&#3634;&#3585;&#3585;&#3634;&#3619;&#3651;&#3594;&#3657;&#3591;&#3634;&#3609;", "refresh", "&#3648;&#3614;&#3636;&#3656;&#3617;&#3612;&#3641;&#3657;&#3651;&#3594;&#3657;&#3651;&#3627;&#3617;&#3656;", "&#3621;&#3610;&#3629;&#3629;&#3585;&#3592;&#3634;&#3585;&#3619;&#3632;&#3610;&#3610;", "&#3629;&#3629;&#3585;&#3592;&#3634;&#3585;&#3619;&#3632;&#3610;&#3610;", "&#3651;&#3594;&#3657;&#3588;&#3640;&#3603;&#3626;&#3617;&#3610;&#3633;&#3605;&#3636; EXTM3U", "&#3592;&#3635;&#3609;&#3623;&#3609;&#3649;&#3606;&#3623;&#3607;&#3637;&#3656;&#3605;&#3657;&#3629;&#3591;&#3585;&#3634;&#3619;&#3651;&#3627;&#3657;&#3649;&#3626;&#3604;&#3591; (&#3617;&#3634;&#3651;&#3627;&#3617;&#3656;/&#3617;&#3634;&#3649;&#3619;&#3591;)", "&#3592;&#3635;&#3609;&#3623;&#3609;&#3649;&#3606;&#3623;&#3626;&#3641;&#3591;&#3626;&#3640;&#3604;&#3651;&#3609;&#3585;&#3634;&#3619;&#3588;&#3657;&#3609;&#3627;&#3634;", "&#3618;&#3585;&#3648;&#3621;&#3636;&#3585;", "&#3648;&#3611;&#3636;&#3604; Directory", "&#3652;&#3611; Directory  : %1", "&#3604;&#3634;&#3623;&#3609;&#3660;&#3650;&#3627;&#3621;&#3604;", "&#3586;&#3638;&#3657;&#3609;&#3652;&#3611; 1 &#3619;&#3632;&#3604;&#3633;&#3610;", "&#3652;&#3611;&#3607;&#3637;&#3656; Directory &#3610;&#3609;&#3626;&#3640;&#3604;", "&#3605;&#3619;&#3623;&#3592;&#3626;&#3629;&#3610;&#3648;&#3614;&#3639;&#3656;&#3629;&#3611;&#3619;&#3633;&#3610;&#3611;&#3619;&#3640;&#3591;&#3619;&#3640;&#3656;&#3609;&#3586;&#3629;&#3591;&#3595;&#3629;&#3615;&#3607;&#3660;&#3649;&#3623;&#3619;&#3660;", "&#3612;&#3641;&#3657;&#3651;&#3594;&#3657;", "&#3616;&#3634;&#3625;&#3634;", "&#3605;&#3633;&#3623;&#3648;&#3621;&#3639;&#3629;&#3585;", "&#3648;&#3619;&#3636;&#3656;&#3617;&#3605;&#3657;&#3609;", "&#3626;&#3640;&#3656;&#3617;", "&#3585;&#3634;&#3619;&#3605;&#3633;&#3657;&#3591;&#3588;&#3656;&#3634;", "Directory &#3648;&#3585;&#3655;&#3610;&#3626;&#3639;&#3656;&#3629;", "&#3649;&#3627;&#3621;&#3656;&#3591; Stream", "&#3616;&#3634;&#3625;&#3605;&#3633;&#3657;&#3591;&#3605;&#3657;&#3609;", "&#3619;&#3632;&#3610;&#3610;  Windows", "&#3605;&#3657;&#3629;&#3591;&#3585;&#3634;&#3619;&#3651;&#3594;&#3657; Https", "&#3629;&#3609;&#3640;&#3597;&#3634;&#3605;&#3651;&#3627;&#3657;&#3648;&#3621;&#3639;&#3656;&#3629;&#3609;&#3648;&#3614;&#3621;&#3591;&#3652;&#3604;&#3657;", "&#3629;&#3609;&#3640;&#3597;&#3634;&#3605;&#3651;&#3627;&#3657;&#3604;&#3634;&#3623;&#3609;&#3660;&#3650;&#3627;&#3621;&#3604;&#3652;&#3604;&#3657;", "Session timeout", "&#3619;&#3634;&#3618;&#3591;&#3634;&#3609;&#3585;&#3634;&#3619; login &#3607;&#3637;&#3656;&#3612;&#3636;&#3604;&#3614;&#3621;&#3634;&#3604;", "&#3619;&#3629;&#3626;&#3633;&#3585;&#3588;&#3619;&#3641;&#3656;&#3585;&#3635;&#3621;&#3633;&#3591;&#3629;&#3656;&#3634;&#3609;&#3588;&#3656;&#3634;&#3592;&#3634;&#3585;&#3619;&#3632;&#3610;&#3610;", "&#3652;&#3617;&#3656;&#3626;&#3634;&#3617;&#3634;&#3619;&#3606;&#3648;&#3614;&#3636;&#3656;&#3617;&#3651;&#3609;&#3619;&#3634;&#3618;&#3585;&#3634;&#3619;&#3648;&#3621;&#3656;&#3609;&#3652;&#3604;&#3657;", "&#3612;&#3641;&#3657;&#3604;&#3641;&#3649;&#3621;&#3619;&#3632;&#3610;&#3610;", "&#3585;&#3619;&#3640;&#3603;&#3634;&#3648;&#3586;&#3657;&#3634;&#3619;&#3632;&#3610;&#3610;&#3604;&#3657;&#3623;&#3618; HTTPS &#3648;&#3614;&#3639;&#3656;&#3629;&#3648;&#3611;&#3621;&#3637;&#3656;&#3618;&#3609;", "&#3629;&#3609;&#3640;&#3597;&#3634;&#3605;&#3636;&#3651;&#3627;&#3657;&#3651;&#3594;&#3657; stream engine", "&#3594;&#3639;&#3656;&#3629;&#3648;&#3614;&#3621;&#3591;", "&#3624;&#3636;&#3621;&#3611;&#3636;&#3609;", "&#3629;&#3633;&#3621;&#3610;&#3633;&#3617;", "&#3586;&#3657;&#3629;&#3648;&#3626;&#3609;&#3629;&#3632;&#3649;&#3609;&#3632;", "&#3611;&#3637;", "&#3648;&#3614;&#3621;&#3591;&#3607;&#3637;&#3656;", "&#3649;&#3609;&#3623;", "&#3652;&#3617;&#3656;&#3605;&#3633;&#3657;&#3591;", "&#3588;&#3656;&#3634;&#3626;&#3641;&#3591;&#3626;&#3640;&#3604;&#3651;&#3609;&#3585;&#3634;&#3619;&#3604;&#3634;&#3623;&#3609;&#3660;&#3650;&#3627;&#3621;&#3604; (kbps)", "&#3612;&#3641;&#3657;&#3651;&#3594;&#3657;", "%1 &#3609;&#3634;&#3607;&#3637; - %2 &#3648;&#3614;&#3621;&#3591;", "%1 Kbit %2 &#3609;&#3634;&#3607;&#3637;", "&#3649;&#3609;&#3623;&#3648;&#3614;&#3621;&#3591; : %1", "wx", "%1 &#3623;&#3633;&#3609; %2 &#3594;&#3633;&#3656;&#3623;&#3650;&#3617;&#3591; %3 &#3609;&#3634;&#3607;&#3637; &#3651;&#3609;&#3585;&#3634;&#3619;&#3648;&#3621;&#3656;&#3609; %4 &#3652;&#3615;&#3621;&#3660; %5 mb", "&#3652;&#3617;&#3656;&#3614;&#3610;&#3626;&#3639;&#3656;&#3629;&#3607;&#3637;&#3656;&#3626;&#3634;&#3617;&#3634;&#3619;&#3648;&#3621;&#3656;&#3609;&#3652;&#3604;&#3657;", "&#3619;&#3627;&#3633;&#3626;&#3612;&#3656;&#3634;&#3609;&#3606;&#3641;&#3585;&#3648;&#3611;&#3621;&#3637;&#3656;&#3618;&#3609;&#3649;&#3621;&#3657;&#3623;", "&#3621;&#3591;&#3607;&#3632;&#3648;&#3610;&#3637;&#3618;&#3609;", "&#3585;&#3619;&#3640;&#3603;&#3634;&#3607;&#3635;&#3585;&#3634;&#3619;&#3648;&#3621;&#3639;&#3629;&#3585;&#3585;&#3656;&#3629;&#3609;", "&#3617;&#3637;&#3629;&#3632;&#3652;&#3619;&#3651;&#3627;&#3617;&#3656;", "&#3588;&#3621;&#3636;&#3585;&#3607;&#3637;&#3656;&#3609;&#3637;&#3656;&#3648;&#3614;&#3639;&#3656;&#3629;&#3586;&#3629;&#3588;&#3623;&#3634;&#3617;&#3594;&#3656;&#3623;&#3618;&#3648;&#3627;&#3621;&#3639;&#3629;", "&#3651;&#3594;&#3657;&#3619;&#3641;&#3611;&#3616;&#3634;&#3614;&#3592;&#3634;&#3585;&#3616;&#3634;&#3618;&#3609;&#3629;&#3585;", "&#3649;&#3627;&#3621;&#3656;&#3591;&#3586;&#3657;&#3629;&#3617;&#3641;&#3621;&#3619;&#3641;&#3611;&#3616;&#3634;&#3614;", "&#3619;&#3627;&#3633;&#3626;&#3612;&#3656;&#3634;&#3609;&#3648;&#3604;&#3636;&#3617;", "&#3619;&#3627;&#3633;&#3626;&#3612;&#3656;&#3634;&#3609;&#3648;&#3604;&#3636;&#3617;&#3652;&#3617;&#3656;&#3606;&#3641;&#3585;&#3605;&#3657;&#3629;&#3591;", "&#3619;&#3641;&#3611;&#3649;&#3610;&#3610;&#3585;&#3634;&#3619;&#3610;&#3637;&#3610;&#3629;&#3633;&#3604;", "&#3652;&#3617;&#3656;&#3626;&#3634;&#3617;&#3634;&#3619;&#3606;&#3607;&#3635;&#3585;&#3634;&#3619;&#3610;&#3637;&#3610;&#3629;&#3633;&#3604;&#3652;&#3615;&#3621;&#3660;&#3652;&#3604;&#3657;", "&#3614;&#3610;&#3652;&#3615;&#3621;&#3660;&#3607;&#3637;&#3656;&#3595;&#3657;&#3635;&#3585;&#3633;&#3609;&#3588;&#3639;&#3629;: \"%1\" \"%2\"", "&#3588;&#3640;&#3603;&#3649;&#3609;&#3656;&#3651;&#3592;&#3627;&#3619;&#3639;&#3629;&#3623;&#3656;&#3634;&#3592;&#3632;&#3621;&#3610;&#3619;&#3634;&#3618;&#3585;&#3634;&#3619;&#3648;&#3621;&#3656;&#3609;", "&#3648;&#3619;&#3637;&#3618;&#3591;&#3605;&#3634;&#3617;&#3621;&#3635;&#3604;&#3633;&#3610;&#3605;&#3633;&#3623;&#3629;&#3633;&#3585;&#3625;&#3619;", "&#3626;&#3640;&#3656;&#3617;", "&#3648;&#3619;&#3637;&#3618;&#3591;&#3621;&#3635;&#3604;&#3633;&#3610;", "&#3607;&#3637;&#3656;&#3617;&#3634;", "&#3651;&#3594;&#3657; javascript", "&#3588;&#3640;&#3603;&#3649;&#3609;&#3656;&#3651;&#3592;&#3623;&#3656;&#3634;&#3592;&#3632;&#3621;&#3610;&#3612;&#3641;&#3657;&#3651;&#3594;&#3657;&#3609;&#3637;&#3657;&#3629;&#3629;&#3585;&#3592;&#3634;&#3585;&#3619;&#3632;&#3610;&#3610;", "&#3604;&#3641;&#3611;&#3619;&#3632;&#3623;&#3633;&#3605;&#3636;&#3585;&#3634;&#3619;&#3651;&#3594;&#3657;", "&#3611;&#3619;&#3632;&#3623;&#3633;&#3605;&#3636;&#3585;&#3634;&#3619;&#3651;&#3594;&#3657;", "&#3627;&#3621;&#3633;&#3585;", "&#3651;&#3594;&#3657; css &#3616;&#3634;&#3618;&#3609;&#3629;&#3585;", "&#3621;&#3610;&#3607;&#3637;&#3656;&#3595;&#3657;&#3635;&#3585;&#3633;&#3609;", "&#3605;&#3585;&#3621;&#3591;", "&#3612;&#3636;&#3604;&#3614;&#3621;&#3634;&#3604;", "Stream", "(&#3649;&#3626;&#3604;&#3591;&#3649;&#3610;&#3610;)", "&#3652;&#3615;&#3621;&#3660;", "&#3629;&#3633;&#3621;&#3610;&#3633;&#3617;", " %1 &#3623;&#3633;&#3609; %2 &#3594;&#3633;&#3656;&#3623;&#3650;&#3617;&#3591; %3 &#3609;&#3634;&#3607;&#3637; %4 &#3623;&#3636;&#3609;&#3634;&#3607;&#3637; ", "&#3607;&#3633;&#3656;&#3623;&#3652;&#3611;", "&#3611;&#3619;&#3633;&#3610;&#3649;&#3605;&#3656;&#3591;", "Filehandling", "&#3588;&#3621;&#3636;&#3585; ? &#3648;&#3614;&#3639;&#3656;&#3629;&#3586;&#3629;&#3588;&#3623;&#3634;&#3617;&#3594;&#3656;&#3623;&#3618;&#3648;&#3627;&#3621;&#3639;&#3629;", "Sync &#3600;&#3634;&#3609;&#3586;&#3657;&#3629;&#3617;&#3641;&#3621;&#3629;&#3633;&#3605;&#3650;&#3609;&#3617;&#3633;&#3605;&#3636;", "&#3626;&#3656;&#3591;&#3626;&#3656;&#3623;&#3609;&#3586;&#3618;&#3634;&#3618;&#3652;&#3615;&#3621;&#3660;", "&#3629;&#3609;&#3640;&#3597;&#3634;&#3605; stream &#3607;&#3637;&#3656;&#3652;&#3617;&#3656;&#3652;&#3604;&#3657;&#3619;&#3633;&#3610;&#3585;&#3634;&#3619;&#3605;&#3619;&#3623;&#3592;&#3626;&#3629;&#3610;", "Include headers", "javascript &#3616;&#3634;&#3618;&#3609;&#3629;&#3585; ", "&#3627;&#3609;&#3657;&#3634;&#3627;&#3621;&#3633;&#3585;", "&#3649;&#3626;&#3604;&#3591;&#3626;&#3656;&#3623;&#3609; Keyteq &#3609;&#3635;&#3648;&#3626;&#3609;&#3629;", "&#3649;&#3626;&#3604;&#3591;&#3626;&#3656;&#3623;&#3609;&#3611;&#3619;&#3633;&#3610;&#3611;&#3619;&#3640;&#3591;&#3619;&#3632;&#3610;&#3610;", "&#3649;&#3626;&#3604;&#3591;&#3626;&#3606;&#3636;&#3605;&#3636;", "&#3648;&#3586;&#3637;&#3618;&#3609; ID3v2 &#3604;&#3657;&#3623;&#3618; stream", "&#3612;&#3641;&#3657;&#3651;&#3594;&#3657;&#3626;&#3634;&#3617;&#3634;&#3619;&#3606;&#3621;&#3591;&#3607;&#3632;&#3648;&#3610;&#3637;&#3618;&#3609;&#3652;&#3604;&#3657;", "&#3594;&#3636;&#3604;&#3652;&#3615;&#3621;&#3660;", "&#3651;&#3594;&#3656;", "&#3652;&#3617;&#3656;&#3651;&#3594;&#3656;", "&#3626;&#3656;&#3623;&#3609;&#3586;&#3618;&#3634;&#3618;", "MIME", "&#3619;&#3623;&#3617;&#3651;&#3609; M3U", "&#3649;&#3585;&#3657;&#3651;&#3586;&#3594;&#3609;&#3636;&#3604;&#3652;&#3615;&#3621;&#3660;", "&#3649;&#3609;&#3656;&#3651;&#3592;&#3627;&#3619;&#3639;&#3629;&#3652;&#3617;&#3656;");

$klang[26]  = array("NewNorwegian", "ISO-8859-1", "Nynorsk", "Kva er mest spelt", "Kva er nytt", "Søk", "(berre %1 vist)", "sek", "Søkeresultat", "Fann", "Ingen.", "Oppdater søkjedatabasevalg", "Slett ubrukte rader", "Ombygg ID3?", "Debug modus", "Oppdater", "Avbryt", "Oppdaterer søkje database", "Fann %1 filer", "kunne ikkje lese fil: %1, hoppa over", "Installert: %1 - Oppdaterar: %2, skanner:", "Skanner:", "Feilet - query: %1", "Kunne ikkje lese denne fila: %1. Hoppa over", "Fjerna: %1", "La inn %1, oppdaterte %2, sletta %3 der %4 feila og %5 blei hoppa over igjennom %6 filer - %7 sek - %8 markert for sletting", "Ferdig", "Steng", "Kunne ikkje finne filer her: \"%1\"", "kPlaylist Innlogging", "Albumliste frå artist: %1", "Snøggvelg %1", "Ingen låtar valgt. Låtliste ikkje oppdatert.", "Låtliste oppdatert!", "Tilbake", "Låtliste lagt til!", "Husk å oppdatere sida.", "Logg inn:", "Hemmeleg", "Advarsel! Dette er ei privat vevside. Alle handligar blir logga", "Logg inn", "SSL krevast for pålogging", "Spel", "Slett", "Delte:", "Lagre", "Kontroller låtliste: \"%1\" - %2 titler", "Editor", "Visar", "Velg", "Sek", "Status", "Informasjon", "Slett", "Navn", "Totalt:", "Feil", "Handling på valgte:", "Sekvens", "rediger låtliste", "Slett denne rekkja", "ny låtliste", "Navn:", "Lag", "Spel:", "Fil", "Album", "Alle", "Valde", "Legg til", "Spel", "rediger", "ny", "Velg", "Spele kontroll:", "Låtliste:", "Hurtigvelg numerisk", "Keyteq gir deg:", "(sjekk for ny versjon)", "Vevside", "Berre id3", "album", "tittel", "artist", "Hurtigvelg album frå artist", "vis", "Delte låtlister", "Brukarar", "Admin kontroll", "Kva er nytt", "Mest spelt", "Logg ut", "Valg", "Sjekk", "Min", "Rediger brukar", "ny brukar", "Fullt namn", "Brukarnamn", "Endre passord?", "passord", "kommentar", "Aksessnivå", "På", "Av", "Slett brukar", "Logg ut brukar", "Oppdater", "Ny brukar", "slett", "logg ut", "bruk EXTM3U eigenskap?", "Vise kor manger rekkejer (mest spelt/nytt)", "Maks søke rekkjer", "Omsetting", "Åpne katalog", "Gå til katalog: %1", "Last ned", "Gå eit steg opp", "Gå til kjerne katalog", "Sjekk for ny versjon", "brukarar", "Språk", "valg", "Avsperra", "Omskuff:", "Instillingar", "Arkiv katalog", "Nedlastingslokalisasjon", "Standardspråk", "Et Windows system", "Krev HTTPS", "Tillat spoling", "Tillat nedlastingar", "Inloggingstidsavbrudd", "Rapportere mislykka påloggingsforsøk", "Vent - Lagar filliste", "Speleliste kunne ikkje bli lagt til!", "Admin", "Logg inn med HTTPS for å endre", "Aktiver innebygd kanalvirkning", "Tittel", "Artist", "Album", "Kommentar", "År", "Låtnummer", "Stil", "ikkje sett", "Maksimal nedlastingshastigheit", "Brukar", "%1 minutt - %2 titlar", "%1 kbit %2 minutt", "Stilliste: %1", "Gå", "%1d %2t %3m speletid %4 %5 mb", "Ingen relevante ressursar her.", "Passord endra!", "Ny brukar", "Vennligst forta eit valg!", "Kva er oppdatering?", "Klikk her for hjelp", "Bruke eksterne bileter?", "Eksterne bilder lokalisering", "Eksisterande passord", "Det eksisterande passord stemmer ikkje!", "Ønsket arkiverer", "Arkiv kunne ikkje bli laga", "Sannsynleg duplikat blei funne: %1 - %2", "Verkeleg slette speleliste?", "Alfabetisk", "Tilfeldeg", "Sorter", "Original", "Bruke javascript", "Sikker på at du vil slette denne brukaren", "Vis historia", "historie", "Rekkjer", "Ekstern CSS fil", "Fjern duplikatar", "OK", "FEIL", "Stream", "(vis som)", "fielr", "album", "%1d %2t %3m %4s", "Generelt", "Skreddarsy", "Filhandtering", "Trykk på ? for hjelp.", "Automatisk database synkronisering", "Send filendelse", "Tillat uatorisert stream", "Inkluder headers", "Ekstern javascript", "Heimeside", "Vis Keyteq gir deg del", "Vis oppgraderingsdel", "Vis statistikk", "Skriv ID3v2 i stream", "Ny brukar funksjonalitet", "Filtyper", "Ja", "Nei", "Filendelse", "MIME", "Inkluder i M3U", "Rediger filtype", "Sikker?", "Optimistisk filsjekk", "Randomiserar", "Modus", "Speleliste", "Ingen, direkte", "Mine favorittar", "Fann ingen rekkjer", "Treff på systemet", "Rekkjefølge", "Slå på lam støtte", "Avslått", "Tillat Lam bruk?");


# please submit new languages, or grammar fixes directly to us for new builds. Se http://www.kplaylist.net/ for more information.

$knrlangs = 27;

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
	TBL_SEARCH			=> array('id','title','free','album','artist','md5','hits','date','fsize','genre','bitrate', 'ratemode','lengths','tagid', 'drive', 											'mtime','dirname'),
	TBL_USERS			=> array('u_name','u_pass','u_login','u_ip','u_comment','u_id','u_sessionkey','u_booted',
								'u_status','u_time','u_access','u_allowdownload','extm3u','defplaylist',
								'defshplaylist','partymode','theme','lockedtime','hotrows','searchrows','lang','udlrate', 'defgenre', 'archer', 'hitsas', 'lameperm', 'lamerate', 'allowemail', 'email'),	
	TBL_KPLAYVERSION	=> array('app_ver','app_build'),
	TBL_MHISTORY		=> array('tid')
);

$dbtable = array(TBL_MHISTORY => 10, TBL_CONFIG => 12, TBL_FILETYPES => 14);

// upgrade info

$dbalter[TBL_USERS]['lang']					= 'ALTER TABLE '.TBL_USERS.' ADD `lang` TINYINT DEFAULT \'0\' NOT NULL';
$dbalter[TBL_USERS]['u_allowdownload']		= 'ALTER TABLE '.TBL_USERS.' ADD u_allowdownload CHAR(1) DEFAULT \'0\' NOT NULL'; 
$dbalter[TBL_USERS]['udlrate']				= 'ALTER TABLE '.TBL_USERS.' ADD udlrate INT(4) DEFAULT 0 NOT NULL';
$dbalter[TBL_USERS]['defgenre']				= 'ALTER TABLE '.TBL_USERS.' ADD defgenre INT(4) DEFAULT 0 NOT NULL';
$dbalter[TBL_USERS]['archer']					= 'ALTER TABLE '.TBL_USERS.' ADD archer CHAR(1) DEFAULT 0 NOT NULL';
$dbalter[TBL_USERS]['hitsas']					= 'ALTER TABLE '.TBL_USERS.' ADD hitsas TINYINT NOT NULL default 0';
$dbalter[TBL_USERS]['lameperm']				= 'ALTER TABLE '.TBL_USERS.' ADD lameperm CHAR(1) DEFAULT \'0\' NOT NULL';
$dbalter[TBL_USERS]['lamerate']				= 'ALTER TABLE '.TBL_USERS.' ADD lamerate INT(4) DEFAULT \'0\' NOT NULL';
$dbalter[TBL_USERS]['allowemail']				= 'ALTER TABLE '.TBL_USERS.' ADD allowemail CHAR(1) DEFAULT \'0\' NOT NULL';
$dbalter[TBL_USERS]['email']				= 'ALTER TABLE '.TBL_USERS.' ADD email VARCHAR(128) NOT NULL';
$dbalter[TBL_PLAYLIST_LIST]['file']			= 'ALTER TABLE '.TBL_PLAYLIST_LIST.' ADD `file` VARCHAR(255) NOT NULL';
$dbalter[TBL_PLAYLIST_LIST]['seq']			= 'ALTER TABLE '.TBL_PLAYLIST_LIST.' ADD seq INT(4) NOT NULL';
$dbalter[TBL_SEARCH]['md5']					= 'ALTER TABLE '.TBL_SEARCH.' ADD md5 VARCHAR(32) NOT NULL';
$dbalter[TBL_SEARCH]['hits']					= 'ALTER TABLE '.TBL_SEARCH.' ADD hits INT(4) NOT NULL';
$dbalter[TBL_SEARCH]['date']					= 'ALTER TABLE '.TBL_SEARCH.' ADD date INT(4) NOT NULL';
$dbalter[TBL_SEARCH]['fsize']					= 'ALTER TABLE '.TBL_SEARCH.' ADD fsize INT(4) NOT NULL';
$dbalter[TBL_SEARCH]['bitrate']				= 'ALTER TABLE '.TBL_SEARCH.' ADD bitrate INT(4) NOT NULL';
$dbalter[TBL_SEARCH]['ratemode']				= 'ALTER TABLE '.TBL_SEARCH.' ADD ratemode TINYINT DEFAULT 0 NOT NULL';
$dbalter[TBL_SEARCH]['genre']					= 'ALTER TABLE '.TBL_SEARCH.' ADD genre INT(4) DEFAULT 255 NOT NULL';
$dbalter[TBL_SEARCH]['lengths']				= 'ALTER TABLE '.TBL_SEARCH.' ADD lengths INT(4) DEFAULT 0 NOT NULL';
$dbalter[TBL_SEARCH]['tagid']					= 'ALTER TABLE '.TBL_SEARCH.' ADD tagid TINYINT DEFAULT 0 NOT NULL';
$dbalter[TBL_SEARCH]['drive']					= 'ALTER TABLE '.TBL_SEARCH.' ADD drive TINYINT DEFAULT 0 NOT NULL';
$dbalter[TBL_SEARCH]['mtime']					= 'ALTER TABLE '.TBL_SEARCH.' ADD mtime INT(4) DEFAULT 0 NOT NULL';
$dbalter[TBL_SEARCH]['dirname']				= 'ALTER TABLE '.TBL_SEARCH.' ADD dirname VARCHAR( 255 ) DEFAULT \'0\' NOT NULL AFTER free';
$dbalter[TBL_PLAYLIST_LIST]['sid']			= 'ALTER TABLE '.TBL_PLAYLIST_LIST.' ADD sid INT(4) DEFAULT 0 NOT NULL';
$dbalter[TBL_MHISTORY]['tid']					= 'ALTER TABLE '.TBL_MHISTORY.' ADD tid TINYINT DEFAULT \'0\' NOT NULL AFTER s_id';

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
  title varchar(75) NOT NULL default '',
  free varchar(255) NOT NULL default '',
  dirname varchar(255) NOT NULL default '0',
  album varchar(50) NOT NULL default '',
  artist varchar(200) NOT NULL default '',
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
) TYPE=MyISAM";
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
  PRIMARY KEY  (u_id),
  UNIQUE KEY u_login (u_login),
  UNIQUE KEY u_id (u_id)
) TYPE=MyISAM";

$installdb[6] ="CREATE TABLE ".TBL_KPLAYVERSION." (
  app_ver varchar(6) NOT NULL default '',
  app_build varchar(6) NOT NULL default ''
) TYPE=MyISAM";

$installdb[7] = 'DELETE FROM '.TBL_KPLAYVERSION;
$installdb[8] = 'INSERT INTO '.TBL_KPLAYVERSION.' (app_ver, app_build) VALUES ("'.$app_ver.'", "'.$app_build.'")';
$installdb[9] = 'INSERT INTO '.TBL_USERS.' SET u_name = "admin", u_login = "admin", u_pass = "'.md5("admin").'",  u_comment = "admin", u_access = "0"';

$installdb[10] = 'CREATE TABLE '.TBL_MHISTORY.' (h_id int(4) NOT NULL auto_increment, u_id int(4) NOT NULL, s_id int(4) NOT NULL, tid tinyint(4) NOT NULL default \'0\', utime int(4) NOT NULL, PRIMARY KEY (h_id))';

$installdb[11] = 'ALTER TABLE '.TBL_MHISTORY.' ADD INDEX (u_id)';

$installdb[12] = 'CREATE TABLE '.TBL_CONFIG.' (id INT( 4 ) NOT NULL, `key` VARCHAR( 255 ) NOT NULL, value text NOT NULL, vtype INT( 2 ) NOT NULL, UNIQUE (id, `key`), KEY `key` (`key`)) TYPE=MyISAM AUTO_INCREMENT=1';

$installdb[13] = 'INSERT INTO '.TBL_CONFIG.' set `key` = "windows", value = "'.$win32.'", vtype = 1'; 

$installdb[14] = 'CREATE TABLE '.TBL_FILETYPES.' (`id` INT( 4 ) NOT NULL AUTO_INCREMENT,`extension` VARCHAR( 32 ) NOT NULL , `mime` VARCHAR( 128 ) NOT NULL ,`m3u` CHAR( 1 ) NOT NULL , `getid` INT( 4 ) NOT NULL , `search` CHAR( 1 ), enabled char(1) NOT NULL , PRIMARY KEY ( `id` ))';

$installdbuser[0] = "GRANT SELECT,INSERT,UPDATE,DELETE,CREATE,ALTER,DROP ON ".$db['name'].".* TO ".$db['user']."@".$db['host']." IDENTIFIED BY '".$db['pass']."'";
$installdbuser[1] = "FLUSH PRIVILEGES";

function check_all_tables($endlf='')
{
	global $dbtables, $dbtable, $dbalter, $installdb;
	$ignore = array();
	if (db_gconnect())
	{
		$sql = false;

		foreach ($dbtable AS $name => $val)  
		if (db_execquery('DESC '.$name) == false) 
		{
			$sql .= $installdb[$val].$endlf;
			$ignore[$name] = true;
		}
		
		foreach ($dbtables AS $name => $val) 
		{
			if (!isset($ignore[$name]))
			{
				for ($i=0,$c=count($dbtables[$name]);$i<$c;$i++)
					if (db_execquery('SELECT '.$dbtables[$name][$i].' FROM '.$name.' LIMIT 1') == false) $sql .= $dbalter[$name][$dbtables[$name][$i]].$endlf;
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

	function kprandomizer()
	{
		$this->limit = 25;
		$this->genre = -1;
		$this->mode = 0;
		$this->playlist = -1;
		$this->sids = array();
		$this->ssort = 'DESC';
		$this->order = 0;		
	}

	function setorder($order)
	{
		$this->order = $order;		
	}

	function setgenre($genre)
	{
		$this->genre = $genre;
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
	
	function getfavourites()
	{
		global $u_id;
		$sql = 'SELECT h.s_id,count(*) as cnt from '.TBL_MHISTORY.' h, '.TBL_SEARCH.' s where s.id = h.s_id AND u_id = '.$u_id;
		if ($this->genre != -1) $sql .= ' AND s.genre = '.$this->genre;
		$sql .= ' GROUP by h.s_id order by cnt '.$this->ssort.' limit '.$this->limit;
		$res = db_execquery($sql);
		if ($res !== false) while ($row = mysql_fetch_row($res))$this->sids[] = $row[0];
	}

	function getalltime()
	{
		global $u_id;
		$sql = 'SELECT id from '.TBL_SEARCH.' where hits > 0';
		if ($this->genre != -1) $sql .= ' AND genre = '.$this->genre;
		$sql .= ' ORDER by hits '.$this->ssort.' LIMIT '.$this->limit;
		$res = db_execquery($sql);
		if ($res !== false) while ($row = mysql_fetch_row($res))$this->sids[] = $row[0];
	}

	function getrandom()
	{
		global $u_id;
		$sql = 'SELECT id from '.TBL_SEARCH.' ';
		if ($this->genre != -1) $sql .= ' WHERE genre = '.$this->genre;
		$res = db_execquery($sql);
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

	function view($message = '')
	{
		global $userauth, $PHP_SELF, $setctl, $u_id;
		kprintheader(get_lang(212),1);
		?>
		<form name="useroptions" method="post" action="<?php echo $PHP_SELF; ?>">
		<input type="hidden" name="randomizer" value="post"/>
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td class="importnant" colspan="2"><?php echo $message; ?></td>
		</tr>
		<tr>
			<td colspan="2" height="5"></td>
		</tr>
		<tr>
			<td class="wtext"><?php echo get_lang(213); ?></td>
			<td>
				<select name="mode" class="fatbuttom">
					<option value="0"<?php if ($this->mode == 0) echo ' selected="selected"'; ?>><?php echo get_lang(216); ?></option>
					<option value="1"<?php if ($this->mode == 1) echo ' selected="selected"'; ?>><?php echo get_lang(218); ?></option>
					<option value="2"<?php if ($this->mode == 2) echo ' selected="selected"'; ?>><?php echo get_lang(171); ?></option>
				</select>
			</td>
		</tr>
		<tr>
			<td class="wtext"><?php echo get_lang(178); ?></td>
			<td><input type="text" size="8" maxlength="6" name="limit" value="<?php echo $this->limit; ?>" class="fatbuttom"/></td>
		</tr>
		<tr>
			<td class="wtext"><?php echo get_lang(147); ?></td>
			<td>
				<select name="genre" class="fatbuttom">
				<option value="-1"<?php if ($this->genre == -1) echo ' selected="selected"'; ?>><?php echo get_lang(67); ?></option>
				<?php if (class_exists('id3')) echo genre_select(false,$this->genre); ?></select>
			</td>
		</tr>
		<tr>
			<td class="wtext"><?php echo get_lang(214); ?></td>			
			<td>
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
		</tr>
		<tr>
			<td class="wtext"><?php echo get_lang(219); ?></td>			
			<td>
				<select name="order" class="fatbuttom">
				<option value="0">+</option>
				<option value="1">-</option>
				</select>
			</td>
		</tr>
		<tr>
			<td colspan="2" height="5"></td>
		</tr>
		<tr>
			<td></td>
			<td><input class="fatbuttom" type="submit" name="execute" value="<?php echo get_lang(154); ?>"/>
			<input class="fatbuttom" type="button" name="closeme" value="<?php echo get_lang(27); ?>" onclick="javascript: window.close();"/>
			</td>
		</tr>
		</table>
		</form>
		<?php
		kprintend();
	}
}


// for the mailing stuff, you'll need this pear package: http://pear.php.net/get/Mail_Mime-1.2.1.tgz

class mailmp3
{
	var $sid;
	var $tomail;
	var $message;

	function mailmp3()
	{
		$this->sid = -1;
		$this->tomail = '';
		$this->message = '';
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
		<body>
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
	
	function sendmail($sid, $mailaddr, $message)
	{
		global $u_id, $setctl, $streamtypes;
		if (MAILMP3 && db_guinfo('allowemail'))
		{
			$f2 = new file2($sid, true);
			if ($f2->fexists)
			{
				addhistory($u_id, $sid, 2);
				
				include('Mail.php');
				include('Mail/mime.php');
				include('Mail/mimeDecode.php');
	
				$crlf = "\r\n";

				if (empty($f2->id3['artist'])) $title = $f2->fname; else $title = $f2->id3['artist'].' '.$f2->id3['title'];
				$html = $this->message($title, str_replace("\n", '<br/>', $message));

				$hdrs = array(	'From' => db_guinfo('email'), 'Subject' => 'Requested MP3: '.$title);

				$mime = new Mail_mime($crlf);
				$mime->setHTMLBody($html);

				$ftype = file_type($f2->fname);
				if ($ftype != -1) $mimetype = $streamtypes[$ftype][1]; else $mimetype = 'application/octet-stream';

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
				$mail->send($mailaddr, $hdrs, $body);			
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
			$this->sendmail($this->sid, $this->tomail, $this->message);
			$msg = get_lang(230);
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
		<form method="post" action="<?php echo $PHP_SELF; ?>">
		<input type="hidden" name="sendmail" value="true"/>
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
				<input type="submit" class="fatbuttom" name="send" value="<?php echo get_lang(229); ?>"/>
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
		<form method="post" enctype="multipart/form-data" action="<?php echo $PHP_SELF; ?>">
		<input type="hidden" name="fupload" value="true"/>
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
				<input type="submit" value="<?php echo get_lang(234); ?>" class="fatbuttom"/>&nbsp; 
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
	    $bits = unpack('H*bits', $r);
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

function errormessage($msg)
{
	kprintheader(get_lang(56),0);
	blackbox(get_lang(56),'<br/>'.$msg.'<br/><br/><a href="javascript:history.go(-1)" class="fatbuttom">&nbsp;'.get_lang(34).'&nbsp;</a><br/><br/>',0);
	kprintend();
	die();
}

function kprintheader($title='',$js_out=0)
{
	global $deflanguage, $klang, $setctl;
	if (empty($title)) $title = '| kPlaylist'; else $title = '| '.$title;	
	if ($setctl->get('includeheaders', 1, 1)) 
	{
	?>
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html>
		<head>
		<title><?php echo $title; ?></title>
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
	'<tr><td width="100%" align="'.$textalign.'">'.$code.'</td></tr></table>'.
	'</td></tr></table>';
	if (!$returncode) echo $mix; else return($mix);
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
						if (file_type($val, 1) != -1) $gData[$gCnt++] = $sPath.$val;
				}
			}
		}
	}
}


$enable_tablecheck = false;
$install_debug=1;

$dbi = array("user,host,name,pass");
$dbi['host'] = $db['host'];
// dynamic from webpage.
$dbi['pass'] = $db['pass'];
$dbi['user'] = $db['user'];
$dbi['name'] = $db['name'];

$error = "";
$err   = "";
$dropdatabase = 0;

function check_version()
{
	global $enable_tablecheck, $app_build;
	$result = db_execcheck('SELECT * from '.TBL_KPLAYVERSION);
	if ($result)
	{
		$data = mysql_fetch_array($result);	
		if (isset($data['app_build']))
		{
			$dbver = (int)$data['app_build'];						
			if ($dbver != $app_build) $enable_tablecheck = true; 
		}
	}
}

function Kinstall_logo($height="64", $width="208") 
{
	global $app_ver,$app_build,$PHP_SELF;
	echo "\n".'<a href="http://www.kplaylist.net" title="Visit homepage"><img width="'.$width.'" height="'.$height.'" src="'.getimagelink('kplaylist.gif').'" alt="kPlaylist" border="0" /></a>';
}

function Kinstall_error($errorno,$i="",$whyinstall="",$isupgrading = 0)
{
	echo "An error occured.<br/>";
	die();
}

function kInstall_check_default()
{
	global $db;
	if (@mysql_connect($db['host'], $db['user'], $db['pass'])) return 1; else return 0;
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

function kInstall_clean($pos=0,$link)
{
	global $db, $dbi, $installdb, $initdb, $dropdatabase, $installdbuser, $win32;
	// fresh install
	?>
	<table width="600" border="0" align="center">  
	<tr> 
      <td colspan="4" class="wtext"><font size="4">Installing database.</font></td>
	 </tr>
	<?php
	if ($dropdatabase) $result = mysql_query($installdb[0],$link);
	$error=0;
	// create database first.
	$result = mysql_query($installdb[1],$link);
	if ($result)
	{
		if (mysql_select_db($db['name']))
		{
			for ($i=2;$i<count($installdb);$i++)
			{
				
				$querytext = str_replace("\n", "<br />", $installdb[$i]);
				$result = mysql_query($installdb[$i]);
				if (!$result) 
				{ 
					echo '<tr><td class="wtext">';
					echo ' <font color="FF0000">Failed query ['.$i.']: </font>'.$querytext.";";
					echo '</td></tr>';
					$error=$i;
				}				
			}
		}
	}
	// check the user...
	if (kInstall_check_default() == 0) 
	{
		for ($i=0;$i<count($installdbuser);$i++)
		{
			echo '<tr><td class="wtext">';
			$querytext = str_replace("\n", "<br />", $installdbuser[$i]);
			$result = mysql_query($installdbuser[$i]);
			if (!$result)
			{
				echo ' <font color="FF0000">Failed query ['.$i.']: </font>'.$querytext.";";
				$error=1;
			}
			echo '</td></tr>';
		}
	}
	if ($error) {
		echo '<tr><td class="dir"><br /><b>Installation may have failed!</b>'."\n";
		echo "\n".'</td></tr>';
	} else {
		echo "\n".'<tr><td class="dir">'."\n";
		$code = '<br /><h2>Installation is now completed.</h2>';		
		$code .= "\n".'<ul><li>To log in to kPlaylist, reload this page (firm reload) and you should be able to log in.</li>'."\n";

		$code .= "<li>All settings and configuration is available via WEB, click the 'Settings' button to the right when you've ".
				'logged in as a administrator.</li>'."\n";

		$code .= '<li>The default kPlaylist login is admin with admin as the password.</li></ul>'.
				'<br />Remember to visit <a href="http://www.kplaylist.net" target="_blank">http://www.kplaylist.net</a> for updates and help.'."\n";
			
		echo $code;
		echo "\n".'</td></tr>'."\n";
	}
	echo "</table>";
}

function kInstall_start()
{
	global $db, $dbi, $installdb, $initdb;
	$dbexist=false;
	$header = "";

	$link = @mysql_connect($db['host'], $dbi['user'], $dbi['pass']);
	if (!$link) { echo "Could not establish a connection to MySQL!"; die(); }

	if (mysql_select_db($db['name'],$link)) $dbexist = true;

	$header = "Installing MySQL database";
	kprintheader($header, 1);

	kInstall_clean(0,$link);
	kprintend();
}

function kInstall_show_form($text="")
{
	global $dbi, $db, $PHP_SELF;

	$drop=' checked="checked" ';

	if (!function_exists("kprintheader"))
	{
		echo "Error! Seems like we're not able to declare functions. Can't go further. Please either upgrade PHP ".
		"or tune your settings if possible.";
		die();
	}

	kprintheader("Install", 1);

	if (kInstall_check_default() == 0) 
	{
		$dbi['user'] = "root";
		$dbi['pass'] = "";
	} 
	?>

<form name="installform" method="post" action="<?php echo $PHP_SELF; ?>">
  <table width="680" border="0" align="center">
    <tr> 
      <td>	<?php Kinstall_logo("43","136"); ?>
</td>
    </tr>
  </table>
	<table width="600" border="0" align="center" class="tdborder">  

	<tr> 
      <td colspan="4" class="wtext"><font size="4">Welcome to the kPlaylist installer.</font></td>
	 </tr>
	 <tr>
	  <td class="wtext" colspan="4">
        To install kPlaylist, you'll need a working and running copy of MySQL. This is a GPL product, 
        please read the <a href="<?php echo $PHP_SELF ?>?showgpl=1" target="_blank"><font color="#0000FF">disclaimer of liability</font></a> 
        before you continue. If you do not agree with the disclaimer <u>you 
        must abort</u> the installation and use of this product.
	 </td>
    </tr>
    <tr> 
      <td height="22" colspan="4">
        <hr size="1"/>
      </td>
    </tr>
    <tr> 
      <td height="22" class="wtext" colspan="4">
	  If you are installing kPlaylist for the FIRST time, you must enter a user and password to MySQL 
	  which has access to create a new database and a new users for kPlaylist. 
	  In most cases, the root user of MySQL should be used.<br />
	  <a href="<?php echo $PHP_SELF ?>?showsql=1" target="_blank"><font color="#0000FF">Click here</font></a> to verify what this installer is going to do. Click 'Continue' when ready to install ! <br /><br />

	  <?php 

		  if ($db['name'] != "kplaylist")
		{
		?>
	  <b>NB!</b><font color="red">&nbsp;You have changed the database name. Make sure it is empty, and do not continue
	  unless you know EXACTLY what you're doing. Also, take a note to the 'drop database' option in the bottom!</font><br />
		
		<?php } ?>

		<?php if ($dbi['user'] == "root")
		{
			?><br><br>Note! The root password will only be used to create
		the tables, a new user called <?php echo $db['user']; ?> with password <?php echo $db['pass']; ?> will be created for the operation of kPlaylist. If you like to change the name and password for this user, please edit the script, and click Reload.<br> 
		<?php }
		if (!empty($text)) echo "<br><br>".$text."<br>"; ?>
		
		</td>
    </tr>
    <tr><td colspan="4">&nbsp;</td></tr>
	
	
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
        <input type="text" name="mysqlhost" size="25" value="<?php echo $db['host']; ?>" disabled="disabled" class="fatbuttom"/>
      </td>
      <td colspan="2" class="wtext" height="22">&nbsp;</td>
    </tr>
    <tr> 
      <td height="22" class="warning" width="121">MySQL database:</td>
      <td height="22" width="221"> 
        <input type="text" name="mysqldatabase" size="25" value="<?php echo $db['name']; ?>" disabled="disabled" class="fatbuttom"/>
      </td>
      <td colspan="4" class="wtext" height="22">&nbsp;</td>
    </tr>
	<tr>
	<td colspan="4" class="wtext"><br /><input type="checkbox" name="dropdatabase" value="on" <?php 
	if ($db['name'] == "kplaylist") echo 'checked="checked"'; ?>/> Drop database '<?php echo $db['name']; ?>' (for full reinstallation: deletes all tables in the database)&nbsp;</td></tr>
	<tr> 
      <td colspan="4">
        <input type="submit" name="reload" value="Reload" class="fatbuttom"/>      
		&nbsp;
		<input type="submit" name="continue" value="Continue" class="fatbuttom"/>
	  </td>
    </tr>
    <tr> 
      <td colspan="4" align="right"><font class="wtext">You'll find documentation here:</font>&nbsp;<a href="http://www.kplaylist.net" target="_blank"><font color="#0000FF">kPlaylist Homepage&nbsp;&nbsp;</font></a></td>
    </tr>
  
  </table>
</form><?php

	kprintend();
	die();
}

if ($enable_install) 
{
	if (!function_exists("mysql_connect")) 
	{	
		kprintheader("Error - function 'mysql_connect()' does not exist", 1);
		Kinstall_logo();
		echo '<br /><blockquote><font color="red" face="Verdana, Arial, Courier" size="2">Your PHP implementation does not support MySQL. Please visit <a href="http://www.php.net"><font color="red" face="Verdana, Arial, Courier" size="2"><u>www.php.net</u></font></a> for information on how you can enable it.<br /></blockquote>';
		kprintend();
	} 
	else
	if (!empty($_POST['continue']))
	{
		$user = $_POST['mysqluser'];
		$pass = $_POST['mysqlpass'];
		if (@$_POST['dropdatabase'] == 'on') $dropdatabase = 1; else $dropdatabase = 0;

		if (@mysql_connect($db['host'], $user, $pass))
		{
			// continue!
			$dbi['user'] = $user;
			$dbi['pass'] = $pass;
			kInstall_start();
		} else { kInstall_show_form('<font color="red">Could not login with the supplied user name and password!</font>'); die(); }
	} 
	else	
	if (@$_GET['showgpl']) 
	{ 
		kprintheader();
		echo 'The GPL license is available here: http://www.kplaylist.net/COPYING';
		kprintend();
	} 
	else	
	if (@$_GET['showsql']) { 
		kprintheader();
		showsql();
		kprintend();
	} 
	else kInstall_show_form();
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
	<tr><td colspan="2" class="wtext"><?php echo $sql; ?></td></tr>
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
		Reload this page to get started.<br/>		
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
	if (!empty($update_sql)) 
	{
		$error = "";
		if (isset($_POST['executeupgrade'])) 
		{
			$dbi['user'] = $_POST['mysqluser'];
			$dbi['pass'] = $_POST['mysqlpass'];
			$link = mysql_connect($db['host'], $dbi['user'], $dbi['pass'], true);
			if ($link)
			{
				$sqls = explode("\n", check_all_tables("\n"));
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
		show_upgrade(check_all_tables(";<br/>"),$error);
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
											?><input type="button" name="Signup" onClick="openwin('Users', '<?php echo $PHP_SELF; ?>?signup=1');" value="<?php echo get_lang(158); ?>" class="logonbuttom" /><?php 
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

	function play()
	{
		if ($this->listid >= 0)
		{
			$result = db_execquery('SELECT sid FROM '.TBL_PLAYLIST_LIST.' WHERE listid = '.$this->listid.' ORDER BY seq ASC');
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
	global $PHP_SELF,$u_cookieid, $base_dir, $u_id, $curdrive,$phpenv;
	kprintheader(get_lang(59), 1);

	$result = db_execquery('SELECT * FROM '.TBL_PLAYLIST.' WHERE listid = '.$plid);	
		
	if ($result)
	{
		$row = mysql_fetch_array($result);
		$name = $row['name'];
		$public = $row['public'];
		if ($row['u_id'] == $u_id) $myown = 1; else $myown = 0;
		$shuffle = $row['status'];
	}
	
	$result = db_execquery('SELECT * FROM '.TBL_PLAYLIST_LIST.' WHERE listid = '.$plid.' ORDER BY seq ASC');

	if ($result) $many = mysql_num_rows($result); else $many = 0;
	$playlistlink = '<input type="hidden" name="action" value="playlist"/>'.
					'<input type="hidden" name="sel_playlist" value="'.$plid.'"/>'.
					'<input type="hidden" name="previous" value="'.$prev.'"/>'.
					'<input type="hidden" name="drive" value="'.$curdrive.'"/>';
	
	$code = '<table width="800" cellspacing="0" border="0" cellpadding="0"><tr><td align="left">';
	$code .= '&nbsp;&nbsp;<input type="button" value="'.get_lang(34).'" class="fatbuttom" onclick="javascript: '."chhttp('$PHP_SELF?p=$prev&amp;d=$curdrive');\"/>&nbsp;&nbsp;".
	$playlistlink.
	'<input type="submit" name="playplaylist" value="'.get_lang(42).'" class="fatbuttom"/>&nbsp;&nbsp;';

	if ($myown) $code .=
	"<input type=\"submit\" name=\"deleteplaylist\" onclick=\"javascript: if (!confirm('".get_lang(169)."')) return false;\"  value=\"".get_lang(43)."\" class=\"fatbuttom\"/>&nbsp;&nbsp;".
	'<input type="text" name="playlistname" value="'.$name.'" size="30" class="fatbuttom"/>&nbsp;&nbsp;';

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

	$out = '';
	if ($many > 0)
	{
		$out =  '<input type="hidden" name="previous" value="'.$prev.'"/>';
		$out .= '<input type="hidden" name="action" value="playlist"/>';
		$out .=	'<input type="hidden" name="sort" value="0"/>';
		$out .=	'<input type="hidden" name="sel_playlist" value="'.$plid.'"/>';

		$out .= '<table width="800" cellspacing="0" border="0" cellpadding="0">';
		
		$out .= '
		<tr> 
		    <td width="60" class="wtext"><b>'.get_lang(49).'</b></td>
		    <td width="60" class="wtext"><b>'.get_lang(50).'</b></td>
			<td width="100" class="wtext"><b>'.get_lang(51).'</b></td>
		    <td width="120" class="wtext"><b>'.get_lang(52).'</b></td>
		    <td width="100" class="wtext"><b>';		
			if ($myown) $out .= get_lang(53);
			$out .= '
			</b></td>

			<td class="wtext" width="360" align="left"><b>'.get_lang(54).'</b></td>
		</tr>';
		$out .= '<tr><td height="8" colspan="6"></td></tr>';		
		$out .= '<tr><td colspan="6"><img src="'.getimagelink('spacer.gif').'" border="0" height="1" width="800" alt=""/></td></tr>';	
		$out .= '<tr><td height="6" colspan="6"></td></tr>';			
		$totplaytime = $count = $countfails = 0;
		
		while ($row = mysql_fetch_array($result))
		{
			$count++;			
			$srow = get_searchrow($row['sid']);
			$fexists = false;
			$id = $row['id'];
			
			if ($srow !== false)
			{
				$f2 = new file2($row['sid'], true);
				$fexists = $f2->ifexists();
				$id3 = $f2->getid3();
				$filelink = $PHP_SELF.'?sid='.$row['sid'].'&amp;c='.$u_cookieid;
			} 
		
			if (($count % 2) == 0) $out .= '<tr class="row2nd">'; else $out .= '<tr>';
			$out .= '<td class="file" align="center" width="60">';
			$out .= '<input type="checkbox" class="wtext" name="selected[]" value="'. $row['id']. '"/>';
			$out .= '</td>';
			$out .= '<td width="60" class="wtext">';
			if ($myown) $out .= '<input class="smalltext" type="text" name="seq[]" value="'.lzero($row['seq']).'" size="4"/>'; 
				else
			$out .= lzero($row['seq']);
			$out .= '</td>';			
			$out .= '<td width="100" class="file">';
			$idv3title = '';
			$idv3info  = '';

			if (!$fexists)
			{ 
				$out .= '<font color="RED">'.get_lang(182).'</font>'; 
				$countfails++; 
			} else
			{
				$idv3title = rtrim($id3['title']) . ' - '. rtrim($id3['album']);				
				if (!empty($id3['bitrate']) && !empty($id3['length'])) $idv3info = rtrim($id3['bitrate']).'kb - '. rtrim($id3['length']); 
				if (!empty($id3['lengths'])) $totplaytime += $id3['lengths'];
				$out .= get_lang(181);
			}
			$out .= '</td>';
			$out .= '<td width="120" class="wtext">'.$idv3info.'</td>';
			$out .= '<td width="100" class="file">';
			if ($myown) $out .= '<a title="'.get_lang(60).'" class="smalltext" href="'. $PHP_SELF . "?action=editplaylist&amp;plid=$plid&amp;del=$id&amp;p=$prev&amp;d=$curdrive".'">&nbsp;'.get_lang(43).'&nbsp;</a>';
			$out .= '</td>';
			$out .= '<td width="360" align="left" class="file">';
			if ($fexists) $out .= '<a title="'.$idv3title.'" href="'. $filelink.'">'.$f2->fname.'</a>'; else $out .= '&nbsp;';
			$out .= '</td></tr>';
		} // file iterator

		$out .= '<tr><td height="6" colspan="6"></td></tr>';
		$out .= '<tr><td colspan="6"><img src="'.getimagelink('spacer.gif').'" border="0" height="1" width="800" alt=""/></td></tr>';
		$out .= '<tr><td height="10" colspan="6"></td></tr>';

		$out .= '
		<tr>
		<td class="wtext" align="center" colspan="2"><b>'.get_lang(55).'</b></td>
		<td class="file">';

		if ($countfails==0) $out .= get_lang(181); else $out .= '<font color="red">'.get_lang(56).'</font>';
		$out .= '</td>';

		$secs = $totplaytime;
		$days = floor($secs/86400);
		$secs = $secs % 86400;
		$hours = floor($secs/3600);
		$secs = $secs % 3600;
		$min = floor($secs/60);
		$secs = $secs % 60;

		$totshow = get_lang(187, $days, $hours, $min, $secs);
		
		$out .= '<td class="wtext">'.$totshow.'</td></tr>';
		$out .= '<tr><td colspan="6">&nbsp;</td></tr>';
		$out .= '<tr><td align="left" class="file" colspan="6">';

		$out .=	'<input type="hidden" name="drive" value="'.$curdrive.'"/>'.
				'&nbsp;&nbsp;'.get_lang(73).'&nbsp;&nbsp;<input type="button" value="+" class="fatbuttom" onclick="javascript: SelectAll();"/>&nbsp;&nbsp;'.
				'<input type="button" value="-" class="fatbuttom" onclick="javascript: SelectAll();"/>&nbsp;&nbsp;'.
		get_lang(57).'&nbsp;&nbsp;<input type="submit" class="fatbuttom" onclick="javascript: if (!anyselected()) { alert(\''.get_lang(159).'\'); return false; }" name="playselected" value="'.get_lang(42).'"/>&nbsp;&nbsp;';

		if ($myown) $out .= '<input type="submit" class="fatbuttom" onclick="javascript: if (!anyselected()) { alert(\''.get_lang(159).'\'); return false; } else if (!confirm(\''.get_lang(210).'\')) return false;" name="delselected" value="'.
		get_lang(43).'"/>&nbsp;&nbsp;'.get_lang(58).'&nbsp;&nbsp;<input type="submit" class="fatbuttom" name="saveseq" value="'.get_lang(45).'"/>';

		$out .= '&nbsp;&nbsp;</td></tr><tr><td colspan="6">&nbsp;</td></tr>';
		$out .= '</table>';
	}
	echo '<form action="'.$PHP_SELF.'" method="post">';	
	if (!$myown)  
			blackbox(get_lang(46, $name, $many),$code,0);
		else 
			blackbox(get_lang(46,$name,$many),$code,0);
	echo '</form>';
	{
		echo '<form name="psongs" action="'.$PHP_SELF.'" method="post">';	
		if ($myown) blackbox(get_lang(47),$out,0); else blackbox(get_lang(48),$out,0);
		echo '</form></body></html>'; 
	}
}

function playlist_new()
{
	global $PHP_SELF;
	kprintheader(get_lang(61), 1);
	?>
	<form method="post" action="<?php echo $PHP_SELF; ?>">
	<input type="hidden" name="newplaylist" value="new"/>
	<table width="300" border="0" cellpadding="0" cellspacing="0">
	<tr> 
		<td class="wtext" align="left" width="60"><?php echo get_lang(62); ?></td>
		<td class="wtext" colspan="2" width="240">&nbsp;<input type="text" name="name" class="wtext"/></td>
	</tr>
	<tr> 
		<td class="wtext" align="left" width="60"><?php echo get_lang(44); ?></td>
		<td class="wtext" colspan="2" width="240"><input type="checkbox" name="shared" value="on" class="wtext"/></td>
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


function KCreate_Mp3Table($pdir="")
{
	global $PHP_SELF, $pdir64;
	$pdir64 = base64_encode($pdir);
	?>
	<table width="100%" border="0" align="left" cellspacing="0" cellpadding="0">
	<tr>
	<td align="left" width="70%" valign="top">
	<form name="psongs" action="<?php echo $PHP_SELF?>" method="post">
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

function KCreate_EndMp3Table($showalbum=1, $dirs=0, $files=0) 
{ 
	global $u_id, $PHP_SELF, $curdrive, $archivemode;
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
		$crstr .= $text.'" class="fatbuttom"'."/>".'&nbsp;&nbsp;';
		$crstr_dl = '<input type="submit" name="pdlall" value="'.$text.'" class="fatbuttom"/>&nbsp;&nbsp;'; 
	} 	
	
	$crstr .= '<input type="submit" onclick="javascript: if (!anyselected()) { alert(\''.get_lang(159).'\'); return false; }" name="psongsselected" value="'.get_lang(68).'" class="fatbuttom"'."/>";
	$crstr_dl .= '<input type="submit" onclick="javascript: if (!anyselected()) { alert(\''.get_lang(159).'\'); return false; }" name="pdlselected" value="'.get_lang(68).'" class="fatbuttom"'."/>";

	$playlists = db_getplaylist($u_id);
	$ploutput = "";
	if (count($playlists)>0)
	{
		$ploutput .= '<input type="submit" name="addplaylist" onclick="javascript: if (!anyselected()) { alert(\''.get_lang(32).'\'); return false; }" value="'.get_lang(69).'" class="fatbuttom"/>&nbsp;';
		$ploutput .= '<select name="sel_playlist" class="file">';
		
		$playid = db_guinfo("defplaylist");
		for ($c=0,$cnt=count($playlists);$c<$cnt;$c++) 
		{		
			if ($playlists[$c][1] == $playid) $sel=' selected="selected" '; else $sel='';
			$ploutput .= '<option value="'. $playlists[$c][1].'"'.$sel.'>'.$playlists[$c][0].'</option>';
		}
		$ploutput .= '</select>';
	}
	$ploutput .= '<input type="hidden" name="action" value="playlist"/>';
	$ploutput .= '<input type="hidden" name="drive" value="'.$curdrive.'"/>';
	if (count($playlists)>0)
	{
		$ploutput .= '<input type="submit" name="playplaylist" value="'.get_lang(70).'" class="fatbuttom"/>&nbsp;';
		$ploutput .= '<input type="submit" name="editplaylist" value="'.get_lang(71).'" class="fatbuttom"/>&nbsp;';
	}
	
	$upload = '<input type="button" name="upload" onclick="javascript: newwin(\'upload\', \''.$PHP_SELF.'?fupload=true\', 120, 450);" value="'.get_lang(69).'" class="fatbuttom"/>';

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
			if (ALLOWDOWNLOAD && db_guinfo('u_allowdownload') && $archivemode) echo '<td align="left"> '.blackbox(get_lang(117), $crstr_dl).'</td>';
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

function album_hotlist($type)
{
	global $PHP_SELF;
	$alf = '0abcdefghijklmnopqrstuvwxyz';
	$chc = 0;
	$chlist = array();
	$qres = db_execquery('SELECT LOWER(SUBSTRING(artist,1,1)) AS ch FROM '.TBL_SEARCH.' WHERE TRIM(album) != "" AND TRIM(artist) != "" GROUP BY SUBSTRING(artist,1,1)');
	while ($row = mysql_fetch_row($qres)) if (is_numeric($row[0])) $chlist[$chc++] = '0'; else $chlist[$chc++] = $row[0];
	$out = '';
	for ($i=0,$c=strlen($alf);$i<$c;$i++)
	{
		if (in_array($alf[$i], $chlist, true))
			$out .= '<a title="'.get_lang(30, $alf[$i]).'" href="'.$PHP_SELF.'?'.$type.'='.$alf[$i].'" class="hot">'.$alf[$i].'</a>&nbsp;'; 
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
			if ($glistid[$i] == $default) 
			$genres .= '<option value="'.$glistid[$i].'" selected="selected">'.$glist[$i].'</option>'; 
				else
			$genres .= '<option value="'.$glistid[$i].'">'.$glist[$i].'</option>';
		}
	}
	if ($top) $genres .= '</select>';
	return $genres;
}

function KCreate_infobox()
{
 	global $PHP_SELF, $u_cookieid, $u_id, $app_ver, $setctl, $u_id, $u_searchstr,
		$u_playlist, $u_playlistid, $pdir64, $app_build, $homepage;

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
	<input type="hidden" name="action" value="Search"/>
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
		<td colspan="4">&nbsp;<input type="text" name="searchfor" value='<?php echo $u_searchstr; ?>' maxlength="150" size="35" class="fatbuttom"/></td>		
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
	</table>
	</form>
	<?php		
		db_sharedplaylist($u_id);
		$ploutput = "";
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
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<?php
		
		if (db_guinfo('u_access') == 0)
		{
			?>
			<tr>
				<td align="left" colspan="2">
			<?php
			$admincode='&nbsp;<input type="button" name="action" value="'.get_lang(87).'" class="fatbuttom" onclick="javascript: openwin(\'Users\', \''.$PHP_SELF.'?users=show\');"/>
			<input type="button" name="updatesearch" value="'.get_lang(15).'" class="fatbuttom" onclick="javascript: openwin(\'Update\', \''. $PHP_SELF.'?filelist=update\');"/>&nbsp;'.
			'<input type="button" name="settings" value="'.get_lang(126).'" class="fatbuttom" onclick="javascript: newwin(\'Settings\',\''.
			$PHP_SELF.'?settings=edit\',370,675);"/>&nbsp;';
			
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
					'<input type="button" name="editoptions" value="'.get_lang(92).'" class="fatbuttom" '. 'onclick="javascript: newwin(\'Options\', \''.$PHP_SELF.'?editoptions=show\',250,550);"/>&nbsp;';
		$usermisc .=  '<input type="button" name="randomizer" value="'.get_lang(212).'" class="fatbuttom" '. 'onclick="javascript: newwin(\'Randomizer\', \''.$PHP_SELF.'?randomizer=show\',220,550);"/>&nbsp;';
				

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
	$result = db_execquery('SELECT name, listid FROM '.TBL_PLAYLIST.' WHERE public = 1 AND u_id != '.$u_id);

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

function db_verify_stream($cookie = 0, $ip)
{
	global $u_id, $demo_mode, $setctl, $disablelogin, $assumeuserid, $validated_user;	
	if ($disablelogin) 
	{
		$u_id = $assumeuserid;
		loadvalidated($u_id);
		if ($validated_user === false) 
		{
			echo '$assumeuserid has a ID to a user that does not exist. Please set it correctly in the script and reload this page.';
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
			if ($demo_mode == 1) return 1;
			if ($setctl->get('timeout') != 0) if (($time + $setctl->get('timeout')) < time()) return 0;
			return mysql_num_rows ($result);
		}
		return 0;		
	}
}

function webprocess()
{
	global $_POST, $cookie_name, $userauth, $u_cookieid, $demo_mode, $phpenv, $disablelogin;
	if ($disablelogin) 
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
					if ($demo_mode) 
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
					setcookie($cookie_name,"");
					setcookie($cookie_name,$num);
				}
			}
		}
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
				break;

			case 1:
				$setctl->set('includeheaders', 0);
				$setctl->set('showkeyteq', 0);
				$setctl->set('showupgrade', 0);
				$setctl->set('showstatistics', 0);
				
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
							if ($s_base_dir[$i][strlen($s_base_dir[$i])-1] != '/') $s_base_dir[$i] .= '/';	
							$value .= slashtranslate($s_base_dir[$i]);					
							if (isset($s_base_dir[$i+1])) if (!empty($s_base_dir[$i+1])) $value .= ';';
						}
					}
					break;

				case 'uploadpath':
					if (!empty($value))
					if ($value[strlen($value)-1] != '/') $value .= '/';	
					break;

				case 'homepage':
					$value = htmlentities($value);
					break;				
				
				case 'externimagespath':						
					if (!empty($value)) if ($value[strlen($value)-1] != '/') $value .= '/';	
					break;				
			}
			$setctl->set($key, $value);			
		}				
	}
}

function helplink($section, $name="?")
{
	global $deflanguage;
	return '<a target="_new" title="'.get_lang(161).'" href="http://www.kplaylist.net/?configuration='.$section."&amp;lang=".$deflanguage.'">'.$name.'</a>';
}

function store_filetype($id, $m3u, $mime, $extension='')
{
	if ($id != 0)
	{	
		db_execquery('UPDATE '.TBL_FILETYPES.' SET m3u = '.$m3u.', mime = "'.$mime.'", extension = "'.$extension.'" WHERE id = '.$id);
		return $id;
	}
	else 
	{
		db_execquery('INSERT INTO '.TBL_FILETYPES.' SET m3u = '.$m3u.', mime = "'.$mime.'", search = 1, enabled = 1, getid = 0, extension = "'.$extension.'"');
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
	}
	kprintheader(get_lang(209), 1);
	?>
	<form name="edit_filetype" method="post" action="<?php echo $PHP_SELF; ?>">
	<input type="hidden" name="store_filetype" value="true"/>
	<input type="hidden" name="id" value="<?php echo $id; ?>"/>

	<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td class="wtext"><?php echo get_lang(206); ?></td>
		<td class="wtext"><input type="text" name="extension" class="fatbuttom" maxlength="32" size="30" value="<?php echo $row['extension']; ?>"/></td>				
	</tr>
	<tr>
		<td class="wtext"><?php echo get_lang(207); ?></td>
		<td class="wtext"><input type="text" name="mime" class="fatbuttom" size="30" maxlength="128" value="<?php echo $row['mime']; ?>"/></td>				
	</tr>
	<tr>
		<td class="wtext"><?php echo get_lang(208); ?></td>
		<td class="wtext"><input type="checkbox" name="m3u" value="1" <?php echo checked($row['m3u']); ?>></td>				
	</tr>
	<tr>
		<td colspan="2" height="10"/>
	</tr>
	<tr>
		<td colspan="2">
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
	global $phpenv, $setctl, $enablegetid3, $win32, $streamtypes, $pstreamtypes, $PHP_SELF;
	
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

			<?php	
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
				<td class="wtext"><input type="checkbox" value="1" <?php if (!$enablegetid3) echo 'disabled="disabled"'; ?> name="writeid3v2" <?php if ($enablegetid3) echo $setctl->getchecked('writeid3v2'); ?>/></td>
				<td class="wtext"><?php echo helplink('writeid3v2'); ?></td>
			</tr>
			<tr>
				<td class="wtext"><?php echo get_lang(220); ?></td>
				<td class="wtext"><input type="checkbox" value="1" name="lamesupport" <?php echo $setctl->getchecked('lamesupport'); ?>/></td>
				<td class="wtext"><?php echo helplink('lamesupport'); ?></td>
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

							for ($i=0,$c=count($streamtypes);$i<$c;$i++)
							{
								$cnt++;
								if ($cnt % 2 == 0) echo '<tr>'; else echo '<tr class="row2nd">';
								?>
									<td class="wtext"><?php echo '.'.$streamtypes[$i][0]; ?></td>
									<td class="wtext"><?php echo $streamtypes[$i][1]; ?></td>
									<td class="wtext"><?php echo selected($streamtypes[$i][2],get_lang(204), get_lang(205)); ?></td>
									<td class="wtext"><?php 
									if (isset($streamtypes[$i][5])) 
									{
										echo '<a class="hot" onclick="javascript: if (!confirm(\''.get_lang(210).'\')) return false;" href="'. $PHP_SELF .'?editfiletype=delete&amp;del='.$streamtypes[$i][5].'">'.get_lang(109).'</a>&nbsp;&nbsp;';
										
										echo '<a class="hot" href="javascript: void(0);" onclick="javascript: newwin(\'fileditor\', \''.$PHP_SELF.'?editfiletype='.$streamtypes[$i][5].'\',110,350);">'.get_lang(71).'</a>&nbsp;';
									}
								?>
								</td>
								
								</tr>
								<?php
							}
							?>
							<tr>
								<td colspan="3"/><td><?php echo '<a class="hot" href="javascript: void(0);" onclick="javascript: newwin(\'fileditor\', \''.$PHP_SELF.'?editfiletype=0\',110,350);">'.get_lang(69).'</a>&nbsp;';?></td>
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
		return $PHP_SELF.'?settings=edit&amp;reload='.$reload.'&amp;page='.$id;
	}
	
	?>
	<form name="settings" method="post" action="<?php echo $PHP_SELF; ?>">
	<input type="hidden" name="settings" value="save"/>
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
	db_execquery('INSERT INTO '.TBL_MHISTORY.' SET u_id = '.$u_id.', s_id = '.$sid.', utime = '.time().', tid = '.$tid);
}

function getlasthistory($sid)
{
	$res = db_execquery('SELECT s_id, utime FROM '.TBL_MHISTORY.' WHERE s_id = '.$sid.' ORDER BY h_id DESC LIMIT 1');	
	if ($res !== false && mysql_num_rows($res) > 0)
	{
		$row = mysql_fetch_assoc($res);
		return $row['utime'];
	} 
	return 0;
}

function gethistory($u_id, $rows = 0)
{
	$sql = 'SELECT h.tid, h.utime, s.free FROM '.TBL_MHISTORY.' h, '.TBL_SEARCH.' s WHERE h.s_id = s.id and h.u_id = '.$u_id.' ORDER BY h.utime DESC';
	if ($rows != 0) $sql .= ' LIMIT '.$rows;
	return db_execquery($sql);
}

function db_login($user, $ip)
{
	global $demo_mode;
	if ($demo_mode != 1)
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
	global $demo_mode;
	if ($demo_mode != 1) db_execquery('UPDATE '.TBL_USERS.' SET u_status = 0, u_sessionkey = 0 WHERE u_sessionkey = '.$cookie.' and u_ip = "'.$ip.'"');
}

function db_update_session($num, $user)
{
	global $demo_mode;
	if ($demo_mode != 1) db_execquery('UPDATE '.TBL_USERS.' SET u_sessionkey = "'.$num.'", u_time = '.time().' WHERE u_login like "'.$user.'"');
}

function show_new_user_form($id = -1, $name='', $pass='', $comment='', $login='', $access=1, $download=0, $udlrate=0, $allowemail = 0)
{
	global $userauth, $PHP_SELF;

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
	<input type="hidden" name="formusers" value="userchange" />
	<input type="hidden" name="u_id" value="<?php echo $id; ?>" />
	<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr><td width="40%"></td><td width="50%"></td><td width="10%"></td></tr>

<?php if ($id != -1) { ?>
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
		<input type="submit" name="Submit" value="<?php echo get_lang(45); ?>" class="fatbuttom" />&nbsp;
		<input type="submit" name="Cancel" value="<?php echo get_lang(16); ?>" class="fatbuttom" />
		</td>
		</tr>
  </table>
</form>
</body>
</html>
<?php
die();
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
				$result = db_execquery('INSERT INTO '.TBL_USERS.' SET u_name = "'.mysql_escape_string($_POST['name']).'", u_login = "'.mysql_escape_string($_POST['login']).'", u_pass = "'.
				md5(mysql_escape_string($_POST['password'])).'", u_comment = "'.mysql_escape_string($_POST['comment']).'", u_access = 1, u_allowdownload = 1, lang = '.$deflanguage);
				if ($result) 
				{
					kprintheader(get_lang(96), 1);
					echo '<p class="wtext">Ok, '.$_POST['login'].' has been added to kPlaylist<br><br>';
					echo '<input type="button" name="Close" value="Close" onclick="javascript: window.close();window.opener.userform.user.focus();"  class="fatbuttom" /></p>';
					kprintend(); 
					die();
				}
				else signup_form('Something went wrong.. Try another username..');
			} else signup_form('Error signing up...');
		} else signup_form(); 
	}
	else die("Signup disabled...");
}

function signup_form($error="")
{
	global $userauth, $PHP_SELF;

	$title=get_lang(96); 
	kprintheader($title,1);
	if (!empty($error)) echo '<font color="RED">'.$error.'</font><br />'; 
	?>	
	<form method="post" action="<?php echo $PHP_SELF; ?>">
	<input type="hidden" name="signup" value="1" />
	<input type="hidden" name="adduser" value="1" />
	<table width="600" border="0" cellpadding="2" cellspacing="2">
	<tr> 
      <td width="110" height="17" class="wtext"><?php echo get_lang(97); ?></td>
      <td width="490" height="17"><input type="text" name="name" class="userfield" value="" /></td>
      <td width="47" height="17">&nbsp;</td>
    </tr>    
	<tr> 
      <td width="110" class="wtext"><?php echo get_lang(98); ?></td>
      <td width="490"><input type="text" name="login" class="userfield" value="" /></td>
      <td width="47">&nbsp;</td>
    </tr>
	<tr> 
      <td width="110" class="wtext"><?php echo get_lang(100); ?></td>
      <td width="490"><input type="password" name="password" class="userfield" value="" /></td>
      <td width="47">&nbsp;</td>
    </tr>    
	<tr> 
      <td width="110" class="wtext"><?php echo get_lang(101); ?></td>
      <td width="490"><input type="text" name="comment" class="userfield" value="" /></td>
      <td width="47">&nbsp;</td>
    </tr>
	<tr>
      <td colspan="2" class="wtext">
        <br/>
		<input type="submit" name="Submit" value="<?php echo get_lang(45); ?>" class="fatbuttom" />&nbsp;
        <input type="submit" name="Cancel" value="<?php echo get_lang(16); ?>" onclick="javascript: window.close();" class="fatbuttom" />
      </td>
      <td width="490">&nbsp;</td>
	</tr>
  </table>
</form>
</body>
</html>
<?php
die();
}

function show_userhistory($id, $length = 20)
{
	global $PHP_SELF;
	kprintheader(get_lang(121),1);
	$res = gethistory($id, $length);
	?>
	<form method="post" action="<?php echo $PHP_SELF; ?>">
	<input type="hidden" name="formusers" value="modify"/>
	<input type="hidden" name="history" value="true"/>
	<input type="hidden" name="id" value="<?php echo $id; ?>"/>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td class="wtext" colspan="3">
			<input class="fatbuttom" type="button" onclick="javascript: location = '<?php echo $PHP_SELF.'?users=show'; ?>';" name="back" value="<?php echo get_lang(34); ?>"/>&nbsp;&nbsp;	
			<input class="fatfield" size="3" maxlength="5" type="text" name="length" value="<?php echo $length; ?>"/>&nbsp;
			<?php echo get_lang(178); ?>&nbsp;&nbsp;
			<input type="submit" value="<?php echo get_lang(107) ;?>" name="Refresh" class="fatbuttom"/>
		</td>
	</tr>
	<tr>
		<td colspan="3" height="15"></td>
	</tr>	
	<?php
		
		$tidarray = array(0 => get_lang(183), 1 => get_lang(117), 2 => get_lang(223));	
		if ($res)
		{
			$cnt = 0;
			while ($row = mysql_fetch_assoc($res))
			{
				if (($cnt % 2) == 0) echo '<tr class="row2nd">'; else echo '<tr>';
				?>
					<td class="file"><?php echo $tidarray[$row['tid']]; ?></td>
					<td class="file"><?php echo strlen($row['free']) > 80 ? substr($row['free'], 0, 80) : $row['free']; ?></td>
					<td class="file"><?php echo date("d.m.y H:i:s",$row['utime']); ?></td>					
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
	global $userauth, $PHP_SELF, $setctl;
	kprintheader(get_lang(121),1);

    $query = "SELECT * FROM ".TBL_USERS." order by u_time desc";
	$result = db_execquery($query);
	
	$pereach=0;
	$out = '<table width="570" border="0" cellspacing="0" cellpadding="0">';
	while ($row = mysql_fetch_array($result)) 
	{
		if (($pereach % 2) == 0) $out .= '<tr class="row2nd">'; else $out .= '<tr>';
		$pereach++;

		$ulogin = $row['u_login'];
		if ($row['u_access'] == 0) $uname = '<font color="red">'. $row['u_name'].'</font>'; else $uname = $row['u_name'];
		
		$out .= '<td width="90" class="file"><a class="hot" href="'. $PHP_SELF .'?users=modify'. "&amp;edit=".$row['u_id'].'" title="'.get_lang(95).'">'. $ulogin. "</a></td>\n";
		$out .= '<td width="175" class="file">'. $uname. '</td>';
		$out .= '<td width="135" class="file"><font title="';
		$out .= date("d.m.y ".'\a\t'." H:i",$row['u_time']);
		$out .= '"> '.$row['u_ip']. '</font></td>';

		if ($setctl->get('timeout') != 0 && $row['u_status'] == 1)  if (((int)$row['u_time'] + $setctl->get('timeout')) < time()) $row['u_status'] = 0;

		switch ($row['u_status'] )
		{
			case 0: $stout = get_lang(104); break;
			case 1: $stout = '<font color="red">'.get_lang(103).'</font>'; break;
			case 2: $stout = "Booted"; break;
			default: $stout = "Unknown"; break;
		}
		$out .= '<td width="60" class="file">'. $stout. "</td>\n";
		$out .= '<td width="110" class="file">'."\n";
		
		$out .= '<a class="hot" onclick="javascript: if (!confirm(\''.get_lang(175).'\')) return false;" href="'. $PHP_SELF .'?users=modify'. "&amp;del=".$row['u_id'].' " title="'.get_lang(105).'">'.get_lang(109).'&nbsp;&nbsp;</a>';
		$out .= '<a class="hot" href="'. $PHP_SELF .'?users=modify'. "&amp;history=".$row['u_id'].'" title="'.get_lang(176).'">'.get_lang(177).'</a>&nbsp;&nbsp;';
		if ($row['u_status'] == 1)
		$out .= '<a class="hot" href="'. $PHP_SELF .'?users=modify'. "&amp;logout=".$row['u_id'].'" title="'.get_lang(106).'">'.get_lang(110).'</a>';
		$out .= '</td></tr>';
	}

	$out .= "</table>";
	$out .= "<form action=\"$PHP_SELF\" method=\"post\">". "\n";
	$out .= '<input type="hidden" name="formusers" value="modify"/>';
	$out .= '<table width="600" border="0">'."\n";
	$out .= "<tr><td height=\"5\" colspan=\"6\"></td></tr>\n";
	$out .= "<tr><td colspan=\"8\">";
	$out .= '<input type="submit" value="'.get_lang(107).'" name="Refresh" class="fatbuttom" />';
	$out .= '&nbsp;<input type="submit" value="'.get_lang(108).'" name="newuser" class="fatbuttom" />';
	$out .= '&nbsp;<input type="submit" value="'.get_lang(27).'" name="button" class="fatbuttom" onclick="javascript: window.close();" />';
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
	db_execquery("UPDATE ".TBL_USERS." set extm3u = $extm3u, hotrows = $hotrows, searchrows = $searchrows, lang = $ulang, archer = $archer, lamerate = $lamerate, email = '".$email."' where u_id = $u_id");
	if (!empty($pass)) 
	{ 
		db_execquery('UPDATE '.TBL_USERS.' set u_pass = "'.$pass.'" where u_id = '.$u_id); 
		$state = 2; 
	}
	loadvalidated($u_id);
	return $state;
}

function show_useroptions($msg="", $reload = false)
{
	global $userauth, $PHP_SELF, $u_id, $klang, $deflanguage, $lamebitrates, $setctl;
	$result = db_execquery('SELECT * from '.TBL_USERS.' WHERE u_id = '.$u_id);
	if ($result) $row = mysql_fetch_array($result);
	if (!$row) die();
	if ($row['extm3u'] == 1) $ext3mu = 'checked="checked"'; else $ext3mu="";
	$langout = get_lang_combo($row['lang'], "u_language");
	
	kprintheader(get_lang(123),1);
	?>
	<form name="useroptions" method="post" action="<?php echo $PHP_SELF; ?>">
	<input type="hidden" name="useroptions" value="save"/>
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
		<td></td>
	</tr>
	<tr>	
		<td class="wtext"><?php echo get_lang(100); ?></td>
		<td><input type="password" maxlength="10" size="10" class="fatbuttom" name="password"/></td>
		<td></td>
	</tr>
	<tr>	
		<td class="wtext"><?php echo get_lang(166); ?></td>
		<td><select name="archer" class="fatbuttom"><?php echo get_archiver_combo($row['archer']); ?></select></td>
		<td></td>
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
		<td></td>
	</tr>
	<?php
	}
	?>
	<tr>	
		<td class="wtext"><?php echo get_lang(223); ?></td>
		<td><input type="text" maxlength="128" size="30" class="fatbuttom" name="email" value="<?php echo $row['email']; ?>"/></td>
		<td></td>
	</tr>
	<tr><td colspan="3" height="10"></td></tr>
	<tr>
		<td colspan="3">
		<input class="fatbuttom" type="submit" name="save" value="<?php echo get_lang(45); ?>"/>&nbsp;
		<input class="fatbuttom" type="button" name="closeme" value="<?php echo get_lang(27); ?>" onclick="javascript: window.close(); <?php 
			if ($reload) echo 'window.opener.location.reload();'; ?>"/>
		</td>
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
	global $titlesperalbum;
	$result = db_execquery($sql);
			
	kprintheader($header, 1);
	KCreate_Mp3Table("");			
	
	show_nice_dir("",$ndir,0);

	echo $extra;

	$cntr=0;
	$many = 0;			
	echo '</td></tr>';
	while ($row = mysql_fetch_array($result)) 
	{
		if ($row['many'] >= $titlesperalbum)
		{
			$f2 = new file2($row['id'], false);			
			$dir = $f2->relativepath;
			$many++;
			$ainf = gen_aheader($row['album'], $row['artist'], $row['lengths'], $row['many']);		
			switch ($special)
			{
				case 0: echo print_dir($row['drive'],$row['artist'].' - '.$row['album'], $dir, -1 , 1,'link.gif', $row['artist'], $ainf, '', true); 
						break;
				case 1: if ($row['cntr'] > 0)  
						{
							echo print_dir($row['drive'],' '.lzero($cntr+1).' '.$row['artist'].' -  '.$row['album'], $dir, -1 , 1, 'link.gif',$row['cntr'].' hits - '.$row['many'].' tunes', $ainf, '', true);							
							$cntr++;
						}
						break;
				case 2: echo print_dir($row['drive'],date("d.m.y H:i",$row['date']).' - '.$row['artist'].' - '.$row['album'], $dir, -1 , 1, 'link.gif',$row['artist'],$ainf, '', true); 
						break;
				
				case 3: if ($row['cntr'] > 0)  
						{
					
							//print_dir($drive,$name, $pdir, $nr, $return=0,$image='dir.gif',$title='', $ainf=null, $mark='', $showalbum = false)
							
							echo print_dir($row['drive'], ' '.lzero($cntr+1).' '.$row['artist'].' -  '.$row['album'], $dir, -1 , 1, 'link.gif',$row['cntr'].' hits - '.$row['many'].' tunes', null, '', true, $row['many']);							
							$cntr++;
						}
						break;
			}
		}
	}
	
	if ($many==0) echo '<tr><td><font class="finfo">'.get_lang(10).'</font></td></tr>';
	
	KCreate_EndMp3Table(0);
	KCreate_infobox();
	echo '</tr></table></body></html>';	
	die();
}

function hotselect($char)
{
	$query = 'SELECT id,artist,album,free,drive,count(free) AS many, sum(lengths) AS lengths FROM '.TBL_SEARCH.' WHERE ';
	if (@$char == "0") 
	{ 
		for ($i=0;$i<10;$i++) 
		{
			$query .= 'rtrim(artist) like "'.$i.'%"';
			if ($i < 9) $query .= ' or ';
		}
	} else $query .= 'rtrim(artist) like "'.$char.'%"';
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
		$query = 'SELECT s.*, sum(s.hits) as cntr, count(*) AS many FROM tbl_search s, tbl_mhistory h WHERE h.utime >= '.$uxfrom.' AND h.utime <= '.$uxto.
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
	global $base_dir;

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

	$ssearchline = 'WHERE ';
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
	$query = 'SELECT *'.$extra.' FROM '.TBL_SEARCH.' '.$ssearchline.' OR '.$ssearchlinefree;
		else 
	$query= 'SELECT *'.$extra.' FROM '.TBL_SEARCH.' '.$ssearchline;	
	
	if ($hitsas == 1) $query .= ' AND LENGTH(rtrim(album)) > 0 GROUP BY RTRIM(album)';
	
	$query .= ' ORDER BY dirname, free ASC';

	$startt = microtime();
	$result = db_execquery($query);
	$endt   = microtime();

	$exectime = $endt-$startt;
	if ($exectime < 0) $execstr = '0.00'; else $execstr =  substr($exectime, 0, 4);

	KCreate_Mp3Table('');
	$many = mysql_num_rows($result);
	$mwritten=0;

	$max = db_guinfo("searchrows");
	$extra="";
	if ($many > $max) $extra = get_lang(6, $max); 
	show_nice_dir("",get_lang(8, $ssearch),0);
	echo '<font class="wtext"> - '.get_lang(9)." $many $extra / $execstr ".get_lang(7).'</font>';
	echo '</td></tr>';

	while ($row = mysql_fetch_array($result)) 
	{
		if ($mwritten+1 > $max) break;
		$f2 = new file2($row['id']);
		if ($f2->ifexists())
		{
			switch ($hitsas)
			{
				case 0: print_file($row['id'],1,1,$f2,$ssearch);
						break;
						
				case 1: $ainf = gen_aheader($row['album'], $row['artist'], $row['lengths'], $row['many']);		
						echo print_dir($row['drive'],date("d.m.y H:i",$row['date']).' - '.$row['artist'].' - '.$row['album'], $f2->relativepath, -1 , 1, 'link.gif',$row['artist'],$ainf,$ssearch, true);
						break;
			}			
			$mwritten++;			
		}
	}
	if ($many==0) echo '<tr><td><font class="finfo">'.get_lang(10).'</font></td></tr>';

	KCreate_EndMp3Table(0, 0, $mwritten);
	KCreate_infobox();
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

function search_qupdcontruct($album, $title, $artist, $genre=255, $filein, $md5,$id, $lengths=0, $ratemode=0, $bitrate=0, $tagid=0, $drive=0, $mtime = 0)
{
	return 'UPDATE '.TBL_SEARCH.' SET title = "'.mysql_escape_string($title).'", album = "'.mysql_escape_string($album).'", artist = "'.mysql_escape_string($artist).'", md5 = "'.$md5.'", free = "'.mysql_escape_string(basename($filein)).'", genre = '.$genre.', lengths = '.$lengths.', ratemode = '.$ratemode.', bitrate = '.$bitrate.', tagid = '.$tagid.', drive = '.$drive.', mtime = '.$mtime.', dirname = "'.mysql_escape_string(getrelative($filein)).'" where id = '.$id;
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
		if ($id)
			$query = search_qupdcontruct($fid['album'], $fid['title'], $fid['artist'], vernumset($fid['genre'],255), substr($free, strlen($base_dir[$drive])), md5file($free), $id, $fid['lengths'], $fid['ratemode'], $fid['bitrate'], $fid['tagid'], $drive, filemtime($free)); 
		else
			$query = search_qinscontruct($fid['album'], $fid['title'], $fid['artist'], vernumset($fid['genre'],255), substr($free, strlen($base_dir[$drive])), md5file($free), filesize($free), $fid['lengths'], $fid['ratemode'], $fid['bitrate'], $fid['tagid'], $drive, filemtime($free));	
		
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
	<input type="hidden" name="update" value="options"/>
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

// update process
function search_updatelist($options="")
{
	global $base_dir, $win32, $gData, $gCnt;
	kprintheader(get_lang(17), 1);
	
	if (@$options['deleteunused'] == '1') $deleteunused = 1; else $deleteunused = 0;
	if (@$options['debugmode'] == '1') $debugmode = 1; else $debugmode = 0;
	if (@$options['sleeppertrans'] != 0) $sleeptrans = $options['sleeppertrans']; else $sleeptrans = 0;

	$db_out= array();	
	$db_mtime = array();
	$db_unique = array();

	$filecntr=0;
	$file="";

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

		$query = 'SELECT fsize, id, md5, free, drive, mtime, dirname FROM '.TBL_SEARCH.' ORDER BY id ASC';
		$result = db_execquery ($query);

		$dcntr=0;
		while ($row = mysql_fetch_row($result)) 
		{
			$db_out[$dcntr++] = $row;
			if (!isset($db_mtime[$row[0]][$row[5]])) $db_mtime[$row[0]][$row[5]] = $dcntr-1;
			if (!isset($db_unique[$row[0]][$row[2]])) $db_unique[$row[0]][$row[2]] = $dcntr-1;
		}
		
		if ($updateall) $db_mtime = array();
		
		$query=null;
		
		$totalqupds = $dcntr;	
		$failed=0;
		
		echo '<font class="notice">'.get_lang(18, $datacnt).'</font><br/><br/>';
		echo '<div id="up_status" class="notice"></div><br/>';
		flush();

		$totalins = $datacnt;	
		$qins = 0;
		$qupd = 0;
		$qupdins = 0;
		$skips=0;
		$qdels=0;
		$perten=0;
		$fdups=0;
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
							$percent2 = substr($percent2,0,4).'%';
						} else $percent2 = '100%';
						if ($qupd > 0 && $totalqupds>0)
						{ 
							$percent = (($qupd + $qupdins) / $totalqupds) * 100;
							$percent=substr($percent,0,4).'%';
						} else $percent='100%';
						$perout = get_lang(20,$percent2,$percent);					
						echo '<script type="text/javascript">document.all.up_status.innerHTML="'.$perout;
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
								$query = search_qupdcontruct($fid['album'], $fid['title'], $fid['artist'], vernumset($fid['genre'],255), $filein, $md5, $db_out[$idupdate][1], $fid['lengths'], $fid['ratemode'], $fid['bitrate'], $fid['tagid'], $drive, $mtime);							
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
							$fid = get_file_info($file);								
							$query = search_qinscontruct($fid['album'], $fid['title'], $fid['artist'], vernumset($fid['genre'],255), $filein, $md5, $fsize, $fid['lengths'],$fid['ratemode'], $fid['bitrate'], $fid['tagid'], $drive, $mtime);
							$db_out[$dcntr++] = array(-1, 0, $md5, $filein, $drive, $mtime, $filein);
							$db_unique[$fsize][$md5] = $dcntr-1;
							$qins++;
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

		echo '<script type="text/javascript">document.all.up_status.innerHTML="";</script>';         
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

// for automatic update using a browser such as lynx without logging in, etc.
function search_updateautomatic($user, $host, $waittrans=0)
{
	global $autoupdate, $autoupdatehost, $autoupdateuser;

	if (@$autoupdate)
	{
		if ($host == $autoupdatehost && $user == $autoupdateuser)
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

function getarchiveline($archiver, $destination, $file)
{
	global $archivers;
	if (isset($archivers[$archiver]))
	if ($archivers[$archiver][0])
	{
		$out = $archivers[$archiver][2];
		$out = str_replace('%D', $destination, $out); 
		$out = str_replace('%F', $file, $out); 
		return $out;
	}
	return 0;
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
	global $enablegetid3;
	if ($enablegetid3)
	{
		require_once(GETID3_INCLUDEPATH.'getid3.id3v2.php');
		$f2 = new file2($sid, true);
		$data['id3v2']['TIT2']['encodingid'] = 0;
		$data['id3v2']['TIT2']['data']       = $f2->id3['title'];
		$data['id3v2']['TPE1']['encodingid'] = 0;
		$data['id3v2']['TPE1']['data']       = $f2->id3['artist'];
		$data['id3v2']['TALB']['encodingid'] = 0;
		$data['id3v2']['TALB']['data']       = $f2->id3['album'];
		return GenerateID3v2Tag($data['id3v2'], 3, 0, 0, '', false, false, false); 
	}
}

function mkextinf2($name, $lengths)
{
	global $crlf;
	return $crlf.'#EXTINF:'.$lengths.','.$name;
}

function decode64($b64)
{
	if (!empty($b64)) return stripslashes(base64_decode($b64));
}

function kPlay_sendlink2($sid)
{
	global $streamtypes;
	$f2 = new file2($sid, true);
	if ($f2->ifexists())
	{
		$ftype = file_type($f2->fname);
		if (isset($streamtypes[$ftype]) && $streamtypes[$ftype][2] == 1)
		{
			if (db_guinfo('extm3u')) echo mkextinf2($f2->fname, $f2->getlengths());
			httpstreamheader2(file_type($f2->fname), $sid);
		}
	}
}

function kplay_m3uurl()
{
	global $addcrlf;
	$num = lzero(getrand(1,999999),6);
    header('Content-Disposition: inline; filename=kp'.$num.'.m3u');
	header('Content-Type: audio/x-mpegurl');
	if (db_guinfo('extm3u'))
	{ 
		echo '#EXTM3U';
		$addcrlf = true;
	}
}

function Kplay_resource2($sid, $many=0)
{
	global $streamtypes;
	$f2 = new file2($sid, true);
	if ($f2->ifexists())
	{
		$ftype = file_type($f2->fname);
		if ($streamtypes[$ftype][2] == 1)
		{
			kplay_m3uurl();
			if (!$many)
			{
				if (db_guinfo('extm3u')) echo mkextinf2($f2->fname, $f2->getlengths());
				httpstreamheader2($ftype, $sid);
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
	while (!feof($fp))
	{
		echo fread($fp, $breadbuf);
		flush();
		while (!$kqm->alarm()) usleep($precision);
		$kqm->start();
	}
}

function getlame($bitrate=128,$file)
{
	global $lamecmd;
	$out = str_replace('%bitrate%', $bitrate, $lamecmd);
	$out = str_replace('%file%', $file, $out);
	return $out;
}


function Kplay_senduser2($sid, $inline=0)
{
	global $win32, $streamtypes, $_SERVER, $setctl, $streamsettings, $u_id, $lamebitrates;

	$f2 = new file2($sid, true);
	if ($f2->ifexists())
	{
		$fp = fopen($f2->fullpath, "rb");
		if ($fp)
		{
			$ftype = file_type($f2->fname);			
			if ($u_id && db_guinfo('lameperm') && db_guinfo('lamerate') != 0 && $setctl->get('lamesupport') && @$streamtypes[$ftype][3] == 1) $uselame = true; else $uselame = false;
			$posfrom = 0;
			if (isset($_SERVER['HTTP_RANGE']))
			{
				$data = explode('=',$_SERVER['HTTP_RANGE']);
        		$ppos = explode('-', trim($data[1]));
        		$posfrom = (int)trim($ppos[0]);
			}

			if ($posfrom == 0)
			{
				$lastux = getlasthistory($sid);
				if ($lastux + 3 < time()) 
				{
					search_updatevote($sid);
					if ($u_id) addhistory($u_id, $sid);
				}
			}

			if (!ALLOWSEEK) $posfrom = 0;		
		
			header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		
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

			header('Content-Type: '.$streamtypes[$ftype][1]);
			header('Content-Range: bytes '.$posfrom.'-');

			if ($setctl->get('writeid3v2') && @$streamtypes[$ftype][3] == 1 && $posfrom == 0)
			{
				$id = fread($fp, 3);
				if ($id != 'ID3') echo kpgenerateid3v2tag($sid);
				fseek($fp, 0, SEEK_SET);
			}

			if ($posfrom > 0)
			{				
				if ($posfrom == ($f2->fsize - 129) || $posfrom == ($f2->fsize - 128)) // request id3v1
				{
					fseek($fp, -128, SEEK_END);
					echo fread($fp, 128);
					rewind($fp);
				} else fseek($fp, $posfrom);
			}

			if ($uselame)
			{
				ignore_user_abort(true);
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

			} else fpassthru($fp);
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
		header('Content-Disposition: inline; filename='.$name);
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
	global $win32, $streamtypes, $dlrate, $curdrive, $u_id;
	if (ALLOWDOWNLOAD && db_guinfo('u_allowdownload'))
	{
		$f2 = new file2($sid);
		if ($f2->ifexists())
		{
			$fp = fopen($f2->fullpath, 'rb');	
			if ($fp)
			{
				if ($u_id) addhistory($u_id, $sid, 1);
				$ftype = file_type($f2->fname);			
				$mimeheader = 'Content-Type: '.$streamtypes[$ftype][1];
				header($mimeheader);
				header('Content-Disposition: attachment; filename="'.$f2->fname.'"');
				header('Content-Length: '.$f2->fsize);

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
	}
	die();
}

function print_dir($drive,$name, $pdir, $nr, $return=0,$image='dir.gif',$title='', $ainf=null, $mark='', $showalbum = false, $hits = 0)
{
	global $PHP_SELF;
	if (!empty($pdir)) $pdir_64 = base64_encode($pdir); else $pdir_64='';
	$out = '<tr><td>&nbsp;';
	if ($showalbum) 
	{
		$out .= '<a href="'.$PHP_SELF.'?n='.$nr.'&amp;p='.$pdir_64.'&amp;d='.$drive.'&amp;psongsalbum=true" class="dir">';
		$out .= '<img alt="'.get_lang(42).'" src="'.getimagelink('album.gif').'" border="0"/>&nbsp;';
		$out .= '</a>';
	}
	$out .= '<a href="'.$PHP_SELF.'?n='.$nr.'&amp;p='.$pdir_64.'&amp;d='.$drive.'&amp;mark='.urlencode($mark).'" class="dir">';
	$out .= '<img alt="'.get_lang(115).'" src="'.getimagelink($image).'" border="0"';
	if (!empty($title)) $out .= ' title="'.get_lang(116, checkchs($pdir)).'"';
	$out .= '/>&nbsp;'.checkchs($name).'</a>';
	if ($ainf) $out .= ' <span class="finfo">&nbsp;('.get_lang(151, $ainf['length'], $ainf['index']).')</span>'; else
	if ($hits > 0) $out .= ' <span class="finfo">&nbsp;('.$hits.' '.get_lang(243).')</span>';
	$out .= '</td></tr>';
	if ($return) return $out; else echo $out;
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
	global $streamtypes, $enablegetid3;
	$ret = gen_file_header();
	if ($enablegetid3)
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

function file_parse($f2)
{
	global $setctl;
	$str = $setctl->get('filetemplate');
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
					case 'm': $add = $f2->id3['lengthmin']; break;
					case 's': $add = $f2->id3['lengthsec']; break;
					case 'h': $add = $f2->getsearchvar('hits'); break;
					default: $match = false; break;
				}
				if ($match) 
				{
					$i++;
					$str2 .= $add; 
				}
				if ($conditional && empty($add)) $fullfilled = false;
				break;
			default: $str2 .= $str[$i]; break;
		}
	}
	return $str2;
}

function print_file($sid, $showlink=0, $includeabsolute=0, $f2=false, $mark = '')
{
	global $PHP_SELF, $u_cookieid, $setctl, $id3editor;

	if (!$f2) $f2 = new file2($sid, true);
	$inf = $f2->getid3();
	
	if (!empty($inf['title'])) $title = rtrim($inf['title']).' - '.rtrim($inf['album']); else $title='';
	
	$extra = '';
	$extravalue = '';

	if ($showlink) $extra = '<a href="'.$PHP_SELF.'?p='.$f2->getdir64().'&amp;d='.$f2->drive.'&amp;mark='.urlencode($mark).'" title="'.get_lang(116, checkchs($f2->relativepath)).'">'.
	'<img src="'.getimagelink('link.gif').'" alt="'.get_lang(116, checkchs($f2->relativepath)).'" border="0"></a>&nbsp;';
	if ($includeabsolute) $extravalue = ';'.$f2->getdir64();

	$out = '<tr><td>';
	$out .= '<input type="checkbox" name="selected[]" value="'.$sid.'"/> ';

	if ($id3editor && @$inf && $inf['tagid'] == 1 && db_guinfo('u_access') == 0)
	{
		$id3link = '&amp;id3sid='.$sid;
		$out .= '<a href="javascript: void(0);" onclick="javascript: openwin(\'id3editor\', \''.$PHP_SELF.'?id3edit=true'.$id3link.'\');">id3</a>&nbsp;';
	}

	if (ALLOWDOWNLOAD && db_guinfo('u_allowdownload')) 
	$out .= '<span class="file"><a href="'. $PHP_SELF. "?downloadfile=".$sid.'&amp;c='.$u_cookieid.'">'.
	'<img src="'.getimagelink('saveicon.gif').'" alt="'.get_lang(117).'" border="0"/></a></span>&nbsp;';

	if (MAILMP3 && db_guinfo('allowemail') && class_exists('mailmp3'))
	$out .= '<a href="javascript: void(0);" onclick="javascript: newwin(\'mp3mail\', \''.$PHP_SELF.'?sendmail='.$sid.'&amp;c='.$u_cookieid.'\', 180, 390);">'.
	'<img src="'.getimagelink('sendmail.gif').'" alt="'.get_lang(223).'" border="0"/></a></span>&nbsp;';

	$out .=	$extra.'<a href="'.$PHP_SELF.'?sid='.$sid.'&amp;c='.$u_cookieid.'"';
	if (!empty($title)) $out .= ' title="'. $title. '"';
	if (ismarked($f2->fname.$title)) $useclass = 'filemarked'; else $useclass = 'file';
	$out .= '>'.'<span class="'.$useclass.'">'.checkchs($f2->fname).'</span></a>&nbsp;&nbsp;';

	if (!empty($inf['bitrate']) && !empty($inf['length'])) $out .= '<span class="finfo">('.get_lang(152, $inf['bitrate'], $inf['length']).')</span>';

	$out .= '</td></tr>';

	echo $out;
}

function listfiles($where, &$in, $drive)
{
	if ($d = @opendir($where)) 
	{
		while ($file = readdir($d)) if (is_file($where.$file) && file_type($file, 1) != -1) $in[] = array($file, $drive); 
		closedir($d);
	}
}

function disksync($dir, $drive, $root = false)
{
	global $base_dir;
	$flist = array();
	$dblist = array();
	$found = 0;
	if (!$root) 
		$dbres = db_execquery('SELECT free FROM '.TBL_SEARCH.' WHERE dirname = "'.$dir.'" AND drive = '.$drive.' ORDER BY free ASC');
			else
		$dbres = db_execquery('SELECT free FROM '.TBL_SEARCH.' WHERE dirname = "" ORDER BY free ASC');

	while ($row = mysql_fetch_row($dbres)) $dblist[] = $row[0];
	
	if (!$root) listfiles($base_dir[$drive].$dir, $flist, $drive); else
	for ($i=0;$i<count($base_dir);$i++) listfiles($base_dir[$i], $flist, $i);
	
	$c2=count($flist);

	for ($i=0,$c=count($dblist);$i<$c;$i++)
	for ($i2=0;$i2<$c2;$i2++)
	if ($dblist[$i] == $flist[$i2][0]) 
	{
		$found++;
		$flist[$i2][0] = '';
	}

	if ($found != $c2)
	{
		for ($i=0,$c=count($flist);$i<$c;$i++)
			if (!empty($flist[$i][0])) updatesingle($base_dir[$flist[$i][1]].$dir.$flist[$i][0]);
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
			<input type="hidden" name="id3info" value="save"/>
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

function checkstructure($where)
{
	$checkdir=stripslashes(base64_decode($where));
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
	global $dir_list, $lastwhere, $dirignorelist;
	$c = 0;
	
	if ($lastwhere == $where) return; 
	$lastwhere = $where;

	$dir_list = array();	

	if ($dir = @opendir($where)) 
	{
		while ($file = readdir($dir)) if (!isset($dirignorelist[$file])) if (is_dir($where.$file)) $dir_list[$c++] = $file;
		closedir($dir);
	}
	usort($dir_list, 'strcasecmp');
}

function listroot()
{
	global $base_dir, $dir_list, $disksync;
	KCreate_Mp3Table();
	echo '</td></tr>';

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
	array_multisort($sortlist, $drivelist, $nrlist, SORT_STRING);	
	for ($i=0;$i<$sortcnt;$i++) echo print_dir($drivelist[$i],$sortlist[$i], '', $nrlist[$i],1);
	
	if ($disksync) disksync('', 0, true);

	$res = fsearch('/', true);
	$fcnt = mysql_num_rows($res);
	while ($row = mysql_fetch_row($res)) print_file($row[0],0,1);
	
	KCreate_EndMp3Table(0, $dcnt, $fcnt);
	KCreate_infobox();
	?>
	</tr>
	</table>
	<?php	
}

function fsearch($dir, $root = false, $drive = 0)
{
	if (!$root) $sql = 'SELECT id FROM '.TBL_SEARCH.' WHERE dirname = "'.$dir.'" AND drive = '.$drive.' ORDER BY free ASC';	
			else
				$sql = 'SELECT id FROM '.TBL_SEARCH.' WHERE dirname = "" ORDER BY free ASC';
	return db_execquery($sql);
}

function read_dir($pdir, $count=-1, $drive=0)
{
	global $base_dir, $dir_list, $disksync;

	if (!empty($pdir)) $pdir=base64_decode($pdir);

	$pdir = stripslashes($pdir);

	readresources($base_dir[$drive].$pdir);

	if (is_numeric($count) && ($count != -1))
	{
			@$pdir .= $dir_list[$count];
			if (!empty($pdir)) 
			{ 
				if ($pdir[strlen($pdir)-1] != '/') $pdir .= '/'; 
			} else $pdir ="";
			readresources($base_dir[$drive].$pdir);
	}

	
	KCreate_Mp3Table($pdir);
	show_nice_dir($pdir,"",$drive);

	echo '</td></tr>';
	
	if ($disksync) disksync($pdir, $drive);

	$res = fsearch($pdir, false, $drive);

	$dcnt = count($dir_list);
	$fcnt = mysql_num_rows($res);

	if ($fcnt == 0 && $dcnt == 0) echo '<tr><td class="file">'.get_lang(156).'</td></tr>'; 
	else
	{
		for ($i=0;$i<$dcnt;$i++) echo print_dir($drive,$dir_list[$i], $pdir, $i,1);
		while ($row = mysql_fetch_row($res)) print_file($row[0],0,1);
	}
	
	KCreate_EndMp3Table(1, $dcnt, $fcnt);
	KCreate_infobox();
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

function show_nice_dir($pdir, $text="", $drive)
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

function kplaylist_filelist($where, $n=-1, $drive=0)
{
	global $mark;
	if (checkstructure($where) == 0)
	{
		if (isset($_GET['mark']) && !empty($_GET['mark'])) $mark = explode(' ', strtoupper(trim($_GET['mark']))); else $mark = array();
		kprintheader("kPlaylist", 1);
		if ( (!isset($n) || $n == -1) && empty($where)) listroot(); else read_dir($where, $n, $drive);  
		kprintend(); 
	} 
	die();
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
		}
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

if (isset($_POST['signup']) || isset($_GET['signup'])) KSignup();

if (isset($_GET['downloadfile']) || isset($_GET['sid']) || isset($_GET['streamsid']) || isset($_GET['streamplaylist'])) KCheckActions();
if (isset($_GET['update']) && isset($_GET['user'])) search_updateautomatic($_GET['user'],$phpenv['remote'],$_GET['update']);

if (!empty($_POST['user']) && !empty($_POST['password']))
{
	webprocess(); 
	if ($userauth) 
	{
		?>
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
		<html>
			<head>
				<title></title>
				<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
				<meta http-equiv="Refresh" content="0; url=<?php echo $PHP_SELF; ?>?checkcookie=true"/>
			</head> 
			<body></body>
		</html>
		<?php
		die();
	}
	else
	{
		if ($setctl->get('report_attempts')) syslog_write('User could not be validated (user: "'.$_POST['user'].'" / pass: "'.$_POST['password'].'")');
		klogon();			
	}
} 

if (isset($_COOKIE[$cookie_name]) || $disablelogin)
{	
	if (db_verify_stream(@$_COOKIE[$cookie_name], $phpenv['remote']) == 1)
	{
		if (REQUIRE_HTTPS && !$phpenv['https']) klogon();

		if (isset($_COOKIE[$cookie_name])) $u_cookieid = $_COOKIE[$cookie_name]; else $u_cookieid = null;
		
		$deflanguage = db_guinfo('lang');

		if (isset($_POST['search'])) user_saveoption('defaultsearch', vernum($_POST['search'])); 

		if (!empty($_POST['searchfor'])) 
		{	
			$u_searchstr = stripslashes(checkchs($_POST['searchfor']));
			user_saveoption('defaultid3', verchar(@$_POST['onlyid3']));
			user_saveoption('hitsas', verchar(@$_POST['hitsas']));
		} 
		
		if (!empty($_POST['sel_playlist'])) user_saveoption('defplaylist', vernum($_POST['sel_playlist']));

		if (!empty($_POST['sel_shplaylist'])) 
		{
			user_saveoption('defshplaylist', vernum($_POST['sel_shplaylist']));
			$_POST['sel_playlist'] = $_POST['sel_shplaylist'];
		}		
		
		if (isset($_GET['randomizer']) || isset($_POST['randomizer'])) 
		{
			if (class_exists('kprandomizer'))
			{
				$rz = new kprandomizer();
				if (isset($_POST['mode'])) $rz->setmode($_POST['mode']);
				if (isset($_POST['limit'])) $rz->setlimit($_POST['limit']);
				if (isset($_POST['genre'])) $rz->setgenre($_POST['genre']);
				if (isset($_POST['playlist'])) $rz->setplaylist($_POST['playlist']);
				if (isset($_POST['order'])) $rz->setorder($_POST['order']);
				if (isset($_POST['execute'])) $rz->execute(); else $rz->view();
				die();
			}
		}
		
		if (isset($_GET['sendmail']) || isset($_POST['sendmail']))
		{
			if (class_exists('mailmp3'))
			{
				$mail3 = new mailmp3();
				if (isset($_GET['sendmail'])) $mail3->setsid($_GET['sendmail']);
				$mail3->decide();
			}
		} else
		if (isset($_GET['fupload']) || isset($_POST['fupload']))
		{
			if (class_exists('fupload'))
			{
				$fu = new fupload();				
				$fu->decide();
			}
		} else
		if (isset($_POST['whatshot'])) whats_hot(db_guinfo('hotrows'));
		else 
		if (isset($_POST['hotoptions'])) 
		{
			if (isset($_POST['hotperiod']) && is_numeric($_POST['hotperiod'])) $filter = $_POST['hotperiod']; else $filter = 0;
			whats_hot(db_guinfo('hotrows'), $filter);
		}
		else 
		if (isset($_POST['whatsnew'])) whats_new(db_guinfo('hotrows'));
		else		
		if (isset($_GET['artist'])) hotselect($_GET['artist']);			
		else
		if (isset($_POST['psongsselected']) || isset($_POST['psongsall']) || isset($_GET['psongsalbum']))
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
			} else
			if (isset($_GET['psongsalbum']))
			{
				$res = fsearch(decode64($_GET['p']), false, $curdrive);
				kplay_m3uurl();	
				while ($row = mysql_fetch_row($res)) kPlay_sendlink2($row[0]);
			}
		} else
		if (isset($_POST['pdlselected']) || isset($_POST['pdlall']))
		{
			$usearc = 0;
			$preferarc = db_guinfo('archer');
			if (isset($archivers[$preferarc]) && $archivers[$preferarc][0] == 1) $usearc = db_guinfo('archer');
			
			$f = tempnam($archivetemp, 'kppack');
			$f .= '.'.$archivers[$usearc][1];

			$cwd = getcwd();
			$error = 0;

			if (isset($_POST['pdlselected']))
			{
				if (isset($_POST['selected']))
				{	
					for ($i=0;$i<count($_POST['selected']);$i++)
					{
						$f2 = new file2($_POST['selected'][$i]);
						if ($f2->ifexists())
						{
							chdir($f2->getdrivedir());
							$run = getarchiveline($usearc, $f, $f2->getfullpath($win32));
							if (!empty($run)) exec($run); 						
						}
					}				
				} else die();				
			} else 
			if (isset($_POST['pdlall']))
			{
				$res = fsearch(decode64($_POST['previous']), false, $curdrive);
				while ($row = mysql_fetch_row($res)) 
				{
					$f2 = new file2($row[0]);
					if ($f2->ifexists())
					{
						$run = getarchiveline($usearc, $f, $f2->getfullpath($win32));
						if (!empty($run)) exec($run);					
					}
				}				
			}
			chdir($cwd);
			if (!file_exists($f)) errormessage(get_lang(167));
			kplay_archivedownload($f, $archivers[$usearc][3], 'kpdl'.date('hi').'.'.$archivers[$usearc][1]);
			@unlink($f);
		}
		else 
		if (!empty($_GET['editoptions'])) show_useroptions();
		else		
		if (isset($_GET['users']) && $_GET['users'] == 'modify')
		{
			if (db_guinfo('u_access') == 0)
			{
				if (!empty($_GET['del'])) 
				{
					$id = $_GET['del'];
					if (is_numeric($id)) db_execquery('DELETE FROM '.TBL_USERS.' WHERE u_id = '.$id);
				} 
				else
				if (!empty($_GET['logout'])) 
				{
					$id = $_GET['logout'];
					if (is_numeric($id)) if (!$demo_mode) db_execquery('UPDATE '.TBL_USERS.' SET u_sessionkey = 0, u_status = 0 WHERE u_id = '.$id);
				} else
				if (!empty($_GET['edit']))
				{
					$id = $_GET['edit'];
					show_new_user_form($id);
					die();
				} else
				if (!empty($_GET['history']))
				{
					show_userhistory($_GET['history']);
					die();
				}
			}
			show_users();
		} 
		else
		if (isset($_POST['useroptions']) && $_POST['useroptions'] == 'save')
		{
			$state = save_useroptions($_POST);
			switch ($state)
			{
				case 2: show_useroptions(get_lang(157),true); break;
				case 3: show_useroptions(get_lang(165),true); break;
				default: show_useroptions(null,true); break;
			}
		} 
		else		
		if (isset($_POST['formusers']) && $_POST['formusers'] == 'userchange')
		{
			if (db_guinfo('u_access') == 0)
			{
				if (!empty($_POST['Submit']))
				{
					isset($_POST['passchange']) ? $changepw = 1 : $changepw = 0;
					isset($_POST['booted']) ? $booted = 1 : $booted = 0;
					isset($_POST['lameperm']) ? $lameperm = 1 : $lameperm = 0;
					isset($_POST['download']) ? $download = 1 : $download = 0;
					isset($_POST['allowemail']) ? $allowemail = 1 : $allowemail = 0;
					
					
					$id = $_POST['u_id'];
					$name = mysql_escape_string($_POST['name']);
					$login = mysql_escape_string($_POST['login']);
					$pass = mysql_escape_string($_POST['password']);
					$comm = mysql_escape_string($_POST['comment']);
					$access = mysql_escape_string($_POST['access']);
					if (is_numeric($_POST['udlrate'])) $udlrate = $_POST['udlrate']; else $udlrate = 0;

					if (empty($pass) && $changepw)
					{
						show_new_user_form($id,$name,$pass,$comm,$login,$access,$download,$udlrate);
						die();
					} 
					if (empty($name) || empty($login) ) 
					{
						show_new_user_form($id,$name,$pass,$comm,$login,$access,$download, $udlrate);
						die();
					}
					$pass = md5($pass);
					if ($id == -1) $query = 'INSERT into '.TBL_USERS.' set u_name = "'.$name.'", u_login = "'.$login.'", u_pass = "'.$pass.'",  u_comment = "'.$comm.'", u_access = '.$access. ', u_allowdownload = "'.$download.'", lang = "'.$setctl->get('default_language').'", udlrate = '.$udlrate.', allowemail = '.$allowemail; 
					else
					{
						if ($changepw) $query = "UPDATE ".TBL_USERS." set u_name = \"$name\", u_login = \"$login\", u_pass=\"$pass\", u_comment = \"$comm\", u_booted = $booted, u_access = $access, u_allowdownload = \"$download\", udlrate = $udlrate, lameperm = $lameperm, allowemail = $allowemail where u_id = $id"; 
						else
						$query = "UPDATE ".TBL_USERS." set u_name = \"$name\", u_login = \"$login\", u_booted = $booted, u_comment = \"$comm\", u_access = $access, u_allowdownload = \"$download\", udlrate = $udlrate, lameperm = $lameperm, allowemail = $allowemail where u_id = $id";
					}
					db_execquery($query);
					show_users();
					die();
				} else show_users();
			}			
		}
		else
		if (isset($_POST['formusers']) && $_POST['formusers'] == 'modify')
		{
			if (db_guinfo('u_access') == 0)
			{
				if (!empty($_POST['newuser']))
				{
					show_new_user_form();
					die();
				} else
				if (!empty($_POST['edit']))
				{
					show_new_user_form($_POST['id']);
					die();
				} else
				if (isset($_POST['history']))
				{
					show_userhistory($_POST['id'], $_POST['length']);
					die();
				}
			}
			show_users();			
		} else		
		if (!empty($_POST['searchfor']) )
		{
			if (!empty($_POST['onlyid3'])) $idv3=1; else $idv3=0;
			isset($_POST['hitsas']) ? $hitsas = $_POST['hitsas'] : $hitsas = 0;
			search($_POST['searchfor'], $_POST['search'], $idv3, $hitsas);
		} else
		if (isset($_POST['logmeout']))
		{ 
			if ($demo_mode != 1) db_logout($u_cookieid, $phpenv['remote']); 
			klogon(); 
		} else		
		if (isset($_GET['action']) && $_GET['action'] == 'playlist_new')
		{
			playlist_new();
		}
		else
		if (isset($_GET['users'])) 
		{
			if (db_guinfo('u_access') == 0) show_users();			
		}
		else
		if (isset($_GET['id3edit']))
		{
			if (db_guinfo('u_access') == 0) 
			{
				$f2 = new file2($_GET['id3sid']);
				if ($f2->ifexists()) file_id3editor($f2->fullpath);
			}
		} 
		else
		if (isset($_POST['id3info']))
		{
			file_id3editor_save(stripcslashes(base64_decode($_POST['file'])), $_POST);
			file_id3editor(stripcslashes(base64_decode($_POST['file'])));
		} 
		else
		if (isset($_GET['filelist']))
		{
			if (db_guinfo('u_access') == 0) search_updatelist_options();
		} 
		else
		if (isset($_GET['settings']))
		{
			isset($_GET['page']) ? $page = $_GET['page'] : $page = 0;
			isset($_GET['reload']) ? $reload = $_GET['reload'] : $reload = 0;
			if (db_guinfo('u_access') == 0 && isset($_GET['settings']) && $_GET['settings'] == 'edit') settings_edit($reload, $page); 
		} 
		else
		if (isset($_POST['settings']))
		{
			if (db_guinfo('u_access') == 0 && isset($_POST['settings']) && $_POST['settings'] == 'save') 
			{
				isset($_POST['page']) ? $page = $_POST['page'] : $page = 0;
				settings_save($_POST, $page); 
				settings_edit(1, $page);
			}
		} 
		else
		if (isset($_GET['editfiletype']))
		{
			if ($_GET['editfiletype'] == 'delete')
			{
				db_execquery('DELETE from '.TBL_FILETYPES.' WHERE id = '.vernum($_GET['del']));
				loadstreamtypes();
				settings_edit(1, 3);
			} else
			edit_filetype($_GET['editfiletype']);
		} 
		else
		if (isset($_POST['store_filetype']))
		{
			if (isset($_POST['extension'])) $extension = $_POST['extension']; else $extension = '';
			if (isset($_POST['m3u'])) $m3u = 1; else $m3u = 0;
			$id = store_filetype(vernum($_POST['id']), $m3u, mysql_escape_string($_POST['mime']), $extension);
			edit_filetype(vernum($id), true);
		} 
		else
		if (isset($_GET['action']) && $_GET['action'] == 'editplaylist')
		{
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
			playlist_editor($plid,$_GET['p']);
		}
		else
		if (!empty($_POST['genrelist']))
		{
			user_saveoption('defgenre', @$_POST['genreno']);
			genrelist($_POST['genreno']);
		}
		else
		if (isset($_POST['action']) && $_POST['action'] == 'playlist')
		{
			if (!empty($_POST['saveseq'])) 
			{
				playlist_savesequence($_POST['seq'],$_POST['sel_playlist']);
				playlist_editor($_POST['sel_playlist'], $_POST['previous'], $_POST['sort']);
				die();
			}
			if (!empty($_POST['addplaylist']))
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
			if (!empty($_POST['saveplaylist']))
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
			} 
			else
			if (!empty($_POST['sortplaylist']))
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
			}
			else
			if (!empty($_POST['playplaylist']))
			{
				if (isset($_POST['sel_playlist']) && is_numeric($_POST['sel_playlist']))
				{
					$kp = new kp_playlist($_POST['sel_playlist']);
					$kp->play();
				}
				/*$result = db_execquery('SELECT status FROM '.TBL_PLAYLIST.' WHERE listid = '.$plid);
				if ($result)
				{
					$row = mysql_fetch_array($result);
					$shuffle = $row['status'];
				}				
				$result = db_execquery('SELECT * FROM '.TBL_PLAYLIST_LIST.' WHERE listid = '.$plid.' ORDER BY seq ASC', true);
				$tunes = array();
				$i=0;
				while ($row = mysql_fetch_array($result)) $tunes[$i++] = $row['sid'];
				$cnt = $i;
				if ($shuffle)
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
				for ($i=0;$i<$cnt;$i++) kPlay_sendlink2($tunes[$i]);*/
			}
			else
			if (!empty($_POST['deleteplaylist']))
			{
				if (is_numeric($_POST['sel_playlist']))
				{
					$id = $_POST['sel_playlist'];
					playlist_delete($id);
					kplaylist_filelist($_POST['previous'],-1,$_POST['drive']);
					die();
				}
				playlist_editor($_POST['sel_playlist'], $_POST['previous'], $_POST['sort']);				
			} 
			else
			if (!empty($_POST['editplaylist']) || !empty($_POST['viewplaylist']))
        	{
			   $pre=$_POST['previous'];
			   playlist_editor($_POST['sel_playlist'], $pre);			
			} 
			else
			if (!empty($_POST['playselected']))
			{
				kplay_m3uurl(); 	
				for ($i=0,$c=count($_POST['selected']);$i<$c;$i++)
				{
					$row = mysql_fetch_array(db_execquery('SELECT * FROM '.TBL_PLAYLIST_LIST.' WHERE id = '.$_POST['selected'][$i]));
					kPlay_sendlink2($row['sid']);					
				}				
			} 
			else
			if (!empty($_POST['delselected']))
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
			} else playlist_editor($_POST['sel_playlist'], $_POST['previous'], $_POST['sort']);
		} 
		else
		if (!empty($_POST['newplaylist']))
		{
			if (empty($_POST['name'])) 
			{ 
				playlist_new(); 
			} else
			{
				if (@$_POST['shared'] == 'on') $shared=1; else $shared = 0;
				$added = playlist_createnew($_POST['name'],$shared);
				kprintheader(get_lang(61), 1);    				
				echo '<font color="#000000" class="notice">';
				if ($added) echo get_lang(35); else echo get_lang(137);
				echo '</font><br/><br/>';
				echo '<a href="javascript:void(0);" onclick="javascript: window.close(); window.opener.location.reload();"><font color="blue">'.get_lang(27).'</font></a>';
				if ($added) echo '<font class="notice"> - '.get_lang(36).'</font>';
				kprintend();
			}
		} 
		else
		if (isset($_POST['update']) && $_POST['update'] == 'options')
		{
			if (db_guinfo('u_access') == 0) search_updatelist($_POST);
		}
		else 
		{
			isset($_GET['p']) ? $p = $_GET['p'] : $p = null;
			isset($_GET['n']) ? $n = $_GET['n'] : $n = null;
			isset($_GET['d']) ? $d = $_GET['d'] : $d = null;
			kplaylist_filelist($p, $n, $d);
		}
	} else
	{
		klogon();
	}
} else if (isset($_GET['checkcookie']))
{
	klogon(get_lang(237));
} else klogon();

?>
