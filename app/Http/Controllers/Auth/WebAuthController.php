<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;

class WebAuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    /**
     * Show login page
     */
    public function show()
    {
        return view('auth.signin');
    }

    /**
     * Process web login
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'username' => [
                'required',
                'string',
                'max:255',
            ],

            'password' => [
                'required',
                'string',
            ],
        ]);

        $user = $this->authService->authenticate(
            $validated['username'],
            $validated['password']
        );

        if (!$user) {
            return back()
                ->withInput(
                    $request->only('username')
                )
                ->withErrors([
                    'username' => 'Invalid username or password.',
                ]);
        }

        $this->authService->loginWeb(
            $request,
            $user
        );

        return match (strtolower($user->role->role_name)) {

            'admin' =>
                redirect()->route('admin.dashboard'),

            'accountant' =>
                redirect()->route('account.dashboard'),

            'teacher' =>
                redirect()->route('teacher.dashboard'),

            'student' =>
                redirect()->route('student.dashboard'),

            default =>
                abort(403, 'Invalid user role.'),
        };
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $this->authService->logoutWeb($request);

        return redirect()
            ->route('auth.signin');
    }
}

