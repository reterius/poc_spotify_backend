<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Facades\Auth;
use \Symfony\Component\Console\Output\ConsoleOutput ;
use Illuminate\Support\Facades\Input;
use App\User;
use App\Helpers\LarafyCustom;

use App\Schemas\SpotifyItemSchema;

class SpotifyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $current_user = Auth::user(); 
        $response_object = [
            "error_code" => null,
            "error_message" => null,
            "success_message" => null,
            "data" => (object) array(
                'items'=>[],
                'page'=>1,
                'pages'=>0,
                'per_page'=>10,
                'total'=>0
            ),
            "status" => 200
        ] ;

        $page = 1 ;
        $per_page = 10 ;

        if (Input::has('page')) {
            $page =  Input::get('page') ;
        }

        $validator = Validator::make(['page' => $page], [
            'page' => 'integer|not_in:0'
        ]);

        if($validator->fails()){
            $response_object["error_code"] = 'page_validation_error' ;
            $response_object["error_message"] = "page value must be integer and grather than 0 " ;
            $response_object['status'] = 422 ;
            return response()->json($response_object, 422);
        }

        $response_object['data']->page = $page ;

        if (Input::has('per_page')) {
            $per_page = (int) Input::get('per_page') ;
        }
        $validator = Validator::make(['per_page' => $per_page], [
            'per_page' => 'integer|in:10,20,50'
        ]);

        if($validator->fails()){
            $response_object["error_code"] = 'per_page_validation_error' ;
            $response_object["error_message"] = "per_page value must be integer and one of 10,20,50 values" ;
            $response_object['status'] = 422 ;
            return response()->json($response_object, 422);
        }

        $response_object['data']->per_page = $per_page ;


        if(($page * $per_page) > 2000){
            $response_object["error_code"] = 'spotify_api_limitation_error' ;
            $response_object["error_message"] = "Item's be searched order  can't be bigger than 2000" ;
            $response_object['status'] = 429 ;
            return response()->json($response_object, 429);
        }

        $keyword = null ;
        if (Input::has('keyword')) {
            $keyword = Input::get('keyword') ;
        }

        $validator = Validator::make(['keyword' => $keyword], [
            'keyword' => 'required|string|min:3|max:60'
        ]);

        if($validator->fails()){
            $response_object["error_code"] = 'keyword_validation_error' ;
            $response_object["error_message"] = "keyword value must be string and keyword's value length must be between of 3 and 60";
            $response_object['status'] = 422 ;
            return response()->json($response_object, 422);
        }

        $type = null ;
        if (Input::has('type')) {
            $type = Input::get('type') ;
        }

        $type_array = explode(",", $type);
        $valid_types = ["artist","playlist","track", "album"] ; 

        foreach($type_array as $t) {
            if (!in_array($t, $valid_types)){
                $response_object["error_code"] = 'type_invalid_validation_error' ;
                $response_object["error_message"] = $t . "not valid type. Type must be in 'artist', 'playlist','track', 'album' " ;
                $response_object['status'] = 422 ;
                return response()->json($response_object, 422);
            }
        }

        $validator = Validator::make(['type' => $type], [
            'type' => 'required|string|min:3|max:90'
        ]);
        if($validator->fails()){
            $response_object["error_code"] = 'type_validation_error' ;
            $response_object["error_message"] = "type value must be string and type's value length must be between of 3 and 90" ;
            $response_object['status'] = 422 ;
            return response()->json($response_object, 422);
        }

        #error_log(json_encode($data));

        $offset = ($page - 1) * $per_page ;

        try {
            $api = new LarafyCustom();
        }    
        catch(ErrorException $e) {

            // invalid ID & Secret provided
            $response_object["error_code"] = 'spotify_authorization_error' ;
            $response_object["error_message"] = "Spotify authorization error" ;
            $response_object['status'] = 500 ;
            return response()->json($response_object, 500);
        }
        
        $api_resp = $api->searchCustom($keyword, $per_page, $offset, $type);
        $k = 0 ;
        $totals = [] ;
        foreach($api_resp as $key=>$value) {
            array_push($totals, $value->total);
            foreach($value->items as $item) {
                
                try{
                    $row = new SpotifyItemSchema() ;
                    $row->id = $item->id ;
                    $row->name = $item->name ;
                    $row->uri = $item->uri ;
                    $row->type = $item->type ;
                    $response_object['data']->items[$k] = $row ;
                    $k++ ;
                }
                catch (ErrorException $e){
                    continue ;
                }
            }
        }

        $max_total = 0 ;

        if(count($totals) > 0 ) {
            $max_total = max($totals) ;
            $pages = floor($max_total / $per_page) ;
            $response_object['data']->pages = (int) $pages ;
        }

        $response_object['data']->total = (int) $max_total ;
        $response_object['success_message'] = "Spotify search results gets successfully";
        return response()->json($response_object, 200);
    }

}
