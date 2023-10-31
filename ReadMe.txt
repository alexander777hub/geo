1) git clone https://github.com/alexander777hub/geo.git
2) cd geo
composer install --prefer-dist
php yii migrate
3) сконфигрировать веб-сервер (у меня nginx + php-fpm)
4) добавить .env файлы
В проекте два .env файла - для php (в корне папки geo) и для golang (geo/go/geo_cluster/)
Пример .env для php
GO_BASE_URL = http://127.0.0.1:8085

WORKER_ACCESS_TOKEN = DLFLDFKDLkll
GO_COUNTRY = tester/country
GO_NETWORK = tester/network


DB_HOST = localhost
DB_DATABASE = test
DB_USERNAME = test
DB_PASSWORD = 12345
DB_CHARSET = utf8mb4


Пример .env файла для go

WORKER_ACCESS_TOKEN = DLFLDFKDLkll
4) убедиться что у пользователя, под которым работает вебсервер, есть права на папку geo(и в на папку geo/runtime)

5) cd geo/go/geo_cluster/web
    go run index.go

    После этого должно работать два приложения:
    1) 127.0.0.1 - php
    2) 127.0.0.1:8085 - golang

Проект представляет собой REST API для получения данных пользователем.
/geo-ip/country/<ip> - для получения данных по стране
/geo-ip/network/<ip> - для получения сети для заданного ip
<ip> обязательный параметр (string)
Оба роута публичные. API для пользователей реализовано на php, внутри приложения происходит обращение
к сервису на golang, где происходят все вычисления и работа с БД и библиотекой geoip2
https://github.com/oschwald/geoip2-golang
github.com/oschwald/maxminddb-golang

БД с ip адресами платные, поэтому я использую набор тестовых данных (файлы .mmdb в папке geo/go/geo_cluster/web/data)
https://www.maxmind.com/en/geoip2-databases для более точных данных maxmind предлагают приобрести подписку.
При тестировании лучше использовать ip адреса из файлов json отсюда https://github.com/maxmind/MaxMind-DB/blob/main/source-data/
Из соображений производительности я поместил все операции по чтению ip в golang, поскольку
1) он позволяет обрабатывать каждый пользовательский запрос в отдельном потоке
2) при большом количестве записей скорость будет выше

Поскольку ТЗ включает создание mysql таблицы, я добавил возможность хранения данных по IP сети и стране в mysql
Для создания тестового набора данных нужно перейти по адресу (сервер go должен быть запущен):
/geo-ip/get-records
Получение  сети для по заданному ip может быть реализовано с помощью метода класса app/models/Network getNetworkByIp
По API получение сети происходит с использванием библиотеки golang.

Для корректной работы проекта должны быть установлены:
1)php 8.1.12
2) mysql
3) go1.21.3
4) модули memcache, curl для php
php часть проекта реализована на фреймворке yii2
Для запуска тестов
cd geo
php vendor/bin/codecept run unit








