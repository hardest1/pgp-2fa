<?php

/*

Title: 2FA-PGP

Description: 2-Factor-Authentication for the web with PGP

Copyright: 2015 hardest1

*/

// Generate a random secret passphrase with a default length of 15

function generateSecretKey($length = 15){
    $secret = '';
    
	for($i = 0; $length > $i; $i++){
        $secret = $secret.rand(0,9);
    }
    
	return $secret;
}

// PGP-2FA Class

class pgp_2fa {
    
	// Unencrypted secret
	
    private $secret;
    
	// Generate safe hash of unencrypted secret, push it to the session and save unencrypted secret locally
	
    public function generateSecret(){
        
		// Generate unencrypted secret
		
		$secret = generateSecretKey();
        
		// Hash the secret with bcrypt
		
		$secret_hash = password_hash($secret, PASSWORD_BCRYPT);
		
		// Save within the session
		
        $_SESSION['pgp-secret-hash'] = $secret_hash;
		
		// Save the unencrypted secret locally for safety
		
        $this->secret = $secret;
    }
    
	// Encrypt secret with PGP public key
	
    public function encryptSecret($public_key){
        
		// Set GnuPG homedir to /tmp
		
        putenv("GNUPGHOME=/tmp");
        
		// Create new GnuPG instance
		
        $gpg = new gnupg();
        
		// Import given public key
		
        $key = $gpg->import($public_key);
        
		// Add imported key for encryption
		
        $gpg->addencryptkey($key['fingerprint']);
        
		// Encrypt the secret to a PGP message
		
        $enc = $gpg->encrypt($this->secret);
        
		// Clear the encryption key
		
        $gpg->clearencryptkeys();
        
		// Return  the PGP message
		
        return $enc;
    }
    
	// Compare user input with saved secret.
	
    public function compare($user_input){
		
		// Compare
		
        if(password_verify($user_input,$_SESSION['pgp-secret-hash'])){
            
			return true;
        
		} else {
            
			return false;
        
		}
	
    }
    
}

?>