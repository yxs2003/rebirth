from flask import Blueprint, request, jsonify
from src.models.base import db
from src.models.article import Article, article_category
from src.models.category import Category
from src.models.user import User
from src.routes.user import token_required
from datetime import datetime
import slugify

article_bp = Blueprint('article', __name__)

@article_bp.route('/', methods=['GET'])
def get_articles():
    """获取文章列表"""
    page = request.args.get('page', 1, type=int)
    per_page = request.args.get('per_page', 10, type=int)
    category_id = request.args.get('category_id', type=int)
    featured = request.args.get('featured', type=bool)
    status = request.args.get('status', 'published')
    
    # 构建查询
    query = Article.query
    
    # 应用过滤条件
    if category_id:
        query = query.join(article_category).join(Category).filter(Category.id == category_id)
    if featured is not None:
        query = query.filter(Article.featured == featured)
    if status:
        query = query.filter(Article.status == status)
    
    # 排序和分页
    articles = query.order_by(Article.created_at.desc()).paginate(page=page, per_page=per_page)
    
    return jsonify({
        'articles': [article.to_dict(include_content=False) for article in articles.items],
        'total': articles.total,
        'pages': articles.pages,
        'current_page': articles.page
    }), 200

@article_bp.route('/<int:id>', methods=['GET'])
def get_article(id):
    """获取单篇文章详情"""
    article = Article.query.get_or_404(id)
    
    # 增加浏览次数
    article.views += 1
    db.session.commit()
    
    return jsonify({'article': article.to_dict()}), 200

@article_bp.route('/', methods=['POST'])
@token_required
def create_article(current_user):
    """创建新文章（需要认证）"""
    data = request.get_json()
    
    # 验证必要字段
    if not all(k in data for k in ('title', 'content')):
        return jsonify({'message': '缺少必要字段'}), 400
    
    # 生成slug
    slug = data.get('slug') or slugify.slugify(data['title'])
    
    # 检查slug是否已存在
    if Article.query.filter_by(slug=slug).first():
        slug = f"{slug}-{datetime.utcnow().strftime('%Y%m%d%H%M%S')}"
    
    # 创建文章
    article = Article(
        title=data['title'],
        slug=slug,
        content=data['content'],
        excerpt=data.get('excerpt', ''),
        cover_image=data.get('cover_image', ''),
        author_id=current_user.id,
        status=data.get('status', 'draft'),
        featured=data.get('featured', False)
    )
    
    # 如果状态为已发布，设置发布时间
    if article.status == 'published':
        article.published_at = datetime.utcnow()
    
    # 添加分类
    if 'category_ids' in data and isinstance(data['category_ids'], list):
        categories = Category.query.filter(Category.id.in_(data['category_ids'])).all()
        article.categories = categories
    
    db.session.add(article)
    db.session.commit()
    
    return jsonify({
        'message': '文章创建成功',
        'article': article.to_dict()
    }), 201

@article_bp.route('/<int:id>', methods=['PUT'])
@token_required
def update_article(current_user, id):
    """更新文章（需要认证）"""
    article = Article.query.get_or_404(id)
    
    # 检查权限（作者或管理员）
    if article.author_id != current_user.id and current_user.role != 'admin':
        return jsonify({'message': '权限不足'}), 403
    
    data = request.get_json()
    
    # 更新文章字段
    if 'title' in data:
        article.title = data['title']
    if 'content' in data:
        article.content = data['content']
    if 'excerpt' in data:
        article.excerpt = data['excerpt']
    if 'cover_image' in data:
        article.cover_image = data['cover_image']
    if 'status' in data:
        old_status = article.status
        article.status = data['status']
        # 如果从草稿变为已发布，设置发布时间
        if old_status == 'draft' and article.status == 'published':
            article.published_at = datetime.utcnow()
    if 'featured' in data:
        article.featured = data['featured']
    if 'slug' in data:
        # 检查slug是否已被其他文章使用
        existing = Article.query.filter_by(slug=data['slug']).first()
        if existing and existing.id != article.id:
            return jsonify({'message': 'Slug已被使用'}), 400
        article.slug = data['slug']
    
    # 更新分类
    if 'category_ids' in data and isinstance(data['category_ids'], list):
        categories = Category.query.filter(Category.id.in_(data['category_ids'])).all()
        article.categories = categories
    
    db.session.commit()
    
    return jsonify({
        'message': '文章更新成功',
        'article': article.to_dict()
    }), 200

@article_bp.route('/<int:id>', methods=['DELETE'])
@token_required
def delete_article(current_user, id):
    """删除文章（需要认证）"""
    article = Article.query.get_or_404(id)
    
    # 检查权限（作者或管理员）
    if article.author_id != current_user.id and current_user.role != 'admin':
        return jsonify({'message': '权限不足'}), 403
    
    db.session.delete(article)
    db.session.commit()
    
    return jsonify({'message': '文章删除成功'}), 200

@article_bp.route('/search', methods=['GET'])
def search_articles():
    """搜索文章"""
    keyword = request.args.get('keyword', '')
    page = request.args.get('page', 1, type=int)
    per_page = request.args.get('per_page', 10, type=int)
    
    if not keyword:
        return jsonify({'message': '请提供搜索关键词'}), 400
    
    # 构建搜索查询
    query = Article.query.filter(
        (Article.title.like(f'%{keyword}%')) | 
        (Article.content.like(f'%{keyword}%')) |
        (Article.excerpt.like(f'%{keyword}%'))
    ).filter(Article.status == 'published')
    
    # 分页
    articles = query.order_by(Article.created_at.desc()).paginate(page=page, per_page=per_page)
    
    return jsonify({
        'articles': [article.to_dict(include_content=False) for article in articles.items],
        'total': articles.total,
        'pages': articles.pages,
        'current_page': articles.page
    }), 200
