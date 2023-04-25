<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestController extends Controller
{


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = 'This is a sample page';
        $items = collect(
            [
                [
                    'id'   => 1,
                    'name' => 'Item 1',
                ],
                [
                    'id'   => 2,
                    'name' => 'Item 2',
                ],
            ]
        );
        return view('test.index', compact('title', 'items'));

    }//end index()


}//end class
