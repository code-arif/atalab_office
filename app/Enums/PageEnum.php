<?php


namespace App\Enums;

enum PageEnum: string
{
    case HOME  = 'home';
    case OUR_STORY = 'our-story';
    case HOW_IT_WORKS = 'how-it-works';
    case STRUCTURE = 'structure';
    case ELIGIBILITY = 'eligibility';
    case PAYMENT_POLICY = 'payment-policy';
    case TAX_POLICY = 'tax-policy';
    case ETHICAL_BOUNDARIES =  'ethical-boundaries';
    case OFFICER_COMPENSATION_POLICY = 'officer_compensation_policy';
    case ARCHIVES =  'archives';
    case CONTACT_US = 'contact-us';
}
