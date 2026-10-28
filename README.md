# Car Garage Management System

Hệ thống quản lý Gara Ô tô – Bài thực hành Lab 07: Software Implementation & Version Control.

## 1. Giới thiệu dự án

Dự án xây dựng bộ khung ban đầu cho hệ thống quản lý gara ô tô, hỗ trợ định hướng phát triển các chức năng quản lý đăng nhập, khách hàng, xe, sửa chữa, kho phụ tùng và hóa đơn.

## 2. Công nghệ sử dụng

- PHP: xử lý phía máy chủ.
- HTML/CSS: xây dựng giao diện.
- MySQL: hệ quản trị cơ sở dữ liệu dự kiến.
- Git và GitHub: quản lý mã nguồn và cộng tác nhóm.

## 3. Cấu trúc dự án

```text
public/
  assets/
  index.php
app/
  Controllers/
  Services/
  Repositories/
  Entities/
  Views/
config/
database/
  migrations/
  seeders/
storage/
docs/
.gitignore
README.md
```

## 4. Các phần đã triển khai

- Khởi tạo repository và bộ khung thư mục.
- Tạo tài liệu kiến trúc hệ thống.
- Tạo giao diện đăng nhập ban đầu.
- Tạo giao diện khung quản lý sửa chữa và kho.
- Tạo khung AuthController và AuthService.

Lưu ý: Các chức năng hiện mới ở mức khung ban đầu, chưa hoàn thiện xác thực tài khoản và xử lý dữ liệu thực tế.

## 5. Quy trình Git Flow

- `main`: lưu phiên bản chính.
- `develop`: nhánh tích hợp các chức năng.
- `feature/*`: nhánh phát triển từng chức năng.

Quy trình làm việc:

1. Tạo nhánh feature từ develop.
2. Chỉnh sửa mã nguồn và commit.
3. Push nhánh feature lên GitHub.
4. Tạo Pull Request vào develop.
5. Kiểm tra thay đổi và merge sau khi được chấp thuận.

## 6. Thành viên nhóm

- Chu Tuấn Vũ – MSSV: 52400331.
- Nguyễn Trung Kiên – MSSV: 52400284.
- Nguyễn Thái An – MSSV: 52400064.

## 7. Hướng phát triển tiếp theo

- Hoàn thiện xác thực và phân quyền.
- Kết nối cơ sở dữ liệu.
- Phát triển quản lý khách hàng, xe, sửa chữa, kho và hóa đơn.
- Kiểm thử và hoàn thiện giao diện.