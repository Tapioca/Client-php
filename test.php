<?

include('vendor/autoload.php');

use Tapioca\Client as Tapioca;
use Tapioca\Query as Query;
use Tapioca\Exception as TapiocaException;
use Tapioca\Cache\Filesystem as Cache;

echo "<pre>";
// try 
// {
//   $cache = new Cache( 'ours-roux', 'tapioca',  10, array(
//       'path' => __DIR__ . '/cache/'
//   ));  
// }
// catch ( TapiocaException\CacheException $e )
// {
//   exit( $e->getMessage() );
// }

// $query = 'oauth'; // '{"foo":"bar"}';
// $collection = 'token'; // 'projets';

// $cache->set( $collection, $query, array('test' => 'ok'));
// exit();

$config = array(
    'slug'         => 'ours-roux'
  , 'url'          => 'http://www.tapioca.dev/api/0.1/'
  , 'clientId'     => '53baa0846eccf'
  , 'clientSecret' => 'b31f5fa0cdb3be252a18a2db0e4b7c60'
  , 'fileStorage'  => 'http://www.tapioca.dev/library/ours-roux/'
  , 'filesystem'   => array(
      'path' => __DIR__ . '/cache/'
    )
  // , 'cache' => array(
  //     'ttl' => 5
  //   )
);

try
{
  $clientTapioca = Tapioca::client( $config );
}
catch( TapiocaException\InvalidArgumentException $e )
{
  exit($e->getMessage());
}
catch( TapiocaException\ErrorResponseException $e )
{
  exit($e->getMessage());
}

$clientTapioca->setlocale('en_UK');

$query = new Query();

$query
  // ->select( 'title', 'catagory' )
  // ->exclude('desc')
  ->limit(2)
  ->skip(0)
  ->setlocale('fr_FR')
  ->orderBy('_tapioca.created', -1)
  ->where('_tapioca.published', true)
  ->orWhereLte('_tapioca.created', 1404809559);

// echo json_encode($query->getQuery());
// exit;

try
{
  $collection = $clientTapioca->collection( 'projets', $query, null, true ); //, $query );
}
catch( TapiocaException\ErrorResponseException $e )
{
  exit($e->getMessage());
}

// Debug 
print_r($collection->query());
echo '<br>';
print_r($collection->debug());
// exit;
echo $collection->count() .' on '.$collection->total().' documents<br>';
// 1 on 21 documents

echo '<ul>';
foreach( $collection as $document)
{
    echo '<li>';
    echo $document->tapioca('ref') . ' || ';
    echo $document->tapioca('user.username') . ' || ';
    echo $document->title.' || ';
    echo $document->desc;
    echo $document->undefinedField; // return empty string
    echo '</li>';
}
echo '</ul>';

// print original document
print_r( $collection->at(0)->get() ); 
echo '<br>';
// print original document
print_r( $collection->get( '53bbb157a5226' )->get('title') ); 
echo '<br>';

echo $collection->at(0)->get('title'); // get title value
echo ', ';
echo $collection->at(0)->get('undefinedField', 'a default value');
echo ' by ';
echo $collection->at(0)->get('_tapioca.user.username');

echo '<hr>';

try
{
  $document = $clientTapioca->document( 'projets', '53bbd1166444d' );
}
catch( TapiocaException\ErrorResponseException $e )
{
  exit($e->getMessage());
}

var_dump( $document );
echo '<br>';
echo $document->tapioca('ref') . ' || ';
echo $document->tapioca('user.username') . ' || ';
echo $document->title.' || ';
echo $document->desc;
echo $document->undefinedField; // return empty string
