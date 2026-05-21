<?php

namespace App\Enums;

enum MovimientoInventarioOrigenEnum: string
{
    case COMPRA = 'compra';
    case MANUAL = 'manual';
    case SALIDA = 'salida';
    case VENTA = 'venta';
    case DEVOLUCION = 'devolucion';
}
