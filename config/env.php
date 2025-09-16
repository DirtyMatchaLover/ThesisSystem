# Application Configuration
APP_NAME="PCC Thesis Management System"
APP_ENV=development

# Database Configuration (Docker)
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=thesis_db
DB_USERNAME=thesis_user
DB_PASSWORD=your_secure_password_here
DB_ROOT_PASSWORD=super_secure_root_password

# Security Configuration
SESSION_LIFETIME=7200
UPLOAD_MAX_SIZE=10485760
ALLOWED_FILE_TYPES=pdf

# File Storage
UPLOAD_PATH=uploads/theses/
BASE_PATH=/pcc-thesishub