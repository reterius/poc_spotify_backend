<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Input;
#use App\Helpers\Helper;
use Helper;
use Validator;
use Hash;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

use \Symfony\Component\Console\Output\ConsoleOutput ;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $out = new ConsoleOutput();
        $response_object = [
            "error_code" => null,
            "error_message" => null,
            "success_message" => null,
            "data" => new \stdClass(),
            "status" => 200
        ] ;

        $validator =  Validator::make($request->all(),[
        'fullname' => 'required|string|max:30',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:6'
        ]);
    
        if($validator->fails()){
            $response_object["error_code"] = 'validation_error' ;
            $response_object["error_message"] = $validator->errors() ;
            $response_object['status'] = 422 ;
            return response()->json($response_object, 422);
        }

        $randomString = Helper::generateRandKey() ;

        $user = new User();
        $user->fullname = $request->fullname;
        $user->email = $request->email;
        #$user->password = Hash::make($request->password) ;
        $user->password = bcrypt($request->password) ;
        
        $user->save();

        $response_object["success_message"] = "Account has been created successfully." ;
        return response()->json($response_object, 200);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $current_user = Auth::user(); 

        $response_object = [
            "error_code" => null,
            "error_message" => null,
            "success_message" => null,
            "data" => new \stdClass()
        ] ;

        $validator = Validator::make(['id' => $id], [
            'id' => 'required|integer'
        ]);

        if($validator->fails()){
            $response_object["error_code"] = 'validation_error' ;
            $response_object["error_message"] = $validator->errors() ;
            $response_object['status'] = 422 ;
            return response()->json($response_object, 422);
        }

        # kayıt bulunamadı
        $user = User::find($id);
        if($user == null){
            $response_object["error_code"] = 'not_found_error' ;
            $response_object["error_message"] = 'Not Found Error' ;
            $response_object['status'] = 404 ;
            return response()->json($response_object, 404);
        }

        # Silinen kayıt kişiye ait değilse hata dön
        if($current_user->id != $user->id){
            $response_object["error_code"] = 'permission_denied_error' ;
            $response_object["error_message"] = 'Permission Denied Error' ;
            $response_object['status'] = 401 ;
            return response()->json($response_object, 401);
        }

        try{
            $user->delete();
        }
        catch (ErrorException $e){
            $response_object["error_code"] = 'internal_server_error' ;
            $response_object["error_message"] = 'Internal Server Error' ;
            $response_object['status'] = 500 ;
            return response()->json($response_object, 500);
        }

        $response_object['success_message'] = "User deleted successfully";
        return response()->json($response_object, 200);

    }

    public function login(Request $request)
    {

        
        $out = new ConsoleOutput();

        $response_object = [
            "error_code" => null,
            "error_message" => null,
            "success_message" => null,
            "data" => (object) array(
                'access_token'=>null,
                'refresh_token'=>"asasaaa",
                '_id'=> null
            ),
            "status" => 201
        ] ;

        $validator =  Validator::make($request->all(),[
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);
        
        if($validator->fails()){
            $response_object["error_code"] = 'validation_error' ;
            $response_object["error_message"] = $validator->errors() ;
            $response_object['status'] = 422 ;
            return response()->json($response_object, 422);
        }

        $credentials = request(['email', 'password']);
        if(Auth::attempt($credentials)){
            
            $u = Auth::user();
            $db_user = User::find($u->id) ;
            
            # Eğer user token'e sahipse mevcut tokeni dön, yoksa oluştur ve db ye yaz
            if($db_user->access_token == null){
                
                $access_token = $u->createToken('MyApp')->accessToken ;
                $db_user->access_token = $access_token ;
                $db_user->save() ;
                
            }else{
                $access_token = $db_user->access_token ;
            }
            
            $response_object['data']->access_token = $access_token;
            $response_object['data']->_id = $u->id ;
            #$response_object['data']->experies_at = Carbon::parse(Carbon::now()->addWeeks(1))->toDateTimeString();

            return  response()->json($response_object, 201);
        }else{
            
            $response_object["error_code"] = 'unauthorised_error' ;
            $response_object["error_message"] = 'Unauthorised Error' ;
            $response_object['status'] = 404 ;
            return response()->json($response_object, 404);
        }

        return response()->json($response_object, 201);
    }

    public function logout(Request $request) {
        $request->user()->token()->revoke();
        $u = Auth::user(); 

        # db de access token field'ını sıfırla
        $db_user = User::find($u->id) ;
        $db_user->access_token = null ;
        $db_user->save() ;

        $response_object = [
            "error_code" => null,
            "error_message" => null,
            "success_message" => "Logout successfully.",
            "data" => new \stdClass(),
            "status" => 200
        ] ;

        return response()->json($response_object, 200);
    }

    public function unauthorized() { 
        $response_object = [
            "error_code" => null,
            "error_message" => null,
            "success_message" => null,
            "data" => new \stdClass(),
            "status" => 401 
        ] ;
        $response_object["error_code"] = 'unauthorised_error' ;
        $response_object["error_message"] = 'Unauthorised Error' ;
        $response_object['status'] = 401 ;
        return response()->json($response_object, 401);
    } 

    


}
