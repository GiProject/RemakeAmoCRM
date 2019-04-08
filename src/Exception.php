<?php

namespace RemakeAmoCRM;

/**
 * Class Exception
 *
 * Базовый класс для всех исключений amoCRM API
 *
 * @package AmoCRM
 * @author dotzero <mail@dotzero.ru>
 * @link http://www.dotzero.ru/
 * @link https://github.com/dotzero/amocrm-php
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Exception extends \Exception
{
    /**
     * @var array Справочник ошибок и ответов amoCRM API
     */
    protected $errors = [
        '101' => 'Аккаунт не найден',
        '102' => 'POST-параметры должны передаваться в формате JSON',
        '103' => 'Параметры не переданы',
        '104' => 'Запрашиваемый метод API не найден',
        '110' => 'Неправильный логин или пароль',
        '111' => 'Неправильный код капчи',
        '112' => 'Пользователь не состоит в данном аккаунте',
        '113' => 'Доступ к данному аккаунту запрещён с Вашего IP адреса',
        '201' => 'Добавление контактов: пустой массив',
        '202' => 'Добавление контактов: нет прав',
        '203' => 'Добавление контактов: системная ошибка при работе с дополнительными полями',
        '204' => 'Добавление контактов: дополнительное поле не найдено',
        '205' => 'Добавление контактов: контакт не создан',
        '206' => 'Добавление/Обновление контактов: пустой запрос',
        '207' => 'Добавление/Обновление контактов: неверный запрашиваемый метод',
        '208' => 'Обновление контактов: пустой массив',
        '209' => 'Обновление контактов: требуются параметры "id" и "last_modified"',
        '210' => 'Обновление контактов: системная ошибка при работе с дополнительными полями',
        '211' => 'Обновление контактов: дополнительное поле не найдено',
        '212' => 'Обновление контактов: контакт не обновлён',
        '213' => 'Добавление сделок: пустой массив',
        '214' => 'Добавление/Обновление сделок: пустой запрос',
        '215' => 'Добавление/Обновление сделок: неверный запрашиваемый метод',
        '216' => 'Обновление сделок: пустой массив',
        '217' => 'Обновление сделок: требуются параметры "id", "last_modified", "status_id", "name"',
        '218' => 'Добавление событий: пустой массив',
        '219' => 'Список контактов: ошибка поиска, повторите запрос позднее',
        '221' => 'Список событий: требуется тип',
        '222' => 'Добавление/Обновление событий: пустой запрос',
        '223' => 'Добавление/Обновление событий: неверный запрашиваемый метод',
        '224' => 'Обновление событий: пустой массив',
        '225' => 'Обновление событий: события не найдены',
        '227' => 'Добавление задач: пустой массив',
        '228' => 'Добавление/Обновление задач: пустой запрос',
        '229' => 'Добавление/Обновление задач: неверный запрашиваемый метод',
        '230' => 'Обновление задач: пустой массив',
        '231' => 'Обновление задач: задачи не найдены',
        '232' => 'Добавление событий: ID элемента или тип элемента пустые либо некорректные',
        '233' => 'Добавление событий: по данному ID элемента не найдены некоторые контакты',
        '234' => 'Добавление событий: по данному ID элемента не найдены некоторые сделки',
        '235' => 'Добавление задач: не указан тип элемента',
        '236' => 'Добавление задач: по данному ID элемента не найдены некоторые контакты',
        '237' => 'Добавление задач: по данному ID элемента не найдены некоторые сделки',
        '238' => 'Добавление контактов: отсутствует значение для дополнительного поля',
        '240' => 'Добавление/Обновление сделок: неверный параметр "id" дополнительного поля',
        '244' => 'Добавление сделок: нет прав',
        '400' => 'Неверная структура массива передаваемых данных, либо не верные идентификаторы кастомных полей',
        '403' => 'Аккаунт заблокирован, за неоднократное превышение количества запросов в секунду',
        '429' => 'Превышено допустимое количество запросов в секунду',
        '2002' => 'По вашему запросу ничего не найдено',
    ];

    /**
     * Exception constructor
     *
     * @param null|string $message Сообщения исключения
     * @param int $code Код исключения
     */
    public function __construct($message = null, $code = 0)
    {
        if (isset($this->errors[$code])) {
            $message = $this->errors[$code];
        }

        parent::__construct($message, $code);
    }
}
