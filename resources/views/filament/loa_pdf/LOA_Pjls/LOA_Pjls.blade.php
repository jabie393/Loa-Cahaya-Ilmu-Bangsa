<html>

<head>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@400;500;700&display=swap"
        rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1e3a8a',
                        secondary: '#854d0e',
                        background: 'white',
                    },
                    fontFamily: {
                        inter: ["Inter", "system-ui", "sans-serif"],
                        playfair: ['"Playfair Display"', "serif"],
                    },
                },
            },
        };

        window.downloadPDF = async function () {
            const {
                jsPDF
            } = window.jspdf;
            const element = document.querySelector('#capture-area');
            const btn = document.querySelector('button');

            btn.style.opacity = '0.5';
            btn.innerText = 'Processing...';

            try {
                const canvas = await html2canvas(element, {
                    scale: 3,
                    useCORS: true,
                    allowTaint: true,
                    backgroundColor: '#ffffff',
                    onclone: (clonedDoc) => {
                        clonedDoc.querySelector('#download-btn').style.display = 'none';
                    }
                });

                const imgData = canvas.toDataURL('image/png');
                const pdf = new jsPDF({
                    orientation: 'portrait',
                    unit: 'mm',
                    format: 'a4'
                });

                pdf.addImage(imgData, 'PNG', 0, 0, 210, 297);
                pdf.save(`LOA-{{ $record->author_name }}.pdf`);
            } catch (e) {
                console.error(e);
                window.print();
            } finally {
                btn.style.opacity = '1';
                btn.innerText = 'Download PDF';
            }
        };
    </script>
    <style type="text/tailwindcss">
        @page {
            size: A4;
            margin: 0;
        }

        @media print {
            body {
                @apply bg-white;
            }

            .print-a4 {
                margin: 0px !important;
                box-shadow: none !important;
            }
        }
    </style>
</head>
<button id="download-btn" onclick="downloadPDF()"
    class="bg-primary fixed right-8 top-8 z-50 flex items-center gap-2 rounded-xl px-6 py-3 font-bold text-white shadow-2xl transition-transform hover:scale-105 active:scale-95 print:hidden">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd"
            d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
            clip-rule="evenodd" />
    </svg>
    Download PDF
</button>

