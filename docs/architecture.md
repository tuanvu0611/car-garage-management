# Kiến trúc hệ thống quản lý Gara Ô tô

## 1. Kiến trúc tổng thể

Hệ thống được thiết kế theo mô hình 3 lớp:

- **Presentation Layer:** Giao diện người dùng, hiển thị dữ liệu và nhận thao tác.
- **Business Logic Layer:** Xử lý nghiệp vụ tiếp nhận xe, sửa chữa, quản lý kho và thanh toán.
- **Data Access Layer:** Truy xuất và lưu trữ dữ liệu trong cơ sở dữ liệu MySQL thông qua Repository.

## 2. Cấu trúc mã nguồn

- `public/`: Điểm vào ứng dụng và tài nguyên giao diện.
- `app/Controllers/`: Tiếp nhận yêu cầu từ người dùng.
- `app/Services/`: Xử lý nghiệp vụ.
- `app/Repositories/`: Truy xuất dữ liệu.
- `app/Entities/`: Các đối tượng dữ liệu.
- `app/Views/`: Giao diện hiển thị.
- `config/`: Cấu hình hệ thống.
- `database/`: Các tệp liên quan đến cơ sở dữ liệu.
- `storage/`: Lưu trữ log và dữ liệu phục vụ vận hành.

## 3. Luồng xử lý

Người dùng gửi yêu cầu qua giao diện. Controller tiếp nhận yêu cầu, Service xử lý nghiệp vụ, Repository truy xuất dữ liệu và trả kết quả về giao diện.
