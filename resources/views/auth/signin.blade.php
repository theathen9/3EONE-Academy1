@extends('layouts.guest')

@section('title', 'Sign in | 3EONE')

@section('content')

<!-- Background Overlay -->
<div class="min-h-screen w-full bg-black/65">

    <div class="container mx-auto min-h-screen px-4">

        <!-- Center Login Card -->
        <div class="flex min-h-screen items-center justify-center">

            <div class="w-full sm:max-w-md md:max-w-lg">

                <div class="overflow-hidden rounded-xl bg-white shadow-2xl">

                    <div class="px-6 pb-8 pt-8 sm:px-8">

                        <!-- Logo + Application Name -->
                        <div class="text-center">

                            <img
                                src="{{ asset('images/icon.webp') }}"
                                alt="3EONE logo"
                                width="150"
                                height="150"
                                class="signin-icon mx-auto mb-5 block rounded-lg object-cover"
                            >

                            <h5 class="mb-6 text-xl font-bold text-gray-900">
                                3EONE
                            </h5>

                        </div>


                        <!-- General Error -->
                        @if ($errors->any())

                            <div
                                class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-center text-sm font-medium text-red-700"
                                role="alert"
                            >
                                {{ $errors->first() }}
                            </div>

                        @endif


                        <!-- Login Form -->
                        <form
                            method="POST"
                            action="{{ route('auth.login') }}"
                            class="space-y-5"
                            autocomplete="off"
                        >

                            @csrf


                            <!-- Username -->
                            <div class="relative">

                                <input
                                    type="text"
                                    id="username"
                                    name="username"
                                    value="{{ old('username') }}"
                                    autocomplete="username"
                                    placeholder="Username or ID or Email"
                                    required
                                    autofocus
                                    class="peer block w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-gray-900 outline-none transition placeholder:text-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                                >

                                <label
                                    for="username"
                                    class="pointer-events-none absolute left-4 top-3 bg-white px-1 text-gray-400 transition-all
                                    peer-focus:-top-2 peer-focus:text-xs peer-focus:text-blue-600
                                    peer-[:not(:placeholder-shown)]:-top-2
                                    peer-[:not(:placeholder-shown)]:text-xs
                                    peer-[:not(:placeholder-shown)]:text-blue-600"
                                >
                                    Username or ID or Email
                                </label>

                            </div>


                            <!-- Username Validation Error -->
                            <!-- @error('username')
                                <p class="-mt-3 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror -->


                            <!-- Password -->
                            <div class="relative">

                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    autocomplete="current-password"
                                    placeholder="Password"
                                    required
                                    class="peer block w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-gray-900 outline-none transition placeholder:text-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                                >

                                <label
                                    for="password"
                                    class="pointer-events-none absolute left-4 top-3 bg-white px-1 text-gray-400 transition-all
                                    peer-focus:-top-2 peer-focus:text-xs peer-focus:text-blue-600
                                    peer-[:not(:placeholder-shown)]:-top-2
                                    peer-[:not(:placeholder-shown)]:text-xs
                                    peer-[:not(:placeholder-shown)]:text-blue-600"
                                >
                                    Password
                                </label>

                            </div>


                            <!-- Password Validation Error -->
                            @error('password')
                                <p class="-mt-3 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror


                            <!-- Submit -->
                            <button
                                type="submit"
                                id="submitBtn"
                                class="w-full cursor-pointer rounded-lg bg-blue-600 px-4 py-3 text-sm font-bold uppercase tracking-wide text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/30 active:bg-blue-800 disabled:cursor-not-allowed disabled:opacity-60"
                            >
                                Sign in
                            </button>

                            

                        </form>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection


@push('scripts')

<script>
    document.addEventListener('DOMContentLoaded', function () {

        const username = document.getElementById('username');
        const password = document.getElementById('password');
        const form = document.querySelector('form');
        const submitBtn = document.getElementById('submitBtn');

        /*
        |--------------------------------------------------------------------------
        | Username → Password
        |--------------------------------------------------------------------------
        */

        username.addEventListener('keydown', function (event) {

            if (event.key === 'Enter') {

                event.preventDefault();

                password.focus();
            }
        });


        /*
        |--------------------------------------------------------------------------
        | Password → Submit
        |--------------------------------------------------------------------------
        */

        password.addEventListener('keydown', function (event) {

            if (event.key === 'Enter') {

                event.preventDefault();

                form.requestSubmit();
            }
        });


        /*
        |--------------------------------------------------------------------------
        | Prevent double submission
        |--------------------------------------------------------------------------
        */

        form.addEventListener('submit', function () {

            submitBtn.disabled = true;

            submitBtn.textContent = 'Signing in...';

        });

    });
</script>

@endpush
