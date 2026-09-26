<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Throwable;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            return response()->json([
                'status' => 'controller-reached',
                'message' => 'Dashboard controller is working',
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'status' => 'controller-error',
                'error' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }
}
