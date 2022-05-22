<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function create (Request $request){
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
        return ResponseController::success();
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
            'token'=>$token
        ]);
    }

    public function createemployee(Request $request){
        try{
            $this->authorize('create',Employee::class);
        }catch(\Throwable $th){
            return ResponseController::error('You Are not allowed');
        }
        $phone = $request->phone;
        $employee =  Employee::where('phone',$phone)->first();
        if($employee){
            return ResponseController::error('This employee already exits');
        }
        $employee = Employee::create([
            'name'=>$request->name,
            'phone'=>$phone,
            'password'=>Hash::make($request->password),
            'role'=>$request->role
        ]);
        return ResponseController::success('successful');
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
        return ResponseController::success('sucessful');
    }

    public function updateemployee(Request $request){
        try{
            $this->authorize('update',Employee::class);
        }catch(\Throwable $th){
            return ResponseController::error('You Are not allowed');
        }
        $employee = Employee::find($request->employee_id);
        if (!$employee) {
            return ResponseController::error('Employee not found', 404);
        }
        $employee->update($request->all());
        return ResponseController::success('successful', 200);
    }
}
