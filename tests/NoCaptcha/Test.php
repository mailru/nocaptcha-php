<?php
/*
 * © 2015 Alexey Kopytko <alexey@kopytko.ru>
 * Distributed under the MIT License.
 */


namespace NoCaptcha;

class Test extends \PHPUnit_Framework_TestCase
{
    public function testCorrect()
    {
        // Пример успешного ответа, верное значение captcha_value
        $response = new Response(json_decode('{"status": "ok", "is_correct": true}'));
        $this->assertTrue($response->isCorrect());
        return $response;
    }

    /**
     * @depends testCorrect
     */
    public function testNotTrow(Response $response)
    {
        $response = $response->throwErrors();
        $this->assertTrue($response instanceof Response);
    }

    public function testIncorrect()
    {
        // Пример успешного ответа, неверное значение captcha_value
        $response = new Response(json_decode('{"status": "ok", "is_correct": false}'));
        $this->assertFalse($response->isCorrect());
        return $response;
    }

    /**
     * @depends testIncorrect
     * @expectedException NoCaptcha\Exception\CaptchaIncorrectException
     */
    public function testTrow(Response $response)
    {
        $response->throwErrors();
    }

    /**
     * @expectedException \NoCaptcha\Exception\BadRequestException
     * @expectedExceptionCode 1745
     */
    public function testBadRequestException()
    {
        // Пример ответа с ошибкой
        new Response(json_decode('{"status": "bad request", "desc": "parameter not found", "code": 1745}'));
    }

    /**
     * @expectedException \NoCaptcha\Exception\ResponseException
     * @expectedExceptionCode 500
     */
    public function testInternalError()
    {
        new Response(json_decode('{"status": "internal error", "desc": "server busy", "code": 500}'));
    }

    public function testInvalidID()
    {
        $response = new Response(json_decode('{"status":"not found","desc":"captcha ID not found","code":1016}'));
        $this->assertFalse($response->isCorrect());
    }

    /**
     * @expectedException NoCaptcha\Exception
     */
    public function testUnknownStatus()
    {
        new Response(json_decode('{"status":"untitled"}'));
    }

    /**
     * @expectedException \NoCaptcha\Exception
     */
    public function testMissingConstants()
    {
        $this->assertTrue(!defined(Config::PUBLIC_KEY_CONSTANT_NAME));
        $this->assertTrue(!defined(Config::PRIVATE_KEY_CONSTANT_NAME));
        new Config();
    }

    const PUBLIC_KEY = 'public-key-for-testing';
    const PRIVATE_KEY = 'private-key-for-testing';

    public function testConfig()
    {
        $config = new Config(self::PUBLIC_KEY, self::PRIVATE_KEY);
        $config->getPrivateKey() == self::PRIVATE_KEY;
        $config->getPublicKey() == self::PUBLIC_KEY;

        return $config;
    }

    /**
     * @depends testConfig
     */
    public function testDefaultConfig(Config $config)
    {
        // если будет возможность указывать произвольные - тест нужно будет заменить
        $config->getAPIEndpoint() == $config::API_ENDPOINT;
        $config->getScriptSrc() == $config::SCRIPT_SOURCE;
    }

    /**
     * @group not-applicable-yet
     */
    public function testCustomConfig()
    {
        $this->fail();
    }

    /**
     * @depends testConfig
     */
    public function testLogger(Config $config)
    {
        $config::$logger = function ($json) use (&$actual) {
            $actual = $json;
        };

        $config->logDebug('{}');
        $this->assertEquals('{}', $actual);
    }

    /**
     * @depends testConfig
     */
    public function testScriptSrc(Config $config)
    {
        $nocaptcha = new NoCaptcha($config);
        $nocaptcha->getScriptSrc() == 'https://api-nocaptcha.mail.ru/captcha?public_key=public-key-for-testing';
        $nocaptcha->getVerifyUrl('ID', 'VAL') == 'https://api-nocaptcha.mail.ru/check?private_key=private-key-for-testing&captcha_id=ID&captcha_value=VAL';
    }

    /**
     * @depends testConfig
     */
    public function testInvalidRequest(Config $config)
    {
        $nocaptcha = new NoCaptcha($config);
        $result = $nocaptcha->verifyRequest([], function () use (&$errorFound) {
            $errorFound = true;
        });

        $this->assertTrue($errorFound);
        $this->assertNull($result);
    }

}

