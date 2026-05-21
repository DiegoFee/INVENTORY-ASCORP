<?php

namespace App\Enums;

enum CompraEstadoEnum: string
{
    case BORRADOR = 'borrador';
    case CONFIRMADA = 'confirmada';
    case RECIBIDA = 'recibida';
}
