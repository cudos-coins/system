#!/bin/sh
php /vagrant/artisan migrate;
php /vagrant/artisan db:seed;
