-- STTC dummy data for MySQL
-- Safe id range: 9001+

START TRANSACTION;

-- Roles
INSERT INTO roles (name, created_at, updated_at) VALUES
('ADMIN', NOW(), NOW()),
('STAFF', NOW(), NOW()),
('CUSTOMER', NOW(), NOW())
ON DUPLICATE KEY UPDATE
updated_at = NOW();

SET @role_admin_id = (SELECT id FROM roles WHERE name = 'ADMIN' LIMIT 1);
SET @role_staff_id = (SELECT id FROM roles WHERE name = 'STAFF' LIMIT 1);
SET @role_customer_id = (SELECT id FROM roles WHERE name = 'CUSTOMER' LIMIT 1);

-- Permissions
INSERT INTO permissions (name, created_at, updated_at) VALUES
('manage_users', NOW(), NOW()),
('manage_products', NOW(), NOW()),
('manage_orders', NOW(), NOW()),
('manage_content', NOW(), NOW())
ON DUPLICATE KEY UPDATE
updated_at = NOW();

SET @perm_manage_users_id = (SELECT id FROM permissions WHERE name = 'manage_users' LIMIT 1);
SET @perm_manage_products_id = (SELECT id FROM permissions WHERE name = 'manage_products' LIMIT 1);
SET @perm_manage_orders_id = (SELECT id FROM permissions WHERE name = 'manage_orders' LIMIT 1);
SET @perm_manage_content_id = (SELECT id FROM permissions WHERE name = 'manage_content' LIMIT 1);

-- Role permissions
INSERT INTO role_permissions (id, role_id, permission_id, created_at, updated_at) VALUES
(9201, @role_admin_id, @perm_manage_users_id, NOW(), NOW()),
(9202, @role_admin_id, @perm_manage_products_id, NOW(), NOW()),
(9203, @role_admin_id, @perm_manage_orders_id, NOW(), NOW()),
(9204, @role_admin_id, @perm_manage_content_id, NOW(), NOW()),
(9205, @role_staff_id, @perm_manage_products_id, NOW(), NOW()),
(9206, @role_staff_id, @perm_manage_orders_id, NOW(), NOW())
ON DUPLICATE KEY UPDATE
role_id = VALUES(role_id),
permission_id = VALUES(permission_id),
updated_at = NOW();

-- Users (password is plain text to match current backend login check)
INSERT INTO users (id, name, email, password, status, phone_number, avatar, address, role_id, activation_token, google_id, created_at, updated_at) VALUES
(9301, 'STTC Admin', 'admin@sttc.local', 'admin123', 'active', '0900000001', '/uploads/admin-avatar.jpg', '1 Admin Street, HCM', @role_admin_id, NULL, NULL, NOW(), NOW()),
(9302, 'Nguyen Van Staff', 'staff@sttc.local', 'staff123', 'active', '0900000002', '/uploads/staff-avatar.jpg', '2 Staff Street, HCM', @role_staff_id, NULL, NULL, NOW(), NOW()),
(9303, 'Tran Thi A', 'customer1@sttc.local', 'customer123', 'active', '0900000003', '/uploads/customer1-avatar.jpg', '3 Customer Street, HCM', @role_customer_id, NULL, NULL, NOW(), NOW()),
(9304, 'Le Van B', 'customer2@sttc.local', 'customer123', 'active', '0900000004', '/uploads/customer2-avatar.jpg', '4 Customer Street, HCM', @role_customer_id, NULL, NULL, NOW(), NOW())
ON DUPLICATE KEY UPDATE
name = VALUES(name),
password = VALUES(password),
status = VALUES(status),
phone_number = VALUES(phone_number),
avatar = VALUES(avatar),
address = VALUES(address),
role_id = VALUES(role_id),
updated_at = NOW();

