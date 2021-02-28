#!/bin/bash

set -e

export LC_ALL=C
export DEBIAN_FRONTEND=noninteractive
minimal_apt_get_install='apt-get install -y --no-install-recommends'

set -x

## Upgrade all packages.
apt-get update
apt-get dist-upgrade -y -o Dpkg::Options::="--force-confdef" --no-install-recommends

## Add APT repository
$minimal_apt_get_install wget
echo 'deb http://apt.newrelic.com/debian/ newrelic non-free' | tee /etc/apt/sources.list.d/newrelic.list
wget -O- https://download.newrelic.com/548C16BF.gpg | apt-key add -

## Install required packages
apt-get update
$minimal_apt_get_install newrelic-php5
NR_INSTALL_SILENT=1 newrelic-install install

## Clear all
apt-get autoremove -y
apt-get clean
apt-get autoclean
rm -rf /build
rm -rf /var/lib/apt/lists/*
rm -rf /tmp/* /var/tmp/*
rm -rf /usr/share/man/??
rm -rf /usr/share/man/??_*
