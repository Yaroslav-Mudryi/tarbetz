<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameTournament extends Model
{
    use HasFactory;
    protected $table = 'game_tournaments';

    // The attributes that are mass assignable
    protected $fillable = [
        'id',
        'category_id',
        'name',
        'status',
        'odd_key',
    ];

    // Optionally, you can also specify attributes that should be cast to specific types
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function gameCategory()
    {
        return $this->belongsTo(GameCategory::class,'category_id' );
    }
    public function gameMatch()
    {
        return $this->hasMany(GameMatch::class,'tournament_id');
    }
    public static function updateStatus()
    {
        $gts = GameTournament::get();
        foreach ($gts as $gt) {
            $is_active = false;
            // dd($gt->gameMatch);
            foreach ($gt->gameMatch as $gm) {
                if($gm->status === 1){
                    $is_active = true;
                }
            }
            if(!$is_active){
                GameTournament::find($gt->id)->update(['status'=>2]);
            }
        }
    }
}
