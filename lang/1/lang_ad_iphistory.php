<?php

declare(strict_types = 1);
global $site_config, $id, $username2, $username;

$lang = [
    //Errors
    'stderr_error' => 'Error',
    'stderr_denied' => 'Access denied.',
    'stderr_badid' => 'You have submitted a bad or invalid user ID.',
    'stderr_noid' => 'No user with this ID exists.',
    //History
    'iphistory_usedby' => 'IP addresses used by ',
    'iphistory_total_unique' => 'Total <u><b>Unique</b></u> IP Addresses',
    'iphistory_total_logged' => 'Has Logged In With ',
    'iphistory_single' => 'Single',
    'iphistory_banned' => 'Banned',
    'iphistory_dupe' => 'Dupe Used',
    'iphistory_last' => 'Last',
    'iphistory_address' => 'Address',
    'iphistory_isphost' => 'ISP/Host Name',
    'iphistory_location' => 'Location',
    'iphistory_type' => 'Type',
    'iphistory_seedbox' => 'SeedBox',
    'iphistory_delete' => 'Delete',
    'iphistory_ban' => 'Ban',
    'iphistory_notfound' => 'Not Found',
    'iphistory_no' => 'No',
    'iphistory_yes' => 'yes',
    'iphistory_stdhead' => "{$username}'s IP History",
    'iphistory_browse' => 'Browse: ',
    'iphistory_announce' => 'Announce: ',
    'iphistory_login' => 'Login: ',
    'iphistory_wipe' => 'History Wipe:',
    'iphistory_justwipe' => ' has just wiped IP: ',
    'iphistory_from' => " from (<a href='{$site_config['paths']['baseurl']}/userdetails.php?id=$id'><b>$username2</b></a>)'s Ip History",
];
