<?php

session_start();

include('pgp-2fa.php');

$pgp = new pgp_2fa();



$msg = '';

if($_SERVER['REQUEST_METHOD'] == 'POST' and !isset($_POST['pgp-key'])){
    
    if($pgp->compare($_POST['user-input'])){
        $msg = '<div class="alert alert-success">Success!</div>';
    }else{
        $msg = '<div class="alert alert-danger">Fail!</div>';
    }
    
    
} else {
    $pgp->generateSecret();
    $pgpmessage = $pgp->encryptSecret($_POST['pgp-key']);
}



?>
<!DOCTYPE>
<html>
    <head>
        <title>2FA-PGP</title>
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootswatch/3.3.5/flatly/bootstrap.min.css">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    </head>
    <body>
        <h1 class="text-center">2FA-PGP</h1>
        <?php echo $msg ?>
        <div class="container">
            <label for="pgp-key">Encrypted Code:</label>
            <textarea rows="15" class="form-control" name="pgp-msg"><?php echo $pgpmessage ?></textarea>
            <form class="form" action="pgp.php" method="post">    
                
                <label for="user-input">Decrypted Code:</label>
                <input type="text" name="user-input" class="form-control">
                <br/>
                <button class="btn btn-primary form-control">Check!</button>
            </form>
        </div>
        <h6 class="text-center">This awesome theme is called <a href="//bootswatch.com/flatly">'Flatly'</a> and was made by <a href="//bootswatch.com/">Bootswatch.com</a>!</h6>
    </body>
</html>