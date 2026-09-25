<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Forgot Password</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>

<div class="container mt-5">

    <div class="row justify-content-center">

        <div class="col-md-5">

            <div class="card shadow-sm">

                <div class="card-body p-4">

                    <h3 class="mb-2">
                        Forgot Password?
                    </h3>

                    <p class="text-muted">
                        Enter your email address and we'll help you reset your password.
                    </p>

                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if (session('reset_url'))
                        <div class="alert alert-info">

                            <strong>Development reset link:</strong>

                            <div class="mt-2">
                                <a
                                    href="{{ session('reset_url') }}"
                                    class="text-break"
                                >
                                    {{ session('reset_url') }}
                                </a>
                            </div>

                        </div>
                    @endif

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
                        action="{{ route('auth.forgot.submit') }}"
                    >

                        @csrf

                        <div class="mb-3">

                            <label
                                for="email"
                                class="form-label"
                            >
                                Email Address
                            </label>

                            <input
                                type="email"
                                name="email"
                                id="email"
                                class="form-control"
                                value="{{ old('email') }}"
                                required
                                autofocus
                            >

                        </div>

                        <button
                            type="submit"
                            class="btn btn-primary w-100"
                        >
                            Send Reset Link
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