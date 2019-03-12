Ledger
=========
[![Build Status](https://img.shields.io/travis/innospan/ledger.svg)](https://travis-ci.org/innospan/ledger)
![Last commit](https://img.shields.io/github/last-commit/innospan/ledger.svg)
![License](https://img.shields.io/github/license/innospan/ledger.svg)

**Warning: This software is still in development,** so we don’t recommend you run it on a production site.

Ledger is a free, open-source and easy-to-use banking tool. It allows you to host your own banking tool which can take care of budgets and monthly costs like rent without having to give this information to third party tools.

### Features

* Import bank transactions into you own enviroment
* Modern and easy-to-use interface with beautifull graphs
* Handles multiple bank accounts from different banks in a single system
* Create budgets to keep an eye on your spending
* Automatically assign transactions to budgets and categories

## Requirements

* PHP >= 7.1.3
* OpenSSL PHP Extension
* PDO PHP Extension
* Mbstring PHP Extension
* Tokenizer PHP Extension
* XML PHP Extension
* Ctype PHP Extension
* JSON PHP Extension
* BCMath PHP Extension
* [Composer](https://getcomposer.org/)

## Quick Start
### Installation Instructions

1. Run `composer install`.

2. Rename `.env.example` file to `.env` or run `cp .env.example .env`. Update `.env` to your specific needs. Don't forget to set `DB_USERNAME` and `DB_PASSWORD` with the settings used behind.

3. Run `php artisan key:generate`.

4. Run `php artisan migrate`.

5. Run `php artisan serve`.

After installed, you can access http://localhost:8000 in your browser, the installer will automatically run.

## Disclaimer

This repository is provided on an as-is basis. The authors or contributors cannot be held responsible for its accuracy or completeness. 

## License

MIT licensed. See [LICENSE](LICENSE) for details.
