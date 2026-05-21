# FASE 4 - SALES/POS IMPLEMENTATION PLAN
## Revised Architecture with User Decisions

**Date:** May 21, 2026  
**Status:** PLAN ONLY (No code generated, no files modified)  
**Phase Goal:** Implement SALES/POS modules following existing patterns

---

## EXECUTIVE SUMMARY

This plan details the implementation of SALES/POS modules for the Laravel Inventory ERP. It incorporates 9 critical architectural decisions that simplify transaction management, standardize state handling, and defer non-essential features.

### Key Revisions from Initial Architecture

| Decision | Initial Design | Revised Design | Rationale |
|----------|---|---|---|
| Transactions | 4-level nesting in services | Single transaction per service method | Simplifies debugging, MySQL savepoint overhead |
| Repository Transactions | Multiple (create, update) | ZERO - Repositories are data-only | Clear separation of concerns |
| CajaRepository | Immediate implementation | Deferred | No immediate feature need |
| State Management | String constants | Native PHP Enums | Type safety, autocompletion |
| Stock Modifications | Direct in services | Centralized via InventoryMovementService | Single source of truth |
| Audit Relations | Via ForeignKeys | Deferred - added later with FK implementation | Dependency order |
| Payment Methods | Included | Excluded from scope | Out of MVP scope |
| findByCodigo() | VentaRepository method | Removed | Not needed for current flows |

---

# PART 1: ARCHITECTURE REVISIONS

## 1.1 Transaction Strategy (Single Level)

**RULE: Single DB::transaction() at Service layer only**

All operations within service method happen atomically in one transaction. No nested transactions in repositories or sub-services.

### Implementation Pattern

```php
class VentaService {
    public function closeVenta(Venta $venta): Venta {
        return DB::transaction(function () use ($venta): Venta {
            // Phase 1: Validate all conditions (no nested transactions)
            $this->validateCanClose($venta);
            
            // Phase 2: Register inventory movements
            foreach ($venta->detalles as $detalle) {
                $this->inventoryService->registerSalidaFromVenta($venta, $detalle);
            }
            
            // Phase 3: Update venta status
            $venta->update(['estado' => VentaEstado::Closed->value]);
            
            // Implicit commit
            return $venta->refresh();
        });
    }
}
```

### Benefits
- Single atomic boundary
- Easier debugging (one transaction per operation)
- Failures roll back entire operation
- No savepoint overhead

---

## 1.2 Repository Layer (No Transactions)

**RULE: Repositories = Data Access Only (No DB::transaction())**

Repositories perform CRUD operations without wrapping in transactions. Services decide when to wrap in DB::transaction().

```php
class VentaRepository {
    public function create(array $data, array $detalles): Venta {
        // ✅ NO TRANSACTION HERE
        $venta = Venta::create($data);
        $venta->detalles()->createMany($detalles);
        return $venta->refresh();
    }
}
```

### VentaRepository Methods

- `paginateForList(?search, ?estado, ?cajaId)` - List with filters
- `findWithRelations(Venta)` - Eager load relationships
- `create(data, detalles)` - Master-detail creation
- `update(Venta, data, detalles)` - Update master + details
- `updateEstado(Venta, estado)` - State transition only
- `delete(Venta)` - Soft delete
- `getPendingByUser(User)` - Get user's open ventas

---

## 1.3 Pessimistic Locking - Stock Only

**RULE: lockForUpdate() ONLY in ProductoRepository::updateStock()**

Only the stock update operation uses pessimistic locking. All other operations don't acquire locks.

```php
class ProductoRepository {
    public function updateStock(Producto $producto, int $stockActual): Producto {
        return DB::transaction(function () use ($producto, $stockActual): Producto {
            $lockedProducto = Producto::query()
                ->whereKey($producto->getKey())
                ->lockForUpdate()  // ✅ ONLY HERE
                ->firstOrFail();
            
            $lockedProducto->update(['stock_actual' => max(0, $stockActual)]);
            return $lockedProducto->refresh();
        });
    }
}
```

This prevents race conditions where two concurrent sales could both reduce stock incorrectly.

---

## 1.4 State Management - Native PHP Enums

**RULE: Use Native PHP Enums (not string constants)**

