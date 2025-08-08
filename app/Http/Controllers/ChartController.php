<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ChartController extends Controller
{
    
    public function index()
    {
        //
    }
    public function getData()
    {
        $labels = ['0-1 Hours', '1-8 Hours', '8-24 Hours', '>24 Hours', 'No Replies'];
        $data = [81, 9, 4, 4, 2];

        return response()->json([
            'labels' => $labels,
            'data' => $data
        ]);
    }
   
    public function create()
    {
        //
    }

   
    public function store(Request $request)
    {
        //
    }

 
    public function show(string $id)
    {
        //
    }


    public function edit(string $id)
    {
        //
    }

 
    public function update(Request $request, string $id)
    {
        //
    }

 
    public function destroy(string $id)
    {
        //
    }
}
