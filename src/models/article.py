from src.models.base import db, TimestampMixin
from datetime import datetime

class Article(db.Model, TimestampMixin):
    """文章模型"""
    __tablename__ = 'articles'
    
    id = db.Column(db.Integer, primary_key=True)
    title = db.Column(db.String(255), nullable=False)
    slug = db.Column(db.String(255), unique=True, nullable=False)
    content = db.Column(db.Text)
    excerpt = db.Column(db.Text)
    cover_image = db.Column(db.String(255))
    author_id = db.Column(db.Integer, db.ForeignKey('users.id'), nullable=False)
    status = db.Column(db.String(20), default='draft')  # published/draft
    featured = db.Column(db.Boolean, default=False)
    views = db.Column(db.Integer, default=0)
    published_at = db.Column(db.DateTime, nullable=True)
    
    # 关联关系通过backref在其他模型中定义
    
    def to_dict(self, include_content=True):
        data = {
            'id': self.id,
            'title': self.title,
            'slug': self.slug,
            'excerpt': self.excerpt,
            'cover_image': self.cover_image,
            'author_id': self.author_id,
            'author': self.author.username if self.author else None,
            'status': self.status,
            'featured': self.featured,
            'views': self.views,
            'created_at': self.created_at.isoformat() if self.created_at else None,
            'updated_at': self.updated_at.isoformat() if self.updated_at else None,
            'published_at': self.published_at.isoformat() if self.published_at else None,
            'categories': [category.to_dict(False) for category in self.categories]
        }
        
        if include_content:
            data['content'] = self.content
            
        return data

# 文章分类关联表
article_category = db.Table('article_category',
    db.Column('article_id', db.Integer, db.ForeignKey('articles.id'), primary_key=True),
    db.Column('category_id', db.Integer, db.ForeignKey('categories.id'), primary_key=True)
)
