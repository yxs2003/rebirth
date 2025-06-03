import os
import sys
# DON'T CHANGE THIS !!!
sys.path.insert(0, os.path.dirname(os.path.dirname(__file__)))

from flask import Flask, send_from_directory
from src.models.base import db
from src.routes.user import user_bp
from src.routes.admin import admin_bp
from src.routes.article import article_bp
from src.routes.category import category_bp
from src.routes.collection import collection_bp
from src.routes.slide import slide_bp
from src.routes.announcement import announcement_bp
from src.routes.menu import menu_bp
from src.routes.setting import setting_bp

app = Flask(__name__, static_folder=os.path.join(os.path.dirname(__file__), 'static'))
app.config['SECRET_KEY'] = 'asdf#FGSgvasgf$5$WGT'

# Enable database
app.config['SQLALCHEMY_DATABASE_URI'] = f"mysql+pymysql://{os.getenv('DB_USERNAME', 'root')}:{os.getenv('DB_PASSWORD', 'password')}@{os.getenv('DB_HOST', 'localhost')}:{os.getenv('DB_PORT', '3306')}/{os.getenv('DB_NAME', 'mydb')}"
app.config['SQLALCHEMY_TRACK_MODIFICATIONS'] = False
db.init_app(app)

# Register blueprints
app.register_blueprint(user_bp, url_prefix='/api/users')
app.register_blueprint(admin_bp, url_prefix='/api/admin')
app.register_blueprint(article_bp, url_prefix='/api/articles')
app.register_blueprint(category_bp, url_prefix='/api/categories')
app.register_blueprint(collection_bp, url_prefix='/api/collections')
app.register_blueprint(slide_bp, url_prefix='/api/slides')
app.register_blueprint(announcement_bp, url_prefix='/api/announcements')
app.register_blueprint(menu_bp, url_prefix='/api/menus')
app.register_blueprint(setting_bp, url_prefix='/api/settings')

# Create database tables
with app.app_context():
    db.create_all()

@app.route('/', defaults={'path': ''})
@app.route('/<path:path>')
def serve(path):
    static_folder_path = app.static_folder
    if static_folder_path is None:
            return "Static folder not configured", 404

    if path != "" and os.path.exists(os.path.join(static_folder_path, path)):
        return send_from_directory(static_folder_path, path)
    else:
        index_path = os.path.join(static_folder_path, 'index.html')
        if os.path.exists(index_path):
            return send_from_directory(static_folder_path, 'index.html')
        else:
            return "index.html not found", 404


if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=True)
