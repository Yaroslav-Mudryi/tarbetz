<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\Upload;
use App\Models\GameCategory;
use App\Models\GameTeam;
use App\Models\GameTournament;
use Illuminate\Http\Request;
use Stevebauman\Purify\Facades\Purify;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use App\Models\ContentOdd;


class TeamController extends Controller
{
    use Upload;

    public function listTeam()
    {
        $data['teams'] = GameTeam::with('gameCategory')->orderBy('id', 'desc')->get();
        $data['categories'] = GameCategory::whereStatus(1)->orderBy('name','asc')->get();
        $data['teams_from_odds'] = json_decode($this::teamsFromOdds());
        return view('admin.team.list', $data);
    }

    public function teamsFromOdds()
    {
        $contentCtrl = new ContentController();
        $content = $contentCtrl->fetchFromOdds('/sports/upcoming/odds', 'regions=us,us2,uk,eu,au&markets=h2h,totals,spreads');
        if ($content == null) return "[]";

        $matches = json_decode($content);
        $added_teams = GameTeam::orderBy('id', 'desc')->get()->pluck('name')->toArray();
        $tours = GameTournament::with('gameCategory')->orderBy('id', 'desc')->get()->toArray();

        $teams = [];
        foreach ($matches as &$match) {
            $tour_id = array_search($match->sport_title, array_column($tours, 'name'));
            if ($tour_id === false) continue;

            $tour = $tours[$tour_id];
            $category = $tour['game_category'];

            if (in_array($match->home_team, $added_teams)) continue;

            $teams[$match->home_team] = [
                'key' => $match->home_team,
                'name' => $match->home_team,
                'sport' => $tour['name'],
                'category' => $category['name'],
                'category_id' => $category['id'],
                'active' => true,
            ];

            if (in_array($match->away_team, $added_teams)) continue;

            $teams[$match->away_team] = [
                'key' => $match->away_team,
                'name' => $match->away_team,
                'sport' => $tour['name'],
                'category' => $category['name'],
                'category_id' => $category['id'],
                'active' => true,
            ];
        }

        return json_encode(array_values($teams));
    }

    public function storeTeam(Request $request)
    {
        $purifiedData = Purify::clean($request->except('image', '_token', '_method'));

        if ($request->has('image')) {
            $purifiedData['image'] = $request->image;
        }
        $rules = [
            'name' => 'required|max:40',
            'category' => 'required',
            'image' => 'required|mimes:jpg,jpeg,png',
        ];
        $message = [
            'name.required' => 'Name field is required',
            'category.required' => 'Category field is required',
        ];

        $validate = Validator::make($purifiedData, $rules, $message);

        if ($validate->fails()) {
            return back()->withInput()->withErrors($validate);
        }

        try {

            $gameTeam = new GameTeam();

            if ($request->has('category')) {
                $gameTeam->category_id = @$purifiedData['category'];
            }
            if ($request->has('name')) {
                $gameTeam->name = @$purifiedData['name'];
            }
            if ($request->hasFile('image')) {
                try {
                    $gameTeam->image = $this->uploadImage($request->image, config('location.team.path'), config('location.team.size'));
                } catch (\Exception $exp) {
                    return back()->with('error', 'Image could not be uploaded.');
                }
            }

            $gameTeam->status = isset($purifiedData['status']) == 'true' ? 1 : 0;

            $gameTeam->save();
            return back()->with('success', 'Successfully Saved');

        } catch (\Exception $e) {
            return back();
        }
    }

    public function storeTeamsFromOdd(Request $request)
    {

        $names = $request->get('checks_add');
        $added_teams = GameTeam::orderBy('id', 'desc')->get()->toArray();

        try {
            for($i = 0; $i < count($names); $i++)
            {
                list($name, $category, $status) = explode(":", $names[$i]);
                $search = $name . "|" . $category;
                if (in_array($search, $added_teams)) continue;

                $gameTeam = new GameTeam();
                $gameTeam->name = $name;
                $gameTeam->category_id = $category;
                // $gameTeam->image = 'odd.png';
                $gameTeam->status = $status;

                $gameTeam->save();
            }

            return back()->with('success', 'Successfully Saved');

        } catch (\Exception $e) {
            return back();
        }
    }

    public function updateTeam(Request $request,$id)
    {
        $purifiedData = Purify::clean($request->except('image', '_token', '_method'));
        if ($request->has('image')) {
            $purifiedData['image'] = $request->image;
        }
        $rules = [
            'name' => 'required|max:40',
            'category' => 'required',
        ];
        $message = [
            'name.required' => 'Name field is required',
            'category.required' => 'Category field is required',
        ];


        $validate = Validator::make($purifiedData, $rules, $message);

        if ($validate->fails()) {
            return back()->withInput()->withErrors($validate);
        }

        try{
            $gameTeam = GameTeam::findOrFail($id);

            if ($request->has('category')) {
                $gameTeam->category_id = @$purifiedData['category'];
            }
            if ($request->has('name')) {
                $gameTeam->name = @$purifiedData['name'];
            }
            if ($request->hasFile('image')) {
                $gameTeam->image = $this->uploadImage($request->image, config('location.team.path'), config('location.team.size'), $gameTeam->image);
            }

            $gameTeam->status = isset($purifiedData['status']) == 'true' ? 1 : 0;

            $gameTeam->save();
            return back()->with('success', 'Successfully Updated');

        }catch (\Exception $e){
            return back();
        }
    }

    public function deleteTeam($id)
    {
        $gameTeam = GameTeam::with(['gameTeam1','gameTeam2'])->findOrFail($id);

        if (0 < count($gameTeam->gameTeam1)) {
            session()->flash('warning', 'This team has a lot of match');
            return back();
        }
        if (0 < count($gameTeam->gameTeam2)) {
            session()->flash('warning', 'This team has a lot of match');
            return back();
        }

        $gameTeam->delete();
        return back()->with('success', 'Successfully deleted');
    }

    public function activeMultiple(Request $request)
    {
        if ($request->strIds == null) {
            session()->flash('error', 'You do not select ID.');
            return response()->json(['error' => 1]);
        } else {
            GameTeam::whereIn('id', $request->strIds)->update([
                'status' => 1,
            ]);
            session()->flash('success', 'Team Has Been Active');
            return response()->json(['success' => 1]);
        }

    }

    public function deActiveMultiple(Request $request)
    {
        if ($request->strIds == null) {
            session()->flash('error', 'You do not select ID.');
            return response()->json(['error' => 1]);
        } else {
            GameTeam::whereIn('id', $request->strIds)->update([
                'status' => 0,
            ]);
            session()->flash('success', 'Team Has Been Deactive');
            return response()->json(['success' => 1]);
        }
    }
}
