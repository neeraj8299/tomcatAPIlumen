<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AuthenticationController extends Controller
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
    public function login(Request $request)
    {
        return response()->json([
            'message' => 'Logged In Succesfully'
        ]);
    }

    /**
     * function to create group with participants details
     * 
     * @param Request $request
     * @return object
     */
    public function addUserData(Request $request)
    {
        $this->validate($request, [
            'file' => 'required|file'
        ]);

        $row = 0;
        $file = fopen($request->file, 'r');

        while (($data = fgetcsv($file, 1000, ";")) !== FALSE) {
            if ($row  != 0) {
                try {
                    $user = new User;
                    $user->name = $data[0];
                    $user->email = $data[1];
                    $user->phone_number = $data[2];
                    $user->child_age_group = $data[3];
                    $user->hobbies = json_encode(isset($data[4]) ? $data[4] : []);
                    $user->favourite_series = json_encode(isset($data[5]) ? $data[5] : []);
                    $user->source_of_info = isset($data[6]) ? $data[6] : '';
                    $user->role_id = 1;
                    $user->save();
                } catch (\Exception $e) {
                    return response()->json([
                        'data' => 'Unable to processs Your CSV'
                    ], 500);
                }
            }
            $row++;
        }

        return response()->json([
            'message' => 'Data Added Succesfully'
        ]);
    }
}
