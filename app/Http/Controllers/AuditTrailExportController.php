<?php

namespace App\Http\Controllers;

use App\Features\Audit\ExportAuditTrailCsvFeature;
use Illuminate\Http\Request;

class AuditTrailExportController extends Controller
{
    public function __invoke(Request $request)
    {
        return $this->serve(ExportAuditTrailCsvFeature::class, $request->only([
            'date_from', 'date_to', 'entity_name', 'action', 'user_id',
        ]));
    }
}
