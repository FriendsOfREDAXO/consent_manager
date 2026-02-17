<?php

$addon = rex_addon::get('consent_manager');

$cm_help_pages = [
    'readme' => [
        'title' => 'Dokumentation',
        'icon' => 'rex-icon fa-book',
        'file' => 'README.md'
    ],
    'inline' => [
        'title' => 'Inline Consent',
        'icon' => 'rex-icon fa-play-circle',
        'file' => 'inline.md'
    ],
    'api' => [
        'title' => 'API',
        'icon' => 'rex-icon fa-code',
        'file' => 'api.md'
    ],
    'changelog' => [
        'title' => rex_i18n::msg('consent_manager_changelog'),
        'icon' => 'rex-icon fa-list',
        'file' => 'CHANGELOG.md'
    ],
];

$func = rex_request('func', 'string', 'readme');
$q = rex_request('q', 'string', '');

// Search Logic
$searchResults = [];
if ($q !== '') {
    foreach ($cm_help_pages as $key => $p) {
        $contentRaw = rex_file::get($addon->getPath($p['file']));
        if (!$contentRaw) continue;
        
        $pos = stripos($contentRaw, $q);
        if ($pos !== false) {
             // Find context
             $start = max(0, $pos - 70);
             $length = strlen($q) + 140;
             $snippet = mb_substr($contentRaw, $start, $length);
             if ($start > 0) $snippet = '...' . $snippet;
             if (($start + $length) < strlen($contentRaw)) $snippet .= '...';
             
             $snippet = rex_escape($snippet);
             // Highlight match
             $snippet = preg_replace('/('.preg_quote(rex_escape($q), '/').')/i', '<mark>$1</mark>', $snippet);
             
             // Find nearest heading
             $heading = '';
             $anchor = '';
             
             $preContent = substr($contentRaw, 0, $pos);
             if (preg_match_all('/^#{1,6}\s+(.+)$/m', $preContent, $matches)) {
                 $lastHeader = end($matches[1]);
                 $heading = trim($lastHeader);
                 $anchor = rex_string::normalize($heading, '-');
             }
             
             $searchResults[$key] = $p;
             $searchResults[$key]['snippet'] = $snippet;
             $searchResults[$key]['heading'] = $heading;
             $searchResults[$key]['anchor'] = $anchor;
        }
    }
}

// Navigation
$nav = '<ul class="nav nav-pills nav-stacked">';
foreach ($cm_help_pages as $key => $p) {
    if (rex_i18n::hasMsg($p['title'])) {
        $p['title'] = rex_i18n::msg($p['title']);
    }
    
    $active = ($key === $func && $q === '') ? ' class="active"' : '';
    $nav .= '<li' . $active . '><a href="' . rex_url::currentBackendPage(['func' => $key]) . '"><i class="' . $p['icon'] . '"></i> ' . $p['title'] . '</a></li>';
}
$nav .= '</ul>';

// Content
$content = '';
$tocHtml = '';

if ($q !== '') {
    $content = '<h2>' . rex_i18n::msg('consent_manager_search_results_for', rex_escape($q)) . '</h2>';
    if (count($searchResults) > 0) {
        $content .= '<div class="list-group">';
        foreach ($searchResults as $key => $p) {
            $title = $p['title'];
            if (rex_i18n::hasMsg($title)) {
                $title = rex_i18n::msg($title);
            }
            
            $url = rex_url::currentBackendPage(['func' => $key]);
            if (!empty($p['anchor'])) {
                $url .= '#' . $p['anchor'];
                $title .= ' <small class="text-muted"><i class="fa fa-angle-right"></i> ' . rex_escape($p['heading']) . '</small>';
            }
            
            $content .= '<a href="' . $url . '" class="list-group-item">';
            $content .= '<h4 class="list-group-item-heading"><i class="' . $p['icon'] . '"></i> ' . $title . '</h4>';
            $content .= '<p class="list-group-item-text" style="color: #666; font-size: 13px; margin-top: 5px;">' . $p['snippet'] . '</p>';
            $content .= '</a>';
        }
        $content .= '</div>';
    } else {
        $content .= rex_view::warning(rex_i18n::msg('consent_manager_no_results_found'));
    }
} else {
    if (isset($cm_help_pages[$func])) {
        $file = $addon->getPath($cm_help_pages[$func]['file']);
        if (file_exists($file)) {
            $md = rex_file::get($file);
            
            $md = preg_replace_callback('/\[([^\]]+)\]\(([^)]+)\.md\)/', function ($matches) use ($cm_help_pages) {
                foreach ($cm_help_pages as $key => $page) {
                    if ($page['file'] === $matches[2] . '.md') {
                         return '[' . $matches[1] . '](' . rex_url::currentBackendPage(['func' => $key]) . ')';
                    }
                }
                return $matches[0];
            }, $md);

            $parsed = rex_markdown::factory()->parse($md);
            
            // Add IDs to headers and build TOC
            $toc = [];
            $parsed = preg_replace_callback('/<h([1-6])>(.*?)<\/h\1>/s', function($matches) use (&$toc) {
                $tag = $matches[1];
                $text = strip_tags($matches[2]);
                $id = rex_string::normalize($text, '-');
                
                $toc[] = [
                    'level' => $tag,
                    'text' => $text,
                    'id' => $id
                ];
                
                return '<h' . $tag . ' id="' . $id . '">' . $matches[2] . '</h' . $tag . '>';
            }, $parsed);
            
            // Generate TOC HTML
            if (!empty($toc)) {
                $tocHtml = '<div class="panel panel-default" style="margin-top: 20px;">
                    <div class="panel-heading"><b>' . rex_i18n::msg('consent_manager_toc_header') . '</b></div>
                    <div class="panel-body" style="padding: 10px;">
                        <input type="text" id="cm-toc-filter" class="form-control input-sm" placeholder="' . rex_i18n::msg('consent_manager_toc_filter_placeholder') . '...">
                    </div>
                    <div class="list-group" id="cm-toc-list" style="max-height: 500px; overflow-y: auto;">';
                
                foreach ($toc as $item) {
                     $style = 'padding-left: ' . (($item['level'] - 1) * 15 + 15) . 'px;';
                     
                     if ($item['level'] == 1) {
                        $style .= ' font-weight: 700; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;';
                     } elseif ($item['level'] == 2) {
                        $style .= ' font-weight: 700; font-size: 13px; margin-top: 5px;';
                     } else {
                        $style .= ' font-size: 13px;';
                     }

                     $tocHtml .= '<a href="#' . $item['id'] . '" class="list-group-item" style="' . $style . '">' . $item['text'] . '</a>';
                }
                $tocHtml .= '</div></div>';

                // JS for Live Search
                $tocHtml .= '<script nonce="' . rex_response::getNonce() . '">
                (function($) {
                    $(document).on("rex:ready", function() {
                        var $input = $("#cm-toc-filter");
                        var $list = $("#cm-toc-list");
                        
                        $input.on("keyup", function() {
                            var value = $(this).val().toLowerCase();
                            $list.find("a").filter(function() {
                                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                            });
                        });
                        // Prevent form submission on enter in filter input
                        $input.on("keydown", function(e) {
                            if (e.key === "Enter") {
                                e.preventDefault();
                                return false;
                            }
                        });
                    });
                })(jQuery);
                </script>';
            }

            $content = '<div class="rex-docs" style="display: block !important">' . $parsed . '</div>';
        }
    }
}

