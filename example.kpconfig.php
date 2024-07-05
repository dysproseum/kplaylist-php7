<?php
$cfg['archivemode'] = true;
$cfg['dateformat'] = 'm.d.y H:i';
$cfg['timeformat'] = 'H:i';
$cfg['smalldateformat'] = 'm.d.y';
$cfg['laststreamscount'] = 12;
$cfg['window_x'] = 420;
$cfg['window_y'] = 420;
$cfg['enablegetid3'] = 1;

// Database connection.
$cfg['db_host'] = 'localhost';
$cfg['db_name'] = 'kplaylist';
$cfg['db_user'] = 'kplaylist';
$cfg['db_pass'] = 'kplaylist';
$cfg['db_prepend'] = 'tbl_';

// Set custom streaming buffer size.
$cfg['stream_buffer_size'] = 1024 * 1024;

// Override mime type so flac will stream.
$cfg['stream_flac_mime_override'] = 'audio/mp3';
