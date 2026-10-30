<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - LuxeCart</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                    colors: {
                        primary: '#0b3506',
                        secondary: '#6bc112',
                        accent: '#e80d0d',
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md p-8 bg-white rounded-xl shadow-xl shadow-secondary/20">
        <div class="mb-10 text-center">
            <div class="inline-block p-4 bg-primary/10 rounded-xl mb-4 ring-8 ring-primary/5">
                <img src="{{ asset('assets/images/logo.png') }}" alt="Jhurio" class="h-12 w-auto object-contain">
            </div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Welcome Back</h1>
            <p class="text-gray-500">Sign in to manage your store</p>
        </div>

        @if ($errors->any())
            <div class="bg-red-50 text-red-500 p-4 rounded-xl mb-6 text-sm">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="/admin/login" method="POST" class="space-y-6">
            @csrf
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                <input type="password" name="password" id="password" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember_me" name="remember" type="checkbox" class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded-lg">
                    <label for="remember_me" class="ml-2 block text-sm text-gray-900">
                        Remember me
                    </label>
                </div>
            </div>

            <div>
                <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-primary hover:shadow-lg hover:shadow-primary/30 transition-all transform hover:-translate-y-0.5">
                    Sign in
                </button>
            </div>
        </form>
    </div>
</body>
</html>
