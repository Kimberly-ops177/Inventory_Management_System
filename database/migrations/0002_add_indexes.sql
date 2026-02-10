-- =====================================================
-- Performance Optimization: Add Database Indexes
-- =====================================================
-- This migration adds indexes to foreign keys and frequently queried columns

-- Users table indexes
CREATE INDEX idx_users_email ON users(email);

-- Products table indexes
CREATE INDEX idx_products_sku ON products(sku);
CREATE INDEX idx_products_category_id ON products(category_id);
CREATE INDEX idx_products_name ON products(name);

-- Purchase Orders indexes
CREATE INDEX idx_po_supplier_id ON purchase_orders(supplier_id);
CREATE INDEX idx_po_status ON purchase_orders(status);
CREATE INDEX idx_po_created_by ON purchase_orders(created_by);
CREATE INDEX idx_po_reference ON purchase_orders(reference);
CREATE INDEX idx_po_created_at ON purchase_orders(created_at);

-- Purchase Order Items indexes
CREATE INDEX idx_poi_purchase_order_id ON purchase_order_items(purchase_order_id);
CREATE INDEX idx_poi_product_id ON purchase_order_items(product_id);

-- Sales Orders indexes
CREATE INDEX idx_so_status ON sales_orders(status);
CREATE INDEX idx_so_created_by ON sales_orders(created_by);
CREATE INDEX idx_so_reference ON sales_orders(reference);
CREATE INDEX idx_so_created_at ON sales_orders(created_at);
CREATE INDEX idx_so_customer_name ON sales_orders(customer_name);

-- Sales Order Items indexes
CREATE INDEX idx_soi_sales_order_id ON sales_order_items(sales_order_id);
CREATE INDEX idx_soi_product_id ON sales_order_items(product_id);

-- Stock Movements indexes
CREATE INDEX idx_sm_product_id ON stock_movements(product_id);
CREATE INDEX idx_sm_direction ON stock_movements(direction);
CREATE INDEX idx_sm_source ON stock_movements(source);
CREATE INDEX idx_sm_source_id ON stock_movements(source_id);
CREATE INDEX idx_sm_created_at ON stock_movements(created_at);

-- Stock Alerts indexes
CREATE INDEX idx_sa_product_id ON stock_alerts(product_id);
CREATE INDEX idx_sa_is_resolved ON stock_alerts(is_resolved);
CREATE INDEX idx_sa_triggered_at ON stock_alerts(triggered_at);

-- Audit Logs indexes
CREATE INDEX idx_audit_user_id ON audit_logs(user_id);
CREATE INDEX idx_audit_action ON audit_logs(action);
CREATE INDEX idx_audit_created_at ON audit_logs(created_at);
