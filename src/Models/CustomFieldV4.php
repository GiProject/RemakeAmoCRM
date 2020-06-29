<?php

namespace RemakeAmoCRM\Models;

use AmoCRM\Exception;
use AmoCRM\NetworkException;

/**
 * Class CustomField
 *
 * Класс модель для работы с Дополнительными полями через API v4
 *
 * @package AmoCRM\Models
 * @author mihasichechek <mihasichechek@gmail.com>
 * @link https://github.com/dotzero/amocrm-php
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class CustomFieldV4 extends AbstractModel
{
    /**
     * @var array Список доступный полей для модели (исключая кастомные поля)
     */
    protected $fields = [
        'name',
        'type',
        'sort',
        'required_statuses',
        'enums',
        'group_id',
        'is_api_only',
    ];

    public const TYPE_TEXT = 'text';
    public const TYPE_NUMERIC = 'numeric';
    public const TYPE_CHECKBOX = 'checkbox';
    public const TYPE_SELECT = 'select';
    public const TYPE_MULTISELECT = 'multiselect';
    public const TYPE_DATE = 'date';
    public const TYPE_URL = 'url';
    public const TYPE_RADIOBUTTON = 'radiobutton';
    public const TYPE_TEXTAREA = 'textarea';
    public const TYPE_STREETADDRESS = 'streetaddress';
    public const TYPE_SMARTADDRESS = 'smart_address';
    public const TYPE_BIRTHDAY = 'birthday';
    public const TYPE_LEGALENTITY = 'legal_entity';
    public const TYPE_DATETIME = 'date_time';

    /**
     * @const string Типа сущности Контакт
     */
    public const ELEMENT_CONTACT = 'contacts';

    /**
     * @const string Типа сущности Сделка
     */
    public const ELEMENT_LEAD = 'leads';

    /**
     * @const string Типа сущности Компания
     */
    public const ELEMENT_COMPANY = 'companies';

    /**
     * @const string Типа сущности Customer
     */
    public const ELEMENT_CUSTOMER = 'customers';

    /**
     * Добавление дополнительных полей
     *
     * Метод позволяет добавлять дополнительные поля по одному или пакетно
     *
     * @param $fields array Массив объектов CustomFieldV4
     * @param $elementType string Тип сущности, см. константы ELEMENT_*
     * @return int|array Уникальный идентификатор поля или массив при пакетном добавлении
     * @throws Exception
     * @throws NetworkException
     */
    public function apiAdd($fields = [], $elementType)
    {
        if (empty($fields)) {
            $fields = [$this];
        }

        $parameters = [];

        foreach ($fields AS $field) {
            $parameters[] = $field->getValues();
        }

        $response = $this->postRequest("/api/v4/$elementType/custom_fields", $parameters);

        if (isset($response['_embedded']['custom_fields'])) {
            $result = array_map(function ($item) {
                return $item['id'];
            }, $response['_embedded']['custom_fields']);
        } else {
            return [];
        }

        return count($fields) == 1 ? array_shift($result) : $result;
    }
}