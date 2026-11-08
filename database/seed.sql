-- Optional sample data for a NEW demo database. No default login credentials.
START TRANSACTION;
INSERT INTO customers(full_name,phone) VALUES ('Nguyễn Văn Minh','0901234567');
SET @customer_id = LAST_INSERT_ID();
INSERT INTO vehicles(customer_id,plate,model) VALUES (@customer_id,'51A-12345','Toyota Vios');
INSERT INTO parts(name,quantity,price) VALUES ('Lọc dầu',10,120000),('Dầu động cơ 1L',20,150000);
COMMIT;
