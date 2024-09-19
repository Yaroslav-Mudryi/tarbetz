<?php

namespace App\Console\Commands;

use App\Http\Traits\Notify;
use App\Models\BetInvest;
use App\Models\GameCategory;
use App\Models\GameTournament;
use App\Models\GameTeam;
use App\Models\GameMatch;
use App\Models\GameQuestions;
use App\Models\GameOption;
use App\Models\ContentOdd;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Facades\App\Services\BasicService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;


class FetchMatch extends Command
{
    use Notify;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:fetch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cron for fetch odd api';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $now = Carbon::now();
        $basic = (object)config('basic');

        $this->createCategories();
        $this->createTournaments();
        $this->createTeams();
        $this->createMatches();

        $this->info('status');
    }

    public function createCategories()
    {
        $ctrl = new Controller();
        $content = $ctrl->fetchFromOdds('/sports', '');
        if ($content == null) return;

        $sports = json_decode($content);
        $added_categories = GameCategory::orderBy('id', 'desc')->get()->pluck('name')->toArray();
        $games = config('games');
        $icon = $games['Normal'];

        $categories = [];
        foreach ($sports as &$sport) {
            if (in_array($sport->group, $added_categories)) continue;
            if (in_array($sport->group, $categories)) continue;

            $categories[] = $sport->group;
        }

        foreach ($categories as $category) {
            $gameCategory = new GameCategory();
            $gameCategory->name = $category;
            $gameCategory->icon = $icon;
            $gameCategory->status = 1;

            $gameCategory->save();
            Log::info('Game Category => ' . $category . '|1 - Saved.');
        }
    }

    public function createTournaments()
    {
        $ctrl = new Controller();
        $content = $ctrl->fetchFromOdds('/sports', '');
        if ($content == null) return;

        $sports = json_decode($content);
        $added_tournaments = GameTournament::orderBy('id', 'desc')->get()->pluck('odd_key')->toArray();
        $categories = GameCategory::whereStatus(1)->orderBy('name','asc')->get()->toArray();

        $tournaments = [];
        foreach ($sports as &$sport) {
            if (in_array($sport->key, $added_tournaments)) continue;

            $idx = array_search($sport->group, array_column($categories, 'name'));
            if ($idx === false) continue;

            $gameTournament = new GameTournament();
            $gameTournament->name = $sport->title;
            $gameTournament->category_id = $categories[$idx]['id'];
            $gameTournament->odd_key = $sport->key;
            $gameTournament->status = $sport->active;

            $gameTournament->save();
            Log::info('Tournament => ' . $sport->title . '|'. $categories[$idx]['name'] . '|' . $sport->active . ' - Saved.');
        }
    }

    public function createTeams()
    {
        $ctrl = new Controller();
        $content = $ctrl->fetchFromOdds('/sports/upcoming/odds', 'regions=us,us2,uk,eu,au&markets=h2h,totals,spreads');
        if ($content == null) return;

        $matches = json_decode($content);
        $added_teams = GameTeam::orderBy('id', 'desc')->get()->pluck('name')->toArray();
        $tours = GameTournament::with('gameCategory')->orderBy('id', 'desc')->get()->toArray();
    
        foreach ($matches as &$match) {
            $tour_id = array_search($match->sport_title, array_column($tours, 'name'));
            if ($tour_id === false) continue;

            $tour = $tours[$tour_id];
            $category = $tour['game_category'];


            if (in_array($match->home_team, $added_teams)) continue;

            $gameTeam = new GameTeam();
            $gameTeam->name = $match->home_team;
            $gameTeam->category_id = $category['id'];
            $gameTeam->image = '';
            $gameTeam->status = 1;

            $gameTeam->save();
            Log::info('Game Team => ' . $match->home_team . '|'. $category['name'] . ' - Saved.');


            if (in_array($match->home_team, $added_teams)) continue;

            $gameTeam = new GameTeam();
            $gameTeam->name = $match->away_team;
            $gameTeam->category_id = $category['id'];
            $gameTeam->image = '';
            $gameTeam->status = 1;

            $gameTeam->save();
            Log::info('Game Team => ' . $match->away_team . '|'. $category['name'] . ' - Saved.');
        }
    }

    public function createMatches()
    {
        $ctrl = new Controller();
        $content = $ctrl->fetchFromOdds('/sports/upcoming/odds', 'regions=us,us2,uk,eu,au&markets=h2h,totals,spreads');
        if ($content == null) return;

        $matches = json_decode($content);
        $tours = GameTournament::with('gameCategory')->orderBy('id', 'desc')->get()->toArray();
        $added_matches = GameMatch::get()->pluck('odd_id')->toArray();

        foreach ($matches as &$match) {
            $tour_id = array_search($match->sport_key, array_column($tours, 'odd_key'));
            if ($tour_id === false) continue;

            if (in_array($match->id, $added_matches)) continue;

            $tour = $tours[$tour_id];
            $category = $tour['game_category'];

            $teams = GameTeam::where('category_id', $tour['category_id'])->orderBy('id', 'desc')->get()->toArray();

            $home_id = array_search($match->home_team, array_column($teams, 'name'));
            if ($home_id === false) continue;
            $home_team = $teams[$home_id];

            $away_id = array_search($match->away_team, array_column($teams, 'name'));
            if ($away_id === false) continue;
            $away_team = $teams[$away_id];

            $gameMatch = new GameMatch();
            $gameMatch->odd_id = $match->id;
            $gameMatch->category_id = $category['id'];
            $gameMatch->tournament_id = $tour['id'];
            $gameMatch->team1_id = $home_team['id'];
            $gameMatch->team2_id = $away_team['id'];
            $gameMatch->start_date = Carbon::parse($match->commence_time, 'UTC')
                    ->setTimezone(config('app.timezone'))
                    ->format('Y-m-d H:i:s');
            $end_time = Carbon::parse($match->commence_time, 'UTC')
                    ->setTimezone(config('app.timezone'))
                    ->addDay()
                    ->format('Y-m-d H:i:s');
            $gameMatch->end_date = $end_time;
            $gameMatch->status = 1;

            $gameMatch->save();
            Log::info('Game Match => ' . $home_team['name'] . ':' . $away_team['name'] . '|'. $tour['name'] . '|' . $match->commence_time . ' - Saved.');

            $this->storeQuestionsFromOdd($gameMatch->id, $match, $end_time);
        }

    }

    
    public function storeQuestionsFromOdd($match_id, $details, $end_time)
    {
        $home_team = $details->home_team;
        $away_team = $details->away_team;

        $h2h = [[
            "name" => $home_team,
            "price" => 0,
        ], [
            "name" => $away_team,
            "price" => 0,
        ], [
            "name" => "Draw",
            "price" => 0,
        ]];

        $spreads = [[
            "name" => $home_team,
            "price" => 0,
            "point" => 0,
        ], [
            "name" => $away_team,
            "price" => 0,
            "point" => 0,
        ]];
        
        $totals = [[
            "name" => 'Over',
            "price" => 0,
            "point" => 0,
        ], [
            "name" => 'Under',
            "price" => 0,
            "point" => 0,
        ]];

        $h2h_count = 0;
        $spreads_count = 0;
        $totals_count = 0;
        
        $bookmakers = $details->bookmakers;
        foreach ($bookmakers as $bookmaker)
        {
            $markets = $bookmaker->markets;
            
            foreach ($markets as $market)
            {
                if ($market->key == 'h2h') {
                    $h2h_count++;
                    $h2h = $this->average($h2h, $market->outcomes, $h2h_count);
                }

                if ($market->key == 'spreads') {
                    $spreads_count++;
                    $spreads = $this->average($spreads, $market->outcomes, $spreads_count);
                }

                if ($market->key == 'totals') {
                    $totals_count++;
                    $totals = $this->average($totals, $market->outcomes, $totals_count);
                }
            }
        }

        $this->storeQuestionFrom($match_id, $h2h, 'MoneyLine', $end_time);
        $this->storeQuestionFrom($match_id, $spreads, 'Spreads', $end_time);
        $this->storeQuestionFrom($match_id, $totals, 'Over / Under', $end_time);
    }

    public function average($arr1, $arr2, $count)
    {
        $result = [];
        foreach ($arr1 as $item1)
        {
            if (count($item1) < 1) continue;

            $item = $item1;
            foreach ($arr2 as $item2)
            {
                $item2 = json_decode(json_encode($item2), true);
                if (count($item2) < 1) continue;
                if ($item1['name'] != $item2['name']) continue;

                $item['name'] = $item1['name'];

                if (isset($item2['point']))
                    $item['point'] = number_format($item2['point'] ? ($item1['point'] * ($count - 1) + $item2['point']) / $count : 0, 2);

                if (isset($item2['price']))
                    $item['price'] = number_format($item2['price'] ? ($item1['price'] * ($count - 1) + $item2['price']) / $count : 0, 2);

                break;
            }

            array_push($result, $item);
        }

        return $result;
    }

    public function storeQuestionFrom($match_id, $data, $title, $end_time)
    {
        $betQues = new GameQuestions();
        $betQues->match_id = $match_id;
        $betQues->creator_id = 1;
        $betQues->name = $title;
        $betQues->status = 1;
        $betQues->end_time = $end_time;
        $betQues->save();
        Log::info('Match Quiz => ' . $title . ' - Saved.');

        foreach ($data as $item) {
            $betOpt = new GameOption();
            $betOpt->creator_id = 1;
            $betOpt->question_id = $betQues->id;
            $betOpt->match_id = $betQues->match_id;

            if (isset($item['point'])) {
                $point = floor($item['point']) + 0.5;
                $betOpt->option_name = $item['name'] . " | " . $point;
            } else {
                $betOpt->option_name = $item['name'];
            }

            $betOpt->ratio = $item['price'];
            $betOpt->status = 1;
            $betOpt->save();
            Log::info('Match Quiz Opt. => ' . $item['name'] . '|' . $item['price'] . ' - Saved.');
        }
    }

}
