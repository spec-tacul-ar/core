<?php

namespace Tests\Unit;

use App\Casts\AsSqid;
use Illuminate\Database\Eloquent\Model;
use Sqids\SqidsInterface;
use Tests\TestCase;

class AsSqidTest extends TestCase
{
    public function test_it_sets_the_underlying_id_from_a_sqid(): void
    {
        $cast = AsSqid::castUsing([]);
        $sqid = app(SqidsInterface::class)->encode([123]);

        $attributes = $cast->set(new class extends Model {}, 'project_sqid', $sqid, []);

        $this->assertSame(['project_id' => 123], $attributes);
    }

    public function test_it_removes_the_literal_sqid_suffix_when_deriving_the_field(): void
    {
        $cast = AsSqid::castUsing([]);
        $sqid = app(SqidsInterface::class)->encode([123]);

        $attributes = $cast->set(new class extends Model {}, 'status_sqid', $sqid, []);

        $this->assertSame(['status_id' => 123], $attributes);
    }

    public function test_it_can_clear_the_underlying_id(): void
    {
        $cast = AsSqid::castUsing([]);

        $attributes = $cast->set(new class extends Model {}, 'project_sqid', null, []);

        $this->assertSame(['project_id' => null], $attributes);
    }
}
