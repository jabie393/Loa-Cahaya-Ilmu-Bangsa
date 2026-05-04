<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Cek Plagiasi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f4f7f9;
        }

        .container {
            max-width: 700px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
        }

        .header {
            background-color: #ffffff;
            padding: 30px;
            text-align: center;
            border-bottom: 4px solid #10b981;
        }

        .header img {
            max-width: 60px;
            height: auto;
            margin-bottom: 15px;
        }

        .header h1 {
            margin: 0;
            font-size: 20px;
            color: #1a365d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .header p {
            margin: 5px 0 0;
            color: #64748b;
            font-size: 12px;
            font-weight: bold;
        }

        .content {
            padding: 40px;
        }

        .intro {
            border-left: 8px solid #10b981;
            padding-left: 20px;
            margin-bottom: 30px;
        }

        .intro h2 {
            font-size: 20px;
            font-weight: bold;
            color: #1f2937;
            margin: 0;
        }

        .score-section {
            text-align: center;
            padding: 20px;
            background-color: #f0fdf4;
            border-radius: 12px;
            margin-bottom: 30px;
            border: 1px solid #dcfce7;
        }

        .score-value {
            font-size: 48px;
            font-weight: 800;
            color: #166534;
            margin: 0;
        }

        .score-label {
            font-size: 14px;
            color: #15803d;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: bold;
        }

        .review-section {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f9fafb;
            border-radius: 8px;
            border: 1px solid #f1f5f9;
        }

        .review-section h3 {
            margin-top: 0;
            color: #1e40af;
            font-size: 15px;
            text-transform: uppercase;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }

        .review-section p {
            margin-bottom: 0;
            font-size: 14px;
            color: #4b5563;
        }

        .footer {
            background-color: #f9fafb;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            background-color: #d1fae5;
            color: #065f46;
            border-radius: 6px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .signature {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 14px;
        }

        .highlight-item {
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 10px;
        }
        
        .highlight-item:last-child {
            border-bottom: none;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Logo Section -->
        <div class="header">
            <img src="https://aset.warunayama.org/images/logo.png" alt="CAHAYA ILMU BANGSA">
            <h1>CAHAYA ILMU BANGSA</h1>
            <p>Penerbitan Jurnal Ilmiah & Konsultasi Akademik Profesional</p>
        </div>

        <div class="content">
            <div class="intro">
                <span class="badge">Hasil Cek Plagiasi</span>
                <h2>Laporan Hasil Pengecekan Kemiripan Naskah</h2>
            </div>

            <p style="font-size: 15px; color: #4b5563;">Halo,</p>
            <p style="font-size: 14px; color: #4b5563; margin-bottom: 25px;">Terima kasih telah menggunakan layanan
                cek plagiasi kami. Sistem kami telah selesai menganalisis naskah Anda yang berjudul <strong>"{{ $record->title ?: 'Dokumen Jurnal' }}"</strong>.</p>

            <div class="score-section" style="background-color: {{ $record->similarity_score < 20 ? '#f0fdf4' : ($record->similarity_score < 50 ? '#fffbeb' : '#fef2f2') }}; border-color: {{ $record->similarity_score < 20 ? '#dcfce7' : ($record->similarity_score < 50 ? '#fef3c7' : '#fee2e2') }};">
                <p class="score-value" style="color: {{ $record->similarity_score < 20 ? '#166534' : ($record->similarity_score < 50 ? '#92400e' : '#991b1b') }};">{{ $record->similarity_score }}%</p>
                <p class="score-label" style="color: {{ $record->similarity_score < 20 ? '#15803d' : ($record->similarity_score < 50 ? '#b45309' : '#b91c1c') }};">Similarity Score ({{ ucfirst($record->similarity_category) }})</p>
            </div>

            @if(!empty($record->report_data['highlighted_parts']))
                <div class="review-section">
                    <h3>Bagian dengan Kemiripan Tinggi</h3>
                    @foreach($record->report_data['highlighted_parts'] as $part)
                        <div class="highlight-item">
                            <p style="font-style: italic; color: #374151;">"{{ Str::limit($part['text'], 200) }}"</p>
                            <p style="font-size: 12px; color: #6b7280; margin-top: 5px;"><strong>Sumber:</strong> {{ $part['source'] ?? 'External' }}</p>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="review-section" style="border-left: 4px solid #10b981; background-color: #f0fdf4;">
                <h3 style="color: #065f46;">Rekomendasi</h3>
                <p>
                    @if($record->similarity_score < 20)
                        Tingkat kemiripan rendah. Dokumen Anda memiliki orisinalitas yang sangat baik.
                    @elseif($record->similarity_score < 50)
                        Tingkat kemiripan sedang. Kami menyarankan untuk melakukan parafrase pada bagian-bagian yang terdeteksi mirip sebelum melakukan submit.
                    @else
                        Tingkat kemiripan tinggi. Diperlukan revisi signifikan dan penulisan ulang pada banyak bagian untuk menghindari plagiarisme.
                    @endif
                </p>
            </div>

            <div class="signature">
                <p style="margin: 0;">Salam,<br><strong>Tim Redaksi Cahaya Ilmu Bangsa</strong></p>
            </div>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} <strong>Cahaya Ilmu Bangsa</strong>. All Rights Reserved.<br>
            Jl. Raya Sempalwadak No.6, Arjowinangun, Kec. Kedungkandang, Kota Malang, Jawa Timur 65132
        </div>
    </div>
</body>

</html>
