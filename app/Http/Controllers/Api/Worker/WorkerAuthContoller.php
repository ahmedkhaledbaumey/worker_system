<?php
namespace App\Http\Controllers\Api\worker;







use Exception;

use App\Models\Worker;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
// use Symfony\Component\Mime\Part\File;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;

class WorkerAuthContoller 
extends Controller
{  
    public function __construct()
    {
        $this->middleware('auth:worker', ['except' => ['login', 'register']]);
    }

    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string|min:6',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            if (!$token = auth('worker')->attempt($validator->validated())) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            return $this->createNewToken($token); 
            

        } catch (TokenExpiredException $e) {
            return response()->json(['error' => 'Token has expired'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'Invalid token'], 401);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Error while processing the token'], 500);
        } catch (AuthenticationException $e) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        } catch (HttpException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getStatusCode());
        }
    }


    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'email' => 'required|email|unique:workers',
                'password' => 'required|string|min:6',
                'phone' => 'required|string|max:17',
                'photo' => 'nullable|image|mimes:png,jpg,jpeg,pdf',
                'location' => 'required|string',

            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 422);
            }

            $inputData = $validator->validated();
            $inputData['password'] = bcrypt($request->password);
 


            if ($request->hasFile('photo')) {
                $photo = $request->file('photo');

                // Check if the file already exists
                $existingPhotoPath = 'public/Photo/WorkersProfile/' . $photo->getClientOriginalName();
                if (File::exists($existingPhotoPath)) {
                    return response()->json(['error' => 'The photo already exists'], 422);
                }

                $inputData['photo'] = $photo->store('Photo/WorkersProfile', 'public');
            }  
            else{ 
                $inputData['photo'] = 'Photo/WorkersProfile/default.jpg'; // تأكد أن الصورة الافتراضية موجودة في هذا المسار

            } 
           
  
            $worker = Worker::create($inputData);

            // Optionally, you may automatically log in the registered user.
            $token = auth('worker')->login($worker);

            return response()->json([
                'message' => 'worker registered successfully',
                'worker' => $worker,
                'token' => $token,
                'token_type' => 'Bearer',
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Registration failed. ' . $e->getMessage()], 500);
        }
    }


    public function logout()
    {
        try {
            // Verify the user is authenticated
            $worker = auth('worker')->user();

            if (!$worker) {
                return response()->json(['error' => 'worker not authenticated'], 401);
            }

            // Logout the user
            auth('worker')->logout();

            // Add cache control headers
            return response()->json(['message' => 'worker successfully signed out'])->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        } catch (JWTException $e) {
            return response()->json(['error' => 'Error while logging out'], 500);
        }
    }


    public function refresh()
    {
        try {
            $newToken = auth('worker')->refresh();

            if (!$newToken) {
                return response()->json(['error' => 'Invalid refresh token'], 401);
            }

            return $this->createNewToken($newToken);
        } catch (TokenExpiredException $e) {
            return response()->json(['error' => 'Token has expired'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'Invalid token'], 401);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Error while refreshing the token'], 500);
        }
    }

    public function userProfile()
    {
        try {
            $worker = auth('worker')->user();

            if (!$worker) {
                return response()->json(['error' => 'worker not authenticated'], 401);
            }

            return response()->json($worker);
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'Invalid token'], 401);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Error fetching worker profile'], 500);
        }
    }

    protected function createNewToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('worker')->factory()->getTTL() * 60,
            'worker' => auth('worker')->user() 
        ]);
    }
}