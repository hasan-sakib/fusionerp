<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()    { return view('coming-soon', ['title' => 'ReportController']); }
    public function create()   { return view('coming-soon', ['title' => 'ReportController']); }
    public function store()    { return back(); }
    public function show($id)  { return view('coming-soon', ['title' => 'ReportController']); }
    public function edit($id)  { return view('coming-soon', ['title' => 'ReportController']); }
    public function update($id){ return back(); }
    public function destroy($id){ return back(); }
}
