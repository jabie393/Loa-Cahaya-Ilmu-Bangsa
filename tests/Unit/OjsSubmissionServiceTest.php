<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\OjsSubmissionService;

class OjsSubmissionServiceTest extends TestCase
{
    /**
     * Test sanitization of 4-byte mathematical alphanumeric characters.
     */
    public function test_sanitize_utf8_converts_mathematical_alphanumeric_and_strips_emojis(): void
    {
        $service = new class extends OjsSubmissionService {
            public function testSanitize(mixed $value): mixed
            {
                return $this->sanitizeUtf8($value);
            }
        };

        // "𝑠" is U+1D460 (Mathematical Italic Small S)
        // "𝑡" is U+1D461 (Mathematical Italic Small T)
        // Emojis like 🚀 are U+1F680
        $input = [
            'title' => 'Test Article 𝑠',
            'abstract' => 'Analisis Betweenness Centrality dihitung untuk seluruh pasangan simpul asal 𝑠 dan tujuan 𝑡 melalui simpul perantara 🚀',
            'keywords' => 'test 𝑠, 𝑡, 🚀',
        ];

        $expected = [
            'title' => 'Test Article s',
            'abstract' => 'Analisis Betweenness Centrality dihitung untuk seluruh pasangan simpul asal s dan tujuan t melalui simpul perantara ',
            'keywords' => 'test s, t, ',
        ];

        $this->assertEquals($expected, $service->testSanitize($input));
    }
}
