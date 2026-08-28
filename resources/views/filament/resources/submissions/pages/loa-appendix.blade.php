@php
    $authors = $record->authors ?? [];
    $firstPageLimit = 15;
    $subsequentPageLimit = 20;

    $pages = [];
    if (count($authors) > 0) {
        // Page 1 of appendix (first chunk)
        $pages[] = array_slice($authors, 0, $firstPageLimit);
        
        // Subsequent pages of appendix
        $offset = $firstPageLimit;
        while ($offset < count($authors)) {
            $pages[] = array_slice($authors, $offset, $subsequentPageLimit);
            $offset += $subsequentPageLimit;
        }
    }
@endphp

<div id="loa-appendix-container" style="display: none;">
@foreach($pages as $pageIndex => $pageAuthors)
    <div class="loa-appendix-page" style="box-sizing: border-box; background: white; width: 210mm; height: 297mm; max-height: 297mm; overflow: hidden; padding: 50pt 72pt 72pt 72pt; position: relative; font-family: 'Calibri', 'Bahnschrift', sans-serif; margin: 0 auto; page-break-before: always; page-break-inside: avoid; clear: both;">
        <div class="border-b-4 border-double border-[#1d428a] pb-4 mb-6">
            <h1 class="text-center font-bold text-2xl text-[#1d428a] uppercase tracking-wide">
                Lampiran Daftar Penulis
                @if($pageIndex > 0)
                    <span style="font-size: 0.8em; text-transform: none;">(Lanjutan)</span>
                @endif
            </h1>
            <p class="text-center text-gray-600 mt-1 font-semibold">
                Surat Keterangan Diterima (Letter of Acceptance) @if(count($pages) > 1) - Lampiran Halaman {{ $pageIndex + 1 }} @endif
            </p>
        </div>
        
        @if($pageIndex === 0)
            <table style="width: 100%; border-collapse: collapse; border: none; font-size: 11pt; line-height: 1.6 !important; margin-bottom: 20px;">
                <tr style="border: none;">
                    <td style="width: 120px; font-weight: bold; border: none; padding: 4px 0; vertical-align: top; line-height: 1.6 !important;">Nomor LOA</td>
                    <td style="width: 15px; font-weight: bold; border: none; padding: 4px 0; vertical-align: top; text-align: center; line-height: 1.6 !important;">:</td>
                    <td style="border: none; padding: 4px 0; vertical-align: top; color: #374151; line-height: 1.6 !important;">{{ $record->loa_number }}</td>
                </tr>
                <tr style="border: none;">
                    <td style="width: 120px; font-weight: bold; border: none; padding: 4px 0; vertical-align: top; line-height: 1.6 !important;">Judul Artikel</td>
                    <td style="width: 15px; font-weight: bold; border: none; padding: 4px 0; vertical-align: top; text-align: center; line-height: 1.6 !important;">:</td>
                    <td style="border: none; padding: 4px 0; vertical-align: top; font-weight: 600; font-style: italic; color: black; text-align: justify; line-height: 1.6 !important;">{{ $record->title }}</td>
                </tr>
                <tr style="border: none;">
                    <td style="width: 120px; font-weight: bold; border: none; padding: 4px 0; vertical-align: top; line-height: 1.6 !important;">Jurnal</td>
                    <td style="width: 15px; font-weight: bold; border: none; padding: 4px 0; vertical-align: top; text-align: center; line-height: 1.6 !important;">:</td>
                    <td style="border: none; padding: 4px 0; vertical-align: top; color: #374151; line-height: 1.6 !important;">{{ $record->journal?->name }}</td>
                </tr>
            </table>
        @else
            <table style="width: 100%; border-collapse: collapse; border: none; font-size: 11pt; line-height: 1.6 !important; margin-bottom: 20px;">
                <tr style="border: none;">
                    <td style="width: 120px; font-weight: bold; border: none; padding: 2px 0; vertical-align: top; line-height: 1.6 !important;">Nomor LOA</td>
                    <td style="width: 15px; font-weight: bold; border: none; padding: 2px 0; vertical-align: top; text-align: center; line-height: 1.6 !important;">:</td>
                    <td style="border: none; padding: 2px 0; vertical-align: top; color: #374151; line-height: 1.6 !important;">{{ $record->loa_number }} (Lanjutan)</td>
                </tr>
                <tr style="border: none;">
                    <td style="width: 120px; font-weight: bold; border: none; padding: 2px 0; vertical-align: top; line-height: 1.6 !important;">Judul Artikel</td>
                    <td style="width: 15px; font-weight: bold; border: none; padding: 2px 0; vertical-align: top; text-align: center; line-height: 1.6 !important;">:</td>
                    <td style="border: none; padding: 2px 0; vertical-align: top; font-weight: 600; font-style: italic; color: black; line-height: 1.6 !important;">
                        <div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 450px;">{{ $record->title }}</div>
                    </td>
                </tr>
            </table>
        @endif
        
        <div style="margin-top: 10px;">
            <table style="width: 100%; border-collapse: collapse; border: 1px solid #d1d5db; font-size: 10pt;">
                <thead>
                    <tr style="background-color: #f3f4f6; color: #374151;">
                        <th style="border: 1px solid #d1d5db; padding-top: 10px !important; padding-bottom: 10px !important; padding-left: 12px !important; padding-right: 12px !important; font-weight: bold; text-align: center; width: 50px; vertical-align: middle; line-height: 1.5 !important;">No.</th>
                        <th style="border: 1px solid #d1d5db; padding-top: 10px !important; padding-bottom: 10px !important; padding-left: 12px !important; padding-right: 12px !important; font-weight: bold; text-align: left; vertical-align: middle; line-height: 1.5 !important;">Nama Penulis</th>
                        <th style="border: 1px solid #d1d5db; padding-top: 10px !important; padding-bottom: 10px !important; padding-left: 12px !important; padding-right: 12px !important; font-weight: bold; text-align: left; vertical-align: middle; line-height: 1.5 !important;">Instansi/Afiliasi</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $startIndex = ($pageIndex === 0) ? 0 : $firstPageLimit + ($pageIndex - 1) * $subsequentPageLimit;
                    @endphp
                    @foreach($pageAuthors as $authorKey => $author)
                        <tr style="border-bottom: 1px solid #e5e7eb;">
                            <td style="border: 1px solid #d1d5db; padding-top: 10px !important; padding-bottom: 10px !important; padding-left: 12px !important; padding-right: 12px !important; text-align: center; vertical-align: middle; line-height: 1.5 !important;">
                                {{ $startIndex + $authorKey + 1 }}
                            </td>
                            <td style="border: 1px solid #d1d5db; padding-top: 10px !important; padding-bottom: 10px !important; padding-left: 12px !important; padding-right: 12px !important; text-align: left; font-weight: 600; color: black; vertical-align: middle; line-height: 1.5 !important;">
                                {{ $author['name'] ?? '' }}
                            </td>
                            <td style="border: 1px solid #d1d5db; padding-top: 10px !important; padding-bottom: 10px !important; padding-left: 12px !important; padding-right: 12px !important; text-align: left; color: #4b5563; vertical-align: middle; line-height: 1.5 !important;">
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