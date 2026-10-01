<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Customers;
use Illuminate\Support\Facades\Hash;
use Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Mail;

class LoginController extends Controller
{

    //
    public function index(Request $request)
    {
        $common = [];
        if ($request->isMethod('post')) {
            $req_fields        = array();
            $req_fields['email']        = "required";
            $req_fields['password']     = "required";
            $errormsg = [
                "email"       => translate("Email"),
                "password"    => translate("Password"),
            ];

            $validation = Validator::make(
                $request->all(),
                $req_fields,
                [
                    'required' => 'The :attribute field is required.',
                ],
                $errormsg
            );

            if ($validation->fails()) {
                return back()->withErrors($validation)->withInput();
            }
            $user = User::where(['email' => $request->email])->whereNull('is_delete')->first();
            //dd($user);
            if ($user) {
                if (Hash::check($request->password, $user->password)) {
                    $userdata = array(
                        'email' => $request->email,
                        'password' => $request->password
                    );
                    if (Auth::attempt($userdata)) {
                        return redirect(route('admin.dashboard'))->withErrors(['success' => "Login Successfully"]);
                    } else {
                        return back()->withErrors(['error' => "Invalid Credential",]);
                    }
                } else {
                    return back()->withErrors(['error' => "Invalid Credential",]);
                }
            } else {
                return back()->withErrors(['error' => "Invalid Credential"]);
            }
        }

        return view('admin.login', compact('common'));
    }

    ///Forget Password
    public function forgot_password(Request $request)
    {

        if ($request->isMethod('post')) {



            $req_fields        = array();

            $req_fields['email']        = "required";



            $errormsg = [

                "email"       => translate("Email"),

            ];



            $validation = Validator::make(

                $request->all(),

                $req_fields,

                [

                    'required' => 'The :attribute field is required.',

                ],

                $errormsg

            );



            if ($validation->fails()) {

                return back()->withErrors($validation)->withInput();
            }



            $admin = User::where(['email' => $request->email])->first();



            if ($admin) {



                $user_id          = $admin->id;

                $name          = $admin->name;

                $email         = $admin->email;



                $expFormat = mktime(

                    date("H"),
                    date("i"),
                    date("s"),
                    date("m"),
                    date("d") + 1,
                    date("Y")

                );

                $expDate = date("Y-m-d H:i:s", $expFormat);

                $key = md5($user_id);

                $addKey = substr(md5(uniqid(rand(), 1)), 3, 10);

                $key = $key . $addKey;



                $admin->reset_password_key = $key;

                $admin->reset_password_expiry = $expDate;

                $admin->save();



                $data = array('subject' => "Forgot Password", 'email' => $email, 'key' => $key, 'name' => $name, 'page' => 'email.forgotpassword');



                Mail::send($data['page'], $data, function ($message) use ($data) {

                    $message->to($data['email'])->subject($data['subject']);
                });

                return redirect(route('admin.forgot_password'))->withErrors(["success" => "Mail Send Successfully"]);
            } else {

                return back()->withErrors(['error' => "Invalid Email Address"]);
            }
        } else {

            return view('admin.forgot_password');
        }
    }

    ///Reset Password
    public function reset_password($user_id = "", Request $request)
    {

        if ($user_id != "") {

            $admin =  User::where('reset_password_key', $user_id)->first();

            if ($admin) {
            } else {

                return redirect(route('admin.login'));
            }
        } else {

            return redirect(route('admin.login'));
        }



        if ($request->isMethod('post')) {



            $validator = Validator::make($request->all(), [

                'new_password'     => 'required',

                'confirm_password' => 'required_with:new_password|same:new_password|'

            ]);

            if ($validator->fails()) {

                return redirect(route('admin.reset_password', $user_id))->withErrors($validator)->withInput();
            }

            $admin = User::where(['reset_password_key' => $user_id])->first();

            $admin->password = Hash::make($request->new_password);

            $admin->reset_password_key = NULL;

            $admin->reset_password_expiry = NULL;

            $admin->save();



            $name          = $admin->name;

            $email         = $admin->email;

            $data = array('subject' => "Change Password", 'email' => $email, 'name' => $name, 'page' => 'email.changepassword');



            Mail::send($data['page'], $data, function ($message) use ($data) {

                $message->to($data['email'])->subject($data['subject']);
            });



            return redirect(route('admin.login'))->withErrors(["success" => "Password Update Successfully "]);
        }

        return view('admin.reset_password', compact('user_id'));
    }

}
