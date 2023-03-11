<?php
namespace App;
use Illuminate\Support\Facades\DB;
use App\Models\Tournament;
use App\Models\Team;
use App\Models\Points;
use App\Models\Results;
use App\Models\History;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;

class PerformanceHelper
{
    public function performance()
    {
        $userId = Auth::id();
        $results = DB::table('results')
                     ->join('tournaments', 'results.tournament_id', '=', 'tournaments.id')
                     ->join('teams', 'teams.id', '=', 'results.team_id')
                     ->where('teams.user_id', '=', $userId)
                     ->select('tournaments.name', DB::raw('SUM(total) as total_points'))
                     ->groupBy('tournaments.name')
                     ->get();

        //to display kills in relation to tournament
        $kills = DB::table('histories')
        ->join('tournaments', 'histories.tournament_id', '=', 'tournaments.id')
        ->join('teams', 'teams.id', '=', 'histories.team_id')
        ->where('teams.user_id', '=', $userId)
        ->select('tournaments.name', DB::raw('SUM(kills) as total_kills'))
        ->groupBy('tournaments.name')
        ->get();

        // to display weekly score
        $now = Carbon::now();
        // Calculate the date and time a week ago
        $weekAgo = $now->subWeek();
        $week_data = DB::table('results')
        ->join('tournaments', 'results.tournament_id', '=', 'tournaments.id')
        ->join('teams', 'teams.id', '=', 'results.team_id')
        ->whereDate('results.created_at', '>=', $weekAgo)
        ->where('teams.user_id', '=', $userId)
        ->select('tournaments.name', DB::raw('SUM(total) as total_points'))
        ->groupBy('tournaments.name')
        ->get();
        //to display  monthly score
        // Calculate the date and time a month ago
        $monthAgo = $now->subMonth();
        $month_data = DB::table('results')
        ->join('tournaments', 'results.tournament_id', '=', 'tournaments.id')
        ->join('teams', 'teams.id', '=', 'results.team_id')
        ->whereDate('results.created_at', '>=', $monthAgo)
        ->where('teams.user_id', '=', $userId)
        ->select('tournaments.name', DB::raw('SUM(total) as total_points'))
        ->groupBy('tournaments.name')
        ->get();
        return [$kills, $week_data,$month_data,$results];

    }

}

?>
