# Scholarship API

## Setup

1. `cp .env.example .env`
2. `composer install`
3. `sail up -d`
4. `sail artisan migrate:fresh --seed`

## Testing

Check the terminal output after seeding to see the test tokens (user and admin token).

Paste the token in your Postman collection and test the routes.


## ERD is attached on the repo
schema.png

## Postman Collection is attached
/storage/postman/api_collection

## Assumptions
disburement schedules will be generated automatically when student is awarded
can also be added manually