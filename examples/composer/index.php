<?php

// usual autoload thingie from Composer
require __DIR__.'/../../vendor/autoload.php';

// add here your public and private keys from nocaptcha.mail.ru
define('NOCAPTCHA_PUBLIC_KEY', 'ed64110f3e3ef4c2aad78446fdfe63a5');
define('NOCAPTCHA_PRIVATE_KEY', 'da9d56871519a43eba63a9a394f8fd53');

// these above are valid only for nocaptcha.example.com
if (gethostbyname('nocaptcha.example.com') != '127.0.0.1') {
    echo "Add to your <tt>hosts</tt> file:<pre>127.0.0.1 nocaptcha.example.com</pre>";
    $myUrl = 'http://nocaptcha.example.com:'.$_SERVER['SERVER_PORT'].'/';
    echo "Then proceed to <a href=$myUrl>$myUrl</a>";
    return;
}

mb_internal_encoding('utf-8');
date_default_timezone_set("UTC");

// enable logging of JSON responses
NoCaptcha\Config::$logger = function ($json) {
    trigger_error("JSON: $json");
};

// use default keys and URI
$nocaptcha = new NoCaptcha\NoCaptcha();

if (!empty($_POST)) {
    // could be null if there are no $_POST['captcha_id'] or $_POST['captcha_value']
    $result = $nocaptcha->verifyRequest($_POST);
}

?><!DOCTYPE html>
<html>
    <head>
        <script src="<?=$nocaptcha->getScriptSrc()?>"></script>
    </head>
    <body>
        <form method="POST">
            <p><div id="nocaptcha"></div></p>
            <p><input type="submit" value="Verify"></p>
        </form>
        <?php if ($result instanceof NoCaptcha\Response): ?>
        <p><?=$result->isCorrect() ? 'Passed' : 'Failed'?></p>
        <pre><?=$result?></pre>
        <?php endif; ?>
    </body>
</html>

