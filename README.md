# TrinityTV API
php >=8
```php
require_once 'TrinityTV.class.php';

//YOUR_PARTNER_ID,YOUR_SALT_CODE,TARIFF_ID - видається менеджерами https://trinity-tv.net
$api = new TrinityTV('YOUR_PARTNER_ID', 'YOUR_SALT_CODE');

// Створити підписку, де 123 - ваш внутрішній номер абонента
$api->create(123, TARIFF_ID);
//Інформація про підписку абнента 123
$user = $api->getUser(123);
//Додати мак пристрою з приміткою або без
$api->addDeviceByMac(123, '112233445566', 'Примітка');
//Додати пристрій по коду з приміткою або без
$api->addDeviceByCode(123, 9999, 'Примітка');
//Видалити пристрій по маку, можна по UUID
$api->deleteDevice(123, '112233445566');
$api->deleteDevice(123, '', 'uuid-id');
$api->deleteDevice(123, uuid: 'uuid-id');
//Оновити примітку до пристрою, де 12345 - ID пристрою
$api->updateDeviceNote(12345, 'Примітка');
//Призупитити підписку абонета
$api->suspend(123);
//Відновити підписку абонета
$api->resume(123);
//Змінити дані про абонента
$api->updateUser(123, 'Ім`я', 'Прізвище', 'По батькові', 'Адреса');
$api->updateUser(123, address: 'Адреса');
$api->updateUser(123, last_name: 'Прізвище');
//Список всіх підписок
$users = $api->usersList();
//Отримати звіт за останній місяць
$report = $api->getReport();
// Отримати підключені пристрої абонета 123
$devices = $api->getDevices(123);
//Отримати всі пристрої підписок
$all_devices = $api->getDeviceList();
//Змінити номер договору (внутрішній)
$api->changeContract(123, 1234);
//Отримати останню сесію абонентів
$sessions = $api->getLastSessions();
//Створити плейлист m3u для абонента
$playlist = $api->getPlaylist(123);
//Видалити плейлист
$api->deletePlaylist(123, 'playlist_url'); // Або $this->deleteDevice(123, uuid: 'playlist_url');
//Отримати лінк для автоматичної асторизації на сайті трініті, 1 - https://trinity-tv.net, 2 - https://sweet.tv/
$link = $api->getLink(123, 1);// 1 - по дефолту
//Отримати назву типу пристрою з масиву devices['device_type'] від $api->getDevices(123) або $api->getDeviceList()
$type = $api->getDeviceType(8);// Поверне DT_MAG250_Micro

if ($api->errors) {
    print_r($api->errors); // Масив з помилками
}
```