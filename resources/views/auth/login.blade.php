@extends('layouts.master-without-nav')
@section('title')
    Login
@endsection
@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/libs/swiper/swiper-bundle.min.css') }}">
@endsection
@section('content')
    <div class="container-fluid">
        <div class="h-screen md:overflow-hidden">
            <div class="flex flex-col items-center w-full">
                <div class="card flex flex-col mx-5 mt-10 p-10" style="min-width: 500px;">
                    <div class="mx-auto mb-4">
                        <a href="{{ url('index') }}" class="">
                            <img src="{{ asset('images/firm-logo.png') }}" alt="" class="h-8 inline">
                        </a>
                    </div>

                    <div class="my-auto">
                        <div class="text-center">
                            <h5 class="text-gray-600 dark:text-gray-100">Welcome Back !</h5>
                            <p class="text-gray-500 dark:text-gray-100/60 mt-1">Sign in to continue to <?= env('APP_NAME'); ?>.</p>
                        </div>
                        @if (session('status'))
                            <div class="">
                                {{ session('status') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}" class="mt-4 pt-2">
                            @csrf
                            <div class="mb-4">
                                <label for="email"
                                       class="text-gray-600 dark:text-gray-100 font-medium mb-2 block">Email
                                    <span class="text-red-600">*</span></label>
                                <input type="email" name="email"
                                       class="w-full rounded placeholder:text-sm py-2 border-gray-100 dark:bg-zinc-700/50 dark:border-zinc-600 dark:text-gray-100 dark:placeholder:text-zinc-100/60"
                                       id="email" placeholder="Enter email" required>
                                @error('email')
                                <span class="text-sm text-red-600">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <div class="flex">
                                    <div class="flex-grow-1">
                                        <label for="password"
                                               class="text-gray-600 dark:text-gray-100 font-medium mb-2 block">Password
                                            <span class="text-red-600">*</span></label>
                                    </div>
                                    @if (Route::has('password.request'))
                                        <div class="ltr:ml-auto rtl:mr-auto">
                                            <a href="{{ route('password.request') }}"
                                               class="text-gray-500 dark:text-gray-100">Forgot
                                                password?</a>
                                        </div>
                                    @endif
                                </div>

                                <div class="flex" x-data="{ inputType: 'password' }">
                                    <input :type="inputType" name="password" id="password"
                                           class="w-full rounded ltr:rounded-r-none rtl:rounded-l-none placeholder:text-sm py-2 border-gray-100 dark:bg-zinc-700/50 dark:border-zinc-600 dark:text-gray-100 dark:placeholder:text-zinc-100/60"
                                           placeholder="Enter password" aria-label="Password"
                                           aria-describedby="password-addon" required>
                                    <button
                                        class="bg-gray-50 px-4 rounded ltr:rounded-l-none rtl:rounded-r-none border border-gray-100 ltr:border-l-0 rtl:border-r-0 dark:bg-zinc-700 dark:border-zinc-600 dark:text-gray-100"
                                        type="button" id="password-addon"
                                        @click="inputType === 'password' ? inputType = 'text' :  inputType = 'password'"
                                    >
                                        <i class="mdi"
                                           :class="inputType === 'password' ? 'mdi-eye-outline' : 'mdi-eye-off-outline'"></i>
                                    </button>
                                    @error('password')
                                    <span class="text-sm text-red-600">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-6">
                                <div class="col">
                                    <div>
                                        <input type="checkbox" name="remember" id="remember"
                                               class="h-4 w-4 border border-gray-300 rounded bg-white checked:bg-blue-600 checked:border-blue-600 focus:outline-none transition duration-200 mt-1 align-top bg-no-repeat bg-center bg-contain ltr:float-left rtl:float-right ltr:mr-2 rtl:ml-2 cursor-pointer focus:ring-offset-0"
                                               checked id="exampleCheck1">
                                        <label class="align-middle text-gray-600 dark:text-gray-100 font-medium"
                                               for="remember">
                                            Remember me
                                        </label>
                                    </div>
                                </div>

                            </div>
                            <div class="mb-3">
                                <button
                                    class="btn border-transparent bg-maintheme-500 w-full py-2.5 text-white w-100 waves-effect waves-light shadow-md shadow-maintheme-200 dark:shadow-zinc-600"
                                    type="submit">Log In
                                </button>
                            </div>
                        </form>

                    </div>

                    <div class=" text-center mt-4">
                        <p class="text-gray-500 dark:text-gray-100 relative mb-5">©
                            <script>
                                document.write(new Date().getFullYear())
                            </script>
                            <a href="https://www.cabinselect.com/" class="text-maintheme-500 underline">CabinSelect</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script src="{{ URL::asset('build/libs/swiper/swiper-bundle.min.js') }}"></script>

    <script src="{{ URL::asset('build/js/pages/login.init.js') }}"></script>
@endsection
