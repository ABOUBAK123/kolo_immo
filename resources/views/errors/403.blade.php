<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accès interdit (403) - Kolo Immo</title>
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

        <!-- 403 Illustration -->
        <div class="mb-8">
            <div class="relative inline-block">
                <div class="text-9xl font-black text-gray-100 select-none leading-none">403</div>
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="w-32 h-32 bg-red-600 rounded-3xl flex items-center justify-center shadow-xl">
                        <svg class="w-16 h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <h1 class="text-3xl font-bold text-gray-900 mb-3">Accès interdit</h1>
        <p class="text-gray-500 text-lg mb-2">Vous n'avez pas les permissions nécessaires pour accéder à cette page.</p>
        <p class="text-gray-400 text-sm mb-8">
            {{ isset($exception) && $exception->getMessage() ? $exception->getMessage() : 'Cette section est réservée à certains types d\'utilisateurs ou nécessite une authentification.' }}
        </p>

        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-left mb-8">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <p class="font-semibold text-yellow-800 text-sm mb-1">Que faire ?</p>
                    <ul class="text-yellow-700 text-sm space-y-1">
                        <li>• Si vous n'êtes pas connecté, essayez de vous connecter</li>
                        <li>• Vérifiez que votre compte a les permissions requises</li>
                        <li>• Contactez le support si vous pensez que c'est une erreur</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="/" class="w-full sm:w-auto bg-blue-700 hover:bg-blue-800 text-white font-bold px-8 py-3.5 rounded-xl transition-colors">
                Retour à l'accueil
            </a>
            <a href="/login" class="w-full sm:w-auto border-2 border-gray-200 text-gray-700 hover:bg-gray-50 font-semibold px-8 py-3.5 rounded-xl transition-colors">
                Se connecter
            </a>
        </div>

        <div class="mt-10 pt-6 border-t border-gray-200">
            <p class="text-gray-400 text-xs">© 2026 Kolo Immo - Résidences meublées en Afrique de l'Ouest</p>
        </div>
    </div>
</body>
</html>
