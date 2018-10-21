<?php

require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'pager_functions.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $lang;

$lang = array_merge($lang, load_language('ad_modded_torrents'));
$modes = [
    'today',
    'yesterday',
    'unmodded',
];
$HTMLOUT = '';
$links = "
    <ul class='level-center bg-06'>
        <li class='altlink margin20'>
            <a href='{$_SERVER['PHP_SELF']}?tool={$_GET['tool']}&amp;type=today' data-toggle='tooltip' data-placement='top' title='Tooltip on top'>" . $lang['mtor_modded_today'] . "</a>
        </li>
        <li class='altlink margin20'>
            <a href='{$_SERVER['PHP_SELF']}?tool={$_GET['tool']}&amp;type=yesterday' >" . $lang['mtor_modded_yesterday'] . "</a>
        </li>
        <li class='altlink margin20'>
            <a href='{$_SERVER['PHP_SELF']}?tool={$_GET['tool']}&amp;type=unmodded' >" . $lang['mtor_all_unmodded_torrents'] . '</a>
        </li>
    </ul>';

function do_sort($arr, $empty = false)
{
    global $lang, $site_config;

    $count = $arr->num_rows;
    $ret_html = '';
    if ($empty) {
        if ($count < 1) {
            return false;
        }
        while ($res = mysqli_fetch_assoc($arr)) {
            $returnto = !empty($_SERVER['REQUEST_URI']) ? '&amp;returnto=' . urlencode($_SERVER['REQUEST_URI']) : '';
            $ret_html .= "
                <tr>
                    <td>
                        <a href='details.php?id=" . (int) $res['id'] . "'>" . htmlsafechars($res['name']) . '</a>
                    </td>
                    <td>' . get_date($res['added'], 'LONG') . "</td>
                    <td>
                        <a href='{$site_config['baseurl']}/edit.php?id=" . (int) $res['id'] . "{$returnto}' class='tooltipper' title='{$lang['mtor_edit']}'>
                            <i class='icon-edit icon'></i>
                        </a>
                    </td>
                </tr>";
        }

        return $ret_html;
    }
    if ($count == 1) {
        $res = mysqli_fetch_assoc($arr);
        $users[$res['checked_by']] = ((isset($users[$res['checked_by']]) && $users[$res['checked_by']] > 0) ? $users[$res['checked_by']] + 1 : 1);
        $ret_html .= "
                <tr>
                    <td>
                        <a href='details.php?id=" . (int) $res['id'] . "'>" . htmlsafechars($res['name']) . '</a>
                    </td>
                    <td>' . format_username($res['checked_by']) . '</td>
                    <td>' . get_date($res['checked_when'], 'LONG') . '</td>
                </tr>';

        return [
            $users,
            $ret_html,
        ];
    } elseif ($count > 1) {
        while ($res = mysqli_fetch_assoc($arr)) {
            $users[$res['checked_by']] = ((isset($users[$res['checked_by']]) && $users[$res['checked_by']] > 0) ? $users[$res['checked_by']] + 1 : 1);
            $ret_html .= "
                <tr>
                    <td>
                        <a href='details.php?id=" . (int) $res['id'] . "'>" . htmlsafechars($res['name']) . '</a>
                    </td>
                    <td>' . format_username($res['checked_by']) . '</td>
                    <td>' . get_date($res['checked_when'], 'LONG') . '</td>
                </tr>';
        }

        return [
            $users,
            $ret_html,
        ];
    }

    return false;
}

