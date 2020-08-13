<?php
return [
	'frontend' => [
		'gaya/user-security-enhancement/authentication-security' => [
		    'target' => \GAYA\UserSecurityEnhancement\Middleware\FrontendUserSecurity::class,
            'before' => [
                'typo3/cms-frontend/base-redirect-resolver'
            ],
            'after' => [
                'typo3/cms-frontend/authentication'
            ],
        ],
	],
];