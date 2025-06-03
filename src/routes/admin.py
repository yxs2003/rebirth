from flask import Blueprint, request, jsonify
from src.routes.user import token_required

admin_bp = Blueprint('admin', __name__)

@admin_bp.route('/dashboard', methods=['GET'])
@token_required
def dashboard(current_user):
    """获取管理员仪表盘数据（需要管理员权限）"""
    if current_user.role != 'admin':
        return jsonify({'message': '权限不足'}), 403
    
    # 这里可以添加更多仪表盘数据，如文章数量、用户数量等
    return jsonify({
        'user': current_user.to_dict(),
        'message': '管理员仪表盘'
    }), 200
