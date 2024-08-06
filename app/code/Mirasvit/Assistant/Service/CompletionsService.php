<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-assistant
 * @version   1.3.11
 * @copyright Copyright (C) 2024 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Assistant\Service;

use Mirasvit\Assistant\Model\ConfigProvider;
use Mirasvit\Assistant\Api\Data\PromptInterface;

class CompletionsService extends AbstractApi
{
    protected $configProvider;

    public function __construct(
        ConfigProvider $configProvider
    ) {
        $this->configProvider = $configProvider;
    }

    public function answerByContext(PromptInterface $promt, array $context): string
    {
        $varArray                    = $this->contextToVariableArray($context);
        $varArray['global']['input'] = '';

        $template = new \Liquid\Template();
        $template->parse($promt->getPrompt());

        $text = $template->render($varArray);

        return $this->answer($promt, $text);
    }

    public function answer(PromptInterface $promt, string $text): string
    {
        if (!$this->configProvider->OpenAIKey()) {
            throw new \RuntimeException("OpenAI Secret Key is not set. Please set it in the extension settings");
        }
        if (getenv("LOG_PROMPT") == "1") {
            echo "\n\n==== PROMPT === \n";
            echo "{$text}\n";
            echo "==== END PROMPT === \n\n";
        }

        $result = '';

        $model = $promt->getOpenAIModel();

        if ($model == PromptInterface::CONFIG_MODEL) {
            $model = $this->configProvider->OpenAIModel();
        }

        switch ($model) {
            case ConfigProvider::OPENAI_MODEL_GTP4:
            case ConfigProvider::OPENAI_MODEL_GTP4_TURBO:
                $result = $this->answerGpt4($promt, $text, $model);
                break;
            case ConfigProvider::OPENAI_MODEL_GTP35:
                $result = $this->answerGpt35($promt, $text, $model);
                break;
            case ConfigProvider::OPENAI_MODEL_GTP3:
                $result = $this->answerGpt3($promt, $text, $model);
                break;
            default:
                $result = $this->answerGpt3($promt, $text, $model);
        }

        if ($promt->isConvert2Html()) {
            $result = $this->convert2Html($result);
        }

        return $result;
    }

    public function answerGpt3(PromptInterface $promt, string $text, string $model = null): string
    {
        $key   = $this->configProvider->OpenAIKey();
        $model = $model ? : $this->configProvider->OpenAIModel();
        $input = [
            'model'             => $model,
            'temperature'       => 0.7,
            'max_tokens'        => 2097,
            'top_p'             => 1,
            'frequency_penalty' => max(0, min(1, $promt->getFrequencyPenalty())),
            'presence_penalty'  => 0,
            'prompt'            => $text,
        ];
        if ($promt->getStopSequences()) {
            $input['stop'] = explode(",", $promt->getStopSequences());
        }

        $result = $this->request($key, 'completions', $input);

        foreach ($result['choices'] as $choice) {
            $answer = (string)$choice['text'];
            $answer = trim($answer);
            $answer = trim($answer, "\n");
            $answer = trim($answer, "\r");
            $answer = trim($answer, "\"");

            return $answer;
        }

        return '';
    }

    public function answerGpt35(PromptInterface $promt, string $text, string $model = null): string
    {
        $key   = $this->configProvider->OpenAIKey();
        $model = $model ? : $this->configProvider->OpenAIModel();
        $input = [
            //https://platform.openai.com/docs/models/gpt-3-5
            'model'    => $model,
            'messages' => [
                ["role" => "user", "content" => $text],
            ],
        ];

        $result = $this->request($key, 'chat/completions', $input);

        foreach ($result['choices'] as $choice) {
            $message = $choice['message'];
            $answer  = $message['content'];

            if ($promt->getStopSequences()) {
                $seq    = trim($promt->getStopSequences());
                $answer = $this->trimToStop($answer, explode(",", $seq));
            }

            $answer = trim($answer);
            $answer = trim($answer, "\n");
            $answer = trim($answer, "\r");
            $answer = trim($answer, "\"");

            return $answer;
        }

        return '';
    }

    public function answerGpt4(PromptInterface $promt, string $text, string $model = null): string
    {
        $key   = $this->configProvider->OpenAIKey();
        $model = $model ? : $this->configProvider->OpenAIModel();
        $input = [
            //https://platform.openai.com/docs/models/gpt-4
            'model'    => $model,
            'messages' => [
                ["role" => "user", "content" => $text],
            ],
        ];

        $result = $this->request($key, 'chat/completions', $input);
        foreach ($result['choices'] as $choice) {
            $message = $choice['message'];
            $answer  = $message['content'];

            if ($promt->getStopSequences()) {
                $seq    = trim($promt->getStopSequences());
                $answer = $this->trimToStop($answer, explode(",", $seq));
            }

            $answer = trim($answer);
            $answer = trim($answer, "\n");
            $answer = trim($answer, "\r");
            $answer = trim($answer, "\"");

            return $answer;
        }

        return '';
    }

    public function convert2Html(string $str): string
    {
        $str = trim($str);
        $str = str_replace("•", "-", $str);
        $re  = '/\n- (.*)/m';
        preg_match_all($re, $str, $matches, PREG_SET_ORDER, 0);

        foreach ($matches as $k => $match) {
            $start = $end = "";
            if ($k == 0) {
                $start = "</p><ul>";
            }
            if ($k == count($matches) - 1) {
                $end = "</ul><p>";
            }
            $str = str_replace($match[0], $start . "<li>" . $match[1] . "</li>" . $end, $str);
        }

        $str = '<p>' . implode("</p>\n<p>", preg_split('/\n+/', $str)) . '</p>';
        $str = str_replace("<p></p>", "", $str);
        $str = preg_replace('/<p>\s*<\/p>\s*/is', '', $str);
        $str = preg_replace('/\s*<\/p>/is', '</p>', $str);

        return $str;
    }

    function tidyHTML(string $buffer)
    {
        $dom                     = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->loadHTML($buffer);
        $dom->formatOutput = true;

        return $dom->saveXML($dom->documentElement);
        //        return($dom->saveHTML());
    }

    private function contextToVariableArray(array $context): array
    {
        if (!count($context)) {
            return $context;
        }

        $variableArray = [];

        foreach ($context as $data) {
            if (!isset($data['id'])) {
                continue;
            }

            [$entity, $varCode] = explode('.', $data['id']);

            $variableArray[$entity][$varCode] = $data['value'];
        }

        return $variableArray;
    }

    private function trimToStop(string $str, array $stop): string
    {
        $min = strlen($str);

        foreach ($stop as $ch) {
            $ch = str_replace('\r\n', "\r\n", $ch);
            $ch = str_replace('\n', "\n", $ch);
            $p  = strpos($str, $ch);
            if ($p > 0) {
                $min = min($min, $p);
            }
        }

        $res = substr($str, 0, $min);

        return $res;
    }
}
