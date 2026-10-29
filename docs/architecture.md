# Kiến trúc hệ thống

Ứng dụng PHP 8.1+ với ba lớp:

- Presentation: public/index.php và Views; định tuyến theo danh sách cho phép, layout và form.
- Business: AuthService / GarageService; xác thực, phân quyền, validation, xuất kho và lập hóa đơn.
- Data access: GarageRepository; PDO prepared statements và transaction MySQL.

Luồng: request → bootstrap/session → router → kiểm tra CSRF và quyền → service → repository → MySQL → view hoặc redirect.
Chỉ public/ được mở bởi web server. .env và công cụ tạo tài khoản nằm ngoài document root.
Lỗi PDO được ghi vào error log máy chủ; người dùng nhận thông báo chung, không lộ thông tin kết nối.
Không dùng framework hoặc thư viện bên ngoài. Phân quyền hiện áp dụng theo module, không theo từng bản ghi.
