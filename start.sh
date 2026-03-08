#!/bin/bash

sed -i 's/80/10000/g' /etc/apache2/ports.conf
sed -i 's/:80/:10000/g' /etc/apache2/sites-available/000-default.conf

apache2-foreground
