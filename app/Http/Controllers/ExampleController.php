<?php

namespace App\Http\Controllers;

use App\Province;
use App\City;
use App\College;
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

    public function getProvinces() {
        $provinces = Province::all();

        $datas = [];

        foreach ($provinces as $province) {
            $data = [];
            $data['id'] = $province->id;
            $data['name'] = $province->name;

            $datas[] = $data;
        }

        return response()->json(['code' => 200,'message'=>'successful.','data'=>$datas]);
    }

    public function getCities(Request $request) {
        $id = $request->get('id');

        $cities = City::where('province_id', $id)->get();
        
        $datas = [];

        foreach ($cities as $city) {
            $data = [];
            $data['id'] = $city->id;
            $data['name'] = $city->name;

            $datas[] = $data;
        }

        return response()->json(['code' => 200,'message'=>'successful.','data'=>$datas]);
    }

    public function getColleges(Request $request) {
        $id = $request->get('id');

        $colleges = College::where('city_id', $id)->get();
        
        $datas = [];

        foreach ($colleges as $college) {
            $data = [];
            $data['id'] = $college->id;
            $data['name'] = $college->name;

            $datas[] = $data;
        }

        return response()->json(['code' => 200,'message'=>'successful.','data'=>$datas]);
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

    public
}
