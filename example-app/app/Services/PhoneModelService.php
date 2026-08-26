<?php

namespace App\Services;

use App\Models\PhoneModelQuery;
use App\Models\PhoneModelResult;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

/**
 * 手机型号查询服务
 *
 * 负责调用第三方接口, 并将结果保存到数据库
 *
 * @package App\Services
 */
class PhoneModelService
{
    /**
     * HTTP 客户端
     *
     * @var \GuzzleHttp\Client
     */
    protected $httpClient;

    public function __construct(Client $httpClient = null)
    {
        $this->httpClient = $httpClient ?: new Client([
            'timeout' => (float) config('phonemodel.timeout', 60),
        ]);
    }

    /**
     * 执行手机型号查询
     *
     * 调用第三方接口并将请求/响应结果保存到数据库
     *
     * @param array   $phones       手机号列表(最多10000个, 支持11位明文或32位md5)
     * @param array   $targetBrands 需要匹配的目标机型ID列表
     * @param string  $phoneType    数据类型: PLAINTEXT 明文 / MD5
     * @param string  $source       业务来源标识(可选)
     * @param array   $pathParams   覆盖第三方路径参数 {id}/{cid}/{code}/{type}(可选)
     * @return array 返回 [queryRecord, rawResponse, success]
     */
    public function query(array $phones, array $targetBrands, $phoneType = 'MD5', $source = null, array $pathParams = [])
    {
        // 参数校验
        $phones = array_values(array_unique(array_filter(array_map('trim', $phones))));
        $targetBrands = array_values(array_unique(array_map('intval', $targetBrands)));

        $maxPhones = (int) config('phonemodel.max_phones', 10000);
        if (count($phones) > $maxPhones) {
            throw new \InvalidArgumentException("手机号数量不能超过 {$maxPhones} 个");
        }
        if (empty($phones)) {
            throw new \InvalidArgumentException('手机号列表不能为空');
        }

        // 组装路径参数
        $apiId   = $pathParams['id']   ?? config('phonemodel.id');
        $apiCid  = $pathParams['cid']  ?? config('phonemodel.cid');
        $apiCode = $pathParams['code'] ?? config('phonemodel.code');
        $apiType = $pathParams['type'] ?? config('phonemodel.type');

        $baseUrl   = rtrim(config('phonemodel.base_url'), '/');
        $path      = config('phonemodel.path');
        $path      = str_replace(
            ['{id}', '{cid}', '{code}', '{type}'],
            [urlencode($apiId), urlencode($apiCid), urlencode($apiCode), urlencode($apiType)],
            $path
        );
        $url = $baseUrl . $path;

        // 请求体
        $body = [
            'phones'       => $phones,
            'targetBrands' => $targetBrands,
            'phoneType'    => strtoupper($phoneType) ?: 'MD5',
        ];

        // 记录查询开始
        $query = PhoneModelQuery::create([
            'source'          => $source,
            'api_id'          => $apiId,
            'api_cid'         => $apiCid,
            'api_code'        => $apiCode,
            'api_type'        => $apiType,
            'phone_count'     => count($phones),
            'phone_type'      => strtoupper($phoneType) ?: 'MD5',
            'request_params'  => json_encode(['url' => $url, 'body' => $body], JSON_UNESCAPED_UNICODE),
            'status'          => PhoneModelQuery::STATUS_FAILED,
        ]);

        $startTime = microtime(true);

        try {
            $response = $this->httpClient->get($url, [
                'query' => $body,
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);

            $durationMs = round((microtime(true) - $startTime) * 1000, 2);
            $statusCode = $response->getStatusCode();
            $responseBody = (string) $response->getBody();

            // 解析响应
            $decoded = json_decode($responseBody, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $decoded = ['raw' => $responseBody];
            }

            // 保存响应信息
            $query->duration_ms = $durationMs;
            $query->response_data = $responseBody;

            if ($statusCode >= 200 && $statusCode < 300) {
                $query->response_code = $this->extractResponseCode($decoded);
                $query->response_message = $this->extractResponseMessage($decoded);
                $query->status = PhoneModelQuery::STATUS_SUCCESS;
                $query->save();

                // 解析并保存结果明细
                $details = $this->saveResults($query, $phones, $decoded);

                return [
                    'query'   => $query,
                    'data'    => $decoded,
                    'details' => $details,
                    'status'  => true,
                ];
            }

            $query->error_message = "HTTP {$statusCode}";
            $query->save();

            return [
                'query'  => $query,
                'data'   => $decoded,
                'status' => false,
            ];
        } catch (\Exception $e) {
            $durationMs = round((microtime(true) - $startTime) * 1000, 2);
            $query->duration_ms = $durationMs;
            $query->error_message = $e->getMessage();
            $query->status = PhoneModelQuery::STATUS_FAILED;
            $query->save();

            Log::error('手机型号查询接口调用失败', [
                'url'    => $url,
                'error'  => $e->getMessage(),
                'query_id' => $query->id,
            ]);

            return [
                'query'  => $query,
                'data'   => null,
                'status' => false,
                'error'  => $e->getMessage(),
            ];
        }
    }

    /**
     * 解析并保存查询结果明细
     *
     * 适配常见第三方响应结构:
     *  - { code, msg, data: { items: [{phone, model, ...}] } }
     *  - { code, msg, data: [{phone, model, ...}] }
     *  - { code, msg, data: { phone: { ... } } }
     *  - { code, msg, phones: [{phone, model}] }
     *
     * @param PhoneModelQuery $query
     * @param array $phones
     * @param array $decoded
     * @return array 返回 [phone => ['model_name' => ..., 'matched' => bool], ...]
     */
    protected function saveResults(PhoneModelQuery $query, array $phones, array $decoded)
    {
        // 提取结果列表
        $items = $this->extractItems($decoded);

        if (empty($items)) {
            return [];
        }

        $results = [];
        $details = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $phone = $item['phone']
                ?? $item['mobile']
                ?? $item['mobileNo']
                ?? $item['number']
                ?? $item['tel']
                ?? null;

            if (!$phone) {
                continue;
            }

            // 提取匹配到的机型(兼容 device/deviceName 字段)
            $model = $item['model']
                ?? $item['modelName']
                ?? $item['phoneModel']
                ?? $item['brand']
                ?? $item['brandName']
                ?? $item['typeName']
                ?? $item['device']
                ?? $item['deviceName']
                ?? $item['deviceModel']
                ?? null;

            $matched = $this->isMatched($item, $model);

            $results[] = [
                'query_id'   => $query->id,
                'phone'      => (string) $phone,
                'model_name' => $model !== null ? (string) $model : null,
                'matched'    => $matched ? 1 : 0,
                'brands'     => $this->extractBrands($item),
                'raw_result' => json_encode($item, JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $details[(string) $phone] = [
                'model_name' => $model !== null ? (string) $model : null,
                'matched'    => $matched,
            ];
        }

        if (!empty($results)) {
            PhoneModelResult::insert($results);
        }

        return $details;
    }

    /**
     * 从响应中提取结果条目列表
     *
     * @param array $decoded
     * @return array
     */
    protected function extractItems(array $decoded)
    {
        if (isset($decoded['data']) && is_array($decoded['data'])) {
            $data = $decoded['data'];

            // data 是索引数组 => 列表
            if (array_keys($data) === range(0, count($data) - 1)) {
                return $data;
            }

            // data.items
            if (isset($data['items']) && is_array($data['items'])) {
                return $data['items'];
            }
            if (isset($data['list']) && is_array($data['list'])) {
                return $data['list'];
            }
            if (isset($data['result']) && is_array($data['result'])) {
                return $this->flatten($data['result']);
            }
            if (isset($data['data']) && is_array($data['data'])) {
                return $this->flatten($data['data']);
            }

            // data 是关联数组, 每个 key 是手机号
            return $this->flatten($data);
        }

        if (isset($decoded['phones']) && is_array($decoded['phones'])) {
            return $decoded['phones'];
        }
        if (isset($decoded['list']) && is_array($decoded['list'])) {
            return $decoded['list'];
        }
        if (isset($decoded['items']) && is_array($decoded['items'])) {
            return $decoded['items'];
        }
        if (isset($decoded['result']) && is_array($decoded['result'])) {
            return $this->flatten($decoded['result']);
        }

        return $this->flatten($decoded);
    }

    /**
     * 将关联数组(键为手机号)扁平化为条目列表
     *
     * @param array $data
     * @return array
     */
    protected function flatten(array $data)
    {
        $items = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                // 如果没有 phone 字段, 尝试用 key 作为手机号
                if (!isset($value['phone']) && !isset($value['mobile'])
                    && !isset($value['mobileNo']) && !isset($value['number']) && !isset($value['tel'])) {
                    $value['phone'] = $key;
                }
                $items[] = $value;
            }
        }
        return $items;
    }

