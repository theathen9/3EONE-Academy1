@extends('layouts.guest')

@section('title', 'Sign in | 3EONE')

@section('content')

<div
    class="min-h-screen w-full flex items-center justify-center bg-cover bg-center"
    style="background: rgba(0, 0, 0, 0.64);">
    <div class="max-w-md px-4">

        <div class="col rounded-xl bg-white shadow-xl">
            <div class="px-6 py-8 sm:px-8">

                {{-- Logo --}}
                <img
                    src="{{ asset('images/logo.jpg') }}"
                    alt="3E ONE Logo"
                    class="mx-auto mb-4 h-[125px] w-[125px] rounded-full object-cover">

                {{-- Title --}}
                <h1 class="mb-6 text-center text-xl font-bold text-gray-900">
                    3E ONE
                </h1>

                {{-- Error Message --}}
                @if ($errors->any())
                <div
                    class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-center text-sm text-red-700">
                    {{ $errors->first() }}
                </div>
                @endif

                {{-- Login Form --}}
                <form
                    method="POST"
                    autocomplete="off"
                    class="space-y-4">
                    @csrf

                    {{-- Username --}}
                    <div>
                        <label
                            for="username"
                            class="mb-1 block text-sm font-medium text-gray-700">
                            Username or ID or Email
                        </label>

                        <input
                            type="text"
                            id="username"
                            name="username"
                            value="{{ old('username') }}"
                            placeholder="Username or ID or Email"
                            autocomplete="username"
                            required
                            autofocus
                            class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">

                        @error('username')
                        <p class="mt-1 text-sm text-red-600">
                            {{ $message }}
                        </p>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div>
                        <label
                            for="password"
                            class="mb-1 block text-sm font-medium text-gray-700">
                            Password
                        </label>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Password"
                            autocomplete="current-password"
                            required
                            class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">

                        @error('password')
                        <p class="mt-1 text-sm text-red-600">
                            {{ $message }}
                        </p>
                        @enderror
                    </div>

                    {{-- Submit --}}
                    <button
                        type="submit"
                        id="submitBtn"
                        class="w-full rounded-lg bg-blue-600 px-4 py-3 text-sm font-bold uppercase tracking-wide text-white transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60">
                        Sign in
                    </button>

                </form>

            </div>
        </div>

    </div>

</div>

@endsection