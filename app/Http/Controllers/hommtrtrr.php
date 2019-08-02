<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\treasury;
use App\payment;
use App\agent;
use App\district;
use App\member;
use DB;
use Charts;
use App\myviews;
use App\salaries;
use Storage;



class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    
    public function index()
    {   //a helper function to count members from the database that have been enrolled 

        
        $results = DB::table('members')->count();

        // counts All agents available in the all country

        $agenthead= DB::table('agents')->where('role','Agent head')->count(); 
        // counts All agents heads available in the all country

        $agents= DB::table('agents')->where('role','Agent')->count();
        //fundings available per month 
        $amount=DB::select(DB::raw("SELECT amount from salaries"));
        //district with no agents .
        $district =DB::select(DB::raw('SELECT count(id) as nums from districts where id NOT IN (SELECT district_Id from agents)'));
         
        //members qualified to agents

        $member =DB::select(DB::raw('SELECT  DISTINCT recommender
        FROM members WHERE recommender IN
          (SELECT recommender FROM members GROUP BY recommender HAVING COUNT(*) >=40)'));
          $co=count($member);
          //district name with the higest enroll
          $districtname=DB::select(DB::raw('SELECT id,name, count(*) as total from districts,members where districts.id=members.memberDistrict  GROUP BY id ORDER BY 2 DESC limit 1'));
        
        //send data to the views
        return view('welcome',['results'=>$results,'agents'=>$agents,'district'=>$district,'co'=>$co,'districtname'=>$districtname,'agenthead'=>$agenthead,'amount'=>$amount]);
    
        
    }
    // show the hierca'districts' display

    public function hierca()
    {    
        // getting all districts from the database
        $district_list= district::orderby('name','ASC')->get(['id','name']);
        
        return view('high',compact('district_list'));

        

      }
      public function fetchs(Request $request)
    {   
        // determines the agents belonging to  aparticular district
        $data = district::find($request->id)->AgentAvailable()->orderBy('role','DESC')->get(['agentid','LastName','firstName','role']);
    
       return response()->json($data);
    }
      
  

    // show the payment details
    public function payment()
    
    { $date=date('m-y');
        global $amountagent; 
        global $totalamount;
        // calculating the amount of money recieved by agents ,admin agent headers
        $amount=DB::select(DB::raw("SELECT amount from salaries"));
        // return $amount;
        $agents= DB::table('agents')->where('role','Agent')->count();
        $agenthead= DB::table('agents')->where('role','Agent head')->count();
        
        $district =DB::select(DB::raw('SELECT id, count(*) as total from districts,members where districts.id=members.memberDistrict  GROUP BY id ORDER BY 2 DESC'));
       
        // $head=DB::select(DB::raw('SELECT id,count(agentid) as agentid from districts,agents where districts.id=agents.district_Id and agents.role="Agent head" GROUP BY id'));
        // return $remainingagent;
           
        foreach($district as $dist)
          {  
              if($dist->id=null){
                $noagentsinhigh=0;
              }else{
            // agents with district of the highest enrollment after getting the id 
           $noagentsinhigh=DB::table('agents')->where('district_Id',$dist->id)->count();
          
              }

            // agents within other country
           $remainingagent=$agents-$noagentsinhigh;
          
           
          foreach($amount as $cash)
          {  
            //total amount
            $totalamount;
           $totalamount=$cash->amount;
          } 
          
         // agent head with the other enrollment
          $remaininghead=$agenthead-1;
       
           if ($totalamount>2000000){
               
         // agent head with the other enrollment
         // agent head with the other enrollment
              $amountagent=($totalamount-2000000)/($remainingagent+0.5+(1.75*$remaininghead)+(2*$noagentsinhigh)+(7/2));
              
              
                     // getting all agents with normal enrollment 
                    $gentlowenr= DB::select(DB::raw('SELECT agentid ,role from agents where agentid  NOT in (select  agentid from agents where district_Id=(  SELECT district_Id from agents GROUP BY district_Id order by count(1) desc limit 1))'));
            
                    
                    // agents with the highest enrollment
                     $genthigenr= DB::select(DB::raw('SELECT agentid ,role from agents where agentid   in (select  agentid from agents where district_Id=(  SELECT district_Id from agents GROUP BY district_Id order by count(1) desc limit 1))'));
                     $dateofpayment=payment::all()->pluck('paymentDate')->last();
                      $dateofreciv=treasury::all()->pluck('date')->last();
                      $timepayment=date('m-y',strtotime($dateofreciv));
                      $lastpayment=date('m-y',strtotime($dateofpayment));
    
                    //  return date('d',strtotime($dateofpayment));
                    if($timepayment==$lastpayment || $timepayment<$lastpayment){
            
            
                     return view('payment',['amountagent'=>$amountagent,'remainingagent'=>$remainingagent,'noagentsinhigh'=>$noagentsinhigh,'remaininghead'=>$remaininghead]);
                     }else{
     
                     foreach($gentlowenr as $lown){
                           // adding payment details for each agent and agent not in highest enrollment
                            DB::statement('INSERT INTO  payments set payment_Id=(SELECT agentid from agents where agentid='.$lown->agentid.'),paymentDate=CURRENT_TIMESTAMP,amountpaid= case when (select role from agents where agentid='.$lown->agentid.')="Agent" then '. $amountagent.' when (select role from agents WHERE agentid='.$lown->agentid.')="Agent head"  then '.($amountagent * (7/4)).' end');
                        

                            
                        }
                     
                     foreach($genthigenr as $higenr){
                         // agents belonging to districts with the highest enrollment  
                         DB::statement('INSERT INTO  payments set payment_Id=(SELECT agentid from agents where agentid='.$higenr->agentid.'),paymentDate=CURRENT_TIMESTAMP,amountpaid= case when (select role from agents where agentid='.$higenr->agentid.')="Agent" then '. ($amountagent * 2).' when (select role from agents WHERE agentid='.$higenr->agentid.')="Agent head"  then '.($amountagent *(7/4) *2).' end');
                         
                        $content="Administrator - ".number_format($amountagent/2,0)."
                          Agent head- ".number_format($amountagent*(7/4),0)."
                          Agent - ".number_format($amountagent,0)."
                          Agent with highest enrollment - ".number_format(2*$amountagent,0)."
                          Agent head with highest enrollment- " .number_format((7/2)*$amountagent,0)."
                          payment Date -".date('d-m-y');
                    //    one approach to write to file or i will consider the know php
                      Storage::put('payment_files/payment.txt', $content);
                    // $myfile=fopen("payment_files/payment.txt",'w+') or die("unble to open") ;
                    // fwrite($myfile,$content);
                    // fclose($myfile);
                    // return $content;
                        
            
            
                       
                    
                    }
                
        }}
          else{
              $amountagent=0;
              Storage::put('payment_files/payment.txt', 'No payments were made this month,sorry check next month ');
          }
       

        
//         }
//     
    return view('payment',['amountagent'=>$amountagent,'remainingagent'=>$remainingagent,'noagentsinhigh'=>$noagentsinhigh,'remaininghead'=>$remaininghead]);
    
// }
    }
}

    
        // declare payment to the database 
        public function newpayment(Request $request){
            $this->validate($request,[
                'source'=>'required',
                'amount'=>'required',
                'date'=>'required'
                ]);
                $time=$request->all();
                $time['date']=date('y-m-d',strtotime($request->date));
                $time['source']=strtoupper($request->source);
                $time['district']=strtoupper($request->district);
                // return $time;
                if(treasury::create($time)){
                    return redirect()->back()->withSuccess('New payment declaration  has been added successfully');
                }
             
        }



      





    // show statistics
    public function stat()
        
    { 
    

//  two alternatives to present the percentage choice any;
           //member enrollment
        $data = myviews::select(
            \ DB::raw("DATE_FORMAT(created_at,'%M %Y') as months")
            ,\DB::raw("total")
            // \DB::raw("COALESCE((LEAD(total) OVER (ORDER BY months DESC)-total)/total, 0) Percent_Change")
        )
        ->get();
        

    // $chart = Charts::database($data, 'bar', 'highcharts')
    //     ->title("PERCENTAGE CHANGE IN ENROLLMENT FIGURES")
    //     ->elementLabel("Percentage change")
    //     ->dimensions(1000, 500)
    //     ->responsive(true)
    //     ->groupBy('months')
    //     ->values($data->pluck('Percent_Change'));
    // $data=DB::table('myviews')->get();
    $value=array();
    $updatedvalue=array();
    $month=array();
    foreach($data as $i)
    {
    array_push($value,$i->total);
    array_push($month,$i->months);
    }
    for($i=0;$i<count($value)-1;$i++){
        array_push($updatedvalue,(($value[$i+1]-$value[$i])/$value[$i]));
    }
    // return $aksam;
    

        $chart = Charts::create('bar', 'highcharts')

        ->title('PERCENTAGE CHANGE IN ENROLLMENT FIGURES')

        ->labels($month)
        ->elementLabel("percentage change")
        ->values($updatedvalue)
        ->dimensions(1000,500)

        ->responsive(true);
       



        $money = treasury::where(DB::raw("(DATE_FORMAT(created_at,'%Y'))"),date('Y'))
    
        ->get();
 
        $chart2 = Charts::database($money, 'bar', 'highcharts')
        
        ->title("Monthly funds")
        
        ->elementLabel("Total wellwishers")
        
        ->dimensions(1000, 500)
        
        ->responsive(false)
        
        ->groupByMonth(date('Y'), true);
        return view('stat',["chart"=>$chart,"chart2"=>$chart2]);    
    } 


    public function report(){

      $contrtable=DB::select('select * from treasuries  order by date desc');
      return view('report',compact('contrtable'));

    }
    // show records
    public function records(Request $requests){
        $agentstable=DB::select('select * from districts,agents where id=district_Id and role order by name,role desc');
        // $headtable=DB::select('select * from districts,agents where id=district_Id  and role="Agent head" order by name asc');
         //$districttable=DB::table('districts')->orderBy('name','desc')->get();
        
        // return $membertable;
        // return view('record',compact('membertable','agentstable','headtable','districttable'));
        return view('record',compact('agentstable'));
    }
      //showing members enrolled in a selected particular district
      public function members(Request $requests){
        $membertable=DB::select("select * from members where memberDistrict='$requests->district'");
        $districttable=DB::table('districts')->orderBy('name','desc')->get();
        return view ('member', compact('membertable','districttable'));
     }
    // show registration form
    public function upgrades(){
        

        $memberqualify=DB::select(DB::raw('SELECT * from members where status=0 AND fname IN (SELECT DISTINCT recommender
        FROM members WHERE recommender IN
          (SELECT recommender FROM members GROUP BY recommender HAVING COUNT(*) >=40))'));
          
          $districtAvailable=DB::select(DB::raw('SELECT * from districts where id NOT IN (SELECT district_Id from agents)
          OR id=( SELECT  district_Id  FROM agents GROUP by district_Id order by COUNT(1) ASC LIMIT 1) ORDER BY RAND() LIMIT 5'));
          return view('upgrade',compact('memberqualify','districtAvailable'));
    }


   // randomly distribute  members who qualify to be agents
   public function becomeAgent(){

       
    $memberqualified=DB::select(DB::raw('SELECT * from members where status=0 AND fname IN (SELECT DISTINCT recommender
    FROM members WHERE recommender IN
    (SELECT recommender FROM members GROUP BY recommender HAVING COUNT(*) >=40))'));
     $Agent;
        $Agent=new agent;
      foreach($memberqualified as $member){
       if($member->fname!==NULL){
      $Agent->firstName=$member->fname;
      $Agent->lastName=$member->fname;
      $Agent->userName=substr($member->fname,0,5);
      $Agent->signature=strtoupper(chr(rand(65,90)));
    DB::statement('UPDATE members Set status=1 where member_Id='.$member->member_Id);
      $district_id=DB::select('SELECT id from districts where id NOT IN (SELECT district_Id from agents)
      OR id=( SELECT  district_Id  FROM agents GROUP by district_Id order by COUNT(1) ASC LIMIT 1) ORDER BY RAND() LIMIT 5');
   
     foreach($district_id as $dist){
                 global $distt;
               $distt=$dist->id;
            
       }
        $Agent->district_Id=$distt ;
  
        $distNoAgenthead=DB::select(DB::raw('SELECT id as nums from districts where id NOT IN (SELECT district_Id from agents)'));
      
         foreach ($distNoAgenthead as $head){
                 global $headno;
                 $headno=$head->nums;
        }
     if($headno==$Agent->district_Id){
        $Agent->role="Agent head";
       }  $district_id=DB::select('SELECT id from districts where id NOT IN (SELECT district_Id from agents)
       OR id=( SELECT  district_Id  FROM agents GROUP by district_Id order by COUNT(1) ASC LIMIT 1) ORDER BY RAND() LIMIT 5');
      
      foreach($district_id as $dist){
                    global $distt;
                  $distt=$dist->id;
               
       }
       $Agent->district_Id=$distt ;
     
       $distNoAgenthead=DB::select(DB::raw('SELECT id as nums from districts where id NOT IN (SELECT district_Id from agents)'));
         
       foreach ($distNoAgenthead as $head){
                    global $headno;
                    $headno=$head->nums;
                    // return $headno;
       }
       if($headno==$Agent->district_Id && $headno!=NULL){
           $Agent->role="Agent head";
           $Agent->save();
           return redirect()->back()->withSuccess('New member has been upgraded  successfully');;
       }
       else {
           $Agent->role="Agent";
           $Agent->save();
           return redirect()->back()->withSuccess('New member has been upgraded  successfully');;
       }
       
    }

    
      
    }
    return redirect()->back()->withSuccess('No member has the required criteria');         
    }



    public function formdata(Request $request){
        // defininf the rules order_by('upload_time', 'desc')->first();that must be followed when submitting data 
        $this->validate($request,[
            'firstName'=>'required',
            'lastName'=>'required',
            // 'name'=>'required',
            'userName'=>'required',
        ]);
     

    
        $Agent=new agent;
        $Agent->firstName=strtoupper($request->firstName);
        $Agent->lastName=strtoupper($request->lastName);
        $Agent->userName=strtoupper($request->userName);
        $Agent->signature=strtoupper(chr(rand(65,90)));
        // $Agent->signature=strtoupper(rand(65,90));
        // $district_id=DB::table('districts')->select('id')->where( 'name',$name)->first();

        // Select a random number from the database to return a value for the district
        $district_id=DB::select('SELECT id from districts where id NOT IN (SELECT district_Id from agents)
        OR id=( SELECT  district_Id  FROM agents GROUP by district_Id order by COUNT(1) ASC LIMIT 1) ORDER BY RAND() LIMIT 5');
       
       foreach($district_id as $dist){
                     global $distt;
                   $distt=$dist->id;
                
        }
        $Agent->district_Id=$distt ;
        $Agent;
        $distNoAgenthead=DB::select(DB::raw('SELECT id as nums from districts where id NOT IN (SELECT district_Id from agents)'));
         global $headno;
        foreach ($distNoAgenthead as $head){
                    
                     $headno=$head->nums;
        }
       if($headno==$Agent->district_Id){
            $Agent->role="Agent head";
        }
        else {
            $Agent->role="Agent";
        }
        $Agent->save();
        if($Agent->save()){
            
            return redirect()->back()->withSuccess('New member has been added successfully');
        }
              //   agent::create([$request->firstName,$request->lastName,$request->signature,$request->userName]);
        
    }
    public function formdata1(Request $request){
      
        $this->validate($request,[
            'name'=>'required',]);
       
        $name=$request->name;
        
       //checking if the record exits in the database
        $districtName=district::where('name','=',$name)->count();
      if($districtName>0){
               $errormessage="The district already exists!!";
            // return redirect('/newuser',compact('errormessage'));
            return redirect()->back()->withSuccess($errormessage);
        }
        else{
            $names=$request->all();
            $names['name']=strtoupper($request->name);
            district::create($names);
            return redirect()->back()->withSuccess('New district has been added successfully');

        }


    }
    // view fo the index to make a contoller
    public function form(){

        return view('newuser');
    }

    public function districtinfo(){
        return view('newdist');
    }

// charts controller
    public function makecharts($type){

     

    return view('stat', compact('chart'));
    
    }
    //  change the id of the member to required id by the administrator
    public function changeid(){
        $distname=district::all();
        $membern=DB::select('select * from districts,members where memberDistrict=id');
     
        
           
           foreach($membern as $mem)
           { 
            $ini=substr($mem->name,0,4);
           $new= strtoupper($ini).$mem->member_Id;
        
           DB::statement("update members SET districtNO='$new' where member_Id='$mem->member_Id'");
           echo $new."<br>";   
           }

         
    }

        




}

?>