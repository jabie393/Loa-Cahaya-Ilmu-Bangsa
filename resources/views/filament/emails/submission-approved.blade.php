<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submission Approved</title>
    <!-- Custom style to keep button text intact -->
    <style>
        /* Optional: subtle custom styling to preserve exact button text appearance */
        .btn-inline {
            transition: all 0.2s ease;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-green-50 font-sans antialiased">

    <!-- Main container: centered card with soft shadow -->
    <div class="max-w-3xl mx-auto my-12 p-8 bg-white rounded-2xl shadow-xl border border-gray-100">
        
        <!-- Logo/Header Section -->
        <div class="text-center mb-8">
            <img src="http://127.0.0.1:8000/images/logo.png" alt="LOA Cahaya Ilmu Bangsa Logo" style="max-width: 120px; height: auto; margin: 0 auto;">
        </div>
        
        <!-- Header with accent border -->
        <div class="border-l-8 border-green-500 pl-6 py-2 mb-8">
            <h1 class="text-3xl font-extrabold text-gray-800 tracking-tight">Selamat! Pengajuan LOA Anda Telah Disetujui</h1>
        </div>

        <!-- Greeting -->
        <p class="text-gray-700 text-lg mb-4">Halo {{ $user->name }},</p>

        <!-- Approval message -->
        <div class="bg-blue-50 p-5 rounded-xl mb-6 text-gray-700 border-l-4 border-blue-400">
            <p>Kami dengan senang hati menginformasikan bahwa pengajuan Letter of Acceptance (LOA) Anda telah disetujui oleh admin.</p>
        </div>

        <!-- Detail Section -->
        <h2 class="text-xl font-bold text-gray-800 border-b-2 border-gray-200 pb-2 mb-4">Detail Pengajuan:</h2>
        
        <!-- Card-like list with soft background -->
        <div class="bg-gray-50 rounded-xl p-5 mb-6">
            <ul class="space-y-2 text-gray-700">
                <li class="flex flex-wrap"><span class="font-semibold w-36">Judul:</span> {{ $submission->title }}</li>
                <li class="flex flex-wrap"><span class="font-semibold w-36">Nama Penulis:</span> {{ $submission->author_name }}</li>
                <li class="flex flex-wrap"><span class="font-semibold w-36">Institusi:</span> {{ $submission->institution }}</li>
                <li class="flex flex-wrap"><span class="font-semibold w-36">Jurnal:</span> {{ $submission->journal->name ?? 'N/A' }}</li>
                <li class="flex flex-wrap"><span class="font-semibold w-36">Volume:</span> {{ $submission->volume }}</li>
                <li class="flex flex-wrap"><span class="font-semibold w-36">Tanggal LOA:</span> {{ $submission->date_of_loa->format('d M Y') }}</li>
                <li class="flex flex-wrap"><span class="font-semibold w-36">Tanggal Disetujui:</span> {{ $submission->approved_date->format('d M Y') }}</li>
            </ul>
        </div>

        <!-- Download/Print info -->
        <p class="text-gray-700 mb-5">Silakan unduh atau cetak LOA & sertifikat Anda dari sistem kami.</p>

        <!-- Buttons container: using flex wrap, exactly same text and links -->
        <div class="flex flex-wrap gap-3 mb-8">
            <a href="http://127.0.0.1:8000/loa/preview/{{ $submission->id }}" 
               class="inline-block px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-md transition-all duration-200 btn-inline">Letter of Acceptance</a>
            <a href="http://127.0.0.1:8000/ac/preview/{{ $submission->id }}?" 
               class="inline-block px-6 py-2.5 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg shadow-md transition-all duration-200 btn-inline">Author's Certificate</a>
            <a href="http://127.0.0.1:8000/pfc/preview/{{ $submission->id }}" 
               class="inline-block px-6 py-2.5 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg shadow-md transition-all duration-200 btn-inline">Plagiarism-Free Certificate</a>
        </div>

        <!-- Closing remarks -->
        <p class="text-gray-700">Terima kasih atas partisipasi Anda dalam LOA Cahaya Ilmu Bangsa.</p>

        <!-- Signature -->
        <div class="mt-6 pt-4 border-t border-gray-200 text-gray-600">
            <p>Salam,<br>Tim LOA Cahaya Ilmu Bangsa</p>
        </div>
    </div>

</body>
</html>