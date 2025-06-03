from src.models.base import db, TimestampMixin

class Category(db.Model, TimestampMixin):
    """分类模型"""
    __tablename__ = 'categories'
    
    id = db.Column(db.Integer, primary_key=True)
    name = db.Column(db.String(50), nullable=False)
    slug = db.Column(db.String(50), unique=True, nullable=False)
    parent_id = db.Column(db.Integer, db.ForeignKey('categories.id'), nullable=True)
    description = db.Column(db.Text)
    
    # 关联关系
    children = db.relationship('Category', backref=db.backref('parent', remote_side=[id]), lazy=True)
    articles = db.relationship('Article', secondary='article_category', backref='categories', lazy=True)
    
    def to_dict(self, include_children=False):
        data = {
            'id': self.id,
            'name': self.name,
            'slug': self.slug,
            'parent_id': self.parent_id,
            'description': self.description,
            'created_at': self.created_at.isoformat() if self.created_at else None,
            'updated_at': self.updated_at.isoformat() if self.updated_at else None
        }
        
        if include_children and self.children:
            data['children'] = [child.to_dict(False) for child in self.children]
            
        return data
