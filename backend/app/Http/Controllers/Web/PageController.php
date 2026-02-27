<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class PageController extends Controller
{
    public function dashboard()       { return view('dashboard'); }
    public function healthProfile()   { return view('health-profile'); }
    public function disease(string $slug)
    {
        $disease = \App\Models\Disease::where('slug', $slug)->where('is_active', true)->with('fields')->firstOrFail();
        return view('disease.show', compact('disease'));
    }
    public function digitalTwin()     { return view('digital-twin'); }
    public function simulations()     { return view('simulations'); }
    public function foodImpact()      { return view('food-impact'); }
    public function alerts()          { return view('alerts'); }
    public function history()         { return view('history'); }
    public function ragQuery()        { return view('rag-query'); }
}
