<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page introuvable (404) - Kolo Immo</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center px-4">
    <div class="max-w-lg w-full text-center">

        <!-- Logo -->
        <a href="/" class="inline-flex items-center gap-2 mb-10">
            <div class="w-10 h-10 bg-blue-700 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                </svg>
            </div>
            <span class="text-xl font-bold text-blue-700">Kolo <span class="text-orange-400">Immo</span></span>
        </a>

        <!-- 404 Illustration -->
        <div class="mb-8">
            <div class="relative inline-block">
                <div class="text-9xl font-black text-gray-100 select-none leading-none">404</div>
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="w-32 h-32 bg-blue-700 rounded-3xl flex items-center justify-center shadow-xl">
                        <svg class="w-16 h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <h1 class="text-3xl font-bold text-gray-900 mb-3">Page introuvable</h1>
        <p class="text-gray-500 text-lg mb-2">Oups ! La page que vous cherchez n'existe pas ou a été déplacée.</p>
        <p class="text-gray-400 text-sm mb-8">Vérifiez l'URL ou retournez à l'accueil pour trouver votre logement.</p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="/" class="w-full sm:w-auto bg-blue-700 hover:bg-blue-800 text-white font-bold px-8 py-3.5 rounded-xl transition-colors flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Retour à l'accueil
            </a>
            <a href="/properties" class="w-full sm:w-auto border-2 border-gray-200 text-gray-700 hover:bg-gray-50 font-semibold px-8 py-3.5 rounded-xl transition-colors flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                Chercher un logement
            </a>
        </div>

        <div class="mt-10 pt-6 border-t border-gray-200">
            <p class="text-gray-400 text-xs">© 2026 Kolo Immo - Résidences meublées en Afrique de l'Ouest</p>
        </div>
    </div>
</body>
</html>