if (isset($_GET['type']) && in_array($_GET['type'], $modes)) {
    $mode = (isset($_GET['type']) && in_array($_GET['type'], $modes)) ? $_GET['type'] : stderr($lang['mtor_error'], '' . $lang['mtor_please_try_that_previous_request_again'] . '.');
    if ($mode === 'unmodded') {
        $res = sql_query('SELECT id, name, added FROM torrents WHERE checked_when = 0');
        $data = do_sort($res, true);
        if (!$data) {
            $HTMLOUT = $links . main_div('<h3>' . $lang['mtor_no_un-modded_torrents_detected'] . ' :D!</h3>', 'top20');
            $title = $lang['mtor_add_done'];
        } else {
            $count = $res->num_rows;
            $put = ($count == 1 ? '1 ' . $lang['mtor_unmodded_torrent'] . '' : $count . ' ' . $lang['mtor_all_unmodded_torrents'] . '');
            $perpage = 15;
            $pager = pager($perpage, $count, "{$_SERVER['PHP_SELF']}?tool=modded_torrents&type={$mode}&");
            $HTMLOUT .= $links . ($count > $perpage ? $pager['pagertop'] : '');
            $HTMLOUT .= "
                <div class='has-text-centered'>
                    <h1>{$lang['mtor_summary']}</h1>
                    <p>$put</p>
                </div>";
            $heading = '
                <tr>
                   <th>' . $lang['mtor_torrent'] . '</th>
                   <th>' . $lang['mtor_added'] . '</th>
                   <th>' . $lang['mtor_edit'] . ' ' . $lang['mtor_torrent'] . '</th>
                </tr>';
            $HTMLOUT .= main_table($data, $heading);
            $HTMLOUT .= $count > $perpage ? $pager['pagertop'] : '';
            $title = $put;
        }
    } else {
        $beginOfDay = strtotime('midnight', TIME_NOW);
        $endOfDay = strtotime('tomorrow', $beginOfDay) - 1;
        $_time = (($mode === 'yesterday') ? $endOfDay : $beginOfDay);
        $res = mysqli_fetch_row(sql_query("SELECT COUNT(*) FROM torrents WHERE checked_when >= $_time AND checked_by > 0"));
        $count = $res[0];
        if ($count < 1) {
            $HTMLOUT .= $links . main_div('<h3>' . $lang['mtor_no_torrents_have_been_modded'] . ' ' . $mode . '.</h3>', 'top20');
            $title = '' . $lang['mtor_no_torrents_modded'] . " $mode";
        } else {
            $perpage = 15;
            $pager = pager($perpage, $count, "{$_SERVER['PHP_SELF']}?tool=modded_torrents&type={$mode}&");
            $HTMLOUT = $trim = '';
            $query = "SELECT tor.*, user.id as uid FROM torrents as tor INNER JOIN users as user ON user.id = tor.checked_by AND tor.checked_when >= $_time ORDER BY tor.checked_when DESC {$pager['limit']}";
            $data = do_sort(sql_query($query));
            if (isset($data[1])) {
                $HTMLOUT .= $count > $perpage ? $pager['pagertop'] : '';
                foreach ($data[0] as $k => $v) {
                    $trim .= "$k : $v ,";
                }
                $trim = trim($trim, ',');
                $HTMLOUT .= $links . "
                <div class='has-text-centered'>
                    <h4>" . $lang['mtor_summary'] . "</h4>$trim
                </div>";
                $heading = '
                    <tr>
                       <th>' . $lang['mtor_torrent'] . '</th>
                       <th>' . $lang['mtor_modded_by'] . '</th>
                       <th>' . $lang['mtor_time'] . '</th>
                    </tr>';
                $HTMLOUT .= main_table($data[1], $heading);
                $HTMLOUT .= $count > $perpage ? $pager['pagerbottom'] : '';
            }
            $title = "$count " . $lang['mtor_modded_torrents'] . " $mode";
        }
    }
    echo stdhead($title) . wrapper($HTMLOUT) . stdfoot();
    die();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $where = false;
    $ts = strtotime(date('F', time()) . ' ' . date('Y', time()));
    $last_day = date('t', $ts);
    $whom = (isset($_POST['username']) && !empty($_POST['username']) ? sqlesc($_POST['username']) : false);
    $when = (isset($_POST['time']) && $_POST['time'] > 0 && $_POST['time'] < $last_day ? (int) $_POST['time'] : false);
    $day = (isset($_POST['day']) && $_POST['day'] > 0 && $_POST['day'] < $last_day ? (int) $_POST['day'] : false);
    $month = (isset($_POST['month']) && $_POST['month'] > 0 && $_POST['month'] < 13 ? (int) $_POST['month'] : false);
    $year = (isset($_POST['year']) && $_POST['year'] <= date('Y', time()) ? (int) $_POST['year'] : false);
    if ($whom) {
        $whom = 'AND LOWER(tor.checked_by) = ' . strtolower($whom);
    }
    if ($when && $when > 0) {
        $when = 'AND tor.checked_when >= ' . (TIME_NOW - $when * 24 * 60 * 60);
    }
    if ($whom || $when || ($day && $month && $year)) {
        if ($day && $month && $year && $whom) {
            $beginOfDay = strtotime('midnight', strtotime("$day-$month-$year"));
            $endOfDay = strtotime('tomorrow', $beginOfDay) - 1;
            $query = "SELECT tor.*,user.id as uid FROM torrents as tor INNER JOIN users as user ON user.id = tor.checked_by $whom AND tor.checked_when > $beginOfDay AND tor.checked_when < $endOfDay ORDER BY tor.checked_when DESC";
            $text = "by <u>$_POST[username]</u> on $day / $month / $year";
            $title = "$_POST[username] : " . $lang['mtor_modded_torrents'] . " on $day / $month / $year";
        } elseif ($whom && $when) {
            $query = "SELECT tor.*,user.id as uid FROM torrents as tor INNER JOIN users as user ON user.id = tor.checked_by $whom $when ORDER BY tor.checked_when DESC";
            $text = "by <u>$_POST[username]</u> within the last " . ($_POST['time'] == 1 ? '<u>1 day.</u>' : '<u>' . $_POST['time'] . ' days.</u>');
            $title = "$_POST[username] : " . $lang['mtor_modded_torrents'] . ' ' . $lang['mtor_from'] . " $_POST[time] days ago";
        } elseif ($when) {
            $query = "SELECT tor.*,user.id as uid FROM torrents as tor INNER JOIN users as user ON user.id = tor.checked_by $when ORDER BY tor.checked_when DESC";
            $text = 'from the past ' . ($_POST['time'] == 1 ? '<u>1 day.</u>' : '<u>' . $_POST['time'] . ' days.</u>');
            $title = "$_POST[username] : " . $lang['mtor_modded_torrents'] . ' ' . $lang['mtor_from'] . " $_POST[time] days ago";
        } elseif ($whom) {
            $query = "SELECT tor.*,user.id as uid FROM torrents as tor INNER JOIN users as user ON user.id = tor.checked_by $whom ORDER BY tor.checked_when DESC";
            $text = "by <u>$_POST[username]</u>";
            $title = "$_POST[username] : " . $lang['mtor_modded_torrents'] . '';
        }
        $res = sql_query($query);
        $count = $res->num_rows;
        if ($count < 1) {
            $HTMLOUT .= main_div('<h3>' . $lang['mtor_no_torrents_have_been_modded'] . " $text</h3>");
            $title = "$_POST[username] : " . $lang['mtor_no_modded_torrents'] . '';
        } else {
            $perpage = 15;
            $pager = pager($perpage, $count, "{$_SERVER['PHP_SELF']}?tool=modded_torrents&type={$mode}&");
            $HTMLOUT = $trim = '';
            $data = do_sort($res);
            if (isset($data[1])) {
                $HTMLOUT .= $count > $perpage ? $pager['pagertop'] : '';
                $trim = "$_POST[username] : $count";
                $HTMLOUT .= $links . "
                <div class='has-text-centered'>
                    <h4>" . $lang['mtor_summary'] . "</h4>$trim
                </div>";
                $heading = '
                    <tr>
                        <th>' . $lang['mtor_torrent'] . '</th>
                        <th>' . $lang['mtor_modded_by'] . '</th>
                        <th>' . $lang['mtor_time'] . '</th>
                    </tr>';
                $HTMLOUT .= main_table($data[1], $heading);
                $HTMLOUT .= $count > $perpage ? $pager['pagerbottom'] : '';
            }
        }
    } else {
        stderr($lang['mtor_error'], '' . $lang['mtor_empty_data_supplied'] . ' ! ' . $lang['mtor_please_try_again'] . '');
    }
    echo stdhead($title) . wrapper($HTMLOUT) . stdfoot();
    die();
}
$HTMLOUT = '';
$HTMLOUT .= $links . "
    <h1 class='has-text-centered'>" . $lang['mtor_modded_torrents_complete_panel'] . '</h1>';

