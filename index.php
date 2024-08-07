<?php
//kPlaylist 1.8 Build 512 (20-03-15_23.54)

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

kPlaylist makes your music archive available via the WEB. Play music, 
	search, create and edit playlists from everywhere by just having a webbrowser 
	and a audio player. Features include logon, accounts, account classes, user editor, 
	automatic installation (MySQL), upload, download, archive download and much more.

Are you a PHP programmer? 
	Would you like to join us in the creation of this product? Before you start 
	changing the code please send a mail to us and tell us that you want to help us. 
	
Note!
	You can get updates and installation instructions here: http://www.kplaylist.net
  
	We develop other products than PHP applications, for commercial and non
	commercial use. Contact our company FirstIT AS here: http://www.firstit.no

Script information:
	Also note, this is a script under construction and strange things may happen,
	though it hasn't on the machines we tested it on. The system writes by
	default only to a MySQL database, but can also be set up to write
	id3v1 tags (mp3 files.).

	Due to the legal responsibility however, we have to note: There
	are NO GUARANTEES WHATSOEVER other than this application will
	occupy certain amount of space on the device you put it.

*****************************************************************************/

// try to set the execution time to 86400 sec = 1 day. 
@ini_set('max_execution_time', 86400);
@ini_set('register_globals', 'Off');
@ini_set('display_errors', 'Off');

// if you for any reason want to reset the configuration, set this variable to true, reload the page and set it back to false.
$resetconfiguration = false;

if (file_exists('kpconfig.php')) include('kpconfig.php');

$db = array(
	'host' => 'localhost', # MySql server
	'name' => 'kplaylist', # Database name
	'user' => 'kplaylist', # MySql user
	'pass' => 'kplaylist', # MySql password
	'prepend' => 'tbl_'    # To prepend before the table names
);
if (isset($cfg['db_host'])) {
  $db = array(
    'host' => $cfg['db_host'], # MySql server
    'name' => $cfg['db_name'], # Database name
    'user' => $cfg['db_user'], # MySql user
    'pass' => $cfg['db_pass'], # MySql password
    'prepend' => $cfg['db_prepend'], # To prepend before the table names
  );
}
$link = false;

// what to prepend before the table names, don't change this after installing! Do it before.
$cfg['dbprepend'] = $db['prepend'];

// If you use the Bad Blue webserver, set the following value to 1
$cfg['badblue'] = 0;

// If you want to disable logins and let everybody with http access to your
// site get in, change the two following options. (WARNING! ALL SECURITY NOW VANISH.)
$cfg['disablelogin'] = 0;

// If you disable logins, a default user has to be chosen. Setting this to 1 means
// the first user which is usually the admin.
$cfg['assumeuserid'] = 1;

// enable the getid3 package. getid package must reside under getid3/ under the directory
// this file exists. If it does not, please change the 'include' statement below.
$cfg['enablegetid3'] = 0;

// where the getid3.php file exists
$cfg['getid3include'] = 'getid3/getid3.php';

//how many titles of one album do we need to treat as a album? Turn to zero to show all.
$cfg['titlesperalbum'] = 0;

// for multiple downloads.
$cfg['archivemode'] = false;

$cfg['archivefilelist_cr'] = "\n";

// turn this on to show commands when creating INSTEAD of executing 
$cfg['archivemodedebug'] = false;

// where archivemode stores data. For UNIX it should be /tmp/, For win32 it should be: c:\\tmp\\
$cfg['archivetemp'] = '/tmp/'; 

// Read here before enabling: http://www.kplaylist.net/forum/viewtopic.php?t=196
$cfg['id3editor'] = 0;

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
$cfg['dateformat'] = 'd.m.y H:i';

$cfg['timeformat'] = 'H:i';

// format when listing periods in what's hot
$cfg['dateformatwhatshot'] = 'M Y';

// small format
$cfg['smalldateformat'] = 'd.m.y';

// if the dir count exceeds this count, it will not be considered a 'album' directory and albums will not be shown
$cfg['isalbumdircount'] = 1;

// sort the root? does not affect sorting in subdirs.
$cfg['sortroot'] = true;

// to reopen an uri after logon
$cfg['accepturi'] = true;

// where to cut the front bulletin message
$cfg['frontbulletinchars'] = 120;

// where to break 'last stream' titles
$cfg['laststreambreak'] = 33;

// miniumum hits to show in whats'hot
$cfg['whatshotminimumhits'] = 5;

// lame command (transcode)
$cfg['lamecmd'] = '/usr/local/bin/lame --silent --nores --nohist --mp3input -h -m s -b %bitrate% "%file%" -';

// ogg command  (transcode)
$cfg['oggcmd'] = '/usr/bin/oggdec -Q "%file%" -o - | /usr/bin/oggenc - --quiet --managed -b %bitrate% -o -';

// enable ogg transcoding, look the line above for the command, check this before enabling
$cfg['oggtranscode'] = false;

$lamebitrates = array(0, 32, 40, 48, 56, 64, 80, 96, 112, 128, 160, 192, 224, 256, 320);

//	enabled	(0/1)	extension	cmd	(%D = destination file,	%F source OR %LIST if using filelist.)  mime	name
// YOU MUST SUIT THESE ARCHIVERS TO YOUR OWN NEED. DO NOT USE THE DEFAULT BLINDLY.
$archivers = array();

$archivers[] = array(1,	'zip', '/usr/bin/zip -j -0 %D "%F"', 'application/zip', 'zip');
$archivers[] = array(1, 'tar','/bin/tar cf %D --files-from "%LIST"', 'application/x-tar', 'tar');
$archivers[] = array(0,	'rar', 'C:\Programfiler\WinRAR\rar.exe -m0 a %D "%F"', 'application/x-rar', 'rar');

// stream 'engine' finetune settings. 
$streamsettings = 
array(
	'preload'			=> 215,
	'buffer'			=> 105,
	'sleep'				=> 0.999,
	'bitrates'			=> array(32, 32, 40, 48, 56, 64, 80, 96, 112, 128, 160, 192, 224, 256, 320, 512),
	'defaultrate'		=> 288,
	'precision'			=> 1000,
	'forcedefaultrate'	=> 0
);

// syntax: .filename, mime header, file in M3U, get id function, viewable, log access
$streamtypes_default = array(
	0 => array	('mp3',		'audio/mpeg',				1, 1, 1, 1),
	1 => array	('mp2',		'audio/mpeg',				1, 1, 1, 1),
	2 => array	('ogg',		'application/x-ogg',		1, 2, 1, 1),
	3 => array	('wav',		'audio/wave',				1, 0, 1, 1),
	4 => array	('wma',		'audio/x-ms-wma',			1, 0, 1, 1),
	5 => array	('mpg',		'video/mpeg',				1, 0, 1, 1),
	6 => array	('mpeg',	'video/mpeg',				1, 0, 1, 1),
	7 => array	('avi',		'video/avi',				0, 0, 1, 1),
	8 => array	('wmv',		'video/x-ms-wmv',			1, 0, 1, 1),
	9 => array	('asf',		'application/vnd.ms-asf',	0, 0, 1, 1),
	10 => array	('m3u',		'audio/x-mpegurl',			0, 0, 0, 0),
	11 => array	('flac',	'audio/x-flac',				1, 0, 1, 1),
	12 => array	('jpg' ,	'image/jpeg',				0, 0, 1, 0),
	13 => array	('gif' ,	'image/gif',				0, 0, 1, 0),
	14 => array	('png' ,	'image/png',				0, 0, 1, 0)
);

// filetypes to include or ignore when showing statistics 
// use * for everything, "-" and "+" for ignore and include.  NB! If change, do an (normal) update.
$cfg['stat_count_ftype'] = array('*'); // example for everything except jpg, gif, png: array('*', '-12', '-13', '-14');

// files to look for to detect basedirs
$cfg['detecttypes'] = array('.mp3' => 1, '.mp2' => 1, '.ogg' => 1, '.wma' => 1);

// dirs to ignore when detecting base dirs (find tool)
$cfg['detectignoredirs'] = array('temp', 'tmp', 'temporary internet files', 'documents and settings', 'winnt', 'windows', 'win32', 'win nt');

// when using getid3 and id3 tags - highest most importnant, 0 to disable
$cfg['id3tagspri'] = array('id3v1' => 1, 'id3v2' => 2);

// maximum size in bytes for album images (if and when auto id3v2 tag.)
$cfg['maxtagimagesize'] = 1048576; // set to zero for no limit - default 1 mb.

// if you enabled urlsecurity in settings, you can specify how long a url will be valid (in seconds) (0 for no limit)
$cfg['urlsecurityvalidtime'] = 0;

// order by tracks
$cfg['ordertrack'] = true;

// when resizing jpeg album
$cfg['jpeg-quality'] = 90;

// resize album images the same way via web as with stream?
$cfg['id3v2albumresize'] = true;

// map design to files instead of inbuilt ('' means inbuilt). set one or each to a relevant filename to customize,
// download the template from here: http://www.kplaylist.net/getdesign.php
$cfg['designmap'] = 
array(
		'login' => '', 
		'infobox' => '', 
		'endmp3table' => '', 
		'top' => '', 
		'bottom' => '', 
		'blackbox' => '', 
		'detailedview' => '', 
		'dirheader' => ''
);

// how many last stream titles to show
$cfg['laststreamscount'] = 6;

// if using pear (mailmp3), where should we include pear mail files? (relevant to php general include path, pear lib should be in this)
$cfg['pearmailpath'] = 'Mail/';

$cfg['striphtmlbulletin'] = true; // strip away tags from bulletin (when storing.)

// many file select boxes in upload
$cfg['uploadselections'] = 3;

// many streams in rss?
$cfg['rsslaststreamcount'] = 25;

// how to group artist/albums from whatsnew. Default: album, artist (before build 420)
$cfg['albumartistgroup'] = array('album', 'artist'); 

// can be switched to false after installing for improved security
$cfg['installerenabled'] = true;

// merge root directories? (to avoid duplicate directory names)
$cfg['mergerootdir'] = false;

// convert filesystem (directories) during display? Needs iconv support.
$cfg['convertcharset'] = false;

// which charset to convert from. for other charsets, please look here: http://no2.php.net/manual/en/ref.iconv.php
$cfg['filesystemcharset'] = 'UTF-8'; 

// count of logins (many times one can login concurrently with the same credentials), 0 means indefinite.
 $cfg['numberlogins'] = 0;

// dirlist: sort each row (1) or each column (2)?
$cfg['columnsorttype'] = 2;

// enable httpQ support
$cfg['httpq_support'] = false;

$cfg['httpq_parm'] = array(
		'server'	=> 'localhost',
		'port'		=> 4800,
		'pass'		=> 'test'
);

// ajax update live streams -- requires that Settings->Customize->AJax url is filled out (and correct.)
$cfg['livestreamajax'] = false;

// number of milliseconds to update (interval) (default 5 seconds.)
$cfg['livestreamajaxupdatetime'] = 5000;

// number of milliseconds to update (interval) (default 5 seconds.)
$cfg['shoutboxupdatetime'] = 5000;

// how many messages to show
$cfg['shoutboxmessages'] = 5;

// enable radio functionality? (NEED icecast/ices2++) Read forum.
$cfg['radio'] = false;

// use _ in hotselect for a new line. If UTF-8 characters, make sure your editor supports it!
$cfg['hotselectchars'] = '*0abcdefghijklmnopqrstuvwxyz';

// customized/personal genres, if adding/changing needs an re-update with id3rebuild for changes to take effect.
// example: = array('My genre', 'Another genre', 'My genre 3');
$cfg['custom_genres'] = array();

// enable auto creating genres by name, set to false to disable, getid3 is required.
$cfg['genre_auto_create'] = true;

// allow users their own homedir (set homedir in usereditor)
$cfg['userhomedir'] = false;

// operate in UTF-8 mode? Recommended if you have any users speaking other
// languages than english and if you have music with other titles than english. 
// NB!! Do not switch this to true unless you know what you're doing.
$cfg['utf8mode'] = false;

// this setting is for utf8 mode, when converting file and directory names into utf-8 during update
$cfg['utf8_translate_from'] = 'ISO-8859-1, UTF-8'; // see list here: http://no2.php.net/manual/en/mbstring.supported-encodings.php

// if you wish to use URL's such as http://kplaylist/music/04343/File.mp3 instead of the ones
// with streamsid, cookieid, etc. This currently only works with stream urls and it REQUIRES
// that your web server redirects calls.
$cfg['filepathurl'] = false;

// start, but do not end with slash 
$cfg['filepathurlprepend'] = '/music';

// authtype, cookie default, session is the alternative
$cfg['authtype'] = 1; // 1=cookie, 2=session

$cfg['musicmatch'] = true; // inbuilt music match support? Default true (randomizer feature.)

// size of window for "external" player
$cfg['window_x'] = 420;
$cfg['window_y'] = 220;

// edit this to suit your setup
$cfg['xspf_url'] = 'http://mysite/blah/xspf_player.swf';

// enable xspf? Make sure the URL above works.
$cfg['xspf_enable'] = false;

// JW: edit this to suit your setup (for jw player 3!)
$cfg['jw_urls'] = 
array(
	'swf'	=> 'jw/mediaplayer.swf',
	'js'	=> 'jw/swfobject.js'
);

// jw player version 6
$cfg['jw6_url'] = '';

// size of window for "external" player
$cfg['jw_window_x'] = 500;
$cfg['jw_window_y'] = 550;

// enable jw player? Make sure the URLs above works.
$cfg['jw_enable'] = false;
$cfg['jw6_enable'] = false;

// end of configuration
if (file_exists('kpconfig.php')) include('kpconfig.php');

function geturi()
{
	global $phpenv;
	if (frm_isset('uri')) $uri = frm_get('uri'); else $uri = urlencode($phpenv['uri']);
	return stripslashes(strip_tags($uri));
}

function make_seed() {
    list($usec, $sec) = explode(' ', microtime());
    return (float) $sec + ((float) $usec * 100000);
}

function mkor($arr, $name)
{
	$sql = '';
	if (is_array($arr))
	{
		$sql = $name;
		foreach($arr as $g) $sql .= ' = '.$g.' OR '.$name;
		return substr($sql, 0, strlen($sql) - (strlen($name) + 4));
	}
	return $sql;
}

function access_denied()
{
	echo 'Access denied.';
	die();
}

function refreshurl($url)
{
	?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html>
		<head>
			<title></title>
			<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1"/>
			<meta http-equiv="Refresh" content="0; url=<?php echo $url; ?>"/>
		</head>
		<body></body>
	</html>
	<?php
}

function syslog_write($msg)
{
	global $phpenv, $win32;
	$msg = 'Client '.$phpenv['remote'].' '.$phpenv['useragent'].' '.$msg;
	if (!$win32)
	{
		if (function_exists('define_syslog_variables')) define_syslog_variables();
		openlog('kplaylist', LOG_PID | LOG_PERROR, LOG_LOCAL0);
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

function genselect($name, $options, $default=0, $disabled = false, $class='fatbuttom', $width=0, $id='')
{
	$out = '<select name="'.$name.'" class="'.$class.'"';
	if (strlen($id) > 0) $out .= ' id="'.$id.'"';
	if ($width > 0) $out .= ' style="width:'.$width.'px"';
	if ($disabled) $out .= ' disabled="disabled"';
	$out .= '>';
	for ($i=0,$c=count($options);$i<$c;$i++)
	{
		$out .= '<option value="'.$options[$i][0].'"';
		if ($options[$i][0] == $default) $out .= ' selected="selected"';
		$out .= '>'.$options[$i][1].'</option>';
	}
	$out .= '</select>';
	return $out;
}

function selectoptions($arr, $default)
{
	// input is single dimensioal array
	$out = '';
	foreach($arr as $id => $val)
	{
		$out .= '<option value="'.$id.'"';
		if ($id == $default) $out .= ' selected="selected"';
		$out .= '>'.$val.'</option>';
	}
	return $out;
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
	{
		$out = str_replace($key, $rep, $in);
		if ($out[strlen($out)-1] != '/') $out .= '/';	
	}
	return stripslashes($out);
}

function noslash($in)
{
	if (strlen($in) > 1)
		if ($in[strlen($in)-1] == '/') return substr($in, 0, strlen($in) - 1);
	return $in;
}

function relativedir($dir)
{
	$relative = '';

	if (isset($_SERVER['DOCUMENT_ROOT']))
	{
		$docroot = $_SERVER['DOCUMENT_ROOT'];
		
		if (strlen($dir) >= strlen($docroot))
		{
			if (substr($dir, 0, strlen($docroot)) == $docroot) 
			{
				$relative = substr($dir, strlen($docroot));		
				
			}
		}
	}

	return $relative;
}

function slashend($in)
{
	$out = $in;
	$lastchar = '';
	if (strlen($out) > 0) $lastchar = $out[strlen($out)-1]; 	
	if ($lastchar != '/') $out .= '/';	
	return $out;
}

function slashstart($in)
{
	$first = '';
	if (strlen($in) > 0) $first = $in[0];
	if ($first != '/') return '/'.$in;
	return $in;
}

function checkcharadd(&$string, $chars, $add)
{
	if (kp_strlen($string) > 0)
	{
		$test = kp_substr($string, kp_strlen($string) - kp_strlen($chars));
		if ($test == $chars) $string .= $add; else $string .= $chars.$add;
	} else $string = $add;
}

function getimagelink($image)
{
	global $setctl, $kpt;
	
	if ($link = $kpt->getfile($image))
	{
		if (strlen($link) > 0) return $link;
	}
	
	if (!empty($setctl->keys['externimagespath'])) return $setctl->get('externimagespath').$image; else return PHPSELF.'?image='.$image;
}

function gethtml($page)
{
	global $kdesign, $cfg, $kpt;

	if (isset($cfg['designmap'][$page])) $f = $cfg['designmap'][$page]; else $f = '';

	if ($link = $kpt->getlocalfile($page.'.kpp'))
	{
		if (strlen($link) > 0 && file_exists($link)) $f = $link; 
	}

	if (strlen($f) > 0)
	{
		$fp = fopen($f, 'rb');
		if ($fp)
		{
			$data = fread($fp, filesize($f));
			fclose($fp);
			return $data;
		}
	}
	if (isset($kdesign[$page])) return $kdesign[$page];
}

function addsq()
{
	return "'";
}

function trspace($height)
{
	echo '<tr><td height="'.$height.'"></td></tr>';
}

function isphp5()
{
	$ver = phpversion();
	if (substr($ver, 0, 1) >= '5') return true;
	return false;
}

function kp_strlen($str)
{
	if (UTF8MODE) return mb_strlen($str);
	return strlen($str);
}

function kp_tolower($str)
{
	if (UTF8MODE) return mb_strtolower($str);
	return strtolower($str);
}

function kp_substr($str, $off, $to=0)
{
	if (UTF8MODE) 
	{
		if ($to > 0) 
			return mb_substr($str, $off, $to);
		else 
			return mb_substr($str, $off);
	}
	if ($to > 0) 
		return substr($str, $off, $to); 
	else
		 return substr($str, $off);
}

function kp_basename($in)
{
	global $win32;
	if (!$win32)
	{
		$t = substr(strrchr($in, '/'), 1);
		return $t ? $t : $in; 
	} else return basename($in);
}

function webpdir($pdir, $url=true)
{
	if ($url) return urlencode(base64_encode($pdir));
		else return base64_encode($pdir);
	
}


// string handling

function vernum($in)
{
	if (is_numeric($in)) return $in; else return 0;
}

function vernumset($in, $value)
{
	if (is_numeric($in)) return $in; else return $value;
}

function verchar($in)
{
	if ($in == '1' || $in == '0') return $in; else return 0;
}

// new handling

function frm_isset($name)
{
	if (isset($_POST[$name]) || isset($_GET[$name])) return true;
	return false;
}

function frm_get($name, $type=0, $default='')
{
	$retval = $default;
	
	if (frm_isset($name))
	{
		if (isset($_POST[$name])) $val = $_POST[$name]; 
			else
		if (isset($_GET[$name])) $val = $_GET[$name];
	
		switch($type)
		{
			case 0: $retval = stripslashes(strip_tags($val));
			case 1: if (is_numeric($val)) $retval = $val;
			case 2: if ($val == '1' || $val == '0') $retval = $val;
			case 3: if (is_array($val)) $retval = $val;
		}
	}

	return $retval;
}

function frm_ok($name, $type)
{
	if (frm_isset($name))
	{
		if (isset($_POST[$name])) $val = $_POST[$name]; 
			else
		if (isset($_GET[$name])) $val = $_GET[$name];
	
		switch($type)
		{
			case 0: return true;
			case 1: if (is_numeric($val)) return true;
			case 2: if ($val == '1' || $val == '0') return true;
			case 3: if (is_array($val)) return true;
		}
	}

	return false;
}

function frm_getwww($name)
{
	$val = frm_get($name);
	return htmlentities($val, ENT_QUOTES, get_lang(1));
}

function frm_empty($name)
{
	$val = frm_get($name);
	if (strlen($val) > 0) return false;
	return true;
}


$kdesign = array();

$kdesign['dirheader'] =
	'?>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td colspan="2" align="left">
			<font class="importnant"><b><?php echo $dirlink; ?>&nbsp;</b></font><?php echo $ximg; ?>
		</td>
	</tr>
	<tr><td height="7"></td></tr>
	<tr>
		<td width="80%" height="1" bgcolor="#CCCCCC"></td>
		<td width="20%"></td>
	</tr>
	<tr>
		<td height="8"></td>
	</tr>
	</table>';

$kdesign['detailedview'] = 
	'?><tr>
			<td>
				<table width="90%" cellspacing="2" cellpadding="0" border="0">
				<tr>
					<td height="10"></td>
				</tr>
				<tr>
					<td height="100" width="120"><?php echo $imgurl; ?></td>
					<td width="10"></td>
					<td class="ainfo" valign="top">
						<table width="100%" cellspacing="0" cellpadding="0" border="0">
						<tr>
							<td width="28">							
							<a href="<?php echo $href; ?>" <?php echo $onclick; ?> title="<?php echo get_lang(337); ?>" class="dir"><img alt="<?php echo get_lang(337); ?>\'" src="<?php echo getimagelink(\'play.gif\'); ?>" border="0"/></a></td>						
							<td><a href="<?php echo $dirurl; ?>" class="dir"><?php echo $cname; ?></a></td>
						</tr>
						</table>
						<table width="100%" cellspacing="0" cellpadding="0" border="0">
						<tr>
							<td width="3"></td>
							<td>
								<table width="100%" cellspacing="0" cellpadding="0" border="0">
								<tr>
									<td height="6"></td>
								</tr>
								<tr>
									<td width="80"><?php echo get_lang(142); ?></td><td><?php echo $ainf[\'artist\']; ?></td>
								</tr>						
								<tr>
									<td width="80"><?php echo get_lang(145); ?></td><td><?php if (is_numeric($ainf[\'year\']) && $ainf[\'year\'] != 0) echo $ainf[\'year\']; ?></td>
								</tr>
								<tr>
									<td width="80"><?php echo get_lang(147); ?></td><td><?php echo checkchs($ainf[\'genre\']); ?></td>
								</tr>
								<tr>
									<td width="80"><?php echo get_lang(336); ?></td><td><?php echo get_lang(151, $ainf[\'length\'], $ainf[\'titles\']); ?></td>
								</tr>
								</table>
							</td>
						</tr>
						</table>
					</td>
				</tr>
				</table>
			</td>
		</tr>';

$kdesign['login'] = '
?>
<form style="margin:0;padding:0" method="post" action="<?php if (HTTPS_REQ_MET) echo PHPSELF;?>">
<input type="hidden" name="l_uri" value="<?php echo geturi(); ?>"/>
<p>&nbsp;</p>
<table width="600" border="0" cellspacing="0" cellpadding="0" align="center">
	<tr>
	<td align="left"><a href="http://www.kplaylist.net/"><font class="loginkplaylist">www.kplaylist.net</font></a></td>
	</tr>
	<tr>
		<td height="5"></td>
	</tr>
	<tr>
		<td colspan="3"><img src="<?php echo getimagelink(\'login.jpg\'); ?>" height="327" width="600" alt="kPlaylist v<?php echo $app_ver; ?> build <?php echo $app_build; ?>"/></td>
	</tr>
	<tr>
		<td height="3" bgcolor="#AAAAAA"></td>
	</tr>
	<tr>
		<td height="12" width="600" valign="top">
			<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tdlogin">
				<tr>
					<td height="10"></td>
				</tr>
				<tr>
					<td width="2%"></td>
					<td width="20%"><font class="text"><?php echo get_lang(37); ?></font></td>
					<td width="30%"><input type="text" id="l_username" name="l_username" tabindex="1" maxlength="30" size="15" class="logonbuttom"/></td>
					<td width="48%"></td>
				</tr>
				<tr>
					<td height="3"></td>
				</tr>
				<tr>
					<td></td>
					<td><font class="text"><?php echo get_lang(38); ?></font></td>
					<td>
						<input type="password" name="l_password" tabindex="2" maxlength="30" size="15" class="logonbuttom"/>
					</td>
				</tr>
				<tr>
					<td height="3"></td>
				</tr>
				<tr>
					<td></td>
					<td><font class="text"><?php echo get_lang(287); ?></font></td>
					<td><input type="checkbox" name="l_rememberme" tabindex="4" value="1" class="logonbuttom"/></td>
				</tr>
				<tr>
					<td height="8"></td>
				</tr>
				<tr>
					<td></td>					
					<td colspan="3">
					<table width="100%" border="0" cellspacing="0" cellpadding="0">
					<tr>
						<td width="30%">
						<?php 
						if (HTTPS_REQ_MET)
						{
							?><input type="submit" name="l_submit" tabindex="3" value="<?php echo get_lang(40); ?>" class="logonbuttom" />
							<?php
							if (USERSIGNUP) 
							{ 
								?><input type="button" name="l_signup" tabindex="5" onclick="newwin(\'Users\', \'<?php echo PHPSELF; ?>?l_signup=1\', 195, 350);" value="<?php echo get_lang(158); ?>" class="logonbuttom" /><?php 
							}
						} else { ?><a href="https://<?php echo $phpenv[\'streamlocation\']; ?>"><font class="logintext"><?php echo get_lang(41); ?></font></a><?php }
						?>
						</td>
						<td valign="bottom" align="right"><font class="logintext"><?php echo get_lang(39); ?>&nbsp;&nbsp;</font></td>
					</tr>
					</table>
					</td>
				</tr>
				<?php if (!empty($msg))
				{
					?>
					<tr>
						<td height="10"></td>
					</tr>
					<tr>
						<td></td><td colspan="2"><font class="logintext"><?php echo $msg; ?></font></td>
					</tr>
					<?php
				}
				?>
				<tr>
					<td height="10"></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
</form>
<script type="text/javascript">
	<!--
	d = document.getElementById(\'l_username\');	
	d.focus();	
	-->
</script>
<table width="610" border="0" cellspacing="0" cellpadding="0" align="center">
<tr><td height="7"></td></tr>
<tr>
	<td align="right">
		<a href="http://validator.w3.org/check/referer">
		<img src="<?php echo getimagelink(\'w3c_xhtml_valid.gif\'); ?>" border="0" alt="Valid XHTML 1.0!" height="31" width="88"/></a>
	</td>
</tr>
</table>';

$kdesign['infobox'] = '	
	
	$trheight = 14;
	$boxwidth = 245;
	?>	
	<table width="100%" cellspacing="0" cellpadding="0" border="0">
	<tr>
		<td valign="top" align="left">
		<?php
		if ($setctl->get(\'showupgrade\')) 
		{
			?><a title="<?php echo get_lang(120); ?>" href="http://www.kplaylist.net/?ver=<?php echo $app_ver; ?>&amp;build=<?php echo $app_build; ?>" target="_blank">
			<font color="#CCCCCC"><?php echo get_lang(78); ?></font></a><br/><?php
		} 
		?>
		<a title="<?php echo get_lang(79); ?>" href="<?php echo $homepage; ?>" target="_blank"><img alt="<?php echo get_lang(79); ?>" src="<?php echo getimagelink(\'kplaylist.gif\'); ?>" border="0"/><span class="notice">v<?php echo $app_ver.\' \'.$app_build; ?></span></a>
		</td>
	</tr>
	<tr>
		<td height="6"></td>
	</tr>
	<tr>
		<td>
		<table width="100%" cellspacing="0" cellpadding="0" border="0">
		<tr>
			<td width="15"></td>
			<td>
			<table width="100%" cellspacing="0" cellpadding="0" border="0">
				<tr>
					<td>
						<form style="margin:0;padding:0" name="search" action="<?php echo PHPSELF; ?>" method="post">
						<input type="hidden" name="action" value="search"/>
						<table width="100%" border="0" cellspacing="0" cellpadding="0">
						<?php
						if ($setctl->get(\'showstatistics\'))
						{
							?>
							<tr><td height="4"></td></tr>
							<tr><td align="left"><font class="smalltext">&nbsp;<?php echo compute_statistics(); ?></font></td></tr>
							<tr><td height="8"></td></tr>
							<?php
						}
						?>		
						<tr>
							<td align="left"><input type="text" name="searchtext" id="searchtext" value=\'<?php echo frm_getwww(\'searchtext\'); ?>\' maxlength="150" size="38" class="fatbuttom"/></td>	
						</tr>
						<tr>
							<td height="5"></td>
						</tr>
						<tr>
							<td align="left">
								<input type="radio" name="searchwh" value="0" <?php if ($valuser->get(\'defaultsearch\')==\'0\') echo \'checked="checked"\';?>/><font class="notice"><?php echo get_lang(81); ?>&nbsp;</font>
								<input type="radio" name="searchwh" value="1" <?php if ($valuser->get(\'defaultsearch\')==\'1\') echo \'checked="checked"\';?>/><font class="notice"><?php echo get_lang(82); ?>&nbsp;</font>
								<input type="radio" name="searchwh" value="2" <?php if ($valuser->get(\'defaultsearch\')==\'2\') echo \'checked="checked"\';?>/><font class="notice"><?php echo get_lang(83); ?>&nbsp;</font>
								<input type="radio" name="searchwh" value="3" <?php if ($valuser->get(\'defaultsearch\')==\'3\') echo \'checked="checked"\';?>/><font class="notice"><?php echo get_lang(67); ?></font>
							</td>		
						</tr>
						<tr>
							<td height="5"></td>
						</tr>
						<tr>
							<td align="left">
								<input type="checkbox" name="onlyid3" value="1" <?php if ($valuser->get(\'defaultid3\')) echo \' checked="checked"\'; ?>/>
								<font class="notice"><?php echo get_lang(80); ?></font>
								<input type="checkbox" name="orsearch" value="1" <?php if ($valuser->get(\'orsearch\')) echo \' checked="checked"\'; ?>/>
								<font class="notice"><?php echo get_lang(306); ?></font>&nbsp;
								<select name="hitsas" class="fatbuttom">
								<option value="0"<?php if ($valuser->get(\'hitsas\') == 0) echo \' selected="selected"\'; ?>><?php echo get_lang(185); ?></option>
								<option value="1"<?php if ($valuser->get(\'hitsas\') == 1) echo \' selected="selected"\'; ?>><?php echo get_lang(186); ?></option>
								</select>								
							</td>		
						</tr>
						<tr>
							<td height="5"></td>
						</tr>
						<tr>
							<td align="left"><input type="submit" name="startsearch" value="<?php echo get_lang(5); ?>" class="fatbuttom"/></td>
						</tr>		
						<?php trspace($trheight); ?>
						<tr>
							<td align="left">
							<script type="text/javascript">
								<!--
								d = document.getElementById(\'searchtext\');
								d.focus();
								-->
							</script>
							<?php 
								$ha = new hotalbum();
								blackbox(get_lang(84), $ha->html(), 0, true, \'boxhotlist\', \'left\', $boxwidth); 
							?>
							</td>
						</tr>
						</table>
						</form>
						<table width="100%" border="0" cellspacing="0" cellpadding="0">
						<?php if (class_exists(\'kbulletin\') && BULLETIN)
						{
						trspace($trheight);
						?>						
						<tr>
							<td><?php 
									$kb = new kbulletin();
									blackbox(get_lang(268), $kb->getlatest(), 0, false, \'box\', \'left\', $boxwidth); ?>
								</td>
						</tr>
						<?php
						}		
						if (SHOUTBOX)
						{
							trspace($trheight);
							?>
							<tr>
								<td><?php 
										blackbox(get_lang(364), $kpshout->show(), 0, false, \'box\', \'left\', $boxwidth); ?>
									</td>
							</tr>

							<tr>
								<td height="5"></td>
							</tr>						
							<tr>
								<td>								
								&nbsp;<input type="text" id="shoutmessage" name="shoutmessage" value="" maxlength="128" size="30" class="fatbuttom"/>&nbsp;
								<input type="button" name="submitmessage" onclick="KPlaylist.Shout.submitMessage(document.getElementById(\'shoutmessage\'));" class="fatbuttom" value="<?php echo get_lang(365); ?>"/>													
								</td>
							</tr>
							
							<?php
							}	
											
						trspace($trheight);
						?>
						<tr>
							<td><?php 
									blackbox(get_lang(286), $ca->show(), 0, false, \'box\', \'left\', $boxwidth); ?>
								</td>
						</tr>
						</table>
					</td>
				</tr>
				<?php
	
				$plshared = pl_shared(75);
				if (!empty($plshared))
				{
					trspace($trheight);
					?>
					<tr>
					<td>
					<form style="margin:0;padding:0" name="sharedplaylist" action="<?php echo PHPSELF; ?>" method="post">
					<table width="100%" border="0" cellspacing="0" cellpadding="0">
					<tr><td><?php echo blackbox(get_lang(86), $plshared, 0, false, \'box\', \'left\', $boxwidth); ?></td></tr>
					</table>
					</form>
					</td>
					</tr>
					<?php 
				}
				?>

				<tr>
				<td>
				<form style="margin:0;padding:0" name="misc" action="<?php echo PHPSELF?>" method="post">
				<input type="hidden" name="action" value="misc"/>
				<table width="100%" border="0" cellspacing="0" cellpadding="0">
				<?php					
					if ($valuser->isadmin())
					{
						trspace($trheight);
						?>
						<tr>
							<td align="left">
						<?php
						$admincode = \'&nbsp;<input type="button" name="action" value="\'.get_lang(87).\'" class="fatbuttom" onclick="\'.jswinscroll(\'Users\', \'?action=showusers\',425,695).\'"/> \';			
						$admincode .= \'<input type="button" name="updatesearch" value="\'.get_lang(15).\'" class="fatbuttom" onclick="\'.jswinscroll(\'Update\', \'?action=updateoptions\').\'"/> \';
						$admincode .= \'<input type="button" name="settings" value="\'.get_lang(126).\'" class="fatbuttom" onclick="\'.jswin(\'Settings\',\'?action=settingsview\',460,785).\'"/>\';
						
						$dropadmin = \'<a class="bbox" onclick="javascript: if (!confirm(\'.addsq().get_lang(313).addsq().\')) return false;" href="\'.PHPSELF.\'?action=dropadmin&amp;p=\'.$runinit[\'pdir64\'].\'&amp;d=\'.$runinit[\'drive\'].\'">x</a>&nbsp;\';		
	
						echo blackbox(get_lang(88),$admincode, 0, false, \'box\', \'left\', $boxwidth, $dropadmin); ?>
						</td></tr>
					<?php 
					} 
					
					if ($valuser->isadmin() && $cfg[\'radio\'])
					{
						trspace($trheight);
						
						$kpr = new kpradio();
						
						$radiocode = $kpr->selectstations();
						if (strlen($radiocode) > 0)
									$radiocode .= \'&nbsp;<input type="button" name="editradio" onclick="\'.jswin(\'radioedite\', \'?action=radio_editjs\', 165, 475).\'" value="\'.get_lang(71).\'" class="fatbuttom"/>\';
	

							$radiocode .= \'&nbsp;<input type="button" name="newradio" onclick="\'.jswin(\'radioeditn\', \'?action=radio_new&amp;stationid=0\', 165, 475).\'" value="\'.get_lang(72).\'" class="fatbuttom"/>\';

						
						?>
							<tr><td><?php echo blackbox(get_lang(343), \'&nbsp;\'.$radiocode, 0, false, \'box\', \'left\', $boxwidth); ?></td></tr>
						<?php 
					}	


					$othercode = \'&nbsp;<input type="submit" name="whatsnew" value="\'.get_lang(89).\'" class="fatbuttom"/>&nbsp;\';
					$othercode .= \'<input type="submit" name="whatshot" value="\'.get_lang(90).\'" class="fatbuttom"/>&nbsp;\';

					$usermisc = \'&nbsp;<input type="submit" name="logmeout" value="\'.get_lang(91).\'" onclick="javascript: if (!confirm(\'.addsq().get_lang(210).addsq().\')) return false;" class="fatbuttom"/> \';
					if ($valuser->get(\'u_access\') != 2) $usermisc .= \'<input type="button" name="editoptions" value="\'.get_lang(92).\'" class="fatbuttom" \'. \'onclick="\'.jswin(\'Options\', \'?action=editoptions\',380,590).\'"/> \';
					$usermisc .= \'<input type="button" name="randomizer" value="\'.get_lang(212).\'" class="fatbuttom" \'. \'onclick="\'.jswin(\'Randomizer\', \'?action=showrandomizer\',380,550).\'"/>\';

					trspace($trheight);

					?>
					<tr><td><?php echo blackbox(get_lang(93), $othercode, 0, false, \'box\', \'left\', $boxwidth); ?></td></tr>

					<?php 
					
					trspace($trheight);

					$genres = \'&nbsp;\'.genre_select(true,$valuser->get(\'defgenre\'));
					$genres .= \'&nbsp;<input type="submit" class="fatbuttom" name="genrelist" value="\'.get_lang(154).\'"/>\';
					?>
					<tr><td><?php echo blackbox(get_lang(147), $genres,1, false, \'box\', \'left\', $boxwidth); ?></td></tr>

					<?php trspace($trheight); ?>
					<tr><td><?php echo blackbox(get_lang(94), $usermisc,1, false, \'box\', \'left\', $boxwidth); ?></td></tr>
				</table>
				</form>
				</td>
				</tr>
			</table>
		</td>
	</tr>
	</table>
	</td>
	</tr>
</table>';

$kdesign['endmp3table'] = '		
	
	$upload = \'<input type="button" name="upload" onclick="\'.jswin(\'upload\', \'?action=fupload\', 220, 520).\'" value="\'.get_lang(69).\'" class="fatbuttom"/>\';
	$httpq  = \'<input type="submit" onclick="javascript: if (!anyselected()) { alert(\'.addsq().get_lang(159).addsq().\'); return false; }" name="httpqselected" value="\'.get_lang(68).\'" class="fatbuttom"/>\';
	
	$selectallcode=\'<input type="button" value="+" class="fatbuttom" onclick="javascript: selectall();"/>&nbsp;&nbsp;<input type="button" value="-" class="fatbuttom" onclick="javascript: disselectall();"/>&nbsp;&nbsp;<input type="button" value="-+" class="fatbuttom" onclick="javascript: kptoggle();"/>\';
	
	?>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr><td height="8"></td></tr>
	<tr>
	<td>
		<table border="0" cellspacing="0" cellpadding="0">	
		<tr>
		<?php
		
		if ($files > 0 || $dirs > 0) echo \'<td align="left">\'.blackbox(get_lang(73), $selectallcode).\'</td><td width="5"></td>\';
		if (strlen($playbts) > 0) echo \'<td align="left"> \'.blackbox(get_lang(74), $playbts).\'</td><td width="5"></td>\';
		if ($archivedl && ($files > 0 || $dirs > 0)) echo \'<td align="left"> \'.blackbox(get_lang(117), $dlbts).\'</td><td width="5"></td>\';

		echo \'<td align="left">\'.blackbox(get_lang(75), $playlistbts).\'</td><td width="5"></td>\';
		if (ENABLEUPLOAD) echo \'<td align="left">\'.blackbox(get_lang(234), $upload).\'</td>\';
	
		if ($cfg[\'httpq_support\'] && ($files > 0 || $dirs > 0)) echo \'<td width="5"></td><td align="left">\'.blackbox(get_lang(332), $httpq).\'</td>\';

		?>
		</tr>
	</table>
	</td></tr></table>';

$kdesign['top'] = '
				?>
				<table width="100%" border="0" align="left" cellspacing="0" cellpadding="0">
				<tr>
					<td width="320" valign="top">
					<?php infobox(); ?></td>
					<td align="left" valign="top">
						<?php if ($this->form) $this->form(); ?>
						<table width="100%" border="0" cellpadding="0" cellspacing="0">
						<tr><td height="5"></td></tr>
						<tr>
						<td>						
				';

$kdesign['bottom'] = '
				echo \'</td></tr></table>\';
				if ($this->form) echo \'</form>\';
				echo \'</td></tr></table>\';
				';


$kdesign['blackbox'] = '
	$mix = \'<table class="\'.$class.\'" border="0" cellspacing="0" cellpadding="0"\';
	if ($width != 0) $mix .= \' width="\'.$width.\'"\';
	$mix .= 
	\'><tr><td height="13" valign="top" class="bbox"><b>&nbsp;\'.$title.\'&nbsp;</b></td><td class="bbox" align="right">\'.$extra.\'</td></tr><tr><td colspan="2" class="notice">\'.
	\'<table class="bboxtable" border="0" cellspacing="0" cellpadding="0" width="100%">\';
	$mix .= \'<tr><td height="6"></td></tr>\'.
	\'<tr><td width="3"></td><td \';
	if ($nowrap) $mix .= \'nowrap="nowrap" \';
	$mix .= \'>\'.$code.\'</td><td width="3"></td></tr><tr><td height="4"></td></tr></table>\'.
	\'</td></tr></table>\';
	if (!$returncode) echo $mix; else return($mix);
	';


$kdesign['welcome'] = 
	'?>	
		<table width="90%" bgcolor="#BBCCCC" cellpadding="8" cellspacing="8" border="1">
			<tr>
				<td class="importnant"><h3>Welcome to kPlaylist!</h3>
				To get your site quickly up:
				
				<br/><br/>Click Settings on the admin menu, choose \'File handling\' and enter the path to your music directory or directories in the \'base directory\' field. You can also click the <a class="importnantlink" href="#" onclick="javascript: newwinscroll(\'find\', \'<?php echo PHPSELF; ?>?action=findmusic\', 450, 600);">find</a> button to automatically detect music directories. Press F5 when finished.<br/><br/>
				
				If you have problems configuring kPlaylist, click <a class="importnantlink" href="http://kplaylist.net/index.php?install=true" target="_blank">here</a> for the kPlaylist installation manual.
				<br/><br/>
				</td>
			</tr>
			</table>
			';

$kdesign['basedirchange'] = 
	'?><table width="90%" bgcolor="#BBCCCC" cellpadding="8" cellspacing="8" border="1">
			<tr>
				<td class="importnant"><h3>Base directory changed</h3>
				The base dir setting was changed. Please click the \'Update\' button on the admin menu to perform
				an update against the music sources. 
				<br/><br/>
				Reload this page when done. (F5)
				<br/><br/>
				</td>
			</tr>
			</table>
			';

$kdesign['needupdate'] = 
	'?><table width="90%" bgcolor="#BBCCCC" cellpadding="8" cellspacing="8" border="1">
			<tr>
				<td class="importnant"><h3>Needs update</h3>
				Due to changes in the search database, you need to run a complete update. Click "Update" on the admin bar and select "Rebuild ID3".
				<br/><br/>
				Reload this page when done. (F5)
				<br/><br/>
				</td>
			</tr>
			</table>
			';


$kdesign['missing_getid3'] = '<font color="red">You don\'t have the latest supported version of getid3. Running kPlaylist without getid3 or an old version of getid3 is not recommended. Please click 
<a class="importnantlink" href="http://www.kplaylist.net/forum/viewtopic.php?t=1003" target="_blank">here</a> for more information.</font>';


function klogon($msg = '')
{
	kprintheader(get_lang(29)); 
	kprintlogin($msg);
	kprintend(); 
	die();
}

function errormessage($msg, $back = true)
{
	kprintheader(get_lang(56));
	if ($back) $code = '&nbsp;<a href="javascript:history.go(-1)" class="fatbuttom">&nbsp;'.get_lang(34).'&nbsp;</a>'; else $code = '';
	blackbox(get_lang(56),'<br/>'.$msg.'<br/><br/>'.$code.'<br/><br/>',0);
	kprintend();
	die();
}

function okmessage($msg, $window=false)
{
	kprintheader(get_lang(181));
	if ($window) $extra = '<a href="javascript: window.close(); window.opener.location.reload();" class="fatbuttom">&nbsp;'.get_lang(27).'&nbsp;</a><br/>'; else $extra = '';
	blackbox(get_lang(181),'<br/>'.$msg.'<br/><br/>'.$extra,0);
	kprintend();
	die();
}

function kprintlogin($msg = '')
{
	global $app_ver, $app_build, $phpenv;

	if (((REQUIRE_HTTPS) && ($phpenv['https'])) || (!REQUIRE_HTTPS)) define('HTTPS_REQ_MET', true); else define('HTTPS_REQ_MET', false);

	eval(gethtml('login'));	
}

class kpdesign
{
	function form()
	{
		global $runinit;
		?>
		<form style="margin:0;padding:0" name="psongs" action="<?php echo PHPSELF?>" method="post">
		<input type="hidden" name="action" value="listedres"/>
		<input type="hidden" name="previous" value="<?php echo $runinit['pdir64']; ?>"/>
		<?php
	}

	function top($form=true, $title='', $js=true, $ajax=true)
	{		
		if ($form) $this->form = true; else $this->form = false;
		kprintheader($title, $ajax);
		eval(gethtml('top'));		
	}

	function bottom()
	{
		eval(gethtml('bottom'));
		kprintend();
	}
}

function updatestatistics()
{
	global $cfg, $streamtypes, $bd;

	$ids = array();
	$all = false;
	foreach($cfg['stat_count_ftype'] as $tag)
	{
		if (strlen($tag) > 1)
		{
			$fid = substr($tag, 1);
			if (!is_numeric($fid)) continue;
		}

		switch($tag[0])
		{
			case '*':
					foreach($streamtypes as $id => $val) $ids[$id] = true;
					$all = true;
					break;

			case '-':
					$ids[$fid] = false;
					$all = false;
					break;
			
			case '+':
					$ids[$fid] = true;
					break;
		}
	}

	$sql = 'SELECT SUM(lengths) AS ls, COUNT(*) AS nr, SUM(fsize) AS fs FROM '.TBL_SEARCH;

	$ok = false;

	if (is_array($ids) && !$all)
	{
		foreach($ids as $id => $val) if ($val) $ok = true;
		if ($ok)
		{
			$sql .= ' WHERE (ftypeid ';
			foreach($ids as $id => $val) if ($val) $sql .= ' = '.$id.' or ftypeid';
			$sql = substr($sql, 0, strlen($sql) - (strlen('ftypeid') + 4)).')';			
		}
	}

	$xsql = $bd->genxdrive();
	if (strlen($xsql) > 0)
	{
		if ($ok) $sql .= $xsql; else $sql .= $bd->genxdrive('drive', 'WHERE');	
	}

	$row = db_fetch_assoc(db_execquery($sql), true);
	if ($row)
	{
		$data = $row['ls'].':'.$row['nr'].':'.$row['fs'];
		updatecache(30, $data);
		return $data;
	}
}

function compute_statistics()
{
	global $cfg;
	$data = '';
	if (!getcache(30, $data) || $cfg['userhomedir']) $data = updatestatistics();

	$stats = explode(':', $data);
	if (count($stats) == 3 && is_numeric($stats[0]) && is_numeric($stats[1]) && is_numeric($stats[2]))
	{
		$secs = (int)$stats[0];
		$days = floor($secs/86400);
		$secs = $secs % 86400;
		$hours = floor($secs/3600);
		$secs = $secs % 3600;
		$min = floor($secs/60);
		$mb = floor($stats[2] / 1048576);
		return get_lang(155,$days, $hours, $min, $stats[1], $mb);
	}
}

function endmp3table($showalbum=1, $dirs=0, $files=0) 
{
	global $u_id, $runinit, $cfg, $valuser;

	$dlbts = $playbts = '';

	if ($files == 1 && $dirs == 0) $idnt = get_lang(65); else
	if ($files > 0 && $dirs == 0) $idnt = get_lang(66); else
	if ($files > 0 && $dirs > 0) $idnt = get_lang(67); else $idnt = '';

	$urlprep = '&amp;p='.$runinit['pdir64'].'&amp;d='.$runinit['drive'];

	if (WINDOWPLAYER) $kpwjs = new kpwinjs();

	if ($showalbum && $files > 0)
	{
		if (WINDOWPLAYER)
		{
			$playbts = '<input type="button" name="playwin" value="'.$idnt.'" onclick="'.$kpwjs->album($runinit['pdir64'], $runinit['drive']).'" class="fatbuttom"/>';
		} else $playbts = '<input type="hidden" name="drive" value="'.$runinit['drive'].'"/><input type="submit" name="psongsall" value="'.$idnt.'" class="fatbuttom"/>';
		$playbts .= '&nbsp;&nbsp;';
		$dlbts = '<input type="button" name="pdlall" value="'.$idnt.'" onclick="'.jswin('dlall', '?action=dlall'.$urlprep, 130, 450).'" class="fatbuttom"/>&nbsp;&nbsp;';
	} 
	
	if ($files > 0 || $dirs > 0)
	{
		if (WINDOWPLAYER)
		{
			$playbts .= '<input type="button" class="fatbuttom" name="playwin" value="'.get_lang(68).'" onclick="javascript: if (!anyselected()) { alert('.addsq().get_lang(159).addsq().'); return false; } else { '.$kpwjs->selected().' }"/>';
		} else
		$playbts .= '<input type="submit" onclick="javascript: if (!anyselected()) { alert('.addsq().get_lang(159).addsq().'); return false; }" name="psongsselected" value="'.get_lang(68).'" class="fatbuttom"/>';
	}

	$dlbts .= '<input type="button" onclick="javascript: if (!anyselected()) alert('.addsq().get_lang(159).addsq().'); else '.jswin('dlselected', '?action=dlselectedjs', 130, 450, false).'" name="pdlselected" value="'.get_lang(68).'" class="fatbuttom"/>';

	$playlists = db_getplaylist($u_id);
	$playlistbts = '<input type="hidden" name="drive" value="'.$runinit['drive'].'"/>';
	if (count($playlists) > 0)
	{
		if ($files > 0 || $dirs > 0) 
		{
			if (AJAX) $playlistbts = '<input type="button" onclick="javascript: if (!anyselected()) { alert('.addsq().get_lang(32).addsq().'); return false; } else addPlaylistSelected(\''.get_lang(33).'\');"';
				else $playlistbts = '<input type="submit" onclick="javascript: if (!anyselected()) { alert('.addsq().get_lang(32).addsq().'); return false; }"';		
			$playlistbts .= ' name="addplaylist" value="'.get_lang(69).'" class="fatbuttom"/>&nbsp;';
		}
		
		$playlistbts .= '<select name="sel_playlist" id="sel_playlist" class="file">';
		
		for ($c=0,$cnt=count($playlists);$c<$cnt;$c++) 
		{		
			$playlistbts .= '<option value="'.$playlists[$c][1].'"';
			if ($playlists[$c][1] == db_guinfo('defplaylist')) $playlistbts .= ' selected="selected"';
			$playlistbts .= '>'.$playlists[$c][0].'</option>';			
		}
		$playlistbts .= '</select>&nbsp;';
		
		if (WINDOWPLAYER)
		{
			$playlistbts .= '<input type="button" value="'.get_lang(70).'" class="fatbuttom" onclick="javascript: '.$kpwjs->userplaylist().'"/>';
		} else $playlistbts .= '<input type="submit" name="playplaylist" value="'.get_lang(70).'" class="fatbuttom"/>';		
		$playlistbts .= '&nbsp;<input type="submit" name="editplaylist" value="'.get_lang(71).'" class="fatbuttom"/>&nbsp;';
	}
	$playlistbts .= '<input type="button" name="newplaylist" onclick="'.jswin('playlist', '?action=playlist_new', 100, 350).'" value="'.get_lang(72).'" class="fatbuttom"/>';

	if (ALLOWDOWNLOAD && $valuser->get('u_allowdownload') && $cfg['archivemode'] && $valuser->get('allowarchive')) $archivedl = true; else $archivedl = false;

	eval(gethtml('endmp3table'));	
}

function infobox()
{
 	global $cfg, $app_ver, $setctl, $app_build, $homepage, $runinit, $valuser;
	$homepage = str_replace('KBUILD', $app_build, str_replace('KVER', $app_ver, $setctl->get('homepage')));
	$ca = new caction();
	$ca->updatelist();
	$kpshout = new kpshoutmessage();
	eval(gethtml('infobox'));
}

function kprintheader($title='', $ajax=0, $addonload='')
{
	global $klang, $setctl, $app_build, $phpenv, $cfg;
	
	if (strlen($title) == 0) $title = '| kPlaylist'; else $title = '| '.$title;	

	if ($setctl->get('includeheaders')) 
	{
	?>
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml">
		<head>
		<title><?php echo $title; ?></title>
		<!-- kp build <?php echo $app_build; ?> -->
		<meta http-equiv="Content-Type" content="text/html; charset=<?php echo get_lang(1); ?>"/>
		<?php if ($setctl->get('publicrssfeed')) 
		{
			?>
			<link rel="alternate" title="kPlaylist RSS Feed" href="<?php echo $setctl->get('streamurl').$phpenv['streamlocation'].'?streamrss'; ?>" type="application/rss+xml"/>
			<link rel="alternate" title="kPlaylist Whats New RSS Feed" href="<?php echo $setctl->get('streamurl').$phpenv['streamlocation'].'?whatsnewrss'; ?>" type="application/rss+xml"/> 
			<?php
		}
		kprintcss();
	}
	$extjs = $setctl->get('externaljavascript');

	if (AJAX)
	{
		echo '<script type="text/javascript" src="'.$setctl->get('ajaxurl').'"></script>';
		ajax($cfg['livestreamajax'], SHOUTBOX);
	}

	if (strlen($extjs) == 0) jsfunctions(); else echo '<script type="text/javascript" src="'.$extjs.'"></script>';

	if ($setctl->get('includeheaders', 1, 1))
	{
		echo '</head>';

		$onload = '';
	
		if ($ajax && AJAX) $onload = 'KPlaylist.init(); ';

		if (strlen($addonload) > 0) $onload .= $addonload;

		if (strlen($onload) > 0) echo '<body onload="'.$onload.'">'; else echo '<body>';
	}
}

function ajax($stream, $shoutbox)
{
	global $cfg;

	?>
	<script type="text/javascript">
	<!--
	KPlaylist = 
	{
		init : function()
		{
			<?php if ($stream)
			{
			?>
			setInterval('KPlaylist.CAction.showStream()', <?php echo $cfg['livestreamajaxupdatetime']; ?>);
			<?php
			}
			?>

			<?php if ($shoutbox)
			{
			?>
			setInterval('KPlaylist.MessageAction.showMessage()', <?php echo $cfg['shoutboxupdatetime']; ?>);
			<?php
			}
			?>
		}
	}

	KPlaylist.Shout =
	{
		submitMessage :  function(object)
		{
			new Ajax.Request('<?php echo PHPSELF; ?>', {method:'post',parameters:'action=sendshout&shoutmessage=' + encodeURIComponent(object.value),onSuccess:function(request) { KPlaylist.MessageAction.showMessage(); object.value = ''; object.focus(); }});		
		}
	}

	function submitSelected()
	{
		var selids = '';
		for(var i=0;i<document.psongs.elements.length;i++) 
			if(document.psongs.elements[i].type == "checkbox") 
				if (document.psongs.elements[i].checked == true) selids = selids + document.psongs.elements[i].value + ';';			
		new Ajax.Request('<?php echo PHPSELF; ?>', {method:'post',parameters:'action=addtemplist&selids=' + encodeURIComponent(selids),asynchronous: false,onSuccess:function(request) { } } );
	}

	function addPlaylistSelected(message)
	{
		var selids = '';
		plsel = document.getElementById('sel_playlist');
		for(var i=0;i<document.psongs.elements.length;i++) 
			if(document.psongs.elements[i].type == "checkbox") 
				if (document.psongs.elements[i].checked == true) selids = selids + document.psongs.elements[i].value + ';';			
		new Ajax.Request('<?php echo PHPSELF; ?>', {method:'post',parameters:'action=addplaylistajax&selids=' + encodeURIComponent(selids) + '&plid=' + plsel.value,asynchronous: false,onSuccess:function(request) { alert(message); } } );
	}

	function submitSelectedRandomizer()
	{
		var selids = '';
		var selobj = document.getElementById('selids'); 			
		for (i=0; i<selobj.options.length;i++) if (selobj.options[i].selected) selids = selids + selobj.options[i].value + ';';			
		new Ajax.Request('<?php echo PHPSELF; ?>', {method:'post',parameters:'action=addtemplist&selids=' + encodeURIComponent(selids),asynchronous: false,onSuccess:function(request) {  }});
	}

	function submitSingle(sid)
	{
		new Ajax.Request('<?php echo PHPSELF; ?>', {method:'post',parameters:'action=addtemplist&selids=' + encodeURIComponent(sid),asynchronous: false,onSuccess:function(request) {  }});
	}

	function submitPlaylist(id)
	{
		new Ajax.Request('<?php echo PHPSELF; ?>', {method:'post',parameters:'action=playlistaddtemplist&id=' + encodeURIComponent(id),asynchronous: false,onSuccess:function(request) {  }});
	}
	
	function submitSharedPlaylist()
	{
		d = document.getElementById('sel_shplaylist'); 
		if (d) new Ajax.Request('<?php echo PHPSELF; ?>', {method:'post',parameters:'action=playlistaddtemplist&id=' + encodeURIComponent(d.value),asynchronous: false,onSuccess:function(request) {  }});
	}

	function submitUserPlaylist()
	{
		d = document.getElementById('sel_playlist');
		if (d) new Ajax.Request('<?php echo PHPSELF; ?>', {method:'post',parameters:'action=playlistaddtemplist&id=' + encodeURIComponent(d.value),asynchronous: false,onSuccess:function(request) {  }});
	}

	function submitAlbum(pdir, drive)
	{
		new Ajax.Request('<?php echo PHPSELF; ?>', {method:'post',parameters:'action=diraddtemplist&p=' + pdir + '&d=' + drive,asynchronous: false,onSuccess:function(request) {  }});
	}

	KPlaylist.CAction = 
	{
		showStream : function()
		{
			new Ajax.Request('<?php echo PHPSELF; ?>', {method:'post',parameters:'action=ajaxstreams', onSuccess:function(request) { $('streams').innerHTML = request.responseText; }});		
		}
	}

	KPlaylist.MessageAction = 
	{
		showMessage : function()
		{
			new Ajax.Request('<?php echo PHPSELF; ?>', {method:'post',parameters:'action=ajaxshoutmessages',onSuccess:function(request) { $('messages').innerHTML = request.responseText; }});		
		}
	}
	//-->
	</script>
	<?php
}

function kprintcss()
{
	global $setctl, $kpt;
	if ($setctl->get('includeheaders'))
	{
		if ($link = $kpt->getfile('kplaylist.css'))
		{
			if (strlen($link) > 0) 
			{
				echo '<link href="'.$link.'" rel="stylesheet" type="text/css"/>';
				return true;
			}
		}

		$css = $setctl->get('externalcss'); 
		if (strlen($css) > 0)
		{
			echo '<link href="'.$css.'" rel="stylesheet" type="text/css"/>';
		} else
		{
			if (function_exists('kpdefcss'))
			{
				?>
				<style type="text/css">
				<?php kpdefcss(); ?>
				</style>
				<?php
			}
		}

		$css = $setctl->get('mobilecss');
		if (strlen($css) > 0)
		{
			echo "<link rel='stylesheet' media='only screen and (max-width: 768px)' href='$css' type='text/css' />";

			echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0,user-scalable=0" />';
		}
	}
}

function kprintend()
{
	global $setctl;
	if ($setctl->get('includeheaders', 1, 1)) echo '</body></html>';
}

function blackbox($title,$code,$returncode=1,$nowrap=true,$class='box',$textalign='left',$width=0, $extra='')
{
	return eval(gethtml('blackbox'));
}

function blackboxpart($title, $pos, $extra='')
{
	$data = blackbox($title, '%code', 1, true, 'box', 'left', 0, $extra);
	$p = strpos($data, '%code');
	if ($p !== false)
	{
		if ($pos == 1) return substr($data, 0, $p);
			else return substr($data, $p+5);
	}
}

function jswin($name, $url, $height=320, $width=675, $withj=true, $func='newwin', $urlprep='P')
{
	if ($urlprep == 'P') $urlprep = PHPSELF; else $urlprep = '';
	if ($withj) $js = "javascript: ".$func."('".$name."', '".$urlprep.$url."', ".$height.", ".$width.");";
		else $js = $func."('".$name."', '".$urlprep.$url."', ".$height.", ".$width.");";
	return $js;
}

function jswinscroll($name, $url, $height=320, $width=675, $withj=true, $func='newwinscroll', $urlprep='P')
{
	return jswin($name, $url, $height, $width, $withj, $func, $urlprep);
}

function jsfunctions()
{
	?>
	<script type="text/javascript">
	<!--
		
	function openwin(name, url) 
	{
		popupWin = window.open(url, name, 'resizable=yes,scrollbars=yes,status=no,toolbar=no,menubar=no,width=675,height=320,left=150,top=270');
		if (popupWin) popupWin.focus();
	}
	
	var jwflvwin = null;

	function openJWFLV(theFile, url, height, width)
	{
		jwflvwin = open("", "jwflvwin",  'width='+width+',height='+height);
		if (!jwflvwin || jwflvwin.closed || !jwflvwin.createPlayer) 
		{
			jwflvwin = window.open(url, "jwflvwin", 'width='+width+',height='+height);
		} else jwflvwin.focus();
		
		jwflvwin.loadXMLDoc(theFile);
	}

	function newwinscroll(name, url, height, width)
	{
		popupWin = window.open(url, name, 'resizable=yes,scrollbars=yes,status=no,toolbar=no,menubar=no,width='+width+',height='+height+',left=250,top=270');
		if (popupWin) popupWin.focus();
	}
	
	function newwin(name, url, height, width) 
	{
		popupWin = window.open(url, name, 'resizable=yes,scrollbars=no,status=no,toolbar=no,menubar=no,width='+width+',height='+height+',left=250,top=270');
		if (popupWin) popupWin.focus();
	}

	function flashwin(name, url, height, width)
	{
		flashpop = window.open(url, name, 'resizable=no,scrollbars=no,status=no,toolbar=no,menubar=no,width='+width+',height='+height+',left=250,top=270');		
		if (flashpop) flashpop.focus();
	}

	function flashwinsharedplaylist(name, url, height, width)
	{
		d = document.getElementById('sel_shplaylist'); 
		if (d) url = url + "&plid=" + d.value;
		flashpop = window.open(url, name, 'resizable=no,scrollbars=no,status=no,toolbar=no,menubar=no,width='+width+',height='+height+',left=250,top=270');		
		if (flashpop) flashpop.focus();
	}

	function flashwinuserplaylist(name, url, height, width)
	{
		d = document.getElementById('sel_playlist');
		if (d) url = url + "&plid=" + d.value;
		flashpop = window.open(url, name, 'resizable=no,scrollbars=no,status=no,toolbar=no,menubar=no,width='+width+',height='+height+',left=250,top=270');		
		if (flashpop) flashpop.focus();
	}

	function savescrolly()
	{
		var scrOfY = 0;
		if( typeof( window.pageYOffset ) == 'number' )
		{
			scrOfY = window.pageYOffset;
		} else if( document.body && ( document.body.scrollLeft || document.body.scrollTop))
		{
			scrOfY = document.body.scrollTop;
		} else if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop))
		{
			scrOfY = document.documentElement.scrollTop;
		}
		
		sy = document.getElementById('scrolly');
		if (sy) sy.value = scrOfY; 
	}

	function kptoggle() 
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

	function selectall() 
	{
		for(var i=0;i<document.psongs.elements.length;i++) 
			if(document.psongs.elements[i].type == "checkbox") if (document.psongs.elements[i].checked == false) document.psongs.elements[i].checked = true; 
	}	

	function disselectall() 
	{
		for(var i=0;i<document.psongs.elements.length;i++) 
			if(document.psongs.elements[i].type == "checkbox") if (document.psongs.elements[i].checked == true) document.psongs.elements[i].checked = false; 
	}	

	function anyselected()
	{
		for(var i=0;i<document.psongs.elements.length;i++) if(document.psongs.elements[i].type == "checkbox") if (document.psongs.elements[i].checked == true) return true;
		return false;
	}

	function chopener(loc, par)
	{
		var ret = "";
		var url = String(loc);
		var i = url.indexOf('?', 0);
		if (i != -1)
		{
			ret = url.substring(0, i) + par;			
		} else ret = url + par;

		window.opener.location = ret;
	}

	function chhttp(where) { 
		document.location = where;
	}
	//-->
	</script>
	<?php
}


class kpwinjs
{
	function kpwinjs()
	{
		global $valuser, $setctl, $u_id, $u_cookieid, $cfg, $phpenv;

		$this->pltype = $valuser->get('pltype');
				
		switch($this->pltype) // init
		{		
			case 7:
			case 8:
			case 9:
				$this->width = 	$cfg['jw_window_x'];
				$this->height = $cfg['jw_window_y'];

				$this->playlist = PHPSELF.'?templist='.$u_id.'&amp;encode=true&amp;c='.$u_cookieid.'&amp;file='.lzero(getrand(1,999999),6).'.xml';
				$this->openfw = 'openJWFLV(\''.$this->playlist.'\', \'?action=loadjw\', \''.$this->height.'\', \''.$this->width.'\');';

				break;

			default: // xspf
				$this->width =  $cfg['window_x'];
				$this->height = $cfg['window_y'];
				break;
		}
	}

	function clickwinjs($action, $par, $window)
	{
		global $cfg;
		return jswin('playwin', '?action='.$action.$par, $this->height, $this->width, false, $window);
	}

	function album($p, $d)
	{
		switch($this->pltype)
		{					
			default:	return $this->clickwinjs('playwin', '&amp;p='.$p.'&amp;d='.$d, 'flashwin');
			case 8:		return 'submitAlbum(\''.$p.'\', \''.$d.'\'); '.$this->openfw;
		}
	}

	function single($sid)
	{
		switch($this->pltype)
		{					
			default:	return $this->clickwinjs('playwinfile', '&amp;id='.$sid, 'flashwin');
			case 8:		return 'submitSingle(\''.$sid.'\'); '.$this->openfw;
		}
	}

	function randomizer()
	{
		switch($this->pltype)
		{					
			default:	return $this->clickwinjs('randomizerselected', '', 'flashwin');
			case 8:		return 'submitSelectedRandomizer(); '.$this->openfw;
		}
	}
	
	function sharedplaylist()
	{
		switch($this->pltype)
		{					
			default:	return $this->clickwinjs('playwinlist', '', 'flashwinsharedplaylist');
			case 8:		return 'submitSharedPlaylist(); '.$this->openfw;
		}
	}

	function userplaylist()
	{
		switch($this->pltype)
		{					
			default:	return $this->clickwinjs('playwinlist', '', 'flashwinuserplaylist');
			case 8:		return 'submitUserPlaylist(); '.$this->openfw;
		}
	}

	function playlist($id)
	{
		switch($this->pltype)
		{					
			default:	return $this->clickwinjs('playwinlist', '&amp;plid='.$id, 'flashwin');
			case 8:		return 'submitPlaylist(\''.$id.'\'); '.$this->openfw;
		}
	}

	function selected()
	{
		switch($this->pltype)
		{
			default: return $this->clickwinjs('playselectedjs', '', 'flashwin');
			case 8: return 'submitSelected(); '.$this->openfw;
		}
	}

}



$klang[0] = array('English', 'ISO-8859-1', 'English', 'What\'s hot', 'What\'s new', 'Search', '(only %1 shown)', 'sec', 'Search results: \'%1\'', 'found', 'None.', 'update search database options', 'Delete unused records?', 'Rebuild ID3?', 'Debug mode?', 'Update', 'Cancel', 'update search database', 'Found %1 files.', 'Could not determine this file: %1, skipped.', 'Installed: %1 - Update: %2, scan: ', 'Scan: ', 'Failed - query: %1', 'Could not read this file: %1. Skipped.', 'Removed link to: %1', 'Inserted %1, updated %2, deleted %3 where %4 failed and %5 skipped through %6 files - %7 sec - %8 marked for deletion.', 'Done.', 'Close', 'Found no files here: "%1"', 'kPlaylist logon', 'Album list for artist: %1', 'Hotselect %1', 'No tunes selected. Playlist not updated.', 'Playlist updated!', 'Back', 'Playlist added!', 'Remember to reload page.', 'login:', 'secret:', 'Notice! This is a non public website. All actions are logged.', 'Login', 'SSL required for logon.', 'Play', 'Delete', 'Shared access: ', 'Save', 'Control playlist: \'%1\' - %2 titles', 'Editor', 'Viewer', 'Select', 'Seq', 'Status', 'Info', 'Del', 'Name', 'Totals:', 'Error', 'Action on selected: ', 'Sequence:', 'edit playlist', 'Delete this entry', 'add playlist', 'Name:', 'Create', 'Play: ', 'File', 'Album', 'All', 'Selected', 'add', 'play', 'edit', 'new', 'Select:', 'Play Control: ', 'Playlist: ', 'Hotselect numeric', 'FirstIT gives you:', '(check for upgrade)', 'Homesite', 'only id3', 'album', 'title', 'artist', 'Hotselect album from artist', 'view', 'Shared playlists', 'Users', 'Admin control', 'What\'s new', 'What\'s hot', 'Logout', 'Options', 'Check', 'My', 'edit user', 'new user', 'Full name', 'Login', 'Change password?', 'Password', 'Comment', 'Access level', 'On', 'Off', 'Delete user', 'Logout user', 'Refresh', 'New user', 'del', 'logout', 'Use EXTM3U feature?', 'Show how many rows (hot/new)', 'Max number of search results', 'Reset', 'Open directory', 'Go to directory: %1', 'Download', 'Go one step up', 'Go to root directory.', 'Check for upgrade', 'users', 'Language', 'options', 'Booted', 'Shuffle:', 'Settings', 'Base directory', 'Stream location', 'Default language', 'A Windows system', 'Require HTTPS', 'Allow seek', 'Allow download', 'Session timeout (sec)', 'Report failed login attempts', 'Hold on - fetching file list', 'Playlist could not be added!', 'Admin', 'Login with HTTPS to change!', 'Enable streaming engine', 'Title', 'Artist', 'Album', 'Comment', 'Year', 'Track', 'Genre', 'not set', 'Max download rate (kbps)', 'User', '%1 mins - %2 titles', '%1 kbit %2 mins', 'Genre list: %1', 'Go', 'Playtime: %1d %2h %3m : %4 files : %5 mb', 'No relevant resources here.', 'Password changed!', 'Signup', 'Please make a selection!', 'What is update?', 'Click here for help', 'Use external images?', 'External images path', 'Current password', 'Current password does not match!', 'Preferred archiver', 'Could not create archive!', 'Possible duplicate found:  "%1" "%2"', 'Really delete playlist?', 'Alphabetical', 'Random', 'Sort', 'Original', 'Use javascript', 'Are you sure you want to delete this user?', 'View history', 'history', 'Rows', 'External CSS file', 'Remove duplicates', 'OK', 'ERR', 'Stream', '(show as)', 'files', 'albums', '%1d %2h %3m %4s', 'General', 'Customize', 'File handling', 'Click on ? for help.', 'Automatic database sync', 'Send file extension', 'Allow unauthorized streams', 'Include headers', 'External javascript', 'Homepage', 'Show First IT gives you part', 'Show upgrade part', 'Show statistics', 'Write ID3v2 with stream', 'Enable user signup', 'File types', 'Yes', 'No', 'Extension', 'MIME', 'Include in M3U', 'edit file type', 'Sure?', 'Optimistic filecheck', 'Randomizer', 'Mode', 'Playlist', 'None, directly', 'My favourites', 'Did not find any hits', 'All-time hits', 'Order', 'Enable LAME support?', 'Disabled', 'Allow LAME usage?', 'Email', 'Allow to mail files?', 'SMTP server', 'SMTP port', 'Mail to', 'Message', 'Send', 'Mail sent!', 'Activate upload', 'Upload directory', 'Activate mp3mail', 'Upload', 'File uploaded!', 'File could not be uploaded!', 'You must enable cookies to log in!', 'Period', 'ever', 'this week', 'this month', 'last month', 'hits', 'LAME command', 'Show album cover', 'Album files', 'Resize album images', 'Album height', 'Album width', 'Mail method', 'Direct', 'Pear', 'Wait!', 'Please enter a valid e-mail in options!', 'Playlists inline?', 'Show album from URL?', 'Album URL', 'Could not send!', 'User added!', 'Archive creator', 'Archive is deleted.', 'User updated!','Music match', '%1 entries filtered','Log access','Viewable', 'Archived','Bulletin','Written %1 by %2','more', 'Publish','%1 mb', '%1 kb', '%1 bytes', 'Recursive', 'Previous', 'Next', 'Goto page %1', 'Page: ', 'Never played', 'Manually approve signups', 'Pending', 'activate', 'All fields marked with * are mandatory', 'Your account will be inspected and activated manually.', 'Last streams', 'remember me', 'Style', 'find', 'Enter paths to search', 'Use selected?', 'Track time min/max', 'Minutes', 'm3u', 'asx (WMA)', 'If update stops, click here: %1', 'Follow symlinks?', 'File presentation template', 'Enable URL security', 'Upload whitelist', 'File type not allowed.', 'Playlist is empty!', 'Lyrics', 'Lyrics URL', 'Show lyrics link?', '(or?)', 'Unknown username or password', 'Max upload size: %1', 'Open public RSS feed?', 'Please set a password!', 'Need a name and login', 'Username already in use!', 'Drop admin access for this session?', 'Fetching database records: %1/%2', 'Could not find  "%1", is file deleted?', 'From/to date (DDMMYY)', 'Error in input field(s), please try again.', 'Maximum text length', 'Dir columns', 'New template', 'Template', 'Template name', 'Need a template name!', 'Default signup template', 'Tag extractor: ', 'Allow using archiver(s)', 'Maximum archive size (mb)', 'Archive exceeded maximum size! (%1mb, max is %2mb)', 'Home dir', 'Force LAME rate', 'Transcode', 'httpQ', 'Error when contacting httpQ server (%1).', 'Use database cache?', 'Unused records were not deleted due to skips.', 'Length', 'Play album', 'Listing view: ', 'Max number of detailed views', 'Effective', 'Detailed', 'AJAX Prototype URL', 'Radio', 'Loop', 'Sorry - there were problems logging you on.', 'Demo', 'Synchronizing %1 with %2 entries', 'Network status %1: %2', 'Network update %1/%2', 'Choose sublevel: %1', 'Current level: %1', 'Network', 'Enable network server mode', 'Network hosts', 'Host URL', 'Username', 'Stored', 'Updated', 'Missing CURL or url fopen support, read here: %1', 'Allow network', 'deactivate', 'Virtual dir', 'Selected archiver not found', 'Shoutbox', 'shout', 'Theme', 'Append', 'Full', 'Next radio sequence(s)', 'Store resized album covers', 'Album store directory', 'Could not read uploaded file', 'No entries found', 'Appended %1 entries', 'Upload playlist (m3u/m3u8/pls)');

$klang[1] = array('Norwegian', 'ISO-8859-1', 'Norsk (bokm�l)', 'Hva er mest spilt', 'Hva er nytt', 'S�k', '(bare %1 vist)', 'sek', 'S�keresultater: \'%1\'', 'fant', 'Ingen.', 'oppdateringsvalg for s�kedatabase', 'Slett ubrukte rader?', 'Ombygg ID3?', 'Debugmodus?', 'Oppdater', 'Avbryt', 'oppdaterer s�kedatabase', 'Fant %1 filer.', 'Kunne ikke lese fil: %1, hoppet over.', 'Installert: %1 - Oppdaterer: %2, skanner: ', 'S�ker: ', 'Feilet - sp�rring: %1', 'Kunne ikke lese denne filen: %1. Hoppet over.', 'Fjernet referanse til: %1', 'La inn %1, oppdaterte %2, slettet %3 hvor %4 feilet og %5 ble hoppet over igjennom %6 filer - %7 sek - %8 markert for sletting.', 'Ferdig.', 'Lukk', 'Fant ingen filer her: "%1"', 'kPlaylist innlogging', 'Albumliste fra artist: %1', 'Hurtigvelg %1', 'Ingen l�ter valgt. Spilleliste ikke oppdatert.', 'Spilleliste oppdatert!', 'Tilbake', 'Spilleliste lagt til!', 'Husk � oppdatere side.', 'logg inn:', 'hemmelighet:', 'Advarsel! Dette er en privat webside. All aktivitet blir logget.', 'Logg inn', 'SSL kreves for p�logging.', 'Spill', 'Slett', 'Delte: ', 'Lagre', 'Kontroller spilleliste: \'%1\' - %2 titler', 'Redigerer', 'Viser', 'Velg', 'Sek', 'Status', 'Info', 'Slett', 'Navn', 'Totalt:', 'Feil', 'Handling p� valgte: ', 'Sekvens:', 'rediger spilleliste', 'Slett denne oppf�ringen', 'ny spilleliste', 'Navn:', 'Lag', 'Spill: ', 'Fil', 'Album', 'Alle', 'Valgte', 'legg til', 'spill', 'editer', 'ny', 'Velg:', 'Spillekontroll: ', 'Spilleliste: ', 'Numerisk hurtigvalg', 'First IT gir deg:', '(se etter ny versjon)', 'Hjemmeside', 'bare id3', 'album', 'tittel', 'artist', 'Hurtigvelg album fra artist', 'vis', 'Delte spillelister', 'Brukere', 'Adminkontroll', 'Hva er nytt', 'Mest spilt', 'Logg ut', 'Valg', 'Sjekk', 'Min', 'endre brukerinformasjon', 'ny bruker', 'Fullt navn', 'Brukernavn', 'Endre passord?', 'Passord', 'Kommentar', 'Tilgangsniv�', 'P�', 'Av', 'Slett bruker', 'Logg ut bruker', 'Oppdater', 'Ny bruker', 'slett', 'logg ut', 'Bruke EXTM3U egenskap?', 'Vise hvor mange rader (mest spilt/nytt)', 'Maks s�kerader', 'Omsetting', '�pne katalog', 'G� til katalog: %1', 'Last ned', 'G� ett steg opp', 'G� til hovedkatalog.', 'Se etter ny versjon', 'brukere', 'Spr�k', 'valg', 'Avsperret', 'Omskuff:', 'Innstillinger', 'Hovedkatalog', 'Nedlastningslokalisasjon', 'Standardspr�k', 'Et Windows-system', 'Krev HTTPS', 'Tillat spoling', 'Tillat nedlastninger', 'Tidsavbrudd for innlogging (sek)', 'Rapportere mislykkede innloggingsfors�k', 'Vent - henter filliste', 'Spilleliste kunne ikke legges til!', 'Admin', 'Logg inn med HTTPS for � endre!', 'Aktiver innebygd kanalvirkning', 'Tittel', 'Artist', 'Album', 'Kommentar', '�r', 'L�tnummer', 'Stil', 'ikke satt', 'Maksimal nedlastningshastighet', 'Bruker', '%1 minutter - %2 titler', '%1 kbit %2 minutter', 'Sjangerliste: %1', 'G�', 'Spilletid %1d %2t %3m : %4 filer : %5 mb', 'Ingen relevante ressurser her.', 'Passord endret!', 'Ny bruker', 'Vennligst foreta et valg!', 'Hva er oppdatering?', 'Klikk her for hjelp', 'Bruk eksterne bilder?', 'Plassering for eksterne bilder', 'Eksisterende passord', 'Det eksisterende passordet er feil!', '�nsket arkiveringsprogram', 'Arkiv kunne ikke opprettes', 'Mulig duplikat funnet: %1 - %2', 'Virkelig slette spilleliste?', 'Alfabetisk', 'Tilfeldig', 'Sorter', 'Original', 'Bruke javascript', 'Er du sikker p� at du vil slette denne brukeren?', 'Vis historie', 'historie', 'Rader', 'Ekstern CSS fil', 'Fjern duplikater', 'OK', 'FEIL', 'Stream', '(vis som)', 'filer', 'album', '%1d %2t %3m %4s', 'Generelt', 'Skreddersy', 'Filh�ndtering', 'Klikk p� ? for hjelp.', 'Automatisk databasesynkronisering', 'Send filendelse', 'Tillat uautoriserte streams', 'Inkluder headere', 'Eksternt javascript', 'Hjemmeside', 'Vis First IT gir deg del', 'Vis oppgraderingsdel', 'Vis statistikk', 'Skriv ID3v2 i stream', 'Ny bruker funksjonalitet', 'Filtyper', 'Ja', 'Nei', 'Filendelse', 'MIME', 'Inkluder i M3U', 'editer filtype', 'Sikker?', 'Optimistisk filsjekk', 'Randomiserer', 'Modus', 'Spilleliste', 'Ingen, direkte', 'Mine favoritter', 'Fant ingen rader', 'Hits p� systemet', 'Rekkef�lge', 'Sl� p� LAME st�tte', 'Deaktivert', 'Tillat LAME bruk?', 'E-post', 'Tillat e-post av filer', 'SMTP-tjener', 'SMTP-port', 'E-post til', 'Beskjed', 'Send', 'E-post sendt!', 'Aktiver opplastning', 'Opplastningskatalog', 'Aktiver mp3e-post', 'Last opp', 'Fil lastet opp!', 'Fil kunne ikke bli lastet opp!', 'Du er n�dt til � skru p� cookies for � logge inn!', 'Periode', 'siden alltid', 'denne uken', 'denne m�neden', 'siste m�ned', 'hits', 'LAME-kommando', 'Vis albumcover', 'Albumfiler', 'Omskaler albumbilder', 'Albumh�yde', 'Albumbredde', 'E-postmetode', 'Direkte', 'Pear', 'Vent!', 'Vennligst skriv inn en gyldig e-post i alternativer!', 'Spillelister direkte?', 'Vis album fra URL?', 'Album URL', 'Kunne ikke sende!', 'Bruker lagt til!', 'Arkivgenerator', 'Arkivet er slettet.', 'Bruker oppdatert!', 'Musikktilpassing', '%1 rader filtrert', 'Logg aksess', 'Vis', 'Arkivert', 'Oppslagstavle', 'Skrevet den %1 av %2', 'mer', 'Publiser', '%1 mb', '%1 kb', '%1 bytes', 'Rekursiv', 'Forrige', 'Neste', 'G� til side %1', 'Side:', 'Aldri spilt', 'Bekreft nyregistreringer manuelt', 'Venter', 'aktiver', 'Alle felter markert med * er obligatoriske', 'Kontoen din vil bli sjekket og aktivert manuelt.', 'Siste avspillinger', 'husk meg', 'Stil', 'finn', 'Skriv inn kataloger og s�ke i', 'Bruke valgte?', 'Spilletid min/maks', 'Minutter', 'm3u', 'asx (WMA)', 'Hvis oppdateringen stopper, klikk her: %1', 'F�lg symboliske lenker?', 'Mal for presentasjon av fillister', 'Aktiver URL-sikkerhet', 'Tillatelseliste for opplasting', 'Filtypen er ikke tillatt', 'Spillelisten er tom!', 'Tekster', 'URL til tekster', 'Vis lenke til tekster?', '(eller?)', 'Ukjent brukernavn eller passord', 'Maks opplastningsst�rrelse: %1', '�pne offentlig RSS-tilgang', 'Vennligst skriv et passord!', 'Trenger brukernavn og navn', 'Brukernavn er allerede i bruk!', 'Sl� av administrasjonstilgang for denne p�loggingen?', 'Henter database rader %1/%2', 'Kunne ikke finne "%1", er fil slettet?', 'Fra/til dato (DDMM��)', 'Feil i verdier, vennligst pr�v p� nytt', 'Maks tekstlengde', 'Katalogkolonner', 'Ny brukermal', 'Mal', 'Navn p� mal', 'Trenger er malnavn', 'Standard mal for ny bruker', 'Tagekstraktor', 'Tillat bruk av arkivering', 'Maksimal arkivst�rrelse (mb)', 'Arkiv st�rre enn det som er tillatt! (%1mb, maks er %2)');

$klang[2] = array('German', 'ISO-8859-15', 'Deutsch', 'Was ist hip', 'Was ist neu', 'Suchen', '(nur %1 angezeigt)', 'Sekunde', 'Suchergebnisse: \'%1\'', 'gefunden', 'Keine.', 'Einstellungen f�r die Aktualisierung der Such-Datenbank', 'Unbenutzte Datens�tze l�schen?', 'ID3 erneuern?', 'Debug Modus?', 'Update', 'Abbrechen', 'Such-Datenbank aktualisieren', '%1 Dateien gefunden', 'Konnte Datei nicht untersuchen: %1, wird �bersprungen.', 'Installiert: %1 - Aktualisiert: %2, untersuche:', 'Suche: ', 'Fehler - Abfrage: %1', 'Konnte Datei nicht lesen: %1, wird �bersprungen.', 'Entfernt: %1', 'Eingef�gt %1, aktualisiert %2, gel�scht %3, dabei %4 fehlgeschlagen und %5 �bersprungen; %6 Dateien gesamt - %7 Sek - %8 markiert zum l�schen.', 'Erledigt', 'Schliessen', 'Konnte hier keine Dateien finden: "%1"', 'kPlaylist Login', 'Album Liste f�r Interpret: %1', 'Kurzwahl %1', 'Keine Lieder ausgew�hlt. Playliste nicht aktualisiert.', 'Playliste aktualisiert', 'Zur�ck', 'Playliste hinzugef�gt!', 'Die Seite erneut laden!', 'Login:', 'Passwort:', 'Achtung! Dies ist eine private Webseite! Alle Aktionen werden protokolliert!', 'Login', 'SSL wird zum Einloggen ben�tigt.', 'Abspielen', 'L�schen', '�ffentlich: ', 'Sichern', 'Playliste bearbeiten: "%1" - %2 Titel', 'Editor', 'Betrachter', 'Ausw�hlen', 'Seq', 'Status', 'Info', 'L�schen', 'Name', 'Summe:', 'Fehler', 'Aktion auf Auswahl:', 'Reihenfolge:', 'Playliste bearbeiten', 'Diesen Eintrag l�schen', 'Playliste hinzuf�gen', 'Name:', 'Erstellen', 'Abspielen: ', 'Datei', 'Album', 'Alle', 'Auswahl', 'Hinzuf�gen', 'Abspielen', 'Bearbeiten', 'Neu', 'Ausw�hlen:', 'Spielen: ', 'Playliste: ', 'Kurzwahl numerisch', 'First IT pr�sentiert:', '(Suche nach Update)', 'Startseite', 'Nur ID3 Tags', 'Album', 'Titel', 'Interpret', 'Kurzwahl Album nach Interpret', 'Zeige', 'Gemeinsame Playlisten', 'Benutzer', 'Administration', 'Was ist neu', 'Was ist hip', 'Logout', 'Optionen', '�berpr�fen', 'Mein KPlaylist', 'Benutzer �ndern', 'Neuer Benutzer', 'Vollst�ndiger Name', 'Login', 'Passwort �ndern?', 'Passwort', 'Anmerkung', 'Zugangslevel', 'An', 'Aus', 'Benutzer l�schen', 'Benutzer ausloggen', 'Erneuern', 'Neuer Benutzer', 'L�schen', 'Logout', 'EXTM3U Feature benutzen?', 'Wieviele Zeilen zeigen (hip/neu)', 'Max. Anzahl von Suchergebnissen', 'Reset', 'Verzeichnis �ffnen', 'Gehe zum Verzeichnis: %1', 'Download', 'Eine Ebene h�her', 'In das Basisverzeichnis', 'Nach einem Upgrade suchen', 'Benutzer', 'Sprache', 'Optionen', 'Gesperrt', 'Zufall:', 'Einstellungen', 'Hauptverzeichnis', 'Stream Location', 'Voreingestellte Sprache', 'Ein Windows-System', 'Ben�tigt HTTPS', 'Suche erlaubt', 'Download erlaubt', 'Session Timeout', 'Fehlgeschlagene Login-Versuche protokollieren', 'Bitte warten - hole Dateiliste', 'Playliste konnte nicht erstellt werden!', 'Administrator', 'Einloggen mit HTTPS f�r �nderungen', 'Streaming Engine aktivieren', 'Titel', 'Artist', 'Album', 'Kommentar', 'Jahr', 'Lied', 'Genre', 'nicht gesetzt', 'Max. Download Rate (kbit/s)', 'Benutzer', '%1 Min - %2 Titel', '%1 kbit %2 Min', 'Genre Liste: %1', 'Los', '%1T %2Std %3Min Spielzeit %4 Dateien %5 MB', 'Hier gibt es keine passenden Eintr�ge.', 'Passwort ge�ndert!', 'Anmelden', 'Bitte treffe eine Auswahl!', 'Was ist ein Update?', 'Klicke hier f�r Hilfe', 'Benutze externe Bilder?', 'Pfad zu externen Bildern', 'Aktuelles Passwort', 'Aktuelles Passwort nicht korrekt!', 'Bevorzugter Archivierer', 'Archiv konnte nicht erstellt werden', 'M�gliche doppelte Datei gefunden: "%1" - "%2"', 'Playliste wirklich l�schen?', 'Alphabetisch', 'Zufall', 'Sortiert', 'Original', 'Benutze Javascript', 'Benutzer wirklich l�schen?', 'Zeige History', 'History', 'Zeilen', 'Externe CSS Datei', 'L�sche doppelte Eintr�ge', 'OK', 'FEHLER', 'Stream', '(erscheinen wie)', 'Dateien', 'Album', '%1T %2Std %3Min %4Sek ', 'Allgemein', 'Anpassen', 'Datei Kontrolle', 'Klick das "?" f�r Hilfe', 'Automatische Datenbanksynchronisation', 'Dateiendungen senden', 'Nichtautorisierte Streams erlauben', 'Header einbeziehen', 'Externes Javascript', 'Homepage', 'Zeige "First IT hat" Teil', 'Zeige Upgrade-Teil', 'Zeige Statistik', 'Schreibe ID3v2 Tags beim Streaming', 'Benutzer Anmeldung aktivieren', 'Datei Typen', 'Ja', 'Nein', 'Dateiendung', 'MIME', 'M3U einbeziehen', 'Datei Typ bearbeiten', 'Sicher?', 'Optimistische Dateipr�fung', 'Zufallsliste', 'Modus', 'Playliste', 'Nein, direkt', 'Meine Favoriten', 'Keine Treffer gefunden', 'Absolute Hits', 'Reihenfolge', 'LAME Unterst�tzung aktivieren?', 'Deaktiviert', 'LAME Verwendung erlauben?', 'Email', 'Versenden von Dateien per Email erlauben?', 'SMTP Server', 'SMTP Port', 'Email an', 'Nachricht', 'Senden', 'Email gesendet!', 'Aktiviere Upload', 'Upload-Verzeichnis', 'Aktiviere mp3mail', 'Upload', 'Datei hochgeladen!', 'Datei konnte nicht hochgeladen werden!', 'Cookies m�ssen aktiviert sein, um einzuloggen!', 'Zeitraum', 'Immer', 'Diese Woche', 'Diesen Monat', 'Letzten Monat', 'Hits', 'LAME Befehl', 'Zeige Album Cover', 'Album Dateien', 'Gr�sse der Album-Bilder anpassen', 'Album H�he', 'Album Breite', 'Email Methode', 'Direkt', 'Pear', 'Warten!', 'Bitte eine g�ltige Emailadresse angeben!', 'Playlists inline?', 'Zeige Album von URL?', 'Album URL', 'Konnte nicht senden!', 'Benutzer hinzugef�gt!', 'Archiv-Ersteller', 'Archiv wurde gel�scht', 'User aktualisiert', 'Musik-Treffer', '%1 Eintr�ge gefiltert', 'Zugriff auf Log', 'Lesbar', 'Archiviert', 'Bulletin', '%1 von %2 eingef�gt', 'Mehr', 'Ver�ffentlichen', '%1 MB', '%1 KB', '%1 Bytes', 'Unterverzeichnis spielen', 'Vorhergehende', 'N�chste', 'Gehe zu Seite %1', 'Seite:', 'Nie gespielt', 'Anmeldung manuell akzeptieren', 'Anh�ngig', 'aktiviere', 'Alle mit * markierten Felder sind zwingend', 'Dein Zugang wird �berpr�ft und manuell aktiviert.', 'K�rzliche Streams', 'Login merken', 'Stil', 'Finde', 'Gib den zu durchsuchenden Pfad ein', 'Benutzer ausgew�hlt', 'Zeit Titel: min/max', 'Minuten', 'm3u', 'asx', 'Falls das Update fehlschl�gt, klicke hier %1', 'Symbolischen Links folgen?', 'Datei Template', 'Aktiviere URL Sicherheit', 'Lade Whitelist hoch', 'Dateityp nicht erlaubt', 'Wiedergabeliste ist leer!', 'Lyrics', 'Lyrics URL', 'Lyrics Link anzeigen', '(oder?)', 'Unbekannter Benutzername oder Passwort', 'Maximale Upload Gr��e: %1', '�ffentlich RSS Feed erstellen?', 'Bitte Passwort festlegen', 'Ben�tige Name und Login', 'Benutzername ist bereits eingeloggt', 'Adminberechtigung f�r die aktuelle Session entfernen?', 'Hole Datenbankeintraege: %1/%2', 'Kann Datei nicht finden "%1", vielleicht gel�scht?', 'von/bis Datum (TTMMJJ)', 'Eingabefehler, bitte noch einmal versuchen', 'maximale Anzahl von Textzeichen', 'Verzeichnisspalten', 'Neue Vorlage', 'Vorlage', 'Vorlagenbezeichnung', 'Ben�tige Vorlagenbezeichnung!', 'Vorgegebene Anmeldevorlage', 'Tag Extraktor:', 'Erlaube Archivierer', 'Maximale Archivgr�sse (mb)', 'Archiv �berschreitet maximale Gr�sse! (%1mb, maximal %2mb)', 'Hauptverzeichnis', 'Lame Rate', 'Transcode', 'httpQ', 'St�rung, wenn mit httpQ Server (%1) in Verbindung getreten wird.', 'Datenbankpufferspeicher benutzen? ', 'Unbenutzte Aufzeichnungen lagen nicht an den Zeilenspr�ngen gel�schtes.', 'L�nge', 'Album abspielen', 'Liste ansehen', 'Max. Anzahl der detailierten Ansicht', 'Effective', 'Detailiert', 'AJAX Prototype URL', 'Radio', 'Loop', 'Entschuldigung, aber es gibt LogIn - Probleme.', 'Demo', 'Synchronisiere %1 mit %2', 'Netzwerkstatus %1: %2 ', 'Netzwerkupdate %1: %2');

$klang[3] = array('Swedish', 'ISO-8859-10', 'Svenska', 'Hetast just nu', 'Vad �r Nytt', 'S�k', '(endast %1 visad)', 'sek', 'S�kresultat: \'%1\'', 'hittade', 'Ingen.', 'uppdatera inst�llningar f�r s�kdatabas', 'Ta bort oanv�nda album', '�teruppbygg ID3?', 'K�r debug?', 'Uppdatera', 'Avbryt', 'uppdatera s�kdatabas', 'Hittade %1 filer.', 'Kunde inte l�sa fil: %1, hoppade �ver.', 'Installerer %1 - Uppdaterar: %2, l�ser:', 'L�ser:', 'Misslyckades - fr�ga: %1', 'Kunde inte l�sa filen: %1, Hoppade �ver.', 'Tog bort: %1', 'Infogade %1, uppdaterade %2, tog bort %3, varav %4 misslyckades och hoppade �ver %5 av %6 filer - %7 sek - %8 markerade f�r borttaganing', 'F�rdig', 'St�ng', 'Kunde inte hitta n�gra filer h�r: \'%1\'', 'kPlaylist Inloggning', 'Albumlista f�r artist: %1', 'Snabbval %1', 'Inga l�tar valda. Spellistan �r ej updaterad.', 'Spellista uppdaterad!', 'Tillbaka', 'Spellista inlagd!', 'Kom ih�g att uppdatera sidan.', 'Anv�ndarnamn:', 'L�senord:', 'Observera! Detta �r inte en publik websida. All aktivitet �r loggad.', 'Inloggning', 'SSL beh�vs f�r inloggning', 'Spela', 'Ta Bort', 'Delad:', 'Spara', 'Kontrollera l�tlista: "%1" - %2 titlar', 'Redigerare ', 'Visare ', 'V�lj ', 'Sekv ', 'Status', 'Info', 'Ta Bort', 'Namn', 'Totalt:', 'Fel', 'Handling vid val', 'Sekvens:', 'redigera spellista', 'Ta bort den h�r raden', 'L�gg till spellista', 'Namn:', 'Skapa', 'Spela:', 'Fil', 'Album', 'Alla', 'Markerad', 'l�gg till', 'spela', 'redigera', 'ny', 'V�lj:', 'Spelkontroll:', 'Spellista:', 'Snabbv�lj numeriskt', 'First IT ger dig:', '(S�k efter uppdatering)', 'Hemsida', 'endast id3', 'album', 'titel', 'artist', 'Snabbv�lj album fr�n artist', 'visa', 'Delade spellistor', 'Anv�ndare', 'Adminkontroll', 'Vad �r nytt', 'Mest spelat', 'Logga ut', 'Inst�llningar', 'Kontrollera ', 'Min ', 'redigera anv�ndare', 'ny anv�ndare', 'Fullst�ndigt namn', 'Anv�ndarnamn ', '�ndra l�senord?', 'L�senord', 'Kommentar ', 'Beh�righet ', 'P� ', 'Av ', 'Ta bort anv�ndare', 'Logga ut anv�ndare', 'Uppdatera ', 'Ny anv�ndare', 'ta bort', 'logga ut', 'Anv�nd EXTM3U funktion?', 'Visa hur m�nga rader (mest spelat/nytt)', 'H�gst antal s�krader', 'Nollst�ll', '�ppna mapp', 'G� till mapp: %1', 'Ladda ner', 'G� ett steg upp', 'G� till rotkatalogen', 'Kolla efter uppgradering', 'anv�ndare ', 'Spr�k ', 'inst�llningar ', 'Kickad', 'Blanda', 'Inst�llningar', 'Rotniv� ', 'Stream lokalisering', 'Standard spr�k', 'Ett Windowssystem', 'Kr�v HTTPS', 'Till�t fils�k', 'Till�t nerladdning', 'Sessionen avbruten.', 'Rapportera misslyckat loginf�rs�k', 'V�nta - h�mtar fillista', 'Spellista kunde inte l�ggas till!', 'Admin', 'Logga in med HTTPS f�r att �ndra!', 'Aktivera streaming', 'Titel', 'Artist', 'Album', 'Kommentar', '�r', 'Sp�r', 'Genre', 'inte satt', 'Max nerladdningshastighet (kbps)', 'Anv�ndare', '%1 min - %2 titlar', '%1 kbit %2 min', 'Genre lista: %1', 'K�r', '%1d %2t %3m speltid %4 filer %5 MB', 'Inga relevanta resurser h�r.', 'L�senordet �ndrat!', 'Skapa konto', 'Var v�nlig och g�r ett val!', 'Vad �r uppdatering?', 'Klicka h�r f�r hj�lp.', 'Anv�nda externa bilder?', 'Externa bildens s�kv�g.', 'Nuvarande l�senord', 'Nuvarande l�senord matchar inte!', '�nskad arkiverare', 'Arkiv kunde inte skapas', 'Trolig fildubblett hittad: "%1"  "%2"', 'Verkligen radera spellistan?', 'Alfabetisk', 'Slumpad', 'Sortera', 'Original', 'Anv�nd javascript', '�r du s�ker att du vill radera denna anv�ndare?', 'Visa historia', 'historia', 'Rader', 'Extern CSS fil', 'Ta bort dubletter', 'OK', 'FEL', 'Stream', '(visa som)', 'filer', 'album', '%1d %2t %3m %4s', 'Generellt', 'Anpassa', 'Filhanterning', 'Klicka p� ? f�r hj�lp', 'Automatisk databas synkronisering', 'Skicka fil �ndelse', 'Till�t overifierade streamar', 'Inkludera headers', 'Externt javascript', 'Hemsida', 'Visa First IT ger dig del', 'Visa uppgraderingsdel', 'Visa statistik', 'Skriv ID3v2 med stream', 'Aktivera anv�ndarregistrering', 'Filtyper', 'Ja', 'Nej', 'Fil�ndelse', 'MIME', 'Inkludera i M3U', 'editera filtyp', 'S�kert?', 'Optimistisk filkontroll', 'Randomisera', 'L�ge', 'Spellista', 'Ingen, direkt', 'Mina favoriter', 'Kunde inte hitta n�gra tr�ffar', 'Alla tiders hitl�tar', 'Ordning', 'Aktivera LAME-st�d?', 'Avst�ngd', 'Till�t LAME-anv�ndning?', 'Epost', 'Till�t epost av filer?', 'SMTP-server', 'SMTP-port', 'E-Post till', 'Meddelande', 'Skicka', 'Meddelandet skickat!', 'Aktivera uppladdning', 'Uppladdningsbibliotek', 'Aktivera mp3mail', 'Uppladdning ', 'Fil uppladdad', 'Filen kunde ej laddas upp', 'Du m�ste aktivera cookies f�r att kunna logga in!', 'Period', 'N�gonsin', 'Denna vecka ', 'Denna m�nad', 'Senaste m�naden', 'tr�ffar', 'LAME kommando', 'Visa omslag', 'Albumfiler', 'Anpassa bildens storlek', 'H�jd', 'Bredd', 'Brevmetod', 'Direkt', 'Pear', 'V�nta', 'Skriv in en giltig epostadress i inst�llningar!', 'Playlist inline', 'Visa album fr�n URL?', 'Album URL', 'Kunde inte skicka!', 'Anv�ndare upplagd!', 'Arkiv skapare', 'Arkiv raderat', 'Anv�ndare uppdaterad!', 'Music match', '%1 inl�gg filtrerat', 'Logg access', 'Visningsbar', 'Arkiv', 'Bulletin', 'Ifyllt %1 av %2', 'mer', 'Publisera', '%1 mb', '%1 kb', '%1 bytes', '�terkommande', 'F�reg�ende', 'N�sta', 'G� till sida %1', 'Sida:', 'Aldrig spelad', 'Manuellt godk�nna registreringar', 'V�ntande', 'aktivera', 'Alla f�lt markerade med * �r obligatoriska', 'Ditt konto kommer att kontrolleras och aktiveras manuellt.', 'Senaste streamar', 'kom ih�g mig', 'Stil', 'hitta', 'Fyll i s�kv�gar f�r att s�ka efter', 'Anv�nd valda?', 'Track tid min/max', 'Minuter', 'm3u', 'asx (WMA)', 'Om uppdateringen stannar, klicka h�r: %1', 'F�lj symlink?', 'Fil mall', 'Aktivera URL s�kerhet', 'Ladda upp vitlista', 'Filtypen �r inte till�ten.', 'Spellistan �r tom!', 'S�ngtexter', 'S�ngtexter URL', 'Visa s�ngtexter l�nk?', '(eller?)', 'Felaktigt anv�ndarnamn eller l�senord', 'Max filstorlek vid uppladdning: %1', '�ppna publik RSS fl�de?', 'Ange ett l�senord.', 'Anv�ndarnamn och l�senord m�ste s�ttas', 'Anv�ndarnamnet upptaget!', 'Drop admin access for this session?', 'H�mtar data: %1/%2', 'Kan inte hitta "%1", filen borttagen?', 'Fr�n/till datum (DDMMYY)', 'Fel i f�lt, f�rs�k igen', 'Max textl�ngd', 'Dir kolumer', 'Ny template', 'template', 'namn p� template', 'Beh�ver ett template namn', 'Standard template', 'Tag extrator', 'Tilll�t anv�nda arkiv', 'St�rsta arkiv storlek (mb)', 'Arkiv har �verstigit st�rsta storlek (%1mb, max �r %2mb)', 'Hemma dir ', 'Framtvinga LAME tal', 'Transcoda', 'httpQ', 'Ett fel upptod n�r httpQ servern kontaktades (%1)', 'Anv�nd databas cache?', 'Oanv�nda l�tar togs ej bort pga. �verhoppnignar.', 'L�ngd', 'Spela Album', 'Listvy:', 'Maximalt antal detaljerade vyer', 'Effektiv', 'Detaljerad', 'AJAX Prototyp URL', 'Radio', 'Loop');

$klang[4] = array('Dutch', 'ISO-8859-1', 'Nederlands', 'Wat is populair', 'Wat is nieuw', 'Zoek', '(waarvan %1 in deze lijst)', 'sec', 'Zoekresultaten: \'%1\'', 'gevonden', 'Geen.', 'update database zoekopties', 'Verwijder ongebruikte bestanden? ', 'ID3 vernieuwen?', 'Foutopsporing?', 'Vernieuwen', 'Annuleren', 'Zoek in database updaten', '%1 bestanden gevonden.', 'Problemen met : %1, overgeslagen.', 'Toegevoegd: %1 Aangepast: %2 Scan:', 'Scan:', 'Mislukt - gezocht: %1', 'Kan het volgende bestand niet lezen: %1. Overgeslagen.', 'Verwijderd: %1', 'Toegevoegd %1, bijgewerkt %2, verwijderd %3 waarvan %4 mislukt en %5 overgelagen van %6 bestanden - %7 sec - %8 gemarkeerd voor verwijdering.', 'Klaar', 'Sluiten', 'Kan geen bestanden vinden in: "%1"', 'kPlaylist Log in', 'Albumlijst van artiest: %1', 'Snelkeuze %1', 'Geen muziek geselecteerd. Afspeellijst niet bijgewerkt.', 'Afspeellijst bijgewerkt!', 'Terug', 'Afspeellijst toegevoegd!', 'Niet vergeten om de pagina te verversen.', 'Gebruikersnaam:', 'Wachtwoord:', 'NB! Dit is een niet publieke toegankelijke website. Alle acties worden opgeslagen in een log bestand.', 'Login', 'SSL benodigd om in te loggen.', 'Afspelen', 'Verwijderen', 'Gedeeld', 'Opslaan', 'Instellingen afspeellijst "%1"- %2 nummer(s)', 'Editor', 'Viewer', 'Selecteren', 'Volgorde', 'Status', 'Informatie', 'Verwijder', 'Naam', 'Totaal:', 'Fout', 'Actie op selectie:', 'Volgorde:', 'afspeellijst bewerken', 'Verwijder deze regel', 'afspeellijst toevoegen', 'Naam:', 'Aanmaken', 'Afspelen:', 'Bestand', 'Album', 'Alles', 'Geselecteerd', 'toevoegen', 'afspelen', 'bewerken', 'nieuw', 'Selectie:', 'Afspeelopties', 'Afspeellijst:', 'Snelkeuze nummer', 'First IT presenteert:', '(Update controle)', 'Startpagina', 'alleen id3', 'album', 'titel', 'artiest', 'Album snelkeuze op artiest', 'bekijk', 'Gedeelde afspeellijsten', 'Gebruikers', 'Administrator opties', 'Wat is nieuw', 'Wat is Populair', 'Uitloggen', 'Instellingen', 'Controleer', 'Mijn opties', 'Bewerk gebruikersaccount', 'Nieuw gebruikersaccount', 'Volledige naam', 'Inlognaam:', 'Wachtwoord veranderen?', 'Wachtwoord', 'Commentaar', 'Toegangsniveau', 'Actief', '----', 'Verwijder gebruiker', 'Gebruiker afsluiten', 'Ververs pagina', 'Nieuwe gebruiker', 'Wis', 'uitloggen', 'Gebruik EXTM3U optie?', 'Hoeveel rijen tonen (Populair / Nieuw)', 'Maximaal aantal rijen zoekresultaat', 'Reset', 'Open map', 'Ga naar map: %1', 'Download', 'Een stap terug', 'Bovenste map', 'Update controle', 'gebruikers', 'Taal', 'opties', 'Verbannen', 'Willekeurig:', 'Instellingen', 'Startdirectory', 'Streamlokatie', 'Standaardtaal', 'Is een Windows systeem', 'HTTPS benodigd', 'Zoeken toestaan', 'Downloaden toestaan', 'Sessie timeout', 'Raporteer niet geslaagde inlogpogingen', 'Een ogenblik - bestandslijst ophalen', 'Afspeellijst kan niet toegevoegd worden!', 'Beheer', 'Om te wijzigen inloggen met https verbinding!', 'Gebruik stream engine', 'Titel', 'Artiest', 'Album', 'Bijzonderheden', 'Jaar', 'Nummer', 'Genre', 'niet ingesteld', 'Maximale downloadsnelheid (kbps)', 'Gebruiker', '%1 minuten- %2 titels', '%1 kbit %2 minuten', 'Genre lijst: %1', 'Ok', '%1d %2h %3m afspeelduur %4 bestanden %5 Mb', 'Geen relevante bron aanwezig', 'Wachtwoord veranderd!', 'Aanmelden', 'Maak een keuze a.u.b.!', 'Toelichting bij het vernieuwen van de database?', 'Klik hier voor help', 'Externe plaatjes gebruiken?', 'Path naar externe plaatjes', 'Huidig wachtwoord', 'Huidig wachtwoord is niet hetzelfde!', 'Compressie programma voorkeur', 'Gecomprimeerd bestand kon niet aangemaakt worden', 'Bestand mogelijk dubbel: %1 - %2', 'Afspeellijst zeker verwijderen?', 'Alfabetisch', 'Willekeurig', 'Sorteer', 'Origineel', 'Gebruik Javascript', 'Weet u zeker dat u deze gebruiker wil verwijderen?', 'Geef historie weer', 'historie', 'Regels', 'Extern CSS bestand', 'Verwijder dubbelingen', 'Ok', 'FOUT', 'Stream', '(toon als)', 'bestanden', 'albums', '%1d %2u %3m %4s', 'Algemeen', 'Aanpassen', 'Bestandsafhandeling', 'Klik ? voor hulp.', 'Synchroniseer database automatisch', 'Zend bestandsextensie mee', 'Sta niet geautoriseerde streams toe', 'Inclusief\' koptekst ', 'Extern javascript', 'Home pagina', 'Laat regel "First IT presenteert" zien', 'Laat regel "Updatecontrole" zien', 'Laat statistieken zien', 'Stuur ID3v2 mee met stream', 'Sta aanmelding van gebruikers toe', 'Bestandstypen', 'Ja', 'Nee', 'Extentie', 'MIME', 'M3U insluiten', 'Pas bestandtype aan', 'Zeker?', 'Optimistische bestandscontrole', 'Willekeurig afspelen', 'Modus', 'Afspeellijst', 'Geen, direct', 'Mijn favorieten', 'Niets gevonden', 'Hits Aller Tijden', 'Volgorde', 'Ondersteuning voor LAME aanzetten?', 'Uitgezet', 'Gebruik van LAME toestaan?', 'E-mailadres', 'Sta het versturen van bestanden via e-mail toe?', 'SMTP server', 'SMTP poort', 'Bericht aan', 'Bericht', 'Verstuur', 'Bericht verzonden!', 'Activeer upload', 'Uploadmap', 'Activeer MP3Mail', 'Upload', 'Bestand geupload!', 'Bestand kon niet geupload worden!', '"Cookies" moeten "aan" staan om in te loggen!', 'Periode', 'ooit', 'deze week', 'deze maand', 'laatste maand', 'gevonden', 'LAME parameters', 'Albumhoes tonen', 'Albumhoes bestanden', 'Albumhoes formaat aanpassen', 'Albumhoes hoogte', 'Albumhoes breedte', 'Wijze van mail versturen', 'Direct (sendmail)', 'Pear (Module)', 'Wacht', 'Gelieve geldig e-mailadres in te vullen! Zie "Opties"!', 'Afspeellijst insluiten?  ', 'Albumhoes ophalen vanaf URL?', 'Albumhoes URL', 'Het verzenden is mislukt!', 'Gebruiker toegevoegd!', 'Compressiebestand aangemaakt door', 'Compressiebestand gewist.', 'Gebruikersaccount aangepast!', 'Muziek overeenkomst', '%1 gefilterd', 'Log toegang', 'Zichtbaar', 'Gearchiveerd', 'Berichten', 'Geplaatst %1 door %2', 'meer', 'Publiceer', '%1 Mb', '%1 kb', '%1 bytes', 'Recursief', 'Vorige', 'Volgende', 'Ga naar pagina %1', 'Pagina:', 'Nog nooit gespeeld', 'Handmatig activeren van nieuwe aanmeldingen', 'Bezig', 'activeer', 'Alle velden met een * verplicht', 'Uw account wordt gecontroleerd en geactiveerd door een admin', 'Laatste stream', 'Onthoudt mijn gegevens', 'Stijl', 'zoek', 'Vul bestandslocatie in om te zoeken', 'Gebruik de geselecteerde bestanden?', 'Track tijd min/max', 'Minuten', 'm3u', 'asx (WMA)', 'Als de update stopt, klik hier: %1', 'Volg symlinks?', 'Bestandstemplate', 'Zet URL beveiling aan.', 'Upload witte lijst.', 'Bestandstype niet toegestaan.', 'Afspeellijst is leeg.', 'Songteksten', 'Songtekst URL', 'Laat de songtekst link zien?', '(of?)', 'Onbekende gebruikersnaam of wachtwoord', 'Maximum uploadgrootte: %1', 'Open publieke RSS feed?', 'Stel a.u.b. een wachtwoord in', 'Naam en login vereist', 'Gebruikersnaam is al bezet', 'Wil je de admin opties voor deze sessie stoppen?', 'Zoeken van databasebestanden: %1/%2', 'Kan "%1" niet vinden, is het bestand verwijderd?', 'Van/tot datum (DDMMYY)', 'Fout bij het invulveld, probeer het nog eens', 'Maximum tekstlengte', 'Directory kolommen', 'Nieuwe template', 'Template', 'Templatenaam', 'Een templatenaam is verplicht', 'Standaard signup template', 'Tag Extractor', 'Gebruik van archief toestaan', 'Maximale grootte archief (mb)', 'Maximum grootte van archief is overschreden! (%1mb, maximum is %2mb)', 'Home dir', 'Forceer LAME', 'Transcodeer', 'httpQ', 'Fout bij het verbinden met httpQ server (%1).', 'Gebruik database cache?', 'Ongebruikte gegevens werden niet gewist omdat ze werden overgeslagen.', 'Lengte / tijdsduuur', 'Afspelen album', 'Afspeel lijst', 'Maximale nummer details van lijst', 'Effectief', 'Gedetialeerd', 'Proto URL', 'Radio', 'Loop');

$klang[5] = array('Spanish', 'ISO-8859-1', 'Espa�ol', 'Lo popular', 'Lo nuevo', 'B�squeda', 's�lo el %1 es visible', 'seg', 'Resulados de B�squeda: \'%1\'', 'encontrado', 'Ninguno.', 'actualizar las opciones de b�squeda de la base de datos', '�Suprimir entradas sin uso? ', '�Reconstruir ID3? ', '�Modo de Depuraci�n? ', 'Actualizar', 'Cancelar', 'actualizar la base de datos de b�squeda', 'Se Encontraron %1 archivos', 'No se pudo determinar este archivo: %1, omitido', 'Instalado: %1 - Actualizado: %2, scanear:  ', 'Scanear', 'B�squeda Fallida: %1', 'No se pudo enconrar el archivo: %1. Omitido.', 'Borrado: %1', 'Insertado %1, actualizado %2, borrado %3 d�nde %4 fall� y %5 omitido %6 archivos - %7 seg - %8 marcado para borrar.', 'Finalizado', 'Cerrar', 'No se encontraron archivos en: "%1"', 'kPlaylist Entrada', 'Lista de canciones del artista: %1 ', 'Hotselect %1 ', 'Ninguna canci�n seleccionada. Lista no actualizada. ', '�Lista actualizada con �xito!', 'Regresar', '�Lista agregada!', 'Recuerde actualizar la p�gina', 'nombre de usuario:', 'contrase�a:', 'Aviso! Este es un sitio restringido. Todos los eventos se registrar�n.', 'Entrar', 'SSL requirido para entrar.', 'Reproducir', 'Borrar', 'Compartido:', 'Guardar', 'Lista de Control: "%1" - %2 t�tulos', 'Editor', 'Visor', 'Seleccionar', 'Seq', 'Estatus', 'Info', 'Supr', 'Nombre', 'Totales:', 'Error', 'Acci�n al seleccionar:', 'Secuencia:', 'editar lista de reproducci�n', 'Borrar esta entrada', 'agregar lista', 'Nombre:', 'Crear', 'Reproducir:', 'Archivo', 'Disco', 'Todo', 'Seleccionados', 'agregar', 'reproducir', 'editar', 'nuevo', 'Seleccionar:', 'Control de Reproducci�n:', 'Lista de reproducci�n:', 'Seleccionador Num�rico ', 'First IT le proporciona:', '(buscar actualizaciones)', 'P�gina Principal', 's�lo id3', 'disco', 't�tulo', 'artista', 'Seleccionador disco de artista', 'ver', 'Listas compartidas', 'Usuarios', 'Control de administrador', 'Lo nuevo', 'Lo popular', 'Salir', 'Opciones', 'Seleccionar', 'Mi', 'editar usuario', 'nuevo usuario', 'Nombre completo', 'Entrar', '�Cambiar contrase�a?', 'Contrase�a', 'Comentario', 'Nivel de aceso', 'Encendido', 'Apagado', 'Borrar usuario', 'Desconectar usuario', 'Actualizar', 'Nuevo usuario', 'supr', 'salir', '�Utilizar la opci�n de EXTM3U?', 'Mostrar cuantas filas (popular/nuevo)', 'M�x filas de la b�squeda', 'Restaurar', 'Abrir directorio', 'Ir al directorio: %1', 'Descargar', 'Subir un nivel', 'Ir al directorio ra�z', 'Buscar actualizaciones', 'usuarios', 'Idioma', 'opciones', 'Cerrado', 'Al azar:', 'Configuraci�n', 'Directorio principal', 'Posici�n del stream', 'Idioma predeterminado', 'Un sistema "Windows"', 'Requiere HTTPS', 'Permitir buscar', 'Permitir descargar', 'Sesi�n expirada ', 'Informar intentos de registro fallidos', 'Espere - obteniendo la lista de archivos', '�No se pudo agregar la lista!', 'Admin', 'Conexi�n con HTTPS a cambiar', '�Utilizar streaming?', 'T�tulo', 'Artista', 'Disco', 'Comentario', 'A�o', 'Pista', 'G�nero', 'no establecido', 'Tasa m�xima de descarga (kbps)', 'Usuario', '%1 minutos - %2 pistas', '%1 kbit %2 min', 'Lista de g�neros: %1', 'Ir', '%1d %2h %3m tiempo de reproducci�n %4 files %5 mb', 'No hay recursos importantes aqu�', '�Contrase�a actualizada!', 'Registrarse', '�Por favor seleccione!', '�Qu� est� actualizado?', '�Ayuda!', '�Utilizar im�genes externas?', 'Ruta de las im�genes externas', 'Contrase�a actual', '�La contrase�a actual no coincide!', 'Archivador preferido', 'No se pudo hacer el archivo', 'Se encontro un archivo probablemente duplicado en: "%1" "%2"', '�Realmente borrar la lista?', 'Alfab�tico', 'Al azar', 'Ordenar', 'Original', 'Utilizar javascript', '�Est� seguro de que desea eliminar este usuario?', 'Historial las vistas', 'historial', 'Filas', 'Archivo CSS externo', 'Eliminar duplicados', 'O.K.', 'ERR', 'Stream', '(mostrar como)', 'archivos', 'discos', '%1d %2h %3m %4s', 'General', 'Personalizar', 'Manejo de archivos', 'Seleccione " ? " para obtener ayuda', 'Sincronizaci�n autom�tica con la base de datos', 'Enviar la extensi�n del archivo', 'Permitir streams no autorizados', 'Incluir encabezados', 'Javascript externo', 'P�gina de inicio', 'Show First IT gives you part', 'Show upgrade part', 'Mostrar estad�sticas', 'Write ID3v2 with stream', 'Activar registro de usuarios', 'Tipos de archivos', 'Si', 'No', 'Extensi�n', 'MIME', 'Incluir en M3U', 'editar lista de tipos de archivo', '�Esta Seguro?', 'Comprobaci�n de archivos', 'Reproducir al azar', 'Modo', 'Lista de resproducci�n', 'Ninguno, directamente', 'Mis favoritos', 'No se encontraron hits', 'Hits de todo el tiempo', 'Orden', '�Activar LAME?', 'Desactivado', '�Permitir el uso de LAME?', 'Correo Electronico', '�Permitir el envio de archivos por email?', 'servidor SMTP', 'puerto del servidor SMTP', 'Enviar email a', 'Mensaje', 'Enviar', '�Email enviado!', 'Activar el agregar archivos', 'Agregar un directorio', 'Activar mp3mail', 'Agregar', '�Archivo agregado!', '�No se pudo agregar el archivo!', '�Debe activar las cookies para entrar!', 'Periodo', 'siempre', 'esta semana', 'este mes', 'el mes anterior', 'hits', 'Comando LAME', 'Mostrar la car�tula del disco', 'Archivos del disco', 'Cambiar el tama�o de las im�genes del disco', 'Altura del disco', 'Ancho del disco', 'M�todo para enviar el email', 'Directo', 'Pear', '�Espere!', '�Por favor, escriba una direcci�n de correo electronico v�lida en las opciones!', '�Lista de reproducci�n integras?', '�Mostrar el disco para el URL?', 'URL del disco', '�No se pudo enviar!', '�Usuario agregado!', 'Creador de archivos', 'El archivo se ha borrado', '�Usuario actualizado!', 'Music match', '%1 entradas filtradas', 'Registrar el acceso', 'Visible', 'Archivado', 'Bolet�n', 'Entrados %1 por %2', 'm�s', 'Publicar', '%1MB', '%1KB', '%1 bytes', 'Recursivo', 'Anterior', 'Siguiente', 'Ir a la p�gina %1', 'P�gina:', 'Nunca se ha reproducido', 'Aprobar los registros manualmente', 'Pendiente', 'Activar', 'Todos los campos marcados con " * " son obligatorios', 'Su cuenta ser� verificada y (de ser apropiado) activada manualmente', '�ltimas reproducciones', 'Recordarme', 'Estilo', 'Buscar', 'Introduzca las rutas en las que se va a buscar', '�Utilizar los seleccionados?', 'Tiempo de pista min/max', 'Minutos', 'm3u', 'asx (WMA)', 'Si la actualizaci�n se detiene, pulsar aqu�: %1', '�Seguir enlaces?', 'Plantilla de archivo', 'Activar seguridad URL', 'Subir lista blanca', 'Tipo de archivo no permitido', '�Lista de reproducci�n vac�a!', 'Letras', 'Letras URL', '�Mostrar enlace de las letras?', '�o?', 'NOmbre o contrase�a desconocida', 'Max tama�o transferencia: %1', '�Abrir RSS p�blico?', 'Ingrese una contrase�a', 'Necesita un Usuario y  contrase�a', 'El nombre de usuario ya  esta siendo utilizado', 'Permitir acceso administrativo para esta sesion?', 'Traer archivos existentes: %1/%2', 'No puedo encontrar "%1", el archivo ha sido borrado?', 'Desde/Fecha (DDMMAA) ', 'Error al ingresar los campos, intente otra vez', 'Longitud maxima', 'Directorio de columnas', 'Nueva esquela', 'Esquela', 'Nombre de la Esquela', 'Se necesita un Nombre de Esquela', 'Esquela por defecto', 'Estractor de Tags', 'Permitir archivar', 'Tama�o maximo de los archivos', 'La archivacion excedio el tama�o maximo!(%1mb, el maximo es %2mb)', 'Directorio inicial', 'Forzar la taza LAME', 'Recodificar', 'httpQ', 'Error contactando servidor httpQ (%1)', 'usar cache de la base de datos?', 'Los archivos no fueron borrados debido a', 'Tama�o', 'Escuchar Album', 'Ver Listado', 'Maximo Numero de listas detalladas', 'Efectividad', 'Detalles', 'Url Prototipo con AJAX', 'Radio', 'Sinfin');

$klang[6] = array('Portuguese', 'ISO-8859-1', 'Portugu�s', 'Populares', 'Mais Recente', 'Busca', '(apenas %1 encontrado)', 'seg', 'Resultados da busca: \'%1\'', 'encontrado', 'Nenhum', 'actualizar op��es da busca na base de dados ', 'Apagar entradas sem uso? ', 'Reconstruir ID3?', 'Modo Debug?', 'Atualizar', 'Cancelar', 'Atualizar busca no banco de dados', 'Encontrados %1 arquivos.', 'N�o foi poss�vel determinar este arquivo: %1, descartado', 'Install %1 - Atualizar: %2, escanear:', 'Busca:', 'Falha na busca: %1', 'N�o foi poss�vel ler este arquivo: %1. Descartado.', 'Removido: %1', 'Inserido %1, atualizado %2, apagado %2, onde %4, falhou em %5, descartado por %6, arquivos - %7 seg - %8 marcado para ser deletado', 'Finalizado', 'Fechar', 'N�o foi poss�vel encontrar arquivos aqui: "%1"', 'Logon kPlaylist', 'Lista de �lbum por artista: %1', 'Populares %1', 'Nenhuma m�sica selecionada. Lista n�o atualizada.', 'Lista atualizada!', 'Voltar', 'Lista atualizada', 'Lembre-se de atualizar a p�gina.', 'login:', 'senha:', 'Aten��o! Este site � restrito. Todas as ac��es s�o monitorizadas.', 'Login', 'SSL necess�rio para entrar.', 'Ouvir', 'Apagar', 'Compartilhado', 'Salvar', 'Lista de controlhe: "%1" - %2 t�tulos', 'Editor', 'Visualizador', 'Selecionar', 'Seq', 'Status', 'Info', 'Del', 'Nome', 'Totais', 'Erro', 'A��o selecionada:', 'Sequ�ncia', 'editar lista', 'Apagar esta entrada', 'adicionar lista', 'Nome:', 'Criar', 'Tocar:', 'Arquivo', '�lbum', 'Todos', 'Selecionado', 'adicionar', 'tocar', 'editar', 'novo', 'Selecionar', 'Controle', 'Lista:', 'Selecionar n�mero', 'First IT oferece:', '(verificar atualiza��o)', 'P�gina incial', 'apenas id3', '�lbum', 't�tulo', 'artista', 'Selecionar �lbum por artista', 'ver', 'Listas compartilhadas', 'Usu�rios', 'Controle de administrador', 'Este � novo', 'Este � popular', 'Logout', 'Op��es', 'Verificar', 'Meu', 'editar usu�rio', 'novo usu�rio', 'Nome completo', 'Login', 'Mudar senha?', 'Senha', 'Coment�rio', 'N�vel de acesso', 'Ligado', 'Desligado', 'Apagar usu�rio', 'Desconectar usu�rio', 'Atualizar', 'Novo usu�rio', 'apagar', 'desconectar', 'Utilizar op��o EXTM3U?', 'Mostrar quantos arquivos (popular/novo)', 'M�ximo de arquivos encontrados', 'Restaurar', 'Abrir diret�rio', 'Para o diret�rio: %1', 'Download', 'Subir um n�vel', 'Para o diret�rio principal', 'Verificar atualiza��es', 'usu�rios', 'Linguagem', 'op��es', 'Carregado', 'Aleat�rio', 'Configura��es', 'Diret�rio base', 'Local de stream', 'Linguagem padr�o', 'Sistema Windows', 'Requer HTTPS', 'Permitir busca', 'Permitir download', 'Sess�o expirou', 'Falha na tentativa de login', 'Aguarde - buscando a lista de arquivos', 'Lista n�o pode ser adicionada!', 'Admin', 'In�cio de uma sess�o com o HTTPS a mudar', 'Abilita motor de stream', 'T�tulo', 'Artista', 'Album', 'Coment�rio', 'Ano', 'Pista', 'G�nero', 'ilegivel', 'Max download rate(kbps)', 'Utilizador', '%1 mins - %2 titulos', '%1kbit%2mins', 'Lista g�nero: %1', 'Ir', 'Tempo audi��o: %1d %2h %3m : %4 ficheiros : %5 mb', 'Nada foi encontrado', 'Password alterada', 'Assinar', 'Por favor selecione algo!', 'O que � o update?', 'Click aqui para ajuda.', 'Usar imagens externas?', 'Caminho para imagens externas', 'Password actual', 'Password actual n�o condiz', 'Arquivo preferido', 'N�o pude criar arquivo!', 'Possiveis duplicados"%1" "%2"', 'Tem certeza?? APAGAR?', 'Alfab�ticamente', 'Aleat�rio', 'Arrumar', 'Original', 'Usa javascript', 'Tem a certeza que quer apagar utilizador ??', 'Ver hist�rico', 'hist�rico', 'Linhas', 'Ficheiro CSS externo', 'Remover duplicados', 'OK', 'Erro', 'Stream', '(mostra como)', 'ficheiros', 'albums', '%1d %2h %3m %4s', 'Geral', 'Personalizar', 'Modificar ficheiros', 'Click em ? para ajuda', 'Sincroniza autom. database', 'Enviar extens�o ficheiro', 'Autorizo streams n autorizados', 'Incluir cabe�alhos', 'Javasripts externos', 'Homepage', 'Mostrar parte First IT d� ', 'Mostrar parte upgrade', 'Mostrar estat�sticas', 'Escrever ID3v2 com stream', 'Habilitar assinatura dos utilizadores', 'Tipo de ficheiros', 'Sim', 'N�o', 'Extens�o', 'MIME', 'Inclui em MP3U', 'editar tipo ficheiro', 'Certeza ?', 'Optimistic filecheck', 'Aleatorizar', 'Modo', 'Playlist', 'Nenhum, directamente', 'Meus favoritos', 'N�o encontrado', 'All-time hits', 'Ordem', 'Abilitar suporte LAME', 'Desabilitado', 'Autorizo uso de LAME', 'Email', 'Autorizar correio de ficheiros', 'Servidor SMTP', 'Porta SMTP', 'Mail to', 'Mensagem', 'Enviar', 'Correio enviado', 'Activar Upload', 'Directoria de Upload', 'Activar correio mp3', 'Upload', 'Ficheiro enviado!', 'O ficheiro n�o p�de ser enviado!', 'Deve habilitar os cookies para login!', 'Periodo', 'sempre', 'esta semana', 'este m�s', 'ultimo m�s', 'buscas', 'comando LAME', 'Mostrar capa do album', 'Fcheiros Album', 'Redimensionar imagens Album', 'Altura album', 'Largura album', 'M�todo correio', 'Directo', 'Pear', 'Espere!!', 'Por favor entre um e-mail v�lido!', 'Playlists inline?', 'Mostrar album do URL?', 'URL do album', 'N�o pude enviar!', 'Utilizador adicionado!', 'Criador do arquivo', 'Arquivo foi apagado.', 'Utilizador foi actualizado.', 'Musica condiz', '%1 entradas filtradas', 'Acesso de Log', 'Visualiz�vel', 'Arquivado', 'Not�cias', 'Escrito %1 by %2', 'mais', 'Publicado', '%1 mb', '%1kb', '%1 bytes', 'Recursivo', 'Anterior', 'Pr�ximo', 'Ir p�gina %1', 'P�gina', 'Nunca ouvido', 'Aprovar manualmente assinaturas', 'Pendente', 'activar', 'Campos com * Obrigat�rios', 'A sua conta ser� inspeccionada e activada', 'maualmente', 'lembrar-me', 'Estilo', 'encontrar', 'Entre caminhos para procurar', 'Usar seleccionado??', 'Track time min/max', 'Minutos', 'm3u', 'asx (WMA)', 'Se o update parar, click aqui: %1', 'Seguir symlinks?', 'Template apresenta��o', 'Habilitar URL seguran�a', 'Lista branca upload', 'Tipo ficheiro n�o autorizado.!', 'Playlist vazia', 'Letras', 'URL letras', 'Mostrar atalho letras??', '(ou?)', 'Utilizador ou password desconhecido', 'Upload Max %1', 'Abrir fontes RSS p�blicas?', 'Por favor entre password', 'Preciso nome e login!!', 'Utilizador j� em uso!!', 'Desistir do acesso de Admin??', 'Fetching database records: %1/%2', 'N�o foi encontrado"%1",foi apagado?', 'De/at� data (DDMMYY)', 'Erro na entrada de campo(s),tente novamente.', 'Extens�o max de texto', 'Colunas Dir', 'Novo template', 'Template', 'Nome Template', 'Necess�rio Nome de template!!', 'Default signup template', 'Extractor de etiqueta', 'Autorizar uso de arquivos', 'Tamanho max arquivos(mb)', 'Arquivo excede o tamanho m�ximo! (%1mb, max is %2mb)', 'Directorio principal', 'Force LAME rate', 'Transcode', 'httpQ', 'Erro ao  contactar httpQ server (%1).', 'Usar cache na database', 'Registos n�o usados n�o foram apagados devido a ignorar.', 'tamanho', 'Ouvir musica', 'Vista da lista:', 'Num max vista detalhada', 'Efectiva', 'Detalhada', 'AJAX Prototype URL', 'Radio', 'Sem Fim', 'Desculpe mas huve problemas de log in.', 'Demo', 'Sincronizando %1 com %2 entradas', 'Estado do Servidor %1: %2', 'Actualiza��o do Servidor %1/%2', 'Escolha Subnivel: %1', 'Nivel actual: %1');

$klang[7] = array('Finnish', 'ISO-8859-1', 'Suomi', 'Suosituimmat', 'Uusimmat', 'Etsi', '(pelk�st��n %1 n�ytet��n)', 'sek', 'Haku-tulokset: \'%1\'', 'l�ytyi', 'Tyhj�.', 'p�ivit� hakutietokannan asetukset', 'Poista k�ytt�m�tt�m�t tiedot?', 'Uudelleenrakenna ID3?', 'Debug-moodi?', 'P�ivit�', 'Peruuta', 'p�ivit� hakutietokanta', 'L�ytyi %1 tiedostoa', 'Ei voinut m��ritt��: %1, skipattu.', 'Install %1 - P�ivit�: %1,  tarkistus:', 'Skannaus:', 'Ep�onnistui - haku: %1', 'Ei voinut lukea t�t� tiedostoa: %1. Skipattu.', 'Poistettu: %1', 'Sy�tetty %1, p�ivitetty %2, poistettu %3, miss� %4 ep�onnistui ja %5 skipattiin %6 tiedostosta - %7 sekuntia - %8 merkitty poistettavaksi', 'Valmis.', 'Sulje', 'Mik��n ei vastannut: %1', 'kPlaylist Kirjautuminen', 'Albumilista artistille: %1', 'Pikavalinta: %1', 'Ei valittuina mit��n. Soittolistaa ei p�ivitetty', 'Soittolista p�ivitetty!', 'Takaisin', 'Soittolista lis�tty!', 'Muista p�ivitt�� sivu.', 'tunnus:', 'salasana:', 'Huomautus! T�m� ei ole julkinen sivu. Kaikki teot kirjataan yl�s', 'Kirjaudu', 'SSL vaaditaan kirjautumiseen.', 'Soita', 'Poista', 'Jaettu:', 'Tallenna', 'Hallitse soittolistaa: \'%1\' - %2 nimet', 'Muokkain', 'Selain', 'Valitse', 'J�rj.', 'Tila', 'Info', 'Poista', 'Nimi', 'Yhteens�:', 'Virhe', 'Toiminto valitussa:', 'J�rjestys:', 'muokkaa soittolistaa', 'Poista t�m� tulos', 'lis�� soittolista', 'Nimi:', 'Luo', 'Soita', 'Tiedosto', 'Albumi', 'Kaikki', 'Valitut', 'lis��', 'soita', 'muokkaa', 'uusi', 'Valitse:', 'Hallinta:', 'Soittolista', 'Pikavalinta numero', 'First ITin tuote:', '(tarkista p�ivityksien varalta)', 'Kotisivu', 'ainoastaan id3', 'albumi', 'biisi', 'artisti', 'Albumit artistin mukaan', 'katso', 'Jaetut soittolistat', 'K�ytt�j�t', 'Yll�pito', 'Mit� uutta', 'Suosituimmat', 'Kirjaudu ulos', 'Asetukset', 'Tarkasta', 'Oma', 'muokkaa k�ytt�j��', 'uusi k�ytt�j�', 'Kokonimi', 'Kirjaudu', 'Vaihda salasana?', 'Salasana', 'Kommentti', 'K�ytt�j�taso', 'On', 'Off', 'Poista k�ytt�j�', 'Kirjaa ulos k�ytt�j�', 'P�ivit�', 'Uusi k�ytt�j�', 'poista', 'kirjaa ulos', 'K�yt� EXT3MU-toimintoa?', 'N�yt� kuinka monta tulosta (suosittu/uusi)', 'Maksimi haku tulokset', 'Resetoi', 'Avaa hakemisto', 'Mene hakemistoon: %1', 'Imuroi', 'Avaa yl�kansio', 'Mene p��hakemistoon', 'Tarkista p�ivityksien varalta', 'k�ytt�j�t', 'Kieli', 'asetukset', 'Potkittu', 'Shuffle', 'Asetukset', 'Perushakemisto', 'Streamin l�hde', 'Oletuskieli', 'Windows systeemi', 'Vaadi HTTPS', 'Salli etsiminen', 'Salli imurointi', 'Istunto p��ttynyt', 'Ilmoita ep�onnistuneet kirjautumisyritykset', 'Hetki. Haen tiedostolistaa', 'Soittolistaa ei voitu lis�t�', 'Yll�pit�j�', 'Kirjaudu HTTPS:ll� vaihtaaksesi', 'Streaming moottori p��lle', 'Nimi', 'Artisti', 'Albumi', 'Kommentti', 'Vuosi', 'Raita', 'Tyyppi', 'ei asetettu', 'Maksimi imurointinopeus (kbps)', 'K�ytt�j�', '%1 minuuttia - %2 biisi�', '%1 kilobitti� %2 minuuttia', 'Musiikkityylilista: %1', 'Mene', ' %1d %2h %3m soittoaika %4 tiedostoa %5 mt', 'Ei soitettavia fileit�', 'Salasana vaihdettu!', 'Rekister�i', 'Ole hyv� ja tee valinta!', 'Mik� on p�ivitys?', 'Ohje painamalla t�st�', 'K�yt� ulkoisia kuvia?', 'Ulkoisten kuvien polku', 'Nykyinen salasana', 'Nykyinen salasana ei natsaa!', 'Valitse pakkaaja', 'Pakkausta ei pystytty tekem��n', 'Todenn�k�inen kopio: %1 - %2', 'Haluatko varmasti poistaa soittolistan?', 'Aakkosellinen', 'Satunnainen', 'J�rjest�', 'Alkuper�inen', 'K�yt� Javascripti�', 'Haluatko varmasti posistaa t�m�n k�ytt�j�n?', 'N�yt� historia', 'historia', 'Rivi�', 'Ulkopuolinen CSS tiedosto', 'Poista tuplat', 'OK', 'VIRHE', 'Stream', '(n�yt� tyyppin�)', 'tiedostot', 'albumit', '%1d %2h %3m %4s ', 'Yleist�', 'Muokkaa', 'Tiedostonk�sittely', 'Klikkaa ? ohjeen n�ytt�miseksi.', 'Automaattinen tietokanta-synkronisaation', 'L�het� tiedostop��te', 'Salli kirjautumattomat streamit', 'Sis�llyt� otsikot', 'Ulkopuolinen javascript', 'Kotisivu', 'N�yt� \'First IT toi sinulle\'-kohdan', 'N�yt� p�ivit� kohta', 'N�yt� statistiikka', 'Kirjoita ID3v2 streamiin', 'Salli k�ytt�jien rekister�inti', 'Tiedostotyypit', 'Kyll�', 'Ei', 'Tiedostop��te', 'MIME', 'Sis�llyt� M3U-tiedostoon', 'muokkaa tyyppi�', 'Varmistus?', 'Optimistinen tiedostotarkistus', 'Arpoja', 'Toimintatila', 'Soittolista', 'Ei mit��n, suoraan', 'Omat suosikit', 'Osumia ei l�ytynyt', 'Kaikkien aikojen parhaat', 'J�rjestys', 'LAME tuki p��lle', 'Pois', 'Salli LAMEn k�ytt�?', 'S�hk�posti', 'Salli tiedoston s�hk�postitus?', 'SMTP palvelin', 'SMTP portti', 'L�het� s�hk�posti', 'Viesti', 'L�het�', 'Viesti l�hetetty!', 'Aktivoi tiedoston lis�ys', 'Tiedoston lis�ys kansio', 'Aktivoi mp3mail', 'Lis�� tiedosto', 'Tiedosto lis�tty', 'Tiedoston lis�ys ei onnistunut!', 'Ev�steiden on oltava p��ll�, jotta sis��nkirjautuminen onnistuisi!', 'Ajanjakso', 'koskaan', 't�ll� viikolla', 't�ss� kuussa', 'edellisess� kuussa', 'osumia', 'LAME komento', 'N�yt� albumin kansi', 'Albumin tiedostot', 'Sovita albumin kuvien koko', 'Albumin korkeus', 'Albumin leveys', 'Postitusmuoto', 'Suora', 'Pear', 'Odota!', 'Anna oikea s�hk�postiosoite asetuksissa!', 'Soittolistat sisennettyin�?', 'N�yt� albumi URLista?', 'Albumin URL', 'L�hetys ei onnistunut!', 'K�ytt�j� lis�tty!', 'Arkiston luonti', 'Arkisto on poistettu.', 'K�ytt�j�n tiedot p�ivitetty!', 'Musiikin vertailu', '%1 rivi� seulottu', 'Loki', 'Tarkasteltavissa', 'Arkistoitu', 'Taulu', 'Kirjoitettu %1 %2:sta', 'lis��', 'Julkaise', '%1 mt', '%1 kt', '%1 tavua', 'Rekursiivininen', 'Edellinen', 'Seuraava', 'Sivulle %1', 'Sivu:', 'Ei kertaakaan soitettu', 'Hyv�ksy uudet tilit manuaalisesti', 'Odottaa', 'aktivoi', 'Kaikki kent�t merkittyin� * ovat pakollisia', 'Sinun tilisi tullaan tarkastamaan ja hyv�ksym��n manuaalisesti', 'Viimeisimm�t streamit', 'muista minut', 'Tyyli', 'etsi', 'Sy�t� hakemistot joista etsit��n', 'K�yt� valittua?', 'Raidan aika minimi/maksimi', 'Minuuttia', 'm3u', 'asx (WMA)', 'Jos p�ivitys pys�htyy, klikkaa t�st�: %1', 'Seuraa symlinkkej�?', 'Tiedoston esitys template', 'K�yt� ', 'L�het� whitelist', 'Tiedostotyyppi ei ole sallittu.', 'Soittolista on tyhj�!', 'Lyriikat', 'Lyriikoiden URL', 'N�yt� lyriikat-linkki', '(tai?)', 'Tuntematon k�ytt�j� tai salasana', 'Maksimi tiedostokoko: %1', 'Avaa julkinen RSS-feed?', 'Ole hyv� ja aseta salasana!', 'Tarvitsee nimen ja k�ytt�j�tunnuksen', 'K�ytt�j�nimi on jo k�yt�ss�!', 'Pudota admin-kyky t�lle istunnolle?', 'Haetaan tietueita: %1/%2', 'En l�yt�nyt "%1", onko tiedosto poistettu?', 'Mist�/mihin p�iv�m��r� (PPKKVV)', 'Virhe sy�tteess�, ole hyv� ja yrit� uudestaan.', 'Tekstin maksimipituus', 'Hakemisto-sarakkeita', 'Uusi pohja', 'Pohja', 'Pohjan nimi', 'Pohja tarvitsee nimen!', 'Oletus rekister�itymispohja', 'Tag-poimija:', 'Salli k�ytett�v�n archivereita', 'Arkiston maksimikoko (mt)', 'Arkisto ylitti maksimikoon! (%1mt, maksimi on %2mt)', 'Kotihakemisto', 'Pakota LAME-enkooderin rate', 'Transkoodaa', 'httpQ', 'Virhe yhteydess� httpQ serveriin (%1).', 'K�yt� tietokannan v�limuistia?', 'K�ytt�m�tt�m�tt�mi� tietueita ei poistettu skippien takia.', 'Pituus', 'Soita albumi', 'Listan�kym�:', 'Yksityiskohtaisten katseluiden maksimim��r�', 'Voimassa', 'Yksityiskohtainen', 'AJAX Prototyypin URL', 'Radio', 'Toisto', 'Pahoittelemme - kirjautumisessa oli ongelma.', 'Demo', 'Synkronoidaan %1 %2:sta', 'Verkon tila %1: %2', 'Verkkop�ivitys %1/%2', 'Valitse alitaso: %1', 'Nykyinen taso: %1');

$klang[8] = array('Danish', 'ISO-8859-1', 'Dansk', 'Hvad er hot', 'Hvad er nyt', 'S�g', '(kun %1 vist)', 'sek', 'S�geresultater: \'%1\'', 'fundet', 'Ingen.', 'indstillinger for opdatering af s�gebasen', 'Fjern slettede sange?', 'Genopbyg ID3?', 'Fejls�gnings mode', 'Opdater', 'Annuller', 'opdater s�gebasen', '%1 filer fundet.', 'Kunne ikke bestemme filtypen p�: %1. Droppet.', 'Installerer: %1 - Opdaterer: %2, scanner: ', 'Scan:', 'Fejl - foresp�rgsel: %1', 'Kunne ikke l�se: %1. Droppet.', 'Fjernet: %1', 'Der er indsat %1, opdateret %2, slettet %3, hvor %4 fejlede og %5 blev droppet. Ialt %6 filer - %7 sekunder - %8 markeret til sletning.', 'Gennemf�rt', 'Luk', 'Ingen filer fundet p�: "%1"', 'kPlaylist login', 'Albumliste for kunstner: %1', 'Hurtigvalg %1', 'Ingen numre valgt. Playlist ikke opdateret.', 'Playlist opdateret!', 'Tilbage', 'Playlist tilf�jet!', 'Husk at opdatere siden.', 'brugernavn:', 'adgangskode:', 'Bem�rk! Dette er en privat webside. Alt logges.', 'Log p�', 'SSL er kr�vet for at logge p�.', 'Afspil', 'Slet', 'Delt:', 'Gem', 'Kontroller playlisten: "%1" - %2 titler', 'Redigering', 'Vis', 'V�lg', 'Sekvens', 'Status', 'Info', 'Slet', 'Navn', 'Total:', 'Fejl', 'Handling p� valgte:', 'Sekvens:', 'rediger playlist', 'Slet dette nummer', 'tilf�j playlist', 'Navn:', 'Opret', 'Afspil:', 'Fil', 'Album', 'Alle', 'Valgte', 'tilf�j', 'afspil', 'rediger', 'ny', 'V�lg:', 'Afspil:', 'Playlist:', 'Numerisk hurtigvalg', 'First IT giver dig:', '(se efter opdateringer)', 'Webside', 'kun ID3', 'album', 'titel', 'kunstner', 'Hurtigvalg album fra kunstner', 'vis', 'Delte playlister', 'Brugere', 'Admin kontrolpanel', 'Hvad er nyt', 'Hvad er hot', 'Log ud', 'Indstillinger', 'Vis', 'Mig', 'rediger bruger', 'ny bruger', 'Fulde navn', 'Brugernavn', '�ndre adgangskode?', 'Adgangskode', 'Kommentar', 'Adgangsniveau', 'Online', 'Offline', 'Slet bruger', 'Log bruger ud', 'Opdater', 'Ny bruger', 'slet', 'logud', 'Anvend EXTM3U?', 'Vis r�kker (hotte/nye)', 'Max. antal i s�ger�kker', 'Nulstil', '�bn mappe', 'G� til mappe: %1', 'Download', 'G� et trin tilbage', 'G� til roden.', 'Se efter opdateringer', 'brugere', 'Sprog', 'indstillinger', 'Afvis', 'Tilf�ldig:', 'Indstillinger', 'Basemappe', 'Stream-lokation', 'Standardsprog', 'Windows underst�ttelse', 'HTTPS kr�ves', 'Tillad s�gning', 'Tillad download', 'Sessionsvarighed', 'Rapporter fejlagtige loginfors�g', 'Vent - skaber filliste', 'Playlisten kunne ikke tilf�jes', 'Admin', 'Log ind med HTTPS for at �ndre denne indstilling!', 'Aktiver streaming', 'Titel', 'Kunstner', 'Album', 'Kommentar', '�r', 'Nummer', 'Genre', 'ukendt', 'Max. download hastighed (kbps)', 'Bruger', '%1 minutter - %2 titler', '%1 kbit %2 minutter', 'Genreliste: %1', 'V�lg', 'Spilletid: %1d %2h %3m - %4 filer %5 mb', 'Intet relevant her.', 'Adgangskoden er �ndret!', 'Ny bruger', 'Foretag venligst en markering!', 'Hvad er en opdatering?', 'Klik her for hj�lp', 'Brug eksterne billeder', 'Sti til eksterne billeder', 'Nuv�rende adgangskode', 'Den nuv�rende adgangskode var forkert!', 'Foretrukne arkivtype', 'Arkivet kunne ikke genereres', 'Sandsynlig dublet fundet: "%1" - "%2"', 'Vil du virkelig slette playlisten?', 'Alfabetisk', 'Vilk�rlig', 'Sorter', 'Original', 'Brug javascript', 'Vil du virkelig slette brugeren?', 'Vis historie', 'historie', 'R�kker', 'Ekstern CSS-fil', 'Fjern dubletter', 'OK', 'FEJL', 'Stream', '(vis som)', 'filer', 'albums', '%1d %2t %3m %4s', 'Generelt', 'Tilpasning', 'Filh�ndtering', 'Klik p� ? for hj�lp.', 'Automatisk s�gebase synkronisering', 'Medsend filefternavn', 'Tillad uautoriseret streams', 'Inkluder headere', 'Ekstern javascript', 'Hjemmeside', 'Vis First IT giver dig', 'Vis opdateringsdelen', 'Vis statistikker', 'Send ID3v2 med stream', 'Tillad nyregistrering af brugere', 'Filtyper', 'Ja', 'Nej', 'Filefternavn', 'MIME', 'Inkluder i M3U', 'rediger filtype', 'Er du sikker?', 'Optimistisk filcheck', 'Randomiser', 'Mode', 'Playlist', 'Ingen, direkte', 'Mine favoritter', 'Ingen hits fundet', 'Alle hits', 'R�kkef�lge', 'LAME underst�ttelse?', 'Slukket', 'Tillad LAME?', 'Email', 'Tillad sending af filer?', 'SMTP server', 'SMTP port', 'Mail til', 'Besked', 'Send', 'Mail sendt!', 'Tillad upload', 'Uploadmappe', 'Tillad mp3mail', 'Upload', 'Fil uploadet!', 'Filen kunne ikke uploades!', 'Cookies er p�kr�vet!', 'Periode', 'nogensinde', 'denne uge', 'denne m�ned', 'sidste m�ned', 'hits', 'LAME kommando', 'Vis albumcovers', 'Album filer', '�ndre cover st�rrelse', 'Cover h�jde', 'Cover bredde', 'Mail metode', 'Direkte', 'Pear', 'Vent!', 'Udfyld en gyldig emailadresse i indstillingerne!', 'Playlist inline?', 'Vis album fra URL?', 'Album URL', 'Kunne ikke sende!', 'Bruger tilf�jet!', 'Arkiv skaber', 'Arkivet er slettet.', 'Brugeren opdateret!', 'Musik match', '%1 gennems�gt', 'Log adgang', 'Vises', 'Arkiveret', 'Opslagstavle', 'Skrevet %1 af %2', 'mere', 'Udgiv', '%1 mb', '%1 kb', '%1 bytes', 'Rekursivt', 'Forrige', 'N�ste', 'G� til side %1', 'Side:', 'Aldrig afspillet', 'Manuel godkendelse af nye brugere', 'Under behandling', 'aktiver', 'Alle felter markeret med * er obligatoriske', 'Din konto vil blive inspiceret og godkendt manuelt.', 'Seneste afspilninger', 'husk mig', 'Stil', 'find', 'Sti at s�ge efter', 'Benyt valgte?', 'Track tid min/max', 'Minutter', 'm3u', 'asx (WMA)', 'Hvis opdatingen stopper, klik her: %1', 'F�lg symlinks?', 'Fil template', 'Aktiver URL sikkerhed', 'Upload whitelist', 'Filtype ikke tilladt.', 'Playlisten er tom!', 'Sangtekst', 'Sangtekst URL', 'Vis link til sangtekster?', '(eller?)', 'Ukendt brugernavn eller adgangskode', 'Max upload st�rrelse: %1', 'Aktiver offentlig RSS feed?', 'S�t et password', 'Navn og login mangler', 'Brugernavnet findes allerede!', 'Afgiv admin funktioner for denne session?', 'Henter database r�kker: %1/%2', 'Kunne ikke finde "%1", er filen slettet?', 'Fra/til dato (DDMM��)', 'Fejl i felt(er), pr�v igen.', 'Maks tekstl�ngde ved listevisning', 'Listevisnings kolonner', 'Ny skabelon', 'Skabelon', 'Skabelonnavn', 'Mangler et skabelonnavn!', 'Standard registreringsskabelon', 'Tag udtr�k:', 'Tillad brug af arkivv�rkt�jer', 'Maksimal arkivst�rrelse (mb)', 'Arkivst�rrelse overskred det tilladte! (%1mb, maks. er %2mb)', 'Hjemmebibliotek', 'Tvungen LAME rate', 'Transcode', 'httpQ', 'Fejl ved kontakt til httpQ server (%1).', 'Brug database cache?', 'Ubenyttede r�kker blev ikke slettet grundet skip.', 'L�ngde', 'Afspil album', 'Listevisning', 'Maks. antal i detaljeret visning', 'Effektivt', 'Detaljeret', 'AJAX Prototype URL', 'Radio', 'Loop');

$klang[9] = array('Russian', 'Windows-1251', '�������', '����������', '�����', '�����', '(������ %1 �������)', '���.', '��������� ������: "%1"', '�������', '�� ����.', '�������� ��������� ������ ���� ������', '������� �������������� ������ � ����?', '����������� ID3?', '����� �������?', '�������� ����', '������', 'O������� ���� ������ ������', '������� %1 ������.', '�� ���� ���������� ���� ����: %1, ���������.', '���������: %1 - ���������: %2, �����������: ', '�����������: ', '������ - ������: %1', '�� ���� ��������� ���� ����: %1. ��������.', '�������: %1', '��������� %1, ��������� %2, ������� %3, �� ��� %4 ������ � %5 ���������. ����� %6 ������ - %7 ���. - %8 ���������� ��� ��������.', '������', '�������', '�� ������� �� ������ �����: "%1"', 'kPlaylist. ����', '������ �������� �����������: %1', '������� ����� %1', '�� ������� �� ����� ����������. �������� �� �������.', '�������� �������!', '�����', '�������� ��������!', '�� �������� ������������� ��������.', '�����:', '������:', '��� �������� ������������� ������������.', '�����', '��� ����� ��������� SSL', '���������', '�������', '��������� ������������: ', '���������', '���������� ����������: "%1" - %2 ����������', '�������������', '��������', '�������', '������.', '���������', '����������', '����.', '���', '�����:', '������', '�������� � ��������: ', '������������������:', '������������� ��������', '������� ��� �������', '�������� ��������', '���:', '�������', '���������: ', '����', '������', '���', '���������', '��������', '���������', '�������������', '�����', '�������:', '���������� �������������: ', '��������: ', '������� ����� �� �����', 'First IT �������� ���:', '(��������� ����������)', '�������� ��������', '������ �  id3', '������', '��������', '�����������', '������� �� ��������', '��������', '����� ���������', '������������', '�����������������', '�������', '����������', '�����', '���������', '��������', '��� ���������', '������������� ������������', '����� ������������', '������ ���', '�����', '�������� ������?', '������', '�����������', '������� �������', '���', '����', '������� ������������', '��������� ������������', '��������', '����� ������������', '����.', '�����', '������������ EXTM3U?', '���������� ��������� ����� (����������/�����)', '���������� ��������� ����� ��� ������', '�����', '����� � �����', '������� � �����: %1', '�������', '����� �� ���� �������', '� ������', '��������� ����������', '�������������', '����', '���������', '�������������!', '��������� �������:', '���������', '���� � ������������ ������', '���� ��� ���������� �������', '���� �� ���������', '������ ��� Windows', '��������� HTTPS', '��������� �����������', '��������� ���������', '������� ��� ������', '�������� � ��������� �������� �����', '��������� - ���������� �� ������� ������', '�������� �� ����� ���� ��������!', '�����������������', '���� ������ ����� HTTPS', '�������� ���������� ������� ���������� �������', '��������', '�����������', '������', '�����������', '���', '����', '�����', '�� ����������', '������������ �������� ���������� (kbps)', '������������', '%1 ���. - %2 ������', '%1 kbit %2 ���.', '������ ������: %1', '���������', '� ���� %4 ������ ����� ������� %5 ��.<br> ����� ����� �������������: %1 ���� %2 ����� %3 �����.', '����������� ����� �����������.', '������ �������', '�����������', '�������� ���� �� ���� ����', '��� ����� ����������?', 'ٸ������ ����� ��� ���������', '������������ ������� ��������?', '���� � ���������', '������� ������', '�������� ������ �� ��������� � �������!', '������������ ���������', '���������� ������� �����', '������� ��������� ��������� ������:  "%1" "%2"', '�� ������������� ������ ������� ��������?', '�� ��������', '��������', '�����������', '��� � ���������', '������������ JavaScript', '�� ������������� ������ ������� ����� ������������?', '�������� �������', '�������', '������', '���� CSS', '������� ���������', 'OK', 'ERR', '�����', '(�������� ���)', '�����', '�������', '%1 ���� %2 ����� %3 ����� %4 ������', '�����', '���������', '������ � �������', 'ٸ������ �� ? ��� ���������.', '�������������� ���������� ���� ������', '�������� ���������� ����� ��� ��������', '��������� �������� ���������� ����� ��� �����������', '�������� ���������', '������� JavaScript', '����� ������ �����', '���������� ����� "First IT �������� ���"', '���������� ����� "��������� ����������"', '���������� ����������', '��������� ��� ID3v2 � �����', '��������� ����������� �������������', '���� ������', '��', '���', '����������', 'MIME', '�������� � M3U', '������������� ��� �����', '�������?', '������ �������� ������', '��������� �������', '����� �������', '�������� � ��������', '�� ���������', '�� ����� ����������', '���������� ���������� �� �������', '����� ����������', '����������', '��������� ��������� LAME?', '���������', '��������� ������������� LAME?', 'Email', '��������� �������� ������ �� Email\'�?', 'SMTP ������', 'SMTP ����', '����������', '���������', '��������', '������ ��������!', '������������ �������� �� ������', '���������� ��� ��������', '������������ mp3�����', '���������', '���� ��������!', '���� �� ��������!', '���� ������ ���� ��������', '����������', '�����', '�� ���� ������', '� ���� ������', '� ������� ������', '����', 'LAME �������', '���������� ������� ��������', '������� ��������', '�������� ������� �������', '������ �������', '������ �������', '������ ������� �����', '������', 'Pear', '�����', '���������� ������� � ������ ���������� ������ ��������� �����!', '�������� "inline"', '���������� ������� � ��������-�������?', '����� ��� �������', '�� �������!', '������������ ��������!', '�������� ������', '����� ������.', '������������ ���������������', '������', '%1 ������������', '��� �������', '���������', '� ������', '����� ����������', '���� %1 �� %2', '�������� ���', '����������', '%1 ��', '%1 ��', '%1 �', '�����������', '����������', '���������', '�� �������� %1', '��������:', '������� �� ������������', '������ ���������� � ������', '�������������', '��������', '���� ���������� ��������� (*) �����������', '��� ������� ����� �������� � ������������� �����.', '��������� ������', '��������� ����', '�����', '�����', '������� ���� ��� ������', '������������ ��������� ?', '����� ����� ������������/�����������', '�����', 'm3u', 'asx (WMA)', '���� ���������� �����������, ������� �����: %1', '������ �� ���������� ?', '������ �����', '��������� ������������ URL', '��������� ���������������� ������', '�� ����������� ��� �����.', '�������� ���� !', '������ ����� ����� �� Lyrics.com', '������ �� Lyrics', '���������� Lyrics ?', '(���?)', '�� ���������� ������������ ��� ������', '�����������  ����������� ������: %1', '������� RSS?', '���������� ������� ������!', '���������� ��� � �����', '����� ������������ ��� ���������������!', '������ ������ �������������� � ������?', '������ ������ �� ����: %1/%2', '�� ���� ����� "%1", ���� �����?', '���� �/�� (��.��.��)', '������ ��� ���������� ����(-��), ���������� ��� ���', '������������ ����� ������', '���������� �������', '����� ������', '������', '��� �������', '���������� ������ ��� �������!', '������ �� ���������', '���������� �����', '��������� ������������ ���������', '������������ ������ ������ (��)', '����� ��������� ����������� ������! (%1��, �������� ��������� %2��)', '�������� �����', '������� LAME �����', '�����������������', 'httpQ', '������ ��� ���������� � httpQ �������� (%1).', '������������ ��� �� ���� ������?', '�� �������������� ������ ��������� � �� �������.', '�����', '������ ������', '��������� ���:', '����. ���-�� ���������� ����', '�����������', '���������', 'AJAX Prototype URL', '�����', '������', '��������. �������� �������� � ������������', '����', 'Synchronizing %1 with %2 entries', 'Network status %1: %2', 'Network update %1/%2', 'Choose sublevel: %1', 'Current level: %1');

$klang[10] = array('Swiss German', 'ISO-8859-15', 'Schwiizerd�tsch', 'Wasch geil', 'Wasch neu', 'Wo isch das Z��g', '(Gseesch nur  %1)', 'sek', 'Suechergebnis: \'%1\'', 'gfunde', 'keini', 'pass das datebank-suech-z��g aa', 'n�d benutzte seich i de db kick� ?', 'ID3 erneuer�?', 'Dib�g-Modus?', 'Update', 'Abbr�che', 'Suech-DB update', '%1 Files gfund�', 'Bin bi dem File n�d druus cho: %1. Has usglaa.', 'Inschtalliert:%1 - Draa umebaschtlet: %2, abchecke:', 'sc�n:', 'Problem bi de Abfrag: %1', 'Han glaub es File verh�eneret: %1. Ussglaa..', 'Weggnoo: %1', 'inetaa: %1, umebaschtlet: %2, weggnoo: %3, %4 h�nd n�d gfunzt und %5 hani ussglaa; %6 dateie insgesamt - %7 sekunde - %8 hani markiert zum abtsch�sse.', 'Schnornig.', 'Zuemache.', 'Da h�tts kei Dateie: "%1"', 'KPlaylist Login', 'Albumlischte f�r Interpret: %1', 'Churzwahl %1', 'Kein Song usgw�hlt. Playlischte n�d aktualisiert.', 'Playlischte aktualisiert.', 'Zrugg', 'Playlischte zuegf�egt!', 'Nomal lade das z��g.', 'Login:', 'Passwort:', 'Achtung! Dasch privat da z��g. Jede seich gitt eis uf de Deckel!', 'Login', 'Bruchsch SSL zum inechoo', 'Abschpile', 'L�sche', 'Die wommer z�me h�nd:', 'Seiv�', 'A de Playlischte umebaschtle: "%1" - %2 Titel', 'Editor', 'Aazeiger', 'Usw�hle', 'Nummer�', 'Schtatus', 'Info', 'Abtsch�sse', 'Nam�', 'Z�mezellt', 'Sch�ne seich', 'Das machemer mit dene wo uusgw�hlt sind', 'Reiefolg', 'a de Playlischte umebaschtle', 'De Iitrag useschmeisse', 'Playlischte dezuetue', 'Nam�:', 'Mache', 'Abschpile:', 'Datei', 'Album', 'Ali', 'die Uusgw�hlte', 'Dezue tue', 'Abschpile', 'draa umebaschtle', 'neu', 'Usw�hle:', 'Abschpile:', 'Playlischte:', 'Churzwahl numerisch', 'First IT pr�sentiert eu:', '(Suche nacheme neue versi�nli)', 'Houmpeitsch', 'Nume id3 T�gZ', 'Album', 'Titel', 'Interpret', 'Churzwahl Album nach Interpret', 'Aasicht', 'Playlischtene, wommer z�me h�nd', 'Benutzer', 'Admin kontroll�', 'Wasch neu', 'Wasch geil', 'Und tsch�ss', 'Iischtellige', 'Abtsch�gge', 'Mini', 'Benutzer ab�ndere', 'Neue Benutzer', 'De ganz Name', 'Login', 'Passwort ab�ndere?', 'Passwort', 'S�nf dezue gee', 'Wie m�chtig isch de Typ', 'Aagschtellt', 'Abgschtellt', 'Benutzer abtsch�sse', 'Uuslogge', 'Erneuere', 'Neue Benutzer', 'L�sche', 'Uuslogge', 'S�li das EXTM3U z��g bruuche?', 'Wivill ziile aazeige (geil/neu)', 'Max. Ziile bi Suechergebnis', 'Reset', 'Ordner ufmache', 'Gang zum Ordner: %1', 'Abesuuge', 'Ein Ordner ufe', 'Is Grundverzeichnis', 'Mal luege �bs es Update gitt', 'Benutzer', 'Spraach', 'Opzione', 'Aaghalte', 'Mischle:', 'Iischtellige', 'Hauptverzeichnis', 'Stream location', 'Standardspraach', 'Es windoof-system', 'bruucht HTTPS', 'd�rf me sueche', 'd�rf me suuge', 'session isch abgloffe', 's�g mer, wenn eine sis PW verh�ngt', 'mom�ntli, mues schn�ll go d\'files l�se', 'han die bl��d playlist n�d ch�ne mache!', 'Admin', 'Login mit HTTPS zum �ndere', 'streaming maschine ihschalte', 'Titel', 'Artischt', 'Album', 'Kommentar', 'Johr', 'Track', 'Stiil', 'n�d', 'Max abesuug rate (kbps)', 'Benutzer', '%1 min - %2 titel', '%1 kbit %2 min', 'Stiil Lischte: %1', 'Gang', 'Spiilziit: %1d %2h %3m : %4 dateie : %5 mb', 'Da h�tts kei wichtigi sache.', 'Passwort g�nderet', 'Regischtriere', 'W�hl bitte �ppis us!', 'Was isch update?', 'da klicke f�r hilf', 'externi bilder bruche?', 'externe bilder ort', 'jetztigs passwort', 'jetztigs passwort stimmt n�d �berih!', 'bevorzugte archivierer', 'has archiv n�d ch�ne erstelle!', 'm�glichs doppel gfunde: "%1" "%2"', 'playliste w�rkli l�sche?', 'Alphabetisch', 'Durenand', 'Sortiere', 'Originau', 'Bruch Javascript', 'Bisch sicher das d� User willsch l�sche?', 'Zeig d\'history', 'history', 'Reihe', 'Externs CSS file', 'Entfern doppleti', 'OK', 'ERR', 'Stream', '(zeig als)', 'dateie', 'albene', '%1d %2h %3m %4s', 'Generel', 'individualisier�', 'Dateihandling', 'Clickuff? f�r hilf', 'Automatische datebank synch', 'Schick d\'datei erwiiterig', 'unber�chtige stream erlaube', 'Adresschopf ihbinde', 'Externs Javascript', 'Homepeitsch', 'Show First IT gives you part', 'Zeig de upgrade teil', 'Zeig d\'statistik�', 'Schriib ID3v2 mit em Stream', 'Registrierig Ihschalte', 'Datei typ�', 'Jo', 'Nei', 'Erwiiterig', 'MIME', 'in M3U Ufn�h', 'dateityp ahpasse', 'Sicher?', 'Optimistische dateicheg', 'Zuefallsgenerator', 'Modus', 'Playlischte', 'Kei, dir�kt', 'Mini favoriit�', 'Han kei tr�ffer gfund�', 'Absolut-hits', 'Sortier�', 'LAME support Ihschalte?', 'Usgschalt�', 'LAME benutzig erlaube?', 'Email', 'datei z\'maile erlaube?', 'SMTP s�rver', 'SMTP port', 'Mail ah', 'Nachricht', 'Schick�', 'Mail gschickt!', 'ufelade aktivier�', 'Ufelad verzeichnis', 'mp3mail Aktivier�', 'Ufelade', 'Datei ufeglade!', 'Datei n�d ch�ne ufelade!', 'Du muesch d\'Cookies ihschalte zum ahm�lde!', 'Ziitruum', 'immer', 'die wuche', 'd� mon�t', 'letscht mon�t', 'tr�ffer', 'LAME comando', 'Zeig album cover', 'Album datei�', 'Gr�ssi vo de Album-bilder ahpass�', 'Album h�chi', 'Album breiti', 'Mail method�', 'Dir�kt', 'Pear', 'Warte!', 'Bitte tr�g en richtigi e-mail adr�sse i de optzione ih!', 'Playlischt� inline?', 'Zeigs alum vom URL?', 'Album URL', 'N�d ch�ne Schicke!', 'Benutzer dezue tah!', 'Archiv erz�ger', 'Archiv isch gl�scht.', 'Benutzer updait�t', 'Musig-tr�ffer', '%1 entries filtered', 'Log d\'zuegriff', 'Sichtbar', 'Archiviert', 'B�lt�h', 'Gschriib� %1 vo %2', 'meh', 'Ver�ffentlich�', '%1 mb', '%1 kb', '%1 bytes', 'Rekursiv', 'Vorh�rig', 'N�chscht', 'Gang zu de siit� %1', 'Sit�:', 'Ni� gspillt', 'Registri�rig� manuel best�tig�', 'usstehend', 'aktivier�', 'Alli F�lder mit em �  * sind zwingend', 'Diin account wird pr�eft und d�nn manuell aktiviert', 'Letschti streams', 'ah mich errin�r�', 'Stiil', 'find�', 'suechpf�d ihtr�ge', 'ahgw�hlte bruch�', 'Titel ziit min/max', 'Minut�', 'm3u', 'asx (WMA)', 'wenn d� update ahaltet, da klick�: %1', 'symlinks folg�?', 'Datei presentations Vorlaag', 'URL sicherheit ihschalte', 'Ufelad whitelist', 'Datei typ isch n�d erlaubt', 'Playlischt� isch leer', 'Lyrics', 'Lyrics URL', 'zeid d� lyrics link?', '(oder?)', 'Unbekannte benutzer oder passwort', 'Max ufelad gr�ssi: %1', 'oeffentliche RSS feed ufmache?', 'Bitte setz es Passwort', 'Brucht en name und es login', 'Username wird scho brucht', 'Die Admin session beende?', 'Hole d\'datebank ihtr�g: %1/%2', 'Could not find "%1", is file deleted?', 'Vo/bis datum (DDMMYY)', 'Fehler  bi(m) Ihgabefelder, bitte nomal probiere', 'Maximali text l�ngi', 'Verzeichniss reihe', 'Neus template', 'Vorlag', 'Vorlags name', 'bruch en Vorlags name!', 'Standard registrier template', 'Tag extrahierer:', 'Archivier(er) funktion erlaube', 'Maximali archiv gr�ssi', 'S\'Archiv h�tt die max. gr�ssi �berschritte! (%1mb, max is %2mb)', 'Home verzeichnis', 'LAME rate forciere', 'umschl�ssle', 'httpQ', 'Fehler bim konatktiere vom  httpQ server (%1).', 'datebank cache bruche?', 'n�d verwendeti ihtr�g werde n�d gl�scht bim �berspring�', 'L�ngi', 'Album abspielle', 'ufz�hligs ahsicht', 'Max ahzahl vo de detaillierte ahsichte', 'Effektiv', 'Detailiert');

$klang[11]  = array('French', 'ISO-8859-15', 'Fran�ais', 'Populaire', 'Nouveaut�s', 'Rechercher', '(seulement %1 visibles)', 'sec', 'R�sultats de la recherche : \'%1\'', 'trouv�', 'Aucun', 'actualiser les options de la base de donn�es de recherche', '<b>Supprimer</b> les entr�es inutiles ?', 'Reconstruire <b>ID3</b> ?', 'Mode de d�buggage ?', 'Actualiser', 'Annuler', 'Actualiser la base de donn�es de recherche', '%1 fichiers trouv�s', 'Ce fichier n\'a pas pu �tre d�termin� : %1, ignor�.', 'Install�s : %1 - Actualis�s : %2 - Scann�s : ', 'Scanner', 'Echec - Requ�te : %1', 'Le fichier : %1 n\'a pas �t� trouv�. Pass�.', 'Elimin�s : %1', 'Ins�r�(s) :%1, Actualis�s %2, Supprim�s : %3 dont %4 �chou�s et %5 ignor�s parmi %6 fichiers - %7 sec. - %8 marqu�s pour effacement.', 'Termin�', 'Fermer', 'Impossible de trouver des fichiers dans : "%1"', 'Nom d\'utilisateur KPlaylist', 'Liste des albums de l\'artiste : %1', 'Pl�biscit� %1', 'Aucune chanson s�lectionn�e. La liste n\'a pas �t� actualis�e.', 'Liste actualis�e avec succ�s !', 'Pr�c�dent', 'Liste ajout�e !', 'Pensez � actualiser la page.', 'Nom d\'utilisateur :', 'Mot de passe :', 'Attention ! Ce site est priv�, toute action est enregistr�e.', 'Se connecter !', 'SSL n�cessaire pour s\'identifier.', 'Lire', 'Effacer', 'Partag�e :', 'Enregistrer', 'Actions sur la liste : "%1" contenant %2 titres', 'Editeur', 'Viseur', 'S�lectionner', 'N� piste', 'Status', 'Informations', 'Supprimer', 'Nom du fichier', 'Totaux :', '<b>Erreur</b>', 'Action � effectuer sur la selection', 'Liste :', '�diter la liste', 'Supprimer cette entr�e', 'ajouter une liste', 'Titre :', 'Cr�er', 'Lire :', 'Fichier', 'Album', 'Tous', 'S�lectionn�s', 'ajouter', 'lire', '�diter', 'nouveau', 'S�lectionner :', 'Lire :', 'Liste :', 'S�lection num�rique', 'First IT vous propose :', '(rechercher des mises � jour)', 'Accueil', 'seulement id3', 'album', 'titre', 'artiste', 'Acc�der � un artiste', 'Voir', 'Listes partag�es', 'Utilisateurs', 'Console d\'administration', 'Nouveaux', 'Populaires', 'D�connecter', 'Options', 'Consulter les fichiers', 'Mon compte', '�diter un utilisateur', 'nouvel utilisateur', 'Nom complet', 'Nom d\'utilisateur', 'Changer le mot de passe ?', 'Mot de passe', 'Commentaires', 'Niveau d\'acc�s', 'On', 'Off', 'Supprimer l\'utilisateur', 'D�connecter l\'utilisateur', 'Actualiser', 'Nouvel utilisateur', 'supprimer', 'd�connecter', 'Utiliser l\'option de EXTM3U ?', 'Montrer combien de lignes (populaires/nouveaux)', 'R�sultat maximum de r�ponses', 'RAZ', 'Ouvrir le r�pertoire', 'Aller dans le r�pertoire : %1', 'T�l�charger', 'Dossier parent', 'Aller au r�pertoire racine', 'Chercher les mises � jour', 'utilisateurs ', 'Langue', 'options', 'D�sactiver le compte', 'Lecture al�atoire :', 'Config.', 'Chemin racine de la librairie musicale', 'Forcer l\'url du flux', 'Langue par d�faut', 'Syst�me de type Windows', 'HTTPS n�cessaire', 'Permettre la recherche', 'Permettre les t�l�chargements', 'D�lai d\'expiration de la session', 'Rapport des tentatives de connexion �chou�es', 'Patientez - Analyse de la librairie', 'La liste n\'a pas pu �tre ajout�e !', 'Admin', 'Connexion en HTTPS obligatoire', 'Activer le moteur de streaming', 'Titre', 'Artiste', 'Album', 'Commentaires', 'Ann�e', 'N� piste', 'Genre', 'n/a', 'Taux de t�l�chargement Max (kbps)', 'Utilisateur', '%1 min - %2 titres', '%1 kbit %2 min', 'Liste des genres : %1', 'Go', 'Temps de lecture : %1 J %2 H %3 m, %4 fichiers %5 Mo', 'Aucune ressource correspondante', 'Mot de passe mis � jour !', 'Inscrivez-vous !', 'Faites une s�lection SVP !', 'Qu\'est ce que la mise � jour ?', 'Clickez ici pour l\'aide', 'Utiliser des images externes ?', 'Chemin vers les images externes', 'Mot de passe actuel', 'Mauvais mot de passe', 'Archiveur pr�f�r�', 'Impossible de cr�er l\'archive', 'Doublon probable : "%1" "%2"', 'Voulez-vous vraiment supprimer la liste ?', 'Alphab�tique', 'Al�atoire', 'Classer', 'Original', 'Utiliser Javascript', 'Voulez vous vraiment supprimer cet utilisateur ?', 'Voir l\'historique', 'historique', 'Lignes', 'Fichier CSS externe', 'Supprimer les doublons', 'OK', 'ERREUR', 'Flux', '(afficher par)', 'fichiers', 'albums', '%1J %2H %3m %4s', 'Principal', 'Personnalisation', 'Gestion de la librairie', 'Cliquer sur "?" pour afficher l\'aide', 'Synchronisation automatique de la base de donn�es ', 'Envoyer les extensions de fichiers', 'Accepter les flux interdits', 'Inclure les en-t�tes', 'Javascript externe', 'Accueil', 'Afficher "First IT vous propose :"', 'Afficher "rechercher des mises � jour"', 'Afficher les statistiques', 'Ecrire les ID3v2 dans le flux', 'Ouvrir les inscriptions aux utilisateurs', 'Types de fichiers', 'Oui', 'Non', 'Extensions', 'MIME', 'Inclure dans le M3U', 'Editer les types de fichiers', '�tes-vous s�r ?', 'Analyse optimale des fichiers', 'Playlist Aleatoire', 'Mode', 'Liste de lecture', 'Aucune, lire directement', 'Mes favoris', 'Aucun fichier trouv�', 'Les plus �cout�s', 'Ordre', 'Activer le support de LAME ?', 'D�sactiv�', 'Autoriser l\'utilisation de LAME ?', 'eMail', 'Autoriser l\'envoi de fichiers par e-mail ?', 'Adresse du serveur SMTP', 'Port du serveur SMTP', 'Destinataire', 'Message', 'Envoyer', 'E-mail envoy� !', 'Activer l\'envoi de fichiers upload', 'R�pertoire pour les envois upload', 'Activer mp3mail', 'Envoyer un fichier', 'Fichier envoy� !', 'Impossible d\'envoyer le fichier !', 'Vous devez autoriser les cookies pour vous connecter !', 'P�riode', 'depuis le d�but', 'cette semaine', 'ce mois-ci', 'le mois dernier', 'requ�tes', 'Commande LAME', 'Afficher la couverture de l\'album', 'Fichiers de l\'album', 'Redimensionner les images de l\'album', 'Hauteur de l\'album', 'Largeur de l\'album', 'M�thode d\'envoi d\'eMail', 'Direct', 'Pear', 'Patientez !', 'Veuillez saisir une adresse e-mail valide dans les options !', 'Listes inline ?', 'Afficher l\'album depuis l\'url ?', 'Url de l\'album', 'Impossible de l\'envoyer !', 'Utilisateur ajout� !', 'Cr�ateur de l\'archive', 'L\'archive a �t� supprim�e.', 'Mis � jour', 'Similitudes', '%1 entr�es filtr�s', 'Traces des op�rations(log)', 'Visible', 'Archiv�', 'Bulletin', 'Ajout� le %1 par %2', 'plus', 'publier', '%1 MegaOctet', '%1 KiloOctet', '%1 Octet', 'R�cursif', 'Pr�c�dent', 'Suivant', 'Aller � la page %1', 'Page : ', 'Jamais jou�', 'Approuver manuellement les inscriptions', 'En attente', 'activer', 'Tous les champs avec un * sont obligatoires', 'Votre compte sera examin� et activ� manuellement.', 'Derni�res �coutes', 'Se souvenir de moi', 'Style', 'trouver', 'Entrer les chemins de recherche pour', 'Utiliser les selectionn�s ?', 'Dur�e de la piste mini/maxi', 'Minutes', 'm3u', 'asx (WMA)', 'Si la mise � jour s\'arr�te, cliquez ici : %1', 'Suivre les liens ?', 'Fichier mod�le', 'Activer les URL s�curis�s', 'Liste blanche des uploads', 'Type de fichier non autoris�', 'La liste de lecture est vide !', 'Paroles', 'URL des paroles', 'Montrer les liens des paroles ?', '(ou?)', 'Utilisateur ou mot de passe inconnu', 'Taille maximale d\'envoi : %1', 'Feed RSS publique ?', 'Veuillez entrer un mot de passe !', 'Identifiant et mot de passe n�cessaires', 'Le nom d\'utilisateur existe d�j� !', 'Supprimer l\'acc�s � l\'admin pour cette session ?', 'R�cuperation des donn�es : %1/%2', 'Impossible de trouver "%1", le fichier est peut-�tre supprim� ?', 'Date, depuis/jusqu\'au (JJMMAA)', 'Erreur dans le formulaire, merci de re�ssayer', 'Taille de texte maximum', 'Colonne des dossiers', 'Nouveau template', 'Template', 'Nom du template', 'Nom de template requis !', 'Template d\'inscription par d�faut', 'Extracteur de tag : ', 'Autoriser l\'utilisation de l\'archiveur', 'Taille maximum de l\'archive (Mo)', 'La taille de l\'archive a d�pass� la taille maximum ! (%1Mo; Max : %2Mo)', 'Chemin de d�part', 'Force LAME rate', 'Transcode', 'httpQ', 'Une erreure est apparue en contactant le serveur httpQ (%1)', 'Utiliser le cache en base?', 'Les enregistrements non utilis�s ne seront pas supprim�s.', 'Dur�e', 'Jouer l\'album', 'Voir la liste en cours', 'Nombre maximun de vue d�tail�e', 'Effectif', 'D�tail�', 'AJAX Prototype URL', 'Radio', 'Al�atoire', 'D�sol� - Il y\'a un probl�me de connexion.', 'Demo', 'Synchronisation %1 avec %2 entr�e', 'Statut de la connexion %1: %2', 'Mise � jour de la connexion %1/%2', 'Choisissez un sous-niveau : %1', 'Niveau courant : %1');

$klang[12] = array("Indonesian", "ISO-8859-1", "Indonesia", "Yang Ter-Hot", "Yang Terbaru", "Cari", "(hanya %1 tampilan)", "dtk", "Hasil Pencarian: '%1'", "ditemukan", "Kosong", "Opsi update pencarian database", "Hapus record tdk terpakai", "Bangun Ulang ID3?",  "Mode Debug ?", "Update", "Batal", "update pencarian database", "ada %1 file", "Tipe file tdk ada: %1, abaikan.", "Terinstall: %1 - Update %2, scan:", "Scan:", "Gagal - query: %1", "File %1 tdk terbaca, Abaikan", "Menghapus: %1",  "Tambah %1, Ubah %2, Hapus %3 dimana %4 gagal dan %5 abaikan bila %6 file - %7 detik - %8 dipilih utk dihapus.", "Selesai", "Tutup", "File yang dicari tdk ada: \"%1\"", "Login kPlaylist", "Daftar album dengan artis: %1", "Hotselect %1", "Tdk ada pilihan, Playlist tdk terupdate", "Playlist ter-update!", "Kembali", "Playlist ditambah!",  "Ingatlah utk me-reload hal. ini", "Login:", "Password:", "Peringatan! Ini bukan web umum. Semua Aktifitas terekam disini.", "Login", "Butuh SSL untuk Login", "Putar", "Hapus", "Sharing:", "Simpan", "Playlist kontrol: \"%1\" - %2 judul",  "Editor", "Viewer", "Pilih", "Seq", "Status", "Info", "Hapus", "Nama", "Total:", "Error", "Action pd terpilih:",  "Sekuen", "Ubah Playlist", "Hapus entri ini", "Tambah playlist", "Nama", "Buat", "Putar:", "File", "Album", "Semua", "terpilih",  "tambah", "putar", "ubah", "baru", "Pilih:", "Kontrol:", "Playlist:", "Nomor HotSelect", "First IT Anda:", "(Cek Upgrade)", "Homesite",  "hanya id3", "album", "judul", "artis", "Hotselect Album dari Artis ", "lihat", "Playlist lainnya", "User", "Kontrol Admin", "Yang terbaru", "Yang Terhot", "Logout", "Opsi", "Cek", "Profil", "Ubah user", "User baru", "Nama Lengkap", "Login", "Ubah Password?", "Password", "Komentar",  "Level Akses", "On", "Off", "Hapus user", "Logout user", "Refresh", "User baru", "hapus", "logout", "Gunakan EXTM3U", "Tampilkan banyak baris (hot/baru)",  "Max. Baris pencarian", "Reset", "Buka direktori", "ke direktori: %1", "Download", "Naik keatas", "Ke direktori root", "Cek Upgrade", "User", "Bahasa", "Opsi",  "Bootd", "Acak:", "Seting", "Direktori base", "Lokasi stream", "Bahasa default", "System Windows", "Butuh HTTPS", "Boleh mencari", "Boleh dowload", "Batas session",  "Report gagal login diperlukan", "Hold on - fetching file list ", "Playlist tdk bisa ditambah!", "Admin", "Login dengan HTTPS untuh mengganti!");

$klang[13] = array('Italian', 'ISO-8859-1', 'Italiano', 'Cosa c\'� di fico', 'Cosa c\'� di nuovo', 'Ricerca', '(soltanto %1 visibile)', 'sec', 'risultato della ricerca: \'%1\'', 'trovato', 'nessuno.', 'aggiona opzioni ricerca nel database', 'Cancella records non utilizzati?', 'Ricostruisci ID3?', 'modalit� di Debug?', 'Aggiorna', 'Annulla', 'aggiorna ricerca nel database', 'Trovati %1 files.', 'Impossibile determinare questo file: %1, saltato.', 'Installato: %1 - Aggiornato: %2, scansione:', 'Scansione:', 'Fallita - ricerca: %1', 'Impossibile leggere questo file: %1. Saltato.', 'Rimosso: %1', 'Inserito %1, aggiornato %1, cancellato %3, quando %4 � fallito e %5 saltato su %6 files - %7 secondi - %8 segnati per la cancellazione.', 'Fatto', 'Chiuso', 'Impossibile trovare files qui: "%1"', 'KPlaylist Login', 'Lista album per artista: %1', 'Hotselect %1', 'Nessuna canzone selezionata. Playlist non aggiornata.', 'Playlist aggiornata!', 'Indietro', 'Playlist aggiunta!', 'Ricorda di ricaricare la pagina.', 'login:', 'password:', 'Attenzione! Questo non � un sito pubblico. Tutte le azioni vengono registrate.', 'Login', 'SSL richiesto per l\'accesso.', 'Play', 'Cancella', 'Condiviso:', 'Salva', 'Controllo playlist: "%1" - %2 titoli', 'Editor', 'Visualizzatore', 'Selezione', 'Seq', 'Stato', 'Informazioni', 'Canc', 'Nome', 'Totale:', 'Errore', 'Azione da eseguire sulla selezione:', 'Sequenza:', 'Edita playlist', 'Cancella questa riga', 'aggiungi playlist', 'Nome:', 'Crea', 'Esegui:', 'File', 'Album', 'Tutto', 'Selezionati', 'aggiungi', 'play', 'modifica', 'nuovo', 'Selezione:', 'Controllo:', 'Playlist:', 'Selezione numerica', 'First IT vi propone:', '(controlla aggiornamenti)', 'Homepage', 'solo id3', 'album', 'titolo', 'artista', 'Seleziona album per artista', 'visualizza', 'Playlists condivise', 'Utenti', 'Controllo dell\'amministratore', 'Cosa c\'� di nuovo', 'Cosa c\'� di Hot', 'Esci', 'Opzioni', 'Controlla', 'Mio', 'modifica utente', 'nuovo utente', 'Nome completo', 'Login', 'Cambio Password?', 'Password', 'Commento', 'Livello d\'accesso', 'On', 'Off', 'Cancella utente', 'Uscita utente', 'Refresh', 'Nuovo utente', 'canc', 'Uscita', 'Usa opzione EXTM3U', 'Mostra quante righe (hot/nuove)', 'Righe massime da cercare', 'Reset', 'Apri directory', 'Vai alla directory: %1', 'Download', 'Sali di un livello', 'Vai al livello principale', 'Controlla per l\'aggiornamento', 'utenti', 'lingua', 'opzioni', 'Booted', 'Casuale:', 'Impostazioni', 'Directory iniziale', 'locazione brano', 'Lingua di default', 'Un sistema Windows', 'Richiede HTTPS', 'Permetti ricerca', 'Permetti download', 'timeout sessione', 'Riporta tentativi falliti di login', 'Aspetta - estrazione lista file', 'La playlist non pu� essere aggiunta!', 'Amministratore', 'Collegarsi tramite HTTPS per cambiare!', 'Abilita morore di streaming', 'Titolo', 'Artista', 'Album', 'Commento', 'Anno', 'Traccia', 'Genere', 'non settato', 'Limitazione download (kbps)', 'Utente', '%1 minuti - %2 titoli', '%1 kilobit %2 minuti', 'Lista generi: %1', 'Vai', '%1d %2h %3m playtime %4 files %5 mb', 'Nessuna risorsa.', 'Password cambiata!', 'Crea utente', 'Fai la tua selezione!', 'Cos\'� l\'update?', 'Aiuto', 'Usa immagini esterne?', 'Path immagini esterne', 'Password corrente', 'La passord corrente � sbagliata!', 'Archiver preferito', 'L\'archivio potrebbe non essere stato creato', 'Probabile file duplicato: %1 - %2', 'Eliminare la playlist?', 'Alfabetico', 'Random', 'Ordina', 'Originale', 'Usa javascript', 'Eliminare questo utente?', 'Guarda la history', 'history', 'Righe', 'File CSS Esterno', 'Rimuovi Duplicati', 'OK', 'Errore', 'Stream', '(mostra come)', 'files', 'album', '%1g %2h %3m %4s', 'Generale', 'Personalizza', 'Gestione Files', 'Clicca su ? per l\'aiuto', 'Sincronizzazione Automatica Database', 'Iniva estensione file', 'Consenti stream non autorizzati', 'Includi Header', 'Javascript Esterno', 'Homepage', 'Mostra First IT gives you part', 'Mostra parte upgrade', 'Mostra Statistiche', 'Scrivi ID3v2 con stream', 'Consenti registrazione utente', 'Tipi di files', 'S�', 'No', 'Estensione', 'MIME', 'Includi nell\'M3U', 'modifica tipo file', 'Sicuro?', 'Filecheck ottimistico', 'Casuale', 'Modalit�', 'Playlist', 'Niente, direttamente', 'I Miei Preferiti', 'Nessuna hit trovata', 'Hit di tutti i tempi', 'Ordina', 'Consentire supporto LAME?', 'Disabilitato', 'Consentire uso di LAME?', 'Email', 'Consentire invio files via email?', 'Server SMTP', 'Porta SMTP', 'Invia a', 'Messaggio', 'Invia', 'Mail Inviata!', 'Attiva Upload', 'Cartella Upload', 'Attiva mp3mail', 'Upload', 'File Caricato!', 'Il file non pu� essere caricato!', 'Devi avere i cookies abilitati per poter effettuare il login!', 'Periodo', 'mai', 'questa settimana', 'questo mese', 'ultimo mese', 'hits', 'Comandi LAME', 'Mostra copertina album', 'File Album', 'Ridimensiona immagini album', 'Altezza album', 'Profondit� album', 'Metodo Mail', 'Diretta', 'Pear', 'Attendi!', 'Digita un email valida nelle opzioni!', 'Playlist Inline?', 'Mostra album dall\'URL?', 'URL Album', 'Impossibile spedire!', 'Utente aggiunto!', 'Creatore Archivio', 'Archivio cancellato.', 'Utente aggiornato!', 'Trova musica', '%1 record filtrati', 'Log accessi', 'Visibile', 'Archiviato', 'Notizie', 'Entrati %1 su %2', 'altro', 'Pubblica', '%1 mb', '%1 kb', '%1 bytes', 'Ricorsivo', 'Precedente', 'Successivo', 'Vai a pagina %1', 'Pagina:', 'Mai ascoltato', 'Approva manualmente registrazioni', 'In attesa', 'attiva', 'Tutti i campi con * sono obbligatori', 'Il tuo account verr� controllato e attivato manualmente', 'Ultimi ascolti', 'ricordami', 'Stile', 'cerca', 'Digita i percorsi da cercare', 'Usa selezionato?', 'Traccia durata min/max', 'Minuti', 'm3u', 'asx (WMA)', 'Se l\'aggiornamento si ferma, clicca qui: %1', 'Seguire symlinks?', 'Formato file', 'Abilita sicurezza URL', 'Carica whitelist', 'Tipo di file non consentito.', 'La Playlist e\' Vuota!', 'Testi', 'URL testi', 'mostra URL testi?', 'o', 'username o password non corretti', 'Dimensione massima upload: %1', 'Apri un RSS feed pubblico?', 'scegli una password', 'necessari nome e login', 'L\'Username scelto gia\' e\' in uso', 'abbandona l\'accesso di amministratore per questa sessione?', 'ricevuti record database: %1/%2', 'Non trovo "%1",  e\' un file cancellato?', 'formato data (DDMMYY)', 'errore nel campo(i) prova ancora', 'lunghezza massima del testo', 'Dir colonne', 'Nuovo template', 'Template', 'nome Template ', 'necessario un nome Template!', 'Template predefinito', 'Tag extractor:', 'Allow using archiver(s)', 'Massima grandezza archivio (mb)', 'Archivio supera grandezza massima! (%1mb, max is %2mb)  ', 'dir. Home ', 'Forza LAME rate', 'Transcode', 'httpQ', 'Errore nel contattare il server httpQ (%1). ', 'Usa la cache del database?', 'Unused records were not deleted due to skips.', 'lunghezza', 'Suona album', 'vedi lista:', 'numero massimo di dettagli visti:', 'Effettivo', 'Dettaglio', 'AJAX Prototype URL', 'Radio', 'Loop', 'Spiacente - ci sono problemi nel tuo login.', 'Demo', 'Synchronizing %1 with %2 entries', 'Network status %1: %2', 'Network update %1/%2');

$klang[14] = array("Traditional Chinese [&amp;#12345]", "big5", "&#32321;&#39636;&#20013;&#25991;", "&#26368;&#29105;&#38272;", "&#26368;&#26032;", "&#25628;&#23563;", "(&#21482;&#26377; %1 &#31558;&#39023;&#31034;)", "&#31186;", "'%1' &#65306;&#25628;&#23563;&#32080;&#26524;", "&#25214;&#21040;", "&#27794;&#26377;", "&#26356;&#26032;&#25628;&#23563;&#36039;&#26009;&#24235;&#36984;&#38917;", "&#21034;&#38500; &#26410;&#29992;&#36942;&#30340;&#35352;&#37636;&#65311;", "&#37325;&#24314; ID3", "&#38500;&#34802;&#27169;&#24335;", "&#26356;&#26032;", "&#21462;&#28040;", "&#26356;&#26032;&#25628;&#23563;&#36039;&#26009;&#24235;", "&#25214;&#21040; %1 &#27284;&#26696;&#12290;", "&#30906;&#23450;&#19981;&#21040;&#27492; %1 &#27284;&#26696;&#65072; &#30053;&#36942;&#12290;", "&#24050;&#23433;&#35037;&#65072; %1 - &#26356;&#26032;&#65306; %2 &#65104; &#25475;&#30596;&#65306;", "&#25475;&#30596;&#65306;", "&#22833;&#25943; - &#21839;&#38988;&#65072; %1", "&#35712;&#19981;&#21040;&#27492; %1 &#27284;&#26696; &#65072;&#30053;&#36942;", "&#24050;&#31227;&#38500;&#65306; %1", "&#24050;&#25554;&#20837; %1 &#65292; &#24050;&#26356;&#26032; %2 &#65292; &#24050;&#21034;&#38500; %3&#65292; &#22320;&#40670; %4  &#22833;&#25943; &#21450; %6 &#27284;&#26696;&#20013;&#30053;&#36942;%5  - %7 &#31186; - &#24050;&#21034;&#38500; %8 &#26377;&#35352;&#34399;&#30340;&#27284;&#26696;", "&#24050;&#23436;&#25104;", "&#38359;&#38281;", "&#22312;&#27492;&#25214;&#19981;&#21040;&#20219;&#20309;&#27284;&#26696;&#65306; \"%1\"","kPlaylist &#30331;&#20837;", "&#27492;&#27468;&#25163;&#30340;&#23560;&#36655;&#28165;&#21934;&#65306; %1", "&#29105;&#36984; %1", "&#27794;&#26377;&#27468;&#26354;&#36984;&#25799;&#12290; &#25773;&#25918;&#28165;&#21934;&#27794;&#26377;&#26356;&#26032;&#12290;", "&#25773;&#25918;&#28165;&#21934;&#24050;&#26356;&#26032;&#65281;", "&#36820;&#22238;", "&#25773;&#25918;&#28165;&#21934;&#24050;&#21152;&#20837;&#65281;",  "&#35352;&#20303;&#37325;&#26032;&#25972;&#29702;&#27492;&#38913;&#12290;", "&#30331;&#20837;&#21517;&#31281;&#65306;","&#23494;&#30908;&#65306;","&#35686;&#21578;&#65281;&#27492;&#32178;&#31449;&#26159;&#19981;&#20844;&#38283;&#30340;&#65292;&#25152;&#26377;&#21205;&#20316;&#26159;&#26371;&#34987;&#35352;&#37636;&#12290;", "&#30331;&#20837;", "&#23433;&#20840;&#24615;(SSL)&#30331;&#20837;", "&#25773;&#25918;", "&#21034;&#38500;", "&#20998;&#20139;&#65109;", "&#20786;&#23384;", "&#25511;&#21046;&#25773;&#25918;&#28165;&#21934;&#65072; \"%1\" - %2 &#27161;&#38988;", "&#32232;&#36655;&#22120;", "&#27298;&#35222;&#22120;", "&#36984;&#25799;","&#38918;&#24207;", "&#29376;&#24907;", "&#36039;&#35338;", "&#21034;&#38500;", "&#21517;&#31281;", "&#32317;&#25976;&#65109;", "&#37679;&#35492;", "&#36984;&#25799;&#20013;&#65306;", "&#27425;&#24207;&#65109;", "&#32232;&#36655;&#25773;&#25918;&#28165;&#21934;", "&#21034;&#38500;&#27492;&#21152;&#20837;", "&#21152;&#20837;&#25773;&#25918;&#28165;&#21934;", "&#21517;&#23383;&#65109;", "&#24314;&#31435;", "&#25773;&#25918;&#65306;", "&#27284;&#26696;", "&#23560;&#36655;", "&#20840;&#37096;", "&#24050;&#36984;&#25799;", "&#26032;&#22686;", "&#25773;&#25918;", "&#32232;&#36655;", "&#26032;&#22686;", "&#36984;&#25799;&#65306;", "&#25773;&#25918;&#25511;&#21046;&#65306;", "&#25773;&#25918;&#30446;&#37636;&#65306;", "&#29105;&#36984;&#25976;&#20540;", "First IT &#25552;&#25552;&#20320;&#65306;", "(&#27298;&#26597;&#26356;&#26032;)", "&#20027;&#38913;", "&#21482;&#25628;&#23563; id3", "&#23560;&#36655;", "&#27161;&#38988;", "&#27468;&#25163;", "&#29105;&#36984;&#27468;&#25163;&#23560;&#36655;", "&#27298;&#35222;", "&#20998;&#20139;&#25773;&#25918;&#30446;&#37636;", "&#29992;&#25142;", "&#31649;&#29702;", "&#26368;&#26032;", "&#26368;&#29105;&#38272;", "&#30331;&#20986;", "&#36984;&#38917;", "&#27298;&#26597;", "&#20854;&#20182;", "&#32232;&#36655;&#20351;&#29992;&#32773;", "&#26032;&#22686;&#20351;&#29992;&#32773;", "&#20840;&#21517;", "&#30331;&#20837;", "&#35722;&#26356;&#23494;&#30908;&#65311;", "&#23494;&#30908;", "&#20633;&#35387;", "&#23384;&#21462;&#23652;&#32026;", "&#38283;", "&#38364;", "&#21034;&#38500;&#20351;&#29992;&#32773;", "&#20999;&#26039;&#20351;&#29992;&#32773;","&#37325;&#26032;&#25972;&#29702;","&#26032;&#22686;&#20351;&#29992;&#32773;", "&#21034;&#38500;", "&#30331;&#20986;", "&#20351;&#29992; EXTM3U &#25928;&#26524;&#65311;", "&#39023;&#31034;&#22810;&#23569;&#34892; (&#29105;&#38272;/&#26032;)", "&#26368;&#22823;&#25628;&#23563;&#34892;&#25976;", "&#37325;&#35373;", "&#38283;&#21855;&#30446;&#37636;", "&#36339;&#21040;&#30446;&#37636;&#65306; %1", "&#19979;&#36617;", "&#36339;&#21040;&#19978;&#19968;&#23652;", "&#36339;&#21040;&#26681;&#30446;&#37636;", "&#27298;&#26597;&#26356;&#26032;", "&#20351;&#29992;&#32773;", "&#35486;&#35328;", "&#36984;&#38917;", "&#24050;&#36215;&#21205;", "&#38568;&#27231;", "&#35373;&#23450;", "&#26681;&#30446;&#37636;&#32085;&#23565;&#36335;&#24465;", "&#20018;&#27969;&#36335;&#24465;", "&#38928;&#35373;&#35486;&#35328;", "&#35222;&#31383;&#31995;&#32113;", "&#35201;&#27714;HTTPS", "&#20801;&#35377;&#25628;&#23563;", "&#20801;&#35377;&#19979;&#36617;","&#36926;&#26178;", "&#22577;&#21578;&#30331;&#20837;&#22833;&#25943;", "&#35531;&#31561;&#31561; - &#24314;&#31435;&#27284;&#26696;&#30446;&#37636;&#20013;","&#25773;&#25918;&#28165;&#21934;&#19981;&#34987;&#26356;&#26032;&#65281;", "&#31649;&#29702;&#32773;", "&#20351;&#29992;HTTPS&#30331;&#20837;&#24460;&#26356;&#25913;&#65281;");

$klang[15] = array('Traditional Chinese - big5', 'big5', '�c�餤��', '�̼���', '�̷s', '�j�M', '(�u�� %1 �����)', '��', '\'%1\' �G�j�M���G', '���', '�S��', '��s�j�M��Ʈw�ﶵ', '�R�� ���ιL���O���H', '���� ID3', '���μҦ�', '��s', '����', '��s�j�M��Ʈw', '��� %1 �ɮסC', '�T�w���즹 %1 �ɮסJ ���L�C', '�w�w�ˡJ %1 - ��s�G %2 �M ���ˡG', '���ˡG', '���� - ���D�J %1', 'Ū���즹 %1 �ɮ� �J���L', '�w�����G %1', '�w���J %1 �A �w��s %2 �A �w�R�� %3�A �a�I %4 ���� �� %6 �ɮפ����L%5 - %7 �� - �w�R�� %8 ���O�����ɮ�', '�w����', '����', '�b���䤣������ɮסG \'%1\'', 'kPlaylist �n�J', '���q�⪺�M��M��G %1', '���� %1', '�S���q����ܡC ����M��S����s�C', '����M��w��s�I', '��^', '����M��w�[�J�I', '�O�����s��z�����C', '�n�J�W�١G', '�K�X�G', 'ĵ�i�I�������O�����}���A�Ҧ��ʧ@�O�|�Q�O���C', '�n�J', '�w����(SSL)�n�J', '����', '�R��', '���ɡR', '�x�s', '�����M��J \'%1\' - %2 ���D', '�s�边', '�˵���', '���', '����', '���A', '��T', '�R��', '�W��', '�`�ơR', '���~', '��ܤ��G', '���ǡR', '�s�輽��M��', '�R�����[�J', '�[�J����M��', '�W�r�R', '�إ�', '����G', '�ɮ�', '�M��', '����', '�w���', '�s�W', '����', '�s��', '�s�W', '��ܡG', '���񱱨�G', '����ؿ��G', '����ƭ�', 'First IT �����A�G', '(�ˬd��s)', '�D��', '�u�j�M id3', '�M��', '���D', '�q��', '����q��M��', '�˵�', '���ɼ���ؿ�', '�Τ�', '�޲z', '�̷s', '�̼���', '�n�X', '�ﶵ', '�ˬd', '��L', '�s��ϥΪ�', '�s�W�ϥΪ�', '���W', '�n�J', '�ܧ�K�X�H', '�K�X', '�Ƶ�', '�s���h��', '�}', '��', '�R���ϥΪ�', '���_�ϥΪ�', '���s��z', '�s�W�ϥΪ�', '�R��', '�n�X', '�ϥ� EXTM3U �ĪG�H', '��ܦh�֦� (����/�s)', '�̤j�j�M���', '���]', '�}�ҥؿ�', '����ؿ��G %1', '�U��', '����W�@�h', '����ڥؿ�', '�ˬd��s', '�ϥΪ�', '�y��', '�ﶵ', '�w�_��', '�H��', '�]�w', '�ڥؿ�������|', '��y���|', '�w�]�y��', '�����t��', '�n�DHTTPS', '�i�H�j�M', '�i�H�U��', '�O��', '���i�n�J����', '�е��� - �إ��ɮץؿ���', '����M�椣�Q��s�I', '�޲z��', '�ϥ�HTTPS�n�J����I', '�Ұʦ�y����', '���D', '�q��', '�ۤ���', '�Ƶ�', '�~', '����', '����', '���]�w', '�̰��U���t�v(kbps)', '�Τ�', '%1 ���� - %2 �q��', '%1 kbit %2 ����', '������: %1', '����', '%1�� %2�p�� %3���� �����ɶ� %4 �ɮ� %5 mb', '�o�̨S���������', '�K�X�w���I', '���U', '�п�ܡI', '�����s�H', '�Ы����D�U', '�ϥΥ~���Ϲ��H', '�~���Ϲ����|', '�{���K�X', '�{���K�X���šI', 'Preferred archiver', 'Archive could not be made', '�i��o�{�����ɮסG  "%1" "%2"', '�T�w�R��������H', '�r������', '�H��', '�Ƨ�', '���Ӫ�', '�ϥ�javascript', '�A�T�w�n�R���o�ӥΤ�H', '�˵����{', '���{', '��', '�~�b��CSS�ɮ�', '�R�����Ъ�', '�T�w', '���~', '��y', '�]������^', '�ɮ�', '�ۤ���', '%1�� %2�� %3�� %4��', '�@��', '�ۭq', '�ɮ׺޲z', '�Ы��H�D�U.', '�۰ʧ�s��Ʈw', '�W���ɮש���', '�����v��y�i�H?', 'Include headers', '�~�b��javascript', '�D����', 'Show First IT gives you part', '��ܧ�s����', '��ܲέp���', '��y�g�XID3v2', '�}�ҥΤ���U', '�ɮ��`��', '�O', '�_', '����', 'MIME', '�ǤJM3U', '����ɮ�����', '�T�w�H', '���u���ɮ��ˬd', '�H������', '�Φ�', '�����', '�S���A����', '�ڪ��ߦn', '�䤣�����ŦX��', '�����ɶ��ŦX', '����', '�Ұ�LAME�䴩�H', '����', '�i�H�ϥ�LAME�H', '�q�l', '�ǳ�q�l�ɮסH', 'SMTP���A��', 'SMTP��', '�����', '���e', '�H�X', '�w�H�X�l��I', '�}�ҤW��', '�W���ؿ�', '�}��mp3mail', '�W��', '�ɮפw�W���I', '�ɮפ���W���I', '�зǳ�ϥ�cookies�n�J�I', '�ɴ�', '�q��', '���P��', '����', '�W��', '�ŦX', 'LAME�R�O', '��ܰۤ����ʭ�', '�ۤ����ɮ�', '�ܧ�ۤ����Ϲ��j�p', '�ۤ��ʮM����', '�ۤ��ʮM���', '�q�l��k', '����', 'Pear', '���I', '�Цb�ﶵ����J���T�q�l�a�}�I', '���O�q�����H', '�qURL����ܰۤ����H', '�ۤ���URL', '����H�X�I', '�Τ�w�[�J�I', 'Archive creator', 'Archive is deleted.', '�Τ�w��s�I', '���֧��', '%1 ���ؿ�X', 'Log access', '�i����', 'Archived', '���i��', '�w�J %1 �`�� %2', '��h', '�o��', '%1 mb', '%1 kb', '%1 bytes', '����', '�W�@��', '�U�@��', '�h�� %1 ��', '���X�G', '�q������', '�H�u���U���', '���ݤ�', '�Ұ�', '�Ҧ���Ʀ� * �����O������', '�A����f�N�|�Q�˵��Υ��ݧ��', '�W����y', '�O�ۧ�', '����', '�j�M', '��J�j�M���|', '�ϥΤw��ܪ��H', '�̤p���j���خɶ�', '����', 'm3u', 'asx (WMA)', '���p��s����A�Ы����G%1', '���Hsymlinks?', '�ɮ׼˥�', '�}��URL�w����', '�{�i�W���C��', '�ɮ��������i�H�D', '�ťժ��q�����I', 'Lyrics', 'Lyrics URL', 'Show lyrics link?', '(��?)', '�����T���Τ�n�J�W�٩αK�X');

$klang[16] = array("Traditional Chinese - gb2312", "gb2312", "�c�餤��", "�̼���", "�̷s", "�j�M", "(�u�� %1 �����)", "��", "'%1' �G�j�M���G", "���", "�S��", "��s�j�M��Ʈw�ﶵ", "�R�� ���ιL���O���H", "���� ID3", "���μҦ�", "��s", "����", "��s�j�M��Ʈw", "��� %1 �ɮסC", "�T�w���즹 %1 �ɮסJ ���L�C", "�w�w�ˡJ %1 - ��s�G %2 �M ���ˡG", "���ˡG", "���� - ���D�J %1", "Ū���즹 %1 �ɮ� �J���L", "�w�����G %1", "�w���J %1 �A �w��s %2 �A �w�R�� %3�A �a�I %4 ���� �� %6 �ɮפ����L%5 - %7 �� - �w�R�� %8 ���O�����ɮ�", "�w����", "?��", "�b���䤣������ɮסG '%1'", "kPlaylist �n�J", "���q�⪺�M��M��G %1", "���� %1", "�S���q����ܡC ����M��S����s�C", "����M��w��s�I", "��^", "����M��w�[�J�I", "�O�����s��z�����C", "�n�J�W�١G", "�K�X�G", "ĵ�i�I�������O�����}���A�Ҧ��ʧ@�O�|�Q�O���C", "�n�J", "�w����(SSL)�n�J", "����", "�R��", "���ɡR", "�x�s", "�����M��J '%1' - %2 ���D", "�s�边", "�˵���", "���", "����", "���A", "��T", "�R��", "�W��", "�`�ơR", "���~", "��ܤ��G", "���ǡR", "�s�輽��M��", "�R�����[�J", "�[�J����M��", "�W�r�R", "�إ�", "����G", "�ɮ�", "�M��", "����", "�w���", "�s�W", "����", "�s��", "�s�W", "��ܡG", "���񱱨�G", "����ؿ��G", "����ƭ�", "First IT �����A�G", "(�ˬd��s)", "�D��", "�u�j�M id3", "�M��", "���D", "�q��", "����q��M��", "�˵�", "���ɼ���ؿ�", "�Τ�", "�޲z", "�̷s", "�̼���", "�n�X", "�ﶵ", "�ˬd", "��L", "�s��ϥΪ�", "�s�W�ϥΪ�", "���W", "�n�J", "�ܧ�K�X�H", "�K�X", "�Ƶ�", "�s���h��", "�}", "��", "�R���ϥΪ�", "���_�ϥΪ�", "���s��z", "�s�W�ϥΪ�", "�R��", "�n�X", "�ϥ� EXTM3U �ĪG�H", "��ܦh�֦� (����/�s)", "�̤j�j�M���", "���]", "�}�ҥؿ�", "����ؿ��G %1", "�U��", "����W�@�h", "����ڥؿ�", "�ˬd��s", "�ϥΪ�", "�y��", "�ﶵ", "�w�_��", "�H��", "�]�w", "�ڥؿ�������|", "��y���|", "�w�]�y��", "�����t��", "�n�DHTTPS", "���\�j�M", "���\�U��", "�O��", "���i�n�J����", "�е��� - �إ��ɮץؿ���", "����M�椣�Q��s�I", "�޲z��", "�ϥ�HTTPS�n�J����I");

$klang[17] = array("Korean", "ISO-8859-1", "&#54620;&#44397;&#50612;", "&#51064;&#44592;&#51221;&#48372;", "&#52572;&#49888;&#51221;&#48372;", "&#44160;&#49353;", "(%1 &#47564; &#48372;&#51076;)", "&#52488;", "&#44160;&#49353; &#44208;&#44284; : '%1'", "&#52286;&#50520;&#51020;", "&#50630;&#51020;.", "&#44160;&#49353; &#51088;&#47308; &#50741;&#49496; &#50629;&#45936;&#51060;&#53944;", "&#49324;&#50857;&#54616;&#51648; &#50506;&#45716; &#44592;&#47197; &#49325;&#51228;?", "ID3&#51116;&#44396;&#49457;?", "&#46356;&#48260;&#44536; &#47784;&#46300;?", "&#50629;&#45936;&#51060;&#53944;", "&#52712;&#49548;", "&#44160;&#49353; &#51088;&#47308; &#50629;&#45936;&#51060;&#53944;", "%1 &#54028;&#51068;&#51012; &#52286;&#50520;&#51020;.", "&#51060; &#54028;&#51068;&#51012; &#44208;&#51221;&#54624; &#49688; &#50630;&#51020;: %1, &#44148;&#45320;&#46848;.", "&#49444;&#52824;&#46120;: %1 - &#50629;&#45936;&#51060;&#53944;: %2, &#44160;&#49353;:", "&#44160;&#49353;:", "&#49892;&#54056; - &#51656;&#47928;: %1", "&#51060; &#54028;&#51068;&#51012; &#51069;&#51012; &#49688; &#50630;&#51020;: %1. &#44148;&#45320;&#46848;.", "&#51228;&#44144;&#46120;: %1", "%6 &#54028;&#51068;&#46308; &#51473; %4 &#45716; &#49892;&#54056;, %5&#45716; &#44148;&#45320;&#46832;&#44256;,%1 &#52628;&#44032; %2 &#44081;&#49888;&#46104;&#44256; %3 &#49325;&#51228;&#46120; - %7 &#52488; - %8 &#51008; &#49325;&#51228;&#54364;&#49884;&#46120;.", "&#45149;", "&#45803;&#51020;", "&#50612;&#46500; &#54028;&#51068;&#46020; &#52286;&#51012; &#49688; &#50630;&#51020;: \"%1\"", "kPlaylist &#47196;&#44536;&#50728;", "&#50500;&#54000;&#49828;&#53944;&#51032; &#50536;&#48276; &#47532;&#49828;&#53944; : %1", "&#51064;&#44592;&#49440;&#53469;&#44257; %1", "&#44257;&#51060; &#49440;&#53469;&#46104;&#51648; &#50506;&#50520;&#51020;. Playlist&#44032; &#44081;&#49888;&#46104;&#51648; &#50506;&#50520;&#51020;.", "Playlist &#44081;&#49888;!", "&#46244;&#47196;", "Playlist &#52628;&#44032;!", "&#51060; &#54168;&#51060;&#51648;&#47484; &#45796;&#49884; &#51069;&#51004;&#49464;&#50836;.", "&#47196;&#44536;&#51064;:", "&#50516;&#54840;:", "&#51452;&#51032;! &#51060; &#44275;&#51008; &#44277;&#44060;&#46108; &#50937;&#49324;&#51060;&#53944;&#44032; &#50500;&#45785;&#45768;&#45796;. &#47784;&#46304; &#54665;&#46041;&#51060; &#44592;&#47197;&#46121;&#45768;&#45796;.", "&#47196;&#44536;&#51064;", "&#47196;&#44536;&#50728;&#51012; &#50948;&#54644; SSL&#51060; &#54596;&#50836;&#54633;&#45768;&#45796;.", "&#51116;&#49373;", "&#49325;&#51228;", "&#44277;&#50976;&#46120;:", "&#51200;&#51109;", "playlist &#44288;&#47532;: \"%1\" - %2 &#51228;&#47785;", "&#54200;&#51665;&#44592;", "&#48624;&#50612;", "&#49440;&#53469;", "&#49692;&#49436;", "&#49345;&#53468;", "&#51221;&#48372;", "&#49325;&#51228;", "&#51060;&#47492;", "&#54633;&#44228;:", "&#50724;&#47448;", "&#49440;&#53469;&#54620; &#46041;&#51089;:", "&#49692;&#49436;:", "playlist &#54200;&#51665;", "&#51060; &#44592;&#47197;&#51012; &#49325;&#51228;&#54632;", "playlist &#52628;&#44032;", "&#51060;&#47492;:", "&#47564;&#46308;&#44592;", "&#51116;&#49373;:", "&#54028;&#51068;:", "&#50536;&#48276;", "&#51204;&#48512;", "&#49440;&#53469;&#46120;", "&#52628;&#44032;", "&#51116;&#49373;", "&#54200;&#51665;", "&#49352;&#47196; &#47564;&#46308;&#44592;", "&#49440;&#53469;:", "&#51116;&#49373; &#44288;&#47532;:", "Playlist:", "&#51064;&#44592;&#49440;&#53469;&#44257; &#49707;&#51088;", "&#45817;&#49888;&#50640;&#44172; First IT &#51060; &#51452;&#45716; &#44163;:", "(&#50629;&#44536;&#47112;&#51060;&#46300;&#47484; &#52404;&#53356;&#54616;&#49464;&#50836;)", "&#54856;", "id3&#47564;", "&#50536;&#48276;", "&#51228;&#47785;", "&#50500;&#54000;&#49828;&#53944;", "&#50500;&#54000;&#49828;&#53944;&#50640;&#49436; &#51064;&#44592;&#50536;&#48276;", "&#48372;&#44592;", "&#44277;&#50976;&#54620; playlist", "&#49324;&#50857;&#51088;", "&#50612;&#46300;&#48124; &#44288;&#47532;", "&#52572;&#49888;&#51221;&#48372;", "&#51064;&#44592;&#51221;&#48372;", "&#47196;&#44536;&#50500;&#50883;", "&#50741;&#49496;", "&#52404;&#53356;", "&#45208;&#51032;", "&#49324;&#50857;&#51088; &#54200;&#51665;", "&#49352;&#47196;&#50868; &#49324;&#50857;&#51088;", "&#51060;&#47492;", "&#47196;&#44536;&#51064;", "&#50516;&#54840;&#47484; &#48148;&#44984;&#49884;&#44192;&#49845;&#45768;&#44620;?", "&#50516;&#54840;", "&#53076;&#47704;&#53944;", "&#51217;&#44540;&#47112;&#48296;", "&#53020;&#44592;", "&#45124;&#44592;", "&#49324;&#50857;&#51088; &#49325;&#51228;", "&#49324;&#50857;&#51088; &#47196;&#44536;&#50500;&#50883;", "&#49352;&#47196; &#44256;&#52824;&#44592;", "&#49352;&#47196;&#50868; &#49324;&#50857;&#51088;", "&#49325;&#51228;", "&#47196;&#44536;&#50500;&#50883;", "EXTM3U &#47484; &#49324;&#50857;&#54633;&#45768;&#44620;?", "&#51460; &#49688; &#48372;&#51060;&#44592;(hot/new)", "&#44032;&#51109; &#47566;&#51008; &#44160;&#49353; &#51460;", "&#47532;&#49483;", "&#46356;&#47113;&#53664;&#47532; &#50676;&#44592;", "&#46356;&#47113;&#53664;&#47532;&#47196; &#44032;&#44592;: %1", "&#45236;&#47140;&#48155;&#44592;", "&#54620; &#45800;&#44228; &#50948;&#47196; &#44032;&#44592;", "&#51228;&#51068; &#50948;&#47196; &#44032;&#44592;.", "&#50629;&#44536;&#47112;&#51060;&#47484; &#52404;&#53356;&#54616;&#49464;&#50836;", "&#49324;&#50857;&#51088;", "&#50616;&#50612;", "&#50741;&#49496;", "&#48512;&#54021;&#46120;", "&#46244;&#49438;&#44592;:", "&#49464;&#54021;", "&#44592;&#48376; &#46356;&#47113;&#53664;&#47532;", "&#49828;&#53944;&#47548; &#51109;&#49548;", "&#44592;&#48376; &#50616;&#50612;", "&#50952;&#46020;&#50864; &#49884;&#49828;&#53596;", "HTTPS &#44032; &#54596;&#50836;&#54632;", "Seek &#54728;&#50857;", "&#45236;&#47140;&#48155;&#44592; &#54728;&#50857;", "&#49464;&#49496; &#49884;&#44036;&#51473;&#45800;", "&#49892;&#54056;&#54620; &#47196;&#44596; &#49884;&#46020; &#50508;&#47532;&#44592;", "&#51104;&#44624;&#47564; - &#54028;&#51068; &#47785;&#47197;&#51012; &#44032;&#51648;&#44256; &#50724;&#44256; &#51080;&#49845;&#45768;&#45796;", "Playlist &#50640; &#52628;&#44032;&#54624; &#49688; &#50630;&#49845;&#45768;&#45796;!", "&#50612;&#46300;&#48124;", "&#48148;&#44984;&#44592; &#50948;&#54644;&#49436; HTTPS&#47196; &#47196;&#44596;&#54616;&#49464;&#50836;!");

$klang[18] = array('Estonian', 'ISO-8859-1', 'Eesti', 'Mis on kuum', 'Mis on uus', 'Otsi', '(ainult %1 n�idatud)', 'sec', 'Otsimis tulemused: \'%1\'', 'leitud', 'puudub.', 'uuenda otsi andmebaas muudatused', 'Kustuta kasutamatta read?', 'Ehita ID3 uuesti?', 'Debug mode?', 'Uuenda', 'Katkesta', 'Uuenda otsimis mootor', 'Leitud %1 faili.', 'Ei leidnud faili: %1, katkestatud.', 'Paigaltatud: %1 - Uuenda: %2, skanneri: ', 'Skanneeri: ', 'Katkend - query: %1', 'V�imatu lugeda faili: %1. Katkestatud.', 'Eemaldatud: %1', 'Lisatud %1, uuendatud %2, kustutatud %3 kus %4 viga ja %5 vahele j�etud %6 faili - %7 sekundid - %8 m�rgitud kustutamiseks.', 'Valmis', 'Sulge', 'Ei leidnud �htki faili siit: "%1"', 'kPlaylist Logi sisse', 'Albumi nimekiri artistidest: %1', 'Kuum-valik %1', '�htki lugu pole valitud. Lugude nimekirja ei uuendatud.', 'Lugude nimekiri uuendatud!', 'Tagasi', 'Nimekiri lisatud!', 'Pea meeles et lae leht uuesti.', 'tunnus:', 'salas�na:', 'M�RKUS! See pole avalik weebileht. K�ik tegevused logitakse.', 'Logi sisse', 'SSL required for logon.', 'M�ngi', 'Kustuta', 'Jagatud: ', 'Salvesta', 'Muuda lugude nimekirja: "%1" - %2 ', 'Muuda', 'N�ita', 'Vali', 'Seq', 'Staatus', 'Info', 'Kustuta', 'Nimi', 'Koku:', 'Viga', 'Tegevus valitud: ', 'Sequence:', 'muuda nimekirja', 'Kustuta sissekanne', 'lisa nimekiri', 'Nimi:', 'Loo', 'M�ngi: ', 'Fail', 'Album', 'K�ik', 'Valitud', 'lisa', 'm�ngi', 'muuda', 'uus', 'Vali:', 'M�ngi: ', 'Nimekiri: ', 'Kuumvalik', 'First IT annab sulle:', '(kontrolli uuendusi)', 'Koduleht', 'ainult id3', 'album', 'pealkiri', 'artist', 'Vali artist', 'vaata', 'Jagatud nimekirjad', 'Kasutajad', 'Kontroll paneel', 'Mida uut?', 'Mis on kuum?', 'Logi v�lja', 'Valikud', 'Vali', 'Minu', 'muuda', 'lisa kasutaja', 'Nimi (pikalt)', 'Kasutaja-tunnus', 'Muuda salas�na?', 'Salas�na', 'Kommentaar', 'Ligip��su tase', 'Sees', 'V�ljas', 'Kustuta kasutaja', 'Logi v�lja', 'V�rskenda', 'Uus kasutaja', 'kustuta', 'logi v�lja', 'Kasuta EXTM3U v�imalust?', 'N�ita ridu (kuum/uus)', 'Otsi maksimaalselt', 'Reseti', 'Ava kataloog', 'Mine kataloogi: %1', 'Lae-alla', '�ks aste �lesse', 'Mine juur kataloogi.', 'Kontrolli uuendusi', 'kasutajad', 'Keel( Language)', 'muudatused', 'Booted', 'Segamini:', 'S�tted', 'Baas kataloog', 'Saatja(Stream) asukoht', 'P�hi-keel', 'Windowsi s�steem', 'N�ua HTTPS', 'Luba kerida', 'Luba alla-laadida', 'Sessioon aegub', 'Teata eba�nnestunud logimistest', 'Hoia kinni - tirin failide nimekirja', 'Nimekirja pole v�imalik lisada!', 'Administraator', 'Sisselogimine muuda HTTPS vastu!', 'Luba voolav(streaming) mootor', 'Pealkiri', 'Artist', 'Album', 'Kommentaar', 'Aasta', 'Rada', 't��p', 'pole seatud', 'Maksimaalne m�ngimise rate (kbps)', 'Kasutaja', '%1 minuteid - %2 pealkirju', '%1 kbit %2 minuted', 'S�see list: %1', 'Go', '%1d %2h %3m m�nguaega %4 faili %5 mb', 'Puuduvad.', 'Salas�na muudetud!', 'Registreeri', 'Tee oma valik!', 'Mis on uuendus?', 'Vajuta siia abisaamiseks', 'Kasuta v�liseid pilte?', 'V�liste piltide kataloog', 'Praegune salas�na', 'Salas�nad ei sobi kokku!', 'Soovitud pakkija', 'Arhiivi pole v�imalik luua', 'Korduvaid kirjeid leitud:  "%1" "%2"', 'Kas kustutada nimekiri?', 'T�hestik', 'Suvaline', 'Sorteeri', 'Originaal', 'Kasuta javascripti', 'Kas oled kindel et soovid kustutada kasutajat?', 'Vata ajalugu', 'ajalugu', 'Ridu', 'V�line CSS fail', 'Eemalda korduvad', 'OK', 'ERR', 'Stream', '(nagu)', 'failid', 'albumid', '%1d %2h %3m %4s', 'Pea', 'Valikuline', 'Failihaldur', 'Vajuta ? abi-saamiseks.', 'Automaatne andmebaasi s�nkroniseerimine', 'Saada faili laiend', 'Luba logimatta kuulajaid', 'Lisa (Headers)', 'V�line javascript', 'Koduleht', 'N�ita First IT annab sulle t�ki', 'N�ita uuenduste osa', 'N�ita statistika', 'Kirjuta ID3v2 streami sisse', 'Luba kasutajate registreerimine', 'Failit��bid', 'Jah', 'Ei', 'Laiend', 'MIME', 'Lisa M3U', 'muuda failit��pi', 'Kindel?', 'Optimistiline failikontroll', 'Segamini', 'Mode', 'Nimekiri', 'Puudub, otsene', 'Minu lemmikud', 'Ei leidnud �htki', 'Kokku', 'J�rjesta', 'Luba LAME toetus?', 'Keelatud', 'Luba LAME kasutus?', 'Email', 'Luba faile saata emailiga?', 'SMTP server', 'SMTP port', 'Mail to', 'Teade', 'Saada', 'Kiri saadetud!', 'Aktiivne �lesse laadimine', '�lesse-laadimise kataloog', 'Aktiveeri mp3mail', 'Lae', 'Faili �lesse-laadimine!', 'Faili pole v�imalik serverisse saata!', 'K�psised peavad olema lubatud!', 'Periood', 'kunagi', 'see n�dal', 'see kuu', 'eelmine kuu', 'hits', 'LAME k�sk', 'N�ita albumi kaant', 'Albumi failid', 'Suurenda albumi pilte', 'Albumi k�rgus', 'Albumi laius', 'Saatmise meetod', 'Otse', 'Pear', 'Oota!', 'Palun sisesta toimiv email!', 'Nimekiri peidetud?', 'N�ita albumeid URLi aadressilt?', 'Albumi URL', 'Pole v�imalik saata!', 'Kasutaja lisatud!', 'Arhiivi looja', 'Arhiiv kustutatud.', 'Kasutaja laetud!', 'Muusika sobivus', '%1 sissekannet filtreeritud', 'Logi ligip��s', 'Vaadatav', 'Arhiveeritud', 'Bulletin', 'Lisatud %1 - %2', 'veel', 'Avalda', '%1 mb', '%1 kb', '%1 baiti', 'Rekursiivne ', 'Eelmine', 'J�rgmine', 'Mine lehele %1', 'Lehek�lg:', 'Pole kunagi m�ngitud', 'K�sitsi luba registreerimisi', 'Ootel', 'Aktiveeri', 'K�ik v�ljad mis on m�rgitud * on kohustuslikud', 'Sinu konto kontrollitakse ja aktiveeritakse k�sitsi.', 'Viimased striimingud', 'M�leta mind', 'Stiil', 'Leia', 'Kinnita otsing', 'Kasuta valikut', 'Ajan�it min/max', 'Minutid', 'm3u', 'asx', 'Kui uuendus peatub, vajuta siia: %1', 'j�lgi symlinke', 'file�i �abloon', 'Annab URL kaitse', 'Lae uus list', 'File t��p ei ole lubatud', 'Lugude nimekiri t�hi', 'Laulus�nad', 'Laulus�nade URL', 'N�ita laulus�nade linki', '(v�i?)', 'Tundamtu kasutajatunnus v�i parool ');

$klang[19] = array('Brazillian Portuguese', 'ISO-8859-1', 'Portugu�s do Brasil', 'Mais ouvidos', 'Novidades', 'Buscar', '(apenas %1 mostrados)', 'seg', 'Resultados da busca: \'%1\'', 'encontrado(s)', 'Nenhum', 'Atualizar op��es de busca no BD', 'Apagar entradas sem uso?', 'Reconstruir ID3?', 'Modo Debug?', 'Atualizar', 'Cancelar', 'Atualizar busca no banco de dados', '%1 arquivo(s) econtrado(s).', 'O arquivo %1 foi descartado (n�o pode ser determinado)', 'Instalado: %1 - Atualizar: %2, Scanear:', 'Escanear:', 'Falha na busca: %1', 'N�o foi poss�vel ler este arquivo: %1. Descartado.', 'Link removido: %1', 'Inserido %1, atualizado %2, apagado %2, onde %4, falhou em %5, descartado por %6, arquivos - %7 seg - %8 marcado para ser deletado', 'Finalizado.', 'Fechar', 'N�o foi encontrado nenhum arquivo aqui: "%1"', 'Logon kPlaylist', 'Lista de �lbum por artista: %1', 'Populares %1', 'Nenhuma m�sica selecionada. Lista n�o atualizada.', 'Lista atualizada!', 'Voltar', 'Lista adicionada!', 'Lembre de atualizar a p�gina.', 'Login:', 'Senha:', 'Aten��o! Este n�o � um site restrito. Todas as a��es s�o monitoradas.', 'Login', 'SSL necess�rio para entrar.', 'Tocar', 'Apagar', 'Compartilhado:', 'Salvar', 'Lista de controle: "%1" - %2 t�tulos', 'Editor', 'Visualizador', 'Selecionar', 'Seq', 'Status', 'Info', 'Del', 'Nome', 'Totais:', 'Erro', 'A��o selecionada:', 'Sequ�ncia:', 'Editar lista', 'Apagar esta entrada', 'Adicionar lista', 'Nome:', 'Criar', 'Tocar:', 'Arquivo', '�lbum', 'Todos', 'Selecionado', 'Adicionar', 'Tocar', 'Editar', 'Novo', 'Selecionar:', 'Controle:', 'Lista:', 'Selecionar n�merico', 'First IT oferece:', '(verificar atualiza��o)', 'P�gina inicial', 'apenas id3', '�lbum', 'T�tulo', 'Artista', 'Selecionar �lbum por artista', 'Ver', 'Listas compartilhadas', 'Usu�rios', 'Controle de administrador', 'Novidades', 'Mais executado', 'Sair', 'Op��es', 'Verificar', 'Meu', 'Editar usu�rio', 'Novo usu�rio', 'Nome completo', 'Login', 'Mudar senha?', 'Senha', 'Coment�rio', 'N�vel de acesso', 'Ligado', 'Desligado', 'Apagar usu�rio', 'Desconectar usu�rio', 'Atualizar', 'Novo usu�rio', 'Apagar', 'Desconectar', 'Utilizar op��o EXTM3U?', 'Mostrar quantos arquivos (popular/novo)', 'M�ximo de arquivos encontrados', 'Restaurar', 'Abrir diret�rio', 'Ir para o diret�rio: %1', 'Download', 'Subir um n�vel', 'Ir para o diret�rio principal.', 'Verificar atualiza��es', 'Usu�rios', 'Idioma', 'Op��es', 'Carregado', 'Aleat�rio:', 'Op��es', 'Diret�rio base', 'Local de stream', 'Idioma padr�o', 'Sistema Windows', 'Requer HTTPS', 'Permitir busca', 'Permitir download', 'Sess�o expirou (seg)', 'Falha na tentativa de login', 'Aguarde - buscando lista de arquivos', 'Lista n�o pode ser adicionada!', '0 = Admin, 1 = Usu�rio', 'In�cio de uma sess�o com o HTTPS a mudar', 'Habilite processo streaming', 'T�tulo', 'Artista', '�lbum', 'Coment�rio', 'Ano', 'Faixa', 'G�nero', 'Desativado', 'Taxa m�xima de download (kbps)', 'Usu�rio', '%1 minuto(s) - %2 T�tulos ', '%1 kbit %2 minuto(s)', 'Lista de G�neros: %1 ', 'Ir', 'Tocando: %1d %2h %3m : %4 files : %5 mb', 'Aqui n�o h� recurso relevante.', 'Senha alterada!', 'Registrar', 'Por favor, selecione!', 'O que est� atualizado?', 'Clique aqui para Ajuda', 'Usar Imagens Externas?', 'Path externo de imagens ', 'Senha Atual', 'A senha n�o confere!', 'Arquivo preferido ', 'Arquivo n�o pode ser criado!', 'Provavelmente encontrado arquivo duplo: "%1" "%2"', 'Deseja apagar a lista?', 'Alfab�tico', 'Rand�mico', 'Tipo', 'Original', 'Usar javascript', 'Deseja realmente deletar este usu�rio?', 'Ver descri��o', 'Descri��o', 'Fileiras', 'Arquivo CSS externo', 'Remover duplicados', 'OK', 'ERR', 'Stream', '(mostrar como)', 'Arquivos', '�lbuns', '%1d %2h %3m %4s', 'Geral', 'Customizar', 'Menu do arquivo', 'Clique em ? para Ajuda.', 'Autom�tico banco de dados sync', 'Enviar extens�o de arquivo ', 'Permitir streams n�o autorizados ', 'Incluir cabe��lho', 'Javascript externo', 'Homepage', 'Exibir o que First IT lhe oferece ', 'Mostrar atualiza��o � parte', 'Mostrar estat�sticas', 'Escrever ID3v2 com stream', 'Permitir registro do usu�rio', 'Tipo de arquivos', 'Sim', 'N�o', 'Extens�o', 'MIME', 'Incluir no M3U', 'Editar tipo de arquivo', '� isso mesmo?', 'Otimizar a procura do arquivo', 'Randomizar', 'Modo', 'Lista para tocar', 'Nenhum, direto', 'Meus favoritos', 'N�o foi encontrado nenhum sucesso (hit)', 'Sempre sucessos (hits)', 'Ordem', 'Habilitar suporte LAME?', 'Desabilitado', 'Permitir o uso de LAME?', 'E-mail', 'Permitir enviar arquivos por e-mail?', 'Servidor SMTP', 'Porta SMTP', 'E-mail para', 'Mensagem', 'Enviar', 'E-mail enviado!', 'Ativar upload', 'Diret�rio de uploads', 'Ativar mp3mail', 'Upload', 'Upload completo!', 'N�o foi poss�vel fazer upload do arquivo', '� necess�rio ativar cookies para o login!', 'Per�odo', 'Sempre', 'Esta semana ', 'Este m�s', '�ltimo m�s', 'Sucessos (hits)', 'Comando LAME', 'Exibir capa do �lbum', 'Arquivos do �lbum', 'Redimencionar tamanho das imagens do �lbum', 'Altura do �lbum', 'Largura do �lbum', 'M�todo de enviar e-mail', 'Direto', 'Pear', 'Aguarde!', 'Por favor, insira seu e-mail v�lido nas op��es!', 'Listas em espera?', 'Exibir �lbum da URL', 'URL do �lbum', 'N�o foi poss�vel enviar!', 'Usu�rio adicionado!', 'Compressor de arquivos', 'Arquivo deletado.', 'Usu�rio atualizado!', 'M�sica encontrada', '%1 entradas filtradas', 'Log de acesso', 'Vis�vel', 'Arquivado', 'Boletim', 'Entrada %1 de %2', 'mais', 'Publicador', '%1 mb', '%1 kb', '%1 bytes', 'Recursivo', 'Anterior', 'Pr�ximo', 'V� para a p�gina %1', 'P�gina:', 'Nunca tocado', 'Aprova��o Manual', 'Pendente', 'Ativando', 'Todos os campos marcados com * s�o Obrigat�rios', 'Sua conta est� aguardando autoriza��o manual', 'Novas streams', 'Lembrar', 'Estilo', 'Busca', 'Informe os caminhos de procura', 'Usar o selecionado?', 'Tempo da faixa min/max', 'Minutos', 'm3u', 'asx (WMA)', 'Se a atualiza��o parar clique aqui: %1', 'Seguir symlinks?', 'Mod�lo de arquivo', 'Habilitar URL security', 'Enviar lista permitida', 'Tipo de arquivo n�o permitido', 'A Lista de Execu��o est� vazia!', 'Letras', 'URL das letras', 'Mostrar link das letras?', '(ou?)', 'Usu�rio ou senha inv�lidos', 'Tamanho max do upload: %1', 'Abrir public RSS?', 'Favor inserir senha!', '� necess�rio o nome e login', 'Usu�rio j� existente!', 'Acesso admin. para esta sess�o?', 'Buscar entradas no banco de dados: %1/%2', 'N�o foi poss�vel encontrar "%1", o arquivo foi apagado?', 'De/At� (DDMMAA)', 'Erro, tente novamente.', 'Comprimento m�ximo do texto', 'Dir Colunas', 'Novo modelo de exibi��o', 'Modelo de exibi��o', 'Nomear modelo', '� necess�rio nomear o modelo!', 'Modo padr�o de registro', 'Extrator Tag:', 'Permitir usar arquivo(s)', 'Tamanho m�ximo de arquivo (mb)', 'Foi excedido o tamanho m�ximo do arquivo! (%1mb, max is %2mb)', 'Principal', 'Force LAME rate', 'Transcode', 'httpQ', 'Error when contacting httpQ server (%1).', 'Use database cache?', 'Unused records were not deleted due to skips.', 'tamanho', 'Ouvir musica', 'Vista da lista:', 'Num max vista detalhada', 'Efectiva', 'Detalhada', 'AJAX Prototype URL', 'Radio', 'Sem Fim', 'Desulpe ocorreu algum erro', 'Demonstra��o', 'Sincronizando %1 com as %2 entradas', 'Status %1 da rede: %2', 'Update %1/%2 da rede');

$klang[20]  = array('Simplified Chinese', 'gb2312', '��������', '�����Ƽ�', '�������', '����', 'Ŀǰֻ�� %1', '��', '�����������%1��', '���ҵ�', 'û��', '�����������ݿ�ѡ��', 'ɾ��δʹ�õļ�¼��', '�ؽ�ID3��ǩ��', '�Ŵ�ģʽ��', '����', 'ȡ��', '�����������ݿ�', '���ҵ� %1 ���ļ�', '�޷�ʶ����ļ���%1����������', '�Ѱ�װ��%1 -���£�%2��ɨ�裺', 'ɨ�裺', '��ѯ��%1��ʧ����', '�޷���ȡ���ļ���%1����������', '%1�ѱ�ɾ����', '����%6���ļ�����%1������%2��ɾ��%3��ʧ��%4������%5������%7�룬���ɾ��%8��', '�����', '�ر�', '�ڡ�%1���Ҳ����κ��ļ�', '��½KPlayList', '��%1����ר���б�', '��ѡ%1', 'δѡ��Ƶ���������б�δ���£�', '�����б��ѱ����£�', '����', '�����б������ӣ�', '��ǵ�ˢ��ҳ�棡', '�ʺţ�', '���ܷ��ʣ�', '��ע�⣡����վ���ǹ����ģ����в�������ϵͳ��¼��', '��½', '��½��ҪSSL֧�֣�', '����', 'ɾ��', '������', '����', '���Ʋ����б�����%1��-%2 ����', '�༭�ˣ�', '�鿴�ߣ�', 'ѡ��', '��', '״̬', '��Ϣ', 'ɾ��', '����', '�ܼƣ�', '����', '����ѡ��ʱ��', '���⣺', '�༭�����б�', 'ɾ���˼�¼', '���Ӳ����б�', '���ƣ�', '����', '���ڲ��ţ�', '�ļ�', 'ר��', 'ȫ��', '��ѡ�е�', '����', '����', '�༭', '��', 'ѡ��', '���ſ��ƣ�', '�����б���', '��ѡ��Ŀ', 'First IT ��ʾ��', '�������£�', '��վ', '��ID3', 'ר��', '����', '������', '�����Ҽ�ѡר��', '�鿴', '�������Ĳ����б�', '�û�', '����Ա�������', '�������', '�����Ƽ�', '�˳�', 'ѡ��', '���', '�ҵ�', '�༭�û���Ϣ', '�������û��ʺ�', 'ȫ��', '�ʺ�', '�������룿', '����', 'ע��', '����Ȩ��', '��', '��', 'ɾ���û�', 'ʹ�û��˳�', 'ˢ��', '�������û��ʺ�', 'ɾ��', '�˳�', 'ʹ��EXTM3U���ԣ�.m3u��', '�鿴����������/���£�', '�����������', '����', '��Ŀ¼', '���뵽Ŀ¼��%1', '����', '������һ��Ŀ¼', '���ظ�Ŀ¼', '�������', '�û�', '����', 'ѡ��', '�ѱ�ϵͳ�߳�', '���򲥷ţ�', '����', '��Ŀ¼', '���ļ�Դ', 'ȱʡ����', 'Windowsϵͳ', '��ҪHTTPS', '��������', '��������', 'Session���̳�ʱ', '����ʧ�ܵĵ�½������Ϊ', '���Եȡ������ڶ�ȡ�ļ��б�', '�����б��޷������ӣ�', '����Ա', '����HTTPS��ʽ��½', '��������Ч', '����', '������', 'ר��', 'ע��', '��', '����', '����', 'δ����', '�����������(Kbps)', '�û�', '%1 ���� - %2 ������', '%1 ǧ���� %2 ����', '�����б���%1', 'ȷ��', '%1d %2h %3m ����ʱ�� %4 ���ļ� %5 ��', 'û�������Դ', '�����Ѿ��ɹ��޸ģ�', '��½', '��ѡ��һ�', '�����ʲô���£�', '��������ȡ����', 'ʹ����չͼ����ʾ��', '��չͼƬ·��', '��ǰ����', '��ǰ���뻥��ƥ�䣡', '��ȡ�õĴ浵', '�޷��浵', '������ͬ���ļ�%1-%2�ҵ���', '���ɾ�������б���', '����ĸ˳������', '�������', '����', '��Դ', 'ʹ��javascript', '��϶�Ҫɾ������û���', '�鿴��ʷ����', '��ʷ����', '��', '�ⲿCSS�ļ�', 'ɾ������', '�ɹ�', '����', '��', '��չʾ���ݣ�', '�ļ�', 'ר��', '%1�� %2ʱ %3�� %4��', 'һ��', '����', '�ļ�����', 'Ҫ�鿴�����밴������', '�Զ����ݿ�ͬ��', '�����ļ���׺', '����δ�����ɵ�������', '�����ļ�ͷ', '�ⲿjavascript', '��ҳ', '��ʾ��First IT�����㡱����', '��ʾ��������', '��ʾͳ����Ϣ', '����������д��ID3v2��Ϣ', '�����û�ע��', '�ļ�����', '��', '��', '��չ��', 'MIME', '���뵽M3U', '�༭�ļ�����', 'ȷ����', '�����ļ����', '���', 'ģʽ', '�����б�', 'û�У�ֱ�ӵ�', '�ҵ�����', 'û���ҵ��κε��', '����ʱ����', '˳��', '����LAME֧�֣�', '�ر�', '����LAME�÷���', '�����ʼ�', '�����ʼ��ļ���', 'SMTP������', 'SMTP�˿�', '�ĸ�', '��Ϣ', '����', '�ʼ��ѷ��ͣ�', '�����ϴ�', '�ϴ�Ŀ¼', '����mp3�ʼ�', '�ϴ�', '�ļ����ϴ�', '�ļ��ϴ����ɹ�', '����뿪��cookies���ܲ��ܵ�½', 'ʱ��', '��ʷ����', '�������', '�����', '�ϸ���', '���', 'LAME����', '��ʾר������', 'ר�������ļ�', '����ר�������С', 'ר�����泤', 'ר�������', '�ʼķ�ʽ', 'ֱ��', 'Pear', '��Ⱥ�', '��������Ч��e-mail��ַ��', '��Ƕ�����б���', '��URL��ʾר�����棿', 'ר������URL', '���ܷ��ͣ�', '�û������ӣ�', '����������', '�����ѽ�����', '�û��Ѹ��£�', '����ƥ��', '%1 ��Ŀ�ѹ���', 'д�������־', '�ɼ�', '�浵', '����', '�� %2 д�� %1', '����', '����', '%1 ���ֽ�', '%1 ǧ�ֽ�', '%1 �ֽ�', 'ѭ��', '��һҳ', '��һҳ', '������ %1 ҳ', 'ҳ��', '��δ����', '�ֹ���׼ע��', '�ȴ�', '����', '����*�����ֶ��Ǳ�����Ŀ', '����ʻ��ᱻ��˲��˹����', '�Ѳ���������', '���ס��', '���', '����', '��������·��', 'ʹ��ѡ���', '����ʱ�� ��С/���', '����', 'm3u', 'asx (WMA)', '�������ֹͣ���밴�� %1', 'Follow symlinks?', '�ļ���Ϣ��ʽģ��', '����URL ��ȫ����', '�ϴ�������', '�ļ����Ͳ���������', '�����б��ǿյģ�', '���', '���URL', '��ʾ������ӣ�', '���򣿣�', '�����ڵ��û��������벻��ȷ', '����ϴ��ļ���С��%1', '�򿪹���RSS feed��', '���������룡', '��Ҫ�û�������½', '�û��Ѿ����ڣ�', '�ڱ��Ự���˳�����ԱȨ�ޣ�', 'ȡ�����ݿ��¼��%1/%2', '�Ҳ�����%1�����ļ���ɾ����', '��/�� ����(DDMMYY)', '�����ֶδ������������롣', '����ı�����', 'Ŀ¼��Ŀ', '��ģ��', 'ģ��', 'ģ������', '��Ҫһ��ģ������', 'Ĭ�ϵ�½ģ��', '��ǩ��ȡ��', '����ʹ�ô浵', '��󵵰���С(mb)', '����̫��(%1mb, ��� %2mb)', '��Ŀ¼', 'ǿ��LAME����', '����', 'httpQ', '����httpQ������(%1)������', 'ʹ�����ݿ⻺�棿', '����������飬���õļ�¼δ��ɾ����', '����', '����ר��', '�б���ͼ��', '��ϸ��Ŀ�����ʾ����', '���', '��ϸ', 'AJAX ԭ�� URL', '�㲥', 'ѭ��', '�Բ��� - ��¼����', '��ʾ');

$klang[21] = array("Catalan", "iso-8859-1", "Catal�", "El m�s nou", "Novetat", "Cerca", "(nom�s es mostra %1)", "seg", "Resultats de la Recerca: '%1'", "trobat", "Cap.", "actualitza les opcions de recerca a la base de dades", "Esborrar registres no utilitzats?", "Regenerar ID3?", "Mode depuraci�?", "Actualitza", "Cancel�la", "Actualitza base de dades de recerca", "Trobats %1 fitxers.", "No puc determinar aquest fitxer: %1, l'ignoro.", "Instal�lat: %1 - Actualitzat: %2, Escanejat:", "Scanejat:", "Error - query: %1", "No puc llegir aquest arxiu: %1. L'ignoro.", "Esborrat: %1","Insertat %1, actualitzat %2, esborrat %3 amb %4 errors i %5 ignorats de %6 arxius - %7 seg - %8 marcats per esborrar.", "Fet", "Tanca", "No he trobat cap arxiu a: \"%1\"", "Entrar a kPlaylist", "Llista d'�lbums de l'artista: %1", "Marcat %1", "No s'han sel�leccionat can�ons. Playlist no actualitzada.", "Playlist actualitzada!", "Tornar", "Playlist afegida!", "Recorda recarregar la p�gina.", "Entrar:", "Secret:", "Compte! Aix� �s una WEB no p�blica. Totes les accions es registren. ", "Entrar", "Es requereix SSL per entrar.", "Reprodueix", "Esborra", "Compartit:", "Graba.", "Playlist de Control: \"%1\" - %2 t�tols", "Editor", "Visualitzador", "Sel�lecciona", "Seq", "Estat", "Info", "Esborra", "Nom", "Totals:", "Error", "Accions en sel�leccionar:", "Seq��ncia:", "edita Playlist","Esborra aquesta entrada", "afegeix playlist", "Nom:", "Crea", "Reprodueix:", "Arxiu", "�lbum", "Tot", "Sel�leccionat", "afegeix", "reprodueix", "edita", "nou", "Sel�lecciona:", "Control de reproducci�:", "Playlist;", "Sel�lecci� num�rica", "First IT et dona:", "(actualitzaci� de soft)", "Homesite", "nom�s id3", "�lbum", "t�tol", "artista", "�lbum sel�leccionat de l'artista", "veure", "Playlists compartits", "Usuaris", "Control d'Administrador", "Que hi ha de nou", "Que hi ha novedos", "Sortir", "Opcions", "Txequeja", "Jo", "edita usuari", "nou usuari", "Nom complet", "Entrada", "Canviar password?", "Password", "Comentari", "Nivell d'acc�s", "On", "Off", "Esborrar usuari", "Desconnectar usuari", "Refrescar", "Nou usuari", "esborra", "sortir", "Utilitzar caracter�stiques EXTM3U?", "Mostrar quantes columnes (hot/nou)", "M�xim de columnes de recerca", "Resetejar", "Obrir directori", "Anar al directori: %1", "Descarregar", "Pujar un nivell", "Anar al directori root.", "Txequeja actualitzacions.", "usuaris", "Llenguatge", "opcions", "Iniciat", "Aleatori:", "Configuraci�", "directori base", "Localitzaci� d'Stream", "Llenguatge per defecte", "Sistema Windows", "Necessita HTTPS", "Permetre recerques", "Permetre desc�rregues", "Temps de sessi� (COOKIE)", "Reporta errors d'intent d'entrada", "Espera. Recuperant llista de fitxers.", "No es pot afegir la Playlist!", "Admin", "Entra per HTTPS per acceptar els canvis!", "Activa el motor d'streaming", "T�tol", "Artista", "�lbum", "Comentaris", "Any", "Pista", "G�nere", "no especificat", "M�xim ample de desc�rrega (kbps)", "Usuari", "%1 mins - %2 t�tols", "%1 kbit %2 mins", "Llista de g�neres: %1", "Som-hi", "Temps de reproducci� %1d %2h %3m %4 arxius %5 mb", "No hi ha arxius relevants.", "Password canviat!", "Signa", "Siusplau fes una sel�lecci�!", "Que hi ha de nou?", "Clica aqu� per a ajuda", "Utilitza imatges externes?", "Cam� per a imatges externes", "Password actual", "Password actual no coincideix!", "Arxivador preferit", "No es pot crear l'arxiu", "Trobat un problable arxiu duplicat: %1 - %2", "Esborrar Playlist de deb�?", "Alfab�tic", "Al�leatori", "Ordena", "Original", "Utilitza javascript", "Estas segur que vols esborrar aquest usuari?", "Veure historial", "Historial", "Files", "Arxiu CCS extern");

$klang[22] = array('Bulgarian', 'Windows-1251', '���������', '���-���������', '���-������', '�������', '(�������� �� ���� %1)', '���', '�������� �� ���������: \'%1\' ', '�������', '����.', '���������� �� ������ ����� �� ������� - �����', '��������� �� �������������� ������?', '������������� ID3? ', '������������ �� ���������?', '����������', '�����', '���������� �� ������ ����� �� �������', '�������� %1 �����.', '�� ���� �� �� �������� ���� ����: %1, ���������.', '����������: %1 - ����������: %2, ���������:', '���������:', '������ - ������: %1', '���� ���� �� ���� �� ���� ��������: %1. ���������.', '����������: %1 ', '������� %1, ���������� %2, ������� %3 ������ %4 �� ��������� � %5 ���������� �� %6 ����� - %7 ��� - %8 ��������/� �� ���������.', '������', '�������', '�� �� �������� ������� ���: "%1"', '����', '������ �� �������� �� �����: %1', '���� ����� %1', '�� �� ������� �����. ���������� �� � �������.', '���������� � �������!', '�����', '���������� � �������!', '�� ���������� �� ����������� ����������.', '���:', '������:', '������������ ��', '���', '��������� � SSL �� �������.', '�����', '������', '�������:', '������', '���������� �� ��������: "%1" - %2  ������� ��������', '��������', '��������', '������', '������.', '���������', '����������', '����.', '���', '����:', '������', '�������� �� ���������:', '����������������:', '���������� ���������', '�������� ����', '������ ���������', 'Stoyan Stoyanov', '������', '�����:', '����', '������', '������', '���������', '������', '�����', '�����������', '���', '������:', '�����', '��������:', '����� �������� �� �����', 'First IT �� ����:', '(������� �� ����������)', '��������� ����', '���� id3 ', '�����', '�����', '�����', '����� �������� �� �����', '���', '��������� ���������', '�����������', '�����. ����������', '���-������', '���-���������', '��������', '���������', '���������', '����� �����', '����������� �� �����������', '��� ����������', '����� ���', '���', '����� �� ��������?', '������', '��������', '����� �� �������', '���.', '����.', '��������� �� ����������', '�������� �� ����������', '�����������', '��� ����������', '����.', '�����', '���������� �� EXTM3U?', '��������� �� ����� ������ (������/����)', '�������� ������ ��� �������', '��������', '������ ����������', '����� � ����������: %1', '�������', '����� ���� ������ ������', '����� � �������� ����������.', '������� �� �������.', '�����������', '����', '�����', '�������', '����������:', '���������', '������ ����������', 'Stream location', '���� �� ������������', 'Windows �������', '��������� HTTPS ', '��������� ���������� �� ��������', '��������� �������', '����� �� �������', '������ �� ���������� �� ������', '��������� �� ������ - ��������� �� ������� � ���������...', '��������� �� ���� �� ���� �������!', '�������������', '���� � HTTPS �� �����!', '�������� streaming engine', '�����', '����������', '�����', '��������', '������', '�����', '����', '�� �������', '����. ������� �� ������� (kbps)', '����������', '%1 ���. - %2 �������� ', '%1 kbit %2 ���. ', '������ �������: %1', '�����', '�����: %1�. %2�. %3�. : %4 ����� : %5 ��.', 'No relevant resources here.', '�������� � �������!', '�����������', '���� ��������� �����!', '����� � ����������?', '��������� ��� �� �����', '���������� �� ������ ������?', '���� �� �������� ������', '������ ������', '�������� ������ �� �������!', '����������� ���������', '������ �� ���� �� ���� ��������', '�������� � �������� ���������: %1 - %2', '������ �� �� �������� ���������?', '�� ������� ���', '����������', '���������', '����������', '��������� javascript', '������� �� ���, �� ������ �� �������� ���� ����������?', '��� ���������', '�������', '������', '������ CSS ����', '�������� �����������', 'OK', 'ERR', '�����', '(������ ����)', '�������', '������', '%1� %2� %3� %4�', '����', '�������', '��������� �� ���������', '��������� ? �� �����.', '���� �������������� �� ������ �����', '����� ������������ �� �����', '������� �������������� ������', '������ ��������', '������ javascript', '������ ��������', '������ ������: First IT �� ����', '������ ������: �� ����������', '������ ����������', '������ ID3v2 ��� �������', '������� ����������� �� ���� �����������', '������ �������', '��', '��', '����������', 'MIME', '������ � M3U', '���������� ������ ���', '������� �� ���?', '������������ �������� �� ���������', '������������', '�����', '��������', '����, ��������', '����� ������', '�� �� �������� ���������', '������ �� ������ �������', '��������', '������ ��������� �� LAME?', '��������', '������� ���������� �� LAME?', '�����', '������� ������� �� ������� �� �����?', 'SMTP ������', 'SMTP ����', '����� ����� ��', '���������', '�������', '���������!', '��������� ���������', '���������� �� ���������', '��������� mp3mail', '�������', '������ � �����!', '������ �� ���� �� �� ����!', '������ �� ��������� ��������� �� �� �������!', '������', '������', '���� �������', '���� �����', '��������� �����', '���������', 'LAME �������', '������ ��������� �� ��������', '��������� ��������� �� ��������', '������� ������� �� ���������', '�������� �� ���������', '������ �� ���������', '����� �����', 'Direct', 'Pear', '�������!', '���� �������� ������� ����� � �������!', '������� ���������?', '������ ������ �� URL?', 'URL �� ������', '�� ���� �� �� �������!', '������������ � �������!', '���������', '������ � ������.', '������������ � �������!', '��������� ������', '%1 ������ ����������', '�������� �������', '�����', '���������', '�������', '�������� %1 �� %2', '���', '����������', '%1 ��', '%1 ��', '%1 �����', '����������', '��������', '�������', '����� �� �������� %1', '��������:', '������ �� �� �������', '����� ���������� �� �������������', '������', '���������', '������ ������ � * �� ������������', '������ ������ �� ���� �������� � ��������� �����.', '���������� ���������', '������� ��', '����', '������', '�������� ��� �� �������', '��������� �����������?', '����������� �� ������� ���/����', '������', 'm3u', 'asx (WMA)', '��� ������������ ����, ��������� ���: %1', '�������� ������������?', '������ ��������', '������ URL ���������', '��������� ������ ������� �� �������', '���� ��� ���� �� � ��������.', '��������� � ������!', '��������', 'URL �� ����������', '������ ���� �� ��������', '���', '������ ��� ��� ������!', '���� ������ �� ������: %1', '������ �������� RSS feed?', '���� �������� �� ������!', '������� �� ��� � ���!', '��������������� ��� � �����!', '�� �� ��������� ����� ������� �� ���� �����?', '��������� �� �������� �� ������ �����: %1/%2', '�� � ������� "%1", �� �� �� �� � ������?', '��/�� ���� (������)', '������ � ��������� �������, ���� �������� ������.', '���������� ������� �� ������', '������ � ������������', '��� ��������', '��������', '��� �� ��������', '������ ��� �� ��������!', '�������� �� ������������ ��� �����������.', '��� ����������', '������� ������������ �� ���������(�)', '���������� ������ �� ������ (��)', '������ ��������� ����. ������! (%1��, � ���� ����. %2��)', '������� ����������', '�������� LAME �����', 'Transcode', 'httpQ', '������ ��� ����������� � httpQ ������� (%1).', '��������� ���� �� ������ �����?', '�������������� ������ �� ���� ������� ������ ��������.', '�������', '����� ������', '������:', '����. ������ �� ��������� ������', '���������', '��������', 'AJAX Prototype URL', '�����', 'Loop', '���������� - ����� �������� ��� �������������� ��.', '����');

$klang[23] = array("Polish", "ISO-8859-2", "Polski", "Popularne", "Nowo&#347;ci", "Wyszukaj", "pokazano tylko %1", "sek", "Wyniki wyszukiwania: \'%1\'", "znaleziono", "Nic.", "aktualizacja opcji wyszukiwania bazy", "Usun&#261;&#263; nieu&#380;ywane wpisy?", "Odbudowa&#263; ID3?", "Tryb usuwania b&#322;&#281;d�w?", "Aktualizacja", "Anuluj", "aktualizacja wyszukiwania bazy", "Znaleziono %1 plik�w", "Nie mo&#380;na okre&#380;li&#263; po&#322;o&#380;enia pliku: %1", "Instalacja: %1 - Aktualizacja: %2, badanie:", "Skanowanie:", "Niepowodzenie - pytanie: %1", "Nie mo&#380;na odczyta&#263; tego pliku: %1. Pomini&#281;cie.", "Usuni&#281;to: %1", "Wstawiono %1, uaktualniono %2, usuni&#281;to %3 gdzie %4 uszkodzonych i %5 pomini&#281;to z powodu %6 plik�w - %7 sek - %8 zaznaczonych do usuni&#281;cia. ", "Sko&#324;czone", "Zamknij", "Nie mo&#380;na znale&#378;&#263; tutaj &#380;adnych plik�w: \"%1\"", "Logowanie kPlaylist", "Lista album�w dla wykonawcy: %1", "popularny wyb�r %1", "nie wybrana melodia. Playlista nie zaktualizowana.", "Playlista zaktualizowana!", "Wstecz", "Playlista dodana!", "Pami&#281;taj o prze&#322;adowaniu strony", "login:", "has&#322;o:", "Uwaga! To nie jest strona publiczna. Wszystkie akcje s&#261; rejestrowane.", "Login", "Do zalogowania wymagany jest SSL", "Odgrywaj", "Usu&#324;", "Wsp�lny:", "Zapisz", "Kontrola playlist: \"%1\" - %2 tytu&#322;y", "Edytor", "Przegl&#261;darka", "Zaznacz", "Ci&#261;g", "Status", "Info", "Kasuj", "Nazwa", "Podsumowanie:", "B&#322;&#261;d", "Akcja na zaznaczonych:", "Kolejno&#347;&#263;", "edytuj playlist&#281;", "Usu&#324; ten zapis", "dodaj playlist&#281;", "Nazwa:", "Utw�rz", "Odtwarzaj:", "Plik", "Album", "Wszystko", "Wybrane", "dodaj", "odtwarzaj", "edytuj", "nowe", "Zaznacz:", "Kontrol odtwarzania:", "Playlista:", "popularne numery", "Tw�j identyfikator:", "(sprawd&#378; czy s&#261; poprawki)", "Stona domowa", "tylko id3", "album", "tytu&#322;", "wykonawca", "Popularne albumy wykonawcy", "widok", "Wsp�lne playlisty", "U&#380;ytkownicy", "Panel administratora", "Nowo&#347;ci", "Popularne", "Wylogowanie", "Opcje", "Sprawd&#378;", "M�j", "edytuj u&#380;ytkownika", "nowy u&#380;ytkownik", "Pe&#322;na nazwa", "Login", "Zmieni&#263; has&#322;o?", "Has&#322;o", "Komentarz", "poziom dost&#281;pu", "W&#322;&#261;czony", "Wy&#322;&#261;czony", "Usu&#324; u&#380;ytkownika", "Wyloguj u&#380;ytkownika", "Od&#347;wie&#380;", "Nowy u&#380;ytkownik", "usu&#324;", "wyloguj", "Mo&#380;liwo&#347;&#263; u&#380;ycia EXTM3U?", "Ile pokaza&#263; wierszy (popularne/nowe)", "Max przeszukiwanych wierszy", "Resetuj", "Otw�rz katalog", "Id&#378; do katalogu: %1", "Pobierz", "Id&#378; katalog wy&#380;ej", "Id&#378; do katalogu g&#322;�wnego", "Sprawd&#378; czy s&#261; poprawki", "u&#380;ytkownicy", "J&#281;zyk", "opcje", "Inicjowanie", "Mieszanie:", "Ustawienia", "Katalog bazowy", "Lokalizacja strumienia", "Domy&#347;lny j&#281;zyk", "System Windows?", "Wymagane HTTPS", "Wszyscy mog&#261; ogl&#261;da&#263;", "Wszyscy mog&#261; &#347;ci&#261;ga&#263;", "Maksymalny czas sesji", "Raportuj b&#322;&#281;dne pr�by logowania", "W&#322;&#261;cz wstrzymywanie - najlepsze listy plik�w", "Playlista nie mo&#380;e by&#263; dodana!", "Administrator", "Zaloguj z HTTPS aby zmieni&#263;!", "Aktywny strumie&#324; silnika", "Tytu&#322;", "Wykonawca", "Album", "Komentarz", "Rok", "&#346;cie&#380;ka", "Rodzaj", "nie ustawione", "Max pr&#281;dko&#347;&#263; &#347;ci&#261;gania (kbps)", "U&#380;ytkownik", "%1 minuty - %2 tytu&#322;y", "%1 kbit %2 minuty", "Rodzaj listy: %1", "Id&#378;", "%1d %2h %3m czas odtwarzania %4 plik�w %5 MB", "Nie zwi&#261;zany z tymi zasobami", "Has&#322;o zmienione!", "Wy&#347;lij", "Prosz&#281; wykona&#263; zaznaczenie!", "Co to jest aktualizacja?", "Kliknij tutaj aby uzyska&#263; pomoc", "U&#380;y&#263; zewn&#281;trznych obrazk�w?", "&#346;cie&#380;ka zewn&#281;trznych obrazk�w", "Bie&#380;&#261;ce has&#322;o", "Bie&#380;&#261;ce has&#322;o nie jest w&#322;a&#347;ciwe!", "Preferowany archiwizator", "Archiwum nie mo&#380;e zosta&#263; utworzone", "Prawdopodobnie znaleziono duplikat pliku: %1 - %2", "Na pewno usun&#261;&#263; pleylist&#281;?", "Alfabetycznie", "Losowo", "Sortuj", "Oryginalnie", "U&#380;yj javascript", "Czy jeste&#347; pewny, &#380;e chcesz usun&#261;&#263; tego uzytkownika?", "Przegl&#261;daj histori&#281;", "historia", "Wiersze", "Zewn&#281;trzny plik CSS");

$klang[24] = array('Lithuanian', 'ISO-8859-13', 'Lietuvi�kai', 'Da&#254;niausiai klausomi', 'Nauja', 'Paie&#240;ka', '(rodoma tiktai %1)', 'sec', 'Paie&#65533;kos rezultatai: \'%1\'', 'rasta', 'N&#279;ra.', 'atnaujinti pai&#65533;kos duomen&#371; baz&#279;s nustatymus', 'I&#65533;trinti nereikalingus &#303;ra&#65533;us?', 'Atnaujinti ID3?', 'Su klaid&#371; aptikimu?', 'Atnaujinti', 'Nutraukti', 'atnaujinti paie&#65533;kos duomen&#371; baz&#281;', 'Rasta %1 fail&#371;.', 'Neina nustatyti &#65533;io failo: %1, praleid&#65533;iam.', '&#302;diegta: %1 - Atnaujinti: %2, skenuoti:', 'Skenuoti:', 'Nepavykusi u&#65533;klausa: %1', 'Neina perskaityt &#65533;io failo: %1. Praleid&#65533;iam.', 'Pa&#65533;alinta: %1', '&#302;traukta %1, atnaujinta %2, i&#65533;trinta %3 kur %4 nepavyk&#281; ir %5 praleisti i&#65533; %6 fail&#371; - %7 sec - %8 pa&#65533;ym&#279;ti i&#65533;trynimui.', 'Atlikta', 'U&#65533;daryti', 'Nepavyko rasti joki&#371; fail&#371; &#269;ia: "%1"', 'kPlaylist Prisijungimas', 'Album&#371; s&#261;ra&#65533;as pagal autori&#371;: %1', 'Populiariausi %1', 'Nepa&#65533;im&#279;jote n&#279; vieno failo. Playlist\'as neatnaujintas.', 'Playlist\'as atnaujintas!', 'Atgal', '&#302;trauktas Playlist\'as.', 'Neu&#65533;mir&#65533;kite perkrauti puslapio.', 'vartotojo vardas:', 'slapta&#65533;odis:', 'D&#279;mesio! Tai ne vie&#65533;as interneto puslapis. Visi veiksmai yra &#303;ra&#65533;omi.', 'Prisijungti', 'SSL reikia norint prisijungti.', 'Groti', 'I&#65533;trinti', 'Vie&#65533;i:', 'I&#65533;saugoti', 'Redaguojamas Playlist\'as: "%1" - %2 pavadinimai', 'Redaktorius', 'Per&#65533;valga', 'Pa&#65533;ym&#279;ti', 'T&#281;sinys', 'Pad&#279;tis', 'Info', 'I&#65533;trinti', 'Vardas', 'I&#65533;viso:', 'Klaida', 'Atlikti veiksm&#261; su pa&#65533;ym&#279;tais:', 'Eil&#279;s tvarka:', 'redaguoti playlist\'&#261;', 'I&#65533;trinti &#65533;&#303; &#303;ra&#65533;&#261;', '&#303;traukti playlist\'&#261;', 'Vardas:', 'Sukurti', 'Groti:', 'Failas', 'Albumas', 'Visi', 'Pa&#65533;ym&#279;tus', '&#303;traukti', 'groti', 'redaguoti', 'naujas', 'Pa&#65533;ym&#279;ti:', 'Grojimo valdymas:', 'Playlist\'as:', 'Pasirink&#371; numeravimas', 'First IT si&#363;lo:', '(patikrinti ar naudoji naujausi&#261; versij&#261;)', 'J&#363;s&#371; puslapis', 'tiktai id3', 'albumas', 'pavainimas', 'atlik&#279;jas', 'Pa&#65533;ym&#279;ti atlik&#279;jo album&#261;', 'per&#65533;i&#363;r&#279;ti', 'Vie&#65533;i playlist\'ai', 'Vartotojai', 'Admin valdymas', 'Naujienos', 'Da&#65533;niausiai', 'Atsijungti', 'Nustatymai', 'Pa&#65533;ym&#279;ti', 'Mano', 'redaguoti vartotoj&#261;', 'naujas vartotojas', 'Pilnas vardas', 'Vartotojo vardas', 'Pakeisti slapta&#65533;od&#303;?', 'Slapta&#65533;odis', 'Komentaras', 'Vartotojo lygis', '&#302;jungta', 'I&#65533;jungta', 'I&#65533;trinti vartotoj&#261;', 'Atjungti vartotoj&#261;', 'Perkrauti', 'Naujas vartotojas', 'i&#65533;trinti', 'atjungti', 'Naudoti EXTM3U?', 'Kiek rodyti stulpeli&#371;?', 'Daugiausia paie&#65533;kos eilu&#269;i&#371;', 'Atstatyti', 'Atidaryti direktorij&#261;', 'Eiti &#303;: %1', 'Parsisi&#371;sti', 'Vienu ejimu atgal', '&#302; root direktorij&#261;', 'Patikrinti atnaujinim&#261;', 'vartotojai', 'Kalba', 'nustatymai', 'Pakrautas', 'Mai&#65533;yti:', 'Nustatymai', 'Pradin&#279; direktorija', 'Stream vieta', 'Pagrindin&#279; kalba', 'Windows sistema', 'Reikalauti HTTPS', 'Leisti paie&#65533;k&#261;', 'Leisti parsisiuntimus', 'Session timeout', 'Prane&#65533;ti apie nepavykusius prisijungimus', 'Palaukite - sudaromas fail&#371; s&#261;ra&#65533;as', 'Playlist\'o neina &#303;traukti', 'Admin', 'Prisijunkite su HTTPS nor&#279;dami k&#261; nors pakeisti!', 'Leisti streming', 'Pavadinimas', 'Atlik&#279;jas', 'Albumas', 'Komentaras', 'metai', 'Takelis', '&#65533;anras', 'nenustatyta', 'Did&#65533;iausias siuntimosi greitis', 'Vartotojas', '%1 min - %2 pavadinimai', '%1 kbit %2 min', '&#65533;anr&#371; s&#261;ra&#65533;as: %1', 'Eiti', '%1 d %2h %3m grojimo laikas %4 fail7 %5 mb', 'N&#279;ra susijusi&#371; resurs&#371;.', 'Slapta&#65533;odis pakeistas.', 'Prisiregistruoti', 'Pasirinkite!', 'Kas yra - Atnaujinimas?', 'Pagalba', 'Naudoti i&#65533;orinius paveiksliukus', 'I&#65533;orini&#371; paveiksliuk&#371; vieta', 'Dabartrinis slapta&#65533;odis', 'Slapta&#65533;od&#65533;iai nesutampa', 'Naudojamas archyvatorius', 'Nepavyko sudaryti archyvo', 'Grei&#269;iausiai rasti du vienodi failai: "%1" "%2"', 'I&#65533;trinti playlist\'&#261;?', 'Alfabeti&#65533;kai', 'Atsitiktinai', 'Sutraukti', 'Orginaliai', 'Naudoti javascript', 'Ar tikrai norite i&#65533;trinti &#65533;&#303; vartotoj&#261;?', 'Per&#65533;i&#363;r&#279;ti istorij&#261;', 'istorija', 'Eilut&#279;s', 'I&#65533;orinis CSS failas', 'I&#65533;trinti dublikatus', 'Taip', 'Klaida', 'Stream', '(rodyti kaip)', 'failai', 'albumai', '%1d %2h %3m %4s', 'Pagrindinis', 'Redaguoti', 'Fail&#371; palaikymas', 'Paspauskite ant ? kad gaut pagalb&#261;.', 'Automatinis Duomen&#371; baz&#279;s atnaujinimas', 'Nusi&#371;sti failo pl&#279;tin&#303;', 'Leisti neautorizuotus streamus', '&#302;traukti headerius', 'I&#65533;orinis javascript', 'Puslapis', 'Rodyti Keuteq duoda tau', 'Rodyti atnaujinim&#261;', 'Rodyti statistik&#261;', '&#302;ra&#65533;yti ID3v2 su streamu', 'Leisti vartotoj&#371; prisiregistravim&#261;', 'Fail&#371; tipai', 'Taip', 'Ne', 'Pl&#279;tinys', 'MIME', '&#302;traukti M3U', 'redaguoti fail&#371; tip&#261;', 'Tikrai?', 'Optimistinis fail&#184; patikrinimas', 'Sumai&#240;yti', 'Metodas', 'Playlistas', 'N&#235;ra, tiesiogiai', 'M&#235;gstamiausi', 'Nerasta nei vieno paspaudimo', 'Vis&#184; laik&#184; hitai', 'U&#254;sisakyti', '&#193;jungti LAME palaikym&#224;?', 'I&#240;jungta', 'Lesti naudotis LAME?', 'El. pa&#240;tas', 'Lesiti si&#184;sti failus el. pa&#240;tu?', 'SMTP serveris', 'SMTP portas', 'Kam si&#184;sti', '&#222;inut&#235;', 'Si&#184;sti', 'Lai&#240;kas i&#240;si&#184;stas!', 'Aktyvuoti atsiuntimus', 'Atsiuntim&#184; direktorija', 'Aktivuoti mp3pa&#240;t&#224;', 'Atsi&#184;sti', 'Failas atsi&#184;stas', 'Nepavyko atsi&#184;sti failo!', 'Cookies palaikymas turi b&#251;ti &#225;jungtas jei norite prisijungti!', 'Periodas', 'kadanors', '&#240;i&#224; savait&#191;', '&#240;&#225; m&#235;nes&#225;', 'praeit&#224; m&#235;nes&#225;', 'paspaudimai', 'LAME komanda', 'Rodyti albumo vir&#240;el&#225;', 'Albumo failai', 'Pakeisti albumo paveiksliuk&#184; dyd&#225;', 'Albumo auk&#240;tis', 'Albumo plotis', 'Siuntimo el. pa&#240;tu metodas', 'Tiesiogiai', 'Netiesiogiai', 'Palaukti', '&#193;veskite teising&#224; el. pa&#240;to adres&#224; nustatymuose.', 'Playlist\'as inline?', 'Rodyti album&#224; i&#240; nuorodos?', 'Albumo nuoroda', 'Nepavyko nusi&#184;sti!', 'Vartotojas &#225;trauktas!', 'Archyv&#224; suk&#251;r&#235;', 'Archyvas i&#240;trintas.', 'Vartotojo apra&#240;ymas atnaujintas!', 'Atitikmenys', '%1 &#225;ra&#240;&#184;', 'Pri&#235;jimas prie log&#184;', 'Skaitoma', 'Suarchyvuota', 'Suvestin&#235;', '&#193;vesta %1 - %2', 'daugiau', 'Publikuoti', '%1 mb', '%1 kb', '%1 bait&#184;', 'Pasikartojantis', 'Atgal', 'Pirmyn', 'Eiti &#225; puslap&#225; %1', 'Puslapis:', 'Niekados negrotas', 'Administruojama registracija', 'Laukia', 'aktyvuoti', 'Laukai pa&#254;ym&#235;ti * yra privalomi.', 'J&#251;s&#184; registracija bus per&#254;i&#251;r&#235;ta ir aktyvuota administratoriaus.', 'Paskutiniai grojimai', 'Prisiminti prisijungimo informacij&#224;', 'Stilius', 'surasti', '�vesti paie�kos raktus', 'Naudoti pa�ym�tus?', 'Dainos trukm� min/max', 'Minut�s', 'm3u', 'asx (WMA)', 'Jei atnaujinimas sustoja, paspauskite �ia: %1', 'Naudoti symlinks?', 'Bylos �ablonas', 'Naudoti URL apsaugas', '�kelti s�ra��', 'Neleid�iamas bylos tipas ,', 'Playlist\'as tu��ias!');

$klang[25] = array("Thai", "ISO-8859-11", "&#3652;&#3607;&#3618;", "&#3617;&#3634;&#3651;&#3627;&#3617;&#3656;", "&#3617;&#3634;&#3649;&#3619;&#3591;", "&#3588;&#3657;&#3609;&#3627;&#3634;", "(&#3649;&#3626;&#3604;&#3591;&#3648;&#3593;&#3614;&#3634;&#3632; %1)", "&#3623;&#3636;&#3609;&#3634;&#3607;&#3637;", "&#3612;&#3621;&#3585;&#3634;&#3619;&#3588;&#3657;&#3609;&#3627;&#3634; :\'%1\'", "&#3614;&#3610;", "&#3652;&#3617;&#3656;", "&#3611;&#3619;&#3633;&#3610;&#3611;&#3619;&#3640;&#3591;&#3605;&#3633;&#3623;&#3648;&#3621;&#3639;&#3629;&#3585;&#3600;&#3634;&#3609;&#3586;&#3657;&#3629;&#3617;&#3641;&#3621;&#3626;&#3635;&#3627;&#3619;&#3633;&#3610;&#3588;&#3657;&#3609;&#3627;&#3634;", "&#3621;&#3610;&#3648;&#3619;&#3588;&#3588;&#3629;&#3619;&#3660;&#3604;&#3607;&#3637;&#3656;&#3652;&#3617;&#3656;&#3648;&#3588;&#3618;&#3651;&#3594;&#3657;", "&#3626;&#3619;&#3657;&#3634;&#3591; ID3 &#3651;&#3627;&#3617;&#3656;", "&#3648;&#3611;&#3636;&#3604; Debug Mode", "&#3611;&#3619;&#3633;&#3610;&#3611;&#3619;&#3640;&#3591;", "&#3618;&#3585;&#3648;&#3621;&#3636;&#3585;", "&#3611;&#3619;&#3633;&#3610;&#3611;&#3619;&#3640;&#3591;&#3600;&#3634;&#3609;&#3586;&#3657;&#3629;&#3617;&#3641;&#3621;&#3651;&#3609;&#3585;&#3634;&#3619;&#3588;&#3657;&#3609;&#3627;&#3634;", "&#3614;&#3610;&#3607;&#3633;&#3657;&#3591;&#3627;&#3617;&#3604; %1 &#3652;&#3615;&#3621;&#3660;", "&#3652;&#3617;&#3656;&#3626;&#3634;&#3617;&#3634;&#3619;&#3606;&#3619;&#3632;&#3610;&#3640;&#3652;&#3615;&#3621;&#3660; %1 , &#3586;&#3657;&#3634;&#3617;&#3652;&#3611;", "&#3605;&#3636;&#3604;&#3605;&#3633;&#3657;&#3591;: %1 -&#3611;&#3619;&#3633;&#3610;&#3611;&#3619;&#3640;&#3591;: %2 ,&#3605;&#3619;&#3623;&#3592;&#3627;&#3634;", "&#3605;&#3619;&#3623;&#3592;&#3627;&#3634;", "&#3585;&#3634;&#3619;&#3588;&#3657;&#3609;&#3627;&#3634;&#3612;&#3636;&#3604;&#3614;&#3621;&#3634;&#3604; :%1", "&#3652;&#3617;&#3656;&#3626;&#3634;&#3617;&#3634;&#3619;&#3606;&#3629;&#3656;&#3634;&#3609;&#3652;&#3615;&#3621;&#3660; : %1 &#3586;&#3657;&#3634;&#3617;&#3652;&#3611;", "&#3621;&#3610; %1", "&#3648;&#3614;&#3636;&#3656;&#3617; %1 ,&#3611;&#3619;&#3633;&#3610;&#3611;&#3619;&#3640;&#3591; %2,&#3621;&#3610; %3,&#3607;&#3637;&#3656; %4,&#3612;&#3636;&#3604;&#3614;&#3621;&#3634;&#3604;&#3649;&#3621;&#3632; %5 ,&#3586;&#3657;&#3634;&#3617;&#3652;&#3611; %6 &#3652;&#3615;&#3621;&#3660; %7 &#3623;&#3636;&#3609;&#3634;&#3607;&#3637; %8 &#3607;&#3635;&#3648;&#3588;&#3619;&#3639;&#3656;&#3629;&#3591;&#3627;&#3617;&#3634;&#3618;&#3648;&#3614;&#3639;&#3656;&#3629;&#3621;&#3610;", "&#3648;&#3619;&#3637;&#3618;&#3610;&#3619;&#3657;&#3629;&#3618;", "&#3611;&#3636;&#3604;", "&#3652;&#3617;&#3656;&#3614;&#3610;&#3652;&#3615;&#3621;&#3660;&#3652;&#3604;&#3654;&#3607;&#3637;&#3656;&#3617;&#3637;&#3626;&#3656;&#3623;&#3609;&#3611;&#3619;&#3632;&#3585;&#3629;&#3610; \"%1\"", "&#3648;&#3586;&#3657;&#3634;&#3626;&#3641;&#3656;&#3619;&#3632;&#3610;&#3610;", "&#3619;&#3634;&#3618;&#3585;&#3634;&#3619;&#3629;&#3633;&#3621;&#3610;&#3633;&#3617;&#3626;&#3635;&#3627;&#3619;&#3633;&#3610;&#3624;&#3636;&#3621;&#3611;&#3636;&#3609; : %1", "&#3606;&#3641;&#3585;&#3648;&#3621;&#3639;&#3629;&#3585;&#3617;&#3634;&#3585;&#3607;&#3637;&#3656;&#3626;&#3640;&#3604; %1", "&#3652;&#3617;&#3656;&#3614;&#3610;&#3619;&#3634;&#3618;&#3585;&#3634;&#3619;&#3607;&#3637;&#3656;&#3648;&#3621;&#3639;&#3629;&#3585; &#3619;&#3634;&#3618;&#3585;&#3634;&#3619;&#3648;&#3621;&#3656;&#3609;&#3652;&#3617;&#3656;&#3652;&#3604;&#3657;&#3617;&#3637;&#3585;&#3634;&#3619;&#3611;&#3619;&#3633;&#3610;&#3611;&#3619;&#3640;&#3591;", "&#3611;&#3619;&#3633;&#3610;&#3611;&#3619;&#3640;&#3591;&#3619;&#3634;&#3618;&#3585;&#3634;&#3619;&#3648;&#3621;&#3656;&#3609;&#3649;&#3621;&#3657;&#3623;", "&#3618;&#3657;&#3629;&#3618;&#3585;&#3621;&#3633;&#3610;", "&#3648;&#3614;&#3636;&#3656;&#3617;&#3648;&#3586;&#3657;&#3634;&#3619;&#3634;&#3618;&#3585;&#3634;&#3619;&#3648;&#3621;&#3656;&#3609;", "&#3629;&#3618;&#3656;&#3634;&#3621;&#3639;&#3617;&#3607;&#3637;&#3656;&#3592;&#3632;&#3648;&#3611;&#3636;&#3604;&#3627;&#3609;&#3657;&#3634;&#3605;&#3656;&#3634;&#3591;&#3609;&#3637;&#3657;&#3651;&#3627;&#3617;&#3656;&#3629;&#3637;&#3585;&#3588;&#3619;&#3633;&#3657;&#3591;", "&#3648;&#3586;&#3657;&#3634;&#3626;&#3641;&#3656;&#3619;&#3632;&#3610;&#3610; :", "&#3619;&#3627;&#3633;&#3626;&#3612;&#3656;&#3634;&#3609;", "&#3627;&#3617;&#3634;&#3618;&#3648;&#3627;&#3605;&#3640; : &#3648;&#3623;&#3655;&#3610;&#3648;&#3614;&#3592;&#3627;&#3609;&#3657;&#3634;&#3627;&#3609;&#3637;&#3657;&#3617;&#3636;&#3651;&#3594;&#3656;&#3627;&#3609;&#3657;&#3634;&#3626;&#3634;&#3608;&#3634;&#3619;&#3603;&#3632;&#3585;&#3634;&#3585;&#3619;&#3632;&#3607;&#3635;&#3607;&#3635;&#3607;&#3633;&#3657;&#3591;&#3627;&#3617;&#3604;&#3592;&#3632;&#3606;&#3647;&#3585;&#3610;&#3633;&#3609;&#3607;&#3638;&#3585;&#3652;&#3623;&#3657;", "&#3648;&#3586;&#3657;&#3634;&#3626;&#3641;&#3656;&#3619;&#3632;&#3610;&#3610;", "&#3605;&#3657;&#3629;&#3591;&#3585;&#3634;&#3619; SSL &#3648;&#3614;&#3639;&#3656;&#3629;&#3585;&#3634;&#3619;&#3648;&#3586;&#3657;&#3634;&#3626;&#3641;&#3656;&#3619;&#3632;&#3610;&#3610;", "&#3648;&#3621;&#3656;&#3609;", "&#3621;&#3610;", "&#3629;&#3609;&#3640;&#3597;&#3634;&#3605;&#3651;&#3627;&#3657;&#3612;&#3641;&#3657;&#3629;&#3639;&#3656;&#3609;&#3651;&#3594;&#3657;&#3604;&#3657;&#3623;&#3618;&#3652;&#3604;&#3657;", "&#3610;&#3633;&#3609;&#3607;&#3638;&#3585;", "&#3588;&#3623;&#3610;&#3588;&#3640;&#3617;&#3619;&#3634;&#3618;&#3585;&#3634;&#3619;&#3648;&#3621;&#3656;&#3609; : \"%1\" - %2 &#3594;&#3639;&#3656;&#3629;", "&#3585;&#3634;&#3619;&#3649;&#3585;&#3657;&#3652;&#3586;", "&#3604;&#3641;", "&#3648;&#3621;&#3639;&#3629;&#3585;", "&#3621;&#3635;&#3604;&#3633;&#3610;", "&#3626;&#3606;&#3634;&#3609;&#3632;", "&#3619;&#3634;&#3618;&#3621;&#3632;&#3648;&#3629;&#3637;&#3618;&#3604;", "&#3621;&#3610;", "&#3594;&#3639;&#3656;&#3629;", "&#3607;&#3633;&#3657;&#3591;&#3627;&#3617;&#3604; :", "&#3612;&#3636;&#3604;&#3614;&#3621;&#3634;&#3604;", "&#3585;&#3634;&#3619;&#3585;&#3619;&#3632;&#3607;&#3635;&#3610;&#3609;&#3585;&#3634;&#3619;&#3648;&#3621;&#3639;&#3629;&#3585;", "&#3621;&#3635;&#3604;&#3633;&#3610;&#3607;&#3637;&#3656; :", "&#3649;&#3585;&#3657;&#3652;&#3586;&#3619;&#3634;&#3618;&#3585;&#3634;&#3619;&#3648;&#3621;&#3656;&#3609;", "&#3621;&#3610;&#3607;&#3633;&#3657;&#3591;&#3627;&#3617;&#3604;", "&#3648;&#3614;&#3636;&#3656;&#3617;&#3648;&#3586;&#3657;&#3634;&#3626;&#3641;&#3656;&#3619;&#3634;&#3618;&#3585;&#3634;&#3619;&#3648;&#3621;&#3656;&#3609;", "&#3594;&#3639;&#3656;&#3629; :", "&#3626;&#3619;&#3657;&#3634;&#3591;", "&#3648;&#3621;&#3656;&#3609; :", "&#3652;&#3615;&#3621;&#3660;", "&#3629;&#3633;&#3621;&#3610;&#3633;&#3617;", "&#3607;&#3633;&#3657;&#3591;&#3627;&#3617;&#3604;", "&#3606;&#3641;&#3585;&#3648;&#3621;&#3639;&#3629;&#3585;", "&#3648;&#3614;&#3636;&#3656;&#3617;", "&#3648;&#3621;&#3656;&#3609;", "&#3649;&#3585;&#3657;&#3652;&#3586;", "&#3651;&#3627;&#3617;&#3656;", "&#3648;&#3621;&#3639;&#3629;&#3585; :", "&#3588;&#3623;&#3610;&#3588;&#3640;&#3617;&#3585;&#3634;&#3619;&#3648;&#3621;&#3656;&#3609; :", "&#3619;&#3634;&#3618;&#3585;&#3634;&#3619;&#3648;&#3621;&#3656;&#3609; :", "&#3627;&#3617;&#3634;&#3618;&#3648;&#3621;&#3586;&#3607;&#3637;&#3656;&#3606;&#3641;&#3585;&#3648;&#3621;&#3639;&#3629;&#3585;&#3617;&#3634;&#3585;&#3607;&#3637;&#3656;&#3626;&#3640;&#3604;", "&#3588;&#3635;&#3649;&#3609;&#3632;&#3609;&#3635;&#3592;&#3634;&#3585; First IT", "&#3605;&#3619;&#3623;&#3592;&#3626;&#3629;&#3610;&#3648;&#3614;&#3639;&#3656;&#3629;&#3611;&#3619;&#3633;&#3610;&#3611;&#3619;&#3640;&#3591;&#3619;&#3640;&#3656;&#3609;&#3586;&#3629;&#3591;&#3595;&#3629;&#3615;&#3607;&#3660;&#3649;&#3623;&#3619;&#3660;", "&#3627;&#3609;&#3657;&#3634;&#3627;&#3621;&#3633;&#3585;", "&#3648;&#3593;&#3614;&#3634;&#3632; ID3", "&#3629;&#3633;&#3621;&#3610;&#3633;&#3617;", "&#3594;&#3639;&#3656;&#3629;&#3629;&#3633;&#3621;&#3610;&#3633;&#3617;", "&#3624;&#3636;&#3621;&#3611;&#3636;&#3609;", "&#3629;&#3633;&#3621;&#3611;&#3633;&#3617;&#3607;&#3637;&#3656;&#3606;&#3641;&#3585;&#3648;&#3621;&#3639;&#3629;&#3585;&#3607;&#3634;&#3585;&#3607;&#3637;&#3656;&#3626;&#3640;&#3604;&#3592;&#3634;&#3585;&#3624;&#3636;&#3621;&#3611;&#3636;&#3609;", "&#3648;&#3586;&#3657;&#3634;&#3594;&#3617;", "&#3619;&#3634;&#3618;&#3585;&#3634;&#3619;&#3648;&#3621;&#3656;&#3609;&#3607;&#3637;&#3656;&#3651;&#3627;&#3657;&#3651;&#3594;&#3657;&#3652;&#3604;&#3657;", "&#3612;&#3641;&#3657;&#3651;&#3594;&#3657;", "&#3626;&#3656;&#3623;&#3609;&#3612;&#3641;&#3657;&#3604;&#3641;&#3649;&#3621;&#3619;&#3632;&#3610;&#3610;", "&#3617;&#3634;&#3651;&#3627;&#3617;&#3656;", "&#3617;&#3634;&#3649;&#3619;&#3591;", "&#3629;&#3629;&#3585;&#3592;&#3634;&#3585;&#3619;&#3632;&#3610;&#3610;", "&#3605;&#3633;&#3623;&#3648;&#3621;&#3639;&#3629;&#3585;", "&#3605;&#3619;&#3623;&#3592;&#3626;&#3629;&#3610;", "&#3588;&#3635;&#3626;&#3633;&#3656;&#3591;&#3629;&#3639;&#3656;&#3609;", "&#3649;&#3585;&#3657;&#3652;&#3586;&#3612;&#3641;&#3657;&#3651;&#3594;&#3657;", "&#3648;&#3614;&#3636;&#3656;&#3617;&#3612;&#3641;&#3657;&#3651;&#3594;&#3657;", "&#3594;&#3639;&#3656;&#3629;&#3592;&#3619;&#3636;&#3591;", "&#3594;&#3639;&#3656;&#3629;&#3648;&#3614;&#3639;&#3656;&#3629;&#3648;&#3586;&#3657;&#3634;&#3619;&#3632;&#3610;&#3610;", "&#3648;&#3611;&#3621;&#3637;&#3656;&#3618;&#3609;&#3619;&#3627;&#3633;&#3626;&#3612;&#3656;&#3634;&#3609;?", "&#3619;&#3627;&#3633;&#3626;&#3612;&#3656;&#3634;&#3609;", "&#3586;&#3657;&#3629;&#3648;&#3626;&#3609;&#3629;&#3649;&#3609;&#3632;", "&#3619;&#3632;&#3604;&#3633;&#3610;&#3651;&#3609;&#3585;&#3634;&#3619;&#3651;&#3594;&#3657;&#3591;&#3634;&#3609;", "&#3585;&#3635;&#3621;&#3633;&#3591;&#3651;&#3594;&#3657;&#3591;&#3634;&#3609;&#3629;&#3618;&#3641;&#3656;", "&#3652;&#3617;&#3656;&#3652;&#3604;&#3657;&#3651;&#3594;&#3657;", "&#3621;&#3610;&#3612;&#3641;&#3657;&#3651;&#3594;&#3657;", "&#3609;&#3635;&#3612;&#3641;&#3657;&#3651;&#3594;&#3657;&#3629;&#3629;&#3585;&#3592;&#3634;&#3585;&#3585;&#3634;&#3619;&#3651;&#3594;&#3657;&#3591;&#3634;&#3609;", "refresh", "&#3648;&#3614;&#3636;&#3656;&#3617;&#3612;&#3641;&#3657;&#3651;&#3594;&#3657;&#3651;&#3627;&#3617;&#3656;", "&#3621;&#3610;&#3629;&#3629;&#3585;&#3592;&#3634;&#3585;&#3619;&#3632;&#3610;&#3610;", "&#3629;&#3629;&#3585;&#3592;&#3634;&#3585;&#3619;&#3632;&#3610;&#3610;", "&#3651;&#3594;&#3657;&#3588;&#3640;&#3603;&#3626;&#3617;&#3610;&#3633;&#3605;&#3636; EXTM3U", "&#3592;&#3635;&#3609;&#3623;&#3609;&#3649;&#3606;&#3623;&#3607;&#3637;&#3656;&#3605;&#3657;&#3629;&#3591;&#3585;&#3634;&#3619;&#3651;&#3627;&#3657;&#3649;&#3626;&#3604;&#3591; (&#3617;&#3634;&#3651;&#3627;&#3617;&#3656;/&#3617;&#3634;&#3649;&#3619;&#3591;)", "&#3592;&#3635;&#3609;&#3623;&#3609;&#3649;&#3606;&#3623;&#3626;&#3641;&#3591;&#3626;&#3640;&#3604;&#3651;&#3609;&#3585;&#3634;&#3619;&#3588;&#3657;&#3609;&#3627;&#3634;", "&#3618;&#3585;&#3648;&#3621;&#3636;&#3585;", "&#3648;&#3611;&#3636;&#3604; Directory", "&#3652;&#3611; Directory  : %1", "&#3604;&#3634;&#3623;&#3609;&#3660;&#3650;&#3627;&#3621;&#3604;", "&#3586;&#3638;&#3657;&#3609;&#3652;&#3611; 1 &#3619;&#3632;&#3604;&#3633;&#3610;", "&#3652;&#3611;&#3607;&#3637;&#3656; Directory &#3610;&#3609;&#3626;&#3640;&#3604;", "&#3605;&#3619;&#3623;&#3592;&#3626;&#3629;&#3610;&#3648;&#3614;&#3639;&#3656;&#3629;&#3611;&#3619;&#3633;&#3610;&#3611;&#3619;&#3640;&#3591;&#3619;&#3640;&#3656;&#3609;&#3586;&#3629;&#3591;&#3595;&#3629;&#3615;&#3607;&#3660;&#3649;&#3623;&#3619;&#3660;", "&#3612;&#3641;&#3657;&#3651;&#3594;&#3657;", "&#3616;&#3634;&#3625;&#3634;", "&#3605;&#3633;&#3623;&#3648;&#3621;&#3639;&#3629;&#3585;", "&#3648;&#3619;&#3636;&#3656;&#3617;&#3605;&#3657;&#3609;", "&#3626;&#3640;&#3656;&#3617;", "&#3585;&#3634;&#3619;&#3605;&#3633;&#3657;&#3591;&#3588;&#3656;&#3634;", "Directory &#3648;&#3585;&#3655;&#3610;&#3626;&#3639;&#3656;&#3629;", "&#3649;&#3627;&#3621;&#3656;&#3591; Stream", "&#3616;&#3634;&#3625;&#3605;&#3633;&#3657;&#3591;&#3605;&#3657;&#3609;", "&#3619;&#3632;&#3610;&#3610;  Windows", "&#3605;&#3657;&#3629;&#3591;&#3585;&#3634;&#3619;&#3651;&#3594;&#3657; Https", "&#3629;&#3609;&#3640;&#3597;&#3634;&#3605;&#3651;&#3627;&#3657;&#3648;&#3621;&#3639;&#3656;&#3629;&#3609;&#3648;&#3614;&#3621;&#3591;&#3652;&#3604;&#3657;", "&#3629;&#3609;&#3640;&#3597;&#3634;&#3605;&#3651;&#3627;&#3657;&#3604;&#3634;&#3623;&#3609;&#3660;&#3650;&#3627;&#3621;&#3604;&#3652;&#3604;&#3657;", "Session timeout", "&#3619;&#3634;&#3618;&#3591;&#3634;&#3609;&#3585;&#3634;&#3619; login &#3607;&#3637;&#3656;&#3612;&#3636;&#3604;&#3614;&#3621;&#3634;&#3604;", "&#3619;&#3629;&#3626;&#3633;&#3585;&#3588;&#3619;&#3641;&#3656;&#3585;&#3635;&#3621;&#3633;&#3591;&#3629;&#3656;&#3634;&#3609;&#3588;&#3656;&#3634;&#3592;&#3634;&#3585;&#3619;&#3632;&#3610;&#3610;", "&#3652;&#3617;&#3656;&#3626;&#3634;&#3617;&#3634;&#3619;&#3606;&#3648;&#3614;&#3636;&#3656;&#3617;&#3651;&#3609;&#3619;&#3634;&#3618;&#3585;&#3634;&#3619;&#3648;&#3621;&#3656;&#3609;&#3652;&#3604;&#3657;", "&#3612;&#3641;&#3657;&#3604;&#3641;&#3649;&#3621;&#3619;&#3632;&#3610;&#3610;", "&#3585;&#3619;&#3640;&#3603;&#3634;&#3648;&#3586;&#3657;&#3634;&#3619;&#3632;&#3610;&#3610;&#3604;&#3657;&#3623;&#3618; HTTPS &#3648;&#3614;&#3639;&#3656;&#3629;&#3648;&#3611;&#3621;&#3637;&#3656;&#3618;&#3609;", "&#3629;&#3609;&#3640;&#3597;&#3634;&#3605;&#3636;&#3651;&#3627;&#3657;&#3651;&#3594;&#3657; stream engine", "&#3594;&#3639;&#3656;&#3629;&#3648;&#3614;&#3621;&#3591;", "&#3624;&#3636;&#3621;&#3611;&#3636;&#3609;", "&#3629;&#3633;&#3621;&#3610;&#3633;&#3617;", "&#3586;&#3657;&#3629;&#3648;&#3626;&#3609;&#3629;&#3632;&#3649;&#3609;&#3632;", "&#3611;&#3637;", "&#3648;&#3614;&#3621;&#3591;&#3607;&#3637;&#3656;", "&#3649;&#3609;&#3623;", "&#3652;&#3617;&#3656;&#3605;&#3633;&#3657;&#3591;", "&#3588;&#3656;&#3634;&#3626;&#3641;&#3591;&#3626;&#3640;&#3604;&#3651;&#3609;&#3585;&#3634;&#3619;&#3604;&#3634;&#3623;&#3609;&#3660;&#3650;&#3627;&#3621;&#3604; (kbps)", "&#3612;&#3641;&#3657;&#3651;&#3594;&#3657;", "%1 &#3609;&#3634;&#3607;&#3637; - %2 &#3648;&#3614;&#3621;&#3591;", "%1 Kbit %2 &#3609;&#3634;&#3607;&#3637;", "&#3649;&#3609;&#3623;&#3648;&#3614;&#3621;&#3591; : %1", "wx", "%1 &#3623;&#3633;&#3609; %2 &#3594;&#3633;&#3656;&#3623;&#3650;&#3617;&#3591; %3 &#3609;&#3634;&#3607;&#3637; &#3651;&#3609;&#3585;&#3634;&#3619;&#3648;&#3621;&#3656;&#3609; %4 &#3652;&#3615;&#3621;&#3660; %5 mb", "&#3652;&#3617;&#3656;&#3614;&#3610;&#3626;&#3639;&#3656;&#3629;&#3607;&#3637;&#3656;&#3626;&#3634;&#3617;&#3634;&#3619;&#3648;&#3621;&#3656;&#3609;&#3652;&#3604;&#3657;", "&#3619;&#3627;&#3633;&#3626;&#3612;&#3656;&#3634;&#3609;&#3606;&#3641;&#3585;&#3648;&#3611;&#3621;&#3637;&#3656;&#3618;&#3609;&#3649;&#3621;&#3657;&#3623;", "&#3621;&#3591;&#3607;&#3632;&#3648;&#3610;&#3637;&#3618;&#3609;", "&#3585;&#3619;&#3640;&#3603;&#3634;&#3607;&#3635;&#3585;&#3634;&#3619;&#3648;&#3621;&#3639;&#3629;&#3585;&#3585;&#3656;&#3629;&#3609;", "&#3617;&#3637;&#3629;&#3632;&#3652;&#3619;&#3651;&#3627;&#3617;&#3656;", "&#3588;&#3621;&#3636;&#3585;&#3607;&#3637;&#3656;&#3609;&#3637;&#3656;&#3648;&#3614;&#3639;&#3656;&#3629;&#3586;&#3629;&#3588;&#3623;&#3634;&#3617;&#3594;&#3656;&#3623;&#3618;&#3648;&#3627;&#3621;&#3639;&#3629;", "&#3651;&#3594;&#3657;&#3619;&#3641;&#3611;&#3616;&#3634;&#3614;&#3592;&#3634;&#3585;&#3616;&#3634;&#3618;&#3609;&#3629;&#3585;", "&#3649;&#3627;&#3621;&#3656;&#3591;&#3586;&#3657;&#3629;&#3617;&#3641;&#3621;&#3619;&#3641;&#3611;&#3616;&#3634;&#3614;", "&#3619;&#3627;&#3633;&#3626;&#3612;&#3656;&#3634;&#3609;&#3648;&#3604;&#3636;&#3617;", "&#3619;&#3627;&#3633;&#3626;&#3612;&#3656;&#3634;&#3609;&#3648;&#3604;&#3636;&#3617;&#3652;&#3617;&#3656;&#3606;&#3641;&#3585;&#3605;&#3657;&#3629;&#3591;", "&#3619;&#3641;&#3611;&#3649;&#3610;&#3610;&#3585;&#3634;&#3619;&#3610;&#3637;&#3610;&#3629;&#3633;&#3604;", "&#3652;&#3617;&#3656;&#3626;&#3634;&#3617;&#3634;&#3619;&#3606;&#3607;&#3635;&#3585;&#3634;&#3619;&#3610;&#3637;&#3610;&#3629;&#3633;&#3604;&#3652;&#3615;&#3621;&#3660;&#3652;&#3604;&#3657;", "&#3614;&#3610;&#3652;&#3615;&#3621;&#3660;&#3607;&#3637;&#3656;&#3595;&#3657;&#3635;&#3585;&#3633;&#3609;&#3588;&#3639;&#3629;: \"%1\" \"%2\"", "&#3588;&#3640;&#3603;&#3649;&#3609;&#3656;&#3651;&#3592;&#3627;&#3619;&#3639;&#3629;&#3623;&#3656;&#3634;&#3592;&#3632;&#3621;&#3610;&#3619;&#3634;&#3618;&#3585;&#3634;&#3619;&#3648;&#3621;&#3656;&#3609;", "&#3648;&#3619;&#3637;&#3618;&#3591;&#3605;&#3634;&#3617;&#3621;&#3635;&#3604;&#3633;&#3610;&#3605;&#3633;&#3623;&#3629;&#3633;&#3585;&#3625;&#3619;", "&#3626;&#3640;&#3656;&#3617;", "&#3648;&#3619;&#3637;&#3618;&#3591;&#3621;&#3635;&#3604;&#3633;&#3610;", "&#3607;&#3637;&#3656;&#3617;&#3634;", "&#3651;&#3594;&#3657; javascript", "&#3588;&#3640;&#3603;&#3649;&#3609;&#3656;&#3651;&#3592;&#3623;&#3656;&#3634;&#3592;&#3632;&#3621;&#3610;&#3612;&#3641;&#3657;&#3651;&#3594;&#3657;&#3609;&#3637;&#3657;&#3629;&#3629;&#3585;&#3592;&#3634;&#3585;&#3619;&#3632;&#3610;&#3610;", "&#3604;&#3641;&#3611;&#3619;&#3632;&#3623;&#3633;&#3605;&#3636;&#3585;&#3634;&#3619;&#3651;&#3594;&#3657;", "&#3611;&#3619;&#3632;&#3623;&#3633;&#3605;&#3636;&#3585;&#3634;&#3619;&#3651;&#3594;&#3657;", "&#3627;&#3621;&#3633;&#3585;", "&#3651;&#3594;&#3657; css &#3616;&#3634;&#3618;&#3609;&#3629;&#3585;", "&#3621;&#3610;&#3607;&#3637;&#3656;&#3595;&#3657;&#3635;&#3585;&#3633;&#3609;", "&#3605;&#3585;&#3621;&#3591;", "&#3612;&#3636;&#3604;&#3614;&#3621;&#3634;&#3604;", "Stream", "(&#3649;&#3626;&#3604;&#3591;&#3649;&#3610;&#3610;)", "&#3652;&#3615;&#3621;&#3660;", "&#3629;&#3633;&#3621;&#3610;&#3633;&#3617;", " %1 &#3623;&#3633;&#3609; %2 &#3594;&#3633;&#3656;&#3623;&#3650;&#3617;&#3591; %3 &#3609;&#3634;&#3607;&#3637; %4 &#3623;&#3636;&#3609;&#3634;&#3607;&#3637; ", "&#3607;&#3633;&#3656;&#3623;&#3652;&#3611;", "&#3611;&#3619;&#3633;&#3610;&#3649;&#3605;&#3656;&#3591;", "Filehandling", "&#3588;&#3621;&#3636;&#3585; ? &#3648;&#3614;&#3639;&#3656;&#3629;&#3586;&#3629;&#3588;&#3623;&#3634;&#3617;&#3594;&#3656;&#3623;&#3618;&#3648;&#3627;&#3621;&#3639;&#3629;", "Sync &#3600;&#3634;&#3609;&#3586;&#3657;&#3629;&#3617;&#3641;&#3621;&#3629;&#3633;&#3605;&#3650;&#3609;&#3617;&#3633;&#3605;&#3636;", "&#3626;&#3656;&#3591;&#3626;&#3656;&#3623;&#3609;&#3586;&#3618;&#3634;&#3618;&#3652;&#3615;&#3621;&#3660;", "&#3629;&#3609;&#3640;&#3597;&#3634;&#3605; stream &#3607;&#3637;&#3656;&#3652;&#3617;&#3656;&#3652;&#3604;&#3657;&#3619;&#3633;&#3610;&#3585;&#3634;&#3619;&#3605;&#3619;&#3623;&#3592;&#3626;&#3629;&#3610;", "Include headers", "javascript &#3616;&#3634;&#3618;&#3609;&#3629;&#3585; ", "&#3627;&#3609;&#3657;&#3634;&#3627;&#3621;&#3633;&#3585;", "&#3649;&#3626;&#3604;&#3591;&#3626;&#3656;&#3623;&#3609; First IT &#3609;&#3635;&#3648;&#3626;&#3609;&#3629;", "&#3649;&#3626;&#3604;&#3591;&#3626;&#3656;&#3623;&#3609;&#3611;&#3619;&#3633;&#3610;&#3611;&#3619;&#3640;&#3591;&#3619;&#3632;&#3610;&#3610;", "&#3649;&#3626;&#3604;&#3591;&#3626;&#3606;&#3636;&#3605;&#3636;", "&#3648;&#3586;&#3637;&#3618;&#3609; ID3v2 &#3604;&#3657;&#3623;&#3618; stream", "&#3612;&#3641;&#3657;&#3651;&#3594;&#3657;&#3626;&#3634;&#3617;&#3634;&#3619;&#3606;&#3621;&#3591;&#3607;&#3632;&#3648;&#3610;&#3637;&#3618;&#3609;&#3652;&#3604;&#3657;", "&#3594;&#3636;&#3604;&#3652;&#3615;&#3621;&#3660;", "&#3651;&#3594;&#3656;", "&#3652;&#3617;&#3656;&#3651;&#3594;&#3656;", "&#3626;&#3656;&#3623;&#3609;&#3586;&#3618;&#3634;&#3618;", "MIME", "&#3619;&#3623;&#3617;&#3651;&#3609; M3U", "&#3649;&#3585;&#3657;&#3651;&#3586;&#3594;&#3609;&#3636;&#3604;&#3652;&#3615;&#3621;&#3660;", "&#3649;&#3609;&#3656;&#3651;&#3592;&#3627;&#3619;&#3639;&#3629;&#3652;&#3617;&#3656;");

$klang[26] = array('NewNorwegian', 'ISO-8859-1', 'Nynorsk', 'Kva er mest spelt?', 'Kva er nytt?', 'S�k', '(berre %1 vist)', 'sek', 'S�kjeresultat: \'%1\'', 'Fann', 'Ingen.', 'Val for oppdatering av s�kjedatabase', 'Slett ubrukte rekkjer', 'Regenerere ID3-informasjon?', 'Probleml�ysingsmodus', 'Oppdater', 'Avbryt', 'Oppdaterer s�kjedatabase', 'Fann %1 filer', 'Kunne ikkje lese fil: %1, hoppa over.', 'Installert: %1<br />Oppdatert: %2<br />S�kjer: ', 'S�kjer: ', 'Feila - sp�rring: %1', 'Kunne ikkje lese denne fila: %1. Hoppa over', 'Fjerna lenkje til: %1', '<br /><b>Resultat:</b><br />Gjekk gjennom %6 filer p� %7 sekund.<br />La til: %1<br />Oppdaterte: %2<br />Sletta: %3<br />Feila: %4<br />Hoppa over: %5<br />Merka for sletting: %8</br>', 'Ferdig.', 'Lukk', 'Fann ingen filer her: "%1"', 'kPlaylist: Innlogging', 'Albumliste for artist: %1', 'Sn�ggvelg %1', 'Ingen l�tar valde. Spelelista vart ikkje oppdatert.', 'Speleliste oppdatert!', 'Attende', 'Speleliste lagt til!', 'Husk � oppdatere sida.', 'Logg inn:', 'Passord', 'Advarsel! Dette er ei privat vevside. All aktivitet vert loggf�rt.', 'Logg inn', 'SSL krevst for innlogging', 'Spel', 'Slett', 'Delte:', 'Lagre', 'Kontroller speleliste: "%1" - %2 titlar', 'Redigerar', 'Visar', 'Vel', 'Sek', 'Status', 'Informasjon', 'Slett', 'Navn', 'Totalt:', 'Feil', 'Handling p� valde:', 'Sekvens', 'rediger speleliste', 'Slett denne oppf�ringa', 'ny speleliste', 'Namn:', 'Opprett', 'Spel:', 'Fil', 'Album', 'Alle', 'Valde', 'Legg til', 'Spel', 'rediger', 'ny', 'Vel:', 'Spelekontroll:', 'Speleliste:', 'Numerisk hurtigval', 'First IT gjev deg:', '(sj� etter ny versjon)', 'Heimeside', 'Berre ID3', 'album', 'tittel', 'artist', 'Sn�ggvelg album fr� artist', 'vis', 'Delte spelelister', 'Brukarar', 'Administrasjon', 'Kva er nytt', 'Mest spelt', 'Logg ut', 'Val', 'Sjekk', 'Mine Alternativ', 'Endre brukarinformasjon', 'ny brukar', 'Fullt namn', 'Brukarnamn', 'Endre passord?', 'Passord', 'Kommentar', 'Tilgangsniv�', 'P�', 'Av', 'Slett brukar', 'Logg ut brukar', 'Oppdater', 'Ny brukar', 'slett', 'logg ut', 'Bruk EXTM3U-eigenskapar?', 'Kor mange resultat skal visast (mest spelt/nytt)?', 'Maks antal viste s�kjeresultat:', 'Nullstill', 'Opne katalog', 'G� til katalog: %1', 'Last ned', 'G� opp eitt niv�', 'G� til hovudkatalog', 'Sj� etter ny versjon', 'brukarar', 'Spr�k', 'val', 'Tilgong blokka', 'Vilk�rleg rekkjef�lgje:', 'Innstillingar', 'Hovudkatalog', 'Hovudadresse for straum', 'Standardspr�k', 'Er dette eit Windows-system?', 'Krev HTTPS?', 'Tillat spoling?', 'Tillat nedlastingar?', 'Tidsgrense for innlogging (sek):', 'Rapportere mislykka innloggingsfors�k?', 'Vent litt - hentar filliste', 'Speleliste kunne ikkje leggjast til!', 'Administrator', 'Logg inn med HTTPS for � endre.', 'Aktiver innebygd straumfunksjon?', 'Tittel', 'Artist', 'Album', 'Kommentar', '�r', 'L�tnummer', 'Sjanger', 'ikkje sett', 'Maksimal fart for nedlasting (kbps)?', 'Brukar', '%1 minutt, %2 titlar', '%1 kbit, %2 minutt', 'Sjangerliste: %1', 'G�', 'Speletid: %1d %2t %3m, %4 filer, %5 mb', 'Ingen relevante ressursar her.', 'Passord endra!', 'Ny brukar', 'Vennligst gjer eit val!', 'Kva er oppdatering?', 'Klikk her for hjelp.', 'Bruke eksterne bilete?', 'Plassering for eksterne bilete:', 'Eksisterande passord:', 'Det eksisterande passordet er feil!', '�nska arkivprogram:', 'Arkivet kunne ikkje opprettast.', 'Fann mogeleg duplikat: %1 - %2', 'Verkeleg slette speleliste?', 'Alfabetisk', 'Tilfeldig', 'Sorter', 'Original', 'Bruk javascript?', 'Er du sikker p� at du vil slette denne brukaren?', 'Vis historikk', 'historikk', 'Rekkjer', 'Ekstern CSS-fil:', 'Fjern duplikat', 'OK', 'FEIL', 'Sanntidsstraum', '(vis som)', 'filer', 'album', '%1d %2t %3m %4s', 'Generelt', 'Skreddarsy', 'Filhandsaming', 'Trykk p� "?" for hjelp.', 'Automatisk synkronisering av databasen?', 'Send filending?', 'Tillat ikkje-autoriserte straumar?', 'Inkluder header-linjer?', 'Eksternt javascript:', 'Heimeside', 'Vis "First IT gjev deg"-del?', 'Vis oppgraderingsdel?', 'Vis statistikk?', 'Inkluder ID3v2-informasjon i straumen?', '"Ny brukar"-funksjonalitet?', 'Filtypar', 'Ja', 'Nei', 'Filending', 'MIME', 'Inkluder i M3U?', 'Endre filtype', 'Sikker?', 'Optimistisk filsjekk?', 'Tilfeldig val', 'Modus', 'Speleliste', 'Ingen, direkte', 'Mine favorittar', 'Ingen treff.', 'H�gste antal treff', 'Rekkjef�lgje', 'Sl� p� st�tte for LAME', 'Deaktivert', 'Tillat bruk av LAME?', 'E-post', 'Tillat sending av filer via e-post?', 'SMTP-tenar:', 'SMTP-port:', 'Send e-post til:', 'Melding:', 'Send', 'E-post sendt!', 'Tillat opplasting?', 'Opplastingsmappe:', 'Sl� p� mp3mail?', 'Last opp', 'Fila er lasta opp!', 'Fila kunne ikkje lastast opp!', 'Du m� bruke cookies for � logge inn!', 'Periode', 'N�r som helst', 'Denne veka', 'Denne m�naden', 'F�rre m�naden', 'Treff', 'LAME-kommando:', 'Vis omslag for album?', 'Albumfiler:', 'Endre storleiken p� albumbilete?', 'Albumh�gde:', 'Albumvidde:', 'E-post-metode:', 'Direkte', 'PEAR', 'Vent', 'V�r vennleg � skrive inn ei gyldig e-postadresse under innstillingar!', 'Integrerte spelelister?', 'Vis album fr� URL?', 'Album-URL', 'Kunne ikkje sende!', 'Ny brukar lagt til!', 'Opprette arkiv', 'Arkivet er sletta.', 'Brukarinformasjon oppdatert!', 'Musikktilpassing', '%1 innlegg filtrert bort', 'Logge tilgong', 'Synleg', 'Arkivert', 'Oppslagstavle', 'Skrive den %1 av %2', 'meir', 'Publiser', '%1 mb', '%1 kb', '%1 bitar', 'Rekursivt', 'F�rre', 'Neste', 'G� til side %1', 'Side:', 'Aldri spelt', 'Manuell godkjenning av nye brukarar?', 'Avventar behandling', 'Aktiver', 'Alle felt merka med * er obligatoriske', 'Brukarkontoen din vil verte sjekka og aktivert manuelt.', 'Siste straumar', 'Hugs meg', 'Stil', 'finn', 'Skriv inn stiar � s�kje i:', 'Bruk valde?', 'Speletid min/maks', 'Minutt', 'm3u', 'asx (WMA)', 'Dersom oppdateringa stoggar, trykk her: %1', 'F�lg symbolske lenkjer?', 'Mal for presentasjon av filliste:', 'Aktiver URL-tryggjing?', 'Tillete filtypar for opplasting:', 'Filtypen er ikkje tillete.', 'Spelelista er tom!', 'Tekstar', 'URL til tekstar', 'Vis lenkje til tekstar?', '(eller?)', 'Ukjend brukarnamn eller passord.', 'Maks filstorleik for opplasting: %1', 'Opne offentleg RSS-tilgong?');

$klang[27] = array('Japanese', 'EUC-JP', 'Japanese', '�͵���', '����', '����', '(%1 ��ɽ��)', '��', '�������: \'%1\'', '�������', '���Ĥ���ޤ���', '�����ǡ����١������� - ���ץ����', '̤���Ѥι��ܤ�������', 'ID3 ��ƹ��ۤ���', '�ǥХå��⡼��', '����', '����󥻥�', '�����ǡ����١����ι���', '%1 ��Υե����뤬���Ĥ���ޤ�����', '�ե����� %1 ����Ǥ��ޤ��󡥥����åפ��ޤ���', '���󥹥ȡ���: %1 - ����: %2���������:', '�������:', '���� - �����꡼: %1', '�ե����� %1 ���ɤ߼��ޤ��󡥥����åפ��ޤ���', '���: %1', '�� %6 ���� - �ɲ� %1 ����� %2 ���� %3 ����� %4 ������å� %5 �� - %7 �� - %8 ��Υե����뤬�������ޤ���', '��λ', '�Ĥ���', '�ե����뤬���Ĥ���ޤ���: "%1"', 'kplaylist ��������', '����Х���� - �����ƥ�����: %1', '����Х���� %1', '�ʤ����򤵤�Ƥ��ޤ��󡥥ץ쥤�ꥹ�ȤϹ�������ޤ���', '�ץ쥤�ꥹ�Ȥ򹹿����ޤ�����', '���', '�ץ쥤�ꥹ�Ȥ��ɲä��ޤ�����', '�ڡ�������ɤ߹��ߤ��Ƥ���������', '��������̾:', '�ѥ����:', '���ա������ϻ�Ū�ʥ����֥����ȤǤ������Ϥ��٤Ƶ�Ͽ����ޤ���', '��������', '��������ˤ� SSL ��ɬ�פǤ���', '����', '���', '��ͭ:', '��¸', '�ץ쥤�ꥹ��: "%1" - %2 �����ȥ�', '���ǥ���', '�ӥ塼��', '����', '�ֹ�', '���ơ�����', '����', '���', '̾��', '���:', '���顼', '���򤷤��ե������', '�ʽ�:', '�ץ쥤�ꥹ�Ȥ��Խ�', '���ι��ܤ�������', '�ץ쥤�ꥹ�Ȥ��ɲ�', '̾��:', '����', '����:', '�ե�����', '����Х�', '���٤�', '�����', '�ɲ�', '����', '�Խ�', '����', '����:', '������˥塼:', '�ץ쥤�ꥹ��:', '����Х���� ����', 'First IT gives you:', '(���åץǡ��Ȥγ�ǧ)', 'kplaylist �Υۡ���ڡ���', 'ID3 �Τ�', '����Х�', '�����ȥ�', '�����ƥ�����', '�����ƥ�����̾���饢��Х������', 'ɽ��', '��ͭ�ץ쥤�ꥹ��', '�桼����', '������˥塼', '����', '�͵���', '����������', '���ץ����', '�����å�', '�桼������˥塼', '�桼�������Խ�', '�桼�������ɲ�', '��̾', '��������', '�ѥ���ɤ��ѹ�', '�ѥ����', '������', '����������٥�', '����', '����', '�桼�����κ��', '�桼�����Υ���������', '����', '�����桼����', '���', '����������', 'EXTM3U �����', 'ɽ����� (����/�͵���)', '����ɽ�����', '�ꥻ�å�', '�ǥ��쥯�ȥ�򳫤�', '�ǥ��쥯�ȥ� %1 �˰�ư', '�����������', '�쳬�ؾ�˰�ư', '�롼�ȥǥ��쥯�ȥ�˰�ư', '���åץ��졼�ɤγ�ǧ', '�桼����', '����', '���ץ����', 'Booted', '����åե�:', '����', '�١����ǥ��쥯�ȥ�', '���ȥ꡼�� URL', '�ǥե���Ȥθ���', 'Windows �����', 'Require HTTPS', '����������Ĥ���', '����������ɤ���Ĥ���', '���å����Υ����ॢ���Ȼ���', '���������Ԥ���𤹤�', 'Hold on - fetching file list', '�ץ쥤�ꥹ�Ȥ��ɲäǤ��ޤ���', '����', 'HTTPS �����Ѥ��ƥ������󤹤�', '���ȥ꡼�२�󥸥��ͭ���ˤ���', '�����ȥ�', '�����ƥ�����', '����Х�', '������', 'ǯ', '�ȥ�å�', '������', '̤����', '��������������®�� (kbps)', '�桼����', '%1 ʬ - %2 �����ȥ�', '%1 kbit %2 ʬ', '���������: %1', '�¹�', '���ջ��� %1 �� %2 ���� %3 ʬ %4 �ե����� %5 mb', 'No relevant resources here.', '�ѥ���ɤ��ѹ����ޤ�����', '�����󥢥å�', '���򤷤Ƥ���������', '���åץǡ��ȤȤϲ��Ǥ�����', '�����򥯥�å�����ȥإ�פ�ɽ�����ޤ�', '�����β�������Ѥ���', '�����β����Υѥ�', '���ߤΥѥ����', '���ߤΥѥ���ɤ����פ��ޤ���', 'ͥ�褹�밵�̷���', '���̤Ǥ��ޤ���Ǥ���', '�����餯�ե����뤬��ʣ���Ƥ��ޤ�: "%1" "%2"', '�����˥ץ쥤�ꥹ�Ȥ������ޤ�����', '����ե��٥åȽ�', '������', '����', '���ꥸ�ʥ�', 'Javascript ����Ѥ���', '�����ˤ��Υ桼�����������ޤ�����', '�����ɽ������', '����', '��', '���� CSS �ե�����', '��ʣ���ܤ�������', 'OK', '���顼', '���ȥ꡼��', '(ɽ����ˡ)', '�ե�����', '����Х�', '%1 �� %2 ���� %3 ʬ %4 ��', '����', '�������ޥ���', '�ե��������', '? �򥯥�å�����ȥإ�פ�ɽ�����ޤ�', '��ư�ǡ����١���Ʊ��', '�ե�����γ�ĥ�Ҥ�����', '��������ʤ��Υ��ȥ꡼�����Ĥ���', '�إå���ޤ��', '���� Javascript', '�ۡ���ڡ���', 'First IT gives you ��ɽ��', '�����Υ����å���ɽ��', '���פ�ɽ��', '���ȥ꡼��� ID 3v2 ������', '�桼�����Υ����󥢥åפ�ͭ���ˤ���', '�ե����륿����', '�Ϥ�', '������', '��ĥ��', 'MIME', 'M3U �˴ޤ��', '�ե����륿���פ��Խ�', '�ۤ�Ȥ��Ǥ�����', '�ڴ�Ū�ʥե������ǧ', '����������', '�⡼��', '�ץ쥤�ꥹ��', '���Ѥ��ʤ�', '����������', '���⸫�Ĥ���ޤ���Ǥ���', '�͵�', '���', 'LAME ���ݡ��Ȥ�ͭ���ˤ���', '̵��', 'LAME �λ��Ѥ���Ĥ���', '�᡼�륢�ɥ쥹', '�ե������᡼����������뤳�Ȥ���Ĥ���', 'SMTP �����С�', 'SMTP �ݡ���', '����', '��å�����', '����', '�᡼����������ޤ�����', '���åץ����ɤ�ͭ���ˤ���', '���åץ����ɤ���ǥ��쥯�ȥ�', 'mp3mail ��ͭ���ˤ���', '���åץ�����', '�ե�����򥢥åץ����ɤ��ޤ�����', '�ե�����򥢥åץ����ɤǤ��ޤ���Ǥ�����', '�������󤹤�ˤϥ��å�����ͭ���ˤ��Ƥ���������', '����', '���ޤ�', '����', '����', '���', '�ҥå�', 'LAME ���ޥ��', '����Х५�С���ɽ������', '����Х�ե�����̾', '�����Υ��������ѹ�����', '�����ι⤵', '��������', '�᡼��������ˡ', 'ľ��', 'Pear', 'Wait!', 'ͭ���ʥ᡼�륢�ɥ쥹�����Ϥ��Ƥ���������', '�ץ쥤�ꥹ�Ȥ򥤥�饤��ˤ���', 'URL ���饢��Х��ɽ������', '����Х�� URL', '�����Ǥ��ޤ���', '�桼�������ɲä��ޤ�����', '���̥ե��������', '���̥ե�����������ޤ�����', '�桼�����򹹿����ޤ�����', 'Music match', '%1 ���ܤ��ե��륿������Ƥ��ޤ�', '�������������˵�Ͽ', 'ɽ������', 'Archived', '�Ǽ���', '%2 ��ȯ�� %1', '��ä�ɽ��', '����', '%1 mb', '%1 kb', '%1 bytes', 'Recursive', '��', '��', '%1 �ڡ����ܤ�ɽ��', '�ڡ���:', '̤����', '��ư����Ͽ������դ���', '��α', '����', '*�Τ�����ܤ�ɬ�ܹ��ܤǤ���', '��Ͽ���Ƥ��ǧ�奢������Ȥ�ȯ�Ԥ��ޤ���', '�Ƕ�κ���', '��������', '��������', 'õ��', '��������ѥ�������', '������ܤ����');

$klang[28] = array('Icelandic', 'ISO-8859-1', '�slenska', 'Vins�lt', 'N�tt', 'Leita', '(s�ni bara fyrstu %1)', 'sek', 'Ni�urst��ur leitar a� \'%1\'', 'Fann', 'Ekkert.', 'Uppf�ra valkosti leitargagnagrunns', 'Ey�a �notu�um f�rslum?', 'Endurbyggja ID3 uppl�singar?', 'Afl�sa kerfi�?', 'Uppf�ra', 'H�tta vi�', 'Uppf�ra leitargagnagrunn', 'Fann %1 skr�(r).', 'Gat ekki greint skr�na "%1" og sleppi henni �v�.', 'Hef sett inn: %1 - Uppf�rt: %2, sko�a:', 'Sko�a:', 'Mist�kst - bei�ni: %1', 'Gat ekki lesi� skr�na "%1" og sleppi henni �v�.', 'Fjarl�g�i tengil � %1', 'Hef sett inn %1, uppf�rt %2, fjarl�gt %3 en �ar af mist�kust %4 skr�ningar og %5 var sleppt af alls %6 skr�m - T�k %7 sek - %8 merktar til ey�ingar.', 'Loki�', 'Loka', 'Fann engar skr�r � "%1"', 'Innskr�ning', 'Pl�tur me� flytjandanum %1', 'Finna vins�lt me� %1', 'Engin l�g voru valin.  Lagalisti var ekki uppf�r�ur.', 'Lagalisti uppf�r�ur!', 'Til baka', 'Lagalista b�tt vi�!', 'Mundu a� endurhla�a s��una.', 'Notendanafn:', 'Lykilor�:', 'Athuga�u a� �essi s��a er til einkanota eing�ngu.  Allar tengingar eru skr��ar.', 'Innskr�ning', 'Innskr�ning m�guleg yfir SSL', 'Spila', 'Ey�a', 'Deilt me�:', 'Vista', 'St�ra lagalista "%1" me� %2 titla', 'Breyta', 'Sko�a', 'Velja', 'R��', 'Sta�a', 'Uppl�singar', 'Ey�a', 'Nafn', 'Alls:', 'Villa', 'Framkv�ma a�ger� � v�ldum l�gum', 'R��:', 'Breyta lagalista', 'Ey�a f�rslu', 'B�ta vi� lagalista', 'Nafn:', 'B�a til', 'Spila:', 'Skr�', 'Plata', 'Allt', 'Vali�', 'B�ta vi�', 'Spila', 'Breyta', 'N�tt', 'Velja:', 'Spila:', 'Lagalisti:', 'Hotselect numeric', 'First IT f�rir ��r', '(kanna me� uppf�rslu)', 'Fors��a', 'Einungis ID3 t�gg', 'Plata', 'Titill', 'Flytjandi', 'Hra�velja pl�tu fr� flytjanda', 'Sko�a', 'Sameiginlegir lagalistar', 'Notendur', 'Kerfisstj�rn', 'Hva� er n�tt', 'Hva� er vins�lt', '�tskr�ning', 'Valkostir', 'Kanna', 'Mitt', 'Breyta notanda', 'N�r notandi', 'Fullt nafn', 'Notendanafn', 'Breyta lykilor�i?', 'Lykilor�', 'Athugasemd', 'A�gangsstig', 'Virkur', '�virkur', 'Ey�a notanda', 'Skr� notanda �t', 'Endurhla�a', 'N�r notandi', 'Ey�a', '�tskr�', 'Nota EXTM3U eiginleika?', 'Hversu margar f�rslur � a� s�na (af n�ju/vins�lu)?', 'H�marsksfj�ldi leitarni�ursta�na', 'Endurstilla', 'Opna m�ppu', 'Fara � m�ppu: %1', 'S�kja', 'Fara eina m�ppu upp�vi�', 'Fara � efstu m�ppu', 'Kanna me� uppf�rslur', 'Notendur', 'Tungum�l', 'Valkostir', 'Sparka�i', 'Uppstokka:', 'Stillingar', 'Grunnmappa', 'Sta�setning straums', 'Sj�lfvali� tungum�l', 'Vef�j�ninn keyrir � Windows', 'Krefjast HTTPS a�gangs', 'Leyfa a� sp�la �fram � l�gum', 'Leyfa ni�urhal � l�gum', 'Session timeout', 'Tilkynna tilraunir til innskr�ningar', 'Doka�u vi� - s�ki skr�alista', 'Ekki var h�gt a� b�ta vi� lagalistanum!', 'Stj�rnandi', 'Sk��u �ig inn gegnum HTTPS til a� breyta', 'Leyfa strauma', 'Titill', 'Flytjandi', 'Plata', 'Athugasemd', '�r', 'Nr.', 'Tegund', 'Ekki stillt', 'Mesti hra�i (kbps)', 'Notandi', '%1 m�n. - %2 titlar', '%1 kbit %2 m�n', 'Genre list: %1', '�fram', '%1d %2h %3m playtime %4 files %5 mb', 'Engin vi�eigandi g�gn tilt�k h�r.', 'Lykilor�i breytt!', 'Skr�ning', '�� ver�ur a� velja.', 'Hva� er a� uppf�ra?', 'Smelltu h�r fyrir a�sto�', 'Nota utana�komandi myndir', 'Sl�� utana�komandi mynda', 'N�verandi lykilor�', 'N�verandi lykilor� er ekki r�tt!', 'Preferred archiver', 'Archive could not be made', 'L�kleg afrit skr�a fundin: "%1" "%2"', 'Virkilega ey�a lagalista?', 'Stafr�fsr��', 'Stokka upp', 'Ra�a', 'Upprunalegt', 'Nota javascript', 'Ertu viss um a� �� viljir ey�a �essum notanda?', 'Sko�a s�gu', 'Saga', 'R��', 'Utana�komandi CSS skr�', 'Fjarl�gja afrit', '� Lagi', 'Villa', 'Straumur', '(s�na sem)', 'skr�r', 'pl�tur', '%1d %2h %3m %4s', 'Almennt', 'Stillingar', 'Skr�ar me�h�ndlun', 'Smella � ? fyrir hj�lp.', 'Automatic database sync', 'Senda skr�arendingar', 'Allow unauthorized streams', 'Include headers', 'Utana�komandi javascript', 'Heimas��a', 'S�na First IT f�rir ��r part', 'S�na uppf�ra part', 'S�na t�lfr��i', 'Skrifa ID3v2 me� straumum', 'Leyfa notanda a� n�skr� sig', 'Skr�ar tegundir', 'J�', 'Nei', 'Skr�arending', 'MIME', 'Innihalda � M3U', 'Breyta skr�artegund', 'Ertu viss?', 'Optimistic filecheck', 'Uppstokkun', 'Mode', 'Lagalisti', 'None, directly', 'Mitt upp�hald', 'Did not find any hits', 'Alltime hits', 'R��', 'Virkja LAME stu�ning?', '�virkt', 'Leifa LAME notkun?', 'Netfang', 'Leyfa a� senda skr�r?', 'SMTP �j�nn', 'SMTP port', 'Senda p�st �', 'Skilabo�', 'Senda', 'P�stur sendur!', 'Virkja upphal', 'Upload mappa', 'Virkja mp3mail', 'Upphla�a', 'Skr� hefur upphla�ist!', 'Ekki t�kst a� hla�a upp skr�!', '�� ver�ur a� leyfa cookies til a� innskr�!', 'T�mabil', 'fr� upphafi', '�essi vika', '�essi m�nu�ur', 's��asti m�nu�ur', 'hits', 'LAME skipun', 'S�na pl�tuumslag', 'Album files', 'Breyta st�r� pl�tuumslags', 'Pl�tu h��', 'Pl�tu breidd', 'P�st a�fer�', 'Direct', 'Pear', 'B�ddu!', 'Vinsamlega settu inn gilt netfang!', 'Lagalisti innfelldur?', 'Show album from URL?', 'Sl�� � pl�tu', 'Gat ekki sent!', 'Notanda b�tt vi�!', 'Archive creator', 'Archive is deleted.', 'Notandi uppf�r�ur!', 'Music match', '%1 entries filtered', 'Skr� a�gengi', 'Viewable', 'Archived', 'Fr�ttaskot', 'Skr�� %1 af %2', 'meira', 'Birta', '%1 mb', '%1 kb', '%1 bytes', 'Recursive', 'Fyrra', 'N�st', 'Fara � s��u%1', 'S��a:', 'Aldrei spila�', 'Sam�ykkja skr�ningar handvirkt', 'B��ur', 'virkja', 'Sv��i merkt * eru skilyrt', 'A�gangur �inn ver�ur sko�a�ur og handvirkt sam�ykktur.', 'S��ustu straumar', 'muna eftir m�r', 'St�ll', 'finna', 'Enter paths to search for', 'Nota vali�?', 'Track time minst/mest', 'M�n�tur', 'm3u', 'asx (WMA)', 'Ef uppf�rsla stoppar, smelltu h�r: %1', 'Fylgja symlinks?', 'Skr�ar sni�skjal', 'Leifa URL �ryggi', 'Upphals hv�tlistun', 'Skr�artegund ekki leyf�.', 'Lagalisti t�mur', 'Texti', 'Texti URL', 'S�na texta stl��', '(e�a?)', 'Notandanafn e�a lykilor� ekki r�tt', 'Mesta st�r� sem m� hla�a upp: %1', 'opna fyrir rss', 'Tilgreini� lykilor�', 'Vantar nafn og notanda', 'Notandi er �egar til!', 'Fella ni�ur stj�nunarr�ttindi fyrir �essa session?', 'S�ki gagnagrunns f�rslur: %1/%2', 'Fann ekki "%1", hefur skr�nni veri� eytt?', 'Fr�/til dags(DDMMYY)', 'Villa � innsl�ttarformi, vinsamlega reyndu aftur.', 'H�marks textalengd', 'D�lkar', 'N�tt sni�skjal', 'Sni�skjal', 'Nafn sni�skjals', 'Vantar nafn � sni�skjal!', 'Sj�lfgefi� innskr�ningar sni�skjal', 'Tag extractor:', 'Allow using archiver(s)', 'Maximum archive size (mb)', 'Archive exceeded maximum size! (%1mb, max is %2mb)', 'Heima mappa', 'Force LAME rate', 'Transcode', 'httpQ', 'Villa vi� tengingu httpQ server (%1).', 'Use database cache?', '�notu�um f�rslum var ekki eytt, �ar sem �eim var sleppt.', 'Lengd', 'Spila pl�tu', 'Listing view:', 'Max number of detailed views', 'Effective', 'Detailed', 'AJAX Prototype URL', '�tvarp', 'Loop');

$klang[29] = array('Turkish', 'ISO-8859-9', 'T�rk�e', 'En �ok sevilenler', 'Yeniler', 'Ara', '(g�sterilen %1 )', 'sn', 'Arama sonucu: \'%1\'', 'bulundu', 'Yok.', 'veritaban&#305; arama se�enekleri g�ncelleme', 'Kullan&#305;lmayan kay&#305;tlar silinsin mi?', 'ID3 ba&#351;tan olu&#351;turulsun mu?', 'Hata arama modu?', 'G�ncelle', '&#304;ptal', 'Arama veritaban&#305;n&#305; g�ncelle', '%1 dosya bulundu.', 'Tan&#305;mlanamayan dosya: %1, iptal edildi.', 'Kuruldu: %1 - G�ncelleme: %2, tarama: ', 'Tarama:', 'Hata&#305; - sorgu: %1', 'Okunamayan dosya: %1. iptal edildi.', 'kald&#305;r&#305;lan link: %1', 'girilen %1, g�ncellenen %2, silinen %3 %4 hatal&#305; ve %5 iptal edilen toplam %6 dosya - %7 sn - %8 silinmek i�in i&#351;aretlendi.', '&#304;&#351;lem Tamam', 'Kapat', '"%1" de herhengi bir dosya bulunamad&#305;', 'kPlaylist Giri&#351;', 'Sanat�&#305;: %1 i�in alb�m listesi', 'Sevilenler %1', 'Se�im yap&#305;lmad&#305;. Liste g�ncellenmedi.', 'Liste g�ncellendi!', 'Geri', 'Liste eklendi!', 'Sayfay&#305; tekrar y�klemeyi unutmay&#305;n.', 'Giri&#351;:', '&#351;ifre:', 'Dikkat! Yap&#305;&#287;&#305;n&#305;z i&#351;lemler kaydedilmektedir.', 'Giri&#351;', 'Giri&#351; i�in SSL gerekmektedir.', '�al', 'Sil', 'Payla&#351;&#305;m: ', 'Kaydet', 'Listeyi kontrol et: \'%1\' - %2 &#351;ark&#305;', 'Yazar', 'G�z at', 'Se�', 'Sn', 'Durum', 'Bilgi', 'Sil', '&#304;sim', 'Toplam:', 'Hata', 'Se�ilenleri: ', 'S&#305;ralama :', 'Listeyi de&#287;i&#351;tir', 'Bu giri&#351;i sil', 'Liste ekle', '&#304;sim:', 'Olu&#351;tur', '�al: ', 'Dosya', 'Alb�m', 'Hepsi', 'Se�ilen', 'ekle', '�al', 'de&#287;i&#351;tir', 'yeni', 'Se�:', 'Kontrol: ', 'Liste: ', 'Say&#305;sal sevilenler', 'First IT in sunduklar&#305;:', '(g�ncelleme i�in kontrol edin)', 'Ana site', 'sadece id3', 'alb�m', '&#351;ark&#305;', 'sanat�&#305;', 'Sana�&#305;n&#305;n en sevilen alb�m�', 'g�z at', 'Payla&#351;&#305;lan Listeler', 'Kullan&#305;c&#305;lar', 'Admin Kontrolleri', 'Yeniler', 'Sevilenler', '�&#305;k&#305;&#351;', 'Se�enekler', 'G�z at', 'Benim ayarlar&#305;m', 'kullan&#305;c&#305; i&#351;lemleri', 'yeni kullan&#305;c&#305;', 'Tam isim', 'Giri&#351;', '&#350;ifre de&#287;i&#351;sin mi?', '&#350;ifre', 'Yorum', 'Eri&#351;im seviyesi', 'A�&#305;k', 'Kapal&#305;', 'Kullan&#305;c&#305;y&#305; sil', 'Kullanc&#305;y&#305; �&#305;kar', 'Yenile', 'Yeni kullan&#305;c&#305;', 'sil', '�&#305;k&#305;&#351;', 'EXTM3U kullan&#305;ls&#305;n m&#305;?', '(sevilen/yeni) sat&#305;r say&#305;s&#305;', 'Maksimum arama sat&#305;r&#305;', 'Reset', 'Dizini a�', 'Gidilecek dizin: %1', '&#304;ndir', 'Bir ad&#305;m yukar&#305; �&#305;k', 'Ana dizine git.', 'G�ncelleme i�in kontrol et', 'kullan&#305;c&#305;lar', 'Dil', 'se�enekler', 'Sepetle', 'kar&#305;&#351;t&#305;r:', 'Ayarlar', 'Ana dizin', 'Kay&#305;t yeri', 'Varsay&#305;lan dil', 'Widows sistemi', 'HTTPS gerektirmektedir', 'Tarama izni', '&#304;ndirme izni', 'Oturum s�resi doldu', 'Hatal&#305; giri&#351;leri rapor et', 'Bekleyin - Dosya listei haz&#305;rlan&#305;yor', 'Liste eklenemedi!', 'Y�netici', 'De&#287;i&#351;tirmek i�in HTTPS ile girin!', 'Yay&#305;n motorunu aktif yap', '&#350;ark&#305;', 'Sanat�&#305;', 'Alb�m', 'Yorum', 'Y&#305;l', 'Kay&#305;t', 'T�r', 'ayarlanmad&#305;', 'Maksimum indirme oran&#305; (kbps)', 'Kullan&#305;c&#305;', '%1 dakika - %2 &#351;ark&#305;', '%1 kbit %2 dakika', 'T�r listesi: %1', 'Tamam', '%1g�n %2saat %3dk �alma s�resi %4 dosya %5 mb', 'Burada uygun kaynak yok.', '&#350;ifre de&#287;i&#351;tirildi!', 'Kay&#305;t yapt&#305;r', 'L�tfen bir se�im yap&#305;n&#305;z!', 'Neler g�ncellensin?', 'Yard&#305;m i�in buraya t&#305;klay&#305;n&#305;z', 'D&#305;&#351;ardan resim kullan?', 'D&#305;&#351;ardan kullan&#305;lacak resmin adresi', '&#350;imdiki &#351;ifre', '&#350;imdiki &#351;ifre tutmuyor!', 'Tercih edilen ar&#351;ivleyici', 'Ar&#351;iv olu&#351;turulamad&#305;', 'Olas&#305; dosya tekrar&#305; bulundu:  "%1" "%2"', 'Listeyi ger�ekten silmek istiyor musunuz?', 'Alfabetik', 'Rastgele', 'S&#305;rala', 'Orjinal', 'Javascript kullan', 'Bu kullan&#305;c&#305;y&#305; silmek iste&#287;inizden emin misiniz?', 'Tarih�eyi izle', 'tarih�e', 'Sat&#305;r', 'D&#305;&#351; CSS dosyas&#305;', 'Tekrarlananlar&#305; sil', 'Tamam', 'Hata', 'Yay&#305;n', '(olarak g�ster)', 'dosyalar', 'alb�mler', '%1g�n %2saat %3dakika %4sn', 'Genel', 'Ki&#351;isel', 'Dosya i&#351;lemleri', 'Yard&#305;m i�in  ? i&#351;aretine t&#305;klay&#305;n.', 'Otomatik veritaban&#305; senkronizasyonu', 'Dosya uzant&#305;s&#305;n&#305; g�nder', 'Yetki verilmemi&#351; yay&#305;nlara da izin ver', 'Ba&#351;l&#305;klar&#305; i�er', 'D&#305;&#351; javascript', 'Ana Sayfa', 'First IT\'in size sunduklar&#305; b�l�m�n� g�ster', 'G�ncelleme b�l�m�n� g�ster', '&#304;statistikleri g�ster', 'Yay&#305;nla beraber ID3v2 ba&#351;l&#305;klar&#305;n&#305; de yaz', 'Kullan&#305;c&#305;n&#305;n kay&#305;t olmas&#305;na izin ver', 'Dosya t�rleri', 'Evet', 'Hay&#305;r', 'Uzant&#305;', 'MIME', 'M3U dakileri i�ersin', 'Dosya t�r�n� d�zenle', 'Eminmisiniz?', 'Dosyan&#305;n var olup olmama kontrol�', 'Rastgele', 'Mod', 'Liste', 'Hay&#305;r, direkt olarak', 'Favorilerim', 'Hit par�a bulunamad&#305;', 'T�m zamanlar&#305;n hit par�alar&#305;', 'S&#305;ra', 'LAME deste&#287;i a�&#305;ls&#305;nm&#305;?', 'Kapat&#305;ld&#305;', 'LAME kullan&#305;ls&#305;n m&#305;?', 'Email', 'Mail dosyalr&#305;na izin verilsin mi?', 'SMTP server', 'SMTP port', 'Gidecek mail adresi', 'Mesaj', 'G�nder', 'Mail g�nderildi!', 'Y�klemeyi aktif yap', 'Y�kleme dizini', 'Mp3mail\'leri aktif yap', 'Y�kle', 'Dosya y�klendi!', 'Dosya y�klememez!', 'Giri&#351; i�in cookie lere izin vermeniz gerekir!', 'Aral&#305;k', 'her zaman', 'bu hafta', 'bu ay', 'ge�en ay', 'hit', 'LAME komutu', 'Alb�m kapa&#287;&#305;n&#305; g�ster', 'Alb�m dosyalar&#305;', 'Alb�m resimlerini yeniden boyutland&#305;r', 'Alb�m y�ksekli&#287;i', 'Alb�m geni&#351;li&#287;i', 'Mail metodu', 'Direk', 'Pear', 'Bekle!', 'L�tfen se�eneklere ge�erli bir e-mail adresi girin!', 'Liste i�erden ba&#351;las&#305;n m&#305;?', 'URL\'den alb�m g�sterilsin mi?', 'Alb�m URL\'si', 'G�nderilemedi!', 'Kullan&#305;c&#305; eklendi!', 'Ar&#351;ivi olu&#351;turan', 'Ar&#351;iv silindi.', 'Kullan&#305;c&#305; g�ncellendi!', 'Uyan par�alar', '%1 giri&#351; filitrelendi', 'Log eri&#351;imi', 'G�r�lebilir', 'Ar&#351;ivlendi', 'Haberler', ' %1 tarihinde %2 giri&#351; yapt&#305;', 'ayr&#305;nt&#305;', 'Yay&#305;nla', '%1 mb', '%1 kb', '%1 bytes', 'Alt dizinlere dallan', '�nceki', 'Sonraki', 'Sayfa %1\'e/a git', 'Sayfa: ', 'Hi� �al&#305;nmad&#305;', 'Yeni kullan&#305;c&#305; y�netici taraf&#305;ndan onaylans&#305;n', 'Beklemede', 'Aktif yap', '"*" ile i&#351;aretlenen t�m alanlar zorunludur', 'Hesab&#305;n&#305;z incelendikten sonra onaylanacakt&#305;r.', 'Son �al&#305;nanlar', 'beni hat&#305;rla', 'Sitil', 'bul', 'Aran&#305;lacak yolu girin', 'Se�ilen kullan&#305;ls&#305;n m&#305;?', 'Kay&#305;t s�resi min/max', 'Dakika', 'm3u', 'asx (WMA)', 'G�ncelleme durursa, buraya t&#305;klay&#305;n: %1', 'Sembolik linkler takip edilsin mi?', 'Dosya tasla&#287;&#305;', 'URL g�venli&#287;ini a�', 'Y�kleme izni filtresi', 'Dosya t�r�ne izin verilmedi.', 'Liste bo&#351;!', '&#350;ark&#305; s�zleri', '&#350;ark&#305; s�zleri URL\'si', '&#350;ark&#305; s�zleri URL\'si g�sterilsin mi?', '(veya?)', 'Hatal&#505; kullan&#305;c&#305; ad&#305; veya &#351;ifre', 'Maksimum y�kleme boyutu : %1', 'Halka a�&#305;k son yay&#253;nlara RSS deste&#287;i verilsin  mi?', 'L�tfen bir Sifre seciniz ', 'Isim ve �yelik Bilgileri gereklidir', 'Bu kullanici adi kullanilmaktadir ', 'Admin onaylasin mi ?', 'Fetching database records: %1/%2', 'Could not find "%1", is file deleted?', 'Su tarihten itibaren', 'Hata olustu. L�tfen tekrar deneyiniz ', 'Maximum Text uzunlugu', 'Dir Columns', 'New template', 'Template', 'Template name', 'Need a template name!', 'Default signup template', 'Tag extractor:', 'Allow using archiver(s)', 'Maximum archive size (mb)', 'Archive exceeded maximum size! (%1mb, max is %2mb)', 'Home dir');


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

function get_lang_combo($userlang='', $fieldname='u_language') 
{ 
	global $klang; 
	function lang_sort ($a, $b) 
	{ 
		return strcmp($a[0], $b[0]); 
	}
	$cache = array(); 
	foreach ($klang as $key => $val) $cache[] = array($val[0], $key);
	usort($cache, "lang_sort"); 

	$langout = '<select name="'.$fieldname.'" class="fatbuttom">'; 

	foreach($cache as $id => $arr)
	{
		$lid = $arr[1];
		if (isset($klang[$lid]))
		{
			$langout .= '<option value="'.$lid.'"';
			if ($lid == $userlang) $langout .= ' selected="selected"';
			$langout .= '>';
			if ($lid == $userlang) $langout .= $klang[$lid][2];
				else $langout .= $klang[$lid][0];
			$langout .= '</option>';	
		}
	}
	
	$langout .= '</select>';
	return $langout;
}

function checkchs($in, $conv=true)
{
	global $cfg;

	if ($conv && $cfg['convertcharset'] && function_exists('iconv')) 
	{
		$ret = @iconv($cfg['filesystemcharset'], get_lang(1).'//TRANSLIT', $in);
		if ($ret != false) $in = $ret;
	}
	$str = @htmlentities($in, ENT_QUOTES, get_lang(1));
	if (strlen($str) > 0) return $str;

	return $in; 
}


class kptheme
{
	function kptheme()
	{
		$this->themes = array();
		$this->theme = false;
	}

	function listdir($path, &$storelist, $stripc)
	{
		$flist = array();
		if ($handle = opendir($path))
		{
			while (false !== ($file = readdir($handle))) $flist[] = $file;
			closedir($handle);

			foreach($flist as $file)
			{
				if ($file != '.' && $file != '..')
				{
					if (is_dir($path.$file)) 
					{ 
						if (!is_link($path.$file)) $this->listdir($path.$file.'/', $storelist, $stripc);
					} else $storelist[substr($path.$file, $stripc)] = true;
				}
			}
		}
	}

	function select($default=0)
	{
		$out = '<option value="0"> -- '.get_lang(49).' -- </option>';
		foreach($this->themes as $themeid => $theme)
		{
			$out .= '<option value="'.$theme[1].'"';
			if ($theme[1] == $default) $out .= ' selected="selected"';
			$out .= '>'.$theme[0].'</option>';
		}
		return $out;
	}

	function findfile($filename, $arr, &$ret)
	{
		$flen = strlen($filename);
		foreach($arr as $name => $id)
		{
			if (strlen($name) >= $flen)
			{
				if ($filename == substr($name, strlen($name) - $flen)) 
				{
					$ret = $name;
					return true;
				}
			}
		}
	}

	function getlink($file, $dir, $local=false)
	{
		global $phpenv;
		
		if ($local)
			return THEMEROOT.$dir.'/'.$file; 
		else 
			return $phpenv['relative'].'/kptheme/'.$dir.'/'.$file;
	}

	function getfile($file, $local=false)
	{
		if (is_array($this->theme))
		{
			$filesrc = '';
			if ($this->findfile($file, $this->theme[2], $filesrc))
			{
				$link = $this->getlink($filesrc, $this->theme[0], $local);
				return $link;
			}
		}
		return false;
	}

	function getlocalfile($file)
	{
		return $this->getfile($file, true);
	}

	function load($id=0)
	{
		if (@is_dir('kptheme')) 
		{
			if (!defined('THEMEROOT'))  define('THEMEROOT', slashend(getcwd()).'kptheme/');

			$dirs = array();
			if ($handle = opendir(THEMEROOT))
			{
				while (false !== ($dir = readdir($handle)))
					if (is_dir(THEMEROOT . $dir) && $dir != '.' && $dir != '..') $dirs[] = $dir;
				closedir($handle);
			}

			foreach($dirs as $dir)
			{
				$themeid = crc32($dir);
				if ($id == $themeid || $id == 0)
				{
					$flist = array();
					$fpath = THEMEROOT.$dir.'/';
					$this->listdir($fpath, $flist, strlen($fpath));
					if ($id == $themeid) $this->theme = array($dir, $themeid, $flist);
						else $this->themes[] = array($dir, $themeid, $flist);
				}
			}

			if (count($this->themes) > 0) return true;
		}
		return false;
	}
}


$app_ver  = 1.8;
$app_build = 512;


$kpdbtables = array('playlist', 'playlist_list', 'search', 'users', 'kplayversion', 'mhistory', 'config', 'filetypes', 'settings', 'bulletin', 'cache', 'session', 'iceradio', 'templist', 'network', 'archive', 'message', 'albumcache', 'genre');
foreach ($kpdbtables as $name) define('TBL_'.strtoupper($name), $cfg['dbprepend'].$name);

if ($cfg['enablegetid3'])
{
	if (@include($cfg['getid3include']))
	{
		if (defined('GETID3VERSION'))
		{
			if (function_exists('GetAllFileInfo')) define('GETID3_V', 16);
		} else
		if (defined('GETID3_VERSION'))	
		{	
			if (class_exists('getID3')) define('GETID3_V', 17);
		} else 
		{
			if (class_exists('getID3')) 
			{
				if (method_exists('getID3', 'version')) define('GETID3_V', 19);
			} 

			if (!defined('GETID3_V')) define('GETID3_V', 1);
		}
	}
	if (!defined('GETID3_V')) define('GETID3_V', 0);
}

function getid3support()
{
	if (defined('GETID3_V') && GETID3_V > 1) return true;
	return false;
}

function db_gconnect()
{
	global $db;
	global $mysqli;

	$mysqli = new mysqli($db['host'], $db['user'], $db['pass'], $db['name']);

	if ($mysqli->connect_errno) {
	  echo "Failed to connect to MySQL: " . $mysqli -> connect_error;
	  echo $cfg['db_host'];
	  return false;
	}
	else {
		return true;
	}

}

//if (!function_exists('mysql_connect')) die('Function \'mysql_connect()\' does not exist! You need to compile PHP with MySQL support or enable MySQL support in your php configuration.');

if (function_exists('mysql_real_escape_string')) define('REALESCAPE', true); else define('REALESCAPE', false);

if ($cfg['utf8mode'])
{
	function utferror($module)
	{
		die('You\'ve enabled UTF8 mode, but '.$module.' is not present. Please set $cfg[\'utf8mode\'] to false or get/enable the missing module.');
	}

	if (function_exists('mb_strlen')) 
	{
		if (function_exists('iconv'))
		{
			if (function_exists('mb_check_encoding'))
			{
				mb_internal_encoding('UTF-8');
				mb_http_output('UTF-8');

				if (ini_get('output_handler') == 'mb_output_handler' && ini_get('mbstring.http_output') != 'UTF-8')
					die('mb_output_handler is set, but is not using UTF-8. Please correct your php.ini file or disable UTF8 mode.');
	
					ini_set('default_charset', '');
					$defset = ini_get('default_charset');
					if (strlen($defset) > 0) die('You\'ve enabled UTF8 mode, but you need to turn off "default_charset", it defaults to '.$defset.'. Please edit your php.ini file and comment this line (restart necessary), or turn off utf8mode.');
				
				define('UTF8MODE', true);
			} else utferror('mb_check_encoding');
		} else utferror('iconv');
	} else utferror('mb_strlen');
} else define('UTF8MODE', false);

if ($cfg['authtype'] == 2)
{
	if (!function_exists('session_start')) die('Session auth is specified, but this PHP implementation does not support it.');
	@session_start();
}

function myescstr($str)
{
	global $mysqli;
	if (REALESCAPE && DBCONNECTION) return $mysqli->real_escape_string($str);
	return $mysqli->escape_string($str);
}

class kpdbconnection
{
	function kpdbconnection($query='')
	{
		$this->query = $query;
		$this->res = false;
	}
	
	function preparestmt($sql, $arguments = array())
	{
                global $mysqli;
		$query = $sql;
		$spos = 0;
		$replaced = 0;
                $num_args = sizeof($arguments);
		if (sizeof($arguments) > 0)
		{
			for ($i=0; $i < $num_args; $i++)
			{
                                // Use updated escape string method.
                                $arg = mysqli_real_escape_string($mysqli, $arguments[$i]);
				$lpos = strpos($query, '?', $spos);
				if ($lpos !== false)
				{
					$query = substr($query, 0, $lpos).$arg.substr($query, $lpos+1);
					$spos = $lpos + strlen($arg);
					$replaced++;
				}
			}
		}

		if ($num_args == $replaced)
		{
			$this->query = $query;
		} else user_error('too few arguments passed!!');
	}

	function setquery($query)
	{
		$this->query = $query;
	}

	function query()
	{
		global $mysqli;
		$this->res = false;
		
		if (strlen($this->query) > 0)
		{
			$this->res = $mysqli->query($this->query);
			if ($this->res) return true;
		}

		return false;
	}

	function getautoid()
	{
		global $mysqli;
		return mysqli_insert_id($mysqli);
	}

	function nextrow()
	{
		return mysqli_fetch_assoc($this->res);
	}

	function num()
	{
		return mysqli_num_rows($this->res);
	}
}

function db_execquery($query, $fast=false)
{
	global $mysqli;
	//if ($fast && function_exists('mysql_unbuffered_query')) $res = mysql_unbuffered_query($query); else
	$res = $mysqli->query($query);
	return $res;
}

function db_thread_id()
{
	global $mysqli;
	return $mysqli->thread_id;
}

function db_fetch_assoc($res)
{
	return mysqli_fetch_assoc($res);
}

function db_fetch_row($res)
{
	return mysqli_fetch_row($res);
}

function db_insert_id()
{
	global $mysqli;
	return mysqli_insert_id($mysqli);
}

function db_num_rows($res)
{
	return mysqli_num_rows($res);
}

function db_free($res)
{
	if ($res) mysqli_free_result($res);
}

function db_list_processes()
{
	global $mysqli;
	$ids = array();
        $res = db_execquery("show processlist");
	while($row = db_fetch_row($res)) {
		$ids[$row[0]]= true;
        }
	return $ids;
}

function db_execcheck($query)
{
	global $mysqli;
	if (db_gconnect()) return $mysqli->query($query); else return 0;
}

class settings
{
	function settings()
	{
		$this->dbperform = true;
		$this->table = TBL_CONFIG;
		$this->defaultsloaded = false;
		$this->defaults = false;
		$this->keys = false;
		$this->keysvtype = false;		
	}

	function setdbperform($dbperform)
	{
		$this->dbperform = $dbperform;
	}

	function update($key, $value, $vtype = 0)
	{
		if (!isset($this->keys[$key])) 
			$sql = 'INSERT INTO '.$this->table.' SET `key` = "'.$key.'", value = "'.myescstr($value).'", vtype = '.$vtype; 
		else $sql = 'UPDATE '.$this->table.' SET value = "'.myescstr($value).'", vtype = '.$vtype.' WHERE `key` = "'.$key.'"';
		
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
				$sql = 'INSERT INTO '.$this->table.' SET `key` = "'.myescstr($key).'", value = "'.myescstr($this->defaults[$key][0]).'", vtype = '.$this->defaults[$key][1];
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
			$sql = 'UPDATE '.$this->table.' SET value = "'.myescstr($value).'" WHERE `key` = "'.myescstr($key).'"'; 
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

	function loaddefaults()
	{
		global $app_ver, $app_build;
		if (!$this->defaultsloaded)
		{
			$this->defaults = array(
				'windows'					=> array(0, 1),
				'allowseek'					=> array(1, 1),
				'allowdownload'				=> array(1, 1),
				'base_dir'					=> array('/path/to/my/music/archive/', 0),
				'streamlocation'			=> array('', 0),
				'default_language'			=> array(0, 2),
				'timeout'					=> array(43200, 2),
				'require_https'				=> array(0, 1),
				'report_attempts'			=> array(1 ,1),
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
				'mobilecss'			=> array('', 0),
				'ajaxurl'					=> array('', 0),
				'showupgrade'				=> array(1,1),
				'showstatistics'			=> array(0, 1),
				'writeid3v2'				=> array(0, 1),
				'unauthorizedstreamsextm3u'	=> array(0, 1),
				'optimisticfile'			=> array(0, 1),
				'lamesupport'				=> array(0, 1),
				'smtphost'					=> array('127.0.0.1', 0),
				'smtpport'					=> array('25', 0),
				'enableupload'				=> array(0, 1),
				'uploadpath'				=> array('', 0),
				'mailmp3'					=> array(0, 1),
				'albumcover'				=> array(1, 1),
				'albumfiles'				=> array('*album*.jpg,*album*.gif,*cover*.jpg,*cover*.gif,*front*.jpg,*front*.gif,*.jpg,*.gif', 0),
				'albumresize'				=> array(1, 1),
				'albumheight'				=> array(320, 2),
				'albumwidth'				=> array(400, 2),
				'mailmethod'				=> array(2,2),
				'albumurl'					=> array('http://www.last.fm/music/%artist/%album', 0),
				'fetchalbum'				=> array(0, 1),
				'bulletin'					=> array(1, 1),
				'approvesignup'				=> array(1, 1),
				'followsymlinks'			=> array(0, 1),
				'filetemplate'				=> array('<a href="%i"[ title="%a %y"]><span class="%c">[%R. ][%t - %l|%f]</span></a> <span class="finfo">[(%b kbit %s mins)] %S</span>', 0),
				'urlsecurity'				=> array(0, 1),
				'oldbase_dir'				=> array('', 0),
				'basedir_changed'			=> array(0, 1),
				'uploadflist'				=> array('*', 0),
				'sessionplaylist'			=> array(0, 1),
				'showlyricslink'			=> array(1, 1),
				'lyricsurl'					=> array('http://lyrc.com.ar/en/tema1en.php?songname=%title&amp;artist=%artist', 0),
				'publicrssfeed'				=> array(0, 1),
				'signuptemplate'			=> array(0, 2),
				'updusecache'				=> array(1, 1),
				'utf8mode'					=> array(0, 1),
				'updatemid'					=> array(0, 2),
				'networkmode'				=> array(0, 1),
				'activenetworkhosts'		=> array(0, 1),
				'virtualdir'				=> array(0, 1),
				'shoutbox'					=> array(0, 1),
				'themeid'					=> array(0, 2),
				'reupdate'					=> array(0, 1),
				'bundleconfigured'			=> array(0, 1),
				'storealbumcovers'			=> array(0, 1),
				'storealbumdir'				=> array('', 0),
				'storealbumrelative'		=> array('', 0)
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
		$this->_load('SELECT * FROM '.$this->table);
	}

	function loaduser($id)
	{
		$this->_load('SELECT * FROM '.$this->table.' WHERE uid = '.$id);
	}

	function _load($sql)
	{
		$this->keys = array();
		$this->keysvtype = array();
		$res = db_execquery($sql);
		if ($res !== false)
			while ($row = db_fetch_row($res)) $this->insert($row[1], $row[2], $row[3]); 
	}
}

class usersettings extends settings
{
	function usersettings($uid)
	{
		settings::settings();
		$this->table = TBL_UCONFIG;
		$this->loaduserdefaults();
		$this->loaduser($uid);
	}

	function loaduserdefaults()
	{
		$udefaults = 
			array(
				'download' => array(0, 1),
				'downloadrate' => array(0, 1),
				'archivedownload' => array(0, 1),
				'stream' => array(1, 1),
				'lame' => array(0, 1),
				'lamerate' => array(0, 2),
				'forcerate' => array(0, 1),
				'mp3mail' => array(1, 1),
				'pltype' => array(1, 2),
				'upload' => array(1, 1),
				'bulletinpublish' => array(1, 1),
				'adduser' => array(0, 1),
				'moduser' => array(0, 1),
				'deluser' => array(0, 1)
				
		);

		foreach ($udefaults as $name => $value) 
			$this->insert($name, $udefaults[$name][0], $udefaults[$name][1]);
	}
}

$varcache = array();

function getcache($id, &$data)
{
	global $varcache;
	if (isset($varcache[$id])) 
	{
		$data = $varcache[$id];
		return true;
	} else
	{
		$res = db_execquery('SELECT id, value FROM '.TBL_CACHE);
		if (mysqli_num_rows($res) > 0)
		{
			while ($row = db_fetch_row($res)) $varcache[$row[0]] = $row[1];
			if (isset($varcache[$id])) 
			{
				$data = $varcache[$id];
				return true;
			}
		}
	}
	return false;
}

function updatecache($id, $value)
{
	global $varcache;
	$out = '';
	if (getcache($id, $out))
		$sql = 'UPDATE '.TBL_CACHE.' SET value = "'.myescstr($value).'" WHERE id = '.$id;
	else $sql = 'INSERT INTO '.TBL_CACHE.' SET id = '.$id.', value = "'.myescstr($value).'"';
	$varcache[$id] = $value;
	db_execquery($sql);
}

$setctl = new settings();

if (db_gconnect())
{
	define('DBCONNECTION', true);
	
	if (UTF8MODE) db_execquery('SET NAMES utf8 COLLATE utf8_unicode_ci');
	
	$setctl->load();

	if ($resetconfiguration) 
	{
		$setctl->defaults();
		echo 'Configuration has been reset. Set $resetconfiguration = false; and reload.';
		die();
	}

	if (!$setctl->get('bundleconfigured'))
	{
		if (isset($bundleconfig) && is_array($bundleconfig))
		{
			foreach($bundleconfig as $name => $val)
			{
				$tval = $setctl->get($name);
				if (strlen($tval) == 0 || $tval == false || $tval == 0) $setctl->set($name, $val);				
			}
		
			$setctl->set('bundleconfigured', 1);
		}
	}
} else
{
	define('DBCONNECTION', false);

	if (!$cfg['installerenabled'])
	{
		echo 'Can\'t connect to the database and the installer is disabled. (If you need to re-install switch $cfg[\'installerenabled\'] to true.)';
		die();
	}
	
	$setctl->setdbperform(false);
	$setctl->defaults();
}

class basedir
{
	function basedir()
	{
		$this->basedirs = array();
		$this->driveaccess = array();
		$this->cnt = 0;
		$this->init();
	}

	function init()
	{
		global $setctl, $cfg;
		
		$defaccess = array();
		$defaccess[] = array('a', 0);

		$basedirs = explode(';', $setctl->get('base_dir'));

		for($i=0,$c=count($basedirs);$i<$c;$i++) 
		{
			if (strlen($basedirs[$i]) > 0) $this->initbase('l', $basedirs[$i], $defaccess);
		}
			
		if (DBCONNECTION && $setctl->get('activenetworkhosts'))
		{
			$ndb = new networkdb();
			$hosts = $ndb->getenabled();
			for ($i=0,$c=count($hosts);$i<$c;$i++) $this->initbase('n', $hosts[$i], $defaccess);
		}

	}

	function initbase($type, $location, $access)
	{
		$this->basedirs[] = array($type, $location);
		$this->driveaccess[] = $access;
		$this->cnt = count($this->basedirs);
	}

	function initusers()
	{
		$res = db_execquery('SELECT u_id, homedir FROM '.TBL_USERS.' WHERE trim(homedir) != "" ORDER BY u_id ASC');
		if ($res)
		{
			while ($row = db_fetch_row($res))
			{
				$access = array();
				$access[] = array('u', $row[0]);
				$access[] = array('g', 0);
				
				$this->initbase('l', $row[1], $access);
			}
			db_free($res);
		}
	}

	function accessok($drive)
	{
		if (isset($this->driveaccess[$drive]) && is_array($this->driveaccess[$drive]))
		{
			for ($i=0,$c=count($this->driveaccess[$drive]);$i<$c;$i++)
			{
				switch($this->driveaccess[$drive][$i][0])
				{
					case 'a':	return true; break;

					case 'u':
								if ($this->driveaccess[$drive][$i][1] == db_guinfo('u_id')) return true; break;
					case 'g':
								if ($this->driveaccess[$drive][$i][1] == db_guinfo('u_access')) return true; break;
				}
			}
		} 
		return false;	
	}

	function genxdrive($name='drive', $oper='AND')
	{
		$xdrives = array();
		for($i=0;$i<$this->cnt;$i++)
			if ($this->accessok($i)) $xdrives[] = $i;
		if (count($xdrives) > 0) return ' '.$oper.' ('.mkor($xdrives, $name).')';
		return '';
	}

	function isnetwork($drive)
	{
		if (isset($this->basedirs[$drive]) && $this->basedirs[$drive][0] == 'n') return true;
		return false;
	}

	function gtype($drive)
	{
		return $this->basedirs[$drive][0];
	}
	
	function getpath($drive)
	{
		if (isset($this->basedirs[$drive])) return $this->basedirs[$drive][1];
	}

	function isdrive($drive)
	{
		if (isset($this->basedirs[$drive])) return true;
		return false;
	}

	function getcnt()
	{
		return $this->cnt;
	}
}

$bd = new basedir();

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
$setctl->publish('filetemplate');
$setctl->publish('urlsecurity');
$setctl->publish('showlyricslink');
$setctl->publish('networkmode');
$setctl->publish('virtualdir');
$setctl->publish('shoutbox');
$setctl->publish('themeid');
$setctl->publish('dlrate');
$setctl->publish('albumresize');

$deflanguage = $setctl->get('default_language');
$win32 = $setctl->get('windows');

if ($win32 && !isphp5()) define('STR_ENGINE', false);
	else define('STR_ENGINE', true); 

$runinit = array('pdir' => '', 'pdir64' => '', 'drive' => 0, 'astream' => 1);

if (!function_exists('db_list_processes') || !function_exists('db_thread_id')) $runinit['astream'] = 0;

// general - used as globals

$dir_list = $mark = array();
$marksid = $u_cookieid = $u_id = -1;
$valuser = false;

if (frm_ok('d', 1)) $runinit['drive'] = frm_get('d', 1); else if (frm_ok('drive', 1)) $runinit['drive'] = frm_get('drive', 1); 

$phpenv = array();

if (!isset($PHP_SELF) || empty($PHP_SELF)) $PHP_SELF = $_SERVER['PHP_SELF'];

if ($cfg['badblue'])
{
	$qpos = strrpos($PHP_SELF, '?');
	if ($qpos !== false) $PHP_SELF = substr($PHP_SELF, 0, $qpos);
}

if ($cfg['ordertrack']) define('ORDERBYTRACK', true); else define('ORDERBYTRACK', false);

function phpfigure()
{
	global $phpenv, $setctl, $PHP_SELF, $_SERVER;

	if (!isset($_SERVER['REMOTE_ADDR'])) die('No IP address - kPlaylist is meant to be running from a browser.');

	$phpenv['streamlocation'] = $setctl->get('streamlocation');
	if (strlen($phpenv['streamlocation']) == 0)
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

		$phpenv['streamlocation'] = $host.str_replace(' ', '%20', $script);
		$phpenv['host'] = noslash($host);
	} else $phpenv['host'] = noslash($_SERVER['SERVER_NAME']);

	if (!defined('PHPSELF')) define('PHPSELF', $PHP_SELF);

	$relative = dirname(PHPSELF);
	if ($relative == '/' || $relative == '\\') $phpenv['relative'] = ''; else $phpenv['relative'] = $relative;

	$phpenv['location'] = dirname($phpenv['streamlocation']);
	
	if (isset($_SERVER['REQUEST_URI'])) $phpenv['uri'] = $_SERVER['REQUEST_URI']; else $phpenv['uri'] = '';

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

$kpt = new kptheme();
if (THEMEID != 0) $kpt->load(THEMEID);

if ($cfg['archivemode'] && extension_loaded('zip'))
{
	$archivers[] = array(1, 'zip', 'INB1', 'application/zip', 'zip inbuilt');
}

$ajaxurl = $setctl->get('ajaxurl');
if (strlen($ajaxurl) > 0)
{
	define('AJAX', true);
} else define('AJAX', false);

if (DBCONNECTION)
{
	$streamtypes = $streamtypes_default;
	$res = db_execquery('SELECT extension, mime, m3u, getid, search, logaccess FROM '.TBL_FILETYPES.' WHERE enabled = 1', true);
	if ($res) 
	{
		while ($row = db_fetch_row($res)) $streamtypes[] = $row;
		db_free($res);
	}

	if ($cfg['userhomedir']) $bd->initusers();

} else $streamtypes = array();


class kpmysqltable
{
	function kpmysqltable()
	{
		$this->install_sql = array();
		$this->install_sql_user = array();

		$this->dbcols = array();

		$this->dbtable = 
		array(
			TBL_MHISTORY => 10, 
			TBL_CONFIG => 11, 
			TBL_FILETYPES => 13, 
			TBL_PLAYLIST => 2, 
			TBL_PLAYLIST_LIST => 3, 
			TBL_SEARCH => 4, 
			TBL_USERS => 5, 
			TBL_KPLAYVERSION => 6, 
			TBL_BULLETIN => 14, 
			TBL_CACHE => 15, 
			TBL_SESSION => 16, 
			TBL_ICERADIO => 17, 
			TBL_TEMPLIST => 18, 
			TBL_NETWORK => 19, 
			TBL_ARCHIVE => 20,
			TBL_MESSAGE => 21,
			TBL_ALBUMCACHE => 22,
			TBL_GENRE => 23
		);
	
		// 0 = NULL
		// 1 = NOT NULL

		$this->dbdef[TBL_USERS] = 
		array(
			'u_name'			=> array('VARCHAR', 64, 1, "''", '', 1),
			'u_pass'			=> array('VARCHAR', 32, 1, "''"),
			'u_login'			=> array('VARCHAR', 32, 1, "''"),
			'u_comment'			=> array('VARCHAR', 64, 0, "''", '', 1),
			'u_id'				=> array('INT', 4, 1, '', 'AUTO_INCREMENT'),
			'u_booted'			=> array('TINYINT', 4, 1, "'0'"),
			'u_status'			=> array('TINYINT', 4, 1, "'0'"),
			'u_access'			=> array('TINYINT', 4, '', "'1'"),
			'u_allowdownload'	=> array('CHAR', 1, 1, '\'1\''),
			'allowarchive'		=> array('CHAR', 1, 1, '\'1\''),
			'archivesize'		=> array('INT', 4, 1, '\'0\''),
			'extm3u'			=> array('CHAR',1, 1, '\'1\''),
			'defplaylist'		=> array('INT', 4, 1, '\'0\''),
			'defshplaylist'		=> array('INT', 4, 1, '\'0\''),
			'defaultid3'		=> array('CHAR', 1, 1, '\'0\''),
			'defstationid'		=> array('INT', 4, 1, '\'0\''), 
			'defaultsearch'		=> array('INT', 1, 1,'\'0\''),
			'partymode'			=> array('CHAR', 1, 1, '\'0\''), 
			'theme'				=> array('BIGINT', 4, 1, '\'1\''),
			'lockedtime'		=> array('INT', 8, 1, '\'0\''),
			'hotrows'			=> array('INT', 4, 1,'\'21\''),
			'searchrows'		=> array('INT', 4, 1, '\'21\''),
			'detailrows'		=> array('INT', 4, 1, '\'5\''),
			'lang'				=> array('TINYINT', 4, 1, '\'0\''),
			'udlrate'			=> array('INT', 4, 1, '\'0\''),
			'defgenre'			=> array('INT', 4, 1, '\'0\''),
			'archer'			=> array('CHAR', 1, 1, '\'0\''),
			'hitsas'			=> array('TINYINT', 4, 1, '\'0\''),
			'lameperm'			=> array('CHAR', 1, 1, '\'0\''),
			'lamerate'			=> array('INT', 4, 1, '\'0\''),
			'allowemail'		=> array('CHAR', 1, 1, '\'0\''),
			'email'				=> array('VARCHAR', 128, 1, '\'\''),
			'plinline'			=> array('CHAR', 1, 1, '\'1\''),
			'hotmode'			=> array('INT', 4, 1, '\'0\''),
			'created'			=> array('INT', 4, 1, '\'0\''),
			'laston'			=> array('INT', 4, 1, '\'0\''),
			'pltype'			=> array('INT', 4, 1, '\'1\''),
			'orsearch'			=> array('CHAR', 1, 1, '\'0\''),
			'textcut'			=> array('INT', 2 ,1, '80'),
			'dircolumn'			=> array('INT', 2, 1, '3',),
			'streamengine'		=> array('CHAR', 1, 1, '\'1\''),
			'utemplate'			=> array('CHAR', 1, 1, '0'),
			'homedir'			=> array('VARCHAR', 255, 1, '\'\''),
			'detailview'		=> array('CHAR', 1 ,1, '\'1\''),
			'forcelamerate'		=> array('INT', 4, 1, '\'0\''),
			'network'			=> array('CHAR', 1, 1, '\'0\'')
		);

		$this->dbkeys[TBL_USERS] = 
		array(
			'PRIMARY KEY (u_id)', 
			'UNIQUE KEY u_login (u_login)'
		);

		$this->dbdef[TBL_ICERADIO] =
		array(
			'stationid'		=> array('INT', '4', 1, '', 'AUTO_INCREMENT'),
			'name'			=> array('VARCHAR', 64, 1, '\'\'', '', 1),
			'playlistid'	=> array('INT', 11, 1, '\'0\''),
			'lactive'		=> array('INT', 4, 1, '\'0\''),
			'curseq'		=> array('INT', 4, 1, '\'0\''),
			'nextseq'		=> array('INT', 4, 1, '\'0\''),
			'pass'			=> array('VARCHAR', 64, 1, '\'\''),
			'loop'			=> array('CHAR', 1, 1, '\'0\'')
			);

		$this->dbkeys[TBL_ICERADIO][] = 'PRIMARY KEY (stationid)';

		$this->dbdef[TBL_PLAYLIST] = 
		array(
			'u_id'		=> array('INT', 4, 1, '0'),
			'name'		=> array('VARCHAR', 32, 1, '\'\'', '', 1),
			'public'	=> array('CHAR', 1, 1, '0'),
			'status'	=> array('TINYINT', 1, 1, '0'),
			'listid'	=> array('INT', 11, 1, '', 'AUTO_INCREMENT')
		);

		$this->dbkeys[TBL_PLAYLIST][] = 'PRIMARY KEY (listid)';
		$this->dbkeys[TBL_PLAYLIST][] = 'UNIQUE KEY u_login (u_id,name)';


		$this->dbdef[TBL_PLAYLIST_LIST] = 
		array(
			'listid'	=> array('INT', 11, 1, '\'0\''),
			'id'		=> array('INT', 11, 1, '', 'AUTO_INCREMENT'),
			'sid'		=> array('INT', 4, 1, '\'0\''),
			'seq'		=> array('INT', 4, 1, '\'0\'')
		);

		$this->dbkeys[TBL_PLAYLIST_LIST][] = 'PRIMARY KEY (id)';
		$this->dbkeys[TBL_PLAYLIST_LIST][] = 'KEY `listid` (`listid`)';

		$this->dbdef[TBL_SEARCH] = 
		array(
			'id'		=> array('INT', 11, 1, '', 'AUTO_INCREMENT'),
			'xid'		=> array('INT', 11, 1, '0'),
			'f_stat'	=> array('INT', 4, 1, '\'0\''),
			'track'		=> array('INT', 4, 1, '\'0\''),
			'year'		=> array('INT', 4, 1, '\'0\''),
			'title'		=> array('VARCHAR', 255, 1, '\'\'', '', 1),
			'comment'	=> array('VARCHAR', 255, 1, '\'\'', '', 1),
			'dirname'	=> array('VARCHAR', 255, 1, '\'\'', '', 1),	
			'free'		=> array('VARCHAR', 255, 1, '\'\'', '', 1),
			'fpath'		=> array('MEDIUMBLOB', '', 1, ''),
			'fname'		=> array('TINYBLOB', '', 1, ''),
			'album'		=> array('VARCHAR', 255, 1, '\'\'', '', 1),
			'artist'	=> array('VARCHAR', 255, 1, '\'\'', '', 1),
			'md5'		=> array('VARCHAR',(32), 1, '\'\''),
			'hits'		=> array('INT', 4, 1, '\'0\''),
			'mtime'		=> array('INT', 4, 1, '\'0\''),
			'ltime'		=> array('INT', 4, 1, '\'0\''),
			'date'		=> array('INT', 4, 1, ''),
			'fsize'		=> array('INT', 4, 1, ''),
			'genre'		=> array('INT', 4, 1, '\'255\''),
			'bitrate'	=> array('INT', 4, 1, '\'0\''),
			'ratemode'	=> array('TINYINT', 4, '', '\'0\''),
			'lengths'	=> array('INT', 4, 1, '\'0\''),
			'drive'		=> array('TINYINT', 4, '', '\'0\''),
			'ftypeid'	=> array('INT', 4, 1, '\'-1\''),
			'id3image'	=> array('CHAR', 1, 1, '\'0\'')
		);

		$this->dbkeys[TBL_SEARCH] = 
		array(
			'PRIMARY KEY (id)',
			'KEY `xid` (`xid`)',
			'KEY `dirname` (`dirname`)',
			'KEY `free` (`free`)',
			'KEY `artist` (`artist`)',
			'KEY `album` (`album`)',
			'KEY `title` (`title`)',
			'KEY `fsize` (`fsize`)',
			'KEY `date` (`date`)',
			'KEY `f_stat` (`f_stat`)',
			'KEY `drive` (`drive`)',
			'KEY `ftypeid` (`ftypeid`)',
			'KEY `fname` (`fname`(255))',
			'KEY `fpath` (`fpath`(255))'
		);

		$this->dbdef[TBL_KPLAYVERSION] = 
		array(
			'app_ver'		=> array('VARCHAR', 6, 1, '\'\''),
			'app_build'		=> array('VARCHAR', 6, 1, '\'\''),
			'app_finstall'	=> array('INT', 4, 1, '0')
		);

		$this->dbdef[TBL_MHISTORY] = 
		array(
			'h_id'			=> array('INT', 4, 1, '', 'AUTO_INCREMENT'),
			'u_id'			=> array('INT', 4, 1, ''),
			's_id'			=> array('INT', 4, 1, ''),
			'tid'			=> array('TINYINT', 4, 1, '\'0\''),
			'utime'			=> array('INT', 4, 1, ''),
			'dwritten'		=> array('INT', 4, 1, '0'),
			'dpercent'		=> array('INT', 4, 1, '0'),
			'cpercent'		=> array('INT', 4, 1, '0'),
			'active'		=> array('TINYINT', 4, 1, '0'),
			'mid'			=> array('INT', 4, 1, '0')
		);

		$this->dbkeys[TBL_MHISTORY] =
		array(
			'PRIMARY KEY (h_id)',
			'KEY `s_id` (`s_id`)',
			'KEY `u_id` (`u_id`)',
			'KEY `utime` (`utime`)'
		);

		$this->dbdef[TBL_CONFIG] = 
		array(
			'id'		=> array('INT', 4, 1, '', 'AUTO_INCREMENT'),
			'key'		=> array('VARCHAR', 255, 1, ''),
			'value'		=> array('TEXT', '', 1, ''),
			'vtype'		=> array('INT', 2, 1, '')
		);

		$this->dbkeys[TBL_CONFIG][] = 'UNIQUE (id, `key`)';
		$this->dbkeys[TBL_CONFIG][] = 'KEY `key` (`key`)';


		$this->dbdef[TBL_FILETYPES] = 
		array(
			'id'		=> array('INT', 4, 1, '', 'AUTO_INCREMENT'),
			'extension'	=> array('VARCHAR', 32, 1, '\'\''), 
			'mime'		=> array('VARCHAR', 128, 1, '\'\''),
			'm3u'		=> array('CHAR', 1, 1, '\'\''), 
			'getid'		=> array('INT', 4, 1, '0'),
			'search'	=> array('CHAR', 1, 1, '\'1\''),
			'logaccess'	=> array('CHAR', 1, 1, '\'1\''),
			'enabled'	=> array('CHAR', 1, 1, '\'1\'')
		);

		$this->dbkeys[TBL_FILETYPES][] = 'PRIMARY KEY (`id`)';


		$this->dbdef[TBL_BULLETIN] = 
		array(
			'bid'		=> array('INT', 4, 1, '', 'AUTO_INCREMENT'),
			'u_id'		=> array('INT', 4, 1, ''),
			'utime'		=> array('INT', 4, 1, ''),
			'publish'	=> array('INT', 4, 1, '0'),
			'mesg'		=> array('TEXT', '', 1, '', '', 1)
		);

		$this->dbkeys[TBL_BULLETIN][] = 'PRIMARY KEY (`bid`)';


		$this->dbdef[TBL_CACHE] = 
		array(
			'cacheid'	=> array('INT', 4, 1, '', 'AUTO_INCREMENT'),
			'id'		=> array('INT', 4, 1, ''),
			'value'		=> array('TEXT', '', 1, '')
		);

		$this->dbkeys[TBL_CACHE][] = 'PRIMARY KEY (`cacheid`)';

		$this->dbdef[TBL_SESSION] = 
		array(
			'sessionid'		=> array('BIGINT', 16, 1, '', 'AUTO_INCREMENT'),
			'u_id'			=> array('INT', 4, 1, '\'0\''),
			'ip'			=> array('BIGINT', 4, 1, '\'0\''),
			'login'			=> array('INT', 4, 1, '\'0\''),
			'refreshed'		=> array('INT', 4, 1, '\'0\''),
			'logout'		=> array('INT', 4, 1, '\'0\''),
			'sstatus'		=> array('INT', 4, 1, '\'0\''),
			'hkey'			=> array('VARCHAR', 128, 1, '\'\'')
		);
		 
		$this->dbkeys[TBL_SESSION][] = 'PRIMARY KEY (`sessionid`)';
		$this->dbkeys[TBL_SESSION][] = 'KEY `u_id` (`u_id`)';


		$this->dbdef[TBL_TEMPLIST] =
		array(
			'rid'			=> array('INT', 4, 1, '', 'AUTO_INCREMENT'),
			'uid'			=> array('INT', 4, 1, '0'),
			'sid'			=> array('INT', 4, 1, '0')
		);

		$this->dbkeys[TBL_TEMPLIST][] = 'PRIMARY KEY (`rid`)';
		$this->dbkeys[TBL_TEMPLIST][] = 'KEY uid (`uid`)';

		$this->dbdef[TBL_NETWORK] =
		array(
			'nid'			=> array('INT', 4, 1, '', 'AUTO_INCREMENT'),
			'enabled'		=> array('CHAR', 1, 1, '\'1\''), 
			'url'			=> array('TEXT', '', 1, ''),
			'username'		=> array('VARCHAR', 64, 1, '0'),
			'password'		=> array('VARCHAR', 64, 1, '0')
		);

		$this->dbkeys[TBL_NETWORK][] = 'PRIMARY KEY (`nid`)';


		$this->dbdef[TBL_ARCHIVE] =
		array(
			'aid'			=> array('INT', 4, 1, '', 'AUTO_INCREMENT'),
			'uid'			=> array('INT', 4, 1, ''), 
			'utime'			=> array('BIGINT', 8, 1, 0),
			'fpath'			=> array('MEDIUMBLOB', '', 1, '')	
		);

		$this->dbkeys[TBL_ARCHIVE][] = 'PRIMARY KEY (`aid`)';
		$this->dbkeys[TBL_ARCHIVE][] = 'KEY uid (`uid`)';


		$this->dbdef[TBL_MESSAGE] =
		array(
			'meid'			=> array('INT', 4, 1, '', 'AUTO_INCREMENT'),
			'uid'			=> array('INT', 4, 1, ''), 
			'utime'			=> array('BIGINT', 8, 1, 0),
			'message'		=> array('TEXT', '', 1, '', '', 1)
		);

		$this->dbkeys[TBL_MESSAGE][] = 'PRIMARY KEY (`meid`)';
		$this->dbkeys[TBL_MESSAGE][] = 'KEY uid (`uid`)';

		$this->dbdef[TBL_ALBUMCACHE] =
		array(
			'rid'			=> array('INT', 4, 1, '', 'AUTO_INCREMENT'),
			'album'			=> array('VARCHAR', 128, 1, '\'\'', '', 1),
			'artist'		=> array('VARCHAR', 128, 1, '\'\'', '', 1),
			'id'			=> array('INT', 11, 1, 0),
			'idid3'			=> array('INT', 11, 1, 0)
		);

		$this->dbkeys[TBL_ALBUMCACHE][] = 'PRIMARY KEY (rid)';
		$this->dbkeys[TBL_ALBUMCACHE][] = 'KEY album (`artist`, `album`)';

		$this->dbdef[TBL_GENRE] =
                array(
                        'gid'                 	=> array('INT', 4, 1, '', 'AUTO_INCREMENT'),
                        'name'                  => array('VARCHAR', 255, 1, '\'\'', '', 1)
                );

		$this->dbkeys[TBL_GENRE][] = 'PRIMARY KEY (gid)';
		$this->dbkeys[TBL_GENRE][] = 'UNIQUE KEY name (name)';
	
		$this->init();
	}

	function createrowdef($def)
	{
		$out = $def[0];	
		if (strlen($def[1]) > 0) $out .= '('.$def[1].')';
		$out .= ' ';

		if (isset($def[5]) && $def[5] == 1 && UTF8MODE) $out .= 'character set utf8 ';

		switch($def[2])
		{
			case '0': $out .= 'NULL'; break;
			case '1': $out .= 'NOT NULL'; break;
			default: $out .= $def[2]; break;				
		}
		
		if (strlen($def[3]) > 0) $out .= ' DEFAULT '.$def[3];
		if (isset($def[4]) && strlen($def[4])> 0) $out .= ' '.$def[4];
				
		return $out;
	}

	function createdbdefinition($table, $autoinc=0)
	{
		$out = 'CREATE TABLE '.$table.' (';

		foreach($this->dbdef[$table] as $column => $def) 
		{
			$out .= "\n".'  `'.$column.'` ';
				
			if (is_array($def)) $out .= $this->createrowdef($def).',';
				else $out .= $def.',';
		}
		
		if (isset($this->dbkeys[$table]))
		{
			for ($i=0,$c=count($this->dbkeys[$table]);$i<$c;$i++)
			{
				$out .= "\n".'  '.$this->dbkeys[$table][$i];
				if ($i + 1 < $c) $out .= ',';
			}
		} else $out = substr($out, 0, strlen($out) - 1);
		$out .= "\n".')';
		if ($autoinc > 0) $out .= ' AUTO_INCREMENT='.$autoinc;
		return $out;
	}

	function init()
	{
		global $db, $app_ver;
		
		foreach($this->dbdef as $tblname => $tblarr) 
			foreach($tblarr as $rowname => $rowsql) $this->dbcols[$tblname][] = $rowname;

		$this->install_sql[0] = '';
		$this->install_sql[1] = 'CREATE DATABASE IF NOT EXISTS '.$db['name'];
		$this->install_sql[2] = $this->createdbdefinition(TBL_PLAYLIST);
		$this->install_sql[3] = $this->createdbdefinition(TBL_PLAYLIST_LIST);
		$this->install_sql[4] = $this->createdbdefinition(TBL_SEARCH, 1);
		$this->install_sql[5] = $this->createdbdefinition(TBL_USERS, 1);
		$this->install_sql[6] = $this->createdbdefinition(TBL_KPLAYVERSION);
		$this->install_sql[7] = 'DELETE FROM '.TBL_KPLAYVERSION;
		$this->install_sql[8] = 'INSERT INTO '.TBL_KPLAYVERSION.' (app_ver, app_build, app_finstall) VALUES ("'.$app_ver.'", "0", "'.time().'")';
		$this->install_sql[9] = 'INSERT INTO '.TBL_USERS.' SET u_name = "admin", u_login = "admin", u_pass = "'.md5('admin').'",  u_comment = "admin", u_access = "0", created = '.time();
		$this->install_sql[10] = $this->createdbdefinition(TBL_MHISTORY);
		$this->install_sql[11] = $this->createdbdefinition(TBL_CONFIG);

		$win32inst = 0;

		if (isset($_SERVER['SERVER_SOFTWARE']))
		{
			if (preg_match("/win/i", $_SERVER['SERVER_SOFTWARE']) || preg_match("/microsoft/i", $_SERVER['SERVER_SOFTWARE'])) $win32inst = 1;
		}

		$this->install_sql[12] = 'INSERT INTO '.TBL_CONFIG.' set `key` = "windows", value = "'.$win32inst.'", vtype = 1';

		$this->install_sql[13] = $this->createdbdefinition(TBL_FILETYPES);
		$this->install_sql[14] = $this->createdbdefinition(TBL_BULLETIN);
		$this->install_sql[15] = $this->createdbdefinition(TBL_CACHE);
		$this->install_sql[16] = $this->createdbdefinition(TBL_SESSION, getrand(1));

		$this->install_sql[17] = $this->createdbdefinition(TBL_ICERADIO, 1);

		$this->install_sql[18] = $this->createdbdefinition(TBL_TEMPLIST, 1);

		$this->install_sql[19] = $this->createdbdefinition(TBL_NETWORK, 1);

		$this->install_sql[20] = $this->createdbdefinition(TBL_ARCHIVE, 1);

		$this->install_sql[21] = $this->createdbdefinition(TBL_MESSAGE, 1);

		$this->install_sql[22] = $this->createdbdefinition(TBL_ALBUMCACHE, 1);

		$this->install_sql[23] = $this->createdbdefinition(TBL_GENRE, 1);

		$this->install_sql_user[0] = 'GRANT ALL ON '.$db['name'].'.* TO '.$db['user'].'@"%h" IDENTIFIED BY "'.$db['pass'].'"';
		$this->install_sql_user[1] = 'SET PASSWORD FOR '.$db['user'].'@'.$db['host']." = OLD_PASSWORD('".$db['pass']."')";
		$this->install_sql_user[2] = 'FLUSH PRIVILEGES';
	}

	function getdbcols()
	{
		return $this->dbcols;
	}

	function getdbtable()
	{
		return $this->dbtable;
	}
	
	function getdbdef()
	{
		return $this->dbdef;
	}

	function getinstallsql()
	{
		return $this->install_sql;
	}

	function getinstallsqluser($host)
	{
		switch($host)
		{
			case 'localhost':
			case '127.0.0.7':
			break;

			default:
				$host = '%';
			break;
		}
		
		$isu = $this->install_sql_user;
		$isu[0] = str_replace('%h', $host, $isu[0]);
		return $isu;
	}
}


function pic_headers($fname, $base64, $mime='image/gif')
{
	header('Content-Disposition: inline; filename='.$fname);
	header('Content-Type: '.$mime);
	header('Content-Length: '.strlen($base64));
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Content-Transfer-Encoding: binary');
	header('Expires: '. gmdate('D, d M Y H:i ', time()+24*60*60) . ' GMT');
	header('Pragma: public');
	echo $base64;
}

function image_rss($fname)
{
	pic_headers($fname, base64_decode('R0lGODlhEgAHAIAAAO93AP///yH5BAAAAAAALAAAAAASAAcAAAIXhI+ZwcdhHoIA0qhqi3'.
'c7/IWgtUnZmRQAOw=='));
}

function image_saveicon($fname) 
{
	pic_headers($fname, base64_decode('R0lGODlhCwALALMAAL+/v////35+fj09PV5eXgAAAI6Ojm5ubt'.
'/f3y0tLc/Pz+/v701NTR0dHZ6engAAACH5BAAAAAAALAAAAAAL'.
'AAsAAAQ3EIFJaUjGVECEGVMgigIggMBImqgalKfCCDRNsO6II8'.
'cxDIIAjlIoDISCxIJoRCoIUEKxISA4IgA7'.
''));
}

function image_play($fname)
{
	pic_headers($fname, base64_decode('R0lGODlhEgASANU/APrq0uqiNOaOCOypRO6wVPG3X+eREfbYqu60XfCjMO2uUfC6afLDff'.
'GpPf3u2O+hLPnjwvTNku6yWPfbseumPe+4ZOqfLeiZIeiWGvPJi/79+eaQDeeTFOyrSe2s'.
'TPrnyu+2YfC8bv779u2aHOiXHPjhvfLGgueTFvG9cOukOe2rSumbJuiYH+eVGO6ZHP/15u'.
'mcJ+mcKPXSnu+fJf3YoPSsP/zQjvvOi+yoQe6zW++5Z/vKgPC7bPKnN/HAdf///yH5BAEA'.
'AD8ALAAAAAASABIAAAbZwJ/wJ5owFCkKKALRDJ8lEOaysgRSOFUG8PwdUpwpzJoaqAi+z7'.
'AUMIQv4wBl4CHkGA6iROCexqxzdQgVMhoHAhsGJyQXf3J0BIMhAAuIiiQuNzMPgZEVPBMB'.
'li0kIy8ONQkDCpE6IREGo5gvtDQ9Da0oDLGJpKa0pwUSIK4Mor0sv7Q7NcM6KBE8iBy+tD'.
'Y9PQrDCygThxvUybYPOB3aINwAIjngLSxVV+XnC4U/JWAY72RZCjkgJnmEHKAwBV6gfgzU'.
'PIGgAw4gSBG4dCEywYSEcguYOBkSBAA7'));
}

function image_dir($fname) 
{
	pic_headers($fname, base64_decode('R0lGODlhEgANAKIAAPf39///zpycAP/OnM7OY////wAAAP//nC'.
'wAAAAAEgANAAADRVglzKYwKgFCOEc8CQX5INE0kWCdqHUQ23Jh'.
'cDyw3RsfA7625g3nM54NA9TRJkOiLlj7LXFMZHE6iC5C2FCrYO'.
'h6v19IAgA7'.
''));
}

function image_kplaylist($fname)
{
	pic_headers($fname, base64_decode('R0lGODlh0ABAAPcAALPV57nY6P+tAJVlAJXF3ciIAMHd69qUAG'.
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
''));
  }

function image_link($fname)
{
 pic_headers($fname, base64_decode(
'R0lGODlhCgAKANX/APn28ffz7fHp3+7j1uvf0Onby+jayOfXxu'.
'XUwOLYzOLQut/Ls9/Ksd3HrNrSydS5mdS4l9G0kdCzj8+wi8ys'.
'hsvHw8rHw7+WZb6WZLujhrmTZbaHT7KBRrB+Qqt7P6CPfI5mNY'.
'B5cIBbL3VUK3RTK21cSVg/IVZNQFA5Hko+L0gzG0Y7LS8iESwg'.
'EP///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'.
'AAAAAAAAAAAAAAAAAAAAAAACH/C0FET0JFOklSMS4wAt7tACH5'.
'BAEAAC4ALAAAAAAKAAoAAAZCQJdwSHQBBIcGYzAMEAqIx0WTEB'.
'oWCAWlI/oIJRNIZENSpYQYjgeEarVWwszIxHK3QkJHyd46WYYV'.
'ISsrIX9Fhy5BADs='.
''));
}
 
function image_root($fname)
{
	pic_headers($fname, base64_decode(
'R0lGODlhEgALAKIAAP///8zMzJmZmWZmZgAAAAAAAAAAAAAAAC'.
'H5BAAAAAAALAAAAAASAAsAAAMyCLrcPjDKyEa4ON9B1HCOwHla'.
'BgliB3yChgZtSk6DeMprAC7wCNi0iUpBKBqPxp2SkQAAOw=='.
''));
}

function image_cdback($fname)
{
	pic_headers($fname, base64_decode('R0lGODlhDwANAKIAAP//////zP//mf/MmczMZpmZAAAAAAAAAC'.
'H5BAAAAAAALAAAAAAPAA0AAANCCFDMphAWEIIQ5cVFuidNs1Rk'.
'WQmEUViXcb3CkK6t4cb4bNn8NcS6l+tHRKlYgltRdhTybAPmao'.
'mLSj/Yz+PJ5SYAADs='.
''));
}

function image_login($fname) 
{
	pic_headers($fname, base64_decode(
'/9j/4AAQSkZJRgABAgAAZABkAAD/7AARRHVja3kAAQAEAAAAUAAA/+4ADkFkb2JlAGTAAA'.
'AAAf/bAIQAAgICAgICAgICAgMCAgIDBAMCAgMEBQQEBAQEBQYFBQUFBQUGBgcHCAcHBgkJ'.
'CgoJCQwMDAwMDAwMDAwMDAwMDAEDAwMFBAUJBgYJDQsJCw0PDg4ODg8PDAwMDAwPDwwMDA'.
'wMDA8MDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwM/8AAEQgBRwJYAwERAAIRAQMRAf/E'.
'AKkAAQAABwEBAQAAAAAAAAAAAAABAgQFBgcIAwkKAQEBAQEBAQEBAAAAAAAAAAAAAQIDBA'.
'UGBxAAAQIFAgQEBAIFCAUKBQUBAQIDABESBAUhBjFxEwdBUWEigaEUCJEysUJSIxXB0WJy'.
'gjMWCfCi0kMk4fGSslOTo9NUJcJjgzUXc0SEVXUYEQEBAQACAgMAAQUBAAAAAAAAARECEi'.
'ExQVEDBGFxIhMFBv/aAAwDAQACEQMRAD8A1vM+cBrnuZvrGbI27c3d6ouXV2lbGOsUKKFv'.
'OKTLRQ1SBOZUOHhrKCx807+9Xf3lxeLQho3CystNilCZ+AHlBVJAIBAICKG3XVBLTanFak'.
'JQJmQ46CAhIgkESgEBDXl5QEYBAIBAdp/bpvVN3inds3bw+pxRnbBR1VbrPtl/VOnKUEx1'.
'UDPUGY84ITPnAJnzgEz5wCZ84BM+cAmfOATPnAJnzgEz5wCZ84CjuVEBuZ8YDyChKAVCAV'.
'CAVCAVCAVCAVCAVCAVCAVCAVCAVCAVCAVCAVCAVCAVCAVCAVCAVCAVCAVCAVCAVCAVCAVC'.
'AVCAVCAVCAVCLLiWS+0ahF71Ok+ioQ706T6KhDvTpPoqEO9Ok+ioQ706T6KhDvTpPoqEO9'.
'Ok+ioQ706T6KhDvTpPoqEO9Ok+ioQ706T6KhDvTpPoqEO9Ok+ioQ706T6KhDvTpPoqEO9O'.
'k+ioQ706T6VhVIEnwBjLT5194NyObm3FmL+6dWbewu14rBWgV7Ai2V++ePMkf9LyTKCxpq'.
'CkAgEBU2Nld5K8tMdj7Z28v799u2srNlJW6686oIbbQlIJUpSjIAcYD9On2a/aHtD7du3b'.
'Oe3XYWN33MzdkLjd2fuUtrFi2tFSrG2cVMIabBIcIP7xU1K9oSEk1+bzuUrEXW/t73G20N'.
'tbfOav3cMhBSlIs1XS+gECeskKTIDw14CCvTtRs9HcHuf292M66phjd24sZiLl9P5kNXl0'.
'2y4saH8qVkwH1S7zf5aee3JvTvHm+1Ldptvbm27PF3OwdpdNxSco8uxQq+tm3lr/AHSkrQ'.
'aSqoKWsJJTqoE18eL2yucdd3FjesOWt1auKauLZ1JQ4haCUqStJkQQRIg8ILKpoBAIDIdq'.
'Zi7wO4cXk7O7Fm6w8kOPLnRQoyUHANaSOPp6wH1Dwl6b/G2twr2rcbSpSAQqRIExUCQZQZ'.
'XWZ84CM9JfOAhM+cBEGXrAQmfOATPnAJnzgEz5wCZ84Car0gLfdEyb5mApwrTxgI1c4BVz'.
'gFXOAVc4BVzgFXOAVc4BVzgFXOAVc4BVzgFXOAVc4BVzgFXOAVc4BVzgFXOAVc4BVzgFXO'.
'AVc4BVzgFXOAVc4BVzgFXOAVc4BVzgFXOAVc4BVzgFXOAVc4BVzgFXOAVc4BVzgFXOAVc4'.
'BVzgFXOAVc4BVzgFXOAVc4CquXFItbhSRUtLailI8SBoID5ndxcS7gc2xhrhVVxZWVubpR'.
'M/376eu9r4+9wyPlBYwGY84KTHnATtNuPutssoU668oIbbQCpSlK0AAGpJMB9U+1H+X9nr'.
'DZvcvcHdTEgZO1t9r2uzrNJWEou8ncY+9vqgAmpbTb30qqSU1F2WoSqCa3b9sn2OO7K+7v'.
'f+Xzdg49sPtLdIu9iOXAqTdv5JJex5CiJOG0ZUSsjUOhBEDWTd2u2P3e/dz3bzWzsynJdo'.
'ft6wt8pqzTcfuBfWbTpSh9VshfUun3gnqBLhDbU/BX5yOl9xfYh2M232K35s/Zvb6wuN3X'.
'm2L1jE7ryDSL7LuZJNupVu6Ll0FTalvBJIaoTrokcILrhvtX9kbfbnvp9sW+LbLKu9r3+2'.
'TvPeS7txtIx+RxNqw69JZCB0FXF4xRPUCuoyE4LX25ZzTl+0hG37ZOQaKR/7s6qizkadW1'.
'gFT2hJHTBQZSK0mDL4A/5mX2y3fb7eVv3pwiA/tzuJdLTuQMsoZbtM4oKcWUoR+VFyhJWm'.
'ZUqsLqUSoQWPlRpBVXY2F3k7y0x+Pt3L2/v3kW9laMpK3HXXVBCEISmZKlEgACLiWvr52j'.
'/y6dlbd2YruF9ym61Ydpi2F5f4S3u27KysGiNBd3iplaxMf3akpCtApehi4ax7I/bf9kve'.
'C5d212I7wnbu/GwpGKx2QcuXLO7dBB6aUX7TTrhPgWHFED3UqlKLkTXQmY+1Q9le2OIvLX'.
'dlzuhGNS0xmry8Q23Qp4pQ30Qjg31DSkKKlCYFR8JYmtFzAJAIJSSCIyqM9ZwCrlACZwEK'.
'uUBGrlAQnOAAyMAJ84CNRgLbeGQRr4mApArTwgI1coBVygFXKAVcoBVygFXKAVcoBVygFX'.
'KAVcoBVygFXKAVcoBVygFXKAVcoBVygFXKAVcoBVygFXKAVcoBVygFXKAVcoBVygFXKAVc'.
'oBVygFXKAVcoBVygFXKAVcoBVygFXKAVcoBVygFXKAVcoBVygFXKAVcoBVygFXKAVcoC7T'.
'GusB89O/iVDuPmSlJl07WZ8v3CILGloKgTKA+r/wDlo/bBab73Oe+G97JL22dn3nS2XYPp'.
'HTu8q17lXSgqdSLWYp0kXSDObZBD9Bz1lYX9q3avtouGG3WX0oOo6lu6l5tX9laAqDKuCU'.
'JMwACeJA1MBYd07nwWy9vZrdm475rGYLb9m7fZXIO6JaYYSVrOgmdBoBqToNYD48/cp33+'.
'9DuE1ZW3aTZ172/2NnA79G5YOtnPFoSoXkblRCbPqJUFJQ0oKTqlbhOgK3l9jf277yxe3L'.
'ffvfHcF5vvO3PRXszF5XJP5exxFohKS27aIfW4ylxVIIcbGgACTLVQ19M0hI9qRwgjQX3S'.
'7Ase5f2/d1tqXlui4cf29eXuKC/1L+wbN1ZrBGok80mfpMeMFj8g9yjpPLR4Awar6S/5an'.
'Z203v3Py3cLM2qbnG9vWmhiW3EzScneFQQ6J6HotIUZS0UpKuIEajNZ3/mfd5Mnf71wXZj'.
'FXq2cBtmzZymfs2iQH8leAqYDo/W6TFKk+rivGULSRzhsf7Ie9G6Nr2e8bK6xuCvXkIvcV'.
'irt99q7kAFtqUpDSktrOhAJmPGnwZV2Ps79vmT3Hn/ALf8Ht77hWwxmsyMhtbLW+XWlpeQ'.
'6btxbJBJV71usNVVJPvlWOM4cuXHj7+XT8/4v6frv+ubk2/2jiPdXbHOdqc5kNs5bOObjt'.
'rR0nD5d9rpPu2a9WOsQtQWtKfaVCUyNddTlysxjlcEKtZwCuAhMQEZ6ekBAKA8YCNWs4AT'.
'5wCuAtl6r8hHmYChC5QCuAVwCuAVwCuAVwCuAVwCuAVwCuAVwCuAVwCuAVwCuAVwCuAVwC'.
'uAVwCuAVwCuAVwCuAVwCuAVwCuAVwCuAVwCuAVwCuAVwCuAVwCuAVwCuAVwCuAVwF7qEBp'.
'fJ7Lx24M9vz+KMB22ybVkyk8FJLTBk4ky0IKtD6QWPn/AHTYbfcQk1BCimfqNIKp+HDjAf'.
'SXs79rH3ed8tpbJymOzatibF2xapTsQX167j0NIUorXc2dpaJKwt5aitbywlS5zqIlBdjt'.
'jtGv7wvt07t9r9jd3N+tdyO3XcLJ3GGQ6489kbm0uE2jr7C/q7llDqZlv8pcWmlKtEmRgm'.
'Pry2uptCyNVAaQZWvO4HBbnxj+F3JhrLP4e6U2q5xeQYbubZxTS0uNlbTqVIUULSFJmNCA'.
'YDxd21t17HXGKdw1k5jbphdtc2K2GyytlxJQttSJUlKkkgjxGkBZO3GBx+0tlbd2pigoYz'.
'adi1grGsGsNYsfRoCpgTMmuMteI4wGcVeMBTXzDd5Z3Vs8kOM3DS2nUKGhSsUkHmDAfi+3'.
'9grjbO8ty7cumyzc4LJ3eOuGVaFK7V5bSgfimDpX3H/yuMbb23ZTN36Gkh/JbnvFvOy1UG'.
're1bSCfSUdJPDnyrgj7o7MZ7778tjMs11bW93Zt2zfYPBVuq3sGwnX9pESxZ6fczZu1Xbj'.
'G2KnEG1tumCJiSlchG4xWi+6Fh3JvN17Q2dkL3CNtXOffv8AYqrZl9JZTa2d6ptV48pRBK'.
'UfqBogqMyopSUq+H/M4ftz5yf1l4v6h/5z9P8An/j/AA+f8jjx550nH9J7v+UkvXxnnb8+'.
'Ggu8G+LXcV9ZYO+zuNz+79qoex267zEuF21S+l5a22kuFlkKW2hUnKUyC6kzqSY+p+XH9J'.
'x/yzX4P/qX+Ny/a3+POU4fE5e2lpy4xp81CoQCoQCoQEaoCFQgFQgI1TgIVCAtd8f7sj1g'.
'LfWYBWYBWYBWYBWYBWYBWYBWYBWYBWYBWYBWYBWYBWYBWYBWYBWYBWYBWYBWYBWYBWYBWY'.
'BWYBWYBWYBWYBWYBWYBWYBWYBWYBWYBWYBWYBWYBWYBWYBWYBWYBWYBWYBWYBWYBWYBWYB'.
'WYDIKhAWLcN03jMJmcn0wpdpZPvmelXSbUoAkcoLHy1fq6rlYINRBB56/OCrjgLuwsM5hr'.
'3LWQyOLtL1h7I2CipIfYQ4C42SkhQqSCNID9I2/vvc7Z9tOy2J3Vt1ab93IWDFttjB25S1'.
'cXVypoK6aCUkNsspkpx2kjVKUe5QkSuM/t53T90H3J/cN203jvTEPWnbjZ17c5xF21jm7e'.
'xtUrtHm2WkXbiS++tZdCUh15agCVcII+8LIk02FHUJEB6wDz9YCxWjhtcrf2DrhKbiV5Yg'.
'8AhQSh1CdB+VYqOp/OPQQF9gBlqDqDxHnAflu/zC+1tz22+5bez6GCjEb6WndGGd1IUm/K'.
'jdAkgAFN0l3TwTT5wajvz/ACs9w2tz2m3PgeqDe4jcr63GZ6hq5trdTajzUlYHKOs9MV1N'.
'e/aT24zPfbM999yMKz+evVWLuIxVwkfR2L9jbNMJuAjXqOnpBQK9E8UpqAVFTXUK7mzx7J'.
'LryGWmxrMylygPnl9y33L5DBd3OyHa3ZORZtrjcG4rK43e+ENruBjlXLbSGkqXPpJfBcqI'.
'91IlMBRnj9PN1vjb1s+L7/q+aXaXIDN5beGZYdKra6zt+q3nwUy66HkD0pLiiP6xjCTw3+'.
'TKAhUIBUIBUIBUIBUIBUIBUIBUIC05BUumfCAtdcArgFcArgFcArgFcArgFcArgFcArgFc'.
'ArgFcArgFcArgFcArgFcArgFcArgFcArgFcArgFcArgFcArgFcArgFcArgFcArgFcArgFc'.
'ArgFcArgFcArgFcBk1XpAUV/aM39pcWdwmq3ukFD7f7SDxSfQjQ+kB86O5mBucNuJy6uEB'.
'lGf6mTt7aRSppD7zlKFA8FAAT5y8INRruA7b+2PsNm/uVx17i7nIusYrt4VN2K1pPQ6uRB'.
'6DC1zKldR5KQlCdZT8JSD9Hdq1tztDtXD2drY2+Mwtgz0ulbt0pqSgTVpPUy1JPMwZaD3N'.
'9w+7947fVdfb3a7f3XuNV6loW2TvgLJFqisOvJXb1dVQWkJKQpMpz1lI7G5u1W6e7uSxbT'.
'fdbZeO2/m0qShb2GvnLy2d/aWQ6wypv+rNXOMDeIMwD4mAtmVsnbppp61IRfWS+taKJKUq'.
'UAQW18fatJKToZfmHuSID2x9+3f2zb7YUgKmFtOSC0LSaVoWBORSoEGAryZwHzW/zNOx/w'.
'D+SOy6N/4iy625O1S3Mg7QBW5iHgkX6T4kNUIe9EoX+1BqPlZ/l9942u2HeYbYzFyLTBdx'.
'W2sd1FqAQjJNKKrNSiSAK61tcPzLTGuPtL6ffrf+5dwYDYW49y7P29/i7cOMxzt1idvh7o'.
'G7cQmqhKwletMyEymo+0SJnHRmPzcb4+7/AO4ncW7spnr3fmQwly8VMJwNmA1YWjaVH923'.
'auBaQUnQrUCsy9yjHKtxhuws/uG6z26+8u5snc5O52vY3C0Ze9UXl3GbyLDlpjW0qWdVtq'.
'UXwBwQyrSQEQbZ+33H5O0xD10+lCsbklm5tHUq1C6lNLQocdOmCD6mBXSpM4MogygIQEZ+'.
'esBCAcoCIPxgIQEQZQFnySpBHOAtNcArgFcArgFcArgFcArgFcArgFcArgFcArgFcArgFc'.
'ArgFcArgFcArgFcArgFcArgFcArgFcArgFcArgFcArgFcArgFcArgFcArgFcArgFcArgFc'.
'ArgFcBlAOpgIzHnAcifcfhFKuLDcKSqhAax5QRpObz0/nBY5ZpVIqANINJVLQHUy+UFfZv'.
'8AypLy3/hndGxcUAr+O4B51J0FJRdFr8XGx8ZQSvs/vjaGM37tXLbYy7VdnlLZbDkp6dRB'.
'QeBBkUkgyPCCOQPs++05vsHjtxrzl67lspfZ27usQVrX0rWyH7piluYSXHECpxRHGQHCoh'.
'3A9k8ZaGm4vGGCnwWtKf0mA9mchZ3ABt7ht4HxQsK/QYCrBHnAY5dD+EX/APEEAixyC0py'.
'YHBt7RDbw8ZK0Qvj+qdAFmAyIKCgP9NICjyVpZ5GwvbC/YburC9YcYvbZ5IU2404kpWhaT'.
'MEKSZEQH4/O9u0LHt93U3VhduvvnBWOTuTtq9WChw2zT620CZ1KmltqbJ0mpJMVqPp79r3'.
'+YnhrbC2Oze+V07j8hYNpYtN7JbW6xeIApSLxLYUtDstCsJKVcTSfzVmr932V/l474fud+'.
'Z/ctiM9czfvf8AClw59VfOK1/e2zCVorWeK1pQf2lResMfLruTvvG7+yeK2X2z2uNn9vcR'.
'crG19sBzqXFxcPSS5fX76lEu3DiUhMyohCQEJMgScNOxtp462xmGs2LazTYooCzbJSEhCl'.
'+5QkNJzJnBllMx5wEAdTAJjwgIzHnAJjzgEx5wCY84BMecBAHzgLLklCSOZgLRWIBWIBWI'.
'BWIBWIBWIBWIBWIBWIBWIBWIBWIBWIBWIBWIBWIBWIBWIBWIBWIBWIBWIBWIBWIBWIBWIB'.
'WIBWIBWIBWIBWIBWIBWIBWIBWIBWIBWIBWIBWIBWIBWIBWIBWIBWIBWIBWIBWIDK5mATPn'.
'wgMe3Ft7HbitWbXJMJuWbd0vpZUJgq6a2x8nDAcsbH7T5LO2ucwNiGn7/J5Q4a3LuiWrpp'.
'8MMuzAJpPVWFSBIBGkWLK3z9re8sh9qX3D7k7c9yVjE2eeLGHyN4kzt2b5h9F1i74101Mr'.
'JlM0+12o0yMLMH6Mdp7psNyY5FzbOIFw0ele2gVUpl0AEpPAyM6kmWqSD4wxFi33uq7wLd'.
'uzjGTcXjy5IbGs5g6aQwa/xWxcrua5OX3HcrAeJULeohISdZDxMvSQ9Yuo2lhNubbxLwTZ'.
'oaXdI8VEKUCPLjKJis1BIA1iDyeabuGnWH0JdZeQpt1pQBSpKhJQIPEEQFkxd2q0cexF69'.
'U/Ziu3ecPuft1TpWZnVQlSvXUirSoCA5o+5PvtYbL7f7ocwDqcnesOHDMtMkn63L3RDFvi'.
'2FJ/MpTiv35RMoQladFzo1E18tPul+zjufuLC9v939vcC9vS4wG3WMPvG2sQg3jt2hbj7l'.
'6lgSLvXdecK+nMg6yM5iVqV8s89t7P7WyVzhtzYW9wGXs1U3eMyNu5a3DSiAQFtOpSpOh8'.
'REVZpz4wGcdtr20sN7YB+8SS0p8tIUPBbqShBP8AaUID6OW7gWy2pP5SBKDL3mfOATMBCq'.
'fjARmfOAFRA/lgEzxnAJmATPnAJmAsmTVJKNfEwFlr9YBX6wCv1gFfrAK/WAV+sAr9YBX6'.
'wCv1gFfrAK/WAV+sAr9YBX6wCv1gFfrAK/WAV+sAr9YBX6wCv1gFfrAK/WAV+sAr9YBX6w'.
'Cv1gFfrAK/WAV+sAr9YBX6wCv1gFfrAK/WAV+sAr9YBX6wCv1gFfrAK/WAV+sAr9YBX6wC'.
'v1gFfrAK/WAy+uAhUIAVTgPHYdmztLdyc42oG3czbGWLMpUEdLqCfjNTZV8YsuDOv8yXsy'.
'b/ABW1++eAtQ4m3abw28XGZf3ayTY3SiANApRaKif1mxGr9rK9PsY+6S+yLVvsHc+QcXuX'.
'btqEY64UZryeKZGiZTClv2YBOnucbq9pUKompfD66MZZu7cZybnTvG5BwKMilSSBIgjThF'.
'ZWTI9x8jlrleG2zYlaz+7XeCYbRPxq8fThP1jOKz3aO273FMtPZK/XeXRBUsqnoV8QDM6C'.
'LaYz8LlGVQU6lCSpawkATJPCLIObe9GCtu4t1tnDY/P5bbl7gLpd9kc3hLpyyulWD7LjDu'.
'PL7SkrS3dEgmWv7qtBS4lCwxK5p3Htux3d3v7fbEsLZDO0eyuLTuPI2DY/4c5XIFdtimqO'.
'FTDbT7wlqCpB8YsmI+gm2bJu0xbKFJ/vBUQR4eHyEStObfus+1DaX3LbQFi4bfb+98UoOb'.
'b3gLcOuNCc3Ld8JKFLZcE/bV7VSUOBBi6/OZ3R7Dbk7M92nO1m/XBbPVo+iy9qCpi7t7gE'.
'W1wwVgTSpYpVMaKCknVJgrWuUwGQ2duW1sciChVu+y+xdJ/K41WClxH4fA6QTX0WxLqV2D'.
'CgZzSCD5wRcqvGAVwCqUAqnAQqEBGqAVwEKhARrgLFlF6I5mAslXOAVc4BVzgFXOAVc4BV'.
'zgFXOAVc4BVzgFXOAVc4BVzgFXOAVc4BVzgFXOAVc4BVzgFXOAVc4BVzgFXOAVc4BVzgFX'.
'OAVc4BVzgFXOAVc4BVzgFXOAVc4BVzgFXOAVc4BVzgFXOAVc4BVzgFXOAVc4BVzgFUBCsw'.
'CswCswCswGZVS1JgIzgITPnAZFtXb99urN2ODxxQm5u1mbrhkhtCRUtaj5JAn+jWLJo7i7'.
'Xbt7cd/wDtXuLt2F3mVwDVovAPv5JlNs/e2vTDbd8w2SSltSkq6SlAKBRMicbv0R8Ge42y'.
'd8fbj3dvcSt57GZ/aWQTd4HMtAoFxbhRVb3Tc6gUupGoMx+ZCtQRGMOV19svtP8AuMwfdn'.
'a1raOutWmbYQG8lhqtba5AJcbQFEnpLkXGjP8ALUj/AHRjSWY+gu18JZWzKLxLLaCoTZSl'.
'IASD4gDzgMifzmPt1FpTwJToaRVKXqIzhq33O6LRpB+nBeVLiRSB/LDDWus9vFwu/RNrN3'.
'kHUzt8a0qWhnJbhE6EAjVR+FSvabILPZMoxVld3t/cpcuHQq5yV8sUIKgkTIEzShKQABPQ'.
'DUlU1GjS32zW43izuTuU60Q73Nz95mWXVN0LGMYULDGJOp0+jtm1gjSaz5zM0x3W0gIbSl'.
'IkEjhGar0mBqdPKA+aX+YN2Ke7w4PBbl2jZJv94dr6r/MMNe11/EPLQpxkKTqXEdMuoT+y'.
'Fy9ykzLr5l757a2+8MNbWF0sWWXx6h9LflFZTwrSoApmFeXgZGCM7wmOOKxtpYF5T/0rSG'.
'usv8yqABUZaTMoC7TPnARn5mUBAKJnAJnzgEz5wCZ84BM+cAmfOATPnAWHKqkEczrAWKrl'.
'AKuUAq5QCrlAKuUAq5QCrlAKuUAq5QCrlAKuUAq5QCrlAKuUAq5QCrlAKuUAq5QCrlAKuU'.
'Aq5QCrlAKuUAq5QCrlAKuUAq5QCrlAKuUAq5QCrlAKuUAq5QCrlAKuUAq5QCrlAKuUAq5Q'.
'CrlAKuUAq5QCrlAKuUAq5QCrlAKuUBmlYgFYgI1ekBdMPmbrC37N9ZrLbrYIC08RUJTB8x'.
'xHrFlwa4RvLcvbTvbje6G3UKx2MvrxnHblW4/1GMhb3CEANG3AAZQHEpSkz9q/dTLjbyH0'.
'K7q9tO1/3j9v7V+2v2sVvbEMFWFzdIN1ZuK1Vb3TcwVsqVxkf6SDxnrIPjVk9q96vtV7is'.
'3dzjbvB5PHPAWuRaStzHZFlKgodN4AIcQqQMj7h+sEqEoxpX2i7KfeRgt3bMxl5udN3tfI'.
'PIDd1buNOuNdQEhRbKUqXSZVAlMpHUzho2Tn/ua7Pbdx72WzO8G7WwYp61wLe6cpqIA9qG'.
'VEzJlpF1MV+6N+Zm4xDF3t22Npjr9P7vNuUOOFKkpUkste9KZhUwXNRL8h4xqJrVm18vd2'.
'OftbgPurVeXSBeuOLUtbtZCVFxSiSoy8SYWDJfua3Nk7ft9ZbE288Wt092sixtPFupmVMs'.
'XgJv7kyIISzaJcVUOBlEV1H2g2jY7T2risXj2Bb2WNs2LHHs/sMW7YbQB8Exmq29UOEwIg'.
'1Z3C7oY7Z4t8RYdPJ7uyqT/CsPWEhCOCrq6V/umG+KlmXkNYCu2ThXbXErdybiru8yql3W'.
'RuX0UuXLrwAW442fyCQpQj9RsJSdZgB8rvuC7fjYW/MnaWyP8A2y7V9Xi1AzAt3ZlKOJ1Q'.
'QUnlPxgNDVekBCsQCsQCsQCsQCsQCsQCsQCsQCsQFiyy/a3zgMfrgFcArgFcArgFcArgFc'.
'ArgFcArgFcArgFcArgFcArgFcArgFcArgFcArgFcArgFcArgFcArgFcArgFcArgFcArgFc'.
'ArgFcArgFcArgFcArgFcArgFcArgM2q1lL4wEQRLjAJgQEtRgLNuDFM53C5LFvySi8YUhK'.
'/FCiJoWPVKpEcoCj7abpzLeIsL5q9fscxaVW948w4ULS+wotOyUggiakkwG3sn3K3vmbL+'.
'H5Pcd/d2cqVNLeV7hKUlK4q+JgMHSEJmUAJmZwGP7twLO6MBksI+4WxetyQ7xoWkhaFSmJ'.
'yUkQHZv2y7/wAUO3uC7V9wMxbJ3NjmTZWCiopTdW7Kim2UytaEguJbpSUzq0nqDGpUsdN4'.
'btva2mUayf1oube3V1GGaKZkflKjV4RrUtxq+zSO433H5S9/v8J2axiMJjQdUHNZhKLq/c'.
'QfNq1DLZ/rqEIrqbuF3q2H2axVmznr5T+UdbCcfgLMB27eP5QaJgJBVoCoiZ0TM6Rz0jjX'.
'c/3X9yt3XGSx+2bS22NZ2DJfyDqwh68s7eSiXr65uaLazSoJMgtPV/YQuYgrJ+xmGymbvm'.
'84w03k77JdO8a3PuV14fVAEFu7ZtHQi8vgkiSFuptmk6KaROpSg7psdv3ApdzGWucq8k1d'.
'Eyt7ZBkQUpZalUgz0DqnCPOYgOZO8/aLcPdbF3O4bbIC0t8JaOf4Q2la2rSUvtKKFKfuXy'.
'kul1xKP3TaChCQZLSpZqSHzEv7K4sLp22uG1NLbUUqbUJEEGRBHmICimPOAhVrKXxgBPlA'.
'AowEZ6y8POAT1gExzgBPCWvnAS1ekoCw5ZftR4amAx6v1+cAr9fnAK/X5wCv1+cAr9fnAK'.
'/X5wCv1+cAr9fnAK/X5wCv1+cAr9fnAK/X5wCv1+cAr9fnAK/X5wCv1+cAr9fnAK/X5wCv'.
'1+cAr9fnAK/X5wCv1+cAr9fnAK/X5wCv1+cAr9fnAK/X5wCv1+cAr9fnAK/X5wCv1+cAr9'.
'fnAK/X5wCv1+cAr9fnAK/X5wCv1+cAr9fnAK/X5wCv1+cAr9fnAK/X5wCv1+cAr9fnAK/X'.
'5wCv1+cAr9fnAZ1X6wCv1gFfrAK/WAVTBE+MBrPCXCcTvHc+FVJtF2tvLWSfNFwmh6XnJ1'.
'BJ/rQGzKvWAV+sAr9YC0Z02asRfpft1LvFNzxF20aHWLsH9yQr9lSpIWDpI1cUiLCu7ftx'.
'7iZ9HYe631vnLt5HH4izvr5i8KiXxY2QWCH1q/MsFpUjxlSDM6xplsf7XNtX+N2Pjc3nU0'.
'7l3zeP7o3KsgpV9Zl3jdKQQeHSQpDX9mNT0r5v9+94rV3J7g2jt89f7h3Dua+xm3MxcKeW'.
'i1x9pdLZddQlhDi1lwoDCKEmSUuJCSSJclbM+2/7Ls53CuLLeW7NyZPCYOxviUYr+GuWrt'.
'9T/vWF3pQ62nyWu2SqeqOAVAfZ7a208HtHHNYzDWDdmw0PepM1OOK4qcdcUVLcWoklS1qK'.
'iSSSSYDG+6Hd7YvaHEWeX3xmP4WxkXjbY1htp24uLhxKS4tLLDCFuLoQCtVKTJIJgMA7K9'.
'2X+4K3LGxxtm7tnE4myVZbotMh9Yi8fceumChIQwGpBq3bdJQ8uRcCZSAWoKXu39t21e4z'.
'j2YsHBgNxO+5y7aQFsvqlop5vT3eakmfnOA4K3p9tfc7aKnnRhVZixQTK9xs7hMuMygAOD'.
'4ogNDXFleWi1N3LC2XEqKVIUkggjiDOApKtZT+EBCv1gFfrAK/WAV+sAq9YBX6wGP5dXtR'.
'r4mAxuuAVwCuAVwCuAvmAxasxkG2CJW7X7y6WNJIB4T81cIDZv8AhPb/AP6D/wAV3/bgH+'.
'Etv/8AoD/3z3+3AYzu3b+JxWDv8rbJVaLsEBwgrUpChUAQayT46SMBqyxyTd4gLQQQeBEB'.
'dGkuPuoZZQXHXVBLaEiZJPgIDZ+K2dZM2vUy6OvcLFS2wtSUNDymgiZ8zwgNT7k3ZtRN6c'.
'bt1hLvRXJ/IhxxaVEcUthSiCP6Xj4eZCS2uesgKnxgKiuAVwCuAnSFr1SgqA4kAmAKC0ip'.
'SSkDiSCICTqA+IgFcArgFcArgFcArgFcArgFcBMmtc6UlUuMgTKAiQtIJUgpA4kgiAqsfZ'.
'3WTuU2tmjqOr1PgEgcVKPgBAZevYuQDNSLxhTwH92QoJPpVL+SAwO4UuzunrK7bLF0wZON'.
'Hw8RzmOEArBgFcArgFcArgFcArgFcArgFcArgFcArgM7r9ZQESqXjACqXjAQr9fjARq0nP'.
'SA1/vSxct3cbuuyaU5c4QqF82gEqdsXZB4ADiW5BwD0PnAZnj75m+tWbhh1LrbqAttxBml'.
'SVAEEHxmDAV0z5wF7wW3cxuS9bsMPZO3twsiaGkqUUgmVSgAZDzMWDeF39m2T33t5u0zm6'.
'H9rXJcbeS3atouFBSFJWAv3JAKVJIFKyDx4yjSM67h7Dw/avs/tLs3g7i5uGe4O6LDAOOv'.
'rCnVW93dqv8AJKMgBSWGnUyAAAVLmR21tSzCGrC2QKRIUgeACZ6cpRLVj5/7G+xrel13S3'.
'UN95a7xnbe1u3Djl4i/UzcZ61doQlq4cZc6zKD0Oq+j21uOCnRE4yr6j7M2bgNg7fxu19s'.
'49vFYXEtBmwsWZ0NomVH8xJJJJJJJJOpM4DLJg+MBi+8tmbY39t+/wBr7uw1tncJkmlNXd'.
'jdICkkKBFSTopChP2rSQpJ1SQdYDRfbvsxkO12de23thNja9rmbL6jbtwh24VnbPJOuhV2'.
'1cvvqdF4y+VF4KcNSV+wpUA2Uhvf+IZXHkN5CxVesAf/AHGyTMAATPUYKi4DPQUVz4mnhA'.
'VdvnMVeOi1TdNfVETVaOex4Djq0uSx+EBi26+2Owt7Nr/xBt2zv3lCn6qjpvjk8ilfwnAc'.
'y7q+zDa9/wBR7bGeusSs6i2uUpuW+QILagPUlUBzluX7Su6GFKl2FnbZ63SSerZvpCpeqH'.
'umqfomcBzvurbOd2XcsWe58ZcYS4uVKTaIvEFkvFMp9OuQVKYnKAsXTdpCqFUngqRgPMzG'.
'hBEBCrnARKpeMBj2XX7Ua+JgMbq5wCrnAKucAq5wCrnAeqcvk8cy6MdduWpd1XRIEkcJ6e'.
'EBgOS3P3GW8r6Xcl60megSU/zQHSXbdrOo2nZXG4sk9k8nkFLui6+QShtcg2gSA0pSFcyY'.
'DXX3CbjVjtt4zb9s7Td7guwp9AOv01tJap+Im4US5GA1zsS0vbxFvbstqefdkEIHHnAdN4'.
'bCWO2rN2/vnmw+20XLu9cIDbSAJqCSZSAHE+MBzX3L7tXW5XHtvbYWtjDTKLm8E0uXWvwK'.
'UenE+PlAYltvb7opefJnx1gNosJS0hKAeAlAe9fOAVc4BVzgN17NsvpMIw4oUuXqi+ufGR'.
'0T/qgGA133n3c3hLTD4Ztf/E5V1T7yQdQyzICY/pKVp/VgMAxF6q6ZS4on3CcBeKucAq5w'.
'CrnAKucAq5wCrnAKucAq5wCrnAbm2VYm1w6bhYk5fLLuv7A9qf0T+MBg/ebdqMBi8Xim1y'.
'us0+pSkA69FiRVMeqlJlyMB5dpcmzdKyLbqgLl9ppduDxKUlVYH4pgN2QHKvdXcloN/wBv'.
'j7F1Lj1lYtNZIoOiXlLWsIVLxCFJnzgKy0eLrCFGeogLhbMXF4+3bWrann3jS22nifxgJr'.
'+1usY/9PfMOWrpE0hfBQ80kaEcoCmC58CYBVzgMv2ztoZtFxcXDrlvbNEIbUiU1r4njPQD'.
'9MBiu4Xsdic3c4i1uVvm1CEurWROtSQojSXCcB4JcqAInrATVc4BVzgFXOAVc4BVzgM/q9'.
'IBUfGATKtJTJ0EBnG1+22994rCNvbcvci2pVJuUNkMpPkp1UkD4mA3tiftA7o5BkuXRxmL'.
'X/2NxcKUv/wW3U/OAuzn2ab5bH/FZfGKQoyWGQ+5pyLSR84DSWQ+3HK9nNwWyNz5Fxfa3J'.
'uyG5bVkj+C3LqpBm8StR6dstRNDwmlBMl0pkqA6L239v2yrzcV8l43eVwdjZ2iW0uPqaqv'.
'XC64+VKt+kSA0WaROXuVOcbR1Bgdp4DbVsmzwuLt8dbJ16LDaUBSv2lUgTJ8SdTBGTBQGg'.
'EhBOTi7ubcXW7/ALqO021LVSl2mwcDk9y5JKRNJdyJ/h7CVcfcACpPMwafQDbOGftulcXC'.
'KChEkNnjqJTPlEpGdjSXpKMqmq9IBVLwgFZgEx5QECauPhAUlzY2d40pm7tWrlpX5mXUpW'.
'g80qBEBalYFtpRcx19d45aiCW23S4zICQSGXq20D+oEn1gPFeSyWHHUywZuLEH97krZKm0'.
'tA/rPNLUspQPFYUZcVJSkEgKLcGUyl0prCbccZbyd2mu7yTyeo3YsTI6hbmmta1ClCSQPz'.
'KM6Skhrqx+3bteq/dzm5Nt22+tzXRndbo3O23lr5WpNLa7hKkMImdG2ENtjwQIDMcj2n7c'.
'5NgMXmzsS6gJCEqFoylaU+SVpSFD4GA1dm/tU7S5VKvp8RcYZxU5OWdwvif6LxdT+AgOe9'.
'4/ZbkLZp652fn0ZAoBKcfeoDKzIHRLqZoJPqEj1gOKdybazO1cpdYfOWD1hf2aih+2eTJS'.
'T/KD4EaGA13mVSCOZgMarEArEArEArEArEBAqB0MBU43HJyV/aWSECq5dSgnyB/MfgNYDp'.
'NttDLbbTaQhtpIQ2gcAkCQAgOLt+oyXcfu7dYbDsqukYJCcc14Nt9E1XDjh4JCXVKST6Ac'.
'ZCA6k2ttXHbPxfTaSq5ug3VeXaUFS1lImUtoSCZeQGpgOY+6+9d0Z7JDBO4u929hwa7axu'.
'W1NOXIHB1ZIFXoBoPU6wFg27ttDQS68Jq46wHYOzMWjF7esmwihy5BuHRw1c/L/qygMR7p'.
'3GfXa4rGYPD3mSTcOqev3bZpS0oS2AEJUUiXuKifhAayZYyds0lWTx11YE8PqGltf9dIgP'.
'YLB4EfjAVdjbqvr21s2/z3LqWwR4BRkT8BrAdINNIZabZbFLbSQhCfJKRICA4S7k5g7q7n'.
'5ZTa+paYZScXaSOkrcnq/i6V/CAz/EMdK2abSmaiAAB48hAZS3hM06gLRibsoPBXSWJ8pi'.
'An/gGc/wD6i7/7pX80BTXeMyVi19ReWL9qzMJLrqClMzwEyIC3hxJ1BBgLujCZp1tDreLu'.
'VtOpC21pbUQUqEwRp4wFFeWt1j1Ibvrdy0W4CW0upKSQNJicBXJwWbUlK04m6KVAFKg2og'.
'g/CAt13b3Fg6li9YXavKSFpadSUqKSSAZHwmICNoyu8ube1aE3LlxLbfNRA/lgOkbdlFsw'.
'xbtClu3QltseSUiQgOHO6+ZO5+516w0qu0wCUYxgA6VtEqf+PUUpPwEBnWAxOUdtkrxDFw'.
'q5tQlQdtgqtsngZp1HCAn3Bk+9LVjctou7+1sWm1KfvE2rTbiWwJk9YNhSZDxBB9YDUuA2'.
'2+Lo3VypTrzrhceeWSpSlKMypRMySSdTAbht0hppKPKA3ztTAW2Ksmro0vXt42lbj41CUq'.
'EwlHp5+cBqjvjuFOMf2xj0tq61wbh8vFJCaBQmkK4Ek8R4aecBiWMujcMIWeJEBsfFbJyt'.
'4tC75H8OtuKiuRcI8QEDh/alAbabZYxGOLdpbqUzZMqU3btAqWukFUgBqVKP4mA4tbw29H'.
'cnf57P4S+x7V1cqfuH7hpbaEqeXOU1AeKpCAzxhQDaRpMDWA96xAKxAKxAKxAKxAZ/VygJ'.
'kErIA1JIAl6wH0j7D/bFiLXGWO5992acnlLtCX7fDPCdvbJUJgOIP51ylMK9o4SJE4Dt+z'.
'xlhYMt29pbN27LSQltpCQlKQPAAcBAXCoASEgBASkgTJ1EBpffG5m3mv4Q7ZMv4/JP/R36'.
'nUpX+4WhQUKFpWlVcgjUcFR06Md3JTmd/wD+Y89bfWWLuS+3ncN0huzzALjtztC8fIAtrt'.
'Wq12Lij+6cUSWv7smmgRLGnc2MssHnbO3yONuPqLa6bS6w8y4FoUhYBSpKhMEEEEaxhVwO'.
'27FIqWtdKdfcofzQHN3ZXattle6Xejug4xUjMZ8bcwBX7qLHbTf0LpbUSSA5e/UEjScgYD'.
'roKA0EhAKuUAq5QCrlAKuUAq5QCrlAKx8YBVygLXmbpi2x105ctJuGlILf0pKZvKc9iGgF'.
'kAlxSgkA6EmAxDttshnYW27HCN5C7yzrKKrrI39w5dPuuEDTqukrKEABDYP5UBKeAgNh1c'.
'oBVygFXKAVcoDmP7ku0Npv3a9xnMbbJ/xPgmVOsrSBVcsJ1WyrhMj8yfXTxMB8a9xNrt3C'.
'0vQoURAYlX6wCv1gFfrAK/WAV+sAr9YDY/brH9a+usksTRaI6TRI/wB45xI5JHzgNuu9Xp'.
'udEoD1J6Vc6apaVS1lPjAa2tbTaPaHbl1e3lyerdOF7JZJwBV1kLtc1EBI8SSZJGiRMnxM'.
'BnOEvrjJ4jH5G6tvonr9hFwbSZUWkuitCFEymoJIB04wGgO/matGLvaeKkld8C/drOk0NG'.
'ltPrJagf8AowFn2lbnL3WOsk//ALlxKVy8EDVZ+CQTAdVABICUgJSkAJA8AIDR2H714/Jb'.
'/v8AZb2NLVqm9csMZmm3KkuPNTQQ4ggSClpISoE+GnjAbueZauGnGH2kvMupKXGlCaVA8Q'.
'QYDj64yTFruPOYdlytnG39xbNGczS04UgE+YlIwG3O3dl9VlHr9Qm3YNewy/3js0j/AFQq'.
'A2Lu/Pt7X2xnM84RPGWjjrCVcFPEUspP9ZwpEBwbsmxfvbsPLCn7i5cnM6qWtavPzJMB3d'.
'tra9lgLRoFtL2QUkG4uyASCRqlHkB84DXncru2di5exw9pYM5G6uLb6m5Di1JoC1qSgCnx'.
'NJJ+EBtjC3d5fYjG31/bptby8tm37m1QSQ2pxIVRM6+2cjAaB75bx+gv8Ftm2c/eLQq/vU'.
'jwCiWmR8l/KAsW0mnc1d4+zJP/ABTiQs+SeKz8EgmA6sQlKEpQhISlICUpHgBwEByvebgX'.
'vju6rCWiupjrC6+iUoajpWc1XBmNJFQXI8oDqrhoIDincO917h3tmnLZyuyZuVWtmRwLTB'.
'6YUPRRSVfGA3F24sje5T6xYm3j2iv/AOov2p+VR+EBtncuaZ25t/M524l08VaO3AQf11oS'.
'aEf2lST8YD5/7RYevr12/uVF1+6dU8+6ripayVKJ5kwHb3bvHfR4FNwoSXfuFY8+mj2pH4'.
'gn4wGK97t1J2/ti3sELld5+5DCEePRak46ofGhJ5wGldvXRuWErIlP0gNo7b21dZ96sksY'.
'9lQFxcy1P9FHmf0QG6cVeYpxL+Mxdyh4YYotrhpKistmmaUlR46evpAav75YBvKbQTlkoB'.
'u9t3KLltcpksuqDTyR6apWf6sBqTauQbZRaXJbS+GFJc6KtAqkgyPoZQHUe2srd5rFNZK7'.
'tkWpuFr6DaCTNtJpBM/EkGAwruv3GV27xWMubWyayORyl0WWLR5Skp6TaCpxc06+0lI+MB'.
'zll+527N8Lt27xlnH45lfURYWoVSpXgpxSiSojw4D0gMlxq3C0nqHWUBdK/WAV+sAr9YBX'.
'6wCv1gNg1QG1+yO3m909ztqYt9sOWwu/qbpB4Fu2SXikjxCigJPOA+2dmhLFs02gBICYCq'.
'rgFUAqnMfjAa7zW38fuLMtW6EFtjFL6186iQS5cKSek34zoSrqKEhIluR4xrsz1jxyPbrG'.
'5XH32IyCGcniMpbrtcni7xoOMvsOpKVtuJ4EEHyh2o587cYTK/bnvOy7Y3OUuMn2r3a48v'.
'tbfXq1Lexd22lTruCeeJNaOmlTtspWtKFoOqUzy06E7s79a7fdtN271DX1Nxh8a67jLLib'.
'm9WOnaW6RMTLr6kIA4zOmsBRdl9qubM7d7U29cOC5vcZjbdrI3lISbi7oCrl9QHi68VLPq'.
'YDa1U4BXAK4BXARqMBCqcAq8IAVSMArgMcyTwvMzjrATLVkg315JUgFGbVuhaZgkKJWsaE'.
'TR4GUBehe2qD0y8ioeEx/PAe6XUr1SoEQE9R9ICFUB5uPtsoK3FBCR4kygMOzOaTcJVbMm'.
'pj9c8Kv+SNSXU18ffud2g1tneDt3YthGPzSl3LKE6BDk/3iB6TMxzlDlCOV6zGVKzAKzAK'.
'zAKzAKzAdFbOxv8ADcBZIWml+6H1L/8AWc1SDyTIQFt353AwWwMWb/Kuda8fChjMS2R1rl'.
'Y8BoaUifuUdB6mQIceY28z/d3f2Id3C9Va3F0lLdimaWbe1SrqLbbHmUpImdSeJgO/QAAA'.
'BIDQAeEBytvvtdvzdu+8jn+ha/wsdO3xaFXIqTbtJkPbLSpVSpesBtDt/snJbeul3WUQ2l'.
'TbPTtg2sL9yiKlaDSQEvjAZhvXPp2xtLP50qCF46zcXaz4F9QoZB5uKSIDQ3aLs7msNkrb'.
'cu7EJtri2JetMdWHXVOqBkt1SSUiU5ymTPjLxDfW7X90N4i4b2hYM3eZuElFu/cupbZt56'.
'dRQOqiPASlPj5EOZcB2Z35ZPuXOQTbOPvLLjzn1QUpSlGaiTLUkmcB0ts3CPYPEC3uqfrH'.
'nVOXNCqgJe1IB9AIDEO8W2d0bv27Z4HbbTKkvXqH8ot50NAtMglCNeM1kK/swGA7K7W7i2'.
'3e469yjNqi3sn237pYfSQlDagpSuA0AE4DpmA5w3P2izuf7nJ3NdXNtd7eu37ZTzRWUutM'.
'sIQlTVBEjOkyIPjMwHR8Byfu3tTv3c++szuN23tBY3L4Rj0m5TULdlIbammWhKU1EeZMBt'.
'nYGzL7b946/k0spdbYotm0OBZ9x9ypDhICXxgNj5ZWRTi8irEtoeyibZ3+GtOEJQX6D06i'.
'eAqlOA0V2b7X5zZuRyuX3Khn619kM2i23esT1FVOqJ8D7U6+pgN17jTll4HLt4JKV5l20d'.
'bxvUVQlLy0lKFFR4BJM/hAcpba7I70xagu7t7QEfs3CVfyQHS2ysE9gsW41dBH1lw8pT1C'.
'goAJ9qUzHlI/jAY73e27uTdm1BgNtttKcvLtpeRU86Gk/TszXTrxJcCT8IDVG2+0G7MU2k'.
'XTFqhQ/MUvpMvlAdRWls3ZWtvaNf3Vs0hpvkgAD9EBzt3Z7d723xuuwu8exbHB4q0Sza9S'.
'4CVKdWordXSRpP2p/swFdtjthlLJ+0t8yWre0Mw4WXUqcUUpJpSJeMvwgNl3mw8W/aG2s7'.
'y+xq5HpPMvrIB9UKJBE+MpQFq7d7Gv9nPZ9d9ft3v8SdZFsWqgOmyFkKWlXBRLkpTPDjAX'.
'ruDjsvmNoZnEYO2bucjk2hbNpecS2hKVqFaypQPBM5esBpDb3aveePZQ3eM2qfA0vhX8kB'.
'0xZWrdjZ2tkz/dWrSGkaeCBKfMwHO/drtzvXfO6rG7x7FscHirRLNqHbgJUpxait1dBGk/'.
'an+zAU2M7SbhsmUJctratI1k8n+aAszgTaXD9mVJU5buKacLagpNSTIyI0PCAmrMArMArM'.
'ArMArMBsQnyMBunsFfvY/uRi7q3VJxlt0jT+jqD6GNcco+wGC3RYZJhkF1LdxSOoyoyIOn'.
'Dz+EXlxSVlIdbVKSwZ+sZxU4IM5EH4xBItxCEkqUBLz0gMS2nk7C/wARbZi3ueqxmwclbP'.
'uJ6alNXii8yCkgKFLa0p9wnprFwZP9bbceqg8lQwYB3Q2badwtmZbbwvTjsi4G7vAZlr3O'.
'2GTs1pfsrtA82XkJXLxAIOhMMHK2M32/31yHbLY92wLK72peK3F3YxCV1CzyODuF2lrj1g'.
'Hg5kW1von+ZFvOUlCcHc9s22ww20FCSEgTgKmaT4wEZg+MAmOE9YCOkAlPx+cBCY84ACPO'.
'JojxiihyORs8VZ3N9fPotrW1aU6++4QlKEIFSlFR0AAGsWTRq2zzV3foucgamFZR0vlhdS'.
'VIbpCWm1JUTQQhIrSJCuoy1jc4/bPK4m6q+JVrF6xntVSzkri3kUPqQB4T0/midYdquKN3'.
'Osj9+psgeJ0/GRi9Ys5LffdxLK2bUX761tEJnUtawJS9SZQ6xe0aiz/3BdtcX1Dk98Y1Ti'.
'J1W7dyl53/ALtsrV+Ai9WO7U2c+52zfQtnYu0Mvuh9YPQvHWTj7Lh4vXISo8fBBjXWud5V'.
'x/3p3PuncmLsbvdv0rORcu1rbx9mk9K3aoISkOKmpajP3GcvIeeOcx0/PlrmYr1McnVCuA'.
'VwCuAVwEepLWQMvAiAqsz3L3402UY/ItMmRCVC2ZVT5SCkEaQGkbnGZvcOUcyedvn8levE'.
'BdzcKK1UjgkT0CR4AaCA2NgbJzCKYu7Nw293bELZeRoUqEBeMt3P7ksrIssoyhI4TtWFfp'.
'RAWRvux3W1C8syfUWduJf+HAdUdv05lW1cbe5+8dvcrlEm9unXP1Q7q2lKQAEgIp0AAnOA'.
'0x9ym4CzicDtVhf73LXJvb1I4hm39qAfRS1z/swGDYzud3GtsZbMNZdLwt2kttuO27K10p'.
'EhUoomoy8Tr5wFOrux3XqNOWZCfL6O3/2ICZzun3PuGFsOZcNhwSU4zbMtrl6KSiaeY1gJ'.
'MZv/ALh4iw+jscsSylSloFwy2+oFZKle5xKlakz1MBE91+7CVH/3dkjy+jt//LgKHK767g'.
'bht/osrlVqtD/e27LaGEr/AK/TSkqHoTKAyHFdzu4OAsG7Rly1yjLKQhj+INLcWhI4Cttb'.
'ajLwqJgMRyO++4+by1lk7vMOWzmMd6uPtrVAaYaWQUk9PWuYJHvq0JHAwGSP91O6iUjp5Z'.
'kHx/4Nj/YgPBrux3VH95lWVf8A8Ngf/BAUuN3V3AezqMqzln3Mxd02wWpKVJWlRFLfTKaA'.
'J8ABx1gOt8zn0bM2k/m9x3X17uMt0qu3WUBBffUQlKG0CQFS1ADyGp8YC47av8jlcBicnl'.
'bVuxv8jbIuXrNoqIaDvvQglWpKUkBXrOA0n3p7rZbZeQw+D2w5b/xS4bVdZNTzYd6bRNLS'.
'ZTkCohRPjIDzgNU//mDudf25Qb9m2Dgkpy3tm0qkfJSgqXMQEMPvzuDhbI2mPypDJWpxKX'.
'2m3yFrJUshTiSr3EzOvGAnV3X7sBf/AN3Zl5fR2/8A5cBR5Pf/AHEzlv8AR5HLr+mV/eM2'.
'7aGAv+sWkpJHoTKAu6u5/dBplCGsu2qhISFrtWFKMvMlGp9YCkR3X7rhXvyzKh4/8Hb/AO'.
'xAW9/eG+8lkrXLXeZfN9ZkmzWgJbQ1VooJbSAnUaHTUcZwF8ue6XdJAAayzJkBqbS3mf8A'.
'UgKQd2e63jlGSfP6Nj/YgPM92O7M5/xdkDy+it//AC4CmyHcDuPmmU2uQzLiWAQVN2zaLe'.
'ogz9xaSkkek5QF3X3R7oNtJS1l2zSJVrtGCTzNEBSI7r91wr35ZlQ8f+Dt/wDYgF33O7k3'.
'9uu0eyxZbdFLi7dlplZB0kFoSFD4EQFvwqLsEF4k84DMAvQQCuAVwCuAVwGx6ucBuXsSGl'.
'7+tVOupaS3avqFapTMgAB5mZBjfHyld/DL49iU8lbtkDVSnAAJc47WOPv0rMdvXGXd63jr'.
'LdVu7eqBKLNi5Sp0hPEhAVOQ8dImRZfhn7OdyTKQPqluEfrKUTEyNbUlzlru8Q4zcPLdad'.
'SUuNqM0qB0IKeEjE6ptW9pYZQlpodNtACUISAAANAABwAjXhdr0D7nitUMibUHLx1tta+o'.
'UpAJJOkgIlzFlrg3sfv13Ld+e6+VsMcjHbe3peKutuZRsmd+cGUY29fK5kEKcUhSeH5jxN'.
'RjMxuvoCnMZAJFNy4J/wBI/wA8XIxbY907hyaJSuFGXidf0zh1Ta9xujJgj9/P4D+aHVdr'.
'1TuvIiU3E6eaR/JDqs5Jxu6/lqUf9H/lh1Xsn/xffy4NnmD/ADxMidnp/i+//Ya/A/zwyH'.
'Z5u7xyQQQ02wXP1agqXyVEnEvJil7vLf8AMiybw4n+XqpuD+hUa6s7Wsd6HupvHHXGIvLv'.
'AsY+6W19S0hq5UHGm3EuKZcSpZCkOhNDif1kEiesXEu1Roa7ypQG2czthkCX5sbeun8fr0'.
'QwkqdNn3iXPq7u2+1MSNGEuCBx4TyU4YLZfbW7s5BNB7oN48E6mxwzKDLyBffuJQway3d2'.
'f3SbBV/lu7O6b9VQS8wxct2bRSr/AOWygCN8ZKxylamR2i2oohWRTeZh4GfWvbt90nmKwk'.
'/hHTo59mW43Z+2cRL+HYi0tFD9dtpCVHmoCZ+MXqeWRobZQJBIHpFuYuucO+IBt7JwT9r5'.
'T8Ck/wA0eb9nb8XMlccHZCuAVwCuAVwCuA81IQv8wnAQS22n8qZfhAetcB5KQ2v8yZwE7D'.
'Fl12fqUqTbdRPXKACoImKpDzlAbmvO9WzMWhLa7fIhDYCUIbYQQANAB+8EByrvrcn/AOQN'.
'93Gbtm3kYtppm1xbT4AWlpsTVUASBNxSlfGAzLH27bduhJTwEBWdBn9iAdBn9iAnDbQEqY'.
'CToM/sQEQyyNaICYttkSpgPMW7AM6ICctNHiiAl6DP7EBcMU8xjsnjr5Tc0Wd0y+sAakNr'.
'CjL8IDo3c23sXv7AM2Dl3Vj37i2u0PsyWlxDLgUpPEaKSCn0nPwlAS7y3zt/YuMcvctcoS'.
'90ybDFtkde4UB7UoR4CehUdBAcDqvcnvPcuQ3FlfddZJ/qLSPyoSAEobTP9VCQEifgIDaV'.
'pZMNMoFGsh4QFcGmgJBEBKWWT+pAAyyNaICYtNESKICToM/sQEwZaSZhEBFTTSuKYCToM/'.
'sQDoM/sQEQyyP1ICYtNESKICToM/sQDoM/sQHskITwEoCauAVwCuAVwCuA2PV6QEjjP1jb'.
'lokFZuAWwiU9VaCXqIs9pZq4XP2+7jxL1orN56wKbn3u2Vk445cIQOFQW2hKZ8zyMeicde'.
'blPLvTsR24w2ybJLrbSUZW9ZS440fc6hk/lLhOs1y8fKQ4GFmNcJ8umazylwERtNMkekFw'.
'mTAwqV5wMao747ud2P2q3ruK3WU5C0xrrWKCeJvbn9xagepecSIzb4WRybgcQ9213N9t+3'.
'rQCp2xzePyi0D+9P0jFy6vzmp5qon1jPFqvoKw4VNNqnxTzjTNmvQrVFjOYAkj0irITI0B'.
'0gYlKjwPCCIhxXARME9R8xExeqFaop1S1EazimAWTBJESowWxCtUEK1QGru4meaatU4pC5'.
'vOlLj3okcBzPGOvDj8ufK/DSBe1MbcuqHWg0j1oJjn3vI2LnFrUD+8tnUugeaZFKv+sDHn'.
'/aO35eHK5XqdfnHnd0K/X5wCv1+cAr9fnAK/X5wCv1+cAr9fnAK/X5wCv1+cAr9fnAK/X5'.
'wFpu8c1dH3gHnAeNtiLe3UFJSAfOAvaVUgAHh6wEa/X5wCv1+cAr9fnAK/X5wCv1+cAr9f'.
'nAK/X5wCv1+cAr9fnAQK5gifH1gLHffxVpKxjcpd2AWZqTbPuNAnzIQRAYSrbt3d3Cn7x5'.
'y5eWZredWVrPNSiSYDM8VimrJCQEgEQGQBctP5YBX6/OAV+vzgFfr84BX6/OAV+vzgFfr8'.
'4BX6/OAV+vzgFfr84BX6/OAV+vzgFfr84BX6/OAV+vzgFcArEArEArEArEBserlAVNneP2'.
'VyzdWywi4YWFsLlMBY/KqXmDqIsuDbW17bOWeQsGn7r+Kbqzt6hhl68BcbQ5NS1KLaTKhh'.
'sKWUj8xGvGPRx5PPym19Cdo4FvA4xpkuuXV0/wDvb2+eIU9cOke5xwiQmeAA0SJBICQBC3'.
'WpMZcXNNCZ+URTqmXEmC6nDhPidPOI0l6qp6cPGKzrmT7kcqLpPbPY7YQ+9urddldXVqrW'.
'qyw08i8qXCQWy2nXzjN+mo88ri2cj3W7XJU0FrweDzl7Pj0luuY+3bn5VJW5LkYYtdOoVQ'.
'hKQCKRIyiom6nqRFZqKV6TmZeMRYj1JmQJA84K8y4qZ1is1AOcZk6QRP1FeYgunVM5a8xE'.
'NRLkx48oKl6kuE4qekwcnxnp5QPaCnJT1M/KBWPZ3cdlhLNx550KfpPQtwZqWrw08vMxZN'.
'YtxzXksm/krp+6fcqceWVLV6ny9I7yZHLd8rf1T6wGE5fuLtjBZYYXJ5JNnfKCVBDqFoRJ'.
'Ymklwpokf60OVkVlttkba8aS9bPJcQsAoUlQIIPl5xNZ8tHd1nf+GOuiqgR5giREcv2dvz'.
'cqKVJSh5EiPK7pa4BXAK4BXAK4BXAK4BXAK4BXAVdnZ3mQcW1ZW6rlxptTziUDUIR+ZXIQ'.
'EHLW7as2Mg4wpNldLUi3uSPatSPzAcoCnQorUlKfcpRAA8yYCqyVld4i9ex2Qa+nvLenrM'.
'1JXTUkLHuQVA6KB0MBR1wCuArrGxvMkt9uya6y7Zhy5fTUlMmmxNavcROXkNYCgDoOk4DI'.
'rba+4ry3au7TEPv2z6amXUAEKHmNYC2Xthe45iyubxnoMZJCl2S6kmtKDJRklRIkfOUBQh'.
'wHgYBXAXU4jKi/ZxZsXf4jcJC2bSXvUkpKgQOQJgLUs0qU2sUrQSlaDxBHEQEAUjgBAVCG'.
'rhbD1w2w4u3tykXD6UKKG6zJNShoJnhOA8OoDAVn0d59Mxe/TrNrculhh4DRTg/UHrAeq8'.
'ZkUKv0Ls3ErxQnkUkf3QnL3fGAo3237Vzo3TDls7SlQadQpCqVCaTJQBkRwgKh6yu2LG0y'.
'TrQTZXynEWr1STWpoyWJAkiU/EQHgGn/AKb6zoOfSV9L6mhXT6kp0V8KpaygPHqAwCuAVw'.
'CuAVwCuAVwCuAVwCuAVwCuAVwCuA2RX6wHvbocuH2WWhUt1aUJA81GQ/TAfQXtBtTJptrP'.
'K57A2GJDDdOJti11MilBTSXbq4UTJa08UIHtmQSeA7xydGhaQABIAcBFW3TqDhOCI1jx1g'.
'JuqILqXqCczpKCVxzkbg78+4vI3KFdXE9sMQ3iGaxNAyeSKbq6U2rhNDCWkK/rRm+2+Pps'.
'PZxczPdPe2Vmo2WCYx237UEST1mW139wtPnUL1pP9iXGcWFroTq8JGUVnUpcHirh4QRMHQ'.
'IGnV9RyguodQecEK/UQEOoDAUl89dotLheOQ05epQo2zbyiltS5aBSkhRA9QDyMBz5YfcJ'.
'bY7dKtn7+wj+y80tYTaruHA9ZXIJklTNyAkEKlpMDy/NpBdXzdHfnb2zM/aYjc9pd4uyyU'.
'ji8+UpcsXhwUC4klSFJJkoKTpoZyM4JrYDu/8AANWrN0m8TcJuEB1gMiupJ4EEe2Xxiyal'.
'5Y1/l+6F0+Ft463TapOheV7l/hwHzjfHh9s3m1pd5J+9dW/cvLedWZla1TP4xuSRi+VL1R'.
'IS4840kgXQQJGR8Yg19vvbW1dyY+e4VotTapJZyQWlpxkHXRatJeh0jncquYWd8XHbTIKs'.
'Nv7iY3XhQokWiwtPS/qOSKdf6JI9I5bnlvFBuDupkt3Utfw5qzSNVGsuH8ZJHyjPP9Nb4x'.
'hZXMzM5+McnVCrnAKucAq5wCrnAKucAq5wCrnAKucAq5wCrnAZ32+V/wC6ZX//AB7w/gEw'.
'HgEpyWz+3Ng44ttvK7gds3XESqCXX+mSJzEwDAVGW2/g8ZYX99i7y+cfwWaRi75N306FnQ'.
'1NhsTEpjjx8hAXTcWFaze/d6vXZu12OCtbW4etrFIVcvLct2g222FBQ11mZGAo0bPsGNx5'.
'KwvL25RhcbiTmXHgEpuehSDQQQUhQM56eHhOA8TtjEXG49q4qxyVwLHctj9cl98IDqUycW'.
'EADSqSJeOsBUbZcwre5M9aWYyFrZMYS9Teovko+pbUmQc9qJAyHAfCAw7dFhjMOztzJYm5'.
'unbDcNop9tq8o6rakKCVAlsBMjPSAyHF5C7T263w81cupVbrxwt1BagUBVwAQnXSY8oC+2'.
'uIxW58V26xGRur62yF9ibtdg7bhvpBTRC1F4rmo6DQJ/GAseztq2mbw+OyF65fVZq7ctLE'.
'2aEqbZDQM3XyQfbUJS05wGFEKTk3sWVzfZuVWqinX3pWUGWvnAdK3ltbJ31is4JJtbWwes'.
'lrH5U3AuW7ZCT6n6n8IDQ93imVf4xyDr7rb2I3Ica2wJUFDjqxVqJ8BMawGTf4RxjW5ty4'.
'l25vbmz27ZIuyxbBtV2+FNtrIQCKdCvy8oCxW7dtcbX3rkcdkL1mwxz1mlqycUkB5DrwSn'.
'rpToVJ4iXjAS7txeF2qiyxn1V4/uIttPZEKCBapS4lRKUfr1AgcdJfhAZTsdr+O4RuyPvR'.
'h9w2N+6D4NOAoWJ+ANEA3vkGrPb26tx2002m8nMMnHrOhpLH1CwJ+JpMxAVdjZY2x3Vn7L'.
'MX2QyryNt/VNXDwbdUGS1N3VUpKQKen4cZwFBi7fAZ3beyMbkLm/ZTlMhkWMUq2Dc6guYU'.
'8VVAAADRI8YCwW2MWcClh3KXJYG8Bg3bRCgGJ9MTfCDP3+HKAuO4NuYHF4rcF3jr2+cu9t'.
'3zVpeC56Ybc6tMqAgTEquJPhwgNds3KXRMHSA96ucAq5wCrnAKucAq5wCrnAKucAq5wCrn'.
'AKucAq5wCrnAbKRU4tKEAqUoyCQNTDNK7R7LdmE2abXc+5rcHIEhywsHBPoAahSgf1/+rz'.
'jrx4sWuvGwltIQgAJSJJEdeMjD0rPpGsgkLhq8Jxi+1TVk8SI3JEQ6ivSOasL7g71sdhbO'.
'3BuvJrCbTC2TtypAMi4pIkhtP9JayEp9TAjnPtgW9gdub/eW8rgW+SyAutxbvvFJkfq7wm'.
'4eEtZlEw0kDwSAPCM11ba7K2WRb2u3lczbGzy24Li4zGRtVmamnL91b6WVE8SwhaWuSY3w'.
'mufJues+kbyMhcPpGeUxUOor0jIdRXpAOor0gBcIEzKUdJIiTr6jUTPhGIr0DhPCXlG8iN'.
'W91u2mC7mbau8Xk2EovWkKXickkDqWz0tFJPGRMqk+I+BEsHz82xnnt04/Ndju4L9OVs3H'.
'WdrZa4NS2Ly2KkhlSzqZEEDzSSn9mOW+SsM2h3X3h2hzD+1M8wcrhLF9TF9grkhSmZKko2'.
'zh4eY/VV4SnOL3xm+XZONyWD3fhWdy7VvRe414TeZJAdt16EocTxEp+Pp4EE9uPLWbFKpw'.
'JnPiniDpF1GGbh7h7V20FJyWUZRcJE/pUGt309iZkT9YzeeJlaU3P32ygtQ5t7CLtrZ8EM'.
'ZO9SSlXqlKdJ+Xu+ETl+tnw3J4amSxuvuG29kH86i8fbUQLJ1wpKfIhAFCZ+Gkc+N0xb9p'.
'YC1c3G9hNyWSi6EkJQVKSUrRqdUqAIKdZwaq77hw+Nw2Qu2sajpsBSUBNRVIpT7hNRJ4mO'.
'fN04MUKtTwjDZVygFXKAVcoBVygFXKAVcoBVygFXKAVcoBVygMo2jnbHBZZdxkgs2F3avW'.
'l040KloS6PzAeMiIDxv90baxY2BhsXe3GUs9tZgZPKZNbBZmk3CXCltokkkJn8vPQKfMb0'.
'w95it22lut7r5jcyMrZVNkA2wpmVGeh04QF6V3BwV1uzfbv117jMRu2ztWrPMsNqL1u9bM'.
'oQFFtKgqU6uBn+M4C24zee2Gd155Djt+Ns5rCLw38SfU9cXFakpqeKXFrWEqNUkg6THDWA'.
'tW4s7tvLZnZtvjcpe2GLwONasX8t0D9QhxhSyl1KAqZJMjodJ6QGT5HfmAXuO7v0vP3SV7'.
'XexFxliwGl3l6tMg6ppJ9oOg14cOAEBgm5s9ZZbB7Ix9mpw3OBs3mMgFpKQFrWlQpPiJCA'.
'vmNzdla7H3Vhnyv67MKszZpCZpIYeC11K8NBAX/Cb7wGOve3lxcrfDe2sfe22TKWiqS30y'.
'RRr7hOAte094beZ2tjcNnshksYrA3z1ybew6o+vYdBV0i4042UGs8SZSHroGC7fv2f8AEl'.
'tkbkLasRkEXLyCVPLS11QtQJUZrIT5nWA2fm+5docfm7FlTy1Xu5mMjarU3wsW3WniDI8a'.
'mRp4zgKBrdezspd72sb6+u8Zjs7mLbL4zIotlOn93q4hbYNQJMwDw1n4SIUg3fi8r3IyW6'.
'jmbvbtpW0vH3DVuXlOpYQ20W3GwrQOISfP1EBNld6YO5xvcm2tGHbRe57yyfw7FGhSw8lb'.
'qlkaJKpFUvMwFDv3O7d3Q7j9w4+9uU525aYZyuKcYpbQW21BbgdBkok0gAeGunCAumyd0s'.
'bbs9x9crSrI4t1qzoE53KQekCfAe46wGL7p3Y3mNjbM2w0XDeYVx5WTSpPsFBUi3pUeP7t'.
'R5QGY3m98G9unL5dC3/or3bLmKYUWiFfUKaSgAiegmOMBasDuvEY6y7eNXKngvbOSvbrKU'.
'tkgNvqBRRr7jLjASjd+JTjlWtb3W/x3/iD+7MvoSimc5/mn+rAeu4t6YfI4vf9pare624s'.
'rbXmLqbIBaaKaqzP2nThAYViVK6aavKAvdXKAVcoBVygFXKAVcoBVygFXKAVcoBVygFXKA'.
'VcoBVygO8OxHbBOQdZ3bm2KrdpdWIt3BoSn/fKB8AR7fx8o68eOM8nbbZQyhLbckhIkANP'.
'0Rth6h4/tRviiPWP7UaEOr4zE/OGCPWP7UBJ1iOKo5K5I72ZJzuDvnbfa20XVgcAtncm/H'.
'ATQrpr/wDb7JctD1HAXVJPggGM8vbXFaMG673k3Yi0s0lXbDY16kMu/wC7zOXtVAhz+nb2'.
'qhNPgtwBWoTFa5enZFolFsyhlv2pQJD4Rvi51UF4/tRrkgHj+1E4iPV/pRrIHV/pQyB1f6'.
'UMgsW4bfL5DGu2+EyycNfK/JeqYTcBP/01KSD+Mc7Ucv5Mfchsi8eyLF7YdxcSk1LsQ2iz'.
'uktpmSUpSEgH0qXyiDNNkfcPt7NvjEblae2huJBCHsRlUlhRX/8ALWsJCp+A0P8ARiy+Rv'.
'8AYydvdJCmX0uJUARI+cb5elfOP7trDbGD3Ni8nhbC7xO7bhQvLq/ZR07W4QCZOpXPV5C0'.
'iZA8ddZRyRyxvreKt63Vhl7xkN5YWTVvlnkiSX3mSpIdHlUikEeBEZ5LFR277k7h7eZQX2'.
'JeLtm/JGSxThmzcNiehHgQCZH+SYKWpW/M7nt9b3bU/jX2NqYu6SFMWbC1uXASRoFuyTKY'.
'/Z+cdLazbI1ridp45q4u8RutChlLpZNneFxcnkkCRbUZAqBJmDrziLLolrM7JX9HlLT+M7'.
'WcVSHaAsNpUeJBnLjwOnkYvr2Km9287Y3FjubY4L7TqqrmxbX7ShWvtmeB8R4Hh5DI2Mxj'.
'8Y+83ue5tja36balxToKFJSBM1JP6wlKcGJrSWWeccaQ46kpW4orUk8RUSZGOXJ6ePpixX'.
'qYy0hXAK4BXAK4BXAK4BXAK4BXAK4CCjUkiAtjliha6iPlATfQt+UAFi3p7RpASCwbBnT8'.
'oCH8PbCpy+UBObNCv1YCcWTY1lAVNAoplpAU5tEEcIDzNi2QdJzgPRm1Q0SUiUBFy0Q7qo'.
'QHl9A3LgICVFihBmBATGyQTMiAfRNiRAgKtLYCKDw8oCnNm2STSJ8oCY2iPKAh9IiR04wE'.
'n0SJnTjAPoW9NOEBVsthrQcID3rgFcArgFcArgFcArgFcArgFcArgFcB9pdt2lvi8PZWds'.
'gNNMNIbaQBwQlMgPwj0T05X2voelP3TnARL3rAOsOE4CHV/pmAj1xIawFo3BnrTA4TK5u/'.
'e6NlibV26vHf2WmUFaz8AkmBHz528ndW7mrvGWZds9492bhzPb0zCCa8Phrk0W7KVHUOqY'.
'SGWk8R71/qxl1d27K2vitm4LHYTE2iLO0sGEMW7TYEkoQJAec/MnUnUx0npz5e2YB8cJxl'.
'EOsNfdxgIl/Wc+EALwOs/hAR+o9fnAQD3jM6wEevLiTASl1KtDqPKAw/c2ytqbvtlWufw1'.
'rkW6SG1OtpK0T4lCxJST6gwHMu6dk9we0qF5btvn7rJYRglX+GL9RuWwkfqNEkLTIeAIJ8'.
'ydC0aT3z35wHcfalzt7eu27nHZ21m5jMlbBLqWbhII1CyhSUqlJQ1/ECM8uWo5EWoFRpEk'.
'+XlGBsjaF5tbDvrdyd4i8+oaCHG1WylIQZz0UZk+R9sb4pW7cVu3aaGUsWWTt22k6NsKWG'.
'6R5JSsiXIR142YxyZC47isq1J1LF2yTNNQDiZjgRxjeoqVC1Ux0V0uNFNJQqapp8jVxiDC'.
'ckxkNuWs9o2LD7CnFu3Vota1KBVL+6TUBLTgPgI5YjDH7/AHJkH215w/SI6aXGsY2SlIqU'.
'UguCZJPt4HhzjHPk6/nxY9m1mlJ9eEcndiJWZmAVmAVmAVmAVmAVmAVmAVmAVmAVmAVmAV'.
'mAVmAVmAVmAVmAVmAVmAVmAVmAVmAVmAVmAVmAVmAVmAVmAVmAVmAVmAVmAVmAVmAVmAVm'.
'AVmAVmAVmAVmAVmAVmAVmAVmAVmAVmAVmAVmA+1ts7K3aA8EiPV8OE96qeqfWMtIF5XHjy'.
'gHWM56wDrHyMBHrH1gOdPuS3ZbYzaWJ22WVX11vXL22LaxiFSXctg9dxo6GSHOmGlq4JC5'.
'w5VrgzfthtBvbmHN1eOJvM3llm7zWRpl1rhSQPaJmlCEgIbT4JA8ZkzicvLaYekeXhFZOs'.
'Zz1gHWPkYB1j5GAdY+RgJOuapSPPwgPXrEacQICUvk+ekBHrH1gIdU+ZlAYlvbMY/Fbbyl'.
'9knOna2zKnVq4kBHvmB8Is8D5bdwczYbyN5lMNt9jD2FotTjuSUgJfunFmUjQOZ1J5xjkl'.
'aUUROQM/MxhIgF+WkCo9QzmNPQQRVW+RvbRVVrdO254/u1qRr8CI1OWCvXubPrHuzF4eTy'.
'/wDah3VlO1MruB24Lpyt19O2fclaysKUfCS5iHdv/Wzl64dfdU+8qt1QCSvxkngPhGOXLW'.
'uPHGKZtfsTzjLTESvUwEK4BXAK4BXAK4BXAK4BXAK4BXAK4BXAK4BXAK4BXAK4BXAK4BXA'.
'K4BXAK4BXAK4BXAK4BXAK4BXAK4BXAK4BXAK4BXAK4BXAK4BXAK4BXAK4BXAK4BXAfaCwu'.
'w9atKB8OM49Hw5K3rAz156wEC+kaTgPB3I2zQPUfQ3L9pQH6YCl/jmNlresj16if54uUQ/'.
'j2MGv1zB01/eJH8sQcu3d7ad0O/DTzLibvAdrbNVsw4CFIXlr+SnylQJB6TKUpPko+kZnl'.
'qePbrBpxDbTbaCJITIAcNI0y9g9P8A54CPV/0nAOr/AKTgHV/0nAOr/pOAh1vD+WAj1f8A'.
'ScBAvDx/TASquEoBKjIDXjAWK/3RjbJKx9Ql54AyabNRn6ngIDQHc7crWXw9zZ5FIdYvv3'.
'LFkCQOIM5ggmUhP8IUca72uGLbGO2VqkNtNqS2hCNAVEzVIDlIRio0lMknzPGMhMwNJmBp'.
'MwNe9sw7dPtsNCa3FAcvWCya2/YWrdlbtMtiVAkT5nxPxjDrx8Kyv0+cBjebX7Ez010gMQ'.
'K9TAQrgFcArgFcArgFcArgFcArgFcArgFcArgFcArgFcArgFcArgFcArgFcArgFcArgFcA'.
'rgFcArgFcArgFcArgFcArgFcArgFcArgFcArgFcArgFcArgFcB9KO3/cV2523YLfaFy6ht'.
'LTqqpEON+0z01n+b4x3l8Mcp8squd83jgIYaQwD4/mPzkPlFZWB/cOUuZh68cIPFINIPwE'.
'oC2KulqJKlEnzJM4B9QeEzLnG5yiME7l7re2jsnP59gj6mzt5WgVqOs6oNtTB4gLUCRGOV'.
'WTWs/t2xebxm3XszlVOJyGbu3ckt1ZPVV1gmS1nQ1KAnDhG75dVWO8MjaEBxf1DQ4pc/N8'.
'FfzwYZ1j92Y69CUqdFu7/wBmsynyPAwGQC7bUAUqEvA+cBObgf8ANAPqJ/8APARLwGsBD6'.
'hPiYC33ecsLMf8RcoQr9mcz+AmYDEL/fQFSLFmZ4B1zw+AgMCz27l29rcX2WyP09owK3lK'.
'VSgAegi4Odr/ALj723LdOI2ZhQjFomEZe8SQFeaxMhIHpImM6jFSvc7y3stuLMG+fYm1Zs'.
'syQykq0KpJSicvCYjPkYNnHCbcvqIpt0qcQo/9pKlOnpMwo1MVamXj5xksK9OEExCo8PnA'.
'wqgYzna1kKVXy06r9jU/IcT8YmunHjjNa/SMtlfpAYznF+0aj4QGIFYmYCFYgFYgFYgFYg'.
'FYgFYgFYgFYgFYgFYgFYgFYgFYgFYgFYgFYgFYgFYgFYgFYgFYgFYgFYgFYgFYgFYgFYgF'.
'YgFYgFYgFYgFYgFYgFYgFYgFYgFYgFYgFYgFYgFYgFYgFYgFYgFYgFYgOn+22dNrfP4l1c'.
'mryTjAJ4OIHuH9pP6BHTjySxvjqEjQz8o6Oa72OIyF+kLbapQfyuL9o+HiYC/M7RfOr90h'.
'PmEgn9MoC6tbSsky61w44fGVIT+gwGm+9GIw12dk7IDIdd3XmWnLtpSiZ2WNBvLjTh7qEI'.
'/tRnlfONcZjdmN21hWLBphNkhFKROmpJ+RjU8HZ4XW1LNwE2zqmVeSvcP54MtfZhC8HeMW'.
't2sIN0CbV06JcKZ1JSTL3Aay4y18DAT2uYvGJfT3a0DjSFafhwgL2zvDKNj3uIeH9Ifypl'.
'AXBvfNxL3WoJHiFEfpnASO74vFCTbLbZP7RJ/lEBZbnceUuphd2pKTpSg0j5QFmcuCqZWs'.
'cdSY1gwzcm/tvbWaKsjfJ68ptWbfveVyQDoPU6RByTvzuVk95XAbANpimVVW9mFTJP7bh8'.
'T5eAjn2R72HdzcVnZHHratXLUpDZoQW1hIkJJIMgJCXCJo2NtrdmN3Daqt6KHkAda1ckT/'.
'AFvUT8YaNK7hy5cC7BsFIbecDvlJKzSIWjD5nziBM+cAmfOA97Zhdy+0wji6qmfl5mCya2'.
'5atJtrdtpAklAAHwEYrrFRUYBXPQmU4DGs5+VMtdeMBiJ4mAQCAQCAQCAQCAQCAQCAQCAQ'.
'CAQCAQCAQCAQCAQCAQCAQCAQCAQCAQCAQCAQCAQCAQCAQCA2vjbw2V/a3bZINu6lcxxkDr'.
'Flwdv7SxbV7btX9z72yAWWzwP9I+Y8o7SudbMSpCE0oFIHACKj06vqYCUvBIJKpAcYDlC1'.
'v3N+/cFmLttXWw/b6wRibUiZSby5PUfUk8JpAKFchGc2tXxHV6VhKQkEinSNMo9X1PKAsu'.
'ewuM3HjX8Vk7cP2z4mNaVIWNUrQoapUk6gjWA4+3df717VZEsX7KtybcWZ2WVPtfSgmQQ6'.
'QCKh6jXz8IDwse+O13mwbsXNm4fzJW1VLkUFUSUXUd5dlEf/AHJYP/6Lv+xF8Clf72bOZ/'.
'u7m4e8whlf/wAVMPAxzI9/cUhC047F3Fw5+qp5SWk/6pWflGbRqvO93t25mppm5GMt1T/d'.
'2uipeRWolX4SidkawfvLi5cW4+6p5xwzWtSiVE+ZJmSYmi447B5PKoW5YW3XQg0qIUkEH1'.
'BIMQW64ZdtnFsvtlt5pVLiFcQRAVGNyVxi71i9tllKmVTUkfrJ/WSfQjSArtxOWzuVuXrV'.
'X7p8pdTPzcSFkfiTAWOr0gFXpAQqEBmO2LOouXiwAB7Gv5TEtdOMZvUIy0V+RlAKx5zgMe'.
'zCvYnnAYvAIBAIBAIBAIBAIBAIBAIBAIBAIBAIBAIBAIBAIBAIBAIBAIBAIBAIBAIBAIBA'.
'IBAIBAIBAbCC/cNOB4wHevbt66O1MMbpHTdFo0FD0pFPxIkTHeOdZ51R6xUOqPWAxjeO5b'.
'ba22M5n7pQDOJsnrlSSZVFtJKUifioyAiXxFjTP264B+w2r/Hcj78vui4czOTeVKanLtVY'.
'P/Qp085xI1Y6LL3xnGmDrfhAOsZ8NIDEN5WFvksWsPtIfbT7XWnEhSShehBB4iA4W3/2pu'.
'ccLvM7dZU/jWh1LqyTMrYE/wAyRxKR4/s8uGeSNDKqSqlUwryjA3KjtHdZDEsZHC5Zu/U6'.
'0lzoLT06qkz9qgVcfWUawaxuMdd4PJJt8xj3EG3cBftXKkFSQdZKHn4ERPQ6AwGM7W7ksU'.
'2jDDLV0pP92pxSLgGXmpU1S+IjUkGFbz7VXeDYeymIf/iOMbFToI/etjzVLRQ9REwYxsuy'.
'vjdLufqTYWKh0nHFGkOLP5UifEgyjIqN44N9l1N2E1zTJxSB+YDgr+SNYNdVSPCMiKnFKM'.
'1TmABM+gkP0QEtRgFR8pwHo0hTziGkCanFBKeZgsmtq2LSLW1aZQJhCQPKfmfjGa6SYqq4'.
'ilcArHlAWHKqmlPMwGOwCAQCAQCAQCAQCAQCAQCAQCAQCAQCAQCAQCAQCAQCAQCAQCAQCA'.
'QCAQCAQCAQCAQCAQCAQG1NtWP8Vz2Kx6h7Lm4QHB/RBmr/AFQYsHflgEW9oy0gBIQkAAaC'.
'O+OdVgePEnXnBEC8YDm37gco7mE7O7bWaz194ZNC8kE8RY2ZDrs5cJmkjkYzyvpqN94JlG'.
'PxtrbNpCEtoSAB5RrEtXgPH+aCAeMtTAeTt2llJWtYCEyn8dBASvKTc27rSvyuJKT8YDV9'.
'o/8AQ5XpPj2KJZeCvJWmvxhg0b3f7KIAud0bRtvZq7ksQ2NBxKnGUjw8Sn8PKM2RGuO1Hc'.
'Y7WyTGJzMlYxa6Wlua9Iq4pM/1T5+Bizlg7Ay+19qb6xiXTa27/URNC6RIT10I1SfUQ8VX'.
'Oe4+xLFs44rF3rti6DNtl4dRBlwpUJKA/GJiKDDZjee0XEYvceNfymMBCEZNgKeKU+aiAS'.
'Rr4gHnAWzuRa/Wsu/w4pbXaO/UKtEaEpoAKgPQz8IgsWBzQz+Kcs7sj+IWSQZmU1plor4+'.
'MTRrXcCWW8gsNBKfakrQkSkdfLnEFjmIBMQCYgMh29bF64VcH8jAknyqP8wiWunGM8K5T8'.
'fSMtFfw9ICJX8ICFfrAWTJKmkc4CxQCAQCAQCAQCAQCAQCAQCAQCAQCAQCAQCAQCAQCAQC'.
'AQCAQCAQCAQCAQCAQCAQCAQCAQCAQG6e3LiEbvxCnFSShTpJPhJpZi8faV2Rjsmq86jiU0'.
'26DSyDxUR+Yx3+HGe12DwOolBpEvhIJJGkStRynt27TvzvfurPg9fG7TZRg8WoiaawoquF'.
'JPmFhQn5GJPa306qDgSAJiQEhGmEeuPSAdceMoDF9x5ItoatkKkpX7xXw4fOCcvTIbS5Dt'.
'u25OdSQZ8xBZ6YRue2LdwLxA9j355ftjT5gQGQ4LIi9smy4r3IFDk+MxL9MBoDuz2VazAu'.
'NwbVYS1ktXL3GokEPnxUgHRKz5cD6Hjmo072x35nNq5xvAX7rv0zjhaSw/MFp1P6hCtQDK'.
'UvA/GE9jte1ymOz9sG3kgO0zoJ9wJ8UmNVVkuscrEOOXIaTeWQbUpwEAkAAmZHpxnAcXb2'.
'yeRx+6/4v0pW7iaUJSZJWgklafQ1En8Iyiw3TrFs9bbnwplbqcle2g0KFK/MkjwB/TGBhN'.
'/cpubu5ebJLbrilIq4yJmJwFHM/DwgEz5wATOnyg3xbHxDH0lm2lQAcUKnOZ1+UZrUXSsn'.
'/kiKV+WvOAVc4BVzgLRkFaJ8oCzwCAQCAQCAQCAQCAQCAQCAQCAQCAQCAQCAQCAQCAQCAQ'.
'CAQCAQCAQCAQCAQCAQCAQCAQCAQG0dlKUNyY/pzKpuJSBrOptQ/li8faV2rjEi3tGm+BCR'.
'Vz8T+MdkV5dPjpEowjuNuxG0dm5/OqWEOWdov6QK0Cn1ChpPxWoTi/CT21j2AwDuD2lZXV'.
'0km/zS15C9Wr8xU97gVT1mEUg+sZi10E5dBptbqj7UCZ+EaE4fqE0yIPCAdYjjEGs8tfG5'.
'vXlhXsCpI/qjQRpx5s0wdyXLFk8ZCn/o6RHTj6Vt+2m8tnGF6VjRXkfAwWsJw945isg5a3'.
'HsQ4qkz0FX6p+MCNgdYLGh0IiVWkO5/a613KheewgFluW0AcbcTJKbgo1CV/0tNFfjpwMX'.
'2xDY26Li/ZcYukrtMtil9K/t+CkLSZVSOoBI+HCCNnZnc6l4C6t3jRcLCWwv9oFQny0gOd'.
'HbzF7jN/jblqpVq8ppaFyCgRoFoI1E/CINX5zGv7Y+rtUuF6wyaJMqPEKQtKtZeI9IyI4y'.
'0x+4MWLVJQxlrJMkKkBWkcJ6TIPyMb+FYdeWdxYvrt7hpTbjZ9yVfz+POMIo6h+HGAumIY'.
'FzeNgiaG5rUOX/ACyjNajYaSAAIjomrA4wCseBgFXpARrI0HCAtV6dBpAW2AQCAQCAQCAQ'.
'CAQCAQCAQCAQCAQCAQCAQCAQCAQCAQCAQCAQCAQCAQCAQCAQCAQCAQCAQCA3z2kxf1eZfy'.
'K0zbskBLc/21/zAfON8UrqcPhCQOQTG4iPXPnFHMfevJK3XuraHbS1JWzcPJyufCdZW7RI'.
'QhRB0qkrTzpMYvtY3vh0C2AZbAptW0tCQ8Tqr+SNz0xfa6ZW4IsH9ZEp/lEE5TYkxd6X7Z'.
'pZVMyAVzGhikXFx89NYCtSky/CMtX01Q84ouK14HjGnKzwzTbN3O2W0VaoWdOYER04sm65'.
'84NLBnbEXzfWZ0uGx4frAeHPyglU+CzZcSbS4UQ83omf6w/nECMnFxMcdIK0D3L2pk8fkE'.
'b92igjJ2onl7NAmLhpPFRSOJkPcPKRGoiMWLO3vTA7t2/NpP0eWYcQLuzJkUmR1HAEE8CP'.
'jBGlNy2N9iMkrcGMNTSzVcIGtJP5pjyJgMX3RnBmRYOoNKEMmbR/VcKiFA/gIwKPAWqrpT'.
'hs7j6bKW5DlvrILHinXhL+WLBlB3DaXP8AwG5MeEut+0vpSZpPgZD3D4RoSObSxl8A7iso'.
'KFCYbXJYB5ggj8IuSie0289hm1uvOturdVTNE5AATHEDjHPnHTiqQoT14RhorAPpAKuUAq'.
'5QCvzM4C33SphPl5QFFAIBAIBAIBAIBAIBAIBAIBAIBAIBAIBAIBAIBAIBAIBAIBAIBAIB'.
'AIBAIBAIBAIBAIBAIBAdd9rLAWGAauVgBy9UXlcjon/VAjrx9Ja2MbrqXXTT+VhM1S/aVw'.
'+UXEel3fM2ls9cvLDbLKFLccUZBKUgkknyEBzH2sUrdW5d2dyb1KijI3C2MRXOabS3klAE'.
'/wBqQn6iJEtx0bh3D9N1F6KdKlqPM6Rqs4uNyQ/bvMzlWkgH4RG4x7B3hbL9quYU2okJPl'.
'/ziLjnjJi8JH1ER0auuyW7h1B4pWoH4GUaYvhe9uXNNw63PRSQqXI/8sSrGbB8SiNI9cfj'.
'AYjmbRbLqb+09sjNyXEK84C84rKpvWQTJLiAA4mfjAXRTqVApImDAaP3r2vsbi4udx7eUM'.
'blGkOOuWqRNl9VJOqZikk+WnpPWDGOY2Nw5Fh25xOaKi2/Uy6tYktoq9vpMCMIxVzG3in7'.
'hlllT3QUQsoBI9COfGApGXbizdS42pTTzRmlQ0IP+nhF9DPbe+xm6GE2t/K1yaU0tvgfml'.
'z4j0haMfvtv5bHPfuUuPNE+x9gFQ+WoiDIMccg1aJReqdCiSoodJmAeGh1icnTirK/+eMt'.
'I1wCuAV8oBXAUVwqdPoYCngEAgEAgEAgEAgEAgEAgEAgEAgEAgEAgEAgEAgEAgEAgEAgEA'.
'gEAgEAgEAgEAgEAgEAgEAgO4bMN43GtNpFDVu2AkeQSOEdp4Y5JcY84tty4WJLuFlRHkOA'.
'i6kav747jfxuznMXYqP8S3K83jLVCdCQ6f3n4p9vxjPKtxctr4tnbW1MXh7cCoNIbKpAFR'.
'A9ytPFStYcYxy9tmWq+my22BolIH4CNLFR1yDoIarHLsLs8gi9H906ZOnwBP8ApOGs4yJN'.
'wVAeUNaYJmgWr50nQOSUPiNfnByvmqfGXfRvWVT0UaSecGp4bCFxNII1nDWpdTdYw1UFOz'.
'BSQCkiSh5iGjHn7Z6ye+rsJqSNXWOOnpDRd7TJN3TYWk6jRQPEH1hoq1O1BQIBChIj9MNH'.
'Kfd7aSbSrIW7YpBrSsfsEyKT/VJEvSMYzjQ9nk720uEOMOycpDZB/KoDQBUzLwiGM0Xf4v'.
'IFLebsDZ3MpfUAFIPJQ1/kjXtlSq2mh0l/GZJtxE5tz46f0kk/ohgrmbrdGKUGnbf+ItJl'.
'JQ9xP9pOv4iEFxvr03C0qLRaWEgKQfPifwjHK66cZigrjLRV4wEQr4esAqlAQqHlAUzpmB'.
'ASDhARgEAgEAgEAgEAgEAgEAgEAgEAgEAgEAgEAgEAgEAgEAgEAgEAgEAgEAgEAgEAgEAg'.
'EAgEB2Hmr4htqzbM1On3j08B+MdnPku1ssMsttg6JASPhDElc+7hcXvHu5j8eklzHbLt+v'.
'cJkCBd3Ein/VCSPVJjF810bcUtL+Rt2UkdK2kkAeSdf0iOnpi3WXJeKUj3RFiJfPGqCvNx'.
'xLqFNrFSVCShASMuFr92pRUB+RZ109fUQFuzFt9WyFoM3WwZDzEGMxhBWppYPAjx8ZxfYz'.
'3GZD6m2Qoq940c5xMWLj1pfrQaOv8A0oB158VekBRqbR1S+yrpOn80uCh6iAq+uPOcBh++'.
'7FGW21kWSKnG2lKbPloQf0xBxTi8ZbX9wtpdyGH0kFLSkzCh46zEZZtZ7/A32menaXiXWv'.
'8A0tykONj0BOojUjKyv4l21JcdtXrU/wDqLBxSk8yg+4RR62zpQ8xXnLhaUmYt3G1JUqWs'.
'pq84luQnt6uu9Ralz/MSZ8eJjlrs86/JUACpniICM5mU5wEKh5wAL9Z+ZgPJw6jXiYCI4Q'.
'EYBAIBAIBAIBAIBAIBAIBAIBAIBAIBAIBAIBAIBAIBAIBAIBAIBAIBAIBAIBAIBAIBAIBA'.
'dO29wchkuoR7Qaz6AcBHfHP2yO/v2MdY3N2+vptWjK3nV+AQ2mpR+AETcJxaj7Z2bjWPyW'.
'6sgkpv9xXDmScCtSlLp/comfAJPzjMW3GwMMouXDzyjOWk+Z1jVZk1lHUAg1DresFOt6/K'.
'AdVMBEOjSXDyglY/lrEOhVwwmShq4gePqIM2Yt+GvCxcKZUqSXeA9R4Q1Yy/rDSRlPwg0d'.
'b1+UA6w4EzgHW9YB1vX5QEj1Fwy4w4Km3UlK0EcQdIDkLeG17LFbiuMfbXBs3VrFxZrcJk'.
'tp3UAHwUlU0xMZsZHZIUwwhtT63lJEuouRJHwAnFZVZWn9bWAs2ZLPRbSUgrrqQfKQ1Pzj'.
'PKtcZrGgtUtfwjlJjoVeMvhFEQ4eHCAlmZ+XrATBZH63ygAWQPzT+EBIpU5T8DAeo4QEYB'.
'AIBAIBAIBAIBAIBAIBAIBAIBAIBAIBAIBAIBAIBAIBAIBAIBAIBAIBAIBAIBAIBAIBAdJY'.
'hamnFooK65TWj3AS86Zyju5Sse7g393c2rW327S5asslrmswttbdtbWLfufrfICAVpFIE/'.
'GZjLa+uqbZxNs1ZJDluUpPUZ9yJS9uqZjlFkZ5Llg5hgkaqUokjxHlOCxfanPI/hBStz9l'.
'X4QCpzyP4QCpzyMuUBAKc/ZVLlARKly1Bl6iCVjV8y0XetauoDyTNTSVCZM/AfyQJIvlu6'.
'640lS21oUQJpIIl+MFVNTn7JHwgITXPgqfKAjU5+yT8ICNTn7KvgICBU5I6E/CA0z3Mx2G'.
'ubzF3l9e27F2wlSDaOOoQtaSZoVSVAkAhUEvpiyNEgIE0gaEa6QYTFS/FMIMbyzjhdCaFU'.
'pGhkZEnjHPm68YtFSwZSnOMqmmv1gITUPAz+cAmrxBMAJPKATV6/CAhrMCAqhwgIwCAQCA'.
'QCAQCAQCAQCAQCAQCAQCAQCAQCAQCAQCAQCAQCAQCAQCAQCAQCAQCAQCAQCAQCA//Z'.
''), 'image/jpeg');
 }

function image_sendmail($fname)
{
	pic_headers($fname, base64_decode('R0lGODlhCwAIAOYAAAAAAP////r6/fPz9v7+//Dw8efn6MnKt9'.
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
''));
}


function image_w3c_xhtml_valid($fname) 
{
	pic_headers($fname, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAFgAAAAfCAMAAABUFvrSAAADAFBMVEUAAQEHCAkKCwsNDx'.
'ASFBUTFhgXGh0cHBwaHiEcICMjIyMgJSkjKS0oKCgmLDApLzQqMTUtNDkzMzM7Ozs2PkQ4'.
'QUc7REtLS0xBS1JPT1BGUFhKVV1UVVVbW1tEXW9MWGBQXGVZXmJVYmtZZnBbaXJlZWVsbG'.
'xgbnhjcn12d3p7e3sAWpwIX58fW4cJYJ8NYqERZaIVaKQaa6YebagybZkhb6klcqopdawx'.
'ea85dKE1fLA4frJnd4JpeYVtfol5fYE8gbNEhrZHiLdKirlVkb1alL9rg5dxgo5zhZF4ip'.
'Z6jZp9kJ12kaddlsBjmsJpnsRtocZ1pcl8qsyDg4OJjpaXl5eYmJiAk6GFmaeGmqiQl6CL'.
'oK+NorGZoaqRp7aUqrqjpaarq620tLS/v7+Crs6GsM+FsNCJs9Gbs8OQt9SVutWYvNebvt'.
'iiusulvtCfwdqnwNKjw9upwtSqxtmtyd2wyt2yzeC20OPCwsLMzMzU1NTY2NjA1ufG2ujN'.
'3+zT4+7d6fLn5+fs7Ozi7PPp8fbu9Pjz8/Py9/r1+Pv+/v7MzMwAAAAAAAAAAAAAAAAAAA'.
'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'.
'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'.
'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'.
'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'.
'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'.
'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'.
'AAAAAAAAAAAAAAAAAAAAAAAAAAAAB3irofAAAAi3RSTlP/////////////////////////'.
'//////////////////////////////////////////////////////////////////////'.
'//////////////////////////////////////////////////////////////////////'.
'//////////////////8AhcMV7wAAAAFiS0dE/6UH8sUAAAAJcEhZcwAAAEgAAABIAEbJaz'.
'4AAAPsSURBVEjHrZaLVxtVEIfvYlpAY2xpIGBSbRX7cKJJThQQRC2NtVIIBmmR1m76ICd7'.
'KsvJ4z5EoK3lletf7czdbLJJrJz09Jezs7PJud/Ozp2ZDdt563r0D4nt6NN1wJVSQgmhpJ'.
'Hynf/Qw7XLfYAVJ67yceiJtwY25OZi9T8h9wmmNBiYMIlofqSi6KXk5jbiDcBcOHZdiaJd'.
'rxRdJZwiL9qkIrftupQV25bCdt4o4tssLm0rLObZNaHCzB1mpGGH4fcyyizhsnAPuJot4O'.
'ryVJkgR8lklpTT+vjXKzv6SQPB+Nij7M57VlH64N834iy+YbuMDWwj3eIui/SAX0IGiXsw'.
'R+AqeFrVOoardkd2EYwL3BBjVyWCx+bnzzJXiGt0C5edt769dCZMEfeC9TQ8R5uGQ7TLkD'.
's82ILMiX7E3r9+ITTwygPzOLNqBDZylUKwQlw0PjQwGbHwFhHVAy7AJtoZqKLNwEutp8id'.
'uIBZ+Oxz7YGLLETpxFTwejgQcbTILDdiUsF7wHswi6sBMK/7MI3ppmsdop8axx5YhK1ilC'.
'0RWIlIIOJRHp9UEUrF0OLioiAwQKsqUpiFLYC01qtQ0Icp2MetGwy29CRi3HfO1ChidQ7B'.
'fsSjFGbYkq5J0TaCvwAie+AcPnoOZkBhPvZ0HpIrR7oRC3ae62CLuE695lS4chwuVM2pKc'.
'Edl8CuI4VD4urhV2bvm+Aq5BqpLIfCAUWdywDMNvS9Npg2z3S1d3iDSJg2bzez13/fNKvK'.
'Ax9CWkH+JDm9BT/S9X4auP6zA4w8HoQp1TE+fPiNJtfvvDnMg9Q3wKsMrVcgH+w8KVsAQl'.
'deO9taXB9cAviygdUAcGSuOUx1gp0wY+teCpzzzIp3oPncs26uD34OcJNaEH7QTXD2lecd'.
'H1NVSGdyzAdfYt+Ns40AV8xCqtzFbQ2hLPxhWrCsny2bVCw89chXnlJV4KpPPbBQw2e5zT'.
'7pev7kppQLHvajjumWhwPTgi/QKxxV01BtXPybuNZf3ub5YFVj50SFfdDmrni8wk1zSt66'.
'2AGWM2T3vtb6JANJoNbbGUwk3mV3dSdYqtCwcNiHwQxDW8mlrnnc2DQnnGn6xUomkz9BZz'.
'cxOPLYrwpuwFc/FnLMcn5iPwdzvNDmlvsb9DSDUCEZYUo+sCw2VAlWhVhucft+g9TvrK8v'.
'bcgHvyHHub243VW/yz6333eefG1LNPULclOl/l+mUp0ClqvJVEnx/l+m/DQwL5U4jaf+/w'.
'k19ynICvo0o3gn+MmpenyfdJfM2v221u6R8b5p2e+v++BYIhabmJhoHYlY99loDI/xWFNR'.
'3xkPWE+J2IgB/wsqW0UODQNcGAAAAABJRU5ErkJggg=='));
}

function image_bl_pix($fname)
{
	pic_headers($fname, base64_decode('R0lGODlhAQABAIAAAGYzzAAAACH5BAAAAAAALAAAAAABAAEAAA'.
	'ICRAEAOw=='));
}

function image_lyrics($fname)
{
	pic_headers($fname, base64_decode('R0lGODlhDQAKALMAANTi/3GY6Mmmauvx/4uw/93Bj7/T/2OK2v///////wAAAAAAAAAAAA'.
'AAAAAAAAAAACH5BAEAAAkALAAAAAANAAoAAAQtMEl5qj1kpmqQ9wfFfV+4dWRJoSliHigh'.
'y+7qzfRRFLA3pCGeYUgkmnbI5C4CADs='));
}

function image_nocover($fname)
{
	pic_headers($fname, base64_decode('/9j/4AAQSkZJRgABAQEASABIAAD/4QAWRXhpZgAATU0AKgAAAAgAAAAAAAD//gAdQUNEIF'.
'N5c3RlbXMgRGlnaXRhbCBJbWFnaW5n/9sAQwABAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEB'.
'AQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEB/9sAQwEBAQEBAQEBAQ'.
'EBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEB'.
'AQEB/8AAEQgAZABkAwEiAAIRAQMRAf/EAB8AAAICAgMBAQEAAAAAAAAAAAgJAAoGBwECBQ'.
'QLA//EAD4QAAEDAwIEBQEECQIGAwAAAAECAwQFBhEHIQAIEjETIkFRYQkUI3GBChUyQlJi'.
'kaHwFsEnM3KS0fGx0uH/xAAcAQABBQEBAQAAAAAAAAAAAAAHAAIEBQYDCAH/xAAzEQACAQ'.
'MEAQIFAQgCAwAAAAABAhEDBCEABRIxQQZREyIyYXGBFCNCkbHB4fAH0RVSof/aAAwDAQAC'.
'EQMRAD8Avy+g3PYfnsr57+v9Py6gnfdWMHfH8u2d/wCn5e/HbIx2Hb0x7K+Px/PO2O+qtZ'.
'NZbB0GsKr6j6jVdNKt+lJSy0y02JFVrdWkJIp1AoFPT0u1Ot1N5Jahw2ikAJdkynI0GPLl'.
'sr/cmB+pOB+TprutNWd2VERSzMxAVVAkkk4AAySdbCrVbo9uUufXLgq1PodEpUV+dU6vVp'.
'sanU2nQ44Lr8udPlusxokZloFbr77rbTaB1KUBwtvU/wCozbzUp+jaI25/rAtvKYcv66jN'.
'odl7FSFv29R0tIua70NOIJS64i0qFUI62Z1FuWpxHEqUqrXzm4vzmauJyTcbhoViU2ciVa'.
'mmkGYZFHpZZcC4lYud1vw2rru3ytu/bZSF0igOpQzbMGI8KhV61r+l1RSg2SoqJWArcgnG'.
'QT3wNhgentkDjc+l/TthuDpVv6vx0MEW9JwieI+JU7OTBVTGR8xOhtvPrgirUttrAVFPE3'.
'brLMccjSpkEBewrsCTAYLBEnTM1w1b1NkOO3lqleCmHSpaKNZlTkab0GGhRy5Gjt2W/Sri'.
'nwSMJ8G5bjuFwpJQp9aVHGwrJtDT+Y8mVUrFs2qS1dHiTarbtKq0910LCg67OqMSTNedUp'.
'IWtx59xxTmFlRVgkO7PqXTg7+UEKyrcDB7kYG+ADg7AdjnBLawpx6UFSx5g0pPbuopV8nG'.
'2P7+44KlPbdusk421lbW6BYIpUlDHj5LwXYn3JP3J1Atb24vAz3FepWdiDL1CflIWPlkBR'.
'+ABo7bM0800rbMdqqab2FUW220ANzbPt+UlAUnBCQ/Ad6QpJUgpSBlOU7gkcbca0R06YbK'.
'7dokqxniUL/4eVuuWHHU8Nw5Lp1qVKk0mpltZBQ3VYE5nHlU0UKIODaXKDjcfP7zLShucH'.
'JSM5xuMkjGe/BLRRhBO25AxsdwRn0O+T8b/wBQGfVCCluDhQBJMR+h8k/jOfue9azYFL1K'.
'vISoPmMQvjyMnx/Ya1UKTqraoC6RckLUenNlSnKRecaBbty9HmKhBu22adHoroYbHRFg1O'.
'zA/MdUDOueKnxH+MhtjUSi3LOcobzFRtu647Tj8q0bjYagVxuOypLbsuH4EqXS69TGlust'.
'u1i2qlWaQ1IcTDdnImJWwjOiRk+g3zsP/rt7n4+RjjC7vtegXdCRBrUQPKjO/a6dPjvyYF'.
'Vo1QQ2ptmpUerQXY1RpVRZQtxKJkCVHfDS3WFOKZdcbXjL7cKNjSNauflBEwMx5wBkx1iS'.
'ffWsWmxYcDPgo2Z6A4t2D1MyD9ozm5zvgq/IZ/e9N/bb/wCd9uOBnP73p3GNs/j/AH32z7'.
'caMoGoE61a/TrEv+pNVJNWfMK0L+8KJCZrsvu1b90xIbLEKk3e6kEQ5MKPFoNzLbX+rYtH'.
'qS2KCreYxnv7egBznt27j3GP9x0tbmjeUUuKDh6dQAggEET4IMEEdGR3rq9OpTIFSm9MkA'.
'gOpUkHyJ7H3Eg+DruO3r6d/wAB/n454nHAz6Y9PXHoP5f8+OwnEjTNeTWa1Srco9UuCu1G'.
'HSKJQ6dMq1XqtQfRFgU2mU6M9LnT5sp5SWo8WHGadffecUlttptSyoBJPFN/nV593uaXVe'.
'VWqfUpMLTK150yk6W288HWUs0jKo8m86pEWEEXJd6UiU74zSXqHQlU6320pkMViXVGPfpA'.
'vOedEtDLS5cbXqohXdr3IkTrucYfDcqFpVbcmP8AbYK/DWl5gXjcK4VJyoFmfQqTdNOcSo'.
'Pnopyx9UEqmgGbkJKTgOZwQAcYO4OxO22VeoxxU7za31zbJStCU5nm5hgXRSIQQPpYzymC'.
'YAGCZVXZG3m1qUTW+DTnIDQanEAwT/6gkSpMMfqEDLmrLulEhba/GDgURv19+2RjJz0+2+'.
'cdvTgkaNVErSFBzuUZIUSAejODnc7kZwQeE/aZavIYcZadk9aFdOxXuk+QhQ3OCe3pkDI7'.
'cH1YeoMOe0wESkOJWEnAX2Vvuckfs5OAR/XccM9JerrnYr5LHcy1NSwQs88QTxAKzEww6B'.
'mBk5Ehj1R6T3DZKhc0XNIwQ4WQwOCRAxxjOB2f1PK1ap0pQTgBScdXUcY3QDgA4wo7Z9AR'.
'kZ4LOwapltCSepZSkJwQCB5cKznGMAfhn1OcgTa9YQY7WHMlSUg7g7En2G+M75+OCIt7Um'.
'0bOgqrl4XRQLTosbw236tc1ap1CpbBwFffVGqSYsNnypUo+K+jASewB49P2O4UNwsBXpVV'.
'ZPhc+ZcAEQCPmYgCQJyZH9avabluSqORYGAoDZAgQACSYMz3p1ejDokxoywVbsISDkkjpG'.
'Vevfv/AF4KRkYQgDI2OQAcE5GTj0wO3qMbYxwsnlV5suWHUCdCtazOYzQi7rnkENR7dtjV'.
'/T64K6+vrThtikUi4pk95ZK8dLcdSu2M5A4Zs2UFCCCM4G2TtnpP5jAO49+A76pqU23J2R'.
'1dIwUYOMx5BPmdFH0xzanctURkcVOIBVllfBAbuYBkf/NdXVBKSTt6eo99vf0xxrO9rqj0'.
'KnPKLqRJUghAJ3AwO+TtsRgYO3ycHJbouGFQabJqMp1CG47SiApQBWrBwn9rfJ7+w37DhV'.
'WuPME245LU3MDYCl4V4hGEpPSEoGcYP72QT+yOBPeU62+7qtpQBa1tmHxiA0VGBHyz1AIP'.
'IGeu8YMno/0027Vzd3I4bfbQ9Wo4AV2HFhTVmEEmB1mCPfX1ayarQpsSpU+Y6mVFlBTctv'.
'xVoCSlQcadjutqQ9HlxnkIkRpMdbb8eQ22+y4262haSd5NeZWPrJQanY1w1NEnUSwmmEyJ'.
'TpCJF3Wq64WaXdQbASlypRnAmj3UlgeG3VkRqqGKfFuKmwGK/OrmvMZtUlbk3pUeoJYS4O'.
'pfUVEqUASkA47kpwMgb9xp0V54pWhuu9i6mx6gtyDb9eaauWlxFdaqtZdUW3CuyjqSFYkS'.
'X6Up2bSkSMx2K/BpM1SCqGhSSAm0UbC0EOEqcV4UxABGMRGMQM9REzqb/wAg7vsT07e3oi'.
'jRq2hCUWVVFQoxRWpsVHJk6YAk8SCR2Rq70Ccfjj90+w9uJx5lErFLuKj0qv0aYxUqPW6d'.
'Bq1JqMVYeiz6bUYrMyDMjOoylxiVGeafZWk4W2tKhkEEziHocAyJHRyNfmZfXq5yXNUvqI'.
'63luomTRNMamzo/bTPiIW1FhafpdplXbaCXAgokXm/c87rSOpxMgdR8uOEmU3XJ9MxS3pC'.
'ejqOTnugKOxwrAAHl2UD05ByBxivN9flYv8A1x1QvepvrkTLsvi57mlPKUlanJNfrs+qSV'.
'kBOR1vzFqA6iT1d+x4EZypymSsEubDAJyUkkknGE49STncH0G+Jdev8Cr8IKCoUACScLC+'.
'YgmJnxOBrhSvK1MAq5iFcCeiYbOc9/2jJ04GxeYGCnwg5NSkpKM5cCfMDuApSjnuQQQSNg'.
'eo8HnpVzEtR3Y7qJyVt5TlCnkKHTsCP2sEFOwJIz8d+KzkG76lCcCkuuq3zlJwCcHzeYg4'.
'CSQABkk+2Mbh0010rLsp2PHemsxYi0iSVkJBYTlSugha+grS0oEEhSfcAnipv7Hbt0UpcU'.
'ijHioqIsFeoyDJaeiCIbswY1bDdaNzbta7jaUrm3YEOGUsyqRBIaZB/BGSfvq3zp7rhq/z'.
'AX3ROW7lUptIqOrNbo6rivG/7sR4mnmg+nja1N1HUS91ocaRNljw302xbJkRxUZDKplRWi'.
'moaj1F5HLz9D7ROYin3xrDb45l9Q32EuSta+bNqtXw3LlOFtx13TTRCDUrdoVk2sFF1NKb'.
'kVG3n3YwZUbabZUh9zSf6NdynUqkcnNsa53XTUSr25oLgr2td81OShDkiZZ1u1120tIrJU'.
'64Cs0CJJotWuF2AFfZpT1JpzMhpxlLjarSdUkVBoM0+iJgt1CQ24tt2c247DhR2ShDkp6N'.
'GejvyihbrTbcRuVDLxWcyo6G1rEuzSvtlnT26ne3VWjSGWqVWlicqIEABFPEeSZJOstZbH'.
'tFpVrXFlaKiVqhemKnzuqTCgkyMxMD5esTnSk79+jvyy3fQZNLqOhHKbdUYpeU3SKjy+NW'.
'stK1JbDTlPue2rukVOjzmi2S1Uv1bVnW1rS42yFMhLgiSmear6XLky5dJZuqGtnLTakVdT'.
'1K5PNUrtm6kXzYlhRXEN1LUHlH1erS11y4KRbDK0S6hpTdT4p64Ycp8Cl2PNeiVOW+moM6'.
'o246qts1ulXrSmWi5UrWFuJo9XW0hZW+9bdWi1N9pMlmMXPs9HrMKYmqPoYjGu0krclK+y'.
'/rOo+p1pRnWUR1TW2W67a9SejhYZluw1FlEhlxPW/SqtClO0ytU9xIROpc2VGdQCtJQ2sD'.
'Xp1KVV6rpURlYCq4eGEfI/LkjD+EqRBjB1cLTpq61FRFYccqoSQsfK3ADkpAgyDIJ86SXq'.
'B9RWydX7DpF82TdECo2bdVFh1y350J1bUaZSqgwl5h9aX+iQxIHUtibClNtTIE5p+DLYYk'.
'R3mkJs1w5uIqn5zcSol9Sy7hbbgUUkgdISnIDYKskKcOTnPR6FYf1DqqjkY5sdfNCrYcqN'.
'E00qU+k666X0ddQK4VLoGssVV01u2qfHQhpMKDS6tKW8iIkrYMyXMfbCC8Wwo+7udaXJ8d'.
'LbzpySkkHCvKQQCSSonZX5Y3xk8ddttrPZaSULFWGJNe4PKs/IAlmYsTnkAemJ7gk60V/w'.
'Ct7+vY0rDbrWntlvSRVqUaDlyaigK5XIJ5MGIZiWMiROm9X5r9Lqq3nHqj0hYPWlLuVq8x'.
'wFuEkqzuceUbbZxjgXKvrNFbkKImN+VeThwDOQVeYhfc59VZ9AN8cKjrPNDWZ/Wpt4jxln'.
'y9WSOkjpzk7AkjAJBHcbDJ127rTXJjilLeWVlYJDaW07lSgQrp7rIJGx/srAktWpsxLu1Q'.
'ns5ABkYy0e/sPaOwO69K5r1DUqBqzsPrqseQM9QcQPt/jX6x30S+ZSJrt9PjSuVVKq0/Wt'.
'LqncOjtTekSUBamLQdiTrZb+8UF9Maya9bEQElRJjk9RO/E4o1/TW+opfugnL9VrOo1SlR'.
'4tR1IrdyONtOpbSZEu2rPpS1YIOSW6M0OrPpj04nDv2dW+YEQ0ESfBAjz9/9gjU2nffDpp'.
'Tf60VVbIGVEeVnxpImulrz498V6E80rxY0+RGeBQoK62pBaWCewKFNrSseTsMhOcHRyNOa'.
'vNUv7NFkLRhJKltFHnKcrSlIcc8ocJSlW5dSA6UNklCbCXOryiuWTzp8xmns2nOIatrV29'.
'Wae240odVFn1yTWaE4EkqSpt+gVSnutuAK+7dHSrBJPgWnyqqlhsM0FSkr6R1BkpTvgDzK'.
'2x6eu/xwq9FKlRnZm+YlgBEgEgj8iD7D7ROrWht91cCmKNF3BCDkqkiCFAPWQCY/rE6R3S'.
'9BbmnqQlqM/wCc4wGlAj3wcA4PVtsQRgYCkqHG8rO5UbwktyUNRXGlPx5CUYbKOt1bZQlS'.
'lJSlKlZUAVEgqA9hkWLNMORyVPDL0imNRkOFPSr7P5juMYJG/cbJSrOQds5LIdH/AKftEd'.
'fhqeo5nOktZLrCvC6so7JI6TknqHSGx/CBvmuurnbrCkal5WpUlQEszMOQHZJE+MRIE/pr'.
'SWfonc7tQ7lbejHKpVrNxVFEEyYEgCczJ/Q6av8Ao9OqFGu36cnKdTopabnW/ppcek1cjK'.
'PS/Tru0sv24H51JkBQCkTZNMu77e3HXlwsU+Y6ElLTqg6K/wC4pNiVOmXrMgVCfaLMKXSL'.
'qco9OmVio0NqRJiyKbcKqVTo8ioTKTDdalxqwqBHlyYTEyPUnI/6vhVCTHrRaXWBfH0l9T'.
'azcd2Mz6FyOcyd60u7EahsMqdp3KrzJSumlGqXigqQ1QtKtWTICFXFOdjW/Fr9Rrds3A7T'.
'Ylcoj79iOzeY+xKvTon+sanTLXkPMNuorUiSFWJVWFoSpmfT7tdcNLhtTEq6mKbX5NMqql'.
'+ImPHnxkNT5PG23G03Kmt3Z1PiUK8vSbiwDLJBiQJgggn8eCJoKtsbKrUs2rUq7W7tS+LR'.
'bklRVYqtRcAw6w2RidZVQNetJ73dTT9Or6tnUOruHwzT7Mq0K43KeoqDZer7lKXKbt6Kyo'.
'/fvVlcM9STHYQ/NWzGdzqtVOn2PZs+py1Bun2zQX31gAdS2aXAKktNJ6Udbr3gpaZaGC46'.
'pDaASpI4xSbrRo7T2PHVqJZEpxxJUxBpFwUqs1aesjIZptIpMubVKpJWcdEanxJUl07NtK'.
'OOE1fU6+qnYXLdYTcSjsLunVS4nCxoZoRTw3MvrUy+mUl2g3FdlBZcectbTu0JqY9xTI1Z'.
'+z1KZLgxHK9GoUGnvxqhI5KMllAySSQAAMkk+ABknwM65khckwP6/Ye5+wk/bVIz9I11Ai'.
'3r9R276BRpaJEvSrSTSXS+6H433jCLuoNqQ3Lii+IhW7sKovvRHEkq8JbRQVFQOa8U2n1B'.
'SlLALiio+VIdyOoJVuk4yE+YFPmGx3xglz+qWhV7XZUbmvnVeou3Nqpf9w1e9tQq64qQ+J'.
'Nz3FMeqVQjQ3HcqNPp7rohQ9kJcbY+0Fttb6m0hPcWi79IkOBMdRb8QnZCxkYUQR5jlWRn'.
'GdjjGVHHExrf9ppUa9u6V6XwjBpsCIAUEyDH8OIMH+nG427cLKGuaBT4hFSAD8oqAOI68H'.
'vqQfA0EggSkLbStt1JVgnCVY8u5222IBB2x/bj26bBcUtCSkHDgJ8pUSQpWPOQFdJyMgEA'.
'gdgTkkG5Yi2nC0/E6FJST0qS6hSSVIOEgnIyM4GO+x3KuOsWwgJaS20UqLgISUrySEqKgk'.
'pIHZIAO2MYIUMkRvgVEP0MDHkSPGf6fz/lD+IDGCZgDz34zEee4A/XRt8sOlNxXHpn+sKX'.
'Blvx016fHWtlqQ4nxkQqY4oEoQoBXQ62SCc4IOACOJxcV+gH9OiztRuQSPqBqHTQ3Mu3V+'.
'+ZtAW8wgmRb1Mo1n254zZcbcJbFwUSvsZCsFbK8DOczi3S4oU0Sm1JOSKqmQZkBR/c/wCJ'.
'EczbVXJYKIYyMjyR7D/OPeJIT6s/KZToHNvZmuTVNR+pNarbj0OrSW2/u279sWN9mcbdIS'.
'Ufaq5Zr8KZH6sLUi0qo5k+GpQ1HYnLjGQhpTNLQgEtdKy0la1ZIO+QrGD7Z9MgnfiyVzU6'.
'CwuYfR+t2QHo0C54MiHdVgVyS2HW6BfVvrXLoU14BJWadMJfotdYbIcl2/VarCBBkZ4FPl'.
'd0xo19Wg3UarT10S4Leq0u2bwtSYpC6va12UJ4Ra5blURkK8aE+EvwJhQlitUKZSa/ALtN'.
'qsN5wber973bbf2BLKhVrJdn9lFSmJ+HXB/drUIIK/EQwjH6ihUZiS36b9Q7Vt2ztRuLcG'.
'/t6zil8gYVreoRUVgcBWovzUgyWUrGBgdNI+VeTVFRg1TT4aVNlx1bQS2gbZUD0bYwonGR'.
'n1PDQNMNB7asiKw65DYl1BKE5ecbQW2lBKf+WlSdyCkYV77hIO/G46HbdOokRqNCjNx2mx'.
'shAGe+CpZxlRz2ySfwAHGQkAdsYwMYI227bf58bjis2v0xdX9Vb/f6rVgYqUrLl+6UniwZ'.
'wCJIiII8nvVBvHqfcN0PwxVahbCQKNL92rDwWCwCYwZEHvvXg3BaltXbbtXtO6qDR7ktiv'.
'06ZR67b1epsKr0WtUqoMGNPplVpc9iRCqECbGccjy4cth6O+ytTbzakKUCnW9/pGR9PnZk'.
'vkl5rNZOUujPLeko0flU639e+X2lrcWt1TVq6caklFyWJBW4t0ijWHqRbFvRW1lunUeGlK'.
'AHUZASc/PrsAMH/Y/j87Yx6sOfcP8AbdK9wTjA61EHfHpt8/0JIsqVNHo0kRFpoyKtMABV'.
'XkMDoAQM5zHnxhd3qClaVaqwKyoxR5AYMASD7nPgeY1WC1G5QvqEMrco94fUYpMOheCmI+'.
'5pDyoWrYV0zISS6hXg1669TdSqbSZSkOKAXEtp5hlRUY7DJKyvQ1F5HNJdHHa7cdPbue/9'.
'TrnimNc+smq1wyb61PuBkLW4YbtfnNtR6NSC6lDgoNr0+g0ELaacVTFONJWH7axwUvF9fS'.
'olLoBxjAHn3zjG5IyPk49OAX1ApaVsBZSEjrKcHcDPUAO4wN8nYEdxtxu/U3pTbrn03WS0'.
'tUo1K1ryZ058i6gE5LMQCclVIB/ikaGO5bluLGkXu6sLDFQYDFWUg/LBMQezA84GK8mu2j'.
'TcWXUFpiJGHHCOlOQDkkZ2I6SdznAwCOwxws699MkhckmMAsKVlRGQP4QCMY/aGD3Bx34s'.
'j6vWQ3UWJTqY6VKCVklSAepO+fT89znPzsFbaj6eht6ckMDCitQASANldQ7YGxyBnAPr6c'.
'ef/TXqG62mu+23BYik5VCSBIHRgiMT/IGIJ0d/Q/qGjv8At1Lb9ydWr00CK7wzCFlRPmcA'.
'TgD86TlXdNWQpREbCwsqJAOxzlQSSO/l3SffbG/Hw6e6M1u977tSy7ao8isXFdVy0q3qFT'.
'Y4K5NQq1ZqLFOp0JlISetcyoSo8dGQOlTpKyUgng6a/Y62krUpkhACiSEA4AOTjG4PYAYP'.
'Yb754fL9An6eb956pPc5eo1EWix9N5s2l6SRp7BS1cd/Jbdh1C5o6HQUSabZrDz0eNISh5'.
'ld0ST4LrUy23AC3b7jb3Vq9YEDgoLDEktAUAkds5jHmT0DqZvmxUbBfjAnjUYLSYAQXIHG'.
'AMfKPnbxAzBibSvKFoDSeWDlo0Z0HpC2S1pxY1HolRksICG6lcKmPttz1cJICgavcMqp1F'.
'QVlQVJIJOOJwSSQANiANtskegz2UPXicUrMXZnYkszFiZ7JMn/AH/s6zyjiAo8AD+WuOn5'.
'9PZPbB+fUZ/H8thW1SsG7bGvORr3o3SzWq87BiQ9WdMmXmYqdV7apDakQKjQnX3WoULVO1'.
'IqnWramzHGINz0sqs6vTYTC6JW7dKzfHZXb3Oeyvjv/vj2A46Yz3BOxyN/4Rsfx3Hz8jhj'.
'KjgB1DqCDBAOQZBEgwR4PY18YT0YI6PcH8edYFpvqTZ+qtrw7ssyqoqNMkuvw5LLsd6DVK'.
'PVoTpYqdBr9ImoYqVDr9JlJXFqtGqcaNUIEhtTUlhBxnPCke/t/D7f9X/v54HG/wDQiau6'.
'Jeqmi1yN6YaryW2E1t809dTsLUqPCHREp2qFpMyoAq70dkGNTLvo8ylXrRGF/Z4tYlUgP0'.
'WXjdN5oYlpymbc5kLWlaEV5bzcGNdFWmrrOjNzylOeE05bmrDMSHQqeuepTZh0G/WbKul5'.
'10x4dFnJZXIX04z9IJHt5HsDmWn3APmQI03nwAD9x9QB4mIzP8Mz0evcjRVvLAT0++STgb'.
'YONxnv3IycYOfXjFK650xn8AY8N0kHAOySQB6kEnHzjBGNuPZjzok+OxMgymJkSVHQ/GlR'.
'XkSI8hh1IW28w+0VtutOIwpDiFKQtGFJJSQeMZuBSjHkhOdmXCM9QzkHbODvg7bbkYPYkT'.
'7FJr0xmeSmPOSs/jH/AH76y2+12ehUXueSqBBxGfc5gdfbzoM9TE+M1LGTusbd07q2ztsR'.
'kDuN+3wGl7Qw5HX0oz5gfMAdtx3GCMg4BHxgduDWvdguMyQBkeQj9rvnGT6ebBzufXHbgV'.
'rtgYjughJHiI3BPbJzuPXqTn57+/BtNFKu2CmTINBlIGSQU9veehjv7aw240S1JW8KvZye'.
'RgQfIBMGfB8eNAtdlKTIS+haM4CgQR1EbKScdWDsCT2274O/AM6lWM2HZSvCT0LQtQV0jy'.
'A5JJOO4GNvfHYAYYreT9Nh1Jillxcir1RfgUuhU6PJqderDqyUIapFDpzMusVZ04USinQp'.
'JQnqWvoSFKG/tHfpz3xrFOhXJrXGqGmOn6FtvCz25DTeolyMDpUGakuMt+LZMB5AUHUpfn'.
'XOpCk9At6SgqV5H9Vel72pvrf+OTgOZL1n/d00B4ksWkMzAAgKoYlvYTNr6QG4292Ht6VU'.
'U1cMz8SqLgEHkwC59gSejEHSfeT/AOnveHORqQiAI8y3NG7bqTJv++zHCAUNLS65bNtreQ'.
'WZ10VBsBvpQHItAjOKqdTPjphU2dcy0407s7Sex7Y05sChwrbs+0KRDodBo0BpDceHAhNh'.
'toHB6nX3CFPSpDpW/KkuOyH1reeWo/3sTT+ztMbVo9k2FbtLta1qDERDpVFo0REOHFZQdy'.
'lCAS686vqdkSHlOSJDy1vvuuurU4rMcnPY9h6n+L1yOx+fTbjXbfaCytKdsHaqQFNSq2Gq'.
'OB9REkADpQMAe5kkvXl/c35pm4eVopwpU1J4IIUEgeWbivJyOTQB0ANcBPyP+1J9B8n/AD'.
'f1zxOOwGe+R29T7D5/LicTNQtdQduw7ew9l/8Aj+59+OoO/Ydj6fH/AOf3PvxOJwtLXIUd'.
'9htv2/mH/n/fvx8NRp9PqsSRT6pBiVGnzGFx5cGdHalQ5Ud5BQ9HkxnkrZkMOoUUuNPIWh'.
'aT0qSRtxOJw1vpP++dLQuzuUXSmnTXZ2mUu+tCZ8l1x906L3jUrQt9chfiPLkvadyU1jS6'.
'XIcdJW6/NsiS68okuqWcYUBzac6PMhyv1KTRqBqCjUaNGddYD+ptq2e7LdQ2UD797T2g6e'.
'JWpQdAWsNpUfCbIIPiFycTi029mNWSSSCIJJkZXo+NUm5UaLEFqVNiHWCyKSMA4kYzoGqH'.
'9UbmU1BrEWi1Kn6ZUtmbISw5Kots1tEttGerLX61uqrROo5Iy7EdGCQEjbDrNA+XSl662w'.
'xdWo+purNQQoxVLt2hVy2LLozgeLilpXNs6z6FdhScFOE3OklKikk4R0zicam/vLtbVlW6'.
'uVWQIWvVAgggiAwEEYI6jUChb27/AAw1Ciw5dNSQjDLGCp68e2mB6XaDaO6QRnBpzp7btt'.
'S5IAm1lmM5OuSpYA3qtzVR6dcFUUSMlVQqUlRJJzk8bkSfTA7H09gf/X4cTicYQkszMxLM'.
'Tkkkk4HZOT2dahVVAFRVVR0qgKB+AABrsVbq2G3x/MO/+d+OArcbJ3IHb59PnicThadqFZ'.
'HYDsPT3A+eJxOJwtLX/9k='));

}

if (isset($_GET['image']))
{
	$aimg = $_GET['image'];
	switch ($aimg)
	{
		case 'w3c_xhtml_valid.gif': image_w3c_xhtml_valid($aimg); break;
		case 'dir.gif':				image_dir($aimg); break;
		case 'login.jpg':			image_login($aimg); break;
		case 'nocover.jpg':			image_nocover($aimg); break;
		case 'kplaylist.gif':		image_kplaylist($aimg); break;
		case 'link.gif':			image_link($aimg); break;
		case 'cdback.gif':			image_cdback($aimg); break;
		case 'root.gif':			image_root($aimg); break;
		case 'saveicon.gif':		image_saveicon($aimg); break;
		case 'spacer.gif':			image_bl_pix($aimg); break;
		case 'sendmail.gif':		image_sendmail($aimg); break;
		case 'lyrics.gif':			image_lyrics($aimg); break;
		case 'rss.gif':				image_rss($aimg); break;
		case 'play.gif':			image_play($aimg); break;
		default: break;
	}
	flush();
	die();
}


class kprandomizer
{
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
		$this->minsec = 0;
		$this->maxsec = 0;
		$this->rowmode = 1;
		$this->fromdate = 0;
		$this->todate = 0;

		$this->fromdatetxt = '';
		$this->todatetxt = '';

	}

	function setrowmode($rowmode)
	{
		$this->rowmode = $rowmode;
	}

	function setminsec($minsec)
	{
		$this->minsec = $minsec;
	}

	function setmaxsec($maxsec)
	{
		$this->maxsec = $maxsec;
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

	function gettiming($context='')
	{
		if ($this->minsec > 0 && $this->maxsec > 0) return $context.'lengths >= '.$this->minsec.' AND '.$context.'lengths <= '.$this->maxsec;
		if ($this->maxsec > 0) return $context.'lengths <= '.$this->maxsec; else return $context.'lengths >= '.$this->minsec;
	}

	function iterate(&$cnt, &$secs, $sec, $id)
	{
		if ($this->rowmode == 1 && $cnt >= $this->limit) return false;
		if ($this->rowmode == 2 && $secs >= ($this->limit * 60)) return false;
		$this->sids[] = $id;
		$cnt++;
		$secs += $sec;
		return true;
	}
	
	function getfavourites()
	{
		global $u_id, $bd;
		$sql = 'SELECT h.s_id,count(*) as cnt, sum(h.dpercent) as rate, s.lengths from '.TBL_MHISTORY.' h, '.TBL_SEARCH.' s WHERE s.id = h.s_id AND u_id = '.$u_id;
		$sql .= $bd->genxdrive('s.drive');
		if ($this->fromdate > 0) $sql .= ' AND h.utime > '.$this->fromdate;
		if ($this->todate > 0) $sql .= ' AND h.utime < '.$this->todate;		
		if (is_array($this->genre))  $sql .= ' AND ('.$this->getgenreor('s.genre').')';
		if ($this->minsec || $this->maxsec) $sql .= ' AND '.$this->gettiming('s.');
		if ($this->rowmode == 2) $sql .= ' AND lengths > 0';
		$idsql = mkor(musictypes(), 's.ftypeid');
		$sql .= ' AND ('.$idsql.')';
		$sql .= ' GROUP by h.s_id ORDER by rate '.$this->ssort.', cnt '.$this->ssort;
		$res = db_execquery($sql, true);
		if ($res !== false) 
		{
			$secs = $ncnt = 0;
			while ($row = db_fetch_assoc($res)) 
			{
				if (!$this->iterate($cnt, $secs, $row['lengths'], $row['s_id'])) break;
			}
			db_free($res);
		}
	}

	function getalltime()
	{
		global $u_id, $bd;
		$sql = 'SELECT id, lengths from '.TBL_SEARCH.' WHERE hits > 0';
		$sql .= $bd->genxdrive();
		if (is_array($this->genre))  $sql .= ' AND ('.$this->getgenreor('genre').')';
		if ($this->minsec || $this->maxsec) $sql .= ' AND '.$this->gettiming();
		if ($this->rowmode == 2) $sql .= ' AND lengths > 0';
		$idsql = mkor(musictypes(), 'ftypeid');
		$sql .= ' AND ('.$idsql.')';
		$sql .= ' ORDER by hits '.$this->ssort;
		$res = db_execquery($sql, true);
		$secs = $ncnt = 0;
		if ($res !== false) 
		{
			while ($row = db_fetch_assoc($res)) if (!$this->iterate($ncnt, $secs, $row['lengths'], $row['id'])) break;
			db_free($res);
		}
	}

	function getrandom()
	{
		global $u_id, $bd;
		$sql = 'SELECT id, lengths from '.TBL_SEARCH.' ';
		$wh = false;
		if (is_array($this->genre)) 
		{
			$wh = true;
			$sql .= ' WHERE ('.$this->getgenreor('genre').')';
		}
	
		if ($this->minsec || $this->maxsec) 
		{
			if (!$wh) 
			{
				$wh = true;
				$sql .= ' WHERE '.$this->gettiming(); 
			} else $sql .= ' AND '.$this->gettiming();			
		}

		$xsql = $bd->genxdrive();
		if (strlen($xsql) > 0)
		{
			if (!$wh) 
			{
				$wh = true;
				$sql .= $bd->genxdrive('drive', 'WHERE');				
			} else $sql .= $xsql;
		}

		if ($this->rowmode == 2)
		{
			if (!$wh)
			{
				$wh = true;
				$sql .= ' WHERE lengths > 0'; 
			} else $sql .= ' AND lengths > 0';
		}

		$idsql = mkor(musictypes(), 'ftypeid');
		if (!$wh) $sql .= ' WHERE ('.$idsql.')'; else $sql .= ' AND ('.$idsql.')';

		$lengths = $tmpsids = array();

		$res = db_execquery($sql, true);
		srand(make_seed());
		if ($res !== false) 
		{
			while ($row = db_fetch_assoc($res)) 
			{
				$tmpsids[$row['id']] = getrand();
				$lengths[$row['id']] = $row['lengths'];
			}
		}
		arsort($tmpsids, SORT_DESC);
		reset($tmpsids);
		
		$nlist = array();
		$secs = $ncnt = 0;
		foreach ($tmpsids as $id => $key) if (!$this->iterate($ncnt, $secs, $lengths[$id], $id)) break;
	}

	function getneverplayed()
	{
		global $u_id, $bd;
		$wh = false;
		$sql = 'SELECT s_id FROM '.TBL_MHISTORY.' WHERE u_id = '.$u_id.' GROUP BY s_id';
		$res = db_execquery($sql, true);
		$ignore = array();
		while ($row = db_fetch_assoc($res)) $ignore[$row['s_id']] = true;

		$sql = 'SELECT id, lengths from '.TBL_SEARCH.' ';
		if (is_array($this->genre))
		{
			$wh = true;
			$sql .= ' WHERE ('.$this->getgenreor('genre').')';
		}

		$xsql = $bd->genxdrive();
		if (strlen($xsql) > 0)
		{
			if (!$wh) 
			{
				$wh = true;
				$sql .= $bd->genxdrive('drive', 'WHERE');				
			} else $sql .= $xsql;
		}

		if ($this->minsec || $this->maxsec) 
		{
			if (!$wh)
			{
				$wh = true;
				$sql .= ' WHERE '.$this->gettiming(); 
			} else $sql .= ' AND '.$this->gettiming();			
		}

		if ($this->rowmode == 2)
		{
			if (!$wh) 
			{
				$wh = true;
				$sql .= ' WHERE lengths > 0'; 
			} else $sql .= ' AND lengths != 0';
		}

		$idsql = mkor(musictypes(), 'ftypeid');
		if (!$wh) $sql .= ' WHERE ('.$idsql.')'; else $sql .=' AND ('.$idsql.')';

		$res = db_execquery($sql, true);
		
		$ncnt = 0;
		$secs = 0;
		if ($res !== false) 
		{
			while ($row = db_fetch_assoc($res)) 
			{
				if (!isset($ignore[$row['id']])) 
				{
					if (!$this->iterate($ncnt, $secs, $row['lengths'], $row['id'])) break;
				}
			}
			db_free($res);
		}
	}

	function getmusicmatch()
	{	
		global $u_id, $bd;
		$master = $lengths = array();
		$users = array();
		
		$sql = 'SELECT h.s_id, sum(h.dpercent) as rate, count(*) as cnt, s.lengths FROM '.TBL_MHISTORY.' h, '.TBL_SEARCH.' s WHERE h.s_id = s.id AND h.u_id = '.$u_id.$bd->genxdrive('s.drive');
		if ($this->fromdate > 0) $sql .= ' AND h.utime > '.$this->fromdate;
		if ($this->todate > 0) $sql .= ' AND h.utime < '.$this->todate;
		if (is_array($this->genre)) $sql .= ' AND ('.$this->getgenreor('s.genre').')';
		if ($this->minsec || $this->maxsec) $sql .= ' AND '.$this->gettiming();

		$idsql = mkor(musictypes(), 's.ftypeid');
		$sql .=' AND ('.$idsql.')';


		if ($this->rowmode == 2) $sql .= ' AND lengths > 0';

		$sql .= ' GROUP BY h.s_id ORDER BY rate DESC,cnt DESC';

		$res = db_execquery($sql, true);	
		while ($row = db_fetch_assoc($res)) 
		{
			$master[] = array($row['s_id'], $row['rate']+$row['cnt']);
			$lengths[$row['s_id']] = $row['lengths'];
		}
		
		for ($i=0,$c=count($this->users);$i<$c;$i++)
		{
			$sql = 'SELECT s_id, sum(dpercent) as rate, count(*) AS cnt FROM '.TBL_MHISTORY.' WHERE u_id = '.$this->users[$i];
			if ($this->fromdate > 0) $sql .= ' AND utime > '.$this->fromdate;
			if ($this->todate > 0) $sql .= ' AND utime < '.$this->todate;
			$sql .= ' GROUP BY s_id ORDER BY rate DESC,cnt DESC';
			$res = db_execquery($sql, true);
			while ($row = db_fetch_assoc($res)) $users[$this->users[$i]][$row['s_id']] = $row['rate'] + $row['cnt'];
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
		
		$secs = $cnt = 0;
		foreach ($musicm as $sid => $hits) if (!$this->iterate($cnt, $secs, $lengths[$sid], $sid)) break;
	}

	function execute()
	{
		global $u_id;

		if (frm_isset('playselected'))
		{
			$m3ug = new m3ugenerator();
			$selids = frm_get('selids', 3);
			foreach($selids as $id) if (is_numeric($id)) $m3ug->sendlink2($id);
			$m3ug->start();
		} else
		if (frm_isset('addplaylist'))
		{
			$this->sids = array();
		
			$selids = frm_get('selids', 3);
			foreach($selids as $id) if (is_numeric($id)) $this->sids[] = $id;
			
			if (frm_ok('playlist', 1)) 
			{
				$kppl = new kp_playlist(frm_get('playlist', 1));
				if ($kppl->appendaccess()) $kppl->addtoplaylist($this->sids);
			}
			$this->showselect(get_lang(33));
		} else
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
				case 4:
					$this->getneverplayed();
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
				$this->showselect();
			} else $this->view(get_lang(217));
		} 
	}

	function showselect($message='')
	{
		global $u_id;

		kprintheader(get_lang(212));

		?>

		<form name="randomizer" method="post" action="<?php echo PHPSELF; ?>">
		<input type="hidden" name="action" value="randomizer"/>
		<?php

		foreach($_POST as $name => $value) 
		{
			if ($name != 'selids')
			{
				if (is_array($value))
					foreach($value as $id) echo '<input type="hidden" name="'.$name.'[]" value="'.$id.'"/>';
				else
					echo '<input type="hidden" name="'.$name.'" value="'.$value.'"/>';
			}
		}

		?>
		<table width="95%" align="center" border="0" cellspacing="2" cellpadding="0">
		<tr>
			<td class="importnant" colspan="2"><?php echo $message; ?></td>
		</tr>
		<tr>
			<td></td>
		</tr>
		<tr>
			<td>
				<select name="selids[]" id="selids" class="fatbuttom" size="6" style="width:500px; height:290px" multiple="multiple">
				<?php

				for ($i=0,$c=count($this->sids);$i<$c;$i++)	
				{
					$rowcnt = $i + 1;
					$f2 = new file2($this->sids[$i], true);
					echo '<option selected="selected" value="'.$this->sids[$i].'">'.lzero($rowcnt).': '.$f2->gentitle().'</option>';
				}

				?>
				</select>
			</td>
		</tr>
		<tr>
			<td>
				<input type="submit" class="fatbuttom" name="returnform" value="<?php echo get_lang(34); ?>"/>
				<?php

					if (WINDOWPLAYER)
					{					
						$kpwjs = new kpwinjs();

						?><input type="button" name="Play" value="<?php echo get_lang(42); ?>" class="fatbuttom" onclick="javascript: <?php echo $kpwjs->randomizer(); ?>"/>
						<?php
					} else
					{
						?><input type="submit" name="playselected" value="<?php echo get_lang(42); ?>" class="fatbuttom"/>
						<?php
					}
				
					$playlists = db_getplaylist($u_id);
					if ($this->playlist == -1) $this->playlist = db_guinfo('defplaylist');
					if (count($playlists) > 0)
					{
						echo '<select name="playlist" class="fatbuttom">';
						for ($c=0,$cnt=count($playlists);$c<$cnt;$c++) 
						{
							echo '<option value="'.$playlists[$c][1].'"';
							if ($playlists[$c][1] == $this->playlist) echo ' selected="selected"';
							echo '>'.$playlists[$c][0].'</option>';			
						}
						echo '</select>&nbsp;';
						echo '<input type="submit" class="fatbuttom" name="addplaylist" value="'.get_lang(69).'"/>';
					}

				?>
				
			
			</td>
		</tr>
		</table>
		</form>	
		<?php
		
		kprintend();
	}

	function getusers($selected)
	{
		$out = '';
		global $u_id;

		$res = db_execquery('SELECT u.u_id, u.u_login FROM '.TBL_USERS.' u, '.TBL_MHISTORY.' h WHERE u.u_id = h.u_id GROUP BY h.u_id', true);
		while ($row = db_fetch_assoc($res))
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

	function toux($date, $h=0, $m=0, $s=0)
	{
		if (is_numeric($date) && strlen($date) == 6)
		{
			$utime = mktime($h,$m,$s, substr($date, 2, 2), substr($date, 0, 2), substr($date, 4, 2));
			if (date('dmy', $utime) == $date) return $utime;
		}
		return -1;
	}

	function fromArray($where)
	{
		$err = false;
		
		if (isset($where['mode'])) $this->setmode($where['mode']);
		if (isset($where['limit'])) $this->setlimit(vernum($where['limit']));
	
		if ($this->limit == 0) $err = true; 

		if (isset($where['genres'])) $this->setgenre($where['genres']);
		if (isset($where['playlist'])) $this->setplaylist($where['playlist']);
		if (isset($where['order'])) $this->setorder($where['order']);
		if (isset($where['usersfilter'])) $this->setusers($where['usersfilter']);
		if (isset($where['minsec'])) $this->setminsec($where['minsec']);
		if (isset($where['maxsec'])) $this->setmaxsec($where['maxsec']);
		if (isset($where['rowmode'])) $this->setrowmode($where['rowmode']);

		if (isset($where['fromdate'])) $this->fromdatetxt = $where['fromdate'];
		if (isset($where['todate'])) $this->todatetxt = $where['todate'];
		
		if (isset($where['fromdate']) && !empty($where['fromdate']))			
			if ($this->toux($where['fromdate']) > 0) $this->fromdate = $this->toux($where['fromdate']); else $err = true;

		if (isset($where['todate']) && !empty($where['todate']))			
			if ($this->toux($where['todate']) > 0) $this->todate = $this->toux($where['todate'], 23, 59, 59); else $err = true;

		if ($err) $this->view(get_lang(317)); else		
			if (isset($where['execute']) && !isset($where['returnform'])) $this->execute(); else $this->view();
	}

	function view($message = '')
	{
		global $setctl, $u_id, $cfg;
		kprintheader(get_lang(212));
		
		$useropt = $this->getusers($this->users);

		$modes = array();
		$modes[] = array(get_lang(216), 0, 1);
		$modes[] = array(get_lang(218), 0, 1);
		$modes[] = array(get_lang(171), 0, 1);
		$modes[] = array(get_lang(263), 0, 1);
		$modes[] = array(get_lang(280), 0, 1);

		$modes[$this->mode][1] = 1;

		if (!$cfg['musicmatch']) $modes[3][2] = 0;
		
		?>
		<form name="randomizer" method="post" action="<?php echo PHPSELF; ?>">
		<input type="hidden" name="action" value="randomizer"/>
		<table width="95%" align="center" border="0" cellspacing="2" cellpadding="2">
		<tr>
			<td class="importnant" colspan="2"><?php echo $message; ?></td>
		</tr>
		<tr>
			<td height="5"></td>
		</tr>
		<tr>
			<td valign="top" class="wtext"><?php echo get_lang(213); ?></td>
			<td valign="top">
				<select name="mode" id="mode" class="fatbuttom" onchange="javascript: chmode(false);">
					<?php
						for ($i=0,$c=count($modes);$i<$c;$i++)
						{
							if ($modes[$i][2])
							{
								echo '<option value="'.$i.'"';
								if ($modes[$i][1]) echo ' selected="selected"';
								echo '>'.$modes[$i][0].'</option>';
							} 
						}
					?>

				</select>
			</td>
			<td valign="top" class="wtext"><?php echo helplink('randmode'); ?></td>
		</tr>
		<?php
		if ($cfg['musicmatch'])
		{
		?>
		<tr>
			<td valign="top" class="wtext"><?php echo get_lang(87); ?></td>
			<td valign="top"><?php if (!empty($useropt)) { ?><select class="fatbuttom" <?php if ($this->mode != 3) echo 'disabled="disabled" '; ?>style="width:150px" multiple="multiple" id="userfilter" size="6" name="usersfilter[]"><?php echo $useropt; ?></select><?php } ?></td>
			<td valign="top" class="wtext"><?php echo helplink('randusers'); ?></td>
		</tr>
		<?php
		}
		?>
		<tr>
			<td valign="top" class="wtext"><?php echo get_lang(49); ?></td>
			<td valign="top"><input type="text" size="5" maxlength="6" name="limit" value="<?php echo $this->limit; ?>" class="fatbuttom"/>
				<select name="rowmode" class="fatbuttom">
				<option value="1"<?php if ($this->rowmode == 1) echo ' selected="selected"'; ?>><?php echo get_lang(178); ?></option>
				<option value="2"<?php if ($this->rowmode == 2) echo ' selected="selected"'; ?>><?php echo get_lang(293); ?></option>
				</select>				
			</td>
			<td class="wtext"><?php echo helplink('randlimit'); ?></td>
		</tr>

		<tr>
			<td valign="top" class="wtext"><?php echo get_lang(316); ?></td>
			<td valign="top" class="wtext"><input type="text" maxlength="6" size="8" id="fromdate" name="fromdate" value="<?php echo $this->fromdatetxt; ?>" class="fatbuttom"/> <input type="text" maxlength="6" size="8" id="todate" name="todate" value="<?php echo $this->todatetxt; ?>" class="fatbuttom"/></td>
			<td valign="top" class="wtext"><?php echo helplink('randfromtodate'); ?></td>
		</tr>

		<tr>
			<td valign="top" class="wtext"><?php echo get_lang(292); ?> (<?php echo get_lang(7); ?>)</td>
			<td valign="top" class="wtext"><input type="text" size="5" maxlength="6" name="minsec" value="<?php echo $this->minsec; ?>" class="fatbuttom"/> <input type="text" size="5" maxlength="6" name="maxsec" value="<?php echo $this->maxsec; ?>" class="fatbuttom"/></td>
			<td valign="top" class="wtext"><?php echo helplink('randminmaxsec'); ?></td>
		</tr>
		<tr>
			<td valign="top" class="wtext"><?php echo get_lang(147); ?></td>
			<td valign="top">
				<select name="genres[]" size="6" style="width:200px" multiple="multiple" class="fatbuttom">
				<option value="-1"<?php if ($this->genre == -1) echo ' selected="selected"'; ?>><?php echo get_lang(67); ?></option>
				<?php echo genre_select(false,$this->genre); ?></select>
			</td>
			<td valign="top" class="wtext"><?php echo helplink('randgenre'); ?></td>
		</tr>
		
		<tr>
			<td valign="top" class="wtext"><?php echo get_lang(219); ?></td>			
			<td valign="top">
				<select name="order" style="width:50px" class="fatbuttom">
				<option value="0"<?php if ($this->order == 0) echo ' selected="selected"'; ?>>+</option>
				<option value="1"<?php if ($this->order == 1) echo ' selected="selected"'; ?>>-</option>
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
		
		<script type="text/javascript">
		<!--
			function chmode()
			{
				d = document.getElementById('mode'); 
				uf = false;
				if (document.getElementById('userfilter')) uf = document.getElementById('userfilter');				

				fdate = document.getElementById('fromdate'); 
				tdate = document.getElementById('todate'); 
				if (d.value != 3 && uf) uf.disabled = true; else uf.disabled = false;
				if (d.value == 2 || d.value == 4 || d.value == 1) 
				{
					fdate.disabled = true;
					tdate.disabled = true;
				} else
				{
					fdate.disabled = false;
					tdate.disabled = false;
				}
			}
		//-->
		</script>

		<?php
		kprintend();
	}
}


class kbulletin
{
	function getlatest()
	{
		$res = db_execquery('SELECT b.*,u.u_login FROM '.TBL_BULLETIN.' b, '.TBL_USERS.' u WHERE b.u_id = u.u_id AND b.publish = 1 ORDER BY bid DESC LIMIT 1');
		if (db_num_rows($res) == 1)
		{
			$row = db_fetch_assoc($res);
			return $this->formatted($row);
		} else
		{
			$row['u_login'] = 'none';
			$row['utime'] = time();
			$row['mesg'] = 'Welcome to kPlaylist! This is a auto generated bulletin. Click on \'more\' to add a real one.';
			return $this->formatted($row);
		}
	}

	function getlink($msg)
	{
		return '<a class="hot" href="'.PHPSELF.'?action=bulletin&amp;m=read">'.$msg.'</a>';
	}

	function savebulletin($bid, $publish, $mesg)
	{
		global $u_id, $cfg;
		$mesg = stripcslashes($mesg);
		if ($cfg['striphtmlbulletin']) $mesg = strip_tags($mesg);
		$mesg = str_replace("\r\n", "\n", $mesg);
		if ($bid == 0)
		{
			$sql = 'INSERT INTO '.TBL_BULLETIN.' SET publish = '.$publish.', mesg = "'.myescstr($mesg).'", utime = '.time().', u_id = '.$u_id;		
			$res = db_execquery($sql);
			return db_insert_id();
		} else
		{
			$sql = 'UPDATE '.TBL_BULLETIN.' SET publish = '.$publish.', mesg = "'.myescstr($mesg).'" WHERE bid = '.$bid;
			$res = db_execquery($sql);
			return $bid;
		}
	}

	function editbulletin($bid, $reload=false)
	{
		if ($bid)
		{
			$res = db_execquery('SELECT * FROM '.TBL_BULLETIN.' WHERE bid = '.$bid);
			$row = db_fetch_assoc($res);
		} else
		{
			$row['publish'] = 0;
			$row['mesg'] = '';
		}
		
		kprintheader(get_lang(268));
		?>
		<form method="post" action="<?php echo PHPSELF; ?>">
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
		global $u_id;
		
		showdir('',get_lang(268),0);

		echo '<table width="100%" cellpadding="0" cellspacing="0" border="0">';
		echo '<tr><td height="10"></td></tr>';
		echo '<tr><td><input type="button" name="new" value="'.get_lang(72).'" class="fatbuttom" onclick="'.jswin('newbulletin', '?action=newbulletin',300,550).'"/></td></tr>';

		$res = db_execquery('SELECT b.*,u.u_login FROM '.TBL_BULLETIN.' b, '.TBL_USERS.' u WHERE b.u_id = u.u_id ORDER BY bid DESC');
		?>
		<tr><td height="15"></td></tr>
		
		
		<?php
		while ($row = db_fetch_assoc($res))
		{
			echo '<tr><td>';
			echo '<table width="50%" cellpadding="3" class="tblbulletin" cellspacing="3" border="0">';
			echo $this->formatted($row, false);			
			
			if (db_guinfo('u_access') == 0 || $row['u_id'] == $u_id) echo '<tr><td><input type="button" class="fatbuttom" name="edit" value="'.get_lang(71).'" onclick="'.jswin('editbulletin', '?action=editbulletin&amp;bid='.$row['bid'], 300, 550).'"/>&nbsp;<input type="button" class="fatbuttom" name="del" value="'.get_lang(109).'" onclick="javascript: if (confirm(\''.get_lang(210).'\')) location = \''.PHPSELF.'?action=delbulletin&amp;bid='.$row['bid'].'\';"/></td></tr>';

			echo '</table></td></tr>';
			echo '<tr><td height="10"></td></tr>';
		}
		echo '<tr><td height="20"></td></tr>';
		echo '</table>';
	}

	function formatted($row, $single =true)
	{
		global $cfg;
		$out = '';
		if ($single) $out .= '<table width="100%" align="center" cellpadding="0" cellspacing="0" border="0"><tr><td width="4"></td><td><table width="100%" align="center" cellpadding="0" cellspacing="0" border="0">';

		$out .= '<tr><td height="4"></td></tr><tr>';
		$msg = $row['mesg'];
		$msg = str_replace("\n", '<br/>', $msg);
		if (kp_strlen($msg) > $cfg['frontbulletinchars'] && $single) 
			$msg = kp_substr($msg, 0, $cfg['frontbulletinchars']).' '.$this->getlink('...');

		$out .= '<td colspan="2" class="wtext">'.$msg.'</td>';			
		$out .= '</tr><tr><td height="4"></td></tr><tr>';
		$out .= '<td colspan="2" class="filemarked">'.get_lang(269, date($cfg['smalldateformat'],$row['utime']), $row['u_login']);
		if ($single) $out .= ' '.$this->getlink(get_lang(270));
		$out .= '</td>';
		$out .= '</tr>';
		if ($single) $out .= '</table></td></tr></table>';
		return $out;
	}
}


// for the mailing stuff, you'll need this package: http://pear.php.net/get/Mail_Mime-1.2.1.tgz if you intend to use pear.

class mailmp3
{
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
		$data .= 'Return-Path: <'.trim($from).'>'.$from.$this->crlf;
		$data .= 'Date: '.date('r').$this->crlf;
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
		return mail ($mailaddr, $subject, '', $data);
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
		global $setctl, $cfg;

		include('Mail.php');
		include($cfg['pearmailpath'].'mime.php');
		include($cfg['pearmailpath'].'mimeDecode.php');
		
		$result = false;

		$hdrs = array('From' => $from, 'To' => $mailaddr, 'Subject' => $subject, 'Date' => date('r'));

		if (class_exists('Mail_mime'))
		{		
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
		} 
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
		if (frm_ok('sid', 1)) $this->setsid(frm_get('sid', 1));
		
		if (frm_isset('message')) $this->setmessage(frm_get('message'));
		if (frm_isset('tomail')) $this->settomail(frm_get('tomail'));	

		$msg = '';
		if (strlen($this->tomail) > 0)
		{
			$from = db_guinfo('email');

			if (strlen($from) > 0)
			{
				if ($this->sendmail($from, $this->sid, $this->tomail, $this->message)) $msg = get_lang(230); else $msg = get_lang(258);
			} else $msg = get_lang(254);
		}		
		$this->gui($msg);
	}

	function gui($msg = '')
	{
		$f2 = new file2($this->sid, true);
		
		if (empty($f2->id3['artist'])) $title = $f2->fname; else $title = $f2->id3['artist'].' '.$f2->id3['title'];
		
		kprintheader(get_lang(223));
		?>
		<form name="mail" method="post" action="<?php echo PHPSELF; ?>">
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
	function fupload()
	{
		global $cfg;

		$this->ufldcnt = $cfg['uploadselections'];
	}
		
	function decide()
	{
		global $setctl;
		$msg = array();		

		if (ENABLEUPLOAD && isset($_FILES['fileupload']) && is_array($_FILES['fileupload']))
		{
			foreach($_FILES['fileupload']['name'] as $id => $name)
			{
				if (!empty($name) && isset($_FILES['fileupload']['tmp_name'][$id]) && $_FILES['fileupload']['size'][$id] > 0)
				{
					$cfok = false;
					$allowed = false;
					$allowedf = explode(',', strtoupper($setctl->get('uploadflist')));	
					for ($i=0,$c=count($allowedf);$i<$c;$i++)
					{
						$amatch = trim($allowedf[$i]);
						if (empty($amatch)) continue;
						if (fmatch(strtoupper($name), $amatch)) 
						{
							$allowed = true;
							break;
						}
					}

					if ($allowed)
					{
						$path = $setctl->get('uploadpath');
						
						if (!empty($path))
						{
							$uploadfile = $path.$this->replace(kp_basename($name));
							if (move_uploaded_file($_FILES['fileupload']['tmp_name'][$id], $uploadfile)) 
							{
								$msg[] = get_lang(235).' ('.$name.')';
								$cfok = true;
							}
						}
						if (!$cfok) $msg[] = get_lang(236).' ('.$name.')';

					} else $msg[] = get_lang(236).' '.get_lang(301).' ('.$name.')';						
				}
			}			
		}
		$this->view($msg);
	}

	function replace($o) 
	{ 
		$checks = array("/", "\\", ":", "*", "?", "<", ">", "\"", "|", '"', "'", ',');
		foreach ($checks as $clear) $o = str_replace($clear,'',$o); 
		return $o;
	}

	function view($msg = '')
	{
		global $cfg;
		kprintheader(get_lang(234));
		?>
		<form method="post" name="fupload" enctype="multipart/form-data" action="<?php echo PHPSELF; ?>">
		<input type="hidden" name="action" value="fupload"/>
		<input type="hidden" name="fuploader" value="true"/>
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td height="10"></td>
		</tr>
		<tr>
			<td align="center" class="notice"><?php echo get_lang(308, min(ini_get('upload_max_filesize'), ini_get('post_max_size'))); ?></td>
		</tr>
		<?php 
			
			if (!empty($msg) || is_array($msg))
			{
				if (is_array($msg))
				{
					for ($i=0,$c=count($msg);$i<$c;$i++) echo '<tr><td class="notice" colspan="2">'.$msg[$i].'</td></tr><tr><td height="5"></td></tr>'; 
				} else echo '<tr><td class="notice" colspan="2">'.$msg.'</td></tr><tr><td height="5"></td></tr>';
			}
	
		for ($i=0;$i<$this->ufldcnt;$i++)
		{
		?>

		<tr> 
			<td colspan="2" align="center" class="notice"> 
			<input type="file" name="fileupload[]" class="fatbuttom" size="60"/>
			</td>
		</tr>
		<tr>
			<td height="3"></td>
		</tr>
		<?php
		}
		?>
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


class krss
{
	function krss($title)
	{
		global $setctl, $phpenv;
		$this->lf = "\r\n";
		$this->data  = '<?xml version="1.0" encoding="';
		if (UTF8MODE) $this->data .= 'UTF-8'; else $this->data .= 'ISO-8859-1';
		$this->data .= '"?>'.$this->lf;
		$this->data .= '<rss version="2.0"><channel>'.$this->lf;
		$this->data .= '<title>'.htmlspecialchars($title).'</title>'.$this->lf;
		$this->data .= '<link>'.$setctl->get('streamurl').$phpenv['streamlocation'].'</link>'.$this->lf;
		$this->data .= '<description>kPlaylist RSS</description>'.$this->lf;
		$this->data .= '<ttl>1</ttl>'.$this->lf;
	}

	function additem($title, $description, $link, $pubtime=0, $category='')
	{
		$this->data .= '<item>'.$this->lf;
		$this->data .= '<title>'.htmlspecialchars($title).'</title>'.$this->lf;
		$this->data .= '<description>'.htmlspecialchars($description).'</description>'.$this->lf;
		if (!empty($link)) $this->data .= '<link>'.$link.'</link>'.$this->lf;
		if ($pubtime != 0) $this->data .= '<pubDate>'.date('r', $pubtime).'</pubDate>'.$this->lf;
		if (!empty($category)) $this->data .= '<category>'.$category.'</category>'.$this->lf;
		$this->data .= '</item>'.$this->lf;		
	}

	function ship()
	{
		$this->data .= '</channel></rss>'.$this->lf;
		header('Content-Disposition: inline; filename=kprss'.lzero(getrand(1,999),6).'.xml');
		header('Content-Type: application/xml');
		header('Content-Length: '.strlen($this->data));
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		echo $this->data;
	}
}


class caction
{
	function updatelist()
	{
		global $runinit;
		if ($runinit['astream'])
		{
			$ids = db_list_processes();
			$res = db_execquery('SELECT h_id, mid FROM '.TBL_MHISTORY.' WHERE active = 1');
			$timeout = time() - 30;
			if ($res) while ($row = db_fetch_assoc($res)) if (!isset($ids[$row['mid']])) db_execquery('UPDATE '.TBL_MHISTORY.' SET active = 0 WHERE h_id = '.$row['h_id'].' AND utime < '.$timeout);
		}
	}

	function getlast($count=5)
	{
		global $cfg, $bd;
		if ($cfg['userhomedir'])
			return db_execquery('SELECT h.s_id as id, h.active, h.h_id as hid, h.utime, h.cpercent FROM '.TBL_MHISTORY.' h, '.TBL_SEARCH.' s WHERE h.s_id = s.id'.$bd->genxdrive().' ORDER BY h.active DESC, h.h_id DESC LIMIT '.$count);
		return db_execquery('SELECT s_id as id, active, h_id as hid, utime, cpercent FROM '.TBL_MHISTORY.' ORDER BY active DESC, h_id DESC LIMIT '.$count);
	}

	function createrss($clink=false)
	{
		global $cfg, $setctl, $phpenv;
		$res = $this->getlast($cfg['rsslaststreamcount']);

		$rss = new krss(get_lang(286));

		while ($row = db_fetch_assoc($res))
		{
			$f2 = new file2($row['id'], true);
			$fd = new filedesc($f2->fname);
			if ($fd->found && $fd->m3u)
			{
				if ($clink) $link = $setctl->get('streamurl').$phpenv['streamlocation'].$f2->weblink(0,0,'sid', false); else $link = $setctl->get('streamurl').$phpenv['streamlocation'];
				$rss->additem($f2->gentitle(array('title', 'artist')), $f2->gentitle(array('title', 'artist', 'album')), $link, $row['utime'], ''); 
			}
		}
		$rss->ship();
	}
	
	function show()
	{
		return '<div id="streams">'.$this->getStreamByAjax().'</div>';
	}
	function getStreamByAjax()
	{
		global $cfg, $setctl, $phpenv;

		$out = '<table width="96%" align="center" cellpadding="0" cellspacing="0" border="0">';
		$res = $this->getlast($cfg['laststreamscount']);
		
		$out .= '<tr><td width="90%"></td><td width="10%"></td></tr>';

		$cnt=0;
		$rows = db_num_rows($res);
		while ($row = db_fetch_assoc($res))
		{
			$cnt++;
			$f2 = new file2($row['id'], true);
			$out .= '<tr><td';
			if ($cnt != $rows) $out .= ' colspan="2"';
			$out .= ' nowrap="nowrap"><a class="';
			if ($row['active']) $out .= 'filemarked'; else $out .= 'wtext';
			$out .= '" '.$f2->mkalink().'>';

			if ($row['active'] && $row['cpercent'] != 0) 
			{
				$out .= lzero($row['cpercent']).'% ';
				$maxlen = $cfg['laststreambreak'] - 3;
			} else $maxlen =  $cfg['laststreambreak'] + 3;

			$out .= checkchs($f2->gentitle(array('title', 'artist'), $maxlen), false);
			$out .= '</a>';
			if ($cnt == $rows && $setctl->get('publicrssfeed')) $out .= '</td><td valign="bottom" align="right">'.'<a href="'.$setctl->get('streamurl').$phpenv['streamlocation'].'?streamrss=rss.xml"><img src="'.getimagelink('rss.gif').'" border="0" alt="RSS"/></a>';
			$out .= '</td></tr>';
		}
		if (!$cnt)	$out .= '<tr><td>'.get_lang(10).'</td></tr>';
		$out .= '</table>';
		return $out;		
	}
}



// for whoever starting used the name shout for "instant messaging in web"; sorry for using the same name.

class kpshoutmessage
{
	function show()
	{
		return '<div id="messages">'.$this->getmessagesByAjax().'</div>';
	}

	function getlast($count=5)
	{
		global $cfg, $bd;
		return db_execquery('SELECT m.message, m.utime, u.u_login FROM '.TBL_MESSAGE.' m, '.TBL_USERS.' u WHERE m.uid = u.u_id ORDER BY m.utime DESC LIMIT '.vernum($count));
	}

	function submit($uid, $message)
	{
		$res = db_execquery('INSERT INTO '.TBL_MESSAGE.' SET `message` = "'.myescstr($message).'", uid = '.$uid.', utime = '.time());
	}

	function getmessagesByAjax()
	{
		global $cfg, $setctl, $phpenv;

		$out = '<table width="97%" align="center" cellpadding="0" cellspacing="0" border="0">';
	
		$res = $this->getlast($cfg['shoutboxmessages']); 
		
		$cnt = 0;
		$userows = array();
		$rows = db_num_rows($res);
		while ($row = db_fetch_assoc($res)) $userows[] = $row;

		for ($i=count($userows);$i>0;$i--)
		{
			$row = $userows[$i - 1];
			$out .= '<tr><td class="wtext">'.date($cfg['timeformat'], $row['utime']).' <b>'.$row['u_login'].'</b> '.$row['message'].'</td></tr>';			
			$cnt++;
		}
		if (!$cnt)	$out .= '<tr><td>'.get_lang(10).'</td></tr>';

		$out .= '<tr><td height="4"></td></tr>';
		$out .= '</table>';
		return $out;		
	}
}


function kpdefcss()
{
?>
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
a
{
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: xx-small;
	font-style: normal;
	color: #000066;
	text-decoration: none
}
a:hover.hot
{
	color: #EF610C;
	text-decoration: underline;
	font-weight: bold;
	font-style: normal
}
a:hover.hotnb
{
	color: #EF610C;
	text-decoration: underline;	
	font-style: normal
}
.smalltext
{
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: xx-small; background-color: #FFFFFF;
	color: #003333
}
.tblbulletin
{
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: xx-small;
	background-color: #ECEFF2;
	color: #000000;
	border: 1px #000000;
	border-style: solid
}
.row2nd
{
	background-color: #DCDEF4
}
.tdlogin
{
	background-color: #262626
}
.logintext
{
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: xx-small;
	color: #FFFFFF;
	background-color: #262626
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
	color: #000000;
	background-color: #FFFFFF;
	border: 1px #000000;
	border-style: solid
}
.fatfield
{
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: xx-small;
	background-color: #DCDEF4;
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
	color: #000066
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
	font-size: small;
	font-style: normal;
	color: #030670
}
.fdet
{
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: xx-small;
	font-style: normal;
	color: #333333
}
.finfo
{
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: xx-small;
	color: #898888
}
.ainfo
{
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: small;
	color: #333333
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
.settings
{
	border-color: #BBBBBB #BBBBBB #BBBBBB #BBBBBB;
	border-style: solid;
	border-top-width: 0px;
	border-right-width: 1px;
	border-bottom-width: 1px;
	border-left-width: 0px;
	text-align: center;
	padding-top: 0px;
	padding-bottom: 6px
}
.importnant
{
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10pt;
	font-style: normal;
	color: #000066
}
.dirheadline
{
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 9pt;
	font-style: normal;
	font-weight: bold;
	color: #000066
}
.slash
{
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10pt;
	color: #000066   
}
.importnantlink
{
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10pt;
	font-style: normal;
	color: #0000FF
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
	font-size: x-small;
	font-style: normal;
	color: #FFFFFF
}
.bboxtable
{
	background-color: #FFFFFF
}
<?php
}


class kpfrontpage
{
	function random($cnt)
	{
		global $bd, $cfg, $setctl;
		$this->query = 'SELECT id,drive,dirname,fsize,date,free,album,artist,xid,COUNT(*) as titles,SUM(lengths) as lengths, year, genre, fpath, fname FROM '.TBL_SEARCH.' WHERE trim(album) != ""'.$bd->genxdrive().' '.grpsql().' ORDER BY RAND() DESC LIMIT '.$cnt;

		user_error($this->query);

		$res = db_execquery($this->query);

		while ($row = db_fetch_assoc($res))
		{
			$f2 = new file2($row['id'], false, $row);			
			$dir = $f2->relativepath;

			//$imgurl = albumcoversmall($f2->drive, $dir);
			
			echo $imgurl;
		}
	}

	function show()
	{
		echo '<div>';
		$this->random(5);
		echo '</div>';
	}

}


class genkpalbum
{
	function genkpalbum()
	{
		global $setctl;
		$this->albumfiles = array();
		$albumsfilest = explode(',', strtoupper($setctl->get('albumfiles')));
		for ($i=0,$c=count($albumsfilest);$i<$c;$i++)
		{		
			$amatch = trim($albumsfilest[$i]);
			if (strlen($amatch) > 0) $this->albumfiles[] = $amatch;
		}
	}

	function getalbumfiles()
	{
		return $this->albumfiles;
	}
	
	function finddirimage($albumsearch)
	{
		for ($i=0,$c=count($this->albumfiles);$i<$c;$i++)
		{		
			for ($i2=0,$c2=count($albumsearch);$i2<$c2;$i2++)
			{
				if (fmatch(strtoupper(kp_basename($albumsearch[$i2][2])), $this->albumfiles[$i]))
				{
					return $albumsearch[$i2][0];
				}				
			}
		}
		return 0;
	}

	function findid3v2image($albumsearch)
	{
		for ($i=0,$c=count($albumsearch);$i<$c;$i++)
		{
			if ($albumsearch[$i][1] == 1) return $albumsearch[$i][0];
		}
		return 0;
	}
	
	function gencache()
	{
		global $cfg;
		
		$dirinfo = $filelist = array();
		
		$sql = 'SELECT artist, album, fpath, count(*) as cnt FROM '.TBL_SEARCH.' WHERE LENGTH(TRIM(artist)) > 0 AND LENGTH(TRIM(album)) > 0 GROUP BY artist, album, fpath HAVING cnt > '.$cfg['titlesperalbum'].' ORDER BY cnt DESC';
		$res = db_execquery($sql);
		while ($row = db_fetch_row($res))
		{
			$dirinfo[$row[0]][$row[1]][] = $row[2];
		}

		$sql = 'SELECT fpath, id, id3image, free FROM '.TBL_SEARCH;
		$res = db_execquery($sql);
		while ($row = db_fetch_row($res))
		{
			$filelist[$row[0]][] = array($row[1], $row[2], $row[3]);
		}
		
		db_execquery('DELETE FROM '.TBL_ALBUMCACHE);
		
		$sql = 'SELECT artist, album FROM '.TBL_SEARCH.' WHERE LENGTH(TRIM(artist)) > 0 AND LENGTH(TRIM(album)) > 0 GROUP BY artist, album';
		$res = db_execquery($sql);
		while ($row = db_fetch_row($res))
		{
			$artist = $row[0]; 
			$album = $row[1];
			
			if (isset($dirinfo[$artist][$album]))
			{
				$albumsearch = array();

				foreach($dirinfo[$artist][$album] as $dir)
				{
					if (isset($filelist[$dir]))
					{
						foreach($filelist[$dir] as $id => $data)
						{
							$albumsearch[] = array($data[0], $data[1], $data[2]);
						}
					}
				}

				if (count($albumsearch) > 0)
				{
					$sid = $this->finddirimage($albumsearch);
					$id3sid = $this->findid3v2image($albumsearch);
		
					db_execquery('INSERT INTO '.TBL_ALBUMCACHE.' SET album = "'.myescstr($album).'", artist = "'.myescstr($artist).'", id = '.$sid.', idid3 = '.$id3sid);
				
				}				
			}
		}
	}
}


class kpsqlinstall
{
	function kpsqlinstall()
	{
		global $db;
		$this->oldbuild = 0;
		$this->mysqlserverv = '';
		
		$this->dbmethod = 0;

		$this->user = '';
		$this->pass = '';

		$this->defpass = '**********';

		define('USEDBINSTALL', 1);
		define('NEWDBINSTALL', 2);
	}
	
	function check_all_tables(&$dbcount)
	{
		$kpsql = new kpmysqltable();
		
		$dbdef = $kpsql->getdbdef();
		$dbtable = $kpsql->getdbtable();
		$installdb = $kpsql->getinstallsql();
		$dbcols = $kpsql->getdbcols();
		
		$ignore = array();
		if (db_gconnect())
		{
			$sql = array();

			foreach ($dbtable AS $name => $val)  
			if (db_execquery('DESC '.$name) == false) 
			{
				$sql[] = $installdb[$val];
				if ($val == 5) $sql[] = $installdb[9];
				if ($val == 6) $sql[] = $installdb[8];
				if ($val == 11) $sql[] = $installdb[12];
				$ignore[$name] = true;
			} else 
			{
				if (UTF8MODE)
				{
					$utfconv = array();				
					
					foreach($dbdef[$name] as $rname => $arr) 
						if (isset($arr[5]) && $arr[5] == 1) $utfconv[$rname] = true;


					if (count($utfconv) > 0)
					{
						$res = db_execquery('SHOW FULL COLUMNS FROM '.$name);
						while ($row = db_fetch_assoc($res))
						{
							if (isset($row['Field']) && isset($row['Collation']) && isset($row['Type']))
							{
								if (isset($utfconv[$row['Field']]))
								{
									if ($row['Collation'] != 'utf8_general_ci')
										$sql[] = 'ALTER TABLE '.$name.' CHANGE `'.$row['Field'].'` `'.$row['Field'].'` '.$row['Type'].' CHARACTER SET utf8';
								}	
							}
						}
					}
				}
				
				$dbcount++;
			}
			
			foreach ($dbcols as $name => $val) 
			{
				if (!isset($ignore[$name]))
				{
					for ($i=0,$c=count($dbcols[$name]);$i<$c;$i++)
						if (db_execquery('SELECT `'.$dbcols[$name][$i].'` FROM '.$name.' LIMIT 1') == false) 
							$sql[] = 'ALTER TABLE '.$name.' ADD `'.$dbcols[$name][$i].'` '.$kpsql->createrowdef($dbdef[$name][$dbcols[$name][$i]]);
				}
			}	

			return $sql;
		} 
	}

	function needcheck()
	{
		global $app_build;
		$result = db_execcheck('SELECT * FROM '.TBL_KPLAYVERSION, true);
		if ($result)
		{
			$data = db_fetch_assoc($result);
			if (isset($data['app_build']))
			{
				$this->oldbuild = (int)$data['app_build'];
				if ($this->oldbuild == $app_build) return false;
			}
		} 
		return true;
	}

	function checkaccess($user, $pass, &$errmsg, &$errno)
	{
		global $db;
		$status = 0;

		$mysqli = new mysqli($db['host'], $db['user'], $db['pass'], $db['name']);
		
		if ($mysqli -> connect_errno) {
			$errno = $mysqli->connect_errno;
			$errmsg = $mysqli->connect_error;
		}
		else {
			$status = 1;
		}
		return $status;
	}

	function htmltable($title='')
	{
		?>
		<table width="750" border="0" cellspacing="0" cellpadding="0" align="center">
		<tr> 
			<td><a href="http://www.kplaylist.net" title="Visit homepage"><img width="208" height="64" src="<?php echo getimagelink('kplaylist.gif'); ?>" alt="kPlaylist" border="0"/></a></td>
		</tr>
		<tr>
			<td height="20"></td>
		</tr>
		</table>
		<?php
		if (!empty($title))
		{
			?>
			<table width="650" border="0" cellspacing="0" cellpadding="0" align="center">
			<tr> 
				<td colspan="4" class="wtext"><font size="4"><?php echo $title; ?></font></td>
			</tr>
			<tr>
				<td height="25"></td>
			</tr>
			</table>
			<?php
		}
		?>	
		<?php
	}

	function showsql()
	{
		$kpsql = new kpmysqltable();
		$installdb = $kpsql->getinstallsql();
		$installdbuser = $kpsql->getinstallsqluser('%');

		kprintheader();

		echo '<table width="600" border="0" align="center">';
		echo '<tr><td class="wtext">';
		echo '<font size="4">The installers SQL code:</font>';
		echo '</td></tr>';

		if (frm_isset('dbmethod')) 
		{
			$method = frm_get('dbmethod', 1);
			if ($method == NEWDBINSTALL) $start = 1; else $start = 2;

			for ($i=$start;$i<count($installdb);$i++) echo '<tr><td class="wtext">'.str_replace("\n", '<br/>', $installdb[$i]).';<br/></td></tr>';

			if ($method == NEWDBINSTALL)
			{
				echo '<tr><td class="wtext"><font color="green">'.$installdbuser[0].';</font></td></tr>';
				echo '<tr><td class="wtext"><font color="green">'.$installdbuser[2].';</font></td></tr>';	
			}
		}

		echo '<tr><td height="15"></td></tr>';
		echo '</table>';
		kprintend();
	}

	function show_feedback($upgrade = false)
	{
		global $app_ver, $app_build;
		?>
		<?php
			if (isset($_SERVER['REMOTE_ADDR'])) $iid = getrand(10000) + ip2long($_SERVER['REMOTE_ADDR']); else $iid = time() + getrand(10000);
			if (isset($_SERVER['SERVER_SOFTWARE'])) $os = $_SERVER['SERVER_SOFTWARE']; else $os = 'Unknown';
		?>	
		<form method="get" action="http://www.kplaylist.net/success.php">
		<input type="hidden" name="build" value="<?php echo $app_build; ?>"/>
		<input type="hidden" name="iid" value="<?php echo $iid; ?>"/>
		<input type="hidden" name="os" value="<?php echo $os; ?>"/>
		<input type="hidden" name="mysql" value="<?php echo $this->mysqlserverv; ?>"/>
		<input type="hidden" name="php" value="<?php echo phpversion(); ?>"/>
		<?php if ($upgrade)
		{
			echo '<input type="hidden" name="upgrade" value="1"/>'; 
			echo '<input type="hidden" name="upgradefrom" value="'.$this->oldbuild.'"/>'; 
		}
		?>
		<table width="100%" cellpadding="1" cellspacing="2" border="0">
			<tr>
				<td>Software</td>
				<td><input disabled="disabled" class="fatbuttom" type="text" name="os" size="45" value="<?php echo $os; ?>"/></td>
			</tr>
			<tr>
				<td>MySQL</td>
				<td><input disabled="disabled" class="fatbuttom" type="text" name="mysql" size="45" value="<?php echo $this->mysqlserverv; ?>"/></td>
			</tr>
			<tr>
				<td>PHP</td>
				<td><input disabled="disabled" class="fatbuttom" type="text" name="php" size="45" value="<?php echo phpversion(); ?>"/></td>
			</tr>
			<tr>
				<td height="2"></td>
			</tr>
			<tr>
				<td>Have a comment?</td>
				<td>
					<textarea class="fatbuttom" name="comment" cols="42" rows="3"></textarea>
				</td>
			</tr>
			<tr>
				<td height="5"></td>
			</tr>
			<tr>
				<td></td>
				<td><input class="fatbuttom" name="send" type="submit" value="Send!"/></td>
			</tr>
		</table>
		</form>
		<?php
	}

	function insterror($msg, $critical=false)
	{
		kprintheader('Error during install');
		$this->htmltable('An error occured during install!');
		?>
		<table width="650" border="0" cellspacing="0" cellpadding="0" align="center">
		<tr> 
			<td class="importnantlink"><font size="2"><?php echo $msg ?></font></td>
		</tr>
		<tr><td height="25"></td></tr>
		<tr><td height="1" bgcolor="#000000"></td></tr>			
		<?php
		if (!$critical)
		{
			?>
			<tr><td height="25"></td></tr>
			<tr>
				<td class="importnant">You can restart the installation process by opening up a new window and enter the same URL.</td>
			</tr>
			<?php
		}
		?>
		<tr><td height="15"></td></tr>
		<tr>
			<td class="importnant">
				Click <a class="importnantlink" href="http://www.kplaylist.net/index.php?install=true" target="_blank">here</a> for opening the INSTALL reference.
			</td>
		</tr>
		<tr><td height="25"></td></tr>
		</table>
		<?php
		kprintend();
	}

	function createuser($mysqli, $sqluser)
	{
		global $db;
		$userok = false;
		if (!$this->checkaccess($db['user'], $db['pass'], $err, $errno))
		{
			if ($mysqli->query($sqluser[0]))
			{
				if ($mysqli->query($sqluser[2]))
				{					
					if (!$this->checkaccess($db['user'], $db['pass'], $err, $errno))
					{
						// ok - test with 4.1.					
						$mysqli->query($sqluser[1]);
						$mysqli->query($sqluser[2]);			
					}
									
					if ($this->checkaccess($db['user'], $db['pass'], $err, $errno)) 
					{
						$userok = true;
					} else $this->insterror('The MySQL user was created successfully, but login with this user is failing. The SQL that was used: '.$sqluser[0]);								
				} else $this->insterror('Unable to update privileges. The SQL that was used: '.$sqluser[2].', MySQL response: '.$mysqli->error);
			} else $this->insterror('Unable to create the MySQL user. The SQL that was used: '.$sqluser[0].', MySQL response: '.$mysqli->_error);
		} else $userok = true;

		return $userok;
	}

	function install()
	{
		global $db;
		global $mysqli;
		db_gconnect();


		if ($mysqli) 
		{
			$this->mysqlserverv = $mysqli->get_server_info();

			$kpsql = new kpmysqltable();
			$installdb = $kpsql->getinstallsql();
			$installdbuser = $kpsql->getinstallsqluser($db['host']);
			
			$errno = 0;
			$err = '';
			$error = 0;
			$errors = '';

			if ($this->dbmethod == NEWDBINSTALL)
			{
				$dbaccess = false;
				
				if ($this->createuser($mysqli, $installdbuser))
				{				
					$result = $mysqli->query($installdb[1]);
					if ($result)
					{	
						// ok, now relogin
						mysql_close($link);
						$link = @mysqli_connect($db['host'], $db['user'], $db['pass'], true);
						if ($link) 
						{
							$dbaccess = true;
						} else $this->insterror('Could not re-connect to MySQL with new user!');
					} else $this->insterror('Unable to create database. The SQL that was used: '.$installdb[1].', MySQL response: '.mysqli_error($link));
				}
			} else $dbaccess = true;
			
			if ($dbaccess)
			{			
				if ($mysqli)
				{
					$cnt = 0;
					$sql = $this->check_all_tables($cnt);

					for ($i=0,$c=count($sql);$i<$c;$i++)
					{
						if (strlen($sql[$i]) > 0)
						{
							$result = $mysqli->query($sql[$i]);
							if (!$result) 
							{ 
								$errors .= 'Failed query: '.str_replace("\n", '<br/>', $sql[$i]).'<br/>';
								$errors .= $mysqli->error.'<br/>';
								$error = $i;
							}
						}
					}
				
					if (!$error) 
					{
						kprintheader('Installing MySQL database');
						?>
						<table width="600" border="0" align="center">
						<tr> 
						<td colspan="4" class="wtext"><font size="4"></font></td>
						</tr>
						<tr>
							<td class="dir">
							<br/>
							<h2>Installation is now completed.</h2>
								<ul>
									<li>To log in to kPlaylist, reload this page (F5) and you should be able to log in.</li>
									<li>The default login is admin with admin as the password. (Case sensitive)</li>
								</ul>				
								<br/>

								<b>Would</b> you like to send the following information about this successful installation? This would
								give the kPlaylist site valuable information about supported systems, but also to increase the motivation knowing
								that this script is used. Thank you!
								<br/><br/>

								<?php $this->show_feedback(false); ?>
								
								Remember to visit <a class="importnantlink" href="http://www.kplaylist.net" target="_blank">http://www.kplaylist.net</a> for updates and help.
							</td>
							</tr>
							</table>
							<?php
							kprintend();
					} else $this->insterror('MySQL installation may not be successful! <br/><br/>'.$errors);
				} else 
				{
					$error = true;
					$this->insterror('Could not use the database ('.$db['name'].'), does it exist? MySQL response: '.$mysqli->error);
				}				
			}
		} else $this->insterror('Could not establish connection to MySQL!');
	}

	function selectmethod()
	{
		kprintheader('Install');
		$this->htmltable('Welcome to the kPlaylist installer!');
		?>
		<form style="margin:0;padding:0" name="installform" method="post" action="<?php echo PHPSELF; ?>">
		<table width="650" border="0" align="center" cellspacing="0" cellpadding="0">		
		<tr>
			<td class="importnant">
			To install kPlaylist, you'll need a working and running copy of MySQL. kPlaylist is based on the GNU GPL license, you
			can read the license here: <a class="importnantlink" href="http://www.kplaylist.net/COPYING" target="_blank">http://www.kplaylist.net/COPYING</a>
			</td>
		</tr>
		<tr><td height="25"></td></tr>		
		<tr>
			<td class="importnant"><b>Click</b> on one of the following installation methods to continue:</td>
		</tr>
		<tr><td height="25"></td></tr>
		<tr>
			<td>
			<table width="60%" align="left" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td width="50%" align="center">
					<input type="submit" name="newdatabase" value="Create new database" style="width:150px;height:25px;" class="fatbuttom"/>
				</td>
				<td width="50%" align="center">
					<input type="submit" name="usedatabase" value="Use existing database" style="width:150px;height:25px;" class="fatbuttom"/>
				</td>
			</tr>
			</table>
			</td>
		</tr>
		<tr><td height="25"></td></tr>
		<tr>
			<td class="importnant"><b>PS!</b> If you are running kPlaylist on your own machine, the suggested method is to create a new database.</td>
		</tr>
		<tr><td height="25"></td></tr>

		<tr> 
			<td colspan="4" align="right"><font class="wtext">Need help? You'll find documentation here:</font>&nbsp;<a href="http://www.kplaylist.net" target="_blank"><font color="#0000FF">kPlaylist Homepage</font></a></td>
		</tr>  

		</table>
		</form>
		<?php
		kprintend();
	}

	function install_form($text='')
	{
		global $db, $cfg;

		kprintheader('Install');

		$err = '';
		$errno = 0;
		$btx = '';
		$this->user = $db['user'];

		if ($this->dbmethod == NEWDBINSTALL)
		{
			if ($this->checkaccess($db['user'], $db['pass'], $err, $errno) == 0) $this->user = $db['user'];
		} else
		if ($this->dbmethod == USEDBINSTALL) 
		{
			$btx = 'disabled="disabled"'; 
		} 

		$this->htmltable();
		?>

		<form name="installform" method="post" action="<?php echo PHPSELF; ?>">
		<input type="hidden" name="dbmethod" value="<?php echo $this->dbmethod; ?>"/> 
		<table width="650" border="0" align="center" class="tdborder" cellspacing="0" cellpadding="0">
		<tr>
		<td height="22" class="importnant" colspan="4">
		<?php if ($this->dbmethod == NEWDBINSTALL)
		{
		?>
			Please enter a user and a password who has access to create a new database and a user for kPlaylist. In
			most cases, the root user of MySQL should be used.
		<?php
		} else
		{
			?>
			Please open the kPlaylist file in a text editor and modify the section called $db to suit
			your database settings. Click 'Reload' when you are done.
			<?php
		}

			?>
		 <br/><br/><a href="<?php echo PHPSELF ?>?showsql=true&amp;dbmethod=<?php echo $this->dbmethod; ?>" target="_blank"><font class="importnantlink">Click here</font></a> to view what the installer is going to do. <br/><br/>Click 'Continue' when ready to install ! <br/>
		  <?php
			if ($this->user == 'root')
			{
				?><br/>Note! The root password will only be used to create
			the tables, a new user called <?php echo $db['user']; ?> will be created for the operation of kPlaylist. If you like to change the name and password for this user, please edit the script, and click Reload.<br/> 
			<?php }
			if (!empty($text)) echo '<br/>'.$text.'<br/>'; ?>
			<br/>
			</td>
		</tr>
		<tr><td height="10"></td></tr>
		
		<tr>
			<td width="30%"></td>
			<td width="30%"></td>
			<td width="20%"></td>
			<td width="20%"></td>
		</tr>

		<tr> 
			<td class="wtext">MySQL user:</td>
			<td>
			<input type="text" name="mysqluser" size="25" <?php echo $btx; ?> value="<?php echo $this->user; ?>" class="fatbuttom"/></td>
			<td colspan="2" class="wtext">default: <font color="green"><?php echo $db['user']; ?></font></td>
		</tr>
		<tr><td height="4"></td></tr>	
		<tr> 
			<td class="wtext">MySQL password:</td>
			<td><input type="password" name="mysqlpass" size="25" <?php echo $btx; ?> value="<?php echo $this->defpass; ?>" class="fatbuttom"/></td>
			<td colspan="2" class="wtext">Not shown, look in script ($db['pass'])</td>
		</tr>
		
		<?php if ($this->dbmethod == NEWDBINSTALL)
		{
		?>
		<tr><td height="8"></td></tr>
		<tr>
			<td colspan="4" class="wtext"><font color="gray">If you need to change the settings below, please edit them in the script and click Reload.</font></td>
		</tr>
		<?php
		}

		?>
		<tr><td height="8"></td></tr>	
		<tr> 
			<td class="wtext" width="121">MySQL host:</td>
			<td><input type="text" name="mysqlhost" size="25" value="<?php echo $db['host']; ?>" disabled="disabled" class="fatbuttom"/></td>
		</tr>
		<tr><td height="4"></td></tr>	
		<tr> 
			<td class="wtext">MySQL database:</td>
			<td><input type="text" name="mysqldatabase" size="25" value="<?php echo $db['name']; ?>" disabled="disabled" class="fatbuttom"/></td>
		</tr>
		<tr><td height="4"></td></tr>	
		<tr> 
			<td class="wtext">Table prepend</td>
			<td><input type="text" name="tblprepend" size="25" value="<?php echo $cfg['dbprepend']; ?>" disabled="disabled" class="fatbuttom"/></td>
		</tr>
		<tr>
			<td height="12"></td>
		</tr>
		<tr>
		  <td colspan="4">
			<input type="submit" name="tomethod" value="Back" class="fatbuttom"/>&nbsp;
			<input type="submit" name="reload" value="Reload" class="fatbuttom"/>&nbsp;
			<input type="submit" name="doinstall" value="Continue" class="fatbuttom"/>
			</td>
		</tr>
		<tr> 
			<td colspan="4" align="right"><font class="wtext">You'll find documentation here:</font>&nbsp;<a href="http://www.kplaylist.net" target="_blank"><font color="#0000FF">kPlaylist Homepage</font></a></td>
		</tr> 
		<tr>
			<td height="3"></td>
		</tr>
		</table>
		</form>
		<?php
		kprintend();
	}

	function preliminstall()
	{
		global $db;
		
		switch($this->dbmethod)
		{
			case USEDBINSTALL:
				$this->user = $db['user'];
				$this->pass = $db['pass'];
				break;
			case NEWDBINSTALL: 
				$this->user = frm_get('mysqluser');
				if (frm_get('mysqlpass') != $this->defpass) $this->pass = frm_get('mysqlpass');
						else $this->pass = $db['pass'];
				break;
		}

		$err = '';
		$errno = 0;
		if ($this->checkaccess($this->user, $this->pass, $err, $errno))
		{
			$this->install();			
		} else 
		{
			$msg = '<font color="red" size="2">Could not login with the supplied user name and password! MySQL response: '.$err.'</font>'; 
			if ($errno == 1251) $msg .= '<br/><br/><font color="red" size="2">Seems like you are running MySQL 4.1/5.0 or newer. Please go to the following location to read the solution: </font><a class="importnantlink" href="http://www.kplaylist.net/forum/viewtopic.php?p=2231" target="_blank">http://www.kplaylist.net/forum/viewtopic.php?p=2231</a>';
			$this->install_form($msg);
		}
	}

	function handler()
	{
		global $db;

		$install = $upgrade = false;

		if (DBCONNECTION)
		{
			$cnt = 0;
			$sql = $this->check_all_tables($cnt);
			if ($cnt > 0) 
			{
				if (count($sql) > 0) $upgrade = true;
					else $this->updateversion();					
			} else $install = true;
		} else $install = true;

		if ($install)
		{		
			if (frm_isset('usedatabase')) $this->dbmethod = USEDBINSTALL;
				else
			if (frm_isset('newdatabase')) $this->dbmethod = NEWDBINSTALL;

			if (frm_ok('dbmethod', 1)) $this->dbmethod = frm_get('dbmethod', 1);
			
		
			if ($this->dbmethod > 0)
			{		
				if (frm_isset('tomethod')) $action = 1;
					else			
				if (frm_isset('doinstall')) $action = 3;
					else
				if (frm_isset('showsql')) $action = 2;
					else
						$action = 4;
			} else $action = 1;
					
			switch($action)
			{
				case 1: $this->selectmethod(); break;
				case 2: $this->showsql(); break;
				case 3: $this->preliminstall(); break;
				case 4: $this->install_form(''); break;
			}
			die();
		} else
		if ($upgrade)
		{
			if (frm_isset('mysqluser')) $this->user = frm_get('mysqluser'); else $this->user = $db['user'];
			if (frm_isset('mysqlpass') && frm_get('mysqlpass') != $this->defpass) $this->pass = frm_get('mysqlpass'); else $this->pass = $db['pass'];

			if (count($sql) > 0) 
			{
				if (frm_isset('executeupgrade')) $this->doupgrade($sql);
					else $this->show_upgrade($sql);

			}
			die();
		}
	}

	function show_upgrade($sql, $error='')
	{
		global $db;
		kprintheader();
		$this->htmltable('Welcome to the kPlaylist database upgrader.');
		?>
		<table width="650" border="0" cellspacing="0" cellpadding="0" align="center">
		<tr>
			<td class="importnant">
			Due to changes in the database, we have to perform a simple database upgrade.<br/><br/> Please supply a user who has access to alter the MySQL database (usually the root user of MySQL.). You can also run the SQL calls listed below manually and reload this page.</td>
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
		<form name="upgradeform" style="margin:0;padding:0" method="post" action="<?php echo PHPSELF; ?>">
		<table width="650" border="0" cellspacing="0" cellpadding="0" align="center">
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
			<input type="text" name="mysqluser" size="25" value="<?php echo $this->user; ?>" class="fatbuttom"/>
			</td>
		</tr>
		<tr> 
			<td height="22" class="wtext" width="121">MySQL password:</td>
			<td height="22" width="221" align="left"> 
			<input type="password" name="mysqlpass" size="25" value="<?php echo $this->defpass; ?>" class="fatbuttom"/>
			</td>
		</tr>
		<tr><td height="10"></td></tr>
		<tr>
			<td colspan="2" class="wtext"><input type="submit" class="fatbuttom" name="executeupgrade" value="Upgrade"/></td>
		</tr>
		<tr><td height="10"></td></tr>
		</table>
		</form>
		<?php
		kprintend();
	}

	function doupgrade($sql)
	{
		global $db;
		
		$error = '';
		
		$link = @mysqli_connect($db['host'], $this->user, $this->pass, true);
		if ($link)
		{
			$this->mysqlserverv = mysql_get_server_info($link);			

			if (mysqli_select_db($db['name'], $link))
			{
				for ($i=0,$c=count($sql);$i<$c;$i++)
				{
					if (strlen($sql[$i]) > 0)
					{
						if (!$mysqli->query($sql[$i], $link))
						{
							$error = 'Could not execute: '.$sql[$i].'<br/>MySQL response: '.mysqli_error($link).'<br/>';
							break;
						}
					}
				}
			} else $error = 'Could not select the database name';
		} else $error = 'Could not connect. Please check that the username or password is correct.';

		if (strlen($error) == 0) $this->upgrade_ok(); else $this->show_upgrade($sql, $error);
	}

	function updateversion()
	{
		global $app_build, $app_ver, $setctl;
		$sql = 'UPDATE '.TBL_KPLAYVERSION.' SET app_build = "'.$app_build.'", app_ver = "'.$app_ver.'"';
		if (UTF8MODE) $setctl->set('utf8mode', 1);

		if ($this->oldbuild != 0 && $this->oldbuild < 444) $setctl->set('reupdate', 1);

		db_execcheck($sql);
	}

	function upgrade_ok()
	{
		global $setctl;
		$setctl->load();
		kprintheader();
		$this->htmltable('kPlaylist database upgraded!');
		?>
		<table width="650" border="0" cellspacing="0" cellpadding="0" align="center">
		<tr>
			<td class="importnant">Upgrading performed successfully. Enjoy your new version of kPlaylist!<br/><br/> 
			Reload (F5) this page to get started.<br/><br/>
			
			<b>Would</b> you like to send the following information about this successful upgrade? This would
			give the kPlaylist site valuable information about supported systems, but also to increase the motivation knowing
			that this script actually is used. Thank you!
			<br/><br/>

			<?php $this->show_feedback(true); ?>
			
			</td>
		</tr>
		</table>
		<?php
		kprintend();
	}
}

$kpinst = new kpsqlinstall();

if (!DBCONNECTION || $kpinst->needcheck() || (DBCONNECTION && UTF8MODE && !$setctl->get('utf8mode'))) $kpinst->handler();


class playlistupload
{
	function decide()
	{
		$plid = frm_get('plid', 1, 0);
		
		$tag = 'streamsid';
		$ids = array();
		$msg = '';
		
		if ($plid > 0)
		{
			$kppl = new kp_playlist($plid);
			if ($kppl->isloaded() && $kppl->appendaccess())
			{
				if (isset($_FILES['fileupload']) && is_array($_FILES['fileupload']))
				{
					foreach($_FILES['fileupload']['name'] as $id => $name)
					{
						if (strlen($name) > 0 && isset($_FILES['fileupload']['tmp_name'][$id]) && $_FILES['fileupload']['size'][$id] > 0)
						{
							$tmpn = $_FILES['fileupload']['tmp_name'][$id];

							$fp = fopen($tmpn, 'rb');
							if ($fp)
							{
								$data = fread($fp, filesize($tmpn));
			
								$spos = 0;
								while (true)
								{
									$pos = strpos($data, $tag, $spos);
									if ($pos !== false)
									{
										$id = '';
										$ipos = $pos + strlen($tag) + 1;
										while (true && $ipos < strlen($data))
										{
											if (is_numeric($data[$ipos])) 
											{
												$id .= $data[$ipos];
												$ipos++;
											} else break;
										}
			
										if (is_numeric($id)) $ids[] = $id;

										$spos = $pos + strlen($tag);
									} else break;
								}

								if (count($ids) > 0) 
								{
									for ($i=0,$c=count($ids);$i<$c;$i++)
									{
										$r = get_searchrow($ids[$i]);
										if (!is_array($r)) unset($ids[$i]);
									}

									if (count($ids) > 0) 
									{
										$kppl->addtoplaylist($ids);										
									}
									
									$msg = get_lang(374, count($ids));
								} else $msg = get_lang(373); 

							} else $msg = get_lang(372);
						} else $msg = get_lang(372);
					}			
				}
			}
		}
		$this->view($msg, $plid);
	}
	
	function view($msg='', $plid)
	{
		global $cfg;
		kprintheader(get_lang(234));
		?>
		<form method="post" name="playlistupload" enctype="multipart/form-data" action="<?php echo PHPSELF; ?>">
		<input type="hidden" name="action" value="playlistupload"/>
		<input type="hidden" name="plid" value="<?php echo $plid; ?>"/>
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td height="10" class="notice"><?php echo get_lang(375); ?></td>
		</tr>
		<tr>
			<td align="center" class="notice"><?php echo get_lang(308, min(ini_get('upload_max_filesize'), ini_get('post_max_size'))); ?></td>
		</tr>
		<?php 
			if (strlen($msg) > 0) echo '<tr><td class="notice" colspan="2">'.$msg.'</td></tr><tr><td height="5"></td></tr>';	
		?>
	
		<tr> 
			<td colspan="2" align="center" class="notice"> 
			<input type="file" name="fileupload[]" class="fatbuttom" size="60"/>
			</td>
		</tr>
	
		<tr>
			<td colspan="2" height="5"></td>
		</tr>
		<tr>
			<td align="center" colspan="2">
				<input type="button" name="sbutton" onclick="javascript: document.playlistupload.sbutton.disabled = true; document.playlistupload.sbutton.value = '<?php echo get_lang(253); ?>'; document.playlistupload.submit();" value="<?php echo get_lang(234); ?>" class="fatbuttom"/>&nbsp; 
				<input type="button" name="Close" value="<?php echo get_lang(27); ?>" onclick="javascript: window.close(); chopener(window.opener.location, '?action=playlist&amp;sel_playlist=<?php echo $plid; ?>&amp;editplaylist=true');" class="fatbuttom"/>
			</td>
		</tr>
		</table>
		</form>
		<?php
		kprintend();
	}
}

class kp_playlist
{
	function kp_playlist($listid=-1)
	{
		$this->listid = -1;
		$this->name = '';
		$this->status = 0;
		$this->uid = -1;
		$this->public = 0;
		$this->loaded = false;

		if ($listid != -1) $this->load($listid);
	}

	function getuid()
	{
		return $this->uid;
	}

	function getstatus()
	{
		return $this->status;
	}

	function setstatus($status)
	{
		$this->status = $status;
	}

	function setname($name)
	{
		$this->name = $name;
	}

	function getname()
	{
		return $this->name;
	}

	function isloaded()
	{
		return $this->loaded;
	}
	
	function update()
	{
		if ($this->loaded) db_execquery('UPDATE '.TBL_PLAYLIST.' SET name = "'.myescstr($this->name).'", public = '.$this->public.', status = '.$this->status.' WHERE listid = '.$this->listid);
	}

	function setpublic($public)
	{
		$this->public = $public;
	}

	function getpublic()
	{
		return $this->public;
	}

	function createnew($u_id, $name, $public)
	{
		$res = db_execquery('INSERT INTO '.TBL_PLAYLIST.' SET name = "'.myescstr($name).'", u_id = '.$u_id.', public = '.$public);
		if ($res) return $this->load(db_insert_id());
	}

	function load($listid)
	{
		$res = db_execquery('SELECT * FROM '.TBL_PLAYLIST.' WHERE listid = '.$listid);
		if ($res && db_num_rows($res) > 0)
		{
			$row = db_fetch_assoc($res);
			$this->listid = $listid;
			$this->public = $row['public'];
			$this->name = $row['name'];
			$this->status = $row['status'];
			$this->uid = $row['u_id'];
			$this->loaded = true;
			return true;
		}
		return false;
	}

	function anyaccess()
	{
		global $u_id, $valuser;
		if ($this->public > 0) return true;
		if ($u_id == $this->uid) return true;
		if ($valuser->isadmin()) return true;
		return false;
	}
	
	function soleaccess()
	{
		global $u_id, $valuser;
		if ($u_id == $this->uid || $valuser->isadmin()) return true;
		return false;
	}

	function writeaccess()
	{
		global $u_id, $valuser;
		if ($u_id == $this->uid || $this->public == 3 || $valuser->isadmin()) return true;
		return false;
	}

	function appendaccess()
	{
		global $u_id, $valuser;
		if ($u_id == $this->uid || $this->public == 2 || $this->public == 3 || $valuser->isadmin()) return true;
		return false;
	}

	function getres($sql='sid')
	{
		return db_execquery('SELECT '.$sql.' FROM '.TBL_PLAYLIST_LIST.' WHERE listid = '.$this->listid.' ORDER BY seq ASC');
	}

	function addtoplaylist($sids)
	{
		$result = db_execquery('SELECT * FROM '.TBL_PLAYLIST_LIST.' WHERE listid = '.$this->listid);	
		$row = db_num_rows($result);
		$cntr=$row;
		$cntr++;

		if (count($sids) > 0)
		{
			for ($i=0,$c=count($sids);$i<$c;$i++)
			{			
				db_execquery('INSERT INTO '.TBL_PLAYLIST_LIST.' (listid, sid, seq) VALUES ('.$this->listid.', '.$sids[$i].', '. $cntr.')');
				$cntr++;
			}		
		}
	}

	function play()
	{
		if ($this->listid >= 0)
		{
			$result = $this->getres();
			if ($result && db_num_rows($result) > 0)
			{
				$tunes = array();
				$i=0;
				while ($row = db_fetch_row($result)) $tunes[$i++] = $row[0];
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
				$m3ug = new m3ugenerator();
				for ($i=0;$i<$cnt;$i++) $m3ug->sendlink2($tunes[$i]);
				$m3ug->start();
				return true;
			}
		}
		return false;
	}

	function remove()
	{
		if ($this->loaded)
		{
			db_execquery('DELETE FROM '.TBL_PLAYLIST_LIST.' WHERE listid = '.$this->listid);
			db_execquery('DELETE FROM '.TBL_PLAYLIST.' WHERE listid = '.$this->listid);
			return true;
		}
		return false;
	}

	function selectaccess()
	{
		$out = '';
		$options = array(0 => 10, 1 => 42, 2 => 367, 3 => 368);
		foreach($options as $id => $langid)
		{
			$out .= '<option value="'.$id.'"';
			if ($this->public == $id) $out .= ' selected="selected"';
			$out .= '>'.get_lang($langid).'</option>';
		}
		return $out;
	}

	function sortoriginal()
	{
		$result = db_execquery('SELECT id from '.TBL_PLAYLIST_LIST.' WHERE listid = '.$this->listid.' ORDER BY ID ASC');	
		$seq = 1;
		while ($row = db_fetch_row($result))
		{
			$id = $row[0];
			db_execquery('UPDATE '.TBL_PLAYLIST_LIST.' SET seq = '.$seq.' WHERE id = '.$id, true);
			$seq++;
		}
	}

	function sortalphabetic()
	{
		$result = db_execquery('SELECT pl.id AS id FROM '.TBL_PLAYLIST_LIST.' pl, '.TBL_SEARCH.' s WHERE pl.listid = '.$this->listid.' AND pl.sid = s.id ORDER BY s.free ASC');		
		$seq = 1;
		while ($row = db_fetch_row($result))
		{
			db_execquery('UPDATE '.TBL_PLAYLIST_LIST.' SET seq = '.$seq.' WHERE id = '.$row[0], true);
			$seq++;
		}
	}

	function sortrandom()
	{
		$result = db_execquery('SELECT id from '.TBL_PLAYLIST_LIST.' WHERE listid = '.$this->listid.' ORDER BY ID ASC');		
		srand(make_seed());
		while ($row = db_fetch_row($result))
			db_execquery('UPDATE '.TBL_PLAYLIST_LIST.' SET seq = '.getrand().' WHERE id = '.$row[0], true);	
		$this->rewriteseq();
	}

	function rewriteseq()
	{
		$result = db_execquery('SELECT * FROM '.TBL_PLAYLIST_LIST.' WHERE listid = '.$this->listid.' ORDER BY seq ASC');
		if (db_num_rows($result) > 0)
		{
			$cntr=1;
			while ($row = db_fetch_assoc($result))
			{
				db_execquery('UPDATE '.TBL_PLAYLIST_LIST.' SET seq = '.$cntr.' WHERE id = '.$row['id'], true);
				$cntr++;
			}
		}
	}

	function savesequence($sequencelist)
	{
		$result = db_execquery('SELECT id FROM '.TBL_PLAYLIST_LIST.' WHERE listid = '.$this->listid.' ORDER BY seq ASC');
		$data = array();
		$cnt=0;
		while ($row = db_fetch_assoc($result))
		{
			$data['id'][$cnt] = $row['id'];
			$data['seq'][$cnt] = (int)$sequencelist[$cnt];
			$cnt++;
		}
		if ($cnt > 0)
		{
			for ($i=0;$i<$cnt;$i++) db_execquery('UPDATE '.TBL_PLAYLIST_LIST.' SET seq = '.$data['seq'][$i].' WHERE id = '.$data['id'][$i]);
			$this->rewriteseq();
		}
	}

	function removeduplicates()
	{
		$result = db_execquery('SELECT sid,id FROM '.TBL_PLAYLIST_LIST.' WHERE listid = '.$this->listid.' ORDER BY ID ASC');	
		$sids = array();
		while ($row = db_fetch_row($result))
		{
			$sid = $row[0];
			$id = $row[1];
			if (isset($sids[$sid])) db_execquery('DELETE FROM '.TBL_PLAYLIST_LIST.' WHERE id = '.$id, true);
			$sids[$sid] = true;
		}
		$this->rewriteseq();
	}

	function removeentry($id)
	{
		db_execquery('DELETE FROM '.TBL_PLAYLIST_LIST.' WHERE id = '.$id.' AND listid = '.$this->listid);
	}	
}

function pl_shared($width)
{
	global $runinit, $u_id;
	$out = '';
	$res = db_execquery('SELECT name, listid FROM '.TBL_PLAYLIST.' WHERE public = 1 AND u_id != '.$u_id.' ORDER by name ASC');	
	if (db_num_rows($res) > 0)
	{
		$out .= '<input type="hidden" name="action" value="playlist"/>';
		$out .= '<input type="hidden" name="previous" value="'.$runinit['pdir64'].'"/>&nbsp;';
		$options = array();
		while ($row = db_fetch_assoc($res)) $options[] = array($row['listid'], $row['name']);
		$out .= genselect('sel_shplaylist', $options, db_guinfo('defshplaylist'), false, 'file', $width, 'sel_shplaylist');
		$out .= '&nbsp; ';

		if (WINDOWPLAYER)
		{
			$kpwjs = new kpwinjs();
			$out .= '<input type="button" value="'.get_lang(70).'" onclick="javascript: '.$kpwjs->sharedplaylist().'" class="fatbuttom"/> ';
		} else $out .= '<input type="submit" name="playplaylist" value="'.get_lang(70).'" class="fatbuttom"/> ';

		$out .= '<input type="submit" name="viewplaylist" value="'.get_lang(85).'" class="fatbuttom"/>';
	}
	return $out;
}

function playlist_editor($plid, $prev, $sort = 0)
{
	global $runinit, $cfg;
	
	$kpd = new kpdesign();
	$kpd->top(false, get_lang(59));

	$radiocolor = array(0 => '#7FFFD4', 1 => '#FFBF00');
	$radioseq = array();
	
	$kppl = new kp_playlist($plid);

	if ($kppl->isloaded() && $kppl->anyaccess())
	{
		if ($kppl->soleaccess()) $access = 1;
			else
		if ($kppl->writeaccess()) $access = 2;
			else $access = 3;
		
		$scrolly = frm_get('scrolly', 1, 0);
		
		if (ALLOWDOWNLOAD && db_guinfo('u_allowdownload') && $cfg['archivemode'] && db_guinfo('allowarchive')) $dlbutton = true; else $dlbutton = false;

		$result = db_execquery('SELECT * FROM '.TBL_PLAYLIST_LIST.' WHERE listid = '.$plid.' ORDER BY seq ASC');
		if ($result) $cnt = db_num_rows($result); else $cnt = 0;

		if (UNAUTHORIZEDSTREAMS) $extra = '<a class="bbox" href="'.PHPSELF.'?streamplaylist='.$plid.'&amp;extm3u=true">i</a> &nbsp;'; else $extra = '';

		echo blackboxpart(get_lang(46, $kppl->getname(), $cnt), 1, $extra);

		if (WINDOWPLAYER)
		{
			$kpwjs = new kpwinjs();
			$playcode = '<input type="button" value="'.get_lang(42).'" class="fatbuttom" onclick="javascript: '.$kpwjs->playlist($plid).'"/>';
		} else $playcode = '<input type="submit" name="playplaylist" value="'.get_lang(42).'" class="fatbuttom"/>';		

		?>
		<form style="margin:0;padding:0" action="<?php echo PHPSELF; ?>" method="post">
		<input type="hidden" name="action" value="playlisteditor"/>
		<input type="hidden" name="sel_playlist" value="<?php echo $plid; ?>"/>
		<input type="hidden" name="previous" value="<?php echo $prev; ?>"/>
		<input type="hidden" name="drive" value="<?php echo $runinit['drive']; ?>"/>

		<table width="700" cellspacing="2" border="0" cellpadding="2">
		<tr class="wtext">
			<td><?php echo get_lang(92); ?></td>
			<td><?php echo get_lang(54); ?></td>
			<td><?php echo get_lang(44); ?></td>
			<td><?php echo get_lang(125); ?></td>
			<td></td>
		</tr>
		<tr>
			<td>				
				<?php
				echo '<input type="button" value="'.get_lang(34).'" class="fatbuttom" onclick="javascript: chhttp(\''.PHPSELF.'?pwd='.$prev.'&amp;d='.$runinit['drive'].'\');"/>'.'&nbsp; ';
				echo $playcode.'&nbsp; ';
				if ($access == 1) echo '<input type="submit" name="deleteplaylist" onclick="javascript: if (!confirm(\''.get_lang(169).'\')) return false;" value="'.get_lang(43).'" class="fatbuttom"/>'.'&nbsp; ';
				if ($dlbutton) echo '<input type="button" name="pdlall" value="'.get_lang(117).'" onclick="javascript: newwin(\'dlplaylist\', \''.PHPSELF.'?action=dlplaylist&amp;pid='.$plid.'\', 130, 450);" class="fatbuttom"/>';
			?>
			</td>
			<?php

			echo '<td>';			
			if ($access == 1) echo '<input type="text" name="playlistname" value="'.checkchs($kppl->getname()).'" size="25" class="fatbuttom"/>';
					else echo checkchs($kppl->getname());
			echo '</td><td>';
			if ($access == 1) echo '<select name="public" class="fatbuttom" style="width:100px">'.$kppl->selectaccess().'</select>';
					else echo '<select name="public" class="fatbuttom" disabled="disabled" style="width:100px">'.$kppl->selectaccess().'</select>';
			echo '</td><td>';
			if ($access == 1) echo '<input type="checkbox" name="shuffle" value="1" '.checked($kppl->getstatus()).'/>';
					else echo '<input disabled="disabled" type="checkbox" name="shuffle" value="1" '.checked($kppl->getstatus()).'/>';

			echo '</td><td>';
			if ($access == 1) echo '<input type="submit" class="fatbuttom" name="saveplaylist" value="'.get_lang(45).'"/>';
			echo '&nbsp; <input type="submit" class="fatbuttom" name="refresh" value="'.get_lang(107).'"/>';
			
			?>
			</td>
		</tr>
		<tr>
			<td colspan="2">
			<?php
			if ($access == 1 || $access == 2)
			{
				echo '<input type="button" name="upload" onclick="'.jswin('upload', '?action=playlistupload&amp;plid='.$plid, 220, 520).'" value="'.get_lang(234).'" class="fatbuttom"/>&nbsp; ';
				
				
				echo '<select name="sort" class="fatbuttom">';
				$sorts = array(0 => get_lang(170), 1 => get_lang(171), 2 => get_lang(173), 3 => get_lang(180));
				echo selectoptions($sorts, $sort);	
				echo '</select> &nbsp;';
				echo '<input type="submit" name="sortplaylist" value="'.get_lang(172).'" class="fatbuttom"/>';				
			}
			?>
			</td>
		</tr>
		<?php
			$min = time() - 1800;
			$res = db_execquery('SELECT * FROM '.TBL_ICERADIO.' WHERE lactive > '.$min.' AND playlistid = '.$plid);
			if (db_num_rows($res) > 0 && ($access == 1 || $access == 2))
			{
				echo '<tr><td>'.get_lang(369).'</td></tr>';
				echo '<tr><td colspan="5">';

				$colorc = 0;
				while ($row = db_fetch_assoc($res))
				{
					$kpr = new kpradio($row['stationid']);
					if ($kpr)
					{
						if (!isset($radiocolor[$colorc])) $radiocolor[] = '#CCCCCC';
						if (!isset($radioseq[$kpr->getcurseq()])) $radioseq[$kpr->getcurseq()] = $radiocolor[$colorc];
						echo '<input class="fatbuttom" title="'.$kpr->getname().'" style="background-color:'.$radiocolor[$colorc].'" size="6" type="text" name="nextradioseq_'.$row['stationid'].'" value="'.lzero($kpr->getnextseq()).'"/> &nbsp;';
						$colorc++;
					}					
				}

				echo ' &nbsp;<input type="submit" class="fatbuttom" name="saveradiosequence" value="'.get_lang(45).'"/>';
				echo '</td></tr>';
			}
		?>
		</table>
		</form>
		<?php
		
		echo blackboxpart(get_lang(46),2);		

		echo '<br/>';
		
		echo '<form onsubmit="javascript: savescrolly();" style="margin:0;padding:0" name="psongs" action="'.PHPSELF.'" method="post">';
		echo '<input type="hidden" name="previous" value="'.$prev.'"/>';
		echo '<input type="hidden" name="drive" value="'.$runinit['drive'].'"/>';
		echo '<input type="hidden" name="sort" value="0"/>';
		echo '<input type="hidden" name="sel_playlist" value="'.$plid.'"/>';
		echo '<input type="hidden" id="scrolly" name="scrolly" value="0"/>';
		echo '<input type="hidden" name="action" value="playlisteditor"/>';
		
		if ($access == 1 || $access == 2) echo blackboxpart(get_lang(47),1); else echo blackboxpart(get_lang(48),1);
		
		if ($cnt > 0)
		{
			echo '<table width="700" cellspacing="0" border="0" cellpadding="0">';
			?>
			<tr>
				<td width="70" class="wtext"><?php echo get_lang(49); ?></td>
				<td width="70" class="wtext"><?php echo get_lang(50); ?></td>
				<td width="110" class="wtext"><?php echo get_lang(52); ?></td>
				<td width="95" class="wtext"><?php if ($access == 1 || $access == 2) echo get_lang(53); ?></td>
				<td width="355" class="wtext"><?php echo get_lang(141); ?></td>
			</tr>
			<?php
			
			echo '<tr><td height="8"></td></tr>';
			echo '<tr bgcolor="#BCBCBC"><td colspan="5" height="1"></td></tr>';
			echo '<tr><td height="6"></td></tr>';
			$totalsec = $count = $countfails = 0;
			
			while ($row = db_fetch_assoc($result))
			{
				$count++;			
				$id = $row['id'];

				$f2 = new file2($row['sid'], true);
				if ($f2)
				{
					$id3 = $f2->getid3();
					if (is_numeric($id3['lengths'])) $totalsec += $id3['lengths'];

					$seq = (int)$row['seq'];

					if (isset($radioseq[$seq])) echo '<tr bgcolor="'.$radioseq[$seq].'">';
						else
					if (($count % 2) == 0) echo '<tr class="row2nd">'; else echo '<tr>';					
					
					echo '<td align="center">';
					echo '<input type="checkbox" class="wtext" name="selected[]" value="'.$id.'"/>';
					echo '</td>';
				
					echo '<td>';
					if ($access == 1 || $access == 2) echo '<input class="smalltext" type="text" name="seq[]" value="'.lzero($seq).'" size="4"/>'; 
						else
					echo lzero($row['seq']);
					echo '</td>';
					
					echo '<td>';
					if (is_numeric($id3['bitrate']) && $id3['bitrate'] != 0 && strlen($id3['length']) != 0) echo $id3['bitrate'].'kb - '.$id3['length'];				
					echo '</td>';

					echo '<td>';
					if ($access == 1 || $access == 2) 
						echo '<input title="'.get_lang(60).'" class="fatbuttom" type="submit" name="singledel_'.$id.'" value="'.get_lang(43).'"/>';
					echo '</td>';

					echo '<td><a '.$f2->mkalink().'>'.checkchs($f2->gentitle(array('title', 'artist'), 60)).'</a></td>'; 										
					echo '</tr>';
				}
			} 

			echo '<tr><td height="8"></td></tr>';
			echo '<tr bgcolor="#BCBCBC"><td colspan="5" height="1"></td></tr>';
			echo '<tr><td height="6"></td></tr>';
			
			$secs = $totalsec;
			$days = floor($secs/86400);
			$secs = $secs % 86400;
			$hours = floor($secs/3600);
			$secs = $secs % 3600;
			$min = floor($secs/60);
			$secs = $secs % 60;

			$totshow = get_lang(187, $days, $hours, $min, $secs);

			echo '<tr><td colspan="2" class="wtext" align="center"><b>'.get_lang(55).'</b></td><td>'.$totshow.'</td></tr>';
			echo '<tr><td height="12"></td></tr>';
			
			echo '<tr><td align="left" class="file" colspan="5">';
		
			echo '&nbsp;&nbsp;'.get_lang(73).'&nbsp;&nbsp;<input type="button" value="+" class="fatbuttom" onclick="javascript: selectall();"/>&nbsp;&nbsp;';
			echo  '<input type="button" value="-" class="fatbuttom" onclick="javascript: disselectall();"/>&nbsp;&nbsp;';
			echo  get_lang(57).'&nbsp;&nbsp;<input type="submit" class="fatbuttom" onclick="javascript: if (!anyselected()) { alert(\''.get_lang(159).'\'); return false; }" name="playselected" value="'.get_lang(42).'"/>&nbsp;&nbsp;';

			if ($access == 1 || $access == 2) 
			{
				echo '<input type="submit" class="fatbuttom" onclick="javascript: if (!anyselected()) { alert(\''.get_lang(159).'\'); return false; } else if (!confirm(\''.get_lang(210).'\')) return false;" name="delselected" value="'.get_lang(43).'"/>&nbsp;&nbsp;';
				echo get_lang(58).'&nbsp;&nbsp;<input type="submit" class="fatbuttom" name="saveseq" value="'.get_lang(45).'"/>';
			}
			echo '</td></tr>';

			echo '<tr><td height="12"></td></tr>';
			echo '</table>';
		} else echo get_lang(302);
		if ($access == 1 || $access == 2) echo blackboxpart(get_lang(47),2); else echo blackboxpart(get_lang(48),2);

		echo '</form>';

		?>
		<script type="text/javascript">
		<!--
			window.scrollTo(0, <?php echo $scrolly; ?>);
		-->
		</script>
		<?php	
	}
	$kpd->bottom();
}

function playlist_new()
{
	kprintheader(get_lang(61));
	$kpl = new kp_playlist();
	
	?>
	<form method="post" action="<?php echo PHPSELF; ?>">
	<input type="hidden" name="action" value="playlist_newsave"/>
	<table width="100%" border="0" cellpadding="2" cellspacing="0">
	<tr> 
		<td class="wtext"><?php echo get_lang(62); ?></td>
		<td class="wtext"><input type="text" size="30" name="name" class="wtext"/></td>
	</tr>
	<tr> 
		<td class="wtext"><?php echo get_lang(44); ?></td>
		<td class="wtext">
			<select name="public" style="width:100px" class="fatbuttom">
				<?php echo $kpl->selectaccess(); ?>
			</select>
		</td>
	</tr>
	<tr> 
		<td></td>
		<td>
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
	
	$result = db_execquery('SELECT u_id, name, listid FROM '.TBL_PLAYLIST.' WHERE u_id = '.$u_id.' OR (public = 2 OR public = 3) ORDER by name ASC');
	$playlists = array();
	if ($result !== false) while ($row = db_fetch_assoc($result)) $playlists[] = array($row['name'], $row['listid']);
	return $playlists;
}


class kpradio
{
	function kpradio($stationid = 0)
	{
		$this->stationid = $stationid;
		$this->name = '';
		$this->playlistid = 0;
		$this->pass = '';
		$this->loop = 0;
		$this->curseq = 0;
		$this->nextseq = 0;
		$this->loaded = false;
		$this->reload = 0;
		if ($this->stationid != 0) $this->load($this->stationid);
	}

	function isloaded()
	{
		return $this->loaded;
	}

	function getname()
	{
		return $this->name;
	}

	function selectstations()
	{
		$res = db_execquery('SELECT stationid, name FROM '.TBL_ICERADIO);
		$sels = array();
		while ($row = db_fetch_assoc($res)) $sels[] = array($row['stationid'], $row['stationid'].': '.$row['name']);
		if (count($sels) > 0) return genselect('stationid', $sels, db_guinfo('defstationid'));
	}

	function updateseq($seq)
	{
		db_execquery('UPDATE '.TBL_ICERADIO.' SET curseq = '.$seq.', nextseq = 0, lactive = '.time().' WHERE stationid = '.$this->stationid);
		$this->curseq = $seq;
	}

	function validpass($pass)
	{
		if ($this->loaded && $pass == $this->pass) return true;
		return false;
	}
	
	function load($id)
	{
		$this->stationid = $id;
		$res = db_execquery('SELECT * FROM '.TBL_ICERADIO.' WHERE stationid = '.$id);
		if (db_num_rows($res) > 0)
		{
			$row = db_fetch_assoc($res);
			$this->name = $row['name'];
			$this->playlistid = $row['playlistid'];
			$this->pass = $row['pass'];
			$this->loop = $row['loop'];
			$this->curseq = $row['curseq'];
			$this->nextseq = $row['nextseq'];
			$this->loaded = true;
		}
	}

	function getcurseq()
	{
		return $this->curseq;
	}

	function getnextseq()
	{
		if ($this->nextseq == 0) return $this->curseq + 1;
		return $this->nextseq;
	}

	function setnextseq($nextseq)
	{
		if ($nextseq != $this->getnextseq()) $this->nextseq = $nextseq;
	}

	function getlist()
	{
		$res = db_execquery('SELECT name, listid FROM '.TBL_PLAYLIST.' WHERE (public = 1 OR public = 2 OR public = 3)');
		$sels = array();
		while ($row = db_fetch_assoc($res)) $sels[] = array($row['listid'], $row['name']);
		if (count($sels) > 0) return genselect('playlistid', $sels, $this->playlistid);
	}

	function fromPost()
	{
		$this->name = frm_get('name');
		$this->playlistid = frm_get('playlistid', 1, 0);
		$this->pass = frm_get('pass');
		$this->loop = frm_get('loop');
		$this->reload = frm_get('reload');
	}

	function isok()
	{
		if (!empty($this->name) && !empty($this->pass)) return true;
	}

	function update()
	{
		db_execquery('UPDATE '.TBL_ICERADIO.' SET name = "'.myescstr($this->name).'", nextseq = '.$this->nextseq.', pass = "'.myescstr($this->pass).'", playlistid = '.myescstr($this->playlistid).', `loop` = '.verchar($this->loop).' WHERE stationid = '.$this->stationid);
		$this->reload = 1;
	}

	function store()
	{
		db_execquery('INSERT INTO '.TBL_ICERADIO.' SET name = "'.myescstr($this->name).'", pass = "'.myescstr($this->pass).'", playlistid = '.myescstr($this->playlistid).', `loop` = '.verchar($this->loop));
		$this->stationid = db_insert_id();
		$this->reload = 1;
	}

	function remove()
	{
		db_execquery('DELETE FROM '.TBL_ICERADIO.' WHERE stationid = '.$this->stationid);
	}

	function edit($message='')
	{
		kprintheader('');
		?>
		<form name="randomizer" method="post" action="<?php echo PHPSELF; ?>">
		<input type="hidden" name="action" value="radio_save"/>
		<input type="hidden" name="stationid" value="<?php echo $this->stationid; ?>"/>
		<input type="hidden" name="reload" value="<?php $this->reload; ?>"/>
		<table width="95%" align="center" border="0" cellspacing="2" cellpadding="0">
		<tr>
			<td class="importnant" colspan="2"><?php echo $message; ?></td>
		</tr>
		<tr>
			<td class="wtext"><?php echo get_lang(54); ?></td>
			<td><input type="text" class="fatbuttom" size="30" maxlength="64" name="name" value="<?php echo checkchs($this->name); ?>"/></td>
			<td valign="top" class="wtext"><?php echo helplink('radioname'); ?></td>
		</tr>
		
		<tr>
			<td class="wtext"><?php echo get_lang(214); ?></td>
			<td><?php echo $this->getlist(); ?></td>
			<td valign="top" class="wtext"><?php echo helplink('radioplaylist'); ?></td>
		</tr>

		<tr>
			<td class="wtext"><?php echo get_lang(100); ?></td>
			<td><input type="text" class="fatbuttom" size="15" maxlength="64" name="pass" value="<?php echo $this->pass; ?>"/></td>
			<td valign="top" class="wtext"><?php echo helplink('radiopass'); ?></td>
		</tr>

		<tr>
			<td class="wtext"><?php echo get_lang(344); ?></td>
			<td><input type="checkbox" name="loop" value="1" <?php echo checked($this->loop); ?> class="fatbuttom"/></td>
			<td valign="top" class="wtext"><?php echo helplink('radioloop'); ?></td>
		</tr>

		<tr>
			<td height="2"></td>
		</tr>

		<tr>
			<td></td>
			<td>
				<input class="fatbuttom" type="submit" name="save" value="<?php echo get_lang(45); ?>"/>
				<input type="button" class="fatbuttom" name="close" value="<?php echo get_lang(27); ?>" onclick="javascript: window.close(); <?php if ($this->reload) echo 'window.opener.location.reload();'; ?>"/>
			</td>
			<td class="wtext" align="right"><?php echo get_lang(191); ?>&nbsp;</td>
		</tr>

		<tr>
			<td colspan="3" align="right">
				<?php 
					if ($this->stationid != 0) { ?><input type="submit" name="deleteradio" onclick="javascript: if (!confirm('<?php echo get_lang(210); ?>')) return false;"  value="<?php echo get_lang(43); ?>" class="fatbuttom"/>
					<?php } ?>
			</td>
		</tr>

		</table>
		</form>
		<?php
		kprintend();
	}

	function getnext($first=true)
	{
		if ($this->loaded)
		{
			$replyid = 0;
			if ($this->nextseq != 0) 
			$res = db_execquery('SELECT sid, seq FROM '.TBL_PLAYLIST_LIST.' WHERE seq = '.$this->nextseq.' AND listid = '.$this->playlistid);
				else
			$res = db_execquery('SELECT sid, seq FROM '.TBL_PLAYLIST_LIST.' WHERE seq > '.$this->curseq.' AND listid = '.$this->playlistid.' ORDER BY seq ASC');

			if (db_num_rows($res) > 0)
			{
				while ($replyid == 0 && $row = db_fetch_row($res))
				{					
					if (isset($row[0]))
					{
						$f2 = new file2($row[0], true);
						$fd = new filedesc($f2->fname);
						if ($fd->found && $fd->m3u) $replyid = $row[0];
						$this->curseq = $row[1];
					}
					$this->updateseq($this->curseq);
				}
				if ($replyid != 0) return $replyid;
			}

			if ($first && $this->loop)
			{
				$this->updateseq(0);
				return $this->getnext(false);
			}
			return 0;
		}
	}
}


function basedir_rewrite($basedirs)
{
	$ignore = array();
	$s_base_dir = explode(';',$basedirs);
	$value = '';
	for ($i=0;$i<count($s_base_dir);$i++) 
	{
		if (!empty($s_base_dir[$i]))
		{
			$sbase = slashtranslate($s_base_dir[$i]);
			if (!isset($ignore[$sbase]))
			{
				$ignore[$sbase] = true;
				$value .= $sbase;
				$value .= ';';
			}
		}
	}
	return substr($value, 0, strlen($value) -1);
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
				$setctl->set('approvesignup', 0);
				$setctl->set('urlsecurity', 0);
				$setctl->set('publicrssfeed', 0);
				$setctl->set('shoutbox', 0);
			break;

			case 1:
				$setctl->set('includeheaders', 0);
				$setctl->set('showupgrade', 0);
				$setctl->set('showstatistics', 0);
				$setctl->set('albumcover', 0);
				$setctl->set('albumresize', 0);
				$setctl->set('fetchalbum', 0);
				$setctl->set('showlyricslink', 0);	
				$setctl->set('storealbumcovers', 0);			
			break;

			case 2:
				$setctl->set('streamingengine', 0);
				$setctl->set('allowdownload', 0);
				$setctl->set('allowseek', 0);
				$setctl->set('virtualdir', 0);
				$setctl->set('disksync', 0);
				$setctl->set('sendfileextension', 0);
				$setctl->set('unauthorizedstreams', 0);				
				$setctl->set('writeid3v2', 0);
				$setctl->set('optimisticfile', 0);
				$setctl->set('lamesupport', 0);
				$setctl->set('enableupload', 0);
			break;
			
			case 4:
				$setctl->set('networkmode', 0);
			break;
		}
				
		foreach ($data as $key => $value)
		{			
			switch ($key)
			{
				case 'storealbumdir':
					
					if (strlen($value) > 0) $value = slashtranslate($value);
						
					$relative = '';

					$value = trim($value);
					if (strlen($value) > 0 && $setctl->get('storealbumcovers')) 
					{
						if ($value[strlen($value)-1] != '/') $value .= '/';

						if ($value[0] != '/')
						{
							$cdir = getcwd();
							$newdir = slashtranslate(dirname($_SERVER['SCRIPT_FILENAME'])).$value;
							if (@chdir($newdir)) $relative = relativedir($newdir);
							chdir($cdir);	
						} else $relative = relativedir($value);
						
					}
					$setctl->set('storealbumrelative', $relative);
					
				break;
				
				case 'base_dir':
					$value = basedir_rewrite($value);
					if ($value != $setctl->get('base_dir')) $setctl->set('basedir_changed', 1);
				break;

				case 'timeout':
					if ($value < 600 && $value != 0) $value = $setctl->get('timeout');
				break;

				case 'uploadpath':
					if (!empty($value)) $value = slashtranslate($value);
				break;

				case 'filetemplate':
					$value = stripcslashes($value);
				break;

				case 'homepage':
					$value = htmlentities($value);
				break;	
				
				case 'albumfiles': 
					$value = stripcslashes($value); 
				break;

				case 'uploadflist': 
					$value = stripcslashes($value); 
				break;
				
				case 'externimagespath':						
					if (!empty($value)) if ($value[strlen($value)-1] != '/') $value .= '/';	
				break;				
			}
			$setctl->set($key, $value);			
		}				
	}
}

function helplink($section, $name='?', $class='')
{
	global $deflanguage, $app_build;
	if (!empty($class)) $x = ' class="'.$class.'"'; else $x = '';
	return '<a'.$x.' target="_new" title="'.get_lang(161).'" href="http://www.kplaylist.net/?configuration='.$section.'&amp;lang='.$deflanguage.'&amp;b='.$app_build.'">'.$name.'</a>';
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
		return db_insert_id();
	}	
}

function edit_filetype($id, $reload = false, $msg='')
{
	if ($id != 0)
	{
		$res = db_execquery('SELECT * FROM '.TBL_FILETYPES.' WHERE id = '.$id);
		$row = db_fetch_assoc($res);		
	} else 
	{
		$row['extension'] = '';
		$row['mime'] = '';
		$row['m3u'] = 1;
		$row['search'] = 1;		
		$row['logaccess'] = 1;
	}
	kprintheader(get_lang(209));
	?>
	<form name="edit_filetype" method="post" action="<?php echo PHPSELF; ?>">
	<input type="hidden" name="action" value="storefiletype"/>
	<input type="hidden" name="id" value="<?php echo $id; ?>"/>

	<table width="97%" align="center" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td class="wtext" colspan="3"><?php echo $msg; ?></td>
	</tr>	
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

function edit_network($host, $reload = false, $msg='')
{
	if (!$host) die(get_lang(56));

	kprintheader(get_lang(352));
	?>
	<form name="edit_filetype" method="post" action="<?php echo PHPSELF; ?>">
	<input type="hidden" name="action" value="storenetwork"/>
	<input type="hidden" name="nid" value="<?php echo $host->getnid(); ?>"/>

	<table width="97%" align="center" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td class="wtext" colspan="3"><?php echo $msg; ?></td>
	</tr>
	<tr>
		<td class="wtext"><?php echo get_lang(355); ?>*</td>
		<td class="wtext"><input type="text" name="url" class="fatbuttom" size="50" value="<?php echo $host->geturl(); ?>"/></td>
		<td class="wtext"><?php echo helplink('neturl'); ?></td>
	</tr>
	<tr>
		<td class="wtext"><?php echo get_lang(356); ?>*</td>
		<td class="wtext"><input type="text" name="username" class="fatbuttom" maxlength="64" size="20" value="<?php echo $host->getusername(); ?>"/></td>
		<td class="wtext"><?php echo helplink('netusername'); ?></td>
	</tr>
	<tr>
		<td class="wtext"><?php echo get_lang(100); ?>*</td>
		<td class="wtext"><input type="password" name="password" class="fatbuttom" maxlength="64" size="20" value="<?php echo $host->getpassword(); ?>"/></td>
		<td class="wtext"><?php echo helplink('netpassword'); ?></td>
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
	global $phpenv, $setctl, $cfg, $win32, $streamtypes_default;
	
	phpfigure();

	$senginesupport = true;
	if ($win32 && !isphp5()) $senginesupport = false;

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
			<td class="wtext"><?php echo get_lang(324); ?></td>
			<td class="wtext">			
			<?php					
				$options = array(0 => array(0, htmlentities('<').get_lang(148).htmlentities('>')));				
				$res = db_execquery('SELECT u_login, u_id FROM '.TBL_USERS.' WHERE utemplate = 1');
				if ($res) while ($row = db_fetch_assoc($res)) $options[] = array($row['u_id'], $row['u_login']);			
				echo genselect('signuptemplate', $options, $setctl->get('signuptemplate'));
			?>
			</td>
			<td class="wtext"><?php echo helplink('signuptemplate'); ?></td>
			</tr>
			<tr>
			<td class="wtext"><?php echo get_lang(281); ?></td>
			<td class="wtext"><input type="checkbox" value="1" name="approvesignup" <?php echo $setctl->getchecked('approvesignup'); ?>/></td>
			<td class="wtext"><?php echo helplink('approvesignup'); ?></td>
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
		
			<tr>
				<td class="wtext"><?php echo get_lang(364); ?></td>
				<td class="wtext"><input type="checkbox" value="1" <?php if (!AJAX) echo 'disabled="disabled"'; ?> name="shoutbox" <?php echo $setctl->getchecked('shoutbox'); ?>/></td>
				<td class="wtext"><?php echo helplink('shoutbox'); ?></td>
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
			?>

			<tr>
			<td class="wtext"><?php echo get_lang(299); ?></td>
			<td class="wtext"><input type="checkbox" value="1" name="urlsecurity" <?php echo $setctl->getchecked('urlsecurity'); ?>/></td>
			<td class="wtext"><?php echo helplink('urlsecurity'); ?></td>
			</tr>

			<tr>
			<td class="wtext"><?php echo get_lang(309); ?></td>
			<td class="wtext"><input type="checkbox" value="1" name="publicrssfeed" <?php echo $setctl->getchecked('publicrssfeed'); ?>/></td>
			<td class="wtext"><?php echo helplink('publicrssfeed'); ?></td>
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

			<?php

				$kpt = new kptheme();
				if ($kpt->load())
				{
					?>
					<tr>
						<td class="wtext"><?php echo get_lang(366); ?></td>
						<td>
							<select name="themeid" class="fatbuttom">
							<?php echo $kpt->select($setctl->get('themeid')); ?>
							</select>							
						</td>
						<td class="wtext"><?php echo helplink('s_themeid'); ?></td>

					</tr>
					<?php
				}
				?>


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
				<td class="wtext">Enable mobile CSS</td>
				<td class="wtext"><input type="text" class="fatbuttom" name="mobilecss" maxlength="50" size="50" value="<?php echo $setctl->get('mobilecss'); ?>"/></td>
				<td class="wtext"><?php echo helplink('externaljavascript'); ?></td>
			</tr>
			<tr>
				<td class="wtext"><?php echo get_lang(196); ?></td>
				<td class="wtext"><input type="text" class="fatbuttom" name="externaljavascript" maxlength="50" size="50" value="<?php echo $setctl->get('externaljavascript'); ?>"/></td>
				<td class="wtext"><?php echo helplink('externaljavascript'); ?></td>
			</tr>
			<tr>
				<td class="wtext"><?php echo get_lang(342); ?></td>
				<td class="wtext"><input type="text" class="fatbuttom" name="ajaxurl" maxlength="50" size="50" value="<?php echo $setctl->get('ajaxurl'); ?>"/></td>
				<td class="wtext"><?php echo helplink('ajaxurl'); ?></td>
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
				<td class="wtext"><?php echo get_lang(248).'/'.get_lang(249); ?></td>
				<td class="wtext">
						<input type="text" size="5" class="fatbuttom" name="albumheight" value="<?php echo $setctl->get('albumheight'); ?>"/>&nbsp;
						<input type="text" size="5" class="fatbuttom" name="albumwidth" value="<?php echo $setctl->get('albumwidth'); ?>"/>
				</td>
				<td class="wtext"><?php echo helplink('albumheight'); ?></td>
			</tr>
			<tr>
				<td class="wtext"><?php echo get_lang(256); ?></td>
				<td class="wtext"><input type="checkbox" value="1" name="fetchalbum" <?php echo $setctl->getchecked('fetchalbum'); ?>/></td>
				<td class="wtext"><?php echo helplink('fetchalbum'); ?></td>
			</tr>
			<tr>
				<td class="wtext"><?php echo get_lang(257); ?></td>
				<td class="wtext"><input type="text" size="50" class="fatbuttom" name="albumurl" value="<?php echo htmlentities($setctl->get('albumurl')); ?>"/></td>
				<td class="wtext"><?php echo helplink('albumurl'); ?></td>
			</tr>

			<tr>
				<td class="wtext"><?php echo get_lang(305); ?></td>
				<td class="wtext"><input type="checkbox" value="1" name="showlyricslink" <?php echo $setctl->getchecked('showlyricslink'); ?>/></td>
				<td class="wtext"><?php echo helplink('showlyricslink'); ?></td>
			</tr>
			<tr>
				<td class="wtext"><?php echo get_lang(304); ?></td>
				<td class="wtext"><input type="text" size="50" class="fatbuttom" name="lyricsurl" value="<?php echo htmlentities($setctl->get('lyricsurl')); ?>"/></td>
				<td class="wtext"><?php echo helplink('lyricsurl'); ?></td>
			</tr>

			<tr>
				<td class="wtext"><?php echo get_lang(298); ?></td>
				<td class="wtext"><input type="text" size="50" class="fatbuttom" name="filetemplate" value="<?php echo htmlentities($setctl->get('filetemplate'), ENT_QUOTES); ?>"/></td>
				<td class="wtext"><?php echo helplink('filetemplate'); ?></td>
			</tr>
			<tr>
				<td class="wtext"><?php echo get_lang(370); ?></td>
				<td class="wtext"><input type="checkbox" value="1" name="storealbumcovers" <?php echo $setctl->getchecked('storealbumcovers'); ?>/></td>
				<td class="wtext"><?php echo helplink('storealbumcovers'); ?></td>
			</tr>
			<tr>
				<td class="wtext"><?php echo get_lang(371); ?></td>
				<td class="wtext"><input type="text" size="50" class="fatbuttom" name="storealbumdir" value="<?php echo $setctl->get('storealbumdir'); ?>"/></td>
				<td class="wtext"><?php echo helplink('storealbumdir'); ?></td>
			</tr>
			<?php
		break;
		
		case 2:
			?>
			<tr>
				<td class="wtext"><?php echo get_lang(127); ?></td>
				<td class="wtext">
					<input type="text" name="base_dir" class="fatbuttom" size="50" value="<?php echo $setctl->get('base_dir'); ?>"/>&nbsp;
					<input type="button" class="fatbuttom" onclick="javascript: newwinscroll('find', '<?php echo PHPSELF; ?>?action=findmusic', 450, 600);" value="<?php echo get_lang(289); ?>"/>			
				</td>
				<td class="wtext"><?php echo helplink('basedir'); ?></td>
			</tr>
		
			<tr>
				<td class="wtext"><?php echo get_lang(362); ?></td>
				<td class="wtext"><input type="checkbox" id="virtualdir" value="1" onclick="javascript: 				
							d = document.getElementById('disksync');
							d2 = document.getElementById('virtualdir');
								if (d && d2) 
								{ 
									if (d2.checked) 
									{
										d.checked = false;
										d.disabled = true;
									} else
									{
										d.disabled = false;
									}
								}
								" name="virtualdir" <?php echo $setctl->getchecked('virtualdir'); ?>/></td>
				<td class="wtext"><?php echo helplink('virtualdir'); ?></td>
			</tr>
			
			<tr>
				<td class="wtext"><?php echo get_lang(192); ?></td>
				<td class="wtext"><input type="checkbox" value="1" <?php if ($setctl->getchecked('virtualdir')) echo 'disabled="disabled"'; ?> id="disksync" name="disksync" <?php echo $setctl->getchecked('disksync'); ?>/></td>
				<td class="wtext"><?php echo helplink('disksync'); ?></td>
			</tr>
			<tr>
				<td class="wtext"><?php echo get_lang(211); ?></td>
				<td class="wtext"><input type="checkbox" value="1" name="optimisticfile" <?php echo $setctl->getchecked('optimisticfile'); ?>/></td>
				<td class="wtext"><?php echo helplink('optimisticfile'); ?></td>
			</tr>
			<?php

			$setstr = $setctl->get('streamlocation');
			if (strlen($setstr) == 0)
			{
				$setstr = $phpenv['streamlocation'];
				$strjs = true;
			} else $strjs = false;

			?>
			<tr>
				<td class="wtext"><?php echo get_lang(128); ?></td>
				<td class="wtext"><input type="text" name="streamurl" size="7" maxlength="32" class="fatbuttom" value="<?php echo $setctl->get('streamurl'); ?>"/>				
				<input type="text" name="streamlocation" id="streamlocation" class="fatbuttom" size="40" value="<?php echo $setstr; ?>" <?php if ($strjs) echo 'disabled="disabled"'; ?>/>
				<?php
				if ($strjs) 
				{
					?>
					&nbsp;<input type="button" class="fatbuttom" onclick="javascript: d = document.getElementById('streamlocation'); if (d) { d.value = ''; d.disabled = false; d.focus(); }" value="<?php echo get_lang(71); ?>"/>		
					<?php
					}
				?>
				</td>
				<td class="wtext"><?php echo helplink('streamlocation'); ?></td>
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
				<td class="wtext"><input type="checkbox" value="1" <?php if (!$senginesupport) echo 'disabled="disabled"'; ?> name="streamingengine" <?php if ($senginesupport) echo $setctl->getchecked('streamingengine'); ?>/></td>
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
				<td class="wtext"><input type="checkbox" value="1" <?php if (!$cfg['enablegetid3'] || GETID3_V <= 1) echo 'disabled="disabled"'; ?> name="writeid3v2" <?php if ($cfg['enablegetid3']) echo $setctl->getchecked('writeid3v2'); ?>/> <?php if (defined('GETID3_V')) echo '(getid3 '.GETID3_V.')'; ?></td>
				<td class="wtext"><?php echo helplink('writeid3v2'); ?></td>
			</tr>
			<tr>
				<td class="wtext"><?php echo get_lang(220); ?></td>
				<td class="wtext"><input type="checkbox" value="1" <?php if (!function_exists('proc_open')) echo 'disabled="disabled"'; ?> name="lamesupport" <?php echo $setctl->getchecked('lamesupport'); ?>/></td>
				<td class="wtext"><?php echo helplink('lamesupport'); ?></td>
			</tr>
			<tr>
				<td class="wtext"><?php echo get_lang(244); ?></td>
				<td class="wtext"><input type="text" class="fatbuttom" size="55" disabled="disabled" maxlength="255" name="lamecmd" value='<?php echo $cfg['lamecmd']; ?>'/></td>
				<td class="wtext"><?php echo helplink('lamecmd'); ?></td>
			</tr>
			<tr>
				<td class="wtext"><?php echo get_lang(231); ?></td>
				<td class="wtext"><input type="checkbox" value="1" name="enableupload" <?php 						
					echo $setctl->getchecked('enableupload');
					$phpupload = ini_get('file_uploads');
					if ($phpupload == 0 || $phpupload == 'off') echo ' disabled="disabled"'; ?>/></td>
				<td class="wtext"><?php echo helplink('enableupload'); ?></td>
			</tr>
			<tr>
				<td class="wtext"><?php echo get_lang(232); ?></td>
				<td class="wtext"><input type="text" name="uploadpath" class="fatbuttom" size="50" value="<?php echo $setctl->get('uploadpath'); ?>"/></td>
				<td class="wtext"><?php echo helplink('uploadpath'); ?></td>
			</tr>
			<tr>
				<td class="wtext"><?php echo get_lang(300); ?></td>
				<td class="wtext"><input type="text" name="uploadflist" class="fatbuttom" size="50" value="<?php echo $setctl->get('uploadflist'); ?>"/></td>
				<td class="wtext"><?php echo helplink('uploadflist'); ?></td>
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
						if ($res) while ($row = db_fetch_row($res)) $editstreamtypes[] = array($row, 0);;

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
									echo '<a class="hot" onclick="javascript: if (!confirm(\''.get_lang(210).'\')) return false;" href="'. PHPSELF .'?action=deletefiletype&amp;del='.$editstreamtypes[$i][0][5].'">'.get_lang(109).'</a>&nbsp;&nbsp;';
									
									echo '<a class="hot" href="javascript: void(0);" onclick="javascript: newwin(\'fileditor\', \''.PHPSELF.'?action=editfiletype&amp;id='.$editstreamtypes[$i][0][5].'\',180,365);">'.get_lang(71).'</a>&nbsp;';
								}
							?>
							</td>
							
							</tr>
							<?php
						}
						?>
						<tr>
							<td colspan="3"/><td><?php echo '<a class="hot" href="javascript: void(0);" onclick="javascript: newwin(\'fileditor\', \''.PHPSELF.'?action=editfiletype&amp;id=0\',180,365);">'.get_lang(69).'</a>&nbsp;';?></td>
						</tr>
					</table>
				</td>
			</tr>
			<?php
		break;
		
		case 4:

			if (ini_get('allow_url_fopen') == '0' || ini_get('allow_url_fopen') == 'Off') $net = false; else $net = true;

			?>
			<tr>
				<td class="wtext"><?php echo get_lang(353); ?></td>
				<td class="wtext"><input type="checkbox" value="1" name="networkmode" <?php echo $setctl->getchecked('networkmode'); ?>/></td>
				<td class="wtext"><?php echo helplink('networkmode'); ?></td>
			</tr>

			<tr>
				<td height="15"></td>
			</tr>

			<?php if (function_exists('curl_init') && (ini_get('allow_url_fopen') == '1' || ini_get('allow_url_fopen') == 'On'))
			{
			?>
			<tr>
				<td colspan="3">
				<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr class="wtext">
					<td width="15%"><?php echo get_lang(92); ?></td>
					<td width="70%"><?php echo get_lang(355); ?></td>
					<td width="15"></td>
				</tr>
				<tr>
					<td colspan="3" height="10"></td>
				</tr>
				<?php

				$cnt=0;
				$ndb = new networkdb();
				$hosts = $ndb->getall();
				for($i=0,$c=count($hosts);$i<$c;$i++)
				{
					$host = $hosts[$i];
					$cnt++;
					if ($cnt % 2 == 0) echo '<tr>'; else echo '<tr class="row2nd">';
					echo '<td>';
					echo '<a class="hot" onclick="javascript: if (!confirm(\''.get_lang(210).'\')) return false;" href="'.PHPSELF.'?action=deletenetwork&amp;nid='.$host->getnid().'">'.get_lang(109).'</a> ';	
					echo '<a class="hot" href="javascript: void(0);" onclick="javascript: newwin(\'networkedit\', \''.PHPSELF.'?action=editnetwork&amp;nid='.$host->getnid().'\',140,450);">'.get_lang(71).'</a>';
					echo '</td>';
					
					echo '<td class="wtext">'.$host->geturl().'</td>';
					echo '<td>';
					if ($host->getenabled()) 
					{	
						echo '<a class="hot" onclick="javascript: if (!confirm(\''.get_lang(210).'\')) return false;" href="'.PHPSELF.'?action=deactivatenetwork&amp;nid='.$host->getnid().'">'.get_lang(361).'</a> ';
					} else
					{
						echo '<a class="hot" href="javascript: void(0);" onclick="javascript: newwin(\'networkedit\', \''.PHPSELF.'?action=activatenetwork&amp;nid='.$host->getnid().'\',140,450);">'.get_lang(283).'</a>';
					}
					echo '</td>';
					echo '</tr>';
				}

				?>

				<tr>
					<td align="left"><?php echo '<a class="hot" href="javascript: void(0);" onclick="javascript: newwin(\'networkedit\', \''.PHPSELF.'?action=addnetwork\',140,450);">'.get_lang(69).'</a>&nbsp;';?></td>
				</tr>
				</table>
				</td>
			</tr>
			<?php
			} else
			{
				$infourl = 'http://www.kplaylist.net/forum/viewtopic.php?p=8200';
				?>
				<tr>
					<td colspan="3" class="wtext"><?php echo get_lang(359, '<a href="'.$infourl.'" target="_blank">'.$infourl.'</a>'); ?></td>
				</tr>
				<?php
			}
		break;
	
	
		case 5:
			
			echo '<tr><td colspan="3"><table width="100%" cellspacing="10" cellpadding="5" border="1">';			
			
			$tests = array('php5', 'getid3', 'iconv', 'zip', 'curl', 'multibyte', 'gd/image', 'openssl'); 
			
			echo '<tr><td valign="top">';
			echo '<table width="100%" cellspacing="0" cellpadding="0" border="0">';
			foreach($tests as $name)
			{
				$testok = false;
				$info = '';

				switch($name)
				{
					case 'php5':		if (isphp5()) $testok = true; 
										break;
					case 'getid3':		if (GETID3_V > 1) $testok = true; 
										$info = 'http://www.kplaylist.net/forum/viewtopic.php?t=1003';
										break;										
					case 'iconv':		if (extension_loaded('iconv')) $testok = true; 
										break;
					case 'zip':			if (extension_loaded('zip')) $testok = true;
										$info = 'http://www.kplaylist.net/forum/viewtopic.php?t=2187';
										break;
					case 'curl':		if (extension_loaded('curl')) $testok = true; 
										break;
					case 'multibyte':	if (extension_loaded('mbstring')) $testok = true; 
										break;
					case 'gd/image':	if (function_exists('imagecreatetruecolor') && function_exists('imagecopyresampled') &&
											function_exists('imagecreatefromgif') && function_exists('imagecreatefrompng') && 
											function_exists('imagecreatefromjpeg') && extension_loaded('gd')) $testok = true; 
										break;
					case 'openssl':		if (extension_loaded('openssl')) $testok = true; 
										break;

				}

				echo '<tr class="wtext">';
				echo '<td>'.$name.'</td>';

				echo '<td>';
				if ($testok) echo get_lang(204); 
				else 
				{
					echo '<font color="red">'.get_lang(205).'</font>';					
					if (strlen($info) > 0) echo '&nbsp;<a target="_blank" href="'.$info.'">?</a>';


				}
				echo '</td>';
				echo '</tr>';
			}
			echo '</table>';
			echo '</td><td valign="top">';
			echo '<table width="100%" cellspacing="0" cellpadding="0" border="0">';

			$infos = array('memory_limit', 'max_execution_time', 'file_uploads', 'upload_max_filesize', 'post_max_size', 'allow_url_fopen');
		
			foreach($infos as $name)
			{
				$value = @ini_get($name);
				echo '<tr class="wtext"><td>'.$name.'</td><td>'.$value.'</td></tr>';
			}

			echo '</table>';
			echo '</td></tr></table></td></tr>';

		break;
	
	}
}

function settings_edit($reload = 0, $page = 0)
{
	global $phpenv, $setctl;
	kprintheader(get_lang(126));

	$menuclass = array('header', 'header', 'header', 'header', 'header', 'header');
	$menuclass[$page] = 'headermarked';

	$widths = array('35%', '50%', '15%'); 

	function pagelink($id, $reload)
	{
		return PHPSELF.'?action=settingsview&amp;reload='.$reload.'&amp;page='.$id;
	}
	
	?>
	<form style="margin:0;padding:0" name="settings" method="post" action="<?php echo PHPSELF; ?>">
	<input type="hidden" name="action" value="savesettings"/>
	<input type="hidden" name="page" value="<?php echo $page; ?>"/>
	<table width="100%" border="0" cellpadding="0" cellspacing="0">
		<tr>
			<td width="13%" class="settings">
				<a class="<?php echo $menuclass[0]; ?>" href="<?php echo pagelink(0,$reload); ?>"><?php echo get_lang(188); ?></a></td>
			<td width="18%" class="settings">
				<a class="<?php echo $menuclass[1]; ?>" href="<?php echo pagelink(1,$reload); ?>"><?php echo get_lang(189); ?></a></td>
			<td width="19%" class="settings">
				<a class="<?php echo $menuclass[2]; ?>" href="<?php echo pagelink(2,$reload); ?>"><?php echo get_lang(190); ?></a></td>
			<td width="18%" class="settings">
				<a class="<?php echo $menuclass[3]; ?>" href="<?php echo pagelink(3,$reload); ?>"><?php echo get_lang(203); ?></a></td>
			<td width="18%" class="settings">
				<a class="<?php echo $menuclass[4]; ?>" href="<?php echo pagelink(4,$reload); ?>"><?php echo get_lang(352); ?></a></td>
			<td width="14%" class="settings" style="border-right-width: 0px;">
				<a class="<?php echo $menuclass[5]; ?>" href="<?php echo pagelink(5,$reload); ?>"><?php echo get_lang(52); ?></a></td>
		</tr>
		<tr>
			<td height="10"></td>
		</tr>
		<tr>
			<td colspan="6" height="5"/>
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


function gen_hkey()
{
	$hkey = '';
	
	$chars = '1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
	$len = strlen($chars);
	$randmax = getrandmax();
	srand((double)microtime()*1000000);

	while (strlen($hkey) < 32) $hkey .= $chars[rand(0, $len - 1)];

	return $hkey;
}	

function webauthenticate()
{
	global $phpenv, $setctl, $cfg, $u_id;

	$status = 0;
	
	if (!$cfg['disablelogin']) 
	{
		if (frm_isset('l_username') && frm_isset('l_password'))
		{
			$user = frm_get('l_username');
			$pass = frm_get('l_password');

			if (strlen($user) > 0 && strlen($pass) > 0)
			{
				$kpu = new kpuser();
				if ($kpu->loadbyuserpass($user, $pass))
				{
					$u_id = $kpu->getid();
					$hkey = gen_hkey();
										
					$kpdb = new kpdbconnection();
					$kpdb->preparestmt('INSERT INTO '.TBL_SESSION.' SET u_id = ?, hkey = "?", login = ?, refreshed = ?, ip = ?', array($kpu->getid(), $hkey, time(), time(), ip2long($phpenv['remote'])));
					if ($kpdb->query())
					{
						$cookie_value = $kpdb->getautoid().'-'.$hkey;
						
						if ($kpu->get('u_access') != 2)
						{
							if ($cfg['numberlogins'] > 0)
							{
								$kpdb->preparestmt('SELECT sessionid FROM '.TBL_SESSION.' WHERE u_id = ? AND logout = 0 ORDER BY sessionid DESC', array($kpu->getid()));
								if ($kpdb->query())
								{
									$cnt = 0;
									while ($row = $kpdb->nextrow())
									{
										if ($cnt >= $cfg['numberlogins']) db_execquery('UPDATE '.TBL_SESSION.' SET logout = '.time().' WHERE sessionid = '.$row['sessionid']);
										$cnt++;
									}
								}
							}
						}					
					
						if ($setctl->get('timeout') > 0 && frm_get('l_rememberme', 2)) $expiration = time() + $setctl->get('timeout'); else $expiration = 0;

						switch ($cfg['authtype'])
						{
							case 2: 
								if (strlen(session_id()) > 0)
								{
									$_SESSION[$cfg['cookie']] = $cookie_value; 
									$status = 1;								
								} else $status = 2;
								break;
							default: 
								if (setcookie($cfg['cookie'], $cookie_value, $expiration)) $status = 1; else $status = 2;
								break;
						}
					} else $status = 2;
				}					
			}
		}
	} else $status = 1;

	return $status;
}

function authset($check=true)
{
	$key = '';
	global $cfg;
	switch ($cfg['authtype'])
	{
		case 2:
				if (isset($_SESSION[$cfg['cookie']])) $key = $_SESSION[$cfg['cookie']];
				break;

		default:
				if (isset($_COOKIE[$cfg['cookie']])) $key = $_COOKIE[$cfg['cookie']];
				break;
		
	}

	if (strlen($key) > 0)
	{
		if ($check) return true; else return $key;
	}

	return false;
}

function timeout($usertime)
{
	global $setctl;

	if ($setctl->get('timeout') != 0) if (($usertime + $setctl->get('timeout')) < time()) return true;

	return false;
}

function db_verify_stream($cookie = '', $ip, $stream)
{
	global $u_id, $setctl, $cfg, $valuser;
	if ($cfg['disablelogin']) 
	{
		$u_id = $cfg['assumeuserid'];
		loadvalidated($u_id);
		if ($valuser === false) 
		{
			echo $cfg['assumeuserid'].' has a ID to a user that does not exist. Please set it correctly in the script and reload this page.';
			die();
		}
		return 1;
	} else
	{
		$ckexp = explode('-', $cookie);
		
		if (count($ckexp) == 2 && is_numeric($ckexp[0]) && strlen($ckexp[1]) > 0)
		{
			if ($stream)
				$sql = 'SELECT u_id, login as u_time, sstatus FROM '.TBL_SESSION.' WHERE sessionid = '.$ckexp[0].' AND hkey = "'.$ckexp[1].'"';
			else 
				$sql = 'SELECT u_id, login as u_time, sstatus FROM '.TBL_SESSION.' WHERE sessionid = '.$ckexp[0].' AND hkey = "'.$ckexp[1].'" AND logout = 0';

			$result = db_execquery($sql);
			if ($result)
			{
				$row = db_fetch_assoc($result);
				$u_id = $row['u_id'];
				loadvalidated($u_id);
				$time = $row['u_time'];
			
				if ($valuser && !timeout($row['u_time']))
				{
					if ($row['sstatus'] == 2) $valuser->setro('u_access', 1);
					return 1;
				}
			}
		}
		return 0;		
	}
}

function getlastlogin($uid)
{
	$res = db_execquery('SELECT * FROM '.TBL_SESSION.' WHERE u_id = '.$uid.' ORDER BY sessionid DESC LIMIT 1');
	if ($res && db_num_rows($res) > 0) return db_fetch_assoc($res);
}

function addhistory($u_id, $sid, $tid = 0)
{
	global $runinit;
	$active = 1;
	if ($runinit['astream']) $mid = db_thread_id(); 
	else
	{
		$mid = 0;
		$active = 0;
	}
	if (db_execquery('INSERT INTO '.TBL_MHISTORY.' SET active = '.$active.', mid = '.$mid.', u_id = '.$u_id.', s_id = '.$sid.', utime = '.time().', tid = '.$tid)) return db_insert_id();
}

function updateactive($id)
{	
	global $runinit;
	if ($runinit['astream']) db_execquery('UPDATE '.TBL_MHISTORY.' SET active = 1, mid = '.db_thread_id().', utime='.time().' WHERE h_id = '.$id);
}

function updatehistory($id, $pos, $fpos)
{
	$res = db_execquery('SELECT h.dwritten, s.fsize FROM '.TBL_MHISTORY.' h, '.TBL_SEARCH.' s WHERE h_id = '.$id.' AND s.id = h.s_id');
	if ($res && db_num_rows($res) == 1)
	{
		$row = db_fetch_row($res);
		$add = (int) $row[0];
		$add = $add + $pos;
		$size = $row[1];

		$cpercent = 0;
		if ($fpos > 0) $cpercent = ($fpos / $row[1]) * 100;

		if ($add > $size) $add = $size;
			
		if ($size > 0 && $add > 0)
		{
			$dpercent = ($add / $size) * 100;
			$sql = 'UPDATE '.TBL_MHISTORY.' SET dwritten = '.$add.', dpercent = '.number_format($dpercent,0);
			if (!connection_aborted()) $sql .= ', cpercent = '.number_format($cpercent,0);
			$sql .= ' WHERE h_id = '.$id;
			db_execquery($sql);
		}
	}
}

function getlasthistory($sid, $uid, $rhid=false)
{
	$res = db_execquery('SELECT s_id, utime, h_id FROM '.TBL_MHISTORY.' WHERE s_id = '.$sid.' AND u_id = '.$uid.' ORDER BY h_id DESC LIMIT 1');	
	if ($res !== false && db_num_rows($res) > 0)
	{
		$row = db_fetch_assoc($res);
		if ($rhid) return $row['h_id']; 
			else return $row['utime'];
	} 
	return 0;
}

function get_archiver_combo($default)
{
	global $archivers;
	$out = '';
	foreach($archivers as $id => $archiverdata)
	{
		if (is_array($archiverdata) && $archiverdata[0])
		{
			$out .= '<option value="'.$id.'"';
			if ($default == $id) $out .= ' selected="selected"'; 
			if (isset($archiverdata[4])) $name = $archiverdata[4]; else $name = $archiverdata[1];
			
			$out .= '>'.$name.'</option>';
		}
	}
	return $out;
}

function chsessionstatus($cookie, $status=0)
{
	global $cfg;
	$ckexp = explode('-', $cookie);

	if (count($ckexp) == 2 && is_numeric($ckexp[0]) && strlen($ckexp[1]) > 0)
	{
		db_execquery('UPDATE '.TBL_SESSION.' SET sstatus = '.$status.' WHERE sessionid = '.$ckexp[0]);
		return true;
	}
	return false;
}

function adminlogout($uid)
{
	db_execquery('UPDATE '.TBL_SESSION.' SET logout = '.time().' WHERE u_id = '.$uid.' AND logout = 0');
}

class saveuser
{
	function saveuser()
	{
		$this->kpu = new kpuser();
		$this->id = -1;
	}

	function setid($id)
	{
		$this->id = $id;
		$this->kpu->id = $id;
		$this->kpu->load($this->id);
	}
	
	function usernameok()
	{
		$result = db_execquery('SELECT u_id FROM '.TBL_USERS.' WHERE u_login = "'.myescstr($this->kpu->get('u_login')).'"');
		if (db_num_rows($result) > 0)
		{
			$row = db_fetch_row($result);
			if ($row[0] != $this->id) return false;
		}
		return true;
	}
	
	function fromtemplate($id)
	{
		$this->setid($id);
		$this->setid(-1);
	}

	function frompost()
	{
		global $setctl;
		$this->kpu->setallowed(array('u_booted', 'u_allowdownload', 'lameperm', 'forcelamerate', 'allowemail', 'u_name', 'u_login', 'u_comment', 'u_access', 'udlrate', 'email', 'lang', 'utemplate', 'streamengine', 'allowarchive', 'archivesize', 'homedir', 'network'));
		$this->kpu->set('u_booted', 0);
		$this->kpu->set('u_allowdownload', 0);
		$this->kpu->set('lameperm', 0);
		$this->kpu->set('allowemail', 0);
		$this->kpu->set('allowarchive', 0);
		$this->kpu->set('network', 0);
		if ($setctl->get('streamingengine')) $this->kpu->set('streamengine', 0);
		foreach($_POST as $name => $value) $this->kpu->set($name, $value, true);
	}

	function validname()
	{
		$name = $this->kpu->get('u_name');
		$login = $this->kpu->get('u_login');

		if ($this->kpu->get('utemplate'))
		{
			if (empty($login)) return false;
		} else if (empty($name) || empty($login)) return false;
	
		return true;
	}
}


function save_user()
{
	global $setctl;

	$form = true;
	$id = frm_get('u_id', 1, -1);
	$tempid = frm_get('templateid', 1, 0);

	$sv = new saveuser();
	$sv->setid($id);

	if (frm_isset('passchange') && $id != -1) $changepw = 1; else $changepw = 0;
	if (frm_isset('password')) $pass = myescstr(frm_get('password')); else $pass = '';	
	if ($tempid > 0 && $id == -1) $sv->fromtemplate($tempid);

	$sv->frompost();

	if ($sv->validname()) 
	{
		if ($sv->usernameok())
		{
			$sv->kpu->set('homedir', slashtranslate(frm_get('homedir')));
			
			$text = get_lang(262);

			if ($changepw)
			{
				if (empty($pass)) $text = get_lang(310);
				else 
				{
					$text = get_lang(157);
					$sv->kpu->set('u_pass', md5($pass));
					$sv->kpu->update();
				}
			} else
			{
				if ($id == -1) 
				{
					$sv->kpu->set('u_pass', md5($pass));
					$sv->kpu->set('created', time());	
					if ($tempid == 0) 
					{
						$sv->kpu->set('lang', $setctl->get('default_language'));
						$sv->kpu->store();
						$text = get_lang(259);
					} else $sv->kpu->store(false);
					$tempid = 0;
				} else $sv->kpu->update();
			}
		} else $text = get_lang(312);
	} else
	{
		switch($sv->kpu->get('utemplate'))
		{
			case 0: $text = get_lang(311); break;
			case 1: $text = get_lang(323); break;
		}
	}	

	show_userform($sv->kpu, $text, $changepw, $tempid);
}

function show_userform($kpu, $text='', $changepass=0, $templateid=0)
{
	global $u_id, $setctl, $lamebitrates;

	if ($kpu->id == -1) $title = get_lang(96); else $title = get_lang(95);
	if ($kpu->get('utemplate') == 1)
	{
		$template = true;
		$title = get_lang(321);
	} else $template = false;

	kprintheader($title);
	?>
	<form method="post" action="<?php echo PHPSELF; ?>">
	<input type="hidden" name="action" value="usersave"/>
	<input type="hidden" name="u_id" value="<?php echo $kpu->id; ?>"/>
	<input type="hidden" name="utemplate" value="<?php echo $kpu->get('utemplate'); ?>"/>
	<input type="hidden" name="templateid" value="<?php echo $templateid; ?>"/>
	<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td width="40%"></td>
		<td width="35%"></td>
		<td width="25%"></td>
	</tr>

	<tr>
		<td class="wtext" colspan="3"><?php echo $text; ?></td>
	</tr>

	<?php 
	
	if ($kpu->id != -1 && $u_id != $kpu->id && !$template) 
	{ 
	?>
	<tr>
		<td class="wtext"><?php echo get_lang(124); ?></td>
		<td><input type="checkbox" name="u_booted" value="1" <?php echo checked($kpu->get('u_booted')); ?> /></td>
		<td class="wtext"><?php echo helplink('ubooted'); ?></td>
	</tr>
	<?php 
	} 
	
	if ($template)
	{
	?>
	<tr> 
		<td class="wtext"><?php echo get_lang(322); ?></td>
		<td><input type="text" name="u_login" class="userfield" value="<?php echo $kpu->get('u_login'); ?>" /></td>
		<td class="wtext"><?php echo helplink('utemplate'); ?></td>
	</tr>
	<?php
	} else
	{	
	?>
	<tr> 
		<td class="wtext"><?php echo get_lang(98); ?></td>
		<td><input type="text" name="u_login" class="userfield" value="<?php echo $kpu->get('u_login'); ?>" /></td>
		<td class="wtext"><?php echo helplink('ulogin'); ?></td>
	</tr>
	<?php 
	}
	if (!$template)
	{
	?>
	<tr> 
		<td class="wtext"><?php echo get_lang(97); ?></td>
		<td><input type="text" name="u_name" class="userfield" value="<?php echo $kpu->get('u_name'); ?>" /></td>
		<td class="wtext"><?php echo helplink('uname'); ?></td>
	</tr>
	<?php
	}
	
	if ($kpu->id != -1) 
	{ 
	?>
	<tr>
		<td class="wtext"><?php echo get_lang(99); ?></td>
		<td align="left"><input type="checkbox" name="passchange" value="1" <?php echo checked($changepass); ?>/></td>
		<td class="wtext"><?php echo helplink('upasschange'); ?></td>
	</tr>
	<?php 
	} 
	
	if (!$template)
	{	
	?>
	<tr> 
		<td class="wtext"><?php echo get_lang(100); ?></td>
		<td width="490"><input type="password" name="password" class="userfield" value=""/></td>
		<td class="wtext"><?php echo helplink('upassword'); ?></td>
	</tr>
	<?php
	}
	?>
	<tr> 
		<td class="wtext"><?php echo get_lang(101); ?></td>
		<td><input type="text" name="u_comment" class="userfield" value="<?php echo $kpu->get('u_comment'); ?>" /></td>
		<td class="wtext"><?php echo helplink('ucomment'); ?></td>
	</tr>
		
	<?php if ($u_id != $kpu->id && !$template)
	{
	?>
	<tr> 
		<td class="wtext"><?php echo get_lang(102); ?></td>
		<td>
		<select name="u_access" class="userfield">
		<option value="0"<?php if ($kpu->get('u_access') == 0) echo ' selected="selected"';?>><?php echo get_lang(138); ?></option>
		<option value="1"<?php if ($kpu->get('u_access') == 1) echo ' selected="selected"';?>><?php echo get_lang(150); ?></option>
		<option value="2"<?php if ($kpu->get('u_access') == 2) echo ' selected="selected"';?>><?php echo get_lang(346); ?></option>
		</select>
		</td>
		<td class="wtext"><?php echo helplink('uaccess'); ?></td>
	</tr>
	<?php 
	}
	?>
	<tr>
		<td class="wtext"><?php echo get_lang(133); ?></td>
		<td><input type="checkbox" name="u_allowdownload" value="1" <?php echo checked($kpu->get('u_allowdownload')); ?> /></td>
		<td class="wtext"><?php echo helplink('udownload'); ?></td>
	</tr>
	<tr>
		<td class="wtext"><?php echo get_lang(326); ?></td>
		<td><input type="checkbox" name="allowarchive" value="1" <?php echo checked($kpu->get('allowarchive')); ?> /></td>
		<td class="wtext"><?php echo helplink('uallowarchive'); ?></td>
	</tr>
	<tr>
		<td class="wtext"><?php echo get_lang(327); ?></td>
		<td><input type="text" name="archivesize" size="5" class="userfield" maxlength="5" value="<?php echo $kpu->get('archivesize'); ?>"/></td>
		<td class="wtext"><?php echo helplink('uarchivesize'); ?></td>
	</tr>
	<tr> 
		<td class="wtext"><?php echo get_lang(149); ?></td>
		<td width="490"><input type="text" size="5" maxlength="5" name="udlrate" class="userfield" value="<?php echo $kpu->get('udlrate'); ?>" /></td>
		<td class="wtext"><?php echo helplink('udlrate'); ?></td>
	</tr>
	<tr> 
		<td class="wtext"><?php echo get_lang(222); ?></td>
		<td width="490"><input type="checkbox" name="lameperm" value="1" <?php echo checked($kpu->get('lameperm')); ?> /></td>
		<td class="wtext"><?php echo helplink('lameperm'); ?></td>
	</tr>
	<?php

	if ($setctl->get('lamesupport'))
	{
	?>	
	<tr>
		<td class="wtext"><?php echo get_lang(330); ?></td>
		<td>
			<?php
			$options = array(0 => array(0, get_lang(221)));
			for ($i=1;$i<count($lamebitrates);$i++) $options[] = array($i, $lamebitrates[$i]);
			echo genselect('forcelamerate', $options, $kpu->get('forcelamerate'));
			?>
		</td>
		<td class="wtext"><?php echo helplink('uforcelamerate'); ?></td>
	</tr>
	<?php
	}
	?>	
	<tr> 
		<td class="wtext"><?php echo get_lang(224); ?></td>
		<td width="490"><input type="checkbox" name="allowemail" value="1" <?php echo checked($kpu->get('allowemail')); ?> /></td>
		<td class="wtext"><?php echo helplink('allowemail'); ?></td>
	</tr>
	<?php
	if ($setctl->get('streamingengine'))
	{
	?>
	<tr> 
		<td class="wtext"><?php echo get_lang(140); ?></td>
		<td width="490"><input type="checkbox" name="streamengine" value="1" <?php echo checked($kpu->get('streamengine')); ?> /></td>
		<td class="wtext"><?php echo helplink('ustreamengine'); ?></td>
	</tr>
	<?php
	}
	if (!$template)
	{	
	?>
	<tr>
		<td class="wtext"><?php echo get_lang(223); ?></td>
		<td><input type="text" maxlength="128" size="30" class="fatbuttom" name="email" value="<?php echo $kpu->get('email'); ?>"/></td>
		<td class="wtext"><?php echo helplink('oemail'); ?></td>
	</tr>
	<tr>
		<td class="wtext"><?php echo get_lang(329); ?></td>
		<td><input type="text" maxlength="128" size="30" class="fatbuttom" name="homedir" value="<?php echo $kpu->get('homedir'); ?>"/></td>
		<td class="wtext"><?php echo helplink('ohomedir'); ?></td>
	</tr>
	<?php
	}
	
	if ($setctl->get('networkmode'))
	{
		?>
		<tr>
			<td class="wtext"><?php echo get_lang(360); ?></td>
			<td><input type="checkbox" name="network" value="1" <?php echo checked($kpu->get('network')); ?> /></td>
			<td class="wtext"><?php echo helplink('unetwork'); ?></td>
		</tr>
		<?php

	}	
	?>

	<tr><td height="10"></td></tr>
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

function kp_signup()
{
	global $deflanguage, $setctl;
	
	if ($setctl->get('approvesignup')) $ustatus = 2; else $ustatus = 1;
		
	if (frm_isset('l_adduser'))
	{
		$uname = frm_get('s_name');
		$ulogin = frm_get('s_login');
		$upass = frm_get('s_password');
		$uemail = frm_get('s_email');

		if (strlen($uname) > 0 && strlen($ulogin) > 0 && strlen($upass) > 0 && strlen($uemail) > 0)
		{
			$result = db_execquery('SELECT u_id FROM '.TBL_USERS.' WHERE u_login = "'.myescstr($ulogin).'"');
			if (db_num_rows($result) == 0 && strtolower($ulogin) != 'admin')
			{				
				$kpu = new kpuser();
				if ($setctl->get('signuptemplate') > 0) $kpu->load($setctl->get('signuptemplate'));
				$kpu->id = -1;
				$kpu->set('utemplate', 0);
				$kpu->set('u_login', $ulogin);
				$kpu->set('u_name', $uname);
				$kpu->set('u_pass', md5($upass));
				$kpu->set('u_comment', frm_get('s_comment'));
				$kpu->set('u_access', 1);
				$kpu->set('email', $uemail);
				$kpu->set('created', time());
				$kpu->set('u_status', $ustatus);
				
				if ($kpu->store(false)) 
				{
					$text = get_lang(259);
					if ($setctl->get('approvesignup')) $text .= '&nbsp;'.get_lang(285);
					kp_signup_form($text, false);
				} else kp_signup_form(get_lang(56));
			} else kp_signup_form(get_lang(312));
		} else kp_signup_form(get_lang(284));
	} else kp_signup_form(); 
}

function kp_signup_form($error='', $controls = true)
{
	kprintheader(get_lang(96));
	?>
	<form method="post" action="<?php echo PHPSELF; ?>">
	<input type="hidden" name="l_signup" value="1"/>
	<input type="hidden" name="l_adduser" value="1"/>
	<table width="100%" align="center" border="0" cellpadding="2" cellspacing="2">
	<tr>
		<td class="wtext" colspan="2"><?php echo $error; ?></td>
	</tr>
	<tr> 
		<td width="50%" class="wtext">* <?php echo get_lang(97); ?></td>
		<td width="50%"><input type="text" name="s_name" class="fatbuttom" value="<?php echo frm_get('u_name'); ?>"/></td>
	</tr>    
	<tr> 
		<td class="wtext">* <?php echo get_lang(98); ?></td>
		<td><input type="text" name="s_login" class="fatbuttom" value="<?php echo frm_get('u_login'); ?>"/></td>
	</tr>
	<tr> 
		<td class="wtext">* <?php echo get_lang(100); ?></td>
		<td><input type="password" name="s_password" class="fatbuttom" value=""/></td>
	</tr>    
	<tr> 
		<td class="wtext"><?php echo get_lang(101); ?></td>
		<td><input type="text" name="s_comment" class="fatbuttom" value="<?php echo frm_get('u_comment'); ?>"/></td>
	</tr>
	<tr> 
		<td class="wtext">* <?php echo get_lang(223); ?></td>
		<td><input type="text" name="s_email" class="fatbuttom" value="<?php echo frm_get('email'); ?>"/></td>
	</tr>
	<tr>
		<td></td>
		<td class="wtext">
			<?php if ($controls) { ?><input type="submit" name="s_submit" value="<?php echo get_lang(45); ?>" class="fatbuttom"/>&nbsp;<?php } ?>
			<input type="submit" name="s_cancel" value="<?php echo get_lang(27); ?>" onclick="javascript: window.close();" class="fatbuttom"/>
		</td>      
	</tr>
	</table>
	</form>
<?php
kprintend();
die();
}

class userhistory
{
	function userhistory()
	{
		$this->rows = 0;
		$this->uid = -1;
		$this->filter = -1;
		$this->perpage = 18;
		$this->kpdb = new kpdbconnection();
	}

	function setuid($huid)
	{
		global $uid, $valuser;
		if ($huid != $uid)
		{
			if ($valuser->isadmin()) $this->uid = $huid;
		} else $this->uid = $huid;
	}

	function setfilter($filter)
	{
		$this->filter = $filter;
	}

	function setrows($rows)
	{
		$this->rows = $rows;
	}

	function setperpage($perpage)
	{
		if ($perpage > 0) $this->perpage = $perpage;
	}

	function show($from = 0, $to = 0)
	{
		global $cfg;
		
		$ca = new caction();
		$ca->updatelist();

		if (!$from)
		{
			$sql = 'SELECT count(*) as cnt FROM '.TBL_MHISTORY.' h, '.TBL_SEARCH.' s WHERE h.s_id = s.id and h.u_id = '.$this->uid;
			if ($this->filter != -1) $sql .= ' AND tid = '.$this->filter;
			
			$this->kpdb->setquery($sql);
			$this->kpdb->query();
			if ($this->kpdb->num() > 0)
			{
				$row = $this->kpdb->nextrow();
				$this->rows = $row['cnt'];
			}
		}		
		
		$sql = 'SELECT h.tid, h.utime, s.free, h.dpercent, s.id, s.date,h.active, s.album, s.title, s.artist FROM '.TBL_MHISTORY.' h, '.TBL_SEARCH.' s WHERE h.s_id = s.id and h.u_id = '.$this->uid;
		if ($this->filter != -1) $sql .= ' AND tid = '.$this->filter;
		$sql .= ' ORDER BY h.utime DESC';
		if ($from && $to) $sql .= ' LIMIT '.$from.','.$to; else $sql .= ' LIMIT '.$this->perpage;

		$res = db_execquery($sql, true);
		
		$options = array(
			0 => array(-1, get_lang(67)),
			1 => array(0, get_lang(183)),
			2 => array(1, get_lang(117)),
			3 => array(2, get_lang(223)),
			4 => array(3, get_lang(267)),
			5 => array(4, get_lang(331))
		);

		?>
		<form method="post" action="<?php echo PHPSELF; ?>">
		<input type="hidden" name="action" value="userhistory"/>
		<input type="hidden" name="id" value="<?php echo $this->uid; ?>"/>
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td width="12%"></td>
			<td width="58%"></td>
			<td width="20%"></td>
			<td width="10%"></td>
		</tr>
		<tr>
			<td class="wtext" valign="top" colspan="4">
				<input class="fatbuttom" type="button" onclick="javascript: location = '<?php echo PHPSELF.'?action=showusers'; ?>';" name="back" value="<?php echo get_lang(34); ?>"/>&nbsp;&nbsp;	
				<input class="fatfield" size="3" maxlength="5" type="text" name="chperpage" value="<?php echo $this->perpage; ?>"/> <?php echo get_lang(178); ?>&nbsp; <?php echo genselect('cfilter', $options, $this->filter, false, 'fatfield'); ?>&nbsp;
				<input type="submit" value="<?php echo get_lang(107) ;?>" name="Refresh" class="fatbuttom"/>
			</td>
		</tr>
		<tr>
			<td colspan="3" height="15"></td>
		</tr>	
		<?php
		
		
		$f2 = new file2();
		$kpwjs = new kpwinjs();
			
		$tidarray = array(0 => get_lang(183), 1 => get_lang(117), 2 => get_lang(223), 3 => get_lang(267), 4 => get_lang(331));	
		if ($res)
		{
			$cnt = 0;
			while ($row = db_fetch_assoc($res))
			{
				$f2->fname = $row['free'];
				$f2->id3['artist'] = $row['artist'];
				$f2->id3['album'] = $row['album'];
				$f2->id3['title'] = $row['title'];
				
				$title = file_parse($f2, '', '', '[%t - %l - %a|%f]');

				if ($row['active']) $class = 'filemarked'; else $class = 'wtext';
				if (($cnt % 2) == 0) echo '<tr class="row2nd">'; else echo '<tr>';
		
				$fd = new filedesc($f2->fname);

				if (WINDOWPLAYER && $fd->m3u) 
				{
					$link = '" onclick="javascript: '.$kpwjs->single($row['id']).' return false;';
				} else	
				{
					$link = $f2->weblink($row['id'], $row['date']);
				}	
					
				?>
					<td class="file"><?php echo $tidarray[$row['tid']]; ?></td>
					<td class="file" nowrap="nowrap"><a class="hotnb" href="<?php echo $link; ?>"><font class="<?php echo $class; ?>"><?php echo strlen($title) > 60 ? substr($title, 0, 60).' ..' : $title; ?></font></a></td>
					<td class="file"><?php echo date($cfg['dateformat'], $row['utime']); ?></td>
					<td class="file"><?php if ($row['tid'] == 0 || $row['tid'] == 1) echo $row['dpercent'].'%'; else echo '-'; ?></td>
				</tr>
				<?php
				$cnt++;
			}
		} 
		if ($cnt == 0) echo '<tr><td class="file" colspan="4">'.get_lang(10).'</td></tr>';
		?>
		</table>
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<?php
	}

	function endshow()
	{
		?>
		</table>
		</form>
		<?php
	}
}


function show_users()
{
	global $setctl, $cfg, $valuser;
	kprintheader(get_lang(121));

	$slistu = $slistr = array();

	$result = db_execquery('SELECT u_id FROM '.TBL_USERS.' ORDER BY u_login ASC');	
	while ($row = db_fetch_assoc($result))
	{
		$uid = $row['u_id'];
		$res2 = db_execquery('SELECT login FROM '.TBL_SESSION.' WHERE u_id = '.$uid.' ORDER BY login DESC LIMIT 1');
		if (db_num_rows($res2) > 0)
		{
			$row = db_fetch_assoc($res2);
			$time = $row['login'];
		} else $time = 0;

		if ($time > 0) $slistu[] = array($uid, $time);
			 else $slistr[] = array($uid, 0);	
	}

	$ulist = array();
	while (true)
	{
		$ctime = 0;
		$lrow = 0;
		for($i=0,$c=count($slistu);$i<$c;$i++)
		{
			if ($slistu[$i][1] > $ctime)
			{
				$ctime = $slistu[$i][1];
				$lrow = $i;
			}
		}
		
		if ($ctime > 0)
		{
			$ulist[] = $slistu[$lrow];
			$slistu[$lrow][1] = -1;
		} else break;
	}

	for($i=0,$c=count($slistr);$i<$c;$i++) $ulist[] = $slistr[$i];
	
	
	$cnt=0;
	?>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="15%"></td>
		<td width="23%"></td>
		<td width="20%"></td>
		<td width="10%"></td>
		<td width="32%"></td>
	</tr>
	<?php

	for ($uii=0,$uic=count($ulist);$uii<$uic;$uii++)
	{
		$result = db_execquery('SELECT * FROM '.TBL_USERS.' WHERE u_id = '.$ulist[$uii][0]);
		$row = db_fetch_assoc($result);
		
		$session = getlastlogin($row['u_id']);

		$ustatus = 0;

		if (!is_array($session))
		{
			$uip = '';
			$usertime = '';
		} else
		{
			$uip = long2ip($session['ip']);
			if ($session['logout'] == 0 && !timeout($session['login'])) $ustatus = 1;
			$usertime = date($cfg['dateformat'], $session['login']);
		}

		if ($row['u_status'] == 2) $ustatus = 2;
		
		
		if ($cnt % 2 == 0) echo '<tr class="row2nd">'; else echo '<tr>';
		$cnt++;

		$ulogin = $row['u_login'];
		
		$uname = '<font title="'.date($cfg['dateformat'],$row['created']).'"';

		if ($row['utemplate'] == 1) $template = true; else $template = false;	

		if ($template) $uname .= ' color="blue">'.get_lang(321);
			else
		if ($row['u_access'] == 0) $uname .= ' color="red">'.$row['u_name']; else $uname .= '>'.$row['u_name'];
		$uname .= '</font>';
		
		echo '<td class="file"><a class="hotnb" href="'. PHPSELF .'?action=useredit&amp;id='.$row['u_id'].'" title="'.get_lang(95).'">'.$ulogin.'</a></td>';
		echo '<td class="file">'.$uname.'</td>';
		
		echo '<td class="file"><font title="'.$usertime.'"> '.$uip. '</font></td>';

		switch ($ustatus)
		{
			case 0: $stout = get_lang(104); break;
			case 1: $stout = '<font color="red">'.get_lang(103).'</font>'; break;
			case 2: $stout = get_lang(282); break;
		}
		
		if ($row['u_booted'] == 1) $stout = get_lang(124); 

		echo '<td class="file">';
		if (!$template) echo $stout;
		echo '</td>';
		echo '<td class="file">';
		
		if ($valuser->get('u_id') != $row['u_id']) echo '<a class="hotnb" onclick="javascript: if (!confirm(\''.get_lang(175).'\')) return false;" href="'.PHPSELF.'?action=userdel&amp;id='.$row['u_id'].'" title="'.get_lang(105).'">'.get_lang(109).'</a>&nbsp;&nbsp;';
		
		if ($ustatus == 2)
		{
			echo '<a class="hotnb" href="'.PHPSELF.'?action=useractivate&amp;id='.$row['u_id'].'">'.get_lang(283).'</a>';
		} else
		{
			if (!$template) echo '<a class="hotnb" href="'.PHPSELF.'?action=userhistory&amp;id='.$row['u_id'].'" title="'.get_lang(176).'">'.get_lang(177).'</a>&nbsp;&nbsp;';
			if ($valuser->get('u_id') != $row['u_id']) echo '<a class="hotnb" href="'.PHPSELF.'?action=admineditoptions&amp;id='.$row['u_id'].'" title="'.get_lang(123).'">'.get_lang(123).'</a>&nbsp;&nbsp;';
			if ($ustatus == 1 && $valuser->get('u_id') != $row['u_id']) echo '<a class="hotnb" href="'.PHPSELF.'?action=userlogout&amp;id='.$row['u_id'].'" title="'.get_lang(106).'">'.get_lang(110).'</a>';
			if ($template) echo '<a class="hotnb" href="'. PHPSELF .'?action=newusertemplate&amp;id='.$row['u_id'].'">'.get_lang(96).'</a>&nbsp;&nbsp;';
		
		}
		echo '</td></tr>';
	}

	echo '</table>';
	echo '<form style="margin:0;padding:0" action="'.PHPSELF.'" method="post">';
	echo '<input type="hidden" name="action" value="useraction"/>';
	echo '<table width="100%" border="0" cellspacing="0" cellpadding="0">';
	echo '<tr><td height="10"></td></tr><tr><td>';
	echo '<input type="submit" value="'.get_lang(107).'" name="refresh" class="fatbuttom" />';
	echo '&nbsp;<input type="submit" value="'.get_lang(108).'" name="newuser" class="fatbuttom" />';
	echo '&nbsp;<input type="submit" value="'.get_lang(320).'" name="newtemplate" class="fatbuttom" />';
	echo '&nbsp;<input type="submit" value="'.get_lang(27).'" name="close" class="fatbuttom" onclick="javascript: window.close();" />';
	echo '</td></tr></table></form>';
	kprintend();
}

function user_saveoption($field, $value)
{
	global $valuser;
	$valuser->set($field, $value);
	$valuser->update();
}

function db_guinfo($field)
{
	global $valuser;
	return $valuser->row[$field];
}

function loadvalidated($uid, $force=false)
{
	global $valuser;
	
	if (is_numeric($uid)) 
	{
		$valuser = new kpuser();
		$valuser->load($uid);		
	}
}

class kpuser
{
	function kpuser()
	{
		$this->allowed = false;
		$this->row = false;
		$this->numerics = array('hotrows', 'searchrows', 'detailrows', 'lang', 'archer', 'theme', 'lamerate', 'pltype', 'created', 'udlrate', 'archivesize', 'u_access', 'textcut', 'dircolumn', 'utemplate');
		$this->stripslash = array('u_comment' => true, 'email' => true);
		$this->id = -1;
		$this->ched = array();
		$this->kpdb = new kpdbconnection();
	}

	function isadmin()
	{
		$u_access = $this->row['u_access'];
		if ($u_access == 0) 
		{
			return true;
		}
		return false;
	}

	function logout($cookie)
	{
		global $cfg;
		
		if ($this->id != -1)
		{
			$ckexp = explode('-', $cookie);

			if (count($ckexp) == 2 && is_numeric($ckexp[0]) && strlen($ckexp[1]) > 0)
			{	
				db_execquery('UPDATE '.TBL_SESSION.' SET logout = '.time().' WHERE sessionid = '.$ckexp[0]);
			}
		}

		switch($cfg['authtype'])
		{
			case 2: $_SESSION[$cfg['cookie']] = ''; break;
			default: setcookie($cfg['cookie'], ''); break;
		}
	}

	function setallowed($allowed)
	{
		$this->allowed = $allowed;
	}

	function loadbyuserpass($user, $pass)
	{

		$this->kpdb->preparestmt('SELECT u_id FROM '.TBL_USERS.' WHERE u_login = "?" AND u_pass = "?" AND u_booted = 0 AND u_status != 2 AND utemplate = 0', array($user, md5($pass)));
		$this->kpdb->query();
		if ($this->kpdb->num() == 1)
		{
			$row = $this->kpdb->nextrow();
			if ($this->load($row['u_id'])) return true;
		} 
		return false;
	}

	function loadbymd5($user, $pass)
	{
		$this->kpdb->preparestmt('SELECT u_id FROM '.TBL_USERS.' WHERE MD5(u_login) = "?" AND u_pass = "?" AND u_booted = 0 AND u_status != 2 AND utemplate = 0', $user, $pass);
		$this->kpdb->query();
		if ($this->kpdb->num() == 1)
		{
			$row = $this->kpdb->nextrow();
			if ($this->load($row['u_id'])) return true;
		} 
		return false;
	}		

	function load($id)
	{
		if (is_numeric($id) && $id != -1)
		{
			$res = db_execquery('SELECT * FROM '.TBL_USERS.' WHERE u_id = '.$id);
			if (db_num_rows($res) == 1) 
			{
				$this->row = db_fetch_assoc($res); 
				$this->id = $id;
				return true;
			}
		}
		return false;		
	}

	function getid()
	{
		return $this->id;
	}

	function arrsearch($needle, $array)
	{
		for ($i=0,$c=count($array);$i<$c;$i++) if ($needle == $array[$i]) return true; 
	}

	function validate($name, $value, $strict = false)
	{
		if ($strict && !$this->arrsearch($name, $this->allowed)) return false;

		if ($this->arrsearch($name, $this->numerics)) 
		{
			if (!is_numeric($value)) return false; 
		}
		
		switch($name)
		{
			case 'dircolumn': if ($value < 0 || $value > 8) return false;
							break;
			
			
			case 'textcut': if ($value < 40) return false;
							break;
			
			case 'detailrows':
			case 'hotrows':
			case 'searchrows':
							if ($value <= 0) return false;
							break;
			
			default: break;
		}
		return true;
	}

	function set($name, $value, $strict=false)
	{
		if ($this->validate($name, $value, $strict)) 
		{
			$this->ched[$name] = true;
			if (isset($this->stripslash[$name])) $this->row[$name] = stripcslashes($value);
					else $this->row[$name] = $value;			
		}
	}

	function setro($name, $value)
	{
		$this->row[$name] = $value;			
	}

	function get($name)
	{
		if (isset($this->row[$name])) return $this->row[$name]; 			
	}

	function gensql($type, $changesonly = true)
	{
		switch($type)
		{
			case 1: $sql = 'UPDATE'; break;
			case 2: $sql = 'INSERT INTO'; break;
		}

		$sql .= ' '.TBL_USERS.' SET ';

		$cnt = 0;
		$addc = false;
		foreach($this->row as $name => $value)
		{
			$cnt++;
			if ($name == 'u_id') continue;
			if (!isset($this->ched[$name]) && $changesonly) continue;
			if ($addc) $sql .= ', ';
			$sql .= $name.' = "'.myescstr($value).'"';
			$addc = true;
		}
		return $sql;		
	}

	function update($changesonly = true)
	{
		if ($this->id != -1)
		{
			$sql = $this->gensql(1, $changesonly);		
			$sql .= ' WHERE u_id = '.$this->id;
			$res = db_execquery($sql);
			if ($res !== false) return true;
		}
		return false;
	}

	function store($changesonly = true)
	{
		$sql = $this->gensql(2, $changesonly);
		$res = db_execquery($sql);		
		if ($res !== false) 
		{
			$this->load(db_insert_id());
			return true;
		}
	}
}

function save_useroptions($uid)
{
	global $u_id, $deflanguage;
	$state = 0;

	$kpu = new kpuser();
	$kpu->setallowed(array('extm3u', 'plinline', 'hotrows', 'searchrows', 'detailrows', 'lang', 'archer', 'lamerate', 'theme', 'email', 'u_pass', 'pltype', 'textcut', 'dircolumn'));
	if ($kpu->load($uid))
	{
		if (frm_isset('changepass') && strlen(frm_get('password')) > 0)
		{		
			if (frm_isset('curpassword'))
			{
				if (db_guinfo('u_pass') == md5(frm_get('curpassword')))
				{
					$state = 2;
					$kpu->set('u_pass', md5(frm_get('password')));
				} else $state = 3;
			}
		}
		$kpu->set('extm3u', 0);
		$kpu->set('plinline', 0);
		foreach($_POST as $name => $value) $kpu->set($name, $value, true);		
		$kpu->update();
	}
	if ($uid == $u_id)
	{
		loadvalidated($uid, true);
		$deflanguage = db_guinfo('lang');
	}
	return $state;	
}

function show_useroptions($admin=false, $id, $msg='', $reload = false)
{
	global $klang, $deflanguage, $lamebitrates, $setctl, $cfg;
	
	$result = db_execquery('SELECT * from '.TBL_USERS.' WHERE u_id = '.$id);
	if ($result) $row = db_fetch_assoc($result);
	if (!$row) die();
	
	if ($row['extm3u'] == 1) $ext3mu = 'checked="checked"'; else $ext3mu = '';
	if ($row['plinline'] == 1) $plinline = 'checked="checked"'; else $plinline = '';
	if ($row['utemplate'] == 1) $template = true; else $template = false;
		
	kprintheader(get_lang(123));
	?>
	<form name="useroptions" method="post" action="<?php echo PHPSELF; ?>">
	<?php
		if ($admin) echo '<input type="hidden" name="action" value="saveadminuseroptions"/>'; else echo '<input type="hidden" name="action" value="saveuseroptions"/>';
	?>
	<input type="hidden" name="id" value="<?php echo $id; ?>"/>
	<table width="100%" border="0" cellspacing="1" cellpadding="0">	
	<tr>
		<td width="45%"></td>
		<td width="45%"></td>
		<td width="10%"></td>
	</tr>
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
		<td class="wtext"><?php echo get_lang(214); ?></td>
		<td><?php 
					
				$pltypes = array(0 => array(1, get_lang(294)), 1 => array(2, get_lang(295)));
				if (class_exists('kpwimpygen')) $pltypes[] = array(3, 'wimpy');
				if (class_exists('m3ugendisk')) $pltypes[] = array(4, 'm3udisk');
				if (UTF8MODE) $pltypes[] = array(5, 'm3u8');
				if ($cfg['xspf_enable']) $pltypes[] = array(6, 'xspf');
				if ($cfg['jw_enable'])
				{
					$pltypes[] = array(7, 'jw');
					if (AJAX) $pltypes[] = array(8, 'jw (enqueue)');
				}

				if ($cfg['jw6_enable']) $pltypes[] = array(9, 'jw6');

				echo genselect('pltype', $pltypes, $row['pltype']); 
			?></td>
		<td class="wtext"><?php echo helplink('pltype'); ?></td>
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
		<td class="wtext"><?php echo get_lang(339); ?></td>
		<td><input type="text" maxlength="3" size="3" class="fatbuttom" value="<?php echo $row['detailrows']; ?>" name="detailrows"/></td>
		<td class="wtext"><?php echo helplink('odetailedviews'); ?></td>
    </tr>
	<tr>	
		<td class="wtext"><?php echo get_lang(318); ?></td>
		<td><input type="text" maxlength="3" size="3" class="fatbuttom" name="textcut" value="<?php echo $row['textcut']; ?>"/></td>
		<td class="wtext"><?php echo helplink('otextcut'); ?></td>
	</tr>
	<tr>	
		<td class="wtext"><?php echo get_lang(319); ?></td>
		<td><input type="text" maxlength="3" size="3" class="fatbuttom" name="dircolumn" value="<?php echo $row['dircolumn']; ?>"/></td>
		<td class="wtext"><?php echo helplink('odircolumn'); ?></td>
	</tr>
	<tr>
		<td class="wtext"><?php echo get_lang(122); ?></td>
		<td><?php echo get_lang_combo($row['lang'], 'lang'); ?></td>
		<td></td>
    </tr>	
	<?php
	if (!$admin)
	{
		?>
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
	<?php
	}
	?>
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
			<?php
			
			if ($row['forcelamerate'] != 0) 
			{
				$disable = true; 
				$rate = $row['forcelamerate'];
			} else 
			{
				$disable = false;
				$rate = $row['lamerate'];
			}
						
			$options = array(0 => array(0, get_lang(221)));
			for ($i=1;$i<count($lamebitrates);$i++) $options[] = array($i, $lamebitrates[$i]);
			echo genselect('lamerate', $options, $rate, $disable);
			?>
		</td>
		<td class="wtext"><?php echo helplink('olamerate'); ?></td>
	</tr>
	<?php
	}
	?>
	<tr>
		<td class="wtext"><?php echo get_lang(288); ?></td>
		<td>
			<select name="theme" class="fatbuttom">
			<?php
				$kpt = new kptheme();
				$kpt->load();
				echo $kpt->select($row['theme']); 
			?>
			</select>
		</td>
		<td></td>
	</tr>
	<?php
	if (!$template)
	{
	?>
	<tr>	
		<td class="wtext"><?php echo get_lang(223); ?></td>
		<td><input type="text" maxlength="128" size="30" class="fatbuttom" name="email" value="<?php echo $row['email']; ?>"/></td>
		<td class="wtext"><?php echo helplink('oemail'); ?></td>
	</tr>
	<?php
	}
	?>
	<tr><td colspan="3" height="10"></td></tr>
	<tr>
		<td>
		<input class="fatbuttom" type="submit" name="save" value="<?php echo get_lang(45); ?>"/>&nbsp;
		<?php if ($admin)
		{
		?>
		<input type="submit" name="cancel" value="<?php echo get_lang(34); ?>" class="fatbuttom"/>
		<?php
		} else
		{
		?>
		<input class="fatbuttom" type="button" name="closeme" value="<?php echo get_lang(27); ?>" onclick="javascript: window.close(); <?php 
			if ($reload) echo 'window.opener.location.reload();'; ?>"/>		
		<?php
		}
		?>
		</td>
		<td colspan="2" align="right" class="wtext"><?php echo get_lang(191); ?>&nbsp;</td>
	</tr>
	</table>
	</form>
	</body>
	</html>
	<?php	
}

class swfront
{
	function swfront()
	{
		global $valuser;

		$this->obj = false;

		switch($valuser->get('pltype'))
		{
			case 6: 
				if (class_exists('kpxspf')) $this->obj = new kpxspf(); 
			break;
			
			case 7:
			case 8: 
				if (class_exists('jwplayer')) $this->obj = new jwplayer(); 
			break;
			
			case 9: 
				if (class_exists('jwplayer6')) $this->obj = new jwplayer6(); 
			break;
		}

		$this->data = '';

		if ($this->obj) return true;
	}

	function xml_link($sid)
	{
		$this->obj->xml_link($sid);
	}

	function flashhtml()
	{
		$this->obj->flashhtml();
	}

	function flashhtml_enqueue()
	{
		$this->obj->flashhtml_enqueue();
	}

	function xml_top()
	{
		$this->crlf = "\r\n";
		$this->data = '<?xml version="1.0" encoding="';
		if (UTF8MODE) $this->data .= 'UTF-8'; else $this->data .= 'ISO-8859-1';
		$this->data .= '"?>'.$this->crlf;
		$this->data .= '<playlist version="1" xmlns="http://xspf.org/ns/0/">'.$this->crlf;
		$this->data .= '<trackList>'.$this->crlf;
	}

	function genxmlfile($uid, $encode=false)
	{	
		$this->xml_top();

		$result = db_execquery('SELECT sid FROM '.TBL_TEMPLIST.' WHERE uid = '.$uid.' ORDER BY rid ASC');
		while ($row = db_fetch_row($result)) $this->obj->xml_link($row[0], $encode);	
	
		$this->data .= $this->obj->getdata();


		
		$this->data .= '</trackList>'.$this->crlf.'</playlist>';

		header('Content-Disposition: attachment; filename=kp'.lzero(getrand(1,999999),6).'.xml');
		header('Content-Type: text/xml');
		header('Content-Length: '.strlen($this->data));
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

		echo $this->data;
	}

	function write($sids)
	{
		global $u_id;
		db_execquery('DELETE FROM '.TBL_TEMPLIST.' WHERE uid = '.$u_id);
		for($i=0,$c=count($sids);$i<$c;$i++) db_execquery('INSERT INTO '.TBL_TEMPLIST.' SET uid = '.$u_id.', sid = '.$sids[$i]);
	}
}


function grpsql($prepend='')
{
	global $cfg;

	$out = '';
	foreach($cfg['albumartistgroup'] as $name)
	{
		if (empty($out)) $out .= 'GROUP BY '.$prepend.$name;
			else $out .= ', '.$prepend.$name;		
	}
	return $out;
}

function showviewform()
{
	$val[0] = $val[1] = '';
	if (db_guinfo('detailview')) $val[1] = ' selected="selected"';
		else $val[0] = ' selected="selected"';

	$out  = get_lang(338).' &nbsp;';
	$out .= '<select style="width:90px" name="viewmode" class="fatbuttom">';
	$out .=	'<option value="0"'.$val[0].'>'.get_lang(340).'</option>';
	$out .=	'<option value="1"'.$val[1].'>'.get_lang(341).'</option>';
	$out .= '</select>';

	return $out;
}

class genlist
{
	function genlist()
	{
		$this->rows = 0;
		$this->query = '';
		$this->headertext = '';
		$this->ximg = '';
		$this->ndir = '';
		$this->extra = '';		
		$this->special = 0;
		$this->from = 0;
	}

	function setrows($rows)
	{
		$this->rows = $rows;		
	}

	function genrelist($from=0,$to=0)
	{
		global $cfg, $bd;
		$this->query = 'SELECT *,count(free) as titles, sum(lengths) as lengths FROM '.TBL_SEARCH.' WHERE genre = '.db_guinfo('defgenre').$bd->genxdrive().' AND length(trim(album)) > 0 GROUP BY album HAVING titles > '.$cfg['titlesperalbum'].' ORDER BY artist ASC';

		if ($from && $to) $this->query .= ' LIMIT '.$from.','.$to;	
		$this->header = get_lang(147);
		$this->ndir = get_lang(153, gengenres(db_guinfo('defgenre')));
	}

	function hotselect($chars,$from=0,$to=0)
	{
		global $cfg, $bd;
		
		for ($ci=0,$cc=kp_strlen($chars),$xsql='';$ci<$cc;$ci++)
		{
			$char = kp_substr($chars, $ci, 1);

			if ($ci > 0) $xsql .= ' AND ';

			$cis = $ci + 1;

			switch($char)
			{
				case '*': 
						$xsql .= 'SUBSTRING(RTRIM(artist), '.$cis.', 1) NOT REGEXP("^[0-9a-zA-Z]")';
						break;

				case '0': 
						$xsql .= '(';
						for ($i=0;$i<10;$i++) 
						{
							$xsql .= 'SUBSTRING(RTRIM(artist), '.$cis.', 1) = "'.$i.'"';
							if ($i < 9) $xsql .= ' OR ';
						}
						$xsql .= ')';									
						break;

				default: 
						$xsql .= 'SUBSTRING(RTRIM(artist), '.$cis.', 1) = "'.myescstr($char).'"';
						break;
			} 
		}

		$this->query  = 'SELECT *,COUNT(*) AS titles, SUM(lengths) AS lengths FROM '.TBL_SEARCH.' WHERE '.$xsql.$bd->genxdrive();	
		$this->query .= ' AND LENGTH(RTRIM(album)) > 0 GROUP BY RTRIM(album),RTRIM(artist) HAVING titles > '.$cfg['titlesperalbum'].' ORDER BY artist';
		if ($from && $to) $this->query .= ' LIMIT '.$from.','.$to;

		$level = strlen($chars) + 1;
		$this->querylist  = 'SELECT LOWER(SUBSTRING(artist,'.$level.',1)) AS ch, COUNT(*) as cnter FROM '.TBL_SEARCH.' WHERE TRIM(album) != "" AND '.$xsql.$bd->genxdrive();
		$this->querylist .= ' GROUP BY SUBSTRING(artist,'.$level.',1)';

		$qres = db_execquery($this->querylist, true);
		$charslist = '';
		while ($row = db_fetch_row($qres)) $charslist .= $row[0];
		if (kp_strlen($charslist) < 2) $charslist = '';

		$ha = new hotalbum($charslist, true);
		$hotlist = $ha->generate();

		$hotlinks = '';
		for ($i=0,$c=count($hotlist);$i<$c;$i++)
		{
			$active = $hotlist[$i][1];
			$char = $hotlist[$i][0];
			if (!$hotlist[$i][2]) 
			{
				if ($active) $hotlinks .= '<a href="'.PHPSELF.'?action=hotselect&amp;artist='.$chars.$char.'" class="hot">'.$char.'</a>';
					else $hotlinks .= '<font class="loginkplaylist">'.$char.'</font>';
				
				$hotlinks .= '&nbsp;';
			}
		}
	
		$this->control();

		$links = '';
		for ($i=0,$c=kp_strlen($chars);$i<$c;$i++) $links .= '<a href="'.PHPSELF.'?action=hotselect&amp;artist='.kp_substr($chars, 0, $i + 1).'" class="hot">'.kp_substr($chars, $i, 1).'</a>&nbsp;'; 

		$this->extra .= '<tr><td height="5"></td></tr><tr><td><font class="notice">&nbsp;'.get_lang(351, $links).'</font>';
		$this->extra .= '</td></tr><tr><td height="10"></td></tr><tr><td><font class="notice">&nbsp;'.get_lang(350, $hotlinks).'</font>';
		$this->extra .= '</td></tr><tr><td height="10"></td></tr>';


		$this->headertext = get_lang(31, $chars);
		$this->ndir = get_lang(30, $chars);
	}

	function whats_hot($filter=0,$from=0,$to=0)
	{
		global $cfg, $bd;
		
		$this->from = $from;
		$this->ndir = get_lang(3);
		
		$lastcheck = mktime(0, 0, 0, date('n'), 1, date('Y'));
		$days = array(1 => 31, 2 => 28, 3 => 31, 4 => 30, 5 => 31, 6 => 30, 7 => 31, 8 => 31, 9 => 30, 10 => 31, 11 => 30, 12 => 31);

		$res = db_execquery('SELECT MIN(utime) FROM '.TBL_MHISTORY);
		$row = db_fetch_row($res);
		$uxstart = (int)$row[0];		
		
		$speriods = $periods = array();
		$cnt = 5;
		if ($uxstart < $lastcheck && $uxstart > 0)
		{
			while ($uxstart < $lastcheck)
			{
				$pr = date('Ym', $uxstart);
				$pry = date('Y', $uxstart);
				if (!isset($periods[$pry])) 
				{
					$periods[$pry] = array($cnt, date('Y', $uxstart));
					$cnt++;
				}
				if (!isset($periods[$pr])) 
				{
					$periods[$pr] = array($cnt, date($cfg['dateformatwhatshot'], $uxstart));
					$cnt++;
				}
				$uxstart += 2332800;
			}
		}

		$periods = array_reverse($periods, true);		
	
		if ($filter == 0) $filter = db_guinfo('hotmode');

		$uxfrom = 0;
		$uxto = time();

		$found = false;
		
		if ($filter >= 5)
		{
			foreach($periods as $pr => $val) 
			{
				if ($val[0] == $filter)
				{
					if (strlen($pr) == 4) // year
					{
						$found = true;
						$uxfrom = mktime(0, 0, 0, 1, 1, $pr);
						$uxto = mktime(23, 59, 59, 12, 31, $pr);
						$this->ndir .= ' '.$val[1];
					} else
					{
						$found = true;
						$y = substr($pr, 0, 4);
						$m = substr($pr, 4, 2);
						$uxfrom = mktime(0,0,0, $m, 1, $y);
						if (($y % 4 == 0) && $m == '02') $eday = 29; else $eday = $days[(int)$m];
						$uxto = mktime(0,0,0, $m, $eday, $y);
						$this->ndir .= ' '.$val[1];
					}
				}
			}
		}

		$this->special = 3;

		switch ($filter)
		{
			case 1:
				$uxfrom = mktime(0, 0, 0, date('n'), date('j') - 7, date('Y'));
				$uxto = mktime(23, 59, 59, date('n'), date('j'), date('Y'));
				$this->ndir .= ' '.get_lang(240);
				break;
			case 2:
				$uxfrom = mktime(0, 0, 0, date('n'), date('j') - 31, date('Y'));
				$uxto = mktime(23, 59, 59, date('n'), date('j'), date('Y'));
				$this->ndir .= ' '.get_lang(241);
				break;
		}

		$this->query = 'SELECT s.album, s.artist, s.dirname, count(*) as hits FROM '.TBL_MHISTORY.' h, '.TBL_SEARCH.' s WHERE ';
		if ($uxfrom != 0) $this->query .= 'h.utime >= '.$uxfrom.' AND h.utime <= '.$uxto.' AND ';
		$this->query .= 'trim(s.album) != ""'.$bd->genxdrive('s.drive').' AND h.s_id = s.id GROUP BY s.album,s.artist HAVING hits >= '.$cfg['whatshotminimumhits'].' ORDER BY hits DESC';

		if ($from && $to) $this->query .= ' LIMIT '.$from.','.$to;

		$fsel = array(0 => '', 1 => '', 2 => '');
		$fsel[$filter] = ' selected="selected"';

		$extra = '<tr><td height="5"></td></tr><tr><td class="notice">'.get_lang(238).':&nbsp; ';		
		$extra .= '<select name="hotperiod" class="fatbuttom">';
		$extra .= '<option value="0"'.$fsel[0].'>'.get_lang(239).'</option>';
		$extra .= '<option value="1"'.$fsel[1].'>'.get_lang(240).'</option>';
		$extra .= '<option value="2"'.$fsel[2].'>'.get_lang(241).'</option>';
			
		foreach($periods as $pr => $val)
		{
			$extra .= '<option value="'.$val[0].'"';
			if ($filter == $val[0]) $extra .= ' selected="selected"';
			$extra .= '>'.$val[1].'</option>';
		}

		$extra .= '</select>&nbsp; &nbsp;';
	
		$extra .= showviewform();

		$extra .= ' &nbsp;<input type="submit" class="fatbuttom" name="hotoptions" value="'.get_lang(107).'"/>';	
		$extra .= '</td></tr><tr><td height="10"></td></tr>';
		$this->extra = $extra;
		$this->headertext = get_lang(3);
	}

	function control($addition='')
	{
		$this->extra .= '<tr><td height="5"></td></tr><tr><td class="notice">';
		$this->extra .= showviewform();
		$this->extra .= ' &nbsp;<input type="submit" class="fatbuttom" name="chlistoption" value="'.get_lang(107).'"/>';	
		$this->extra .= '</td></tr>';
		$this->extra .= '<tr><td height="5"></td></tr>';
	}

	function whats_new($from=0, $to=0)
	{
		global $setctl, $phpenv, $bd;

		$this->query = 'SELECT id,drive,dirname,fsize,date,free,album,artist,xid,COUNT(*) as titles,SUM(lengths) as lengths, year, genre, fpath, fname FROM '.TBL_SEARCH.' WHERE trim(album) != ""'.$bd->genxdrive().' '.grpsql().' ORDER BY date DESC';

		if ($from && $to) $this->query .= ' LIMIT '.$from.','.$to; else
			if ($to)  $this->query .= ' LIMIT '.$to;

		$this->headertext = get_lang(4);
		
		if ($setctl->get('publicrssfeed')) $this->ximg = '&nbsp;<a href="'.$setctl->get('streamurl').$phpenv['streamlocation'].'?whatsnewrss=rss.xml"><img src="'.getimagelink('rss.gif').'" border="0" alt="RSS"/></a>';
					
		$this->ndir = get_lang(4);
		$this->special = 2;
		$this->extra = '';		
	}
	
	function outrss()
	{
		global $cfg, $setctl, $phpenv;
		$result = db_execquery($this->query);
		$rss = new krss(get_lang(4));

		while ($row = db_fetch_assoc($result)) 
		{
			$f2 = new file2($row['id'], false, $row);
			$albumlink = $setctl->get('streamurl').$phpenv['streamlocation'].'?d='.$row['drive'].'&amp;pwd='.webpdir($f2->relativepath);

			switch ($this->special)
			{
				case 2: $rss->additem(date($cfg['smalldateformat'], $row['date']).' - '.$row['artist'].' - '.$row['album'], '', $albumlink, $row['date']);
						break;
			}
		}
		$rss->ship();
	}

	function nhghlist($extra = '')
	{
		global $cfg;	
		
		showdir('',$this->ndir,0, $this->ximg);

		echo '<table width="100%" cellspacing="0" cellpadding="0" border="0">';
		if (strlen($this->extra) == 0) $this->control();
		echo $this->extra;
		echo '</table>';
		echo '<table width="100%" cellspacing="0" cellpadding="0" border="0">';

		if (db_guinfo('detailview')) $max = db_guinfo('detailrows'); else $max = db_guinfo('hotrows');

		$result = db_execquery($this->query, false); 
		if (!$this->rows) $this->rows = db_num_rows($result);

		$cntr= $this->from;
		$cnt = 0;			
		
		$rowsdata = array();

		while ($row = db_fetch_assoc($result)) 
		{			
			$cnt++;
			if ($cnt <= $max) $rowsdata[] = $row; else break;		
		}
	

		for ($i=0,$c=count($rowsdata);$i<$c;$i++)
		{
			$row = $rowsdata[$i];
				
			if (isset($row['hits'])) $hits = $row['hits']; else $hits = 0;
			
			if (!isset($row['lengths']))
			{
				$sql = 'SELECT *,count(*) as titles, sum(lengths) as lengths FROM '.TBL_SEARCH.' WHERE dirname = "'.myescstr($row['dirname']).'" AND artist = "'.myescstr($row['artist']).'" AND album = "'.myescstr($row['album']).'" GROUP BY dirname';
				$result2 = db_execquery($sql);
				$row = db_fetch_assoc($result2);
			}					
			
			$f2 = new file2($row['id'], false, $row);			
			$dir = $f2->relativepath;

			$ainf = gen_aheader($row['album'], $row['artist'], $row['lengths'], $row['titles'], $row['year'], $row['genre']);		

			switch ($this->special)
			{
				case 0: print_album($row['drive'],$row['artist'].' - '.$row['album'], $dir, $ainf, '', 0, $row['id']); 
						break;

				case 1: print_album($row['drive'],' '.lzero($cntr+1).'. '.$row['artist'].' -  '.$row['album'], $dir, $ainf, '', $hits, $row['id']);							
						$cntr++;	
						break;

				case 2: print_album($row['drive'],date($cfg['dateformat'],$row['date']).' - '.$row['artist'].' - '.$row['album'], $dir, $ainf, '', 0, $row['id']); 
						break;				
				
				case 3: print_album($row['drive'], ' '.lzero($cntr+1).'. '.$row['artist'].' -  '.$row['album'], $dir, $ainf, '', $hits, $row['id']);							
						$cntr++;
						break;
			}
		}
		echo '</table>';

		if ($cnt == 0) echo '<tr><td><font class="fdet">'.get_lang(10).'</font></td></tr>';		

	}

	function endlist()
	{
		endmp3table(0);
	}
}

function genliststart($id)
{
	$gl = new genlist();
	switch($id)
	{
		case 3: $gl->whats_new(); break;
		case 4: $gl->whats_hot(); break;
		case 6: $gl->genrelist(); break;
	}
	
	$kpd = new kpdesign();
	$kpd->top(true, $gl->headertext);
	
	$gl->nhghlist();
	$nv = new navi($id, $gl->rows, true);
	$nv->writenavi();
	$gl->endlist();
	$kpd->bottom();
}

function hotselect($chars='')
{
	$gl = new genlist();
	$gl->hotselect($chars);
	$kpd = new kpdesign();
	$kpd->top(true, $gl->headertext);
	$gl->nhghlist();
	$nv = new navi(5, $gl->rows, true);
	$nv->setfollow('hchar', $chars);
	$nv->writenavi();
	$gl->endlist();
	$kpd->bottom();
}

function updatehotlist()
{
	global $bd;
	$hotsels = '';
	$qres = db_execquery('SELECT LOWER(SUBSTRING(artist,1,1)) AS ch FROM '.TBL_SEARCH.' WHERE TRIM(album) != "" AND TRIM(artist) != ""'.$bd->genxdrive().' GROUP BY SUBSTRING(artist,1,1)', true);
	while ($row = db_fetch_row($qres)) $hotsels .= $row[0];
	updatecache(10, $hotsels);
	return $hotsels;
}

function cache_updateall()
{
	updategenre();
	updatehotlist();
	updatestatistics();
	$kpal = new genkpalbum();
	$kpal->gencache();
}

class hotalbum
{
	function hotalbum($chars='', $custom=false)
	{
		global $cfg;
		$this->chars = $chars;
		$this->displaychars = $cfg['hotselectchars'];

		if (!$custom)
		{
			if (!getcache(10, $this->chars) || $cfg['userhomedir']) $this->chars = updatehotlist();
		}

		$this->chlen = kp_strlen($this->chars);
		$this->displen = kp_strlen($this->displaychars);

		$this->pixels = floor(100 / $this->displen);
	}

	function generate()
	{
		$chlist = $alf = $hotlist = array();
		for ($i=0;$i<$this->displen;$i++) $alf[] = kp_substr($this->displaychars, $i, 1);

		for ($i=0,$c=kp_strlen($this->chars);$i<$c;$i++) 
			if (is_numeric(kp_substr($this->chars, $i, 1))) $chlist['0'] = true; 
				else 
			$chlist[kp_substr($this->chars, $i, 1)] = true;

		for ($i=0,$c=count($alf);$i<$c;$i++)
		{
			$active = $linebreak = false;
			$char = $alf[$i];

			switch($char)
			{
				case '_':
						$linebreak = true;
						break;
				
				case '*':
						foreach ($chlist as $tch => $val) if (!in_array($tch, $alf)) $active = true;
						break;
			
				default:
						if (isset($chlist[$char])) $active = true;
						break;
			}	
			
			$hotlist[] = array($char, $active, $linebreak);
		}
		return $hotlist;
	}

	function html($prep='')
	{
		$hotlist = $this->generate();

		$out = $this->top();
		for ($i=0,$c=count($hotlist);$i<$c;$i++)
		{
			$char = $hotlist[$i][0];
			$active = $hotlist[$i][1];
			$break = $hotlist[$i][2];

			if ($break) $out .= '</tr><tr>';
			else
			{
				if ($active)
					$out .= '<td align="center" width="'.$this->pixels.'%"><a href="'.PHPSELF.'?action=hotselect&amp;artist='.$prep.$char.'" class="hot">'.$char.'</a></td>'; 
				else
					$out .= '<td align="center" width="'.$this->pixels.'%"><font class="loginkplaylist">'.$char.'</font></td>';				
			}
		}
		$out .= $this->bottom();
		return $out;
	}

	function top()
	{
		return '<table width="100%" cellpadding="0" cellspacing="1" border="0"><tr>';
	}

	function bottom()
	{
		return '</tr></table>';
	}	
}

function updategenre()
{
	global $bd;
	$res = db_execquery('SELECT genre,count(*) as cnt FROM '.TBL_SEARCH.' WHERE genre != 255 AND TRIM(album) != ""'.$bd->genxdrive().' GROUP BY genre ORDER BY genre', true);
	$data = '';
	while ($row = db_fetch_row($res)) $data .= $row[0].'-'.$row[1].',';
	updatecache(20, $data);
	return $data;
}

function genre_select($top = true, $default)
{
	global $cfg;
	$glist = $glistid = $glistcnt = array();
	$genredata = '';
	if (!getcache(20, $genredata) || $cfg['userhomedir']) $genredata = updategenre();

	$genrenames = gengenres();

	if ($top) $genres = '<select style="width:170px" name="genreno" class="fatbuttom">'; else $genres = '';
	
	$genrerows = explode(',', $genredata);
	
	$cnt = 0;
	for ($i=0,$c=count($genrerows);$i<$c;$i++)
	{
		$ln = explode('-', $genrerows[$i]);
		if (count($ln) == 2)
		{
			if (isset($genrenames[$ln[0]]))	
			{
				$gname = $genrenames[$ln[0]];
				$glist[$cnt] = checkchs($gname, false);
				$glistid[$cnt] = $ln[0];
				$glistcnt[$cnt] = $ln[1];
				$cnt++;
			}
		}
	}

	if ($cnt > 0)
	{
		array_multisort($glist, $glistid, $glistcnt, SORT_STRING);
		for ($i=0;$i<$cnt;$i++)
		{
			$selected = false;
			if (is_array($default))
			{
				foreach($default as $name) if ($name == $glistid[$i]) $selected = true;
			} else if ($glistid[$i] == $default) $selected = true;
			if ($selected)
			$genres .= '<option value="'.$glistid[$i].'" selected="selected">'.$glist[$i].' ('.$glistcnt[$i].')</option>'; 
				else
			$genres .= '<option value="'.$glistid[$i].'">'.$glist[$i].' ('.$glistcnt[$i].')</option>';
		}
	} else $genres .= '<option value="255">'.get_lang(10).'</option>';
	if ($top) $genres .= '</select>';
	return $genres;
}

function nextch($ssearch,$pos)
{
	for ($i=$pos,$c=strlen($ssearch);$i<$c;$i++) 
		if ($ssearch[$i] != ' ') return $i-1;
	return strlen($ssearch);
}

class kpsearch
{
	function kpsearch()
	{
		global $ud;
		
		$this->id3 = db_guinfo('defaultid3');
		$this->orsearch = db_guinfo('orsearch');
		$this->hitsas = db_guinfo('hitsas');
		$this->where = db_guinfo('defaultsearch');
		$this->what = trim(frm_get('searchtext'));
		$this->files = 0;
		$this->query = '';
		$this->mwritten = 0;
		$this->rows = 0;
	}

	function setrows($rows)
	{
		$this->rows = $rows;
	}

	function getwords($text)
	{
		if (empty($text)) return false;

		$i2 = $quote = $squote = 0;
		$words = array(0 => '');
	
		$squotes = substr_count ($text, "'");
		$dblquotes = substr_count ($text, '"');

		$chars = 0;

		for ($i=0,$c=strlen($text);$i<$c;$i++)	
		{		
			switch ($text[$i])
			{
				case ';':	break;			
				
				case ' ':	if (!$quote && !$squote)
							{
								$i2++;
								$words[$i2] = '';
								$i = nextch($text,$i);
								break;
							} else $words[$i2] .= $text[$i];
				case '"':	$dblquotes--;
							if ($quote) 
							{
								$quote = 0; 
								break;
							} else if ($dblquotes > 0) 
							{
								$quote = 1; 
								break;
							}
							break;
				case "'":	$squotes--;
							if ($squote) 
							{
								$squote = 0; 
								break;
							} else if ($squotes > 0) 
							{
								$squote = 1; 
								break;
							}
				default:	$chars++;
							$words[$i2] .= $text[$i];
							break;						
			}		
		}

		$nwords = array();
		for ($i2=0;$i2<2;$i2++)
		{
			for ($i=0,$c=count($words);$i<$c;$i++)
			{
				switch($words[$i][0])
				{
					case '-': if ($i2 == 1) $nwords[] = $words[$i]; break;
					default:
					case '+': if ($i2 == 0) $nwords[] = $words[$i]; break;					
				}
			}
		}	
		
		if (!$chars) return false; else return $nwords;
	}
	
	function gensearchsql($from=0, $to=0)
	{
		global $bd;
		
		$subquery = ' (';
		
		$words = $this->getwords($this->what);
		$pluscnt=0;
			
		for ($i=0,$c=count($words);$i<$c;$i++)
		{
			switch ($words[$i][0])
			{
				case '-':	$search = myescstr(substr($words[$i],1));
							$ident = 'NOT LIKE';
							$plus = false;
							break;
				case '+':	$search = myescstr(substr($words[$i],1));
							$ident = 'LIKE';
							$plus = true;
							break;
				default:	$search = myescstr($words[$i]);
							$ident = 'LIKE';
							$plus = true;
							break;
			}
		
			if ($i > 0) 
			{
				if (!$plus && $pluscnt > 0) $subquery .= ') ';
				if ($this->orsearch && $plus) $subquery .= ' OR '; else $subquery .= ' AND ';
				if (!$plus && $pluscnt > 0) 
				{
					$subquery .= '( ';
					$pluscnt = 0;
				}
			}
			if ($plus) $pluscnt++;
			switch ($this->where)
			{
				case 0: if (!$this->id3) $subquery .= 'concat(album,dirname,free) '.$ident.' "%'.$search.'%"'; else
						$subquery .= 'album '.$ident.' "%'.$search.'%"';
						break;
				case 1: if (!$this->id3) $subquery .= 'concat(title,dirname,free) '.$ident.' "%'.$search.'%"'; else
						$subquery .= 'title '.$ident.' "%'.$search.'%"';
						break;
				case 2: if (!$this->id3) $subquery .= 'concat(artist,dirname,free) '.$ident.' "%'.$search.'%"'; else
						$subquery .= 'artist '.$ident.' "%'.$search.'%"';
						break;
				case 3: if (!$this->id3) $subquery .= 'concat(album,artist,title,dirname,free) '.$ident.' "%'.$search.'%"'; else
						$subquery .= 'concat(album,artist,title) '.$ident.' "%'.$search.'%"';
						break;
			}		
		}
		$subquery .= ')';

		if ($this->hitsas == 1) $extra = ',COUNT(free) AS titles, SUM(lengths) AS lengths'; else $extra = '';
		
		$query = 'SELECT *'.$extra.' FROM '.TBL_SEARCH.' WHERE f_stat = 0 AND '.$subquery;
		
		if ($this->hitsas == 1) $query .= ' AND LENGTH(rtrim(album)) > 0 GROUP BY RTRIM(album),RTRIM(artist)';

		$query .= $bd->genxdrive();
		
		$query .= ' ORDER BY dirname, free ASC';

		if ($from && $to) $query .= ' LIMIT '.$from.','.$to;

		$this->query = $query;

		return $this->query;
	}

	function viewsearch()
	{
		global $cfg;

		$kqm = new kq_Measure();
		$kqm->start();
		if (!$this->rows) $result = db_execquery($this->query); else $result = db_execquery($this->query);
		$kqm->stop();

		if (!$this->rows) $this->rows = db_num_rows($result);
		$this->mwritten =0;

		$max = db_guinfo('searchrows');
		if (db_guinfo('detailview') && $this->hitsas == 1) $max = db_guinfo('detailrows');
		$extra = '';
		if ($this->rows > $max) $extra = get_lang(6, $max); 
		showdir('',get_lang(8, checkchs($this->what, false)),0);

		echo '<table width="100%" cellspacing="0" cellpadding="0" border="0">';

		echo '<tr><td>';
		echo '<font class="wtext"> - '.get_lang(9).' '.$this->rows.' '.$extra.' / '.$kqm->result(3).' '.get_lang(7).'</font>';
		echo '</td></tr>';

		if ($this->hitsas == 1) 
		{
			echo '<tr><td><table width="100%" cellspacing="0" cellpadding="0" border="0">';
			echo '<tr><td height="10"></td></tr>';
			echo '<tr><td class="notice">';
			echo showviewform();
			echo  ' &nbsp;<input type="submit" class="fatbuttom" name="chlistoption" value="'.get_lang(107).'"/>';	
			echo '</td></tr>';
			echo '<tr><td height="5"></td></tr>';
			echo '</table>';
			echo '<table width="100%" cellspacing="0" cellpadding="0" border="0"><tr><td></td></tr>';
		}

		$filter = 0;

		while ($row = db_fetch_assoc($result)) 
		{
			if ($this->mwritten+1 > $max) break;
			$f2 = new file2($row['id'], false, $row);
			$fdesc = new filedesc($f2->fname);
			if ($fdesc->view && $f2->ifexists())
			{
				switch ($this->hitsas)
				{
					case 0: print_file($row['id'],1,1,$f2,$row['id']);
							$this->files++;
							break;
							
					case 1: $ainf = gen_aheader($row['album'], $row['artist'], $row['lengths'], $row['titles'], $row['year'], $row['genre']);
							print_album($row['drive'],$row['artist'].' - '.$row['album'], $f2->relativepath, $ainf, $this->what, 0, $row['id']);
							break;
				}			
				$this->mwritten++;
			} else $filter++;
		}

		if ($this->hitsas == 1) echo '</table></td></tr>';
		if ($this->rows==0) echo '<tr><td><font class="fdet">'.get_lang(10).'</font></td></tr>';
		if ($filter>0) echo '<tr><td><font class="fdet">'.get_lang(264,$filter).'</font></td></tr>';
		echo '</table>';
	}

	function endsearch()
	{		
		endmp3table(0, 0, $this->files);	
	}
}

class navi
{
	function navi($navid = 0, $rows = 0, $start=false, $pos=0)
	{
		$this->gui = true;
		
		$this->navid = frm_get('navid', 1);
		if (!$this->navid) $this->navid = $navid;

		$this->start = $start;
		$this->navrows = frm_get('navrows', 1);
		$this->navpos = frm_get('navpos', 1);
		
		if ($this->start)
		{
			$this->navrows = $rows;
			$this->navpos = $pos;
		}
		
		switch($this->navid)
		{
			case 2: $this->header = get_lang(5); break;
			case 3: $this->header = get_lang(4); break;
			case 4: $this->header = get_lang(3); break;
			case 5: $this->header = $this->headertext = get_lang(31, frm_get('hchar')); break;
			case 6: $this->header = get_lang(147); break;
			case 7: $this->header = get_lang(121); $this->gui = false; break;
		}

		if ($this->navid == 2) $this->perpage = db_guinfo('searchrows'); else
		if ($this->navid == 7) $this->perpage = frm_get('hperpage', 1, 18); else
				$this->perpage = db_guinfo('hotrows');

		if (db_guinfo('detailview') && $this->navid != 7) $this->perpage = db_guinfo('detailrows');

		if ($this->navid == 2 && db_guinfo('hitsas') == 0) $this->perpage = db_guinfo('searchrows');
		
		$this->searchtext = frm_get('searchtext');
		
		$this->follow = array();
	}

	function setperpage($perpage)
	{
		$this->perpage = $perpage;
	}

	function setfollow($name, $value)
	{
		$this->follow[] = array($name, $value);
	}

	function writepagelink($page, $mark=0)
	{
		if ($page == $mark) $class = 'filemarked'; else $class = 'hot';
		
		$extra = '';
		for ($i=0,$c=count($this->follow);$i<$c;$i++) $extra .= '&amp;'.$this->follow[$i][0].'='.$this->follow[$i][1];
		
		return '<a title="'.get_lang(278, $page).'" href="'.PHPSELF.'?action=gotopage&amp;page='.$page.'&amp;navrows='.$this->navrows.'&amp;searchtext='.urlencode(stripcslashes($this->searchtext)).'&amp;navid='.$this->navid.$extra.'" class="'.$class.'">'.$page.'</a>&nbsp;'; 
	}

	function searchnavi($direction, $pos=0)
	{
		if ($direction == 1) $this->navpos = $this->navpos + $this->perpage; else if ($direction == 0) $this->navpos = $this->navpos - $this->perpage;
		if ($pos != 0) $this->navpos = ($pos * $this->perpage);

		if ($this->navpos < 0) $this->navpos = 0;
			else
		if ($this->navpos % $this->perpage != 0)
			$this->navpos = ceil($this->navpos / $this->perpage) * $this->perpage;
		
		switch($this->navid)
		{
			case 7: // user history
				if (db_guinfo('u_access') == 0)
				{
					$uh = new userhistory();
					$uh->setuid(frm_get('huid', 1));
					$uh->setfilter(frm_get('filter', 1, -1));
					$uh->setperpage(frm_get('hperpage', 1, 18));
					$uh->show($this->navpos, $this->perpage);						
					
					$this->setfollow('huid', frm_get('huid', 1));
					$this->setfollow('filter', frm_get('filter', 1, -1));
					$this->setfollow('hperpage', frm_get('hperpage', 1, 18));
					$this->writenavi();
					$uh->endshow();
				}
				break;			
			
			case 2: // normal search
				$kps = new kpsearch();
				$kps->setrows($this->navrows);
				$kps->gensearchsql($this->navpos, $this->perpage);
				$kps->viewsearch();
				$this->writenavi();
				$kps->endsearch();	
				break;		

			default:
				$gl = new genlist();
				$gl->setrows($this->navrows);

				switch ($this->navid)
				{
					case 3: $gl->whats_new($this->navpos, $this->perpage); break;
					case 4: $gl->whats_hot(0, $this->navpos, $this->perpage); break;
					case 5: $this->setfollow('hchar', frm_get('hchar'));
							$gl->hotselect(frm_get('hchar'), $this->navpos, $this->perpage);
							break;
					case 6: $gl->genrelist($this->navpos, $this->perpage); break;
				}

				$gl->nhghlist();
				$this->writenavi();
				$gl->endlist();	
				break;
		}
	}

	function writenavi()
	{
		echo '<table width="100%" cellspacing="0" cellpadding="0" border="0">';
		echo '<tr><td>';
		echo '<input type="hidden" name="navpos" value="'.$this->navpos.'"/>';
		echo '<input type="hidden" name="searchtext" value="'.$this->searchtext.'"/>';
		echo '<input type="hidden" name="navrows" value="'.$this->navrows.'"/>';
		echo '<input type="hidden" name="navid" value="'.$this->navid.'"/>';

		for ($i=0,$c=count($this->follow);$i<$c;$i++) 
		echo '<input type="hidden" name="'.$this->follow[$i][0].'" value="'.$this->follow[$i][1].'"/>';
		
		echo '</td></tr>';
		
		if ($this->navrows > $this->perpage)
		{		
			echo '<tr><td height="8"></td></tr>';
			echo '<tr><td>';
			if ($this->navpos + $this->perpage >= $this->navrows) $disright = ' disabled="disabled" style="color:#CCCCCC"'; else $disright = '';
			if ($this->navpos > 0) $disleft = ''; else $disleft = ' disabled="disabled" style="color:#CCCCCC"';		
			echo '<input type="submit" class="fatbuttom" name="searchnavigate_left" value="'.get_lang(276).'"'.$disleft.'/>&nbsp;';
			echo '<input type="submit" class="fatbuttom" name="searchnavigate_right" value="'.get_lang(277).'"'.$disright.'/>&nbsp;&nbsp;';		
				

			$pages = ceil($this->navrows / $this->perpage);
			$curpage = ceil($this->navpos / $this->perpage) + 1;

			echo '<font class="wtext">'.get_lang(279).'</font>';

			if ($pages < 10) for ($i=0;$i<$pages;$i++) echo $this->writepagelink($i+1, $curpage);
			else
			{
				for ($i=0;$i<4;$i++) echo $this->writepagelink($i+1, $curpage);
						
				if ($curpage >= 4 && ($curpage + 2) < $pages)
				{
					echo ' .. ';
					if (($curpage - 3) <= 4) 
					{
						$start = 5;					
						$endpage = $curpage + 7;
					} else 
					{
						$start = $curpage - 3;
						$endpage = $curpage + 4;
					}

					for ($i=$start;$i < $endpage;$i++) 
					{
						if ($i > ($pages - 4)) break;
						echo $this->writepagelink($i, $curpage);
					}
				}			
				echo ' .. ';
				for ($i=$pages-4;$i<$pages;$i++) echo $this->writepagelink($i+1, $curpage);
			}	
			echo '</td></tr>';
			echo '<tr><td height="4"></td></tr>';		
		}
		echo '</table>';
	}
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

function search_qupdorins($id, $finf, $filein, $md5, $drive, $mtime, $f_stat, $fsize, $ltime, $xid=0)
{
	global $cfg;

	$utf_filein = $filein;

	if (UTF8MODE) 
	{
		if (!mb_check_encoding($filein, 'UTF-8')) $utf_filein = mb_convert_encoding($filein, 'UTF-8', $cfg['utf8_translate_from']);		
	}

	if ($id > 0) $sql = 'UPDATE '; else $sql = 'INSERT INTO ';
	
	$sql .= TBL_SEARCH.' SET title = "'.myescstr($finf['title']).'", fname = "'.myescstr(kp_basename($filein)).'", fpath = "'.myescstr(getrelative($filein)).'", album = "'.myescstr($finf['album']).'", artist = "'.myescstr($finf['artist']).'", md5 = "'.$md5.'", free = "'.myescstr(kp_basename($utf_filein)).'", genre = '. vernumset($finf['genre'],255).', lengths = '.$finf['lengths'].', ratemode = '.$finf['ratemode'].', bitrate = '.(int)$finf['bitrate'].', drive = '.$drive.', ltime = '.$ltime.', mtime = '.$mtime.', dirname = "'.myescstr(getrelative($utf_filein)).'", f_stat = '.$f_stat.', fsize = '.$fsize.', track = '.$finf['track'].', `year` = '.$finf['year'].', comment = "'.myescstr($finf['comment']).'", ftypeid = '.$finf['ftypeid'].', id3image = '.$finf['id3image'].', xid = '.$xid;
	if ($id > 0) $sql .= ' WHERE id = '.$id; else $sql .= ', `date` = '.$mtime;
	
	return $sql;
}

function search_qupdfree($free, $drive, $id)
{
	return 'UPDATE '.TBL_SEARCH.' SET fpath = "'.myescstr(getrelative($free)).'", fname = "'.myescstr(kp_basename($free)).'", free = "'.myescstr(kp_basename($free)).'", ltime = '.time().', dirname = "'.myescstr(getrelative($free)).'", drive = '.$drive.', f_stat = 0 WHERE id = '.$id;
}

function search_findid($free)
{
	$fsize = filesize($free);
	$md5 = md5file($free);
	if (!empty($md5))
	{
		$query = 'SELECT id FROM '.TBL_SEARCH.' WHERE md5 = "'.$md5.'" AND fsize = '.$fsize;
		$result = db_execquery($query);
		$row = db_fetch_assoc($result);
		$cnt = db_num_rows($result);
		if ($cnt > 0) return $row['id']; else return 0;
	} 	
}

function updatesingle($free)
{
	global $bd;
	$id = search_findid($free);
	$fid = get_file_info($free);

	$drive = -1;
	for ($i=0;$i<$bd->getcnt();$i++)
	{
		if ($bd->gtype($i) == 'l')
		{
			$str = substr($free,0,strlen($bd->getpath($i)));
			if (strcasecmp($bd->getpath($i), $str) == 0) $drive = $i;
		}
	}
	
	if ($fid && $drive != -1)
	{		
		$freestrip = substr($free, strlen($bd->getpath($drive)));
	
		if (!$id)
		{
			$sfree = myescstr(kp_basename($freestrip));
			$sdirname = myescstr(getrelative($freestrip));

			$res = db_execquery('SELECT id FROM '.TBL_SEARCH.' WHERE fname = "'.myescstr($sfree).'" AND fpath = "'.myescstr($sdirname).'"');
			
			if ($res && db_num_rows($res) == 1) 
			{
				$row = db_fetch_row($res);
				$id = $row[0];
			}
		}			
		$query = search_qupdorins($id, $fid, $freestrip, md5file($free), $drive, filemtime($free), 0, filesize($free), time());
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
	global $setctl, $win32;
	kprintheader(get_lang(11));
	?>
	<form name="updateoptions" method="post" action="<?php echo PHPSELF; ?>">
	<input type="hidden" name="action" value="performupdate"/>
	<table width="100%" border="0" cellspacing="1" cellpadding="1">	
	<tr>
		<td colspan="3"><?php if (!defined('GETID3_V') || GETID3_V != 19) echo gethtml('missing_getid3'); ?></td>
	</tr>	
	<tr>
		<td colspan="3"><?php echo helplink('whatisupdate', get_lang(160), 'importnant'); ?></td>
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
	<tr>
		<td class="wtext"><?php echo get_lang(13);?></td>
		<td><input type="checkbox" value="1" name="rebuildid3"/></td>
		<td class="wtext"><?php echo helplink('rebuildid3'); ?></td>
	</tr>

	

	<?php
	
	if (!$win32)
	{
	?>
	<tr>
		<td class="wtext"><?php echo get_lang(297);?></td>
		<td><input type="checkbox" value="1" name="followsymlinks" <?php echo $setctl->getchecked('followsymlinks'); ?>/></td>
		<td class="wtext"><?php echo helplink('updatefollowsymlinks'); ?></td>
	</tr>
	<?php
	}
	?>

	<tr>
		<td class="wtext"><?php echo get_lang(334);?></td>
		<td><input type="checkbox" value="1" name="updusecache" <?php echo $setctl->getchecked('updusecache'); ?>/></td>
		<td class="wtext"><?php echo helplink('updusecache'); ?></td>
	</tr>
	<tr>
		<td height="10"></td>
	</tr>

	<tr>
		<td class="wtext" colspan="3">
		<?php		
			echo get_lang(325).' ';
			$extracts = array();
			if (defined('GETID3_V')) 
			{
				switch(GETID3_V)
				{
					case 16: $extracts[] = 'getid3 v1.6'; break;
					case 17: $extracts[] = 'getid3 v1.7'; break;
					case 19: $extracts[] = 'getid3 v1.9'; break;
				}
			}
			
			if (class_exists('id3')) $extracts[] = 'class.id3';
			if (class_exists('ogg')) $extracts[] = 'class.ogg';

			for($i=0,$c=count($extracts);$i<$c;$i++) 
			{
				echo $extracts[$i];
				if ($i + 1 < $c) echo ', ';
			}
			if ($c == 0) echo get_lang(10);		
		?></td>
	</tr>

	<tr>
		<td height="10"></td>
	</tr>
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

function updateup_status($text, $tag='up_status2')
{
	?>
	<script type="text/javascript">
	d = document.getElementById('<?php echo $tag; ?>');
	d.innerHTML="<?php echo $text; ?>";
	</script>
	<?php
	flush();
}

function getsrow($id)
{
	$res = db_execquery('SELECT fsize, id, md5, fname, drive, mtime, fpath FROM '.TBL_SEARCH.' WHERE id = '.$id, true);	
	if ($res) return db_fetch_row($res);
}

function updaterunning()
{
	global $setctl, $runinit;
	if ($runinit['astream'] && $setctl->get('updatemid'))
	{
		$mid = $setctl->get('updatemid');
		$res = db_list_processes();
			while ($row = db_fetch_assoc($res)) if ($row['Id'] == $mid) return true;
	}
	return false;	
}

function search_updatelist($options='')
{
	global $win32, $setctl, $bd, $runinit;
	
	if ($runinit['astream']) 
	{
		if (!updaterunning()) $setctl->set('updatemid', db_thread_id());
			else errormessage('Another update is running. Please try again later.', true);
	}
	
	
	kprintheader(get_lang(17));
	
	$updateall = false;
	@ini_set('output_buffering', '1');	

	

	if (isset($options['deleteunused'])) $deleteunused = 1; else $deleteunused = 0;
	if (isset($options['debugmode'])) $debugmode = 1; else $debugmode = 0;
	if (isset($options['sleeppertrans'])) $sleeptrans = $options['sleeppertrans']; else $sleeptrans = 0;
	if (isset($options['rebuildid3'])) $updateall = true;

	$setctl->publish('updusecache');
	$setctl->publish('followsymlinks');

	$db_out = $db_mtime = $db_unique = $db_path = array();	

	$filecntr = 0;
	$file = '';

	$fixurl = 'http://www.kplaylist.net/forum/viewtopic.php?p=3672';

	echo '<font class="notice">'.get_lang(296, '<a href="'.$fixurl.'" target="_blank">'.$fixurl.'</a>').'</font><br/><br/>';
	echo '<font class="notice">'.get_lang(136).'..</font><br/>';
	flush();

	$data = array();
	$datacnt = 0;

	if ($debugmode) 
	{	
		@ini_set('display_errors', 'On');
		echo '<!-- update debug step 0 - mem limit detected: '.@ini_get('memory_limit').' -->';
	}
	
	$kqm = new kq_Measure();
	$kqm->start();

	echo '<br/><div id="up_status2" class="notice"></div>';
	echo '<br/><div id="up_status" class="notice"></div><br/>';

	$cnt = 0;

	

	for ($i=0,$c=$bd->getcnt();$i<$c;$i++)
	{
		if ($bd->gtype($i) == 'l')
		{
			$grabdata = array();
			if ($debugmode) echo '<!-- update debug step 1 - grabbing filelist from '.$bd->getpath($i).' -->';
			$stripc = strlen($bd->getpath($i));
			GetDirArray($bd->getpath($i), $grabdata, $cnt, $stripc);
			$data[$i] = $grabdata;
			$datacnt += count($data[$i]);
		}
	}

	if ($debugmode) echo '<!-- update debug step 2 -->';
	
	if ($datacnt > 0)
	{
		$result = db_execquery ('SELECT count(*) FROM '.TBL_SEARCH.' WHERE xid = 0');
		$row = db_fetch_row($result);
		$dbrows = $row[0];
		
		$result = db_execquery('SELECT fsize, id, md5, fname, drive, mtime, fpath FROM '.TBL_SEARCH.' WHERE xid = 0 ORDER BY id ASC', true);

		$dcntr=0;
				
		updateup_status(get_lang(314, $dcntr, $dbrows));
	
		while ($row = db_fetch_row($result)) 
		{			
			if (UPDUSECACHE) $db_out[$dcntr] = $row; else $db_out[$dcntr] = array($row[0], $row[1]);

			if ($dcntr % 50 == 0) updateup_status(get_lang(314, $dcntr, $dbrows));

			if (!isset($db_mtime[$row[0]][$row[5]])) $db_mtime[$row[0]][$row[5]] = $dcntr;
			if (!isset($db_unique[$row[0]][$row[2]])) $db_unique[$row[0]][$row[2]] = $dcntr;

			if (strlen($row[6]) != 0 || strlen($row[3]) != 0)
			{
				$path = crc32($row[6].$row[3]);

				if (isset($db_path[$path]))
				{
					$ids = $db_path[$path];
					$ids[] = $dcntr; 
					$db_path[$path] = $ids;
				} else $db_path[$path] = array($dcntr);
			}
			$dcntr++;
		}

		updateup_status(get_lang(314, $dcntr, $dbrows), 'up_status');

		db_free($result);

		if ($debugmode) echo '<!-- update debug step 3 -->';

		if ($updateall) $db_mtime = array();
		
		$query = null;
				
		updateup_status(get_lang(18, $datacnt));

		$totalqupds = $dcntr;	
		$totalins = $datacnt;
		$qins = $qupd = $failed = $qupdins = $skips= $qdels = $fdups = 0;
	
		$rowinsertid = -1;

		if ($datacnt > 0)
		{
			for ($drive=0,$drivec=$bd->getcnt();$drive<$drivec;$drive++)
			{
				if ($bd->gtype($drive) == 'l')
				{				
					for ($i=0,$ic=count($data[$drive]);$i<$ic;$i++)
					{
						$filein = $data[$drive][$i];
						$file = $bd->getpath($drive).$filein;

						if ($i % 50 == 0 || $debugmode)
						{
							$countups = $qupd + $qupdins;
							$out = get_lang(20,$qins,$countups);
							$out .= (kp_strlen($filein) > 60) ? addslashes(kp_substr($filein,0,60)).'...' : addslashes($filein);
							updateup_status($out, 'up_status');
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
							$i2 = $db_mtime[$fsize][$mtime];

							if (UPDUSECACHE) $userow = $db_out[$i2]; else $userow = getsrow($db_out[$i2][1]);						

							if ($db_out[$i2][0] != -1 && $userow[6].$userow[3] == $filein && $userow[4] == $drive)
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
									if (UPDUSECACHE) $userow = $db_out[$idupdate]; else $userow = getsrow($db_out[$idupdate][1]);
									
									$checkf = $bd->getpath($userow[4]).$userow[6].$userow[3];
									
									/* duplicate check - not in production - uncomment to use. But, do not use if you do not understand what it does.
									$identical = false;
									$fp1 = fopen($checkf, 'rb');
									$fp2 = fopen($file, 'rb');
									if ($fp1 && $fp2)
									{
										$identical = true;
										while (!feof($fp1))
										{
											$data1 = fread($fp1, 32768);
											$data2 = fread($fp2, 32768);
											if (strcmp($data1,$data2) != 0) 
											{
												$identical = false;
												break;
											}
										}									
										fclose($fp1);
										fclose($fp2);
									}

									if ($identical) $extra = '! '; else $extra = '';*/

									echo '<font class="notice">'.get_lang(168, $file, $checkf).'</font><br/><br/>';
									$fdups++;

									continue;								
								}
							} else $idupdate = -1;

							if ($idupdate != -1)
							{
								$db_out[$idupdate][0] = -1;

								if (UPDUSECACHE) $userow = $db_out[$idupdate]; else $userow = getsrow($db_out[$idupdate][1]);

								if ($updateall) $userow[5] = 0;
		
								if ($mtime != $userow[5])
								{
									$fid = get_file_info($file);								
									$query = search_qupdorins($db_out[$idupdate][1], $fid, $filein, $md5, $drive, $mtime, 0, $fsize, time());
									$qupdins++;
								} else
								if ($userow[6].$userow[3] != $filein || $userow[4] != $drive)
								{
									$query = search_qupdfree($filein, $drive, $db_out[$idupdate][1]);
									$qupdins++;
								}							
							} else
							{
								$frel = getrelative($filein);
								$ffilein = kp_basename($filein);
								$checkex = crc32($frel.$ffilein);
								$useid = -1;
								if (isset($db_path[$checkex]))
								{
									$ids = $db_path[$checkex];
									for ($i3=0,$c3=count($ids);$i3<$c3;$i3++)
									{
										$cid = $ids[$i3];

										if (UPDUSECACHE) $userow = $db_out[$cid]; else $userow = getsrow($db_out[$cid][1]);
										
										if ($userow[3] == $ffilein && $userow[6] == $frel)
										{
											$useid = $cid;
											break;
										}
									}							
								}

								$fid = get_file_info($file);								

								if ($useid == -1) 
								{
									$query = search_qupdorins(0, $fid, $filein, $md5, $drive, $mtime, 0, $fsize, time());	
									if (UPDUSECACHE) $db_out[$dcntr] = array(-1, 0, $md5, kp_basename($filein), $drive, $mtime, getrelative($filein));
										else  $db_out[$dcntr] = array(-1, 0);
									$db_unique[$fsize][$md5] = $dcntr;
									$rowinsertid = $dcntr;
									$dcntr++;
									$qins++;
								} else
								{
									$query = search_qupdorins($db_out[$useid][1], $fid, $filein, $md5, $drive, $mtime, 0, $fsize, time());
									$db_out[$useid][0] = -1;
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
								} else
								{
									if ($rowinsertid != -1)
									{
										$db_out[$rowinsertid][1] = db_insert_id();
										$rowinsertid = -1;
									}
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
				} // if local drive
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
					if (UPDUSECACHE) $userow = $db_out[$i2]; else $userow = getsrow($db_out[$i2][1]);
					
					echo '<font class="notice">'.get_lang(24, $userow[6].$userow[3]);
					$result = db_execquery('DELETE FROM '.TBL_SEARCH.' WHERE id = '.$db_out[$i2][1], true);
					if ($result) $qdels++;
					echo '</font><br/>';
					$fordel = 0;
				}
				echo '<br/>';
			} else
			{
				if ($fordel > 0)
				{
					echo '<font class="notice">'.get_lang(335).'</font><br/>';
				}
			}
		} else
		{
			$one = false;
			for ($i2=0;$i2<$dcntr;$i2++)
			if ($db_out[$i2][0] != -1) 			
			{ 			
				$one = true; 
		
				if (UPDUSECACHE) $userow = $db_out[$i2]; else $userow = getsrow($db_out[$i2][1]);

				echo '<font class="notice">'.get_lang(315, $userow[6].$userow[3]).'</font><br/>'; 
			}
			if ($one) echo '<br/>';
		}
		$kqm->stop();
		updateup_status(get_lang(26), 'up_status');
		echo '<font class="notice">'.get_lang(25, $qins, $qupdins, $qdels, $failed, $skips, $filecntr, $kqm->result(3), $fordel);
		echo '</font><br/><br/>';
		
	} 
	else
	{
		for ($i=0,$c=$bd->getcnt();$i<$c;$i++) 
		{
			if ($bd->gtype($i) == 'l') echo '<font class="notice">'.get_lang(28, $bd->getpath($i)).'</font><br/>';
		}
	}

	// network update && clean up

	for ($i=0,$c=$bd->getcnt();$i<$c;$i++)
	{
		if ($bd->gtype($i) == 'n')
		{
			updateup_status(get_lang(253));
			updateup_status('', 'up_status');
			
			$kpn = new kpnetwork();
			
			if ($kpn->setdrive($i))
			{
				if ($kpn->checklogin())
				{
					$cnt = $kpn->preparesync($i);

					$host = $kpn->getnetworkhost();

					if ($cnt > 0)
					{
						updateup_status(get_lang(347, $host->geturl(), $cnt));	
	
						if ($kpn->genchlist($updateall, $deleteunused) > 0) $kpn->dosync();

						updateup_status(get_lang(348, $host->geturl(), get_lang(181)), 'up_status');
					} else
					{
						if ($cnt == -1) updateup_status(get_lang(348, $host->geturl(), $kpn->geterrorstr()), 'up_status');
					}
				} else
				{
					updateup_status(get_lang(348, $host->geturl(), $kpn->geterrorstr()), 'up_status');
				}
			}			
		} else 
		if ($bd->gtype($i) == 'l')
		{
			if ($deleteunused)
			{
				$res = db_execquery('SELECT count(*) as cnt FROM '.TBL_SEARCH.' WHERE xid != 0 AND drive = '.$i);
				$row = db_fetch_assoc($res);
				if ($row['cnt'] > 0) $res = db_execquery('DELETE FROM '.TBL_SEARCH.' WHERE xid != 0 AND drive = '.$i);
			}
		}
	}

	echo '<input type="button" value="'.get_lang(27).'" name="close" class="fatbuttom" onclick="javascript: self.close();"/><br/><br/>';

	if ($runinit['astream']) $setctl->set('updatemid', 0);
	$setctl->set('basedir_changed', 0);
	cache_updateall();
	kprintend();	
}

function search_updateautomatic($user, $host, $waittrans=0)
{
	global $cfg, $setctl;

	if ($cfg['autoupdate'])
	{
		if ($host == $cfg['autoupdatehost'] && $user == $cfg['autoupdateuser'])
		{ 
			//$options['deleteunused'] = 1;
			//$options['rebuildid3'] = 1;
			//$options['debugmode'] = 1;
			$options['sleeppertrans'] = $waittrans;
			search_updatelist($options);
		} else echo "Wrong host ($host) or user ($user) for update.";
	} 
	die();
}


class coverinterface
{
	function coverinterface()
	{
		global $setctl;
		
		$this->files = array();
		$this->album = '';
		$this->artist = '';
		$this->pdir = '';

		$this->dirid = 0;
		$this->id3id = 0;

		$this->width = $setctl->get('albumwidth');
		$this->height = $setctl->get('albumheight');
		
		$this->scale = false;
	}
	
	function setartist($artist, $album)
	{
		$this->artist = $artist;
		$this->album = $album;
	}

	function setdimension($width, $height)
	{
		$this->width = $width;
		$this->height = $height;
	}

	function setlocation($drive, $pdir)
	{
		$this->drive = $drive;
		$this->pdir = $pdir;
	}

	function setfiles($files)
	{
		$this->files = $files;
	}

	function findalbum()
	{
		if (strlen($this->album) > 0 && strlen($this->artist) > 0)
		{
			$sql = 'SELECT * FROM '.TBL_ALBUMCACHE.' WHERE album = "'.myescstr($this->album).'" AND artist = "'.myescstr($this->artist).'"';
			$res = db_execquery($sql);
			if (db_num_rows($res) > 0)
			{
				$row = db_fetch_assoc($res);
				if ($row['id'] != 0) 
				{
					$this->dirid = $row['id'];
					return true;
				}
				if ($row['idid3'] != 0) 
				{
					$this->id3id = $row['idid3'];
					return true;
				}
			}
		}
		return false;
	}

	function findfile()
	{
		$gk = new genkpalbum();

		if (count($this->files) == 0)
		{
			if (strlen($this->pdir) > 0)
			{
				$kpdir = new kpdir();
				$kpdir->setdrive($this->drive);
				$kpdir->setpwd($this->pdir);
				if ($kpdir->determine())
				{
					$res = $kpdir->filesql('id, id3image, free');
					while ($row = db_fetch_row($res)) $this->files[] = $row;
				}
			}
		}

		if (count($this->files) > 0)
		{
			$this->dirid = $gk->finddirimage($this->files);
			if ($this->dirid) return true;
			$this->id3id = $gk->findid3v2image($this->files);			
			if ($this->id3id) return true;
		}

		return false;
	}

	function coverexists()
	{
		if (!$this->findalbum()) $this->findfile();
		if ($this->dirid || $this->id3id) return true;
		return false;
	}

	function getimagedata($resize)
	{
		global $cfg;
		
		if ($resize) $this->resize();

		if ($this->dirid) $ic = new imagecache($this->dirid, $this->width, $this->height, false);
				else $ic = new imagecache($this->id3id, $this->width, $this->height, true);

		$fname = '';
		if ($ic->getfilepointer($fname))
		{
			$fp = fopen($fname, 'rb');
			$imgdata = fread($fp, filesize($fname));
			fclose($fp);
			return $imgdata;
		} else
		{
			if ($this->dirid != 0) 
			{
				$imgp = createfromfile($this->dirid, $this->width, $this->height);
			} else
			{
				$imgp = createfromid3v2($this->id3id, $this->width, $this->height);
			}

			if ($imgp)
			{
				ob_start();
				ob_clean();
				imagejpeg($imgp, NULL, $cfg['jpeg-quality']);
				$imgdata = ob_get_contents();
				ob_end_clean();
				return $imgdata;
			}
		}
	}

	function geturl(&$url, $resize=ALBUMRESIZE, $direct=false)
	{
		if (!$this->findalbum()) $this->findfile();
		
		if ($this->dirid || $this->id3id)
		{
			if ($resize) $this->resize();
			
			if ($this->dirid) $ic = new imagecache($this->dirid, $this->width, $this->height, false);
				else $ic = new imagecache($this->id3id, $this->width, $this->height, true);

			if ($ic->checkgeturl($url, $direct))
			{
				return true;
			} else
			{				
				$url = $this->genurl($direct);
				return true;
			}
		}
		
		return false;
	}

	function resize()
	{
		$src = '';
		if ($this->dirid != 0) 
		{
			$f2 = new file2($this->dirid, false);
			$src = $f2->fullpath;
			$id3image = false;
		} else
		if ($this->id3id != 0)
		{
			$f2 = new file2($this->id3id, false);
			$src = $f2->fullpath;
			$id3image = true;
		}

		if (strlen($src) > 0)
		{
			$nw = $nh = 0;
			imgcoords($this->width, $this->height, $src, $nw, $nh, $id3image);
			$this->width = $nw;
			$this->height = $nh;
			$this->scale = true;
		}
	}

	function genurl($direct=false)
	{
		global $cfg;

		if ($this->dirid != 0) 
		{
			$f2 = new file2($this->dirid, false);

			if ($direct) 
					$url = $f2->mklink('imgsid', $f2->fname, $this->width, $this->height); 
				else
					$url = '<a href="'.$f2->mklink('sid', $f2->fname).'"><img border="0" src="'.$f2->mklink('imgsid', $f2->fname, $this->width, $this->height);

			if ($this->scale && !$cfg['filepathurl']) $url .= '&amp;w='.$this->width.'&amp;h='.$this->height;
			
			if (!$direct)
			{
				$url .= '" alt="album"';
				if ($this->scale) $url .= ' width="'.$this->width.'" height="'.$this->height.'"';
				$url .= '/></a>'; 
			}
			return $url;
		} else
		if ($this->id3id != 0)
		{
			$f2 = new file2($this->id3id, false);
			if (!$direct) 
					return '<img border="0" src="'.$f2->mklink('imgid3sid', 'album.jpg').'" width="'.$this->width.'" height="'.$this->height.'" alt="album"/>';
				else return $f2->mklink('imgid3sid', 'album.jpg');
		
		}		
	}	
}

class imagecache
{
	function imagecache($sid, $w, $h, $id3v2=false)
	{
		$this->w = $w;
		$this->h = $h;
		$this->sid = $sid;
		$this->id3v2 = $id3v2;
	}
	
	function check()
	{
		global $setctl;
		
		if ($setctl->get('storealbumcovers'))
		{
			$sdir = noslash($setctl->get('storealbumdir'));
			if (strlen($sdir) > 0 && @is_dir($sdir)) 
			{
				if (function_exists('imagecreatetruecolor') && function_exists('imagecopyresampled') && function_exists('imagejpeg')) return true;
			}
		}
		return false;
	}

	function getfname()
	{
		global $setctl;
		return $setctl->get('storealbumdir').$this->w.$this->h.$this->sid.'.jpg';
	}

	function getfilepointer(&$fname)
	{
		if ($this->check())
		{
			if (!file_exists($this->getfname())) $this->createsid();
				
			$fname = $this->getfname();
	
			if (file_exists($fname)) return true;
		}
		
		return false;
	}

	function checkgeturl(&$url, $direct=false)
	{
		global $phpenv, $setctl;
		
		if ($this->check())
		{
			if (!file_exists($this->getfname())) $this->createsid();
				
			$fname = $this->getfname();
	
			if (file_exists($fname))
			{
				$sdir = noslash($setctl->get('storealbumrelative'));
				$imgsrc = slashstart($sdir).'/'.kp_basename($fname);
				if (!$direct) $url = '<img alt="image" border="0" src="'.$imgsrc.'" height="'.$this->h.'" width="'.$this->w.'"/>';
					else $url = $setctl->get('streamurl').$phpenv['host'].$imgsrc;
				
				return true;
			}		
		}
		
		return false;
	}

	function getimage()
	{
		$ok = false;

		if ($this->check())
		{
			$fname = $this->getfname();
			
			if (!file_exists($fname)) $this->createsid();

			if (file_exists($fname))
			{
				$this->send($fname);
				return true;
			}
		}

		if ($this->id3v2) 
		{
			id3v2image($this->sid, true, true);
		} else createimg($this->sid, true, $this->w, $this->h);
	}

	function send($fname)
	{
		$fp = fopen($fname, 'rb');
		if ($fp)
		{
			header('Content-Disposition: inline; filename="'.$fname.'"'); 
			header('Content-Type: image/jpeg');
			header('Content-Length: '.filesize($fname));
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Content-Transfer-Encoding: binary');
			header('Expires: '. gmdate('D, d M Y H:i ', time()+24*60*60) . ' GMT');
			header('Pragma: public');		
			while (!feof($fp) && !connection_aborted()) echo fread($fp, 16384);
			@fclose($fp);
			return true;
		}
		return false;
	}

	function getsid()
	{
		$fname = $this->getfname();
		if (file_exists($fname) && $this->send($fname)) return true;
		return false;
	}

	function createid3v2()
	{
		global $cfg;
		$f2 = new file2($this->sid, true);
		if ($f2->ifexists())
		{
			$info = get_file_info($f2->fullpath, true);
	
			if (isset($info['id3v2']['APIC'][0]) && is_array($info['id3v2']['APIC'][0]))
			{
				$apic = $info['id3v2']['APIC'][0];

				if (isset($apic['mime']) && isset($apic['data'])) 
				{
					$image = imagecreatefromstring($apic['data']);
					if ($image)
					{
						if (imagejpeg($image, $this->getfname(), $cfg['jpeg-quality'])) return true;
					}
				}
			} else return true;
		}	
	}

	function rewrite($fpath)
	{
		global $cfg;

		if (file_exists($fpath))
		{
			$fdesc = new filedesc($fpath);
			if ($fdesc->found)
			{
				$nh = $nw = 0;
				
				imgcoords($this->w, $this->h, $fpath, $nw, $nh, false);

				if ($imagesize = getimagesize($fpath))
				{
					$w = $imagesize[0]; 
					$h = $imagesize[1];
					$image = false;
					$image_p = imagecreatetruecolor($nw, $nh);	
						
					if (is_resource($image_p))
					{
						switch ($fdesc->extension)
						{
							case 'gif':
								if (function_exists('imagecreatefromgif') && function_exists('imagegif'))
									$image = imagecreatefromgif($fpath);
							break;
								
							case 'png':
								if (function_exists('imagecreatefrompng') && function_exists('imagepng'))
									$image = imagecreatefrompng($fpath);
							break;
								
							case 'jpeg':
							case 'jpg':
								if (function_exists('imagecreatefromjpeg') && function_exists('imagejpeg'))
									$image = imagecreatefromjpeg($fpath);
							break;						
						}					
					}

					if ($image)
					{
						imagecopyresampled($image_p, $image, 0, 0, 0, 0, $nw, $nh, $w, $h);
						if (imagejpeg($image_p, $this->getfname(), $cfg['jpeg-quality'])) return true;
					}
				}
			}
		}
	}

	function createsid()
	{
		$f2 = new file2($this->sid);
		$fpath = $f2->fullpath;

		if ($this->id3v2 && getid3support())
		{
			if ($this->createid3v2())
			{
				$fpath = $this->getfname();
			} else return false;
		}

		return $this->rewrite($fpath);		
	}
}


function imgcoords($width=0, $height=0, $srcfp, &$nw, &$nh, $idv3)
{
	global $setctl;
	$resize = false;
	
	if (!$width) $wm = $setctl->get('albumwidth'); else $wm = $width;
	if (!$height) $hm = $setctl->get('albumheight');  else $hm = $height;

	if ($idv3) $imagesize = getimagesizeid3($srcfp); else $imagesize = getimagesize($srcfp);
	
	if (is_array($imagesize))
	{
		$w = $imagesize[0]; 
		$h = $imagesize[1]; 
		
		$nw = min ($wm, $w); 
		$nh = min ($hm, $h); 

		if ($nw > 0 && $nh > 0)
		{		
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

			if ($nw != $w || $nh != $h) $resize = true;
		}

	} else
	{		
		$nw = $wm;
		$nh = $hm;
	}

	return $resize;
}

function createimg($sid, $headers=true, $w=0, $h=0, $fdest=NULL)
{
	global $setctl, $cfg;
	
	$sent = false;
	$f2 = new file2($sid);
	$fdesc = new filedesc($f2->fname);
	if ($f2->fexists && $fdesc->found)
	{		
		$nh = $nw = 0;
		if ($setctl->get('albumresize'))
		{
			if (imgcoords($w,$h,$f2->fullpath, $nw, $nh, false) && function_exists('imagecreatetruecolor') && function_exists('imagecopyresampled'))
			{
				if ($imagesize = @getimagesize($f2->fullpath))
				{
					$w = $imagesize[0]; 
					$h = $imagesize[1]; 
					$image_p = imagecreatetruecolor($nw, $nh);	
					
					if (is_resource($image_p))
					{
						switch ($fdesc->extension)
						{
							case 'gif':
								if (function_exists('imagecreatefromgif') && function_exists('imagegif'))
								{
									$image = @imagecreatefromgif($f2->fullpath);
									if ($image) 
									{
										imagecopyresampled($image_p, $image, 0, 0, 0, 0, $nw, $nh, $w, $h);
										imgsend($f2, $fdesc, false, $headers);
										imagegif($image_p, $fdest);
										$sent = true;
									}
								}
								break;
								
							case 'png':
								if (function_exists('imagecreatefrompng') && function_exists('imagepng'))
								{
									$image = @imagecreatefrompng($f2->fullpath);
									if ($image)
									{
										imagecopyresampled($image_p, $image, 0, 0, 0, 0, $nw, $nh, $w, $h);
										imgsend($f2, $fdesc, false, $headers);
										imagepng($image_p, $fdest);
										$sent = true;
									}
								}
								break;
								
							case 'jpg':
								if (function_exists('imagecreatefromjpeg') && function_exists('imagejpeg'))
								{
									$image = @imagecreatefromjpeg($f2->fullpath);
									if ($image)
									{
										imagecopyresampled($image_p, $image, 0, 0, 0, 0, $nw, $nh, $w, $h);
										imgsend($f2, $fdesc, false, $headers);
										imagejpeg($image_p, $fdest, $cfg['jpeg-quality']);
										$sent = true;
									}
								}
								break;						
						}					
					}
				}
			}		
		}
		if (!$sent) imgsend($f2, $fdesc, true);
	}
}

function createfromid3v2($sid, $nw, $nh)
{
	global $cfg;

	$f2 = new file2($sid, true);
	if ($f2->ifexists())
	{
		$info = get_file_info($f2->fullpath, true);
	
		if (isset($info['id3v2']['APIC'][0]) && is_array($info['id3v2']['APIC'][0]))
		{
			$apic = $info['id3v2']['APIC'][0];

			if (isset($apic['mime']) && isset($apic['data'])) 
			{				
				$image = imagecreatefromstring($apic['data']);
				if ($image)
				{
					$image_p = imagecreatetruecolor($nw, $nh);
	
					$h = imagesy($image);
					$w = imagesx($image);

					if (imagecopyresampled($image_p, $image, 0, 0, 0, 0, $nw, $nh, $w, $h)) return $image_p;

					return $image_p;
				}
			}
		}
	}

	return false;
}

function createfromfile($sid, $nw, $nh)
{
	$f2 = new file2($sid);
	$fdesc = new filedesc($f2->fname);
	if ($f2->fexists && $fdesc->found)
	{	
		if (function_exists('imagecreatetruecolor') && function_exists('imagecopyresampled'))
		{
			if ($imagesize = @getimagesize($f2->fullpath))
			{
				$w = $imagesize[0]; 
				$h = $imagesize[1]; 
				$image_p = imagecreatetruecolor($nw, $nh);
				$image = false;
					
				if (is_resource($image_p))
				{
					switch ($fdesc->extension)
					{
						case 'gif':
							if (function_exists('imagecreatefromgif'))
								$image = @imagecreatefromgif($f2->fullpath);
						break;
								
						case 'png':
							if (function_exists('imagecreatefrompng'))
								$image = @imagecreatefrompng($f2->fullpath);
						break;
								
						case 'jpeg':
						case 'jpg':
							if (function_exists('imagecreatefromjpeg'))
								$image = @imagecreatefromjpeg($f2->fullpath);
						break;						
					}

					if ($image) if (imagecopyresampled($image_p, $image, 0, 0, 0, 0, $nw, $nh, $w, $h)) return $image_p;
				}
			}
		}
	}

	return false;
}

function getimagesizeid3($fullpath)
{
	$info = get_file_info($fullpath, true);
	
	$h = $w = 0;
	
	if (isset($info['id3v2']['APIC'][0]) && is_array($info['id3v2']['APIC'][0]))
	{
		$apic = $info['id3v2']['APIC'][0];

		if (isset($apic['mime']) && isset($apic['data']) && strlen($apic['data']) > 0) 
		{
			$image = @imagecreatefromstring($apic['data']);
			if ($image)
			{
				$h = imagesy($image);
				$w = imagesx($image);
			}
		}
	}

	$res = array(0 => $w, 1 => $h);

	return $res;
}

function imgsend($f2, $fdesc, $data = false, $headers=true, $cname='')
{
	if ($data)
	{
		$fp = fopen($f2->fullpath, 'rb');
		if (!$fp) return false;
	}
	
	if ($headers)
	{
		if (!empty($cname)) $uname = $cname; else $uname = $f2->fname;
		
		header('Content-Disposition: inline; filename="'.$uname.'"'); 
		header('Content-Type: '.$fdesc->mime);
		if ($data) header('Content-Length: '.$f2->fsize);
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Transfer-Encoding: binary');
		header('Expires: '. gmdate('D, d M Y H:i ', time()+24*60*60) . ' GMT');
		header('Pragma: public');
	}

	if ($data) 
	{
		while (!feof($fp) && !connection_aborted()) echo fread($fp, 16384);
		@fclose($fp);
	}
	return true;
}

function id3v2image($sid, $send=true, $headers=true)
{
	$f2 = new file2($sid, true);
	if ($f2->ifexists())
	{
		$info = get_file_info($f2->fullpath, true);

		if (isset($info['id3v2']['APIC'][0]) && is_array($info['id3v2']['APIC'][0]))
		{
			$fdesc = new filedesc();

			$apic = $info['id3v2']['APIC'][0];

			if (isset($apic['mime']) && isset($apic['data'])) 
			{
				$fdesc->mime = $apic['mime'];
				$name = 'cover';

				$mimex = explode('/', $fdesc->mime);
				if (count($mimex) == 2) $name .= '.'.$mimex[1];

				if ($send)
				{
					if ($headers) imgsend($f2, $fdesc, false, true, $name);
					echo $apic['data'];
				} else return true;
			}	
		}
	}
	return false;	
}


$genresid3 = array(0   => 'Blues', 1 => 'Classic Rock', 2 => 'Country', 3 => 'Dance', 4 => 'Disco', 5 => 'Funk', 6 => 'Grunge',
					7   => 'Hip-Hop',8  => 'Jazz', 9   => 'Metal', 10  => 'New Age', 11  => 'Oldies', 12  => 'Other', 13  => 'Pop',
					14  => 'R&B', 15 => 'Rap',	16  => 'Reggae', 17  => 'Rock', 18  => 'Techno', 19  => 'Industrial', 20  => 'Alternative',
					21  => 'Ska', 22 => 'Death Metal', 23  => 'Pranks', 24  => 'Soundtrack', 25  => 'Euro-Techno', 26  => 'Ambient',
					27  => 'Trip-Hop', 28  => 'Vocal', 29  => 'Jazz+Funk', 30  => 'Fusion', 31  => 'Trance', 32  => 'Classical',
					33  => 'Instrumental', 34  => 'Acid', 35  => 'House', 36  => 'Game', 37  => 'Sound Clip', 38  => 'Gospel',
					39  => 'Noise', 40  => 'Alternative Rock', 41  => 'Bass', 42  => 'Soul', 43  => 'Punk', 44  => 'Space',
					45  => 'Meditative', 46  => 'Instrumental Pop', 47  => 'Instrumental Rock', 48  => 'Ethnic', 49  => 'Gothic',
					50  => 'Darkwave', 51 => 'Techno-Industrial', 52  => 'Electronic', 53  => 'Pop-Folk', 54  => 'Eurodance',
					55  => 'Dream', 56  => 'Southern Rock', 57  => 'Comedy', 58  => 'Cult', 59  => 'Gangsta', 60  => 'Top 40',
					61  => 'Christian Rap', 62  => 'Pop/Funk', 63  => 'Jungle', 64  => 'Native US', 65  => 'Cabaret',
					66  => 'New Wave', 67 => 'Psychadelic', 68  => 'Rave', 69  => 'Showtunes', 70 => 'Trailer',
					71  => 'Lo-Fi', 72  => 'Tribal', 73  => 'Acid Punk', 74  => 'Acid Jazz', 75  => 'Polka', 76  => 'Retro',
					77  => 'Musical', 78 => 'Rock & Roll', 79  => 'Hard Rock', 80  => 'Folk', 81  => 'Folk-Rock',
					82  => 'National Folk', 83  => 'Swing', 84  => 'Fast Fusion', 85  => 'Bebob', 86  => 'Latin',
					87  => 'Revival', 88 => 'Celtic', 89  => 'Bluegrass', 90  => 'Avantgarde', 91  => 'Gothic Rock',
					92  => 'Progressive Rock', 93  => 'Psychedelic Rock', 94  => 'Symphonic Rock', 95  => 'Slow Rock',
					96  => 'Big Band', 97 => 'Chorus', 98  => 'Easy Listening', 99  => 'Acoustic', 100 => 'Humour',
					101 => 'Speech', 102 => 'Chanson', 103 => 'Opera', 104 => 'Chamber Music', 105 => 'Sonata',
					106 => 'Symphony', 107 => 'Booty Bass', 108 => 'Primus', 109 => 'Porn Groove', 110 => 'Satire',
					111 => 'Slow Jam', 112 => 'Club', 113 => 'Tango', 114 => 'Samba', 115 => 'Folklore', 116 => 'Ballad',
					117 => 'Power Ballad', 118 => 'Rhytmic Soul', 119 => 'Freestyle', 120 => 'Duet', 121 => 'Punk Rock',
					122 => 'Drum Solo', 123 => 'Acapella', 124 => 'Euro-House', 125 => 'Dance Hall', 126 => 'Goa',
					127 => 'Drum & Bass', 128 => 'Club-House', 129 => 'Hardcore', 130 => 'Terror', 131 => 'Indie',
					132 => 'BritPop', 133 => 'Negerpunk', 134 => 'Polsk Punk', 135 => 'Beat', 136 => 'Christian Gangsta Rap',
					137 => 'Heavy Metal', 138 => 'Black Metal', 139 => 'Crossover', 140 => 'Contemporary Christian',
					141 => 'Christian Rock', 142 => 'Merengue', 143 => 'Salsa', 144 => 'Trash Metal', 145 => 'Anime',
					146 => 'Jpop', 147 => 'Synthpop'
		    );

class kpgenres
{
	function kpgenres()
	{
	}

	function getidgenres()
	{
		return $this->idgenres;
	}
	
	function init()
	{
		global $genresid3, $cfg;

		$this->genres = array();
		$this->idgenres = array();

		foreach ($genresid3 as $id => $gname) $this->genres[kp_tolower(trim($gname))] = $id;

		$cg = 256;
		foreach($cfg['custom_genres'] as $name)
		{
			$this->genres[kp_tolower(trim($name))] = $cg;
			$cg++;
		}
	
		if ($cfg['genre_auto_create'])
		{
			$sgid = 1024;

			$sql = 'SELECT name, gid FROM '.TBL_GENRE.' ORDER BY gid ASC';
			$res = db_execquery($sql);
			while ($row = db_fetch_row($res)) 
			{
				$this->genres[kp_tolower(trim($row[0]))] = ($row[1] + 1024);			
			}
		}

		foreach($this->genres as $name => $id) $this->idgenres[$id] = $name;
	}

	function getbyname($name)
	{
		$sname = kp_tolower(trim($name));
		
		if (isset($this->genres[$sname])) return $this->genres[$sname];
		return -1;
	}

	function getbyid($id)
	{
		if (isset($this->idgenres[$id])) return $this->idgenres[$id];
		return 255;
	}

	function addname($name)
	{
		global $cfg;

		if ($cfg['genre_auto_create'])
		{
			$gname = kp_tolower(trim($name));
			
			$sql = 'INSERT INTO '.TBL_GENRE.' SET name = "'.myescstr($gname).'"';
			if (db_execquery($sql))
			{
				$sgid = db_insert_id();
				if (is_numeric($sgid))
				{
					$id = $sgid + 1024;
					$this->genres[$gname] = $id;
					$this->idgenres[$id] = $gname;
				}
			}
		}
	}
}

$kpgenre = new kpgenres();
$kpgenre->init();

function gengenres($id=255)
{
	global $kpgenre;

	if ($id != 255)
	{
		return $kpgenre->getbyid($id);
	} else return $kpgenre->getidgenres();
}


function findmusic()
{
	global $win32, $setctl;
	kprintheader(get_lang(289));
	
	if (frm_isset('paths')) $paths = str_replace("\r\n", "\n", frm_get('paths')); else $paths = '';

	echo '<div id="up_status2" class="notice"></div>';
	$data = array();

	if (frm_isset('useselected'))
	{
		$nbasedir = '';
		$sel = frm_get('selected', 3);
		foreach($sel as $name => $val) $nbasedir .= $val.';';		
		$setctl->set('base_dir', basedir_rewrite($nbasedir));
		$setctl->set('basedir_changed', 1);
		?>
		<script type="text/javascript">
		<!--			
			window.close();
			window.opener.location.reload();
		-->
		</script>
		<?php
		die();
	}	

	if (empty($paths))
	{
		$cpath = getcwd();
		if (!empty($cpath)) $paths .= basedir_rewrite($cpath)."\n";
		if ($win32) $paths .= 'c:/'."\n".'d:/'."\n".'e:/'; else $paths .= '/';
	} else
	{
		$cnt = 0;
		$pathse = explode("\n", $paths);
		for ($i=0,$c=count($pathse);$i<$c;$i++) GetDirArrayLight(basedir_rewrite($pathse[$i]), $data, $cnt);		
	}

	?>
	<form action="<?php echo PHPSELF; ?>" method="post">			
	<input type="hidden" name="action" value="findmusic"/>
	<table width="95%" cellpadding="2" cellspacing="0" border="0" align="center">
	
	
	<?php
	$useselected = false;


	$data2 = array(); 
	if (count($data) > 0)
	{
		foreach($data as $name => $cnt)
		{
			$rpos = strrpos(substr($name, 0, strlen($name)-1), '/');
			if ($rpos === false) $rpos = 0;
			$data2[] = array($name, $cnt, $rpos);	
		}

		for($i=0,$c=count($data2);$i<$c;$i++)
		for($i2=0,$c2=count($data2);$i2<$c2;$i2++)
		{			
			if ($data2[$i] != false && $data2[$i][2] != 0 && $data2[$i][2] == $data2[$i2][2] && $i != $i2)
			{				
				if (substr($data2[$i][0], 0, $data2[$i][2]) == substr($data2[$i2][0], 0, $data2[$i2][2]))
				{
					$data2[$i2][0] = substr($data2[$i2][0], 0, $data2[$i2][2] + 1);
					$data2[$i2][1] += $data2[$i][1];	
					$data2[$i] = false;
				}
			}
		}
		
		for($i=0,$c=count($data2);$i<$c;$i++)
		{
			$check = $data2[$i][0];
			if (empty($check)) continue;		
			for($i2=0,$c2=count($data2);$i2<$c2;$i2++)
			{
				$compare = $data2[$i2][0];
				if (empty($compare)) continue;				

				if (strlen($check) > strlen($compare))
				{					
					if (substr($check, 0, strlen($compare)) == $compare)
					{
						 $data2[$i] = false;		
					}
				}
			}
		}
	}

	if (count($data2) > 0)
	{
		for($i=0,$c=count($data2);$i<$c;$i++)
		{
			if ($data2[$i] != false)
			{
				$useselected = true;
				echo '<tr><td class="wtext">';
				echo '<input type="checkbox" class="fatbuttom" checked="checked" name="selected[]" value="'.$data2[$i][0].'"/>&nbsp;'.$data2[$i][0].'&nbsp;'.get_lang(18, $data2[$i][1]);
				echo '</td></tr>';
			}
		}
	}
	?>
	<tr>
		<td class="wtext"><?php echo get_lang(290); ?></td>
	</tr>
	<tr>
		<td><textarea class="fatbuttom" rows="10" cols="70" name="paths"><?php echo $paths; ?></textarea></td>
	</tr>
	<tr>
		<td><input type="submit" class="fatbuttom" name="check" value="<?php echo get_lang(5); ?>"/>
		<?php if ($useselected) echo '<input type="submit" class="fatbuttom" name="useselected" value="'.get_lang(291).'"/>'; ?>
		<input type="button" value="<?php echo get_lang(27); ?>" name="close me" class="fatbuttom" onclick="javascript: window.close();"/>
		</td>
	</tr>
	</table>
	</form>
	<?php		
	kprintend();
}

function GetDirArrayLight($spath, &$data, &$cnt)
{
	global $cfg;
	$flist = array();

	foreach($cfg['detectignoredirs'] as $name)
	{
		$spos = strpos(strtolower($spath), $name);
		if ($spos !== false) return;		
	}

	if (@$handle=opendir($spath))
	{
		while (false !== ($file = readdir($handle))) $flist[] = $file;
		closedir($handle);

		if (count($flist) > 0)
		{
			for ($i=0,$c=count($flist);$i<$c;$i++)
			{
				$val = $flist[$i];
				if ($val != '.' && $val != '..')
				{
					if (@is_file($spath.$val) && !is_dir($spath.$val))
					{
						$cnt++;
						if ($cnt % 100 == 0) updateup_status(get_lang(253)." ".$cnt);

						if (isset($cfg['detecttypes'][file_extension($val)]))
						{
							$dirse = explode('/', $spath);
							$cpath = '';
							$found = false;
							$cpath = $dirse[0];
							for ($i2=1,$c2=count($dirse);$i2<$c2;$i2++)
							{
								$cpath .= '/'.$dirse[$i2];
								if (isset($data[$cpath.'/'])) 
								{
									$data[$cpath.'/']++;
									$found = true;
									break;
								}
							}							
							if (!$found) $data[$spath] = 1;
						}
					}
				}
			}
			for ($i=0,$c=count($flist);$i<$c;$i++)
			{
				$val = $flist[$i];
				if ($val != '.' && $val != '..' && @is_dir($spath.$val) && !is_link($spath.$val)) GetDirArrayLight($spath.$val.'/', $data, $cnt);
			}
		}
	}
}

function GetDirArray($spath, &$data, &$cnt, $stripc=0)
{
	$flist = array();
	$flistcnt = 0;

	if ($handle = opendir($spath))
	{
		while (false !== ($file = readdir($handle))) $flist[$flistcnt++] = $file;
		closedir($handle);

		if ($flistcnt > 0)
		{
			for ($i=0;$i<$flistcnt;$i++)
			{
				$val = $flist[$i];
				if ($val != '.' && $val != '..')
				{
					if ($cnt % 100 == 0) updateup_status($cnt);

					if (is_dir($spath.$val)) 
					{
						if (is_link($spath.$val) && !FOLLOWSYMLINKS) continue;
						GetDirArray($spath.$val.'/', $data, $cnt, $stripc);
					} else 
					if (file_type($val) != -1) 
					{
						$data[] = substr($spath.$val, $stripc);
						$cnt++;
					}
				}
			}
		}
	}
}

function kpgenerateid3v2tag($sid)
{
	global $cfg, $phpenv, $setctl;
	
	if ($cfg['enablegetid3'])
	{
		$f2 = new file2($sid, true);

		switch (GETID3_V)
		{
			case 16:
					require_once(GETID3_INCLUDEPATH.'getid3.id3v2.php');
					$data['id3v2']['TIT2'][0]['encodingid'] = 0;
					$data['id3v2']['TIT2'][0]['data']       = $f2->id3['title'];
					$data['id3v2']['TPE1'][0]['encodingid'] = 0;
					$data['id3v2']['TPE1'][0]['data']       = $f2->id3['artist'];
					$data['id3v2']['TALB'][0]['encodingid'] = 0;
					$data['id3v2']['TALB'][0]['data']       = $f2->id3['album'];
					$data['id3v2']['TRCK'][0]['encodingid'] = 0;
					$data['id3v2']['TRCK'][0]['data']       = $f2->id3['track'];
					$data['id3v2']['COM'][0]['encodingid'] = 0;
					$data['id3v2']['COM'][0]['data']       = $f2->id3['comment'];
					$data['id3v2']['TYER'][0]['encodingid'] = 0;
					$data['id3v2']['TYER'][0]['data']       = $f2->id3['year'];
					return GenerateID3v2Tag($data['id3v2'], 3, 0, 0, '', false, false, false);
					break;
			
			case 19:
			case 17:
					$tagformat = 'UTF-8';
					$major = 3;
					$getID3 = new getID3;
					$getID3->encoding = $tagformat;

					if (!defined('GETID3_INCLUDEPATH')) define('GETID3_INCLUDEPATH', dirname($cfg['getid3include']).'/');
					if (getid3_lib::IncludeDependency(GETID3_INCLUDEPATH.'module.tag.id3v2.php', __FILE__, false) &&
					getid3_lib::IncludeDependency(GETID3_INCLUDEPATH.'write.php', __FILE__, false) &&
					getid3_lib::IncludeDependency(GETID3_INCLUDEPATH.'write.id3v2.php', __FILE__, false))
					{			
						$tagwriter = new getid3_writetags;
						$tagwriter->tagformats = array('id3v2.3');

						$tagwriter->filename = $f2->fullpath;
						if (GETID3_V == 17) $tagwriter->overwrite_tags = false;
						$tagwriter->tag_encoding   = $tagformat;
						$tagwriter->remove_other_tags = false;

						$TagData['title'][0]   = $f2->id3['title'];
						$TagData['artist'][0]  = $f2->id3['artist'];
						$TagData['album'][0]   = $f2->id3['album'];
						if (vernum($f2->id3['year']) != 0) $TagData['year'][0] = vernum($f2->id3['year']);
						$TagData['comment'][0] = $f2->id3['comment'];
						$TagData['track'][0]   = vernum($f2->id3['track']);

						if (empty($TagData['title'][0])) $TagData['title'][0] = $f2->fname;
						if (empty($TagData['artist'][0])) $TagData['artist'][0] = 'Unknown';
						if (empty($TagData['album'][0]))
						{
							$exp = explode('/', dirname($f2->fullpath));
							if (count($exp) > 1) $TagData['album'][0] = $exp[count($exp) - 1];
						}
		
						$ci = new coverinterface();
						$ci->setartist($f2->id3['artist'], $f2->id3['album']);
						$ci->setlocation($f2->drive, $f2->relativepath);
						if ($ci->coverexists())
						{
							if ($cfg['id3v2albumresize']) $rs = true; else $rs = false;
							$imgdata = $ci->getimagedata($rs);

							if (strlen($imgdata) > 0)
							{
								if ($cfg['maxtagimagesize'] == 0 || strlen($imgdata) <= $cfg['maxtagimagesize'])
								{
									$TagData['attached_picture'][0]['data'] = $imgdata;
									$TagData['attached_picture'][0]['picturetypeid'] = 3;
									$TagData['attached_picture'][0]['encodingid'] = 0;
									$TagData['attached_picture'][0]['description'] = 'ART';
									$TagData['attached_picture'][0]['mime'] = 'image/jpeg';
								}
							}							
						}				

						$tagwriter->tag_data = $TagData;

						$id3v2_writer = new getid3_write_id3v2;
						$id3v2_writer->majorversion = $major;
						$id3v2_writer->paddedlength = 0;

						if (($id3v2_writer->tag_data = $tagwriter->FormatDataForID3v2($major)) !== false) 
							return $id3v2_writer->GenerateID3v2Tag();
					}
					break;		
		}
	}
	return '';
}

function httpstreamheader2($ftype=1, $sid, $keyint=0, $fname)
{
	$f2 = new file2($sid, true);
	httpstreamheader3($ftype, $sid, $f2);
}

function httpstreamheader3($ftype=1, $sid, $f2, $unicode=false)
{
	global $phpenv, $streamtypes, $setctl, $u_cookieid, $cfg, $bd;
	$url = '';
	
	if (isset($streamtypes[$ftype]) && $streamtypes[$ftype][2] == 1)
	{
		if ($cfg['filepathurl'])
		{
			$url = $setctl->get('streamurl').$phpenv['location'].$cfg['filepathurlprepend'];
			$url .= '/streamsid_'.$sid.'/c_'.$u_cookieid;
			if (URLSECURITY) $url .= '/stag_'.urlsecurity($f2->fdate, $sid, false, false);  
			if ($unicode) $fname = $f2->free; else $fname = $f2->fname;
			$url .= '/'.$fname;
		} else
		{		
			$url = $setctl->get('streamurl').$phpenv['streamlocation'].'?streamsid='.$sid.'&c='.$u_cookieid;
			if (URLSECURITY) $url .= '&'.urlsecurity($f2->fdate, $sid);
			if ($setctl->get('sendfileextension')) $url .= '&file=.'.$streamtypes[$ftype][0]; 		
		}
	}
	return $url;
}

class asxgen
{
	function asxgen()
	{
		$this->crlf = "\r\n";
		$this->data = '<ASX version="3">'.$this->crlf.'<TITLE>WMA kPlaylist</TITLE>'.$this->crlf;		
	}

	function sendlink2($sid)
	{
		$f2 = new file2($sid, true);
		if ($f2->ifexists())
		{
			$fd = new filedesc($f2->fname);
			if ($fd->found && $fd->m3u)
			{
				$url = httpstreamheader3($fd->fid, $sid, $f2);
				if (strlen($url) > 0)
				{
					$this->data .= '<ENTRY>'.$this->crlf;					
					$this->data .= '<TITLE>'.$f2->gentitle(array('track', 'title', 'album', 'artist')).'</TITLE>'.$this->crlf;
					$this->data .= '<REF HREF="'.$url.'"/>'.$this->crlf;
					$this->data .= '</ENTRY>'.$this->crlf;
      			}
			}
		}
	}

	function start()
	{
		$this->data .= '</ASX>';
		if (db_guinfo('plinline')) $method = 'inline'; else $method = 'attachment';
		header('Content-Disposition: '.$method.'; filename=kp'.lzero(getrand(1,999999),6).'.asx');
		header('Content-Type: video/x-ms-asf');
		header('Content-Length: '.strlen($this->data));
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		echo $this->data;
	}
}

class m3ugenerator
{
	function m3ugenerator()
	{
		$this->extension = 'm3u';
		$pltype = db_guinfo('pltype');

		$this->obj = new m3ugen();

		switch($pltype)
		{
			case 5: 
					if (UTF8MODE)
					{
						$this->extension = 'm3u8';
						$this->obj = new m3ugen8(); 
					}
					break;							
			
			case 4: 
					if (class_exists('m3ugendisk')) $this->obj = new m3ugendisk(); 
					break;

			case 3: if (class_exists('kpwimpygen'))
					{
						$this->obj = new kpwimpygen();
						$this->extension = 'xml';
					}
					break;

			case 2: 
					$this->obj = new asxgen(); $this->extension = 'asx';
					break;
			
		}		

	}

	function getextension()
	{
		return $this->extension;
	}

	function sendlink2($sid)
	{
		$this->obj->sendlink2($sid);
	}

	function start()
	{
		$this->obj->start();
	}
}


class m3ugen
{
	function m3ugen()
	{
		$this->data = '';
		$this->crlf = "\r\n";
		$this->addcrlf = false;
		
		if (db_guinfo('extm3u'))
		{
			$this->setdata('#EXTM3U');
			$this->addcrlf = true;
		}
	}

	function mkextinf2($name, $lengths)
	{
		return $this->crlf.'#EXTINF:'.$lengths.','.$name;
	}
	
	function checkcrlf()
	{
		if ($this->addcrlf) $this->setdata($this->crlf);
			$this->addcrlf = false;
	}
	
	function sendlink2($sid)
	{
		$f2 = new file2($sid, true);
		if ($f2->ifexists())
		{
			$fd = new filedesc($f2->fname);
			if ($fd->found && $fd->m3u)
			{
				if (db_guinfo('extm3u')) $this->setdata($this->mkextinf2($f2->gentitle(), $f2->getlengths()));
				$this->checkcrlf();
				$this->setdata(httpstreamheader3($fd->fid, $sid, $f2));
				$this->addcrlf = true;
			}
		}
	}
	
	function setdata($data)
	{
		$this->data .= $data;
	}

	function start()
	{
		if (db_guinfo('plinline')) $method = 'inline'; else $method = 'attachment';
		$this->checkcrlf();
		header('Content-Disposition: '.$method.'; filename=kp'.lzero(getrand(1,999999),6).'.m3u');
		header('Content-Type: audio/x-mpegurl');
		header('Content-Length: '.strlen($this->data));
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		echo $this->data;
	}
}

class m3ugen8
{
	function m3ugen8()
	{
		$this->data = '';
		$this->crlf = "\r\n";
		$this->addcrlf = false;
		
		if (db_guinfo('extm3u'))
		{
			$this->setdata('#EXTM3U');
			$this->addcrlf = true;
		}
	}

	function mkextinf2($name, $lengths)
	{
		return $this->crlf.'#EXTINF:'.$lengths.','.$name;
	}
	
	function checkcrlf()
	{
		if ($this->addcrlf) $this->setdata($this->crlf);
			$this->addcrlf = false;
	}
	
	function sendlink2($sid)
	{
		$f2 = new file2($sid, true);
		if ($f2->ifexists())
		{
			$fd = new filedesc($f2->fname);
			if ($fd->found && $fd->m3u)
			{
				$title = $f2->gentitle();
				if (db_guinfo('extm3u')) $this->setdata($this->mkextinf2($title, $f2->getlengths()));
				$this->checkcrlf();
				$this->setdata(httpstreamheader3($fd->fid, $sid, $f2, true));
				$this->addcrlf = true;
			}
		}
	}
	
	function setdata($data)
	{
		$this->data .= $data;
	}

	function start()
	{
		if (db_guinfo('plinline')) $method = 'inline'; else $method = 'attachment';
		$this->checkcrlf();
		header('Content-Disposition: '.$method.'; filename=kp'.lzero(getrand(1,999999),6).'.m3u8');
		header('Content-Type: audio/x-mpegurl8');
		header('Content-Length: '.strlen($this->data));
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		echo $this->data;
	}
}

function playresource2($sid)
{
	$f2 = new file2($sid, true);
	if ($f2->ifexists())
	{
		$fd = new filedesc($f2->fname);
		if ($fd->m3u)
		{
			$m3ug = new m3ugenerator();
			$m3ug->sendlink2($sid);
			$m3ug->start();
		} else Kplay_senduser2($sid, 1); // for video/mpeg/etc
	}
}

function seek_stream($sid)
{
	global $cfg, $u_id;

	$f2 = new file2($sid, true);
	$fdesc = new filedesc($f2->fname);
	$filesize = $f2->fsize;
	$length = $filesize;

	// Set custom streaming buffer size.
	if (isset($cfg['stream_buffer_size']))
	{
		$buffer_size = $cfg['stream_buffer_size'];
	} else
	{
		$buffer_size = 1024 * 1024;
	}

	if (isset($_SERVER['HTTP_RANGE'])) {
		preg_match('/bytes=(\d+)-(\d+)?/', $_SERVER['HTTP_RANGE'], $matches);
		$offset = intval($matches[1]);
		if (!isset($matches[2])) {
		        $end = $offset + $buffer_size;
		        if ($end > $filesize - 1) {
		                $end = $filesize - 1;
		        }
		}
		else {
		        $end = $matches[2];
		}
		$length = $end + 1 - $offset;
		header("HTTP/1.1 206 Partial content");
		header("Content-Range: bytes $offset-$end/$filesize");
	}

	// Override mime type so flac will stream.
	if (isset($cfg['stream_flac_mime_override']))
	{
		header('Content-Type: ' . $cfg['stream_flac_mime_override']);
	} else
	{
		header('Content-Type: ' . $fdesc->mime);
	}

	header('Content-Disposition: attachment; filename="' . $f2->fname.'"');
	header('Connection: Keep-Alive');
	header('Content-Transfer-Encoding: binary');
	header('Accept-Ranges: bytes');
	header('Content-Length: ' . $length);

	// Poll to music history table for last streams.
	$upc = 0;
	$tid = 0;
	$lastux = getlasthistory($sid, $u_id);

	if ($offset == 0) {
		if (($lastux + 5) <= time()) {
			search_updatevote($sid);
			if ($u_id && $fdesc->logaccess) {
				$hid = addhistory($u_id, $sid, $tid);
			}
		} else {
			$hid = getlasthistory($sid, $u_id, true);
		}
	}
	if ($offset > 0) {
		$hid = getlasthistory($sid, $u_id, true);
	}

	updateactive($hid);
	$ph = new pollhid($hid, $f2->fsize, $offset);

	// Read file.
	$fp = fopen($f2->fullpath, 'rb');
	fseek($fp, $offset);
	while (!feof($fp))
	{
	        $buffer = fread($fp, 32 * 1024);
	        print $buffer;
                $upc += strlen($buffer);
		$ph->poll($upc);
	}
	fclose($fp);
	$ph->poll($upc, true);
	exit;
}

function seek_stream_mobile($sid)
{
	global $cfg;

	$f2 = new file2($sid, true);
	$fdesc = new filedesc($f2->fname);
	$filename = $f2->fullpath;
	$length = filesize($filename);

	// Override mime type so flac will stream.
	if (isset($cfg['stream_flac_mime_override']))
	{
		header('Content-Type: ' . $cfg['stream_flac_mime_override']);
	} else
	{
		header('Content-Type: ' . $fdesc->mime);
	}

	// Allow seek on mobile - prevent "Live Broadcast" in player.
	$fp = fopen($filename, "rb");
	header("Content-Length: $length");
	header('Content-Disposition: attachment; filename="' . $f2->fname.'"');
	fpassthru($fp);
	exit;
}

class kq_Measure
{
	function getmicrotime()
	{ 
		list($usec, $sec) = explode(' ', microtime());
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
	
	function stop()
	{
		$this->stop =  $this->getmicrotime();
	}

	function result($decimal=2)
	{
		return number_format($this->stop-$this->start, $decimal);
	}
}

function streamfp($fp, $kbit, $prebuffer=true, $hid = 0, $fsize=0, $fpos = 0)
{
	global $streamsettings;	
	$rpos = 0;
	$bread = ($kbit * 1000) / 8;
	$kqm = new kq_Measure();
	$kqm->setalarm($streamsettings['sleep']);
	if ($streamsettings['preload'] && $prebuffer)
	{
		$prebuff = ceil(($bread / 100) * (int)$streamsettings['preload']);
		$data = '';
		while (strlen($data) < $prebuff && !feof($fp)) $data .= fread($fp, $prebuff - strlen($data));
		$rpos += strlen($data);
		echo $data;
		flush();
	}

	$ph = new pollhid($hid, $fsize, $fpos);

	$breadbuf = ceil(($bread / 100) * (int)$streamsettings['buffer']);
	$precision = (int)$streamsettings['precision'];

	$kqm->start();
	while (!feof($fp) && !connection_aborted())
	{
		$data = '';
		while (strlen($data) < $breadbuf && !feof($fp)) $data .= fread($fp, $breadbuf-strlen($data));
		echo $data;
		$rpos += strlen($data);
		$ph->poll($rpos);
		flush();
		while (!$kqm->alarm()) usleep($precision);
		$kqm->start();
	}
	$ph->poll($rpos, true);
}

function gettranscmd($bitrate=128,$file,$cmd)
{
	$out = str_replace('%bitrate%', $bitrate, $cmd);
	$out = str_replace('%file%', $file, $out);
	return $out;
}

function kp_fseek(&$fp, $offsetfp, $mode, $f2)
{
	global $bd;
	if (@fseek($fp, $offsetfp, $mode) == -1)
	{
		if ($bd->isnetwork($f2->drive))
		{
			$kp = new kpnetwork();
			if ($kp->setdrive($f2->drive)) 
			{
				$fullpath = $kp->gensidurl($f2, $offsetfp);
				fclose($fp);
				$fp = fopen($fullpath, 'rb');
			}
		}
	}
}

function Kplay_senduser2($sid, $inline=0, $download=false, $tid = 0)
{
	global $win32, $_SERVER, $setctl, $streamsettings, $u_id, $lamebitrates, $cfg, $bd;
	ignore_user_abort(true);
	$hid = $bytepos = 0;
	$id3v2tag = '';
	$f2 = new file2($sid, true);
		
	if (($f2->ifexists() && $bd->gtype($f2->drive) == 'l') || $bd->gtype($f2->drive) == 'n')
	{
		$fp = @fopen($f2->fullpath, 'rb');
		
		if ($fp)
		{
			$fdesc = new filedesc($f2->fname);
			
			$uselame = false;

			if (!$download && $setctl->get('lamesupport'))
			{
				if ($fdesc->gid == 1 || ($fdesc->gid == 2 && $cfg['oggtranscode']))
				{
					if ($u_id && db_guinfo('lameperm') && db_guinfo('lamerate') != 0) 
					{
						$lamerate = db_guinfo('lamerate');
						$uselame = true; 
					}
					
					if (db_guinfo('forcelamerate'))
					{
						$lamerate = db_guinfo('forcelamerate');
						$uselame = true; 
					}
				}
			}
			
			$posfrom = 0;
			if (isset($_SERVER['HTTP_RANGE']) && ALLOWSEEK)
			{
				$data = explode('=',$_SERVER['HTTP_RANGE']);
        		$ppos = explode('-', trim($data[1]));
        		$posfrom = (int)trim($ppos[0]);
			}

			if ($posfrom == 0)
			{
				$lastux = getlasthistory($sid, $u_id);
				if (($lastux + 5) <= time()) 
				{
					search_updatevote($sid);
					if ($u_id && $fdesc->logaccess) 
					{
						if ($uselame) $tid = 4;
						$hid = addhistory($u_id, $sid, $tid);
					}
				} else $hid = getlasthistory($sid, $u_id, true);
			}

			if ($posfrom > 0) $hid = getlasthistory($sid, $u_id, true);

			if ($hid) updateactive($hid);
			
			$clen = $f2->fsize;
			$offsetfp = 0;

			if ($setctl->get('writeid3v2') && $fdesc->gid == 1 && !$download)
			{
				$id = fread($fp, 3);
				kp_fseek($fp, 0, SEEK_SET, $f2);
				if ($id == 'ID3') 
				{
					if ($cfg['enablegetid3'] && (GETID3_V == 17 || GETID3_V == 19)) // don't rewrite id3 unless we have getid3 1.7.x
					{						
						$taginfo = get_file_info($f2->fullpath, true);
						if (isset($taginfo['id3v2']['headerlength']) && is_numeric($taginfo['id3v2']['headerlength']))
						{
							$oid3v2tagl = $taginfo['id3v2']['headerlength'];
							if ($clen > $oid3v2tagl)
							{
								$clen -= $oid3v2tagl;
								$id3v2tag = kpgenerateid3v2tag($sid);
								$clen += strlen($id3v2tag);							
								$offsetfp = $oid3v2tagl;
								kp_fseek($fp, $offsetfp, SEEK_SET, $f2);
								$bytepos = $offsetfp;
							}
						}
					}
				} else
				{
					$id3v2tag = kpgenerateid3v2tag($sid);
					$clen += strlen($id3v2tag);
				}
			}

			$sendclen = false;
		
			if (!$inline)
			{		
				if ($download) header('Content-Disposition: attachment; filename="'.$f2->fname.'"'); 
				else
					header('Content-Disposition: attachment; filename='.$f2->gentitle()); 
				if (ALLOWSEEK && !$uselame) $sendclen = true;				
			} else
			{
				header('Content-Disposition: inline; filename='.$f2->fname); 
				if (!$uselame) $sendclen = true;			
			}

			header('Content-Type: '.$fdesc->mime);
			header('Content-Range: bytes '.$posfrom.'-');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Content-Transfer-Encoding: binary');
			header('Expires: '. gmdate('D, d M Y H:i ', time()+24*60*60) . ' GMT');
			header('Pragma: public');

			if ($posfrom > 0)
			{				
				header('HTTP/1.1 206 Partial Content', true);
				kp_fseek($fp, $offsetfp + $posfrom, SEEK_SET, $f2);
				$bytepos = $offsetfp + $posfrom;
			}

			if ($sendclen)
			{
				$rest = $clen - $posfrom;
				header('Accept-Ranges: bytes');
				header('Content-Length: '.$rest);
			} 
			
			// finally STREAM  - no more headers allowed.

			if (!empty($id3v2tag) && $posfrom == 0) echo $id3v2tag; 

			if ($download)
			{
				$upc = 0;
				if (db_guinfo('udlrate')) $udlrate = db_guinfo('udlrate');
					else
				if (DLRATE) $udlrate = DLRATE;
					else
				$udlrate = 0;
				
				$ph = new pollhid($hid, $f2->fsize, $bytepos);

				if ($udlrate && STR_ENGINE) streamfp($fp, $udlrate, false, $hid, $f2->fsize, $bytepos);
					else
				while (!feof($fp) && !connection_aborted()) 
				{	
					$dt = fread($fp, 16384);
					echo $dt;
					$upc += strlen($dt);
					$ph->poll($upc);
				}
				$ph->poll($upc, true);
			} else
			{			
				if ($uselame)
				{
					$descriptorspec = array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w'));
					
					switch($fdesc->gid)
					{
						case 1: $process = proc_open(gettranscmd($lamebitrates[$lamerate], $f2->fullpath, $cfg['lamecmd']), $descriptorspec, $pipes);
								break;
						
						case 2: $process = proc_open(gettranscmd($lamebitrates[$lamerate], $f2->fullpath, $cfg['oggcmd']), $descriptorspec, $pipes);
								break;
					}
					
					
					if (is_resource($process))
					{
						//if (function_exists('stream_set_blocking')) stream_set_blocking($pipes[1], 0);
						if ($setctl->get('streamingengine') && STR_ENGINE && db_guinfo('streamengine')) streamfp($pipes[1], $lamebitrates[$lamerate]);
							else while (!feof($pipes[1]) && !connection_aborted()) echo fgets($pipes[1], 1024);
						fclose($pipes[0]);
						fclose($pipes[1]);
						proc_close($process);
					}
				} else
				{
					if ($setctl->get('streamingengine') && STR_ENGINE && db_guinfo('streamengine'))
					{
							if (@$streamsettings['forcedefaultrate']) 
						streamfp($fp, $streamsettings['defaultrate'], true, $hid, $f2->fsize, $bytepos);
							else
							if (in_array ($f2->id3['bitrate'], $streamsettings['bitrates']) && $f2->id3['ratemode'] == 1)  // cbr
						streamfp($fp, $f2->id3['bitrate'], true, $hid, $f2->fsize, $bytepos);
							else
							{
								$rate = (int) $f2->id3['bitrate'] + ceil(($f2->id3['bitrate'] / 100) * 5);
								if ($rate < $streamsettings['defaultrate']) $rate = $streamsettings['defaultrate'];
								streamfp($fp, $rate, true, $hid, $f2->fsize, $bytepos);
							}
					} else 
					{	
						$ph = new pollhid($hid, $f2->fsize, $bytepos);

						$upc = 0;
						while (!feof($fp) && !connection_aborted()) 
						{
							$dt = fread($fp, 16384);
							echo $dt;
							$upc += strlen($dt);
							$ph->poll($upc);
						}
						$ph->poll($upc, true);
					}
				}
			}
			@fclose($fp);
		}
	}
	flush();
}

class pollhid
{
	function pollhid($hid, $fsize, $fpos)
	{
		$this->hid = $hid;
		$this->fsize = $fsize;
		$this->fpos = $fpos;
	}

	function poll(&$pos, $end = false)
	{
		if ($this->hid != 0)
		{
			if ($pos > (int)($this->fsize / 100) || $end)
			{
				$this->fpos += $pos;
				updatehistory($this->hid, $pos, $this->fpos);
				$pos = 0;
			}
		}
	}
}

function mkdirecturl($m3u)
{
	global $setctl, $cfg, $phpenv;

	$url = $setctl->get('streamurl').$phpenv['location'].$cfg['filepathurlprepend'];

	$numargs = func_num_args();
	if ($numargs > 1)
	{
		$arg = func_get_args();
		for ($i=1;$i<$numargs;$i++)
		{
			$url .= '/'.$arg[$i].'_'.$arg[$i+1];
			$i++;
		}
	} 

	if ($m3u)
	{
		$m3ug = new m3ugenerator();
		$url .= '/kplaylist.'.$m3ug->getextension();
	}
	
	return $url;
}

function print_album($drive, $name, $pdir, $ainf=null, $mark='', $hits = 0, $albumid=0)
{
	global $cfg;
	$extraref = '';
	$cname = checkchs($name, false);

	if (strlen($pdir) > 0) $pdir_64 = webpdir($pdir); else $pdir_64='';
	if ($albumid > 0 && $ainf['titles'] == 1) $extraref = '&amp;marksid='.$albumid; else if (!empty($mark)) $extraref = '&amp;mark='.urlencode($mark);

	if (db_guinfo('detailview')) $detailed = true; else $detailed = false;

	if ($cfg['filepathurl']) $playurl = mkdirecturl(true, 'p', $pdir_64, 'd', $drive, 'ftid', $albumid, 'action', 'playalbum');
			else $playurl = PHPSELF.'?p='.$pdir_64.'&amp;d='.$drive.'&amp;ftid='.$albumid.'&amp;action=playalbum';

			$urlprep = '&amp;p='.$pdir_64.'&amp;d='.$drive;

	$dirurl = PHPSELF.'?pwd='.$pdir_64.'&amp;d='.$drive.$extraref;

	if (strlen($cname) > db_guinfo('textcut')) $cname = kp_substr($cname, 0, db_guinfo('textcut')).' ..';	

	if (WINDOWPLAYER) 
	{
		$href = 'javascript: return false;'; 
		$kpwjs = new kpwinjs();	
		$onclick = 'onclick="javascript: '.$kpwjs->album($pdir_64, $drive).' return false;"';
	} else
	{
		$href = $playurl;
		$onclick = '';
	}
	
	if ($detailed)
	{		
		$ci = new coverinterface();
		$ci->setartist($ainf['artist'], $ainf['album']);
		$ci->setlocation($drive, $pdir);
		$ci->setdimension(100,100);
		$imgurl = '';
		$ci->geturl($imgurl);	
		echo eval(gethtml('detailedview'));
	} else
	{
		?>	
		<tr>
			<td width="27" height="24">
				<a href="<?php echo $href; ?>" <?php echo $onclick; ?> title="<?php echo get_lang(337); ?>" class="dir">
				<img alt="<?php echo get_lang(337); ?>" src="<?php echo getimagelink('play.gif'); ?>" border="0"/></a></td><td valign="middle">
		
		<a href="<?php echo $dirurl; ?>" class="ainfo"><?php echo $cname; ?></a>
		
		<?php
		
		if ($ainf) echo ' <span class="finfo">&nbsp;('.get_lang(151, $ainf['length'], $ainf['titles']).')</span>';
		if ($hits > 0) echo ' <span class="fdet">&nbsp;('.$hits.' '.get_lang(243).')</span>';
		?>
		</td></tr>
		<?php
	}	
}

class filedesc
{
	function filedesc($fname='')
	{
		global $streamtypes;
		
		if (strlen($fname) > 0) $this->fid = file_type($fname);
			else $this->fid = -1;

		$this->extension = '';
		$this->found = false;
		$this->mime = 0;
		$this->gid = 0;
		$this->m3u = 0;
		$this->view = 0;
		$this->logaccess = 0;		

		if ($this->fid != -1)
		{
			$this->found = true;
			$this->extension = $streamtypes[$this->fid][0];
			$this->mime = $streamtypes[$this->fid][1];
			$this->m3u = $streamtypes[$this->fid][2];
			$this->gid = $streamtypes[$this->fid][3];
			$this->view = $streamtypes[$this->fid][4];
			$this->logaccess = $streamtypes[$this->fid][5];		
		}		
	}	
}

function musictypes()
{
	global $streamtypes;

	$ids = array();
	foreach($streamtypes as $id => $row)
	{
		if (isset($row[2]) && $row[2] == 1) $ids[] = $id;
	}
	return $ids;
}

function file_type($name, $sindex=0)
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


function gen_aheader($album, $artist, $lengths, $titles, $year, $genre)
{
	$ret['album'] = $album;
	$ret['artist'] = $artist;
	$ret['titles'] = $titles;
	$ret['year'] = $year;
	$ret['lengths'] = $lengths;
	if ($genre != 255) $ret['genre'] = gengenres($genre); else $ret['genre'] = '';
	if ($lengths > 0) $ret['length'] = sprintf('%02d:%02d',floor($lengths/60), $lengths % 60); else $ret['length'] = '00:00';
	return $ret;
}

function gen_file_header($title = '', $artist = '', $album = '', $bitrate = 0, $lengths = 0, $genre = 255, $ratemode = 1, $track = 0, $year = 0, $comment = '', $ftypeid=0, $id3image = 0)
{
	$ret = array('title' => $title, 'artist' => $artist, 'album' => $album, 'length' => '00:00', 'bitrate' => $bitrate, 'lengths' => $lengths, 'genre' => $genre, 'ratemode' => $ratemode, 'track' => $track, 'year' => $year, 'comment' => $comment, 'ftypeid' => $ftypeid, 'id3image' => $id3image);
	if ($lengths > 0) $ret['length'] = sprintf('%02d:%02d', floor($lengths/60), $lengths % 60);
	return $ret;
}

function gen_file_info_sid($row)
{
	if ($row) return gen_file_header($row['title'], $row['artist'], $row['album'], $row['bitrate'], $row['lengths'], $row['genre'], $row['ratemode'], $row['track'], $row['year'], $row['comment'], $row['ftypeid'], $row['id3image']);
	return false;
}

function get_searchrow($sid)
{
	return @db_fetch_assoc(db_execquery('SELECT * FROM '.TBL_SEARCH.' WHERE id = '.$sid, true));
}

function unstig($field)
{
	if (is_array($field))
		foreach($field as $val) return trim($val);
	else return $field;
}

function tunstig($field, $arrayi)
{
	if (isset($arrayi[$field]))
	{
		$dt = trim(unstig($arrayi[$field]));
		if (!empty($dt)) return true;
	}
	return false;
}

function array_fetch($arrayi, &$ret)
{
	if (tunstig('title', $arrayi)) $ret['title'] = unstig($arrayi['title']);
	if (tunstig('artist', $arrayi)) $ret['artist'] = unstig($arrayi['artist']);
	if (tunstig('album', $arrayi)) $ret['album'] = unstig($arrayi['album']);
	if (tunstig('track', $arrayi)) $ret['track'] = unstig($arrayi['track']);
	if (tunstig('tracknumber', $arrayi)) $ret['track'] = unstig($arrayi['tracknumber']);
	if (tunstig('track_number', $arrayi)) $ret['track'] = unstig($arrayi['track_number']);
	if (tunstig('year', $arrayi)) $ret['year'] = unstig($arrayi['year']);
	if (tunstig('genreid', $arrayi)) $ret['genre'] = unstig($arrayi['genreid']);
	if (tunstig('comment', $arrayi)) $ret['comment'] = unstig($arrayi['comment']);

	if (tunstig('content_type', $arrayi)) $ret['genre'] = getgenreidfromName(unstig($arrayi['content_type']));

	
}

function extractnum($str)
{
	$out = '';
	for ($i=0,$c=strlen($str);$i<$c;$i++)
	{
		if (is_numeric($str[$i])) $out .= $str[$i];
	}
	return $out;
}

function getid3order()
{
	global $cfg;
	$order = array();
	
	$pri1 = $cfg['id3tagspri']['id3v1'];
	$pri2 = $cfg['id3tagspri']['id3v2'];
	
	if ($pri1 && $pri1 < $pri2) $order[] = 1;
	if ($pri2) $order[] = 2;
	if ($pri1 && $pri2 < $pri1) $order[] = 1;
	
	return $order;
}

function getgenreidfromName($name)
{
	global $kpgenre, $cfg;

	$id = $kpgenre->getbyname($name);

	if ($id == -1 && $cfg['genre_auto_create'])
	{
		$kpgenre->addname($name);
		$id = $kpgenre->getbyname($name);
	}
	
	if ($id == -1) return 255;

	return $id;
}

function get_file_info($name, $return_finfo=false)
{
	global $streamtypes, $cfg;
	$ret = gen_file_header();
	$ret['ftypeid'] = file_type($name);
	$finfo = null;
	if ($cfg['enablegetid3'])
	{
		if (GETID3_V == 16)
		{
			$finfo = GetAllFileInfo($name, file_extension($name));

			if (isset($finfo['tags']) && is_array($finfo['tags']))
			{
				$use = '';
				foreach ($finfo['tags'] as $tagroot)
				{
					$use = $tagroot;
					if ($use == 'id3v2') break; // prefer id3v2.
				}
			}

			$ret['bitrate'] = isset($finfo['bitrate']) ? round($finfo['bitrate']) / 1000 : 0;
			$ret['ratemode'] = isset($finfo['audio']['bitrate_mode']) ? ratetypeid($finfo['audio']['bitrate_mode']) : 0;
			$ret['length'] = isset($finfo['playtime_string']) ? $finfo['playtime_string'] : '00:00';
			$ret['lengths'] = isset($finfo['playtime_seconds']) ? (int)round($finfo['playtime_seconds']) : 0;

			if (!empty($use) && @is_array($finfo[$use]))
			{			
				if ($use == 'id3v2' && isset($finfo['id3v1'])) 
				{					
					$order = getid3order();											
					foreach($order as $idtag)
					{
						switch($idtag)
						{
							case 1: array_fetch($finfo['id3v1'], $ret); break;
							case 2: array_fetch($finfo[$use]['comments'], $ret); break;
						}
					}					
				} else
				{
					if ($use == 'id3v2') array_fetch($finfo[$use]['comments'], $ret);
						else array_fetch($finfo[$use], $ret);
				}
			} else if (@is_array($finfo['comments'])) array_fetch($finfo['comments'], $ret);
		} else
		if (GETID3_V == 17 || GETID3_V == 19)
		{		
			$getID3 = new getID3();
			if (UTF8MODE) $getID3->encoding = 'UTF-8';
			$finfo = $getID3->analyze($name);

			$ret['length'] = isset($finfo['playtime_string']) ? $finfo['playtime_string'] : '00:00';
			$ret['lengths'] = isset($finfo['playtime_seconds']) ? round($finfo['playtime_seconds']) : 0;
			isset($finfo['audio']['bitrate']) ? $ret['bitrate'] = round($finfo['audio']['bitrate']) / 1000 : 0;
			if (isset($finfo['bitrate'])) $ret['bitrate'] = round($finfo['bitrate']) / 1000;
			$ret['ratemode'] = isset($finfo['audio']['bitrate_mode']) ? ratetypeid($finfo['audio']['bitrate_mode']) : 0;

			if (isset($finfo['tags']) && is_array($finfo['tags']))
			{
				$first = '';
				foreach ($finfo['tags'] as $tagroot => $vals)
				{
					$use = $tagroot;
					if ($use == 'id3v2') break; // prefer id3v2.
				}			
				
				if (!empty($use))
				{
					if ($use == 'id3v2' && isset($finfo['tags']['id3v1'])) 
					{
						$order = getid3order();											
						foreach($order as $idtag)
						{
							switch($idtag)
							{
								case 1: array_fetch($finfo['tags']['id3v1'], $ret); break;
								case 2: array_fetch($finfo['tags']['id3v2'], $ret); break;
							}
						}			
					} else array_fetch($finfo['tags'][$use], $ret);	

					if (isset($finfo['id3v2']['APIC'][0]) && is_array($finfo['id3v2']['APIC'][0])) 
					{
						$apic = $finfo['id3v2']['APIC'][0];
						if (isset($apic['mime']) && isset($apic['data']) && !empty($apic['data'])) $ret['id3image'] = 1;
					}

					if (isset($finfo['tags'][$use]['genre'])) $ret['genre'] = getgenreidfromName($finfo['tags'][$use]['genre'][0]);
						else 
					if (isset($finfo['tags'][$use]['content_type'][0])) 
					{	
						$genreinfo = $finfo['tags'][$use]['content_type'][0];
						$gnum = extractnum($genreinfo);
						if (is_numeric($gnum)) $ret['genre'] = $gnum;
					}					
				}
			}
		}
	} else
	{
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
						$ret['track'] = (int)$id3->track;
						$ret['year'] = (int)$id3->year;
						$ret['comment'] = trim($id3->comment);
						if ($id3->bitrate) $ret['bitrate'] = $id3->bitrate;
						if ($id3->lengths > 0) $ret['lengths'] = $id3->lengths;
						$ret['genre'] = $id3->genreno;
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
	}
	
	if (!is_numeric($ret['track'])) 
	{
		$slashp = strpos($ret['track'], '/', 1);
		if ($slashp !== false) $ret['track'] = substr($ret['track'], 0, $slashp);
		if (!is_numeric($ret['track'])) $ret['track'] = 0;
	}

	if (!is_numeric($ret['year'])) $ret['year'] = 0;
	if (!is_numeric($ret['lengths'])) $ret['lengths'] = 0;
	if (!is_numeric($ret['bitrate'])) $ret['bitrate'] = 0;
		
	if ($return_finfo) return $finfo;

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

function file_parse($f2, $link, $class, $str = FILETEMPLATE)
{
	$str2 = array(0 => '', 1 => '', 2 => '');
	$slot = 0;
	$fullfilled = true;
	$conditional = false;
	$level = 0;
	$or = false;
	for ($i=0,$l=strlen($str);$i<$l;$i++)
	{
		switch ($str[$i])
		{
			case '[': 
					$conditional = true;
					$slot = 1;
					break;
			case ']': 
				if ($level == 0)
				{
					if ($fullfilled) $str2[0] .= $str2[1]; else if ($or) $str2[0] .= $str2[$slot];
					$str2[1] = '';
					$str2[2] = '';
					$fullfilled = true;
					$conditional = false;  
					$slot = 0;
					$or = false;
				}
				break;
			case '|':			
				if ($conditional) 
				{
					$or = true;
					$slot = 2;
				}
				break;
			case '%':
				if ($i + 1 >= $l) 
				{
					$str2[$slot] .= $str[$i]; 
					break; 
				}
				$add = '';
				$match = true;
				switch ($str[$i+1])
				{
					case 'f': $add = checkchs($f2->fname, true); break;
					case 'a': $add = checkchs($f2->id3['artist'], false); break;
					case 'l': $add = checkchs($f2->id3['album'], false); break;
					case 't': $add = checkchs($f2->id3['title'], false); break;
					case 'o': $add = checkchs($f2->id3['comment'], false); break;

					case 'b': $add = $f2->id3['bitrate']; break;
					case 'r': if ($f2->id3['track'] != 0) $add = $f2->id3['track']; break;
					case 'R': if ($f2->id3['track'] != 0) $add = lzero($f2->id3['track']); break;
					case 'S': if ($f2->fsize > 1048576) $add = get_lang(272, number_format($f2->fsize / 1048576,2)); else
									if ($f2->fsize > 8192) $add = get_lang(273, number_format($f2->fsize / 1024,1)); else
										$add = get_lang(274, $f2->fsize);
								break;
					case 's': $add = $f2->id3['length']; break;
					case 'h': $add = $f2->hits; break;
					case 'y': if ($f2->id3['year'] != 0) $add = $f2->id3['year']; break;
					case 'i': $add = $link; break;
					case 'c': $add = $class; break;
					case 'g': if ($f2->id3['genre'] != 255) $add = gengenres($f2->id3['genre']); break;
					default: $match = false; break;
				}
				if ($match) 
				{
					$i++;
					$str2[$slot] .= $add;
				} else $str2[$slot] .= '%';
				if ($conditional && empty($add)) $fullfilled = false;
				break;
			default: $str2[$slot] .= $str[$i]; break;
		}
	}	
	return $str2[0];
}

function urlsecurity($fdate, $sid, $tag=true)
{
	$code = bin2hex(pack('l2', time(), $fdate+$sid));
	if ($tag) return 'stag='.$code; else return $code;
}

function chksecurity($sid=0)
{	
	global $cfg, $bd;

	if ($sid != 0 && is_numeric($sid)) 
	{
		$f2 = new file2($sid);
		if ($bd->accessok($f2->drive))
		{
			if (URLSECURITY)
			{
				$ok = false;
				if (frm_isset('stag'))
				{
					$datat = @unpack('l2', pack('H*', frm_get('stag')));
					
					if (isset($datat[1]) && is_numeric($datat[1]) && isset($datat[2]) && is_numeric($datat[2]))
					{
						if ($f2 && $f2->fexists)
						{
							$chkval = $f2->fdate + $sid;						
								
							if ($datat[2] == ($f2->fdate + $sid))
							{
								if (($datat[1] + $cfg['urlsecurityvalidtime']) >= time() || $cfg['urlsecurityvalidtime'] == 0) $ok = true;
							}
						}
					}
				}
				return $ok;
			} else return true;
		} else return false;
	}
	return false;
}

function parseurl($url, $title = '', $artist = '', $album = '')
{
	$urlr = str_replace('%title', urlencode($title), $url);
	$urlr = str_replace('%artist', urlencode($artist), $urlr);
	$urlr = str_replace('%album', urlencode($album), $urlr);
	return $urlr;
}

function is_mobile() {
	$useragent=$_SERVER['HTTP_USER_AGENT'];
	if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4))) return true;
	else return false;
}

function print_file($sid, $showlink=0, $includeabsolute=0, $f2=false, $smarksid = -1)
{
	global $u_cookieid, $setctl, $cfg, $marksid;
	
	if (!$f2) $f2 = new file2($sid, true);
	$inf = $f2->getid3();
	$title = $f2->gentitle(array('title', 'album'));	

	echo '<tr><td><input type="checkbox" name="fsel[]" value="'.$sid.'"/> ';

	if ($cfg['id3editor'] && db_guinfo('u_access') == 0 && function_exists('file_id3editor'))
	{
		$id3link = '&amp;id3sid='.$sid;
		echo '<a href="javascript: void(0);" onclick="'.jswin('id3editor', '?action=id3edit'.$id3link).'">id3</a>&nbsp;';
	}

	if (ALLOWDOWNLOAD && db_guinfo('u_allowdownload'))
	{
		if (URLSECURITY) $urlextra = '&amp;'.urlsecurity($f2->fdate, $sid); else $urlextra = '';
		echo '<span class="file"><a href="'. PHPSELF. "?downloadfile=".$sid.'&amp;c='.$u_cookieid.$urlextra.'">'.
		'<img src="'.getimagelink('saveicon.gif').'" alt="'.get_lang(117).'" border="0"/></a></span> ';
	}

	if (SHOWLYRICSLINK && !empty($inf['title']) && !empty($inf['artist'])) 
	{
		$url = parseurl($setctl->get('lyricsurl'), $inf['title'], $inf['artist'], $inf['album']);
		
		echo '<a class="file" onclick="'.jswin('lyrics', $url, 410, 675, true, 'newwinscroll', '').'"';
		echo ' href="javascript: void(0);"><img border="0" src="'.getimagelink('lyrics.gif').'" alt="'.get_lang(303).'"/></a> ';
	}

	if (MAILMP3 && db_guinfo('allowemail') && class_exists('mailmp3'))
	echo '<a href="javascript: void(0);" onclick="'.jswin('mp3mail', '?action=sendmail&amp;id='.$sid.'&amp;c='.$u_cookieid, 195, 390).'">'.
	'<img src="'.getimagelink('sendmail.gif').'" alt="'.get_lang(223).'" border="0"/></a>&nbsp;';

	if ($showlink) echo '<a href="'.PHPSELF.'?pwd='.$f2->getdir64().'&amp;d='.$f2->drive.'&amp;marksid='.$smarksid.'" title="'.get_lang(116, checkchs($f2->relativepath)).'">'.'<img src="'.getimagelink('link.gif').'" alt="'.get_lang(116, checkchs($f2->relativepath)).'" border="0"/></a>&nbsp;';
	
	if (ismarked($f2->fname.$title) || $f2->sid == $marksid) $useclass = 'filemarked'; else $useclass = 'file';

	$fd = new filedesc($f2->fname);

	if (WINDOWPLAYER && $fd->m3u) 
	{
		$kpwjs = new kpwinjs();
		$link = '" onclick="javascript: '.$kpwjs->single($f2->sid).' return false;';
	} else	
	{
		$link = $f2->mklink();
	}

	echo file_parse($f2, $link, $useclass);
	echo '</td></tr>';
}

function listfiles($where, &$in, $drive)
{
	if ($d = @opendir($where)) 
	{
		while (false !== ($file = readdir($d))) if (is_file($where.$file) && file_type($file) != -1) $in[] = array($file, $drive, filesize($where.$file),filemtime($where.$file));
		closedir($d);
	}
}

function disksync($dir, $drive, $stop = false)
{
	global $bd;

	if (!updaterunning())
	{
		$flist = array();
		$dblist = array();
		$found = 0;

		$kpdir = new kpdir();
		$kpdir->setpwd($dir); 
		$kpdir->setdrive($drive);							
		if ($kpdir->readdrive($dir, $drive))
		{
			$dbres = $kpdir->filesql('fname,fsize,mtime');							

			while ($row = db_fetch_row($dbres)) $dblist[] = array($row[0], $row[1], $row[2]);
			
			listfiles($bd->getpath($drive).$dir, $flist, $drive);

			$c2=count($flist);
			$c=count($dblist);

			if ($c2 != $c)
			{
				db_execquery('UPDATE '.TBL_SEARCH.' SET f_stat = 1, ltime = '.time().' WHERE fpath = "'.myescstr($dir).'" AND drive = '.$drive);
				for ($i=0;$i<$c2;$i++) updatesingle($bd->getpath($flist[$i][1]).$dir.$flist[$i][0]);
				cache_updateall();
			} else
			{
				$changes = false;
				for ($i=0;$i<$c;$i++) for ($i2=0;$i2<$c2;$i2++) if ($dblist[$i][0] == $flist[$i2][0] && $dblist[$i][1] == $flist[$i2][2] && $dblist[$i][2] == $flist[$i2][3]) $flist[$i2][0] = '';
				for ($i2=0;$i2<$c2;$i2++) if (strlen($flist[$i2][0]) > 0) 
				{
					$changes = true;
					updatesingle($bd->getpath($flist[$i2][1]).$dir.$flist[$i2][0]);
				}
				if ($changes) cache_updateall();
				if ($changes && !$stop) disksync($dir, $drive, true);
			}
		}
	}
}

function fsearch($dir, $drive = 0, $r='id', $recurse = false, $fast=false)
{
	$order = 'dirname,free';
	if (ORDERBYTRACK) $order = 'track,dirname,free';

	if ($recurse)
		$dir_oper = 'fpath like "'.myescstr($dir).'%"';
	else
		$dir_oper = 'fpath = "'.myescstr($dir).'"';

	if (is_array($drive)) $drivesql = mkor($drive, 'drive'); else $drivesql = 'drive = '.$drive; 

	$sql = 'SELECT '.$r.' FROM '.TBL_SEARCH.' WHERE f_stat = 0 AND '.$dir_oper.' AND ('.$drivesql.') ORDER BY '.$order.' ASC';

	return db_execquery($sql, $fast);
}

function fmatch($file, $pattern)
{
	$match = '';
	$fpos = 0;
	$any = false;
	for ($i=0,$c=strlen($pattern);$i<=$c;$i++)
	{
		if ($i == $c || $pattern[$i] == '*')
		{
			if (!empty($match))
			{
				$found = true;
				$p = strpos($file, $match, $fpos);
				if ($p !== false)
				{
					if (($i - strlen($match)) != $p && !$any) $found = false;
					$fpos = $p + strlen($match);
				} else $found = false;
				$any = false;
				if (!$found) return false;					
			} 		
			if ($i < $c) $any = true;
			$match = '';
		} else $match .= $pattern[$i];
	}
	if (!$any && strlen($file) > $fpos) return false;
	return true;
}

function getsidspost($recur = true)
{
	$sids = array();
	if (frm_isset('dsel')) $sids = retrievesids(frm_get('dsel', 3));
	if (frm_isset('fsel')) 
	{
		$fsel = frm_get('fsel', 3);
		for ($i=0,$c=count($fsel);$i<$c;$i++) if (is_numeric($fsel[$i])) $sids[] = $fsel[$i];
	}

	return $sids;
}

function retrievesids($arr)
{
	$ids = array();
	for ($i=0,$c=count($arr);$i<$c;$i++) 
	{
		if (is_numeric($arr[$i])) $ids[] = $arr[$i];
		else
		{
			$kpdir = new kpdir();
			if ($kpdir->getpathpost($arr[$i]))
			{
				$res = $kpdir->filesql_recur();
				while ($row = db_fetch_row($res)) $ids[] = $row[0];
			}
		}
	}
	return $ids;
}

function mkdirlinks($path, $drive)
{
	$out = '';
	$dir = '';
	$dirs = explode('/', $path);
	for ($i=0,$c=count($dirs);$i<$c;$i++)
	{
		if (strlen($dirs[$i]) > 0)
		{
			$dir .= $dirs[$i].'/';
			if ($i == 0) $add = '/'; else $add = ''; 
			$out .= '<a class="dirheadline" href="'.PHPSELF.'?pwd='.webpdir($dir).'&amp;d='.$drive.'">'.checkchs($add.$dirs[$i]).'/</a>';
		}
	}
	return $out;	
}

function showdir($pdir, $text='', $drive, $ximg='')
{
	$root = '<a href="'.PHPSELF.'?action=root"><img src="'.getimagelink('root.gif').'" title="'.get_lang(119).'" alt="'.get_lang(119).'" border="0"/></a> &nbsp;';
	
	$dirname = '';
	$dirs = explode('/', $pdir);
	if (count($dirs) > 2) for ($i=0,$c=count($dirs) - 2;$i<$c;$i++) $dirname .= $dirs[$i].'/';
	
	if (strlen($text) == 0)
	{
		$dirlink = $root;
		$divs = mkdirlinks($pdir, $drive);
		if (strlen($divs) > 0) $dirlink .= '<a title="'.get_lang(118).'" href="'.PHPSELF.'?pwd='.webpdir($dirname).'&amp;d='.$drive.'"><img src="'.getimagelink('cdback.gif').'" alt="'.get_lang(118).'" border="0"/></a>&nbsp;&nbsp;'.$divs;
	} else $dirlink = $root.$text;

	eval(gethtml('dirheader'));
}

function okpath($checkdir)
{
	$srcstr1 = '../';	
	if (strlen($checkdir) > 0)
	{
		if ($checkdir[0] == '/') return false;
		$i = strpos ( $checkdir, $srcstr1);
		if ($i !== false) return false;
	}
	return true;
}

function kplaylist_filelist($pwd, $d, $n3)
{
	global $runinit, $mark, $marksid, $setctl, $bd, $cfg, $valuser;
	
	$kpdir = new kpdir();
	$kpdir->setpwd(base64_decode($pwd)); 
	$kpdir->setdrive($d);

	if (strlen($n3) > 0)
	{
		$ln = explode('_', $n3);
		if (count($ln) == 2) $kpdir->finddest($ln[0], $ln[1]);				
	}

	if (frm_isset('mark') && !frm_empty('mark')) $mark = explode(' ', strtoupper(trim(frm_get('mark')))); else $mark = array();
	if (frm_ok('marksid', 1)) $marksid = frm_get('marksid', 1);
	
	$kpd = new kpdesign();
	$kpd->top();

	$list = true;

	if ($valuser->isadmin())
	{
		if ($bd->getcnt() == 1 && $bd->getpath(0) == '/path/to/my/music/archive/')
		{	
			$list = false;
			eval(gethtml('welcome'));
		} 

		if ($setctl->get('basedir_changed') && $bd->getpath(0) != '/path/to/my/music/archive/') 
		{
			$setctl->set('basedir_changed', 0);
			if ($setctl->get('base_dir') != $setctl->get('oldbase_dir'))
			{
				$setctl->set('oldbase_dir', $setctl->get('base_dir'));
				$list = false;
				eval(gethtml('basedirchange'));
			}
		} else
		{
			if ($setctl->get('reupdate')) 
			{
				$setctl->set('reupdate', 0);
				$list = false;
				eval(gethtml('needupdate'));
			}
		}
	}
	
	$dcnt = $fcnt = 0;

	if ($list)
	{
		$kpdir = new kpdir();
		$kpdir->setdrive($d);
		$kpdir->setpwd($runinit['pdir']);
		if (!$kpdir->determine()) access_denied();
	
		showdir($kpdir->pwd, '', $d);

		echo '<table width="100%" border="0" cellspacing="0" cellpadding="0">';

		if ($cfg['mergerootdir']) $kpdir->merge();
		$kpdir->dsort();
		
		$dcnt = $kpdir->show();
		$fcnt = $kpdir->showfiles($dcnt);
		if ($fcnt == 0 && $dcnt == 0) echo '<tr><td class="file">'.get_lang(156).'</td></tr>';

		echo '</table>';

	}
	endmp3table(1, $dcnt, $fcnt);
	$kpd->bottom();
}


class kparchiver
{
	function kparchiver()
	{
		$this->files = array();
		$this->tempfile = '';
		$this->destfile = '';

		$this->archiverinfo = false;
	}

	function getarchiveline($destination, $file, $flist = '')
	{
		$out = $this->archiverinfo[2];
		$out = str_replace('%D', $destination, $out); 
		$out = str_replace('%F', $file, $out); 
		$out = str_replace('%LIST', $flist, $out);
		return $out;
	}
	
	function setfile($file)
	{
		$this->files[] = $file;
	}

	function addarchivetbl($dest)
	{
		global $valuser;
	
		$sql = 'INSERT INTO '.TBL_ARCHIVE.' SET uid = '.$valuser->getid().', utime = '.time().', fpath = "'.myescstr($dest).'"';
		$res = db_execquery($sql);
		if ($res) return db_insert_id();	
		return 0;
	}

	function getarchivefile($id)
	{
		global $valuser;
		$sql = 'SELECT fpath FROM '.TBL_ARCHIVE.' WHERE uid = '.$valuser->getid().' AND aid = '.$id;
		$res = db_execquery($sql);
		if ($res) 
		{
			$row = db_fetch_assoc($res);
			return $row['fpath'];
		}
		return '';
	}

	function determinesize()
	{
		$sizebytes = 0;
		
		for ($i=0,$c=count($this->files);$i<$c;$i++)
		{
			$f2 = new file2($this->files[$i]);
			if ($f2->ifexists()) $sizebytes += $f2->fsize;
		}

		$sizemb = ceil($sizebytes / 1048576);

		return $sizemb;
	}

	function createtemp($ext)
	{
		global $cfg;
		
		$tf = tempnam($cfg['archivetemp'], 'kppack');
		if (strlen($tf) > 0 && file_exists($tf))
		{
			$this->tempfile = $tf;			
			$this->destfile = $tf . '.'.$ext;
			return true;
		}
		return false;
	}

	function downloadpage($mime, $id, $fileext)
	{
		?>
		<form style="margin:0;padding:0" action="<?php echo PHPSELF; ?>" method="post">			
		<input type="hidden" name="action" value="downloadarchive"/>
		<input type="hidden" name="fileid" value="<?php echo $id; ?>"/>
		<input type="hidden" name="mime" value="<?php echo $mime; ?>"/>
		<table width="95%" cellpadding="0" cellspacing="0" border="0" align="center">
		<tr>
			<td class="notice"><?php echo get_lang(65); ?></td>
			<td><input type="text" class="fatbuttom" name="filename" value="<?php echo 'kpdl'.date('hi').'.'.$fileext; ?>"/></td>
		</tr>
		<tr>
			<td height="5"></td>
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
	}

	function logdl()
	{
		global $valuser;
		for ($i=0,$c=count($this->files);$i<$c;$i++)
		{
			$f2 = new file2($this->files[$i]);
			if ($f2->ifexists()) 
			{
				$fdesc = new filedesc($f2->fname);
				if ($fdesc->logaccess) $hid = addhistory($valuser->getid(), $f2->sid, 3);
			}				
		}
	}

	function debugerr($id)
	{
		switch($id)
		{
			case 1: $msg = 'Could not create a temporary file'; break;
			case 2: $msg = 'Unable to initialize given archiver'; break;
			case 3: $msg = 'Error occured during generation.'; break;
			case 4: $msg = 'Could not find archive'; break;
			case 5: $msg = 'Destfile (archive) seems empty'; break;
			case 6: $msg = 'Unable to insert into archive table.'; break;
			default: $msg = 'Unknown error'; break;
		}

		return $msg;
	}

	function execute()
	{
		global $win32, $cfg, $archivers;		

		kprintheader(get_lang(260));

		$usearc = db_guinfo('archer');
		if (isset($archivers[$usearc]) && $archivers[$usearc][0] == 1) 
		{		
			$this->archiverinfo = $archivers[$usearc];
			
			$filegenerated = false;

			$totalmb = $this->determinesize();

			if ($totalmb <= db_guinfo('archivesize') || db_guinfo('archivesize') == 0)
			{
				$debugcode = 0;

				$method = 0;
			
				if (strpos($this->archiverinfo[2],'%LIST') !== false) $method = 1;
					else
				if (strpos($this->archiverinfo[2],'INB1') !== false) $method = 2;

				if ($method == 1) $dual = true; else $dual = false;
				
				if ($this->createtemp($this->archiverinfo[1]))
				{
					$initerror = false;

					switch($method)
					{
						case 1: 
								$fp = fopen($this->tempfile, 'ab');
								if ($fp)
								{
									for ($i=0,$c=count($this->files);$i<$c;$i++)
									{
										$f2 = new file2($this->files[$i]);
										if ($f2->ifexists()) fwrite($fp, $f2->fullpath.$cfg['archivefilelist_cr']);
									}
									fclose($fp);
								} else $initerror = true;
								break;
						case 2:
								$zip = new ZipArchive();
								$php5code = 'if ($zip->open($this->destfile, ZIPARCHIVE::CREATE) !== true) $initerror = true;';
								eval($php5code);
								//if ($zip->open($this->destfile, ZIPARCHIVE::CREATE) !== true) $initerror = true;
								break;
					}

					if (!$initerror)
					{
						echo '<div id="up_status2" class="notice">0%</div><br/>';
						flush();
						
						$generror = false;

						if ($method == 1)
						{
							$out = array();
							$ret = -1;
							$run = $this->getarchiveline($this->destfile, $f2->getfullpath($win32), $this->tempfile);
							if ($cfg['archivemodedebug']) echo($run).'<br/>';
							exec($run, $out, $ret);
							if ($ret != 0) $generror = true;
							updateup_status('100%');
						} else
						{
							$cnt = 0;
							$filescnt = count($this->files);
							for ($i=0;$i<$filescnt;$i++)
							{
								$f2 = new file2($this->files[$i]);
								if ($f2->ifexists()) 
								{
									
									switch($method)
									{
										case 2:
											$zip->addFile($f2->getfullpath($win32));
											break;
									
										case 0:
											$out = array();
											$ret = -1;
											$run = $this->getarchiveline($this->destfile, $f2->getfullpath($win32));
											if ($cfg['archivemodedebug']) echo($run).'<br/>';
											exec($run, $out, $ret);
											if ($ret != 0) $generror = true;
											break;									
									}
								}
								$cnt++;
								$per = ($cnt / $filescnt) * 100;
								$per = number_format($per, 0).'%';
								updateup_status($per. ' .. '.$f2->fname);
								flush();
							}
						}

						// deconstructer
							
						if ($method == 2) $zip->close();

						if (!$generror)
						{
							if (file_exists($this->destfile))
							{
								if (filesize($this->destfile) > 0)
								{
									$id = $this->addarchivetbl($this->destfile);
									if ($id > 0)
									{
										$this->logdl();
										
										$this->downloadpage($this->archiverinfo[3], $id, $this->archiverinfo[1]);
										$filegenerated = true;
										@unlink($this->tempfile);
									} else $debugcode = 6;
								} else $debugcode = 5;
							} else $debugcode = 4;
						} else $debugcode = 3;
					} else $debugcode =  2;
				} else $debugcode = 1;
			} else
			{
				echo '<font class="notice">'.get_lang(328, $totalmb, db_guinfo('archivesize')).'</font>'; 
				kprintend();
				return false;
			}
		} else
		{
			echo '<font class="notice">'.get_lang(363).'</font>'; 
			kprintend();
			return false;
		}
		
		if (!$filegenerated) 
		{
			echo '<font class="notice">'.get_lang(167).'</font>'; 
			if ($cfg['archivemodedebug']) echo $this->debugerr($debugcode);
		}
		kprintend();
	}

	function download($file, $mime, $name)
	{
		$fp = fopen($file, 'rb');
		ignore_user_abort(true); 
		if ($fp)
		{
			header('Content-Type: '.$mime);
			header('Content-Disposition: attachment; filename="'.$name.'"');
			header('Content-Length: '.filesize($file));
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Content-Transfer-Encoding: binary');
			header('Expires: '. gmdate('D, d M Y H:i ', time()+24*60*60) . ' GMT');
			header('Pragma: public');

			if (db_guinfo('udlrate')) $udlrate = db_guinfo('udlrate');
				else
			if (DLRATE) $udlrate = DLRATE;
				else
			$udlrate = 0;

			if ($udlrate && STR_ENGINE) streamfp($fp, $udlrate, false);
				else
			while (!feof($fp) && !connection_aborted()) echo fread($fp, 32768); 
			@fclose($fp);
		}
	}
}


class kpdir
{
	function kpdir()
	{
		$this->dirlist = array();
		$this->pwd = '';
		$this->drive = -1;
		$this->drives = array();
	}	

	function setpwd($pwd)
	{
		global $runinit;
		$runinit['pdir'] = $this->pwd = $pwd;
		$runinit['pdir64'] = webpdir($pwd, false);
	}

	function setdrive($drive)
	{
		global $runinit;
		$runinit['drive'] = $this->drive = $drive;
	}

	function getpathpost($line)
	{
		$ln = explode('_', urldecode($line));
		if (count($ln) == 4)
		{
			if (is_numeric($ln[3]))
			{
				$this->setpwd(base64_decode($ln[2]));
				$this->setdrive($ln[3]);
				$this->finddest($ln[0], $ln[1]);
				$this->determine();
				if (strlen($this->pwd) > 0) return true;
			}
		}
		return false;
	}

	function readres($where, $drive)
	{
		global $bd;

		if ($bd->isdrive($drive))
		{
			if (VIRTUALDIR || $bd->isnetwork($drive)) $this->readresvirtual($where, $drive); 
					else $this->readresdisk($where, $drive);
		}
	}

	function readresdisk($where, $drive)
	{
		global $cfg, $bd;

		if ($dir = opendir($bd->getpath($drive).$where)) 
		{
			while (false !== ($file = readdir($dir)))
			{
				if (!isset($cfg['dirignorelist'][$file])) 
					if (is_dir($bd->getpath($drive).$where.$file)) 
						$this->dirlist[] = array($file, array($drive));
			}
			closedir($dir);
		}
	}

	function readresvirtual($where, $drive)
	{
		$paths = array(); 

		if (strlen($where) == 0)
		{
			$sql = 'SELECT DISTINCT fpath FROM '.TBL_SEARCH.' WHERE LENGTH(fpath) > 0 AND drive = '.$drive;
		} else
		{
			$lenwhere = strlen($where) + 1;
			$sql = 'SELECT DISTINCT SUBSTR(fpath, '.$lenwhere.') FROM '.TBL_SEARCH.' WHERE drive = '.$drive.' AND SUBSTR(fpath, 1, '.strlen($where).') = "'.myescstr($where).'" AND LENGTH(fpath) > '.strlen($where);
		}
		
		$res = db_execquery($sql, true);

		while ($row = db_fetch_row($res))
		{
			$pathx = explode('/', $row[0]);
			$paths[$pathx[0]] = true;			
		}

		foreach($paths as $name => $val) $this->dirlist[] = array($name, array($drive));
	}

	function readroot()
	{
		global $bd;
		for ($i=0;$i<$bd->getcnt();$i++) if ($bd->accessok($i)) 
		{
			$this->drives[] = $i;
			$this->readres('', $i);
		}
	}

	function _finddir($crc, $cnt)
	{
		if (isset($this->dirlist[$cnt]) && crc32($this->dirlist[$cnt][0]) == $crc) return $this->dirlist[$cnt][0];
		for ($i=0,$c=count($this->dirlist);$i<$c;$i++) if (crc32($this->dirlist[$i][0]) == $crc) 
			return $this->dirlist[$i][0];
		if (isset($this->dirlist[$cnt])) return $this->dirlist[$cnt][0];
		return '';
	}

	function finddest($cnt, $crc)
	{
		global $bd;
		
		if (strlen($this->pwd) == 0) $this->readroot(); else $this->readres($this->pwd, $this->drive);
		
		$chdir = $this->_finddir($crc, $cnt);		

		if (strlen($chdir) > 0) $this->setpwd($this->pwd.$chdir.'/');		
	}

	function disksync()
	{
		global $bd;

		for ($i=0,$c=count($this->drives);$i<$c;$i++)
		{
			if (!$bd->isnetwork($this->drives[$i])) disksync($this->pwd, $this->drives[$i], false);
		}
	}

	function determine()
	{
		global $cfg, $bd;
		$this->dirlist = array();
		$this->drives = array();
		if (strlen($this->pwd) == 0)
		{
			$this->readroot();
		} else
		{
			if ($cfg['mergerootdir']) 
			{
				for ($i=0;$i<$bd->getcnt();$i++)
				{
					if ($bd->isdrive($i) && $bd->accessok($i))
					{
						if (okpath($this->pwd))
						{
							if (VIRTUALDIR || $bd->isnetwork($i) || is_dir($bd->getpath($i).$this->pwd))
							{
								$this->drives[] = $i;
								$this->readres($this->pwd, $i);
							}
						}
					}
				}
			} else
			{
				if ($bd->isdrive($this->drive) && $bd->accessok($this->drive) && okpath($this->pwd)) 
				{
					$this->drives[] = $this->drive;
					$this->readres($this->pwd, $this->drive);
				}
			}
		}

		if (count($this->drives) > 0) return true;
		return false;
	}

	function readdrive($dir, $drive)
	{
		global $bd;
		if ($bd->isdrive($this->drive) && $bd->accessok($this->drive) && okpath($this->pwd)) 
		{
			$this->drives[] = $this->drive;
			$this->readres($this->pwd, $this->drive);
			return true;
		}

		return false;
	}	
	
	function dprint($drive, $name, $cnt)
	{
		$cname = checkchs($name); 

		echo '<tr><td height="19" align="left"><input type="checkbox" name="dsel[]" value="'.urlencode($cnt.'_'.crc32($name).'_'.base64_encode($this->pwd).'_'.$drive).'"/> ';		
		echo '<a href="'.PHPSELF.'?n3='.urlencode($cnt.'_'.crc32($name)).'&amp;pwd='.webpdir($this->pwd).'&amp;d='.$drive.'" class="dir">';
		echo '<img alt="'.$cname.'" src="'.getimagelink('dir.gif').'" border="0"/>';
		echo strlen($cname) > db_guinfo('textcut') ? substr($cname, 0, db_guinfo('textcut')).' ..' : $cname; 
		echo '</a>';
		echo '</td></tr>';
	}	

	function merge()
	{
		$namelist = array();
		$dirlist = array();
		$cnt = 0;
		for ($i=0,$c=count($this->dirlist);$i<$c;$i++)
		{
			$name = $this->dirlist[$i][0];

			if (isset($namelist[$name]))
			{
				$id = $namelist[$name];
				foreach($this->dirlist[$i][1] as $did) $dirlist[$id][1][] = $did;
			} else 
			{
				$dirlist[$cnt] = array($name, $this->dirlist[$i][1]);
				$namelist[$name] = $cnt;
				$cnt++;
			}
		}

		$this->dirlist = $dirlist;
	}

	function dsort()
	{
		$namelist = array();
		for ($i=0,$c=count($this->dirlist);$i<$c;$i++) $namelist[$i] = $this->dirlist[$i][0];
		array_multisort($namelist, $this->dirlist, SORT_ASC);
	}

	function filesql($flds='id, free, track')
	{
		return fsearch($this->pwd, $this->drives, $flds, false);
	}

	function filesql_recur()
	{
		return fsearch($this->pwd, $this->drives,'id, free, track', true);
	}

	function showfiles($dcnt)
	{
		global $cfg, $setctl, $phpenv;
		
		if (DISKSYNC) $this->disksync();
		
		$res = $this->filesql('id, id3image, free, track, album, artist');

		$artist = $album = '';
		
		$idfimg = 0;
		$rows = $viewrows = array();
		while ($row = db_fetch_row($res)) 
		{
			$rows[] = $row;
			$fdesc = new filedesc($row[2]);		
			if ($fdesc->view) $viewrows[] = $row[0];
			if ($row[1] == 1 && $idfimg == 0) $idfimg = $row[0];
			
			if (strlen($row[4]) > 0 && strlen($row[5]) > 0)
			{
				$album = $row[4];
				$artist = $row[5];
			}
				
		}

		if (ALBUMCOVER && $dcnt <= $cfg['isalbumdircount'] && count($viewrows) > 0) 
		{
			$ci = new coverinterface();
			$ci->setartist($artist, $album);
			$ci->setfiles($rows);
			$imgurl = '';
			if ($ci->geturl($imgurl)) echo '<tr><td>'.$imgurl.'</td></tr><tr><td height="6"></td></tr>';
		} 

		for ($i=0,$c=count($viewrows);$i<$c;$i++) print_file($viewrows[$i],0,1);
		return $c;
	}

	function show()
	{
		global $cfg;

		$cols = db_guinfo('dircolumn');
		$c=count($this->dirlist);
		if ($c > 0)
		{
			if ($cols > 1)
			{		
				$colwidth = floor(100 / $cols);

				echo '<tr>';
				echo '<td>';
				echo '<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr>';
				$i = 0;
				while ($i < $c)
				{
					switch($cfg['columnsorttype'])
					{
						case 1:
							if ($i % $cols == 0 && $i > 0) echo '</tr><tr>';			
							echo '<td width="'.$colwidth.'%"><table width="100%" border="0" cellspacing="0" cellpadding="0" align="left">';
							$this->dprint($this->dirlist[$i][1][0], $this->dirlist[$i][0], $i);
							$i++;
							echo '</table></td>';
							break;
			
						case 2:
							for ($i2=0;$i2<$cols;$i2++)
							{
								$max = ceil($c / $cols);						
								echo '<td valign="top" width="'.$colwidth.'%"><table width="100%" border="0" cellspacing="0" cellpadding="0" align="left">';	
								for ($i3=0;$i3<$max;$i3++)
								{
									if ($i<$c) 
									{
										$this->dprint($this->dirlist[$i][1][0], $this->dirlist[$i][0], $i);
										$i++;

									} else echo '<tr><td></td></tr>';
								}
								echo '</table></td>';
							}							
							break;
					}					
					
				}
				echo '</tr></table></td></tr>';
			} else for ($i=0;$i<$c;$i++) $this->dprint($this->dirlist[$i][1][0], $this->dirlist[$i][0], $i);
		}
		return $c;	
	}
}


class file2
{	
	function file2($sid = -1, $id3 = false, $dbrow=false)
	{
		$this->fexists = false;
		$this->fullpath = '';
		$this->fsize = 0;
		$this->fname = '';
		$this->free = '';
		$this->drive = 0;
		$this->origrow = false;
		$this->investigated = false;
		$this->id3 = false;
		$this->relativepath = '';
		$this->dir64 = '';
		$this->fdate = 0;
		$this->hits = 0;
		$this->xid = 0;

		$this->fsize = 0;
		$this->dbrow = $dbrow;
		$this->sid = $sid;
		
		if ($sid != -1)
		{
			$this->investigate();
			if ($id3) $this->getid3();
		}
	}
	
	function netunique()
	{		
		return $this->origrow['md5'];
	}

	function investigate()
	{
		global $bd;
		if ($this->dbrow != false) $this->origrow = $this->dbrow; 
			else $this->origrow = get_searchrow($this->sid);
		
		if ($this->origrow != false)
		{
			$this->fexists = false;
			$this->drive = $this->origrow['drive'];
			$this->investigated = true;

			$this->relativepath = $this->origrow['fpath']; 
			$this->fsize = $this->origrow['fsize'];
			$this->fdate = $this->origrow['date'];
			$this->xid = $this->origrow['xid'];
			$this->free = $this->origrow['free'];

			if ($bd->isdrive($this->drive))
			{
				$this->fname = $this->origrow['fname'];				
				
				switch($bd->gtype($this->drive))
				{
					case 'l': 
								$this->fullpath = $bd->getpath($this->drive).$this->relativepath.$this->fname; 
								if (OPTIMISTICFILE) $this->fexists = true;
									else
								if (@file_exists($this->fullpath)) $this->fexists = true;
								break;
								
					case 'n': 
								$kp = new kpnetwork();
								if ($kp->setdrive($this->drive)) $this->fullpath = $kp->gensidurl($this); else $this->fullpath = '';
								$this->fexists = true;
								break;
				}
				
			}
		} else return false;
	}

	function gentitle($fields = array('track', 'artist', 'title', 'album'), $maxlength = 256)
	{
		$title = '';
		foreach ($fields as $name) if (isset($this->origrow[$name]) && !empty($this->origrow[$name])) checkcharadd($title, ' - ', $this->origrow[$name]);
		if (kp_strlen($title) == 0)
		{
			if (UTF8MODE) $title = $this->free; else $title = $this->fname;
		}
		$title = trim($title);
		if (kp_strlen($title) > $maxlength) return kp_substr($title, 0, $maxlength - 3).' ..';
		return $title;
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
		global $bd;
		return $bd->getpath($this->drive);
	}
	
	function getdir64($url=true)
	{
		if (empty($this->dir64)) $this->dir64 = webpdir($this->relativepath, $url);
		return $this->dir64;
	}

	function mkalink()
	{
		$fd = new filedesc($this->fname);
		if (WINDOWPLAYER && $fd->m3u)
		{
			$kpwjs = new kpwinjs();
			return 'href="" onclick="javascript: '.$kpwjs->single($this->sid).' return false;"';
		} else return 'href="'.$this->mklink().'"';
	}

	function mklink($action='sid', $fname='', $imgw=0, $imgh=0)
	{
		if ($action == 'sid' && strlen($fname) == 0) 
		{
			$fd = new filedesc($this->fname);
			if ($fd->m3u)
			{			
				$m3ug = new m3ugenerator();
				$fname = 'kplaylist.'.$m3ug->getextension();
			} else $fname = $this->fname;
		}
		return $this->weblink(0, 0, $action, true, '&amp;', $fname, $imgw, $imgh);
	}

	function weblink($sid=0, $fdate=0, $action='sid', $relative=true, $sand='&amp;', $fname='', $imgw=0, $imgh=0)
	{
		global $u_cookieid, $cfg, $phpenv, $setctl;
		if (!$sid) $sid = $this->sid;
		if (!$fdate) $fdate = $this->fdate;
		if (URLSECURITY) $urlextra = $sand.urlsecurity($fdate, $sid); else $urlextra = '';
		
		$url = '';
		
		if ($cfg['filepathurl'])
		{
			$url = $setctl->get('streamurl').$phpenv['location'].$cfg['filepathurlprepend'];
			$url .= '/'.$action.'_'.$sid.'/c_'.$u_cookieid;
			if ($imgw > 0) $url .= '/w_'.$imgw;
			if ($imgh > 0) $url .= '/h_'.$imgh;
			if (URLSECURITY) $url .= '/stag_'.urlsecurity($fdate, $sid, false, false);
			$url .= '/'.$fname;
		} else
		{	
			if ($relative)	$url = PHPSELF.'?'.$action.'='.$sid.$sand.'c='.$u_cookieid.$urlextra;
					else	$url = '?'.$action.'='.$sid.$sand.'c='.$u_cookieid.$urlextra;
		}
		return $url;
		
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
}


// very simple httpq class

class kphttpq
{
	function append($sid)
	{
		$f2 = new file2($sid, true);
		if ($f2->ifexists())
		{
			$fd = new filedesc($f2->fname);
			if ($fd->found && $fd->m3u)
			{
				$url = httpstreamheader2($fd->fid, $sid, $f2->fdate);
				$this->cmd('playfile', '&file='.urlencode($url));
			}
		}
	}

	function check()
	{
		$ret = $this->cmd('getversion');
		if (!empty($ret)) return true;
		return false;
	}
	
	function cmd($command, $arg='')
	{
		global $cfg;

		$fp = @fsockopen($cfg['httpq_parm']['server'], $cfg['httpq_parm']['port']);
  
		if($fp) 
		{
			$msg = 'GET /'.$command.'?p='.$cfg['httpq_parm']['pass'].$arg.' HTTP/1.0'."\r\n\r\n";

			fputs($fp, $msg);
			$ret = '';
			while(!feof($fp)) $ret .= fgets($fp,128);
			fclose($fp);
			return $ret;
		}
		return false;
	}
}


class networkinstance
{
	function networkinstance($nid=0, $enabled=0, $url='', $username='', $password='')
	{
		$this->nid = $nid;
		$this->enabled = $enabled;
		$this->url = $url;
		$this->username = $username;
		$this->password = $password;		
	}

	function getnid()
	{
		return $this->nid;
	}

	function getenabled()
	{
		return $this->enabled;
	}

	function setenabled($enabled)
	{
		$this->enabled = $enabled;
	}
	
	function geturl()
	{
		return $this->url;
	}

	function seturl($url)
	{
		$this->url = $url;
	}

	function getusername()
	{
		return $this->username;
	}

	function setusername($username)
	{
		$this->username = $username;
	}

	function getpassword()
	{
		return $this->password;
	}

	function setpassword($password)
	{
		$this->password = $password;
	}

	function sql()
	{
		return '`enabled` = "'.$this->enabled.'", url = "'.$this->url.'", username = "'.$this->username.'", `password` = "'.$this->password.'"';
	}
	
	function store()
	{
		$sql = 'INSERT INTO '.TBL_NETWORK.' SET '.$this->sql();
		$res = db_execquery($sql);
		if ($res) $this->nid = db_insert_id();
	}

	function update()
	{
		$sql = 'UPDATE '.TBL_NETWORK.' SET '.$this->sql().' WHERE nid = '.$this->nid;
		db_execquery($sql);

	}

	function remove()
	{
		$sql = 'DELETE FROM '.TBL_NETWORK.' WHERE nid = '.$this->nid;
		db_execquery($sql);
	}

	function valid()
	{
		if (strlen($this->url) == 0) return false;
		if (strlen($this->username) == 0) return false;
		if (strlen($this->password) == 0) return false;

		return true;
	}
}

class networkdb
{
	function getall()
	{
		$hosts = array();
		$sql = 'SELECT * FROM '.TBL_NETWORK;
		return $this->load($hosts, $sql);
	}

	function load(&$hosts, $sql)
	{
		$res = db_execquery($sql);
		while ($row = db_fetch_assoc($res)) $hosts[] = new networkinstance($row['nid'], $row['enabled'], $row['url'], $row['username'], $row['password']);
		return $hosts;
	}

	function getenabled()
	{
		$sql = 'SELECT * FROM '.TBL_NETWORK.' WHERE `enabled` = 1';
		return $this->load($hosts, $sql);
	}

	function getone($nid)
	{
		$hosts = array();
		$sql = 'SELECT * FROM '.TBL_NETWORK.' WHERE nid = '.$nid;
		$this->load($hosts, $sql);
		if (isset($hosts[0])) return $hosts[0];
		return false;
	}
}

class kpnetwork
{
	function kpnetwork()
	{
		$this->netdata = array();
		$this->predata = array();
		$this->drive = -1;
		$this->curlerr = 0;
		$this->chlistcnt = 0;
		$this->lasterrmsg = '';
		$this->xids = array();
		$this->strictssl = false;
		$this->netverkver = '3';
	}

	function getlist()
	{
		global $bd;
		$data = '';
		$res = db_execquery('SELECT id, ltime FROM '.TBL_SEARCH.' WHERE xid = 0 AND f_stat = 0'.$bd->genxdrive(), true);
		while ($row = db_fetch_row($res)) $data .= $row[0].'-'.$row[1]."\n";
		return $data;
	}

	function curlhost($url, $postdata)
	{
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		if (!$this->strictssl)
		{
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		}
		$data = curl_exec($ch);

		$this->curlerr = curl_errno($ch);
	
		if ($this->curlerr == 0) return $data; 
			else $this->lasterrmsg = curl_error($ch);
	}

	function genchlist($updateall=false, $deleteunused=false)
	{
		$xids = array();
		$xidsdel = array();
		
		$res = db_execquery('SELECT xid, ltime, id FROM '.TBL_SEARCH.' WHERE drive = '.$this->drive, true);
		while ($row = db_fetch_row($res)) 
		{
			$xid = $row[0];
			if (isset($xids[$xid])) $xidsdel[$xid] = $row[2]; 
				else $xids[$xid] = $row[1];
		}

		$this->chlist = array();

		for ($i=0,$c=count($this->predata);$i<$c;$i++)
		{
			$line = explode('-', $this->predata[$i]);
			$xid = $line[0];

			if (is_numeric($xid))
			{
				if (!$updateall)
				{
					if (!isset($xids[$xid]) || $xids[$xid] != $line[1]) $this->chlist[$xid] = true;
				} else $this->chlist[$xid] = true;
				if (isset($xids[$xid])) $xids[$xid] = -1;
			}
		}

		if ($deleteunused)
		{
			foreach($xids as $xid => $val)
			{
				if ($val != -1) 
				{
					db_execquery('DELETE FROM '.TBL_SEARCH.' WHERE xid = '.$xid.' AND drive = '.$this->drive);
				
				}
			}

			foreach($xidsdel as $xid => $id)
			{
				db_execquery('DELETE FROM '.TBL_SEARCH.' WHERE xid = '.$xid.' AND id = '.$id.' AND drive = '.$this->drive);
			}
		}
		
		$this->chlistcnt = count($this->chlist);
		return $this->chlistcnt;
	}

	function writeheader()
	{
		global $app_build;
		echo 'kPlaylist '.$app_build.'-';
		if (UTF8MODE) echo 'unicode'; else echo 'latin1';
		echo '-'.$this->netverkver;
		echo "\n";
	}

	function getdetailed($list)
	{
		global $bd;
		$result = db_execquery('SELECT * FROM '.TBL_SEARCH.' WHERE xid = 0'.$bd->genxdrive(), true);
		while ($row = db_fetch_assoc($result))
		{
			$id = $row['id'];
			if (isset($list[$id]))
			{
				echo $row['id'].'-'.$row['track'].'-'.$row['year'].'-'.base64_encode($row['title']).'-'.base64_encode($row['comment']).'-'.base64_encode($row['dirname']).'-';
				echo base64_encode($row['free']).'-'.base64_encode($row['fpath']).'-'.base64_encode($row['fname']).'-'.base64_encode($row['album']).'-';
				echo base64_encode($row['artist']).'-'.$row['md5'].'-'.$row['ltime'].'-'.$row['mtime'].'-'.$row['date'].'-'.$row['fsize'].'-'.$row['genre'].'-'.$row['bitrate'].'-'.$row['ratemode'].'-';
				echo $row['lengths'].'-'.$row['ftypeid'].'-'.$row['id3image'];
				echo "\n";

				/*

				0 = id
				1 = track
				2 = year
				3 = title
				4 = comment
				5 = dirname
				6 = free
				7 = fpath
				8 = fname
				9 = album
				10 = artist
				11 = md5
				12 = ltime
				13 = mtime
				14 = date
				15 = fsize
				16 = genre
				17 = bitrate
				18 = ratemode
				19 = lengths
				20 = ftypeid
				21 = id3image

				*/

			}
		}
	}

	function postlist($list)
	{
		$data = '';
		foreach ($list as $id => $val) $data .= $id.'_';
		return substr($data, 0, strlen($data) - 1);
	}

	function unpostlist($list)
	{
		$nlist = array();
		$datax = explode('_', $list);
		foreach($datax as $rid => $id) if (is_numeric($id)) $nlist[$id] = true;
		return $nlist;
	}

	function sync($list)
	{
		$data = $this->curlhost($this->genurl('detailedlist'), 'list='.$this->postlist($list));
	
		if ($this->curlerr == 0)
		{
			$datax = explode("\n", $data);		

			if (count($datax) > 0)
			{
				$header = explode('-', $datax[0]);
				if ($header[1] == 'unicode') $unicode = true; else $unicode = false;

				for ($i=1,$c=count($datax);$i<$c;$i++)
				{
					$line = explode('-', $datax[$i]);
					if (count($line) == 22)
					{
						$b64s = array(3, 4, 5, 6, 7, 8, 9, 10);
						foreach($b64s as $ids) $line[$ids] = base64_decode($line[$ids]);

						$charids = array(3, 4, 5, 6, 9, 10);

						if (!$unicode && UTF8MODE)
						{
							foreach($charids as $ids) $line[$ids] = iconv('', 'UTF-8//IGNORE', $line[$ids]);
						} 

						$xid = $line[0];
						$dirname = $line[5];
						$free = $line[6];
						$fpath = $line[7];
						$fname = $line[8];
						$md5 = $line[11];
						$ltime = $line[12];
						$mtime = $line[13];
						$date = $line[14];
						$fsize = $line[15];	

						$finf = gen_file_header($line[3], $line[10], $line[9], $line[17], $line[19], $line[16], $line[18], $line[1], $line[2], $line[4], $line[20], $line[21]);

						if (isset($this->xids[$xid])) 
						{
							$useid = $this->xids[$xid]; 
							$this->xids[$xid] = 0;
						} else $useid = 0;

						$filein = $fpath.$fname;
					
						$sql = search_qupdorins($useid, $finf, $filein, $md5, $this->drive, $mtime, 0, $fsize, $ltime, $xid);
			
						db_execquery($sql);					

					}				
				}				
			}
		}
	}

	function dosync()
	{
		$cnt = 0;
		$totcnt = 0;
		
		$this->xids = array();
		$res = db_execquery('SELECT xid, id FROM '.TBL_SEARCH.' WHERE drive = '.$this->drive, true);
		while ($row = db_fetch_row($res)) $this->xids[$row[0]] = $row[1];				
		
		foreach ($this->chlist as $id => $val)
		{
			$list[$id] = true;
			$cnt++;
			$totcnt++;

			if ($cnt > 512)
			{
				updateup_status(get_lang(349, $totcnt, $this->chlistcnt), 'up_status');
	
				$this->sync($list);
				$cnt = 0;
				$list = array();
			}
		}

		if ($cnt > 0) $this->sync($list);		
		
	}

	function setnetworkhost($networkhost)
	{
		$this->networkhost = $networkhost;
	}

	function getnetworkhost()
	{
		return $this->networkhost;
	}

	function setdrive($drive)
	{
		global $bd;

		if ($bd->gtype($drive) == 'n')
		{
			$this->drive = $drive;
			$this->networkhost = $bd->getpath($this->drive);

			return true;
		}
		return false;
	}
	
	function genurl($action, $extra='', $netid='')
	{
		$url = $this->networkhost->geturl();
		$packed = md5($this->networkhost->getusername()).md5($this->networkhost->getpassword()).$netid;
		$url .= '?network='.base64_encode($packed);
		$url .= '&netaction='.$action;
		$url .= $extra;
		return $url;
	}

	function gensidurl($f2, $seek=0)
	{
		$url = $this->genurl('download', '', $f2->netunique());
		$url .= '&id='.$f2->xid;
		if ($seek > 0) $url .= '&seekp='.$seek;
		return $url;
	}

	function geterrorstr()
	{
		return $this->lasterrmsg;
	}

	function checklogin()
	{
		$data = $this->curlhost($this->genurl('checklogin'), '');
		if ($this->curlerr == 0)
		{
			if ($data == 'OKNETLOGIN') 
				return true;
			else
			{
				$this->lasterrmsg = get_lang(307);
				return false;
			}
		}
	}

	function preparesync($drive)
	{
		global $bd;

		$this->netdata = $bd->getpath($this->drive);

		$data = $this->curlhost($this->genurl('prelist'), '');
		
		if ($this->curlerr == 0)
		{
			if (strlen($data) > 0)
			{
				$this->predata = explode("\n", $data);
				return count($this->predata) - 1;
			}
		} else return -1;
	}
}


class kpxspf
{
	function kpxspf()
	{
		$this->crlf = "\r\n";
		$this->data = '';
	}
	
	function xml_link($sid, $encode=false)
	{
		$f2 = new file2($sid, true);
		if ($f2->ifexists())
		{
			$fd = new filedesc($f2->fname);
			if ($fd->found && $fd->m3u)
			{
				$url = httpstreamheader3($fd->fid, $sid, $f2);
				if (!empty($url))
				{
					$this->data .= '<track>'.$this->crlf;
					$this->data .= '<location>'.$url.'</location>'.$this->crlf;
					$this->data .= '<creator>'.$f2->id3['artist'].'</creator>'.$this->crlf;
					$this->data .= '<album>'.$f2->id3['album'].'</album>'.$this->crlf;
					$this->data .= '<title>'.$f2->gentitle(array('title')).'</title>'.$this->crlf;
					$this->data .= '<annotation>'.$f2->gentitle(array('artist', 'title')).'</annotation>'.$this->crlf;
					$this->data .= '<duration>'.vernum($f2->id3['lengths']).'</duration>'.$this->crlf;
					
					$imgurl = '';

					$ci = new coverinterface();
					$ci->setartist($f2->id3['artist'], $f2->id3['album']);
					$ci->setlocation($f2->drive, $f2->relativepath);
					if ($ci->coverexists()) $ci->geturl($imgurl, true, true);

					$this->data .= '<image>'.$imgurl.'</image>'.$this->crlf;
					$this->data .= '<info></info>'.$this->crlf;
					$this->data .= '</track>'.$this->crlf;
				}
			}
		}

	}

	function getdata()
	{
		return $this->data;
	}

	function flashhtml()
	{
		global $setctl, $phpenv, $u_cookieid, $u_id, $cfg;

		kprintheader('');

		$playlist = $setctl->get('streamurl').$phpenv['streamlocation'];		
		$playlist .= '?templist='.$u_id.'&amp;c='.$u_cookieid.'&file='.lzero(getrand(1,999999),6).'.xml';

		$link = $cfg['xspf_url'].'?autoplay=1&amp;autoload=1&amp;playlist_url='.urlencode($playlist);

		?>
		<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0" width="400" height="200" id="xspf_player" align="middle">
		<param name="allowScriptAccess" value="sameDomain" />
		<param name="movie" value="<?php echo $link; ?>" />
		<param name="quality" value="high" />
		<param name="bgcolor" value="#e6e6e6" />
		<embed src="<?php echo $link; ?>" quality="high" bgcolor="#e6e6e6" width="400" height="200" name="xspf_player" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
		</object>
		<?php
		kprintend();
	}
}


class jwplayer
{
	function jwplayer()
	{
		$this->crlf = "\r\n";
		$this->data = '';
	}
	
	function xml_link($sid, $encode=false)
	{
		$f2 = new file2($sid, true);
		if ($f2->ifexists())
		{
			$fd = new filedesc($f2->fname);
			if ($fd->found && $fd->m3u)
			{
				$url = httpstreamheader3($fd->fid, $sid, $f2);
				if (strlen($url) > 0)
				{
					$title = $f2->gentitle(array('track', 'title'));
					$creator = $f2->id3['artist'];					
					
					$imgurl = '';
					
					$ci = new coverinterface();
					$ci->setartist($f2->id3['artist'], $f2->id3['album']);
					$ci->setlocation($f2->drive, $f2->relativepath);
					if ($ci->coverexists()) $ci->geturl($imgurl, true, true);					
					
					
					if ($encode)
					{
						$imgurl = urlencode($imgurl);
						$url = urlencode($url);
					}

					$this->data .= '<track>'.$this->crlf;
					$this->data .= '<title>'.$title.'</title>'.$this->crlf;
					$this->data .= '<creator>'.$creator.'</creator>'.$this->crlf;
					$this->data .= '<image>'.$imgurl.'</image>'.$this->crlf;
					$this->data .= '<duration>'.$f2->id3['lengths'].'</duration>'.$this->crlf;
					$this->data .= '<location>'.$url.'</location>'.$this->crlf;
					$this->data .= '</track>'.$this->crlf;
				}
			}
		}
	}
	
	function getdata()
	{
		return $this->data;
	}

	function flashhtml()
	{
		global $setctl, $phpenv, $u_cookieid, $u_id, $cfg;
		kprintheader('');
		$playlist = PHPSELF.'?templist='.$u_id.'&c='.$u_cookieid.'&file='.lzero(getrand(1,999999),6).'.xml';
		$link = $cfg['jw_urls']['swf'].'?file='.urlencode($playlist);
	
		$width = $cfg['jw_window_x'] - 20;
		$height = $cfg['jw_window_y'] - 10;
		$imgheight = round($cfg['jw_window_y'] / 2);

		?>
		<script type="text/javascript" src="<?php echo $cfg['jw_urls']['js']; ?>"></script>
		<p id="player1"><a href="http://www.macromedia.com/go/getflashplayer">Get the Flash Player</a> to see this player.</p>
		<script type="text/javascript">
		var s1 = new SWFObject('<?php echo $link; ?>','mpl',<?php echo $width; ?>,<?php echo $height; ?>,'8');
		  s1.addParam('allowscriptaccess','always');
		  s1.addParam('allowfullscreen','true');
		  s1.addVariable('height',<?php echo $height; ?>);
		  s1.addVariable('width',<?php echo $width; ?>);
		  s1.addVariable('displayheight',<?php echo $imgheight; ?>);
		  s1.addVariable('autostart','true');
		  s1.addVariable('repeat','list');
		  s1.addVariable('shuffle','false');
		  s1.addVariable("backcolor","0x9e9e9e");
		  s1.addVariable("frontcolor","0x333333");
		  s1.addVariable("lightcolor","0x555555");
		  s1.write('player1');
		</script>
		<?php
		kprintend();
	}

	function flashhtml_enqueue()
	{
		global $setctl, $phpenv, $u_cookieid, $u_id, $cfg;
		kprintheader('', 0, 'createPlayer();');

		
		$playlist = $setctl->get('streamurl').$phpenv['streamlocation'];
		$playlist .= '?templist='.$u_id.'&c='.$u_cookieid.'&file='.lzero(getrand(1,999999),6).'.xml';
		$playlist = urlencode($playlist);		
		
		$width = $cfg['jw_window_x'] - 20;
		$height = $cfg['jw_window_y'] - 20;
		$imgheight = round($height / 2);
		$link = $cfg['jw_urls']['swf'];

		?>
		<script type="text/javascript" src="<?php echo $cfg['jw_urls']['js']; ?>"></script>
		<script type="text/javascript">
		<!--
	
		var xmlhttp;
		var xmlDoc;

		function loadXMLDoc(theFile) 
		{
			xmlhttp=null;
			if (window.XMLHttpRequest) 
			{
				xmlhttp = new XMLHttpRequest(); 
			} else 
			if (window.ActiveXObject) 
			{ 
				xmlhttp = new ActiveXObject("Microsoft.XMLHTTP"); 
			}

			if (xmlhttp != null)
			{
				xmlhttp.onreadystatechange = loaded;
				xmlhttp.open("GET",theFile,true);
				xmlhttp.send(null);
			} 
			else 
			{
				alert('Sorry, your browser can\'t handle this script'); 
				return;
			}
		}

		function checkReadyState(obj) 
		{
			if(obj.readyState == 4) 
			{
				if(obj.status == 200) 
				{ 
					return true; 
				} else 
				{ 
					alert("Problem retrieving XML data"); 
				}
			}
		}

		function loaded() 
		{	
			if(checkReadyState(xmlhttp)) 
			{
				xmlDoc=xmlhttp.responseXML.documentElement;
				showTags('track');
			}
		}

		function showTags(theTag) 
		{
			function getTag(tag) 
			{
				var tmp='';
				xx=x[i].getElementsByTagName(tag);
				try 
				{ 
					tmp=xx[0].firstChild.data; 
				}
				catch(er) 
				{ 
					tmp=''; 
				}
				return(tmp);
			}

			var xx; var x; var txt;
			x = xmlDoc.getElementsByTagName(theTag);
			for (i=0; i<x.length; i++) 
			{
				addItem('mpl',{file: decodeURIComponent(getTag("location")), image: decodeURIComponent(getTag("image")), title: getTag("title"), author: getTag("creator")});
			}
		}

		function createPlayer() 
		{
			var s1 = new SWFObject('<?php echo $link; ?>','mpl','<?php echo $width; ?>','<?php echo $height; ?>','8');
			s1.addParam("allowfullscreen", "true");
			s1.addParam('allowscriptaccess','always');			
			s1.addVariable('height',<?php echo $height; ?>);
			s1.addVariable('width',<?php echo $width; ?>);
			s1.addVariable('displayheight',<?php echo $imgheight; ?>);			
			s1.addVariable("enablejs", "true");
			s1.addVariable("javascriptid", "kPlayer");
			s1.addVariable('searchbar','false');
			s1.addVariable('autostart','true');
			s1.addVariable('repeat','list');
			s1.addVariable('shuffle','false');
			s1.addVariable('file','<?php echo $playlist; ?>');
			s1.write('player1');
		}

		function thisMovie(swf) 
		{
			if(navigator.appName.indexOf("Microsoft") != -1)
			{
				return window[swf];
			} 
			else 
			{
				return document[swf];
			}
		}

		function loadFile(swf,obj) 
		{ 
			thisMovie(swf).loadFile(obj); 
		}
			
		function addItem(swf, obj, idx)
		{
			thisMovie(swf).addItem(obj, idx);
		}
		
		-->
		</script>

		<p id="player1"><a href="http://www.macromedia.com/go/getflashplayer">Get the Flash Player</a> to see this player.</p>

		<?php
		kprintend();
	}
}


class jwplayer6
{
	function jwplayer6()
	{
		$this->crlf = "\r\n";
		$this->data = '';

		$this->items = array();
	}
	
	function xml_link($sid, $encode=false)
	{
		$f2 = new file2($sid, true);
		if ($f2->ifexists())
		{
			$fd = new filedesc($f2->fname);
			if ($fd->found && $fd->m3u)
			{
				$url = httpstreamheader3($fd->fid, $sid, $f2, UTF8MODE);
				if (strlen($url) > 0)
				{
					$id3 = $f2->getid3();

					$title = '';					
					if (is_numeric($id3['track']) && $id3['track'] > 0) $title .= lzero($id3['track']).'. ';
					$title .= $f2->gentitle(array('title'));
							
					$imgurl = '';					
					$ci = new coverinterface();
					$ci->setartist($f2->id3['artist'], $f2->id3['album']);
					$ci->setlocation($f2->drive, $f2->relativepath);
					if ($ci->coverexists()) $ci->geturl($imgurl, true, true);				
					
					$this->items[] = array($imgurl, $url, $title, $fd->extension, $id3['length']);
				}
			}
		}
	}
	
	function getdata()
	{
		return $this->data;
	}

	function flashhtml()
	{
		global $setctl, $phpenv, $u_cookieid, $u_id, $cfg;
		kprintheader('');

		$result = db_execquery('SELECT sid FROM '.TBL_TEMPLIST.' WHERE uid = '.$u_id.' ORDER BY rid ASC');
		while ($row = db_fetch_row($result)) $this->xml_link($row[0], false);	
				
		$width = $cfg['jw_window_x'] - 20;
		$height = $cfg['jw_window_y'] - 10;
		$imgheight = round($cfg['jw_window_y'] / 2);

		$jsdata = '';

		for($i=0,$c=count($this->items);$i<$c;$i++)
		{
			$jsdata .= '{ ';
			if (strlen($this->items[$i][0]) > 0) $jsdata .= 'image: "'.$this->items[$i][0].'", ';
			$jsdata .= 'sources: [ { file: "'.$this->items[$i][1].'", type: "'.$this->items[$i][3].'" } ], title: "'.str_replace('"', '\"', $this->items[$i][2]).'", description: "'.$this->items[$i][4].'" }';
			if ($i + 1 < $c) $jsdata .= ',';
		}
		
		?>

		<script type="text/javascript" src="<?php echo $cfg['jw6_url']; ?>"></script>
		<div id="jw6id"><?php echo get_lang(253); ?></div>

		<script type="text/javascript">
		jwplayer("jw6id").setup({
			autostart: true,
			height: <?php echo $height; ?>,
			width: <?php echo $width; ?>,
			listbar: {
				position: 'bottom',
				size: 200
			},
			playlist: [
				<?php echo $jsdata; ?>
			]
		});
		</script>	
	
		<?php
		kprintend();
	}

	function flashhtml_enqueue()
	{	
	}
}


if (frm_isset('radionext') && frm_isset('pass')) 
{
	$stationid = frm_get('radionext', 1);
	$pass = frm_get('pass');

	if (is_numeric($stationid))
	{
		$kpr = new kpradio($stationid);
		if ($kpr->validpass($pass))
		{
			$id = $kpr->getnext();
			if ($id > 0)
			{
				$f2 = new file2($id);
				echo $f2->fullpath;
			}
		}
	}
	die();
}

if (NETWORKMODE && frm_isset('network') && frm_isset('netaction'))
{
	$uid = frm_get('network');
	$uidd = base64_decode($uid);
	
	if (strlen($uidd) > 0)
	{
		$user = substr($uidd, 0, 32);
		$md5pass = substr($uidd, 32, 32);
		$netid = substr($uidd, 64, 32);

		if (strlen($user) > 0 && strlen($md5pass) > 0)
		{
			$valuser = new kpuser();
			if ($valuser->loadbymd5($user, $md5pass))
			{
				if ($valuser->get('network'))
				{			
					$kpn = new kpnetwork();
						
					switch(frm_get('netaction'))
					{
						case 'checklogin':
							echo 'OKNETLOGIN';
						break;

						case 'download':
							$id = frm_get('id');

							if (is_numeric($id) && strlen($netid) > 0)
							{
								$f2 = new file2($id, true);
								if ($f2->ifexists())
								{
									if ($f2->netunique() == $netid)
									{
										$fp = fopen($f2->fullpath, "rb");
										if ($fp)
										{
											$seek = frm_get('seekp', 1);
											if (is_numeric($seek) && $seek < $f2->fsize) fseek($fp, $seek);
											fpassthru($fp);
											@fclose($fp);
										}
									}
								}
							}
						break;

						case 'prelist': 
							echo $kpn->getlist();	
						break;

						case 'detailedlist':
							if (frm_isset('list'))
							{
								echo $kpn->writeheader();
								$list = $kpn->unpostlist(frm_get('list'));
								$kpn->getdetailed($list);
							}
						break;								
					}
				}
			}	
		}
	}
	die();
}
		

if (frm_isset('path') && $cfg['filepathurl']) 
{
	$dirs = explode('/', frm_get('path'));
	
	foreach($dirs as $name)
	{
		$sepp = strpos($name, '_');
		if ($sepp !== false)
		{
			$id = substr($name, 0, $sepp);
			$val = substr($name, $sepp + 1);
			
			if (!isset($_GET[$id]) && strlen($id) > 0) $_GET[$id] = $val;
		}
	}
}

$a = '';

foreach($_GET as $name => $val) 
{
	switch($name)
	{
		case 'downloadfile':
		case 'sid':
		case 'streamsid':
		case 'streamplaylist':
		case 'imgsid':
		case 'imgid3sid':
		case 'templist':
		case 'seek_stream':
		case 'seek_stream_mobile':
			$a = $name;
		break;
	}
}

if (strlen($a) > 0)
{
	$stat = 0;
	if (frm_isset('c')) $acookie = frm_get('c'); else $acookie = '';
	
	if (authset())
	{
		$stat = db_verify_stream(authset(false), $phpenv['remote'], false);
		if ($stat) $acookie = authset(false);
	}
	if (frm_isset('c') && $stat == 0) $stat = db_verify_stream(frm_get('c'), $phpenv['remote'], true);

	if ($stat == 0)
	{
		if ($setctl->get('unauthorizedstreams'))
		{
			$valuser = new kpuser();
			$valuser->set('extm3u', $setctl->get('unauthorizedstreamsextm3u'));
			$valuser->set('plinline', 1);
			$valuser->set('pltype', 1);
			$valuser->set('lameperm', 0);
			$valuser->set('streamengine', 1);
			$valuser->set('forcelamerate', 0);
			$stat = 2;
		}
	} else $u_cookieid = $acookie;
		
	if ($stat == 1 || $stat == 2)
	{
		switch($a)
		{
			case 'imgsid':		
				if ($stat == 1 && chksecurity(frm_get('imgsid', 1)))
				{
					$ic = new imagecache(frm_get('imgsid', 1), frm_get('w', 1, 0), frm_get('h', 1, 0));
					$ic->getimage();
				}
			break;
			
			case 'imgid3sid':
				if ($stat == 1 && chksecurity(frm_get('imgid3sid', 1))) 
				{					
					$ic = new imagecache(frm_get('imgid3sid', 1), frm_get('w', 1, 0), frm_get('h', 1, 0), true);
					$ic->getimage();
				}
			break;

			case 'downloadfile':
				if ($stat == 1 && chksecurity(frm_get('downloadfile', 1))) Kplay_senduser2(frm_get('downloadfile', 1), 0, true, 1);
			break;

			case 'streamsid':
				if (chksecurity(frm_get('streamsid', 1))) Kplay_senduser2(frm_get('streamsid', 1), 0);
			break;
		
			case 'sid':
				if (chksecurity(frm_get('sid', 1))) playresource2(frm_get('sid', 1));
			break;
			
			case 'seek_stream':
				seek_stream(frm_get('seek_stream', 1));
			break;

			case 'seek_stream_mobile':
				seek_stream_mobile(frm_get('seek_stream_mobile', 1));
			break;

			case 'streamplaylist':
				if (frm_isset('extm3u')) $valuser->set('extm3u', 1);
				if (frm_ok('streamplaylist', 1))
				{
					$kp = new kp_playlist(frm_get('streamplaylist', 1));
					if ($kp->anyaccess()) $kp->play();
				}
			break;
			
			case 'templist':
				$kpx = new swfront();
				if (frm_isset('encode')) $encode = true; else $encode = false;
				if (frm_ok('templist', 1)) $kpx->genxmlfile(frm_get('templist'), $encode);
			break;
		}		
	}
	die();
}


if (USERSIGNUP && frm_isset('l_signup')) kp_signup();

if (frm_isset('update') && frm_isset('user')) search_updateautomatic(frm_get('user'), $phpenv['remote'], frm_get('update', 1, 0));

if (frm_isset('l_username') && frm_isset('l_password')) 
{
	$loginstatus = webauthenticate();
	switch($loginstatus)
	{
		case 0:
			if ($setctl->get('report_attempts')) syslog_write('User could not be validated (user: "'.frm_get('l_username').'")');
			klogon(get_lang(307));
			break;
		
		case 1:
			$uri = PHPSELF.'?checkcookie=true';
			if (frm_isset('l_uri') && !frm_empty('l_uri')) 
			{
				$ourl = urldecode(frm_get('l_uri'));
				$qpos = strrpos($ourl, '?');
				if ($qpos !== false && $cfg['accepturi']) 
				{
					$addchkc = strpos($ourl, 'checkcookie=true');
					if ($addchkc === false) $addurl = '&checkcookie=true'; else $addurl = '';					
					$uri = PHPSELF.stripslashes(strip_tags(substr($ourl, $qpos))).$addurl;	
				}
			}
			refreshurl($uri);
			die();
			break;
		
		case 2:
			klogon(get_lang(345));
			break;
	}	
} 

if (authset() || $cfg['disablelogin'])
{	
	if (db_verify_stream(authset(false), $phpenv['remote'], false))
	{
		if (REQUIRE_HTTPS && !$phpenv['https']) klogon();

		if (authset()) $u_cookieid = authset(false); else $u_cookieid = null;
		
		$deflanguage = db_guinfo('lang');

		if (frm_ok('sel_playlist', 1)) user_saveoption('defplaylist', frm_get('sel_playlist', 1));

		if (frm_ok('viewmode', 2)) user_saveoption('detailview', frm_get('viewmode', 2));

		if (frm_ok('stationid', 1)) user_saveoption('defstationid', frm_get('stationid', 1));

		if (frm_ok('sel_shplaylist', 1)) 
		{
			user_saveoption('defshplaylist', frm_get('sel_shplaylist', 1));
		}

		switch($valuser->get('pltype'))
		{
			case 6:
			case 7:
			case 8:
			case 9:
				define('WINDOWPLAYER', true);
			break;

			default:
				define('WINDOWPLAYER', false);
			break;
		}

		if ($valuser->get('theme') != 0)
		{
			$kpt->load($valuser->get('theme'));
		}
	
		$list = false;

		if (frm_isset('streamrss'))
		{
			$ca = new caction();
			$ca->updatelist();
			$ca->createrss(true);
		} else
		if (frm_isset('whatsnewrss'))
		{
			$gl = new genlist();
			$gl->whats_new(0, 30);
			$gl->outrss();			
		} else	
		if (frm_isset('action'))
		{
			$action = frm_get('action');
			$match = true;
			
			switch ($action)
			{
				case 'deactivatenetwork':
					if ($valuser->isadmin())
					{
						$nid = frm_get('nid', 1);
						if (is_numeric($nid))
						{
							$ndc = new networkdb();
							$host = $ndc->getone($nid);
							if ($host)
							{
								$host->setenabled(0);
								$host->update();
								$ndc = new networkdb();
								$hosts = $ndc->getenabled();
								if (count($hosts) == 0) $setctl->set('activenetworkhosts', 0);
							}
						}
						refreshurl(PHPSELF.'?action=settingsview&page=4');
					}						
				break;
				
				case 'activatenetwork':
					if ($valuser->isadmin())
					{		
						if (frm_ok('nid', 1))
						{
							$ndc = new networkdb();
							$host = $ndc->getone(frm_get('nid', 1));
							if ($host)
							{
								$kpn = new kpnetwork();
								$kpn->setnetworkhost($host);
								if ($kpn->checklogin())
								{
									$setctl->set('activenetworkhosts', 1);
									$host->setenabled(1);
									$host->update();
									okmessage(get_lang(181), true);										
								} else errormessage($kpn->geterrorstr(), false);
							}
						}
					}
				break;
				
				case 'addnetwork':
					if ($valuser->isadmin())
					{		
						$host = new networkinstance();
						if ($host) edit_network($host);
					}				
				break;

				case 'editnetwork':
					if ($valuser->isadmin())
					{		
						if (frm_ok('nid', 1))
						{
							$ndc = new networkdb();
							$host = $ndc->getone(frm_get('nid', 1));
							if ($host) edit_network($host);
						}
					}				
				break;
				
				case 'storenetwork':
					if ($valuser->isadmin())
					{		
						$ndc = new networkdb();							
						$nid = frm_get('nid', 1);
							
						if ($nid > 0) $host = $ndc->getone($nid); else $host = new networkinstance();
						if ($host)
						{
							$host->seturl(frm_get('url'));
							$host->setusername(frm_get('username'));
							$host->setpassword(frm_get('password'));
						}

						if ($host->valid())
						{
							if ($nid > 0) 
							{
								$host->update(); 
								$msg = get_lang(358);
							} else 
							{
								$host->store();
								$msg = get_lang(357);
							}
						} else $msg = get_lang(284);
						edit_network($host, true, $msg);
					}
				break;

				case 'deletenetwork':
					if ($valuser->isadmin())
					{
						$nid = frm_get('nid', 1);
						if (is_numeric($nid))
						{
							$ndc = new networkdb();
							$host = $ndc->getone($nid);
							if ($host) $host->remove();
						}
						settings_edit(1, 4);
					}
				break;
				
				case 'bulletin':
					if (BULLETIN && class_exists('kbulletin'))
					{
						$kpd = new kpdesign(false);
						$kpd->top(false, get_lang(268));
						$kb = new kbulletin();
						$kb->showall();
						$kpd->bottom();
					}
				break;

				case 'frontpage':
					$kpd = new kpdesign(false);
					$kpd->top(false, get_lang(268));
					$kpfp = new kpfrontpage();
					$kpfp->show();
					$kpd->bottom();
				break;
				
				case 'newbulletin':
					$kb = new kbulletin();
					$kb->editbulletin(0);
				break;

				case 'sendshout':
					if (SHOUTBOX)
					{
						if (frm_isset('shoutmessage'))
						{
							$msg = strip_tags(trim(frm_get('shoutmessage')));
							if (!UTF8MODE && function_exists('iconv')) $msg = iconv('UTF-8', get_lang(1), $msg);
							if (strlen($msg) <= 128 && strlen($msg) > 0)
							{
								$msgsp = explode(' ', $msg);
								$ratio = strlen($msg) / count($msgsp);									
			
								if ($ratio <= 20) 
								{
									$kpshout = new kpshoutmessage();
									$kpshout->submit($u_id, $msg);
								}
							}
						}
					}
				break;

				case 'ajaxshoutmessages':
					header('Content-type: text/html;charset='.get_lang(1));
					if (SHOUTBOX)
					{
						$kpshout = new kpshoutmessage();
						echo $kpshout->getmessagesByAjax();
					}
				break;
				
				case 'ajaxstreams':
					header('Content-type: text/html;charset='.get_lang(1));
					$ca = new caction();
					$ca->updatelist();
					echo $ca->getStreamByAjax();
				break;
					
				case 'delbulletin':
					if (frm_ok('bid', 1))
					{
						$kpd = new kpdesign();
						$kpd->top(false, get_lang(268));
						$kb = new kbulletin();
						$kb->delbulletin(frm_get('bid', 1), $u_id);
						$kb->showall();
						$kpd->bottom();
					}
				break;

				case 'editbulletin':
					if (frm_ok('bid', 1))
					{
						$kb = new kbulletin();
						$kb->editbulletin(frm_get('bid', 1));
					}
				break;
				
				case 'dropadmin':
					if ($valuser->isadmin())
					{
						chsessionstatus($u_cookieid, 2);
						$uri = '';
						if (frm_isset('p')) $uri = '?pwd='.frm_get('p');
						if (frm_ok('d', 1) && strlen($uri) > 0) $uri .= '&d='.frm_get('d', 1);	
						refreshurl(PHPSELF.$uri);
					}
				break;
				
				case 'savebulletin':
					$kb = new kbulletin();
					if (frm_isset('publish') && $valuser->isadmin()) $publish = 1; else $publish = 0;
	
					if (frm_isset('mesg')) $mesg = frm_get('mesg'); else $mesg = '';
					if (frm_isset('bid')) $bid = frm_get('bid', 1); else $bid = 0;

					$bid = $kb->savebulletin($bid, $publish, $mesg);
					$kb = new kbulletin();
					$kb->editbulletin($bid, true);
				break;

				case 'sendmail':
					if (class_exists('mailmp3'))
					{
						$mail3 = new mailmp3();
						if (frm_ok('id', 1)) $mail3->setsid(frm_get('id', 1));
						$mail3->decide();
					}
				break;

				case 'playlistupload':
					$pu = new playlistupload();
					$pu->decide();
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

				case 'radio_save':
					$stationid = frm_get('stationid', 1);
					if ($valuser->isadmin() && $cfg['radio'] && is_numeric($stationid))
					{
						$kpr = new kpradio();
						$kpr->load($stationid);
	
						if (frm_isset('save'))
						{
							$kpr->fromPost();
							if ($kpr->isok())
								if ($stationid != 0) $kpr->update(); else $kpr->store();
							$kpr->edit();
						} else
						if (frm_isset('deleteradio'))
						{
							$kpr->remove();
							?>
							<script type="text/javascript">
							<!--
								window.close(); 
								window.opener.location.reload();
								//-->
							</script>
							<?php
						}							
					}
				break;

				case 'radio_new':
				case 'radio_edit':
					if ($valuser->isadmin() && $cfg['radio'] && frm_ok('stationid', 1))
					{
						$kpr = new kpradio();
						$kpr->load(frm_get('stationid', 1));
						$kpr->edit();							
					}
				break;

				case 'radio_editjs':
					?>
					<script type="text/javascript">
					<!--
						location = '<?php echo PHPSELF; ?>?action=radio_edit&stationid='+opener.document.misc.stationid.value;
					//-->
					</script>
					<?php
				break;

				case 'playlist_newsave':
					$name = frm_get('name');
					if (strlen($name) > 0)
					{
						$public = frm_get('public', 1, 0);
						
						$kppl = new kp_playlist();
						if ($kppl->createnew($u_id, $name, $public)) $added = true; else
								$added = false;
							
						kprintheader(get_lang(61));
						echo '<font color="#000000" class="notice">';
						if ($added) echo get_lang(35); else echo get_lang(137);
						echo '</font><br/><br/>';
						echo '<a href="javascript:void(0);" onclick="javascript: window.close(); window.opener.location.reload();"><font color="blue">'.get_lang(27).'</font></a>';
						if ($added) echo '<font class="notice"> - '.get_lang(36).'</font>';
						kprintend();
					} else playlist_new();
				break;

				case 'admineditoptions':
					if ($valuser->isadmin() && frm_ok('id', 1)) show_useroptions(true, frm_get('id', 1));
				break;
				
				case 'editoptions':
					if (db_guinfo('u_access') != 2) show_useroptions(false, $u_id);
				break;				
				
				case 'randomizer':
					$rz = new kprandomizer();
					$rz->fromArray($_POST);
				break;

				case 'showrandomizer':
					if (class_exists('kprandomizer'))
					{
						$rz = new kprandomizer();
						$rz->view();
					}
				break;
						
				case 'updateoptions':
					if ($valuser->isadmin()) search_updatelist_options();
				break;

				case 'settingsview':
					if ($valuser->isadmin())
					{
						$page = frm_get('page', 1, 0);
						$reload = frm_get('reload', 2, 0);
						settings_edit($reload, $page);
					}
				break;
				
				case 'savesettings':
					if ($valuser->isadmin())
					{
						$page = frm_get('page', 1, 0);
						settings_save($_POST, $page);
						settings_edit(1, $page);
					}
				break;
				
				case 'performupdate':
					if ($valuser->isadmin()) 
					{
						if (frm_isset('followsymlinks')) $setctl->set('followsymlinks', 1); else $setctl->set('followsymlinks', 0);
						if (frm_isset('updusecache')) $setctl->set('updusecache', 1); else $setctl->set('updusecache', 0);
						search_updatelist($_POST);
					}						
				break;

				case 'id3edit':
					if ($valuser->isadmin() && $cfg['id3editor'] && function_exists('file_id3editor'))
					{
						if (frm_ok('id3sid', 1))
						{
							$f2 = new file2(frm_get('id3sid', 1));
							if ($f2->ifexists()) file_id3editor($f2->fullpath);
						}
					}
				break;

				case 'id3save':
					if ($valuser->isadmin() && $cfg['id3editor'] && function_exists('file_id3editor_save'))
					{
						$file = frm_get('file');
						file_id3editor_save(stripcslashes(base64_decode($file)), $_POST);
						file_id3editor(stripcslashes(base64_decode($file)));
					} 
				break;
				
				case 'showusers':
					if ($valuser->isadmin()) show_users();			
				break;

				case 'userdel':
					if ($valuser->isadmin() && frm_ok('id', 1))
					{
						db_execquery('DELETE FROM '.TBL_USERS.' WHERE u_id = '.frm_get('id', 1));
						show_users();
					}
				break;
				
				case 'usersave': 
					if ($valuser->isadmin())
					{
						if (frm_isset('submit'))
						{
							save_user();
						} else show_users();
					}
				break;

				case 'newusertemplate':
					if ($valuser->isadmin() && frm_ok('id', 1))
					{
						$id = frm_get('id', 1);
						$kpu = new kpuser();
						if ($kpu->load($id)) 
						{
							$kpu->id = -1;
							$kpu->set('u_login', '');
							$kpu->set('utemplate', 0);
							show_userform($kpu, '', 0, $id);
						}
					}
				break;
				
				case 'useraction':
					if ($valuser->isadmin())
					{				
						$kpu = new kpuser();
						$kpu->set('u_access', 1);						

						if (frm_isset('newuser')) show_userform($kpu);
						else
						if (frm_isset('newtemplate'))
						{
							$kpu->set('utemplate', 1);
							show_userform($kpu);
						} else
						if (frm_isset('refresh')) show_users();
					}
				break;

				case 'useredit':
					if ($valuser->isadmin())
					{
						if (frm_ok('id', 1))
						{
							$id = frm_get('id', 1);
							$kpu = new kpuser();
							if ($kpu->load($id)) show_userform($kpu);
						}
					}
				break;
				
				case 'userlogout':
					if ($valuser->isadmin())
					{							
						if (frm_ok('id', 1))
						{
							$id = frm_get('id', 1);
							if ($id != $u_id) adminlogout($id);
							show_users();
						}
					}
				break;

				case 'userhistory':
					if ($valuser->isadmin()) 
					{	
						kprintheader(get_lang(121));														
						if (frm_isset('searchnavigate_right') || frm_isset('searchnavigate_left')) 
						{
							$nv = new navi(7);
							if (frm_isset('searchnavigate_right')) $nv->searchnavi(1); else $nv->searchnavi(0);
						} else
						{
							$uh = new userhistory();
							if (frm_ok('id', 1))
							{
								$uh->setuid(frm_get('id', 1));
								$uh->setfilter(frm_get('cfilter', 1, -1));
								$uh->setperpage(frm_get('chperpage', 1, 18));
								$uh->show();
								$nv = new navi(7, $uh->rows, true);
								$nv->setperpage($uh->perpage);
								$nv->setfollow('huid', $uh->uid);
								$nv->setfollow('filter', $uh->filter);
								$nv->setfollow('hperpage', $uh->perpage);
								$nv->writenavi();
								$uh->endshow();
							}
						}
						kprintend();
					}
				break;
					
				case 'useractivate':
					if ($valuser->isadmin() && frm_ok('id', 1)) db_execquery('UPDATE '.TBL_USERS.' SET u_status = 0 WHERE u_id = '.frm_get('id', 1));
					show_users();
				break;

				case 'saveadminuseroptions':	
					if ($valuser->isadmin())
					{
						if (!frm_isset('cancel'))
						{
							$id = frm_get('id', 1);
						
							if (frm_ok('id', 1))
							{
								$id = frm_get('id', 1);
								save_useroptions($id);
								show_useroptions(true, $id, get_lang(358), true);
							}
						} else show_users(); 
					}
				break;

				case 'saveuseroptions':
					if (db_guinfo('u_access') != 2)
					{
						$state = save_useroptions($u_id);
						switch ($state)
						{
							case 2: show_useroptions(false, $u_id, get_lang(157), true); break;
							case 3: show_useroptions(false, $u_id, get_lang(165), true); break;
							default: show_useroptions(false, $u_id, get_lang(358),true); break;
						}
					}
				break;

				case 'deletefiletype':
					if ($valuser->isadmin() && frm_ok('del', 1))
					{
						db_execquery('DELETE from '.TBL_FILETYPES.' WHERE id = '.frm_get('del', 1));
						settings_edit(1, 3);
					}
				break;

				case 'findmusic':
					if ($valuser->isadmin()) findmusic();
				break;

				case 'editfiletype':
					if ($valuser->isadmin() && frm_ok('id', 1)) edit_filetype(frm_get('id', 1));
				break;		

				case 'storefiletype':
					if ($valuser->isadmin())
					{
						if (frm_isset('extension')) $extension = frm_get('extension'); else $extension = '';
						if (frm_isset('m3u')) $m3u = 1; else $m3u = 0;
						if (frm_isset('search')) $search = 1; else $search = 0;
						if (frm_isset('logaccess')) $logaccess = 1; else $logaccess = 0;
						if (frm_ok('id', 1))
						{
							$id = store_filetype(frm_get('id', 1), $m3u, $search, $logaccess, frm_get('mime'), $extension);	
							if (frm_get('id', 1) > 0) $msg = get_lang(358); else $msg = get_lang(357);
							edit_filetype(vernum($id), true, $msg);
						}
					}
				break;

				case 'search':
					if (frm_isset('orsearch')) $valuser->set('orsearch', 1); else $valuser->set('orsearch', 0);
					if (frm_isset('onlyid3')) $valuser->set('defaultid3', 1); else $valuser->set('defaultid3', 0);

					if (frm_ok('hitsas', 2)) $valuser->set('hitsas', frm_get('hitsas', 2));
					if (frm_ok('searchwh', 1)) $valuser->set('defaultsearch', frm_get('searchwh', 1));
					$valuser->update();

					$kps = new kpsearch();
					if (frm_isset('searchtext') && strlen(frm_get('searchtext')) > 0 && is_array($kps->getwords($kps->what)))
					{							
						$kps->gensearchsql();
						$kpd = new kpdesign();
						$kpd->top(true, get_lang(5));							
						$kps->viewsearch();							
						$nv = new navi(2, $kps->rows, true);
						$nv->writenavi();
						$kps->endsearch();
						$kpd->bottom();
					} else $match = false;
				break;

				case 'playlist':
					if (frm_isset('editplaylist') || frm_isset('viewplaylist'))
					{
						if (frm_ok('sel_playlist', 1))
						{
							playlist_editor(frm_get('sel_playlist', 1), frm_get('previous'));
						} else
						if (frm_ok('sel_shplaylist', 1))
						{
							playlist_editor(frm_get('sel_shplaylist', 1), frm_get('previous'));
						}
					} else
					if (frm_isset('playplaylist'))
					{
						if (frm_ok('sel_playlist', 1))
						{
							$kp = new kp_playlist(frm_get('sel_playlist', 1));
							if ($kp->anyaccess()) $kp->play();
						} else
						if (frm_ok('sel_shplaylist', 1))
						{
							$kp = new kp_playlist(frm_get('sel_shplaylist', 1));
							if ($kp->anyaccess()) $kp->play();
						}
					}
				break;

				case 'playlisteditor':
					if (frm_ok('sel_playlist', 1))
					{
						$pl_id = frm_get('sel_playlist', 1);
						$subaction = '';
						$loadeditor = true;
		
						$kppl = new kp_playlist($pl_id);
						if ($kppl->isloaded())
						{
							foreach($_POST as $name => $value)
							{
								switch($name)
								{
									case 'refresh':
									case 'saveseq': 
									case 'saveplaylist': 
									case 'sortplaylist':
									case 'playplaylist':
									case 'deleteplaylist':
									case 'playselected':
									case 'delselected':	
									case 'saveradiosequence': $subaction = $name; break;
								}
								if (substr($name, 0, 10) == 'singledel_') $subaction = 'singledel';
							}

							switch($subaction)
							{
								case 'saveseq':	
									if ($kppl->writeaccess()) $kppl->savesequence(frm_get('seq', 3)); 
								break;

								case 'saveplaylist':
									$name = frm_get('playlistname');
									if (frm_isset('shuffle')) $kppl->setstatus(1); else $kppl->setstatus(0);
									if (frm_ok('public', 1)) $kppl->setpublic(frm_get('public', 1));
									if (strlen($name) > 0) $kppl->setname($name);
									if ($kppl->soleaccess()) $kppl->update();
								break;
									
								case 'sortplaylist':
									if ($kppl->writeaccess())
									{
										switch(frm_get('sort', 1))
										{					
											case 0: $kppl->sortalphabetic(); break;
											case 1: $kppl->sortrandom(); break;
											case 2: $kppl->sortoriginal(); break;
											case 3: $kppl->removeduplicates(); break;
										}
									}
								break;
									
								case 'playplaylist':
									if ($kppl->anyaccess()) $kppl->play();
									$loadeditor = false;
								break;
									
								case 'deleteplaylist':
									if ($kppl->soleaccess()) $kppl->remove();
									refreshurl(PHPSELF.'?d='.frm_get('drive', 1).'&pwd='.frm_get('previous'));
									$loadeditor = false;
								break;
									
								case 'playselected':
									if ($kppl->anyaccess())
									{
										$m3ug = new m3ugenerator();
										$sel = frm_get('selected', 3);
										for ($i=0,$c=count($sel);$i<$c;$i++)
										{
											$id = $sel[$i];
											if (is_numeric($id))
											{
												$res = db_execquery('SELECT sid FROM '.TBL_PLAYLIST_LIST.' WHERE id = '.$id);
												$row = db_fetch_assoc($res);
												$m3ug->sendlink2($row['sid']);					
											}
										}
										$m3ug->start();
									}
									$loadeditor = false;
								break;
									
								case 'delselected':
									if ($kppl->writeaccess())
									{
										$sel = frm_get('selected', 3);
										if (count($sel) > 0)
										{
											for ($i=0,$c=count($sel);$i<$c;$i++)
											{
												$id = $sel[$i];
												if (is_numeric($id)) $kppl->removeentry($id);		
											}
											$kppl->rewriteseq();
										}
									}
								break;

								case 'saveradiosequence':
									if ($kppl->writeaccess())
									{
										foreach($_POST as $name => $val)
										{
											if (substr($name, 0, 13) == 'nextradioseq_')
											{
												$id = substr($name, 13);
												if (is_numeric($id))
												{
													$nextseq = (int) $val;
													$kpr = new kpradio($id);
													if ($kpr->isloaded()) 
													{
														$kpr->setnextseq($nextseq);
														$kpr->update();
													}
												}
											}
										}
									}
								break;

								case 'singledel':
									if ($kppl->writeaccess())
									{
										foreach($_POST as $name => $val)
										{
											if (substr($name, 0, 10) == 'singledel_')
											{
												$id = substr($name, 10);
												if (is_numeric($id)) $kppl->removeentry($id);
												$kppl->rewriteseq();
											}
										}
									}
								break;
							} // subaction
						} // loaded
							
						if ($loadeditor) playlist_editor($pl_id, frm_get('previous'), frm_get('sort', 1));

					} // playlist
				break;
				
				case 'misc':
					if (frm_isset('whatshot')) genliststart(4);	
						else
					if (frm_isset('whatsnew')) genliststart(3);
						else
					if (!frm_empty('genrelist'))
					{
						if (frm_ok('genreno', 1)) user_saveoption('defgenre', frm_get('genreno', 1));
						genliststart(6);
					} else
					if (frm_isset('logmeout'))
					{ 
						$valuser->logout($u_cookieid);
						$deflanguage = $setctl->get('default_language');
						klogon(); 
					} 
				break;
				
				case 'hotselect':
					if (frm_isset('artist')) hotselect(frm_get('artist'));	
				break;

				case 'playalbum':
					if (frm_isset('p'))
					{							
						if (frm_ok('ftid', 1))
						{
							$f2 = new file2(frm_get('ftid', 1), true);
							$ft = $f2->id3['album'];
						} else $ft = '';
													
						$kpdir = new kpdir();
						$kpdir->setpwd(base64_decode(frm_get('p'))); 
						$kpdir->setdrive(frm_get('d', 1));
							
						if ($kpdir->determine())
						{
							$res = $kpdir->filesql('id,album');

							if ($res)
							{
								$m3ug = new m3ugenerator();
								while ($row = db_fetch_assoc($res))
								{
									if (strlen($ft) > 0 && $ft != $row['album']) continue;
									$m3ug->sendlink2($row['id']);
								}
								$m3ug->start();
							}
						}
					}
				break;

				case 'downloadarchive':
					if (frm_isset('mime') && frm_isset('fileid'))
					{
						$fileok = false;
							
						if (!frm_isset('filename') || frm_empty('filename')) $filename = 'kpdl'.date('hi'); 
							else $filename = frm_get('filename');
						
						$fileid = frm_get('fileid', 1);
						if (is_numeric($fileid))
						{
							$kpa = new kparchiver();
							$file = $kpa->getarchivefile($fileid);
						
							if (strlen($file) > 0 && file_exists($file))
							{
								$kpa = new kparchiver();
								$kpa->download($file, frm_get('mime'), $filename);
								@unlink($file);
								$fileok = true;
							}
						}
							
						if (!$fileok)
						{
							kprintheader(get_lang(260));
							echo '<form action="'.PHPSELF.'" method="get">';
							echo '<font class="notice">'.get_lang(261).'</font><br/><br/>';
							echo '<input type="button" value="'.get_lang(27).'" name="close" class="fatbuttom" onclick="javascript: window.close();"/>'; 
							echo '</form>';
							kprintend();
						}
					}
				break;
				
				case 'playwinlist':
					if (frm_ok('plid', 1))		
					{
						$ids = array();
							
						$kp = new kp_playlist(frm_get('plid', 1));
						if ($kp->anyaccess())
						{
							$res = $kp->getres();
							if ($res) while ($row = db_fetch_assoc($res)) $ids[] = $row['sid'];
							$kpx = new swfront();
							$kpx->write($ids);
							$kpx->flashhtml();
						}
					}
				break;
								
				case 'playwinfile':
					if (frm_ok('id', 1))
					{
						$ids = array();
						$ids[] = frm_get('id', 1);
						$kpx = new swfront();
						$kpx->write($ids);
						$kpx->flashhtml();
					}
				break;
				
				case 'playwin':
					if (frm_isset('p') && frm_ok('d', 1))
					{
						$ids = array();
						$kpdir = new kpdir();
						$kpdir->setpwd(base64_decode(frm_get('p')));
						$kpdir->setdrive(frm_get('d', 1));
						if ($kpdir->determine())
						{
							$res = $kpdir->filesql();
							if ($res) while ($row = db_fetch_assoc($res)) $ids[] = $row['id'];
						}

						$kpx = new swfront();
						$kpx->write($ids);
						$kpx->flashhtml();
					}
				break;

				case 'loadjw':
					$kpx = new swfront();
					$kpx->flashhtml_enqueue();
				break;

				case 'diraddtemplist':						
					if (frm_isset('p') && frm_isset('d'))
					{
						$ids = array();
						$kpdir = new kpdir();
						$kpdir->setpwd(base64_decode(frm_get('p')));
						$kpdir->setdrive(frm_get('d', 1));
						if ($kpdir->determine())
						{
							$res = $kpdir->filesql();
							if ($res) while ($row = db_fetch_assoc($res)) $ids[] = $row['id'];
						}

						$kpx = new swfront();
						$kpx->write($ids);
					}
				break;			

				case 'playlistaddtemplist':
					if (frm_ok('id', 1))
					{
						$ids = array();
						$kp = new kp_playlist(frm_get('id', 1));
						if ($kp->anyaccess())
						{
							$res = $kp->getres();
							while ($row = db_fetch_assoc($res)) $ids[] = $row['sid'];
							$kpx = new swfront();
							$kpx->write($ids);
						}
					}
				break;				
				
				case 'addplaylistajax':
					if (frm_isset('selids') && frm_ok('plid', 1))
					{							
						$kppl = new kp_playlist(frm_get('plid', 1));
						if ($kppl->appendaccess())
						{
							$fl = explode(';', frm_get('selids'));
							$ids = retrievesids($fl);
							$kppl->addtoplaylist($ids);
						}
					}
				break;
				
				case 'addtemplist':
					if (frm_isset('selids'))
					{							
						$fl = explode(';', frm_get('selids'));
						$ids = retrievesids($fl);
						$kpx = new swfront();
						$kpx->write($ids);	
					}
				break;
				
				case 'playselected':
					if (frm_isset('filestoarc'))
					{
						$fl = explode(';', frm_get('filestoarc'));
						$ids = retrievesids($fl);
						$kpx = new swfront();
						$kpx->write($ids);
						$kpx->flashhtml();
					}
				break;
				
				case 'dlall':
					if (ALLOWDOWNLOAD && $cfg['archivemode'] && db_guinfo('u_allowdownload') && db_guinfo('allowarchive') && frm_isset('p') && frm_ok('d', 1))
					{
						$kpdir = new kpdir();
						$kpdir->setpwd(base64_decode(frm_get('p')));
						$kpdir->setdrive(frm_get('d', 1));
						if ($kpdir->determine())
						{
							$res = $kpdir->filesql();
							if ($res)
							{
								$kpa = new kparchiver();
								while ($row = db_fetch_assoc($res)) $kpa->setfile($row['id']);
								$kpa->execute();
							}
						}
					}
				break;

				case 'dlplaylist':
					if (ALLOWDOWNLOAD && $cfg['archivemode'] && db_guinfo('u_allowdownload') && db_guinfo('allowarchive') && frm_ok('pid', 1))
					{
						$kpa = new kparchiver();
						$kp = new kp_playlist(frm_get('pid', 1));
						if ($kp->anyaccess())
						{
							$res = $kp->getres();							
							while ($row = db_fetch_assoc($res)) $kpa->setfile($row['sid']);
							$kpa->execute();
						}
					}
				break;

				case 'dlselected':
					if (frm_isset('filestoarc') && ALLOWDOWNLOAD && db_guinfo('u_allowdownload') && $cfg['archivemode'] && db_guinfo('allowarchive'))
					{
						$kpa = new kparchiver();
						$fl = explode(';', frm_get('filestoarc'));
						$ids = retrievesids($fl);
						for ($i=0,$c=count($ids);$i<$c;$i++) $kpa->setfile($ids[$i]);
						$kpa->execute();
					}
				break;

				case 'randomizerselected':
					kprintheader(get_lang(260));
					?>
					<form name="arcfiles" action="<?php echo PHPSELF; ?>" method="post">
					<input type="hidden" name="action" value="playselected"/>
					<input type="hidden" name="filestoarc" value=""/>
					<script type="text/javascript">
					<!--

					var selobj = opener.document.getElementById('selids'); 
					for (i=0; i<selobj.options.length;i++) 
					{
						if (selobj.options[i].selected) 
							document.arcfiles.filestoarc.value = document.arcfiles.filestoarc.value + selobj.options[i].value + ';';	
					}
					document.arcfiles.submit();
					
					//-->
					</script>
					</form>
					<?php
					kprintend();
				break;
						

				case 'playselectedjs':
				case 'dlselectedjs':
					kprintheader(get_lang(260));
	
					switch($action)
					{
						case 'dlselectedjs': $newaction = 'dlselected'; break;
						case 'playselectedjs': $newaction = 'playselected'; break;
					}
					
					?>
					<form name="arcfiles" action="<?php echo PHPSELF; ?>" method="post">
					<input type="hidden" name="action" value="<?php echo $newaction; ?>"/>
					<input type="hidden" name="filestoarc" value=""/>
					<script type="text/javascript">
					<!--
					for(var i=0;i<opener.document.psongs.elements.length;i++) 
						if(opener.document.psongs.elements[i].type == "checkbox") 
							if (opener.document.psongs.elements[i].checked == true) 
						document.arcfiles.filestoarc.value = document.arcfiles.filestoarc.value + opener.document.psongs.elements[i].value + ';';
					document.arcfiles.submit();
					//-->
					</script>
					</form>
					<?php
					kprintend();
				break;

				case 'gotopage':
					$page = frm_get('page', 1);
					if (is_numeric($page))
					{						
						$nv = new navi();
						if ($nv->gui) 
						{
							$kpd = new kpdesign();
							$kpd->top(true, $nv->header);
						} else kprintheader(get_lang(121));	
						
						$nv->searchnavi(2, $page - 1);
						if ($nv->gui) $kpd->bottom(); else kprintend();
					}
				break;
				
				case 'listedres':
					if (frm_isset('searchnavigate_right') || frm_isset('searchnavigate_left') || frm_isset('chlistoption'))
					{
						$nv = new navi(2);
						$kpd = new kpdesign();
						$kpd->top(true, $nv->header);
						if (frm_isset('searchnavigate_right')) $nv->searchnavi(1); 
							else
						if (frm_isset('searchnavigate_left')) $nv->searchnavi(0);
							else $nv->searchnavi(2);
						$kpd->bottom();
					} else
					if (frm_isset('hotoptions'))
					{							
						if (frm_ok('hotperiod', 1))
						{
							$filter = frm_get('hotperiod', 1);
							user_saveoption('hotmode', $filter);
						} else $filter = 0;
						genliststart(4);
					} else
					if (frm_isset('editplaylist') || frm_isset('viewplaylist'))
					{
						playlist_editor(frm_get('sel_playlist', 1), frm_get('previous'));
					} else
					if (frm_isset('addplaylist'))
					{
						$ok = false;
						kprintheader(get_lang(61), 1);

						$sids = getsidspost();
						if (count($sids) > 0)
						{
							if (frm_ok('sel_playlist', 1)) 
							{
								$kppl = new kp_playlist(frm_get('sel_playlist', 1));
								if ($kppl->appendaccess()) 
								{
									$ok = true;
									$kppl->addtoplaylist($sids);
								}
							}
						}

						if ($ok) echo '<font color="#000000" class="notice">'.get_lang(33).'&nbsp;&nbsp;</font>';
							else
								echo '<font color="#000000" class="notice">'.get_lang(32).'&nbsp;&nbsp;</font>';
						

						echo '<a href="javascript:history.go(-1)" class="fatbuttom">&nbsp;'.get_lang(34).'&nbsp;</a>';
						kprintend();
					} else
					if (frm_isset('playplaylist'))
					{
						if (frm_ok('sel_playlist', 1))
						{
							$kp = new kp_playlist(frm_get('sel_playlist', 1));
							if (!$kp->anyaccess() || !$kp->play()) errormessage(get_lang(302), true);
						}
					} else
					if (frm_isset('httpqselected'))
					{
						if ($cfg['httpq_support'])
						{
							$sids = getsidspost();
							$httpq = new kphttpq();
							kprintheader(get_lang(61), 1);

							if ($httpq->check())
							{
								for ($i=0,$c=count($sids);$i<$c;$i++) $httpq->append($sids[$i]);									
								echo '<font color="#000000" class="notice">'.get_lang(33).'&nbsp;&nbsp;</font>';
							} else 
							{
								echo '<font color="#000000" class="notice">'.get_lang(333, $cfg['httpq_parm']['server']).'&nbsp;&nbsp;</font>';
							}
							echo '<a href="javascript:history.go(-1)" class="fatbuttom">&nbsp;'.get_lang(34).'&nbsp;</a>';
							kprintend();					
						}
					} else
					if (frm_isset('psongsselected'))
					{	
						$m3ug = new m3ugenerator();
						$sids = getsidspost();											
						for ($i=0,$c=count($sids);$i<$c;$i++) $m3ug->sendlink2($sids[$i]);
						$m3ug->start();
					} else
					if (frm_isset('psongsall'))
					{
						if (frm_isset('previous'))
						{						
							$kpdir = new kpdir();
							$kpdir->setpwd(base64_decode(frm_get('previous'))); 
							$kpdir->setdrive($runinit['drive']);
							if ($kpdir->determine())
							{
								$res = $kpdir->filesql();

								if ($res)
								{
									$m3ug = new m3ugenerator();
									while ($row = db_fetch_assoc($res)) $m3ug->sendlink2($row['id']);
									$m3ug->start();
								}
							}
						}
					}
				break;
				
				default:
					$match = false;
				break;	
			
			}

			if (!$match) $list = true;

		} else $list = true;
		
		if ($list) kplaylist_filelist(frm_get('pwd', 0), frm_get('d', 1), frm_get('n3'));
		
	} else
	{
		klogon();
	}
} else 
if (frm_isset('checkcookie'))
{
	if (headers_sent()) klogon(get_lang(345)); else klogon(get_lang(237));
} else 
if (frm_isset('streamrss'))
{
	if ($setctl->get('publicrssfeed'))
	{
		$ca = new caction();
		$ca->updatelist();
		if ($setctl->get('unauthorizedstreams')) $ca->createrss(true); else $ca->createrss(false);
	}
} else
if (frm_isset('whatsnewrss'))
{
	if ($setctl->get('publicrssfeed'))
	{
		$gl = new genlist();
		$gl->whats_new(0, 20);
		$gl->outrss();
	}
} else
klogon();

?>
