<?php
namespace GAYA\UserSecurityEnhancement\Validator;

/**
 * Class PasswordValidator
 *
 *  NOTE : this validator must only be used for user creation. It does not check password history
 *  For password changing, use FrontendLoginController::changePassword() from felogin extension of implement your own check.
 *
 * @package GAYA\UserSecurityEnhancement\Validator
 */
class PasswordValidator extends \TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator
{

	/**
	 * passwordUtility
	 *
	 * @var \GAYA\UserSecurityEnhancement\Utility\PasswordUtility
	 * @inject
	 */
	protected $passwordUtility = NULL;

    /**
     * This validator always needs to be executed even if the given value is empty.
     * See AbstractValidator::validate()
     *
     * @var bool
     */
    protected $acceptsEmptyValues = true;

    /**
     * Checks if the given property ($propertyValue) corresponds to the constraints.
     *
     * @param mixed $value The value that should be validated
     * @return void
     */
    public function isValid($value)
    {
	    if (!$this->passwordUtility->checkPasswordValidity($value)) {
		    $this->addError(
			    $this->translateErrorMessage(
				    'validator.password.notvalid',
				    'userSecurityEnhancement'
			    ), 1530798227);
	    }
    }
}
