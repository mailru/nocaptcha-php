<?php

define('API_SERVER', 'https://api-nocaptcha.mail.ru');


/**
 * Helper to encode querystring as array to string
 * @param $data
 * @return string
 */
function qsencode($data) {
    $req = array();
    foreach ($data as $key => $value) $req[] = $key.'='.urlencode(stripslashes($value));
    $req = (count($req)) ? '?'.join('&', $req) : '';
    return $req;
}


/**
 * @param $private_key
 * @param $captcha_id
 * @param $captcha_value
 * @param string $api_server
 * @return bool|string
 */
function check_captcha($private_key, $captcha_id, $captcha_value, $api_server='') {
    $api_server = ($api_server) ? $api_server : API_SERVER;
    $params = array(
        'private_key' => $private_key,
        'captcha_id' => $captcha_id,
        'captcha_value' => $captcha_value,
    );

    $url = $api_server.'/check'.qsencode($params);
    $response = @file_get_contents($url);
    $data = json_decode($response);

    //echo $url;
    //echo $response;
    //print_r($data);

    if(!$response) {
        $result = 'Server unavailable';
    }
    elseif(!$data) {
        $result = 'Bad response';
    }
    elseif($data->status != 'ok') {
        $result = $data->desc;
    }
    elseif(!$data->is_correct) {
        $result = 'Bad captcha';
    }
    else {
        $result = true;
    }

    return $result;
}


/**
 * @param $public_key
 * @param string $api_server
 * @return string
 */
function source_captcha($public_key, $api_server='') {
    $api_server = ($api_server) ? $api_server : API_SERVER;
    $params = array(
        'public_key' => $public_key,
    );

    $url = $api_server.'/captcha'.qsencode($params);
    return $url;
}


/**
 * @param $public_key
 * @param string $api_server
 * @return string
 */
function display_captcha($public_key, $api_server='') {
    return '<script src="'.source_captcha($public_key, $api_server).'" type="text/javascript"></script>';
}
