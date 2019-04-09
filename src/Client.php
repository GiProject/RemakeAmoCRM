<?php

namespace RemakeAmoCRM;

use RemakeAmoCRM\Models\ModelInterface;
use RemakeAmoCRM\Request\CurlHandle;
use RemakeAmoCRM\Request\ParamsBag;
use RemakeAmoCRM\Helpers\Fields;
use RemakeAmoCRM\Helpers\Format;

/**
 * Class Client
 *
 * Основной класс для получения доступа к моделям amoCRM API
 *
 * @package RemakeAmoCRM
 * @author dotzero <mail@dotzero.ru>
 * @link http://www.dotzero.ru/
 * @link https://github.com/dotzero/amocrm-php
 * @property \RemakeAmoCRM\Models\Account $account
 * @property \RemakeAmoCRM\Models\Call $call
 * @property \RemakeAmoCRM\Models\Catalog $catalog
 * @property \RemakeAmoCRM\Models\CatalogElement $catalog_element
 * @property \RemakeAmoCRM\Models\Company $company
 * @property \RemakeAmoCRM\Models\Contact $contact
 * @property \RemakeAmoCRM\Models\Customer $customer
 * @property \RemakeAmoCRM\Models\CustomersPeriods $customers_periods
 * @property \RemakeAmoCRM\Models\CustomField $custom_field
 * @property \RemakeAmoCRM\Models\Lead $lead
 * @property \RemakeAmoCRM\Models\Links $links
 * @property \RemakeAmoCRM\Models\Note $note
 * @property \RemakeAmoCRM\Models\Pipelines $pipelines
 * @property \RemakeAmoCRM\Models\Task $task
 * @property \RemakeAmoCRM\Models\Transaction $transaction
 * @property \RemakeAmoCRM\Models\Unsorted $unsorted
 * @property \RemakeAmoCRM\Models\Webhooks $webhooks
 * @property \RemakeAmoCRM\Models\Widgets $widgets
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Client
{
    /**
     * @var Fields|null Экземпляр Fields для хранения номеров полей
     */
    public $fields = null;

    /**
     * @var ParamsBag|null Экземпляр ParamsBag для хранения аргументов
     */
    public $parameters = null;

    /**
     * @var CurlHandle Экземпляр CurlHandle для повторного использования
     */
    private $curlHandle;

    /**
     * Client constructor
     *
     * @param string $domain Поддомен или домен RemakeAmoCRM
     * @param string $login Логин RemakeAmoCRM
     * @param string $apikey Ключ пользователя RemakeAmoCRM
     * @param string|null $proxy Прокси сервер для отправки запроса
     */
    public function __construct($domain, $login, $apikey, $proxy = null)
    {
        // Разернуть поддомен в полный домен
        if (strpos($domain, '.') === false) {
            $domain = sprintf('%s.amocrm.ru', $domain);
        }

        $this->parameters = new ParamsBag();
        $this->parameters->addAuth('domain', $domain);
        $this->parameters->addAuth('login', $login);
        $this->parameters->addAuth('apikey', $apikey);

        if ($proxy !== null) {
            $this->parameters->addProxy($proxy);
        }

        $this->fields = new Fields();

        $this->curlHandle = new CurlHandle();
    }

    /**
     * Возвращает экземпляр модели для работы с amoCRM API
     *
     * @param string $name Название модели
     * @return ModelInterface
     * @throws ModelException
     */
    public function __get($name)
    {
        $classname = '\\RemakeAmoCRM\\Models\\' . Format::camelCase($name);

        if (!class_exists($classname)) {
            throw new ModelException('Model not exists: ' . $name);
        }

        // Чистим GET и POST от предыдущих вызовов
        $this->parameters->clearGet()->clearPost();

        return new $classname($this->parameters, $this->curlHandle);
    }
}
