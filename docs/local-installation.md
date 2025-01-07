# Local Installation

## Requirements
- Docker > 22

## Installation
* This project by default runs on the vhost `obe.travelagentadmin.test`
* If you have not, create ssl credentials for `travelagentadmin.test` using the `create-certificates.sh` 
script found besides this doc.
  * run `bash create-certificates.sh travelagentadmin.test`
  * This will generate a cert for `*.travelagentadmin.test` so it will work by default with `obe.travelagentadmin.test`
  By trusting `ca.travelagentadmin.test.crt` in your machine as a Trusted Certificate Root, any certificate derived from this CA
  will give you a green lock in the browser.
  * SECURITY WARNING: Keep these certificates and CA secure, since you'll trust in your browser, ensure these are for your
personal use and don't leak them anywhere or you'll expose yourself to attackers.
* Copy the following files in the folder `infrastructure/docker/php-8.2/ssl`
  * `travelagentadmin.test.crt`
  * `travelagentadmin.test.key`
* Run `cp .env.example .env`
  * Make sure to fill the following entries:
    * All booking suppliers (expedia, hbsi, Ice portal, Giata)
    * Google GOOGLE_API_DEVELOPER_KEY
* In case it does not exist, create the network
  * `docker network ls`
  * `docker network create --subnet=10.10.0.0/16 travelagentadmin-network`
* Add this record to your `/etc/hosts` file 
  * `127.0.0.1 obe.travelagentadmin.test`
* Run the following Commands
* Run `docker-compose -f docker-compose.local.yml up -d`
* Run `docker-compose ps` to check if all containers are up
* Run `docker exec -it booking-engine bash` to enter the instance.
* Run `composer install`
* Run `php artisan key:generate`
* Run `php artisan migrate --seed`
* Run `npm ci`
* Run `npm run build`
* Run the following commands (Giata commands can take more than 2hs):
```
php artisan download-giata-poi
php artisan download-giata-places
php artisan download-giata-geography
php artisan download-giata-data
```
* To download EXPEDIA content for only a city (cancun recomended) for test environments, run the following command.
```
php artisan download-expedia-data content '1, 2, 3, 4' 'cancun'
```
* To generate the Ice Portal mappings for cancun, run:
```
php artisan db:seed --class=IcePortalCancunMappingPropertiesSeeder
```
* To generate mappings for HBSI test env:
```
php artisan db:seed --class=TestPropertiesSeeder
```
* At this point everything should up & running.
  * Head to https://obe.travelagentadmin.test/admin
    * You should be automatically presented with the login page. Default credentials:
      * Username: `admin@ujv.com`
      * Password: `C5EV0gEU9OnlS5r`
      * Password: `C5EV0gEU9OnlS5r`
      * See [UsersSeeder.php](../database/seeders/UserSeeder.php)
  * To configure ADMIN access:
    * Go to https://obe.travelagentadmin.test:8448/admin/channels and copy the Access token (it looks like that: 1|OJHtyifdfdfdfsdfdsafasdfasdfasdf8dsdfasdffsdsdfafa). Paste it in ADMI .env: TRAVEL_CONNECT_OBE_TOKEN 
    * In admin run:
    ```
      php artisan db:seed --class=ExternalIdentifiersTableSeeder
      ```
