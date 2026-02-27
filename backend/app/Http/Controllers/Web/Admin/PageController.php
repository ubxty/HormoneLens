<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;

class PageController extends Controller
{
    public function dashboard()     { return view('admin.dashboard'); }
    public function users()         { return view('admin.users.index'); }
    public function userShow($id)   { return view('admin.users.show', compact('id')); }
    public function simulations()   { return view('admin.simulations'); }
    public function alerts()        { return view('admin.alerts'); }
    public function reports()       { return view('admin.reports'); }
    public function rag()           { return view('admin.rag.index'); }
    public function ragDocument($id){ return view('admin.rag.document', compact('id')); }
}
