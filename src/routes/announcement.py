from flask import Blueprint, request, jsonify
from src.models.base import db
from src.models.announcement import Announcement
from src.routes.user import token_required

announcement_bp = Blueprint('announcement', __name__)

@announcement_bp.route('/', methods=['GET'])
def get_announcements():
    """获取公告列表"""
    # 默认只获取启用的公告
    status = request.args.get('status', 'active')
    
    query = Announcement.query
    if status == 'active':
        query = query.filter_by(status=True)
    
    announcements = query.order_by(Announcement.sort_order).all()
    
    return jsonify({
        'announcements': [announcement.to_dict() for announcement in announcements]
    }), 200

@announcement_bp.route('/<int:id>', methods=['GET'])
def get_announcement(id):
    """获取单个公告详情"""
    announcement = Announcement.query.get_or_404(id)
    return jsonify({'announcement': announcement.to_dict()}), 200

@announcement_bp.route('/', methods=['POST'])
@token_required
def create_announcement(current_user):
    """创建新公告（需要管理员权限）"""
    if current_user.role != 'admin':
        return jsonify({'message': '权限不足'}), 403
    
    data = request.get_json()
    
    # 验证必要字段
    if not all(k in data for k in ('title', 'content')):
        return jsonify({'message': '缺少必要字段'}), 400
    
    # 创建公告
    announcement = Announcement(
        title=data['title'],
        content=data['content'],
        status=data.get('status', True),
        sort_order=data.get('sort_order', 0)
    )
    
    db.session.add(announcement)
    db.session.commit()
    
    return jsonify({
        'message': '公告创建成功',
        'announcement': announcement.to_dict()
    }), 201

@announcement_bp.route('/<int:id>', methods=['PUT'])
@token_required
def update_announcement(current_user, id):
    """更新公告（需要管理员权限）"""
    if current_user.role != 'admin':
        return jsonify({'message': '权限不足'}), 403
    
    announcement = Announcement.query.get_or_404(id)
    data = request.get_json()
    
    # 更新公告字段
    if 'title' in data:
        announcement.title = data['title']
    if 'content' in data:
        announcement.content = data['content']
    if 'status' in data:
        announcement.status = data['status']
    if 'sort_order' in data:
        announcement.sort_order = data['sort_order']
    
    db.session.commit()
    
    return jsonify({
        'message': '公告更新成功',
        'announcement': announcement.to_dict()
    }), 200

@announcement_bp.route('/<int:id>', methods=['DELETE'])
@token_required
def delete_announcement(current_user, id):
    """删除公告（需要管理员权限）"""
    if current_user.role != 'admin':
        return jsonify({'message': '权限不足'}), 403
    
    announcement = Announcement.query.get_or_404(id)
    
    # 删除公告
    db.session.delete(announcement)
    db.session.commit()
    
    return jsonify({'message': '公告删除成功'}), 200

@announcement_bp.route('/sort', methods=['PUT'])
@token_required
def sort_announcements(current_user):
    """更新公告排序（需要管理员权限）"""
    if current_user.role != 'admin':
        return jsonify({'message': '权限不足'}), 403
    
    data = request.get_json()
    
    if 'announcement_ids' not in data or not isinstance(data['announcement_ids'], list):
        return jsonify({'message': '缺少公告ID列表'}), 400
    
    # 更新排序
    for i, announcement_id in enumerate(data['announcement_ids']):
        announcement = Announcement.query.get(announcement_id)
        if announcement:
            announcement.sort_order = i
    
    db.session.commit()
    
    return jsonify({'message': '公告排序更新成功'}), 200
