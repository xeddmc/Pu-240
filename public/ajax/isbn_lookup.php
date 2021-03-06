<?php

declare(strict_types = 1);

use Pu239\Torrent;
use Rakit\Validation\Validator;

require_once __DIR__ . '/../../include/bittorrent.php';
require_once INCL_DIR . 'function_books.php';
check_user_status();
header('content-type: application/json');
global $container;

$_POST['isbn'] = str_replace([
    ' ',
    '_',
    '-',
], '', $_POST['isbn']);
$validator = $container->get(Validator::class);
$validation = $validator->validate($_POST, [
    'isbn' => 'required|integer',
    'tid' => 'required|integer',
    'name' => 'required',
]);
if ($validation->fails()) {
    echo json_encode(['content' => 'Invalid or missing parameters']);
    die();
}
$torrents_class = $container->get(Torrent::class);
$torrent = $torrents_class->get((int) $_POST['tid']);
$poster = !empty($torrent['poster']) ? $torrent['poster'] : '';
$book_info = get_book_info((!empty($_POST['isbn']) ? $_POST['isbn'] : '000000'), htmlsafechars($_POST['name']), $tid, $poster);
if (!empty($book_info)) {
    echo json_encode(['content' => $book_info[0]]);
    die();
}

echo json_encode(['content' => 'Lookup Failed']);
die();
