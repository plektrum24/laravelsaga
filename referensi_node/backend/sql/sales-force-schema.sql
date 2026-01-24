-- Salesmen Table
CREATE TABLE IF NOT EXISTS salesmen (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    area VARCHAR(100),
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Visit Plans Table
CREATE TABLE IF NOT EXISTS visit_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    salesman_id INT NOT NULL,
    customer_id INT NOT NULL,
    planned_date DATE NOT NULL,
    status ENUM('planned', 'visited', 'cancelled') DEFAULT 'planned',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_salesman_date (salesman_id, planned_date)
);

-- Note: We need to alter transactions table to add salesman_id and visit_id
-- This will be handled by the migration script carefully to avoid errors if columns exist.
