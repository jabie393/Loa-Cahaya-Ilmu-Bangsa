<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Parafrase &amp; Optimasi Naskah</title>
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
            max-width: 750px;
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
            border-bottom: 4px solid #1e40af;
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
            border-left: 8px solid #1e40af;
            padding-left: 20px;
            margin-bottom: 30px;
        }

        .intro h2 {
            font-size: 20px;
            font-weight: bold;
            color: #1f2937;
            margin: 0;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            background-color: #dbeafe;
            color: #1e40af;
            border-radius: 6px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .comparison-summary {
            display: table;
            width: 100%;
            margin-bottom: 30px;
            background-color: #f8fafc;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }

        .score-col {
            display: table-cell;
            width: 45%;
            text-align: center;
            padding: 20px;
            vertical-align: middle;
        }

        .arrow-col {
            display: table-cell;
            width: 10%;
            text-align: center;
            font-size: 28px;
            color: #64748b;
            vertical-align: middle;
            font-weight: bold;
        }

        .score-val {
            font-size: 38px;
            font-weight: 800;
            margin: 0;
        }

        .score-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: bold;
            margin-top: 5px;
        }

        .improvement-card {
            background-color: #ffffff;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            margin-bottom: 25px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
            overflow: hidden;
        }

        .card-header {
            background-color: #f1f5f9;
            padding: 10px 15px;
            font-size: 13px;
            font-weight: bold;
            color: #475569;
            border-bottom: 1px solid #e2e8f0;
        }

        .card-body {
            padding: 15px;
        }

        .text-block {
            margin-bottom: 15px;
        }

        .text-block:last-child {
            margin-bottom: 0;
        }

        .text-label {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .text-content {
            font-size: 13px;
            padding: 10px;
            border-radius: 6px;
            margin: 0;
        }

        .text-original {
            background-color: #fef2f2;
            color: #991b1b;
            border-left: 3px solid #f87171;
            font-style: italic;
        }

        .text-improved {
            background-color: #f0fdf4;
            color: #166534;
            border-left: 3px solid #4ade80;
            font-weight: 500;
        }

        .editor-note {
            background-color: #eff6ff;
            color: #1e40af;
            border-left: 3px solid #60a5fa;
            font-size: 12px;
        }

        .footer {
            background-color: #f9fafb;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
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
        <!-- Logo Header -->
        <div class="header">
            <img src="https://aset.warunayama.org/images/logo.png" alt="CAHAYA ILMU BANGSA">
            <h1>CAHAYA ILMU BANGSA</h1>
            <p>Penerbitan Jurnal Ilmiah &amp; Konsultasi Akademik Profesional</p>
        </div>

        <div class="content">
            <div class="intro">
                <span class="badge">Layanan Parafrase Jurnal</span>
                <h2>Hasil Parafrase &amp; Optimasi Akademik Naskah</h2>
            </div>

            <p style="font-size: 15px; color: #4b5563;">Halo,</p>
            <p style="font-size: 14px; color: #4b5563; margin-bottom: 25px;">
                Kami senang mengabarkan bahwa tim editorial kami telah berhasil menyelesaikan **Parafrase Akademik** pada naskah Anda yang berjudul: 
                <strong>"{{ $paraphrase->plagiarismCheck->title ?: 'Dokumen Jurnal' }}"</strong>.
            </p>

            <!-- Comparison Summary Table -->
            <div class="comparison-summary">
                <div class="score-col" style="background-color: #fef2f2;">
                    <p class="score-val" style="color: #991b1b;">{{ $paraphrase->original_score }}%</p>
                    <p class="score-label" style="color: #b91c1c;">Similarity Awal</p>
                </div>
                <div class="arrow-col">➔</div>
                <div class="score-col" style="background-color: #f0fdf4;">
                    <p class="score-val" style="color: #166534;">{{ $paraphrase->estimated_new_score }}%</p>
                    <p class="score-label" style="color: #15803d;">Estimasi Setelah Parafrase</p>
                </div>
            </div>

            <p style="font-size: 14px; color: #1e3a8a; background-color: #eff6ff; padding: 12px; border-radius: 8px; border: 1px solid #bfdbfe; margin-bottom: 25px;">
                <strong>Catatan Layanan Editorial:</strong> Fokus parafrase ditargetkan secara presisi hanya pada bagian kalimat dengan kemiripan tinggi. Istilah ilmiah khusus, konvensi penulisan akademik, dan format rujukan (sitasi) tetap kami pertahankan secara utuh agar orisinalitas naskah meningkat tajam tanpa merusak substansi ilmiah naskah Anda.
            </p>

            <!-- Detailed Paraphrases -->
            <h3 style="color: #1e2937; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px; margin-bottom: 20px; font-size: 16px; text-transform: uppercase; letter-spacing: 0.5px;">
                Detail Hasil Optimasi Kalimat
            </h3>

            @if(!empty($paraphrase->improvements))
                @foreach($paraphrase->improvements as $index => $item)
                    <div class="improvement-card">
                        <div class="card-header">Bagian #{{ $index + 1 }}</div>
                        <div class="card-body">
                            <div class="text-block">
                                <div class="text-label" style="color: #b91c1c;">Teks Asli (Similarity Tinggi):</div>
                                <p class="text-content text-original">"{{ $item['original'] }}"</p>
                            </div>
                            <div class="text-block">
                                <div class="text-label" style="color: #15803d;">Rekomendasi Hasil Parafrase:</div>
                                <p class="text-content text-improved">"{{ $item['improved'] }}"</p>
                            </div>
                            @if(!empty($item['explanation']))
                                <div class="text-block">
                                    <div class="text-label" style="color: #1e40af;">Catatan Perubahan:</div>
                                    <p class="text-content editor-note">{{ $item['explanation'] }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            @endif

            <div class="signature">
                <p style="margin: 0;">Salam hangat,<br><strong>Tim Editorial Cahaya Ilmu Bangsa</strong></p>
            </div>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} <strong>Cahaya Ilmu Bangsa</strong>. All Rights Reserved.<br>
            Jl. Raya Sempalwadak No.6, Arjowinangun, Kec. Kedungkandang, Kota Malang, Jawa Timur 65132
        </div>
    </div>
</body>

</html>
