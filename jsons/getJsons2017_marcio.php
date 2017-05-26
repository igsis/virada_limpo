<?php
date_default_timezone_set('America/Sao_Paulo');
if (file_exists(__DIR__ . '/api-config.php')) {
    include __DIR__ . '/api-config.php';
}
if(!defined('API_URL')) define('API_URL', "http://spcultura.prefeitura.sp.gov.br/api/");
if(!defined('REPLACE_IMAGES_URL_FROM')) define('REPLACE_IMAGES_URL_FROM', 'http://spcultura.prefeitura.sp.gov.br/files/');
if(!defined('REPLACE_IMAGES_URL_TO')) define('REPLACE_IMAGES_URL_TO', 'http://spcultura.prefeitura.sp.gov.br/files/');
$project_id = 1763;
$date_from = '2016-05-21';
$date_to = '2016-05-22';
$children_project_ids = json_decode(file_get_contents(API_URL . "project/getChildrenIds/{$project_id}"));
$children_project_ids[] = $project_id;
$project_ids = implode(',',$children_project_ids);
$get_spaces_url = API_URL . "space/findByEvents?@version=1&@select=id,name,shortDescription,endereco,location&@files=(avatar.viradaSmall,avatar.viradaBig):url&@order=name&@from={$date_from}&@to={$date_to}&project=IN({$project_ids})";
$get_events_url = API_URL . "event/find?@version=1&@select=id,name,subTitle,shortDescription,description,classificacaoEtaria,terms,traducaoLibras,descricaoSonora,project.id,project.name,project.singleUrl&@files=(avatar.viradaSmall,avatar.viradaBig):url&project=IN({$project_ids})";
echo "\nbaixando eventos $get_events_url\n\n";
echo "<br />";
$events_json = file_get_contents($get_events_url);
echo "\nbaixando espaços $get_spaces_url\n\n";
echo "<br />";
$spaces_json = file_get_contents($get_spaces_url);
$spaces = json_decode($spaces_json);
$events = array();
$events_by_id = array();
$event_ids = [];
foreach (json_decode($events_json) as $e) {
    $events[] = $e;
    $events_by_id[$e->id] = $e;
    $event_ids[] = $e->id;
}
$result_events = array();
if($event_ids){
    $event_ids = implode(',', $event_ids);
    $occurrences_json = file_get_contents(API_URL . "eventOccurrence/find?@version=1&@select=id,space.{id,name},eventId,rule&event=IN($event_ids)&@order=_startsAt");
    $occurrences = json_decode($occurrences_json);
    $count = 0;
    foreach ($occurrences as $occ) {
        $rule = json_decode($occ->rule);
        $e = clone $events_by_id[$occ->eventId];
        $e->id = $occ->id;
        $e->eventId =  $occ->eventId;
        $e->spaceId = $occ->space->id;
        $e->startsAt = $rule->startsAt;
        $e->startsOn = $rule->startsOn;
        $datetime = new DateTime("{$rule->startsOn} {$rule->startsAt}");
        $e->price = $rule->price;
        $e->timestamp = $datetime->getTimestamp();
        $e->duration = @$rule->duration;
        if($e->duration == 1440){
            $e->duration = '24h00';
        }
        $e->acessibilidade = array();
        if($e->traducaoLibras)
            $e->acessibilidade[] = 'Tradução para LIBRAS';
        if($e->descricaoSonora)
            $e->acessibilidade[] = 'Descrição sonora';
        $small_image_property = '@files:avatar.viradaSmall';
        $big_image_property = '@files:avatar.viradaBig';
        if (property_exists($e, $small_image_property)) {
            $e->defaultImage = str_replace(REPLACE_IMAGES_URL_FROM, REPLACE_IMAGES_URL_TO, $e->$big_image_property->url);
            $e->defaultImageThumb = str_replace(REPLACE_IMAGES_URL_FROM, REPLACE_IMAGES_URL_TO, $e->$small_image_property->url);
            $e->image768 = str_replace(REPLACE_IMAGES_URL_FROM, REPLACE_IMAGES_URL_TO, $e->$small_image_property->url);
            $e->image800 = str_replace(REPLACE_IMAGES_URL_FROM, REPLACE_IMAGES_URL_TO, $e->$big_image_property->url);
            $e->image1024 = str_replace(REPLACE_IMAGES_URL_FROM, REPLACE_IMAGES_URL_TO, $e->$big_image_property->url);
            $e->image1280 = str_replace(REPLACE_IMAGES_URL_FROM, REPLACE_IMAGES_URL_TO, $e->$big_image_property->url);
        } else {
            $e->defaultImage = '';
            $e->defaultImageThumb = '';
        }
        $result_events[] = $e;
    }
}

if (json_last_error() == 0) {
    echo '- Nao houve erro! O parsing foi perfeito';
}	
else {			
    echo 'Erro!</br>';
	switch (json_last_error()) {
	
        case JSON_ERROR_DEPTH:
            echo ' - profundidade maxima excedida';
        break;
        case JSON_ERROR_STATE_MISMATCH:
            echo ' - state mismatch';
        break;
        case JSON_ERROR_CTRL_CHAR:
            echo ' - Caracter de controle encontrado';
        break;
        case JSON_ERROR_SYNTAX:
            echo ' - Erro de sintaxe! String JSON mal-formada!';
        break;
        case JSON_ERROR_UTF8:
            echo ' - Erro na codificação UTF-8';
        break;
        default:
            echo ' – Erro desconhecido';
        break;
    }
}


file_put_contents(__DIR__ . '/events.json', json_encode($result_events));
file_put_contents(__DIR__ . '/spaces.json', json_encode($spaces));

if (json_last_error() == 0) {
    echo '- Nao houve erro! O parsing foi perfeito';
}	
else {			
    echo 'Erro!</br>';
	switch (json_last_error()) {
	
        case JSON_ERROR_DEPTH:
            echo ' - profundidade maxima excedida';
        break;
        case JSON_ERROR_STATE_MISMATCH:
            echo ' - state mismatch';
        break;
        case JSON_ERROR_CTRL_CHAR:
            echo ' - Caracter de controle encontrado';
        break;
        case JSON_ERROR_SYNTAX:
            echo ' - Erro de sintaxe! String JSON mal-formada!';
        break;
        case JSON_ERROR_UTF8:
            echo ' - Erro na codificação UTF-8';
        break;
        default:
            echo ' – Erro desconhecido';
        break;
    }
}