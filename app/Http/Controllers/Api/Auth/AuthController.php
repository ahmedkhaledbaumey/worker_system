<?php
namespace App\Http\Controllers\Api\Auth;







use Exception;


use App\Models\Admin;
use App\Models\Client;
use App\Models\Worker;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\Auth\LoginService;
use App\Services\Auth\RegisterService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\AuthenticationException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;

class AuthController 
extends Controller
{  
    public function __construct()
    {
        $this->middleware('auth:admin', ['except' => ['login', 'register']]);
    }

    public function login(LoginRequest $request, $guard)
    { 
//لو احتاجت اللوجيك موجود فالكنرولر الممنفصله  او فالسيرفس بس روح للكنترولر 

        return (new LoginService($guard))->login($request, $guard);
    }


    public function register(RegisterRequest $request, $guard)
    { 
        // switch ($guard) {
        //     case 'admin': 
        //         $model = Admin::class;
        //         break;
        //     case 'client': 
        //         $model = Client::class;
        //         break;
        //     case 'worker': 
        //         $model = Worker::class;
        //         break;
            
        //     default:
        //     $model = Admin::class;
        //     break;
        // }
        // try {
        //     $validator = Validator::make($request->all(), [
        //         'name' => 'required|string',
        //         'email' => 'required|email|unique:'.$guard.'s',
        //         'password' => 'required|string|min:6',
        //         'phone' => 'nullable|string|max:17',
        //         'photo' => 'nullable|image|mimes:png,jpg,jpeg,pdf',
        //         'location' => 'nullable|string',

        //     ]);

        //     if ($validator->fails()) {
        //         return response()->json(['error' => $validator->errors()->first()], 422);
        //     }

        //     $inputData = $validator->validated();
        //     $inputData['password'] = bcrypt($request->password);

            
        //     if ($request->hasFile('photo')) {
        //         $photo = $request->file('photo');

        //         // Check if the file already exists
        //         $existingPhotoPath = 'public/Photo/'.$guard.'sProfile/' . $photo->getClientOriginalName();
        //         if (File::exists($existingPhotoPath)) {
        //             return response()->json(['error' => 'The photo already exists'], 422);
        //         }

        //         $inputData['photo'] = $photo->store('Photo/'.$guard.'sProfile', 'public');
        //     }  
        //     else{ 
        //         $inputData['photo'] = 'Photo/ClientsProfile/default.jpg'; // تأكد أن الصورة الافتراضية موجودة في هذا المسار

        //     } 
           

        //     $admin = $model::create($inputData);

        //     // Optionally, you may automatically log in the registered user.
        //     $token = auth($guard)->login($admin);

        //     return response()->json([
        //         'message' => 'admin registered successfully',
        //         'admin' => $admin,
        //         'token' => $token,
        //         'token_type' => 'Bearer',
        //     ]);
        // } catch (Exception $e) {
        //     return response()->json(['error' => 'Registration failed. ' . $e->getMessage()], 500);
        // } 
        // return ( new RegisterService( $guard))->register($request, $guard) ; 
        $request->merge(['guard' => $guard]); // دمج الحارس في الطلب
  
    
        // قم بإجراء التحقق على البيانات المرسلة في الطلب
    

        return (new RegisterService($guard))->register($request);

    }


    public function logout($guard)
    {
        try {
            // Verify the user is authenticated
            $admin = auth($guard)->user();

            if (!$admin) {
                return response()->json(['error' => ' not authenticated'], 401);
            }

            // Logout the user
            auth($guard)->logout();

            // Add cache control headers
            return response()->json(['message' => ' successfully signed out'])->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        } catch (JWTException $e) {
            return response()->json(['error' => 'Error while logging out'], 500);
        }
    }


    public function refresh($guard)
    {
        try {
            $newToken = auth($guard)->refresh();

            if (!$newToken) {
                return response()->json(['error' => 'Invalid refresh token'], 401);
            }

            return $this->createNewToken($newToken, $guard);
        } catch (TokenExpiredException $e) {
            return response()->json(['error' => 'Token has expired'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'Invalid token'], 401);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Error while refreshing the token'], 500);
        }
    }

    public function userProfile($guard)
    {
        try {
            $admin = auth($guard)->user();

            if (!$admin) {
                return response()->json(['error' => ' not authenticated'], 401);
            }

            return response()->json($admin);
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'Invalid token'], 401);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Error fetching  profile'], 500);
        }
    }

    protected function createNewToken($token,$guard)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'admin' => auth($guard)->user()
        ]);
    }
}