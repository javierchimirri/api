<?php 

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

//autoload Slim3
require 'vendor/autoload.php';
//autoload spotify-api
require 'vendor/vendor/autoload.php';

$app = new \Slim\App;

$app->get('/v1/albums', function (Request $request, Response $response, array $args) {
    //Control que la variable "q" no esté vacía o no exista
    if(!isset($_GET['q']) || $_GET['q'] == '') {
        $res = [
            "error" => "Falta el nombre de la banda."
        ];
        $json_response = json_encode($res);
        $response->getBody()->write("$json_response");

        return $response;
    } else {
        $session = new SpotifyWebAPI\Session(
            '6ee8726695d34042a9204eba953b48fd',
            '1aa2e3ffc56f41bb9eebfc3467ee41b2'
        );
        
        $session->requestCredentialsToken();
        $accessToken = $session->getAccessToken();
    
        $api = new SpotifyWebAPI\SpotifyWebAPI();
        
        $api->setAccessToken($accessToken);
            
        $query = urldecode($_GET['q']); //Obtengo el nombre de la banda o artista sin %20
        $type = "album";

        $res = $api->search($query, $type);
        
        //Obtengo el id del artista o banda para asi obtener sus albums

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
            
            return $response;
        } else {
            $res = [
                "error" => "No se encontraron datos."
            ];
            $json_response = json_encode($res);
            $response->getBody()->write("$json_response");
        }
    }
});
$app->run();