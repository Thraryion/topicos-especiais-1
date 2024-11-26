<?php

require 'vendor/autoload.php';

use Discord\Discord;
use Discord\WebSockets\Event;
use Discord\Parts\Channel\Message;
use Discord\Parts\User\Member;
use Discord\WebSockets\Intents;
use Google\Cloud\Translate\V2\TranslateClient;

$token = 'MTMwODIxNjU0NzI2OTE1Mjg1MA.GpqiKB.gw_kmWYExG6yIVXtvK1t7I5HaTWqsGmz3Y5_PU';
$ApiKey = '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';

$discord = new Discord([
    'token' => $token,
    'intents' => Intents::GUILDS | Intents::GUILD_MESSAGES | Intents::MESSAGE_CONTENT,
]);

function traduzirParaIngles($texto) {
    $translate = new TranslateClient([
        'key' => 'AIzaSyCi238kAGcNKnelKI-WSj8jJ1vr-MAOKS8' 
    ]);
    
    $translation = $translate->translate($texto, [
        'target' => 'en'
    ]);
    
    return $translation['text'];
}

function verificarConteudoComIA($textoIngles) {
    global $ApiKey;
    $url = 'https://api-inference.huggingface.co/models/KoalaAI/Text-Moderation';

    $headers = [
        "Authorization: Bearer $ApiKey",
        "Content-Type: application/json"
    ];
    
    $data = [
        "inputs" => $textoIngles
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code != 200) {
        return "Erro na API. Código HTTP: $http_code";
    }
    
    $decoded = json_decode($result, true);
    
    if (isset($decoded['error'])) {
        return "Erro na API: " . $decoded['error']['message'];
    }

    if (isset($decoded[0])) {
        $labels = $decoded[0];
    
        foreach ($labels as $label) {
            if (in_array($label['label'], ['H', 'HR', 'V', 'S', 'SH', 'S3', 'H2', 'V2']) && $label['score'] > 0.5) {
                return "inapropriada";
            }
        }
    }

    return "aprovada";  
}

$discord->on(Event::MESSAGE_CREATE, function (Message $message) use ($discord) {
    echo "Mensagem recebida: {$message->content}", PHP_EOL;

    if ($message->author->bot) {
        echo "Mensagem ignorada (enviada por um bot).", PHP_EOL;
        return;
    }

    $texto = $message->content;
    echo "Texto da mensagem analisado: {$texto}", PHP_EOL;

    $textoIngles = traduzirParaIngles($texto);
    echo "Texto traduzido para inglês: {$textoIngles}", PHP_EOL;

    try {
        $resultado = verificarConteudoComIA($textoIngles);
        echo "Resultado da análise da IA: {$resultado}", PHP_EOL;
    } catch (Exception $e) {
        if (strpos($e->getMessage(), "429 Too Many Requests") !== false) {
            echo "Erro 429: Limite de requisições atingido. Aguardando antes de tentar novamente.", PHP_EOL;
            sleep(10); 
            try {
                $resultado = verificarConteudoComIA($textoIngles);
                echo "Resultado após retry: {$resultado}", PHP_EOL;
            } catch (Exception $e2) {
                echo "Erro ao tentar novamente: {$e2->getMessage()}", PHP_EOL;
                return;
            }
        } else {
            echo "Erro ao chamar verificarConteudoComIA: {$e->getMessage()}", PHP_EOL;
            return;
        }
    }

    if ($resultado === 'inapropriada') {
        echo "Mensagem marcada como inapropriada. Tentando excluir...", PHP_EOL;
        try {
            $message->delete();
            echo "Mensagem excluída com sucesso.", PHP_EOL;

            $message->channel->sendMessage("Mensagem removida por conter conteúdo inapropriado.");
            echo "Aviso enviado para o canal.", PHP_EOL;
        } catch (Exception $e) {
            echo "Erro ao excluir mensagem ou enviar aviso: {$e->getMessage()}", PHP_EOL;
        }
    } else {
        echo "Mensagem considerada apropriada.", PHP_EOL;
    }
});
