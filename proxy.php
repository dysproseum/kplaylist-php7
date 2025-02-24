<?php

$url = $_GET['url'];

$html = file_get_contents($url);
if (!$html) {
  // @todo check status codes https://stackoverflow.com/a/52662522
  print "Failed to load";
  exit;
}

// $html=<<<HTML
// <img src="/relative/url/img.jpg" />
// <form action="/">
// <a href='/relative/url/'>Note the Single Quote</a>
// <img src="//site.com/protocol-relative-img.jpg" />
// HTML;
//
// $base='https://example.com';
//
// echo preg_replace('~(?:src|action|href)=[\'"]\K/(?!/)[^\'"]*~',"$base$0",$html);

$base = parse_url($url, PHP_URL_SCHEME) . "://" . parse_url($url, PHP_URL_HOST);

echo preg_replace('~(?:src|action|href)=[\'"]\K/(?!/)[^\'"]*~',"$base$0",$html);

