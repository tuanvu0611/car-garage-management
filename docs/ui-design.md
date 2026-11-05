# Thiết kế giao diện

Layout dùng chung: tên gara, tên người dùng/vai trò, đăng xuất; thanh điều hướng hiển thị chức năng được phép; tiêu đề và nội dung.
CSS responsive, bảng có cuộn ngang, label gắn bằng phần tử bao input, thông báo lỗi role=alert.

| Màn hình | Thành phần |
| --- | --- |
| Đăng nhập | Username, password, lỗi phía trên form |
| Tổng quan | Hướng dẫn luồng demo |
| Khách hàng | Thêm họ tên/điện thoại; danh sách |
| Xe | Chọn khách hàng, biển số, dòng xe; danh sách |
| Sửa chữa | Tạo phiếu; dùng phụ tùng; hoàn tất; danh sách |
| Kho | Tạo phụ tùng với tồn ban đầu và giá; nhập bổ sung số lượng; danh sách tồn |
| Hóa đơn | Chọn phiếu đã hoàn tất để lập; danh sách tổng tiền |
| Lỗi | 403 khi thiếu quyền, 404 khi URL không tồn tại |

Form sử dụng POST và CSRF; thành công chuyển hướng về GET để tránh gửi lại khi refresh.
Dropdown có thể hiển thị phiếu không còn phù hợp; máy chủ kiểm tra trạng thái trước khi lưu.
Form giữ lại dữ liệu và lựa chọn của đúng thao tác vừa gửi khi có lỗi; giá trị được escape trước khi hiển thị. Chưa có giao diện sửa/xóa hoặc chi tiết hóa đơn.
