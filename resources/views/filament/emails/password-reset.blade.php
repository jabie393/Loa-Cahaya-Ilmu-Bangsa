<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atur Ulang Kata Sandi - LOA Cahaya Ilmu Bangsa</title>
</head>
<body style="margin: 0; padding: 20px 0; background-color: #f3f4f6; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #374151;">

    <!-- Main Container -->
    <div style="max-width: 600px; margin: 0 auto; padding: 32px; background-color: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.06);">
        
        <!-- Logo / Brand Header -->
        <div style="text-align: center; margin-bottom: 28px;">
            <img src="https://aset.warunayama.org/images/logo.png" alt="LOA Cahaya Ilmu Bangsa Logo" style="max-width: 58px; height: auto; display: inline-block;">
        </div>
        
        <!-- Title with Blue Accent Border (Consistent with other email templates) -->
        <div style="border-left: 6px solid #2563eb; padding-left: 18px; margin-bottom: 24px;">
            <h1 style="font-size: 21px; font-weight: 700; color: #111827; margin: 0; line-height: 1.3;">
                Permintaan Atur Ulang Kata Sandi
            </h1>
        </div>

        <!-- Greeting -->
        <p style="font-size: 15px; color: #4b5563; margin-bottom: 18px;">
            Halo <strong>{{ $name }}</strong>,
        </p>

        <!-- Message Box -->
        <div style="background-color: #eff6ff; padding: 16px; border-radius: 8px; margin-bottom: 22px; border-left: 4px solid #2563eb; color: #1e3a8a;">
            <p style="margin: 0; font-size: 14px; line-height: 1.6;">
                Kami menerima permintaan untuk mengatur ulang kata sandi akun Anda pada <strong>Portal LOA Cahaya Ilmu Bangsa</strong>.
            </p>
        </div>

        <!-- Action Instructions -->
        <p style="color: #4b5563; font-size: 14px; margin-bottom: 24px; line-height: 1.6;">
            Silakan klik tombol di bawah ini untuk membuat kata sandi baru. Tautan ini aman dan hanya berlaku selama <strong>{{ $expire ?? 60 }} menit</strong>.
        </p>

        <!-- CTA Button -->
        <div style="text-align: center; margin: 30px 0;">
            <a href="{!! $resetUrl !!}" 
               style="display: inline-block; padding: 13px 32px; background-color: #2563eb; color: #ffffff; text-decoration: none; font-size: 15px; font-weight: 600; border-radius: 8px; box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35); text-align: center;">
                Atur Ulang Kata Sandi
            </a>
        </div>

        <!-- Security Notice Card -->
        <div style="background-color: #f9fafb; border-radius: 8px; padding: 16px; margin-bottom: 24px; border: 1px solid #e5e7eb;">
            <p style="margin: 0 0 10px 0; font-size: 13px; color: #4b5563; line-height: 1.5;">
                <strong style="color: #1f2937;">Catatan Keamanan:</strong> Jika Anda tidak pernah meminta pengaturan ulang kata sandi ini, abaikan email ini. Kata sandi Anda tetap aman dan tidak akan berubah tanpa persetujuan Anda.
            </p>
            <p style="margin: 0; font-size: 12px; color: #6b7280; word-break: break-all; line-height: 1.5;">
                Jika tombol di atas tidak dapat diklik, salin dan tempel tautan berikut ke peramban web Anda:<br>
                <a href="{!! $resetUrl !!}" style="color: #2563eb; text-decoration: underline;">{!! $resetUrl !!}</a>
            </p>
        </div>

        <!-- Closing Message -->
        <p style="color: #4b5563; font-size: 14px; margin-bottom: 16px;">
            Terima kasih atas kepercayaan Anda menggunakan layanan <strong>LOA Cahaya Ilmu Bangsa</strong>.
        </p>

        <!-- Footer Section (Consistent with other email templates) -->
        <div style="margin-top: 28px; padding-top: 18px; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 13px;">
            <p style="margin: 0 0 4px 0;">Salam hangat,</p>
            <p style="margin: 0; font-weight: 600; color: #374151;">Tim LOA Cahaya Ilmu Bangsa</p>
            <p style="margin: 12px 0 0 0; font-size: 11px; color: #9ca3af;">
                &copy; {{ date('Y') }} Cahaya Ilmu Bangsa. Seluruh hak cipta dilindungi undang-undang.
            </p>
        </div>
    </div>

</body>
</html>
