from src.models.base import db, TimestampMixin

class Menu(db.Model, TimestampMixin):
    """菜单模型"""
    __tablename__ = 'menus'
    
    id = db.Column(db.Integer, primary_key=True)
    name = db.Column(db.String(50), nullable=False)
    location = db.Column(db.String(50), nullable=False)  # header/footer
    
    # 关联关系
    items = db.relationship('MenuItem', backref='menu', lazy=True)
    
    def to_dict(self, include_items=True):
        data = {
            'id': self.id,
            'name': self.name,
            'location': self.location,
            'created_at': self.created_at.isoformat() if self.created_at else None,
            'updated_at': self.updated_at.isoformat() if self.updated_at else None
        }
        
        if include_items and self.items:
            # 只获取顶级菜单项
            top_items = [item for item in self.items if item.parent_id is None]
            data['items'] = [item.to_dict(True) for item in top_items]
            
        return data


class MenuItem(db.Model, TimestampMixin):
    """菜单项模型"""
    __tablename__ = 'menu_items'
    
    id = db.Column(db.Integer, primary_key=True)
    menu_id = db.Column(db.Integer, db.ForeignKey('menus.id'), nullable=False)
    parent_id = db.Column(db.Integer, db.ForeignKey('menu_items.id'), nullable=True)
    title = db.Column(db.String(100), nullable=False)
    url = db.Column(db.String(255), nullable=False)
    target = db.Column(db.String(20), default='_self')  # _self/_blank
    sort_order = db.Column(db.Integer, default=0)
    
    # 关联关系
    children = db.relationship('MenuItem', backref=db.backref('parent', remote_side=[id]), lazy=True)
    
    def to_dict(self, include_children=False):
        data = {
            'id': self.id,
            'menu_id': self.menu_id,
            'parent_id': self.parent_id,
            'title': self.title,
            'url': self.url,
            'target': self.target,
            'sort_order': self.sort_order,
            'created_at': self.created_at.isoformat() if self.created_at else None,
            'updated_at': self.updated_at.isoformat() if self.updated_at else None
        }
        
        if include_children and self.children:
            data['children'] = [child.to_dict(False) for child in self.children]
            
        return data
