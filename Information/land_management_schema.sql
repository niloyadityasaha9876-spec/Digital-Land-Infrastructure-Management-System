-- Database creation
CREATE DATABASE land_management_db;

USE land_management_db;

-- ADMIN table
CREATE TABLE admin (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    nid_number VARCHAR(185) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('superadmin','officer','surveyor') NOT NULL,
    last_login DATETIME DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- USER table
CREATE TABLE user (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    nid_number VARCHAR(185) NOT NULL UNIQUE,
    email VARCHAR(185) DEFAULT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    address TEXT DEFAULT NULL,
    is_verified TINYINT(1) DEFAULT 0,
    registered_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- LAND table
CREATE TABLE land (
    land_id INT AUTO_INCREMENT PRIMARY KEY,
    parcel_number VARCHAR(50) NOT NULL UNIQUE,
    area_size DECIMAL(12,2) NOT NULL,
    land_type ENUM('residential','agricultural','commercial','industrial','government') NOT NULL,
    location VARCHAR(100) NOT NULL,
    upazila VARCHAR(100) NOT NULL,
    district VARCHAR(100) NOT NULL,
    current_status ENUM('active','disputed','transferred','government') DEFAULT 'active'
);

-- LAND_TRANSACTION table
CREATE TABLE land_transaction (
    transaction_id INT AUTO_INCREMENT PRIMARY KEY,
    land_id INT NOT NULL,
    from_user_id INT NOT NULL,
    to_user_id INT NOT NULL,
    transaction_type ENUM('sale','inheritance','gift','court_order') NOT NULL,
    transaction_date DATE DEFAULT NULL,
    amount DECIMAL(15,2) DEFAULT 0.00,
    deed_number VARCHAR(100) NOT NULL UNIQUE,
    approved_by INT DEFAULT NULL,
    approved_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (land_id) REFERENCES land(land_id) ON DELETE RESTRICT,
    FOREIGN KEY (from_user_id) REFERENCES user(user_id) ON DELETE RESTRICT,
    FOREIGN KEY (to_user_id) REFERENCES user(user_id) ON DELETE RESTRICT,
    FOREIGN KEY (approved_by) REFERENCES admin(admin_id) ON DELETE SET NULL
);

-- LAND_HISTORY table
CREATE TABLE land_history (
    history_id INT AUTO_INCREMENT PRIMARY KEY,
    land_id INT NOT NULL,
    event_type ENUM('survey','transaction','dispute_filed','status_change','billboard_placed','work_order') NOT NULL,
    description TEXT NOT NULL,
    event_date DATE NOT NULL,
    recorded_by_admin INT DEFAULT NULL,
    FOREIGN KEY (land_id) REFERENCES land(land_id) ON DELETE RESTRICT,
    FOREIGN KEY (recorded_by_admin) REFERENCES admin(admin_id) ON DELETE SET NULL
);

-- LAND_SURVEY table
CREATE TABLE land_survey (
    survey_id INT AUTO_INCREMENT PRIMARY KEY,
    land_id INT NOT NULL,
    survey_admin_id INT DEFAULT NULL,
    survey_date DATE DEFAULT NULL,
    survey_type ENUM('digital','satellite') NOT NULL,
    survey_notes TEXT DEFAULT NULL,
    FOREIGN KEY (land_id) REFERENCES land(land_id) ON DELETE RESTRICT,
    FOREIGN KEY (survey_admin_id) REFERENCES admin(admin_id) ON DELETE SET NULL
);

-- DISPUTE table
CREATE TABLE dispute (
    dispute_id INT AUTO_INCREMENT PRIMARY KEY,
    land_id INT NOT NULL,
    raised_by_user INT NOT NULL,
    opponent_party VARCHAR(45) DEFAULT NULL,
    dispute_type ENUM('ownership','boundary','mutation','encroachment','tax') NOT NULL,
    status ENUM('filed','under_review','resolved','rejected','dismissed') DEFAULT 'filed',
    escalation_level INT DEFAULT 0,
    filed_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_updated DATETIME DEFAULT CURRENT_TIMESTAMP,
    resolution_notes TEXT DEFAULT NULL,
    handled_by_admin INT DEFAULT NULL,
    FOREIGN KEY (land_id) REFERENCES land(land_id) ON DELETE RESTRICT,
    FOREIGN KEY (raised_by_user) REFERENCES user(user_id) ON DELETE RESTRICT,
    FOREIGN KEY (handled_by_admin) REFERENCES admin(admin_id) ON DELETE SET NULL
);

-- DOCUMENT_REGISTRATION table
CREATE TABLE document_registration (
    document_id INT AUTO_INCREMENT PRIMARY KEY,
    land_id INT NOT NULL,
    document_type ENUM('deed','survey_certificate','tax_clearance','court_order','mutation_order','other') NOT NULL,
    title VARCHAR(255) DEFAULT NULL,
    registered_by_admin INT DEFAULT NULL,
    registration_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    expiry_date DATE DEFAULT NULL,
    doc_status ENUM('valid','expiring_soon','expired','revoked') DEFAULT 'valid',
    verified TINYINT(1) DEFAULT 0,
    FOREIGN KEY (registered_by_admin) REFERENCES admin(admin_id) ON DELETE RESTRICT,
    FOREIGN KEY (land_id) REFERENCES land(land_id) ON DELETE RESTRICT
);

-- AUDIT_LOG table
CREATE TABLE audit_log (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    table_name VARCHAR(50) NOT NULL,
    action ENUM('INSERT','UPDATE','DELETE') NOT NULL,
    record_id INT DEFAULT NULL,
    admin_id INT DEFAULT NULL,
    user_id INT DEFAULT NULL,
    remarks TEXT DEFAULT NULL,
    log_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admin(admin_id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE SET NULL
);

-- BILLBOARD table
CREATE TABLE billboard (
    billboard_id INT AUTO_INCREMENT PRIMARY KEY,
    land_id INT NOT NULL,
    owner_user_id INT NOT NULL,
    license_number VARCHAR(8) NOT NULL UNIQUE,
    size_sqm DECIMAL(8,2) NOT NULL,
    installation_date DATE NOT NULL,
    annual_fee DECIMAL(8,2) NOT NULL,
    status ENUM('active','expired','suspended','removed') DEFAULT 'active',
    FOREIGN KEY (land_id) REFERENCES land(land_id) ON DELETE RESTRICT,
    FOREIGN KEY (owner_user_id) REFERENCES user(user_id) ON DELETE RESTRICT
);

-- BILLBOARD_PAYMENT table
CREATE TABLE billboard_payment (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    billboard_id INT NOT NULL,
    payment_date DATE NOT NULL,
    payment_amount DECIMAL(8,2) NOT NULL,
    payment_method ENUM('cash','bank_transfer','online') NOT NULL,
    payment_status ENUM('paid','pending','overdue') DEFAULT 'pending',
    received_by_admin INT DEFAULT NULL,
    FOREIGN KEY (billboard_id) REFERENCES billboard(billboard_id) ON DELETE RESTRICT,
    FOREIGN KEY (received_by_admin) REFERENCES admin(admin_id) ON DELETE SET NULL
);

-- TAX table
CREATE TABLE tax (
    tax_id INT AUTO_INCREMENT PRIMARY KEY,
    land_id INT NOT NULL,
    owner_user_id INT NOT NULL,
    tax_financial_year YEAR NOT NULL,
    tax_assessed DECIMAL(8,2) NOT NULL,
    tax_paid DECIMAL(8,2) NOT NULL,
    tax_status ENUM('unpaid','partial','paid','overdue') DEFAULT 'unpaid',
    calculated_by INT DEFAULT NULL,
    FOREIGN KEY (land_id) REFERENCES land(land_id) ON DELETE RESTRICT,
    FOREIGN KEY (owner_user_id) REFERENCES user(user_id) ON DELETE RESTRICT,
    FOREIGN KEY (calculated_by) REFERENCES admin(admin_id) ON DELETE SET NULL
);

-- INFRASTRUCTURE table
CREATE TABLE infrastructure (
    asset_id INT AUTO_INCREMENT PRIMARY KEY,
    land_id INT NOT NULL,
    asset_name VARCHAR(150) NOT NULL,
    asset_type ENUM('building','road','drainage','bridge','utility') NOT NULL,
    construction_date DATE DEFAULT NULL,
    condition_status ENUM('excellent','good','fair','poor','critical') DEFAULT NULL,
    owner_entity VARCHAR(120) DEFAULT NULL,
    FOREIGN KEY (land_id) REFERENCES land(land_id) ON DELETE RESTRICT
);

-- MAINTENANCE table
CREATE TABLE maintenance (
    maintenance_id INT AUTO_INCREMENT PRIMARY KEY,
    asset_id INT NOT NULL,
    maintenance_date DATE NOT NULL,
    maintenance_type ENUM('routine','emergency','upgrade','demolition') NOT NULL,
    cost_estimate DECIMAL(12,2) DEFAULT 0.0,
    assigned_admin_id INT DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    next_scheduled_date DATE DEFAULT NULL,
    FOREIGN KEY (asset_id) REFERENCES infrastructure(asset_id) ON DELETE RESTRICT,
    FOREIGN KEY (assigned_admin_id) REFERENCES admin(admin_id) ON DELETE SET NULL
);

-- WORK_ORDER table
CREATE TABLE work_order (
    work_order_id INT AUTO_INCREMENT PRIMARY KEY,
    land_id INT NOT NULL,
    work_type ENUM('road_construction','survey','demolition','utility_install','inspection') NOT NULL,
    start_date DATE DEFAULT NULL,
    end_date DATE DEFAULT NULL,
    status ENUM('pending','in_progress','completed','cancelled') DEFAULT 'pending',
    assigned_admin_id INT DEFAULT NULL,
    FOREIGN KEY (land_id) REFERENCES land(land_id) ON DELETE RESTRICT,
    FOREIGN KEY (assigned_admin_id) REFERENCES admin(admin_id) ON DELETE SET NULL
);
