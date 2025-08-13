# Contributing to the Sendy PrestaShop module

## PrestaShop documentation

First, familiarize yourself with the [PrestaShop documentation](https://devdocs.prestashop.com/).

- [Getting started](https://devdocs.prestashop-project.org/9/modules/creation/)
- [List of hooks](https://devdocs.prestashop-project.org/9/modules/concepts/hooks/list-of-hooks/)
- [Built-in translations](https://github.com/PrestaShop/PrestaShop/tree/develop/translations/default)

## Development

To set up a development environment for the Sendy PrestaShop module, make sure the following programs are installed locally:

- PHP CLI
- Composer
- Docker
- Lefthook

### Prestashop installation

PrestaShop 1.7, 8 and 9 can run alongside each other using Docker:

```shell
docker compose --file docker-compose.1.7.yml up -d
docker compose --file docker-compose.8.yml up -d
docker compose --file docker-compose.9.yml up -d
```

Wait for the containers to start and the PrestaShop installations to complete. This may take a few minutes.

You can then log in to the PrestaShop back office with email `demo@prestashop.com` and password `prestashop_demo` at the following URLs:
- http://localhost:8017/admin7639
- http://localhost:8080/admin7639
- http://localhost:8090/admin7639

### Testing webhooks

To test webhooks, you can use [ngrok](https://ngrok.com/) to expose your local server to the internet.

```
ngrok http 8090
```

When starting the docker container, make sure to update the `PS_DOMAIN` environment variable to match the ngrok URL, and set the `PS_ENABLE_SSL` variable to `1`.

```yaml
# docker-compose
    environment:
      PS_DOMAIN: your-ngrok-url.ngrok.io
      PS_ENABLE_SSL: 1
```

Then, to work around the PrestaShop warning in the back office, add the following line to the top of the prestashop-{version}/admin7639/index.php file:

```php
$_SERVER['HTTPS'] = 'on';
```

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

## Publishing

Before releasing a new version, ensure there is an upgrade script if needed (https://devdocs.prestashop-project.org/9/modules/creation/enabling-auto-update/). For example, you might need to run `Tools::clearAllCache()` after an update to JavaScript files, or to update the database schema.

To release a new version of the module, first bump the version using the bump_version.sh script with the new version number as an argument:

```shell
./bump_version.sh 1.2.3
```
