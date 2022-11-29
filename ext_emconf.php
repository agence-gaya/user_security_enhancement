<?php

$EM_CONF['user_security_enhancement'] = array(
	'title' => 'User Security Enhancement',
	'description' => 'Define custom policies for secure frontend authentication.',
	'category' => 'module',
	'author' => 'GAYA - Manufacture digitale',
	'author_email' => 'contact@gaya.fr',
	'state' => 'alpha',
	'version' => '2.0.0',
	'constraints' => array(
		'depends' => array(
			'typo3' => '10.4.0-10.4.99'
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
);