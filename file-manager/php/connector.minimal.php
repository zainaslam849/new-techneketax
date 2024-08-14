<?php
error_reporting(0); // Set E_ALL for debuging
is_readable('./vendor/autoload.php') && require './vendor/autoload.php';
require './autoload.php';

elFinder::$netDrivers['ftp'] = 'FTP';


function access($attr, $path, $data, $volume, $isDir, $relpath) {
	$basename = basename($path);
	return $basename[0] === '.'
			 && strlen($relpath) !== 1
		? !($attr == 'read' || $attr == 'write')
		:  null;
}


$opts = array(
	// 'debug' => true,
	'roots' => array(
		// Items volume
		array(
			'driver'        => 'LocalFileSystem',
			'path'          => '../../file-manager/Documents/',
			'URL'           => dirname($_SERVER['PHP_SELF']) . '../../Documents/',
			'trashHash'     => 't1_Lw',
			'winHashFix'    => DIRECTORY_SEPARATOR !== '/',
			'uploadDeny'    => array('all'),
			'uploadAllow'   => array('image/x-ms-bmp', 'image/gif', 'image/jpeg', 'image/png', 'image/x-icon', 'text/plain'),
			'uploadOrder'   => array('deny', 'allow'),
			'accessControl' => 'access'
		),

		// Trash volume
		array(
			'id'            => '1',
			'driver'        => 'Trash',
			'path'          => '../../file-manager/Documents/.trash/',
			'tmbURL'        => dirname($_SERVER['PHP_SELF']) . '/../Documents/.trash/.tmb/',
			'winHashFix'    => DIRECTORY_SEPARATOR !== '/',
			'uploadDeny'    => array('all'),
			'uploadAllow'   => array('image/x-ms-bmp', 'image/gif', 'image/jpeg', 'image/png', 'image/x-icon', 'text/plain'),
			'uploadOrder'   => array('deny', 'allow'),
			'accessControl' => 'access',
		),
	)
);

$connector = new elFinderConnector(new elFinder($opts));
$connector->run();