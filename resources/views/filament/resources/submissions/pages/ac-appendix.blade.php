@php
    $authors = $record->authors ?? [];
    $firstPageLimit = 5;
    $subsequentPageLimit = 8;

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

<div id="ac-appendix-container" style="display: none;">
    @foreach($pages as $pageIndex => $pageAuthors)
        <div class="ac-appendix-page"
            style="box-sizing: border-box; background: white; width: 297mm; height: 210mm; max-height: 210mm; overflow: hidden; padding: 28pt 45pt 28pt 45pt; position: relative; font-family: 'Calibri', 'Bahnschrift', sans-serif; margin: 0 auto; page-break-before: always; page-break-inside: avoid; clear: both;">
            <div class="border-b-4 border-double border-[#003354] pb-3 mb-4">
                <h1 class="text-center font-bold text-2xl text-[#003354] uppercase tracking-wide">
                    Lampiran Daftar Penulis Sertifikat
                    @if($pageIndex > 0)
                        <span style="font-size: 0.8em; text-transform: none;">(Lanjutan)</span>
                    @endif
                </h1>
                <p class="text-center text-gray-600 mt-0.5 font-semibold text-sm">
                    Certificate of Achievement @if(count($pages) > 1) - Lampiran Halaman {{ $pageIndex + 1 }} @endif
                </p>
            </div>

            @if($pageIndex === 0)
                <table
                    style="width: 100%; border-collapse: collapse; border: none; font-size: 10pt; line-height: 1.4 !important; margin-bottom: 12px;">
                    <tr style="border: none;">
                        <td
                            style="width: 140px; font-weight: bold; border: none; padding: 2px 0; vertical-align: top; line-height: 1.4 !important;">
                            Nomor Sertifikat</td>
                        <td
                            style="width: 15px; font-weight: bold; border: none; padding: 2px 0; vertical-align: top; text-align: center; line-height: 1.4 !important;">
                            :</td>
                        <td
                            style="border: none; padding: 2px 0; vertical-align: top; color: #374151; line-height: 1.4 !important;">
                            {{ $record->created_at->format('Y') }}/CIB{{ sprintf('%03d', $record->journal->id) }}/AC{{ sprintf('%03d', $record->id) }}
                        </td>
                    </tr>
                    <tr style="border: none;">
                        <td
                            style="width: 140px; font-weight: bold; border: none; padding: 2px 0; vertical-align: top; line-height: 1.4 !important;">
                            Judul Artikel</td>
                        <td
                            style="width: 15px; font-weight: bold; border: none; padding: 2px 0; vertical-align: top; text-align: center; line-height: 1.4 !important;">
                            :</td>
                        <td
                            style="border: none; padding: 2px 0; vertical-align: top; font-weight: 600; font-style: italic; color: black; text-align: justify; line-height: 1.4 !important;">
                            {{ $record->title }}</td>
                    </tr>
                    <tr style="border: none;">
                        <td
                            style="width: 140px; font-weight: bold; border: none; padding: 2px 0; vertical-align: top; line-height: 1.4 !important;">
                            Jurnal</td>
                        <td
                            style="width: 15px; font-weight: bold; border: none; padding: 2px 0; vertical-align: top; text-align: center; line-height: 1.4 !important;">
                            :</td>
                        <td
                            style="border: none; padding: 2px 0; vertical-align: top; color: #374151; line-height: 1.4 !important;">
                            {{ $record->journal?->name }}</td>
                    </tr>
                </table>
            @else
                <table
                    style="width: 100%; border-collapse: collapse; border: none; font-size: 10pt; line-height: 1.4 !important; margin-bottom: 12px;">
                    <tr style="border: none;">
                        <td
                            style="width: 140px; font-weight: bold; border: none; padding: 2px 0; vertical-align: top; line-height: 1.4 !important;">
                            Nomor Sertifikat</td>
                        <td
                            style="width: 15px; font-weight: bold; border: none; padding: 2px 0; vertical-align: top; text-align: center; line-height: 1.4 !important;">
                            :</td>
                        <td
                            style="border: none; padding: 2px 0; vertical-align: top; color: #374151; line-height: 1.4 !important;">
                            {{ $record->created_at->format('Y') }}/CIB{{ sprintf('%03d', $record->journal->id) }}/AC{{ sprintf('%03d', $record->id) }}
                            (Lanjutan)</td>
                    </tr>
                    <tr style="border: none;">
                        <td
                            style="width: 140px; font-weight: bold; border: none; padding: 2px 0; vertical-align: top; line-height: 1.4 !important;">
                            Judul Artikel</td>
                        <td
                            style="width: 15px; font-weight: bold; border: none; padding: 2px 0; vertical-align: top; text-align: center; line-height: 1.4 !important;">
                            :</td>
                        <td
                            style="border: none; padding: 2px 0; vertical-align: top; font-weight: 600; font-style: italic; color: black; text-align: justify; line-height: 1.4 !important;">
                            {{ $record->title }}</td>
                    </tr>
                </table>
            @endif

            <div style="margin-top: 6px;">
                <table style="width: 100%; border-collapse: collapse; border: 1px solid #d1d5db; font-size: 9.5pt;">
                    <thead>
                        <tr style="background-color: #f3f4f6; color: #374151;">
                            <th
                                style="border: 1px solid #d1d5db; padding: 6px 10px !important; font-weight: bold; text-align: center; width: 45px; vertical-align: middle; line-height: 1.35 !important;">
                                No.</th>
                            <th
                                style="border: 1px solid #d1d5db; padding: 6px 10px !important; font-weight: bold; text-align: left; width: 220px; vertical-align: middle; line-height: 1.35 !important;">
                                Nama Penulis</th>
                            <th
                                style="border: 1px solid #d1d5db; padding: 6px 10px !important; font-weight: bold; text-align: left; vertical-align: middle; line-height: 1.35 !important;">
                                Instansi/Afiliasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $startIndex = ($pageIndex === 0) ? 0 : $firstPageLimit + ($pageIndex - 1) * $subsequentPageLimit;
                        @endphp
                        @foreach($pageAuthors as $authorKey => $author)
                            <tr style="border-bottom: 1px solid #e5e7eb;">
                                <td
                                    style="border: 1px solid #d1d5db; padding: 6px 10px !important; text-align: center; vertical-align: middle; line-height: 1.35 !important;">
                                    {{ $startIndex + $authorKey + 1 }}
                                </td>
                                <td
                                    style="border: 1px solid #d1d5db; padding: 6px 10px !important; text-align: left; font-weight: 600; color: black; vertical-align: middle; line-height: 1.35 !important;">
                                    {{ $author['name'] ?? '' }}
                                </td>
                                <td
                                    style="border: 1px solid #d1d5db; padding: 6px 10px !important; text-align: left; color: #4b5563; vertical-align: middle; line-height: 1.35 !important;">
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