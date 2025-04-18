<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\TestNotification;

class TestNoti extends Controller
{
    public function store(Request $request)
    {
        $validation = $request->validate([
            'author' => 'required',
            'title' => 'required'

        ]);
        if(!$validation){
            return response()->json(['validate error'=>'validate error' , 422 ]);
        }
        try{

            // $Post = Post::create([
            //     'author'=>$request->author,
            //     'title'=>$request->title
            // ]);
            event(new TestNotification( [
                'author' => $request->author,
                'title' => $request->title,
            ]));
            return response()->json(['data'=>'successfully ']);


        }catch(\Exception $e){
            return response()->json(['erorr'=>$e->getMessage() , 500 ]);
        }

    }
}
