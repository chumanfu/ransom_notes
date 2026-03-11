<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;

class DatabaseResetController extends Controller
{
    /**
     * Wipe the database, re-run migrations, and seed. Admin only.
     * All users (including the current one) are logged out; seed data includes admin@example.com.
     */
    public function __invoke(): JsonResponse
    {
        Artisan::call('migrate:fresh', ['--seed' => true, '--force' => true]);

        return response()->json([
            'message' => 'Database reset. You will need to log in again (e.g. admin@example.com).',
        ]);
    }
}
