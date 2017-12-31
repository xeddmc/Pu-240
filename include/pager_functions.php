<?php
/**
 * @param       $rpp
 * @param       $count
 * @param       $href
 * @param array $opts
 *
 * @return array
 */
function pager($rpp, $count, $href, $opts = [], $class = null)
{
    // $rpp = results per page
    // $count = total number to process
    // $href = the link
    // $ops = unkonwn

    $pages = ceil($count / $rpp);

    if (!isset($opts['lastpagedefault'])) {
        $pagedefault = 0;
    } else {
        $pagedefault = floor(($count - 1) / $rpp);
        if ($pagedefault < 0) {
            $pagedefault = 0;
        }
    }
    if (isset($_GET['page'])) {
        $page = (int)$_GET['page'];
        if ($page < 0) {
            $page = $pagedefault;
        }
    } else {
        $page = $pagedefault;
    }
    $mp = $pages - 1;
    $pager = $pager2 = '';
    if ($page >= 1) {
        $pager = "
                        <a href='{$href}page=" . ($page - 1) . "' class='pagination-previous button $class tooltipper is_hidden-mobile'  title='Goto Page $page'>Previous</a>";
    }
    if ($page < $mp && $mp >= 0) {
        $pager2 = "
                        <a href='{$href}page=" . ($page + 1) . "' class='pagination-next button $class tooltipper is_hidden-mobile' title='Goto Page " . ($page + 2) . "'>Next</a>";
    }
    if ($count) {
        $pagerarr[] = "<ul class='pagination-list'>";
        $dotted = 0;
        $dotspace = 3;
        $dotend = $pages - $dotspace;
        $curdotend = $page - $dotspace;
        $curdotstart = $page + $dotspace;
        for ($i = 0; $i < $pages; ++$i) {
            if (($i >= $dotspace && $i <= $curdotend) || ($i >= $curdotstart && $i < $dotend)) {
                if (!$dotted) {
                    $pagerarr[] = "
                            <li><span class='pagination-ellipsis'>&hellip;</span></li>";
                }
                $dotted = 1;
                continue;
            }
            $dotted = 0;
            $start = $i * $rpp + 1;
            $end = $start + $rpp - 1;
            if ($end > $count) {
                $end = $count;
            }
            $text = $i + 1;
            if ($i != $page) {
                $pagerarr[] = "
                            <li><a href='{$href}page=$i' class='pagination-link button $class' aria-label='Goto page $text'>$text</a></li>";
            } else {
                $pagerarr[] = "
                            <li><a class='pagination-link is-current' aria-label='Page $text' aria-current='page'>$text</a></li>";
            }
        }
        $pagerarr[] = "
                        </ul>";
        $pagerstr = join('', $pagerarr);
        $pagertop = "<nav class='pagination is-centered is-marginless is-small' role='navigation' aria-label='pagination'>{$pager}{$pager2}
                        $pagerstr
                    </nav>";
        $pagerbottom = "
                    <div class='has-text-centered bottom10'>Overall $count items in " . ($i) . ' page' . ($i > 1 ? '\'s' : '') . ", showing $rpp per page.</div>
                    <nav class='pagination is-centered is-marginless is-small' role='navigation' aria-label='pagination'>{$pager}{$pager2}
                        $pagerstr
                    </nav>";
    } else {
        $pagertop = $pager;
        $pagerbottom = $pagertop;
    }
    $start = $page * $rpp;

    return [
        'pagertop'    => $pagertop,
        'pagerbottom' => $pagerbottom,
        'limit'       => "LIMIT $start,$rpp",
    ];
}

/**
 * @param $data
 *
 * @return string
 */
function pager_rep($data)
{
    $pager = [
        'pages'     => 0,
        'page_span' => '',
        'start'     => '',
        'end'       => '',
    ];
    $section = $data['span'] = isset($data['span']) ? $data['span'] : 2;
    $parameter = isset($data['parameter']) ? $data['parameter'] : 'page';
    $mini = isset($data['mini']) ? 'mini' : '';
    if ($data['count'] > 0) {
        $pager['pages'] = ceil($data['count'] / $data['perpage']);
    }
    $pager['pages'] = $pager['pages'] ? $pager['pages'] : 1;
    $pager['total_page'] = $pager['pages'];
    $pager['current_page'] = $data['start_value'] > 0 ? ($data['start_value'] / $data['perpage']) + 1 : 1;
    $previous_link = '';
    $next_link = '';
    if ($pager['current_page'] > 1) {
        $start = $data['start_value'] - $data['perpage'];
        $previous_link = "<a href='{$data['url']}&amp;$parameter=$start' class='tooltipper' title='Previous'><span class='{$mini}pagelink'>&lt;</span></a>";
    }
    if ($pager['current_page'] < $pager['pages']) {
        $start = $data['start_value'] + $data['perpage'];
        $next_link = "&#160;<a href='{$data['url']}&amp;$parameter=$start' class='tooltipper' title='Next'><span class='{$mini}pagelink'>&gt;</span></a>";
    }
    if ($pager['pages'] > 1) {
        if (isset($data['mini'])) {
            $pager['first_page'] = "<img src='./images/multipage.gif' alt='' title='' />";
        } else {
            $pager['first_page'] = "<span style='background: #F0F5FA; border: 1px solid #072A66;padding: 1px 3px 1px 3px;'>{$pager['pages']} Pages</span>&#160;";
        }
        for ($i = 0; $i <= $pager['pages'] - 1; ++$i) {
            $RealNo = $i * $data['perpage'];
            $PageNo = $i + 1;
            if ($RealNo == $data['start_value']) {
                $pager['page_span'] .= $mini ? "&#160;<a href='{$data['url']}&amp;$parameter={$RealNo}' class='tooltipper' title='$PageNo'><span  class='{$mini}pagelink'>$PageNo</span></a>" : "&#160;<span class='pagecurrent'>{$PageNo}</span>";
            } else {
                if ($PageNo < ($pager['current_page'] - $section)) {
                    $pager['start'] = "<a href='{$data['url']}' class='tooltipper' title='Goto First'><span class='{$mini}pagelinklast'>&laquo;</span></a>&#160;";
                    continue;
                }
                if ($PageNo > ($pager['current_page'] + $section)) {
                    $pager['end'] = "&#160;<a href='{$data['url']}&amp;$parameter=" . (($pager['pages'] - 1) * $data['perpage']) . "' class='tooltipper' title='Go To Last'><span class='{$mini}pagelinklast'>&raquo;</span></a>&#160;";
                    break;
                }
                $pager['page_span'] .= "&#160;<a href='{$data['url']}&amp;$parameter={$RealNo}' class='tooltipper' title='$PageNo'><span  class='{$mini}pagelink'>$PageNo</span></a>";
            }
        }
        $float = $mini ? '' : ' fleft';
        $pager['return'] = "<div class='pager{$float}'>{$pager['first_page']}{$pager['start']}{$previous_link}{$pager['page_span']}{$next_link}{$pager['end']}
            </div>";
    } else {
        $pager['return'] = '';
    }

    return $pager['return'];
}
