#!/usr/bin/perl -w

`cp ./www/helpers.php /tmp`;


open FILE, ">./www/helpers.php" or die $!;
open FILE2, "/tmp/helpers.php" or die $!;

while ($line = <FILE2>) {
    $line =~ s/\$www_root\s*=\s*\"http:\/\/localhost\";/\$www_root = \"http:\/\/ec2-23-22-14-153.compute-1.amazonaws.com\";/;
    print FILE $line;
}

close FILE;
close FILE2;

`./z-test-push.sh`;
`cp /tmp/helpers.php ./www`;