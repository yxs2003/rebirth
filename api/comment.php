<?php
require_once '../core/db.php';
checkAuth();

$post = null;
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM rb_posts WHERE id = ?");
    $stmt->execute([intval($_GET['id'])]);
    $post = $stmt->fetch();
}
// 简单的两级分类读取
$cats = $pdo->query("SELECT * FROM rb_categories ORDER BY parent_id, sort_order")->fetchAll();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8"><title><?= $post ? '编辑文章' : '写文章' ?> - Rebirth</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        .writer-layout { display: grid; grid-template-columns: 1fr 300px; gap: 20px; height: calc(100vh - 100px); }
        .editor-area { display: flex; flex-direction: column; height: 100%; }
        #title-input { font-size: 2rem; font-weight: 800; border: none; background: transparent; padding: 20px 0; width: 100%; outline: none; color: #2d3436; }
        .toolbar { background: #fff; padding: 10px; border-radius: 8px 8px 0 0; border: 1px solid #dfe6e9; border-bottom: none; display: flex; flex-wrap: wrap; gap: 8px; align-items: center;}
        .tool-btn { background: #f8f9fa; border: 1px solid #eee; padding: 6px 10px; border-radius: 4px; cursor: pointer; color: #4b4b4b; font-size: 0.9rem; transition: 0.2s; display:flex; align-items:center; gap:4px; }
        .tool-btn:hover { background: #dfe6e9; color: #2d3436; border-color:#ccc; }
        #content-editor { flex: 1; padding: 20px; font-size: 1.1rem; line-height: 1.6; border: 1px solid #dfe6e9; border-radius: 0 0 8px 8px; background: rgba(255,255,255,0.8); resize: none; outline: none; font-family: 'Inter', monospace; white-space: pre-wrap; }
        .settings-panel { background: rgba(255,255,255,0.6); padding: 20px; border-radius: 16px; border: 1px solid rgba(255,255,255,0.5); overflow-y: auto;}
    </style>
</head>
<body>
<div class="admin-container">
    <?php include 'sidebar_template.php'; ?>
    <main class="main-content" style="padding-top: 20px;">
        <form id="post-form" class="writer-layout">
            <input type="hidden" name="post_id" value="<?= $post ? $post['id'] : '' ?>">
            
            <div class="editor-area">
                <input type="text" name="title" id="title-input" value="<?= htmlspecialchars($post['title'] ?? '') ?>" placeholder="在此输入惊艳的标题..." required>
                
                <div class="toolbar">
                    <button type="button" class="tool-btn" onclick="insertTag('<b>', '</b>')"><i class="ri-bold"></i></button>
                    <button type="button" class="tool-btn" onclick="insertTag('<i>', '</i>')"><i class="ri-italic"></i></button>
                    <button type="button" class="tool-btn" onclick="insertTag('<s>', '</s>')"><i class="ri-strikethrough"></i></button>
                    <button type="button" class="tool-btn" onclick="insertTag('<mark>', '</mark>')"><i class="ri-mark-pen-line"></i></button>
                    
                    <div style="width:1px; height:20px; background:#ddd; margin:0 5px;"></div>
                    <button type="button" class="tool-btn" onclick="insertTag('<h2>', '</h2>')">H2</button>
                    <button type="button" class="tool-btn" onclick="insertTag('<h3>', '</h3>')">H3</button>
                    <button type="button" class="tool-btn" onclick="insertTag('<blockquote>\n', '\n</blockquote>')"><i class="ri-double-quotes-l"></i></button>
                    
                    <div style="width:1px; height:20px; background:#ddd; margin:0 5px;"></div>
                    <button type="button" class="tool-btn" onclick="openModal('link')"><i class="ri-link"></i></button>
                    <button type="button" class="tool-btn" onclick="triggerImageUpload()"><i class="ri-image-add-line"></i></button>
                    <button type="button" class="tool-btn" onclick="openModal('download')"><i class="ri-download-cloud-line"></i> 下载框</button>
                    <button type="button" class="tool-btn" onclick="insertMacCode()"><i class="ri-macbook-line"></i> 代码框</button>
                    
                    <div style="width:1px; height:20px; background:#ddd; margin:0 5px;"></div>
                    <button type="button" class="tool-btn" style="color:#16a085" onclick="insertTag('\n<div class=\'rb-alert alert-success\'><i class=\'ri-checkbox-circle-fill\'></i><div>成功提示信息</div></div>\n', '')"><i class="ri-check-line"></i></button>
                    <button type="button" class="tool-btn" style="color:#d35400" onclick="insertTag('\n<div class=\'rb-alert alert-warn\'><i class=\'ri-error-warning-fill\'></i><div>警告提示信息</div></div>\n', '')"><i class="ri-alarm-warning-line"></i></button>
                    <button type="button" class="tool-btn" style="color:#c0392b" onclick="insertTag('\n<div class=\'rb-alert alert-error\'><i class=\'ri-close-circle-fill\'></i><div>错误提示信息</div></div>\n', '')"><i class="ri-close-line"></i></button>
                    
                    <input type="file" id="img-upload-input" style="display:none" onchange="uploadImage(this)">
                </div>
                
                <textarea name="content" id="content-editor" placeholder="开始创作..."><?= htmlspecialchars($post['content'] ?? '') ?></textarea>
            </div>

            <aside class="settings-panel">
                <button type="submit" class="btn" style="width:100%; height:50px; font-size:1.1rem; margin-bottom:20px;">
                    <i class="ri-send-plane-fill"></i> <?= $post ? '保存更新' : '立即发布' ?>
                </button>

                <label style="font-weight:bold; font-size:0.9rem;">URL别名</label>
                <input type="text" name="slug" class="form-input" value="<?= htmlspecialchars($post['slug'] ?? '') ?>" placeholder="english-slug" required>

                <label style="font-weight:bold; font-size:0.9rem;">分类目录</label>
                <select name="category_id" class="form-input" style="background:#fff;">
                    <option value="0">未分类</option>
                    <?php foreach($cats as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= ($post && $post['category_id'] == $c['id']) ? 'selected' : '' ?>>
                            <?= $c['parent_id'] == 0 ? '' : '&nbsp;&nbsp;├ ' ?><?= $c['name'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label style="font-weight:bold; font-size:0.9rem;">文章标签 (英文逗号分隔)</label>
                <input type="text" name="tags" class="form-input" value="<?= htmlspecialchars($post['tags'] ?? '') ?>" placeholder="PHP,教程,生活">

                <label style="font-weight:bold; font-size:0.9rem;">封面图 URL</label>
                <input type="text" name="cover_image" class="form-input" value="<?= htmlspecialchars($post['cover_image'] ?? '') ?>" placeholder="留空使用默认图片">

                <label style="font-weight:bold; font-size:0.9rem;">自定义摘要</label>
                <textarea name="summary" class="form-input" rows="4" placeholder="留空则自动提取正文首段"><?= htmlspecialchars($post['summary'] ?? '') ?></textarea>
            </aside>
        </form>
    </main>
</div>

<div class="rb-modal-overlay" id="rb-modal">
    <div class="rb-modal-box">
        <h3 class="rb-modal-title" id="m-title">标题</h3>
        <div id="m-body"></div>
        <div class="rb-modal-actions">
            <button class="rb-modal-btn btn-cancel" onclick="closeModal()">取消</button>
            <button class="rb-modal-btn btn-confirm" id="m-confirm">确定</button>
        </div>
    </div>
</div>

<script>
    // 弹窗逻辑
    function openModal(type) {
        const modal = document.getElementById('rb-modal');
        const body = document.getElementById('m-body');
        const confirmBtn = document.getElementById('m-confirm');
        
        if (type === 'link') {
            document.getElementById('m-title').innerText = "插入超级链接";
            body.innerHTML = `<input type="text" id="m-url" class="rb-modal-input" placeholder="输入网址 http://..." value="http://">
                              <input type="text" id="m-text" class="rb-modal-input" placeholder="链接显示文字 (选填)">`;
            confirmBtn.onclick = () => {
                const url = document.getElementById('m-url').value;
                const txt = document.getElementById('m-text').value;
                if(url) insertTag(`<a href="${url}" target="_blank" style="color:var(--primary); font-weight:bold;">${txt || url}</a>`, '');
                closeModal();
            };
        } else if (type === 'download') {
            document.getElementById('m-title').innerText = "插入高颜值下载框";
            body.innerHTML = `<input type="text" id="m-d-name" class="rb-modal-input" placeholder="资源名称，如：Rebirth源码">
                              <input type="text" id="m-d-url" class="rb-modal-input" placeholder="网盘地址">
                              <input type="text" id="m-d-code" class="rb-modal-input" placeholder="提取码 (留空不显示)">`;
            confirmBtn.onclick = () => {
                const title = document.getElementById('m-d-name').value || '未知资源';
                const link = document.getElementById('m-d-url').value || '#';
                const code = document.getElementById('m-d-code').value;
                let metaHtml = code ? `提取码: <span class="rb-download-code" onclick="copyCode(this)">${code}</span>` : '点击右侧按钮直接下载';
                const html = `\n<div class="rb-download">\n  <div class="rb-download-left">\n    <i class="ri-folder-zip-fill rb-download-icon"></i>\n    <div>\n      <div class="rb-download-title">${title}</div>\n      <div class="rb-download-meta">${metaHtml}</div>\n    </div>\n  </div>\n  <a href="${link}" target="_blank" class="rb-download-btn"><i class="ri-download-cloud-2-line"></i> 获取资源</a>\n</div>\n<p><br></p>\n`;
                insertTag(html, '');
                closeModal();
            };
        }
        modal.classList.add('show');
    }
    function closeModal() { document.getElementById('rb-modal').classList.remove('show'); }

    // 编辑器操作逻辑
    function insertTag(start, end) {
        const t = document.getElementById('content-editor');
        const s = t.selectionStart, e = t.selectionEnd;
        t.value = t.value.substring(0, s) + start + t.value.substring(s, e) + end + t.value.substring(e);
        t.focus();
        t.selectionEnd = s + start.length + (e - s);
    }
    
    // 拦截回车换行
    document.getElementById('content-editor').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); insertTag('\n<br>\n', ''); }
    });

    function insertMacCode() {
        const html = `\n<div class="rb-mac-code">\n  <div class="mac-header">\n    <div class="mac-dots"><div class="mac-dot rb-dot-r"></div><div class="mac-dot rb-dot-y"></div><div class="mac-dot rb-dot-g"></div></div>\n    <button class="mac-copy" onclick="copyRbCode(this)">复制代码</button>\n  </div>\n  <pre class="mac-body"><code>\n// 在这里写入代码...\n  </code></pre>\n</div>\n<p><br></p>\n`;
        insertTag(html, '');
    }

    function triggerImageUpload() { document.getElementById('img-upload-input').click(); }
    function uploadImage(input) {
        if (input.files && input.files[0]) {
            const formData = new FormData();
            formData.append('file', input.files[0]);
            fetch('upload.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                if(data.location) insertTag('<img src="' + data.location + '" alt="image">', '');
                else alert('上传失败: ' + data.error);
            });
        }
    }

    document.getElementById('post-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = this.querySelector('button[type="submit"]');
        const orig = btn.innerHTML;
        btn.innerHTML = '<i class="ri-loader-4-line ri-spin"></i> 处理中...'; btn.disabled = true;

        fetch('save_post.php', { method: 'POST', body: new FormData(this) })
        .then(r => r.json())
        .then(data => {
            if(data.success) { window.location.href = 'posts.php'; } 
            else { alert('错误: ' + data.message); btn.innerHTML = orig; btn.disabled = false; }
        });
    });
</script>
</body>
</html>