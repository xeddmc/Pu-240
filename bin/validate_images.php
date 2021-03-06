<?php

declare(strict_types = 1);

use Pu239\ImageProxy;

require_once __DIR__ . '/../include/bittorrent.php';
global $container, $site_config;

$site_config['cache']['driver'] = 'memory';
$image_proxy = $container->get(ImageProxy::class);
$paths = [
    IMAGES_DIR . 'proxy',
];

$optimize = !empty($argv[1]) && $argv[1] === 'optimize' ? true : false;
$dirsize = $o = $i = 0;
foreach ($paths as $path) {
    $dirsize += GetDirectorySize($path, false, false);
    $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
    foreach ($objects as $name => $object) {
        if (!exif_imagetype($name)) {
            if (basename($name) === '.gitignore') {
                continue;
            }
            ++$i;
            unlink($name);
            echo "$name \n";
        } else {
            if ($optimize) {
                $image_proxy->optimize_image($name);
            }
            ++$o;
        }
    }
}
$dirsize = mksize($dirsize);
echo "$o images validated
Images size: $dirsize
$i bad images removed\n";