Define proper Enums with methods and type safety.

```php
enum VentaEstado: string {
    case Open = 'open';
    case Closed = 'closed';
    
    public function label(): string {
        return match ($this) {
            self::Open => 'Abierta',
            self::Closed => 'Cerrada',
        };
    }
}

enum CajaEstado: string {
    case Open = 'open';
    case Closed = 'closed';
}

enum DevolucionEstado: string {
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
}
```

### Model Casting

```php
class Venta extends Model {
    public function casts(): array {
        return [
            'estado' => VentaEstado::class,
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }
}
```

### Benefits
- Type safety (IDE autocompletion)
- Single source of truth
- Methods on enums (label(), etc.)
- Database queries still work: `where('estado', 'open')`

---

## 1.5 Inventory Movement Centralization

**RULE: All Stock Modifications via InventoryMovementService**

Every stock change (entrada/salida) must go through InventoryMovementService for complete audit trail.

### New Methods

```php
class InventoryMovementService {
    // NEW: Called from VentaService when closing sale
    public function registerSalidaFromVenta(
        Venta $venta,
        DetalleVenta $detalle,
        ?int $userId = null
    ): MovimientoInventario { }
    
    // NEW: Called from DevolucionService when approving return
    public function registerEntradaFromDevolucion(
        Devolucion $devolucion,
        DetalleDevolucion $detalle,
        ?int $userId = null
    ): MovimientoInventario { }
}
```

### Benefits
- Single source of truth for inventory movements
- Complete audit trail (who, what, when, why)
- Traceability from venta/devolucion to stock change
- Consistent validation everywhere

---

## 1.6 CajaRepository Deferred

**Decision: Postpone CajaRepository Implementation**

No dedicated CajaRepository in FASE 4. Caja operations handled directly in controllers or simple queries.

**Workaround for FASE 4:**

```php
class VentaService {
    public function createVenta(array $data, array $detalles): Venta {
        return DB::transaction(function () use ($data, $detalles) {
            // Get user's open caja directly (no repository)
            $caja = Caja::query()
                ->where('user_id_open', auth()->id())
                ->where('status', 'open')
                ->firstOrFail();
            
            $data['caja_id'] = $caja->id;
            return $this->ventaRepository->create($data, $detalles);
        });
    }
}
```

### Future Implementation (FASE 5+)

When Caja features expand:
1. Create CajaRepository interface
2. Move queries to repository
3. Zero changes to VentaService (already abstracted)

---

## 1.7 Removed Features

### findByCodigo() - REMOVED
- Venta doesn't have unique `codigo` field
- Not needed for current flows

### Payment Methods - EXCLUDED
- Out of MVP scope
- Future: PaymentService, PaymentController, payments table
- Zero impact on current architecture

---

# PART 2: FILES & DEPENDENCIES

## 2.1 New Files to Create (28 total)

### Enums (3 files)
- `app/Enums/VentaEstado.php`
- `app/Enums/CajaEstado.php`
- `app/Enums/DevolucionEstado.php`

### Repositories (4 files)
- `app/Repositories/Interfaces/VentaRepositoryInterface.php`
- `app/Repositories/VentaRepository.php`
- `app/Repositories/Interfaces/DevolucionRepositoryInterface.php`
- `app/Repositories/DevolucionRepository.php`

### Services (2 files - InventoryMovementService modified)
- `app/Services/VentaService.php`
- `app/Services/DevolucionService.php`

### Policies (2 files)
- `app/Policies/VentaPolicy.php`
- `app/Policies/DevolucionPolicy.php`

### Form Requests (5 files)
- `app/Http/Requests/StoreVentaRequest.php`
- `app/Http/Requests/UpdateVentaRequest.php`
- `app/Http/Requests/CloseVentaRequest.php`
- `app/Http/Requests/StoreDevolucioRequest.php`
- `app/Http/Requests/ApproveDevolucioRequest.php`

### Controllers (2 files)
- `app/Http/Controllers/VentaController.php`
- `app/Http/Controllers/DevolucionController.php`

### Routes (2 files)
- `routes/sales.php`
- `routes/returns.php`

