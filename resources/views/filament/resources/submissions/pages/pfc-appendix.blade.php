@php
    $authors = $record->authors ?? [];
    $totalAuthors = count($authors);

    // Jika author <= 11, muat semua di halaman 1 lampiran agar tidak menyisakan ruang kosong besar dan tidak membuat halaman lanjutan yang nanggung.
    // Jika lebih dari 11 author, gunakan limit 10 di halaman 1 dan 12 di halaman lanjutan.
    $firstPageLimit = ($totalAuthors <= 11) ? 11 : 10;
    $subsequentPageLimit = 12;

    $pages = [];
    if ($totalAuthors > 0) {
        $pages[] = array_slice($authors, 0, $firstPageLimit);
        
        $offset = $firstPageLimit;
        while ($offset < $totalAuthors) {
            $pages[] = array_slice($authors, $offset, $subsequentPageLimit);
            $offset += $subsequentPageLimit;
        }
    }
@endphp

<div id="pfc-appendix-container" style="display: none;">
@foreach($pages as $pageIndex => $pageAuthors)
    <div class="pfc-appendix-page" style="box-sizing: border-box; background: white; width: 210mm; height: 297mm; max-height: 297mm; overflow: hidden; padding: 36pt 54pt 36pt 54pt; position: relative; font-family: 'Calibri', 'Bahnschrift', sans-serif; margin: 0 auto; page-break-before: always; page-break-inside: avoid; clear: both;">
        <div class="border-b-4 border-double border-[#1a365d] pb-3 mb-4">
            <h1 class="text-center font-bold text-2xl text-[#1a365d] uppercase tracking-wide">
                Lampiran Daftar Penulis Sertifikat
                @if($pageIndex > 0)
                    <span style="font-size: 0.8em; text-transform: none;">(Lanjutan)</span>
                @endif
            </h1>
            <p class="text-center text-gray-600 mt-0.5 font-semibold text-sm">
                Plagiarism-Free Certificate @if(count($pages) > 1) - Lampiran Halaman {{ $pageIndex + 1 }} @endif
            </p>
        </div>
        
        @if($pageIndex === 0)
            <table style="width: 100%; border-collapse: collapse; border: none; font-size: 10pt; line-height: 1.4 !important; margin-bottom: 12px;">
                <tr style="border: none;">
                    <td style="width: 150px; font-weight: bold; border: none; padding: 2px 0; vertical-align: top; line-height: 1.4 !important;">Nomor Sertifikat</td>
                    <td style="width: 15px; font-weight: bold; border: none; padding: 2px 0; vertical-align: top; text-align: center; line-height: 1.4 !important;">:</td>
                    <td style="border: none; padding: 2px 0; vertical-align: top; color: #374151; line-height: 1.4 !important;">{{ $record->created_at->format('Y') }}/CIB{{ sprintf('%03d', $record->journal->id) }}/PFC{{ sprintf('%03d', $record->id) }}</td>
                </tr>
                <tr style="border: none;">
                    <td style="width: 150px; font-weight: bold; border: none; padding: 2px 0; vertical-align: top; line-height: 1.4 !important;">Judul Artikel</td>
                    <td style="width: 15px; font-weight: bold; border: none; padding: 2px 0; vertical-align: top; text-align: center; line-height: 1.4 !important;">:</td>
                    <td style="border: none; padding: 2px 0; vertical-align: top; font-weight: 600; font-style: italic; color: black; text-align: justify; line-height: 1.4 !important;">{{ $record->title }}</td>
                </tr>
                <tr style="border: none;">
                    <td style="width: 150px; font-weight: bold; border: none; padding: 2px 0; vertical-align: top; line-height: 1.4 !important;">Jurnal</td>
                    <td style="width: 15px; font-weight: bold; border: none; padding: 2px 0; vertical-align: top; text-align: center; line-height: 1.4 !important;">:</td>
                    <td style="border: none; padding: 2px 0; vertical-align: top; color: #374151; line-height: 1.4 !important;">{{ $record->journal?->name }}</td>
                </tr>
            </table>
        @else
            <table style="width: 100%; border-collapse: collapse; border: none; font-size: 10pt; line-height: 1.4 !important; margin-bottom: 12px;">
                <tr style="border: none;">
                    <td style="width: 150px; font-weight: bold; border: none; padding: 2px 0; vertical-align: top; line-height: 1.4 !important;">Nomor Sertifikat</td>
                    <td style="width: 15px; font-weight: bold; border: none; padding: 2px 0; vertical-align: top; text-align: center; line-height: 1.4 !important;">:</td>
                    <td style="border: none; padding: 2px 0; vertical-align: top; color: #374151; line-height: 1.4 !important;">{{ $record->created_at->format('Y') }}/CIB{{ sprintf('%03d', $record->journal->id) }}/PFC{{ sprintf('%03d', $record->id) }} (Lanjutan)</td>
                </tr>
                <tr style="border: none;">
                    <td style="width: 150px; font-weight: bold; border: none; padding: 2px 0; vertical-align: top; line-height: 1.4 !important;">Judul Artikel</td>
                    <td style="width: 15px; font-weight: bold; border: none; padding: 2px 0; vertical-align: top; text-align: center; line-height: 1.4 !important;">:</td>
                    <td style="border: none; padding: 2px 0; vertical-align: top; font-weight: 600; font-style: italic; color: black; text-align: justify; line-height: 1.4 !important;">{{ $record->title }}</td>
                </tr>
            </table>
        @endif
        
        <div style="margin-top: 6px;">
            <table style="width: 100%; border-collapse: collapse; border: 1px solid #d1d5db; font-size: 9.5pt;">
                <thead>
                    <tr style="background-color: #f3f4f6; color: #374151;">
                        <th style="border: 1px solid #d1d5db; padding: 7px 10px !important; font-weight: bold; text-align: center; width: 45px; vertical-align: middle; line-height: 1.35 !important;">No.</th>
                        <th style="border: 1px solid #d1d5db; padding: 7px 10px !important; font-weight: bold; text-align: left; width: 175px; vertical-align: middle; line-height: 1.35 !important;">Nama Penulis</th>
                        <th style="border: 1px solid #d1d5db; padding: 7px 10px !important; font-weight: bold; text-align: left; vertical-align: middle; line-height: 1.35 !important;">Instansi/Afiliasi</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $startIndex = ($pageIndex === 0) ? 0 : $firstPageLimit + ($pageIndex - 1) * $subsequentPageLimit;
                    @endphp
                    @foreach($pageAuthors as $authorKey => $author)
                        <tr style="border-bottom: 1px solid #e5e7eb;">
                            <td style="border: 1px solid #d1d5db; padding: 7px 10px !important; text-align: center; vertical-align: middle; line-height: 1.35 !important;">
                                {{ $startIndex + $authorKey + 1 }}
                            </td>
                            <td style="border: 1px solid #d1d5db; padding: 7px 10px !important; text-align: left; font-weight: 600; color: black; vertical-align: middle; line-height: 1.35 !important;">
                                {{ $author['name'] ?? '' }}
                            </td>
                            <td style="border: 1px solid #d1d5db; padding: 7px 10px !important; text-align: left; color: #4b5563; vertical-align: middle; line-height: 1.35 !important;">
                                {{ $author['institution'] ?? '' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endforeach
</div>