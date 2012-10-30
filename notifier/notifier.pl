#!/usr/bin/perl -w

use strict;
use warnings;
use DBI;
use Term::ANSIColor;
use IO::Handle;
use HTML::Template;
use Switch;
use JSON::XS;
use Crypt::OpenSSL::AES;
use Crypt::CBC;
use MIME::Base64;
use URI::Escape;
use WWW::Mailgun;
use Net::Address::IP::Local;
use Getopt::Long qw(GetOptionsFromArray);
use Proc::ProcessTable;


# Force autoflush on std/stderr, so we log
# what happens if a process dies
STDERR->autoflush(1);
STDOUT->autoflush(1);

my $ip = Net::Address::IP::Local->public;
if (!defined($ip) || $ip eq "127.0.0.1") {
    die "Error: Unable to determine IP address for this process.\n";
}


# Get arguments to determin if we're in debug mode (default) or production mode
# (use: --production)
my $G_DEBUG;
my $G_SLEEP_SECONDS = 180;
GetOptionsFromArray(\@ARGV,
		    "production" => \$G_DEBUG,
		    "sleep=i" => \$G_SLEEP_SECONDS);


if (!defined($G_DEBUG)) {
    $G_DEBUG = 1;
} else {
    $G_DEBUG = 0;
    print "[$ip]\n";
    if ($ip ne "10.215.122.185") {
	die "Error: attempting to use --production from non prod machine!\n";
    }
}

my $pid = $$;
my $t = new Proc::ProcessTable;

foreach my $p ( @{$t->table} ){
    if ($p->cmndline =~ /perl[^n]{1,7}notifier\.pl/ && $p->pid ne $pid) {
	die "Error: it appears another instance of notifier is running!\n";
    }
}

################################ CONSTANTS ###############################
my $G_ZIPIO;
my $G_FB_APP_ID;
my $G_WWW_ROOT;
my $G_DATABASE;

if ($G_DEBUG) {
    $G_WWW_ROOT = "http://localhost";
    $G_ZIPIO = "zipiyo";
    $G_DATABASE = "Zipiyo";
    $G_FB_APP_ID = "255929901188660";
} else {
    $G_WWW_ROOT = "http://zipio.com";
    $G_ZIPIO = "zipio";
    $G_DATABASE = "Zipio";
    $G_FB_APP_ID = "457795117571468";
}

my $G_S3_BUCKET_NAME = "s3.".$G_ZIPIO.".com";
my $G_S3_FOLDER_NAME = "photos";
my $G_S3_ROOT = "http://$G_S3_BUCKET_NAME/$G_S3_FOLDER_NAME";
my $G_FOUNDERS_EMAIL_ADDRESS = "$G_ZIPIO <founders@"."$G_ZIPIO.com>";

############################################################################






################################ GLOBALS ###################################
my $json_coder = JSON::XS->new->utf8->allow_nonref;
my $cipher = Crypt::CBC->new(
    {
        'key'         => 'length16length16',
        'cipher'      => 'Crypt::OpenSSL::AES',
        'iv'          => '1234567812345678',
        'literal_key' => 1,
        'header'      => 'none',
        keysize       => 128 / 8
    }
);

my %template_files;



$template_files{1} = "./templates/add_album.tmpl"; # ACTION_ADD_ALBUM
$template_files{2} = "./templates/add_albumphoto.tmpl"; # ACTION_ADD_ALBUMPHOTO
$template_files{3} = "./templates/add_comment.tmpl"; # ACTION_ADD_COMMENT
$template_files{4} = "./templates/"; # ACTION_LIKE_ALBUM
$template_files{5} = "./templates/like_albumphoto.tmpl"; # ACTION_LIKE_ALBUMPHOTO
$template_files{6} = "./templates/like_comment.tmpl"; # ACTION_LIKE_COMMENT
$template_files{7} = "./templates/edit_caption.tmpl"; # ACTION_EDIT_CAPTION
$template_files{8} = "./templates/"; # ACTION_EDIT_COMMENT
$template_files{9} = "./templates/"; # ACTION_DELETE_ALBUM
$template_files{10} = "./templates/"; # ACTION_DELETE_ALBUMPHOTO
$template_files{11} = "./templates/"; # ACTION_DELETE_COMMENT
$template_files{12} = "./templates/"; # ACTION_ROTATE_ALBUMPHOTO
$template_files{13} = "./templates/filter_albumphoto.tmpl"; # ACTION_FILTER_ALBUMPHOTO
$template_files{14} = "./templates/"; # ACTION_CHANGE_ALBUM_COVER
############################################################################


