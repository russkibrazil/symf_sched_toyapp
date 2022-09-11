#!/bin/bash
# php bin/console doctrine:fixture:load --no-interaction
APP_ENV=test php bin/console doctrine:fixture:load --no-interaction
rm -r var/error-screenshots/*test* && echo "Error screenshots cleared"
php bin/phpunit --verbose > output.phpunit.log