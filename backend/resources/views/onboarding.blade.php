<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Welcome — HormoneLens</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: #f5f3ff; overflow-x: hidden; }
    </style>
    @viteReactRefresh
    @vite(['resources/js/onboarding-app.jsx'])
</head>
<body>
    <div id="onboarding-root"></div>
</body>
</html>
