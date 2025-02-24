<?php 

$url = $_GET['url'];

$output= file_get_contents($url);

print $output ? $output : "Failed to load";
