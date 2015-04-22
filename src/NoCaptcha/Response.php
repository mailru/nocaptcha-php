<?php
/*
 * © 2015 Alexey Kopytko <alexey@kopytko.ru>
 * Distributed under the MIT License.
 */

namespace NoCaptcha;

use NoCaptcha\Exception\ResponseException;
use NoCaptcha\Exception\BadRequestException;
use NoCaptcha\Exception\CaptchaIncorrectException;

class Response
{
    const STATUS_OK = 'ok';
    const STATUS_BAD_REQUEST = 'bad request';
    const STATUS_INTERNAL_ERROR = 'internal error';
    const STATUS_NOT_FOUND = 'not found';

    private $response;

    /** Умолчальные значения ответа чтобы не проверять ключи везде */
    private static $default = [
        'status'        => null,
        'is_correct'    => null,
        'desc'          => null,
        'code'          => null
    ];

    /**
     * Декодированный JSON в виде массива или объекта
     * @param array $json_array
     */
    public function __construct($json_array_or_object)
    {
        // так будет работать автодополнение в IDE
        $this->response = new ResponseFiller();
        // все поля объекта всегда присутствуют, для удобства
        $this->response = (object) ((array) $json_array_or_object + (array) $this->response); // TODO нужны ли скобки здесь?

        // ошибочный запрос всегда вызывает исключение - это ошибка разработчика или он о ней должен знать
        if ($this->response->status != self::STATUS_OK) {
            switch ($this->response->status) {
                case self::STATUS_NOT_FOUND:
                    // штатное поведение - нужно показать капчю ещё раз; без исключений
                    $this->response->is_correct = false;
                    break;
                case self::STATUS_BAD_REQUEST:
                    throw new BadRequestException($this->response->desc, $this->response->code);
                case self::STATUS_INTERNAL_ERROR:
                    throw new ResponseException($this->response->desc, $this->response->code);
                default:
                    throw new Exception("Unknown status: {$this->response->status}");
            }
        }
    }

    public function isCorrect()
    {
        // явно кастим так как могло прилететь что-то другое
        return (bool) $this->response->is_correct;
    }

    public function throwErrors()
    {
        if (!$this->response->is_correct) {
            throw new CaptchaIncorrectException();
        }

        return $this;
    }

    /** В целях отладки */
    public function __toString()
    {
        return http_build_query($this->response);
    }
}

/**
 * Для работы автодоплнения
 */
class ResponseFiller
{
    public $status;
    public $is_correct = false;
    public $desc;
    public $code;
}

