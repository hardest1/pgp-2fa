<?php

/*

Title: 2FA-PGP

Description: 2-Factor-Authentication for the web with PGP

Copyright: 2015 hardest1

License: MIT

*/

/**
 * Generate a random secret passphrase with a default length of 15.
 *
 * Uses cryptographically secure random_int() for secret generation.
 *
 * @param int $length Length of the secret to generate.
 * @return string The generated numeric secret.
 */
function generateSecretKey(int $length = 15): string
{
    return pgp_2fa::generateSecretKey($length);
}

/**
 * PGP-based Two-Factor Authentication class.
 *
 * Wraps the GnuPG PHP extension to provide a simple interface for
 * encrypting a secret with a user's PGP public key and verifying
 * the decrypted response.
 */
class pgp_2fa {

    /** Maximum number of verification attempts before lockout. */
    private const MAX_ATTEMPTS = 5;

    /** Secret time-to-live in seconds (5 minutes). */
    private const SECRET_TTL = 300;

    /** @var string Unencrypted secret, held in memory only. */
    private string $secret = '';

    /**
     * Generate a random numeric secret key.
     *
     * Uses cryptographically secure random_int() instead of rand().
     *
     * @param int $length Length of the secret to generate.
     * @return string The generated numeric secret.
     */
    public static function generateSecretKey(int $length = 15): string
    {
        $secret = '';
        for ($i = 0; $i < $length; $i++) {
            $secret .= random_int(0, 9);
        }
        return $secret;
    }

    /**
     * Generate a secret, hash it into the session, and keep the
     * plaintext in the instance for subsequent encryption.
     *
     * @return void
     */
    public function generateSecret(): void
    {
        $secret = self::generateSecretKey();

        $secret_hash = password_hash($secret, PASSWORD_BCRYPT);

        $_SESSION['pgp-secret-hash'] = $secret_hash;
        $_SESSION['pgp-2fa-created'] = time();
        $_SESSION['pgp-2fa-attempts'] = 0;

        $this->secret = $secret;
    }

    /**
     * Encrypt the secret with the given PGP public key.
     *
     * Creates an isolated GnuPG home directory per request to avoid
     * sharing the keyring via /tmp. Validates the key format and
     * checks all GnuPG return values.
     *
     * @param string $public_key ASCII-armored PGP public key.
     * @return string The encrypted PGP message.
     * @throws \InvalidArgumentException If the key format is invalid.
     * @throws \RuntimeException On any GnuPG failure.
     */
    public function encryptSecret(string $public_key): string
    {
        if (strpos($public_key, '-----BEGIN PGP PUBLIC KEY BLOCK-----') === false) {
            throw new \InvalidArgumentException('Invalid PGP public key format');
        }

        // Use an isolated, per-request GnuPG home directory
        $gnupgHome = sys_get_temp_dir() . '/gnupg_' . bin2hex(random_bytes(16));
        if (!mkdir($gnupgHome, 0700, true)) {
            throw new \RuntimeException('Failed to create GnuPG home directory');
        }

        putenv("GNUPGHOME=$gnupgHome");

        try {
            $gpg = new gnupg();

            $key = $gpg->import($public_key);
            if ($key === false || empty($key['fingerprint'])) {
                throw new \RuntimeException('Failed to import PGP public key: ' . $gpg->geterror());
            }

            $gpg->addencryptkey($key['fingerprint']);

            $enc = $gpg->encrypt($this->secret);
            if ($enc === false) {
                throw new \RuntimeException('PGP encryption failed: ' . $gpg->geterror());
            }

            $gpg->clearencryptkeys();

            return $enc;
        } finally {
            // Clean up the temporary GnuPG home directory
            $files = glob("$gnupgHome/*") ?: [];
            // Also remove hidden files (e.g. .gpg-v21-migrated)
            $hiddenFiles = glob("$gnupgHome/.*") ?: [];
            foreach (array_merge($files, $hiddenFiles) as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            @rmdir($gnupgHome);
        }
    }

    /**
     * Compare user input against the hashed secret stored in the session.
     *
     * Enforces a maximum number of attempts and a time-to-live on the
     * secret. On successful verification the secret is invalidated to
     * prevent replay attacks.
     *
     * @param string $user_input The code the user decrypted.
     * @return bool True if the code matches, false otherwise.
     */
    public function compare(string $user_input): bool
    {
        if (!isset($_SESSION['pgp-secret-hash'])) {
            return false;
        }

        // Enforce secret TTL
        if (isset($_SESSION['pgp-2fa-created']) &&
            time() - $_SESSION['pgp-2fa-created'] > self::SECRET_TTL) {
            $this->clearSecret();
            return false;
        }

        // Enforce attempt limit
        if (isset($_SESSION['pgp-2fa-attempts']) &&
            $_SESSION['pgp-2fa-attempts'] >= self::MAX_ATTEMPTS) {
            $this->clearSecret();
            return false;
        }

        $_SESSION['pgp-2fa-attempts'] = ($_SESSION['pgp-2fa-attempts'] ?? 0) + 1;

        $result = password_verify($user_input, $_SESSION['pgp-secret-hash']);

        // Invalidate the secret after successful verification to prevent replay
        if ($result) {
            $this->clearSecret();
        }

        return $result;
    }

    /**
     * Remove all 2FA-related data from the session.
     *
     * @return void
     */
    private function clearSecret(): void
    {
        unset(
            $_SESSION['pgp-secret-hash'],
            $_SESSION['pgp-2fa-created'],
            $_SESSION['pgp-2fa-attempts']
        );
    }
}