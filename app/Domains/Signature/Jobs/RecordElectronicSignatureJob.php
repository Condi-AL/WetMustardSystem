<?php

namespace App\Domains\Signature\Jobs;

use App\Models\ElectronicSignature;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Records a named, timestamped electronic signature against an entity.
 *
 * entity_name is derived from the model's table so signatures are consistently
 * keyed across the system.
 */
class RecordElectronicSignatureJob
{
    public function __invoke(
        Model $entity,
        string $purpose,
        User $user,
        string $meaning,
        ?string $comment = null,
    ): ElectronicSignature {
        return ElectronicSignature::create([
            'entity_name' => $entity->getTable(),
            'entity_id' => $entity->getKey(),
            'signature_purpose' => $purpose,
            'user_id' => $user->id,
            'signed_at' => now(),
            'meaning' => $meaning,
            'comment' => $comment,
        ]);
    }
}
