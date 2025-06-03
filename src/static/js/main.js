// 主题切换和夜间模式功能
document.addEventListener('DOMContentLoaded', function() {
  // 获取当前主题设置
  const getCurrentTheme = () => {
    // 优先从localStorage获取用户设置
    const savedTheme = localStorage.getItem('theme');
    const savedStyle = localStorage.getItem('themeStyle');
    
    if (savedTheme) {
      return {
        theme: savedTheme,
        style: savedStyle || 'flat'
      };
    }
    
    // 如果没有保存的设置，检查系统偏好
    if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
      return {
        theme: 'dark',
        style: 'flat'
      };
    }
    
    // 默认为浅色模式和扁平风格
    return {
      theme: 'light',
      style: 'flat'
    };
  };
  
  // 应用主题设置
  const applyTheme = (settings) => {
    document.documentElement.setAttribute('data-theme', settings.theme);
    document.documentElement.setAttribute('data-theme-style', settings.style);
    
    // 保存设置到localStorage
    localStorage.setItem('theme', settings.theme);
    localStorage.setItem('themeStyle', settings.style);
    
    // 更新主题切换按钮状态
    updateThemeToggleButton(settings.theme);
    updateStyleSelector(settings.style);
  };
  
  // 更新主题切换按钮状态
  const updateThemeToggleButton = (theme) => {
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
      themeToggle.innerHTML = theme === 'dark' ? '☀️' : '🌙';
      themeToggle.setAttribute('aria-label', theme === 'dark' ? '切换到浅色模式' : '切换到深色模式');
    }
  };
  
  // 更新风格选择器状态
  const updateStyleSelector = (style) => {
    const styleSelector = document.getElementById('style-selector');
    if (styleSelector) {
      styleSelector.value = style;
    }
  };
  
  // 切换深色/浅色模式
  const toggleTheme = () => {
    const currentSettings = getCurrentTheme();
    const newTheme = currentSettings.theme === 'dark' ? 'light' : 'dark';
    
    applyTheme({
      theme: newTheme,
      style: currentSettings.style
    });
  };
  
  // 切换主题风格
  const changeStyle = (style) => {
    const currentSettings = getCurrentTheme();
    
    applyTheme({
      theme: currentSettings.theme,
      style: style
    });
  };
  
  // 初始化主题
  const initTheme = () => {
    const currentSettings = getCurrentTheme();
    applyTheme(currentSettings);
    
    // 绑定主题切换按钮事件
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
      themeToggle.addEventListener('click', toggleTheme);
    }
    
    // 绑定风格选择器事件
    const styleSelector = document.getElementById('style-selector');
    if (styleSelector) {
      styleSelector.addEventListener('change', (e) => {
        changeStyle(e.target.value);
      });
    }
  };
  
  // 初始化
  initTheme();
  
  // 监听系统主题变化
  if (window.matchMedia) {
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
      // 只有在用户没有明确设置主题时才跟随系统
      if (!localStorage.getItem('theme')) {
        applyTheme({
          theme: e.matches ? 'dark' : 'light',
          style: getCurrentTheme().style
        });
      }
    });
  }
});

// 移动端菜单切换
document.addEventListener('DOMContentLoaded', function() {
  const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
  const mainNav = document.querySelector('.main-nav');
  
  if (mobileMenuToggle && mainNav) {
    mobileMenuToggle.addEventListener('click', function() {
      mainNav.classList.toggle('active');
      mobileMenuToggle.setAttribute(
        'aria-expanded', 
        mainNav.classList.contains('active') ? 'true' : 'false'
      );
    });
  }
});

// API请求工具函数
const API = {
  baseUrl: '/api',
  
  // 获取请求头
  getHeaders() {
    const headers = {
      'Content-Type': 'application/json'
    };
    
    const token = localStorage.getItem('token');
    if (token) {
      headers['Authorization'] = `Bearer ${token}`;
    }
    
    return headers;
  },
  
  // GET请求
  async get(endpoint, params = {}) {
    const url = new URL(`${this.baseUrl}${endpoint}`, window.location.origin);
    
    // 添加查询参数
    Object.keys(params).forEach(key => {
      if (params[key] !== undefined && params[key] !== null) {
        url.searchParams.append(key, params[key]);
      }
    });
    
    try {
      const response = await fetch(url, {
        method: 'GET',
        headers: this.getHeaders()
      });
      
      if (!response.ok) {
        throw new Error(`HTTP error! Status: ${response.status}`);
      }
      
      return await response.json();
    } catch (error) {
      console.error('API GET Error:', error);
      throw error;
    }
  },
  
  // POST请求
  async post(endpoint, data = {}) {
    try {
      const response = await fetch(`${this.baseUrl}${endpoint}`, {
        method: 'POST',
        headers: this.getHeaders(),
        body: JSON.stringify(data)
      });
      
      if (!response.ok) {
        throw new Error(`HTTP error! Status: ${response.status}`);
      }
      
      return await response.json();
    } catch (error) {
      console.error('API POST Error:', error);
      throw error;
    }
  },
  
  // PUT请求
  async put(endpoint, data = {}) {
    try {
      const response = await fetch(`${this.baseUrl}${endpoint}`, {
        method: 'PUT',
        headers: this.getHeaders(),
        body: JSON.stringify(data)
      });
      
      if (!response.ok) {
        throw new Error(`HTTP error! Status: ${response.status}`);
      }
      
      return await response.json();
    } catch (error) {
      console.error('API PUT Error:', error);
      throw error;
    }
  },
  
  // DELETE请求
  async delete(endpoint) {
    try {
      const response = await fetch(`${this.baseUrl}${endpoint}`, {
        method: 'DELETE',
        headers: this.getHeaders()
      });
      
      if (!response.ok) {
        throw new Error(`HTTP error! Status: ${response.status}`);
      }
      
      return await response.json();
    } catch (error) {
      console.error('API DELETE Error:', error);
      throw error;
    }
  },
  
  // 用户登录
  async login(username, password) {
    try {
      const response = await this.post('/users/login', { username, password });
      
      if (response.token) {
        localStorage.setItem('token', response.token);
        localStorage.setItem('user', JSON.stringify(response.user));
      }
      
      return response;
    } catch (error) {
      console.error('Login Error:', error);
      throw error;
    }
  },
  
  // 用户注销
  logout() {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    window.location.href = '/';
  },
  
  // 检查用户是否已登录
  isLoggedIn() {
    return !!localStorage.getItem('token');
  },
  
  // 获取当前用户信息
  getCurrentUser() {
    const userJson = localStorage.getItem('user');
    return userJson ? JSON.parse(userJson) : null;
  },
  
  // 检查用户是否为管理员
  isAdmin() {
    const user = this.getCurrentUser();
    return user && user.role === 'admin';
  }
};

