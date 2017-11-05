#!/usr/bin/env bash

# ERROR REPORTING

if [ $ENVIRONMENT != DEV ]; then
    sed -i 's/^display_startup_errors.*$/display_startup_errors = Off/' /usr/local/etc/php/conf.d/application.ini
    sed -i 's/^display_errors.*$/display_errors = Off/' /usr/local/etc/php/conf.d/application.ini
else
    sed -i 's/^display_startup_errors.*$/display_startup_errors = On/' /usr/local/etc/php/conf.d/application.ini
    sed -i 's/^display_errors.*$/display_errors = On/' /usr/local/etc/php/conf.d/application.ini
fi
