<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Group;
use App\Models\GroupGame;
use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class QuizController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * function to create group with participants details
     * 
     * @param Request $request
     * @return object
     */
    public function createGroup(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|unique:groups,name',
            'participants' => 'required|array|min:1',
            'participants.*' => 'required|email|exists:users,email'
        ]);

        $groupDetails = Group::create([
            'name' => $request->name
        ]);

        $participantsEmail = array_unique($request->participants);

        foreach ($participantsEmail as $userEmail) {
            $userId = User::where('email', $userEmail)->value('id');

            $userGroup = UserGroup::firstOrCreate([
                'user_id' => $userId,
                'group_id' => $groupDetails->id
            ]);
        }

        return response()->json([
            'message' => 'Group Created Succesfully'
        ]);
    }

    /**
     * function to create game with group particiapnts
     * 
     * @param Request $request
     * @return object
     */
    public function createGame(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|unique:games,name',
            'groups' => 'required|array|min:1',
            'groups.*' => 'required|exists:groups,name'
        ]);

        $gameDeatils = Game::create([
            'name' => $request->name
        ]);

        $participantsGroups = array_unique($request->groups);

        foreach ($participantsGroups as $userGroup) {
            $groupId = Group::where('name', $userGroup)->value('id');

            $groupGame = GroupGame::firstOrNew([
                'game_id' => $gameDeatils->id,
                'group_id' => $groupId
            ]);

            $groupGame->save();
        }

        return response()->json([
            'message' => 'Game Created Succesfully'
        ]);
    }

    /**
     * function to get Leaderboard data on game id
     * 
     * @param int $gameId
     * @return object
     */
    public function getLeadaerBoard(int $gameId)
    {
        $validator = Validator::make([
            'gameId' => $gameId
        ], [
            'gameId' => 'required|integer|exists:games,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'gameId' => 'invalid Game Id'
            ], 422);
        }

        $data = GroupGame::join('groups', 'groups.id', 'group_id')
            ->select('score', 'groups.name as group_name')
            ->where('game_id', $gameId)
            ->orderBy('group_games.updated_at', 'desc')
            ->get();

        return response()->json([
            'data' => $data
        ]);
    }

    /**
     * Update Group Score Data Game wise
     * 
     * @param Request $request
     * @return object
     */
    public function updateGameScore(Request $request)
    {
        $this->validate($request, [
            'gameId' => 'required|exists:games,id',
            'groupId' => 'required|exists:groups,id',
            'score' => 'required|numeric'
        ]);

        $scoreUpdate = GroupGame::where([['game_id', $request->gameId], ['group_id', $request->groupId]])->update([
            'score' => $request->score
        ]);

        $message = 'Unable to update score';
        $code = 400;

        if ($scoreUpdate) {
            $message = 'Score Updated Succesfully';
            $code = 200;
        }
        return response()->json([
            'message' => $message
        ], $code);
    }
}
