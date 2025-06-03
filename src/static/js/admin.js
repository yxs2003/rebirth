// 后台管理系统JavaScript
document.addEventListener('DOMContentLoaded', function() {
  // 侧边栏切换
  const sidebarToggle = document.getElementById('sidebar-toggle');
  const adminSidebar = document.querySelector('.admin-sidebar');
  
  if (sidebarToggle && adminSidebar) {
    sidebarToggle.addEventListener('click', function() {
      adminSidebar.classList.toggle('active');
    });
  }
  
  // 用户下拉菜单
  const userBtn = document.querySelector('.admin-user-btn');
  const dropdownMenu = document.querySelector('.admin-dropdown-menu');
  
  if (userBtn && dropdownMenu) {
    userBtn.addEventListener('click', function(e) {
      e.stopPropagation();
      dropdownMenu.classList.toggle('active');
    });
    
    // 点击其他区域关闭下拉菜单
    document.addEventListener('click', function() {
      if (dropdownMenu.classList.contains('active')) {
        dropdownMenu.classList.remove('active');
      }
    });
  }
  
  // 退出登录
  const logoutBtn = document.getElementById('logout-btn');
  if (logoutBtn) {
    logoutBtn.addEventListener('click', function(e) {
      e.preventDefault();
      
      if (confirm('确定要退出登录吗？')) {
        API.logout();
      }
    });
  }
  
  // 主题切换
  const themeToggle = document.getElementById('admin-theme-toggle');
  if (themeToggle) {
    themeToggle.addEventListener('click', function() {
      const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
      const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
      
      document.documentElement.setAttribute('data-theme', newTheme);
      localStorage.setItem('theme', newTheme);
      
      themeToggle.innerHTML = newTheme === 'dark' ? '☀️' : '🌙';
    });
  }
  
  // 检查登录状态
  function checkAuth() {
    if (!API.isLoggedIn()) {
      window.location.href = '/login.html';
      return false;
    }
    
    const user = API.getCurrentUser();
    if (!user || user.role !== 'admin') {
      alert('您没有管理员权限');
      window.location.href = '/';
      return false;
    }
    
    return true;
  }
  
  // 初始化后台管理页面
  async function initAdminPage() {
    if (!checkAuth()) return;
    
    // 更新用户信息
    const user = API.getCurrentUser();
    const usernameElement = document.querySelector('.admin-username');
    const avatarElement = document.querySelector('.admin-avatar');
    
    if (usernameElement && user) {
      usernameElement.textContent = user.username;
    }
    
    if (avatarElement && user) {
      avatarElement.textContent = user.username.charAt(0).toUpperCase();
    }
    
    // 加载仪表盘数据
    try {
      const dashboardData = await API.get('/admin/dashboard');
      updateDashboard(dashboardData);
    } catch (error) {
      console.error('Failed to load dashboard data:', error);
    }
  }
  
  // 更新仪表盘数据
  function updateDashboard(data) {
    // 更新统计数据
    const statElements = document.querySelectorAll('.stat-number');
    if (data.stats && statElements.length >= 4) {
      statElements[0].textContent = data.stats.articleCount || 0;
      statElements[1].textContent = data.stats.userCount || 0;
      statElements[2].textContent = data.stats.totalViews || 0;
      statElements[3].textContent = data.stats.monthlyGrowth || '0%';
    }
    
    // 更新最近文章
    if (data.recentArticles && data.recentArticles.length > 0) {
      const tableBody = document.querySelector('.admin-table tbody');
      if (tableBody) {
        tableBody.innerHTML = '';
        
        data.recentArticles.forEach(article => {
          const row = document.createElement('tr');
          row.innerHTML = `
            <td><a href="/admin/articles/edit/${article.id}">${article.title}</a></td>
            <td>${article.author}</td>
            <td>${article.category}</td>
            <td>${new Date(article.published_at).toLocaleDateString()}</td>
            <td><span class="status-${article.status}">${article.status === 'published' ? '已发布' : '草稿'}</span></td>
          `;
          tableBody.appendChild(row);
        });
      }
    }
    
    // 更新分类统计
    if (data.categoryStats && data.categoryStats.length > 0) {
      const categoryList = document.querySelector('.admin-category-stats');
      if (categoryList) {
        categoryList.innerHTML = '';
        
        data.categoryStats.forEach(category => {
          const item = document.createElement('li');
          item.innerHTML = `
            <span class="category-name">${category.name}</span>
            <span class="category-count">${category.count}</span>
          `;
          categoryList.appendChild(item);
        });
      }
    }
  }
  
  // 初始化页面
  initAdminPage();
});
