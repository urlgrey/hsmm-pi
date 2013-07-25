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
