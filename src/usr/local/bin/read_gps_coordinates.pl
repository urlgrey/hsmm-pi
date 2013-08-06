#!/usr/bin/perl
use strict;
use warnings;

use Net::GPSD3;
my $host=shift || undef;
my $port=shift || undef;

my $gpsd=Net::GPSD3->new(host=>$host, port=>$port); #default host port as undef

$gpsd->addHandler(\&tpv);
$gpsd->watch;

sub tpv {
    my $tpv=shift;
    return unless $tpv->class eq "TPV";
    open(COORDS, '>/var/run/latlong-input-olsrd') or die "Cannot open file for write";
    printf COORDS "%s,%s", $tpv->lat, $tpv->lon;
    close(COORDS);
    exit 0;
}
