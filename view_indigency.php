<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Certificate of Indigency</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none; }
            .print-area { border: none !important; box-shadow: none !important; }
        }
    </style>
</head>
<body class="bg-gray-50 font-sans antialiased">

    <div class="max-w-4xl mx-auto py-10 px-4">
        <div class="flex justify-between items-center mb-6 no-print">
            <a href="view_records.php" class="text-gray-600 hover:text-black flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                Back to Records
            </a>
            <button onclick="window.print()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                Print Certificate
            </button>
        </div>

        <div class="bg-white shadow-xl border border-gray-200 p-12 print-area relative">
            <div class="absolute inset-0 flex items-center justify-center opacity-5 pointer-events-none">
                <img src="https://via.placeholder.com/400" alt="Seal" class="w-96">
            </div>

            <div class="text-center mb-10">
                <p class="uppercase font-semibold text-sm">Republic of the Philippines</p>
                <p class="uppercase font-semibold text-sm">Province of [Province Name]</p>
                <p class="uppercase font-semibold text-sm">City/Municipality of [City Name]</p>
                <p class="uppercase font-bold text-lg mt-1 text-blue-900">Barangay [Barangay Name]</p>
                <div class="h-1 w-full bg-blue-900 mt-4"></div>
                <h1 class="text-3xl font-serif font-bold mt-8 tracking-widest uppercase italic">Certificate of Indigency</h1>
            </div>

            <div class="space-y-6 text-gray-800 leading-relaxed text-lg">
                <p class="font-bold">TO WHOM IT MAY CONCERN:</p>

                <p class="indent-12">
                    This is to certify that <span class="font-bold border-b border-black px-2">JUAN DELA CRUZ</span>, 
                    of legal age, Filipino, and a resident of <span class="font-bold border-b border-black px-2">Purok 4, Barangay Poblacion</span>, 
                    is one of the indigents in this community.
                </p>

                <p class="indent-12">
                    This certification is being issued upon the request of the above-named person for 
                    <span class="font-bold italic underline">Educational Assistance / Medical Requirement</span> 
                    and for whatever legal purpose it may serve.
                </p>

                <p class="indent-12">
                    Issued this <span class="font-bold">10th day of March, 2026</span> at the Office of the Barangay Chairman.
                </p>
            </div>

            <div class="mt-20 flex justify-between">
                <div class="text-center">
                    <div class="w-48 border-b border-black mb-1"></div>
                    <p class="text-sm">Specimen Signature</p>
                </div>
                
                <div class="text-center">
                    <p class="font-bold underline uppercase text-lg">Hon. Ricardo Dalisay</p>
                    <p class="text-sm font-semibold">Punong Barangay</p>
                </div>
            </div>

            <div class="mt-16 text-xs text-gray-400 flex justify-between items-end">
                <p>Note: Not valid without official dry seal.</p>
                <div class="w-24 h-24 border-2 border-dashed border-gray-300 rounded-full flex items-center justify-center">
                    Dry Seal
                </div>
            </div>
        </div>
    </div>

</body>
</html>