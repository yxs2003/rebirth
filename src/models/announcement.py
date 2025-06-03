from src.models.base import db, TimestampMixin

class Announcement(db.Model, TimestampMixin):
    """公告模型"""
    __tablename__ = 'announcements'
    
    id = db.Column(db.Integer, primary_key=True)
    title = db.Column(db.String(255), nullable=False)
    content = db.Column(db.Text)
    status = db.Column(db.Boolean, default=True)
    sort_order = db.Column(db.Integer, default=0)
    
    def to_dict(self):
        return {
            'id': self.id,
            'title': self.title,
            'content': self.content,
            'status': self.status,
            'sort_order': self.sort_order,
            'created_at': self.created_at.isoformat() if self.created_at else None,
            'updated_at': self.updated_at.isoformat() if self.updated_at else None
        }
