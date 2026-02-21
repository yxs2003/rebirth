SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+08:00";

-- Rebirth V4.5 Database Structure
-- 版权所有 (c) Rebirth Blog System

CREATE TABLE `rb_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nickname` varchar(50) DEFAULT 'Admin',
  `avatar` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `rb_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT 0 COMMENT '父级分类ID',
  `name` varchar(50) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `description` varchar(200) DEFAULT NULL,
  `show_in_nav` tinyint(1) DEFAULT 1 COMMENT '是否在首页导航显示',
  `sort_order` int(11) DEFAULT 0 COMMENT '排序权重',
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `rb_posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `summary` varchar(500) DEFAULT NULL,
  `content` longtext NOT NULL,
  `tags` varchar(255) DEFAULT NULL COMMENT '文章标签',
  `views` int(11) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  `show_meta` tinyint(1) DEFAULT 1,
  `custom_css` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `rb_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT 0,
  `nickname` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `content` text NOT NULL,
  `reply_content` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `is_approved` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `post_id` (`post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `rb_options` (
  `option_name` varchar(100) NOT NULL,
  `option_value` longtext,
  PRIMARY KEY (`option_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `rb_categories` (`name`, `slug`, `description`, `show_in_nav`, `sort_order`) VALUES ('默认分类', 'default', 'Rebirth的起点', 1, 0);

INSERT INTO `rb_options` (`option_name`, `option_value`) VALUES
('site_title', 'Rebirth Blog'),
('site_subtitle', 'Stay Hungry, Stay Foolish.'),
('show_subtitle_in_title', '1'),
('site_desc', '纯净、极简的独立博客系统。'),
('site_keywords', '博客,开源,Rebirth'),
('logo_type', 'text'),
('site_logo', 'Re<span>birth</span>'),
('site_favicon', ''),
('site_theme', 'glass'),
('home_layout', 'card'), 
('home_columns', '3'),
('post_limit', '12'),
('top_area_mode', 'text'),
('top_bg_image', ''),
('slider_data', '[]'),
('show_summary', '1'),
('title_lines', '2'),
('comment_audit', '0'),
('sidebar_enable', '1'),
('sidebar_blocks', 'author,toc,capsule,recent'),
('author_name', 'Rebirth 主理人'),
('author_avatar', ''),
('author_desc', '热爱技术，热爱生活。'),
('author_gender', '♂ 男'),
('author_email', 'admin@example.com'),
('footer_text', '记录生活，分享技术，不忘初心。'),
('icp_beian', ''),
('gov_beian', ''),
('build_dir', 'article'), 
('enable_dark_mode', '1'),
('site_version', '4.5.0');
COMMIT;