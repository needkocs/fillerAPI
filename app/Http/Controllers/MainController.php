<?php

namespace App\Http\Controllers;

use App\Http\Resources\GameResource;
use App\Models\Cell;
use App\Models\Field;
use App\Models\Game;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MainController extends Controller
{
    /**
     * Новая игра
     */
    public function game(request $request) {

        Artisan::call('migrate:refresh');

        $validate = Validator::make($request->all(), [
           'width' => 'required|int|min:5|max:99',
           'height' => 'required|int|min:5|max:99',
        ]);



        if($validate->fails()) {
            return response()->json([
                'Ширина или высота не попадают в диапазоны минимального и максильного значения или четные'
            ])->setStatusCode('400', 'Validation Error');
        }


        $uuid = Str::uuid(50);
//        ['blue', 'green', 'cyan', 'red', 'magenta', 'yellow', 'white']

        for ($players = 1; $players <= 2; $players++) {
            $player = new Player();
            $player->id = $players;
            $player->color = Arr::random(['blue', 'green', 'cyan', 'red', 'magenta', 'yellow', 'white']);
            $player->save();
        }

        $cellSum = $request->width * $request->height;
        for ($cell = 1; $cell <= $cellSum; $cell++) {
            $cells = new Cell();
            $cells->color = Arr::random(['blue', 'green', 'cyan', 'red', 'magenta', 'yellow', 'white']);
            $cells->save();
        }


        $fields = new Field();
        $fields->width = $request->width;
        $fields->height = $request->height;
        $fields->cells_id = $cells->id;
        $fields->save();


        $game = new Game();
        $game->id = $uuid;
        $game->players_id = $player->id;
        $game->fields_id = $fields->id;
        $game->currentPlayerId = $player->id;
        $game->winnerPlayerId = 0;
        $game->save();

        return response()->json([
            'id' => $uuid
        ])->setStatusCode(201, 'Create');

    }

    /**
     * Ход игры
     */
    public function gameID(request $request){

        $validate = Validator::make($request->all(), [
            'gameId' => 'required',
            'playerId' => 'required',
            'color' => 'required'
        ]);

        if($validate->fails()) {
            return response()->json([
                'Неправильные параметры запроса'
            ])->setStatusCode('400', 'Validate Error');
        }
        $game = Game::where('id', $request->gameId)->first();
        if(!$game) {
            return response()->json([
                'Игра с указанным ID не существует'
            ])->setStatusCode('404', 'Error');
        }



        if($game->winnerPlayerId == 0) {
            $cell = Cell::where('player_id', null)->get();
            if(count($cell) == 0) {
                $cellOne = count(Cell::where('player_id', '1')->get());
                $cellTwo = count(Cell::where('player_id', '2')->get());
                if($cellOne > $cellTwo) {
                    $playOne = Player::where('id', '1')->first();
                    $game->winnerPlayerId = $playOne->id;
                    $game->save();
                }elseif($cellTwo > $cellOne){
                    $playTwo = Player::where('id', '2')->first();
                    $game->winnerPlayerId = $playTwo->id;
                    $game->save();
                }
                return response()->json([
                    'Игра завершена!'
                ]);
            }else {
                if($request->playerId != $game->currentPlayerId) {
                    return response()->json([
                        'Игрок с указанным номером не может сейчас ходить'
                    ])->setStatusCode('403', 'Error');
                }
                $playOne = Player::where('id', '1')->first();
                $playTwo = Player::where('id', '2')->first();

                if($request->playerId == 1) {
                    if($request->color == $playTwo->color) {
                        return response()->json([
                            'Игрок с указанным номером не может выбрать указанный цвет'
                        ])->setStatusCode('409', 'Error');
                    }
                    $cell = Cell::where('color', $request->color)->get();

                    foreach ($cell as $cells) {
                        $cells->player_id = $playOne->id;
                        $cells->save();
                    }
                    $playOne->color = $request->color;
                    $playOne->save();
                    $game->currentPlayerId = $playTwo->id;
                    $game->save();
                }else {
                    if($request->color == $playOne->color) {
                        return response()->json([
                            'Игрок с указанным номером не может выбрать указанный цвет'
                        ])->setStatusCode('409', 'Error');
                    }
                    $cell = Cell::where('color', $request->color)->get();
                    //2
                    foreach ($cell as $cells) {
                        $cells->player_id = $playTwo->id;
                        $cells->save();
                    }
                    $playTwo->color = $request->color;
                    $playTwo->save();
                    $game->currentPlayerId = $playOne->id;
                    $game->save();
                }

                return response()->json([
                   'Ход сделан!'
                ]);
            }
        }else {
            return response()->json([
                'Игра завершена!'
            ]);
        }






    }


    /**
     * Состояние игры
     */
    public function gameInfo(request $request) {
        $validate = Validator::make($request->all(), [
           'gameId' => 'required'
        ]);
        if($validate->fails()) {
            return response()->json([
                'error' => 'Неправильные параметры запроса'
            ])->setStatusCode('400', 'Validate Error');
        }
        $game = Game::where('id', $request->gameId)->first();

        if(!$game) {
            return response()->json([
                'error' => 'Игра с указанным ID не существует'
            ])->setStatusCode('404', 'Error');
        }

        return new GameResource(Game::with('fields')->with('players')->where('id', $request->gameId)->first());


    }


}
