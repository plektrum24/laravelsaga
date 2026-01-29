<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    /**
     * Display reports page.
     */
    public function index(Request $request)
    {
        return view('pages.admin.reports.index');
    }
}
