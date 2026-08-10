<?php

namespace Tests\Unit;

use App\Http\Middleware\DecodeSqids;
use App\Models\Traits\HasSqid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Sqids\SqidsInterface;
use Tests\TestCase;

class SqidCanonicalizationTest extends TestCase
{
    public function test_middleware_rejects_a_non_canonical_sqid(): void
    {
        $request = Request::create('/', 'POST', ['project_id' => $this->nonCanonicalSqid()]);

        (new DecodeSqids(app(SqidsInterface::class)))->handle($request, function (Request $request) {
            $this->assertNull($request->input('project_id'));

            return response()->noContent();
        }, 'project_id');
    }

    public function test_route_binding_rejects_a_non_canonical_sqid(): void
    {
        $model = new class extends Model {
            use HasSqid;
        };

        $this->expectException(ModelNotFoundException::class);

        $model->resolveRouteBinding($this->nonCanonicalSqid());
    }

    public function test_soft_deletable_route_binding_rejects_a_non_canonical_sqid(): void
    {
        $model = new class extends Model {
            use HasSqid;
        };

        $this->assertNull($model->resolveSoftDeletableRouteBinding($this->nonCanonicalSqid()));
    }

    private function nonCanonicalSqid(): string
    {
        $sqids = app(SqidsInterface::class);
        $canonical = $sqids->encode([123]);

        foreach (str_split(config('spectacular.sqids.alphabet')) as $suffix) {
            $value = $canonical . $suffix;
            $ids = $sqids->decode($value);

            if (count($ids) === 1 && $sqids->encode($ids) !== $value) {
                return $value;
            }
        }

        $this->fail('Unable to generate a non-canonical Sqid.');
    }
}
