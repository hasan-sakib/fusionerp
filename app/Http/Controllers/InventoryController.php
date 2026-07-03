<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index()    { return view('coming-soon', ['title' => 'InventoryController']); }
    public function create()   { return view('coming-soon', ['title' => 'InventoryController']); }
    public function store()    { return back(); }
    public function show($id)  { return view('coming-soon', ['title' => 'InventoryController']); }
    public function edit($id)  { return view('coming-soon', ['title' => 'InventoryController']); }
    public function update($id){ return back(); }
    public function destroy($id){ return back(); }
}
