<?php

/*

php -S 127.0.0.1:8080 router.php

*/

$api_server = 'https://api-nocaptcha.mail.ru';

// keys for localsite.loc hostname
$public_key = '8dd9f759e0bf146a9a13206df6feadfe';
$private_key = 'dca7c260125df4b6ab29b25811cb32bc';

require_once('../../src/Nocaptcha.php');

use Mailru\Nocaptcha;

$nc = new Mailru\Nocaptcha($public_key, $private_key);

?>
<!DOCTYPE html>
<html>
    <head>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
    <meta http-equiv="Content-Language" content="ru" />

    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.js"></script>

    <!-- Three ways to include js to display captcha widget
    <!--<script src="<?=$api_server?>/captcha?public_key=<?=$public_key?>" type="text/javascript"></script>-->
    <script src="<?=$nc->generateScriptUrl()?>" type="text/javascript"></script>
    <!--<?=$nc->generateScriptTag()?>-->

    <script type="text/javascript">
        $(function(){
            var nr = $('#nocaptcha_event_result'),
                sr = $('#nocaptcha_script_result');

            $('#nocaptcha').on('nocaptcha', function(ev) {
                var is_verified = ev.originalEvent.detail.is_verified;
                nr.text((is_verified) ? 'verified': 'not verified');
                sr.text(true);
            });
        });
    </script>
    <style>
        body {
            width: 40em;
        }
        form .error {
            color: red;
        }
    </style>
    </head>
<body>


<?php
if($_POST) {
    $captcha = $nc->check($_POST['captcha_id'], $_POST['captcha_value']);
}

if(isset($captcha) && $captcha === true):
    header('Refresh: 10;url=/');
?>


<h1>Спасибо</h1>
<p>А тут мы такие отправляем кому-то письмо с данными пользователя «<?=$_POST['name']?>» и «<?=$_POST['email']?>», мухахаха!</p>


<?php else: ?>


<h1>Библиотекой через боевой сервер</h1>

<p>Сервер: <?=$api_server?></p>
<p>Публичный ключ: <?=$public_key?></p>
<p>Приватный ключ: <?=$private_key?></p>
<p>Клиентский скрипт: <span id="nocaptcha_script_result">false</span></p>
<p>Событие nocaptcha: <span id="nocaptcha_event_result">-</span></p>

<form action="" method="post">
    <fieldset>
    <legend>Форма</legend>

    <p><label for="id_name">Имя:</label> <input id="id_name" name="name" type="text" value="<?=$_POST['name']?>" /></p>
    <p><label for="id_email">Электронная почта:</label> <input id="id_email" name="email" type="email" value="<?=$_POST['email']?>" /></p>
    <?php if($_POST): ?>
    <p class="error"><?=$captcha?></p>
    <?php endif ?>
    <div id="nocaptcha"></div>
    <button type="submit">Отправить</button>
    </fieldset>
</form>

<h2>phpinfo</h2>
<?php phpinfo(); ?>


<?php endif ?>


</body>
</html>
