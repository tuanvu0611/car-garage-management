# Car Garage Management System

Hệ thống quản lý Gara Ô tô — Lab 07: Software Implementation & Version Control.

## Chức năng bản demo

- Đăng nhập bằng mật khẩu băm, session, đăng xuất bằng POST và bảo vệ CSRF.
- Thêm và xem khách hàng, xe, phiếu sửa chữa và phụ tùng.
- Xuất phụ tùng cho phiếu đang mở, kiểm tra tồn kho trong transaction.
- Nhập bổ sung tồn kho cho phụ tùng đã có; giữ nguyên đơn giá.
- Giữ lại dữ liệu đúng biểu mẫu khi nhập sai, không cần điền lại từ đầu.
- Hoàn tất phiếu; lập một hóa đơn cho mỗi phiếu, tổng tiền tính từ tiền công và phụ tùng.
- Kiểm tra quyền ở máy chủ; xử lý trang 403/404 và thông báo lỗi dữ liệu.

## Yêu cầu và cài đặt

PHP **8.1 trở lên** với PDO MySQL; MySQL 8 hoặc MariaDB 10.4 trở lên. Không cần Composer.
Thực hiện các lệnh tại thư mục chứa README này.

1. Khởi động MySQL (ví dụ trong XAMPP).
2. Tạo database bằng phpMyAdmin hoặc MySQL client:
   `CREATE DATABASE car_garage CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;`
3. Import `database/create_table.sql` vào database đó. Tùy chọn import `database/seed.sql` một lần vào database demo mới.
4. Sao chép cấu hình và điền tài khoản MySQL:

   ```powershell
   Copy-Item .env.example .env
   ```

5. Tạo tài khoản bằng CLI; mật khẩu không được ghi vào repository:

   ```powershell
   $env:GARAGE_USER_PASSWORD = 'ThayBangMatKhauRieng123!'
   php database/create-user.php admin "Quản trị viên" admin
   Remove-Item Env:GARAGE_USER_PASSWORD
   ```

   Đổi tham số cuối thành `manager`, `technician` hoặc `accountant` để tạo tài khoản tương ứng.
6. Chạy:

   ```powershell
   php -S 127.0.0.1:8000 -t public
   ```

   Mở http://127.0.0.1:8000 và đăng nhập bằng tài khoản vừa tạo.

Nếu PHP chưa có trong PATH, dùng `& C:\xampp\php\php.exe` thay cho `php` trong PowerShell.
Nếu port 8000 đã dùng, đổi sang 8001. Lỗi kết nối: kiểm tra MySQL, extension `pdo_mysql`, file `.env` và schema đã import.

## Quy trình demo

Đăng nhập quản trị → thêm khách hàng → thêm xe → tạo phiếu (tiền công 200000) → thêm phụ tùng (10 chiếc, 120000/chiếc) → xuất 2 chiếc cho phiếu → hoàn tất → lập hóa đơn.
Kết quả: tồn kho 8, hóa đơn 440000 VND. Dữ liệu mẫu đã có khách hàng, xe và phụ tùng để dùng ngay.

## Phân quyền

| Vai trò | Khách hàng / xe | Sửa chữa | Kho | Hóa đơn |
| --- | --- | --- | --- | --- |
| admin / manager | Có | Có | Có | Có |
| technician | Có | Có | Không | Không |
| accountant | Có | Không | Không | Có |

Kỹ thuật viên được xuất phụ tùng trong phiếu sửa chữa; không được thêm danh mục phụ tùng.
Năm lần đăng nhập thất bại sẽ tạm chặn 60 giây trong session hiện tại.

## Cấu trúc

- `public/index.php`: định tuyến, kiểm tra session/CSRF/quyền; document root.
- `public/assets/`: CSS.
- `app/Controllers/`: xác thực.
- `app/Services/`: nghiệp vụ và kiểm tra dữ liệu.
- `app/Repositories/`: truy vấn PDO có tham số.
- `app/Views/`: layout, form và danh sách.
- `config/bootstrap.php`: nạp cấu hình, autoload, session.
- `database/`: schema, dữ liệu mẫu và CLI tạo tài khoản.
- `tests/`: kiểm tra dữ liệu và quyền.
- `docs/`: kiến trúc, ERD, giao diện, Git Flow và checklist.

## Kiểm tra

```powershell
php tests/validation.php
php tests/restock.php
Get-ChildItem -Recurse -Filter *.php | ForEach-Object { php -l $_.FullName }
```

Kiểm tra tích hợp theo [checklist](docs/testing.md), cần MySQL và ứng dụng đang chạy.

## Phạm vi và giới hạn

Đây là bản demo nghiệp vụ: thêm/xem dữ liệu, nhập bổ sung tồn kho, xuất kho, hoàn tất và lập hóa đơn. Chưa có sửa/xóa danh mục, thanh toán/công nợ, VAT, in hóa đơn, phân trang, quản trị tài khoản qua UI hoặc tự động migration.
Hóa đơn chưa thể hiện trạng thái đã thanh toán. Không sửa phiếu sau hoàn tất.
Máy chủ PHP tích hợp dùng cho phát triển. Khi triển khai, trỏ document root vào `public/`, dùng HTTPS, đặt `SESSION_SECURE=1`, tắt hiển thị lỗi và cấu hình log/backup. Bộ hạn chế đăng nhập theo session cần thay bằng giới hạn theo tài khoản/IP trước khi đưa lên Internet.

## Git Flow

`feature/*` tạo từ `develop` → Pull Request → review → merge `develop` → kiểm tra → phát hành vào `main`.
Xem [quy trình chi tiết](docs/git-flow.md). Minh chứng commit/PR lấy từ lịch sử Git/GitHub thực tế.

## Thành viên

- Chu Tuấn Vũ — 52400331.
- Nguyễn Trung Kiên — 52400284.
- Nguyễn Thái An — 52400064.
