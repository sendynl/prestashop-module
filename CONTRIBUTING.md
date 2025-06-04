# Contributing to the Sendy PrestaShop module

## Development

To set up a development environment for the Sendy PrestaShop module, make sure the following programs are installed locally:

- PHP CLI
- Composer
- Docker

### Prestashop installation

PrestaShop 1.7, 8 and 9 can run alongside each other using Docker:

```shell
docker compose --file docker-compose.1.7.yml up -d
docker compose --file docker-compose.8.yml up -d
docker compose --file docker-compose.9.yml up -d
```

You can log in to the PrestaShop back office with email `demo@prestashop.com` and password `prestashop_demo` at the following URLs:
- http://localhost:8017/admin7639
- http://localhost:8080/admin7639
- http://localhost:8090/admin7639

### Code style and quality

We use the tools provided by [prestashop/php-dev-tools](https://github.com/PrestaShop/php-dev-tools).

To fix the code style, run:

```shell
composer run fix
```

To statically analyze the code, first, ensure the PrestaShop source is available by running the Docker containers, then run:

```shell
composer run analyse
```
