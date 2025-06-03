# Rebirth 网站数据库设计

## 数据库表结构设计

### 1. 用户表 (users)

| 字段名 | 类型 | 说明 |
|--------|------|------|
| id | INT | 主键，自增 |
| username | VARCHAR(50) | 用户名，唯一 |
| password | VARCHAR(255) | 密码（加密存储） |
| email | VARCHAR(100) | 电子邮箱 |
| role | VARCHAR(20) | 角色（admin/editor） |
| created_at | DATETIME | 创建时间 |
| updated_at | DATETIME | 更新时间 |

### 2. 网站设置表 (settings)

| 字段名 | 类型 | 说明 |
|--------|------|------|
| id | INT | 主键，自增 |
| site_name | VARCHAR(100) | 网站名称 |
| site_description | TEXT | 网站描述 |
| site_keywords | TEXT | 网站关键词 |
| logo_light | VARCHAR(255) | 白天模式Logo路径 |
| logo_dark | VARCHAR(255) | 黑夜模式Logo路径 |
| theme_style | VARCHAR(50) | 主题风格（neumorphism/newspaper/flat） |
| display_mode | VARCHAR(20) | 显示模式（light/dark/auto） |
| footer_info | TEXT | 页脚信息 |
| updated_at | DATETIME | 更新时间 |

### 3. 分类表 (categories)

| 字段名 | 类型 | 说明 |
|--------|------|------|
| id | INT | 主键，自增 |
| name | VARCHAR(50) | 分类名称 |
| slug | VARCHAR(50) | URL别名 |
| parent_id | INT | 父分类ID（用于子分类） |
| description | TEXT | 分类描述 |
| created_at | DATETIME | 创建时间 |
| updated_at | DATETIME | 更新时间 |

### 4. 文章表 (articles)

| 字段名 | 类型 | 说明 |
|--------|------|------|
| id | INT | 主键，自增 |
| title | VARCHAR(255) | 文章标题 |
| slug | VARCHAR(255) | URL别名 |
| content | TEXT | 文章内容 |
| excerpt | TEXT | 文章摘要 |
| cover_image | VARCHAR(255) | 封面图片路径 |
| author_id | INT | 作者ID（关联users表） |
| status | VARCHAR(20) | 状态（published/draft） |
| featured | BOOLEAN | 是否推荐 |
| views | INT | 浏览次数 |
| created_at | DATETIME | 创建时间 |
| updated_at | DATETIME | 更新时间 |
| published_at | DATETIME | 发布时间 |

### 5. 文章分类关联表 (article_category)

| 字段名 | 类型 | 说明 |
|--------|------|------|
| article_id | INT | 文章ID |
| category_id | INT | 分类ID |

### 6. 文章合辑表 (collections)

| 字段名 | 类型 | 说明 |
|--------|------|------|
| id | INT | 主键，自增 |
| title | VARCHAR(255) | 合辑标题 |
| slug | VARCHAR(255) | URL别名 |
| description | TEXT | 合辑描述 |
| cover_image | VARCHAR(255) | 封面图片路径 |
| created_at | DATETIME | 创建时间 |
| updated_at | DATETIME | 更新时间 |

### 7. 文章合辑关联表 (article_collection)

| 字段名 | 类型 | 说明 |
|--------|------|------|
| article_id | INT | 文章ID |
| collection_id | INT | 合辑ID |
| sort_order | INT | 排序顺序 |

### 8. 幻灯片表 (slides)

| 字段名 | 类型 | 说明 |
|--------|------|------|
| id | INT | 主键，自增 |
| title | VARCHAR(255) | 幻灯片标题 |
| image | VARCHAR(255) | 图片路径 |
| link | VARCHAR(255) | 链接URL |
| description | TEXT | 描述文字 |
| sort_order | INT | 排序顺序 |
| status | BOOLEAN | 是否启用 |
| created_at | DATETIME | 创建时间 |
| updated_at | DATETIME | 更新时间 |

### 9. 公告表 (announcements)

| 字段名 | 类型 | 说明 |
|--------|------|------|
| id | INT | 主键，自增 |
| title | VARCHAR(255) | 公告标题 |
| content | TEXT | 公告内容 |
| status | BOOLEAN | 是否启用 |
| sort_order | INT | 排序顺序 |
| created_at | DATETIME | 创建时间 |
| updated_at | DATETIME | 更新时间 |

### 10. 菜单表 (menus)

| 字段名 | 类型 | 说明 |
|--------|------|------|
| id | INT | 主键，自增 |
| name | VARCHAR(50) | 菜单名称 |
| location | VARCHAR(50) | 菜单位置（如header/footer） |
| created_at | DATETIME | 创建时间 |
| updated_at | DATETIME | 更新时间 |

### 11. 菜单项表 (menu_items)

| 字段名 | 类型 | 说明 |
|--------|------|------|
| id | INT | 主键，自增 |
| menu_id | INT | 所属菜单ID |
| parent_id | INT | 父菜单项ID（用于子菜单） |
| title | VARCHAR(100) | 菜单项标题 |
| url | VARCHAR(255) | 链接URL |
| target | VARCHAR(20) | 打开方式（_self/_blank） |
| sort_order | INT | 排序顺序 |
| created_at | DATETIME | 创建时间 |
| updated_at | DATETIME | 更新时间 |

## 数据库关系图

```
users ──────┐
            │
            ▼
articles ───┼─── article_category ───── categories
            │
            ├─── article_collection ─── collections
            │
slides      │
            │
announcements
            │
menus ──────┴─── menu_items
```

## 索引设计

- users: username(唯一), email(唯一)
- articles: slug(唯一), author_id, status, featured
- categories: slug(唯一), parent_id
- collections: slug(唯一)
- article_category: (article_id, category_id)联合索引
- article_collection: (article_id, collection_id)联合索引
- menu_items: menu_id, parent_id, sort_order
