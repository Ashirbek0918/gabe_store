<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function create (Request $request){
        $validation = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|unique:users,email',
            'password' =>'required|min:8',
            'profile_photo' =>'required|url' 
        ]);
        if ($validation->fails()) {
            return ResponseController::error($validation->errors()->first(), 422);
        }
        $name = $request->name;
        $email = $request->email;
        $password = $request->password;
        $profile_photo = $request->profile_photo;
        $user = User::where('email',$email)->first();
        if($user){
            return ResponseController::error('This user already exits');
        }
        $user = User::create([
            'name'=>$name,
            'email'=>$email,
            'password'=>Hash::make($password),
            'profile_photo'=>$profile_photo
        ]);
        return ResponseController::success('Succesfuly',201);
    }

    public function login(Request $request){
        $email = $request->email;
        $password = $request->password;
        $user = User::where('email',$email)->first();
        if (!$user OR !Hash::check($password,$user->password)){
            return ResponseController::error('Email or Password incorrect');
        } 
        $token = $user->createToken('user')->plainTextToken;
        return ResponseController::data([
            'token'=>$token 
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return ResponseController::success('You have successfully logged out');
    }

    public function getme (Request $request){
        return $request->user();
    }

    public function employeelogin(Request $request){
        $employee = Employee::where('phone',$request->phone)->first();
        $password = $request->password;
        if(!$employee OR !Hash::check($password,$employee->password)){
            return ResponseController::error('Phone or password incorrect');
        }
        $token = $employee->createToken('employee :'.$employee->phone)->plainTextToken;
        return ResponseController::data([
            'employee_id'=>$employee->id,
            'name'=>$employee->name,
            'phone'=>$employee->phone,
            'role' =>$employee->role,
            'token'=>$token
        ]);
    }
    
    public function createemployee(Request $request){
        try{
            $this->authorize('create',Employee::class);
        }catch(\Throwable $th){
            return ResponseController::error('You Are not allowed');
        }
        $validation = Validator::make($request->all(),[
            'name' =>'required|unique:employees,name|string|max:255',
            'phone' =>'required|unique:employees,phone|min:10',
            'password' =>'required|min:8',
            'role' =>'required',
        ]);
        if ($validation->fails()) {
            return ResponseController::error($validation->errors()->first(), 422);
        }
        Employee::create([
            'name'=>$request->name,
            'phone'=>$request->phone,
            'password'=>Hash::make($request->password),
            'role'=>$request->role
        ]);
        return ResponseController::success('successful',201);
    }
     
    public function destroyemployee(Request $request){
        try{
            $this->authorize('delete',Employee::class);
        }catch(\Throwable $th){
            return ResponseController::error('You Are not allowed');
        }
        $employee = Employee::find($request->employee_id);
        if(!$employee){
            return ResponseController::error('Employee not found',404);
        }
        $employee->delete();
        return ResponseController::success('sucessful');
    }

    public function updateemployee(Request $request){
        try{
            $this->authorize('update',Employee::class);
        }catch(\Throwable $th){
            return ResponseController::error('You Are not allowed');
        }
        $validation = Validator::make($request->all(),[
            'name' =>'required|unique:employees,name|string|max:255',
            'phone' =>'required|unique:employees,phone|min:10',
            'password' =>'required|min:8',
            'role' =>'required',
        ]);
        if ($validation->fails()) {
            return ResponseController::error($validation->errors()->first(), 422);
        }
        $employee = Employee::find($request->employee_id);
        if (!$employee) {
            return ResponseController::error('Employee not found', 404);
        }
        $employee->update($request->all());
        return ResponseController::success('successful', 200);
    }

    public function allUsers(){
        $users = User::paginate(10);
        $final = [
            'last_page' =>$users->lastPage(),
            'users' => [],
        ];
        foreach ($users as $user){ 
            $final['users'][] = [
                'id'=>$user->id,
                'name'=>$user->name,
                'profile_photo'=>$user->profile_photo,
                'point' =>$user->point,
                'level'=>$user->level,
                'comments' =>$user->comments()->count(),
            ];
            $user['comments'] = $user->comments()->count();
        }
    
        return ResponseController::data($final);
    }

    public function orderbyPoint(Request $request){
        $final = [];
        $user = $request->user();
        $users = User::orderBy('point','Desc')->take(10)->get(['id','name','profile_photo','point','level']);
        $final['user'] = $user;
        $final['users'] = $users;
        return ResponseController::data($final);
    }

    public function singleUser($user_id){
        $user = User::find($user_id);
        if(!$user){
            return ResponseController::error('User not found',404);
        }
        return ResponseController::data($user);
     }
}
