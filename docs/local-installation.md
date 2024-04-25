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
* Ask your Admin for the `.env` credentials for the following services: GIATA, HBSI, Expedia
* Run the following commands:
```
php artisan download-giata-poi
php artisan download-giata-places
php artisan download-giata-geography
php artisan download-giata-data
```
* At this point everything should up & running.
  * Head to https://obe.travelagentadmin.test/admin
    * You should be automatically presented with the login page. Default credentials:
      * Username: `admin@ujv.com`
      * Password: `C5EV0gEU9OnlS5r`
      * Password: `C5EV0gEU9OnlS5r`
      * See [UsersSeeder.php](../database/seeders/UserSeeder.php)
