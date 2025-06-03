from flask import Blueprint, request, jsonify
from src.models.base import db
from src.models.collection import Collection
from src.models.article import Article, article_collection
from src.routes.user import token_required

collection_bp = Blueprint('collection', __name__)

@collection_bp.route('/', methods=['GET'])
def get_collections():
    """获取文章合辑列表"""
    collections = Collection.query.all()
    return jsonify({
        'collections': [collection.to_dict() for collection in collections]
    }), 200

@collection_bp.route('/<int:id>', methods=['GET'])
def get_collection(id):
    """获取单个文章合辑详情"""
    collection = Collection.query.get_or_404(id)
    
    # 获取合辑中的文章
    articles = Article.query.join(article_collection).filter(
        article_collection.c.collection_id == id
    ).order_by(article_collection.c.sort_order).all()
    
    collection_data = collection.to_dict()
    collection_data['articles'] = [article.to_dict(include_content=False) for article in articles]
    
    return jsonify({'collection': collection_data}), 200

@collection_bp.route('/', methods=['POST'])
@token_required
def create_collection(current_user):
    """创建新文章合辑（需要管理员权限）"""
    if current_user.role != 'admin':
        return jsonify({'message': '权限不足'}), 403
    
    data = request.get_json()
    
    # 验证必要字段
    if not all(k in data for k in ('title', 'slug')):
        return jsonify({'message': '缺少必要字段'}), 400
    
    # 检查slug是否已存在
    if Collection.query.filter_by(slug=data['slug']).first():
        return jsonify({'message': 'Slug已存在'}), 400
    
    # 创建合辑
    collection = Collection(
        title=data['title'],
        slug=data['slug'],
        description=data.get('description', ''),
        cover_image=data.get('cover_image', '')
    )
    
    db.session.add(collection)
    db.session.commit()
    
    return jsonify({
        'message': '文章合辑创建成功',
        'collection': collection.to_dict()
    }), 201

@collection_bp.route('/<int:id>', methods=['PUT'])
@token_required
def update_collection(current_user, id):
    """更新文章合辑（需要管理员权限）"""
    if current_user.role != 'admin':
        return jsonify({'message': '权限不足'}), 403
    
    collection = Collection.query.get_or_404(id)
    data = request.get_json()
    
    # 更新合辑字段
    if 'title' in data:
        collection.title = data['title']
    if 'slug' in data:
        # 检查slug是否已被其他合辑使用
        existing = Collection.query.filter_by(slug=data['slug']).first()
        if existing and existing.id != collection.id:
            return jsonify({'message': 'Slug已被使用'}), 400
        collection.slug = data['slug']
    if 'description' in data:
        collection.description = data['description']
    if 'cover_image' in data:
        collection.cover_image = data['cover_image']
    
    db.session.commit()
    
    return jsonify({
        'message': '文章合辑更新成功',
        'collection': collection.to_dict()
    }), 200

@collection_bp.route('/<int:id>/articles', methods=['PUT'])
@token_required
def update_collection_articles(current_user, id):
    """更新文章合辑中的文章（需要管理员权限）"""
    if current_user.role != 'admin':
        return jsonify({'message': '权限不足'}), 403
    
    collection = Collection.query.get_or_404(id)
    data = request.get_json()
    
    if 'article_ids' not in data or not isinstance(data['article_ids'], list):
        return jsonify({'message': '缺少文章ID列表'}), 400
    
    # 获取文章
    articles = Article.query.filter(Article.id.in_(data['article_ids'])).all()
    if len(articles) != len(data['article_ids']):
        return jsonify({'message': '部分文章不存在'}), 400
    
    # 更新合辑中的文章
    collection.articles = articles
    
    # 更新排序
    for i, article_id in enumerate(data['article_ids']):
        db.session.execute(
            article_collection.update().
            where(article_collection.c.collection_id == id).
            where(article_collection.c.article_id == article_id).
            values(sort_order=i)
        )
    
    db.session.commit()
    
    return jsonify({'message': '文章合辑内容更新成功'}), 200

@collection_bp.route('/<int:id>', methods=['DELETE'])
@token_required
def delete_collection(current_user, id):
    """删除文章合辑（需要管理员权限）"""
    if current_user.role != 'admin':
        return jsonify({'message': '权限不足'}), 403
    
    collection = Collection.query.get_or_404(id)
    
    # 删除合辑
    db.session.delete(collection)
    db.session.commit()
    
    return jsonify({'message': '文章合辑删除成功'}), 200
