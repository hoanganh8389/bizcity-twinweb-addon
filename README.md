# BizCity TwinWeb — Trợ lý `/gpt/`

<!-- [2026-10-12 12:11 PM Johnny Chu - Chu Hoàng Anh] PHASE-0.102 S102-L20 — README của add-on bizcity-twinweb-addon -->

Trang **`/gpt/`** kiểu ChatGPT cho thành viên của site: trò chuyện với trợ lý, quản lý số Zalo của tôi, CRM/việc được giao, nội dung đã tạo. **Add-on của [Zalo Brain](https://github.com/hoanganh8389/zalo-brain)** — không có nó, Zalo Brain vẫn đầy đủ (Ask Brain, cài Zalo, Guru, CRM) và Channel Gateway là trang chủ.

**Tác giả:** Biz Central Brain — Johnny Chu (Chu Hoàng Anh).

## Cài

1. Cài và **kích hoạt Zalo Brain** (thư mục tên gì cũng được — `bizcity-twin-ai/`, `zalo-brain-main/`…).
2. Tải thư mục này vào `wp-content/plugins/` (tên thư mục cũng không quan trọng) và **kích hoạt**.
3. Mở `/gpt/`. Nếu kích hoạt trước khi bật Zalo Brain, WordPress sẽ từ chối và nói rõ lý do.

Không cần multisite (`Network: false`); site đơn là đủ.

## Cách nó được nạp

File chính chỉ khai báo và chặn kích hoạt khi thiếu Zalo Brain; **không tự nạp gì**. Zalo Brain tìm thư mục này bằng **tên file `bizcity-twinweb-addon.php` + dòng `Zalo Brain Addon: twinweb`** ở mọi thư mục plugin (`BizCity_Addon_Locator`), rồi `require` `bootstrap.php` đúng chỗ bản đóng gói cũ từng nạp. Chế độ nạp đặt trong `wp-config.php`:

```php
define( 'BIZCITY_TWINWEB_LOAD', 'active' ); // mặc định: chỉ nạp khi plugin đang được kích hoạt
define( 'BIZCITY_TWINWEB_LOAD', 'auto' );   // nạp khi thư mục có mặt, kể cả chưa kích hoạt
define( 'BIZCITY_TWINWEB_LOAD', 'off' );    // không bao giờ (site không có /gpt/)
```

## Của ai cái gì

| | Ở add-on này | Ở Zalo Brain (`core/twinweb-axis/`) |
|---|---|---|
| Trang | `/gpt/` + 17 trang con, shortcode `[bizcity_twin]`, trang WordPress `gpt` | thẻ giữ chỗ khi thiếu add-on (không bao giờ in chữ thô shortcode) |
| REST `bizcity-twinweb/v1` | `projects*`, `my-files*`, `crm/tasks*`, `crm/pipeline*`, `admin/profile-templates*`, `me/profile-*` | controller chính (≈100 route): chat, `mychannels/*` (cài Zalo thành viên), `me/agent-gurus`, `admin/guru-workspaces*`, `artifacts/jobs/*`, `threads*`, `skills`… |
| Dữ liệu | — | bảng `bizcity_twinweb_threads`, `bizcity_twinweb_artifact_jobs`; option `bizcity_twinweb_*` |
| Giao diện | `ui/dist` (một bundle) | — |

Gỡ add-on **không mất dữ liệu**; cài lại là chạy tiếp. Xoá hẳn chỉ làm gì khi `wp-config.php` có `BIZCITY_TWINWEB_UNINSTALL_DROP` (khi đó chỉ quên id trang `gpt`).

## Thiếu add-on thì người dùng thấy gì

Icon Trợ lý trên ActivityBar **ẩn hẳn**; Channel Gateway ("Cấu hình Zalo") là trang chủ; vào `/gpt/` khi đã đăng nhập thì được chuyển về Twin Shell; khách thấy một thẻ ngắn; tab Twin GPT trong Channel Gateway và menu admin "Twin GPT · Mở /gpt/" không hiện.

## Phát triển

`ui/` là Vite + React (nguồn `ui/src` chỉ ở máy dev, **không** lên repo). Build: `cd ui && npm run build`. Gói `zalo-connection-ui` của Zalo Brain được tìm qua alias Vite `@bizcity/zalo-connection-ui` (biến môi trường `BIZCITY_TWIN_AI_DIR`, hoặc thư mục anh em `bizcity-twin-ai`/`zalo-brain`/`zalo-brain-main`).

Tài liệu thiết kế: `bizcity-twin-ai/core/channel-gateway/docs/PHASE-0.102-TWINWEB-ADDON-SPLIT/`. Chuẩn add-on: `…/standards/ADDON-HOST-STANDARD.md`.
