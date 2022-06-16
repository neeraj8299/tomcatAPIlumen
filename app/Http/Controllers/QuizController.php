<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Room;
use App\Models\RoomSession;
use App\Models\Session;
use App\Models\User;
use App\Models\UserRoom;
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
     * Group Display Name For a Group in Game
     * @var array
     */
    public $groupDisplayNameList = [
        'A',
        'B',
        'C',
        'D',
        'E',
        'F',
        'G',
        'H',
        'I',
        'J',
        'K',
        'L',
        'M',
        'N',
        'O',
        'P',
        'Q',
        'R',
        'S',
        'T',
        'U',
        'V',
        'W',
        'X',
        'Y',
        'Z'
    ];

    /**
     * function to add new game with their uuid
     * 
     * @param Request $request
     * @return object
     */
    public function createGame(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|unique:games,name'
        ]);

        $gameDeatils = Game::firstOrNew([
            'name' => $request->name
        ]);

        $gameDeatils->save();

        return response()->json([
            'data' => 'Game Added SuccesFully'
        ]);
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

        $groupDetails = Room::create([
            'name' => $request->name
        ]);

        $participantsEmail = array_unique($request->participants);

        foreach ($participantsEmail as $userEmail) {
            $userId = User::where('email', $userEmail)->value('id');

            $userGroup = UserRoom::firstOrCreate([
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
    public function createGameSession(Request $request)
    {
        $this->validate($request, [
            'session_uuid' => 'required|string',
            'game_name' => 'required|string|exists:games,name',
            'room_name' => 'required|string',
            'participants' => 'array',
            'participants.*' => 'email|exists:users,email'
        ]);

        $gameDeatils = Game::where('name', $request->game_name)->first();

        if ($gameDeatils->status === 'inactive') {
            return response()->json([
                'data' => 'Unable to Create Session as Game is Inactive'
            ], 400);
        }

        $roomName = $request->room_name;

        $roomDetails = Room::firstOrCreate([
            'name' => $roomName
        ]);

        $gameSessionDetails = Session::firstOrCreate([
            'session_uuid' => $request->session_uuid,
            'game_id' => $gameDeatils->id
        ]);

        $gameSessionRoomCount = RoomSession::where('session_id', $gameSessionDetails->id)->count();

        if ($gameSessionRoomCount >= count($this->groupDisplayNameList)) {
            return response()->json([
                "data" => "Unable To join Game Group Maximum Limit Exceeded"
            ], 400);
        }

        $groupGame = RoomSession::firstOrNew([
            'session_id' => $gameSessionDetails->id,
            'room_id' => $roomDetails->id,
            'display_room_name' => $this->getGroupDisplayName($gameSessionRoomCount),
        ]);

        $groupGame->save();

        $participantsEmail = array_unique($request->participants ?? []);

        foreach ($participantsEmail as $userEmail) {
            $userId = User::where('email', $userEmail)->value('id');

            $userGroup = UserRoom::firstOrCreate([
                'user_id' => $userId,
                'room_id' => $roomDetails->id
            ]);
        }

        return response()->json([
            'message' => 'Game Session Created Succesfully'
        ]);
    }

    /**
     * Function to get GroupDisplayName
     * 
     * @param int $groupCount
     * @return string
     */
    private function getGroupDisplayName(int $groupCount)
    {
        return $this->groupDisplayNameList[$groupCount];
    }

    /**
     * function to get Leaderboard data on game name
     * 
     * @param string $sessionUuid
     * @return object
     */
    public function getLeadaerBoard(string $sessionUuid)
    {
        $validator = Validator::make([
            'gameSessionName' => $sessionUuid
        ], [
            'gameSessionName' => 'required|exists:sessions,session_uuid',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'gameId' => 'Invalid Session UUID'
            ], 422);
        }

        $sessionID = Session::where('session_uuid', $sessionUuid)->value('id');

        $data = RoomSession::join('rooms', 'rooms.id', 'room_id')
            ->select('score', 'rooms.name as group_name', 'room_sessions.display_room_name')
            ->where('session_id', $sessionID)
            ->orderBy('group_games.score', 'desc')
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
    public function updateGameSessionScore(Request $request)
    {
        $this->validate($request, [
            'sessionUuid' => 'required|string|exists:sessions,session_uuid',
            'roomName' => 'required|exists:rooms,name',
            'score' => 'required|numeric'
        ]);

        $sessionID = Session::where('session_uuid', $request->sessionUuid)->value('id');
        $roomId = Room::where('name', $request->roomName)->value('id');

        $scoreUpdate = RoomSession::where([['session_id', $sessionID], ['room_id', $roomId]])
                        ->update([
                            'score' => $request->score
                        ]);

        $response = [
            'message' => 'Unable to update score',
            'data' => []
        ];
        $code = 400;
        $updatedLeaderBoard = $this->getLeadaerBoard($request->sessionUuid);
        $updatedLeaderBoard = $updatedLeaderBoard->getData();

        $updatedLeaderBoard = (isset($updatedLeaderBoard->data) && !empty($updatedLeaderBoard->data)) ? $updatedLeaderBoard->data : [];

        if ($scoreUpdate) {
            $response = [
                'data' => $updatedLeaderBoard
            ];
            $code = 200;
        }

        return response()->json($response, $code);
    }

    /**
     * Function to Close Active Game
     * 
     * @param 
     * @return object
     */
    public function closeGameSession(Request $request)
    {
        $this->validate($request, [
            'sessionUuid' => 'required|string|exists:sessions,session_uuid',
        ]);

        $status = Session::where('session_uuid', $request->sessionUuid)->update([
            'status' => 'inactive'
        ]);

        return response()->json([
            'data' => $status ? 'Game Closed Succesffully' : 'Unable to Close Game'
        ], $status ? 200 : 400);
    }
}
