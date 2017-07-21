<?php

namespace App\Http\Controllers;

use App\College;
use App\Grade;
use App\Interest;

class ExampleController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function getColleges()
    {
        $colleges = College::all();

        $data = [];

        foreach ($colleges as $college)
        {
            $data[] = $college->name;
        }

        return response()->json(['code' => 200,'message'=>'successful.','data'=>$data]);
    }

    public function getGrades()
    {
        $grades = Grade::all();

        $data = [];

        foreach ($grades as $grade)
        {
            $data[] = $grade->name;
        }

        return response()->json(['code' => 200,'message'=>'successful.','data'=>$data]);
    }

    public function getInterests() {
        $interests = Interest::all();

        $datas = [];

        foreach ($interests as $interest) {
            $data = [];
            $data['id'] = $interest->id;
            $data['name'] = $interest->name;
            $data['description'] = $interest->description;

            $datas[] = $data;
        }

        return response()->json(['code' => 200,'message'=>'successful.','data'=>$datas]);
    }
}
