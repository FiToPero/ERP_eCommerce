<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        <!-- Styles / Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
       
    </head>
    <body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] flex p-5 min-w-screen min-h-screen">
        <div class="text-[13px] flex gap-10 min-h-full min-w-full p-12  bg-white dark:bg-[#161615] dark:text-[#EDEDEC] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg">
            <div class="flex flex-col items-center gap-8 w-full max-w-xs my-auto">
                <p class="text-2xl text-black dark:text-[#EDEDEC]">Selecciones a que Web ir</p>
                @auth
                    <a
                        href="{{ route('filament.admin.pages.dashboard') }}"
                        class="w-full text-center px-5 py-2 dark:bg-gray-500 dark:text-[#EDEDEC] border border-[#19140035] hover:border-[#1915014a] text-[#1b1b18] dark:border-[#3E3E3A] dark:hover:border-[#62605b] rounded-sm text-sm leading-normal transition-colors"
                    >
                        Dashboard
                    </a>
                @else
                    <a
                    href="{{ route('filament.admin.auth.login') }}"
                        class="w-full text-center px-5 py-2 dark:bg-gray-500 dark:text-[#EDEDEC] text-[#1b1b18] border border-transparent hover:border-[#19140035] dark:hover:border-[#3E3E3A] rounded-sm text-sm leading-normal transition-colors"
                    >
                        Log in
                    </a>
                    {{-- <a
                        href="{{ route('filament.admin.auth.register') }}"
                        class="w-full text-center px-5 py-2 dark:bg-gray-500 dark:text-[#EDEDEC] border border-[#19140035] hover:border-[#1915014a] text-[#1b1b18] dark:border-[#3E3E3A] dark:hover:border-[#62605b] rounded-sm text-sm leading-normal transition-colors"
                    >
                        Register
                    </a> --}}
                @endauth
            </div>
            <div class="bg-[#fff2f2] dark:bg-[#033221] rounded-lg w-full shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]">
                <span class="p-10 text-[26px] text-[#1b1b18] dark:text-[#EDEDEC] block">Welcome to your ERP_Filament application!</span>
            </div>
        </div>
            
        
    </body>
</html>
