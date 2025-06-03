from src.models.base import db, TimestampMixin

class Setting(db.Model):
    """网站设置模型"""
    __tablename__ = 'settings'
    
    id = db.Column(db.Integer, primary_key=True)
    site_name = db.Column(db.String(100), nullable=False)
    site_description = db.Column(db.Text)
    site_keywords = db.Column(db.Text)
    logo_light = db.Column(db.String(255))
    logo_dark = db.Column(db.String(255))
    theme_style = db.Column(db.String(50), default='flat')  # neumorphism/newspaper/flat
    display_mode = db.Column(db.String(20), default='auto')  # light/dark/auto
    footer_info = db.Column(db.Text)
    updated_at = db.Column(db.DateTime, nullable=True)
    
    def to_dict(self):
        return {
            'id': self.id,
            'site_name': self.site_name,
            'site_description': self.site_description,
            'site_keywords': self.site_keywords,
            'logo_light': self.logo_light,
            'logo_dark': self.logo_dark,
            'theme_style': self.theme_style,
            'display_mode': self.display_mode,
            'footer_info': self.footer_info,
            'updated_at': self.updated_at.isoformat() if self.updated_at else None
        }
