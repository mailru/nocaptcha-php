<?php
/*
 * © 2015 Alexey Kopytko <alexey@kopytko.ru>
 * Distributed under the MIT License.
 */


namespace NoCaptcha;
use \NoCaptcha\Exception;

class Config
{
    const PUBLIC_KEY_CONSTANT_NAME = 'NOCAPTCHA_PUBLIC_KEY';
    const PRIVATE_KEY_CONSTANT_NAME = 'NOCAPTCHA_PRIVATE_KEY';

    const API_ENDPOINT = 'https://api-nocaptcha.mail.ru/check';
    const SCRIPT_SOURCE = 'https://api-nocaptcha.mail.ru/captcha';

    /**
     * Публичный ключ для использования в JavaScript
     * @var string
     */
    private $publicKey;

    /**
     * Приватный (секретный) ключ для обмена данными с сервисом
     * @var string
     */
    private $privateKey;

    /**
     * Куда отправлять запросы на проверку
     * @var string
     */
    private $apiEndpoint = self::API_ENDPOINT;

    /**
     * Откуда брать JS для клиента
     * @var string
     */
    private $scriptSource = self::SCRIPT_SOURCE;

    /**
     * Функция, которая вызывается с голым JSON при каждом запросе
     * @var callable
     */
    public static $logger = null;

    public function __construct($publicKey = null, $privateKey = null)
    {
        $this->publicKey = is_null($publicKey) && defined(self::PUBLIC_KEY_CONSTANT_NAME) ? constant(self::PUBLIC_KEY_CONSTANT_NAME) : $publicKey;
        $this->privateKey = is_null($privateKey) && defined(self::PRIVATE_KEY_CONSTANT_NAME) ? constant(self::PRIVATE_KEY_CONSTANT_NAME) : $privateKey;

        if (!$this->publicKey || !$this->privateKey) {
            throw new Exception(sprintf("Define the following constants with your keys to make the defaults work: %s and %s", self::PUBLIC_KEY_CONSTANT_NAME, self::PRIVATE_KEY_CONSTANT_NAME));
        }
    }

    public function getPublicKey()
    {
        return $this->publicKey;
    }

    public function getPrivateKey()
    {
        return $this->privateKey;
    }

    public function getAPIEndpoint()
    {
        return $this->apiEndpoint;
    }

    public function getScriptSrc()
    {
        return $this->scriptSource;
    }

    public function logDebug($JSON)
    {
        if (self::$logger) {
            call_user_func(self::$logger, $JSON);
        }
    }
}
