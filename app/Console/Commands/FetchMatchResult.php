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


class FetchMatchResult extends Command
{
    use Notify;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:match-result';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cron for fetch match result odd api';

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

        $matches = GameMatch::with('gameTournament')->where('status', 1)->get()->toArray();
        $keys = [];
        foreach($matches as $match)
        {
            $keys[] = $match['game_tournament']['odd_key'];
        }
        

        foreach ($keys as $key)
        {
            $match_results = $this->fetchResult($key);
            Log::info("Key : " . $key);
    
            foreach($match_results as $match_result)
            {
                if (!$match_result->completed) continue;

                $max = max(array_column($match_result->scores, 'score'));
                $min = min(array_column($match_result->scores, 'score'));
                if ($max == $min)
                {
                    $winner = 'Draw';
                }
                else
                {
                    $idx = array_search($max, array_column($match_result->scores, 'score'));
                    $winner = [
                        'name' => $match_result->scores[$idx]->name,
                        'max' => $max,
                        'min' => $min,
                    ];
                }
                Log::info("Winner : " . json_encode($winner));

                $idx = array_search($match_result->id, array_column($matches, 'odd_id'));
                $this->setWinnersToQuiz($matches[$idx], $winner);
            }
        }

        $this->info('status');
    }

    public function fetchResult($sport_key)
    {
        $ctrl = new Controller();
        $content = $ctrl->fetchFromOdds('/sports/'.$sport_key.'/scores', 'daysFrom=1');
        if ($content == null) [];

        return json_decode($content);
    }

    public function setWinnersToQuiz($match, $winner)
    {
        GameQuestions::where('match_id', $match['id'])->get()->map(function ($quiz) use ($match, $winner) {
            
            if ($quiz['name'] == 'MoneyLine')
            {
                $this->setWinnersToMoneyLine($match, $quiz, $winner);
            }
            else if ($quiz['name'] == 'Spreads')
            {
                $this->setWinnersToSpreads($match, $quiz, $winner);
            }
            else if ($quiz['name'] == 'Over / Under')
            {
                $this->setWinnersToOverUnder($match, $quiz, $winner);
            }

            $quiz->result = 1;
            $quiz->status = 2;
            $quiz->save();

        });

        $match_db_obj = GameMatch::where('id', $match['id'])->first();
        $match_db_obj->end_date = Carbon::now()
                ->setTimezone(config('app.timezone'))
                ->format('Y-m-d H:i:s');
        $match_db_obj->update();
    }

    public function setWinnersToMoneyLine($match, $quiz, $winner)
    {
        $opts = GameOption::where('question_id', $quiz->id)->get()->map(function ($opt) use ($winner) {
            if ($opt->option_name == $winner['name'])
            {
                $opt->status = 2;
            }
            else
            {
                $opt->status = -2;
            }
            $opt->update();
        });
    }

    public function setWinnersToSpreads($match, $quiz, $winner)
    {
        $opts = GameOption::where('question_id', $quiz->id)->get()->map(function ($opt) use ($winner) {
            
            list($name, $point) = explode(" | ", $opt->option_name);

            if ($name == $winner['name'])
            {
                if ($point > 0) {
                    $opt->status = 2;
                } else {
                    $opt->status = $winner['max'] - $winner['min'] >= abs($point) ? 2 : -2;
                }
            }
            else
            {
                if ($point > 0) {
                    $opt->status = $winner['max'] - $winner['min'] < abs($point) ? 2 : -2;
                } else {
                    $opt->status = -2;
                }
            }
            $opt->update();
        });
    }

    public function setWinnersToOverUnder($match, $quiz, $winner)
    {
        $opts = GameOption::where('question_id', $quiz->id)->get()->map(function ($opt) use ($winner) {

            list($name, $point) = explode(" | ", $opt->option_name);
            $totals = $winner['max'] + $winner['min'];
            
            if ($name == 'Over')
            {
                Log::info("Over Opt => " . $totals . $point);
                $opt->status = $totals >= abs($point) ? 2 : -2;
            }
            else
            {
                Log::info("Under Opt => " . $totals . $point);
                $opt->status = $totals <= abs($point) ? 2 : -2;
            }
            Log::info("Opt => " . $opt->status);
            $opt->update();
        });
    }
}
