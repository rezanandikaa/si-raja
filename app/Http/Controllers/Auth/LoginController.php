<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\RecoveryPassword;
use App\Models\System\Sy_user_recovery;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Mews\Captcha\Facades\Captcha;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Ramsey\Uuid\Uuid;

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
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->redirectTo = '/';
    }

    public function showLoginForm()
    {
        return view('baduyengine.auth.login');
    }

    protected function validateLogin(Request $request)
    {
        $this->validate($request, [
            $this->username() => 'required|string',
            'password' => 'required|string',
            'captcha' => 'required|captcha'
        ]);
    }

    protected function authenticated(Request $request, $user)
    {
        logbook('Berhasil login ke sistem');
    }

    protected function logout(Request $request)
    {
        logbook('Berhasil logout dari sistem');

        $this->guard()->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        if ($response = $this->loggedOut($request)) {
            return $response;
        }

        return $request->wantsJson()
            ? new JsonResponse([], 204)
            : redirect('/');
    }

    protected function credentials(Request $request)
    {
        return $request->only($this->username(), 'password', 'active_flag');
    }

    protected function forgot(Request $request)
    {
        return view('baduyengine.auth.forgot');
    }

    protected function forgotPass(Request $request)
    {
        $email = $request->email ?? '';
        if ($email != ''){
            $record = User::where('email', $email)
                ->first();
            if ($record) {
                DB::beginTransaction();
                try {
                    $token = Uuid::uuid4()->toString();
                    $expired = Carbon::now()->addHour()->format('Y-m-d H:i:s');
                    $data = [
                        'email' => $email,
                        'token' => $token,
                        'expired' => $expired,
                        'close_flag' => false,
                    ];
                    Sy_user_recovery::where('email', $email)
                        ->update(['close_flag' => true]);
                    Sy_user_recovery::create($data);
                    Mail::to($email)->send(new RecoveryPassword($data));
                    DB::commit();
                } catch (\Exception $err) {
                    DB::rollBack();
                    Log::info("Error {$err->getCode()} : ". $err->getMessage());
                }
            }
        }
        return redirect(route('login'));
    }

    public function forgotPassRecovery($token)
    {
        if ($token != null) {
            $record = DB::table('sy_user_recovery')
                ->where('token', $token)
                ->where('expired', '>=', date('Y-m-d H:i:s'))
                ->where('close_flag', false)
                ->get()
                ->first();

            if ($record) {
                $data = [
                    'token' => $token
                ];
                return view('baduyengine.auth.recovery', compact('data'));
            }
        }
        return redirect(route('login'));
    }

    protected function recoveryPass(Request $request, $token)
    {
        $validated = Validator::make($request->all(), [
            'password' => 'required|confirmed|min:8',
        ]);

        if($validated->fails()){
            $result = [
                'status' => 'FAIL',
                'message' => $validated->getMessageBag()->first()
            ];
            return response()->json($result);
        }

        $data['password'] = bcrypt($request->password);
        $record = DB::table('sy_user_recovery')
                ->where('token', $token)
                ->where('expired', '>=', date('Y-m-d H:i:s'))
                ->where('close_flag', false);

        $user = $record->first();
        if ($user == null) {
            $result = [
                'status' => 'FAIL',
                'message' => 'Sorry! Link Expired'
            ];
            return response()->json($result);
        }

        DB::beginTransaction();
        try {
            DB::table('mt_user')->where('email', $user->email)->update($data);
            $record->update(['close_flag' => true]);
            $result = [
                'status' => 'OK',
                'message' => 'Data tersimpan'
            ];
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info($e);
            $result = [
                'status' => 'FAIL',
                'message' => $e->getMessage()
            ];
        }
        return response()->json($result);
    }

    public function reloadCaptcha()
    {
        return response()->json(['captcha'=> Captcha::img('flat')]);
    }
}
