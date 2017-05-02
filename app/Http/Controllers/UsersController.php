<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Users as userModel;
use Basemkhirat\Elasticsearch\Facades\ES;

class UsersController extends Controller
{
    /* 
    * @var $model: userModel
    */
    protected $model;

    /* 
    * @var $es: ES
    */
    protected $es;
    
    public function __construct(userModel $model, ES $es){
        $this->model = $model;
        $this->es = $es;
    }
    

    /*
    * @method: GET
    * @endpoint: /users    
    */
    public function index(Request $request){
        $result = $this->model->all();

        $test = ES::type("user")->get();
        return response()->json([
            'status' => 'success',
            'result' => $result->toArray(),
            'test' => $test->toArray(),
        ]);
    }


    /*
    * @method: POST
    * @endpoint: /users
    * first_name: String
    * last_name: String
    * age: Integer
    */
    public function store(Request $request){
        $user = clone $this->model;
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;        
        $user->age = $request->age;
        $user->save();

        $user_ES = ES::type("user")->id($user->getKey())->insert([
            "first_name" => $user->first_name,
            "last_name" => $user->last_name,
            "age" => $user->age,
        ]);

        return response()->json([
            'message' => 'successful saved',
            'user_mysql' => [
                "id" => $user->getKey(),
                "first_name" => $user->first_name,
                "last_name" => $user->last_name,
                "age" => $user->age,
            ],
            'user_es' => $user_ES,
        ]);
    }

    /*
    * @method: POST
    * @endpoint: /users/{id}
    * first_name: String
    * last_name: String
    * age: Integer
    * _method: "patch"
    */
    public function update(Request $request, $id){
        $user = clone $this->model;
        $user->find($id);
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->age = $request->age;
        $user->save();

        $user_es = ES::type("user")->id($user->getKey())->update([
            "first_name" => $user->first_name,
            "last_name" => $user->last_name,
            "age" => $user->age,
        ]);

        return response()->json([
            'status' => 'success',
            'data_mysql' => $user->toArray(),
            'data_es' => $user_es,
        ]);
        
    }


    /*
    * @method: GET
    * @endpoint: /users/{id}
    */
    public function show($id){
        $data_mysql = $this->model->find($id);

        $data_ES = ES::type("user")->_id($id)->first();

        return response()->json([
            'status' => 'success',
            'data_mysql' => $data_mysql->toArray(),
            'data_ES' => $data_ES,
        ]);
    }

    /*
    * @method: DELETE
    * @endpoint: /users/{id}
    */
    public function destroy($id){
        $user = clone $this->model;
        $user->destroy($id);
        
        ES::type("user")->id($id)->delete();
        
        return response()->json([
            'status' => 'successful deleted'
        ]);
    }

     /*
    * @method: POST
    * @endpoint: /users/search
    * keyword : String
    */
    public function search(Request $request){
      $result = ES::type("user")->where("first_name", "like", $request->keyword )->get();
      return response()->json([
          'search_result' => $result
      ]);
    }    
}
