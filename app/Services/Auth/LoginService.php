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
    // public function getStatus($email)
    // {
    //     $user = $this->model->where('email', $email)->first();
    
    //     if (!$user) {
    //         // إذا لم يتم العثور على المستخدم
    //         return null ; 
    //     }
    
    //     return $user->status;
    // }
    
    public function getStatus($email)
    {
        $user = $this->model->where('email', $email)->first();
    
        if (!$user) {
            // إذا لم يتم العثور على المستخدم
            return response()->json(['error' => 'User not found'], 404);
        }
    
        if ($user->status == 0) {
            // إذا كان الحساب غير مفعل
            return response()->json(['error' => 'Account is not active'], 401);
        }
    
        return $user; // إعادة المستخدم إذا كان كل شيء صحيح
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

    // الحصول على المستخدم أو رسالة الخطأ من دالة getStatus
    $statusResponse = $this->getStatus($request->email);

    // إذا كانت النتيجة استجابة، قم بإعادتها
    if ($statusResponse instanceof \Illuminate\Http\JsonResponse) {
        return $statusResponse;
    }

    // تابع عملية التحقق وإنشاء التوكين
    $token = $this->isValid($data);
    
    return $this->createNewToken($token, $guard);
}

//الطريقتين شغالين بس دي جديده خالص      

//     public function login($request, $guard)
// {
//     $data = $this->validation($request);

//     $status = $this->getStatus($request->email);
    
//     if (is_null($status)) {
//         // إعادة الاستجابة الخاصة بعدم العثور على المستخدم
//         return response()->json(['error' => 'User not found'], 404);
//     }

//     $token = $this->isValid($data);

//     if ($status == 0) {
//         return response()->json(['error' => 'Account is not active'], 401);
//     }

//     return $this->createNewToken($token, $guard);
// }


}
