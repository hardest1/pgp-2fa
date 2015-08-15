<?php session_start(); ?>
<!DOCTYPE>
<html>
    <head>
        <title>2FA-PGP</title>
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootswatch/3.3.5/flatly/bootstrap.min.css">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    </head>
    <body>
        <h1 class="text-center">2FA-PGP</h1>
        
        <div class="container">
            <form class="form" action="pgp.php" method="post">
                <label for="pgp-key">Your Public Key:</label>
                <textarea rows="20" class="form-control" name="pgp-key"></textarea>
                <br/>
                <button class="btn btn-primary form-control">Submit!</button>
            </form>
        </div>
        <h6 class="text-center">This awesome theme is called <a href="//bootswatch.com/flatly">'Flatly'</a> and was made by <a href="//bootswatch.com/">Bootswatch.com</a>!</h6>
    </body>
</html>