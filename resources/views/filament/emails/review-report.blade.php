<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Review Pra-OJS</title>
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
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border: 1px solid #e5e7eb;
        }
        .header {
            background-color: #ffffff;
            padding: 30px;
            text-align: center;
            border-bottom: 4px solid #ee4d2d;
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
            border-left: 8px solid #ee4d2d;
            padding-left: 20px;
            margin-bottom: 30px;
        }
        .intro h2 {
            font-size: 20px;
            font-weight: bold;
            color: #1f2937;
            margin: 0;
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
            white-space: pre-line;
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
            background-color: #e0f2fe;
            color: #0284c7;
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
                <span class="badge">Hasil Review Pra-OJS</span>
                <h2>Hasil Evaluasi Awal Naskah Jurnal</h2>
            </div>

            <p style="font-size: 15px; color: #4b5563;">Halo <strong>{{ $review->author_name }}</strong>,</p>
            <p style="font-size: 14px; color: #4b5563; margin-bottom: 25px;">Terima kasih telah menggunakan layanan penelaahan awal kami. Tim editorial telah selesai melakukan review singkat terhadap naskah Anda untuk memastikan kesesuaian standar sebelum diajukan ke sistem OJS.</p>

            @if($review->structure_review)
            <div class="review-section">
                <h3>Kelengkapan Struktur Jurnal</h3>
                <p>{{ $review->structure_review }}</p>
            </div>
            @endif

            @if($review->abstract_review)
            <div class="review-section">
                <h3>Abstrak</h3>
                <p>{{ $review->abstract_review }}</p>
            </div>
            @endif

            @if($review->introduction_review)
            <div class="review-section">
                <h3>Pendahuluan</h3>
                <p>{{ $review->introduction_review }}</p>
            </div>
            @endif

            @if($review->method_review)
            <div class="review-section">
                <h3>Metode Penelitian</h3>
                <p>{{ $review->method_review }}</p>
            </div>
            @endif

            @if($review->results_review)
            <div class="review-section">
                <h3>Hasil dan Pembahasan</h3>
                <p>{{ $review->results_review }}</p>
            </div>
            @endif

            @if($review->conclusion_review)
            <div class="review-section">
                <h3>Kesimpulan</h3>
                <p>{{ $review->conclusion_review }}</p>
            </div>
            @endif

            @if($review->bibliography_review)
            <div class="review-section">
                <h3>Daftar Pustaka</h3>
                <p>{{ $review->bibliography_review }}</p>
            </div>
            @endif

            @if($review->general_suggestions)
            <div class="review-section" style="border-left: 4px solid #fbbf24; background-color: #fffbeb;">
                <h3 style="color: #92400e;">Saran Revisi Umum</h3>
                <p>{{ $review->general_suggestions }}</p>
            </div>
            @endif

            <div class="signature">
                <p style="margin: 0;">Salam,<br><strong>Tim Redaksi Cahaya Ilmu Bangsa</strong></p>
            </div>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} <strong>Cahaya Ilmu Bangsa</strong>. All Rights Reserved.<br>
            Jl. Raya Jurnal No. 123, Blok Akademik, Indonesia.
        </div>
    </div>
</body>
</html>
