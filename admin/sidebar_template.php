<?php
/**
 * Rebirth 后台侧边栏与全局组件 V4.0
 */
$current_page = basename($_SERVER['PHP_SELF']);
$nickname = $_SESSION['nickname'] ?? 'Admin';
?>
<aside class="sidebar">
    <div class="logo"><i class="ri-restart-line" style="color:var(--primary)"></i> Re<span>birth</span></div>
    <nav>
        <a href="index.php" class="nav-link <?= $current_page == 'index.php' ? 'active' : '' ?>"><i class="ri-dashboard-line"></i> 控制台</a>
        <a href="writer.php" class="nav-link <?= $current_page == 'writer.php' ? 'active' : '' ?>"><i class="ri-edit-circle-line"></i> 写文章</a>
        <a href="posts.php" class="nav-link <?= $current_page == 'posts.php' ? 'active' : '' ?>"><i class="ri-article-line"></i> 文章管理</a>
        <a href="categories.php" class="nav-link <?= $current_page == 'categories.php' ? 'active' : '' ?>"><i class="ri-folder-2-line"></i> 分类目录</a>
        <a href="comments.php" class="nav-link <?= $current_page == 'comments.php' ? 'active' : '' ?>"><i class="ri-message-2-line"></i> 评论管理</a>
        <a href="media.php" class="nav-link <?= $current_page == 'media.php' ? 'active' : '' ?>"><i class="ri-image-line"></i> 媒体库</a>
        <a href="settings.php" class="nav-link <?= $current_page == 'settings.php' ? 'active' : '' ?>"><i class="ri-settings-4-line"></i> 系统设置</a>
        <a href="logout.php" class="nav-link" style="margin-top: 20px; color: #ff7675;"><i class="ri-logout-box-line"></i> 退出登录</a>
    </nav>
</aside>

<style>
.rb-modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(8px); z-index: 9999; display: none; align-items: center; justify-content: center; opacity: 0; transition: 0.3s; }
.rb-modal-overlay.show { display: flex; opacity: 1; }
.rb-modal-box { background: #fff; width: 420px; max-width: 90%; padding: 30px; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); transform: scale(0.95); transition: 0.3s; }
.rb-modal-overlay.show .rb-modal-box { transform: scale(1); }
.rb-modal-title { margin: 0 0 20px 0; color: #2d3436; font-size: 1.3rem; display: flex; align-items: center; gap: 8px; }
.rb-modal-body { margin-bottom: 25px; color: #636e72; font-size: 1rem; line-height: 1.5; }
.rb-modal-input { width: 100%; padding: 12px; border: 1px solid #dfe6e9; border-radius: 8px; margin-bottom: 15px; outline: none; transition: 0.3s; box-sizing: border-box; }
.rb-modal-input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.1); }
.rb-modal-actions { display: flex; justify-content: flex-end; gap: 12px; }
.rb-modal-btn { padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; transition: 0.2s; }
.btn-cancel { background: #f1f2f6; color: #636e72; }
.btn-cancel:hover { background: #dfe6e9; }
.btn-confirm { background: var(--primary); color: #fff; }
.btn-confirm:hover { background: #5649c0; transform: translateY(-2px); }
</style>

<div class="rb-modal-overlay" id="rb-sys-modal">
    <div class="rb-modal-box">
        <h3 class="rb-modal-title" id="rb-sys-title">提示</h3>
        <div class="rb-modal-body" id="rb-sys-body"></div>
        <div class="rb-modal-actions" id="rb-sys-actions"></div>
    </div>
</div>

<script>
/**
 * 核心：全局 UI 弹窗管理器
 */
const rbModal = {
    show: function(title, bodyHtml, actionsHtml) {
        document.getElementById('rb-sys-title').innerHTML = title;
        document.getElementById('rb-sys-body').innerHTML = bodyHtml;
        document.getElementById('rb-sys-actions').innerHTML = actionsHtml;
        document.getElementById('rb-sys-modal').classList.add('show');
    },
    hide: function() {
        document.getElementById('rb-sys-modal').classList.remove('show');
    },
    alert: function(msg) {
        this.show('<i class="ri-information-fill" style="color:var(--primary)"></i> 系统提示', msg, 
        `<button class="rb-modal-btn btn-confirm" onclick="rbModal.hide()">确定</button>`);
    },
    confirm: function(msg, onConfirm) {
        window._rbConfirmCb = () => { rbModal.hide(); if(onConfirm) onConfirm(); };
        this.show('<i class="ri-question-fill" style="color:#f39c12"></i> 确认操作', msg, 
        `<button class="rb-modal-btn btn-cancel" onclick="rbModal.hide()">取消</button>
         <button class="rb-modal-btn btn-confirm" onclick="window._rbConfirmCb()">确定</button>`);
    },
    prompt: function(title, fields, onConfirm) {
        let html = '';
        fields.forEach((f, i) => {
            html += `<input type="${f.type||'text'}" id="rb-prompt-val-${i}" class="rb-modal-input" placeholder="${f.placeholder||''}" value="${f.value||''}">`;
        });
        window._rbPromptCb = () => {
            let results = fields.map((f, i) => document.getElementById(`rb-prompt-val-${i}`).value);
            rbModal.hide();
            if(onConfirm) onConfirm(results);
        };
        this.show(title, html, 
        `<button class="rb-modal-btn btn-cancel" onclick="rbModal.hide()">取消</button>
         <button class="rb-modal-btn btn-confirm" onclick="window._rbPromptCb()">确定</button>`);
    }
};

// 拦截原生的 alert 和 confirm
window.alert = function(msg) { rbModal.alert(msg); };
</script>

<?php function renderTopBar($pageTitle) { global $nickname; ?>
<div class="top-bar">
    <div class="page-title"><?= $pageTitle ?></div>
    <div class="user-info">
        你好, <strong><?= htmlspecialchars($nickname) ?></strong>
        <a href="../index.html" target="_blank" class="btn" style="padding: 8px 15px; font-size: 0.8rem; margin-left:10px;"><i class="ri-external-link-line"></i> 访问首页</a>
    </div>
</div>
<?php } ?>