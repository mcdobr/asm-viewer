#!/usr/bin/env bash

# Update and install apache2
apt-get update
apt-get upgrade -y
apt-get install -y apache2

# If /var/www is not a symbolic link then make it
# a symbolic link to the vagrant folder
if ! [ -L /var/www ]; then
	rm -rf /var/www
	ln -fs /vagrant /var/www
fi


# Install php
apt-get install -y php libapache2-mod-php

# Install compilers
apt-get install -y gcc


## ARM compilers
apt-get install -y gcc-arm-none-eabi gcc-arm-linux-gnueabi 

## MIPS compilers
apt-get install -y gcc-mips-linux-gnu

## AVR compilers
apt-get install -y gcc-avr


# Restart apache
echo "-- Restart apache --"
service apache2 restart
