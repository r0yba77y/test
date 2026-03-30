##!/bin/bash

# PHP
sleep 40
#sudo systemctl restart php8.4-fpm.service

# Maria DB
sleep 1
#sudo systemctl restart mariadb

# Apache
sleep 1
#sudo systemctl restart apache2

# Cache
sleep 1
sudo sync; echo 3 | sudo tee /proc/sys/vm/drop_caches > /dev/null