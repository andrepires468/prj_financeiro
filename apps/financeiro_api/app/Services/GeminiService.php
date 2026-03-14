<?php

namespace App\Services;

class GeminiService
{
  protected $apiKey;
  /** Modelo com cota no free tier (altere via GEMINI_MODEL no .env se der quota exceeded). */
  protected $model = 'gemini-2.5-flash';

  public function __construct()
  {
    $this->apiKey = env('GEMINI_API_KEY');
    if (empty($this->apiKey)) {
      throw new \Exception('GEMINI_API_KEY não configurada. Obtenha uma API key em Google AI Studio (https://aistudio.google.com/apikey) e adicione no .env da API.');
    }
    $this->model = env('GEMINI_MODEL', 'gemini-2.5-flash');
    if (empty($this->model)) {
      $this->model = 'gemini-2.5-flash';
    }
  }

  /**
   * Envia um prompt de texto e retorna o texto da resposta.
   *
   * @param string $prompt
   * @return string
   * @throws \Exception
   */
  public function generateContent(string $prompt): string
  {
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . $this->model . ':generateContent?key=' . urlencode($this->apiKey);

    $body = [
      'contents' => [
        [
          'parts' => [
            ['text' => $prompt],
          ],
        ],
      ],
      'generationConfig' => [
        'temperature' => 0.1,
        'maxOutputTokens' => 8192,
        'responseMimeType' => 'application/json',
      ],
    ];

    $response = $this->httpPost($url, $body);

    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
      throw new \Exception('Resposta inválida da Gemini API');
    }

    // Erro retornado pela API (ex.: chave inválida, modelo não encontrado, quota)
    if (!empty($data['error'])) {
      $err = $data['error'];
      $message = $err['message'] ?? $err['status'] ?? json_encode($err);
      if (is_array($message)) {
        $message = json_encode($message);
      }
      throw new \Exception($this->formatGeminiErrorMessage($message));
    }

    $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
    if ($text === null || $text === '') {
      throw new \Exception('Resposta vazia da Gemini API');
    }

    return trim($text);
  }

  /**
   * Mensagem mais clara para o usuário quando a API retorna erro (ex.: quota).
   */
  private function formatGeminiErrorMessage(string $apiMessage): string
  {
    $lower = strtolower($apiMessage);
    if (strpos($lower, 'quota') !== false || strpos($lower, 'rate limit') !== false || strpos($lower, 'resource_exhausted') !== false) {
      return 'Cota ou limite de uso da Gemini API excedido. '
        . 'Tente novamente mais tarde ou no .env defina GEMINI_MODEL=gemini-2.5-flash (ou gemini-1.5-flash). '
        . 'Ver: https://ai.google.dev/gemini-api/docs/rate-limits';
    }
    return 'Gemini API: ' . $apiMessage;
  }

  /**
   * POST JSON com cURL (preferido em Docker) ou file_get_contents.
   */
  private function httpPost(string $url, array $body): string
  {
    $json = json_encode($body);
    if (function_exists('curl_init')) {
      return $this->httpPostCurl($url, $json);
    }
    return $this->httpPostStream($url, $json);
  }

  private function httpPostCurl(string $url, string $json): string
  {
    $ch = curl_init($url);
    if ($ch === false) {
      throw new \Exception('Erro ao chamar Gemini API: não foi possível iniciar cURL');
    }
    curl_setopt_array($ch, [
      CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => $json,
      CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_TIMEOUT => 60,
      CURLOPT_CONNECTTIMEOUT => 15,
      CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $response = curl_exec($ch);
    $errNo = curl_errno($ch);
    $errMsg = curl_error($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($errNo !== 0) {
      $hint = '';
      if (strpos($errMsg, 'resolve') !== false || $errNo === 6) {
        $hint = ' (DNS/rede: em Docker verifique se o container tem acesso à internet)';
      } elseif (strpos($errMsg, 'SSL') !== false || strpos($errMsg, 'certificate') !== false) {
        $hint = ' (SSL: em Docker pode ser necessário CA bundle ou CURLOPT_SSL_VERIFYPEER)';
      }
      throw new \Exception('Erro ao chamar Gemini API: ' . $errMsg . $hint);
    }
    if ($response === false) {
      throw new \Exception('Erro ao chamar Gemini API: resposta vazia do servidor');
    }
    if ($httpCode >= 400) {
      $data = json_decode($response, true);
      $msg = isset($data['error']['message']) ? $data['error']['message'] : 'HTTP ' . $httpCode;
      throw new \Exception($this->formatGeminiErrorMessage($msg));
    }
    return $response;
  }

  private function httpPostStream(string $url, string $json): string
  {
    if (!ini_get('allow_url_fopen')) {
      throw new \Exception('Erro ao chamar Gemini API: allow_url_fopen está desativado. Ative no php.ini ou use extensão cURL.');
    }
    $context = stream_context_create([
      'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\n",
        'content' => $json,
        'timeout' => 60,
      ],
      'ssl' => [
        'verify_peer' => true,
      ],
    ]);
    $response = @file_get_contents($url, false, $context);
    if ($response === false) {
      $error = error_get_last();
      $msg = $error['message'] ?? 'Falha de conexão';
      $hint = ' Em Docker, verifique se o container tem saída HTTPS para generativelanguage.googleapis.com (rede/DNS/firewall).';
      throw new \Exception('Erro ao chamar Gemini API: ' . $msg . $hint);
    }
    return $response;
  }
}
