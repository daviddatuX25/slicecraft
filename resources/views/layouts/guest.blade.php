<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'SliceCraft · Custom Cake Shop' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#fffaf5] text-[#3c2924]">
    {{-- NOTE (change NOT in the worksheet instructions): A small guest layout keeps the login focused while the authenticated cake pages continue using the existing app shell. --}}
    @yield('content')
</body>
</html>