$HTMLOUT .= main_div("
    <div class='has-text-centered'>
        <form method='post' action='{$_SERVER['PHP_SELF']}?tool=modded_torrents'>
            <div>
                <label for='username'>" . $lang['mtor_username'] . "</label><br>
                <input type='text' placeholder='" . $lang['mtor_username'] . "' name='username' id='username'>
            </div>
            <div class='top20'>
                <label for='time'>" . $lang['mtor_from'] . ' ' . $lang['mtor_numbers_of_days_ago'] . "</label><br>
                <input type='text' placeholder='" . $lang['mtor_day'] . "' name='time' id='time'>
            </div>
            <div class='top20'>
                <label for='day'>" . $lang['mtor_on_which_day'] . "</label>
                <input type='text' placeholder='" . $lang['mtor_day'] . "' class='input-small' name='day' id='day'>
                <input type='text' class='input-small' placeholder='" . $lang['mtor_month'] . "' name='month'>
                <input type='text' class='input-small' placeholder='" . $lang['mtor_year'] . "' name='year' value='" . date('Y', time()) . "'>
            </div>
            <button type='submit' class='button is-small top20'>" . $lang['mtor_search'] . '</button>
        </form>
  </div>');

echo stdhead($lang['mtor_modded_torrents_panel']) . wrapper($HTMLOUT) . stdfoot();
