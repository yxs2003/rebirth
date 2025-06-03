from flask import Blueprint, request, jsonify
from src.models.base import db
from src.models.setting import Setting
from src.routes.user import token_required
from datetime import datetime

setting_bp = Blueprint('setting', __name__)

@setting_bp.route('/', methods=['GET'])
def get_settings():
    """获取网站设置"""
    setting = Setting.query.first()
    if not setting:
        return jsonify({'message': '网站设置不存在'}), 404
    
    return jsonify({'setting': setting.to_dict()}), 200

@setting_bp.route('/', methods=['PUT'])
@token_required
def update_settings(current_user):
    """更新网站设置（需要管理员权限）"""
    if current_user.role != 'admin':
        return jsonify({'message': '权限不足'}), 403
    
    data = request.get_json()
    setting = Setting.query.first()
    
    # 如果设置不存在，创建新设置
    if not setting:
        setting = Setting(
            site_name=data.get('site_name', 'Rebirth'),
            site_description=data.get('site_description', ''),
            site_keywords=data.get('site_keywords', ''),
            theme_style=data.get('theme_style', 'flat'),
            display_mode=data.get('display_mode', 'auto')
        )
        db.session.add(setting)
    else:
        # 更新现有设置
        if 'site_name' in data:
            setting.site_name = data['site_name']
        if 'site_description' in data:
            setting.site_description = data['site_description']
        if 'site_keywords' in data:
            setting.site_keywords = data['site_keywords']
        if 'logo_light' in data:
            setting.logo_light = data['logo_light']
        if 'logo_dark' in data:
            setting.logo_dark = data['logo_dark']
        if 'theme_style' in data:
            setting.theme_style = data['theme_style']
        if 'display_mode' in data:
            setting.display_mode = data['display_mode']
        if 'footer_info' in data:
            setting.footer_info = data['footer_info']
        
        setting.updated_at = datetime.utcnow()
    
    db.session.commit()
    
    return jsonify({
        'message': '设置更新成功',
        'setting': setting.to_dict()
    }), 200