my @db_params = ("DBI:mysql:$G_DATABASE;host=localhost;mysql_ssl=1",
		 "zipio", "daewoo",
		 {RaiseError => 1, AutoCommit => 1});

my $dbh;
my $sth;

my $mg = WWW::Mailgun->new({
    key => 'key-68imhgvpoa-6uw3cl8728kcs9brvlmr9',
    domain => 'zipio.com',
    from => 'founders@zipio.com'
});

# Main loop
while (1) {
    $dbh = DBI->connect_cached(@db_params) || die "Failed DB connection!\n";
    my $query = "select event_id from LastNotifiedPosition where id=1";
    $sth = $dbh->prepare($query);
    $sth->execute() || die "Failed DB query [$query]\n";
    my $event_id = $sth->fetchrow_array();
    if (!defined($event_id)) {
	die "Unable to find event_id from LastNotifiedPosition table!\n";
    }
    
    print "ID of last event that has been processed: $event_id.\n";
    
    $query = "select * from Events where id > $event_id order by id";
    $sth = $dbh->prepare($query);
    $sth->execute() || die "Failed DB query [$query]\n";
    my $event_ref;
    my $last_event_id;
    my %emails;
    my %activity_count;
    while ($event_ref = $sth->fetchrow_hashref) {
	process_event($event_ref, \%template_files, \%emails,
		      \%activity_count, $dbh);
	
	print "Event: ".$$event_ref{"id"}."\n";
	$last_event_id = $$event_ref{"id"};
    }
    
    
    if (defined($last_event_id)) {
	print "Last id $last_event_id\n";
	$query = "update LastNotifiedPosition set event_id=$last_event_id where id=1";
	$sth = $dbh->prepare($query);
	$sth->execute() || die "Failed DB query [$query]\n";
	
	foreach my $email (keys %emails) {
	    print "$email:".$activity_count{$email}."\n";
	    #print $emails{$email},"\n";
	    
	    my $update = "updates";
	    if ($activity_count{$email} == 1) {
		$update = "update";
	    }

	    my $template = HTML::Template->new(filename => "./templates/email_container_template.tmpl");
	    $template->param(email_message_loop => $emails{$email});
	    my $html = $template->output;
	    print "$html\n";
	    #$mg->send({
	#	to => $email,
	#	subject => "Zipio notification: $activity_count{$email} $update",
	#	html => $html});
	}
	
    } else {
	print "No events to process, sleeping for $G_SLEEP_SECONDS seconds... ".time()."\n";
	sleep($G_SLEEP_SECONDS);
    }
}


############################################################################
sub process_event {
    my $event_ref = shift;
    my $template_files_ref = shift;
    my $emails_ref = shift;
    my $activity_count_ref = shift;
    my $dbh = shift;

    my %users_to_notify;
    get_users_to_notify(\%users_to_notify, $event_ref, $dbh);

    foreach my $user_id (keys %users_to_notify) {
	my $email = get_attribute($user_id, "email", "Users", $dbh);
	my $message = construct_message($user_id, $event_ref,
					$template_files_ref, $dbh);
	my %email_section;
	if (defined($$emails_ref{$email})) {
	    %email_section =  ( email_section => $message,
				__FIRST__ => 0);
	    push(@{$$emails_ref{$email}}, \%email_section);
	    $$activity_count_ref{$email}++;
	} else {
	    %email_section =  ( email_section => $message,
				__FIRST__ => 1);
	    my @array = ( \%email_section );
	    $$emails_ref{$email} = \@array;
	    $$activity_count_ref{$email}++;
	}
    }
}


