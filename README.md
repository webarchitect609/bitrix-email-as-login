[![Build Status](https://travis-ci.org/webarchitect609/bitrix-email-as-login.svg?branch=master)](https://travis-ci.org/webarchitect609/bitrix-email-as-login)

Переключение Битрикс в режим, когда email используется как логин.

**Пока нестабильная версия - будьте внимательны!**

Как использовать: 
-----------------

1 Установить через composer 

`composer require webarchitect609/bitrix-email-as-login`

2 Включить настройки "E-mail является обязательным полем" и "Проверять E-mail на уникальность при регистрации" на 
вкладке "Авторизация" в настройках "Главного модуля" или же однократно выполнить представленный ниже скрипт в 
"Командной PHP-строке" в административной панели. 

```php
$options = ['new_user_email_required', 'new_user_email_uniq_check'];
foreach ($options as $option) {
    COption::SetOptionString('main', $option, 'Y');
}
foreach ($options as $option) {
    echo sprintf(
        '%s=%s' . PHP_EOL,
        $option,
        COption::GetOptionString('main', $option, 'null')
    );
}
```

Результатом успешного срабатывания скрипта должен быть вывод:
```
new_user_email_required=Y
new_user_email_uniq_check=Y
```

3 В init.php инициализировать установку обработчиков событий: 
`(new \WebArch\BitrixEmailAsLogin\EventHandlers())->init();`
    
    Происходит обработка событий `main:OnBeforeUserUpdate` и `main:OnBeforeUserAdd` c приоритетом 500. Если требуется
    использовать дополнительные обработчики для этих же событий, то им требуется по необходимости задавать приоритет
    меньше 500 или больше 500. 

4 Теперь можно пользоваться! Каким бы способом не был создан или обновлён пользователь, его "Логин" всегда будет
равен его "E-Mail". А если вдруг случится так, что и в "Логин" и в "E-Mail" будут переданы корректные, но разные email
адреса, то приоритет отдаётся значению поля "E-Mail".  
