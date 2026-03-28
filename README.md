# pgp-2fa

2-Factor-Authentication for the Web with PGP

Wrapper around the GnuPG extension for PHP to make 2-Factor-Authentication with PGP as easy as possible.

**Requires PHP ≥ 7.4 and the [GnuPG PECL extension](https://pecl.php.net/package/gnupg).**

**Root access to the server is necessary to install the extension!**

## Installation

### Via Composer

```bash
composer require hardest1/pgp-2fa
```

### Manual

Download `pgp-2fa.php` and include it in your project:

```php
require_once '/path/to/pgp-2fa.php';
```

## Usage

Usage is pretty simple.

If pgp-2fa is used with a standard MySQL-based login, this code has to go on the page where your login form is.
First step is to start the session (if it isn't already started):
```php
<?php
session_start();
?>
```
Then you have to include the pgp-2fa class and create a new instance:
```php
<?php
include('/path/to/pgp-2fa.php');
$pgp = new pgp_2fa();
?>
```
Now you can generate a new secret code. The default length is 15 and it is made out of numbers.
The function to generate the secret code can easily be adjusted for your own needs.
After invoking this function, the unencrypted form of the secret is saved within the instance of the class for the next step, and a hashed and safe form of this secret is stored in the session:
```php
<?php
$pgp->generateSecret();
?>
```
After generating the secret, you can encrypt it with PGP with a given Public Key:
(In most cases, the public key is stored in a MySQL database so you have to connect to your database and retrieve the public key for the user that is currently logging in)
```php
<?php
$pgp_message = $pgp->encryptSecret($public_key);
?>
```
The complete code until now should look something like this:
```php
<?php
session_start();

include('/path/to/pgp-2fa.php');

$pgp = new pgp_2fa();
$pgp->generateSecret();

$pgp_message = $pgp->encryptSecret($public_key);
?>
```
The `$pgp_message` variable contains the PGP message the user has to decrypt.
This message should be displayed together with an input where the user can type in the decrypted code.

To compare the user given code with the real code, just use `compare()` in your Form validation process:
```php
<?php
if($pgp->compare($_POST['user-input'])){
  // Success!
}else{
  // Failure!
}
?>
```

### Security notes

- The secret is automatically invalidated after successful verification to prevent replay attacks.
- A maximum of **5 attempts** is allowed before the secret is locked out.
- The secret expires after **5 minutes**.
- Always use **HTTPS** and configure session cookies accordingly:
  ```php
  ini_set('session.cookie_httponly', 1);
  ini_set('session.cookie_secure', 1);
  ini_set('session.use_strict_mode', 1);
  ini_set('session.cookie_samesite', 'Strict');
  session_start();
  ```
- Always add **CSRF protection** to your forms.

Examples are included in the `examples/` directory!

## How to install the GnuPG PHP Extension

### 1. Install required packages
```bash
apt-get install build-essential libssl-dev
apt-get install gnupg libgpg-error-dev libassuan-dev libgpgme11-dev
apt-get install php-dev php-pear
```

### 2. Download and build GPGME
Go to https://www.gnupg.org/download/ and download the latest GPGME tarball to a writable directory.
Example (replace X.X.X with current version number):
```bash
wget https://www.gnupg.org/ftp/gcrypt/gpgme/gpgme-X.X.X.tar.bz2
```
Then extract the archive and cd to the new directory:
```bash
tar xfvj gpgme-X.X.X.tar.bz2
cd gpgme-X.X.X
```
Configure, make and install GPGME:
```bash
./configure
make
make install
```

### 3. Install the PHP extension:
```bash
pecl install gnupg
```
Open your php.ini and add `extension=gnupg.so`:
```ini
extension=gnupg.so
```

### Done!

If everything works fine, you should be able to see a new entry in your phpinfo():

![PHPInfo](docs/img/phpinfo.png "PHP Info")

## License

MIT — see [LICENSE](LICENSE) for details.
