<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\Validator;
use App\Models\Admin;
use App\Models\Client;
use App\Models\Worker;

class LoginService
{
    protected $model;
    protected $guard;

    public function __construct($guard)
    { 
        // بناء نموذج ديناميكي بناءً على الحارس
        switch ($guard) {
            case 'admin':
                $this->model = new Admin(); 

                break;
            case 'client':
                $this->model = new Client();
                break;
            case 'worker':
                $this->model = new Worker();
                break;
            default:
                throw new \Exception('Invalid guard');
        } 
        $this->guard = $guard;    
     }

    public function validation($request)
    {
        $validator = Validator::make($request->all(), $request->rules());
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        return $validator;
    }

    public function isValid($data)
    {
        if (!$token = auth($this->guard)->attempt($data->validated())) {
            return response()->json(['error' => 'Invalid data'], 401);
        }
        return $token;
    }

    public function getStatus($email)
    {
        $user = $this->model->where('email', $email)->first();
        if ($user) {
            if ($user->status == 0) {
                return response()->json('Your account is pending', 403);
            } else {
                return $user;
            }
        }
        return response()->json('User not found', 404);
    }

    protected function createNewToken($token, $guard)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => auth($guard)->user()
        ]);
    }

    public function login($request, $guard)
    {
        $data = $this->validation($request);

        $token = $this->isValid($data);

        $this->getStatus($request->email);

        return $this->createNewToken($token, $guard);
    }
}
