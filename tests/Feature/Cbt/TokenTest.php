<?php

namespace Tests\Feature\Cbt;

use App\Models\Cbt\Token;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class TokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_token_auto_generate_command_works_when_auto_is_true()
    {
        $token = Token::create([
            'token' => 'OLDTOK',
            'auto' => true,
        ]);

        Artisan::call('cbt:generate-token');

        $token->refresh();
        $this->assertNotEquals('OLDTOK', $token->token);
        $this->assertEquals(6, strlen($token->token));
    }

    public function test_token_auto_generate_command_skips_when_auto_is_false()
    {
        $token = Token::create([
            'token' => 'OLDTOK',
            'auto' => false,
        ]);

        Artisan::call('cbt:generate-token');

        $token->refresh();
        $this->assertEquals('OLDTOK', $token->token);
    }
}
