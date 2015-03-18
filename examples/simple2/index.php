<?php

// your public and private keys from nocaptcha.mail.ru
define('PUBLIC_KEY', 'ed64110f3e3ef4c2aad78446fdfe63a5');
define('PRIVATE_KEY', 'da9d56871519a43eba63a9a394f8fd53');

// import nocaptcha module
require_once('../../Package/nocaptcha/captcha.php');

// helper to separate code from templates
function expand_template($file, $vars = NULL)
{
    if ($vars !== NULL)
        extract($vars, EXTR_OVERWRITE);
    include $file;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $result = check_captcha(PRIVATE_KEY, $_POST['captcha_id'],
                            $_POST['captcha_value']);
    if ($result === true) {
        // do form processing here
        $result = 'ok';
    } else {
        // inform user about error
    }
    $vars['result'] = $result;
} else {
    // simply display the form
    $vars['form'] = true;
    $vars['nocaptcha_script'] = display_captcha(PUBLIC_KEY);
}
expand_template('template.html', $vars);
