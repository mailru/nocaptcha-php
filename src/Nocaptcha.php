<?php
namespace Mailru;

class Nocaptcha
{
    const API_SERVER = 'https://api-nocaptcha.mail.ru';

    private $public_key;
    private $private_key;

    /**
     * Constructs class instance and initializes it with the key pair
     * corresponded to the registered domain.
     *
     * @param string $public_key Public key.
     * @param string $private_key Private key.
     */
    public function __construct($public_key, $private_key)
    {
        $this->public_key = $public_key;
        $this->private_key = $private_key;
    }

    /**
     * Checks the CAPTCHA value entered by the user. Use it in a form handler
     * to verify user is a human.
     *
     * @param string $captcha_id CAPTCHA ID from 'captcha_id' request parameter.
     * @param string $captcha_value CAPTCHA value from 'captcha_value' request parameter.
     * @return bool|string Result of the check or error message.
     */
    public function check($captcha_id, $captcha_value)
    {
        $params = array(
            'private_key'   => $this->private_key,
            'captcha_id'    => $captcha_id,
            'captcha_value' => $captcha_value,
        );
        $url = self::API_SERVER . '/check?' . http_build_query($params);

        $resp = @file_get_contents($url);
        if ($resp === false) {
            return 'request failed';
        }
        $data = json_decode($resp);
        if (!$data) {
            return 'invalid response';
        }
        if ($data->status !== 'ok') {
            return 'service returned an error: ' . $data->desc;
        }
        if (!$data->is_correct) {
            return false;
        }
        return true;
    }

    /**
     * Checks the CAPTCHA value entered by the user. Use it in a form handler
     * to verify user is a human. Obtains data from _REQUEST array.
     *
     * @return bool|string Result of the check or error message.
     */
    public function checkRequest()
    {
        return $this->check($_REQUEST['captcha_id'], $_REQUEST['captcha_value']);
    }

    /**
     * Generate URL of the Nocaptcha script.
     *
     * @return string URL of the script.
     */
    public function generateScriptUrl()
    {
        $params = array('public_key' => $this->public_key);
        return self::API_SERVER . '/captcha?' . http_build_query($params);
    }

    /**
     * Generate <script> HTML tag for Nocaptcha script.
     *
     * @return string <script> tag.
     */
    public function generateScriptTag()
    {
        return '<script type="text/javascript" src="' .
            $this->generateScriptUrl() . '"></script>';
    }
}
