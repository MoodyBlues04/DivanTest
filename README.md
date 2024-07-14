# DivanTest

## Motivation
It's test task

## Installation
1. ```git clone git@github.com:MoodyBlues04/DivanTest.git```
2. ```composer install```
3. set up .env file ```cp .env.example .env```
4. run ```php artisan migrate``` and ```php artisan db:seed``` to seed test data
5. enjoy

## Usage
+ test cases checking in: ```tests/Feature/BankAccountTest.php```
+ run ```php artisan test --testsuite=Feature``` to run tests

## Tips
+ in ```db_schema.mwb``` you can find graphic db schema (for workbench sql)