<body
    class="font-inter print-a4 mx-auto my-[20mm] box-border max-h-[297mm] w-[210mm] bg-white text-[10.5pt] text-black shadow-[0_0_10px_rgba(0,0,0,0.2)]">
    <div id="capture-area" class="relative h-[297mm] w-[210mm] bg-white px-[60pt] pb-[50pt] pt-[45pt]">

        <!-- Header Section -->
        <table class="w-full border-b border-slate-300 pb-3 mb-5" style="border-collapse: collapse; width: 100%;">
            <tr>
                <td style="width: 15%; vertical-align: top; text-align: left; padding-bottom: 10px;">
                    <img src="{{ asset('assets/pjls_assets/pjls_left_logo.png') }}" style="width: 80px; height: auto;"
                        alt="PJLS Logo" />
                </td>
                <td
                    style="width: 85%; text-align: left; vertical-align: middle; font-family: 'Inter', sans-serif; padding-left: 20px; padding-bottom: 10px;">
                    <h2 class="font-bold text-black uppercase"
                        style="margin: 0; font-size: 14pt; font-weight: 800; letter-spacing: 0.2px;">
                        PAKISTAN JOURNAL OF LIFE AND SOCIAL SCIENCES
                    </h2>
                    <div class="text-black uppercase font-medium mt-1" style="font-size: 9pt; font-weight: 500;">
                        PRINT ISSN : 1727-4915, ONLINE ISSN 2221-7630
                    </div>
                    <div class="text-slate-500 mt-1" style="font-size: 9.5pt;">
                        available at <a href="https://ijefijournal.com"
                            style="color: #2563eb; text-decoration: underline;">https://ijefijournal.com</a>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Document Title -->
        <div class="text-center mt-5 mb-6">
            <h1 class="font-bold text-primary" style="font-size: 18pt; font-weight: 700; letter-spacing: 0.5px;">
                LETTER OF ACCEPTANCE (LOA)
            </h1>
        </div>

        <!-- Body Content Area -->
        <div class="leading-relaxed text-justify space-y-3.5 text-[10.5pt]">
            <p><strong>Date:</strong>
                {{ $record->approved_date ? $record->approved_date->format('F d Y') : now()->format('F d Y') }}</p>
            <p class="pb-1"><strong>Ref Number:</strong>
                {{ $record->created_at->format('Y') }}/PJLS/LOA-{{ sprintf('%03d', $record->id) }}</p>

            <p>Dear Author(s),</p>

            <p class="font-bold text-[11pt]">{{ $record->formatted_authors }}</p>

            <p>We are pleased to inform you that your manuscript entitled:</p>

            <p
                class="pl-4 font-bold italic text-slate-800 text-[11pt] border-l-4 border-slate-300 py-1 my-2 bg-slate-50/50 pr-2 uppercase">
                "{{ $record->title }}"
            </p>

            <p>has been reviewed and accepted for publication in:</p>

            <!-- Metadata Table -->
            <table class="w-full my-4 text-[10pt]" style="border-collapse: collapse; width: 100%;">
                <tr class="border-b border-slate-100">
                    <td style="width: 150px; padding: 5px 0; font-weight: 700; vertical-align: top; color: #475569;">
                        Journal Name</td>
                    <td style="width: 20px; padding: 5px 0; text-align: center; vertical-align: top; color: #475569;">:
                    </td>
                    <td
                        style="padding: 5px 0; vertical-align: top; font-weight: 700; color: #0f172a; text-transform: uppercase;">
                        PAKISTAN JOURNAL OF LIFE AND SOCIAL SCIENCES
                    </td>
                </tr>
                <tr class="border-b border-slate-100">
                    <td style="padding: 5px 0; font-weight: 700; vertical-align: top; color: #475569;">ISSN</td>
                    <td style="padding: 5px 0; text-align: center; vertical-align: top; color: #475569;">:</td>
                    <td style="padding: 5px 0; vertical-align: top; color: #0f172a; text-transform: uppercase;">
                        PRINT ISSN : 1727-4915, ONLINE ISSN 2221-7630
                    </td>
                </tr>
                <tr class="border-b border-slate-100">
                    <td style="padding: 5px 0; font-weight: 700; vertical-align: top; color: #475569;">Volume/Issue
                    </td>
                    <td style="padding: 5px 0; text-align: center; vertical-align: top; color: #475569;">:</td>
                    <td style="padding: 5px 0; vertical-align: top; color: #0f172a;">
                        {{ $record->volume ?: 'Vol 16 No 1 (' . ($record->approved_date ? $record->approved_date->format('Y') : now()->format('Y')) . ')' }}
                    </td>
                </tr>
                <tr class="border-b border-slate-100">
                    <td style="padding: 5px 0; font-weight: 700; vertical-align: top; color: #475569;">Publication Date
                    </td>
                    <td style="padding: 5px 0; text-align: center; vertical-align: top; color: #475569;">:</td>
                    <td style="padding: 5px 0; vertical-align: top; color: #0f172a;">
                        {{ $record->approved_date ? $record->approved_date->format('F d Y') : now()->format('F d Y') }}
                    </td>
                </tr>
                <tr class="border-b border-slate-100">
                    <td style="padding: 5px 0; font-weight: 700; vertical-align: top; color: #475569;">Publication Link
                    </td>
                    <td style="padding: 5px 0; text-align: center; vertical-align: top; color: #475569;">:</td>
                    <td style="padding: 5px 0; vertical-align: top; color: #2563eb; word-break: break-all;">
                        {{ $record->publication_link ?: 'https://pjlsedu.com/index.php/pjls/article/view/' . $record->ojs_submission_id }}
                    </td>
                </tr>
            </table>

            <p>This acceptance is based on the recommendation of the reviewers and the editorial board.</p>
            <p>The manuscript is considered eligible for publication according to the journal's standards and policies.
            </p>
            <p class="pb-3">Thank you for your valuable contribution. We look forward to publishing your work.</p>

            <!-- Signature Section -->
            <div class="mt-8 pt-4" style="page-break-inside: avoid;">
                <p class="m-0">Sincerely,</p>
                <p class="m-0">Editor-in-Chief</p>

                <div class="my-3">
                    <img src="{{ asset('assets/pjls_assets/pjls_signature.png') }}" style="height: 52px; width: auto;"
                        alt="Signature" />
                </div>

                <p class="font-bold text-slate-900 text-[11pt] m-0">Dr. Jemi Philip</p>
                <p class="font-bold text-slate-900 text-[10pt] m-0">Publisher</p>
            </div>
        </div>

    </div>
</body>

</html>