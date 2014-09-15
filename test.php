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
  , 'url'          => 'http://www.tapioca.dev/'
  , 'clientId'     => '540e011b8597d'
  , 'clientSecret' => 'dd4111734d012012b271cdce8aded611'
  , 'fileStorage'  => 'http://www.tapioca.dev/library/ours-roux/'
  , 'filesystem'   => array(
      'path' => __DIR__ . '/cache/'
    )
  // , 'cache'        => array(
  //       'strategy'     => 'memory'
  //   )
  // , 'cache' => array(
  //     'ttl' => 5
  //   )
);

$docRef  = '5414de53cb09e'; // '5414bcc54a15a';
$colSlug = 'test';  //'deps'

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

// $clientTapioca->setlocale('en_UK');

$query = new Query();

$query
  ->limit(1)
  ->skip(0);

  // ->select( 'title', 'catagory' )
  // ->exclude('desc')
  // ->setlocale('fr_FR');
  // ->orderBy('_tapioca.created', -1)
  // ->where('_tapioca.published', true)
  // ->orWhereLte('_tapioca.created', 1404809559);

// echo json_encode($query->getQuery());
// exit;

try
{
  $collection = $clientTapioca->collection( $colSlug, $query );
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

if( !$collection->count() )
  exit;

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

try
{
  // print original document
  print_r( $collection->at( 0 )->get() ); 
  echo '<br>';
}
catch( TapiocaException\DocumentNotFoundException $e )
{
  // uncomment to get error details
  echo $e->getMessage();
  exit();
}
catch( TapiocaException\InvalidArgumentException $e )
{
  // uncomment to get error details
  echo $e->getMessage();
  exit();
}

try
{
  // print original document
  // 5414bb88df94d
  print_r( $collection->get( $docRef )->get('title') ); 
  echo '<br>';
}
catch( TapiocaException\DocumentNotFoundException $e )
{
  // uncomment to get error details
  echo $e->getMessage();
  // exit();
}

echo $collection->at(0)->get('title'); // get title value
echo ', ';
echo $collection->at(0)->get('undefinedField', 'a default value');
echo ' by ';
echo $collection->at(0)->get('_tapioca.user.username');
// exit;
echo '<hr>';

try
{
  $document = $clientTapioca->document( $colSlug, $docRef );
}
catch( TapiocaException\ErrorResponseException $e )
{
echo 'doucment ?<br>';
  // uncomment to get error details
  echo $e->getMessage();
  // exit('404');
}

var_dump( $document );
echo '<br>';
echo $document->tapioca('ref') . ' || ';
echo $document->tapioca('user.username') . ' || ';
echo $document->title.' || ';
echo $document->desc;
echo $document->undefinedField; // return empty string


echo '<hr>';
// exit;
// Preview
echo 'preview<br>';
try
{
  $preview = $clientTapioca->preview( 'fb1e19a3991780e4513147c6867ab37876d6a0ca' );
  var_dump( $preview );
}
catch( TapiocaException\ErrorResponseException $e )
{
  // uncomment to get error details
  // echo $e->getMessage();
  exit('preview 404');
}
echo $preview->tapioca('ref') . ' || ';
echo $preview->tapioca('user.username') . ' || ';
echo $preview->title.' || ';
exit();