<?php
require_once '../core/db.php';
checkAuth();

$post = null;
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM rb_posts WHERE id = ?");
    $stmt->execute([intval($_GET['id'])]);
    $post = $stmt->fetch();
}

function getCatOptions($pdo, $parent=0, $level=0) {
    $res = [];
    $cats = $pdo->query("SELECT * FROM rb_categories WHERE parent_id=$parent ORDER BY sort_order")->fetchAll();
    foreach($cats as $c) {
        $c['level'] = $level;
        $res[] = $c;
        $res = array_merge($res, getCatOptions($pdo, $c['id'], $level+1));
    }
    return $res;
}
$cats = getCatOptions($pdo);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8"><title><?= $post ? '编辑' : '创作' ?> - Rebirth</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        .writer-layout { display: grid; grid-template-columns: 1fr 340px; gap: 30px; height: calc(100vh - 100px); }
        .editor-area { display: flex; flex-direction: column; height: 100%; box-shadow:0 10px 30px rgba(0,0,0,0.05); border-radius:16px; background:#fff;}
        #title-input { font-size: 2rem; font-weight: 800; border: none; background: transparent; padding: 30px; width: 100%; outline: none; color: #2d3436; border-bottom:1px solid #eee;}
        .toolbar { background: #fafafa; padding: 15px; display: flex; flex-wrap: wrap; gap: 10px; align-items: center; border-bottom:1px solid #eee;}
        .tool-btn { background: #fff; border: 1px solid #e0e0e0; padding: 8px 12px; border-radius: 8px; cursor: pointer; color: #4b4b4b; font-size: 1rem; transition: 0.2s; display:flex; align-items:center; gap:6px; font-weight:bold; box-shadow:0 2px 5px rgba(0,0,0,0.02);}
        .tool-btn:hover { background: var(--primary); color: #fff; border-color:var(--primary); transform:translateY(-2px);}
        .color-picker { border: none; padding: 0; width: 22px; height: 22px; cursor: pointer; background:none; }
        #content-editor { flex: 1; padding: 30px; font-size: 1.15rem; line-height: 1.8; border: none; border-radius: 0 0 16px 16px; background: transparent; resize: none; outline: none; font-family: 'Inter', monospace; white-space: pre-wrap; color:#333;}
        .settings-panel { background: rgba(255,255,255,0.8); padding: 30px; border-radius: 16px; border: 1px solid #eee; overflow-y: auto; box-shadow: 0 10px 30px rgba(0,0,0,0.02);}
        .panel-label { font-size: 0.95rem; font-weight: 800; color: #2d3436; margin-bottom: 8px; display: block; }
    </style>
</head>
<body>
<div class="admin-container">
    <?php include 'sidebar_template.php'; ?>
    <main class="main-content" style="padding-top: 20px;">
        <form id="post-form" class="writer-layout">
            <input type="hidden" id="post_id" name="post_id" value="<?= $post ? $post['id'] : '' ?>">
            
            <div class="editor-area">
                <input type="text" name="title" id="title-input" value="<?= htmlspecialchars($post['title'] ?? '') ?>" placeholder="在此输入惊艳的标题..." required>
                
                <div class="toolbar">
                    <button type="button" class="tool-btn" onclick="insertTag('<b>', '</b>')" title="加粗"><i class="ri-bold"></i></button>
                    <button type="button" class="tool-btn" onclick="insertTag('<i>', '</i>')" title="斜体"><i class="ri-italic"></i></button>
                    <button type="button" class="tool-btn" onclick="insertTag('<u>', '</u>')" title="下划线"><i class="ri-underline"></i></button>
                    <button type="button" class="tool-btn" onclick="insertTag('<s>', '</s>')" title="删除线"><i class="ri-strikethrough"></i></button>
                    <button type="button" class="tool-btn" onclick="insertTag('<mark>', '</mark>')"><i class="ri-mark-pen-line"></i></button>
                    <button type="button" class="tool-btn" onclick="insertColor()"><i class="ri-font-color"></i><input type="color" id="font-color" class="color-picker" value="#ff0000" onclick="event.stopPropagation()"></button>
                    
                    <div style="width:2px; height:20px; background:#e0e0e0; margin:0 5px;"></div>
                    <button type="button" class="tool-btn" onclick="insertTag('<h2>', '</h2>')">H2</button>
                    <button type="button" class="tool-btn" onclick="insertTag('<h3>', '</h3>')">H3</button>
                    <button type="button" class="tool-btn" onclick="insertTag('<blockquote>\n', '\n</blockquote>')"><i class="ri-double-quotes-l"></i></button>
                    
                    <div style="width:2px; height:20px; background:#e0e0e0; margin:0 5px;"></div>
                    <button type="button" class="tool-btn" onclick="rbModal.prompt('插入超级链接', [{placeholder:'输入网址'}], (res) => insertTag(`<a href='${res[0]}' target='_blank' class='rb-link'>链接内容</a>`, ''))"><i class="ri-link"></i></button>
                    <button type="button" class="tool-btn" onclick="triggerImageUpload()"><i class="ri-image-add-line"></i></button>
                    <button type="button" class="tool-btn" onclick="rbModal.prompt('插入音频', [{placeholder:'音频直链URL'}], (res) => insertTag(`\n<audio controls src='${res[0]}' style='width:100%; margin:1em 0; outline:none; border-radius:8px;'></audio>\n`, ''))"><i class="ri-music-2-line"></i></button>
                    
                    <div style="width:2px; height:20px; background:#e0e0e0; margin:0 5px;"></div>
                    <button type="button" class="tool-btn" onclick="insertTag('\n<hr class=\'rb-divider\'>\n', '')" title="分割线"><i class="ri-separator"></i></button>
                    <button type="button" class="tool-btn" onclick="insertMacCode()"><i class="ri-macbook-line"></i> 代码框</button>
                    <button type="button" class="tool-btn" onclick="insertDownload()"><i class="ri-download-cloud-line"></i> 下载</button>
                    
                    <button type="button" class="tool-btn" style="color:#1abc9c" onclick="insertAlert('success')"><i class="ri-checkbox-circle-fill"></i></button>
                    <button type="button" class="tool-btn" style="color:#3498db" onclick="insertAlert('info')"><i class="ri-information-fill"></i></button>
                    <button type="button" class="tool-btn" style="color:#f39c12" onclick="insertAlert('warn')"><i class="ri-error-warning-fill"></i></button>
                    <button type="button" class="tool-btn" style="color:#e74c3c" onclick="insertAlert('error')"><i class="ri-close-circle-fill"></i></button>
                    
                    <input type="file" id="img-upload-input" style="display:none" onchange="uploadImage(this)">
                </div>
                
                <textarea name="content" id="content-editor" placeholder="在此开启思维的火花..."><?= htmlspecialchars($post['content'] ?? '') ?></textarea>
            </div>

            <aside class="settings-panel">
                <button type="submit" class="btn" style="width:100%; height:55px; font-size:1.15rem; margin-bottom:25px; box-shadow:0 5px 20px rgba(108,92,231,0.3);"><i class="ri-send-plane-fill"></i> <?= $post ? '保存并静态化' : '发布文章' ?></button>

                <label class="panel-label">URL 别名 (Slug)</label>
                <input type="text" name="slug" class="form-input" value="<?= htmlspecialchars($post['slug'] ?? '') ?>" placeholder="english-slug" required>

                <label class="panel-label">发布时间</label>
                <input type="datetime-local" step="1" name="created_at" class="form-input" value="<?= $post ? date('Y-m-d\TH:i:s', strtotime($post['created_at'])) : date('Y-m-d\TH:i:s') ?>">

                <label class="panel-label">所属分类</label>
                <select name="category_id" class="form-input" style="background:#fff;">
                    <?php foreach($cats as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= ($post && $post['category_id'] == $c['id']) ? 'selected' : '' ?>>
                            <?= str_repeat('&nbsp;&nbsp;', $c['level']) . '├ ' . htmlspecialchars($c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label class="panel-label">文章标签 (逗号分隔)</label>
                <input type="text" name="tags" class="form-input" value="<?= htmlspecialchars($post['tags'] ?? '') ?>" placeholder="PHP,教程">

                <label class="panel-label" style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                    <input type="checkbox" name="show_meta" value="1" <?= (!isset($post) || $post['show_meta']) ? 'checked' : '' ?> style="width:18px;height:18px;"> 在文章页显示分类与时间
                </label>

                <label class="panel-label" style="margin-top:20px;">封面图</label>
                <input type="text" name="cover_image" class="form-input" value="<?= htmlspecialchars($post['cover_image'] ?? '') ?>" placeholder="URL...">

                <label class="panel-label">自定义摘要</label>
                <textarea name="summary" class="form-input" rows="4" placeholder="留空则自动提取正文..."><?= htmlspecialchars($post['summary'] ?? '') ?></textarea>
            </aside>
        </form>
    </main>
</div>

<script>
    // 防手滑：本地缓存自动保存系统
    document.addEventListener('DOMContentLoaded', () => {
        const titleInp = document.getElementById('title-input');
        const contentInp = document.getElementById('content-editor');
        const isNew = !document.getElementById('post_id').value;

        if (isNew && localStorage.getItem('rb_autosave_c')) {
            rbModal.confirm('系统发现您有未保存的草稿，是否恢复？', () => {
                titleInp.value = localStorage.getItem('rb_autosave_t');
                contentInp.value = localStorage.getItem('rb_autosave_c');
            });
        }
        
        setInterval(() => {
            if (isNew && contentInp.value.trim().length > 10) {
                localStorage.setItem('rb_autosave_t', titleInp.value);
                localStorage.setItem('rb_autosave_c', contentInp.value);
            }
        }, 5000);
    });

    function insertTag(start, end) {
        const t = document.getElementById('content-editor');
        const s = t.selectionStart, e = t.selectionEnd;
        t.value = t.value.substring(0, s) + start + t.value.substring(s, e) + end + t.value.substring(e);
        t.focus();
        t.selectionEnd = s + start.length + (e - s);
    }
    
    document.getElementById('content-editor').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); insertTag('\n<br>\n', ''); }
    });

    function insertColor() { insertTag('<span style="color:' + document.getElementById('font-color').value + ';">', '</span>'); }

    function insertDownload() {
        rbModal.prompt('极客下载框', [{placeholder:'资源名称'},{placeholder:'下载链接'},{placeholder:'提取码(选填)'}], (res) => {
            const title = res[0] || '资源下载'; const link = res[1] || '#'; const code = res[2];
            let meta = code ? `提取码: <span class="rb-download-code" onclick="copyRbText(this)">${code}</span>` : '点击按钮直接下载';
            insertTag(`\n<div class="rb-download">\n  <div class="rb-download-left"><i class="ri-folder-zip-fill rb-download-icon"></i>\n    <div><div class="rb-download-title">${title}</div><div class="rb-download-meta">${meta}</div></div>\n  </div>\n  <a href="${link}" target="_blank" class="rb-download-btn"><i class="ri-download-cloud-2-line"></i> 获取资源</a>\n</div>\n<p><br></p>\n`, '');
        });
    }

    function insertMacCode() {
        rbModal.prompt('代码片段', [{placeholder:'语言(如 php, js)', value:'text'}], (res) => {
            insertTag(`\n<div class="rb-mac-code">\n  <div class="mac-header">\n    <div class="mac-dots"><div class="mac-dot rb-dot-r"></div><div class="mac-dot rb-dot-y"></div><div class="mac-dot rb-dot-g"></div></div>\n    <button class="mac-copy" onclick="copyRbCode(this)">复制代码</button>\n  </div>\n  <pre class="mac-body"><code class="language-${res[0]}">\n// 贴入代码...\n  </code></pre>\n</div>\n<p><br></p>\n`, '');
        });
    }

    function insertAlert(type) {
        let icon = '', text = '';
        if(type=='success'){ icon='ri-checkbox-circle-fill'; text='成功完成的操作。'; }
        if(type=='info'){ icon='ri-information-fill'; text='这是一条普通的提示信息。'; }
        if(type=='warn'){ icon='ri-error-warning-fill'; text='警告：请注意此项内容。'; }
        if(type=='error'){ icon='ri-close-circle-fill'; text='发生严重错误，请停止。'; }
        insertTag(`\n<div class="rb-alert alert-${type}">\n  <i class="${icon} rb-alert-icon"></i>\n  <div class="rb-alert-text">${text}</div>\n</div>\n`, '');
    }

    function triggerImageUpload() { document.getElementById('img-upload-input').click(); }
    function uploadImage(input) {
        if (input.files && input.files[0]) {
            const fd = new FormData(); fd.append('file', input.files[0]);
            fetch('upload.php', { method: 'POST', body: fd }).then(r => r.json()).then(data => {
                if(data.location) insertTag('<img src="' + data.location + '" alt="image">', '');
                else window.alert('上传失败: ' + data.error);
            });
        }
    }

    // 健壮的发布逻辑拦截
    document.getElementById('post-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = this.querySelector('button[type="submit"]');
        const orig = btn.innerHTML;
        btn.innerHTML = '<i class="ri-loader-4-line ri-spin"></i> 静态魔法生效中...'; 
        btn.disabled = true;

        fetch('save_post.php', { method: 'POST', body: new FormData(this) })
        .then(r => r.text()) // 先获取文本，防止致命错误导致 JSON 解析崩溃
        .then(text => {
            try {
                const data = JSON.parse(text);
                if(data.success) { 
                    localStorage.removeItem('rb_autosave_t'); localStorage.removeItem('rb_autosave_c');
                    window.location.href = 'posts.php'; 
                } else { 
                    rbModal.alert('保存被拒绝: ' + data.message); 
                    btn.innerHTML = orig; btn.disabled = false; 
                }
            } catch (err) {
                // 如果 JSON 解析失败，说明 PHP 抛出了 HTML 报错
                console.error("服务端异常:", text);
                rbModal.alert('发生服务器致命错误，请按 F12 查看控制台详情。<br>您可以先复制保存您的正文避免丢失。');
                btn.innerHTML = orig; btn.disabled = false;
            }
        }).catch(err => {
            rbModal.alert('网络请求失败: ' + err);
            btn.innerHTML = orig; btn.disabled = false;
        });
    });
</script>
</body></html>