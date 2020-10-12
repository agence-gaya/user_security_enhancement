# User security enhancement

## Introduction

This extension enhance frontend security by defining policies.
This extension is working with felogin core extension with extbase module. The pibase module is not supported anymore.

## Policies

After installation, you will be able to configure policies in extension configuration.
Don't forget to include TypoScript setup in your root template.

### Password complexity

This policy allow to define multiple password rules :

- Password minimum length
- Minimum number of capital letters
- Minimum number of tiny letters
- Minimum number of special characters
- Minimum number of digits

Configuration :

- passwordLength : 8 by default
- capitalLettersNumber : 1 by default
- tinyLettersNumber : 1 by default
- specialCharactersNumber : 1 by default
- digitsNumber : 1 by default

### Password history

This policy store last passwords (encrypted) and prevent user to re-use them :

Configuration :

- passwordHistory : 5 by default

### Authentication failure lock

After successives authentication failure, the user is lock for certain time. If an other attempts are are done during this time, the time will be multiplied by 2 for each attempts.

Configuration :

- authenticationFailureAttempts : 5 by default
- authenticationFailureLock : 15 min by default
- authenticationFailureMaxLock : 1 day by default

### Session invalidation

When a user change its password, all it's TYPO3 sessions are invalidated.

## Credits
&copy; 2020 GAYA Manufacture digitale [https://www.gaya.fr/]