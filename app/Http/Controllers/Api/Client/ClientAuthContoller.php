<?php
namespace App\Http\Controllers\Api\Client;







use Exception;

use App\Models\Client;
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

class ClientAuthContoller 
extends Controller
{  
    public function __construct()
    {
        $this->middleware('auth:client', ['except' => ['login', 'register']]);
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

            if (!$token = auth('client')->attempt($validator->validated())) {
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
                'email' => 'required|email|unique:clients',
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
                $existingPhotoPath = 'public/Photo/ClientsProfile/' . $photo->getClientOriginalName();
                if (File::exists($existingPhotoPath)) {
                    return response()->json(['error' => 'The photo already exists'], 422);
                }

                $inputData['photo'] = $photo->store('Photo/ClientsProfile', 'public');
            }  
            else{ 
                $inputData['photo'] = 'Photo/ClientsProfile/default.jpg'; // تأكد أن الصورة الافتراضية موجودة في هذا المسار

            } 
           
  
            $client = client::create($inputData);

            // Optionally, you may automatically log in the registered user.
            $token = auth('client')->login($client);

            return response()->json([
                'message' => 'client registered successfully',
                'client' => $client,
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
            $client = auth('client')->user();

            if (!$client) {
                return response()->json(['error' => 'client not authenticated'], 401);
            }

            // Logout the user
            auth('client')->logout();

            // Add cache control headers
            return response()->json(['message' => 'client successfully signed out'])->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        } catch (JWTException $e) {
            return response()->json(['error' => 'Error while logging out'], 500);
        }
    }


    public function refresh()
    {
        try {
            $newToken = auth('client')->refresh();

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
            $client = auth('client')->user();

            if (!$client) {
                return response()->json(['error' => 'client not authenticated'], 401);
            }

            return response()->json($client);
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'Invalid token'], 401);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Error fetching client profile'], 500);
        }
    }

    protected function createNewToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('client')->factory()->getTTL() * 60,
            'client' => auth('client')->user() 
        ]);
    }
}