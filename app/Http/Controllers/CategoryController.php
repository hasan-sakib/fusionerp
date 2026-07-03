<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()    { return view('coming-soon', ['title' => 'CategoryController']); }
    public function create()   { return view('coming-soon', ['title' => 'CategoryController']); }
    public function store()    { return back(); }
    public function show($id)  { return view('coming-soon', ['title' => 'CategoryController']); }
    public function edit($id)  { return view('coming-soon', ['title' => 'CategoryController']); }
    public function update($id){ return back(); }
    public function destroy($id){ return back(); }
}
