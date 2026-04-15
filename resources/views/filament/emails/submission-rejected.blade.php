<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submission Rejected</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">

    <!-- Main container with inline CSS -->
    <div style="max-width: 600px; margin: 20px auto; padding: 30px; background-color: #fff; border: 1px solid #e5e7eb; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">

        <!-- Logo/Header Section -->
        <div style="text-align: center; margin-bottom: 30px;">
            <img src="https://aset.warunayama.org/images/logo.png" alt="LOA Cahaya Ilmu Bangsa Logo" style="max-width: 58px; height: auto;">
        </div>

        <!-- Header with accent border -->
        <div style="border-left: 8px solid #dc2626; padding-left: 20px; margin-bottom: 30px;">
            <h1 style="font-size: 22px; font-weight: bold; color: #1f2937; margin: 0;">Maaf, Publikasi Ilmiah Anda Ditolak</h1>
        </div>

        <!-- Greeting -->
        <p style="font-size: 16px; color: #4b5563; margin-bottom: 15px;">Halo {{ $user->name }},</p>

        <!-- Rejection message -->
        <div style="background-color: #fef2f2; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #dc2626; color: #475569;">
            <p style="margin: 0;">Kami mohon maaf, pengajuan Letter of Acceptance (LOA) Anda telah ditolak oleh admin.</p>
        </div>

        <!-- Rejection reason -->
        <div style="background-color: #f9fafb; border-radius: 8px; padding: 15px; margin-bottom: 20px;">
            <h3 style="font-size: 16px; font-weight: bold; color: #1f2937; margin-top: 0; margin-bottom: 10px;">Alasan Penolakan:</h3>
            <p style="color: #4b5563; margin: 0;">{{ $submission->rejection_reason }}</p>
        </div>

        <!-- Detail Section -->
        <h2 style="font-size: 18px; font-weight: bold; color: #1f2937; border-bottom: 2px solid #e5e7eb; padding-bottom: 8px; margin-bottom: 15px;">Detail Pengajuan:</h2>

        <!-- Card-like details table with inline CSS for better wrapping -->
        <div style="background-color: #f9fafb; border-radius: 8px; padding: 15px; margin-bottom: 20px;">
            <table role="presentation" style="width: 100%; border-collapse: collapse; border-spacing: 0;">
                <tr>
                    <td style="padding: 8px 0; vertical-align: top; width: 140px; font-weight: 600; color: #1f2937;">Judul:</td>
                    <td style="padding: 8px 0; color: #4b5563;">{{ $submission->title }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; vertical-align: top; font-weight: 600; color: #1f2937;">Nama Penulis:</td>
                    <td style="padding: 8px 0; color: #4b5563;">{{ $submission->author_name }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; vertical-align: top; font-weight: 600; color: #1f2937;">Institusi:</td>
                    <td style="padding: 8px 0; color: #4b5563;">{{ $submission->institution }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; vertical-align: top; font-weight: 600; color: #1f2937;">Jurnal:</td>
                    <td style="padding: 8px 0; color: #4b5563;">{{ $submission->journal->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; vertical-align: top; font-weight: 600; color: #1f2937;">Volume:</td>
                    <td style="padding: 8px 0; color: #4b5563;">{{ $submission->volume }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; vertical-align: top; font-weight: 600; color: #1f2937;">Tanggal Ditolak:</td>
                    <td style="padding: 8px 0; color: #4b5563;">{{ $submission->rejected_date->format('d M Y') }}</td>
                </tr>
            </table>
        </div>

        <!-- Closing remarks -->
        <p style="color: #4b5563; margin-bottom: 15px;">Silakan revisi pengajuan sesuai alasan penolakan untuk mengajukan ulang sebelum pengajuan dihapus otomatis oleh sistem pada {{ $submission->rejected_date->addDays(7)->format('d M Y') }}, atau hubungi tim kami jika Anda memiliki pertanyaan lebih lanjut.</p>

        <!-- Signature -->
        <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 14px;">
            <p style="margin: 0;">Salam,<br>Tim LOA Cahaya Ilmu Bangsa</p>
        </div>
    </div>

</body>
</html>