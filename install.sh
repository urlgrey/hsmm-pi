#!/usr/bin/env sh

#
# File: install.sh
# Authors: Scott Kidder, Clayton Smith
# Purpose: This script will configure a newly-imaged Raspberry Pi running
#   Raspbian Jessie Lite with the dependencies and HSMM-Pi components.
#

if [ "$(id -u)" = "0" ]
  then echo "Please do not run as root, HTTP interface will not work"
  exit
fi

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
    libnet-gpsd3-perl \
    ntp \
    php5-mcrypt \
    olsrd

# Enabe php5-mcrypt
sudo php5enmod mcrypt

# Remove ifplugd if present, as it interferes with olsrd
sudo apt-get remove -y ifplugd


# On Ubuntu 13.04 systems this file is a symbolic link to a file in the /run/
# directory structure.  Remove the symbolic link and replace with a file that
# can be managed by HSMM-Pi.
if [ -L /etc/resolv.conf ]; then
    rm -f /etc/resolv.conf
    touch /etc/resolv.conf
fi

sudo bash -c "echo 'nameserver 8.8.8.8' > /etc/resolv.conf"
sudo chgrp www-data /etc/resolv.conf
sudo chmod g+w /etc/resolv.conf

sudo bash -c "echo '# This file will be overwritten' > /etc/ethers"

# Install cakephp with GitHub
#git clone -b 2.x git://github.com/cakephp/cakephp.git ~/projects/
#sudo mv -f ~/projects/lib/Cake /usr/share/php
#rm -rf ~/projects
# Install cakephp with pear
sudo pear channel-discover pear.cakephp.org
sudo pear install cakephp/CakePHP-2.8.3

# Checkout the HSMM-Pi project
if [ ! -e ${PROJECT_HOME} ]; then
    git clone https://github.com/cbegg50/hsmm-pi.git
else
    cd ${PROJECT_HOME}
    git pull
fi

# Set symlink to webapp
if [ -d /var/www/html ]; then
    cd /var/www/html
else
    cd /var/www
fi
if [ ! -d hsmm-pi ]; then
    sudo ln -s ${PROJECT_HOME}/src/var/www/hsmm-pi
fi
sudo rm -f index.html
sudo ln -s ${PROJECT_HOME}/src/var/www/index.html

#cd ${PROJECT_HOME}
#php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
#php -r "if (hash_file('SHA384', 'composer-setup.php') === '070854512ef404f16bac87071a6db9fd9721da1684cd4589b1196c3faf71b9a2682e2311b36a5079825e155ac7ce150d') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
#php composer-setup.php
#php -r "unlink('composer-setup.php');"
#php composer.phar install
#sudo mv Vendor/cakephp/cakephp/lib/Cake /usr/share/php
#rm -rf Vendor composer.phar composer.lock

cd ${PROJECT_HOME}/src/var/www/hsmm-pi

# Create temporary directory used by HSMM-PI webapp, granting write priv's to www-data
mkdir -p tmp/cache/models
mkdir -p tmp/cache/persistent
mkdir -p tmp/logs
mkdir -p tmp/persistent
sudo chgrp -R www-data tmp
sudo chmod -R 775 tmp

# Set permissions on system files to give www-data group write priv's
for file in /etc/hosts /etc/hostname /etc/resolv.conf /etc/network/interfaces /etc/rc.local /etc/ntp.conf /etc/default/gpsd /etc/dhcp/dhclient.conf /etc/ethers; do
    sudo chgrp www-data ${file}
    sudo chmod g+w ${file}
done

sudo chgrp www-data /etc/dnsmasq.d
sudo chmod 775 /etc/dnsmasq.d

# Copy scripts into place
if [ ! -e /usr/local/bin/read_gps_coordinates.pl ]; then
    sudo cp ${PROJECT_HOME}/src/usr/local/bin/read_gps_coordinates.pl /usr/local/bin/read_gps_coordinates.pl
    sudo chgrp www-data /usr/local/bin/read_gps_coordinates.pl
    sudo chmod 775 /usr/local/bin/read_gps_coordinates.pl
fi

sudo mkdir -p /var/data/hsmm-pi
sudo chown root.www-data /var/data/hsmm-pi
sudo chmod 775 /var/data/hsmm-pi
if [ ! -e /var/data/hsmm-pi/hsmm-pi.sqlite ]; then
    sudo Console/cake schema create -y
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
    sudo bash -c "echo 'www-data ALL=(ALL) NOPASSWD: /sbin/shutdown' >> /etc/sudoers"
fi

# enable apache mod-rewrite
sudo a2enmod rewrite
if [ -d /etc/apache2/conf.d ]; then
    sudo cp ${PROJECT_HOME}/src/etc/apache2/conf.d/hsmm-pi.conf /etc/apache2/conf.d/hsmm-pi.conf
elif [ -d /etc/apache2/conf-available ]; then
    sudo cp ${PROJECT_HOME}/src/etc/apache2/conf-available/hsmm-pi.conf /etc/apache2/conf-available/hsmm-pi.conf
    sudo a2enconf hsmm-pi
fi
sudo service apache2 restart

# Download and build olsrd
cd /var/tmp
#git clone https://github.com/OLSR/olsrd.git
#git clone --branch release-0.6.8.1 --depth 1 https://github.com/OLSR/olsrd.git
#cd olsrd

# patch the Makefile configuration to produce position-independent code (PIC)
# applies only to ARM architecture (i.e. Beaglebone/Beagleboard)
#if uname -m | grep -q arm -; then
#  printf "CFLAGS +=\t-fPIC\n" >> Makefile.inc
#fi

# build the OLSRD core
#make
#sudo make install

# build the OLSRD plugins (libs)
#make libs
#sudo make libs_install

sudo mkdir -p /etc/olsrd
sudo chgrp -R www-data /etc/olsrd
sudo chmod g+w -R /etc/olsrd

sudo cp ${PROJECT_HOME}/src/etc/init.d/olsrd /etc/init.d/olsrd
sudo chmod +x /etc/init.d/olsrd

sudo mkdir -p /etc/default
sudo cp ${PROJECT_HOME}/src/etc/default/olsrd /etc/default/olsrd

cd /var/tmp
rm -rf /var/tmp/olsrd

sudo rm -f /etc/olsrd.conf
sudo ln -fs /etc/olsrd/olsrd.conf /etc/olsrd.conf
sudo ln -fs /usr/local/sbin/olsrd /usr/sbin/

# enable services
sudo sysv-rc-conf --level 2345 olsrd on
sudo sysv-rc-conf --level 2345 dnsmasq on
sudo sysv-rc-conf --level 2345 gpsd on

# fix the priority for the olsrd service during bootup
sudo update-rc.d olsrd defaults 02

# install CRON jobs
sudo cp ${PROJECT_HOME}/src/etc/cron.d/* /etc/cron.d/

# print success message if we make it this far
printf "\n\n---- SUCCESS ----\n\nLogin to the web console to configure the node\n"
