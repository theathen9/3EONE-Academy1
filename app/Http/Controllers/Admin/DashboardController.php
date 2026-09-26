<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Throwable;

class DashboardController extends Controller
{
    public function index()
<<<<<<< HEAD
    {
        try {
            return view('admin.dashboard');
        } catch (Throwable $e) {
            return response()->json([
                'error' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }
}
=======
{
    return 'Admin dashboard controller works';
}
}
>>>>>>> 5671c4ae1b3059ed502cb0372cf4bcf9fb225b15
