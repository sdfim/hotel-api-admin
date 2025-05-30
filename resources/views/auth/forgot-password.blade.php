@extends('layouts.master-without-nav')
@section('title')
    Forget Password
@endsection
@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/libs/swiper/swiper-bundle.min.css') }}">
@endsection
@section('content')
    <div class="container-fluid">
        <div class="h-screen md:overflow-hidden">
            <div class="flex flex-col items-center w-full">
                <div class="card flex flex-col mx-5 mt-10 p-10">
                    <div class=" mx-auto mb-4">
                        <a href="{{ url('index') }}" class="">
                            <img src="{{ URL::asset('build/images/logo-sm.svg') }}" alt=""
                                 class="h-8 inline"> <span
                                class="text-xl align-middle font-medium ltr:ml-2 rtl:mr-2 dark:text-white">TerraMare</span>
                        </a>
                    </div>

                    <div class="my-auto">
                        <div class="text-center mb-8">
                            <h5 class="text-gray-600 dark:text-gray-100">Reset Password</h5>
                            <p class="text-gray-500 mt-1 dark:text-zinc-100/60">Reset Password with TerraMare.</p>
                        </div>

                        <div class="px-5 py-3 bg-green-500/10  border-2 border-green-500/30 rounded">
                            <p class="text-green-500">Enter your Email and instructions will be sent to you!</p>
                        </div>

                        @if (session('status'))
                            <div class="mb-4 font-medium text-sm text-green-600">
                                {{ session('status') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('password.email') }}" class="mt-4 pt-2">
                            @csrf
                            <div class="mb-6">
                                <label class="text-gray-600 font-medium mb-2 block dark:text-gray-100">Email
                                    <span class="text-red-600">*</span></label>
                                <input type="email" name="email" :value="old('email')" required
                                       class="w-full border-gray-100 rounded placeholder:text-sm py-2 placeholder:text-gray-400 dark:bg-zinc-700/50 dark:border-zinc-600 dark:text-gray-100 dark:placeholder:text-zinc-100/60"
                                       id="email" placeholder="Enter email">
                                @error('email')
                                <span class="text-sm text-red-600">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <button
                                    class="btn border-transparent bg-mandarin-500 w-full py-2.5 text-white w-100 waves-effect waves-light shadow-md shadow-mandarin-200 dark:shadow-zinc-600"
                                    type="submit">Reset
                                </button>
                            </div>
                        </form>

                        <div class="mt-12 text-center">
                            <p class="text-gray-500 dark:text-zinc-100">Remember It ? <a
                                    href="{{ route('login') }}"
                                    class="text-mandarin-500 font-semibold"> Sign In </a></p>
                        </div>
                    </div>

                    <div class=" text-center mt-4">
                        <p class="text-gray-500 relative mb-5 dark:text-gray-100">©
                            <script>
                                document.write(new Date().getFullYear())
                            </script>
                            TerraMare . Crafted with <i class="mdi mdi-heart text-red-400"></i>
                            by TerraMare API
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

    @vite(['resources/js/app.js'])
@endsection