    /**
     * 提取响应中的业务码
     *
     * @param array $decoded
     * @return int|null
     */
    protected function extractResponseCode(array $decoded)
    {
        foreach (['code', 'status', 'code_', 'resultCode', 'retCode', 'ret', 'errcode'] as $key) {
            if (isset($decoded[$key]) && is_numeric($decoded[$key])) {
                return (int) $decoded[$key];
            }
        }
        return null;
    }

    /**
     * 提取响应中的信息
     *
     * @param array $decoded
     * @return string|null
     */
    protected function extractResponseMessage(array $decoded)
    {
        foreach (['message', 'msg', 'msg_', 'resultMsg', 'retMsg', 'errmsg', 'error', 'description'] as $key) {
            if (isset($decoded[$key]) && is_string($decoded[$key])) {
                return substr($decoded[$key], 0, 255);
            }
        }
        return null;
    }

    /**
     * 判断是否命中目标机型
     *
     * @param array $item
     * @param mixed $model
     * @return bool
     */
    protected function isMatched(array $item, $model)
    {
        // 尝试显式命中字段
        foreach (['matched', 'hit', 'isMatch', 'is_hit', 'match'] as $key) {
            if (isset($item[$key])) {
                return (bool) $item[$key];
            }
        }
        // 有明确机型即视为命中(未命中通常模型字段为空)
        return !empty($model);
    }

    /**
     * 提取机型结果列表
     *
     * @param array $item
     * @return string|null
     */
    protected function extractBrands(array $item)
    {
        foreach (['brands', 'models', 'modelList', 'phoneModels', 'targetModels'] as $key) {
            if (isset($item[$key]) && is_array($item[$key])) {
                return json_encode($item[$key], JSON_UNESCAPED_UNICODE);
            }
        }
        return null;
    }
}
