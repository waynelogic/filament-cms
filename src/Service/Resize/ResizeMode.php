<?php

namespace Waynelogic\FilamentCms\Service\Resize;

enum ResizeMode: string
{
    case EXACT = 'exact';
    case PORTRAIT = 'portrait';
    case LANDSCAPE = 'landscape';
    case AUTO = 'auto';
    case CROP = 'crop';
    case FIT = 'fit';
}
