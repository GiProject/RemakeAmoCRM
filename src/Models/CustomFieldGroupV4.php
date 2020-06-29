<?php

namespace RemakeAmoCRM\Models;

use AmoCRM\Exception;
use AmoCRM\NetworkException;

/**
 * Class CustomFieldGroup
 *
 * Класс модель для работы с группами полей
 *
 * @package AmoCRM\Models
 * @author mihasichechek <mihasichechek@gmail.com>
 * @link https://github.com/dotzero/amocrm-php
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class CustomFieldGroupV4 extends AbstractModel
{
    /**
     * @var array Список доступный полей для модели (исключая кастомные поля)
     */
    protected $fields = [
        'name',
        'sort',
    ];

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
     * Добавление дополнительных групп
     *
     * Метод позволяет добавлять дополнительные группы по одному или пакетно
     *
     * @param array $groups Массив объектов CustomFieldGroupV4
     * @param $elementType string Тип сущности, см. константы ELEMENT_*
     * @return int|array Уникальный идентификатор поля или массив при пакетном добавлении
     * @throws Exception
     * @throws NetworkException
     */
    public function apiAdd($groups = [], $elementType = 'leads')
    {
        if (!in_array($elementType, ['contacts', 'leads', 'companies', 'customers'])) {
            throw new Exception("Element type '$elementType' is unknown");
        }

        if (empty($groups)) {
            $groups = [$this];
        }

        $parameters = [];

        foreach ($groups AS $field) {
            $parameters[] = $field->getValues();
        }

        $response = $this->postRequest("/api/v4/{$elementType}/custom_fields/groups", $parameters);

        if (isset($response['_embedded']['custom_field_groups'])) {
            $result = array_map(function ($item) {
                return $item['id'];
            }, $response['_embedded']['custom_field_groups']);

            return count($groups) === 1 ? array_shift($result) : $result;
        }

        return [];
    }
}