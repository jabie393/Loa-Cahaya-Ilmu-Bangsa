<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submission Approved</title>
</head>
<body>
    <h1>Selamat! Pengajuan LOA Anda Telah Disetujui</h1>

    <p>Halo {{ $user->name }},</p>

    <p>Kami dengan senang hati menginformasikan bahwa pengajuan Letter of Acceptance (LOA) Anda telah disetujui oleh admin.</p>

    <h2>Detail Pengajuan:</h2>
    <ul>
        <li><strong>Judul:</strong> {{ $submission->title }}</li>
        <li><strong>Nama Penulis:</strong> {{ $submission->author_name }}</li>
        <li><strong>Institusi:</strong> {{ $submission->institution }}</li>
        <li><strong>Jurnal:</strong> {{ $submission->journal->name ?? 'N/A' }}</li>
        <li><strong>Volume:</strong> {{ $submission->volume }}</li>
        <li><strong>Tanggal LOA:</strong> {{ $submission->date_of_loa->format('d M Y') }}</li>
        <li><strong>Tanggal Disetujui:</strong> {{ $submission->approved_date->format('d M Y') }}</li>
    </ul>

    <p>Silakan unduh atau cetak LOA & sertifikat Anda dari sistem kami.</p>

    <p>
        <a href="http://127.0.0.1:8000/loa/preview/{{ $submission->id }}?print=1" style="display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 5px;">Letter of Acceptance</a>
        <a href="http://127.0.0.1:8000/ac/preview/{{ $submission->id }}?print=1" style="display: inline-block; padding: 10px 20px; background-color: #28a745; color: white; text-decoration: none; border-radius: 5px; margin: 5px;">Author's Certificate</a>
        <a href="http://127.0.0.1:8000/pfc/preview/{{ $submission->id }}?print=1" style="display: inline-block; padding: 10px 20px; background-color: #dc3545; color: white; text-decoration: none; border-radius: 5px; margin: 5px;">Plagiarism-Free Certificate</a>
    </p>

    <p>Terima kasih atas partisipasi Anda dalam LOA Cahaya Ilmu Bangsa.</p>

    <p>Salam,<br>
    Tim LOA Cahaya Ilmu Bangsa</p>
</body>
</html>