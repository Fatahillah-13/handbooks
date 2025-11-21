<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AuditLog;

class AuditLogController extends Controller
{
    public function index()
    {
        // ambil 100 log terakhir
        $logs = AuditLog::with('user', 'auditable')
            ->orderByDesc('id')
            ->limit(100)
            ->get();

        return response()->json($logs);
    }
}
