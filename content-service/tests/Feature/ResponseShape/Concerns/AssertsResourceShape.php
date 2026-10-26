<?php

namespace Tests\Feature\ResponseShape\Concerns;

trait AssertsResourceShape
{
    /**
     * Assert that all expected keys exist in the given response array.
     */
    protected function assertAllResourceKeys(array $expectedKeys, array $responseData): void
    {
        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey(
                $key,
                $responseData,
                "Response is missing expected Resource key: [{$key}]"
            );
        }
    }

    /**
     * Assert that the response data contains exact expected keys (no missing, no unexpected extra).
     */
    protected function assertExactResourceKeys(array $expectedKeys, array $responseData): void
    {
        $actualKeys = array_keys($responseData);
        sort($expectedKeys);
        sort($actualKeys);

        $missingKeys = array_diff($expectedKeys, $actualKeys);
        $extraKeys = array_diff($actualKeys, $expectedKeys);

        $this->assertEmpty(
            $missingKeys,
            'Response is missing keys: '.implode(', ', $missingKeys)
        );
        $this->assertEmpty(
            $extraKeys,
            'Response has unexpected extra keys: '.implode(', ', $extraKeys)
        );
    }

    /**
     * Assert that the actual response data contains all the keys documented in the sample JSON.
     */
    protected function assertMatchesSampleJsonStructure(string|array $sampleJson, array $actualData): void
    {
        $sample = is_string($sampleJson) ? json_decode($sampleJson, true) : $sampleJson;
        $this->assertNotNull($sample, 'Sample JSON must be valid JSON: '.json_last_error_msg());

        // If sample has top-level 'data' wrapper, unwrap it if actualData is already unwrapped
        if (isset($sample['data']) && ! isset($actualData['data'])) {
            $sample = $sample['data'];
        }

        $expectedKeys = array_keys($sample);
        $this->assertAllResourceKeys($expectedKeys, $actualData);
    }
}
