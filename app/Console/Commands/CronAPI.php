<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Http;
use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\GameTournament;
use App\Models\GameMatch;
use App\Models\GameTeam;
use App\Models\GameQuestions;
use App\Models\GameOption;
use App\Models\GameCategory;

class CronAPI extends Command
{
    protected $signature = 'cronapi:run';
    protected $description = 'Cron job to fetch all sports data from Bet365 API';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $start_time = Carbon::now();
        $this->info('Started at: ' . $start_time);

        // Reset statuses
        GameMatch::where('status',1)->where('end_date', '<', $start_time)->get()->map(function ($item){
            $item->status = 2;
            $item->save();
        });

        GameQuestions::where('status',1)->where('end_time', '<', $start_time)->get()->map(function ($item){
            $item->status = 2;
            $item->save();
        });

        // Define eventTypeId to sport name mapping
        $eventTypeIdMap = [
            1 => 'Soccer',
            7522 => 'Basketball',
            2 => 'Tennis',
            998917 => 'Volleyball',
            468328 => 'Handball',
            7524 => 'Ice Hockey',
            4 => 'Cricket',
            5 => 'Rugby Union',
        ];

        // Get sport_ids from game_categories table
        $sportIds = GameCategory::pluck('id', 'name')->toArray();

        // Reverse the sportIds mapping to get name by id
        $sportIdByName = array_flip($sportIds);

        // Fetch upcoming events
        foreach ($eventTypeIdMap as $eventTypeId => $sportName) {
            // dd($sportIds[$sportName]);
            if (isset($sportIds[$sportName])) {
                $sportId = $sportIds[$sportName];

                $this->processEvents("https://api.b365api.com/v1/betfair/sb/upcoming", 'upcoming', $sportId, $eventTypeId);
            }
        }

        // Fetch in-play events
        foreach ($eventTypeIdMap as $eventTypeId => $sportName) {
            if (isset($sportIds[$sportName])) {
                $sportId = $sportIds[$sportName];
                $this->processEvents("https://api.b365api.com/v1/betfair/sb/inplay", 'in-play', $sportId, $eventTypeId);
            }
        }

        $end_time = Carbon::now();
        $this->info('Ended at: ' . $end_time);
        $this->info("Duration: " . $start_time->diff($end_time)->format('%H:%I:%S'));
    }

    private function processEvents($url, $type, $sportId, $eventTypeId)
    {
        $eventsResponse = Http::get($url, [
            'token' => env('BETFAIR_API_KEY'),
            'eventTypeId' => $eventTypeId,
            'day' => 'today',
        ]);

        $events = json_decode($eventsResponse->body(), true);

        if (!isset($events['results'])) {
            $this->error('No ' . $type . ' events found or API error for sport_id: ' . env('BETFAIR_API_KEY'));
            return;
        }

        foreach ($events['results'] as $event) {
            $this->info("Processing " . ucfirst($type) . " Event: " . $event['home']['name'] . " vs " . $event['away']['name']);

            // Update or create tournament
            GameTournament::updateOrCreate([
                'id' => $event['league']['id'],
            ], [
                'id' => $event['league']['id'],
                'name' => $event['league']['name'],
                'category_id' => $sportId,
                'status' => 1,
            ]);

            // Update or create teams
            GameTeam::updateOrCreate([
                'id' => $event['home']['id'],
            ], [
                'id' => $event['home']['id'],
                'name' => $event['home']['name'],
                'status' => 1,
                'category_id' => $sportId,
            ]);

            GameTeam::updateOrCreate([
                'id' => $event['away']['id'],
            ], [
                'id' => $event['away']['id'],
                'name' => $event['away']['name'],
                'status' => 1,
                'category_id' => $sportId,
            ]);

            // Update or create match
            GameMatch::updateOrCreate([
                'id' => $event['id'],
            ], [
                'id' => $event['id'],
                'team1_id' => $event['home']['id'],
                'team2_id' => $event['away']['id'],
                'start_date' => Carbon::createFromTimestamp($event['time'])->toDateTimeString(),
                'end_date' => Carbon::createFromTimestamp($event['time'])->toDateTimeString(),
                'category_id' => $sportId,
                'tournament_id' => $event['league']['id'],
                'status' => 1,
                'is_unlock' => 1,
            ]);

            // Fetch and save odds
            $oddsData = $this->fetchOdds($event['id']);

            if (!empty($oddsData)) {
                foreach ($oddsData as $marketKey => $marketOdds) {
                    $optionNames = $this->getOptionNames($marketKey);
                    foreach ($marketOdds as $runner) {
                        foreach ($optionNames as $key => $label) {
                            // Check if the key exists in the $runner array
                            $ratio = $runner[$key] ?? null;

                            $question = GameQuestions::updateOrCreate([
                                'match_id' => $event['id'],
                                'name' => $label,
                            ], [
                                'match_id' => $event['id'],
                                'creator_id' => 1,
                                'name' => $label,
                                'status' => 1,
                                'end_time' => date('Y-m-d H:i:s', $event['time']),
                            ]);

                            GameOption::updateOrCreate([
                                'match_id' => $event['id'],
                                'question_id' => $question->id,
                                'option_name' => $label,
                            ], [
                                'match_id' => $event['id'],
                                'question_id' => $question->id,
                                'option_name' => $label,
                                'creator_id' => 1,
                                'ratio' => $ratio,
                                'status' => 1,
                            ]);
                        }
                    }
                }
            } else {
                $this->error("No odds data found for event ID: " . $event['id']);
            }
        }
    }

    private function fetchOdds($eventId)
    {
        $oddsResponse = Http::get("https://api.b365api.com/v2/event/odds", [
            'token' => env('BETFAIR_API_KEY'),
            'event_id' => $eventId,
        ]);

        $oddsData = json_decode($oddsResponse->body(), true);

        return $oddsData['results']['odds'] ?? [];
    }

    private function getOptionNames($marketKey)
    {
        switch ($marketKey) {
            case '1_1':
                return [
                    'home_od' => 'Home Win',
                    'draw_od' => 'Draw',
                    'away_od' => 'Away Win',
                ];
            case '1_2':
                return [
                    'home_od' => 'Home Handicap',
                    'away_od' => 'Away Handicap',
                ];
            case '1_3':
                return [
                    'Over' => 'Over',
                    'Under' => 'Under',
                ];
            case '1_4':
                return [
                    'home_od' => 'Home Corners',
                    'away_od' => 'Away Corners',
                ];
            case '1_5':
                return [
                    'home_od' => 'Home 1st Half Handicap',
                    'away_od' => 'Away 1st Half Handicap',
                ];
            case '1_6':
                return [
                    'home_od' => 'Home 2nd Half Goals',
                    'away_od' => 'Away 2nd Half Goals',
                ];
            case '1_7':
                return [
                    'home_od' => 'Home 2nd Half Handicap',
                    'away_od' => 'Away 2nd Half Handicap',
                ];
            case '1_8':
                return [
                    'home_od' => 'Home 1st Half Time Result',
                    'away_od' => 'Away 1st Half Time Result',
                ];
            default:
                return [];
        }
    }
}
