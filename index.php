<?php


class API{
	
	public static function crul($endpoints){
		
		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => "https://5f27781bf5d27e001612e057.mockapi.io/webprovise/".$endpoints,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "GET",
		  CURLOPT_HTTPHEADER => array(
		    "cache-control: no-cache",
		    "postman-token: f26cdb0b-29f7-e62d-f673-51a481ceaac1"
		  ),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
		  return ["error" => "cURL Error #:" . $err];
		} else {
		  return ["error" => false, "data"=> json_decode($response)];

		}	
	}
}

class Travel
{

	public $travel_data;
	public function __construct(){
		$result=API::crul('travels');
		$this->travel_data=$result['data'];	
	}
	function get($companyId){
		foreach ($this->travel_data as $key => $value) {
			if($value->companyId==$companyId){
				return $value;
			}
		}
	}
	
}

class Company
{
	public $companies;
	public function __construct(){
		$result=API::crul('companies');
		$this->companies=$result['data'];	
	}
	function get_compnies(){
		$travel_data=new Travel();
		$companies=$this->companies;
		foreach ($companies as $key => $value) {
			$companies[$key]->price=isset($travel_data->get($value->id)->price)?$travel_data->get($value->id)->price:0;
		}
		return $this->companies;

	}
	public static function get_compaines_tree(array $companies, $parentId = 0) {
	    $filtered_companies = array();

	    foreach ($companies as $company) {
	        if ($company->parentId == $parentId) {
	            $children = Company::get_compaines_tree($companies, $company->id);
	            if ($children) {
	                $company->children = $children;

	            } else{
	            	$company->children=[];
	            }
	            $filtered_companies[] = $company;
	        }
	    }
	    //return $filtered_companies;
	    return $filtered_companies;
	}
	public static function recursiveSum($source)
	{
	    $children = $source->children ?? [];

	    $sum = $source->price ?? 0;

	    foreach($children as $index => $child)
	    {
	        $source->children[$index] = Company::recursiveSum($child);
	        $sum += $source->children[$index]->cost;
	    }
	    $temp_children=$source->children;
	    unset($source->children);
	    unset($source->createdAt,$source->parentId);
	    $source->cost = $sum;
	    $source->children = $temp_children;
	    return $source;
	}
}

class TestScript
{
    public function execute()
    {
        $start = microtime(true);
        $companies=new Company();
        $e_companies=[];
        $companies=Company::get_compaines_tree($companies->get_compnies());
	foreach ($companies as $key => $company) {
        	$e_companies[]=Company::recursiveSum($companies[0]);

        }
        echo json_encode($e_companies);
        echo 'Total time: '.  (microtime(true) - $start);
    }
}

(new TestScript())->execute();
