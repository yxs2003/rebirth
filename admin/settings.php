<?php
require_once '../core/db.php';
require_once '../core/functions.php';
checkAuth();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_options') {
    foreach ($_POST['options'] as $key => $value) {
        $stmt = $pdo->prepare("INSERT INTO rb_options (option_name, option_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE option_value = ?");
        $stmt->execute([$key, $value, $value]);
    }
    generateHomeHtml(); 
    echo "<script>alert('设置已保存并生效！'); location.href='settings.php';</script>";
    exit;
}

$options_raw = $pdo->query("SELECT * FROM rb_options")->fetchAll();
$options = [];
foreach ($options_raw as $opt) $options[$opt['option_name']] = $opt['option_value'];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8"><title>系统设置 - Rebirth V4.5</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        .tabs-container { display: flex; gap: 30px; }
        .tabs-nav { width: 220px; flex-shrink: 0; display:flex; flex-direction:column; gap:8px; }
        .tab-btn { padding: 12px 20px; background: rgba(255,255,255,0.6); border: 1px solid #eee; border-radius: 8px; text-align: left; cursor: pointer; font-weight: 600; color: #636e72; transition: 0.3s; }
        .tab-btn.active, .tab-btn:hover { background: var(--primary); color: #fff; border-color:var(--primary);}
        .tab-content { flex: 1; background: rgba(255,255,255,0.8); border-radius: 16px; padding: 35px; box-shadow: 0 5px 20px rgba(0,0,0,0.02); display: none; }
        .tab-content.active { display: block; animation: fadeIn 0.4s ease; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .set-item { margin-bottom: 25px; }
        .set-label { display: block; font-weight: 600; margin-bottom: 8px; color: #2d3436; }
    </style>
</head>
<body>
<div class="admin-container">
    <?php include 'sidebar_template.php'; ?>
    <main class="main-content">
        <?php renderTopBar('⚙️ 全局设置'); ?>
        
        <form method="POST" class="tabs-container">
            <input type="hidden" name="action" value="save_options">
            
            <div class="tabs-nav">
                <button type="button" class="tab-btn active" onclick="switchTab('basic')"><i class="ri-global-line"></i> 基本信息</button>
                <button type="button" class="tab-btn" onclick="switchTab('theme')"><i class="ri-palette-line"></i> 主题与文章列表</button>
                <button type="button" class="tab-btn" onclick="switchTab('hero')"><i class="ri-slideshow-line"></i> 首页幻灯片</button>
                <button type="button" class="tab-btn" onclick="switchTab('sidebar')"><i class="ri-layout-right-2-line"></i> 侧边栏与作者</button>
                <button type="button" class="tab-btn" onclick="switchTab('footer')"><i class="ri-layout-bottom-line"></i> 底部与备案</button>
                
                <button type="submit" class="btn" style="margin-top:20px;"><i class="ri-save-line"></i> 保存所有设置</button>
            </div>

            <div style="flex:1;">
                <div id="tab-basic" class="tab-content active">
                    <h2 style="margin-top:0; border-bottom:1px dashed #eee; padding-bottom:15px;">SEO 与站点标识</h2>
                    <div class="set-item">
                        <label class="set-label">网站主标题 (Title)</label>
                        <input type="text" name="options[site_title]" class="form-input" value="<?= htmlspecialchars($options['site_title'] ?? '') ?>" required>
                    </div>
                    <div class="set-item">
                        <label class="set-label">副标题 (Slogan)</label>
                        <input type="text" name="options[site_subtitle]" class="form-input" value="<?= htmlspecialchars($options['site_subtitle'] ?? '') ?>">
                        <label><input type="checkbox" name="options[show_subtitle_in_title]" value="1" <?= ($options['show_subtitle_in_title']??'1')=='1'?'checked':'' ?>> 在浏览器标签栏显示副标题</label>
                    </div>
                    <div class="set-item">
                        <label class="set-label">SEO 描述 (Description)</label>
                        <textarea name="options[site_desc]" class="form-input" rows="2"><?= htmlspecialchars($options['site_desc'] ?? '') ?></textarea>
                    </div>
                    <div class="set-item">
                        <label class="set-label">导航栏 Logo 模式</label>
                        <select name="options[logo_type]" class="form-input">
                            <option value="text" <?= ($options['logo_type'] ?? '') == 'text' ? 'selected' : '' ?>>纯文字 / HTML</option>
                            <option value="img" <?= ($options['logo_type'] ?? '') == 'img' ? 'selected' : '' ?>>图片</option>
                        </select>
                        <input type="text" name="options[site_logo]" class="form-input" value="<?= htmlspecialchars($options['site_logo'] ?? '') ?>" placeholder="文字HTML: Re<span>birth</span>">
                        <input type="text" name="options[logo_img]" class="form-input" value="<?= htmlspecialchars($options['logo_img'] ?? '') ?>" placeholder="图片URL: http://...">
                    </div>
                    <div class="set-item">
                        <label class="set-label">站点图标 (Favicon URL)</label>
                        <input type="text" name="options[site_favicon]" class="form-input" value="<?= htmlspecialchars($options['site_favicon'] ?? '') ?>">
                    </div>
                    
                    <div class="set-item">
                        <label class="set-label">文章静态化生成目录名</label>
                        <input type="text" name="options[build_dir]" class="form-input" value="<?= htmlspecialchars($options['build_dir'] ?? 'article') ?>">
                        <small style="color:#e74c3c;">修改此项后，系统会在新目录生成网页。旧目录的文件需要您手动登录服务器删除。</small>
                    </div>
                </div>

                <div id="tab-theme" class="tab-content">
                    <h2 style="margin-top:0; border-bottom:1px dashed #eee; padding-bottom:15px;">视觉引擎与列表排版</h2>
                    <div class="set-item">
                        <label class="set-label" style="color:var(--primary); font-size:1.1rem;">选择全站主题</label>
                        <select name="options[site_theme]" class="form-input" style="font-weight:bold;">
                            <option value="glass" <?= ($options['site_theme'] ?? '') == 'glass' ? 'selected' : '' ?>>💎 玻璃拟态 (推荐)</option>
                            <option value="mac" <?= ($options['site_theme'] ?? '') == 'mac' ? 'selected' : '' ?>>💻 仿 Mac 视窗风格</option>
                            <option value="news" <?= ($options['site_theme'] ?? '') == 'news' ? 'selected' : '' ?>>📰 老旧报纸复古风</option>
                            <option value="text" <?= ($options['site_theme'] ?? '') == 'text' ? 'selected' : '' ?>>📝 极简纯文字笔记本</option>
                        </select>
                    </div>
                    
                    <div class="set-item">
                        <label class="set-label">允许用户切换暗色模式</label>
                        <select name="options[enable_dark_mode]" class="form-input">
                            <option value="1" <?= ($options['enable_dark_mode'] ?? '1') == '1' ? 'selected' : '' ?>>开启</option>
                            <option value="0" <?= ($options['enable_dark_mode'] ?? '1') == '0' ? 'selected' : '' ?>>关闭</option>
                        </select>
                    </div>

                    <div class="set-item" style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
                        <div>
                            <label class="set-label">文章排列模式</label>
                            <select name="options[home_layout]" class="form-input">
                                <option value="card" <?= ($options['home_layout'] ?? '') == 'card' ? 'selected' : '' ?>>卡片排版 (顶部图片)</option>
                                <option value="list" <?= ($options['home_layout'] ?? '') == 'list' ? 'selected' : '' ?>>列表排版 (左图右文)</option>
                            </select>
                        </div>
                        <div>
                            <label class="set-label">首页每页提取文章数</label>
                            <input type="number" name="options[post_limit]" class="form-input" value="<?= htmlspecialchars($options['post_limit'] ?? '12') ?>">
                        </div>
                    </div>
                    <div class="set-item">
                        <label class="set-label">评论审核机制</label>
                        <select name="options[comment_audit]" class="form-input">
                            <option value="0" <?= ($options['comment_audit'] ?? '') == '0' ? 'selected' : '' ?>>不需要，直接展示 (含60秒防刷)</option>
                            <option value="1" <?= ($options['comment_audit'] ?? '') == '1' ? 'selected' : '' ?>>需要，管理员在后台审核后才展示</option>
                        </select>
                    </div>
                </div>

                <div id="tab-hero" class="tab-content">
                    <h2 style="margin-top:0; border-bottom:1px dashed #eee; padding-bottom:15px;">顶部视觉区域配置</h2>
                    <div class="set-item">
                        <label class="set-label">首页顶部区域模式</label>
                        <select name="options[top_area_mode]" class="form-input">
                            <option value="text" <?= ($options['top_area_mode'] ?? '') == 'text' ? 'selected' : '' ?>>纯文字标语 (不带背景)</option>
                            <option value="bg" <?= ($options['top_area_mode'] ?? '') == 'bg' ? 'selected' : '' ?>>静态全屏背景图</option>
                            <option value="slider" <?= ($options['top_area_mode'] ?? '') == 'slider' ? 'selected' : '' ?>>动态幻灯片 (Slider)</option>
                        </select>
                    </div>
                    <div class="set-item">
                        <label class="set-label">静态背景图 URL (若选择静态背景模式)</label>
                        <input type="text" name="options[top_bg_image]" class="form-input" value="<?= htmlspecialchars($options['top_bg_image'] ?? '') ?>">
                    </div>
                    <div class="set-item">
                        <label class="set-label">幻灯片数据 (JSON 格式)</label>
                        <p style="font-size:0.85rem; color:#999; margin-top:-5px;">格式：[{"img":"图片地址","title":"大标题","link":"跳转链接"}]</p>
                        <textarea name="options[slider_data]" class="form-input" rows="4"><?= htmlspecialchars($options['slider_data'] ?? '[]') ?></textarea>
                    </div>
                </div>

                <div id="tab-sidebar" class="tab-content">
                    <h2 style="margin-top:0; border-bottom:1px dashed #eee; padding-bottom:15px;">博主卡片与挂件</h2>
                    <div class="set-item">
                        <label class="set-label">全站侧边栏总开关</label>
                        <select name="options[sidebar_enable]" class="form-input">
                            <option value="1" <?= ($options['sidebar_enable'] ?? '1') == '1' ? 'selected' : '' ?>>开启</option>
                            <option value="0" <?= ($options['sidebar_enable'] ?? '1') == '0' ? 'selected' : '' ?>>关闭 (纯净居中)</option>
                        </select>
                    </div>
                    <div class="set-item">
                        <label class="set-label">侧边栏组件排序 (英文逗号分隔)</label>
                        <p style="font-size:0.85rem; color:#999; margin-top:-5px;">可用组件：author(作者), capsule(时间胶囊), toc(文章目录), recent(最新文章)</p>
                        <input type="text" name="options[sidebar_blocks]" class="form-input" value="<?= htmlspecialchars($options['sidebar_blocks'] ?? 'author,toc,capsule,recent') ?>">
                    </div>
                    <hr style="border:0; border-top:1px dashed #eee; margin:20px 0;">
                    <div class="set-item" style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
                        <div><label class="set-label">博主名字</label><input type="text" name="options[author_name]" class="form-input" value="<?= htmlspecialchars($options['author_name'] ?? '') ?>"></div>
                        <div><label class="set-label">博主头像 URL</label><input type="text" name="options[author_avatar]" class="form-input" value="<?= htmlspecialchars($options['author_avatar'] ?? '') ?>"></div>
                        <div><label class="set-label">性别/年龄标识</label><input type="text" name="options[author_gender]" class="form-input" value="<?= htmlspecialchars($options['author_gender'] ?? '') ?>" placeholder="如：♂ 22岁"></div>
                        <div><label class="set-label">公开邮箱</label><input type="text" name="options[author_email]" class="form-input" value="<?= htmlspecialchars($options['author_email'] ?? '') ?>"></div>
                    </div>
                    <div class="set-item">
                        <label class="set-label">博主一句话简介</label>
                        <input type="text" name="options[author_desc]" class="form-input" value="<?= htmlspecialchars($options['author_desc'] ?? '') ?>">
                    </div>
                </div>

                <div id="tab-footer" class="tab-content">
                    <div class="set-item">
                        <label class="set-label">网站底部说明</label>
                        <input type="text" name="options[footer_text]" class="form-input" value="<?= htmlspecialchars($options['footer_text'] ?? '') ?>">
                    </div>
                    <div class="set-item">
                        <label class="set-label">文章页版权声明区域说明</label>
                        <textarea name="options[post_copyright]" class="form-input" rows="2"><?= htmlspecialchars($options['post_copyright'] ?? '本文遵循 CC BY-NC-SA 4.0 协议，转载请注明出处。') ?></textarea>
                    </div>
                    <div class="set-item">
                        <label class="set-label">ICP 备案号 (留空不显示)</label>
                        <input type="text" name="options[icp_beian]" class="form-input" value="<?= htmlspecialchars($options['icp_beian'] ?? '') ?>">
                    </div>
                    <div class="set-item">
                        <label class="set-label">公安备案号 (留空不显示)</label>
                        <input type="text" name="options[gov_beian]" class="form-input" value="<?= htmlspecialchars($options['gov_beian'] ?? '') ?>">
                    </div>
                </div>
            </div>
        </form>
    </main>
</div>
<script>
    function switchTab(tabId) {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        event.target.classList.add('active');
        document.getElementById('tab-' + tabId).classList.add('active');
    }
</script>
</body></html>