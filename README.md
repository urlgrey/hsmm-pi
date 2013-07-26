HSMM-Pi
=======

A set of tools to easily configure the Raspberry Pi to function as a High-Speed 
Multimedia (HSMM) wireless node.  HSMM offers radio amateurs (HAMs) the ability 
to operate high-speed data networks in the frequencies shared with unlicenced 
users of 802.11 b/g/n networking equipment.  HAMs can operate HSMM at higher 
power with larger antennas than are available to unlicensed users.  The HSMM-Pi 
project makes it possible to run an HSMM mesh node on the Raspberry Pi.

Modes
=====

Mesh Gateway Node
=================
The Mesh Gateway is capable of routing traffic throughout the mesh, and 
provides an Internet link to the mesh through the wired Ethernet port.  The 
gateway obtains a DHCP lease on the wired interface, and advertises its 
Internet link to member of the mesh using OLSRD.


Internal Mesh Node
==================
This node provides is capable of routing traffic throughout the mesh, and 
providing mesh access to any hosts connected to its wired Ethernet port.  
The node runs a DHCP server that can issue DHCP leases to any hosts on the 
wired connection.  It also runs a DNS server that can provide name resolution 
for mesh nodes and Internet hosts.

The following sequence shows how the two types of can be deployed

(Client1) --> (Switch) --> (Internal Mesh Node) --> (Ad-Hoc WiFi Network) --> (Mesh Gateway) --> (Internet)

There could be any number of mesh nodes in the Ad-Hoc WiFi Network used to 
route traffic between the client and its destination.  The route among the 
nodes is managed entirely with OLSRD.

Project Components
==================
The project consistent of a PHP web application that is used to configure and monitor the mesh node, and an installation shell script that installs dependencies and puts things in the right spots.  


The HSMM-Pi project is based on the Raspbian distribution of Debian customized for the Raspberry Pi.  Rather than providing an OS image for HSMM-Pi, I've instead created an installation script that will transform a newly-imaged Rasbian host into an HSMM-Pi node.  This has several benefits:
 * Greater transparency:  You can see exactly which changes are made to the base Raspbian system by looking at the install shell script.
 * Easier to host:  I only need to post the installation script and webapp files on Github and it's done.
 * Easier to seek support: The Raspbian distribution is widely used and supported, no need to introduce another variant.

Installation
============

1) Download the Raspbian disk image on your Mac/PC/whatever
2) Write the image to a SD memory card
3) Insert the card into a Raspberry Pi
4) Connect the wired Ethernet port on the Pi to a network with Internet access
5) Apply power to the Pi
6) Login to the Pi, either through an SSH session or the console, using the 'pi' account
7) Run the following commands to download the HSMM-Pi project and install
git clone https://github.com/urlgrey/hsmm-pi.git
sh hsmm-pi/install.sh
8) Login to the web application on the Pi:
http://(IP Address of Raspberry Pi)/hsmm-pi/
9) Access the Admin account using the 'admin' username and 'changeme' password.
10) Change the password


Internal Mesh Node Configuration
================================
This represents the minimum set of steps:

1) Select Admin->Network from the menubar
2) Configure the WiFi interface:
2a) Specify an IP address that will be unique throughout the mesh network.  This will be different every mesh node.  A default of 10.201.5.1 is specified; you must change this.
3) Configure the Wired interface:
3a) Set the Wired interface mode to LAN
4) Configure the Mesh settings
4a) Specify your amatuer radio callsign (i.e. KK6DCI)
4b) Specify your node name, likely a composition of your callsign and a unique number in your mesh (i.e. KK6DCI-7)
5) Click 'Save'
6) If successful, click the 'Reboot' button in the alert and proceed.


Gateway Node Configuration
================================
This represents the minimum set of steps:

1) Select Admin->Network from the menubar
2) Configure the WiFi interface:
2a) Specify an IP address that will be unique throughout the mesh network.  This will be different every mesh node.  A default of 10.201.5.1 is specified; you must change this.
3) Configure the Wired interface:
3a) Set the Wired interface mode to WAN
4) Configure the Mesh settings
4a) Specify your amatuer radio callsign (i.e. KK6DCI)
4b) Specify your node name, likely a composition of your callsign and a unique number in your mesh (i.e. KK6DCI-7)
5) Click 'Save'
6) If successful, click the 'Reboot' button in the alert and proceed.

