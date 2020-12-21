<?php
//    error_reporting(E_ALL);
//ini_set('display_errors', TRUE);
//ini_set('display_startup_errors', TRUE);
header('Access-Control-Allow-Origin', '*');
header('Content-Type: application/json; charset=UTF-8');
    use Elasticsearch\ClientBuilder;
    
    require 'vendor/autoload.php';

    $hosts = [
    		'10.60.185.217:9200'
	];

	$client = ClientBuilder::create()           // Instantiate a new ClientBuilder
                    ->setHosts($hosts)      // Set the hosts
                    ->build();              // Build the client object


if(isset($_GET['search'])){
	$keyword=$_GET['search'];
if(isset($_GET['page'])){
		$page=$_GET['page'];
		$fr = ($page - 1) * 10;
}else{
	$fr=0;
}


$st=$_GET['st'];
$ed=$_GET['ed'];

if($st==null && $ed==null){
$params = [
 'index' => '_all',
    'type' => 'search',
    'body' => [
    	"from" => $fr, "size" => 10,
	'sort'=>[
	['_score'=>['order'=>'desc']],
	['published'=>['order'=>'desc']]
	],
        	'query'=> [
        'function_score'=> [
            'query'=> [
                'multi_match' => [
					'query'=> $keyword, 
					'fields'=> ['head_title^4', 'description^3', 'url^5', 'description.ngram'] 
				]
            ],
            'script_score' => [
                'script' => [
                  'source'=> "Math.log(2 + doc['clicked'].value)"
                ]
            ]
        ]
    ],
	'highlight' => [
		'pre_tags' => ['<b>'],
		'post_tags' => ['</b>'],
		'fields' => [
                	'description' => ['fragment_size' => 85, 'number_of_fragments' => 3]
            ]
        ]
    ]
];
}else{
$params = [
 'index' => '_all',
    'type' => 'search',
    'body' => [
    	"from" => $fr, "size" => 10,
	'sort'=>[
	['_score'=>['order'=>'desc']],
	['published'=>['order'=>'desc']],
	],
        	'query'=> [
        'function_score'=> [
            'query'=> [
                'bool'=>[
            		'must' => [
            			'multi_match'=> [
						    'query'=> $keyword, 
						    'fields'=> ["head_title^4", "description^3", "url^5", "description.ngram"] 
            			]
            		],
					'filter'=>[[
						'range'=>[
							'published' => [
				                'gte'=> $st,
				                'lte'=> $ed
				            ]
						]
					]]
            	]
            ],
            'script_score' => [
                'script' => [
                  'source'=> "Math.log(2 + doc['clicked'].value)"
                ]
            ]
        ]
    ],
	'highlight' => [
		'pre_tags' => ['<b>'],
		'post_tags' => ['</b>'],
		'fields' => [
                	'description' => ['fragment_size' => 85, 'number_of_fragments' => 3]
            ]
        ]
    ]
];
}
$response = $client->search($params);

$myJSON = json_encode($response);

echo $myJSON;	
}else{
	echo json_encode('must fill the keyword first');
}

// $hits = count($response['hits']['hits']);
// $result = null;
// $i = 0;
 
// while ($i < $hits) {
//  $result[$i] = $response['hits']['hits'][$i]['_source'];
//  $i++;
// }
// foreach ($result as $key => $value) {
//  echo $value['first field'] . "<br>";
// }
    ?>