-- Categories
INSERT INTO categories (id, name, slug, description, image, created_at, updated_at) VALUES
(9401, 'Dog Food', 'dog-food', 'Premium food for dogs', '/uploads/category-dog-food.jpg', NOW(), NOW()),
(9402, 'Cat Food', 'cat-food', 'Nutrition products for cats', '/uploads/category-cat-food.jpg', NOW(), NOW()),
(9403, 'Accessories', 'pet-accessories', 'Toys and accessories', '/uploads/category-accessories.jpg', NOW(), NOW())
ON DUPLICATE KEY UPDATE
name = VALUES(name),
slug = VALUES(slug),
description = VALUES(description),
image = VALUES(image),
updated_at = NOW();

-- Products
INSERT INTO products (id, name, slug, category_id, description, price, stock, status, unit, created_at, updated_at) VALUES
(9501, 'Royal Canin Mini Adult', 'royal-canin-mini-adult', 9401, 'Dry food for small breed adult dogs', 320000.00, 120, 'in_stock', 'bag', NOW(), NOW()),
(9502, 'Whiskas Tuna 1.2kg', 'whiskas-tuna-12kg', 9402, 'Cat food with tuna flavor', 185000.00, 95, 'in_stock', 'bag', NOW(), NOW()),
(9503, 'Pet Leash Nylon', 'pet-leash-nylon', 9403, 'Durable nylon leash', 99000.00, 60, 'in_stock', 'item', NOW(), NOW()),
(9504, 'Pet Bowl Stainless', 'pet-bowl-stainless', 9403, 'Stainless steel feeding bowl', 79000.00, 75, 'in_stock', 'item', NOW(), NOW())
ON DUPLICATE KEY UPDATE
name = VALUES(name),
slug = VALUES(slug),
category_id = VALUES(category_id),
description = VALUES(description),
price = VALUES(price),
stock = VALUES(stock),
status = VALUES(status),
unit = VALUES(unit),
updated_at = NOW();

-- Product images
INSERT INTO product_images (id, product_id, image, created_at, updated_at) VALUES
(9601, 9501, '/uploads/product-9501-a.jpg', NOW(), NOW()),
(9602, 9501, '/uploads/product-9501-b.jpg', NOW(), NOW()),
(9603, 9502, '/uploads/product-9502-a.jpg', NOW(), NOW()),
(9604, 9503, '/uploads/product-9503-a.jpg', NOW(), NOW())
ON DUPLICATE KEY UPDATE
product_id = VALUES(product_id),
image = VALUES(image),
updated_at = NOW();

