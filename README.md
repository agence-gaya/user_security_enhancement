# User security enhancement

## Introduction

This extension enhance frontend ssecurity by defining policies.

## Policies

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
&copy; 2019 GAYA Manufacture digitale [http://www.gaya.fr/]