// Search Form
$searchForm = '
    <form action="' . rex_url::currentBackendPage() . '" method="get" style="margin-bottom: 20px;">
        <input type="hidden" name="page" value="consent_manager/help">
        <div class="input-group">
            <input type="text" class="form-control" name="q" value="' . rex_escape($q) . '" placeholder="' . rex_i18n::msg('consent_manager_help_search_placeholder') . '...">
            <span class="input-group-btn">
                <button class="btn btn-default" type="submit"><i class="rex-icon fa-search"></i></button>
            </span>
        </div>
    </form>
';

// Sidebar Fragment (Search + Nav + TOC)
$sidebarContent = $searchForm . $nav;
if ($tocHtml !== '') {
    $sidebarContent .= $tocHtml;
}

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('consent_manager_help')); 
$fragment->setVar('body', $sidebarContent, false);
$sidebar = $fragment->parse('core/page/section.php');

// Content Fragment
$fragment = new rex_fragment();
$fragment->setVar('title', isset($cm_help_pages[$func]) ? $cm_help_pages[$func]['title'] : rex_i18n::msg('search_results'));
$fragment->setVar('body', $content, false);
$mainContent = $fragment->parse('core/page/section.php');

echo '
<div class="row">
    <div class="col-md-3">
        ' . $sidebar . '
    </div>
    <div class="col-md-9">
        ' . $mainContent . '
    </div>
</div>
';

echo '
<style>
mark {
    background-color: #ffe066;
    color: #000;
    padding: 0 2px;
    border-radius: 2px;
}
.rex-docs pre {
    position: relative;
}
.rex-docs .btn-copy-code {
    position: absolute;
    top: 5px;
    right: 5px;
    padding: 3px 8px;
    font-size: 12px;
    opacity: 0.5;
    transition: opacity 0.2s;
    cursor: pointer;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 3px;
    color: #333;
    z-index: 10;
}
.rex-docs pre:hover .btn-copy-code {
    opacity: 1;
}
.rex-docs .btn-copy-code:hover {
    background: #f5f5f5;
}
.rex-docs .btn-copy-code.copied {
    background: #5bb75b;
    color: #fff;
    border-color: #5bb75b;
    opacity: 1;
}
</style>
<script>
(function($) {
    $(document).on("rex:ready", function() {
        $(".rex-docs pre").each(function() {
            var $pre = $(this);
            if ($pre.find(".btn-copy-code").length) return;
            
            var $code = $pre.find("code");
            if (!$code.length) return;
            
            var $btn = $("<button class=\"btn-copy-code\" title=\"Copy to clipboard\" type=\"button\"><i class=\"rex-icon fa-clipboard\"></i></button>");
            $pre.append($btn);
            
            $btn.on("click", function() {
                var codeText = $code.text();
                
                var textarea = document.createElement("textarea");
                textarea.value = codeText;
                document.body.appendChild(textarea);
                textarea.select();
                try {
                    document.execCommand("copy");
                    $btn.addClass("copied").html("<i class=\"rex-icon fa-check\"></i>");
                    setTimeout(function() {
                        $btn.removeClass("copied").html("<i class=\"rex-icon fa-clipboard\"></i>");
                    }, 2000);
                } catch (err) {
                    console.error("Failed to copy text", err);
                }
                document.body.removeChild(textarea);
            });
        });
    });
})(jQuery);
</script>
';
