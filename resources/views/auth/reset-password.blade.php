<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Reset Password</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>

<div class="container mt-5">

    <div class="row justify-content-center">

        <div class="col-md-5">

            <div class="card shadow-sm">

                <div class="card-body p-4">

                    <h3 class="mb-2">
                        Reset Password
                    </h3>

                    <p class="text-muted">
                        Enter your new password below.
                    </p>

                    @if ($errors->any())
                        <div class="alert alert-danger">

                            <ul class="mb-0">

                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach

                            </ul>

                        </div>
                    @endif

                    <form
                        method="POST"
                        action="{{ route('auth.reset.submit') }}"
                    >

                        @csrf

                        <input
                            type="hidden"
                            name="token"
                            value="{{ $token }}"
                        >

                        <div class="mb-3">

                            <label
                                for="password"
                                class="form-label"
                            >
                                New Password
                            </label>

                            <input
                                type="password"
                                name="password"
                                id="password"
                                class="form-control"
                                required
                                minlength="8"
                                autocomplete="new-password"
                            >

                        </div>

                        <div class="mb-3">

                            <label
                                for="password_confirmation"
                                class="form-label"
                            >
                                Confirm Password
                            </label>

                            <input
                                type="password"
                                name="password_confirmation"
                                id="password_confirmation"
                                class="form-control"
                                required
                                minlength="8"
                                autocomplete="new-password"
                            >

                        </div>

                        <button
                            type="submit"
                            class="btn btn-primary w-100"
                        >
                            Reset Password
                        </button>

                    </form>

                    <div class="text-center mt-3">

                        <a href="{{ route('auth.signin') }}">
                            Back to Sign In
                        </a>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

</body>
</html>