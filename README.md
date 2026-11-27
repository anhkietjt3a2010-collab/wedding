# Xuân Hiến & Trúc Ly — Wedding PHP/MySQL demo

## Yêu cầu
PHP 8.1+ với PDO MySQL, fileinfo, session; MySQL 5.7+ hoặc MariaDB hỗ trợ JSON; Apache 2.4 hoặc Nginx. HTTPS khi đưa lên mạng.

## Xem thử giao diện (không cần DB)
`php -S 127.0.0.1:8080` trong thư mục dự án rồi mở http://127.0.0.1:8080. Chỉ là chế độ đọc, RSVP và lời chúc bị vô hiệu hóa. Không dùng PHP built-in server trên Internet.

## Cài đặt hosting
1. Tạo database và user MySQL riêng, cấp quyền trên database này.
2. Tải thư mục dự án lên hosting (ví dụ public_html/wedding).
3. Sao chép config.example.php thành config.php, sửa DSN, tên tài khoản, mật khẩu và setup_key ngẫu nhiên ít nhất 24 ký tự. Không commit config.php.
4. Mở /setup.php qua HTTPS, nhập setup_key và tạo tài khoản quản trị riêng (mật khẩu ít nhất 12 ký tự). Trình cài đặt tạo bảng tự động từ schema.sql. Không có mật khẩu mặc định.
5. Xóa setup.php sau khi cài đặt. Mở /admin.php để đăng nhập và cập nhật website. Thư mục uploads cần quyền ghi cho PHP, không cấp 777.
6. Nếu dùng Nginx, cấu hình chặn truy cập trực tiếp config*, schema.sql, README.md, CODEX.md, defaults.php, bootstrap.php; không thực thi PHP trong uploads; tắt directory listing. Với Apache, bật AllowOverride tương ứng cho .htaccess. Nên để cấu hình bí mật bên ngoài webroot khi triển khai thật.
7. Sao lưu DB + uploads, bật HTTPS, thêm rate limiting tại reverse proxy/WAF cho login và submit, kiểm tra quyền truy cập và chính sách dữ liệu trước khi gửi link công khai.

## Dữ liệu local và bí mật
- Tạo `config.php` riêng từ `config.example.php`; tệp cấu hình local đã được Git bỏ qua.
- Tạo tài khoản quản trị qua `/setup.php`, dùng mật khẩu riêng và lưu ngoài source code. Không có mật khẩu quản trị mặc định trong repo.
- Nội dung cá nhân, địa chỉ và tài khoản mừng cưới lưu trong `settings` của MySQL, không ghi vào README hay tệp mặc định.
- Modal “Mừng cưới” đọc tài khoản từ settings; QR tùy chỉnh lấy từ thư viện ảnh, nếu chưa có thì VietinBank dùng VietQR Quick Link với BIN 970415. QR cần Internet và không gắn số tiền cố định.

## Phạm vi
Website một cặp đôi, slider lớn, các section riêng theo chiều dọc, mở thiệp, đếm ngược, gallery lightbox, bản đồ liên kết, RSVP, lời chúc duyệt trước, nhạc tùy chọn, quản trị thông tin và tải ảnh. Dữ liệu nội dung lưu MySQL JSON; RSVP và lời chúc lưu bảng riêng. Chưa có SaaS đa khách hàng, mã mời riêng, video, thanh toán hoặc builder kéo thả.

Ảnh demo cắt từ mockup AI, không phải ảnh thật của cặp đôi. Thay ảnh bằng ảnh có quyền sử dụng trước khi công khai. Theo xác nhận của gia đình, tiệc nhà gái tổ chức lúc 17:00 ngày 12/12/2026. Trang iWedding hiện hiển thị ngày khác nên cần đồng bộ nguồn đó trước khi gửi thiệp. Không bịa mốc tình yêu hoặc thông tin tài khoản ngân hàng. Google Fonts cần kết nối Internet; Uicons có CDN và fallback local, nhưng vẫn phải tuân thủ giấy phép/attribution. Animation dùng CSS/JS thuần để nhẹ, không tích hợp Motion/GSAP. UI/UX Pro Max Skill đã được cài cục bộ trong `.agents/skills/ui-ux-pro-max/`.

## Kiểm thử
Kiểm tra login/logout, đổi mật khẩu, lưu nội dung, upload, RSVP, duyệt/ẩn lời chúc, responsive, CDN và timezone trên hosting có MySQL. Schema đã được smoke-test trên MariaDB 10.4 cục bộ; các luồng đăng nhập/upload/submit vẫn cần kiểm thử end-to-end sau khi cấu hình `config.php` riêng cho môi trường triển khai.

## Ghi chú giao diện và tài nguyên (audit 27/11/2026)
- Hệ thiết kế giữ Romantic Pink + Calligraphy, hero 88dvh và các section tách theo trục dọc. Font ưu tiên Great Vibes, Cormorant Garamond và Be Vietnam Pro; fallback hệ thống luôn được khai báo.
- Flaticon Uicons Rounded Regular đang dùng CDN 4.0.0. Bản CSS và webfont 4.0.0 được lưu tại `assets/vendor/uicons/` để dự phòng khi CDN lỗi. Theo giấy phép Flaticon miễn phí, attribution “Uicons by Flaticon” được đặt ở footer; gói Premium có thể bỏ attribution theo điều khoản hiện hành.
- Không thêm GSAP: hiệu ứng hiện tại chỉ dùng transform/opacity và IntersectionObserver, nhẹ hơn cho Zalo WebView và thiết bị di động. Carousel có nút dừng, tự dừng khi focus/hover và tôn trọng `prefers-reduced-motion`.
- Ảnh trong `assets/` vẫn là ảnh minh họa, cần thay bằng ảnh thật có quyền sử dụng. Ngày tiệc nhà gái theo xác nhận của gia đình là 12/12/2026 lúc 17:00.
- Admin phân trang 25 dòng cho RSVP/lời chúc. Submit có CSRF, honeypot và giới hạn tần suất theo fingerprint phía server/DB. Upload xác minh MIME/kích thước, đổi tên ngẫu nhiên và thư mục `uploads/` chặn thực thi script qua Apache.
