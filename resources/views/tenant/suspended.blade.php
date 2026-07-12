<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Suspended — FusionERP</title>
    @vite(['resources/css/app.css'])
</head>
<body class="flex h-full flex-col items-center justify-center bg-gray-50 px-6 dark:bg-gray-950">
    <div class="mx-auto max-w-md text-center">
        <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
            <svg class="h-8 w-8 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Account Suspended</h1>

        <p class="mt-3 text-gray-600 dark:text-gray-400">
            The account <strong class="font-medium text-gray-900 dark:text-white">{{ $tenant->name }}</strong>
            has been suspended. Please contact support to restore access.
        </p>

        <p class="mt-6 text-sm text-gray-500 dark:text-gray-500">
            If you believe this is an error, please reach out to
            <a href="mailto:support@fusionerp.com"
               class="text-indigo-600 hover:underline dark:text-indigo-400">
                support@fusionerp.com
            </a>.
        </p>
    </div>
</body>
</html>
