from flask_sqlalchemy import SQLAlchemy
from datetime import datetime

db = SQLAlchemy()

class TimestampMixin:
    """为模型添加创建时间和更新时间"""
    created_at = db.Column(db.DateTime, default=datetime.utcnow)
    updated_at = db.Column(db.DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)
