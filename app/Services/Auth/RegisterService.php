<?php 

namespace App\Services\Auth; 

use App\Models\Admin;
use App\Models\Client;
use App\Models\Worker;
use Illuminate\Support\Facades\Validator;

 class RegisterService {  

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

 }