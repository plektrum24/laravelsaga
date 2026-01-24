-- Migration: Add barcode column to products table
ALTER TABLE products ADD COLUMN barcode VARCHAR(255) DEFAULT NULL;
CREATE INDEX idx_products_barcode ON products(barcode);
