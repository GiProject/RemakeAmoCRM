<?php

namespace AmoCRM\Models;

use AmoCRM\Models\Traits\SetDateCreate;
use AmoCRM\Models\Traits\SetLastModified;

/**
 * Class Note
 *
 * Класс модель для работы с Примечаниями
 *
 * @package AmoCRM\Models
 * @author dotzero <mail@dotzero.ru>
 * @link http://www.dotzero.ru/
 * @link https://github.com/dotzero/amocrm-php
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Call extends AbstractModel
{
    use SetDateCreate, SetLastModified;

    /**
     * @var array Список доступный полей для модели (исключая кастомные поля)
     */
    protected $fields = [
        'phone_number',
        'direction',
        'uniq',
        'duration',
        'call_status',
        'link',
        'source',
        'call_result'
    ];

    const IN = 'inbound';
    const OUT = 'outbound';

    public function apiAdd($calls = [])
    {


        if (empty($calls)) {
            $calls = [$this];
        }

        $parameters = [
            'add' => []
        ];

        foreach ($calls as $call) {
            $parameters['add'][] = $call->getValues();
        }

        $response = $this->postRequest('/api/v2/calls', $parameters);

        if( isset( $response['items'] ) ){
            $result = [];
            foreach ( $response['items'] as $item ){
                $result[] = [
                    'call_id' => $item['id'],
                    'element_id' => $item['element_id'],
                    'element_type' => $item['element_type'],
                ];
            }
            return count($result) ? $result : null;
        }
        return false;
    }

    /**
     * Обновление примечания
     *
     * Метод позволяет обновлять данные по уже существующим примечаниям
     *
     * @link https://developers.amocrm.ru/rest_api/notes_set.php
     * @param int $id Уникальный идентификатор примечания
     * @param string $modified Дата последнего изменения данной сущности
     * @return bool Флаг успешности выполнения запроса
     * @throws \AmoCRM\Exception
     */
    public function apiUpdate($id, $modified = 'now')
    {
        $this->checkId($id);

        $parameters = [
            'notes' => [
                'update' => [],
            ],
        ];

        $lead = $this->getValues();
        $lead['id'] = $id;
        $lead['last_modified'] = strtotime($modified);

        $parameters['notes']['update'][] = $lead;

        $response = $this->postRequest('/private/api/v2/json/notes/set', $parameters);

        return empty($response['notes']['update']['errors']);
    }
}
