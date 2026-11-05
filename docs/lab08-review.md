# Rà soát nội bộ Lab08

Đây là tự rà soát kỹ thuật, không phải review đã được Vũ đăng trên GitHub.
Không tạo lỗi để đủ số nhận xét và không dùng danh tính người khác.

Ba điểm thiếu được phát hiện trong commit thiết lập test ban đầu và đã bổ sung:

1. `tests/ValidationTest.php`, test escape: chỉ kiểm tra thẻ script chưa chứng minh an toàn dấu nháy trong thuộc tính HTML. Thêm test dấu nháy kép, nháy đơn, ampersand và dấu góc.
2. `tests/RestockTest.php`, kiểm tra giới hạn: có test vượt trần nhưng chưa có giá trị sát biên hợp lệ. Thêm nhập đúng tổng tồn 999999999 và validation chấp nhận đúng trần, giữ nguyên giá.
3. `tests/ValidationTest.php`, phân quyền: đã kiểm tra vai trò lạ nhưng chưa kiểm tra trang lạ với admin. Thêm test đảm bảo trang không nằm trong allowlist vẫn bị từ chối.

Vũ cần dùng tài khoản của mình để đọc Files changed và đăng các nhận xét thực tế nếu còn vấn đề.
Các điểm trên đã sửa nên không đăng lại như lỗi còn tồn tại hoặc yêu cầu sửa giả.
Review Request changes chỉ phù hợp khi vẫn còn vấn đề cần sửa.
