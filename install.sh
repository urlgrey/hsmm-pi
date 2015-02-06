#!/bin/sh

#
# File: install.sh
# Author: Scott Kidder
# Purpose: This script will configure a newly-imaged Raspberry Pi running 
#   Raspbian Wheezy 2014-09-09 with the dependencies and HSMM-Pi components.
#

PROJECT_HOME=${HOME}/hsmm-pi

cd ${HOME}

# Update list of packages
sudo apt-get update

# Install Web Server deps
sudo apt-get install -y \
    apache2 \
    php5 \
    sqlite \
    php-pear \
    php5-sqlite  \
    dnsmasq \
    sysv-rc-conf \
    make \
    bison \
    flex \
    gpsd \
    gpsd-clients \
    libnet-gpsd3-perl \
    ntp

# Install cakephp with Pear
sudo pear channel-discover pear.cakephp.org
sudo pear install cakephp/CakePHP-2.4.10

# Checkout the HSMM-Pi project
if [ ! -e ${PROJECT_HOME} ]; then
    git clone https://github.com/urlgrey/hsmm-pi.git
else
    cd ${PROJECT_HOME}
    git pull
fi

# Set symlink to webapp
cd /var/www
if [ ! -e /var/www/hsmm-pi ]; then
    sudo ln -s ${PROJECT_HOME}/src/var/www/hsmm-pi
fi
sudo rm -f /var/www/index.html
sudo ln -s ${PROJECT_HOME}/src/var/www/index.html /var/www/

# Create temporary directory used by HSMM-PI webapp, granting write priv's to www-data
cd ${PROJECT_HOME}/src/var/www/hsmm-pi
mkdir -p tmp/cache/models
mkdir -p tmp/cache/persistent
mkdir -p tmp/logs
mkdir -p tmp/persistent
sudo chgrp -R www-data tmp
sudo chmod -R 775 tmp

# Set permissions on system files to give www-data group write priv's
for file in /etc/hosts /etc/hostname /etc/resolv.conf /etc/network/interfaces /etc/rc.local /etc/ntp.conf /etc/default/gpsd /etc/dhcp/dhclient.conf; do
    sudo chgrp www-data ${file}
    sudo chmod g+w ${file}
done

sudo chgrp www-data /etc/dnsmasq.d
sudo chmod 775 /etc/dnsmasq.d

# On Ubuntu 13.04 systems this file is a symbolic link to a file in the /run/
# directory structure.  Remove the symbolic link and replace with a file that
# can be managed by HSMM-Pi.
if [ -L /etc/resolv.conf ]; then
    rm -f /etc/resolv.conf
    touch /etc/resolv.conf
fi

sudo chgrp www-data /etc/resolv.conf
sudo chmod g+w /etc/resolv.conf

# Copy scripts into place
if [ ! -e /usr/local/bin/callsign_announcement.sh ]; then
    sudo cp ${PROJECT_HOME}/src/var/www/hsmm-pi/webroot/files/callsign_announcement.sh.template /usr/local/bin/callsign_announcement.sh
    sudo chgrp www-data /usr/local/bin/callsign_announcement.sh
    sudo chmod 775 /usr/local/bin/callsign_announcement.sh
fi

if [ ! -e /usr/local/bin/read_gps_coordinates.pl ]; then
    sudo cp ${PROJECT_HOME}/src/usr/local/bin/read_gps_coordinates.pl /usr/local/bin/read_gps_coordinates.pl
    sudo chgrp www-data /usr/local/bin/read_gps_coordinates.pl
    sudo chmod 775 /usr/local/bin/read_gps_coordinates.pl
fi

sudo mkdir -p /var/data/hsmm-pi
sudo chown root.www-data /var/data/hsmm-pi
sudo chmod 775 /var/data/hsmm-pi
if [ ! -e /var/data/hsmm-pi/hsmm-pi.sqlite ]; then
    sudo cp ${PROJECT_HOME}/src/var/data/hsmm-pi/hsmm-pi.sqlite /var/data/hsmm-pi/hsmm-pi.sqlite
    sudo chown root.www-data /var/data/hsmm-pi/hsmm-pi.sqlite
    sudo chmod 664 /var/data/hsmm-pi/hsmm-pi.sqlite
fi

# enable port 8080 on the Apache server
OUTPUT=`grep "Listen 8080" /etc/apache2/ports.conf`
if [ -z "$OUTPUT" ]; then
    sudo bash -c "echo 'Listen 8080' >> /etc/apache2/ports.conf"
fi

# allow the www-data user to run the WiFi scanning program, iwlist
OUTPUT=`sudo grep "www-data" /etc/sudoers`
if [ -z "$OUTPUT" ]; then
    sudo bash -c "echo 'www-data ALL=(ALL) NOPASSWD: /sbin/iwlist' >> /etc/sudoers"
fi

# enable apache mod-rewrite
cd /etc/apache2/mods-enabled
sudo ln -fs ../mods-available/rewrite.load
sudo cp ${PROJECT_HOME}/src/etc/apache2/conf.d/hsmm-pi.conf /etc/apache2/conf.d/hsmm-pi.conf
sudo service apache2 restart

# Download BBHN packages (needed for olsrd patch)
cd /var/tmp
git clone git://ubnt.broadband-hamnet.org/bbhn_packages

# Download and build olsrd
cd /var/tmp
git clone git://olsr.org/olsrd.git
cd olsrd

# Checkout the latest 0.6.7 release, have seen intermittent problems with 0.6.5
git checkout release-0.6.7

# Apply BBHN patch to olsrd
patch -p1 < ../bbhn_packages/net/olsrd/patches/002-mode_secure-timediff-fix

# patch the Makefile configuration to produce position-independent code (PIC)
# applies only to ARM architecture (i.e. Beaglebone/Beagleboard)
if uname -m | grep -q arm -; then
  printf "CFLAGS +=\t-fPIC\n" >> Makefile.inc
fi

# build the OLSRD core
make
sudo make install

# build the OLSRD plugins (libs)
make libs
sudo make libs_install

sudo mkdir -p /etc/olsrd
sudo chgrp -R www-data /etc/olsrd
sudo chmod g+w -R /etc/olsrd

sudo cp ${PROJECT_HOME}/src/etc/init.d/olsrd /etc/init.d/olsrd
sudo chmod +x /etc/init.d/olsrd

sudo mkdir -p /etc/default
sudo cp ${PROJECT_HOME}/src/etc/default/olsrd /etc/default/olsrd

cd /var/tmp
rm -rf /var/tmp/olsrd
rm -rf /var/tmp/bbhn_packages

sudo rm -f /etc/olsrd.conf
sudo ln -fs /etc/olsrd/olsrd.conf /etc/olsrd.conf
sudo ln -fs /usr/local/sbin/olsrd /usr/sbin/

# enable services
sudo sysv-rc-conf --level 2345 olsrd on
sudo sysv-rc-conf --level 2345 dnsmasq on
sudo sysv-rc-conf --level 2345 gpsd on

# fix the priority for the olsrd service during bootup
sudo update-rc.d olsrd defaults 02

# install CRON jobs for reboot and callsign announcement
sudo cp ${PROJECT_HOME}/src/etc/cron.d/* /etc/cron.d/

# print success message if we make it this far
printf "\n\n---- SUCCESS ----\n\nLogin to the web console to configure the node\n"

