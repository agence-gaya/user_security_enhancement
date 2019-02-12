<?php

$EM_CONF[$_EXTKEY] = array(
	'title' => 'User Security Enhancement',
	'description' => 'Define custom policies for secure frontend authentication',
	'category' => 'module',
	'author' => 'GAAY - Manufacture digitale',
	'author_email' => 'contact@gaya.fr',
	'state' => 'stable',
	'internal' => '',
	'uploadfolder' => '0',
	'createDirs' => '',
	'clearCacheOnLoad' => 0,
	'version' => '1.0.0',
	'constraints' => array(
		'depends' => array(
			'typo3' => '7.6.0-7.6.99',
            'saltedpasswords' => '7.6.0-7.6.99'
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
);