<?php
// NOTE: In production, always use HTTPS and configure these session settings
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');
session_start();

include('pgp-2fa.php');

$pgp = new pgp_2fa();

$msg = '';
$pgpmessage = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['pgp-key'])) {

    if ($pgp->compare($_POST['user-input'] ?? '')) {
        $msg = '<div class="alert alert-success">Success!</div>';
    } else {
        $msg = '<div class="alert alert-danger">Fail!</div>';
    }

} elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['pgp-key'])) {
    $pgp->generateSecret();
    try {
        $pgpmessage = $pgp->encryptSecret($_POST['pgp-key']);
    } catch (\Exception $e) {
        $msg = '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</div>';
    }
}

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>2FA-PGP</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YcnS/1WR6zNg4t1pBIROGGFAOTB3Oqb0+Rm" crossorigin="anonymous">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    </head>
    <body>
        <h1 class="text-center my-4">2FA-PGP</h1>
        <?php echo $msg; ?>
        <div class="container">
            <div class="mb-3">
                <label for="pgp-msg" class="form-label">Encrypted Code:</label>
                <textarea rows="15" class="form-control" id="pgp-msg" name="pgp-msg" readonly><?php echo htmlspecialchars($pgpmessage, ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>
            <!-- WARNING: In production, always include a CSRF token in your forms. -->
            <form class="form" action="pgp.php" method="post">
                <div class="mb-3">
                    <label for="user-input" class="form-label">Decrypted Code:</label>
                    <input type="text" id="user-input" name="user-input" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary w-100">Check!</button>
            </form>
        </div>
    </body>
</html>