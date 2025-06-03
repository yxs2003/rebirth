# Rebirth 

# 项目为AI生成，不保证可以运行！！！

## 项目概述

Rebirth 是一个多风格可自选的自适应网站主题，基于 Flask 框架开发，不依赖任何 CMS，可直接运行。它的特点是 UI 自适应，支持多种风格切换，包括圆角拟态风格、老式报纸杂志风格和传统扁平风格。同时，它还支持黑夜/白天模式切换，为用户提供舒适的浏览体验。

------------


这曾是我想开发的WordPress主题，但是我从未接触过代码，所以这次只能拜托manus来完成了。但是说实话，不尽人意。这种复杂的项目对于AI来时还是过于复杂了。
源码现在就首页能看看，文章页报错，后台报错。期待有缘人能修复它吧！

![8eea531093eb267ef563d5147353490c.png](https://img.picui.cn/free/2025/06/03/683f121468b9b.png)
![00facfb2f60a114f709119f2132dee12.png](https://img.picui.cn/free/2025/06/03/683f12149a985.png)
![3ca30c9331c21c717853bbba33bbe213.png](https://img.picui.cn/free/2025/06/03/683f121437291.png)

## 目录结构

```
rebirth_website_app/
├── docs/                 # 文档
│   ├── requirements_analysis.md  # 需求分析文档
│   ├── database_design.md        # 数据库设计文档
│   └── test_report.md            # 测试报告
├── src/                  # 源代码
│   ├── models/           # 数据库模型
│   │   ├── base.py       # 基础模型
│   │   ├── user.py       # 用户模型
│   │   ├── article.py    # 文章模型
│   │   ├── category.py   # 分类模型
│   │   ├── collection.py # 合辑模型
│   │   ├── slide.py      # 幻灯片模型
│   │   ├── announcement.py # 公告模型
│   │   ├── menu.py       # 菜单模型
│   │   └── setting.py    # 设置模型
│   ├── routes/           # API路由
│   │   ├── user.py       # 用户相关API
│   │   ├── article.py    # 文章相关API
│   │   ├── category.py   # 分类相关API
│   │   ├── collection.py # 合辑相关API
│   │   ├── slide.py      # 幻灯片相关API
│   │   ├── announcement.py # 公告相关API
│   │   ├── menu.py       # 菜单相关API
│   │   ├── setting.py    # 设置相关API
│   │   └── admin.py      # 后台管理API
│   ├── static/           # 静态资源
│   │   ├── css/          # 样式表
│   │   │   ├── base.css  # 基础样式
│   │   │   ├── flat.css  # 扁平风格样式
│   │   │   ├── neumorphism.css # 拟态风格样式
│   │   │   ├── newspaper.css   # 报纸风格样式
│   │   │   └── admin.css       # 后台管理样式
│   │   ├── js/           # JavaScript脚本
│   │   │   ├── main.js   # 主要脚本
│   │   │   ├── admin.js  # 后台管理脚本
│   │   │   └── theme-switcher.js # 主题切换脚本
│   │   ├── img/          # 图片资源
│   │   ├── admin/        # 后台管理页面
│   │   │   ├── index.html      # 后台首页
│   │   │   ├── articles.html   # 文章管理
│   │   │   └── settings.html   # 网站设置
│   │   ├── index.html    # 前台首页
│   │   ├── login.html    # 登录页面
│   │   ├── install.html  # 安装指南
│   │   ├── documentation.html # 使用文档
│   │   └── about.html    # 关于页面
│   └── main.py           # 应用入口
├── venv/                 # 虚拟环境
├── requirements.txt      # 依赖列表
└── README.md             # 项目说明
```

## 安装与部署

### 系统要求

- Python 3.8+
- MySQL 5.7+
- 现代浏览器（Chrome、Firefox、Safari、Edge等）

### 安装步骤

1. **克隆代码库**

```bash
git clone https://github.com/your-username/rebirth-theme.git
cd rebirth-theme
```

2. **创建虚拟环境**

```bash
python -m venv venv
source venv/bin/activate  # Linux/Mac
# 或
venv\Scripts\activate  # Windows
```

3. **安装依赖**

```bash
pip install -r requirements.txt
```

4. **配置数据库**

创建MySQL数据库，并在`src/main.py`中配置数据库连接：

```python
app.config['SQLALCHEMY_DATABASE_URI'] = f"mysql+pymysql://{os.getenv('DB_USERNAME', 'root')}:{os.getenv('DB_PASSWORD', 'password')}@{os.getenv('DB_HOST', 'localhost')}:{os.getenv('DB_PORT', '3306')}/{os.getenv('DB_NAME', 'rebirth_db')}"
```

5. **初始化数据库**

```bash
python src/init_db.py
```

6. **运行应用**

```bash
python src/main.py
```

现在，您可以通过访问 http://localhost:5000 来查看网站。

7. **访问管理后台**

访问 http://localhost:5000/login.html 并使用以下默认管理员账号登录：

- 用户名：admin
- 密码：admin123

**注意：** 首次登录后请立即修改默认密码！

## 主要功能

### 多风格主题

Rebirth 主题提供三种不同的风格：

1. **扁平风格**：类似DUX主题的简洁扁平设计，现代感强，适合各类网站。
2. **拟态风格**：新拟态设计，柔和圆角，立体感强，给用户带来舒适的视觉体验。
3. **报纸风格**：复古排版，类似传统印刷品，适合文字内容为主的博客或新闻网站。

用户可以在前台右上角的下拉菜单中切换不同的风格，也可以在后台的"网站设置"页面中设置默认的主题风格。

### 黑夜/白天模式

Rebirth 主题支持黑夜/白天模式切换，可以通过以下方式控制：

1. **手动切换**：点击网站右上角的月亮/太阳图标。
2. **自动模式**：在后台设置中选择"自动"，将跟随系统设置自动切换。
3. **固定模式**：在后台设置中选择"浅色模式"或"深色模式"，固定显示模式。

### 内容管理

Rebirth 主题提供了多种内容管理功能：

- **文章管理**：创建、编辑、删除文章，设置分类和标签，上传封面图等。
- **分类管理**：创建、编辑、删除分类，设置分类层级关系。
- **合辑管理**：创建、编辑、删除合辑，添加文章到合辑。
- **幻灯片管理**：上传幻灯片图片，设置标题、描述和链接。
- **公告管理**：创建、编辑、删除公告，设置显示时间和优先级。
- **菜单管理**：创建、编辑、删除菜单，添加菜单项，调整顺序。

### 网站设置

在"网站设置"页面，您可以配置以下内容：

- **基本设置**：网站名称、描述、关键词、备案号等。
- **主题设置**：默认主题风格、显示模式（自动/浅色/深色）。
- **Logo设置**：上传浅色模式和深色模式的Logo。
- **社交媒体**：设置社交媒体链接。

## 自定义开发

### 添加新的主题风格

如果您想添加新的主题风格，可以按照以下步骤操作：

1. 在`src/static/css/`目录下创建新的CSS文件
2. 参考现有主题风格的CSS结构，使用CSS变量定义样式
3. 在`src/static/js/theme-switcher.js`中添加新的主题风格
4. 在前台和后台的主题选择器中添加新的选项

### 添加新的内容模块

如果您想添加新的内容模块，需要：

1. 在`src/models/`目录下创建新的数据模型
2. 在`src/routes/`目录下创建新的API路由
3. 在前台和后台添加相应的界面

## 常见问题

### Q: 如何更改网站Logo？

A: 登录后台管理系统，进入"网站设置"页面，在"Logo设置"部分上传新的Logo图片。

### Q: 如何添加新的菜单项？

A: 登录后台管理系统，进入"菜单管理"页面，选择要编辑的菜单，点击"添加菜单项"按钮。

### Q: 如何设置网站默认主题风格？

A: 登录后台管理系统，进入"网站设置"页面，在"主题设置"部分选择默认的主题风格。

### Q: 如何创建文章合辑？

A: 登录后台管理系统，进入"合辑管理"页面，点击"新建合辑"按钮，填写合辑信息并选择要包含的文章。

### Q: 如何修改网站底部版权信息？

A: 目前需要直接修改模板文件。找到`src/static/index.html`文件，修改底部的版权信息。
