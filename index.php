<?php 
session_start();

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';
require 'vendor/vendor/autoload.php';

$app = new \Slim\App;

$app->get('/v1/albums', function (Request $request, Response $response, array $args) {
    if(!isset($_GET['q']) || $_GET['q'] == ''){
        $res = [
            "error" => "Falta el nombre de la banda."
        ];
        $json_response = json_encode($res);
        $response->getBody()->write("$json_response");

        return $response;
    }else{
        if(!isset($_SESSION['name'])){
            $_SESSION['name'] = $_GET['q'];
        }

        $session = new SpotifyWebAPI\Session(
            '6ee8726695d34042a9204eba953b48fd',
            '1aa2e3ffc56f41bb9eebfc3467ee41b2',
            'http://localhost/api/Callback.php'
        );
    
        $api = new SpotifyWebAPI\SpotifyWebAPI();
        
        if (isset($_SESSION['code'])) {
            
            $session->requestAccessToken($_SESSION['code']);
            $api->setAccessToken($session->getAccessToken());
            
            $query = urldecode($_GET['q']);
            $type = "album";

            $res = $api->search($query, $type);

            if(count($res->albums->items) > 0) {
                $id = $res->albums->items[0]->artists[0]->id;
                $res = $api->getArtistAlbums($id);
                foreach($res->items as $album){
                    $item = [
                        "name" => $album->name,
                        "realesed" => $album->release_date,
                        "tracks" => $album->total_tracks,
                        "cover" => $album->images
                    ];
                    $albums[] = $item;
                }

                $json_response = json_encode($albums);
                $response->getBody()->write("$json_response");

                unset($_SESSION['name']);
                unset($_SESSION['code']);

                return $response;
            } else {

                unset($_SESSION['name']);
                unset($_SESSION['code']);
                
                $res = [
                    "error" => "No se encontraron datos."
                ];

                $json_response = json_encode($res);
                $response->getBody()->write("$json_response");
            }
        } else {
            $options = [
                'scope' => [
                    'user-read-email',
                ],
            ];
            header('Location: ' . $session->getAuthorizeUrl($options));
            die();
        }
    }
});
$app->run();