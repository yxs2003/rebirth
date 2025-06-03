from flask import Blueprint, request, jsonify
from src.models.base import db
from src.models.category import Category
from src.routes.user import token_required

category_bp = Blueprint('category', __name__)

@category_bp.route('/', methods=['GET'])
def get_categories():
    """获取分类列表"""
    # 只获取顶级分类
    categories = Category.query.filter_by(parent_id=None).all()
    return jsonify({
        'categories': [category.to_dict(include_children=True) for category in categories]
    }), 200

@category_bp.route('/<int:id>', methods=['GET'])
def get_category(id):
    """获取单个分类详情"""
    category = Category.query.get_or_404(id)
    return jsonify({'category': category.to_dict(include_children=True)}), 200

@category_bp.route('/', methods=['POST'])
@token_required
def create_category(current_user):
    """创建新分类（需要管理员权限）"""
    if current_user.role != 'admin':
        return jsonify({'message': '权限不足'}), 403
    
    data = request.get_json()
    
    # 验证必要字段
    if not all(k in data for k in ('name', 'slug')):
        return jsonify({'message': '缺少必要字段'}), 400
    
    # 检查slug是否已存在
    if Category.query.filter_by(slug=data['slug']).first():
        return jsonify({'message': 'Slug已存在'}), 400
    
    # 检查父分类是否存在
    parent_id = data.get('parent_id')
    if parent_id and not Category.query.get(parent_id):
        return jsonify({'message': '父分类不存在'}), 400
    
    # 创建分类
    category = Category(
        name=data['name'],
        slug=data['slug'],
        parent_id=parent_id,
        description=data.get('description', '')
    )
    
    db.session.add(category)
    db.session.commit()
    
    return jsonify({
        'message': '分类创建成功',
        'category': category.to_dict()
    }), 201

@category_bp.route('/<int:id>', methods=['PUT'])
@token_required
def update_category(current_user, id):
    """更新分类（需要管理员权限）"""
    if current_user.role != 'admin':
        return jsonify({'message': '权限不足'}), 403
    
    category = Category.query.get_or_404(id)
    data = request.get_json()
    
    # 更新分类字段
    if 'name' in data:
        category.name = data['name']
    if 'slug' in data:
        # 检查slug是否已被其他分类使用
        existing = Category.query.filter_by(slug=data['slug']).first()
        if existing and existing.id != category.id:
            return jsonify({'message': 'Slug已被使用'}), 400
        category.slug = data['slug']
    if 'description' in data:
        category.description = data['description']
    if 'parent_id' in data:
        # 检查父分类是否存在
        parent_id = data['parent_id']
        if parent_id:
            parent = Category.query.get(parent_id)
            if not parent:
                return jsonify({'message': '父分类不存在'}), 400
            # 防止循环引用
            if parent_id == category.id:
                return jsonify({'message': '不能将分类设为自己的父分类'}), 400
        category.parent_id = parent_id
    
    db.session.commit()
    
    return jsonify({
        'message': '分类更新成功',
        'category': category.to_dict()
    }), 200

@category_bp.route('/<int:id>', methods=['DELETE'])
@token_required
def delete_category(current_user, id):
    """删除分类（需要管理员权限）"""
    if current_user.role != 'admin':
        return jsonify({'message': '权限不足'}), 403
    
    category = Category.query.get_or_404(id)
    
    # 检查是否有子分类
    if category.children:
        return jsonify({'message': '请先删除所有子分类'}), 400
    
    # 删除分类
    db.session.delete(category)
    db.session.commit()
    
    return jsonify({'message': '分类删除成功'}), 200
