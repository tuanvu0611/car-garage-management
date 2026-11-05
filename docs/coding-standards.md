# Quy chuẩn lập trình PHP

Áp dụng cho mã mới và phần mã được sửa; không định dạng lại toàn dự án trong PR không liên quan.

## Đặt tên

- Class và tên file class dùng PascalCase, ví dụ `GarageService.php`.
- Hàm, phương thức và biến dùng camelCase; tên phải diễn tả nghiệp vụ.
- Hằng số dùng UPPER_SNAKE_CASE. Bảng, cột SQL và khóa biểu mẫu giữ snake_case theo schema hiện tại.
- Test dùng hậu tố `Test.php`; phương thức `test...` mô tả hành vi và kết quả mong đợi.

## Định dạng

- PHP 8.1+, `declare(strict_types=1);`, UTF-8 không BOM, xuống dòng LF, thụt 4 dấu cách, không tab.
- Theo PSR-12: ngoặc mở class/phương thức ở dòng mới, ngoặc khối điều khiển cùng dòng; luôn dùng ngoặc cho if/foreach.
- Khai báo kiểu tham số và kết quả khi phù hợp; một câu lệnh mỗi dòng; không đóng `?>` trong file chỉ có PHP.
- Giữ controller mỏng, validation/nghiệp vụ ở service, SQL ở repository, hiển thị ở view.

## Ngoại lệ

- Dữ liệu nghiệp vụ không hợp lệ ném `DomainException`; lỗi hạ tầng giữ đúng loại ngoại lệ.
- Chỉ bắt lỗi khi có thể xử lý, chuyển thành thông báo phù hợp hoặc rollback transaction rồi ném lại.
- Không nuốt lỗi hoặc coi mọi `Throwable` là lỗi nhập liệu. Không hiển thị stack trace, SQL hoặc bí mật cho người dùng.
- Test phải kiểm tra loại ngoại lệ cụ thể bằng `expectException`; kiểm tra trạng thái không đổi sau thất bại khi cần.

## SQL có tham số

- Dùng PDO `prepare`/`execute` hoặc `GarageRepository::query($sql, $parameters)` cho dữ liệu đầu vào.
- Không nối dữ liệu người dùng vào SQL. Placeholder chỉ thay giá trị; tên bảng/cột và hướng sắp xếp động phải qua allowlist cố định.
- Thao tác nhiều bước cần transaction; commit khi thành công, rollback khi thất bại.

```php
$repository->query('SELECT * FROM parts WHERE id = ?', [$partId]);
```

## Escape khi hiển thị

- Lưu dữ liệu gốc đã validation; escape tại điểm xuất HTML bằng `e($value)` cho nội dung và thuộc tính có dấu nháy.
- Không echo trực tiếp POST, session hoặc dữ liệu database. Tránh escape hai lần.
- Với URL, kiểm tra giao thức/đích hợp lệ, mã hóa từng thành phần rồi escape thuộc tính HTML.
- Không chèn dữ liệu vào JavaScript/CSS trực tiếp; HTML escaping không thay thế encoding theo ngữ cảnh.

## Kiểm thử và review

- Mỗi test tự tạo dữ liệu; SQLite in-memory mới cho mỗi test nhập kho, không dùng database thật.
- Đặt lại session, POST và request method trong setUp; PHPUnit backupGlobals khôi phục global sau mỗi test.
- Provider trả dữ liệu thuần, có nhãn dễ đọc. Chạy thêm thứ tự ngẫu nhiên để phát hiện phụ thuộc.
- Coverage chỉ tính `app/`, gồm cả file chưa chạy; không tính `vendor/`, `tests/` và công cụ.
- Coverage thấp phải được giải thích bằng phạm vi kiểm thử; không bỏ file ứng dụng chỉ để nâng tỷ lệ.
- Test SQLite không thay thế kiểm thử tích hợp MySQL, transaction/khóa hàng và kiểm thử HTTP/CSRF.
