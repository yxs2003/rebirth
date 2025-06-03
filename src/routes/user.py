from flask import Blueprint, request, jsonify
from werkzeug.security import generate_password_hash, check_password_hash
from src.models.base import db
from src.models.user import User
import jwt
import datetime
import os

user_bp = Blueprint('user', __name__)

# 密钥用于JWT令牌
SECRET_KEY = os.environ.get('SECRET_KEY', 'rebirth_secret_key')

@user_bp.route('/register', methods=['POST'])
def register():
    """注册新用户"""
    data = request.get_json()
    
    # 验证必要字段
    if not all(k in data for k in ('username', 'password', 'email')):
        return jsonify({'message': '缺少必要字段'}), 400
    
    # 检查用户名是否已存在
    if User.query.filter_by(username=data['username']).first():
        return jsonify({'message': '用户名已存在'}), 400
    
    # 检查邮箱是否已存在
    if User.query.filter_by(email=data['email']).first():
        return jsonify({'message': '邮箱已被注册'}), 400
    
    # 创建新用户
    user = User(
        username=data['username'],
        email=data['email'],
        role=data.get('role', 'editor')
    )
    user.password = data['password']  # 使用setter方法加密密码
    
    # 保存到数据库
    db.session.add(user)
    db.session.commit()
    
    return jsonify({'message': '注册成功', 'user': user.to_dict()}), 201

@user_bp.route('/login', methods=['POST'])
def login():
    """用户登录"""
    data = request.get_json()
    
    # 验证必要字段
    if not all(k in data for k in ('username', 'password')):
        return jsonify({'message': '缺少必要字段'}), 400
    
    # 查找用户
    user = User.query.filter_by(username=data['username']).first()
    
    # 验证用户和密码
    if not user or not user.verify_password(data['password']):
        return jsonify({'message': '用户名或密码错误'}), 401
    
    # 生成JWT令牌
    token = jwt.encode({
        'user_id': user.id,
        'username': user.username,
        'role': user.role,
        'exp': datetime.datetime.utcnow() + datetime.timedelta(days=1)
    }, SECRET_KEY, algorithm='HS256')
    
    return jsonify({
        'message': '登录成功',
        'token': token,
        'user': user.to_dict()
    }), 200

@user_bp.route('/profile', methods=['GET'])
def get_profile():
    """获取用户资料（需要认证）"""
    token = request.headers.get('Authorization')
    if not token:
        return jsonify({'message': '未提供认证令牌'}), 401
    
    try:
        # 解析令牌
        token = token.split(' ')[1] if ' ' in token else token
        payload = jwt.decode(token, SECRET_KEY, algorithms=['HS256'])
        user_id = payload['user_id']
        
        # 获取用户信息
        user = User.query.get(user_id)
        if not user:
            return jsonify({'message': '用户不存在'}), 404
        
        return jsonify({'user': user.to_dict()}), 200
    except jwt.ExpiredSignatureError:
        return jsonify({'message': '令牌已过期'}), 401
    except jwt.InvalidTokenError:
        return jsonify({'message': '无效的令牌'}), 401

@user_bp.route('/profile', methods=['PUT'])
def update_profile():
    """更新用户资料（需要认证）"""
    token = request.headers.get('Authorization')
    if not token:
        return jsonify({'message': '未提供认证令牌'}), 401
    
    try:
        # 解析令牌
        token = token.split(' ')[1] if ' ' in token else token
        payload = jwt.decode(token, SECRET_KEY, algorithms=['HS256'])
        user_id = payload['user_id']
        
        # 获取用户
        user = User.query.get(user_id)
        if not user:
            return jsonify({'message': '用户不存在'}), 404
        
        # 更新用户信息
        data = request.get_json()
        if 'email' in data:
            # 检查邮箱是否已被其他用户使用
            existing_user = User.query.filter_by(email=data['email']).first()
            if existing_user and existing_user.id != user.id:
                return jsonify({'message': '邮箱已被其他用户注册'}), 400
            user.email = data['email']
        
        if 'password' in data:
            user.password = data['password']
        
        db.session.commit()
        
        return jsonify({'message': '资料更新成功', 'user': user.to_dict()}), 200
    except jwt.ExpiredSignatureError:
        return jsonify({'message': '令牌已过期'}), 401
    except jwt.InvalidTokenError:
        return jsonify({'message': '无效的令牌'}), 401

# 用于验证令牌的辅助函数
def token_required(f):
    def decorated(*args, **kwargs):
        token = request.headers.get('Authorization')
        if not token:
            return jsonify({'message': '未提供认证令牌'}), 401
        
        try:
            token = token.split(' ')[1] if ' ' in token else token
            payload = jwt.decode(token, SECRET_KEY, algorithms=['HS256'])
            user_id = payload['user_id']
            user = User.query.get(user_id)
            if not user:
                return jsonify({'message': '用户不存在'}), 404
            return f(user, *args, **kwargs)
        except jwt.ExpiredSignatureError:
            return jsonify({'message': '令牌已过期'}), 401
        except jwt.InvalidTokenError:
            return jsonify({'message': '无效的令牌'}), 401
    
    decorated.__name__ = f.__name__
    return decorated