### Tests (8 files)
- `tests/Feature/Sales/CreateVentaTest.php`
- `tests/Feature/Sales/CloseVentaTest.php`
- `tests/Feature/Sales/DeleteVentaTest.php`
- `tests/Feature/Returns/CreateDevolucionTest.php`
- `tests/Feature/Returns/ApproveDevolucionTest.php`
- `tests/Feature/Returns/RejectDevolucionTest.php`
- `tests/Feature/Services/VentaServiceTest.php`
- `tests/Feature/Services/DevolucionServiceTest.php`

---

## 2.2 Files to Modify

### Model Files (4 files)
- `app/Models/Venta.php` - Add enum cast for estado
- `app/Models/Caja.php` - Add enum cast for status
- `app/Models/Devolucion.php` - Add enum cast for estado
- `app/Models/MovimientoInventario.php` - Add venta() and devolucion() relationships

### Service Files (1 file)
- `app/Services/InventoryMovementService.php` - Add 2 new methods

### Dependency Injection (1 file)
- `app/Providers/AppServiceProvider.php` - Add 2 bindings

### Routes (1 file)
- `bootstrap/app.php` - Register sales.php and returns.php routes

### Database (1 file)
- `database/migrations/2026_05_21_XXXXXX_add_venta_devolucion_fks_to_movimientos_inventario.php` [NEW]

---

## 2.3 Dependencies to Reuse

### Existing Services
- StockCalculationService - ✅ Reuse as-is
- InventoryMovementService - ✅ Extend with 2 methods

### Reference Implementations
- CompraRepository - 📖 Reference for master-detail pattern
- ProductoRepository - 📖 Reference for lockForUpdate pattern
- UserPolicy - 📖 Reference for role-based authorization
- StoreCompraRequest - 📖 Reference for nested validation

### Existing Middleware
- EnsureUserHasRole - ✅ Reuse for route protection

---

# PART 3: IMPLEMENTATION SEQUENCE

## Phase 1: Enums (Foundation)
**Files:** 3  
**Time Est:** 30 min  
**Dependencies:** None

1. Create VentaEstado enum
2. Create CajaEstado enum
3. Create DevolucionEstado enum

**Verification:**
- Enum files parse without errors
- Can instantiate: `VentaEstado::Open`

---

## Phase 2: Database & Models (Foundation)
**Files:** 5 modified + 1 migration  
**Time Est:** 45 min  
**Dependencies:** Phase 1 (Enums)

1. Create & run migration (add venta_id, devolucion_id FK to movimientos_inventario)
2. Update Venta model (add estado cast)
3. Update Caja model (add status cast)
4. Update Devolucion model (add estado cast)
5. Update MovimientoInventario model (add relationships)

**Verification:**
- Migration runs: `php artisan migrate`
- Models load without errors
- Can cast: `$venta->estado === VentaEstado::Open`

---

## Phase 3: Repositories (Data Layer)
**Files:** 4  
**Time Est:** 1.5 hours  
**Dependencies:** Phase 2 (Models)

1. Create VentaRepositoryInterface
2. Create VentaRepository implementation
3. Create DevolucionRepositoryInterface
4. Create DevolucionRepository implementation

**Verification:**
- Repository classes implement interfaces
- Can instantiate via DI
- paginateForList() tests pass

---

## Phase 4: Extend InventoryMovementService (Audit Layer)
**Files:** 1 modified  
**Time Est:** 45 min  
**Dependencies:** Phase 3 (Repositories)

1. Add registerSalidaFromVenta() method
2. Add registerEntradaFromDevolucion() method
3. Modify registerMovement() signature

**Verification:**
- Methods callable: `$inventoryService->registerSalidaFromVenta()`
- MovimientoInventario records have venta_id populated

---

## Phase 5: Services (Business Logic)
**Files:** 2  
**Time Est:** 2 hours  
**Dependencies:** Phase 4 (InventoryMovementService)

1. Create VentaService (createVenta, updateVenta, closeVenta, deleteVenta)
2. Create DevolucionService (createDevolucion, approveDevolucion, rejectDevolucion)

**Verification:**
- Services instantiate via DI
- closeVenta() correctly updates stock
- approveDevolucion() restores stock atomically

---

## Phase 6: Policies (Authorization)
**Files:** 2  
**Time Est:** 45 min  
**Dependencies:** Phase 5 (Services)

