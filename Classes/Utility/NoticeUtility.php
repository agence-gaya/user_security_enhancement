<?php
namespace GAYA\UserSecurityEnhancement\Utility;

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class NoticeUtility
 *
 * @package GAYA\UserSecurityEnhancement\Utility
 */
class NoticeUtility implements SingletonInterface
{
    /**
     * configurationUtility
     *
     * @var ConfigurationUtility
     */
    protected $configurationUtility = NULL;

    public function __construct()
    {
        $this->configurationUtility = GeneralUtility::makeInstance(ConfigurationUtility::class);
    }

    /**
     * Get the notice policies
     *
     * @return string
     */
    public function getNotice(): string
    {
        $configuration = $this->configurationUtility->getConfiguration();

        $text = LocalizationUtility::translate('notice.password', 'userSecurityEnhancement') . '<ul>';

        if ($configuration['passwordLength']) {
            $text .= '<li>' . LocalizationUtility::translate('notice.password.length', 'userSecurityEnhancement', array($configuration['passwordLength'])) . '</li>';
        }
        if ($configuration['capitalLettersNumber']) {
            $text .= '<li>' . LocalizationUtility::translate('notice.password.capitalLettersNumber', 'userSecurityEnhancement', array($configuration['capitalLettersNumber'])) . '</li>';
        }
        if ($configuration['tinyLettersNumber']) {
            $text .= '<li>' . LocalizationUtility::translate('notice.password.tinyLettersNumber', 'userSecurityEnhancement', array($configuration['tinyLettersNumber'])) . '</li>';
        }
        if ($configuration['specialCharactersNumber']) {
            $text .= '<li>' . LocalizationUtility::translate('notice.password.specialCharactersNumber', 'userSecurityEnhancement', array($configuration['specialCharactersNumber'])) . '</li>';
        }
        if ($configuration['digitsNumber']) {
            $text .= '<li>' . LocalizationUtility::translate('notice.password.digitsNumber', 'userSecurityEnhancement', array($configuration['digitsNumber'])) . '</li>';
        }

        $text .= '</ul>';

        return $text;
    }
}
