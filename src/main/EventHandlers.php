<?php

namespace WebArch\BitrixEmailAsLogin;

use Bitrix\Main\EventManager;

class EventHandlers
{
    public function init()
    {
        $eventManager = EventManager::getInstance();

        foreach (['OnBeforeUserUpdate', 'OnBeforeUserRegister'] as $eventType) {
            $eventManager->addEventHandler(
                'main',
                $eventType,
                [$this, 'makeEmailAsLogin']
            );
        }
    }

    public function makeEmailAsLogin(array &$fields): bool
    {
        /**
         * Игнорировать регистрацию через внешние сервисы
         * или отсутствие необходимых для работы полей.
         * (например, если происходит обновление чего-то отличного от EMAIL и LOGIN)
         */
        if (
            isset($fields['EXTERNAL_AUTH_ID'])
            || (!isset($fields['EMAIL']) && !isset($fields['LOGIN']))
        ) {
            return true;
        }

        /**
         * Нормализация отсутствующих или некорректных начальных значений:
         * либо корректный email-адресс, либо пустая строка.
         */
        if (!array_key_exists('LOGIN', $fields) || !$this->checkEmail($fields['LOGIN'])) {
            $fields['LOGIN'] = '';
        }
        if (!array_key_exists('EMAIL', $fields) || !$this->checkEmail($fields['EMAIL'])) {
            $fields['EMAIL'] = '';
        }

        /**
         * Взаимная замена на соседа со срезанием лишних пробельных символов.
         */
        $fields['LOGIN'] = trim($fields['LOGIN'] ?: $fields['EMAIL']);
        $fields['EMAIL'] = trim($fields['EMAIL'] ?: $fields['LOGIN']);

        /**
         * Если оба заполнены корректными, но разными email,
         * то приоритет у поля EMAIL.
         */
        if ($fields['LOGIN'] != $fields['EMAIL']) {

            $fields['LOGIN'] = $fields['EMAIL'];

            return true;
        }

        /**
         * Если хотя бы один остался пустым, то это ошибка,
         * означающая, что ни в логине, ни в пароле не было корректного email-адреса.
         */
        if (trim($fields['LOGIN']) === '' || trim($fields['EMAIL']) === '') {

            $this->throwEmailNotFoundException();

            return false;
        }

        return true;
    }

    protected function throwEmailNotFoundException()
    {
        global $APPLICATION;
        $APPLICATION->ThrowException('Ни Логин, ни E-mail не содержат корректного email-адреса.');
    }

    /**
     * @param $email
     *
     * @return bool
     */
    protected function checkEmail($email): bool
    {
        return \check_email($email);
    }
}
