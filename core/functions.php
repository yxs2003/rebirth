<?php
/**
 * Rebirth 静态引擎与前端组件核心 V4.5
 * 修复 SQL 歧义、JS 变量解析错误、动态绑定构建目录
 */
require_once __DIR__ . '/db.php';

function generateAssets() {
    $theme = getOption('site_theme', 'glass');
    
    // 【深度修复】使用 PHP Nowdoc (<<<'CSS') 防止任何变量意外解析
    $css = "/* Rebirth Auto-Generated Core CSS V4.5 */\n:root {\n";
    if ($theme === 'mac') {
        $css .= "  --primary:#007aff; --bg:#f5f5f7; --surface:#ffffff; --border:1px solid rgba(0,0,0,0.05); --shadow:0 4px 20px rgba(0,0,0,0.04); --radius:14px; --text:#1d1d1f; --text-mute:#86868b; --font:-apple-system,BlinkMacSystemFont,sans-serif; --backdrop:none;\n";
    } elseif ($theme === 'news') {
        $css .= "  --primary:#8b0000; --bg:#f4ecd8; --surface:#fdf6e3; --border:2px solid #2c2c2c; --shadow:4px 4px 0px #2c2c2c; --radius:0px; --text:#1a1a1a; --text-mute:#555555; --font:'Georgia',serif; --backdrop:none;\n";
    } elseif ($theme === 'text') {
        $css .= "  --primary:#333; --bg:#faf9f5; --surface:transparent; --border:none; --shadow:none; --radius:0; --text:#222; --text-mute:#777; --font:'Courier New',monospace; --backdrop:none;\n";
    } else { // glass
        $css .= "  --primary:#6c5ce7; --bg:linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); --surface:rgba(255,255,255,0.7); --border:1px solid rgba(255,255,255,0.5); --shadow:0 8px 32px 0 rgba(31,38,135,0.05); --radius:16px; --text:#2d3436; --text-mute:#636e72; --font:'Inter',system-ui,sans-serif; --backdrop:blur(15px);\n";
    }
    $css .= "}\n";
    
    $css .= <<<'CSS'
    /* 深色模式 Dark Mode */
    body.dark-theme { --bg:#121212; --surface:#1e1e1e; --text:#e0e0e0; --text-mute:#aaa; --border:1px solid #333; --shadow:0 5px 25px rgba(0,0,0,0.5); }
    
    * { box-sizing: border-box; }
    body { margin:0; font-family:var(--font); background:var(--bg); color:var(--text); line-height:1.8; overflow-x:hidden; background-attachment: fixed; transition: background 0.3s, color 0.3s; }
    a { text-decoration:none; color:inherit; transition:0.3s; }
    img { max-width:100%; border-radius:8px; display:block; margin:auto; box-shadow:var(--shadow); }

    .rb-header { position:fixed; top:0; width:100%; z-index:1000; background:var(--surface); backdrop-filter:var(--backdrop); border-bottom:var(--border); transition:transform 0.3s, box-shadow 0.3s; display:flex; justify-content:space-between; align-items:center; padding:15px 5%; }
    .rb-header.scrolled-down { transform:translateY(-100%); }
    .rb-header.scrolled-up { box-shadow:0 4px 15px rgba(0,0,0,0.05); }
    .logo { font-size:1.5rem; font-weight:800; display:flex; align-items:center; gap:10px; }
    .logo span { color:var(--primary); }
    .logo img { height:40px; }
    .nav { display:flex; gap:20px; align-items:center; }
    .nav a { font-weight:600; color:var(--text-mute); }
    .nav a:hover { color:var(--primary); }
    
    .theme-toggle { background:rgba(0,0,0,0.05); border:none; padding:8px 12px; border-radius:20px; cursor:pointer; color:var(--text); font-weight:bold; outline:none; transition:0.3s; }
    .theme-toggle:hover { background:var(--primary); color:#fff; }

    .hero { position:relative; min-height:55vh; display:flex; align-items:center; justify-content:center; text-align:center; margin-bottom:50px; background:#000; color:#fff; overflow:hidden;}
    .hero-bg { position:absolute; inset:0; background-size:cover; background-position:center; opacity:0.6; }
    .hero-content { position:relative; z-index:1; padding:0 20px; max-width:1000px; }
    .hero h1 { font-size:3.5rem; font-weight:800; margin-bottom:15px; text-shadow:0 2px 10px rgba(0,0,0,0.5); line-height:1.2; }
    
    .slider { position:relative; width:100%; height:450px; overflow:hidden; margin-bottom:50px; }
    .slide-item { position:absolute; inset:0; opacity:0; transition:opacity 0.8s ease; display:flex; align-items:center; justify-content:center; text-align:center; color:#fff; background-size:cover; background-position:center;}
    .slide-item.active { opacity:1; z-index:1; }
    .slide-content { background:rgba(0,0,0,0.4); backdrop-filter:blur(5px); padding:40px 60px; border-radius:16px; }
    .slide-content h2 { margin:0 0 15px 0; font-size:2.8rem; text-shadow:0 2px 5px rgba(0,0,0,0.5);}
    .slide-btn { display:inline-block; padding:12px 30px; background:var(--primary); color:#fff; border-radius:30px; font-weight:bold; font-size:1.1rem;}

    /* 巨幕级页面布局 */
    .page-wrap { max-width:1300px; margin:0 auto; padding:0 5%; display:grid; grid-template-columns: 3fr 1fr; gap:50px; align-items:start; }
    .grid { display:grid; gap:30px; }
    .card { background:var(--surface); border:var(--border); border-radius:var(--radius); box-shadow:var(--shadow); backdrop-filter:var(--backdrop); overflow:hidden; transition:transform 0.3s; display:flex; flex-direction:column; }
    .card:hover { transform:translateY(-5px); box-shadow:0 15px 35px rgba(0,0,0,0.08); }
    .c-img { height:220px; background-size:cover; background-position:center; }
    .c-body { padding:30px; flex:1; display:flex; flex-direction:column; }
    .c-cat { color:var(--primary); font-size:0.85rem; font-weight:bold; margin-bottom:10px; text-transform:uppercase; letter-spacing:1px; }
    .c-desc { color:var(--text-mute); font-size:1rem; display:-webkit-box; -webkit-box-orient:vertical; overflow:hidden; flex:1; margin-top:10px; }

    /* 侧边栏大气样式 */
    .sidebar { display:flex; flex-direction:column; gap:30px; position:sticky; top:100px; }
    .widget { background:var(--surface); border:var(--border); border-radius:var(--radius); padding:30px; box-shadow:var(--shadow); backdrop-filter:var(--backdrop); }
    .w-title { font-size:1.2rem; margin:0 0 20px 0; padding-left:12px; border-left:5px solid var(--primary); color:var(--text); font-weight:800; }
    .author-card { text-align:center; }
    .author-avatar { width:100px; height:100px; border-radius:50%; border:4px solid var(--surface); box-shadow:0 5px 15px rgba(0,0,0,0.1); margin-bottom:15px; }
    
    .capsule-bar { height:10px; background:rgba(0,0,0,0.05); border-radius:5px; margin-bottom:15px; overflow:hidden; }
    .capsule-inner { height:100%; background:var(--primary); border-radius:5px; }
    .capsule-text { display:flex; justify-content:space-between; font-size:0.85rem; color:var(--text-mute); margin-bottom:5px; font-weight:bold;}

    .toc-list { list-style:none; padding:0; margin:0; font-size:0.95rem; }
    .toc-list li { margin-bottom:10px; }
    .toc-list a { color:var(--text-mute); display:block; padding:6px 12px; border-radius:8px; border-left: 2px solid transparent;}
    .toc-list a:hover { background:rgba(108,92,231,0.08); color:var(--primary); border-left-color:var(--primary); font-weight:bold; }

    .post-content { font-size:1.15rem; word-wrap:break-word; }
    .post-content h2, .post-content h3 { margin-top:2em; padding-bottom:10px; border-bottom:1px dashed rgba(0,0,0,0.1); }
    .post-content p { margin-bottom: 1.5em; }
    .copyright-box { background:rgba(0,0,0,0.02); border:1px dashed rgba(0,0,0,0.1); border-radius:12px; padding:25px; font-size:0.95rem; color:var(--text-mute); margin-top:50px; }

    .rb-alert { padding:18px 25px; border-radius:12px; margin:2em 0; display:flex; gap:15px; align-items:flex-start; font-size:1.05rem;}
    .rb-alert-icon { font-size:1.6rem; margin-top:-2px;}
    .alert-success { background:#e8f8f5; border-left:5px solid #1abc9c; color:#16a085; }
    .alert-info { background:#ebf5fb; border-left:5px solid #3498db; color:#2980b9; }
    .alert-warn { background:#fef9e7; border-left:5px solid #f39c12; color:#d35400; }
    .alert-error { background:#fdf2e9; border-left:5px solid #e74c3c; color:#c0392b; }
    
    .rb-mac-code { background:#282c34; border-radius:12px; margin:2em 0; overflow:hidden; box-shadow:0 10px 30px rgba(0,0,0,0.2);}
    .mac-header { background:#1e1e1e; padding:12px 20px; display:flex; justify-content:space-between; align-items:center; }
    .mac-dots { display:flex; gap:8px; } .mac-dot { width:12px; height:12px; border-radius:50%; }
    .rb-dot-r {background:#ff5f56;} .rb-dot-y {background:#ffbd2e;} .rb-dot-g {background:#27c93f;}
    .mac-copy { background:#444; color:#fff; border:none; padding:5px 12px; border-radius:6px; cursor:pointer; font-weight:bold; transition:0.2s;}
    .mac-copy:hover { background:var(--primary); }
    .mac-body { padding:20px; margin:0; color:#abb2bf; font-family:'Fira Code', monospace; font-size:0.95rem; overflow-x:auto; line-height:1.6; }
    
    .rb-download { display:flex; justify-content:space-between; align-items:center; background:var(--surface); border:var(--border); padding:25px; border-radius:16px; margin:2em 0; box-shadow:var(--shadow);}
    .rb-download-left { display:flex; gap:20px; align-items:center; }
    .rb-download-icon { font-size:3rem; color:var(--primary); }
    .rb-download-title { font-weight:800; font-size:1.2rem; color:var(--text); }
    .rb-download-meta { font-size:0.95rem; color:var(--text-mute); margin-top:5px;}
    .rb-download-code { background:rgba(0,0,0,0.05); padding:3px 8px; border-radius:6px; cursor:pointer; font-family:monospace; border:1px solid rgba(0,0,0,0.1); color:var(--text);}
    .rb-download-btn { background:var(--primary); color:#fff; padding:12px 25px; border-radius:10px; font-weight:bold; font-size:1.1rem; }

    @media (max-width:900px) { .page-wrap { grid-template-columns:1fr; margin-top:80px;} }
    @media (max-width:600px) { .rb-download { flex-direction:column; text-align:center; gap:15px;} .rb-download-left {flex-direction:column;} .hero h1 {font-size:2.2rem;}}
CSS;

    // 【深度修复】使用 PHP Nowdoc，防止任何 JS 中的 $ 符号被解析报错
    $js = <<<'JS'
    // 深色模式逻辑
    function toggleTheme() {
        const body = document.body;
        body.classList.toggle('dark-theme');
        const isDark = body.classList.contains('dark-theme');
        localStorage.setItem('rb_theme', isDark ? 'dark' : 'light');
        document.getElementById('theme-btn').innerHTML = isDark ? '<i class="ri-sun-line"></i> 亮色' : '<i class="ri-moon-line"></i> 暗色';
    }
    if(localStorage.getItem('rb_theme') === 'dark') {
        document.body.classList.add('dark-theme');
    }

    // 导航栏滚动逻辑
    let lastScroll = 0; const header = document.querySelector('.rb-header');
    window.addEventListener('scroll', () => {
        const current = window.pageYOffset;
        if(current <= 0) { header.classList.remove('scrolled-up', 'scrolled-down'); return; }
        if(current > lastScroll && !header.classList.contains('scrolled-down')) {
            header.classList.remove('scrolled-up'); header.classList.add('scrolled-down');
        } else if (current < lastScroll && header.classList.contains('scrolled-down')) {
            header.classList.remove('scrolled-down'); header.classList.add('scrolled-up');
        } lastScroll = current;
    });

    // 一键复制
    window.copyRbText = function(el) {
        navigator.clipboard.writeText(el.innerText).then(() => {
            const origin = el.innerText; el.innerText = '已复制!'; setTimeout(()=>el.innerText=origin, 2000);
        });
    };
    window.copyRbCode = function(btn) {
        const code = btn.parentElement.nextElementSibling.innerText;
        navigator.clipboard.writeText(code).then(() => {
            btn.innerText = '成功!'; setTimeout(()=>btn.innerText='复制代码', 2000);
        });
    };

    // 动态生成 TOC 目录 (使用完全安全的 JS 模板字符串)
    document.addEventListener('DOMContentLoaded', () => {
        const themeBtn = document.getElementById('theme-btn');
        if(themeBtn && localStorage.getItem('rb_theme') === 'dark') {
            themeBtn.innerHTML = '<i class="ri-sun-line"></i> 亮色';
        }

        const content = document.querySelector('.post-content');
        const toc = document.getElementById('dynamic-toc');
        if (content && toc) {
            const headings = content.querySelectorAll('h2, h3');
            if(headings.length === 0) { toc.innerHTML = '<li><span style="color:#999">暂无目录</span></li>'; return; }
            let html = '';
            headings.forEach((h, index) => {
                h.id = 'h-' + index;
                const pl = h.tagName.toLowerCase() === 'h3' ? '15px' : '0';
                html += `<li style="margin-left:${pl}"><a href="#${h.id}">${h.innerText}</a></li>`;
            });
            toc.innerHTML = html;
        }
    });

    // 幻灯片逻辑
    const slides = document.querySelectorAll('.slide-item');
    if(slides.length > 0) {
        let cur = 0;
        setInterval(() => {
            slides[cur].classList.remove('active');
            cur = (cur + 1) % slides.length;
            slides[cur].classList.add('active');
        }, 4000);
    }
JS;

    $base_dir = dirname(__DIR__);
    file_put_contents($base_dir . '/assets/css/front.css', $css);
    file_put_contents($base_dir . '/assets/js/front.js', $js);
}

// 【深度修复】指定 p.slug 防止 Column is ambiguous 崩溃
function renderSidebar($pdo, $is_post = false, $post_tags = '') {
    if (getOption('sidebar_enable', '1') != '1') return '';
    $blocks = explode(',', getOption('sidebar_blocks', 'author,toc,capsule,recent'));
    $build_dir = getOption('build_dir', 'article');
    // 如果是在文章页，返回上两级回到根目录
    $root_path = $is_post ? '../../' : ''; 
    
    ob_start();
    echo '<aside class="sidebar">';
    
    foreach ($blocks as $block) {
        if ($block == 'author') {
            echo '<div class="widget author-card">';
            if($ava = getOption('author_avatar')) echo "<img src='$ava' class='author-avatar'>";
            echo "<h3 style='margin:10px 0 5px; font-size:1.3rem; color:var(--text);'>".htmlspecialchars(getOption('author_name'))."</h3>";
            echo "<p style='color:var(--text-mute); font-size:0.95rem;'>".htmlspecialchars(getOption('author_desc'))."</p>";
            echo "<div style='display:flex; justify-content:center; gap:15px; font-size:0.9rem; color:var(--primary); font-weight:bold;'><span>".htmlspecialchars(getOption('author_gender'))."</span><span>".htmlspecialchars(getOption('author_email'))."</span></div>";
            echo '</div>';
        }
        if ($block == 'capsule') {
            $y = round((date('z') + 1) / 365 * 100, 1);
            $m = round((date('j') - 1) / date('t') * 100, 1);
            $d = round(date('G') / 24 * 100, 1);
            echo '<div class="widget"><h3 class="w-title"><i class="ri-timer-line"></i> 时光进度</h3>';
            echo "<div class='capsule-text'><span>今年度过</span><span>{$y}%</span></div><div class='capsule-bar'><div class='capsule-inner' style='width:{$y}%'></div></div>";
            echo "<div class='capsule-text'><span>本月度过</span><span>{$m}%</span></div><div class='capsule-bar'><div class='capsule-inner' style='width:{$m}%'></div></div>";
            echo "<div class='capsule-text'><span>今日度过</span><span>{$d}%</span></div><div class='capsule-bar'><div class='capsule-inner' style='width:{$d}%'></div></div>";
            echo '</div>';
        }
        if ($block == 'toc' && $is_post) {
            echo '<div class="widget"><h3 class="w-title"><i class="ri-list-check"></i> 文章目录</h3><ul id="dynamic-toc" class="toc-list"></ul></div>';
        }
        if ($block == 'recent') {
            // 【关键修复点】明确使用 p.slug
            $posts = $pdo->query("SELECT p.title, p.slug, c.slug as cat_slug FROM rb_posts p LEFT JOIN rb_categories c ON p.category_id=c.id WHERE status=1 ORDER BY created_at DESC LIMIT 5")->fetchAll();
            echo '<div class="widget"><h3 class="w-title"><i class="ri-history-line"></i> 最新发布</h3><ul class="toc-list">';
            foreach($posts as $p) { echo "<li><a href='{$root_path}{$build_dir}/".($p['cat_slug']?:'uncategorized')."/{$p['slug']}.html'>{$p['title']}</a></li>"; }
            echo '</ul></div>';
        }
    }
    echo '</aside>';
    return ob_get_clean();
}

function generatePostHtml($post_id) {
    global $pdo;
    generateAssets(); 
    
    $stmt = $pdo->prepare("SELECT p.*, c.slug as cat_slug, c.name as cat_name FROM rb_posts p LEFT JOIN rb_categories c ON p.category_id = c.id WHERE p.id = ?");
    $stmt->execute([$post_id]);
    $post = $stmt->fetch();
    if (!$post) return "文章不存在";

    // 动态提取自定义的构建目录
    $build_dir = getOption('build_dir', 'article');
    $base_dir = dirname(__DIR__) . '/' . $build_dir;
    $cat_dir = $base_dir . '/' . ($post['cat_slug'] ?: 'uncategorized');
    if (!is_dir($cat_dir)) mkdir($cat_dir, 0777, true);
    $file_path = $cat_dir . '/' . $post['slug'] . '.html';

    $site_title = getOption('site_title', 'Rebirth Blog');
    $logo_type = getOption('logo_type', 'text');
    $logo_html = $logo_type == 'img' ? '<img src="'.htmlspecialchars(getOption('logo_img')).'">' : getOption('site_logo', 'Re<span>birth</span>');
    $tdk_title = htmlspecialchars($post['title']) . (getOption('show_subtitle_in_title')=='1' ? ' - ' . $site_title : '');
    
    // 【新增】智能字数与阅读时间计算
    $pure_text = strip_tags($post['content']);
    $word_count = mb_strlen($pure_text, 'UTF-8');
    $read_time = ceil($word_count / 400); 

    ob_start();
    ?>
    <!DOCTYPE html>
    <html lang="zh-CN">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= $tdk_title ?></title>
        <meta name="description" content="<?= htmlspecialchars($post['summary']) ?>">
        <?php if($f=getOption('site_favicon')): ?><link rel="icon" href="<?= $f ?>"><?php endif; ?>
        <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
        <link rel="stylesheet" href="../../assets/css/front.css">
        <style>
            <?= $post['custom_css'] ?>
            <?php if(getOption('sidebar_enable')=='0'): ?> .page-wrap { grid-template-columns: 1fr; max-width:900px;} <?php endif; ?>
        </style>
    </head>
    <body>
        <header class="rb-header">
            <a href="../../index.html" class="logo"><?= $logo_html ?></a>
            <div class="nav">
                <a href="../../index.html"><i class="ri-home-4-line"></i> 首页</a>
                <?php if(getOption('enable_dark_mode', '1') == '1'): ?>
                    <button class="theme-toggle" id="theme-btn" onclick="toggleTheme()"><i class="ri-moon-line"></i> 暗色</button>
                <?php endif; ?>
            </div>
        </header>

        <?php if($post['cover_image'] && getOption('site_theme') != 'text'): ?>
            <div class="hero" style="min-height:50vh;">
                <div class="hero-bg" style="background-image:url('<?= $post['cover_image'] ?>')"></div>
                <div class="hero-content">
                    <h1><?= htmlspecialchars($post['title']) ?></h1>
                    <?php if($post['show_meta']): ?>
                    <div style="font-size:1.1rem; opacity:0.9; margin-top:20px; display:flex; justify-content:center; gap:20px; flex-wrap:wrap;">
                        <span style="background:var(--primary); padding:6px 15px; border-radius:30px;"><i class="ri-folder-2-line"></i> <?= $post['cat_name']?:'未分类' ?></span>
                        <span><i class="ri-calendar-line"></i> <?= date('Y-m-d', strtotime($post['created_at'])) ?></span>
                        <span><i class="ri-book-read-line"></i> 约 <?= $read_time ?> 分钟</span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div style="height: 120px;"></div> 
        <?php endif; ?>

        <main class="page-wrap">
            <div class="main-col">
                <article class="card" style="padding: 50px;">
                    <?php if(!$post['cover_image'] || getOption('site_theme') == 'text'): ?>
                        <h1 style="font-size:2.8rem; text-align:center; margin-top:0; font-weight:900; line-height:1.3;"><?= htmlspecialchars($post['title']) ?></h1>
                        <?php if($post['show_meta']): ?>
                        <div style="text-align:center; color:var(--text-mute); margin-bottom:40px; font-size:1rem; display:flex; justify-content:center; gap:20px;">
                            <span style="color:var(--primary); font-weight:bold;"><?= $post['cat_name']?:'未分类' ?></span> 
                            <span><?= date('Y-m-d', strtotime($post['created_at'])) ?></span>
                            <span><?= $word_count ?> 字</span>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <div class="post-content"><?= $post['content'] ?></div>
                    
                    <?php if($post['tags']): ?>
                        <div style="margin-top:40px; padding-top:20px; border-top:1px dashed rgba(0,0,0,0.1);">
                            <i class="ri-price-tag-3-fill" style="color:var(--primary); font-size:1.2rem; vertical-align:middle; margin-right:5px;"></i> 
                            <?php foreach(explode(',', $post['tags']) as $t): ?>
                                <span style="background:rgba(108,92,231,0.1); color:var(--primary); padding:6px 15px; border-radius:20px; font-size:0.9rem; margin-right:10px; font-weight:bold;"><?= htmlspecialchars(trim($t)) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="copyright-box">
                        <div style="font-weight:900; color:var(--text); margin-bottom:10px; font-size:1.1rem;"><i class="ri-copyright-line"></i> 版权与分享</div>
                        <div style="margin-bottom:15px;"><?= nl2br(htmlspecialchars(getOption('post_copyright', '本文遵循 CC BY-NC-SA 4.0 协议，转载请注明出处。'))) ?></div>
                        <button onclick="copyRbText(this.nextElementSibling)" style="background:var(--primary); color:#fff; border:none; padding:8px 15px; border-radius:6px; cursor:pointer; font-weight:bold;">分享此文章的链接</button>
                        <span style="display:none;"><?= 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . "/../{$build_dir}/" . ($post['cat_slug'] ?: 'uncategorized') . '/' . $post['slug'] . '.html' ?></span>
                    </div>
                </article>

                <div class="card c-wrap" id="comments" style="padding:40px;">
                    <h3 style="margin-top:0; border-bottom:1px solid rgba(0,0,0,0.05); padding-bottom:15px; font-size:1.5rem;">
                        <i class="ri-discuss-line" style="color:var(--primary)"></i> 交流探讨 (<span id="c-count">0</span>)
                    </h3>
                    <div id="c-list" style="margin: 30px 0;">正在呼叫服务器...</div>
                    <div style="background:rgba(0,0,0,0.02); padding:30px; border-radius:16px; border:1px solid rgba(0,0,0,0.05);">
                        <h4 style="margin-top:0; font-size:1.2rem;">发表见解 <span id="reply-target" style="color:var(--primary); font-size:0.95rem; display:none;"></span></h4>
                        <form id="c-form">
                            <input type="hidden" id="p_id" value="<?= $post['id'] ?>"><input type="hidden" id="parent_id" value="0">
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px;">
                                <input type="text" id="c_name" placeholder="您的昵称 *" required style="padding:15px; border:var(--border); border-radius:10px; outline:none; background:var(--surface); color:var(--text);">
                                <input type="email" id="c_mail" placeholder="邮箱 (填QQ邮箱自动获头像) *" required style="padding:15px; border:var(--border); border-radius:10px; outline:none; background:var(--surface); color:var(--text);">
                            </div>
                            <textarea id="c_text" rows="5" placeholder="写下你的想法，支持简单排版..." required style="width:100%; box-sizing:border-box; padding:15px; border:var(--border); border-radius:10px; outline:none; margin-bottom:20px; background:var(--surface); color:var(--text); resize:vertical;"></textarea>
                            <button type="submit" style="background:var(--primary); color:#fff; border:none; padding:15px 35px; border-radius:10px; cursor:pointer; font-weight:bold; font-size:1.1rem; box-shadow:0 5px 15px rgba(108,92,231,0.3); transition:0.3s;">发射评论 🚀</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <?= renderSidebar($pdo, true, $post['tags']) ?>
        </main>

        <footer style="text-align:center; padding: 50px 20px; color:var(--text-mute); font-size:0.95rem; border-top:var(--border); margin-top:50px;">
            <?= htmlspecialchars(getOption('footer_text')) ?><br>
            <?php if($icp = getOption('icp_beian')): ?><a href="https://beian.miit.gov.cn/" target="_blank" style="margin-right:15px;"><?= htmlspecialchars($icp) ?></a><?php endif; ?>
            <?php if($gov = getOption('gov_beian')): ?><span><?= htmlspecialchars($gov) ?></span><?php endif; ?>
        </footer>

        <script src="../../assets/js/front.js"></script>
        <script>
            function loadComments() {
                fetch('../../api/comment.php?action=get&post_id=<?= $post['id'] ?>').then(r=>r.json()).then(data => {
                    document.getElementById('c-count').innerText = data.length;
                    if(data.length === 0) { document.getElementById('c-list').innerHTML = '<div style="text-align:center; color:var(--text-mute); padding:30px;">暂无评论，来做第一个发言的人吧！</div>'; return; }
                    let html = ''; let cMap = {}; data.forEach(c => { c.children = []; cMap[c.id] = c; });
                    let roots = []; data.forEach(c => { if(c.parent_id > 0 && cMap[c.parent_id]) { cMap[c.parent_id].children.push(c); } else { roots.push(c); } });

                    function renderNode(c, isChild = false) {
                        let replyHtml = isChild ? `<span style="color:var(--primary); font-size:0.85rem;"><i class="ri-reply-line"></i> 回复 @${cMap[c.parent_id].nickname}</span>` : '';
                        let adminHtml = c.reply_content ? `<div style="background:rgba(108,92,231,0.1); padding:15px; border-radius:10px; margin-top:15px; border-left:4px solid var(--primary); font-size:0.95rem; color:var(--text);"><strong>博主回复:</strong> ${c.reply_content}</div>` : '';
                        let res = `
                        <div style="display:flex; gap:20px; margin-bottom:30px;">
                            <img src="${c.avatar}" style="width:50px; height:50px; border-radius:50%; box-shadow:0 4px 10px rgba(0,0,0,0.1);">
                            <div style="flex:1; background:var(--surface); border:var(--border); padding:20px; border-radius:0 16px 16px 16px; box-shadow:var(--shadow);">
                                <div style="font-weight:900; font-size:1.05rem; margin-bottom:8px; color:var(--text);">${c.nickname} ${replyHtml} <span style="font-size:0.85rem; color:var(--text-mute); font-weight:normal; margin-left:15px;">${c.created_at}</span></div>
                                <div style="color:var(--text-mute); font-size:1rem; line-height:1.7;">${c.content}</div>
                                ${adminHtml}
                                <div style="text-align:right; margin-top:10px;"><button onclick="setReply(${c.id}, '${c.nickname}')" style="background:rgba(0,0,0,0.05); border:none; color:var(--text); padding:6px 15px; border-radius:20px; cursor:pointer; font-weight:bold; transition:0.3s;">回复 Ta</button></div>
                            </div>
                        </div>`;
                        if(c.children.length > 0) {
                            res += `<div style="margin-left:70px; position:relative;">`;
                            res += `<div style="position:absolute; left:-35px; top:0; bottom:0; width:2px; background:rgba(0,0,0,0.05);"></div>`;
                            c.children.forEach(child => res += renderNode(child, true));
                            res += `</div>`;
                        }
                        return res;
                    }
                    roots.forEach(r => html += renderNode(r));
                    document.getElementById('c-list').innerHTML = html;
                });
            }
            loadComments();

            function setReply(id, name) {
                document.getElementById('parent_id').value = id;
                const t = document.getElementById('reply-target'); t.style.display = 'inline';
                t.innerHTML = `(@${name}) <a href="javascript:void(0)" onclick="cancelReply()" style="color:var(--text-mute); margin-left:10px;">取消回复</a>`;
                document.getElementById('c_text').focus();
            }
            function cancelReply() { document.getElementById('parent_id').value = 0; document.getElementById('reply-target').style.display = 'none'; }

            document.getElementById('c-form').addEventListener('submit', function(e){
                e.preventDefault();
                const btn = this.querySelector('button'); btn.innerText = '发送中...'; btn.disabled = true;
                const fd = new FormData();
                fd.append('post_id', document.getElementById('p_id').value); fd.append('parent_id', document.getElementById('parent_id').value);
                fd.append('nickname', document.getElementById('c_name').value); fd.append('email', document.getElementById('c_mail').value);
                fd.append('content', document.getElementById('c_text').value);

                fetch('../../api/comment.php?action=post', { method: 'POST', body: fd }).then(r=>r.json()).then(res => {
                    alert(res.msg); if(res.success) { this.reset(); cancelReply(); loadComments(); }
                }).finally(() => { btn.innerText = '发射评论 🚀'; btn.disabled = false; });
            });
        </script>
    </body></html>
    <?php
    file_put_contents($file_path, ob_get_clean()); return true;
}

function generateHomeHtml() {
    global $pdo;
    generateAssets();

    $limit = intval(getOption('post_limit', '12'));
    $build_dir = getOption('build_dir', 'article');
    // 【关键修复点】SQL 中的 slug 指定为 p.slug 解决冲突
    $posts = $pdo->query("SELECT p.*, c.name as cat_name, c.slug as cat_slug FROM rb_posts p LEFT JOIN rb_categories c ON p.category_id = c.id WHERE p.status = 1 ORDER BY p.created_at DESC LIMIT $limit")->fetchAll();
    $nav_cats = $pdo->query("SELECT * FROM rb_categories WHERE show_in_nav = 1 AND parent_id = 0 ORDER BY sort_order ASC")->fetchAll();

    $site_title = getOption('site_title', 'Rebirth');
    $site_sub = getOption('site_subtitle');
    $logo_type = getOption('logo_type', 'text');
    $logo_html = $logo_type == 'img' ? '<img src="'.htmlspecialchars(getOption('logo_img')).'">' : getOption('site_logo', 'Re<span>birth</span>');
    $top_mode = getOption('top_area_mode', 'text');
    $sliders = json_decode(getOption('slider_data', '[]'), true) ?: [];

    ob_start();
    ?>
    <!DOCTYPE html>
    <html lang="zh-CN">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= htmlspecialchars($site_title) . ($site_sub && getOption('show_subtitle_in_title')=='1' ? ' - '.$site_sub : '') ?></title>
        <meta name="description" content="<?= htmlspecialchars(getOption('site_desc')) ?>">
        <?php if($f=getOption('site_favicon')): ?><link rel="icon" href="<?= $f ?>"><?php endif; ?>
        <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
        <link rel="stylesheet" href="assets/css/front.css">
        <style>
            <?php if(getOption('home_layout') == 'list'): ?>
                .grid { grid-template-columns: repeat(2, 1fr); }
                .card { flex-direction: row; height: 250px;} .c-img { width: 45%; height: 100%; } .c-body { justify-content: center;}
                @media(max-width:768px){.grid{grid-template-columns:1fr;}.card{flex-direction:column;height:auto;}.c-img{width:100%;height:220px;}}
            <?php else: ?>
                .grid { grid-template-columns: repeat(<?= getOption('home_columns', '3') ?>, 1fr); }
                @media(max-width:900px){.grid{grid-template-columns:repeat(2,1fr);}} @media(max-width:600px){.grid{grid-template-columns:1fr;}}
            <?php endif; ?>
            .c-title { -webkit-line-clamp: <?= getOption('title_lines', '2') ?>; font-size:1.4rem;}
            <?php if(getOption('sidebar_enable')=='0'): ?> .page-wrap { grid-template-columns: 1fr; max-width: 1000px;} <?php endif; ?>
        </style>
    </head>
    <body>
        <header class="rb-header">
            <a href="index.html" class="logo"><?= $logo_html ?></a>
            <div class="nav">
                <a href="index.html">首页</a>
                <?php foreach($nav_cats as $c): ?>
                    <a href="<?= $build_dir ?>/<?= $c['slug'] ?>/index.html"><?= $c['name'] ?></a>
                <?php endforeach; ?>
                <?php if(getOption('enable_dark_mode', '1') == '1'): ?>
                    <button class="theme-toggle" id="theme-btn" onclick="toggleTheme()"><i class="ri-moon-line"></i> 暗色</button>
                <?php endif; ?>
            </div>
        </header>

        <?php if($top_mode == 'slider' && !empty($sliders)): ?>
            <div class="slider">
                <?php foreach($sliders as $k => $s): ?>
                <div class="slide-item <?= $k==0?'active':'' ?>" style="background-image:url('<?= $s['img'] ?>')">
                    <div class="slide-content">
                        <h2><?= htmlspecialchars($s['title']) ?></h2>
                        <?php if($s['link']): ?><a href="<?= $s['link'] ?>" class="slide-btn">探索更多</a><?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php elseif($top_mode == 'bg' && getOption('top_bg_image')): ?>
            <div class="hero">
                <div class="hero-bg" style="background-image:url('<?= getOption('top_bg_image') ?>')"></div>
                <div class="hero-content">
                    <h1 style="font-size:4rem;"><?= htmlspecialchars($site_sub) ?></h1>
                    <p style="font-size:1.3rem; opacity:0.9;"><?= htmlspecialchars(getOption('site_desc')) ?></p>
                </div>
            </div>
        <?php else: ?>
            <div style="padding:180px 20px 100px; text-align:center;">
                <h1 style="font-size:3.5rem; margin:0 0 15px 0; color:var(--text); font-weight:900; letter-spacing:-1px;"><?= htmlspecialchars($site_sub) ?></h1>
                <p style="font-size:1.25rem; color:var(--text-mute); max-width:600px; margin:0 auto; line-height:1.6;"><?= htmlspecialchars(getOption('site_desc')) ?></p>
            </div>
        <?php endif; ?>

        <main class="page-wrap" <?= ($top_mode=='text') ? 'style="margin-top:0;"' : '' ?>>
            <div class="main-col">
                <div class="grid">
                    <?php foreach($posts as $p): 
                        // 动态获取链接
                        $link = "{$build_dir}/" . ($p['cat_slug']?:'uncategorized') . "/" . $p['slug'] . ".html";
                        $img = $p['cover_image'] ?: "https://images.unsplash.com/photo-1499750310107-5fef28a66643?auto=format&fit=crop&w=600&q=80"; 
                    ?>
                    <a href="<?= $link ?>" class="card">
                        <div class="c-img" style="background-image:url('<?= $img ?>')"></div>
                        <div class="c-body">
                            <div class="c-cat"><?= $p['cat_name'] ?: '未分类' ?></div>
                            <h2 class="c-title"><?= htmlspecialchars($p['title']) ?></h2>
                            <?php if(getOption('show_summary')=='1'): ?><div class="c-desc"><?= htmlspecialchars($p['summary']) ?></div><?php endif; ?>
                            <div style="margin-top:15px; font-size:0.85rem; color:var(--text-mute); font-weight:bold; display:flex; align-items:center; gap:5px;">
                                <i class="ri-time-line"></i> <?= date('Y-m-d', strtotime($p['created_at'])) ?>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?= renderSidebar($pdo) ?>
        </main>
        
        <footer style="text-align:center; padding: 60px 20px; color:var(--text-mute); font-size:0.95rem; border-top:var(--border); margin-top:50px;">
            <?= htmlspecialchars(getOption('footer_text')) ?><br>
            <?php if($icp = getOption('icp_beian')): ?><a href="https://beian.miit.gov.cn/" target="_blank" style="margin-right:15px;"><?= htmlspecialchars($icp) ?></a><?php endif; ?>
            <?php if($gov = getOption('gov_beian')): ?><span><?= htmlspecialchars($gov) ?></span><?php endif; ?>
            <p style="margin-top:15px; opacity:0.8;">Powered by Rebirth V<?= getOption('site_version', '4.5') ?></p>
        </footer>
        <script src="assets/js/front.js"></script>
    </body></html>
    <?php
    file_put_contents(dirname(__DIR__) . '/index.html', ob_get_clean());
}
?>