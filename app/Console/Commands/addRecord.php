<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Storage;
use App\district;
use App\member;
use App\agent;

class addRecord extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:addRecord';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'inserts data in the database';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()

    {   
        // function getdistrict($distname)
        // {
        //     //caling the method from my controller 
            
        //     $distname= str_replace(' ', '', $distname);
        //     $id=district::where('name',$distname)->first();
        //     $memid=member::all()->pluck('member_Id')->last();
        //     $memid=$memid+1;
        //     $ini=substr($id->name,0,4);
        //     $new= strtoupper($ini).$memid;
        
        // return $new;

        // } 
        function districtid($ids)
        {
            $distname= str_replace(' ', '', $ids);
            $did=district::where('name',$distname)->first();
            return $did->id;
        }
        function agentids($username,$sign){
            $agentsname= str_replace(' ', '',$username);
            $agentsign= str_replace(' ', '',$sign);
            $agent=agent::where(['userName'=>$agentsname,'signature'=>$agentsign])->first();
            // echo
            return  $agent;

        } 
        function agentsid($username,$sign){
            $agentsname= str_replace(' ', '',$username);
            $agentsign= str_replace(' ', '',$sign);
            $agent=agent::where(['userName'=>$agentsname,'signature'=>$agentsign])->first();
            // echo
            return  $agent->agentid;

        } 
       
        
        function deleterecord($arrays,$district)
        {
            foreach ($arrays as $url) 
            {
                $tempcontent ="  ";
                 $contents = $tempcontent;
                 Storage::put($district, $contents);
            }   
        }
        function getrecommendid($recom){
            $recommender= str_replace(' ', '',$recom);
            $recomid=member::where('recommender',$recommender)->first();
             if($recomid){
                 return 1;
             }
        }
          
        
        $files = Storage::files('public/district_files');
        
        foreach($files as $district)
           {
               $content = Storage::get($district);
               $contents = explode("\n",$content);
               $fail=0;
               $counter=0;

                foreach($contents as $arrays)
                {   $counter=$counter+1;
                    // echo($counter);
                  if(!isset($arrays)){
                    
                      continue;
                  }
                    $name = explode(",",$arrays);
                    // if(!isset($name[1]))
                    // {
                    //     deleterecord($contents,$district);
                    //     // echo "deleted";
                    //     continue;
                    // }

                    
                    $fname_lname=explode("_",$name[0]);

                    print_r($fname_lname[0]."\t".$fname_lname[1]."\n");
                 if(!agentids(@$name[5],@$name[4])==null)
                  {
                      
                    if(!isset($name[6])){
                      
                    // if(count($name)>5){
                        
                        
                        DB::table('members')->updateOrInsert(
                            ['fname'=>$fname_lname[0],'LName'=>$fname_lname[1],'memberdistrict'=>districtid($name[6]),'gender'=>strtoupper($name[2]),'agentid'=>agentsid($name[5],$name[4])]
                         
                        );
                        
                    // }
                    }
                    else
                    {
                        // doesnt allow to enter incomplete details
                        // if(count($name)>6)
                        // {

                            if(getrecommendid($name[3]))
                            {  
                                DB::table('members')->updateOrInsert(
                                    ['fname'=>$fname_lname[0],'LName'=>$fname_lname[1],'recommender'=>$name[3],'memberdistrict'=>districtid($name[6]),'gender'=>strtoupper($name[2]),'agentid'=>agentsid($name[5],$name[4])]
                                 
                                );

                        
                            }
                          else
                             {
                                Storage::append('error/'.$district,'wrong recommender id  '.$arrays.'');
                             }      
                        // }
                    }
                  }
                  else{
                    if(!isset($name[1])){
                        
                         
                        if($counter==count($contents)){
                            // print_r ("\n".count($contents)."\n");
                            //   $su=Storage::append('success/'.$district,' total records not inserted into the database '.$fail.'');
                              $su=Storage::prepend('success/'.$district,' total records not inserted into the database '.$fail.'');
                        }
                        deleterecord($contents,$district,$content);
                        
                        continue;
                    }
                    $fail=$fail+1;
                    Storage::append('error/'.$district,''.$arrays.' #invalid signature with the following details ');
                    
                    
                  }
                 
                }

                  
    
            }
    
        
    
    }
           
    
    
}
