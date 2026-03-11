<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Session, Log,Exception,Redirect;
use App\Models\User;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    // protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        // logout should require a valid sanctum token, not the web guard
        $this->middleware('auth:sanctum')->only('logout');
    }


    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $credentials = request(['email','password']);
        if(!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        /**
         * @var \App\Models\User&\Laravel\Sanctum\HasApiTokens|null $user
         */
        $user = $request->user();

        if (! $user instanceof \App\Models\User) {
            // should never happen but make static analysis happy
            throw new \RuntimeException('Authenticated user is not a User instance');
        }
        // if (!$user->is_active ) {
        //     // throw new \Exception("User creation failed.");
        //     return response()->json([
        //         'message' => 'Kindly verify your Email. Thanks!',
        //     ],400);
        // }

        // if (!$user->is_account_active ) {
        //     // throw new \Exception("User creation failed.");
        //     return response()->json([
        //         'message' => 'Your account is disabled...!',
        //     ],400);
        // }

        
        // // 👉 Mark user as logged in
        // $user->is_logged_in = true;
        // $user->last_seen_at = now();
        // $user->save();

        // if we've previously issued a personal-access token for this
        // user we keep the plaintext value in the users table so we can
        // hand it back on subsequent logins. Sanctum does not persist the
        // unhashed token anywhere, only the hash is stored in
        // personal_access_tokens, hence the extra column.
        if ($user->api_token) {
            $token = $user->api_token;
        } else {
            $tokenResult = $user->createToken('Personal Access Token');
            $token = $tokenResult->plainTextToken;

            // persist the plain string so we can return it next time
            $user->update(['api_token' => $token]);
        }

        return response()->json([
            'accessToken' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function logout(Request $request)
    {
        $user = auth('sanctum')->user();
        
        if ($user) {
            // when the user logs out we delete their sanctum tokens and also
            // clear the stored plaintext value so that a fresh token will be
            // created the next time they log back in.  if you prefer the same
            // token to survive logout simply remove these two lines.
            /** @phpstan-ignore-line */
            $user->tokens()->delete();
            /** @phpstan-ignore-line */
            $user->update(['api_token' => null]);

            return response()->json([
                'status' => true,
                'message' => 'Successfully logged out'
            ], 200);
        }

        return response([
            'message' => 'No authenticated user'
        ], 401);
    }
}
