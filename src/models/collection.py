from src.models.base import db, TimestampMixin

class Collection(db.Model, TimestampMixin):
    """文章合辑模型"""
    __tablename__ = 'collections'
    
    id = db.Column(db.Integer, primary_key=True)
    title = db.Column(db.String(255), nullable=False)
    slug = db.Column(db.String(255), unique=True, nullable=False)
    description = db.Column(db.Text)
    cover_image = db.Column(db.String(255))
    
    # 关联关系
    articles = db.relationship('Article', secondary='article_collection', backref='collections', lazy=True)
    
    def to_dict(self):
        return {
            'id': self.id,
            'title': self.title,
            'slug': self.slug,
            'description': self.description,
            'cover_image': self.cover_image,
            'created_at': self.created_at.isoformat() if self.created_at else None,
            'updated_at': self.updated_at.isoformat() if self.updated_at else None,
            'article_count': len(self.articles) if self.articles else 0
        }

# 文章合辑关联表
article_collection = db.Table('article_collection',
    db.Column('article_id', db.Integer, db.ForeignKey('articles.id'), primary_key=True),
    db.Column('collection_id', db.Integer, db.ForeignKey('collections.id'), primary_key=True),
    db.Column('sort_order', db.Integer, default=0)
)
