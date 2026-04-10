<html>
  <head>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&family=Noto+Serif:ital,wght@0,100..900;1,100..900&display=swap"
      rel="stylesheet" />
    <meta content="text/html; charset=UTF-8" http-equiv="content-type" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            fontFamily: {
              manrope: ["Manrope", "sans-serif"],
              noto: ["Noto Serif Display", "serif"],
              calibri: ["Calibri", "sans-serif"],
              playfair: ['"Playfair Display"', "serif"],
            },
          },
        },
      };
    </script>
    <style type="text/tailwindcss">
      @import url(https://themes.googleusercontent.com/fonts/css?kit=1n8us-eEzYYhbTLMubR0ZA1KNGra13VIU0iL8fHG2AX-SYK_Ln3uQOimoUBe-l5o);
      /* Base layer fully removed; cascading utilities set on body instead */
      @page {
        size: A4;
        margin: 0;
        max-height: A4;
      }
      @media print {
        body {
          @apply bg-white;
        }
        .print-a4 {
          margin: 0px !important;
          box-shadow: none !important;
          min-height: 0 !important;
          height: auto !important;
          width: 100% !important;
          overflow: hidden !important;
        }
      }
    </style>
  </head>
  <body
    class="text-black text-[11pt] font-calibri print-a4 bg-white w-[210mm] max-h-[297mm] box-border mx-auto my-[10mm] pt-[105.8pt] px-[72pt] pb-[72pt] shadow-[0_0_10px_rgba(0,0,0,0.2)]">
    <span
      class="overflow-hidden inline-block w-[639.5px] h-[639.5px] absolute opacity-10 z-[-10]">
      <img
        alt=""
        src="{{ asset('assets/logo.png') }}"
        class="w-[639.5px] h-[639.5px]"
        title="" />
    </span>
    <div class="flex w-[165mm] justify-between absolute top-20">
      <div>
        <div class="text-left">
          <p class="text-blue-900 font-bold font-manrope text-[18pt] mb-[-4pt]">
            CAHAYA ILMU BANGSA INSTITUTE
          </p>
          <p class="text-yellow-700 font-bold font-manrope text-[8pt] pb-[5pt]">
            Biro Penelitian, Publikasi, dan Pengabdian Kepada Masyarakat
          </p>
          <p class="font-manrope text-[7pt]">
            KEMENKUMHAM AHU-0018912-AH.01.14
          </p>
          <p class="font-manrope text-[7pt]">
            Perum Puri Kartika Asri Blok 2 A2 Malang
          </p>
          <p class="font-manrope text-[7pt]">
            e-mail:
            <a href="mailto:admin@cahayailmubangsa.institute">
              admin@cahayailmubangsa.institute
            </a>
          </p>
        </div>
      </div>
      <div class="text-right">
        <p class="text-blue-900 font-bold font-manrope text-[18pt] mb-[-4pt]">
          JAYABAMA
        </p>
        <p class="text-yellow-700 font-bold font-manrope text-[6.7pt] pb-[5pt]">
          JURNAL PEMINAT OLAHRAGA
        </p>
      </div>
    </div>
    <div class="pt-10">
      <p class="py-0 leading-[1.079]">
        <span class="text-blue-900 font-bold text-[18pt] font-manrope">
          LETTER OF ACCEPTANCE
        </span>
      </p>
      <p class="py-0 leading-[1.079]">
        <span class="text-yellow-700 font-bold text-[12pt] font-manrope">
          NO: 2025/CIB/LOA
        </span>
      </p>
    </div>
    <div class="pt-10 pb-1">
      <p class="py-0 leading-[1.079] text-left">
        <span class="text-black font-bold text-[12pt] font-noto">
          Assalamualaikum Wr. Wb.
        </span>
      </p>
      <p class="py-0 leading-[1.079] text-left">
        <span class="text-gray-700 italic text-[12pt] font-noto">
          Bersama surat ini, kami menerangkan bahwa artikel dengan keterangan
          naskah berikut
        </span>
      </p>
    </div>
    <table class="border-collapse mr-auto">
      <tr class="my-4 flex flex-col">
        <td class="border-0 align-top w-[91.9pt]" colspan="1" rowspan="1">
          <p class="py-0 leading-none text-left">
            <span
              class="text-blue-900 font-bold text-[11pt] font-noto uppercase">
              Judul :
            </span>
          </p>
        </td>
        <td class="border-0 align-top w-full" colspan="2" rowspan="1">
          <p class="py-0 leading-none text-left">
            <span class="text-black font-normal text-[11pt] font-noto">
              {{ $record->title }}
            </span>
          </p>
        </td>
      </tr>
      <tr class="my-4 flex flex-col">
        <td class="border-0 align-top w-[91.9pt]" colspan="1" rowspan="1">
          <p class="py-0 leading-none text-left">
            <span
              class="text-blue-900 font-bold text-[11pt] font-noto uppercase">
              Author :
            </span>
          </p>
        </td>
        <td class="border-0 align-top w-[367.9pt]" colspan="2" rowspan="1">
          <p class="py-0 leading-none text-left">
            <span class="text-black font-normal text-[11pt] font-noto">
              {{ $record->author_name }}
            </span>
          </p>
        </td>
      </tr>
      <tr class="my-4 flex flex-col">
        <td class="border-0 align-top w-[91.9pt]" colspan="1" rowspan="1">
          <p class="py-0 leading-none text-left">
            <span
              class="text-blue-900 font-bold text-[11pt] font-noto uppercase">
              Instansi :
            </span>
          </p>
        </td>
        <td class="border-0 align-top w-[367.9pt]" colspan="1" rowspan="1">
          <p class="py-0 leading-none text-left">
            <span class="text-black font-normal text-[11pt] font-noto">
              {{ $record->institution }}
            </span>
          </p>
        </td>
      </tr>
      <tr class="my-4 flex flex-col">
        <td class="border-0 align-top w-full" colspan="2" rowspan="1">
          <p class="py-0 leading-none text-left">
            <span
              class="text-blue-900 font-bold text-[11pt] font-noto uppercase">
              Korespondensi :
            </span>
          </p>
        </td>
        <td class="border-0 align-top w-full" colspan="1" rowspan="1">
          <p class="py-0 leading-none text-left">
            <a
              href="mailto:[EMAIL_ADDRESS]"
              class="text-black font-normal text-[11pt] font-noto">
              {{ $record->email }}
            </a>
          </p>
        </td>
      </tr>
      <tr class="my-4 flex flex-col">
        <td class="border-0 align-top w-full" colspan="1" rowspan="1">
          <p class="py-0 leading-none text-left">
            <span
              class="text-blue-900 font-bold text-[11pt] font-noto uppercase">
              Jurnal :
            </span>
          </p>
        </td>
        <td class="border-0 align-top w-full" colspan="1" rowspan="1">
          <p class="py-0 leading-none text-left">
            <span class="text-black font-normal text-[11pt] font-noto">
              {{ $record->journal->name }}
            </span>
          </p>
        </td>
      </tr>
      <tr class="my-4 flex flex-col">
        <td class="border-0 align-top w-full" colspan="1" rowspan="1">
          <p class="py-0 leading-none text-left">
            <span
              class="text-blue-900 font-bold text-[11pt] font-noto uppercase">
              Volume :
            </span>
          </p>
        </td>
        <td class="border-0 align-top w-full" colspan="1" rowspan="1">
          <p class="py-0 leading-none text-left">
            <span class="text-black font-normal text-[11pt] font-noto">
              {{ $record->volume }}
            </span>
          </p>
        </td>
      </tr>
      <tr class="my-4 flex flex-col">
        <td class="border-0 align-top w-full" colspan="2" rowspan="1">
          <p class="py-0 leading-none text-left">
            <span
              class="text-blue-900 font-bold text-[11pt] font-noto uppercase">
              Link Terbitan :
            </span>
          </p>
        </td>
        <td class="border-0 align-top w-full" colspan="1" rowspan="1">
          <p class="py-0 leading-none text-left">
            <a
              href="https://cibangsa.com/index.php/jayabamajournal/index"
              class="text-black font-normal text-[11pt] font-noto">
              {{ $record->publication_link }}
            </a>
          </p>
        </td>
      </tr>
    </table>
    <div class="pt-5 pb-5">
      <p class="pt-0 pb-[8pt] leading-[1.079] text-left text-justify">
        <span class="text-black font-normal text-[12pt] font-noto italic">
          Berstatus ACCEPTED untuk dipublish. Keputusan ini dibuat sebagai tanda
          bahwa naskah yang bersangkutan telah lolos plagiarism checker. Dan LoA
          ini dibuat sebagai bukti bahwa author telah menyelesaikan APC yang
          telah ditetapkan oleh pengelola jurnal.
          <br />
          LOA Berlaku jika dilengkapi link dan pdf publish. Hubungi kami di
          <br />
          admin_jurnal@cahayailmubangsa.institute jika ada pertanyaan lebih
          lanjut, terima kasih.
        </span>
      </p>
    </div>
    <div class="pb-5"> 
      <p class="pt-0 pb-[8pt] leading-[1.079] text-left ml-[318.9pt]">
        <span class="text-black font-bold text-[11pt] font-noto">
          Malang, {{ $record->approved_date?->format('d F Y') }}
        </span>
      </p>
      <p class="pt-0 pb-[8pt] leading-[1.079] text-left ml-[318.9pt]">
        <span class="overflow-hidden inline-block w-[73.33px] h-[73.33px]">
          <img
            alt=""
            src="{{ asset('assets/qrcode.png') }}"
            class="w-[73.33px] h-[73.33px]"
            title="" />
        </span>
      </p>
      <p class="ml-[318.9pt] py-0 leading-[1.079] text-left">
        <span class="font-bold font-playfair text-black text-[11pt]">
          Drs. H. M. Danam Priambodo, M.Pd., Ph.D.
        </span>
      </p>
      <p class="ml-[318.9pt] py-0 leading-[1.079] text-left">
        <span class="font-bold font-playfair">Director</span>
      </p>
    </div>
  </body>
</html>
