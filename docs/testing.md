# Checklist kiểm thử

## Kết quả kiểm tra ngày 28/10/2026

- PHP 8.2.12 (XAMPP): toàn bộ file PHP qua kiểm tra cú pháp.
- `php tests/validation.php`: PASS (validation, ma trận quyền, escape HTML).
- HTTP local: PASS cho trang login 200, chuyển hướng trang bảo vệ, trang lạ 404 và POST sai CSRF 403.
- Chưa chạy kiểm thử tích hợp dữ liệu: MySQL tại 127.0.0.1 chưa hoạt động. Các bước bên dưới cần được chạy sau khi khởi động MySQL và import schema.

## Kiểm tra tích hợp

Kiểm tra tự động bổ sung: `php tests/restock.php` dùng SQLite in-memory, không sửa database thật. Kiểm tra nhập thêm số lượng, giữ giá, từ chối dữ liệu sai/vượt giới hạn/phụ tùng không tồn tại và phục hồi form an toàn. Vẫn cần kiểm tra đồng thời bằng MySQL.

- [ ] Kho đang có 8, nhập bổ sung 5: tồn thành 13, đơn giá không đổi.
- [ ] Nhập bổ sung 0, số âm, số lẻ hoặc vượt giới hạn: từ chối, tồn không đổi.
- [ ] Gửi lỗi ở form nhập kho: giữ lựa chọn và số lượng ở form đó; form thêm mới không bị điền nhầm.

Chạy php tests/validation.php trước. Các bước dưới đây cần MySQL đã import schema và các tài khoản có vai trò tương ứng.

- [ ] Chưa đăng nhập: mở ?page=repairs chuyển sang login.
- [ ] Đăng nhập sai: báo lỗi chung; đúng: về tổng quan; đăng xuất: không vào lại trang bảo vệ.
- [ ] Sai 5 lần: chờ 60 giây trong cùng session.
- [ ] Sửa hoặc xóa token CSRF trong POST: không ghi dữ liệu.
- [ ] Technician mở ?page=inventory hoặc invoices trực tiếp: 403; POST cũng không ghi dữ liệu.
- [ ] Accountant mở repairs: 403.
- [ ] ?page=unknown: 404.
- [ ] Thêm khách hàng và xe; biển số trùng hoặc customer_id không tồn tại: báo lỗi.
- [ ] Tạo phiếu tiền công 200000; phụ tùng tồn 10, giá 120000; dùng 2: còn 8.
- [ ] Dùng 9 khi còn 8: từ chối; tồn và chi tiết phiếu không đổi.
- [ ] Gửi số âm, số lượng lẻ, mảng thay chuỗi: báo lỗi, không ghi.
- [ ] Hoàn tất phiếu: không được xuất thêm phụ tùng.
- [ ] Lập hóa đơn phiếu đang mở: từ chối.
- [ ] Lập hóa đơn phiếu hoàn tất ở ví dụ trên: 440000; lập lần hai: từ chối.
- [ ] Hai phiên đồng thời xuất vượt tổng tồn: không âm kho.
- [ ] Nhập nội dung HTML vào mô tả: danh sách hiển thị dạng chữ, không chạy script.
- [ ] Refresh sau khi lưu: không nhân đôi dữ liệu.
- [ ] MySQL dừng: báo lỗi chung; lỗi chi tiết nằm trong PHP error log.
- [ ] Kiểm tra trên màn hình điện thoại: form sử dụng được, bảng cuộn ngang.

Ghi ngày chạy, phiên bản PHP/MySQL, người kiểm tra và kết quả thực tế vào báo cáo Lab. Checklist này không phải bằng chứng các bước đã được chạy.
