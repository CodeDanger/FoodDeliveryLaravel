<?php

namespace App\Http\Controllers;
use App\User;
use Illuminate\Http\Request;

class InstallController extends Controller
{

    private function setEnv($key, $value)
    {
        $file = app()->environmentFilePath();


        if(strstr(file_get_contents($file),$key)){
        file_put_contents($file, str_replace(
            $key . '=' . env($key),
            $key . '=' . $value,
            file_get_contents($file)
        ));
        
        }else{
            file_put_contents(app()->environmentFilePath(),
                file_get_contents(app()->environmentFilePath())
                .$key . '=' . $value);
        }
    }
// for installing part
    private function updateConfig(){
        \Artisan::call("config:clear");
        \Artisan::call("cache:clear");
        \Artisan::call("route:clear");
    return true;
    }
    public function index(){
        return view('install');
    }
    
    public function setAppName(Request $request){
        
        if(!empty($request->name)){
            $pname=(string) $request->name;
            foreach(["APP_NAME"=>str_replace( " ","_",$pname),"DB_CONNECTION"=>"mysql"] as $key => $value)
            $this->setEnv($key,$value);
            $this->updateConfig();
            return response()->json(['success'=>  "true"]);
        }else{
            return response()->json(['success'=>  "false"]);
        }
    }

    
    public function setMysqlData(Request $request){
        $host = (string) $request->host;
        if(empty($host)) return response()->json(['success'=>  "false"]);
        $user = (string) $request->user;
        if(empty($user)) return response()->json(['success'=>  "false"]);
        $pwd =(string)  $request->pwd;
        $db = (string) $request->db;
        if(empty($db)) return response()->json(['success'=>  "false"]);
        foreach(['DB_HOST' => $host ,'DB_PORT' => '3306' , 'DB_DATABASE'=>$db , 'DB_USERNAME'=>$user, 'DB_PASSWORD'=>$pwd] as $key => $value)
        $this->setEnv($key,$value);
        if($this->updateConfig()){
            return response()->json(['success'=>  "true"]);
        }
        else{
            return response()->json(['success'=>  "false"]);
        }
    }
    
    public function createTables(Request $request){
        \Artisan::call("migrate:rollback");    
        \Artisan::call("migrate");    
            if($this->updateConfig()){
                return response()->json(['success'=>  "true"]);
            }
            else{
                return response()->json(['success'=>  "false"]);
            }
    }
    public function insertAdmin(Request $request){
        $username = (string) $request->username;
        $email = (string) $request->email;
        $apwd = (string) $request->apwd;

        if(strlen($username)==0) return response()->json(['success'=>  "false"]);
        if(strlen($email)==0 || ! strstr($email,"@")) return response()->json(['success'=>  "false"]);
        if(strlen($apwd)==0) return response()->json(['success'=>  "false"]);
        User::create(["username"=>$username,"name"=>config("app.name","Food_Delivery"),"password"=>$apwd,"email"=>$email,"role"=>"admin"]);
        foreach(["installed"=>true] as $key => $value)
        $this->setEnv($key,$value);
        if($this->updateConfig()){
            return response()->json(['success'=>  "true"]);
        }
        else{
            return response()->json(['success'=>  "false"]);
        }        
    }
// end installing part
}
