<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class PageController extends Controller
{
    public function dashboard()       { return view('dashboard'); }
    public function healthProfile()   { return view('health-profile'); }
    public function diabetes()        { return view('disease.diabetes'); }
    public function pcod()            { return view('disease.pcod'); }
    public function digitalTwin()     { return view('digital-twin'); }
    public function simulations()     { return view('simulations'); }
    public function foodImpact()      { return view('food-impact'); }
    public function alerts()          { return view('alerts'); }
    public function history()         { return view('history'); }
    public function ragQuery()        { return view('rag-query'); }
}