1. Create VentaPolicy
2. Create DevolucionPolicy

**Verification:**
- Policies authorize correctly
- Can use in controllers: `$this->authorize('close', $venta)`

---

## Phase 7: Form Requests (Validation)
**Files:** 5  
**Time Est:** 1.5 hours  
**Dependencies:** Phase 6 (Policies)

1. Create StoreVentaRequest
2. Create UpdateVentaRequest
3. Create CloseVentaRequest
4. Create StoreDevolucioRequest
5. Create ApproveDevolucioRequest

**Verification:**
- Validation rules work correctly
- Authorization checks pass

---

## Phase 8: Controllers & Routes (HTTP Layer)
**Files:** 2 controllers + 2 route files + 1 bootstrap modification  
**Time Est:** 2 hours  
**Dependencies:** Phase 7 (Form Requests)

1. Create VentaController (index, show, store, update, destroy, close)
2. Create DevolucionController (index, show, store, approve, reject)
3. Create routes/sales.php
4. Create routes/returns.php
5. Modify bootstrap/app.php to register routes

**Verification:**
- Routes exist: `php artisan route:list --path=sales`
- Can POST to `/sales` with validation

---

## Phase 9: Tests (Verification)
**Files:** 8  
**Time Est:** 3 hours  
**Dependencies:** Phase 8 (Controllers)

1. VentaRepository tests
2. DevolucionRepository tests
3. VentaService tests
4. DevolucionService tests
5. Feature tests (CreateVentaTest, CloseVentaTest, etc.)

**Verification:**
- All tests pass: `php artisan test --filter=Venta`
- Coverage > 80%

---

# PART 4: RISK ASSESSMENT

## Risk 1: Transaction Atomicity (HIGH)
**Description:** Single-level transaction means all operations complete atomically or rollback.

**Mitigation:**
- Validate all conditions BEFORE entering transaction
- Use FormRequest validation
- Use policy authorization

---

## Risk 2: Stock Race Condition (MEDIUM)
**Description:** Without locking, concurrent sales could over-reduce stock.

**Mitigation:**
- ProductoRepository::updateStock() uses lockForUpdate()
- All stock changes through StockCalculationService
- Pessimistic lock held for entire transaction

---

## Risk 3: Enum Casting Issue (LOW)
**Description:** If enum values don't match database values, casting fails.

**Mitigation:**
- Enum values = current database values (open, closed, pending, etc.)
- No data migration needed

---

## Risk 4: N+1 Query Problem (LOW)
**Description:** Listing ventas without eager loading causes N+1 queries.

**Mitigation:**
- VentaRepository::paginateForList() uses eager load
- Use findWithRelations() for detail views

---

## Risk 5: Venta Without Open Caja (HIGH)
**Description:** If caja closes, closing venta fails.

**Mitigation:**
- VentaService::closeVenta() validates caja.status == 'open'
- Throws ValidationException if caja closed

---

## Risk 6: Audit Trail Gaps (LOW)
**Description:** Some stock changes not recorded.

**Mitigation:**
- All changes centralized via InventoryMovementService
- registerSalidaFromVenta() called for every closed venta

---

# VALIDATION CHECKLIST

## Pre-Implementation

- [ ] app/Enums/ directory exists
- [ ] app/Repositories/Interfaces/ directory exists
- [ ] All FASE 0-2 migrations applied
- [ ] Models (Venta, Caja, Devolucion) exist
- [ ] InventoryMovementService exists
- [ ] StockCalculationService exists
- [ ] Role constants verified (Admin, Seller, Warehouse)

## Post-Implementation

- [ ] All 28 new files created
- [ ] All 8 existing files modified
- [ ] Migration runs: `php artisan migrate`
- [ ] Models load without errors
- [ ] Services instantiate via DI
- [ ] All tests pass: `php artisan test --filter=Venta`
- [ ] Routes exist: `php artisan route:list --path=sales`
- [ ] No N+1 queries in list views

---

**Document Status:** ✅ PLAN COMPLETE  
**Awaiting:** User approval to proceed with implementation

**Next Steps:**
1. Review this plan
2. Clarify any points
3. Approve or suggest changes
4. Begin FASE 4 implementation
