<?php

$url = filter_var($_REQUEST['url'], FILTER_VALIDATE_URL);
if (!$url) {
  exit('Invalid URL');
}

$html = file_get_contents($url);

/**
 * @var array $http_response_header materializes out of thin air
 */

$status_line = $http_response_header[0];
preg_match('{HTTP\/\S*\s(\d{3})}', $status_line, $match);
$status = $match[1];

if (in_array($status, ["400", "401", "402", "403", "404"])) {
  print $status_line;
  exit;
}

if (in_array($status, ["301", "302"])) {
  foreach ($http_response_header as $header) {
    header($header);
  }
}

$base = parse_url($url, PHP_URL_SCHEME) . "://" . parse_url($url, PHP_URL_HOST);

echo preg_replace('~(?:src|action|href)=[\'"]\K/(?!/)[^\'"]*~', "$base$0", $html);

exit;
