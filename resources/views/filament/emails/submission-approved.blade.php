<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submission Approved</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">

    <!-- Main container with inline CSS -->
    <div style="max-width: 600px; margin: 20px auto; padding: 30px; background-color: #fff; border: 1px solid #e5e7eb; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
        
        <!-- Logo/Header Section -->
        <div style="text-align: center; margin-bottom: 30px;">
            <img src="https://aset.warunayama.org/images/logo.png" alt="LOA Cahaya Ilmu Bangsa Logo" style="max-width: 120px; height: auto;">
        </div>
        
        <!-- Header with accent border -->
        <div style="border-left: 8px solid #22c55e; padding-left: 20px; margin-bottom: 30px;">
            <h1 style="font-size: 28px; font-weight: bold; color: #1f2937; margin: 0;">Selamat! Publikasi Ilmiah Anda Telah Disetujui</h1>
        </div>

        <!-- Greeting -->
        <p style="font-size: 16px; color: #4b5563; margin-bottom: 15px;">Halo {{ $user->name }},</p>

        <!-- Approval message -->
        <div style="background-color: #e0f2fe; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #0284c7; color: #475569;">
            <p style="margin: 0;">Kami dengan senang hati menginformasikan bahwa pengajuan Letter of Acceptance (LOA) Anda telah disetujui oleh admin.</p>
        </div>

        <!-- Detail Section -->
        <h2 style="font-size: 18px; font-weight: bold; color: #1f2937; border-bottom: 2px solid #e5e7eb; padding-bottom: 8px; margin-bottom: 15px;">Detail Pengajuan:</h2>
        
        <!-- Card-like list with inline CSS -->
        <div style="background-color: #f9fafb; border-radius: 8px; padding: 15px; margin-bottom: 20px;">
            <ul style="list-style: none; padding: 0; margin: 0;">
                <li style="display: flex; flex-wrap: wrap; margin-bottom: 8px;">
                    <span style="font-weight: 600; width: 140px; color: #1f2937;">Judul:</span>
                    <span style="color: #4b5563;">{{ $submission->title }}</span>
                </li>
                <li style="display: flex; flex-wrap: wrap; margin-bottom: 8px;">
                    <span style="font-weight: 600; width: 140px; color: #1f2937;">Nama Penulis:</span>
                    <span style="color: #4b5563;">{{ $submission->author_name }}</span>
                </li>
                <li style="display: flex; flex-wrap: wrap; margin-bottom: 8px;">
                    <span style="font-weight: 600; width: 140px; color: #1f2937;">Institusi:</span>
                    <span style="color: #4b5563;">{{ $submission->institution }}</span>
                </li>
                <li style="display: flex; flex-wrap: wrap; margin-bottom: 8px;">
                    <span style="font-weight: 600; width: 140px; color: #1f2937;">Jurnal:</span>
                    <span style="color: #4b5563;">{{ $submission->journal->name ?? 'N/A' }}</span>
                </li>
                <li style="display: flex; flex-wrap: wrap; margin-bottom: 8px;">
                    <span style="font-weight: 600; width: 140px; color: #1f2937;">Volume:</span>
                    <span style="color: #4b5563;">{{ $submission->volume }}</span>
                </li>
                <li style="display: flex; flex-wrap: wrap; margin-bottom: 8px;">
                    <span style="font-weight: 600; width: 140px; color: #1f2937;">Tanggal LOA:</span>
                    <span style="color: #4b5563;">{{ $submission->date_of_loa->format('d M Y') }}</span>
                </li>
                <li style="display: flex; flex-wrap: wrap;">
                    <span style="font-weight: 600; width: 140px; color: #1f2937;">Tanggal Disetujui:</span>
                    <span style="color: #4b5563;">{{ $submission->approved_date->format('d M Y') }}</span>
                </li>
            </ul>
        </div>

        <!-- Download/Print info -->
        <p style="color: #4b5563; margin-bottom: 20px;">Silakan unduh atau cetak LOA & sertifikat Anda dari sistem kami.</p>

        <!-- Buttons container with inline CSS -->
        <div style="margin-bottom: 25px;">
            <a href="{{ url('/loa/preview/' . $submission->id) }}" 
               style="display: inline-block; padding: 12px 24px; background-color: #2563eb; color: white; text-decoration: none; font-weight: 600; border-radius: 6px; margin-right: 10px; margin-bottom: 10px; transition: background-color 0.2s;">Letter of Acceptance</a>
            <a href="{{ url('/ac/preview/' . $submission->id) }}" 
               style="display: inline-block; padding: 12px 24px; background-color: #16a34a; color: white; text-decoration: none; font-weight: 600; border-radius: 6px; margin-right: 10px; margin-bottom: 10px; transition: background-color 0.2s;">Author's Certificate</a>
            <a href="{{ url('/pfc/preview/' . $submission->id) }}" 
               style="display: inline-block; padding: 12px 24px; background-color: #dc2626; color: white; text-decoration: none; font-weight: 600; border-radius: 6px; margin-bottom: 10px; transition: background-color 0.2s;">Plagiarism-Free Certificate</a>
        </div>

        <!-- Closing remarks -->
        <p style="color: #4b5563; margin-bottom: 15px;">Terima kasih atas partisipasi Anda dalam LOA Cahaya Ilmu Bangsa.</p>

        <!-- Signature -->
        <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 14px;">
            <p style="margin: 0;">Salam,<br>Tim LOA Cahaya Ilmu Bangsa</p>
        </div>
    </div>

</body>
</html>