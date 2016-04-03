#!/usr/bin/env perl
use strict;
use warnings;

use Net::GPSD3;
my $host=shift || undef;
my $port=shift || undef;

my $lock_file = '/var/run/read_gps_coordinates.lock';
`touch $lock_file`; 
my $gpsd=Net::GPSD3->new(host=>$host, port=>$port); #default host port as undef


$gpsd->addHandler(\&tpv);
$gpsd->watch;
unlink '/var/run/read_gps_coordinates.lock' or warn "Could not unlink $lock_file: $!";

sub tpv {
    my $tpv=shift;
    return unless $tpv->class eq "TPV";

    # have observed cases where the tpv structure is missing these fields, bail
    if ((0 == length($tpv->lat // '')) || (0 == length($tpv->lon // ''))) {
	unlink '/var/run/read_gps_coordinates.lock' or warn "Could not unlink $lock_file: $!";
        exit -1;
    }

    open(COORDS, '>/var/run/latlong-input-olsrd') or die "Cannot open file for write";
    printf COORDS "%s,%s", $tpv->lat, $tpv->lon;
    close(COORDS);
    unlink '/var/run/read_gps_coordinates.lock' or warn "Could not unlink $lock_file: $!";
    exit 0;
}
