from flask import Blueprint, request, jsonify
from src.models.base import db
from src.models.menu import Menu, MenuItem
from src.routes.user import token_required

menu_bp = Blueprint('menu', __name__)

@menu_bp.route('/', methods=['GET'])
def get_menus():
    """获取菜单列表"""
    location = request.args.get('location')
    
    query = Menu.query
    if location:
        query = query.filter_by(location=location)
    
    menus = query.all()
    
    return jsonify({
        'menus': [menu.to_dict() for menu in menus]
    }), 200

@menu_bp.route('/<int:id>', methods=['GET'])
def get_menu(id):
    """获取单个菜单详情"""
    menu = Menu.query.get_or_404(id)
    return jsonify({'menu': menu.to_dict()}), 200

@menu_bp.route('/', methods=['POST'])
@token_required
def create_menu(current_user):
    """创建新菜单（需要管理员权限）"""
    if current_user.role != 'admin':
        return jsonify({'message': '权限不足'}), 403
    
    data = request.get_json()
    
    # 验证必要字段
    if not all(k in data for k in ('name', 'location')):
        return jsonify({'message': '缺少必要字段'}), 400
    
    # 创建菜单
    menu = Menu(
        name=data['name'],
        location=data['location']
    )
    
    db.session.add(menu)
    db.session.commit()
    
    return jsonify({
        'message': '菜单创建成功',
        'menu': menu.to_dict()
    }), 201

@menu_bp.route('/<int:id>', methods=['PUT'])
@token_required
def update_menu(current_user, id):
    """更新菜单（需要管理员权限）"""
    if current_user.role != 'admin':
        return jsonify({'message': '权限不足'}), 403
    
    menu = Menu.query.get_or_404(id)
    data = request.get_json()
    
    # 更新菜单字段
    if 'name' in data:
        menu.name = data['name']
    if 'location' in data:
        menu.location = data['location']
    
    db.session.commit()
    
    return jsonify({
        'message': '菜单更新成功',
        'menu': menu.to_dict()
    }), 200

@menu_bp.route('/<int:id>', methods=['DELETE'])
@token_required
def delete_menu(current_user, id):
    """删除菜单（需要管理员权限）"""
    if current_user.role != 'admin':
        return jsonify({'message': '权限不足'}), 403
    
    menu = Menu.query.get_or_404(id)
    
    # 删除菜单及其所有菜单项
    db.session.delete(menu)
    db.session.commit()
    
    return jsonify({'message': '菜单删除成功'}), 200

@menu_bp.route('/<int:menu_id>/items', methods=['GET'])
def get_menu_items(menu_id):
    """获取菜单项列表"""
    menu = Menu.query.get_or_404(menu_id)
    
    # 获取顶级菜单项
    items = MenuItem.query.filter_by(menu_id=menu_id, parent_id=None).order_by(MenuItem.sort_order).all()
    
    return jsonify({
        'menu_items': [item.to_dict(include_children=True) for item in items]
    }), 200

@menu_bp.route('/<int:menu_id>/items', methods=['POST'])
@token_required
def create_menu_item(current_user, menu_id):
    """创建新菜单项（需要管理员权限）"""
    if current_user.role != 'admin':
        return jsonify({'message': '权限不足'}), 403
    
    menu = Menu.query.get_or_404(menu_id)
    data = request.get_json()
    
    # 验证必要字段
    if not all(k in data for k in ('title', 'url')):
        return jsonify({'message': '缺少必要字段'}), 400
    
    # 检查父菜单项是否存在
    parent_id = data.get('parent_id')
    if parent_id:
        parent = MenuItem.query.get(parent_id)
        if not parent or parent.menu_id != menu_id:
            return jsonify({'message': '父菜单项不存在或不属于当前菜单'}), 400
    
    # 创建菜单项
    item = MenuItem(
        menu_id=menu_id,
        parent_id=parent_id,
        title=data['title'],
        url=data['url'],
        target=data.get('target', '_self'),
        sort_order=data.get('sort_order', 0)
    )
    
    db.session.add(item)
    db.session.commit()
    
    return jsonify({
        'message': '菜单项创建成功',
        'menu_item': item.to_dict()
    }), 201

@menu_bp.route('/items/<int:id>', methods=['PUT'])
@token_required
def update_menu_item(current_user, id):
    """更新菜单项（需要管理员权限）"""
    if current_user.role != 'admin':
        return jsonify({'message': '权限不足'}), 403
    
    item = MenuItem.query.get_or_404(id)
    data = request.get_json()
    
    # 更新菜单项字段
    if 'title' in data:
        item.title = data['title']
    if 'url' in data:
        item.url = data['url']
    if 'target' in data:
        item.target = data['target']
    if 'sort_order' in data:
        item.sort_order = data['sort_order']
    if 'parent_id' in data:
        # 检查父菜单项是否存在
        parent_id = data['parent_id']
        if parent_id:
            parent = MenuItem.query.get(parent_id)
            if not parent or parent.menu_id != item.menu_id:
                return jsonify({'message': '父菜单项不存在或不属于当前菜单'}), 400
            # 防止循环引用
            if parent_id == item.id:
                return jsonify({'message': '不能将菜单项设为自己的父菜单项'}), 400
        item.parent_id = parent_id
    
    db.session.commit()
    
    return jsonify({
        'message': '菜单项更新成功',
        'menu_item': item.to_dict()
    }), 200

@menu_bp.route('/items/<int:id>', methods=['DELETE'])
@token_required
def delete_menu_item(current_user, id):
    """删除菜单项（需要管理员权限）"""
    if current_user.role != 'admin':
        return jsonify({'message': '权限不足'}), 403
    
    item = MenuItem.query.get_or_404(id)
    
    # 检查是否有子菜单项
    if item.children:
        return jsonify({'message': '请先删除所有子菜单项'}), 400
    
    # 删除菜单项
    db.session.delete(item)
    db.session.commit()
    
    return jsonify({'message': '菜单项删除成功'}), 200

@menu_bp.route('/<int:menu_id>/items/sort', methods=['PUT'])
@token_required
def sort_menu_items(current_user, menu_id):
    """更新菜单项排序（需要管理员权限）"""
    if current_user.role != 'admin':
        return jsonify({'message': '权限不足'}), 403
    
    menu = Menu.query.get_or_404(menu_id)
    data = request.get_json()
    
    if 'items' not in data or not isinstance(data['items'], list):
        return jsonify({'message': '缺少菜单项排序数据'}), 400
    
    # 更新排序
    for item_data in data['items']:
        if 'id' not in item_data or 'sort_order' not in item_data:
            continue
            
        item = MenuItem.query.get(item_data['id'])
        if item and item.menu_id == menu_id:
            item.sort_order = item_data['sort_order']
    
    db.session.commit()
    
    return jsonify({'message': '菜单项排序更新成功'}), 200
