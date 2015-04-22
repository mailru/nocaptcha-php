<?php
/*
 * © 2015 Alexey Kopytko <alexey@kopytko.ru>
 * Distributed under the MIT License.
 */

namespace NoCaptcha;

use NoCaptcha\Config;
use NoCaptcha\Response;
use NoCaptcha\Exception\ResponseException;

class NoCaptcha
{
    private $config;

    public function __construct(Config $config = null)
    {
        $this->config = $config ?: new Config();
    }

    public function getScriptSrc()
    {
        return $this->config->getScriptSrc().'?'.http_build_query([
            'public_key'=> $this->config->getPublicKey()
        ]);
    }

    public function getVerifyUrl($captcha_id, $captcha_value)
    {
        return $this->config->getAPIEndpoint().'?'.http_build_query([
            'private_key'=> $this->config->getPrivateKey(),
            'captcha_id' => $captcha_id,
            'captcha_value' => $captcha_value,
        ]);
    }

    /**
     * Получим результат проверки используя $_REQUEST (или $_POST)
     * @param array $requestArray
     * @param callable $onError
     * @return \NoCaptcha\Response|null
     */
    public function verifyRequest($requestArray, callable $onError = null)
    {
        if (!isset($requestArray['captcha_id']) || !isset($requestArray['captcha_value'])) {
            $onError && call_user_func($onError);
            return null;
        }

        return $this->verify($requestArray['captcha_id'], $requestArray['captcha_value']);
    }

    /**
     * Получим результат проверки указав параметры явно
     * @param string $captcha_id
     * @param string $captcha_value
     * @throws \NoCaptcha\Exception\ResponseException
     * @return \NoCaptcha\Response
     */
    public function verify($captcha_id, $captcha_value)
    {
        if (!$JSON = file_get_contents($this->getVerifyUrl($captcha_id, $captcha_value))) {
            throw new ResponseException("Server unavailable");
        }

        $this->config->logDebug($JSON);

        return new Response(json_decode($JSON));
    }
}