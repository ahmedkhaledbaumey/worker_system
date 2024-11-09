<?php 

namespace App\Services\Auth;

use Illuminate\Support\Facades\DB;
use App\Models\Admin;
use App\Models\Client;
use App\Models\Worker;
use Exception;

class VerifyService
{
    protected $model;
    protected $guard;

    public function __construct($guard)
    { 
        // تحديد النموذج بناءً على الحارس
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
                throw new Exception('Invalid guard');
        }
        $this->guard = $guard;
    }

    public function verifyToken($token)
    {
        // البحث عن المستخدم باستخدام التوكن والتحقق من وجوده
        $user = $this->model->where('verification_token', $token)->first();

        if (!$user) {
            return response()->json(['error' => 'Invalid verification token'], 404);
        }

        // تحديث حالة المستخدم وإزالة التوكن
        $user->email_verified_at = now();
        $user->verification_token = null;
        $user->save();

        return response()->json(['message' => 'Account verified successfully'], 200);
    }
}