// 幻灯片功能
class Slider {
  constructor(selector) {
    this.slider = document.querySelector(selector);
    if (!this.slider) return;
    
    this.sliderInner = this.slider.querySelector('.slider-inner');
    this.slides = this.slider.querySelectorAll('.slider-item');
    this.navBtns = [];
    this.currentIndex = 0;
    this.slideCount = this.slides.length;
    this.interval = null;
    
    this.init();
  }
  
  init() {
    if (this.slideCount <= 1) return;
    
    // 创建导航按钮
    this.createNavButtons();
    
    // 设置自动播放
    this.startAutoPlay();
    
    // 鼠标悬停时暂停自动播放
    this.slider.addEventListener('mouseenter', () => this.stopAutoPlay());
    this.slider.addEventListener('mouseleave', () => this.startAutoPlay());
  }
  
  createNavButtons() {
    const navContainer = document.createElement('div');
    navContainer.className = 'slider-nav';
    
    for (let i = 0; i < this.slideCount; i++) {
      const btn = document.createElement('span');
      btn.className = 'slider-nav-btn';
      if (i === 0) btn.classList.add('active');
      
      btn.addEventListener('click', () => this.goToSlide(i));
      
      navContainer.appendChild(btn);
      this.navBtns.push(btn);
    }
    
    this.slider.appendChild(navContainer);
  }
  
  goToSlide(index) {
    if (index < 0) index = this.slideCount - 1;
    if (index >= this.slideCount) index = 0;
    
    this.currentIndex = index;
    this.sliderInner.style.transform = `translateX(-${index * 100}%)`;
    
    // 更新导航按钮状态
    this.navBtns.forEach((btn, i) => {
      btn.classList.toggle('active', i === index);
    });
  }
  
  nextSlide() {
    this.goToSlide(this.currentIndex + 1);
  }
  
  prevSlide() {
    this.goToSlide(this.currentIndex - 1);
  }
  
  startAutoPlay() {
    this.stopAutoPlay();
    this.interval = setInterval(() => this.nextSlide(), 5000);
  }
  
  stopAutoPlay() {
    if (this.interval) {
      clearInterval(this.interval);
      this.interval = null;
    }
  }
}

// 页面加载完成后初始化幻灯片
document.addEventListener('DOMContentLoaded', function() {
  new Slider('.slider');
});

// 搜索功能
document.addEventListener('DOMContentLoaded', function() {
  const searchForm = document.getElementById('search-form');
  const searchInput = document.getElementById('search-input');
  
  if (searchForm && searchInput) {
    searchForm.addEventListener('submit', function(e) {
      e.preventDefault();
      
      const keyword = searchInput.value.trim();
      if (keyword) {
        window.location.href = `/search?keyword=${encodeURIComponent(keyword)}`;
      }
    });
  }
});

// 加载网站设置
async function loadSiteSettings() {
  try {
    const response = await API.get('/settings/');
    const settings = response.setting;
    
    // 设置网站标题
    document.title = settings.site_name;
    
    // 设置网站logo
    const logoElement = document.querySelector('.logo img');
    if (logoElement) {
      const isDarkMode = document.documentElement.getAttribute('data-theme') === 'dark';
      logoElement.src = isDarkMode && settings.logo_dark ? settings.logo_dark : settings.logo_light;
      logoElement.alt = settings.site_name;
    }
    
    // 设置主题风格
    if (settings.theme_style && !localStorage.getItem('themeStyle')) {
      document.documentElement.setAttribute('data-theme-style', settings.theme_style);
    }
    
    // 设置显示模式
    if (settings.display_mode && !localStorage.getItem('theme')) {
      if (settings.display_mode === 'light' || settings.display_mode === 'dark') {
        document.documentElement.setAttribute('data-theme', settings.display_mode);
      }
    }
    
    return settings;
  } catch (error) {
    console.error('Failed to load site settings:', error);
    return null;
  }
}

// 页面加载完成后初始化网站设置
document.addEventListener('DOMContentLoaded', function() {
  loadSiteSettings();
});
