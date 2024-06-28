<?php
//kPlaylist 1.7 Build 426 (20-05-06_03.06)

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
	automatic installation (MySQL) and automatic search engine update. 

Are you a PHP programmer? 
	Would you like to join us in the creation of this product? Before you start 
	changing the code please send a mail to us and tell us that you want to help us. 
	We'll send you some information on how you can  send us upgrade information and 
	how to get the latest up2date source. We got a development source available.

Translate or errors in the grammar?
	Please submit new languages, or grammar fixes directly to us for immediate
	new builds. Se http://www.kplaylist.net/addlang/ for more information.

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


// try to set the execution time to 86400 sec = 1 day. 
@ini_set('max_execution_time', 86400);
@ini_set('register_globals', 'Off');
@ini_set('display_errors', 'Off');

// if you for any reason want to reset the configuration, set this variable to true, reload the page and set it back to false.
$resetconfiguration = false;

$db = array(
	'host' => 'localhost', # MySql server
	'name' => 'kplaylist', # Database name
	'user' => 'kplaylist', # MySql user
	'pass' => 'kplaylist', # MySql password
	'prepend' => 'tbl_'    # To prepend before the table names
);


// what to prepend before the table names, don't change this after installing! Do it before.
$cfg['dbprepend'] = $db['prepend'];

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

// where the getid3.php file exists
$cfg['getid3include'] = 'getid3/getid3.php';

//how many titles of one album do we need to treat as a album? Turn to zero to show all.
$cfg['titlesperalbum'] = 0;

// demo mode on/off. Default off.
$cfg['demomode'] = 0; 

// for multiple downloads.
$cfg['archivemode'] = false;

$cfg['archivefilelist_cr'] = "\n";

// turn this on to show commands when creating INSTEAD of executing 
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
$cfg['dateformat'] = 'd.m.y H:i';

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

// lame command 
$cfg['lamecmd'] = '/usr/local/bin/lame --silent --nores --nohist --mp3input -h -m s -b %bitrate% "%file%" -';

$lamebitrates = array(0, 32, 40, 48, 56, 64, 80, 96, 112, 128, 160, 192, 224, 256, 320);

//	enabled	(0/1)	name	cmd	(%D = destination file,	%F source OR %LIST if using filelist.)
// YOU MUST SUIT THESE ARCHIVERS TO YOUR OWN NEED. DO NOT USE THE DEFAULT BLINDLY.
$archivers = array(
	0 => array(1,	'zip', '/usr/bin/zip -j -0 %D "%F"', 'application/zip'),
	1 => array(1,	'rar', 'C:\Programfiler\WinRAR\rar.exe -m0 a %D "%F"', 'application/x-rar'),
	2 => array(0,	'rar2', 'C:\Programfiler\WinRAR\rar.exe -m0 a %D @"%LIST"', 'application/x-rar')
);

// Not much to see at yet. (id, name and css style) - not finished - more will come. 
$themes = array(
	0 => array('menu right', 0),
	1 => array('menu left', 0)
);

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

// map design to files instead of inbuilt ('' means inbuilt). set one or each to a relevant filename to customize. 
$cfg['designmap'] = array('login' => '', 'infobox' => '', 'endmp3table' => '', 'top' => '', 'bottom' => '', 'blackbox' => '');

// how many last stream titles to show
$cfg['laststreamscount'] = 5;

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

// end of configuration
if (file_exists('kpconfig.php')) include('kpconfig.php');


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

function frpost($name, $numeric=false, $numvalue=0)
{
	if (isset($_POST[$name])) return $_POST[$name];
	if ($numeric) return $numvalue; else return '';
}

function fruser($name, $numeric=false, $numvalue=0)
{
	if (isset($_POST[$name])) return $_POST[$name]; 
	if (isset($_GET[$name])) return $_GET[$name]; 
	if ($numeric) return $numvalue; else return '';
}

function fruserset($name)
{
	if (isset($_POST[$name]) || isset($_GET[$name])) return true;
	return false;
}

function fruserempty($name)
{
	$data = fruser($name);
	if (empty($data)) true; 
	return false;
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
	$msg = 'Client '.$phpenv['remote'].' '.$phpenv['useragent'].' '.$msg;
	if (!$win32)
	{
		define_syslog_variables();
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

function genselect($name, $options, $default=0, $class='fatbuttom')
{
	$out = '<select name="'.$name.'" class="'.$class.'">';
	for ($i=0,$c=count($options);$i<$c;$i++)
	{
		$out .= '<option value="'.$options[$i][0].'"';
		if ($options[$i][0] == $default) $out .= ' selected="selected"';
		$out .= '>'.$options[$i][1].'</option>';
	}
	$out .= '</select>';
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
		$out = str_replace($key, $rep, $in);
	return stripslashes($out);
}

function checkcharadd(&$string, $chars, $add)
{
	if (strlen($string) > 0)
	{
		$test = substr($string, strlen($string) - strlen($chars));
		if ($test == $chars) $string .= $add; else $string .= $chars.$add;
	} else $string = $add;
}

function getimagelink($image)
{
	global $PHP_SELF, $setctl;
	if (!empty($setctl->keys['externimagespath'])) return $setctl->get('externimagespath').$image; else return $PHP_SELF.'?image='.$image;
}

function gethtml($page)
{
	global $kdesign, $cfg;

	if (isset($cfg['designmap'][$page])) $f = $cfg['designmap'][$page]; else $f = '';

	if (!empty($f))
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

function jswinscroll($name, $url, $height=320, $width=675, $withj=true, $func='newwinscroll', $urlprep='P')
{
	return jswin($name, $url, $height, $width, $withj, $func, $urlprep);
}

function trspace($height)
{
	echo '<tr><td height="'.$height.'"></td></tr>';
}

function jswin($name, $url, $height=320, $width=675, $withj=true, $func='newwin', $urlprep='P')
{
	global $PHP_SELF;
	if ($urlprep == 'P') $urlprep = $PHP_SELF; else $urlprep = '';
	if ($withj) $js = "javascript: ".$func."('".$name."', '".$urlprep.$url."', ".$height.", ".$width.");";
		else $js = $func."('".$name."', '".$urlprep.$url."', ".$height.", ".$width.");";
	return $js;
}


$kdesign = array();

$kdesign['login'] = '
?>
<form style="margin:0;padding:0" method="post" action="<?php if (HTTPS_REQ_MET) echo $PHP_SELF;?>">
<input type="hidden" name="uri" value="<?php if (isset($_POST[\'uri\'])) echo $_POST[\'uri\']; else echo urlencode($phpenv[\'uri\']); ?>"/>
<p>&nbsp;</p>
<table width="600" border="0" cellspacing="0" cellpadding="0" align="center">
	<tr>
		<td colspan="3"><img src="<?php echo getimagelink(\'login.jpg\'); ?>" height="327" width="600" alt="kPlaylist v<?php echo $app_ver; ?> build <?php echo $app_build; ?>"/></td>
	</tr>
	<tr>
		<td height="4"/>
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
					<td width="30%"><input type="text" id="user" name="user" tabindex="1" maxlength="30" size="15" class="logonbuttom"/></td>
					<td width="48%"></td>
				</tr>
				<tr>
					<td height="3"></td>
				</tr>
				<tr>
					<td></td>
					<td><font class="text"><?php echo get_lang(38); ?></font></td>
					<td>
						<input type="password" name="password" tabindex="2" maxlength="30" size="15" class="logonbuttom"/>
					</td>
				</tr>
				<tr>
					<td height="3"></td>
				</tr>
				<tr>
					<td></td>
					<td><font class="text"><?php echo get_lang(287); ?></font></td>
					<td><input type="checkbox" name="rememberme" tabindex="4" value="1" class="logonbuttom"/></td>
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
							?><input type="submit" name="submit" tabindex="3" value="<?php echo get_lang(40); ?>" class="logonbuttom" />
							<?php
							if (USERSIGNUP) 
							{ 
								?><input type="button" name="Signup" tabindex="5" onclick="newwin(\'Users\', \'<?php echo $PHP_SELF; ?>?signup=1\', 195, 350);" value="<?php echo get_lang(158); ?>" class="logonbuttom" /><?php 
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
	d = document.getElementById(\'user\');	
	d.focus();	
	-->
</script>
<table width="610" border="0" cellspacing="0" cellpadding="0" align="center">
<tr><td height="9"></td></tr>
<tr>
	<td align="right">
		<a href="http://validator.w3.org/check/referer">
		<img src="<?php echo getimagelink(\'w3c_xhtml_valid.gif\'); ?>" border="0" alt="Valid XHTML 1.0!" height="31" width="88"/></a>
	</td>
</tr>
<tr>
	<td align="right"><a href="http://www.kplaylist.net/"><font class="loginkplaylist">www.kplaylist.net</font>&nbsp;</a></td>
</tr>
</table>';

$kdesign['infobox'] = '	
	$trheight = 14;
	$boxwidth = 245;
	?>	
	<table width="100%" cellspacing="0" cellpadding="0" border="0">
	<tr>
		<td valign="top" align="left">		
		<?php if ($setctl->get(\'showkeyteq\')) 
		{
			?><span class="notice"><?php echo \'<a href="http://keyteq.no" target="_blank">\'.substr(get_lang(77),0,3).\'</a>\'.substr(get_lang(77),3); ?></span><?php
		}
		if ($setctl->get(\'showupgrade\')) 
		{
			?><a title="<?php echo get_lang(120); ?>" href="http://www.kplaylist.net/?ver=<?php echo $app_ver; ?>&amp;build=<?php echo $app_build; ?>" target="_blank">
			<font color="#CCCCCC"><?php echo get_lang(78); ?></font></a><br/><?php
		} else if ($setctl->get(\'showkeyteq\')) echo \'<br/>\'; ?>
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
						<form style="margin:0;padding:0" name="search" action="<?php echo $PHP_SELF; ?>" method="post">
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
							<td align="left"><input type="text" name="searchfor" id="searchfor" value=\'<?php echo htmlentities(sanstr(\'searchfor\'), ENT_QUOTES); ?>\' maxlength="150" size="46" class="fatbuttom"/></td>	
						</tr>
						<tr>
							<td height="5"></td>
						</tr>
						<tr>
							<td align="left">
								<input type="radio" name="searchwh" value="0" <?php if (db_guinfo(\'defaultsearch\')==\'0\') echo \'checked="checked"\';?>/><font class="notice"><?php echo get_lang(81); ?>&nbsp;</font>
								<input type="radio" name="searchwh" value="1" <?php if (db_guinfo(\'defaultsearch\')==\'1\') echo \'checked="checked"\';?>/><font class="notice"><?php echo get_lang(82); ?>&nbsp;</font>
								<input type="radio" name="searchwh" value="2" <?php if (db_guinfo(\'defaultsearch\')==\'2\') echo \'checked="checked"\';?>/><font class="notice"><?php echo get_lang(83); ?>&nbsp;</font>
								<input type="radio" name="searchwh" value="3" <?php if (db_guinfo(\'defaultsearch\')==\'3\') echo \'checked="checked"\';?>/><font class="notice"><?php echo get_lang(67); ?></font>
							</td>		
						</tr>
						<tr>
							<td height="5"></td>
						</tr>
						<tr>
							<td align="left">
								<input type="checkbox" name="onlyid3" value="1" <?php if (db_guinfo(\'defaultid3\')) echo \' checked="checked"\'; ?>/>
								<font class="notice"><?php echo get_lang(80); ?></font>
								<input type="checkbox" name="orsearch" value="1" <?php if (db_guinfo(\'orsearch\')) echo \' checked="checked"\'; ?>/>
								<font class="notice"><?php echo get_lang(306); ?></font>&nbsp;
								<select name="hitsas" class="fatbuttom">
								<option value="0"<?php if (db_guinfo(\'hitsas\') == 0) echo \'selected="selected"\'; ?>><?php echo get_lang(185); ?></option>
								<option value="1"<?php if (db_guinfo(\'hitsas\') == 1) echo \'selected="selected"\'; ?>><?php echo get_lang(186); ?></option>
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
								d = document.getElementById(\'searchfor\');
								d.focus();
								-->
							</script>
							<?php blackbox(get_lang(84), album_hotlist(\'artist\'), 0, true, \'boxhotlist\', \'left\', $boxwidth); ?>
							</td>
						</tr>
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
						trspace($trheight);
						?>
						<tr>
							<td><?php 
									blackbox(get_lang(286), $ca->show(), 0, false, \'box\', \'left\', $boxwidth); ?>
								</td>
						</tr>		
						</table>
						</form>
					</td>
				</tr>
				<?php
	
				$ploutput = sharedplaylists();
				if (!empty($ploutput))
				{
					trspace($trheight);
					?>
					<tr>
					<td>
					<form style="margin:0;padding:0" name="sharedplaylist" action="<?php echo $PHP_SELF?>" method="post">
					<table width="100%" border="0" cellspacing="0" cellpadding="0">
					<tr><td><?php echo blackbox(get_lang(86), $ploutput, 0, false, \'box\', \'left\', $boxwidth); ?></td></tr>
					</table>
					</form>
					</td>
					</tr>
					<?php 
				}
				?>

				<tr>
				<td>
				<form style="margin:0;padding:0" name="misc" action="<?php echo $PHP_SELF?>" method="post">
				<input type="hidden" name="action" value="misc"/>
				<table width="100%" border="0" cellspacing="0" cellpadding="0">
				<?php					
					if (db_guinfo(\'u_access\') == 0)
					{
						trspace($trheight);
						?>
						<tr>
							<td align="left">
						<?php
						$admincode = \'&nbsp;<input type="button" name="action" value="\'.get_lang(87).\'" class="fatbuttom" onclick="\'.jswinscroll(\'Users\', \'?action=showusers\',360,685).\'"/> \';			
						$admincode .= \'<input type="button" name="updatesearch" value="\'.get_lang(15).\'" class="fatbuttom" onclick="\'.jswinscroll(\'Update\', \'?action=updateoptions\').\'"/> \';
						$admincode .= \'<input type="button" name="settings" value="\'.get_lang(126).\'" class="fatbuttom" onclick="\'.jswin(\'Settings\',\'?action=settingsview\',460,685).\'"/>\';
						
						$dropadmin = \'<a class="bbox" onclick="javascript: if (!confirm(\'.addsq().get_lang(313).addsq().\')) return false;" href="\'.$PHP_SELF.\'?action=dropadmin&amp;p=\'.$runinit[\'pdir64\'].\'&amp;d=\'.$runinit[\'drive\'].\'">x</a>&nbsp;\';

						
						
	
						echo blackbox(get_lang(88),$admincode, 0, false, \'box\', \'left\', $boxwidth, $dropadmin); ?>
						</td></tr>
					<?php 
					} 
					
					$othercode = \'&nbsp;<input type="submit" name="whatsnew" value="\'.get_lang(89).\'" class="fatbuttom"/>&nbsp;\';
					$othercode .= \'<input type="submit" name="whatshot" value="\'.get_lang(90).\'" class="fatbuttom"/>&nbsp;\';

					$usermisc = \'&nbsp;<input type="submit" name="logmeout" value="\'.get_lang(91).\'" onclick="javascript: if (!confirm(\'.addsq().get_lang(210).addsq().\')) return false;" class="fatbuttom"/> \';
					$usermisc .= \'<input type="button" name="editoptions" value="\'.get_lang(92).\'" class="fatbuttom" \'. \'onclick="\'.jswin(\'Options\', \'?action=editoptions\',360,590).\'"/> \';
					$usermisc .= \'<input type="button" name="randomizer" value="\'.get_lang(212).\'" class="fatbuttom" \'. \'onclick="\'.jswin(\'Randomizer\', \'?action=showrandomizer\',380,550).\'"/>\';

					trspace($trheight);

					?>
					<tr><td><?php echo blackbox(get_lang(93), $othercode, 0, false, \'box\', \'left\', $boxwidth); ?></td></tr>

					<?php trspace($trheight); ?>
					
					<?php

					$genres = \'&nbsp;\'.genre_select(true,db_guinfo(\'defgenre\'));
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

$kdesign['endmp3table'] = '	$text = $crstr_dl = $crstr = \'\';

	if ($showalbum && $files > 0)
	{
		$crstr .= \'<input type="submit" name="psongsall" value="\'; 
		if ($files == 1 && $dirs == 0) $text = get_lang(65); else
		if ($files > 0 && $dirs == 0) $text = get_lang(66); else
		if ($files > 0 && $dirs > 0) $text = get_lang(67);
		$crstr .= $text.\'" class="fatbuttom"/>&nbsp;&nbsp;\';
		$crstr_dl = \'<input type="button" name="pdlall" value="\'.$text.\'" onclick="\'.jswin(\'dlall\', \'?action=dlall&amp;p=\'.$runinit[\'pdir64\'].\'&amp;d=\'.$runinit[\'drive\'], 130, 450).\'" class="fatbuttom"/>&nbsp;&nbsp;\';
	} 
	
	if ($files > 0) $crstr .= \'<input type="submit" onclick="javascript: if (!anyselected()) { alert(\'.addsq().get_lang(159).addsq().\'); return false; }" name="psongsselected" value="\'.get_lang(68).\'" class="fatbuttom"/>\';

	if ($dirs > 0 && $recursive) $crstr .= \'&nbsp;<input type="submit" name="pdirsall" value="\'.get_lang(275).\'" class="fatbuttom"/>&nbsp;\';
	
	$crstr_dl .= \'<input type="button" onclick="javascript: if (!anyselected()) alert(\'.addsq().get_lang(159).addsq().\'); else \'.jswin(\'dlselected\', \'?action=dlselectedjs\', 130, 450, false).\'" name="pdlselected" value="\'.get_lang(68).\'" class="fatbuttom"/>\';

	$playlists = db_getplaylist($u_id);
	$ploutput = \'\';
	if (count($playlists)>0)
	{
		if ($files > 0) $ploutput .= \'<input type="submit" name="addplaylist" onclick="javascript: if (!anyselected()) { alert(\'.addsq().get_lang(32).addsq().\'); return false; }" value="\'.get_lang(69).\'" class="fatbuttom"/>&nbsp;\';
		$ploutput .= \'<select name="sel_playlist" class="file">\';
		
		$playid = db_guinfo("defplaylist");
		for ($c=0,$cnt=count($playlists);$c<$cnt;$c++) 
		{		
			if ($playlists[$c][1] == $playid) $sel=\' selected="selected" \'; else $sel=\'\';
			$ploutput .= \'<option value="\'. $playlists[$c][1].\'"\'.$sel.\'>\'.$playlists[$c][0].\'</option>\';
		}
		$ploutput .= \'</select>&nbsp;\';
	}
	$ploutput .= \'<input type="hidden" name="drive" value="\'.$runinit[\'drive\'].\'"/>\';
	if (count($playlists)>0)
	{
		$ploutput .= \'<input type="submit" name="playplaylist" value="\'.get_lang(70).\'" class="fatbuttom"/>&nbsp;\';
		$ploutput .= \'<input type="submit" name="editplaylist" value="\'.get_lang(71).\'" class="fatbuttom"/>&nbsp;\';
	}
	
	$upload = \'<input type="button" name="upload" onclick="\'.jswin(\'upload\', \'?action=fupload\', 220, 520).\'" value="\'.get_lang(69).\'" class="fatbuttom"/>\';

	$ploutput .= \'<input type="button" name="newplaylist" onclick="\'.jswin(\'playlist\', \'?action=playlist_new\', 100, 350).\'" value="\'.get_lang(72).\'" class="fatbuttom"/>\';

	$selectallcode=\'<input type="button" value="+" class="fatbuttom" onclick="javascript: selectall();"/>&nbsp;&nbsp;<input type="button" value="-" class="fatbuttom" onclick="javascript: disselectall();"/>&nbsp;&nbsp;<input type="button" value="-+" class="fatbuttom" onclick="javascript: toggle();"/>\';
	
	?>
	<tr><td height="8"></td></tr>
	<tr>
	<td>
	<table border="0" cellspacing="0" cellpadding="0">	
		<tr>
		<?php
		
		if ($files > 0) echo \'<td align="left">\'.blackbox(get_lang(73), $selectallcode).\'</td><td width="5"></td>\';
		if (!empty($crstr)) echo \'<td align="left"> \'.blackbox(get_lang(74), $crstr).\'</td><td width="5"></td>\';
		if (ALLOWDOWNLOAD && db_guinfo(\'u_allowdownload\') && $cfg[\'archivemode\'] && db_guinfo(\'allowarchive\') && $files > 0) echo \'<td align="left"> \'.blackbox(get_lang(117), $crstr_dl).\'</td><td width="5"></td>\';

		echo \'<td align="left">\'.blackbox(get_lang(75), $ploutput).\'</td><td width="5"></td>\';
		if (ENABLEUPLOAD) echo \'<td align="left">\'.blackbox(get_lang(234), $upload).\'</td>\';
		?>
		</tr>
	</table>
	</td></tr>
	</table>
	</form>';

$kdesign['top'] = '
		switch($this->style)
		{
			case 0:
				?>
				<table width="100%" border="0" align="left" cellspacing="0" cellpadding="0">
				<tr>
					<td align="left" width="70%" valign="top">
					<?php if ($this->addform) $this->form(); ?>
					<table width="100%" border="0" cellpadding="0" cellspacing="0">					
					<tr>
					<td>
				<?php
			break;

			case 1:
				?>
				<table width="100%" border="0" align="left" cellspacing="0" cellpadding="0">
				<tr>
					<td width="320" valign="top">
					<?php infobox(); ?></td>
					<td align="left" valign="top">
						<?php if ($this->addform) $this->form(); ?>
						<table width="100%" border="0" cellpadding="0" cellspacing="0">
						<tr><td height="5"></td></tr>
						<tr>
						<td>						
				<?php
			break;
		}
	';

$kdesign['bottom'] = '

		switch($this->style)
		{
			case 0:
				echo \'</td><td valign="top" align="left" width="30%">\';
				infobox();
				echo \'</td></tr></table>\';
				break;
		
			case 1:
				echo \'</td></tr></table>\';
				break;
		}';


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
	if ($back) $code = '&nbsp;<a href="javascript:history.go(-1)" class="fatbuttom">&nbsp;'.get_lang(34).'&nbsp;</a>'; else $code = '';
	blackbox(get_lang(56),'<br/>'.$msg.'<br/><br/>'.$code.'<br/><br/>',0);
	kprintend();
	die();
}

function kprintlogin($msg = '')
{ 
	global $app_ver, $app_build, $PHP_SELF, $phpenv;

	if (((REQUIRE_HTTPS) && ($phpenv['https'])) || (!REQUIRE_HTTPS)) define('HTTPS_REQ_MET', true); else define('HTTPS_REQ_MET', false);

	eval(gethtml('login'));	
}

class kpdesign
{
	function kpdesign()
	{
		$this->style = db_guinfo('theme');
		$this->addform = true;
	}

	function form()
	{
		global $PHP_SELF, $runinit;
		?>
		<form style="margin:0;padding:0" name="psongs" action="<?php echo $PHP_SELF?>" method="post">
		<input type="hidden" name="action" value="listedres"/>
		<input type="hidden" name="previous" value="<?php echo $runinit['pdir64']; ?>"/>
		<?php
	}

	function top()
	{		
		eval(gethtml('top'));		
	}

	function bottom()
	{
		eval(gethtml('bottom'));
	}
}

function updatestatistics()
{
	global $cfg, $streamtypes;

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

	if (is_array($ids) && !$all)
	{
		$ok = false;
		foreach($ids as $id => $val) if ($val) $ok = true;
		if ($ok)
		{
			$sql .= ' WHERE (ftypeid ';
			foreach($ids as $id => $val) if ($val) $sql .= ' = '.$id.' or ftypeid';
			$sql = substr($sql, 0, strlen($sql) - (strlen('ftypeid') + 4)).')';			
		}
	}

	$row = mysql_fetch_array(db_execquery($sql), true);
	if ($row)
	{
		$data = $row['ls'].':'.$row['nr'].':'.$row['fs'];
		updatecache(30, $data);
		return $data;
	}
}

function compute_statistics()
{
	$data = '';
	if (!getcache(30, $data)) $data = updatestatistics();

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

function endmp3table($showalbum=1, $dirs=0, $files=0, $recursive = true) 
{
	global $u_id, $PHP_SELF, $runinit, $cfg;	
	eval(gethtml('endmp3table'));	
}

function sharedplaylists()
{
	global $runinit, $u_id;
	$out = '';
	$res = db_execquery('SELECT name, listid FROM '.TBL_PLAYLIST.' WHERE public = 1 AND u_id != '.$u_id.' ORDER by name ASC');	
	if (mysql_num_rows($res) > 0)
	{
		$out .= '<input type="hidden" name="action" value="playlist"/>';
		$out .= '<input type="hidden" name="previous" value="'.$runinit['pdir64'].'"/>&nbsp;';
		$options = array();
		while ($row = mysql_fetch_assoc($res)) $options[] = array($row['listid'], $row['name']);
		$out .= genselect('sel_shplaylist', $options, db_guinfo('defshplaylist'), 'file');		
		$out .= '&nbsp;<input type="submit" name="playplaylist" value="'.get_lang(70).'" class="fatbuttom"/> ';
		$out .= '<input type="submit" name="viewplaylist" value="'.get_lang(85).'" class="fatbuttom"/>';
	}
	return $out;
}

function infobox()
{
 	global $PHP_SELF, $u_cookieid, $u_id, $app_ver, $setctl, $u_id, $app_build, $homepage, $runinit;
	$homepage = str_replace('KBUILD', $app_build, str_replace('KVER', $app_ver, $setctl->get('homepage')));
	$ca = new caction();
	$ca->updatelist();
	eval(gethtml('infobox'));
}

function kprintheader($title='',$js_out=0)
{
	global $klang, $setctl, $app_build, $phpenv;
	if (empty($title)) $title = '| kPlaylist'; else $title = '| '.$title;	
	if ($setctl->get('includeheaders')) 
	{
	?>
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html>
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
		echo kprintcss();
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
	global $cssthemes, $setctl;
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
			if (is_array($cssthemes))
			{
				?>
				<style type="text/css">
				<?php echo $cssthemes[0]; ?>
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

function blackbox($title,$code,$returncode=1,$nowrap=true,$class='box',$textalign='left',$width=0, $extra='')
{
	return eval(gethtml('blackbox'));
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

	function toggle() 
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

	function chhttp(where) { 
		document.location = where;
	}
	//-->
	</script>
	<?php
}


$klang[0] = array('English', 'ISO-8859-1', 'English', 'What\'s hot', 'What\'s new', 'Search', '(only %1 shown)', 'sec', 'Search results: \'%1\'', 'found', 'None.', 'update search database options', 'Delete unused records?', 'Rebuild ID3?', 'Debug mode?', 'Update', 'Cancel', 'update search database', 'Found %1 files.', 'Could not determine this file: %1, skipped.', 'Installed: %1 - Update: %2, scan: ', 'Scan: ', 'Failed - query: %1', 'Could not read this file: %1. Skipped.', 'Removed link to: %1', 'Inserted %1, updated %2, deleted %3 where %4 failed and %5 skipped through %6 files - %7 sec - %8 marked for deletion.', 'Done.', 'Close', 'Found no files here: "%1"', 'kPlaylist logon', 'Album list for artist: %1', 'Hotselect %1', 'No tunes selected. Playlist not updated.', 'Playlist updated!', 'Back', 'Playlist added!', 'Remember to reload page.', 'login:', 'secret:', 'Notice! This is a non public website. All actions are logged.', 'Login', 'SSL required for logon.', 'Play', 'Delete', 'Shared: ', 'Save', 'Control playlist: \'%1\' - %2 titles', 'Editor', 'Viewer', 'Select', 'Seq', 'Status', 'Info', 'Del', 'Name', 'Totals:', 'Error', 'Action on selected: ', 'Sequence:', 'edit playlist', 'Delete this entry', 'add playlist', 'Name:', 'Create', 'Play: ', 'File', 'Album', 'All', 'Selected', 'add', 'play', 'edit', 'new', 'Select:', 'Play Control: ', 'Playlist: ', 'Hotselect numeric', 'Keyteq gives you:', '(check for upgrade)', 'Homesite', 'only id3', 'album', 'title', 'artist', 'Hotselect album from artist', 'view', 'Shared playlists', 'Users', 'Admin control', 'What\'s new', 'What\'s hot', 'Logout', 'Options', 'Check', 'My', 'edit user', 'new user', 'Full name', 'Login', 'Change password?', 'Password', 'Comment', 'Access level', 'On', 'Off', 'Delete user', 'Logout user', 'Refresh', 'New user', 'del', 'logout', 'Use EXTM3U feature?', 'Show how many rows (hot/new)', 'Max number of search results', 'Reset', 'Open directory', 'Go to directory: %1', 'Download', 'Go one step up', 'Go to root directory.', 'Check for upgrade', 'users', 'Language', 'options', 'Booted', 'Shuffle:', 'Settings', 'Base directory', 'Stream location', 'Default language', 'A Windows system', 'Require HTTPS', 'Allow seek', 'Allow download', 'Session timeout (sec)', 'Report failed login attempts', 'Hold on - fetching file list', 'Playlist could not be added!', 'Admin', 'Login with HTTPS to change!', 'Enable streaming engine', 'Title', 'Artist', 'Album', 'Comment', 'Year', 'Track', 'Genre', 'not set', 'Max download rate (kbps)', 'User', '%1 mins - %2 titles', '%1 kbit %2 mins', 'Genre list: %1', 'Go', 'Playtime: %1d %2h %3m : %4 files : %5 mb', 'No relevant resources here.', 'Password changed!', 'Signup', 'Please make a selection!', 'What is update?', 'Click here for help', 'Use external images?', 'External images path', 'Current password', 'Current password does not match!', 'Preferred archiver', 'Could not create archive!', 'Possible duplicate found:  "%1" "%2"', 'Really delete playlist?', 'Alphabetical', 'Random', 'Sort', 'Original', 'Use javascript', 'Are you sure you want to delete this user?', 'View history', 'history', 'Rows', 'External CSS file', 'Remove duplicates', 'OK', 'ERR', 'Stream', '(show as)', 'files', 'albums', '%1d %2h %3m %4s', 'General', 'Customize', 'Filehandling', 'Click on ? for help.', 'Automatic database sync', 'Send file extension', 'Allow unauthorized streams', 'Include headers', 'External javascript', 'Homepage', 'Show Keyteq gives you part', 'Show upgrade part', 'Show statistics', 'Write ID3v2 with stream', 'Enable user signup', 'File types', 'Yes', 'No', 'Extension', 'MIME', 'Include in M3U', 'edit file type', 'Sure?', 'Optimistic filecheck', 'Randomizer', 'Mode', 'Playlist', 'None, directly', 'My favourites', 'Did not find any hits', 'All-time hits', 'Order', 'Enable LAME support?', 'Disabled', 'Allow LAME usage?', 'Email', 'Allow to mail files?', 'SMTP server', 'SMTP port', 'Mail to', 'Message', 'Send', 'Mail sent!', 'Activate upload', 'Upload directory', 'Activate mp3mail', 'Upload', 'File uploaded!', 'File could not be uploaded!', 'You must enable cookies to log in!', 'Period', 'ever', 'this week', 'this month', 'last month', 'hits', 'LAME command', 'Show album cover', 'Album files', 'Resize album images', 'Album height', 'Album width', 'Mail method', 'Direct', 'Pear', 'Wait!', 'Please enter a valid e-mail in options!', 'Playlists inline?', 'Show album from URL?', 'Album URL', 'Could not send!', 'User added!', 'Archive creator', 'Archive is deleted.', 'User updated!','Music match', '%1 entries filtered','Log access','Viewable', 'Archived','Bulletin','Written %1 by %2','more', 'Publish','%1 mb', '%1 kb', '%1 bytes', 'Recursive', 'Previous', 'Next', 'Goto page %1', 'Page: ', 'Never played', 'Manually approve signups', 'Pending', 'activate', 'All fields marked with * are mandatory', 'Your account will be inspected and activated manually.', 'Last streams', 'remember me', 'Style', 'find', 'Enter paths to search', 'Use selected?', 'Track time min/max', 'Minutes', 'm3u', 'asx (WMA)', 'If update stops, click here: %1', 'Follow symlinks?', 'File presentation template', 'Enable URL security', 'Upload whitelist', 'File type not allowed.', 'Playlist is empty!', 'Lyrics', 'Lyrics URL', 'Show lyrics link?', '(or?)', 'Unknown username or password', 'Max upload size: %1', 'Open public RSS feed?', 'Please set a password!', 'Need a name and login', 'Username already in use!', 'Drop admin access for this session?', 'Fetching database records: %1/%2', 'Could not find  "%1", is file deleted?', 'From/to date (DDMMYY)', 'Error in input field(s), please try again.', 'Maximum text length', 'Dir columns', 'New template', 'Template', 'Template name', 'Need a template name!', 'Default signup template', 'Tag extractor: ', 'Allow using archiver(s)', 'Maximum archive size (mb)', 'Archive exceeded maximum size! (%1mb, max is %2mb)');

$klang[1] = array('Norwegian', 'ISO-8859-1', 'Norsk (bokmеl)', 'Hva er mest spilt', 'Hva er nytt', 'Sшk', '(bare %1 vist)', 'sek', 'Sшkeresultater: \'%1\'', 'fant', 'Ingen.', 'oppdateringsvalg for sшkedatabase', 'Slett ubrukte rader?', 'Ombygg ID3?', 'Debugmodus?', 'Oppdater', 'Avbryt', 'oppdaterer sшkedatabase', 'Fant %1 filer.', 'Kunne ikke lese fil: %1, hoppet over.', 'Installert: %1 - Oppdaterer: %2, skanner: ', 'Sшker: ', 'Feilet - spшrring: %1', 'Kunne ikke lese denne filen: %1. Hoppet over.', 'Fjernet referanse til: %1', 'La inn %1, oppdaterte %2, slettet %3 hvor %4 feilet og %5 ble hoppet over igjennom %6 filer - %7 sek - %8 markert for sletting.', 'Ferdig.', 'Lukk', 'Fant ingen filer her: "%1"', 'kPlaylist innlogging', 'Albumliste fra artist: %1', 'Hurtigvelg %1', 'Ingen lеter valgt. Spilleliste ikke oppdatert.', 'Spilleliste oppdatert!', 'Tilbake', 'Spilleliste lagt til!', 'Husk е oppdatere side.', 'logg inn:', 'hemmelighet:', 'Advarsel! Dette er en privat webside. All aktivitet blir logget.', 'Logg inn', 'SSL kreves for pеlogging.', 'Spill', 'Slett', 'Delte: ', 'Lagre', 'Kontroller spilleliste: \'%1\' - %2 titler', 'Redigerer', 'Viser', 'Velg', 'Sek', 'Status', 'Info', 'Slett', 'Navn', 'Totalt:', 'Feil', 'Handling pе valgte: ', 'Sekvens:', 'rediger spilleliste', 'Slett denne oppfшringen', 'ny spilleliste', 'Navn:', 'Lag', 'Spill: ', 'Fil', 'Album', 'Alle', 'Valgte', 'legg til', 'spill', 'editer', 'ny', 'Velg:', 'Spillekontroll: ', 'Spilleliste: ', 'Numerisk hurtigvalg', 'Keyteq gir deg:', '(se etter ny versjon)', 'Hjemmeside', 'bare id3', 'album', 'tittel', 'artist', 'Hurtigvelg album fra artist', 'vis', 'Delte spillelister', 'Brukere', 'Adminkontroll', 'Hva er nytt', 'Mest spilt', 'Logg ut', 'Valg', 'Sjekk', 'Min', 'endre brukerinformasjon', 'ny bruker', 'Fullt navn', 'Brukernavn', 'Endre passord?', 'Passord', 'Kommentar', 'Tilgangsnivе', 'Pе', 'Av', 'Slett bruker', 'Logg ut bruker', 'Oppdater', 'Ny bruker', 'slett', 'logg ut', 'Bruke EXTM3U egenskap?', 'Vise hvor mange rader (mest spilt/nytt)', 'Maks sшkerader', 'Omsetting', 'Еpne katalog', 'Gе til katalog: %1', 'Last ned', 'Gе ett steg opp', 'Gе til hovedkatalog.', 'Se etter ny versjon', 'brukere', 'Sprеk', 'valg', 'Avsperret', 'Omskuff:', 'Innstillinger', 'Hovedkatalog', 'Nedlastningslokalisasjon', 'Standardsprеk', 'Et Windows-system', 'Krev HTTPS', 'Tillat spoling', 'Tillat nedlastninger', 'Tidsavbrudd for innlogging (sek)', 'Rapportere mislykkede innloggingsforsшk', 'Vent - henter filliste', 'Spilleliste kunne ikke legges til!', 'Admin', 'Logg inn med HTTPS for е endre!', 'Aktiver innebygd kanalvirkning', 'Tittel', 'Artist', 'Album', 'Kommentar', 'Еr', 'Lеtnummer', 'Stil', 'ikke satt', 'Maksimal nedlastningshastighet', 'Bruker', '%1 minutter - %2 titler', '%1 kbit %2 minutter', 'Sjangerliste: %1', 'Gе', 'Spilletid %1d %2t %3m : %4 filer : %5 mb', 'Ingen relevante ressurser her.', 'Passord endret!', 'Ny bruker', 'Vennligst foreta et valg!', 'Hva er oppdatering?', 'Klikk her for hjelp', 'Bruk eksterne bilder?', 'Plassering for eksterne bilder', 'Eksisterende passord', 'Det eksisterende passordet er feil!', 'Шnsket arkiveringsprogram', 'Arkiv kunne ikke opprettes', 'Mulig duplikat funnet: %1 - %2', 'Virkelig slette spilleliste?', 'Alfabetisk', 'Tilfeldig', 'Sorter', 'Original', 'Bruke javascript', 'Er du sikker pе at du vil slette denne brukeren?', 'Vis historie', 'historie', 'Rader', 'Ekstern CSS fil', 'Fjern duplikater', 'OK', 'FEIL', 'Stream', '(vis som)', 'filer', 'album', '%1d %2t %3m %4s', 'Generelt', 'Skreddersy', 'Filhеndtering', 'Klikk pе ? for hjelp.', 'Automatisk databasesynkronisering', 'Send filendelse', 'Tillat uautoriserte streams', 'Inkluder headere', 'Eksternt javascript', 'Hjemmeside', 'Vis Keyteq gir deg del', 'Vis oppgraderingsdel', 'Vis statistikk', 'Skriv ID3v2 i stream', 'Ny bruker funksjonalitet', 'Filtyper', 'Ja', 'Nei', 'Filendelse', 'MIME', 'Inkluder i M3U', 'editer filtype', 'Sikker?', 'Optimistisk filsjekk', 'Randomiserer', 'Modus', 'Spilleliste', 'Ingen, direkte', 'Mine favoritter', 'Fant ingen rader', 'Hits pе systemet', 'Rekkefшlge', 'Slе pе LAME stшtte', 'Deaktivert', 'Tillat LAME bruk?', 'E-post', 'Tillat e-post av filer', 'SMTP-tjener', 'SMTP-port', 'E-post til', 'Beskjed', 'Send', 'E-post sendt!', 'Aktiver opplastning', 'Opplastningskatalog', 'Aktiver mp3e-post', 'Last opp', 'Fil lastet opp!', 'Fil kunne ikke bli lastet opp!', 'Du er nшdt til е skru pе cookies for е logge inn!', 'Periode', 'siden alltid', 'denne uken', 'denne mеneden', 'siste mеned', 'hits', 'LAME-kommando', 'Vis albumcover', 'Albumfiler', 'Omskaler albumbilder', 'Albumhшyde', 'Albumbredde', 'E-postmetode', 'Direkte', 'Pear', 'Vent!', 'Vennligst skriv inn en gyldig e-post i alternativer!', 'Spillelister direkte?', 'Vis album fra URL?', 'Album URL', 'Kunne ikke sende!', 'Bruker lagt til!', 'Arkivgenerator', 'Arkivet er slettet.', 'Bruker oppdatert!', 'Musikktilpassing', '%1 rader filtrert', 'Logg aksess', 'Vis', 'Arkivert', 'Oppslagstavle', 'Skrevet den %1 av %2', 'mer', 'Publiser', '%1 mb', '%1 kb', '%1 bytes', 'Rekursiv', 'Forrige', 'Neste', 'Gе til side %1', 'Side:', 'Aldri spilt', 'Bekreft nyregistreringer manuelt', 'Venter', 'aktiver', 'Alle felter markert med * er obligatoriske', 'Kontoen din vil bli sjekket og aktivert manuelt.', 'Siste avspillinger', 'husk meg', 'Stil', 'finn', 'Skriv inn kataloger og sшke i', 'Bruke valgte?', 'Spilletid min/maks', 'Minutter', 'm3u', 'asx (WMA)', 'Hvis oppdateringen stopper, klikk her: %1', 'Fшlg symboliske lenker?', 'Mal for presentasjon av fillister', 'Aktiver URL-sikkerhet', 'Tillatelseliste for opplasting', 'Filtypen er ikke tillatt', 'Spillelisten er tom!', 'Tekster', 'URL til tekster', 'Vis lenke til tekster?', '(eller?)', 'Ukjent brukernavn eller passord', 'Maks opplastningsstшrrelse: %1', 'Еpne offentlig RSS-tilgang', 'Vennligst skriv et passord!', 'Trenger brukernavn og navn', 'Brukernavn er allerede i bruk!', 'Slе av administrasjonstilgang for denne pеloggingen?', 'Henter database rader %1/%2', 'Kunne ikke finne "%1", er fil slettet?', 'Fra/til dato (DDMMЕЕ)', 'Feil i verdier, vennligst prшv pе nytt', 'Maks tekstlengde', 'Katalogkolonner', 'Ny brukermal', 'Mal', 'Navn pе mal', 'Trenger er malnavn', 'Standard mal for ny bruker', 'Tagekstraktor', 'Tillat bruk av arkivering', 'Maksimal arkivstшrrelse (mb)', 'Arkiv stшrre enn det som er tillatt! (%1mb, maks er %2)');

$klang[2] = array('German', 'ISO-8859-15', 'Deutsch', 'Was ist hip', 'Was ist neu', 'Suchen', '(nur %1 angezeigt)', 'Sek', 'Suchergebnisse: \'%1\'', 'gefunden', 'Keine.', 'Einstellungen fьr die Aktualisierung der Such-Datenbank', 'Unbenutzte Datensдtze lцschen?', 'ID3 erneuern?', 'Debug Modus?', 'Update', 'Abbrechen', 'Such-Datenbank aktualisieren', '%1 Dateien gefunden', 'Konnte Datei nicht untersuchen: %1, wird ьbersprungen.', 'Installiert: %1 - Aktualisiert: %2, untersuche:', 'Suche: ', 'Fehler - Abfrage: %1', 'Konnte Datei nicht lesen: %1, wird ьbersprungen.', 'Entfernt: %1', 'Eingefьgt %1, aktualisiert %2, gelцscht %3, dabei %4 fehlgeschlagen und %5 ьbersprungen; %6 Dateien gesamt - %7 Sek - %8 markiert zum lцschen.', 'Erledigt', 'Schliessen', 'Konnte hier keine Dateien finden: "%1"', 'kPlaylist Login', 'Album Liste fьr Interpret: %1', 'Kurzwahl %1', 'Keine Lieder ausgewдhlt. Playliste nicht aktualisiert.', 'Playliste aktualisiert', 'Zurьck', 'Playliste hinzugefьgt!', 'Die Seite erneut laden!', 'Login:', 'Passwort:', 'Achtung! Dies ist eine private Webseite! Alle Aktionen werden protokolliert!', 'Login', 'SSL wird zum Einloggen benцtigt.', 'Abspielen', 'Lцschen', 'Цffentlich: ', 'Sichern', 'Playliste bearbeiten: "%1" - %2 Titel', 'Editor', 'Betrachter', 'Auswдhlen', 'Seq', 'Status', 'Info', 'Lцschen', 'Name', 'Summe:', 'Fehler', 'Aktion auf Auswahl:', 'Reihenfolge:', 'Playliste bearbeiten', 'Diesen Eintrag lцschen', 'Playliste hinzufьgen', 'Name:', 'Erstellen', 'Abspielen: ', 'Datei', 'Album', 'Alle', 'Auswahl', 'Hinzufьgen', 'Abspielen', 'Bearbeiten', 'Neu', 'Auswдhlen:', 'Spielen: ', 'Playliste: ', 'Kurzwahl numerisch', 'Keyteq prдsentiert:', '(Suche nach Update)', 'Startseite', 'Nur ID3 Tags', 'Album', 'Titel', 'Interpret', 'Kurzwahl Album nach Interpret', 'Zeige', 'Gemeinsame Playlisten', 'Benutzer', 'Administration', 'Was ist neu', 'Was ist hip', 'Logout', 'Optionen', 'Ьberprьfen', 'Mein KPlaylist', 'Benutzer дndern', 'Neuer Benutzer', 'Vollstдndiger Name', 'Login', 'Passwort дndern?', 'Passwort', 'Anmerkung', 'Zugangslevel', 'An', 'Aus', 'Benutzer lцschen', 'Benutzer ausloggen', 'Erneuern', 'Neuer Benutzer', 'Lцschen', 'Logout', 'EXTM3U Feature benutzen?', 'Wieviele Zeilen zeigen (hip/neu)', 'Max. Anzahl von Suchergebnissen', 'Reset', 'Verzeichnis цffnen', 'Gehe zum Verzeichnis: %1', 'Download', 'Eine Ebene hцher', 'In das Basisverzeichnis', 'Nach einem Upgrade suchen', 'Benutzer', 'Sprache', 'Optionen', 'Gesperrt', 'Zufall:', 'Einstellungen', 'Hauptverzeichnis', 'Stream Location', 'Voreingestellte Sprache', 'Ein Windows-System', 'Benцtigt HTTPS', 'Suche erlaubt', 'Download erlaubt', 'Session Timeout', 'Fehlgeschlagene Login-Versuche protokollieren', 'Bitte warten - hole Dateiliste', 'Playliste konnte nicht erstellt werden!', 'Administrator', 'Einloggen mit HTTPS fьr Дnderungen', 'Streaming Engine aktivieren', 'Titel', 'Artist', 'Album', 'Kommentar', 'Jahr', 'Lied', 'Genre', 'nicht gesetzt', 'Max. Download Rate (kbit/s)', 'Benutzer', '%1 Min - %2 Titel', '%1 kbit %2 Min', 'Genre Liste: %1', 'Los', '%1T %2Std %3Min Spielzeit %4 Dateien %5 MB', 'Hier gibt es keine passenden Eintrдge.', 'Passwort geдndert!', 'Anmelden', 'Bitte treffe eine Auswahl!', 'Was ist ein Update?', 'Klicke hier fьr Hilfe', 'Benutze externe Bilder?', 'Pfad zu externen Bildern', 'Aktuelles Passwort', 'Aktuelles Passwort nicht korrekt!', 'Bevorzugter Archivierer', 'Archiv konnte nicht erstellt werden', 'Mцgliche doppelte Datei gefunden: "%1" - "%2"', 'Playliste wirklich lцschen?', 'Alphabetisch', 'Zufall', 'Sortiert', 'Original', 'Benutze Javascript', 'Benutzer wirklich lцschen?', 'Zeige History', 'History', 'Zeilen', 'Externe CSS Datei', 'Lцsche doppelte Eintrдge', 'OK', 'FEHLER', 'Stream', '(erscheinen wie)', 'Dateien', 'Album', '%1T %2Std %3Min %4Sek ', 'Allgemein', 'Anpassen', 'Datei Kontrolle', 'Klick das "?" fьr Hilfe', 'Automatische Datenbanksynchronisation', 'Dateiendungen senden', 'Nichtautorisierte Streams erlauben', 'Header einbeziehen', 'Externes Javascript', 'Homepage', 'Zeige "Keyteq hat" Teil', 'Zeige Upgrade-Teil', 'Zeige Statistik', 'Schreibe ID3v2 Tags beim Streaming', 'Benutzer Anmeldung aktivieren', 'Datei Typen', 'Ja', 'Nein', 'Dateiendung', 'MIME', 'M3U einbeziehen', 'Datei Typ bearbeiten', 'Sicher?', 'Optimistische Dateiprьfung', 'Zufallsgenerator', 'Modus', 'Playliste', 'Nein, direkt', 'Meine Favoriten', 'Keine Treffer gefunden', 'Absolute Hits', 'Reihenfolge', 'LAME Unterstьtzung aktivieren?', 'Deaktiviert', 'LAME Verwendung erlauben?', 'Email', 'Versenden von Dateien per Email erlauben?', 'SMTP Server', 'SMTP Port', 'Email an', 'Nachricht', 'Senden', 'Email gesendet!', 'Aktiviere Upload', 'Upload-Verzeichnis', 'Aktiviere mp3mail', 'Upload', 'Datei hochgeladen!', 'Datei konnte nicht hochgeladen werden!', 'Cookies mьssen aktiviert sein, um einzuloggen!', 'Zeitraum', 'Immer', 'Diese Woche', 'Diesen Monat', 'Letzten Monat', 'Hits', 'LAME Befehl', 'Zeige Album Cover', 'Album Dateien', 'Grцsse der Album-Bilder anpassen', 'Album Hцhe', 'Album Breite', 'Email Methode', 'Direkt', 'Pear', 'Warten!', 'Bitte eine gьltige Emailadresse angeben!', 'Playlists inline?', 'Zeige Album von URL?', 'Album URL', 'Konnte nicht senden!', 'Benutzer hinzugefьgt!', 'Archiv-Ersteller', 'Archiv wurde gelцscht', 'User aktualisiert', 'Musik-Treffer', '%1 Eintrдge gefiltert', 'Zugriff auf Log', 'Lesbar', 'Archiviert', 'Bulletin', '%1 von %2 eingefьgt', 'Mehr', 'Verцffentlichen', '%1 MB', '%1 KB', '%1 Bytes', 'Unterverzeichnis spielen', 'Vorhergehende', 'Nдchste', 'Gehe zu Seite %1', 'Seite:', 'Nie gespielt', 'Anmeldung manuell akzeptieren', 'Anhдngig', 'aktiviere', 'Alle mit * markierten Felder sind zwingend', 'Dein Zugang wird ьberprьft und manuell aktiviert.', 'Kьrzliche Streams', 'Login merken', 'Stil', 'Finde', 'Gib den zu durchsuchenden Pfad ein', 'Benutzer ausgewдhlt', 'Zeit Titel: min/max', 'Minuten', 'm3u', 'asx', 'Falls das Update fehlschlдgt, klicke hier %1', 'Symbolischen Links folgen?', 'Datei Template', 'Aktiviere URL Sicherheit', 'Lade Whitelist hoch', 'Dateityp nicht erlaubt', 'Wiedergabeliste ist leer!', 'Lyrics', 'Lyrics URL', 'Lyrics Link anzeigen', '(oder?)', 'Unbekannter Benutzername oder Passwort', 'Maximale Upload GrцЯe: %1', 'Цffentlich RSS Feed erstellen?', 'Bitte Passwort festlegen', 'Benцtige Name und Login', 'Benutzername ist bereits eingeloggt', 'Adminberechtigung fьr die aktuelle Session entfernen?', 'Hole Datenbankeintraege: %1/%2', 'Kann Datei nicht finden "%1", vielleicht gelцscht?', 'von/bis Datum (TTMMJJ)', 'Eingabefehler, bitte noch einmal versuchen', 'maximale Anzahl von Textzeichen');

$klang[3] = array('Swedish', 'ISO-8859-10', 'Svenska', 'Vad дr Hetast', 'Vad дr Nytt', 'Sцk', '(endast %1 visad)', 'Sek', 'Sцkresultat: \'%1\'', 'hittade', 'Ingen.', 'uppdatera instдllningar fцr sцkdatabas', 'Ta bort oanvдnda album', 'Еteruppbygg ID3?', 'Kцr debug?', 'Uppdatera', 'Avbryt', 'uppdatera sцkdatabas', 'Hittade %1 filer.', 'Kunde inte lдsa fil: %1, hoppade цver.', 'Installerer %1 - Uppdaterar: %2, lдser:', 'Lдser:', 'Misslyckades - frеga: %1', 'Kunde inte lдsa filen: %1, Hoppade цver.', 'Tog bort: %1', 'Infogade %1, uppdaterade %2, tog bort %3, varav %4 misslyckades och hoppade цver %5 av %6 filer - %7 sek - %8 markerade fцr borttaganing', 'Fдrdig', 'Stдng', 'Kunde inte hitta nеgra filer hдr: \'%1\'', 'kPlaylist Inloggning', 'Albumlista fцr artist: %1', 'Snabbval %1', 'Inga lеtar valda. Spellistan дr ej updaterad.', 'Spellista uppdaterad!', 'Tillbaka', 'Spellista inlagd!', 'Kom ihеg att uppdatera sidan.', 'Anvдndarnamn:', 'Lцsenord:', 'Observera! Detta дr inte en publik websida. All aktivitet дr loggad.', 'Inloggning', 'SSL behцvs fцr inloggning', 'Spela', 'Ta Bort', 'Delad:', 'Spara', 'Kontrollera lеtlista: "%1" - %2 titlar', 'Redigerare ', 'Visare ', 'Vдlj ', 'Sekv ', 'Status', 'Info', 'Ta Bort', 'Namn', 'Totalt:', 'Fel', 'Handling vid val', 'Sekvens:', 'redigera spellista', 'Ta bort den hдr raden', 'Lдgg till spellista', 'Namn:', 'Skapa', 'Spela:', 'Fil', 'Album', 'Alla', 'Markerad', 'lдgg till', 'spela', 'redigera', 'ny', 'Vдlj:', 'Spelkontroll:', 'Spellista:', 'Snabbvдlj numeriskt', 'Keyteq ger dig:', '(Sцk efter uppdatering)', 'Hemsida', 'endast id3', 'album', 'titel', 'artist', 'Snabbvдlj album frеn artist', 'visa', 'Delade spellistor', 'Anvдndare', 'Adminkontroll', 'Vad дr nytt', 'Mest spelat', 'Logga ut', 'Instдllningar', 'Kontrollera ', 'Min ', 'redigera anvдndare', 'ny anvдndare', 'Fullstдndigt namn', 'Anvдndarnamn ', 'Дndra lцsenord?', 'Lцsenord', 'Kommentar ', 'Behцrighet ', 'Pе ', 'Av ', 'Ta bort anvдndare', 'Logga ut anvдndare', 'Uppdatera ', 'Ny anvдndare', 'ta bort', 'logga ut', 'Anvдnd EXTM3U funktion?', 'Visa hur mеnga rader (mest spelat/nytt)', 'Hцgst antal sцkrader', 'Nollstдll', 'Цppna mapp', 'Gе till mapp: %1', 'Ladda ner', 'Gе ett steg upp', 'Gе till rotkatalogen', 'Kolla efter uppgradering', 'anvдndare ', 'Sprеk ', 'instдllningar ', 'Kickad', 'Blanda', 'Instдllningar', 'Rotnivе ', 'Stream lokalisering', 'Standard sprеk', 'Ett Windowssystem', 'Krдv HTTPS', 'Tillеt filsцk', 'Tillеt nerladdning', 'Sessionen avbruten.', 'Rapportera misslyckat loginfцrsцk', 'Vдnta - hдmtar fillista', 'Spellista kunde inte lдggas till!', 'Admin', 'Logga in med HTTPS fцr att дndra!', 'Aktivera streaming', 'Titel', 'Artist', 'Album', 'Kommentar', 'Еr', 'Spеr', 'Genre', 'Inte satt', 'Max nerladdningshastighet (kbps)', 'Anvдndare', '%1 min - %2 titlar', '%1 kbit %2 min', 'Genre lista: %1', 'Gе', '%1d %2t %3m speltid %4 filer %5 MB', 'Inga relevanta resurser hдr.', 'Lцsenordet дndrat!', 'Skapa konto', 'Var vдnlig och gцr ett val!', 'Vad дr uppdatering?', 'Klicka hдr fцr hjдlp.', 'Anvдnda externa bilder?', 'Externa bildens sцkvдg.', 'Nuvarande lцsenord', 'Nuvarande lцsenord matchar inte!', 'Цnskad arkiverare', 'Arkiv kunde inte skapas', 'Trolig fildubblett hittad: "%1"  "%2"', 'Verkligen radera spellistan?', 'Alfabetisk', 'Slumpad', 'Sortera', 'Original', 'Anvдnd javascript', 'Дr du sдker att du vill radera denna anvдndare?', 'Visa historia', 'historia', 'Rader', 'Extern CSS fil', 'Ta bort dubletter', 'OK', 'FEL', 'Stream', '(visa som)', 'filer', 'album', '%1d %2t %3m %4s', 'Generellt', 'Anpassa', 'Filhanterning', 'Klicka pе ? fцr hjдlp', 'Automatisk databas synkronisering', 'Skicka fil дndelse', 'Tillеt overifierade streamar', 'Inkludera headers', 'Externt javascript', 'Hemsida', 'Visa Keyteq ger dig del', 'Visa uppgraderingsdel', 'Visa statistik', 'Skriv ID3v2 med stream', 'Aktivera anvдndarregistrering', 'Filtyper', 'Ja', 'Nej', 'Fil дndelse', 'MIME', 'Inkludera i M3U', 'editera filtyp', 'Sдkert?', 'Optimistisk filkontroll', 'Randomisera', 'Lдge', 'Spellista', 'Ingen, direkt', 'Mina favoriter', 'Kunde inte hitta nеgra trдffar', 'Alla tiders hitlеtar', 'Ordning', 'Aktivera LAME-stцd?', 'Avstдngd', 'Tillеt LAME-anvдndning?', 'Epost', 'Tillеt epost av filer?', 'SMTP-server', 'SMTP-port', 'E-Post till', 'Meddelande', 'Skicka', 'Meddelandet skickat!', 'Aktivera uppladdning', 'Uppladdningsbibliotek', 'Aktivera mp3mail', 'Uppladdning ', 'Fil uppladdad', 'Filen kunde ej laddas upp', 'Du mеste aktivera cookies fцr att kunna logga in!', 'Period', 'Nеgonsin', 'Denna vecka ', 'Denna mеnad', 'Senaste mеnaden', 'trдffar', 'LAME kommando', 'Visa omslag', 'Albumfiler', 'Anpassa bildens storlek', 'Hцjd', 'Bredd', 'Brevmetod', 'Direkt', 'Pear', 'Vдnta', 'Skriv in en giltig epostadress i instдllningar!', 'Playlist inline', 'Visa album frеn URL?', 'Album URL', 'Kunde inte skicka!', 'Anvдndare upplagd!', 'Arkiv skapare', 'Arkiv raderat', 'Anvдndare uppdaterad!', 'Music match', '%1 inlдgg filtrerat', 'Logg access', 'Visningsbar', 'Arkiv', 'Bulletin', 'Ifyllt %1 av %2', 'mer', 'Publisera', '%1 mb', '%1 kb', '%1 bytes', 'Еterkommande', 'Fцregеende', 'Nдsta', 'Gе till sida %1', 'Sida:', 'Aldrig spelad', 'Manuellt godkдnna registreringar', 'Vдntande', 'aktivera', 'Alla fдlt markerade med * дr obligatoriska', 'Ditt konto kommer att kontrolleras och aktiveras manuellt.', 'Senaste streamar', 'kom ihеg mig', 'Stil', 'hitta', 'Fyll i sцkvдgar fцr att sцka efter', 'Anvдnd valda?', 'Track tid min/max', 'Minuter', 'm3u', 'asx (WMA)', 'Om uppdateringen stannar, klicka hдr: %1', 'Fцlj symlink?', 'Fil mall', 'Aktivera URL sдkerhet', 'Ladda upp vitlista', 'Filtypen дr inte tillеten.', 'Spellistan дr tom!', 'Sеngtexter', 'Sеngtexter URL', 'Visa sеngtexter lдnk?', '(eller?)', 'Felaktigt anvдndarnamn eller lцsenord', 'Max filstorlek vid uppladdning: %1', 'Цppna publik RSS flцde?', 'Ange ett lцsenord.', 'Anvдndarnamn och lцsenord mеste sдttas', 'Anvдndarnamnet upptaget!', 'Drop admin access for this session?', 'Hдmtar data: %1/%2', 'Kan inte hitta "%1", filen borttagen?', 'Frеn/till datum (DDMMYY)', 'Fel i fдlt, fцrsцk igen', 'Max textlдngd');

$klang[4] = array('Dutch', 'ISO-8859-15', 'Nederlands', 'Wat is populair', 'Wat is nieuw', 'Zoek', '(waarvan %1 in deze lijst)', 'sec', 'Zoek resultaten: \'%1\'', 'gevonden', 'Geen.', 'update zoek database opties', 'Verwijder ongebruikte bestanden? ', 'ID3 vernieuwen?', 'Fout opsporing?', 'Vernieuwen', 'Annuleren', 'Zoek database updaten', '%1 bestanden gevonden.', 'Problemen met : %1, overgeslagen.', 'Toegevoegd: %1 Aangepast: %2 Scan:', 'Scan:', 'Mislukt - gezocht: %1', 'Kan het volgende bestand niet lezen: %1. Overgeslagen.', 'Verwijderd: %1', 'Toegevoegd %1, bijgewerkt %2, verwijderd %3 waarvan %4 mislukt en %5 overgelagen van %6 bestanden - %7 sec - %8 gemarkeerd voor verwijdering.', 'Klaar', 'Sluiten', 'Kan geen bestanden vinden in: "%1"', 'kPlaylist Login', 'Albumlijst van artiest: %1', 'Snelkeuze %1', 'Geen muziek geselecteerd. Afspeellijst niet bijgewerkt.', 'Afspeellijst bijgewerkt!', 'Terug', 'Afspeellijst toegevoegd!', 'Niet vergeten om de pagina te verversen.', 'Gebruikersnaam:', 'Wachtwoord:', 'NB! Dit is een niet publieke website. Alle acties worden opgeslagen in een log bestand.', 'Login', 'SSL benodigd om in te loggen.', 'Afspelen', 'Verwijderen', 'Gedeeld', 'Opslaan', 'Instellingen afspeellijst "%1"- %2 nummer(s)', 'Editor', 'Viewer', 'Selecteren', 'Volgorde', 'Status', 'Informatie', 'Verwijder', 'Naam', 'Totaal:', 'Fout', 'Actie op selectie:', 'Volgorde:', 'afspeellijst bewerken', 'Verwijder deze regel', 'afspeellijst toevoegen', 'Naam:', 'Aanmaken', 'Afspelen:', 'Bestand', 'Album', 'Alles', 'Geselecteerd', 'toevoegen', 'afspelen', 'bewerken', 'nieuw', 'Selectie:', 'Afspeel opties', 'Afspeellijst:', 'Snelkeuze nummer', 'Keyteq presenteert:', '(Update controle)', 'Startpagina', 'alleen id3', 'album', 'titel', 'artiest', 'Album snelkeuze op artiest', 'bekijk', 'Gedeelde afspeellijsten', 'Gebruikers', 'Administrator opties', 'Wat is nieuw', 'Wat is Populair', 'Uitloggen', 'Instellingen', 'Controleer', 'Mijn opties', 'Bewerk gebruikersaccount', 'Nieuw gebruikersaccount', 'Volledige naam', 'Inlog naam:', 'Wachtwoord veranderen?', 'Wachtwoord', 'Commentaar', 'Toegangs level', 'Actief', '----', 'Verwijder gebruiker', 'Gebruiker afsluiten', 'Ververs pagina', 'Nieuwe gebruiker', 'Wis', 'uitloggen', 'Gebruik EXTM3U optie?', 'Hoeveel rijen tonen (Populair / Nieuw)', 'Maximaal aantal rijen zoekresultaat', 'Reset', 'Open map', 'Ga naar map: %1', 'Download', 'Een stap terug', 'Bovenste map', 'Update controle', 'gebruikers', 'Taal', 'opties', 'Booted', 'Willekeurig:', 'Instellingen', 'Start directory', 'Stream lokatie', 'Standaard taal', 'Een Windows systeem', 'HTTPS benodigd', 'Zoeken toestaan', 'Downloaden toestaan', 'Sessie timeout', 'Raporteer niet geslaagde inlog pogingen', 'Een ogenblik - bestands lijst ophalen', 'Afspeellijst kan niet toegevoegd worden!', 'Beheer', 'Om te wijzigen inloggen met https verbinding!', 'Gebruik stream engine', 'Titel', 'Artiest', 'Album', 'Bijzonderheden', 'Jaar', 'Nummer', 'Genre', 'niet ingesteld', 'Maximale downloadsnelheid (kbps)', 'Gebruiker', '%1 minuten- %2 titels', '%1 kbit %2 minuten', 'Genre lijst: %1', 'Ok', '%1d %2h %3m afspeelduur %4 bestanden %5 mb', 'Geen relevante bron aanwezig', 'Wachtwoord veranderd!', 'Aanmelden', 'Maak een keuze a.u.b.!', 'Toelichting bij het vernieuwen van de database?', 'Klik hier voor help', 'Externe plaatjes gebruiken?', 'Path naar externe plaatjes', 'Huidig wachtwoord', 'Huidig wachtwoord is niet het zelfde!', 'Compressie programma voorkeur', 'Gecomprimeerd bestand kon niet aangemaakt worden', 'Bestand mogelijk dubbel: %1 - %2', 'Afspeellijst zeker verwijderen?', 'Alfabetisch', 'Willekeurig', 'Sorteer', 'Origineel', 'Gebruik Javascript', 'Weet u zeker dat u deze gebruiker wil verwijderen?', 'Geef historie weer', 'historie', 'Regels', 'Extern Css bestand', 'Verwijder dubbelingen', 'Ok', 'FOUT', 'Stream', '(laat zien als)', 'bestanden', 'albums', '%1d %2u %3m %4s', 'Algemeen', 'Aanpassen', 'Bestands afhandeling', 'Klik ? voor hulp.', 'Synchroniseer database automatisch', 'Zend bestands extentie', 'Sta niet geautoriseerde streams toe', 'Inclusief\' koptekst ', 'Extern javascript', 'Home pagina', 'Laat regel "Keyteq presenteert" zien', 'Laat regel "Update controle" zien', 'Laat statistieken zien', 'Stuur ID3v2 mee met stream', 'Sta aanmelding van gebruikers toe', 'Bestand typen', 'Ja', 'Nee', 'Extentie', 'MIME', 'M3U insluiten', 'Pas bestandtype aan', 'Zeker?', 'Optimistische bestandscontrole', 'Willekeurig afspelen', 'Modus', 'Afspeel lijst', 'Geen, direct', 'Mijn favorieten', 'Niets gevonden', 'Hits allertijden', 'Volgorde', 'Ondersteuning voor LAME aanzetten?', 'Uitgezet', 'Gebruik van LAME toestaan?', 'Email adres', 'Sta het sturen van bestanden via de mail toe?', 'SMTP server', 'SMTP poort', 'Bericht aan', 'Bericht', 'Verstuur', 'Bericht verzonden!', 'Activeer upload', 'Upload map', 'Activeer MP3Mail', 'Upload', 'Bestand geupload!', 'Bestand kon niet geupload worden!', '"Cookies" moeten "aan" staan om in te loggen!', 'Periode', 'ooit', 'deze week', 'deze maand', 'laatste maand', 'gevonden', 'LAME parameters', 'Albumhoes tonen', 'Albumhoes bestanden', 'Albumhoes formaat aanpassen', 'Albumhoes hoogte', 'Albumhoes breedte', 'Wijze van mail versturen', 'Direct', 'Pear', 'Wacht', 'Gelieve geldig email adres in te vullen! Zie "Opties"!', 'Afspeellijst insluiten?  ', 'Albumhoes ophalen vanaf URL?', 'Albumhoes URL', 'Het verzenden is mislukt!', 'Gebruiker toegevoegd!', 'Compressie bestand aangemaakt door', 'Compressie bestand gewist.', 'Gebruikersaccount aangepast!', 'Muziek overeenkomst', '%1 gefilterd', 'Log toegang', 'Zichtbaar', 'Gearchiveerd', 'Berichten', 'Geplaatst %1 door %2', 'meer', 'Publiceer', '%1 mb', '%1 kb', '%1 bytes', 'Recursief', 'Vorige', 'Volgende', 'Ga naar pagina %1', 'Pagina:', 'Nog nooit gespeeld', 'Handmatig activeren van nieuwe aanmeldingen', 'Bezig', 'activeer', 'Alle velden met een * verplicht', 'Uw account wordt gecontroleerd en geactiveerd door een admin', 'Laatste stream', 'onthoud mijn gegevens', 'Stijl', 'zoek', 'Vul bestandslocatie in om te zoeken', 'Gebruik de geselecteerde bestanden?', 'Track tijd min/max', 'Minuten', 'm3u', 'asx (WMA)', 'Als de update stopt, klik hier: %1', 'Volg symlinks?', 'Bestands template', 'Zet URL beveiling aan.', 'Upload witte lijst.', 'Bestandstype niet toegestaan.', 'Playlist is leeg.', 'Songteksten', 'Songtekst URL', 'Laat de songtekst link zien?', '(of?)', 'Onbekende gebruikersnaam of wachtwoord', 'Maximum upload grootte: %1', 'Open publieke RSS feed?', 'Stel aub een wachtwoord in', 'Naam en login vereist', 'Gebruikersnaam is al bezet', 'Wil je de admin opties voor deze sessie stoppen?', 'Zoeken van database bestanden: %1/%2', 'Kon niks vinden "%1", is het bestand verwijderd?', 'Van/tot datum (DDMMYY)', 'Fout bij het invul veld, probeer het nog eens', 'Maximum tekst lengte');

$klang[5] = array('Spanish', 'ISO-8859-1', 'Espaсol', 'Lo popular', 'Lo nuevo', 'Bъsqueda', 'sуlo el %1 es visible', 'seg', 'Resulados de Bъsqueda: \'%1\'', 'encontrado', 'Ninguno.', 'actualizar las opciones de bъsqueda de la base de datos', 'їSuprimir entradas sin uso? ', 'їReconstruir ID3? ', 'їModo de Depuraciуn? ', 'Actualizar', 'Cancelar', 'actualizar la base de datos de bъsqueda', 'Se Encontraron %1 archivos', 'No se pudo determinar este archivo: %1, omitido', 'Instalado: %1 - Actualizado: %2, scanear:  ', 'Scanear', 'Bъsqueda Fallida: %1', 'No se pudo enconrar el archivo: %1. Omitido.', 'Borrado: %1', 'Insertado %1, actualizado %2, borrado %3 dуnde %4 fallу y %5 omitido %6 archivos - %7 seg - %8 marcado para borrar.', 'Finalizado', 'Cerrar', 'No se encontraron archivos en: "%1"', 'kPlaylist Entrada', 'Lista de canciones del artista: %1 ', 'Hotselect %1 ', 'Ninguna canciуn seleccionada. Lista no actualizada. ', 'ЎLista actualizada con йxito!', 'Regresar', 'ЎLista agregada!', 'Recuerde actualizar la pбgina', 'nombre de usuario:', 'contraseсa:', 'Aviso! Este es un sitio restringido. Todos los eventos se registrarбn.', 'Entrar', 'SSL requirido para entrar.', 'Reproducir', 'Borrar', 'Compartido:', 'Guardar', 'Lista de Control: "%1" - %2 tнtulos', 'Editor', 'Visor', 'Seleccionar', 'Seq', 'Estatus', 'Info', 'Supr', 'Nombre', 'Totales:', 'Error', 'Acciуn al seleccionar:', 'Secuencia:', 'editar lista de reproducciуn', 'Borrar esta entrada', 'agregar lista', 'Nombre:', 'Crear', 'Reproducir:', 'Archivo', 'Disco', 'Todo', 'Seleccionados', 'agregar', 'reproducir', 'editar', 'nuevo', 'Seleccionar:', 'Control de Reproducciуn:', 'Lista de reproducciуn:', 'Seleccionador Numйrico ', 'Keyteq le proporciona:', '(buscar actualizaciones)', 'Pбgina Principal', 'sуlo id3', 'disco', 'tнtulo', 'artista', 'Seleccionador disco de artista', 'ver', 'Listas compartidas', 'Usuarios', 'Control de administrador', 'Lo nuevo', 'Lo popular', 'Salir', 'Opciones', 'Seleccionar', 'Mi', 'editar usuario', 'nuevo usuario', 'Nombre completo', 'Entrar', 'їCambiar contraseсa?', 'Contraseсa', 'Comentario', 'Nivel de aceso', 'Encendido', 'Apagado', 'Borrar usuario', 'Desconectar usuario', 'Actualizar', 'Nuevo usuario', 'supr', 'salir', 'їUtilizar la opciуn de EXTM3U?', 'Mostrar cuantas filas (popular/nuevo)', 'Mбx filas de la bъsqueda', 'Restaurar', 'Abrir directorio', 'Ir al directorio: %1', 'Descargar', 'Subir un nivel', 'Ir al directorio raнz', 'Buscar actualizaciones', 'usuarios', 'Idioma', 'opciones', 'Cerrado', 'Al azar:', 'Configuraciуn', 'Directorio principal', 'Posiciуn del stream', 'Idioma predeterminado', 'Un sistema "Windows"', 'Requiere HTTPS', 'Permitir buscar', 'Permitir descargar', 'Sesiуn expirada ', 'Informar intentos de registro fallidos', 'Espere - obteniendo la lista de archivos', 'ЎNo se pudo agregar la lista!', 'Admin', 'Conexiуn con HTTPS a cambiar', 'їUtilizar streaming?', 'Tнtulo', 'Artista', 'Disco', 'Comentario', 'Aсo', 'Pista', 'Gйnero', 'no establecido', 'Tasa mбxima de descarga (kbps)', 'Usuario', '%1 minutos - %2 pistas', '%1 kbit %2 min', 'Lista de gйneros: %1', 'Ir', '%1d %2h %3m tiempo de reproducciуn %4 files %5 mb', 'No hay recursos importantes aquн', 'ЎContraseсa actualizada!', 'Registrarse', 'ЎPor favor seleccione!', 'їQuй estб actualizado?', 'ЎAyuda!', 'їUtilizar imбgenes externas?', 'Ruta de las imбgenes externas', 'Contraseсa actual', 'ЎLa contraseсa actual no coincide!', 'Archivador preferido', 'No se pudo hacer el archivo', 'Se encontro un archivo probablemente duplicado en: "%1" "%2"', 'їRealmente borrar la lista?', 'Alfabйtico', 'Al azar', 'Ordenar', 'Original', 'Utilizar javascript', 'їEstб seguro de que desea eliminar este usuario?', 'Historial las vistas', 'historial', 'Filas', 'Archivo CSS externo', 'Eliminar duplicados', 'O.K.', 'ERR', 'Stream', '(mostrar como)', 'archivos', 'discos', '%1d %2h %3m %4s', 'General', 'Personalizar', 'Manejo de archivos', 'Seleccione " ? " para obtener ayuda', 'Sincronizaciуn automбtica con la base de datos', 'Enviar la extensiуn del archivo', 'Permitir streams no autorizados', 'Incluir encabezados', 'Javascript externo', 'Pбgina de inicio', 'Show Keyteq gives you part', 'Show upgrade part', 'Mostrar estadнsticas', 'Write ID3v2 with stream', 'Activar registro de usuarios', 'Tipos de archivos', 'Si', 'No', 'Extensiуn', 'MIME', 'Incluir en M3U', 'editar lista de tipos de archivo', 'їEsta Seguro?', 'Comprobaciуn de archivos', 'Reproducir al azar', 'Modo', 'Lista de resproducciуn', 'Ninguno, directamente', 'Mis favoritos', 'No se encontraron hits', 'Hits de todo el tiempo', 'Orden', 'їActivar LAME?', 'Desactivado', 'їPermitir el uso de LAME?', 'Correo Electronico', 'їPermitir el envio de archivos por email?', 'servidor SMTP', 'puerto del servidor SMTP', 'Enviar email a', 'Mensaje', 'Enviar', 'ЎEmail enviado!', 'Activar el agregar archivos', 'Agregar un directorio', 'Activar mp3mail', 'Agregar', 'ЎArchivo agregado!', 'ЎNo se pudo agregar el archivo!', 'ЎDebe activar las cookies para entrar!', 'Periodo', 'siempre', 'esta semana', 'este mes', 'el mes anterior', 'hits', 'Comando LAME', 'Mostrar la carбtula del disco', 'Archivos del disco', 'Cambiar el tamaсo de las imбgenes del disco', 'Altura del disco', 'Ancho del disco', 'Mйtodo para enviar el email', 'Directo', 'Pear', 'ЎEspere!', 'ЎPor favor, escriba una direcciуn de correo electronico vбlida en las opciones!', 'їLista de reproducciуn integras?', 'їMostrar el disco para el URL?', 'URL del disco', 'ЎNo se pudo enviar!', 'ЎUsuario agregado!', 'Creador de archivos', 'El archivo se ha borrado', 'ЎUsuario actualizado!', 'Music match', '%1 entradas filtradas', 'Registrar el acceso', 'Visible', 'Archivado', 'Boletнn', 'Entrados %1 por %2', 'mбs', 'Publicar', '%1MB', '%1KB', '%1 bytes', 'Recursivo', 'Anterior', 'Siguiente', 'Ir a la pбgina %1', 'Pбgina:', 'Nunca se ha reproducido', 'Aprobar los registros manualmente', 'Pendiente', 'Activar', 'Todos los campos marcados con " * " son obligatorios', 'Su cuenta serб verificada y (de ser apropiado) activada manualmente', 'Ъltimas reproducciones', 'Recordarme', 'Estilo', 'Buscar', 'Introduzca las rutas en las que se va a buscar', 'їUtilizar los seleccionados?', 'Tiempo de pista min/max', 'Minutos', 'm3u', 'asx (WMA)', 'Si la actualizaciуn se detiene, pulsar aquн: %1', 'їSeguir enlaces?', 'Plantilla de archivo', 'Activar seguridad URL', 'Subir lista blanca', 'Tipo de archivo no permitido', 'ЎLista de reproducciуn vacнa!', 'Letras', 'Letras URL', 'їMostrar enlace de las letras?', 'їo?', 'NOmbre o contraseсa desconocida', 'Max tamaсo transferencia: %1', 'їAbrir RSS pъblico?');

$klang[6] = array("Portuguese", "ISO-8859-1", "Portuguкs", "este й popular", "Este й novo", "Busca", "(apenas %1 encontrado)", "seg", "Resultados da busca: '%1'", "encontrado", "Nenhum", "atualizar opзхes da busca na base de dados ", "Apagar entradas sem uso? ", "Reconstruir ID3?",  "Modo Debug?", "Atualizar", "Cancelar", "Atualizar busca no banco de dados", "Encontrados %1 arquivos.", "Nгo foi possнvel determinar este arquivo: %1, descartado", "Install %1 - Atualizar: %2, escanear:", "Escanear:", "Falha na busca: %1", "Nгo foi possнvel ler este arquivo: %1. Descartado.", "Removido: %1",  "Inserido %1, atualizado %2, apagado %2, onde %4, falhou em %5, descartado por %6, arquivos - %7 seg - %8 marcado para ser deletado", "Finalizado", "Fechar", "Nгo foi possнvel encontrar arquivos aqui: \"%1\"", "Logon kPlaylist", "Lista de бlbum por artista: %1", "Populares %1", "Nenhuma mъsica selecionada. Lista nгo atualizada.", "Lista atualizada!", "Voltar", "Lista atualizada",  "Lembre-se de atualizar a pбgina.", "login:", "senha:", "Atenзгo! Este nгo й um site restrito. Todas as aзхes sгo monitoradas.", "Login", "SSL necessбrio para entrar.", "Tocar", "Apagar", "Compartilhado", "Salvar", "Lista de controlhe: \"%1\" - %2 tнtulos",  "Editor", "Visualizador", "Selecionar", "Seq", "Status", "Info", "Del", "Nome", "Totais", "Erro", "Aзгo selecionada:", "Sequкncia", "editar lista", "Apagar esta entrada", "adicionar lista", "Nome:", "Criar", "Tocar:", "Arquivo", "Бlbum", "Todos", "Selecionado",  "adicionar", "tocar", "editar", "novo", "Selecionar", "Controle", "Lista:", "Selecionar nъmero", "Keyteq oferece:", "(verificar atualizaзгo)", "Pбgina incial", "apenas id3", "бlbum", "tнtulo", "artista", "Selecionar бlbum por artista", "ver", "Listas compartilhadas", "Usuбrios", "Controle de administrador", "Este й novo", "Este й popular", "Logout", "Opзхes", "Verificar", "Meu", "editar usuбrio", "novo usuбrio", "Nome completo", "Login", "Mudar senha?", "Senha", "Comentбrio", "Nнvel de acesso", "Ligado", "Desligado", "Apagar usuбrio", "Desconectar usuбrio", "Atualizar", "Novo usuбrio", "apagar", "desconectar", "Utilizar opзгo EXTM3U?", "Mostrar quantos arquivos (popular/novo)",  "Mбximo de arquivos encontrados", "Restaurar", "Abrir diretуrio", "Para o diretуrio: %1", "Download", "Subir um nнvel", "Para o diretуrio principal", "Verificar atualizaзхes", "usuбrios", "Linguagem", "opзхes", "Carregado", "Aleatуrio", "Configuraзхes", "Diretуrio base", "Local de stream", "Linguagem padrгo", "Sistema Windows", "Requer HTTPS", "Permitir busca", "Permitir download", "Sessгo expirou",  "Falha na tentativa de login", "Aguarde - buscando a lista de arquivos", "Lista nгo pode ser adicionada!", "Admin", "Inнcio de uma sessгo com o HTTPS a mudar");

$klang[7] = array('Finnish', 'ISO-8859-1', 'Suomi', 'Suosituimmat', 'Uusimmat', 'Etsi', '(pelkдstддn %1 nдytetддn)', 'sek', 'Haku-tulokset: \'%1\'', 'lцytyi', 'Tyhjд.', 'pдivitд hakutietokannan asetukset', 'Poista kдyttдmдttцmдt tiedot?', 'Uudelleenrakenna ID3?', 'Debug-moodi?', 'Pдivitд', 'Peruuta', 'pдivitд hakutietokanta', 'Lцytyi %1 tiedostoa', 'Ei voinut mддrittдд: %1, skipattu.', 'Install %1 - Pдivitд: %1,  tarkistus:', 'Tarkistus:', 'Epдonnistui - haku: %1', 'Ei voinut lukea tдtд tiedostoa: %1. Skipattu.', 'Poistettu: %1', 'Syцtetty %1, pдivitetty %2, poistettu %3, missд %4 epдonnistui ja %5 skipattiin %6 tiedostosta - %7 sekuntia - %8 merkitty poistettavaksi', 'Valmis', 'Sulje', 'Mikддn ei vastannut: %1', 'kPlaylist Kirjautuminen', 'Albumilista artistille: %1', 'Pikavalinta: %1', 'Ei valittuina mitддn. Soittolistaa ei pдivitetty', 'Soittolista pдivitetty!', 'Takaisin', 'Soittolista lisдtty!', 'Muista pдivittдд sivu.', 'tunnus', 'salasana:', 'Huomautus! Tдmд ei ole julkinen sivu. Kaikki teot kirjataan ylцs', 'Kirjaudu', 'SSL vaaditaan kirjautumiseen.', 'Soita', 'Poista', 'Jaettu:', 'Tallenna', 'Hallitse soittolistaa: \'%1\' - %2 nimet', 'Muokkain', 'Selain', 'Valitse', 'Jдrj.', 'Tila', 'Info', 'Poista', 'Nimi', 'Yhteensд:', 'Virhe', 'Toiminto valitussa:', 'Jдrjestys:', 'muokkaa soittolistaa', 'Poista tдmд tulos', 'lisдд soittolista', 'Nimi:', 'Luo', 'Soita', 'Tiedosto', 'Albumi', 'Kaikki', 'Valitut', 'lisдд', 'soita', 'muokkaa', 'uusi', 'Valitse:', 'Hallinta:', 'Soittolista', 'Pikavalinta numero', 'Keyteqin tuote:', '(tarkista pдivityksien varalta)', 'Kotisivu', 'ainoastaan id3', 'albumi', 'biisi', 'artisti', 'Albumit artistin mukaan', 'katso', 'Jaetut soittolistat', 'Kдyttдjдt', 'Yllдpito', 'Mitд uutta', 'Suosituimmat', 'Kirjaudu ulos', 'Asetukset', 'Tarkasta', 'Oma', 'muokkaa kдyttдjдд', 'uusi kдyttдjд', 'Kokonimi', 'Kirjaudu', 'Vaihda salasana?', 'Salasana', 'Kommentti', 'Taso', 'On', 'Off', 'Poista kдyttдjд', 'Kirjaa ulos kдyttдjд', 'Pдivitд', 'Uusi kдyttдjд', 'poista', 'kirjaa ulos', 'Kдytд EXT3MU-toimintoa?', 'Nдytд kuinka monta tulosta (suosittu/uusi)', 'Maksimi haku tulokset', 'Resetoi', 'Avaa hakemisto', 'Mene hakemistoon: %1', 'Imuroi', 'Avaa ylдkansio', 'Mene pддhakemistoon', 'Tarkista pдivityksien varalta', 'kдyttдjдt', 'Kieli', 'asetukset', 'Bannattu', 'Shuffle', 'Asetukset', 'Perushakemisto', 'Streamin lдhde', 'Oletuskieli', 'Windows systeemi', 'Vaadi HTTPS (Salattu yhteys)', 'Salli etsiminen', 'Salli imurointi', 'Istunto pддttynyt', 'Ilmoita epдonnistuneet kirjautumisyritykset', 'Hetki. Haen tiedostolistaa', 'Soittolistaa ei voitu lisдtд', 'Yllдpitдjд', 'Kirjaudu HTTPS:llд vaihtaaksesi', 'Streaming moottori pддlle', 'Nimi', 'Artisti', 'Albumi', 'Kommentti', 'Vuosi', 'Raidan numero', 'Tyyppi', 'ei asetettu', 'Maksimi imurointinopeus (kbps)', 'Kдyttдjд', '%1 minuuttia - %2 biisiд', '%1 kilobittiд %2 minuuttia', 'Tyyppilista: %1', 'Mene', ' %1d %2h %3m soittoaika %4 tiedostoa %5 mt', 'Ei soitettavia fileitд', 'Salasana vaihdettu!', 'Rekisterцi', 'Tee valintasi', 'Mikд on pдivitys?', 'Ohje painamalla tдstд', 'Kдytд ulkoisia kuvia?', 'Ulkoisten kuvien polku', 'Nykyinen salasana', 'Nykyinen salasana ei natsaa!', 'Valitse pakkaaja', 'Pakkausta ei pystytty tekemддn', 'Todennдkцinen kopio: %1 - %2', 'Haluatko varmasti poistaa soittolistan?', 'Aakkosellinen', 'Shuffle', 'Jдrjestд', 'Alkuperдinen', 'Kдytд javascriptiд', 'Haluatko varmasti posistaa tдmдn kдyttдjдn?', 'Nдytд historia', 'historia', 'Riviд', 'Ulkopuolinen CSS tiedosto', 'Poista tuplat', 'OK', 'VIRHE', 'Stream', '(nдytд tyyppinд)', 'tiedostot', 'albumit', '%1d %2h %3m %4s ', 'Yleistд', 'Muokkaa', 'Tiedostonkдsittely', 'Klikkaa ? ohjeen nдyttдmiseksi.', 'Automaattinen tietokanta-synkronisaation', 'Lдhetд tiedostopддte', 'Salli kirjautumattomat streamit', 'Sisдllytд otsikot', 'Ulkopuolinen javascript', 'Kotisivu', 'Nдytд \'Keyteq toi sinulle\'-kohdan', 'Nдytд pдivitд kohta', 'Nдytд statistiikka', 'Kirjoita ID3v2 streamiin', 'Salli kдyttдjien rekisterцinti', 'Tiedostotyypit', 'Kyllд', 'Ei', 'Pддte', 'MIME', 'Sisдllytд M3U-tiedostoon', 'muokkaa tyyppiд', 'Varmasti?', 'Optimistinen tiedoston tarkastus', 'Arpoja', 'Toimintatila', 'Soittolista', 'Ei mitддn, suoraan', 'Omat suosikit', 'Osumia ei lцytynyt', 'Kaikkien aikojen parhaat', 'Jдrjestys', 'LAME tuki pддlle', 'Pois', 'Salli LAMEn kдyttц?', 'Sдhkцposti', 'Salli tiedoston sдhkцpostitus?', 'SMTP palvelin', 'SMTP portti', 'Lдhetд sдhkцposti', 'Viesti', 'Lдhetд', 'Viesti lдhetetty!', 'Aktivoi tiedoston lisдys', 'Tiedoston lisдys kansio', 'Aktivoi mp3mail', 'Lisдд tiedosto', 'Tiedosto lisдtty', 'Tiedoston lisдys ei onnistunut!', 'Evдsteiden on oltava pддllд, jotta sisддnkirjautuminen onnistuisi!', 'Ajanjakso', 'koskaan', 'tдllд viikolla', 'tдssд kuussa', 'edellisessд kuussa', 'osumia', 'LAME komento', 'Nдytд albumin kansi', 'Albumin tiedostot', 'Sovita albumin kuvien koko', 'Albumin korkeus', 'Albumin leveys', 'Postitusmuoto', 'Suora', 'PEAR', 'Odota', 'Anna oikea sдhkцpostiosoite asetuksissa!', 'Soittolistat sisennettyinд?', 'Nдytд albumi URLista?', 'Albumin URL', 'Lдhetys ei onnistunut!', 'Kдyttдjд lisдtty!', 'Arkiston luonti', 'Arkisto on poistettu.', 'Kдyttдjдn tiedot pдivitetty!', 'Musiikin vertailu');

$klang[8] = array('Danish', 'ISO-8859-1', 'Dansk', 'Hvad er hot', 'Hvad er nyt', 'Sшg', '(kun %1 vist)', 'sek', 'Sшgeresultater: \'%1\'', 'fundet', 'Ingen.', 'indstillinger for opdatering af sшgebasen', 'Fjern slettede sange?', 'Genopbyg ID3?', 'Fejlsшgnings mode', 'Opdater', 'Annuller', 'opdater sшgebasen', '%1 filer fundet.', 'Kunne ikke bestemme filtypen pе: %1. Droppet.', 'Installerer: %1 - Opdaterer: %2, scanner: ', 'Scan:', 'Fejl - forespшrgsel: %1', 'Kunne ikke lжse: %1. Droppet.', 'Fjernet: %1', 'Der er indsat %1, opdateret %2, slettet %3, hvor %4 fejlede og %5 blev droppet. Ialt %6 filer - %7 sekunder - %8 markeret til sletning.', 'Gennemfшrt', 'Luk', 'Ingen filer fundet pе: "%1"', 'kPlaylist login', 'Albumliste for kunstner: %1', 'Hurtigvalg %1', 'Ingen numre valgt. Playlist ikke opdateret.', 'Playlist opdateret!', 'Tilbage', 'Playlist tilfшjet!', 'Husk at opdatere siden.', 'brugernavn:', 'adgangskode:', 'Bemжrk! Dette er en privat webside. Alt logges.', 'Log pе', 'SSL er krжvet for at logge pе.', 'Afspil', 'Slet', 'Delt:', 'Gem', 'Kontroller playlisten: "%1" - %2 titler', 'Redigering', 'Vis', 'Vжlg', 'Sekvens', 'Status', 'Info', 'Slet', 'Navn', 'Total:', 'Fejl', 'Handling pе valgte:', 'Sekvens:', 'rediger playlist', 'Slet dette nummer', 'tilfшj playlist', 'Navn:', 'Opret', 'Afspil:', 'Fil', 'Album', 'Alle', 'Valgte', 'tilfшj', 'afspil', 'rediger', 'ny', 'Vжlg:', 'Afspil:', 'Playlist:', 'Numerisk hurtigvalg', 'Keyteq giver dig:', '(se efter opdateringer)', 'Webside', 'kun ID3', 'album', 'titel', 'kunstner', 'Hurtigvalg album fra kunstner', 'vis', 'Delte playlister', 'Brugere', 'Admin kontrolpanel', 'Hvad er nyt', 'Hvad er hot', 'Log ud', 'Indstillinger', 'Vis', 'Mig', 'rediger bruger', 'ny bruger', 'Fulde navn', 'Brugernavn', 'Жndre adgangskode?', 'Adgangskode', 'Kommentar', 'Adgangsniveau', 'Online', 'Offline', 'Slet bruger', 'Log bruger ud', 'Opdater', 'Ny bruger', 'slet', 'logud', 'Anvend EXTM3U?', 'Vis rжkker (hotte/nye)', 'Max. antal i sшgerжkker', 'Nulstil', 'Еbn mappe', 'Gе til mappe: %1', 'Download', 'Gе et trin tilbage', 'Gе til roden.', 'Se efter opdateringer', 'brugere', 'Sprog', 'indstillinger', 'Afvis', 'Tilfжldig:', 'Indstillinger', 'Basemappe', 'Stream-lokation', 'Standardsprog', 'Windows understшttelse', 'HTTPS krжves', 'Tillad sшgning', 'Tillad download', 'Sessionsvarighed', 'Rapporter fejlagtige loginforsшg', 'Vent - skaber filliste', 'Playlisten kunne ikke tilfшjes', 'Admin', 'Log ind med HTTPS for at жndre denne indstilling!', 'Aktiver streaming', 'Titel', 'Kunstner', 'Album', 'Kommentar', 'Еr', 'Nummer', 'Genre', 'ukendt', 'Max. download hastighed (kbps)', 'Bruger', '%1 minutter - %2 titler', '%1 kbit %2 minutter', 'Genreliste: %1', 'Vжlg', 'Spilletid: %1d %2h %3m - %4 filer %5 mb', 'Intet relevant her.', 'Adgangskoden er жndret!', 'Ny bruger', 'Foretag venligst en markering!', 'Hvad er en opdatering?', 'Klik her for hjжlp', 'Brug eksterne billeder', 'Sti til eksterne billeder', 'Nuvжrende adgangskode', 'Den nuvжrende adgangskode var forkert!', 'Foretrukne arkivtype', 'Arkivet kunne ikke genereres', 'Sandsynlig dublet fundet: "%1" - "%2"', 'Vil du virkelig slette playlisten?', 'Alfabetisk', 'Vilkеrlig', 'Sorter', 'Original', 'Brug javascript', 'Vil du virkelig slette brugeren?', 'Vis historie', 'historie', 'Rжkker', 'Ekstern CSS-fil', 'Fjern dubletter', 'OK', 'FEJL', 'Stream', '(vis som)', 'filer', 'albums', '%1d %2t %3m %4s', 'Generelt', 'Tilpasning', 'Filhеndtering', 'Klik pе ? for hjжlp.', 'Automatisk sшgebase synkronisering', 'Medsend filefternavn', 'Tillad uautoriseret streams', 'Inkluder headere', 'Ekstern javascript', 'Hjemmeside', 'Vis Keyteq giver dig', 'Vis opdateringsdelen', 'Vis statistikker', 'Send ID3v2 med stream', 'Tillad nyregistrering af brugere', 'Filtyper', 'Ja', 'Nej', 'Filefternavn', 'MIME', 'Inkluder i M3U', 'rediger filtype', 'Er du sikker?', 'Optimistisk filcheck', 'Vilkеrlig afspilning', 'Mode', 'Playlist', 'Ingen, direkte', 'Mine favoritter', 'Ingen hits fundet', 'Alle hits', 'Rжkkefшlge', 'LAME understшttelse?', 'Slukket', 'Tillad LAME?', 'Email', 'Tillad sending af filer?', 'SMTP server', 'SMTP port', 'Mail til', 'Besked', 'Send', 'Mail sendt!', 'Tillad upload', 'Uploadmappe', 'Tillad mp3mail', 'Upload', 'Fil uploadet!', 'Filen kunne ikke uploades!', 'Cookies er pеkrжvet!', 'Periode', 'nogensinde', 'denne uge', 'denne mеned', 'sidste mеned', 'hits', 'LAME kommando', 'Vis albumcovers', 'Album filer', 'Жndre cover stшrrelse', 'Cover hшjde', 'Cover bredde', 'Mail metode', 'Direkte', 'Pear', 'Vent!', 'Udfyld en gyldig emailadresse i indstillingerne!', 'Playlist inline?', 'Vis album fra URL?', 'Album URL', 'Kunne ikke sende!', 'Bruger tilfшjet!', 'Arkiv skaber', 'Arkivet er slettet.', 'Brugeren opdateret!', 'Musik match', '%1 gennemsшgt', 'Log adgang', 'Vises', 'Arkiveret', 'Opslagstavle', 'Skrevet %1 af %2', 'mere', 'Udgiv', '%1 mb', '%1 kb', '%1 bytes', 'Rekursivt', 'Forrige', 'Nжste', 'Gе til side %1', 'Side:', 'Aldrig afspillet', 'Manuel godkendelse af nye brugere', 'Under behandling', 'aktiver', 'Alle felter markeret med * er obligatoriske', 'Din konto vil blive inspiceret og godkendt manuelt.', 'Seneste afspilninger', 'husk mig', 'Stil', 'find', 'Sti at sшge efter', 'Benyt valgte?', 'Track tid min/max', 'Minutter', 'm3u', 'asx (WMA)', 'Hvis opdatingen stopper, klik her: %1', 'Fшlg symlinks?', 'Fil template', 'Aktiver URL sikkerhed', 'Upload whitelist', 'Filtype ikke tilladt.', 'Playlisten er tom!', 'Sangtekst', 'Sangtekst URL', 'Vis link til sangtekster?', '(eller?)', 'Ukendt brugernavn eller adgangskode', 'Max upload stшrrelse: %1', 'Aktiver offentlig RSS feed?', 'Sжt et password', 'Navn og login mangler', 'Brugernavnet findes allerede!', 'Drop adminadgang for denne session?', 'Henter database rжkker: %1/%2', 'Kunne ikke finde "%1", er filen slettet?', 'Fra/til dato (DDMMЕЕ)', 'Fejl i felt(er), prшv igen.', 'Maksimal tekst lжngde');

$klang[9] = array('Russian', 'Windows-1251', 'Русский', 'Популярные', 'Новые', 'Поиск', '(только %1 показан)', 'сек.', 'Результат поиска: "%1"', 'найдено', 'Ни один.', 'обновить настройки поиска базы данных', 'Удалить неиспользуемые записи в базе?', 'Пересоздать ID3?', 'Режим отладки?', 'Обновить базу', 'Отмена', 'Oбновить базу данных поиска', 'Найдено %1 файлов.', 'Не могу определить этот файл: %1, пропускаю.', 'Добавлено: %1 - Обновлено: %2, сканируется: ', 'Сканировать: ', 'Ошибка - запрос: %1', 'Не могу прочитать этот файл: %1. Пропущено.', 'Удалено: %1', 'Добавлено %1, обновлено %2, удалено %3, из них %4 ошибки и %5 пропущено. Всего %6 файлов - %7 сек. - %8 отмеченных для удаления.', 'Готов', 'Закрыть', 'Не найдено ни одного файла: "%1"', 'kPlaylist. Вход', 'Список альбомов для исполнителя: %1', 'Быстрый выбор %1', 'Не выбрано ни одной композиции. Плейлист не обновлён.', 'Плейлист обновлён!', 'Назад', 'Плейлист добавлен!', 'Не забудьте перезагрузить страницу.', 'Логин:', 'Пароль:', 'Все действия пользователей записываются.', 'Войти', 'Для входа необходим SSL', 'Проиграть', 'Удалить', 'Совместно используемый: ', 'Сохранить', 'Управление плейлистом: "%1" - %2 композиции', 'Редактировать', 'Просмотр', 'Выбрать', 'Послед.', 'Состояние', 'Информация', 'Удал.', 'Имя', 'Итоги:', 'Ошибка', 'Операции с выборкой: ', 'Последовательность:', 'редактировать плейлист', 'Удалить эту позицию', 'добавить плейлист', 'Имя:', 'Создать', 'Проиграть: ', 'Файл', 'Альбом', 'Все', 'Выбранные', 'Добавить', 'Проиграть', 'Редактировать', 'Новый', 'Выбрать:', 'Управление проигрыванием: ', 'Плейлист: ', 'Быстрый выбор по числу', 'Keyteq помогает вам:', '(проверить обновления)', 'Домашняя страница', 'только в  id3', 'альбом', 'название', 'исполнитель', 'Альбомы по алфавиту', 'просмотр', 'Общие плейлисты', 'Пользователи', 'Администрирование', 'Новинки', 'Популярные', 'Выход', 'Настройки', 'Показать', 'Мои настройки', 'Редактировать пользователя', 'Новый пользователь', 'Полное имя', 'Логин', 'Изменить пароль?', 'Пароль', 'Комментарий', 'Уровень доступа', 'Вкл', 'Выкл', 'Удалить пользователя', 'Отключить пользователя', 'Обновить', 'Новый пользователь', 'Удал.', 'Выход', 'Использовать EXTM3U?', 'Количество выводимых строк (популярные/новые)', 'Количество выводимых строк при поиске', 'Сброс', 'Войти в папку', 'Перейти в папку: %1', 'Скачать', 'Вверх на один уровень', 'В начало', 'Проверить обновления', 'пользователей', 'Язык', 'настройки', 'Загружено', 'Случайный порядок:', 'Настройки', 'Путь к музыкальному архиву', 'Путь для потокового вещания', 'Язык по умолчанию', 'Работа под Windows', 'Необходим HTTPS', 'Разрешить проматывать', 'Разрешить скачивать', 'Таймаут для сессии', 'Сообщать о неудачных попытках входа', 'Подождите - разбираюсь со списком файлов', 'Плейлист не может быть добавлен!', 'Администрирование', 'Вход только через HTTPS', 'Включить встроенную систему потокового вещания', 'Название', 'Исполнитель', 'Альбом', 'Комментарий', 'Год', 'Трек', 'Стиль', 'не установлен', 'Максимальная скорость скачивания (kbps)', 'Пользователь', '%1 мин. - %2 треков', '%1 kbit %2 мин.', 'Список стилей: %1', 'Выполнить', 'В базе %4 файлов общим объёмом %5 Мб.<br> Общее время прослушивания: %1 дней %2 часов %3 минут.', 'Музыкальные файлы отсутствуют.', 'Пароль изменен', 'Регистрация', 'Выберите хотя бы один файл', 'Что такое обновление?', 'Щёлкните здесь для подсказки', 'Использовать внешние картинки?', 'Путь к картинкам', 'Текущий пароль', 'Введённый пароль не совпадает с текущим!', 'Использовать архиватор', 'Невозможно создать архив', 'Найдены возможные дубликаты файлов:  "%1" "%2"', 'Вы действительно хотите удалить плейлист?', 'По алфавиту', 'Случайно', 'Сортировать', 'Как в оригинале', 'Использовать JavaScript', 'Вы действительно хотите удалить этого пользователя?', 'Просмотр истории', 'История', 'Строки', 'Файл CSS', 'Удалить дубликаты', 'OK', 'ERR', 'Поток', '(показать как)', 'файлы', 'альбомы', '%1 дней %2 часов %3 минут %4 секунд', 'Общие', 'Интерфейс', 'Работа с файлами', 'Щёлкните на ? для подсказки.', 'Автоматическое обновление базы данных', 'Отсылать расширение файла при передаче', 'Разрешить передачу потокового звука без авторизации', 'Включить заголовки', 'Внешний JavaScript', 'Адрес вашего сайта', 'Показывать фразу "Keyteq помогает вам"', 'Показывать фразу "Проверить обновления"', 'Показывать статистику', 'Добавлять тэг ID3v2 в поток', 'Разрешить регистрацию пользователей', 'Типы файлов', 'Да', 'Нет', 'Расширение', 'MIME', 'Включить в M3U', 'Редактировать тип файла', 'Уверены?', 'Точная проверка файлов', 'Случайная выборка', 'Режим выборки', 'Добавить в плейлист', 'Не добавлять', 'Из моего избранного', 'Популярные композиции не найдены', 'Самые популярные', 'Сортировка', 'Разрешить поддержку LAME?', 'Выключено', 'Разрешить использование LAME?', 'Email', 'Разрешить отправку файлов по Email\'у?', 'SMTP сервер', 'SMTP порт', 'Получатель', 'Сообщение', 'Отослать', 'Письмо отослано!', 'Активироватm загрузку на сервер', 'Директория для загрузки', 'Активировать mp3почту', 'Загрузить', 'Файл загружен!', 'Файл не загружен!', 'Куки должны быть вклучены', 'Промежуток', 'всего', 'на этой недели', 'в этом месяце', 'в прошлом месяце', 'хиты', 'LAME команда', 'Показывать обложки альбомов', 'Обложки альбомов', 'Изменять размеры обложек', 'Высота обложки', 'Ширина обложки', 'Способ отсылки почты', 'Прямой', 'Pear', 'Ждать', 'Пожалуйста введите в опциях правильный адресс почтового ящика!', 'Плейлист "inline"', 'Показывать обложки с интернет-адресса?', 'Адресс для обложек', 'Не отослан!', 'Пользователь добавлен!', 'Создание архива', 'Архив удален.', 'Пользователь зарегистрирован', 'Музыка', '%1 отфильтрован', 'Лог доступа', 'Обозрение', 'В архиве', 'Доска объявлений', 'Вход %1 до %2', 'Смотреть все', 'Публикация', '%1 МБ', '%1 КБ', '%1 Б', 'Рекурсивный', 'Предыдущий', 'Следующий', 'На страницу %1', 'Страница:', 'Никогда не проигровался', 'Записи утверждать в ручную', 'Незаконченный', 'активный', 'Поля отмеченные звёздочкой (*) обязательны', 'Ваш аккаунт будет проверен и активизирован позже.', 'Последние потоки', 'запомнить меня', 'Стиль', 'найти', 'Введите путь для поиска', 'Использовать выбранные ?', 'Время трека максимальный/минимальный', 'Минут', 'm3u', 'asx (WMA)', 'Если обновление прекратится, нажмите здесь: %1', 'Пройти по подссылкам ?', 'Шаблон файла', 'Разрешить безопасность URL', 'Загрузить рекомендательный список', 'Не разрешённый тип файла.', 'Плейлист пуст !', 'Искать текст песни на Lyrics.com', 'Ссылка на Lyrics', 'Показывать Lyrics ?', '(или?)', 'Не правильный пользователь или пароль', 'Максимально  загружаемый размер: %1', 'Открыть RSS?', 'Пожалуйста введите пароль!', 'Необходимы Имя и Логин', 'Такой пользователь уже зарегестрирован!', 'Убрать доступ администратора к сессии?', 'Достаю записи из базы: %1/%2', 'Не могу найти "%1", файл удалён?', 'Дата с/до (ддммгг)', 'Ошибка при заполнении поля(-ей), попробуйте ещё раз', 'Максимальная длина строки');

$klang[10]  = array('Swiss German', 'ISO-8859-15', 'Schwiizerdьtsch', 'Wasch geil', 'Wasch neu', 'Wo isch das Zььg', '(Gseesch nur  %1)', 'sek', 'Suechergebnis: \'%1\'', 'gfundд', 'keini', 'pass das datebank-suech-zььg aa', 'nцd benutzte seich i de db kickд ?', 'ID3 erneuerд?', 'Dibцg-Modus?', 'Update', 'Abbrдche', 'Suech-DB update', '%1 Files gfundд', 'Bin bi dem File nцd druus cho: %1. Has usglaa.', 'Inschtalliert:%1 - Draa umebaschtlet: %2, abchecke:', 'scдn:', 'Problem bi de Abfrag: %1', 'Han glaub es File verhьeneret: %1. Ussglaa..', 'Weggnoo: %1', 'inetaa: %1, umebaschtlet: %2, weggnoo: %3, %4 hдnd nцd gfunzt und %5 hani ussglaa; %6 dateie insgesamt - %7 sekunde - %8 hani markiert zum abtschьsse.', 'Schnornig.', 'Zuemachд.', 'Da hдtts kei Dateie: "%1"', 'KPlaylist Login', 'Albumlischte fьr Interpret: %1', 'Churzwahl %1', 'Kein Song usgwдhlt. Playlischte nцd aktualisiert.', 'Playlischte aktualisiert.', 'Zrugg', 'Playlischte zuegfьegt!', 'Nomal lade das zььg.', 'Login:', 'Passwort:', 'Achtung! Dasch privat da zььg. Jede seich gitt eis uf de Deckel!', 'Login', 'Bruchsch SSL zum inechoo', 'Abschpile', 'Lцsche', 'Die wommer zдme hдnd:', 'Seivд', 'A de Playlischte umebaschtle: "%1" - %2 Titel', 'Editor', 'Aazeiger', 'Uswдhle', 'Nummerд', 'Schtatus', 'Info', 'Abtschьsse', 'Namд', 'Zдmezellt', 'Schцne seich', 'Das machemer mit dene wo uusgwдhlt sind', 'Reiefolg', 'a de Playlischte umebaschtle', 'De Iitrag useschmeisse', 'Playlischte dezuetue', 'Namд:', 'Mache', 'Abschpile:', 'Datei', 'Album', 'Ali', 'die Uusgwдhlte', 'Dezue tue', 'Abschpile', 'draa umebaschtle', 'neu', 'Uswдhle:', 'Abschpile:', 'Playlischte:', 'Churzwahl numerisch', 'Keyteq prдsentiert eu:', '(Suche nacheme neue versiцnli)', 'Houmpeitsch', 'Nume id3 TдgZ', 'Album', 'Titel', 'Interpret', 'Churzwahl Album nach Interpret', 'Aasicht', 'Playlischtene, wommer zдme hдnd', 'Benutzer', 'Admin kontrollд', 'Wasch neu', 'Wasch geil', 'Und tschьss', 'Iischtellige', 'Abtschдgge', 'Mini', 'Benutzer abдndere', 'Neue Benutzer', 'De ganz Name', 'Login', 'Passwort abдndere?', 'Passwort', 'Sдnf dezue gee', 'Wie mдchtig isch de Typ', 'Aagschtellt', 'Abgschtellt', 'Benutzer abtschьsse', 'Uuslogge', 'Erneuerд', 'Neue Benutzer', 'Lцsche', 'Uuslogge', 'Sцli das EXTM3U zььg bruuche?', 'Wivill ziile aazeige (geil/neu)', 'Max. Ziile bi Suechergebnis', 'Reset', 'Ordner ufmache', 'Gang zum Ordner: %1', 'Abesuuge', 'Ein Ordner ufe', 'Is Grundverzeichnis', 'Mal luege цbs es Update gitt', 'Benutzer', 'Spraach', 'Opzione', 'Aaghalte', 'Mischle:', 'Iischtellige', 'Hauptverzeichnis', 'Stream location', 'Standardspraach', 'Es windoof-system', 'bruucht HTTPS', 'dцrf me sueche', 'dцrf me suuge', 'session isch abgloffe', 'sдg mer, wenn eine sis PW verhдngt', 'momдntli, mues schnдll go d\'files lдse', 'han die blццd playlist nцd chцne mache!', 'Admin', 'Login mit HTTPS zum дndere', 'streaming maschine ihschalte', 'Titel', 'Artischt', 'Album', 'Kommentar', 'Johr', 'Track', 'Stiil', 'nцd', 'Max abesuug rate (kbps)', 'Benutzer', '%1 min - %2 titel', '%1 kbit %2 min', 'Stiil Lischte: %1', 'Gang', 'Spiilziit: %1d %2h %3m : %4 dateie : %5 mb', 'Da hдtts kei wichtigi sache.', 'Passwort gдnderet', 'Regischtriere', 'Wдhl bitte цppis us!', 'Was isch update?', 'da klicke fьr hilf', 'externi bilder bruche?', 'externe bilder ort', 'jetztigs passwort', 'jetztigs passwort stimmt nцd ьberih!', 'bevorzugte archivierer', 'has archiv nцd chцne erstelle!', 'mцglichs doppel gfunde: "%1" "%2"', 'playliste wьrkli lцsche?', 'Alphabetisch', 'Durenand', 'Sortiere', 'Originau', 'Bruch Javascript', 'Bisch sicher das dд User willsch lцsche?', 'Zeig d\'history', 'history', 'Reihe', 'Externs CSS file', 'Entfern doppleti', 'OK', 'ERR', 'Stream', '(zeig als)', 'dateie', 'albene', '%1d %2h %3m %4s', 'Generel', 'individualisierд', 'Dateihandling', 'Clickuff? fьr hilf', 'Automatische datebank synch', 'Schick d\'datei erwiiterig', 'unberдchtige stream erlaube', 'Adresschopf ihbinde', 'Externs Javascript', 'Homepeitsch', 'Show Keyteq gives you part', 'Zeig de upgrade teil', 'Zeig d\'statistikд', 'Schriib ID3v2 mit em Stream', 'Registrierig Ihschalte', 'Datei typд', 'Jo', 'Nei', 'Erwiiterig', 'MIME', 'in M3U Ufnдh', 'dateityp ahpasse', 'Sicher?', 'Optimistische dateicheg', 'Zuefallsgenerator', 'Modus', 'Playlischte', 'Kei, dirдkt', 'Mini favoriitд', 'Han kei trдffer gfundд', 'Absolut-hits', 'Sortierд', 'LAME support Ihschalte?', 'Usgschaltд', 'LAME benutzig erlaube?', 'Email', 'datei z\'maile erlaube?', 'SMTP sцrver', 'SMTP port', 'Mail ah', 'Nachricht', 'Schickд', 'Mail gschickt!', 'ufelade aktivierд', 'Ufelad verzeichnis', 'mp3mail Aktivierд', 'Ufelade', 'Datei ufeglade!', 'Datei nцd chцne ufelade!', 'Du muesch d\'Cookies ihschalte zum ahmдlde!', 'Ziitruum', 'immer', 'die wuche', 'dд monдt', 'letscht monдt', 'trдffer', 'LAME comando', 'Zeig album cover', 'Album dateiд', 'Grцssi vo de Album-bilder ahpassд', 'Album hцchi', 'Album breiti', 'Mail methodд', 'Dirдkt', 'Pear', 'Warte!', 'Bitte trдg en richtigi e-mail adrдsse i de optzione ih!', 'Playlischtд inline?', 'Zeigs alum vom URL?', 'Album URL', 'Nцd chцne Schicke!', 'Benutzer dezue tah!', 'Archiv erzьger', 'Archiv isch glцscht.', 'Benutzer updaitдt', 'Musig-trдffer', '%1 entries filtered', 'Log d\'zuegriff', 'Sichtbar', 'Archiviert', 'Bьltдh', 'Gschriibд %1 vo %2', 'meh', 'Verцffentlichд', '%1 mb', '%1 kb', '%1 bytes', 'Rekursiv', 'Vorhдrig', 'Nцchscht', 'Gang zu de siitд %1', 'Sitд:', 'Niд gspillt', 'Registriдrigд manuel bestдtigд', 'usstehend', 'aktivierд', 'Alli Fдlder mit em д  * sind zwingend', 'Diin account wird prьeft und dдnn manuell aktiviert', 'Letschti streams', 'ah mich errinдrд', 'Stiil', 'findд', 'suechpfдd ihtrдge', 'ahgwдhlte bruchд', 'Titel ziit min/max', 'Minutд', 'm3u', 'asx (WMA)', 'wenn dд update ahaltet, da klickд: %1', 'symlinks folgд?', 'Datei presentations Vorlaag', 'URL sicherheit ihschalte', 'Ufelad whitelist', 'Datei typ isch nцd erlaubt', 'Playlischtд isch leer', 'Lyrics', 'Lyrics URL', 'zeid dд lyrics link?', '(oder?)', 'Unbekannte benutzer oder passwort', 'Max ufelad grцssi: %1', 'йffentlich RSS feed ufmache?');

$klang[11]  = array('French', 'ISO-8859-15', 'Franзais', 'Populaire', 'Nouveautйs', 'Rechercher', '(seulement %1 visibles)', 'sec', 'Rйsultats de la recherche : \'%1\'', 'trouvй', 'Aucun', 'actualiser les options de la base de donnйes de recherche', '<b>Supprimer</b> les entrйes inutiles ?', 'Reconstruire <b>ID3</b> ?', 'Mode de dйbuggage ?', 'Actualiser', 'Annuler', 'Actualiser la base de donnйes de recherche', '%1 fichiers trouvйs', 'Ce fichier n\'a pas pu кtre dйterminй : %1, ignorй.', 'Installйs : %1 - Actualisйs : %2 - Scannйs : ', 'Scanner', 'Echec - Requкte : %1', 'Le fichier : %1 n\'a pas йtй trouvй. Passй.', 'Eliminйs : %1', 'Insйrй(s) :%1, Actualisйs %2, Supprimйs : %3 dont %4 йchouйs et %5 ignorйs parmi %6 fichiers - %7 sec. - %8 marquйs pour effacement.', 'Terminй', 'Fermer', 'Impossible de trouver des fichiers dans : "%1"', 'Nom d\'utilisateur KPlaylist', 'Liste des albums de l\'artiste : %1', 'Plйbiscitй %1', 'Aucune chanson sйlectionnйe. La liste n\'a pas йtй actualisйe.', 'Liste actualisйe avec succиs !', 'Prйcйdent', 'Liste ajoutйe !', 'Pensez а actualiser la page.', 'Nom d\'utilisateur :', 'Mot de passe :', 'Attention ! Ce site est privй, toute action est enregistrйe.', 'Se connecter !', 'SSL nйcessaire pour s\'identifier.', 'Lire', 'Effacer', 'Partagйe :', 'Enregistrer', 'Actions sur la liste : "%1" contenant %2 titres', 'Editeur', 'Viseur', 'Sйlectionner', 'N° piste', 'Status', 'Informations', 'Supprimer', 'Nom du fichier', 'Totaux :', '<b>Erreur</b>', 'Action а effectuer sur la selection', 'Liste :', 'йditer la liste', 'Supprimer cette entrйe', 'ajouter une liste', 'Titre :', 'Crйer', 'Lire :', 'Fichier', 'Album', 'Tous', 'Sйlectionnйs', 'ajouter', 'lire', 'йditer', 'nouveau', 'Sйlectionner :', 'Lire :', 'Liste :', 'Sйlection numйrique', 'Keyteq vous propose :', '(rechercher des mises а jour)', 'Accueil', 'seulement id3', 'album', 'titre', 'artiste', 'Accйder а un artiste', 'Voir', 'Listes partagйes', 'Utilisateurs', 'Console d\'administration', 'Nouveaux', 'Populaires', 'Dйconnecter', 'Options', 'Consulter les fichiers', 'Mon compte', 'йditer un utilisateur', 'nouvel utilisateur', 'Nom complet', 'Nom d\'utilisateur', 'Changer le mot de passe ?', 'Mot de passe', 'Commentaires', 'Niveau d\'accиs', 'On', 'Off', 'Supprimer l\'utilisateur', 'Dйconnecter l\'utilisateur', 'Actualiser', 'Nouvel utilisateur', 'supprimer', 'dйconnecter', 'Utiliser l\'option de EXTM3U ?', 'Montrer combien de lignes (populaires/nouveaux)', 'Rйsultat maximum de rйponses', 'RAZ', 'Ouvrir le rйpertoire', 'Aller dans le rйpertoire : %1', 'Tйlйcharger', 'Dossier parent', 'Aller au rйpertoire racine', 'Chercher les mises а jour', 'utilisateurs ', 'Langue', 'options', 'Dйsactiver le compte', 'Lecture alйatoire :', 'Config.', 'Chemin racine de la librairie musicale', 'Forcer l\'url du flux', 'Langue par dйfaut', 'Systиme de type Windows', 'HTTPS nйcessaire', 'Permettre la recherche', 'Permettre les tйlйchargements', 'Dйlai d\'expiration de la session', 'Rapport des tentatives de connexion йchouйes', 'Patientez - Analyse de la librairie', 'La liste n\'a pas pu кtre ajoutйe !', 'Admin', 'Connexion en HTTPS obligatoire', 'Activer le moteur de streaming', 'Titre', 'Artiste', 'Album', 'Commentaires', 'Annйe', 'N° piste', 'Genre', 'n/a', 'Taux de tйlйchargement Max (kbps)', 'Utilisateur', '%1 min - %2 titres', '%1 kbit %2 min', 'Liste des genres : %1', 'Go', 'Temps de lecture : %1 J %2 H %3 m, %4 fichiers %5 Mo', 'Aucune ressource correspondante', 'Mot de passe mis а jour !', 'Inscrivez-vous !', 'Faites une sйlection SVP !', 'Qu\'est ce que la mise а jour ?', 'Clickez ici pour l\'aide', 'Utiliser des images externes ?', 'Chemin vers les images externes', 'Mot de passe actuel', 'Mauvais mot de passe', 'Archiveur prйfйrй', 'Impossible de crйer l\'archive', 'Doublon probable : "%1" "%2"', 'Voulez-vous vraiment supprimer la liste ?', 'Alphabйtique', 'Alйatoire', 'Classer', 'Original', 'Utiliser Javascript', 'Voulez vous vraiment supprimer cet utilisateur ?', 'Voir l\'historique', 'historique', 'Lignes', 'Fichier CSS externe', 'Supprimer les doublons', 'OK', 'ERREUR', 'Flux', '(afficher par)', 'fichiers', 'albums', '%1J %2H %3m %4s', 'Principal', 'Personnalisation', 'Gestion de la librairie', 'Cliquer sur "?" pour afficher l\'aide', 'Synchronisation automatique de la base de donnйes ', 'Envoyer les extensions de fichiers', 'Accepter les flux interdits', 'Inclure les en-tкtes', 'Javascript externe', 'Accueil', 'Afficher "Keyteq vous propose :"', 'Afficher "rechercher des mises а jour"', 'Afficher les statistiques', 'Ecrire les ID3v2 dans le flux', 'Ouvrir les inscriptions aux utilisateurs', 'Types de fichiers', 'Oui', 'Non', 'Extensions', 'MIME', 'Inclure dans le M3U', 'Editer les types de fichiers', 'Кtes-vous sыr ?', 'Analyse optimale des fichiers', 'Playlist Aleatoire', 'Mode', 'Liste de lecture', 'Aucune, lire directement', 'Mes favoris', 'Aucun fichier trouvй', 'Les plus йcoutйs', 'Ordre', 'Activer le support de LAME ?', 'Dйsactivй', 'Autoriser l\'utilisation de LAME ?', 'E-mail', 'Autoriser l\'envoi de fichiers par e-mail ?', 'Adresse du serveur SMTP', 'Port du serveur SMTP', 'Destinataire', 'Message', 'Envoyer', 'E-mail envoyй !', 'Activer l\'envoi de fichiers upload', 'Rйpertoire pour les envois upload', 'Activer mp3mail', 'Envoyer un fichier', 'Fichier envoyй !', 'Impossible d\'envoyer le fichier !', 'Vous devez autoriser les cookies pour vous connecter !', 'Pйriode', 'depuis le dйbut', 'cette semaine', 'ce mois-ci', 'le mois dernier', 'requкtes', 'Commande LAME', 'Afficher la couverture de l\'album', 'Fichiers de l\'album', 'Redimensionner les images de l\'album', 'Hauteur de l\'album', 'Largeur de l\'album', 'Mйthode d\'envoi d\'e-mail', 'Direct', 'Pear', 'Attendre', 'Veuillez saisir une adresse e-mail valide dans les options !', 'Listes inline ?', 'Afficher l\'album depuis l\'url ?', 'Url de l\'album', 'Impossible de l\'envoyer !', 'Utilisateur ajoutй !', 'Crйateur de l\'archive', 'L\'archive a йtй supprimйe.', 'Mis а jour', 'Similitudes', '%1 entrйes filtrйs', 'Traces des opйrations(log)', 'Visible', 'Archivй', 'Bulletin', 'Ajoutй le %1 par %2', 'Plus', 'Publier', '%1 MegaOctet', '%1 KiloOctet', '%1 Octet', 'Rйcursif', 'Prйcйdent', 'Suivant', 'Aller а la page %1', 'Page:', 'Jamais jouй', 'Approuver manuellement les inscriptions', 'En attente', 'Activer', 'Tous les champs avec un * sont obligatoires', 'Votre compte sera examinй et activй manuellement.', 'Derniиres йcoutes', 'Se souvenir de moi', 'Style', 'Trouver', 'Entrer les chemins de recherche pour', 'Utiliser les selectionnйs ?', 'Durйe de la piste mini/maxi', 'Minutes', 'm3u', 'asx (WMA)', 'Si la mise а jour s\'arrкte, cliquer ici : %1', 'Suivre les liens ?', 'Fichier modиle', 'Activer la sйcuritй des URL', 'Liste blanche des uploads', 'Type de fichier non autorisй', 'La liste de lecture est vide !', 'Paroles', 'URL des paroles', 'Montrer les liens des paroles ?', '(ou?)', 'Utilisateur ou mot de passe inconnu', 'Taille maximale de dйpфt: %1', 'lien RSS ?', 'Configurez un mot de passe, s\'il vous plaоt !', 'Identifiant et Mot de passe nйcessaires', 'Le Nom d\'utilisateur existe dйjа !'); 

$klang[12] = array("Indonesian", "ISO-8859-1", "Indonesia", "Yang Ter-Hot", "Yang Terbaru", "Cari", "(hanya %1 tampilan)", "dtk", "Hasil Pencarian: '%1'", "ditemukan", "Kosong", "Opsi update pencarian database", "Hapus record tdk terpakai", "Bangun Ulang ID3?",  "Mode Debug ?", "Update", "Batal", "update pencarian database", "ada %1 file", "Tipe file tdk ada: %1, abaikan.", "Terinstall: %1 - Update %2, scan:", "Scan:", "Gagal - query: %1", "File %1 tdk terbaca, Abaikan", "Menghapus: %1",  "Tambah %1, Ubah %2, Hapus %3 dimana %4 gagal dan %5 abaikan bila %6 file - %7 detik - %8 dipilih utk dihapus.", "Selesai", "Tutup", "File yang dicari tdk ada: \"%1\"", "Login kPlaylist", "Daftar album dengan artis: %1", "Hotselect %1", "Tdk ada pilihan, Playlist tdk terupdate", "Playlist ter-update!", "Kembali", "Playlist ditambah!",  "Ingatlah utk me-reload hal. ini", "Login:", "Password:", "Peringatan! Ini bukan web umum. Semua Aktifitas terekam disini.", "Login", "Butuh SSL untuk Login", "Putar", "Hapus", "Sharing:", "Simpan", "Playlist kontrol: \"%1\" - %2 judul",  "Editor", "Viewer", "Pilih", "Seq", "Status", "Info", "Hapus", "Nama", "Total:", "Error", "Action pd terpilih:",  "Sekuen", "Ubah Playlist", "Hapus entri ini", "Tambah playlist", "Nama", "Buat", "Putar:", "File", "Album", "Semua", "terpilih",  "tambah", "putar", "ubah", "baru", "Pilih:", "Kontrol:", "Playlist:", "Nomor HotSelect", "KeyTeq Anda:", "(Cek Upgrade)", "Homesite",  "hanya id3", "album", "judul", "artis", "Hotselect Album dari Artis ", "lihat", "Playlist lainnya", "User", "Kontrol Admin", "Yang terbaru", "Yang Terhot", "Logout", "Opsi", "Cek", "Profil", "Ubah user", "User baru", "Nama Lengkap", "Login", "Ubah Password?", "Password", "Komentar",  "Level Akses", "On", "Off", "Hapus user", "Logout user", "Refresh", "User baru", "hapus", "logout", "Gunakan EXTM3U", "Tampilkan banyak baris (hot/baru)",  "Max. Baris pencarian", "Reset", "Buka direktori", "ke direktori: %1", "Download", "Naik keatas", "Ke direktori root", "Cek Upgrade", "User", "Bahasa", "Opsi",  "Bootd", "Acak:", "Seting", "Direktori base", "Lokasi stream", "Bahasa default", "System Windows", "Butuh HTTPS", "Boleh mencari", "Boleh dowload", "Batas session",  "Report gagal login diperlukan", "Hold on - fetching file list ", "Playlist tdk bisa ditambah!", "Admin", "Login dengan HTTPS untuh mengganti!");

$klang[13] = array('Italian', 'ISO-8859-1', 'Italiano', 'Cosa c\'и di Hot', 'Cosa c\'и di nuovo', 'Ricerca', '(soltanto %1 visibile)', 'sec', 'risultato della ricerca: \'%1\'', 'trovato', 'nessuno.', 'aggiona opzioni ricerca nel database', 'Cancella records non utilizzati?', 'Ricostruisci ID3?', 'modalitа di Debug?', 'Aggiorna', 'Annulla', 'aggiorna ricerca nel database', 'Trovati %1 files.', 'Impossibile determinare questo file: %1, saltato.', 'Installato: %1 - Aggiornato: %2, scansione:', 'Scansione:', 'Fallita - ricerca: %1', 'Impossibile leggere questo file: %1. Saltato.', 'Rimosso: %1', 'Inserito %1, aggiornato %1, cancellato %3, quando %4 и fallito e %5 saltato su %6 files - %7 secondi - %8 segnati per la cancellazione.', 'Fatto', 'Chiuso', 'Impossibile trovare files qui: "%1"', 'KPlaylist Login', 'Lista album per artista: %1', 'Hotselect %1', 'Nessuna canzone selezionata. Playlist non aggiornata.', 'Playlist aggiornata!', 'Indietro', 'Playlist aggiunta!', 'Ricorda di ricaricare la pagina.', 'login:', 'password:', 'Attenzione! Questo non и un sito pubblico. Tutte le azioni vengono registrate.', 'Login', 'SSL richiesto per l\'accesso.', 'Play', 'Cancella', 'Condiviso:', 'Salva', 'Controllo playlist: "%1" - %2 titoli', 'Editor', 'Visualizzatore', 'Selezione', 'Seq', 'Stato', 'Informazioni', 'Canc', 'Nome', 'Totale:', 'Errore', 'Azione da eseguire sulla selezione:', 'Sequenza:', 'Edita playlist', 'Cancella questa riga', 'aggiungi playlist', 'Nome:', 'Crea', 'Esegui:', 'File', 'Album', 'Tutto', 'Selezionati', 'aggiungi', 'play', 'modifica', 'nuovo', 'Selezione:', 'Controllo:', 'Playlist:', 'Selezione numerica', 'Keyteq vi propone:', '(controlla aggiornamenti)', 'Homepage', 'solo id3', 'album', 'titolo', 'artista', 'Seleziona album per artista', 'visualizza', 'Playlists condivise', 'Utenti', 'Controllo dell\'amministratore', 'Cosa c\'и di nuovo', 'Cosa c\'и di Hot', 'Esci', 'Opzioni', 'Controlla', 'Mio', 'modifica utente', 'nuovo utente', 'Nome completo', 'Login', 'Cambio Password?', 'Password', 'Commento', 'Livello d\'accesso', 'On', 'Off', 'Cancella utente', 'Uscita utente', 'Refresh', 'Nuovo utente', 'canc', 'Uscita', 'Usa opzione EXTM3U', 'Mostra quante righe (hot/nuove)', 'Righe massime da cercare', 'Reset', 'Apri directory', 'Vai alla directory: %1', 'Download', 'Sali di un livello', 'Vai al livello principale', 'Controlla per l\'aggiornamento', 'utenti', 'lingua', 'opzioni', 'Booted', 'Casuale:', 'Impostazioni', 'Directory iniziale', 'locazione brano', 'Lingua di default', 'Un sistema Windows', 'Richiede HTTPS', 'Permetti ricerca', 'Permetti download', 'timeout sessione', 'Riporta tentativi falliti di login', 'Aspetta - estrazione lista file', 'La playlist non puт essere aggiunta!', 'Amministratore', 'Collegarsi tramite HTTPS per cambiare!', 'Abilita morore di streaming', 'Titolo', 'Artista', 'Album', 'Commento', 'Anno', 'Traccia', 'Genere', 'non settato', 'Limitazione download (kbps)', 'Utente', '%1 minuti - %2 titoli', '%1 kilobit %2 minuti', 'Lista generi: %1', 'Vai', '%1d %2h %3m playtime %4 files %5 mb', 'Nessuna risorsa.', 'Password cambiata!', 'Crea utente', 'Fai la tua selezione!', 'Cos\'и l\'update?', 'Aiuto', 'Usa immagini esterne?', 'Path immagini esterne', 'Password corrente', 'La passord corrente и sbagliata!', 'Archiver preferito', 'L\'archivio potrebbe non essere stato creato', 'Probabile file duplicato: %1 - %2', 'Eliminare la playlist?', 'Alfabetico', 'Random', 'Ordina', 'Originale', 'Usa javascript', 'Eliminare questo utente?', 'Guarda la history', 'history', 'Righe', 'File CSS Esterno', 'Rimuovi Duplicati', 'OK', 'Errore', 'Stream', '(mostra come)', 'files', 'album', '%1g %2h %3m %4s', 'Generale', 'Personalizza', 'Gestione Files', 'Clicca su ? per l\'aiuto', 'Sincronizzazione Automatica Database', 'Iniva estensione file', 'Consenti stream non autorizzati', 'Includi Header', 'Javascript Esterno', 'Homepage', 'Mostra Keyteq gives you part', 'Mostra parte upgrade', 'Mostra Statistiche', 'Scrivi ID3v2 con stream', 'Consenti registrazione utente', 'Tipi di files', 'Sм', 'No', 'Estensione', 'MIME', 'Includi nell\'M3U', 'modifica tipo file', 'Sicuro?', 'Filecheck ottimistico', 'Casuale', 'Modalitа', 'Playlist', 'Niente, direttamente', 'I Miei Preferiti', 'Nessuna hit trovata', 'Hit di tutti i tempi', 'Ordina', 'Consentire supporto LAME?', 'Disabilitato', 'Consentire uso di LAME?', 'Email', 'Consentire invio files via email?', 'Server SMTP', 'Porta SMTP', 'Invia a', 'Messaggio', 'Invia', 'Mail Inviata!', 'Attiva Upload', 'Cartella Upload', 'Attiva mp3mail', 'Upload', 'File Caricato!', 'Il file non puт essere caricato!', 'Devi avere i cookies abilitati per poter effettuare il login!', 'Periodo', 'mai', 'questa settimana', 'questo mese', 'ultimo mese', 'hits', 'Comandi LAME', 'Mostra copertina album', 'File Album', 'Ridimensiona immagini album', 'Altezza album', 'Profonditа album', 'Metodo Mail', 'Diretta', 'Pear', 'Attendi!', 'Digita un email valida nelle opzioni!', 'Playlist Inline?', 'Mostra album dall\'URL?', 'URL Album', 'Impossibile spedire!', 'Utente aggiunto!', 'Creatore Archivio', 'Archivio cancellato.', 'Utente aggiornato!', 'Trova musica', '%1 record filtrati', 'Log accessi', 'Visibile', 'Archiviato', 'Notizie', 'Entrati %1 su %2', 'altro', 'Pubblica', '%1 mb', '%1 kb', '%1 bytes', 'Ricorsivo', 'Precedente', 'Successivo', 'Vai a pagina %1', 'Pagina:', 'Mai ascoltato', 'Approva manualmente registrazioni', 'In attesa', 'attiva', 'Tutti i campi con * sono obbligatori', 'Il tuo account verrа controllato e attivato manualmente', 'Ultimi ascolti', 'ricordami', 'Stile', 'cerca', 'Digita i percorsi da cercare', 'Usa selezionato?', 'Traccia durata min/max', 'Minuti', 'm3u', 'asx (WMA)', 'Se l\'aggiornamento si ferma, clicca qui: %1', 'Seguire symlinks?', 'Formato file', 'Abilita sicurezza URL', 'Carica whitelist', 'Tipo di file non consentito.', 'La Playlist и Vuota!');

$klang[14] = array("Traditional Chinese [&amp;#12345]", "big5", "&#32321;&#39636;&#20013;&#25991;", "&#26368;&#29105;&#38272;", "&#26368;&#26032;", "&#25628;&#23563;", "(&#21482;&#26377; %1 &#31558;&#39023;&#31034;)", "&#31186;", "'%1' &#65306;&#25628;&#23563;&#32080;&#26524;", "&#25214;&#21040;", "&#27794;&#26377;", "&#26356;&#26032;&#25628;&#23563;&#36039;&#26009;&#24235;&#36984;&#38917;", "&#21034;&#38500; &#26410;&#29992;&#36942;&#30340;&#35352;&#37636;&#65311;", "&#37325;&#24314; ID3", "&#38500;&#34802;&#27169;&#24335;", "&#26356;&#26032;", "&#21462;&#28040;", "&#26356;&#26032;&#25628;&#23563;&#36039;&#26009;&#24235;", "&#25214;&#21040; %1 &#27284;&#26696;&#12290;", "&#30906;&#23450;&#19981;&#21040;&#27492; %1 &#27284;&#26696;&#65072; &#30053;&#36942;&#12290;", "&#24050;&#23433;&#35037;&#65072; %1 - &#26356;&#26032;&#65306; %2 &#65104; &#25475;&#30596;&#65306;", "&#25475;&#30596;&#65306;", "&#22833;&#25943; - &#21839;&#38988;&#65072; %1", "&#35712;&#19981;&#21040;&#27492; %1 &#27284;&#26696; &#65072;&#30053;&#36942;", "&#24050;&#31227;&#38500;&#65306; %1", "&#24050;&#25554;&#20837; %1 &#65292; &#24050;&#26356;&#26032; %2 &#65292; &#24050;&#21034;&#38500; %3&#65292; &#22320;&#40670; %4  &#22833;&#25943; &#21450; %6 &#27284;&#26696;&#20013;&#30053;&#36942;%5  - %7 &#31186; - &#24050;&#21034;&#38500; %8 &#26377;&#35352;&#34399;&#30340;&#27284;&#26696;", "&#24050;&#23436;&#25104;", "&#38359;&#38281;", "&#22312;&#27492;&#25214;&#19981;&#21040;&#20219;&#20309;&#27284;&#26696;&#65306; \"%1\"","kPlaylist &#30331;&#20837;", "&#27492;&#27468;&#25163;&#30340;&#23560;&#36655;&#28165;&#21934;&#65306; %1", "&#29105;&#36984; %1", "&#27794;&#26377;&#27468;&#26354;&#36984;&#25799;&#12290; &#25773;&#25918;&#28165;&#21934;&#27794;&#26377;&#26356;&#26032;&#12290;", "&#25773;&#25918;&#28165;&#21934;&#24050;&#26356;&#26032;&#65281;", "&#36820;&#22238;", "&#25773;&#25918;&#28165;&#21934;&#24050;&#21152;&#20837;&#65281;",  "&#35352;&#20303;&#37325;&#26032;&#25972;&#29702;&#27492;&#38913;&#12290;", "&#30331;&#20837;&#21517;&#31281;&#65306;","&#23494;&#30908;&#65306;","&#35686;&#21578;&#65281;&#27492;&#32178;&#31449;&#26159;&#19981;&#20844;&#38283;&#30340;&#65292;&#25152;&#26377;&#21205;&#20316;&#26159;&#26371;&#34987;&#35352;&#37636;&#12290;", "&#30331;&#20837;", "&#23433;&#20840;&#24615;(SSL)&#30331;&#20837;", "&#25773;&#25918;", "&#21034;&#38500;", "&#20998;&#20139;&#65109;", "&#20786;&#23384;", "&#25511;&#21046;&#25773;&#25918;&#28165;&#21934;&#65072; \"%1\" - %2 &#27161;&#38988;", "&#32232;&#36655;&#22120;", "&#27298;&#35222;&#22120;", "&#36984;&#25799;","&#38918;&#24207;", "&#29376;&#24907;", "&#36039;&#35338;", "&#21034;&#38500;", "&#21517;&#31281;", "&#32317;&#25976;&#65109;", "&#37679;&#35492;", "&#36984;&#25799;&#20013;&#65306;", "&#27425;&#24207;&#65109;", "&#32232;&#36655;&#25773;&#25918;&#28165;&#21934;", "&#21034;&#38500;&#27492;&#21152;&#20837;", "&#21152;&#20837;&#25773;&#25918;&#28165;&#21934;", "&#21517;&#23383;&#65109;", "&#24314;&#31435;", "&#25773;&#25918;&#65306;", "&#27284;&#26696;", "&#23560;&#36655;", "&#20840;&#37096;", "&#24050;&#36984;&#25799;", "&#26032;&#22686;", "&#25773;&#25918;", "&#32232;&#36655;", "&#26032;&#22686;", "&#36984;&#25799;&#65306;", "&#25773;&#25918;&#25511;&#21046;&#65306;", "&#25773;&#25918;&#30446;&#37636;&#65306;", "&#29105;&#36984;&#25976;&#20540;", "Keyteq &#25552;&#25552;&#20320;&#65306;", "(&#27298;&#26597;&#26356;&#26032;)", "&#20027;&#38913;", "&#21482;&#25628;&#23563; id3", "&#23560;&#36655;", "&#27161;&#38988;", "&#27468;&#25163;", "&#29105;&#36984;&#27468;&#25163;&#23560;&#36655;", "&#27298;&#35222;", "&#20998;&#20139;&#25773;&#25918;&#30446;&#37636;", "&#29992;&#25142;", "&#31649;&#29702;", "&#26368;&#26032;", "&#26368;&#29105;&#38272;", "&#30331;&#20986;", "&#36984;&#38917;", "&#27298;&#26597;", "&#20854;&#20182;", "&#32232;&#36655;&#20351;&#29992;&#32773;", "&#26032;&#22686;&#20351;&#29992;&#32773;", "&#20840;&#21517;", "&#30331;&#20837;", "&#35722;&#26356;&#23494;&#30908;&#65311;", "&#23494;&#30908;", "&#20633;&#35387;", "&#23384;&#21462;&#23652;&#32026;", "&#38283;", "&#38364;", "&#21034;&#38500;&#20351;&#29992;&#32773;", "&#20999;&#26039;&#20351;&#29992;&#32773;","&#37325;&#26032;&#25972;&#29702;","&#26032;&#22686;&#20351;&#29992;&#32773;", "&#21034;&#38500;", "&#30331;&#20986;", "&#20351;&#29992; EXTM3U &#25928;&#26524;&#65311;", "&#39023;&#31034;&#22810;&#23569;&#34892; (&#29105;&#38272;/&#26032;)", "&#26368;&#22823;&#25628;&#23563;&#34892;&#25976;", "&#37325;&#35373;", "&#38283;&#21855;&#30446;&#37636;", "&#36339;&#21040;&#30446;&#37636;&#65306; %1", "&#19979;&#36617;", "&#36339;&#21040;&#19978;&#19968;&#23652;", "&#36339;&#21040;&#26681;&#30446;&#37636;", "&#27298;&#26597;&#26356;&#26032;", "&#20351;&#29992;&#32773;", "&#35486;&#35328;", "&#36984;&#38917;", "&#24050;&#36215;&#21205;", "&#38568;&#27231;", "&#35373;&#23450;", "&#26681;&#30446;&#37636;&#32085;&#23565;&#36335;&#24465;", "&#20018;&#27969;&#36335;&#24465;", "&#38928;&#35373;&#35486;&#35328;", "&#35222;&#31383;&#31995;&#32113;", "&#35201;&#27714;HTTPS", "&#20801;&#35377;&#25628;&#23563;", "&#20801;&#35377;&#19979;&#36617;","&#36926;&#26178;", "&#22577;&#21578;&#30331;&#20837;&#22833;&#25943;", "&#35531;&#31561;&#31561; - &#24314;&#31435;&#27284;&#26696;&#30446;&#37636;&#20013;","&#25773;&#25918;&#28165;&#21934;&#19981;&#34987;&#26356;&#26032;&#65281;", "&#31649;&#29702;&#32773;", "&#20351;&#29992;HTTPS&#30331;&#20837;&#24460;&#26356;&#25913;&#65281;");

$klang[15] = array('Traditional Chinese - big5', 'big5', 'БcЕй¤¤¤е', 'іМјцЄщ', 'іМ·s', '·jґM', '(Ґu¦і %1 µ§ЕгҐЬ)', '¬н', '\'%1\' ЎG·jґMµІЄG', '§дЁм', 'ЁS¦і', '§у·s·jґMёк®Ж®wїп¶µ', '§R°Ј ҐјҐО№LЄє°OїэЎH', '­««Ш ID3', '°ЈВОјТ¦Ў', '§у·s', 'Ёъ®ш', '§у·s·jґMёк®Ж®w', '§дЁм %1 АЙ®ЧЎC', 'ЅT©w¤ЈЁм¦№ %1 АЙ®ЧЎJ І¤№LЎC', '¤w¦wёЛЎJ %1 - §у·sЎG %2 ЎM ±ЅєЛЎG', '±ЅєЛЎG', 'Ґў±С - °ЭГDЎJ %1', 'ЕЄ¤ЈЁм¦№ %1 АЙ®Ч ЎJІ¤№L', '¤wІѕ°ЈЎG %1', '¤wґЎ¤J %1 ЎA ¤w§у·s %2 ЎA ¤w§R°Ј %3ЎA ¦aВI %4 Ґў±С ¤О %6 АЙ®Ч¤¤І¤№L%5 - %7 ¬н - ¤w§R°Ј %8 ¦і°Oё№ЄєАЙ®Ч', '¤w§№¦Ё', 'Гці¬', '¦b¦№§д¤ЈЁмҐф¦уАЙ®ЧЎG \'%1\'', 'kPlaylist µn¤J', '¦№єq¤вЄє±MїиІMіжЎG %1', 'јцїп %1', 'ЁS¦ієq¦±їпѕЬЎC јЅ©сІMіжЁS¦і§у·sЎC', 'јЅ©сІMіж¤w§у·sЎI', 'Єр¦^', 'јЅ©сІMіж¤wҐ[¤JЎI', '°O¦н­«·sѕгІz¦№­¶ЎC', 'µn¤J¦WєЩЎG', '±KЅXЎG', 'Дµ§iЎI¦№єфЇё¬O¤Ј¤Ѕ¶}ЄєЎA©Т¦і°К§@¬O·|іQ°OїэЎC', 'µn¤J', '¦wҐю©К(SSL)µn¤J', 'јЅ©с', '§R°Ј', '¤АЁЙЎR', 'Аx¦s', '±±ЁојЅ©сІMіжЎJ \'%1\' - %2 јРГD', 'Ѕsїиѕ№', 'АЛµшѕ№', 'їпѕЬ', '¶¶§З', 'Є¬єA', 'ёк°T', '§R°Ј', '¦WєЩ', 'Б`јЖЎR', 'їщ»~', 'їпѕЬ¤¤ЎG', '¦ё§ЗЎR', 'ЅsїијЅ©сІMіж', '§R°Ј¦№Ґ[¤J', 'Ґ[¤JјЅ©сІMіж', '¦W¦rЎR', '«ШҐЯ', 'јЅ©сЎG', 'АЙ®Ч', '±Mїи', 'ҐюіЎ', '¤wїпѕЬ', '·sјW', 'јЅ©с', 'Ѕsїи', '·sјW', 'їпѕЬЎG', 'јЅ©с±±ЁоЎG', 'јЅ©сҐШїэЎG', 'јцїпјЖ­И', 'Keyteq ґЈґЈ§AЎG', '(АЛ¬d§у·s)', 'ҐD­¶', 'Ґu·jґM id3', '±Mїи', 'јРГD', 'єq¤в', 'јцїпєq¤в±Mїи', 'АЛµш', '¤АЁЙјЅ©сҐШїэ', 'ҐО¤б', 'єЮІz', 'іМ·s', 'іМјцЄщ', 'µnҐX', 'їп¶µ', 'АЛ¬d', 'ЁдҐL', 'ЅsїиЁПҐОЄМ', '·sјWЁПҐОЄМ', 'Ґю¦W', 'µn¤J', 'ЕЬ§у±KЅXЎH', '±KЅX', 'іЖµщ', '¦sЁъјhЇЕ', '¶}', 'Гц', '§R°ЈЁПҐОЄМ', '¤БВ_ЁПҐОЄМ', '­«·sѕгІz', '·sјWЁПҐОЄМ', '§R°Ј', 'µnҐX', 'ЁПҐО EXTM3U ®ДЄGЎH', 'ЕгҐЬ¦h¤Ц¦ж (јцЄщ/·s)', 'іМ¤j·jґM¦жјЖ', '­«і]', '¶}±ТҐШїэ', 'ёхЁмҐШїэЎG %1', '¤Uёь', 'ёхЁм¤W¤@јh', 'ёхЁм®ЪҐШїэ', 'АЛ¬d§у·s', 'ЁПҐОЄМ', '»yЁҐ', 'їп¶µ', '¤w°_°К', 'АHѕч', 'і]©w', '®ЪҐШїэµґ№пёф®|', '¦к¬yёф®|', '№wі]»yЁҐ', 'µшµЎЁtІО', '­nЁDHTTPS', 'ҐiҐH·jґM', 'ҐiҐH¤Uёь', '№O®Й', 'іш§iµn¤JҐў±С', 'ЅРµҐµҐ - «ШҐЯАЙ®ЧҐШїэ¤¤', 'јЅ©сІMіж¤ЈіQ§у·sЎI', 'єЮІzЄМ', 'ЁПҐОHTTPSµn¤J«б§у§пЎI', '±Т°К¦к¬y¤ЮАє', 'јРГD', 'єq¤в', '°Ы¤щ¶°', 'іЖµщ', '¦~', '¦±ҐШ', 'Гю«¬', 'Ґјі]©w', 'іМ°Є¤UёьіtІv(kbps)', 'ҐО¤б', '%1 ¤АДБ - %2 єq¦±', '%1 kbit %2 ¤АДБ', 'Гю«¬Єн: %1', '°х¦ж', '%1¤С %2¤p®Й %3¤АДБ јЅ¦±®Й¶Ў %4 АЙ®Ч %5 mb', 'іoёМЁS¦і¬ЫГцёк®Ж', '±KЅX¤w§у§пЎI', 'µщҐU', 'ЅРїпѕЬЎI', '¦і¦у§у·sЎH', 'ЅР«ц¦№ЁD§U', 'ЁПҐОҐ~іЎ№П№іЎH', 'Ґ~іЎ№П№іёф®|', 'І{¦і±KЅX', 'І{¦і±KЅX¤ЈІЕЎI', 'Preferred archiver', 'Archive could not be made', 'ҐiЇаµoІ{­«ВРАЙ®ЧЎG  "%1" "%2"', 'ЅT©w§R°ЈјЅ©сЄнЎH', '¦rҐА¦ё§З', 'АHѕч', '±Ж§З', 'Ґ»ЁУЄє', 'ЁПҐОjavascript', '§AЅT©w­n§R°Јіo­УҐО¤бЎH', 'АЛµшѕъµ{', 'ѕъµ{', '¦ж', 'Ґ~¦bЄєCSSАЙ®Ч', '§R°Ј­«ВРЄє', 'ЅT©w', 'їщ»~', '¦к¬y', 'Ў]Егµш¬°Ў^', 'АЙ®Ч', '°Ы¤щ¶°', '%1¤С %2®Й %3¤А %4¬н', '¤@Їл', '¦Ы­q', 'АЙ®ЧєЮІz', 'ЅР«цЎHЁD§U.', '¦Ы°К§у·sёк®Ж®w', '¤WёьАЙ®Ч©µ¦щ', 'ҐјЁьЕv¦к¬yҐiҐH?', 'Include headers', 'Ґ~¦bЄєjavascript', 'ҐDєф­¶', 'Show Keyteq gives you part', 'ЕгҐЬ§у·sіЎҐч', 'ЕгҐЬІО­pёк®Ж', '¦к¬yјgҐXID3v2', '¶}±ТҐО¤бµщҐU', 'АЙ®ЧБ`Гю', '¬O', '§_', '©µ¦щ', 'MIME', 'ЇЗ¤JM3U', '§у§пАЙ®ЧГю«¬', 'ЅT©wЎH', 'іМАu¤ЖАЙ®ЧАЛ¬d', 'АHѕчјЅ©с', '§О¦Ў', 'јЅ©сЄн', 'ЁS¦іЎAЄЅ±µ', '§ЪЄєіЯ¦n', '§д¤ЈЁмҐф¦уІЕ¦XЄє', 'ҐюіЎ®Й¶ЎІЕ¦X', '¦ё§З', '±Т°КLAME¤дґ©ЎH', 'Гці¬', 'ҐiҐHЁПҐОLAMEЎH', '№q¶l', '·Зі№q¶lАЙ®ЧЎH', 'SMTP¦шЄAѕ№', 'SMTP°р', '¦¬ҐуЄМ', '¤є®e', '±HҐX', '¤w±HҐX¶lҐуЎI', '¶}±Т¤Wёь', '¤WёьҐШїэ', '¶}±Тmp3mail', '¤Wёь', 'АЙ®Ч¤w¤WёьЎI', 'АЙ®Ч¤ЈЇа¤WёьЎI', 'ЅР·ЗіЁПҐОcookiesµn¤JЎI', '®ЙґБ', '±qЁУ', 'Ґ»¬PґБ', 'Ґ»¤л', '¤W¤л', 'ІЕ¦X', 'LAME©RҐO', 'ЕгҐЬ°Ы¤щ¶°«К­±', '°Ы¤щ¶°АЙ®Ч', 'ЕЬ§у°Ы¤щ¶°№П№і¤j¤p', '°Ы¤щ«К®MЄш«Ч', '°Ы¤щ«К®MБп«Ч', '№q¶l¤иЄk', 'ЄЅ±µ', 'Pear', 'µҐЎI', 'ЅР¦bїп¶µ¤¤їй¤JҐїЅT№q¶l¦a§}ЎI', '¤єґOєq¦±ЄнЎH', '±qURL¤¤ЕгҐЬ°Ы¤щ¶°ЎH', '°Ы¤щ¶°URL', '¤ЈЇа±HҐXЎI', 'ҐО¤б¤wҐ[¤JЎI', 'Archive creator', 'Archive is deleted.', 'ҐО¤б¤w§у·sЎI', '­µјЦ§дЁм', '%1 ¶µҐШїпҐX', 'Log access', 'ҐiµшЄє', 'Archived', '¤Ѕ§iЄ©', '¤w¤J %1 Б`јЖ %2', '§у¦h', 'µo¦ж', '%1 mb', '%1 kb', '%1 bytes', '­«ВР', '¤W¤@­¶', '¤U¤@­¶', 'ҐhІД %1 ­¶', '­¶ЅXЎG', '±qҐјјЅ©с', '¤H¤uµщҐU§е·З', 'µҐ«Э¤¤', '±Т°К', '©Т¦іёк®Ж¦і * ЄєіЈ¬OҐІ¶·Єє', '§AЄє¤б¤f±N·|іQАЛµш¤ОҐї«Э§е·З', '¤W¦ё¦к¬y', '°OµЫ§Ъ', '­·®ж', '·jґM', 'їй¤J·jґMёф®|', 'ЁПҐО¤wїпѕЬЄєЎH', 'іМ¤pЎю¤j¦±ҐШ®Й¶Ў', '¤АДБ', 'm3u', 'asx (WMA)', '°І¦p§у·s°±¤оЎAЅР«ц¦№ЎG%1', 'ётАHsymlinks?', 'АЙ®ЧјЛҐ»', '¶}±ТURL¦wҐю©К', '»{Ґi¤Wёь¦CЄн', 'АЙ®ЧГю«¬¤ЈҐiҐHЎD', 'ЄЕҐХЄєєq¦±ЄнЎI', 'Lyrics', 'Lyrics URL', 'Show lyrics link?', '(©О?)', '¤ЈҐїЅTЄєҐО¤бµn¤J¦WєЩ©О±KЅX');

$klang[16] = array("Traditional Chinese - gb2312", "gb2312", "БcЕй¤¤¤е", "іМјцЄщ", "іМ·s", "·jґM", "(Ґu¦і %1 µ§ЕгҐЬ)", "¬н", "'%1' ЎG·jґMµІЄG", "§дЁм", "ЁS¦і", "§у·s·jґMёк®Ж®wїп¶µ", "§R°Ј ҐјҐО№LЄє°OїэЎH", "­««Ш ID3", "°ЈВОјТ¦Ў", "§у·s", "Ёъ®ш", "§у·s·jґMёк®Ж®w", "§дЁм %1 АЙ®ЧЎC", "ЅT©w¤ЈЁм¦№ %1 АЙ®ЧЎJ І¤№LЎC", "¤w¦wёЛЎJ %1 - §у·sЎG %2 ЎM ±ЅєЛЎG", "±ЅєЛЎG", "Ґў±С - °ЭГDЎJ %1", "ЕЄ¤ЈЁм¦№ %1 АЙ®Ч ЎJІ¤№L", "¤wІѕ°ЈЎG %1", "¤wґЎ¤J %1 ЎA ¤w§у·s %2 ЎA ¤w§R°Ј %3ЎA ¦aВI %4 Ґў±С ¤О %6 АЙ®Ч¤¤І¤№L%5 - %7 ¬н - ¤w§R°Ј %8 ¦і°Oё№ЄєАЙ®Ч", "¤w§№¦Ё", "?і¬", "¦b¦№§д¤ЈЁмҐф¦уАЙ®ЧЎG '%1'", "kPlaylist µn¤J", "¦№єq¤вЄє±MїиІMіжЎG %1", "јцїп %1", "ЁS¦ієq¦±їпѕЬЎC јЅ©сІMіжЁS¦і§у·sЎC", "јЅ©сІMіж¤w§у·sЎI", "Єр¦^", "јЅ©сІMіж¤wҐ[¤JЎI", "°O¦н­«·sѕгІz¦№­¶ЎC", "µn¤J¦WєЩЎG", "±KЅXЎG", "Дµ§iЎI¦№єфЇё¬O¤Ј¤Ѕ¶}ЄєЎA©Т¦і°К§@¬O·|іQ°OїэЎC", "µn¤J", "¦wҐю©К(SSL)µn¤J", "јЅ©с", "§R°Ј", "¤АЁЙЎR", "Аx¦s", "±±ЁојЅ©сІMіжЎJ '%1' - %2 јРГD", "Ѕsїиѕ№", "АЛµшѕ№", "їпѕЬ", "¶¶§З", "Є¬єA", "ёк°T", "§R°Ј", "¦WєЩ", "Б`јЖЎR", "їщ»~", "їпѕЬ¤¤ЎG", "¦ё§ЗЎR", "ЅsїијЅ©сІMіж", "§R°Ј¦№Ґ[¤J", "Ґ[¤JјЅ©сІMіж", "¦W¦rЎR", "«ШҐЯ", "јЅ©сЎG", "АЙ®Ч", "±Mїи", "ҐюіЎ", "¤wїпѕЬ", "·sјW", "јЅ©с", "Ѕsїи", "·sјW", "їпѕЬЎG", "јЅ©с±±ЁоЎG", "јЅ©сҐШїэЎG", "јцїпјЖ­И", "Keyteq ґЈґЈ§AЎG", "(АЛ¬d§у·s)", "ҐD­¶", "Ґu·jґM id3", "±Mїи", "јРГD", "єq¤в", "јцїпєq¤в±Mїи", "АЛµш", "¤АЁЙјЅ©сҐШїэ", "ҐО¤б", "єЮІz", "іМ·s", "іМјцЄщ", "µnҐX", "їп¶µ", "АЛ¬d", "ЁдҐL", "ЅsїиЁПҐОЄМ", "·sјWЁПҐОЄМ", "Ґю¦W", "µn¤J", "ЕЬ§у±KЅXЎH", "±KЅX", "іЖµщ", "¦sЁъјhЇЕ", "¶}", "Гц", "§R°ЈЁПҐОЄМ", "¤БВ_ЁПҐОЄМ", "­«·sѕгІz", "·sјWЁПҐОЄМ", "§R°Ј", "µnҐX", "ЁПҐО EXTM3U ®ДЄGЎH", "ЕгҐЬ¦h¤Ц¦ж (јцЄщ/·s)", "іМ¤j·jґM¦жјЖ", "­«і]", "¶}±ТҐШїэ", "ёхЁмҐШїэЎG %1", "¤Uёь", "ёхЁм¤W¤@јh", "ёхЁм®ЪҐШїэ", "АЛ¬d§у·s", "ЁПҐОЄМ", "»yЁҐ", "їп¶µ", "¤w°_°К", "АHѕч", "і]©w", "®ЪҐШїэµґ№пёф®|", "¦к¬yёф®|", "№wі]»yЁҐ", "µшµЎЁtІО", "­nЁDHTTPS", "¤№і\·jґM", "¤№і\¤Uёь", "№O®Й", "іш§iµn¤JҐў±С", "ЅРµҐµҐ - «ШҐЯАЙ®ЧҐШїэ¤¤", "јЅ©сІMіж¤ЈіQ§у·sЎI", "єЮІzЄМ", "ЁПҐОHTTPSµn¤J«б§у§пЎI");

$klang[17] = array("Korean", "ISO-8859-1", "&#54620;&#44397;&#50612;", "&#51064;&#44592;&#51221;&#48372;", "&#52572;&#49888;&#51221;&#48372;", "&#44160;&#49353;", "(%1 &#47564; &#48372;&#51076;)", "&#52488;", "&#44160;&#49353; &#44208;&#44284; : '%1'", "&#52286;&#50520;&#51020;", "&#50630;&#51020;.", "&#44160;&#49353; &#51088;&#47308; &#50741;&#49496; &#50629;&#45936;&#51060;&#53944;", "&#49324;&#50857;&#54616;&#51648; &#50506;&#45716; &#44592;&#47197; &#49325;&#51228;?", "ID3&#51116;&#44396;&#49457;?", "&#46356;&#48260;&#44536; &#47784;&#46300;?", "&#50629;&#45936;&#51060;&#53944;", "&#52712;&#49548;", "&#44160;&#49353; &#51088;&#47308; &#50629;&#45936;&#51060;&#53944;", "%1 &#54028;&#51068;&#51012; &#52286;&#50520;&#51020;.", "&#51060; &#54028;&#51068;&#51012; &#44208;&#51221;&#54624; &#49688; &#50630;&#51020;: %1, &#44148;&#45320;&#46848;.", "&#49444;&#52824;&#46120;: %1 - &#50629;&#45936;&#51060;&#53944;: %2, &#44160;&#49353;:", "&#44160;&#49353;:", "&#49892;&#54056; - &#51656;&#47928;: %1", "&#51060; &#54028;&#51068;&#51012; &#51069;&#51012; &#49688; &#50630;&#51020;: %1. &#44148;&#45320;&#46848;.", "&#51228;&#44144;&#46120;: %1", "%6 &#54028;&#51068;&#46308; &#51473; %4 &#45716; &#49892;&#54056;, %5&#45716; &#44148;&#45320;&#46832;&#44256;,%1 &#52628;&#44032; %2 &#44081;&#49888;&#46104;&#44256; %3 &#49325;&#51228;&#46120; - %7 &#52488; - %8 &#51008; &#49325;&#51228;&#54364;&#49884;&#46120;.", "&#45149;", "&#45803;&#51020;", "&#50612;&#46500; &#54028;&#51068;&#46020; &#52286;&#51012; &#49688; &#50630;&#51020;: \"%1\"", "kPlaylist &#47196;&#44536;&#50728;", "&#50500;&#54000;&#49828;&#53944;&#51032; &#50536;&#48276; &#47532;&#49828;&#53944; : %1", "&#51064;&#44592;&#49440;&#53469;&#44257; %1", "&#44257;&#51060; &#49440;&#53469;&#46104;&#51648; &#50506;&#50520;&#51020;. Playlist&#44032; &#44081;&#49888;&#46104;&#51648; &#50506;&#50520;&#51020;.", "Playlist &#44081;&#49888;!", "&#46244;&#47196;", "Playlist &#52628;&#44032;!", "&#51060; &#54168;&#51060;&#51648;&#47484; &#45796;&#49884; &#51069;&#51004;&#49464;&#50836;.", "&#47196;&#44536;&#51064;:", "&#50516;&#54840;:", "&#51452;&#51032;! &#51060; &#44275;&#51008; &#44277;&#44060;&#46108; &#50937;&#49324;&#51060;&#53944;&#44032; &#50500;&#45785;&#45768;&#45796;. &#47784;&#46304; &#54665;&#46041;&#51060; &#44592;&#47197;&#46121;&#45768;&#45796;.", "&#47196;&#44536;&#51064;", "&#47196;&#44536;&#50728;&#51012; &#50948;&#54644; SSL&#51060; &#54596;&#50836;&#54633;&#45768;&#45796;.", "&#51116;&#49373;", "&#49325;&#51228;", "&#44277;&#50976;&#46120;:", "&#51200;&#51109;", "playlist &#44288;&#47532;: \"%1\" - %2 &#51228;&#47785;", "&#54200;&#51665;&#44592;", "&#48624;&#50612;", "&#49440;&#53469;", "&#49692;&#49436;", "&#49345;&#53468;", "&#51221;&#48372;", "&#49325;&#51228;", "&#51060;&#47492;", "&#54633;&#44228;:", "&#50724;&#47448;", "&#49440;&#53469;&#54620; &#46041;&#51089;:", "&#49692;&#49436;:", "playlist &#54200;&#51665;", "&#51060; &#44592;&#47197;&#51012; &#49325;&#51228;&#54632;", "playlist &#52628;&#44032;", "&#51060;&#47492;:", "&#47564;&#46308;&#44592;", "&#51116;&#49373;:", "&#54028;&#51068;:", "&#50536;&#48276;", "&#51204;&#48512;", "&#49440;&#53469;&#46120;", "&#52628;&#44032;", "&#51116;&#49373;", "&#54200;&#51665;", "&#49352;&#47196; &#47564;&#46308;&#44592;", "&#49440;&#53469;:", "&#51116;&#49373; &#44288;&#47532;:", "Playlist:", "&#51064;&#44592;&#49440;&#53469;&#44257; &#49707;&#51088;", "&#45817;&#49888;&#50640;&#44172; Keyteq &#51060; &#51452;&#45716; &#44163;:", "(&#50629;&#44536;&#47112;&#51060;&#46300;&#47484; &#52404;&#53356;&#54616;&#49464;&#50836;)", "&#54856;", "id3&#47564;", "&#50536;&#48276;", "&#51228;&#47785;", "&#50500;&#54000;&#49828;&#53944;", "&#50500;&#54000;&#49828;&#53944;&#50640;&#49436; &#51064;&#44592;&#50536;&#48276;", "&#48372;&#44592;", "&#44277;&#50976;&#54620; playlist", "&#49324;&#50857;&#51088;", "&#50612;&#46300;&#48124; &#44288;&#47532;", "&#52572;&#49888;&#51221;&#48372;", "&#51064;&#44592;&#51221;&#48372;", "&#47196;&#44536;&#50500;&#50883;", "&#50741;&#49496;", "&#52404;&#53356;", "&#45208;&#51032;", "&#49324;&#50857;&#51088; &#54200;&#51665;", "&#49352;&#47196;&#50868; &#49324;&#50857;&#51088;", "&#51060;&#47492;", "&#47196;&#44536;&#51064;", "&#50516;&#54840;&#47484; &#48148;&#44984;&#49884;&#44192;&#49845;&#45768;&#44620;?", "&#50516;&#54840;", "&#53076;&#47704;&#53944;", "&#51217;&#44540;&#47112;&#48296;", "&#53020;&#44592;", "&#45124;&#44592;", "&#49324;&#50857;&#51088; &#49325;&#51228;", "&#49324;&#50857;&#51088; &#47196;&#44536;&#50500;&#50883;", "&#49352;&#47196; &#44256;&#52824;&#44592;", "&#49352;&#47196;&#50868; &#49324;&#50857;&#51088;", "&#49325;&#51228;", "&#47196;&#44536;&#50500;&#50883;", "EXTM3U &#47484; &#49324;&#50857;&#54633;&#45768;&#44620;?", "&#51460; &#49688; &#48372;&#51060;&#44592;(hot/new)", "&#44032;&#51109; &#47566;&#51008; &#44160;&#49353; &#51460;", "&#47532;&#49483;", "&#46356;&#47113;&#53664;&#47532; &#50676;&#44592;", "&#46356;&#47113;&#53664;&#47532;&#47196; &#44032;&#44592;: %1", "&#45236;&#47140;&#48155;&#44592;", "&#54620; &#45800;&#44228; &#50948;&#47196; &#44032;&#44592;", "&#51228;&#51068; &#50948;&#47196; &#44032;&#44592;.", "&#50629;&#44536;&#47112;&#51060;&#47484; &#52404;&#53356;&#54616;&#49464;&#50836;", "&#49324;&#50857;&#51088;", "&#50616;&#50612;", "&#50741;&#49496;", "&#48512;&#54021;&#46120;", "&#46244;&#49438;&#44592;:", "&#49464;&#54021;", "&#44592;&#48376; &#46356;&#47113;&#53664;&#47532;", "&#49828;&#53944;&#47548; &#51109;&#49548;", "&#44592;&#48376; &#50616;&#50612;", "&#50952;&#46020;&#50864; &#49884;&#49828;&#53596;", "HTTPS &#44032; &#54596;&#50836;&#54632;", "Seek &#54728;&#50857;", "&#45236;&#47140;&#48155;&#44592; &#54728;&#50857;", "&#49464;&#49496; &#49884;&#44036;&#51473;&#45800;", "&#49892;&#54056;&#54620; &#47196;&#44596; &#49884;&#46020; &#50508;&#47532;&#44592;", "&#51104;&#44624;&#47564; - &#54028;&#51068; &#47785;&#47197;&#51012; &#44032;&#51648;&#44256; &#50724;&#44256; &#51080;&#49845;&#45768;&#45796;", "Playlist &#50640; &#52628;&#44032;&#54624; &#49688; &#50630;&#49845;&#45768;&#45796;!", "&#50612;&#46300;&#48124;", "&#48148;&#44984;&#44592; &#50948;&#54644;&#49436; HTTPS&#47196; &#47196;&#44596;&#54616;&#49464;&#50836;!");

$klang[18] = array('Estonian', 'ISO-8859-1', 'Eesti', 'Mis on kuum', 'Mis on uus', 'Otsi', '(ainult %1 nдidatud)', 'sec', 'Otsimis tulemused: \'%1\'', 'leitud', 'puudub.', 'uuenda otsi andmebaas muudatused', 'Kustuta kasutamatta read?', 'Ehita ID3 uuesti?', 'Debug mode?', 'Uuenda', 'Katkesta', 'Uuenda otsimis mootor', 'Leitud %1 faili.', 'Ei leidnud faili: %1, katkestatud.', 'Paigaltatud: %1 - Uuenda: %2, skanneri: ', 'Skanneeri: ', 'Katkend - query: %1', 'Vхimatu lugeda faili: %1. Katkestatud.', 'Eemaldatud: %1', 'Lisatud %1, uuendatud %2, kustutatud %3 kus %4 viga ja %5 vahele jдetud %6 faili - %7 sekundid - %8 mдrgitud kustutamiseks.', 'Valmis', 'Sulge', 'Ei leidnud ьhtki faili siit: "%1"', 'kPlaylist Logi sisse', 'Albumi nimekiri artistidest: %1', 'Kuum-valik %1', 'Ьhtki lugu pole valitud. Lugude nimekirja ei uuendatud.', 'Lugude nimekiri uuendatud!', 'Tagasi', 'Nimekiri lisatud!', 'Pea meeles et lae leht uuesti.', 'tunnus:', 'salasхna:', 'MДRKUS! See pole avalik weebileht. Kхik tegevused logitakse.', 'Logi sisse', 'SSL required for logon.', 'Mдngi', 'Kustuta', 'Jagatud: ', 'Salvesta', 'Muuda lugude nimekirja: "%1" - %2 ', 'Muuda', 'Nдita', 'Vali', 'Seq', 'Staatus', 'Info', 'Kustuta', 'Nimi', 'Koku:', 'Viga', 'Tegevus valitud: ', 'Sequence:', 'muuda nimekirja', 'Kustuta sissekanne', 'lisa nimekiri', 'Nimi:', 'Loo', 'Mдngi: ', 'Fail', 'Album', 'Kхik', 'Valitud', 'lisa', 'mдngi', 'muuda', 'uus', 'Vali:', 'Mдngi: ', 'Nimekiri: ', 'Kuumvalik', 'Keyteq annab sulle:', '(kontrolli uuendusi)', 'Koduleht', 'ainult id3', 'album', 'pealkiri', 'artist', 'Vali artist', 'vaata', 'Jagatud nimekirjad', 'Kasutajad', 'Kontroll paneel', 'Mida uut?', 'Mis on kuum?', 'Logi vдlja', 'Valikud', 'Vali', 'Minu', 'muuda', 'lisa kasutaja', 'Nimi (pikalt)', 'Kasutaja-tunnus', 'Muuda salasхna?', 'Salasхna', 'Kommentaar', 'Ligipддsu tase', 'Sees', 'Vдljas', 'Kustuta kasutaja', 'Logi vдlja', 'Vдrskenda', 'Uus kasutaja', 'kustuta', 'logi vдlja', 'Kasuta EXTM3U vхimalust?', 'Nдita ridu (kuum/uus)', 'Otsi maksimaalselt', 'Reseti', 'Ava kataloog', 'Mine kataloogi: %1', 'Lae-alla', 'Ьks aste ьlesse', 'Mine juur kataloogi.', 'Kontrolli uuendusi', 'kasutajad', 'Keel( Language)', 'muudatused', 'Booted', 'Segamini:', 'Sдtted', 'Baas kataloog', 'Saatja(Stream) asukoht', 'Pхhi-keel', 'Windowsi sьsteem', 'Nхua HTTPS', 'Luba kerida', 'Luba alla-laadida', 'Sessioon aegub', 'Teata ebaхnnestunud logimistest', 'Hoia kinni - tirin failide nimekirja', 'Nimekirja pole vхimalik lisada!', 'Administraator', 'Sisselogimine muuda HTTPS vastu!', 'Luba voolav(streaming) mootor', 'Pealkiri', 'Artist', 'Album', 'Kommentaar', 'Aasta', 'Rada', 'tььp', 'pole seatud', 'Maksimaalne mдngimise rate (kbps)', 'Kasutaja', '%1 minuteid - %2 pealkirju', '%1 kbit %2 minuted', 'Sьsee list: %1', 'Go', '%1d %2h %3m mдnguaega %4 faili %5 mb', 'Puuduvad.', 'Salasхna muudetud!', 'Registreeri', 'Tee oma valik!', 'Mis on uuendus?', 'Vajuta siia abisaamiseks', 'Kasuta vдliseid pilte?', 'Vдliste piltide kataloog', 'Praegune salasхna', 'Salasхnad ei sobi kokku!', 'Soovitud pakkija', 'Arhiivi pole vхimalik luua', 'Korduvaid kirjeid leitud:  "%1" "%2"', 'Kas kustutada nimekiri?', 'Tдhestik', 'Suvaline', 'Sorteeri', 'Originaal', 'Kasuta javascripti', 'Kas oled kindel et soovid kustutada kasutajat?', 'Vata ajalugu', 'ajalugu', 'Ridu', 'Vдline CSS fail', 'Eemalda korduvad', 'OK', 'ERR', 'Stream', '(nagu)', 'failid', 'albumid', '%1d %2h %3m %4s', 'Pea', 'Valikuline', 'Failihaldur', 'Vajuta ? abi-saamiseks.', 'Automaatne andmebaasi sьnkroniseerimine', 'Saada faili laiend', 'Luba logimatta kuulajaid', 'Lisa (Headers)', 'Vдline javascript', 'Koduleht', 'Nдita Keyteq annab sulle tьki', 'Nдita uuenduste osa', 'Nдita statistika', 'Kirjuta ID3v2 streami sisse', 'Luba kasutajate registreerimine', 'Failitььbid', 'Jah', 'Ei', 'Laiend', 'MIME', 'Lisa M3U', 'muuda failitььpi', 'Kindel?', 'Optimistiline failikontroll', 'Segamini', 'Mode', 'Nimekiri', 'Puudub, otsene', 'Minu lemmikud', 'Ei leidnud ьhtki', 'Kokku', 'Jдrjesta', 'Luba LAME toetus?', 'Keelatud', 'Luba LAME kasutus?', 'Email', 'Luba faile saata emailiga?', 'SMTP server', 'SMTP port', 'Mail to', 'Teade', 'Saada', 'Kiri saadetud!', 'Aktiivne ьlesse laadimine', 'Ьlesse-laadimise kataloog', 'Aktiveeri mp3mail', 'Lae', 'Faili ьlesse-laadimine!', 'Faili pole vхimalik serverisse saata!', 'Kьpsised peavad olema lubatud!', 'Periood', 'kunagi', 'see nдdal', 'see kuu', 'eelmine kuu', 'hits', 'LAME kдsk', 'Nдita albumi kaant', 'Albumi failid', 'Suurenda albumi pilte', 'Albumi kхrgus', 'Albumi laius', 'Saatmise meetod', 'Otse', 'Pear', 'Oota!', 'Palun sisesta toimiv email!', 'Nimekiri peidetud?', 'Nдita albumeid URLi aadressilt?', 'Albumi URL', 'Pole vхimalik saata!', 'Kasutaja lisatud!', 'Arhiivi looja', 'Arhiiv kustutatud.', 'Kasutaja laetud!', 'Muusika sobivus', '%1 sissekannet filtreeritud', 'Logi ligipддs', 'Vaadatav', 'Arhiveeritud', 'Bulletin', 'Lisatud %1 - %2', 'veel', 'Avalda', '%1 mb', '%1 kb', '%1 baiti', 'Rekursiivne ', 'Eelmine', 'Jдrgmine', 'Mine lehele %1', 'Lehekьlg:', 'Pole kunagi mдngitud', 'Kдsitsi luba registreerimisi', 'Ootel', 'Aktiveeri', 'Kхik vдljad mis on mдrgitud * on kohustuslikud', 'Sinu konto kontrollitakse ja aktiveeritakse kдsitsi.', 'Viimased striimingud', 'Mдleta mind', 'Stiil', 'Leia', 'Kinnita otsing', 'Kasuta valikut', 'Ajanдit min/max', 'Minutid', 'm3u', 'asx', 'Kui uuendus peatub, vajuta siia: %1', 'jдlgi symlinke', 'fileґi љabloon', 'Annab URL kaitse', 'Lae uus list', 'File tььp ei ole lubatud', 'Lugude nimekiri tьhi', 'Laulusхnad', 'Laulusхnade URL', 'Nдita laulusхnade linki', '(vхi?)', 'Tundamtu kasutajatunnus vхi parool ');

$klang[19] = array('Brazillian Portuguese', 'ISO-8859-1', 'Portuguкs do Brasil', 'Mais executados', 'Novidades', 'Busca', '(apenas %1 encontrado)', 'seg', 'Resultados da busca: \'%1\'', 'Encontrado', 'Nenhum', 'Atualizar opзхes de busca na base de dados ', 'Apagar entradas sem uso? ', 'Reconstruir ID3?', 'Modo Debug?', 'Atualizar', 'Cancelar', 'Atualizar busca no banco de dados', 'Encontrados %1 arquivos.', 'Nгo foi possнvel determinar este arquivo: %1, descartado', 'Instalaзгo %1 - Atualizar: %2, escanear:', 'Escanear:', 'Falha na busca: %1', 'Nгo foi possнvel ler este arquivo: %1. Descartado.', 'Removido em: %1', 'Inserido %1, atualizado %2, apagado %2, onde %4, falhou em %5, descartado por %6, arquivos - %7 seg - %8 marcado para ser deletado', 'Finalizado.', 'Fechar', 'Nгo foi encontrado nenhum arquivo aqui: "%1"', 'Logon kPlaylist', 'Lista de бlbum por artista: %1', 'Populares %1', 'Nenhuma mъsica selecionada. Lista nгo atualizada.', 'Lista atualizada!', 'Voltar', 'Lista adicionada!', 'Lembre de atualizar a pбgina.', 'Login:', 'Senha:', 'Atenзгo! Este nгo й um site restrito. Todas as aзхes sгo monitoradas.', 'Login', 'SSL necessбrio para entrar.', 'Tocar', 'Apagar', 'Compartilhado:', 'Salvar', 'Lista de controle: "%1" - %2 tнtulos', 'Editor', 'Visualizador', 'Selecionar', 'Seq', 'Status', 'Info', 'Del', 'Nome', 'Totais:', 'Erro', 'Aзгo selecionada:', 'Sequкncia:', 'Editar lista', 'Apagar esta entrada', 'Adicionar lista', 'Nome:', 'Criar', 'Tocar:', 'Arquivo', 'Бlbum', 'Todos', 'Selecionado', 'Adicionar', 'Tocar', 'Editar', 'Novo', 'Selecionar:', 'Controle:', 'Lista:', 'Selecionar nъmerico', 'Keyteq oferece:', '(verificar atualizaзгo)', 'Pбgina inicial', 'apenas id3', 'Бlbum', 'Tнtulo', 'Artista', 'Selecionar бlbum por artista', 'Ver', 'Listas compartilhadas', 'Usuбrios', 'Controle de administrador', 'Novidades', 'Mais executado', 'Sair', 'Opзхes', 'Verificar', 'Meu', 'Editar usuбrio', 'Novo usuбrio', 'Nome completo', 'Login', 'Mudar senha?', 'Senha', 'Comentбrio', 'Nнvel de acesso', 'Ligado', 'Desligado', 'Apagar usuбrio', 'Desconectar usuбrio', 'Atualizar', 'Novo usuбrio', 'Apagar', 'Desconectar', 'Utilizar opзгo EXTM3U?', 'Mostrar quantos arquivos (popular/novo)', 'Mбximo de arquivos encontrados', 'Restaurar', 'Abrir diretуrio', 'Ir para o diretуrio: %1', 'Download', 'Subir um nнvel', 'Ir para o diretуrio principal.', 'Verificar atualizaзхes', 'Usuбrios', 'Idioma', 'Opзхes', 'Carregado', 'Aleatуrio:', 'Configuraзхes', 'Diretуrio base', 'Local de stream', 'Idioma padrгo', 'Sistema Windows', 'Requer HTTPS', 'Permitir busca', 'Permitir download', 'Sessгo expirou (seg)', 'Falha na tentativa de login', 'Aguarde - buscando lista de arquivos', 'Lista nгo pode ser adicionada!', '0 = Admin, 1 = Usuбrio', 'Inнcio de uma sessгo com o HTTPS a mudar', 'Habilite processo streaming', 'Tнtulo', 'Artista', 'Бlbum', 'Comentбrio', 'Ano', 'Faixa', 'Gкnero', 'Desativado', 'Taxa mбxima de download (kbps)', 'Usuбrio', '%1 minuto(s) - %2 Tнtulos ', '%1 kbit %2 minuto(s)', 'Lista de Gкneros: %1 ', 'Ir', 'Tocando: %1d %2h %3m : %4 files : %5 mb', 'Aqui nгo hб recurso relevante.', 'Senha alterada!', 'Registrar', 'Por favor, selecione!', 'O que estб atualizado?', 'Clique aqui para Ajuda', 'Usar Imagens Externas?', 'Path externo de imagens ', 'Senha Atual', 'A senha nгo confere!', 'Arquivo preferido ', 'Arquivo nгo pode ser criado!', 'Provavelmente encontrado arquivo duplo: "%1" "%2"', 'Deseja apagar a lista?', 'Alfabйtico', 'Randфmico', 'Tipo', 'Original', 'Usar javascript', 'Deseja realmente deletar este usuбrio?', 'Ver descriзгo', 'Descriзгo', 'Fileiras', 'Arquivo CSS externo', 'Remover duplicados', 'OK', 'ERR', 'Stream', '(mostrar como)', 'Arquivos', 'Бlbuns', '%1d %2h %3m %4s', 'Geral', 'Customizar', 'Menu do arquivo', 'Clique em ? para Ajuda.', 'Automбtico banco de dados sync', 'Enviar extensгo de arquivo ', 'Permitir streams nгo autorizados ', 'Incluir cabeзбlho', 'Javascript externo', 'Homepage', 'Exibir o que Keyteq lhe oferece ', 'Mostrar atualizaзгo а parte', 'Mostrar estatнsticas', 'Escrever ID3v2 com stream', 'Permitir registro do usuбrio', 'Tipo de arquivos', 'Sim', 'Nгo', 'Extensгo', 'MIME', 'Incluir no M3U', 'Editar tipo de arquivo', 'Й isso mesmo?', 'Otimizar a procura do arquivo', 'Randomizar', 'Modo', 'Lista para tocar', 'Nenhum, direto', 'Meus favoritos', 'Nгo foi encontrado nenhum sucesso (hit)', 'Sempre sucessos (hits)', 'Ordem', 'Habilitar suporte LAME?', 'Desabilitado', 'Pertimir o uso de LAME?', 'E-mail', 'Permitir enviar arquivos por e-mail?', 'Servidor SMTP', 'Porta SMTP', 'E-mail para', 'Mensagem', 'Enviar', 'E-mail enviado!', 'Ativar upload', 'Diretуrio de uploads', 'Ativar mp3mail', 'Upload', 'Upload completo!', 'Nгo foi possнvel fazer upload do arquivo', 'Й necessбrio ativar cookies para o login!', 'Perнodo', 'Sempre', 'Esta semana ', 'Este mкs', 'Ъltimo mкs', 'Sucessos (hits)', 'Comando LAME', 'Exibir capa do бlbum', 'Arquivos do бlbum', 'Redimencionar tamanho das imagens do бlbum', 'Altura do бlbum', 'Largura do бlbum', 'Mйtodo de enviar e-mail', 'Direto', 'Pear', 'Aguarde!', 'Por favor, insira seu e-mail vбlido nas opзхes!', 'Listas em espera?', 'Exibir бlbum da URL', 'URL do бlbum', 'Nгo foi possнvel enviar!', 'Usuбrio adicionado!', 'Compressor de arquivos', 'Arquivo deletado.', 'Usuбrio atualizado!', 'Mъsica encontrada', '%1 entradas filtradas', 'Log de acesso', 'Visнvel', 'Arquivado', 'Boletim', 'Entrada %1 de %2', 'mais', 'Publicador', '%1 mb', '%1 kb', '%1 bytes', 'Recursivo', 'Anterior', 'Prуximo', 'Vб para a pбgina %1', 'Pбgina:', 'Nunca tocado', 'Aprovaзгo Manual', 'Pendente', 'Ativando', 'Todos os campos marcados com * sгo Obrigatуrios', 'Sua conta estб aguardando autorizaзгo manual', 'Novas streams', 'Lembrar', 'Estilo', 'Busca', 'Informe os caminhos de procura', 'Usar o selecionado?', 'Tempo da faixa min/max', 'Minutos', 'm3u', 'asx (WMA)', 'Se a atualizaзгo parar clique aqui: %1', 'Seguir symlinks?', 'Modкlo de arquivo', 'Habilitar URL security', 'Enviar lista permitida', 'Tipo de arquivo nгo permitido', 'A Lista de Execuзгo estб vazia!', 'Letras', 'URL das letras', 'Mostrar link das letras?', '(ou?)', 'Usuбrio ou senha invбlidos', 'Tamanho max do upload: %1', 'Abrir public RSS?', 'Favor inserir senha!', 'Й necessбrio o nome e login', 'Usuбrio jб existente!', 'Acesso admin. para esta sessгo?', 'Buscar entradas no banco de dados: %1/%2', 'Nгo foi possнvel encontrar "%1", o arquivo foi apagado?', 'De/Atй (DDMMAA)', 'Erro, tente novamente.', 'Comprimento mбximo do texto', 'Dir Colunas', 'Novo modelo de exibiзгo', 'Modelo de exibiзгo', 'Nomear modelo', 'Й necessбrio nomear o modelo!', 'Modo padrгo de registro', 'Extrator Tag:', 'Permitir usar arquivo(s)', 'Tamanho mбximo de arquivo (mb)', 'Foi excedido o tamanho mбximo do arquivo! (%1mb, max is %2mb)');

$klang[20]  = array("Simplified Chinese", "big5", "јтМеЦРОД", "ИИБ¦НЖјц", "ЧоЅьёьРВ", "ЛСЛч", "ДїЗ°Ц»УР %1", "Гл", "ЛСЛчЅб№ыЈєЎ°%1Ў±", "±»ХТµЅ", "Г»УР", "ёьРВЛСЛчКэѕЭївСЎПо", "ЙѕµфОґК№УГµДјНВјЈї", "ЦШЅЁID3±кЗ©Јї", "ЕЕґнДЈКЅЈї", "Йэј¶", "ИЎПы", "ёьРВЛСЛчКэѕЭїв", "№ІХТµЅ %1 ёцОДјю", "ОЮ·ЁК¶±рґЛОДјюЈє%1Ј¬ТСМш№эЈЎ", "ТС°ІЧ°Јє%1 -ёьРВЈє%2Ј¬ЙЁГиЈє", "ЙЁГиЈє", "ІйСЇЎ°%1Ў±К§°ЬБЛ", "ОЮ·Ё¶БИЎґЛОДјюЈє%1Ј¬ТСМш№эЈЎ", "%1ТС±»ЙѕіэЈЎ", "ТСФЪ%4ІеИл%1Ј¬ёьРВ%2Ј¬Йѕіэ%3", "ТСНкіЙ", "№Ш±Х", "ФЪЎ°%1Ў±ХТІ»µЅИОєООДјю", "µЗВЅKPlayList", "Ў°%1Ў±µДЧЁј­БР±н", "јцСЎ%1", "ОґСЎФсЖµµАЈЎІҐ·ЕБР±нОґёьРВЈЎ", "ІҐ·ЕБР±нТС±»ёьРВЈЎ", "·µ»Ш", "ІҐ·ЕБР±нТСМнјУЈЎ", "ЗлјЗµГЛўРВТіГжЈЎ", "ХКєЕЈє", "јУГЬ·ГОКЈє", "ЗлЧўТвЈЎґЛНшХѕІў·З№«№ІµДЈ¬ЛщУРІЩЧчЅ«±»ПµНіјЗВјЈЎ", "µЗВЅ", "µЗВЅРиТЄSSLЦ§іЦЈЎ", "ІҐ·Е", "Йѕіэ", "№ІПнЈє", "±Јґж", "їШЦЖІҐ·ЕБР±нЈєЎ°%1Ў±-%2 ±кМв", "±ај­ИЛЈє", "ІйїґХЯЈє", "СЎФс", "Гл", "ЧґМ¬", "РЕПў", "Йѕіэ", "ГыіЖ", "ЧЬјЖЈє", "ґнОу", "µ±±»СЎЦРК±Јє", "ѕщєвЈє", "±ај­ІҐ·ЕБР±н", "ЙѕіэґЛјНВј", "МнјУІҐ·ЕБР±н", "ГыіЖЈє", "ґґЅЁ", "ХэФЪІҐ·ЕЈє", "ОДјю", "ЧЁј­", "И«Ії", "±»СЎЦРµД", "МнјУ", "ІҐ·Е", "±ај­", "РВ", "СЎФсЈє", "ІҐ·ЕїШЦЖЈє", "ІҐ·ЕБР±нЈє", "јцСЎКэДї", "Keyteq МбКѕДг", "ЈЁјмІйёьРВЈ©", "НшХѕ", "ЅцID3", "ЧЁј­", "±кМв", "ТХКхјТ", "ТХКхјТјцСЎЧЁј­", "Ійїґ", "±»№ІПнµДІҐ·ЕБР±н", "УГ»§", "№ЬАнФ±їШЦЖГж°е", "ЧоЅьёьРВ", "ИИБ¦НЖјц", "НЛіц", "СЎПо", "јмІй", "ОТµД", "±ај­УГ»§РЕПў", "ґґЅЁРВУГ»§ХКєЕ", "И«Гы", "ХКєЕ", "ёьёДГЬВлЈї", "ГЬВл", "ЧўКН", "·ГОКИЁПЮ", "КЗ", "·с", "ЙѕіэУГ»§", "К№УГ»§НЛіц", "ЛўРВ", "ґґЅЁРВУГ»§ХКєЕ", "Йѕіэ", "НЛіц", "К№УГEXTM3UКфРФЈЁ.m3uЈ©", "ІйїґРРКэЈЁЧоИИ/ЧоРВЈ©", "ЧоґуЛСЛчРРКэ", "ЦШЦГ", "ґтїЄДїВј", "ЅшИлµЅДїВјЈє%1", "ПВФШ", "·µ»ШЙПТ»ј¶ДїВј", "·µ»ШёщДїВј", "јмІйЙэј¶", "УГ»§", "УпСФ", "СЎПо", "ТС±»ПµНіМЯіц", "ВТРтІҐ·ЕЈє", "ЙиЦГ", "ёщДїВј", "БчОДјюФґ", "И±КЎУпСФ", "WindowsПµНі", "РиТЄHTTPS", "ФКРнЛСЛч", "ФКРнПВФШ", "SessionБчіМі¬К±", "±ЁёжК§°ЬµДµЗВЅіўКФРРОЄ", "ЗлЙФµИЎЄЎЄХэФЪ¶БИЎОДјюБР±н", "ІҐ·ЕБР±нОЮ·Ё±»МнјУЈЎ", "№ЬАнФ±", "»»ТФHTTPS·ЅКЅµЗВЅ", "БчТэЗжЙъР§", "±кМв", "ТХКхјТ", "ЧЁј­", "ЧўКН", "Дк", "Тф№м", "БчЕЙ", "ОґЙиЦГ", "ЧоґуПВФШЛЩВК(Kbps)", "УГ»§", "%1 ·ЦЦУ - %2 ёц±кМв", "%1 З§±ИМШ %2 ·ЦЦУ", "БчЕЙБР±н", "И·¶Ё", "%1d %2h %3m ІҐ·ЕК±і¤ %4 ёцОДјю %5 ХЧ", "Г»УРПа№ШЧКФґ", "ГЬВлТСѕ­іЙ№¦РЮёДЈЎ", "µЗВЅ", "ЗеСЎФсТ»ПоЈЎ", "ЧоЅьУРКІГґёьРВЈї", "µг»чХвАп»сИЎ°пЦъ", "К№УГА©Х№НјПсПФКѕЈї", "А©Х№НјЖ¬В·ѕ¶", "µ±З°ГЬВл", "µ±З°ГЬВл»ҐІ»ЖҐЕдЈЎ", "їЙИЎµГµДґжµµ", "ОЮ·Ёґжµµ", "їЙДЬПаН¬µДОДјю%1-%2ХТµЅБЛ", "ХжµДЙѕіэІҐ·ЕБР±нЈї", "°ґЧЦДёЛіРтЕЕРт", "Лж»ъІҐ·Е", "ЕЕРт", "ЖрФґ");

$klang[21] = array("Catalan", "iso-8859-1", "Catalа", "El mйs nou", "Novetat", "Cerca", "(nomйs es mostra %1)", "seg", "Resultats de la Recerca: '%1'", "trobat", "Cap.", "actualitza les opcions de recerca a la base de dades", "Esborrar registres no utilitzats?", "Regenerar ID3?", "Mode depuraciу?", "Actualitza", "Cancel·la", "Actualitza base de dades de recerca", "Trobats %1 fitxers.", "No puc determinar aquest fitxer: %1, l'ignoro.", "Instal·lat: %1 - Actualitzat: %2, Escanejat:", "Scanejat:", "Error - query: %1", "No puc llegir aquest arxiu: %1. L'ignoro.", "Esborrat: %1","Insertat %1, actualitzat %2, esborrat %3 amb %4 errors i %5 ignorats de %6 arxius - %7 seg - %8 marcats per esborrar.", "Fet", "Tanca", "No he trobat cap arxiu a: \"%1\"", "Entrar a kPlaylist", "Llista d'аlbums de l'artista: %1", "Marcat %1", "No s'han sel·leccionat canзons. Playlist no actualitzada.", "Playlist actualitzada!", "Tornar", "Playlist afegida!", "Recorda recarregar la pаgina.", "Entrar:", "Secret:", "Compte! Aixт йs una WEB no pъblica. Totes les accions es registren. ", "Entrar", "Es requereix SSL per entrar.", "Reprodueix", "Esborra", "Compartit:", "Graba.", "Playlist de Control: \"%1\" - %2 tнtols", "Editor", "Visualitzador", "Sel·lecciona", "Seq", "Estat", "Info", "Esborra", "Nom", "Totals:", "Error", "Accions en sel·leccionar:", "Seqьиncia:", "edita Playlist","Esborra aquesta entrada", "afegeix playlist", "Nom:", "Crea", "Reprodueix:", "Arxiu", "Аlbum", "Tot", "Sel·leccionat", "afegeix", "reprodueix", "edita", "nou", "Sel·lecciona:", "Control de reproducciу:", "Playlist;", "Sel·lecciу numйrica", "Keyteq et dona:", "(actualitzaciу de soft)", "Homesite", "nomйs id3", "аlbum", "tнtol", "artista", "аlbum sel·leccionat de l'artista", "veure", "Playlists compartits", "Usuaris", "Control d'Administrador", "Que hi ha de nou", "Que hi ha novedos", "Sortir", "Opcions", "Txequeja", "Jo", "edita usuari", "nou usuari", "Nom complet", "Entrada", "Canviar password?", "Password", "Comentari", "Nivell d'accйs", "On", "Off", "Esborrar usuari", "Desconnectar usuari", "Refrescar", "Nou usuari", "esborra", "sortir", "Utilitzar caracterнstiques EXTM3U?", "Mostrar quantes columnes (hot/nou)", "Mаxim de columnes de recerca", "Resetejar", "Obrir directori", "Anar al directori: %1", "Descarregar", "Pujar un nivell", "Anar al directori root.", "Txequeja actualitzacions.", "usuaris", "Llenguatge", "opcions", "Iniciat", "Aleatori:", "Configuraciу", "directori base", "Localitzaciу d'Stream", "Llenguatge per defecte", "Sistema Windows", "Necessita HTTPS", "Permetre recerques", "Permetre descаrregues", "Temps de sessiу (COOKIE)", "Reporta errors d'intent d'entrada", "Espera. Recuperant llista de fitxers.", "No es pot afegir la Playlist!", "Admin", "Entra per HTTPS per acceptar els canvis!", "Activa el motor d'streaming", "Tнtol", "Artista", "Аlbum", "Comentaris", "Any", "Pista", "Gиnere", "no especificat", "Mаxim ample de descаrrega (kbps)", "Usuari", "%1 mins - %2 tнtols", "%1 kbit %2 mins", "Llista de gиneres: %1", "Som-hi", "Temps de reproducciу %1d %2h %3m %4 arxius %5 mb", "No hi ha arxius relevants.", "Password canviat!", "Signa", "Siusplau fes una sel·lecciу!", "Que hi ha de nou?", "Clica aquн per a ajuda", "Utilitza imatges externes?", "Camн per a imatges externes", "Password actual", "Password actual no coincideix!", "Arxivador preferit", "No es pot crear l'arxiu", "Trobat un problable arxiu duplicat: %1 - %2", "Esborrar Playlist de debт?", "Alfabиtic", "Al·leatori", "Ordena", "Original", "Utilitza javascript", "Estas segur que vols esborrar aquest usuari?", "Veure historial", "Historial", "Files", "Arxiu CCS extern");

$klang[22] = array('Bulgarian', 'windows-1251', 'Български', 'Кое е супер?', 'Кое е ново?', 'Търсене', '(показват се само %1)', 'сек', 'Резултат от търсенето: \'%1\' ', 'намерен', 'Нищо.', 'Обновление на базата данни за търсене - опция', 'Изтриване на неизползваните записи?', 'Възстановявам ID3? ', 'Отстраняване на дефектите ?', 'Обновление', 'Отказ', 'Обновление на базатаданни за търсене', 'Намерени %1 фаила. ', 'Неможе да се определи този фаил: %1, пропуснат.  ', 'Инсталиран: %1 - Обновление: %2, сканиране:', 'Сканиране:', 'Грешка - заявка: %1 ', 'Този файл неможе да се прочете: %1. Пропуснат.', 'Премахнати: %1 ', 'Вкарани %1, обновление %2, изтрити %3 къде %4 failed and %5 skipped through %6 files - %7 sec - %8 marked for deletion', 'Готово', 'Затвори', 'Неможе да се намерят никакви фаилове тук:  "%1" ', 'kPlaylist Вход', 'Списък на албумите по певци: %1', 'Бърз избор %1 ', 'Не са избрани песни. Плейлистът не е обновен. ', 'Плейлистът е обновен!', 'Назад', 'Плейлистът е добавен!', 'Не забравяйте да презаредите страницата.', 'Име:', 'Парола:', 'Съобщение! Това не е обществен сайт. Всички действия се записват.', 'Влез', 'Необховимо е SSL за влизане.', 'Пусни', 'Изтрий', 'Сподели:', 'Запази', 'Управление playlist: "%1" - %2 заглавие', 'Редактор', 'Погледни', 'Избери', 'Послед.', 'Състояние', 'Информация', 'Изтр.', 'Име', 'Общо:', 'Грешка', 'Действие на избраното:', 'Последователност:', 'Редакция playlist ', 'Изтрийте това', 'добавяне playlist ', 'Име:', 'Създай', 'Пусни:', 'Файл', 'Албум', 'Всичко', 'Избери', 'добави', 'пусни', 'редактиране', 'нов', 'Избери:', 'Контрол на пускане:', 'Плейлист:', 'Бързо избиране по номер', 'Keyteq gives you:', '(провери за обновяване)', 'Homesite ', 'само id3 ', 'албум', 'заглавие', 'певец', 'Бързо избиране по буква', 'виж', 'Сподели playlists ', 'Потребители', 'Админ. управление', 'Кое е ново?', 'Кое е супер?', 'Излизане', 'Настройки', 'Прегледай', 'Мой', 'редактиране на потребителя', 'нов потребител', 'Пълно име', 'Име(ник)', 'Смяна на паролата?', 'Парола', 'Коментар', 'Избор на достъпа', 'Вкл.', 'Изкл.', 'Изтриване на потребител', 'Излизане на потребител', 'Опреснение', 'Нов потребител', 'изтр.', 'изход', 'Използване на EXTM3U?', 'Показване на колко редове (горещи/нови)', 'Максимум редове при търсене', 'Нулиране', 'Отвори директория', 'Отиди в директория:  %1 ', 'Сваляне', 'Отиди една стъпка нагоре', 'Отиди в главната директория.', 'Провери за ъпгрейди', 'потребилтели', 'Език', 'опции', 'Изгонен', 'Разбъркано:', 'Настрийки', 'Главна директория', 'Stream location', 'Език по подразбиране', 'A Windows system', 'Изискване HTTPS ', 'Позволено превъртане в песента', 'Позволено сваляне', 'Време на сесията', 'Рапорт на неуспелите да влязат', 'Hold on - fetching file list', 'Playlist  неможе да бъде добавен!', 'Администратор', 'Влез с HTTPS за смяна!', 'Разрешен streaming engine ', 'Заглавие', 'Изпълнител', 'Албум', 'Коментар', 'Година', 'Песен', 'Жанр', 'не избрано', 'Макс. скорост на сваляне (kbps)', 'Потребител', '%1 мин. - %2 заглавия ', '%1 kbit %2 мин. ', 'Жанров списък: %1', 'Отиди', '%1д. %2ч. %3м. време за слушане %4 файла %5 мб.', 'No relevant resources here.', 'Паролата е сменена!', 'Регистрация', 'Моля направете избор!', 'Какво е обновяване?', 'Натиснете тук за помощ', 'Използване на външни снимки?', 'Пътя до външните снимки', 'Текуща парола', 'Текущата парола не съвпада!', 'Предпочитан архиватор', 'Архива неможе да бъде направен', 'Намерено е вероятно дублиране:  %1 - %2', 'Искате ли да изтриете плейлиста?', 'По азбучен ред', 'Рабъркано', 'Сортирано', 'Оригинал', 'Използвай javascript ', 'Сигурен ли сте, че искате да изтриете този потребител?', 'Виж историята', 'история', 'Редове', 'Външен CSS фаил', 'Премахни дубликатите', 'OK', 'ERR', 'Поток', '(покажи като)', 'файлове', 'албуми', '%1д %2ч %3м %4с', 'Общи', 'Промени', 'Обработка на файловете', 'Кликнете ? за помощ.', 'Автоматично синхронизиране на базата данни', 'Прати разширението на файла', 'Позволи неупълномощени потоци', 'Добави хедърите', 'Външен javascript', 'Главна страница', 'Покажи частта Keyteg ти дава', 'Покажи частта - обновление', 'Покажи статистики', 'Запиши ID3v2 със стрийма', 'Позволи регистрация на нови потребители', 'Видове файлове', 'Да', 'Не', 'Разширение', 'MIME', 'Включи в M3U', 'редактирай файлов тип', 'Сигурен ли сте?', 'Оптимистична проверка на файловете', 'Разбърквател', 'Режим', 'Плейлист', 'Няма, директно', 'Моите любими', 'Не са намерени попадения', 'Хитове на всички времена', 'Подредба', 'Включи поддръжка на LAME?', 'Изключен', 'Позволи използване на LAME?', 'Имейл', 'Позволи пращане на файлове по имейл?', 'SMTP сървър', 'SMTP порт', 'Прати майл до', 'Съобщение', 'Изпрати', 'Изпратено!', 'Активирай ъплоуд', 'Директория за ъплоудите', 'Активирай mp3mail', 'Ъплоуд', 'Файлът е качен!', 'Файлът неможе да се качи!', 'Трябва да разрешите cookies за да влезете!', 'Период', 'винаги', 'тази седмица', 'този месец', 'последния месец', 'попадения', 'LAME команда', 'Покажи обложката на албум', 'Файловете на албум', 'Промени размера на изображенията за обложки', 'Височина на обложката', 'Ширина на обложката', 'Метод за имей', 'Direct', 'Pear', 'Почакай!', 'Моля въведете валиден имейл!', 'Вложени плейлисти?', 'Покажи албума от URL?', 'URL за албума', 'Неможе да се изпрати!', 'Потребителя е добавен!', 'Архиватор', 'Архива е изтрит.', 'Потребителя е обновен!', 'Съвпадаща музика', '%1 полета филтрирани', 'Записвай достъпа', 'Видим', 'Архивиран', 'Форум', 'Въведени %1 от %2', 'още', 'Публикувай', '%1 мб', '%1 кб', '%1 байта', 'Рекурсивно', 'Предишен', 'Следващ', 'Отиди на страница %1', 'Страница:', 'Никога не е пускан', 'Ръчно одобрявай регистрации', 'Чакащо', 'активирай', 'Всички полета маркирани с * са задължителни', 'Вашият акаунт ще бъде проверен и активиран ръчно.', 'Последните стиймове.', 'запомни ме', 'Стил', 'намери', 'Въведете път за търсене', 'Използвай маркираните?', 'Време на песента мин/макс', 'Минути', 'm3u', 'asx(WMA)', 'Ако обновяването спре, натиснете тук: %1', 'Follow symlinks?', 'Файлове темплейт ', 'Включи URL сигурност', 'Upload whitelist', 'Този тип файл не е позволен.', 'Плейлиста е празен!');

$klang[23] = array("Polish", "ISO-8859-2", "Polski", "Popularne", "Nowo&#347;ci", "Wyszukaj", "pokazano tylko %1", "sek", "Wyniki wyszukiwania: \'%1\'", "znaleziono", "Nic.", "aktualizacja opcji wyszukiwania bazy", "Usun&#261;&#263; nieu&#380;ywane wpisy?", "Odbudowa&#263; ID3?", "Tryb usuwania b&#322;&#281;dуw?", "Aktualizacja", "Anuluj", "aktualizacja wyszukiwania bazy", "Znaleziono %1 plikуw", "Nie mo&#380;na okre&#380;li&#263; po&#322;o&#380;enia pliku: %1", "Instalacja: %1 - Aktualizacja: %2, badanie:", "Skanowanie:", "Niepowodzenie - pytanie: %1", "Nie mo&#380;na odczyta&#263; tego pliku: %1. Pomini&#281;cie.", "Usuni&#281;to: %1", "Wstawiono %1, uaktualniono %2, usuni&#281;to %3 gdzie %4 uszkodzonych i %5 pomini&#281;to z powodu %6 plikуw - %7 sek - %8 zaznaczonych do usuni&#281;cia. ", "Sko&#324;czone", "Zamknij", "Nie mo&#380;na znale&#378;&#263; tutaj &#380;adnych plikуw: \"%1\"", "Logowanie kPlaylist", "Lista albumуw dla wykonawcy: %1", "popularny wybуr %1", "nie wybrana melodia. Playlista nie zaktualizowana.", "Playlista zaktualizowana!", "Wstecz", "Playlista dodana!", "Pami&#281;taj o prze&#322;adowaniu strony", "login:", "has&#322;o:", "Uwaga! To nie jest strona publiczna. Wszystkie akcje s&#261; rejestrowane.", "Login", "Do zalogowania wymagany jest SSL", "Odgrywaj", "Usu&#324;", "Wspуlny:", "Zapisz", "Kontrola playlist: \"%1\" - %2 tytu&#322;y", "Edytor", "Przegl&#261;darka", "Zaznacz", "Ci&#261;g", "Status", "Info", "Kasuj", "Nazwa", "Podsumowanie:", "B&#322;&#261;d", "Akcja na zaznaczonych:", "Kolejno&#347;&#263;", "edytuj playlist&#281;", "Usu&#324; ten zapis", "dodaj playlist&#281;", "Nazwa:", "Utwуrz", "Odtwarzaj:", "Plik", "Album", "Wszystko", "Wybrane", "dodaj", "odtwarzaj", "edytuj", "nowe", "Zaznacz:", "Kontrol odtwarzania:", "Playlista:", "popularne numery", "Twуj identyfikator:", "(sprawd&#378; czy s&#261; poprawki)", "Stona domowa", "tylko id3", "album", "tytu&#322;", "wykonawca", "Popularne albumy wykonawcy", "widok", "Wspуlne playlisty", "U&#380;ytkownicy", "Panel administratora", "Nowo&#347;ci", "Popularne", "Wylogowanie", "Opcje", "Sprawd&#378;", "Mуj", "edytuj u&#380;ytkownika", "nowy u&#380;ytkownik", "Pe&#322;na nazwa", "Login", "Zmieni&#263; has&#322;o?", "Has&#322;o", "Komentarz", "poziom dost&#281;pu", "W&#322;&#261;czony", "Wy&#322;&#261;czony", "Usu&#324; u&#380;ytkownika", "Wyloguj u&#380;ytkownika", "Od&#347;wie&#380;", "Nowy u&#380;ytkownik", "usu&#324;", "wyloguj", "Mo&#380;liwo&#347;&#263; u&#380;ycia EXTM3U?", "Ile pokaza&#263; wierszy (popularne/nowe)", "Max przeszukiwanych wierszy", "Resetuj", "Otwуrz katalog", "Id&#378; do katalogu: %1", "Pobierz", "Id&#378; katalog wy&#380;ej", "Id&#378; do katalogu g&#322;уwnego", "Sprawd&#378; czy s&#261; poprawki", "u&#380;ytkownicy", "J&#281;zyk", "opcje", "Inicjowanie", "Mieszanie:", "Ustawienia", "Katalog bazowy", "Lokalizacja strumienia", "Domy&#347;lny j&#281;zyk", "System Windows?", "Wymagane HTTPS", "Wszyscy mog&#261; ogl&#261;da&#263;", "Wszyscy mog&#261; &#347;ci&#261;ga&#263;", "Maksymalny czas sesji", "Raportuj b&#322;&#281;dne prуby logowania", "W&#322;&#261;cz wstrzymywanie - najlepsze listy plikуw", "Playlista nie mo&#380;e by&#263; dodana!", "Administrator", "Zaloguj z HTTPS aby zmieni&#263;!", "Aktywny strumie&#324; silnika", "Tytu&#322;", "Wykonawca", "Album", "Komentarz", "Rok", "&#346;cie&#380;ka", "Rodzaj", "nie ustawione", "Max pr&#281;dko&#347;&#263; &#347;ci&#261;gania (kbps)", "U&#380;ytkownik", "%1 minuty - %2 tytu&#322;y", "%1 kbit %2 minuty", "Rodzaj listy: %1", "Id&#378;", "%1d %2h %3m czas odtwarzania %4 plikуw %5 MB", "Nie zwi&#261;zany z tymi zasobami", "Has&#322;o zmienione!", "Wy&#347;lij", "Prosz&#281; wykona&#263; zaznaczenie!", "Co to jest aktualizacja?", "Kliknij tutaj aby uzyska&#263; pomoc", "U&#380;y&#263; zewn&#281;trznych obrazkуw?", "&#346;cie&#380;ka zewn&#281;trznych obrazkуw", "Bie&#380;&#261;ce has&#322;o", "Bie&#380;&#261;ce has&#322;o nie jest w&#322;a&#347;ciwe!", "Preferowany archiwizator", "Archiwum nie mo&#380;e zosta&#263; utworzone", "Prawdopodobnie znaleziono duplikat pliku: %1 - %2", "Na pewno usun&#261;&#263; pleylist&#281;?", "Alfabetycznie", "Losowo", "Sortuj", "Oryginalnie", "U&#380;yj javascript", "Czy jeste&#347; pewny, &#380;e chcesz usun&#261;&#263; tego uzytkownika?", "Przegl&#261;daj histori&#281;", "historia", "Wiersze", "Zewn&#281;trzny plik CSS");

$klang[24] = array('Lithuanian', 'ISO-8859-13', 'Lietuviрkai', 'Da&#254;niausiai klausomi', 'Nauja', 'Paie&#240;ka', '(rodoma tiktai %1)', 'sec', 'Paie&#65533;kos rezultatai: \'%1\'', 'rasta', 'N&#279;ra.', 'atnaujinti pai&#65533;kos duomen&#371; baz&#279;s nustatymus', 'I&#65533;trinti nereikalingus &#303;ra&#65533;us?', 'Atnaujinti ID3?', 'Su klaid&#371; aptikimu?', 'Atnaujinti', 'Nutraukti', 'atnaujinti paie&#65533;kos duomen&#371; baz&#281;', 'Rasta %1 fail&#371;.', 'Neina nustatyti &#65533;io failo: %1, praleid&#65533;iam.', '&#302;diegta: %1 - Atnaujinti: %2, skenuoti:', 'Skenuoti:', 'Nepavykusi u&#65533;klausa: %1', 'Neina perskaityt &#65533;io failo: %1. Praleid&#65533;iam.', 'Pa&#65533;alinta: %1', '&#302;traukta %1, atnaujinta %2, i&#65533;trinta %3 kur %4 nepavyk&#281; ir %5 praleisti i&#65533; %6 fail&#371; - %7 sec - %8 pa&#65533;ym&#279;ti i&#65533;trynimui.', 'Atlikta', 'U&#65533;daryti', 'Nepavyko rasti joki&#371; fail&#371; &#269;ia: "%1"', 'kPlaylist Prisijungimas', 'Album&#371; s&#261;ra&#65533;as pagal autori&#371;: %1', 'Populiariausi %1', 'Nepa&#65533;im&#279;jote n&#279; vieno failo. Playlist\'as neatnaujintas.', 'Playlist\'as atnaujintas!', 'Atgal', '&#302;trauktas Playlist\'as.', 'Neu&#65533;mir&#65533;kite perkrauti puslapio.', 'vartotojo vardas:', 'slapta&#65533;odis:', 'D&#279;mesio! Tai ne vie&#65533;as interneto puslapis. Visi veiksmai yra &#303;ra&#65533;omi.', 'Prisijungti', 'SSL reikia norint prisijungti.', 'Groti', 'I&#65533;trinti', 'Vie&#65533;i:', 'I&#65533;saugoti', 'Redaguojamas Playlist\'as: "%1" - %2 pavadinimai', 'Redaktorius', 'Per&#65533;valga', 'Pa&#65533;ym&#279;ti', 'T&#281;sinys', 'Pad&#279;tis', 'Info', 'I&#65533;trinti', 'Vardas', 'I&#65533;viso:', 'Klaida', 'Atlikti veiksm&#261; su pa&#65533;ym&#279;tais:', 'Eil&#279;s tvarka:', 'redaguoti playlist\'&#261;', 'I&#65533;trinti &#65533;&#303; &#303;ra&#65533;&#261;', '&#303;traukti playlist\'&#261;', 'Vardas:', 'Sukurti', 'Groti:', 'Failas', 'Albumas', 'Visi', 'Pa&#65533;ym&#279;tus', '&#303;traukti', 'groti', 'redaguoti', 'naujas', 'Pa&#65533;ym&#279;ti:', 'Grojimo valdymas:', 'Playlist\'as:', 'Pasirink&#371; numeravimas', 'Keyteq si&#363;lo:', '(patikrinti ar naudoji naujausi&#261; versij&#261;)', 'J&#363;s&#371; puslapis', 'tiktai id3', 'albumas', 'pavainimas', 'atlik&#279;jas', 'Pa&#65533;ym&#279;ti atlik&#279;jo album&#261;', 'per&#65533;i&#363;r&#279;ti', 'Vie&#65533;i playlist\'ai', 'Vartotojai', 'Admin valdymas', 'Naujienos', 'Da&#65533;niausiai', 'Atsijungti', 'Nustatymai', 'Pa&#65533;ym&#279;ti', 'Mano', 'redaguoti vartotoj&#261;', 'naujas vartotojas', 'Pilnas vardas', 'Vartotojo vardas', 'Pakeisti slapta&#65533;od&#303;?', 'Slapta&#65533;odis', 'Komentaras', 'Vartotojo lygis', '&#302;jungta', 'I&#65533;jungta', 'I&#65533;trinti vartotoj&#261;', 'Atjungti vartotoj&#261;', 'Perkrauti', 'Naujas vartotojas', 'i&#65533;trinti', 'atjungti', 'Naudoti EXTM3U?', 'Kiek rodyti stulpeli&#371;?', 'Daugiausia paie&#65533;kos eilu&#269;i&#371;', 'Atstatyti', 'Atidaryti direktorij&#261;', 'Eiti &#303;: %1', 'Parsisi&#371;sti', 'Vienu ejimu atgal', '&#302; root direktorij&#261;', 'Patikrinti atnaujinim&#261;', 'vartotojai', 'Kalba', 'nustatymai', 'Pakrautas', 'Mai&#65533;yti:', 'Nustatymai', 'Pradin&#279; direktorija', 'Stream vieta', 'Pagrindin&#279; kalba', 'Windows sistema', 'Reikalauti HTTPS', 'Leisti paie&#65533;k&#261;', 'Leisti parsisiuntimus', 'Session timeout', 'Prane&#65533;ti apie nepavykusius prisijungimus', 'Palaukite - sudaromas fail&#371; s&#261;ra&#65533;as', 'Playlist\'o neina &#303;traukti', 'Admin', 'Prisijunkite su HTTPS nor&#279;dami k&#261; nors pakeisti!', 'Leisti streming', 'Pavadinimas', 'Atlik&#279;jas', 'Albumas', 'Komentaras', 'metai', 'Takelis', '&#65533;anras', 'nenustatyta', 'Did&#65533;iausias siuntimosi greitis', 'Vartotojas', '%1 min - %2 pavadinimai', '%1 kbit %2 min', '&#65533;anr&#371; s&#261;ra&#65533;as: %1', 'Eiti', '%1 d %2h %3m grojimo laikas %4 fail7 %5 mb', 'N&#279;ra susijusi&#371; resurs&#371;.', 'Slapta&#65533;odis pakeistas.', 'Prisiregistruoti', 'Pasirinkite!', 'Kas yra - Atnaujinimas?', 'Pagalba', 'Naudoti i&#65533;orinius paveiksliukus', 'I&#65533;orini&#371; paveiksliuk&#371; vieta', 'Dabartrinis slapta&#65533;odis', 'Slapta&#65533;od&#65533;iai nesutampa', 'Naudojamas archyvatorius', 'Nepavyko sudaryti archyvo', 'Grei&#269;iausiai rasti du vienodi failai: "%1" "%2"', 'I&#65533;trinti playlist\'&#261;?', 'Alfabeti&#65533;kai', 'Atsitiktinai', 'Sutraukti', 'Orginaliai', 'Naudoti javascript', 'Ar tikrai norite i&#65533;trinti &#65533;&#303; vartotoj&#261;?', 'Per&#65533;i&#363;r&#279;ti istorij&#261;', 'istorija', 'Eilut&#279;s', 'I&#65533;orinis CSS failas', 'I&#65533;trinti dublikatus', 'Taip', 'Klaida', 'Stream', '(rodyti kaip)', 'failai', 'albumai', '%1d %2h %3m %4s', 'Pagrindinis', 'Redaguoti', 'Fail&#371; palaikymas', 'Paspauskite ant ? kad gaut pagalb&#261;.', 'Automatinis Duomen&#371; baz&#279;s atnaujinimas', 'Nusi&#371;sti failo pl&#279;tin&#303;', 'Leisti neautorizuotus streamus', '&#302;traukti headerius', 'I&#65533;orinis javascript', 'Puslapis', 'Rodyti Keuteq duoda tau', 'Rodyti atnaujinim&#261;', 'Rodyti statistik&#261;', '&#302;ra&#65533;yti ID3v2 su streamu', 'Leisti vartotoj&#371; prisiregistravim&#261;', 'Fail&#371; tipai', 'Taip', 'Ne', 'Pl&#279;tinys', 'MIME', '&#302;traukti M3U', 'redaguoti fail&#371; tip&#261;', 'Tikrai?', 'Optimistinis fail&#184; patikrinimas', 'Sumai&#240;yti', 'Metodas', 'Playlistas', 'N&#235;ra, tiesiogiai', 'M&#235;gstamiausi', 'Nerasta nei vieno paspaudimo', 'Vis&#184; laik&#184; hitai', 'U&#254;sisakyti', '&#193;jungti LAME palaikym&#224;?', 'I&#240;jungta', 'Lesti naudotis LAME?', 'El. pa&#240;tas', 'Lesiti si&#184;sti failus el. pa&#240;tu?', 'SMTP serveris', 'SMTP portas', 'Kam si&#184;sti', '&#222;inut&#235;', 'Si&#184;sti', 'Lai&#240;kas i&#240;si&#184;stas!', 'Aktyvuoti atsiuntimus', 'Atsiuntim&#184; direktorija', 'Aktivuoti mp3pa&#240;t&#224;', 'Atsi&#184;sti', 'Failas atsi&#184;stas', 'Nepavyko atsi&#184;sti failo!', 'Cookies palaikymas turi b&#251;ti &#225;jungtas jei norite prisijungti!', 'Periodas', 'kadanors', '&#240;i&#224; savait&#191;', '&#240;&#225; m&#235;nes&#225;', 'praeit&#224; m&#235;nes&#225;', 'paspaudimai', 'LAME komanda', 'Rodyti albumo vir&#240;el&#225;', 'Albumo failai', 'Pakeisti albumo paveiksliuk&#184; dyd&#225;', 'Albumo auk&#240;tis', 'Albumo plotis', 'Siuntimo el. pa&#240;tu metodas', 'Tiesiogiai', 'Netiesiogiai', 'Palaukti', '&#193;veskite teising&#224; el. pa&#240;to adres&#224; nustatymuose.', 'Playlist\'as inline?', 'Rodyti album&#224; i&#240; nuorodos?', 'Albumo nuoroda', 'Nepavyko nusi&#184;sti!', 'Vartotojas &#225;trauktas!', 'Archyv&#224; suk&#251;r&#235;', 'Archyvas i&#240;trintas.', 'Vartotojo apra&#240;ymas atnaujintas!', 'Atitikmenys', '%1 &#225;ra&#240;&#184;', 'Pri&#235;jimas prie log&#184;', 'Skaitoma', 'Suarchyvuota', 'Suvestin&#235;', '&#193;vesta %1 - %2', 'daugiau', 'Publikuoti', '%1 mb', '%1 kb', '%1 bait&#184;', 'Pasikartojantis', 'Atgal', 'Pirmyn', 'Eiti &#225; puslap&#225; %1', 'Puslapis:', 'Niekados negrotas', 'Administruojama registracija', 'Laukia', 'aktyvuoti', 'Laukai pa&#254;ym&#235;ti * yra privalomi.', 'J&#251;s&#184; registracija bus per&#254;i&#251;r&#235;ta ir aktyvuota administratoriaus.', 'Paskutiniai grojimai', 'Prisiminti prisijungimo informacij&#224;', 'Stilius', 'surasti', 'Бvesti paieрkos raktus', 'Naudoti paюymлtus?', 'Dainos trukmл min/max', 'Minutлs', 'm3u', 'asx (WMA)', 'Jei atnaujinimas sustoja, paspauskite иia: %1', 'Naudoti symlinks?', 'Bylos рablonas', 'Naudoti URL apsaugas', 'Бkelti sаraра', 'Neleidюiamas bylos tipas ,', 'Playlist\'as tuриias!');

$klang[25] = array("Thai", "ISO-8859-11", "&#3652;&#3607;&#3618;", "&#3617;&#3634;&#3651;&#3627;&#3617;&#3656;", "&#3617;&#3634;&#3649;&#3619;&#3591;", "&#3588;&#3657;&#3609;&#3627;&#3634;", "(&#3649;&#3626;&#3604;&#3591;&#3648;&#3593;&#3614;&#3634;&#3632; %1)", "&#3623;&#3636;&#3609;&#3634;&#3607;&#3637;", "&#3612;&#3621;&#3585;&#3634;&#3619;&#3588;&#3657;&#3609;&#3627;&#3634; :\'%1\'", "&#3614;&#3610;", "&#3652;&#3617;&#3656;", "&#3611;&#3619;&#3633;&#3610;&#3611;&#3619;&#3640;&#3591;&#3605;&#3633;&#3623;&#3648;&#3621;&#3639;&#3629;&#3585;&#3600;&#3634;&#3609;&#3586;&#3657;&#3629;&#3617;&#3641;&#3621;&#3626;&#3635;&#3627;&#3619;&#3633;&#3610;&#3588;&#3657;&#3609;&#3627;&#3634;", "&#3621;&#3610;&#3648;&#3619;&#3588;&#3588;&#3629;&#3619;&#3660;&#3604;&#3607;&#3637;&#3656;&#3652;&#3617;&#3656;&#3648;&#3588;&#3618;&#3651;&#3594;&#3657;", "&#3626;&#3619;&#3657;&#3634;&#3591; ID3 &#3651;&#3627;&#3617;&#3656;", "&#3648;&#3611;&#3636;&#3604; Debug Mode", "&#3611;&#3619;&#3633;&#3610;&#3611;&#3619;&#3640;&#3591;", "&#3618;&#3585;&#3648;&#3621;&#3636;&#3585;", "&#3611;&#3619;&#3633;&#3610;&#3611;&#3619;&#3640;&#3591;&#3600;&#3634;&#3609;&#3586;&#3657;&#3629;&#3617;&#3641;&#3621;&#3651;&#3609;&#3585;&#3634;&#3619;&#3588;&#3657;&#3609;&#3627;&#3634;", "&#3614;&#3610;&#3607;&#3633;&#3657;&#3591;&#3627;&#3617;&#3604; %1 &#3652;&#3615;&#3621;&#3660;", "&#3652;&#3617;&#3656;&#3626;&#3634;&#3617;&#3634;&#3619;&#3606;&#3619;&#3632;&#3610;&#3640;&#3652;&#3615;&#3621;&#3660; %1 , &#3586;&#3657;&#3634;&#3617;&#3652;&#3611;", "&#3605;&#3636;&#3604;&#3605;&#3633;&#3657;&#3591;: %1 -&#3611;&#3619;&#3633;&#3610;&#3611;&#3619;&#3640;&#3591;: %2 ,&#3605;&#3619;&#3623;&#3592;&#3627;&#3634;", "&#3605;&#3619;&#3623;&#3592;&#3627;&#3634;", "&#3585;&#3634;&#3619;&#3588;&#3657;&#3609;&#3627;&#3634;&#3612;&#3636;&#3604;&#3614;&#3621;&#3634;&#3604; :%1", "&#3652;&#3617;&#3656;&#3626;&#3634;&#3617;&#3634;&#3619;&#3606;&#3629;&#3656;&#3634;&#3609;&#3652;&#3615;&#3621;&#3660; : %1 &#3586;&#3657;&#3634;&#3617;&#3652;&#3611;", "&#3621;&#3610; %1", "&#3648;&#3614;&#3636;&#3656;&#3617; %1 ,&#3611;&#3619;&#3633;&#3610;&#3611;&#3619;&#3640;&#3591; %2,&#3621;&#3610; %3,&#3607;&#3637;&#3656; %4,&#3612;&#3636;&#3604;&#3614;&#3621;&#3634;&#3604;&#3649;&#3621;&#3632; %5 ,&#3586;&#3657;&#3634;&#3617;&#3652;&#3611; %6 &#3652;&#3615;&#3621;&#3660; %7 &#3623;&#3636;&#3609;&#3634;&#3607;&#3637; %8 &#3607;&#3635;&#3648;&#3588;&#3619;&#3639;&#3656;&#3629;&#3591;&#3627;&#3617;&#3634;&#3618;&#3648;&#3614;&#3639;&#3656;&#3629;&#3621;&#3610;", "&#3648;&#3619;&#3637;&#3618;&#3610;&#3619;&#3657;&#3629;&#3618;", "&#3611;&#3636;&#3604;", "&#3652;&#3617;&#3656;&#3614;&#3610;&#3652;&#3615;&#3621;&#3660;&#3652;&#3604;&#3654;&#3607;&#3637;&#3656;&#3617;&#3637;&#3626;&#3656;&#3623;&#3609;&#3611;&#3619;&#3632;&#3585;&#3629;&#3610; \"%1\"", "&#3648;&#3586;&#3657;&#3634;&#3626;&#3641;&#3656;&#3619;&#3632;&#3610;&#3610;", "&#3619;&#3634;&#3618;&#3585;&#3634;&#3619;&#3629;&#3633;&#3621;&#3610;&#3633;&#3617;&#3626;&#3635;&#3627;&#3619;&#3633;&#3610;&#3624;&#3636;&#3621;&#3611;&#3636;&#3609; : %1", "&#3606;&#3641;&#3585;&#3648;&#3621;&#3639;&#3629;&#3585;&#3617;&#3634;&#3585;&#3607;&#3637;&#3656;&#3626;&#3640;&#3604; %1", "&#3652;&#3617;&#3656;&#3614;&#3610;&#3619;&#3634;&#3618;&#3585;&#3634;&#3619;&#3607;&#3637;&#3656;&#3648;&#3621;&#3639;&#3629;&#3585; &#3619;&#3634;&#3618;&#3585;&#3634;&#3619;&#3648;&#3621;&#3656;&#3609;&#3652;&#3617;&#3656;&#3652;&#3604;&#3657;&#3617;&#3637;&#3585;&#3634;&#3619;&#3611;&#3619;&#3633;&#3610;&#3611;&#3619;&#3640;&#3591;", "&#3611;&#3619;&#3633;&#3610;&#3611;&#3619;&#3640;&#3591;&#3619;&#3634;&#3618;&#3585;&#3634;&#3619;&#3648;&#3621;&#3656;&#3609;&#3649;&#3621;&#3657;&#3623;", "&#3618;&#3657;&#3629;&#3618;&#3585;&#3621;&#3633;&#3610;", "&#3648;&#3614;&#3636;&#3656;&#3617;&#3648;&#3586;&#3657;&#3634;&#3619;&#3634;&#3618;&#3585;&#3634;&#3619;&#3648;&#3621;&#3656;&#3609;", "&#3629;&#3618;&#3656;&#3634;&#3621;&#3639;&#3617;&#3607;&#3637;&#3656;&#3592;&#3632;&#3648;&#3611;&#3636;&#3604;&#3627;&#3609;&#3657;&#3634;&#3605;&#3656;&#3634;&#3591;&#3609;&#3637;&#3657;&#3651;&#3627;&#3617;&#3656;&#3629;&#3637;&#3585;&#3588;&#3619;&#3633;&#3657;&#3591;", "&#3648;&#3586;&#3657;&#3634;&#3626;&#3641;&#3656;&#3619;&#3632;&#3610;&#3610; :", "&#3619;&#3627;&#3633;&#3626;&#3612;&#3656;&#3634;&#3609;", "&#3627;&#3617;&#3634;&#3618;&#3648;&#3627;&#3605;&#3640; : &#3648;&#3623;&#3655;&#3610;&#3648;&#3614;&#3592;&#3627;&#3609;&#3657;&#3634;&#3627;&#3609;&#3637;&#3657;&#3617;&#3636;&#3651;&#3594;&#3656;&#3627;&#3609;&#3657;&#3634;&#3626;&#3634;&#3608;&#3634;&#3619;&#3603;&#3632;&#3585;&#3634;&#3585;&#3619;&#3632;&#3607;&#3635;&#3607;&#3635;&#3607;&#3633;&#3657;&#3591;&#3627;&#3617;&#3604;&#3592;&#3632;&#3606;&#3647;&#3585;&#3610;&#3633;&#3609;&#3607;&#3638;&#3585;&#3652;&#3623;&#3657;", "&#3648;&#3586;&#3657;&#3634;&#3626;&#3641;&#3656;&#3619;&#3632;&#3610;&#3610;", "&#3605;&#3657;&#3629;&#3591;&#3585;&#3634;&#3619; SSL &#3648;&#3614;&#3639;&#3656;&#3629;&#3585;&#3634;&#3619;&#3648;&#3586;&#3657;&#3634;&#3626;&#3641;&#3656;&#3619;&#3632;&#3610;&#3610;", "&#3648;&#3621;&#3656;&#3609;", "&#3621;&#3610;", "&#3629;&#3609;&#3640;&#3597;&#3634;&#3605;&#3651;&#3627;&#3657;&#3612;&#3641;&#3657;&#3629;&#3639;&#3656;&#3609;&#3651;&#3594;&#3657;&#3604;&#3657;&#3623;&#3618;&#3652;&#3604;&#3657;", "&#3610;&#3633;&#3609;&#3607;&#3638;&#3585;", "&#3588;&#3623;&#3610;&#3588;&#3640;&#3617;&#3619;&#3634;&#3618;&#3585;&#3634;&#3619;&#3648;&#3621;&#3656;&#3609; : \"%1\" - %2 &#3594;&#3639;&#3656;&#3629;", "&#3585;&#3634;&#3619;&#3649;&#3585;&#3657;&#3652;&#3586;", "&#3604;&#3641;", "&#3648;&#3621;&#3639;&#3629;&#3585;", "&#3621;&#3635;&#3604;&#3633;&#3610;", "&#3626;&#3606;&#3634;&#3609;&#3632;", "&#3619;&#3634;&#3618;&#3621;&#3632;&#3648;&#3629;&#3637;&#3618;&#3604;", "&#3621;&#3610;", "&#3594;&#3639;&#3656;&#3629;", "&#3607;&#3633;&#3657;&#3591;&#3627;&#3617;&#3604; :", "&#3612;&#3636;&#3604;&#3614;&#3621;&#3634;&#3604;", "&#3585;&#3634;&#3619;&#3585;&#3619;&#3632;&#3607;&#3635;&#3610;&#3609;&#3585;&#3634;&#3619;&#3648;&#3621;&#3639;&#3629;&#3585;", "&#3621;&#3635;&#3604;&#3633;&#3610;&#3607;&#3637;&#3656; :", "&#3649;&#3585;&#3657;&#3652;&#3586;&#3619;&#3634;&#3618;&#3585;&#3634;&#3619;&#3648;&#3621;&#3656;&#3609;", "&#3621;&#3610;&#3607;&#3633;&#3657;&#3591;&#3627;&#3617;&#3604;", "&#3648;&#3614;&#3636;&#3656;&#3617;&#3648;&#3586;&#3657;&#3634;&#3626;&#3641;&#3656;&#3619;&#3634;&#3618;&#3585;&#3634;&#3619;&#3648;&#3621;&#3656;&#3609;", "&#3594;&#3639;&#3656;&#3629; :", "&#3626;&#3619;&#3657;&#3634;&#3591;", "&#3648;&#3621;&#3656;&#3609; :", "&#3652;&#3615;&#3621;&#3660;", "&#3629;&#3633;&#3621;&#3610;&#3633;&#3617;", "&#3607;&#3633;&#3657;&#3591;&#3627;&#3617;&#3604;", "&#3606;&#3641;&#3585;&#3648;&#3621;&#3639;&#3629;&#3585;", "&#3648;&#3614;&#3636;&#3656;&#3617;", "&#3648;&#3621;&#3656;&#3609;", "&#3649;&#3585;&#3657;&#3652;&#3586;", "&#3651;&#3627;&#3617;&#3656;", "&#3648;&#3621;&#3639;&#3629;&#3585; :", "&#3588;&#3623;&#3610;&#3588;&#3640;&#3617;&#3585;&#3634;&#3619;&#3648;&#3621;&#3656;&#3609; :", "&#3619;&#3634;&#3618;&#3585;&#3634;&#3619;&#3648;&#3621;&#3656;&#3609; :", "&#3627;&#3617;&#3634;&#3618;&#3648;&#3621;&#3586;&#3607;&#3637;&#3656;&#3606;&#3641;&#3585;&#3648;&#3621;&#3639;&#3629;&#3585;&#3617;&#3634;&#3585;&#3607;&#3637;&#3656;&#3626;&#3640;&#3604;", "&#3588;&#3635;&#3649;&#3609;&#3632;&#3609;&#3635;&#3592;&#3634;&#3585; Keyteq", "&#3605;&#3619;&#3623;&#3592;&#3626;&#3629;&#3610;&#3648;&#3614;&#3639;&#3656;&#3629;&#3611;&#3619;&#3633;&#3610;&#3611;&#3619;&#3640;&#3591;&#3619;&#3640;&#3656;&#3609;&#3586;&#3629;&#3591;&#3595;&#3629;&#3615;&#3607;&#3660;&#3649;&#3623;&#3619;&#3660;", "&#3627;&#3609;&#3657;&#3634;&#3627;&#3621;&#3633;&#3585;", "&#3648;&#3593;&#3614;&#3634;&#3632; ID3", "&#3629;&#3633;&#3621;&#3610;&#3633;&#3617;", "&#3594;&#3639;&#3656;&#3629;&#3629;&#3633;&#3621;&#3610;&#3633;&#3617;", "&#3624;&#3636;&#3621;&#3611;&#3636;&#3609;", "&#3629;&#3633;&#3621;&#3611;&#3633;&#3617;&#3607;&#3637;&#3656;&#3606;&#3641;&#3585;&#3648;&#3621;&#3639;&#3629;&#3585;&#3607;&#3634;&#3585;&#3607;&#3637;&#3656;&#3626;&#3640;&#3604;&#3592;&#3634;&#3585;&#3624;&#3636;&#3621;&#3611;&#3636;&#3609;", "&#3648;&#3586;&#3657;&#3634;&#3594;&#3617;", "&#3619;&#3634;&#3618;&#3585;&#3634;&#3619;&#3648;&#3621;&#3656;&#3609;&#3607;&#3637;&#3656;&#3651;&#3627;&#3657;&#3651;&#3594;&#3657;&#3652;&#3604;&#3657;", "&#3612;&#3641;&#3657;&#3651;&#3594;&#3657;", "&#3626;&#3656;&#3623;&#3609;&#3612;&#3641;&#3657;&#3604;&#3641;&#3649;&#3621;&#3619;&#3632;&#3610;&#3610;", "&#3617;&#3634;&#3651;&#3627;&#3617;&#3656;", "&#3617;&#3634;&#3649;&#3619;&#3591;", "&#3629;&#3629;&#3585;&#3592;&#3634;&#3585;&#3619;&#3632;&#3610;&#3610;", "&#3605;&#3633;&#3623;&#3648;&#3621;&#3639;&#3629;&#3585;", "&#3605;&#3619;&#3623;&#3592;&#3626;&#3629;&#3610;", "&#3588;&#3635;&#3626;&#3633;&#3656;&#3591;&#3629;&#3639;&#3656;&#3609;", "&#3649;&#3585;&#3657;&#3652;&#3586;&#3612;&#3641;&#3657;&#3651;&#3594;&#3657;", "&#3648;&#3614;&#3636;&#3656;&#3617;&#3612;&#3641;&#3657;&#3651;&#3594;&#3657;", "&#3594;&#3639;&#3656;&#3629;&#3592;&#3619;&#3636;&#3591;", "&#3594;&#3639;&#3656;&#3629;&#3648;&#3614;&#3639;&#3656;&#3629;&#3648;&#3586;&#3657;&#3634;&#3619;&#3632;&#3610;&#3610;", "&#3648;&#3611;&#3621;&#3637;&#3656;&#3618;&#3609;&#3619;&#3627;&#3633;&#3626;&#3612;&#3656;&#3634;&#3609;?", "&#3619;&#3627;&#3633;&#3626;&#3612;&#3656;&#3634;&#3609;", "&#3586;&#3657;&#3629;&#3648;&#3626;&#3609;&#3629;&#3649;&#3609;&#3632;", "&#3619;&#3632;&#3604;&#3633;&#3610;&#3651;&#3609;&#3585;&#3634;&#3619;&#3651;&#3594;&#3657;&#3591;&#3634;&#3609;", "&#3585;&#3635;&#3621;&#3633;&#3591;&#3651;&#3594;&#3657;&#3591;&#3634;&#3609;&#3629;&#3618;&#3641;&#3656;", "&#3652;&#3617;&#3656;&#3652;&#3604;&#3657;&#3651;&#3594;&#3657;", "&#3621;&#3610;&#3612;&#3641;&#3657;&#3651;&#3594;&#3657;", "&#3609;&#3635;&#3612;&#3641;&#3657;&#3651;&#3594;&#3657;&#3629;&#3629;&#3585;&#3592;&#3634;&#3585;&#3585;&#3634;&#3619;&#3651;&#3594;&#3657;&#3591;&#3634;&#3609;", "refresh", "&#3648;&#3614;&#3636;&#3656;&#3617;&#3612;&#3641;&#3657;&#3651;&#3594;&#3657;&#3651;&#3627;&#3617;&#3656;", "&#3621;&#3610;&#3629;&#3629;&#3585;&#3592;&#3634;&#3585;&#3619;&#3632;&#3610;&#3610;", "&#3629;&#3629;&#3585;&#3592;&#3634;&#3585;&#3619;&#3632;&#3610;&#3610;", "&#3651;&#3594;&#3657;&#3588;&#3640;&#3603;&#3626;&#3617;&#3610;&#3633;&#3605;&#3636; EXTM3U", "&#3592;&#3635;&#3609;&#3623;&#3609;&#3649;&#3606;&#3623;&#3607;&#3637;&#3656;&#3605;&#3657;&#3629;&#3591;&#3585;&#3634;&#3619;&#3651;&#3627;&#3657;&#3649;&#3626;&#3604;&#3591; (&#3617;&#3634;&#3651;&#3627;&#3617;&#3656;/&#3617;&#3634;&#3649;&#3619;&#3591;)", "&#3592;&#3635;&#3609;&#3623;&#3609;&#3649;&#3606;&#3623;&#3626;&#3641;&#3591;&#3626;&#3640;&#3604;&#3651;&#3609;&#3585;&#3634;&#3619;&#3588;&#3657;&#3609;&#3627;&#3634;", "&#3618;&#3585;&#3648;&#3621;&#3636;&#3585;", "&#3648;&#3611;&#3636;&#3604; Directory", "&#3652;&#3611; Directory  : %1", "&#3604;&#3634;&#3623;&#3609;&#3660;&#3650;&#3627;&#3621;&#3604;", "&#3586;&#3638;&#3657;&#3609;&#3652;&#3611; 1 &#3619;&#3632;&#3604;&#3633;&#3610;", "&#3652;&#3611;&#3607;&#3637;&#3656; Directory &#3610;&#3609;&#3626;&#3640;&#3604;", "&#3605;&#3619;&#3623;&#3592;&#3626;&#3629;&#3610;&#3648;&#3614;&#3639;&#3656;&#3629;&#3611;&#3619;&#3633;&#3610;&#3611;&#3619;&#3640;&#3591;&#3619;&#3640;&#3656;&#3609;&#3586;&#3629;&#3591;&#3595;&#3629;&#3615;&#3607;&#3660;&#3649;&#3623;&#3619;&#3660;", "&#3612;&#3641;&#3657;&#3651;&#3594;&#3657;", "&#3616;&#3634;&#3625;&#3634;", "&#3605;&#3633;&#3623;&#3648;&#3621;&#3639;&#3629;&#3585;", "&#3648;&#3619;&#3636;&#3656;&#3617;&#3605;&#3657;&#3609;", "&#3626;&#3640;&#3656;&#3617;", "&#3585;&#3634;&#3619;&#3605;&#3633;&#3657;&#3591;&#3588;&#3656;&#3634;", "Directory &#3648;&#3585;&#3655;&#3610;&#3626;&#3639;&#3656;&#3629;", "&#3649;&#3627;&#3621;&#3656;&#3591; Stream", "&#3616;&#3634;&#3625;&#3605;&#3633;&#3657;&#3591;&#3605;&#3657;&#3609;", "&#3619;&#3632;&#3610;&#3610;  Windows", "&#3605;&#3657;&#3629;&#3591;&#3585;&#3634;&#3619;&#3651;&#3594;&#3657; Https", "&#3629;&#3609;&#3640;&#3597;&#3634;&#3605;&#3651;&#3627;&#3657;&#3648;&#3621;&#3639;&#3656;&#3629;&#3609;&#3648;&#3614;&#3621;&#3591;&#3652;&#3604;&#3657;", "&#3629;&#3609;&#3640;&#3597;&#3634;&#3605;&#3651;&#3627;&#3657;&#3604;&#3634;&#3623;&#3609;&#3660;&#3650;&#3627;&#3621;&#3604;&#3652;&#3604;&#3657;", "Session timeout", "&#3619;&#3634;&#3618;&#3591;&#3634;&#3609;&#3585;&#3634;&#3619; login &#3607;&#3637;&#3656;&#3612;&#3636;&#3604;&#3614;&#3621;&#3634;&#3604;", "&#3619;&#3629;&#3626;&#3633;&#3585;&#3588;&#3619;&#3641;&#3656;&#3585;&#3635;&#3621;&#3633;&#3591;&#3629;&#3656;&#3634;&#3609;&#3588;&#3656;&#3634;&#3592;&#3634;&#3585;&#3619;&#3632;&#3610;&#3610;", "&#3652;&#3617;&#3656;&#3626;&#3634;&#3617;&#3634;&#3619;&#3606;&#3648;&#3614;&#3636;&#3656;&#3617;&#3651;&#3609;&#3619;&#3634;&#3618;&#3585;&#3634;&#3619;&#3648;&#3621;&#3656;&#3609;&#3652;&#3604;&#3657;", "&#3612;&#3641;&#3657;&#3604;&#3641;&#3649;&#3621;&#3619;&#3632;&#3610;&#3610;", "&#3585;&#3619;&#3640;&#3603;&#3634;&#3648;&#3586;&#3657;&#3634;&#3619;&#3632;&#3610;&#3610;&#3604;&#3657;&#3623;&#3618; HTTPS &#3648;&#3614;&#3639;&#3656;&#3629;&#3648;&#3611;&#3621;&#3637;&#3656;&#3618;&#3609;", "&#3629;&#3609;&#3640;&#3597;&#3634;&#3605;&#3636;&#3651;&#3627;&#3657;&#3651;&#3594;&#3657; stream engine", "&#3594;&#3639;&#3656;&#3629;&#3648;&#3614;&#3621;&#3591;", "&#3624;&#3636;&#3621;&#3611;&#3636;&#3609;", "&#3629;&#3633;&#3621;&#3610;&#3633;&#3617;", "&#3586;&#3657;&#3629;&#3648;&#3626;&#3609;&#3629;&#3632;&#3649;&#3609;&#3632;", "&#3611;&#3637;", "&#3648;&#3614;&#3621;&#3591;&#3607;&#3637;&#3656;", "&#3649;&#3609;&#3623;", "&#3652;&#3617;&#3656;&#3605;&#3633;&#3657;&#3591;", "&#3588;&#3656;&#3634;&#3626;&#3641;&#3591;&#3626;&#3640;&#3604;&#3651;&#3609;&#3585;&#3634;&#3619;&#3604;&#3634;&#3623;&#3609;&#3660;&#3650;&#3627;&#3621;&#3604; (kbps)", "&#3612;&#3641;&#3657;&#3651;&#3594;&#3657;", "%1 &#3609;&#3634;&#3607;&#3637; - %2 &#3648;&#3614;&#3621;&#3591;", "%1 Kbit %2 &#3609;&#3634;&#3607;&#3637;", "&#3649;&#3609;&#3623;&#3648;&#3614;&#3621;&#3591; : %1", "wx", "%1 &#3623;&#3633;&#3609; %2 &#3594;&#3633;&#3656;&#3623;&#3650;&#3617;&#3591; %3 &#3609;&#3634;&#3607;&#3637; &#3651;&#3609;&#3585;&#3634;&#3619;&#3648;&#3621;&#3656;&#3609; %4 &#3652;&#3615;&#3621;&#3660; %5 mb", "&#3652;&#3617;&#3656;&#3614;&#3610;&#3626;&#3639;&#3656;&#3629;&#3607;&#3637;&#3656;&#3626;&#3634;&#3617;&#3634;&#3619;&#3648;&#3621;&#3656;&#3609;&#3652;&#3604;&#3657;", "&#3619;&#3627;&#3633;&#3626;&#3612;&#3656;&#3634;&#3609;&#3606;&#3641;&#3585;&#3648;&#3611;&#3621;&#3637;&#3656;&#3618;&#3609;&#3649;&#3621;&#3657;&#3623;", "&#3621;&#3591;&#3607;&#3632;&#3648;&#3610;&#3637;&#3618;&#3609;", "&#3585;&#3619;&#3640;&#3603;&#3634;&#3607;&#3635;&#3585;&#3634;&#3619;&#3648;&#3621;&#3639;&#3629;&#3585;&#3585;&#3656;&#3629;&#3609;", "&#3617;&#3637;&#3629;&#3632;&#3652;&#3619;&#3651;&#3627;&#3617;&#3656;", "&#3588;&#3621;&#3636;&#3585;&#3607;&#3637;&#3656;&#3609;&#3637;&#3656;&#3648;&#3614;&#3639;&#3656;&#3629;&#3586;&#3629;&#3588;&#3623;&#3634;&#3617;&#3594;&#3656;&#3623;&#3618;&#3648;&#3627;&#3621;&#3639;&#3629;", "&#3651;&#3594;&#3657;&#3619;&#3641;&#3611;&#3616;&#3634;&#3614;&#3592;&#3634;&#3585;&#3616;&#3634;&#3618;&#3609;&#3629;&#3585;", "&#3649;&#3627;&#3621;&#3656;&#3591;&#3586;&#3657;&#3629;&#3617;&#3641;&#3621;&#3619;&#3641;&#3611;&#3616;&#3634;&#3614;", "&#3619;&#3627;&#3633;&#3626;&#3612;&#3656;&#3634;&#3609;&#3648;&#3604;&#3636;&#3617;", "&#3619;&#3627;&#3633;&#3626;&#3612;&#3656;&#3634;&#3609;&#3648;&#3604;&#3636;&#3617;&#3652;&#3617;&#3656;&#3606;&#3641;&#3585;&#3605;&#3657;&#3629;&#3591;", "&#3619;&#3641;&#3611;&#3649;&#3610;&#3610;&#3585;&#3634;&#3619;&#3610;&#3637;&#3610;&#3629;&#3633;&#3604;", "&#3652;&#3617;&#3656;&#3626;&#3634;&#3617;&#3634;&#3619;&#3606;&#3607;&#3635;&#3585;&#3634;&#3619;&#3610;&#3637;&#3610;&#3629;&#3633;&#3604;&#3652;&#3615;&#3621;&#3660;&#3652;&#3604;&#3657;", "&#3614;&#3610;&#3652;&#3615;&#3621;&#3660;&#3607;&#3637;&#3656;&#3595;&#3657;&#3635;&#3585;&#3633;&#3609;&#3588;&#3639;&#3629;: \"%1\" \"%2\"", "&#3588;&#3640;&#3603;&#3649;&#3609;&#3656;&#3651;&#3592;&#3627;&#3619;&#3639;&#3629;&#3623;&#3656;&#3634;&#3592;&#3632;&#3621;&#3610;&#3619;&#3634;&#3618;&#3585;&#3634;&#3619;&#3648;&#3621;&#3656;&#3609;", "&#3648;&#3619;&#3637;&#3618;&#3591;&#3605;&#3634;&#3617;&#3621;&#3635;&#3604;&#3633;&#3610;&#3605;&#3633;&#3623;&#3629;&#3633;&#3585;&#3625;&#3619;", "&#3626;&#3640;&#3656;&#3617;", "&#3648;&#3619;&#3637;&#3618;&#3591;&#3621;&#3635;&#3604;&#3633;&#3610;", "&#3607;&#3637;&#3656;&#3617;&#3634;", "&#3651;&#3594;&#3657; javascript", "&#3588;&#3640;&#3603;&#3649;&#3609;&#3656;&#3651;&#3592;&#3623;&#3656;&#3634;&#3592;&#3632;&#3621;&#3610;&#3612;&#3641;&#3657;&#3651;&#3594;&#3657;&#3609;&#3637;&#3657;&#3629;&#3629;&#3585;&#3592;&#3634;&#3585;&#3619;&#3632;&#3610;&#3610;", "&#3604;&#3641;&#3611;&#3619;&#3632;&#3623;&#3633;&#3605;&#3636;&#3585;&#3634;&#3619;&#3651;&#3594;&#3657;", "&#3611;&#3619;&#3632;&#3623;&#3633;&#3605;&#3636;&#3585;&#3634;&#3619;&#3651;&#3594;&#3657;", "&#3627;&#3621;&#3633;&#3585;", "&#3651;&#3594;&#3657; css &#3616;&#3634;&#3618;&#3609;&#3629;&#3585;", "&#3621;&#3610;&#3607;&#3637;&#3656;&#3595;&#3657;&#3635;&#3585;&#3633;&#3609;", "&#3605;&#3585;&#3621;&#3591;", "&#3612;&#3636;&#3604;&#3614;&#3621;&#3634;&#3604;", "Stream", "(&#3649;&#3626;&#3604;&#3591;&#3649;&#3610;&#3610;)", "&#3652;&#3615;&#3621;&#3660;", "&#3629;&#3633;&#3621;&#3610;&#3633;&#3617;", " %1 &#3623;&#3633;&#3609; %2 &#3594;&#3633;&#3656;&#3623;&#3650;&#3617;&#3591; %3 &#3609;&#3634;&#3607;&#3637; %4 &#3623;&#3636;&#3609;&#3634;&#3607;&#3637; ", "&#3607;&#3633;&#3656;&#3623;&#3652;&#3611;", "&#3611;&#3619;&#3633;&#3610;&#3649;&#3605;&#3656;&#3591;", "Filehandling", "&#3588;&#3621;&#3636;&#3585; ? &#3648;&#3614;&#3639;&#3656;&#3629;&#3586;&#3629;&#3588;&#3623;&#3634;&#3617;&#3594;&#3656;&#3623;&#3618;&#3648;&#3627;&#3621;&#3639;&#3629;", "Sync &#3600;&#3634;&#3609;&#3586;&#3657;&#3629;&#3617;&#3641;&#3621;&#3629;&#3633;&#3605;&#3650;&#3609;&#3617;&#3633;&#3605;&#3636;", "&#3626;&#3656;&#3591;&#3626;&#3656;&#3623;&#3609;&#3586;&#3618;&#3634;&#3618;&#3652;&#3615;&#3621;&#3660;", "&#3629;&#3609;&#3640;&#3597;&#3634;&#3605; stream &#3607;&#3637;&#3656;&#3652;&#3617;&#3656;&#3652;&#3604;&#3657;&#3619;&#3633;&#3610;&#3585;&#3634;&#3619;&#3605;&#3619;&#3623;&#3592;&#3626;&#3629;&#3610;", "Include headers", "javascript &#3616;&#3634;&#3618;&#3609;&#3629;&#3585; ", "&#3627;&#3609;&#3657;&#3634;&#3627;&#3621;&#3633;&#3585;", "&#3649;&#3626;&#3604;&#3591;&#3626;&#3656;&#3623;&#3609; Keyteq &#3609;&#3635;&#3648;&#3626;&#3609;&#3629;", "&#3649;&#3626;&#3604;&#3591;&#3626;&#3656;&#3623;&#3609;&#3611;&#3619;&#3633;&#3610;&#3611;&#3619;&#3640;&#3591;&#3619;&#3632;&#3610;&#3610;", "&#3649;&#3626;&#3604;&#3591;&#3626;&#3606;&#3636;&#3605;&#3636;", "&#3648;&#3586;&#3637;&#3618;&#3609; ID3v2 &#3604;&#3657;&#3623;&#3618; stream", "&#3612;&#3641;&#3657;&#3651;&#3594;&#3657;&#3626;&#3634;&#3617;&#3634;&#3619;&#3606;&#3621;&#3591;&#3607;&#3632;&#3648;&#3610;&#3637;&#3618;&#3609;&#3652;&#3604;&#3657;", "&#3594;&#3636;&#3604;&#3652;&#3615;&#3621;&#3660;", "&#3651;&#3594;&#3656;", "&#3652;&#3617;&#3656;&#3651;&#3594;&#3656;", "&#3626;&#3656;&#3623;&#3609;&#3586;&#3618;&#3634;&#3618;", "MIME", "&#3619;&#3623;&#3617;&#3651;&#3609; M3U", "&#3649;&#3585;&#3657;&#3651;&#3586;&#3594;&#3609;&#3636;&#3604;&#3652;&#3615;&#3621;&#3660;", "&#3649;&#3609;&#3656;&#3651;&#3592;&#3627;&#3619;&#3639;&#3629;&#3652;&#3617;&#3656;");

$klang[26] = array('NewNorwegian', 'ISO-8859-1', 'Nynorsk', 'Kva er mest spelt?', 'Kva er nytt?', 'Sшk', '(berre %1 vist)', 'sek', 'Sшkjeresultat: \'%1\'', 'Fann', 'Ingen.', 'Val for oppdatering av sшkjedatabase', 'Slett ubrukte rekkjer', 'Regenerere ID3-informasjon?', 'Problemlшysingsmodus', 'Oppdater', 'Avbryt', 'Oppdaterer sшkjedatabase', 'Fann %1 filer', 'Kunne ikkje lese fil: %1, hoppa over.', 'Installert: %1<br />Oppdatert: %2<br />Sшkjer: ', 'Sшkjer: ', 'Feila - spшrring: %1', 'Kunne ikkje lese denne fila: %1. Hoppa over', 'Fjerna lenkje til: %1', '<br /><b>Resultat:</b><br />Gjekk gjennom %6 filer pе %7 sekund.<br />La til: %1<br />Oppdaterte: %2<br />Sletta: %3<br />Feila: %4<br />Hoppa over: %5<br />Merka for sletting: %8</br>', 'Ferdig.', 'Lukk', 'Fann ingen filer her: "%1"', 'kPlaylist: Innlogging', 'Albumliste for artist: %1', 'Snшggvelg %1', 'Ingen lеtar valde. Spelelista vart ikkje oppdatert.', 'Speleliste oppdatert!', 'Attende', 'Speleliste lagt til!', 'Husk е oppdatere sida.', 'Logg inn:', 'Passord', 'Advarsel! Dette er ei privat vevside. All aktivitet vert loggfшrt.', 'Logg inn', 'SSL krevst for innlogging', 'Spel', 'Slett', 'Delte:', 'Lagre', 'Kontroller speleliste: "%1" - %2 titlar', 'Redigerar', 'Visar', 'Vel', 'Sek', 'Status', 'Informasjon', 'Slett', 'Navn', 'Totalt:', 'Feil', 'Handling pе valde:', 'Sekvens', 'rediger speleliste', 'Slett denne oppfшringa', 'ny speleliste', 'Namn:', 'Opprett', 'Spel:', 'Fil', 'Album', 'Alle', 'Valde', 'Legg til', 'Spel', 'rediger', 'ny', 'Vel:', 'Spelekontroll:', 'Speleliste:', 'Numerisk hurtigval', 'Keyteq gjev deg:', '(sjе etter ny versjon)', 'Heimeside', 'Berre ID3', 'album', 'tittel', 'artist', 'Snшggvelg album frе artist', 'vis', 'Delte spelelister', 'Brukarar', 'Administrasjon', 'Kva er nytt', 'Mest spelt', 'Logg ut', 'Val', 'Sjekk', 'Mine Alternativ', 'Endre brukarinformasjon', 'ny brukar', 'Fullt namn', 'Brukarnamn', 'Endre passord?', 'Passord', 'Kommentar', 'Tilgangsnivе', 'Pе', 'Av', 'Slett brukar', 'Logg ut brukar', 'Oppdater', 'Ny brukar', 'slett', 'logg ut', 'Bruk EXTM3U-eigenskapar?', 'Kor mange resultat skal visast (mest spelt/nytt)?', 'Maks antal viste sшkjeresultat:', 'Nullstill', 'Opne katalog', 'Gе til katalog: %1', 'Last ned', 'Gе opp eitt nivе', 'Gе til hovudkatalog', 'Sjе etter ny versjon', 'brukarar', 'Sprеk', 'val', 'Tilgong blokka', 'Vilkеrleg rekkjefшlgje:', 'Innstillingar', 'Hovudkatalog', 'Hovudadresse for straum', 'Standardsprеk', 'Er dette eit Windows-system?', 'Krev HTTPS?', 'Tillat spoling?', 'Tillat nedlastingar?', 'Tidsgrense for innlogging (sek):', 'Rapportere mislykka innloggingsforsшk?', 'Vent litt - hentar filliste', 'Speleliste kunne ikkje leggjast til!', 'Administrator', 'Logg inn med HTTPS for е endre.', 'Aktiver innebygd straumfunksjon?', 'Tittel', 'Artist', 'Album', 'Kommentar', 'Еr', 'Lеtnummer', 'Sjanger', 'ikkje sett', 'Maksimal fart for nedlasting (kbps)?', 'Brukar', '%1 minutt, %2 titlar', '%1 kbit, %2 minutt', 'Sjangerliste: %1', 'Gе', 'Speletid: %1d %2t %3m, %4 filer, %5 mb', 'Ingen relevante ressursar her.', 'Passord endra!', 'Ny brukar', 'Vennligst gjer eit val!', 'Kva er oppdatering?', 'Klikk her for hjelp.', 'Bruke eksterne bilete?', 'Plassering for eksterne bilete:', 'Eksisterande passord:', 'Det eksisterande passordet er feil!', 'Шnska arkivprogram:', 'Arkivet kunne ikkje opprettast.', 'Fann mogeleg duplikat: %1 - %2', 'Verkeleg slette speleliste?', 'Alfabetisk', 'Tilfeldig', 'Sorter', 'Original', 'Bruk javascript?', 'Er du sikker pе at du vil slette denne brukaren?', 'Vis historikk', 'historikk', 'Rekkjer', 'Ekstern CSS-fil:', 'Fjern duplikat', 'OK', 'FEIL', 'Sanntidsstraum', '(vis som)', 'filer', 'album', '%1d %2t %3m %4s', 'Generelt', 'Skreddarsy', 'Filhandsaming', 'Trykk pе "?" for hjelp.', 'Automatisk synkronisering av databasen?', 'Send filending?', 'Tillat ikkje-autoriserte straumar?', 'Inkluder header-linjer?', 'Eksternt javascript:', 'Heimeside', 'Vis "Keyteq gjev deg"-del?', 'Vis oppgraderingsdel?', 'Vis statistikk?', 'Inkluder ID3v2-informasjon i straumen?', '"Ny brukar"-funksjonalitet?', 'Filtypar', 'Ja', 'Nei', 'Filending', 'MIME', 'Inkluder i M3U?', 'Endre filtype', 'Sikker?', 'Optimistisk filsjekk?', 'Tilfeldig val', 'Modus', 'Speleliste', 'Ingen, direkte', 'Mine favorittar', 'Ingen treff.', 'Hшgste antal treff', 'Rekkjefшlgje', 'Slе pе stшtte for LAME', 'Deaktivert', 'Tillat bruk av LAME?', 'E-post', 'Tillat sending av filer via e-post?', 'SMTP-tenar:', 'SMTP-port:', 'Send e-post til:', 'Melding:', 'Send', 'E-post sendt!', 'Tillat opplasting?', 'Opplastingsmappe:', 'Slе pе mp3mail?', 'Last opp', 'Fila er lasta opp!', 'Fila kunne ikkje lastast opp!', 'Du mе bruke cookies for е logge inn!', 'Periode', 'Nеr som helst', 'Denne veka', 'Denne mеnaden', 'Fшrre mеnaden', 'Treff', 'LAME-kommando:', 'Vis omslag for album?', 'Albumfiler:', 'Endre storleiken pе albumbilete?', 'Albumhшgde:', 'Albumvidde:', 'E-post-metode:', 'Direkte', 'PEAR', 'Vent', 'Vжr vennleg е skrive inn ei gyldig e-postadresse under innstillingar!', 'Integrerte spelelister?', 'Vis album frе URL?', 'Album-URL', 'Kunne ikkje sende!', 'Ny brukar lagt til!', 'Opprette arkiv', 'Arkivet er sletta.', 'Brukarinformasjon oppdatert!', 'Musikktilpassing', '%1 innlegg filtrert bort', 'Logge tilgong', 'Synleg', 'Arkivert', 'Oppslagstavle', 'Skrive den %1 av %2', 'meir', 'Publiser', '%1 mb', '%1 kb', '%1 bitar', 'Rekursivt', 'Fшrre', 'Neste', 'Gе til side %1', 'Side:', 'Aldri spelt', 'Manuell godkjenning av nye brukarar?', 'Avventar behandling', 'Aktiver', 'Alle felt merka med * er obligatoriske', 'Brukarkontoen din vil verte sjekka og aktivert manuelt.', 'Siste straumar', 'Hugs meg', 'Stil', 'finn', 'Skriv inn stiar е sшkje i:', 'Bruk valde?', 'Speletid min/maks', 'Minutt', 'm3u', 'asx (WMA)', 'Dersom oppdateringa stoggar, trykk her: %1', 'Fшlg symbolske lenkjer?', 'Mal for presentasjon av filliste:', 'Aktiver URL-tryggjing?', 'Tillete filtypar for opplasting:', 'Filtypen er ikkje tillete.', 'Spelelista er tom!', 'Tekstar', 'URL til tekstar', 'Vis lenkje til tekstar?', '(eller?)', 'Ukjend brukarnamn eller passord.', 'Maks filstorleik for opplasting: %1', 'Opne offentleg RSS-tilgong?');

$klang[27] = array('Japanese', 'EUC-JP', 'Japanese', 'їНµ¤¶К', 'ї·¶К', 'ёЎєч', '(%1 ·пЙЅјЁ)', 'ЙГ', 'ёЎєч·лІМ: \'%1\'', 'ёЎєч·лІМ', 'ё«¤Д¤«¤к¤Ю¤»¤уЎҐ', 'ёЎєчҐЗЎјҐїҐЩЎјҐ№№№ї· - ҐЄҐЧҐ·ҐзҐу', 'М¤»ИНС¤О№аМЬ¤тєпЅь¤№¤л', 'ID3 ¤тєЖ№ЅГЫ¤№¤л', 'ҐЗҐРҐГҐ°ҐвЎјҐЙ', '№№ї·', 'Ґ­ҐгҐуҐ»Ґл', 'ёЎєчҐЗЎјҐїҐЩЎјҐ№¤О№№ї·', '%1 ·п¤ОҐХҐЎҐ¤Ґл¤¬ё«¤Д¤«¤к¤Ю¤·¤їЎҐ', 'ҐХҐЎҐ¤Ґл %1 ¤тІт·и¤З¤­¤Ю¤»¤уЎҐҐ№Ґ­ҐГҐЧ¤·¤Ю¤№ЎҐ', 'Ґ¤ҐуҐ№ҐИЎјҐл: %1 - №№ї·: %2Ў¤Ґ№Ґ­ҐгҐу:', 'Ґ№Ґ­ҐгҐу:', 'јєЗФ - ҐЇҐЁҐкЎј: %1', 'ҐХҐЎҐ¤Ґл %1 ¤тЖЙ¤Яји¤м¤Ю¤»¤уЎҐҐ№Ґ­ҐГҐЧ¤·¤Ю¤№ЎҐ', 'єпЅь: %1', 'Бґ %6 ·пГж - ДЙІГ %1 ·пЎ¤№№ї· %2 ·пЎ¤єпЅь %3 ·пЎ¤јєЗФ %4 ·пЎ¤Ґ№Ґ­ҐГҐЧ %5 ·п - %7 ЙГ - %8 ·п¤ОҐХҐЎҐ¤Ґл¤¬єпЅь¤µ¤м¤Ю¤№ЎҐ', 'ЅЄО»', 'КД¤ё¤л', 'ҐХҐЎҐ¤Ґл¤¬ё«¤Д¤«¤к¤Ю¤»¤у: "%1"', 'kplaylist ҐнҐ°Ґ¤Ґу', 'ҐўҐлҐРҐа°мНч - ҐўЎјҐЖҐЈҐ№ҐИ: %1', 'ҐўҐлҐРҐа°мНч %1', '¶К¤¬БЄВт¤µ¤м¤Ж¤¤¤Ю¤»¤уЎҐҐЧҐмҐ¤ҐкҐ№ҐИ¤П№№ї·¤µ¤м¤Ю¤»¤уЎҐ', 'ҐЧҐмҐ¤ҐкҐ№ҐИ¤т№№ї·¤·¤Ю¤·¤їЎЄ', 'Мб¤л', 'ҐЧҐмҐ¤ҐкҐ№ҐИ¤тДЙІГ¤·¤Ю¤·¤їЎЄ', 'ҐЪЎјҐё¤тєЖЖЙ¤Я№ю¤Я¤·¤Ж¤Ї¤А¤µ¤¤ЎҐ', 'ҐнҐ°Ґ¤ҐуМѕ:', 'ҐСҐ№ҐпЎјҐЙ:', 'Гн°ХЎЄ¤і¤і¤П»дЕЄ¤КҐ¦Ґ§ҐЦҐµҐ¤ҐИ¤З¤№ЎҐБаєо¤П¤№¤Щ¤Жµ­Пї¤µ¤м¤Ю¤№ЎҐ', 'ҐнҐ°Ґ¤Ґу', 'ҐнҐ°Ґ¤Ґу¤Л¤П SSL ¤¬Й¬НЧ¤З¤№ЎҐ', 'єЖАё', 'єпЅь', '¶¦Н­:', 'КЭВё', 'ҐЧҐмҐ¤ҐкҐ№ҐИ: "%1" - %2 ҐїҐ¤ҐИҐл', 'ҐЁҐЗҐЈҐї', 'ҐУҐеЎјҐў', 'БЄВт', 'ИЦ№ж', 'Ґ№ҐЖЎјҐїҐ№', 'ѕрКу', 'єпЅь', 'МѕБ°', '№з·Ч:', 'ҐЁҐйЎј', 'БЄВт¤·¤їҐХҐЎҐ¤Ґл¤т', '¶КЅз:', 'ҐЧҐмҐ¤ҐкҐ№ҐИ¤ОКФЅё', '¤і¤О№аМЬ¤тєпЅь¤№¤л', 'ҐЧҐмҐ¤ҐкҐ№ҐИ¤ОДЙІГ', 'МѕБ°:', 'єоА®', 'єЖАё:', 'ҐХҐЎҐ¤Ґл', 'ҐўҐлҐРҐа', '¤№¤Щ¤Ж', 'БЄВт¶К', 'ДЙІГ', 'єЖАё', 'КФЅё', 'ї·µ¬', 'БЄВт:', 'єЖАёҐбҐЛҐеЎј:', 'ҐЧҐмҐ¤ҐкҐ№ҐИ:', 'ҐўҐлҐРҐа°мНч їф»ъ', 'Keyteq gives you:', '(ҐўҐГҐЧҐЗЎјҐИ¤ОіОЗ§)', 'kplaylist ¤ОҐЫЎјҐаҐЪЎјҐё', 'ID3 ¤О¤Я', 'ҐўҐлҐРҐа', 'ҐїҐ¤ҐИҐл', 'ҐўЎјҐЖҐЈҐ№ҐИ', 'ҐўЎјҐЖҐЈҐ№ҐИМѕ¤«¤йҐўҐлҐРҐа¤тБЄВт', 'ЙЅјЁ', '¶¦Н­ҐЧҐмҐ¤ҐкҐ№ҐИ', 'ҐжЎјҐ¶Ўј', 'ґЙНэҐбҐЛҐеЎј', 'ї·¶К', 'їНµ¤¶К', 'ҐнҐ°ҐўҐ¦ҐИ', 'ҐЄҐЧҐ·ҐзҐу', 'ҐБҐ§ҐГҐЇ', 'ҐжЎјҐ¶ЎјҐбҐЛҐеЎј', 'ҐжЎјҐ¶Ўј¤ОКФЅё', 'ҐжЎјҐ¶Ўј¤ОДЙІГ', '»бМѕ', 'ҐнҐ°Ґ¤Ґу', 'ҐСҐ№ҐпЎјҐЙ¤ОКС№№', 'ҐСҐ№ҐпЎјҐЙ', 'ҐіҐбҐуҐИ', 'ҐўҐЇҐ»Ґ№ҐмҐЩҐл', 'ҐЄҐу', 'ҐЄҐХ', 'ҐжЎјҐ¶Ўј¤ОєпЅь', 'ҐжЎјҐ¶Ўј¤ОҐнҐ°ҐўҐ¦ҐИ', '№№ї·', 'ї·µ¬ҐжЎјҐ¶Ўј', 'єпЅь', 'ҐнҐ°ҐўҐ¦ҐИ', 'EXTM3U ¤т»ИНС', 'ЙЅјЁ·пїф (ї·¶К/їНµ¤¶К)', 'ёЎєчЙЅјЁ·пїф', 'ҐкҐ»ҐГҐИ', 'ҐЗҐЈҐмҐЇҐИҐк¤ті«¤Ї', 'ҐЗҐЈҐмҐЇҐИҐк %1 ¤Л°ЬЖ°', 'ҐАҐ¦ҐуҐнЎјҐЙ', '°мі¬БШѕе¤Л°ЬЖ°', 'ҐлЎјҐИҐЗҐЈҐмҐЇҐИҐк¤Л°ЬЖ°', 'ҐўҐГҐЧҐ°ҐмЎјҐЙ¤ОіОЗ§', 'ҐжЎјҐ¶Ўј', 'ёАём', 'ҐЄҐЧҐ·ҐзҐу', 'Booted', 'Ґ·ҐгҐГҐХҐл:', 'АЯДк', 'ҐЩЎјҐ№ҐЗҐЈҐмҐЇҐИҐк', 'Ґ№ҐИҐкЎјҐа URL', 'ҐЗҐХҐ©ҐлҐИ¤ОёАём', 'Windows ¤т»ИНС', 'Require HTTPS', 'Ґ·ЎјҐЇ¤тµцІД¤№¤л', 'ҐАҐ¦ҐуҐнЎјҐЙ¤тµцІД¤№¤л', 'Ґ»ҐГҐ·ҐзҐу¤ОҐїҐ¤ҐаҐўҐ¦ҐИ»юґЦ', 'ҐнҐ°Ґ¤ҐујєЗФ¤тКу№р¤№¤л', 'Hold on - fetching file list', 'ҐЧҐмҐ¤ҐкҐ№ҐИ¤¬ДЙІГ¤З¤­¤Ю¤»¤уЎЄ', 'ґЙНэ', 'HTTPS ¤тНшНС¤·¤ЖҐнҐ°Ґ¤Ґу¤№¤л', 'Ґ№ҐИҐкЎјҐаҐЁҐуҐёҐу¤тН­ёъ¤Л¤№¤л', 'ҐїҐ¤ҐИҐл', 'ҐўЎјҐЖҐЈҐ№ҐИ', 'ҐўҐлҐРҐа', 'ҐіҐбҐуҐИ', 'ЗЇ', 'ҐИҐйҐГҐЇ', 'ҐёҐгҐуҐл', 'М¤АЯДк', 'єЗВзҐАҐ¦ҐуҐнЎјҐЙВ®ЕЩ (kbps)', 'ҐжЎјҐ¶Ўј', '%1 К¬ - %2 ҐїҐ¤ҐИҐл', '%1 kbit %2 К¬', 'ҐёҐгҐуҐл°мНч: %1', 'јВ№Ф', '±йБХ»юґЦ %1 Жь %2 »юґЦ %3 К¬ %4 ҐХҐЎҐ¤Ґл %5 mb', 'No relevant resources here.', 'ҐСҐ№ҐпЎјҐЙ¤тКС№№¤·¤Ю¤·¤їЎЄ', 'ҐµҐ¤ҐуҐўҐГҐЧ', 'БЄВт¤·¤Ж¤Ї¤А¤µ¤¤ЎЄ', 'ҐўҐГҐЧҐЗЎјҐИ¤И¤ПІї¤З¤№¤«Ў©', '¤і¤і¤тҐЇҐкҐГҐЇ¤№¤л¤ИҐШҐлҐЧ¤тЙЅјЁ¤·¤Ю¤№', 'і°Йф¤ОІиБь¤т»ИНС¤№¤л', 'і°Йф¤ОІиБь¤ОҐСҐ№', 'ёЅєЯ¤ОҐСҐ№ҐпЎјҐЙ', 'ёЅєЯ¤ОҐСҐ№ҐпЎјҐЙ¤¬°мГЧ¤·¤Ю¤»¤уЎЄ', 'НҐАи¤№¤л°µЅМ·Бј°', '°µЅМ¤З¤­¤Ю¤»¤у¤З¤·¤ї', '¤Є¤Ѕ¤й¤ЇҐХҐЎҐ¤Ґл¤¬ЅЕКЈ¤·¤Ж¤¤¤Ю¤№: "%1" "%2"', 'ЛЬЕц¤ЛҐЧҐмҐ¤ҐкҐ№ҐИ¤тєпЅь¤·¤Ю¤№¤«Ў©', 'ҐўҐлҐХҐЎҐЩҐГҐИЅз', 'ҐйҐуҐАҐа', 'А°Оу', 'ҐЄҐкҐёҐКҐл', 'Javascript ¤т»ИНС¤№¤л', 'ЛЬЕц¤Л¤і¤ОҐжЎјҐ¶Ўј¤тєпЅь¤·¤Ю¤№¤«Ў©', 'НъОт¤тЙЅјЁ¤№¤л', 'НъОт', 'Оу', 'і°Йф CSS ҐХҐЎҐ¤Ґл', 'ЅЕКЈ№аМЬ¤тєпЅь¤№¤л', 'OK', 'ҐЁҐйЎј', 'Ґ№ҐИҐкЎјҐа', '(ЙЅјЁКэЛЎ)', 'ҐХҐЎҐ¤Ґл', 'ҐўҐлҐРҐа', '%1 Жь %2 »юґЦ %3 К¬ %4 ЙГ', '°мИМ', 'Ґ«Ґ№ҐїҐЮҐ¤Ґє', 'ҐХҐЎҐ¤ҐлБаєо', '? ¤тҐЇҐкҐГҐЇ¤№¤л¤ИҐШҐлҐЧ¤тЙЅјЁ¤·¤Ю¤№', 'ј«Ж°ҐЗЎјҐїҐЩЎјҐ№Ж±ґь', 'ҐХҐЎҐ¤Ґл¤ОіИДҐ»Т¤тБч¤л', 'ҐнҐ°Ґ¤Ґу¤К¤·¤ОҐ№ҐИҐкЎјҐа¤тµцІД¤№¤л', 'ҐШҐГҐА¤тґЮ¤б¤л', 'і°Йф Javascript', 'ҐЫЎјҐаҐЪЎјҐё', 'Keyteq gives you ¤тЙЅјЁ', '№№ї·¤ОҐБҐ§ҐГҐЇ¤тЙЅјЁ', 'Еэ·Ч¤тЙЅјЁ', 'Ґ№ҐИҐкЎјҐа¤Л ID 3v2 ¤тБч¤л', 'ҐжЎјҐ¶Ўј¤ОҐµҐ¤ҐуҐўҐГҐЧ¤тН­ёъ¤Л¤№¤л', 'ҐХҐЎҐ¤ҐлҐїҐ¤ҐЧ', '¤П¤¤', '¤¤¤¤¤Ё', 'іИДҐ»Т', 'MIME', 'M3U ¤ЛґЮ¤б¤л', 'ҐХҐЎҐ¤ҐлҐїҐ¤ҐЧ¤ОКФЅё', '¤Ы¤у¤И¤¦¤З¤№¤«Ў©', 'іЪґСЕЄ¤КҐХҐЎҐ¤ҐліОЗ§', 'ҐйҐуҐАҐаАёА®', 'ҐвЎјҐЙ', 'ҐЧҐмҐ¤ҐкҐ№ҐИ', '»ИНС¤·¤К¤¤', '¤Єµ¤¤ЛЖю¤к', 'Ії¤вё«¤Д¤«¤к¤Ю¤»¤у¤З¤·¤ї', 'їНµ¤', 'ЅзЅш', 'LAME ҐµҐЭЎјҐИ¤тН­ёъ¤Л¤№¤л', 'Мµёъ', 'LAME ¤О»ИНС¤тµцІД¤№¤л', 'ҐбЎјҐлҐўҐЙҐмҐ№', 'ҐХҐЎҐ¤Ґл¤тҐбЎјҐл¤ЗБчї®¤№¤л¤і¤И¤тµцІД¤№¤л', 'SMTP ҐµЎјҐРЎј', 'SMTP ҐЭЎјҐИ', '°ёАи', 'ҐбҐГҐ»ЎјҐё', 'Бчї®', 'ҐбЎјҐл¤тБчї®¤·¤Ю¤·¤їЎЄ', 'ҐўҐГҐЧҐнЎјҐЙ¤тН­ёъ¤Л¤№¤л', 'ҐўҐГҐЧҐнЎјҐЙ¤№¤лҐЗҐЈҐмҐЇҐИҐк', 'mp3mail ¤тН­ёъ¤Л¤№¤л', 'ҐўҐГҐЧҐнЎјҐЙ', 'ҐХҐЎҐ¤Ґл¤тҐўҐГҐЧҐнЎјҐЙ¤·¤Ю¤·¤їЎЄ', 'ҐХҐЎҐ¤Ґл¤тҐўҐГҐЧҐнЎјҐЙ¤З¤­¤Ю¤»¤у¤З¤·¤їЎЄ', 'ҐнҐ°Ґ¤Ґу¤№¤л¤Л¤ПҐЇҐГҐ­Ўј¤тН­ёъ¤Л¤·¤Ж¤Ї¤А¤µ¤¤ЎЄ', 'ґьґЦ', 'єЈ¤Ю¤З', 'єЈЅµ', 'єЈ·о', 'Аи·о', 'ҐТҐГҐИ', 'LAME ҐіҐЮҐуҐЙ', 'ҐўҐлҐРҐаҐ«ҐРЎј¤тЙЅјЁ¤№¤л', 'ҐўҐлҐРҐаҐХҐЎҐ¤ҐлМѕ', 'ІиБь¤ОҐµҐ¤Ґє¤тКС№№¤№¤л', 'ІиБь¤О№в¤µ', 'ІиБь¤ОЙэ', 'ҐбЎјҐлБчї®КэЛЎ', 'ДѕАЬ', 'Pear', 'Wait!', 'Н­ёъ¤КҐбЎјҐлҐўҐЙҐмҐ№¤тЖюОП¤·¤Ж¤Ї¤А¤µ¤¤ЎЄ', 'ҐЧҐмҐ¤ҐкҐ№ҐИ¤тҐ¤ҐуҐйҐ¤Ґу¤Л¤№¤л', 'URL ¤«¤йҐўҐлҐРҐа¤тЙЅјЁ¤№¤л', 'ҐўҐлҐРҐа¤О URL', 'Бчї®¤З¤­¤Ю¤»¤уЎЄ', 'ҐжЎјҐ¶Ўј¤тДЙІГ¤·¤Ю¤·¤їЎЄ', '°µЅМҐХҐЎҐ¤ҐлєоА®', '°µЅМҐХҐЎҐ¤Ґл¤тєпЅь¤·¤Ю¤·¤їЎҐ', 'ҐжЎјҐ¶Ўј¤т№№ї·¤·¤Ю¤·¤їЎЄ', 'Music match', '%1 №аМЬ¤¬ҐХҐЈҐлҐїЎј¤µ¤м¤Ж¤¤¤Ю¤№', 'ҐўҐЇҐ»Ґ№ҐнҐ°¤Лµ­Пї', 'ЙЅјЁ¤№¤л', 'Archived', '·ЗјЁИД', '%2 ¤ОИЇёА %1', '¤в¤Г¤ИЙЅјЁ', 'ёші«', '%1 mb', '%1 kb', '%1 bytes', 'Recursive', 'Б°', 'јЎ', '%1 ҐЪЎјҐёМЬ¤тЙЅјЁ', 'ҐЪЎјҐё:', 'М¤єЖАё', 'јкЖ°¤ЗЕРПї¤тјх¤±ЙХ¤±¤л', 'КЭО±', 'µцІД', '*¤О¤ў¤л№аМЬ¤ПЙ¬їЬ№аМЬ¤З¤№ЎЈ', 'ЕРПїЖвНЖ¤тіОЗ§ёеҐўҐ«Ґ¦ҐуҐИ¤тИЇ№Ф¤·¤Ю¤№ЎЈ', 'єЗ¶б¤ОєЖАё', 'µ­І±¤№¤л', 'Ґ№ҐїҐ¤Ґл', 'Гµ¤№', 'ёЎєч¤№¤лҐСҐ№¤тЖюОП', 'БЄВт№аМЬ¤т»ИНС');

$klang[28] = array('Icelandic', 'ISO-8859-1', 'Нslenska', 'Vinsжlt', 'Nэtt', 'Leita', '(sэni bara fyrstu %1)', 'sek', 'Niрurstцрur leitar aр \'%1\'', 'Fann', 'Ekkert.', 'Uppfжra valkosti leitargagnagrunns', 'Eyрa уnotuрum fжrslum?', 'Endurbyggja ID3 upplэsingar?', 'Aflъsa kerfiр?', 'Uppfжra', 'Hжtta viр', 'Uppfжra leitargagnagrunn', 'Fann %1 skrб(r).', 'Gat ekki greint skrбna "%1" og sleppi henni юvн.', 'Hef sett inn: %1 - Uppfжrt: %2, skoрa:', 'Skoрa:', 'Mistуkst - beiрni: %1', 'Gat ekki lesiр skrбna "%1" og sleppi henni юvн.', 'Fjarlжgрi tengingu б %1', 'Hef sett inn %1, uppfжrt %2, fjarlжgt %3 en юar af mistуkust %4 skrбningar og %5 var sleppt af alls %6 skrбm - Tуk %7 sek - %8 merktar til eyрingar.', 'Lokiр', 'Loka', 'Fann engar skrбr н "%1"', 'Innskrбning', 'Plцtur meр flytjandanum %1', 'Finna vinsжlt meр %1', 'Engin lцg voru valin.  Lagalisti var ekki uppfжrрur.', 'Lagalisti uppfжrрur!', 'Tilbaka', 'Lagalista bжtt viр!', 'Mundu aр endurhlaрa sнрuna.', 'Notendanafn:', 'Lykilorр:', 'Athugaрu aр юessi sнрa er til einkanota eingцngu.  Allar tengingar eru skrбрar.', 'Innskrбning', 'Innskrбning mцguleg yfir SSL', 'Spila', 'Eyрa', 'Deilt meр:', 'Vista', 'Stэra lagalista "%1" meр %2 titla', 'Breyta', 'Skoрa', 'Velja', 'Rцр', 'Staрa', 'Upplэsingar', 'Eyрa', 'Nafn', 'Alls:', 'Villa', 'Framkvжma aрgerр б vцldum lцgum', 'Rцр:', 'Breyta lagalista', 'Eyрa fжrslu', 'Bжta viр lagalista', 'Nafn:', 'Bъa til', 'Spila:', 'Skrб', 'Plata', 'Allt', 'Valiр', 'Bжta viр', 'Spila', 'Breyta', 'Nэtt', 'Velja:', 'Spila:', 'Lagalisti:', 'Hotselect numeric', 'Keyteq fжrir юйr', '(kanna meр uppfжrslu)', 'Forsнрa', 'Einungis ID3 tцgg', 'Plata', 'Titill', 'Flytjandi', 'Hotselect album from artist', 'Skoрa', 'Sameiginlegir lagalistar', 'Notendur', 'Kerfisstjуrn', 'Hvaр er nэtt', 'Hvaр er vinsжlt', 'Ъtskrбning', 'Valkostir', 'Kanna', 'Mitt', 'Breyta notanda', 'Nэr notandi', 'Fullt nafn', 'Notendanafn', 'Breyta lykilorрi?', 'Lykilorр', 'Athugasemd', 'Aрgangsstig', 'Virkur', 'Уvirkur', 'Eyрa notanda', 'Skrб notanda ъt', 'Endurhlaрa', 'Nэr notandi', 'Eyрa', 'Ъtskrб', 'Nota EXTM3U eiginleika?', 'Hversu margar fжrslur б aр sэna (af nэju/vinsжlu)?', 'Hбmarsksfjцldi leitarniрurstaрna', 'Endurstilla', 'Opna mцppu', 'Fara н mцppu: %1', 'Sжkja', 'Fara eina mцppu uppбviр', 'Fara н efstu mцppu', 'Kanna meр uppfжrslur', 'Notendur', 'Tungumбl', 'Valkostir', 'Sparkaрi', 'Uppstokka:', 'Stillingar', 'Grunnmappa', 'Stream location', 'Sjбlfvaliр tungumбl', 'Vefюjуninn keyrir б Windows', 'Krefjast HTTPS aрgangs', 'Leyfa aр spуla бfram н lцgum', 'Leyfa niрurhal б lцgum', 'Session timeout', 'Report failed login attempts', 'Dokaрu viр - sжki skrбalista', 'Ekki var hжgt aр bжta viр lagalistanum!', 'Stjуrnandi', 'Skбрu юig inn gegnum HTTPS til aр breyta', 'Leyfa strauma', 'Titill', 'Flytjandi', 'Plata', 'Athugasemd', 'Бr', 'Nr.', 'Tegund', 'Ekki stillt', 'Mesti hraрi (kbps)', 'Notandi', '%1 mнn. - %2 titlar', '%1 kbit %2 mнn', 'Genre list: %1', 'Бfram', '%1d %2h %3m playtime %4 files %5 mb', 'No relevant resources here.', 'Lykilorрi breytt!', 'Skrбning', 'Юъ verрur aр velja.', 'Hvaр er aр uppfжra?', 'Smelltu hйr fyrir aрstoр', 'Nota utanaрkomandi myndir', 'Slур utanaрkomandi mynda', 'Nъverandi lykilorр', 'Nъverandi lykilorр er ekki rйtt!', 'Preferred archiver', 'Archive could not be made', 'Probable file duplicate found: "%1" "%2"', 'Really delete playlist?', 'Stafrуfsrцр', 'Stokka upp', 'Raрa', 'Upprunalegt', ' Nota javascript', 'Ertu viss um aр юъ viljir eyрa юessum notanda?', 'Skoрa sцgu', 'Saga', 'Rцр', 'External CSS file', 'Remove duplicates', 'OK', 'ERR', 'Straumur', '(show as)', 'files', 'albums', '%1d %2h %3m %4s', 'General', 'Customize', 'Filehandling', 'Click on ? for help.', 'Automatic database sync', 'Send file extension', 'Allow unauthorized streams', 'Include headers', 'External javascript', 'Heimasнрa', 'Show Keyteq gives you part', 'Show upgrade part', 'Show statistics', 'Write ID3v2 with stream', 'Enable user signup', 'Filetypes', 'Yes', 'No', 'Extension', 'MIME', 'Include in M3U', 'Edit filetype', 'Sure?', 'Optimistic filecheck', 'Randomizer', 'Mode', 'Playlist', 'None, directly', 'My favourites', 'Did not find any hits', 'Alltime hits', 'Order', 'Virkja LAME stuрning?', 'Уvirkt', 'Leifa LAME notkun?', 'Email', 'Allow to mail files?', 'SMTP юjуnn', 'SMTP port', 'Mail to', 'Skilaboр', 'Senda', 'Mail sent!', 'Activate upload', 'Upload directory', 'Activate mp3mail', 'Upload', 'File uploaded!', 'File could not be uploaded!', 'You must enable cookies to log in!', 'Period', 'ever', 'this week', 'this month', 'last month', 'hits', 'LAME skipun', 'Show album cover', 'Album files', 'Resize album images', 'Album height', 'Album width', 'Mail method', 'Direct', 'Pear', 'Wait!', 'Please enter a valid e-mail in options!', 'Playlists inline?', 'Show album from URL?', 'Album URL', 'Could not send!', 'User added!', 'Archive creator', 'Archive is deleted.', 'User updated!', 'Music match', '%1 entries filtered', 'Log access', 'Viewable', 'Archived', 'Bulletin', 'Entered %1 by %2', 'more', 'Publish', '%1 mb', '%1 kb', '%1 bytes', 'Recursive', 'Previous', 'Next', 'Goto page %1', 'Page:', 'Never played', 'Manually approve signups', 'Pending', 'activate', 'All fields marked with * is obligatory', 'Your account will be inspected and activated manually.', 'Sнрustu straumar', 'muna eftir mйr', 'Style', 'finna', 'Enter paths to search for', 'Use selected?', 'Track time minst/mest', 'Mнnъtur', 'm3u', 'asx (WMA)', 'If update stops, click here: %1', 'Follow symlinks?', 'File template', 'Leifa URL цryggi', 'Upload whitelist', 'File type not allowed.', 'Lagalisti tуmur', 'Texti', 'Texti URL', 'Sнna texta stlур', '(eрa?)', 'Notandanafn eрa lykilorр ekki rйtt', 'Mesta stжrр sem mб hlaрa upp: %1', 'opna fyrir rss', 'tilgreiniр lykilorр', 'Vantar nafn og notanda', 'Notandi er юegar til');

$klang[29] = array('Turkish', 'ISO-8859-9', 'Tьrkзe', 'En зok sevilenler', 'Yeniler', 'Ara', '(gцsterilen %1 )', 'sn', 'Arama sonucu: \'%1\'', 'bulundu', 'Yok.', 'veritaban&#305; arama seзenekleri gьncelleme', 'Kullan&#305;lmayan kay&#305;tlar silinsin mi?', 'ID3 ba&#351;tan olu&#351;turulsun mu?', 'Hata arama modu?', 'Gьncelle', '&#304;ptal', 'Arama veritaban&#305;n&#305; gьncelle', '%1 dosya bulundu.', 'Tan&#305;mlanamayan dosya: %1, iptal edildi.', 'Kuruldu: %1 - Gьncelleme: %2, tarama: ', 'Tarama:', 'Hata&#305; - sorgu: %1', 'Okunamayan dosya: %1. iptal edildi.', 'kald&#305;r&#305;lan link: %1', 'girilen %1, gьncellenen %2, silinen %3 %4 hatal&#305; ve %5 iptal edilen toplam %6 dosya - %7 sn - %8 silinmek iзin i&#351;aretlendi.', '&#304;&#351;lem Tamam', 'Kapat', '"%1" de herhengi bir dosya bulunamad&#305;', 'kPlaylist Giri&#351;', 'Sanatз&#305;: %1 iзin albьm listesi', 'Sevilenler %1', 'Seзim yap&#305;lmad&#305;. Liste gьncellenmedi.', 'Liste gьncellendi!', 'Geri', 'Liste eklendi!', 'Sayfay&#305; tekrar yьklemeyi unutmay&#305;n.', 'Giri&#351;:', '&#351;ifre:', 'Dikkat! Yap&#305;&#287;&#305;n&#305;z i&#351;lemler kaydedilmektedir.', 'Giri&#351;', 'Giri&#351; iзin SSL gerekmektedir.', 'Зal', 'Sil', 'Payla&#351;&#305;m: ', 'Kaydet', 'Listeyi kontrol et: \'%1\' - %2 &#351;ark&#305;', 'Yazar', 'Gцz at', 'Seз', 'Sn', 'Durum', 'Bilgi', 'Sil', '&#304;sim', 'Toplam:', 'Hata', 'Seзilenleri: ', 'S&#305;ralama :', 'Listeyi de&#287;i&#351;tir', 'Bu giri&#351;i sil', 'Liste ekle', '&#304;sim:', 'Olu&#351;tur', 'Зal: ', 'Dosya', 'Albьm', 'Hepsi', 'Seзilen', 'ekle', 'зal', 'de&#287;i&#351;tir', 'yeni', 'Seз:', 'Kontrol: ', 'Liste: ', 'Say&#305;sal sevilenler', 'Keyteq in sunduklar&#305;:', '(gьncelleme iзin kontrol edin)', 'Ana site', 'sadece id3', 'albьm', '&#351;ark&#305;', 'sanatз&#305;', 'Sanaз&#305;n&#305;n en sevilen albьmь', 'gцz at', 'Payla&#351;&#305;lan Listeler', 'Kullan&#305;c&#305;lar', 'Admin Kontrolleri', 'Yeniler', 'Sevilenler', 'З&#305;k&#305;&#351;', 'Seзenekler', 'Gцz at', 'Benim ayarlar&#305;m', 'kullan&#305;c&#305; i&#351;lemleri', 'yeni kullan&#305;c&#305;', 'Tam isim', 'Giri&#351;', '&#350;ifre de&#287;i&#351;sin mi?', '&#350;ifre', 'Yorum', 'Eri&#351;im seviyesi', 'Aз&#305;k', 'Kapal&#305;', 'Kullan&#305;c&#305;y&#305; sil', 'Kullanc&#305;y&#305; з&#305;kar', 'Yenile', 'Yeni kullan&#305;c&#305;', 'sil', 'З&#305;k&#305;&#351;', 'EXTM3U kullan&#305;ls&#305;n m&#305;?', '(sevilen/yeni) sat&#305;r say&#305;s&#305;', 'Maksimum arama sat&#305;r&#305;', 'Reset', 'Dizini aз', 'Gidilecek dizin: %1', '&#304;ndir', 'Bir ad&#305;m yukar&#305; з&#305;k', 'Ana dizine git.', 'Gьncelleme iзin kontrol et', 'kullan&#305;c&#305;lar', 'Dil', 'seзenekler', 'Sepetle', 'kar&#305;&#351;t&#305;r:', 'Ayarlar', 'Ana dizin', 'Kay&#305;t yeri', 'Varsay&#305;lan dil', 'Widows sistemi', 'HTTPS gerektirmektedir', 'Tarama izni', '&#304;ndirme izni', 'Oturum sьresi doldu', 'Hatal&#305; giri&#351;leri rapor et', 'Bekleyin - Dosya listei haz&#305;rlan&#305;yor', 'Liste eklenemedi!', 'Yцnetici', 'De&#287;i&#351;tirmek iзin HTTPS ile girin!', 'Yay&#305;n motorunu aktif yap', '&#350;ark&#305;', 'Sanatз&#305;', 'Albьm', 'Yorum', 'Y&#305;l', 'Kay&#305;t', 'Tьr', 'ayarlanmad&#305;', 'Maksimum indirme oran&#305; (kbps)', 'Kullan&#305;c&#305;', '%1 dakika - %2 &#351;ark&#305;', '%1 kbit %2 dakika', 'Tьr listesi: %1', 'Tamam', '%1gьn %2saat %3dk зalma sьresi %4 dosya %5 mb', 'Burada uygun kaynak yok.', '&#350;ifre de&#287;i&#351;tirildi!', 'Kay&#305;t yapt&#305;r', 'Lьtfen bir seзim yap&#305;n&#305;z!', 'Neler gьncellensin?', 'Yard&#305;m iзin buraya t&#305;klay&#305;n&#305;z', 'D&#305;&#351;ardan resim kullan?', 'D&#305;&#351;ardan kullan&#305;lacak resmin adresi', '&#350;imdiki &#351;ifre', '&#350;imdiki &#351;ifre tutmuyor!', 'Tercih edilen ar&#351;ivleyici', 'Ar&#351;iv olu&#351;turulamad&#305;', 'Olas&#305; dosya tekrar&#305; bulundu:  "%1" "%2"', 'Listeyi gerзekten silmek istiyor musunuz?', 'Alfabetik', 'Rastgele', 'S&#305;rala', 'Orjinal', 'Javascript kullan', 'Bu kullan&#305;c&#305;y&#305; silmek iste&#287;inizden emin misiniz?', 'Tarihзeyi izle', 'tarihзe', 'Sat&#305;r', 'D&#305;&#351; CSS dosyas&#305;', 'Tekrarlananlar&#305; sil', 'Tamam', 'Hata', 'Yay&#305;n', '(olarak gцster)', 'dosyalar', 'albьmler', '%1gьn %2saat %3dakika %4sn', 'Genel', 'Ki&#351;isel', 'Dosya i&#351;lemleri', 'Yard&#305;m iзin  ? i&#351;aretine t&#305;klay&#305;n.', 'Otomatik veritaban&#305; senkronizasyonu', 'Dosya uzant&#305;s&#305;n&#305; gцnder', 'Yetki verilmemi&#351; yay&#305;nlara da izin ver', 'Ba&#351;l&#305;klar&#305; iзer', 'D&#305;&#351; javascript', 'Ana Sayfa', 'Keyteq\'in size sunduklar&#305; bцlьmьnь gцster', 'Gьncelleme bцlьmьnь gцster', '&#304;statistikleri gцster', 'Yay&#305;nla beraber ID3v2 ba&#351;l&#305;klar&#305;n&#305; de yaz', 'Kullan&#305;c&#305;n&#305;n kay&#305;t olmas&#305;na izin ver', 'Dosya tьrleri', 'Evet', 'Hay&#305;r', 'Uzant&#305;', 'MIME', 'M3U dakileri iзersin', 'Dosya tьrьnь dьzenle', 'Eminmisiniz?', 'Dosyan&#305;n var olup olmama kontrolь', 'Rastgele', 'Mod', 'Liste', 'Hay&#305;r, direkt olarak', 'Favorilerim', 'Hit parзa bulunamad&#305;', 'Tьm zamanlar&#305;n hit parзalar&#305;', 'S&#305;ra', 'LAME deste&#287;i aз&#305;ls&#305;nm&#305;?', 'Kapat&#305;ld&#305;', 'LAME kullan&#305;ls&#305;n m&#305;?', 'Email', 'Mail dosyalr&#305;na izin verilsin mi?', 'SMTP server', 'SMTP port', 'Gidecek mail adresi', 'Mesaj', 'Gцnder', 'Mail gцnderildi!', 'Yьklemeyi aktif yap', 'Yьkleme dizini', 'Mp3mail\'leri aktif yap', 'Yьkle', 'Dosya yьklendi!', 'Dosya yьklememez!', 'Giri&#351; iзin cookie lere izin vermeniz gerekir!', 'Aral&#305;k', 'her zaman', 'bu hafta', 'bu ay', 'geзen ay', 'hit', 'LAME komutu', 'Albьm kapa&#287;&#305;n&#305; gцster', 'Albьm dosyalar&#305;', 'Albьm resimlerini yeniden boyutland&#305;r', 'Albьm yьksekli&#287;i', 'Albьm geni&#351;li&#287;i', 'Mail metodu', 'Direk', 'Pear', 'Bekle!', 'Lьtfen seзeneklere geзerli bir e-mail adresi girin!', 'Liste iзerden ba&#351;las&#305;n m&#305;?', 'URL\'den albьm gцsterilsin mi?', 'Albьm URL\'si', 'Gцnderilemedi!', 'Kullan&#305;c&#305; eklendi!', 'Ar&#351;ivi olu&#351;turan', 'Ar&#351;iv silindi.', 'Kullan&#305;c&#305; gьncellendi!', 'Uyan parзalar', '%1 giri&#351; filitrelendi', 'Log eri&#351;imi', 'Gцrьlebilir', 'Ar&#351;ivlendi', 'Haberler', ' %1 tarihinde %2 giri&#351; yapt&#305;', 'ayr&#305;nt&#305;', 'Yay&#305;nla', '%1 mb', '%1 kb', '%1 bytes', 'Alt dizinlere dallan', 'Цnceki', 'Sonraki', 'Sayfa %1\'e/a git', 'Sayfa: ', 'Hiз зal&#305;nmad&#305;', 'Yeni kullan&#305;c&#305; yцnetici taraf&#305;ndan onaylans&#305;n', 'Beklemede', 'Aktif yap', '"*" ile i&#351;aretlenen tьm alanlar zorunludur', 'Hesab&#305;n&#305;z incelendikten sonra onaylanacakt&#305;r.', 'Son зal&#305;nanlar', 'beni hat&#305;rla', 'Sitil', 'bul', 'Aran&#305;lacak yolu girin', 'Seзilen kullan&#305;ls&#305;n m&#305;?', 'Kay&#305;t sьresi min/max', 'Dakika', 'm3u', 'asx (WMA)', 'Gьncelleme durursa, buraya t&#305;klay&#305;n: %1', 'Sembolik linkler takip edilsin mi?', 'Dosya tasla&#287;&#305;', 'URL gьvenli&#287;ini aз', 'Yьkleme izni filtresi', 'Dosya tьrьne izin verilmedi.', 'Liste bo&#351;!', '&#350;ark&#305; sцzleri', '&#350;ark&#305; sцzleri URL\'si', '&#350;ark&#305; sцzleri URL\'si gцsterilsin mi?', '(veya?)', 'Hatal&#505; kullan&#305;c&#305; ad&#305; veya &#351;ifre', 'Maksimum yьkleme boyutu : %1', 'Halka aз&#305;k son yay&#253;nlara RSS deste&#287;i verilsin  mi?');


# please submit new languages, or grammar fixes directly to us for new builds. Se http://www.kplaylist.net/ for more information.

$knrlangs = 30;

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

function checkchs($in)
{
	return @htmlentities($in, ENT_QUOTES, get_lang(1));
}



$app_ver  = 1.7;
$app_build = 426;


$kpdbtables = array('playlist', 'playlist_list', 'search', 'users', 'kplayversion', 'mhistory', 'config', 'filetypes', 'settings', 'bulletin', 'cache', 'session');
foreach ($kpdbtables as $name) define('TBL_'.strtoupper($name), $cfg['dbprepend'].$name);

if ($cfg['enablegetid3'])
{
	if (include($cfg['getid3include']))
	{
		if (defined('GETID3VERSION'))
		{
			if (function_exists('GetAllFileInfo')) define('GETID3_V', 16);
		} else
		if (defined('GETID3_VERSION'))	
		{	
			if (class_exists('getID3')) define('GETID3_V', 17);
		} else define('GETID3_V', 1);
	}
	if (!defined('GETID3_V')) define('GETID3_V', 0);
}

function db_gconnect()
{
	global $db;
	if (@mysql_connect($db['host'], $db['user'], $db['pass']) && mysql_select_db ($db['name'])) return true;
	return false;
}

if (function_exists('mysql_real_escape_string')) define('REALESCAPE', true); else define('REALESCAPE', false);

function myescstr($str)
{
	if (REALESCAPE && DBCONNECTION) return mysql_real_escape_string($str);
	return mysql_escape_string($str);
}

function db_execquery($query, $fast=false)
{
	if ($fast && function_exists('mysql_unbuffered_query')) $res = mysql_unbuffered_query($query); else
	$res = mysql_query($query);
	return $res;
}

function db_free($res)
{
	if ($res) mysql_free_result($res);
}

function db_execcheck($query)
{
	if (db_gconnect()) return mysql_query($query); else return 0;	
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
				$sql = 'INSERT INTO '.$this->table.' SET `key` = "'.$key.'", value = "'.myescstr($this->defaults[$key][0]).'", vtype = '.$this->defaults[$key][1];
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
			$sql = 'UPDATE '.$this->table.' SET value = "'.myescstr($value).'" WHERE `key` = "'.$key.'"'; 
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
				'showkeyteq'				=> array(1,1),
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
				'signuptemplate'			=> array(0, 2)
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
			while ($row = mysql_fetch_row($res)) $this->insert($row[1], $row[2], $row[3]); 
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
		if (mysql_num_rows($res) > 0) 
		{
			while ($row = mysql_fetch_row($res)) $varcache[$row[0]] = $row[1];
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

if (!function_exists('mysql_connect')) 
{
	echo 'Your PHP seem to lack MySQL support. Please locate your php.ini file and enable MySQL support.';
	die();
}

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

	if (!$cfg['installerenabled'])
	{
		echo 'Can\'t connect to the database and the installer is disabled. (If you need to re-install switch $cfg[\'installerenabled\'] to true.)';
		die();
	}
	
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
$setctl->publish('filetemplate');
$setctl->publish('urlsecurity');
$setctl->publish('showlyricslink');

$base_dir = explode(';', $setctl->get('base_dir'));
$deflanguage = $setctl->get('default_language');
$win32 = $setctl->get('windows');
if (!$win32) $dlrate = $setctl->get('dlrate'); else $dlrate = 0;

$runinit = array('pdir' => '', 'pdir64' => '', 'drive' => 0, 'astream' => 1);

if (!function_exists('mysql_list_processes') || !function_exists('mysql_thread_id')) $runinit['astream'] = 0;

// general - used as globals

$dir_list = $mark = array();
$marksid = $u_cookieid = $u_id = -1;
$valuser = false;

if (isset($_GET['d'])) $runinit['drive'] = $_GET['d']; else if (isset($_POST['drive'])) $runinit['drive'] = $_POST['drive']; 

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

if (DBCONNECTION)
{
	$streamtypes = $streamtypes_default;
	$res = db_execquery('SELECT extension, mime, m3u, getid, search, logaccess FROM '.TBL_FILETYPES.' WHERE enabled = 1', true);
	if ($res) 
	{
		while ($row = mysql_fetch_row($res)) $streamtypes[] = $row;
		db_free($res);
	}
} else $streamtypes = array();

$build_db_changes = array(423, 426);


$dbtable = array(TBL_MHISTORY => 10, TBL_CONFIG => 11, TBL_FILETYPES => 13, TBL_PLAYLIST => 2, TBL_PLAYLIST_LIST => 3, TBL_SEARCH => 4, TBL_USERS => 5, TBL_KPLAYVERSION => 6, TBL_BULLETIN => 14, TBL_CACHE => 15, TBL_SESSION => 16);

$oldbuild = 0;

function createdbdefinition($table, $autoinc=0)
{
	global $dbdef, $dbkeys;
	$out = 'CREATE TABLE '.$table.' (';

	foreach($dbdef[$table] as $column => $def) $out .= "\n".'  `'.$column.'` '.$def.',';
	
	if (isset($dbkeys[$table]))
	{
		for ($i=0,$c=count($dbkeys[$table]);$i<$c;$i++)
		{
			$out .= "\n".'  '.$dbkeys[$table][$i];
			if ($i + 1 < $c) $out .= ',';
		}
	} else $out = substr($out, 0, strlen($out) - 1);
	$out .= "\n".')';
	if ($autoinc > 0) $out .= ' AUTO_INCREMENT='.$autoinc;
	return $out;
}

$dbdef[TBL_USERS] = 
array(
	'u_name'			=> 'VARCHAR(32) NOT NULL DEFAULT \'\'',
	'u_pass'			=> 'VARCHAR(32) NOT NULL DEFAULT \'\'',
	'u_login'			=> 'VARCHAR(32) NOT NULL DEFAULT \'\'',
	'u_ip'				=> 'VARCHAR(16) NOT NULL DEFAULT \'\'',
	'u_comment'			=> 'VARCHAR(64) DEFAULT NULL',
	'u_id'				=> 'INT(4) NOT NULL AUTO_INCREMENT',
	'u_sessionkey'		=> 'BIGINT(16) unsigned DEFAULT \'0\'',
	'u_booted'			=> 'TINYINT(4) NOT NULL DEFAULT \'0\'',
	'u_status'			=> 'TINYINT(4) NOT NULL DEFAULT \'0\'',
	'u_time'			=> 'BIGINT(16) NOT NULL DEFAULT \'0\'',
	'u_access'			=> 'TINYINT(4) DEFAULT \'1\'',
	'u_allowdownload'	=> 'CHAR(1) NOT NULL DEFAULT \'1\'',
	'allowarchive'		=> 'CHAR(1) NOT NULL DEFAULT \'1\'',
	'archivesize'		=> 'INT(4) NOT NULL DEFAULT \'0\'',
	'extm3u'			=> 'CHAR(1) NOT NULL DEFAULT \'1\'', 
	'defplaylist'		=> 'INT(4) NOT NULL DEFAULT \'0\'', 
	'defshplaylist'		=> 'INT(4) NOT NULL DEFAULT \'0\'', 
	'defaultid3'		=> 'CHAR(1) NOT NULL DEFAULT \'0\'', 
	'defaultsearch'		=> 'INT(1) NOT NULL DEFAULT \'0\'', 
	'partymode'			=> 'CHAR(1) NOT NULL DEFAULT \'0\'', 
	'theme'				=> 'INT(4) NOT NULL DEFAULT \'1\'', 
	'lockedtime'		=> 'INT(8) NOT NULL DEFAULT \'0\'',
	'hotrows'			=> 'INT(4) NOT NULL DEFAULT \'25\'',
	'searchrows'		=> 'INT(4) NOT NULL DEFAULT \'25\'',
	'lang'				=> 'TINYINT NOT NULL DEFAULT \'0\'',
	'udlrate'			=> 'INT(4) NOT NULL DEFAULT \'0\'',
	'defgenre'			=> 'INT(4) NOT NULL DEFAULT \'0\'',
	'archer'			=> 'CHAR(1) NOT NULL DEFAULT \'0\'',
	'hitsas'			=> 'TINYINT NOT NULL DEFAULT \'0\'',
	'lameperm'			=> 'CHAR(1) NOT NULL DEFAULT \'0\'',
	'lamerate'			=> 'INT(4) NOT NULL DEFAULT \'0\'',
	'allowemail'		=> 'CHAR(1) NOT NULL DEFAULT \'0\'',
	'email'				=> 'VARCHAR(128) NOT NULL DEFAULT \'\'',
	'plinline'			=> 'CHAR(1) NOT NULL DEFAULT \'1\'',
	'hotmode'			=> 'INT(4) NOT NULL DEFAULT \'0\'',
	'created'			=> 'INT(4) NOT NULL DEFAULT \'0\'',
	'laston'			=> 'INT(4) NOT NULL DEFAULT \'0\'',
	'pltype'			=> 'INT(4) NOT NULL DEFAULT \'1\'',
	'orsearch'			=> 'CHAR(1) DEFAULT \'0\' NOT NULL',
	'textcut'			=> 'INT(2) DEFAULT 80 NOT NULL',
	'dircolumn'			=> 'INT(2) DEFAULT 1 NOT NULL',
	'streamengine'		=> 'CHAR(1) NOT NULL DEFAULT \'1\'',
	'utemplate'			=> 'CHAR(1) DEFAULT 0 NOT NULL'	
);

$dbkeys[TBL_USERS] = 
array(
	'PRIMARY KEY (u_id)', 
	'UNIQUE KEY u_login (u_login)'
);

$dbdef[TBL_PLAYLIST] = 
array(
	'u_id'		=> 'INT(4) NOT NULL DEFAULT 0',
	'name'		=> 'VARCHAR(32) NOT NULL DEFAULT \'\'',
	'public'	=> 'CHAR(1) NOT NULL DEFAULT 0',
	'status'	=> 'TINYINT(1) NOT NULL DEFAULT 0',
	'listid'	=> 'INT(11) NOT NULL AUTO_INCREMENT'
);

$dbkeys[TBL_PLAYLIST][] = 'PRIMARY KEY (listid)';
$dbkeys[TBL_PLAYLIST][] = 'UNIQUE KEY u_login (u_id,name)';


$dbdef[TBL_PLAYLIST_LIST] = 
array(
	'listid'	=> 'INT(11) NOT NULL DEFAULT \'0\'',
	'id'		=> 'INT(11) NOT NULL AUTO_INCREMENT',
	'sid'		=> 'INT(4) NOT NULL DEFAULT \'0\'',
	'seq'		=> 'INT(4) NOT NULL DEFAULT \'0\''
);

$dbkeys[TBL_PLAYLIST_LIST][] = 'PRIMARY KEY (id)';
$dbkeys[TBL_PLAYLIST_LIST][] = 'KEY `listid` (`listid`)';

$dbdef[TBL_SEARCH] = 
array(
	'id'		=> 'INT(11) NOT NULL AUTO_INCREMENT',
	'f_stat'	=> 'INT(4) NOT NULL DEFAULT \'0\'',
	'track'		=> 'INT(4) NOT NULL DEFAULT \'0\'',
	'year'		=> 'INT(4) NOT NULL DEFAULT \'0\'',
	'title'		=> 'VARCHAR(255) NOT NULL DEFAULT \'\'',
	'free'		=> 'VARCHAR(255) NOT NULL DEFAULT \'\'',
	'comment'	=> 'VARCHAR(255) NOT NULL DEFAULT \'\'',
	'dirname'	=> 'VARCHAR(255) NOT NULL DEFAULT \'0\'',
	'album'		=> 'VARCHAR(255) NOT NULL DEFAULT \'\'',
	'artist'	=> 'VARCHAR(255) NOT NULL DEFAULT \'\'',
	'md5'		=> 'VARCHAR(32) NOT NULL DEFAULT \'\'',
	'hits'		=> 'INT(4) NOT NULL DEFAULT \'0\'',
	'mtime'		=> 'INT(4) NOT NULL DEFAULT \'0\'',
	'date'		=> 'INT(4) NOT NULL',
	'fsize'		=> 'INT(4) NOT NULL',
	'genre'		=> 'INT(4) NOT NULL DEFAULT \'255\'',
	'bitrate'	=> 'INT(4) NOT NULL DEFAULT \'0\'',
	'ratemode'	=> 'TINYINT DEFAULT \'0\'',
	'lengths'	=> 'INT(4) NOT NULL DEFAULT \'0\'',
	'drive'		=> 'TINYINT DEFAULT \'0\'',
	'ftypeid'	=> 'INT(4) NOT NULL DEFAULT \'-1\''
);

$dbkeys[TBL_SEARCH] = 
array(
	'PRIMARY KEY (id)',
	'KEY `dirname` (`dirname`)',
	'KEY `free` (`free`)',
	'KEY `artist` (`artist`)',
	'KEY `album` (`album`)',
	'KEY `title` (`title`)',
	'KEY `fsize` (`fsize`)',
	'KEY `date` (`date`)',
	'KEY `f_stat` (`f_stat`)',
	'KEY `drive` (`drive`)',
	'KEY `ftypeid` (`ftypeid`)'
);

$dbdef[TBL_KPLAYVERSION] = 
array(
	'app_ver'		=> 'VARCHAR(6) NOT NULL DEFAULT \'\'',
	'app_build'		=> 'VARCHAR(6) NOT NULL DEFAULT \'\'',
	'app_finstall'	=> 'INT(4) NOT NULL DEFAULT 0'
);

$dbdef[TBL_MHISTORY] = 
array(
	'h_id'			=> 'INT(4) NOT NULL AUTO_INCREMENT',
	'u_id'			=> 'INT(4) NOT NULL',
	's_id'			=> 'INT(4) NOT NULL',
	'tid'			=> 'TINYINT(4) NOT NULL DEFAULT \'0\'',
	'utime'			=> 'INT(4) NOT NULL',
	'dwritten'		=> 'INT(4) NOT NULL DEFAULT 0',
	'dpercent'		=> 'INT(4) NOT NULL DEFAULT 0',
	'active'		=> 'TINYINT(4) DEFAULT 0 NOT NULL',
	'mid'			=> 'INT(4) DEFAULT 0 NOT NULL'
);

$dbkeys[TBL_MHISTORY] =
array(
	'PRIMARY KEY (h_id)',
	'KEY `s_id` (`s_id`)',
	'KEY `u_id` (`u_id`)',
	'KEY `utime` (`utime`)'
);

$dbdef[TBL_CONFIG] = 
array(
	'id'		=> 'INT(4) NOT NULL AUTO_INCREMENT',
	'key'		=> 'VARCHAR(255) NOT NULL',
	'value'		=> 'TEXT NOT NULL',
	'vtype'		=> 'INT(2) NOT NULL'
);

$dbkeys[TBL_CONFIG][] = 'UNIQUE (id, `key`)';
$dbkeys[TBL_CONFIG][] = 'KEY `key` (`key`)';


$dbdef[TBL_FILETYPES] = 
array(
	'id'		=> 'INT(4) NOT NULL AUTO_INCREMENT',
	'extension'	=> 'VARCHAR(32) NOT NULL DEFAULT \'\'', 
	'mime'		=> 'VARCHAR(128) NOT NULL DEFAULT \'\'',
	'm3u'		=> 'CHAR(1) NOT NULL DEFAULT \'\'', 
	'getid'		=> 'INT(4) NOT NULL DEFAULT 0',
	'search'	=> 'CHAR(1) NOT NULL DEFAULT \'1\'',
	'logaccess'	=> 'CHAR(1) NOT NULL DEFAULT \'1\'',
	'enabled'	=> 'CHAR(1) NOT NULL DEFAULT \'1\''
);

$dbkeys[TBL_FILETYPES][] = 'PRIMARY KEY (`id`)';


$dbdef[TBL_BULLETIN] = 
array(
	'bid'		=> 'INT(4) NOT NULL AUTO_INCREMENT',
	'u_id'		=> 'INT(4) NOT NULL',
	'utime'		=> 'INT(4) NOT NULL',
	'publish'	=> 'INT(4) NOT NULL DEFAULT 0',
	'mesg'		=> 'TEXT NOT NULL'
);

$dbkeys[TBL_BULLETIN][] = 'PRIMARY KEY (`bid`)';


$dbdef[TBL_CACHE] = 
array(
	'cacheid'	=> 'INT(4) NOT NULL AUTO_INCREMENT',
	'id'		=> 'INT(4) NOT NULL',
	'value'		=> 'TEXT NOT NULL'
);

$dbkeys[TBL_CACHE][] = 'PRIMARY KEY (`cacheid`)';

$dbdef[TBL_SESSION] = 
array(
	'sessionid'		=> 'BIGINT(16) NOT NULL AUTO_INCREMENT',
	'u_id'			=> 'INT(4) NOT NULL DEFAULT \'0\'',
	'ip'			=> 'INT(4) NOT NULL DEFAULT \'0\'',
	'login'			=> 'INT(4) NOT NULL DEFAULT \'0\'',
	'refreshed'		=> 'INT(4) NOT NULL DEFAULT \'0\'',
	'logout'		=> 'INT(4) NOT NULL DEFAULT \'0\'',
	'sstatus'		=> 'INT(4) DEFAULT \'0\' NOT NULL'
);
 
$dbkeys[TBL_SESSION][] = 'PRIMARY KEY (`sessionid`)';
$dbkeys[TBL_SESSION][] = 'KEY `u_id` (`u_id`)';

$dbtables = $installdb = $installdbuser = array();

function init_db_tables()
{
	global $installdb, $installdbuser, $dbtables, $dbdef, $db, $app_ver;
	
	foreach($dbdef as $tblname => $tblarr) 
		foreach($tblarr as $rowname => $rowsql) $dbtables[$tblname][] = $rowname;

	$installdb[0] = '';
	$installdb[1] = 'CREATE DATABASE IF NOT EXISTS '.$db['name'];
	$installdb[2] = createdbdefinition(TBL_PLAYLIST);
	$installdb[3] = createdbdefinition(TBL_PLAYLIST_LIST);
	$installdb[4] = createdbdefinition(TBL_SEARCH, 1);
	$installdb[5] = createdbdefinition(TBL_USERS, 1);
	$installdb[6] = createdbdefinition(TBL_KPLAYVERSION);
	$installdb[7] = 'DELETE FROM '.TBL_KPLAYVERSION;
	$installdb[8] = 'INSERT INTO '.TBL_KPLAYVERSION.' (app_ver, app_build, app_finstall) VALUES ("'.$app_ver.'", "0", "'.time().'")';
	$installdb[9] = 'INSERT INTO '.TBL_USERS.' SET u_name = "admin", u_login = "admin", u_pass = "'.md5('admin').'",  u_comment = "admin", u_access = "0", created = '.time();
	$installdb[10] = createdbdefinition(TBL_MHISTORY);
	$installdb[11] = createdbdefinition(TBL_CONFIG);

	if (isset($_SERVER['SERVER_SOFTWARE']))
	{
		if (preg_match("/win/i", $_SERVER['SERVER_SOFTWARE']) || preg_match("/microsoft/i", $_SERVER['SERVER_SOFTWARE'])) $win32inst = 1; else $win32inst = 0;
	}

	$installdb[12] = 'INSERT INTO '.TBL_CONFIG.' set `key` = "windows", value = "'.$win32inst.'", vtype = 1';

	$installdb[13] = createdbdefinition(TBL_FILETYPES);
	$installdb[14] = createdbdefinition(TBL_BULLETIN);
	$installdb[15] = createdbdefinition(TBL_CACHE);
	$installdb[16] = createdbdefinition(TBL_SESSION, getrand(1));

	$installdbuser[0] = 'GRANT ALL ON '.$db['name'].'.* TO '.$db['user'].'@'.$db['host']." IDENTIFIED BY '".$db['pass']."'";
	$installdbuser[1] = 'SET PASSWORD FOR '.$db['user'].'@'.$db['host']." = OLD_PASSWORD('".$db['pass']."')";
	$installdbuser[2] = 'FLUSH PRIVILEGES';
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

function image_album($fname) 
{
  pic_headers($fname, base64_decode('R0lGODlhEgANAMQAAKZlCvvu1/eon/zQb/7LUvTKxr56Bv/7+/'.
'zotv+6J//2429IC/7ahvK8YOuZA9OGAORyUc+zevHh2finDpx4'.
'MubHtOyJTP/BPOZLM7o9BN+jRPrv7bSXW8ePTsWMP////yH5BA'.
'AAAAAALAAAAAASAA0AAAWP4Cd+R2BK0naMrCg1AxIoiqm0YmU4'.
'FzPTtcBqVADsejJgQDbadB5QQoRCiSAQDMJgJAE4vp1FJrPgDB'.
'KTyahidCQoCwhmQSE4DAYi4DG5NBaACxp3ABRcEH0EDAwREQ0J'.
'DwAAESMHAhZai4sXEw6FGywblw2aAwQTDx4SOJYQFg2wGhoCoD'.
'giGwUCugUqOCEAOw=='));
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
	pic_headers($fname, base64_decode('R0lGODlhWAAfAPf/AP///wgICBAQEBgYGCkpKTExMTk5OUJCQk'.
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
''));
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

if (isset($_GET['image']))
{
	$aimg = $_GET['image'];
	switch ($_GET['image'])
	{
		case 'w3c_xhtml_valid.gif': image_w3c_xhtml_valid($aimg); break;
		case 'dir.gif':				image_dir($aimg); break;
		case 'login.jpg':			image_login($aimg); break;
		case 'kplaylist.gif':		image_kplaylist($aimg); break;
		case 'album.gif':			image_album($aimg); break;
		case 'link.gif':			image_link($aimg); break;
		case 'cdback.gif':			image_cdback($aimg); break;
		case 'root.gif':			image_root($aimg); break;
		case 'saveicon.gif':		image_saveicon($aimg); break;
		case 'spacer.gif':			image_bl_pix($aimg); break;
		case 'sendmail.gif':		image_sendmail($aimg); break;
		case 'lyrics.gif':			image_lyrics($aimg); break;
		case 'rss.gif':				image_rss($aimg); break;
		default: break;
	}
	flush();
	die();
}

// end of pictures...


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
		global $u_id;
		$sql = 'SELECT h.s_id,count(*) as cnt, sum(h.dpercent) as rate, s.lengths from '.TBL_MHISTORY.' h, '.TBL_SEARCH.' s WHERE s.id = h.s_id AND u_id = '.$u_id;
		if ($this->fromdate > 0) $sql .= ' AND h.utime > '.$this->fromdate;
		if ($this->todate > 0) $sql .= ' AND h.utime < '.$this->todate;		
		if (is_array($this->genre))  $sql .= ' AND ('.$this->getgenreor('s.genre').')';
		if ($this->minsec || $this->maxsec) $sql .= ' AND '.$this->gettiming('s.');
		if ($this->rowmode == 2) $sql .= ' AND lengths > 0';
		$sql .= ' GROUP by h.s_id ORDER by rate '.$this->ssort.', cnt '.$this->ssort;
		$res = db_execquery($sql, true);
		if ($res !== false) 
		$secs = $ncnt = 0;
		while ($row = mysql_fetch_row($res)) if (!$this->iterate($cnt, $secs, $row[3], $row[0])) break;
		db_free($res);
	}

	function getalltime()
	{
		global $u_id;
		$sql = 'SELECT id, lengths from '.TBL_SEARCH.' WHERE hits > 0';
		if (is_array($this->genre))  $sql .= ' AND ('.$this->getgenreor('genre').')';
		if ($this->minsec || $this->maxsec) $sql .= ' AND '.$this->gettiming();
		if ($this->rowmode == 2) $sql .= ' AND lengths > 0';
		$sql .= ' ORDER by hits '.$this->ssort;
		$res = db_execquery($sql, true);
		$secs = $ncnt = 0;
		if ($res !== false) while ($row = mysql_fetch_row($res)) if (!$this->iterate($ncnt, $secs, $row[1], $row[0])) break;
		db_free($res);
	}

	function getrandom()
	{
		global $u_id;
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

		if ($this->rowmode == 2)
		{
			if (!$wh) $sql .= ' WHERE lengths > 0'; else $sql .= ' AND lengths > 0';
		}

		$lengths = $tmpsids = array();

		$res = db_execquery($sql, true);
		srand(make_seed());
		if ($res !== false) while ($row = mysql_fetch_row($res)) 
		{
			$tmpsids[$row[0]] = getrand();
			$lengths[$row[0]] = $row[1];
		}
		arsort($tmpsids, SORT_DESC);
		reset($tmpsids);
		
		$nlist = array();
		$secs = $ncnt = 0;
		foreach ($tmpsids as $id => $key) if (!$this->iterate($ncnt, $secs, $lengths[$id], $id)) break;
	}

	function getneverplayed()
	{
		global $u_id;
		$wh = false;
		$sql = 'SELECT s_id FROM '.TBL_MHISTORY.' WHERE u_id = '.$u_id.' GROUP BY s_id';
		$res = db_execquery($sql, true);
		$ignore = array();
		while ($row = mysql_fetch_row($res)) $ignore[$row[0]] = true;

		$sql = 'SELECT id, lengths from '.TBL_SEARCH.' ';
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

		if ($this->rowmode == 2)
		{
			if (!$wh) $sql .= ' WHERE lengths > 0'; else $sql .= ' AND lengths != 0';
		}

		$res = db_execquery($sql, true);
		
		$ncnt = 0;
		$secs = 0;
		if ($res !== false) while ($row = mysql_fetch_row($res)) 
		{
			if (!isset($ignore[$row[0]])) 
			{
				if (!$this->iterate($ncnt, $secs, $row[1], $row[0])) break;
			}
		}
		db_free($res);
	}

	function getmusicmatch()
	{	
		global $u_id;
		$master = $lengths = array();
		$users = array();
		
		$sql = 'SELECT h.s_id, sum(h.dpercent) as rate, count(*) as cnt, s.lengths FROM '.TBL_MHISTORY.' h, '.TBL_SEARCH.' s WHERE h.s_id = s.id AND h.u_id = '.$u_id;
		if ($this->fromdate > 0) $sql .= ' AND h.utime > '.$this->fromdate;
		if ($this->todate > 0) $sql .= ' AND h.utime < '.$this->todate;
		if (is_array($this->genre)) $sql .= ' AND ('.$this->getgenreor('s.genre').')';
		if ($this->minsec || $this->maxsec) $sql .= ' AND '.$this->gettiming();

		if ($this->rowmode == 2) $sql .= ' AND lengths > 0';

		$sql .= ' GROUP BY h.s_id ORDER BY rate DESC,cnt DESC';

		$res = db_execquery($sql, true);	
		while ($row = mysql_fetch_row($res)) 
		{
			$master[] = array($row[0], $row[1]+$row[2]);
			$lengths[$row[0]] = $row[3];
		}
		
		for ($i=0,$c=count($this->users);$i<$c;$i++)
		{
			$sql = 'SELECT s_id, sum(dpercent) as rate, count(*) AS cnt FROM '.TBL_MHISTORY.' WHERE u_id = '.$this->users[$i];
			if ($this->fromdate > 0) $sql .= ' AND utime > '.$this->fromdate;
			if ($this->todate > 0) $sql .= ' AND utime < '.$this->todate;
			$sql .= ' GROUP BY s_id ORDER BY rate DESC,cnt DESC';
			$res = db_execquery($sql, true);
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
		
		$secs = $cnt = 0;
		foreach ($musicm as $sid => $hits) if (!$this->iterate($cnt, $secs, $lengths[$sid], $sid)) break;
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
			if ($this->playlist == -1)
			{
				$m3ug = new m3ugenerator();
				for ($i=0,$c=count($this->sids);$i<$c;$i++)	$m3ug->sendlink2($this->sids[$i]);
				$m3ug->start();
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

		$res = db_execquery('SELECT u.u_id, u.u_login FROM '.TBL_USERS.' u, '.TBL_MHISTORY.' h WHERE u.u_id = h.u_id GROUP BY h.u_id', true);
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
		if (isset($where['execute'])) $this->execute(); else $this->view();
	}

	function view($message = '')
	{
		global $PHP_SELF, $setctl, $u_id;
		kprintheader(get_lang(212),1);
		
		$useropt = $this->getusers($this->users);
		
		?>
		<form name="randomizer" method="post" action="<?php echo $PHP_SELF; ?>">
		<input type="hidden" name="action" value="randomizer"/>
		<table width="95%" align="center" border="0" cellspacing="2" cellpadding="0">
		<tr>
			<td class="importnant" colspan="2"><?php echo $message; ?></td>
		</tr>
		<tr>
			<td height="5"></td>
		</tr>
		<tr>
			<td valign="top" class="wtext"><?php echo get_lang(213); ?></td>
			<td valign="top">
				<select name="mode" id="mode" class="fatbuttom" onchange="javascript: 
							
							d = document.getElementById('mode'); 
							e = document.getElementById('userfilter'); 
							fdate = document.getElementById('fromdate'); 
							tdate = document.getElementById('todate'); 
							if (d.value != 3) e.disabled = true; else e.disabled = false;
							if (d.value == 2 || d.value == 4 || d.value == 1) 
							{
								fdate.disabled = true;
								tdate.disabled = true;
							} else
							{
								fdate.disabled = false;
								tdate.disabled = false;
							}
					">
					<option value="0"<?php if ($this->mode == 0) echo ' selected="selected"'; ?>><?php echo get_lang(216); ?></option>
					<option value="1"<?php if ($this->mode == 1) echo ' selected="selected"'; ?>><?php echo get_lang(218); ?></option>
					<option value="2"<?php if ($this->mode == 2) echo ' selected="selected"'; ?>><?php echo get_lang(171); ?></option>
					<option value="3"<?php if ($this->mode == 3) echo ' selected="selected"'; ?>><?php echo get_lang(263); ?></option>
					<option value="4"<?php if ($this->mode == 4) echo ' selected="selected"'; ?>><?php echo get_lang(280); ?></option>
				</select>
			</td>
			<td valign="top" class="wtext"><?php echo helplink('randmode'); ?></td>
		</tr>
		<tr>
			<td valign="top" class="wtext"><?php echo get_lang(87); ?></td>
			<td valign="top"><?php if (!empty($useropt)) { ?><select class="fatbuttom" <?php if ($this->mode != 3) echo 'disabled="disabled" '; ?>style="width:150px" multiple="multiple" id="userfilter" size="6" name="usersfilter[]"><?php echo $useropt; ?></select><?php } ?></td>
			<td valign="top" class="wtext"><?php echo helplink('randusers'); ?></td>
		</tr>
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
			$row['mesg'] = 'Welcome to kPlaylist! This is a auto generated bulletin. Click on \'more\' to add a real one.';
			return $this->formatted($row);
		}
	}

	function getlink($msg)
	{
		global $PHP_SELF;
		return '<a class="hot" href="'.$PHP_SELF.'?action=bulletin&amp;m=read">'.$msg.'</a>';
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
			return mysql_insert_id();
		} else
		{
			$sql = 'UPDATE '.TBL_BULLETIN.' SET publish = '.$publish.', mesg = "'.myescstr($mesg).'" WHERE bid = '.$bid;
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
		
		showdir('',get_lang(268),0);

		echo '</td></tr>';

		$res = db_execquery('SELECT b.*,u.u_login FROM '.TBL_BULLETIN.' b, '.TBL_USERS.' u WHERE b.u_id = u.u_id ORDER BY bid DESC');
		?>
		<tr><td height="15"></td></tr>
		
		
		<?php
		while ($row = mysql_fetch_assoc($res))
		{
			echo '<tr><td>';
			echo '<table width="50%" cellpadding="3" class="tblbulletin" cellspacing="3" border="0">';
			echo $this->formatted($row, false);			
			
			if (db_guinfo('u_access') == 0 || $row['u_id'] == $u_id) echo '<tr><td><input type="button" class="fatbuttom" name="edit" value="'.get_lang(71).'" onclick="'.jswin('editbulletin', '?action=editbulletin&amp;bid='.$row['bid'], 300, 550).'"/>&nbsp;<input type="button" class="fatbuttom" name="del" value="'.get_lang(109).'" onclick="javascript: if (confirm(\''.get_lang(210).'\')) location = \''.$PHP_SELF.'?action=delbulletin&amp;bid='.$row['bid'].'\';"/></td></tr>';

			echo '</table></td></tr>';
			echo '<tr><td height="10"></td></tr>';
		}
		echo '<tr><td><input type="button" name="new" value="'.get_lang(72).'" class="fatbuttom" onclick="'.jswin('newbulletin', '?action=newbulletin',300,550).'"/></td></tr>';
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
		if (strlen($msg) > $cfg['frontbulletinchars'] && $single) 
			$msg = substr($msg, 0, $cfg['frontbulletinchars']).' '.$this->getlink('...');

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
		$data  = 'Return-Path: <'.trim($from).'>'.$from.$this->crlf;
		$data .= 'Date: '.date('r').$this->crlf;
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
		if (isset($_POST['sid'])) $this->setsid($_POST['sid']);
		if (isset($_POST['message'])) $this->setmessage(stripcslashes($_POST['message']));
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
		$msg = array();		

		if (ENABLEUPLOAD && isset($_FILES['fileupload']) && is_array($_FILES['fileupload']))
		{
			foreach($_FILES['fileupload']['name'] as $id => $name)
			{
				if (!empty($name) && isset($_FILES['fileupload']['tmp_name'][$id]))
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
						
						if (!empty($path) && filesize($_FILES['fileupload']['tmp_name'][$id]) > 0)
						{
							$uploadfile = $path.$this->replace($name);
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
		global $PHP_SELF, $cfg;
		kprintheader(get_lang(234), 1);
		?>
		<form method="post" name="fupload" enctype="multipart/form-data" action="<?php echo $PHP_SELF; ?>">
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
	
		for ($i=0;$i<$cfg['uploadselections'];$i++)
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
		global $PHP_SELF, $setctl, $phpenv;
		$this->lf = "\r\n";
		$this->data  = '<?xml version="1.0" encoding="ISO-8859-1"?>'.$this->lf;
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
			$ids = array();
			$res = mysql_list_processes();
			while ($row = mysql_fetch_assoc($res)) $ids[$row['Id']] = true;
			db_free($res);
			$res = db_execquery('SELECT h_id, mid FROM '.TBL_MHISTORY.' WHERE active = 1');
			if ($res) while ($row = mysql_fetch_row($res)) if (!isset($ids[$row[1]])) db_execquery('UPDATE '.TBL_MHISTORY.' SET active = 0 WHERE h_id = '.$row[0]);
		}
	}

	function getlast($count=5)
	{
		return db_execquery('SELECT s.id as id, h.active as active, h.h_id as hid, h.utime as utime FROM '.TBL_SEARCH.' s, '.TBL_MHISTORY.' h WHERE h.s_id = s.id ORDER BY h.active DESC, h.h_id DESC LIMIT '.$count);
	}

	function createrss($clink=false)
	{
		global $cfg, $setctl, $phpenv;
		$res = $this->getlast($cfg['rsslaststreamcount']);

		$rss = new krss(get_lang(286));

		while ($row = mysql_fetch_assoc($res))
		{
			$f2 = new file2($row['id'], true);
			$fd = new filedesc($f2->fname);
			if ($fd->found && $fd->m3u)
			{
				if ($clink) $link = $setctl->get('streamurl').$phpenv['streamlocation'].$f2->weblink(); else $link = $setctl->get('streamurl').$phpenv['streamlocation'];
				$rss->additem($f2->gentitle(array('title', 'artist')), $f2->gentitle(array('title', 'artist', 'album')), $link, $row['utime'], ''); 
			}
		}
		$rss->ship();
	}
	
	function show()
	{
		global $cfg, $setctl, $phpenv;
		$out = '<table width="96%" align="center" cellpadding="0" cellspacing="0" border="0">';
		$res = $this->getlast($cfg['laststreamscount']);
		
		$out .= '<tr><td width="90%"></td><td width="10%"></td></tr>';

		$cnt=0;
		$rows = mysql_num_rows($res);
		while ($row = mysql_fetch_assoc($res))
		{
			$cnt++;
			$f2 = new file2($row['id'], true);
			$out .= '<tr><td';
			if ($cnt != $rows) $out .= ' colspan="2"';
			$out .= ' nowrap="nowrap"><a class="';
			if ($row['active']) $out .= 'filemarked'; else $out .= 'wtext';
			$out .= '" href="'.$f2->weblink().'">';			
			$out .= $f2->gentitle(array('title', 'artist'), $cfg['laststreambreak'] + 3);
			$out .= '</a>';
			if ($cnt == $rows && $setctl->get('publicrssfeed')) $out .= '</td><td valign="bottom" align="right">'.'<a href="'.$setctl->get('streamurl').$phpenv['streamlocation'].'?streamrss=rss.xml"><img src="'.getimagelink('rss.gif').'" border="0" alt="RSS"/></a>';
			$out .= '</td></tr>';
		}
		if (!$cnt)	$out .= '<tr><td>'.get_lang(10).'</td></tr>';
		$out .= '</table>';
		return $out;		
	}
}



$cssthemes[0] = '
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
.tblbulletin
{
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: xx-small;
	background-color: #ECEFF2;
	color: #000000;
	border: 1px #000000;
	border-style: solid
}
.bulletin
{
	color: #48599C;
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px
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
	font-size: x-small;
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
a
{
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: xx-small;
	font-style: normal;
	color: #000066;
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
}';


$kdesign = array();

$kdesign['login'] = '
?>
<form style="margin:0;padding:0" method="post" action="<?php if (HTTPS_REQ_MET) echo $PHP_SELF;?>">
<input type="hidden" name="uri" value="<?php if (isset($_POST[\'uri\'])) echo $_POST[\'uri\']; else echo urlencode($phpenv[\'uri\']); ?>"/>
<p>&nbsp;</p>
<table width="600" border="0" cellspacing="0" cellpadding="0" align="center">
	<tr>
		<td colspan="3"><img src="<?php echo getimagelink(\'login.jpg\'); ?>" height="327" width="600" alt="kPlaylist v<?php echo $app_ver; ?> build <?php echo $app_build; ?>"/></td>
	</tr>
	<tr>
		<td height="4"/>
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
					<td width="30%"><input type="text" id="user" name="user" tabindex="1" maxlength="30" size="15" class="logonbuttom"/></td>
					<td width="48%"></td>
				</tr>
				<tr>
					<td height="3"></td>
				</tr>
				<tr>
					<td></td>
					<td><font class="text"><?php echo get_lang(38); ?></font></td>
					<td>
						<input type="password" name="password" tabindex="2" maxlength="30" size="15" class="logonbuttom"/>
					</td>
				</tr>
				<tr>
					<td height="3"></td>
				</tr>
				<tr>
					<td></td>
					<td><font class="text"><?php echo get_lang(287); ?></font></td>
					<td><input type="checkbox" name="rememberme" tabindex="4" value="1" class="logonbuttom"/></td>
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
							?><input type="submit" name="submit" tabindex="3" value="<?php echo get_lang(40); ?>" class="logonbuttom" />
							<?php
							if (USERSIGNUP) 
							{ 
								?><input type="button" name="Signup" tabindex="5" onclick="newwin(\'Users\', \'<?php echo $PHP_SELF; ?>?signup=1\', 195, 350);" value="<?php echo get_lang(158); ?>" class="logonbuttom" /><?php 
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
	d = document.getElementById(\'user\');	
	d.focus();	
	-->
</script>
<table width="610" border="0" cellspacing="0" cellpadding="0" align="center">
<tr><td height="9"></td></tr>
<tr>
	<td align="right">
		<a href="http://validator.w3.org/check/referer">
		<img src="<?php echo getimagelink(\'w3c_xhtml_valid.gif\'); ?>" border="0" alt="Valid XHTML 1.0!" height="31" width="88"/></a>
	</td>
</tr>
<tr>
	<td align="right"><a href="http://www.kplaylist.net/"><font class="loginkplaylist">www.kplaylist.net</font>&nbsp;</a></td>
</tr>
</table>';

$kdesign['infobox'] = '	
	$trheight = 14;
	$boxwidth = 245;
	?>	
	<table width="100%" cellspacing="0" cellpadding="0" border="0">
	<tr>
		<td valign="top" align="left">		
		<?php if ($setctl->get(\'showkeyteq\')) 
		{
			?><span class="notice"><?php echo \'<a href="http://keyteq.no" target="_blank">\'.substr(get_lang(77),0,3).\'</a>\'.substr(get_lang(77),3); ?></span><?php
		}
		if ($setctl->get(\'showupgrade\')) 
		{
			?><a title="<?php echo get_lang(120); ?>" href="http://www.kplaylist.net/?ver=<?php echo $app_ver; ?>&amp;build=<?php echo $app_build; ?>" target="_blank">
			<font color="#CCCCCC"><?php echo get_lang(78); ?></font></a><br/><?php
		} else if ($setctl->get(\'showkeyteq\')) echo \'<br/>\'; ?>
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
						<form style="margin:0;padding:0" name="search" action="<?php echo $PHP_SELF; ?>" method="post">
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
							<td align="left"><input type="text" name="searchfor" id="searchfor" value=\'<?php echo htmlentities(sanstr(\'searchfor\'), ENT_QUOTES); ?>\' maxlength="150" size="46" class="fatbuttom"/></td>	
						</tr>
						<tr>
							<td height="5"></td>
						</tr>
						<tr>
							<td align="left">
								<input type="radio" name="searchwh" value="0" <?php if (db_guinfo(\'defaultsearch\')==\'0\') echo \'checked="checked"\';?>/><font class="notice"><?php echo get_lang(81); ?>&nbsp;</font>
								<input type="radio" name="searchwh" value="1" <?php if (db_guinfo(\'defaultsearch\')==\'1\') echo \'checked="checked"\';?>/><font class="notice"><?php echo get_lang(82); ?>&nbsp;</font>
								<input type="radio" name="searchwh" value="2" <?php if (db_guinfo(\'defaultsearch\')==\'2\') echo \'checked="checked"\';?>/><font class="notice"><?php echo get_lang(83); ?>&nbsp;</font>
								<input type="radio" name="searchwh" value="3" <?php if (db_guinfo(\'defaultsearch\')==\'3\') echo \'checked="checked"\';?>/><font class="notice"><?php echo get_lang(67); ?></font>
							</td>		
						</tr>
						<tr>
							<td height="5"></td>
						</tr>
						<tr>
							<td align="left">
								<input type="checkbox" name="onlyid3" value="1" <?php if (db_guinfo(\'defaultid3\')) echo \' checked="checked"\'; ?>/>
								<font class="notice"><?php echo get_lang(80); ?></font>
								<input type="checkbox" name="orsearch" value="1" <?php if (db_guinfo(\'orsearch\')) echo \' checked="checked"\'; ?>/>
								<font class="notice"><?php echo get_lang(306); ?></font>&nbsp;
								<select name="hitsas" class="fatbuttom">
								<option value="0"<?php if (db_guinfo(\'hitsas\') == 0) echo \'selected="selected"\'; ?>><?php echo get_lang(185); ?></option>
								<option value="1"<?php if (db_guinfo(\'hitsas\') == 1) echo \'selected="selected"\'; ?>><?php echo get_lang(186); ?></option>
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
								d = document.getElementById(\'searchfor\');
								d.focus();
								-->
							</script>
							<?php blackbox(get_lang(84), album_hotlist(\'artist\'), 0, true, \'boxhotlist\', \'left\', $boxwidth); ?>
							</td>
						</tr>
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
						trspace($trheight);
						?>
						<tr>
							<td><?php 
									blackbox(get_lang(286), $ca->show(), 0, false, \'box\', \'left\', $boxwidth); ?>
								</td>
						</tr>		
						</table>
						</form>
					</td>
				</tr>
				<?php
	
				$ploutput = sharedplaylists();
				if (!empty($ploutput))
				{
					trspace($trheight);
					?>
					<tr>
					<td>
					<form style="margin:0;padding:0" name="sharedplaylist" action="<?php echo $PHP_SELF?>" method="post">
					<table width="100%" border="0" cellspacing="0" cellpadding="0">
					<tr><td><?php echo blackbox(get_lang(86), $ploutput, 0, false, \'box\', \'left\', $boxwidth); ?></td></tr>
					</table>
					</form>
					</td>
					</tr>
					<?php 
				}
				?>

				<tr>
				<td>
				<form style="margin:0;padding:0" name="misc" action="<?php echo $PHP_SELF?>" method="post">
				<input type="hidden" name="action" value="misc"/>
				<table width="100%" border="0" cellspacing="0" cellpadding="0">
				<?php					
					if (db_guinfo(\'u_access\') == 0)
					{
						trspace($trheight);
						?>
						<tr>
							<td align="left">
						<?php
						$admincode = \'&nbsp;<input type="button" name="action" value="\'.get_lang(87).\'" class="fatbuttom" onclick="\'.jswinscroll(\'Users\', \'?action=showusers\',360,685).\'"/> \';			
						$admincode .= \'<input type="button" name="updatesearch" value="\'.get_lang(15).\'" class="fatbuttom" onclick="\'.jswinscroll(\'Update\', \'?action=updateoptions\').\'"/> \';
						$admincode .= \'<input type="button" name="settings" value="\'.get_lang(126).\'" class="fatbuttom" onclick="\'.jswin(\'Settings\',\'?action=settingsview\',460,685).\'"/>\';
						
						$dropadmin = \'<a class="bbox" onclick="javascript: if (!confirm(\'.addsq().get_lang(313).addsq().\')) return false;" href="\'.$PHP_SELF.\'?action=dropadmin&amp;p=\'.$runinit[\'pdir64\'].\'&amp;d=\'.$runinit[\'drive\'].\'">x</a>&nbsp;\';

						
						
	
						echo blackbox(get_lang(88),$admincode, 0, false, \'box\', \'left\', $boxwidth, $dropadmin); ?>
						</td></tr>
					<?php 
					} 
					
					$othercode = \'&nbsp;<input type="submit" name="whatsnew" value="\'.get_lang(89).\'" class="fatbuttom"/>&nbsp;\';
					$othercode .= \'<input type="submit" name="whatshot" value="\'.get_lang(90).\'" class="fatbuttom"/>&nbsp;\';

					$usermisc = \'&nbsp;<input type="submit" name="logmeout" value="\'.get_lang(91).\'" onclick="javascript: if (!confirm(\'.addsq().get_lang(210).addsq().\')) return false;" class="fatbuttom"/> \';
					$usermisc .= \'<input type="button" name="editoptions" value="\'.get_lang(92).\'" class="fatbuttom" \'. \'onclick="\'.jswin(\'Options\', \'?action=editoptions\',360,590).\'"/> \';
					$usermisc .= \'<input type="button" name="randomizer" value="\'.get_lang(212).\'" class="fatbuttom" \'. \'onclick="\'.jswin(\'Randomizer\', \'?action=showrandomizer\',380,550).\'"/>\';

					trspace($trheight);

					?>
					<tr><td><?php echo blackbox(get_lang(93), $othercode, 0, false, \'box\', \'left\', $boxwidth); ?></td></tr>

					<?php trspace($trheight); ?>
					
					<?php

					$genres = \'&nbsp;\'.genre_select(true,db_guinfo(\'defgenre\'));
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

$kdesign['endmp3table'] = '	$text = $crstr_dl = $crstr = \'\';

	if ($showalbum && $files > 0)
	{
		$crstr .= \'<input type="submit" name="psongsall" value="\'; 
		if ($files == 1 && $dirs == 0) $text = get_lang(65); else
		if ($files > 0 && $dirs == 0) $text = get_lang(66); else
		if ($files > 0 && $dirs > 0) $text = get_lang(67);
		$crstr .= $text.\'" class="fatbuttom"/>&nbsp;&nbsp;\';
		$crstr_dl = \'<input type="button" name="pdlall" value="\'.$text.\'" onclick="\'.jswin(\'dlall\', \'?action=dlall&amp;p=\'.$runinit[\'pdir64\'].\'&amp;d=\'.$runinit[\'drive\'], 130, 450).\'" class="fatbuttom"/>&nbsp;&nbsp;\';
	} 
	
	if ($files > 0) $crstr .= \'<input type="submit" onclick="javascript: if (!anyselected()) { alert(\'.addsq().get_lang(159).addsq().\'); return false; }" name="psongsselected" value="\'.get_lang(68).\'" class="fatbuttom"/>\';

	if ($dirs > 0 && $recursive) $crstr .= \'&nbsp;<input type="submit" name="pdirsall" value="\'.get_lang(275).\'" class="fatbuttom"/>&nbsp;\';
	
	$crstr_dl .= \'<input type="button" onclick="javascript: if (!anyselected()) alert(\'.addsq().get_lang(159).addsq().\'); else \'.jswin(\'dlselected\', \'?action=dlselectedjs\', 130, 450, false).\'" name="pdlselected" value="\'.get_lang(68).\'" class="fatbuttom"/>\';

	$playlists = db_getplaylist($u_id);
	$ploutput = \'\';
	if (count($playlists)>0)
	{
		if ($files > 0) $ploutput .= \'<input type="submit" name="addplaylist" onclick="javascript: if (!anyselected()) { alert(\'.addsq().get_lang(32).addsq().\'); return false; }" value="\'.get_lang(69).\'" class="fatbuttom"/>&nbsp;\';
		$ploutput .= \'<select name="sel_playlist" class="file">\';
		
		$playid = db_guinfo("defplaylist");
		for ($c=0,$cnt=count($playlists);$c<$cnt;$c++) 
		{		
			if ($playlists[$c][1] == $playid) $sel=\' selected="selected" \'; else $sel=\'\';
			$ploutput .= \'<option value="\'. $playlists[$c][1].\'"\'.$sel.\'>\'.$playlists[$c][0].\'</option>\';
		}
		$ploutput .= \'</select>&nbsp;\';
	}
	$ploutput .= \'<input type="hidden" name="drive" value="\'.$runinit[\'drive\'].\'"/>\';
	if (count($playlists)>0)
	{
		$ploutput .= \'<input type="submit" name="playplaylist" value="\'.get_lang(70).\'" class="fatbuttom"/>&nbsp;\';
		$ploutput .= \'<input type="submit" name="editplaylist" value="\'.get_lang(71).\'" class="fatbuttom"/>&nbsp;\';
	}
	
	$upload = \'<input type="button" name="upload" onclick="\'.jswin(\'upload\', \'?action=fupload\', 220, 520).\'" value="\'.get_lang(69).\'" class="fatbuttom"/>\';

	$ploutput .= \'<input type="button" name="newplaylist" onclick="\'.jswin(\'playlist\', \'?action=playlist_new\', 100, 350).\'" value="\'.get_lang(72).\'" class="fatbuttom"/>\';

	$selectallcode=\'<input type="button" value="+" class="fatbuttom" onclick="javascript: selectall();"/>&nbsp;&nbsp;<input type="button" value="-" class="fatbuttom" onclick="javascript: disselectall();"/>&nbsp;&nbsp;<input type="button" value="-+" class="fatbuttom" onclick="javascript: toggle();"/>\';
	
	?>
	<tr><td height="8"></td></tr>
	<tr>
	<td>
	<table border="0" cellspacing="0" cellpadding="0">	
		<tr>
		<?php
		
		if ($files > 0) echo \'<td align="left">\'.blackbox(get_lang(73), $selectallcode).\'</td><td width="5"></td>\';
		if (!empty($crstr)) echo \'<td align="left"> \'.blackbox(get_lang(74), $crstr).\'</td><td width="5"></td>\';
		if (ALLOWDOWNLOAD && db_guinfo(\'u_allowdownload\') && $cfg[\'archivemode\'] && db_guinfo(\'allowarchive\') && $files > 0) echo \'<td align="left"> \'.blackbox(get_lang(117), $crstr_dl).\'</td><td width="5"></td>\';

		echo \'<td align="left">\'.blackbox(get_lang(75), $ploutput).\'</td><td width="5"></td>\';
		if (ENABLEUPLOAD) echo \'<td align="left">\'.blackbox(get_lang(234), $upload).\'</td>\';
		?>
		</tr>
	</table>
	</td></tr>
	</table>
	</form>';

$kdesign['top'] = '
		switch($this->style)
		{
			case 0:
				?>
				<table width="100%" border="0" align="left" cellspacing="0" cellpadding="0">
				<tr>
					<td align="left" width="70%" valign="top">
					<?php if ($this->addform) $this->form(); ?>
					<table width="100%" border="0" cellpadding="0" cellspacing="0">					
					<tr>
					<td>
				<?php
			break;

			case 1:
				?>
				<table width="100%" border="0" align="left" cellspacing="0" cellpadding="0">
				<tr>
					<td width="320" valign="top">
					<?php infobox(); ?></td>
					<td align="left" valign="top">
						<?php if ($this->addform) $this->form(); ?>
						<table width="100%" border="0" cellpadding="0" cellspacing="0">
						<tr><td height="5"></td></tr>
						<tr>
						<td>						
				<?php
			break;
		}
	';

$kdesign['bottom'] = '

		switch($this->style)
		{
			case 0:
				echo \'</td><td valign="top" align="left" width="30%">\';
				infobox();
				echo \'</td></tr></table>\';
				break;
		
			case 1:
				echo \'</td></tr></table>\';
				break;
		}';


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



$dbi = array('user' => $db['user'], 'host' => $db['host'], 'name' => $db['name'], 'pass' => $db['pass']);

$mysqlserverv = '';

function check_all_tables(&$dbcount)
{
	global $dbtables, $dbtable, $dbdef, $installdb;
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
		} else $dbcount++;
		
		foreach ($dbtables AS $name => $val) 
		{
			if (!isset($ignore[$name]))
			{
				for ($i=0,$c=count($dbtables[$name]);$i<$c;$i++)
					if (db_execquery('SELECT `'.$dbtables[$name][$i].'` FROM '.$name.' LIMIT 1') == false) 
						$sql[] = 'ALTER TABLE '.$name.' ADD `'.$dbtables[$name][$i].'` '.$dbdef[$name][$dbtables[$name][$i]];
			}
		}		
		return $sql;
	} 
}

function check_version()
{
	global $app_build, $oldbuild;
	$result = db_execcheck('SELECT * FROM '.TBL_KPLAYVERSION, true);
	if ($result)
	{
		$data = mysql_fetch_array($result);
		if (isset($data['app_build']))
		{
			$oldbuild = (int)$data['app_build'];
			if ($oldbuild != $app_build) return true;
		}
	} else return true;
	return false;
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

function insthtmltable($title='')
{
	?>
	<table width="680" border="0" cellspacing="0" cellpadding="0" align="center">
	<tr> 
		<td><a href="http://www.kplaylist.net" title="Visit homepage"><img width="208" height="64" src="<?php echo getimagelink('kplaylist.gif'); ?>" alt="kPlaylist" border="0"/></a></td>
	</tr>
	<tr>
		<td height="12"></td>
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
			<td height="20"></td>
		</tr>
		</table>
		<?php
	}
	?>	
	<?php
}

function showsql()
{
	global $installdb, $installdbuser;
	echo '<table width="600" border="0" align="center">';
	echo '<tr><td class="wtext">'."\n";
	echo '<font size="4">The installers SQL code:</font>';
	echo "\n".'</td></tr>';

	if (isset($_GET['dbi'])) $dbi = $_GET['dbi']; else $dbi = 1;

	if ($dbi) $start = 1; else $start = 2;

	for ($i=$start;$i<count($installdb);$i++) echo '<tr><td class="wtext">'.str_replace("\n", '<br/>', $installdb[$i]).';<br/></td></tr>';

	if ($dbi)
	{
		echo '<tr><td class="wtext"><font color="green">'.$installdbuser[0].';</font></td></tr>';
		echo '<tr><td class="wtext"><font color="green">'.$installdbuser[2].';</font></td></tr>';	
	}
	echo '</table>';
}

function show_feedback($upgrade = false)
{
	global $app_ver, $app_build, $oldbuild, $mysqlserverv;
	?>
	<?php
		if (isset($_SERVER['REMOTE_ADDR'])) $iid = getrand(10000) + ip2long($_SERVER['REMOTE_ADDR']); else $iid = time() + getrand(10000);
		if (isset($_SERVER['SERVER_SOFTWARE'])) $os = $_SERVER['SERVER_SOFTWARE']; else $os = 'Unknown';
	?>	
	<form method="get" action="http://www.kplaylist.net/success.php">
	<input type="hidden" name="build" value="<?php echo $app_build; ?>"/>
	<input type="hidden" name="iid" value="<?php echo $iid; ?>"/>
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

function insterror($msg, $critical=false)
{
	kprintheader('Error during install', 1);
	insthtmltable('An error occured during install!');
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
	die();
}

function kpinstall($newdb=true)
{
	global $db, $dbi, $installdb, $initdb, $installdbuser, $mysqlserverv;

	$link = @mysql_connect($db['host'], $dbi['user'], $dbi['pass'], true);
	
	if (!$link) insterror('Could not establish connection to MySQL!');

	$mysqlserverv = mysql_get_server_info($link);

	$errno = 0;
	$err = '';
	$error = 0;
	$errors = '';

	if ($newdb)
	{
		if (!kcheckaccess($db['user'], $db['pass'], $err, $errno))
		{
			if (mysql_query($installdbuser[0], $link))
			{
				if (mysql_query($installdbuser[2], $link))
				{					
					if (!kcheckaccess($db['user'], $db['pass'], $err, $errno))
					{
						// ok - test with 4.1.					
						mysql_query($installdbuser[1], $link);
						mysql_query($installdbuser[2], $link);			
					}
									
					if (!kcheckaccess($db['user'], $db['pass'], $err, $errno))
						insterror('The MySQL user was created successfully, but login with this user is failing. The SQL that was used: '.$installdbuser[0]);								
				} else insterror('Unable to update privileges. The SQL that was used: '.$installdbuser[2].', MySQL response: '.mysql_error($link));
			} else insterror('Unable to create MySQL user. The SQL that was used: '.$userinst.', MySQL response: '.mysql_error($link));				
		}
		
		$result = mysql_query($installdb[1], $link);
		if ($result)
		{	
			// ok, now relogin
			mysql_close($link);
			$link = @mysql_connect($db['host'], $db['user'], $db['pass'], true);
			if (!$link) insterror('Could not establish connection to MySQL!');
		} else insterror('Unable to create database. The SQL that was used: '.$installdb[1].', MySQL response: '.mysql_error($link));	
	}

	if (mysql_select_db($db['name'], $link))
	{
		for ($i=2,$c=count($installdb);$i<$c;$i++)
		{				
			$result = mysql_query($installdb[$i], $link);
			if (!$result) 
			{ 
				$errors .= 'Failed query: '.str_replace("\n", '<br/>', $installdb[$i]).'<br/>';
				$errors .= mysql_error($link).'<br/>';
				$error = $i;
			}
		}
	} else insterror('Could not use the database ('.$db['name'].')');

	if (!$error) 
	{
		kprintheader('Installing MySQL database', 1);
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

 				<?php show_feedback(false); ?>
				
				Remember to visit <a href="http://www.kplaylist.net" target="_blank">http://www.kplaylist.net</a> for updates and help.
			</td>
			</tr>
			</table>
			<?php
			kprintend();
	} else insterror('MySQL installation may not be successful! <br/><br/>'.$errors);
}

function kinstall_selectmethod()
{
	global $PHP_SELF;
	kprintheader('Install', 1);
	insthtmltable('Welcome to the kPlaylist installer!');
	?>
	<form style="margin:0;padding:0" name="installform" method="post" action="<?php echo $PHP_SELF; ?>">
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
		<td>&nbsp;&nbsp;<input type="submit" name="newdatabase" value="Create new database" style="width:150px;height:25px;" class="fatbuttom"/>&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="submit" name="usedatabase" value="Use existing database" style="width:150px;height:25px;" class="fatbuttom"/>
		</td>
	</tr>
	<tr><td height="25"></td></tr>
	<tr>
		<td class="importnant"><b>PS!</b> If you are running kPlaylist on your own machine, the suggested method is to create a new database.</td>
	</tr>
	</table>
	</form>
	<?php
	kprintend();
}

function manual_upgrade($text)
{
	global $PHP_SELF;
	kprintheader('Manual upgrade', 1);
	insthtmltable('Manual upgrade necessary');
	?>
	<table align="center" width="650" cellpadding="0" cellspacing="0" border="0">
	<tr><td class="importnant"><?php echo $text; ?></td></tr>
	</table>
	<?php
	kprintend();
}

function kinstall_show_form($text='', $dbmethod=1)
{
	global $dbi, $db, $PHP_SELF, $cfg;

	kprintheader('Install', 1);

	$err = '';
	$errno = 0;
	if (kcheckaccess($db['user'], $db['pass'], $err, $errno) == 0 && $dbmethod) 
	{
		$dbi['user'] = 'root';
		$dbi['pass'] = '';
	}

	if (!$dbmethod) $btx = 'disabled="disabled" '; else $btx = '';

	insthtmltable();
	?>

	<form name="installform" method="post" action="<?php echo $PHP_SELF; ?>">
	<input type="hidden" name="instmethod" value="<?php echo $dbmethod; ?>"/> 
	<table width="650" border="0" align="center" class="tdborder" cellspacing="0" cellpadding="0">
	<tr>
	<td height="22" class="importnant" colspan="4">
	<?php if ($dbmethod)
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
	 <br/><br/><a href="<?php echo $PHP_SELF ?>?showsql=1&amp;dbi=<?php echo $dbmethod; ?>" target="_blank"><font class="importnantlink">Click here</font></a> to view what the installer is going to do. <br/><br/>Click 'Continue' when ready to install ! <br/>
	  <?php
		if ($dbi['user'] == 'root')
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
		<input type="text" name="mysqluser" size="25" <?php echo $btx; ?>value="<?php echo $dbi['user']; ?>" class="fatbuttom"/></td>
		<td colspan="2" class="wtext">default: <font color="green"><?php echo $db['user']; ?></font></td>
	</tr>
	<tr><td height="4"></td></tr>	
	<tr> 
		<td class="wtext">MySQL password:</td>
		<td><input type="password" name="mysqlpass" size="25" <?php echo $btx; ?>value="******************" class="fatbuttom"/></td>
		<td colspan="2" class="wtext">Not shown, look in script ($db['pass'])</td>
	</tr>
	
	<?php if ($dbmethod)
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
		<td><input type="text" name="mysqlhost" size="25" value="<?php echo $dbi['host']; ?>" disabled="disabled" class="fatbuttom"/></td>
	</tr>
	<tr><td height="4"></td></tr>	
	<tr> 
		<td class="wtext">MySQL database:</td>
		<td><input type="text" name="mysqldatabase" size="25" value="<?php echo $dbi['name']; ?>" disabled="disabled" class="fatbuttom"/></td>
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
		<input type="submit" name="back" value="Back" class="fatbuttom"/>&nbsp;
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

function kinstall_handler()
{
	global $db, $dbi;
	if (!function_exists('mysql_connect')) insterror('Function \'mysql_connect()\' does not exist! You need to compile PHP with MySQL support or enable MySQL support in your php configuration.', true);

	if (!function_exists('kprintheader')) insterror('Seems like we\'re not able to declare functions. Can\'t go further. Please upgrade PHP!', true);

	if (isset($_POST['usedatabase'])) $dbmethod = 0; else $dbmethod = 1;
	if (isset($_POST['instmethod'])) $dbmethod = $_POST['instmethod'];

	if (isset($_POST['back'])) kinstall_selectmethod();
	else	
	if (isset($_POST['continue']))
	{
		if ($dbmethod)
		{
			$user = $_POST['mysqluser'];
			$pass = $_POST['mysqlpass'];
		} else
		{
			$user = $db['user'];
			$pass = $db['pass'];
		}
		
		$err = '';
		$errno = 0;
		if (kcheckaccess($user, $pass, $err, $errno))
		{
			$dbi['user'] = $user;
			$dbi['pass'] = $pass;
			kpinstall($dbmethod);
			
		} else 
		{
			$msg = '<font color="red" size="2">Could not login with the supplied user name and password! MySQL said: '.$err.'</font>'; 
			if ($errno == 1251) $msg .= '<br/><br/><font color="red" size="2">Seems like you are running MySQL 4.1/5.0 or newer. Please go to the following location to read the solution: </font><a class="importnantlink" href="http://www.kplaylist.net/forum/viewtopic.php?p=2231" target="_blank">http://www.kplaylist.net/forum/viewtopic.php?p=2231</a>'; 
			kinstall_show_form($msg, $dbmethod);
		}
	} else
	if (isset($_POST['usedatabase']) || isset($_POST['newdatabase']) || isset($_POST['instmethod']))
	{		
		kinstall_show_form('', $dbmethod);
	} else
	if (isset($_GET['showsql']))
	{
		kprintheader();
		showsql();
		kprintend();
	} else kinstall_selectmethod();
	die();
}

if ($enable_install) 
{
	init_db_tables();
	kinstall_handler();
}

function show_upgrade($sql, $error="")
{
	global $db, $dbi, $PHP_SELF;
	kprintheader();
	insthtmltable('Welcome to the kPlaylist database upgrader.');
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
	<form name="upgradeform" style="margin:0;padding:0" method="post" action="<?php echo $PHP_SELF; ?>">
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
	<tr><td height="10"></td></tr>
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
	insthtmltable('kPlaylist database upgraded!');
	?>
	<table width="650" border="0" cellspacing="0" cellpadding="0" align="center">
	<tr>
		<td class="importnant">Upgrading performed successfully. Enjoy your new version of kPlaylist!<br/><br/> 
		Reload (F5) this page to get started.<br/><br/>
		
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

if (!$enable_install && check_version())
{
	init_db_tables();

	if (DBCONNECTION)
	{
		$lc = $setctl->get('lamecmd');
		if (!empty($lc))
		{
			if (crc32($lc) != 237452143 && $lc != $cfg['lamecmd'])
			{
				$stxt = 'Due to security reasons, the lame command is no longer available in the settings, and you have to edit the kPlaylist file manually to suit your existing setup. 
				Please change the $cfg[\'lamecmd\'] in the kPlaylist file <b>or</b> create a kpconfig file (look <a class="importnantlink" href="http://www.kplaylist.net/forum/viewtopic.php?t=659" target="_blank">here</a>) and change it to this: <br/><br/>';
				$stxt .= '$cfg[\'lamecmd\'] = \''.str_replace("'", "\'", $lc).'\';<br/><br/>';				
				$stxt .= 'When you are finished, press F5.';
				manual_upgrade($stxt);
				die();
			} else $setctl->set('lamecmd', '');
		}
	}

	$cnt = 0;
	$update_sql = check_all_tables($cnt);

	if ($cnt == 0) kinstall_handler();

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
	
				$sqls = check_all_tables($cnt);
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
		show_upgrade(check_all_tables($cnt),$error);
	} else
	{
		$sql = 'update '.TBL_KPLAYVERSION.' set app_build = "'.$app_build.'", app_ver = "'.$app_ver.'"';
		db_execcheck($sql);
	}
}


class kp_playlist
{
	function kp_playlist($listid)
	{
		$this->listid = -1;
		$this->name = '';
		$this->status = false;
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
				$m3ug = new m3ugenerator();
				for ($i=0;$i<$cnt;$i++) $m3ug->sendlink2($tunes[$i]);
				$m3ug->start();
				return true;
			}
		}
		return false;
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
	global $PHP_SELF,$u_cookieid, $base_dir, $u_id, $runinit,$phpenv, $cfg;
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
					'<input type="hidden" name="drive" value="'.$runinit['drive'].'"/>';
	
	$code = '<table width="800" cellspacing="0" border="0" cellpadding="0"><tr><td align="left">';
	$code .= '&nbsp;&nbsp;<input type="button" value="'.get_lang(34).'" class="fatbuttom" onclick="javascript: '."chhttp('$PHP_SELF?p=$prev&amp;d=".$runinit['drive']."');\"/>&nbsp;&nbsp;".
	$playlistlink.
	'<input type="submit" name="playplaylist" value="'.get_lang(42).'" class="fatbuttom"/>&nbsp;&nbsp;';

	if ($myown) $code .=
	"<input type=\"submit\" name=\"deleteplaylist\" onclick=\"javascript: if (!confirm('".get_lang(169)."')) return false;\"  value=\"".get_lang(43)."\" class=\"fatbuttom\"/>&nbsp;&nbsp;";
	if (ALLOWDOWNLOAD && db_guinfo('u_allowdownload') && $cfg['archivemode'] && db_guinfo('allowarchive')) $code .= '<input type="button" name="pdlall" value="'.get_lang(117).'" onclick="javascript: newwin(\'dlplaylist\', \''.$PHP_SELF.'?action=dlplaylist&amp;pid='.$plid.'\', 130, 450);" class="fatbuttom"/>&nbsp;&nbsp;';
	if ($myown) $code .= '<input type="text" name="playlistname" value="'.$name.'" size="35" class="fatbuttom"/>&nbsp;&nbsp;';

	if ($myown)
	{			
		$code .= '<font class="wtext">'.get_lang(44).'&nbsp;<input type="checkbox" name="shared" value="1" '.checked($public).'/>&nbsp;'.
		get_lang(125).'&nbsp;<input type="checkbox" name="shuffle" value="1" '.checked($shuffle).'/>&nbsp;&nbsp;&nbsp;</font>'.
		'<input type="submit" class="fatbuttom" name="saveplaylist" value="'.get_lang(45).'"/>';
	
		$e = array(0 => '', 1 => '', 2 => '', 3 => '');
		$e[$sort] = ' selected="selected"';
		
		$code .= '</td></tr><tr><td height="8"></td></tr>';
		
		$code .= '<tr><td align="left">&nbsp;&nbsp;<select name="sort" class="fatbuttom">'.
				'<option value="0"'.$e[0].'>'.get_lang(170).'</option>'.
				'<option value="1"'.$e[1].'>'.get_lang(171).'</option>'.
				'<option value="2"'.$e[2].'>'.get_lang(173).'</option>'.
				'<option value="3"'.$e[3].'>'.get_lang(180).'</option>'.
				'</select>';
		$code .= '&nbsp;&nbsp;<input type="submit" name="sortplaylist" value="'.get_lang(172).'" class="fatbuttom"/>&nbsp;&nbsp;&nbsp;';
	}
	
	$code .= '&nbsp;&nbsp;';
	if (UNAUTHORIZEDSTREAMS) $code .= '<a href="'.$PHP_SELF.'?streamplaylist='.$plid.'&amp;extm3u=true">i</a>';
	$code .= '</td></tr></table>';

	echo '<form style="margin:0;padding:0" action="'.$PHP_SELF.'" method="post">';	
	blackbox(get_lang(46, $name, $many),$code,0);
	echo '</form>';

	echo '<br/>';
	
	echo '<form style="margin:0;padding:0" name="psongs" action="'.$PHP_SELF.'" method="post">';	
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
		
		while ($row = mysql_fetch_array($result))
		{
			$count++;			
			$id = $row['id'];

			$f2 = new file2($row['sid'], true);
			if (!$f2) continue;
			$fexists = $f2->ifexists();
			$id3 = $f2->getid3();

			if (($count % 2) == 0) echo '<tr class="row2nd">'; else echo '<tr>';
			
			echo '<td class="file" align="center" width="60">
			<input type="checkbox" class="wtext" name="selected[]" value="'.$id.'"/></td>
			<td width="60" class="wtext">';

			if ($myown) echo '<input class="smalltext" type="text" name="seq[]" value="'.lzero($row['seq']).'" size="4"/>'; 
				else
			echo lzero($row['seq']);
			echo '</td><td width="100" class="file">';
			$idv3info  = '';

			if (!$fexists)
			{ 
				echo '<font color="RED">'.get_lang(182).'</font>'; 
				$countfails++; 
			} else
			{
				if (!empty($id3['bitrate']) && !empty($id3['length'])) $idv3info = $id3['bitrate'].'kb - '.$id3['length']; 
				if (is_numeric($id3['lengths'])) $totplaytime += $id3['lengths'];
				echo get_lang(181);
			}
			echo '</td><td width="120" class="wtext">'.$idv3info.'</td>';
			echo '<td width="100" class="file">';
			if ($myown) echo '<a title="'.get_lang(60).'" class="smalltext" href="'. $PHP_SELF . "?action=delsingleplaylist&amp;plid=$plid&amp;del=$id&amp;p=$prev&amp;d=".$runinit['drive'].'">&nbsp;'.get_lang(43).'&nbsp;</a>';
			echo '</td><td width="360" align="left" class="file">';

			if ($fexists) echo '<a href="'.$f2->weblink().'">'.checkchs($f2->gentitle(array('title', 'artist'), 70)).'</a>'; 
				else echo '&nbsp;';

			
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

		echo	'<input type="hidden" name="drive" value="'.$runinit['drive'].'"/>'.
				'&nbsp;&nbsp;'.get_lang(73).'&nbsp;&nbsp;<input type="button" value="+" class="fatbuttom" onclick="javascript: selectall();"/>&nbsp;&nbsp;'.
				'<input type="button" value="-" class="fatbuttom" onclick="javascript: disselectall();"/>&nbsp;&nbsp;'.
		get_lang(57).'&nbsp;&nbsp;<input type="submit" class="fatbuttom" onclick="javascript: if (!anyselected()) { alert(\''.get_lang(159).'\'); return false; }" name="playselected" value="'.get_lang(42).'"/>&nbsp;&nbsp;';

		if ($myown) echo '<input type="submit" class="fatbuttom" onclick="javascript: if (!anyselected()) { alert(\''.get_lang(159).'\'); return false; } else if (!confirm(\''.get_lang(210).'\')) return false;" name="delselected" value="'.
		get_lang(43).'"/>&nbsp;&nbsp;'.get_lang(58).'&nbsp;&nbsp;<input type="submit" class="fatbuttom" name="saveseq" value="'.get_lang(45).'"/>';

		echo '&nbsp;&nbsp;</td></tr><tr><td colspan="6">&nbsp;</td></tr>';
		echo '</table>';
	} else echo get_lang(302);
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
		<td class="wtext" colspan="2" width="240"><input type="text" name="name" class="wtext"/></td>
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
	$result = db_execquery('SELECT u_id, name, listid FROM '.TBL_PLAYLIST.' WHERE u_id = '.$u_id.' ORDER by name ASC');
	$playlists = array();
	if ($result !== false) while ($row = mysql_fetch_array($result)) $playlists[] = array($row['name'], $row['listid']);
	return $playlists;
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
			if ($sbase[strlen($sbase)-1] != '/') $sbase .= '/';
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
				break;

			case 1:
				$setctl->set('includeheaders', 0);
				$setctl->set('showkeyteq', 0);
				$setctl->set('showupgrade', 0);
				$setctl->set('showstatistics', 0);
				$setctl->set('albumcover', 0);
				$setctl->set('albumresize', 0);
				$setctl->set('fetchalbum', 0);
				$setctl->set('showlyricslink', 0);				
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
					$value = basedir_rewrite($value);
					if ($value != $setctl->get('base_dir')) $setctl->set('basedir_changed', 1);
					break;

				case 'timeout':
						if ($value < 600 && $value != 0) $value = $setctl->get('timeout');
						break;

				case 'uploadpath':
					if (!empty($value))
					{
						$value = slashtranslate($value);
						if (strlen($value) > 0) if ($value[strlen($value)-1] != '/') $value .= '/';	
					}
					break;

				case 'filetemplate':
					$value = stripcslashes($value);
					break;

				case 'homepage':
					$value = htmlentities($value);
					break;	
				
				case 'albumfiles': $value = stripcslashes($value); break;

				case 'uploadflist': $value = stripcslashes($value); break;
				
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
			<td class="wtext"><?php echo get_lang(324); ?></td>
			<td class="wtext">			
			<?php					
				$options = array(0 => array(0, htmlentities('<').get_lang(148).htmlentities('>')));				
				$res = db_execquery('SELECT u_login, u_id FROM '.TBL_USERS.' WHERE utemplate = 1');
				if ($res) while ($row = mysql_fetch_assoc($res)) $options[] = array($row['u_id'], $row['u_login']);			
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
			<?php

			break;
		
		case 2:
			?>
			<tr>
				<td class="wtext"><?php echo get_lang(127); ?></td>
				<td class="wtext">
					<input type="text" name="base_dir" class="fatbuttom" size="50" value="<?php echo $setctl->get('base_dir'); ?>"/>&nbsp;
					<input type="button" class="fatbuttom" onclick="javascript: newwinscroll('find', '<?php echo $PHP_SELF; ?>?action=findmusic', 450, 600);" value="<?php echo get_lang(289); ?>"/>			
				</td>
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
	global $PHP_SELF, $phpenv, $setctl;
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


function webauthenticate()
{
	global $_POST, $u_cookieid, $phpenv, $setctl, $cfg, $u_id;
	if (!$cfg['disablelogin']) 
	{
		if (isset($_POST['user']) && isset($_POST['password']))
		{
			$user = myescstr($_POST['user']);
			$pass = myescstr($_POST['password']);
			if (!empty($user) && !empty($pass))
			{
				if (db_verifyuserpass($user, $pass) == 1)
				{
					if ($cfg['demomode']) 
					{
						$result = db_execquery('SELECT u_sessionkey FROM '.TBL_USERS.' WHERE u_pass = "'.md5($pass).'" AND u_login = "'.$user.'"');
						$row = mysql_fetch_array($result);
						$u_cookieid = $row['u_sessionkey'];
					} else
					{
						db_execquery('INSERT INTO '.TBL_SESSION.' SET u_id = '.$u_id.', login = '.time().', refreshed = '.time().', ip = '.ip2long($phpenv['remote']));
						$u_cookieid = mysql_insert_id();
						db_execquery('UPDATE '.TBL_USERS.' SET u_status = 1, u_ip = "'.$phpenv['remote'].'", u_sessionkey = "'.$u_cookieid.'", laston = u_time, u_time = '.time().' WHERE u_id = '.$u_id);	
					}
					if ($setctl->get('timeout') > 0 && isset($_POST['rememberme'])) $expiration = time() + $setctl->get('timeout'); else $expiration = 0;
					setcookie($cfg['cookie'], $u_id.'-'.$u_cookieid, $expiration);
					return true;
				}
			}
		}
	} else return true;
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
		if (count($ckexp) == 2 && is_numeric($ckexp[0]) && is_numeric($ckexp[1]))
		{
			if ($stream)
				$sql = 'SELECT u_id, login as u_time, sstatus FROM '.TBL_SESSION.' WHERE u_id = '.$ckexp[0].' AND sessionid = '.$ckexp[1];
			else 
				$sql = 'SELECT u_id, login as u_time, sstatus FROM '.TBL_SESSION.' WHERE u_id = '.$ckexp[0].' AND sessionid = '.$ckexp[1].' AND logout = 0';

			$result = db_execquery($sql);
			if ($result)
			{				
				$row = mysql_fetch_array($result);
				$u_id = $row['u_id'];
				loadvalidated($u_id);
				$time = $row['u_time'];
				if ($cfg['demomode'] == 1) return 1;
				if ($setctl->get('timeout') != 0) if (($time + $setctl->get('timeout')) < time()) return 0;
				if ($valuser) 
				{
					if ($row['sstatus'] == 2) $valuser->setro('u_access', 1);
					return 1;
				}
			}
		}
		return 0;		
	}
}

function db_verifyuserpass($user, $pass)
{
	global $u_id;
	$result = db_execquery('SELECT u_id FROM '.TBL_USERS.' WHERE u_login = "'.$user.'" AND u_pass = "'.md5($pass).'" AND u_booted = 0 AND u_status != 2 AND utemplate = 0');
	$row = mysql_fetch_array($result);
	$u_id = $row['u_id'];
	return mysql_num_rows ($result);
}

function addhistory($u_id, $sid, $tid = 0)
{
	global $runinit;
	$active = 1;
	if ($runinit['astream']) $mid = mysql_thread_id(); 
	else
	{
		$mid = 0;
		$active = 0;
	}
	if (db_execquery('INSERT INTO '.TBL_MHISTORY.' SET active = '.$active.', mid = '.$mid.', u_id = '.$u_id.', s_id = '.$sid.', utime = '.time().', tid = '.$tid)) return mysql_insert_id();
}

function updateactive($id)
{	
	global $runinit;
	if ($runinit['astream']) db_execquery('UPDATE '.TBL_MHISTORY.' SET active = 1, mid = '.mysql_thread_id().' WHERE h_id = '.$id);
}

function updatehistory($id, $pos)
{
	$res = db_execquery('SELECT h.dwritten, s.fsize FROM '.TBL_MHISTORY.' h, '.TBL_SEARCH.' s WHERE h_id = '.$id.' AND s.id = h.s_id');
	if ($res && mysql_num_rows($res) == 1)
	{
		$row = mysql_fetch_row($res);
		$add = (int) $row[0];
		$add = $add + $pos;
		$size = $row[1];

		if ($add > $size) $add = $size;
			
		if ($size > 0 && $add > 0)
		{
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

function get_archiver_combo($default)
{
	global $archivers;
	$out = '';
	for ($i=0,$c=count($archivers);$i<$c;$i++) 
	{
		if (is_array($archivers[$i]) && $archivers[$i][0])
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
	$ckexp = explode('-', $cookie);
	if ($cfg['demomode'] != 1)
	{
		if (count($ckexp) == 2 && is_numeric($ckexp[0]) && is_numeric($ckexp[1]))
		{	
			db_execquery('UPDATE '.TBL_USERS.' SET u_status = 0, u_sessionkey = 0 WHERE u_sessionkey = '.$ckexp[1].' and u_ip = "'.$ip.'"');
			db_execquery('UPDATE '.TBL_SESSION.' SET logout = '.time().' WHERE sessionid = '.$ckexp[1]);
		}
	}
	setcookie($cfg['cookie'],'');
}

function chsessionstatus($cookie, $status=0)
{
	global $cfg;
	$ckexp = explode('-', $cookie);
	if ($cfg['demomode'] != 1)
	{
		if (count($ckexp) == 2 && is_numeric($ckexp[0]) && is_numeric($ckexp[1]))
		{
			db_execquery('UPDATE '.TBL_SESSION.' SET sstatus = '.$status.' WHERE sessionid = '.$ckexp[1]);
			return true;
		}
	}
	return false;
}

function adminlogout($uid)
{
	$res = db_execquery('SELECT u_sessionkey FROM tbl_users WHERE u_id = '.$uid);
	if (mysql_num_rows($res) > 0)
	{
		$row = mysql_fetch_row($res);
		$sessionkey = $row[0];
		db_execquery('UPDATE '.TBL_USERS.' SET u_sessionkey = 0, u_status = 0 WHERE u_id = '.$uid);
		db_execquery('UPDATE '.TBL_SESSION.' SET logout = '.time().' WHERE u_id = '.$uid.' AND sessionid = '.$sessionkey.' AND logout = 0');
	}
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
		$result = db_execquery('SELECT u_id FROM '.TBL_USERS.' WHERE u_login = "'.$this->kpu->get('u_login').'"');
		if (mysql_num_rows($result) > 0)
		{
			$row = mysql_fetch_row($result);
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
		$this->kpu->setallowed(array('u_booted', 'u_allowdownload', 'lameperm', 'allowemail', 'u_name', 'u_login', 'u_comment', 'u_access', 'udlrate', 'email', 'lang', 'utemplate', 'streamengine', 'allowarchive', 'archivesize'));
		$this->kpu->set('u_booted', 0);
		$this->kpu->set('u_allowdownload', 0);
		$this->kpu->set('lameperm', 0);
		$this->kpu->set('allowemail', 0);
		$this->kpu->set('allowarchive', 0);	
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
	$id = frpost('u_id', -1);
	$tempid = frpost('templateid', 0);

	$sv = new saveuser();
	$sv->setid($id);
	
	if (isset($_POST['passchange']) && $id != -1) $changepw = 1; else $changepw = 0;
	if (isset($_POST['password'])) $pass = myescstr($_POST['password']); else $pass = '';	
	if ($tempid > 0 && $id == -1) $sv->fromtemplate($tempid);

	$sv->frompost();

	if ($sv->validname()) 
	{
		if ($sv->usernameok())
		{
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
	global $PHP_SELF, $u_id, $setctl;

	if ($kpu->id == -1) $title = get_lang(96); else $title = get_lang(95);
	if ($kpu->get('utemplate') == 1)
	{
		$template = true;
		$title = get_lang(321);
	} else $template = false;

	kprintheader($title, 1);
	?>
	<form method="post" action="<?php echo $PHP_SELF; ?>">
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

function KSignup()
{
	global $_POST, $_GET, $deflanguage, $setctl;
	if (USERSIGNUP && empty($_GET['usersignup']) && empty($_POST['usersignup'])) 
	{
		if ($setctl->get('approvesignup')) $ustatus = 2; else $ustatus = 1;
		
		if (isset($_POST['adduser']))
		{
			if (!empty($_POST['u_name']) && !empty($_POST['u_login']) && !empty($_POST['password']) && !empty($_POST['email'])) 
			{
				$result = db_execquery('SELECT u_id FROM '.TBL_USERS.' WHERE u_login = "'.myescstr($_POST['u_login']).'"');
				if (mysql_num_rows($result) == 0 && strtolower(myescstr($_POST['u_login'])) != 'admin')
				{				
					$kpu = new kpuser();
					if ($setctl->get('signuptemplate') > 0) $kpu->load($setctl->get('signuptemplate'));
					$kpu->id = -1;
					$kpu->set('utemplate', 0);
					$kpu->set('u_login', $_POST['u_login']);
					$kpu->set('u_name', $_POST['u_name']);
					$kpu->set('u_pass', md5($_POST['password']));
					$kpu->set('u_comment', $_POST['u_comment']);
					$kpu->set('u_access', 1);
					$kpu->set('email', $_POST['email']);
					$kpu->set('created', time());
					$kpu->set('u_status', $ustatus);
					
					if ($kpu->store(false)) 
					{
						$text = get_lang(259);
						if ($setctl->get('approvesignup')) $text .= '&nbsp;'.get_lang(285);
						signup_form($text, false);
					} else signup_form(get_lang(56));
				} else signup_form(get_lang(312));
			} else signup_form(get_lang(284));
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
		<td width="50%" class="wtext">* <?php echo get_lang(97); ?></td>
		<td width="50%"><input type="text" name="u_name" class="fatbuttom" value="<?php echo fruser('u_name'); ?>"/></td>
	</tr>    
	<tr> 
		<td class="wtext">* <?php echo get_lang(98); ?></td>
		<td><input type="text" name="u_login" class="fatbuttom" value="<?php echo fruser('u_login'); ?>"/></td>
	</tr>
	<tr> 
		<td class="wtext">* <?php echo get_lang(100); ?></td>
		<td><input type="password" name="password" class="fatbuttom" value=""/></td>
	</tr>    
	<tr> 
		<td class="wtext"><?php echo get_lang(101); ?></td>
		<td><input type="text" name="u_comment" class="fatbuttom" value="<?php echo fruser('u_comment'); ?>"/></td>
	</tr>
	<tr> 
		<td class="wtext">* <?php echo get_lang(223); ?></td>
		<td><input type="text" name="email" class="fatbuttom" value="<?php echo fruser('email'); ?>"/></td>
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

class userhistory
{
	function userhistory()
	{
		$this->rows = 0;
		$this->uid = -1;
		$this->filter = -1;
		$this->perpage = 18;
	}

	function setuid($huid)
	{
		global $uid;
		if ($huid != $uid)
		{
			if (db_guinfo('u_access') == 0) $this->uid = $huid;		
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
		global $cfg, $PHP_SELF;
		
		$ca = new caction();
		$ca->updatelist();

		if (!$from)
		{
			$sql = 'SELECT count(*) as cnt FROM '.TBL_MHISTORY.' h, '.TBL_SEARCH.' s WHERE h.s_id = s.id and h.u_id = '.$this->uid;
			if ($this->filter != -1) $sql .= ' AND tid = '.$this->filter;
			$res = db_execquery($sql);
			if (mysql_num_rows($res) > 0)
			{
				$row = mysql_fetch_assoc($res);
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
			4 => array(3, get_lang(267))
		);

		?>
		<form method="post" action="<?php echo $PHP_SELF; ?>">
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
				<input class="fatbuttom" type="button" onclick="javascript: location = '<?php echo $PHP_SELF.'?action=showusers'; ?>';" name="back" value="<?php echo get_lang(34); ?>"/>&nbsp;&nbsp;	
				<input class="fatfield" size="3" maxlength="5" type="text" name="chperpage" value="<?php echo $this->perpage; ?>"/> <?php echo get_lang(178); ?>&nbsp; <?php echo genselect('cfilter', $options, $this->filter, 'fatfield'); ?>&nbsp;
				<input type="submit" value="<?php echo get_lang(107) ;?>" name="Refresh" class="fatbuttom"/>
			</td>
		</tr>
		<tr>
			<td colspan="3" height="15"></td>
		</tr>	
		<?php
		
		
		$f2 = new file2();
			
		$tidarray = array(0 => get_lang(183), 1 => get_lang(117), 2 => get_lang(223), 3 => get_lang(267));	
		if ($res)
		{
			$cnt = 0;
			while ($row = mysql_fetch_assoc($res))
			{
				$f2->fname = $row['free'];
				$f2->id3['artist'] = $row['artist'];
				$f2->id3['album'] = $row['album'];
				$f2->id3['title'] = $row['title'];
				
				$title = file_parse($f2, '', '', '[%t - %l - %a|%f]');

				if ($row['active']) $class = 'filemarked'; else $class = 'wtext';
				if (($cnt % 2) == 0) echo '<tr class="row2nd">'; else echo '<tr>';
				?>
					<td class="file"><?php echo $tidarray[$row['tid']]; ?></td>
					<td class="file" nowrap="nowrap"><a class="hotnb" href="<?php echo $f2->weblink($row['id'], $row['date']); ?>"><font class="<?php echo $class; ?>"><?php echo strlen($title) > 60 ? substr($title, 0, 60).' ..' : $title; ?></font></a></td>
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
	global $PHP_SELF, $setctl, $u_id, $cfg;
	kprintheader(get_lang(121),1);

	$result = db_execquery('SELECT * FROM '.TBL_USERS.' ORDER BY u_time DESC');
	
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

	while ($row = mysql_fetch_array($result)) 
	{
		if ($cnt % 2 == 0) echo '<tr class="row2nd">'; else echo '<tr>';
		$cnt++;

		$ulogin = $row['u_login'];
		
		$uname = '<font title="'.date($cfg['dateformat'],$row['created']).'"';

		if ($row['utemplate'] == 1) $template = true; else $template = false;	

		if ($template) $uname .= ' color="blue">'.get_lang(321);
			else
		if ($row['u_access'] == 0) $uname .= ' color="red">'.$row['u_name']; else $uname .= '>'.$row['u_name'];
		$uname .= '</font>';
		
		echo '<td class="file"><a class="hotnb" href="'. $PHP_SELF .'?action=useredit&amp;id='.$row['u_id'].'" title="'.get_lang(95).'">'.$ulogin.'</a></td>';
		echo '<td class="file">'.$uname.'</td>';
		echo '<td class="file"><font title="';
		echo date($cfg['dateformat'], $row['u_time']);
		echo '"> '.$row['u_ip']. '</font></td>';

		if ($setctl->get('timeout') != 0 && $row['u_status'] == 1)  if (((int)$row['u_time'] + $setctl->get('timeout')) < time()) $row['u_status'] = 0;

		switch ($row['u_status'] )
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
		
		if ($u_id != $row['u_id']) echo '<a class="hotnb" onclick="javascript: if (!confirm(\''.get_lang(175).'\')) return false;" href="'.$PHP_SELF.'?action=userdel&amp;id='.$row['u_id'].'" title="'.get_lang(105).'">'.get_lang(109).'</a>&nbsp;&nbsp;';
		
		if ($row['u_status'] == 2)
		{
			echo '<a class="hotnb" href="'. $PHP_SELF .'?action=useractivate&amp;id='.$row['u_id'].'">'.get_lang(283).'</a>';
		} else
		{
			if (!$template) echo '<a class="hotnb" href="'. $PHP_SELF .'?action=userhistory&amp;id='.$row['u_id'].'" title="'.get_lang(176).'">'.get_lang(177).'</a>&nbsp;&nbsp;';
			if ($u_id != $row['u_id']) echo '<a class="hotnb" href="'. $PHP_SELF .'?action=admineditoptions&amp;id='.$row['u_id'].'" title="'.get_lang(123).'">'.get_lang(123).'</a>&nbsp;&nbsp;';
			if ($row['u_status'] == 1 && $u_id != $row['u_id']) echo '<a class="hotnb" href="'. $PHP_SELF .'?action=userlogout&amp;id='.$row['u_id'].'" title="'.get_lang(106).'">'.get_lang(110).'</a>';
			if ($template) echo '<a class="hotnb" href="'. $PHP_SELF .'?action=newusertemplate&amp;id='.$row['u_id'].'">'.get_lang(96).'</a>&nbsp;&nbsp;';
		
		}
		echo '</td></tr>';
	}

	echo '</table>';
	echo '<form style="margin:0;padding:0" action="'.$PHP_SELF.'" method="post">';
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
		$this->numerics = array('hotrows', 'searchrows', 'lang', 'archer', 'theme', 'lamerate', 'pltype', 'created', 'udlrate', 'u_access', 'textcut', 'dircolumn', 'utemplate');
		$this->stripslash = array('u_comment' => true, 'email' => true);
		$this->id = -1;
		$this->ched = array();
	}

	function setallowed($allowed)
	{
		$this->allowed = $allowed;
	}

	function load($id)
	{
		if (is_numeric($id) && $id != -1)
		{
			$res = db_execquery('SELECT * FROM '.TBL_USERS.' WHERE u_id = '.$id);
			if (mysql_num_rows($res) == 1) 
			{
				$this->row = mysql_fetch_assoc($res); 
				$this->id = $id;
				return true;
			}
		}
		return false;		
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
			if ($res !== false) 
			{
				$this->load($this->id);
				return true;
			}
		}
		return false;
	}

	function store($changesonly = true)
	{
		$sql = $this->gensql(2, $changesonly);
		$res = db_execquery($sql);		
		if ($res !== false) 
		{
			$this->load(mysql_insert_id());
			return true;
		}
	}
}

function save_useroptions($uid, $_POST)
{
	global $u_id, $deflanguage;
	$state = 0;

	$kpu = new kpuser();
	$kpu->setallowed(array('extm3u', 'plinline', 'hotrows', 'searchrows', 'lang', 'archer', 'lamerate', 'theme', 'email', 'u_pass', 'pltype', 'textcut', 'dircolumn'));
	if ($kpu->load($uid))
	{
		if (isset($_POST['changepass']) && isset($_POST['password']) && !empty($_POST['password']))
		{
			if (isset($_POST['curpassword']))
			{
				if (db_guinfo('u_pass') == md5($_POST['curpassword']))
				{
					$state = 2;
					$kpu->set('u_pass', md5($_POST['password']));
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
	global $PHP_SELF, $klang, $deflanguage, $lamebitrates, $setctl, $themes;
	$result = db_execquery('SELECT * from '.TBL_USERS.' WHERE u_id = '.$id);
	if ($result) $row = mysql_fetch_array($result);
	if (!$row) die();
	if ($row['extm3u'] == 1) $ext3mu = 'checked="checked"'; else $ext3mu = '';
	if ($row['plinline'] == 1) $plinline = 'checked="checked"'; else $plinline = '';
	if ($row['utemplate'] == 1) $template = true; else $template = false;
		
	kprintheader(get_lang(123),1);
	?>
	<form name="useroptions" method="post" action="<?php echo $PHP_SELF; ?>">
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
			$options = array(0 => array(0, get_lang(221)));
			for ($i=1;$i<count($lamebitrates);$i++) $options[] = array($i, $lamebitrates[$i]);
			echo genselect('lamerate', $options, $row['lamerate']);
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
		<?php
		$options = array();
		for ($i=0,$c=count($themes);$i<$c;$i++) $options[] = array($i, $themes[$i][0]);
		echo genselect('theme', $options, $row['theme']);
		?>
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

function sanstr($name)
{
	return stripslashes(strip_tags(fruser($name)));
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
		global $cfg;
		$this->query = 'SELECT *,count(free) as many, sum(lengths) as lengths FROM '.TBL_SEARCH.' WHERE genre = '.db_guinfo('defgenre').' AND length(trim(album)) > 0 GROUP BY album HAVING many > '.$cfg['titlesperalbum'].' ORDER BY artist ASC';
		if ($from && $to) $this->query .= ' LIMIT '.$from.','.$to;	
		$this->header = get_lang(147);
		$this->ndir = get_lang(153, gengenres(db_guinfo('defgenre')));
	}

	function hotselect($char,$from=0,$to=0)
	{
		global $cfg;
		$this->query = 'SELECT *,count(*) AS many, sum(lengths) AS lengths FROM '.TBL_SEARCH.' WHERE ';
	
		switch($char)
		{
			case '*': $this->query .= 'rtrim(artist) NOT REGEXP("^[0-9a-zA-Z]")'; break;
			case '0': 
					for ($i=0;$i<10;$i++) 
					{
						$this->query .= 'rtrim(artist) like "'.$i.'%"';
						if ($i < 9) $this->query .= ' or ';
					}
					break;
			default: $this->query .= 'rtrim(artist) like "'.$char.'%"'; break;
		} 
		$this->query .= ' and length(rtrim(album)) > 0 group by rtrim(album),rtrim(artist) having many > '.$cfg['titlesperalbum'].' order by artist';
		if ($from && $to) $this->query .= ' LIMIT '.$from.','.$to;
		$this->headertext = get_lang(31, $char);
		$this->ndir = get_lang(30, $char);
	}

	function whats_hot($filter=0,$from=0,$to=0)
	{
		global $cfg;
		
		$this->from = $from;
		$this->ndir = get_lang(3);
		
		$lastcheck = mktime(0, 0, 0, date('n'), 1, date('Y'));
		$days = array(1 => 31, 2 => 28, 3 => 31, 4 => 30, 5 => 31, 6 => 30, 7 => 31, 8 => 31, 9 => 30, 10 => 31, 11 => 30, 12 => 31);

		$res = db_execquery('SELECT MIN(utime) FROM '.TBL_MHISTORY);
		$row = mysql_fetch_row($res);
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
			
			default:
				if (!$found)
				{
					$this->ndir .= ' '.get_lang(239);
					$this->special = 1;
					$this->query = 'select sum(hits) as cntr, artist, id, album, bitrate, date, sum(lengths) as lengths, genre, drive, count(free) as many, dirname, free, fsize from '.TBL_SEARCH.' where rtrim(album) != "" group by album,artist having cntr >= '.$cfg['whatshotminimumhits'].' and many > '.$cfg['titlesperalbum'].' order by cntr desc, many desc';
				}
				break;
		}

		if ($this->special == 3) $this->query = 'SELECT s.*,count(*) as many, sum(h.dpercent) as rate from '.TBL_MHISTORY.' h, '.TBL_SEARCH.' s where h.utime >= '.$uxfrom.' AND h.utime <= '.$uxto.' AND trim(s.album) != "" AND h.s_id = s.id group by s.album,s.artist having many >= '.$cfg['whatshotminimumhits'].' order by many desc';

		if ($from && $to) $this->query .= ' LIMIT '.$from.','.$to;

		$fsel = array(0 => '', 1 => '', 2 => '');
		$fsel[$filter] = ' selected="selected"';

		$extra = '</td></tr><tr><td height="5"></td></tr><tr><td class="notice">'.get_lang(238).':&nbsp; ';		
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

		$extra .= '</select>&nbsp;';
		$extra .= '<input type="submit" class="fatbuttom" name="hotoptions" value="'.get_lang(107).'"/>';	
		$extra .= '</td></tr><tr><td height="15"></td></tr><tr><td>';
		$this->extra = $extra;
		$this->headertext = get_lang(3);
	}

	function whats_new($from=0, $to=0)
	{
		global $setctl, $phpenv;
		$this->query = 'select id,drive,dirname,fsize,date,free,album,artist,count(*) as many,sum(lengths) as lengths from '.TBL_SEARCH.' where trim(album) != "" '.grpsql().' order by date desc';

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

		while ($row = mysql_fetch_array($result)) 
		{
			$f2 = new file2($row['id'], false, $row);
			$albumlink = $setctl->get('streamurl').$phpenv['streamlocation'].'?n=-1&amp;d='.$row['drive'].'&amp;p='.urlencode(base64_encode($f2->relativepath));

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

		echo $this->extra;

		if (!$this->rows)
		{
			$result = db_execquery($this->query);
			$this->rows = mysql_num_rows($result);
		} else $result = db_execquery($this->query, true); 
		
		$cntr= $this->from;
		$many = 0;			
		
		echo '</td></tr>';
		while ($row = mysql_fetch_array($result)) 
		{
			$f2 = new file2($row['id'], false, $row);			
			$dir = $f2->relativepath;
			$many++;
			if ($many > db_guinfo('hotrows')) break;
			$ainf = gen_aheader($row['album'], $row['artist'], $row['lengths'], $row['many']);		
		
			printdirhtml();
			
			switch ($this->special)
			{
				case 0: echo print_dir($row['drive'],$row['artist'].' - '.$row['album'], $dir, -1, 'link.gif', $row['artist'], $ainf, '', true, 0, $row['id']); 
						break;

				case 1: echo print_dir($row['drive'],' '.lzero($cntr+1).' '.$row['artist'].' -  '.$row['album'], $dir, -1, 'link.gif', '', null, '', true, $row['cntr'], $row['id']);							
						$cntr++;	
						//echo print_dir($row['drive'],' '.lzero($cntr+1).' '.$row['artist'].' -  '.$row['album'], $dir, -1, 'link.gif',$row['cntr'].' hits - '.$row['many'].' tunes', $ainf, '', true, $row['cntr'], $row['id']);							
						$cntr++;	
						break;

				case 2: echo print_dir($row['drive'],date($cfg['dateformat'],$row['date']).' - '.$row['artist'].' - '.$row['album'], $dir, -1, 'link.gif',$row['artist'],$ainf, '', true,0, $row['id']); 
						break;
				
				
				case 3: echo print_dir($row['drive'], ' '.lzero($cntr+1).' '.$row['artist'].' -  '.$row['album'], $dir, -1, 'link.gif', '', null, '', true, $row['many'], $row['id']);							
						$cntr++;
						break;
			}

			printdirhtml(false);
		}
		if (!$many) echo '<tr><td><font class="fdet">'.get_lang(10).'</font></td></tr>';		
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
	
	kprintheader($gl->headertext, 1); 
	$kpd = new kpdesign();
	$kpd->top();
	
	$gl->nhghlist();
	$nv = new navi($id, $gl->rows, true);
	$nv->writenavi();
	$gl->endlist();
	$kpd->bottom();
	kprintend();
}

function hotselect($char='')
{
	$gl = new genlist();
	$gl->hotselect($char);
	kprintheader($gl->headertext, 1); 
	$kpd = new kpdesign();
	$kpd->top();
	$gl->nhghlist();
	$nv = new navi(5, $gl->rows, true);
	$nv->setfollow('hchar', $char);
	$nv->writenavi();
	$gl->endlist();
	$kpd->bottom();
	kprintend();
}

function updatehotlist()
{
	$hotsels = '';
	$qres = db_execquery('SELECT LOWER(SUBSTRING(artist,1,1)) AS ch FROM '.TBL_SEARCH.' WHERE TRIM(album) != "" AND TRIM(artist) != "" GROUP BY SUBSTRING(artist,1,1)', true);
	while ($row = mysql_fetch_row($qres)) $hotsels .= $row[0];
	updatecache(10, $hotsels);
	return $hotsels;
}

function cache_updateall()
{
	updategenre();
	updatehotlist();
	updatestatistics();
}

function album_hotlist($type)
{
	global $PHP_SELF;
	$alf = '*0abcdefghijklmnopqrstuvwxyz';
	$chlist = $alfa = array();
	for ($i=0,$c=strlen($alf);$i<$c;$i++) $alfa[] = $alf[$i];

	$hotsels = '';
	if (!getcache(10, $hotsels)) $hotsels = updatehotlist();
		
	for ($i=0,$c=strlen($hotsels);$i<$c;$i++) if (is_numeric($hotsels[$i])) $chlist['0'] = true; else $chlist[$hotsels[$i]] = true;
	$out = '<table width="100%" cellpadding="0" cellspacing="1" border="0"><tr>';
	
	$pcnt = floor(100 / strlen($alf));

	for ($i=0,$c=strlen($alf);$i<$c;$i++)
	{
		$add = false;
		if ($i == 0)
		{
			foreach ($chlist as $tch => $val) if (!in_array($tch, $alfa)) $add = true; 
		} else
			if (isset($chlist[$alf[$i]])) $add = true;

		if ($add)
			$out .= '<td align="center" width="'.$pcnt.'%"><a href="'.$PHP_SELF.'?action=hotselect&amp;'.$type.'='.$alf[$i].'" class="hot">'.$alf[$i].'</a></td>'; 
		else 
			$out .= '<td align="center" width="'.$pcnt.'%"><font class="loginkplaylist">'.$alf[$i].'</font></td>';
	}
	$out .= '</tr></table>';
	return $out;
}

function updategenre()
{
	$res = db_execquery('SELECT genre,count(*) as cnt FROM '.TBL_SEARCH.' WHERE genre != 255 AND TRIM(album) != "" GROUP BY genre ORDER BY genre', true);
	$data = '';
	while ($row = mysql_fetch_array($res)) $data .= $row[0].'-'.$row[1].',';
	updatecache(20, $data);
	return $data;
}

function genre_select($top = true, $default)
{
	$glist = $glistid = $glistcnt = array();
	$genredata = '';
	if (!getcache(20, $genredata)) $genredata = updategenre();

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
				$glist[$cnt] = checkchs($gname);
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
		$this->id3 = db_guinfo('defaultid3');
		$this->orsearch = db_guinfo('orsearch');
		$this->hitsas = db_guinfo('hitsas');
		$this->where = db_guinfo('defaultsearch');
		$this->what = trim(sanstr('searchfor'));
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

		if ($this->hitsas == 1) $extra = ',COUNT(free) AS many, SUM(lengths) AS lengths'; else $extra = '';
		
		$query = 'SELECT *'.$extra.' FROM '.TBL_SEARCH.' WHERE f_stat = 0 AND '.$subquery;
		
		if ($this->hitsas == 1) $query .= ' AND LENGTH(rtrim(album)) > 0 GROUP BY RTRIM(album),RTRIM(artist)';
		
		$query .= ' ORDER BY dirname, free ASC';

		if ($from && $to) $query .= ' LIMIT '.$from.','.$to;
	
		return $this->query = $query;
	}

	function viewsearch()
	{
		global $cfg;

		$kqm = new kq_Measure();
		$kqm->start();
		if (!$this->rows) $result = db_execquery($this->query); else $result = db_execquery($this->query, true);
		$kqm->stop();

		if (!$this->rows) $this->rows = mysql_num_rows($result);
		$this->mwritten =0;

		$max = db_guinfo('searchrows');
		$extra = '';
		if ($this->rows > $max) $extra = get_lang(6, $max); 
		showdir('',get_lang(8, $this->what),0);
		echo '<font class="wtext"> - '.get_lang(9).' '.$this->rows.' '.$extra.' / '.$kqm->result(3).' '.get_lang(7).'</font>';
		echo '</td></tr>';
		$filter = 0;

		while ($row = mysql_fetch_array($result)) 
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
							
					case 1: $ainf = gen_aheader($row['album'], $row['artist'], $row['lengths'], $row['many']);		
							echo print_dir($row['drive'],date($cfg['dateformat'], $row['date']).' - '.$row['artist'].' - '.$row['album'], $f2->relativepath, -1, 'link.gif',$row['artist'],$ainf,$this->what, true, 0, $row['id']);
							break;
				}			
				$this->mwritten++;
			} else $filter++;
		}
		if ($this->rows==0) echo '<tr><td><font class="fdet">'.get_lang(10).'</font></td></tr>';
		if ($filter>0) echo '<tr><td><font class="fdet">'.get_lang(264,$filter).'</font></td></tr>';		
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
		
		$this->navid = fruser('navid', true);
		if (!$this->navid) $this->navid = $navid;

		$this->start = $start;
		$this->navrows = fruser('navrows', true);
		$this->navpos = fruser('navpos', true);
		
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
			case 5: $this->header = $this->headertext = get_lang(31, fruser('hchar')); break;
			case 6: $this->header = get_lang(147); break;
			case 7: $this->header = get_lang(121); $this->gui = false; break;
		}

		if ($this->navid == 2) $this->perpage = db_guinfo('searchrows'); else
		if ($this->navid == 7) $this->perpage = fruser('hperpage', true, 18); else
				$this->perpage = db_guinfo('hotrows');

		$this->searchfor = sanstr('searchfor');	
		
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
		global $PHP_SELF;
		if ($page == $mark) $class = 'filemarked'; else $class = 'hot';
		
		$extra = '';
		for ($i=0,$c=count($this->follow);$i<$c;$i++) $extra .= '&amp;'.$this->follow[$i][0].'='.$this->follow[$i][1];
		
		return '<a title="'.get_lang(278, $page).'" href="'.$PHP_SELF.'?action=gotopage&amp;page='.$page.'&amp;navrows='.$this->navrows.'&amp;searchfor='.urlencode(stripcslashes($this->searchfor)).'&amp;navid='.$this->navid.$extra.'" class="'.$class.'">'.$page.'</a>&nbsp;'; 
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
					$uh->setuid(vernum(fruser('huid')));
					$uh->setfilter(fruser('filter', true, -1));
					$uh->setperpage(fruser('hperpage', true, 18));
					$uh->show($this->navpos, $this->perpage);						
					
					$this->setfollow('huid', fruser('huid'));
					$this->setfollow('filter', fruser('filter', true, -1));
					$this->setfollow('hperpage', fruser('hperpage', true, 18));
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
					case 5: $this->setfollow('hchar', fruser('hchar'));
							$gl->hotselect(fruser('hchar'), $this->navpos, $this->perpage);
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
		if ($this->navrows > $this->perpage)
		{		
			echo '<tr><td height="10"></td></tr>';		
			echo '<tr><td>';
			if ($this->navpos + $this->perpage >= $this->navrows) $disright = ' disabled="disabled" style="color:#CCCCCC"'; else $disright = '';
			if ($this->navpos > 0) $disleft = ''; else $disleft = ' disabled="disabled" style="color:#CCCCCC"';		
			echo '<input type="hidden" name="navpos" value="'.$this->navpos.'"/>';
			echo '<input type="hidden" name="searchfor" value="'.$this->searchfor.'"/>';
			echo '<input type="hidden" name="navrows" value="'.$this->navrows.'"/>';
			echo '<input type="hidden" name="navid" value="'.$this->navid.'"/>';
			echo '&nbsp;<input type="submit" class="fatbuttom" name="searchnavigate_left" value="'.get_lang(276).'"'.$disleft.'/>&nbsp;';
			echo '<input type="submit" class="fatbuttom" name="searchnavigate_right" value="'.get_lang(277).'"'.$disright.'/>&nbsp;&nbsp;';		
				
			for ($i=0,$c=count($this->follow);$i<$c;$i++) 
			echo '<input type="hidden" name="'.$this->follow[$i][0].'" value="'.$this->follow[$i][1].'"/>';

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
		}
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

function search_qupdorins($id, $finf, $filein, $md5, $drive, $mtime, $f_stat, $fsize)
{
	if ($id > 0) $sql = 'UPDATE '; else $sql = 'INSERT INTO ';	
	
	$sql .= TBL_SEARCH.' SET title = "'.myescstr($finf['title']).'", album = "'.myescstr($finf['album']).'", artist = "'.myescstr($finf['artist']).'", md5 = "'.$md5.'", free = "'.myescstr(basename($filein)).'", genre = '. vernumset($finf['genre'],255).', lengths = '.$finf['lengths'].', ratemode = '.$finf['ratemode'].', bitrate = '.(int)$finf['bitrate'].', drive = '.$drive.', mtime = '.$mtime.', dirname = "'.myescstr(getrelative($filein)).'", f_stat = '.$f_stat.', fsize = '.$fsize.', track = '.$finf['track'].', `year` = '.$finf['year'].', comment = "'.myescstr($finf['comment']).'", ftypeid = '.$finf['ftypeid'];
	if ($id > 0) $sql .= ' WHERE id = '.$id; else $sql .= ', `date` = '.time();
	return $sql;
}

function search_qupdfree($free, $drive, $id)
{
	return 'UPDATE '.TBL_SEARCH.' SET free = "'.myescstr(basename($free)).'", dirname = "'.myescstr(getrelative($free)).'", drive = '.$drive.' WHERE id = '.$id;
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
			$sfree = myescstr(basename($freestrip));
			$sdirname = myescstr(getrelative($freestrip));

			$res = db_execquery('SELECT id FROM '.TBL_SEARCH.' WHERE free = "'.$sfree.'" AND dirname = "'.$sdirname.'"');
			
			if ($res && mysql_num_rows($res) == 1) 
			{
				$row = mysql_fetch_row($res);
				$id = $row[0];
			}
		}			
		$query = search_qupdorins($id, $fid, $freestrip, md5file($free), $drive, filemtime($free), 0, filesize($free));
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
	global $PHP_SELF, $setctl, $win32;
	kprintheader(get_lang(11), 1);
	?>
	<form name="updateoptions" method="post" action="<?php echo $PHP_SELF; ?>">
	<input type="hidden" name="action" value="performupdate"/>
	<table width="400" border="0" cellspacing="1" cellpadding="1">
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

function search_updatelist($options="")
{
	global $base_dir, $win32, $setctl;
	kprintheader(get_lang(17), 1);
	
	$updateall = false;

	if (isset($options['deleteunused'])) $deleteunused = 1; else $deleteunused = 0;
	if (isset($options['debugmode'])) $debugmode = 1; else $debugmode = 0;
	if (isset($options['sleeppertrans'])) $sleeptrans = $options['sleeppertrans']; else $sleeptrans = 0;
	if (isset($options['rebuildid3'])) $updateall = true;
	
	$db_out = $db_mtime = $db_unique = $db_path = array();	

	$filecntr = 0;
	$file = '';

	$fixurl = 'http://www.kplaylist.net/forum/viewtopic.php?p=3672';

	echo '<font class="notice">'.get_lang(296, '<a href="'.$fixurl.'" target="_blank">'.$fixurl.'</a>').'</font><br/><br/>';
	echo '<font class="notice">'.get_lang(136).'..</font><br/>';
	flush();

	$data = array();
	$basedirlen = array();
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

	for ($i=0,$c=count($base_dir);$i<$c;$i++)
	{
		$grabdata = array();
		if ($debugmode) echo '<!-- update debug step 1 - grabbing filelist from '.$base_dir[$i].' -->';
		GetDirArray($base_dir[$i], $grabdata, $cnt);
		$basedirlen[$i] = strlen($base_dir[$i]);
		$data[$i] = $grabdata;
		$datacnt += count($data[$i]);
	}

	if ($debugmode) echo '<!-- update debug step 2 -->';
	
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
				
		updateup_status(get_lang(314, $dcntr, $dbrows));
	
		while ($row = mysql_fetch_row($result)) 
		{			
			$db_out[$dcntr++] = $row;

			if ($dcntr % 50 == 0) updateup_status(get_lang(314, $dcntr, $dbrows));

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
		updateup_status(get_lang(314, $dcntr, $dbrows), 'up_status');

		db_free($result);

		if ($debugmode) echo '<!-- update debug step 3 -->';

		if ($updateall) $db_mtime = array();
		
		$query = null;
				
		updateup_status(get_lang(18, $datacnt));

		$totalqupds = $dcntr;	
		$totalins = $datacnt;
		$qins = $qupd = $failed = $qupdins = $skips= $qdels = $fdups = 0;

		if ($datacnt > 0)
		{
			for ($drive=0,$drivec=count($data);$drive<$drivec;$drive++)
			{
				for ($i=0,$ic=count($data[$drive]);$i<$ic;$i++)
				{
					$file = $data[$drive][$i];
					$filein = substr($file, $basedirlen[$drive]);

					if ($i % 50 == 0 || $debugmode)
					{
						$countups = $qupd + $qupdins;
						$out = get_lang(20,$qins,$countups);
						$out .= (strlen($filein) > 60) ? addslashes(substr($filein,0,60)).'...' : addslashes($filein);
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
								$checkf = $base_dir[$db_out[$idupdate][4]]. $db_out[$idupdate][6].$db_out[$idupdate][3];
																							
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

							if ($updateall) $db_out[$idupdate][5] = 0;
	
							if ($mtime != $db_out[$idupdate][5])
							{
								$fid = get_file_info($file);								
								$query = search_qupdorins($db_out[$idupdate][1], $fid, $filein, $md5, $drive, $mtime, 0, $fsize);
								$qupdins++;
							}
							else
							if ($db_out[$idupdate][6].$db_out[$idupdate][3] != $filein || $db_out[$idupdate][4] != $drive)
							{
								$query = search_qupdfree($filein, $drive, $db_out[$idupdate][1]);
								$qupdins++;
							}							
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
								$query = search_qupdorins(0, $fid, $filein, $md5, $drive, $mtime, 0, $fsize);	
								$db_out[$dcntr++] = array(-1, 0, $md5, $filein, $drive, $mtime, $filein);
								$db_unique[$fsize][$md5] = $dcntr-1;
								$qins++;
							} else
							{
								$query = search_qupdorins($db_out[$useid][1], $fid, $filein, $md5, $drive, $mtime, 0, $fsize);
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
					$fordel = 0;
				}
				echo '<br/>';
			}
		} else
		{
			$one = false;
			for ($i2=0;$i2<$dcntr;$i2++)
			if ($db_out[$i2][0] != -1) { $one = true; echo '<font class="notice">'.get_lang(315, $db_out[$i2][6].$db_out[$i2][3]).'</font><br/>'; }
			if ($one) echo '<br/>';
		}
		$kqm->stop();
		updateup_status(get_lang(26), 'up_status');
		echo '<font class="notice">'.get_lang(25, $qins, $qupdins, $qdels, $failed, $skips, $filecntr, $kqm->result(3), $fordel);
		echo '</font><br/><br/>';
		echo '<input type="button" value="'.get_lang(27).'" name="close" class="fatbuttom" onclick="javascript: self.close();"/><br/><br/>';
	} 
	else
	{
		$prbasedir='';
		for ($i=0;$i<count($base_dir);$i++)	$prbasedir .= $base_dir[$i];
		echo '<br/><font class="notice">'.get_lang(28, $prbasedir).'</font><br/>';
	}
	$setctl->set('basedir_changed', 0);
	cache_updateall();
	kprintend();	
}

function search_updateautomatic($user, $host, $waittrans=0)
{
	global $cfg, $setctl;
	$setctl->publish('followsymlinks');

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


function retornaEndImgCapa($artist, $album)
{
	global $setctl;
	
	$strEndereco = parseurl($setctl->get('albumurl'), '', $artist, $album);
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
		} else $contents = '';
		
		if (!is_numeric(strpos($contents,'.jpg')) & !is_numeric(strpos($contents,'.gif'))) return false;
			else 
		return $contents;
	}
	return false;
}

function imgsend($f2, $fdesc, $data = false, $headers=true)
{
	if ($data)
	{
		$fp = fopen($f2->fullpath, 'rb');
		if (!$fp) return false;
	}
	
	if ($headers)
	{
		header('Content-Disposition: inline; filename="'.$f2->fname.'"'); 
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

function createimg($sid, $headers=true, $w=0, $h=0)
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
			if (imgcoords($w,$h,$f2->fullpath, $nw, $nh) && function_exists('imagecreatetruecolor') && function_exists('imagecopyresampled'))
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
										imagegif($image_p);
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
										imagepng($image_p);
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
										imagejpeg($image_p, null, $cfg['jpeg-quality']);
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

function imgcoords($width=0, $height=0, $srcfp, &$nw, &$nh)
{
	global $setctl;
	$resize = false;
	if (!$width) $wm = $setctl->get('albumwidth'); else $wm = $width;
	if (!$height) $hm = $setctl->get('albumheight');  else $hm = $height;

	if ($imagesize = @getimagesize($srcfp))
	{
		$w = $imagesize[0]; 
		$h = $imagesize[1]; 
		
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

		if ($nw != $w || $nh != $h) $resize = true;

	} else
	{		
		$nw = $wm;
		$nh = $hm;
	}
	return $resize;
}

function albumshow($rows, &$url, $patht=0, $width=0, $height=0)
{
	global $setctl;
	$names = array();
	for ($i=0,$c=count($rows);$i<$c;$i++)
	{
		$f2 = new file2($rows[$i], false);
		$names[] = array($f2->fname, $f2->weblink(0,0,'imgsid'), $f2->fullpath, $f2->weblink(), $rows[$i]);
	}

	if (!$width) $wm = $setctl->get('albumwidth'); else $wm = $width;
	if (!$height) $hm = $setctl->get('albumheight');  else $hm = $height;

	$albumfiles = explode(',', strtoupper($setctl->get('albumfiles')));
	
	for ($i=0,$c=count($albumfiles);$i<$c;$i++)
	{		
		$amatch = trim($albumfiles[$i]);
		if (empty($amatch)) continue;
		for ($i2=0,$c2=count($names);$i2<$c2;$i2++)
		{
			if (fmatch(strtoupper($names[$i2][0]), $amatch))
			{
				if ($patht > 0) 
				{
					$url = $names[$i2][$patht];
					return true;
				} else
				{
					if ($setctl->get('albumresize')) 
					{ 
						$nw = $nh = 0;
						imgcoords(0,0,$names[$i2][2], $nw, $nh);
						$url = '<a href="'.$names[$i2][3].'"><img border="0" src="'.$names[$i2][1].'" alt="album" width="'.$nw.'" height="'.$nh.'"/></a>'; 
					} else $url = '<a href="'.$names[$i2][3].'"><img alt="album" src="'.$names[$i2][1].'"/></a>';				
				}
				return true;
			}
		}
	}
	return false;
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
	
		$this->_version = 1.24; // Version of the id3 class
		$this->file = false;		// mp3/mpeg file name
		$this->id3v1 = false;		// ID3 v1 tag found? (also true if v1.1 found)
		$this->id3v11 = false;	// ID3 v1.1 tag found?
		$this->id3v2 = false;		// ID3 v2 tag found? (not used yet)
    // ID3v1.1 Fields:
		$this->name = '';		// track name
		$this->artists = '';		// artists
		$this->album = '';		// album
		$this->year = '';		// year
		$this->comment = '';		// comment
		$this->track = 0;		// track number
		$this->genre = '';		// genre name
		$this->genreno = 255;		// genre number
    // MP3 Frame Stuff
		$this->studied = false;	// Was the file studied to learn more info?
		$this->mpeg_ver = false;	// version of mpeg
		$this->layer = false;		// version of layer
		$this->bitrate = false;	// bitrate
		$this->crc = false;		// Frames are crc protected?
		$this->frequency = 0;		// Frequency
		$this->padding = false;	// Frames padded
		$this->private = false;	// Private bit set?
		$this->mode = '';		// Mode (Stereo etc)
		$this->copyright = false;	// Copyrighted?
		$this->original = false;	// On Original Media? (never used)
		$this->emphasis = '';		// Emphasis (also never used)
		$this->filesize = -1;		// Bytes in file
		$this->frameoffset = -1;	// Byte at which the first mpeg header was found.
		$this->length = false;	// length of mp3 format hh:ss
		$this->lengths = false;	// length of mp3 in seconds
		$this->error = false;		// if any errors they will be here
		$this->debug = false;		// print debugging info?
		$this->debugbeg = '<DIV STYLE="margin: 0.5 em; padding: 0.5 em; border-width: thin; border-color: black; border-style: solid">';
		$this->debugend = '</DIV>';
	
	
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
	    $this->error = 'Unable to see to end - 128 of ' . $this->file;
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

function gengenres($id=255)
{
	global $genresid3;

	if ($id != 255)
	{
		if (isset($genresid3[$id])) return $genresid3[$id];
	} else return $genresid3;
}

function findmusic()
{
	global $PHP_SELF, $win32, $setctl;
	kprintheader(get_lang(289),0);
	
	if (isset($_POST['paths'])) $paths = str_replace("\r\n", "\n", $_POST['paths']); else $paths = '';

	echo '<div id="up_status2" class="notice"></div>';
	$data = array();

	if (isset($_POST['useselected']))
	{
		$nbasedir = '';
		foreach($_POST['selected'] as $name => $val) $nbasedir .= $val.';';		
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
	<form action="<?php echo $PHP_SELF; ?>" method="post">			
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
		while ($file = readdir($handle)) $flist[] = $file;
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

function GetDirArray($spath, &$data, &$cnt)
{
	$flist = array();
	$flistcnt = 0;
	if (@$handle = opendir($spath))
	{
		while ($file = readdir($handle)) $flist[$flistcnt++] = $file;
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
						GetDirArray($spath.$val.'/', $data, $cnt);
					} else 
					if (file_type($val) != -1) 
					{
						$data[] = $spath.$val;
						$cnt++;
					}
				}
			}
		}
	}
}

class kparchiver
{
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

		$usearc = 0;
		$preferarc = db_guinfo('archer');
		if (isset($archivers[$preferarc]) && $archivers[$preferarc][0] == 1) $usearc = db_guinfo('archer');
		$tf = tempnam($cfg['archivetemp'], 'kppack');
		$f = $tf . '.'.$archivers[$usearc][1];

		if (strpos($archivers[$usearc][2],'%LIST') !== false) $listmode = true;
		
		$cwd = getcwd();
	
		$f2 = false;
		$files = 0;
		$sizemb = 0;
		for ($i=0,$c=count($this->files);$i<$c;$i++)
		{
			$f2 = new file2($this->files[$i]);
			if ($f2->ifexists()) 
			{
				$sizemb += $f2->fsize;
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

		$sizemb = ceil($sizemb / 1048576);
		if ($sizemb > db_guinfo('archivesize') && db_guinfo('archivesize') > 0) 
		{
			echo '<font class="notice">'.get_lang(328, $sizemb, db_guinfo('archivesize')).'</font>'; 
			kprintend();
			die();
		}

		echo '<div id="up_status2" class="notice">0%</div><br/>';
		flush();

		if ($listmode)
		{
			if ($f2)
			{
				$run = getarchiveline($usearc, $f, $f2->getfullpath($win32), $tf);
				if ($cfg['archivemodedebug']) echo($run); else exec($run);
				updateup_status('100%');
			}
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
					updateup_status($per. ' .. '.$f2->fname);
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
				<form style="margin:0;padding:0" action="<?php echo $PHP_SELF; ?>" method="post">			
				<input type="hidden" name="action" value="downloadarchive"/>
				<input type="hidden" name="file" value="<?php echo basename($f); ?>"/>
				<input type="hidden" name="mime" value="<?php echo $archivers[$usearc][3]; ?>"/>
				<table width="95%" cellpadding="0" cellspacing="0" border="0" align="center">
				<tr>
					<td class="notice"><?php echo get_lang(65); ?></td>
					<td><input type="text" class="fatbuttom" name="filename" value="<?php echo 'kpdl'.date('hi').'.'.$archivers[$usearc][1]; ?>"/></td>
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
		} else echo '<font class="notice">'.get_lang(167).'</font>'; 
		@unlink($tf);
		kprintend();
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
						$tagwriter->overwrite_tags = false;
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
										
						$res = fsearch($f2->relativepath, false, $f2->drive,'id, free', false, true);
						$rows = array();
						while ($row = mysql_fetch_row($res)) $rows[] = $row[0];	
						if (albumshow($rows, $url, 2))
						{
							$fp = fopen($url, 'rb');
							if ($fp)
							{
								$asid = 0;								
								if (albumshow($rows, $asid, 4) && $cfg['id3v2albumresize'])
								{
									ob_start();
									createimg($asid, false);
									$imgdata = ob_get_contents();
									ob_end_clean();
								} else $imgdata = fread($fp, filesize($url));
								fclose($fp);
								
								if ($cfg['maxtagimagesize'] == 0 || strlen($imgdata) <= $cfg['maxtagimagesize'])
								{
									$fdesc = new filedesc($url);
									$TagData['attached_picture'][0]['data'] = $imgdata;
									$TagData['attached_picture'][0]['picturetypeid'] = 3;
									$TagData['attached_picture'][0]['encodingid'] = 0;
									$TagData['attached_picture'][0]['description'] = 'ART';
									$TagData['attached_picture'][0]['mime'] = $fdesc->mime;
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

function httpstreamheader2($ftype=1, $sid, $keyint=0)
{
	global $phpenv, $streamtypes, $setctl, $u_cookieid, $cfg;
	$url = '';
	if (isset($streamtypes[$ftype]) && $streamtypes[$ftype][2] == 1)
	{
		$url = $setctl->get('streamurl').$phpenv['streamlocation'].'?streamsid='.$sid.'&c='.$u_cookieid;
		if (URLSECURITY) $url .= '&'.urlsecurity($keyint, $sid);
		if ($setctl->get('sendfileextension')) $url .= '&file=.'.$streamtypes[$ftype][0]; 		
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
				$url = httpstreamheader2($fd->fid, $sid, $f2->fdate);
				if (!empty($url))
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
		$pltype = db_guinfo('pltype');
		switch($pltype)
		{
			case 4: if (class_exists('m3ugendisk')) $this->obj = new m3ugendisk(); else $this->obj = new m3ugen(); break;
			case 3: if (class_exists('kpwimpygen')) $this->obj = new kpwimpygen(); else $this->obj = new m3ugen(); break;
			case 2: $this->obj = new asxgen(); break;
			default: $this->obj = new m3ugen(); break;
		}		

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
				$this->setdata(httpstreamheader2($fd->fid, $sid, $f2->fdate));
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

function streamfp($fp, $kbit, $prebuffer=true, $hid = 0, $fsize=0)
{
	global $streamsettings;
	@ini_set('output_buffering', 0);
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

	$breadbuf = ceil(($bread / 100) * (int)$streamsettings['buffer']);
	$precision = (int)$streamsettings['precision'];

	$kqm->start();
	while (!feof($fp) && !connection_aborted())
	{
		$data = '';
		while (strlen($data) < $breadbuf && !feof($fp)) $data .= fread($fp, $breadbuf-strlen($data));
		echo $data;
		$rpos += strlen($data);
		pollhid($hid, $rpos, $fsize);
		flush();
		while (!$kqm->alarm()) usleep($precision);
		$kqm->start();
	}
	pollhid($hid, $rpos, $fsize, true);
}

function getlame($bitrate=128,$file)
{
	global $cfg;
	$out = str_replace('%bitrate%', $bitrate, $cfg['lamecmd']);
	$out = str_replace('%file%', $file, $out);
	return $out;
}

function Kplay_senduser2($sid, $inline=0, $download=false, $tid = 0)
{
	global $win32, $_SERVER, $setctl, $streamsettings, $u_id, $lamebitrates, $dlrate, $cfg;
	ignore_user_abort(true);
	$hid = 0;
	$id3v2tag = '';
	$f2 = new file2($sid, true);
	
	if ($f2->ifexists())
	{
		$fp = fopen($f2->fullpath, "rb");
		if ($fp)
		{
			$fdesc = new filedesc($f2->fname);
			if (!$download && $u_id && db_guinfo('lameperm') && db_guinfo('lamerate') != 0 && $setctl->get('lamesupport') && $fdesc->gid == 1) 
				$uselame = true; else $uselame = false;
			
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
					if ($u_id && $fdesc->logaccess)  $hid = addhistory($u_id, $sid, $tid);
				} else $hid = getlasthistory($sid, $u_id, true);
			}

			if ($posfrom > 0) $hid = getlasthistory($sid, $u_id, true);

			if ($hid) updateactive($hid);
			
			$clen = $f2->fsize;
			$offsetfp = 0;
			
			if ($setctl->get('writeid3v2') && $fdesc->gid == 1 && !$download)
			{
				$id = fread($fp, 3);
				fseek($fp, 0, SEEK_SET);
				if ($id == 'ID3') 
				{
					if ($cfg['enablegetid3'] && GETID3_V == 17) // don't rewrite id3 unless we have getid3 1.7.x
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
								fseek($fp, $offsetfp, SEEK_SET);
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
				 else header('Content-Disposition: attachment; filename='.$f2->gentitle()); 
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
				header('HTTP/1.1 206 Partial Content');
				if ($posfrom == ($f2->fsize - 129) || $posfrom == ($f2->fsize - 128)) // request id3v1
				{
					fseek($fp, -128, SEEK_END);
					echo fread($fp, 128);
					rewind($fp);
				} else fseek($fp, $offsetfp + $posfrom, SEEK_SET);
			}

			if ($sendclen)
			{
				header('Accept-Ranges: bytes');
				header('Content-Length: '.$clen);
			}
			
			// finally STREAM  - no more headers allowed.

			if (!empty($id3v2tag) && $posfrom == 0) echo $id3v2tag; 

			if ($download)
			{
				$upc = 0;
				if (db_guinfo('udlrate')) $udlrate = db_guinfo('udlrate');
					else
				if ($dlrate) $udlrate = $dlrate;
					else
				$udlrate = 0;
				
				if ($udlrate && !$win32) streamfp($fp, $udlrate, false, $hid, $f2->fsize);
					else
				while (!feof($fp) && !connection_aborted()) 
				{	
					$dt = fread($fp, 16384);
					echo $dt;
					$upc += strlen($dt);
					pollhid($hid, $upc, $f2->fsize);
				}
				pollhid($hid, $upc, $f2->fsize, true);
			} else
			{			
				if ($uselame)
				{
					$descriptorspec = array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w'));
					$process = proc_open(getlame($lamebitrates[db_guinfo('lamerate')], $f2->fullpath), $descriptorspec, $pipes);
					if (is_resource($process))
					{
						//if (function_exists('stream_set_blocking')) stream_set_blocking($pipes[1], 0);
						if ($setctl->get('streamingengine') && !$win32 && db_guinfo('streamengine')) streamfp($pipes[1], $lamebitrates[db_guinfo('lamerate')]);
							else while (!feof($pipes[1]) && !connection_aborted()) echo fgets($pipes[1], 1024);
						fclose($pipes[0]);
						fclose($pipes[1]);
						proc_close($process);
					}
				} else
				{
					if ($setctl->get('streamingengine') && !$win32 && db_guinfo('streamengine'))
					{
							if (@$streamsettings['forcedefaultrate']) 
						streamfp($fp, $streamsettings['defaultrate'], true, $hid, $f2->fsize);
							else
							if (in_array ($f2->id3['bitrate'], $streamsettings['bitrates']) && $f2->id3['ratemode'] == 1)  // cbr
						streamfp($fp, $f2->id3['bitrate'], true, $hid, $f2->fsize);
							else
							{
								$rate = (int) $f2->id3['bitrate'] + ceil(($f2->id3['bitrate'] / 100) * 5);
								if ($rate < $streamsettings['defaultrate']) $rate = $streamsettings['defaultrate'];
								streamfp($fp, $rate, true, $hid, $f2->fsize);
							}
					} else 
					{	
						$upc = 0;
						while (!feof($fp) && !connection_aborted()) 
						{
							$dt = fread($fp, 16384);
							echo $dt;
							$upc += strlen($dt);
							pollhid($hid, $upc, $f2->fsize);
						}
						pollhid($hid, $upc, $f2->fsize, true);
					}
				}
			}
			@fclose($fp);
		}
	}
	flush();
	die();
}

function pollhid($hid, &$pos, $fsize, $end = false)
{
	if ($hid != 0)
	{
		if ($pos > (int)($fsize / 100) || $end)
		{
			updatehistory($hid, $pos);
			$pos = 0;
		}
	}
}

function kplay_archivedownload($file, $mime, $name)
{
	global $dlrate, $win32, $u_id;
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
		if ($dlrate) $udlrate = $dlrate;
			else
		$udlrate = 0;

		if ($udlrate && !$win32) streamfp($fp, $udlrate, false);
			else
		while (!feof($fp) && !connection_aborted()) echo fread($fp, 32768); 
		@fclose($fp);
	}
}

function print_dir($drive, $name, $pdir, $nr, $image='dir.gif',$title='', $ainf=null, $mark='', $showalbum = false, $hits = 0, $albumid=0)
{
	global $PHP_SELF;
	$extraref = '';
	/*if ($showalbum)
	{
		$res = fsearch($pdir, false, $drive,'id, free');
		while ($row = mysql_fetch_row($res)) $rows[] = $row[0];
		$url = '';
		if (albumshow($rows, $url, 1)) 
		{
			echo '<tr><td><img width="85" height="85" src="'.$url.'&amp;h=85&amo;w=85"/></td></tr>';
		}
	}*/

	$cname = checkchs($name); 

	if (!empty($pdir)) $pdir_64 = urlencode(base64_encode($pdir)); else $pdir_64='';
	$out = '<tr><td height="19">';
	if ($showalbum)
	{
		$out .= '<a href="'.$PHP_SELF.'?n='.$nr.'&amp;p='.$pdir_64.'&amp;d='.$drive.'&amp;ftid='.$albumid.'&amp;action=playalbum" class="dir">';
		$out .= '<img alt="'.get_lang(42).'" src="'.getimagelink('album.gif').'" border="0"/>&nbsp;';
		$out .= '</a>';
	}
	if ($nr != -1) $md = md5($name); else $md = '';
	if ($albumid > 0 && isset($ainf['index']) && $ainf['index'] == 1) $extraref = '&amp;marksid='.$albumid; else if (!empty($mark)) $extraref = '&amp;mark='.urlencode($mark);
	$out .= '<a href="'.$PHP_SELF.'?n='.$nr.'&amp;n2='.$md.'&amp;p='.$pdir_64.'&amp;d='.$drive.$extraref.'" class="dir">';
	$out .= '<img alt="'.$cname.'" src="'.getimagelink($image).'" border="0"';

	if (!empty($title)) $out .= ' title="'.get_lang(116, checkchs($pdir)).'"';
	$out .= '/>&nbsp;';
	$out .= strlen($cname) > db_guinfo('textcut') ? substr($cname, 0, db_guinfo('textcut')).' ..' : $cname; 
	$out .= '</a>';
	if ($ainf) $out .= ' <span class="finfo">&nbsp;('.get_lang(151, $ainf['length'], $ainf['index']).')</span>';
	if ($hits > 0) $out .= ' <span class="fdet">&nbsp;('.$hits.' '.get_lang(243).')</span>';
	$out .= '</td></tr>';
	return $out;
}

class filedesc
{
	function filedesc($fname)
	{
		global $streamtypes;
		$this->fid = file_type($fname);

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

function gen_file_header($title = '', $artist = '', $album = '', $bitrate = 0, $lengths = 0, $genre = 255, $ratemode = 1, $track = 0, $year = 0, $comment = '', $ftypeid=0)
{
	$ret = array('title' => $title, 'artist' => $artist, 'album' => $album, 'length' => '00:00', 'bitrate' => $bitrate, 'lengths' => $lengths, 'genre' => $genre, 'tag' => false, 'ratemode' => $ratemode, 'track' => $track, 'year' => $year, 'comment' => $comment, 'ftypeid' => $ftypeid);
	if ($lengths > 0) $ret['length'] = sprintf('%02d:%02d', floor($lengths/60), $lengths % 60);
	return $ret;
}

function gen_file_info_sid($row)
{
	if ($row) return gen_file_header($row['title'], $row['artist'], $row['album'], $row['bitrate'], $row['lengths'], $row['genre'], $row['ratemode'], $row['track'], $row['year'], $row['comment'], $row['ftypeid']);
	return false;
}

function get_searchrow($sid)
{
	return @mysql_fetch_array(db_execquery('SELECT * FROM '.TBL_SEARCH.' WHERE id = '.$sid, true));
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
	if (tunstig('year', $arrayi)) $ret['year'] = unstig($arrayi['year']);
	if (tunstig('genreid', $arrayi)) $ret['genre'] = unstig($arrayi['genreid']);
	if (tunstig('comment', $arrayi)) $ret['comment'] = unstig($arrayi['comment']);
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

$arrgenrename = array();

function getgenreidfromName($name)
{
	global $arrgenrename;
	if (count($arrgenrename) == 0)
	{
		$genrelist = gengenres();
		foreach ($genrelist as $id => $gname) $arrgenrename[strtolower($gname)] = $id;		
	}
	if (isset($arrgenrename[strtolower($name)])) return $arrgenrename[strtolower($name)];
	return 255;
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
		if (GETID3_V == 17)
		{		
			$getID3 = new getID3();
			$finfo = $getID3->analyze($name);
	
			$ret['length'] = isset($finfo['playtime_string']) ? $finfo['playtime_string'] : '00:00';
			$ret['lengths'] = isset($finfo['playtime_seconds']) ? round($finfo['playtime_seconds']) : 0;
			isset($finfo['audio']['bitrate']) ? $ret['bitrate'] = round($finfo['audio']['bitrate']) / 1000 : 0;
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
					if (isset($finfo['tags'][$use]['genre'])) $ret['genre'] = getgenreidfromName($finfo['tags'][$use]['genre'][0]);					
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
					case 'f': $add = checkchs($f2->fname); break;
					case 'a': $add = checkchs($f2->id3['artist']); break;
					case 'l': $add = checkchs($f2->id3['album']); break;
					case 't': $add = checkchs($f2->id3['title']); break;
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

function urlsecurity($fdate, $sid)
{
	return 'stag='.urlencode(base64_encode(pack('ll', time(), $fdate+$sid)));
}

function chksecurity($sid=0)
{	
	global $cfg;
	if (URLSECURITY)
	{
		$ok = false;
		if (isset($_GET['stag']))
		{
			$datat = @unpack('l2', @base64_decode($_GET['stag']));
			if (isset($datat[1]) && is_numeric($datat[1]) && isset($datat[2]) && is_numeric($datat[2]))
			{
				if ($sid)
				{
					$f2 = new file2($sid);
					if ($f2->fexists)
					{
						if ($datat[2] == ($f2->fdate + $sid))
						{
							if (($datat[1] + $cfg['urlsecurityvalidtime']) >= time() || $cfg['urlsecurityvalidtime'] == 0) $ok = true;
						}
					}
				}
			}
		}
		return $ok;
	} else return true;
}

function parseurl($url, $title = '', $artist = '', $album = '')
{
	$urlr = str_replace('%title', urlencode($title), $url);
	$urlr = str_replace('%artist', urlencode($artist), $urlr);
	$urlr = str_replace('%album', urlencode($album), $urlr);
	return $urlr;
}

function print_file($sid, $showlink=0, $includeabsolute=0, $f2=false, $smarksid = -1)
{
	global $PHP_SELF, $u_cookieid, $setctl, $cfg, $marksid;
	
	if (!$f2) $f2 = new file2($sid, true);
	$inf = $f2->getid3();
	$title = $f2->gentitle(array('title', 'album'));	

	echo '<tr><td align="left"><input type="checkbox" style="padding-left:4px" name="selected[]" value="'.$sid.'"/> ';

	if ($cfg['id3editor'] && db_guinfo('u_access') == 0 && function_exists('file_id3editor'))
	{
		$id3link = '&amp;id3sid='.$sid;
		echo '<a href="javascript: void(0);" onclick="'.jswin('id3editor', '?action=id3edit'.$id3link).'">id3</a>&nbsp;';
	}

	if (ALLOWDOWNLOAD && db_guinfo('u_allowdownload'))
	{
		if (URLSECURITY) $urlextra = '&amp;'.urlsecurity($f2->fdate, $sid); else $urlextra = '';
		echo '<span class="file"><a href="'. $PHP_SELF. "?downloadfile=".$sid.'&amp;c='.$u_cookieid.$urlextra.'">'.
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

	if ($showlink) echo '<a href="'.$PHP_SELF.'?p='.$f2->getdir64().'&amp;d='.$f2->drive.'&amp;marksid='.$smarksid.'" title="'.get_lang(116, checkchs($f2->relativepath)).'">'.'<img src="'.getimagelink('link.gif').'" alt="'.get_lang(116, checkchs($f2->relativepath)).'" border="0"/></a>&nbsp;';
	
	if (ismarked($f2->fname.$title) || $f2->sid == $marksid) $useclass = 'filemarked'; else $useclass = 'file';
	
	echo file_parse($f2, $f2->weblink(), $useclass);
	echo '</td></tr>';
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
		$dbres = db_execquery('SELECT free,fsize,mtime FROM '.TBL_SEARCH.' WHERE f_stat = 0 AND dirname = "'.myescstr($dir).'" AND drive = '.$drive.' ORDER BY free ASC', true);
			else
		$dbres = db_execquery('SELECT free,fsize,mtime FROM '.TBL_SEARCH.' WHERE f_stat = 0 AND dirname = "" ORDER BY free ASC', true);

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
		cache_updateall();
	} else
	{
		$changes = false;
		for ($i=0;$i<$c;$i++) for ($i2=0;$i2<$c2;$i2++) if ($dblist[$i][0] == $flist[$i2][0] && $dblist[$i][1] == $flist[$i2][2] && $dblist[$i][2] == $flist[$i2][3]) $flist[$i2][0] = '';
		for ($i2=0;$i2<$c2;$i2++) if (!empty($flist[$i2][0])) 
		{
			$changes = true;
			updatesingle($base_dir[$flist[$i2][1]].$dir.$flist[$i2][0]);
		}
		if ($changes) cache_updateall();
		if ($changes && !$stop) disksync($dir, $drive, $root, true);
	}
}

function checkstructure($checkdir)
{
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
	global $PHP_SELF;
	?>
	<tr>
		<td>
			<table width="90%" bgcolor="#BBCCCC" cellpadding="8" cellspacing="8" border="1">
			<tr>
				<td class="importnant"><h3>Welcome to kPlaylist!</h3>				
				To get your site quickly up:
				
				<br/><br/>Click Settings on the admin menu, choose 'File handling' and enter the path to your music directory or directories in the 'base directory' field. You can also click the <a class="importnantlink" href="#" onclick="javascript: newwinscroll('find', '<?php echo $PHP_SELF; ?>?action=findmusic', 450, 600);">find</a> button to automatically detect music directories. Press F5 when finished.<br/><br/>
				
				If you have problems, click <a class="importnantlink" href="http://kplaylist.net/index.php?install=true" target="_blank">here</a> for the kPlaylist installation manual.
				<br/><br/>
				</td>
			</tr>
			</table>
		</td>
	</tr>
	<?php
}

function basedirchanged()
{
	?>
	<tr>
		<td>
			<table width="90%" bgcolor="#BBCCCC" cellpadding="8" cellspacing="8" border="1">
			<tr>
				<td class="importnant"><h3>Base directory changed</h3>
				The base dir setting was changed. Please click the 'Update' button on the admin menu to perform
				an update against the music sources.
				<br/><br/>
				Reload this page when done. (F5)
				<br/><br/>
				</td>
			</tr>
			</table>
		</td>
	</tr>
	<?php
}

function fsearch($dir, $root = false, $drive = 0, $r='id', $recurse = false, $fast=false)
{
	$order = 'dirname,free';
	if (ORDERBYTRACK) $order = 'track,dirname,free';

	if ($recurse)
		$dir_oper = 'dirname like "'.myescstr($dir).'%"';
	else
		$dir_oper = 'dirname = "'.myescstr($dir).'"';
	if (!$root)
		$sql = 'SELECT '.$r.' FROM '.TBL_SEARCH.' WHERE f_stat = 0 AND '.$dir_oper.' AND drive = '.$drive.' ORDER BY '.$order.' ASC';
	else
		$sql = 'SELECT '.$r.' FROM '.TBL_SEARCH.' WHERE f_stat = 0 AND dirname = "" ORDER BY '.$order.' ASC';
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

function printdirhtml($start=true)
{
	if ($start)
	{
		?>
			<tr><td>
				<table width="100%" border="0" cellspacing="0" cellpadding="0">
					<tr><td width="3"></td><td>
						<table width="100%" border="0" cellspacing="0" cellpadding="0">
						
		<?php
	} else
	{
		?>
		<tr><td height="0"></td></tr>
		</table></td></tr></table></td></tr>
		<?php
	}
	
}

function listroot(&$fcnt, &$dcnt)
{
	global $base_dir, $dir_list, $cfg, $setctl;
	
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
	
	printdirhtml();
	
	if (db_guinfo('dircolumn') > 1) listdir($drivelist,$sortlist, '', $nrlist);
			else for ($i=0;$i<$sortcnt;$i++) echo print_dir($drivelist[$i],$sortlist[$i], '', $nrlist[$i]);	
	
	printdirhtml(false);

	if (DISKSYNC) disksync('', 0, true);

	$res = fsearch('/', true,0,'id, free', false, false);
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
}

function listdir($drivelist ,$sortlist, $pdir='', $nrlist)
{
	$cols = db_guinfo('dircolumn');
	$colwidth = floor(100 / $cols);

	if (count($sortlist) > 0)
	{
		echo '<tr>';
		echo '<td>';
		echo '<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr>';
		for ($i=0,$c=count($sortlist);$i<$c;$i++) 
		{
			if ($i % $cols == 0 && $i > 0) echo '</tr><tr>';
			
			echo '<td width="'.$colwidth.'%"><table width="100%" border="0" cellspacing="0" cellpadding="0" align="left">';
			if (!is_array($drivelist)) $p1 = $drivelist; else $p1 = $drivelist[$i];
			if (!is_array($nrlist)) $p4 = $i; else $p4 = $nrlist[$i];
			echo print_dir($p1,$sortlist[$i], $pdir, $p4);
			echo '</table></td>';
	   }
	   echo '</tr></table></td></tr>';
	}
}

function read_dir($pdir, $drive=0, &$fcnt, &$dcnt)
{
	global $base_dir, $dir_list, $setctl, $cfg;	
	
	readresources($base_dir[$drive].$pdir);
	
	if (DISKSYNC) disksync($pdir, $drive);

	$res = fsearch($pdir, false, $drive,'id, free, track', false, true);

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
		$url = '';
		$result = albumshow($rows, $url, 0);
		
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
					if ($setctl->get('albumresize'))
					{
						$nw = $nh = 0;
						imgcoords(0,0, $img, $nw, $nh);
						echo '<img src="'.$img.'" alt="album" width="'.$nw.'" height="'.$nh.'"/>'; 
					} else echo '<img alt="album" src="'.$img.'"/>';
					?>
					</td></tr>
					<tr><td height="4"></td></tr>
					<?php
				}
			}
		} else if ($result) 
		{
			printdirhtml();
			echo '<tr><td>'.$url.'</td></tr><tr><td height="6"></td></tr>';
			printdirhtml(false);
		}
	}

	if ($fcnt == 0 && $dcnt == 0) echo '<tr><td class="file">'.get_lang(156).'</td></tr>'; 
	else
	{
		printdirhtml();
		
		if (db_guinfo('dircolumn') > 1) listdir($drive,$dir_list, $pdir, 0);
			else for ($i=0;$i<$dcnt;$i++) echo print_dir($drive,$dir_list[$i], $pdir, $i);
	
		printdirhtml(false);
		for ($i=0,$c=count($viewrows);$i<$c;$i++) print_file($viewrows[$i],0,1);
	}
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
			if ($i == 0) $show = '<font class="slash">/</font>'; else $show = null; 
			$out .= '<a class="dirheadline" href="'.$PHP_SELF.'?p='.base64_encode($dir).'&amp;d='.$drive.'">'.$show.$dirs[$i].'<font class="slash">/</font>'.'</a>';
		}
	}
	return $out;	
}

function showdir($pdir, $text='', $drive, $ximg='')
{
	global $PHP_SELF;

	$show= null;
	$root = '<a href="'.$PHP_SELF.'?action=root"><img src="'.getimagelink('root.gif').'" title="'.get_lang(119).'" alt="'.get_lang(119).'" border="0"/></a>&nbsp;';
	
	$dirname = null;
	$dirs = explode('/', $pdir);
	$selection = count($dirs) - 1;
	if (empty($dirs[count($dirs)-1])) $selection--;
	for ($i=0;$i<$selection;$i++) $dirname .= $dirs[$i].'/';
	
	if (empty($text))
	{
		$show = $root . '<a title="'.get_lang(118).'" href="'.$PHP_SELF.'?p='.base64_encode($dirname).'&amp;d='.$drive.'"><img src="'.getimagelink('cdback.gif').'" alt="'.get_lang(118).'" border="0"/></a>&nbsp;&nbsp;' . dir_divide($pdir,$drive);
	} else $show = $root.$text;

	echo '<table width="100%" border="0" cellspacing="0" cellpadding="0">';
	echo '<tr><td align="left"><font class="importnant"><b>'.$show.'&nbsp;</b></font>'.$ximg.'</td></tr>';
	echo '<tr><td height="5"></td></tr>';
	echo '<tr><td><img src="'.getimagelink('spacer.gif').'" border="0" height="1" width="80%" alt=""/></td></tr>';
	echo '<tr><td height="7"></td></tr>';
	echo '</table>';	
}

function get_dir($dirlist, $cnt, $n2)
{
	if (isset($dirlist[$cnt]) && md5($dirlist[$cnt]) == $n2) return $dirlist[$cnt];
	for ($i=0,$c=count($dirlist);$i<$c;$i++)
		if (md5($dirlist[$i]) == $n2) return $dirlist[$i];
	if (isset($dirlist[$cnt])) return $dirlist[$cnt];
	return '';
}

function figurepdir($pdir='', $count=-1, $drive=0, $n2='')
{
	global $runinit, $base_dir, $dir_list;
	if (!empty($pdir)) $pdir = stripslashes(base64_decode($pdir));

	if (checkstructure($pdir) == 0)
	{	
		if (isset($base_dir[$drive]))
		{
			readresources($base_dir[$drive].$pdir);

			if (is_numeric($count) && $count != -1)
			{
				$pdir .= get_dir($dir_list, $count, $n2);
				if (!empty($pdir)) 
				{ 
					if ($pdir[strlen($pdir)-1] != '/') $pdir .= '/'; 
				} else $pdir = '';
			}
			$runinit['pdir'] = $pdir;
			$runinit['pdir64'] = base64_encode($pdir);
			$runinit['drive'] = $drive;
			return true;
		} else return false;
	} else return false;
}

function kplaylist_filelist($where, $n=-1, $drive=0, $n2='')
{
	global $runinit, $mark, $marksid, $base_dir, $setctl;

	if (figurepdir($where, $n, $drive, $n2))
	{
		if (isset($_GET['mark']) && !empty($_GET['mark'])) $mark = explode(' ', strtoupper(trim($_GET['mark']))); else $mark = array();
		if (isset($_GET['marksid'])) $marksid = $_GET['marksid'];
		kprintheader('kPlaylist', 1);
		$kpd = new kpdesign();
		$kpd->top();
		if ((!isset($n) || $n == -1) && empty($where)) $root = true; else $root = false;

		if (!$root) showdir($runinit['pdir'],'', $runinit['drive']);		
		$fcnt = 0;
		$dcnt = 0;
		echo '</td></tr>';

		$list = true;

		if (db_guinfo('u_access') == 0)
		{
			if (count($base_dir) == 1 && $base_dir[0] == '/path/to/my/music/archive/')
			{	
				$list = false;
				firsttime(); 
			} 

			if ($setctl->get('basedir_changed') && $base_dir[0] != '/path/to/my/music/archive/') 
			{
				$setctl->set('basedir_changed', 0);
				if ($setctl->get('base_dir') != $setctl->get('oldbase_dir'))
				{
					$setctl->set('oldbase_dir', $setctl->get('base_dir'));
					$list = false;
					basedirchanged();
				}
			}
		}
		
		if ($list)
		{
			if ($root) listroot($fcnt, $dcnt); 
				else read_dir($runinit['pdir'], $runinit['drive'], $fcnt, $dcnt);
		}		
		
		if ($root) endmp3table(0, $dcnt, $fcnt); else endmp3table(1, $dcnt, $fcnt);
		$kpd->bottom();
		kprintend(); 
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
		$this->drive = 0;
		$this->origrow = false;
		$this->investigated = false;
		$this->id3 = false;
		$this->relativepath = '';
		$this->dir64 = '';
		$this->fdate = 0;
		
		$this->fsize = 0;
		$this->dbrow = $dbrow;
		$this->sid = $sid;
		if ($sid != -1)
		{
			$this->investigate();
			if ($id3) $this->getid3();
		}
	}
	
	function investigate()
	{
		global $base_dir;
		if ($this->dbrow !== false) $this->origrow = $this->dbrow; 
			else $this->origrow = get_searchrow($this->sid);
		
		if ($this->origrow !== false)
		{
			$this->fexists = false;
			$this->drive = $this->origrow['drive'];
			$this->investigated = true;
			$this->relativepath = $this->origrow['dirname']; 
			$this->fsize = $this->origrow['fsize'];
			$this->fdate = $this->origrow['date'];

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

	function gentitle($fields = array('track', 'artist', 'title', 'album'), $maxlength = 256)
	{
		$title = '';
		foreach ($fields as $name) if (isset($this->origrow[$name]) && !empty($this->origrow[$name])) checkcharadd($title, ' - ', $this->origrow[$name]);
		if (empty($title)) $title = $this->fname;
		$title = trim($title);
		if (strlen($title) > $maxlength) return substr($title, 0, $maxlength - 3).' ..';
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
		global $base_dir;
		return $base_dir[$this->drive];
	}
	
	function getdir64()
	{
		if (empty($this->dir64)) $this->dir64 = base64_encode($this->relativepath);
		return $this->dir64;
	}

	function weblink($sid=0, $fdate=0, $action='sid')
	{
		global $PHP_SELF, $u_cookieid, $cfg;
		if (!$sid) $sid = $this->sid;
		if (!$fdate) $fdate = $this->fdate;
		if (URLSECURITY) $urlextra = '&amp;'.urlsecurity($fdate, $sid); else $urlextra = '';
		return $PHP_SELF.'?'.$action.'='.$sid.'&amp;c='.$u_cookieid.$urlextra;
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


function KCheckActions()
{ 
	global $_POST, $_GET, $phpenv, $u_cookieid, $u_id, $PHP_SELF, $setctl, $valuser, $cfg;

	$stat = 0;
	if (isset($_GET['c'])) $acookie = $_GET['c']; else $acookie = '';	 

	if (isset($_COOKIE[$cfg['cookie']]))
	{
		$stat = db_verify_stream($_COOKIE[$cfg['cookie']], $phpenv['remote'], false);
		if ($stat) $acookie = $_COOKIE[$cfg['cookie']];
	}

	if (isset($_GET['c']) && $stat == 0) $stat = db_verify_stream($_GET['c'], $phpenv['remote'], true);		
	
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
			if (isset($_GET['streamsid'])) 
			{
				if (chksecurity($_GET['streamsid'])) Kplay_senduser2($_GET['streamsid'], 0); 
			} else
			if (isset($_GET['sid'])) 
			{
				if (chksecurity($_GET['sid'])) playresource2($_GET['sid']); 
			} else
			if (isset($_GET['streamplaylist']))
			{
				if (isset($_GET['extm3u'])) $valuser->set('extm3u', 1);
				$kp = new kp_playlist($_GET['streamplaylist']);
				$kp->play();
			}			
		}
	} else
	{
		$u_cookieid = $acookie;

		if (isset($_GET['downloadfile'])) 
		{
			if (chksecurity($_GET['downloadfile'])) Kplay_senduser2($_GET['downloadfile'], 0, true, 1); 
		} else
		if (isset($_GET['sid'])) 
		{
			if (chksecurity($_GET['sid'])) playresource2($_GET['sid']); 
		} else	
		if (isset($_GET['streamsid'])) 
		{
			if (chksecurity($_GET['streamsid'])) Kplay_senduser2($_GET['streamsid'], 0);
		} else	
		if (isset($_GET['imgsid']))
		{
			if (isset($_GET['w']) && is_numeric($_GET['w'])) $w = $_GET['w']; else $w = 0;
			if (isset($_GET['h']) && is_numeric($_GET['h'])) $h = $_GET['h']; else $h = 0;

			if (chksecurity($_GET['imgsid'])) createimg($_GET['imgsid'], true, $w, $h);
		}
	}
	die();
}

if (isset($_GET['downloadfile']) || isset($_GET['sid']) || isset($_GET['streamsid']) || isset($_GET['streamplaylist']) || isset($_GET['imgsid'])) KCheckActions();

if (isset($_GET['update']) && isset($_GET['user'])) search_updateautomatic($_GET['user'],$phpenv['remote'],$_GET['update']);

if (isset($_POST['signup']) || isset($_GET['signup']) && USERSIGNUP) KSignup();

if (isset($_POST['user']) && isset($_POST['password']) && !empty($_POST['user']) && !empty($_POST['password']))
{
	if (webauthenticate())
	{
		$uri = $PHP_SELF.'?checkcookie=true';
		if (isset($_POST['uri']) && !empty($_POST['uri'])) 
		{
			$ourl = urldecode($_POST['uri']);
			$qpos = strrpos($ourl, '?');
			if ($qpos !== false && $cfg['accepturi']) 
			{
				$addchkc = strpos($ourl, 'checkcookie=true');
				if ($addchkc === false) $addurl = '&checkcookie=true'; else $addurl = '';
				$uri = $PHP_SELF.substr($ourl, $qpos).$addurl;
			}
		}
		refreshurl($uri);
		die();
	} else
	{
		if ($setctl->get('report_attempts')) syslog_write('User could not be validated (user: "'.$_POST['user'].'")');
		klogon(get_lang(307));
	}
} 

if (isset($_COOKIE[$cfg['cookie']]) || $cfg['disablelogin'])
{	
	if (db_verify_stream(@$_COOKIE[$cfg['cookie']], $phpenv['remote'], false))
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

		if (isset($_GET['streamrss']))
		{
			$ca = new caction();
			$ca->updatelist();
			$ca->createrss(true);
			die();
		} else
		if (isset($_GET['whatsnewrss']))
		{
			$gl = new genlist();
			$gl->whats_new(0, 30);
			$gl->outrss();
			die();
		}

		if (isset($_GET['action']) || isset($_POST['action']))
		{
			if (isset($_GET['action'])) $action = $_GET['action']; else $action = $_POST['action'];		
			$match = true;
			
			switch ($action)
			{
				case 'bulletin':
						if (BULLETIN && class_exists('kbulletin'))
						{
							kprintheader(get_lang(268), 1);
							$kpd = new kpdesign();
							$kpd->addform = false;
							$kpd->top();
							$kb = new kbulletin();
							$kb->showall();
							$kpd->bottom();
							kprintend();							
						}
						break;
				
				case 'newbulletin':
						$kb = new kbulletin();
						$kb->editbulletin(0);
						break;
					
				case 'delbulletin':
						if (isset($_GET['bid']) && is_numeric($_GET['bid']))
						{
							kprintheader(get_lang(268), 1);
							$kpd = new kpdesign();
							$kpd->addform = false;
							$kpd->top();
							$kb = new kbulletin();
							$kb->delbulletin($_GET['bid'], $u_id);
							$kb->showall();
							$kpd->bottom();
							kprintend();
							
						}
						break;

				case 'editbulletin':
						if (isset($_GET['bid'])) 
						{
							$kb = new kbulletin();
							$kb->editbulletin($_GET['bid']);
						}
						break;

				
				case 'dropadmin':
						if (db_guinfo('u_access') == 0)
						{
							chsessionstatus($u_cookieid, 2);
							$uri = '';
							if (isset($_GET['p'])) $uri = '?p='.$_GET['p'];
							if (isset($_GET['d']) && !empty($uri)) $uri .= '&d='.$_GET['d'];	
							refreshurl($PHP_SELF.$uri);
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

				case 'admineditoptions':
						if (db_guinfo('u_access') == 0)
						{
								if (isset($_GET['id']) && is_numeric($_GET['id'])) show_useroptions(true, $_GET['id']); 
						}
						break;
				
				case 'editoptions':
						show_useroptions(false, $u_id);
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
						if (db_guinfo('u_access') == 0) 
						{
							if (isset($_POST['followsymlinks'])) $setctl->set('followsymlinks', 1); else $setctl->set('followsymlinks', 0);
							$setctl->publish('followsymlinks');
							
							search_updatelist($_POST);
						}						
						break;

				case 'id3edit':
						if (db_guinfo('u_access') == 0 && $cfg['id3editor'] && function_exists('file_id3editor'))
						{
							$f2 = new file2($_GET['id3sid']);
							if ($f2->ifexists()) file_id3editor($f2->fullpath);
						}
						break;

				case 'id3save':
						if (db_guinfo('u_access') == 0 && $cfg['id3editor'] && function_exists('file_id3editor_save'))
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
								save_user();
							} else show_users();
						}
						break;

				case 'newusertemplate':
						if (db_guinfo('u_access') == 0)
						{
							$id = vernum($_GET['id']);
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
						if (db_guinfo('u_access') == 0)
						{				
							$kpu = new kpuser();
							$kpu->set('u_access', 1);
							if (isset($_POST['newuser'])) show_userform($kpu);
								else
							if (isset($_POST['newtemplate'])) 
							{
								$kpu->set('utemplate', 1);
								show_userform($kpu);
							} else
							if (isset($_POST['refresh'])) show_users();
						}
						break;

				case 'useredit':
						if (db_guinfo('u_access') == 0)
						{
							$id = vernum($_GET['id']);
							$kpu = new kpuser();
							if ($kpu->load($id)) show_userform($kpu);
						}
						break;
				
				case 'userlogout':
						if (db_guinfo('u_access') == 0)
						{							
							$id = $_GET['id'];
							if (is_numeric($id) && $id != $u_id && !$cfg['demomode']) adminlogout($id);							
							show_users();
						}
						break;

				case 'userhistory':
						if (db_guinfo('u_access') == 0) 
						{	
							kprintheader(get_lang(121), 1);														
							if (isset($_POST['searchnavigate_right']) || isset($_POST['searchnavigate_left'])) 
							{
								$nv = new navi(7);
								if (isset($_POST['searchnavigate_right'])) $nv->searchnavi(1); else $nv->searchnavi(0);
							} else
							{
								$uh = new userhistory();
								$uh->setuid(vernum(fruser('id')));
								$uh->setfilter(fruser('cfilter', true, -1));
								$uh->setperpage(fruser('chperpage', true, 18));
								$uh->show();
								$nv = new navi(7, $uh->rows, true);
								$nv->setperpage($uh->perpage);
								$nv->setfollow('huid', $uh->uid);
								$nv->setfollow('filter', $uh->filter);
								$nv->setfollow('hperpage', $uh->perpage);
								$nv->writenavi();
								$uh->endshow();
							}
							kprintend();
						}
						break;
					
				case 'useractivate':
						if (db_guinfo('u_access') == 0 && is_numeric($_GET['id'])) db_execquery('UPDATE '.TBL_USERS.' SET u_status = 0 WHERE u_id = '.$_GET['id']);
						show_users();
						break;

				case 'saveadminuseroptions':	
						if (db_guinfo('u_access') == 0)
						{
							if (!isset($_POST['cancel'])) 
							{
								$id = $_POST['id'];
								save_useroptions($id, $_POST);
								show_useroptions(true, $id, null,true); break;
							} else show_users(); 
						}
						break;

				case 'saveuseroptions':
						$state = save_useroptions($u_id, $_POST);
						switch ($state)
						{
							case 2: show_useroptions(false, $u_id, get_lang(157), true); break;
							case 3: show_useroptions(false, $u_id, get_lang(165), true); break;
							default: show_useroptions(false, $u_id, null,true); break;
						}
						break;

				case 'deletefiletype':
						if (db_guinfo('u_access') == 0)
						{
							db_execquery('DELETE from '.TBL_FILETYPES.' WHERE id = '.vernum($_GET['del']));
							settings_edit(1, 3);
						}
						break;

				case 'findmusic':
						if (db_guinfo('u_access') == 0) findmusic();
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
							$id = store_filetype(vernum($_POST['id']), $m3u, $search, $logaccess, myescstr($_POST['mime']), $extension);
							edit_filetype(vernum($id), true);
						}
						break;

				case 'search':
						if (fruserset('orsearch')) $valuser->set('orsearch', 1); else $valuser->set('orsearch', 0);
						if (fruserset('onlyid3')) $valuser->set('defaultid3', 1); else $valuser->set('defaultid3', 0);
						if (fruserset('hitsas')) $valuser->set('hitsas', verchar(fruser('hitsas')));
						if (fruserset('searchwh')) $valuser->set('defaultsearch', vernum(fruser('searchwh'))); 
						$valuser->update();

						$kps = new kpsearch();
						if (fruserset('searchfor') && !fruserempty('searchfor') && is_array($kps->getwords($kps->what)))
						{							
							$kps->gensearchsql();
							kprintheader(get_lang(5), 1);
							$kpd = new kpdesign();
							$kpd->top();
							
							$kps->viewsearch();							
							$nv = new navi(2, $kps->rows, true);
							$nv->writenavi();
							$kps->endsearch();
							$kpd->bottom();
							kprintend();
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
									$name = myescstr(stripslashes(checkchs($_POST['playlistname'])));						
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
							$m3ug = new m3ugenerator();
							for ($i=0,$c=count($_POST['selected']);$i<$c;$i++)
							{
								$row = mysql_fetch_array(db_execquery('SELECT sid FROM '.TBL_PLAYLIST_LIST.' WHERE id = '.$_POST['selected'][$i]));
								$m3ug->sendlink2($row['sid']);					
							}
							$m3ug->start();
						} else
						if (isset($_POST['delselected']))
						{
							if (count($_POST['selected']) > 0)
							{
								for ($i=0;$i<count($_POST['selected']);$i++)
								{
									$id = $_POST['selected'][$i];
									db_execquery('DELETE FROM '.TBL_PLAYLIST_LIST.' WHERE id = '.$id);
								}
								playlist_rewriteseq($_POST['sel_playlist']);
							}
							playlist_editor($_POST['sel_playlist'], $_POST['previous'], $_POST['sort']);				
						}
						break;
				
				case 'misc':
						if (isset($_POST['whatshot'])) genliststart(4);	
							else
						if (isset($_POST['whatsnew'])) genliststart(3);
							else
						if (!empty($_POST['genrelist']))
						{
							if (isset($_POST['genreno'])) user_saveoption('defgenre', $_POST['genreno']);
							genliststart(6);
						} else
						if (isset($_POST['logmeout']))
						{ 
							if ($cfg['demomode'] != 1) db_logout($u_cookieid, $phpenv['remote']); 
							$deflanguage = $setctl->get('default_language');
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
								db_execquery('DELETE FROM '.TBL_PLAYLIST_LIST.' WHERE id = '.$id);					
								playlist_rewriteseq($plid);
							}
						}
						playlist_editor($plid, $_GET['p']);
						break;
				
				case 'hotselect':
						if (isset($_GET['artist'])) hotselect($_GET['artist']);	
						break;				
				
				case 'playalbum':
						
						if (figurepdir($_GET['p'], -1, $runinit['drive']))
						{							
							$res = fsearch($runinit['pdir'], false, $runinit['drive'], 'id,album');						
						
							$m3ug = new m3ugenerator();
							if (!empty($_GET['ft'])) $ft = stripcslashes($_GET['ft']); else $ft = '';
							if (isset($_GET['ftid']) && is_numeric($_GET['ftid'])) 
							{
								$f2 = new file2($_GET['ftid'], true);
								$ft = $f2->id3['album'];
							}

							while ($row = mysql_fetch_row($res))
							{
								if (!empty($ft) && $ft != $row[1]) continue;
								$m3ug->sendlink2($row[0]);
							}
							$m3ug->start();						
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
						break;
				
				case 'dlall':
						if (isset($_GET['p']) && isset($_GET['d']) && ALLOWDOWNLOAD && db_guinfo('u_allowdownload') && $cfg['archivemode'] && db_guinfo('allowarchive'))
						{
							$kpa = new kparchiver();							
							if (figurepdir($_GET['p'], -1, $_GET['d']))
							{
								$res = fsearch($runinit['pdir'], false, $_GET['d']);
								while ($row = mysql_fetch_row($res)) $kpa->setfile($row[0]);								
								$kpa->execute();
							}
						}
						break;

				case 'dlplaylist':
						if (isset($_GET['pid']) && ALLOWDOWNLOAD && db_guinfo('u_allowdownload') && $cfg['archivemode'] && db_guinfo('allowarchive'))
						{
							$kpa = new kparchiver();
							$kp = new kp_playlist($_GET['pid']);
							$res = $kp->getres();							
							while ($row = mysql_fetch_row($res)) $kpa->setfile($row[0]);								
							$kpa->execute();
						}
						break;

				case 'dlselected':
						if (isset($_POST['filestoarc']) && ALLOWDOWNLOAD && db_guinfo('u_allowdownload') && $cfg['archivemode'] && db_guinfo('allowarchive'))
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

				case 'gotopage':
					$page = $_GET['page'];
					if (is_numeric($page))
					{						
						$nv = new navi();

						kprintheader($nv->header, 1);
						if ($nv->gui) 
						{
							$kpd = new kpdesign();
							$kpd->top();
						}
						$nv->searchnavi(2, $page - 1);							
						if ($nv->gui) $kpd->bottom();
						kprintend();						
					}
					break;

				case 'listedres':
						if (isset($_POST['searchnavigate_right']) || isset($_POST['searchnavigate_left'])) 
						{
							$nv = new navi(2);
							kprintheader($nv->header, 1);
							$kpd = new kpdesign();
							$kpd->top();							
							if (isset($_POST['searchnavigate_right'])) $nv->searchnavi(1); else $nv->searchnavi(0);
							$kpd->bottom();
							kprintend();
						} else
						if (isset($_POST['hotoptions'])) 
						{
							if (isset($_POST['hotperiod']) && is_numeric($_POST['hotperiod'])) 
							{
								$filter = $_POST['hotperiod']; 
								user_saveoption('hotmode', $filter);
							} else $filter = 0;
							genliststart(4);
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
								if (!$kp->play()) errormessage(get_lang(302), true);
							}		
						} else
						if (isset($_POST['psongsselected']) || isset($_POST['psongsall']) || isset($_POST['pdirsall']))
						{
							$m3ug = new m3ugenerator();
							if (isset($_POST['psongsselected']))
							{
								if (isset($_POST['selected']))
									for ($i=0,$c=count($_POST['selected']);$i<$c;$i++) $m3ug->sendlink2($_POST['selected'][$i]);
								$m3ug->start();
							} else 
							if (isset($_POST['psongsall']))
							{
								if (figurepdir($_POST['previous'], -1, $runinit['drive']))
								{
									$res = fsearch($runinit['pdir'], false, $runinit['drive']);
									while ($row = mysql_fetch_row($res)) $m3ug->sendlink2($row[0]);
									$m3ug->start();
								}
							} else 
							if (isset($_POST['pdirsall']))
							{
								if (figurepdir($_POST['previous'], -1, $runinit['drive']))
								{
									$res = fsearch($runinit['pdir'], false, $runinit['drive'], 'id', true);
									while ($row = mysql_fetch_row($res)) $m3ug->sendlink2($row[0]);
									$m3ug->start();
								}
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
		isset($_GET['d']) ? $d = $_GET['d'] : $d = 0;
		kplaylist_filelist($p, $n, $d, $n2);
	} else
	{
		klogon();
	}
} else if (isset($_GET['checkcookie']))
{
	klogon(get_lang(237));
} else if (isset($_GET['streamrss']))
{
	if ($setctl->get('publicrssfeed'))
	{
		$ca = new caction();
		$ca->updatelist();
		if ($setctl->get('unauthorizedstreams')) $ca->createrss(true); else $ca->createrss(false);
	}
} else
if (isset($_GET['whatsnewrss']))
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