-- Shipping addresses
INSERT INTO shipping_addresses (id, user_id, full_name, phone, address, city, `default`, created_at, updated_at) VALUES
(9701, 9303, 'Tran Thi A', '0900000003', '10 Nguyen Trai', 'Ho Chi Minh', 1, NOW(), NOW()),
(9702, 9304, 'Le Van B', '0900000004', '20 Le Loi', 'Ha Noi', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE
user_id = VALUES(user_id),
full_name = VALUES(full_name),
phone = VALUES(phone),
address = VALUES(address),
city = VALUES(city),
`default` = VALUES(`default`),
updated_at = NOW();

-- Orders
INSERT INTO orders (id, user_id, total_price, status, shipping_address_id, created_at, updated_at) VALUES
(9801, 9303, 419000.00, 'processing', 9701, NOW(), NOW()),
(9802, 9304, 264000.00, 'pending', 9702, NOW(), NOW())
ON DUPLICATE KEY UPDATE
user_id = VALUES(user_id),
total_price = VALUES(total_price),
status = VALUES(status),
shipping_address_id = VALUES(shipping_address_id),
updated_at = NOW();

-- Order items
INSERT INTO order_items (id, order_id, product_id, user_id, quantity, price, created_at, updated_at) VALUES
(9901, 9801, 9501, 9303, 1, 320000.00, NOW(), NOW()),
(9902, 9801, 9503, 9303, 1, 99000.00, NOW(), NOW()),
(9903, 9802, 9502, 9304, 1, 185000.00, NOW(), NOW()),
(9904, 9802, 9504, 9304, 1, 79000.00, NOW(), NOW())
ON DUPLICATE KEY UPDATE
order_id = VALUES(order_id),
product_id = VALUES(product_id),
user_id = VALUES(user_id),
quantity = VALUES(quantity),
price = VALUES(price),
updated_at = NOW();

-- Order status history
INSERT INTO order_status_history (id, order_id, status, note, changed_at, created_at, updated_at) VALUES
(9911, 9801, 'processing', 'Order is being prepared', NOW(), NOW(), NOW()),
(9912, 9802, 'pendung', 'Order created and waiting for confirmation', NOW(), NOW(), NOW())
ON DUPLICATE KEY UPDATE
order_id = VALUES(order_id),
status = VALUES(status),
note = VALUES(note),
changed_at = VALUES(changed_at),
updated_at = NOW();

-- Payments (column name follows your schema: paid_ay)
INSERT INTO payments (id, order_id, payment_method, transaction_id, status, paid_ay, amount, created_at, updated_at) VALUES
(9921, 9801, 'cash', NULL, 'pending', NULL, 419000.00, NOW(), NOW()),
(9922, 9802, 'paypal', 'TXN-STTC-9802', 'completed', NOW(), 264000.00, NOW(), NOW())
ON DUPLICATE KEY UPDATE
order_id = VALUES(order_id),
payment_method = VALUES(payment_method),
transaction_id = VALUES(transaction_id),
status = VALUES(status),
paid_ay = VALUES(paid_ay),
amount = VALUES(amount),
updated_at = NOW();

-- Wishlists
INSERT INTO wishlists (id, user_id, product_id, created_at, updated_at) VALUES
(9931, 9303, 9502, NOW(), NOW()),
(9932, 9304, 9501, NOW(), NOW())
ON DUPLICATE KEY UPDATE
user_id = VALUES(user_id),
product_id = VALUES(product_id),
updated_at = NOW();

-- Reviews
INSERT INTO reviews (id, user_id, product_id, rating, comment, created_at, updated_at) VALUES
(9941, 9303, 9501, 5, 'Very good quality, dog likes it', NOW(), NOW()),
(9942, 9304, 9502, 4, 'Cat eats well, reasonable price', NOW(), NOW())
ON DUPLICATE KEY UPDATE
user_id = VALUES(user_id),
product_id = VALUES(product_id),
rating = VALUES(rating),
comment = VALUES(comment),
updated_at = NOW();

-- Cart items
INSERT INTO cart_items (id, user_id, product_id, quantity, created_at, updated_at) VALUES
(9951, 9303, 9504, 2, NOW(), NOW()),
(9952, 9304, 9503, 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE
user_id = VALUES(user_id),
product_id = VALUES(product_id),
quantity = VALUES(quantity),
updated_at = NOW();

-- Notifications
INSERT INTO notifications (id, user_id, type, message, link, is_read, created_at, updated_at) VALUES
(9961, 9303, 'order', 'Your order #9801 is processing', '/orders/9801', 0, NOW(), NOW()),
(9962, 9304, 'promo', 'Weekend discount up to 20%', '/promotions/weekend', 0, NOW(), NOW())
ON DUPLICATE KEY UPDATE
user_id = VALUES(user_id),
type = VALUES(type),
message = VALUES(message),
link = VALUES(link),
is_read = VALUES(is_read),
updated_at = NOW();

-- Contacts
INSERT INTO contacts (id, full_name, phone_number, email, message, is_replied, created_at, updated_at) VALUES
(9971, 'Pham Van C', '0900000010', 'contact1@example.com', 'I need support for my recent order', '0', NOW(), NOW()),
(9972, 'Hoang Thi D', '0900000011', 'contact2@example.com', 'Please advise product for small dog', '1', NOW(), NOW())
ON DUPLICATE KEY UPDATE
full_name = VALUES(full_name),
phone_number = VALUES(phone_number),
email = VALUES(email),
message = VALUES(message),
is_replied = VALUES(is_replied),
updated_at = NOW();

-- Password reset tokens
INSERT INTO password_reset_tokens (email, token, created_at) VALUES
('customer1@sttc.local', 'RESET-TOKEN-9303', NOW()),
('customer2@sttc.local', 'RESET-TOKEN-9304', NOW())
ON DUPLICATE KEY UPDATE
token = VALUES(token),
created_at = VALUES(created_at);

COMMIT;
