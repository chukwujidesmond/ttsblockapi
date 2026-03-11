<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
     public function register(Request $request)
    {
        
        $request->validate([
            // 'first_name' => 'required|string',
            // 'last_name' => 'required|string',
            'email'=>'required|string|unique:users',
            'password'=>'required|min:8',
            // 'confirm_password' => 'required|same:password'
        ]);

        $user = User::where('email', $request->email)->first();

        if($user){
            return response()->json([
                'message' => 'User with this Email already Exist.'
            ],409);
        }
        // if ($request->has('appsumo_license_key') && 
        //     $request->has('tier') && 
        //     $request->has('ref') && 
        //     $request->input('ref') === 'appsumo') {
            
        //     return $this->registerAppsumoUsers($request);
        // }
        if ($request->input('ref') === 'appsumo') { 
            return $this->registerAppsumoUsers($request);
        }

        $str =rand();
        $token= md5($str);
     
        $user = new User;
        $user->full_name =  $request->first_name . ' ' . $request->last_name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->token =$token;


        if($user->save()){
                // create an api token and persist the plain value so the
                // frontend can immediately start using it; we do not return
                // it in the response here because the registration endpoint
                // currently does not return JSON, but feel free to add it if
                // you need to.
                $tokenResult = $user->createToken('Personal Access Token');
                $user->api_token = $tokenResult->plainTextToken;
                $user->save();

                $body = [
                    'email' =>$user->email,
                    'fullname' =>$user->fullname,
                    'token' => $user->token,
                    'link' => $request->link
                ];
            Mail::to($user->email)->send(new newUserRegistrationMail($body));

            // $tokenResult = $user->createToken('Personal Access Token');
            // $token = $tokenResult->plainTextToken;

            // return $user->createToken(
            //     'token-name', ['*'], now()->addWeek()
            // )->plainTextToken;
            $sub_plan = 'free_plan';
            $limit = 1;
            $type = 'free';
            $end_date = Carbon::now()->addDays(8);
            $postlimit = 10;

            $this->createWorkspace($user);
            $this->registerUserSubscription($user->id, $sub_plan, $limit, $type, $end_date, $postlimit);
            $this->checkIfTokenIsActive($user);

            return response()->json([
                'message' => 'Kindly check your Email for Verification. Thanks!',
                // 'accessToken'=> $token,
            ],201);
        }
        else{
            return response()->json(['error'=>'Provide proper details']);
        }
    }
}
