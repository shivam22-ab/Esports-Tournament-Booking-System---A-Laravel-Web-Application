<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Points;
use App\Models\Results;
use App\Models\Tournament;
use App\Models\Team;
use App\Models\Game;
use App\Models\History;
use Illuminate\Support\Facades\Session;

class PointsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $points = Points::where('user_id',auth()->user()->id)->get();
        return view('console.tournaments.point',compact('points'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
            $formFields= $request->validate([
                'kills_point'=>'required',
                'placement_point'=>'required',
            ]);
            $count = Points::where('user_id',auth()->user()->id)->get()->count();
            if($count > 0)
            {
                return redirect('/dashboard');
            }
            else{
                Points::create($formFields+['user_id'=>auth()->user()->id]);
                return redirect('/dashboard');
            }

    }
    public function calculate(Request $request)
    {
        $user = Points::where('user_id',auth()->user()->id)->first();
        $tournament = Session::get('tournament_id');
        $check_data = Results::where('tournament_id', $tournament)->first();
        if($check_data){
            return redirect('/')->with('message','Result already published');
        }
        else{
            $kills_pts = $user->kills_point;
            $points = $user->placement_point;
            $total_kills = $request->kills1 + $request->kills2 + $request->kills3 + $request->kills4;
            $points_array = explode(',', $points);
            $placement_points = 0;
            foreach($points_array as $point){
                $placement = explode('=', $point);
                if(strpos($placement[0],'-') !== false){
                    $placement_range = explode('-', $placement[0]);
                    if($request->placement >= $placement_range[0] && $request->placement <= $placement_range[1]){
                        $placement_points = $placement[1];
                        break;
                    }
                }else{
                    if($request->placement == $placement[0]){
                        $placement_points = $placement[1];
                        break;
                    }
                }
            }
        // Calculate the total points
        $totalPoints = $placement_points + $total_kills * $kills_pts;
        // Insert the data into result table
        $result = new Results();
        $result->tournament_id = $tournament;
        $result->team_id = $request->team_id;
        $result->kills = $total_kills;
        $result->placement = $request->placement;
        $result->total = $totalPoints;
        $result->save();
        // inserting data for histories table
        $data = [
            ['tournament_id' => $tournament, 'team_id' => $request->team_id, 'player_name' => $request->input('player_name1'), 'kills' => $request->input('kills1')],
            ['tournament_id' => $tournament, 'team_id' => $request->team_id, 'player_name' => $request->input('player_name2'), 'kills' => $request->input('kills2')],
            ['tournament_id' => $tournament, 'team_id' => $request->team_id, 'player_name' => $request->input('player_name3'), 'kills' => $request->input('kills3')],
            ['tournament_id' => $tournament, 'team_id' => $request->team_id, 'player_name' => $request->input('player_name4'), 'kills' => $request->input('kills4')],
        ];
        History::insert($data);
        return redirect('/dashboard');
        }

    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $games = Game::all();
        $tournaments = Tournament::orderBy('closing_time','desc')->where('user_id', auth()->user()->id)->get();
        $points = Points::findOrFail($id);
        return view('console.points.edit',compact('games','tournaments','points'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $points = Points::findOrFail($id);
        // Make sure logged in user is owner
        if($points->user_id != auth()->id()) {
            abort(403, 'Unauthorized Action');
        }
        $formFields = $request->validate([
            'kills_point'=>'required',
            'placement_point'=>'required',
        ]);
        $points->update($formFields);
        return redirect('/dashboard')->with('message', 'Points has been updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $points = Points::findOrFail($id);
         // Make sure logged in user is owner
         if($points->user_id != auth()->id()) {
            abort(403, 'Unauthorized Action');
        }
        $points->delete();
        return redirect('/dashboard')->with('message','Points has been deleted');
    }
}
