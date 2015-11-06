<?php namespace App\Http\Controllers;

use App\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Votes;
use DB;
use Redis;
const COUNTER_KEY = 'MENDOAN:COUNT';

class HomeController extends Controller
{
    
    function __construct()
    {
         $this->redis    = new \Predis\Client();
    }

    public function index(Request $request)
    {
        if(! $count = $this->redis->get(COUNTER_KEY)) {
            $count = Votes::count();
            $this->redis->set(COUNTER_KEY, $count);
        }

        $total = str_pad($count, 6, 0, STR_PAD_LEFT);
        $exist = Votes::where('ip_address', $request->getClientIp())->count();
        
        return view('home', ['total' => $total, 'exist' => $exist]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), []);

        if ($validator->fails()) {
            $status = [
                'status'    => 'failed',
                'error'     => $validator->errors(),
            ];
        } else {
            if(! $count = $this->redis->get(COUNTER_KEY))
                $count = Votes::count();

            $data = Votes::firstOrCreate([
                'ip_address'  => $request->getClientIp()
            ]);
            $data->save();
            
            
            $count = (int)$count+1;
            $this->redis->set(COUNTER_KEY, $count);
            $total = str_pad($count, 6, 0, STR_PAD_LEFT);
            
            echo $total;
        }
    }
}