############################################################################
# Policy for who should be notified for different event action_types
sub get_users_to_notify {
    my $users_to_notify_ref = shift;
    my $event_ref = shift;
    my $dbh = shift;
    
    switch ($$event_ref{"action_type"}) {
	case 1 { # ACTION_ADD_ALBUM
	    add_album_followers($$event_ref{"album_id"},
				$users_to_notify_ref);
	}
	case 2 { # ACTION_ADD_ALBUMPHOTO
	    $$users_to_notify_ref{$$event_ref{"album_owner_id"}} = 1;
	    add_album_collaborators($$event_ref{"album_id"},
				    $users_to_notify_ref,
				    $dbh);
	    add_album_followers($$event_ref{"album_id"},
				$users_to_notify_ref,
				$dbh);
	    $$users_to_notify_ref{$$event_ref{"album_owner_id"}} = 1;
	}
	case 3 { # ACTION_ADD_COMMENT
	    $$users_to_notify_ref{$$event_ref{"album_owner_id"}} = 1;
	    add_album_collaborators($$event_ref{"album_id"},
				    $users_to_notify_ref,
				    $dbh);
	}
	case 4 { # ACTION_LIKE_ALBUM
	    # Not supported at this time ...
	}
	case 5 { # ACTION_LIKE_ALBUMPHOTO
	    $$users_to_notify_ref{$$event_ref{"album_owner_id"}} = 1;
	    $$users_to_notify_ref{$$event_ref{"albumphoto_owner_id"}} = 1;
	}
	case 6 { # ACTION_LIKE_COMMENT
	    $$users_to_notify_ref{$$event_ref{"album_owner_id"}} = 1;
	    $$users_to_notify_ref{$$event_ref{"albumphoto_owner_id"}} = 1;
	    $$users_to_notify_ref{$$event_ref{"commenter_id"}} = 1;
	}
	case 7 { # ACTION_EDIT_CAPTION
	    $$users_to_notify_ref{$$event_ref{"album_owner_id"}} = 1;
	    $$users_to_notify_ref{$$event_ref{"albumphoto_owner_id"}} = 1;
	}
	case 8 { # ACTION_EDIT_COMMENT
	    # Not supported at this time ...
	}
	case 9 { # ACTION_DELETE_ALBUM
	    # Not supported at this time ...
	}
	case 10 { # ACTION_DELETE_ALBUMPHOTO
	    # Not supported at this time ...
	}
	case 11 { # ACTION_DELETE_COMMENT
	    # Not supported at this time ...
	}
	case 12 { # ACTION_ROTATE_ALBUMPHOTO
	    # Not supported at this time ...
	}
	case 13 { # ACTION_FILTER_ALBUMPHOTO
	    $$users_to_notify_ref{$$event_ref{"album_owner_id"}} = 1;
	    $$users_to_notify_ref{$$event_ref{"albumphoto_owner_id"}} = 1;
	}
	case 14 { # ACTION_CHANGE_ALBUM_COVER
	    $$users_to_notify_ref{$$event_ref{"album_owner_id"}} = 1;
	}
    }

    # Make sure never to notify the person who was responsible
    # for the event occurring, because they obviously already
    # know about it.
    delete $$users_to_notify_ref{$$event_ref{"actor_id"}};
}

############################################################################
sub add_album_collaborators {
    my $album_id = shift;
    my $users_to_notify_ref = shift;
    my $dbh = shift;

    my $query =
	"select collaborator_id from Collaborators where album_id='$album_id'";

    my $sth = $dbh->prepare($query);
    my $collaborator_id;
    $sth->execute() || die "Failed DB query [$query]\n";
    $sth->bind_columns(\$collaborator_id);
    while ($sth->fetch) {
	$$users_to_notify_ref{$collaborator_id} = 1;
    }
}

############################################################################
sub add_album_followers {
    my $album_id = shift;
    my $users_to_notify_ref = shift;
    my $dbh = shift;

    my $query =
	"select user_id from AlbumFollowers where album_id='$album_id'";

    my $sth = $dbh->prepare($query);
    my $follower_id;
    $sth->execute() || die "Failed DB query [$query]\n";
    $sth->bind_columns(\$follower_id);
    while ($sth->fetch) {
	$$users_to_notify_ref{$follower_id} = 1;
    }
}

