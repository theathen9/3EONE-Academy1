<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Throwable;

class DashboardController extends Controller
{
    public function index()
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