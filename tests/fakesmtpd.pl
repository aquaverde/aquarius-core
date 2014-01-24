#!c:\Perl\bin\perl.exe

# A Fake SMTP Daemon for debugging SMTP connections
# (c) 2000,2001 John Brewer DBA Jera Design
# You are permitted to use, modify and redistribute this
# code under the terms of the Perl Artistic License.


use IO::Socket;

STDOUT->autoflush(1);

my $echo = 1;
my $running = 1;
my $port = 2525;

my $server = IO::Socket::INET->new(LocalPort => $port,
                                    Type => SOCK_STREAM,
                                    Reuse => 1,
                                    Listen => 10 )
    or die "Couldn't open server on port $port";

print "Listening on port $port. Press Ctrl-C to exit\n"; 

my $client;

while ($client = $server->accept()) {
    print "------------------------------------------------------------------------\n";
    sendLine("220 Service ready");
    while ($running) {
        my $command = uc receiveLine();
        if (!$command || $command eq "QUIT") {
            sendLine("221 Later");
            $client->close();
            last;
        }
        elsif ($command eq "DATA") {
            sendLine("354 Ready for data");
            my $data = receiveData();
            sendLine("250 Thanks for the data");
        }
        elsif ($command =~ /fail/i) {
            sendLine("521 Don't feel like it");
        }
        else {
            sendLine("250 Whatever");
        }
    }
}

sub sendLine {
    my $message = $_[0];
    print ">$message\n" if echo;
    $client->send("$message\r\n");
}

sub receiveLine {
    my $line;
    $client->recv($line, 1024);
    return decodeLine($line);
}

sub receiveData {
    my $fullLine;
    my $line;
    while ($fullLine !~ /[\r\n]\.[\r\n]/) {
        $client->recv($line, 255);
        $fullLine .= $line;
    }
    return decodeLine($fullLine);
}

sub decodeLine {
    my $line = $_[0];

    if ($echo) {
        my $visline = $line;
        $visline =~ s/\r/\\r/g;     # make \r visible
        $visline =~ s/\n/\\n/g;     # make \n visible
        $visline =~ s/(\\r|\\n)([^\\])/$1\n<$2/g;   # break line after '\r', '\n', '\r\n, or '\n\r'
        $visline =~ s/(\\r\\n)(\\r\\n)/$1\n<$2/g;   # break line between adjacent sets of '\r\n'
        print "<$visline\n";
    }
    $line =~ s/[\r\n]//g;
    return $line;
}
