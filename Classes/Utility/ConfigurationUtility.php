<?php
namespace GAYA\UserSecurityEnhancement\Utility;

use TYPO3\CMS\Core\SingletonInterface;

/**
 * Class ConfigurationUtility
 *
 * @package GAYA\UserSecurityEnhancement\Utility
 */
class ConfigurationUtility implements SingletonInterface
{
    /**
     * Get extension configuration
     *
     * @param string|null $key
     * @return array|string
     */
    public function getConfiguration(string $key = null)
    {
        $configuration = array(
            'passwordLength' => 8,
            'capitalLettersNumber' => 1,
            'tinyLettersNumber' => 1,
            'specialCharactersNumber' => 1,
            'digitsNumber' => 1,
            'passwordHistory' => 5,
            'authenticationFailureAttempts' => 5,
            'authenticationFailureLock' => 15,
            'authenticationFailureMaxLock' => 1440
        );

        if (!empty($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['user_security_enhancement'])) {
            $configuration = array_merge($configuration, unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['user_security_enhancement']));

            $configuration = array_map('intval', $configuration);
        }

        if ($key) {
            return $configuration[$key];
        } else {
            return $configuration;
        }
    }
}
