<?php
namespace GAYA\UserSecurityEnhancement\ViewHelpers;

use GAYA\UserSecurityEnhancement\Utility\ConfigurationUtility;

/**
 * Class ConfigurationViewHelper
 *
 * @package GAYA\UserSecurityEnhancement\ViewHelpers
 */
class ConfigurationViewHelper extends \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * Disable escaping of tag based ViewHelpers so that the rendered tag is not htmlspecialchar'd
     *
     * @var boolean
     */
    protected $escapeOutput = false;

    /**
     * configurationUtility
     *
     * @var ConfigurationUtility
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $configurationUtility = NULL;

    /**
     * Initialize arguments
     */
    public function initializeArguments()
    {
        $this->registerArgument('as', 'string', 'The name of the variable to retrieve configuration', true);
    }

    public function render()
    {
        $configuration = $this->configurationUtility->getConfiguration();

        $templateVariableContainer = $this->renderingContext->getVariableProvider();

        $templateVariableContainer->add($this->arguments['as'], $configuration);
        $content = $this->renderChildren();
        $templateVariableContainer->remove($this->arguments['as']);

        return $content;
    }

}
