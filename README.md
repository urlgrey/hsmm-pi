HSMM-Pi
=======

HSMM-Pi is a set of tools to easily configure the Raspberry Pi to function as a High-Speed 
Multimedia (HSMM) wireless node.  HSMM offers radio amateurs (HAMs) the ability 
to operate high-speed data networks in the frequencies shared with unlicenced 
users of 802.11 b/g/n networking equipment.  HAMs can operate HSMM at higher 
power with larger antennas than are available to unlicensed users.  The HSMM-Pi 
project makes it possible to run an HSMM mesh node on the Raspberry Pi.

HSMM-Pi Blog:
http://hsmmpi.wordpress.com/

For a video tour, see the following YouTube video:
http://www.youtube.com/watch?v=ltUAw02vfqk

Modes
=====

Mesh Gateway Node
=================
The Mesh Gateway is capable of routing traffic throughout the mesh, and 
provides an Internet link to the mesh through the wired Ethernet port.  The 
gateway obtains a DHCP lease on the wired interface, and advertises its 
Internet link to members of the mesh using OLSRD.


Internal Mesh Node
==================
This node is capable of routing traffic throughout the mesh, and 
providing mesh access to any hosts connected to its wired Ethernet port.  
The node can run a DHCP server that issues DHCP leases to any hosts on the 
wired connection.  It also runs a DNS server that can provide name resolution 
for mesh nodes and Internet hosts.

The following sequence shows how the two types of nodes can be deployed:

```
(Client1) --> (Switch) --> (Internal Mesh Node) --> 
(Ad-Hoc WiFi Network) --> (Mesh Gateway) --> (Internet)
```

There could be any number of mesh nodes in the Ad-Hoc WiFi Network.  The route among the nodes is managed entirely with OLSRD.

I've done all of my testing with N150 USB wifi adapters that use the Ralink 5370 wireless chipset.  These adapters are cheap (~$7 USD), compact, and easy to come by.  They also use drivers that are bundled in the Raspbian distribution, making setup easy.  The N150 adapter tested included a threaded antenna connector that should make it easy to add a linear amplifier and aftermarket antenna (outside the scope of the HSMM-Pi project).

Project Components
==================
The project consists of a PHP web application that is used to configure and monitor the mesh node, and an installation shell script that installs dependencies and puts things in the right spots.  

The HSMM-Pi project is based on the Raspbian distribution of Debian customized for the Raspberry Pi.  Rather than providing an OS image for HSMM-Pi, I've instead created an installation script that will transform a newly-imaged Rasbian host into an HSMM-Pi node.  This has several benefits:
 * Greater transparency:  You can see exactly which changes are made to the base Raspbian system by looking at the install shell script.
 * Easier to host:  I only need to post the installation script and webapp files on Github and it's done.
 * Easier to seek support: The Raspbian distribution is widely used and supported, no need to introduce another variant.

Hardware Requirements
=====================

1.  Raspberry Pi (256MB or 512MB of RAM will work)
2.  USB WiFi adapter (tested with the N150 adapter using the Ralink 5370 chipset)
3.  SD memory card (4GB minimum)

Installation
============

1.  Download the Raspbian Wheezy 2013-05-25 disk image on your Mac/PC/whatever (http://www.raspberrypi.org/downloads)
2.  Write the image to a SD memory card.  This involves formatting the SD card; I recommend the steps described at http://elinux.org/RPi_Easy_SD_Card_Setup
3.  Insert the card into a Raspberry Pi
4.  Connect the wired Ethernet port on the Pi to a network with Internet access
5.  Apply power to the Pi
6.  Login to the Pi, either through an SSH session or the console, using the 'pi' account
7.  Run the Raspberry Pi Setup program:
```
sudo raspi-config
```
8.  Expand the filesystem to fill the SD memory card
9.  Change the password for the 'pi' account
10.  Run the following commands to download the HSMM-Pi project and install
```
git clone https://github.com/urlgrey/hsmm-pi.git
sh hsmm-pi/install.sh
```
11.  Login to the web application on the Pi:
http://(IP Address of Raspberry Pi)/hsmm-pi/
12.  Access the Admin account using the 'admin' username and 'changeme' password.
13.  Change the password for HSMM-Pi
14.  Configure the Pi as either an Internal or Gateway node


Internal Mesh Node Configuration
================================
This represents the minimum set of steps:

1.  Select Admin->Network from the menubar
2.  Configure the WiFi interface:
2a.  Specify an IP address that will be unique throughout the mesh network.  This will be different every mesh node.  A default of 10.201.5.1 is specified; you must change this.
3.  Configure the Wired interface:
3a.  Set the Wired interface mode to LAN
4.  Configure the Mesh settings
    4a.  Specify your amatuer radio callsign (i.e. KK6DCI)
    4b.  Specify your node name, likely a composition of your callsign and a unique number in your mesh (i.e. KK6DCI-7)
5.  Click 'Save'
6.  If successful, click the 'Reboot' button in the alert and proceed.


Gateway Node Configuration
================================
This represents the minimum set of steps:

1.  Select Admin->Network from the menubar
2.  Configure the WiFi interface:
2a.  Specify an IP address that will be unique throughout the mesh network.  This will be different every mesh node.  A default of 10.201.5.1 is specified; you must change this.
3.  Configure the Wired interface:
3a.  Set the Wired interface mode to WAN
4.  Configure the Mesh settings
    4a.  Specify your amatuer radio callsign (i.e. KK6DCI)
    4b.  Specify your node name, likely a composition of your callsign and a unique number in your mesh (i.e. KK6DCI-7)
5.  Click 'Save'
6.  If successful, click the 'Reboot' button in the alert and proceed.