############################################################################
sub construct_message {
    my $user_to_notify_id = shift;
    my $event_ref = shift;
    my $template_files_ref = shift;
    my $dbh = shift;

    my $template = HTML::Template->new(filename => $$template_files_ref{$$event_ref{"action_type"}});

    my $object_owner_id;
    my $album_owner_username = get_attribute($$event_ref{"album_owner_id"},
					     "username", "Users", $dbh);
    my $possessive_album_owner_username;
    my $possessive_albumphoto_owner_username;
    my $commenter_username;
    my $albumphoto_s3;
    my $comment;
    my $display_album_pretty_link;
    my $request = get_request($user_to_notify_id, $dbh);

    my $actor_username = get_attribute($$event_ref{"actor_id"},
				       "username", "Users", $dbh);
    my $album_handle = get_attribute($$event_ref{"album_id"},
				     "handle", "Albums", $dbh);

    if (defined($$event_ref{"album_owner_id"})) {
	if ($$event_ref{"album_owner_id"} ==
	    $$event_ref{"actor_id"}) {
	    $possessive_album_owner_username = "their";
	} elsif ($$event_ref{"album_owner_id"} ==
		  $user_to_notify_id) {
	    $possessive_album_owner_username = "your";
	} else {
	    $possessive_album_owner_username =
		get_attribute($$event_ref{"album_owner_id"},
			      "username", "Users", $dbh) . "'s";
	}

	$display_album_pretty_link = $G_WWW_ROOT . "/" . $album_handle;
	$object_owner_id = $$event_ref{"album_owner_id"};
    }
    if (defined($$event_ref{"albumphoto_owner_id"})) {
	if ($$event_ref{"albumphoto_owner_id"} ==
	    $$event_ref{"actor_id"}) {
	    $possessive_albumphoto_owner_username = "their own";
	} elsif ($$event_ref{"albumphoto_owner_id"} ==
		  $user_to_notify_id) {
	    $possessive_albumphoto_owner_username = "your";
	} else {
	    $possessive_albumphoto_owner_username =
		get_attribute($$event_ref{"albumphoto_user_id"},
			      "username", "Users", $dbh) . "'s";
	}

	$albumphoto_s3 = get_albumphoto_s3($$event_ref{"albumphoto_id"}, $dbh);
	$object_owner_id = $$event_ref{"albumphoto_owner_id"};
    }
    if (defined($$event_ref{"commenter_id"})) {
	$comment = get_attribute($$event_ref{"comment_id"},
				 "comment", "Comments", $dbh);
	$commenter_username =
	    get_attribute($$event_ref{"commenter_id"},
			  "username", "Users", $dbh);
	$object_owner_id = $$event_ref{"commenter_id"};
    }

    my $object_owner_username = get_object_owner_username($object_owner_id,
							  $user_to_notify_id,
							  $dbh);

    switch ($$event_ref{"action_type"}) {
	case 1 { # ACTION_ADD_ALBUM

	}
	case 2 { # ACTION_ADD_ALBUMPHOTO
	    $template->param(actor_username => $actor_username);
	    $template->param(album_owner_username => $album_owner_username);
	    $template->param(possessive_album_owner_username => $possessive_album_owner_username);
	    $template->param(album_handle => $album_handle);
	    $template->param(request => $request);
	    $template->param(g_s3_root => $G_S3_ROOT);
	    $template->param(albumphoto_s3 => $albumphoto_s3);
	    $template->param(web_root => $G_WWW_ROOT);
	    $template->param(albumphoto_id => $$event_ref{"albumphoto_id"});
	}
	case 3 { # ACTION_ADD_COMMENT
	    $template->param(actor_username => $actor_username);
	    $template->param(album_owner_username => $album_owner_username);
	    $template->param(possessive_album_owner_username => $possessive_album_owner_username);
	    $template->param(possessive_albumphoto_owner_username =>
			     $possessive_albumphoto_owner_username);
	    $template->param(comment => $comment);
	    $template->param(album_handle => $album_handle);
	    $template->param(request => $request);
	    $template->param(g_s3_root => $G_S3_ROOT);
	    $template->param(albumphoto_s3 => $albumphoto_s3);
	    $template->param(web_root => $G_WWW_ROOT);
	    $template->param(albumphoto_id => $$event_ref{"albumphoto_id"});
	}
	case 4 { # ACTION_LIKE_ALBUM
	    # Not supported at this time ...
	}
	case 5 { # ACTION_LIKE_ALBUMPHOTO
	    $template->param(actor_username => $actor_username);
	    $template->param(album_owner_username => $album_owner_username);
	    $template->param(possessive_album_owner_username => $possessive_album_owner_username);
	    $template->param(album_handle => $album_handle);
	    $template->param(request => $request);
	    $template->param(g_s3_root => $G_S3_ROOT);
	    $template->param(albumphoto_s3 => $albumphoto_s3);
	    $template->param(web_root => $G_WWW_ROOT);
	    $template->param(albumphoto_id => $$event_ref{"albumphoto_id"});
	}
	case 6 { # ACTION_LIKE_COMMENT
	    $template->param(actor_username => $actor_username);
	    $template->param(album_owner_username => $album_owner_username);
	    $template->param(comment => $comment);
	    $template->param(commenter_username => $commenter_username);
	    $template->param(album_handle => $album_handle);
	    $template->param(request => $request);
	    $template->param(g_s3_root => $G_S3_ROOT);
	    $template->param(albumphoto_s3 => $albumphoto_s3);
	    $template->param(web_root => $G_WWW_ROOT);
	    $template->param(albumphoto_id => $$event_ref{"albumphoto_id"});
	}
	case 7 { # ACTION_EDIT_CAPTION
	    my $caption = get_attribute($$event_ref{"albumphoto_id"},
					"caption", "AlbumPhotos", $dbh);
	    $template->param(actor_username => $actor_username);
	    $template->param(album_owner_username => $album_owner_username);
	    $template->param(possessive_albumphoto_owner_username =>
			     $possessive_albumphoto_owner_username);
	    $template->param(caption => $caption);
	    $template->param(album_handle => $album_handle);
	    $template->param(request => $request);
	    $template->param(g_s3_root => $G_S3_ROOT);
	    $template->param(albumphoto_s3 => $albumphoto_s3);
	    $template->param(web_root => $G_WWW_ROOT);
	    $template->param(albumphoto_id => $$event_ref{"albumphoto_id"});
	}
	case 8 { # ACTION_EDIT_COMMENT
	    # Not supported at this time ...
	}
	case 9 { # ACTION_DELETE_ALBUM
	    # Not supported at this time ...
	}
	case 10 { # ACTION_DELETE_ALBUMPHOTO
	    # Not supported at this time ...
	}
	case 11 { # ACTION_DELETE_COMMENT
	    # Not supported at this time ...
	}
	case 12 { # ACTION_ROTATE_ALBUMPHOTO
	    # Not supported at this time ...
	}
	case 13 { # ACTION_FILTER_ALBUMPHOTO
	    $template->param(actor_username => $actor_username);
	    $template->param(album_owner_username => $album_owner_username);
	    $template->param(possessive_albumphoto_owner_username => $possessive_albumphoto_owner_username);
	    $template->param(album_handle => $album_handle);
	    $template->param(request => $request);
	    $template->param(g_s3_root => $G_S3_ROOT);
	    $template->param(albumphoto_s3 => $albumphoto_s3);
	    $template->param(web_root => $G_WWW_ROOT);
	    $template->param(albumphoto_id => $$event_ref{"albumphoto_id"});
	}
	case 14 { # ACTION_CHANGE_ALBUM_COVER

	}
    }

    return $template->output;
}

