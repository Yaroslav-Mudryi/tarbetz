<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GameCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Stevebauman\Purify\Facades\Purify;
use Illuminate\Support\Facades\Http;
use App\Models\ContentOdd;


class CategoryController extends Controller
{
    public function listCategory()
    {

        $games = config('games');
        ksort($games);
        $data['games'] = $games;
        $data['categories'] = GameCategory::withCount('activeTournament', 'activeTeam','activeMatch')->orderBy('id', 'desc')->get();
        $data['categories_from_odds'] = json_decode($this::categoriesFromOdds());
        return view('admin.category.list', $data);
    }

    public function categoriesFromOdds()
    {
        $contentCtrl = new ContentController();
        $content = $contentCtrl->fetchFromOdds('/sports', '');
        if ($content == null) return "[]";

        $sports = json_decode($content);
        $added_categories = GameCategory::orderBy('id', 'desc')->get()->pluck('name')->toArray();

        $categories = [];
        foreach ($sports as &$sport) {
            if (in_array($sport->group, $added_categories)) continue;
            if (in_array($sport->group, array_column($categories, 'name'))) continue;

            $categories[] = [
                'key' => $sport->group,
                'title' => $sport->title,
                'name' => $sport->group,
                'active' => $sport->active,
            ];
        }

        return json_encode($categories);
    }

    public function storeCategory(Request $request)
    {

        $purifiedData = Purify::clean($request->except('image', '_token', '_method'));

        $rules = [
            'title' => 'required|max:40',
            'icon' => 'required',
        ];
        $message = [
            'title.required' => 'Title field is required',
            'icon.required' => 'Icon field is required',
        ];

        $validate = Validator::make($purifiedData, $rules, $message);

        if ($validate->fails()) {
            return back()->withInput()->withErrors($validate);
        }

        try {
            $gameCategory = new GameCategory();

            if (isset($purifiedData['title'])) {
                $gameCategory->name = @$purifiedData['title'];
            }
            if (isset($purifiedData['icon'])) {
                $gameCategory->icon = $request->icon;
            }

            $gameCategory->status = isset($purifiedData['status']) == 'true' ? 1 : 0;

            $gameCategory->save();
            return back()->with('success', 'Successfully Saved');

        } catch (\Exception $e) {
            return back();
        }
    }

    public function storeCategoriesFromOdd(Request $request)
    {

        $names = $request->get('checks_add');
        $icons = $request->get('icons_add');

        if (!$icons || count($icons) < count($names)) {
            return back()->withInput()->withErrors(['icon.required' => 'Icon field is required']);
        }

        try {
            for($i = 0; $i < count($names); $i++)
            {
                list($name, $status) = explode(":", $names[$i]);

                $gameCategory = new GameCategory();
                $gameCategory->name = $name;
                $gameCategory->icon = $icons[$i];
                $gameCategory->status = $status;

                $gameCategory->save();
            }

            return back()->with('success', 'Successfully Saved');

        } catch (\Exception $e) {
            return back();
        }
    }

    public function updateCategory(Request $request, $id)
    {
        $purifiedData = Purify::clean($request->except('image', '_token', '_method'));
        $rules = [
            'title' => 'required|max:40',
            'icon' => 'required',
        ];
        $message = [
            'title.required' => 'Title field is required',
            'icon.required' => 'Icon field is required',
        ];

        $validate = Validator::make($purifiedData, $rules, $message);

        if ($validate->fails()) {
            return back()->withInput()->withErrors($validate);
        }

        try {
            $gameCategory = GameCategory::findOrFail($id);

            if ($request->has('title')) {
                $gameCategory->name = @$purifiedData['title'];
            }
            if ($request->has('icon')) {
                $gameCategory->icon = $request->icon;
            }

            $gameCategory->status = isset($purifiedData['status']) == 'true' ? 1 : 0;

            $gameCategory->save();
            return back()->with('success', 'Successfully Updated');

        } catch (\Exception $e) {
            return back();
        }
    }

    public function deleteCategory($id)
    {
        $gameCategory = GameCategory::with(['gameTournament', 'gameTeam','gameMatch'])->findOrFail($id);

        if (0 < count($gameCategory->gameTournament)) {
            session()->flash('warning', 'This category has a lot of tournament');
            return back();
        }
        if (0 < count($gameCategory->gameTeam)) {
            session()->flash('warning', 'This category has a lot of team');
            return back();
        }
        if (0 < count($gameCategory->gameMatch)) {
            session()->flash('warning', 'This category has a lot of Match');
            return back();
        }

        $gameCategory->delete();
        return back()->with('success', 'Successfully deleted');
    }

    public function activeMultiple(Request $request)
    {
        if ($request->strIds == null) {
            session()->flash('error', 'You do not select ID.');
            return response()->json(['error' => 1]);
        } else {
            GameCategory::whereIn('id', $request->strIds)->update([
                'status' => 1,
            ]);
            session()->flash('success', 'Category Has Been Active');
            return response()->json(['success' => 1]);
        }

    }

    public function deActiveMultiple(Request $request)
    {
        if ($request->strIds == null) {
            session()->flash('error', 'You do not select ID.');
            return response()->json(['error' => 1]);
        } else {
            GameCategory::whereIn('id', $request->strIds)->update([
                'status' => 0,
            ]);
            session()->flash('success', 'Category Has Been Deactive');
            return response()->json(['success' => 1]);
        }
    }
}
