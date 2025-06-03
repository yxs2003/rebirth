from flask import Blueprint, request, jsonify
from src.models.base import db
from src.models.slide import Slide
from src.routes.user import token_required

slide_bp = Blueprint('slide', __name__)

@slide_bp.route('/', methods=['GET'])
def get_slides():
    """获取幻灯片列表"""
    # 默认只获取启用的幻灯片
    status = request.args.get('status', 'active')
    
    query = Slide.query
    if status == 'active':
        query = query.filter_by(status=True)
    
    slides = query.order_by(Slide.sort_order).all()
    
    return jsonify({
        'slides': [slide.to_dict() for slide in slides]
    }), 200

@slide_bp.route('/<int:id>', methods=['GET'])
def get_slide(id):
    """获取单个幻灯片详情"""
    slide = Slide.query.get_or_404(id)
    return jsonify({'slide': slide.to_dict()}), 200

@slide_bp.route('/', methods=['POST'])
@token_required
def create_slide(current_user):
    """创建新幻灯片（需要管理员权限）"""
    if current_user.role != 'admin':
        return jsonify({'message': '权限不足'}), 403
    
    data = request.get_json()
    
    # 验证必要字段
    if not all(k in data for k in ('title', 'image')):
        return jsonify({'message': '缺少必要字段'}), 400
    
    # 创建幻灯片
    slide = Slide(
        title=data['title'],
        image=data['image'],
        link=data.get('link', ''),
        description=data.get('description', ''),
        sort_order=data.get('sort_order', 0),
        status=data.get('status', True)
    )
    
    db.session.add(slide)
    db.session.commit()
    
    return jsonify({
        'message': '幻灯片创建成功',
        'slide': slide.to_dict()
    }), 201

@slide_bp.route('/<int:id>', methods=['PUT'])
@token_required
def update_slide(current_user, id):
    """更新幻灯片（需要管理员权限）"""
    if current_user.role != 'admin':
        return jsonify({'message': '权限不足'}), 403
    
    slide = Slide.query.get_or_404(id)
    data = request.get_json()
    
    # 更新幻灯片字段
    if 'title' in data:
        slide.title = data['title']
    if 'image' in data:
        slide.image = data['image']
    if 'link' in data:
        slide.link = data['link']
    if 'description' in data:
        slide.description = data['description']
    if 'sort_order' in data:
        slide.sort_order = data['sort_order']
    if 'status' in data:
        slide.status = data['status']
    
    db.session.commit()
    
    return jsonify({
        'message': '幻灯片更新成功',
        'slide': slide.to_dict()
    }), 200

@slide_bp.route('/<int:id>', methods=['DELETE'])
@token_required
def delete_slide(current_user, id):
    """删除幻灯片（需要管理员权限）"""
    if current_user.role != 'admin':
        return jsonify({'message': '权限不足'}), 403
    
    slide = Slide.query.get_or_404(id)
    
    # 删除幻灯片
    db.session.delete(slide)
    db.session.commit()
    
    return jsonify({'message': '幻灯片删除成功'}), 200

@slide_bp.route('/sort', methods=['PUT'])
@token_required
def sort_slides(current_user):
    """更新幻灯片排序（需要管理员权限）"""
    if current_user.role != 'admin':
        return jsonify({'message': '权限不足'}), 403
    
    data = request.get_json()
    
    if 'slide_ids' not in data or not isinstance(data['slide_ids'], list):
        return jsonify({'message': '缺少幻灯片ID列表'}), 400
    
    # 更新排序
    for i, slide_id in enumerate(data['slide_ids']):
        slide = Slide.query.get(slide_id)
        if slide:
            slide.sort_order = i
    
    db.session.commit()
    
    return jsonify({'message': '幻灯片排序更新成功'}), 200
