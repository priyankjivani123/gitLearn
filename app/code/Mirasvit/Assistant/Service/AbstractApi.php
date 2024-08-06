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

use Mirasvit\Core\Service\SerializeService;

abstract class AbstractApi
{
    protected function request(string $token, string $api, array $input): array
    {
        $ch = curl_init('https://api.openai.com/v1/' . $api);

        $authorization = "Authorization: Bearer " . $token;

        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', $authorization]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, SerializeService::encode($input));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $result = curl_exec($ch);
        curl_close($ch);

        $data = SerializeService::decode($result);
        if (getenv("LOG_REQUEST")== "1") {
            echo "\n\n==== REQUEST === \n\n";
            $json_string = json_encode($input, JSON_PRETTY_PRINT);
            echo "{$json_string}\n\n";
            echo "==== ANSWER === \n\n";
            $json_string = json_encode($data, JSON_PRETTY_PRINT);
            echo "{$json_string}\n\n";
            echo "==== END === \n\n\n";
        }
        if (isset($data['error'])) {
            throw new \Exception($data['error']['message'].' '.$data['error']['type']);
        }
        if (!$data) {
            throw new \Exception("OpenAI API Error: ".$result);
        }
        return $data;
    }
}
