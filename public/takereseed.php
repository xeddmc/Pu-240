<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Message;

require_once __DIR__ . '/../include/bittorrent.php';
check_user_status();
$pm_what = isset($_POST['pm_what']) && $_POST['pm_what'] === 'last10' ? 'last10' : 'owner';
$reseedid = (int) $_POST['reseedid'];
$uploader = (int) $_POST['uploader'];
$name = $_POST['name'];
global $container, $site_config, $CURUSER;

$dt = TIME_NOW;
$subject = 'Request reseed!';
$msg = "@{$CURUSER['username']} asked for a reseed on [url={$site_config['paths']['baseurl']}/details.php?id={$reseedid}][class=has-text-success]{$name}[/class][/url]![br][br]Thank You!";
$msgs_buffer = [];
if ($pm_what === 'last10') {
    $res = sql_query('SELECT s.userid, s.torrentid FROM snatched AS s WHERE s.torrentid =' . sqlesc($reseedid) . " AND s.seeder = 'yes' LIMIT 10") or sqlerr(__FILE__, __LINE__);
    while ($row = mysqli_fetch_assoc($res)) {
        $msgs_buffer[] = [
            'sender' => 0,
            'receiver' => $row['userid'],
            'added' => $dt,
            'msg' => $msg,
            'subject' => $subject,
        ];
    }
} elseif ($pm_what === 'owner') {
    $msgs_buffer[] = [
        'sender' => 0,
        'receiver' => $uploader,
        'added' => $dt,
        'msg' => $msg,
        'subject' => $subject,
    ];
}

if (count($msgs_buffer) > 0) {
    $message_stuffs = $container->get(Message::class);
    $message_stuffs->insert($msgs_buffer);
    $session->set('is-success', 'PM was sent! Now wait for a seeder!');
} else {
    $session->set('is-warning', 'There were no users to PM!');
}
sql_query('UPDATE torrents SET last_reseed = ' . $dt . ' WHERE id=' . sqlesc($reseedid)) or sqlerr(__FILE__, __LINE__);
$cache = $container->get(Cache::class);
$cache->update_row('torrent_details_' . $reseedid, [
    'last_reseed' => $dt,
], $site_config['expires']['torrent_details']);
if ($site_config['bonus']['on']) {
    sql_query('UPDATE users SET seedbonus = seedbonus-10.0 WHERE id=' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
    $update['seedbonus'] = ($CURUSER['seedbonus'] - 10);
    $cache->update_row('user_' . $CURUSER['id'], [
        'seedbonus' => $update['seedbonus'],
    ], $site_config['expires']['user_cache']);
}

header("Refresh: 0; url={$site_config['paths']['baseurl']}/details.php?id=$reseedid");
