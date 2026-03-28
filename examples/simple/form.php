<?php
// NOTE: In production, always use HTTPS and configure these session settings
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');
session_start();
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>2FA-PGP</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-OLBgp1GsljhM2TJ+sbHjaiH9txEUvgdDTAzHv2P24donTt6/529l+9Ua0vFImLlb" crossorigin="anonymous">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    </head>
    <body>
        <h1 class="text-center my-4">2FA-PGP</h1>

        <div class="container">
            <!-- WARNING: In production, always include a CSRF token in your forms. -->
            <form class="form" action="pgp.php" method="post">
                <div class="mb-3">
                    <label for="pgp-key" class="form-label">Your Public Key:</label>
                    <textarea rows="20" class="form-control" id="pgp-key" name="pgp-key"></textarea>
                </div>
                <button type="submit" class="btn btn-primary w-100">Submit!</button>
            </form>
        </div>
    </body>
</html>