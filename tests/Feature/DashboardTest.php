<?php

namespace Tests\Feature;

use App\Domains\WinMan\Data\ManufacturingOrderData;
use App\Features\ManufacturingOrders\SearchManufacturingOrdersFeature;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_groups_orders_by_classification_and_unit_of_measure(): void
    {
        $mock = Mockery::mock(SearchManufacturingOrdersFeature::class);
        $mock->shouldReceive('__invoke')->once()->with(null, 250)->andReturn([
            new ManufacturingOrderData(
                winmanManufacturingOrder: 1,
                winmanManufacturingOrderId: 'MO-INT-10',
                winmanProductInternal: 101,
                winmanProductId: '70010001',
                productDescription: 'Intermediate product',
                systemType: 'F',
                plannedQuantity: 100.0,
                quantityOutstanding: 40.0,
                classification: 30,
                unitOfMeasure: 10,
                dueDate: '2026-07-10 00:00:00',
                lastModifiedDate: null,
            ),
            new ManufacturingOrderData(
                winmanManufacturingOrder: 2,
                winmanManufacturingOrderId: 'MO-WET-IBC',
                winmanProductInternal: 102,
                winmanProductId: '70010002',
                productDescription: 'Wet packed IBC product',
                systemType: 'F',
                plannedQuantity: 1000.0,
                quantityOutstanding: 600.0,
                classification: 29,
                unitOfMeasure: 2,
                dueDate: '2026-07-11 00:00:00',
                lastModifiedDate: null,
            ),
            new ManufacturingOrderData(
                winmanManufacturingOrder: 3,
                winmanManufacturingOrderId: 'MO-WET-BUCKET',
                winmanProductInternal: 104,
                winmanProductId: '70010004',
                productDescription: 'Wet packed bucket product',
                systemType: 'F',
                plannedQuantity: 300.0,
                quantityOutstanding: 120.0,
                classification: 29,
                unitOfMeasure: 44,
                dueDate: '2026-07-13 00:00:00',
                lastModifiedDate: null,
            ),
            new ManufacturingOrderData(
                winmanManufacturingOrder: 4,
                winmanManufacturingOrderId: 'MO-OTHER',
                winmanProductInternal: 103,
                winmanProductId: '70010003',
                productDescription: 'Other classification',
                systemType: 'F',
                plannedQuantity: 500.0,
                quantityOutstanding: 200.0,
                classification: 13,
                unitOfMeasure: 20,
                dueDate: '2026-07-12 00:00:00',
                lastModifiedDate: null,
            ),
        ]);

        $this->app->instance(SearchManufacturingOrdersFeature::class, $mock);

        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Classification 30 - Intermediate');
        $response->assertSee('Classification 29 - Wet Packed');
        $response->assertSee('UnitOfMeasure: 10');
        $response->assertSee('UnitOfMeasure: IBC');
        $response->assertSee('UnitOfMeasure: Buckets');
        $response->assertSee('MO-INT-10');
        $response->assertSee('MO-WET-IBC');
        $response->assertSee('MO-WET-BUCKET');
        $response->assertSee('outside Classification 30 (Intermediate) and 29 (Wet Packed)');
    }
}
