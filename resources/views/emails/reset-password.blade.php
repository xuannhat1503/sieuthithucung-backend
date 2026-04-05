<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu</title>
</head>
<body style="margin:0;padding:0;background:#f4f7ff;font-family:'Be Vietnam Pro','Segoe UI',Arial,sans-serif;color:#17324f;">
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background:#f4f7ff;margin:0;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="max-width:680px;background:#ffffff;border-radius:28px;overflow:hidden;box-shadow:0 20px 50px rgba(33,58,110,0.12);">
                    <tr>
                        <td style="background:linear-gradient(135deg,#f08a32 0%,#f0b14f 100%);padding:18px 32px;text-align:center;">
                            <div style="display:inline-block;padding:10px 18px;border-radius:999px;background:rgba(255,255,255,0.16);color:#ffffff;font-size:13px;font-weight:800;letter-spacing:0.08em;text-transform:uppercase;">
                                PETSAIGON
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:42px 42px 20px;">
                            <h1 style="margin:0 0 18px;font-size:38px;line-height:1.1;font-weight:800;letter-spacing:-0.03em;color:#102b33;">
                                Đặt lại mật khẩu
                            </h1>
                            <p style="margin:0 0 14px;font-size:16px;line-height:1.9;color:#47617d;">
                                Xin chào <strong>{{ $user->name }}</strong>,
                            </p>
                            <p style="margin:0 0 14px;font-size:16px;line-height:1.9;color:#47617d;">
                                Chúng tôi đã nhận được yêu cầu đặt lại mật khẩu cho tài khoản PETSAIGON của bạn.
                                Vui lòng nhấn vào nút bên dưới để chuyển đến trang đặt lại mật khẩu.
                            </p>
                            <p style="margin:0 0 32px;font-size:16px;line-height:1.9;color:#47617d;">
                                Liên kết này sẽ hết hạn sau 60 phút để đảm bảo an toàn cho tài khoản của bạn.
                            </p>
                            <div style="text-align:center;margin:0 0 34px;">
                                <a href="{{ $resetUrl }}" style="display:inline-block;min-width:240px;padding:17px 28px;border-radius:18px;background:linear-gradient(135deg,#f08a32 0%,#f0b14f 100%);color:#ffffff;text-decoration:none;font-size:15px;font-weight:800;letter-spacing:-0.01em;text-transform:uppercase;box-shadow:0 16px 34px rgba(240,138,50,0.24);">
                                    Đặt lại mật khẩu
                                </a>
                            </div>
                            <div style="padding:20px 22px;border-radius:18px;background:#fff8ef;border:1px solid #f7dfbf;">
                                <p style="margin:0 0 10px;font-size:14px;line-height:1.8;color:#5d7390;">
                                    Nếu nút không hoạt động, bạn có thể sao chép và dán liên kết sau vào trình duyệt của mình:
                                </p>
                                <p style="margin:0;word-break:break-all;font-size:14px;line-height:1.8;color:#35527a;">
                                    {{ $resetUrl }}
                                </p>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 42px 42px;">
                            <p style="margin:0 0 10px;font-size:15px;line-height:1.8;color:#47617d;">
                               Nếu bạn không yêu cầu đặt lại mật khẩu, bạn có thể bỏ qua email này.
                            </p>
                            <p style="margin:0;font-size:15px;line-height:1.8;font-weight:800;color:#102b33;">Doi ngu PETSAIGON</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
