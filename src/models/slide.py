from src.models.base import db, TimestampMixin

class Slide(db.Model, TimestampMixin):
    """幻灯片模型"""
    __tablename__ = 'slides'
    
    id = db.Column(db.Integer, primary_key=True)
    title = db.Column(db.String(255), nullable=False)
    image = db.Column(db.String(255), nullable=False)
    link = db.Column(db.String(255))
    description = db.Column(db.Text)
    sort_order = db.Column(db.Integer, default=0)
    status = db.Column(db.Boolean, default=True)
    
    def to_dict(self):
        return {
            'id': self.id,
            'title': self.title,
            'image': self.image,
            'link': self.link,
            'description': self.description,
            'sort_order': self.sort_order,
            'status': self.status,
            'created_at': self.created_at.isoformat() if self.created_at else None,
            'updated_at': self.updated_at.isoformat() if self.updated_at else None
        }
