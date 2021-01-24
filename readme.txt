
Components needed for installation: 
1)Docker version 19.03.8 
2)Docker-compose version 1.25.4 

Installation:
1) git clone https://github.com/reterius/poc_spotify_backend.git
2) cd poc_spotify_backend
3) docker-compose up -d
4) It will be available http://127.0.0.1:8000

TEST:
tests are in this folder:  tests/Feature/AuthenticationTest.php

To run tests:

1) you have to go inside the container via: docker exec rest_api /bin/bash
2) We must use the test database for tests. 
In config/database.php file 
find  'database' => env('DB_DATABASE', database_path('rest-api.sqlite'))  
replace with  'database' => env('DB_DATABASE', database_path('rest-api-test.sqlite')) 
3 cd /source/poc_spotify_backend
3) Run this command: vendor/bin/phpunit komutunu 


EK BİLGİLER:
You can see some helpers some custom helpers file in this folder app/Helpers/Helper.php


Spotify api erişim bilgileri:

The following information in the .env file is the spotify api access information.
SPOTIFY_CLIENT_ID=2e39e74729b24e6bbb6801f89d84f6b9
SPOTIFY_CLIENT_SECRET=b201741a50bb474e994084bbfcfa5c5b

Api docs: https://documenter.getpostman.com/view/5458897/TW6tMAhm



