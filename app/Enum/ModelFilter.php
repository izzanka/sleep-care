<?php

namespace App\Enum;

enum ModelFilter: string
{
    case EQUAL = 'equal';
    case OR_EQUAL = 'or_equal';
    case NOT_EQUAL = 'not_equal';
    case GROUP_BY = 'group_by';
    case ORDER_BY = 'order_by';
    case MORE_THAN = '>';
    case LESS_THAN = '<';
}
