<?php

namespace App\Enums;


enum SectionEnum: string
{
    case HERO = 'hero';
    case REDISTRIBUTION_TABLE = 'redistribution-table';
    case PARCENTAGE = 'percentage';
    case SELECTED_NAME = 'selected_name';
    case QUOTE = 'quote';
    case OUR_STORY = 'our-story';
    case TESTIMONIAL = 'testimonial';
    case GALLERY = 'gallery';
    case DISCLAIMER = 'disclaimer';
    case WE_BELIEVE = 'we-believe';
    case FOUNDER_STATEMENT = 'founder-statement';
}
