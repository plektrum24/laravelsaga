-- SAGA TOKO APP - Main Database Initialization
-- Run this script to create the main database and seed initial data

-- Create database
CREATE DATABASE IF NOT EXISTS saga_main;
USE saga_main;

-- Create tenants table
CREATE TABLE IF NOT EXISTS tenants (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  code VARCHAR(50) UNIQUE NOT NULL,
  database_name VARCHAR(100) NOT NULL,
  logo_url VARCHAR(500),
  address TEXT,
  phone VARCHAR(50),
  status ENUM('active', 'suspended', 'inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create users table
CREATE TABLE IF NOT EXISTS users (
  id INT PRIMARY KEY AUTO_INCREMENT,
  email VARCHAR(255) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  name VARCHAR(255) NOT NULL,
  role ENUM('super_admin', 'tenant_owner', 'manager', 'cashier') NOT NULL,
  tenant_id INT NULL,
  is_active BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE SET NULL
);

-- Create index for faster lookups
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_tenant ON users(tenant_id);
CREATE INDEX idx_tenants_code ON tenants(code);
CREATE INDEX idx_tenants_status ON tenants(status);

-- Seed Super Admin user
-- Password: admin123 (bcrypt hash)
INSERT INTO users (email, password, name, role, is_active) VALUES 
('admin@sagatoko.com', '$2a$10$rTQpZqFWkAQB5.6WfhF0.uWqQMGPMxNmZYQxQ6B7WzQcGqGGCJVJK', 'Super Admin', 'super_admin', true)
ON DUPLICATE KEY UPDATE name = name;

-- Seed Demo Tenant
INSERT INTO tenants (name, code, database_name, address, phone, status) VALUES 
('Toko Jakarta', 'JKT001', 'saga_tenant_jkt001', 'Jl. Sudirman No. 1, Jakarta', '021-1234567', 'active')
ON DUPLICATE KEY UPDATE name = name;

-- Seed Demo Tenant Owner
-- Password: owner123 (bcrypt hash)
INSERT INTO users (email, password, name, role, tenant_id, is_active) VALUES 
('owner@tokojakarta.com', '$2a$10$rTQpZqFWkAQB5.6WfhF0.uWqQMGPMxNmZYQxQ6B7WzQcGqGGCJVJK', 'Budi Santoso', 'tenant_owner', 1, true)
ON DUPLICATE KEY UPDATE name = name;

-- Seed Demo Cashier
-- Password: cashier123 (bcrypt hash)
INSERT INTO users (email, password, name, role, tenant_id, is_active) VALUES 
('kasir@tokojakarta.com', '$2a$10$rTQpZqFWkAQB5.6WfhF0.uWqQMGPMxNmZYQxQ6B7WzQcGqGGCJVJK', 'Ani Kasir', 'cashier', 1, true)
ON DUPLICATE KEY UPDATE name = name;

SELECT 'Main database initialized successfully!' as message;
