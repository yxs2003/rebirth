/**
 * Rebirth 主题切换系统
 * 实现主题风格和显示模式的切换功能，并与后台设置集成
 */

class ThemeSwitcher {
  constructor() {
    this.themeStyles = ['flat', 'neumorphism', 'newspaper'];
    this.displayModes = ['light', 'dark', 'auto'];
    this.defaultSettings = {
      themeStyle: 'flat',
      displayMode: 'auto'
    };
    
    this.init();
  }
  
  /**
   * 初始化主题切换系统
   */
  init() {
    // 获取当前主题设置
    this.loadSettings()
      .then(settings => {
        this.applyTheme(settings);
        this.bindEvents();
      })
      .catch(error => {
        console.error('Failed to load theme settings:', error);
        // 使用默认设置
        this.applyTheme(this.defaultSettings);
        this.bindEvents();
      });
  }
  
  /**
   * 从后台加载网站设置
   * @returns {Promise} 包含主题设置的Promise
   */
  async loadSettings() {
    try {
      // 尝试从API获取设置
      const response = await fetch('/api/settings/theme');
      
      if (!response.ok) {
        throw new Error(`HTTP error! Status: ${response.status}`);
      }
      
      const data = await response.json();
      
      // 返回主题相关设置
      return {
        themeStyle: data.theme_style || this.defaultSettings.themeStyle,
        displayMode: data.display_mode || this.defaultSettings.displayMode
      };
    } catch (error) {
      console.error('Error loading settings from API:', error);
      
      // 如果API请求失败，尝试从localStorage获取
      return this.getLocalSettings();
    }
  }
  
  /**
   * 从localStorage获取本地保存的主题设置
   * @returns {Object} 主题设置对象
   */
  getLocalSettings() {
    const themeStyle = localStorage.getItem('themeStyle') || this.defaultSettings.themeStyle;
    let displayMode = localStorage.getItem('theme') || this.defaultSettings.displayMode;
    
    // 验证设置有效性
    if (!this.themeStyles.includes(themeStyle)) {
      localStorage.setItem('themeStyle', this.defaultSettings.themeStyle);
      return this.defaultSettings;
    }
    
    if (!this.displayModes.includes(displayMode)) {
      displayMode = this.defaultSettings.displayMode;
      localStorage.setItem('theme', displayMode);
    }
    
    return {
      themeStyle,
      displayMode
    };
  }
  
  /**
   * 应用主题设置
   * @param {Object} settings - 主题设置对象
   */
  applyTheme(settings) {
    // 应用主题风格
    document.documentElement.setAttribute('data-theme-style', settings.themeStyle);
    
    // 应用显示模式
    if (settings.displayMode === 'auto') {
      // 自动模式，跟随系统
      if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
        document.documentElement.setAttribute('data-theme', 'dark');
      } else {
        document.documentElement.setAttribute('data-theme', 'light');
      }
    } else {
      // 手动模式
      document.documentElement.setAttribute('data-theme', settings.displayMode);
    }
    
    // 更新本地存储
    localStorage.setItem('themeStyle', settings.themeStyle);
    localStorage.setItem('theme', settings.displayMode);
    
    // 更新主题切换按钮状态
    this.updateToggleButton();
  }
  
  /**
   * 更新主题切换按钮状态
   */
  updateToggleButton() {
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
      const currentTheme = document.documentElement.getAttribute('data-theme');
      themeToggle.innerHTML = currentTheme === 'dark' ? '☀️' : '🌙';
      themeToggle.setAttribute('aria-label', currentTheme === 'dark' ? '切换到浅色模式' : '切换到深色模式');
    }
  }
  
  /**
   * 切换显示模式（深色/浅色）
   */
  toggleDisplayMode() {
    const currentTheme = document.documentElement.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    
    document.documentElement.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);
    
    // 更新主题切换按钮状态
    this.updateToggleButton();
  }
  
  /**
   * 切换主题风格
   * @param {string} style - 主题风格名称
   */
  changeThemeStyle(style) {
    if (this.themeStyles.includes(style)) {
      document.documentElement.setAttribute('data-theme-style', style);
      localStorage.setItem('themeStyle', style);
    }
  }
  
  /**
   * 绑定事件监听
   */
  bindEvents() {
    // 主题切换按钮
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
      themeToggle.addEventListener('click', () => this.toggleDisplayMode());
    }
    
    // 主题风格选择器
    const styleSelector = document.getElementById('style-selector');
    if (styleSelector) {
      styleSelector.addEventListener('change', (e) => this.changeThemeStyle(e.target.value));
      
      // 设置当前选中的风格
      const currentStyle = localStorage.getItem('themeStyle') || this.defaultSettings.themeStyle;
      styleSelector.value = currentStyle;
    }
    
    // 监听系统主题变化
    if (window.matchMedia) {
      window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
        // 只有在自动模式下才跟随系统
        if (localStorage.getItem('theme') === 'auto') {
          document.documentElement.setAttribute('data-theme', e.matches ? 'dark' : 'light');
          this.updateToggleButton();
        }
      });
    }
  }
}

// 页面加载完成后初始化主题切换系统
document.addEventListener('DOMContentLoaded', function() {
  window.themeSwitcher = new ThemeSwitcher();
});
