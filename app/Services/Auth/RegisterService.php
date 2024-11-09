<?php

namespace App\Services\Auth;

use App\Models\Admin;
use App\Models\Client;
use App\Models\Worker;
use App\Mail\VerificationEmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class RegisterService {
    protected $model;
    protected $guard;

    public function __construct($guard)
    { 
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
        $validator = Validator::make($request->all(), $request->rules([]));

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // التحقق من صحة البيانات
        $validatedData = $validator->validated();
        $validatedData['password'] = bcrypt($validatedData['password']); // تشفير كلمة المرور

        // معالجة الصورة
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $existingPhotoPath = 'public/Photo/' . $this->guard . 'sProfile/' . $photo->getClientOriginalName();
            if (File::exists($existingPhotoPath)) {
                return response()->json(['error' => 'The photo already exists'], 422);
            }
            $validatedData['photo'] = $photo->store('Photo/' . $this->guard . 'sProfile', 'public');
        } else {
            $validatedData['photo'] = 'Photo/ClientsProfile/default.jpg'; // الصورة الافتراضية
        }

        return $validatedData;
    }

    public function store($data)
    { 
        return $this->model->create($data);
    }

    public function generate_token($email)
{
    $token = substr(md5(rand(0, 9) . $email . time()), 0, 32);  
    $worker = $this->model->where('email', $email)->lockForUpdate()->first(); // قفل الصف

    if (!$worker) {
        throw new \Exception("User not found");
    }

    $worker->verification_token = $token; 
    $worker->save(); 

    return $worker; // تأكد من إعادة كائن العامل هنا
}

    function sendEmail($worker) 
    { 
        try {
            // هنا، worker يجب أن يكون كائن المستخدم وليس سلسلة
            Mail::to($worker->email)->send(new VerificationEmail($worker)); // تمرير الكائن بدلاً من الاسم فقط
        } catch (\Exception $e) {
            throw new \Exception('Error in sending email: ' . $e->getMessage());
        }
    } 
  


    public function register($request)
    { 
        try { 
            DB::beginTransaction();
            $validatedData = $this->validation($request);
            
            if ($validatedData instanceof \Illuminate\Http\JsonResponse) {
                return $validatedData;
            }
            
            $user = $this->store($validatedData); 
            
            $user = $this->generate_token($user->email); // يجب أن يكون كائن Worker
            
            // الآن يتم تمرير الكائن الكامل إلى sendEmail
            $this->sendEmail($user); 
    
            DB::commit(); 
    
            return response()->json(['message' => 'Account has been created, please check your email'], 200);
            
        } catch (\Exception $th) {
            DB::rollBack();   
            throw new \Exception('Error in: ' . $th->getMessage());
        }
    }
    
   
}
