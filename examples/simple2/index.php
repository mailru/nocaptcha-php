<?php

// your public and private keys from nocaptcha.mail.ru
// for nocaptcha.example.com
define('PUBLIC_KEY', 'ed64110f3e3ef4c2aad78446fdfe63a5');
define('PRIVATE_KEY', 'da9d56871519a43eba63a9a394f8fd53');

// import nocaptcha module
require_once('../../src/Nocaptcha.php');

use Mailru\Nocaptcha;

$nc = new Mailru\Nocaptcha(PUBLIC_KEY, PRIVATE_KEY);

// helper to separate code from templates
function expand_template($file, $vars = NULL)
{
    if ($vars !== NULL)
        extract($vars, EXTR_OVERWRITE);
    include $file;
}

$vars = array();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $result = $nc->checkRequest();
    if ($result === true) {
        // do form processing here
        $result = 'ok';
    } else {
        // inform user about error
        if ($result === false) {
            $result = 'invalid captcha value';
        }
    }
    $vars['result'] = $result;
} else {
    // simply display the form
    $vars['form'] = true;
    $vars['nocaptcha_script'] = $nc->generateScriptTag();
}
expand_template('template.html', $vars);
