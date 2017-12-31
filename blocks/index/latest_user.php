<?php
global $site_config, $cache, $lang, $fluent;

$latestuser = $cache->get('latestuser');
if ($latestuser === false || is_null($latestuser)) {
    $latestuser = $fluent->from('users')
        ->select(null)
        ->select('id')
        ->where('status = ?', 'confirmed')
        ->where('perms < ?', bt_options::PERMS_STEALTH)
        ->orderBy('id DESC')
        ->limit(1)
        ->fetch();

    $latestuser = format_username($latestuser['id']);
    $cache->set('latestuser', $latestuser, $site_config['expires']['latestuser']);
}

$HTMLOUT .= "
        <a id='latestuser-hash'></a>
        <fieldset id='latestuser' class='header'>
            <legend class='flipper has-text-primary'><i class='fa fa-angle-up right10' aria-hidden='true'></i>{$lang['index_lmember']}</legend>
            <div class='bordered'>
                <div class='alt_bordered bg-00 level-item is-wrapped'>
                    {$lang['index_wmember']}&nbsp;{$latestuser}!
                </div>
            </div>
        </fieldset>";
