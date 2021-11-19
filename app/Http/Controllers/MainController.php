<?php

namespace App\Http\Controllers;

use App\Models\Assessments;
use App\Models\Surveys;
use App\Models\Mitras;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MainController extends Controller
{
    public function register(Request $request)
    {
        //register logic here

    }

    public function showasses()
    {
        
        return view('survey.show-assessment');
    }
    

    public function show($id)
    {
        $mitra = Mitras::where('email', $id)->first();

        $surveys = Surveys::all();
        $now = new DateTime(date("Y-m-d"));
        $currentsurveys = [];
        foreach ($surveys as $survey) {
            $start = $survey->start_date;
            $end = $survey->end_date;

            $s =  new DateTime($start);
            $e =  new DateTime($end);
            if ($now >= $s && $now <= $e)
                $currentsurveys[] = $survey;
        }

        $mitras = Mitras::all();
        $data = [];

        $label = [];
        $total = [];

        foreach ($data as $key => $value) {
            $label[] = $key;
            $total[] = $value;
        }

        $label = json_encode($label);
        $total = json_encode($total);

        return view('survey.mysurvey', compact('mitra','currentsurveys'));
    }

    public function data(Request $request)
    {
        if ($request->id == 0) {
            return json_encode([
                "draw" => $request->draw,
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => []
            ]);
        } else {
            $survey = Mitras::find('adams.leo@example.org');
            $mitras = $survey->surveys;
            $recordsTotal = count($mitras);
            $recordsFiltered = $mitras->where('name', 'like', '%' . $request->search["value"] . '%')->count();

            $orderColumn = 'name';
            $orderDir = 'desc';
            if ($request->order != null) {
                if ($request->order[0]['dir'] == 'asc') {
                    $orderDir = 'asc';
                } else {
                    $orderDir = 'desc';
                }
                if ($request->order[0]['column'] == '2') {
                    $orderColumn = 'name';
                } else if ($request->order[0]['column'] == '3') {
                    $orderColumn = 'email';
                }
            }
            $searchkeyword = $request->search['value'];
            if ($searchkeyword != null) {
                $mitras = $mitras->filter(function ($q) use (
                    $searchkeyword
                ) {
                    return Str::contains(strtolower($q['name']), strtolower($searchkeyword));
                });
            }
            if ($orderDir == 'asc') {
                $mitras = $mitras->sortBy($orderColumn)->skip($request->start)
                    ->take($request->length);
            } else {
                $mitras = $mitras->sortByDesc($orderColumn)->skip($request->start)
                    ->take($request->length);
            }

            $mitrasArray = array();
            $i = $request->start + 1;
            foreach ($mitras as $mitra) {
                $mitraData = array();
                $mitraData["index"] = $i;
                $mitraData["name"] = $mitra->name;
                $mitraData["email"] = $mitra->email;
                $mitraData["rating"] = $mitra->avgrating();
                $surveys = Surveys::find($mitra->survey_id);
                $mitraData["survey_id"] = $surveys->name;
                $mitraData["id"] = 'adams.leo@example.org';
                $mitrasArray[] = $mitraData;
                $i++;
            }
            return json_encode([
                "draw" => $request->draw,
                "recordsTotal" => $recordsTotal,
                "recordsFiltered" => $recordsFiltered,
                "data" => $mitrasArray
            ]);
        }
    }
}
