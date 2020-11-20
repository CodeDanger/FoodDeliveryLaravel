<?php

namespace App\Http\Controllers;

use App\Address;
use App\Item;
use App\Orders;
use App\User;
use Illuminate\Http\Request;
use  Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Facades\Storage;
use Laravel\Ui\Presets\React;

class PagesController extends Controller
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
            file_put_contents($file,
                file_get_contents($file)
                .$key . '=' . $value);
        }
    }

    public function install(){
        if(config("app.installed")){
            return redirect(route("welcome"));
        }
        return view("install");
    }

    public function logout () {
        auth()->logout();
        return redirect('/');
    }
    
    private function updateConfig(){
        \Artisan::call("config:clear");
        \Artisan::call("cache:clear");
        \Artisan::call("route:clear");
    return true;
    }

    public function updatesite(Request $request)
    {

        if (!auth()->user()->isAdmin()) 
        return view("welcome");
        $validatedData = $request->validate([
            'appname' => ['required', 'string', 'max:255'],
            'host' => ['required', 'string', 'max:255'],
            'user' => ['required', 'string','max:255'],
            'db' => ['required', 'string'],
            ]);
            $myarr = array(
                "APP_NAME" => str_replace ( " ", "_" , $request->appname ),
                "DB_HOST" => $request->host,
                "DB_USER" => $request->user,
                "DB_PASSWORD" => $request->password,
                "DB_DATABASE" => $request->db
            );
            foreach($myarr as $key=>$value)
            $this->setEnv($key,$value);           
            $this->updateConfig(); 
            $myarr = null;
            return redirect()->back()->with('success', 'Site Settings Has Been Updated');
        }




    public function updateuser(Request $request)
    {

        if (!auth()->user()->isAdmin()) 
        return response()->json(['success'=>  "false"]);
         $request->validate([
            'id' => ['required'],
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255',"min:4"],
            'email' => ['required', 'string', 'email', 'max:255'],
            ]);
            
            $user = auth()->user()->find((int)$request->id);
            $user2 = User::where('email',$request->email) -> first();

            if($user2!=null && $user2!=$user) return back()->withInput()->withErrors(['email'=>'This Email Already Used']);
            if($request->accst=="Null") $request->accst=null;

            if(empty($request->password)){
            $user->update(array(
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'role' => $request->accrole,
            ));
            $user->forceFill(['email_verified_at' => $request->accst]);
            $user->save();
            }else{
             $request->validate([
                    'password'=>['required', 'string', 'min:8']
                ]);
                $user->update(array(
                    'name' => $request->name,
                    'username' => $request->username,
                    'email' => $request->email,
                    'role' => $request->accrole,
                    'password' => $request->password

                ));
                $user->forceFill(['email_verified_at' => $request->accst]);
                $user->save();
            }

            return redirect()->back()->with('success', 'Account Has Been Updated');

    }

    public function update_item(Request $request)
    {

        if (!auth()->user()->isAdmin()) 
            return view("welcome");
       
         $request->validate( [
            'id' => ['required', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255',"min:4"],
            'price' => ['required'],
            'image' => ['image','mimes:jpeg,png,jpg,gif,svg','max:2048'],

        ]);            
            $item=Item::find($request->id);
            $item->update([
                'name' => $request->name,
                'description' => $request->description,
                'price' => (float)$request->price,
            ]);
            if($request->hasFile("image")){
                Storage::delete(asset('/images/'.$item->image));
                $imageName = time().'.'.request()->image->getClientOriginalExtension();
                request()->image->move(public_path('images'), $imageName);        
                $item->forceFill(['image' => $imageName]);
                $item->save();
            }
            return redirect()->back()->with('success', 'Item Has Been Updated');

    }

    public function adduser(Request $request)
    {

        if (!auth()->user()->isAdmin()) 
        return view("welcome");
       
        $request->validate( [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255',"min:4"],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
        ]);
        if($request->accst=="Null") $request->accst=null;
        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'role' => $request->accrole,
            'email' => $request->email,
            'password' => $request->password,
        ]);
        $user->forceFill(['email_verified_at' => $request->accst]);
        $user->save();
        return redirect()->back()->with('success', 'Account Has Been Created');

// i am here

    }

    public function add_item(Request $request)
    {
        if (!auth()->user()->isAdmin()) 
        return view("welcome");
       
         $request->validate( [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255',"min:4"],
            'price' => ['required'],
            'image' => ['required','image','mimes:jpeg,png,jpg,gif,svg','max:2048'],

        ]);
        
        $imageName = time().'.'.request()->image->getClientOriginalExtension();
        request()->image->move(public_path('images'), $imageName);
        Item::create(["name"=>$request->name,
        "description"=>$request->description,
        "price"=>(float)$request->price,
        "image"=>$imageName
        ]);
        return redirect()->back()->with('success', 'Item Has Been Created');


    }
    
    public function add_address(Request $request)
    {
      
       
         $request->validate( [
            'country' => ['required', 'string', 'max:255',"min:3"],
            'city' => ['required', 'string', 'max:255',"min:3"],
            'state' => ['required', 'string', 'max:255',"min:3"],
            'address' => ['required', 'string', 'max:255',"min:3"],

        ]);
        $allitems = $request->toArray();
        $allitems['user_id'] =auth()->user()->id;
        $item_id= $allitems['item_id'];
        unset($allitems['item_id']);
        $address_id = Address::create($allitems)->id;
        
        Orders::create(['user_id'=>auth()->user()->id,'item_id'=>$item_id,'address_id'=>(int)$address_id,'paid'=>false,'state'=>'notdone']);
        return response()->json(['success'=>  "true"]);


    }



    public function add_order(Request $request)
    {
      
        $allitems = $request->toArray();
        $allitems['user_id'] =auth()->user()->id;
        $item_id= $allitems['item_id'];

        Orders::create(['user_id'=>auth()->user()->id,'item_id'=>$item_id,'address_id'=>(int)$allitems['address_id'],'paid'=>false,'state'=>'notdone']);
        return response()->json(['success'=>  "true"]);


    }

    public function delete_order(Request $request)
    {
        if (!auth()->user()->isAdmin()) 
        return response()->json(['success'=>  "false"]);
        $order = Orders::find((int)$request->id);
        $order->delete();
        return response()->json(['success'=>  "true"]);


    }

    public function delete_item(Request $request)
    {
        if (!auth()->user()->isAdmin()) 
        return response()->json(['success'=>  "false"]);
        $item = Item::find((int)$request->id);
        $item->delete();
        return response()->json(['success'=>  "true"]);


    }

    public function deleteuser(Request $request)
    {
        if (!auth()->user()->isAdmin()) 
        return response()->json(['success'=>  "false"]);
        $user = auth()->user()->find((int)$request->id);
        
        $user->delete();
        return response()->json(['success'=>  "true"]);


    }
    public function showupdateuser($id)
    {
        if (!auth()->user()->isAdmin()) 
        return view("welcome");
        $data = auth()->user()->find((int)$id);
        return view("admin.update_user",compact("id"))->with(['data'=> $data,"items"=>Item::all(),"orders"=>Orders::all()]);

    }

    public function showupdateitem($id)
    {
        if (!auth()->user()->isAdmin()) 
        return view("welcome");
        $data = Item::find((int)$id);
        return view("admin.update_item",compact("id"))->with(['data'=> $data,"items"=>Item::all(),"orders"=>Orders::all()]);

    }
    
    public function redirecter($page,$data)
    {
        if (!auth()->user()->isAdmin()) 
        return view("welcome");
        return redirect(route($page,$data));

    }
}