############################################################################
sub get_object_owner_username {
    my $object_owner_id = shift;
    my $user_to_notify_id = shift;
    my $dbh = shift;

    if ($object_owner_id == $user_to_notify_id) {
	return "your";
    } else {
	return
	    get_attribute($object_owner_id, "username", "Users", $dbh) . "'s";
    }
}


############################################################################
sub get_attribute {
    my $id = shift;
    my $attribute = shift;
    my $table = shift;
    my $dbh = shift;

    my $query = "select $attribute from $table where id='$id'";
    my $attribute_value;
    my $sth = $dbh->prepare($query);
    $sth->execute() || die "Failed DB query [$query]\n";
    $sth->bind_columns(\$attribute_value);
    $sth->fetch;

    return $attribute_value;
}


############################################################################
sub get_albumphoto_s3 {
    my $albumphoto_id = shift;
    my $dbh = shift;

    my $query =
	"select s3_url, filtered from Photos left join AlbumPhotos on ".
	"AlbumPhotos.photo_id = Photos.id where ".
	"AlbumPhotos.id='$albumphoto_id'";
    my $s3_url;
    my $filtered;
    my $sth = $dbh->prepare($query);
    $sth->execute() || die "Failed DB query [$query]\n";
    $sth->bind_columns(\$s3_url, \$filtered);
    $sth->fetch;

    $s3_url .= "_cropped";
    if ($filtered) {
	$s3_url .= "_filtered";
    }
    return $s3_url;
}

############################################################################
sub get_request {
    my $user_id = shift;
    my $dbh = shift;

    my @arr = ($user_id, time());
    my %hash = ( "user_id" => $user_id,
		 "timestamp" => time() );

    return uri_escape(encode_base64($cipher->encrypt($json_coder->encode(\%hash)), ""));
}
