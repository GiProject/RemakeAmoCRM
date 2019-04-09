<?php

namespace RemakeAmoCRM\Request;

use RemakeAmoCRM\Exception;
use RemakeAmoCRM\NetworkException;


/**
 * Class Request
 *
 * Класс отправляющий запросы к API amoCRM используя cURL
 *
 * @package AmoCRM\Request
 * @author dotzero <mail@dotzero.ru>
 * @link http://www.dotzero.ru/
 * @link https://github.com/dotzero/amocrm-php
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Request
{
    /**
     * @var bool Использовать устаревшую схему авторизации
     */
    protected $v1 = false;

    /**
     * @var bool Флаг вывода отладочной информации
     */
    private $debug = false;

    /**
     * @var ParamsBag|null Экземпляр ParamsBag для хранения аргументов
     */
    private $parameters = null;

    /**
     * @var int|null Последний полученный HTTP код
     */
    private $lastHttpCode = null;

    /**
     * @var string|null Последний полученный HTTP ответ
     */
    private $lastHttpResponse = null;
  
    //private $pdo;

    /**
     * Request constructor
     *
     * @param ParamsBag $parameters Экземпляр ParamsBag для хранения аргументов
     * @throws NetworkException
     */
    public function __construct(ParamsBag $parameters)
    {
        if (!function_exists('curl_init')) {
            throw new NetworkException('The cURL PHP extension was not loaded');
        }

        $this->parameters = $parameters;
    }

    /**
     * Установка флага вывода отладочной информации
     *
     * @param bool $flag Значение флага
     * @return $this
     */
    public function debug($flag = false)
    {
        $this->debug = (bool)$flag;

        return $this;
    }

    /**
     * Возвращает последний полученный HTTP код
     *
     * @return int|null
     */
    public function getLastHttpCode()
    {
        return $this->lastHttpCode;
    }

    /**
     * Возвращает последний полученный HTTP ответ
     *
     * @return null|string
     */
    public function getLastHttpResponse()
    {
        return $this->lastHttpResponse;
    }

    /**
     * Возвращает экземпляр ParamsBag для хранения аргументов
     *
     * @return ParamsBag|null
     */
    protected function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Выполнить HTTP GET запрос и вернуть тело ответа
     *
     * @param string $url Запрашиваемый URL
     * @param array $parameters Список GET параметров
     * @param null|string $modified Значение заголовка IF-MODIFIED-SINCE
     * @return mixed
     * @throws Exception
     * @throws NetworkException
     */
    protected function getRequest($url, $parameters = [], $modified = null)
    {
        if (!empty($parameters)) {
            $this->parameters->addGet($parameters);
        }

        return $this->request($url, $modified);
    }

    /**
     * Выполнить HTTP POST запрос и вернуть тело ответа
     *
     * @param string $url Запрашиваемый URL
     * @param array $parameters Список POST параметров
     * @return mixed
     * @throws Exception
     * @throws NetworkException
     */
    protected function postRequest($url, $parameters = [])
    {
        if (!empty($parameters)) {
            $this->parameters->addPost($parameters);
        }

        return $this->request($url);
    }

    /**
     * Подготавливает список заголовков HTTP
     *
     * @param mixed $modified Значение заголовка IF-MODIFIED-SINCE
     * @return array
     */
    protected function prepareHeaders($modified = null)
    {
        $headers = ['Content-Type: application/json'];

        if ($modified !== null) {
            if (is_int($modified)) {
                $headers[] = 'IF-MODIFIED-SINCE: ' . $modified;
            } else {
                $headers[] = 'IF-MODIFIED-SINCE: ' . (new \DateTime($modified))->format(\DateTime::RFC1123);
            }
        }

        return $headers;
    }

    /**
     * Подготавливает URL для HTTP запроса
     *
     * @param string $url Запрашиваемый URL
     * @return string
     */
    protected function prepareEndpoint($url)
    {
        if ($this->v1 === false) {
            $query = http_build_query(array_merge($this->parameters->getGet(), [
                'USER_LOGIN' => $this->parameters->getAuth('login'),
                'USER_HASH' => $this->parameters->getAuth('apikey'),
            ]), null, '&');
        } else {
            $query = http_build_query(array_merge($this->parameters->getGet(), [
                'login' => $this->parameters->getAuth('login'),
                'api_key' => $this->parameters->getAuth('apikey'),
            ]), null, '&');
        }

        return sprintf('https://%s%s?%s', $this->parameters->getAuth('domain'), $url, $query);
    }

    /**
     * Выполнить HTTP запрос и вернуть тело ответа
     *
     * @param string $url Запрашиваемый URL
     * @param null|string $modified Значение заголовка IF-MODIFIED-SINCE
     * @return mixed
     * @throws Exception
     * @throws NetworkException
     */
    protected function request($url, $modified = null)
    {
      
//        $last_request = $this->read_last_request();
//        $last_request = $last_request['time'];
//
//        $diff_time = round(((double)doubleTime() - (double)$last_request) * 1000)/1000;
//
//        $sleep_time = (1 - $diff_time) * 1000000;
//
//        if($sleep_time > 0) usleep($sleep_time);
//
//        $this->write_last_request(doubleTime());
      
      
        $cookie = $_SERVER['DOCUMENT_ROOT'].'/vendor/dotzero/amocrm/cookie/'.$this->parameters->getAuth('domain').'_cookie.txt';

        $headers = $this->prepareHeaders($modified);
        $endpoint = $this->prepareEndpoint($url);

        $this->printDebug('url', $endpoint);
        $this->printDebug('headers', $headers);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ($this->parameters->hasPost()) {
            $fields = json_encode([
                'request' => $this->parameters->getPost(),
            ]);

            $ex = [
                '/api/v2/leads',
                '/api/v2/calls'
            ];

            if( in_array($url,$ex,false) ){
                $params = json_decode($fields,1);
                $rParams = [];
                if( isset( $params['request'] ) ){
                    foreach ( $params['request'] as $key => $param){
                        $rParams[$key] = $param;
                    }
                }
                $fields = json_encode( $rParams, 1);
            }
          
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
            $this->printDebug('post params', $fields);
          
        }

        if ($this->parameters->hasProxy()) {
            curl_setopt($ch, CURLOPT_PROXY, $this->parameters->getProxy());
        }
        
        $result = curl_exec($ch);
        $info = curl_getinfo($ch);
        $error = curl_error($ch);
        $errno = curl_errno($ch);
        
        curl_close($ch);

        $this->lastHttpCode = $info['http_code'];
        $this->lastHttpResponse = $result;

        $this->printDebug('curl_exec', $result);
        $this->printDebug('curl_getinfo', $info);
        $this->printDebug('curl_error', $error);
        $this->printDebug('curl_errno', $errno);

        if ($result === false && !empty($error)) {
            if( (int)$errno === 56 ){
              $this->request($url, $modified);
              return false;
            }else{
              throw new NetworkException($error, $errno);
            }
        }
      
        if(intval($info['http_code']) == 429 || intval($info['http_code']) == 403){
          sleep(1);
          $this->request($url, $modified);
        }
      
        return $this->parseResponse($result, $info);

    }

    /**
     * Парсит HTTP ответ, проверяет на наличие ошибок и возвращает тело ответа
     *
     * @param string $response HTTP ответ
     * @param array $info Результат функции curl_getinfo
     * @return mixed
     * @throws Exception
     */
    protected function parseResponse($response, $info)
    {
        $result = json_decode($response, true);

        if (floor($info['http_code'] / 100) >= 3) {
            if (isset($result['response']['error_code']) && $result['response']['error_code'] > 0) {
                $code = $result['response']['error_code'];
            } elseif ($result !== null) {
                $code = 0;
            } else {
                $code = $info['http_code'];
            }
            if ($this->v1 === false && isset($result['response']['error'])) {
                throw new Exception($result['response']['error'], $code);
            } elseif (isset($result['response'])) {
                throw new Exception(json_encode($result['response']));
            } else {
                throw new Exception('Invalid response body.', $code);
            }
        } elseif ( !isset($result['response']) && !isset($result['_embedded']) ) {
            return false;
        }

        if( isset( $result['response'] ) ){
            return $result['response'];
        }elseif( isset($result['_embedded']) ){
            return $result['_embedded'];
        }else{
            return null;
        }


    }

    /**
     * Вывода отладочной информации
     *
     * @param string $key Заголовок отладочной информации
     * @param mixed $value Значение отладочной информации
     * @param bool $return Возврат строки вместо вывода
     * @return mixed
     */
    protected function printDebug($key = '', $value = null, $return = false)
    {
        if ($this->debug !== true) {
            return false;
        }

        if (!is_string($value)) {
            $value = print_r($value, true);
        }

        $line = sprintf('[DEBUG] %s: %s', $key, $value);

        if ($return === false) {
            return print_r($line . PHP_EOL);
        }

        return $line;
    }

}
